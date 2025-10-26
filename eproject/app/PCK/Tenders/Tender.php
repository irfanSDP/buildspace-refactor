<?php namespace PCK\Tenders;

use Carbon\Carbon;
use PCK\Helpers\ModelOperations;
use PCK\Projects\Project;
use PCK\Companies\Company;
use PCK\Base\TimestampFormatterTrait;
use Illuminate\Database\Eloquent\Model;
use PCK\TenderFormVerifierLogs\FormLevelStatus;
use PCK\TenderInterviews\TenderInterview;

class Tender extends Model implements TenderStatuses, FormLevelStatus {

    use TimestampFormatterTrait, Roles, ReTenderTrait, OpenTenderTrait, SyncToBuildSpaceQueueChecker, TechnicalEvaluationTrait;

    const QUEUE_SYNC_TO_BS_TUBE_NAME = 'sync_to_buildspace';

    const OPEN_TENDER_MODULE_NAME = 'Open Tender';

    protected $appends = array( 'current_tender_name', 'current_form_type_name', 'open_tender_status_text', 'open_tender_verifying_status', 'technical_evaluation_verifying_status' );

    protected static function boot()
    {
        parent::boot();

        static::created(function(self $tender)
        {
            $tender = self::find($tender->id);

            // for tender resubmissions
            if(!$tender->isFirstTender())
            {
                $formOfTenderRepository = \App::make('PCK\FormOfTender\FormOfTenderRepository');
                $formOfTenderRepository->createNewResources($tender);
            }
            
            $previousTender = self::where('project_id', '=', $tender->project->id)
                ->where('count', '=', ( $tender->count - 1 ))
                ->first();

            if( $previousTender ) TenderInterview::where('tender_id', '=', $previousTender->id)->update(array( 'key' => null ));
        });

        static::deleting(function(self $tender)
        {
            \DB::transaction(function() use ($tender)
            {
                $tender->deleteRelatedModels();
            });
        });
    }

    public function companies()
    {
        return $this->belongsToMany('PCK\Companies\Company');
    }

    public function project()
    {
        return $this->belongsTo('PCK\Projects\Project');
    }

    public function recommendationOfTendererInformation()
    {
        return $this->hasOne('PCK\TenderRecommendationOfTendererInformation\TenderRecommendationOfTendererInformation');
    }

    public function listOfTendererInformation()
    {
        return $this->hasOne('PCK\TenderListOfTendererInformation\TenderListOfTendererInformation');
    }

    public function callingTenderInformation()
    {
        return $this->hasOne('PCK\TenderCallingTenderInformation\TenderCallingTenderInformation');
    }

    public function openTenderPageInformation()
    {
        return $this->hasOne('PCK\Tenders\OpenTenderPageInformation');
    }

    public function openTenderTenderRequirement()
    {
        return $this->hasOne('PCK\Tenders\OpenTenderTenderRequirement');
    }

    public function openTenderPersonInCharges()
    {
        return $this->hasMany('PCK\Tenders\OpenTenderPersonInCharge');
    }

    public function openTenderAnnouncements()
    {
        return $this->hasMany('PCK\Tenders\OpenTenderAnnouncement');
    }

    public function openTenderTenderDocuments()
    {
        return $this->hasMany('PCK\Tenders\OpenTenderTenderDocument');
    }

    public function openTenderIndustryCodes()
    {
        return $this->hasMany('PCK\Tenders\OpenTenderIndustryCode');
    }

    public function tenderReminder()
    {
        $tenderStage = TenderStages::TENDER_STAGE_RECOMMENDATION_OF_TENDERER;

        if( $this->getTenderStage() == TenderStages::TENDER_STAGE_CALLING_TENDER ) $tenderStage = TenderStages::TENDER_STAGE_CALLING_TENDER;

        return $this->hasOne('PCK\TenderReminder\TenderReminder')->where('tender_stage', '=', $tenderStage);
    }

    public function sentTenderRemindersLog()
    {
        return $this->hasMany('PCK\TenderReminder\SentTenderRemindersLog', 'tender_id');
    }

    public function tenderInterviewInfo()
    {
        return $this->hasOne('PCK\TenderInterviews\TenderInterviewInformation');
    }

    public function acknowledgementLetter()
    {
        return $this->hasOne('PCK\Tenders\AcknowledgementLetter');
    }

    public function createdBy()
    {
        return $this->belongsTo('PCK\Users\User', 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo('PCK\Users\User', 'updated_by');
    }

    public function selectedFinalContractors()
    {
        return $this->belongsToMany('PCK\Companies\Company', 'company_tender', 'tender_id')
            ->orderBy('name', 'ASC')
            ->withPivot('id', 'rates', 'tender_amount', 'completion_period', 'submitted', 'submitted_at', 'selected_contractor', 'supply_of_material_amount', 'other_bill_type_amount_except_prime_cost_provisional', 'contractor_adjustment_percentage', 'contractor_adjustment_amount', 'original_tender_amount', 'discounted_percentage', 'discounted_amount', 'earnest_money', 'remarks')
            ->withTimestamps();
    }

    public function selectedFinalContractorsWithProjects()
    {
        return $this->belongsToMany('PCK\Companies\Company', 'company_tender', 'tender_id')
            ->orderBy('name', 'ASC')
            ->with('ongoingProjects', 'completedProjects', 'latestParticipatedTenders')
            ->withPivot('id', 'rates', 'tender_amount', 'completion_period', 'submitted', 'submitted_at', 'selected_contractor', 'supply_of_material_amount', 'other_bill_type_amount_except_prime_cost_provisional', 'contractor_adjustment_percentage', 'contractor_adjustment_amount', 'original_tender_amount', 'discounted_percentage', 'discounted_amount', 'earnest_money', 'remarks')
            ->withTimestamps();
    }

    public function submittedTenderRateContractors()
    {
        return $this->belongsToMany('PCK\Companies\Company', 'company_tender', 'tender_id')
            ->orderBy('name', 'ASC')
            ->wherePivot('submitted', '=', true)
            ->withPivot('id', 'rates', 'tender_amount', 'completion_period', 'submitted', 'submitted_at', 'selected_contractor', 'supply_of_material_amount', 'other_bill_type_amount_except_prime_cost_provisional', 'contractor_adjustment_percentage', 'contractor_adjustment_amount', 'original_tender_amount', 'discounted_percentage', 'discounted_amount', 'earnest_money', 'remarks')
            ->withTimestamps();
    }

    public function isFirstTender()
    {
        return $this->count === 0;
    }

    public function allowedReTender()
    {
        if( $this->openTenderAwardRecommendtion && \PCK\Verifier\Verifier::isBeingVerified($this->openTenderAwardRecommendtion)) return false;

        return in_array($this->current_form_type, array(
            Project::STATUS_TYPE_CALLING_TENDER,
            Project::STATUS_TYPE_CLOSED_TENDER
        ));
    }

    public function getCurrentTenderNameByLocale($locale)
    {
        if($this->isFirstTender())
        {
            return trans('tenders.tender', [], 'messages', $locale);
        }

        return trans('tenders.tenderResubmission:count', ['count' => $this->count], 'messages', $locale);
    }

    public function getCurrentTenderNameAttribute()
    {
        if( $this->isFirstTender() )
        {
            return trans('tenders.tender');
        }

        return trans('tenders.tenderResubmission:count', array( 'count' => $this->count ));
    }

    public function isRevisedTender()
    {
        return ( ! $this->isFirstTender() );
    }

    public function getCurrentFormTypeNameAttribute()
    {
        return Project::getStatusById($this->current_form_type);
    }

    public function getTenderStartingDateAttribute($value)
    {
        if( $value ) return Carbon::parse($value)->format(\Config::get('dates.created_and_updated_at_formatting'));
    }

    public function getTenderClosingDateAttribute($value)
    {
        if( $value ) return Carbon::parse($value)->format(\Config::get('dates.created_and_updated_at_formatting'));
    }

    public function getTechnicalTenderClosingDateAttribute($value)
    {
        if( $value ) return Carbon::parse($value)->format(\Config::get('dates.created_and_updated_at_formatting'));
    }

    // allow the Pivot table to again access to add attachments
    public function newPivot(Model $parent, array $attributes, $table, $exists)
    {
        if( $parent instanceof Company )
        {
            return new SubmitTenderRate($parent, $attributes, $table, $exists);
        }

        return parent::newPivot($parent, $attributes, $table, $exists);
    }

    public function verifierLogs()
    {
        return $this->morphMany('PCK\TenderFormVerifierLogs\TenderFormVerifierLog', 'loggable')
            ->orderBy('id', 'ASC');
    }

    public function formOfTender()
    {
        return $this->hasOne('PCK\FormOfTender\FormOfTender');
    }

    /**
     * Delete related records.
     */
    protected function deleteRelatedModels()
    {
        if( $this->recommendationOfTendererInformation )
        {
            $this->recommendationOfTendererInformation->delete();
        }

        if( $this->listOfTendererInformation )
        {
            $this->listOfTendererInformation->delete();
        }

        if( $this->callingTenderInformation )
        {
            $this->callingTenderInformation->delete();
        }

        $this->companies()->detach();

        ModelOperations::deleteWithTrigger(array(
            $this->verifierLogs,
            $this->openTenderVerifierLogs,
            $this->reTenderVerifierLogs,
            $this->sentTenderRemindersLog,
            $this->tenderReminder,
        ));

        $this->deleteTechnicalEvaluationData();

        $this->allOpenTenderVerifiers()->detach();

        $this->allReTenderVerifiers()->detach();

        if( isset( $this->formOfTender ) || ( $this->formOfTender()->count() > 0 ) )
        {
            $this->formOfTender->delete();
        }
    }

    /**
     * Returns the date the tender is valid until.
     *
     * @return Carbon
     */
    public function validUntil()
    {
        $numberOfDays = isset( $this->validity_period_in_days ) ? $this->validity_period_in_days : 0;
        $carbonObject = Carbon::parse($this->tender_closing_date);
        $carbonObject->addDays($numberOfDays);

        return $carbonObject;
    }

    /**
     * Returns the stage of the tender.
     *
     * @return bool|int
     */
    public function getTenderStage()
    {
        if( $this->callingTenderInformation )
        {
            return TenderStages::TENDER_STAGE_CALLING_TENDER;
        }
        elseif( $this->listOfTendererInformation )
        {
            return TenderStages::TENDER_STAGE_LIST_OF_TENDERER;
        }
        elseif( $this->recommendationOfTendererInformation )
        {
            return TenderStages::TENDER_STAGE_RECOMMENDATION_OF_TENDERER;
        }
        else
        {
            return TenderStages::INVALID;
        }
    }

    /**
     * Returns the latest tender stage information.
     *
     * @return mixed
     * @throws \Exception
     */
    public function getTenderStageInformation()
    {
        switch($this->getTenderStage())
        {
            case TenderStages::TENDER_STAGE_CALLING_TENDER:
                return $this->callingTenderInformation;
            case TenderStages::TENDER_STAGE_LIST_OF_TENDERER:
                return $this->listOfTendererInformation;
            case TenderStages::TENDER_STAGE_RECOMMENDATION_OF_TENDERER:
                return $this->recommendationOfTendererInformation;
            default:
                return null;
        }
    }

    public function getTenderInformationClosingTime()
    {
        switch($this->getTenderStage())
        {
            case TenderStages::TENDER_STAGE_CALLING_TENDER:
                return $this->callingTenderInformation->date_of_closing_tender;
            case TenderStages::TENDER_STAGE_LIST_OF_TENDERER:
                return $this->listOfTendererInformation->date_of_closing_tender;
            case TenderStages::TENDER_STAGE_RECOMMENDATION_OF_TENDERER:
                return $this->recommendationOfTendererInformation->proposed_date_of_closing_tender;
            default:
                return null;
        }
    }

    public function getTenderInformationCallingTime()
    {
        switch($this->getTenderStage())
        {
            case TenderStages::TENDER_STAGE_CALLING_TENDER:
                return $this->callingTenderInformation->date_of_calling_tender;
            case TenderStages::TENDER_STAGE_LIST_OF_TENDERER:
                return $this->listOfTendererInformation->date_of_calling_tender;
            case TenderStages::TENDER_STAGE_RECOMMENDATION_OF_TENDERER:
                return $this->recommendationOfTendererInformation->proposed_date_of_calling_tender;
            default:
                return null;
        }
    }

    public function hasClosed()
    {
        return ( Carbon::now() > Carbon::parse($this->tender_closing_date) );
    }

    public function technicalEvaluationStarted()
    {
        return Carbon::now()->gte(Carbon::parse($this->tender_starting_date));
    }

    public function technicalEvaluationEnded()
    {
        return Carbon::now()->gte(Carbon::parse($this->technical_tender_closing_date));
    }

    public function rejectOpenTenderVerification()
    {
        \DB::table('tender_user_verifier_open_tender')
            ->where('tender_id', '=', $this->id)
            ->whereIn('status', [self::USER_VERIFICATION_IN_PROGRESS, self::USER_VERIFICATION_CONFIRMED])
            ->update(array( 'status' => self::USER_VERIFICATION_REJECTED ));
        
        $this->open_tender_verification_status = self::IN_PROGRESS;

        $this->save();
    }

    public function rejectTechnicalEvaluationVerification()
    {
        \DB::table('tender_user_technical_evaluation_verifier')
            ->where('tender_id', '=', $this->id)
            ->whereIn('status', [self::USER_VERIFICATION_IN_PROGRESS, self::USER_VERIFICATION_CONFIRMED])
            ->update(array( 'status' => self::USER_VERIFICATION_REJECTED ));
        
        $this->technical_evaluation_verification_status = self::IN_PROGRESS;

        $this->save();
    }
}