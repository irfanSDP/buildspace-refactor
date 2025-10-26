<?php
use PCK\ContractGroupCategory\ContractGroupCategory;
use PCK\ConsultantManagement\ConsultantManagementContract;
use PCK\ConsultantManagement\DevelopmentType;
use PCK\ConsultantManagement\ProductType;
use PCK\Forms\ConsultantManagement\RolesForm;
use PCK\Forms\ConsultantManagement\DevelopmentTypeForm;
use PCK\Forms\ConsultantManagement\ProductTypeForm;

class ConsultantManagementMaintenanceController extends \BaseController
{
    private $rolesForm;
    private $productTypeForm;
    private $developmentTypeForm;

    public function __construct(RolesForm $rolesForm, DevelopmentTypeForm $developmentTypeForm, ProductTypeForm $productTypeForm)
    {
        $this->rolesForm = $rolesForm;
        $this->productTypeForm = $productTypeForm;
        $this->developmentTypeForm = $developmentTypeForm;
    }

    public function roles()
    {
        $roles = [
            ConsultantManagementContract::ROLE_RECOMMENDATION_OF_CONSULTANT => trans('general.recommendationOfConsultant'),
            ConsultantManagementContract::ROLE_LIST_OF_CONSULTANT => trans('general.listOfConsultant'),
            ConsultantManagementContract::ROLE_CONSULTANT => trans('vendorManagement.consultant')
        ];

        $groupCategories = ContractGroupCategory::where('hidden', '=', false)->orderBy('name', 'asc')->lists('name', 'id');

        $rolesGroupCategories = \DB::table('consultant_management_roles_contract_group_categories')->select('role', 'contract_group_category_id')->get();

        $selectedGroupCategories = [];

        foreach($rolesGroupCategories as $roleGroupCategory)
        {
            if(!array_key_exists($roleGroupCategory->role, $selectedGroupCategories))
            {
                $selectedGroupCategories[$roleGroupCategory->role] = [];
            }

            $selectedGroupCategories[$roleGroupCategory->role][] = $roleGroupCategory->contract_group_category_id;
        }

        return View::make('consultant_management.maintenance.roles.index', compact('roles', 'groupCategories', 'selectedGroupCategories'));
    }

    public function rolesStore()
    {
        $this->rolesForm->validate(Input::all());

        $inputs = Input::all();

        \DB::table('consultant_management_roles_contract_group_categories')->truncate();

        $user = \Confide::user();
        $data = [];

        foreach($inputs['roles'] as $roleId)
        {
            foreach($inputs['group_categories'][$roleId] as $groupCategoryId)
            {
                $data[] = [
                    'role' => $roleId,
                    'contract_group_category_id' => $groupCategoryId,
                    'created_by' => $user->id,
                    'updated_by' => $user->id,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ];
            }
        }

        \DB::table('consultant_management_roles_contract_group_categories')->insert($data);

        \Flash::success(trans('forms.saved'));

        return Redirect::route('consultant.management.maintenance.roles.index');
    }

    public function developmentType()
    {
        return View::make('consultant_management.maintenance.development_types.index');
    }

    public function developmentTypeList()
    {
        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $model = DevelopmentType::select("development_types.id AS id", "development_types.title AS title")
        ->join('development_types_product_types', 'development_types.id', '=', 'development_types_product_types.development_type_id')
        ->join('product_types', 'product_types.id', '=', 'development_types_product_types.product_type_id');

        if($request->has('filters'))
        {
            foreach($request->get('filters') as $filters)
            {
                if(!is_array($filters['value']))//tabulator will send select type filter in form of array upon clicking. we are only interested in single selection
                {
                    $val = trim($filters['value']);
                    switch(trim(strtolower($filters['field'])))
                    {
                        case 'title':
                            if(strlen($val) > 0)
                            {
                                $model->where('development_types.title', 'ILIKE', '%'.$val.'%');
                            }
                            break;
                        case 'product_types':
                            if(strlen($val) > 0)
                            {
                                $model->where('product_types.title', 'ILIKE', '%'.$val.'%');
                            }
                            break;
                    }
                }
            }
        }

        $model->groupBy('development_types.id')->orderBy('development_types.title', 'asc');

        $rowCount = $model->get()->count();

        $records = $model->skip($limit * ($page - 1))
        ->take($limit)
        ->get();
        
        $data = [];

        $productTypeRecords = ProductType::select("product_types.id AS id", "product_types.title AS title", "development_types.id AS development_type_id")
        ->join('development_types_product_types', 'product_types.id', '=', 'development_types_product_types.product_type_id')
        ->join('development_types', 'development_types.id', '=', 'development_types_product_types.development_type_id')
        ->orderBy('product_types.title', 'asc')
        ->get();

        $productTypes = [];
        foreach($productTypeRecords as $productTypeRecord)
        {
            if(!array_key_exists($productTypeRecord->development_type_id, $productTypes))
            {
                $productTypes[$productTypeRecord->development_type_id] = [];
            }

            $productTypes[$productTypeRecord->development_type_id][] = $productTypeRecord->title;
        }

        foreach($records->all() as $key => $record)
        {
            $counter = ($page-1) * $limit + $key + 1;

            $data[] = [
                'id'            => $record->id,
                'counter'       => $counter,
                'product_types' => (array_key_exists($record->id, $productTypes)) ? $productTypes[$record->id] : [],
                'title'         => $record->title,
                'deletable'     => $record->deletable(),
                'route:edit'    => route('consultant.management.maintenance.development.type.edit', [$record->id]),
                'route:delete'  => route('consultant.management.maintenance.development.type.delete', [$record->id])
            ];
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }

    public function developmentTypeCreate()
    {
        $productTypes = ProductType::orderBy('title', 'asc')->lists('title', 'id');

        $selectedProductTypes = [];

        $developmentType = null;

        return View::make('consultant_management.maintenance.development_types.edit', compact('developmentType', 'productTypes', 'selectedProductTypes'));
    }

    public function developmentTypeEdit($developmentTypeId)
    {
        $developmentType = DevelopmentType::findOrFail($developmentTypeId);

        $productTypes = ProductType::orderBy('title', 'asc')->lists('title', 'id');

        $selectedProductTypes = $developmentType->productTypes()->lists('id');

        return View::make('consultant_management.maintenance.development_types.edit', compact('developmentType', 'productTypes', 'selectedProductTypes'));
    }

    public function developmentTypeStore()
    {
        $this->developmentTypeForm->validate(Input::all());

        $user  = \Confide::user();
        $input = Input::all();

        $developmentType = DevelopmentType::find($input['id']);

        if(!$developmentType)
        {
            $developmentType = new DevelopmentType();

            $developmentType->created_by = $user->id;
        }

        $developmentType->title      = trim($input['title']);
        $developmentType->updated_by = $user->id;

        $developmentType->save();

        $productTypeIds = (array_key_exists('product_type_id', $input) && is_array($input['product_type_id'])) ? $input['product_type_id'] : [];

        \DB::table('development_types_product_types')->where('development_type_id', '=', $developmentType->id)->delete();

        if(!empty($productTypeIds))
        {
            $productTypeIds = ProductType::whereIn('id', $productTypeIds)->lists('id');

            $data = [];
            foreach($productTypeIds as $productTypeId)
            {
                $data[] = [
                    'development_type_id' => $developmentType->id,
                    'product_type_id' => $productTypeId,
                    'created_by' => $user->id,
                    'updated_by' => $user->id,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ];
            }

            \DB::table('development_types_product_types')->insert($data);
        }

        \Flash::success(trans('forms.saved'));

        return Redirect::route('consultant.management.maintenance.development.type.index');
    }

    public function developmentTypeDelete($developmentTypeId)
    {
        $developmentType = DevelopmentType::findOrFail($developmentTypeId);
        $user = \Confide::user();

        if($developmentType->deletable())
        {
            $developmentType->delete();

            \Log::info("Delete development type [development type id: {$developmentTypeId}] [user id:{$user->id}]");
        }

        \Flash::success(trans('forms.deleted'));

        return Redirect::route('consultant.management.maintenance.development.type.index');
    }

    public function productType()
    {
        return View::make('consultant_management.maintenance.product_types.index');
    }

    public function productTypeList()
    {
        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $model = ProductType::select("product_types.id AS id", "product_types.title AS title")
        ->leftJoin('development_types_product_types', 'product_types.id', '=', 'development_types_product_types.product_type_id')
        ->leftJoin('development_types', 'development_types.id', '=', 'development_types_product_types.development_type_id');

        if($request->has('filters'))
        {
            foreach($request->get('filters') as $filters)
            {
                if(!is_array($filters['value']))//tabulator will send select type filter in form of array upon clicking. we are only interested in single selection
                {
                    $val = trim($filters['value']);
                    switch(trim(strtolower($filters['field'])))
                    {
                        case 'title':
                            if(strlen($val) > 0)
                            {
                                $model->where('product_types.title', 'ILIKE', '%'.$val.'%');
                            }
                            break;
                        case 'development_types':
                            if(strlen($val) > 0)
                            {
                                $model->where('development_types.title', 'ILIKE', '%'.$val.'%');
                            }
                            break;
                    }
                }
            }
        }

        $model->groupBy('product_types.id')->orderBy('product_types.title', 'asc');

        $rowCount = $model->get()->count();

        $records = $model->skip($limit * ($page - 1))
        ->take($limit)
        ->get();
        
        $data = [];

        $developmentTypeRecords = DevelopmentType::select("development_types.id AS id", "development_types.title AS title", "product_types.id AS product_type_id")
        ->join('development_types_product_types', 'development_types.id', '=', 'development_types_product_types.development_type_id')
        ->join('product_types', 'product_types.id', '=', 'development_types_product_types.product_type_id')
        ->orderBy('development_types.title', 'asc')
        ->get();

        $developmentTypes = [];
        foreach($developmentTypeRecords as $developmentTypeRecord)
        {
            if(!array_key_exists($developmentTypeRecord->product_type_id, $developmentTypes))
            {
                $developmentTypes[$developmentTypeRecord->product_type_id] = [];
            }

            $developmentTypes[$developmentTypeRecord->product_type_id][] = $developmentTypeRecord->title;
        }

        foreach($records->all() as $key => $record)
        {
            $counter = ($page-1) * $limit + $key + 1;

            $data[] = [
                'id'                => $record->id,
                'counter'           => $counter,
                'development_types' => (array_key_exists($record->id, $developmentTypes)) ? $developmentTypes[$record->id] : [],
                'title'             => $record->title,
                'deletable'         => $record->deletable(),
                'route:edit'        => route('consultant.management.maintenance.product.type.edit', [$record->id]),
                'route:delete'      => route('consultant.management.maintenance.product.type.delete', [$record->id])
            ];
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }

    public function productTypeCreate()
    {
        $developmentTypes = DevelopmentType::orderBy('title', 'asc')->lists('title', 'id');

        $selectedDevelopmentTypes = [];

        $productType = null;

        return View::make('consultant_management.maintenance.product_types.edit', compact('productType', 'developmentTypes', 'selectedDevelopmentTypes'));
    }

    public function productTypeEdit($productTypeId)
    {
        $productType = ProductType::findOrFail($productTypeId);

        $developmentTypes = DevelopmentType::orderBy('title', 'asc')->lists('title', 'id');

        $selectedDevelopmentTypes =  $productType->developmentTypes()->lists('id');

        return View::make('consultant_management.maintenance.product_types.edit', compact('productType', 'developmentTypes', 'selectedDevelopmentTypes'));
    }

    public function productTypeStore()
    {
        $this->productTypeForm->validate(Input::all());

        $user  = \Confide::user();
        $input = Input::all();

        $productType = ProductType::find($input['id']);

        if(!$productType)
        {
            $productType = new ProductType();

            $productType->created_by = $user->id;
        }

        $productType->title      = trim($input['title']);
        $productType->updated_by = $user->id;

        $productType->save();

        $developmentTypeIds = (array_key_exists('development_type_id', $input) && is_array($input['development_type_id'])) ? $input['development_type_id'] : [];

        \DB::table('development_types_product_types')->where('product_type_id', '=', $productType->id)->delete();

        if(!empty($developmentTypeIds))
        {
            $developmentTypeIds = DevelopmentType::whereIn('id', $developmentTypeIds)->lists('id');

            $data = [];
            foreach($developmentTypeIds as $developmentTypeId)
            {
                $data[] = [
                    'development_type_id' => $developmentTypeId,
                    'product_type_id' => $productType->id,
                    'created_by' => $user->id,
                    'updated_by' => $user->id,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ];
            }

            \DB::table('development_types_product_types')->insert($data);
        }

        \Flash::success(trans('forms.saved'));

        return Redirect::route('consultant.management.maintenance.product.type.index');
    }

    public function productTypeDelete($productTypeId)
    {
        $productType = ProductType::findOrFail($productTypeId);
        $user = \Confide::user();

        if($productType->deletable())
        {
            $productType->delete();

            \Log::info("Delete product type [product type id: {$productTypeId}] [user id:{$user->id}]");
        }

        \Flash::success(trans('forms.deleted'));

        return Redirect::route('consultant.management.maintenance.product.type.index');
    }
}