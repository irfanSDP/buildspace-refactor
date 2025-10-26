<?php

/**
 * NewPostContractFormInformation form base class.
 *
 * @method NewPostContractFormInformation getObject() Returns the current form's model object
 *
 * @package    buildspace
 * @subpackage form
 * @author     1337 developers
 * @version    SVN: $Id$
 */
abstract class BaseNewPostContractFormInformationForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'                           => new sfWidgetFormInputHidden(),
      'type'                         => new sfWidgetFormInputText(),
      'form_number'                  => new sfWidgetFormInputText(),
      'project_structure_id'         => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('ProjectStructure'), 'add_empty' => false)),
      'contract_period_from'         => new sfWidgetFormDateTime(),
      'contract_period_to'           => new sfWidgetFormDateTime(),
      'awarded_date'                 => new sfWidgetFormDateTime(),
      'pre_defined_location_code_id' => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('PreDefinedLocationCode'), 'add_empty' => false)),
      'creditor_code'                => new sfWidgetFormTextarea(),
      'remarks'                      => new sfWidgetFormTextarea(),
      'retention'                    => new sfWidgetFormInputText(),
      'max_retention_sum'            => new sfWidgetFormInputText(),
      'reference'                    => new sfWidgetFormTextarea(),
      'e_tender_waiver_option_type'  => new sfWidgetFormInputText(),
      'e_auction_waiver_option_type' => new sfWidgetFormInputText(),
      'created_at'                   => new sfWidgetFormDateTime(),
      'updated_at'                   => new sfWidgetFormDateTime(),
      'created_by'                   => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Creator'), 'add_empty' => true)),
      'updated_by'                   => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Updator'), 'add_empty' => true)),
    ));

    $this->setValidators(array(
      'id'                           => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'type'                         => new sfValidatorInteger(),
      'form_number'                  => new sfValidatorInteger(),
      'project_structure_id'         => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('ProjectStructure'), 'column' => 'id')),
      'contract_period_from'         => new sfValidatorDateTime(),
      'contract_period_to'           => new sfValidatorDateTime(),
      'awarded_date'                 => new sfValidatorDateTime(),
      'pre_defined_location_code_id' => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('PreDefinedLocationCode'), 'column' => 'id')),
      'creditor_code'                => new sfValidatorString(array('required' => false)),
      'remarks'                      => new sfValidatorString(array('required' => false)),
      'retention'                    => new sfValidatorNumber(array('required' => false)),
      'max_retention_sum'            => new sfValidatorNumber(array('required' => false)),
      'reference'                    => new sfValidatorString(),
      'e_tender_waiver_option_type'  => new sfValidatorPass(array('required' => false)),
      'e_auction_waiver_option_type' => new sfValidatorPass(array('required' => false)),
      'created_at'                   => new sfValidatorDateTime(),
      'updated_at'                   => new sfValidatorDateTime(),
      'created_by'                   => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Creator'), 'column' => 'id', 'required' => false)),
      'updated_by'                   => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Updator'), 'column' => 'id', 'required' => false)),
    ));

    $this->validatorSchema->setPostValidator(
      new sfValidatorAnd(array(
        new sfValidatorDoctrineUnique(array('model' => 'NewPostContractFormInformation', 'column' => array('project_structure_id'))),
        new sfValidatorDoctrineUnique(array('model' => 'NewPostContractFormInformation', 'column' => array('reference'))),
      ))
    );

    $this->widgetSchema->setNameFormat('new_post_contract_form_information[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'NewPostContractFormInformation';
  }

}
