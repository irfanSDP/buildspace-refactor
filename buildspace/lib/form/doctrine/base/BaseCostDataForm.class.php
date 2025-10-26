<?php

/**
 * CostData form base class.
 *
 * @method CostData getObject() Returns the current form's model object
 *
 * @package    buildspace
 * @subpackage form
 * @author     1337 developers
 * @version    SVN: $Id$
 */
abstract class BaseCostDataForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'                  => new sfWidgetFormInputHidden(),
      'master_cost_data_id' => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('MasterCostData'), 'add_empty' => false)),
      'name'                => new sfWidgetFormInputText(),
      'subsidiary_id'       => new sfWidgetFormInputText(),
      'approved_date'       => new sfWidgetFormDateTime(),
      'awarded_date'        => new sfWidgetFormDateTime(),
      'adjusted_date'       => new sfWidgetFormDateTime(),
      'cost_data_type_id'   => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('CostDataType'), 'add_empty' => false)),
      'region_id'           => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Region'), 'add_empty' => false)),
      'subregion_id'        => new sfWidgetFormInputText(),
      'currency_id'         => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Currency'), 'add_empty' => false)),
      'tender_date'         => new sfWidgetFormDateTime(),
      'award_date'          => new sfWidgetFormDateTime(),
      'created_at'          => new sfWidgetFormDateTime(),
      'updated_at'          => new sfWidgetFormDateTime(),
      'created_by'          => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Creator'), 'add_empty' => true)),
      'updated_by'          => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Updator'), 'add_empty' => true)),
      'deleted_at'          => new sfWidgetFormDateTime(),
    ));

    $this->setValidators(array(
      'id'                  => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'master_cost_data_id' => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('MasterCostData'), 'column' => 'id')),
      'name'                => new sfValidatorString(array('max_length' => 200)),
      'subsidiary_id'       => new sfValidatorInteger(),
      'approved_date'       => new sfValidatorDateTime(array('required' => false)),
      'awarded_date'        => new sfValidatorDateTime(array('required' => false)),
      'adjusted_date'       => new sfValidatorDateTime(array('required' => false)),
      'cost_data_type_id'   => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('CostDataType'), 'column' => 'id')),
      'region_id'           => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Region'), 'column' => 'id')),
      'subregion_id'        => new sfValidatorInteger(),
      'currency_id'         => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Currency'), 'column' => 'id')),
      'tender_date'         => new sfValidatorDateTime(array('required' => false)),
      'award_date'          => new sfValidatorDateTime(array('required' => false)),
      'created_at'          => new sfValidatorDateTime(),
      'updated_at'          => new sfValidatorDateTime(),
      'created_by'          => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Creator'), 'column' => 'id', 'required' => false)),
      'updated_by'          => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Updator'), 'column' => 'id', 'required' => false)),
      'deleted_at'          => new sfValidatorDateTime(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('cost_data[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'CostData';
  }

}
