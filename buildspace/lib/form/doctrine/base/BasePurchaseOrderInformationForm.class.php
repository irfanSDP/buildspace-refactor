<?php

/**
 * PurchaseOrderInformation form base class.
 *
 * @method PurchaseOrderInformation getObject() Returns the current form's model object
 *
 * @package    buildspace
 * @subpackage form
 * @author     1337 developers
 * @version    SVN: $Id$
 */
abstract class BasePurchaseOrderInformationForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'                  => new sfWidgetFormInputHidden(),
      'purchase_order_id'   => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('PurchaseOrder'), 'add_empty' => false)),
      'sub_package_id'      => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('SubPackage'), 'add_empty' => true)),
      'currency_id'         => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Currency'), 'add_empty' => true)),
      'ref'                 => new sfWidgetFormInputText(),
      'quo_ref'             => new sfWidgetFormInputText(),
      'company_address_1'   => new sfWidgetFormInputText(),
      'company_address_2'   => new sfWidgetFormInputText(),
      'company_address_3'   => new sfWidgetFormInputText(),
      'supplier_address_1'  => new sfWidgetFormInputText(),
      'supplier_address_2'  => new sfWidgetFormInputText(),
      'supplier_address_3'  => new sfWidgetFormInputText(),
      'attention_to'        => new sfWidgetFormInputText(),
      'ship_to_1'           => new sfWidgetFormInputText(),
      'ship_to_2'           => new sfWidgetFormInputText(),
      'ship_to_3'           => new sfWidgetFormInputText(),
      'note'                => new sfWidgetFormTextarea(),
      'signature'           => new sfWidgetFormInputText(),
      'status'              => new sfWidgetFormInputText(),
      'price_format'        => new sfWidgetFormInputText(),
      'print_without_cents' => new sfWidgetFormInputCheckbox(),
      'created_at'          => new sfWidgetFormDateTime(),
      'updated_at'          => new sfWidgetFormDateTime(),
      'created_by'          => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Creator'), 'add_empty' => true)),
      'updated_by'          => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Updator'), 'add_empty' => true)),
      'deleted_at'          => new sfWidgetFormDateTime(),
    ));

    $this->setValidators(array(
      'id'                  => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'purchase_order_id'   => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('PurchaseOrder'), 'column' => 'id')),
      'sub_package_id'      => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('SubPackage'), 'column' => 'id', 'required' => false)),
      'currency_id'         => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Currency'), 'column' => 'id', 'required' => false)),
      'ref'                 => new sfValidatorString(array('max_length' => 50, 'required' => false)),
      'quo_ref'             => new sfValidatorString(array('max_length' => 50, 'required' => false)),
      'company_address_1'   => new sfValidatorString(array('max_length' => 50, 'required' => false)),
      'company_address_2'   => new sfValidatorString(array('max_length' => 50, 'required' => false)),
      'company_address_3'   => new sfValidatorString(array('max_length' => 50, 'required' => false)),
      'supplier_address_1'  => new sfValidatorString(array('max_length' => 50, 'required' => false)),
      'supplier_address_2'  => new sfValidatorString(array('max_length' => 50, 'required' => false)),
      'supplier_address_3'  => new sfValidatorString(array('max_length' => 50, 'required' => false)),
      'attention_to'        => new sfValidatorString(array('max_length' => 50, 'required' => false)),
      'ship_to_1'           => new sfValidatorString(array('max_length' => 50, 'required' => false)),
      'ship_to_2'           => new sfValidatorString(array('max_length' => 50, 'required' => false)),
      'ship_to_3'           => new sfValidatorString(array('max_length' => 50, 'required' => false)),
      'note'                => new sfValidatorString(array('max_length' => 370, 'required' => false)),
      'signature'           => new sfValidatorPass(array('required' => false)),
      'status'              => new sfValidatorInteger(array('required' => false)),
      'price_format'        => new sfValidatorString(array('max_length' => 20, 'required' => false)),
      'print_without_cents' => new sfValidatorBoolean(array('required' => false)),
      'created_at'          => new sfValidatorDateTime(),
      'updated_at'          => new sfValidatorDateTime(),
      'created_by'          => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Creator'), 'column' => 'id', 'required' => false)),
      'updated_by'          => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Updator'), 'column' => 'id', 'required' => false)),
      'deleted_at'          => new sfValidatorDateTime(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('purchase_order_information[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'PurchaseOrderInformation';
  }

}
