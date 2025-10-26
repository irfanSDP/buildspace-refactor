<?php

/**
 * ProjectSummaryDefaultSetting form base class.
 *
 * @method ProjectSummaryDefaultSetting getObject() Returns the current form's model object
 *
 * @package    buildspace
 * @subpackage form
 * @author     1337 developers
 * @version    SVN: $Id$
 */
abstract class BaseProjectSummaryDefaultSettingForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'                                => new sfWidgetFormInputHidden(),
      'first_row_text'                    => new sfWidgetFormTextarea(),
      'second_row_text'                   => new sfWidgetFormTextarea(),
      'left_text'                         => new sfWidgetFormTextarea(),
      'right_text'                        => new sfWidgetFormTextarea(),
      'summary_title'                     => new sfWidgetFormInputText(),
      'include_printing_date'             => new sfWidgetFormInputCheckbox(),
      'carried_to_next_page_text'         => new sfWidgetFormInputText(),
      'continued_from_previous_page_text' => new sfWidgetFormInputText(),
      'page_number_prefix'                => new sfWidgetFormInputText(),
      'created_at'                        => new sfWidgetFormDateTime(),
      'updated_at'                        => new sfWidgetFormDateTime(),
      'created_by'                        => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Creator'), 'add_empty' => true)),
      'updated_by'                        => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Updator'), 'add_empty' => true)),
    ));

    $this->setValidators(array(
      'id'                                => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'first_row_text'                    => new sfValidatorString(array('required' => false)),
      'second_row_text'                   => new sfValidatorString(array('required' => false)),
      'left_text'                         => new sfValidatorString(array('required' => false)),
      'right_text'                        => new sfValidatorString(array('required' => false)),
      'summary_title'                     => new sfValidatorPass(array('required' => false)),
      'include_printing_date'             => new sfValidatorBoolean(array('required' => false)),
      'carried_to_next_page_text'         => new sfValidatorPass(array('required' => false)),
      'continued_from_previous_page_text' => new sfValidatorPass(array('required' => false)),
      'page_number_prefix'                => new sfValidatorPass(array('required' => false)),
      'created_at'                        => new sfValidatorDateTime(),
      'updated_at'                        => new sfValidatorDateTime(),
      'created_by'                        => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Creator'), 'column' => 'id', 'required' => false)),
      'updated_by'                        => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Updator'), 'column' => 'id', 'required' => false)),
    ));

    $this->widgetSchema->setNameFormat('project_summary_default_setting[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'ProjectSummaryDefaultSetting';
  }

}
