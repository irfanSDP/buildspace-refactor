<?php
namespace PCK\ConsultantManagement;

use Carbon\Carbon;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use PCK\ConsultantManagement\ConsultantManagementVendorCategoryRfp;
use PCK\ConsultantManagement\ConsultantManagementRfpInterviewConsultant;
use PCK\Users\User;

class ConsultantManagementRfpInterview extends Model
{
    protected $table = 'consultant_management_rfp_interviews';

    protected $fillable = ['vendor_category_rfp_id', 'title', 'details', 'interview_date', 'status'];

    const STATUS_DRAFT = 1;
    const STATUS_SENT = 2;

    const STATUS_DRAFT_TEXT = 'Draft';
    const STATUS_SENT_TEXT = 'Sent';

    protected static function boot()
    {
        parent::boot();

        self::creating(function(self $model)
        {
            $model->status = ConsultantManagementRfpInterview::STATUS_DRAFT;
        });

        self::deleting(function(self $model)
        {
            $model->consultants()->delete();
        });
    }

    public function consultantManagementVendorCategoryRfp()
    {
        return $this->belongsTo(ConsultantManagementVendorCategoryRfp::class, 'vendor_category_rfp_id');
    }

    public function consultants()
    {
        return $this->hasMany(ConsultantManagementRfpInterviewConsultant::class, 'consultant_management_rfp_interview_id')->orderBy('interview_timestamp', 'asc');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function getStatusText()
    {
        switch($this->status)
        {
            case self::STATUS_DRAFT:
                return self::STATUS_DRAFT_TEXT;
            case self::STATUS_SENT:
                return self::STATUS_SENT_TEXT;
            default:
                throw new \Exception('Invalid status');
        }
    }

    public function getInterviewDateAttribute($value)
    {
        return Carbon::parse($value)->format(\Config::get('dates.created_and_updated_at_formatting'));
    }

    public function deletable()
    {
        return $this->status == self::STATUS_DRAFT;
    }
}