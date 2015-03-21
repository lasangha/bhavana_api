<?php

if(!isset($_POST['email'])){
	exit;
}

if(!isset($_POST['from'])){
	exit;
}

if(!isset($_POST['msg'])){
	exit;
}

$to      = 'lasanghacr@yahoo.com';
$subject = 'Contacto - ' . $_POST['from'];
$message = $_POST['msg'];
$headers = 'From: app@lasangha.org' . "\r\n" .
    'Reply-To: ' . $_POST['email'] . "\r\n" .
    'X-Mailer: PHP/' . phpversion();

mail($to, $subject, $message, $headers);
//$a = $to.$subject.$message.$headers;
//system("echo 'hello$a' >> aqui");
