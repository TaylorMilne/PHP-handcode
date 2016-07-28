<?php
session_start();
include "connection.php";
include "custom_function.php";

//id='.$encrypt.'&is_org='.$is_org
//check id and goto reset page

$err_msg="";
$fail_redirect_url="Location: ".HTML_PATH."login";
$redirect_url="Location: ".HTML_PATH."reset_password";

$encrypt="";
$is_org="";

if(isset($_GET['encrypt']))
{
	$encrypt=$_GET['encrypt'];
	$is_org=$_GET['is_org'];
}else if(isset($_POST['encrypt']))
{
	$encrypt=$_POST['encrypt'];
	$is_org=$_POST['is_org'];
}

$encrypt = mysqli_real_escape_string($conn, $encrypt);

if(empty($encrypt) || empty($is_org))
{
	$_SESSION['err_msg']="Email ID got is empty.";
	header($fail_redirect_url);
	die();
}else
{
	//check id
	if($is_org == 1)
	{
		$query="select email from eli_org_data where  md5(1290*3+org_id)='".$encrypt."'";
	}else
	{
		$query="select email from eli_user_data where  md5(1290*3+id)='".$encrypt."'";
	}

	if($result=mysqli_query($conn, $query))
	{
		$row=mysqli_fetch_array($result);
		if(count($row))
		{
			$_SESSION['email']=$row['email'];
			mysqli_free_result($result);
			header($redirect_url);
			die();
		}
	}
	
	$_SESSION['err_msg']="Get your email failed. Please try again later.";
	header($fail_redirect_url);
	die();	
}

?>