<?php
// # CreatePaymentSample
//
// This sample code demonstrate how you can process
// a direct credit card payment. Please note that direct 
// credit card payment and related features using the 
// REST API is restricted in some countries.
// API used: /v1/payments/payment
require __DIR__ . '/bootstrap.php';
use PayPal\Api\Amount;
use PayPal\Api\CreditCard;
use PayPal\Api\Details;
use PayPal\Api\FundingInstrument;
use PayPal\Api\Item;
use PayPal\Api\ItemList;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\Transaction;

// ### CreditCard
// A resource representing a credit card that can be
// used to fund a payment.

//get receiver email first
//$receiver_bank_email=$_SESSION['bank_email'];
//receiver_bank_email=bank_account_number
$org_email=$_SESSION['select_org_email'];
$org_name=$_SESSION['select_org'];

$receiver_bank_email="";
$query="select bank_account_email from eli_org_data where email='$org_email' and org_name='$org_name'";
if($result=mysqli_query($conn, $query))
 {
    $row=mysqli_fetch_assoc($result);
    $receiver_bank_email=$row['bank_account_email'];
    mysqli_free_result($result);
 }
//$receiver_bank_email="mirjandes@outlook.com";
if(empty($receiver_bank_email))
{
  $_SESSION['err_msg']="Get Org bank account failed.";
  return false;
}

//this is Sponsor user's credit card
//get credit card info
$sponsor_email=$_SESSION['email'];

$query="select credit_card_number, cv_code, expire_date, card_holder from eli_user_data where email='$sponsor_email'";

$type=$credit_card_number=$expire_year=$expire_month=$firstname=$lastname="";
   
if($result=mysqli_query($conn, $query))
{
   $row=mysqli_fetch_assoc($result);
   
   $credit_card_number=encrypt_decrypt(2, $row['credit_card_number']);

   //error
   $credit_card_number=str_replace("-", "", $credit_card_number);

   $cv_code=$row['cv_code'];

   $expire_date=$row['expire_date'];
   $expire_date=preg_split("/-/", $expire_date);
   $expire_year=$expire_date[0];
   $expire_month=$expire_date[1];

   
   $name=$row['card_holder'];
   $name=preg_split("/[\s,]+/", $name);
   $firstname=$name[0];
   if(isset($name[1]))
    $lastname=$name[1];
   else
    $lastname=" ";

   mysqli_free_result($result);
   
   $card_type=check_cc($credit_card_number);

   $card = new CreditCard();

   /*$card->setType("visa")
       ->setNumber("4148529247832259")
       ->setExpireMonth("11")
       ->setExpireYear("2019")
       ->setCvv2("010")
       ->setFirstName("Joe")
       ->setLastName("Shopper");*/

   $card->setType($card_type)
       ->setNumber($credit_card_number)
       ->setExpireMonth($expire_month)
       ->setExpireYear($expire_year)
       ->setCvv2($cv_code)
       ->setFirstName($firstname)
       ->setLastName($lastname);



   // ### FundingInstrument
   // A resource representing a Payer's funding instrument.
   // For direct credit card payments, set the CreditCard
   // field on this object.
   $fi = new FundingInstrument();
   $fi->setCreditCard($card);

   // ### Payer
   // A resource representing a Payer that funds a payment
   // For direct credit card payments, set payment method
   // to 'credit_card' and add an array of funding instruments.
   $payer = new Payer();
   $payer->setPaymentMethod("credit_card")
       ->setFundingInstruments(array($fi));

   // ### Itemized information
   // (Optional) Lets you specify item wise
   // information
   $item = new Item();

   //price calc
   $payout=$_SESSION['billing_amount']-5;
   $total=$_SESSION['total'];
   
   $service_fee=$total-$payout;
      
   $item->setName($_SESSION['package_name'])
       ->setDescription("This package is for ".$_SESSION['select_event_name'])
       ->setCurrency('USD')
       ->setQuantity(1)
       ->setPrice($payout);

   $itemList = new ItemList();
   $itemList->setItems(array($item));

   // ### Additional payment details
   // Use this optional field to set additional
   // payment information such as tax, shipping
   // charges etc.
   $details = new Details();
   $details ->setTax($service_fee)
      ->setSubtotal($payout);

   //get service fee: 5$ per event

   // ### amount
   // Lets you specify a payment amount.
   // You can also specify additional details
   // such as shipping, tax.
   $amount = new Amount();
   $amount->setCurrency("USD")
       ->setTotal($total)
       ->setDetails($details);

   // ### Transaction
   // A transaction defines the contract of a
   // payment - what is the payment for and who
   // is fulfilling it. 
   $transaction = new Transaction();
   $transaction->setAmount($amount)
       ->setItemList($itemList)
       ->setDescription("Payment description")
       ->setInvoiceNumber(uniqid());

   // ### Payment
   // A Payment Resource; create one using
   // the above types and intent set to sale 'sale'
   $payment = new Payment();
   $payment->setIntent("sale")
       ->setPayer($payer)
       ->setTransactions(array($transaction));

   // For Sample Purposes Only.
   $request = clone $payment;

   // ### Create Payment
   // Create a payment by calling the payment->create() method
   // with a valid ApiContext (See bootstrap.php for more on `ApiContext`)
   // The return object contains the state.
   try {
       $payment->create($apiContext);

       //remember the transaction id for after refund
       $transactions = $payment->getTransactions();
       $relatedResources = $transactions[0]->getRelatedResources();
       $sale = $relatedResources[0]->getSale();
       $saleId = $sale->getId();
       
   } catch (Exception $ex) {
       // NOTE: PLEASE DO NOT USE RESULTPRINTER CLASS IN YOUR ORIGINAL CODE. FOR SAMPLE ONLY
      //ResultPrinter::printError('Create Payment Using Credit Card. If 500 Exception, try creating a new Credit Card using <a href="https://ppmts.custhelp.com/app/answers/detail/a_id/750">Step 4, on this link</a>, and using it.', 'Payment', null, $request, $ex);
      $_SESSION['err_msg'] = "Get Paid from you failed.";
      return false;
   }

   // NOTE: PLEASE DO NOT USE RESULTPRINTER CLASS IN YOUR ORIGINAL CODE. FOR SAMPLE ONLY
   //ResultPrinter::printResult('Create Payment Using Credit Card', 'Payment', $payment->getId(), $request, $payment);
   // # Create Single Synchronous Payout Sample
   //
   // This sample code demonstrate how you can create a synchronous payout sample, as documented here at:
   // https://developer.paypal.com/docs/integration/direct/create-single-payout/
   // API used: /v1/payments/payouts?sync_mode=true

   // Create a new instance of Payout object
   $payouts = new \PayPal\Api\Payout();

   // This is how our body should look like:
   /*
    * {
               "sender_batch_header":{
                   "sender_batch_id":"2014021801",
                   "email_subject":"You have a Payout!"
               },
               "items":[
                   {
                       "recipient_type":"EMAIL",
                       "amount":{
                           "value":"1.0",
                           "currency":"USD"
                       },
                       "note":"Thanks for your patronage!",
                       "sender_item_id":"2014031400023",
                       "receiver":"shirt-supplier-one@mail.com"
                   }
               ]
           }
    */

   $senderBatchHeader = new \PayPal\Api\PayoutSenderBatchHeader();
   // ### NOTE:
   // You can prevent duplicate batches from being processed. If you specify a `sender_batch_id` that was used in the last 30 days, the batch will not be processed. For items, you can specify a `sender_item_id`. If the value for the `sender_item_id` is a duplicate of a payout item that was processed in the last 30 days, the item will not be processed.

   // #### Batch Header Instance
   $senderBatchHeader->setSenderBatchId(uniqid())
       ->setEmailSubject("You have a Payout!");

   // #### Sender Item
   // Please note that if you are using single payout with sync mode, you can only pass one Item in the request
   $senderItem = new \PayPal\Api\PayoutItem();

   $senderItem->setRecipientType('Email')
       ->setNote('You sent money')
       ->setReceiver($receiver_bank_email)
       ->setAmount(new \PayPal\Api\Currency('{
                           "value":"'.$payout.'",
                           "currency":"USD"
                       }'));

   $payouts->setSenderBatchHeader($senderBatchHeader)
       ->addItem($senderItem);


   // For Sample Purposes Only.
   $request = clone $payouts;

   // ### Create Payout
   try {
       $output = $payouts->createSynchronous($apiContext);

   } catch (Exception $ex) {
       // NOTE: PLEASE DO NOT USE RESULTPRINTER CLASS IN YOUR ORIGINAL CODE. FOR SAMPLE ONLY
       //ResultPrinter::printError("Created Single Synchronous Payout", "Payout", null, $request, $ex);
       $_SESSION['err_msg'] = "Failed payout to org user.";
       return false;
   }

   // NOTE: PLEASE DO NOT USE RESULTPRINTER CLASS IN YOUR ORIGINAL CODE. FOR SAMPLE ONLY
   //ResultPrinter::printResult("Created Single Synchronous Payout", "Payout", $output->getBatchHeader()->getPayoutBatchId(), $request, $output);
   //$saleId2=$output->getBatchHeader()->getPayoutBatchId();
   
   $ids=array();
   //array_push($ids, $saleId1, $saleId2);
   array_push($ids, $saleId, $payout);
   //echo $saleId1, $saleId2;
   return $ids;

}else
{
   $_SESSION['err_msg'] = "Get your financial failed. Please try again later";
   return false;
}


