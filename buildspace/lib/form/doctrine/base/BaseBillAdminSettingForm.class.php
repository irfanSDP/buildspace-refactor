<?php

/**
 * BillAdminSetting form base class.
 *
 * @method BillAdminSetting getObject() Returns the current form's model object
 *
 * @package    buildspace
 * @subpackage form
 * @author     1337 developers
 * @version    SVN: $Id$
 */
abstract class BaseBillAdminSettingForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'                              => new sfWidgetFormInputHidden(),
      'build_up_quantity_rounding_type' => new sfWidgetFormInputText(),
      'build_up_rate_rounding_type'     => new sfWidgetFormInputText(),
      'unit_type'                       => new sfWidgetFormInputText(),
      'created_at'                      => new sfWidgetFormDateTime(),
      'updated_at'                      => new sfWidgetFormDateTime(),
    ));

    $this->setValidators(array(
      'id'                              => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'build_up_quantity_rounding_type' => new sfValidatorInteger(array('required' => false)),
      'build_up_rate_rounding_type'     => new sfValidatorInteger(array('required' => false)),
      'unit_type'                       => new sfValidatorInteger(array('required' => false)),
      'created_at'                      => new sfValidatorDateTime(),
      'updated_at'                      => new sfValidatorDateTime(),
    ));

    $this->widgetSchema->setNameFormat('bill_admin_setting[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'BillAdminSetting';
  }

}
