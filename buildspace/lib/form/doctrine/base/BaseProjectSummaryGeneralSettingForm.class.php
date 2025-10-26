<?php

/**
 * ProjectSummaryGeneralSetting form base class.
 *
 * @method ProjectSummaryGeneralSetting getObject() Returns the current form's model object
 *
 * @package    buildspace
 * @subpackage form
 * @author     1337 developers
 * @version    SVN: $Id$
 */
abstract class BaseProjectSummaryGeneralSettingForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'                                => new sfWidgetFormInputHidden(),
      'project_structure_id'              => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Project'), 'add_empty' => false)),
      'project_title'                     => new sfWidgetFormTextarea(),
      'summary_title'                     => new sfWidgetFormInputText(),
      'include_printing_date'             => new sfWidgetFormInputCheckbox(),
      'carried_to_next_page_text'         => new sfWidgetFormInputText(),
      'continued_from_previous_page_text' => new sfWidgetFormInputText(),
      'page_number_prefix'                => new sfWidgetFormInputText(),
      'include_state_and_country'         => new sfWidgetFormInputCheckbox(),
      'include_additional_description'    => new sfWidgetFormInputCheckbox(),
      'include_tax'                       => new sfWidgetFormInputCheckbox(),
      'tax_name'                          => new sfWidgetFormInputText(),
      'tax_percentage'                    => new sfWidgetFormInputText(),
      'additional_description'            => new sfWidgetFormInputText(),
      'created_at'                        => new sfWidgetFormDateTime(),
      'updated_at'                        => new sfWidgetFormDateTime(),
      'created_by'                        => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Creator'), 'add_empty' => true)),
      'updated_by'                        => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Updator'), 'add_empty' => true)),
    ));

    $this->setValidators(array(
      'id'                                => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'project_structure_id'              => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Project'), 'column' => 'id')),
      'project_title'                     => new sfValidatorString(array('required' => false)),
      'summary_title'                     => new sfValidatorPass(array('required' => false)),
      'include_printing_date'             => new sfValidatorBoolean(array('required' => false)),
      'carried_to_next_page_text'         => new sfValidatorPass(array('required' => false)),
      'continued_from_previous_page_text' => new sfValidatorPass(array('required' => false)),
      'page_number_prefix'                => new sfValidatorPass(array('required' => false)),
      'include_state_and_country'         => new sfValidatorBoolean(array('required' => false)),
      'include_additional_description'    => new sfValidatorBoolean(array('required' => false)),
      'include_tax'                       => new sfValidatorBoolean(array('required' => false)),
      'tax_name'                          => new sfValidatorPass(array('required' => false)),
      'tax_percentage'                    => new sfValidatorNumber(array('required' => false)),
      'additional_description'            => new sfValidatorPass(array('required' => false)),
      'created_at'                        => new sfValidatorDateTime(),
      'updated_at'                        => new sfValidatorDateTime(),
      'created_by'                        => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Creator'), 'column' => 'id', 'required' => false)),
      'updated_by'                        => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Updator'), 'column' => 'id', 'required' => false)),
    ));

    $this->validatorSchema->setPostValidator(
      new sfValidatorDoctrineUnique(array('model' => 'ProjectSummaryGeneralSetting', 'column' => array('project_structure_id')))
    );

    $this->widgetSchema->setNameFormat('project_summary_general_setting[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'ProjectSummaryGeneralSetting';
  }

}
