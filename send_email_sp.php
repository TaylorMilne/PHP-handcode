<?php
session_start();
include "connection.php";
include 'custom_function.php';

//email send is hold only when he is sponsor
if(isset($_SESSION['is_org']) && !$_SESSION['is_org'])
{
	$to  = $_SESSION['email']; // note the comma	


	$sponsor_name=$_GET['sponsor_name'];
	$package_name=$_SESSION['package_name'];
	$org_name=$_SESSION['select_org'];


	$query="SELECT item_count, item_desc, item_price, item_total FROM eli_org_package WHERE org_name = '$org_name' and package_name='$package_name'";

	$result = mysqli_query($conn,$query);

	$subtotal=$total=0;

	$table_data='<table><thead><td width="10%">QTY</td><td width="60%">DESCRIPTION</td><td width="10%">PRICE</td></thead><tbody>';

	if($result)
	{
		while($row = mysqli_fetch_array($result)) {
			$table_data=$table_data.'<tr>
			<td>'.$row['item_count'].'</td>
			<td>'.$row['item_desc'].'</td>
			<td>$'.$row['item_price'].'</td></tr>';
			$subtotal+=intval($row['item_total']);
		}
		mysqli_free_result($result);
	}

	if(isset($_SESSION['select_is_full']) && !$_SESSION['select_is_full'])
	{
		$subtotal/=2;
		$half='HALF';
	}else
	   $half='FULL';

	$table_data=$table_data."</tbody></table><br>";
	$total=$subtotal+$tax;

	//contents
	$contents = 'Sponsored Package content as follows:<br>';
	$contents .= $table_data;
	$contents .= '<br>You sponsored for '$org_name' on the event '.$_SESSION['select_event_name'].'.<br>';
	$contents .= '<br>Billing amount: $'.$total."<br>";

	// subject
	$subject = 'You sponsored for '.$_SESSION['select_org'];

	// message
	$message = '
	<html>
	<head>
	  <title>Thank you for sponsoring.</title>
	</head>
	<body>'.$contents.'</body></html>';

	// To send HTML mail, the Content-type header must be set
	$headers  = 'MIME-Version: 1.0' . "\r\n";
	$headers .= 'Content-type: text/html; charset=iso-8859-1'."\r\n";
	$headers .= "From: www.chulant.com"."\r\n";

	// Mail it
	if(mail($to, $subject, $message, $headers))
		echo "Email about sponsored package has been sent you. Please check email";
	else
		echo "Email sent failed.";
}

die();
?>