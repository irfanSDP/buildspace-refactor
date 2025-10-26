<?php namespace PCK\ProjectReport;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use PCK\ModulePermission\ModulePermissionRepository;
use PCK\Projects\Project;

class ProjectReportDashboardRepository
{
    private $columnRepository;
    private $modulePermissionRepository;

    public function __construct(ProjectReportColumnRepository $columnRepository, ModulePermissionRepository $modulePermissionRepository )
    {
        $this->columnRepository = $columnRepository;
        $this->modulePermissionRepository = $modulePermissionRepository;
    }

    public function getListOfReportTypes()
    {
        $projectReportTable            = with(new ProjectReport)->getTable();
        $projectReportTypeTable        = with(new ProjectReportType)->getTable();
        $projectReportTypeMappingTable = with(new ProjectReportTypeMapping)->getTable();

        $status = ProjectReport::STATUS_COMPLETED;

        return ProjectReportType::join($projectReportTypeMappingTable, "{$projectReportTypeMappingTable}.project_report_type_id", '=', "{$projectReportTypeTable}.id")
            ->leftJoin($projectReportTable, "{$projectReportTable}.project_report_type_mapping_id", '=', "{$projectReportTypeMappingTable}.id")
            ->where("{$projectReportTypeMappingTable}.project_type", Project::TYPE_MAIN_PROJECT)
            ->whereNotNull("{$projectReportTable}.project_id")
            ->whereNull("{$projectReportTable}.deleted_at")
            ->where("{$projectReportTable}.status", $status)
            ->groupBy("{$projectReportTypeTable}.id", "{$projectReportTypeMappingTable}.id")
            ->having(\DB::raw("COUNT({$projectReportTypeTable}.id)"), '>', 0)
            ->orderBy("{$projectReportTypeTable}.id", "ASC")
            ->select("{$projectReportTypeTable}.id", "{$projectReportTypeTable}.title", "{$projectReportTypeMappingTable}.id AS mapping_id")
            ->get()
            ->toArray();
    }

    public function getReportsByMapping(ProjectReportTypeMapping $mapping, $projectType)
    {
        $userSubsidiaryIds = $this->modulePermissionRepository->getUserSubsidiaryIds($this->modulePermissionRepository->getModuleId('project_report_dashboard'));
        $subsidiaryIds = ! empty($userSubsidiaryIds) ? implode(',', $userSubsidiaryIds) : 0;

        $status = ProjectReport::STATUS_COMPLETED;

        $query = "WITH project_report_cte AS (
                    SELECT 
                        pr.*
                    FROM 
                        project_reports pr
                        INNER JOIN project_report_type_mappings tm ON tm.id = pr.project_report_type_mapping_id 
                        JOIN projects p ON pr.project_id = p.id
                    WHERE 
                        pr.project_id IS NOT NULL
                        AND pr.deleted_at IS NULL
                        AND pr.status = :status
                        AND pr.project_report_type_mapping_id = :mapping_id
                        AND tm.project_type = :project_type
                        AND p.deleted_at IS NULL
                        AND p.subsidiary_id IN ({$subsidiaryIds})
                )
                SELECT 
                    cte.id, cte.project_id, cte.root_id, cte.origin_id
                FROM 
                    project_report_cte cte
                ORDER BY 
                    cte.project_report_type_mapping_id ASC;";

        return DB::select(DB::raw($query), array(
            'status' => $status,
            'mapping_id' => $mapping->id,
            'project_type' => $projectType,
        ));
    }

    public function getLatestReportsByMapping(ProjectReportTypeMapping $mapping, $projectType)
    {
        $userSubsidiaryIds = $this->modulePermissionRepository->getUserSubsidiaryIds($this->modulePermissionRepository->getModuleId('project_report_dashboard'));
        $subsidiaryIds = ! empty($userSubsidiaryIds) ? implode(',', $userSubsidiaryIds) : 0;

        $status = ProjectReport::STATUS_COMPLETED;

        $query = "WITH project_report_cte AS (
                    SELECT 
                        ROW_NUMBER() OVER (PARTITION BY pr.project_id, pr.project_report_type_mapping_id ORDER BY pr.revision DESC) AS rank, 
                        pr.*
                    FROM 
                        project_reports pr
                        INNER JOIN project_report_type_mappings tm ON tm.id = pr.project_report_type_mapping_id 
                        JOIN projects p ON pr.project_id = p.id
                    WHERE 
                        pr.project_id IS NOT NULL
                        AND pr.deleted_at IS NULL
                        AND pr.status = :status
                        AND pr.project_report_type_mapping_id = :mapping_id
                        AND tm.project_type = :project_type
                        AND p.deleted_at IS NULL
                        AND p.subsidiary_id IN ({$subsidiaryIds})
                    )
                SELECT 
                    cte.id, cte.project_id, cte.root_id, cte.origin_id
                FROM 
                    project_report_cte cte
                WHERE 
                    cte.rank = 1
                ORDER BY 
                    cte.project_report_type_mapping_id ASC;";

        return DB::select(DB::raw($query), array(
            'status' => $status,
            'mapping_id' => $mapping->id,
            'project_type' => $projectType,
        ));
    }

    public function getLatestSubpackageReportsByMapping(ProjectReportTypeMapping $mapping, $projectType, $projectIds = [])
    {
        if(count($projectIds) == 0) return [];

        $status = ProjectReport::STATUS_COMPLETED;

        $projectIdsClause = count($projectIds) > 0 ? ' AND pr.project_id IN (' . implode(', ', $projectIds) . ') ' : '';

        $query = "WITH project_report_cte AS (
                	SELECT ROW_NUMBER() OVER (PARTITION BY project_id, project_report_type_mapping_id ORDER BY revision DESC) AS rank, pr.*
                 	FROM project_reports pr
                 	INNER JOIN project_report_type_mappings tm ON tm.id = pr.project_report_type_mapping_id 
                 	WHERE pr.project_id IS NOT NULL
                 	AND pr.deleted_at IS NULL
                 	AND pr.status = {$status}
                 	AND pr.project_report_type_mapping_id = {$mapping->id}
                 	AND tm.project_type = {$projectType}
                    {$projectIdsClause}
                 )
                 SELECT cte.id, cte.project_id, cte.root_id, cte.origin_id
                 FROM project_report_cte cte 
                 WHERE cte.rank = 1
                 ORDER BY cte.project_report_type_mapping_id ASC;";

        return DB::select(DB::raw($query));
    }

    public function getProjectReportsGroupedByTemplate($latestProjectReportIds, $latestRevision=true)
    {
        if(count($latestProjectReportIds) == 0) return [];

        $latestProjectReportIdsString = implode(', ', $latestProjectReportIds);

        if ($latestRevision) {
            $query = "WITH RECURSIVE project_report_template_cte AS (
                          SELECT pr.id, pr.origin_id, pr.root_id, pr.revision
                          FROM project_reports pr
                          WHERE pr.id IN ({$latestProjectReportIdsString})
                          AND pr.project_id IS NOT NULL
                          AND pr.deleted_at IS NULL
                          UNION
                          SELECT pr2.id, pr2.origin_id, pr2.root_id, pr2.revision 
                          FROM project_reports pr2
                          INNER JOIN project_report_template_cte pcte ON pcte.origin_id = pr2.id
                          WHERE pr2.project_id IS NOT NULL
                          AND pr2.deleted_at IS NULL
                      ),
                      report_and_template_grouping_cte AS (
                          SELECT ROW_NUMBER() OVER (PARTITION BY prt_cte.root_id ORDER BY prt_cte.revision ASC) AS rank,
                          LAST_VALUE(prt_cte.id) OVER (PARTITION BY prt_cte.root_id) AS latest_project_report_id,
                          prt_cte.*
                          FROM project_report_template_cte prt_cte
                          ORDER BY prt_cte.root_id ASC, prt_cte.revision DESC
                      ),
                      final_cte AS (
                          SELECT cte.origin_id AS template_id, ARRAY_TO_JSON(ARRAY_AGG(cte.latest_project_report_id)) AS project_report_ids
                          FROM report_and_template_grouping_cte cte
                          WHERE cte.rank = 1
                          GROUP BY cte.origin_id
                          ORDER BY cte.origin_id ASC
                      )
                      SELECT pr.id AS template_id, pr.title as template_title, fcte.project_report_ids
                      FROM final_cte fcte
                      INNER JOIN project_reports pr ON pr.id = fcte.template_id;";
        } else {
            $query = "WITH RECURSIVE project_report_template_cte AS (
                    SELECT pr.id, pr.origin_id, pr.root_id, pr.revision
                    FROM project_reports pr
                    WHERE pr.id IN ({$latestProjectReportIdsString})
                      AND pr.project_id IS NOT NULL
                      AND pr.deleted_at IS NULL
                    UNION ALL
                    SELECT pr2.id, pr2.origin_id, pr2.root_id, pr2.revision
                    FROM project_reports pr2
                             INNER JOIN project_report_template_cte pcte ON pcte.origin_id = pr2.id
                    WHERE pr2.project_id IS NOT NULL
                      AND pr2.deleted_at IS NULL
                ),
                aggregated_reports AS (
                    SELECT 
                        prt_cte.origin_id,
                        JSON_AGG(prt_cte.id ORDER BY prt_cte.revision DESC) AS project_report_ids
                    FROM project_report_template_cte prt_cte
                    GROUP BY prt_cte.origin_id
                )
                SELECT 
                    pr.id AS template_id, 
                    pr.title AS template_title, 
                    ar.project_report_ids
                FROM aggregated_reports ar
                INNER JOIN project_reports pr ON pr.id = ar.origin_id
                ORDER BY ar.origin_id ASC;";
        }

        $data = [];

        foreach(DB::select(DB::raw($query)) as $result)
        {
            $data[$result->template_id] = [
                'template_id'        => $result->template_id,
                'template_title'     => $result->template_title,
                'project_report_ids' => json_decode($result->project_report_ids),
            ];
        }

        return $data;
    }

    public function getColumnContentsByTemplate(ProjectReportTypeMapping $mapping, $projectReportIds, $projectType, $convertLineBreakToHTML = true)
    {
        $data           = [];
        $mainProjectIds = [];

        $projectReports = ProjectReport::whereIn('id', $projectReportIds)->orderBy('project_id', 'ASC')->get();

        foreach($projectReports as $projectReport)
        {
            $temp = [
                'projectId'                  => $projectReport->project_id,
                'projectReportId'            => $projectReport->id,
                'rowData'                    => $this->columnRepository->getDashboardColumnContents($projectReport, $convertLineBreakToHTML),
                'approvedDate'               => is_null($projectReport->approved_date) ? null : Carbon::parse($projectReport->approved_date)->format(\Config::get('dates.full_format')),
                'remarks'                    => $projectReport->remarks,
                'route:show'                 => route('projectReport.dashboard.projectReport.subpackage.show', $projectReport->id),
                'route:updateRemarks'        => route('projectReport.dashboard.remarks.update', $projectReport->id),
                'route:listAllReportsInLine' => route('projectReport.dashboard.allReportsInLine.get', $projectReport->id),
            ];

            $data[] = $temp;

            if($projectType == Project::TYPE_MAIN_PROJECT)
            {
                array_push($mainProjectIds, $projectReport->project_id);
            }
        }

        // checks whether a main project has sub-projects with approved project reports
        if($projectType == Project::TYPE_MAIN_PROJECT && count($mainProjectIds) > 0)
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
        }

        return $data;
    }
}