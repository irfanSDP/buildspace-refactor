<?php namespace PCK\Buildspace;

use Illuminate\Database\Eloquent\Model;

class ClaimCertificateInformation extends Model {

    protected $connection = 'buildspace';

    protected $table = 'bs_claim_certificate_information';

    public function claimCertificate()
    {
        return $this->belongsTo('PCK\Buildspace\ClaimCertificate');
    }
}