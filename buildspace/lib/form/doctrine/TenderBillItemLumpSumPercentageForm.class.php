<?php

/**
 * TenderBillItemLumpSumPercentage form.
 *
 * @package    buildspace
 * @subpackage form
 * @author     1337 developers
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class TenderBillItemLumpSumPercentageForm extends BaseTenderBillItemLumpSumPercentageForm
{
  public function configure()
  {
        unset($this['tender_bill_item_rate_id'], $this['deleted_at'], $this['created_at'], $this['updated_at']);
  }

  public function doSave($conn = null)
  {
      parent::doSave($conn);

      $object = $this->object;

      $object->refresh();

      $tenderBillItemRate = Doctrine_Core::getTable('TenderBillItemRate')->find($object->tender_bill_item_rate_id);

      $tenderBillItemRate->rate = $object->amount;

      $tenderBillItemRate->save($conn);
  }
}
