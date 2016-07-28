<?php
session_start();
include "connection.php";
include 'custom_function.php';

$package_name = strval($_GET['package_name']);

$email=test_input($_SESSION['email']);
$email=mysqli_real_escape_string($conn, $email);

$query="delete from eli_org_package where org_email='$email' and package_name='$package_name' ";
mysqli_query($conn, $query);
die();
?>