<?php namespace PCK\VendorRegistration;

use Illuminate\Database\Eloquent\Model;
use PCK\VendorRegistration\VendorRegistration;
use PCK\Users\User;

class SubmissionLog extends Model
{
    protected $table = 'vendor_registration_submission_logs';

    const SUBMITTED              = 1;
    const PROCESSING             = 2;
    const REJECTED               = 4;
    const APPROVED               = 8;
    const SUBMITTED_FOR_APPROVAL = 16;

    public function vendorRegistration()
    {
        return $this->belongsTo(VendorRegistration::class, 'vendor_registration_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function getActionDescription()
    {
        $actions = [
            self::SUBMITTED              => trans('vendorManagement.submitted'),
            self::PROCESSING             => trans('vendorManagement.startedProcessing'),
            self::REJECTED               => trans('vendorManagement.rejected'),
            self::APPROVED               => trans('vendorManagement.approved'),
            self::SUBMITTED_FOR_APPROVAL => trans('vendorManagement.submittedForApproval'),
        ];

        return $actions[$this->action_type];
    }

    public static function logAction(VendorRegistration $vendorRegistration, $actionType)
    {
        $log                         = new self();
        $log->vendor_registration_id = $vendorRegistration->id;
        $log->action_type            = $actionType;
        $log->created_by             = \Confide::user()->id;
        $log->updated_by             = \Confide::user()->id;
        $log->save();

        return self::find($log->id);
    }
}