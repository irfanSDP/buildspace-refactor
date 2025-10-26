<?php namespace PCK\TechnicalEvaluationSetReferences;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use PCK\Companies\Company;
use PCK\ContractLimits\ContractLimit;
use PCK\Helpers\ModelOperations;
use PCK\TechnicalEvaluationAttachments\TechnicalEvaluationAttachment;
use PCK\TechnicalEvaluationItems\TechnicalEvaluationItem;
use PCK\TechnicalEvaluationTendererOption\TechnicalEvaluationTendererOption;
use PCK\WorkCategories\WorkCategory;

class TechnicalEvaluationSetReference extends Model {

    protected $fillable = [
        'set_id',
        'work_category_id',
        'contract_limit_id',
        'project_id',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function(self $setReference)
        {
            if( ! $setReference->set || ! $setReference->set_id ) $setReference->createEmptySet();
        });

        static::saving(function(self $setReference)
        {
            if( $setReference->set && (empty($setReference->set->name) || trim($setReference->set->name) != trim($setReference->generateSetName())))
            {
                $setReference->set->name = $setReference->generateSetName();
                $setReference->set->save();
            }
        });

        static::deleting(function(self $setReference)
        {
            if( $setReference->project_id ) throw new \Exception('This technical evaluation is linked to a project and cannot be deleted.');

            \DB::transaction(function() use ($setReference)
            {
                $setReference->deleteRelatedModels();
            });
        });
    }

    public function set()
    {
        return $this->belongsTo('PCK\TechnicalEvaluationItems\TechnicalEvaluationItem', 'set_id');
    }

    public function getCompleteSet()
    {
        $item = TechnicalEvaluationItem::where('id', $this->set_id)->first();

        $item->loadChildren();

        return $item;
    }

    public function workCategory()
    {
        return $this->belongsTo('PCK\WorkCategories\WorkCategory');
    }

    public function contractLimit()
    {
        return $this->belongsTo('PCK\ContractLimits\ContractLimit');
    }

    public function project()
    {
        return $this->belongsTo('PCK\Projects\Project');
    }

    public function attachmentListItems()
    {
        return $this->hasMany('PCK\TechnicalEvaluationAttachmentListItems\TechnicalEvaluationAttachmentListItem', 'set_reference_id')
            ->orderBy('id', 'asc');
    }

    public function responseLog()
    {
        return $this->hasMany('PCK\TechnicalEvaluationTendererOption\TechnicalEvaluationResponseLog', 'set_reference_id');
    }

    /**
     * Creates an empty set to be referred to.
     */
    private function createEmptySet()
    {
        $newSet = TechnicalEvaluationItem::create(array(
            'parent_id' => null,
            'name'      => $this->generateSetName(),
            'type'      => TechnicalEvaluationItem::TYPE_SET,
        ));

        $this->set_id = $newSet->id;
    }

    /**
     * Generates a name for the set.
     *
     * @return mixed|string
     */
    public function generateSetName()
    {
        $workCategory = WorkCategory::find($this->work_category_id);
        $newSetName = ($workCategory) ? $workCategory->name : "";

        if( $contractLimit = ContractLimit::find($this->contract_limit_id) ) $newSetName .= ' (' . $contractLimit->limit . ')';

        return $newSetName;
    }

    /**
     * Returns true if all item have an associated option for the company.
     *
     * @param Company $company
     *
     * @return bool
     */
    public function allItemsChecked(Company $company)
    {
        if( $this->isTemplate() ) return false;

        foreach($this->set->getLevel(TechnicalEvaluationItem::TYPE_ITEM) as $item)
        {
            if( ! TechnicalEvaluationTendererOption::getTendererOption($company, $item) ) return false;
        }

        return true;
    }

    /**
     * Returns true if all compulsory list items are submitted by the company.
     *
     * @param Company $company
     *
     * @return bool
     */
    public function allAttachmentsSubmitted(Company $company)
    {
        if( $this->isTemplate() ) return false;

        foreach($this->attachmentListItems as $listItem)
        {
            if( ! $listItem->compulsory ) continue;

            if( ! $listItem->attachmentSubmitted($company) ) return false;
        }

        return true;
    }

    public function getAttachmentsSubmissionTime(Company $company)
    {
        if( ! $this->allAttachmentsSubmitted($company) ) return null;

        $latestSubmissionTime = TechnicalEvaluationAttachment::where('company_id', '=', $company->id)
            ->whereIn('item_id', $this->attachmentListItems->lists('id'))->max('created_at');

        return Carbon::parse($latestSubmissionTime)->format(\Config::get('dates.created_and_updated_at_formatting'));
    }

    public function isTemplate()
    {
        return ( $this->workCategory ) ? true : false;
    }

    /**
     * Creates a copy of the list of attachment items
     * and binds it to the target Set Reference.
     *
     * @param TechnicalEvaluationSetReference $targetSetReference
     *
     * @return bool
     */
    public function copyAttachmentListTo(TechnicalEvaluationSetReference $targetSetReference)
    {
        foreach($this->attachmentListItems as $templateListItem)
        {
            $listItem = $templateListItem->replicate(array(
                'id',
                'set_reference_id',
            ));

            $listItem->set_reference_id = $targetSetReference->id;
            $listItem->save();
        }

        return true;
    }

    private function deleteRelatedModels()
    {
        $this->set->delete();

        ModelOperations::deleteWithTrigger(array(
            $this->attachmentListItems,
            $this->responseLog,
        ));
    }

}