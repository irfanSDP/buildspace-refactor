<?php

/**
 * SubPackageBillLayoutPhrase form base class.
 *
 * @method SubPackageBillLayoutPhrase getObject() Returns the current form's model object
 *
 * @package    buildspace
 * @subpackage form
 * @author     1337 developers
 * @version    SVN: $Id$
 */
abstract class BaseSubPackageBillLayoutPhraseForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'                                 => new sfWidgetFormInputHidden(),
      'sub_package_bill_layout_setting_id' => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('SubPackageBillLayoutSetting'), 'add_empty' => true)),
      'to_collection'                      => new sfWidgetFormInputText(),
      'table_header_description'           => new sfWidgetFormInputText(),
      'table_header_unit'                  => new sfWidgetFormInputText(),
      'table_header_qty'                   => new sfWidgetFormInputText(),
      'table_header_rate'                  => new sfWidgetFormInputText(),
      'table_header_amt'                   => new sfWidgetFormInputText(),
      'currency'                           => new sfWidgetFormInputText(),
      'cents'                              => new sfWidgetFormInputText(),
      'collection_in_grid'                 => new sfWidgetFormInputText(),
      'summary'                            => new sfWidgetFormInputText(),
      'summary_in_grid'                    => new sfWidgetFormInputText(),
      'totalPerUnitPrefix'                 => new sfWidgetFormInputText(),
      'totalUnitPrefix'                    => new sfWidgetFormInputText(),
      'totalPerTypePrefix'                 => new sfWidgetFormInputText(),
      'summary_page_no'                    => new sfWidgetFormInputText(),
      'summary_tender'                     => new sfWidgetFormInputText(),
      'summary_page_one'                   => new sfWidgetFormInputText(),
      'summary_page_two'                   => new sfWidgetFormInputText(),
      'summary_page_three'                 => new sfWidgetFormInputText(),
      'summary_page_four'                  => new sfWidgetFormInputText(),
      'summary_page_five'                  => new sfWidgetFormInputText(),
      'summary_page_six'                   => new sfWidgetFormInputText(),
      'summary_page_seven'                 => new sfWidgetFormInputText(),
      'summary_page_eight'                 => new sfWidgetFormInputText(),
      'summary_page_nine'                  => new sfWidgetFormInputText(),
      'element_header_bold'                => new sfWidgetFormInputCheckbox(),
      'element_header_underline'           => new sfWidgetFormInputCheckbox(),
      'element_header_italic'              => new sfWidgetFormInputCheckbox(),
      'element_footer_bold'                => new sfWidgetFormInputCheckbox(),
      'element_footer_underline'           => new sfWidgetFormInputCheckbox(),
      'element_footer_italic'              => new sfWidgetFormInputCheckbox(),
      'element_note_top_left_row1'         => new sfWidgetFormInputText(),
      'element_note_top_left_row2'         => new sfWidgetFormInputText(),
      'element_note_top_right_row1'        => new sfWidgetFormInputText(),
      'element_note_bot_left_row1'         => new sfWidgetFormInputText(),
      'element_note_bot_left_row2'         => new sfWidgetFormInputText(),
      'element_note_bot_right_row1'        => new sfWidgetFormInputText(),
      'element_note_bot_right_row2'        => new sfWidgetFormInputText(),
      'created_at'                         => new sfWidgetFormDateTime(),
      'updated_at'                         => new sfWidgetFormDateTime(),
      'created_by'                         => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Creator'), 'add_empty' => true)),
      'updated_by'                         => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Updator'), 'add_empty' => true)),
      'deleted_at'                         => new sfWidgetFormDateTime(),
    ));

    $this->setValidators(array(
      'id'                                 => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'sub_package_bill_layout_setting_id' => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('SubPackageBillLayoutSetting'), 'column' => 'id', 'required' => false)),
      'to_collection'                      => new sfValidatorString(array('max_length' => 150, 'required' => false)),
      'table_header_description'           => new sfValidatorString(array('max_length' => 150, 'required' => false)),
      'table_header_unit'                  => new sfValidatorString(array('max_length' => 150, 'required' => false)),
      'table_header_qty'                   => new sfValidatorString(array('max_length' => 150, 'required' => false)),
      'table_header_rate'                  => new sfValidatorString(array('max_length' => 150, 'required' => false)),
      'table_header_amt'                   => new sfValidatorString(array('max_length' => 150, 'required' => false)),
      'currency'                           => new sfValidatorString(array('max_length' => 150, 'required' => false)),
      'cents'                              => new sfValidatorString(array('max_length' => 150, 'required' => false)),
      'collection_in_grid'                 => new sfValidatorString(array('max_length' => 150, 'required' => false)),
      'summary'                            => new sfValidatorString(array('max_length' => 150, 'required' => false)),
      'summary_in_grid'                    => new sfValidatorString(array('max_length' => 150, 'required' => false)),
      'totalPerUnitPrefix'                 => new sfValidatorString(array('max_length' => 150, 'required' => false)),
      'totalUnitPrefix'                    => new sfValidatorString(array('max_length' => 150, 'required' => false)),
      'totalPerTypePrefix'                 => new sfValidatorString(array('max_length' => 150, 'required' => false)),
      'summary_page_no'                    => new sfValidatorString(array('max_length' => 150, 'required' => false)),
      'summary_tender'                     => new sfValidatorString(array('max_length' => 150, 'required' => false)),
      'summary_page_one'                   => new sfValidatorString(array('max_length' => 150, 'required' => false)),
      'summary_page_two'                   => new sfValidatorString(array('max_length' => 150, 'required' => false)),
      'summary_page_three'                 => new sfValidatorString(array('max_length' => 150, 'required' => false)),
      'summary_page_four'                  => new sfValidatorString(array('max_length' => 150, 'required' => false)),
      'summary_page_five'                  => new sfValidatorString(array('max_length' => 150, 'required' => false)),
      'summary_page_six'                   => new sfValidatorString(array('max_length' => 150, 'required' => false)),
      'summary_page_seven'                 => new sfValidatorString(array('max_length' => 150, 'required' => false)),
      'summary_page_eight'                 => new sfValidatorString(array('max_length' => 150, 'required' => false)),
      'summary_page_nine'                  => new sfValidatorString(array('max_length' => 150, 'required' => false)),
      'element_header_bold'                => new sfValidatorBoolean(array('required' => false)),
      'element_header_underline'           => new sfValidatorBoolean(array('required' => false)),
      'element_header_italic'              => new sfValidatorBoolean(array('required' => false)),
      'element_footer_bold'                => new sfValidatorBoolean(array('required' => false)),
      'element_footer_underline'           => new sfValidatorBoolean(array('required' => false)),
      'element_footer_italic'              => new sfValidatorBoolean(array('required' => false)),
      'element_note_top_left_row1'         => new sfValidatorString(array('max_length' => 150, 'required' => false)),
      'element_note_top_left_row2'         => new sfValidatorString(array('max_length' => 150, 'required' => false)),
      'element_note_top_right_row1'        => new sfValidatorString(array('max_length' => 150, 'required' => false)),
      'element_note_bot_left_row1'         => new sfValidatorString(array('max_length' => 150, 'required' => false)),
      'element_note_bot_left_row2'         => new sfValidatorString(array('max_length' => 150, 'required' => false)),
      'element_note_bot_right_row1'        => new sfValidatorString(array('max_length' => 150, 'required' => false)),
      'element_note_bot_right_row2'        => new sfValidatorString(array('max_length' => 150, 'required' => false)),
      'created_at'                         => new sfValidatorDateTime(),
      'updated_at'                         => new sfValidatorDateTime(),
      'created_by'                         => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Creator'), 'column' => 'id', 'required' => false)),
      'updated_by'                         => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Updator'), 'column' => 'id', 'required' => false)),
      'deleted_at'                         => new sfValidatorDateTime(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('sub_package_bill_layout_phrase[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'SubPackageBillLayoutPhrase';
  }

}
