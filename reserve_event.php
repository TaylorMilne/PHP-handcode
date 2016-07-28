<?php
session_start();
include "connection.php";
include 'custom_function.php';

//reserve event from half event of empty event
$redirect_url="Location: ".HTML_PATH."organization";
//change org setting php
$err_msg="";
$email=$_SESSION['email'];
$org_name=$_SESSION['org_name'];

if($_SERVER["REQUEST_METHOD"] == "POST"){
	$action=$_POST['action'];
	if($action == "reserve_from_half")
	{
		//reserve event from half event
		//package name
		$package_name=$_POST['package_name'];
		$event_date=$_POST['event_date'];
		$event_name=$_POST['event_name'];
		$sponsor_name=$_POST['sponsor_name'];
		$sponsor_email=$_POST['sponsor_email'];

		//first check whether the sponsor is exist in our user list
		$select_query="select count(id) from eli_user_data where username='$sponsor_name' and email='$sponsor_email'";
		if($result=mysqli_query($conn, $select_query))
		{
			$number=mysqli_fetch_array($result);
			mysqli_free_result($result);
			
			if ( $number['count(id)']!= 1)
			{
				$err_msg="The Sponsor does not exist our list. Please the sponsor signup to our sponsor list.";
			}
			else {

					//get package price
					$price=get_package_price($org_name, $package_name)*0.5;
					
					//second update the before event data which is half sponsored
					$update_query="update eli_spon_data set is_full='1' where org_email='$email' and event_name='$event_name' and event_date='$event_date' and package_name='$package_name'";
					mysqli_query($conn, $update_query);
					
					//third insert new data
					$insert_query="insert eli_spon_data(org_name, org_email, event_name, event_date, is_full, spon_half, package_name, sponsor_name, sponsor_email, send_amount) ";
					$insert_query=$insert_query."values('$org_name', '$email', '$event_name', '$event_date', 1, 0.5, '$package_name', '$sponsor_name', '$sponsor_email', '$price')";
					mysqli_query($conn, $insert_query);



					//send email to cater
				    //now let's send email to cater, org_user
					
					$query="SELECT item_count, item_desc, item_price, caterer_email FROM eli_org_package WHERE org_name = '$org_name' and package_name='$package_name'";

					$result = mysqli_query($conn,$query);

					$table_data="<table><thead><td width='10%'>QTY</td><td width='60%'>DESCRIPTION</td><td width='10%'>PRICE</td></thead><tbody>";

					$caterer_email="";

					if($result)
					{
						while($row = mysqli_fetch_array($result)) {
							$table_data=$table_data."<tr>
							<td>".$row['item_count']."</td>
							<td>".$row['item_desc']."</td>
							<td>$".$row['item_price']."</td>
							</tr>";
							if(empty($caterer_email))
							{
								$caterer_email=$row['caterer_email'];
							}
						}
						mysqli_free_result($result);
					}

					if(!empty($caterer_email))
					{
						$half="HALF";
					
						$table_data=$table_data."</tbody></table>";
						
						//contents
						$contents ="<br>".$table_data;
						$to = $caterer_email;
						$subject='Get ready party for '.$org_name;
						$message = '
						<html>
						<head>
					  	<title>The event '.$event_name.' has been sponsored for '.$org_name.'.</title>
						</head>
						<body>';
						$message .= '<p>The event '.$event_name.' has been '.$half.' sponsored for '.$org_name.' on '.$event_date.'.</p>';
						$message .='<p>You should prepair the party.</p>';
						$message .='<P>Package Item list as follows:</p>'.$contents;
						$message .='<p>Please click this link so the sponsor and organization can know you got email.</p>';
						$message .='<a href="'.PHP_PATH.'/send_confirm_email.php?action=confirm_prepare_event&org_name='.$org_name.'&org_email='.$email.'&sponsor_name='.$sponsor_name.'&sponsor_email='.$sponsor_email.'&event_name='.$event_name.'&event_date='.$event_date.'" target="_blank">Confirm Prepare Event</a>';
						$message .='</body></html>';

						// To send HTML mail, the Content-type header must be set
						$headers  = 'MIME-Version: 1.0' . "\r\n";
						$headers .= 'Content-type: text/html; charset=iso-8859-1'."\r\n";
						$headers .= "From: www.chulant.com"."\r\n";

						// Mail it
						mail($to, $subject, $message, $headers);
					}
			}
		}
	}
	else if($action == "reserve_from_empty")
	{
		$package_name=$_POST['reserve_package'];
		$event_date=$_POST['event_date'];
		$event_name=$_POST['event_name'];
		$sponsor_name=$_POST['sponsor_name'];
		$sponsor_email=$_POST['sponsor_email'];
		$spon_type=$_POST['spon_radio'];//full or half
		$price=get_package_price($org_name, $package_name);
		if($spon_type=="full")
		{
			$spon_t=1;
			$spon_q=1;
			$half="FULL";
		}
		else
		{
			$spon_t=0;
			$spon_q=0.5;
			$price*=0.5;
			$half="HALF";
		}

		//first check whether the sponsor is exist in our user list
		$select_query="select count(id) from eli_user_data where username='$sponsor_name' and email='$sponsor_email'";
		if($result=mysqli_query($conn, $select_query))
		{
			$number=mysqli_fetch_array($result);
			mysqli_free_result($result);
			
			if ( $number['count(id)']!= 1)
			{
				$err_msg="The Sponsor does not exist our list. Please the sponsor signup to our sponsor list.";
			}
			else {
				
					//update empty data
					$update_query="update eli_spon_data set is_full='$spon_t', spon_half='$spon_q', package_name='$package_name', sponsor_name='$sponsor_name', sponsor_email='$sponsor_email', send_amount='$price' ";
					$update_query=$update_query." where event_name='$event_name' and org_email='$email' and event_date='$event_date'";
					
					mysqli_query($conn, $update_query);
					

					//send email to caterer

					$query="SELECT item_count, item_desc, item_price, caterer_email FROM eli_org_package WHERE org_name = '$org_name' and package_name='$package_name'";

					$result = mysqli_query($conn,$query);

					$table_data="<table><thead><td width='10%'>QTY</td><td width='60%'>DESCRIPTION</td><td width='10%'>PRICE</td></thead><tbody>";

					$caterer_email="";

					if($result)
					{
						while($row = mysqli_fetch_array($result)) {
							$table_data=$table_data."<tr>
							<td>".$row['item_count']."</td>
							<td>".$row['item_desc']."</td>
							<td>$".$row['item_price']."</td>
							</tr>";
							if(empty($caterer_email))
							{
								$caterer_email=$row['caterer_email'];
							}
						}
						mysqli_free_result($result);
					}

					if(!empty($caterer_email))
					{
						$table_data=$table_data."</tbody></table>";
						
						//contents
						$contents ="<br>".$table_data;
						$to = $caterer_email;
						$subject='Get ready party for '.$org_name;
						$message = '
						<html>
						<head>
					  	<title>The event '.$event_name.' has been sponsored for '.$org_name.'.</title>
						</head>
						<body>';
						$message .= '<p>The event '.$event_name.' has been '.$half.' sponsored for '.$org_name.' on '.$event_date.'.</p>';
						$message .='<p>You should prepair the party.</p>';
						$message .='<P>Package Item list as follows:</p>'.$contents;
						$message .='<p>Please click this link so the sponsor and organization can know you got email.</p>';
						$message .='<a href="'.PHP_PATH.'/send_confirm_email.php?action=confirm_prepare_event&org_name='.$org_name.'&org_email='.$email.'&sponsor_name='.$sponsor_name.'&sponsor_email='.$sponsor_email.'&event_name='.$event_name.'&event_date='.$event_date.'" target="_blank">Confirm Prepare Event</a>';
						$message .='</body></html>';

						// To send HTML mail, the Content-type header must be set
						$headers  = 'MIME-Version: 1.0' . "\r\n";
						$headers .= 'Content-type: text/html; charset=iso-8859-1'."\r\n";
						$headers .= "From: www.chulant.com"."\r\n";

						// Mail it
						mail($to, $subject, $message, $headers);
					}
			}
		}
	}
}

$_SESSION['err_msg']=$err_msg;
header($redirect_url);
die();
?>