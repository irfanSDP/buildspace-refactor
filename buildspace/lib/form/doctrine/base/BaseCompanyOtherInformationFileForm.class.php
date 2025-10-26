<?php

/**
 * CompanyOtherInformationFile form base class.
 *
 * @method CompanyOtherInformationFile getObject() Returns the current form's model object
 *
 * @package    buildspace
 * @subpackage form
 * @author     1337 developers
 * @version    SVN: $Id$
 */
abstract class BaseCompanyOtherInformationFileForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'                           => new sfWidgetFormInputHidden(),
      'company_other_information_id' => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('CompanyOtherInformation'), 'add_empty' => false)),
      'original_file_name'           => new sfWidgetFormTextarea(),
      'uploaded_file_name'           => new sfWidgetFormTextarea(),
      'created_at'                   => new sfWidgetFormDateTime(),
      'updated_at'                   => new sfWidgetFormDateTime(),
      'deleted_at'                   => new sfWidgetFormDateTime(),
    ));

    $this->setValidators(array(
      'id'                           => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'company_other_information_id' => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('CompanyOtherInformation'), 'column' => 'id')),
      'original_file_name'           => new sfValidatorString(),
      'uploaded_file_name'           => new sfValidatorString(),
      'created_at'                   => new sfValidatorDateTime(),
      'updated_at'                   => new sfValidatorDateTime(),
      'deleted_at'                   => new sfValidatorDateTime(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('company_other_information_file[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'CompanyOtherInformationFile';
  }

}
