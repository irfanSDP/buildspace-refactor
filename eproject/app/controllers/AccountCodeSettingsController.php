<?php

use PCK\Verifier\Verifier;
use PCK\Buildspace\ProjectMainInformation;
use PCK\Buildspace\ItemCodeSetting;
use PCK\Projects\Project;
use PCK\Subsidiaries\Subsidiary;
use PCK\Subsidiaries\SubsidiaryRepository;
use PCK\ClaimCertificate\ClaimCertificatePaymentRepository;
use PCK\AccountCodeSettings\AccountCodeSettingRepository;
use PCK\AccountCodeSettings\AccountCodeSetting;
use PCK\ModulePermission\ModulePermission;
use PCK\Tenders\TenderRepository;
use PCK\VendorCategory\VendorCategory;
use PCK\SystemModules\SystemModuleConfiguration;
use PCK\Forms\AccountCodeSettingsVendorCategoryForm;
use PCK\Forms\ItemCodeSettingAmountForm;

class AccountCodeSettingsController extends \BaseController
{
	private $subsidiaryRepository;
	private $claimCertificatePaymentRepository;
	private $accountCodeSettingRepository;
	private $tenderRepository;
	private $accountCodeSettingsVendorCategoryForm;
	private $itemCodeSettingAmountForm;

	public function __construct(SubsidiaryRepository $subsidiaryRepository, ClaimCertificatePaymentRepository $claimCertificatePaymentRepository, AccountCodeSettingRepository $accountCodeSettingRepository, TenderRepository $tenderRepository, AccountCodeSettingsVendorCategoryForm $accountCodeSettingsVendorCategoryForm, ItemCodeSettingAmountForm $itemCodeSettingAmountForm)
	{
		$this->subsidiaryRepository = $subsidiaryRepository;
		$this->claimCertificatePaymentRepository = $claimCertificatePaymentRepository;
		$this->accountCodeSettingRepository = $accountCodeSettingRepository;
		$this->tenderRepository = $tenderRepository;
		$this->accountCodeSettingsVendorCategoryForm = $accountCodeSettingsVendorCategoryForm;
		$this->itemCodeSettingAmountForm = $itemCodeSettingAmountForm;
	}

	public function index()
	{
		$user = Confide::user();
        $subsidiaries = $this->subsidiaryRepository->getHierarchicalCollection();

        if(!$user->isSuperAdmin())
        {
			$visibleSubsidiaryIds = $user->modulePermission(ModulePermission::MODULE_ID_FINANCE)->first()->subsidiaries->lists('id');

            $subsidiaries = $subsidiaries->filter(function($subsidiary) use ($visibleSubsidiaryIds) {
                return in_array($subsidiary->id, $visibleSubsidiaryIds);
            });
        }

		$subsidiaries = $subsidiaries->lists('fullName', 'id');

        return View::make('finance.projects.index', [
			'subsidiaries' => $subsidiaries,
			'statuses'	   => AccountCodeSetting::getStatusKeyValues(),
		]);
	}

	public function getProjectsList()
	{
		$user = Confide::user();
		$params = Input::all();
		$subsidiaryId = isset($params['subsidiaryId']) ? $params['subsidiaryId'] : null;
		$filters =  isset($params['filters']) ? $params['filters'] : [];
		$projects = [];

		$query = "select i.eproject_origin_id
					from bs_project_main_information i
					join bs_project_structures p on p.id = i.project_structure_id
					where i.eproject_origin_id is not null
					and i.status = " . ProjectMainInformation::STATUS_POSTCONTRACT;

		if(!$user->isSuperAdmin())
		{
			$visibleProjectIds = [];
            
      $visibleSubsidiaryIds = $user->modulePermission(ModulePermission::MODULE_ID_FINANCE)->first()->subsidiaries->lists('id');

			foreach(Subsidiary::whereIn('id', $visibleSubsidiaryIds)->get() as $subsidiary)
			{
				$listOfSubsidiaryIds = $subsidiary->getSubsidiaryChildrenIdRecursively();

				foreach(Subsidiary::whereIn('id', $listOfSubsidiaryIds)->get() as $childSub)
				{
					foreach($childSub->projects as $project)
					{
						array_push($visibleProjectIds, $project->id);
					}
				}
			}

			if(count($visibleProjectIds) > 0)
			{
				$query .= " and eproject_origin_id in (" . implode(',', $visibleProjectIds) . ") ";
			}

		}

		if(!empty($subsidiaryId))
        {
			$visibleProjectIds = [];
			$subsidiary = Subsidiary::find($subsidiaryId);
			$listOfSubsidiaryIds = $subsidiary->getSubsidiaryChildrenIdRecursively();

			foreach($listOfSubsidiaryIds as $subId)
			{
				$subsidiary = Subsidiary::find($subId);

				foreach($subsidiary->projects as $project)
				{
					array_push($visibleProjectIds, $project->id);
				}
			}

            if(count($visibleProjectIds) > 0)
            {
                $query .= " and eproject_origin_id in (" . implode(',', $visibleProjectIds) . ") ";
            }
		}

		$query .= " group by i.eproject_origin_id ";
		
		$queryResults = \DB::connection('buildspace')->select(\DB::raw($query));

		$data = [];
		$count = 0;

		foreach($queryResults as $result)
		{
			$project = Project::find($result->eproject_origin_id);

			if(is_null($project)) continue;

			$accountCodeSetting = $project->accountCodeSetting;
			$status = $project->accountCodeSetting ? $project->accountCodeSetting->status : AccountCodeSetting::STATUS_OPEN;

			if(isset($params['statusId']))
			{
				if($status != $params['statusId']) continue;
			}

			array_push($data, [
				'indexNo'          => ++$count,
				'projectId'	       => $project->id,
				'projectTitle'     => $project->title,
				'projectReference' => $project->reference,
				'subsidiaryId'	   => $project->subsidiary->id,
				'subsidiary'	   => $project->subsidiary->name,
				'status'		   => $accountCodeSetting ? $accountCodeSetting->status : AccountCodeSetting::STATUS_OPEN,
				'statusText'	   => $accountCodeSetting ? AccountCodeSetting::getStatusText($accountCodeSetting->status) : AccountCodeSetting::getStatusText(AccountCodeSetting::STATUS_OPEN),
				'route_show'	   => route('finance.account.code.settings.show', [$project->id]),
			]);
		}

		return Response::json($data);
	}

	public function show(Project $project)
	{
		$accountCodeSetting = $project->accountCodeSetting;

		if(is_null($accountCodeSetting))
		{
			$accountCodeSetting = $this->accountCodeSettingRepository->createNewRecord($project);
			$accountCodeSetting = AccountCodeSetting::find($accountCodeSetting->id);
		}

		$selectedContractor = $project->getBsProjectMainInformation()->projectStructure->tenderSetting->bsCompany->getEprojectCompany();

		$apportionmentTypes  	 = $this->accountCodeSettingRepository->getApportionmentTypes();
		$supplierCode		 	 = $project->getBsProjectMainInformation()->projectStructure->letterOfAward->creditor_code;
		$accountGroups		 	 = $this->accountCodeSettingRepository->getAccountGroups();
		$verifiers				 = $accountCodeSetting->getVerifiers();
		$isEditor			 	 = ModulePermission::isEditor(\Confide::user(), ModulePermission::MODULE_ID_FINANCE);
		$isCurrentVerifier	 	 = Verifier::isCurrentVerifier(\Confide::user(), $accountCodeSetting);
		$isLocked			 	 = $accountCodeSetting->isLocked() || (!$isEditor);
		$verifierLogs        	 = Verifier::getAssignedVerifierRecords($accountCodeSetting, true);
		$selectedContractorName  = $selectedContractor->name;
		$contractSum             = 0;

		if($project->pam2006Detail)
		{
		    $contractSum = floatval($project->pam2006Detail->contract_sum);
		}

		if($project->indonesiaCivilContractInformation)
		{
		    $contractSum = floatval($project->postContractInformation->contract_sum);
		}

		if($vendorManagementModuleEnabled = SystemModuleConfiguration::isEnabled(SystemModuleConfiguration::MODULE_ID_VENDOR_MANAGEMENT))
		{
			$vendorCategories[-1] = trans('forms.select');

			$vendorCategories = $vendorCategories + VendorCategory::select('vendor_categories.id', 'vendor_categories.name')
				->where('company_vendor_category.company_id', '=', $selectedContractor->id)
				->join('company_vendor_category', 'company_vendor_category.vendor_category_id', '=', 'vendor_categories.id')
				->lists('name', 'id');
		}

		return View::make('finance.projects.accountCodes.show', [
			'project'			      => $project,
			'apportionmentTypes'      => $apportionmentTypes,
			'accountCodeSetting'      => $accountCodeSetting,
			'supplierCode'		      => $supplierCode,
			'accountGroups'		      => $accountGroups,
			'verifiers'			      => $verifiers,
			'isCurrentVerifier'	      => $isCurrentVerifier,
			'isLocked'			      => $isLocked,
			'verifierLogs'		      => $verifierLogs,
			'finalSelectedContractor' => $selectedContractorName,
			'vendorCategories'        => $vendorCategories ?? [],
			'contractSum'        	  => $contractSum,
			'vendorManagementModuleEnabled' => $vendorManagementModuleEnabled,
			'beneficiaryBankAccountNumber'  => $accountCodeSetting->beneficiary_bank_account_number,
		]);
	}

	public function getProjectCodeSettingRecords()
	{
		$inputs = Input::all();
		$projectCodeSettingRecords = $this->accountCodeSettingRepository->getProjectCodeSettingRecords($inputs['projectId']);

		return Response::json($projectCodeSettingRecords);
	}

	public function getSubsidiaryHierarchy()
	{
		$inputs = Input::all();
		$subsidiaryHierarchy = $this->accountCodeSettingRepository->getSubsidiaryHierarchy($inputs['subsidiaryId']);

		return Response::json($subsidiaryHierarchy);
	}

	public function getSelectedSubsidiaries()
	{
		$inputs = Input::all();
		$selectedSubsidiaries = $this->accountCodeSettingRepository->getSelectedSubsidiaries($inputs['projectId']);

		return Response::json($selectedSubsidiaries);
	}

	public function getApprovedPhaseSubsidiaries(Project $project)
	{
		$selectedPhaseSubsidiaries = $this->accountCodeSettingRepository->getApprovedPhaseSubsidiaries($project->id);
		
		return Response::json($selectedPhaseSubsidiaries);
	}

	public function getProportionsGroupedByIds(Project $project)
	{
		$inputs = Input::all();

		if(!isset($inputs['selectedIds']))
		{
			$inputs['selectedIds'] = [];
		}
		
		$proportionsGroupedByIds = $this->accountCodeSettingRepository->getProportionsGroupedByIds($project, $inputs['selectedIds']);

		return Response::json(json_encode($proportionsGroupedByIds));
	}

	public function saveSelectedSubsidiaries()
	{
		$inputs = Input::all();
		$results = $this->accountCodeSettingRepository->saveSelectedSubsidiaries($inputs);

		return Response::json($results);
	}

	public function updateSelectedSubsidiaries()
	{
		$inputs = Input::all();
		$result = $this->accountCodeSettingRepository->updateSelectedSubsidiaries($inputs);
		
		return Response::json($result);
	}

	public function saveApportionmentType()
	{
		$inputs = Input::all();
		$result = $this->accountCodeSettingRepository->saveApportionmentType($inputs);

		return Response::json($result);
	}

	public function updateSupplierCode()
	{
		$inputs = Input::all();
		$result = $this->accountCodeSettingRepository->updateSupplierCode($inputs);
		
		return Response::json($result); 
	}

	public function updateBeneficiaryBankAccountNumber($project)
	{
		$success  = false;
		$errorMsg = null;

		try
		{
			$project->accountCodeSetting->beneficiary_bank_account_number = Input::get('beneficiary_bank_account_number');

			$success = $project->accountCodeSetting->save();
		}
		catch(\Exception $e)
		{
			\Log::error($e->getMessage());
			\Log::error($e->getTraceAsString());

			$errorMsg = trans('forms.anErrorOccured');
		}

		return Response::json([
			'success' => $success,
			'errorMsg' => $errorMsg,
		]);
	}

	public function updateVendorCategory($project)
	{
		$this->accountCodeSettingsVendorCategoryForm->setCompanyId($project->getBsProjectMainInformation()->projectStructure->tenderSetting->bsCompany->getEprojectCompany()->id);

		$this->accountCodeSettingsVendorCategoryForm->validate(Input::all());

		$success  = false;
		$errorMsg = null;

		if($this->accountCodeSettingsVendorCategoryForm->success)
		{
			$project->accountCodeSetting->vendor_category_id = Input::get('vendor_category_id');

			$success = $project->accountCodeSetting->save();
		}
		else
		{
			$errorMsg = $this->accountCodeSettingsVendorCategoryForm->getErrors()->first();
		}

		return Response::json([
			'success' => $success,
			'errorMsg' => $errorMsg,
		]);
	}

	public function getSelectedAccountGroup()
	{
		$inputs = Input::all();
		$selectedAccountGroupId = $this->accountCodeSettingRepository->getSelectedAccountGroup($inputs);

		return Response::json([
			'selectedAccountGroupId' => $selectedAccountGroupId,
		]);
	}

	public function getListOfAccountCodes()
	{
		$inputs = Input::all();
		$listOfAccountCodes = $this->accountCodeSettingRepository->getListOfAccountCodes($inputs);

		return Response::json($listOfAccountCodes);
	}

	public function saveSelectedAccountCodes()
	{
		$inputs = Input::all();
		$success = $this->accountCodeSettingRepository->saveSelectedAccountCodes($inputs);

		return Response::json([
			'success' => $success,
		]);
	}

	public function getSelectedAccountCodes()
	{
		$selectedAccountCodes = array_column($this->accountCodeSettingRepository->getSavedItemCodes(Input::get('projectId')), 'accountCodeId');

		return Response::json([
			'selectedAccountCodes' => $selectedAccountCodes,
		]);
	}

	public function getSavedItemCodes()
	{
		$savedItemCodes = $this->accountCodeSettingRepository->getSavedItemCodes(Input::get('projectId'));

		return Response::json($savedItemCodes);
	}

	public function submitForApproval(Project $project)
    {
		$inputs = Input::all();

		$this->accountCodeSettingRepository->submitForApproval($project->accountCodeSetting, $inputs);

        return Redirect::back();
	}
	
	public function submitForApprovalCheck(Project $project)
	{
		$errorMessages = $this->accountCodeSettingRepository->submitForApprovalCheck($project);

		return Response::json([
			'errorMessages' => $errorMessages,
		]);
	}

	public function saveItemCodeSettingsAmounts(Project $project)
	{
		$bsUser = \Confide::user()->getBsUser();

		$success = false;

		$this->itemCodeSettingAmountForm->setProject($project);

		$this->itemCodeSettingAmountForm->validate(Input::all());

		if($this->itemCodeSettingAmountForm->success)
		{
			foreach(Input::get('item_code_setting_amounts') as $amountInfo)
			{
				$itemCodeSetting = ItemCodeSetting::find($amountInfo['id']);
				$itemCodeSetting->amount = $amountInfo['amount'];
				$itemCodeSetting->updated_by = $bsUser->id;
				$itemCodeSetting->save();
			}

			$success = true;
		}

		return Response::json([
            'success'       => $success,
			'errorMessages' => $this->itemCodeSettingAmountForm->getErrors(),
		]);
	}
}
