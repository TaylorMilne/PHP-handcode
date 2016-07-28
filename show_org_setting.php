<?php
session_start();
include 'connection.php';
include 'custom_function.php';
//show org settings
/*
  this user is organisation user who has loged in or signup.
  now $_SESSION['email'] & $_SESSION['is_org']=true
  
  show inform: 
	 1--org name, phone number, about me. email addr, org photo
	 2-- bank account email
	 4(change display) -- org user photo, org user name, 
	 5(change password) -- old password(******)
	 6(change email) --?
	 	 
  add fields to org table:
     org user photo, about
*/
      
  $redirect_url="Location: ".HTML_PATH."org_setting";
 
  $err_msg="";
  $email=$_SESSION['email'];
  //if(empty($email))
	//  $email="pizza@outlook.com";
  
  //get org common data
  $org_name=$phone=$about=$photo="";
  //get org user data
  $org_user_name=$org_user_photo="";
  
  //bank account email
  $bank_account_email="";
   
  $query="select org_name, photo, phone_number, about, desc_email, org_user_name, org_user_photo, bank_account_email, billing_address, card_holder, credit_card_number, cv_code, expire_date from eli_org_data where email='$email'";
  if(!$result=mysqli_query($conn, $query))
  {
	$err_msg="Get information of the organisation failed. Please try again later.";
	$_SESSION['err_msg']=$err_msg;
  }
  else
  {
	if(!mysqli_num_rows($result)==1)
	{
		echo mysqli_error($conn);
	}
	$row=mysqli_fetch_assoc($result);
	$org_name=$row['org_name'];
	$desc_email=$row['desc_email'];
	$phone=$row['phone_number'];
	$about=$row['about'];
	$photo=$row['photo'];
	$org_user_name=$row['org_user_name'];
	$org_user_photo=$row['org_user_photo'];
	$bank_account_email=$row['bank_account_email'];
	$billing_address=$row['billing_address'];

	$credit_card_number=encrypt_decrypt(2, $row['credit_card_number']);
	$len=strlen($credit_card_number);
	$e="xxxxxxxxxxxxxxx";
	$d=substr($credit_card_number, $len-4, 4);
	$credit_card_number=$e.$d;

	$card_holder=$row['card_holder'];
	$cv_code=$row['cv_code'];
	$expire_date=$row['expire_date'];
	$expire_date=split("-", $expire_date);
	$expire_year=$expire_date[0];
	$expire_month=$expire_date[1];
	mysqli_free_result($result);
  }
  
?>