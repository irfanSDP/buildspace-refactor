<?php namespace PCK\VendorPerformanceEvaluation;

use Carbon\Carbon;
use PCK\Statuses\FormStatus;
use PCK\CompanyProject\CompanyProject;
use PCK\Traits\FormTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingTrait;
use PCK\Base\ModuleAttachmentTrait;
use PCK\ContractGroups\Types\Role;
use PCK\Verifier\Verifier;
use PCK\Verifier\Verifiable;
use PCK\Users\User;
use PCK\VendorWorkCategory\VendorWorkCategory;

class VendorPerformanceEvaluationCompanyForm extends Model implements FormStatus, Verifiable {

    use FormTrait, SoftDeletingTrait, ModuleAttachmentTrait;

    protected $table = 'vendor_performance_evaluation_company_forms';

    protected $fillable = ['vendor_performance_evaluation_id', 'company_id', 'weighted_node_id', 'evaluator_company_id', 'vendor_management_grade_id', 'vendor_work_category_id', 'status_id'];

    protected static function boot()
    {
        parent::boot();

        static::creating(function(self $model)
        {
            $model->status_id = self::STATUS_DRAFT;
        });
    }

    public function vendorManagementGrade()
    {
        return $this->belongsTo('PCK\ModuleParameters\VendorManagement\VendorManagementGrade', 'vendor_management_grade_id');
    }

    public function vendorPerformanceEvaluation()
    {
        return $this->belongsTo('PCK\VendorPerformanceEvaluation\VendorPerformanceEvaluation');
    }

    public function weightedNode()
    {
        return $this->belongsTo('PCK\WeightedNode\WeightedNode');
    }

    public function company()
    {
        return $this->belongsTo('PCK\Companies\Company');
    }

    public function evaluatorCompany()
    {
        return $this->belongsTo('PCK\Companies\Company', 'evaluator_company_id');
    }

    public function vendorWorkCategory()
    {
        return $this->belongsTo(VendorWorkCategory::class, 'vendor_work_category_id');
    }

    public function submittedForApprovalBy()
    {
        return $this->belongsTo(User::class, 'submitted_for_approval_by');
    }

    public function vendorPerformanceEvaluationCompanyFormEvaluationLogs()
    {
        return $this->hasMany(VendorPerformanceEvaluationCompanyFormEvaluationLog::class, 'vendor_performance_evaluation_company_form_id');
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

    public static function cloneFormsIfNone($evaluation, $company)
    {
        $setups = VendorPerformanceEvaluationSetup::where('vendor_performance_evaluation_id', '=', $evaluation->id)
            ->where('company_id', '=', $company->id)
            ->get();

        foreach($setups as $setup)
        {
            if( is_null($setup->template_node_id) ) continue;

            $evaluatorCompanyProjects = CompanyProject::where('project_id', '=', $evaluation->project_id)
                ->where('company_id', '!=', $setup->company_id)
                ->get();

            foreach($evaluatorCompanyProjects as $evaluatorCompanyProject)
            {
                $isBuCompany = $evaluatorCompanyProject->company->hasProjectRole($evaluatorCompanyProject->project, Role::PROJECT_OWNER);

                if( ($evaluation->type == VendorPerformanceEvaluation::TYPE_180) && !$isBuCompany )
                {
                    continue;
                }

                $existingRecord = self::where('vendor_performance_evaluation_id', '=', $evaluation->id)
                    ->where('company_id', '=', $company->id)
                    ->where('evaluator_company_id', '=', $evaluatorCompanyProject->company_id)
                    ->where('vendor_work_category_id', '=', $setup->vendor_work_category_id)
                    ->first();

                if( $existingRecord ) continue;

                $clonedForm = $setup->weightedNode->clone();

                $form = self::create(array(
                    'vendor_performance_evaluation_id' => $evaluation->id,
                    'company_id'                       => $company->id,
                    'weighted_node_id'                 => $clonedForm->id,
                    'evaluator_company_id'             => $evaluatorCompanyProject->company_id,
                    'vendor_work_category_id'          => $setup->vendor_work_category_id,
                ));
            }
        }
    }

    public function getListOfVerifiers()
    {
        $buCompany = $this->vendorPerformanceEvaluation->project->getCompanyByGroup(Role::PROJECT_OWNER);

        $currentUser = \Confide::user();

        return $buCompany->getProjectUsers($this->vendorPerformanceEvaluation->project)->sortBy('name');
    }

    /**
     * Verifiable functions
     */
    public function getOnApprovedView()
    {
        return 'vendorPerformanceEvaluation.approved';
    }

    public function getOnRejectedView()
    {
        return 'vendorPerformanceEvaluation.rejected';
    }

    public function getOnPendingView()
    {
        return 'vendorPerformanceEvaluation.pending';
    }

    public function getRoute()
    {
        return route('vendorPerformanceEvaluation.companyForms.approval.edit', [$this->id]);
    }

    public function getViewData($locale)
    {
        $viewData = [
            'senderName'         => \Confide::user()->name,
			'project' 		     => $this->vendorPerformanceEvaluation->project->title,
            'company'            => $this->company->name,
            'vendorWorkCategory' => $this->vendorWorkCategory->name,
            'cycleStartDate'     => Carbon::parse($this->vendorPerformanceEvaluation->cycle->start_date)->format(\Config::get('dates.full_format')),
            'cycleEndDate'       => Carbon::parse($this->vendorPerformanceEvaluation->cycle->end_date)->format(\Config::get('dates.full_format')),
            'recipientLocale'    => $locale,
        ];
        
        if( ! Verifier::isApproved($this) )
        {
            $viewData['toRoute'] = $this->getRoute();
        }

        return $viewData;
    }

    public function getOnApprovedNotifyList()
    {
        $users = [];

        if(is_null($this->submittedForApprovalBy))
        {
            $verifierRecords = Verifier::getLog($this);

            foreach($verifierRecords as $record)
            {
                array_push($users, $record->verifier);
            }
        }
        else
        {
            array_push($users, $this->submittedForApprovalBy);
        }

        return $users;
    }

    public function getOnRejectedNotifyList()
    {
        return $this->getOnApprovedNotifyList();
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
        return trans('vendorManagement.vendorPerformanceEvaluation', [], 'messages', $locale);
    }

    public function getSubmitterId()
    {

    }

    public function getModuleName()
    {
        return trans('vendorManagement.vendorPerformanceEvaluationForm');
    }

    public static function getPendingVpeCompanyFormsCount()
    {
        $latestCycle = Cycle::latestActive();

        if(is_null($latestCycle)) return 0;

        $user = \Confide::user();

        $userCompanyIds = $user->getAllCompanyIds();

        if(empty($userCompanyIds)) return 0;

        $query = "SELECT vpecf.id
                  FROM vendor_performance_evaluation_cycles vpec 
                  INNER JOIN vendor_performance_evaluations vpe ON vpec.id = vpe.vendor_performance_evaluation_cycle_id 
                  INNER JOIN vendor_performance_evaluation_company_forms vpecf ON vpecf.vendor_performance_evaluation_id = vpe.id
                  INNER JOIN projects p ON p.id = vpe.project_id
                  INNER JOIN contract_group_project_users cgpu ON cgpu.project_id = p.id AND cgpu.user_id = {$user->id}
                  WHERE vpecf.deleted_at IS NULL
                  AND vpe.deleted_at IS NULL
                  AND p.deleted_at IS NULL
                  AND vpec.id = {$latestCycle->id}
                  AND vpecf.evaluator_company_id IN (" . implode(', ', $userCompanyIds) . ")
                  AND vpecf.status_id IN (" . implode(', ', [self::STATUS_SUBMITTED, self::STATUS_PENDING_VERIFICATION]) . ")
                  ORDER BY vpecf.id ASC;";

        $queryResults = \DB::select(\DB::raw($query));

        return count($queryResults);
    }

    public function getDaysPendingAttribute()
    {
        $then = Carbon::parse($this->updated_at);
        $now = Carbon::now();

        return $then->diffInDays($now);
    }
}