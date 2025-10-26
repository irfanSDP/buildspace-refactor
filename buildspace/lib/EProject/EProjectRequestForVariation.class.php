<?php

class EProjectRequestForVariation extends BaseEProjectRequestForVariation
{
    const STATUS_NEW_RFV = 0;
    const STATUS_PENDING_COST_ESTIMATE = 1;
    const STATUS_PENDING_VERIFICATION = 2;
    const STATUS_VERIFIED = 3;
    const STATUS_PENDING_APPROVAL = 4;
    const STATUS_APPROVED = 5;
    const STATUS_REJECTED = 6;

    const STATUS_NEW_RFV_TEXT = "New Request for Variation";
    const STATUS_PENDING_COST_ESTIMATE_TEXT = 'Pending Cost Estimate';
    const STATUS_PENDING_VERIFICATION_TEXT = 'Pending Verification';
    const STATUS_VERIFIED_TEXT = 'Verified';
    const STATUS_PENDING_APPROVAL_TEXT = 'Pending Approval';
    const STATUS_APPROVED_TEXT = 'Approved';
    const STATUS_REJECTED_TEXT = 'Rejected';

    public function hasClaimCertificate()
    {
        $pdo = VariationOrderTable::getInstance()->getConnection()->getDbh();

        $stmt = $pdo->prepare("SELECT COUNT(vo.id)
        FROM ".VariationOrderTable::getInstance()->getTableName()." vo
        JOIN ".VariationOrderClaimCertificateTable::getInstance()->getTableName()." xref on xref.variation_order_id = vo.id
        JOIN ".ClaimCertificateTable::getInstance()->getTableName()." c on xref.claim_certificate_id = c.id
        JOIN ".PostContractClaimRevisionTable::getInstance()->getTableName()." r on c.post_contract_claim_revision_id = r.id
        WHERE vo.eproject_rfv_id = {$this->id}
        AND vo.eproject_rfv_id IS NOT NULL
        AND vo.deleted_at IS NULL");

        $stmt->execute();
        
        return ($stmt->fetch(PDO::FETCH_COLUMN, 0) > 0);
    }
}
