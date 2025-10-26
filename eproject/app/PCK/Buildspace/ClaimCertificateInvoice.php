<?php namespace PCK\Buildspace;

use Illuminate\Database\Eloquent\Model;
use PCK\Users\User;

class ClaimCertificateInvoice extends Model
{
    protected $connection = 'buildspace';
    protected $table = 'bs_claim_certificate_invoices';

    public function claimCertificate()
    {
        $this->belongsTo('PCK\Buildspace\ClaimCertificate');
    }
}

