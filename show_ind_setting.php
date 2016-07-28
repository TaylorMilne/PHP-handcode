<?php
session_start();
include 'connection.php';
include 'custom_function.php';
//show ind settings
/*
  this user is individual user who has loged in or signup.
  now $_SESSION['email'] & $_SESSION['is_org']=false
  
  show inform: 
	 1-- credit card number, cardholder's name, billing address, cv code, expire date
	 2(change display) -- org user photo, org user name, 
	 3(change password) -- old password(******)
	 4(change email) --?
	 	 
  add fields to ind table:
     ind user photo, credit card number, cardholder, billing address, cv code, expire date.
*/
      
  $redirect_url="Location: ".HTML_PATH."ind_setting";
 
  $err_msg="";
  $email=$_SESSION['email'];
  
  //get ind user data
  $user_name=$photo="";
  
  //credit_card_number, card holder name, billing address, cv code, expire_date
  $credit_card_number=$card_holder=$billing_address=$cv_code=$expire_date="";
   
  
  $query="select username, photo, phone_number, bank_account_email, credit_card_number, card_holder, billing_address, cv_code, expire_date from eli_user_data where email='$email'";
  
    
  if(!$conn)
	  echo "conn is null";
  if(!$result=mysqli_query($conn, $query))
  {
	$err_msg="Get information of the Sponsor failed. Please try again later.";
	$_SESSION['err_msg']=$err_msg;
  }
  else
  {
	if(!mysqli_num_rows($result)==1)
	{
		echo mysqli_error($conn);
	}
	
	$row=mysqli_fetch_assoc($result);
	$user_name=$row['username'];
	$phone=$row['phone_number'];
	$photo=$row['photo'];
	$bank_account_email=$row['bank_account_email'];

	$credit_card_number=encrypt_decrypt(2, $row['credit_card_number']);
	$card_holder=$row['card_holder'];
	$billing_address=$row['billing_address'];
	$cv_code=$row['cv_code'];
	$expire_date=$row['expire_date'];
	$expire_date=split("-", $expire_date);
	$expire_year=$expire_date[0];
	$expire_month=$expire_date[1];
	
	mysqli_free_result($result);
  }
  
?>