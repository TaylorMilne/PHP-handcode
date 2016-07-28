<?php
session_start();
include "connection.php";
include 'custom_function.php';

//show sponsors name
//event name, event_date, SPONSORED_BY, hr, sponsor1_name, hr, sponsor2_name
$event_date = strval(test_input($_GET['event_date']));
$event_date=mysqli_real_escape_string($conn, $event_date);
$event_date_exp=strval(test_input($_GET['event_date_exp']));
$in_main=false;

if(isset($_GET['in_main']) && $_GET['in_main'] == 1)
{ 
  $in_main=true;
  $org_name=test_input($_SESSION['select_org']);
  $org_name=mysqli_real_escape_string($conn, $org_name);
  $query="select event_name, sponsor_name, org_email, is_full, anonymous from eli_spon_data where event_date='$event_date' and org_name='$org_name' and cancel='0'";
}else{
  $org_email=test_input($_SESSION['email']);
  $org_email=mysqli_real_escape_string($conn, $org_email);
  $query="select event_name, sponsor_name, is_full, anonymous from eli_spon_data where event_date='$event_date' and org_email='$org_email' and cancel='0'";
}

if(!($result=mysqli_query($conn, $query)))
{
	echo mysqli_error($conn);
  die();
}

$row1=mysqli_fetch_assoc($result);

$event_name=$row1['event_name'];

echo '<p id="event_name">'.$event_name.'</p>
	<p class="date" id="date_le">'.$event_date_exp.'</p>
	<p id="sponsored_by">SPONSORED BY</p>
	<hr class="medium_hr">';

if($row1['is_full'] == 0)
{
	//there is only one sponsor
  if($in_main)
  {
    //memorize the selected event day and event_name
    $_SESSION['select_event_name']=$event_name;
    $_SESSION['select_event_day']=$event_date;
    $_SESSION['select_event_day_exp']=$event_date_exp;
    $_SESSION['already']=true;
    $_SESSION['select_org_email']=$row1['org_email'];
  }

	$sponsor_name=$row1['sponsor_name'];
  $anony_sponsor=($row1['anonymous'] == 1) ? true: false;

  if(!$anony_sponsor)
    echo '<p class="name">'.$sponsor_name.'</p>';
  else
    echo '<p class="name">anonymous sponsor</p>';

}else if($row1['is_full'] == 1){
   if(mysqli_num_rows($result) == 2)
   {
   	//there are two sponsor each of whom has sponsored half
       $sponsor_name1=$row1['sponsor_name'];
       $anony_sponsor1=($row1['anonymous'] == 1) ? true: false;

     	 $row2=mysqli_fetch_assoc($result);
     	 $sponsor_name2=$row2['sponsor_name'];
       $anony_sponsor2=($row2['anonymous'] == 1) ? true: false;
   	 
       if($sponsor_name1 == $sponsor_name2)
       {
        if($anony_sponsor1 || $anony_sponsor2)
          echo '<p class="name">anonymous sponsor</p>';
        else
         echo '<p class="name">'.$sponsor_name1.'</p>';
       }
       else{
        if($anony_sponsor1)
           echo '<p class="name">anonymous sponsor</p>';
        else
          echo '<p class="name">'.$sponsor_name1.'</p>';
        echo '<hr class="medium_hr">';
        if($anony_sponsor2)
           echo '<p class="name">anonymous sponsor</p>';
        else
          echo '<p class="name">'.$sponsor_name2.'</p>';
       }

   }else{
   	 $sponsor_name=$row1['sponsor_name'];
     $anony_sponsor=($row1['anonymous'] == 1) ? true: false;
   	 if($anony_sponsor)
        echo '<p class="name">anonymous sponsor</p>';
     else
        echo '<p class="name">'.$sponsor_name.'</p>';
   }
}else{
	echo "<p class='name'>No one sponsored</p>";
  if($in_main)
  {
    //memorize the selected event day and event_name
    $_SESSION['select_event_name']=$event_name;
    $_SESSION['select_event_day']=$event_date;
    $_SESSION['select_event_day_exp']=$event_date_exp;
    $_SESSION['already']=false;
    $_SESSION['select_org_email']=$row1['org_email'];
  }
}

mysqli_free_result($result);
die();
?>