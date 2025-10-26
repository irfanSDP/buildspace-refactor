<?php

use PCK\VendorWorkCategory\VendorWorkCategory;
use PCK\VendorWorkSubcategory\VendorWorkSubcategory;
use PCK\WorkCategories\WorkCategory;
use PCK\Forms\VendorWorkCategoryForm;
use PCK\VendorCategory\VendorCategory;

class VendorWorkCategoriesController extends \BaseController {

    protected $vendorWorkCategoryForm;

    public function __construct(VendorWorkCategoryForm $vendorWorkCategoryForm)
    {
        $this->vendorWorkCategoryForm = $vendorWorkCategoryForm;
    }

    public function index()
    {
        $records = VendorWorkCategory::orderBy('name', 'asc')
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

        return View::make('vendor_work_category.index', compact('hiddenFilterOptions', 'recordIds', 'hiddenIds'));
    }

    public function list()
    {
        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $model = VendorWorkCategory::select("vendor_work_categories.id AS id", "vendor_work_categories.name AS name",
        "vendor_work_categories.code AS code", "vendor_work_categories.hidden AS hidden", "contract_group_categories.name AS vendor_group",
        \DB::raw("ARRAY_TO_JSON(ARRAY_AGG(DISTINCT vendor_work_subcategories.name) FILTER (WHERE vendor_work_subcategories.name != '')) AS subcategories"),
        \DB::raw("COALESCE(COUNT(vendor_work_subcategories.id), 0) AS total_subcategories"))
        ->leftJoin('vendor_category_vendor_work_category', 'vendor_work_categories.id', '=', 'vendor_category_vendor_work_category.vendor_work_category_id')
        ->leftJoin('vendor_categories', 'vendor_categories.id', '=', 'vendor_category_vendor_work_category.vendor_category_id')
        ->leftJoin('contract_group_categories', 'contract_group_categories.id', '=', 'vendor_categories.contract_group_category_id')
        ->leftJoin('vendor_work_subcategories', function($join){
            $join->on('vendor_work_subcategories.vendor_work_category_id', '=', 'vendor_work_categories.id');
            $join->on(\DB::raw("vendor_work_subcategories.hidden IS FALSE"), \DB::raw(''), \DB::raw(''));
        });

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
                                $model->where('vendor_work_categories.name', 'ILIKE', '%'.$val.'%');
                            }
                            break;
                        case 'code':
                            if(strlen($val) > 0)
                            {
                                $model->where('vendor_work_categories.code', 'ILIKE', '%'.$val.'%');
                            }
                            break;
                        case 'hidden':
                            if($val == 'h')
                            {
                                $model->whereRaw('vendor_work_categories.hidden IS TRUE');
                            }
                            elseif($val == 's')
                            {
                                $model->whereRaw('vendor_work_categories.hidden IS FALSE');
                            }
                            break;
                        case 'vendor_group':
                            if(strlen($val) > 0)
                            {
                                $model->where('contract_group_categories.name', 'ILIKE', '%'.$val.'%');
                            }
                            break;
                        case 'vendor_categories':
                            if(strlen($val) > 0)
                            {
                                if($val=='(blanks)')
                                {
                                    $model->whereRaw("NOT EXISTS (
                                        SELECT 
                                        FROM vendor_category_vendor_work_category a
                                        WHERE a.vendor_work_category_id = vendor_work_categories.id
                                    )");
                                }
                                else
                                {
                                    $model->where('vendor_categories.name', 'ILIKE', '%'.$val.'%');
                                }
                            }
                            break;
                        case 'subcategories':
                            if(strlen($val) > 0)
                            {
                                $model->where('vendor_work_subcategories.name', 'ILIKE', '%'.$val.'%');
                            }
                            break;
                    }
                }
            }
        }

        $model->orderBy('vendor_work_categories.hidden', 'asc')
        ->orderBy('vendor_work_categories.code', 'asc')
        ->groupBy(\DB::raw('vendor_work_categories.id, contract_group_categories.name'));

        $rowCount = $model->get()->count();

        $records = $model->skip($limit * ($page - 1))
        ->take($limit)
        ->get();
        
        $data = [];

        $vendorCategoryRecords = VendorCategory::select("vendor_categories.id AS id", "vendor_categories.name AS name", "vendor_work_categories.id AS vendor_work_category_id")
        ->join('vendor_category_vendor_work_category', 'vendor_categories.id', '=', 'vendor_category_vendor_work_category.vendor_category_id')
        ->join('vendor_work_categories', 'vendor_work_categories.id', '=', 'vendor_category_vendor_work_category.vendor_work_category_id')
        ->orderBy('vendor_categories.code', 'asc')
        ->orderBy('vendor_categories.name', 'asc')
        ->get();

        $vendorCategories = [];
        foreach($vendorCategoryRecords as $vendorCategoryRecord)
        {
            if(!array_key_exists($vendorCategoryRecord->id, $vendorCategories))
            {
                $vendorCategories[$vendorCategoryRecord->id] = [
                    'name' => $vendorCategoryRecord->name,
                    'work_categories' => []
                ];
            }

            $vendorCategories[$vendorCategoryRecord->id]['work_categories'][] = $vendorCategoryRecord->vendor_work_category_id;
        }

        foreach($records->all() as $key => $record)
        {
            $counter = ($page-1) * $limit + $key + 1;
            $vendorCategoryNames = [];

            foreach($vendorCategories as $vendorCategoryId => $vendorCategory)
            {
                if(in_array($record->id, $vendorCategory['work_categories']))
                {
                    $vendorCategoryNames[] = $vendorCategory['name'];
                }
            }

            $data[] = [
                'id'                            => $record->id,
                'counter'                       => $counter,
                'vendor_group'                  => $record->vendor_group,
                'vendor_categories'             => $vendorCategoryNames,
                'name'                          => $record->name,
                'code'                          => $record->code,
                'hidden'                        => $record->hidden,
                'subcategories'                 => is_null(json_decode($record->subcategories)) ? [] : json_decode($record->subcategories),
                'total_subcategories'           => $record->total_subcategories,
                'route:vendorWorkSubcategories' => route('vendorWorkSubcategories.index', [$record->id]),
                'route:vendor_categories'       => route('vendorWorkCategories.vendorCategories.index', [$record->id]),
                'route:edit'                    => route('vendorWorkCategories.edit', [$record->id])
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
        $workCategories = WorkCategory::orderBy('name', 'asc')->lists('name', 'id');

        $selectedWorkCategories = [];

        $vendorWorkCategory = null;

        return View::make('vendor_work_category.create', compact('vendorWorkCategory', 'workCategories', 'selectedWorkCategories'));
    }

    public function edit($vendorWorkCategoryId)
    {
        $vendorWorkCategory = VendorWorkCategory::findOrFail($vendorWorkCategoryId);

        $workCategories = WorkCategory::orderBy('name', 'asc')->lists('name', 'id');

        $selectedWorkCategories = $vendorWorkCategory->workCategories()->lists('id');

        return View::make('vendor_work_category.create', compact('vendorWorkCategory', 'workCategories', 'selectedWorkCategories'));
    }

    public function store()
    {
        $this->vendorWorkCategoryForm->validate(Input::all());

        $input = Input::all();

        $vendorWorkCategory = VendorWorkCategory::find($input['id']);

        if($vendorWorkCategory)
        {
            $vendorWorkCategory->name = trim($input['name']);
            $vendorWorkCategory->code = trim($input['code']);

            $vendorWorkCategory->save();
        }
        else
        {
            $vendorWorkCategory = VendorWorkCategory::create($input);
        }

        $workCategoryIds = (array_key_exists('work_category_id', $input) && is_array($input['work_category_id'])) ? $input['work_category_id'] : [];

        $oldWorkCategoryIds = $vendorWorkCategory->workCategories()->lists('id');

        $vendorWorkCategory->workCategories()->sync($workCategoryIds);

        $newWorkCategoryIds = $vendorWorkCategory->workCategories()->lists('id');

        sort($oldWorkCategoryIds);
        sort($newWorkCategoryIds);

        if($oldWorkCategoryIds != $newWorkCategoryIds)
        {
            $changedWorkCategoryIds = array_merge(array_diff($oldWorkCategoryIds,$newWorkCategoryIds), array_diff($newWorkCategoryIds, $oldWorkCategoryIds));

            \Event::fire('vendorWorkCategory.workCategoriesUpdated', [$changedWorkCategoryIds]);
        }

        \Flash::success(trans('forms.saved'));

        return Redirect::route('vendorWorkCategories.index');
    }

    public function hide()
    {
        $input = Input::get('id') ?? [];

        VendorWorkCategory::whereIn('id', $input)->update(['hidden' => true]);
        VendorWorkCategory::whereNotIn('id', $input)->update(['hidden' => false]);

        \Flash::success(trans('forms.saved'));

        return Redirect::route('vendorWorkCategories.index');
    }

    public function getVendorWorkCategories()
    {
        $inputs         = Input::all();
        $vendorCategory = VendorCategory::find($inputs['vendorCategoryId']);

        $data = [];

        foreach($vendorCategory->vendorWorkCategories()->orderBy('id', 'ASC')->get() as $vendorWorkCategory)
        {
            array_push($data, [
                'id'          => $vendorWorkCategory->id,
                'description' => $vendorWorkCategory->name,
            ]);
        }

        return Response::json(array(
            'success' => true,
            'default' => null,
            'data'    => $data
        ));
    }

    public function getVendorWorkSubCategories()
    {
        $inputs = Input::all();

        $data = VendorWorkSubcategory::select('id', 'name AS description')
            ->where('vendor_work_category_id', '=', $inputs['vendorWorkCategoryId'])
            ->where('hidden', '=', false)
            ->orderBy('name', 'asc')
            ->get();

        return Response::json(array(
            'success' => true,
            'default' => null,
            'data'    => $data
        ));
    }
}