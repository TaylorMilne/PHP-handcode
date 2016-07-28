<?php
session_start();
include 'custom_function.php';
include 'connection.php';
include 'verify_credit_card.php';

$redirect_url="Location: ".HTML_PATH."ind_setting";
//change org setting php
$err_msg="";
$now_email=$_SESSION['email'];


if($_SERVER["REQUEST_METHOD"] == "POST" && (!$_SESSION['is_org']) ) {
	$action=$_POST['action'];
	if ($action== 'change_ind_user'){
		//change the org user
		$user_name = $_POST['ind_user_name'];
		$oui_name=$_FILES['upload_user_photo']['tmp_name'];
		if(empty($oui_name))
		{
			change_ind_user_data_name($user_name);
		}else{
			$photo = addslashes(file_get_contents($oui_name));
			change_ind_user_data($user_name, $photo);	
		}
	}else if($action == 'change_ind_password'){
		$old_p = test_input($_POST['old_pwd']);
		$new_p = test_input($_POST['new_pwd']);
		change_ind_password($old_p, $new_p);
		
	}else if($action == 'change_ind_email'){
		$new_email=test_input($_POST['new_email']);
		
		$new_email = filter_var($new_email, FILTER_SANITIZE_EMAIL);
		if(!filter_var($new_email, FILTER_VALIDATE_EMAIL))
		{
			$err_msg="Email format is invalid";
		}else {
			if(change_ind_email($new_email))
				$now_email=$new_email;
		}
	} else if($action == 'change_ind_pay'){
		$bank_account_email=test_input($_POST['bank_account_email']);
		
		$credit_card_number=test_input($_POST['credit_card_number']);
		$card_holder=test_input($_POST['card_holder']);
		$billing_addr=test_input($_POST['billing_addr']);
		$cv_code=test_input($_POST['cv_code']);
		
		$expire_month=test_input($_POST['expire_month']);
		$expire_year=test_input($_POST['expire_year']);
		$expire_date=$expire_year."-".$expire_month;
				
		if(validate_creditcard($credit_card_number))
		{
			change_ind_credit($bank_account_email, $credit_card_number, $card_holder, $billing_addr, $cv_code, $expire_date);
			if(has_credit_info())
		{
			if(isset($_SESSION['temp']))
			{
				$redirect_url="Location: ".HTML_PATH."invoice";
				unset($_SESSION['temp']);
			}else if(isset($_SESSION['need_bank_info']) && !$_SESSION['is_org'])
			{
				$redirect_url="Location: ".HTML_PATH."invoice";
				unset($_SESSION['need_bank_info']);
			}
		}
		}else{
			$err_msg="Credit card number is not correct.";
		}
	}
		
	$_SESSION['email']=$now_email;
	$_SESSION['err_msg']=$err_msg;
}
header($redirect_url);
die();


function change_ind_user_data_name($user_name)
{
	global $conn, $err_msg, $now_email;
	$query="update eli_user_data set username='$user_name' where email='$now_email'";
	
	if(!mysqli_query($conn, $query))
	{
		$err_msg="Update Failed: ".mysqli_error($conn);
		return;
	}
	//change eli_spon_data
	$query="update eli_spon_data set sponsor_name='$user_name' where sponsor_email='$now_email'";
	if(!mysqli_query($conn, $query))
	{
		$err_msg="Update Failed: ".mysqli_error($conn);
		return;
	}
	
	$err_msg="Update Success.";
	$_SESSION['sponsor_fname']=$user_name;
	return;
}

function change_ind_user_data($user_name, $photo)
{
	global $conn, $err_msg, $now_email;
	$query="update eli_user_data set username='$user_name', photo='$photo' where email='$now_email'";
	
	if(!mysqli_query($conn, $query))
	{
		$err_msg="Update Failed: ".mysqli_error($conn);
		return;
	}
	//change eli_spon_data
	$query="update eli_spon_data set sponsor_name='$user_name' where sponsor_email='$now_email'";
	if(!mysqli_query($conn, $query))
	{
		$err_msg="Update Failed: ".mysqli_error($conn);
		return;
	}
	
	
	$err_msg="Update Success.";
	$_SESSION['sponsor_fname']=$user_name;
	return;
}




function change_ind_password($old_p, $new_p)
{
	global $conn, $err_msg, $now_email;
	
	//check old password
	$query="select password from eli_user_data where email='$now_email'";
	
	if(!$result=mysqli_query($conn, $query))
	{
		$err_msg='Update Failed: '.mysqli_error($conn);
		mysqli_free_result($result);
		return;
	}else{
		$row=mysqli_fetch_row($result);
		
		if(!$row[0] == encrypt_decrypt(1, $old_p)){
			$err_msg="Update Failed: Old Password is incorrect.";
			mysqli_free_result($result);
			return;
		}
		mysqli_free_result($result);
	}
	
	$new_p=mysqli_real_escape_string($conn, $new_p);
	$new_p=encrypt_decrypt(1, $new_p);
	//set new password
	$query="update eli_user_data set password='$new_p' where email='$now_email'";
	if(!mysqli_query($conn, $query))
		$err_msg='Update Failed: '.mysqli_error($conn);
	else
		$err_msg="Update Success.";
	return;
}

function change_ind_email($new_email){
	global $conn, $now_email, $err_msg;

	$new_email=mysqli_real_escape_string($conn, $new_email);
	
	$query="update eli_user_data set email='$new_email' where email='$now_email'";
	if(!mysqli_query($conn, $query))
	{
		$err_msg="Update Failed: ".mysqli_error($conn);
	    return false;
    }

    //change eli_spon_data
    $query="update eli_spon_data set sponsor_email='$new_email' where sponsor_email='$now_email'";
	if(!mysqli_query($conn, $query))
	{
		$err_msg="Update Failed: ".mysqli_error($conn);
	    return false;
    }

	$err_msg="Update Success.";
    return true;
}

function change_ind_credit($bank_account_email, $credit_card_number, $card_holder, $billing_addr, $cv_code, $expire_date)
{
  global $conn, $now_email, $err_msg;

  //card encryption
  $credit_card_number=encrypt_decrypt(1, $credit_card_number);

  $query="update eli_user_data set bank_account_email='$bank_account_email', credit_card_number='$credit_card_number', card_holder='$card_holder', billing_address='$billing_addr', cv_code='$cv_code', expire_date='$expire_date' where email='$now_email'";
  
  if(!mysqli_query($conn, $query))
  {
	$err_msg="Update Failed: ".mysqli_error($conn);
  }else{
	$err_msg="Update Success.";
  }
    
  return;
}

?>