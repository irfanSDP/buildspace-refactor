<?php

use PCK\ContractGroupCategory\ContractGroupCategory;
use PCK\ModuleParameters\VendorManagement\VendorManagementGrade;
use PCK\ModuleParameters\VendorManagement\VendorManagementGradeLevel;
use PCK\VendorPreQualification\VendorGroupGrade;

class VendorPreQualificationVendorGroupGradesController extends \BaseController {

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        return View::make('vendor_pre_qualification.grades.index');
    }

    public function list()
    {
        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $model = ContractGroupCategory::select("contract_group_categories.id", "contract_group_categories.name", "vendor_management_grades.name as grade_name", "vendor_pre_qualification_vendor_group_grades.id as vendor_group_grade_id", "vendor_pre_qualification_vendor_group_grades.vendor_management_grade_id")
        ->where('contract_group_categories.type', '=', ContractGroupCategory::TYPE_EXTERNAL)
        ->where('contract_group_categories.hidden', '=', false)
        ->leftJoin('vendor_pre_qualification_vendor_group_grades', function($join)
        {
            $join->on('vendor_pre_qualification_vendor_group_grades.contract_group_category_id', '=', 'contract_group_categories.id')
                ->whereNull('vendor_pre_qualification_vendor_group_grades.deleted_at');
        })
        ->leftJoin('vendor_management_grades', 'vendor_management_grades.id', '=', 'vendor_pre_qualification_vendor_group_grades.vendor_management_grade_id')
        ->whereNull('vendor_pre_qualification_vendor_group_grades.deleted_at');

        //tabulator filters
        if($request->has('filters'))
        {
            foreach($request->get('filters') as $filters)
            {
                $val = trim($filters['value']);

                switch(trim(strtolower($filters['field'])))
                {
                    case 'vendor_group':
                        if(strlen($val) > 0)
                        {
                            $model->where('contract_group_categories.name', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                    case 'grade':
                        if(strlen($val) > 0)
                        {
                            $model->where('vendor_management_grades.name', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                }
            }
        }

        $model->orderBy('contract_group_categories.name', 'asc');

        $rowCount = $model->get()->count();

        $records = $model->skip($limit * ($page - 1))
        ->take($limit)
        ->get();

        $data = [];

        foreach($records->all() as $key => $record)
        {
            $counter = ($page-1) * $limit + $key + 1;

            $data[] = [
                'id'            => $record->id,
                'counter'       => $counter,
                'vendor_group'  => $record->name,
                'grade'         => $record->grade_name,
                'route:update'  => route('vendorPreQualification.grades.update', array($record->id)),
                'route:delete'  => route('vendorPreQualification.grades.delete', array($record->id)),
                'can_delete'    => $record->vendor_group_grade_id,
                'route:preview' => $record->vendor_management_grade_id ? route('vendorPreQualification.grades.gradePreview', array($record->vendor_management_grade_id)) : "",
            ];
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }

    public function gradesList()
    {
        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $model = VendorManagementGrade::select("vendor_management_grades.id", "vendor_management_grades.name")
        ->where('vendor_management_grades.is_template', '=', true);

        //tabulator filters
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
                            $model->where('vendor_management_grades.name', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                }
            }
        }

        $model->orderBy('vendor_management_grades.name', 'desc');

        $rowCount = $model->get()->count();

        $records = $model->skip($limit * ($page - 1))
        ->take($limit)
        ->get();

        $data = [];

        foreach($records->all() as $key => $record)
        {
            $counter = ($page-1) * $limit + $key + 1;

            $data[] = [
                'id'            => $record->id,
                'counter'       => $counter,
                'name'          => $record->name,
                'route:preview' => route('vendorPreQualification.grades.gradePreview', array($record->id)),
            ];
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }

    public function update($contractGroupCategoryId)
    {
        $success = false;
        $errorMessage = null;

        try
        {
            $vendorGroupGrade = VendorGroupGrade::firstOrNew(['contract_group_category_id' => $contractGroupCategoryId]);

            $vendorGroupGrade->vendor_management_grade_id = Input::get('id');

            $success = $vendorGroupGrade->save();
        }
        catch(\Exception $e)
        {
            \Log::error($e->getMessage());
            \Log::error($e->getTraceAsString());

            $errorMessage = trans('forms.anErrorOccured');
        }

        return [
            'success'        => $success,
            'errorMessage' => $errorMessage,
        ];
    }

    public function delete($contractGroupCategoryId)
    {
        $success = false;
        $errorMessage = null;

        try
        {
            $vendorGroupGrade = VendorGroupGrade::where('contract_group_category_id', '=', $contractGroupCategoryId)->first();

            if($vendorGroupGrade) $vendorGroupGrade->delete();

            $success = true;
        }
        catch(\Exception $e)
        {
            \Log::error($e->getMessage());
            \Log::error($e->getTraceAsString());

            $errorMessage = trans('forms.anErrorOccured');
        }

        return [
            'success'      => $success,
            'errorMessage' => $errorMessage,
        ];
    }

    public function gradePreview($vendorManagementGradeId)
    {
        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $model = VendorManagementGradeLevel::select("id", "description", "score_upper_limit", "definition")
            ->where('vendor_management_grade_id', '=', $vendorManagementGradeId)
            ->orderBy('score_upper_limit', 'desc');

        $rowCount = $model->get()->count();

        $records = $model->skip($limit * ($page - 1))
        ->take($limit)
        ->get();

        $data = [];

        foreach($records->all() as $key => $record)
        {
            $counter = ($page-1) * $limit + $key + 1;

            $data[] = [
                'id'            => $record->id,
                'counter'       => $counter,
                'description'          => $record->description,
                'score_upper_limit'          => $record->score_upper_limit,
                'definition'          => $record->definition,
            ];
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }
}
