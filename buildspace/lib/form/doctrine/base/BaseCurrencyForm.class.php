<?php

/**
 * Currency form base class.
 *
 * @method Currency getObject() Returns the current form's model object
 *
 * @package    buildspace
 * @subpackage form
 * @author     1337 developers
 * @version    SVN: $Id$
 */
abstract class BaseCurrencyForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'            => new sfWidgetFormInputHidden(),
      'currency_name' => new sfWidgetFormInputText(),
      'currency_code' => new sfWidgetFormInputText(),
    ));

    $this->setValidators(array(
      'id'            => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'currency_name' => new sfValidatorString(array('max_length' => 60, 'required' => false)),
      'currency_code' => new sfValidatorString(array('max_length' => 3, 'required' => false)),
    ));

    $this->widgetSchema->setNameFormat('currency[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'Currency';
  }

}
