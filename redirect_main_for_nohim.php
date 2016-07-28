<?php
session_start();
include 'connection.php';
include 'custom_function.php';
//when sponsor user click the "Not mendy"button.
//log out current user and goto main page
//delete email and goto main page
if(isset($_SESSION['email']))
{
	echo "<script>var target_menu= document.getElementById('menu-item-1416');                       
       target_menu.childNodes[0].innerHTML='CREATE | LOGIN';</script>";

    not_mendy_session_unset();
}
var_dump($_SESSION);

$redirect_url="Location: ".HTML_PATH."main";
header($redirect_url);
die();
?>