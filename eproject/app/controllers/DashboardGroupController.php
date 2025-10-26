<?php

use PCK\Users\User;
use PCK\Projects\Project;
use PCK\Countries\Country;
use PCK\Countries\CountryRepository;
use PCK\Projects\StatusType;
use PCK\Dashboard\DashboardGroup;

use PCK\Forms\DashboardGroupForm;

class DashboardGroupController extends BaseController
{
    private $countryRepo;
    private $dashboardGroupForm;

    public function __construct(CountryRepository $countryRepo, DashboardGroupForm $dashboardGroupForm)
    {
        $this->countryRepo = $countryRepo;
        $this->dashboardGroupForm = $dashboardGroupForm;
    }

    public function index()
    {
        $user = \Confide::user();
        
        $ids = [
            DashboardGroup::TYPE_DEVELOPER,
            DashboardGroup::TYPE_MAIN_CONTRACTOR,
            DashboardGroup::TYPE_E_BIDDING,
        ];

        foreach($ids as $id)
        {
            $dashboardGroup = DashboardGroup::find($id);
            if(!$dashboardGroup)
            {
                $dashboardGroup       = new DashboardGroup();
                $dashboardGroup->type = $id;
                
                $dashboardGroup->save();
            }
        }

        $data = [];

        $dashboardGroups = DashboardGroup::orderBy('type', 'asc')->get();

        foreach($dashboardGroups as $dashboardGroup)
        {
            $data[] = [
                'id'                      => $dashboardGroup->type,
                'name'                    => $dashboardGroup->getName(),
                'total_users'             => $dashboardGroup->users->count(),
                'total_excluded_projects' => $dashboardGroup->excludedProjects->count(),
                'url'                     => route('dashboard.group.show', ['id'=>$dashboardGroup->type])
            ];
        }

        $dashboardGroups = $data;

        return View::make('dashboard.group.index', compact('user', 'dashboardGroups'));
    }

    public function show($id)
    {
        $user = \Confide::user();

        $id = (int)$id;

        if(!in_array($id, [
            DashboardGroup::TYPE_DEVELOPER,
            DashboardGroup::TYPE_MAIN_CONTRACTOR,
            DashboardGroup::TYPE_E_BIDDING,
        ])) {
            return Redirect::route('dashboard.group.index');
        }

        $dashboardGroup = DashboardGroup::find($id);

        if(!$dashboardGroup)
        {
            $dashboardGroup       = new DashboardGroup();
            $dashboardGroup->type = $id;
            
            $dashboardGroup->save();
        }

        switch ($dashboardGroup->type) {
            case DashboardGroup::TYPE_DEVELOPER:
            case DashboardGroup::TYPE_MAIN_CONTRACTOR:
                $projectStatuses = [
                    0 => 'All',
                    StatusType::STATUS_TYPE_DESIGN => StatusType::STATUS_TYPE_DESIGN_TEXT,
                    StatusType::STATUS_TYPE_POST_CONTRACT => StatusType::STATUS_TYPE_POST_CONTRACT_TEXT,
                    StatusType::STATUS_TYPE_COMPLETED => StatusType::STATUS_TYPE_COMPLETED_TEXT,
                    StatusType::STATUS_TYPE_RECOMMENDATION_OF_TENDERER => StatusType::STATUS_TYPE_RECOMMENDATION_OF_TENDERER_TEXT,
                    StatusType::STATUS_TYPE_LIST_OF_TENDERER => StatusType::STATUS_TYPE_LIST_OF_TENDERER_TEXT,
                    StatusType::STATUS_TYPE_CALLING_TENDER => StatusType::STATUS_TYPE_CALLING_TENDER_TEXT,
                    StatusType::STATUS_TYPE_CLOSED_TENDER => StatusType::STATUS_TYPE_CLOSED_TENDER_TEXT
                ];
                break;

            default:    // Others
                $projectStatuses = [];
        }

        return View::make('dashboard.group.show', compact('user', 'dashboardGroup', 'projectStatuses'));
    }

    public function store($id)
    {
        $dashboardGroup = DashboardGroup::findOrFail($id);

        $this->dashboardGroupForm->validate(Input::all());

        $user  = \Confide::user();
        $input = Input::all();

        $dashboardGroup->title = trim($input['title']);

        $dashboardGroup->save();

        \Flash::success(trans('forms.saved'));

        return Redirect::route('dashboard.group.show', [$dashboardGroup->type]);
    }

    public function assignUsers()
    {
        $inputs = Input::all();

        if(!array_key_exists('id', $inputs))
        {
            return Response::json([
                'success' => false,
                'error'   => "No id input"
            ]);
        }

        $dashboardGroup = DashboardGroup::find($inputs['id']);

        if(!$dashboardGroup)
        {
            return Response::json([
                'success' => false,
                'error'   => "Invalid dashboard group id"
            ]);
        }

        try
        {
            if(array_key_exists('users', $inputs) && !empty($inputs['users']))
            {
                $dashboardGroup->users()->sync($inputs['users'], false);
            }

            $success  = true;
            $errorMsg = "";
        }
        catch(\Exception $e)
        {
            $success  = false;
            $errorMsg = $e->getMessage();
        }

        return Response::json([
            'success' => $success,
            'error'   => $errorMsg
        ]);
    }

    public function removeUser($dashboardId, $userId)
    {
        $success = false;

        $dashboardGroup = DashboardGroup::find($dashboardId);
        $user = User::find($userId);

        if($dashboardGroup && $user)
        {
            \DB::table('dashboard_groups_users')
                ->where('user_id', $user->id)
                ->where('dashboard_group_type', $dashboardGroup->type)
                ->delete();
            $success = true;
        }
        
        return Response::json([
            'success' => $success
        ]);
    }

    public function getAssignedUsers($id)
    {
        $id = (int)$id;

        $inputs = Input::all();

        $dashboardGroup = DashboardGroup::findOrFail($id);

        $currentPage = $inputs['page'];
        $pageSize    = $inputs['size'];
        $filters     = isset($inputs['filters']) ? $inputs['filters'] : [];

        $query = \DB::table('users as u')
            ->select('u.id','u.name','u.email','c.name as company_name')
            ->leftJoin('companies as c', function ($join) {
                $join->on('c.id', '=', 'u.company_id')
                    ->where('c.confirmed', '=', true);
            })
            ->whereExists(function ($q) use ($dashboardGroup) {
                $q->select(\DB::raw(1))
                    ->from('dashboard_groups_users as x')
                    ->whereRaw('x.user_id = u.id')
                    ->where('x.dashboard_group_type', $dashboardGroup->type);
            });

        if(!empty($filters))
        {
            foreach($filters as $filter)
            {
                $field = $filter['field'];
                $value = trim($filter['value']);

                switch($field)
                {
                    case 'name':
                        $query->where('u.name', 'ilike', '%' . $value . '%');
                        break;
                    case 'company':
                        $query->where('c.name', 'ilike', '%' . $value . '%');
                        break;
                    case 'email':
                        $query->where('u.email', 'ilike', '%' . $value . '%');
                        break;
                }
            }
        }

        $rowCount = $query->count();

        $queryResults = $query->limit($pageSize)
            ->offset(($currentPage * $pageSize) - $pageSize)
            ->orderBy('c.name', 'ASC')
            ->orderBy('u.name', 'ASC')
            ->get();

        $count = ($currentPage * $pageSize) - $pageSize;
        $data = [];

        foreach($queryResults as $result)
        {
            array_push($data, [
                'indexNo'    => ++$count,
                'id'         => $result->id,
                'name'       => $result->name,
                'email'      => $result->email,
                'company'    => $result->company_name,
                'remove_url' => route('dashboard.group.user.remove', [$dashboardGroup->type, $result->id])
            ]);
        }

        return Response::json([
            'last_page' => ceil((float) ($rowCount / $pageSize)),
            'data'      => $data,
        ]);
    }

    public function getAssignableUsers()
    {
        $inputs = Input::all();

        if(!array_key_exists('id', $inputs))
        {
            return Response::json([]);
        }

        $dashboardGroup = DashboardGroup::findOrFail($inputs['id']);

        $currentPage = $inputs['page'];
        $pageSize    = $inputs['size'];
        $filters     = isset($inputs['filters']) ? $inputs['filters'] : [];

        $query = \DB::table('users AS u')
            ->select('u.id', 'u.name', 'u.email', 'c.name AS company_name')
            ->leftJoin('companies AS c', function ($join) {
                $join->on('c.id', '=', 'u.company_id')
                    ->where('c.confirmed', '=', true);
            })
            ->whereNotExists(function ($q) use ($dashboardGroup) {
                $q->select(\DB::raw(1))
                    ->from('dashboard_groups_users AS x')
                    ->whereRaw('x.user_id = u.id');

                $groupTypes = [];

                switch ($dashboardGroup->type) {
                    case DashboardGroup::TYPE_DEVELOPER:
                    case DashboardGroup::TYPE_MAIN_CONTRACTOR:
                        $groupTypes[] = DashboardGroup::TYPE_DEVELOPER;
                        $groupTypes[] = DashboardGroup::TYPE_MAIN_CONTRACTOR;
                        break;
                    case DashboardGroup::TYPE_E_BIDDING:
                        $groupTypes[] = DashboardGroup::TYPE_E_BIDDING;
                        break;
                }
                if (! empty($groupTypes)) {
                    $q->whereIn('x.dashboard_group_type', $groupTypes);
                }
            })
            ->where('u.confirmed', '=', true)
            ->where('u.account_blocked_status', '=', false);

        if(!empty($filters))
        {
            foreach($filters as $filter)
            {
                $field = $filter['field'];
                $value = trim($filter['value']);

                switch($field)
                {
                    case 'name':
                        $query->where('u.name', 'ilike', '%' . $value . '%');
                        break;
                    case 'company':
                        $query->where('c.name', 'ilike', '%' . $value . '%');
                        break;
                    case 'email':
                        $query->where('u.email', 'ilike', '%' . $value . '%');
                        break;
                }
            }
        }

        $rowCount = $query->count();

        $queryResults = $query->limit($pageSize)
            ->offset(($currentPage * $pageSize) - $pageSize)
            ->orderBy('c.name', 'ASC')
            ->orderBy('u.name', 'ASC')
            ->get();
        
        $count = ($currentPage * $pageSize) - $pageSize;
        $data = [];

        foreach($queryResults as $result)
        {
            array_push($data, [
                'indexNo'               => ++$count,
                'id'                    => $result->id,
                'name'                  => $result->name,
                'email'                 => $result->email,
                'company'               => $result->company_name,
            ]);
        }

        return Response::json([
            'last_page' => ceil((float) ($rowCount / $pageSize)),
            'data'      => $data,
        ]);
    }

    public function getExcludedProjects($id)
    {
        $id = (int)$id;

        $inputs = Input::all();

        $dashboardGroup = DashboardGroup::findOrFail($id);

        $currentPage = $inputs['page'];
        $pageSize    = $inputs['size'];
        $filters     = isset($inputs['filters']) ? $inputs['filters'] : [];

        $currentlyExcludedProjectIds = \DB::table('dashboard_groups_excluded_projects')->where('dashboard_group_type', $dashboardGroup->type)->lists('project_id');
        
        $query = \DB::table('projects AS p')->select('p.id', 'p.title', 'p.reference', 'p.status_id', 's.name AS state', 'c.country AS country')
            ->join('states AS s', 'p.state_id', '=', 's.id')
            ->join('countries AS c', 's.country_id', '=', 'c.id')
            ->join('dashboard_groups_excluded_projects AS x', 'x.project_id', '=', 'p.id')
            ->join('dashboard_groups AS g', 'g.type', '=', 'x.dashboard_group_type')
            ->whereNull('p.deleted_at')
            ->where('g.type', $dashboardGroup->type);

        if(!empty($filters))
        {
            foreach($filters as $filter)
            {
                $field = $filter['field'];
                $value = trim($filter['value']);

                switch($field)
                {
                    case 'reference':
                        $query->where('p.reference', 'ilike', '%' . $value . '%');
                        break;
                    case 'title':
                        $query->where('p.title', 'ilike', '%' . $value . '%');
                        break;
                    case 'status':
                        if((int)$value > 0)
                            $query->where('p.status_id', '=', $value);
                        break;
                    case 'country':
                        $query->where('c.country', 'ilike', '%' . $value . '%');
                        break;
                }
            }
        }

        $rowCount = $query->count();

        $queryResults = $query->limit($pageSize)
            ->offset(($currentPage * $pageSize) - $pageSize)
            ->orderBy('p.title', 'ASC')
            ->get();

        $count = ($currentPage * $pageSize) - $pageSize;
        $data = [];

        foreach($queryResults as $result)
        {
            array_push($data, [
                'indexNo'    => ++$count,
                'id'         => $result->id,
                'title'      => $result->title,
                'reference'  => $result->reference,
                'status'     => Project::getStatusById($result->status_id),
                'country'    => $result->country,
                'show_url'   => route('projects.show', array( $result->id )),
                'remove_url' => route('dashboard.group.project.remove', [$dashboardGroup->type, $result->id])
            ]);
        }

        return Response::json([
            'last_page' => ceil((float) ($rowCount / $pageSize)),
            'data'      => $data,
        ]);
    }

    public function getExcludableProjects()
    {
        $inputs = Input::all();

        if(!array_key_exists('id', $inputs))
        {
            return Response::json([]);
        }

        $dashboardGroup = DashboardGroup::findOrFail($inputs['id']);

        $currentPage = $inputs['page'];
        $pageSize    = $inputs['size'];
        $filters     = isset($inputs['filters']) ? $inputs['filters'] : [];

        $currentlyAssignedProjectIds = \DB::table('dashboard_groups_excluded_projects')->where('dashboard_group_type', $dashboardGroup->type)->lists('project_id');
        
        $query = \DB::table('projects AS p')->select('p.id', 'p.title', 'p.reference', 'p.status_id', 's.name AS state', 'c.country AS country')
            ->join('states AS s', 'p.state_id', '=', 's.id')
            ->join('countries AS c', 's.country_id', '=', 'c.id')
            ->whereNull('p.deleted_at')
            ->whereNotIn('p.id', $currentlyAssignedProjectIds);

        if(!empty($filters))
        {
            foreach($filters as $filter)
            {
                $field = $filter['field'];
                $value = trim($filter['value']);

                switch($field)
                {
                    case 'title':
                        $query->where('p.title', 'ilike', '%' . $value . '%');
                        break;
                    case 'reference':
                        $query->where('p.reference', 'ilike', '%' . $value . '%');
                        break;
                    case 'status':
                        if((int)$value > 0)
                            $query->where('p.status_id', '=', $value);
                        break;
                    case 'country':
                        $query->where('c.country', 'ilike', '%' . $value . '%');
                        break;
                }
            }
        }

        $rowCount = $query->count();

        $queryResults = $query->limit($pageSize)
            ->offset(($currentPage * $pageSize) - $pageSize)
            ->orderBy('p.title', 'ASC')
            ->get();
        
        $count = ($currentPage * $pageSize) - $pageSize;
        $data = [];

        foreach($queryResults as $result)
        {
            array_push($data, [
                'indexNo'   => ++$count,
                'id'        => $result->id,
                'title'     => $result->title,
                'reference' => $result->reference,
                'country'   => $result->country,
                'status'    => Project::getStatusById($result->status_id),
                'show_url'  => route('projects.show', array( $result->id )),
            ]);
        }

        return Response::json([
            'last_page' => ceil((float) ($rowCount / $pageSize)),
            'data'      => $data,
        ]);
    }

    public function excludeProjects()
    {
        $inputs = Input::all();

        if(!array_key_exists('id', $inputs))
        {
            return Response::json([
                'success' => false,
                'error'   => "No id input"
            ]);
        }

        $dashboardGroup = DashboardGroup::find($inputs['id']);

        if(!$dashboardGroup)
        {
            return Response::json([
                'success' => false,
                'error'   => "Invalid dashboard group id"
            ]);
        }

        try
        {
            if(array_key_exists('projects', $inputs) && !empty($inputs['projects']))
            {
                $dashboardGroup->excludedProjects()->sync($inputs['projects'], false);
            }

            $success  = true;
            $errorMsg = "";
        }
        catch(\Exception $e)
        {
            $success  = false;
            $errorMsg = $e->getMessage();
        }

        return Response::json([
            'success' => $success,
            'error'   => $errorMsg
        ]);
    }

    public function removeProject($dashboardId, $projectId)
    {
        $success = false;

        $dashboardGroup = DashboardGroup::find($dashboardId);
        $project = Project::findOrFail($projectId);

        if($dashboardGroup && $project)
        {
            \DB::table('dashboard_groups_excluded_projects')
            ->where('dashboard_group_type', $dashboardId)
            ->where('project_id', $project->id)
            ->delete();

            $success = true;
        }
        
        return Response::json([
            'success' => $success
        ]);
    }
}