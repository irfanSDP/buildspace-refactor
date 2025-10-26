<?php

/**
 * RFQItem form base class.
 *
 * @method RFQItem getObject() Returns the current form's model object
 *
 * @package    buildspace
 * @subpackage form
 * @author     1337 developers
 * @version    SVN: $Id$
 */
abstract class BaseRFQItemForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'                       => new sfWidgetFormInputHidden(),
      'request_for_quotation_id' => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('RFQ'), 'add_empty' => false)),
      'resource_item_id'         => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('ResourceItem'), 'add_empty' => false)),
      'quantity'                 => new sfWidgetFormInputText(),
      'remark_id'                => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Remark'), 'add_empty' => true)),
      'created_at'               => new sfWidgetFormDateTime(),
      'updated_at'               => new sfWidgetFormDateTime(),
      'created_by'               => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Creator'), 'add_empty' => true)),
      'updated_by'               => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Updator'), 'add_empty' => true)),
    ));

    $this->setValidators(array(
      'id'                       => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'request_for_quotation_id' => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('RFQ'), 'column' => 'id')),
      'resource_item_id'         => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('ResourceItem'), 'column' => 'id')),
      'quantity'                 => new sfValidatorNumber(array('required' => false)),
      'remark_id'                => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Remark'), 'column' => 'id', 'required' => false)),
      'created_at'               => new sfValidatorDateTime(),
      'updated_at'               => new sfValidatorDateTime(),
      'created_by'               => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Creator'), 'column' => 'id', 'required' => false)),
      'updated_by'               => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Updator'), 'column' => 'id', 'required' => false)),
    ));

    $this->validatorSchema->setPostValidator(
      new sfValidatorDoctrineUnique(array('model' => 'RFQItem', 'column' => array('request_for_quotation_id', 'resource_item_id')))
    );

    $this->widgetSchema->setNameFormat('rfq_item[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'RFQItem';
  }

}
