<?php

use PCK\ContractGroupCategory\ContractGroupCategory;
use PCK\Forms\VendorGroupForm;

class InternalVendorGroupsController extends \BaseController {

    protected $vendorGroupForm;

    public function __construct(VendorGroupForm $vendorGroupForm)
    {
        $this->vendorGroupForm = $vendorGroupForm;
    }

    public function index()
    {
        $records = ContractGroupCategory::orderBy('name', 'asc')
            ->where('type', '=', ContractGroupCategory::TYPE_INTERNAL)
            ->get();

        $recordIds = [];
        $hiddenIds = [];
        $defaultBuildspaceAccessIds = [];

        foreach($records as $record)
        {
            $recordIds[] = $record->id;
            if($record->hidden) $hiddenIds[] = $record->id;
            if($record->default_buildspace_access) $defaultBuildspaceAccessIds[] = $record->id;
        }

        return View::make('vendor_groups.internal.index', compact('recordIds', 'hiddenIds', 'defaultBuildspaceAccessIds'));
    }

    public function list()
    {
        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $model = ContractGroupCategory::select("contract_group_categories.id AS id", "contract_group_categories.name", "contract_group_categories.default_buildspace_access",
        "contract_group_categories.code", "contract_group_categories.hidden")
        ->where('type', '=', ContractGroupCategory::TYPE_INTERNAL);

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
                            $model->where('contract_group_categories.name', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                    case 'code':
                        if(strlen($val) > 0)
                        {
                            $model->where('contract_group_categories.code', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                }
            }
        }

        $model->orderBy('contract_group_categories.code', 'asc');

        $rowCount = $model->get()->count();

        $records = $model->skip($limit * ($page - 1))
        ->take($limit)
        ->get();
        
        $data = [];

        foreach($records->all() as $key => $record)
        {
            $counter = ($page-1) * $limit + $key + 1;

            $data[] = [
                'id'                        => $record->id,
                'counter'                   => $counter,
                'name'                      => $record->name,
                'code'                      => $record->code,
                'hidden'                    => $record->hidden,
                'default_buildspace_access' => $record->default_buildspace_access,
                'route:edit'                => route('vendorGroups.internal.edit', [$record->id])
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
        return View::make('vendor_groups.internal.create');
    }

    public function edit($contractGroupCategoryId)
    {
        $contractGroupCategory = ContractGroupCategory::findOrFail($contractGroupCategoryId);

        return View::make('vendor_groups.internal.create', compact('contractGroupCategory'));
    }

    public function store()
    {
        $input = Input::all();

        $this->vendorGroupForm->validate($input);

        $contractGroup = ContractGroupCategory::find($input['id']);

        if($contractGroup)
        {
            $contractGroup->name = trim($input['name']);
            $contractGroup->code = trim($input['code']);

            $contractGroup->save();
        }
        else
        {
            $input['type'] = ContractGroupCategory::TYPE_INTERNAL;
            
            ContractGroupCategory::create($input);
        }

        \Flash::success(trans('forms.saved'));

        return Redirect::route('vendorGroups.internal.index');
    }

    public function updateSettings()
    {
        $idsToHide = Input::get('hide-id') ?? [];

        ContractGroupCategory::where('type', '=', ContractGroupCategory::TYPE_INTERNAL)->whereIn('id', $idsToHide)->update(array('hidden' => true));
        ContractGroupCategory::where('type', '=', ContractGroupCategory::TYPE_INTERNAL)->whereNotIn('id', $idsToHide)->update(array('hidden' => false));

        if(\Confide::user()->isSuperAdmin())
        {
            $idsToHaveDefaultBuildspaceAccess = Input::get('buildspace-access-id') ?? [];

            ContractGroupCategory::where('type', '=', ContractGroupCategory::TYPE_INTERNAL)->whereIn('id', $idsToHaveDefaultBuildspaceAccess)->update(array('default_buildspace_access' => true));
            ContractGroupCategory::where('type', '=', ContractGroupCategory::TYPE_INTERNAL)->whereNotIn('id', $idsToHaveDefaultBuildspaceAccess)->update(array('default_buildspace_access' => false));
        }

        \Flash::success(trans('forms.saved'));

        return Redirect::route('vendorGroups.internal.index');
    }
}