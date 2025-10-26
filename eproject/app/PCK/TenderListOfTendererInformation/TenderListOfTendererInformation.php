<?php namespace PCK\TenderListOfTendererInformation;

use App;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use PCK\Base\TimestampFormatterTrait;
use PCK\Helpers\ModelOperations;
use PCK\TenderFormVerifierLogs\FormLevelStatus as TenderFormLevelStatus;
use PCK\Tenders\Tender;

use PCK\TechnicalEvaluationItems\TechnicalEvaluationItem;
use PCK\TechnicalEvaluationTendererOption\TechnicalEvaluationTendererOption;

class TenderListOfTendererInformation extends Model implements TenderFormLevelStatus {

    use TimestampFormatterTrait;

    const MODAL_ID = 'lotContractorsList';

    const LIST_OF_TENDERER_MODULE_NAME = 'List of Tenderer';

    protected $table = 'tender_lot_information';

    protected $with = array( 'verifiers' );

    protected $fillable = ["status"];

    protected static function boot()
    {
        parent::boot();

        static::deleting(function(self $tenderListOfTendererInformation)
        {
            $tenderListOfTendererInformation->deleteRelatedModels();
        });

        static::created(function(self $object)
        {
            $contractors = $object->selectedContractors->reject(function($contractor)
            {
                return ( $contractor->pivot->deleted_at );
            });

            $tenderInterviewRepository = App::make('PCK\TenderInterviews\TenderInterviewRepository');

            foreach($contractors as $contractor)
            {
                // Ensure that records exist.
                $tenderInterviewRepository->findByCompanyAndTenderIdOrNew($contractor->id, $object->tender->id);
            }
        });
    }

    public function tender()
    {
        return $this->belongsTo('PCK\Tenders\Tender');
    }

    public function procurementMethod()
    {
        return $this->belongsTo('PCK\ProcurementMethod\ProcurementMethod');
    }

    public function createdBy()
    {
        return $this->belongsTo('PCK\Users\User', 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo('PCK\Users\User', 'updated_by');
    }

    public function selectedContractors()
    {
        return $this->belongsToMany('PCK\Companies\Company', 'company_tender_lot_information', 'tender_lot_information_id')
            ->with('contractor')
            ->orderBy('name', 'ASC')
            ->withPivot('remarks', 'deleted_at', 'added_by_gcd', 'status')
            ->withTimestamps();
    }

    public function selectedContractorsAddedByGCD()
    {
        return $this->belongsToMany('PCK\Companies\Company', 'company_tender_lot_information', 'tender_lot_information_id')
            ->wherePivot('added_by_gcd', true);
    }

    public function checkSelectedContractorStatusInListOfTendererInformation($commpanyId, $status)
    {
        return $this->selectedContractors()->where("status", $status)
                                            ->where("company_id", $commpanyId)
                                            ->first();
    }

    /**
     * List all verifiers, past and present.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function allVerifiers()
    {
        return $this->belongsToMany('PCK\Users\User', 'tender_lot_information_user', 'tender_lot_information_id');
    }

    /**
     * List current verifiers.
     *
     * @return mixed
     */
    public function verifiers()
    {
        return $this->belongsToMany('PCK\Users\User', 'tender_lot_information_user', 'tender_lot_information_id')
            ->wherePivot('status', '=', self::USER_VERIFICATION_IN_PROGRESS)
            ->withTimestamps()
            ->withPivot('id')
            ->orderBy('pivot_id');
    }

    public function currentBatchVerifiers()
    {
        return $this->belongsToMany('PCK\Users\User', 'tender_lot_information_user', 'tender_lot_information_id')
            ->wherePivot('status', '!=', self::USER_VERIFICATION_REJECTED)
            ->withTimestamps()
            ->withPivot('id')
            ->orderBy('pivot_id');
    }

    /**
     * Returns the latest verifier that has not yet verified.
     *
     * @return mixed
     */
    public function latestVerifier()
    {
        return $this->belongsToMany('PCK\Users\User', 'tender_lot_information_user', 'tender_lot_information_id')
            ->wherePivot('status', '=', self::USER_VERIFICATION_IN_PROGRESS)
            ->withTimestamps()
            ->withPivot('id')
            ->orderBy('pivot_id')
            ->limit(1);
    }

    public function verifierLogs()
    {
        return $this->morphMany('PCK\TenderFormVerifierLogs\TenderFormVerifierLog', 'loggable')
            ->orderBy('id', 'ASC');
    }

    public function latestVerifierLog()
    {
        return $this->morphMany('PCK\TenderFormVerifierLogs\TenderFormVerifierLog', 'loggable')
            ->orderBy('updated_at', 'DESC')
            ->limit(1);
    }

    public function contractLimit()
    {
        return $this->belongsTo('PCK\ContractLimits\ContractLimit');
    }

    public function getCompletionPeriodAttribute($value)
    {
        return $value + 0;
    }

    public function getDateOfCallingTenderAttribute($value)
    {
        return Carbon::parse($value)->format(\Config::get('dates.created_and_updated_at_formatting'));
    }

    public function getDateOfClosingTenderAttribute($value)
    {
        return Carbon::parse($value)->format(\Config::get('dates.created_and_updated_at_formatting'));
    }

    public function getTechnicalTenderClosingDateAttribute($value)
    {
        return Carbon::parse($value)->format(\Config::get('dates.created_and_updated_at_formatting'));
    }

    public function getProjectIncentivePercentageAttribute($value)
    {
        if( is_null($value) ) return 0;

        return $value;
    }

    public function stillInProgress()
    {
        return $this->status === self::IN_PROGRESS;
    }

    public function isBeingValidated()
    {
        return $this->status === self::NEED_VALIDATION;
    }

    public function isSubmitted()
    {
        return $this->status === self::SUBMISSION;
    }

    public function isTechnicalEvaluationReadOnly()
    {
        if(!$this->allowCopyTechnicalEvaluationSetReferences())
        {
            return true;
        }

        $tender = $this->tender;

        return ( $tender->listOfTendererInformation->isBeingValidated() || $tender->listOfTendererInformation->isSubmitted() );
    }

    public function allowCopyTechnicalEvaluationSetReferences()
    {
        $tender = $this->tender;
        $project = $tender->project;

        $hasResubmissionTenders = Tender::where('project_id', $project->id)
            ->where('id', '<>', $tender->id)
            ->where('count', '>', $tender->count)
            ->orderBy('count', 'DESC')
            ->get();
        
        //only allows for the current tender
        if($hasResubmissionTenders->count())
        {
            return false;
        }

        if( $this->technical_evaluation_required )
        {
            $setReferenceRepository = \App::make('PCK\TechnicalEvaluationSetReferences\TechnicalEvaluationSetReferenceRepository');
            $setReference           = $setReferenceRepository->getSetReferenceByProject($project);

            /* validates any submitted list or attachment for the current tender.
             * LOT will become read-only is there is any submitted list or attachment from ANY contractor
             */
            if($setReference)
            {
                foreach($this->selectedContractors as $selectedContractor)
                {
                    foreach($setReference->set->getLevel(TechnicalEvaluationItem::TYPE_ITEM) as $item)
                    {
                        if(TechnicalEvaluationTendererOption::getTendererOption($selectedContractor, $item)) return false;
                    }

                    foreach($setReference->attachmentListItems as $listItem)
                    {
                        //any item either compulsory or not..if there is an attachment to it then LOT is considered read-only
                        if($listItem->attachmentSubmitted($selectedContractor) ) return false;
                    }
                }

                /* If it passes th first validation then the second validation validates any submitted list or attachment for the previous tender.
                 * LOT will become read-only is there is any submitted list or attachment from ANY contractor
                 */
                $previousTenders = Tender::where('project_id', $project->id)
                ->where('id', '<>', $tender->id)
                ->where('count', $tender->count - 1)
                ->orderBy('count', 'DESC')
                ->get();

                if($previousTenders)
                {
                    foreach($previousTenders as $previousTender)
                    {
                        $previousListOfTendererInformation = $previousTender->listOfTendererInformation;

                        foreach($previousListOfTendererInformation->selectedContractors as $selectedContractor)
                        {
                            foreach($setReference->set->getLevel(TechnicalEvaluationItem::TYPE_ITEM) as $item)
                            {
                                if(TechnicalEvaluationTendererOption::getTendererOption($selectedContractor, $item)) return false;
                            }

                            foreach($setReference->attachmentListItems as $listItem)
                            {
                                //any item either compulsory or not..if there is an attachment to it then LOT is considered read-only
                                if($listItem->attachmentSubmitted($selectedContractor) ) return false;
                            }
                        }
                    }
                }
            }
        }

        return true;
    }

    /**
     * Delete related records.
     */
    protected function deleteRelatedModels()
    {
        $this->selectedContractors()->detach();

        $this->allVerifiers()->detach();

        ModelOperations::deleteWithTrigger(array(
            $this->verifierLogs
        ));
    }

    public function rejectVerification()
    {
        \DB::table('tender_lot_information_user')
            ->where('tender_lot_information_id', '=', $this->id)
            ->whereIn('status', [self::USER_VERIFICATION_IN_PROGRESS, self::USER_VERIFICATION_CONFIRMED])
            ->update(array( 'status' => self::USER_VERIFICATION_REJECTED ));

        $this->load('verifiers');

        $this->status = self::IN_PROGRESS;

        if( \PCK\Forum\ObjectThread::objectHasThread($this) )
        {
            $thread = \PCK\Forum\ObjectThread::getObjectThread($this);
            $thread->users()->sync(array());
        }

        $this->save();
    }
}