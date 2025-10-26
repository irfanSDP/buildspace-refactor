<?php namespace PCK\TenderInterviews;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class TenderInterviewLog extends Model {

    public function user()
    {
        return $this->belongsTo('PCK\Users\User');
    }

    public function interview()
    {
        return $this->belongsTo('PCK\TenderInterviews\TenderInterview');
    }

    public function getTime($format = null)
    {
        if(is_null($format))
        {
            $format = \Config::get('dates.timestamp');
        }

        return Carbon::parse($this->created_at)->format($format);
    }
}