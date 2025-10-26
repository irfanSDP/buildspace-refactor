<?php

/**
 * ClaimCertificateInvoice form.
 *
 * @package    buildspace
 * @subpackage form
 * @author     1337 developers
 * @version    SVN: $Id$
 */
class ClaimCertificateInvoiceForm extends BaseClaimCertificateInvoiceForm
{
    public function configure()
    {
        parent::configure();

        unset($this['claim_certificate_id']);
        unset($this['invoice_date']);
        unset($this['invoice_number']);
        unset($this['post_month']);
        unset($this['created_at']);
        unset($this['updated_at']);
        unset($this['created_by']);
        unset($this['updated_by']);
    }

    public function bind(array $taintedValues = null, array $taintedFiles = null)
    {
        $this->setWidget('invoiceDate', new sfWidgetFormInputText());
        $this->setWidget('invoiceNumber', new sfWidgetFormInputText());
        $this->setWidget('postMonth', new sfWidgetFormInputText());
        $this->setWidget('_csrf_token', new sfWidgetFormInputText());

        $this->setValidators(array(
            'claimCertificateId' => new sfValidatorString(
                array(
                    'required' => true,
                ),
                array(
                    'required' => 'Claim Certificate ID is required.'
                )
            ),
            'invoiceDate' => new sfValidatorString(
                array(
                    'required' => true,
                ),
                array(
                    'required' => 'Invoice Date is required.'
                )
            ),
            'invoiceNumber' => new sfValidatorString(
                array(
                    'required' => true,
                ),
                array(
                    'required' => 'Invoice Number is required.'
                )
            ),
            'postMonth' => new sfValidatorString(
                array(
                    'required' => true,
                ),
                array(
                    'required' => 'Post Month is required.'
                )
            ),
            '_csrf_token' => new SfValidatorString(
                array(
                    'required' => true,
                )
            )
        ));

        parent::bind($taintedValues, $taintedFiles);
    }

    public function doSave($conn = null)
    {
        $values           = $this->getValues();
        $claimCertificate = Doctrine_Core::getTable('ClaimCertificate')->find($values['claimCertificateId']);
        $userId           = $this->getOption('userId');

        $invoice                       = $claimCertificate->Invoice->exists() ? $claimCertificate->Invoice : new ClaimCertificateInvoice();
        $invoice->claim_certificate_id = $claimCertificate->id;
        $invoice->invoice_date         = $values['invoiceDate'];
        $invoice->invoice_number       = $values['invoiceNumber'];
        $invoice->post_month           = $values['postMonth'];
        $invoice->created_by           = $userId;
        $invoice->updated_by           = $userId;
        $invoice->save();
    }
}
