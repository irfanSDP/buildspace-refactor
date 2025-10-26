<?php

/**
 * ClaimCertificate form base class.
 *
 * @method ClaimCertificate getObject() Returns the current form's model object
 *
 * @package    buildspace
 * @subpackage form
 * @author     1337 developers
 * @version    SVN: $Id$
 */
abstract class BaseClaimCertificateForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'                              => new sfWidgetFormInputHidden(),
      'post_contract_claim_revision_id' => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('PostContractClaimRevision'), 'add_empty' => false)),
      'contractor_submitted_date'       => new sfWidgetFormDate(),
      'site_verified_date'              => new sfWidgetFormDate(),
      'qs_received_date'                => new sfWidgetFormDate(),
      'release_retention_amount'        => new sfWidgetFormInputText(),
      'release_retention_percentage'    => new sfWidgetFormInputText(),
      'retention_tax_percentage'        => new sfWidgetFormInputText(),
      'amount_certified'                => new sfWidgetFormInputText(),
      'person_in_charge'                => new sfWidgetFormInputText(),
      'valuation_date'                  => new sfWidgetFormDate(),
      'due_date'                        => new sfWidgetFormDate(),
      'budget_amount'                   => new sfWidgetFormInputText(),
      'budget_due_date'                 => new sfWidgetFormDate(),
      'tax_percentage'                  => new sfWidgetFormInputText(),
      'acc_remarks'                     => new sfWidgetFormTextarea(),
      'qs_remarks'                      => new sfWidgetFormTextarea(),
      'status'                          => new sfWidgetFormInputText(),
      'created_at'                      => new sfWidgetFormDateTime(),
      'updated_at'                      => new sfWidgetFormDateTime(),
      'created_by'                      => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Creator'), 'add_empty' => true)),
      'updated_by'                      => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Updator'), 'add_empty' => true)),
    ));

    $this->setValidators(array(
      'id'                              => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'post_contract_claim_revision_id' => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('PostContractClaimRevision'), 'column' => 'id')),
      'contractor_submitted_date'       => new sfValidatorDate(),
      'site_verified_date'              => new sfValidatorDate(),
      'qs_received_date'                => new sfValidatorDate(),
      'release_retention_amount'        => new sfValidatorNumber(array('required' => false)),
      'release_retention_percentage'    => new sfValidatorNumber(array('required' => false)),
      'retention_tax_percentage'        => new sfValidatorNumber(array('required' => false)),
      'amount_certified'                => new sfValidatorNumber(array('required' => false)),
      'person_in_charge'                => new sfValidatorString(array('max_length' => 255)),
      'valuation_date'                  => new sfValidatorDate(array('required' => false)),
      'due_date'                        => new sfValidatorDate(),
      'budget_amount'                   => new sfValidatorNumber(array('required' => false)),
      'budget_due_date'                 => new sfValidatorDate(),
      'tax_percentage'                  => new sfValidatorNumber(array('required' => false)),
      'acc_remarks'                     => new sfValidatorString(array('required' => false)),
      'qs_remarks'                      => new sfValidatorString(array('required' => false)),
      'status'                          => new sfValidatorPass(),
      'created_at'                      => new sfValidatorDateTime(),
      'updated_at'                      => new sfValidatorDateTime(),
      'created_by'                      => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Creator'), 'column' => 'id', 'required' => false)),
      'updated_by'                      => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Updator'), 'column' => 'id', 'required' => false)),
    ));

    $this->validatorSchema->setPostValidator(
      new sfValidatorDoctrineUnique(array('model' => 'ClaimCertificate', 'column' => array('post_contract_claim_revision_id')))
    );

    $this->widgetSchema->setNameFormat('claim_certificate[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'ClaimCertificate';
  }

}
