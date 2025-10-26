<?php namespace PCK\Buildspace;

use Illuminate\Database\Eloquent\Model;

class ClaimCertificateApprovalLog extends Model {

    protected $connection = 'buildspace';

    protected $table = 'bs_claim_certificate_approval_logs';

    protected static function boot()
    {
        parent::boot();

        static::creating(function(self $log)
        {
            $log->created_by = \Confide::user()->getBsUser()->id;
        });
        static::saving(function(self $log)
        {
            $log->updated_by = \Confide::user()->getBsUser()->id;
        });
    }
}