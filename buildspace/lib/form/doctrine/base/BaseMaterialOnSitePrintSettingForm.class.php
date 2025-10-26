<?php

/**
 * MaterialOnSitePrintSetting form base class.
 *
 * @method MaterialOnSitePrintSetting getObject() Returns the current form's model object
 *
 * @package    buildspace
 * @subpackage form
 * @author     1337 developers
 * @version    SVN: $Id$
 */
abstract class BaseMaterialOnSitePrintSettingForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'                                  => new sfWidgetFormInputHidden(),
      'project_structure_id'                => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Project'), 'add_empty' => true)),
      'project_name'                        => new sfWidgetFormTextarea(),
      'site_belonging_address'              => new sfWidgetFormInputText(),
      'original_finished_date'              => new sfWidgetFormInputText(),
      'contract_duration'                   => new sfWidgetFormInputText(),
      'contract_original_amount'            => new sfWidgetFormInputText(),
      'payment_revision_no'                 => new sfWidgetFormInputText(),
      'evaluation_date'                     => new sfWidgetFormInputText(),
      'total_text'                          => new sfWidgetFormInputText(),
      'percentage_of_material_on_site_text' => new sfWidgetFormInputText(),
      'carried_to_final_summary_text'       => new sfWidgetFormInputText(),
      'created_at'                          => new sfWidgetFormDateTime(),
      'updated_at'                          => new sfWidgetFormDateTime(),
      'created_by'                          => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Creator'), 'add_empty' => true)),
      'updated_by'                          => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Updator'), 'add_empty' => true)),
      'deleted_at'                          => new sfWidgetFormDateTime(),
    ));

    $this->setValidators(array(
      'id'                                  => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'project_structure_id'                => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Project'), 'column' => 'id', 'required' => false)),
      'project_name'                        => new sfValidatorString(),
      'site_belonging_address'              => new sfValidatorString(array('max_length' => 100, 'required' => false)),
      'original_finished_date'              => new sfValidatorString(array('max_length' => 100, 'required' => false)),
      'contract_duration'                   => new sfValidatorString(array('max_length' => 100, 'required' => false)),
      'contract_original_amount'            => new sfValidatorString(array('max_length' => 100, 'required' => false)),
      'payment_revision_no'                 => new sfValidatorString(array('max_length' => 100, 'required' => false)),
      'evaluation_date'                     => new sfValidatorString(array('max_length' => 100, 'required' => false)),
      'total_text'                          => new sfValidatorString(array('max_length' => 100, 'required' => false)),
      'percentage_of_material_on_site_text' => new sfValidatorString(array('max_length' => 100, 'required' => false)),
      'carried_to_final_summary_text'       => new sfValidatorString(array('max_length' => 100, 'required' => false)),
      'created_at'                          => new sfValidatorDateTime(),
      'updated_at'                          => new sfValidatorDateTime(),
      'created_by'                          => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Creator'), 'column' => 'id', 'required' => false)),
      'updated_by'                          => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Updator'), 'column' => 'id', 'required' => false)),
      'deleted_at'                          => new sfValidatorDateTime(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('material_on_site_print_setting[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'MaterialOnSitePrintSetting';
  }

}
