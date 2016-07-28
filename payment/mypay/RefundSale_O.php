<?php

// # Get Sale sample 
// Sale transactions are nothing but completed payments.
// This sample code demonstrates how you can retrieve 
// details of completed Sale Transaction.
// API used: /v1/payments/sale/{sale-id}
require __DIR__ . '/bootstrap.php';
use PayPal\Api\Payment;
use PayPal\Api\Sale;
use PayPal\Api\Amount;
use PayPal\Api\Refund;

echo "start refund_sale.php<br>";

// ### Get Sale From Created Payment
// You can retrieve the sale Id from Related Resources for each transactions.
$event_date = strval(test_input($_GET['event_date']));
$event_date=mysqli_real_escape_string($conn, $event_date);

$email=test_input($_SESSION['email']);
$email=mysqli_real_escape_string($conn, $email);

//is_full!=-1

$rt=true;

$query="select get_tr_id, send_tr_id from eli_spon_data where org_email='$email' and event_date='$event_date' and is_full<>-1";
if($result1=mysqli_query($conn, $query))
{
    while($row=mysqli_fetch_assoc($result1))
    {
        //$send_tr_id=$row['send_tr_id'];
        //$r1=true;
        //if(!empty($send_tr_id))
            //$r1=refund_tr_id($send_tr_id);
                
        $get_tr_id=$row['get_tr_id'];
        if(!empty($get_tr_id))
            $r2=refund_tr_id($get_tr_id);
                
        //if(!$r1 || !$r2)
        if(!$r2)
          $rt=false;
    }
    mysqli_free_result($result1);
}

return $rt;


function refund_tr_id($id)
{
    global $clientId, $clientSecret;
    $saleId=$id;
    
    // ### Refund amount
    // Includes both the refunded amount (to Payer) 
    // and refunded fee (to Payee). Use the $amt->details
    // field to mention fees refund details.
    //$saleId="7A052622ET340680Y";
    //$saleId="91600172G1674652W";
    $saleId=$id;

    $amt = new Amount();
    $amt->setCurrency('USD');
        
    // ### Refund object
    $refund = new Refund();
    $refund->setAmount($amt);

    // ###Sale
    // A sale transaction.
    // Create a Sale object with the
    // given sale transaction id.
    $sale = new Sale();
    $sale->setId($saleId);

    $refundedSale=new Refund();

    try {
        // Create a new apiContext object so we send a new
        // PayPal-Request-Id (idempotency) header for this resource
        $apiContext = getApiContext($clientId, $clientSecret);
        
        // Refund the sale
        // (See bootstrap.php for more on `ApiContext`)
        //$refundedSale = $sale->refund($refund, $apiContext);
        $refundedSale=$refund->get($saleId, $apiContext);
        
    } catch (Exception $ex) {
        // NOTE: PLEASE DO NOT USE RESULTPRINTER CLASS IN YOUR ORIGINAL CODE. FOR SAMPLE ONLY
        ResultPrinter::printError("Refund Sale", "Sale", $refundedSale->getId(), $refund, $ex);
        exit(1);
    }

    // NOTE: PLEASE DO NOT USE RESULTPRINTER CLASS IN YOUR ORIGINAL CODE. FOR SAMPLE ONLY
    //ResultPrinter::printResult("Refund Sale", "Sale", $refundedSale->getId(), $refund, $refundedSale);
    $refund_sucess_id=$refundedSale->getId();
    if(empty($refund_sucess_id))
        return false;
    else 
        return true;
}

/*
$z=round(((((($payout+0.3)/0.971)+5)/0.9113)+0.3)/0.971, 2);
$refund=$z*0.971-0.058*$payout-0.9;
*/
?>
