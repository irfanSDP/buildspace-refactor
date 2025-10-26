<?php

use PCK\Buildspace\CostData;
use PCK\Buildspace\CostDataProject;
use PCK\Buildspace\CostDataType;
use PCK\Buildspace\Region;
use PCK\Buildspace\Currency;
use PCK\Forms\CostDataForm;
use PCK\Helpers\DataTables;
use PCK\Subsidiaries\SubsidiaryRepository;
use PCK\Subsidiaries\Subsidiary;
use PCK\Users\User;
use PCK\Countries\CountryRepository;
use PCK\Projects\Project;
use PCK\Notifications\EmailNotifier;
use PCK\ModulePermission\ModulePermission;

class CostDataController extends Controller {

    private $form;
    private $subsidiaryRepository;
    protected $countryRepository;
    protected $emailNotifier;

    public function __construct(CostDataForm $form, SubsidiaryRepository $subsidiaryRepository, CountryRepository $countryRepository, EmailNotifier $emailNotifier)
    {
        $this->form                 = $form;
        $this->subsidiaryRepository = $subsidiaryRepository;
        $this->countryRepository    = $countryRepository;
        $this->emailNotifier        = $emailNotifier;
    }

    public function index()
    {
        $user = Confide::user();

        $costDataRecords = $user->getVisibleCostData();

        return View::make('costData.index', compact('costDataRecords'));
    }

    public function list()
    {
        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $user = Confide::user();

        $costDatas = $user->getVisibleCostData();

        $costDataIds = $costDatas->lists('id');

        $model = CostData::select('bs_cost_data.id', 'bs_cost_data.name', 'bs_master_cost_data.name as master_cost_data', \DB::raw('EXTRACT(YEAR FROM bs_cost_data.tender_date) as tender_year'), \DB::raw('EXTRACT(YEAR FROM bs_cost_data.award_date) as award_year'), 'bs_sf_guard_user_profile.name as created_by', 'bs_cost_data.subsidiary_id')
        ->join('bs_master_cost_data', 'bs_master_cost_data.id', '=', 'bs_cost_data.master_cost_data_id')
        ->join('bs_sf_guard_user', 'bs_sf_guard_user.id', '=', 'bs_cost_data.created_by')
        ->join('bs_sf_guard_user_profile', 'bs_sf_guard_user_profile.user_id', '=', 'bs_cost_data.created_by')
        ->whereIn('bs_cost_data.id', $costDataIds);

        $subsidiaryIds = $model->lists('subsidiary_id');

        if($request->has('filters'))
        {
            foreach($request->get('filters') as $filters)
            {
                $val = trim($filters['value']);

                switch(trim(strtolower($filters['field'])))
                {
                    case 'name':
                        if(strlen($val) > 0)
                        {
                            $model->where('bs_cost_data.name', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                    case 'master_cost_data':
                        if(strlen($val) > 0)
                        {
                            $model->where('bs_master_cost_data.name', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                    case 'tender_year':
                        if(strlen($val) > 0)
                        {
                            $model->where(DB::raw('CAST(bs_cost_data.tender_date AS VARCHAR)'), 'LIKE', '%'.$val.'%');
                        }
                        break;
                    case 'award_year':
                        if(strlen($val) > 0)
                        {
                            $model->where(DB::raw('CAST(bs_cost_data.award_date AS VARCHAR)'), 'LIKE', '%'.$val.'%');
                        }
                        break;
                    case 'created_by':
                        if(strlen($val) > 0)
                        {
                            $model->where('bs_sf_guard_user_profile.name', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                    case 'business_unit':
                        if(strlen($val) > 0)
                        {
                            $subsidiaryIds = Subsidiary::select('id')
                                ->whereNull('parent_id')
                                ->where('name', 'ILIKE', '%'.$val.'%')
                                ->lists('id');

                            $matchingRootSubsidiariesAndDescendants = Subsidiary::getSelfAndDescendantIds($subsidiaryIds);

                            $selfAndDescendantSubsidiaryIds = [];

                            foreach($matchingRootSubsidiariesAndDescendants as $descendantIds)
                            {
                                $selfAndDescendantSubsidiaryIds = array_merge($selfAndDescendantSubsidiaryIds, $descendantIds);
                            }

                            $model->whereIn('bs_cost_data.subsidiary_id', $selfAndDescendantSubsidiaryIds);
                        }
                        break;
                }
            }
        }

        $model->orderBy('bs_cost_data.created_at', 'desc');

        $rowCount = $model->get()->count();

        $records = $model->skip($limit * ($page - 1))
        ->take($limit)
        ->get();

        $data = [];

        $isModuleEditor = ModulePermission::isEditor($user, ModulePermission::MODULE_ID_COST_DATA);

        $rootSubsidiaries = Subsidiary::getTopParentsGroupedBySubsidiaryIds($records->lists('subsidiary_id'));

        foreach($records->all() as $key => $record)
        {
            $counter = ($page-1) * $limit + $key + 1;

            $data[] = [
                'id'                => $record->id,
                'counter'           => $counter,
                'name'              => $record->name,
                'master_cost_data'  => $record->master_cost_data,
                'tender_year'       => $record->tender_year,
                'award_year'        => $record->award_year,
                'app_link'          => CostData::generateAppLink($record->id),
                'show_app_link'     => \PCK\General\ObjectPermission::isAssigned($user, $costDatas->find($record->id)),
                'route:users'       => route('costData.users', array($record->id)),
                'show_route:users'  => $isModuleEditor,
                'route:edit'        => route('costData.edit', array($record->id)),
                'show_route:edit'   => $isModuleEditor,
                'route:show'        => route('costData.show', array($record->id)),
                'route:delete'      => route('costData.delete', array($record->id)),
                'show_route:delete' => $isModuleEditor,
                'created_by'        => $record->created_by,
                'business_unit'     => $rootSubsidiaries[$record->subsidiary_id]['name'],
            ];
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }

    public function create()
    {
        $masterCostDataRecords = \PCK\Buildspace\MasterCostData::orderBy('created_at', 'desc')->lists('name', 'id');

        $subsidiaries = $this->subsidiaryRepository->getHierarchicalCollection()->lists('fullName', 'id');

        $types = CostDataType::orderBy('name', 'asc')->lists('name', 'id');

        $currencies = \DB::connection('buildspace')
            ->table('bs_currency')
            ->select(\DB::raw('id, currency_code'))
            ->orderBy('id', 'asc')
            ->lists('currency_code', 'id');


        $subsidiaryId  = Input::old('subsidiary_id');
        $projectIds    = Input::old('project_id');
        $currencyId    = Input::old('currency_id') ?? Currency::getDefault()->id;
        $urlRegions    = route('costData.options.regions');
        $urlSubregions = route('costData.options.subregions');

        JavaScript::put(compact('subsidiaryId', 'projectIds', 'urlRegions', 'urlSubregions', 'currencyId'));

        $projectStatuses = [
            0 => trans('general.all'),
            Project::STATUS_TYPE_DESIGN => Project::STATUS_TYPE_DESIGN_TEXT,
            Project::STATUS_TYPE_POST_CONTRACT => Project::STATUS_TYPE_POST_CONTRACT_TEXT,
            Project::STATUS_TYPE_COMPLETED => Project::STATUS_TYPE_COMPLETED_TEXT,
            Project::STATUS_TYPE_RECOMMENDATION_OF_TENDERER => Project::STATUS_TYPE_RECOMMENDATION_OF_TENDERER_TEXT,
            Project::STATUS_TYPE_LIST_OF_TENDERER => Project::STATUS_TYPE_LIST_OF_TENDERER_TEXT,
            Project::STATUS_TYPE_CALLING_TENDER => Project::STATUS_TYPE_CALLING_TENDER_TEXT,
            Project::STATUS_TYPE_CLOSED_TENDER => Project::STATUS_TYPE_CLOSED_TENDER_TEXT
        ];

        return View::make('costData.create', compact('masterCostDataRecords', 'subsidiaries', 'types', 'currencies', 'projectStatuses'));
    }

    public function store()
    {
        $input = Input::all();

        $this->form->validate($input);

        $user = Confide::user();

        $costData = CostData::create(array(
            'name'                => $input['name'],
            'master_cost_data_id' => $input['master_cost_data_id'],
            'subsidiary_id'       => $input['subsidiary_id'],
            'cost_data_type_id'   => $input['cost_data_type_id'],
            'region_id'           => $input['region_id'],
            'subregion_id'        => $input['subregion_id'],
            'currency_id'         => $input['currency_id'],
            'tender_date'         => empty($input['tender_date']) ? null : \Carbon\Carbon::parse("{$input['tender_date']}-01-01"),
            'award_date'          => empty($input['award_date']) ? null : \Carbon\Carbon::parse("{$input['award_date']}-01-01"),
            'created_by'          => $user->getBsUser()->id,
            'updated_by'          => $user->getBsUser()->id
        ));

        CostDataProject::syncEProjectProjects($costData, Input::get('project_id') ?? array());

        $eProjectCostData = $costData->getEProjectCostData();

        $eProjectCostData->notes = $input['notes'] ?? null;

        $eProjectCostData->save();

        return Redirect::route('costData');
    }

    public function show($costDataId)
    {
        $costData = CostData::find($costDataId);

        return View::make('costData.show', compact('costData'));
    }

    public function edit($costDataId)
    {
        $costData = CostData::find($costDataId);

        $masterCostDataRecords = \PCK\Buildspace\MasterCostData::orderBy('created_at', 'desc')->lists('name', 'id');

        $subsidiaries = $this->subsidiaryRepository->getHierarchicalCollection()->lists('fullName', 'id');

        $types = CostDataType::orderBy('name', 'asc')->lists('name', 'id');

        $currencies = \DB::connection('buildspace')
            ->table('bs_currency')
            ->select(\DB::raw('id, currency_code'))
            ->orderBy('id', 'asc')
            ->lists('currency_code', 'id');

        $subsidiaryId  = Input::old('subsidiary_id', $costData->getSubsidiary()->id);
        $currencyId    = Input::old('currency_id', $costData->currency_id);
        $regionId      = Input::old('region_id', $costData->region_id);
        $subregionId   = Input::old('subregion_id', $costData->subregion_id);
        $projectIds    = Input::old('project_id', $costData->e_project_projects->lists('id'));
        $urlRegions    = route('costData.options.regions');
        $urlSubregions = route('costData.options.subregions');

        $relevantSubsidiaryIds = Subsidiary::getSelfAndDescendantIds([$subsidiaryId])[$subsidiaryId];

        $notSelectedProjects = Project::select('projects.id', 'projects.title', 'projects.reference', 'projects.status_id', 'countries.country', 'states.name as state')
            ->join('countries', 'countries.id', '=', 'projects.country_id')
            ->join('states', 'states.id', '=', 'projects.state_id')
            ->whereIn('subsidiary_id', $relevantSubsidiaryIds)
            ->whereNotIn('projects.id', $projectIds)
            ->get();

        if ($notSelectedProjects->count() > 0) {
            $notSelectedProjectIds = $notSelectedProjects->lists('id');
        } else {
            $notSelectedProjectIds = [];
        }

        JavaScript::put(compact('subsidiaryId', 'projectIds', 'notSelectedProjectIds', 'currencyId', 'regionId', 'subregionId', 'urlRegions', 'urlSubregions'));

        $projectStatuses = [
            0 => trans('general.all'),
            Project::STATUS_TYPE_DESIGN => Project::STATUS_TYPE_DESIGN_TEXT,
            Project::STATUS_TYPE_POST_CONTRACT => Project::STATUS_TYPE_POST_CONTRACT_TEXT,
            Project::STATUS_TYPE_COMPLETED => Project::STATUS_TYPE_COMPLETED_TEXT,
            Project::STATUS_TYPE_RECOMMENDATION_OF_TENDERER => Project::STATUS_TYPE_RECOMMENDATION_OF_TENDERER_TEXT,
            Project::STATUS_TYPE_LIST_OF_TENDERER => Project::STATUS_TYPE_LIST_OF_TENDERER_TEXT,
            Project::STATUS_TYPE_CALLING_TENDER => Project::STATUS_TYPE_CALLING_TENDER_TEXT,
            Project::STATUS_TYPE_CLOSED_TENDER => Project::STATUS_TYPE_CLOSED_TENDER_TEXT
        ];

        return View::make('costData.edit', compact('costData', 'masterCostDataRecords', 'subsidiaries', 'types', 'currencies', 'projectStatuses'));
    }

    public function update($costDataId)
    {
        $user = Confide::user();

        $this->form->ignoreUnique($costDataId);

        $this->form->validate(Input::all());

        $costData                      = CostData::find($costDataId);
        $costData->master_cost_data_id = Input::get('master_cost_data_id');
        $costData->name                = Input::get('name');
        $costData->subsidiary_id       = Input::get('subsidiary_id');
        $costData->cost_data_type_id   = Input::get('cost_data_type_id');
        $costData->region_id           = Input::get('region_id');
        $costData->subregion_id        = Input::get('subregion_id');
        $costData->currency_id         = Input::get('currency_id');
        $costData->tender_date         = empty(Input::get('tender_date')) ? null : \Carbon\Carbon::parse(Input::get('tender_date')."-01-01");
        $costData->award_date          = empty(Input::get('award_date')) ? null : \Carbon\Carbon::parse(Input::get('award_date')."-01-01");
        $costData->updated_by          = $user->getBsUser()->id;
        $costData->save();

        CostDataProject::syncEProjectProjects($costData, Input::get('project_id') ?? array());

        $eProjectCostData = $costData->getEProjectCostData();

        $eProjectCostData->notes      = Input::get('notes') ?? null;

        $eProjectCostData->save();

        return Redirect::route('costData');
    }

    public function usersIndex($costDataId)
    {
        $costData             = CostData::find($costDataId);
        $assignRoute          = route('costData.users.update', array( $costDataId ));
        $assignableUsersRoute = route('costData.users.assignable', array( $costDataId ));
        $assignedUsersRoute   = route('costData.users.assigned', array( $costDataId ));

        return View::make('costData.assignUsers', compact('costData', 'costData', 'assignRoute', 'assignableUsersRoute', 'assignedUsersRoute'));
    }

    public function getAssignedUsers($costDataId)
    {
        $costData = CostData::find($costDataId);

        $input         = Input::all();
        $idColumn      = 'users.id';
        $selectColumns = array( $idColumn, 'users.name' );

        $userColumns = array(
            'name'  => 1,
            'email' => 2
        );

        $companyColumns = array(
            'name' => 3
        );

        $allColumns = array(
            'users'     => $userColumns,
            'companies' => $companyColumns
        );

        $currentlyAssignedUserIds = \PCK\General\ObjectPermission::getUserList($costData)->lists('user_id');

        $query = \DB::table("users as users");

        $datatable = new DataTables($query, $input, $allColumns, $idColumn, $selectColumns);

        $datatable->properties->query->join('companies', 'companies.id', '=', 'users.company_id');
        $datatable->properties->query->where('companies.confirmed', '=', true);
        $datatable->properties->query->whereIn('users.id', $currentlyAssignedUserIds);

        $datatable->addAllStatements();

        $results = $datatable->getResults();

        $dataArray = array();

        foreach($results as $arrayIndex => $arrayItem)
        {
            $indexNo = ( $arrayIndex + 1 ) + ( $datatable->properties->pagingOffset );
            $record  = User::find($arrayItem->id);

            $dataArray[] = array(
                'indexNo'         => $indexNo,
                'id'              => $record->id,
                'name'            => $record->name,
                'email'           => $record->email,
                'companyName'     => $record->company->name,
                'route:revoke'    => route('costData.users.revoke', array( $costDataId, $record->id )),
                'route:setEditor' => route('costData.users.editor.toggle', array( $costDataId, $record->id )),
                'route:sendNotification' => route('costData.users.resendNotification', array( $costDataId, $record->id )),
                'isEditor'        => \PCK\General\ObjectPermission::isEditor($record, $costData),
            );
        }

        return $datatable->dataTableResponse($dataArray);
    }

    public function getAssignableUsers($costDataId)
    {
        $costData = CostData::find($costDataId);

        $relevantProjects = $costData->e_project_projects;

        $relevantUserIds = ModulePermission::where('module_identifier', '=', ModulePermission::MODULE_ID_COST_DATA)->lists('user_id');

        foreach($relevantProjects as $project)
        {
            $relevantUserIds += $project->getProjectUsers()->lists('id', 'id');
        }

        $input         = Input::all();
        $idColumn      = 'users.id';
        $selectColumns = array( $idColumn, 'users.name' );

        $userColumns = array(
            'name'  => 1,
            'email' => 2
        );

        $companyColumns = array(
            'name' => 3
        );

        $allColumns = array(
            'users'     => $userColumns,
            'companies' => $companyColumns
        );

        $currentlyAssignedUserIds = \PCK\General\ObjectPermission::getUserList($costData)->lists('user_id');

        $query = \DB::table("users as users");

        $datatable = new DataTables($query, $input, $allColumns, $idColumn, $selectColumns);

        $datatable->properties->query->join('companies', 'companies.id', '=', 'users.company_id');
        $datatable->properties->query->where('companies.confirmed', '=', true);
        $datatable->properties->query->whereNotIn('users.id', $currentlyAssignedUserIds);
        $datatable->properties->query->where('users.confirmed', '=', true);
        $datatable->properties->query->where('users.account_blocked_status', '=', false);
        $datatable->properties->query->whereIn('users.id', $relevantUserIds);

        $datatable->addAllStatements();

        $results = $datatable->getResults();

        $dataArray = array();

        foreach($results as $arrayIndex => $arrayItem)
        {
            $indexNo = ( $arrayIndex + 1 ) + ( $datatable->properties->pagingOffset );
            $record  = User::find($arrayItem->id);

            $dataArray[] = array(
                'indexNo'     => $indexNo,
                'id'          => $record->id,
                'name'        => $record->name,
                'email'       => $record->email,
                'companyName' => $record->company->name,
            );
        }

        return $datatable->dataTableResponse($dataArray);
    }

    public function assignUsers($costDataId)
    {
        $userIds = Input::get('users') ?? array();

        $costData = CostData::find($costDataId);

        foreach($userIds as $userId)
        {
            \PCK\General\ObjectPermission::grant(User::find($userId), $costData);
        }

        $this->emailNotifier->sendAssignedToCostDataNotification($costDataId, $userIds);

        return Response::json(array(
            'success' => true,
        ));
    }

    public function toggleEditorStatus($costDataId, $userId)
    {
        $costData = CostData::find($costDataId);

        $user = \PCK\Users\User::find($userId);

        $success = \PCK\General\ObjectPermission::setAsEditor($user, $costData, ! \PCK\General\ObjectPermission::isEditor($user, $costData));

        return Response::json(array(
            'success' => $success,
        ));
    }

    public function revoke($costDataId, $userId)
    {
        $costData = CostData::find($costDataId);

        $success = \PCK\General\ObjectPermission::revoke(User::find($userId), $costData);

        return Response::json(array(
            'success' => $success,
        ));
    }

    public function delete($costDataId)
    {
        $costData = CostData::find($costDataId);

        try
        {
            $costData->delete();

            Flash::success(trans('costData.deleted', array( 'costData' => $costData->name )));
        }
        catch(Exception $e)
        {
            Flash::error(trans('costData.cannotBeDeleted', array( 'costData' => $costData->name )));
        }

        return Redirect::back();
    }

    public function listProjects()
    {
        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $projectIds = $request->has('projectIds') ? $request->get('projectIds') : [];

        $relevantSubsidiaryIds = Subsidiary::getSelfAndDescendantIds([$request->get('subsidiaryId')])[$request->get('subsidiaryId')];

        $model = Project::select('projects.id', 'projects.title', 'projects.reference', 'projects.status_id', 'countries.country', 'states.name as state')
            ->join('countries', 'countries.id', '=', 'projects.country_id')
            ->join('states', 'states.id', '=', 'projects.state_id')
            ->whereIn('subsidiary_id', $relevantSubsidiaryIds);

        $data = [];
        $totalPages = 0;

        if (! empty($projectIds)) 
        {
            $model->whereIn('projects.id', $projectIds);

            if($request->has('filters'))
            {
                foreach($request->get('filters') as $filters)
                {
                    if(is_array($filters['value']) or strlen($filters['value'])==0)
                    {
                        continue;
                    }

                    $val = trim($filters['value']);

                    switch(trim(strtolower($filters['field'])))
                    {
                        case 'reference':
                            if(strlen($val) > 0)
                            {
                                $model->where('projects.reference', 'ILIKE', '%'.$val.'%');
                            }
                            break;
                        case 'title':
                            if(strlen($val) > 0)
                            {
                                $model->where('projects.title', 'ILIKE', '%'.$val.'%');
                            }
                            break;
                        case 'country':
                            if(strlen($val) > 0)
                            {
                                $model->where('countries.country', 'ILIKE', '%'.$val.'%');
                            }
                            break;
                        case 'state':
                            if(strlen($val) > 0)
                            {
                                $model->where('states.name', 'ILIKE', '%'.$val.'%');
                            }
                            break;
                        case 'status':
                            if((int)$val > 0)
                            {
                                $model->where('projects.status_id', (int)$val);
                            }
                            break;
                    }
                }
            }

            $model->orderBy('projects.title', 'asc');

            $rowCount = $model->get()->count();

            $records = $model->skip($limit * ($page - 1))
            ->take($limit)
            ->get();

            foreach($records->all() as $key => $record)
            {
                $counter = ($page-1) * $limit + $key + 1;

                $data[] = [
                    'id'        => $record->id,
                    'counter'   => $counter,
                    'reference' => $record->reference,
                    'title'     => $record->title,
                    'country'   => $record->country,
                    'state'     => $record->state,
                    'status'    => Project::getStatusById($record->status_id),
                ];
            }

            $totalPages = ceil( $rowCount / $limit );
        }

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }

    public function listProjectOptions()
    {
        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $projectIds = $request->has('projectIds') ? $request->get('projectIds') : [];
        $notSelectedProjectIds = $request->has('notSelectedProjectIds') ? $request->get('notSelectedProjectIds') : [];

        $costDataId = $request->has('costDataId') ? $request->get('costDataId') : -1;

        $subsidiaryId = $request->has('subsidiaryId') ? $request->get('subsidiaryId') : null;
        $relevantSubsidiaryIds = ! empty($subsidiaryId) ? Subsidiary::getSelfAndDescendantIds([$subsidiaryId])[$subsidiaryId] : [];

        $model = Project::select('projects.id', 'projects.title', 'projects.reference', 'projects.status_id', 'countries.country', 'states.name as state')
            ->join('countries', 'countries.id', '=', 'projects.country_id')
            ->join('states', 'states.id', '=', 'projects.state_id')
            ->whereIn('subsidiary_id', $relevantSubsidiaryIds)
            ->whereNotIn('projects.id', $projectIds);

        if ($costDataId > 0) {
            $projectIdsTiedToCostData = CostDataProject::join('bs_project_structures', 'bs_project_structures.id', '=', 'bs_cost_data_project.project_structure_id')
                ->join('bs_project_main_information', 'bs_project_main_information.project_structure_id', '=', 'bs_project_structures.id')
                ->join('bs_cost_data', 'bs_cost_data.id', '=', 'bs_cost_data_project.cost_data_id')
                ->where('bs_cost_data_project.cost_data_id', '!=', $costDataId)
                ->lists('bs_project_main_information.eproject_origin_id');

            if (! empty($notSelectedProjectIds)) {
                $projectIdsTiedToCostData = array_diff($projectIdsTiedToCostData, $notSelectedProjectIds);
            }

            $model->whereNotIn('projects.id', $projectIdsTiedToCostData);
        }

        if($request->has('filters'))
        {
            foreach($request->get('filters') as $filters)
            {
                if(is_array($filters['value']) or strlen($filters['value'])==0)
                {
                    continue;
                }

                $val = trim($filters['value']);

                switch(trim(strtolower($filters['field'])))
                {
                    case 'reference':
                        if(strlen($val) > 0)
                        {
                            $model->where('projects.reference', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                    case 'title':
                        if(strlen($val) > 0)
                        {
                            $model->where('projects.title', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                    case 'country':
                        if(strlen($val) > 0)
                        {
                            $model->where('countries.country', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                    case 'state':
                        if(strlen($val) > 0)
                        {
                            $model->where('states.name', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                    case 'status':
                        if((int)$val > 0)
                        {
                            $model->where('projects.status_id', (int)$val);
                        }
                        break;
                }
            }
        }

        $model->orderBy('projects.title', 'asc');

        $rowCount = $model->get()->count();

        $records = $model->skip($limit * ($page - 1))
        ->take($limit)
        ->get();

        $data = [];

        foreach($records->all() as $key => $record)
        {
            $counter = ($page-1) * $limit + $key + 1;

            $data[] = [
                'id'        => $record->id,
                'counter'   => $counter,
                'reference' => $record->reference,
                'title'     => $record->title,
                'country'   => $record->country,
                'state'     => $record->state,
                'status'    => Project::getStatusById($record->status_id),
            ];
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }

    public function getRegionOptions()
    {
        $default = Region::getDefault();

        $data = \DB::connection('buildspace')
            ->table('bs_regions')
            ->select(\DB::raw('id, country as description'))
            ->orderBy('id', 'asc')
            ->get();

        return Response::json(array(
            'success' => true,
            'default' => $default->id,
            'data'    => $data
        ));
    }

    public function getSubregionOptions($id = null)
    {
        $regionId = $id ? : Input::get('regionId');

        $data = \DB::connection('buildspace')
            ->table('bs_subregions')
            ->select(\DB::raw('id, name as description'))
            ->where('region_id', '=', $regionId)
            ->orderBy('id', 'asc')
            ->get();

        return Response::json(array(
            'data'    => $data
        ));
    }

    public function resendNotification($costDataId, $userId)
    {
        $success = false;

        $costData = CostData::find($costDataId);

        $currentlyAssignedUserIds = \PCK\General\ObjectPermission::getUserList($costData)->lists('user_id');

        if(in_array($userId, $currentlyAssignedUserIds))
        {
            $this->emailNotifier->sendAssignedToCostDataNotification($costDataId, [$userId]);
            $success = true;
        }

        return Response::json(array(
            'success' => $success,
        ));
    }

}