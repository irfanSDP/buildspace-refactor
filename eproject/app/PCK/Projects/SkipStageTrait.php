<?php namespace PCK\Projects;

use Carbon\Carbon;
use PCK\Helpers\DBTransaction;
use PCK\TenderCallingTenderInformation\TenderCallingTenderInformation;
use PCK\TenderListOfTendererInformation\TenderListOfTendererInformation;
use PCK\TenderRecommendationOfTendererInformation\TenderRecommendationOfTendererInformation;
use PCK\Tenders\Tender;
use PCK\TenderRecommendationOfTendererInformation\ContractorCommitmentStatus;

trait SkipStageTrait {

    public function canManuallySkipToPostContract()
    {
        if( ! $this->isDesignStage() ) return false;

        if( $rotInformation = $this->tenders->first()->recommendationOfTendererInformation )
        {
            if( $rotInformation->created_at != $rotInformation->updated_at ) return false;
        }

        return true;
    }

    public function skippedToPostContract()
    {
        return $this->skipped_to_post_contract;
    }

    private function finalizeDesignStage()
    {
        $this->load('latestTender');

        $tender = $this->latestTender;

        if( ! ( $rotInformation = $tender->recommendationOfTendererInformation ) )
        {
            $user         = \Confide::user();
            $nowTimeStamp = Carbon::now();

            $object                                                 = new TenderRecommendationOfTendererInformation();
            $object->proposed_date_of_calling_tender                = $nowTimeStamp;
            $object->proposed_date_of_closing_tender                = $nowTimeStamp;
            $object->technical_tender_closing_date                  = $nowTimeStamp;
            $object->target_date_of_site_possession                 = $nowTimeStamp;
            $object->budget                                         = 0;
            $object->consultant_estimates                           = null;
            $object->completion_period                              = 0;
            $object->project_incentive_percentage                   = null;
            $object->allow_contractor_propose_own_completion_period = false;
            $object->technical_evaluation_required                  = false;
            $object->contract_limit_id                              = null;

            $object->created_by = $user->id;
            $object->updated_by = $user->id;

            $object->status = TenderRecommendationOfTendererInformation::SUBMISSION;

            $tender->recommendationOfTendererInformation()->save($object);
        }

        \Event::fire('system.updateProjectStatus', array( $this, Project::STATUS_TYPE_RECOMMENDATION_OF_TENDERER ));
        \Event::fire('system.updateTenderFormStatus', array( $tender, Project::STATUS_TYPE_RECOMMENDATION_OF_TENDERER ));
    }

    private function finalizeRecommendationOfTenderer(array $selectedContractorIds = array())
    {
        $this->load('latestTender');

        $tender = $this->latestTender;

        $rotInformation = $tender->recommendationOfTendererInformation;

        $rotInformation->status = TenderRecommendationOfTendererInformation::SUBMISSION;
        $rotInformation->save();

        // Selected contractor.
        $contractorIds = ( $rotInformation->selectedContractors->lists('id') + $selectedContractorIds );
        $rotInformation->selectedContractors()->sync(array( $contractorIds[0] => array( 'status' => ContractorCommitmentStatus::OK ) ));

        $tenderListOfTendererInformationRepository = \App::make('PCK\TenderListOfTendererInformation\TenderListOfTendererInformationRepository');
        $tenderListOfTendererInformationRepository->cloneInformationToListOfTenderer($rotInformation);
        $tender->load('recommendationOfTendererInformation');

        \Event::fire('system.updateProjectStatus', array( $this, Project::STATUS_TYPE_LIST_OF_TENDERER ));
        \Event::fire('system.updateTenderFormStatus', array( $tender, Project::STATUS_TYPE_LIST_OF_TENDERER ));
    }

    private function finalizeListOfTenderer()
    {
        $this->load('latestTender');

        $tender = $this->latestTender;

        $lotInformation = $tender->listOfTendererInformation;

        $lotInformation->status = TenderListOfTendererInformation::SUBMISSION;
        $lotInformation->save();

        // Technical Evaluation.
        $technicalEvaluationSetReferenceRepository = \App::make('PCK\TechnicalEvaluationSetReferences\TechnicalEvaluationSetReferenceRepository');
        if( $tender->listOfTendererInformation->technical_evaluation_required && ( ! $technicalEvaluationSetReferenceRepository->getSetReferenceByProject($this) ) )
        {
            $technicalEvaluationSetReferenceRepository->copy($this, $tender->listOfTendererInformation->contractLimit);
        }

        $tenderCallingTenderInformationRepository = \App::make('PCK\TenderCallingTenderInformation\TenderCallingTenderInformationRepository');
        $tenderCallingTenderInformationRepository->cloneInformationToCallingTender($lotInformation);

        \Event::fire('system.updateProjectStatus', array( $this, Project::STATUS_TYPE_CALLING_TENDER ));
        \Event::fire('system.updateTenderFormStatus', array( $tender, Project::STATUS_TYPE_CALLING_TENDER ));
    }

    private function finalizeCallingTender()
    {
        $this->load('latestTender');

        $tender = $this->latestTender;

        $callingTenderInformation         = $tender->callingTenderInformation;
        $callingTenderInformation->status = TenderCallingTenderInformation::SUBMISSION;
        $callingTenderInformation->save();

        $tenderRepository = \App::make('PCK\Tenders\TenderRepository');
        $tenderRepository->cloneSelectedFinalContractors($tender, $callingTenderInformation);

        \Event::fire('system.updateProjectStatus', array( $this, Project::STATUS_TYPE_CLOSED_TENDER ));
        \Event::fire('system.updateTenderFormStatus', array( $tender, Project::STATUS_TYPE_CLOSED_TENDER ));
        \Event::fire('system.updateTechnicalEvaluationStatus', array($tender));
    }

    private function finalizeClosedTender()
    {
        $this->load('latestTender');

        $tender = $this->latestTender;

        \Event::fire('system.updateProjectStatus', array( $this, Project::STATUS_TYPE_POST_CONTRACT ));
        \Event::fire('system.updateTenderFormStatus', array( $tender, Project::STATUS_TYPE_POST_CONTRACT ));
        \Event::fire('system.updateTechnicalEvaluationStatus', array($tender));
    }

    private function finalizePostContract($selectedContractorId, $postContractFormInput)
    {
        $this->load('latestTender');

        $tenderRepository  = \App::make('PCK\Tenders\TenderRepository');
        $projectRepository = \App::make('PCK\Projects\ProjectRepository');

        $projectRepository->savePostContractInformation($this, $postContractFormInput);

        $tenderRepository->setSelectedContractor($this->latestTender, array( 'contractorId' => $selectedContractorId ));
        
        $projectRepository->assignFinalContractor($this, $selectedContractorId, false);

        $tenderRepository->cancelAccessToSelectedContractors($this->latestTender);
    }

    private function finalizeOpenTender()
    {
        $this->load('latestTender');

        $this->latestTender->open_tender_status   = Tender::OPEN_TENDER_STATUS_OPENED;
        $this->latestTender->tender_starting_date = $this->latestTender->callingTenderInformation->date_of_calling_tender;
        $this->latestTender->tender_closing_date  = $this->latestTender->callingTenderInformation->date_of_closing_tender;
        $this->latestTender->save();
    }

    private function finalizeProjectCompletion($completionDate)
    {
        $completion_date = date('Y-m-d', strtotime($completionDate));

        if( $this->pam2006Detail )
        {
            $this->pam2006Detail->completion_date = $completion_date;

            $this->pam2006Detail->save();
        }

        if( $this->indonesiaCivilContractInformation )
        {
            $this->indonesiaCivilContractInformation->completion_date = $completion_date;

            $this->indonesiaCivilContractInformation->save();
        }

        $this->status_id = Project::STATUS_TYPE_COMPLETED;

        $this->completion_date = $completion_date;

        $this->save();
    }

    public static function getStagesSequence()
    {
        return array(
            Project::STATUS_TYPE_DESIGN,
            Project::STATUS_TYPE_RECOMMENDATION_OF_TENDERER,
            Project::STATUS_TYPE_LIST_OF_TENDERER,
            Project::STATUS_TYPE_CALLING_TENDER,
            Project::STATUS_TYPE_CLOSED_TENDER,
            Project::STATUS_TYPE_POST_CONTRACT,
            Project::STATUS_TYPE_COMPLETED,
        );
    }

    private function finalizeStage($stageStatus, $data = array())
    {
        switch($stageStatus)
        {
            case Project::STATUS_TYPE_DESIGN:
                $this->finalizeDesignStage();
                break;
            case Project::STATUS_TYPE_RECOMMENDATION_OF_TENDERER:
                $this->finalizeRecommendationOfTenderer(array( $data['selectedContractorId'] ));
                break;
            case Project::STATUS_TYPE_LIST_OF_TENDERER:
                $this->finalizeListOfTenderer();
                break;
            case Project::STATUS_TYPE_CALLING_TENDER:
                $this->finalizeCallingTender();
                break;
            case Project::STATUS_TYPE_CLOSED_TENDER:
                $this->finalizeClosedTender();
                break;
            case Project::STATUS_TYPE_POST_CONTRACT:
                $this->finalizePostContract($data['selectedContractorId'], $data['postContractFormInput']);

                $this->finalizeOpenTender();

                $this->skipped_to_post_contract = true;
                $this->save();
                break;
            case Project::STATUS_TYPE_COMPLETED:
                $this->finalizeProjectCompletion($data['completionDate']);
                break;
            default:
                throw new \Exception('Invalid project status');
        }
    }

    /**
     * Pushes the project directly to the target project stage,
     * automating all steps in between.
     *
     * @param       $targetStatusId
     * @param array $data
     *
     * @return bool
     */
    public function skipToStage($targetStatusId, $data = array())
    {
        $success     = false;
        $transaction = new DBTransaction();
        $transaction->begin();

        try
        {
            foreach(self::getStagesSequence() as $statusId)
            {
                if( $this->status_id == $statusId ) $this->finalizeStage($this->status_id, $data);

                if( $statusId == $targetStatusId ) break;
            }

            $transaction->commit();
            $success = true;
        }
        catch(\Exception $exception)
        {
            \Log::error($exception->getMessage());
            $transaction->rollback();

            throw $exception;
        }

        return $success;
    }

    public function stageSequenceCompare($operator, $statusId)
    {
        $currentStage = array_search($this->status_id, self::getStagesSequence());
        $targetStage  = array_search($statusId, self::getStagesSequence());

        switch($operator)
        {
            case '>':
                return $currentStage > $targetStage;
            case '>=':
                return $currentStage >= $targetStage;
            case '<':
                return $currentStage < $targetStage;
            case '<=':
                return $currentStage <= $targetStage;
            case '=':
            case '==':
            case '===':
                return $currentStage === $targetStage;
            default:
                throw new \Exception('Invalid operator');
        }
    }

}