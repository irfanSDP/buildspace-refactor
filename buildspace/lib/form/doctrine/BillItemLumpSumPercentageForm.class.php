<?php

/**
 * BillItemLumpSumPercentage form.
 *
 * @package    buildspace
 * @subpackage form
 * @author     1337 developers
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class BillItemLumpSumPercentageForm extends BaseBillItemLumpSumPercentageForm
{
    public function configure()
    {
        parent::configure();

        unset($this['bill_item_id'], $this['deleted_at'], $this['created_at'], $this['updated_at']);
    }

    public function doSave($conn = null)
    {
        parent::doSave($conn);

        $object = $this->object;

        $object->refresh();

        $formulatedColumnTable = Doctrine_Core::getTable('BillItemFormulatedColumn');

        $formulatedColumn = $formulatedColumnTable->getByRelationIdAndColumnName($object->bill_item_id, BillItem::FORMULATED_COLUMN_RATE);

        $formulatedColumn->setFormula($object->amount);

        $formulatedColumn->linked = false;
        $formulatedColumn->has_build_up = false;

        $formulatedColumn->save($conn);
    }
}
