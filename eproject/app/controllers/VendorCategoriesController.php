<?php

use PCK\ContractGroupCategory\ContractGroupCategory;
use PCK\VendorCategory\VendorCategory;
use PCK\VendorWorkCategory\VendorWorkCategory;
use PCK\Forms\VendorCategoryForm;

class VendorCategoriesController extends \BaseController {

    protected $vendorCategoryForm;

    public function __construct(VendorCategoryForm $vendorCategoryForm)
    {
        $this->vendorCategoryForm = $vendorCategoryForm;
    }

    public function index($contractGroupCategoryId)
    {
        $contractGroupCategory = ContractGroupCategory::findOrFail($contractGroupCategoryId);

        $records = VendorCategory::orderBy('name', 'asc')
            ->where('contract_group_category_id', '=', $contractGroupCategory->id)
            ->get();

        $recordIds = [];
        $hiddenIds = [];

        foreach($records as $record)
        {
            $recordIds[] = $record->id;
            if($record->hidden) $hiddenIds[] = $record->id;
        }

        $hiddenFilterOptions = [
            0   => trans('documentManagementFolders.all'),
            'h' => trans('general.hide'),
            's' => trans('general.show')
        ];

        return View::make('vendor_category.index', compact('contractGroupCategory', 'hiddenFilterOptions', 'recordIds', 'hiddenIds'));
    }

    public function list($contractGroupCategoryId)
    {
        $contractGroupCategory = ContractGroupCategory::findOrFail($contractGroupCategoryId);

        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $totalVendors = VendorCategory::select(\DB::raw("COUNT(companies.id) AS total_vendor"), "vendor_categories.id")
        ->where('vendor_categories.contract_group_category_id', '=', $contractGroupCategory->id)
        ->join('company_vendor_category', 'company_vendor_category.vendor_category_id', '=', 'vendor_categories.id')
        ->join('companies', function($join){
            $join->on('companies.contract_group_category_id', '=', 'vendor_categories.contract_group_category_id');
            $join->on('companies.id', '=', 'company_vendor_category.company_id');
        })
        ->groupBy(\DB::raw('vendor_categories.id'))
        ->lists('total_vendor', 'id');

        $model = VendorCategory::select("vendor_categories.id AS id", "vendor_categories.name",
        "vendor_categories.code", "vendor_categories.hidden","vendor_categories.target",
        "vendor_categories.contract_group_category_id")
        ->where('contract_group_category_id', '=', $contractGroupCategory->id);
        
        if($request->has('filters'))
        {
            foreach($request->get('filters') as $filters)
            {
                if(!is_array($filters['value']))//tabulator will send select type filter in form of array upon clicking. we are only interested in single selection
                {
                    $val = trim($filters['value']);
                    switch(trim(strtolower($filters['field'])))
                    {
                        case 'name':
                            if(strlen($val) > 0)
                            {
                                $model->where('vendor_categories.name', 'ILIKE', '%'.$val.'%');
                            }
                            break;
                        case 'code':
                            if(strlen($val) > 0)
                            {
                                $model->where('vendor_categories.code', 'ILIKE', '%'.$val.'%');
                            }
                            break;
                        case 'vendor_work_categories':
                            if(strlen($val) > 0)
                            {
                                $vendorCategoryIds = VendorWorkCategory::select('vendor_categories.id')
                                    ->leftJoin('vendor_category_vendor_work_category as pivot', 'pivot.vendor_work_category_id', '=', 'vendor_work_categories.id')
                                    ->leftJoin('vendor_categories', 'vendor_categories.id', '=', 'pivot.vendor_category_id')
                                    ->where('vendor_work_categories.name', 'ILIKE', '%'.$val.'%')
                                    ->lists('vendor_categories.id');

                                $model->whereIn('vendor_categories.id', $vendorCategoryIds);
                            }
                            break;
                        case 'hidden':
                            if($val == 'h')
                            {
                                $model->whereRaw('vendor_categories.hidden IS TRUE');
                            }
                            elseif($val == 's')
                            {
                                $model->whereRaw('vendor_categories.hidden IS FALSE');
                            }
                            break;
                    }
                }
            }
        }

        $model->orderBy('vendor_categories.hidden', 'asc')
        ->orderBy('vendor_categories.code', 'asc');

        $rowCount = $model->get()->count();

        $records = $model->skip($limit * ($page - 1))
        ->take($limit)
        ->get();

        $vendorWorkCategories = VendorWorkCategory::select('vendor_categories.id as vendor_category_id', 'vendor_work_categories.name as vendor_work_category')
            ->leftJoin('vendor_category_vendor_work_category as pivot', 'pivot.vendor_work_category_id', '=', 'vendor_work_categories.id')
            ->leftJoin('vendor_categories', 'vendor_categories.id', '=', 'pivot.vendor_category_id')
            ->whereIn('vendor_categories.id', $records->lists('id'))
            ->where('vendor_work_categories.hidden', '=', false)
            ->orderBy('vendor_work_categories.code')
            ->get();

        $vendorWorkCategoriesByVendorCategory = [];

        foreach($vendorWorkCategories as $vendorWorkCategory)
        {
            if(!array_key_exists($vendorWorkCategory->vendor_category_id, $vendorWorkCategoriesByVendorCategory)) $vendorWorkCategoriesByVendorCategory[$vendorWorkCategory->vendor_category_id] = [];

            $vendorWorkCategoriesByVendorCategory[$vendorWorkCategory->vendor_category_id][] = $vendorWorkCategory->vendor_work_category;
        }
        
        $data = [];

        foreach($records->all() as $key => $record)
        {
            $counter = ($page-1) * $limit + $key + 1;

            $data[] = [
                'id'                                 => $record->id,
                'counter'                            => $counter,
                'name'                               => $record->name,
                'code'                               => $record->code,
                'hidden'                             => $record->hidden,
                'target'                             => $record->target,
                'vendor_work_categories'             => $vendorWorkCategoriesByVendorCategory[$record->id] ?? [],
                'total_vendor'                       => (array_key_exists($record->id, $totalVendors)) ? $totalVendors[$record->id] : 0,
                'route:vendor_work_categories'       => route('vendorCategories.vendorWorkCategories.index', [$contractGroupCategory->id, $record->id]),
                'route:edit'                         => route('vendorCategories.edit', [$contractGroupCategory->id, $record->id]),
                'route:vendor_work_category_summary' => route('vendorCategories.summary.vendorWorkCategories', [$record->id]),
            ];
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }

    public function create($contractGroupCategoryId)
    {
        $contractGroupCategory = ContractGroupCategory::findOrFail($contractGroupCategoryId);

        return View::make('vendor_category.create', compact('contractGroupCategory'));
    }

    public function store($contractGroupCategoryId)
    {
        $contractGroupCategory = ContractGroupCategory::findOrFail($contractGroupCategoryId);

        $this->vendorCategoryForm->validate(Input::all());

        $input = Input::all();

        $input['contract_group_category_id'] = $contractGroupCategory->id;

        VendorCategory::create($input);

        \Flash::success(trans('forms.saved'));

        return Redirect::route('vendorCategories.index', [$contractGroupCategory->id]);
    }

    public function hide($contractGroupCategoryId)
    {
        $contractGroupCategory = ContractGroupCategory::findOrFail($contractGroupCategoryId);

        $input = Input::get('id') ?? [];

        VendorCategory::whereIn('id', $input)->update(array('hidden' => true));
        VendorCategory::whereNotIn('id', $input)->update(array('hidden' => false));

        \Flash::success(trans('forms.saved'));

        return Redirect::route('vendorCategories.index', [$contractGroupCategory->id]);
    }

    public function edit($contractGroupCategoryId, $vendorCategoryId)
    {
        $vendorGroup = ContractGroupCategory::findOrFail($contractGroupCategoryId);

        $vendorCategory = VendorCategory::findOrFail($vendorCategoryId);

        return View::make('vendor_category.edit', compact('vendorGroup', 'vendorCategory'));
    }

    public function update($contractGroupCategoryId, $vendorCategoryId)
    {
        $contractGroupCategory = ContractGroupCategory::findOrFail($contractGroupCategoryId);
        $vendorCategory = VendorCategory::findOrFail($vendorCategoryId);

        $this->vendorCategoryForm->mode = 'update';

        $this->vendorCategoryForm->validate(Input::all());

        $vendorCategory->name   = trim(Input::get('name'));
        $vendorCategory->code   = trim(Input::get('code'));
        $vendorCategory->target = Input::get('target');

        $vendorCategory->save();

        \Flash::success(trans('forms.saved'));

        return Redirect::route('vendorCategories.index', [$contractGroupCategory->id]);
    }

    public function vendorList($contractGroupCategoryId, $vendorCategoryId)
    {
        $contractGroupCategory = ContractGroupCategory::findOrFail($contractGroupCategoryId);
        $vendorCategory        = VendorCategory::findOrFail($vendorCategoryId);

        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $model = ContractGroupCategory::select("companies.id AS id", "companies.name", "companies.reference_no")
        ->where('type', '=', ContractGroupCategory::TYPE_EXTERNAL)
        ->join('vendor_categories', function($join){
            $join->on('vendor_categories.contract_group_category_id', '=', 'contract_group_categories.id');
            $join->on(\DB::raw("vendor_categories.hidden IS FALSE"), \DB::raw(''), \DB::raw(''));
        })
        ->join('company_vendor_category', 'company_vendor_category.vendor_category_id', '=', 'vendor_categories.id')
        ->join('companies', function($join){
            $join->on('companies.contract_group_category_id', '=', 'vendor_categories.contract_group_category_id');
            $join->on('companies.id', '=', 'company_vendor_category.company_id');
        })
        ->where('contract_group_categories.id', $contractGroupCategory->id)
        ->where('vendor_categories.id', $vendorCategory->id);

        $vendorCodePrefix = getenv('VENDOR_CODE_PREFIX') ? getenv('VENDOR_CODE_PREFIX') : "BSP";
        $vendorCodePadLength = getenv('VENDOR_CODE_PAD_LENGTH') ? getenv('VENDOR_CODE_PAD_LENGTH') : 5;

        if($request->has('filters'))
        {
            foreach($request->get('filters') as $filters)
            {
                $val = trim($filters['value']);
                switch(trim(strtolower($filters['field'])))
                {
                    case 'vendor_code':
                        if(strlen($val) > 0)
                        {
                            $model->where(\DB::raw("'" . $vendorCodePrefix . "' || LPAD(companies.id::text, " . $vendorCodePadLength . ", '0')"), 'ILIKE', '%' . $val . '%');
                        }
                        break;
                    case 'name':
                        if(strlen($val) > 0)
                        {
                            $model->where('companies.name', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                    case 'reference_no':
                        if(strlen($val) > 0)
                        {
                            $model->where('companies.reference_no', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                }
            }
        }

        $model->orderBy('companies.name', 'asc')
        ->groupBy(\DB::raw('companies.id'));

        $rowCount = $model->get()->count();

        $records = $model->skip($limit * ($page - 1))
        ->take($limit)
        ->get();
        
        $data = [];

        foreach($records->all() as $key => $record)
        {
            $counter = ($page-1) * $limit + $key + 1;

            $data[] = [
                'id'           => $record->id,
                'counter'      => $counter,
                'name'         => $record->name,
                'vendor_code'  => $vendorCodePrefix . str_pad($record->id, $vendorCodePadLength, 0, STR_PAD_LEFT),
                'reference_no' => $record->reference_no,
                'route:show'   => route('vendorProfile.show', [$record->id])
            ];
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }

    public function vendorWorkCategories($vendorCategoryId)
    {
        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $model = \DB::table('vendor_work_categories as vwc')
            ->select('vwc.id', 'vwc.name', 'vwc.code')
            ->join('vendor_category_vendor_work_category as pivot', 'pivot.vendor_work_category_id', '=', 'vwc.id')
            ->where('vwc.hidden', '=', false)
            ->where('pivot.vendor_category_id', '=', $vendorCategoryId);

        if($request->has('filters'))
        {
            foreach($request->get('filters') as $filters)
            {
                if(!is_array($filters['value']))//tabulator will send select type filter in form of array upon clicking. we are only interested in single selection
                {
                    $val = trim($filters['value']);
                    switch(trim(strtolower($filters['field'])))
                    {
                        case 'name':
                            if(strlen($val) > 0)
                            {
                                $model->where('vwc.name', 'ILIKE', '%'.$val.'%');
                            }
                            break;
                        case 'code':
                            if(strlen($val) > 0)
                            {
                                $model->where('vwc.code', 'ILIKE', '%'.$val.'%');
                            }
                            break;
                    }
                }
            }
        }

        $model->orderBy('vwc.code', 'asc');

        $rowCount = count($model->get());

        $records = $model->skip($limit * ($page - 1))
        ->take($limit)
        ->get();

        $data = [];

        foreach($records as $key => $record)
        {
            $counter = ($page-1) * $limit + $key + 1;

            $data[] = [
                'id'      => $record->id,
                'counter' => $counter,
                'name'    => $record->name,
                'code'    => $record->code,
            ];
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }
}