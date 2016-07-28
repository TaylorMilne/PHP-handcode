<?php
include 'connection.php';

$org_list=[];
$org_list=show_org_list($conn);

$org_num=count($org_list);

if(isset($_COOKIE['eli_remember']) && $_COOKIE['eli_remember'] == '1'){
	//this user asked remember him before
	//so find email, $session['is_org'] for him
	$rem_email=$_COOKIE['eli_email'];
	if(check_rem_email($rem_email))
		$_SESSION['remember_user']=true;
	//now the session var has his email and is_org value.
}

mysqli_close($conn);
 
//define function
function check_rem_email($email){
	global $conn;
					
		$query="select email from eli_org_data where email='$email'";

     	       if(!$result=mysqli_query($conn, $query))
		{
			mysqli_free_result($result);
			return false;
		}
					
		if(mysqli_num_rows($result)>0)
		{
			//double exsits
			if(!isset($_SESSION['is_org']))
			{
				$_SESSION['is_org']=true;
			}
			if(!isset($_SESSION['email']))
			{
				$_SESSION['email']=$email;
			}
			
			mysqli_free_result($result);
			return false;
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
			if(!isset($_SESSION['is_org']))
			{
				$_SESSION['is_org']=false;
			}
			if(!isset($_SESSION['email']))
			{
				$_SESSION['email']=$email;
			}
			
		}
		mysqli_free_result($result);
		return true;
}

function show_org_list($con){
	$array=[];
	$query="select org_name from eli_org_data";
	if($result=mysqli_query($con, $query)){
		//fetch one row by one
		while($row=mysqli_fetch_row($result))
		{
			$org_name=$row[0];
			array_push($array, $org_name);
		}
		//free result
		mysqli_free_result($result);
		return $array;
	}else{
		mysqli_free_result($result);
		return null;
	}
}
?>