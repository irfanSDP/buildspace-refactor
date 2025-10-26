<?php namespace PCK\AssignCompaniesLogs;

use PCK\Users\User;
use PCK\Projects\Project;
use PCK\AssignCompanyInDetailLogs\AssignCompanyInDetailLog;
use PCK\ContractGroupTenderDocumentPermissionLogs\ContractGroupTenderDocumentPermissionLog;
use PCK\ProjectContractGroupTenderDocumentPermissions\ProjectContractGroupTenderDocumentPermission;

class AssignCompaniesLogRepository {

    private $assignCompaniesLog;

    public function __construct(AssignCompaniesLog $assignCompaniesLog)
    {
        $this->assignCompaniesLog = $assignCompaniesLog;
    }

    public function saveLog(Project $project, User $user, ProjectContractGroupTenderDocumentPermission $tenderDocumentGroup, array $groupCompanies = array())
    {
        $this->assignCompaniesLog->project_id = $project->id;
        $this->assignCompaniesLog->user_id    = $user->id;

        $this->assignCompaniesLog->save();

        $tenderDocumentPermissionLog                        = new ContractGroupTenderDocumentPermissionLog();
        $tenderDocumentPermissionLog->assign_company_log_id = $this->assignCompaniesLog->id;
        $tenderDocumentPermissionLog->contract_group_id     = $tenderDocumentGroup->contract_group_id;

        $tenderDocumentPermissionLog->save();

        foreach ( $groupCompanies as $groupId => $companyId )
        {
            $inDetailLog = new AssignCompanyInDetailLog();

            $inDetailLog->assign_company_log_id = $this->assignCompaniesLog->id;
            $inDetailLog->contract_group_id     = $groupId;
            $inDetailLog->company_id            = $companyId;

            $inDetailLog->save();
        }

        return $this->assignCompaniesLog;
    }

}