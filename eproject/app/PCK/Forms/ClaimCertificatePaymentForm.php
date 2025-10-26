<?php namespace PCK\Forms;

use Laracasts\Validation\FormValidator;

class ClaimCertificatePaymentForm extends FormValidator {

    protected $maxPayableAmount = null;

    protected $rules = [
        'bank'      => 'required|min:3|max:200',
        'reference' => 'required|min:3|max:200|unique:claim_certificate_payments',
        'amount'    => 'required|numeric|min:0',
        'date'      => 'required|date',
    ];

    public function setMaxPayableAmount($amount)
    {
        $this->maxPayableAmount = $amount;
    }

    public function getValidationRules()
    {
        $this->rules['amount'] .= "|max:{$this->maxPayableAmount}";

        return parent::getValidationRules();
    }

    public function ignoreUnique($id)
    {
        $this->rules['reference'] .= ",id,{$id}";
    }

}