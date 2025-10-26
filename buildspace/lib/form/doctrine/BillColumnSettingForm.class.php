<?php

/**
 * BillColumnSetting form.
 *
 * @package    buildspace
 * @subpackage form
 * @author     1337 developers
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class BillColumnSettingForm extends BaseBillColumnSettingForm {

	public function configure()
	{
		unset( $this['deleted_at'], $this['tender_origin_id'], $this['created_at'], $this['updated_at'] );

		$this->setValidator('name', new sfValidatorString(array(
			'required'   => true,
			'trim'       => true,
			'max_length' => 200 ), array(
				'required'   => 'Name is required',
				'max_length' => 'Name is too long (%max_length% character max)' )
		));

		$this->setValidator('quantity', new sfValidatorInteger(array(
			'required' => true,
			'trim'     => true,
			'min'      => 1,
			'max'      => 500000 ), array(
				'required' => 'Quantity is required',
				'invalid'  => 'Quantity must be integer',
				'min'      => 'Quantity must be more than 0',
				'max'      => 'Quantity cannot exceed %max%' )
		));

		$this->setValidator('total_floor_area_m2', new sfValidatorNumber(array(
			'required' => true,
			'trim'     => true ), array(
				'required' => 'Floor Area is required',
				'invalid'  => 'Floor Area must be integer' )
		));

		$this->setValidator('total_floor_area_ft2', new sfValidatorNumber(array(
			'required' => true,
			'trim'     => true ), array(
				'required' => 'Floor Area is required',
				'invalid'  => 'Floor Area must be integer' )
		));
	}

	public function doSave($conn = null)
	{
		$oldRemeasureQuantityEnabled = false;
		$isNew                       = true;

		if ( !$this->object->isNew() )
		{
			$isNew                       = false;
			$oldQuantity                 = $this->object->quantity;
			$oldRemeasureQuantityEnabled = $this->object->remeasurement_quantity_enabled;
			$oldUseOriginalQuantity      = $this->object->use_original_quantity;
		}

		parent::doSave($conn);

		$object = $this->object;

		$object->refresh();

		if ( !$object->remeasurement_quantity_enabled )
		{
			$object->use_original_quantity = true;

			$object->save();

            Doctrine_Query::create()->update('LocationBQSetting s')
                ->set('s.use_original_qty', 'TRUE')
                ->where('s.bill_column_setting_id = ?', $object->id)
                ->andWhere('s.use_original_qty IS FALSE')
                ->execute();

			/*
			 * We need to clear out all re-measurement columns (if any) since re-measurement quantity
			 * is disabled. We also need to update quantity amount in bill item due to this update.
			 */
			BillItemTypeReferenceTable::clearAllRemeasurementQuantityByColumnSettingId($object);
		}

		/*
		 * If any of these 3 columns changed then we need to update quantity columns in bill items. This process probably
		 * will be time consuming and eats up cpu resource depending on amount of records that needs to be updated.
		 */
		if ( !$isNew and ( $object->remeasurement_quantity_enabled != $oldRemeasureQuantityEnabled or $object->use_original_quantity != $oldUseOriginalQuantity or $object->quantity != $oldQuantity ) )
		{
			$billMarkupSetting = $object->ProjectStructure->BillMarkupSetting;

			BillItemTypeReferenceTable::updateQuantityByBillColumnSetting($object, $billMarkupSetting);
		}

		// clone existing item-type lump sum percent's record if available
		if ( $isNew )
		{
			BillItemTypeReferenceTable::cloneItemLumpSumRecords($object);
			BillItemTypeReferenceTable::cloneItemLumpSumPercentRecords($object);
		}

		// find item-type lump sum, lump sum percent and lump sum exclude
		// to add remeasurement quantity to it
		if ( $object->remeasurement_quantity_enabled AND $oldRemeasureQuantityEnabled != $object->remeasurement_quantity_enabled )
		{
			BillColumnSetting::addQuantityRemeasurementToExistingItemLumpSum($object);
		}
	}

}