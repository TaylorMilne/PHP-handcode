<?php

// # Get Sale sample 
// Sale transactions are nothing but completed payments.
// This sample code demonstrates how you can retrieve 
// details of completed Sale Transaction.
// API used: /v1/payments/sale/{sale-id}
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

function refund($refund_amount, $org_email, $sponsor_email)
{
    global $conn, $apiContext;
    
    $query1="select credit_card_number, cv_code, expire_date, card_holder from eli_org_data where email='$org_email'";
    $query2="select bank_account_email from eli_user_data where email='$sponsor_email'";
    if(($result1=mysqli_query($conn, $query1)) && ($result2=mysqli_query($conn, $query2)))
    {
        //get org user's credit card info
        $row1=mysqli_fetch_assoc($result1);
        
        $credit_card_number=encrypt_decrypt(2, $row1['credit_card_number']);

       //error
       $credit_card_number=str_replace("-", "", $credit_card_number);

       $cv_code=$row1['cv_code'];

       $expire_date=$row1['expire_date'];
       $expire_date=preg_split("/-/", $expire_date);
       $expire_year=$expire_date[0];
       $expire_month=$expire_date[1];

       
       $name=$row1['card_holder'];
       $name=preg_split("/[\s,]+/", $name);
       $firstname=$name[0];
       if(isset($name[1]))
        $lastname=$name[1];
       else
        $lastname=" ";        

        mysqli_free_result($result1);

        //get sponsor's bank account email
        $row2=mysqli_fetch_assoc($result2);
        $sponsor_bank_account_email=$row2['bank_account_email'];
        mysqli_free_result($result2);

        

        //get refund from org user's credit card
       $card_type=check_cc($credit_card_number);

       $card = new CreditCard();

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

       $item->setName("Cancel Event")
           ->setDescription("You canceled the event, so you should refund.")
           ->setCurrency('USD')
           ->setQuantity(1)
           ->setPrice($refund_amount);

       $itemList = new ItemList();
       $itemList->setItems(array($item));

       // Lets you specify a payment amount.
       // You can also specify additional details
       // such as shipping, tax.
       $amount = new Amount();
       $amount->setCurrency("USD")
           ->setTotal($refund_amount);
           
       // ### Transaction
       // A transaction defines the contract of a
       // payment - what is the payment for and who
       // is fulfilling it. 
       $transaction = new Transaction();
       $transaction->setAmount($amount)
           ->setItemList($itemList)
           ->setDescription("Refund Description")
           ->setInvoiceNumber(uniqid());

       // ### Payment
       // A Payment Resource; create one using
       // the above types and intent set to sale 'sale'
       $payment = new Payment();
       $payment->setIntent("sale")
           ->setPayer($payer)
           ->setTransactions(array($transaction));


        $request=clone $payment;
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
           $saleid = $sale->getId(); 
           
        } catch (Exception $ex) {
          // NOTE: PLEASE DO NOT USE RESULTPRINTER CLASS IN YOUR ORIGINAL CODE. FOR SAMPLE ONLY
          //ResultPrinter::printError('Create Payment Using Credit Card. If 500 Exception, try creating a new Credit Card using <a href="https://ppmts.custhelp.com/app/answers/detail/a_id/750">Step 4, on this link</a>, and using it.', 'Payment', null, $request, $ex);
          return false;
       }

       //Reset the Request Id as the apiContext is reused, and needs to generate a new Idempotent Request Id
       $apiContext->resetRequestId();
       
        //now you get refund from org user succefully.
        //now payout the refund money to sponsor


      $payouts = new \PayPal\Api\Payout();

       $senderBatchHeader = new \PayPal\Api\PayoutSenderBatchHeader();
       // ### NOTE:
       // You can prevent duplicate batches from being processed. If you specify a `sender_batch_id` that was used in the last 30 days, the batch will not be processed. For items, you can specify a `sender_item_id`. If the value for the `sender_item_id` is a duplicate of a payout item that was processed in the last 30 days, the item will not be processed.

       // #### Batch Header Instance
       $senderBatchHeader->setSenderBatchId(uniqid())
           ->setEmailSubject("You got refund from the event you sponsored!");

       // #### Sender Item
       // Please note that if you are using single payout with sync mode, you can only pass one Item in the request
       $senderItem = new \PayPal\Api\PayoutItem();

       $refund=round(0.971*$refund_amount+4.4)/1.029;
       
       $senderItem->setRecipientType('Email')
           ->setNote('You got refund.')
           ->setReceiver($sponsor_bank_account_email)
           ->setAmount(new \PayPal\Api\Currency('{
                               "value":"'.$refund.'",
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
          return false;
       }
       return true;

    }else{
        return false;
    }
}


/*
$z=round(((((($payout+0.3)/0.971)+5)/0.9113)+0.3)/0.971, 2);
$refund=$z*0.971-0.058*$payout-0.9;
*/
?>
