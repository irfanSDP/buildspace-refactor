<?php

/**
 * ScheduleOfRateBillLayoutSetting form base class.
 *
 * @method ScheduleOfRateBillLayoutSetting getObject() Returns the current form's model object
 *
 * @package    buildspace
 * @subpackage form
 * @author     1337 developers
 * @version    SVN: $Id$
 */
abstract class BaseScheduleOfRateBillLayoutSettingForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'                      => new sfWidgetFormInputHidden(),
      'project_structure_id'    => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('ProjectStructure'), 'add_empty' => true)),
      'font'                    => new sfWidgetFormTextarea(),
      'size'                    => new sfWidgetFormInputText(),
      'comma_total'             => new sfWidgetFormInputCheckbox(),
      'comma_rate'              => new sfWidgetFormInputCheckbox(),
      'priceFormat'             => new sfWidgetFormChoice(array('choices' => array('normal' => 'normal', 'opposite' => 'opposite'))),
      'includeIAndOForBillRef'  => new sfWidgetFormInputCheckbox(),
      'add_cont'                => new sfWidgetFormInputCheckbox(),
      'contd'                   => new sfWidgetFormInputText(),
      'print_element_grid'      => new sfWidgetFormInputCheckbox(),
      'print_element_grid_once' => new sfWidgetFormInputCheckbox(),
      'page_no_prefix'          => new sfWidgetFormInputText(),
      'align_element_to_left'   => new sfWidgetFormInputCheckbox(),
      'created_at'              => new sfWidgetFormDateTime(),
      'updated_at'              => new sfWidgetFormDateTime(),
      'created_by'              => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Creator'), 'add_empty' => true)),
      'updated_by'              => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Updator'), 'add_empty' => true)),
      'deleted_at'              => new sfWidgetFormDateTime(),
    ));

    $this->setValidators(array(
      'id'                      => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'project_structure_id'    => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('ProjectStructure'), 'column' => 'id', 'required' => false)),
      'font'                    => new sfValidatorString(array('required' => false)),
      'size'                    => new sfValidatorInteger(array('required' => false)),
      'comma_total'             => new sfValidatorBoolean(array('required' => false)),
      'comma_rate'              => new sfValidatorBoolean(array('required' => false)),
      'priceFormat'             => new sfValidatorChoice(array('choices' => array(0 => 'normal', 1 => 'opposite'), 'required' => false)),
      'includeIAndOForBillRef'  => new sfValidatorBoolean(array('required' => false)),
      'add_cont'                => new sfValidatorBoolean(array('required' => false)),
      'contd'                   => new sfValidatorString(array('max_length' => 150, 'required' => false)),
      'print_element_grid'      => new sfValidatorBoolean(array('required' => false)),
      'print_element_grid_once' => new sfValidatorBoolean(array('required' => false)),
      'page_no_prefix'          => new sfValidatorString(array('max_length' => 150, 'required' => false)),
      'align_element_to_left'   => new sfValidatorBoolean(array('required' => false)),
      'created_at'              => new sfValidatorDateTime(),
      'updated_at'              => new sfValidatorDateTime(),
      'created_by'              => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Creator'), 'column' => 'id', 'required' => false)),
      'updated_by'              => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Updator'), 'column' => 'id', 'required' => false)),
      'deleted_at'              => new sfValidatorDateTime(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('schedule_of_rate_bill_layout_setting[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'ScheduleOfRateBillLayoutSetting';
  }

}
