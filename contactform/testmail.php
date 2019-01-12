<?php 
$mail1 = new PHPMailer();
      //Server settings
$to = 'srivastava.deepanshu24@gmail.com';
      $mail1->SMTPDebug = 2; // Enable verbose debug output
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

 ?>