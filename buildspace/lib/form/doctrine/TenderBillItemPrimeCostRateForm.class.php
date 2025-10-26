<?php

/**
 * TenderBillItemPrimeCostRate form.
 *
 * @package    buildspace
 * @subpackage form
 * @author     1337 developers
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class TenderBillItemPrimeCostRateForm extends BaseTenderBillItemPrimeCostRateForm
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

      $tenderBillItemRate->rate = $object->total;

      $tenderBillItemRate->save($conn);
  }
}
