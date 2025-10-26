<?php namespace PCK\TechnicalEvaluationSetReferences;

use Illuminate\Database\Eloquent\Collection;
use PCK\ContractLimits\ContractLimit;
use PCK\Projects\Project;
use PCK\WorkCategories\WorkCategory;

class TechnicalEvaluationSetReferenceRepository {

    public function find($id)
    {
        return TechnicalEvaluationSetReference::find($id);
    }

    /**
     * Find a template resource by its identifiers.
     *
     * @param WorkCategory $workCategory
     * @param null         $contractLimit
     *
     * @return mixed
     */
    public function findTemplate(WorkCategory $workCategory, $contractLimit = null)
    {
        $setReference = TechnicalEvaluationSetReference::where('work_category_id', '=', $workCategory->id)
            ->whereNull('project_id');

        if( $contractLimit )
        {
            $setReference->where('contract_limit_id', '=', $contractLimit->id);
        }
        else
        {
            $setReference->whereNull('contract_limit_id');
        }

        return $setReference->get()->first();
    }

    /**
     * Returns true if the Work Category has at least one associated Technical Evaluation template.
     *
     * @param WorkCategory $workCategory
     *
     * @return bool
     */
    public function hasTemplate(WorkCategory $workCategory)
    {
        return ( TechnicalEvaluationSetReference::where('work_category_id', '=', $workCategory->id)->whereNull('project_id')->count() > 0 );
    }

    /**
     * Returns all Set References which references templates.
     *
     * @return mixed
     */
    public function templateSetReferences()
    {
        return TechnicalEvaluationSetReference::whereNull('project_id')
            ->whereNotNull('work_category_id')
            ->orderBy('hidden')
            ->get();
    }

    /**
     * Creates a new template resource.
     *
     * @param WorkCategory                      $workCategory
     * @param                                   $contractLimit
     * @param TechnicalEvaluationSetReference   $templateSetReference
     *
     * @return bool
     */
    public function storeTemplate(WorkCategory $workCategory, $contractLimit, TechnicalEvaluationSetReference $templateSetReference = null)
    {
        $setReference = new TechnicalEvaluationSetReference;

        $setReference->work_category_id = $workCategory->id;

        if( $contractLimit ) $setReference->contract_limit_id = $contractLimit->id;

        if( $templateSetReference )
        {
            $newSet = $templateSetReference->set->copy();

            $setReference->set_id = $newSet->id;

            $setReference->save();

            $templateSetReference->copyAttachmentListTo($setReference);
        }

        return $setReference->save();
    }

    /**
     * Copies a Technical Evaluation from the associated template into the project.
     *
     * @param Project       $project
     * @param ContractLimit $contractLimit
     *
     * @return bool
     */
    public function copy(Project $project, ContractLimit $contractLimit = null)
    {
        if( ! $templateSetReference = $this->findTemplate($project->workCategory, $contractLimit) ) return false;

        if($setReference = $this->getSetReferenceByProject($project)) return false;

        $setReference = new TechnicalEvaluationSetReference();

        $setReference->project_id = $project->id;

        $newSet = $templateSetReference->set->copy();

        $setReference->set_id = $newSet->id;

        $success = $setReference->save();

        $templateSetReference->copyAttachmentListTo($setReference);

        return $success;
    }

    public function getSetReferenceByProject(Project $project)
    {
        return TechnicalEvaluationSetReference::where('project_id', '=', $project->id)->orderBy('created_at', 'DESC')->first();
    }

    public function getWorkCategoryContractLimits(WorkCategory $workCategory)
    {
        $references = TechnicalEvaluationSetReference::where('work_category_id', '=', $workCategory->id)->where('hidden', '=', false)->distinct('contract_limit_id')->get();

        $hasNull        = false;
        $contractLimits = new Collection;

        foreach($references as $reference)
        {
            if( $reference->contractLimit == null ) $hasNull = true;

            if( $reference->contractLimit ) $contractLimits->push($reference->contractLimit);
        }

        if( $hasNull ) $contractLimits->prepend(null);

        return $contractLimits;
    }

}