<?php

use PCK\Companies\Company;
use Carbon\Carbon;
use PCK\VendorRegistration\VendorRegistration;
use PCK\ModuleParameters\VendorManagement\VendorRegistrationAndPrequalificationModuleParameter;
use PCK\Base\Helpers;

class UnsuccessfulRegisteredVendorsController extends \BaseController {

    public function index()
    {
        return View::make('vendor_management.lists.unsuccessful_vendors.index');
    }

    public function list()
    {
        if( ! Request::ajax() )
        {
            App::abort(404);
        }

        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $model = VendorRegistration::whereNotNull('unsuccessful_at')
            ->join('companies', 'companies.id', '=', 'vendor_registrations.company_id')
            ->orderBy('unsuccessful_at', 'asc');

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
                            $model->where('companies.name', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                }
            }
        }

        $rowCount = $model->get()->count();

        $records = $model->skip($limit * ($page - 1))
            ->take($limit)
            ->get();

        $data = [];

        switch(VendorRegistrationAndPrequalificationModuleParameter::getValue('period_retain_unsuccessful_reg_and_preq_submission_unit'))
        {
            case VendorRegistrationAndPrequalificationModuleParameter::DAY:
                $validityPeriodUnit = 'days';
                break;
            case VendorRegistrationAndPrequalificationModuleParameter::WEEK:
                $validityPeriodUnit = 'weeks';
                break;
            case VendorRegistrationAndPrequalificationModuleParameter::MONTH:
                $validityPeriodUnit = 'months';
                break;
            default:
                throw new \Exception("Invalid time unit");
        }

        foreach($records->all() as $key => $record)
        {
            $counter = ($page-1) * $limit + $key + 1;

            $startTime = Carbon::parse($record->unsuccessful_at);

            $purgeDate  = Helpers::getTimeFrom($startTime, VendorRegistrationAndPrequalificationModuleParameter::getValue('period_retain_unsuccessful_reg_and_preq_submission_value'), $validityPeriodUnit);

            $data[] = [
                'id'               => $record->id,
                'counter'          => $counter,
                'name'             => $record->company->name,
                'unsuccessfulDate' => Carbon::parse($record->unsuccessful_at)->format(\Config::get('dates.standard')),
                'purgeDate'        => Carbon::parse($purgeDate)->format(\Config::get('dates.standard')),
            ];
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }
}