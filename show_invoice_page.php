<?php
session_start();
include "connection.php";
include 'custom_function.php';

//show sponsor photo, name, email
//show org photo, org_name, addr, tel
//show event_name, event_date
//show package item table
//show subtotal,tax, total

//get sponsor info
//checkout
if(isset($_SESSION['email']) && !$_SESSION['is_org'])
{
	$sponsor_email=$_SESSION['email'];
	$query="select username, photo from eli_user_data where email='$sponsor_email'";
	if(!$result=mysqli_query($conn, $query))
	{
		$err_msg="Get information of user failed. Please try again later.";
		$_SESSION['err_msg']=$err_msg;
		mysqli_free_result($result);
	}else{
		$row=mysqli_fetch_assoc($result);
		$sponsor_photo=$row['photo'];
		$sponsor_name=$row['username'];
		mysqli_free_result($result);
	}
	
	$org_name=$_SESSION['select_org'];

	$query="select email, phone_number, address, photo from eli_org_data where org_name='$org_name'";
	  if(!$result=mysqli_query($conn, $query))
	  {
		$err_msg="Get information of the organisation failed. Please try again later.";
		$_SESSION['err_msg']=$err_msg;
		mysqli_free_result($result);
	  }
	  else
	  {
		$row=mysqli_fetch_assoc($result);
		$org_phone=$row['phone_number'];
		$org_address=$row['address'];
		$org_photo=$row['photo'];
		mysqli_free_result($result);
	  }

 	//get package item table
	 $package_name=$_SESSION['package_name'];
	 $query="SELECT item_count, item_desc, item_price, item_total FROM eli_org_package WHERE org_name = '$org_name' and package_name='$package_name'";

	$result = mysqli_query($conn,$query);

	$subtotal=$total=0;

	$table_data="<table><thead><td width='10%'>QTY</td><td width='60%'>DESCRIPTION</td><td width='10%'>PRICE</td></thead><tbody>";

	if($result)
	{
		while($row = mysqli_fetch_array($result)) {
			$table_data=$table_data."<tr>
			<td>".$row['item_count']."</td>
			<td>".$row['item_desc']."</td>
			<td>$".$row['item_price']."</td></tr>";
			$subtotal+=intval($row['item_total']);
		}
		mysqli_free_result($result);
	}

	if(isset($_SESSION['select_is_full']) && !$_SESSION['select_is_full'])
		$subtotal/=2;

	if($subtotal!=0)
	{
		$table_data=$table_data."</tbody></table>";
		$total=round($subtotal*1.029+0.3, 2);
		$_SESSION['billing_amount']=$subtotal;
		$_SESSION['total']=$total;
		$tax=$total-$subtotal;
	}else
	{
		$total=0;
		$tax=0;
	}
}

?>