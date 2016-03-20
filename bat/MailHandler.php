<?php
	$owner_email='anantaluz@gmail.com';
	//SMTP server settings	
	$host = 'ssl://smtp.gmail.com';
    $port = '465';//"587";
    $username = '';
    $password = '';

    $subject='[WEB] Nuevo mensaje de formulario de ';
    $user_email='';    
	$message_body='';
	$message_type='html';

	$max_file_size=50;//MB 
	$file_types='/(doc|docx|txt|pdf|zip|rar)$/';
	$error_text='something goes wrong';
	$error_text_filesize='File size must be less than';
	$error_text_filetype='Failed to upload file. This file type is not allowed. Accepted files types: doc, docx, txt, pdf, zip, rar.';

	$private_recaptcha_key='6LeZwukSAAAAACmqrbLmdpvdhC68NLB1c9EA5vzU'; //localhost
	
	
	$use_recaptcha=isset( $_POST["recaptcha_challenge_field"]) and isset($_POST["recaptcha_response_field"]);
	$use_smtp=($host=='' or $username=='' or $password=='');
	$max_file_size*=1048576;

	if($owner_email=='' || $owner_email=='#'){
		die('Atención, no se ha establecido ningún email para recivir este formulario, por favor define la variable "owner_email" en el archivo MailHanlder.php.');
	}

	if(preg_match('/^(127\.|192\.168\.)/',$_SERVER['REMOTE_ADDR'])){
		die('Atención, este formulario no funciona con paginas web almacenadas de manera local, por favor utiliza este formulario en una página en la nube.');
	}

	if($use_recaptcha){
		require_once('recaptchalib.php');
		$resp = recaptcha_check_answer ($private_recaptcha_key,$_SERVER["REMOTE_ADDR"],$_POST["recaptcha_challenge_field"],$_POST["recaptcha_response_field"]);
		if (!$resp->is_valid){
			die ('wrong captcha');
		}
	}
	
	if(isset($_POST['name']) and $_POST['name'] != ''){$message_body .= '<p>Nombre del interesado: ' . $_POST['name'] . '</p>' . "\n" . '<br>' . "\n"; $subject.=$_POST['name'];}
	if(isset($_POST['email']) and $_POST['email'] != ''){$message_body .= '<p>Dirección de email: ' . $_POST['email'] . '</p>' . "\n" . '<br>' . "\n"; $user_email=$_POST['email'];}
	if(isset($_POST['state']) and $_POST['state'] != ''){$message_body .= '<p>Provincia: ' . $_POST['state'] . '</p>' . "\n" . '<br>' . "\n";}
	if(isset($_POST['phone']) and $_POST['phone'] != ''){$message_body .= '<p>Número de teléfono: ' . $_POST['phone'] . '</p>' . "\n" . '<br>' . "\n";}	
	if(isset($_POST['fax']) and $_POST['fax'] != ''){$message_body .= '<p>Número de fax: ' . $_POST['fax'] . '</p>' . "\n" . '<br>' . "\n";}
	if(isset($_POST['message']) and $_POST['message'] != ''){$message_body .= '<p>Mensaje: ' . $_POST['message'] . '</p>' . "\n";}	
	if(isset($_POST['stripHTML']) and $_POST['stripHTML']=='true'){$message_body = strip_tags($message_body);$message_type='text';}

try{
	include "libmail.php";
	$m= new Mail("utf-8");
	$m->From($user_email);
	$m->To($owner_email);
	$m->Subject($subject);
	$m->Body($message_body,$message_type);
	//$m->log_on(true);

	if(isset($_FILES['attachment'])){
		if($_FILES['attachment']['size']>$max_file_size){
			$error_text=$error_text_filesize . ' ' . $max_file_size . 'bytes';
			die($error_text);			
		}else{			
			if(preg_match($file_types,$_FILES['attachment']['name'])){
				$m->Attach($_FILES['attachment']['tmp_name'],$_FILES['attachment']['name'],'','attachment');
			}else{
				$error_text=$error_text_filetype;
				die($error_text);				
			}
		}		
	}
	if(!$use_smtp){
		$m->smtp_on( $host, $username, $password, $port);
	}

	if($m->Send()){
		die('success');
	}	
	
}catch(Exception $mail){
	die($mail);
}	
?>