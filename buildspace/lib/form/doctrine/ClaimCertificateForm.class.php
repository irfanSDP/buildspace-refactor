<?php

/**
 * ClaimCertificate form.
 *
 * @package    buildspace
 * @subpackage form
 * @author     1337 developers
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class ClaimCertificateForm extends BaseClaimCertificateForm
{
    public function configure()
    {
        parent::configure();

        unset($this['post_contract_claim_revision_id'], $this['status'], $this['created_by'], $this['updated_by'], $this['created_at'], $this['updated_at']);
    }

    public function doSave($con = null)
    {
        if($this->object->isNew())
        {
            $this->object->status = ClaimCertificate::STATUS_TYPE_IN_PROGRESS;
        }

        return parent::doSave($con);
    }
}
