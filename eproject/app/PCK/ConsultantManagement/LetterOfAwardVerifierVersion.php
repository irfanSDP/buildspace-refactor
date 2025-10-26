<?php
namespace PCK\ConsultantManagement;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

use PCK\ConsultantManagement\LetterOfAwardVerifier;

class LetterOfAwardVerifierVersion extends Model
{
    protected $table = 'consultant_management_letter_of_award_verifier_versions';

    protected $fillable = ['consultant_management_letter_of_award_verifier_id', 'user_id', 'version', 'status', 'remarks'];

    const STATUS_PENDING = 1;
    const STATUS_APPROVED = 2;
    const STATUS_REJECTED = 4;

    const STATUS_PENDING_TEXT = 'Pending';
    const STATUS_APPROVED_TEXT = 'Approved';
    const STATUS_REJECTED_TEXT = 'Rejected';

    public function letterOfAwardVerifier()
    {
        return $this->belongsTo(LetterOfAwardVerifier::class, 'consultant_management_letter_of_award_verifier_id');
    }

    public function getStatusText()
    {
        switch($this->status)
        {
            case self::STATUS_PENDING:
                return self::STATUS_PENDING_TEXT;
            case self::STATUS_APPROVED:
                return self::STATUS_APPROVED_TEXT;
            case self::STATUS_REJECTED:
                return self::STATUS_REJECTED_TEXT;
            default:
                throw new \Exception('Invalid status');
        }
    }
}