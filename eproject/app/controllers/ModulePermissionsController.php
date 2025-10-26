<?php

use PCK\ModulePermission\ModulePermission;
use PCK\ModulePermission\ModulePermissionRepository;

class ModulePermissionsController extends \BaseController
{
    private $repository;

    public function __construct(ModulePermissionRepository $repository)
    {
        $this->repository = $repository;
    }

    public function index()
    {
        $modules = array(
            ModulePermission::MODULE_ID_TENDER_DOCUMENTS_TEMPLATE     => trans('modulePermissions.tenderDocumentTemplate'),
            ModulePermission::MODULE_ID_FORM_OF_TENDER_TEMPLATE       => trans('modulePermissions.formOfTenderTemplate'),
            ModulePermission::MODULE_ID_TECHNICAL_EVALUATION_TEMPLATE => trans('modulePermissions.technicalEvaluationTemplate'),
            ModulePermission::MODULE_ID_CONTRACTOR_LISTING            => trans('modulePermissions.contractorListing'),
            ModulePermission::MODULE_ID_DEFECTS                       => trans('modulePermissions.defects'),
            ModulePermission::MODULE_ID_WEATHERS                      => trans('modulePermissions.weathers'),
            ModulePermission::MODULE_ID_FINANCE                       => trans('modulePermissions.finance'),
            ModulePermission::MODULE_ID_PROJECTS_OVERVIEW             => trans('modulePermissions.projectsOverview'),
            ModulePermission::MODULE_ID_MASTER_COST_DATA              => trans('modulePermissions.masterCostData'),
            ModulePermission::MODULE_ID_COST_DATA                     => trans('modulePermissions.costData'),
            ModulePermission::MODULE_ID_LETTER_OF_AWARD               => trans('modulePermissions.letterOfAward'),
            ModulePermission::MODULE_ID_RFV_CATEGORY                  => trans('modulePermissions.rfvCategories'),
            ModulePermission::MODULE_ID_TOP_MANAGEMENT_VERIFIERS      => trans('modulePermissions.topManagementVerifiers'),
            ModulePermission::MODULE_ID_CONSULTANT_PAYMENT            => trans('modulePermissions.consultantPayments'),
            ModulePermission::MODULE_ID_PROJECT_REPORT_DASHBOARD      => trans('modulePermissions.projectReportDashboard'),
            ModulePermission::MODULE_ID_PROJECT_REPORT_TEMPLATE       => trans('modulePermissions.projectReportTemplate'),
            ModulePermission::MODULE_ID_PROJECT_REPORT_CHART_TEMPLATE => trans('modulePermissions.projectReportChartTemplate'),
            ModulePermission::MODULE_ID_PROJECT_REPORT_CHARTS         => trans('modulePermissions.projectReportCharts'),
            ModulePermission::MODULE_ID_SITE_DIARY_MAINTENANCE        => trans('modulePermissions.siteDiaryMaintenance'),
            ModulePermission::MODULE_ID_ORDERS                        => trans('modulePermissions.orders'),
        );

        if( \PCK\SystemModules\SystemModuleConfiguration::isEnabled(\PCK\SystemModules\SystemModuleConfiguration::MODULE_ID_INPSECTION) )
        {
            $modules[ModulePermission::MODULE_ID_INSPECTION_TEMPLATE] = trans('modulePermissions.inspectionTemplate');
            $modules[ModulePermission::MODULE_ID_INSPECTION]          = trans('modulePermissions.inspection');
        }

        return View::make('modulePermissions.delegate', array(
            'modules' => $modules,
        ));
    }

    public function getAssignableUsers()
    {
        return Response::json($this->repository->getAssignableUsers(Input::all()));
    }

    public function getAssignedUsers()
    {
        return Response::json($this->repository->getAssignedUsers(Input::all()));
    }

    public function toggleEditorStatus($userId, $moduleId)
    {
        $user = \PCK\Users\User::find($userId);

        $success = ModulePermission::setAsEditor($user, $moduleId, ! ModulePermission::isEditor($user, $moduleId));

        return Response::json(array(
            'success' => $success,
        ));
    }

    public function assign()
    {
        $success = $this->repository->assign(Input::get('users') ?? array(), Input::get('module_id'));

        return Response::json(array(
            'success' => $success,
        ));
    }

    public function revoke($userId, $moduleId)
    {
        $canDelete = true;
        $errors    = null;

        switch($moduleId)
        {
            case ModulePermission::MODULE_ID_TOP_MANAGEMENT_VERIFIERS:
                list($canDelete, $errors) = $this->repository->checkForPendingTopManagementVerifierTasks($userId);
                break;
        }

        if( ! $canDelete )
        {
            return Response::json(array(
                'success' => false,
                'errors'  => $errors,
            ));
        }

        $success = $this->repository->revoke($userId, $moduleId);

        return Response::json(array(
            'success' => $success,
            'errors'  => $errors,
        ));
    }
}

