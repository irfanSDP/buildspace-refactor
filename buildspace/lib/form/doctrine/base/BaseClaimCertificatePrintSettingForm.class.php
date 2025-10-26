<?php

/**
 * ClaimCertificatePrintSetting form base class.
 *
 * @method ClaimCertificatePrintSetting getObject() Returns the current form's model object
 *
 * @package    buildspace
 * @subpackage form
 * @author     1337 developers
 * @version    SVN: $Id$
 */
abstract class BaseClaimCertificatePrintSettingForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'                                         => new sfWidgetFormInputHidden(),
      'post_contract_id'                           => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('PostContract'), 'add_empty' => false)),
      'certificate_title'                          => new sfWidgetFormInputText(),
      'certificate_print_format'                   => new sfWidgetFormInputText(),
      'section_a_label'                            => new sfWidgetFormInputText(),
      'section_b_label'                            => new sfWidgetFormInputText(),
      'section_c_label'                            => new sfWidgetFormInputText(),
      'section_d_label'                            => new sfWidgetFormInputText(),
      'section_misc_label'                         => new sfWidgetFormInputText(),
      'section_others_label'                       => new sfWidgetFormInputText(),
      'section_payment_on_behalf_label'            => new sfWidgetFormInputText(),
      'tax_label'                                  => new sfWidgetFormInputText(),
      'tax_invoice_by_sub_contractor_label'        => new sfWidgetFormInputText(),
      'tax_invoice_by_subsidiary_label'            => new sfWidgetFormInputText(),
      'include_advance_payment'                    => new sfWidgetFormInputCheckbox(),
      'include_deposit'                            => new sfWidgetFormInputCheckbox(),
      'include_material_on_site'                   => new sfWidgetFormInputCheckbox(),
      'include_ksk'                                => new sfWidgetFormInputCheckbox(),
      'include_work_on_behalf_mc'                  => new sfWidgetFormInputCheckbox(),
      'include_work_on_behalf'                     => new sfWidgetFormInputCheckbox(),
      'include_purchase_on_behalf'                 => new sfWidgetFormInputCheckbox(),
      'include_penalty'                            => new sfWidgetFormInputCheckbox(),
      'include_utility'                            => new sfWidgetFormInputCheckbox(),
      'include_permit'                             => new sfWidgetFormInputCheckbox(),
      'include_debit_credit_note'                  => new sfWidgetFormInputCheckbox(),
      'debit_credit_note_with_breakdown'           => new sfWidgetFormInputCheckbox(),
      'footer_format'                              => new sfWidgetFormInputText(),
      'footer_bank_label'                          => new sfWidgetFormInputText(),
      'footer_bank_signature_label'                => new sfWidgetFormInputText(),
      'footer_cheque_number_label'                 => new sfWidgetFormInputText(),
      'footer_cheque_number_signature_label'       => new sfWidgetFormInputText(),
      'footer_cheque_date_label'                   => new sfWidgetFormInputText(),
      'footer_cheque_date_signature_label'         => new sfWidgetFormInputText(),
      'footer_cheque_amount_label'                 => new sfWidgetFormInputText(),
      'footer_cheque_amount_signature_label'       => new sfWidgetFormInputText(),
      'display_tax_column'                         => new sfWidgetFormInputCheckbox(),
      'contractor_submitted_date_label'            => new sfWidgetFormInputText(),
      'site_verified_date_label'                   => new sfWidgetFormInputText(),
      'certificate_received_date_label'            => new sfWidgetFormInputText(),
      'request_for_variation_category_id_to_print' => new sfWidgetFormInputText(),
      'display_tax_amount'                         => new sfWidgetFormInputCheckbox(),
      'created_at'                                 => new sfWidgetFormDateTime(),
      'updated_at'                                 => new sfWidgetFormDateTime(),
      'created_by'                                 => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Creator'), 'add_empty' => true)),
      'updated_by'                                 => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Updator'), 'add_empty' => true)),
    ));

    $this->setValidators(array(
      'id'                                         => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'post_contract_id'                           => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('PostContract'), 'column' => 'id')),
      'certificate_title'                          => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'certificate_print_format'                   => new sfValidatorPass(),
      'section_a_label'                            => new sfValidatorString(array('max_length' => 10, 'required' => false)),
      'section_b_label'                            => new sfValidatorString(array('max_length' => 10, 'required' => false)),
      'section_c_label'                            => new sfValidatorString(array('max_length' => 10, 'required' => false)),
      'section_d_label'                            => new sfValidatorString(array('max_length' => 10, 'required' => false)),
      'section_misc_label'                         => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'section_others_label'                       => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'section_payment_on_behalf_label'            => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'tax_label'                                  => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'tax_invoice_by_sub_contractor_label'        => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'tax_invoice_by_subsidiary_label'            => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'include_advance_payment'                    => new sfValidatorBoolean(array('required' => false)),
      'include_deposit'                            => new sfValidatorBoolean(array('required' => false)),
      'include_material_on_site'                   => new sfValidatorBoolean(array('required' => false)),
      'include_ksk'                                => new sfValidatorBoolean(array('required' => false)),
      'include_work_on_behalf_mc'                  => new sfValidatorBoolean(array('required' => false)),
      'include_work_on_behalf'                     => new sfValidatorBoolean(array('required' => false)),
      'include_purchase_on_behalf'                 => new sfValidatorBoolean(array('required' => false)),
      'include_penalty'                            => new sfValidatorBoolean(array('required' => false)),
      'include_utility'                            => new sfValidatorBoolean(array('required' => false)),
      'include_permit'                             => new sfValidatorBoolean(array('required' => false)),
      'include_debit_credit_note'                  => new sfValidatorBoolean(array('required' => false)),
      'debit_credit_note_with_breakdown'           => new sfValidatorBoolean(array('required' => false)),
      'footer_format'                              => new sfValidatorInteger(array('required' => false)),
      'footer_bank_label'                          => new sfValidatorString(array('max_length' => 50, 'required' => false)),
      'footer_bank_signature_label'                => new sfValidatorString(array('max_length' => 50, 'required' => false)),
      'footer_cheque_number_label'                 => new sfValidatorString(array('max_length' => 50, 'required' => false)),
      'footer_cheque_number_signature_label'       => new sfValidatorString(array('max_length' => 50, 'required' => false)),
      'footer_cheque_date_label'                   => new sfValidatorString(array('max_length' => 50, 'required' => false)),
      'footer_cheque_date_signature_label'         => new sfValidatorString(array('max_length' => 50, 'required' => false)),
      'footer_cheque_amount_label'                 => new sfValidatorString(array('max_length' => 50, 'required' => false)),
      'footer_cheque_amount_signature_label'       => new sfValidatorString(array('max_length' => 50, 'required' => false)),
      'display_tax_column'                         => new sfValidatorBoolean(array('required' => false)),
      'contractor_submitted_date_label'            => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'site_verified_date_label'                   => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'certificate_received_date_label'            => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'request_for_variation_category_id_to_print' => new sfValidatorInteger(array('required' => false)),
      'display_tax_amount'                         => new sfValidatorBoolean(array('required' => false)),
      'created_at'                                 => new sfValidatorDateTime(),
      'updated_at'                                 => new sfValidatorDateTime(),
      'created_by'                                 => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Creator'), 'column' => 'id', 'required' => false)),
      'updated_by'                                 => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Updator'), 'column' => 'id', 'required' => false)),
    ));

    $this->validatorSchema->setPostValidator(
      new sfValidatorDoctrineUnique(array('model' => 'ClaimCertificatePrintSetting', 'column' => array('post_contract_id')))
    );

    $this->widgetSchema->setNameFormat('claim_certificate_print_setting[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'ClaimCertificatePrintSetting';
  }

}
