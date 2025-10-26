<?php namespace PCK\QueueJobs;

use Illuminate\Queue\Jobs\Job;

use PCK\Helpers\PathRegistry;

class GenerateVendorEvaluationForms {

    public function fire(Job $job, array $data)
    {
        \Log::info("Generating Vendor Evaluation Forms for download.");

        $cycleId = $data['cycle_id'];

        $artisanPath = PathRegistry::artisan();

        $command = "php {$artisanPath} report:generate-vpe-forms {$cycleId}";

        exec($command);

        return $job->delete();
    }
}