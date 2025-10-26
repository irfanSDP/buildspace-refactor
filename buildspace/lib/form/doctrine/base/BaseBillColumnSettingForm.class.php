<?php

/**
 * BillColumnSetting form base class.
 *
 * @method BillColumnSetting getObject() Returns the current form's model object
 *
 * @package    buildspace
 * @subpackage form
 * @author     1337 developers
 * @version    SVN: $Id$
 */
abstract class BaseBillColumnSettingForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'                             => new sfWidgetFormInputHidden(),
      'name'                           => new sfWidgetFormInputText(),
      'quantity'                       => new sfWidgetFormInputText(),
      'project_structure_id'           => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('ProjectStructure'), 'add_empty' => false)),
      'remeasurement_quantity_enabled' => new sfWidgetFormInputCheckbox(),
      'use_original_quantity'          => new sfWidgetFormInputCheckbox(),
      'total_floor_area_m2'            => new sfWidgetFormInputText(),
      'total_floor_area_ft2'           => new sfWidgetFormInputText(),
      'floor_area_has_build_up'        => new sfWidgetFormInputCheckbox(),
      'floor_area_use_metric'          => new sfWidgetFormInputCheckbox(),
      'floor_area_display_metric'      => new sfWidgetFormInputCheckbox(),
      'show_estimated_total_cost'      => new sfWidgetFormInputCheckbox(),
      'tender_origin_id'               => new sfWidgetFormTextarea(),
      'is_hidden'                      => new sfWidgetFormInputCheckbox(),
      'created_at'                     => new sfWidgetFormDateTime(),
      'updated_at'                     => new sfWidgetFormDateTime(),
      'created_by'                     => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Creator'), 'add_empty' => true)),
      'updated_by'                     => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Updator'), 'add_empty' => true)),
      'deleted_at'                     => new sfWidgetFormDateTime(),
    ));

    $this->setValidators(array(
      'id'                             => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'name'                           => new sfValidatorString(array('max_length' => 200)),
      'quantity'                       => new sfValidatorInteger(array('required' => false)),
      'project_structure_id'           => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('ProjectStructure'), 'column' => 'id')),
      'remeasurement_quantity_enabled' => new sfValidatorBoolean(array('required' => false)),
      'use_original_quantity'          => new sfValidatorBoolean(array('required' => false)),
      'total_floor_area_m2'            => new sfValidatorNumber(array('required' => false)),
      'total_floor_area_ft2'           => new sfValidatorNumber(array('required' => false)),
      'floor_area_has_build_up'        => new sfValidatorBoolean(array('required' => false)),
      'floor_area_use_metric'          => new sfValidatorBoolean(array('required' => false)),
      'floor_area_display_metric'      => new sfValidatorBoolean(array('required' => false)),
      'show_estimated_total_cost'      => new sfValidatorBoolean(array('required' => false)),
      'tender_origin_id'               => new sfValidatorString(array('required' => false)),
      'is_hidden'                      => new sfValidatorBoolean(array('required' => false)),
      'created_at'                     => new sfValidatorDateTime(),
      'updated_at'                     => new sfValidatorDateTime(),
      'created_by'                     => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Creator'), 'column' => 'id', 'required' => false)),
      'updated_by'                     => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Updator'), 'column' => 'id', 'required' => false)),
      'deleted_at'                     => new sfValidatorDateTime(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('bill_column_setting[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'BillColumnSetting';
  }

}
