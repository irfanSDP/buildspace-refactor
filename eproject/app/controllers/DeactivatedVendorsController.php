<?php

use PCK\Companies\Company;
use PCK\Vendor\Vendor;
use Carbon\Carbon;
use PCK\Notifications\EmailNotifier;
use PCK\ContractGroupCategory\ContractGroupCategory;

class DeactivatedVendorsController extends \BaseController {

    protected $emailNotifier;

    public function __construct(EmailNotifier $emailNotifier)
    {
        $this->emailNotifier = $emailNotifier;
    }

    public function index()
    {
        return View::make('vendor_management.lists.deactivated_vendors.index');
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

        $model = Company::select('companies.*')
            ->join('vendors', 'vendors.company_id', '=', 'companies.id')
            ->join('contract_group_categories', 'contract_group_categories.id', '=', 'companies.contract_group_category_id')
            ->where('companies.confirmed', '=', true)
            ->where('vendors.type', '=', Vendor::TYPE_ACTIVE)//watch list vendors should stay in watch list
            ->whereNotNull('companies.deactivated_at')
            ->whereNotNull('companies.activation_date')
            ->where('contract_group_categories.type', '=', ContractGroupCategory::TYPE_EXTERNAL)
            ->where('contract_group_categories.hidden', '=', false)
            ->groupBy('companies.id')
            ->orderBy('companies.name', 'asc');

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
                    case 'vendor_code':
                        if(strlen($val) > 0)
                        {
                            $vendorCodePrefix = getenv('VENDOR_CODE_PREFIX') ? getenv('VENDOR_CODE_PREFIX') : "BSP";
                            $vendorCodePadLength = getenv('VENDOR_CODE_PAD_LENGTH') ? getenv('VENDOR_CODE_PAD_LENGTH') : 5;

                            $model->where(DB::raw("'" . $vendorCodePrefix . "' || LPAD(companies.id::text, " . $vendorCodePadLength . ", '0')"), 'ILIKE', '%' . $val . '%');
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

        foreach($records->all() as $key => $record)
        {
            $counter = ($page-1) * $limit + $key + 1;

            $data[] = [
                'id'                    => $record->id,
                'counter'               => $counter,
                'name'                  => $record->name,
                'vendor_code'           => $record->getVendorCode(),
                'deactivatedAt'         => Carbon::parse($record->deactivated_at)->format(\Config::get('dates.standard')),
                'route:view'            => route('vendorProfile.show', array($record->id)),
                'route:reminder'        => route('vendorManagement.renewalReminder', array($record->id)),
                'route:update-reminder' => route('vendorManagement.updateReminder', array($record->id)),
            ];
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }

    public function sendRenewalReminder($companyId)
    {
        $this->emailNotifier->sendVendorRenewalReminders([$companyId]);

        return array('success' => true);
    }

    public function sendUpdateReminder($companyId)
    {
        $inputs   = Input::all();
        $contents = $inputs['contents'];

        $this->emailNotifier->sendVendorUpdateReminders([$companyId], $contents);

        return array('success' => true);
    }
}