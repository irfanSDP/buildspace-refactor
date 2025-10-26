<?php

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Crypt;
use PCK\Base\Helpers;
use PCK\ContractGroupCategory\ContractGroupCategory;
use PCK\Helpers\ModuleAttachment;
use PCK\ModuleUploadedFiles\ModuleUploadedFile;
use PCK\ObjectField\ObjectField;
use PCK\Orders\Order;
use PCK\Orders\OrderItem;
use PCK\Payment\PaymentSetting;
use PCK\PaymentGateway\PaymentGatewayRepository;
use PCK\PaymentGateway\PaymentGatewaySettingRepository;
use PCK\VendorManagement\InstructionSetting;
use PCK\VendorRegistration\Payment\VendorRegistrationPayment;
use PCK\VendorRegistration\Payment\VendorRegistrationPaymentRepository;
use PCK\VendorRegistration\Section;

class VendorRegistrationPaymentsController extends \BaseController
{
    private $repository;
    private $paymentGatewayRepository;
    protected $paymentGatewaySettingRepository;

    public function __construct(
        VendorRegistrationPaymentRepository $repository,
        PaymentGatewayRepository $paymentGatewayRepository,
        PaymentGatewaySettingRepository $paymentGatewaySettingRepository
    ) {
        $this->repository = $repository;
        $this->paymentGatewayRepository = $paymentGatewayRepository;
        $this->paymentGatewaySettingRepository = $paymentGatewaySettingRepository;
    }

    public function index()
    {
        $input              = Input::all();
        $user               = \Confide::user();
        $company            = $user->company;

        $currentlySelectedPaymentMethodRecord = VendorRegistrationPayment::getCurrentlySelectedPaymentMethodRecord($company);
        $attachmentsCount = $currentlySelectedPaymentMethodRecord ? $currentlySelectedPaymentMethodRecord->attachments->count() : 0;

        // Check if payment is allowed
        $allowPayment = $this->repository->allowPayment($company);

        $paymentGatewayData = [
            'allow_payment' => $allowPayment['allow'],
            'selections' => null,
            'selected' => $input['q'] ?? null,
            'html' => '',
        ];

        if ($paymentGatewayData['allow_payment']) { // Payment is allowed -> Show payment options
            $pgSetting = $this->paymentGatewaySettingRepository->getDefaultGateway(true);
            if ($pgSetting) {
                if (is_null($paymentGatewayData['selected'])) {
                    $paymentGatewayData['selected'] = $pgSetting->payment_gateway;
                }

                if (is_null($company->expiry_date)) {
                    $dType = OrderItem::TYPE_VENDOR_REG;
                    $dOrigin = Order::ORIGIN_REG;
                } else {
                    $dType = OrderItem::TYPE_VENDOR_RENEWAL;
                    $dOrigin = Order::ORIGIN_RENEWAL;
                }

                $paymentGatewayData['html'] = View::make('payments.gateway.partials.pg-btn-container', [
                    'paymentGatewayBtnData' => [
                        'pg' => '',
                        'd' => Crypt::encrypt(serialize([
                            'lang' => 'en',
                            'type' =>  $dType,
                            'origin' => $dOrigin,
                        ])),
                        'lnk' => base64_encode(route('api.payment-gateway.html.payment-form')),
                        'html' => ''
                    ]
                ])->render();
            }
            $paymentGatewayData['selections'] = $this->paymentGatewaySettingRepository->getSelections();

            $vendorRegistrationPaymentSection = $company->vendorRegistration->getSection(Section::SECTION_PAYMENT);
            $instructionSettings = InstructionSetting::first();
        } else {    // Payment is not allowed -> Show message
            $paymentGatewayData['html'] = $allowPayment['message'];

            $vendorRegistrationPaymentSection = null;
            $instructionSettings = null;
        }

        return View::make('vendor_registration.payment', [
            'company'                              => $company,
            'paymentGatewayData'                   => $paymentGatewayData,
            'paymentMethods'                       => PaymentSetting::getPaymentMethods(),
            'currentlySelectedPaymentMethodRecord' => $currentlySelectedPaymentMethodRecord,
            'attachmentsCount'                     => $attachmentsCount,
            'vendorRegistrationPaymentSection'     => $vendorRegistrationPaymentSection,
            'instructionSettings'                  => $instructionSettings,
        ]);
    }

    public function selectedPaymentMethod()
    {
        $inputs                   = Input::all();
        $paymentSetting           = PaymentSetting::find($inputs['paymentSettingId']);
        $virtualBankAccountNumber = $this->repository->getVirtualAccountNumber($paymentSetting);

        return Response::json([
            'virtualBankAccountNumber' => $virtualBankAccountNumber,
        ]);
    }

    public function getAttachmentsList($paymentId)
	{
        $record        = VendorRegistrationPayment::find($paymentId);
		$uploadedFiles = $this->getAttachmentDetails($record);

		$data = array();

		foreach($uploadedFiles as $file)
		{
			$file['imgSrc']      = $file->generateThumbnailURL();
			$file['deleteRoute'] = $file->generateDeleteURL();
			$file['size']	     = Helpers::formatBytes($file->size);

			$data[] = $file;
		}

		return $data;
	}

    public function attachmentsUpdate($paymentId)
    {
        $inputs = Input::all();
        $record = VendorRegistrationPayment::find($paymentId);

        ModuleAttachment::saveAttachments($record, $inputs);

        $company = \Confide::user()->company;

        $vendorRegistrationPaymentSection = $company->vendorRegistration->getSection(Section::SECTION_PAYMENT);

        if($vendorRegistrationPaymentSection->amendmentsRequired())
        {
            $vendorRegistrationPaymentSection->amendment_status = Section::AMENDMENT_STATUS_MADE;
            $vendorRegistrationPaymentSection->save();
        }

		return array(
			'success' => true,
		);
    }

    public function getAttachmentCount($paymentId)
    {
        $record = VendorRegistrationPayment::find($paymentId);

        return Response::json([
            'attachmentCount' => count($this->getAttachmentDetails($record)),
        ]);
    }

    public function masterListIndex()
    {
        $yesNoSelection = [
            -1 => trans('general.all'),
            1 => trans('general.yes'),
            2 => trans('general.no'),
        ];

        return View::make('vendor_registration.payment_master_list_index', [
            'yesNoSelection' => $yesNoSelection,
        ]);
    }

    public function getVendorPayments()
    {
        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $data = [];

        $query = "SELECT c.id AS company_id, c.name AS company, vrp.id AS payment_id, vrp.submitted, vrp.paid, vrp.successful, vrp.status AS payment_status, COALESCE(ps.account_number || '-' || LPAD(vrp.running_number::TEXT, 10, '0'), '-') AS virtual_account_number, vrp.submitted_date, vrp.paid_date, vrp.successful_date, ps.name as bank
                    FROM companies c 
                    INNER JOIN contract_group_categories cgc ON cgc.id = c.contract_group_category_id 
                    LEFT OUTER JOIN vendor_registration_payments vrp ON vrp.company_id = c.id AND vrp.currently_selected IS TRUE
                    LEFT OUTER JOIN payment_settings ps on ps.id = vrp.payment_setting_id 
                    WHERE cgc.type = " . ContractGroupCategory::TYPE_EXTERNAL;

        if($request->has('filters'))
        {
            foreach($request->get('filters') as $filters)
            {
                $val = trim($filters['value']);

                switch(trim(strtolower($filters['field'])))
                {
                    case 'company':
                        if(strlen($val) > 0)
                        {
                            $query .= " AND c.name ILIKE '%{$val}%'";
                        }
                        break;
                    case 'bank':
                        if(strlen($val) > 0)
                        {
                            $query .= " AND ps.name ILIKE '%{$val}%'";
                        }
                        break;
                    case 'virtual_account_number':
                        if(strlen($val) > 0)
                        {
                            $query .= " AND COALESCE(ps.account_number || '-' || LPAD(vrp.running_number::TEXT, 10, '0'), '-') ILIKE '%{$val}%'";
                        }
                        break;
                    case 'submitted':
                        if($val > 0)
                        {
                            if($val == 1)
                            {
                                $query .= " AND vrp.submitted IS TRUE";
                            }
                            else
                            {
                                $query .= " AND vrp.submitted IS FALSE";
                            }
                        }
                        break;
                    case 'paid':
                        if($val > 0)
                        {
                            if($val == 1)
                            {
                                $query .= " AND vrp.paid IS TRUE";
                            }
                            else
                            {
                                $query .= " AND vrp.paid IS FALSE";
                            }
                        }
                        break;
                    case 'completed':
                        if($val > 0)
                        {
                            if($val == 1)
                            {
                                $query .= " AND vrp.successful IS TRUE";
                            }
                            else
                            {
                                $query .= " AND vrp.successful IS FALSE";
                            }
                        }
                        break;
                }
            }
        }

        $rowCount = count(DB::select(DB::raw($query)));

        $query .= " ORDER BY c.id ASC";
        $query .= " LIMIT " .$limit. " OFFSET " . $limit * ($page - 1);

        $records = DB::select(DB::raw($query));

        $collection = new Collection($records);

        $companyIds = $collection->lists('company_id');

        array_unshift($companyIds, 0); // Prevent sql error when array is empty.

        $paymentProofAttachmentCountRecords = \DB::select(\DB::raw("SELECT c.id, COUNT(f.id)
            FROM companies c
            JOIN vendor_registration_payments p ON p.company_id = c.id
            JOIN module_uploaded_files f ON f.uploadable_id = p.id AND f.uploadable_type = '". get_class(new VendorRegistrationPayment) ."'
            WHERE c.id IN (".implode(',', $companyIds).")
            GROUP BY c.id;"
        ));

        $paymentProofAttachmentCount = [];

        foreach($paymentProofAttachmentCountRecords as $record)
        {
            $paymentProofAttachmentCount[$record->id] = $record->count;
        }

        $paidAttachmentCountRecords = \DB::select(\DB::raw("SELECT c.id, COUNT(f.id)
            FROM companies c
            JOIN vendor_registration_payments p ON p.company_id = c.id
            JOIN object_fields of ON of.object_id = p.id AND of.object_type = '".get_class(new VendorRegistrationPayment)."' AND of.field = '".ObjectField::VENDOR_REGISTRATION_PAYMENT_PAID."'
            JOIN module_uploaded_files f ON f.uploadable_id = of.id AND f.uploadable_type = '". get_class(new ObjectField) ."'
            WHERE c.id IN (".implode(',', $companyIds).")
            GROUP BY c.id;"
        ));

        $paidAttachmentCount = [];

        foreach($paidAttachmentCountRecords as $record)
        {
            $paidAttachmentCount[$record->id] = $record->count;
        }

        $completedAttachmentCountRecords = \DB::select(\DB::raw("SELECT c.id, COUNT(f.id)
            FROM companies c
            JOIN vendor_registration_payments p ON p.company_id = c.id
            JOIN object_fields of ON of.object_id = p.id AND of.object_type = '".get_class(new VendorRegistrationPayment)."' AND of.field = '".ObjectField::VENDOR_REGISTRATION_PAYMENT_SUCCESSFUL."'
            JOIN module_uploaded_files f ON f.uploadable_id = of.id AND f.uploadable_type = '". get_class(new ObjectField) ."'
            WHERE c.id IN (".implode(',', $companyIds).")
            GROUP BY c.id;"
        ));

        $completedAttachmentCount = [];

        foreach($completedAttachmentCountRecords as $record)
        {
            $completedAttachmentCount[$record->id] = $record->count;
        }

        foreach($records as $key => $record)
        {
            if(is_null($record->company_id)) continue;

            $counter = ($page-1) * $limit + $key + 1;

            array_push($data, [
                'counter'                        => $counter,
                'id'                             => $record->company_id,
                'company_id'                     => $record->company_id,
                'company'                        => $record->company,
                'virtual_account_number'         => $record->virtual_account_number,
                'submitted'                      => is_null($record->payment_id) || is_null($record->submitted_date) ? null : \Carbon\Carbon::parse($record->submitted_date)->format(\Config::get('dates.submitted_at')),
                'paid'                           => is_null($record->payment_id) || is_null($record->paid_date) ? null : \Carbon\Carbon::parse($record->paid_date)->format(\Config::get('dates.submitted_at')),
                'completed'                      => is_null($record->payment_id) || is_null($record->successful_date) ? null : \Carbon\Carbon::parse($record->successful_date)->format(\Config::get('dates.submitted_at')),
                'bank'                           => $record->bank,
                'route:payment_proof'            => is_null($record->payment_id) ? null : route('vendor.registration.payment.proof', [$record->company_id, $record->payment_id]),
                'payment_proof_attachment_count' => $paymentProofAttachmentCount[$record->company_id] ?? 0,
                'route:get_uploads_paid'         => is_null($record->payment_id) ? null : route('vendor.registration.payment.paid.uploads', [$record->payment_id]),
                'route:do_upload_paid'           => is_null($record->payment_id) ? null : route('vendor.registration.payment.paid.doUpload', [$record->payment_id]),
                'paid_attachments_count'         => $paidAttachmentCount[$record->company_id] ?? 0,
                'route:get_uploads_completed'    => is_null($record->payment_id) ? null : route('vendor.registration.payment.completed.uploads', [$record->payment_id]),
                'route:do_upload_completed'      => is_null($record->payment_id) ? null : route('vendor.registration.payment.completed.doUpload', [$record->payment_id]),
                'completed_attachments_count'    => $completedAttachmentCount[$record->company_id] ?? 0,
            ]);
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }

    public function getPaymentProof($companyId, $paymentId)
    {
        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $data = [];

        $model = ModuleUploadedFile::where('module_uploaded_files.uploadable_id', '=', $paymentId)
            ->join('uploads', 'uploads.id', '=', 'module_uploaded_files.upload_id')
            ->where('module_uploaded_files.uploadable_type', '=', get_class(new VendorRegistrationPayment));

        if($request->has('filters'))
        {
            foreach($request->get('filters') as $filters)
            {
                $val = trim($filters['value']);

                switch(trim(strtolower($filters['field'])))
                {
                    case 'filename':
                        if(strlen($val) > 0)
                        {
                            $model->where('uploads.filename', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                }
            }
        }

        $model->orderBy('filename', 'asc');

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
                'filename'     => $record->filename,
                'download_url' => $record->file->download_url,
            ];
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }

    public function getUploadedPaidAttachments($paymentId)
    {
        $input = Input::all();

        $payment = VendorRegistrationPayment::find($paymentId);

        $object = ObjectField::findOrCreateNew($payment, ObjectField::VENDOR_REGISTRATION_PAYMENT_PAID);

        $uploadedFiles = $this->getAttachmentDetails($object);

        $data = array();

        foreach($uploadedFiles as $file)
        {
            $file['imgSrc']      = $file->generateThumbnailURL();
            $file['deleteRoute'] = $file->generateGeneralDeleteURL();
            $file['createdAt']   = Carbon::parse($file->created_at)->format(\Config::get('dates.created_at'));
            $file['size']        = \PCK\Base\Helpers::formatBytes($file->size);

            $data[] = $file;
        }

        return $data;
    }

    public function doUploadPaidAttachments($paymentId)
    {
        $input = Input::all();

        $payment = VendorRegistrationPayment::find($paymentId);

        $object = ObjectField::findOrCreateNew($payment, ObjectField::VENDOR_REGISTRATION_PAYMENT_PAID);

        \PCK\Helpers\ModuleAttachment::saveAttachments($object, $input);

        return array(
            'success' => true,
        );
    }

    public function getUploadedCompletedAttachments($paymentId)
    {
        $input = Input::all();

        $payment = VendorRegistrationPayment::find($paymentId);

        $object = ObjectField::findOrCreateNew($payment, ObjectField::VENDOR_REGISTRATION_PAYMENT_SUCCESSFUL);

        $uploadedFiles = $this->getAttachmentDetails($object);

        $data = array();

        foreach($uploadedFiles as $file)
        {
            $file['imgSrc']      = $file->generateThumbnailURL();
            $file['deleteRoute'] = $file->generateGeneralDeleteURL();
            $file['createdAt']   = Carbon::parse($file->created_at)->format(\Config::get('dates.created_at'));
            $file['size']        = \PCK\Base\Helpers::formatBytes($file->size);

            $data[] = $file;
        }

        return $data;
    }

    public function doUploadCompletedAttachments($paymentId)
    {
        $input = Input::all();

        $payment = VendorRegistrationPayment::find($paymentId);

        $object = ObjectField::findOrCreateNew($payment, ObjectField::VENDOR_REGISTRATION_PAYMENT_SUCCESSFUL);

        \PCK\Helpers\ModuleAttachment::saveAttachments($object, $input);

        return array(
            'success' => true,
        );
    }

}