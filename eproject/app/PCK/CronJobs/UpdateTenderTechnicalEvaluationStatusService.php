<?php namespace PCK\CronJobs;

use PCK\Tenders\Tender;

class UpdateTenderTechnicalEvaluationStatusService {

    public function handle()
    {
        \Log::info("Running service", [
            'class' => get_class($this)
        ]);

        $tenders = Tender::whereIn('technical_evaluation_status', [
            Tender::TECHNICAL_EVALUATION_STATUS_NOT_STARTED,
            Tender::TECHNICAL_EVALUATION_STATUS_STARTED,
        ])->get();

        foreach($tenders as $tender)
        {
            \Event::fire('system.updateTechnicalEvaluationStatus', array($tender));
        }        
    }

}