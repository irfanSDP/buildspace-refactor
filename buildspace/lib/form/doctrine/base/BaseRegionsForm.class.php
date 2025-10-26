<?php

/**
 * Regions form base class.
 *
 * @method Regions getObject() Returns the current form's model object
 *
 * @package    buildspace
 * @subpackage form
 * @author     1337 developers
 * @version    SVN: $Id$
 */
abstract class BaseRegionsForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'            => new sfWidgetFormInputHidden(),
      'iso'           => new sfWidgetFormInputText(),
      'iso3'          => new sfWidgetFormInputText(),
      'fips'          => new sfWidgetFormInputText(),
      'country'       => new sfWidgetFormInputText(),
      'continent'     => new sfWidgetFormInputText(),
      'currency_code' => new sfWidgetFormInputText(),
      'currency_name' => new sfWidgetFormInputText(),
      'phone_prefix'  => new sfWidgetFormInputText(),
      'postal_code'   => new sfWidgetFormInputText(),
      'languages'     => new sfWidgetFormInputText(),
      'geonameid'     => new sfWidgetFormInputText(),
    ));

    $this->setValidators(array(
      'id'            => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'iso'           => new sfValidatorPass(array('required' => false)),
      'iso3'          => new sfValidatorPass(array('required' => false)),
      'fips'          => new sfValidatorPass(array('required' => false)),
      'country'       => new sfValidatorPass(),
      'continent'     => new sfValidatorPass(array('required' => false)),
      'currency_code' => new sfValidatorString(array('max_length' => 3, 'required' => false)),
      'currency_name' => new sfValidatorString(array('max_length' => 60, 'required' => false)),
      'phone_prefix'  => new sfValidatorPass(array('required' => false)),
      'postal_code'   => new sfValidatorPass(array('required' => false)),
      'languages'     => new sfValidatorPass(array('required' => false)),
      'geonameid'     => new sfValidatorPass(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('regions[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'Regions';
  }

}
