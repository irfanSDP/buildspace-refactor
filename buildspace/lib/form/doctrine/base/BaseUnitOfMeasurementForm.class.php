<?php

/**
 * UnitOfMeasurement form base class.
 *
 * @method UnitOfMeasurement getObject() Returns the current form's model object
 *
 * @package    buildspace
 * @subpackage form
 * @author     1337 developers
 * @version    SVN: $Id$
 */
abstract class BaseUnitOfMeasurementForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'              => new sfWidgetFormInputHidden(),
      'name'            => new sfWidgetFormInputText(),
      'symbol'          => new sfWidgetFormInputText(),
      'display'         => new sfWidgetFormInputCheckbox(),
      'type'            => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('UnitOfMeasurementType'), 'add_empty' => false)),
      'created_at'      => new sfWidgetFormDateTime(),
      'updated_at'      => new sfWidgetFormDateTime(),
      'created_by'      => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Creator'), 'add_empty' => true)),
      'updated_by'      => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Updator'), 'add_empty' => true)),
      'deleted_at'      => new sfWidgetFormDateTime(),
      'dimensions_list' => new sfWidgetFormDoctrineChoice(array('multiple' => true, 'model' => 'Dimension')),
    ));

    $this->setValidators(array(
      'id'              => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'name'            => new sfValidatorString(array('max_length' => 200)),
      'symbol'          => new sfValidatorPass(),
      'display'         => new sfValidatorBoolean(array('required' => false)),
      'type'            => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('UnitOfMeasurementType'), 'column' => 'id')),
      'created_at'      => new sfValidatorDateTime(),
      'updated_at'      => new sfValidatorDateTime(),
      'created_by'      => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Creator'), 'column' => 'id', 'required' => false)),
      'updated_by'      => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Updator'), 'column' => 'id', 'required' => false)),
      'deleted_at'      => new sfValidatorDateTime(array('required' => false)),
      'dimensions_list' => new sfValidatorDoctrineChoice(array('multiple' => true, 'model' => 'Dimension', 'required' => false)),
    ));

    $this->validatorSchema->setPostValidator(
      new sfValidatorDoctrineUnique(array('model' => 'UnitOfMeasurement', 'column' => array('symbol', 'type', 'deleted_at')))
    );

    $this->widgetSchema->setNameFormat('unit_of_measurement[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'UnitOfMeasurement';
  }

  public function updateDefaultsFromObject()
  {
    parent::updateDefaultsFromObject();

    if (isset($this->widgetSchema['dimensions_list']))
    {
      $this->setDefault('dimensions_list', $this->object->Dimensions->getPrimaryKeys());
    }

  }

  protected function doUpdateObject($values)
  {
    $this->updateDimensionsList($values);

    parent::doUpdateObject($values);
  }

  public function updateDimensionsList($values)
  {
    if (!isset($this->widgetSchema['dimensions_list']))
    {
      // somebody has unset this widget
      return;
    }

    if (!array_key_exists('dimensions_list', $values))
    {
      // no values for this widget
      return;
    }

    $existing = $this->object->Dimensions->getPrimaryKeys();
    $values = $values['dimensions_list'];
    if (!is_array($values))
    {
      $values = array();
    }

    $unlink = array_diff($existing, $values);
    if (count($unlink))
    {
      $this->object->unlink('Dimensions', array_values($unlink));
    }

    $link = array_diff($values, $existing);
    if (count($link))
    {
      $this->object->link('Dimensions', array_values($link));
    }
  }

}
