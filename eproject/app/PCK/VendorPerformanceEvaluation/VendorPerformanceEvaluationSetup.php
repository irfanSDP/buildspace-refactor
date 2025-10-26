<?php namespace PCK\VendorPerformanceEvaluation;

use Illuminate\Database\Eloquent\Model;
use PCK\VendorWorkCategory\VendorWorkCategory;
use PCK\Companies\Company;
use PCK\VendorPerformanceEvaluation\TemplateForm;
use PCK\VendorPerformanceEvaluation\VendorPerformanceEvaluation;
use PCK\VendorPerformanceEvaluation\VendorPerformanceEvaluationCompanyForm;

class VendorPerformanceEvaluationSetup extends Model {

    protected $table = 'vendor_performance_evaluation_setups';

    protected $fillable = ['vendor_performance_evaluation_id', 'company_id', 'template_node_id', 'vendor_management_grade_id', 'vendor_work_category_id'];

    protected static function boot()
    {
        parent::boot();

        static::created(function(self $model)
        {
            $projectHasPreviousEvaluations = VendorPerformanceEvaluation::where('project_id', '=', $model->vendorPerformanceEvaluation->project_id)
                ->where('id', '!=', $model->vendor_performance_evaluation_id)
                ->exists();

            if($projectHasPreviousEvaluations)
            {
                $model->copyEvaluationFormsFromPreviousEvaluations();
            }
        });

        static::saving(function(self $model)
        {
            if($model->isDirty('template_node_id'))
            {
                FormChangeLog::create([
                    'user_id' => \Confide::user()->id,
                    'vendor_performance_evaluation_setup_id' => $model->id,
                    'old_template_node_id' => $model->getOriginal('template_node_id'),
                    'new_template_node_id' => $model->template_node_id,
                ]);
            }
        });
    }

    public function weightedNode()
    {
        return $this->belongsTo('PCK\WeightedNode\WeightedNode', 'template_node_id');
    }

    public function vendorManagementGrade()
    {
        return $this->belongsTo('PCK\ModuleParameters\VendorManagement\VendorManagementGrade', 'vendor_management_grade_id');
    }

    public function vendorPerformanceEvaluation()
    {
        return $this->belongsTo('PCK\VendorPerformanceEvaluation\VendorPerformanceEvaluation');
    }

    public function vendorWorkCategory()
    {
        return $this->belongsTo(VendorWorkCategory::class, 'vendor_work_category_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function copyEvaluationFormsFromPreviousEvaluations()
    {
        $latestMatchingSetupTemplateForms = \DB::select(\DB::raw("
            SELECT tf.original_form_id
            FROM vendor_performance_evaluation_setups s
            JOIN vendor_performance_evaluations e ON e.id = s.vendor_performance_evaluation_id
            LEFT JOIN vendor_performance_evaluation_template_forms tf ON tf.weighted_node_id = s.template_node_id
            WHERE e.project_id = {$this->vendorPerformanceEvaluation->project_id}
            AND s.company_id = {$this->company_id}
            AND s.vendor_work_category_id = {$this->vendor_work_category_id}
            AND s.id != {$this->id}
            ORDER by e.vendor_performance_evaluation_cycle_id DESC, e.created_at DESC
            LIMIT 1
            "));

        $originalTemplateFormId = null;

        if(!empty($latestMatchingSetupTemplateForms))
        {
            $originalTemplateFormId = $latestMatchingSetupTemplateForms[0]->original_form_id;
        }

        $templateForm = TemplateForm::getTemplateForm($originalTemplateFormId);

        $weightedNodeId = null;
        $gradeId        = null;

        if(!is_null($templateForm))
        {
            $weightedNodeId = $templateForm->weighted_node_id;

            $gradingClone = $templateForm->vendorManagementGrade->clone();
            $gradeId      = $gradingClone->id;
        }

        if(!is_null($weightedNodeId) || !is_null($gradeId)) \DB::statement("UPDATE vendor_performance_evaluation_setups SET template_node_id = {$weightedNodeId}, vendor_management_grade_id = {$gradeId} WHERE id = {$this->id}");
    }

    public function getCompanyForms()
    {
        return VendorPerformanceEvaluationCompanyForm::where('vendor_performance_evaluation_id', '=', $this->vendor_performance_evaluation_id)
            ->where('company_id', '=', $this->company_id)
            ->where('vendor_work_category_id', '=', $this->vendor_work_category_id)
            ->get();
    }
}