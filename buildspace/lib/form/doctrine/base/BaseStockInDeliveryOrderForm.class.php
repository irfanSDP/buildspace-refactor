<?php

/**
 * StockInDeliveryOrder form base class.
 *
 * @method StockInDeliveryOrder getObject() Returns the current form's model object
 *
 * @package    buildspace
 * @subpackage form
 * @author     1337 developers
 * @version    SVN: $Id$
 */
abstract class BaseStockInDeliveryOrderForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'                    => new sfWidgetFormInputHidden(),
      'stock_in_invoice_id'   => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Invoice'), 'add_empty' => true)),
      'delivery_order_no'     => new sfWidgetFormInputText(),
      'delivery_order_date'   => new sfWidgetFormDateTime(),
      'delivery_order_upload' => new sfWidgetFormTextarea(),
      'created_at'            => new sfWidgetFormDateTime(),
      'updated_at'            => new sfWidgetFormDateTime(),
      'created_by'            => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Creator'), 'add_empty' => true)),
      'updated_by'            => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Updator'), 'add_empty' => true)),
      'deleted_at'            => new sfWidgetFormDateTime(),
    ));

    $this->setValidators(array(
      'id'                    => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'stock_in_invoice_id'   => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Invoice'), 'column' => 'id', 'required' => false)),
      'delivery_order_no'     => new sfValidatorString(array('max_length' => 100, 'required' => false)),
      'delivery_order_date'   => new sfValidatorDateTime(array('required' => false)),
      'delivery_order_upload' => new sfValidatorString(array('required' => false)),
      'created_at'            => new sfValidatorDateTime(),
      'updated_at'            => new sfValidatorDateTime(),
      'created_by'            => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Creator'), 'column' => 'id', 'required' => false)),
      'updated_by'            => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Updator'), 'column' => 'id', 'required' => false)),
      'deleted_at'            => new sfValidatorDateTime(array('required' => false)),
    ));

    $this->validatorSchema->setPostValidator(
      new sfValidatorDoctrineUnique(array('model' => 'StockInDeliveryOrder', 'column' => array('stock_in_invoice_id', 'delivery_order_no', 'deleted_at')))
    );

    $this->widgetSchema->setNameFormat('stock_in_delivery_order[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'StockInDeliveryOrder';
  }

}
