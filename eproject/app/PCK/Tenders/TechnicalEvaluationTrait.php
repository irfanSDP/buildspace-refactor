<?php namespace PCK\Tenders;

use Carbon\Carbon;
use PCK\Helpers\ModelOperations;
use PCK\TenderFormVerifierLogs\FormLevelStatus;
use PCK\Verifier\Verifier;

trait TechnicalEvaluationTrait {

    public function allTechnicalEvaluationVerifiers()
    {
        return $this->belongsToMany('PCK\Users\User', 'tender_user_technical_evaluation_verifier', 'tender_id')
            ->withTimestamps();
    }

    public function technicalEvaluationVerifiersApproved()
    {
        return $this->belongsToMany('PCK\Users\User', 'tender_user_technical_evaluation_verifier', 'tender_id')
            ->wherePivot('status', '=', FormLevelStatus::USER_VERIFICATION_CONFIRMED);
    }

    public function technicalEvaluationVerifiers()
    {
        return $this->belongsToMany('PCK\Users\User', 'tender_user_technical_evaluation_verifier', 'tender_id')
            ->wherePivot('status', '=', FormLevelStatus::USER_VERIFICATION_IN_PROGRESS)
            ->withTimestamps();
    }

    public function technicalEvaluationVerifierLogs()
    {
        return $this->hasMany('PCK\TechnicalEvaluationVerifierLogs\TechnicalEvaluationVerifierLog')
            ->orderBy('id', 'ASC');
    }

    public function tendererTechnicalEvaluationInformation()
    {
        return $this->hasMany('PCK\TendererTechnicalEvaluationInformation\TendererTechnicalEvaluationInformation', 'tender_id');
    }

    public function technicalEvaluation() {
        return $this->hasOne('PCK\TendererTechnicalEvaluationInformation\TechnicalEvaluation');
    }

    public function configuredToHaveTechnicalEvaluation()
    {
        $this->load('listOfTendererInformation');

        return $this->listOfTendererInformation->technical_evaluation_required ?? false;
    }

    public function getTechnicalEvaluationStatusTextAttribute()
    {
        $text = self::OPEN_TENDER_STATUS_NOT_YET_OPEN_TEXT;

        if( $this->technicalEvaluationIsSubmitted() )
        {
            $text = self::OPEN_TENDER_STATUS_OPENED_TEXT;
        }

        return $text;
    }

    public function technicalEvaluationIsOpen()
    {
        if( ! $this->configuredToHaveTechnicalEvaluation() ) return false;

        if( ! Carbon::now()->lte(Carbon::parse($this->technical_tender_closing_date)) ) return false;

        if( $this->technicalEvaluation )
        {
            if( Verifier::isApproved($this->technicalEvaluation) || Verifier::isBeingVerified($this->technicalEvaluation) ) return false;
        }

        return true;
    }

    private function deleteTechnicalEvaluationVerifierRecords()
    {
        return \DB::table('tender_user_technical_evaluation_verifier')->where('tender_id', '=', $this->id)->delete();
    }

    private function deleteTechnicalEvaluationData()
    {
        ModelOperations::deleteWithTrigger(array(
            $this->technicalEvaluationVerifierLogs,
            $this->tendererTechnicalEvaluationInformation,
        ));

        $this->deleteTechnicalEvaluationVerifierRecords();
    }

}