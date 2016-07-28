<?php

	$headers  = 'MIME-Version: 1.0' . "\r\n";
	$headers .= 'Content-type: text/html; charset=iso-8859-1'."\r\n";
	$headers .= "From: www.chulant.com"."\r\n";

 if(isset($_GET['action']) && $_GET['action'] == "confirm_prepare_event")
 {
 	//once caterer confirmed the event, we must send email to Org user and Sponsor that has received the message
 	$org_name=$_GET['org_name'];
 	$org_email=$_GET['org_email'];
 	$sponsor_name=$_GET['sponsor_name'];
 	$sponsor_email=$_GET['sponsor_email'];
 	$event_name= $_GET['event_name'];
 	$event_date= $_GET['event_date'];

 	//send email to org user
 	$to = $org_email;
	$subject='The caterer confirmed the event.';
	$message = '<html><head><title>The caterer confirmed the event.</title></head><body>';
	$message .= '<p>Your caterer has confirmed the event '.$event_name.' on '.$event_date.'.</p>';
	$message .= '</body></html>';

	// Mail it
	mail($to, $subject, $message, $headers);

    $to = $sponsor_email;
    $subject='The caterer of '.$org_name.' has confirmed the event.';
    $message = '<html><head><title>The caterer of '.$org_name.' confirmed the event.</title></head><body>';
	$message .= '<p>The caterer of '.$org_name.' has confirmed the event '.$event_name.' on '.$event_date.'.</p>';
	$message .= '<p>Thank you for sponsoring this event.</p>';
	$message .= '</body></html>';

	// Mail it
	mail($to, $subject, $message, $headers);

	echo "Thank you for your confirmed event";


 } else if(isset($_GET['action']) && $_GET['action'] == "confirm_cancel_event"){

 	//send mail to org user that they know the fact of cancelled event.
 	$org_email=$_GET['org_email'];
 	$event_name= $_GET['event_name'];
 	$event_date= $_GET['event_date'];

 	//send email to org user
 	$to = $org_email;
	$subject='The caterer confirmed the event cancelled.';
	$message = '<html><head><title>The caterer confirmed the event cancelled.</title></head><body>';
	$message .= '<p>Your caterer has confirmed the event '.$event_name.' on '.$event_date.' cancelled.</p>';
	$message .= '</body></html>';

	// To send HTML mail, the Content-type header must be set

	// Mail it
	mail($to, $subject, $message, $headers);
	echo "Thank you for your confirmed event cancelled.";
 }
 die();
?>