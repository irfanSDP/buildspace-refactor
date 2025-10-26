<?php namespace PCK\TenderFormVerifierLogs;

use PCK\Base\TimestampFormatterTrait;
use PCK\Tenders\Tender;
use Illuminate\Database\Eloquent\Model;
use Laracasts\Presenter\PresentableTrait;

class TenderFormVerifierLog extends Model implements FormLevelStatus {

    use TimestampFormatterTrait, PresentableTrait;

    protected $table = 'tender_form_verifier_logs';

    protected $with = array( 'user' );

    protected $presenter = 'PCK\TenderFormVerifierLogs\TenderFormVerifierLogPresenter';

    public function loggable()
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->belongsTo('PCK\Users\User');
    }

    public function getTenderAttribute()
    {
        if($this->loggable instanceof Tender) return $this->loggable;
        
        return $this->loggable->tender;
    }

    public static function getTextForVerificationStatus($type)
    {
        $text = null;

        switch($type)
        {
            case self::USER_VERIFICATION_REJECTED:
                $text = self::USER_VERIFICATION_REJECTED_TEXT;
                break;

            case self::USER_VERIFICATION_CONFIRMED:
                $text = self::USER_VERIFICATION_CONFIRMED_TEXT;
                break;

            case self::NEED_VALIDATION:
                $text = self::USER_VERIFICATION_IN_PROGRESS_TEXT;
                break;

            case self::EXTEND_DATE_VALIDATION_IN_PROGRESS:
                $text = self::EXTEND_DATE_VALIDATION_IN_PROGRESS_TEXT;
                break;

            case self::REASSIGNED:
                $text = self::REASSIGNED_TEXT;
                break;
        }

        return $text;
    }

}