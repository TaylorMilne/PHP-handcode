<?php
include 'show_org_packages.php';
//show finances data

if(!$conn)
	die("Database error");

$date_value=$_GET['day'];
$date_value=str_replace(',', ' ', $date_value);

$time=strtotime($date_value);

$month=date("m", $time);
$year=date("Y", $time);

$org_name=$_SESSION['org_name'];

//$pack_data

if(isset($_GET['week']) && $_GET['week'])
{
	//this shows weekly info
	//get full sponsored events and package price

	//get events num
	//count(event_date)=count(event_name)
		//but event_date is more correct to get unrepeating data

	// First/last day of week
	$date1=new DateTime($date_value);
	$date2=new DateTime($date_value);

	if($date1->format("w") == 0)
		$first=$date1;
	else
		$first = $date1->modify('last Sunday');
	$last = $date2->modify('next Saturday');

	
	$first =  $first->format("d");
	$last = $last->format("d");

	$full_event_num=0;
	$query="select count(DISTINCT event_date) from eli_spon_data where is_full='1' and org_name='$org_name' and MONTH(event_date)='$month' and YEAR(event_date) = '$year' and DAY(event_date) >= '$first' and DAY(event_date) <= '$last' and cancel='0'";
	if($result=mysqli_query($conn, $query))	
	{
		$row=mysqli_fetch_row($result);
		$full_event_num=$row[0];
		mysqli_free_result($result);
	}

	$half_event_num=0;
	$query="select count(DISTINCT event_date) from eli_spon_data where is_full='0' and org_name='$org_name' and MONTH(event_date)='$month' and YEAR(event_date) = '$year' and DAY(event_date) >= '$first' and DAY(event_date) <= '$last' and cancel='0'";
	if($result=mysqli_query($conn, $query))	
	{
		$row=mysqli_fetch_row($result);
		$half_event_num=$row[0];
		mysqli_free_result($result);
	}

	$none_event_num=0;
	$query="select count(DISTINCT event_date) from eli_spon_data where is_full='-1' and org_name='$org_name' and MONTH(event_date)='$month' and YEAR(event_date) = '$year' and DAY(event_date) >= '$first' and DAY(event_date) <= '$last' and cancel='0'";
	if($result=mysqli_query($conn, $query))	
	{
		$row=mysqli_fetch_row($result);
		$none_event_num=$row[0];
		mysqli_free_result($result);
	}

	echo "
	<p class='clearfix'>
	<div class='li full' >Full sponsored Events: <span class='lii'style='float:right'>$full_event_num</div>
	<div class='li half' >Half sponsored Events: <span class='lii'>$half_event_num</div>
	<div class='li none' >Not sponsored Events: <span class='lii'>$none_event_num</div>
	</p>
	";

	$total=$paid=0;
	//get event price
	$query="select is_full, spon_half, package_name from eli_spon_data where org_name='$org_name' and MONTH(event_date)='$month' and YEAR(event_date) = '$year' and is_full IN (0, 1) and DAY(event_date) >= '$first' and DAY(event_date) <= '$last' and cancel='0'";
	if($result=mysqli_query($conn, $query))
	{
		while($row=mysqli_fetch_assoc($result))
		{
			$price=$pack_data[$row['package_name']];
			if($row['is_full'] == 0)
			{
				//spon half=0.5
				$total+=$price;
				$paid+=$price/2;
			}
			else
			{
				if($row['spon_half'] == 1)
				{
					$total+=$price;
					$paid+=$price;		
				}
				else
				{
					$total+=$price/2;
					$paid+=$price/2;
				}
			}
		}
		mysqli_free_result($result);
	}

	echo "
	<input type='hidden' value='$total' id='total_price'>
	<input type='hidden' value='$paid' id='paid_price'>
	";

}else if(isset($_GET['month']) && $_GET['month'])
{
	//this shows monthly info
	
	//get full sponsored events and package price

	//get events num
	//count(event_date)=count(event_name)
		//but event_date is more correct to get unrepeating data
	$full_event_num=0;
	$query="select count(DISTINCT event_date) from eli_spon_data where is_full='1' and org_name='$org_name' and MONTH(event_date)='$month' and YEAR(event_date) = '$year' and cancel='0'";
	if($result=mysqli_query($conn, $query))	
	{
		$row=mysqli_fetch_row($result);
		$full_event_num=$row[0];
		mysqli_free_result($result);
	}

	$half_event_num=0;
	$query="select count(DISTINCT event_date) from eli_spon_data where is_full='0' and org_name='$org_name' and MONTH(event_date)='$month' and YEAR(event_date) = '$year' and cancel='0'";
	if($result=mysqli_query($conn, $query))	
	{
		$row=mysqli_fetch_row($result);
		$half_event_num=$row[0];
		mysqli_free_result($result);
	}

	$none_event_num=0;
	$query="select count(DISTINCT event_date) from eli_spon_data where is_full='-1' and org_name='$org_name' and MONTH(event_date)='$month' and YEAR(event_date) = '$year' and cancel='0'";
	if($result=mysqli_query($conn, $query))	
	{
		$row=mysqli_fetch_row($result);
		$none_event_num=$row[0];
		mysqli_free_result($result);
	}

	echo "
	<p class='clearfix'>
	<div class='li full' >Full sponsored Events: <span class='lii'style='float:right'>$full_event_num</div>
	<div class='li half' >Half sponsored Events: <span class='lii'>$half_event_num</div>
	<div class='li none' >Not sponsored Events: <span class='lii'>$none_event_num</div>
	</p>
	";
	
	$total=$paid=0;
	//get event price
	$query="select is_full, spon_half, package_name from eli_spon_data where org_name='$org_name' and MONTH(event_date)='$month' and YEAR(event_date) = '$year' and is_full IN (0, 1) and cancel='0'";
	if($result=mysqli_query($conn, $query))
	{
		while($row=mysqli_fetch_assoc($result))
		{
			$price=$pack_data[$row['package_name']];
			if($row['is_full'] == 0)
			{
				//spon half=0.5
				$total+=$price;
				$paid+=$price/2;
			}
			else
			{
				if($row['spon_half'] == 1)
				{
					$total+=$price;
					$paid+=$price;		
				}
				else
				{
					$total+=$price/2;
					$paid+=$price/2;
				}
			}
		}
		mysqli_free_result($result);
	}

	echo "
	<input type='hidden' value='$total' id='total_price'>
	<input type='hidden' value='$paid' id='paid_price'>
	";

} else {
	//this shows yearly info
	//this shows monthly info
	
	//get full sponsored events and package price

	//get events num
	//count(event_date)=count(event_name)
		//but event_date is more correct to get unrepeating data
	$full_event_num=0;
	$query="select count(DISTINCT event_date) from eli_spon_data where is_full='1' and org_name='$org_name' and YEAR(event_date) = '$year' and cancel='0'";
	if($result=mysqli_query($conn, $query))	
	{
		$row=mysqli_fetch_row($result);
		$full_event_num=$row[0];
		mysqli_free_result($result);
	}

	$half_event_num=0;
	$query="select count(DISTINCT event_date) from eli_spon_data where is_full='0' and org_name='$org_name' and YEAR(event_date) = '$year' and cancel='0'";
	if($result=mysqli_query($conn, $query))	
	{
		$row=mysqli_fetch_row($result);
		$half_event_num=$row[0];
		mysqli_free_result($result);
	}

	$none_event_num=0;
	$query="select count(DISTINCT event_date) from eli_spon_data where is_full='-1' and org_name='$org_name' and YEAR(event_date) = '$year' and cancel='0'";
	if($result=mysqli_query($conn, $query))	
	{
		$row=mysqli_fetch_row($result);
		$none_event_num=$row[0];
		mysqli_free_result($result);
	}

	echo "
	<p class='clearfix'>
	<div class='li full' >Full sponsored Events: <span class='lii'style='float:right'>$full_event_num</div>
	<div class='li half' >Half sponsored Events: <span class='lii'>$half_event_num</div>
	<div class='li none' >Not sponsored Events: <span class='lii'>$none_event_num</div>
	</p>
	";

	$total=$paid=0;
	//get event price
	$query="select is_full, spon_half, package_name from eli_spon_data where org_name='$org_name' and YEAR(event_date) = '$year' and is_full IN (0, 1) and cancel='0'";
	if($result=mysqli_query($conn, $query))
	{
		while($row=mysqli_fetch_assoc($result))
		{
			$price=$pack_data[$row['package_name']];
			if($row['is_full'] == 0)
			{
				//spon half=0.5
				$total+=$price;
				$paid+=$price/2;
			}
			else
			{
				if($row['spon_half'] == 1)
				{
					$total+=$price;
					$paid+=$price;		
				}
				else
				{
					$total+=$price/2;
					$paid+=$price/2;
				}
			}
		}
		mysqli_free_result($result);
	}

	echo "
	<input type='hidden' value='$total' id='total_price'>
	<input type='hidden' value='$paid' id='paid_price'>
	";
}

die();
?>