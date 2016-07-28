<?php
session_start();
include "connection.php";
include 'custom_function.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
	//whether event is full or half
		if(isset($_POST['spon_radio']) && $_POST['spon_radio'] == 'full')
		 $is_full=1;
		else
		 $is_full=0;
		$_SESSION['select_is_full']=$is_full;

		if(!empty($_POST['honor_txt']))
		{
			$_SESSION['honor_txt']=$_POST['honor_txt'];
		}
		
		//package name
		//$_SESSION['package_name'];

		//anonymouse
		if($_POST['anony_check'])
			$_SESSION['select_anony']=1;
		else
			$_SESSION['select_anony']=0;

	if(isset($_SESSION['is_org'])&& !$_SESSION['is_org'] && isset($_SESSION['email']))
	{
		//get sponsor info
		//sponsor_name, sponsor_email, org_name, org_email
		//pack_name, full/half, anonymous, comment 

		//sponsor info: $_SESSION['email']
		//org info: $_SESSION['select_org']
		//event info: $_SESSION['select_event_name'], $_SESSION['select_event_date']

		
		//check whether this sponsor has credit card info
		if(has_credit_info())
		{
			//this is logined user or remembered user
			$redirect_url="Location: ".HTML_PATH."Invoice";			
		}else
		{
			$_SESSION['err_msg']="You need to complete the bank account info and credit card info in your settings.";
			$redirect_url="Location: ".HTML_PATH."ind_setting";
			$_SESSION['need_bank_info']=1;
		
		}
	}
	else
	{
		
		//he needs signup or logined
		$redirect_url="Location: ".HTML_PATH."Need_login_signup";

		//save_temp
		//save his data for unlogged in user
		$_SESSION['temp']=1;

	}
	
	header($redirect_url);
	die();
}

?>