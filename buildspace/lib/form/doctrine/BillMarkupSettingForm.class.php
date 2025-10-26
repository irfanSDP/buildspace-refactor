<?php

/**
 * BillMarkupSetting form.
 *
 * @package    buildspace
 * @subpackage form
 * @author     1337 developers
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class BillMarkupSettingForm extends BaseBillMarkupSettingForm
{
    public function configure()
    {
        parent::configure();

        unset($this['project_structure_id'], $this['deleted_at'], $this['created_at'], $this['updated_at']);

        switch($this->getOption('type'))
        {
            case 'bill':
                unset($this['element_markup_enabled'], $this['item_markup_enabled'], $this['rounding_type']);
                break;
            case 'element':
                unset($this['bill_markup_enabled'], $this['bill_markup_percentage'], $this['bill_markup_amount'], $this['item_markup_enabled'], $this['rounding_type']);
                break;
            case 'item':
                unset($this['bill_markup_enabled'], $this['bill_markup_percentage'], $this['bill_markup_amount'], $this['element_markup_enabled'], $this['rounding_type']);
                break;
            case 'rounding':
                unset($this['bill_markup_enabled'], $this['bill_markup_percentage'], $this['bill_markup_amount'], $this['element_markup_enabled'], $this['item_markup_enabled']);
                break;
            default:
                break;
        }
    }

    public function doSave($conn = null)
    {
        $markupAmount = $this->object->isNew() ? 0 : $this->object->bill_markup_amount;
        $lastRoundingType = $this->object->rounding_type;
        $lastElementMarkupSetting = $this->object->element_markup_enabled;
        $lastItemMarkupSetting = $this->object->item_markup_enabled;

        parent::doSave($conn);

        //Update total column after markup if there is any change
        if(!$this->object->isNew() && (($lastRoundingType != $this->object->rounding_type) || ($lastElementMarkupSetting != $this->object->element_markup_enabled) || ($lastItemMarkupSetting != $this->object->item_markup_enabled)))
        {
            $con = $this->object->ProjectStructure->getTable()->getConnection();
            
            try
            {
                $con->beginTransaction();
                $this->object->ProjectStructure->updateAllItemTotalAfterMarkup();
                $con->commit();
            }
            catch(Exception $e)
            {
                $con->rollback();
                return $e->getMessage();
            }
            
        }

        /*
         * TODO: update markup amount when based on grand total (this to make sure markup amount would be accurate with latest total amount)
         *
        if($markupAmount != $this->object->bill_markup_amount)
        {
            $markupPercentage = $this->object->bill_markup_amount != 0 ? $this->object->bill_markup_amount / $grandTotal * 100 : 0;
            $this->object->bill_markup_percentage = $markupPercentage;
        }
        else
        {
            $markupAmount = $grandTotal * ($this->object->bill_markup_percentage / 100);
            $this->object->bill_markup_amount = $markupAmount;
        }
        $this->object->save($conn);

        */
    }
}
