<?php
session_start();
include 'custom_function.php';
include 'connection.php';
//make event from input text in right_event_div
//org_name, org_email, event_name, event_date, is_full=-1: default;
$redirect_url="Location: ".HTML_PATH."organization";
if(($_SERVER["REQUEST_METHOD"] == "POST") && ($_SESSION['is_org'])){
	
	$action=$_POST['action'];
	if($action=='make_event')
	{
		$event_name=test_input($_POST['event_name']);
		$org_name=test_input($_SESSION['org_name']);
		$org_email=test_input($_SESSION['email']);
		$event_date=test_input($_POST['event_date']);

		if(!has_org_credit_info())
		{
		   $_SESSION['err_msg']="You need to complete the bank account info  and credit card info to make event in your setting.";
		   $_SESSION['need_org_bank_info']=1;
		   $redirect_url="Location: ".HTML_PATH."org_setting";
		   header($redirect_url);
		   die();
		}
		   
		$query="insert eli_spon_data(org_name, org_email, event_name, event_date) values('$org_name', '$org_email', '$event_name', '$event_date')";
		mysqli_query($conn, $query);
	}
}

header($redirect_url);
die();
?>