<?php namespace PCK\Projects;

use Carbon\Carbon;
use PCK\TechnicalEvaluationSetReferences\TechnicalEvaluationSetReference;
use PCK\Tenders\Tender;

trait ProjectScopesTrait {

    public function inRecommendationOfTenderer()
    {
        return $this->status_id === Project::STATUS_TYPE_RECOMMENDATION_OF_TENDERER;
    }

    public function inListOfTenderer()
    {
        return $this->status_id === Project::STATUS_TYPE_LIST_OF_TENDERER;
    }

    public function inClosedTender()
    {
        return $this->status_id === Project::STATUS_TYPE_CLOSED_TENDER;
    }

    public function inCallingTender()
    {
        return $this->status_id === Project::STATUS_TYPE_CALLING_TENDER;
    }

    public function isCompleted()
    {
        return $this->status_id === Project::STATUS_TYPE_COMPLETED;
    }

    public function inEBidding()
    {
        return $this->status_id === Project::STATUS_TYPE_E_BIDDING;
    }

    public function isDesignStage()
    {
        return $this->status_id === Project::STATUS_TYPE_DESIGN;
    }

    public function isCurrentTenderStatusClosed()
    {
        return $this->status_id === Project::STATUS_TYPE_CLOSED_TENDER;
    }

    // Switch from Closed Tender to E-Bidding status
    public function changeToEBidding()
    {
        // Check if the current status is Closed Tender
        if ($this->status_id === Project::STATUS_TYPE_CLOSED_TENDER) {
            // Update the status to E-Bidding
            $this->status_id = Project::STATUS_TYPE_E_BIDDING;
            return true;
        }
        return false;
    }

    // Switch from E-Bidding to Closed Tender status
    public function changeToClosedTender()
    {
        // Check if the current status is E-Bidding
        if ($this->status_id === Project::STATUS_TYPE_E_BIDDING) {
            // Update the status to Closed Tender
            $this->status_id = Project::STATUS_TYPE_CLOSED_TENDER;
            return true;
        }
        return false;
    }

    public static function getDefaultStatusId()
    {
        return self::STATUS_TYPE_DESIGN;
    }

    public static function getDefaultStatusText()
    {
        return self::STATUS_TYPE_DESIGN_TEXT;
    }

    public static function getStatusById($id)
    {
        switch($id)
        {
            case self::STATUS_TYPE_DESIGN:
                return trans('projects.design');
            case self::STATUS_TYPE_POST_CONTRACT:
                return trans('projects.postContract');
            case self::STATUS_TYPE_COMPLETED:
                return trans('projects.completed');
            case self::STATUS_TYPE_RECOMMENDATION_OF_TENDERER:
                return trans('projects.recommendationOfTenderer');
            case self::STATUS_TYPE_LIST_OF_TENDERER:
                return trans('projects.listofTenderer');
            case self::STATUS_TYPE_CALLING_TENDER:
                return trans('projects.callingTender');
            case self::STATUS_TYPE_CLOSED_TENDER:
                return trans('projects.closedTender');
            case self::STATUS_TYPE_E_BIDDING:
                return trans('projects.eBidding');
        }
    }

    public static function tenderingStagesStatus()
    {
        return array(
            self::STATUS_TYPE_RECOMMENDATION_OF_TENDERER,
            self::STATUS_TYPE_LIST_OF_TENDERER,
            self::STATUS_TYPE_CALLING_TENDER,
            self::STATUS_TYPE_CLOSED_TENDER,
        );
    }

    public function onLastTenderingStage()
    {
        return in_array($this->status_id, array(
            self::STATUS_TYPE_CALLING_TENDER,
            self::STATUS_TYPE_CLOSED_TENDER,
        ));
    }

    public function onPostContractStages()
    {
        return in_array($this->status_id, array(
            self::STATUS_TYPE_POST_CONTRACT,
            self::STATUS_TYPE_COMPLETED
        ));
    }

    public function isPostContract()
    {
        return $this->status_id === self::STATUS_TYPE_POST_CONTRACT;
    }

    public static function generateContractNumberSuffix()
    {
        $now  = Carbon::now();
        $year = substr($now->year, 2);

        return "{$year}";
    }

    public function hasTechnicalEvaluation()
    {
        $technicalEvaluation = TechnicalEvaluationSetReference::where('project_id', '=', $this->id)->first();

        return ( ! empty( $technicalEvaluation ) );
    }

    public function technicalEvaluationEnabled()
    {
        $tenders = Tender::where("project_id", $this->id)->get();

        foreach($tenders as $tender)
        {
            if($tender->listOfTendererInformation->technical_evaluation_required)
            {
                return true;
            }
        }

        return false;
    }

    public function showTechnicalEvaluationDetails(Tender $tender)
    {
        if( ! $this->hasTechnicalEvaluation() ) return false;

        // Check if the tender belongs to the project.
        if( $tender->project_id != $this->id ) return false;

        return ( $tender->technicalEvaluationIsSubmitted() ) ? true : false;
    }

    public function showOpenTender()
    {
        return ( $this->latestTender->callingTenderInformation && $this->latestTender->callingTenderInformation->isSubmitted() );
    }

}