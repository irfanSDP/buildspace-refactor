<?php

/**
 * ClaimCertificatePrintSetting form.
 *
 * @package    buildspace
 * @subpackage form
 * @author     1337 developers
 * @version    SVN: $Id$
 */
class ClaimCertificatePrintSettingForm extends BaseClaimCertificatePrintSettingForm
{
    public function configure()
    {
        parent::configure();
        unset($this['post_contract_id'], $this['created_by'], $this['updated_by'], $this['created_at'], $this['updated_at']);
    }
}
