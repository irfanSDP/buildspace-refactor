<?php namespace PCK\VendorRegistration;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use PCK\Statuses\FormStatus;
use PCK\Traits\FormTrait;
use PCK\Users\User;
use PCK\VendorPreQualification\VendorPreQualification;
use PCK\TrackRecordProject\TrackRecordProject;
use PCK\Verifier\Verifier;
use PCK\Verifier\Verifiable;
use PCK\Vendor\Vendor;
use PCK\FormBuilder\FormObjectMapping;
use PCK\FormBuilder\DynamicForm;
use PCK\VendorRegistration\Payment\VendorRegistrationPayment;
use PCK\ObjectField\ObjectField;
use PCK\VendorPreQualification\TemplateForm;
use PCK\VendorPreQualification\VendorGroupGrade;
use PCK\VendorRegistration\FormTemplateMapping\VendorRegistrationFormTemplateMapping;
use Illuminate\Database\Eloquent\SoftDeletingTrait;
use PCK\VendorRegistration\CompanyTemporaryDetail;
use PCK\VendorRegistration\VendorCategoryTemporaryRecord;
use PCK\Companies\Company;
use PCK\VendorRegistration\VendorRegistrationProcessor;
use PCK\Users\UserCompanyLog;

class VendorRegistration extends Model implements FormStatus, Verifiable
{
    use FormTrait, SoftDeletingTrait;

    const SUBMISSION_TYPE_NEW     = 1;
    const SUBMISSION_TYPE_RENEWAL = 2;
    const SUBMISSION_TYPE_UPDATE  = 4;

    protected $table = 'vendor_registrations';

    public $disableNotifications = true;

    protected $fillable = ['company_id', 'status', 'revision', 'submission_type'];

    protected static function boot()
    {
        parent::boot();

        static::creating(function(self $model)
        {
            if( is_null($model->status) ) $model->status = self::STATUS_DRAFT;
        });

        static::created(function(self $model)
        {
            Section::initiate($model);
        });
    }

    public function company()
    {
        return $this->belongsTo('PCK\Companies\Company', 'company_id');
    }

    public function processor()
    {
        return $this->hasOne('PCK\VendorRegistration\VendorRegistrationProcessor', 'vendor_registration_id');
    }

    public function companyPersonnel()
    {
        return $this->hasMany('PCK\CompanyPersonnel\CompanyPersonnel');
    }

    public function supplierCreditFacilities()
    {
        return $this->hasMany('PCK\SupplierCreditFacility\SupplierCreditFacility');
    }

    public function vendorPreQualifications()
    {
        return $this->hasMany('PCK\VendorPreQualification\VendorPreQualification', 'vendor_registration_id');
    }

    public function trackRecordProjects()
    {
        return $this->hasMany('PCK\TrackRecordProject\TrackRecordProject', 'vendor_registration_id');
    }

    public function vendorCategoryTemporaryRecords()
    {
        return $this->hasMany('PCK\VendorRegistration\VendorCategoryTemporaryRecord', 'vendor_registration_id');
    }

    public function submissionLogs()
    {
        return $this->hasMany(SubmissionLog::class, 'vendor_registration_id');
    }

    public function getSection($section)
    {
        return Section::where('section', '=', $section)
            ->where('vendor_registration_id', '=', $this->id)
            ->first();
    }

    public function getStatusTextAttribute()
    {
        return self::getStatusText($this->status);
    }

    public function isFirstRevision()
    {
        return $this->revision === 0;
    }

    public function isSubmitted()
    {
        return $this->status == self::STATUS_SUBMITTED;
    }

    public function isCompleted()
    {
        return $this->status == self::STATUS_COMPLETED;
    }

    public function isDraft()
    {
        return $this->status == self::STATUS_DRAFT;
    }

    public function isProcessing()
    {
        return $this->status == self::STATUS_PROCESSING;
    }

    public function isPendingVerification()
    {
        return $this->status == self::STATUS_PENDING_VERIFICATION;
    }

    public function getPreviousVendorRegistration()
    {
        if($this->isFirst()) return null;

        return self::where('company_id', $this->company->id)->where('revision', ($this->revision - 1))->first();
    }

    public function getCompanyPersonnelDownloads()
    {
        $data = [];

        return array(
            array(
                'label' => trans('vendorManagement.directors'),
                'values' => [trans('vendorManagement.directors')],
                'route_attachments' => route('vendors.vendorRegistration.companyPersonnel.downloads.directors'),
                'attachments_count' => ObjectField::findOrCreateNew($this, ObjectField::COMPANY__COMPANY_PERSONNEL_DIRECTOR)->attachments->count(),
            ),
            array(
                'label' => trans('vendorManagement.shareholders'),
                'values' => [trans('vendorManagement.shareholders')],
                'route_attachments' => route('vendors.vendorRegistration.companyPersonnel.downloads.shareholders'),
                'attachments_count' => ObjectField::findOrCreateNew($this, ObjectField::COMPANY__COMPANY_PERSONNEL_SHAREHOLDER)->attachments->count(),
            ),
            array(
                'label' => trans('vendorManagement.headOfCompany'),
                'values' => [trans('vendorManagement.headOfCompany')],
                'route_attachments' => route('vendors.vendorRegistration.companyPersonnel.downloads.companyHeads'),
                'attachments_count' => ObjectField::findOrCreateNew($this, ObjectField::COMPANY__COMPANY_PERSONNEL_COMPANY_HEAD)->attachments->count(),
            ),
        );
    }

    public function getPreQualificationDownloads()
    {
        $preQualifications = VendorPreQualification::where('vendor_registration_id', '=', $this->id)->get();

        $data = [];

        foreach($preQualifications as $preQualification)
        {
            if(is_null($preQualification->weightedNode)) continue;

            $nodes = $preQualification->weightedNode->getDescendantsAndSelf();

            foreach($nodes as $node)
            {
                if(!$node->attachments->isEmpty())
                {
                    $data[] = array(
                        'label' => $node->getRoot()->name,
                        'values' => [$node->name],
                        'route_attachments' => route('preQualification.node.downloads', array($node->id)),
                        'attachments_count' => $node->attachments->count(),
                    );
                }
            }
        }

        return $data;
    }

    public function rejectApplication()
    {
        // Check for new forms. Delete current forms and copy new forms if any.
        $formObjectMappping = FormObjectMapping::findRecord($this->company->vendorRegistration, DynamicForm::VENDOR_REGISTRATION_IDENTIFIER);

        $form         = $formObjectMappping->dynamicForm;
        $form->status = DynamicForm::STATUS_DESIGN_APPROVED;
        $form->save();

        // Set all forms to draft.

        VendorPreQualification::where('vendor_registration_id', '=', $this->id)
            ->where('status_id', '=', VendorPreQualification::STATUS_REJECTED)
            ->update(array('status_id' => VendorPreQualification::STATUS_DRAFT));

        foreach(Section::getSections() as $section)
        {
            $section = $this->getSection($section);
            $section->status_id = Section::STATUS_DRAFT;
            $section->save();
        }

        $vendorRegistrationPayment = VendorRegistrationPayment::getCurrentlySelectedPaymentMethodRecord($this->company);

        if($vendorRegistrationPayment)
        {
            $vendorRegistrationPayment->status = VendorRegistrationPayment::STATUS_DRAFT;
            $vendorRegistrationPayment->submitted = false;
            $vendorRegistrationPayment->submitted_date = null;
            $vendorRegistrationPayment->paid = false;
            $vendorRegistrationPayment->paid_date = null;
            $vendorRegistrationPayment->successful = false;
            $vendorRegistrationPayment->successful_date = null;
            $vendorRegistrationPayment->save();
        }

        $this->status = self::STATUS_DRAFT;
        $this->save();
    }

    public function onReview()
    {
        if(Verifier::isBeingVerified($this))
        {
            $this->setSectionsToPending();
        }

        if(Verifier::isApproved($this))
        {
            $this->setSectionsToApproved();

            $this->applyDraftChanges();

            if($this->isSubmissionTypeRenewal() || $this->isSubmissionTypeNew())
            {
                $this->processResults();

                if($this->company->getStatus() == Company::STATUS_DEACTIVATED)
                {
                    $this->company->deactivation_date = null;
                    $this->company->deactivated_at    = null;
                    $this->company->save();
                }
            }
            elseif($this->isSubmissionTypeUpdate())
            {
                $this->processResults(false);
            }

            $this->company->updateVendorStatus();
        }

        if(Verifier::isRejected($this))
        {
            $this->status = self::STATUS_PROCESSING;
            $this->save();
        }
    }

    public function applyDraftChanges()
    {
        if($temporaryDetails = CompanyTemporaryDetail::findRecord($this))
        {
            $temporaryDetails->applyChanges();
        }

        VendorCategoryTemporaryRecord::applyChanges($this);
    }

    public function setSectionsToPending()
    {
        // Set all forms to pending verification.

        // vendor prequalification
        VendorPreQualification::where('vendor_registration_id', '=', $this->id)->update(array('status_id' => VendorPreQualification::STATUS_PENDING_VERIFICATION));

        foreach(Section::getSections() as $section)
        {
            $section = $this->getSection($section);
            $section->status_id = Section::STATUS_PENDING_VERIFICATION;
            $section->amendment_status = Section::AMENDMENT_STATUS_NOT_REQUIRED;
            $section->amendment_remarks = "";
            $section->save();
        }

        $vendorRegistrationPayment = VendorRegistrationPayment::getCurrentlySelectedPaymentMethodRecord($this->company);
        if($vendorRegistrationPayment)
        {
            $vendorRegistrationPayment->status = VendorRegistrationPayment::STATUS_PENDING_VERIFICATION;
            $vendorRegistrationPayment->save();
        }
    }

    public function setSectionsToApproved()
    {
        // Set all forms to approved/completed.
        $formObjectMappping = FormObjectMapping::findRecord($this->company->vendorRegistration, DynamicForm::VENDOR_REGISTRATION_IDENTIFIER);

        $form         = $formObjectMappping->dynamicForm;
        $form->status = DynamicForm::STATUS_VENDOR_SUBMISSION_APPROVED;
        $form->save();

        VendorPreQualification::where('vendor_registration_id', '=', $this->id)->update(array('status_id' => VendorPreQualification::STATUS_COMPLETED));

        foreach(Section::getSections() as $section)
        {
            $section                   = $this->getSection($section);
            $section->status_id        = Section::STATUS_COMPLETED;
            $section->amendment_status = Section::AMENDMENT_STATUS_NOT_REQUIRED;
            $section->save();
        }

        $vendorRegistrationPayment = VendorRegistrationPayment::getCurrentlySelectedPaymentMethodRecord($this->company);

        if($vendorRegistrationPayment)
        {
            $vendorRegistrationPayment->status = VendorRegistrationPayment::STATUS_COMPLETED;
            $vendorRegistrationPayment->save();
        }

        $this->status = self::STATUS_COMPLETED;
        $this->save();
    }

    public function processResults($updateValidityPeriod = true)
    {
        $this->createVendorRecords();

        if(!getenv('VENDOR_MANAGEMENT_DISABLE_SECTION_PRE_QUALIFICATION')) $this->updateVendorRecordsQualificationStatus();

        if($updateValidityPeriod)
        {
            $this->company->permanentize();

            $this->company->setExpiryDate();

            $this->company->generateVendorProfile();
        }
    }

    protected function updateVendorRecordsQualificationStatus()
    {
        // Calculate preQ scores.
        $vendorPreQualifications = VendorPreQualification::where('vendor_registration_id', '=', $this->id)->get();

        $grading = VendorGroupGrade::getGradeByGroup($this->company->contract_group_category_id);

        foreach($vendorPreQualifications as $vendorPreQualification)
        {
            if( is_null($vendorPreQualification->score) || ( ! ( $grading && $vendorPreQualification->score > $grading->levels()->min('score_upper_limit') ) ) )
            {
                Vendor::where('company_id', '=', $this->company_id)
                    ->where('vendor_work_category_id', '=', $vendorPreQualification->vendor_work_category_id)
                    ->update(['is_qualified' => false]);
            }
        }
    }

    protected function createVendorRecords()
    {
        $projectTrackRecordVendorWorkCategories = TrackRecordProject::where('vendor_registration_id', '=', $this->id)->lists('vendor_work_category_id');

        foreach($projectTrackRecordVendorWorkCategories as $vendorWorkCategoryId)
        {
            $vendor = Vendor::firstOrCreate(array(
               'vendor_work_category_id' => $vendorWorkCategoryId,
               'company_id'              => $this->company_id,
            ));

            if (! $vendor->is_qualified) {
                $vendor->is_qualified = true;
                $vendor->save();
            }
        }
    }

    public function createNewRevision($isRenewal = true)
    {
        $newRevision = self::create(array(
            'company_id'      => $this->company_id,
            'revision'        => $this->revision+1,
            'submission_type' => $isRenewal ? self::SUBMISSION_TYPE_RENEWAL : self::SUBMISSION_TYPE_UPDATE,
        ));

        $this->cloneCompanyDetails($newRevision);
        $this->cloneVendorRegistrationForm($newRevision, $isRenewal);
        $this->clonePreQualification($newRevision, $isRenewal);
        $this->cloneCompanyPersonnel($newRevision);
        $this->cloneSupplierCreditFacilities($newRevision);
        $this->cloneProjectTrackRecord($newRevision);

        return $newRevision;
    }

    public function cloneCompanyDetails($newVendorRegistration)
    {
        CompanyTemporaryDetail::init($newVendorRegistration);
        VendorCategoryTemporaryRecord::init($newVendorRegistration);
    }

    public function cloneVendorRegistrationForm($newVendorRegistration, $isRenewal)
    {
        if(!$isRenewal)
        {
            $user = \Confide::user();

            $formObjectMapping = FormObjectMapping::findRecord($user->company->finalVendorRegistration, DynamicForm::VENDOR_REGISTRATION_IDENTIFIER);

            if($formObjectMapping)
            {
                $newForm = $formObjectMapping->dynamicForm->clone($formObjectMapping->dynamicForm->name, $formObjectMapping->dynamicForm->module_identifier, true);
            }
            else
            {
                // migrated users have no first registration form
                $mappedTemplateForm = VendorRegistrationFormTemplateMapping::findRecord($newVendorRegistration->company->contractGroupCategory, $newVendorRegistration->company->businessEntityType)->dynamicForm;

                $newForm = $mappedTemplateForm->clone($mappedTemplateForm->name, $mappedTemplateForm->module_identifier, true);
            }

            $newForm->is_renewal_form = true;
            $newForm->save();

            $newform = DynamicForm::find($newForm->id);

            $formObjectMapping = FormObjectMapping::bindFormToObject($newForm, $newVendorRegistration);
        }
        else
        {
            $mapping = VendorRegistrationFormTemplateMapping::findRecord($newVendorRegistration->company->contractGroupCategory, $newVendorRegistration->company->businessEntityType);

            if($mapping && $mappedTemplateForm = $mapping->dynamicForm)
            {
                $previousVendorRegistration = $newVendorRegistration->getPreviousVendorRegistration();

                if($previousVendorRegistration && $previousFormObjectMapping = FormObjectMapping::findRecord($previousVendorRegistration, DynamicForm::VENDOR_REGISTRATION_IDENTIFIER))
                {
                    $previousVendorRegistrationForm = $previousFormObjectMapping->dynamicForm;
    
                    // check if previous registration form's template form is the latest revision
                    $originalVendorRegistrationTemplateForm = $previousVendorRegistrationForm->getOriginalTemplateForm();

                    if($originalVendorRegistrationTemplateForm->id == $mappedTemplateForm->id)
                    {
                        // newly cloned form is based off previous registration form, all input values carried forward
                        $newForm = $previousVendorRegistrationForm->clone($previousVendorRegistrationForm->name, $previousVendorRegistrationForm->module_identifier, true);

                        $newForm->is_renewal_form = true;
                        $newForm->save();

                        $newform = DynamicForm::find($newForm->id);

                        $formObjectMapping = FormObjectMapping::bindFormToObject($newForm, $newVendorRegistration);
                    }
                    else
                    {
                        // newly cloned form is based off mapped form template
                        $newForm = $mappedTemplateForm->clone($mappedTemplateForm->name, $mappedTemplateForm->module_identifier, true);

                        $newForm->is_renewal_form = true;
                        $newForm->save();

                        $newform = DynamicForm::find($newForm->id);

                        $formObjectMapping = FormObjectMapping::bindFormToObject($newForm, $newVendorRegistration);
                    }
                }
                else // migrated vendors have no previous registration form (details update was never performed)
                {
                    // newly cloned form is based off mapped form template
                    $newForm = $mappedTemplateForm->clone($mappedTemplateForm->name, $mappedTemplateForm->module_identifier, true);

                    $newForm->is_renewal_form = true;
                    $newForm->save();

                    $newform = DynamicForm::find($newForm->id);

                    $formObjectMapping = FormObjectMapping::bindFormToObject($newForm, $newVendorRegistration);
                }
            }
            else
            {
                // no template form mapped
                // throw some errors
            }
        }
    }

    public function clonePreQualification($targetVendorRegistration, $checkForNewForms)
    {
        foreach($this->vendorPreQualifications as $vendorPreQualification)
        {
            $templateForm = TemplateForm::getTemplateForm($vendorPreQualification->vendor_work_category_id);

            if($checkForNewForms && $templateForm && $vendorPreQualification->template_form_id != $templateForm->id)
            {
                VendorPreQualification::cloneFormIfNone($targetVendorRegistration->id, $vendorPreQualification->vendor_work_category_id);
            }
            else
            {
                $clonedNode = $vendorPreQualification->weightedNode->clone();
                $clone = $vendorPreQualification->replicate();
                $clone->vendor_registration_id = $targetVendorRegistration->id;
                $clone->status_id = VendorPreQualification::STATUS_DRAFT;
                $clone->weighted_node_id = $clonedNode->id;
                $clone->save();
            }
        }
    }

    public function cloneCompanyPersonnel($targetVendorRegistration)
    {
        foreach($this->companyPersonnel as $companyPersonnel)
        {
            $clone = $companyPersonnel->replicate();
            $clone->vendor_registration_id = $targetVendorRegistration->id;
            $clone->save();
        }

        ObjectField::findOrCreateNew($this, ObjectField::COMPANY__COMPANY_PERSONNEL_DIRECTOR)->copyAttachmentsTo(ObjectField::findOrCreateNew($targetVendorRegistration, ObjectField::COMPANY__COMPANY_PERSONNEL_DIRECTOR));
        ObjectField::findOrCreateNew($this, ObjectField::COMPANY__COMPANY_PERSONNEL_SHAREHOLDER)->copyAttachmentsTo(ObjectField::findOrCreateNew($targetVendorRegistration, ObjectField::COMPANY__COMPANY_PERSONNEL_SHAREHOLDER));
        ObjectField::findOrCreateNew($this, ObjectField::COMPANY__COMPANY_PERSONNEL_COMPANY_HEAD)->copyAttachmentsTo(ObjectField::findOrCreateNew($targetVendorRegistration, ObjectField::COMPANY__COMPANY_PERSONNEL_COMPANY_HEAD));
    }

    public function cloneSupplierCreditFacilities($targetVendorRegistration)
    {
        foreach($this->supplierCreditFacilities as $supplierCreditFacility)
        {
            $clone = $supplierCreditFacility->replicate();
            $clone->vendor_registration_id = $targetVendorRegistration->id;
            $clone->save();

            $supplierCreditFacility->copyAttachmentsTo($clone);
        }
    }

    public function cloneProjectTrackRecord($targetVendorRegistration)
    {
        foreach($this->trackRecordProjects as $trackRecordProject)
        {
            $clone = $trackRecordProject->replicate();
            $clone->vendor_registration_id = $targetVendorRegistration->id;
            $clone->save();

            foreach($trackRecordProject->trackRecordProjectVendorWorkSubcategories as $subCategory)
            {
                $subCategoryClone = $subCategory->replicate();
                $subCategoryClone->track_record_project_id = $clone->id;
                $subCategoryClone->save();
            }

            $trackRecordProject->copyAttachmentsTo($clone);
        }
    }

    // vm.Todo: Remove. No longer required; All updates will require approval.
    public function needsApproval()
    {
        $renewalForm           = FormObjectMapping::findRecord($this, DynamicForm::VENDOR_REGISTRATION_IDENTIFIER)->dynamicForm;
        $keyInformationChanged = $renewalForm->isRenewalApprovalRequired();

        return ($this->isSubmissionTypeNew() || $this->isSubmissionTypeRenewal() || $keyInformationChanged);
    }

    public function isLatestFinalized()
    {
        if( ! $this->company->finalVendorRegistration ) return false;

        return $this->id == $this->company->finalVendorRegistration->id;
    }

    public function isFirst()
    {
        return $this->revision == 0;
    }

    public function getSubmissionTypeTextAttribute()
    {
        if($this->isSubmissionTypeNew()) return trans('vendorManagement.newRegistration');

        if($this->isSubmissionTypeRenewal()) return trans('vendorManagement.renewal');

        return trans('vendorManagement.update');
    }

    public static function getSubmissionTypeText($type)
    {
        if($type == self::SUBMISSION_TYPE_NEW) return trans('vendorManagement.newRegistration');

        if($type == self::SUBMISSION_TYPE_RENEWAL) return trans('vendorManagement.renewal');

        if($type == self::SUBMISSION_TYPE_UPDATE) return trans('vendorManagement.update');

        return null;
    }

    public function isSubmissionTypeNew()
    {
        return $this->submission_type == self::SUBMISSION_TYPE_NEW;
    }

    public function isSubmissionTypeRenewal()
    {
        return $this->submission_type == self::SUBMISSION_TYPE_RENEWAL;
    }

    public function isSubmissionTypeUpdate()
    {
        return $this->submission_type == self::SUBMISSION_TYPE_UPDATE;
    }

    public function flushRelatedVendorRegistrationData()
    {
        VendorCategoryTemporaryRecord::flushRecords($this);

        FormObjectMapping::flushRecord($this, DynamicForm::VENDOR_REGISTRATION_IDENTIFIER);

        TrackRecordProject::flushRecords($this);

        UserCompanyLog::where('company_id', '=', $this->company->id)->delete();

        VendorPreQualification::flushRecords($this, true);
    }

    public function getOnApprovedView(){}
    public function getOnRejectedView(){}
    public function getOnPendingView(){}

    public function getRoute()
    {
        return route('vendorManagement.approval.registrationAndPreQualification.show', $this->id);
    }

    public function getViewData($locale){}
    public function getOnApprovedNotifyList(){}
    public function getOnRejectedNotifyList(){}
    public function getOnApprovedFunction(){}
    public function getOnRejectedFunction(){}
    public function getEmailSubject($locale){}
    public function getSubmitterId(){}

    public function getModuleName()
    {
        return trans('vendorManagement.vendorRegistration');
    }

    public static function getPendingVendorRegistrationsCount()
    {
        $query = "WITH final_vendor_registrations AS (
                      SELECT ROW_NUMBER() OVER (PARTITION BY company_id ORDER BY revision DESC) AS RANK, * FROM vendor_registrations WHERE deleted_at IS NULL AND status <> " . self::STATUS_COMPLETED . "
                  )
                  SELECT DISTINCT fvr.id
                  FROM final_vendor_registrations fvr
                  WHERE fvr.rank = 1
                  ORDER BY fvr.id ASC;";

        $queryResults = \DB::select(\DB::raw($query));

        return count($queryResults);
    }

    public function getDaysPendingAttribute()
    {
        $then = Carbon::parse($this->updated_at);
        $now = Carbon::now();

        return $then->diffInDays($now);
    }

    public function getProcessorRemarks()
    {
        if(!$this->processor) return null;

        if(!$this->processor->remark) return null;

        return $this->processor->remark->remarks;
    }

    public function getCurrentProcessor()
    {
        if(!$this->processor) return null;

        return User::find($this->processor->user_id);
    }

    public function setProcessor($userId)
    {
        if($this->processor && $this->processor->user_id === intval($userId)) return;

        VendorRegistrationProcessor::where('vendor_registration_id', '=', $this->id)->delete();

        VendorRegistrationProcessor::create([
            'vendor_registration_id' => $this->id,
            'user_id'                => $userId,
        ]);
    }
}