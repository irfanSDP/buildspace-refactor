<?php

/**
 * SubPackagePreliminariesTimeBasedClaim form base class.
 *
 * @method SubPackagePreliminariesTimeBasedClaim getObject() Returns the current form's model object
 *
 * @package    buildspace
 * @subpackage form
 * @author     1337 developers
 * @version    SVN: $Id$
 */
abstract class BaseSubPackagePreliminariesTimeBasedClaimForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'                                          => new sfWidgetFormInputHidden(),
      'sub_package_post_contract_bill_item_rate_id' => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('ItemRate'), 'add_empty' => false)),
      'up_to_date_duration'                         => new sfWidgetFormInputText(),
      'total_project_duration'                      => new sfWidgetFormInputText(),
      'total'                                       => new sfWidgetFormInputText(),
      'revision_id'                                 => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Revision'), 'add_empty' => false)),
      'created_at'                                  => new sfWidgetFormDateTime(),
      'updated_at'                                  => new sfWidgetFormDateTime(),
      'created_by'                                  => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Creator'), 'add_empty' => true)),
      'updated_by'                                  => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Updator'), 'add_empty' => true)),
      'deleted_at'                                  => new sfWidgetFormDateTime(),
    ));

    $this->setValidators(array(
      'id'                                          => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'sub_package_post_contract_bill_item_rate_id' => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('ItemRate'), 'column' => 'id')),
      'up_to_date_duration'                         => new sfValidatorNumber(array('required' => false)),
      'total_project_duration'                      => new sfValidatorNumber(array('required' => false)),
      'total'                                       => new sfValidatorNumber(array('required' => false)),
      'revision_id'                                 => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Revision'), 'column' => 'id')),
      'created_at'                                  => new sfValidatorDateTime(),
      'updated_at'                                  => new sfValidatorDateTime(),
      'created_by'                                  => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Creator'), 'column' => 'id', 'required' => false)),
      'updated_by'                                  => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Updator'), 'column' => 'id', 'required' => false)),
      'deleted_at'                                  => new sfValidatorDateTime(array('required' => false)),
    ));

    $this->validatorSchema->setPostValidator(
      new sfValidatorDoctrineUnique(array('model' => 'SubPackagePreliminariesTimeBasedClaim', 'column' => array('sub_package_post_contract_bill_item_rate_id', 'revision_id', 'deleted_at')))
    );

    $this->widgetSchema->setNameFormat('sub_package_preliminaries_time_based_claim[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'SubPackagePreliminariesTimeBasedClaim';
  }

}
