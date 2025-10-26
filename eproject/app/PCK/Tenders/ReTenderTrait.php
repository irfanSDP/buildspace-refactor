<?php namespace PCK\Tenders;

use Carbon\Carbon;
use PCK\TenderFormVerifierLogs\FormLevelStatus;
use PCK\Users\User;

trait ReTenderTrait {

    public function allReTenderVerifiers()
    {
        return $this->belongsToMany('PCK\Users\User', 'tender_user_verifier_retender', 'tender_id');
    }

    // listing of verifiers
    public function reTenderVerifiers()
    {
        return $this->belongsToMany('PCK\Users\User', 'tender_user_verifier_retender', 'tender_id')
            ->wherePivot('status', '=', FormLevelStatus::USER_VERIFICATION_IN_PROGRESS)
            ->withTimestamps()
            ->withPivot('id')
            ->orderBy('pivot_id');
    }

    // get the latest verifier that haven't verify yet
    public function latestReTenderVerifiers()
    {
        return $this->belongsToMany('PCK\Users\User', 'tender_user_verifier_retender', 'tender_id')
            ->wherePivot('status', '=', FormLevelStatus::USER_VERIFICATION_IN_PROGRESS)
            ->withTimestamps()
            ->withPivot('id')
            ->orderBy('pivot_id')
            ->limit(1);
    }

    public function reTenderVerifierLogs()
    {
        return $this->morphMany('PCK\TenderFormVerifierLogs\TenderFormVerifierLog', 'loggable')
            ->orderBy('id', 'ASC');
    }

    public function requestRetenderBy()
    {
        return $this->belongsTo(User::class, 'request_retender_by');
    }

    public function stillInProgress()
    {
        return $this->retender_verification_status === FormLevelStatus::IN_PROGRESS;
    }

    public function isBeingValidated()
    {
        return $this->retender_verification_status === FormLevelStatus::NEED_VALIDATION;
    }

    public function isSubmitted()
    {
        return $this->retender_verification_status === FormLevelStatus::SUBMISSION;
    }

    public function hasBeenReTender()
    {
        return $this->retender_status;
    }
    
    public function getTenderResubmissionModuleName()
    {
        return trans('tenders.tenderResubmission');
    }

    public static function getPendingTenderResubmissionDaysPending(Tender $tender, User $user)
    {
        $isCurrentUserFirstVerifier = ($tender->reTenderVerifiers->first()->id === $user->id);

        if($isCurrentUserFirstVerifier)
        {
            $then = Carbon::parse($tender->updated_at);
        }
        else
        {
            $then = Carbon::parse($tender->reTenderVerifierLogs->first()->updated_at);
        }

        return $then->diffInDays(Carbon::now()); 
    }
}