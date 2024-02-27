<?php

class Merchant {
    
    public function getMerchantsReturnsIds($merchant_id,$allMerchantStatus,$ormRO)
    {
        $merchantToQuery = array();
        if ($merchant_id == 0) {
            if ($allMerchantStatus == 0) {
                $resultMerchants = $ormRO->query("SELECT id FROM merchant where status_id = 1 AND settlement_status_id = 1 AND doc_status = 17 ");
                while ($rowMerchants = $resultMerchants->fetch_assoc()) {
                    $merchantToQuery[] = $rowMerchants['id'];
                }
            } else {
                $resultMerchants = $ormRO->query("SELECT id FROM merchant ");
                while ($rowMerchants = $resultMerchants->fetch_assoc()) {
                    $merchantToQuery[] = $rowMerchants['id'];
                }
            }
        } else {
            $resultMerchants = $ormRO->query("SELECT id FROM merchant where id = " . $merchant_id);
            while ($rowMerchants = $resultMerchants->fetch_assoc()) {
                $merchantToQuery[] = $rowMerchants['id'];
            }
        }

        return $merchantToQuery;
    }
}