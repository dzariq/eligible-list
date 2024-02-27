<?php

class Payment {
    
    public function newRecord($dataInsert,$settlement_term_id,$date_cutoff,$ormDB)
    {
      
        foreach ($dataInsert as $key => $data) {
            $ormDB->delete('payment', 'merchant_id = ' . $key . ' AND status_id = 2 AND settlement_term_id = ' . $settlement_term_id . ' AND date_cutoff = ' . $date_cutoff);
            $ormDB->insert(
                'payment',
                array(
                    'merchant_id' => $key,
                    'settlement_term_id' => $settlement_term_id,
                    'unpaid_charges' => $data['unpaid_charges'],
                    'date_cutoff' => $data['date_cutoff'],
                    'amount' => $data['amount'],
                    'fee' => $data['fee'],
                    'gst' => $data['gst'],
                    'status_id' => $data['status_id'],
                    'transactions' => json_encode($data['transactions']),
                    'details' => json_encode($data['details'])
                )
            );
        }
 
    }
}