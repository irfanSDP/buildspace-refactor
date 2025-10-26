<?php

use PCK\VendorWorkCategory\VendorWorkCategory;
use PCK\VendorWorkSubcategory\VendorWorkSubcategory;
use PCK\Forms\VendorWorkSubcategoryForm;
use PCK\Forms\VendorWorkSubcategoryReassignForm;

class VendorWorkSubcategoriesController extends \BaseController {

    protected $vendorWorkSubcategoryForm;
    protected $vendorWorkSubcategoryReassignForm;

    public function __construct(VendorWorkSubcategoryForm $vendorWorkSubcategoryForm, VendorWorkSubcategoryReassignForm $vendorWorkSubcategoryReassignForm)
    {
        $this->vendorWorkSubcategoryForm = $vendorWorkSubcategoryForm;
        $this->vendorWorkSubcategoryReassignForm = $vendorWorkSubcategoryReassignForm;
    }

    public function index($vendorWorkCategoryId)
    {
        $vendorWorkCategory = VendorWorkCategory::findOrFail($vendorWorkCategoryId);

        $records = VendorWorkSubcategory::where('vendor_work_category_id', '=', $vendorWorkCategory->id)
            ->orderBy('name', 'asc')
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

        return View::make('vendor_work_subcategory.index', compact('vendorWorkCategory', 'hiddenFilterOptions', 'recordIds', 'hiddenIds'));
    }

    public function list($vendorWorkCategoryId)
    {
        $vendorWorkCategory = VendorWorkCategory::findOrFail($vendorWorkCategoryId);

        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $model = VendorWorkSubcategory::select("vendor_work_subcategories.id AS id", "vendor_work_subcategories.name AS name",
        "vendor_work_subcategories.code AS code", "vendor_work_subcategories.hidden AS hidden",
        "vendor_work_subcategories.vendor_work_category_id");

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
                                $model->where('vendor_work_subcategories.name', 'ILIKE', '%'.$val.'%');
                            }
                            break;
                        case 'code':
                            if(strlen($val) > 0)
                            {
                                $model->where('vendor_work_subcategories.code', 'ILIKE', '%'.$val.'%');
                            }
                            break;
                        case 'hidden':
                            if($val == 'h')
                            {
                                $model->whereRaw('vendor_work_subcategories.hidden IS TRUE');
                            }
                            elseif($val == 's')
                            {
                                $model->whereRaw('vendor_work_subcategories.hidden IS FALSE');
                            }
                            break;
                    }
                }
            }
        }

        $model->where('vendor_work_subcategories.vendor_work_category_id', $vendorWorkCategory->id)
        ->orderBy('vendor_work_subcategories.hidden', 'asc')
        ->orderBy('vendor_work_subcategories.code', 'asc');

        $rowCount = $model->get()->count();

        $records = $model->skip($limit * ($page - 1))
        ->take($limit)
        ->get();
        
        $data = [];

        foreach($records->all() as $key => $record)
        {
            $counter = ($page-1) * $limit + $key + 1;

            $data[] = [
                'id'             => $record->id,
                'counter'        => $counter,
                'name'           => $record->name,
                'code'           => $record->code,
                'hidden'         => $record->hidden,
                'route:edit'     => route('vendorWorkSubcategories.edit', [$vendorWorkCategory->id, $record->id]),
                'route:reassign' => route('vendorWorkSubcategories.reassign', [$vendorWorkCategory->id, $record->id])
            ];
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }

    public function create($vendorWorkCategoryId)
    {
        $vendorWorkCategory = VendorWorkCategory::findOrFail($vendorWorkCategoryId);

        return View::make('vendor_work_subcategory.create', compact('vendorWorkCategory'));
    }

    public function edit($vendorWorkCategoryId, $vendorWorkSubcategoryId)
    {
        $vendorWorkCategory = VendorWorkCategory::findOrFail($vendorWorkCategoryId);
        $vendorWorkSubcategory = VendorWorkSubcategory::findOrFail($vendorWorkSubcategoryId);

        return View::make('vendor_work_subcategory.create', compact('vendorWorkCategory', 'vendorWorkSubcategory'));
    }

    public function store($vendorWorkCategoryId)
    {
        $vendorWorkCategory = VendorWorkCategory::findOrFail($vendorWorkCategoryId);

        $this->vendorWorkSubcategoryForm->validate(Input::all());

        $input = Input::all();

        $vendorWorkSubcategory = VendorWorkSubcategory::find($input['id']);

        if($vendorWorkSubcategory)
        {
            $vendorWorkSubcategory->name                    = trim($input['name']);
            $vendorWorkSubcategory->code                    = trim($input['code']);
            $vendorWorkSubcategory->vendor_work_category_id = $vendorWorkCategory->id;

            $vendorWorkSubcategory->save();
        }
        else
        {
            $input['vendor_work_category_id'] = $vendorWorkCategory->id;

            VendorWorkSubcategory::create($input);
        }

        \Flash::success(trans('forms.saved'));

        return Redirect::route('vendorWorkSubcategories.index', [$vendorWorkCategory->id]);
    }

    public function reassign($vendorWorkCategoryId, $vendorWorkSubcategoryId)
    {
        $vendorWorkCategory = VendorWorkCategory::findOrFail($vendorWorkCategoryId);
        $vendorWorkSubcategory = VendorWorkSubcategory::findOrFail($vendorWorkSubcategoryId);

        $vendorWorkCategories = VendorWorkCategory::where('id', '<>', $vendorWorkSubcategory->vendor_work_category_id)
        ->whereRaw("hidden IS FALSE")
        ->orderBy('code', 'asc')
        ->get();

        return View::make('vendor_work_subcategory.reassign', compact('vendorWorkSubcategory', 'vendorWorkCategories'));
    }

    public function reassignStore($vendorWorkCategoryId)
    {
        $vendorWorkCategory = VendorWorkCategory::findOrFail($vendorWorkCategoryId);

        $this->vendorWorkSubcategoryReassignForm->validate(Input::all());

        $input = Input::all();

        $vendorWorkSubcategory = VendorWorkSubcategory::findOrFail($input['id']);
        $newVendorWorkCategory = VendorWorkCategory::findOrFail($input['vendor_work_category_id']);

        $duplicateCodeAndNameVendorWorkSubcategory = VendorWorkSubcategory::where('vendor_work_category_id', $newVendorWorkCategory->id)
        ->where("name", $vendorWorkSubcategory->name)
        ->where("code", $vendorWorkSubcategory->code)
        ->count();

        if($duplicateCodeAndNameVendorWorkSubcategory > 0)
        {
            \Flash::error('There is already Vendor Work Subcategory with the same name and code under Vendor Work Category '.$vendorWorkCategory->code);

            return Redirect::route('vendorWorkSubcategories.index', [$vendorWorkCategory->id]);
        }

        if($vendorWorkSubcategory)
        {
            $vendorWorkSubcategory->vendor_work_category_id = $newVendorWorkCategory->id;

            $vendorWorkSubcategory->save();
        }

        \Flash::success(trans('forms.saved'));

        return Redirect::route('vendorWorkSubcategories.index', [$vendorWorkCategory->id]);
    }

    public function hide($vendorWorkCategoryId)
    {
        $vendorWorkCategory = VendorWorkCategory::findOrFail($vendorWorkCategoryId);

        $input = Input::get('id') ?? [];

        VendorWorkSubcategory::where('vendor_work_category_id', $vendorWorkCategory->id)
        ->whereIn('id', $input)->update(['hidden' => true]);
        VendorWorkSubcategory::where('vendor_work_category_id', $vendorWorkCategory->id)
        ->whereNotIn('id', $input)->update(['hidden' => false]);
        
        \Flash::success(trans('forms.saved'));

        return Redirect::route('vendorWorkSubcategories.index', [$vendorWorkCategory->id]);
    }
}