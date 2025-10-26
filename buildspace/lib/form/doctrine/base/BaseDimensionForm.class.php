<?php

/**
 * Dimension form base class.
 *
 * @method Dimension getObject() Returns the current form's model object
 *
 * @package    buildspace
 * @subpackage form
 * @author     1337 developers
 * @version    SVN: $Id$
 */
abstract class BaseDimensionForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'                        => new sfWidgetFormInputHidden(),
      'name'                      => new sfWidgetFormInputText(),
      'created_at'                => new sfWidgetFormDateTime(),
      'updated_at'                => new sfWidgetFormDateTime(),
      'created_by'                => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Creator'), 'add_empty' => true)),
      'updated_by'                => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Updator'), 'add_empty' => true)),
      'deleted_at'                => new sfWidgetFormDateTime(),
      'unit_of_measurements_list' => new sfWidgetFormDoctrineChoice(array('multiple' => true, 'model' => 'UnitOfMeasurement')),
    ));

    $this->setValidators(array(
      'id'                        => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'name'                      => new sfValidatorString(array('max_length' => 50)),
      'created_at'                => new sfValidatorDateTime(),
      'updated_at'                => new sfValidatorDateTime(),
      'created_by'                => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Creator'), 'column' => 'id', 'required' => false)),
      'updated_by'                => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Updator'), 'column' => 'id', 'required' => false)),
      'deleted_at'                => new sfValidatorDateTime(array('required' => false)),
      'unit_of_measurements_list' => new sfValidatorDoctrineChoice(array('multiple' => true, 'model' => 'UnitOfMeasurement', 'required' => false)),
    ));

    $this->validatorSchema->setPostValidator(
      new sfValidatorDoctrineUnique(array('model' => 'Dimension', 'column' => array('name', 'deleted_at')))
    );

    $this->widgetSchema->setNameFormat('dimension[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'Dimension';
  }

  public function updateDefaultsFromObject()
  {
    parent::updateDefaultsFromObject();

    if (isset($this->widgetSchema['unit_of_measurements_list']))
    {
      $this->setDefault('unit_of_measurements_list', $this->object->UnitOfMeasurements->getPrimaryKeys());
    }

  }

  protected function doUpdateObject($values)
  {
    $this->updateUnitOfMeasurementsList($values);

    parent::doUpdateObject($values);
  }

  public function updateUnitOfMeasurementsList($values)
  {
    if (!isset($this->widgetSchema['unit_of_measurements_list']))
    {
      // somebody has unset this widget
      return;
    }

    if (!array_key_exists('unit_of_measurements_list', $values))
    {
      // no values for this widget
      return;
    }

    $existing = $this->object->UnitOfMeasurements->getPrimaryKeys();
    $values = $values['unit_of_measurements_list'];
    if (!is_array($values))
    {
      $values = array();
    }

    $unlink = array_diff($existing, $values);
    if (count($unlink))
    {
      $this->object->unlink('UnitOfMeasurements', array_values($unlink));
    }

    $link = array_diff($values, $existing);
    if (count($link))
    {
      $this->object->link('UnitOfMeasurements', array_values($link));
    }
  }

}
