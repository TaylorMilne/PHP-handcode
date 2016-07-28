<?php
session_start();
include 'connection.php';
include 'custom_function.php';

//checkout as guest

//2. payment
//3. save paydata and guest info as sponsor

//now current session info
/*
 [“select_org”]=> string(9) “Pizza Org” 
 [“err_msg”]=> string(0) “” 
 [“select_org_email”]=> string(21) “wibdaniel@outlook.com” 
 [“select_event_name”]=> string(12) “Feb_22_event” 
 [“select_event_day”]=> string(10) “2016-02-22” 
 [“select_event_day_exp”]=> string(17) “22, February,2016” 
 [“already”]=> bool(false) 
 [“package_name”]=> string(8) “package1” 
 [“select_is_full”]=> int(0)
 [“select_anony”]=> int(1) 
 */

$fail_redirect_url="Location: ".HTML_PATH."checkout";
$redirect_url="Location: ".HTML_PATH."main";
$suc_redirect_url="Location: ".HTML_PATH."invoice_next";
$login_redirect_url="Location: ".HTML_PATH."login";

if($_SERVER["REQUEST_METHOD"] == "POST" && $_POST['action']=='checkout' )
{
	//1. get credit and bank info of guest and validate
	if(isset($_SESSION['temp']))
	{
		unset($_SESSION['temp']);
    } 

	$_SESSION['checkout']=1;

	$guest_email=test_input($_POST['guest_email']);
	$_SESSION['guest_email']=$guest_email;


	//check guest email exist in eli_user_data
	$check_exist=check_email_exist_only($guest_email);

	if($check_exist == 1)
	{
		//this is sponsor user, inform please login
		$_SESSION['err_msg']="You are signed up as sponsor. Please login.";
		unset_billing_info();
		unset_checkout_session_var();
		header($login_redirect_url);
		die();

	} else if($check_exist == 2)
	{
		//this is sponsor user, inform please login
		$_SESSION['err_msg']="You are signed up as organization user. Please login.";
		unset_billing_info();
		unset_checkout_session_var();
		header($login_redirect_url);
		die();
	} else if($check_exist == -1)
	{
		//check failed. try again later
		$_SESSION['err_msg']="Sorry, we can't check your email. Please try again later";
		unset_billing_info();
		unset_checkout_session_var();
		header($redirect_url);
		die();
	}
	//check_exist=0, 3
	$guest_bank_email=test_input($_POST['guest_bank_email']);
	$_SESSION['guest_bank_email']=$guest_bank_email;

	$credit_card_number=test_input($_POST['credit_card_number']);
	$_SESSION['credit_card_number']=$credit_card_number;

	$card_holder=test_input($_POST['card_holder']);
	$_SESSION['card_holder']=$card_holder;

	$billing_addr=test_input($_POST['billing_addr']);
	$_SESSION['billing_addr']=$billing_addr;

	$cv_code=test_input($_POST['cv_code']);
	$_SESSION['cv_code']=$cv_code;

	$expire_month=test_input($_POST['expire_month']);
	$_SESSION['expire_month']=test_input($_POST['expire_month']);

	$expire_year=test_input($_POST['expire_year']);
	$_SESSION['expire_year']=$expire_year;


	$org_email=$_SESSION['select_org_email'];
	$org_name=$_SESSION['select_org'];
	$event_name=$_SESSION['select_event_name'];
	$event_date=$_SESSION['select_event_day'];
	$is_full=$_SESSION['select_is_full'];
	$anony=$_SESSION['select_anony'];
	$package_name=$_SESSION['package_name'];

	//check email
	$guest_email = filter_var($guest_email, FILTER_SANITIZE_EMAIL);
	if(!filter_var($guest_email, FILTER_VALIDATE_EMAIL))
	{
		$_SESSION['err_msg']="Your email format is invalid.";
		header($fail_redirect_url);
		die();
	}
	$guest_bank_email = filter_var($guest_bank_email, FILTER_SANITIZE_EMAIL);
	if(!filter_var($guest_bank_email, FILTER_VALIDATE_EMAIL))
	{
		$_SESSION['err_msg']="Your bank email format is invalid.";
		header($fail_redirect_url);
		die();
	}

	$card_type=check_cc($credit_card_number);
	if(!$card_type)
	{
		$_SESSION['err_msg']="Your credit card number is invalid.";
		header($fail_redirect_url);
		die();
	}
	$_SESSION['card_type']=$card_type;

	//2. get billing amount
	$query="SELECT item_count, item_desc, item_price, item_total, caterer_email FROM eli_org_package WHERE org_name = '$org_name' and package_name='$package_name'";
	
	$caterer_email="";
	$subtotal=$total=0;
	
	if($result = mysqli_query($conn, $query))
	{
		while($row = mysqli_fetch_array($result)) {
			$table_data="<tr>
			<td>".$row['item_count']."</td>
			<td>".$row['item_desc']."</td>
			<td>$".$row['item_price']."</td>
			</tr>";
			$subtotal += floatval($row['item_total']);
			if(empty($caterer_email))
			{
				$caterer_email=$row['caterer_email'];
			}
		}
		mysqli_free_result($result);
	}else
	{
		$_SESSION['err_msg']="Get organization package info failed. Please try again later.";
	    header($fail_redirect_url);
		die();
	}

	if(isset($_SESSION['select_is_full']))
	{
		if(!$_SESSION['select_is_full'])
		{
			$subtotal/=2;
			$half="HALF";	
		}else
		{
			$half="FULL";	
		}
	}	   

	if($subtotal!=0)
	{
		$total=round(1.029*$subtotal+0.3);
		$service_fee=$total-$subtotal;
		$table_data=$table_data."</tbody></table>";
		
		$_SESSION['billing_amount']=$subtotal;
		$_SESSION['total']=$total;
	}else
	{
		$_SESSION['err_msg']="Organization package info is invalid. Please try again after updated the package info.";
		header($fail_redirect_url);
		die();
	}
	
	//3. get paid and send pay
	//process payment (Get and Send)
	
	$ids=require (__DIR__).'/payment/mypay/CheckOutPay.php';
	if(!$ids)
	{
		header($fail_redirect_url);
		die();
	}
	
	//4. save guest info in eli_user_data and sponsorship info in eli_spon_data
	
	//save spon data
	$get_tr_id=$ids[0];
	$send_amount=$subtotal-5;
	$spon_half=($_SESSION['select_is_full'] == 1)? 1 : 0.5;
	$is_full=($_SESSION['select_is_full'] == 1)? 1 : 0;
	$is_already=($_SESSION['already']);
	if($is_already)
	{
		//check whether this is same user
		//insert/update

		//update half, insert half
		$query="update eli_spon_data set is_full='1' where org_name='$org_name' and org_email='$org_email' and event_name='$event_name' and event_date='$event_date'";
		mysqli_query($conn, $query);
		
		//insert
		$query="insert eli_spon_data(org_name, org_email, event_name, event_date, is_full, spon_half, package_name, sponsor_name, sponsor_email, anonymous, get_tr_id, send_amount) ";
		$query=$query."values('$org_name', '$org_email', '$event_name', '$event_date', '1', '0.5', '$package_name', 'GUEST', '$guest_email', '$anony', '$get_tr_id', '$send_amount')";
		mysqli_query($conn, $query);
	}else
	{
		$query="update eli_spon_data set is_full='$is_full', spon_half='$spon_half', package_name='$package_name', sponsor_name='GUEST', sponsor_email='$sponsor_email', anonymous='$anony', get_tr_id='$get_tr_id', send_amount='$send_amount' ";
		$query=$query."where org_name='$org_name' and org_email='$org_email' and event_name='$event_name' and event_date='$event_date'";
		mysqli_query($conn, $query);

	}

	$expire_date=$expire_year."-".$expire_month;
	$expire_date=date("Y-m", strtotime($expire_date));

	$credit_card_number=encrypt_decrypt(1, $credit_card_number);
	
	//save guest's data to sponsor table
	//check if guest_email exists in eli_user_data then update
	if($check_exist ==3)
	{
		$query="update eli_user_data set billing_address='$billing_addr', card_holder='$card_holder', credit_card_number='$credit_card_number', cv_code='$cv_code', expire_date='$expire_date', bank_account_email='$guest_bank_email' where email='$guest_email'";
		mysqli_query($conn, $query);
	} else {
		//insert
		$query="insert into eli_user_data(username, email, bank_account_email, billing_address, card_holder, credit_card_number, cv_code, expire_date)";
		$query .= " values('GUEST', '$guest_email', '$guest_bank_email', '$billing_addr', '$card_holder', '$credit_card_number', '$cv_code', '$expire_date')";
		mysqli_query($conn, $query);		
	}
		
	

	//5. mail send to cater_email, guest
	
	//now let's send email to cater, org_user
	$table_data1="<table><thead><td width='10%'>QTY</td><td width='50%'>ITEMS</td></thead><tbody>";

	$table_data=$table_data1.$table_data."</tbody></table>";
	
	//contents
	$contents ="<br>".$table_data;
	
	//send email to org user

	$to  = $_SESSION['select_org_email']; // note the comma
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
	$message .= '<p>type:'.$half.'</p></body></html>';
	$message .= '<p>Sponsored amount: $'.$send_amount.'<p>';
	

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
		$subject='Please confirm the '.$org_name.' event.';
		$message = '
		<html>
		<head>
	  	<title>The event '.$event_name.' has been sponsored for '.$org_name.'.</title>
		</head>
		<body>';
		$message .= '<p>The event '.$event_name.' has been sponsored for '.$org_name.' on '.$event_date.'.</p>';
		$message .='<p>You should prepair the party.</p>';
		$message .='<P>Package Item list as follows:</p>'.$contents;
		$message .='<p>please confirm this event by clicking the confirm link below.</p>';
		$message .='<a href="'.PHP_PATH.'/send_confirm_email.php?action=confirm_prepare_event&org_name='.$org_name.'&org_email='.$org_email.'&sponsor_name=GUEST&sponsor_email='.$sponsor_email.'&event_name='.$event_name.'&event_date='.$event_date.'" target="_blank">Confirm Order</a>';
		$message .='</body></html>';

		// To send HTML mail, the Content-type header must be set
		$headers  = 'MIME-Version: 1.0' . "\r\n";
		$headers .= 'Content-type: text/html; charset=iso-8859-1'."\r\n";
		$headers .= "From: www.chulant.com"."\r\n";

		// Mail it
		mail($to, $subject, $message, $headers);
	}
	

	//all is success
	$_SESSION['err_msg']="Payment Success. Thank you for your sponsorship.";
	unset_billing_info();
	unset_checkout_session_var();

	header($suc_redirect_url);
	die();

} else
{
	header($fail_redirect_url);
	die();
}
?>