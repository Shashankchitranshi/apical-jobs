<?php
//  error_reporting(E_ALL);
//  ini_set('display_errors', 1);
$url = "";
$info = pathinfo($_SERVER['REQUEST_URI']);
$path = '//' . $_SERVER['HTTP_HOST'] . $info['dirname'] . '/';
require dirname(__FILE__) . '/db.class.php';
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Origin: ' . (isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '*'));
header('Access-Control-Allow-Headers: *');
$fields = array(
	array('name' => 'referred_by', 'valid' => array('require'), 'title' => 'Name'),
	array('name' => 'referred_by_email', 'valid' => array('require'), 'title' => 'Email'),
	array('name' => 'referred_by_number', 'valid' => array('require'), 'title' => 'Number'),
	array('name' => 'fullname', 'valid' => array('require'), 'title' => 'Name'),
	array('name' => 'email', 'valid' => array('require', 'email'), 'title' => 'Email'),
	array('name' => 'phone', 'valid' => array('require'), 'title' => 'Phone Number'),
	array('name' => 'qualification', 'title' => 'Qualification', 'valid' => array('require')),
	array('name' => 'experience', 'title' => 'Experience', 'valid' => array('require')),
);
$dbConn = new Database();
$data = array('addedOn' => date('Y-m-d H:i:s', time()));

if (!empty($_POST)) {
	$error_fields = array();
	$email_content = array();
	foreach ($fields AS $field) {
		$value = isset($_POST[$field['name']]) ? $_POST[$field['name']] : '';
		$title = empty($field['title']) ? $field['name'] : $field['title'];
		if (is_array($value)) {
			$value = implode('/ ', $value);
		}
		$email_content[] = $title . ': ' . $value;
		$data[$field['name']] = $_POST[$field['name']];
		$is_valid = true;
		$err_message = '';
		if (!empty($field['valid'])) {
			foreach ($field['valid'] AS $valid) {
				switch ($valid) {
				case 'require':
					$is_valid = $is_valid && strlen($value) > 0;
					$err_message .= $field['title'] . ' is required<br/>';
					break;
				case 'email':
					if ($value != '') {
						$is_valid = $is_valid && preg_match("/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/i", $value);
						$err_message .= 'Email you entered is invalid<br/>';
					}
					break;

				default:
					break;
				}
			}
		}

		if (!$is_valid) {
			if (!empty($field['err_message'])) {
				$err_message = $field['err_message'];
			}
			$error_fields[] = array('name' => $field['name'], 'message' => $err_message);
		}
	}
	$fmsg = '';
	if (isset($_FILES['chooseFile']) && !empty($_FILES['chooseFile'])) {
		$name = $_FILES['chooseFile']['name'];
		$size = $_FILES['chooseFile']['size'];
		$type = $_FILES['chooseFile']['type'];
		$tmp_name = $_FILES['chooseFile']['tmp_name'];
		$error = $_FILES['chooseFile']['error'];
		$extension = substr($name, strpos($name, '.') + 1);

		if ($size > 1024000) {
			$fmsg = "File size should be less than 1 MB";
		}
		if (($extension == "pdf" || $extension == "docx" || $extension == "doc")) {

		} else {
			$fmsg = "Only PDF and DOC , DOCX files are allowed";
		}

	} else {
		$fmsg = "Please Select a File";
	}
	if (!empty($fmsg)) {
		$error_fields[] = array('name' => 'file', 'message' => $fmsg);
	}

	if (empty($error_fields)) {
		$to = 'srivastava.deepanshu24@gmail.com';

    require (dirname(__FILE__) . '/phpmailer/class.phpmailer.php');
    require (dirname(__FILE__) . '/phpmailer/class.smtp.php');

    $mail = new PHPMailer();
      //Server settings
      $mail->SMTPDebug = 0; // Enable verbose debug output
      $mail->isSMTP(); // Set mailer to use SMTP
      $mail->Host = 'smtp.gmail.com'; // Specify main and backup SMTP servers
      $mail->SMTPAuth = true; // Enable SMTP authentication
      $mail->Username = $to; // SMTP username
      $mail->Password = 'Deepanshu@24'; // SMTP password
      $mail->SMTPSecure = 'tls'; // Enable TLS encryption, `ssl` also accepted
      $mail->Port = 587; // TCP port to connect to

      //Recipients
      $mail->setFrom($data["referred_by_email"], $data["referred_by"]);
      $mail->addAddress($to, 'Deepanshu'); // Add a recipient
      $mail->addReplyTo($data["email"], $data["fullname"]);

      //Attachments
      $mail->addAttachment($_FILES['chooseFile']['tmp_name'], $_FILES['chooseFile']['name']); // Add attachments

      //Content
      $mail->isHTML(true); // Set email format to HTML
      $mail->Subject = 'Submission from ' . $data['referred_by'];
      $mail->Body = 'Hi there,<br/><br/>
                I am ' . $data["referred_by"] . ' referring my friend who <br/>
                 have done ' . $data["qualification"] . ' with an experience of ' . $data["experience"] . '.<br/>
                It would be a sincere pleasure to hear back from you soon to discuss the opportunity.<br/>
                Look at the details below.<br/>
                 ' . $data["fullname"] . '<br/>
                 ' . $data["phone"] . '<br/>
                 ' . $data["email"] . '<br/>';

    if($mail->send()){
      $res['mail'] = 'Message has been sent';
    } else{
      $res['mail'] = 'Message could not be sent. Mailer Error: ';
    }


      $mail1 = new PHPMailer();
      //Server settings
      $mail1->SMTPDebug = 0; // Enable verbose debug output
      $mail1->isSMTP(); // Set mailer to use SMTP
      $mail1->Host = 'smtp.gmail.com'; // Specify main and backup SMTP servers
      $mail1->SMTPAuth = true; // Enable SMTP authentication
      $mail1->Username = $to; // SMTP username
      $mail1->Password = 'Deepanshu@24'; // SMTP password
      $mail1->SMTPSecure = 'tls'; // Enable TLS encryption, `ssl` also accepted
      $mail1->Port = 587; // TCP port to connect to

      $mail1->setFrom($to, 'Deepanshu'); 
      $mail1->addAddress($data["email"], $data["fullname"]); // Add a recipient
      $mail1->isHTML(true); // Set email format to HTML
      $mail1->Subject = 'Response from ApicalJobs';
      $mail1->Body = 'Hi '. $data["fullname"].',<br/><br/>
                     Thanks for your job Submission. Our team will review and get back to you shortly. <br/><br/>
                   Regards,<br/>
                   Apical Jobs';
      if($mail1->send()){
        $res['mail'] = 'Message has been sent';
      } else{
        $res['mail'] = 'Message could not be sent. Mailer Error: ';
      }
    // //$insertdata = $dbConn->insert('contact', $data);
    

    $res['code'] = 'success';
    echo (json_encode($res));
  } else {
    echo json_encode(array('code' => 'failed', 'fields' => $error_fields));
  }
}
?>