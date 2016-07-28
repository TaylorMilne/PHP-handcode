<?php 
session_start();
include "connection.php";
include 'custom_function.php';

$package_name=$_SESSION['package_name'];
$email=$_SESSION['email'];
$org_name=$_SESSION['org_name'];

$redirect_url="Location: ".HTML_PATH."organization";

if($_POST['action']=="update_package"){
	    //save cater email first
	    $caterer_email="";
		$query="select caterer_email from eli_org_package where org_email='$email' and package_name='$package_name'";
		if($result=mysqli_query($conn, $query))
		{
			$row=mysqli_fetch_assoc($result);
			if(!empty($row['caterer_email']))
			{
				$caterer_email=$row['caterer_email'];
			}
		}
		//delete package info
		$query="delete from eli_org_package where org_email='$email' and package_name='$package_name'";
		if(mysqli_query($conn, $query))
		{
			//insert new package info
			foreach ($_POST['item_count'] as $key => $value) 
			{
				$count = $_POST["item_count"][$key];
				$desc = $_POST["item_desc"][$key];
				$price = $_POST["item_price"][$key];
				$item_total=$count*$price;
								
				$query="insert into eli_org_package(package_name, item_count, item_desc, item_price, item_total, org_name, org_email, caterer_email) values ('$package_name', '$count', '$desc', '$price', '$item_total', '$org_name', '$email', '$caterer_email')";
				if(!mysqli_query($conn, $query))
				{
					$_SESSION['err_msg']=mysqli_error($conn);
					break;
				}
			}
     	}
}
 header($redirect_url);
 die();
?>