<?php

/**
 * ScheduleOfRateBillLayoutPhraseSetting form base class.
 *
 * @method ScheduleOfRateBillLayoutPhraseSetting getObject() Returns the current form's model object
 *
 * @package    buildspace
 * @subpackage form
 * @author     1337 developers
 * @version    SVN: $Id$
 */
abstract class BaseScheduleOfRateBillLayoutPhraseSettingForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'                                      => new sfWidgetFormInputHidden(),
      'schedule_of_rate_bill_layout_setting_id' => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('ScheduleOfRateBillLayoutSetting'), 'add_empty' => true)),
      'to_collection'                           => new sfWidgetFormInputText(),
      'currency'                                => new sfWidgetFormInputText(),
      'collection_in_grid'                      => new sfWidgetFormInputText(),
      'element_header_bold'                     => new sfWidgetFormInputCheckbox(),
      'element_header_underline'                => new sfWidgetFormInputCheckbox(),
      'element_header_italic'                   => new sfWidgetFormInputCheckbox(),
      'element_note_top_left_row1'              => new sfWidgetFormInputText(),
      'element_note_top_left_row2'              => new sfWidgetFormInputText(),
      'element_note_top_right_row1'             => new sfWidgetFormInputText(),
      'created_at'                              => new sfWidgetFormDateTime(),
      'updated_at'                              => new sfWidgetFormDateTime(),
      'created_by'                              => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Creator'), 'add_empty' => true)),
      'updated_by'                              => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Updator'), 'add_empty' => true)),
      'deleted_at'                              => new sfWidgetFormDateTime(),
    ));

    $this->setValidators(array(
      'id'                                      => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'schedule_of_rate_bill_layout_setting_id' => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('ScheduleOfRateBillLayoutSetting'), 'column' => 'id', 'required' => false)),
      'to_collection'                           => new sfValidatorString(array('max_length' => 150, 'required' => false)),
      'currency'                                => new sfValidatorString(array('max_length' => 150, 'required' => false)),
      'collection_in_grid'                      => new sfValidatorString(array('max_length' => 150, 'required' => false)),
      'element_header_bold'                     => new sfValidatorBoolean(array('required' => false)),
      'element_header_underline'                => new sfValidatorBoolean(array('required' => false)),
      'element_header_italic'                   => new sfValidatorBoolean(array('required' => false)),
      'element_note_top_left_row1'              => new sfValidatorString(array('max_length' => 150, 'required' => false)),
      'element_note_top_left_row2'              => new sfValidatorString(array('max_length' => 150, 'required' => false)),
      'element_note_top_right_row1'             => new sfValidatorString(array('max_length' => 150, 'required' => false)),
      'created_at'                              => new sfValidatorDateTime(),
      'updated_at'                              => new sfValidatorDateTime(),
      'created_by'                              => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Creator'), 'column' => 'id', 'required' => false)),
      'updated_by'                              => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Updator'), 'column' => 'id', 'required' => false)),
      'deleted_at'                              => new sfValidatorDateTime(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('schedule_of_rate_bill_layout_phrase_setting[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'ScheduleOfRateBillLayoutPhraseSetting';
  }

}
