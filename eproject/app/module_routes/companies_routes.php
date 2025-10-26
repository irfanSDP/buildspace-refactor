<?php

use PCK\ContractGroups\Types\Role;

Route::group(array( 'prefix' => 'companies' ), function()
{
    // only allow super admin to view company's listing and adding new company
    Route::group(array( 'before' => 'superAdminAccessLevel' ), function()
    {
        Route::get('/', array( 'as' => 'companies', 'uses' => 'CompaniesController@index' ));
        Route::get('companiesdata', array( 'as' => 'companiesData', 'uses' => 'CompaniesController@ajaxGetCompaniesDataInJson' ));

        Route::get('create', array( 'as' => 'companies.create', 'uses' => 'CompaniesController@create' ));
        Route::post('create', array( 'uses' => 'CompaniesController@store' ));

        // add contractor details
        Route::group(array( 'prefix' => '{companyId}/contractor' ), function()
        {
            Route::get('create', array( 'as' => 'companies.contractors.create', 'uses' => 'ContractorsController@create' ));
            Route::post('create', array( 'uses' => 'ContractorsController@store' ));

            //default details
            Route::post('create_default', array( 'as' => 'companies.contractors.storeDefault', 'uses' => 'ContractorsController@storeDefault' ));

            Route::get('{contractorId}/edit', array( 'as' => 'companies.contractors.edit', 'uses' => 'ContractorsController@edit' ));
            Route::put('{contractorId}/edit', array( 'uses' => 'ContractorsController@update' ));
        });
    });

    // Import users (only available to company admin).
    Route::group(array( 'prefix' => '{companyId}', 'before' => 'companyOwnerChecking|companyConfirmed' ), function()
    {
        Route::get('importable-users', array( 'as' => 'companies.importableUsers', 'uses' => 'AuthController@getImportableUsers' ));
        Route::post('import-users', array( 'as' => 'companies.importUsers', 'uses' => 'AuthController@importUsers' ));
    });

    // company admin can edit back their own company's record
    Route::group(array( 'before' => 'superAdminCompanyAdminAccessLevel|companyOwnerChecking|companyConfirmed' ), function()
    {
        Route::group(array( 'prefix' => '{companyId}' ), function() {
            Route::group(array('before' => 'canEditCompanyDetails'), function() {
                Route::get('edit', array( 'as' => 'companies.edit', 'uses' => 'CompaniesController@edit' ));
                Route::put('edit', array( 'as' => 'companies.update', 'uses' => 'CompaniesController@update' ));
            });

            Route::get('checkCompanyUsersForPendingTasks', ['as' => 'company.users.pending.tasks.check', 'uses' => 'CompaniesController@checkUsersForPendingTasks']);
            Route::get('getUsersWithPendingTasks', ['as' => 'company.users.with.pending.tasks.get', 'uses' => 'CompaniesController@getUsersWithPendingTasks']);
            
            Route::group([ 'prefix' => 'user/{userId}' ],function() {
                Route::get('checkCompanyUserCanBeRemoved', ['as' => 'company.user.delete.or.deport.pending.tasks.check', 'uses' => 'CompaniesController@checkCompanyUserCanBeRemoved']);
                Route::get('getCompanyUserPendingTenderingTasks', ['as' => 'company.user.tendering.pending.tasks.get', 'uses' => 'CompaniesController@getCompanyUserPendingTenderingTasks']);
                Route::get('getCompanyUserPendingPostContractTasks', ['as' => 'company.user.post.contract.pending.tasks.get', 'uses' => 'CompaniesController@getCompanyUserPendingPostContractTasks']);
                Route::get('getCompanyUserPendingSiteModuleTasks', ['as' => 'company.user.post.site.module.tasks.get', 'uses' => 'CompaniesController@getCompanyUserPendingSiteModuleTasks']);
                Route::get('getCompanyUserLetterOfAwardUserPermissions', ['as' => 'company.user.letterOfAward.user.permissions.get', 'uses' => 'CompaniesController@getCompanyUserLetterOfAwardUserPermissions']);
                Route::get('getCompanyUserRequestForVariationUserPermissions', ['as' => 'company.user.requestForVariation.user.permissions.get', 'uses' => 'CompaniesController@getCompanyUserRequestForVariationUserPermissions']);
                Route::get('getCompanyUserContractManagementUserPermissions', ['as' => 'company.user.contractManagement.user.permission.get', 'uses' => 'CompaniesController@getCompanyUserContractManagementUserPermissions']);
                Route::get('getCompanyUserSiteManagementUserPermissions', ['as' => 'company.user.siteManagement.user.permission.get', 'uses' => 'CompaniesController@getCompanyUserSiteManagementUserPermissions']);
                Route::get('getCompanyUserRequestForInspectionUserPermissions', ['as' => 'company.user.request.for.inspection.user.permission.get', 'uses' => 'CompaniesController@getCompanyUserRequestForInspectionUserPermissions']);
                Route::get('get-vendor-performance-evaluation-approvals', ['as' => 'company.user.getVendorPerformanceEvaluationApprovals', 'uses' => 'CompaniesController@getVendorPerformanceEvaluationApprovals']);
            });
        });

        Route::delete('delete/{companyId}', array( 'as' => 'companies.delete', 'uses' => 'CompaniesController@destroy' ));
    });

    Route::get('my_company', array( 'as' => 'companies.profile', 'uses' => 'CompaniesController@showMyCompany' ));

    Route::group(array( 'before' => 'roles:' . Role::GROUP_CONTRACT . ',' . Role::PROJECT_OWNER ), function()
    {
        Route::get('{companyId}/show', array( 'as' => 'companies.show', 'uses' => 'CompaniesController@show' ));
    });

    // super admin and company admin can create new user
    Route::group(array( 'prefix' => '{companyId}/users', 'before' => 'superAdminCompanyAdminAccessLevel|companyOwnerChecking|companyConfirmed' ), function()
    {
        Route::get('/', array( 'as' => 'companies.users', 'uses' => 'AuthController@index' ));

        Route::group(array( 'before' => 'canAddUser' ), function()
        {
            Route::get('create', array( 'as' => 'companies.users.create', 'uses' => 'AuthController@create' ));
            Route::post('create', array( 'uses' => 'AuthController@store' ));
        });

        Route::group(array( 'prefix' => '{userId}' ), function()
        {
            Route::get('edit', array( 'as' => 'companies.users.show', 'uses' => 'AuthController@show' ));
            Route::put('edit', array( 'uses' => 'AuthController@update' ));

            Route::get('resend_validation_email', array( 'as' => 'companies.users.resend_validation_email', 'uses' => 'AuthController@resendValidationEmail' ));

            Route::post('delete', array( 'as' => 'companies.users.delete', 'uses' => 'AuthController@destroy' ));
            Route::post('deport', array( 'as' => 'companies.users.deport', 'uses' => 'AuthController@deport' ));
        });
    });

    Route::group(array( 'before' => 'companyRegistrationVerifierAccessLevel' ), function()
    {
        Route::get('verification', array( 'as' => 'companies.verification.index', 'uses' => 'CompanyVerificationController@index' ));
        Route::get('verification-get', array( 'as' => 'companies.verification.data', 'uses' => 'CompanyVerificationController@get' ));

        Route::group(array( 'prefix' => '{companyId}', 'before' => 'companyNotConfirmed' ), function()
        {
            Route::get('verify', array( 'as' => 'companies.verify', 'uses' => 'CompanyVerificationController@confirmCompany' ));
            Route::delete('verification/delete', array( 'as' => 'companies.verification.delete', 'uses' => 'CompanyVerificationController@destroy' ));
            Route::get('verification/show', array( 'as' => 'companies.verification.show', 'uses' => 'CompanyVerificationController@show' ));
        });
    });
});

Route::group(array( 'prefix' => 'vendor-profiles' ), function()
{
    Route::group(array('before' => 'vendorManagment.vendorProfile.canView'), function()
    {
        Route::get('/', array( 'as' => 'vendorProfile', 'uses' => 'VendorProfilesController@index' ));
        Route::post('list', array( 'as' => 'vendorProfile.ajax.list', 'uses' => 'VendorProfilesController@list' ));
        Route::get('export/excel/type/{type}', array( 'as' => 'vendorProfile.export.excel', 'uses' => 'VendorProfilesController@exportExcel' ));

        Route::get('{companyId}/archived-storage', array( 'as' => 'vendorProfile.archivedStorage.ajax.list', 'uses' => 'VendorProfilesController@archivedStorage' ));
        Route::get('{companyId}/archived-storage-download', array( 'as' => 'vendorProfile.archivedStorage.download', 'uses' => 'VendorProfilesController@archivedStorageDownload' ));
        Route::get('{companyId}', array( 'as' => 'vendorProfile.show', 'uses' => 'VendorProfilesController@show' ));

        Route::get('{companyId}/vendor-list', array( 'as' => 'vendorProfile.vendor.list', 'uses' => 'VendorProfilesController@vendorList' ));
        Route::get('{companyId}/vendor-registration-certificate', array( 'as' => 'vendorProfile.registrationCertificate', 'uses' => 'VendorProfilesController@certificate' ));
        Route::get('{companyId}/vendor-registration-remark-logs', array( 'as' => 'vendorProfile.remark.logs.get', 'uses' => 'VendorProfilesController@getVendorRegistrationRemarkLogs'));

        Route::get('{companyId}/vendor-performance-evaluations/lastest', array( 'as' => 'vendorProfile.vendorPerformanceEvaluation.latest', 'uses' => 'VendorProfilesController@vendorPerformanceEvaluationLatest' ));
        Route::get('{companyId}/vendor-performance-evaluations/vendor-work-category/{vendorWorkCategoryId}/historic', array( 'as' => 'vendorProfile.vendorPerformanceEvaluation.historic', 'uses' => 'VendorProfilesController@vendorPerformanceEvaluationHistoric' ));
        Route::get('{companyId}/vendor-performance-evaluations/vendor-work-category/{vendorWorkCategoryId}/cycle/{cycleId}/evaluations', array( 'as' => 'vendorProfile.vendorPerformanceEvaluation.cycleEvaluations', 'uses' => 'VendorProfilesController@vendorPerformanceEvaluationCycleEvaluations' ));
        Route::get('{companyId}/vendor-performance-evaluations/vendor-work-category/{vendorWorkCategoryId}/evaluation/{evaluationId}/forms', array( 'as' => 'vendorProfile.vendorPerformanceEvaluation.forms', 'uses' => 'VendorProfilesController@evaluationForms' ));
        Route::get('{companyId}/vendor-performance-evaluations/evaluation-form/{companyEvaluationFormId}/evaluator-log', array( 'as' => 'vendorProfile.vendorPerformanceEvaluation.form.evaluatorLog', 'uses' => 'VendorProfilesController@evaluationFormEvaluationEvaluatorLog' ));
        Route::get('{companyId}/vendor-performance-evaluations/evaluation-form/{companyEvaluationFormId}/verifier-log', array( 'as' => 'vendorProfile.vendorPerformanceEvaluation.form.verifierLog', 'uses' => 'VendorProfilesController@evaluationFormEvaluationVerifierLog' ));
        Route::get('{companyId}/vendor-performance-evaluations/evaluation-form/{companyEvaluationFormId}/edit-log', array( 'as' => 'vendorProfile.vendorPerformanceEvaluation.form.editLog', 'uses' => 'VendorProfilesController@evaluationFormEvaluationEditLog' ));
        Route::get('{companyId}/vendor-performance-evaluations/evaluation-form/{companyEvaluationFormId}/edit-log/{editLogId}/edit-details-log', array( 'as' => 'vendorProfile.vendorPerformanceEvaluation.form.editDetailsLog', 'uses' => 'VendorProfilesController@evaluationFormEvaluationEditDetailsLog' ));
        Route::get('{companyId}/vendor-performance-evaluations/evaluation-form/{companyEvaluationFormId}/export', array( 'as' => 'vendorProfile.vendorPerformanceEvaluation.form.export', 'uses' => 'VendorProfilesController@evaluationFormExport' ));
        Route::get('{companyId}/vendor-performance-evaluations/evaluation-form/{companyEvaluationFormId}/form-information', array( 'as' => 'vendorProfile.vendorPerformanceEvaluation.form.information', 'uses' => 'VendorProfilesController@evaluationFormInformation' ));
        Route::get('{companyId}/vendor-performance-evaluations/evaluation-form/{companyEvaluationFormId}/show', array( 'as' => 'vendorProfile.vendorPerformanceEvaluation.form.show', 'uses' => 'VendorProfilesController@evaluationForm' ));
        Route::get('{companyId}/vendor-performance-evaluations/evaluation-form/{companyEvaluationFormId}/attachments', array( 'as' => 'vendorProfile.vendorPerformanceEvaluation.form.attachments', 'uses' => 'VendorProfilesController@evaluationFormAttachments' ));

        Route::get('{companyId}/awarded-projects', array( 'as' => 'vendorProfile.awardedProjects', 'uses' => 'VendorProfilesController@awardedProjects' ));
        Route::get('{companyId}/completed-projects', array( 'as' => 'vendorProfile.completedProjects', 'uses' => 'VendorProfilesController@completedProjects' ));

        Route::get('{companyId}/{type}/company-personnel-list', array( 'as' => 'vendorProfile.company.personnel.list', 'uses' => 'VendorProfilesController@companyPersonnelList' ));
        Route::get('{companyId}/{type}/track-record-list', array( 'as' => 'vendorProfile.track.record.list', 'uses' => 'VendorProfilesController@trackRecordList' ));
        Route::get('{companyId}/vendor-prequalifiction-list', array( 'as' => 'vendorProfile.vendor.prequalification.list', 'uses' => 'VendorProfilesController@vendorPrequalifictionList' ));
        Route::get('vendor-prequalification/{vendorPrequalificationId}/details', array( 'as' => 'vendorProfile.vendor.prequalification.details', 'uses' => 'VendorProfilesController@vendorPrequalifictionDetails' ));
        Route::get('{companyId}/vendor-prequalification/form/{vendorPreQualificationId}/export', array( 'as' => 'vendorProfile.vendorPreQualification.form.export', 'uses' => 'VendorProfilesController@preQualificationFormExport' ));
        Route::get('{companyId}/supplier-credit-facility-list', array( 'as' => 'vendorProfile.supplier.credit.facility.list', 'uses' => 'VendorProfilesController@supplierCreditFacilityList' ));

        Route::get('{vendorProfileId}/getActionLogs', array( 'as' => 'vendorProfile.action.logs.get', 'uses' => 'VendorProfilesController@getActionsLogs' ));

        Route::get('{companyId}/company-users', array('as' => 'company.users.list', 'uses' => 'UsersController@listCompanyUsers'));
        Route::get('{companyId}/lms-users', array('as' => 'lms.users.list', 'uses' => 'UsersController@listLmsUsers'));
        Route::post('{userId}/resendValidationEmail',['as' => 'processor.validation.email.resend', 'uses' => 'UsersController@resendValidationEmail']);

        Route::get('{companyId}/contract/{contractId}/consultant-contract-details', array('as' => 'consultant.contract.details.get', 'uses' => 'VendorProfilesController@getConsultantContractDetails'));

        Route::get('project-track-record/{trackRecordProjectId}/downloads', array('as' => 'vendorProfile.projectTrackRecord.downloads.get', 'uses' => 'ProjectTrackRecordController@getDownloadList' ));
        Route::get('supplier-credit-facilities/{supplierCreditFacilitiesId}/getAttachmentsList', array('as' => 'vendorProfile.supplierCreditFacilities.attachments.get', 'uses' => 'SupplierCreditFacilitiesController@getAttachmentsList' ));
    });

    Route::get('vendor-registration/{vendorRegistrationId}/company-personnel/getAttachmentListByCompanyPersonnelType/type/{type}', ['before' => 'vendorProfile.vendorRegistration.isViewerOrOwner', 'as' => 'vendorProfile.companyPersonnel.attachments.get', 'uses' => 'CompanyPersonnelController@getAttachmentListByCompanyPersonnelType']);
    Route::get('vendor-registration/{vendorRegistrationId}/submissionLogs', array('before' => 'vendorProfile.vendorRegistration.isViewerOrOwner', 'as' => 'vendorProfile.registrationAndPreQualification.submissionLogs.get', 'uses' => 'VendorRegistrationAndPreQualificationApprovalsController@getSubmissionLogs'));
    Route::get('vendor-registration/{vendorRegistrationId}/processorAttachments/type/{type}/getProcessorAttachmentsList', ['before' => 'vendorProfile.vendorRegistration.isViewerOrOwner', 'as' => 'vendorProfile.processor.attachments.list', 'uses' => 'VendorRegistrationAndPreQualificationApprovalsController@getProcessorAttachmentsList']);

    Route::group(array('before' => 'vendorManagement.hasPermission:'.\PCK\VendorManagement\VendorManagementUserPermission::TYPE_VENDOR_PROFILE_EDIT), function()
    {
        Route::get('{companyId}/edit', array( 'as' => 'vendorProfile.edit', 'uses' => 'VendorProfilesController@edit' ));
        Route::post('company-details-store', array( 'as' => 'vendorProfile.company.details.store', 'uses' => 'VendorProfilesController@companyDetailsStore' ));
        Route::post('vendor-store', array( 'as' => 'vendorProfile.vendor.store', 'uses' => 'VendorProfilesController@vendorStore' ));
        Route::get('{vendorId}/vendor-edit', array( 'as' => 'vendorProfile.vendor.edit', 'uses' => 'VendorProfilesController@vendorEdit' ));
        Route::post('{vendorId}/vendor-delete', array( 'as' => 'vendorProfile.vendor.delete', 'uses' => 'VendorProfilesController@vendorDelete' ));
        Route::post('{companyId}/deactivate', array( 'as' => 'vendorProfile.deactivate', 'uses' => 'VendorProfilesController@deactivate' ));
        Route::post('{companyId}/activate', array( 'as' => 'vendorProfile.activate', 'uses' => 'VendorProfilesController@activate' ));
    });

    Route::group(array('before' => 'vendorManagement.vendorProfile.isOwnerOrEditor', 'prefix' => 'vendor-profile/{vendorProfileId}/attachments/field/{field}'), function() {
        Route::get('/getAttachmentsCount', array('as' => 'vendorProfile.attachments.count', 'uses' => 'VendorProfilesController@getAttachmentsCount'));
        Route::get('/getAttachmentsList', array('as' => 'vendorProfile.attachments.list', 'uses' => 'VendorProfilesController@getAttachmentsList'));
        Route::post('/updateAttachments', array('as' => 'vendorProfile.attachments.update', 'uses' => 'VendorProfilesController@updateAttachments'));
    });

    Route::group(array('before' => 'vendorManagement.hasPermission:'.\PCK\VendorManagement\VendorManagementUserPermission::TYPE_VENDOR_PROFILE_EDIT), function()
    {
        Route::post('{companyId}/tags/sync', array( 'as' => 'vendorProfile.tags.sync', 'uses' => 'VendorProfilesController@syncTags' ));

        Route::group(array('prefix' => 'vendor-profile/{vendorProfileId}'), function() {
            Route::get('remarks', array( 'as' => 'vendorProfile.remarks.ajax.list', 'uses' => 'VendorProfilesController@remarks' ));
            Route::post('saveRemarks', array('as' => 'vendorProfile.remarks.save', 'uses' => 'VendorProfilesController@saveRemarks'));
        });

        Route::group(array('prefix' => 'vendor-profile-remarks/{remarkId}'), function() {
            Route::post('updateRemarks', array('as' => 'vendorProfile.remarks.update', 'uses' => 'VendorProfilesController@updateRemarks'));
            Route::post('deleteRemarks', array('as' => 'vendorProfile.remarks.delete', 'uses' => 'VendorProfilesController@deleteRemarks'));
        });
    });
});