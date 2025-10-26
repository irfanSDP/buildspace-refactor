<?php

use PCK\Users\User;
use PCK\ContractGroupCategory\ContractGroupCategoryRepository;
use PCK\ContractGroups\Types\Role;
use PCK\Forms\AddNewCompanyForm;
use PCK\Companies\CompanyRepository;
use PCK\ContractGroups\ContractGroupRepository;
use PCK\Licenses\LicenseRepository;
use PCK\ContractGroupCategory\ContractGroupCategory;
use PCK\VendorCategory\VendorCategory;
use PCK\VendorWorkCategory\VendorWorkCategory;
use PCK\ModuleParameters\VendorManagement\VendorRegistrationAndPrequalificationModuleParameter;
use PCK\BusinessEntityType\BusinessEntityType;
use PCK\Settings\SystemSettings;
use PCK\SystemModules\SystemModuleConfiguration;

class CompaniesController extends \BaseController {

    private $compRepo;
    private $gcRepo;
    private $addNewCompanyForm;
    private $contractGroupCategoryRepository;
    private $licenseRepository;

    public function __construct
    (
        CompanyRepository $compRepo,
        ContractGroupRepository $gcRepo,
        AddNewCompanyForm $addNewCompanyForm,
        ContractGroupCategoryRepository $contractGroupCategoryRepository,
        LicenseRepository $licenseRepository
    )
    {
        $this->compRepo                        = $compRepo;
        $this->gcRepo                          = $gcRepo;
        $this->addNewCompanyForm               = $addNewCompanyForm;
        $this->contractGroupCategoryRepository = $contractGroupCategoryRepository;
        $this->licenseRepository               = $licenseRepository;
    }

    public function index()
    {
        $datasource = route('companiesData');

        return View::make('companies.index', compact('datasource'));
    }

    public function ajaxGetCompaniesDataInJson()
    {
        $records = $this->compRepo->allInArray(Input::all());

        foreach($records['aaData'] as $i => $record)
        {
            $records["aaData"][ $i ]["createdAt"] = date('d M Y', strtotime($record["createdAt"]));
        }

        return Response::json($records);
    }

    public function create()
    {
        $companyLimitHasBeenReached = $this->licenseRepository->checkCompanyLimitHasBeenReached();

        if($companyLimitHasBeenReached)
        {
            Flash::error(trans('licenses.companyLimitReached'));

            return Redirect::route('companies');        
        }
        $user = \Confide::user();

        $uploadedFiles = $this->getAttachmentDetails();

        $urlCountry = route('country');
        $urlStates  = route('country.states');
        $countryId  = Input::old('country_id', null);
        $stateId    = Input::old('state_id', null);

        $vendorManagementModuleEnabled = SystemModuleConfiguration::isEnabled(SystemModuleConfiguration::MODULE_ID_VENDOR_MANAGEMENT);

        $urlContractGroupCategories = route('registration.contractGroupCategories');
        $urlVendorCategories        = route('registration.vendorCategories');
        $contractGroupCategoryId    = Input::old('contract_group_category_id');
        $vendorCategoryId           = Input::old('vendor_category_id');
        $businessEntityTypeId       = Input::old('business_entity_type_id');
        $businessEntityTypeName     = Input::old('business_entity_type_other');
        $businessEntityTypes        = BusinessEntityType::where('hidden', false)->orderBy('id', 'ASC')->get();

        $multipleVendorCategories   = VendorRegistrationAndPrequalificationModuleParameter::getValue('allow_only_one_comp_to_reg_under_multi_vendor_category');

        $allowOtherBusinessEntityTypes = SystemSettings::getValue('allow_other_business_entity_types');

        JavaScript::put(compact('urlCountry', 'urlStates', 'countryId', 'stateId', 'urlContractGroupCategories', 'urlVendorCategories', 'contractGroupCategoryId', 'vendorCategoryId', 'businessEntityTypeId', 'businessEntityTypeName', 'allowOtherBusinessEntityTypes'));

        return View::make('companies.create', array(
            'user'                          => $user,
            'uploadedFiles'                 => $uploadedFiles,
            'multipleVendorCategories'      => $multipleVendorCategories,
            'businessEntityTypes'           => $businessEntityTypes,
            'allowOtherBusinessEntityTypes' => $allowOtherBusinessEntityTypes,
            'vendorManagementModuleEnabled' => $vendorManagementModuleEnabled,
        ));
    }

    public function store()
    {
        $inputs = Input::all();

        if(!isset($inputs['business_entity_type_id']) || empty($inputs['business_entity_type_id'])) $inputs['business_entity_type_id'] = null;

        $this->addNewCompanyForm->validate($inputs);

        $company = $this->compRepo->add($inputs, true);

        if( $company->contractGroupCategory->includesContractGroups(Role::CONTRACTOR) )
        {
            Flash::success("Company {$inputs['name']} successfully added! Now add Contractor Details");

            return Redirect::route('companies.contractors.create', array( $company->id ));
        }

        Flash::success("Company {$inputs['name']} successfully added!");

        return Redirect::route('companies');
    }

    public function show($id)
    {
        $company = $this->compRepo->find($id);

        return View::make('companies.show', compact('company'));
    }

    /**
     * Shows the company information of the user.
     *
     * @return \Illuminate\View\View
     */
    public function showMyCompany()
    {
        $user = Confide::user();

        $company = $this->compRepo->find($user->company_id);

        $canEditCompanyDetails = ! SystemModuleConfiguration::isEnabled(SystemModuleConfiguration::MODULE_ID_VENDOR_MANAGEMENT) || ($user->company->contractGroupCategory->type == ContractGroupCategory::TYPE_INTERNAL);

        return View::make('companies.showMyCompany', compact('user', 'company', 'canEditCompanyDetails'));
    }

    public function edit($id)
    {
        $user = \Confide::user();
   
        $company = $this->compRepo->find($id);

        $urlCountry = route('country');
        $urlStates  = route('country.states');
        $stateId    = Input::old('state_id', $company->state_id);
        $countryId  = Input::old('country_id', $company->country_id);

        $urlContractGroupCategories = route('registration.contractGroupCategories');
        $urlVendorCategories        = route('registration.vendorCategories');
        $contractGroupCategoryId    = Input::old('contract_group_category_id') ?? $company->contract_group_category_id;
        $vendorCategoryId           = Input::old('vendor_category_id') ?? $company->vendorCategories()->lists('id');
        $businessEntityTypeId       = Input::old('business_entity_type_id') ?? $company->business_entity_type_id;
        $businessEntityTypeName     = Input::old('business_entity_type_other') ?? $company->business_entity_type_name;
        $multipleVendorCategories   = VendorRegistrationAndPrequalificationModuleParameter::getValue('allow_only_one_comp_to_reg_under_multi_vendor_category');
        $businessEntityTypes        = BusinessEntityType::where('hidden', false)->orderBy('id', 'ASC')->get();

        $allowOtherBusinessEntityTypes = SystemSettings::getValue('allow_other_business_entity_types');

        JavaScript::put(compact('urlCountry', 'urlStates', 'countryId', 'stateId', 'urlContractGroupCategories', 'urlVendorCategories', 'contractGroupCategoryId', 'vendorCategoryId', 'businessEntityTypeId', 'businessEntityTypeName', 'allowOtherBusinessEntityTypes'));

        $uploadedFiles = $this->getAttachmentDetails($company);

        $vendorManagementModuleEnabled = SystemModuleConfiguration::isEnabled(SystemModuleConfiguration::MODULE_ID_VENDOR_MANAGEMENT);

        return View::make('companies.edit', array(
            'user'                          => $user,
            'company'                       => $company,
            'uploadedFiles'                 => $uploadedFiles,
            'multipleVendorCategories'      => $multipleVendorCategories,
            'businessEntityTypes'           => $businessEntityTypes,
            'allowOtherBusinessEntityTypes' => $allowOtherBusinessEntityTypes,
            'vendorManagementModuleEnabled' => $vendorManagementModuleEnabled,
        ));
    }

    public function update($id)
    {
        $company = $this->compRepo->find($id);

        $inputs = Input::all();

        $this->addNewCompanyForm->ignoreUnique($id);
        $this->addNewCompanyForm->validate($inputs);

        $user = \Confide::user();

        $company = $this->compRepo->update($company, $inputs);

        if( $company->contractGroupCategory->includesContractGroups(Role::CONTRACTOR) )
        {
            Flash::success("Company {$inputs['name']} successfully updated! Now add Contractor Details");

            if( $company->contractor )
            {
                return Redirect::route('companies.contractors.edit', array( $company->id, $company->contractor->id ));
            }

            return Redirect::route('companies.contractors.create', array( $company->id ));
        }

        $this->compRepo->deleteAllContractGroupTypes($company);

        Flash::success("Company {$company->name} successfully updated!");

        if( $user->isSuperAdmin() )
        {
            return Redirect::route('companies');
        }

        return Redirect::route('companies.profile');
    }

    public function destroy($companyId)
    {
        $company = $this->compRepo->find($companyId);

        try
        {
            $company->delete();
        }
        catch(Exception $e)
        {
            Flash::error(trans('forms.resourceCannotBeDeleted', array('resource' => $company->name)));

            return Redirect::back();
        }

        Flash::success(trans('forms.resourceDeleted', array('resource' => $company->name)));

        return Redirect::back();
    }

    public function checkUsersForPendingTasks($companyId)
    {
        $company = $this->compRepo->find($companyId);
        $usersWithPendingTasks = $this->compRepo->getUsersWithPendingTasks($company);

        return Response::json([
            'hasUsersWithPendingTasks' => (count($usersWithPendingTasks) > 0),
        ]);
    }

    public function getUsersWithPendingTasks($companyId)
    {
        $company = $this->compRepo->find($companyId);
        $usersWithPendingTasks = $this->compRepo->getUsersWithPendingTasks($company);

        return Response::json($usersWithPendingTasks);
    }

    public function checkCompanyUserCanBeRemoved($companyId, $userId)
    {
        $company = $this->compRepo->find($companyId);
        $user    = User::find($userId);

        return Response::json([
            'companyUserCanBeRemoved' => $this->compRepo->companyUserHasNoPendingTasks($company, $user),
        ]);
    }

    public function getCompanyUserPendingTenderingTasks($companyId, $userId)
    {
        $company = $this->compRepo->find($companyId);
        $user    = User::find($userId);

        return Response::json($this->compRepo->getCompanyUserPendingTenderingTasks($company, $user));
    }

    public function getCompanyUserPendingPostContractTasks($companyId, $userId)
    {
        $company = $this->compRepo->find($companyId);
        $user    = User::find($userId);

        return Response::json($this->compRepo->getCompanyUserPendingPostContractTasks($company, $user));
    }

    public function getCompanyUserPendingSiteModuleTasks($companyId, $userId)
    {
        $company = $this->compRepo->find($companyId);
        $user    = User::find($userId);

        return Response::json($this->compRepo->getCompanyUserPendingSiteModuleTasks($company, $user));
    }

    public function getCompanyUserLetterOfAwardUserPermissions($companyId, $userId)
    {
        $company = $this->compRepo->find($companyId);
        $user    = User::find($userId);

        return Response::json($this->compRepo->getCompanyUserLetterOfAwardUserPermissions($company, $user));
    }

    public function getCompanyUserRequestForVariationUserPermissions($companyId, $userId)
    {
        $company = $this->compRepo->find($companyId);
        $user    = User::find($userId);

        return Response::json($this->compRepo->getCompanyUserRequestForVariationUserPermissions($company, $user));
    }

    public function getCompanyUserContractManagementUserPermissions($companyId, $userId)
    {
        $company = $this->compRepo->find($companyId);
        $user    = User::find($userId);

        return Response::json($this->compRepo->getCompanyUserContractManagementUserPermissions($company, $user));
    }

    public function getCompanyUserSiteManagementUserPermissions($companyId, $userId)
    {
        $company = $this->compRepo->find($companyId);
        $user    = User::find($userId);

        return Response::json($this->compRepo->getCompanyUserSiteManagementUserPermissions($company, $user));
    }

    public function getCompanyUserRequestForInspectionUserPermissions($companyId, $userId)
    {
        $company = $this->compRepo->find($companyId);
        $user    = User::find($userId);

        return Response::json($this->compRepo->getCompanyUserRequestForInspectionUserPermissions($company, $user));
    }

    public function getVendorPerformanceEvaluationApprovals($companyId, $userId)
    {
        $company = $this->compRepo->find($companyId);
        $user    = User::find($userId);

        return Response::json($this->compRepo->getVendorPerformanceEvaluationApprovals($company, $user));
    }

    public function getAllContractGroupCategories()
    {
        $data = ContractGroupCategory::select('id', 'name AS description')
            ->where('hidden', '=', false)
            ->orderBy('name', 'asc')
            ->get();

        return Response::json(array(
            'success' => true,
            'default' => null,
            'data'    => $data
        ));
    }

    public function getVendorCategoryByContractGroupCategoryId($contractGroupCategoryId)
    {
        $data = VendorCategory::select('id', 'name AS description')
            ->where('contract_group_category_id', '=', $contractGroupCategoryId)
            ->where('hidden', '=', false)
            ->orderBy('name', 'asc')
            ->get();

        $success = true;

        return Response::json(compact('success', 'data'));
    }

    public function getVendorWorkCategoriesByVendorCategoryId($vendorCategoryId)
    {
        $vendorCategory = VendorCategory::find($vendorCategoryId);

        $data = [];

        if($vendorCategory)
        {
            $data = $vendorCategory->vendorWorkCategories()
            ->select("vendor_work_categories.id", "vendor_work_categories.name")
            ->where('vendor_work_categories.hidden', '=', false)
            ->orderBy('vendor_work_categories.name', 'asc')
            ->lists("name", "id");
        }

        $success = true;

        return Response::json(compact('success', 'data'));
    }
}