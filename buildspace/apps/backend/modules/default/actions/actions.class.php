<?php

class defaultActions extends BaseActions {

   public function executeIndex(sfWebRequest $request)
{
    // 1. If user is not logged in at all, send them to our Symfony login page
    if (!$this->getUser()->isAuthenticated()) {
        return $this->redirect('auth_login'); // <- local login, NOT SAML
    }

    // 2. We're logged in (via sfGuard session). Continue with your existing access checks.
    $gu      = method_exists($this->getUser(), 'getGuardUser') ? $this->getUser()->getGuardUser() : null;
    $profile = $gu ? $gu->Profile : null;
    $epu     = ($profile && method_exists($profile, 'getEProjectUser')) ? $profile->getEProjectUser() : null;

    // If this user doesn't have allow_access_to_buildspace, send to @no_access
    // BUT: @no_access MUST NOT trigger SAML anymore.
    if (!$gu || !$epu || !$epu->allow_access_to_buildspace) {
        return $this->redirect('@no_access');
    }

    // 3. Normal logic that picks which app window to open
    $this->data = null;

    if ($bsApp = $request->getParameter('bsApp')) {
        switch ($bsApp) {
            case Menu::BS_APP_IDENTIFIER_PROJECT_BUILDER:
                $this->forward('default', 'projectBuilderIndex');
            case Menu::BS_APP_IDENTIFIER_TENDERING:
                $this->forward('default', 'tenderingIndex');
            case Menu::BS_APP_IDENTIFIER_POST_CONTRACT:
                $this->forward('default', 'postContractIndex');
            case Menu::BS_APP_IDENTIFIER_PROJECT_BUILDER_REPORT:
                $this->forward('default', 'projectBuilderReportIndex');
            case Menu::BS_APP_IDENTIFIER_TENDERING_REPORT:
                $this->forward('default', 'tenderingReportIndex');
            case Menu::BS_APP_IDENTIFIER_POST_CONTRACT_REPORT:
                $this->forward('default', 'postContractReportIndex');
            case Menu::BS_APP_IDENTIFIER_APPROVAL:
                $this->forward('default', 'approvalIndex');
            case Menu::BS_APP_IDENTIFIER_PROJECT_MANAGEMENT:
                $this->forward('default', 'projectManagementIndex');
            case Menu::BS_APP_IDENTIFIER_RESOURCE_LIBRARY:
                $this->forward('default', 'resourceLibraryIndex');
            case Menu::BS_APP_IDENTIFIER_SCHEDULE_OF_RATE_LIBRARY:
                $this->forward('default', 'scheduleOfRateIndex');
            case Menu::BS_APP_IDENTIFIER_BQ_LIBRARY:
                $this->forward('default', 'bqLibraryIndex');
            case Menu::BS_APP_IDENTIFIER_COMPANY_DIRECTORIES:
                $this->forward('default', 'companyDirectoriesIndex');
            case Menu::BS_APP_IDENTIFIER_REQUEST_FOR_QUOTATION:
                $this->forward('default', 'requestForQuotationIndex');
            case Menu::BS_APP_IDENTIFIER_PURCHASE_ORDER:
                $this->forward('default', 'purchaseOrderIndex');
            case Menu::BS_APP_IDENTIFIER_STOCK_IN:
                $this->forward('default', 'stockInIndex');
            case Menu::BS_APP_IDENTIFIER_STOCK_OUT:
                $this->forward('default', 'stockOutIndex');
            case Menu::BS_APP_IDENTIFIER_RESOURCE_LIBRARY_REPORT:
                $this->forward('default', 'resourceLibraryReportingIndex');
            case Menu::BS_APP_IDENTIFIER_SCHEDULE_OF_RATE_LIBRARY_REPORT:
                $this->forward('default', 'scheduleOfRateReportingIndex');
            case Menu::BS_APP_IDENTIFIER_BQ_LIBRARY_REPORT:
                $this->forward('default', 'bqLibraryReportingIndex');
            case Menu::BS_APP_IDENTIFIER_STOCK_IN_REPORT:
                $this->forward('default', 'stockInReportingIndex');
            case Menu::BS_APP_IDENTIFIER_STOCK_OUT_REPORT:
                $this->forward('default', 'stockOutReportingIndex');
            case Menu::BS_APP_IDENTIFIER_PRINTING_LAYOUT_SETTING:
                $this->forward('default', 'printLayoutSettingIndex');
            case Menu::BS_APP_IDENTIFIER_SYSTEM_MAINTENANCE:
                $this->forward('default', 'systemMaintenanceIndex');
            case Menu::BS_APP_IDENTIFIER_SYSTEM_ADMINISTRATION:
                $this->forward('default', 'systemAdministrationIndex');
            case Menu::BS_APP_IDENTIFIER_EPROJECT_SITE_PROGRESS:
                $this->forward('default', 'eprojectSiteProgressIndex');
            default:
                break;
        }
    }

    // If we don't forward, Symfony will render apps/backend/modules/default/templates/indexSuccess.php
}



    public function executeProjectBuilderIndex(sfWebRequest $request)
    {
        $bsAppName = Menu::BS_APP_NAME_PROJECT_BUILDER;

        $this->forward404Unless($this->getUser()->getGuardUser()->hasMenuItemAccess($bsAppName));

        $project = null;

        $hasProjectUserPermission = false;

        if($request->hasParameter('id') && is_numeric($request->getParameter('id')))
        {
            $project = Doctrine_Core::getTable('ProjectMainInformation')->findOneByEProjectId($request->getParameter('id'))->ProjectStructure;
        }

        if($project)
        {
            $hasProjectUserPermission = $this->getUser()->getGuardUser()->hasProjectUserPermission($project, ProjectUserPermission::STATUS_PROJECT_BUILDER);
        }

        $allowedProjectStatuses = array(ProjectMainInformation::STATUS_PRETENDER);

        if($hasProjectUserPermission && in_array($project->MainInformation->status, $allowedProjectStatuses))
        {
            $this->data = array(
                'bs_app_name' => $bsAppName,
                'bs_app_method' => 'createBuilderWin',
                'bs_app_method_args' => array('projectData' => $this->getModuleProjectData($project)),
                'bs_app_method_args_store_keys' => array('projectData'),
            );
        }
        else
        {
            $this->data = array(
                'bs_app_name' => $bsAppName,
                'bs_app_method' => 'init',
                'bs_app_method_args' => array(),
            );
        }

        $this->setTemplate('index', 'default');
    }

    public function executeTenderingIndex(sfWebRequest $request)
    {
        $bsAppName = Menu::BS_APP_NAME_TENDERING;

        $this->forward404Unless($this->getUser()->getGuardUser()->hasMenuItemAccess($bsAppName));

        $project = null;

        $hasProjectUserPermission = false;

        if($request->hasParameter('id') && is_numeric($request->getParameter('id')))
        {
            $project = Doctrine_Core::getTable('ProjectMainInformation')->findOneByEProjectId($request->getParameter('id'))->ProjectStructure;
        }

        if($project)
        {
            $hasProjectUserPermission = $this->getUser()->getGuardUser()->hasProjectUserPermission($project, ProjectUserPermission::STATUS_TENDERING);
        }

        $allowedProjectStatuses = array(ProjectMainInformation::STATUS_TENDERING, ProjectMainInformation::STATUS_POSTCONTRACT);

        if($hasProjectUserPermission && in_array($project->MainInformation->status, $allowedProjectStatuses))
        {
            $this->data = array(
                'bs_app_name' => $bsAppName,
                'bs_app_method' => 'createBuilderWin',
                'bs_app_method_args' => array('projectData' => $this->getModuleProjectData($project, ProjectMainInformation::STATUS_TENDERING)),
                'bs_app_method_args_store_keys' => array('projectData'),
            );
        }
        else
        {
            $this->data = array(
                'bs_app_name' => $bsAppName,
                'bs_app_method' => 'init',
                'bs_app_method_args' => array(),
            );
        }

        $this->setTemplate('index', 'default');
    }

    public function executePostContractIndex(sfWebRequest $request)
    {
        $bsAppName = Menu::BS_APP_NAME_POST_CONTRACT;

        $this->forward404Unless($this->getUser()->getGuardUser()->hasMenuItemAccess($bsAppName));

        $project = null;

        $hasProjectUserPermission = false;

        if($request->hasParameter('id') && is_numeric($request->getParameter('id')))
        {
            $project = Doctrine_Core::getTable('ProjectMainInformation')->findOneByEProjectId($request->getParameter('id'))->ProjectStructure;
        }

        if($project)
        {
            $hasProjectUserPermission = $this->getUser()->getGuardUser()->hasProjectUserPermission($project, ProjectUserPermission::STATUS_POST_CONTRACT);
        }

        $allowedProjectStatuses = array(ProjectMainInformation::STATUS_POSTCONTRACT);

        if($hasProjectUserPermission && in_array($project->MainInformation->status, $allowedProjectStatuses))
        {
            $this->data = array(
                'bs_app_name' => $bsAppName,
                'bs_app_method' => 'createBuilderWin',
                'bs_app_method_args' => array('projectData' => $this->getModuleProjectData($project)),
                'bs_app_method_args_store_keys' => array('projectData'),
            );
        }
        else
        {
            $this->data = array(
                'bs_app_name' => $bsAppName,
                'bs_app_method' => 'init',
                'bs_app_method_args' => array(),
            );
        }

        $this->setTemplate('index', 'default');
    }

    public function executeProjectBuilderReportIndex(sfWebRequest $request)
    {
        $bsAppName = Menu::BS_APP_NAME_PROJECT_BUILDER_REPORT;

        $this->forward404Unless($this->getUser()->getGuardUser()->hasMenuItemAccess($bsAppName));

        $project = null;

        $hasProjectUserPermission = false;

        if($request->hasParameter('id') && is_numeric($request->getParameter('id')))
        {
            $project = Doctrine_Core::getTable('ProjectMainInformation')->findOneByEProjectId($request->getParameter('id'))->ProjectStructure;
        }

        if($project)
        {
            $hasProjectUserPermission = $this->getUser()->getGuardUser()->hasProjectUserPermission($project, ProjectUserPermission::STATUS_PROJECT_BUILDER);
        }

        $allowedProjectStatuses = array(ProjectMainInformation::STATUS_PRETENDER);

        if($hasProjectUserPermission && in_array($project->MainInformation->status, $allowedProjectStatuses))
        {
            $this->data = array(
                'bs_app_name' => $bsAppName,
                'bs_app_method' => 'createBuilderWin',
                'bs_app_method_args' => array('projectData' => $this->getModuleProjectData($project)),
                'bs_app_method_args_store_keys' => array('projectData'),
            );
        }
        else
        {
            $this->data = array(
                'bs_app_name' => $bsAppName,
                'bs_app_method' => 'init',
                'bs_app_method_args' => array(),
            );
        }

        $this->setTemplate('index', 'default');
    }

    public function executeTenderingReportIndex(sfWebRequest $request)
    {
        $bsAppName = Menu::BS_APP_NAME_TENDERING_REPORT;

        $this->forward404Unless($this->getUser()->getGuardUser()->hasMenuItemAccess($bsAppName));

        $project = null;

        $hasProjectUserPermission = false;

        if($request->hasParameter('id') && is_numeric($request->getParameter('id')))
        {
            $project = Doctrine_Core::getTable('ProjectMainInformation')->findOneByEProjectId($request->getParameter('id'))->ProjectStructure;
        }

        if($project)
        {
            $hasProjectUserPermission = $this->getUser()->getGuardUser()->hasProjectUserPermission($project, ProjectUserPermission::STATUS_TENDERING);
        }

        $allowedProjectStatuses = array(ProjectMainInformation::STATUS_TENDERING, ProjectMainInformation::STATUS_POSTCONTRACT);

        if($hasProjectUserPermission && in_array($project->MainInformation->status, $allowedProjectStatuses))
        {
            $this->data = array(
                'bs_app_name' => $bsAppName,
                'bs_app_method' => 'createBuilderWin',
                'bs_app_method_args' => array('projectData' => $this->getModuleProjectData($project, ProjectMainInformation::STATUS_TENDERING)),
                'bs_app_method_args_store_keys' => array('projectData'),
            );
        }
        else
        {
            $this->data = array(
                'bs_app_name' => $bsAppName,
                'bs_app_method' => 'init',
                'bs_app_method_args' => array(),
            );
        }

        $this->setTemplate('index', 'default');
    }

    public function executePostContractReportIndex(sfWebRequest $request)
    {
        $bsAppName = Menu::BS_APP_NAME_POST_CONTRACT_REPORT;

        $this->forward404Unless($this->getUser()->getGuardUser()->hasMenuItemAccess($bsAppName));

        $project = null;

        $hasProjectUserPermission = false;

        if($request->hasParameter('id') && is_numeric($request->getParameter('id')))
        {
            $project = Doctrine_Core::getTable('ProjectMainInformation')->findOneByEProjectId($request->getParameter('id'))->ProjectStructure;
        }

        if($project)
        {
            $hasProjectUserPermission = $this->getUser()->getGuardUser()->hasProjectUserPermission($project, ProjectUserPermission::STATUS_POST_CONTRACT);
        }

        $allowedProjectStatuses = array(ProjectMainInformation::STATUS_POSTCONTRACT);

        if($hasProjectUserPermission && in_array($project->MainInformation->status, $allowedProjectStatuses))
        {
            $this->data = array(
                'bs_app_name' => $bsAppName,
                'bs_app_method' => 'createBuilderWin',
                'bs_app_method_args' => array('projectData' => $this->getModuleProjectData($project)),
                'bs_app_method_args_store_keys' => array('projectData'),
            );
        }
        else
        {
            $this->data = array(
                'bs_app_name' => $bsAppName,
                'bs_app_method' => 'init',
                'bs_app_method_args' => array(),
            );
        }

        $this->setTemplate('index', 'default');
    }

    public function executeApprovalIndex(sfWebRequest $request)
    {
        $this->setTemplate('index', 'default');

        $bsApp = Menu::BS_APP_NAME_APPROVAL;

        $user = $this->getUser()->getGuardUser();

        $pdo = ProjectStructureTable::getInstance()->getConnection()->getDbh();

        $project = null;

        $this->data = null;

        if($request->hasParameter('id') && is_numeric($request->getParameter('id')))
        {
            $project = Doctrine_Core::getTable('ProjectMainInformation')->findOneByEProjectId($request->getParameter('id'))->ProjectStructure;
        }

        $moduleIdentifier = $request->getParameter('module_identifier');

        if(!$project || !$moduleIdentifier) return;

        $isCurrentVerifier = ($objectId = $request->getParameter('object_id')) ? ContractManagementClaimVerifierTable::isCurrentVerifier($user, $project, $moduleIdentifier, $objectId) : ContractManagementVerifierTable::isCurrentVerifier($user, $project, $moduleIdentifier);

        $projectData = $this->getModuleProjectData($project, null, false);

        if($request->hasParameter('module_identifier') && is_numeric($moduleIdentifier) && $isCurrentVerifier && isset($projectData))
        {
            $object = array();
            $record = null;

            switch($moduleIdentifier)
            {
                case PostContractClaim::TYPE_LETTER_OF_AWARD:
                    break;
                case PostContractClaim::TYPE_ADVANCED_PAYMENT:
                case PostContractClaim::TYPE_WATER_DEPOSIT:
                case PostContractClaim::TYPE_DEPOSIT:
                case PostContractClaim::TYPE_OUT_OF_CONTRACT_ITEM:
                case PostContractClaim::TYPE_PURCHASE_ON_BEHALF:
                case PostContractClaim::TYPE_WORK_ON_BEHALF:
                case PostContractClaim::TYPE_WORK_ON_BEHALF_BACK_CHARGE:
                case PostContractClaim::TYPE_PENALTY:
                case PostContractClaim::TYPE_PERMIT:
                case PostContractClaim::TYPE_POST_CONTRACT_CLAIM_MATERIAL_ON_SITE:
                    $record = Doctrine_Core::getTable('PostContractClaim')->find($objectId);
                    break;
                case PostContractClaim::TYPE_VARIATION_ORDER:
                    $record = Doctrine_Core::getTable('VariationOrder')->find($objectId);
                    $object['is_approved'] = $record->is_approved;
                    break;
                case PostContractClaim::TYPE_CLAIM_CERTIFICATE:
                    $record = Doctrine_Core::getTable('ClaimCertificate')->find($objectId);
                    $object['post_contract_claim_revision_id'] = $record->post_contract_claim_revision_id;
                    break;
                default:
                    throw new Exception('Invalid module');
            }

            if($record)
            {
                $object['id']     = $record->id;
                $object['status'] = $record->status;
            }

            $object['module_identifier'] = $moduleIdentifier;

            $this->data = array(
                'bs_app_name' => $bsApp,
                'bs_app_method' => 'createBuilderWin',
                'bs_app_method_args' => array('projectData' => $projectData, 'object' => $object),
                'bs_app_method_args_store_keys' => array('projectData'),
            );
        }
    }

    public function executeProjectManagementIndex(sfWebRequest $request)
    {
        $bsAppName = Menu::BS_APP_NAME_PROJECT_MANAGEMENT;

        $this->forward404Unless($this->getUser()->getGuardUser()->hasMenuItemAccess($bsAppName));

        $project = null;

        $hasProjectUserPermission = false;

        if($request->hasParameter('id') && is_numeric($request->getParameter('id')))
        {
            $project = Doctrine_Core::getTable('ProjectMainInformation')->findOneByEProjectId($request->getParameter('id'))->ProjectStructure;
        }

        if($project)
        {
            $hasProjectUserPermission = $this->getUser()->getGuardUser()->hasProjectUserPermission($project, ProjectUserPermission::STATUS_PROJECT_MANAGEMENT);
        }

        if($hasProjectUserPermission)
        {
            $this->data = array(
                'bs_app_name' => $bsAppName,
                'bs_app_method' => 'createBuilderWin',
                'bs_app_method_args' => array('projectData' => $this->getModuleProjectData($project)),
                'bs_app_method_args_store_keys' => array('projectData'),
            );
        }
        else
        {
            $this->data = array(
                'bs_app_name' => $bsAppName,
                'bs_app_method' => 'init',
                'bs_app_method_args' => array(),
            );
        }

        $this->setTemplate('index', 'default');
    }

    public function executeResourceLibraryIndex(sfWebRequest $request)
    {
        $bsAppName = Menu::BS_APP_NAME_RESOURCE_LIBRARY;

        $this->forward404Unless($this->getUser()->getGuardUser()->hasMenuItemAccess($bsAppName));

        $this->data = array(
            'bs_app_name' => $bsAppName,
            'bs_app_method' => 'init',
            'bs_app_method_args' => array(),
        );

        $this->setTemplate('index', 'default');
    }

    public function executeScheduleOfRateIndex(sfWebRequest $request)
    {
        $bsAppName = Menu::BS_APP_NAME_SCHEDULE_OF_RATE_LIBRARY;

        $this->forward404Unless($this->getUser()->getGuardUser()->hasMenuItemAccess($bsAppName));

        $this->data = array(
            'bs_app_name' => $bsAppName,
            'bs_app_method' => 'init',
            'bs_app_method_args' => array(),
        );

        $this->setTemplate('index', 'default');
    }

    public function executeBqLibraryIndex(sfWebRequest $request)
    {
        $bsAppName = Menu::BS_APP_NAME_BQ_LIBRARY;

        $this->forward404Unless($this->getUser()->getGuardUser()->hasMenuItemAccess($bsAppName));

        $this->data = array(
            'bs_app_name' => $bsAppName,
            'bs_app_method' => 'init',
            'bs_app_method_args' => array(),
        );

        $this->setTemplate('index', 'default');
    }

    public function executeCompanyDirectoriesIndex(sfWebRequest $request)
    {
        $bsAppName = Menu::BS_APP_NAME_COMPANY_DIRECTORIES;

        $this->forward404Unless($this->getUser()->getGuardUser()->hasMenuItemAccess($bsAppName));

        $this->data = array(
            'bs_app_name' => $bsAppName,
            'bs_app_method' => 'init',
            'bs_app_method_args' => array(),
        );

        $this->setTemplate('index', 'default');
    }

    public function executeRequestForQuotationIndex(sfWebRequest $request)
    {
        $bsAppName = Menu::BS_APP_NAME_REQUEST_FOR_QUOTATION;

        $this->forward404Unless($this->getUser()->getGuardUser()->hasMenuItemAccess($bsAppName));

        $this->data = array(
            'bs_app_name' => $bsAppName,
            'bs_app_method' => 'init',
            'bs_app_method_args' => array(),
        );

        $this->setTemplate('index', 'default');
    }

    public function executePurchaseOrderIndex(sfWebRequest $request)
    {
        $bsAppName = Menu::BS_APP_NAME_PURCHASE_ORDER;

        $this->forward404Unless($this->getUser()->getGuardUser()->hasMenuItemAccess($bsAppName));

        $this->data = array(
            'bs_app_name' => $bsAppName,
            'bs_app_method' => 'init',
            'bs_app_method_args' => array(),
        );

        $this->setTemplate('index', 'default');
    }

    public function executeStockInIndex(sfWebRequest $request)
    {
        $bsAppName = Menu::BS_APP_NAME_STOCK_IN;

        $this->forward404Unless($this->getUser()->getGuardUser()->hasMenuItemAccess($bsAppName));

        $this->data = array(
            'bs_app_name' => $bsAppName,
            'bs_app_method' => 'init',
            'bs_app_method_args' => array(),
        );

        $this->setTemplate('index', 'default');
    }

    public function executeStockOutIndex(sfWebRequest $request)
    {
        $bsAppName = Menu::BS_APP_NAME_STOCK_OUT;

        $this->forward404Unless($this->getUser()->getGuardUser()->hasMenuItemAccess($bsAppName));

        $this->data = array(
            'bs_app_name' => $bsAppName,
            'bs_app_method' => 'init',
            'bs_app_method_args' => array(),
        );

        $this->setTemplate('index', 'default');
    }

    public function executeResourceLibraryReportingIndex(sfWebRequest $request)
    {
        $bsAppName = Menu::BS_APP_NAME_RESOURCE_LIBRARY_REPORT;

        $this->forward404Unless($this->getUser()->getGuardUser()->hasMenuItemAccess($bsAppName));

        $this->data = array(
            'bs_app_name' => $bsAppName,
            'bs_app_method' => 'init',
            'bs_app_method_args' => array(),
        );

        $this->setTemplate('index', 'default');
    }

    public function executeScheduleOfRateReportingIndex(sfWebRequest $request)
    {
        $bsAppName = Menu::BS_APP_NAME_SCHEDULE_OF_RATE_LIBRARY_REPORT;

        $this->forward404Unless($this->getUser()->getGuardUser()->hasMenuItemAccess($bsAppName));

        $this->data = array(
            'bs_app_name' => $bsAppName,
            'bs_app_method' => 'init',
            'bs_app_method_args' => array(),
        );

        $this->setTemplate('index', 'default');
    }

    public function executeBqLibraryReportingIndex(sfWebRequest $request)
    {
        $bsAppName = Menu::BS_APP_NAME_BQ_LIBRARY_REPORT;

        $this->forward404Unless($this->getUser()->getGuardUser()->hasMenuItemAccess($bsAppName));

        $this->data = array(
            'bs_app_name' => $bsAppName,
            'bs_app_method' => 'init',
            'bs_app_method_args' => array(),
        );

        $this->setTemplate('index', 'default');
    }

    public function executeStockInReportingIndex(sfWebRequest $request)
    {
        $bsAppName = Menu::BS_APP_NAME_STOCK_IN_REPORT;

        $this->forward404Unless($this->getUser()->getGuardUser()->hasMenuItemAccess($bsAppName));

        $this->data = array(
            'bs_app_name' => $bsAppName,
            'bs_app_method' => 'init',
            'bs_app_method_args' => array(),
        );

        $this->setTemplate('index', 'default');
    }

    public function executeStockOutReportingIndex(sfWebRequest $request)
    {
        $bsAppName = Menu::BS_APP_NAME_STOCK_OUT_REPORT;

        $this->forward404Unless($this->getUser()->getGuardUser()->hasMenuItemAccess($bsAppName));

        $this->data = array(
            'bs_app_name' => $bsAppName,
            'bs_app_method' => 'init',
            'bs_app_method_args' => array(),
        );

        $this->setTemplate('index', 'default');
    }

    public function executePrintLayoutSettingIndex(sfWebRequest $request)
    {
        $bsAppName = Menu::BS_APP_NAME_PRINTING_LAYOUT_SETTING;

        $this->forward404Unless($this->getUser()->getGuardUser()->hasMenuItemAccess($bsAppName));

        $this->data = array(
            'bs_app_name' => $bsAppName,
            'bs_app_method' => 'init',
            'bs_app_method_args' => array(),
        );

        $this->setTemplate('index', 'default');
    }

    public function executeSystemMaintenanceIndex(sfWebRequest $request)
    {
        $bsAppName = Menu::BS_APP_NAME_SYSTEM_MAINTENANCE;

        $this->forward404Unless($this->getUser()->getGuardUser()->hasMenuItemAccess($bsAppName));

        $this->data = array(
            'bs_app_name' => $bsAppName,
            'bs_app_method' => 'init',
            'bs_app_method_args' => array(),
        );

        $this->setTemplate('index', 'default');
    }

    public function executeSystemAdministrationIndex(sfWebRequest $request)
    {
        $bsAppName = Menu::BS_APP_NAME_SYSTEM_ADMINISTRATION;

        $this->forward404Unless($this->getUser()->getGuardUser()->hasMenuItemAccess($bsAppName));

        $this->data = array(
            'bs_app_name' => $bsAppName,
            'bs_app_method' => 'init',
            'bs_app_method_args' => array(),
        );

        $this->setTemplate('index', 'default');
    }

    public function executeEprojectSiteProgressIndex(sfWebRequest $request)
    {
        $bsAppName = Menu::BS_APP_NAME_EPROJECT_SITE_PROGRESS;

        $project = null;

        $this->data = null;

        if($request->hasParameter('id') && is_numeric($request->getParameter('id')))
        {
            $project     = Doctrine_Core::getTable('ProjectMainInformation')->findOneByEProjectId($request->getParameter('id'))->ProjectStructure;
            $projectData = $this->getModuleProjectData($project);
        }

        $allowedProjectStatuses = array(ProjectMainInformation::STATUS_POSTCONTRACT);

        if($project && isset($projectData) && in_array($project->MainInformation->status, $allowedProjectStatuses))
        {
            $this->data = array(
                'bs_app_name' => $bsAppName,
                'bs_app_method' => 'createBuilderWin',
                'bs_app_method_args' => array('projectData' => $projectData),
                'bs_app_method_args_store_keys' => array('projectData'),
            );
        }

        $this->setTemplate('index', 'default');
    }

    public function executeGetMyApps(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest());

        $this->getResponse()->setContentType('application/json; charset=UTF-8');

        if (!method_exists($this->getUser(), 'getGuardUser') || !$this->getUser()->isAuthenticated()) {
            $this->getResponse()->setStatusCode(401);
            return $this->renderText(json_encode(['error' => true, 'message' => 'Not authenticated']));
        }

        $guard = $this->getUser()->getGuardUser();
        $items = $guard && method_exists($guard, 'getMenuItems') ? (array) $guard->getMenuItems() : [];

        return $this->renderText(json_encode($items, JSON_UNESCAPED_SLASHES));
    }


    public function executeGetMyProfile(sfWebRequest $request)
{
  $isDev = (sfConfig::get('sf_environment') === 'dev');

  // --- your dynamic re-auth block (unchanged) -----------------------------
  if ($isDev && !$this->getUser()->isAuthenticated()) {
    $userId = $this->getUser()->getAttribute('user_id', null, 'sfGuardSecurityUser');
    if (!$userId) {
      $cookieName = sfConfig::get('app_sf_guard_plugin_remember_cookie_name', 'sfRemember');
      if ($val = $request->getCookie($cookieName)) {
        if ($rk = Doctrine_Core::getTable('sfGuardRememberKey')->findOneByRememberKey($val)) {
          $userId = method_exists($rk, 'getUserId') ? $rk->getUserId() : $rk->user_id;
        }
      }
    }
    if ($userId && ($u = Doctrine_Core::getTable('sfGuardUser')->find($userId))) {
      $this->getUser()->signIn($u, true);
    }
  }
  // ------------------------------------------------------------------------

  $this->forward404Unless($request->isXmlHttpRequest());
  sfConfig::set('sf_web_debug', false);
  $this->getResponse()->setContentType('application/json; charset=UTF-8');

  if (!method_exists($this->getUser(), 'getGuardUser') || !$this->getUser()->isAuthenticated()) {
    $this->getResponse()->setStatusCode(401);
    return $this->renderText(json_encode([
      'error' => true,
      'message' => 'Not authenticated'
    ]));
  }

  // reload user with LEFT JOIN profile — and capture DQL/SQL/params
  $sessionUser = $this->getUser()->getGuardUser();

  $q = Doctrine_Query::create()
        ->from('sfGuardUser u')
        ->leftJoin('u.Profile p')
        ->where('u.id = ?', $sessionUser->id)
        ->limit(1);

  // Get the generated SQL (with placeholders) and parameters
  $debug = [];
  if ($isDev) {
    try {
      $debug = [
        'dql'    => $q->getDql(),
        'sql'    => $q->getSqlQuery(),         // compiled SQL
        'params' => $q->getFlattenedParams(),  // bound params (positional)
      ];
    } catch (Exception $e) {
      $debug = ['note' => 'Could not compile SQL: '.$e->getMessage()];
    }
  }

  $guard   = $q->fetchOne();
  $profile = ($guard && isset($guard->Profile)) ? $guard->Profile : null;

  $payload = [
    'username'   => $guard ? $guard->username : null,
    'fullname'   => $profile ? $profile->name  : null,
    'profileImg' => $profile ? $profile->getPhoto() : null,
    'curr_abb'   => sfConfig::get('app_default_currency_abbreviation'),
  ];

  if ($isDev) $payload['_debug'] = $debug;

  return $this->renderText(json_encode($payload));
}


/**
 * Helper to extract SQL/params/elapsed from Doctrine_Connection_Profiler.
 */
protected function collectSql(Doctrine_Connection_Profiler $profiler)
{
  $out = [];
  foreach ($profiler->getQueryProfiles() as $qp) {
    /** @var Doctrine_Connection_Profiler_Query $qp */
    $out[] = [
      'sql'     => $qp->getQuery(),
      'params'  => $qp->getParams(),      // bound parameters
      'time_s'  => $qp->getElapsedSecs(), // execution time
    ];
  }
  return $out;
}


public function executeLoginAs(sfWebRequest $r)
{
  // dev only (comment while testing if needed)
  // $this->forward404Unless(sfConfig::get('sf_environment') === 'dev');

  $id = (int)$r->getParameter('id', 1);

  // make sure we're using the SAME cookie name/path the app uses in dev
  ini_set('session.name', 'BuildSpaceEProject-dev');
  ini_set('session.cookie_domain', '');      // host-only
  ini_set('session.cookie_secure', '1');
  ini_set('session.cookie_httponly', '1');
  ini_set('session.cookie_samesite', 'Lax');

  $u = Doctrine_Core::getTable('sfGuardUser')->find($id);
  $this->forward404Unless($u);

  $this->getUser()->signIn($u, true);  // writes guard info into PHP session

  return $this->renderText(json_encode([
    'signed_in_as' => $u->username,
    'cookie'       => @$_SERVER['HTTP_COOKIE'],
    'session'      => [
      'name'   => session_name(),
      'id'     => session_id(),
      'params' => session_get_cookie_params(),
    ],
    'auth'         => [
      'isAuthenticated' => $this->getUser()->isAuthenticated(),
      'guardUserId'     => $u->id,
    ],
  ]));
}


public function executeAmILogged(sfWebRequest $r)
{
  $u = $this->getUser();
  $g = method_exists($u, 'getGuardUser') ? $u->getGuardUser() : null;

  return $this->renderText(json_encode([
    'cookie' => @$_SERVER['HTTP_COOKIE'],
    'session'=> [
      'name' => session_name(),
      'id'   => session_id(),
    ],
    'auth'   => [
      'isAuthenticated' => $u->isAuthenticated(),
      'guardUserId'     => $g ? $g->id : null,
    ],
  ]));
}



//     public function executeGetMyProfile(sfWebRequest $request)
// {
//   // TEMP: relax the AJAX gate while debugging
//   // $this->forward404Unless($request->isXmlHttpRequest());

//   // Diagnostics
//   $ctx   = sfContext::getInstance();
//   $resp  = $ctx->getResponse();
//   $user  = $ctx->getUser();

//   $diag = array(
//     'front'          => 'dev',                       // so we know which front controller ran
//     'request_is_xhr' => $request->isXmlHttpRequest(),
//     'cookie_header'  => isset($_SERVER['HTTP_COOKIE']) ? $_SERVER['HTTP_COOKIE'] : '(none)',
//     'session' => array(
//       'name'   => session_name(),
//       'id'     => session_id(),
//       'status' => function_exists('session_status') ? session_status() : 'n/a',
//       'params' => session_get_cookie_params(),
//     ),
//     'auth' => array(
//       'isAuthenticated' => $user->isAuthenticated(),
//       'guardUserId'     => method_exists($user, 'getGuardUser') && $user->getGuardUser() ? $user->getGuardUser()->id : null,
//     ),
//   );

//   $resp->setHttpHeader('Content-Type', 'application/json');
//   return $this->renderText(json_encode($diag));
// }


    public function executeGetBillAdminSettingDetail(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest());
        $billAdminSetting = DoctrineQuery::create()->select('*')
            ->from('BillAdminSetting s')
            ->fetchOne();

        if ( !$billAdminSetting )
        {
            $billAdminSetting = new BillAdminSetting();
            $billAdminSetting->save();
        }

        return $this->renderJson(array(
            'id'                          => $billAdminSetting->id,
            'buildUpQuantityRoundingType' => $billAdminSetting->build_up_quantity_rounding_type,
            'buildUpRateRoundingType'     => $billAdminSetting->build_up_rate_rounding_type
        ));
    }

    public function executeBillAdminSettingUpdate(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $billAdminSetting = Doctrine_Core::getTable('BillAdminSetting')->find($request->getParameter('id'))
        );

        $billAdminSetting->build_up_quantity_rounding_type = $request->getParameter('buildUpQuantityRoundingType');
        $billAdminSetting->build_up_rate_rounding_type     = $request->getParameter('buildUpRateRoundingType');
        $billAdminSetting->unit_type                       = $request->getParameter('unitType');
        $billAdminSetting->save();

        return $this->renderJson(array(
            'id'                          => $billAdminSetting->id,
            'buildUpQuantityRoundingType' => $billAdminSetting->build_up_quantity_rounding_type,
            'buildUpRateRoundingType'     => $billAdminSetting->build_up_rate_rounding_type
        ));
    }

    public function executeGetSystemMaintenanceMenu()
    {
        return $this->renderJson(array(
            'identifier' => 'name',
            'label'      => 'name',
            'items'      => array(
                array(
                    'name' => "Bill Settings",
                    'slug' => "BillAdminSettingMaintenance",
                    'app'  => true
                ),
                array(
                    'name' => "Dimensions",
                    'slug' => "DimensionMaintenance",
                    'app'  => true
                ),
                array(
                    'name'       => "Units",
                    'slug'       => "Units",
                    'app'        => false,
                    'parent'     => true,
                    '__children' => array(
                        array(
                            'name'  => "Metric",
                            'slug'  => "UnitOfMeasurementMaintenance",
                            'param' => UnitOfMeasurement::UNIT_TYPE_METRIC,
                            'app'   => true
                        ),
                        array(
                            'name'  => "Imperial",
                            'slug'  => "UnitOfMeasurementMaintenance",
                            'param' => UnitOfMeasurement::UNIT_TYPE_IMPERIAL,
                            'app'   => true
                        )
                    ) ),
                array(
                    'name'       => "Company Directory",
                    'slug'       => "CompanyDirectories",
                    'app'        => false,
                    'parent'     => true,
                    '__children' => array(
                        array(
                            'name' => "Business Types",
                            'slug' => "BusinessTypeMaintenance",
                            'app'  => true
                        )
                    ) ),
                array(
                    'name' => 'Regions',
                    'slug' => 'RegionMaintenance',
                    'app'  => true
                ),
                array(
                    'name' => "Work Categories",
                    'slug' => "WorkCategoryMaintenance",
                    'app'  => true
                ),
                array(
                    'name' => 'Project Summary Default Settings',
                    'slug' => 'ProjectSummaryDefaultSettings',
                    'app'  => true
                ),
                array(
                    'name' => 'VO Footer Default Settings',
                    'slug' => 'VoPrintingDefaultSettings',
                    'app'  => true
                ),
                array(
                    'name' => 'Global Calendar',
                    'slug' => 'GlobalCalendarMaintenance',
                    'app'  => true
                ),
                array(
                    'name' => "Predefined Location Codes",
                    'slug' => "PredefinedLocationCodeMaintenance",
                    'app'  => true
                ),
                array(
                    'name'       => "Sub Package Works",
                    'slug'       => "SubPackageWork",
                    'app'        => false,
                    'parent'     => true,
                    '__children' => array(
                        array(
                            'name'  => "Works",
                            'slug'  => "SubPackageWorkMaintenance",
                            'param' => SubPackageWorks::TYPE_1,
                            'app'   => true
                        ),
                        array(
                            'name'  => "Works 2",
                            'slug'  => "SubPackageWorkMaintenance",
                            'param' => SubPackageWorks::TYPE_2,
                            'app'   => true
                        )
                    )
                ),
                array(
                    'name'          => "Claim Certificate Taxes",
                    'slug'          => "ClaimCertificateTaxes",
                    'app'           => false,
                    'parent'        => true,
                    '__children'    => array(
                        array(
                            'name'  => "Claim Certificate Tax",
                            'slug'  => "ClaimCertificateTaxMaintenance",
                            'app'   => true,
                        ),
                    ),
                ),
                array(
                    'name'          => "Account Groups",
                    'slug'          => "AccountGroups",
                    'app'           => false,
                    'parent'        => true,
                    '__children'    => array(
                        array(
                            'name'  => "Account Group",
                            'slug'  => "AccountGroupMaintenance",
                            'app'   => true,
                        ),
                    ),
                ),
                array(
                    'name' => "Retention Sum Code",
                    'slug' => "RetentionSumCodeMaintenance",
                    'app'  => true
                ),
            ),
        ));
    }

    public function executeGlobalNotifier(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest());

        $data = array(
            'success' => true
        );

        return $this->renderJson($data);
    }

    public function executeGetMyProfileInformation(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest());

        $guardUserInfo   = $this->getUser()->getGuardUser();

        return $this->renderJson(array(
                'name'           => $guardUserInfo->Profile->name,
                'email'          => $guardUserInfo->email_address,
                'company'        => $guardUserInfo->Profile->getEProjectUser()->Company->name,
                'contact_number' => $guardUserInfo->Profile->contact_num
        ));
    }

    public function executeUploadMyProfileImage(sfWebRequest $request)
    {
        $this->forward404Unless($request->isMethod('post'));

        sfConfig::set('sf_web_debug', false);
        sfContext::getInstance()->getConfiguration()->loadHelpers(array( 'I18N', 'Asset', 'Url', 'Tag' ));

        $form = new MyProfileImageUploadForm($this->getUser()->getProfile());

        if ( $this->isFormValid($request, $form) )
        {
            $form->save();

            $success          = true;
            $imgURL           = image_path('profiles/' . $form->getObject()->profile_photo);
            $profilePhotoName = $form->getObject()->profile_photo;
            $errorMsgs        = null;
        }
        else
        {
            $success          = false;
            $imgURL           = null;
            $safeFileName     = null;
            $profilePhotoName = null;
            $errorMsgs        = $form->getErrors();
        }

        return $this->renderJson(array( 'success' => $success, 'imgURL' => $imgURL, 'imgName' => $profilePhotoName, 'errorMsgs' => $errorMsgs ));
    }

    public function executeNoAccess(sfWebRequest $request)
    {
        sfConfig::set('sf_web_debug', false);
    }

    public function executeGetEProjectUrl(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest());

        return $this->renderJson(array(
            "eproject_url"=>sfConfig::get('app_e_project_url')
        ));
    }

    public function executeGetCsrfToken(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest());

        $form  = new CsrfForm(null);
        $token = $form->getCSRFToken();

        return $this->renderJson(array( '_csrf_token' => $token ));
    }

    public function executeSyncRatesProgress(sfWebRequest $request)
    {
        $this->forward404Unless(
            $EProject = EProjectProjectTable::getInstance()->find($request->getParameter('id'))
        );

        $project = $EProject->BuildspaceProjectMainInfo->ProjectStructure;

        $logPath = sfConfig::get("sf_root_dir").DIRECTORY_SEPARATOR.'log'.DIRECTORY_SEPARATOR;
        $filePrefix = 'sync_contractor_rates-'.$project->id.'-';

        $syncing = false;

        $companies = [];
        foreach (new \DirectoryIterator($logPath) as $file)
        {
            if($file->isFile() && preg_match("/^({$filePrefix}.*.log)/i", $file->getFilename(), $names) && isset($names[0]))
            {
                $syncing = true;
                $x = explode("-", $names[0]);
                if(!empty($x) && isset($x[2]))
                {
                    $companyId = substr($x[2], 0, strpos($x[2], ".log"));
                    
                    if($company = CompanyTable::getInstance()->find($companyId))
                    {
                        if($eprojectCompany = $company->getEProjectCompany())
                        {
                            $companies[] = $eprojectCompany->id;
                        }
                    }
                }
            }
        }

        //need to reset response headers to handle CORS if request comes from non-origin/cross domains
        $this->getResponse()->clearHttpHeaders();
        $this->getResponse()->setStatusCode(200);
        $this->getResponse()->setHttpHeader("Access-Control-Allow-Origin", "*");
        $this->getResponse()->setHttpHeader('Access-Control-Allow-Methods', 'POST, GET, PUT, DELETE, PATCH, OPTIONS');
        $this->getResponse()->setHttpHeader('Access-Control-Allow-Headers', "Access-Control-Allow-Headers, Access-Control-Allow-Origin, Origin, Accept, X-Requested-With, Content-Type, Access-Control-Request-Method, Access-Control-Request-Headers");
        $this->getResponse()->setHttpHeader('Access-Control-Max-Age', '86400');
        $this->getResponse()->setHttpHeader("Content-Type", "application/json");
        $this->getResponse()->sendHttpHeaders();

        ob_end_flush();

        return $this->renderJson([
            'id' => $EProject->id,
            'companies' => $companies,
            'syncing' => $syncing
        ]);
    }

    public function preExecute()
{
  sfConfig::set('sf_web_debug', false);
}

/**
 * POST /auth/login   { username: "...", password: "...", remember: 1 }
 * Returns 200 + user payload on success, 401 on failure.
 */
public function executeLogin(sfWebRequest $r)
{
  try {
    if (!$r->isMethod(sfRequest::POST)) {
      $this->getResponse()->setStatusCode(405);
      $this->getResponse()->setContentType('application/json; charset=UTF-8');
      return $this->renderText(json_encode(['error'=>true,'message'=>'Method Not Allowed']));
    }

    $this->getResponse()->setContentType('application/json; charset=UTF-8');

    $username = trim((string)$r->getParameter('username', ''));
    $password = (string)$r->getParameter('password', '');
    $remember = (bool)$r->getParameter('remember', false);

    if ($username === '' || $password === '') {
      $this->getResponse()->setStatusCode(400);
      return $this->renderText(json_encode(['error'=>true,'message'=>'Missing username or password']));
    }

    $q = Doctrine_Query::create()
          ->from('sfGuardUser u')
          ->where('LOWER(u.username) = LOWER(?) OR LOWER(u.email_address) = LOWER(?)',
                  array($username, $username))
          ->limit(1);

    /** @var sfGuardUser $u */
    $u = $q->fetchOne();

    if (!$u || !$u->getIsActive() || !$u->checkPassword($password)) {
      $this->getResponse()->setStatusCode(401);
      return $this->renderText(json_encode(['error'=>true,'message'=>'Invalid credentials']));
    }

    $this->getUser()->signIn($u, $remember);

    $p = $u->Profile ?? null;
    $payload = [
      'username'   => (string)$u->username,
      'fullname'   => $p ? (string)$p->name : '',
      'profileImg' => $p ? (string)$p->getPhoto() : '',
      'curr_abb'   => (string)sfConfig::get('app_default_currency_abbreviation', 'RM'),
    ];

    return $this->renderText(json_encode($payload, JSON_UNESCAPED_SLASHES));
  } catch (Exception $e) {
    sfContext::getInstance()->getLogger()->err('Login error: '.$e->getMessage());
    $this->getResponse()->setStatusCode(500);
    // In dev you might want to surface it; in prod keep generic
    $msg = (sfConfig::get('sf_environment') === 'dev') ? $e->getMessage() : 'Internal Server Error';
    return $this->renderText(json_encode(['error'=>true,'message'=>$msg]));
  }
}


/**
 * POST /auth/logout
 */
public function executeLogout(sfWebRequest $r)
{
  $this->getUser()->signOut();
  $this->getResponse()->setContentType('application/json; charset=UTF-8');
  return $this->renderText(json_encode(['ok' => true]));
}
}