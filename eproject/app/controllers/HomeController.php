<?php

use Illuminate\Support\MessageBag;

use PCK\Projects\StatusType;
use PCK\Projects\Project;
use PCK\Projects\ProjectRepository;
use PCK\Companies\Company;
use PCK\SystemModules\SystemModuleConfiguration;

class HomeController extends \BaseController {

    private $projectRepo;

    public function __construct(ProjectRepository $projectRepo)
    {
        $this->projectRepo = $projectRepo;

    }

    public function index()
    {
        $user       = Confide::user();
        // $companies = collect();
        $projectIds = $this->projectRepo->getVisibleProjectIds($user);

        $totalProjectByStatuses = [];

        if(!empty($projectIds))
        {
            $pdo=\DB::getPdo();

            $stmt = $pdo->prepare("SELECT p.status_id, COUNT(p.id)
            FROM projects AS p
            WHERE p.id IN (".implode(',', $projectIds).")
            AND p.deleted_at IS NULL
            GROUP BY p.status_id");
            
            $stmt->execute();
            
            $totalProjectByStatuses = $stmt->fetchAll(\PDO::FETCH_KEY_PAIR);
        }
        
        $totalProject = array_sum($totalProjectByStatuses);

        $colors = [
            [
                'class_name' => 'blue',
                'hex'        => '#2196f3'
            ],
            [
                'class_name' => 'green',
                'hex'        => '#1dc9b7'
            ],
            [
                'class_name' => 'yellow',
                'hex'        => '#ffc241',
            ],
            [
                'class_name' => 'purple',
                'hex'        => '#886ab5'
            ],
            [
                'class_name' => 'red',
                'hex'        => '#fd3995'
            ],
            [
                'class_name' => 'orange',
                'hex'        => '#ffa500'
            ],
            [
                'class_name' => 'darken',
                'hex'        => '#495057'
            ],
            [
                'class_name' => 'greenDark',
                'hex'        => '#18a899'
            ]
        ];

        $cnt = 0;
        $defaultColor = $colors[0]; // Set default color to blue

        foreach ($totalProjectByStatuses as $status => $total) {
            if ($totalProject) {
                $color = $colors[$cnt] ?? $defaultColor;    // Check if the current color index exists, else use default

                $overallStatusInfo[] = [
                    'id'    => $status,
                    'name'  => Project::getStatusText($status),
                    'color' => $color,
                    'total' => $total
                ];

                $cnt++;
            }
        }

        $overallStatusInfo[] = [
            'id'    => -1,
            'name'  => trans('projects.totalProjects'),
            'color' => [],
            'total' => $totalProject
        ];

        return View::make('home.index', compact(
            // 'companies',
            'user',
            'overallStatusInfo'
        ));
    }

    public function getCompany()
    {
        $companies = Company::where('country_id', 200)->get();

        if ($companies->count() > 0)  {
            return Response::json($companies->map(function ($company) {
                return [
                    'id'    => $company->id,
                    'name'  => $company->name,
                    'email' => $company->email,
                ];
            }));
        } else {
            return Response::json([
                'error' => 'No companies found'
            ], 404);
        }
    
    }

    public function getMyToDoListAjax()
    {
        $includeFutureTasks             = false;
        $user                           = Confide::user();
        $allPendingReviews              = $user->getPendingReviews($includeFutureTasks);
        $pendingTenderingReviews        = $allPendingReviews['tendering'];
        $pendingPostContractReviews     = $allPendingReviews['postContract'];
        $pendingSiteModuleReviews       = $allPendingReviews['siteModule'];
        $pendingVendorManagementReviews = $allPendingReviews['vendorManagement'];

        $tendering = [];
        
        foreach($pendingTenderingReviews as $record)
        {
            $key = snake_case(str_replace(' ', '', $record['module']));
            if(!array_key_exists($key, $tendering))
            {
                $tendering[$key] = [
                    'name'    => $record['module'],
                    'records' => []
                ];
            }

            $tendering[$key]['records'][] = [
                'project_reference'        => $record['project_reference'],
                'parent_project_reference' => $record['parent_project_reference'],
                'project_id'               => $record['project_id'],
                'parent_project_id'        => $record['parent_project_id'],
                'project_title'            => $record['project_title'],
                'parent_project_title'     => $record['parent_project_title'],
                'days_pending'             => $record['days_pending'],
                'route'                    => $record['route']
            ];
        }

        $postContract = [];
        
        foreach($pendingPostContractReviews as $record)
        {
            $key = snake_case(str_replace(' ', '', $record->getModuleName()));

            if(!array_key_exists($key, $postContract))
            {
                $postContract[$key] = [
                    'name'    => $record->getModuleName(),
                    'records' => []
                ];
            }

            $postContract[$key]['records'][] = [
                'project_reference'        => $record->getProject()->reference,
                'parent_project_reference' => ($record->getProject()->parentProject) ? $record->getProject()->parentProject->reference : null,
                'project_id'               => $record->getProject()->id,
                'parent_project_id'        => ($record->getProject()->parentProject) ? $record->getProject()->parentProject->id : null,
                'project_title'            => $record->getProject()->title,
                'parent_project_title'     => ($record->getProject()->parentProject) ? $record->getProject()->parentProject->title : null,
                'project_route'            => route('projects.show', array($record->getProject()->id)),
                'parent_project_route'     => ($record->getProject()->parentProject) ? route('projects.show', array($record->getProject()->parentProject->id)) : "",
                'description'              => $record->getObjectDescription(),
                'module'                   => $record->getModuleName(),
                'days_pending'             => ($record->daysPending) ? $record->daysPending : 0,
                'route'                    => $record->getRoute()
            ];
        }

        $siteModule = [];

        foreach($pendingSiteModuleReviews as $record)
        {
            $key = snake_case(str_replace(' ', '', $record->getModuleName()));

            if(!array_key_exists($key, $siteModule))
            {
                $siteModule[$key] = [
                    'name'    => $record->getModuleName(),
                    'records' => []
                ];
            }

            $siteModule[$key]['records'][] = [
                'project_reference'        => $record->getProject()->reference,
                'parent_project_reference' => ($record->getProject()->parentProject) ? $record->getProject()->parentProject->reference : null,
                'project_id'               => $record->getProject()->id,
                'parent_project_id'        => ($record->getProject()->parentProject) ? $record->getProject()->parentProject->id : null,
                'project_title'            => $record->getProject()->title,
                'parent_project_title'     => ($record->getProject()->parentProject) ? $record->getProject()->parentProject->title : null,
                'project_route'            => route('projects.show', array($record->getProject()->id)),
                'parent_project_route'     => ($record->getProject()->parentProject) ? route('projects.show', array($record->getProject()->parentProject->id)) : "",
                'description'              => $record->getObjectDescription(),
                'module'                   => $record->getModuleName(),
                'days_pending'             => ($record->daysPending) ? $record->daysPending : 0,
                'route'                    => $record->getRoute(),
            ];
        }

        $vendorManagementModule = [];

        foreach($pendingVendorManagementReviews as $record)
        {
            $key = snake_case(str_replace(' ', '', $record->getModuleName()));

            if(!array_key_exists($key, $vendorManagementModule))
            {
                $vendorManagementModule[$key] = [
                    'name'    => $record->getModuleName(),
                    'records' => []
                ];
            }

            $vendorManagementModule[$key]['records'][] = [
                'vendor_name'   => $record->company->name,
                'days_pending'  => $record->daysPending,
                'route'         => $record->getRoute(),
            ];
        }

        $consultantManagementPendingReviews = $user->getConsultantManagementPendingReviews();

        $consultantManagementContracts = [
            'records' => [],
            'total_pending' => 0
        ];

        foreach($consultantManagementPendingReviews as $module => $records)
        {
            foreach($records as $record)
            {
                if(!array_key_exists($record['id'], $consultantManagementContracts['records']))
                {
                    $consultantManagementContracts['records'][$record['id']] = [
                        'title' => trim($record['title']),
                        'reference_no' => $record['reference_no'],
                        'route' => route('consultant.management.contracts.contract.show', [$record['id']]),
                        'total_pending_reviews' => 0,
                        'total_days_pending' => 0
                    ];
                }

                $consultantManagementContracts['records'][$record['id']]['total_pending_reviews']++;

                $consultantManagementContracts['records'][$record['id']]['total_days_pending'] += $record['days_pending'];

                $consultantManagementContracts['total_pending']++;
            }
        }

        // Digital Star
        $digitalStar = [];

        if (SystemModuleConfiguration::isEnabled(SystemModuleConfiguration::MODULE_ID_DIGITAL_STAR))
        {
            $pendingDigitalStarReviews = $allPendingReviews['digitalStar'];

            foreach ($pendingDigitalStarReviews as $record)
            {
                $key = snake_case(str_replace(' ', '', $record['module']));
                if (!array_key_exists($key, $digitalStar))
                {
                    $digitalStar[$key] = [
                        'name'    => $record['module'],
                        'records' => []
                    ];
                }

                $digitalStar[$key]['records'][] = [
                    'project_reference'        => $record['project_reference'],
                    //'parent_project_reference' => $record['parent_project_reference'],
                    //'project_id'               => $record['project_id'],
                    //'parent_project_id'        => $record['parent_project_id'],
                    'project_title'            => $record['project_title'],
                    'parent_project_title'     => $record['parent_project_title'],
                    'days_pending'             => $record['days_pending'],
                    'route'                    => $record['route']
                ];
            }
        }

        return Response::json([
            'tendering'             => $tendering,
            'post_contract'         => $postContract,
            'site_module'           => $siteModule,
            'consultant_management' => $consultantManagementContracts,
            'vendor_management'     => $vendorManagementModule,
            'digital_star'          => $digitalStar,
        ]);
    }
}