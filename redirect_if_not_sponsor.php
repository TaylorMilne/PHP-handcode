<?php
session_start();
if((!isset($_SESSION['is_org']))|| (isset($_SESSION['is_org']) && $_SESSION['is_org']))
{
	$redirect_url="Location: ".HTML_PATH."HOME";
	header($redirect_url);
	die();
}else
{
	$redirect_url="Location: ".HTML_PATH."ORDERS";
	header($redirect_url);
	die();
}
?>