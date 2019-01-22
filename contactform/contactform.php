<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

$url = "";
$info = pathinfo($_SERVER['REQUEST_URI']);
$path = '//' . $_SERVER['HTTP_HOST'] . $info['dirname'] . '/';
require dirname(__FILE__) . '/db.class.php';
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Origin: ' . (isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '*'));
header('Access-Control-Allow-Headers: *');
$fields = array(
	array('name' => 'fullname', 'valid' => array('require'), 'title' => 'Name'),
	array('name' => 'email', 'valid' => array('require', 'email'), 'title' => 'Email'),
	array('name' => 'phone', 'valid' => array('require'), 'title' => 'Phone Number'),
	array('name' => 'qualification', 'title' => 'Qualification', 'valid' => array('require')),
	array('name' => 'experience', 'title' => 'Experience', 'valid' => array('require')),
);
$dbConn = new Database();
$data = array('addedOn' => date('Y-m-d H:i:s', time()));

if (!empty($_POST)) {
	$expected = isset($_POST['salary']) && !empty(trim($_POST['salary'])) ? "Expected:- " . $_POST['salary'] : "N/A";
	$employer = isset($_POST['employer']) && !empty(trim($_POST['employer'])) ? "Last Employer:- " . $_POST['employer'] : "N/A";
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

		//recipient
		$to = 'chitranshi.shashank74@gmail.com';
		//sender
		$from = $data["email"];
		$fromName = $data["fullname"];

		//email subject
		$subject = 'Submission from ' . $data['fullname'];

		//attachment file path
		$file = $_FILES['chooseFile']['tmp_name'];

		//email body content
		$htmlContent = 'Hi , <br/>I have done ' . $data["qualification"] . ' with an experience of ' . $data["experience"] . '.  It would be a sincere pleasure to hear back from you soon to discuss the opportunity.<br/>' . $expected . '
								               <br/>' . $employer . '<br/><br/>Regards,<br/>
								               ' . $data["fullname"] . '<br/>
								               ' . $data["phone"] . '<br/>
								               ' . $data["email"] . '<br/><br/><br/>';

		$headers = "From: $fromName" . " <" . $from . ">";

		//boundary
		$semi_rand = md5(time());
		$mime_boundary = "==Multipart_Boundary_x{$semi_rand}x";

		//headers for attachment
		$headers .= "\nMIME-Version: 1.0\n" . "Content-Type: multipart/mixed;\n" . " boundary=\"{$mime_boundary}\"";

		//multipart boundary
		$message = "--{$mime_boundary}\n" . "Content-Type: text/html; charset=\"UTF-8\"\n" .
			"Content-Transfer-Encoding: 7bit\n\n" . $htmlContent . "\n\n";

		//preparing attachment
		if (!empty($file) > 0) {
			if (is_file($file)) {
				$message .= "--{$mime_boundary}\n";
				$fp = @fopen($_FILES['chooseFile']['tmp_name'], "rb");
				$datas = @fread($fp, $_FILES['chooseFile']['size']);

				@fclose($fp);
				$datas = chunk_split(base64_encode($datas));
				$message .= "Content-Type: application/octet-stream; name=\"" . $_FILES['chooseFile']['name'] . "\"\n" .
					"Content-Description: " . $_FILES['chooseFile']['name'] . "\n" .
					"Content-Disposition: attachment;\n" . " filename=\"" . $_FILES['chooseFile']['name'] . "\"; size=" . $_FILES['chooseFile']['size'] . ";\n" .
					"Content-Transfer-Encoding: base64\n\n" . $datas . "\n\n";
			}
		}
		$message .= "--{$mime_boundary}--";
		$returnpath = "-f" . $from;

		//send email
		$mail = @mail($to, $subject, $message, $headers, $returnpath);

		//email sending status
		$res['mail'] = $mail ? 'Message has been sent' : "Mail sending failed.";

		$insertdata = $dbConn->insert('contact', $data);
		$userheaders = "MIME-Version: 1.0" . "\r\n";
		$userheaders .= "Content-type:text/html;charset=UTF-8" . "\r\n";

		// Additional headers
		$userheaders .= 'From: Shashank<chitranshi.shashank74@gmail.com>' . "\r\n";
		$usersubject = "Response from Jobspah.com";
		$userto = $data["email"];

		$usersend = 'Hi ' . $data["fullname"] . ',<br/> <br/>Thank you for your interest in  Jobspah.com. <br/>Our ninjas will connect with you shortly in response to your submission.<br/><br/> Warm Regards <br/>Jobspah.com';
		if (mail($userto, $usersubject, $usersend, $userheaders, "-f chitranshi.shashank74@gmail.com")):
			$res['mail'] = 'Email has sent successfully.';
		else:
			$res['mail'] = 'Email sending fail.';
		endif;
		$res['code'] = 'success';
		echo (json_encode($res));
	} else {
		echo json_encode(array('code' => 'failed', 'fields' => $error_fields));
	}
}
?>