<?php

namespace DigitalStar;

use Input;
use PCK\Companies\Company;
use PCK\DigitalStar\Evaluation\DsEvaluation;
use PCK\Users\User;
use PCK\VendorManagement\VendorManagementUserPermission;
use Request;
use Response;
use View;

class DsSetupCompanyEvaluatorController extends \BaseController
{
    public function index($evaluationId)
    {
        $evaluation = DsEvaluation::find($evaluationId);
        $company = Company::find($evaluation->company_id);

        return View::make('digital_star.setups.evaluations.evaluators.index', compact('evaluation', 'company'));
    }

    public function assigned($evaluationId)
    {
        $evaluation = DsEvaluation::find($evaluationId);
        if (! $evaluation) {
            return Response::json([
                'last_page' => 1,
                'data' => []
            ]);
        }

        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $model = User::select(
            'users.id as user_id',
            'users.name as user_name',
            'users.email as user_email',
            'companies.id as company_id',
            'companies.name as company_name',
            'contract_group_categories.name as vendor_group'
        )
        ->join('companies', 'companies.id', '=', 'users.company_id')
        ->join('contract_group_categories', 'companies.contract_group_category_id', '=', 'contract_group_categories.id')
        ->where('companies.id', '=', $evaluation->company_id)
        ->where('users.is_admin', '=', true);

        if ($request->has('filters')) {
            foreach ($request->get('filters') as $filters) {
                if (!isset($filters['value']) || is_array($filters['value'])) continue;

                $val = trim($filters['value']);

                switch (trim(strtolower($filters['field']))) {
                    case 'user_name':
                        if (strlen($val) > 0) {
                            $model->where('users.name', '=', $val);
                        }
                        break;

                    case 'user_email':
                        if (strlen($val) > 0) {
                            $model->where('users.email', '=', $val);
                        }
                        break;

                    case 'company_name':
                        if (strlen($val) > 0) {
                            $model->where('companies.name', 'ILIKE', '%' . $val . '%');
                        }
                        break;

                    case 'vendor_group':
                        if (strlen($val) > 0) {
                            $model->where('contract_group_categories.name', 'ILIKE', '%' . $val . '%');
                        }
                        break;
                }
            }
        }

        $rowCount = $model->count();

        $records = $model->orderBy('users.name', 'asc')
            ->orderBy('companies.name', 'asc')
            ->skip($limit * ($page - 1))
            ->take($limit)
            ->get();

        $data = [];

        foreach ($records->all() as $key => $record) {
            $counter = ($page - 1) * $limit + $key + 1;

            $data[] = [
                'id' => $record->company_id,
                'counter' => $counter,
                'user_name' => $record->user_name,
                'user_email' => $record->user_email,
                'company_name' => $record->company_name,
                'vendor_group' => $record->vendor_group,
            ];
        }

        $totalPages = ceil($rowCount / $limit);

        return Response::json([
            'last_page' => $totalPages,
            'data' => $data
        ]);
    }
}