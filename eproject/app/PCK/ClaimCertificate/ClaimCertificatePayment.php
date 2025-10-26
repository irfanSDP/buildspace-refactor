<?php namespace PCK\ClaimCertificate;

use Illuminate\Database\Eloquent\Model;

class ClaimCertificatePayment extends Model {

    protected $fillable = [ 'bank', 'reference', 'amount', 'date' ];

    public function claimCertificate()
    {
        return $this->belongsTo('PCK\Buildspace\ClaimCertificate');
    }

    public function createdBy()
    {
        return $this->belongsTo('PCK\Users\User', 'created_by', 'id');
    }

}