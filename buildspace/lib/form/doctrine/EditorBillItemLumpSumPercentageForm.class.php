<?php

/**
 * EditorBillItemLumpSumPercentage form.
 *
 * @package    buildspace
 * @subpackage form
 * @author     1337 developers
 * @version    SVN: $Id$
 */
class EditorBillItemLumpSumPercentageForm extends BaseEditorBillItemLumpSumPercentageForm
{
    public function configure()
    {
        parent::configure();

        unset($this['bill_item_info_id'], $this['created_at'], $this['updated_at']);

        $this->widgetSchema->setNameFormat('bill_item_lump_sum_percentage[%s]');
    }

    public function doSave($conn = null)
    {
        parent::doSave($conn);

        $object = $this->object;

        $object->refresh();

        $formulatedColumnTable = Doctrine_Core::getTable('EditorBillItemFormulatedColumn');

        $formulatedColumn = $formulatedColumnTable->getByRelationIdAndColumnName($object->bill_item_info_id, BillItem::FORMULATED_COLUMN_RATE);

        $formulatedColumn->setFormula($object->amount);

        $formulatedColumn->linked = false;

        $formulatedColumn->save($conn);

        $formulatedColumn->refresh();

        $object->EditorBillItemInfo->updateBillItemTotalColumns();
    }
}
