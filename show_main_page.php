<?php
session_start();
include 'connection.php';
//show selected org info
/*
  this user is sponsor user who has loged in or signup.
  now $_SESSION['email'] & $_SESSION['is_org']=false
  $_SESSION['select_org']=current selected org name
  
  show inform: 
	 org name, phone number, about me. email addr, org photo
*/
      
  
  $err_msg="";
  $spon_email=$_SESSION['email'];
  
  //get org common data
  $org_name=$_SESSION['select_org'];

  $phone=$about=$photo="";
  
  $query="select email, photo, phone_number, about from eli_org_data where org_name='$org_name'";
  if(!$result=mysqli_query($conn, $query))
  {
	$err_msg="Get information of the organisation failed. Please try again later.";
	$_SESSION['err_msg']=$err_msg;
	mysqli_free_result($result);
  }
  else
  {
	$row=mysqli_fetch_assoc($result);
 	$org_email=$row['email'];
  $_SESSION['select_org_email']=$org_email;
	$phone=$row['phone_number'];
 	$about=$row['about'];
	$photo=$row['photo'];
	mysqli_free_result($result);
  }

?>