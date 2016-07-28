<?php
session_start();
include "connection.php";
include "custom_function.php";
// define variables and set to empty values
//create information
$org_name = $org_user_name =$username= $email = $password = "";
$image=null;

//verify information
//about org
$bank="";

//about spon
$zip=$country=$state="";

//same
$phone=$addr="";
$is_org=true;

//error message
$err_msg="";

//redirect url
$redirect_url="";

if ($_SERVER["REQUEST_METHOD"] == "POST") {


	 if($_POST['action']=="create"){
		 //this create org/user
		 if(array_key_exists('org_name', $_POST))
	   {
		   //this is creat_org data
		   
		   //check org name
		   $org_name = test_input($_POST["org_name"]);
		   //check org user name
		   $org_user_name = test_input($_POST["org_username"]);
		   
		   $is_org=true;
		
	   }else if (array_key_exists('spon_name', $_POST)){
		   //check spon name
		   $username = test_input($_POST["spon_name"]);
		   $is_org=false;
		}	     	 
		 
		//get same name's values
		//check email
		$email = test_input($_POST["email"]);
		// check if e-mail address is well-formed
		$email = filter_var($email, FILTER_SANITIZE_EMAIL);
		if(!filter_var($email, FILTER_VALIDATE_EMAIL))
		{
			$redirect_url="Location: ".HTML_PATH."Create_org";
			$_SESSION['err_msg']="Email format is invalid";
			header($redirect_url);
			die();
		}
		
				
		//get password
		$password=test_input($_POST["password"]);

		$image=addslashes(file_get_contents($_FILES['upload_photo']['tmp_name']));
		
		if($is_org){
			if(!add_org_data($org_name, $org_user_name, $email, $password, $image))
			{
				$redirect_url="Location: ".HTML_PATH."Create_org";
			}
			else
			{
				$redirect_url="Location: ".HTML_PATH."Verifyorg";
				$_SESSION['email']=$email;
				$_SESSION['org_name']=$org_name;
				$_SESSION['is_org']=true;
				$_SESSION['org_user_name']=$org_user_name;
			}
		}else{
			if(!add_user_data($username, $email, $password, $image))
				$redirect_url="Location: ".HTML_PATH."Create_spon";
			else
			{
				$redirect_url="Location: ".HTML_PATH."Verifyspon";
				$_SESSION['email']=$email;
				$_SESSION['is_org']=false;
                $_SESSION['sponsor_fname']=$username;
			}
		}
							
	 }
	 else if ($_POST['action']=="verify")
	 {
		 //this is verify more details page
		 	 
		 //first check if this is detail about org
		 if (isset($_SESSION['is_org']) && $_SESSION['is_org'])
		 {
			 $bank= test_input($_POST['bank_account_email']);
			 $phone= test_input($_POST['phone']);
			 $addr= test_input($_POST['addr1']." ".$_POST['addr2']);
			 			 
			 //add verify data
			 if(add_org_verify_data($bank, $phone, $addr)){
				$err_msg="Congratulations, you have successfully signed up.";
				$redirect_url="Location: ".HTML_PATH."Create_success";
			 }else{
				$redirect_url="Location: ".HTML_PATH."Verifyorg";
			 }
		 } else if (isset($_SESSION['is_org']) && !$_SESSION['is_org']){
			 		 
			 $phone=test_input($_POST['phone']);
			 $zip=test_input($_POST['zip']);
			 $addr=test_input($_POST['addr1']." ".$_POST['addr2']);
			 
			 $state=test_input($_POST['state']);
			 $country=test_input($_POST['country']);
		 
			 if(add_spon_verify_data($phone, $zip, $addr, $state, $country)){
			 	$redirect_url="Location: ".HTML_PATH."Create_success";
				$err_msg="Congratulations, you have successfully signed up.";
			 }else{
				$redirect_url="Location: ".HTML_PATH."Verifyspon";
			 }
		 } else {
			 //this is spam, he entered the only addr
			 $err_msg="Please signup from first";
			 $redirect_url="Location: ".HTML_PATH."SIGN_UP";
			 
		 }
		 
		  
	 }
	 else if($_POST['action'] == "login")
	 {
		 //this is user login
			 
		 //get information
		$email = test_input($_POST["email"]);
		// check if e-mail address is well-formed
		$email = filter_var($email, FILTER_SANITIZE_EMAIL);
		if(!filter_var($email, FILTER_VALIDATE_EMAIL))
		{
			$redirect_url="Location: ".HTML_PATH."LOGIN";
			$_SESSION['err_msg']="Email format is invalid";
			header($redirect_url);
			die();
		}
		
		$remember=isset($_POST['rem_check']);
		echo "remember=".$remember;
		
		if(!is_login_from_session($email))
		{
			$err_msg="You have no accout. Please sign up.";
			$redirect_url="Location: ".HTML_PATH."SIGN_UP";
		}else{
			//get password
			$password=test_input($_POST["password"]);

			echo "password=".$password;

			if(check_login($email, $password)){
				
				if($remember=='1')
				{
					setcookie('eli_remember', '1');
					setcookie('eli_email', $email);
					var_dump($_COOKIE);
				}
				$redirect_url="Location: ".HTML_PATH."HOME";

				//save_temp
				if(isset($_SESSION['temp'])&& $_SESSION['temp'])
				{
					//this user ever visited the main page
					//if this is a sponsor then go to main page again
					if(isset($_SESSION['is_org']) && !$_SESSION['is_org'])
					{
						if(has_credit_info())
						{
							$redirect_url="Location: ".HTML_PATH."invoice";
							unset($_SESSION['temp']);
						}	
						else	
						{
							$err_msg="You need to complete the bank account and credit card info.";
							$redirect_url="Location: ".HTML_PATH."ind_setting";
						}
					}
				}
           	}
			else{
				//password incorrect or get failed
				$redirect_url="Location: ".HTML_PATH."LOGIN";
			}
		}
			
	 }
	 else if($_POST['action'] == "reset")
	 {
		 $email = test_input($_POST["email"]);
		// check if e-mail address is well-formed
		$email = filter_var($email, FILTER_SANITIZE_EMAIL);
		if(!filter_var($email, FILTER_VALIDATE_EMAIL))
		{
			$redirect_url="Location: ".HTML_PATH."Reset_Password";
			$_SESSION['err_msg']="Email format is invalid.";
			header($redirect_url);
			die();
		}
		
		$password=test_input($_POST["password"]);
		 
		 //confirm email exist
		 if(!is_login_from_session($email))
		 {
			$redirect_url="Location: ".HTML_PATH."SIGN_UP";
			$_SESSION['err_msg']="You have no account. Please signup.";
			header($redirect_url);
			die();
		 }
		 
		 //reset password
		 if(reset_password($email, $password))
		 {
			 $err_msg="Reset Password Successed. Please confirm.";
			 $redirect_url="Location: ".HTML_PATH."LOGIN";
		 }else{
			 $redirect_url="Location: ".HTML_PATH."Reset_Password";
		 }
	 } else if ($_POST['action'] == "go_to_org_or_main") {
		 
		 $select_org=$_POST['select_org'];
		 
		 if(empty($select_org) || $select_org=="org0")
		{
			$redirect_url="Location: ".HTML_PATH."Home";
			$err_msg="Please select organisation for sponsorship if you are a sponsor.";
		}
		 else if(isset($_SESSION['is_org']) && $_SESSION['is_org']) {
			$err_msg="You are an organisation user, not a sponsor.";
			$redirect_url="Location: ".HTML_PATH."Home";			
		} else {
			$redirect_url="Location: ".HTML_PATH."main";
			$_SESSION['select_org']=$select_org; 
		 }
			
	 }
	 
	 	$_SESSION['err_msg']=$err_msg;
		header($redirect_url);
		die();
}


	//check login user from cookie
	function is_login_from_cookie(){
		if(isset($_COOKIE['is_org']))
		{
			if(!isset($_SESSION['is_org']))
			{
				if($_COOKIE['is_org'] == "yes")
					$_SESSION['is_org']=true;
				else
					$_SESSION['is_org']=false;
			}
			return true;
		}else
			return false;
	}
	
	//check login user from email verify and session
	function is_login_from_session($email){
		global $conn, $err_msg;
			
		$query="select org_name from eli_org_data where email='$email'";
					
		if(!$result=mysqli_query($conn, $query))
		{
			mysqli_free_result($result);
			return false;
		}

		if(mysqli_num_rows($result) > 0)
		{
			$row=mysqli_fetch_row($result);
			//email exsits
			$_SESSION['is_org']=true;
			$_SESSION['email']=$email;
			$_SESSION['org_name']=$row[0];
			
			mysqli_free_result($result);
			return true;
		}		
		mysqli_free_result($result);
		
		
		$query="select email from eli_user_data where email='$email'";
				
		if(!$result=mysqli_query($conn, $query))
		{
			mysqli_free_result($result);
			return false;
		}
			
		if(mysqli_num_rows($result) > 0)
		{
			//email exsits
			$_SESSION['is_org']=false;
			$_SESSION['email']=$email;
			mysqli_free_result($result);
			return true;
		}
		mysqli_free_result($result);
		return false;
	}
	
	//insert org date to db
	function add_org_data($org_name, $org_user_name, $email, $password, $image){
		global $conn, $err_msg;
			
		if(is_null($conn))
		{
			$err_msg="Database connection is lost. Please redo after some minutes.";
			return false;
		}
		//check double email
		
		$org_name=mysqli_real_escape_string($conn, $org_name);
		$org_user_name=mysqli_real_escape_string($conn, $org_user_name);
		$email=mysqli_real_escape_string($conn, $email);
		
		//crypt password
		$password=mysqli_real_escape_string($conn, $password);
		$password=encrypt_decrypt(1, $password);
		
		if(check_email_exist($email)!=0)
			return false;
			
		$query="insert into eli_org_data (org_name, org_user_name, email, password, photo) values('$org_name', '$org_user_name', '$email', '$password', '$image')";
		
		if(mysqli_query($conn, $query))
		{
			return true;
		}else{
			$err_msg="Insert data is failed.";
			return false;
		}
		
	}

	//insert user date to db
	function add_user_data($username, $email, $password, $image){
		global $conn, $err_msg;
			
		if(is_null($conn))
		{
			echo "database is null";
			$err_msg="Database connection is lost. Please redo after some minutes.";
			return false;
		}
			
		
		$username=mysqli_real_escape_string($conn, $username);
		$email=mysqli_real_escape_string($conn, $email);
		
		$password=mysqli_real_escape_string($conn, $password);
		$password=encrypt_decrypt(1, $password);
		
		//crypt password
		$res=check_email_exist($email);

		if($res==0){
			//insert
			$query="insert into eli_user_data (username, email, password, photo) values('$username', '$email', '$password', '$image')";
		}else if($res==1)
		{
			//this is sponsor user
			return false;
		}else if($res == 3)
		{
			//this is guest user
			//update: guest-->loggedin user
			$query= "update eli_user_data set username='$username', password='$password', photo='$image' where email='$email'";
		}else
		{
			//this is org user or failed for checking
			return false;
		}
			
		if(mysqli_query($conn, $query))
		{
			//signup and then change menu
			$_SESSION['sponsor_fname']=$username;
			return true;
		}else{
			$err_msg="Insert data is failed.";
		   return false;
		}
	}

	//insert org verify data
	function add_org_verify_data($bank, $phone, $addr){
		global $conn, $err_msg;
			
		if(is_null($conn))
		{
			$err_msg="Database connection is lost. Please redo after some minutes.";
			return false;
		}

		//check double email
		
		$bank=mysqli_real_escape_string($conn, $bank);
		$phone=mysqli_real_escape_string($conn, $phone);
		$addr=mysqli_real_escape_string($conn, $addr);
		$email=$_SESSION['email'];
		
		$query="update eli_org_data set bank_account_email='$bank', phone_number='$phone',  address='$addr' where email='$email'";
		
		echo $query;
		
		if(mysqli_query($conn, $query))
		{
			return true;
		}else{
			$err_msg="Insert data is failed.";
		   return false;
		}
	}

	//insert user verify data
	function add_spon_verify_data($phone, $zip, $addr, $state, $country){
		global $conn, $err_msg;
		
		if(is_null($conn))
		{
			$err_msg="Database connection is lost. Please redo after some minutes.";
			return false;
		}
			
		$phone=mysqli_real_escape_string($conn, $phone);
		$zip=mysqli_real_escape_string($conn, $zip);
		$addr=mysqli_real_escape_string($conn, $addr);
		$state=mysqli_real_escape_string($conn, $state);
		$country=mysqli_real_escape_string($conn, $country);
		
		$email=$_SESSION['email'];
		
		$query="update eli_user_data set phone_number = '$phone', zip='$zip', address='$addr', state='$state', country='$country' where email='$email'";
		
		if(mysqli_query($conn, $query))
		{
			return true;
		}else{
			$err_msg="Insert data is failed.";
		   return false;
		}
	}

	//check login
	function check_login($email, $password){
		global $conn, $err_msg;

		if(is_null($conn))
		{
			$err_msg="Database connection is lost. Please redo after some minutes.";
			return false;
		}
		
		$email=mysqli_real_escape_string($conn, $email);
		$password=mysqli_real_escape_string($conn, $password);

		$password=encrypt_decrypt(1, $password);

		//check password: email check is ok, only password check is need
		if(isset($_SESSION['is_org']) && $_SESSION['is_org'])
		{
			//this is org user
			$table='eli_org_data';
		
			$query="select email, org_user_name from $table where email='$email' and password='$password'";	

			if(!$result=mysqli_query($conn, $query)){
				$err_msg="Get Password failed. Please try again later";
				return false;
			}
			
			$num=mysqli_num_rows($result);
			
					
			if($num>0)
			{
				$row=mysqli_fetch_assoc($result);
				//this is logined user
				$login=true;
				$_SESSION['email']=$email;
				$_SESSION['org_user_name']=$row['org_user_name'];
				$_COOKIE['is_org']=($_SESSION['is_org'])? "yes":"no";
				$err_msg="Welcome! $email is our user";
				
			}else{
				//password is incorrect
				unset($_SESSION['is_org']);
				$login=false;
				$err_msg="Password is incorrect. Did you forget password? You can reset your password.";
			}
		
		} else if(isset($_SESSION['is_org']) && !$_SESSION['is_org'])	{
			//this is sponsor
			$table='eli_user_data';
							
			$query="select email, username from $table where email='$email' and password='$password'";	
						
			if(!$result=mysqli_query($conn, $query)){
				$err_msg="Get Password failed. Please try again later";
				return false;
			}
			
			$num=mysqli_num_rows($result);
					
			if($num>0)
			{
				//this is logined user
				$row=mysqli_fetch_assoc($result);
				$login=true;
				$_SESSION['email']=$email;
				$_SESSION['sponsor_fname']=$row['username'];
				$_COOKIE['is_org']=($_SESSION['is_org'])? "yes":"no";
				$err_msg="Welcome! $email is our user";
				
			}else{
				//password is incorrect
				unset($_SESSION['is_org']);
				$login=false;
				$err_msg="Password is incorrect. Did you forget password? You can reset your password.";
			}
		}
		
		mysqli_free_result($result);
		return $login;
	}

	function reset_password($email, $password){
		global $conn, $err_msg;
		
		if(is_null($conn))
		{
			$err_msg="Database connection is lost. Please redo after some minutes.";
			return false;
		}
		
		$email=mysqli_real_escape_string($conn, $email);
		$password=mysqli_real_escape_string($conn, $password);
		$password=encrypt_decrypt(1, $password);
		
		//check password: email check is ok, only password check is need
		if(isset($_SESSION['is_org']) && $_SESSION['is_org'])
		{
			//this is org user
			$table='eli_org_data';
		} else if(isset($_SESSION['is_org']) && !$_SESSION['is_org'])
		{
			$table='eli_user_data';
		}else{
			return false;
		}
					
		$query="update $table set password='$password' where email='$email'";
					
		if(!$result=mysqli_query($conn, $query)){
			$err_msg="Reset Password failed. Please try again later";
			return false;
		}
		
		mysqli_free_result($result);
		return true;
	}

?>