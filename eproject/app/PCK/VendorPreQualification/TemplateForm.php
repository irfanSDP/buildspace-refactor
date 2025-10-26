<?php namespace PCK\VendorPreQualification;

use Illuminate\Database\Eloquent\Model;
use PCK\Statuses\FormStatus;
use PCK\Traits\FormTrait;
use PCK\Verifier\Verifiable;
use PCK\ModuleParameters\VendorManagement\VendorManagementGrade;

class TemplateForm extends Model implements FormStatus, Verifiable {

    use FormTrait;

    protected $table = 'vendor_pre_qualification_template_forms';

    protected $fillable = ['vendor_work_category_id', 'weighted_node_id', 'revision'];

    public $disableNotifications = true;

    public function vendorWorkCategory()
    {
        return $this->belongsTo('PCK\VendorWorkCategory\VendorWorkCategory');
    }

    public static function getCurrentEditingForm($vendorWorkCategoryId)
    {
        return self::where('vendor_work_category_id', '=', $vendorWorkCategoryId)
            ->orderBy('revision', 'desc')
            ->first();
    }

    public static function getTemplateForm($vendorWorkCategoryId)
    {
        return self::where('vendor_work_category_id', '=', $vendorWorkCategoryId)
            ->where('status_id', '=', self::STATUS_COMPLETED)
            ->orderBy('revision', 'desc')
            ->first();
    }

    public function weightedNode()
    {
        return $this->belongsTo('PCK\WeightedNode\WeightedNode');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function($model){
            $model->status_id = self::STATUS_DRAFT;
        });
    }

    public function isDraft()
    {
        return $this->status_id == self::STATUS_DRAFT;
    }

    public function isPendingVerification()
    {
        return $this->status_id == self::STATUS_PENDING_VERIFICATION;
    }

    public function isCompleted()
    {
        return $this->status_id == self::STATUS_COMPLETED;
    }

    public function onReview()
    {
        if(\PCK\Verifier\Verifier::isApproved($this))
        {
            $this->status_id = self::STATUS_COMPLETED;
            $this->save();
        }
        if(\PCK\Verifier\Verifier::isRejected($this))
        {
            $this->status_id = self::STATUS_DRAFT;
            $this->save();
        }
    }

    public function getOnApprovedView(){}
    public function getOnRejectedView(){}
    public function getOnPendingView(){}
    public function getRoute(){}
    public function getViewData($locale){}
    public function getOnApprovedNotifyList(){}
    public function getOnRejectedNotifyList(){}
    public function getOnApprovedFunction(){}
    public function getOnRejectedFunction(){}
    public function getEmailSubject($locale){}
    public function getSubmitterId(){}
    public function getModuleName(){}
}