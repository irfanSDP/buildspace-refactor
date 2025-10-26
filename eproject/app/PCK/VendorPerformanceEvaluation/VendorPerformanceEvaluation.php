<?php namespace PCK\VendorPerformanceEvaluation;

use Illuminate\Database\Eloquent\Model;
use PCK\Projects\Project;
use PCK\Base\Helpers;
use PCK\CompanyProject\CompanyProject;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletingTrait;
use PCK\VendorPerformanceEvaluation\RemovalRequest;
use PCK\ContractGroupProjectUsers\ContractGroupProjectUser;

class VendorPerformanceEvaluation extends Model {

    use SoftDeletingTrait;

    const STATUS_DRAFT = 1;
    const STATUS_IN_PROGRESS = 2;
    const STATUS_COMPLETED = 4;

    // Not to be confused with the \PCK\Projects\Project stages.
    const PROJECT_STAGE_DESIGN                   = 1;
    const PROJECT_STAGE_CONSTRUCTION             = 2;
    const PROJECT_STAGE_DEFECTS_LIABILITY_PERIOD = 3;
    const PROJECT_STAGE_COMPLETED                = 4;

    const TYPE_360 = 1;
    const TYPE_180 = 2;

    protected $table = 'vendor_performance_evaluations';

    protected $fillable = ['vendor_performance_evaluation_cycle_id', 'project_id', 'project_status_id', 'business_unit_id', 'person_in_charge_id', 'start_date', 'end_date', 'type'];

    public $skipSyncVendorWorkCategorySetups = false;

    protected static function boot()
    {
        parent::boot();

        static::creating(function(self $model)
        {
            $model->status_id = self::STATUS_DRAFT;
            $model->created_by = \Auth::id();
        });

        static::created(function ($model) {
            if (! $model->skipSyncVendorWorkCategorySetups) {
                $model->syncVendorWorkCategorySetups();
            }
        });

        static::saving(function(self $model)
        {
            $model->updated_by = \Auth::id();
        });

        static::saved(function(self $model)
        {
            if($model->type == self::TYPE_180)
            {
                VendorPerformanceEvaluator::purge($model);
            }

            if($model->isStatus(self::STATUS_DRAFT) && Carbon::parse($model->start_date)->isPast()) $model->start();

            if($model->isStatus(self::STATUS_IN_PROGRESS) && Carbon::parse($model->end_date)->isPast()) $model->end();
        });

        static::deleted(function(self $model)
        {
            \DB::statement("UPDATE vendor_performance_evaluations SET deleted_by = :userId WHERE id = {$model->id}", ['userId' => \Auth::id()]);

            RemovalRequest::where('vendor_performance_evaluation_id', '=', $model->id)->update(array('action_by' => \Confide::user()->id, 'evaluation_removed' => true, 'removed_at' => \Carbon\Carbon::now()->toDateTimeString()));

            foreach($model->companyForms as $companyForm)
            {
                $companyForm->delete();
            }
        });
    }

    public static function getStatusText($status)
    {
        switch($status)
        {
            case self::STATUS_DRAFT:
                $text = trans('vendorManagement.draft');
                break;
            case self::STATUS_IN_PROGRESS:
                $text = trans('vendorManagement.inProgress');
                break;
            case self::STATUS_COMPLETED:
                $text = trans('vendorManagement.completed');
                break;
            default:
                throw new \Exception("Invalid type");
        }

        return $text;
    }

    public function project()
    {
        return $this->belongsTo('PCK\Projects\Project')->withTrashed();
    }

    public function cycle()
    {
        return $this->belongsTo('PCK\VendorPerformanceEvaluation\Cycle', 'vendor_performance_evaluation_cycle_id');
    }

    public function evaluators()
    {
        return $this->hasMany(VendorPerformanceEvaluator::class, 'vendor_performance_evaluation_id');
    }

    public function isStatus($status)
    {
        return $this->status_id == $status;
    }

    public static function determineProjectStage(Project $project)
    {
        if( $project->isPostContract() 
            && $project->getBsProjectMainInformation()
            && $project->getBsProjectMainInformation()->projectStructure
            && $project->getBsProjectMainInformation()->projectStructure->letterOfAward
            && $project->getBsProjectMainInformation()->projectStructure->letterOfAward->contract_period_from 
            && Carbon::parse($project->getBsProjectMainInformation()->projectStructure->letterOfAward->contract_period_from)->isPast() )
        {
            if( $project->pam2006Detail && $project->pam2006Detail->certificate_of_making_good_defect_date && Carbon::parse($project->pam2006Detail->certificate_of_making_good_defect_date)->isPast() )
            {
                return self::PROJECT_STAGE_COMPLETED;
            }

            if( $project->pam2006Detail && $project->pam2006Detail->cpc_date && Carbon::parse($project->pam2006Detail->cpc_date)->isPast() )
            {
                return self::PROJECT_STAGE_DEFECTS_LIABILITY_PERIOD;
            }

            return self::PROJECT_STAGE_CONSTRUCTION;
        }

        return self::PROJECT_STAGE_DESIGN;
    }

    public function companyForms()
    {
        return $this->hasMany('PCK\VendorPerformanceEvaluation\VendorPerformanceEvaluationCompanyForm', 'vendor_performance_evaluation_id');
    }

    /**
     * Should return results in the form:
     * [
     *      company_id_1 => [
     *          vendor_work_category_id_1 => scoreA,
     *          vendor_work_category_id_2 => scoreB,
     *      ]
     * ]
     **/
    public function calculateScores()
    {
        $scoresByCompany = [];

        foreach($this->companyForms()->where('status_id', '=', VendorPerformanceEvaluationCompanyForm::STATUS_COMPLETED)->get() as $companyForm)
        {
            if( ! array_key_exists($companyForm->company_id, $scoresByCompany) ) $scoresByCompany[ $companyForm->company_id ] = [];

            if( ! array_key_exists($companyForm->vendor_work_category_id, $scoresByCompany[$companyForm->company_id]) ) $scoresByCompany[ $companyForm->company_id ][$companyForm->vendor_work_category_id] = [];

            $scoresByCompany[ $companyForm->company_id ][ $companyForm->vendor_work_category_id ][] = $companyForm->weightedNode->getScore();
        }

        $averageScores = [];

        foreach($scoresByCompany as $companyId => $vendorWorkCategoryScores)
        {
            foreach($vendorWorkCategoryScores as $vendorWorkCategoryId => $scores)
            {
                $averageScores[ $companyId ][ $vendorWorkCategoryId ] = Helpers::divide(array_sum($scores), count($scores));
            }
        }

        return $averageScores;
    }

    public function generateScores()
    {
        foreach($this->calculateScores() as $companyId => $vendorWorkCategoryScores)
        {
            foreach($vendorWorkCategoryScores as $vendorWorkCategoryId => $averageScore)
            {
                $evaluationScore = EvaluationScore::firstOrNew(array(
                    'vendor_work_category_id'          => $vendorWorkCategoryId,
                    'company_id'                       => $companyId,
                    'vendor_performance_evaluation_id' => $this->id,
                ));

                $evaluationScore->score = $averageScore;
                $evaluationScore->save();
            }
        }
    }

    public function start()
    {
        if( ! $this->isStatus(self::STATUS_DRAFT) ) return;

        $this->status_id = self::STATUS_IN_PROGRESS;

        $this->save();

        $this->generateForms();
    }

    public function generateForms()
    {
        $companyProjects = CompanyProject::where('project_id', '=', $this->project_id)->get();

        foreach($companyProjects as $projectCompany)
        {
            VendorPerformanceEvaluationCompanyForm::cloneFormsIfNone($this, $projectCompany->company);
        }
    }

    public function end()
    {
        if( ! $this->isStatus(self::STATUS_IN_PROGRESS) ) return;

        $this->generateScores();

        $this->status_id = self::STATUS_COMPLETED;

        $this->save();
    }

    public static function getProjectStageName($projectStageId)
    {
        switch($projectStageId)
        {
            case self::PROJECT_STAGE_DESIGN:
                $name = trans('vendorManagement.design');
                break;
            case self::PROJECT_STAGE_CONSTRUCTION:
                $name = trans('vendorManagement.construction');
                break;
            case self::PROJECT_STAGE_DEFECTS_LIABILITY_PERIOD:
                $name = trans('vendorManagement.defectsLiabilityPeriod');
                break;
            case self::PROJECT_STAGE_COMPLETED:
                $name = trans('vendorManagement.completed');
                break;
            default:
                throw new \Exception("Invalid project stage");
        }

        return $name;
    }

    public function syncVendorWorkCategorySetups()
    {
        if($this->status_id == self::STATUS_COMPLETED) return;

        $assignedCompanies = $this->project->selectedCompanies;

        // Remove setups for companies no longer assigned.
        VendorPerformanceEvaluationSetup::where('vendor_performance_evaluation_id', '=', $this->id)
            ->whereNotIn('company_id', $assignedCompanies->lists('id'))
            ->delete();

        foreach($assignedCompanies as $assignedCompany)
        {
            $vendorWorkCategories = [];

            foreach($assignedCompany->vendors as $vendorRecord) $vendorWorkCategories[] = $vendorRecord->vendor_work_category_id;

            // Remove unrelated company setups (vendor work category changed removed).
            VendorPerformanceEvaluationSetup::where('vendor_performance_evaluation_id', '=', $this->id)
                ->where('company_id', '=', $assignedCompany->id)
                ->whereNotIn('vendor_work_category_id', $vendorWorkCategories)
                ->delete();

            // Create new setups.
            foreach($vendorWorkCategories as $vendorWorkCategoryId)
            {
                VendorPerformanceEvaluationSetup::firstOrCreate([
                    'vendor_performance_evaluation_id' => $this->id,
                    'company_id' => $assignedCompany->id,
                    'vendor_work_category_id' => $vendorWorkCategoryId,
                ]);
            }
        }
    }

    // public static function getPendingEvaluationsCount()
    // {
    //     $user = \Confide::user();

    //     $evaluationsAsEvaluator = VendorPerformanceEvaluator::where('user_id', '=', $user->id)->lists('vendor_performance_evaluation_id');

    //     $relevantProjectIds = ContractGroupProjectUser::where('user_id', '=', $user->id)
    //         ->lists('project_id');

    //     $evaluationsAsProjectUser = VendorPerformanceEvaluationCompanyForm::whereIn('evaluator_company_id', $user->getAllCompanies()->lists('id'))
    //         ->join('vendor_performance_evaluations', 'vendor_performance_evaluations.id', '=', 'vendor_performance_evaluation_company_forms.vendor_performance_evaluation_id')
    //         ->join('projects', 'projects.id', '=', 'vendor_performance_evaluations.project_id')
    //         ->whereIn('projects.id', $relevantProjectIds)
    //         ->lists('vendor_performance_evaluation_id');

    //     $evaluationIds = array_merge($evaluationsAsEvaluator, $evaluationsAsProjectUser);

    //     return VendorPerformanceEvaluation::whereIn('id', $evaluationIds)
    //         ->has('project')
    //         ->where('status_id', '=', VendorPerformanceEvaluation::STATUS_IN_PROGRESS)
    //         ->where('start_date', '<=', 'now()')
    //         ->count();
    // }

    public static function getPendingEvaluationsCount()
    {
        $user = \Confide::user();

        // Always coerce to arrays (lists() can return null if nothing matches)
        $evaluationsAsEvaluator = (array) VendorPerformanceEvaluator::where('user_id', $user->id)
            ->lists('vendor_performance_evaluation_id');

        $relevantProjectIds = (array) ContractGroupProjectUser::where('user_id', $user->id)
            ->lists('project_id');

        // getAllCompanies() sometimes returns a collection/array with nulls – sanitize first
        $companies = $user->getAllCompanies();

        if ($companies instanceof \Illuminate\Support\Collection) {
            $companyIds = $companies
                ->filter(function ($row) {
                    if (is_object($row))
                        return isset($row->id);
                    if (is_array($row))
                        return isset($row['id']);
                    return false;
                })
                ->lists('id'); // returns array of ids
        } else {
            $arr = is_array($companies) ? $companies : [];
            $arr = array_filter($arr); // drop null rows
            $ids = function ($row) {
                if (is_object($row) && isset($row->id))
                    return $row->id;
                if (is_array($row) && isset($row['id']))
                    return $row['id'];
                return null;
            };
            $companyIds = array_values(array_filter(array_map($ids, $arr)));
        }

        // If either side is empty, skip the JOIN query entirely
        $evaluationsAsProjectUser = [];
        if (!empty($companyIds) && !empty($relevantProjectIds)) {
            $evaluationsAsProjectUser = (array) VendorPerformanceEvaluationCompanyForm::whereIn('evaluator_company_id', $companyIds)
                ->join('vendor_performance_evaluations', 'vendor_performance_evaluations.id', '=', 'vendor_performance_evaluation_company_forms.vendor_performance_evaluation_id')
                ->join('projects', 'projects.id', '=', 'vendor_performance_evaluations.project_id')
                ->whereIn('projects.id', $relevantProjectIds)
                ->lists('vendor_performance_evaluation_id');
        }

        // Merge safely, dedupe, and bail out early if none
        $evaluationIds = array_values(array_unique(array_merge(
            $evaluationsAsEvaluator ?: [],
            $evaluationsAsProjectUser ?: []
        )));

        if (empty($evaluationIds)) {
            return 0;
        }

        return VendorPerformanceEvaluation::whereIn('id', $evaluationIds)
            ->has('project')
            ->where('status_id', VendorPerformanceEvaluation::STATUS_IN_PROGRESS)
            ->where('start_date', '<=', \DB::raw('now()')) // compare to SQL now()
            ->count();
    }

}