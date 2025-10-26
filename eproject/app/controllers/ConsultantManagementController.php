<?php
use Carbon\Carbon;

use PCK\ContractGroupCategory\ContractGroupCategory;
use PCK\ConsultantManagement\ConsultantManagementContract;
use PCK\ConsultantManagement\ConsultantManagementUserRole;
use PCK\ConsultantManagement\ConsultantManagementCompanyRole;
use PCK\ConsultantManagement\ConsultantManagementCompanyRoleLog;
use PCK\ConsultantManagement\ConsultantManagementSubsidiary;
use PCK\ConsultantManagement\ConsultantManagementProductType;
use PCK\ConsultantManagement\ConsultantManagementVendorCategoryRfp;
use PCK\ConsultantManagement\ConsultantManagementVendorCategoryRfpAccountCode;
use PCK\ConsultantManagement\ConsultantManagementAttachmentSetting;
use PCK\ConsultantManagement\DevelopmentType;
use PCK\ConsultantManagement\ProductType;
use PCK\ConsultantManagement\ConsultantManagementRecommendationOfConsultant;
use PCK\ConsultantManagement\ConsultantManagementListOfConsultant;
use PCK\ConsultantManagement\ConsultantManagementCallingRfp;
use PCK\ConsultantManagement\ConsultantManagementOpenRfp;
use PCK\ConsultantManagement\ApprovalDocument;
use PCK\ConsultantManagement\LetterOfAward;

use PCK\Subsidiaries\Subsidiary;
use PCK\Subsidiaries\SubsidiaryRepository;
use PCK\Countries\Country;
use PCK\States\State;
use PCK\Users\User;
use PCK\Companies\Company;
use PCK\VendorCategory\VendorCategory;

use PCK\Buildspace\AccountGroup as BsAccountGroup;
use PCK\Buildspace\AccountCode as BsAccountCode;

use PCK\Helpers\ModuleAttachment;
use PCK\Base\Helpers;
use PCK\ObjectField\ObjectField;
use PCK\Base\Upload;
use PCK\CIDBGrades\CIDBGrade;
use PCK\Notifications\EmailNotifier;

use PCK\Forms\ConsultantManagement\ConsultantManagementContractForm;
use PCK\Forms\ConsultantManagement\PhaseForm;
use PCK\Forms\ConsultantManagement\UserManagementForm;
use PCK\Forms\ConsultantManagement\AssignCompanyRoleForm;
use PCK\Forms\ConsultantManagement\VendorCategoryRfpForm;
use PCK\Forms\ConsultantManagement\AttachmentSettingForm;

class ConsultantManagementController extends \BaseController
{
    private $consultantManagementContractForm;
    private $phaseForm;
    private $userManagementForm;
    private $assignCompanyRoleForm;
    private $vendorCategoryRfpForm;
    private $attachmentSettingForm;

    private $subsidiaryRepository;

    private $emailNotifier;

    public function __construct(SubsidiaryRepository $subsidiaryRepository, ConsultantManagementContractForm $consultantManagementContractForm, PhaseForm $phaseForm, UserManagementForm $userManagementForm, AssignCompanyRoleForm $assignCompanyRoleForm, VendorCategoryRfpForm $vendorCategoryRfpForm, AttachmentSettingForm $attachmentSettingForm, EmailNotifier $emailNotifier)
    {
        $this->subsidiaryRepository = $subsidiaryRepository;
        $this->consultantManagementContractForm = $consultantManagementContractForm;
        $this->phaseForm = $phaseForm;
        $this->userManagementForm = $userManagementForm;
        $this->assignCompanyRoleForm = $assignCompanyRoleForm;
        $this->vendorCategoryRfpForm = $vendorCategoryRfpForm;
        $this->attachmentSettingForm = $attachmentSettingForm;
        $this->emailNotifier = $emailNotifier;
    }

    public function contractIndex()
    {
        $user = \Confide::user();

        $rfpStatuses = [
            0 => trans('general.all'),
            ConsultantManagementVendorCategoryRfp::STATUS_RECOMMENDATION_OF_CONSULTANT => "ROC",
            ConsultantManagementVendorCategoryRfp::STATUS_LIST_OF_CONSULTANT => "LOC",
            ConsultantManagementVendorCategoryRfp::STATUS_CALLING_RFP => trans('general.callingRFP'),
            ConsultantManagementVendorCategoryRfp::STATUS_CLOSED_RFP => trans('general.closedRFP'),
            ConsultantManagementVendorCategoryRfp::STATUS_APPROVED => trans('verifiers.approved'),
            ConsultantManagementVendorCategoryRfp::STATUS_AWARDED => trans('general.awarded')
        ];

        return View::make('consultant_management.contracts.index', compact('user', 'rfpStatuses'));
    }

    public function contractList()
    {
        $user = \Confide::user();

        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $model = ConsultantManagementContract::select("consultant_management_contracts.id AS id", "consultant_management_contracts.title AS title",
        "consultant_management_contracts.reference_no AS reference_no", "countries.country AS country_name", "states.name AS state_name",
        "consultant_management_contracts.created_at AS created_at")
        ->leftJoin('consultant_management_subsidiaries', 'consultant_management_contracts.id', '=', 'consultant_management_subsidiaries.consultant_management_contract_id')
        ->join('consultant_management_user_roles', 'consultant_management_user_roles.consultant_management_contract_id', '=', 'consultant_management_contracts.id')
        ->join('countries', 'consultant_management_contracts.country_id', '=', 'countries.id')
        ->join('states', 'consultant_management_contracts.state_id', '=', 'states.id')
        ->where('consultant_management_user_roles.user_id', '=', $user->id);

        $totalRFPQuery = ConsultantManagementContract::selectRaw("consultant_management_contracts.id AS id, COUNT(DISTINCT rfp.id) AS total_rfp, COUNT(DISTINCT awarded_rfp.id) AS total_awarded_rfp")
        ->join('consultant_management_vendor_categories_rfp AS rfp', 'consultant_management_contracts.id', '=', 'rfp.consultant_management_contract_id')
        ->leftJoin('consultant_management_letter_of_awards AS loa', function($join){
            $join->on('loa.vendor_category_rfp_id', '=', 'rfp.id');
            $join->on('loa.status','=', \DB::raw(LetterOfAward::STATUS_APPROVED));
        })
        ->leftJoin('consultant_management_vendor_categories_rfp AS awarded_rfp', 'awarded_rfp.id', '=', 'loa.vendor_category_rfp_id')
        ->join('consultant_management_user_roles', 'consultant_management_user_roles.consultant_management_contract_id', '=', 'consultant_management_contracts.id')
        ->where('consultant_management_user_roles.user_id', '=', $user->id);

        //tabulator filters
        if($request->has('filters'))
        {
            foreach($request->get('filters') as $filters)
            {
                if(!isset($filters['value']) || is_array($filters['value'])) continue;

                $val = trim($filters['value']);

                switch(trim(strtolower($filters['field'])))
                {
                    case 'contract_title':
                        if(strlen($val) > 0)
                        {
                            $model->where('consultant_management_contracts.title', 'ILIKE', '%'.$val.'%');
                            $totalRFPQuery->where('consultant_management_contracts.title', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                    case 'reference_no':
                        if(strlen($val) > 0)
                        {
                            $model->where('consultant_management_contracts.reference_no', 'ILIKE', '%'.$val.'%');
                            $totalRFPQuery->where('consultant_management_contracts.reference_no', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                }
            }
        }

        $model->groupBy(\DB::raw('consultant_management_contracts.id, countries.id, states.id'))
        ->orderBy('consultant_management_contracts.created_at', 'desc');

        $totalRFPRecords = $totalRFPQuery->groupBy('consultant_management_contracts.id')
        ->orderBy('consultant_management_contracts.created_at', 'desc')
        ->skip($limit * ($page - 1))
        ->take($limit)
        ->get()
        ->keyBy('id')
        ->toArray();
        
        $rowCount = $model->get()->count();

        $records = $model->skip($limit * ($page - 1))
        ->take($limit)
        ->get();

        $data = [];
        
        foreach($records->all() as $key => $record)
        {
            $counter = ($page-1) * $limit + $key + 1;

            $data[] = [
                'id'                  => $record->id,
                'counter'             => $counter,
                'contract_title'      => trim($record->title),
                'reference_no'        => trim($record->reference_no),
                'country'             => trim($record->country_name),
                'state'               => trim($record->state_name),
                'total_rfp'           => (is_array($totalRFPRecords) && array_key_exists($record->id, $totalRFPRecords)) ? $totalRFPRecords[$record->id]['total_rfp'] : 0,
                'total_awarded_rfp'   => (is_array($totalRFPRecords) && array_key_exists($record->id, $totalRFPRecords)) ? $totalRFPRecords[$record->id]['total_awarded_rfp'] : 0,
                'contract_created_at' => Carbon::parse($record->created_at)->format('d/m/Y'),
                'deletable'           => $record->editableByUser($user),
                'route:show'          => route('consultant.management.contracts.contract.show', [$record->id]),
                'route:delete'        => route('consultant.management.contracts.contract.delete', [$record->id]),
            ];
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }

    public function rfpList()
    {
        $user = \Confide::user();

        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $model = ConsultantManagementVendorCategoryRfp::select("consultant_management_vendor_categories_rfp.id AS id", "consultant_management_contracts.id AS contract_id",
        "vendor_categories.name AS title", "consultant_management_contracts.title AS contract_title", "consultant_management_contracts.reference_no AS reference_no",
        "countries.country AS country_name", "states.name AS state_name", "consultant_management_vendor_categories_rfp.created_at AS created_at",
        "consultant_management_contracts.created_at AS contract_created_at")
        ->join('vendor_categories', 'vendor_categories.id', '=', 'consultant_management_vendor_categories_rfp.vendor_category_id')
        ->join('consultant_management_contracts', 'consultant_management_contracts.id', '=', 'consultant_management_vendor_categories_rfp.consultant_management_contract_id')
        ->join('consultant_management_user_roles', 'consultant_management_user_roles.consultant_management_contract_id', '=', 'consultant_management_contracts.id')
        ->join('countries', 'consultant_management_contracts.country_id', '=', 'countries.id')
        ->join('states', 'consultant_management_contracts.state_id', '=', 'states.id');

        //tabulator filters
        if($request->has('filters'))
        {
            foreach($request->get('filters') as $filters)
            {
                if(!isset($filters['value']) || is_array($filters['value'])) continue;

                $val = trim($filters['value']);

                switch(trim(strtolower($filters['field'])))
                {
                    case 'rfp_status':
                        switch((int)$val){
                            case ConsultantManagementVendorCategoryRfp::STATUS_AWARDED:
                                $model->join('consultant_management_rfp_revisions AS rev', 'rev.vendor_category_rfp_id', '=', 'consultant_management_vendor_categories_rfp.id')
                                ->join(\DB::raw('(
                                    SELECT rfp.id, MAX(rev2.revision) AS revision
                                    FROM consultant_management_vendor_categories_rfp rfp
                                    JOIN consultant_management_letter_of_awards loa2 ON loa2.vendor_category_rfp_id = rfp.id
                                    JOIN consultant_management_rfp_revisions rev2 ON loa2.vendor_category_rfp_id = rev2.vendor_category_rfp_id
                                    JOIN consultant_management_user_roles AS ur ON ur.consultant_management_contract_id = rfp.consultant_management_contract_id
                                    WHERE ur.user_id = '.$user->id.'
                                    GROUP BY rfp.id
                                ) max_rfp_revisions'), function($join){
                                    $join->on('max_rfp_revisions.id', '=', 'consultant_management_vendor_categories_rfp.id');
                                    $join->on('max_rfp_revisions.revision','=', 'rev.revision');
                                })
                                ->join('consultant_management_letter_of_awards AS loa', 'max_rfp_revisions.id', '=', 'loa.vendor_category_rfp_id')
                                ->where('loa.status', '=', LetterOfAward::STATUS_APPROVED);
                                break;
                            case ConsultantManagementVendorCategoryRfp::STATUS_APPROVED:
                                $model->join('consultant_management_rfp_revisions AS rev', 'rev.vendor_category_rfp_id', '=', 'consultant_management_vendor_categories_rfp.id')
                                ->join(\DB::raw('(
                                    SELECT rfp.id, MAX(rev2.revision) AS revision
                                    FROM consultant_management_vendor_categories_rfp rfp
                                    JOIN consultant_management_approval_documents doc2 ON doc2.vendor_category_rfp_id = rfp.id
                                    JOIN consultant_management_rfp_revisions rev2 ON doc2.vendor_category_rfp_id = rev2.vendor_category_rfp_id
                                    JOIN consultant_management_user_roles AS ur ON ur.consultant_management_contract_id = rfp.consultant_management_contract_id
                                    WHERE ur.user_id = '.$user->id.'
                                    GROUP BY rfp.id
                                ) max_rfp_revisions'), function($join){
                                    $join->on('max_rfp_revisions.id', '=', 'consultant_management_vendor_categories_rfp.id');
                                    $join->on('max_rfp_revisions.revision','=', 'rev.revision');
                                })
                                ->join('consultant_management_approval_documents AS doc', 'max_rfp_revisions.id', '=', 'doc.vendor_category_rfp_id')
                                ->where('doc.status', '=', ApprovalDocument::STATUS_APPROVED)
                                ->whereNotExists(function($query) use($user){
                                    $query->select(\DB::raw(1))
                                    ->from('consultant_management_vendor_categories_rfp AS rfp')
                                    ->join('consultant_management_letter_of_awards AS loa2', 'rfp.id', '=', 'loa2.vendor_category_rfp_id')
                                    ->join('consultant_management_user_roles AS ur', 'ur.consultant_management_contract_id', '=', 'rfp.consultant_management_contract_id')
                                    ->whereRaw('ur.user_id = '.$user->id.'
                                    AND loa2.status = '.LetterOfAward::STATUS_APPROVED.'
                                    AND consultant_management_vendor_categories_rfp.id = rfp.id');
                                });
                                break;
                            case ConsultantManagementVendorCategoryRfp::STATUS_CALLING_RFP:
                                $model->join('consultant_management_rfp_revisions AS rev', 'rev.vendor_category_rfp_id', '=', 'consultant_management_vendor_categories_rfp.id')
                                ->join(\DB::raw('(
                                    SELECT rfp.id, MAX(rev2.revision) AS revision
                                    FROM consultant_management_vendor_categories_rfp rfp
                                    JOIN consultant_management_rfp_revisions rev2 ON rfp.id = rev2.vendor_category_rfp_id
                                    JOIN consultant_management_user_roles AS ur ON ur.consultant_management_contract_id = rfp.consultant_management_contract_id
                                    WHERE ur.user_id = '.$user->id.'
                                    GROUP BY rfp.id
                                ) max_rfp_revisions'), function($join){
                                    $join->on('max_rfp_revisions.id', '=', 'consultant_management_vendor_categories_rfp.id');
                                    $join->on('max_rfp_revisions.revision','=', 'rev.revision');
                                })
                                ->join('consultant_management_calling_rfp', 'rev.id', '=', 'consultant_management_calling_rfp.consultant_management_rfp_revision_id')
                                ->whereRaw('NOW() < consultant_management_calling_rfp.closing_rfp_date')
                                ->where('consultant_management_calling_rfp.status', '=', ConsultantManagementCallingRfp::STATUS_APPROVED)
                                ->whereNotExists(function($query) use($user){
                                    $query->select(\DB::raw(1))
                                    ->from('consultant_management_vendor_categories_rfp AS rfp')
                                    ->join('consultant_management_approval_documents AS doc', 'rfp.id', '=', 'doc.vendor_category_rfp_id')
                                    ->join('consultant_management_user_roles AS ur', 'ur.consultant_management_contract_id', '=', 'rfp.consultant_management_contract_id')
                                    ->whereRaw('ur.user_id = '.$user->id.'
                                    AND doc.status = '.ApprovalDocument::STATUS_APPROVED.'
                                    AND consultant_management_vendor_categories_rfp.id = rfp.id');
                                });
                                break;
                            case ConsultantManagementVendorCategoryRfp::STATUS_CLOSED_RFP:
                                $model->join('consultant_management_rfp_revisions AS rev', 'rev.vendor_category_rfp_id', '=', 'consultant_management_vendor_categories_rfp.id')
                                ->join(\DB::raw('(
                                    SELECT rfp.id, MAX(rev2.revision) AS revision
                                    FROM consultant_management_vendor_categories_rfp rfp
                                    JOIN consultant_management_rfp_revisions rev2 ON rfp.id = rev2.vendor_category_rfp_id
                                    JOIN consultant_management_user_roles AS ur ON ur.consultant_management_contract_id = rfp.consultant_management_contract_id
                                    WHERE ur.user_id = '.$user->id.'
                                    GROUP BY rfp.id
                                ) max_rfp_revisions'), function($join){
                                    $join->on('max_rfp_revisions.id', '=', 'consultant_management_vendor_categories_rfp.id');
                                    $join->on('max_rfp_revisions.revision','=', 'rev.revision');
                                })
                                ->join('consultant_management_calling_rfp', 'rev.id', '=', 'consultant_management_calling_rfp.consultant_management_rfp_revision_id')
                                ->whereRaw('consultant_management_calling_rfp.status <> '.ConsultantManagementCallingRfp::STATUS_APPROVED.' OR NOW() > consultant_management_calling_rfp.closing_rfp_date')
                                ->whereNotExists(function($query) use($user){
                                    $query->select(\DB::raw(1))
                                    ->from('consultant_management_vendor_categories_rfp AS rfp')
                                    ->join('consultant_management_approval_documents AS doc', 'rfp.id', '=', 'doc.vendor_category_rfp_id')
                                    ->join('consultant_management_user_roles AS ur', 'ur.consultant_management_contract_id', '=', 'rfp.consultant_management_contract_id')
                                    ->whereRaw('ur.user_id = '.$user->id.'
                                    AND doc.status = '.ApprovalDocument::STATUS_APPROVED.'
                                    AND consultant_management_vendor_categories_rfp.id = rfp.id');
                                });
                                break;
                            case ConsultantManagementVendorCategoryRfp::STATUS_LIST_OF_CONSULTANT:
                                $model->join('consultant_management_rfp_revisions AS rev', 'rev.vendor_category_rfp_id', '=', 'consultant_management_vendor_categories_rfp.id')
                                ->join(\DB::raw('(
                                    SELECT rfp.id, MAX(rev2.revision) AS revision
                                    FROM consultant_management_vendor_categories_rfp rfp
                                    JOIN consultant_management_rfp_revisions rev2 ON rfp.id = rev2.vendor_category_rfp_id
                                    JOIN consultant_management_user_roles AS ur ON ur.consultant_management_contract_id = rfp.consultant_management_contract_id
                                    WHERE ur.user_id = '.$user->id.'
                                    GROUP BY rfp.id
                                ) max_rfp_revisions'), function($join){
                                    $join->on('max_rfp_revisions.id', '=', 'consultant_management_vendor_categories_rfp.id');
                                    $join->on('max_rfp_revisions.revision','=', 'rev.revision');
                                })
                                ->whereNotExists(function($query) use($user){
                                    $query->select(\DB::raw(1))
                                    ->from('consultant_management_calling_rfp AS calling_rfp')
                                    ->where('calling_rfp.consultant_management_rfp_revision_id', '=', \DB::raw('rev.id'));
                                });
                                break;
                            case ConsultantManagementVendorCategoryRfp::STATUS_RECOMMENDATION_OF_CONSULTANT:
                                $model->whereNotExists(function($query) use($user){
                                    $query->select(\DB::raw(1))
                                    ->from('consultant_management_rfp_revisions AS rev')
                                    ->where('rev.vendor_category_rfp_id', '=', \DB::raw('consultant_management_vendor_categories_rfp.id'));
                                });
                                break;
                        }
                        break;
                    case 'title':
                        if(strlen($val) > 0)
                        {
                            $model->where('vendor_categories.name', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                    case 'contract_title':
                        if(strlen($val) > 0)
                        {
                            $model->where('consultant_management_contracts.title', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                    case 'reference_no':
                        if(strlen($val) > 0)
                        {
                            $model->where('consultant_management_contracts.reference_no', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                }
            }
        }

        $model->where('consultant_management_user_roles.user_id', '=', $user->id)
        ->groupBy(\DB::raw('vendor_categories.id, consultant_management_vendor_categories_rfp.id, consultant_management_contracts.id, countries.id, states.id'))
        ->orderBy(\DB::raw('consultant_management_contracts.created_at desc, consultant_management_vendor_categories_rfp.created_at'), 'desc');

        $rowCount = $model->get()->count();

        $records = $model->skip($limit * ($page - 1))
        ->take($limit)
        ->get();

        $data = [];
        
        foreach($records->all() as $key => $record)
        {
            $counter = ($page-1) * $limit + $key + 1;
            
            $status = $record->getStatusText();

            switch($status)
            {
                case 'LOC':
                    $showRoute = route('consultant.management.loc.show', [$record->id, $record->getLatestRfpRevision()->listOfConsultant->id]);
                    break;
                case trans('general.closedRFP'):
                case trans('general.callingRFP'):
                    $showRoute = route('consultant.management.calling.rfp.show', [$record->id, $record->getLatestRfpRevision()->callingRfp->id]);
                    break;
                case trans('verifiers.approved'):
                    $showRoute = route('consultant.management.approval.document.index', [$record->id, $record->getLatestRfpRevision()->openRfp->id]);
                    break;
                case trans('general.awarded'):
                    $showRoute = route('consultant.management.loa.index', [$record->id]);
                    break;
                default:
                    $showRoute = route('consultant.management.roc.index', [$record->id]);
            }

            $data[] = [
                'id'                  => $record->id,
                'contract_id'         => $record->contract_id,
                'counter'             => $counter,
                'title'               => trim(mb_strtoupper($record->title)),
                'contract_title'      => trim($record->contract_title),
                'reference_no'        => trim($record->reference_no),
                'country'             => trim($record->country_name),
                'state'               => trim($record->state_name),
                'rfp_status'          => $status,
                'created_at'          => Carbon::parse($record->created_at)->format('d/m/Y'),
                'contract_created_at' => Carbon::parse($record->contract_created_at)->format('d/m/Y'),
                'route:show'          => $showRoute
            ];
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }

    public function contractShow(ConsultantManagementContract $consultantManagementContract)
    {
        $user = \Confide::user();
        $isContractEditor = $user->isConsultantManagementEditorByRole($consultantManagementContract, ConsultantManagementContract::ROLE_RECOMMENDATION_OF_CONSULTANT);

        return View::make('consultant_management.contracts.show', compact('consultantManagementContract', 'isContractEditor', 'user'));
    }

    public function contractCreate()
    {
        $user = \Confide::user();

        if(!$user->isSuperAdmin() and !$user->isGroupAdmin())
        {
            return Redirect::route('consultant.management.contracts.index');
        }

        $contract         = null;
        $rootSubsidiaries = Subsidiary::whereNull('parent_id')->orWhere('parent_id', '=', \DB::raw('id'))->orderBy('name', 'asc')->lists('name', 'id');
        $urlCountry       = route('country');
        $urlStates        = route('country.states');
        $countryId        = Input::old('country_id');
        $stateId          = Input::old('state_id');

        // init values are for the default values for the form (on page load).
        $initRunningNumber = 1;
        $initSuffix        = PCK\Projects\Project::generateContractNumberSuffix();

        JavaScript::put(compact('urlCountry', 'urlStates', 'countryId', 'stateId'));

        return View::make('consultant_management.contracts.edit', compact('contract', 'rootSubsidiaries', 'initRunningNumber', 'initSuffix'));
    }

    public function contractEdit(ConsultantManagementContract $contract)
    {
        $rootSubsidiaries = Subsidiary::whereNull('parent_id')->orWhere('parent_id', '=', \DB::raw('id'))->orderBy('name', 'asc')->lists('name', 'id');
        $urlCountry       = route('country');
        $urlStates        = route('country.states');
        $countryId        = Input::old('country_id', $contract->country_id);
        $stateId          = Input::old('state_id', $contract->state_id);

        JavaScript::put(compact('urlCountry', 'urlStates', 'countryId', 'stateId'));

        return View::make('consultant_management.contracts.edit', compact('contract', 'rootSubsidiaries'));
    }

    public function contractStore()
    {
        $this->consultantManagementContractForm->validate(Input::all());

        $user       = \Confide::user();
        $input      = Input::all();
        $subsidiary = Subsidiary::findOrFail((int)$input['subsidiary_id']);
        $country    = Country::findOrFail((int)$input['country_id']);
        $state      = State::findOrFail((int)$input['state_id']);

        $contract = ConsultantManagementContract::find($input['id']);

        $sendCreateEmailNotification = false;

        if(!$contract)
        {
            $contract = new ConsultantManagementContract();

            $contract->created_by = $user->id;

            $sendCreateEmailNotification = true;
        }

        $contract->subsidiary_id = $subsidiary->id;
        $contract->title         = trim($input['title']);
        $contract->reference_no  = trim($input['reference_no']);
        $contract->description   = trim($input['description']);
        $contract->address       = trim($input['address']);
        $contract->country_id    = $country->id;
        $contract->state_id      = $state->id;

        $contract->updated_by = $user->id;

        $contract->save();

        if($sendCreateEmailNotification)
        {
            $recipients = User::select('users.*')
                ->join('consultant_management_user_roles', 'consultant_management_user_roles.user_id', '=', 'users.id')
                ->whereRaw('consultant_management_user_roles.consultant_management_contract_id = '.$contract->id.'
                AND consultant_management_user_roles.role = '.ConsultantManagementContract::ROLE_RECOMMENDATION_OF_CONSULTANT.'
                AND consultant_management_user_roles.editor IS TRUE
                AND users.confirmed IS TRUE
                AND users.account_blocked_status IS FALSE')
                ->get();

            if(!empty($recipients))
            {
                $content = [
                    'subject' => "Consultant Management - New Development Planning created (".$contract->Subsidiary->name.")",//need to move this to i10n
                    'view' => 'consultant_management.email.create_contract',
                    'data' => [
                        'developmentPlanningTitle' => $contract->title,
                        'subsidiaryName' => $contract->Subsidiary->name,
                        'route' => route('consultant.management.contracts.contract.show', [$contract->id])
                    ]
                ];
                
                $this->emailNotifier->sendGeneralEmail($content, $recipients);
            }
        }

        \Flash::success(trans('forms.saved'));

        return Redirect::route('consultant.management.contracts.contract.show', [$contract->id]);
    }

    public function contractDelete(ConsultantManagementContract $contract)
    {
        $consultantManagementContractId = $contract->id;
        $user = \Confide::user();

        if($contract->editableByUser($user))
        {
            foreach($contract->consultantManagementSubsidiaries as $consultantManagementSubsidiary)
            {
                $consultantManagementSubsidiary->delete();
            }

            ConsultantManagementUserRole::where('consultant_management_contract_id', '=', $contract->id)->delete();
            ConsultantManagementCompanyRole::where('consultant_management_contract_id', '=', $contract->id)->delete();

            foreach($contract->consultantManagementVendorCategories as $consultantManagementVendorCategory)
            {
                $consultantManagementVendorCategory->delete();
            }

            ConsultantManagementVendorCategoryRfp::where('consultant_management_contract_id', '=', $contract->id)->delete();

            $contract->delete();

            \Log::info("Delete consultant management contract [contract id: {$consultantManagementContractId}]][user id:{$user->id}]");
        }

        return Redirect::route('consultant.management.contracts.index');
    }

    public function phaseCreate(ConsultantManagementContract $contract)
    {
        $phase                  = null;
        $subsidiaries           = $this->subsidiaryRepository->getHierarchicalCollection($contract->subsidiary_id);
        $developmentTypeRecords = DevelopmentType::orderBy('title', 'asc')->lists('title', 'id');

        $developmentTypes = [
            '-1' => trans('forms.select')
        ];

        foreach($developmentTypeRecords as $id => $title)
        {
            $developmentTypes[$id] = $title;
        }

        unset($developmentTypeRecords);

        return View::make('consultant_management.rfp.edit', compact('contract', 'phase', 'subsidiaries', 'developmentTypes'));
    }

    public function phaseEdit(ConsultantManagementSubsidiary $phase)
    {
        $contract               = $phase->consultantManagementContract;
        $subsidiaries           = $this->subsidiaryRepository->getHierarchicalCollection($contract->subsidiary_id);
        $developmentTypeRecords = DevelopmentType::orderBy('title', 'asc')->lists('title', 'id');

        $developmentTypes = [
            '-1' => trans('forms.select')
        ];

        foreach($developmentTypeRecords as $id => $title)
        {
            $developmentTypes[$id] = $title;
        }

        unset($developmentTypeRecords);

        return View::make('consultant_management.rfp.edit', compact('contract', 'phase', 'subsidiaries', 'developmentTypes'));
    }

    public function phaseStore()
    {
        $this->phaseForm->validate(Input::all());

        $user            = \Confide::user();
        $input           = Input::all();
        $contract        = ConsultantManagementContract::findOrFail($input['cid']);
        $subsidiary      = Subsidiary::findOrFail($input['subsidiary_id']);
        $developmentType = DevelopmentType::findOrFail($input['development_type_id']);

        $phase = ConsultantManagementSubsidiary::find($input['id']);

        if(!$phase)
        {
            $phase = new ConsultantManagementSubsidiary();

            $phase->consultant_management_contract_id = $contract->id;
            $phase->created_by = $user->id;
        }

        $phase->subsidiary_id           = $subsidiary->id;
        $phase->development_type_id     = $developmentType->id;
        $phase->business_case           = trim($input['business_case']);
        $phase->gross_acreage           = trim($input['gross_acreage']);
        $phase->project_budget          = trim($input['project_budget']);
        $phase->total_construction_cost = trim($input['total_construction_cost']);
        $phase->total_landscape_cost    = trim($input['total_landscape_cost']);
        $phase->cost_per_square_feet    = trim($input['cost_per_square_feet']);

        $phase->planning_permission_date = Carbon::parse($input['planning_permission_date'])->format('Y-m-d');
        $phase->building_plan_date       = Carbon::parse($input['building_plan_date'])->format('Y-m-d');
        $phase->launch_date              = Carbon::parse($input['launch_date'])->format('Y-m-d');

        $phase->updated_by = $user->id;

        $phase->save();

        $phase->productTypes()->delete();

        foreach($input['product_type'] as $fields)
        {
            $productType = new ConsultantManagementProductType();

            $productType->consultant_management_subsidiary_id = $phase->id;
            $productType->product_type_id                     = $fields['product_type_id'];
            $productType->number_of_unit                      = $fields['number_of_unit'];
            $productType->lot_dimension_length                = $fields['lot_dimension_length'];
            $productType->lot_dimension_width                 = $fields['lot_dimension_width'];
            $productType->proposed_built_up_area              = $fields['proposed_built_up_area'];
            $productType->proposed_average_selling_price      = $fields['proposed_average_selling_price'];
            $productType->created_by                          = $user->id;
            $productType->updated_by                          = $user->id;

            $productType->save();
        }

        \Flash::success(trans('forms.saved'));

        return Redirect::route('consultant.management.contracts.contract.show', [$contract->id]);
    }

    public function phaseDelete(ConsultantManagementSubsidiary $phase)
    {
        $consultantManagementSubsidiaryId = $phase->id;

        $contractId = $phase->consultant_management_contract_id;
        $user       = \Confide::user();

        if($phase->deletable())
        {
            $phase->delete();

            \Log::info("Delete consultant management subsidiary [phase id: {$consultantManagementSubsidiaryId}]][contract id:{$contractId}][user id:{$user->id}]");
        }

        return Redirect::route('consultant.management.contracts.contract.show', [$contractId]);
    }

    public function productTypeList($developmentTypeId)
    {
        $request = Request::instance();

        $developmentType = DevelopmentType::find($developmentTypeId);
        
        if(!$developmentType)
        {
            return Response::json([
                'results' => []
            ]);
        }

        $model = $developmentType->productTypes();
        
        if($request->has('q') && strlen($request->get('q')))
        {
            $searchStr = '%'.urldecode(trim($request->get('q'))).'%';

            $model->where('title', 'ILIKE', $searchStr);
        }

        $productTypeRecords = $model->orderBy('title', 'asc')->lists('title', 'id');

        $productTypes = [];

        foreach($productTypeRecords as $id => $title)
        {
            $productTypes[] = [
                'id' => $id,
                'text' => $title
            ];
        }

        return Response::json([
            'results' => $productTypes
        ]);
    }

    public function userManagementIndex(ConsultantManagementContract $consultantManagementContract)
    {
        $user = Confide::user();

        $companyRole = ConsultantManagementCompanyRole::where('consultant_management_contract_id', '=', $consultantManagementContract->id)
        ->where('role', '=', ConsultantManagementContract::ROLE_RECOMMENDATION_OF_CONSULTANT)
        ->first();

        $company = ($companyRole) ? $companyRole->company : $user->company;

        $users                                   = $consultantManagementContract->getUsersByRoleAndCompany(ConsultantManagementContract::ROLE_RECOMMENDATION_OF_CONSULTANT, $company);
        $recommendationOfConsultantUsers         = $users['users'];
        $recommendationOfConsultantImportedUsers = $users['imported_users'];
        $isROCUser = (array_key_exists($user->id, array_column($recommendationOfConsultantUsers, 'id', 'id')) || array_key_exists($user->id, array_column($recommendationOfConsultantImportedUsers, 'id', 'id')));

        $companyRole = ConsultantManagementCompanyRole::where('consultant_management_contract_id', '=', $consultantManagementContract->id)
        ->where('role', '=', ConsultantManagementContract::ROLE_LIST_OF_CONSULTANT)
        ->first();

        $company = ($companyRole) ? $companyRole->company : $user->company;

        $users                         = $consultantManagementContract->getUsersByRoleAndCompany(ConsultantManagementContract::ROLE_LIST_OF_CONSULTANT, $company);
        $listOfConsultantUsers         = $users['users'];
        $listOfConsultantImportedUsers = $users['imported_users'];
        $isLOCUser = (array_key_exists($user->id, array_column($listOfConsultantUsers, 'id', 'id')) || array_key_exists($user->id, array_column($listOfConsultantImportedUsers, 'id', 'id')));

        return View::make('consultant_management.user_management.index', compact('consultantManagementContract', 'company', 'isROCUser', 'isLOCUser', 'recommendationOfConsultantUsers', 'recommendationOfConsultantImportedUsers', 'listOfConsultantUsers', 'listOfConsultantImportedUsers'));
    }

    public function userManagementStore(ConsultantManagementContract $consultantManagementContract)
    {
        $this->userManagementForm->validate(Input::all());
        
        $user   = \Confide::user();
        $inputs = Input::all();
        $role   = (int)$inputs['role'];

        $viewers = (array_key_exists('viewer', $inputs)) ? $inputs['viewer'] : [];
        $editors = (array_key_exists('editor', $inputs)) ? $inputs['editor'] : [];

        foreach($viewers as $idx => $userId)
        {
            if(in_array($userId, $editors))
            {
                unset($viewers[$idx]);//remove user id from viewer list if exists in editor list (redundant as editor is a viewer)
            }
        }

        $records = [];

        foreach($editors as $userId)
        {
            $records[] = [
                'consultant_management_contract_id' => $consultantManagementContract->id,
                'role'       => $role,
                'user_id'    => $userId,
                'editor'     => true,
                'created_by' => $user->id,
                'updated_by' => $user->id,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
        }

        foreach($viewers as $userId)
        {
            $records[] = [
                'consultant_management_contract_id' => $consultantManagementContract->id,
                'role'       => $role,
                'user_id'    => $userId,
                'editor'     => false,
                'created_by' => $user->id,
                'updated_by' => $user->id,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
        }

        if(!empty($records))
        {
            ConsultantManagementUserRole::where('consultant_management_contract_id', '=', $consultantManagementContract->id)
            ->where('role', '=', $role)
            ->delete();

            ConsultantManagementUserRole::insert($records);

            $roleTxt = ($role == ConsultantManagementContract::ROLE_LIST_OF_CONSULTANT) ? ConsultantManagementContract::ROLE_LIST_OF_CONSULTANT_TEXT : ConsultantManagementContract::ROLE_RECOMMENDATION_OF_CONSULTANT_TEXT;

            $recipients = User::select('users.*')
            ->join('consultant_management_user_roles', 'consultant_management_user_roles.user_id', '=', 'users.id')
            ->whereRaw('consultant_management_user_roles.consultant_management_contract_id = '.$consultantManagementContract->id.'
            AND consultant_management_user_roles.role = '.$role.'
            AND consultant_management_user_roles.editor IS TRUE
            AND users.confirmed IS TRUE
            AND users.account_blocked_status IS FALSE')
            ->groupBy('users.id')
            ->get();
            
            if(!empty($recipients))
            {
                $content = [
                    'subject' => "Consultant Management - User Role Assignment (".$roleTxt.")",//need to move this to i10n
                    'view' => 'consultant_management.email.role_management',
                    'data' => [
                        'developmentPlanningTitle' => $consultantManagementContract->title,
                        'subsidiaryName' => $consultantManagementContract->Subsidiary->name,
                        'creator' => $user->name,
                        'contentTxt' => 'You have been assigned as an Editor in above planning process.',
                        'route' => route('consultant.management.contracts.contract.show', [$consultantManagementContract->id])
                    ]
                ];
                
                $this->emailNotifier->sendGeneralEmail($content, $recipients);
            }

            Flash::success("Updated Users (".$roleTxt.") Permission");
        }

        return Redirect::route('consultant.management.user.management.index', [$consultantManagementContract->id]);
    }

    public function companyRoleAssignmentIndex(ConsultantManagementContract $consultantManagementContract)
    {
        $user = Confide::user();

        $recommendationOfConsultantCompany = Company::select('companies.*')
            ->join('consultant_management_company_roles', 'consultant_management_company_roles.company_id', '=', 'companies.id')
            ->where('consultant_management_company_roles.consultant_management_contract_id', '=', $consultantManagementContract->id)
            ->where('consultant_management_company_roles.role', '=', ConsultantManagementContract::ROLE_RECOMMENDATION_OF_CONSULTANT)
            ->first();

        $listOfConsultantCompany = Company::select('companies.*')
            ->join('consultant_management_company_roles', 'consultant_management_company_roles.company_id', '=', 'companies.id')
            ->where('consultant_management_company_roles.consultant_management_contract_id', '=', $consultantManagementContract->id)
            ->where('consultant_management_company_roles.role', '=', ConsultantManagementContract::ROLE_LIST_OF_CONSULTANT)
            ->first();
        
        $listOfConsultantCompanies = Company::select("companies.*")
            ->join('contract_group_categories', 'companies.contract_group_category_id', '=', 'contract_group_categories.id')
            ->join('consultant_management_roles_contract_group_categories', 'consultant_management_roles_contract_group_categories.contract_group_category_id', '=', 'contract_group_categories.id')
            ->where('consultant_management_roles_contract_group_categories.role', '=', ConsultantManagementContract::ROLE_LIST_OF_CONSULTANT)
            ->orderBy('companies.name', 'asc')
            ->get();

        unset($listOfConsultantCompanyRecords);

        return View::make('consultant_management.company_role_assignment.index', compact('consultantManagementContract', 'recommendationOfConsultantCompany', 'listOfConsultantCompany', 'listOfConsultantCompanies'));
    }

    public function validateCompanyRoleLOCAssignment(ConsultantManagementContract $consultantManagementContract)
    {
        $request    = Request::instance();
        $newCompany = Company::findOrFail((int)$request->get('cid'));
        $user       = \Confide::user();

        $company = Company::select('companies.*')
            ->join('consultant_management_company_roles', 'consultant_management_company_roles.company_id', '=', 'companies.id')
            ->where('consultant_management_company_roles.consultant_management_contract_id', '=', $consultantManagementContract->id)
            ->where('consultant_management_company_roles.role', '=', ConsultantManagementContract::ROLE_LIST_OF_CONSULTANT)
            ->first();
        
        $pendingLocData        = [];
        $pendingCallingRfpData = [];
        $openRfpData           = [];
        $resubmissionRfpData   = [];
        $approvalDocumentData  = [];

        if($company && ($newCompany->id != $company->id))
        {
            $pendingLocData = ConsultantManagementListOfConsultant::getPendingVerificationByCompanyAndContract($company, $consultantManagementContract);

            $companyRole = ConsultantManagementCompanyRole::select('consultant_management_company_roles.company_id AS company_id', 'consultant_management_company_roles.role')
            ->join('consultant_management_contracts', 'consultant_management_company_roles.consultant_management_contract_id', '=', 'consultant_management_contracts.id')
            ->where('consultant_management_contracts.id', '=', $consultantManagementContract->id)
            ->whereRaw('consultant_management_company_roles.calling_rfp IS TRUE')
            ->first();

            if($companyRole->company_id == $company->id)
            {
                $pendingCallingRfpData = ConsultantManagementCallingRfp::getPendingVerificationByCompanyRoleAndContract($company, $consultantManagementContract, $companyRole->role);
                $openRfpData           = ConsultantManagementOpenRfp::getPendingOpenRfpVerificationByCompanyRoleAndContract($company, $consultantManagementContract, $companyRole->role);
                $resubmissionRfpData   = ConsultantManagementOpenRfp::getPendingResubmissionRfpVerificationByCompanyRoleAndContract($company, $consultantManagementContract, $companyRole->role);
                $approvalDocumentData  = ApprovalDocument::getPendingVerificationByCompanyRoleAndContract($company, $consultantManagementContract, $companyRole->role);
            }
        }
        
        $data = [
            'modules' => [
                'loc'              => $pendingLocData,
                'calling_rfp'      => $pendingCallingRfpData,
                'open_rfp'         => $openRfpData,
                'resubmission_rfp' => $resubmissionRfpData,
                'approval_doc'     => $approvalDocumentData
            ],
            'has_pending' => (!empty($pendingLocData) || !empty($pendingCallingRfpData) || !empty($openRfpData) || !empty($resubmissionRfpData) || !empty($approvalDocumentData))
        ];

        return Response::json($data);
    }

    public function validateCompanyRoleCallingRfp(ConsultantManagementContract $consultantManagementContract)
    {
        $request        = Request::instance();
        $newCompanyRole = (int)$request->get('role');
        $user           = \Confide::user();

        $companyRole = ConsultantManagementCompanyRole::select('consultant_management_company_roles.company_id AS company_id', 'consultant_management_company_roles.role')
        ->join('consultant_management_contracts', 'consultant_management_company_roles.consultant_management_contract_id', '=', 'consultant_management_contracts.id')
        ->where('consultant_management_contracts.id', '=', $consultantManagementContract->id)
        ->whereRaw('consultant_management_company_roles.calling_rfp IS TRUE')
        ->first();

        $company = Company::findOrFail($companyRole->company_id);

        $pendingCallingRfpData = [];
        $openRfpData           = [];
        $resubmissionRfpData   = [];
        $approvalDocumentData  = [];

        if($newCompanyRole && ($newCompanyRole != $companyRole->role))
        {
            $pendingCallingRfpData = ConsultantManagementCallingRfp::getPendingVerificationByCompanyRoleAndContract($company, $consultantManagementContract, $companyRole->role);
            $openRfpData           = ConsultantManagementOpenRfp::getPendingOpenRfpVerificationByCompanyRoleAndContract($company, $consultantManagementContract, $companyRole->role);
            $resubmissionRfpData   = ConsultantManagementOpenRfp::getPendingResubmissionRfpVerificationByCompanyRoleAndContract($company, $consultantManagementContract, $companyRole->role);
            $approvalDocumentData  = ApprovalDocument::getPendingVerificationByCompanyRoleAndContract($company, $consultantManagementContract, $companyRole->role);
        }
        
        $data = [
            'modules' => [
                'calling_rfp'      => $pendingCallingRfpData,
                'open_rfp'         => $openRfpData,
                'resubmission_rfp' => $resubmissionRfpData,
                'approval_doc'     => $approvalDocumentData
            ],
            'has_pending' => (!empty($pendingCallingRfpData) || !empty($openRfpData) || !empty($resubmissionRfpData) || !empty($approvalDocumentData))
        ];

        return Response::json($data);
    }

    public function companyRoleAssignmentStore(ConsultantManagementContract $consultantManagementContract)
    {
        $this->assignCompanyRoleForm->validate(Input::all());

        $user    = \Confide::user();
        $inputs  = Input::all();
        $company = Company::findOrFail((int)$inputs['company_id']);

        //remove all users from previous company
        $companyRole = ConsultantManagementCompanyRole::where('consultant_management_contract_id', '=', $consultantManagementContract->id)
        ->where('role', '=', ConsultantManagementContract::ROLE_LIST_OF_CONSULTANT)
        ->first();

        if(!$companyRole || ($companyRole && $companyRole->company_id != $company->id))
        {
            ConsultantManagementUserRole::where('consultant_management_contract_id', '=', $consultantManagementContract->id)
            ->where('role', '=', ConsultantManagementContract::ROLE_LIST_OF_CONSULTANT)
            ->delete();

            $adminUsers = User::select('users.id')
            ->join('companies', 'users.company_id', '=', 'companies.id')
            ->whereNotExists(function($query) use($company, $consultantManagementContract){
                $query->select(\DB::raw(1))
                    ->from('consultant_management_user_roles')
                    ->whereRaw('consultant_management_user_roles.consultant_management_contract_id = '.$consultantManagementContract->id.'
                    AND consultant_management_user_roles.role = '.ConsultantManagementContract::ROLE_LIST_OF_CONSULTANT.'
                    AND consultant_management_user_roles.user_id = users.id');
            })
            ->where('companies.id', '=', $company->id)
            ->whereRaw('users.is_admin IS TRUE')
            ->whereRaw('users.confirmed IS TRUE')
            ->whereRaw('users.account_blocked_status IS FALSE')
            ->orderBy('users.name', 'asc')
            ->get();

            $userRoles = [];
            foreach($adminUsers as $adminUser)
            {
                $userRoles[] = [
                    'role' => ConsultantManagementContract::ROLE_LIST_OF_CONSULTANT,
                    'user_id' => $adminUser->id,
                    'consultant_management_contract_id' => $consultantManagementContract->id,
                    'editor' => true,
                    'created_by' => $user->id,
                    'updated_by' => $user->id,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ];
            }

            $importedAdminUsers = User::select('users.id')
            ->join('company_imported_users', 'company_imported_users.user_id', '=', 'users.id')
            ->whereNotExists(function($query) use($company, $consultantManagementContract){
                $query->select(\DB::raw(1))
                        ->from('consultant_management_user_roles')
                        ->whereRaw('consultant_management_user_roles.consultant_management_contract_id = '.$consultantManagementContract->id.'
                        AND consultant_management_user_roles.role = '.ConsultantManagementContract::ROLE_LIST_OF_CONSULTANT.'
                        AND consultant_management_user_roles.user_id = users.id');
            })
            ->where('company_imported_users.company_id', '=', $company->id)
            ->whereRaw('users.is_admin IS TRUE')
            ->whereRaw('users.confirmed IS TRUE')
            ->whereRaw('users.account_blocked_status IS FALSE')
            ->orderBy('users.name', 'asc')
            ->get();

            foreach($importedAdminUsers as $adminUser)
            {
                $userRoles[] = [
                    'role' => ConsultantManagementContract::ROLE_LIST_OF_CONSULTANT,
                    'user_id' => $adminUser->id,
                    'consultant_management_contract_id' => $consultantManagementContract->id,
                    'editor' => true,
                    'created_by' => $user->id,
                    'updated_by' => $user->id,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ];
            }

            if(!empty($userRoles))
            {
                ConsultantManagementUserRole::insert($userRoles);

                $recipients = User::select('users.*')
                ->join('consultant_management_user_roles', 'consultant_management_user_roles.user_id', '=', 'users.id')
                ->whereRaw('consultant_management_user_roles.consultant_management_contract_id = '.$consultantManagementContract->id.'
                AND consultant_management_user_roles.role = '.ConsultantManagementContract::ROLE_LIST_OF_CONSULTANT.'
                AND consultant_management_user_roles.editor IS TRUE
                AND users.confirmed IS TRUE
                AND users.account_blocked_status IS FALSE')
                ->groupBy('users.id')
                ->get();
                
                if(!empty($recipients))
                {
                    $content = [
                        'subject' => "Consultant Management - Company Role Assignment",//need to move this to i10n
                        'view' => 'consultant_management.email.role_management',
                        'data' => [
                            'developmentPlanningTitle' => $consultantManagementContract->title,
                            'subsidiaryName' => $consultantManagementContract->Subsidiary->name,
                            'creator' => $user->name,
                            'contentTxt' => 'Your department has been assigned to participate in above planning process.',
                            'route' => route('consultant.management.contracts.contract.show', [$consultantManagementContract->id])
                        ]
                    ];
                    
                    $this->emailNotifier->sendGeneralEmail($content, $recipients);
                }
            }
        }
        
        ConsultantManagementCompanyRole::where('consultant_management_contract_id', '=', $consultantManagementContract->id)
        ->where('role', '=', ConsultantManagementContract::ROLE_LIST_OF_CONSULTANT)
        ->delete();

        $companyRole = new ConsultantManagementCompanyRole;

        $companyRole->consultant_management_contract_id = $consultantManagementContract->id;
        $companyRole->role       = ConsultantManagementContract::ROLE_LIST_OF_CONSULTANT;
        $companyRole->company_id = $company->id;
        $companyRole->created_by = $user->id;
        $companyRole->updated_by = $user->id;

        $companyRole->save();

        ConsultantManagementCompanyRole::where('consultant_management_contract_id', '=', $consultantManagementContract->id)
        ->whereRaw('calling_rfp IS TRUE')
        ->update(['calling_rfp' => false]);

        ConsultantManagementCompanyRole::where('consultant_management_contract_id', '=', $consultantManagementContract->id)
        ->where('role', '=', (int)$inputs['calling_rfp'])
        ->update(['calling_rfp' => true]);

        $companyRoles = ConsultantManagementCompanyRole::where('consultant_management_company_roles.consultant_management_contract_id', '=', $consultantManagementContract->id)
        ->orderBy('consultant_management_company_roles.role', 'asc')
        ->get()
        ->toArray();

        $logs = [];

        foreach($companyRoles as $companyRole)
        {
            $logs[] = [
                'role' => $companyRole['role'],
                'consultant_management_contract_id' => $consultantManagementContract->id,
                'company_id'  => $companyRole['company_id'],
                'calling_rfp' => ($companyRole['calling_rfp']),
                'created_by'  => $companyRole['updated_by'],
                'updated_by'  => $companyRole['updated_by'],
                'created_at'  => $companyRole['updated_at'],
                'updated_at'  => $companyRole['updated_at']
            ];
        }

        if(!empty($logs))
        {
            ConsultantManagementCompanyRoleLog::insert($logs);
        }

        Flash::success("Updated Company Roles Assignment");

        return Redirect::route('consultant.management.company.role.assignment.index', [$consultantManagementContract->id]);
    }

    public function companyRoleAssignmentLogs(ConsultantManagementContract $consultantManagementContract)
    {
        $user = \Confide::user();

        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $model = ConsultantManagementCompanyRoleLog::select("consultant_management_company_role_logs.id AS id", "consultant_management_company_role_logs.role",
        "consultant_management_company_role_logs.calling_rfp", "companies.name AS company_name", "users.name AS user_name",
        "consultant_management_company_role_logs.updated_at AS updated_at")
        ->join('companies', 'consultant_management_company_role_logs.company_id', '=', 'companies.id')
        ->join('users', 'consultant_management_company_role_logs.updated_by', '=', 'users.id')
        ->where('consultant_management_company_role_logs.consultant_management_contract_id', '=', $consultantManagementContract->id)
        ->orderBy('consultant_management_company_role_logs.updated_at', 'desc');

        $rowCount = $model->get()->count();

        $records = $model->skip($limit * ($page - 1))
        ->take($limit)
        ->get();

        $data = [];
        
        foreach($records->all() as $key => $record)
        {
            $counter = ($page-1) * $limit + $key + 1;

            $data[] = [
                'id'          => $record->id,
                'counter'     => $counter,
                'role'        => ($record->role == ConsultantManagementContract::ROLE_RECOMMENDATION_OF_CONSULTANT) ? ConsultantManagementContract::ROLE_RECOMMENDATION_OF_CONSULTANT_TEXT : ConsultantManagementContract::ROLE_LIST_OF_CONSULTANT_TEXT,
                'calling_rfp' => $record->calling_rfp,
                'company'     => trim(mb_strtoupper($record->company_name)),
                'updated_by'  => trim($record->user_name),
                'updated_at'  => Carbon::parse($record->updated_at)->format('d/m/Y h:i A')
            ];
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }

    public function vendorCategoryRfpCreate(ConsultantManagementContract $consultantManagementContract)
    {
        $vendorCategoryRfp = null;

        $vendorCategories = VendorCategory::select('vendor_categories.*')
        ->join('contract_group_categories', 'contract_group_categories.id', '=', 'vendor_categories.contract_group_category_id')
        ->join('consultant_management_roles_contract_group_categories', 'consultant_management_roles_contract_group_categories.contract_group_category_id', '=', 'contract_group_categories.id')
        ->where('consultant_management_roles_contract_group_categories.role', '=', ConsultantManagementContract::ROLE_CONSULTANT)
        ->whereRaw('vendor_categories.hidden IS FALSE')
        ->whereRaw("NOT EXISTS (
            SELECT 1
            FROM consultant_management_vendor_categories_rfp
            WHERE consultant_management_vendor_categories_rfp.vendor_category_id = vendor_categories.id
            AND consultant_management_vendor_categories_rfp.consultant_management_contract_id = ".$consultantManagementContract->id."
        )")
        ->orderBy('vendor_categories.name', 'asc')
        ->groupBy('vendor_categories.id')
        ->get();

        $costTypes = [
            ConsultantManagementVendorCategoryRfp::COST_TYPE_CONSTRUCTION_COST => ConsultantManagementVendorCategoryRfp::COST_TYPE_CONSTRUCTION_COST_TEXT,
            ConsultantManagementVendorCategoryRfp::COST_TYPE_LANDSCAPE_COST => ConsultantManagementVendorCategoryRfp::COST_TYPE_LANDSCAPE_COST_TEXT,
            ConsultantManagementVendorCategoryRfp::COST_TYPE_LUMP_SUM_COST => ConsultantManagementVendorCategoryRfp::COST_TYPE_LUMP_SUM_COST_TEXT
        ];

        return View::make('consultant_management.vendor_category_rfp.edit', compact('consultantManagementContract', 'vendorCategoryRfp', 'vendorCategories', 'costTypes'));
    }

    public function vendorCategoryRfpEdit(ConsultantManagementContract $consultantManagementContract, ConsultantManagementVendorCategoryRfp $vendorCategoryRfp)
    {
        $vendorCategories  = [];
        $costTypes         = [
            ConsultantManagementVendorCategoryRfp::COST_TYPE_CONSTRUCTION_COST => ConsultantManagementVendorCategoryRfp::COST_TYPE_CONSTRUCTION_COST_TEXT,
            ConsultantManagementVendorCategoryRfp::COST_TYPE_LANDSCAPE_COST => ConsultantManagementVendorCategoryRfp::COST_TYPE_LANDSCAPE_COST_TEXT,
            ConsultantManagementVendorCategoryRfp::COST_TYPE_LUMP_SUM_COST => ConsultantManagementVendorCategoryRfp::COST_TYPE_LUMP_SUM_COST_TEXT
        ];

        return View::make('consultant_management.vendor_category_rfp.edit', compact('consultantManagementContract', 'vendorCategoryRfp', 'vendorCategories', 'costTypes'));
    }

    public function vendorCategoryRfpStore(ConsultantManagementContract $consultantManagementContract)
    {
        $this->vendorCategoryRfpForm->validate(Input::all());

        $user           = \Confide::user();
        $inputs         = Input::all();
        $vendorCategory = VendorCategory::findOrFail((int)$inputs['vendor_category_id']);

        $vendorCategoryRfp = ConsultantManagementVendorCategoryRfp::find($inputs['id']);

        if(!$vendorCategoryRfp)
        {
            $vendorCategoryRfp = new ConsultantManagementVendorCategoryRfp();

            $vendorCategoryRfp->consultant_management_contract_id = $consultantManagementContract->id;
            $vendorCategoryRfp->created_by = $user->id;
        }

        $vendorCategoryRfp->vendor_category_id = $vendorCategory->id;
        $vendorCategoryRfp->cost_type          = (int)$inputs['cost_type'];
        $vendorCategoryRfp->updated_by         = $user->id;

        $vendorCategoryRfp->save();

        \Flash::success(trans('forms.saved'));

        return Redirect::route('consultant.management.contracts.contract.show', [$consultantManagementContract->id]);
    }

    public function vendorCategoryRfpDelete(ConsultantManagementContract $consultantManagementContract, ConsultantManagementVendorCategoryRfp $vendorCategoryRfp)
    {
        $vendorCategoryRfpId = $vendorCategoryRfp->id;
        $contractId = $vendorCategoryRfp->consultant_management_contract_id;
        $user       = \Confide::user();

        if($vendorCategoryRfp->deletable())
        {
            $vendorCategoryRfp->delete();

            \Log::info("Delete consultant management vendor category rfp [vendor category rfp id: {$vendorCategoryRfpId}]][contract id:{$contractId}][user id:{$user->id}]");
        }

        return Redirect::route('consultant.management.contracts.contract.show', [$contractId]);
    }

    public function validateRemoveRecommendationOfConsultantViewer(ConsultantManagementContract $consultantManagementContract, $userId)
    {
        $user = User::findOrFail((int)$userId);

        $exists = User::select('users.id', 'users.name')
            ->join('consultant_management_user_roles', 'consultant_management_user_roles.user_id', '=', 'users.id')
            ->join('consultant_management_contracts', 'consultant_management_user_roles.consultant_management_contract_id', '=', 'consultant_management_contracts.id')
            ->join('consultant_management_vendor_categories_rfp', 'consultant_management_vendor_categories_rfp.consultant_management_contract_id', '=', 'consultant_management_contracts.id')
            ->join('consultant_management_recommendation_of_consultants','consultant_management_recommendation_of_consultants.vendor_category_rfp_id', '=', 'consultant_management_vendor_categories_rfp.id')
            ->join('consultant_management_recommendation_of_consultant_verifiers', function($join){
                $join->on('consultant_management_recommendation_of_consultant_verifiers.consultant_management_recommendation_of_consultant_id', '=', 'consultant_management_recommendation_of_consultants.id');
                $join->on('consultant_management_recommendation_of_consultant_verifiers.user_id','=', 'users.id');
            })
            ->where('consultant_management_contracts.id', '=', $consultantManagementContract->id)
            ->where('consultant_management_user_roles.role', '=', ConsultantManagementContract::ROLE_RECOMMENDATION_OF_CONSULTANT)
            ->where('users.id', '=', $user->id)
            ->groupBy('users.id')
            ->count();
        
        return Response::json([
            'removable' => empty($exists),
            'msg' => (!empty($exists)) ?  "User has been selected as verifier. Cannot be removed from User List" : ''
        ]);
    }

    public function validateRemoveRecommendationOfConsultantEditor(ConsultantManagementContract $consultantManagementContract, $userId)
    {
        $user = User::findOrFail((int)$userId);

        $exists = User::select('users.id', 'users.name')
            ->join('consultant_management_user_roles', 'consultant_management_user_roles.user_id', '=', 'users.id')
            ->join('consultant_management_contracts', 'consultant_management_user_roles.consultant_management_contract_id', '=', 'consultant_management_contracts.id')
            ->where('consultant_management_contracts.id', '=', $consultantManagementContract->id)
            ->where('consultant_management_user_roles.role', '=', ConsultantManagementContract::ROLE_RECOMMENDATION_OF_CONSULTANT)
            ->whereRaw('consultant_management_user_roles.editor IS TRUE')
            ->where('users.id', '<>', $user->id)
            ->groupBy('users.id')
            ->count();
        
        return Response::json([
            'removable' => !empty($exists),
            'msg' => (empty($exists)) ?  "User cannot be removed as Editor. At least ONE user as Editor must be set" : ''
        ]);
    }

    public function attachmentSettingIndex(ConsultantManagementContract $consultantManagementContract)
    {
        $user = Confide::user();

        return View::make('consultant_management.attachment_settings.index', compact('consultantManagementContract', 'user'));
    }

    public function attachmentSettingList(ConsultantManagementContract $consultantManagementContract)
    {
        $user = Confide::user();

        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $model = ConsultantManagementAttachmentSetting::select("consultant_management_attachment_settings.id AS id", "consultant_management_attachment_settings.title AS title",
        "consultant_management_attachment_settings.mandatory AS mandatory", "consultant_management_attachment_settings.created_at AS created_at")
        ->join('consultant_management_contracts', 'consultant_management_attachment_settings.consultant_management_contract_id', '=', 'consultant_management_contracts.id')
        ->where('consultant_management_contracts.id', '=', $consultantManagementContract->id);

        //tabulator filters
        if($request->has('filters'))
        {
            foreach($request->get('filters') as $filters)
            {
                if(!isset($filters['value']) || is_array($filters['value'])) continue;

                $val = trim($filters['value']);

                switch(trim(strtolower($filters['field'])))
                {
                    case 'title':
                        if(strlen($val) > 0)
                        {
                            $model->where('consultant_management_attachment_settings.title', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                }
            }
        }

        $model->orderBy('consultant_management_attachment_settings.created_at', 'desc');

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
                'title'        => trim($record->title),
                'mandatory'    => $record->mandatory,
                'created_at'   => Carbon::parse($record->created_at)->format('d/m/Y'),
                'deletable'    => $record->deletable(),
                'route:show'   => route('consultant.management.attachment.settings.show', [$consultantManagementContract->id, $record->id]),
                'route:delete' => route('consultant.management.attachment.settings.delete', [$consultantManagementContract->id, $record->id]),
            ];
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }

    public function attachmentSettingShow(ConsultantManagementContract $consultantManagementContract, $attachmentSettingId)
    {
        $attachmentSetting = ConsultantManagementAttachmentSetting::findOrFail((int)$attachmentSettingId);
        $user  = \Confide::user();

        return View::make('consultant_management.attachment_settings.show', compact('attachmentSetting', 'consultantManagementContract', 'user'));
    }

    public function attachmentSettingCreate(ConsultantManagementContract $consultantManagementContract)
    {
        $attachmentSetting = null;
        $user  = \Confide::user();

        return View::make('consultant_management.attachment_settings.edit', compact('attachmentSetting', 'consultantManagementContract', 'user'));
    }

    public function attachmentSettingEdit(ConsultantManagementContract $consultantManagementContract, $attachmentSettingId)
    {
        $attachmentSetting = ConsultantManagementAttachmentSetting::findOrFail((int)$attachmentSettingId);
        $user  = \Confide::user();

        return View::make('consultant_management.attachment_settings.edit', compact('attachmentSetting', 'consultantManagementContract', 'user'));
    }

    public function attachmentSettingStore(ConsultantManagementContract $consultantManagementContract)
    {
        $this->attachmentSettingForm->validate(Input::all());

        $user  = \Confide::user();
        $input = Input::all();

        $attachmentSetting = ConsultantManagementAttachmentSetting::find($input['id']);

        if(!$attachmentSetting)
        {
            $attachmentSetting = new ConsultantManagementAttachmentSetting();

            $attachmentSetting->consultant_management_contract_id = $consultantManagementContract->id;
            $attachmentSetting->created_by = $user->id;
        }

        $attachmentSetting->title      = trim($input['title']);
        $attachmentSetting->mandatory  = $input['mandatory'];
        $attachmentSetting->updated_by = $user->id;

        $attachmentSetting->save();

        \Flash::success(trans('forms.saved'));

        return Redirect::route('consultant.management.attachment.settings.index', [$consultantManagementContract->id]);
    }

    public function attachmentSettingDelete(ConsultantManagementContract $consultantManagementContract,$attachmentSettingId)
    {
        $attachmentSetting = ConsultantManagementAttachmentSetting::findOrFail((int)$attachmentSettingId);
        $user       = \Confide::user();

        if($attachmentSetting->deletable())
        {
            $attachmentSetting->delete();
        }

        return Redirect::route('consultant.management.attachment.settings.index', [$consultantManagementContract->id]);
    }

    public function vendorProfileInfo($companyId)
    {
        $company = Company::findOrFail($companyId);

        $vendorProfile = $company->vendorProfile;

        $vendorCategories = [];

        foreach($company->vendorCategories as $companyVendorCategory)
        {
            $vendorCategories[] = $companyVendorCategory->name;
        }

        $cidbCodeArray = [];

        if($company->cidbCodes != null)
        {
            $cidbCodes = $company->cidbCodes;
            $cidbCodeArray = [];

            foreach($cidbCodes as $cidbCode)
            {

                $cidbCodeArray[] = $cidbCode->code . ' (' .$cidbCode->description . ')';

            }
        }

        $companyDetails = [
            'company_name'          => $company->name,
            'vendor_code'           => ($vendorProfile) ? $company->getVendorCode() : "-",
            'activation_date'       => ($company->activation_date) ? date('d/m/Y', strtotime($company->activation_date)) : '-',
            'expiry_date'           => ($company->expiry_date) ? date('d/m/Y', strtotime($company->expiry_date)) : '-',
            'deactivation_date'     => ($company->deactivation_date) ? date('d/m/Y', strtotime($company->deactivation_date)) : '-',
            'company_address'       => $company->address,
            'company_state'         => $company->state->name,
            'company_country'       => $company->country->country,
            'vendor_group'          => ($vendorProfile) ? $company->contractGroupCategory->name : '-',
            'vendor_categories'     => $vendorCategories,
            'main_contact'          => $company->main_contact,
            'reference_number'      => $company->reference_no,
            'tax_registration_no'   => $company->tax_registration_no,
            'email'                 => $company->email,
            'telephone_number'      => $company->telephone_number,
            'fax_number'            => $company->fax_number,
            'company_status'        => $company->company_status ? Company::getCompanyStatusDescriptions($company->company_status) : '-',
            'bumiputera_equity'     => $company->bumiputera_equity,
            'non_bumiputera_equity' => $company->non_bumiputera_equity,
            'foreigner_equity'      => $company->foreigner_equity,
            'is_contractor'         => $company->isContractor(),
            'cidb_grade'            => ($company->isContractor() && !is_null($company->cidb_grade)) ? CIDBGrade::find($company->cidb_grade)->grade : '-',
            'cidb_codes'            => ($company->isContractor()) ? $cidbCodeArray : '-',
            'is_consultant'         => $company->isConsultant(),
            'bim_level'             => ($company->isConsultant() && !is_null($company->bimLevel)) ? $company->bimLevel->name : '-'
        ];

        $vpeRows = [];
        $count = 0;
        $gradingSystem = PCK\ModuleParameters\VendorManagement\VendorPerformanceEvaluationModuleParameter::first()->vendorManagementGrade;
        $vendorWorkCategoryIds = \PCK\VendorPerformanceEvaluation\CycleScore::where('company_id', '=', $company->id)
            ->orderBy('vendor_work_category_id', 'asc')
            ->lists('vendor_work_category_id', 'vendor_work_category_id');

        if(empty($vendorWorkCategoryIds))
        {
            $row = '<tr style="display:table;width:100%;table-layout:fixed;">';
            $row .= '<td colspan="6"><div class="well text-center">'.trans('general.noRecordsToDisplay').'</div></td>';
            $row .= '</tr>';

            $vpeRows[] = $row;
        }

        foreach($vendorWorkCategoryIds as $vendorWorkCategoryId)
        {
            $cycleScore = \PCK\VendorPerformanceEvaluation\CycleScore::where('company_id', '=', $company->id)
                ->where('vendor_work_category_id', '=', $vendorWorkCategoryId)
                ->orderBy('id', 'desc')
                ->first();
            
            if(!$cycleScore)
                continue;

            $row = '<tr style="display:table;width:100%;table-layout:fixed;">';
            
            $row .= '<td class="text-center" style="width:64px;">'.++$count.'</td>';
            
            $row .= '<td style="min-width:380px;">'.$cycleScore->vendorWorkCategory->name.'</td>';
            $row .= '<td class="text-center">'.$cycleScore->score.'</td>';

            $scoreDesc = ($gradingSystem) ? $gradingSystem->getGrade($cycleScore->score)->description : null;
            $row .= '<td class="text-center">'.$scoreDesc.'</td>';

            $row .= '<td class="text-center">'.$cycleScore->deliberated_score.'</td>';

            $deliberatedScoreDesc = ($gradingSystem) ? $gradingSystem->getGrade($cycleScore->deliberated_score)->description : null;
            $row .= '<td class="text-center">'.$deliberatedScoreDesc.'</td>';

            $row .= '</tr>';

            $vpeRows[] = $row;
        }

        return Response::json([
            'details'  => $companyDetails,
            'vpe_rows' => $vpeRows
        ]);
    }

    public function phaseGeneralAttachmentCount($phaseId, $field)
    {
        $phase  = ConsultantManagementSubsidiary::findOrFail($phaseId);
        $object = ObjectField::findOrCreateNew($phase, $field);

        return Response::json([
            'phase_id'        => $phase->id,
            'field'           => $field,
            'attachmentCount' => count($this->getAttachmentDetails($object)),
        ]);
    }

    public function phaseGeneralAttachmentList($phaseId, $field)
    {
        $phase         = ConsultantManagementSubsidiary::findOrFail($phaseId);
        $object        = ObjectField::findOrCreateNew($phase, $field);
        $uploadedFiles = $this->getAttachmentDetails($object);

        $data = [];

        foreach($uploadedFiles as $file)
        {
            $file['imgSrc']      = $file->generateThumbnailURL();
            $file['deleteRoute'] = $file->generateDeleteURL();
            $file['size']	     = Helpers::formatBytes($file->size);
            $file['deleteRoute'] = route('consultant.management.contracts.phase.general.attachment.delete', [$phase->id, $field, $file->id]);

            $data[] = $file;
        }

        return $data;
    }

    public function phaseGeneralAttachmentStore($phaseId, $field)
    {
        $inputs = Input::all();
        $phase  = ConsultantManagementSubsidiary::findOrFail($phaseId);
        $object = ObjectField::findOrCreateNew($phase, $field);

        ModuleAttachment::saveAttachments($object, $inputs);

        return [
            'success' => true
        ];
    }

    public function phaseGeneralAttachmentDelete($phaseId, $field, $fileId)
    {
        $phase   = ConsultantManagementSubsidiary::findOrFail($phaseId);
        $upload  = Upload::findOrFail($fileId);
        $success = false;

        try
        {
            $upload->delete();

            $success = true;
        }
        catch(\Exception $e)
        {
            $success = false;
        }

        return [
            'success' => $success,
            'count_url' => route('consultant.management.contracts.phase.general.attachment.count', [$phase->id, $field])
        ];
    }

    public function generateContractNumber()
    {
        $request = Request::instance();

        $subsidiary           = Subsidiary::find($request->get('sid'));
        $contract             = ConsultantManagementContract::find($request->get('cid'));
        $subsidiaryIdentifier = '';
        $runningNumber        = str_pad(1, 4, '0', STR_PAD_LEFT);

        if($subsidiary)
        {
            $subsidiaryIdentifier = Subsidiary::find($request->get('sid'))->identifier ?? null;

            $totalContractBySubsidiary = ConsultantManagementContract::where('subsidiary_id', '=', $subsidiary->id)->count();

            $runningNumber = str_pad(($totalContractBySubsidiary + 1), 4, '0', STR_PAD_LEFT);
        }

        $runningNumberPrefix = date('Y');

        $contractNumber = "{$subsidiaryIdentifier}/{$runningNumberPrefix}/{$runningNumber}";

        return Response::json([
            'contract_number' => ($contract && $subsidiary && $contract->subsidiary_id == $subsidiary->id) ? $contract->reference_no : $contractNumber
        ]);
    }

    public function recommendationOfConsultantTodoList($id)
    {
        $contract = ConsultantManagementContract::findOrFail($id);
        $user     = \Confide::user();

        $pendingReviews = ConsultantManagementRecommendationOfConsultant::getPendingReviewsByUser($user, $contract);

        foreach($pendingReviews as $key => $pendingReview)
        {
            $pendingReviews[$key]['route:show'] = route('consultant.management.roc.index', [$pendingReview['rfp_id']]);
        }

        return Response::json($pendingReviews);
    }

    public function listOfConsultantTodoList($id)
    {
        $contract = ConsultantManagementContract::findOrFail($id);
        $user     = \Confide::user();

        $pendingReviews = ConsultantManagementListOfConsultant::getPendingReviewsByUser($user, $contract);

        foreach($pendingReviews as $key => $pendingReview)
        {
            $pendingReviews[$key]['route:show'] = route('consultant.management.loc.show', [$pendingReview['rfp_id'], $pendingReview['loc_id']]);
        }

        return Response::json($pendingReviews);
    }

    public function callingRfpTodoList($id)
    {
        $contract = ConsultantManagementContract::findOrFail($id);
        $user     = \Confide::user();

        $pendingReviews = ConsultantManagementCallingRfp::getPendingReviewsByUser($user, $contract);

        foreach($pendingReviews as $key => $pendingReview)
        {
            $pendingReviews[$key]['route:show'] = route('consultant.management.calling.rfp.show', [$pendingReview['rfp_id'], $pendingReview['calling_rfp_id']]);
        }

        return Response::json($pendingReviews);
    }

    public function openRfpTodoList($id)
    {
        $contract = ConsultantManagementContract::findOrFail($id);
        $user     = \Confide::user();

        $pendingReviews = ConsultantManagementOpenRfp::getPendingReviewsByUser($user, $contract);

        foreach($pendingReviews as $key => $pendingReview)
        {
            $pendingReviews[$key]['route:show'] = route('consultant.management.open.rfp.verifier', [$pendingReview['rfp_id'], $pendingReview['open_rfp_id']]);
        }

        return Response::json($pendingReviews);
    }

    public function rfpResubmissionTodoList($id)
    {
        $contract = ConsultantManagementContract::findOrFail($id);
        $user     = \Confide::user();

        $pendingReviews = ConsultantManagementOpenRfp::getPendingResubmissionReviewsByUser($user, $contract);

        foreach($pendingReviews as $key => $pendingReview)
        {
            $pendingReviews[$key]['route:show'] = route('consultant.management.open.rfp.show', [$pendingReview['rfp_id'], $pendingReview['open_rfp_id']]);
        }

        return Response::json($pendingReviews);
    }

    public function approvalDocumentTodoList($id)
    {
        $contract = ConsultantManagementContract::findOrFail($id);
        $user     = \Confide::user();

        $pendingReviews = ApprovalDocument::getPendingReviewsByUser($user, $contract);

        $pdo = \DB::getPdo();

        $stmt = $pdo->prepare("SELECT doc.id AS approval_document_id, open_rfp.id AS open_rfp_id, com.id AS company_id, com.name AS company_name, COALESCE(SUM(fee.proposed_fee_amount), 0) AS proposed_fee_sum
        FROM consultant_management_approval_documents doc
        JOIN consultant_management_vendor_categories_rfp rfp ON doc.vendor_category_rfp_id = rfp.id
        JOIN consultant_management_rfp_revisions rfp_rev ON rfp_rev.vendor_category_rfp_id = doc.vendor_category_rfp_id
        INNER JOIN (
            SELECT rfp.id, MAX(rfp_rev.revision) AS revision
            FROM consultant_management_contracts c
            JOIN consultant_management_vendor_categories_rfp rfp ON rfp.consultant_management_contract_id = c.id
            JOIN consultant_management_rfp_revisions rfp_rev ON rfp_rev.vendor_category_rfp_id = rfp.id
            JOIN consultant_management_open_rfp open_rfp ON open_rfp.consultant_management_rfp_revision_id = rfp_rev.id
            WHERE c.id = ".$contract->id." AND open_rfp.status IN (".ConsultantManagementOpenRfp::STATUS_APPROVED.", ".ConsultantManagementOpenRfp::STATUS_RESUBMISSION_APPROVED.")
            GROUP BY rfp.id
        ) max_revisions ON max_revisions.id = rfp.id AND max_revisions.revision = rfp_rev.revision
        JOIN consultant_management_open_rfp open_rfp ON open_rfp.consultant_management_rfp_revision_id = rfp_rev.id
        JOIN consultant_management_consultant_rfp con ON con.consultant_management_rfp_revision_id = open_rfp.consultant_management_rfp_revision_id
        LEFT JOIN consultant_management_consultant_rfp_proposed_fees fee ON fee.consultant_management_consultant_rfp_id = con.id
        JOIN companies com ON con.company_id = com.id
        WHERE rfp.consultant_management_contract_id = ".$contract->id."
        AND doc.status = ".ApprovalDocument::STATUS_APPROVAL." AND con.awarded IS TRUE
        AND open_rfp.status IN (".ConsultantManagementOpenRfp::STATUS_APPROVED.", ".ConsultantManagementOpenRfp::STATUS_RESUBMISSION_APPROVED.")
        GROUP BY doc.id, open_rfp.id, com.id");

        $stmt->execute();

        $latestApprovedOpenRfps = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        foreach($pendingReviews as $key => $pendingReview)
        {
            $pendingReviews[$key]['route:show'] = "";
            $pendingReviews[$key]['company_id'] = -1;
            $pendingReviews[$key]['company_name'] = "";
            $pendingReviews[$key]['proposed_fee_sum'] = 0;

            foreach($latestApprovedOpenRfps as $latestApprovedOpenRfp)
            {
                if($pendingReview['approval_document_id'] == $latestApprovedOpenRfp['approval_document_id'])
                {
                    $pendingReviews[$key]['route:show']       = route('consultant.management.approval.document.index', [$pendingReview['rfp_id'], $latestApprovedOpenRfp['open_rfp_id']]);
                    $pendingReviews[$key]['company_id']       = $latestApprovedOpenRfp['company_id'];
                    $pendingReviews[$key]['company_name']     = $latestApprovedOpenRfp['company_name'];
                    $pendingReviews[$key]['proposed_fee_sum'] = $latestApprovedOpenRfp['proposed_fee_sum'];
                }
            }
        }

        return Response::json($pendingReviews);
    }

    public function letterOfAwardTodoList($id)
    {
        $contract = ConsultantManagementContract::findOrFail($id);
        $user     = \Confide::user();

        $pendingReviews = LetterOfAward::getPendingReviewsByUser($user, $contract);

        $pdo = \DB::getPdo();

        $stmt = $pdo->prepare("SELECT loa.id AS loa_id, open_rfp.id AS open_rfp_id, com.id AS company_id, com.name AS company_name, SUM(fee.proposed_fee_amount) AS proposed_fee_sum
        FROM consultant_management_letter_of_awards loa
        JOIN consultant_management_vendor_categories_rfp rfp ON loa.vendor_category_rfp_id = rfp.id
        JOIN consultant_management_rfp_revisions rfp_rev ON rfp_rev.vendor_category_rfp_id = rfp.id
        INNER JOIN (
            SELECT rfp.id, MAX(rfp_rev.revision) AS revision
            FROM consultant_management_contracts c
            JOIN consultant_management_vendor_categories_rfp rfp ON rfp.consultant_management_contract_id = c.id
            JOIN consultant_management_rfp_revisions rfp_rev ON rfp_rev.vendor_category_rfp_id = rfp.id
            JOIN consultant_management_open_rfp open_rfp ON open_rfp.consultant_management_rfp_revision_id = rfp_rev.id
            WHERE c.id = ".$contract->id." AND open_rfp.status IN (".ConsultantManagementOpenRfp::STATUS_APPROVED.", ".ConsultantManagementOpenRfp::STATUS_RESUBMISSION_APPROVED.")
            GROUP BY rfp.id
        ) max_revisions ON max_revisions.id = rfp.id AND max_revisions.revision = rfp_rev.revision
        JOIN consultant_management_open_rfp open_rfp ON open_rfp.consultant_management_rfp_revision_id = rfp_rev.id
        JOIN consultant_management_consultant_rfp con ON con.consultant_management_rfp_revision_id = open_rfp.consultant_management_rfp_revision_id
        JOIN consultant_management_consultant_rfp_proposed_fees fee ON fee.consultant_management_consultant_rfp_id = con.id
        JOIN companies com ON con.company_id = com.id
        WHERE rfp.consultant_management_contract_id = ".$contract->id."
        AND loa.status = ".LetterOfAward::STATUS_APPROVAL." AND con.awarded IS TRUE
        AND open_rfp.status IN (".ConsultantManagementOpenRfp::STATUS_APPROVED.", ".ConsultantManagementOpenRfp::STATUS_RESUBMISSION_APPROVED.")
        GROUP BY loa.id, open_rfp.id, com.id");

        $stmt->execute();

        $latestApprovedOpenRfps = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        foreach($pendingReviews as $key => $pendingReview)
        {
            $pendingReviews[$key]['route:show'] = "";
            $pendingReviews[$key]['company_id'] = -1;
            $pendingReviews[$key]['company_name'] = "";
            $pendingReviews[$key]['proposed_fee_sum'] = 0;

            foreach($latestApprovedOpenRfps as $latestApprovedOpenRfp)
            {
                if($pendingReview['loa_id'] == $latestApprovedOpenRfp['loa_id'])
                {
                    $pendingReviews[$key]['route:show']       = route('consultant.management.loa.index', [$pendingReview['rfp_id']]);
                    $pendingReviews[$key]['company_id']       = $latestApprovedOpenRfp['company_id'];
                    $pendingReviews[$key]['company_name']     = $latestApprovedOpenRfp['company_name'];
                    $pendingReviews[$key]['proposed_fee_sum'] = $latestApprovedOpenRfp['proposed_fee_sum'];
                }
            }
        }

        return Response::json($pendingReviews);
    }

    public function vendorCategoryRfpAccountCodeIndex(ConsultantManagementVendorCategoryRfp $vendorCategoryRfp)
    {
        $contract = $vendorCategoryRfp->consultantManagementContract;
        $accountGroups = BsAccountGroup::get();

        return View::make('consultant_management.vendor_category_rfp.account_code.edit', compact('vendorCategoryRfp', 'contract', 'accountGroups'));
    }

    public function vendorCategoryRfpAccountCodeList(ConsultantManagementVendorCategoryRfp $vendorCategoryRfp, $accountGroupId)
    {
        $accountGroup = BsAccountGroup::findOrFail((int)$accountGroupId);

        $user = \Confide::user();

        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $model = BsAccountCode::select("id", "code", "description", "tax_code", "type")
        ->where('account_group_id', '=', $accountGroup->id)
        ->whereNull('deleted_at');

        $assignedAccountCodeIds = ConsultantManagementVendorCategoryRfpAccountCode::where('vendor_category_rfp_id', $vendorCategoryRfp->id)->lists('account_code_id');

        //tabulator filters
        if($request->has('filters'))
        {
            foreach($request->get('filters') as $filters)
            {
                if(!isset($filters['value']) || is_array($filters['value'])) continue;

                $val = trim($filters['value']);

                switch(trim(strtolower($filters['field'])))
                {
                    case 'code':
                        if(strlen($val) > 0)
                        {
                            $model->where('code', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                    case 'tax_code':
                        if(strlen($val) > 0)
                        {
                            $model->where('tax_code', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                    case 'assigned':
                        if($val === 'Assigned')
                        {
                            $model->whereIn('id', $assignedAccountCodeIds);
                        }
                        elseif($val === 'Unassigned')
                        {
                            $model->whereNotIn('id', $assignedAccountCodeIds);
                        }
                        break;
                }
            }
        }

        $model->orderBy('priority', 'asc');

        $rowCount = $model->get()->count();

        $records = $model->skip($limit * ($page - 1))
        ->take($limit)
        ->get();

        $data = [];
        
        foreach($records->all() as $key => $record)
        {
            $counter = ($page-1) * $limit + $key + 1;

            $data[] = [
                'id'          => $record->id,
                'counter'     => $counter,
                'code'        => $record->code,
                'description' => $record->description,
                'tax_code'    => $record->tax_code,
                'type_txt'    => BsAccountCode::getTypeText($record->type),
                'group_id'    => $accountGroup->id,
                'assigned'    => in_array($record->id, $assignedAccountCodeIds),
            ];
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }

    public function vendorCategoryRfpAccountCodeStore(ConsultantManagementVendorCategoryRfp $vendorCategoryRfp)
    {
        $user  = \Confide::user();
        $input = Input::all();

        $contract = $vendorCategoryRfp->consultantManagementContract;
        $accountCode = BsAccountCode::findOrFail((int)$input['id']);

        try
        {
            ConsultantManagementVendorCategoryRfpAccountCode::where('vendor_category_rfp_id', $vendorCategoryRfp->id)
                ->whereNotIn('account_code_id', BsAccountCode::where('account_group_id', $accountCode->account_group_id)->lists('id'))
                ->delete();

            ConsultantManagementVendorCategoryRfpAccountCode::where('vendor_category_rfp_id', $vendorCategoryRfp->id)
                ->update(['amount' => 0]);

            $record = ConsultantManagementVendorCategoryRfpAccountCode::where('vendor_category_rfp_id', $vendorCategoryRfp->id)
                ->where('account_code_id', $accountCode->id)
                ->first();

            if($record)
            {
                $record->delete();
            }
            else
            {
                ConsultantManagementVendorCategoryRfpAccountCode::create([
                    'vendor_category_rfp_id' => $vendorCategoryRfp->id,
                    'account_code_id'        => $accountCode->id,
                    'created_by'             => $user->id,
                    'updated_by'             => $user->id,
                ]);
            }

            $success = true;
            $errorMsg = '';
        }
        catch(\Exception $e)
        {
            $success = false;
            $errorMsg = $e->getMessage();
        }

        return Response::json([
            'success' => $success,
            'error'   => $errorMsg
        ]);
    }
}
