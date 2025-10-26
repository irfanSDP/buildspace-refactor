<?php namespace PCK\Forms;

use Laracasts\Validation\FormValidator;

class ClaimCertificateInvoiceInformationForm extends FormValidator {

    protected $maxPayableAmount = null;

    protected $rules = [
        'invoiceNumber'    => 'required',
        'postMonth'        => 'required',
        'invoiceDate'      => 'required|date',
    ];
}