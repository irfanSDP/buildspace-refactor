<?php

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

use PCK\ContractGroupCategory\ContractGroupCategory;
use PCK\VendorCategory\VendorCategory;
use PCK\Forms\VendorGroupForm;
use PCK\Helpers\StringOperations;

class ExternalVendorGroupsController extends \BaseController {

    protected $vendorGroupForm;

    public function __construct(VendorGroupForm $vendorGroupForm)
    {
        $this->vendorGroupForm = $vendorGroupForm;
    }

    public function index()
    {
        $records = ContractGroupCategory::orderBy('name', 'asc')
            ->where('type', '=', ContractGroupCategory::TYPE_EXTERNAL)
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

        $hiddenFilterOptions = [
            0   => trans('documentManagementFolders.all'),
            'h' => trans('general.hide'),
            's' => trans('general.show')
        ];

        $vendorTypes = ContractGroupCategory::getVendorTypes();

        return View::make('vendor_groups.external.index', compact('hiddenFilterOptions', 'recordIds', 'hiddenIds', 'defaultBuildspaceAccessIds', 'vendorTypes'));
    }

    public function list()
    {
        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $totalVendors = ContractGroupCategory::select(\DB::raw("COUNT(DISTINCT(companies.id)) AS total_vendor"), "contract_group_categories.id")
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
        ->groupBy(\DB::raw('contract_group_categories.id'))
        ->lists('total_vendor', 'id');

        $model = ContractGroupCategory::select("contract_group_categories.id AS id", "contract_group_categories.name", "contract_group_categories.vendor_type",
        "contract_group_categories.code", "contract_group_categories.hidden", "contract_group_categories.default_buildspace_access",
        \DB::raw("COALESCE(SUM(vendor_categories.target), 0) AS total_target"))
        ->where('type', '=', ContractGroupCategory::TYPE_EXTERNAL)
        ->leftJoin('vendor_categories', function($join){
            $join->on('vendor_categories.contract_group_category_id', '=', 'contract_group_categories.id');
            $join->on(\DB::raw("vendor_categories.hidden IS FALSE"), \DB::raw(''), \DB::raw(''));
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
                                $model->where('contract_group_categories.name', 'ILIKE', '%'.$val.'%');
                            }
                            break;
                        case 'code':
                            if(strlen($val) > 0)
                            {
                                $model->where('contract_group_categories.code', 'ILIKE', '%'.$val.'%');
                            }
                            break;
                        case 'hidden':
                            if($val == 'h')
                            {
                                $model->whereRaw('contract_group_categories.hidden IS TRUE');
                            }
                            elseif($val == 's')
                            {
                                $model->whereRaw('contract_group_categories.hidden IS FALSE');
                            }
                            break;
                        case 'vendor_type':
                            if(strlen($val) > 0)
                            {
                                $model->where('contract_group_categories.vendor_type', '=', $val);
                            }
                    }
                }
            }
        }
        $model->orderBy('contract_group_categories.hidden', 'asc')
        ->orderBy('contract_group_categories.code', 'asc')
        ->groupBy(\DB::raw('contract_group_categories.id'));

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
                'default_buildspace_access' => $record->default_buildspace_access,
                'hidden'                    => $record->hidden,
                'target'                    => $record->total_target,
                'total_vendor'              => (array_key_exists($record->id, $totalVendors)) ? $totalVendors[$record->id] : 0,
                'vendor_type'               => ContractGroupCategory::getVendorTypes($record->vendor_type),
                'route:vendorCategories'    => route('vendorCategories.index', [$record->id]),
                'route:edit'                => route('vendorGroups.external.edit', [$record->id])
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
        $vendorTypes = ContractGroupCategory::getVendorTypes();

        return View::make('vendor_groups.external.create', compact('vendorTypes'));
    }

    public function edit($contractGroupCategoryId)
    {
        $contractGroupCategory = ContractGroupCategory::findOrFail($contractGroupCategoryId);
        $vendorTypes           = ContractGroupCategory::getVendorTypes();

        return View::make('vendor_groups.external.create', compact('contractGroupCategory', 'vendorTypes'));
    }

    public function store()
    {
        $input = Input::all();

        $this->vendorGroupForm->validate($input);

        $contractGroup = ContractGroupCategory::find($input['id']);

        if($contractGroup)
        {
            $contractGroup->name        = trim($input['name']);
            $contractGroup->code        = trim($input['code']);
            $contractGroup->vendor_type = trim($input['vendor_type']);

            $contractGroup->save();
        }
        else
        {
            $input['type'] = ContractGroupCategory::TYPE_EXTERNAL;
            
            ContractGroupCategory::create($input);
        }

        \Flash::success(trans('forms.saved'));

        return Redirect::route('vendorGroups.external.index');
    }

    public function updateSettings()
    {
        $idsToHide = Input::get('hide-id') ?? [];

        ContractGroupCategory::where('type', '=', ContractGroupCategory::TYPE_EXTERNAL)->whereIn('id', $idsToHide)->update(array('hidden' => true));
        ContractGroupCategory::where('type', '=', ContractGroupCategory::TYPE_EXTERNAL)->whereNotIn('id', $idsToHide)->update(array('hidden' => false));

        if(\Confide::user()->isSuperAdmin())
        {
            $idsToHaveDefaultBuildspaceAccess = Input::get('buildspace-access-id') ?? [];

            ContractGroupCategory::where('type', '=', ContractGroupCategory::TYPE_EXTERNAL)->whereIn('id', $idsToHaveDefaultBuildspaceAccess)->update(array('default_buildspace_access' => true));
            ContractGroupCategory::where('type', '=', ContractGroupCategory::TYPE_EXTERNAL)->whereNotIn('id', $idsToHaveDefaultBuildspaceAccess)->update(array('default_buildspace_access' => false));
        }

        \Flash::success(trans('forms.saved'));

        return Redirect::route('vendorGroups.external.index');
    }

    public function exportExcel()
    {
        $request = Request::instance();

        $structures = \DB::table(\DB::raw('contract_group_categories AS cg'))
        ->select(\DB::raw("
            CASE
                WHEN LENGTH(wc.name) > 0
                THEN wc.code
                ELSE vc.code
            END AS code"),
            \DB::raw("vc.id AS vendor_category_id, wc.id AS vendor_work_category_id"),
            \DB::raw("cg.name AS level_1, vc.name AS level_2, wc.name AS level_3")
        )
        ->join(\DB::raw('vendor_categories AS vc'), 'cg.id', '=', 'vc.contract_group_category_id')
        ->leftJoin(\DB::raw("vendor_category_vendor_work_category AS x"), function($join){
            $join->on('x.vendor_category_id', '=', 'vc.id');
            $join->on("vc.hidden", \DB::raw('IS'), \DB::raw('FALSE'));
        })
        ->leftJoin(\DB::raw("vendor_work_categories AS wc"), function($join){
            $join->on('x.vendor_work_category_id', '=', 'wc.id');
            $join->on("wc.hidden", \DB::raw('IS'), \DB::raw('FALSE'));
        })
        ->whereRaw("cg.hidden IS FALSE AND vc.hidden IS FALSE")
        ->whereIn("cg.id", $request->get('ids'))
        ->groupBy(\DB::raw('cg.id, vc.id, wc.id'))
        ->orderBy(\DB::raw('cg.name, vc.name, code'))
        ->get();

        $categories = \DB::table(\DB::raw('contract_group_categories AS cg'))
        ->select(\DB::raw("
            CASE
                WHEN LENGTH(wsc.name) > 0
                THEN wsc.code
                ELSE wc.code
            END AS code"),
            \DB::raw("wc.id AS vendor_work_category_id, wsc.id AS vendor_work_subcategory_id"),
            \DB::raw("wc.name AS level_3, wsc.name AS level_4")
        )
        ->join(\DB::raw('vendor_categories AS vc'), 'cg.id', '=', 'vc.contract_group_category_id')
        ->join(\DB::raw("vendor_category_vendor_work_category AS x"), function($join){
            $join->on('x.vendor_category_id', '=', 'vc.id');
            $join->on("vc.hidden", \DB::raw('IS'), \DB::raw('FALSE'));
        })
        ->join(\DB::raw("vendor_work_categories AS wc"), function($join){
            $join->on('x.vendor_work_category_id', '=', 'wc.id');
            $join->on("wc.hidden", \DB::raw('IS'), \DB::raw('FALSE'));
        })
        ->leftJoin(\DB::raw("vendor_work_subcategories AS wsc"), function($join){
            $join->on('wsc.vendor_work_category_id', '=', 'wc.id');
            $join->on("wsc.hidden", \DB::raw('IS'), \DB::raw('FALSE'));
        })
        ->whereRaw("cg.hidden IS FALSE AND vc.hidden IS FALSE")
        ->whereIn("cg.id", $request->get('ids'))
        ->groupBy(\DB::raw('wc.id, wsc.id'))
        ->orderBy(\DB::raw('code, wc.name, wsc.name'))
        ->get();

        $vendorWorkCategories = [];
        foreach($categories as $category)
        {
            if(!array_key_exists($category->vendor_work_category_id, $vendorWorkCategories))
            {
                $vendorWorkCategories[$category->vendor_work_category_id] = [];
            }

            $vendorWorkCategories[$category->vendor_work_category_id][] = $category;
        }

        unset($categories);

        $records = [];
        foreach($structures as $structure)
        {
            $records[] = [
                'code'    => $structure->code,
                'level_1' => $structure->level_1,
                'level_2' => $structure->level_2,
                'level_3' => $structure->level_3,
                'level_4' => null,
            ];

            if($structure->vendor_work_category_id && array_key_exists($structure->vendor_work_category_id, $vendorWorkCategories))
            {
                foreach($vendorWorkCategories[$structure->vendor_work_category_id] as $vendorWorkCategory)
                {
                    $records[] = [
                        'code'    => $vendorWorkCategory->code,
                        'level_1' => $structure->level_1,
                        'level_2' => $structure->level_2,
                        'level_3' => $vendorWorkCategory->level_3,
                        'level_4' => $vendorWorkCategory->level_4,
                    ];
                }
            }
        }

        unset($structures, $vendorWorkCategories);

        $spreadsheet = new Spreadsheet();
        $spreadsheet->getProperties()->setCreator("Buildspace");

        $activeSheet = $spreadsheet->getActiveSheet();
        $activeSheet->setTitle("Company Categories");

        $activeSheet->setAutoFilter('A1:E1');

        $headerStyle = [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'ffffff']
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '187bcd']
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER
            ]
        ];

        $headers = [
            'code'    => "Vendor Category Code",
            'level_1' => "Level 1",
            'level_2' => "Level 2",
            'level_3' => "Level 3",
            'level_4' => "Level 4"
        ];

        $headerCount = 1;
        foreach($headers as $key => $val)
        {
            $cell = StringOperations::numberToAlphabet($headerCount)."1";
            $activeSheet->setCellValue($cell, $val);
            $activeSheet->getStyle($cell)->applyFromArray($headerStyle);

            $activeSheet->getColumnDimension(StringOperations::numberToAlphabet($headerCount))->setAutoSize(true);
            
            $headerCount++;
        }

        $row = 2;

        foreach($records as $record)
        {
            $headerCount = 1;
            foreach($headers as $key => $val)
            {
                $cell = StringOperations::numberToAlphabet($headerCount).$row;
                $activeSheet->setCellValue($cell, $record[$key]);

                $activeSheet->getStyle($cell)->getAlignment()->setWrapText(true);

                $headerCount++;
            }

            $row++;
        }

        $writer = new Xlsx($spreadsheet);

        $filepath = \PCK\Helpers\Files::getTmpFileUri();

        $writer->save($filepath);

        $filename = 'Company_Categories-'.date("dmYHis");

        return \PCK\Helpers\Files::download($filepath, "{$filename}.".\PCK\Helpers\Files::EXTENSION_EXCEL);
    }

    public function vendorList($contractGroupCategoryId)
    {
        $contractGroupCategory = ContractGroupCategory::findOrFail($contractGroupCategoryId);

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
        ->where('contract_group_categories.id', $contractGroupCategory->id);

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
}