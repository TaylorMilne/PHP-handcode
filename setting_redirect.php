<?php
session_start();
include "connection.php";

if (isset($_SESSION['is_org']))
{
	if($_SESSION['is_org'])
		$redirect_url="Location: ".HTML_PATH."org_setting";
	else
		$redirect_url="Location: ".HTML_PATH."ind_setting";
} else {
	$redirect_url="Location: ".HTML_PATH."HOME";
	$_SESSION['err_msg']="You did not login.";
}

  header($redirect_url);
  die();
?>