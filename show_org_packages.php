<?php 
session_start();
include 'connection.php';
//show org packages list
//get org packages list from session[email, org_name]

$packages=[];

if(isset($_SESSION['is_org']) && $_SESSION['is_org'])
{
	$org_email=$_SESSION['email'];
	$org_name=$_SESSION['org_name'];

	//get package price
	$pack_data=array();
	//get organisation package names
	$query="select distinct package_name from eli_org_package where org_email='$org_email'";
	
}else{
	$org_name=$_SESSION['select_org'];
	$query="select distinct package_name from eli_org_package where org_name='$org_name'";
}

if($result=mysqli_query($conn, $query)){
		//fetch one row by one
		while($row=mysqli_fetch_row($result))
		{
			$pack_name=$row[0];
			array_push($packages, $pack_name);

			if(isset($_SESSION['is_org']) && $_SESSION['is_org'])
			{
				$query2="select sum(item_total) from eli_org_package where org_name='$org_name' and package_name='$pack_name'";
				if($result_price=mysqli_query($conn, $query2))
				{
					$row_price=mysqli_fetch_row($result_price);
					$pack_price=$row_price[0];
					mysqli_free_result($result_price);
				}
				$pack_data[$pack_name]=$pack_price;
			}
		}
		//free result
		mysqli_free_result($result);
	}
	
?>