<?php

class EProjectClaimCertificatePaymentTable extends Doctrine_Table
{
    public static function getInstance()
    {
        return Doctrine_Core::getTable('EProjectClaimCertificatePayment');
    }

    public static function getPaidAmount($claimCertificateId)
    {
        $results = DoctrineQuery::create()->select('p.amount')->from('EProjectClaimCertificatePayment p')
            ->where('p.claim_certificate_id = ?', $claimCertificateId)
            ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
            ->execute();

        $paidAmount = 0;

        foreach($results as $result)
        {
            $paidAmount += ($result['amount']);
        }

        return $paidAmount;
    }
}