<?php

use PCK\TechnicalEvaluationSetReferences\TechnicalEvaluationSetReferenceRepository;

class TechnicalEvaluationSetReferenceTableSeeder extends Seeder {

    private $technicalEvaluationSetReferenceRepository;

    public function __construct(TechnicalEvaluationSetReferenceRepository $technicalEvaluationSetReferenceRepository)
    {
        $this->technicalEvaluationSetReferenceRepository = $technicalEvaluationSetReferenceRepository;
    }

    public function run()
    {
        $transaction = new \PCK\Helpers\DBTransaction();
        $transaction->begin();

        $className = get_class($this);

        Log::info("Starting $className.");

        try
        {
            $this->createSetReferences();

            $transaction->commit();

            Log::info("$className successful.");
        }
        catch(Exception $exception)
        {
            Log::error("Failed running $className. Error: {$exception->getMessage()}");

            $transaction->rollback();
        }
    }

    private function createSetReferences()
    {
        foreach(\PCK\TenderListOfTendererInformation\TenderListOfTendererInformation::all() as $lotInformation)
        {
            if( ! ( $project = $lotInformation->tender->project ) ) continue;

            if( $lotInformation->technical_evaluation_required && ( ! $this->technicalEvaluationSetReferenceRepository->getSetReferenceByProject($project) ) )
            {
                Log::info("Copying technical evaluation set reference for project $project->id.");

                $this->technicalEvaluationSetReferenceRepository->copy($project, $lotInformation->contractLimit);
            }
        }
    }
}