<?php
session_start();
include "connection.php";
include 'custom_function.php';

$packages=[];

if((isset($_SESSION['is_org'])) && $_SESSION['is_org'])
{
	$org_email=$_SESSION['email'];
	//only organisation
	//get organisation package names
	$query="select distinct package_name from eli_org_package where org_email='$org_email'";
	if($result=mysqli_query($conn, $query)){
		//fetch one row by one
		while($row=mysqli_fetch_row($result))
		{
			$pack_name=$row[0];
			array_push($packages, $pack_name);
		}
		//free result
		mysqli_free_result($result);
	}
}

//show sponsors name

//get flag
$flag=strval($_GET['flag']);

//event name, event_date, SPONSORED_BY, hr, sponsor1_name, hr, sponsor2_name
$event_date = strval(test_input($_GET['event_date']));
$event_date=mysqli_real_escape_string($conn, $event_date);

$event_date_exp=strval(test_input($_GET['event_date_exp']));

$org_email=test_input($_SESSION['email']);
$org_email=mysqli_real_escape_string($conn, $org_email);

$query="select event_name, sponsor_name, is_full, anonymous, package_name from eli_spon_data where event_date='$event_date' and org_email='$org_email' and cancel='0'";

if(!($result=mysqli_query($conn, $query)))
{
	echo mysqli_error($conn);
	die();
}

$row1=mysqli_fetch_assoc($result);

$event_name=$row1['event_name'];
$package_name=$row1['package_name'];

$reserve_php=PHP_PATH."reserve_event.php";

if($row1['is_full'] == 0)
{
	//there is only one half sponsor
	$sponsor_name=$row1['sponsor_name'];
	$anony_sponsor=($row1['anonymous'] == 1) ? true:false;
	echo '
   	 <div id="if_exist_event">
		<div class="left-col">
			<p class="helw_20_p entire_width">'.$event_name.'</p>
			<p class="with_image_right">Packages</p>
			<hr style="margin:auto; width: 100%;">
			<p class="with_image_right">'.$package_name.'</p>
		</div>
		<div class="right-col">
			<p id="date_re" class="helw_20_p entire_width">'.$event_date_exp.'</p>
			<div class="clearfix">
				<p class="with_image_right">Sponsors</p>
			</div>
			<hr style="margin:auto; width: 100%;">
			<div class="clearfix">';
			 if($anony_sponsor)
	 	     		echo '<p class="with_image_right">anonymous sponsor</p>';
	  			else
	  				echo '<p class="with_image_right">'.$sponsor_name.'</p>';
	  			echo '
					<img name="half_mark" class="mark" id="half_mark_img" src="../wp-content/themes/elitheme/img/half_mark_tiny_10.png">
			</div>
			<div style="text-align: center" class="clearfix">
				<span><img src="../wp-content/themes/elitheme/img/half_mark_tiny_10.png"></span>
				<span class="mark_label">Half</span>
				<span><img src="../wp-content/themes/elitheme/img/full_mark_tiny_10.png"></span>
				<span class="mark_label">Full</span>
			</div>
		</div>
		<input type="text" class="no_display" id="event_date_flag" value="'.$flag.'">
		<div class="clearfix_each"></div>
		<input type="button" value="CANCEL EVENT" class="btn" id="red_cancel_event_btn" onclick="cancel_event()">
 </div>
 
 <div id="reserve_event">
		<p class="helw_17_p" style="margin-top: 20px;" title="This Event can be half sponsored by other sponsor.">Reserve</p>
		<hr style="margin: 10px 0;">
		<form method="POST" action="'.$reserve_php.'">
			<div id="name_div">
				<p class="helw_17_p">Name</p>
				<input type="text" class="gray_input" name="sponsor_name" id="gray_input_name" required>
			</div>
			<div id="email_div">
				<p class="helw_17_p">Email</p>
				<input type="email" class="gray_input" name="sponsor_email" id="gray_input_email" required>
			</div>
			<input type="text" class="no_display" name="action" value="reserve_from_half">
			<input type="text" class="no_display" name="package_name" value="'.$package_name.'">
			<input type="text" class="no_display" name="event_name" value="'.$event_name.'">
			<input type="text" class="no_display" name="event_date" value="'.$event_date.'">
   			<input type="submit" value="+" class="green_btn" id="event_right_plus_bt">
   		</form>
  </div>';

}else if($row1['is_full'] == 1){
   if(mysqli_num_rows($result) == 2)
   {
   	//there are two sponsor each of whom has sponsored half
   	 $sponsor_name1=$row1['sponsor_name'];
   	 $anony_sponsor1=($row1['anonymous'] == 1) ? true:false;
   	 $row2=mysqli_fetch_assoc($result);
   	 $sponsor_name2=$row2['sponsor_name'];
   	 $anony_sponsor2=($row2['anonymous'] == 1) ? true:false;

   	 if($sponsor_name1 == $sponsor_name2)
   	 {
		echo '
	   	 <div id="if_exist_event">
			<div class="left-col">
				<p class="helw_20_p entire_width">'.$event_name.'</p>
				<p class="with_image_right">Packages</p>
				<hr style="margin:auto; width: 100%;">
				<p class="with_image_right">'.$package_name.'</p>
			</div>
			<div class="right-col">
				<p id="date_re" class="helw_20_p entire_width">'.$event_date_exp.'</p>
				<div class="clearfix">
					<p class="with_image_right">Sponsors</p>
				</div>
				<hr style="margin:auto; width: 100%;">
				<div class="clearfix">';
				if($anony_sponsor1 || $anony_sponsor2)
	 	     		echo '<p class="with_image_right">anonymous sponsor</p>';
	  			else
	  				echo '<p class="with_image_right">'.$sponsor_name1.'</p>';
	  			echo '
					<img name="half_mark" class="mark" id="full_mark_img" src="../wp-content/themes/elitheme/img/full_mark_tiny_10.png">
				</div>
				<div style="text-align: center" class="clearfix">
					<span><img src="../wp-content/themes/elitheme/img/half_mark_tiny_10.png"></span>
					<span class="mark_label">Half</span>
					<span><img src="../wp-content/themes/elitheme/img/full_mark_tiny_10.png"></span>
					<span class="mark_label">Full</span>
				</div>
			</div>
			<input type="text" class="no_display" id="event_date_flag" value="'.$flag.'">
			<div class="clearfix_each">
				<input type="button" value="CANCEL EVENT" class="btn" id="red_cancel_event_btn" onclick="cancel_event()">
			</div>';
   	 }
   	 else
   	 {
   	 	echo '
	   	 <div id="if_exist_event">
			<div class="left-col">
				<p class="helw_20_p entire_width">'.$event_name.'</p>
				<p class="with_image_right">Packages</p>
				<hr style="margin:auto; width: 100%;">
				<p class="with_image_right">'.$package_name.'</p>
			</div>
			<div class="right-col">
				<p id="date_re" class="helw_20_p entire_width">'.$event_date_exp.'</p>
				<div class="clearfix">
					<p class="with_image_right">Sponsors</p>
				</div>
				<hr style="margin:auto; width: 100%;">
				<div class="clearfix">';
				 if($anony_sponsor1)
	 	     		echo '<p class="with_image_right">anonymous sponsor</p>';
	  			else
	  				echo '<p class="with_image_right">'.$sponsor_name1.'</p>';
	  			echo '
					<img name="half_mark" class="mark" id="half_mark_img" src="../wp-content/themes/elitheme/img/half_mark_tiny_10.png">
				</div>
				<div class="clearfix">';
				 if($anony_sponsor2)
	 	     		echo '<p class="with_image_right">anonymous sponsor</p>';
	  			else
	  				echo '<p class="with_image_right">'.$sponsor_name2.'</p>';
	  			echo '
					<img name="full_mark" class="mark" id="full_mark_img" src="../wp-content/themes/elitheme/img/half_mark_tiny_10.png">
				</div>
				<div style="text-align: center" class="clearfix">
					<span><img src="../wp-content/themes/elitheme/img/half_mark_tiny_10.png"></span>
					<span class="mark_label">Half</span>
					<span><img src="../wp-content/themes/elitheme/img/full_mark_tiny_10.png"></span>
					<span class="mark_label">Full</span>
				</div>
			</div>
			<input type="text" class="no_display" id="event_date_flag" value="'.$flag.'">
			<div class="clearfix_each">
				<input type="button" value="CANCEL EVENT" class="btn" id="red_cancel_event_btn" onclick="cancel_event()">
			</div>';
   	 }
   }else{
   	 //there is only one full sponsor
   	 $sponsor_name=$row1['sponsor_name'];
   	 $anony_sponsor=($row1['anonymous'] == 1) ? true:false;
   	 echo '
   	 <div id="if_exist_event">
		<div class="left-col">
			<p class="helw_20_p entire_width">'.$event_name.'</p>
			<p class="with_image_right">Packages</p>
			<hr style="margin:auto; width: 100%;">
			<p class="with_image_right">'.$package_name.'</p>
		</div>
		<div class="right-col">
			<p id="date_re" class="helw_20_p entire_width">'.$event_date_exp.'</p>
			<div class="clearfix">
				<p class="with_image_right">Sponsors</p>
			</div>
			<hr style="margin:auto; width: 100%;">
			<div class="clearfix">';
	 if($anony_sponsor)
	 	     echo '<p class="with_image_right">anonymous sponsor</p>';
	  else
	  		echo '<p class="with_image_right">'.$sponsor_name.'</p>';
	    
	    echo '<img name="full_mark" class="mark" id="full_mark_img" src="../wp-content/themes/elitheme/img/full_mark_tiny_10.png">
			</div>
			<div style="text-align: center" class="clearfix">
				<span><img src="../wp-content/themes/elitheme/img/half_mark_tiny_10.png"></span>
				<span class="mark_label">Half</span>
				<span><img src="../wp-content/themes/elitheme/img/full_mark_tiny_10.png"></span>
				<span class="mark_label">Full</span>
			</div>
		</div>
		<input type="text" class="no_display" id="event_date_flag" value="'.$flag.'">
		<div class="clearfix_each">
			<input type="button" value="CANCEL EVENT" class="btn" id="red_cancel_event_btn" onclick="cancel_event()">
		</div>';
		
   }
}else{
	//available event. not sponsored
	 echo '<div id="if_exist_event">
				<div class="left-col" style="height:130px">
					<p class="helw_20_p entire_width">'.$event_name.'</p>
					<p class="with_image_right">Packages</p>
					<hr style="margin:auto; width: 100%;">
					<p class="with_image_right"></p>
				</div>
				<div class="right-col" style="height:130px">
					<p id="date_re" class="helw_20_p">'.$event_date_exp.'</p>
					<div class="clearfix">
						<p class="with_image_right">Sponsors</p>
					</div>
					<hr style="margin:auto; width: 100%;">
					<div class="clearfix">
					</div>
					<div style="text-align: center" class="clearfix">
					</div>
				</div>
				<div class="clearfix_each"></div>
					<input type="button" value="CANCEL EVENT" class="btn" id="red_cancel_event_btn" onclick="cancel_event()">
				</div>
				<div id="reserve_event">
					<p class="helw_17_p" style="margin-top: 20px;" title="This Event can be half sponsored by other sponsor.">Reserve</p>
					<hr style="margin: 10px 0;">
					<form method="POST" action="'.$reserve_php.'">
						<div id="name_div">
							<p class="helw_17_p">Name</p>
							<input type="text" class="gray_input" name="sponsor_name" id="gray_input_name" required>
						</div>
						<div id="email_div">
							<p class="helw_17_p">Email</p>
							<input type="email" class="gray_input" name="sponsor_email" id="gray_input_email" required>
						</div>
						<div id="reserve_package_div">
						    <p class="helw_17_p">Package</p>
						    <select name="reserve_package" id="reserve_package">';
								foreach($packages as $pack_one)
        						{
          							echo "<option value='".$pack_one."'>".$pack_one."</option>";
         						}
							echo '</select>
						</div>
						<div id="spon_type_div">
								<input type="radio" name="spon_radio" id="full_spon_check" class="spon_radio_button" value="full" checked/>
								<label for="full_spon_check" class="radio_label" id="full_radio_label">Full</label>
								<input type="radio" name="spon_radio" id="half_spon_check" class="spon_radio_button"  value="half"/>
								<label for="half_spon_check" class="radio_label" id="half_radio_label">Half</label>
						</div>
						<input type="text" class="no_display" name="action" value="reserve_from_empty">
						<input type="text" class="no_display" name="event_name" value="'.$event_name.'">
						<input type="text" class="no_display" name="event_date" value="'.$event_date.'">
			   			<input type="submit" value="+" class="green_btn" id="event_right_plus_bt">
			   		</form>
  				</div>
		  </div>
		';
}

 echo '<input id="event_date" type="text" class="no_display" value="'.$event_date.'">';
mysqli_free_result($result);
die();
?> 