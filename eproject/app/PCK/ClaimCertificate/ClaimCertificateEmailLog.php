<?php namespace PCK\ClaimCertificate;

use Illuminate\Database\Eloquent\Model;
use PCK\Users\User;

class ClaimCertificateEmailLog extends Model {

    protected $fillable = [ 'claim_certificate_id', 'user_id' ];

    public function user()
    {
        return $this->belongsTo('PCK\Users\User');
    }

    public static function addEntry($claimCertificateId, User $user)
    {
        $record = new self(array( 'claim_certificate_id' => $claimCertificateId, 'user_id' => $user->id ));

        return $record->save();
    }

    public static function getLog($claimCertificateId)
    {
        return self::where('claim_certificate_id', '=', $claimCertificateId)->orderBy('created_at', 'desc')->get();
    }

}