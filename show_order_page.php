<?php
session_start();
include "connection.php";
include 'custom_function.php';

//show orders to sponsor: month, sponsorname
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_SESSION['is_org']) && !$_SESSION['is_org'])
{
	//sponsor name
	$sponsor_name=$_SESSION['sponsor_fname'];
	$sponsor_email=$_SESSION['email'];

	//get year and month
	$date_value=$_GET['day'];
	$date_value=str_replace(',', ' ', $date_value);
	
	$dd= new DateTime($date_value);
	$month= $dd->format('m');
	$year= $dd->format('Y');
	
	$query="select cancel, event_date, event_name, org_name, is_full, spon_half, package_name, anonymous from eli_spon_data ";
	$query=$query."where sponsor_email='$sponsor_email' and MONTH(event_date) = '$month' and YEAR(event_date) = '$year' and is_full<>'-1'";
	
	$contents="<table class='apc_table'><thead><tr><td>STATUS</td><td>DATE</td><td>EVENT NAME</td><td>ORGANISATION</td><td>AMOUNT PAID</td><td>TYPE</td><td>ANON</td><td>PKG</td></tr></thead><tbody>";

	//get spon data
	if(isset($_GET['cancel']) && $_GET['cancel'] == 1)
	{
		//get cancelled orders
		$query=$query." and cancel = '1'";

	}else if(isset($_GET['paid']) && $_GET['paid'] == 1)
	{
		//get paid orders
		$query=$query." and cancel = '0'";

	}
	//<tr><td>Cancelled</td>
	//<td>10 Oct 2015</td>
	//<td>Mendy's Gala</td>
	//<td>Globa Expo Co</td>
	//<td>$240.00</td>
	//<td>Half</td>
	//<td>Yes</td>
	//<td>pkg_1</td>
	//<td><input type="button" class="status_btn expire" value="Expire"></td>
	//</tr>

	if($result=mysqli_query($conn, $query))
	{
		$pack_data=array();
		//$pack_data[org_name][pack_name]=price

		while($row=mysqli_fetch_assoc($result))
		{
			//Cancelled/Paid
			if($row['cancel'] == '1')
			{
				$cancel="Cancelled";
			}else{
				$cancel="Paid";
			}

			//03 Feb 2016
			$event_date = new DateTime($row['event_date']);
			$day=$event_date->format('d');
			$month=$event_date->format('M');
			$year=$event_date->format('Y');

			$event_date=$day." ".$month." ".$year;

			$event_name=$row['event_name'];
			$org_name=$row['org_name'];

			$anony=($row['anonymous'])?"Yes":"No";
			$type=($row['is_full'])?"Full":"Half";

			$pack_name=$row['package_name'];
			if(isset($pack_data[$org_name][$pack_name]))
			{
				$price=$pack_data[$org_name][$pack_name] * $row['spon_half'];
			}else{
				//get org pack price
				$query="select sum(item_total) from eli_org_package where org_name='$org_name' and package_name='$pack_name'" ;
				if($re_price=mysqli_query($conn, $query))
				{
					$row1=mysqli_fetch_row($re_price);
					$price=$row1[0];
					$pack_data[$org_name][$pack_name]=$price;
					$price=$price*$row['spon_half'];
					mysqli_free_result($re_price);
				}
			}
			$contents.="<tr><td>$cancel</td><td>$event_date</td><td>$event_name</td><td>$org_name</td><td>$$price</td><td>$type</td><td>$anony</td><td>$pack_name</td></tr>";		}
		mysqli_free_result($result);
	}
	$contents.="</tbody></table>";
	echo $contents;
}

?>
