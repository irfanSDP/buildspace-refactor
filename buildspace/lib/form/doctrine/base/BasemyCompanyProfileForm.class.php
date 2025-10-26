<?php

/**
 * myCompanyProfile form base class.
 *
 * @method myCompanyProfile getObject() Returns the current form's model object
 *
 * @package    buildspace
 * @subpackage form
 * @author     1337 developers
 * @version    SVN: $Id$
 */
abstract class BasemyCompanyProfileForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'           => new sfWidgetFormInputHidden(),
      'name'         => new sfWidgetFormInputText(),
      'address'      => new sfWidgetFormInputText(),
      'city'         => new sfWidgetFormInputText(),
      'region_id'    => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Regions'), 'add_empty' => true)),
      'subregion_id' => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Subregions'), 'add_empty' => true)),
      'zipcode'      => new sfWidgetFormInputText(),
      'timezone'     => new sfWidgetFormInputText(),
      'email'        => new sfWidgetFormInputText(),
      'phone_number' => new sfWidgetFormInputText(),
      'fax_number'   => new sfWidgetFormInputText(),
      'website'      => new sfWidgetFormInputText(),
      'company_logo' => new sfWidgetFormTextarea(),
      'created_at'   => new sfWidgetFormDateTime(),
      'updated_at'   => new sfWidgetFormDateTime(),
      'created_by'   => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Creator'), 'add_empty' => true)),
      'updated_by'   => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Updator'), 'add_empty' => true)),
    ));

    $this->setValidators(array(
      'id'           => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'name'         => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'address'      => new sfValidatorPass(array('required' => false)),
      'city'         => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'region_id'    => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Regions'), 'column' => 'id', 'required' => false)),
      'subregion_id' => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Subregions'), 'column' => 'id', 'required' => false)),
      'zipcode'      => new sfValidatorString(array('max_length' => 50, 'required' => false)),
      'timezone'     => new sfValidatorString(array('max_length' => 50, 'required' => false)),
      'email'        => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'phone_number' => new sfValidatorString(array('max_length' => 50, 'required' => false)),
      'fax_number'   => new sfValidatorString(array('max_length' => 50, 'required' => false)),
      'website'      => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'company_logo' => new sfValidatorString(array('required' => false)),
      'created_at'   => new sfValidatorDateTime(),
      'updated_at'   => new sfValidatorDateTime(),
      'created_by'   => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Creator'), 'column' => 'id', 'required' => false)),
      'updated_by'   => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Updator'), 'column' => 'id', 'required' => false)),
    ));

    $this->widgetSchema->setNameFormat('my_company_profile[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'myCompanyProfile';
  }

}
