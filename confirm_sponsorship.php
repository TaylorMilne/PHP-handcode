<?php
session_start();
include "connection.php";
include "custom_function.php";

//check password and insert sponsorship data
//update data: org_name, org_email, event_name, event_date, is_full, spon_half, package_name, sponsor_name, sponsor_email, anonymous(0), cancel (0)
$err_msg="";
$fail_redirect_url="Location: ".HTML_PATH."confirm_email";
$redirect_url="Location: ".HTML_PATH."invoice_next";
$sponsor_name="";

if ($_SERVER["REQUEST_METHOD"] == "POST" && $_POST['action'] == 'confirm_sponsor')
{		//check password of sponsor user
	$password=test_input($_POST['password']);
	$password=mysqli_real_escape_string($conn, $password);
	$password=encrypt_decrypt(1, $password);
	$sponsor_email=$_SESSION['email'];
	$sponsor_email=mysqli_real_escape_string($conn, $sponsor_email);

	$check=false;
	$query="select count(id), username from eli_user_data where email='$sponsor_email' and password='$password'";
	if($result=mysqli_query($conn, $query))
	{
		$row=mysqli_fetch_assoc($result);
		if($row['count(id)']!=1)
		{
			$_SESSION['err_msg']="Password is incorrect.";
		}else{
			$check=true;
			$sponsor_name=$row['username'];
		}
		mysqli_free_result($result);
	}else{
		$_SESSION['err_msg']="Checking Password failed. Please try again later.";
	}

	if(!$check)
	{
		header($fail_redirect_url);
		die();
	}

	//process payment (Get and Send)
	$ids=require (__DIR__).'/payment/mypay/GetSendpay.php';
					
	if(!$ids)
	{
		header($fail_redirect_url);
		die();
	}else
	{
		//if already -> half+half
		//else empty->half, full
		$already=$_SESSION['already'];
		$event_name=$_SESSION['select_event_name'];
		$org_name=$_SESSION['select_org'];
		$event_date=$_SESSION['select_event_day'];
		$package_name=$_SESSION['package_name'];
		$is_full=$_SESSION['select_is_full'];
		$anony=$_SESSION['select_anony'];
		$org_email=$_SESSION['select_org_email'];
		if($is_full)
			$spon_half=1;
		else
			$spon_half=0.5;

		$get_tr_id=$ids[0];
		
		$send_amount=$_SESSION['billing_amount'];
		$total=$_SESSION['total'];
		$real_send_amount=$send_amount-5;//get 5 per event

		$honor_txt="";
		if(isset($_SESSION['honor_txt']) && !empty($_SESSION['honor_txt']))
		{
			$honor_txt=$_SESSION['honor_txt'];
		}

		if($already)
		{
			//update half, insert half
			$query="update eli_spon_data set is_full='1' where org_name='$org_name' and org_email='$org_email' and event_name='$event_name' and event_date='$event_date'";
			mysqli_query($conn, $query);
			
			//insert
			$query="insert eli_spon_data(org_name, org_email, event_name, event_date, is_full, spon_half, package_name, sponsor_name, sponsor_email, anonymous, get_tr_id, send_amount) ";
			$query=$query."values('$org_name', '$org_email', '$event_name', '$event_date', '1', '0.5', '$package_name', '$sponsor_name', '$sponsor_email', '$anony', '$get_tr_id', '$real_send_amount')";
			mysqli_query($conn, $query);

		}else{
			$query="update eli_spon_data set is_full='$is_full', spon_half='$spon_half', package_name='$package_name', sponsor_name='$sponsor_name', sponsor_email='$sponsor_email', anonymous='$anony', get_tr_id='$get_tr_id', send_amount='$real_send_amount' ";
			$query=$query."where org_name='$org_name' and org_email='$org_email' and event_name='$event_name' and event_date='$event_date'";
			mysqli_query($conn, $query);
		}
			
		//now let's send email to cater, org_user
		send_email_make($org_name, $org_email, $sponsor_name, $sponsor_email, $package_name, $is_full, $event_name, $event_date, $honor_txt);

		/*
		
		$query="SELECT item_count, item_desc, item_price, caterer_email FROM eli_org_package WHERE org_name = '$org_name' and package_name='$package_name'";

		$result = mysqli_query($conn,$query);

		$table_data="<table><thead><td width='10%'>QTY</td><td width='50%'>ITEMS</td></thead><tbody>";

		$caterer_email="";

		if($result)
		{
			while($row = mysqli_fetch_array($result)) {
				$table_data=$table_data."<tr>
				<td>".$row['item_count']."</td>
				<td>".$row['item_desc']."</td>
				</tr>";
				if(empty($caterer_email))
				{
					$caterer_email=$row['caterer_email'];
				}
			}
			mysqli_free_result($result);
		}

		if(isset($_SESSION['select_is_full']) && !$_SESSION['select_is_full'])
		{
			$half="HALF";
		}else
		   $half="FULL";

		$table_data=$table_data."</tbody></table>";
		
		//contents
		$contents ="<br>".$table_data;
		
		//send email to org user

		$to  = $org_email; // note the comma
		// subject
		$subject = 'You got sponsored for the event'.$event_name;
		// message
		$message = '
		<html>
		<head>
		  <title>The event '.$event_name.' has been sponsored.</title>
		</head>
		<body>You got '.$half.' sponsored for the event '.$event_name.' on '.$event_date;
		if(isset($_SESSION['honor_txt']) && !empty($_SESSION['honor_txt']))
		{
			$message .='<p>The sponsor says:'.$_SESSION['honor_txt'].'<p>';
		}
		$message .= '<p>Sponsored package item list as follows:'.$contents.'</p>';
		$message .='<p>type:'.$half.'</p>';
		$message .=  '<p>Get amount: $'.$real_send_amount.'<p></body></html>';

		// To send HTML mail, the Content-type header must be set
		$headers  = 'MIME-Version: 1.0' . "\r\n";
		$headers .= 'Content-type: text/html; charset=iso-8859-1'."\r\n";
		$headers .= "From: www.chulant.com"."\r\n";

		// Mail it
		mail($to, $subject, $message, $headers);
						
		//send email to caterer
		if(!empty($caterer_email))
		{	
			$to = $caterer_email;
			$subject='Please confirm the '.$org_name.' event';
			$message = '
			<html>
			<head>
		  	<title>The event '.$event_name.' has been sponsored for '.$org_name.'.</title>
			</head>
			<body>';
			$message .= '<p>The event '.$event_name.' has been '.$half.' sponsored for '.$org_name.' on '.$event_date.'.</p>';
			$message .='<p>You should prepair the party.</p>';
			$message .='<P>Package Item list as follows:</p>'.$contents;
			$message .='<p>Please click this link so the sponsor and organization can know you got email.</p>';
			$message .='<a href="'.PHP_PATH.'/send_confirm_email.php?action=confirm_prepare_event&org_name='.$org_name.'&org_email='.$org_email.'&sponsor_name='.$sponsor_name.'&sponsor_email='.$sponsor_email.'&event_name='.$event_name.'&event_date='.$event_date.'" target="_blank">Confirm Prepare Event</a>';
			$message .='</body></html>';

			// To send HTML mail, the Content-type header must be set
			$headers  = 'MIME-Version: 1.0' . "\r\n";
			$headers .= 'Content-type: text/html; charset=iso-8859-1'."\r\n";
			$headers .= "From: www.chulant.com"."\r\n";

			// Mail it
			mail($to, $subject, $message, $headers);
		}*/

		unset_billing_info();
		
		header($redirect_url);
		die();
	}
}
header($fail_redirect_url);
die();
?>