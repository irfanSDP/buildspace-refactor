<?php namespace PCK\DigitalStar\Evaluation;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use PCK\Base\Helpers;
use PCK\Base\ModuleAttachmentTrait;
use PCK\CompanyProject\CompanyProject;
use PCK\ConsultantManagement\ConsultantManagementContract;
use PCK\ConsultantManagement\LetterOfAward;
use PCK\ContractGroups\ContractGroup;
use PCK\ContractGroups\Types\Role;
use PCK\Statuses\FormStatus;
use PCK\Traits\FormTrait;
use PCK\VendorManagement\VendorManagementUserPermission;
use PCK\Verifier\Verifier;
use PCK\Verifier\Verifiable;
use PCK\Users\User;

class DsEvaluationForm extends Model implements FormStatus, Verifiable {

    use FormTrait, ModuleAttachmentTrait;

    protected $table = 'ds_evaluation_forms';

    protected $fillable = [
        'ds_evaluation_id',
        'project_id',
        'weighted_node_id',
        'score',
        'status_id',
        'submitted_for_approval_by'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function(self $model)
        {
            $model->status_id = self::STATUS_DRAFT;
        });
    }

    public function evaluation()
    {
        return $this->belongsTo('PCK\DigitalStar\Evaluation\DsEvaluation', 'ds_evaluation_id');
    }

    public function project()
    {
        return $this->belongsTo('PCK\Projects\Project', 'project_id');
    }

    public function weightedNode()
    {
        return $this->belongsTo('PCK\WeightedNode\WeightedNode');
    }

    public function submittedForApprovalBy()
    {
        return $this->belongsTo(User::class, 'submitted_for_approval_by');
    }

    public function evaluatorRemark()
    {
        return $this->hasOne('PCK\DigitalStar\Evaluation\DsEvaluationFormRemark', 'ds_evaluation_form_id');
    }

    public function evaluationFormLogs()
    {
        return $this->hasMany('PCK\DigitalStar\Evaluation\DsEvaluationLog', 'ds_evaluation_form_id');
    }

    public function isDraft()
    {
        return $this->status_id == self::STATUS_DRAFT;
    }

    public function isSubmitted()
    {
        return $this->status_id == self::STATUS_SUBMITTED;
    }

    public function isPendingVerification()
    {
        return $this->status_id == self::STATUS_PENDING_VERIFICATION;
    }

    public function isCompleted()
    {
        return $this->status_id == self::STATUS_COMPLETED;
    }

    public static function getStatuses()
    {
        return [
            -1                                => trans('general.all'),
            self::STATUS_DRAFT                => self::getStatusText(self::STATUS_DRAFT),
            self::STATUS_SUBMITTED            => self::getStatusText(self::STATUS_SUBMITTED),
            self::STATUS_PENDING_VERIFICATION => self::getStatusText(self::STATUS_PENDING_VERIFICATION),
            self::STATUS_COMPLETED            => self::getStatusText(self::STATUS_COMPLETED),
        ];
    }

    public static function renewForms($evaluation, $company)
    {
        self::deleteForms($evaluation, $company);
        self::cloneFormsIfNone($evaluation, $company);
    }

    public static function deleteForms($evaluation, $company)
    {
        self::where('company_id', '=', $company->id)
            ->where('vendor_performance_evaluation_id', '=', $evaluation->id)
            ->delete();
    }

    public function projectIds($companyId) {
        $projectIds = [];

        $records = CompanyProject::where('company_id', '=', $companyId)
            ->where('contract_group_id', '=', ContractGroup::getIdByGroup(Role::CONTRACTOR))
            ->orderBy('id', 'desc')
            ->get();

        foreach($records as $record) {
            if (! $record->project) continue;

            $projectIds[] = $record->project->id;
        }

        $consultantManagementContracts = ConsultantManagementContract::select('consultant_management_contracts.id')
            ->distinct()
            ->join('consultant_management_vendor_categories_rfp', 'consultant_management_vendor_categories_rfp.consultant_management_contract_id', '=', 'consultant_management_contracts.id')
            ->join('consultant_management_rfp_revisions', 'consultant_management_rfp_revisions.vendor_category_rfp_id', '=', 'consultant_management_vendor_categories_rfp.id')
            ->join('consultant_management_consultant_rfp', 'consultant_management_consultant_rfp.consultant_management_rfp_revision_id', '=', 'consultant_management_rfp_revisions.id')
            ->join('consultant_management_letter_of_awards', 'consultant_management_letter_of_awards.vendor_category_rfp_id', '=', 'consultant_management_vendor_categories_rfp.id')
            ->where('consultant_management_consultant_rfp.company_id', $companyId)
            ->whereRaw('consultant_management_consultant_rfp.awarded IS TRUE')
            ->where('consultant_management_letter_of_awards.status', LetterOfAward::STATUS_APPROVED)
            ->get();

        foreach($consultantManagementContracts as $contract) {
            $projectIds[] = $contract->id;
        }

        return $projectIds;
    }

    public static function cloneFormsIfNone($evaluation)
    {
        $latestCompletedCycle = DsCycle::latestCompleted();
        $cycleForms = DsCycleTemplateForm::where('ds_cycle_id', $evaluation->ds_cycle_id)->get();

        foreach($cycleForms as $cycleForm)
        {
            if (is_null($cycleForm->ds_template_form_id)) { // No template form set
                if (! $latestCompletedCycle) { // No previous completed cycle
                    continue;
                }
                $previousCycleForm = DsCycleTemplateForm::where('ds_cycle_id', $latestCompletedCycle->id)
                    ->where('type', $cycleForm->type)
                    ->first();

                if (! $previousCycleForm) {
                    continue;
                }
                if (is_null($previousCycleForm->ds_template_form_id)) {
                    continue;
                }

                $cycleForm->ds_template_form_id = $previousCycleForm->ds_template_form_id;
                $cycleForm->save();

                $cycleForm = DsCycleTemplateForm::find($cycleForm->id);
            }

            switch ($cycleForm->type) {
                case 'project':
                    $companyProjects = CompanyProject::where('company_id', $evaluation->company_id)
                        ->whereIn('project_id', self::projectIds($evaluation->company_id))
                        ->get();

                    foreach($companyProjects as $companyProject) {
                        $existingRecord = DsEvaluationForm::where('ds_evaluation_id', $evaluation->id)
                            ->where('project_id', $companyProject->project_id)
                            ->first();

                        if ($existingRecord) continue;

                        $weightedNode = $cycleForm->templateForm->weightedNode->createNew();    // Create withut cloning

                        self::create(array(
                            'ds_evaluation_id'                 => $evaluation->id,
                            'project_id'                       => $companyProject->project_id,
                            'weighted_node_id'                 => $weightedNode->id,
                        ));
                    }
                    break;

                case 'company':
                    $existingRecord = DsEvaluationForm::where('ds_evaluation_id', $evaluation->id)
                        ->whereNull('project_id')
                        ->first();

                    if ($existingRecord) break;

                    $weightedNode = $cycleForm->templateForm->weightedNode->clone();    // Cloned

                    self::create(array(
                        'ds_evaluation_id'                 => $evaluation->id,
                        'weighted_node_id'                 => $weightedNode->id,
                    ));
                    break;
            }
        }
        return true;
    }

    public function getListOfVerifiers($formId)
    {
        $form = self::where('id', '=', $formId)->first();
        if (is_null($form->project_id)) {
            $permissionType = VendorManagementUserPermission::TYPE_DIGITAL_STAR_VERIFIER_COMPANY;
        } else {
            $permissionType = VendorManagementUserPermission::TYPE_DIGITAL_STAR_VERIFIER_PROJECT;
        }

        $userIds = VendorManagementUserPermission::getUserIds($permissionType);

        $users = User::whereIn('id', $userIds)
            ->where('confirmed', '=', true)
            ->where('account_blocked_status', '=', false)
            ->orderBy('name')
            ->get();

        return $users;
    }

    /**
     * Verifiable functions
     */
    public function getOnApprovedView()
    {
        return 'digitalStar.approved';  // Notifications view
    }

    public function getOnRejectedView()
    {
        return 'digitalStar.rejected';  // Notifications view
    }

    public function getOnPendingView()
    {
        return 'digitalStar.pending';   // Notifications view
    }

    public function getRoute()
    {
        // Routes for verifier to approve / reject evaluation
        if (is_null($this->project_id)) {
            return route('digital-star.approval.company.approve.edit', [$this->id]);
        } else {
            return route('digital-star.approval.project.approve.edit', [$this->id]);
        }
    }

    // Used by verifier module for notifications/email
    public function getViewData($locale)
    {
        $viewData = [
            'actionBy'           => \Confide::user()->name,
            'companyName'        => null,
            'vendorGroup'        => null,
            'projectTitle'       => null,
            'contractNo'         => null,
            'cycleStartDate'     => Carbon::parse($this->evaluation->cycle->start_date)->format(\Config::get('dates.full_format')),
            'cycleEndDate'       => Carbon::parse($this->evaluation->cycle->end_date)->format(\Config::get('dates.full_format')),
            'recipientLocale'    => $locale,
        ];

        $evaluation = $this->evaluation;
        if ($evaluation) {
            $company = $evaluation->company;
            if ($company) {
                $viewData['companyName'] = $company->name;

                $vendorGroup = $company->contractGroupCategory;
                if ($vendorGroup) {
                    $viewData['vendorGroup'] = $vendorGroup->name;
                }
            }
        }

        if (! is_null($this->project_id)) {   // Project Evaluation
            $project = $this->project;
            if ($project) {
                $viewData['projectTitle'] = $project->title;
                $viewData['contractNo'] = $project->reference;
            }
        }

        if( ! Verifier::isApproved($this) )
        {
            $viewData['link'] = $this->getRoute();
        }

        return $viewData;
    }

    public function getOnApprovedNotifyList()
    {
        $recipients = $this->getOnRejectedNotifyList();

        $verifiers = [];
        $verifierRecords = Verifier::getLog($this);

        foreach($verifierRecords as $record) {
            array_push($verifiers, $record->verifier);
        }

        $recipients = array_merge($recipients, $verifiers);

        return $recipients;
    }

    public function getOnRejectedNotifyList()
    {
        $recipients = [];

        if (is_null($this->project_id)) {   // Company evaluation
            $roleSlug = 'company-processor';
        } else {    // Project evaluation
            $roleSlug = 'project-evaluator';
        }

        $role = DsRole::where('slug', '=', $roleSlug)->first();
        if ($role) {
            $userRoles = DsEvaluationFormUserRole::where('ds_evaluation_form_id', $this->id)
                ->where('ds_role_id', $role->id)
                ->get();

            if (! $userRoles->isEmpty()) {
                $userIds = $userRoles->lists('user_id');
                $users = User::whereIn('id', $userIds)->get();

                foreach ($users as $user) {
                    $recipients[] = $user;
                }
            }
        }

        return $recipients;
    }

    public function getOnApprovedFunction()
    {
        return function()
        {
            $this->status_id = self::STATUS_COMPLETED;
			$this->save();
		};
    }

    public function getOnRejectedFunction()
    {
        return function()
        {
            $this->status_id = self::STATUS_SUBMITTED;
			$this->save();
		};
    }

    public function onReview()
    {

    }

    public function getEmailSubject($locale)
    {
        if (is_null($this->project_id)) {
            $type = 'companyEvaluation';
        } else {
            $type = 'projectEvaluation';
        }
        return trans('digitalStar/email.'.$type.'NotificationTitle', [], 'messages', $locale);
    }

    public function getSubmitterId()
    {

    }

    public function getModuleName()
    {
        return trans('digitalStar/digitalStar.moduleName');
    }

    public function getDaysPendingAttribute()
    {
        return Helpers::getDaysPending($this);
    }
}