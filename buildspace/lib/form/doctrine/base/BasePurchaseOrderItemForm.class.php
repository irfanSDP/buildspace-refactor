<?php

/**
 * PurchaseOrderItem form base class.
 *
 * @method PurchaseOrderItem getObject() Returns the current form's model object
 *
 * @package    buildspace
 * @subpackage form
 * @author     1337 developers
 * @version    SVN: $Id$
 */
abstract class BasePurchaseOrderItemForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'                => new sfWidgetFormInputHidden(),
      'purchase_order_id' => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('PurchaseOrder'), 'add_empty' => false)),
      'resource_item_id'  => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('ResourceItem'), 'add_empty' => false)),
      'quantity'          => new sfWidgetFormInputText(),
      'rates'             => new sfWidgetFormInputText(),
      'remark_id'         => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Remark'), 'add_empty' => true)),
      'created_at'        => new sfWidgetFormDateTime(),
      'updated_at'        => new sfWidgetFormDateTime(),
      'created_by'        => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Creator'), 'add_empty' => true)),
      'updated_by'        => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Updator'), 'add_empty' => true)),
      'deleted_at'        => new sfWidgetFormDateTime(),
    ));

    $this->setValidators(array(
      'id'                => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'purchase_order_id' => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('PurchaseOrder'), 'column' => 'id')),
      'resource_item_id'  => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('ResourceItem'), 'column' => 'id')),
      'quantity'          => new sfValidatorNumber(array('required' => false)),
      'rates'             => new sfValidatorNumber(array('required' => false)),
      'remark_id'         => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Remark'), 'column' => 'id', 'required' => false)),
      'created_at'        => new sfValidatorDateTime(),
      'updated_at'        => new sfValidatorDateTime(),
      'created_by'        => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Creator'), 'column' => 'id', 'required' => false)),
      'updated_by'        => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Updator'), 'column' => 'id', 'required' => false)),
      'deleted_at'        => new sfValidatorDateTime(array('required' => false)),
    ));

    $this->validatorSchema->setPostValidator(
      new sfValidatorDoctrineUnique(array('model' => 'PurchaseOrderItem', 'column' => array('purchase_order_id', 'resource_item_id', 'deleted_at')))
    );

    $this->widgetSchema->setNameFormat('purchase_order_item[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'PurchaseOrderItem';
  }

}
