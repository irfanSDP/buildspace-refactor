<?php namespace PCK\CronJobs;

use Carbon\Carbon;
use PCK\Projects\Project;
use PCK\Projects\ProjectRepository;

class UpdateProjectTenderStatusToClosedTenderService {

    private $projectRepo;

    public function __construct(ProjectRepository $projectRepo)
    {
        $this->projectRepo = $projectRepo;
    }

    public function handle()
    {
        $now      = Carbon::now();
        $projects = $this->projectRepo->getWithCurrentTenderStatus(Project::STATUS_TYPE_CALLING_TENDER);

        // for each project, the system will check the latest tender's expiry date
        // if expired then will update it's status to closed tender
        foreach($projects as $project)
        {
            if( $project->skipped_to_post_contract ) continue;

            if( ! $latestTender = $project->latestTender ) continue;

            if( ! $latestTender->tender_closing_date ) continue;

            $tenderClosingDate = Carbon::createFromFormat(\Config::get('dates.created_and_updated_at_formatting'), $latestTender->tender_closing_date);

            // Determines if the instance is greater (after) than or equal to another (if not)
            // then continue looping to another project
            if( ! $now->gte($tenderClosingDate) )
            {
                continue;
            }

            // will update the latest tender status as well
            $latestTender->current_form_type = Project::STATUS_TYPE_CLOSED_TENDER;
            $latestTender->save();

            $this->projectRepo->updateProjectStatus($project, Project::STATUS_TYPE_CLOSED_TENDER);
        }
    }

}