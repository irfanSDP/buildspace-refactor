<?php

/**
 * PurchaseOrderTax form base class.
 *
 * @method PurchaseOrderTax getObject() Returns the current form's model object
 *
 * @package    buildspace
 * @subpackage form
 * @author     1337 developers
 * @version    SVN: $Id$
 */
abstract class BasePurchaseOrderTaxForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'                            => new sfWidgetFormInputHidden(),
      'purchase_order_information_id' => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('PurchaseOrderInformation'), 'add_empty' => false)),
      'tax_name'                      => new sfWidgetFormInputText(),
      'percentage'                    => new sfWidgetFormInputText(),
      'created_at'                    => new sfWidgetFormDateTime(),
      'updated_at'                    => new sfWidgetFormDateTime(),
      'created_by'                    => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Creator'), 'add_empty' => true)),
      'updated_by'                    => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Updator'), 'add_empty' => true)),
      'deleted_at'                    => new sfWidgetFormDateTime(),
    ));

    $this->setValidators(array(
      'id'                            => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'purchase_order_information_id' => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('PurchaseOrderInformation'), 'column' => 'id')),
      'tax_name'                      => new sfValidatorString(array('max_length' => 150, 'required' => false)),
      'percentage'                    => new sfValidatorNumber(array('required' => false)),
      'created_at'                    => new sfValidatorDateTime(),
      'updated_at'                    => new sfValidatorDateTime(),
      'created_by'                    => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Creator'), 'column' => 'id', 'required' => false)),
      'updated_by'                    => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Updator'), 'column' => 'id', 'required' => false)),
      'deleted_at'                    => new sfValidatorDateTime(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('purchase_order_tax[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'PurchaseOrderTax';
  }

}
