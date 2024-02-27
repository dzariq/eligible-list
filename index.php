<?php

require_once __DIR__ . '/vendor/autoload.php'; // Include PHPExcel and PHPMailer autoloaders
require 'orm.php';
require 'sendMail.php';
require 'excel_generator.php';
require 'models/merchant.php';
require 'models/settlement_term.php';
require 'models/transaction.php';
require 'models/payment.php';
require 'mns.php';

function handler($event, $context)
{

    $logger = $GLOBALS['fcLogger'];
    $eventJson = json_decode($event, true);
    if (isset($eventJson['Message'])) {
        $logger->info(sprintf("MessageBody is: %s ,MessageID is: %s", $eventJson['Message'], $eventJson['MessageId']));
        $dataEvent = json_decode($eventJson['Message']);
        $date_cutoff = $dataEvent->date_cutoff;
        $settlement_term_id = $dataEvent->settlement_term_id;
        $merchant_id = isset($dataEvent->merchant_id) ? $dataEvent->merchant_id : 0;
        $allMerchantStatus = isset($dataEvent->all) ? $dataEvent->all : 0;

        // Create an instance of the ORM class
        $ormRO = new ORM(getenv('DB_RO_HOST'), getenv('DB_RO_USER'), getenv('DB_RO_PASS'), getenv('DB_RO_DATABASE'));
        $ormDB = new ORM(getenv('DB_HOST'), getenv('DB_USER'), getenv('DB_PASS'), getenv('DB_DATABASE'));

        $merchantModel = new Merchant;
        $merchantToQuery = $merchantModel->getMerchantsReturnsIds($merchant_id, $allMerchantStatus, $ormRO);

        //get settlement term channels
        $settlementTermModel = new SettlementTerm;
        $payment_channels = $settlementTermModel->getPaymentChannels($settlement_term_id,$ormRO);

        $transactionMmodel = new Transaction;
        $dataInsert = $transactionMmodel->getTransactions($payment_channels,$merchantToQuery,$date_cutoff,$ormRO,$logger);

        $paymentModel = new Payment;
     //   $paymentModel->newRecord($dataInsert,$settlement_term_id,$date_cutoff,$ormDB);

        $excelGenerator = new ExcelGenerator;
        $file = $excelGenerator->generate($dataInsert,$date_cutoff,$logger);
     
        $mail = new MAIL;
        $mail->sendEmailWithAttachment($file);

        $mns = new MNS;
        $mns->publish('batch-payment',array(
            'payment_ids' => [106]
        ),$logger);

        return sprintf("MessageBody is: %s ,MessageID is: %s", $eventJson['Message'], $eventJson['MessageId']);
    }
}
