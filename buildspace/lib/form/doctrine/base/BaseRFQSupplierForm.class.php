<?php

/**
 * RFQSupplier form base class.
 *
 * @method RFQSupplier getObject() Returns the current form's model object
 *
 * @package    buildspace
 * @subpackage form
 * @author     1337 developers
 * @version    SVN: $Id$
 */
abstract class BaseRFQSupplierForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'                       => new sfWidgetFormInputHidden(),
      'request_for_quotation_id' => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('RFQ'), 'add_empty' => false)),
      'company_id'               => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Company'), 'add_empty' => false)),
      'status'                   => new sfWidgetFormInputText(),
      'created_at'               => new sfWidgetFormDateTime(),
      'updated_at'               => new sfWidgetFormDateTime(),
      'created_by'               => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Creator'), 'add_empty' => true)),
      'updated_by'               => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Updator'), 'add_empty' => true)),
    ));

    $this->setValidators(array(
      'id'                       => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'request_for_quotation_id' => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('RFQ'), 'column' => 'id')),
      'company_id'               => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Company'), 'column' => 'id')),
      'status'                   => new sfValidatorInteger(array('required' => false)),
      'created_at'               => new sfValidatorDateTime(),
      'updated_at'               => new sfValidatorDateTime(),
      'created_by'               => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Creator'), 'column' => 'id', 'required' => false)),
      'updated_by'               => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Updator'), 'column' => 'id', 'required' => false)),
    ));

    $this->validatorSchema->setPostValidator(
      new sfValidatorDoctrineUnique(array('model' => 'RFQSupplier', 'column' => array('request_for_quotation_id', 'company_id')))
    );

    $this->widgetSchema->setNameFormat('rfq_supplier[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'RFQSupplier';
  }

}
