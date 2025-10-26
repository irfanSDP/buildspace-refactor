<?php
namespace PCK\ConsultantManagement;

use Carbon\Carbon;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use PCK\ConsultantManagement\ConsultantManagementRfpInterview;
use PCK\Companies\Company;

class ConsultantManagementRfpInterviewConsultant extends Model
{
    protected $table = 'consultant_management_rfp_interview_consultants';

    protected $fillable = ['consultant_management_rfp_interview_id', 'company_id', 'status', 'remarks', 'interview_timestamp', 'consultant_remarks'];

    const STATUS_UNSET = 1;
    const STATUS_ACCEPTED = 2;
    const STATUS_DECLINED = 4;

    const STATUS_UNSET_TEXT = 'No Reply';
    const STATUS_ACCEPTED_TEXT = 'Accepted';
    const STATUS_DECLINED_TEXT = 'Declined';

    protected static function boot()
    {
        parent::boot();

        self::creating(function(self $model)
        {
            $model->status = ConsultantManagementRfpInterviewConsultant::STATUS_UNSET;
        });
    }

    public function consultantManagementRfpInterview()
    {
        return $this->belongsTo(ConsultantManagementRfpInterview::class, 'consultant_management_rfp_interview_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function getStatusText()
    {
        return self::getInterviewStatusText($this->status);
    }

    public static function getInterviewStatusText($status)
    {
        switch($status)
        {
            case self::STATUS_UNSET:
                return self::STATUS_UNSET_TEXT;
            case self::STATUS_ACCEPTED:
                return self::STATUS_ACCEPTED_TEXT;
            case self::STATUS_DECLINED:
                return self::STATUS_DECLINED_TEXT;
            default:
                throw new \Exception('Invalid status');
        }
    }

    public function getInterviewTimestampAttribute($value)
    {
        return Carbon::parse($value)->format(\Config::get('dates.created_and_updated_at_formatting'));
    }
}