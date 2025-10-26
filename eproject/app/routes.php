<?php

use PCK\Projects\Project;
use PCK\ContractGroups\Types\Role;
use PCK\RequestForVariation\RequestForVariation;
use PCK\ConsultantManagement\ConsultantManagementContract;
use PCK\ConsultantManagement\ConsultantManagementSubsidiary;
use PCK\ConsultantManagement\ConsultantManagementVendorCategoryRfp;
use PCK\ConsultantManagement\ConsultantManagementCallingRfp;
use PCK\ExternalApplication\Client;
use PCK\ExternalApplication\ClientModule;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/*
|--------------------------------------------------------------------------
| CSRF Protection
|--------------------------------------------------------------------------
|
*/

 
Route::when('*', 'csrf', array( 'post', 'put', 'patch', 'delete' ));
Route::get('maintenance-login', array('before' => 'authenticated', 'as' => 'maintenance.login', 'uses' => 'SamlExtController@maintenanceLogin'));
Route::group(['before' => 'maintenance' ], function()
{
    Route::model('projectId', Project::class, function(){
        $id = Route::input('projectId');
        $project = Project::withTrashed()->find((int)$id);
        if($project)
        {
            $content = "Oops... Project has been deleted!\n\nRef : ".$project->reference."\nTitle : " .$project->title."\nDeleted At : ".$project->getAppTimeZoneTime($project->deleted_at);
        }
        else
        {
            $content = 'Oops... Project is invalid!';
        }

        throw new PCK\Exceptions\InvalidRecordException($content);
    });
    Route::model('rfvId', RequestForVariation::class);
    Route::model('consultantManagementContractId', ConsultantManagementContract::class);
    Route::model('consultantManagementSubsidiaryId', ConsultantManagementSubsidiary::class);
    Route::model('vendorCategoryRfpId', ConsultantManagementVendorCategoryRfp::class);
    Route::model('consultantManagementRfpId', ConsultantManagementCallingRfp::class);
    Route::model('extAppClientId', Client::class);
    Route::model('extAppClientModuleId', ClientModule::class);

    Route::cache(__FILE__, function()
    {
        Route::group(array( 'before' => 'guest|superAdminAccessLevel', 'prefix' => 'license' ), function()
        {
            Route::get('/', array( 'as' => 'license.index', 'uses' => 'LicenseController@index' ));
            Route::post('/store', array( 'as' => 'license.store', 'uses' => 'LicenseController@store' ));
        });

        Route::get('time', array( 'uses' => 'ProjectsController@manualUpdateCallingTenderStatus' ));

        Route::get('no-script', array( 'as' => 'noScript', 'uses' => 'BaseController@noScript' ));//no javascript route
        /*
        |--------------------------------------------------------------------------
        | Confide User Routes
        |--------------------------------------------------------------------------
        |
        */

        Route::get('forgot_password', array( 'as' => 'users.forgotPassword', 'uses' => 'AuthController@forgotPassword' ));
        Route::post('forgot_password', array( 'uses' => 'AuthController@doForgotPassword' ));

        Route::get('reset_password/{token}', array( 'as' => 'users.resetPassword', 'uses' => 'AuthController@resetPassword' ));
        Route::post('reset_password/{token}', array( 'uses' => 'AuthController@doResetPassword' ));

        Route::get('confirm/{code}', array( 'as' => 'users.confirm', 'uses' => 'AuthController@confirm' ));


        /*
        |--------------------------------------------------------------------------
        | Access from BuildSpace Routes
        |--------------------------------------------------------------------------
        |
        */

        Route::post('project_push_to_tendering/{projectId}', array( 'as' => 'buildspace.api.to_tendering', 'uses' => 'BuildSpaceAccessController@projectPushToTenderingState' ));

        Route::post('project_addendum/{projectId}', array( 'as' => 'buildspace.api.addendum', 'uses' => 'BuildSpaceAccessController@projectAddendum' ));

        Route::post('project_get_default_tendering_stage_users/{projectId}', array( 'as' => 'buildspace.api.get_default_tendering_stage_users', 'uses' => 'BuildSpaceAccessController@getDefaultTenderingStageUsers' ));

        Route::post('project_get_default_post_contract_stage_users/{projectId}', array( 'as' => 'buildspace.api.get_default_post_contract_stage_users', 'uses' => 'BuildSpaceAccessController@getDefaultPostContractStageUsers' ));

        Route::post('contract-management/verifiers/get/project/{projectId}/module/{moduleIdentifier}', array( 'as' => 'buildspace.api.get_contract_management_verifiers', 'uses' => 'BuildSpaceAccessController@getContractManagementVerifiers' ));

        Route::post('buildspace/notifications/{projectId}/contract-management/{moduleIdentifier}/claim/{claimId}/review/send', array( 'as' => 'buildspace.api.notifications.contractManagement.claim.review', 'uses' => 'BuildSpaceAccessController@sendContractManagementClaimReviewNotifications' ));

        Route::post('buildspace/notifications/{projectId}/contract-management/{moduleIdentifier}/review/send', array( 'as' => 'buildspace.api.notifications.contractManagement.review', 'uses' => 'BuildSpaceAccessController@sendContractManagementReviewNotifications' ));

        Route::post('buildspace/pushToPostContract/{projectId}', array( 'as' => 'buildspace.api.project.stage.push.postContract', 'uses' => 'BuildSpaceAccessController@pushToPostContract' ));

        Route::post('buildspace/canAccessBqEditor/project/{projectId}/user/{userId}', array( 'as' => 'buildspace.api.project.bq.editor.access', 'uses' => 'BuildSpaceAccessController@canAccessBqEditor' ));

        Route::post('buildspace/tenderer-rates/submit/project/{projectId}/user/{userId}', array( 'as' => 'buildspace.api.tendererRates.submit', 'uses' => 'BuildSpaceAccessController@submitTendererRate' ));

        Route::post('buildspace/canAccessMasterCostData/masterCostData/{masterCostDataId}/user/{userId}', array( 'as' => 'buildspace.api.costData.master.access', 'uses' => 'BuildSpaceAccessController@canAccessMasterCostData' ));

        Route::post('buildspace/canAccessCostData/costData/{costDataId}/user/{userId}', array( 'as' => 'buildspace.api.costData.access', 'uses' => 'BuildSpaceAccessController@canAccessCostData' ));

        Route::post('buildspace/getFullSubsidiaryName/{subsidiaryId}', array( 'as' => 'buildspace.api.subsidiary.getFullName', 'uses' => 'BuildSpaceAccessController@getFullSubsidiaryName' ));

        Route::post('buildspace/checkLicenseValidity', array( 'as' => 'buildspace.api.license.validity.check', 'uses' => 'BuildSpaceAccessController@checkLicenseValidity' ));

        Route::post('buildspace/getRoundedAmount', array( 'as' => 'buildspace.api.rounded.amount.get', 'uses' => 'BuildSpaceAccessController@getRoundedAmount' ));

        Route::post('buildspace/getProportionsGroupedByIds', array( 'as' => 'buildspace.api.proportion.groupedby.id.get', 'uses' => 'BuildSpaceAccessController@getProportionsGroupedByIds'));

        Route::post('buildspace/getSubsidiaryHierarchicalCollection', [ 'as' => 'buildspace.api.subsidiary.hierarchical.collection.get', 'uses' => 'BuildSpaceAccessController@getSubsidiaryHierarchicalCollection' ]);

        Route::post('buildspace/notifications/new-claim-revision-initiated', [ 'as' => 'buildspace.api.notifications.claimRevision.new', 'uses' => 'BuildSpaceAccessController@sendNewClaimRevisionInitiatedNotifications' ]);

        Route::post('buildspace/notifications/claim-submitted', [ 'as' => 'buildspace.api.notifications.claimSubmitted', 'uses' => 'BuildSpaceAccessController@sendContractorClaimSubmittedNotifications' ]);

        Route::post('buildspace/notifications/claim-approved', [ 'as' => 'buildspace.api.notifications.claimApproved', 'uses' => 'BuildSpaceAccessController@sendClaimApprovedNotifications' ]);

        Route::post('buildspace/getPostContractClaimTopManagementVerifiers', [ 'as' => 'buildspace.api.postContractClaim.topManagement.verifiers.get', 'uses' => 'BuildSpaceAccessController@getPostContractClaimTopManagementVerifiers' ]);

        /*
        |--------------------------------------------------------------------------
        | User Dashboard Routes
        |--------------------------------------------------------------------------
        |
        */
        Route::group(array( 'before' => 'guest' ), function()
        {
            Route::get('password-update', array( 'as' => 'passwordUpdateForm', 'uses' => 'AuthController@passwordUpdateForm' ));
            Route::put('password-update', array( 'as' => 'passwordUpdate', 'uses' => 'AuthController@passwordUpdate' ));
        });

        Route::group(array( 'before' => 'guest|appLicenseValid|passwordUpdated|temporaryLogin' ), function()
        {
            Route::post('calculate_dates_api/{projectId}', array( 'as' => 'dates.calculateDates', 'uses' => 'CalculateDatesController@calculates' ));

            Route::get('my_profile', array( 'as' => 'user.updateMyProfile', 'uses' => 'AuthController@showMyProfile' ));
            Route::put('my_profile', array( 'uses' => 'AuthController@updateMyProfile' ));

            Route::get('settings', array( 'as' => 'user.settings.edit', 'uses' => 'AuthController@editSettings' ));
            Route::put('settings', array( 'as' => 'user.settings.update', 'uses' => 'AuthController@updateSettings' ));

            require( 'module_routes/scheduled_maintenance_routes.php' );

            require( 'module_routes/open_tender_banners_routes.php' );

            require( 'module_routes/open_tender_news_routes.php' );

            require( 'module_routes/maintenance_routes.php' );

            require( 'module_routes/defects_route.php' );

            require( 'module_routes/letter_of_award_template_routes.php' );

            require( 'module_routes/master_inspection_routes.php' );

            require( 'module_routes/finance_routes.php' );

            require( 'module_routes/finance_contractor_routes.php' );

            require( 'module_routes/finance_account_code_settings_routes.php' );

            require( 'module_routes/projects_overview_routes.php' );

            require( 'module_routes/cost_data_routes.php' );

            require( 'module_routes/weather_routes.php' );

            require( 'module_routes/rejected_material_routes.php' );
            require( 'module_routes/machinery_routes.php' );
            require( 'module_routes/labour_routes.php' );

            require( 'module_routes/all_users_routes.php' );

            require( 'module_routes/request_for_variation_category_routes.php' );

            require( 'module_routes/vendor_form_routes.php' );

            require( 'module_routes/vendor_form_designer_routes.php' );

            require( 'module_routes/vendor_management_module_parameter_routes.php' );

            require( 'module_routes/ds_module_parameter_routes.php' );

            require( 'module_routes/email_notification_settings_routes.php' );

            require( 'module_routes/payment_settings_routes.php' );

            require( 'module_routes/building_information_modelling_routes.php' );

            require( 'module_routes/cidb_grades_routes.php' );

            require( 'module_routes/cidb_codes_routes.php' );

            require( 'module_routes/email_settings_routes.php' );

            require( 'module_routes/consultant_management_routes.php' );

            require( 'module_routes/top_management_verifiers_routes.php' );

            require( 'module_routes/project_report_dashboard_routes.php' );

            require( 'module_routes/project_report_chart_routes.php' );

            require( 'module_routes/e_bidding_session_routes.php' );

            Route::group(array( 'prefix' => 'notifications' ), function()
            {
                Route::get('/', array( 'as' => 'notifications.index', 'uses' => 'NotificationsController@index' ));
            });

            Route::get('contractors/show/{id}', array( 'as' => 'contractors.show', 'uses' => 'ContractorsController@show' ));

            Route::group(array( 'before' => 'superAdminAccessLevel|users.companySwitch.switchableCompany' ), function()
            {
                Route::get('{userId}/switch', array( 'as' => 'users.company.switch', 'uses' => 'UsersController@switchCompany' ));
                Route::put('{userId}/switch', array( 'before' => 'users.companySwitch.transferable', 'as' => 'users.company.switch.update', 'uses' => 'UsersController@switchCompanyUpdate' ));
            });

            Route::group(array( 'before' => 'superAdminCompanyAdminAccessLevel' ), function()
            {
                Route::get('user/{userId}/getTenderingPendingTasks', ['as' => 'user.tendering.pending.tasks.get', 'uses' => 'UsersController@getPendingTenderingTasks']);
                Route::get('user/{userId}/getPostContractPendingTasks', ['as' => 'user.postContract.pending.tasks.get', 'uses' => 'UsersController@getPendingPostContractTasks']);
                Route::get('user/{userId}/getSiteModulePendingTasks', ['as' => 'user.site.module.pending.tasks.get', 'uses' => 'UsersController@getPendingSiteModuleTasks']);
                Route::get('user/{userId}/getLetterOfAwardUserPermissions', ['as' => 'user.letterOfAward.user.permissions.get', 'uses' => 'UsersController@getAssignedLetterOfAwardPermissions']);
                Route::get('user/{userId}/getRequestForVariationUserPermissions', ['as' => 'user.requestForVariation.user.permissions.get', 'uses' => 'UsersController@getAssignedRequestForVariationPermissions']);
                Route::get('user/{userId}/getContractManagementUserPermissions', ['as' => 'user.contractManagement.user.permission.get', 'uses' => 'UsersController@getAssignedContractManagementPermissions']);
                Route::get('user/{userId}/getSiteManagementUserPermissions', ['as' => 'user.siteManagement.user.permission.get', 'uses' => 'UsersController@getAssignedSiteManagementPermissions']);
                Route::get('user/{userId}/getRequestForInspectionUserPermissions', ['as' => 'user.request.for.inspection.user.permission.get', 'uses' => 'UsersController@getAssignedRequestForInspectionPermissions']);
                Route::get('user/{userId}/get-vendor-performance-evaluation-approvals', ['as' => 'user.getVendorPerformanceEvaluationApprovals', 'uses' => 'UsersController@getVendorPerformanceEvaluationApprovals']);

                Route::get('user/{userId}/project/{projectId}/getTenderingPendingTasks', ['as' => 'user.project.tendering.pending.tasks.get', 'uses' => 'UsersController@getPendingTenderingTasks']);
                Route::get('user/{userId}/project/{projectId}/getPostContractPendingTasks', ['as' => 'user.project.postContract.pending.tasks.get', 'uses' => 'UsersController@getPendingPostContractTasks']);
                Route::get('user/{userId}/project/{projectId}/getSiteModulePendingTasks', ['as' => 'user.project.site.module.pending.tasks.get', 'uses' => 'UsersController@getPendingSiteModuleTasks']);
                Route::get('user/{userId}/project/{projectId}/getLetterOfAwardUserPermissions', ['as' => 'user.project.letterOfAward.user.permissions.get', 'uses' => 'UsersController@getAssignedLetterOfAwardPermissions']);
                Route::get('user/{userId}/project/{projectId}/getRequestForVariationUserPermissions', ['as' => 'user.project.requestForVariation.user.permissions.get', 'uses' => 'UsersController@getAssignedRequestForVariationPermissions']);
                Route::get('user/{userId}/project/{projectId}/getContractManagementUserPermissions', ['as' => 'user.project.contractManagement.user.permission.get', 'uses' => 'UsersController@getAssignedContractManagementPermissions']);
                Route::get('user/{userId}/project/{projectId}/getSiteManagementUserPermissions', ['as' => 'user.project.siteManagement.user.permission.get', 'uses' => 'UsersController@getAssignedSiteManagementPermissions']);
                Route::get('user/{userId}/project/{projectId}/getRequestForInspectionUserPermissions', ['as' => 'user.project.request.for.inspection.user.permission.get', 'uses' => 'UsersController@getAssignedRequestForInspectionPermissions']);
                Route::get('user/{userId}/project/{projectId}/get-vendor-performance-evaluation-approvals', ['as' => 'user.project.getVendorPerformanceEvaluationApprovals', 'uses' => 'UsersController@getVendorPerformanceEvaluationApprovals']);
            });

            Route::group(array( 'prefix' => 'home' ), function()
            {
                Route::get('/', array( 'as' => 'home.index', 'uses' => 'HomeController@index' ));
                Route::get('/myToDoListAjax', array( 'as' => 'home.my.todo.list.ajax', 'uses' => 'HomeController@getMyToDoListAjax' ));

                require( 'module_routes/my_processes_routes.php' );
            });

            Route::group(array( 'prefix' => 'dashboard' ), function()
            {
                Route::get('overview', array( 'as' => 'dashboard.overview', 'uses' => 'DashboardController@overview' ));
                Route::post('overview', array( 'as' => 'dashboard.overview', 'uses' => 'DashboardController@overview' ));

                Route::get('subsidiaries', array( 'as' => 'dashboard.subsidiaries', 'uses' => 'DashboardController@subsidiaries' ));
                Route::post('subsidiaries', array( 'as' => 'dashboard.subsidiaries', 'uses' => 'DashboardController@subsidiaries' ));

                Route::get('status-summary', array( 'as' => 'dashboard.status.summary', 'uses' => 'DashboardController@statusSummary' ));
                Route::post('status-summary', array( 'as' => 'dashboard.status.summary', 'uses' => 'DashboardController@statusSummary' ));

                Route::get('ebidding', array( 'as' => 'dashboard.ebidding', 'uses' => 'DashboardController@ebidding'));
                Route::get('ebidding/stats', array( 'as' => 'dashboard.ebidding.stats', 'uses' => 'DashboardController@eBiddingStats'));
                Route::get('ebidding/subsidiaries', array( 'as' => 'dashboard.ebidding.subsidiaries', 'uses' => 'DashboardController@getEBiddingSubsidiaries'));

                Route::get('subsidiaries-ajax/{id}/{fromMonth}/{fromYear}/{toMonth}/{toYear}', array( 'as' => 'dashboard.subsidiaries.ajax', 'uses' => 'DashboardController@getSubsidiariesAjax' ));
                Route::get('subsidiaries-b-ajax/{id}/{fromMonth}/{fromYear}/{toMonth}/{toYear}', array( 'as' => 'dashboard.subsidiaries.B.ajax', 'uses' => 'DashboardController@getSubsidiariesDashboardBAjax' ));
                Route::get('subsidiaries-c-ajax/{id}/{fromMonth}/{fromYear}/{toMonth}/{toYear}', array( 'as' => 'dashboard.subsidiaries.C.ajax', 'uses' => 'DashboardController@getSubsidiariesDashboardCAjax' ));
                Route::get('subsidiaries-d-ajax/{id}/{fromMonth}/{fromYear}/{toMonth}/{toYear}', array( 'as' => 'dashboard.subsidiaries.D.ajax', 'uses' => 'DashboardController@getSubsidiariesDashboardDAjax' ));
                Route::get('subsidiaries-e-ajax/{id}/{year}/{fromMonth}/{fromYear}/{toMonth}/{toYear}', array( 'as' => 'dashboard.subsidiaries.E.ajax', 'uses' => 'DashboardController@getSubsidiariesDashboardEAjax' ));
                Route::get('subsidiaries-e-years-ajax/{id}/{fromMonth}/{fromYear}/{toMonth}/{toYear}', array( 'as' => 'dashboard.subsidiaries.E.years.ajax', 'uses' => 'DashboardController@getSubsidiariesDashboardEYearsAjax' ));

                Route::get('main-contracts-ajax/{id}/{fromMonth}/{fromYear}/{toMonth}/{toYear}', array( 'as' => 'dashboard.main.contracts.ajax', 'uses' => 'DashboardController@getMainContractsAjax' ));

                Route::get('procurement-method-ajax/{id}/{fromMonth}/{fromYear}/{toMonth}/{toYear}', array( 'as' => 'dashboard.procurement.method.ajax', 'uses' => 'DashboardController@getProcurementMethodAjax' ));
                Route::get('project-status-ajax/{id}/{fromMonth}/{fromYear}/{toMonth}/{toYear}', array( 'as' => 'dashboard.project.status.ajax', 'uses' => 'DashboardController@getProjectStatusAjax' ));

                Route::get('e-tender-waiver-ajax/{id}/{fromMonth}/{fromYear}/{toMonth}/{toYear}', array( 'as' => 'dashboard.e.tender.waiver.status.ajax', 'uses' => 'DashboardController@getETenderWaiverStatusAjax' ));
                Route::get('e-tender-waiver-other-ajax/{id}/{countryId}/{fromMonth}/{fromYear}/{toMonth}/{toYear}', array( 'as' => 'dashboard.e.tender.waiver.status.other.ajax', 'uses' => 'DashboardController@getETenderWaiverStatusOtherAjax' ));

                Route::get('e-auction-waiver-ajax/{id}/{fromMonth}/{fromYear}/{toMonth}/{toYear}', array( 'as' => 'dashboard.e.auction.waiver.status.ajax', 'uses' => 'DashboardController@getEAuctionWaiverStatusAjax' ));
                Route::get('e-auction-waiver-other-ajax/{id}/{countryId}/{fromMonth}/{fromYear}/{toMonth}/{toYear}', array( 'as' => 'dashboard.e.auction.waiver.status.other.ajax', 'uses' => 'DashboardController@getEAuctionWaiverStatusOtherAjax' ));

                Route::get('overall-certified-payment-ajax/{countryId}/{year}/{fromMonth}/{fromYear}/{toMonth}/{toYear}', array( 'as' => 'dashboard.overall.certified.payment.ajax', 'uses' => 'DashboardController@getOverallCertifiedPaymentAjax' ));

            });

            Route::group(['prefix' => 'contractor-questionnaires'], function()
            {
                Route::get('', ['as' => 'contractor.questionnaires.index', 'uses' => 'ProjectQuestionnairesController@contractorQuestionnaireIndex']);
                Route::get('projects', ['as' => 'contractor.questionnaires.projects.ajax.list', 'uses' => 'ProjectQuestionnairesController@contractorQuestionnaireProjectList']);
                Route::get('{pId}/show', ['as' => 'contractor.questionnaires.show', 'uses' => 'ProjectQuestionnairesController@contractorQuestionnaireShow']);//need to set to pId instead projectId so we wont get project object because this menu in navigation bar is outside project's menu

                Route::post('notify', ['as' => 'contractor.questionnaires.notify', 'uses' => 'ProjectQuestionnairesController@contractorQuestionnaireNotify']);
                Route::post('reply', ['as' => 'contractor.questionnaires.reply', 'uses' => 'ProjectQuestionnairesController@contractorQuestionnaireReply']);
                Route::post('reply-upload-attachments', ['as' => 'contractor.questionnaires.upload.attachments', 'uses' => 'ProjectQuestionnairesController@contractorQuestionnaireAttachmentUpload']);
                Route::get('{id}/attachment-list', ['as' => 'contractor.questionnaires.attachments.list', 'uses' => 'ProjectQuestionnairesController@contractorQuestionnaireAttachmentList']);
                Route::get('{id}/attachment-download', ['as' => 'contractor.questionnaires.attachments.download', 'uses' => 'ProjectQuestionnairesController@contractorQuestionnaireAttachmentDownload']);
                Route::delete('{id}/attachment-delete', ['as' => 'contractor.questionnaires.attachments.delete', 'uses' => 'ProjectQuestionnairesController@contractorQuestionnaireAttachmentDelete']);
            });

            Route::group(array( 'prefix' => 'projects' ), function()
            {
                Route::get('/', array( 'as' => 'projects.index', 'uses' => 'ProjectsController@index' ));
                Route::get('ajax-list', array( 'as' => 'projects.ajax.list', 'uses' => 'ProjectsController@ajaxList' ));

                Route::get('projectScheduleCostTimeData/{id}', array( 'as' => 'projects.projectScheduleCostTimeData', 'uses' => 'ProjectsController@ajaxGetProjectScheduleCostTimeData' ));

                Route::group(array( 'before' => 'superAdminAccessLevel' ), function()
                {
                    Route::delete('{projectId}/delete', array( 'as' => 'projects.delete', 'uses' => 'ProjectsController@destroy' ));
                });

                // Reference (Contract Number) auto-generate processes.
                Route::post('generateContractNumber', array( 'as' => 'projects.generateContractNumber', 'uses' => 'ProjectsController@generateContractNumber' ));
                Route::post('generateRunningNumber', array( 'as' => 'projects.generateRunningNumber', 'uses' => 'ProjectsController@generateRunningNumber' ));
                Route::post('checkContractNumberAvailability', array( 'as' => 'projects.contractNumber.check', 'uses' => 'ProjectsController@checkContractNumberAvailability' ));
                Route::post('checkRunningNumberAvailability', array( 'as' => 'projects.runningNumber.check', 'uses' => 'ProjectsController@checkRunningNumberAvailability' ));

                // only business owner's admin can add new project
                Route::group(array( 'before' => 'roles:' . Role::PROJECT_OWNER . '|companyAdminAccessLevel' ), function()
                {
                    Route::get('create', array( 'as' => 'projects.create', 'uses' => 'ProjectsController@create' ));
                    Route::post('create', array( 'uses' => 'ProjectsController@store' ));
                });

                Route::group(array( 'prefix' => '{projectId}/submit_tenders'), function()
                {
                    Route::get('/', array( 'as' => 'projects.submitTender', 'uses' => 'ProjectTendererTendersController@index' ));
                });

                Route::group(array( 'prefix' => '{projectId}'), function()
                {
                    Route::post('/update-project-progress-checklist', array( 'as' => 'projects.progress-checklist', 'uses' => 'ProjectsController@updateProjectProgressChecklist'));

                    Route::get('/', array( 'as' => 'projects.show', 'uses' => 'ProjectsController@show' ));

                    Route::group(array( 'before' => 'projectRoles:' . Role::PROJECT_OWNER . ',' . Role::GROUP_CONTRACT . '|companyAdminAccessLevel' ), function()
                    {
                        Route::get('assign_companies', array( 'as' => 'projects.company.assignment', 'uses' => 'ProjectCompaniesController@create' ));

                        Route::group(array('before' => 'project.currentTenderStatus.closedTender'), function()
                        {
                            Route::post('toggleContractorAccess', array( 'as' => 'projects.contractorAccess.toggle', 'uses' => 'ProjectsController@toggleContractorAccess' ));

                            Route::post('toggleContractorContractualClaimAccess', array( 'as' => 'projects.contractorAccess.contractualClaim.toggle', 'uses' => 'ProjectsController@toggleContractorContractualClaimAccess' ));
                        });
                    });

                    Route::group(array( 'prefix' => 'sub-packages' ), function()
                    {
                        Route::get('/', array( 'as' => 'projects.subPackages.index', 'uses' => 'ProjectsController@subPackagesIndex' ));
                        Route::get('/list', array( 'as' => 'projects.subPackages.list.ajax', 'uses' => 'ProjectsController@getSubPackagesList' ));

                        Route::group(array( 'before' => 'roles:' . Role::PROJECT_OWNER . '|companyAdminAccessLevel|canAddSubProject' ), function()
                        {
                            Route::get('create', array( 'as' => 'projects.subPackages.create', 'uses' => 'ProjectsController@subPackagesCreate' ));
                            Route::post('create', array( 'as' => 'projects.subPackages.store', 'uses' => 'ProjectsController@subPackagesStore' ));
                        });
                    });

                    // Site Management Module Routes

                    Route::group(array( 'prefix' => 'site-management' ), function()
                    {
                        Route::group(array( 'prefix' => 'permissions', 'before' => 'siteManagement.hasSiteManagementUserManagementPermission' ), function()
                        {
                            Route::get('/', array( 'as' => 'site-management.permissions.index', 'uses' => 'SiteManagementUserPermissionsController@index' ));
                            Route::get('defect-assigned/get', array( 'as' => 'site-management.permissions.defectAssignedUsers', 'uses' => 'SiteManagementUserPermissionsController@getDefectAssignedUsers' ));
                            Route::get('daily-labour-report-assigned/get', array( 'as' => 'site-management.permissions.dailyLabourReportAssignedUsers', 'uses' => 'SiteManagementUserPermissionsController@getDailyLabourReportsAssignedUsers' ));
                            Route::get('update-site-progress-assigned/get', array( 'as' => 'site-management.permissions.updateSiteProgressAssignedUsers', 'uses' => 'SiteManagementUserPermissionsController@getUpdateSiteProgressAssignedUsers' ));
                            Route::get('site-diary-assigned/get', array( 'as' => 'site-management.permissions.siteDiaryAssignedUsers', 'uses' => 'SiteManagementUserPermissionsController@getSiteDiaryAssignedUsers' ));
                            Route::get('instruction-to-contractor-assigned/get', array( 'as' => 'site-management.permissions.instructionToContractorAssignedUsers', 'uses' => 'SiteManagementUserPermissionsController@getInstructionToContractorAssignedUsers' ));
                            Route::get('daily-report-assigned/get', array( 'as' => 'site-management.permissions.dailyReportAssignedUsers', 'uses' => 'SiteManagementUserPermissionsController@getDailyReportAssignedUsers' ));
                            Route::get('e-bidding-assigned/get', array( 'as' => 'site-management.permissions.eBiddingAssignedUsers', 'uses' => 'SiteManagementUserPermissionsController@getEBiddingAssignedUsers' ));
                            
                            Route::get('assignable/get', array( 'as' => 'site-management.permissions.assignable', 'uses' => 'SiteManagementUserPermissionsController@getAssignableUsers' ));
                            Route::post('assign', array( 'as' => 'site-management.permissions.assign', 'uses' => 'SiteManagementUserPermissionsController@assign' ));
                            Route::delete('user/{userId}/module/{moduleId}', array( 'as' => 'site-management.permissions.revoke', 'uses' => 'SiteManagementUserPermissionsController@revoke' ));
                            Route::post('site/toggle/user/{userId}/module/{moduleId}', array( 'as' => 'site-management.permissions.site.toggle', 'uses' => 'SiteManagementUserPermissionsController@toggleSiteStatus' ));
                            Route::post('client/toggle/user/{userId}/module/{moduleId}', array( 'as' => 'site-management.permissions.client.toggle', 'uses' => 'SiteManagementUserPermissionsController@toggleClientStatus' ));
                            Route::post('pm/toggle/user/{userId}/module/{moduleId}', array( 'as' => 'site-management.permissions.pm.toggle', 'uses' => 'SiteManagementUserPermissionsController@togglePmStatus' ));
                            Route::post('qs/toggle/user/{userId}/module/{moduleId}', array( 'as' => 'site-management.permissions.qs.toggle', 'uses' => 'SiteManagementUserPermissionsController@toggleQsStatus' ));
                            Route::post('editor/toggle/user/{userId}/module/{moduleId}', array( 'as' => 'site-management.permissions.editor.toggle', 'uses' => 'SiteManagementUserPermissionsController@toggleEditorStatus' ));
                            Route::post('rateEditor/toggle/user/{userId}/module/{moduleId}', array( 'as' => 'site-management.permissions.rateEditor.toggle', 'uses' => 'SiteManagementUserPermissionsController@toggleRateEditorStatus' ));
                            Route::post('viewer/toggle/user/{userId}/module/{moduleId}', array( 'as' => 'site-management.permissions.viewer.toggle', 'uses' => 'SiteManagementUserPermissionsController@toggleViewerStatus' ));
                            Route::post('verifier/toggle/user/{userId}/module/{moduleId}', array( 'as' => 'site-management.permissions.verifier.toggle', 'uses' => 'SiteManagementUserPermissionsController@toggleVerifierStatus' ));
                            Route::post('submitter/toggle/user/{userId}/module/{moduleId}', array( 'as' => 'site-management.permissions.submitter.toggle', 'uses' => 'SiteManagementUserPermissionsController@toggleSubmitterStatus' ));

                            Route::post('viewer/checbox/toggle/user/{userId}/module/{moduleId}', array( 'as' => 'site-management.permissions.viewer.checkbox.toggle', 'uses' => 'SiteManagementUserPermissionsController@toggleViewerCheckboxStatus' ));
                            Route::post('verifier/checbox/toggle/user/{userId}/module/{moduleId}', array( 'as' => 'site-management.permissions.verifier.checkbox.toggle', 'uses' => 'SiteManagementUserPermissionsController@toggleVerifierCheckboxStatus' ));
                            Route::post('submitter/checbox/toggle/user/{userId}/module/{moduleId}', array( 'as' => 'site-management.permissions.submitter.checkbox.toggle', 'uses' => 'SiteManagementUserPermissionsController@toggleSubmitterCheckboxStatus' ));

                        });

                        // Site Management Defect Module Routes

                        Route::group(array( 'prefix' => 'site-management-defect', 'before' => 'siteManagement.hasDefectPermission' ),
                            function()
                            {
                                Route::get('/', array( 'as' => 'site-management-defect.index', 'uses' => 'SiteManagementDefectController@index' ));
                                Route::get('create', array( 'as' => 'site-management-defect.create', 'uses' => 'SiteManagementDefectController@create' ));
                                Route::post('create', array( 'as' => 'site-management-defect.create', 'uses' => 'SiteManagementDefectController@store' ));
                                Route::get('response/{id}', array( 'before' => 'siteManagement.hasViewDefectFormPermission', 'as' => 'site-management-defect.getResponse', 'uses' => 'SiteManagementDefectController@getResponse' ));
                                Route::post('storeResponse/{id}', array( 'as' => 'site-management-defect.storeResponse', 'uses' => 'SiteManagementDefectController@storeResponse' ));
                                Route::get('assignPIC/{id}', array( 'before' => 'siteManagement.hasDefectProjectManagerPermission', 'as' => 'site-management-defect.assignPIC', 'uses' => 'SiteManagementDefectController@assignPIC' ));
                                Route::post('assignPIC/{id}', array( 'as' => 'site-management-defect.assignPIC', 'uses' => 'SiteManagementDefectController@postAssignPIC' ));

                                Route::group(array( 'before' => 'siteManagement.hasViewMCARFormPermission' ), function()
                                {
                                    Route::group(array( 'before' => 'siteManagement.hasCreateMCARFormPermission' ), function()
                                    {
                                        Route::get('createMCAR/{id}', array( 'as' => 'site-management-defect.createMCAR', 'uses' => 'SiteManagementDefectController@createMCAR' ));
                                        Route::post('createMCAR/{id}', array( 'as' => 'site-management-defect.createMCAR', 'uses' => 'SiteManagementDefectController@postCreateMCAR' ));
                                    });
                                    Route::get('replyMCAR/{id}', array( 'as' => 'site-management-defect.replyMCAR', 'uses' => 'SiteManagementDefectController@replyMCAR' ));
                                    Route::post('postReplyMCAR/{id}', array( 'as' => 'site-management-defect.postReplyMCAR', 'uses' => 'SiteManagementDefectController@postReplyMCAR' ));
                                    Route::post('verifyMCAR/{id}', array( 'as' => 'site-management-defect.verifyMCAR', 'uses' => 'SiteManagementDefectController@verifyMCAR' ));
                                    Route::get('printMCAR/{id}', array( 'as' => 'site-management-defect.printMCAR', 'uses' => 'SiteManagementDefectController@printMCAR' ));
                                });
                                Route::post('storeBackcharge/{id}', array( 'as' => 'site-management-defect.storeBackcharge', 'uses' => 'SiteManagementDefectController@storeBackcharge' ));
                                Route::get('showBackcharge/{backchargeId}', array( 'as' => 'site-management-defect.showBackcharge', 'uses' => 'SiteManagementDefectController@showBackcharge' ));
                                Route::delete('delete/{id}', array( 'as' => 'site-management-defect.delete', 'uses' => 'SiteManagementDefectController@destroy' ));
                                Route::post('populateCategory', array( 'as' => 'site-management-defect.populateCategory', 'uses' => 'SiteManagementDefectController@populateCategory' ));
                                Route::post('populateDefect', array( 'as' => 'site-management-defect.populateDefect', 'uses' => 'SiteManagementDefectController@populateDefect' ));
                                Route::post('populateUnit', array( 'as' => 'site-management-defect.populateUnit', 'uses' => 'SiteManagementDefectController@populateUnit' ));
                                Route::post('getLocationByLevel', array( 'as' => 'site-management-defect.getLocationByLevel', 'uses' => 'SiteManagementDefectController@getLocationByLevel' ));
                            });

                        Route::group(array( 'prefix' => 'site-management-site-diary','before' => 'siteManagement.hasSiteDiaryPermission'), function()
                        {
                            Route::get('/', array( 'as' => 'site-management-site-diary.index', 'uses' => 'SiteManagementSiteDiaryController@index' ));
                            Route::get('create', array( 'as' => 'site-management-site-diary.general-form.create', 'uses' => 'SiteManagementSiteDiaryController@create'));
                            Route::post('create', array( 'as' => 'site-management-site-diary.general-form.create', 'uses' => 'SiteManagementSiteDiaryController@store'));
                            Route::get('edit/{id}/{form}', array( 'as' => 'site-management-site-diary.general-form.edit', 'uses' => 'SiteManagementSiteDiaryController@edit'));
                            Route::put('update/{id}', array( 'as' => 'site-management-site-diary.general-form.update', 'uses' => 'SiteManagementSiteDiaryController@update' ));
                            Route::delete('delete/{id}', array( 'as' => 'site-management-site-diary.general-form.delete', 'uses' => 'SiteManagementSiteDiaryController@destroy' ));
                            Route::get('show/{id}', array( 'as' => 'site-management-site-diary.general-form.show', 'uses' => 'SiteManagementSiteDiaryController@show'));
                            Route::post('submitGeneralFormForApproval/{id}', array( 'as' => 'site-management-site-diary.submitGeneralFormForApproval', 'uses' => 'SiteManagementSiteDiaryController@submitGeneralFormForApproval' ));
                            Route::get('getDayFromCalendar', array( 'as' => 'site-management-site-diary.getDayFromCalendar', 'uses' => 'SiteManagementSiteDiaryController@getDayFromCalendar'));

                            Route::group(array( 'prefix' => '{siteDiaryId}/visitor','before' => 'siteManagement.hasSiteDiaryPermission'), function()
                            {
                                Route::get('/', array( 'as' => 'site-management-site-diary-visitor.index', 'uses' => 'SiteManagementSiteDiaryVisitorController@index' ));
                                Route::get('create', array( 'as' => 'site-management-site-diary-visitor.create', 'uses' => 'SiteManagementSiteDiaryVisitorController@create'));
                                Route::post('create', array( 'as' => 'site-management-site-diary-visitor.create', 'uses' => 'SiteManagementSiteDiaryVisitorController@store'));
                                Route::get('edit/{id}', array( 'as' => 'site-management-site-diary-visitor.edit', 'uses' => 'SiteManagementSiteDiaryVisitorController@edit'));
                                Route::put('update/{id}', array( 'as' => 'site-management-site-diary-visitor.update', 'uses' => 'SiteManagementSiteDiaryVisitorController@update' ));
                                Route::delete('delete/{id}', array( 'as' => 'site-management-site-diary-visitor.delete', 'uses' => 'SiteManagementSiteDiaryVisitorController@destroy' ));
                            });

                            Route::group(array( 'prefix' => '{siteDiaryId}/weather','before' => 'siteManagement.hasSiteDiaryPermission'), function()
                            {
                                Route::get('/', array( 'as' => 'site-management-site-diary-weather.index', 'uses' => 'SiteManagementSiteDiaryWeatherController@index' ));
                                Route::get('create', array( 'as' => 'site-management-site-diary-weather.create', 'uses' => 'SiteManagementSiteDiaryWeatherController@create'));
                                Route::post('create', array( 'as' => 'site-management-site-diary-weather.create', 'uses' => 'SiteManagementSiteDiaryWeatherController@store'));
                                Route::get('edit/{id}', array( 'as' => 'site-management-site-diary-weather.edit', 'uses' => 'SiteManagementSiteDiaryWeatherController@edit'));
                                Route::put('update/{id}', array( 'as' => 'site-management-site-diary-weather.update', 'uses' => 'SiteManagementSiteDiaryWeatherController@update' ));
                                Route::delete('delete/{id}', array( 'as' => 'site-management-site-diary-weather.delete', 'uses' => 'SiteManagementSiteDiaryWeatherController@destroy' ));
                            });

                            Route::group(array( 'prefix' => '{siteDiaryId}/rejected_material','before' => 'siteManagement.hasSiteDiaryPermission'), function()
                            {
                                Route::get('/', array( 'as' => 'site-management-site-diary-rejected_material.index', 'uses' => 'SiteManagementSiteDiaryRejectedMaterialController@index' ));
                                Route::get('create', array( 'as' => 'site-management-site-diary-rejected_material.create', 'uses' => 'SiteManagementSiteDiaryRejectedMaterialController@create'));
                                Route::post('create', array( 'as' => 'site-management-site-diary-rejected_material.create', 'uses' => 'SiteManagementSiteDiaryRejectedMaterialController@store'));
                                Route::get('edit/{id}', array( 'as' => 'site-management-site-diary-rejected_material.edit', 'uses' => 'SiteManagementSiteDiaryRejectedMaterialController@edit'));
                                Route::put('update/{id}', array( 'as' => 'site-management-site-diary-rejected_material.update', 'uses' => 'SiteManagementSiteDiaryRejectedMaterialController@update' ));
                                Route::delete('delete/{id}', array( 'as' => 'site-management-site-diary-rejected_material.delete', 'uses' => 'SiteManagementSiteDiaryRejectedMaterialController@destroy' ));
                            });
                        });

                        require( 'module_routes/instructions_to_contractors_routes.php' );
                        require( 'module_routes/daily_report_routes.php' );


                        // Site Management Daily Labour Reports Module Routes

                        Route::group(array( 'prefix' => 'daily-labour-report', 'before' => 'siteManagement.hasDailyLabourReportsPermission' ), function()
                        {
                            Route::get('/', array( 'as' => 'daily-labour-report.index', 'uses' => 'DailyLabourReportsController@index' ));
                            Route::get('create', array( 'as' => 'daily-labour-report.create', 'uses' => 'DailyLabourReportsController@create' ));
                            Route::post('create', array( 'as' => 'daily-labour-report.create', 'uses' => 'DailyLabourReportsController@store' ));
                            Route::get('edit/{id}', array( 'as' => 'daily-labour-report.edit', 'uses' => 'DailyLabourReportsController@edit' ));
                            Route::put('update/{id}', array( 'as' => 'daily-labour-report.update', 'uses' => 'DailyLabourReportsController@update' ));
                            Route::get('show/{id}', array( 'as' => 'daily-labour-report.show', 'uses' => 'DailyLabourReportsController@show' ));
                            Route::post('populateCategory', array( 'as' => 'daily-labour-report.populateCategory', 'uses' => 'DailyLabourReportsController@populateCategory' ));
                            Route::post('populateDefect', array( 'as' => 'daily-labour-report.populateDefect', 'uses' => 'DailyLabourReportsController@populateDefect' ));
                            Route::post('populateUnit', array( 'as' => 'daily-labour-report.populateUnit', 'uses' => 'DailyLabourReportsController@populateUnit' ));
                            Route::post('getLocationByLevel', array( 'as' => 'daily-labour-report.getLocationByLevel', 'uses' => 'DailyLabourReportsController@getLocationByLevel' ));
                            Route::post('populateProjectLabourRate', array( 'as' => 'daily-labour-report.populateProjectLabourRate', 'uses' => 'DailyLabourReportsController@populateProjectLabourRate' ));
                        });

                        Route::post('populateContractor', array( 'as' => 'daily-labour-report.populateContractor', 'uses' => 'DailyLabourReportsController@populateContractor' ));

                        Route::post('populatePostContractProjectLabourRate', array( 'as' => 'daily-labour-report.populatePostContractProjectLabourRate', 'uses' => 'DailyLabourReportsController@populatePostContractProjectLabourRate' ));


                    

                    });

                    require( 'module_routes/contract_management_routes.php' );

                    require( 'module_routes/request_for_variation_routes.php' );

                    require( 'module_routes/letter_of_award_routes.php' );

                    require( 'module_routes/form_of_tender_routes.php' );

                    require( 'module_routes/inspection_routes.php' );
                    
                    Route::group(array( 'before' => 'projectRoles:' . Role::PROJECT_OWNER . ',' . Role::GROUP_CONTRACT . '|companyAdminAccessLevel' ), function()
                    {
                        Route::post('assign_companies', array( 'uses' => 'ProjectCompaniesController@store' ));

                        Route::get('contractGroup/{contractGroupId}/getAssignableCompanies', array( 'as' => 'assignable.companies.get', 'uses' => 'ProjectCompaniesController@getAssignableCompanies' ));

                        Route::get('getSelectedCompaniesUsers', ['as' => 'selected.companies.users.get', 'uses' => 'ProjectCompaniesController@getSelectedCompaniesUsers']);

                        Route::group(array( 'before' => 'canManuallySkipToPostContract' ), function()
                        {
                            Route::get('skipToPostContract/confirmation', array( 'as' => 'projects.skip.postContract.confirmation', 'uses' => 'ProjectsController@skipToPostContractConfirmation' ));

                            Route::post('skipToPostContract', array( 'as' => 'projects.skip.postContract', 'uses' => 'ProjectsController@skipToPostContract' ));
                        });

                        Route::get('post-contract-info-edit', array( 'as' => 'projects.postContract.info.edit', 'uses' => 'ProjectsController@postContractInfoEdit' ));
                        Route::post('post-contract-info-store', array( 'as' => 'projects.postContract.info.store', 'uses' => 'ProjectsController@postContractInfoStore' ));
                    });

                    Route::group(array( 'before' => 'project.isPostContract|contractorClaims.canAccess', 'prefix' => 'submit-claims' ), function()
                    {
                        Route::get('/', array( 'as' => 'projects.contractorClaims', 'uses' => 'SubmitClaimsController@show' ));
                        Route::post('/', array( 'before' => 'contractorClaims.canSubmitClaim|projectRoles:' . Role::CONTRACTOR, 'as' => 'projects.contractorClaims.submit', 'uses' => 'SubmitClaimsController@update' ));
                        Route::get('attachments', array( 'as' => 'projects.contractorClaims.attachments', 'uses' => 'SubmitClaimsController@getAttachmentList' ));
                        Route::post('unlock-submission/{claimRevisionId}', array( 'before' => 'contractorClaims.canUnlockSubmission', 'as' => 'projects.contractorClaims.unlockSubmission', 'uses' => 'SubmitClaimsController@unlockSubmission' ));
                        Route::get('claim-submission/log/{claimRevisionId}', array( 'as' => 'projects.contractorClaims.submission.log', 'uses' => 'SubmitClaimsController@getClaimSubmissionLog' ));
                        Route::get('unlock-submission/log/{claimRevisionId}', array( 'as' => 'projects.contractorClaims.unlockSubmission.log', 'uses' => 'SubmitClaimsController@getUnlockSubmissionLog' ));
                        Route::get('invoice/attachments/list/{claimRevisionId}', array( 'as' => 'projects.contractorClaims.invoice.attachments.list', 'uses' => 'SubmitClaimsController@invoiceAttachmentList' ));
                        Route::post('invoice/attachments/{claimRevisionId}', array( 'before' => 'claimRevision.certApproved|projectRoles:' . Role::CONTRACTOR, 'as' => 'projects.contractorClaims.invoice', 'uses' => 'SubmitClaimsController@invoiceUpload' ));
                    });

                    Route::group(array( 'before' => 'companyAdminAccessLevel' ), function()
                    {
                        Route::get('user/{userId}/viewerRemoveValidate', ['as' => 'projects.viewer.remove.validate', 'uses' => 'UsersController@checkProjectUserIsTransferable']);
                        Route::get('user/{userId}/editorRemoveValidate', ['as' => 'projects.editor.remove.validate', 'uses' => 'UsersController@checkProjectEditorRemovable']);
                        Route::get('assign_users', array( 'as' => 'projects.assignUsers', 'uses' => 'ProjectGroupsController@edit' ));
                        Route::put('assign_users', array( 'uses' => 'ProjectGroupsController@update' ));
                    });

                    Route::group(array( 'before' => 'isEditor|allowBusinessUnitOrGCDToAccess|checkValidStatusForPostContract' ), function()
                    {
                        Route::get('to_post_contract', array( 'as' => 'projects.postContract.create', 'uses' => 'ProjectsController@postContractCreate' ));
                        Route::post('to_post_contract', array( 'uses' => 'ProjectsController@postContractStore' ));
                    });

                    Route::group(array( 'before' => 'isEditor|allowBusinessUnitOrGCDToAccess|checkValidStatusForCompletion' ), function()
                    {
                        Route::get('completion', array( 'as' => 'projects.completion.create', 'uses' => 'ProjectsController@completionCreate' ));
                        Route::post('completion', array( 'uses' => 'ProjectsController@completionStore' ));
                    });

                    Route::group(array( 'prefix' => 'technical-evaluation', 'before' => 'hasTechnicalEvaluation' ), function()
                    {
                        Route::get('tender/{tenderId}/companies/{companyId}/form-responses', array( 'before' => 'companyAdminAccessLevel|companyOwnerChecking|companyConfirmed|project.stage.technicalEvaluation', 'as' => 'technicalEvaluation.formResponses', 'uses' => 'TendererTechnicalEvaluationController@getFormResponses' ));
                        Route::get('tender/{tenderId}/companies/{companyId}/form-responses/log', array( 'as' => 'technicalEvaluation.formResponses.log', 'uses' => 'TendererTechnicalEvaluationController@getFormResponseLog' ));

                        Route::post('companies/{companyId}/form/update', array( 'before' => 'companyAdminAccessLevel|companyOwnerChecking|companyConfirmed|project.stage.technicalEvaluation', 'as' => 'technicalEvaluation.form.update', 'uses' => 'TechnicalEvaluationController@formUpdate' ));

                        Route::group(array( 'prefix' => 'company/{companyId}/attachments' ), function()
                        {
                            Route::group(array( 'before' => 'companyOwnerChecking|project.stage.technicalEvaluation' ), function()
                            {
                                Route::post('upload', array( 'as' => 'technicalEvaluation.attachments.upload', 'uses' => 'TechnicalEvaluationAttachmentsController@upload' ));

                                Route::delete('{attachmentId}/delete', array( 'as' => 'technicalEvaluation.attachments.delete', 'uses' => 'TechnicalEvaluationAttachmentsController@deleteAttachment' ));
                            });

                            Route::get('{attachmentId}/fileDownload', array( 'as' => 'technicalEvaluation.results.fileDownload', 'uses' => 'TechnicalEvaluationAttachmentsController@fileDownload' ));
                        });

                        Route::group(array( 'prefix' => 'results', 'before' => 'technicalEvaluationAccess' ), function()
                        {
                            Route::group(array( 'prefix' => 'company/{companyId}' ), function()
                            {
                                Route::post('form/update/foreign', array( 'as' => 'technicalEvaluation.form.update.foreign', 'before' => 'technicalEvaluation.canUpdateResults', 'uses' => 'TechnicalEvaluationController@formUpdate' ));
                            });

                            Route::get('index', array( 'as' => 'technicalEvaluation.results.index', 'uses' => 'TechnicalEvaluationController@resultsIndex' ));

                            Route::group(array( 'prefix' => 'tenders/{tenderId}' ), function()
                            {
                                Route::get('verifier-logs', array( 'as' => 'technicalEvaluation.results.verifiers.logs', 'uses' => 'TechnicalEvaluationController@viewTechnicalEvaluationVerifierLogs' ));

                                Route::group(array( 'before' => 'isEditor|checkTenderAccessLevelPermission|checkTechnicalEvaluationStatus' ), function()
                                {
                                    Route::get('resend-technical-evaluation-verifier-email/{receiverId}', array( 'as' => 'technicalEvaluation.resendTechnicalEvaluationVerifierEmail', 'uses' => 'TechnicalEvaluationController@resendTechnicalEvaluationVerifierEmail' ));

                                    Route::get('select-verifier-form', array( 'as' => 'technicalEvaluation.results.verifiers.form', 'uses' => 'TechnicalEvaluationController@selectVerifierForm' ));
                                    Route::post('select-verifier-form/assign', array( 'as' => 'technicalEvaluation.results.verifiers.assign', 'uses' => 'TechnicalEvaluationController@assignVerifiers' ));

                                    Route::post('verifiers/reassign', array( 'as' => 'technicalEvaluation.results.verifiers.reassign', 'uses' => 'TechnicalEvaluationController@reassignVerifiers' ));
                                });

                                Route::get('show', array( 'as' => 'technicalEvaluation.results.show', 'uses' => 'TechnicalEvaluationController@resultsShow' ));
                                Route::get('show/tenderers-list', array( 'as' => 'technicalEvaluation.results.show.tenderers', 'uses' => 'TechnicalEvaluationController@resultsShowTenderers' ));
                                Route::get('show/tenderers/{companyId}/attachment-list', array( 'as' => 'technicalEvaluation.results.show.tenderers.attachments', 'uses' => 'TechnicalEvaluationController@resultsShowTendererAttachments' ));
                                Route::get('show/tenderers/{companyId}/attachments/download', array( 'as' => 'technicalEvaluation.results.show.tenderers.attachments.download', 'uses' => 'TechnicalEvaluationController@downloadTendererAttachments' ));
                                Route::get('show/tenderers/form-responses', array( 'as' => 'technicalEvaluation.results.show.tenderers.formResponses', 'uses' => 'TechnicalEvaluationController@getFormResponses' ));
                                Route::get('show/tenderers/{companyId}/form-responses/log', array( 'as' => 'technicalEvaluation.results.show.tenderers.formResponses.log', 'uses' => 'TechnicalEvaluationController@getFormResponseLog' ));

                                Route::group(array( 'before' => 'checkTechnicalEvaluationVerifierStatus' ), function()
                                {
                                    Route::get('summary', array( 'as' => 'technicalEvaluation.results.summary', 'uses' => 'TechnicalEvaluationController@overallSummary' ));
                                    Route::get('summary/download', array( 'as' => 'technicalEvaluation.results.summary.excel.export', 'uses' => 'TechnicalEvaluationController@overallSummaryExcelExport' ));
                                    Route::get('item/{aspectId}/in-depth', array( 'as' => 'technicalEvaluation.results.inDepth', 'uses' => 'TechnicalEvaluationController@inDepthSummary' ));

                                    Route::post('remarks/update', array( 'as' => 'technicalEvaluation.remarks.update', 'uses' => 'TechnicalEvaluationController@updateRemark' ));
                                    Route::post('syncTenderer', array( 'as' => 'technicalEvaluation.tenderer.save', 'uses' => 'TechnicalEvaluationController@syncTenderer' ));
                                    Route::get('assessmentConfirmation', array( 'as' => 'technicalEvaluation.assessment.confirm', 'uses' => 'TechnicalEvaluationController@confirmAssessment' ));
                                    Route::put('submitForApproval', array( 'as' => 'technicalEvaluation.approval.submit', 'uses' => 'TechnicalEvaluationController@submitTechnicalAssessmentForApproval' ));
                                    Route::put('updateApprovalStatus', array( 'as' => 'technicalEvaluation.approval.status.update', 'uses' => 'TechnicalEvaluationController@updateTechnicalAssessmentApprovalStatus' ));
                                    Route::get('sendVerificationReminder', array( 'as' => 'technicalEvaluation.approval.reminder.send', 'uses' => 'TechnicalEvaluationController@sendPendingVerificationEmailReminder' ));
                                });

                                Route::group(['before' => 'technicalAssessmentApprovalCheck'], function() {
                                    Route::get('technicalAssessmentExport', ['as' => 'technical.assessment.export', 'uses' => 'TechnicalEvaluationController@technicalAssessmentExport']);
                                });
                            });
                        });
                    });

                    Route::group(array( 'prefix' => 'forum/threads' ), function()
                    {
                        Route::get('/', array( 'before' => 'checkpoint', 'as' => 'forum.threads', 'uses' => 'ForumThreadsController@index' ));
                        Route::get('create', array( 'as' => 'forum.threads.create', 'uses' => 'ForumThreadsController@create' ));
                        Route::post('/', array( 'as' => 'forum.threads.store', 'uses' => 'ForumThreadsController@store' ));
                        Route::get('{threadId}', array( 'before' => 'forum.thread.isViewable', 'as' => 'forum.threads.show', 'uses' => 'ForumThreadsController@show' ));
                        Route::delete('delete', array( 'as' => 'forum.threads.delete', 'uses' => 'ForumThreadsController@destroy' ));
                        Route::get('posts/{postId}/history', array( 'before' => 'forum.post.isViewable', 'as' => 'form.threads.posts.edit.history', 'uses' => 'ForumPostsController@editHistory' ));
                        Route::post('{threadId}/toggle-privacy', array( 'before' => 'forum.post.canTogglePrivacy', 'as' => 'form.threads.privacy.toggle', 'uses' => 'ForumThreadsController@togglePrivacySetting' ));
                        Route::get('{threadId}/users', array( 'before' => 'forum.thread.isViewable', 'as' => 'forum.threads.users', 'uses' => 'ForumThreadsController@getUserList' ));

                        Route::group(array( 'prefix' => 'posts' ), function()
                        {
                            Route::get('{parentPostId}/create', array( 'as' => 'form.threads.posts.create', 'uses' => 'ForumPostsController@create' ));
                            Route::post('{parentPostId}', array( 'as' => 'form.threads.posts.store', 'uses' => 'ForumPostsController@store' ));
                            Route::get('{postId}', array( 'before' => 'forum.isContentCreator', 'as' => 'form.threads.posts.edit', 'uses' => 'ForumPostsController@edit' ));
                            Route::put('{postId}', array( 'before' => 'forum.isContentCreator', 'as' => 'form.threads.posts.update', 'uses' => 'ForumPostsController@update' ));
                            Route::delete('delete', array( 'before' => 'forum.isContentCreator', 'as' => 'form.threads.posts.delete', 'uses' => 'ForumPostsController@destroy' ));

                            Route::group(array( 'before' => 'forum.isContentCreator' ), function()
                            {
                                Route::post('{postId}/alert', array( 'before' => 'notProjectRoles:' . Role::CONTRACTOR, 'as' => 'form.threads.posts.alert', 'uses' => 'ForumPostsController@alert' ));
                                Route::post('{postId}/automated-alert', array( 'as' => 'form.threads.posts.automatedAlert', 'uses' => 'ForumPostsController@automatedAlert' ));
                            });
                        });

                        Route::post('approval/forum/init', array( 'as' => 'approval.forum.threads.initialise', 'uses' => 'VerifierController@initiateThread' ));
                    });

                    require( 'module_routes/e_bidding_routes.php' );

                    require( 'module_routes/tender_routes.php' );

                    require( 'module_routes/messages_routes.php' );

                    require( 'module_routes/email_notifications_routes.php' );

                    require( 'module_routes/module_uploads_routes.php' );

                    require( 'contract_routes/PAM2006_routes.php' );

                    require( 'contract_routes/indonesia_civil_contract_routes.php' );

                    require( 'module_routes/tender_document_folder_routes.php' );

                    require( 'module_routes/project_document_folder_routes.php' );

                    // require( 'module_routes/form_of_tender_routes.php' );

                    require( 'module_routes/document_control_routes.php' );

                    require( 'module_routes/tender_document_file_routes.php' );

                    require( 'module_routes/project_document_file_routes.php' );

                    require( 'module_routes/project_report_user_permission_routes.php' );

                    require( 'module_routes/project_report_routes.php' );

                    require( 'module_routes/project_completion_date_routes.php' );
                });

                Route::group(array( 'prefix' => '{projectId}/open_tender_verification/{tenderId}' ), function()
                {
                    // user can still see the Tender has been opened, if it has been opened
                    Route::get('/', array( 'as' => 'projects.openTender.accessToVerifierDecisionForm', 'uses' => 'ProjectOpenTendersController@showOTVerifierDecisionForm' ));

                    // block the POST operation to confirm Tender once it has been opened
                    // block the POST operation if Open Tender is no longer being validated (due to rejection)
                    Route::post('/', array( 'before' => 'checkOpenTenderStatus|checkOpenTenderStillInValidation', 'uses' => 'ProjectOpenTendersController@processOTVerifierDecisionForm' ));
                });

                Route::group(array( 'prefix' => '{projectId}/technical-evaluation-verification/{tenderId}' ), function()
                {
                    // user can still see the Tender has been opened, if it has been opened
                    Route::get('/', array( 'as' => 'projects.technicalEvaluation.accessToVerifierDecisionForm', 'uses' => 'TechnicalEvaluationController@showTechnicalEvaluationVerifierDecisionForm' ));

                    // block the POST operation to confirm Tender once it has been opened
                    // block the POST operation if Technical Opening is no longer being validated (due to rejection)
                    Route::post('/', array( 'before' => 'checkTechnicalEvaluationStatus|technicalEvaluationStillInValidation', 'uses' => 'TechnicalEvaluationController@processTechnicalEvaluationVerifierDecisionForm' ));
                });

            });

            Route::group(array( 'before' => 'roles:' . Role::PROJECT_OWNER . '|companyAdminAccessLevel', 'prefix' => 'subsidiaries' ), function()
            {
                Route::get('/', array( 'as' => 'subsidiaries.index', 'uses' => 'SubsidiariesController@index' ));
                Route::get('list', array( 'as' => 'subsidiaries.ajax.list', 'uses' => 'SubsidiariesController@list' ));
                Route::get('create', array( 'as' => 'subsidiaries.create', 'uses' => 'SubsidiariesController@create' ));
                Route::get('{subsidiaryId}/edit', array( 'as' => 'subsidiaries.edit', 'uses' => 'SubsidiariesController@edit' ));
                Route::post('store', array( 'as' => 'subsidiaries.store', 'uses' => 'SubsidiariesController@store' ));
                Route::delete('{subsidiaryId}/delete', array( 'as' => 'subsidiaries.delete', 'uses' => 'SubsidiariesController@delete' ));
            });

            Route::group(array( 'before' => 'superAdminAccessLevel', 'prefix' => 'general-settings' ), function()
            {
                Route::get('/', array( 'as' => 'general_settings.index', 'uses' => 'GeneralSettingController@index' ));
                Route::post('store', array( 'as' => 'general_settings.store', 'uses' => 'GeneralSettingController@store' ));
            });

            Route::group(array( 'before' => 'superAdminAccessLevel', 'prefix' => 'delegate-company-verification' ), function()
            {
                Route::get('/', array( 'as' => 'users.companies.verification.delegate', 'uses' => 'CompanyVerificationController@delegate' ));
                Route::get('assigned', array( 'as' => 'users.companies.verification.assigned', 'uses' => 'CompanyVerificationController@getAssignedUsers' ));
                Route::get('assignable', array( 'as' => 'users.companies.verification.assignable', 'uses' => 'CompanyVerificationController@getAssignableUsers' ));
                Route::post('assign', array( 'as' => 'users.companies.verification.assign', 'uses' => 'CompanyVerificationController@assign' ));
                Route::delete('{userId}/unassign', array( 'as' => 'users.companies.verification.unassign', 'uses' => 'CompanyVerificationController@unassign' ));
            });

            Route::group(array( 'prefix' => 'general_uploads' ), function()
            {
                Route::get('download/{fileId}', array( 'as' => 'generalUploads.download', 'uses' => 'GeneralUploadsController@download' ));
                Route::post('create', array( 'as' => 'generalUploads.upload', 'uses' => 'GeneralUploadsController@store' ));
                Route::post('delete/{fileId}', array( 'as' => 'generalUploads.delete', 'uses' => 'GeneralUploadsController@destroy' ));
            });

            Route::group(array( 'prefix' => 'contractors', 'before' => 'moduleAccess:' . \PCK\ModulePermission\ModulePermission::MODULE_ID_CONTRACTOR_LISTING ), function()
            {
                Route::get('/', array( 'as' => 'contractors', 'uses' => 'ContractorsController@index' ));
                Route::get('contractorsdata', array( 'as' => 'contractorsData', 'uses' => 'ContractorsController@ajaxGetContractorsDataInJson' ));
            });

            Route::group(array( 'prefix' => 'country' ), function()
            {
                Route::get('{id?}', array( 'as' => 'country', 'uses' => 'CountriesController@getAllCountries' ));
                Route::get('states/{id?}', array( 'as' => 'country.states', 'uses' => 'CountriesController@getStateByCountryId' ));
                Route::get('getEvents/{id}', 'CountriesController@getEventByCountryId');
            });

            Route::get('/getVendorWorkCategories', ['as' => 'vendor.work.categories.get', 'uses' => 'VendorWorkCategoriesController@getVendorWorkCategories']);
            Route::get('/getVendorWorkSubCategories', ['as' => 'vendor.work.sub.categories.get', 'uses' => 'VendorWorkCategoriesController@getVendorWorkSubCategories']);

            Route::group(array( 'prefix' => 'calendar' ), function()
            {
                Route::post('update', 'CalendarController@update');
                Route::get('delete/{id}', 'CalendarController@delete');
                Route::get('events', 'CalendarController@getEvents');
                Route::get('setDefaultCountry/{id}', 'CalendarController@setDefaultCountry');
                Route::get('dt_events', 'CalendarController@listEvents');
            });

            Route::post('verify/{objectId}', array( 'before' => 'verifier.isCurrentVerifier', 'as' => 'verify', 'uses' => 'VerifierController@verify' ));

            require( 'module_routes/companies_routes.php' );

            require( 'module_routes/countries_routes.php' );

            require( 'module_routes/templates_routes.php' );

            require( 'module_routes/apportionment_types_routes.php' );

            require( 'module_routes/order_routes.php' );

            require( 'module_routes/vendor_registration_routes.php' );
            
            require( 'module_routes/vendor_management_routes.php' );

            require( 'module_routes/vendor_pre_qualification_routes.php' );

            require( 'module_routes/vendor_performance_evaluation_routes.php' );

            require( 'module_routes/ds_evaluation_routes.php' );

            require( 'module_routes/vendor_management_dashboard_routes.php' );

            require( 'module_routes/folder_routes.php' );

            require( 'module_routes/email_announcements_routes.php' );

            require( 'module_routes/project_report_template_routes.php' );

            require( 'module_routes/project_report_chart_template_routes.php' );

            Route::group(['before' => 'superAdminAccessLevel' ], function() {
                require( 'module_routes/theme_settings_routes.php' );
                require( 'module_routes/payment_gateway_settings_routes.php' );
            });
        });

        Route::post('spellCurrencyAmount', array( 'as' => 'convert.spellCurrencyAmount', 'uses' => 'MiscController@spellCurrencyAmount' ));

        // Unauthenticated forms
        Route::get('contractor-status/{key}/confirmation', array( 'as' => 'contractors.confirmStatus', 'uses' => 'ProjectTendersController@confirmStatus' ));
        Route::post('contractor-status/{key}/confirm', array( 'as' => 'contractors.confirmStatusSubmit', 'uses' => 'ProjectTendersController@confirmStatusSubmit' ));

        Route::get('tender-interview/{key}/request', array( 'as' => 'tender_interview.request', 'uses' => 'TenderInterviewsController@request' ));
        Route::post('tender-interview/{key}/confirm', array( 'as' => 'tender_interview.confirm', 'uses' => 'TenderInterviewsController@confirmStatus' ));
        Route::get('reply_sent', array( 'as' => 'replySent', 'uses' => 'UnauthenticatedFormsController@replySent' ));

        //consultant management
        Route::get('consultant-rfp-interview/{token}/{email}/reply', ['as' => 'consultant.management.consultant.rfp.interview.reply', 'uses' => 'ConsultantManagementRfpInterviewController@reply']);
        Route::post('consultant-rfp-interview/reply-store', ['as' => 'consultant.management.consultant.rfp.interview.reply.store', 'uses' => 'ConsultantManagementRfpInterviewController@replyStore']);
        Route::get('consultant-rfp-interview/{id}/reply-success', ['as' => 'consultant.management.consultant.rfp.interview.reply.success', 'uses' => 'ConsultantManagementRfpInterviewController@replySuccess']);

        Route::group(array( 'before' => 'appLicenseValid' ), function()
        {
            Route::group(array( 'prefix' => 'country' ), function()
            {
                Route::get('{id?}', array( 'as' => 'country', 'uses' => 'CountriesController@getAllCountries' ));
                Route::get('states/{id?}', array( 'as' => 'country.states', 'uses' => 'CountriesController@getStateByCountryId' ));
            });

            Route::get('register', array( 'as' => 'register', 'uses' => 'RegistrationController@create' ));
            Route::post('register', array( 'as' => 'register.store', 'uses' => 'RegistrationController@store' ));
            Route::get('register-success', array( 'as' => 'register.success', 'uses' => 'RegistrationController@success' ));
            Route::group(array( 'prefix' => 'register/data' ), function()
            {
                Route::get('contract-group-categories/all', array( 'as' => 'registration.contractGroupCategories', 'uses' => 'CompaniesController@getAllContractGroupCategories' ));
                Route::get('contract-group-categories/external-vendors', array( 'as' => 'registration.externalVendors.contractGroupCategories', 'uses' => 'RegistrationController@getExternalVendorContractGroupCategories' ));
                Route::get('vendor-categories/{contractGroupCategoryId?}', array( 'as' => 'registration.vendorCategories', 'uses' => 'CompaniesController@getVendorCategoryByContractGroupCategoryId' ));
                Route::get('vendor-work-categories/{vendorCategoryId?}', array( 'as' => 'registration.vendorWorkCategories', 'uses' => 'CompaniesController@getVendorWorkCategoriesByVendorCategoryId' ));
            });

            Route::get('project-main', array( 'as' => 'open_tenders.main_project', 'uses' => 'ProjectsController@mainProject' ));
            Route::get('project-detail/{Id}', array( 'as' => 'open_tenders.detail_project', 'uses' => 'ProjectsController@detailProject' ));
            Route::get('projects-main-ajax', ['as' => 'open_tenders.ajax_list', 'uses' => 'ProjectsController@mainList']);

            // news
            Route::get('list-news', array( 'as' => 'open_tenders.list_news', 'uses' => 'ProjectsController@listNews' ));
            Route::get('detail-news/{Id}', array( 'as' => 'open_tenders.detail_news', 'uses' => 'ProjectsController@detailNews' ));
            Route::get('list-news-ajax', ['as' => 'open_tenders.list_news_ajax', 'uses' => 'ProjectsController@ajaxListNews']);

            // insert contractors
            Route::post('tender-insert-contractor', ['as' => 'open_tender.tender_insert_contractor', 'uses' => 'ProjectTendersController@insertContractorIntoTenderDetails']);
            Route::post('lot-insert-contractor', ['as' => 'open_tender.lot_insert_contractor', 'uses' => 'ProjectTendersController@insertContractorIntoListOfTendererAsPending']);
        });

        /*
        |--------------------------------------------------------------------------
        | Login Routes
        |--------------------------------------------------------------------------
        |
        */

        Route::get('logout', array( 'as' => 'users.logout', 'uses' => 'SamlExtController@logout' ));

        Route::get('/', array( 'before' => 'authenticated', 'as' => 'users.login', 'uses' => 'SamlExtController@login' ));

    });

    require( 'module_routes/external_applications_routes.php' );
    require( 'module_routes/vm_vendor_migration_routes.php' );

    Route::group(array( 'prefix' => 'api' ), function()
    {
        Route::get('get-data', array( 'before' => 'auth.basic', 'as' => 'api.get-data', 'uses' => 'AutomateCodeController@getData' ));
        Route::get('post-data', array( 'before' => 'auth.basic', 'as' => 'api.post-data', 'uses' => 'AutomateCodeController@postData' ));
        Route::get('put-data', array( 'before' => 'auth.basic', 'as' => 'api.put-data', 'uses' => 'AutomateCodeController@putData' ));
        Route::get('delete-data', array( 'before' => 'auth.basic', 'as' => 'api.delete-data', 'uses' => 'AutomateCodeController@deleteData' ));
        Route::get('route', array( 'before' => 'auth.basic', 'as' => 'api.route', 'uses' => 'AutomateCodeController@apiRoute' ));
        Route::get('route-postman', array( 'before' => 'auth.basic', 'as' => 'api.route-postman', 'uses' => 'AutomateCodeController@apiRoutePostman' ));
        Route::get('bypass-route', array( 'before' => 'auth.basic', 'as' => 'api.bypass-route', 'uses' => 'AutomateCodeController@apiBypassRoute' ));
        Route::get('generate-json-data', array( 'before' => 'auth.basic', 'as' => 'api.generate-json-data', 'uses' => 'AutomateCodeController@generateJsonData' ));
        require( 'module_routes/api/data_api_routes.php' );
        Route::get('users_allow_access/{allow_access_to_gp}', array('as' => 'api.users', 'uses' => 'Api\ProcurementController@users' ));
        Route::get('vendors/{status}', array('as' => 'api.vendors', 'uses' => 'Api\ProcurementController@vendors' ));
        Route::get('subsidiaries/hierarchical', array('as' => 'api.subsidiaries.hierarchical', 'uses' => 'Api\ProcurementController@subsidiaries'));
        
        Route::get('user', array( 'before' => 'auth.basic', 'as' => 'api.user', 'uses' => 'MobileRestController@getUserInfo' ));
        Route::get('projects', array( 'before' => 'auth.basic', 'as' => 'api.projects', 'uses' => 'MobileRestController@getProjects' ));
        Route::get('projects/{projectId}', array( 'before' => 'auth.basic', 'as' => 'api.project.show', 'uses' => 'MobileRestController@getProject' ));
        Route::get('bills/{projectId}', array( 'before' => 'auth.basic', 'as' => 'api.projects.info', 'uses' => 'MobileRestController@getBills' ));

        Route::post('defectCategories', array( 'before' => 'auth.basic', 'as' => 'api.defects.categories.create', 'uses' => 'MobileRestController@defectCategoryAdd' ));
        Route::put('defectCategories/{defectCategoryId}', array( 'before' => 'auth.basic', 'as' => 'api.defects.categories.update', 'uses' => 'MobileRestController@defectCategoryUpdate' ));
        Route::delete('defectCategories/{defectCategoryId}', array( 'before' => 'auth.basic', 'as' => 'api.defects.categories.delete', 'uses' => 'MobileRestController@defectCategoryDelete' ));
        Route::get('defectCategories', array( 'before' => 'auth.basic', 'as' => 'api.defects.categories', 'uses' => 'MobileRestController@getDefectCategories' ));
        Route::get('defectCategories/{defectCategoryId}', array( 'before' => 'auth.basic', 'as' => 'api.defects.categories.show', 'uses' => 'MobileRestController@getDefectCategory' ));

        Route::post('defects', array( 'before' => 'auth.basic', 'as' => 'api.defects.create', 'uses' => 'MobileRestController@defectAdd' ));
        Route::put('defects/{defectId}', array( 'before' => 'auth.basic', 'as' => 'api.defects.update', 'uses' => 'MobileRestController@defectUpdate' ));
        Route::delete('defects/{defectId}', array( 'before' => 'auth.basic', 'as' => 'api.defects.delete', 'uses' => 'MobileRestController@defectDelete' ));
        Route::get('defects', array( 'before' => 'auth.basic', 'as' => 'api.defects', 'uses' => 'MobileRestController@getDefects' ));
        Route::get('defects/{defectId}', array( 'before' => 'auth.basic', 'as' => 'api.defects.show', 'uses' => 'MobileRestController@getDefect' ));

        Route::get('initial/{devId}', array( 'before' => 'auth.basic', 'as' => 'api.initial', 'uses' => 'MobileRestController@initialSync' ));
        Route::get('queuedCount/{devId}', array( 'before' => 'auth.basic', 'as' => 'api.queued.count', 'uses' => 'MobileRestController@getQueuedRecordCount' ));
        Route::get('queuedRecords/{limit}/{devId}', array( 'before' => 'auth.basic', 'as' => 'api.queued.records', 'uses' => 'MobileRestController@getQueuedRecords' ));
        Route::post('acknowledgedRecords/{devId}', array( 'before' => 'auth.basic', 'as' => 'api.acknowledged.records', 'uses' => 'MobileRestController@acknowledgedRecords' ));
        Route::post('syncRecords/{devId}', array( 'before' => 'auth.basic', 'as' => 'api.sync.records', 'uses' => 'MobileRestController@syncRecords' ));
        Route::get('attachment/{id}/{devId}', array( 'before' => 'auth.basic', 'as' => 'api.attachment', 'uses' => 'MobileRestController@getAttachment' ));
        Route::post('upload/{devId}', array( 'before' => 'auth.basic', 'as' => 'api.upload', 'uses' => 'MobileRestController@upload' ));

        //require( 'module_routes/api/order_routes.php' );
        require( 'module_routes/api/payment_gateway_routes.php' );

        //Route::get('projects', array('before' => 'auth.basic', 'as' => 'rest.projects', 'uses' => 'MobileRestController@getProjects' ));
        //Route::get('defects', array('before' => 'RESTServerCallOnly', 'as' => 'rest.defects', 'uses' => 'MobileRestController@getDefects' ));
        //Route::get('mod-permission', array('before' => 'RESTServerCallOnly', 'as' => 'rest.module.permissions', 'uses' => 'MobileRestController@getModulePermissionsByProject' ));
        //Route::get('trades-related-info', array('before' => 'RESTServerCallOnly', 'as' => 'rest.trades', 'uses' => 'MobileRestController@getProjectLabourRateTradesRelatedInfo' ));

    });

    Route::get('translate', ['as' => 'translate', 'uses' => 'TranslationController@translate']);
    Route::get('routes/generate', ['as' => 'routes.generate', 'uses' => 'RoutesController@generateRoutes']);

    Route::get('systime', function()
    {
        echo '<pre>';
        print_r(\Carbon\Carbon::now());
        echo '</pre>';
        die();
    });
});

