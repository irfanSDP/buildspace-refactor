<?php

/**
 * ResourceItemSelectedRate form base class.
 *
 * @method ResourceItemSelectedRate getObject() Returns the current form's model object
 *
 * @package    buildspace
 * @subpackage form
 * @author     1337 developers
 * @version    SVN: $Id$
 */
abstract class BaseResourceItemSelectedRateForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'                  => new sfWidgetFormInputHidden(),
      'resource_item_id'    => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('ResourceItem'), 'add_empty' => true)),
      'sorting_type'        => new sfWidgetFormInputText(),
      'created_at'          => new sfWidgetFormDateTime(),
      'updated_at'          => new sfWidgetFormDateTime(),
      'created_by'          => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Creator'), 'add_empty' => true)),
      'updated_by'          => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Updator'), 'add_empty' => true)),
      'rfq_item_rates_list' => new sfWidgetFormDoctrineChoice(array('multiple' => true, 'model' => 'RFQItemRate')),
    ));

    $this->setValidators(array(
      'id'                  => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'resource_item_id'    => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('ResourceItem'), 'column' => 'id', 'required' => false)),
      'sorting_type'        => new sfValidatorInteger(array('required' => false)),
      'created_at'          => new sfValidatorDateTime(),
      'updated_at'          => new sfValidatorDateTime(),
      'created_by'          => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Creator'), 'column' => 'id', 'required' => false)),
      'updated_by'          => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Updator'), 'column' => 'id', 'required' => false)),
      'rfq_item_rates_list' => new sfValidatorDoctrineChoice(array('multiple' => true, 'model' => 'RFQItemRate', 'required' => false)),
    ));

    $this->validatorSchema->setPostValidator(
      new sfValidatorDoctrineUnique(array('model' => 'ResourceItemSelectedRate', 'column' => array('resource_item_id')))
    );

    $this->widgetSchema->setNameFormat('resource_item_selected_rate[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'ResourceItemSelectedRate';
  }

  public function updateDefaultsFromObject()
  {
    parent::updateDefaultsFromObject();

    if (isset($this->widgetSchema['rfq_item_rates_list']))
    {
      $this->setDefault('rfq_item_rates_list', $this->object->RFQItemRates->getPrimaryKeys());
    }

  }

  protected function doUpdateObject($values)
  {
    $this->updateRFQItemRatesList($values);

    parent::doUpdateObject($values);
  }

  public function updateRFQItemRatesList($values)
  {
    if (!isset($this->widgetSchema['rfq_item_rates_list']))
    {
      // somebody has unset this widget
      return;
    }

    if (!array_key_exists('rfq_item_rates_list', $values))
    {
      // no values for this widget
      return;
    }

    $existing = $this->object->RFQItemRates->getPrimaryKeys();
    $values = $values['rfq_item_rates_list'];
    if (!is_array($values))
    {
      $values = array();
    }

    $unlink = array_diff($existing, $values);
    if (count($unlink))
    {
      $this->object->unlink('RFQItemRates', array_values($unlink));
    }

    $link = array_diff($values, $existing);
    if (count($link))
    {
      $this->object->link('RFQItemRates', array_values($link));
    }
  }

}
