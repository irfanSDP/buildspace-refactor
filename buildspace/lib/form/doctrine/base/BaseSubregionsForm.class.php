<?php

/**
 * Subregions form base class.
 *
 * @method Subregions getObject() Returns the current form's model object
 *
 * @package    buildspace
 * @subpackage form
 * @author     1337 developers
 * @version    SVN: $Id$
 */
abstract class BaseSubregionsForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'        => new sfWidgetFormInputHidden(),
      'region_id' => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Regions'), 'add_empty' => false)),
      'name'      => new sfWidgetFormInputText(),
      'timezone'  => new sfWidgetFormInputText(),
    ));

    $this->setValidators(array(
      'id'        => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'region_id' => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Regions'), 'column' => 'id')),
      'name'      => new sfValidatorPass(),
      'timezone'  => new sfValidatorPass(array('required' => false)),
    ));

    $this->validatorSchema->setPostValidator(
      new sfValidatorDoctrineUnique(array('model' => 'Subregions', 'column' => array('region_id', 'name')))
    );

    $this->widgetSchema->setNameFormat('subregions[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'Subregions';
  }

}
