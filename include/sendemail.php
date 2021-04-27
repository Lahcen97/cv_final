<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'phpmailer/src/Exception.php';
require 'phpmailer/src/PHPMailer.php';

// If you intend you use SMTP, uncomment next line
//require 'phpmailer/src/SMTP.php';


$recipients = array();

$recipients[] = array(
	'email' => 'ryoken.esport2021@gmail.com',
	'name' => 'Mohamed'
);

$senderMail=$_POST["cf-email"];
$senderName=$_POST["cf-name"];
$senderSubject=$_POST["cf-name"];
$senderMessage=$_POST["cf-message"];
$key="RYokenryoken123";
// Set the sender email address here
$sender = array(
	'email' => $senderMail,
	'name' => $senderName
);





// PHPMailer Initialization
$mail = new PHPMailer();
$mail->IsHTML(true);
$mail->SMTPDebug = 0;  // debogage: 1 = Erreurs et messages, 2 = messages seulement
$mail->SMTPAuth = true;  // Authentification SMTP active
$mail->SMTPSecure = 'ssl'; // Gmail REQUIERT Le transfert securise
$mail->Host = 'smtp.gmail.com';
$mail->Port = 465;
$mail->Username = 'ryoken.esport2021@gmail.com';
$mail->Password = md5($key);
$mail->SetFrom('ryoken.esport2021@gmail.com', 'Ryoken');
$mail->Subject = $senderSubject;
$mail->Body = $senderMessage;
$mail->AddAddress($recipients["email"]);
// If you intend you use SMTP, add your SMTP Code after this Line

// If you intend you use SMTP, add your SMTP Code after this Line


// End of SMTP


// Form Messages
$message = array(
	'success'           => 'Thank you for your message. It has been sent.',
	'error'             => 'There was an error trying to send your message. Please try again later.',
	'error_bot'         => 'Bot Detected! Message could not be send. Please try again.',
	'error_unexpected'  => 'There was an unexpected error trying to send your message. Please try again later.',
	'recaptcha_invalid' => 'Captcha not Validated! Please Try Again.',
	'recaptcha_error'   => 'Captcha not Submitted! Please Try Again.'
);

// Form Processor
if( $_SERVER['REQUEST_METHOD'] == 'POST' ) {

	$prefix    = !empty( $_POST['prefix'] ) ? $_POST['prefix'] : '';
	$submits   = $_POST;
	$botpassed = false;
	
	$message_form                 = !empty( $submits['message'] ) ? $submits['message'] : array();
	$message['success']           = !empty( $message_form['success'] ) ? $message_form['success'] : $message['success'];
	$message['error']             = !empty( $message_form['error'] ) ? $message_form['error'] : $message['error'];
	$message['error_bot']         = !empty( $message_form['error_bot'] ) ? $message_form['error_bot'] : $message['error_bot'];
	$message['error_unexpected']  = !empty( $message_form['error_unexpected'] ) ? $message_form['error_unexpected'] : $message['error_unexpected'];
	$message['recaptcha_invalid'] = !empty( $message_form['recaptcha_invalid'] ) ? $message_form['recaptcha_invalid'] : $message['recaptcha_invalid'];
	$message['recaptcha_error']   = !empty( $message_form['recaptcha_error'] ) ? $message_form['recaptcha_error'] : $message['recaptcha_error'];


	// Bot Protection
	if( isset( $submits[ $prefix . 'botcheck' ] ) ) {
		$botpassed = true;
	}

	if( !empty( $submits[ $prefix . 'botcheck' ] ) ) {
		$botpassed = false;
	}

	if( $botpassed == false ) {
		echo '{ "alert": "error", "message": "' . $message['error_bot'] . '" }';
		exit;
	}



	$html_title	= !empty( $submits['html_title'] ) ? $submits['html_title'] : 'Form Response';
	$forcerecaptcha	= ( !empty( $submits['force_recaptcha'] ) && $submits['force_recaptcha'] != 'false' ) ? true : false;
	$replyto = !empty( $submits['replyto'] ) ? explode( ',', $submits['replyto'] ) : false;

	if( $forcerecaptcha ) {
		if( !isset( $submits['g-recaptcha-response'] ) ) {
			echo '{ "alert": "error", "message": "' . $message['recaptcha_error'] . '" }';
			exit;
		}
	}

	$mail->Subject = !empty( $submits['subject'] ) ? $submits['subject'] : 'Form response from your website';
	$mail->SetFrom( $sender['email'] , $sender['name'] );

	if( !empty( $replyto ) ) {
		if( count( $replyto ) > 1 ) {
			$replyto_e = $submits[ $replyto[0] ];
			$replyto_n = $submits[ $replyto[1] ];
			$mail->AddReplyTo( $replyto_e , $replyto_n );
		} elseif( count( $replyto ) == 1 ) {
			$replyto_e = $submits[ $replyto[0] ];
			$mail->AddReplyTo( $replyto_e );
		}
	}

	foreach( $recipients as $recipient ) {
		$mail->AddAddress( $recipient['email'] , $recipient['name'] );
	}

	$unsets = array( 'prefix', 'subject', 'replyto', 'message', $prefix . 'botcheck', 'g-recaptcha-response', 'force_recaptcha', $prefix . 'submit' );

	foreach( $unsets as $unset ) {
		unset( $submits[ $unset ] );
	}

	$fields = array();

	foreach( $submits as $name => $value ) {
		if( empty( $value ) ) continue;

		$name = str_replace( $prefix , '', $name );
		$name = ucwords( str_replace( '-', ' ', $name ) );

		if( is_array( $value ) ) {
			$value = implode( ', ', $value );
		}

		$fields[$name] = $value;
	}

	$response = array();

	foreach( $fields as $fieldname => $fieldvalue ) {
		$response[] = $fieldname . ': ' . $fieldvalue;
	}

	$referrer = $_SERVER['HTTP_REFERER'] ? '<br><br><br>This Form was submitted from: ' . $_SERVER['HTTP_REFERER'] : '';

	$body = implode( "<br>", $response ) . $referrer;

	$mail->MsgHTML( $body );
	$sendEmail = $mail->Send();

	if( $sendEmail == true ):
		if( $autores && !empty( $replyto_e ) ) {
			$send_arEmail = $autoresponder->Send();
		}

		echo '{ "alert": "success", "message": "' . $message['success'] . '" }';
	else:
		echo '{ "alert": "error", "message": "' . $message['error'] . '<br><br><strong>Reason:</strong><br>' . $mail->ErrorInfo . '" }';
	endif;

} else {
	echo '{ "alert": "error", "message": "' . $message['error_unexpected'] . '" }';
}

?>