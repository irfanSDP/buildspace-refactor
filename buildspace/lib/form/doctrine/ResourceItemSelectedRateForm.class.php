<?php

/**
 * ResourceItemSelectedRate form.
 *
 * @package    buildspace
 * @subpackage form
 * @author     1337 developers
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class ResourceItemSelectedRateForm extends BaseResourceItemSelectedRateForm
{
    public function configure()
    {
        parent::configure();

        unset($this['created_at'], $this['updated_at']);
    }

    public function doSave($con = null)
    {
        parent::doSave($con);

        $rate        = 0;
        $rfqRatesId  = $this->getValue('rfq_item_rates_list');
        $sortingType = $this->getValue('sorting_type');

        if ( count($rfqRatesId) > 0 )
        {
            $rate = RFQItemRateTable::getSupplierRatesByIdAndRateDisplayType($rfqRatesId, $sortingType);
        }

        // update resource item's rate
        $object = $this->getObject();

        $resourceItem    = $object->getResourceItem();
        $resourceItemCon = $resourceItem->getTable()->getConnection();

        $formulatedColumnTable = Doctrine_Core::getTable('ResourceItemFormulatedColumn');

        $formulatedColumn = $formulatedColumnTable->getByRelationIdAndColumnName($resourceItem->id, ResourceItem::FORMULATED_COLUMN_RATE);

        $formulatedColumn->setFormula($rate);
        $formulatedColumn->save($resourceItemCon);
        $formulatedColumn->refresh();
        $formulatedColumn->updateLinkedSorValues($resourceItemCon);

        unset($object, $resourceItem, $formulatedColumn, $resourceItemCon);
    }
}