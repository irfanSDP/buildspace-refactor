<?php

/**
 * ItemCodeSettingObjectBreakdown form base class.
 *
 * @method ItemCodeSettingObjectBreakdown getObject() Returns the current form's model object
 *
 * @package    buildspace
 * @subpackage form
 * @author     1337 developers
 * @version    SVN: $Id$
 */
abstract class BaseItemCodeSettingObjectBreakdownForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'                          => new sfWidgetFormInputHidden(),
      'item_code_setting_object_id' => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('ItemCodeSettingObject'), 'add_empty' => false)),
      'claim_certificate_id'        => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('ClaimCertificate'), 'add_empty' => false)),
      'item_code_setting_id'        => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('ItemCodeSetting'), 'add_empty' => false)),
      'amount'                      => new sfWidgetFormInputText(),
      'created_at'                  => new sfWidgetFormDateTime(),
      'updated_at'                  => new sfWidgetFormDateTime(),
      'created_by'                  => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Creator'), 'add_empty' => true)),
      'updated_by'                  => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Updator'), 'add_empty' => true)),
    ));

    $this->setValidators(array(
      'id'                          => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'item_code_setting_object_id' => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('ItemCodeSettingObject'), 'column' => 'id')),
      'claim_certificate_id'        => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('ClaimCertificate'), 'column' => 'id')),
      'item_code_setting_id'        => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('ItemCodeSetting'), 'column' => 'id')),
      'amount'                      => new sfValidatorNumber(array('required' => false)),
      'created_at'                  => new sfValidatorDateTime(),
      'updated_at'                  => new sfValidatorDateTime(),
      'created_by'                  => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Creator'), 'column' => 'id', 'required' => false)),
      'updated_by'                  => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Updator'), 'column' => 'id', 'required' => false)),
    ));

    $this->validatorSchema->setPostValidator(
      new sfValidatorDoctrineUnique(array('model' => 'ItemCodeSettingObjectBreakdown', 'column' => array('item_code_setting_object_id', 'claim_certificate_id', 'item_code_setting_id')))
    );

    $this->widgetSchema->setNameFormat('item_code_setting_object_breakdown[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'ItemCodeSettingObjectBreakdown';
  }

}
