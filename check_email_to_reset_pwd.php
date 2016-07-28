<?php
session_start();
include "connection.php";
include "custom_function.php";

//check email
$reset_id="";
$is_org=1;

if(isset($_GET['email']))
{
	$email= mysqli_real_escape_string($conn,$_GET['email']);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) // Validate email address
    {
        echo "Invalid email address please type a valid email!";
    }
    else{
    	
    	$query="select org_id from eli_org_data where email='$email'";
	    if($result=mysqli_query($conn, $query))
	    {
	    	$row=mysqli_fetch_array($result);
	    	if(count($row)>=1)
		    {
		      $reset_id=$row['org_id'];
		    }
		    mysqli_free_result($result);
	    }
	    
	    if(empty($reset_id))
	    {
	    	$query="select id from eli_user_data where email='$email'";
	    	        
			if($result=mysqli_query($conn, $query))
		    {
		    	$row=mysqli_fetch_array($result);
		    	if(count($row)>=1)
			    {
			      $reset_id=$row['id'];
			      $is_org=0;
			    }
			    mysqli_free_result($result);
		    }
	
	    }

	    if(empty($reset_id))
	    {
	    	echo  "Email not found. Please signup.";
	    }else{
	    	//send email to email address
	    	$encrypt = md5(1290*3+$reset_id);
            $message = "Your password reset link send to your e-mail address.";
            $to=$email;
            $subject="Forget Password";
            $from = $_SERVER['HTTP_HOST'];
            $body='<br>Click here to reset your password'.'<a href="'.PHP_PATH.'/get_id_to_reset_pwd.php?encrypt='.$encrypt.'&is_org='.$is_org.'">Reset Password</a>'.'<br/> <br/>--<br>Solve your problems.';
            $headers = "From: " . strip_tags($from) . "\r\n";
            $headers .= "Reply-To: ". strip_tags($from) . "\r\n";
            $headers .= "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
 
            if(mail($to,$subject,$body,$headers))
            	echo  "Email has been sent to you. Please check your email.";
            else
            	echo  "Email has not been sent. Please try again later.";
        }
    }
}
?>