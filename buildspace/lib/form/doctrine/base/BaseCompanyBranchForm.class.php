<?php

/**
 * CompanyBranch form base class.
 *
 * @method CompanyBranch getObject() Returns the current form's model object
 *
 * @package    buildspace
 * @subpackage form
 * @author     1337 developers
 * @version    SVN: $Id$
 */
abstract class BaseCompanyBranchForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'                         => new sfWidgetFormInputHidden(),
      'company_id'                 => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Company'), 'add_empty' => false)),
      'name'                       => new sfWidgetFormInputText(),
      'contact_person_name'        => new sfWidgetFormInputText(),
      'contact_person_email'       => new sfWidgetFormInputText(),
      'contact_person_direct_line' => new sfWidgetFormInputText(),
      'contact_person_mobile'      => new sfWidgetFormInputText(),
      'address'                    => new sfWidgetFormTextarea(),
      'region_id'                  => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Region'), 'add_empty' => true)),
      'sub_region_id'              => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('SubRegion'), 'add_empty' => true)),
      'postcode'                   => new sfWidgetFormInputText(),
      'phone_number'               => new sfWidgetFormInputText(),
      'fax_number'                 => new sfWidgetFormInputText(),
      'created_at'                 => new sfWidgetFormDateTime(),
      'updated_at'                 => new sfWidgetFormDateTime(),
      'created_by'                 => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Creator'), 'add_empty' => true)),
      'updated_by'                 => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Updator'), 'add_empty' => true)),
      'deleted_at'                 => new sfWidgetFormDateTime(),
    ));

    $this->setValidators(array(
      'id'                         => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'company_id'                 => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Company'), 'column' => 'id')),
      'name'                       => new sfValidatorPass(),
      'contact_person_name'        => new sfValidatorPass(array('required' => false)),
      'contact_person_email'       => new sfValidatorPass(array('required' => false)),
      'contact_person_direct_line' => new sfValidatorPass(array('required' => false)),
      'contact_person_mobile'      => new sfValidatorPass(array('required' => false)),
      'address'                    => new sfValidatorString(),
      'region_id'                  => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Region'), 'column' => 'id', 'required' => false)),
      'sub_region_id'              => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('SubRegion'), 'column' => 'id', 'required' => false)),
      'postcode'                   => new sfValidatorPass(array('required' => false)),
      'phone_number'               => new sfValidatorPass(array('required' => false)),
      'fax_number'                 => new sfValidatorPass(array('required' => false)),
      'created_at'                 => new sfValidatorDateTime(),
      'updated_at'                 => new sfValidatorDateTime(),
      'created_by'                 => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Creator'), 'column' => 'id', 'required' => false)),
      'updated_by'                 => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Updator'), 'column' => 'id', 'required' => false)),
      'deleted_at'                 => new sfValidatorDateTime(array('required' => false)),
    ));

    $this->validatorSchema->setPostValidator(
      new sfValidatorDoctrineUnique(array('model' => 'CompanyBranch', 'column' => array('name', 'deleted_at')))
    );

    $this->widgetSchema->setNameFormat('company_branch[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'CompanyBranch';
  }

}
