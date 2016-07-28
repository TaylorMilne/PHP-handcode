<?php
session_start();
include 'custom_function.php';
include 'connection.php';
include 'verify_credit_card.php';

$redirect_url="Location: ".HTML_PATH."org_setting";
//change org setting php
$err_msg="";
$now_email=$_SESSION['email'];


if($_SERVER["REQUEST_METHOD"] == "POST" && $_SESSION['is_org'] ){
	$action=$_POST['action'];
	if($action =='change_common_org'){
		//change the common data about org
		$org_name=test_input($_POST['org_name']);
		$phone=test_input($_POST['org_phone']);
		$about=test_input($_POST['org_about']);
		$desc_email = test_input($_POST['org_desc_email']);
		// check if e-mail address is well-formed
		$desc_email = filter_var($desc_email, FILTER_SANITIZE_EMAIL);
		$oi_name=$_FILES['upload_photo']['tmp_name'];
		if(empty($oi_name))
		{
		  change_org_common_data_without_photo($org_name, $desc_email, $phone, $about);
		}else{
			$image=addslashes(file_get_contents($oi_name));
			change_org_common_data($org_name, $desc_email, $phone, $about, $image);
		}
	}else if ($action== 'change_org_user'){
		//change the org user
		$org_user_name = $_POST['org_user_name'];
		$oui_name=$_FILES['upload_user_photo']['tmp_name'];
		if(empty($oui_name))
		{
			change_org_user_data_name($org_user_name);
		}else{
			$org_user_photo = addslashes(file_get_contents($oui_name));
			change_org_user_data($org_user_name, $org_user_photo);	
		}
	}else if($action == 'change_org_password'){
		$old_p = test_input($_POST['old_pwd']);
		$new_p = test_input($_POST['new_pwd']);
		change_org_password($old_p, $new_p);
	}else if($action == 'change_org_email'){
		$new_email=test_input($_POST['new_email']);
		
		$new_email = filter_var($new_email, FILTER_SANITIZE_EMAIL);
		if(!filter_var($new_email, FILTER_VALIDATE_EMAIL))
		{
			$err_msg="Email format is invalid";
		}else {
			if(change_org_email($new_email))
				$now_email=$new_email;
		}
	}else if($action == 'change_pay_org'){
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
			change_org_pay($bank_account_email, $credit_card_number, $card_holder, $billing_addr, $cv_code, $expire_date);

			if(has_org_credit_info() && isset($_SESSION['need_org_bank_info']))
			{
				unset($_SESSION['need_org_bank_info']);
				$redirect_url="Location: ".HTML_PATH."organization";
			}	
		}else{
			$err_msg="Credit card number is not correct.";
		}
	}
		
	$_SESSION['email']=$now_email;
	$_SESSION['err_msg']=$err_msg;
	header($redirect_url);
	die();
	var_dump($_SESSION);
	
}else
{
	header($redirect_url);
	die();
}


function change_org_common_data_without_photo($org_name, $desc_email, $phone, $about)
{
	global $conn, $err_msg, $now_email;
	
	$result=true;

	$query="update eli_org_data set org_name='$org_name', desc_email='$desc_email', phone_number='$phone', about='$about' where email='$now_email'";
	
	if(!mysqli_query($conn, $query))
	{
		$result=false;
		$err_msg="Update Failed: ".mysqli_error($conn);
		return;
	}
	
	//sponsor_table, org_package

	$query="update eli_org_package set org_name='$org_name' where org_email='$now_email'";
	if(!mysqli_query($conn, $query))
	{
		$result=false;
		$err_msg="Update Failed: ".mysqli_error($conn);
		return;
	}
	
	
	$query="update eli_spon_data set org_name='$org_name' where org_email='$now_email'";
	if(!mysqli_query($conn, $query))
	{
		$result=false;
		$err_msg="Update Failed: ".mysqli_error($conn);
		return;
	}
	
	if($result)
	{
		$_SESSION['org_name']=$org_name;
	}

	return $result;
}

function change_org_common_data($org_name, $desc_email, $phone, $about, $image)
{
	global $conn, $err_msg, $now_email;
	
	$query="update eli_org_data set org_name='$org_name', desc_email='$desc_email', phone_number='$phone', about='$about', photo='$image' where email='$now_email'";

	$result=true;
	
	if(!mysqli_query($conn, $query))
	{
		$result=false;
		$err_msg="Update Failed: ".mysqli_error($conn);
		return;
	}
	
	//sponsor_table, org_package

	$query="update eli_org_package set org_name='$org_name' where org_email='$now_email'";
	if(!mysqli_query($conn, $query))
	{
		$result=false;
		$err_msg="Update Failed: ".mysqli_error($conn);
		return;
	}
	
	$query="update eli_spon_data set org_name='$org_name' where org_email='$now_email'";
	if(!mysqli_query($conn, $query))
	{
		$result=false;
		$err_msg="Update Failed: ".mysqli_error($conn);
		return;
	}

	if($result)
		$_SESSION['org_name']=$org_name;

	return $result;
}

function change_org_user_data_name($org_user_name)
{
	global $conn, $err_msg, $now_email;
	$query="update eli_org_data set org_user_name='$org_user_name' where email='$now_email'";
	
	if(mysqli_query($conn, $query))
	{
		$err_msg="Update Success.";
		$_SESSION['org_user_name']=$org_user_name;
	}else{
		$err_msg='Update Failed: '.mysqli_error($conn);
	}
	return;
}

function change_org_user_data($org_user_name, $org_user_photo)
{
	global $conn, $err_msg, $now_email;
	$query="update eli_org_data set org_user_name='$org_user_name', org_user_photo='$org_user_photo' where email='$now_email'";
	
	if(mysqli_query($conn, $query))
	{
		$err_msg="Update Success.";
		$_SESSION['org_user_name']=$org_user_name;
	}else{
		$err_msg="Update Failed: ".mysqli_error($conn);
	}
	return;
}

function change_org_password($old_p, $new_p)
{
	global $conn, $err_msg, $now_email;
	
	//check old password
	$query="select password from eli_org_data where email='$now_email'";
	
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
	$query="update eli_org_data set password='$new_p' where email='$now_email'";
	if(!mysqli_query($conn, $query))
		$err_msg='Update Failed: '.mysqli_error($conn);
	else
		$err_msg="Update Success.";
	return;
}

function change_org_email($new_email){
	global $conn, $now_email, $err_msg;
	
	$new_email=mysqli_real_escape_string($conn, $new_email);
	
	$query="update eli_org_data set email='$new_email' where email='$now_email'";
	$result=true;
	
	if(!mysqli_query($conn, $query))
		$result=false;
	
	//sponsor_table, org_package

	$query="update eli_org_package set org_email='$new_email' where org_email='$now_email'";
	if(!mysqli_query($conn, $query))
		$result=false;
	
	
	$query="update eli_spon_data set org_email='$new_email' where org_email='$now_email'";
	if(!mysqli_query($conn, $query))
		$result=false;

	return $result;

}

function change_org_pay($bank_account_email, $credit_card_number, $card_holder, $billing_addr, $cv_code, $expire_date)
{
  global $conn, $now_email;

  //card encryption
  $credit_card_number=encrypt_decrypt(1, $credit_card_number);

  $query="update eli_org_data set bank_account_email='$bank_account_email', credit_card_number='$credit_card_number', card_holder='$card_holder', billing_address='$billing_addr', cv_code='$cv_code', expire_date='$expire_date' where email='$now_email'";
  
  if(!mysqli_query($conn, $query))
  {
	$err_msg="Update Failed: ".mysqli_error($conn);
  }else{
	$err_msg="Update Success.";
  }
    
  return;
}

?>