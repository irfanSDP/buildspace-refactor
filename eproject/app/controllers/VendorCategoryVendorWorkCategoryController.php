<?php

use PCK\ContractGroupCategory\ContractGroupCategory;
use PCK\VendorCategory\VendorCategory;
use PCK\VendorWorkCategory\VendorWorkCategory;
use PCK\VendorPreQualification\Setup;
use PCK\Forms\VendorWorkCategoryForm;

class VendorCategoryVendorWorkCategoryController extends \BaseController {

    protected $vendorWorkCategoryForm;

    public function __construct(VendorWorkCategoryForm $vendorWorkCategoryForm)
    {
        $this->vendorWorkCategoryForm = $vendorWorkCategoryForm;
    }

    public function vendorWorkCategoryIndex($contractGroupCategoryId, $vendorCategoryId)
    {
        $contractGroupCategory = ContractGroupCategory::findOrFail($contractGroupCategoryId);
        $vendorCategory        = VendorCategory::findOrFail($vendorCategoryId);

        $includedIds = $vendorCategory->vendorWorkCategories->lists('id');

        return View::make('vendor_category.vendor_work_categories', compact('contractGroupCategory', 'vendorCategory', 'includedIds'));
    }

    public function vendorWorkCategoryList($contractGroupCategoryId, $vendorCategoryId)
    {
        $contractGroupCategory = ContractGroupCategory::findOrFail($contractGroupCategoryId);
        $vendorCategory = VendorCategory::findOrFail($vendorCategoryId);

        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $model = VendorWorkCategory::select("vendor_work_categories.id AS id", "vendor_work_categories.name",
        "vendor_work_categories.code", "vendor_work_categories.hidden", "vendor_category_vendor_work_category.vendor_work_category_id")
        ->leftJoin('vendor_category_vendor_work_category', function($join) use($vendorCategory){
            $join->on('vendor_category_vendor_work_category.vendor_work_category_id', '=', 'vendor_work_categories.id');
            $join->on('vendor_category_vendor_work_category.vendor_category_id', '=', \DB::raw($vendorCategory->id));
        })
        ->where('vendor_work_categories.hidden', false);
        
        if($request->has('filters'))
        {
            foreach($request->get('filters') as $filters)
            {
                if(!isset($filters['value']) || is_array($filters['value'])) continue;

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
                    case 'vendor_categories':
                        if(strlen($val) > 0)
                        {
                            $vendorWorkCategoryIds = VendorCategory::select('vendor_work_categories.id')
                                ->leftJoin('vendor_category_vendor_work_category as pivot', 'pivot.vendor_category_id', '=', 'vendor_categories.id')
                                ->leftJoin('vendor_work_categories', 'vendor_work_categories.id', '=', 'pivot.vendor_work_category_id')
                                ->where('vendor_categories.name', 'ILIKE', '%'.$val.'%')
                                ->lists('vendor_work_categories.id');

                            $model->whereIn('vendor_work_categories.id', $vendorWorkCategoryIds);
                        }
                        break;
                    case 'included':
                        if($val === "true")
                        {
                            $model->whereNotNull('vendor_category_vendor_work_category.vendor_work_category_id');
                        }
                        elseif($val === "false")
                        {
                            $model->whereNull('vendor_category_vendor_work_category.vendor_work_category_id');
                        }
                        break;
                }
            }
        }

        $model->orderBy(\DB::raw('vendor_category_vendor_work_category.vendor_work_category_id, vendor_work_categories.code'), 'asc');

        $rowCount = $model->get()->count();

        $records = $model->skip($limit * ($page - 1))
        ->take($limit)
        ->get();

        $vendorCategories = VendorCategory::select('vendor_work_categories.id as vendor_work_category_id', 'vendor_categories.name as vendor_category')
            ->leftJoin('vendor_category_vendor_work_category as pivot', 'pivot.vendor_category_id', '=', 'vendor_categories.id')
            ->leftJoin('vendor_work_categories', 'vendor_work_categories.id', '=', 'pivot.vendor_work_category_id')
            ->whereIn('vendor_work_categories.id', $records->lists('id'))
            ->where('vendor_categories.hidden', '=', false)
            ->orderBy('vendor_categories.code')
            ->get();

        $vendorCategoriesByVendorWorkCategory = [];

        foreach($vendorCategories as $vendorCategory)
        {
            if(!array_key_exists($vendorCategory->vendor_work_category_id, $vendorCategoriesByVendorWorkCategory)) $vendorCategoriesByVendorWorkCategory[$vendorCategory->vendor_work_category_id] = [];

            $vendorCategoriesByVendorWorkCategory[$vendorCategory->vendor_work_category_id][] = $vendorCategory->vendor_category;
        }

        $data = [];

        foreach($records->all() as $key => $record)
        {
            $counter = ($page-1) * $limit + $key + 1;

            $data[] = [
                'id'                            => $record->id,
                'counter'                       => $counter,
                'name'                          => $record->name,
                'code'                          => $record->code,
                'hidden'                        => $record->hidden,
                'included'                      => ($record->vendor_work_category_id),
                'vendor_categories'             => $vendorCategoriesByVendorWorkCategory[$record->id] ?? [],
                'route:vendor_category_summary' => route('vendorCategories.vendorWorkCategories.summary.vendorCategories', [$record->id]),
            ];
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }

    public function vendorWorkCategoryInclude($contractGroupCategoryId, $vendorCategoryId)
    {
        $contractGroupCategory = ContractGroupCategory::findOrFail($contractGroupCategoryId);
        $vendorCategory = VendorCategory::findOrFail($vendorCategoryId);

        $workCategoryIds = Input::get('id') ?? [];

        $currentWorkCategoryIds = $vendorCategory->vendorWorkCategories->lists('id');

        $newWorkCategoryIds = array_diff($workCategoryIds, $currentWorkCategoryIds);

        $vendorCategory->vendorWorkCategories()->sync($workCategoryIds);

        foreach($newWorkCategoryIds as $workCategoryId)
        {
            Setup::firstOrCreate(array(
                'vendor_category_id'      => $vendorCategory->id,
                'vendor_work_category_id' => $workCategoryId,
            ));
        }

        \Flash::success(trans('forms.saved'));

        return Redirect::route('vendorCategories.vendorWorkCategories.index', [$contractGroupCategory->id, $vendorCategory->id]);
    }

    public function vendorCategoriesByVendorWorkCategory($vendorWorkCategoryId)
    {
        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $model = \DB::table('vendor_categories as vc')
            ->select('vc.id', 'vc.name', 'vc.code')
            ->join('vendor_category_vendor_work_category as pivot', 'pivot.vendor_category_id', '=', 'vc.id')
            ->where('vc.hidden', '=', false)
            ->where('pivot.vendor_work_category_id', '=', $vendorWorkCategoryId);

        if($request->has('filters'))
        {
            foreach($request->get('filters') as $filters)
            {
                if(!is_array($filters['value']))
                {
                    $val = trim($filters['value']);
                    switch(trim(strtolower($filters['field'])))
                    {
                        case 'name':
                            if(strlen($val) > 0)
                            {
                                $model->where('vc.name', 'ILIKE', '%'.$val.'%');
                            }
                            break;
                        case 'code':
                            if(strlen($val) > 0)
                            {
                                $model->where('vc.code', 'ILIKE', '%'.$val.'%');
                            }
                            break;
                    }
                }
            }
        }

        $model->orderBy('vc.code', 'asc');

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

    public function vendorWorkCategoryStore($contractGroupCategoryId, $vendorCategoryId)
    {
        $input = Input::all();

        $input['id'] = $id = -1;

        $this->vendorWorkCategoryForm->setThrowException(false);
        $this->vendorWorkCategoryForm->validate($input);

        if($this->vendorWorkCategoryForm->success)
        {
            try
            {
                $vendorWorkCategory = VendorWorkCategory::create($input);

                $vendorCategory = VendorCategory::findOrFail($vendorCategoryId);

                $vendorCategory->vendorWorkCategories()->attach($vendorWorkCategory->id);

                $id = $vendorWorkCategory->id;
            }
            catch(\Exception $e)
            {
                \Log::error($e->getMessage());
                \Log::error($e->getTraceAsString());
            }
        }

        return array(
            'id'      => $id,
            'success' => $this->vendorWorkCategoryForm->success,
            'errors'  => $this->vendorWorkCategoryForm->getErrorMessages(),
        );
    }

    public function vendorCategoryIndex($vendorWorkCategoryId)
    {
        $vendorWorkCategory = VendorWorkCategory::findOrFail($vendorWorkCategoryId);

        $includedIds = $vendorWorkCategory->vendorCategories->lists('id');

        return View::make('vendor_work_category.vendor_categories', compact('vendorWorkCategory', 'includedIds'));
    }

    public function vendorCategoryList($vendorWorkCategoryId)
    {
        $vendorWorkCategory = VendorWorkCategory::findOrFail($vendorWorkCategoryId);

        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $model = VendorCategory::select("vendor_categories.id AS id", "vendor_categories.name",
        "vendor_categories.code", "vendor_categories.hidden", "vendor_category_vendor_work_category.vendor_category_id")
        ->leftJoin('vendor_category_vendor_work_category', function($join) use($vendorWorkCategory){
            $join->on('vendor_category_vendor_work_category.vendor_category_id', '=', 'vendor_categories.id');
            $join->on('vendor_category_vendor_work_category.vendor_work_category_id', '=', \DB::raw($vendorWorkCategory->id));
        })
        ->where('vendor_categories.hidden', false);

        if($request->has('filters'))
        {
            foreach($request->get('filters') as $filters)
            {
                if(!isset($filters['value']) || is_array($filters['value'])) continue;

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
                    case 'included':
                        if($val === "true")
                        {
                            $model->whereNotNull('vendor_category_vendor_work_category.vendor_category_id');
                        }
                        elseif($val === "false")
                        {
                            $model->whereNull('vendor_category_vendor_work_category.vendor_category_id');
                        }
                        break;
                }
            }
        }

        $model->orderBy(\DB::raw('vendor_category_vendor_work_category.vendor_category_id, vendor_categories.code'), 'asc');

        $rowCount = $model->get()->count();

        $records = $model->skip($limit * ($page - 1))
        ->take($limit)
        ->get();

        $vendorWorkCategories = VendorWorkCategory::select('vendor_categories.id as vendor_category_id', 'vendor_work_categories.id as vendor_work_category_id', 'vendor_work_categories.name as vendor_work_category')
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
                'included'                           => ($record->vendor_category_id),
                'vendor_work_categories'             => $vendorWorkCategoriesByVendorCategory[$record->id] ?? [],
                'route:vendor_work_category_summary' => route('vendorCategories.summary.vendorWorkCategories', [$record->id]),
            ];
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }

    public function vendorCategoryInclude($vendorWorkCategoryId)
    {
        $vendorWorkCategory = VendorWorkCategory::findOrFail($vendorWorkCategoryId);

        $vendorCategoryIds = Input::get('id') ?? [];

        $currentVendorCategoryIds = $vendorWorkCategory->vendorCategories->lists('id');

        $newVendorCategoryIds = array_diff($vendorCategoryIds, $currentVendorCategoryIds);

        $vendorWorkCategory->vendorCategories()->sync($vendorCategoryIds);

        foreach($newVendorCategoryIds as $vendorCategoryId)
        {
            Setup::firstOrCreate(array(
                'vendor_category_id'      => $vendorCategoryId,
                'vendor_work_category_id' => $vendorWorkCategory->id,
            ));
        }

        \Flash::success(trans('forms.saved'));

        return Redirect::route('vendorWorkCategories.vendorCategories.index', [$vendorWorkCategory->id]);
    }
}