<?php

class SettlementTerm {
    
    public function getPaymentChannels($settlement_term_id,$ormRO)
    {
        $resultSettlementTerm = $ormRO->query("SELECT payment_channels FROM settlement_term where id = " . $settlement_term_id);
        $payment_channels = '';
        while ($rowSettlementTerm = $resultSettlementTerm->fetch_assoc()) {
            $payment_channels = $rowSettlementTerm['payment_channels'];
        }

        return $payment_channels;
    }
}