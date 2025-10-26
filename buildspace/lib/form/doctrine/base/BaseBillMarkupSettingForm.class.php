<?php

/**
 * BillMarkupSetting form base class.
 *
 * @method BillMarkupSetting getObject() Returns the current form's model object
 *
 * @package    buildspace
 * @subpackage form
 * @author     1337 developers
 * @version    SVN: $Id$
 */
abstract class BaseBillMarkupSettingForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'                     => new sfWidgetFormInputHidden(),
      'project_structure_id'   => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('ProjectStructure'), 'add_empty' => false)),
      'bill_markup_enabled'    => new sfWidgetFormInputCheckbox(),
      'bill_markup_percentage' => new sfWidgetFormInputText(),
      'bill_markup_amount'     => new sfWidgetFormInputText(),
      'element_markup_enabled' => new sfWidgetFormInputCheckbox(),
      'item_markup_enabled'    => new sfWidgetFormInputCheckbox(),
      'rounding_type'          => new sfWidgetFormInputText(),
      'created_at'             => new sfWidgetFormDateTime(),
      'updated_at'             => new sfWidgetFormDateTime(),
      'created_by'             => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Creator'), 'add_empty' => true)),
      'updated_by'             => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Updator'), 'add_empty' => true)),
      'deleted_at'             => new sfWidgetFormDateTime(),
    ));

    $this->setValidators(array(
      'id'                     => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'project_structure_id'   => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('ProjectStructure'), 'column' => 'id')),
      'bill_markup_enabled'    => new sfValidatorBoolean(array('required' => false)),
      'bill_markup_percentage' => new sfValidatorNumber(array('required' => false)),
      'bill_markup_amount'     => new sfValidatorNumber(array('required' => false)),
      'element_markup_enabled' => new sfValidatorBoolean(array('required' => false)),
      'item_markup_enabled'    => new sfValidatorBoolean(array('required' => false)),
      'rounding_type'          => new sfValidatorInteger(),
      'created_at'             => new sfValidatorDateTime(),
      'updated_at'             => new sfValidatorDateTime(),
      'created_by'             => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Creator'), 'column' => 'id', 'required' => false)),
      'updated_by'             => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Updator'), 'column' => 'id', 'required' => false)),
      'deleted_at'             => new sfValidatorDateTime(array('required' => false)),
    ));

    $this->validatorSchema->setPostValidator(
      new sfValidatorDoctrineUnique(array('model' => 'BillMarkupSetting', 'column' => array('project_structure_id', 'deleted_at')))
    );

    $this->widgetSchema->setNameFormat('bill_markup_setting[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'BillMarkupSetting';
  }

}
