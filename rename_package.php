<?php
session_start();
include "connection.php";
include 'custom_function.php';

$pk1 = test_input(strval($_GET['pk1']));
$pk1=mysqli_real_escape_string($conn, $pk1);

$pk2 = test_input(strval($_GET['pk2']));
$pk2=mysqli_real_escape_string($conn, $pk2);

$email=test_input($_SESSION['email']);
$email=mysqli_real_escape_string($conn, $email);

$query="update eli_org_package set package_name='$pk2' where org_email='$email' and package_name='$pk1' ";
mysqli_query($conn, $query);
die();
?>