<?php namespace PCK\VendorPreQualification;

use Illuminate\Database\Eloquent\Model;
use PCK\Statuses\FormStatus;
use PCK\Traits\FormTrait;
use PCK\Companies\Company;
use PCK\ModuleParameters\VendorManagement\VendorManagementGrade;
use Illuminate\Database\Eloquent\SoftDeletingTrait;
use PCK\VendorRegistration\VendorRegistration;

class VendorPreQualification extends Model implements FormStatus
{
    use SoftDeletingTrait, FormTrait;

    protected $table = 'vendor_pre_qualifications';

    protected $fillable = ['vendor_registration_id', 'vendor_work_category_id'];

    public function weightedNode()
    {
        return $this->belongsTo('PCK\WeightedNode\WeightedNode');
    }

    public function vendorRegistration()
    {
        return $this->belongsTo('PCK\VendorRegistration\VendorRegistration');
    }

    public function vendorWorkCategory()
    {
        return $this->belongsTo('PCK\VendorWorkCategory\VendorWorkCategory');
    }

    public function vendorManagementGrade()
    {
        return $this->belongsTo('PCK\ModuleParameters\VendorManagement\VendorManagementGrade', 'vendor_management_grade_id');
    }

    public function updateStatusIdBasedOnVendorRegistrationStatus()
    {
        switch($this->vendorRegistration->status)
        {
            case VendorRegistration::STATUS_DRAFT:
                $this->status_id = self::STATUS_DRAFT;
                break;
            case VendorRegistration::STATUS_SUBMITTED:
            case VendorRegistration::STATUS_PROCESSING:
                $this->status_id = self::STATUS_SUBMITTED;
                break;
            case VendorRegistration::STATUS_PENDING_VERIFICATION:
                $this->status_id = self::STATUS_PENDING_VERIFICATION;
                break;
            case VendorRegistration::STATUS_COMPLETED:
                $this->status_id = self::STATUS_COMPLETED;
                break;
            default:
                $this->status_id = self::STATUS_DRAFT;
        }

        $this->save();
    }

    public static function syncLatestForms($vendorRegistration)
    {
        $vendorRegistration->load('trackRecordProjects');

        $relevantVendorWorkCategories = $vendorRegistration->trackRecordProjects->lists('vendor_work_category_id');

        foreach($vendorRegistration->vendorPreQualifications as $vendorPreQualification)
        {
            // If no longer relevant, delete
            if(!in_array($vendorPreQualification->vendor_work_category_id, $relevantVendorWorkCategories))
            {
                $vendorPreQualification->delete();
                continue;
            }

            // If current form does not use latest form as template, delete.
            $latestTemplateForm = TemplateForm::getTemplateForm($vendorPreQualification->vendor_work_category_id);

            if( ( ! $latestTemplateForm ) || $vendorPreQualification->template_form_id != $latestTemplateForm->id ) $vendorPreQualification->delete();
        }

        foreach($relevantVendorWorkCategories as $vendorWorkCategory)
        {
            self::cloneFormIfNone($vendorRegistration->id, $vendorWorkCategory);
        }

        $vendorRegistration->load('vendorPreQualifications');

        foreach($vendorRegistration->vendorPreQualifications as $vendorPreQualification)
        {
            $vendorPreQualification->updateStatusIdBasedOnVendorRegistrationStatus();
        }
    }

    public static function cloneFormIfNone($vendorRegistrationId, $vendorWorkCategoryId)
    {
        $vendorPreQualification = VendorPreQualification::firstOrNew(array(
            'vendor_registration_id' => $vendorRegistrationId,
            'vendor_work_category_id' => $vendorWorkCategoryId,
        ));

        if( is_null($vendorPreQualification->weightedNode) )
        {
            $templateForm = TemplateForm::getTemplateForm($vendorWorkCategoryId);

            if($templateForm && $templateForm->weightedNode)
            {
                $clonedForm = $templateForm->weightedNode->clone();

                $vendorPreQualification->weightedNode()->associate($clonedForm);

                $vendorPreQualification->template_form_id = $templateForm->id;

                $vendorPreQualification->save();
            }
        }
    }

    public static function flushRecords(VendorRegistration $vendorRegistration, $hardDelete = false)
    {
        foreach($vendorRegistration->vendorPreQualifications as $record)
        {
            if($hardDelete)
            {
                $record->forceDelete();
            }
            else
            {
                $record->delete();
            }
        }

        $vendorRegistration->load('vendorPreQualifications');
    }
}