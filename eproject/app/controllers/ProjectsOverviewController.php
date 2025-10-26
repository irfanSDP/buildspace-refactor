<?php

use Carbon\Carbon;
use PCK\Helpers\DataTables;
use PCK\Projects\Project;
use PCK\Projects\ProjectRepository;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ProjectsOverviewController extends \BaseController {

    private $projectRepository;

    public function __construct(ProjectRepository $projectRepository)
    {
        $this->projectRepository = $projectRepository;
    }

    public function index()
    {
        $subsidiaries = \PCK\Subsidiaries\Subsidiary::orderBy('name', 'asc')->get();

        if( ! ( $superAdminUser = \PCK\Users\User::where('is_super_admin', '=', true)->first() ) ) throw new Exception('No super admin users to use the privileges of.');

        $projectsData = $this->projectRepository->getProjectsDashboardData($superAdminUser);

        return View::make('projectsOverview.index', compact('subsidiaries', 'projectsData'));
    }

    public function getIndexData()
    {
        $query         = \DB::table("projects")->whereNull('projects.deleted_at');
        $idColumn      = 'projects.id';
        $selectColumns = array( $idColumn );

        $allColumns = array(
            "projects" => array(
                "reference"     => 1,
                "title"         => 2,
                "projectStatus" => 3,
            ),
        );

        $input     = Input::all();
        $dataTable = new DataTables($query, Input::all(), $allColumns, $idColumn, $selectColumns);

        $dataTable->properties->query->join('countries', 'projects.country_id', '=', 'countries.id')
            ->join('states', 'states.country_id', '=', 'countries.id')
            ->leftJoin('subsidiaries', 'subsidiaries.id', '=', 'projects.subsidiary_id');

        $subsidiaryName             = trim(isset( $input['subsidiaryName'] ) ? $input['subsidiaryName'] : '');
        $subsidiaryNameSearchString = '%' . $subsidiaryName . '%';
        $dataTable->properties->query->where(function($query) use ($subsidiaryNameSearchString)
        {
            $query->where('subsidiaries.name', 'ILIKE', $subsidiaryNameSearchString);
            $query->orWhereNull('subsidiaries.id');
        });

        $customGlobalFilter = [
            3 => function($query, $searchString)
            {
                DataTables::genericCustomGlobalFilteringFunction($query, $searchString, $this->projectRepository->getAllProjectStatusTypeIdAndName(), 'projects.status_id');
            }
        ];

        $dataTable->setCustomGlobalFilter($customGlobalFilter);

        $dataTable->addAllStatements();

        $results = $dataTable->getResults();

        $dataArray = array();

        foreach($results as $arrayIndex => $stdObject)
        {
            $project = Project::find($stdObject->id);
            $project->load('parentProject');

            $indexNo = ( $arrayIndex + 1 ) + ( $dataTable->properties->pagingOffset );

            $dataArray[] = array(
                'indexNo'                => $indexNo,
                'reference'              => $project->reference,
                'projectTitle'           => $project->title,
                'projectShortTitle'      => $project->short_title,
                'projectStatus'          => Project::getStatusById($project->status_id),
                'country'                => $project->country->country ?: 'N/A',
                'state'                  => $project->state->name ?: 'N/A',
                'contractName'           => $project->contract->name,
                'projectCreatedAt'       => Carbon::parse($project->created_at)->format(\Config::get('dates.submission_date_formatting')),
                'isSubPackage'           => $project->isSubProject(),
                'parentProjectTitle'     => $project->parentProject->title ?? '',
                'parentProjectReference' => $project->parentProject->reference ?? '',
                'subPackagesCount'       => $project->subProjects->count(),
            );
        }

        return Response::json($dataTable->dataTableResponse($dataArray));
    }

    public function exportProjectsOverviewExcel()
    {
        $spreadsheet = $this->projectRepository->generateProjectDataSpreadsheet();

        $writer = new Xlsx($spreadsheet);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename=' . trans('projects.projectsOverview') . '.xlsx');

        try
        {
            $writer->save("php://output");
        } 
        catch(Exception $e) {
            echo $e->getMessage();
        }
    }
}