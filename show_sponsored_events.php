<?php
session_start();
include "connection.php";
include 'custom_function.php';

if(!$conn)
	die("Database error");

$date_value=$_GET['show_month'];
$date_value=str_replace(',', ' ', $date_value);

$time=strtotime($date_value);

$month=date("m", $time);
$year=date("Y", $time);

if(isset($_SESSION['is_org']) && $_SESSION['is_org'])
{
	//check again org
	$org_name=$_SESSION['org_name'];
	$email=$_SESSION['email'];

	//get all events is full or not
	$query="select event_name, event_date, is_full from eli_spon_data ";
	$query=$query."where org_name='$org_name' and org_email='$email' and MONTH(event_date)='$month' and YEAR(event_date)='$year' and cancel='0' order by event_date";

	if($result=mysqli_query($conn, $query))
	{
		echo "
		  <table id='spon_event_tb'>
		  <thead><td width='35%'>EVENT</td><td width='30%'>DATE</td><td width='35%'>Available/Not</td></thead>
		  <tbody>
		";

		$full_event_list=array();

		while($row=mysqli_fetch_assoc($result))
		{
			$flag=$row['is_full'];
			if($flag == 1)
			{
				$event_date=$row['event_date'];
				if (!in_array($event_date, $full_event_list))
				{
					echo "<tr><td>".$row['event_name']."</td><td>".$row['event_date']."</td><td>Not</td></tr>";
					array_push($full_event_list, $row['event_date']);
				}
			}else if( $flag == 0 || $flag == -1)
				echo "<tr class='avail'><td>".$row['event_name']."</td><td>".$row['event_date']."</td><td>Available</td></tr>";
		}
		mysqli_free_result($result);

		echo "</tbody></table>";
	}
}
?>