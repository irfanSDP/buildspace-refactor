<?php namespace PCK\AccountCodeSettings;

use PCK\Helpers\DBTransaction;
use PCK\Users\User;
use PCK\Projects\Project;
use PCK\Verifier\Verifier;
use PCK\Notifications\EmailNotifier;
use PCK\Notifications\SystemNotifier;
use PCK\AccountCodeSettings\AccountCodeSetting;
use PCK\AccountCodeSettings\SubsidiaryApportionmentRecord;
use PCK\Subsidiaries\Subsidiary;
use PCK\Subsidiaries\SubsidiaryRepository;
use PCK\Buildspace\ProjectCodeSetting;
use PCK\Buildspace\ItemCodeSetting;
use PCK\Buildspace\AccountGroup;
use PCK\Buildspace\AccountCode;
use PCK\ModulePermission\ModulePermission;
use PCK\SystemModules\SystemModuleConfiguration;

class AccountCodeSettingRepository
{
    protected $emailNotifier;
    protected $systemNotifier;
    protected $subsidiaryRepository;

    public function __construct(EmailNotifier $emailNotifier, SystemNotifier $systemNotifier, SubsidiaryRepository $subsidiaryRepository)
    {
        $this->emailNotifier        = $emailNotifier;
        $this->systemNotifier       = $systemNotifier;
        $this->subsidiaryRepository = $subsidiaryRepository;
    }

    public function createNewRecord(Project $project)
    {
        $accountCodeSetting = new AccountCodeSetting();
        $accountCodeSetting->project_id = $project->id;
        $accountCodeSetting->created_by = \Confide::user()->id;
        $accountCodeSetting->updated_by = \Confide::user()->id;
        $accountCodeSetting->save();

        return $accountCodeSetting;
    }

    public function getApportionmentTypes()
    {
        return ApportionmentType::all();
    }

    public function getAccountGroups()
    {
        return AccountGroup::all();
    }

    public function getProjectCodeSettingRecords($projectId)
    {
        $project               = Project::find($projectId);
        $projectStructure      = $project->getBsProjectMainInformation()->projectStructure;
        $selectedSubsidiaries  = ProjectCodeSetting::getSelectedSubsidiaries($projectStructure)->toArray();
        $selectedSubsidiaryIds = array_column($selectedSubsidiaries, 'eproject_subsidiary_id');
        $isEditor			   = ModulePermission::isEditor(\Confide::user(), ModulePermission::MODULE_ID_FINANCE);
        $data                  = [];

        if(empty($selectedSubsidiaryIds))
        {
            return [];
        }

        $orderedSubsidiaryRecords = ProjectCodeSetting::getSortedSelectedProjectCodeSettingSubsidiary($selectedSubsidiaryIds);
        $displayHierarchy = self::getHierarchyOfSubsidiaries($orderedSubsidiaryRecords);
        $proportionsGroupedByIds = ProjectCodeSetting::getProportionsGroupedByIds($projectStructure);

        foreach($displayHierarchy as $subId => $hierarchy)
        {
            $projectCodeSetting      = ProjectCodeSetting::getRecordBy($projectStructure, $subId);
            $subsidiaryApportionment = SubsidiaryApportionmentRecord::getSubsidiaryApportionment($projectCodeSetting->subsidiary, $project->accountCodeSetting->apportionmentType->id);
            $apportionment           = null;
            $proportion              = null;
            $canEditApportionment    = false;
            $canEditSubsidiaryCode   = !$project->accountCodeSetting->isLocked();

            if(!$project->accountCodeSetting->isLocked() && ($projectCodeSetting->type == ProjectCodeSetting::TYPE_PHASE_SUBSIDIARY))
            {
                $canEditApportionment = $subsidiaryApportionment ? !$subsidiaryApportionment->isLocked() : true;
            }

            if($projectCodeSetting->type == ProjectCodeSetting::TYPE_PHASE_SUBSIDIARY)
            {
                $apportionment = $subsidiaryApportionment ? $subsidiaryApportionment->value : null;
                $proportion    = isset($proportionsGroupedByIds[$projectCodeSetting->id]) ? $proportionsGroupedByIds[$projectCodeSetting->id] : null;
            }

            array_push($data, [
                'id'                     => $projectCodeSetting->id,
                'eproject_subsidiary_id' => $projectCodeSetting->subsidiary->id,
                'name'                   => $projectCodeSetting->subsidiary->name,
                'subsidiary_code'        => $projectCodeSetting->subsidiary_code,
                'proportion'             => $proportion,
                'level'                  => $hierarchy['level'],
                'apportionment'          => $apportionment,
                'canEditApportionment'   => $canEditApportionment && $isEditor,
                'canEditSubsidiaryCode'  => $canEditSubsidiaryCode && $isEditor,
                'route_delete'           => route('project.code.setting.delete', [$project->id, $projectCodeSetting->id]),
            ]); 
        }

        return $data;
    }

    private function getEntireSubsidiaryTree(Subsidiary $subsidiary)
    {
        $parentsOfSubsidiary = $subsidiary->getParentsOfSubsidiary();
        $rootSubsidiary      = empty($parentsOfSubsidiary) ? $subsidiary : $parentsOfSubsidiary[0];

        return $this->subsidiaryRepository->getHierarchicalCollection($rootSubsidiary->id);
    }

    public function getApprovedPhaseSubsidiaries($projectId)
    {
        $project                   = Project::find($projectId);
        $projectStructure          = $project->getBsProjectMainInformation()->projectStructure;
        $selectedSubsidiaryRecords = ProjectCodeSetting::getSelectedSubsidiaries($projectStructure);
        $apportionmentType         = $project->accountCodeSetting->apportionmentType;
        $itemCodeSettingsData      = [];
        $data                      = [];

        foreach(ItemCodeSetting::getItemCodeSettings($projectStructure) as $itemCodeSetting)
        {
            array_push($itemCodeSettingsData, [
                'id'          => $itemCodeSetting->id,
                'description' => $itemCodeSetting->accountCode->description,
            ]);
        }

        foreach($selectedSubsidiaryRecords as $record)
        {
            $subsidiary = Subsidiary::find($record->eproject_subsidiary_id);
            $apportionment = SubsidiaryApportionmentRecord::getSubsidiaryApportionment($subsidiary, $apportionmentType->id);

            array_push($data, [
                'id'               => $record->id,
                'name'             => $subsidiary->name,
                'identifier'       => $record->subsidiary_code,
                'itemCodeSettings' => $itemCodeSettingsData,
                'weightage'        => is_null($apportionment) ? null : $apportionment->value,
                'proportion'       => null,
            ]);
        }

        return $data;
    }

    public function getProportionsGroupedByIds(Project $project, $selectedIds)
    {
        $projectStructure = $project->getBsProjectMainInformation()->projectStructure;
        $projectCodeSetting = [];

        foreach($selectedIds as $id)
        {
            array_push($projectCodeSetting, ProjectCodeSetting::find($id));
        }

        return ProjectCodeSetting::getProportionsGroupedByIds($projectStructure, $projectCodeSetting);
    }

    public function getSubsidiaryHierarchy($subsidiaryId)
    {
        $subsidiary          = Subsidiary::find($subsidiaryId);
        $subsidiaryHierarchy = $this->getEntireSubsidiaryTree($subsidiary);
        $data = [];

        foreach($subsidiaryHierarchy as $subsidiary)
        {
            array_push($data, [
                'id'         => $subsidiary->id,
                'parent_id'  => $subsidiary->parent_id,
                'name'       => $subsidiary->name,
                'identifier' => $subsidiary->identifier,
                'company_id' => $subsidiary->company->id,
                'company'    => $subsidiary->company->name,
                'level'      => $subsidiary->level,
            ]);
        }

        return $data;
    }

    public function getSelectedSubsidiaries($projectId)
    {
        $project = Project::find($projectId);
        $selectedSubsidiaries = ProjectCodeSetting::getSelectedSubsidiaries($project->getBsProjectMainInformation()->projectStructure);
        return array_column($selectedSubsidiaries->toArray(), 'eproject_subsidiary_id');
    }

    public function saveSelectedSubsidiaries($inputs)
    {
        $project = Project::find($inputs['projectId']);
        $projectStructure = $project->getBsProjectMainInformation()->projectStructure;
        $selectedSubsidiaries = [];
        $savedRecordIds = [];
        $success = false;
        $transaction = new DBTransaction();

        $transaction->begin();

        try
        {
            if(isset($inputs['subsidiaryIds']))
            {
                $hierarchyOfSelectedSubsidiaries = self::getHierarchyOfSubsidiaries($inputs['subsidiaryIds']);

                foreach($hierarchyOfSelectedSubsidiaries as $subsidiaryId => $subsidiaryInfo)
                {
                    $projectCodeSetting = ProjectCodeSetting::getRecordBy($projectStructure, $subsidiaryId);
        
                    if(is_null($projectCodeSetting))
                    {
                        $projectCodeSetting = new ProjectCodeSetting();
                        $projectCodeSetting->eproject_subsidiary_id = $subsidiaryId;
                        $projectCodeSetting->project_structure_id = $projectStructure->id;
                        $projectCodeSetting->subsidiary_code = $subsidiaryInfo['subsidiary_code'];
                    }
        
                    $projectCodeSetting->type = in_array($subsidiaryId, $inputs['subsidiaryIds']) ? ProjectCodeSetting::TYPE_PHASE_SUBSIDIARY : ProjectCodeSetting::TYPE_PARENT_SUBSIDIARY;
                    $projectCodeSetting->created_by = \Confide::user()->getBsUser()->id;
                    $projectCodeSetting->updated_by = \Confide::user()->getBsUser()->id;
                    $projectCodeSetting->save();
        
                    array_push($savedRecordIds, $projectCodeSetting->id);
                }
            }
    
            ProjectCodeSetting::deleteProjectCodeSettings($projectStructure, $savedRecordIds);
    
            $currentSelectedSubsidiaries  = ProjectCodeSetting::getSelectedSubsidiaries($projectStructure)->toArray();
            $currentSelectedSubsidiaryIds = array_column($currentSelectedSubsidiaries, 'eproject_subsidiary_id');
            SubsidiaryApportionmentRecord::deleteApportionments($currentSelectedSubsidiaryIds);

            $transaction->commit();
            $success = true;
        }
        catch(\Exception $exception)
        {
            \Log::error('AccountCodeSettingRepository@saveSelectedSubsidiaries() : ' . $exception->getMessage());
            $transaction->rollback();
        }

        return $success;
    }

    public static function getHierarchyOfSubsidiaries($subsidiaryIds)
    {
        $parentsInformation = [];

        foreach($subsidiaryIds as $id)
        {
            $level = 0;
            $subsidiary = Subsidiary::find($id);
            $parentsOfSubsidiary = $subsidiary->getParentsOfSubsidiary();

            foreach($parentsOfSubsidiary as $parentSub)
            {
                if(empty($parentsInformation))
                {
                    $parentsInformation[$parentSub->id] = [
                        'subsidiary_code' => $parentSub->identifier,
                        'level'           => $level,
                    ];

                    ++ $level;

                    continue;
                }

                if(!array_key_exists($parentSub->id, $parentsInformation))
                {
                    $parentsInformation[$parentSub->id] = [
                        'subsidiary_code' => $parentSub->identifier,
                        'level'           => $level,
                    ];
                }

                ++ $level;
            }

            $parentsInformation[$subsidiary->id] = [
                'subsidiary_code' => $subsidiary->identifier,
                'level'           => $level,
            ];
        }

        return $parentsInformation;
    }

    public function updateSelectedSubsidiaries($inputs)
    {
        $project          = Project::find($inputs['projectId']);
        $projectStructure = $project->getBsProjectMainInformation()->projectStructure;
        $projectCodeSetting = ProjectCodeSetting::find($inputs['id']);
        $fieldName        = $inputs['field'];
        $fieldValue       = $inputs['val'];
        $success          = false;
        $transaction      = new DBTransaction();

        $transaction->begin();

        try
        {
            switch($fieldName)
            {
                case 'subsidiary_code':
                    
                    $projectCodeSetting->subsidiary_code = $fieldValue;
                    $projectCodeSetting->save();
                    break;
                case 'apportionment':
                    $subsidiaryApportionment = SubsidiaryApportionmentRecord::getSubsidiaryApportionment($projectCodeSetting->subsidiary, $project->accountCodeSetting->apportionment_type_id);
            
                    if(is_null($subsidiaryApportionment))
                    {
                        $subsidiaryApportionment = new SubsidiaryApportionmentRecord();
                        $subsidiaryApportionment->subsidiary_id = $projectCodeSetting->subsidiary->id;
                        $subsidiaryApportionment->apportionment_type_id = $project->accountCodeSetting->apportionment_type_id;
                        $subsidiaryApportionment->created_by = \Confide::user()->id;
                    }
                    
                    $subsidiaryApportionment->value = $fieldValue;
                    $subsidiaryApportionment->updated_by = \Confide::user()->id;
                    $subsidiaryApportionment->save();
            
                    $projectCodeSettingPhaseSubsidiaryIds = array_column(ProjectCodeSetting::getSelectedSubsidiaries($projectStructure)->toArray(), 'eproject_subsidiary_id');

                    break;
            }

            $transaction->commit();
            $success = true;
        }
        catch(\Exception $exception)
        {
            \Log::error('AccountCodeSettingRepository@updateSelectedSubsidiaries() : ' . $exception->getMessage());
            $transaction->rollback();
        }

        return $success;
    }

    public function saveApportionmentType($inputs)
    {
        $project = Project::find($inputs['projectId']);
        $projectStructure = $project->getBsProjectMainInformation()->projectStructure;
        $projectCodeSettingPhaseSubsidiaryIds = array_column(ProjectCodeSetting::getSelectedSubsidiaries($projectStructure)->toArray(), 'eproject_subsidiary_id');
        $success          = false;
        $transaction      = new DBTransaction();

        $transaction->begin();

        try
        {
            $accountCodeSetting = $project->accountCodeSetting;
            $accountCodeSetting->apportionment_type_id = $inputs['apportionmentTypeId'];
            $accountCodeSetting->save();

            $transaction->commit();
            $success = true;
        }
        catch(\Exception $exception)
        {
            \Log::error('AccountCodeSettingRepository@updateSelectedSubsidiaries() : ' . $exception->getMessage());
            $transaction->rollback();
        }

        return $success;
    }

    public function updateSupplierCode($inputs)
    {
        $project = Project::find($inputs['projectId']);

        $bsLetterOfAward = $project->getBsProjectMainInformation()->projectStructure->letterOfAward;
        $bsLetterOfAward->creditor_code = $inputs['supplierCode'];
        $bsLetterOfAward->save();

        return true;
    }

    public function getSelectedAccountGroup($inputs)
    {
        $project = Project::find($inputs['projectId']);
        $selectedAccountGroupId = $project->accountCodeSetting->account_group_id;

        if(is_null($selectedAccountGroupId))
        {
            $selectedAccountGroupId = AccountGroup::orderBy('id', 'ASC')->first()->id;
        }

        return $selectedAccountGroupId;
    }

    public function getListOfAccountCodes($inputs)
    {
        $accountGroup = AccountGroup::find($inputs['accountGroupId']);
        $accountCodes = $accountGroup->accountCodes->filter(function($accCode)
        {
            return $accCode->type == AccountCode::ACCOUNT_TYPE_PIV;
        });
        $data = [];
        
        foreach($accountCodes as $accountCode)
        {
            array_push($data, [
                'id'          => $accountCode->id,
                'accountCode' => $accountCode->code,
                'description' => $accountCode->description,
                'taxCode'     => $accountCode->tax_code,
            ]);
        }

        return $data;
    }

    public function saveSelectedAccountCodes($inputs)
    {
        $project            = Project::find($inputs['projectId']);
        $projectStructure   = $project->getBsProjectMainInformation()->projectStructure;
        $accountCodeSetting = $project->accountCodeSetting;
        $success            = false;
        $savedRecordIds     = [];
        $transaction        = new DBTransaction();

        $transaction->begin();

        try
        {
            if($accountCodeSetting->account_group_id != $inputs['accountGroupId'])
            {
                ItemCodeSetting::purgeItemCodeSettings($projectStructure);
            }

            $accountCodeSetting->account_group_id = $inputs['accountGroupId'];
            $accountCodeSetting->save();

            foreach($inputs['itemCodeIds'] as $accountCodeId)
            {
                $itemCodeSetting = ItemCodeSetting::where('project_structure_id', $projectStructure->id)
                                    ->where('account_group_id', $inputs['accountGroupId'])
                                    ->where('account_code_id', $accountCodeId)
                                    ->first();

                if(is_null($itemCodeSetting))
                {
                    $itemCodeSetting = new ItemCodeSetting();
                }
                
                $itemCodeSetting->project_structure_id = $projectStructure->id;
                $itemCodeSetting->account_group_id = $inputs['accountGroupId'];
                $itemCodeSetting->account_code_id = $accountCodeId;
                $itemCodeSetting->created_by = \Confide::user()->getBsUser()->id;
                $itemCodeSetting->updated_by = \Confide::user()->getBsUser()->id;
                $itemCodeSetting->amount = 0;
                $itemCodeSetting->save();

                array_push($savedRecordIds, $itemCodeSetting->id);
            }

            ItemCodeSetting::deleteItemCodeSettings($projectStructure, AccountGroup::find($inputs['accountGroupId']), $savedRecordIds);

            $transaction->commit();
            $success = true;
        }
        catch(\Exception $exception)
        {
            \Log::error('AccountCodeSettingRepository@saveSelectedItemCodes() : ' . $exception->getMessage());
            $transaction->rollback();
        }

        return $success;
    }
    
    public function getSavedItemCodes($projectId)
    {
        $project          = Project::find($projectId);
        $projectStructure = $project->getBsProjectMainInformation()->projectStructure;
        $data             = [];

        foreach($projectStructure->itemCodeSettings as $itemCodeSetting)
        {
            array_push($data, [
                'id'               => $itemCodeSetting->id,
                'accountGroupId'   => $itemCodeSetting->accountGroup->id,
                'accountGroupName' => $itemCodeSetting->accountGroup->name,
                'accountCodeId'    => $itemCodeSetting->accountCode->id,
                'accountCode'      => $itemCodeSetting->accountCode->code,
                'description'      => $itemCodeSetting->accountCode->description,
                'amount'           => $itemCodeSetting->amount,
                'taxCode'          => $itemCodeSetting->accountCode->tax_code,
                'route_delete'     => route('item.code.setting.delete', [$project->id, $itemCodeSetting->id]),
            ]);
        }

        return $data;
    }

    public function submitForApproval(AccountCodeSetting $accountCodeSetting, $inputs)
    {
        $verifiers = array_filter($inputs['verifiers'], function($value)
        {
            return $value != "";
        });

        if( empty( $verifiers ) )
        {
            $accountCodeSetting->status = AccountCodeSetting::STATUS_APPROVED;
            $accountCodeSetting->save();
        }
        else
        {
            Verifier::setVerifiers($verifiers, $accountCodeSetting);

            $accountCodeSetting->submitted_for_approval_by = \Confide::user()->id;
            $accountCodeSetting->status                    = AccountCodeSetting::STATUS_PENDING_FOR_APPROVAL;
            $accountCodeSetting->save();

            Verifier::sendPendingNotification($accountCodeSetting);
        }

        $projectStructure = $accountCodeSetting->project->getBsProjectMainInformation()->projectStructure;
        $projectCodeSettingPhaseSubsidiaryIds = array_column(ProjectCodeSetting::getSelectedSubsidiaries($projectStructure)->toArray(), 'eproject_subsidiary_id');
        SubsidiaryApportionmentRecord::lockApportionmentRecords($projectCodeSettingPhaseSubsidiaryIds, $accountCodeSetting->apportionment_type_id);
        SubsidiaryApportionmentRecord::flushUnusedApportionmentRecords($projectCodeSettingPhaseSubsidiaryIds, $accountCodeSetting->apportionment_type_id);
    }

    public function submitForApprovalCheck(Project $project)
    {
        $errorMessages = [];
        $projectStructure = $project->getBsProjectMainInformation()->projectStructure;
        $projectCodeSettingSelectedSubsidiaries = ProjectCodeSetting::getSelectedSubsidiaries($projectStructure);

        if($projectCodeSettingSelectedSubsidiaries->count() > 0)
        {
            foreach($projectCodeSettingSelectedSubsidiaries as $selectedSubsidiary)
            {
                $subsidiary = Subsidiary::find($selectedSubsidiary->eproject_subsidiary_id);

                if(is_null(SubsidiaryApportionmentRecord::getSubsidiaryApportionment($subsidiary, $project->accountCodeSetting->apportionmentType->id)))
                {
                    array_push($errorMessages, trans('accountCodes.proportionNotDeterminded'));
                    break;
                }
            }
        }
        else
        {
            array_push($errorMessages, trans('accountCodes.subsidiariesNotSelected'));
        }

        if(ItemCodeSetting::getItemCodeSettingsCount($projectStructure) == 0)
        {
            array_push($errorMessages, trans('accountCodes.itemCodeSettingsNotSelected'));
        }

        $bsLetterOfAward = $projectStructure->letterOfAward;

        if($bsLetterOfAward->creditor_code == "")
        {
            array_push($errorMessages, trans('accountCodes.supplierCodeNotUpdated'));
        }

        if(SystemModuleConfiguration::isEnabled(SystemModuleConfiguration::MODULE_ID_VENDOR_MANAGEMENT))
        {
            if(empty($project->accountCodeSetting->vendor_category_id))
            {
                array_push($errorMessages, trans('accountCodes.vendorCategoryNotSelected'));
            }
        }

        $contractSum = 0;

        if($project->pam2006Detail)
        {
            $contractSum = $project->pam2006Detail->contract_sum;
        }

        if($project->indonesiaCivilContractInformation)
        {
            $contractSum = $project->postContractInformation->contract_sum;
        }

        if(ItemCodeSetting::where('project_structure_id', '=', $projectStructure->id)->sum('amount') != $contractSum)
        {
            array_push($errorMessages, trans('accountCodes.itemCodeSettingAmountNotSet'));
        }

        return $errorMessages;
    }

    public function getPendingAccountCodeSettings(User $user, $includeFutureTasks, $project = null)
    {
        $pendingAccountCodeSettings = [];

        if($project)
        {
            $accountCodeSetting = $project->accountCodeSetting;
           
            if(is_null($accountCodeSetting)) return [];

            $isCurrentVerifier = Verifier::isCurrentVerifier($user, $accountCodeSetting);

            $proceed = $includeFutureTasks ? Verifier::isAVerifierInline($user, $accountCodeSetting) : $isCurrentVerifier;

            if($proceed)
            {
                $accountCodeSetting['is_future_task'] = ! $isCurrentVerifier;
                $accountCodeSetting['company_id']     = $project->business_unit_id;

                $pendingAccountCodeSettings[$accountCodeSetting->id] = $accountCodeSetting;
            }
        }
        else
        {
            $records = Verifier::where('verifier_id', $user->id)
                ->where('object_type', AccountCodeSetting::class)
                ->get();

            foreach($records as $record)
            {
                $accountCodeSetting = AccountCodeSetting::find($record->object_id);
                $isCurrentVerifier  = Verifier::isCurrentVerifier($user, $accountCodeSetting);
                $proceed            = $includeFutureTasks ? Verifier::isAVerifierInline($user, $accountCodeSetting) : $isCurrentVerifier;

                if($accountCodeSetting->project && $proceed)
                {
                    $accountCodeSetting['is_future_task'] = ! $isCurrentVerifier;
                    $accountCodeSetting['company_id']     = $accountCodeSetting->project->business_unit_id;

                    $pendingAccountCodeSettings[$accountCodeSetting->id] = $accountCodeSetting;
                }
            }
        }

        return $pendingAccountCodeSettings;
    }
}

