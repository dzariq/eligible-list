<?php

class Transaction
{

    public function getTransactions($payment_channels, $merchantToQuery, $date_cutoff, $ormRO,$logger)
    {
        $sqlPaymentChannels = '';
        if ($payment_channels != '') {
            $sqlPaymentChannels = 'AND payment_type IN ("' . $payment_channels . '") ';
        }

        $merchantQueryString = implode(',', $merchantToQuery);

        $result = $ormRO->query("SELECT merchant_id, (SELECT form_code from merchant where id = merchant_id) as merchant_code,
        (SELECT NAME FROM status WHERE id = (SELECT doc_status from merchant where id = merchant_id )) as doc_status,
        (SELECT name from merchant where id = merchant_id ) as name,
        (SELECT header_name from merchant where id = merchant_id ) as header_name,
        (SELECT header_email from merchant where id = merchant_id ) as header_email,
        (SELECT bank from bank where id = (SELECT bank_id from merchant where id = merchant_id )) as bank_name,
        (SELECT bank_account_no from merchant where id = merchant_id ) as bank_account_no,
        (SELECT bank_account_name from merchant where id = merchant_id ) as bank_account_name,
        (SELECT DATE_FORMAT(FROM_UNIXTIME(date_cutoff),'%d-%m-%y') from payment where status_id = 4 AND merchant_id = t.merchant_id ORDER BY id DESC LIMIT 1) as last_payment_date,
        merchant_id,payment_type,source,SUM(price_total) as gross_profit,SUM(mdr_value) AS total_mdr,COUNT(id) AS total,
        group_concat(transaction_reference) transaction_references,
        group_concat(mdr_rate) mdr_rates,
        group_concat(CONCAT(DAY,'-',MONTH,'-',YEAR)) transaction_dates,
        (SELECT SUM(amount+sst) FROM charges WHERE payment_id = 0 AND date_created < " . $date_cutoff . " AND
        status_id = 3 AND settlement_deductible = 1 AND merchant_id = t.merchant_id) AS total_charges,
        COALESCE(SUM(price_total)-SUM(mdr_value),0) AS nett_total from transaction t where payment_id = 0 AND
        merchant_id IN (" . $merchantQueryString . ") AND
        settlement_status_id = 1
        " . $sqlPaymentChannels . " AND
        status_id = 4 AND date_created < " . $date_cutoff . "
        GROUP BY merchant_id,payment_type,source");

        $logger->info("SELECT merchant_id, (SELECT form_code from merchant where id = merchant_id) as merchant_code,
        (SELECT NAME FROM status WHERE id = (SELECT doc_status from merchant where id = merchant_id )) as doc_status,
        (SELECT name from merchant where id = merchant_id ) as name,
        (SELECT header_name from merchant where id = merchant_id ) as header_name,
        (SELECT header_email from merchant where id = merchant_id ) as header_email,
        (SELECT bank from bank where id = (SELECT bank_id from merchant where id = merchant_id )) as bank_name,
        (SELECT bank_account_no from merchant where id = merchant_id ) as bank_account_no,
        (SELECT bank_account_name from merchant where id = merchant_id ) as bank_account_name,
        (SELECT DATE_FORMAT(FROM_UNIXTIME(date_cutoff),'%d-%m-%y') from payment where status_id = 4 AND merchant_id = t.merchant_id ORDER BY id DESC LIMIT 1) as last_payment_date,
        merchant_id,payment_type,source,SUM(price_total) as gross_profit,SUM(mdr_value) AS total_mdr,COUNT(id) AS total,
        group_concat(transaction_reference) transaction_references,
        group_concat(mdr_rate) mdr_rates,
        group_concat(CONCAT(DAY,'-',MONTH,'-',YEAR)) transaction_dates,
        (SELECT SUM(amount+sst) FROM charges WHERE payment_id = 0 AND date_created < " . $date_cutoff . " AND
        status_id = 3 AND settlement_deductible = 1 AND merchant_id = t.merchant_id) AS total_charges,
        COALESCE(SUM(price_total)-SUM(mdr_value),0) AS nett_total from transaction t where payment_id = 0 AND
        merchant_id IN (" . $merchantQueryString . ") AND
        settlement_status_id = 1
        " . $sqlPaymentChannels . " AND
        status_id = 4 AND date_created < " . $date_cutoff . "
        GROUP BY merchant_id,payment_type,source");

        // Fetch and output results
        $dataInsert = array();

        while ($row = $result->fetch_assoc()) {
            $dataInsert[$row['merchant_id']]['merchant_code'] = $row['merchant_code'];
            $dataInsert[$row['merchant_id']]['header_email'] = $row['header_email'];
            $dataInsert[$row['merchant_id']]['doc_status'] = $row['doc_status'];
            $dataInsert[$row['merchant_id']]['name'] = $row['name'];
            $dataInsert[$row['merchant_id']]['header_name'] = $row['header_name'];
            $dataInsert[$row['merchant_id']]['bank_name'] = $row['bank_name'];
            $dataInsert[$row['merchant_id']]['bank_account_no'] = $row['bank_account_no'];
            $dataInsert[$row['merchant_id']]['bank_account_name'] = $row['bank_account_name'];
            $dataInsert[$row['merchant_id']]['last_payment_date'] = $row['last_payment_date'];

            $dataInsert[$row['merchant_id']]['unpaid_charges'] = $row['total_charges'] ? $row['total_charges'] : 0;
            $dataInsert[$row['merchant_id']]['date_cutoff'] = $date_cutoff;


            if (!isset($dataInsert[$row['merchant_id']]['gross_profit']))
                $dataInsert[$row['merchant_id']]['gross_profit'] = $row['gross_profit'];
            else
                $dataInsert[$row['merchant_id']]['gross_profit'] += $row['gross_profit'];

            if (!isset($dataInsert[$row['merchant_id']]['amount']))
                $dataInsert[$row['merchant_id']]['amount'] = $row['nett_total'];
            else
                $dataInsert[$row['merchant_id']]['amount'] += $row['nett_total'];

            if (!isset($dataInsert[$row['merchant_id']]['fee']))
                $dataInsert[$row['merchant_id']]['fee'] = $row['total_mdr'];
            else
                $dataInsert[$row['merchant_id']]['fee'] += $row['total_mdr'];

            $dataInsert[$row['merchant_id']]['gst'] = 0;
            $dataInsert[$row['merchant_id']]['status_id'] = 2;
            $dataInsert[$row['merchant_id']]['transactions'][] = $row['transaction_references'];
            $dataInsert[$row['merchant_id']]['details'][] = $row;

            foreach ($dataInsert as &$merchantPaymentData) {
                $merchantPaymentData['amount'] -= $merchantPaymentData['unpaid_charges'];
            }

        }
        return $dataInsert;
    }
}