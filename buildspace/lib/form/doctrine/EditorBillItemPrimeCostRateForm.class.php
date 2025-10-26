<?php

/**
 * EditorBillItemPrimeCostRate form.
 *
 * @package    buildspace
 * @subpackage form
 * @author     1337 developers
 * @version    SVN: $Id$
 */
class EditorBillItemPrimeCostRateForm extends BaseEditorBillItemPrimeCostRateForm
{
    public function configure()
    {
        parent::configure();

        unset($this['bill_item_info_id'], $this['created_at'], $this['updated_at']);

        $this->widgetSchema->setNameFormat('bill_item_prime_cost_rate[%s]');
    }
}
