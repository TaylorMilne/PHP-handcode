<?php
session_start();
include 'connection.php';
$redirect_url="Location: ".HTML_PATH."HOME";
$_SESSION['logout']=1;
header($redirect_url);
die();
?>