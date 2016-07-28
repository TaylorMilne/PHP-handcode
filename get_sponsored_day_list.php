<?php
session_start();
include 'custom_function.php';
include 'connection.php';

//you know the org_name
/*
$date_value=$_GET['day'];
$date_value=str_replace(',', ' ', $date_value);

$time=strtotime($date_value);

$month=date("m", $time);
$year=date("Y", $time);
*/

//get full sponsored day
$fullDaylist=array();
$halfDaylist=array();
$availDaylist=array();

if(isset($_SESSION['is_org']) && ($_SESSION['is_org'] == true)){
	//in org page
	$org_name=test_input($_SESSION['org_name']);
}else{
	//in main page
	//anyway show the calendar
	$org_name=test_input($_SESSION['select_org']);
}

	$org_name=mysqli_real_escape_string($conn, $org_name);

	//$query="select event_date, is_full from eli_spon_data where org_name='$org_name' and MONTH(event_date) = '$month' and YEAR(event_date) = '$year'";
	$query="select event_date, is_full from eli_spon_data where org_name='$org_name' and cancel='0'";

	if($result=mysqli_query($conn, $query))
	{
		while($row=mysqli_fetch_row($result))
		{
			switch($row[1])
			{
				case "1":
					array_push($fullDaylist, $row[0]);
					break;
				case "0":
					array_push($halfDaylist, $row[0]);
					break;
				default:
					array_push($availDaylist, $row[0]);
					break;
			}
		}
		mysqli_free_result($result);
	}else 
	  die(mysqli_error($conn));

	$fullDaylist=json_encode($fullDaylist);
	$halfDaylist=json_encode($halfDaylist);
	$availDaylist=json_encode($availDaylist);


	echo'<script>
	var fulld='.$fullDaylist.';
	var halfd='.$halfDaylist.';
	var availd='.$availDaylist.';
	fullDays=get_days(fulld);
	halfDays=get_days(halfd);
	availDays=get_days(availd);
	</script>';
	
?>