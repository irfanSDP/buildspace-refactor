<?php namespace PCK\TenderCallingTenderInformation;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use PCK\Base\TimestampFormatterTrait;
use PCK\Helpers\ModelOperations;
use PCK\TenderFormVerifierLogs\FormLevelStatus as TenderFormLevelStatus;
use PCK\Users\User;
use PCK\TenderRecommendationOfTendererInformation\ContractorCommitmentStatus;

class TenderCallingTenderInformation extends Model implements TenderFormLevelStatus {

    use TimestampFormatterTrait;

    const CALLING_TENDER_MODULE_NAME = 'Calling Tender';

    protected $table = 'tender_calling_tender_information';

    protected $with = array( 'verifiers' );

    protected $fillable = ["status"];

    protected static function boot()
    {
        parent::boot();

        static::deleting(function (self $tenderCallingTenderInformation)
        {
            $tenderCallingTenderInformation->deleteRelatedModels();
        });
    }

    public function tender()
    {
        return $this->belongsTo('PCK\Tenders\Tender');
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
        return $this->belongsToMany('PCK\Companies\Company', 'company_tender_calling_tender_information', 'tender_calling_tender_information_id')
            ->with('contractor')
            ->orderBy('name', 'ASC')
            ->withPivot('status')
            ->withTimestamps();
    }

    public function selectedConfirmedContractors()
    {
        return $this->belongsToMany('PCK\Companies\Company', 'company_tender_calling_tender_information', 'tender_calling_tender_information_id')
            ->where('status', ContractorCommitmentStatus::TENDER_OK)
            ->with('contractor')
            ->orderBy('name', 'ASC')
            ->withPivot('status')
            ->withTimestamps();
    }

    /**
     * List all verifiers, past and present.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function allVerifiers()
    {
        return $this->belongsToMany('PCK\Users\User', 'tender_calling_tender_information_user', 'tender_calling_tender_information_id');
    }

    /**
     * List current verifiers.
     *
     * @return mixed
     */
    public function verifiers()
    {
        return $this->belongsToMany('PCK\Users\User', 'tender_calling_tender_information_user', 'tender_calling_tender_information_id')
            ->wherePivot('status', '=', self::USER_VERIFICATION_IN_PROGRESS)
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
        return $this->belongsToMany('PCK\Users\User', 'tender_calling_tender_information_user', 'tender_calling_tender_information_id')
            ->wherePivot('status', '=', self::USER_VERIFICATION_IN_PROGRESS)
            ->withTimestamps()
            ->withPivot('id')
            ->orderBy('pivot_id')
            ->limit(1);
    }

    public function currentBatchVerifiers()
    {
        return $this->belongsToMany('PCK\Users\User', 'tender_calling_tender_information_user', 'tender_calling_tender_information_id')
            ->wherePivot('status', '!=', self::USER_VERIFICATION_REJECTED)
            ->withTimestamps()
            ->withPivot('id')
            ->orderBy('pivot_id');
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

    public function stillInProgress()
    {
        return $this->status === self::IN_PROGRESS;
    }

    public function extendingDateInProgress()
    {
        return $this->status === self::EXTEND_DATE_VALIDATION_IN_PROGRESS;
    }

    public function extendingDateAllowed()
    {
        return $this->status === self::EXTEND_DATE_VALIDATION_ALLOWED;
    }

    public function isBeingValidated()
    {
        return in_array($this->status, array(
            self::NEED_VALIDATION,
            self::EXTEND_DATE_VALIDATION_IN_PROGRESS,
        ));
    }

    public function isSubmitted()
    {
        return in_array($this->status, array(
            self::SUBMISSION,
            self::EXTEND_DATE_VALIDATION_ALLOWED,
        ));
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
        \DB::table('tender_calling_tender_information_user')
            ->where('tender_calling_tender_information_id', '=', $this->id)
            ->whereIn('status', [self::USER_VERIFICATION_IN_PROGRESS, self::USER_VERIFICATION_CONFIRMED])
            ->update(array( 'status' => self::USER_VERIFICATION_REJECTED ));

        $this->load('verifiers');

        // cater for extend deadline verification due to the form has been submitted before
        // proceeding with extending datelines
        if( $this->extendingDateInProgress() )
        {
            $this->status = self::SUBMISSION;
        }
        else
        {
            $this->status = self::IN_PROGRESS;
        }

        $this->save();
    }

    public function allowContractorProposeOwnCompletionPeriod()
    {
        return $this->allow_contractor_propose_own_completion_period;
    }

    public function allowEditableContractorStatus(User $user)
    {
        $project = $this->tender->project;

        if(!$user->isEditor($project) || !$user->hasCompanyProjectRole($project, $project->getCallingTenderRole()))
        {
            return false;
        }

        $latestTender = $project->latestTender;

        if($latestTender->id != $this->tender_id)
        {
            return false;
        }
        
        return true;
    }
}