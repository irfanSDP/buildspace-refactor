<?php

/**
 * ResourceItemSelectionOfRate form base class.
 *
 * @method ResourceItemSelectionOfRate getObject() Returns the current form's model object
 *
 * @package    buildspace
 * @subpackage form
 * @author     1337 developers
 * @version    SVN: $Id$
 */
abstract class BaseResourceItemSelectionOfRateForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'               => new sfWidgetFormInputHidden(),
      'resource_item_id' => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('ResourceItem'), 'add_empty' => true)),
      'sorting_type'     => new sfWidgetFormInputText(),
      'created_at'       => new sfWidgetFormDateTime(),
      'updated_at'       => new sfWidgetFormDateTime(),
      'created_by'       => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Creator'), 'add_empty' => true)),
      'updated_by'       => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Updator'), 'add_empty' => true)),
    ));

    $this->setValidators(array(
      'id'               => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'resource_item_id' => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('ResourceItem'), 'column' => 'id', 'required' => false)),
      'sorting_type'     => new sfValidatorInteger(array('required' => false)),
      'created_at'       => new sfValidatorDateTime(),
      'updated_at'       => new sfValidatorDateTime(),
      'created_by'       => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Creator'), 'column' => 'id', 'required' => false)),
      'updated_by'       => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Updator'), 'column' => 'id', 'required' => false)),
    ));

    $this->validatorSchema->setPostValidator(
      new sfValidatorDoctrineUnique(array('model' => 'ResourceItemSelectionOfRate', 'column' => array('resource_item_id')))
    );

    $this->widgetSchema->setNameFormat('resource_item_selection_of_rate[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'ResourceItemSelectionOfRate';
  }

}
