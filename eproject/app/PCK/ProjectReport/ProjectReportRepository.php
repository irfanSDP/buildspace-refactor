<?php namespace PCK\ProjectReport;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use PCK\ProjectReport\ProjectReport;
use PCK\ProjectReport\ProjectReportType;
use PCK\ProjectReport\ProjectReportTypeMapping;
use PCK\ProjectReport\ProjectReportColumnRepository;
use PCK\Projects\Project;
use PCK\Verifier\Verifier;
use PCK\Users\User;

class ProjectReportRepository
{
    private $columnRepository;

    public function __construct(ProjectReportColumnRepository $columnRepository)
    {
        $this->columnRepository = $columnRepository;
    }

    private function templateQuery($status = null)
    {
        $statusQuery = is_null($status) ? '' : " AND status = {$status} ";

        return "WITH project_report_templates_cte AS (
                      SELECT ROW_NUMBER() OVER (PARTITION BY root_id ORDER BY revision DESC) AS rank, *
                      FROM project_reports
                      WHERE project_id IS NULL
                      AND deleted_at IS NULL
                      {$statusQuery}
                  )
                  SELECT cte.id, cte.title, cte.revision, cte.status
                  FROM project_report_templates_cte cte
                  WHERE cte.rank = 1
                  ORDER BY cte.root_id ASC";
    }

    public function listTemplates()
    {
        $query = $this->templateQuery();

        return DB::select(DB::raw($query));
    }

    public function listLatestApprovedTemplates()
    {
        $query = $this->templateQuery(ProjectReport::STATUS_COMPLETED);

        return DB::select(DB::raw($query));
    }

    public function listPreviousRevisions(Project $project, ProjectReportTypeMapping $mapping)
    {
        $latestProjectReport = ProjectReport::getLatestProjectReport($project, $mapping);

        $records = ProjectReport::where('project_id', $project->id)
                    ->where('project_report_type_mapping_id', $mapping->id)
                    ->where('status', ProjectReport::STATUS_COMPLETED)
                    ->where('id', '!=', $latestProjectReport->id)
                    ->orderBy('revision', 'DESC')
                    ->get()
                    ->toArray();

        $data = [];

        foreach($records as $record)
        {
            array_push($data, [
                'id'         => $record['id'],
                'title'      => $record['title'],
                'revision'   => $record['revision'],
                'route:show' => route('projectReport.previousRevision.show', [$project->id, $record['id']]),
            ]);
        }

        return $data;
    }

    public function createNewTemplate($title, ProjectReport $template = null)
    {
        $newTemplate            = new ProjectReport();
        $newTemplate->root_id   = is_null($template) ? null : $template->root_id;
        $newTemplate->origin_id = is_null($template) ? null : $template->id;
        $newTemplate->title     = trim($title);
        $newTemplate->revision  = is_null($template) ? 0 : $template->revision + 1;
        $newTemplate->status    = ProjectReport::STATUS_DRAFT;
        $newTemplate->save();

        return ProjectReport::find($newTemplate->id);
    }

    public function createNewRevision(ProjectReport $template, $title)
    {
        $newRevision = $this->createNewTemplate($title, $template);

        return ProjectReportColumn::clone($template, $newRevision);
    }

    public function cloneNewForm(ProjectReport $template, $title)
    {
        $newTemplate            = new ProjectReport();
        $newTemplate->title     = trim($title);
        $newTemplate->revision  = 0;
        $newTemplate->status    = ProjectReport::STATUS_DRAFT;
        $newTemplate->save();

        $newTemplate = ProjectReport::find($newTemplate->id);

        return ProjectReportColumn::clone($template, $newTemplate, false);  // Clone template -> Don't clone but generate new column reference ID
    }

    public function getProjectReportTypesList(Project $project, $permissionTypeList = array())
    {
        $projectTypeIdentifier = is_null($project->parent_project_id) ? Project::TYPE_MAIN_PROJECT : Project::TYPE_SUB_PACKAGE;

        $records = ProjectReportType::getUserAccessibleProjectReportTypes($project, \Confide::user(), $projectTypeIdentifier, $permissionTypeList);
        $data    = [];

        foreach($records as $record)
        {
            $mapping             = ProjectReportTypeMapping::find($record->mapping_id);
            $latestProjectReport = ProjectReport::getLatestProjectReport($project, $mapping);

            $temp = [
                //'mapping_id'              => $record->report_type_id,
                'mapping_title'           => $record->report_type_title,
                'project_id'              => $project->id,
                'mapping_id'              => $record->mapping_id,
                'mapping_project_type'    => $record->mapping_project_type,
                'mapped_template_id'      => $record->mapped_template_id,
                'mapped_template_title'   => $record->mapped_template_title,
                'project_report_revision' => is_null($latestProjectReport) ? null : $latestProjectReport->revision,
                'project_report_status'   => is_null($latestProjectReport) ? null : ProjectReport::getStatusText($latestProjectReport->status),
                'route:previousRevisions' => route('projectReport.previousRevisions.list', [$project->id, $mapping->id]),
            ];

            if (in_array(ProjectReportUserPermission::IDENTIFIER_SUBMIT_REPORT, $permissionTypeList)) {
                $temp['route:show'] = route('projectReport.showAll', [$project->id, $mapping->id]);
            }
            if (in_array(ProjectReportUserPermission::IDENTIFIER_EDIT_REMINDER, $permissionTypeList)) {
                $temp['route:show'] = route('projectReport.notification.index', [$project->id, $mapping->id]);
            }

            array_push($data, $temp);
        }

        return $data;
    }

    public function getReportsByMapping(Project $project, ProjectReportTypeMapping $mapping, $projectType)
    {
        $status = ProjectReport::STATUS_COMPLETED;

        $query = "WITH project_report_cte AS (
                	SELECT pr.*
                 	FROM project_reports pr
                 	INNER JOIN project_report_type_mappings tm ON tm.id = pr.project_report_type_mapping_id 
                 	WHERE pr.project_id = {$project->id}
                 	AND pr.deleted_at IS NULL
                 	AND pr.status = {$status}
                 	AND pr.project_report_type_mapping_id = {$mapping->id}
                 	AND tm.project_type = {$projectType}
                 )
                 SELECT cte.id, cte.project_id, cte.root_id, cte.origin_id
                 FROM project_report_cte cte
                 ORDER BY cte.project_report_type_mapping_id ASC;";

        return DB::select(DB::raw($query));
    }

    public function cloneReportFromTemplateMapping(Project $project, ProjectReportTypeMapping $mapping)
    {
        // latest approved project report
        $latestApprovedProjectReport = ProjectReport::latestApprovedProjectReport($project, $mapping);

        $template = null;

        // if first revision, copy from mapped template
        if(is_null($latestApprovedProjectReport))
        {
            $template = $mapping->projectReportTemplate;
        }
        else
        {
            $latestClonedTemplate = $latestApprovedProjectReport->getLatestClonedTemplateInSeries();

            // latest template is still the same with the already cloned template
            if($latestClonedTemplate->id === $mapping->projectReportTemplate->id)
            {
                $template = $latestApprovedProjectReport;
            }
            else
            {
                $template = $mapping->projectReportTemplate;
            }
        }

        $projectReport                                 = new ProjectReport();
        $projectReport->project_id                     = $project->id;
        $projectReport->root_id                        = is_null($latestApprovedProjectReport) ? null : $latestApprovedProjectReport->root_id; 
        $projectReport->origin_id                      = $template->id;
        $projectReport->title                          = $template->title;
        $projectReport->revision                       = is_null($latestApprovedProjectReport) ? 0 : $latestApprovedProjectReport->revision + 1;
        $projectReport->status                         = ProjectReport::STATUS_DRAFT;
        $projectReport->project_report_type_mapping_id = $mapping->id;
        $projectReport->save();

        $projectReport = ProjectReport::find($projectReport->id);

        ProjectReportColumn::clone($template, $projectReport);

        return $projectReport;
    }

    public function submitForApproval(ProjectReport $projectReport, $inputs)
    {
        $verifiers = array_filter($inputs['verifiers'], function($value)
        {
            return $value != "";
        });

        if( empty( $verifiers ) )
        {
            $projectReport->status        = ProjectReport::STATUS_COMPLETED;
            $projectReport->approved_date = Carbon::now();
            $projectReport->save();

            ProjectReportColumn::persistVerifiedValues($projectReport);
        }
        else
        {
            Verifier::setVerifiers($verifiers, $projectReport);

            $projectReport->submitted_by = \Confide::user()->id;
            $projectReport->status       = ProjectReport::STATUS_PENDING_VERIFICATION;
            $projectReport->save();

            Verifier::sendPendingNotification($projectReport);
        }
    }

    public function getPendingApprovalProjectReports(User $user, $includeFutureTasks, Project $project = null)
    {
        $pendingApprovalProjectReports = [];

        if($project)
        {
            $pendingApprovalRecords = $project->projectReports()
                                        ->where('status', ProjectReport::STATUS_PENDING_VERIFICATION)
                                        ->where('project_id', $project->id)
                                        ->orderBy('updated_at', 'ASC')
                                        ->get();

            if($pendingApprovalRecords->isEmpty()) return [];

            foreach($pendingApprovalRecords as $record)
            {
                $isCurrentVerifier = Verifier::isCurrentVerifier($user, $record);

                $proceed = $includeFutureTasks ? Verifier::isAVerifierInline($user, $record) : $isCurrentVerifier;

                if($proceed)
                {
                    $record['is_future_task'] = ! $isCurrentVerifier;
                    $record['company_id']     = $project->business_unit_id;

                    $pendingApprovalProjectReports[$record->id] = $record;
                }
            }
        }
        else
        {
            $records          = Verifier::where('verifier_id', $user->id)->where('object_type', ProjectReport::class)->get();
            $projectReportIds = $records->lists('object_id');
            $projectReports   = ProjectReport::whereIn('id', $projectReportIds)->where('status', ProjectReport::STATUS_PENDING_VERIFICATION)->get();

            foreach($projectReports as $projectReport)
            {
                $isCurrentVerifier  = Verifier::isCurrentVerifier($user, $projectReport);
                $proceed            = $includeFutureTasks ? Verifier::isAVerifierInline($user, $projectReport) : $isCurrentVerifier;

                if($proceed)
                {
                    $projectReport['is_future_task'] = ! $isCurrentVerifier;
                    $projectReport['company_id']     = $projectReport->project->business_unit_id;

                    $pendingApprovalProjectReports[$projectReport->id] = $projectReport;
                }
            }
        }

        return $pendingApprovalProjectReports;
    }

    public function getColumnContentsByTemplate(ProjectReportTypeMapping $mapping, $projectReportIds, $projectType, $convertLineBreakToHTML = true)
    {
        $data           = [];
        //$mainProjectIds = [];

        $projectReports = ProjectReport::whereIn('id', $projectReportIds)->orderBy('project_id', 'ASC')->get();

        foreach($projectReports as $projectReport)
        {
            $temp = [
                'projectId'                  => $projectReport->project_id,
                'projectReportId'            => $projectReport->id,
                'rowData'                    => $this->columnRepository->getDashboardColumnContents($projectReport, $convertLineBreakToHTML),
                //'approvedDate'               => is_null($projectReport->approved_date) ? null : Carbon::parse($projectReport->approved_date)->format(\Config::get('dates.full_format')),
                'route:show'                 => route('projectReport.show', [$projectReport->project_id, $mapping->id, 'prid' => $projectReport->id]),
            ];

            $data[] = $temp;

            /*if($projectType == Project::TYPE_MAIN_PROJECT)
            {
                array_push($mainProjectIds, $projectReport->project_id);
            }*/
        }

        // checks whether a main project has sub-projects with approved project reports
        /*if($projectType == Project::TYPE_MAIN_PROJECT && count($mainProjectIds) > 0)
        {
            $subPackageMapping = $mapping->projectReportType->mappings()->where('project_type', Project::TYPE_SUB_PACKAGE)->first();

            // sub-projects grouped by main projects
            $subProjectsGroupedByMainProjectRecords = Project::whereIn('projects.parent_project_id', $mainProjectIds)
                ->select('projects.parent_project_id', DB::raw('ARRAY_TO_JSON(ARRAY_AGG(projects.id)) AS sub_project_ids'))
                ->groupBy('projects.parent_project_id')
                ->orderBy('projects.parent_project_id', 'ASC')
                ->get()
                ->toArray();

            $subProjectsGroupedByMainProject = [];
            $allSubProjectsIds               = [];

            foreach($subProjectsGroupedByMainProjectRecords as $record)
            {
                $subProjectIds = json_decode($record['sub_project_ids']);

                $subProjectsGroupedByMainProject[$record['parent_project_id']] = $subProjectIds;

                foreach($subProjectIds as $subProjectId)
                {
                    array_push($allSubProjectsIds, $subProjectId);
                }
            }

            $subProjectProjectReportRecords = ProjectReport::whereIn('project_id', $allSubProjectsIds)
                ->select('project_reports.project_id', DB::raw('COUNT(project_reports.id)'))
                ->where('project_report_type_mapping_id', $subPackageMapping->id)
                ->where('status', ProjectReport::STATUS_COMPLETED)
                ->groupBy('project_id')
                ->lists('project_id');

            // determines which main reports have sub-projects that have project reports
            $mainProjectSubProjectProjectReport = [];

            foreach($subProjectsGroupedByMainProject as $mainProjectId => $subProjectIds)
            {
                foreach($subProjectIds as $subProjectId)
                {
                    if(in_array($subProjectId, $subProjectProjectReportRecords))
                    {
                        $mainProjectSubProjectProjectReport[$mainProjectId][] = $subProjectId;
                    }
                }
            }

            foreach($data as $index => $record)
            {
                $data[$index]['subProjectProjectReportsCount'] = array_key_exists($record['projectId'], $mainProjectSubProjectProjectReport) ? count($mainProjectSubProjectProjectReport[$record['projectId']]) : 0;
            }
        }*/

        return $data;
    }
}