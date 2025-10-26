<?php

/**
 * Currency filter form base class.
 *
 * @package    buildspace
 * @subpackage filter
 * @author     1337 developers
 * @version    SVN: $Id$
 */
abstract class BaseCurrencyFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'currency_name' => new sfWidgetFormFilterInput(),
      'currency_code' => new sfWidgetFormFilterInput(),
    ));

    $this->setValidators(array(
      'currency_name' => new sfValidatorPass(array('required' => false)),
      'currency_code' => new sfValidatorPass(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('currency_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'Currency';
  }

  public function getFields()
  {
    return array(
      'id'            => 'Number',
      'currency_name' => 'Text',
      'currency_code' => 'Text',
    );
  }
}
