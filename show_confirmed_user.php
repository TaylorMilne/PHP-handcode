<?php 
session_start();
include 'connection.php';

$email=$_SESSION['email'];

//get sponsor user's photo and username
$query="select photo, username from eli_user_data where email='$email'";
if($result=mysqli_query($conn, $query))
{
	$row=mysqli_fetch_assoc($result);
	$photo=$row['photo'];
	$name=$row['username'];
	$first_name=split(" ", $name)[0];
	mysqli_free_result($result);
}

?>