<?php namespace PCK\TenderInterviews;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class TenderInterview extends Model {

    const STATUS_YES      = 1;
    const STATUS_NO      = 2;
    const STATUS_DEFAULT = self::STATUS_NO;
    const DEFAULT_INTERVIEW_LENGTH_IN_MINUTES = 45;

    protected $fillable = [
        'company_id',
        'tender_interview_information_id',
        'tender_id',
        'venue',
        'date_and_time',
        'status',
    ];

    public static function getText($key, $locale = null)
    {
        $text = null;

        switch($key)
        {
            case self::STATUS_YES:
                $text = is_null($locale) ? trans('tenders.attendanceConfirmed') : trans('tenders.attendanceConfirmed', [], 'messages', $locale);
                break;
            case self::STATUS_NO:
                $text = is_null($locale) ? trans('tenders.attendanceNotConfirmed') : trans('tenders.attendanceNotConfirmed', [], 'messages', $locale);
                break;
        }

        return $text;
    }

    public function tender()
    {
        return $this->belongsTo('PCK\Tenders\Tender');
    }

    public function company()
    {
        return $this->belongsTo('PCK\Companies\Company');
    }

    public function info()
    {
        return $this->belongsTo('PCK\TenderInterviews\TenderInterviewInformation', 'tender_interview_information_id');
    }

    public function getTime($format = 'dates.time_only')
    {
        return Carbon::parse($this->date_and_time)->format(\Config::get($format));
    }

    public function getDate($format = 'dates.standard_spaced')
    {
        return Carbon::parse($this->date_and_time)->format(\Config::get($format));
    }

    public static function getStatusDropDownListing($locale = null)
    {
        return array(
            self::STATUS_YES => self::getText(self::STATUS_YES, $locale),
            self::STATUS_NO  => self::getText(self::STATUS_NO, $locale),
        );
    }

}