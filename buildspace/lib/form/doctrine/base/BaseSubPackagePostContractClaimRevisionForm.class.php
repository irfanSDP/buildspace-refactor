<?php

/**
 * SubPackagePostContractClaimRevision form base class.
 *
 * @method SubPackagePostContractClaimRevision getObject() Returns the current form's model object
 *
 * @package    buildspace
 * @subpackage form
 * @author     1337 developers
 * @version    SVN: $Id$
 */
abstract class BaseSubPackagePostContractClaimRevisionForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'                        => new sfWidgetFormInputHidden(),
      'sub_package_id'            => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('SubPackage'), 'add_empty' => false)),
      'version'                   => new sfWidgetFormInputText(),
      'current_selected_revision' => new sfWidgetFormInputCheckbox(),
      'locked_status'             => new sfWidgetFormInputCheckbox(),
      'created_at'                => new sfWidgetFormDateTime(),
      'updated_at'                => new sfWidgetFormDateTime(),
      'deleted_at'                => new sfWidgetFormDateTime(),
      'created_by'                => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Creator'), 'add_empty' => true)),
      'updated_by'                => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Updator'), 'add_empty' => true)),
    ));

    $this->setValidators(array(
      'id'                        => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'sub_package_id'            => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('SubPackage'), 'column' => 'id')),
      'version'                   => new sfValidatorInteger(array('required' => false)),
      'current_selected_revision' => new sfValidatorBoolean(array('required' => false)),
      'locked_status'             => new sfValidatorBoolean(array('required' => false)),
      'created_at'                => new sfValidatorDateTime(),
      'updated_at'                => new sfValidatorDateTime(),
      'deleted_at'                => new sfValidatorDateTime(array('required' => false)),
      'created_by'                => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Creator'), 'column' => 'id', 'required' => false)),
      'updated_by'                => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Updator'), 'column' => 'id', 'required' => false)),
    ));

    $this->validatorSchema->setPostValidator(
      new sfValidatorDoctrineUnique(array('model' => 'SubPackagePostContractClaimRevision', 'column' => array('sub_package_id', 'version', 'current_selected_revision')))
    );

    $this->widgetSchema->setNameFormat('sub_package_post_contract_claim_revision[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'SubPackagePostContractClaimRevision';
  }

}
