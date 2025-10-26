<?php

/**
 * BillLayoutHeadSetting form base class.
 *
 * @method BillLayoutHeadSetting getObject() Returns the current form's model object
 *
 * @package    buildspace
 * @subpackage form
 * @author     1337 developers
 * @version    SVN: $Id$
 */
abstract class BaseBillLayoutHeadSettingForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'                     => new sfWidgetFormInputHidden(),
      'bill_layout_setting_id' => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('BillHeadSetting'), 'add_empty' => true)),
      'head'                   => new sfWidgetFormInputText(),
      'bold'                   => new sfWidgetFormInputCheckbox(),
      'underline'              => new sfWidgetFormInputCheckbox(),
      'italic'                 => new sfWidgetFormInputCheckbox(),
      'created_at'             => new sfWidgetFormDateTime(),
      'updated_at'             => new sfWidgetFormDateTime(),
      'created_by'             => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Creator'), 'add_empty' => true)),
      'updated_by'             => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Updator'), 'add_empty' => true)),
      'deleted_at'             => new sfWidgetFormDateTime(),
    ));

    $this->setValidators(array(
      'id'                     => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'bill_layout_setting_id' => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('BillHeadSetting'), 'column' => 'id', 'required' => false)),
      'head'                   => new sfValidatorInteger(array('required' => false)),
      'bold'                   => new sfValidatorBoolean(array('required' => false)),
      'underline'              => new sfValidatorBoolean(array('required' => false)),
      'italic'                 => new sfValidatorBoolean(array('required' => false)),
      'created_at'             => new sfValidatorDateTime(),
      'updated_at'             => new sfValidatorDateTime(),
      'created_by'             => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Creator'), 'column' => 'id', 'required' => false)),
      'updated_by'             => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Updator'), 'column' => 'id', 'required' => false)),
      'deleted_at'             => new sfValidatorDateTime(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('bill_layout_head_setting[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'BillLayoutHeadSetting';
  }

}
