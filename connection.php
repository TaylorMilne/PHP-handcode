<?php
define ("HTML_PATH", "http://".$_SERVER['HTTP_HOST']."/elisponsor/");
define ("PHP_PATH", "http://".$_SERVER['HTTP_HOST']."/elisponsor/wp-content/themes/elitheme/php/");
$user="root";
$host="localhost";
$db="elidb";
$password="";

$tax=20;

if(!isset($conn))
	$conn=mysqli_connect($host, $user, $password, $db);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
//mysqli_close($conn);
?>