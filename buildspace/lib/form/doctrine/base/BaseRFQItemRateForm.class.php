<?php

/**
 * RFQItemRate form base class.
 *
 * @method RFQItemRate getObject() Returns the current form's model object
 *
 * @package    buildspace
 * @subpackage form
 * @author     1337 developers
 * @version    SVN: $Id$
 */
abstract class BaseRFQItemRateForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'                                => new sfWidgetFormInputHidden(),
      'request_for_quotation_item_id'     => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('RFQItem'), 'add_empty' => false)),
      'request_for_quotation_supplier_id' => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Supplier'), 'add_empty' => false)),
      'rate'                              => new sfWidgetFormInputText(),
      'created_at'                        => new sfWidgetFormDateTime(),
      'updated_at'                        => new sfWidgetFormDateTime(),
      'created_by'                        => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Creator'), 'add_empty' => true)),
      'updated_by'                        => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Updator'), 'add_empty' => true)),
      'resource_item_selected_rates_list' => new sfWidgetFormDoctrineChoice(array('multiple' => true, 'model' => 'ResourceItemSelectedRate')),
    ));

    $this->setValidators(array(
      'id'                                => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'request_for_quotation_item_id'     => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('RFQItem'), 'column' => 'id')),
      'request_for_quotation_supplier_id' => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Supplier'), 'column' => 'id')),
      'rate'                              => new sfValidatorNumber(array('required' => false)),
      'created_at'                        => new sfValidatorDateTime(),
      'updated_at'                        => new sfValidatorDateTime(),
      'created_by'                        => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Creator'), 'column' => 'id', 'required' => false)),
      'updated_by'                        => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Updator'), 'column' => 'id', 'required' => false)),
      'resource_item_selected_rates_list' => new sfValidatorDoctrineChoice(array('multiple' => true, 'model' => 'ResourceItemSelectedRate', 'required' => false)),
    ));

    $this->validatorSchema->setPostValidator(
      new sfValidatorDoctrineUnique(array('model' => 'RFQItemRate', 'column' => array('request_for_quotation_item_id', 'request_for_quotation_supplier_id')))
    );

    $this->widgetSchema->setNameFormat('rfq_item_rate[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'RFQItemRate';
  }

  public function updateDefaultsFromObject()
  {
    parent::updateDefaultsFromObject();

    if (isset($this->widgetSchema['resource_item_selected_rates_list']))
    {
      $this->setDefault('resource_item_selected_rates_list', $this->object->ResourceItemSelectedRates->getPrimaryKeys());
    }

  }

  protected function doUpdateObject($values)
  {
    $this->updateResourceItemSelectedRatesList($values);

    parent::doUpdateObject($values);
  }

  public function updateResourceItemSelectedRatesList($values)
  {
    if (!isset($this->widgetSchema['resource_item_selected_rates_list']))
    {
      // somebody has unset this widget
      return;
    }

    if (!array_key_exists('resource_item_selected_rates_list', $values))
    {
      // no values for this widget
      return;
    }

    $existing = $this->object->ResourceItemSelectedRates->getPrimaryKeys();
    $values = $values['resource_item_selected_rates_list'];
    if (!is_array($values))
    {
      $values = array();
    }

    $unlink = array_diff($existing, $values);
    if (count($unlink))
    {
      $this->object->unlink('ResourceItemSelectedRates', array_values($unlink));
    }

    $link = array_diff($values, $existing);
    if (count($link))
    {
      $this->object->link('ResourceItemSelectedRates', array_values($link));
    }
  }

}
