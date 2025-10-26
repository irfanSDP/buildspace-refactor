<?php

/**
 * TenderBillItemRationalizedLumpSumPercentage form base class.
 *
 * @method TenderBillItemRationalizedLumpSumPercentage getObject() Returns the current form's model object
 *
 * @package    buildspace
 * @subpackage form
 * @author     1337 developers
 * @version    SVN: $Id$
 */
abstract class BaseTenderBillItemRationalizedLumpSumPercentageForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'                                     => new sfWidgetFormInputHidden(),
      'tender_bill_item_rationalized_rates_id' => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('TenderBillItemRationalizedRates'), 'add_empty' => false)),
      'rate'                                   => new sfWidgetFormInputText(),
      'percentage'                             => new sfWidgetFormInputText(),
      'amount'                                 => new sfWidgetFormInputText(),
      'created_at'                             => new sfWidgetFormDateTime(),
      'updated_at'                             => new sfWidgetFormDateTime(),
      'created_by'                             => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Creator'), 'add_empty' => true)),
      'updated_by'                             => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Updator'), 'add_empty' => true)),
    ));

    $this->setValidators(array(
      'id'                                     => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'tender_bill_item_rationalized_rates_id' => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('TenderBillItemRationalizedRates'), 'column' => 'id')),
      'rate'                                   => new sfValidatorNumber(array('required' => false)),
      'percentage'                             => new sfValidatorNumber(array('required' => false)),
      'amount'                                 => new sfValidatorNumber(array('required' => false)),
      'created_at'                             => new sfValidatorDateTime(),
      'updated_at'                             => new sfValidatorDateTime(),
      'created_by'                             => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Creator'), 'column' => 'id', 'required' => false)),
      'updated_by'                             => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Updator'), 'column' => 'id', 'required' => false)),
    ));

    $this->validatorSchema->setPostValidator(
      new sfValidatorDoctrineUnique(array('model' => 'TenderBillItemRationalizedLumpSumPercentage', 'column' => array('tender_bill_item_rationalized_rates_id')))
    );

    $this->widgetSchema->setNameFormat('tender_bill_item_rationalized_lump_sum_percentage[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'TenderBillItemRationalizedLumpSumPercentage';
  }

}
