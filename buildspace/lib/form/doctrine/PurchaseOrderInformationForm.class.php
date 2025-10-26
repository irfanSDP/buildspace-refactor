<?php

/**
 * PurchaseOrderInformation form.
 *
 * @package    buildspace
 * @subpackage form
 * @author     1337 developers
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class PurchaseOrderInformationForm extends BasePurchaseOrderInformationForm
{

	public function configure()
	{
		parent::configure();

		unset($this['sub_package_id'], $this['created_at'], $this['updated_at']);

		$this->setValidator('ref', new sfValidatorString(
			array('required' => false, 'max_length' => 48),
			array('max_length' => 'Ref is too long (%max_length% characters max).')
		));

		$this->setValidator('quo_ref', new sfValidatorString(
			array('required' => false, 'max_length' => 48),
			array('max_length' => 'Your Quo Ref is too long (%max_length% characters max).')
		));

		$this->setValidator('company_address_1', new sfValidatorString(
			array('required' => false, 'max_length' => 48),
			array('max_length' => 'Company Address 1 is too long (%max_length% characters max).')
		));

		$this->setValidator('company_address_2', new sfValidatorString(
			array('required' => false, 'max_length' => 48),
			array('max_length' => 'Company Address 2 is too long (%max_length% characters max).')
		));

		$this->setValidator('company_address_3', new sfValidatorString(
			array('required' => false, 'max_length' => 48),
			array('max_length' => 'Company Address 3 is too long (%max_length% characters max).')
		));

		$this->setValidator('supplier_address_1', new sfValidatorString(
			array('required' => false, 'max_length' => 48),
			array('max_length' => 'Supplier Address 1 is too long (%max_length% characters max).')
		));

		$this->setValidator('supplier_address_2', new sfValidatorString(
			array('required' => false, 'max_length' => 48),
			array('max_length' => 'Supplier Address 2 is too long (%max_length% characters max).')
		));

		$this->setValidator('supplier_address_3', new sfValidatorString(
			array('required' => false, 'max_length' => 48),
			array('max_length' => 'Supplier Address 3 is too long (%max_length% characters max).')
		));

		$this->setValidator('attention_to', new sfValidatorString(
			array('required' => false, 'max_length' => 48),
			array('max_length' => 'ATTN is too long (%max_length% characters max).')
		));

		$this->setValidator('ship_to_1', new sfValidatorString(
			array('required' => false, 'max_length' => 48),
			array('max_length' => 'Ship To 1 is too long (%max_length% characters max).')
		));

		$this->setValidator('ship_to_2', new sfValidatorString(
			array('required' => false, 'max_length' => 48),
			array('max_length' => 'Ship To 2 is too long (%max_length% characters max).')
		));

		$this->setValidator('ship_to_3', new sfValidatorString(
			array('required' => false, 'max_length' => 48),
			array('max_length' => 'Ship To 3 is too long (%max_length% characters max).')
		));

		$this->setValidator('note', new sfValidatorString(
			array('required' => false, 'max_length' => 370),
			array('max_length' => 'Note is too long (%max_length% characters max).')
		));
	}

}