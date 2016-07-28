<?php
session_start();
include "connection.php";
include 'custom_function.php';
//get_half_sponsored_package from date, org_name

$event_date=$_GET['event_date'];
$org_name=$_SESSION['select_org'];

$query="select package_name from eli_spon_data where org_name='$org_name' and event_date='$event_date' and is_full='0' and spon_half='0.5' and cancel='0'";
if($result=mysqli_query($conn, $query))
{
	$row=mysqli_fetch_assoc($result);
	echo $row['package_name'];
	mysqli_free_result($result);
}
die();
?>