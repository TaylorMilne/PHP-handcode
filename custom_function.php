<?php

//validate inputed text
function test_input($data) {
	   $data = trim($data);
	   $data = stripslashes($data);
	   $data = htmlspecialchars($data);
	   return $data;
}
	
function getUrl() {
  $url  = @( $_SERVER["HTTPS"] != 'on' ) ? 'http://'.$_SERVER["SERVER_NAME"] :  'https://'.$_SERVER["SERVER_NAME"];
  $url .= ( $_SERVER["SERVER_PORT"] !== 80 ) ? ":".$_SERVER["SERVER_PORT"] : "";
  $url .= $_SERVER["REQUEST_URI"];
  return $url;
}

function get_inner_html( $node ) { 
    $innerHTML= ''; 
    $children = $node->childNodes; 
    foreach ($children as $child) { 
        $innerHTML .= $child->ownerDocument->saveXML( $child ); 
    } 

    return $innerHTML; 
}

function check_cc($cc, $extra_check = false){
  $cards = array(
        "visa" => "(4\d{12}(?:\d{3})?)",
        "amex" => "(3[47]\d{13})",
        "jcb" => "(35[2-8][89]\d\d\d{10})",
        "mastercard" => "(5[1-5]\d{14})",
        "switch" => "(?:(?:(?:4903|4905|4911|4936|6333|6759)\d{12})|(?:(?:564182|633110)\d{10})(\d\d)?\d?)",
        "discover" => "([6011]{4})(\d{12})"
    );
    $names = array("visa", "amex", "jcb", "mastercard", "switch", "discover");
    $matches = array();
    $pattern = "#^(?:".implode("|", $cards).")$#";
    $result = preg_match($pattern, str_replace("-", "", $cc), $matches);
    if($extra_check && $result > 0){
        $result = (validatecard($cc))?1:0;
    }
    return ($result>0)?$names[sizeof($matches)-2]:false;
}

function encrypt_decrypt($action, $string) {
    $output = false;

    $encrypt_method = "AES-256-CBC";
    $secret_key = 'daniel';
    $secret_iv = 'daniel';

    // hash
    $key = hash('sha256', $secret_key);
    
    // iv - encrypt method AES-256-CBC expects 16 bytes - else you will get a warning
    $iv = substr(hash('sha256', $secret_iv), 0, 16);

    if( $action == '1' ) {
        $output = openssl_encrypt($string, $encrypt_method, $key, 0, $iv);
        $output = base64_encode($output);
    }
    else if( $action == '2' ){
        $output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);
    }

    return $output;
}

function unset_billing_info()
{
    //if he successed the payment, let's delete the temp info
    if(isset($_SESSION['select_event_day']))
    {
        unset($_SESSION['select_event_day']);
    }
    if(isset($_SESSION['select_event_day_exp']))
    {
        unset($_SESSION['select_event_day_exp']);
    }
    if(isset($_SESSION['select_event_name']))
    {
        unset($_SESSION['select_event_name']);
    }
    if(isset($_SESSION['select_anony']))
    {
        unset($_SESSION['select_anony']);
    }
    if(isset($_SESSION['already']))
    {
        unset($_SESSION['already']);
    }
    if(isset($_SESSION['select_is_full']))
    {
        unset($_SESSION['select_is_full']);
    }
    if(isset($_SESSION['billing_amount']))
    {
        unset($_SESSION['billing_amount']);
    }
    if(isset($_SESSION['total']))
    {
        unset($_SESSION['total']);
    }
    if(isset($_SESSION['package_name']))
    {
        unset($_SESSION['package_name']);
    }
    if(isset($_SESSION['checkout']))
    {
        unset($_SESSION['checkout']);
    }
                
}

//save_temp
function has_credit_info()
{
    global $conn;
    $email=$_SESSION['email'];
    $query="select credit_card_number, card_holder, billing_address, cv_code, expire_date, bank_account_email from eli_user_data where email='$email'";
    if($result=mysqli_query($conn, $query))
    {
        $row=mysqli_fetch_row($result);
       if(empty($row[0]) || empty($row[1]) || empty($row[2]) || empty($row[3]) || empty($row[4]) || empty($row[5]))
       {
         mysqli_free_result($result);
         $_SESSION['err_msg']="You need to complete the bank account info and credit card info in your settings.";
         return false;
       } else {
        mysqli_free_result($result);
        return true;
       }
    } 
    else 
        return false;
}

//save org info
function has_org_credit_info()
{
    global $conn;
    $org_email=$_SESSION['email'];
    $org_name=$_SESSION['org_name'];
    $query="select bank_account_email, credit_card_number, billing_address, card_holder, cv_code, expire_date from eli_org_data where email='$org_email' and org_name='$org_name'";
    if($result=mysqli_query($conn, $query))
    {
        $row=mysqli_fetch_row($result);
        if(empty($row[0]) || empty($row[1]) || empty($row[2]) || empty($row[3]) || empty($row[4]) || empty($row[5]) )
        {
            mysqli_free_result($result);
            return false;
        }else
        {
            mysqli_free_result($result);
            return true;
        }
    } 
        return false;
}


//delete checkout sessoin var
function unset_checkout_session_var()
{
    if(isset($_SESSION['guest_bank_email']))
    {
        unset($_SESSION['guest_bank_email']);
    }
    if(isset($_SESSION['credit_card_number']))
    {
        unset($_SESSION['credit_card_number']);
    }
    if(isset($_SESSION['card_holder']))
    {
        unset($_SESSION['card_holder']);
    }
    if(isset($_SESSION['card_type']))
    {
        unset($_SESSION['card_type']);
    }
    if(isset($_SESSION['ids']))
    {
        unset($_SESSION['ids']);
    }
    if(isset($_SESSION['billing_addr']))
    {
        unset($_SESSION['billing_addr']);
    }
    if(isset($_SESSION['cv_code']))
    {
        unset($_SESSION['cv_code']);
    }
    if(isset($_SESSION['expire_month']))
    {
        unset($_SESSION['expire_month']);
    }
    if(isset($_SESSION['expire_year']))
    {
        unset($_SESSION['expire_year']);
    }
}

function check_email_exist($email){
    global $conn, $err_msg;
                
    $query="select email from eli_org_data where email='$email'";
                    
    if(!$result=mysqli_query($conn, $query))
    {
        $err_msg="Email check failed";
        mysqli_free_result($result);
        return -1;
    }
                
    if(mysqli_num_rows($result)>0)
    {
        //double exsits
        $err_msg="Same Email exists in org.";
        echo $err_msg;
        if(!isset($_SESSION['is_org']))
        {
            $_SESSION['is_org']=true;
        }
        if(!isset($_SESSION['email']))
        {
            $_SESSION['email']=$email;
        }
        
        mysqli_free_result($result);
        return 2;
    }
    mysqli_free_result($result);
    
    $query="select email, username from eli_user_data where email='$email'";
    echo $query;
            
    if(!$result=mysqli_query($conn, $query))
    {
        $err_msg="Email check failed";
        echo $err_msg;
        mysqli_free_result($result);
        return -1;
    }
        
    if(mysqli_num_rows($result) > 0)
    {
        //double exsits
        

        $row=mysqli_fetch_assoc($result);
        if(!isset($_SESSION['is_org']))
        {
            $_SESSION['is_org']=false;
        }
        if(!isset($_SESSION['email']))
        {
            $_SESSION['email']=$email;
        }
        
        if($row['username'] == "GUEST")
        {
            //this is guest user, return 3;
            $err_msg="You was a guest. Updated your profile.";
            if(isset($_SESSION['guest_email']))
            {
                unset($_SESSION['guest_email']);
            }
            mysqli_free_result($result);
            return 3;   

        }else
        {
            //this is sponsor user who has signup
            $err_msg="Same Email exists in user.";
            mysqli_free_result($result);
            return 1;
        }
    }
    mysqli_free_result($result);
    return 0;
}

function check_email_exist_only($email){
    global $conn, $err_msg;
                
    $query="select email from eli_org_data where email='$email'";
                    
    if(!$result=mysqli_query($conn, $query))
    {
        $err_msg="Email check failed";
        mysqli_free_result($result);
        return -1;
    }
                
    if(mysqli_num_rows($result)>0)
    {
        //double exsits
        $err_msg="Same Email exists in org.";
        mysqli_free_result($result);
        return 2;
    }
    mysqli_free_result($result);
    
    $query="select email, username from eli_user_data where email='$email'";
               
    if(!$result=mysqli_query($conn, $query))
    {
        $err_msg="Email check failed";
        return -1;
    }
        
    if(mysqli_num_rows($result) > 0)
    {
        //double exsits
        $row=mysqli_fetch_assoc($result);
        if($row['username'] == "GUEST")
        {
            //this is guest user, return 3;
            $err_msg="You was a guest. Updated your profile.";
            mysqli_free_result($result);
            return 3;   

        }else
        {
            //this is sponsor user who has signup
            $err_msg="Same Email exists in user.";
            mysqli_free_result($result);
            return 1;
        }
    }
    mysqli_free_result($result);
    return 0;
}

function not_mendy_session_unset()
{
      if(isset($_SESSION['email']))
     {
        unset($_SESSION['email']);
     }
     if(isset($_SESSION['is_org']))
     {
        unset($_SESSION['is_org']);
     }
     if(isset($_SESSION['sponsor_fname']))
     {
        unset($_SESSION['sponsor_fname']);
     }
     if(isset($_SESSION['select_event_name']))
     {
        unset($_SESSION['select_event_name']);
     }
     if(isset($_SESSION['select_event_day']))
     {
        unset($_SESSION['select_event_day']);
     }
     if(isset($_SESSION['select_event_day_exp']))
     {
        unset($_SESSION['select_event_day_exp']);
     }
     if(isset($_SESSION['already']))
     {
        unset($_SESSION['already']);
     }
     if(isset($_SESSION['package_name']))
     {
        unset($_SESSION['package_name']);
     }
     if(isset($_SESSION['select_is_full']))
     {
        unset($_SESSION['select_is_full']);
     }
     if(isset($_SESSION['select_anony']))
     {
        unset($_SESSION['select_anony']);
     }
     if(isset($_SESSION['billing_amount']))
     {
        unset($_SESSION['billing_amount']);
     }
     if(isset($_SESSION['total']))
     {
        unset($_SESSION['total']);
     }
}

function get_package_price($org_name, $package_name)
{
    global $conn;
    $price=0;

    $query="SELECT item_count, item_price FROM eli_org_package WHERE org_name = '$org_name' and package_name='$package_name'";
    if($result=mysqli_query($conn, $query))
    {
        while($row=mysqli_fetch_assoc($result))
        {
            $price +=floatval($row['item_count'])*floatval($row['item_price']);
        }
        mysqli_free_result($result);
    }
    return $price;
}

function send_email_make($org_name, $org_email, $sponsor_name, $sponsor_email, $package_name, $is_half, $event_name, $event_day, $honor_txt)
{
        $query="SELECT item_count, item_desc, item_price, caterer_email FROM eli_org_package WHERE org_name = '$org_name' and package_name='$package_name'";

        $result = mysqli_query($conn,$query);

        $table_data="<table><thead><td width='10%'>QTY</td><td width='50%'>ITEMS</td></thead><tbody>";

        $caterer_email="";
        $subtotal=0;

        if($result)
        {
            while($row = mysqli_fetch_array($result)) {
                $table_data=$table_data."<tr>
                <td>".$row['item_count']."</td>
                <td>".$row['item_desc']."</td>
                </tr>";
                $subtotal+=$row['item_price']*$row['item_count'];
                if(empty($caterer_email))
                {
                    $caterer_email=$row['caterer_email'];
                }
            }
            mysqli_free_result($result);
        }

        if(empty($caterer_email))
        {
            return;
        }

        if($is_half)
        {
            $half="HALF";
            $subtotal*=0.5-5;
        }else
        {
           $half="FULL";
           $subtotal-=5;
       }

        $table_data=$table_data."</tbody></table>";
        
        //contents
        $contents ="<br>".$table_data;
        
        //send email to org user

        $to  = $org_email; // note the comma
        // subject
        $subject = 'You got sponsored for the event'.$event_name;
        // message
        $message = '
        <html>
        <head>
          <title>The event '.$event_name.' has been sponsored.</title>
        </head>
        <body>You got '.$half.' sponsored for the event '.$event_name.' on '.$event_date;
        if(!empty($honor_txt))
        {
            $message .='<p>The sponsor says:'.$honor_txt.'<p>';
        }
        $message .= '<p>Sponsored package item list as follows:'.$contents.'</p>';
        $message .='<p>type:'.$half.'</p>';
        $message .=  '<p>Get amount: $'.$subtotal.'<p></body></html>';

        // To send HTML mail, the Content-type header must be set
        $headers  = 'MIME-Version: 1.0' . "\r\n";
        $headers .= 'Content-type: text/html; charset=iso-8859-1'."\r\n";
        $headers .= "From: www.chulant.com"."\r\n";

        // Mail it
        mail($to, $subject, $message, $headers);
                        
        //send email to caterer
        if(!empty($caterer_email))
        {   
            $to = $caterer_email;
            $subject='Please confirm the '.$org_name.'event';
            $message = '
            <html>
            <head>
            <title>The event '.$event_name.' has been sponsored for '.$org_name.'.</title>
            </head>
            <body>';
            $message .= '<p>The event '.$event_name.' has been '.$half.' sponsored for '.$org_name.' on '.$event_date.'.</p>';
            $message .='<p>You should prepair the party.</p>';
            $message .='<P>Package Item list as follows:</p>'.$contents;
            $message .='<p>please confirm this event by clicking the confirm link below.</p>';
            $message .='<a href="'.PHP_PATH.'/send_confirm_email.php?action=confirm_prepare_event&org_name='.$org_name.'&org_email='.$org_email.'&sponsor_name='.$sponsor_name.'&sponsor_email='.$sponsor_email.'&event_name='.$event_name.'&event_date='.$event_date.'" target="_blank">Confirm Order</a>';
            $message .='</body></html>';

            // Mail it
            mail($to, $subject, $message, $headers);
        }

        //send email to sponsor

        $to=$sponsor_email;
        $subject="You have successfully sponsored.";
        $message = '
            <html>
            <head>
            <title>You have successfully sponsored.</title>
            </head>
            <body>';
            $message .= '<p>You have successfully sponsored the event '.$event_name.'. Please wait up to 24 hours for the caterer to confirm.</p>';
            $message .= '<p>If you have any issues, please contact your organization.<p>';
            $message .='</body></html>';
        mail($to, $subject, $message, $headers);
}


?>