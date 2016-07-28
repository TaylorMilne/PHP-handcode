<?php
session_start();
include 'connection.php';
include 'custom_function.php';

include __DIR__.'/payment/mypay/RefundSale.php';

$event_date = strval(test_input($_GET['event_date']));
$event_date=mysqli_real_escape_string($conn, $event_date);

$org_email=test_input($_SESSION['email']);
$org_email=mysqli_real_escape_string($conn, $org_email);

$org_name=$_SESSION['org_name'];

$suc=true;
$query="select is_full, send_amount, package_name, sponsor_email, event_name from eli_spon_data where org_email='$org_email' and event_date='$event_date'";
if($result=mysqli_query($conn, $query))
{
	$num=mysqli_num_rows($result);
	$row=mysqli_fetch_assoc($result);

	if($num==1 && $row['is_full'] == -1)
	{
		//update only
		$suc=true;
	}else{
		if($num == 1)
	    {
	    	$get_amount=$row['send_amount'];
	    	$sponsor_email=$row['sponsor_email'];
	    	$package_name=$row['package_name'];
	    	$event_name=$row['event_name'];
	    	$suc=refund($get_amount, $org_email, $sponsor_email);

			if($suc)
			{
				send_email_to_sponsor_carter($org_name, $org_email, $sponsor_email, $event_date, $event_name, $package_name);
			}
			

	    } else if($num == 2) {
	    	$get_amount=$row['send_amount'];
	    	$sponsor_email1=$row['sponsor_email'];
	    	$package_name=$row['package_name'];
	    	$event_name1=$row['event_name'];
	    	$suc=refund($get_amount, $org_email, $sponsor_email1);
	    	if($suc)
			{
				
				send_email_to_sponsor_carter($org_name, $org_email, $sponsor_email1, $event_date, $event_name1, $package_name);

				$row2=mysqli_fetch_assoc($result);
		    	$get_amount=$row2['send_amount'];
		    	$sponsor_email2=$row2['sponsor_email'];
		    	$event_name2=$row2['event_name'];
		    	$suc=refund($get_amount, $org_email, $sponsor_email2);
		    	if($suc)
				{
					send_email_to_sponsor_carter($org_name, $org_email, $sponsor_email2, $event_date, $event_name2, $package_name);
				}
			}
	    }
	}
    mysqli_free_result($result);
}else
{
	$suc=false;
}

if($suc)
{
	$query="update eli_spon_data set cancel='1' where org_email='$org_email' and event_date='$event_date'";
	mysqli_query($conn, $query);
	//mail send to cater, sponsor
	echo "1";
}else
{
	echo "2";
}


function send_email_to_sponsor_carter($org_name, $org_email, $sponsor_email, $event_date, $event_name, $package_name)
{
	global $conn;

	//send email to sponsor
	$to = $sponsor_email;
	$subject='Event Cancelled';
	$message = '
				<html>
				<head>
			  	<title>The event for '.$org_name.' cancelled.</title>
				</head>
				<body><P>The event for '.$org_name.' cancelled on '.$event_date.'.<br>You will get refund.</p></body></html>';
	// To send HTML mail, the Content-type header must be set
	$headers  = 'MIME-Version: 1.0' . "\r\n";
	$headers .= 'Content-type: text/html; charset=iso-8859-1'."\r\n";
	$headers .= "From: www.chulant.com"."\r\n";
	// Mail it
	mail($to, $subject, $message, $headers);
	//send email to cater email
	$caterer_email="";
	$query3="select caterer_email from eli_org_package where package_name='$package_name' and org_email='$org_email'";
	if($result3=mysqli_query($conn, $query3))
	{
		$row3=mysqli_fetch_assoc($result3);
		$caterer_email=$row3['caterer_email'];
		$to = $caterer_email;
		$subject='Event Cancelled';
		$message = '
					<html>
					<head>
				  	<title>The event for '.$org_name.' cancelled.</title>
					</head>
					<body><P>The event for '.$org_name.' cancelled on'.$event_date.'.<br>They decided to cancel the event.</p>';
	    $message .='<p>Please click this link so the Organization '.$org_name.' can know that you confirmed the event cancel.</p>';
		$message .='<a href="'.PHP_PATH.'/send_confirm_email.php?action=confirm_cancel_event&org_email='.$org_email.'&event_name='.$event_name.'&event_date='.$event_date.'" target="_blank">Confirm Cancel Event</a>';
		$message .='</body></html>';
		// To send HTML mail, the Content-type header must be set
		$headers  = 'MIME-Version: 1.0' . "\r\n";
		$headers .= 'Content-type: text/html; charset=iso-8859-1'."\r\n";
		$headers .= "From: www.chulant.com"."\r\n";
		// Mail it
		mail($to, $subject, $message, $headers);
	}
}

die();
?>