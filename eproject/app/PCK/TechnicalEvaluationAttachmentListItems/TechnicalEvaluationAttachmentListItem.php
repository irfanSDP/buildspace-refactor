<?php namespace PCK\TechnicalEvaluationAttachmentListItems;

use Illuminate\Database\Eloquent\Model;
use PCK\Companies\Company;
use PCK\Helpers\ModelOperations;
use PCK\TechnicalEvaluationAttachments\TechnicalEvaluationAttachment;

class TechnicalEvaluationAttachmentListItem extends Model {

    protected $fillable = [];

    protected static function boot()
    {
        parent::boot();

        static::deleting(function(self $listItem)
        {
            \DB::transaction(function() use ($listItem)
            {
                $listItem->deleteRelatedModels();
            });
        });
    }

    public function setReference()
    {
        return $this->belongsTo('PCK\TechnicalEvaluationSetReferences\TechnicalEvaluationSetReference');
    }

    public function attachments()
    {
        return $this->hasMany('PCK\TechnicalEvaluationAttachments\TechnicalEvaluationAttachment', 'item_id');
    }

    public function getCompanyAttachment(Company $company)
    {
        return TechnicalEvaluationAttachment::where('item_id', '=', $this->id)
            ->where('company_id', '=', $company->id)
            ->first();
    }

    /**
     * Returns true if the company has submitted an attachment for this list item.
     *
     * @param Company $company
     *
     * @return bool
     */
    public function attachmentSubmitted(Company $company)
    {
        return $this->getCompanyAttachment($company) ? true : false;
    }

    private function deleteRelatedModels()
    {
        ModelOperations::deleteWithTrigger($this->attachments);
    }

}