<?php

if(@$_GET['type'] == 'email') {
	
	$emails = $_GET['emails'];	
	$subject = $_GET['subject'];
	$email = $_GET['template'];
	
	$headers  = "From: Pi-Com<no-reply@pi-com.ch>\r\n";
	$headers .= "MIME-Version: 1.0\r\n";
	$headers .= "Content-type: text/html; charset=iso-8859-1";
	
	$sent = true;
	foreach($emails as $email) {
		if(!mail($email, $subject, $email, $headers)) {
			$sent = false;
		}
	}
}


?>