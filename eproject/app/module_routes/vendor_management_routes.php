<?php
Route::group(array( 'prefix' => 'vendor-management', 'before' => 'systemModule.vendorManagement.enabled'), function()
{
    Route::group(array('prefix'=>'approval/registration-and-pre-qualification', 'before' => 'vendorManagement.hasPermission:'.\PCK\VendorManagement\VendorManagementUserPermission::TYPE_APPROVAL_REGISTRATION || \PCK\VendorManagement\VendorManagementUserPermission::TYPE_VENDOR_REGISTRATION_VERIFIER), function()
    {
        Route::get('/', array('as' => 'vendorManagement.approval.registrationAndPreQualification', 'uses' => 'VendorRegistrationAndPreQualificationApprovalsController@index'));
        Route::get('list', array('as' => 'vendorManagement.approval.registrationAndPreQualification.ajax.list', 'uses' => 'VendorRegistrationAndPreQualificationApprovalsController@list'));
        Route::get('{vendorRegistrationId}/submissionLogs', array('as' => 'vendorManagement.approval.registrationAndPreQualification.submissionLogs.get', 'uses' => 'VendorRegistrationAndPreQualificationApprovalsController@getSubmissionLogs'));
        
        Route::get('processorDeleteCompanyLogs', array('as' => 'vendorManagement.approval.registrationAndPreQualification.processorDeletedCompanyLogs', 'uses' => 'VendorRegistrationAndPreQualificationApprovalsController@getProcessorDeleteCompanyLogs'));

        Route::get('{vendorRegistrationId}/assign', array('as' => 'vendorManagement.approval.registrationAndPreQualification.assignForm', 'uses' => 'VendorRegistrationAndPreQualificationApprovalsController@assignForm'));
        Route::get('{vendorRegistrationId}/assign/get-processors', array('as' => 'vendorManagement.approval.registrationAndPreQualification.assignForm.processors', 'uses' => 'VendorRegistrationAndPreQualificationApprovalsController@assignableProcessorsList'));
        
        Route::group(['before' => 'vendorManagement.vendorRegistration.canAssign'], function()
        {
            Route::post('{vendorRegistrationId}/assign', array('as' => 'vendorManagement.approval.registrationAndPreQualification.assign', 'uses' => 'VendorRegistrationAndPreQualificationApprovalsController@assign'));
        });
        
        Route::get('{vendorRegistrationId}', array('as' => 'vendorManagement.approval.registrationAndPreQualification.show', 'uses' => 'VendorRegistrationAndPreQualificationApprovalsController@show'));
        Route::post('{vendorRegistrationId}', array('before' => 'vendorManagement.isRegistrationProcessor', 'as' => 'vendorManagement.approval.registrationAndPreQualification.submit', 'uses' => 'VendorRegistrationAndPreQualificationApprovalsController@submit'));
        Route::post('{vendorRegistrationId}/approve', array('before' => 'vendorManagement.isRegistrationApprover', 'as' => 'vendorManagement.approval.registrationAndPreQualification.approve', 'uses' => 'VendorRegistrationAndPreQualificationApprovalsController@approve'));
        Route::post('{vendorRegistrationId}/delete', array('before' => 'vendorManagement.isCompanyDeletable', 'as' => 'vendorManagement.approval.vendorRegistration.company.delete', 'uses' => 'VendorRegistrationAndPreQualificationApprovalsController@deleteCompany'));

        Route::get('{vendorRegistrationId}/company-details/attachment/field/{field}/getCompanyDetailsAttachmentsList', ['as' => 'vendor.approval.registration.details.attachements.get', 'uses' => 'VendorRegistrationAndPreQualificationApprovalsController@getCompanyDetailsAttachmentsList']);

        Route::group(['prefix' => '{vendorRegistrationId}/processorAttachments/type/{type}'], function() {
            Route::get('/getProcessorAttachmentsList', ['as' => 'vendorManagement.approval.processor.attachments.list', 'uses' => 'VendorRegistrationAndPreQualificationApprovalsController@getProcessorAttachmentsList']);
            Route::post('/uploadProcessorAttachments', ['as' => 'vendorManagement.approval.processor.attachments.upload', 'uses' => 'VendorRegistrationAndPreQualificationApprovalsController@uploadProcessorAttachments']);
            Route::get('/getProcessorAttachmentsCount', ['as' => 'vendorManagement.approval.processor.attachments.count', 'uses' => 'VendorRegistrationAndPreQualificationApprovalsController@getProcessorAttachmentsCount']);
        });

        Route::get('{vendorRegistrationId}/company-details', array('as' => 'vendorManagement.approval.companyDetails', 'uses' => 'VendorRegistrationAndPreQualificationApprovalsController@companyDetails'));
        Route::post('{vendorRegistrationId}/company-details', array('as' => 'vendorManagement.approval.companyDetails.reject', 'uses' => 'VendorRegistrationAndPreQualificationApprovalsController@companyDetailsReject'));
        Route::post('{vendorRegistrationId}/company-details/resolve', array('as' => 'vendorManagement.approval.companyDetails.resolve', 'uses' => 'VendorsVendorRegistrationDetailsController@resolve'));

        Route::get('{vendorRegistrationId}/company-personnel', array('as' => 'vendorManagement.approval.companyPersonnel', 'uses' => 'VendorRegistrationAndPreQualificationApprovalsController@companyPersonnel'));
        Route::get('{vendorRegistrationId}/company-personnel/create', array('as' => 'vendorManagement.approval.companyPersonnel.create', 'uses' => 'CompanyPersonnelController@processorCreate'));
        Route::post('{vendorRegistrationId}/company-personnel/store', array('as' => 'vendorManagement.approval.companyPersonnel.store', 'uses' => 'CompanyPersonnelController@processorStore'));
        Route::get('{companyPersonnelId}/company-personnel/edit', array('as' => 'vendorManagement.approval.companyPersonnel.edit', 'uses' => 'CompanyPersonnelController@processorEdit'));
        Route::post('{companyPersonnelId}/company-personnel/update', array('as' => 'vendorManagement.approval.companyPersonnel.update', 'uses' => 'CompanyPersonnelController@update'));
        Route::delete('{companyPersonnelId}/company-personnel/delete', array('as' => 'vendorManagement.approval.companyPersonnel.destroy', 'uses' => 'CompanyPersonnelController@destroy'));
        Route::post('{vendorRegistrationId}/company-personnel', array('as' => 'vendorManagement.approval.companyPersonnel.reject', 'uses' => 'VendorRegistrationAndPreQualificationApprovalsController@companyPersonnelReject'));
        Route::post('{vendorRegistrationId}/company-personnel/resolve', array('as' => 'vendorManagement.approval.companyPersonnel.resolve', 'uses' => 'CompanyPersonnelController@resolve'));
        Route::get('{vendorRegistrationId}/company-personnel/getActionLogs', array('as' => 'vendorManagement.approval.companyPersonnel.action.logs.get', 'uses' => 'CompanyPersonnelController@getActionLogs'));

        Route::get('{vendorRegistrationId}/project-track-record', array('as' => 'vendorManagement.approval.projectTrackRecord', 'uses' => 'VendorRegistrationAndPreQualificationApprovalsController@projectTrackRecord'));
        Route::post('{vendorRegistrationId}/project-track-record', array('as' => 'vendorManagement.approval.projectTrackRecord.reject', 'uses' => 'VendorRegistrationAndPreQualificationApprovalsController@projectTrackRecordReject'));
        Route::get('{vendorRegistrationId}/project-track-record/create', array('as' => 'vendorManagement.approval.projectTrackRecord.create', 'uses' => 'ProjectTrackRecordController@processorCreate'));
        Route::post('{vendorRegistrationId}/project-track-record/store', array('as' => 'vendorManagement.approval.projectTrackRecord.store', 'uses' => 'ProjectTrackRecordController@processorStore'));
        Route::get('{vendorRegistrationId}/project-track-record/edit', array('as' => 'vendorManagement.approval.projectTrackRecord.edit', 'uses' => 'ProjectTrackRecordController@processorEdit'));
        Route::post('{trackRecordProjectId}/project-track-record/update', array('as' => 'vendorManagement.approval.projectTrackRecord.update', 'uses' => 'ProjectTrackRecordController@update'));
        Route::delete('{trackRecordProjectId}/project-track-record/delete', array('as' => 'vendorManagement.approval.projectTrackRecord.delete', 'uses' => 'ProjectTrackRecordController@destroy'));
        Route::get('{trackRecordProjectId}/downloads', array('as' => 'vendorManagement.approval.projectTrackRecord.downloads.get', 'uses' => 'ProjectTrackRecordController@getDownloadList' ));
        Route::post('{vendorRegistrationId}/project-track-record/resolve', array('as' => 'vendorManagement.approval.projectTrackRecord.resolve', 'uses' => 'ProjectTrackRecordController@resolve'));
        Route::get('{vendorRegistrationId}/project-track-record/getActionLogs' , array('as' => 'vendorManagement.approval.projectTrackRecord.action.logs.get', 'uses' => 'ProjectTrackRecordController@getActionLogs'));

        Route::get('{vendorRegistrationId}/supplier-credit-facilities', array('as' => 'vendorManagement.approval.supplierCreditFacilities', 'uses' => 'VendorRegistrationAndPreQualificationApprovalsController@supplierCreditFacilities'));
        Route::post('{vendorRegistrationId}/supplier-credit-facilities', array('as' => 'vendorManagement.approval.supplierCreditFacilities.reject', 'uses' => 'VendorRegistrationAndPreQualificationApprovalsController@supplierCreditFacilitiesReject'));
        Route::get('{vendorRegistrationId}/supplier-credit-facilities/create', array('as' => 'vendorManagement.approval.supplierCreditFacilities.create', 'uses' => 'SupplierCreditFacilitiesController@processorCreate'));
        Route::post('{vendorRegistrationId}/supplier-credit-facilities/store', array('as' => 'vendorManagement.approval.supplierCreditFacilities.store', 'uses' => 'SupplierCreditFacilitiesController@processorStore'));
        Route::get('{supplierCreditFacilityId}/supplier-credit-facilities/edit', array('as' => 'vendorManagement.approval.supplierCreditFacilities.edit', 'uses' => 'SupplierCreditFacilitiesController@processorEdit'));
        Route::post('{supplierCreditFacilityId}/supplier-credit-facilities/update', array('as' => 'vendorManagement.approval.supplierCreditFacilities.update', 'uses' => 'SupplierCreditFacilitiesController@update'));
        Route::delete('{supplierCreditFacilityId}/supplier-credit-facilities/delete', array('as' => 'vendorManagement.approval.supplierCreditFacilities.destroy', 'uses' => 'SupplierCreditFacilitiesController@destroy'));
        Route::post('{vendorRegistrationId}/supplier-credit-facilities/resolve', array('as' => 'vendorManagement.approval.supplierCreditFacilities.resolve', 'uses' => 'SupplierCreditFacilitiesController@resolve'));
        Route::get('{vendorRegistrationId}/supplier-credit-facilities/getActionLogs', array('as' => 'vendorManagement.approval.supplierCreditFacilities.action.logs.get', 'uses' => 'SupplierCreditFacilitiesController@getActionLogs'));

        Route::post('{vendorRegistrationId}/uploads/directors', array( 'as' => 'vendorManagement.approval.uploads.directors', 'uses' => 'CompanyPersonnelController@processorDirectorsUpload' ));
        Route::post('{vendorRegistrationId}/uploads/shareholders', array( 'as' => 'vendorManagement.approval.uploads.shareholders', 'uses' => 'CompanyPersonnelController@processorShareholdersUpload' ));
        Route::post('{vendorRegistrationId}/uploads/company-heads', array( 'as' => 'vendorManagement.approval.uploads.companyHeads', 'uses' => 'CompanyPersonnelController@processorCompanyHeadsUpload' ));

        Route::group(array('prefix' => '{vendorRegistrationId}/registration'), function()
        {
            Route::get('/vendorRegistrationFormShow', array('as' => 'vendorManagement.approval.registration', 'uses' => 'VendorRegistrationApprovalsController@vendorRegistrationFormShow'));
            Route::post('/rejectVendorFormSubmission', ['as' => 'vendor.form.submission.reject', 'uses' => 'VendorRegistrationApprovalsController@rejectVendorFormSubmission']);
            Route::get('/getActionLogs', ['as' => 'vendor.form.submission.action.logs.get', 'uses' => 'VendorRegistrationApprovalsController@getActionLogs']);
        });

        Route::group(array('prefix'=>'{vendorRegistrationId}/pre-qualification'), function()
        {
            Route::get('/', array('as' => 'vendorManagement.approval.preQualification', 'uses' => 'VendorPreQualificationApprovalsController@index'));

            Route::get('/getActionLogs', ['as' => 'vendorManagement.approval.vendorPreQualification.action.logs.get', 'uses' => 'VendorsVendorPreQualificationController@getActionLogs']);

            Route::get('{vendorPreQualificationId}/', array('as' => 'vendorManagement.approval.preQualification.approval', 'uses' => 'VendorPreQualificationApprovalsController@approval'));
            Route::get('{vendorPrequalificationId}/getLiveVpqScore', array('as' => 'vendorPreQualification.live.score.get', 'uses' => 'VendorsVendorPreQualificationController@getLiveVpqScore'));

            Route::post('{vendorPreQualificationId}/save', array( 'as' => 'vendorManagement.approval.preQualification.approval.save', 'uses' => 'VendorPreQualificationApprovalsController@save' ));

            Route::get('{vendorPreQualificationId}/form/{formId}/edit', array( 'as' => 'vendorManagement.approval.vendorPreQualification.form', 'uses' => 'VendorsVendorPreQualificationController@processorForm' ));
            Route::post('{vendorPreQualificationId}/form/{formId}', array( 'as' => 'vendorManagement.approval.vendorPreQualification.formUpdate', 'uses' => 'VendorsVendorPreQualificationController@processorFormUpdate' ));
        });

        Route::get('{vendorRegistrationId}/company-personnel/getAttachmentListByCompanyPersonnelType/type/{type}', ['as' => 'company.personnel.approval.attachments.get', 'uses' => 'CompanyPersonnelController@getAttachmentListByCompanyPersonnelType']);

        Route::group(array('prefix' => '{companyId}/payment'), function()
        {
            Route::get('/', array('as' => 'vendorManagement.approval.payment', 'uses' => 'VendorRegistrationPaymentApprovalsController@index')); // can change to company
            Route::get('/getAllRecordsByCompany', array('as' => 'vendorManagement.approval.payment.records.all.get', 'uses' => 'VendorRegistrationPaymentApprovalsController@getAllRecordsByCompany')); //

            Route::group(['prefix' => '{paymentId}'], function()
            {
                Route::get('/getUploadedAttachments', array('as' => 'vendorManagement.approval.payment.uploaded.attachments.get', 'uses' => 'VendorRegistrationPaymentApprovalsController@getUploadedAttachments'));

                // payment updates to set the paid status and completed status (along with their dates)
                Route::post('/vendorRegistrationPaymentUpdate', array('as' => 'vendorManagement.approval.payment.paidOrSuccessful.status.update', 'uses' => 'VendorRegistrationPaymentApprovalsController@vendorRegistrationPaymentUpdate'));

                Route::group(['prefix' => 'field/{field}'], function() {
                    Route::get('/getPaymentAdditionalAttachments', array('as' => 'vendorManagement.approval.payment.additional.attachments.get', 'uses' => 'VendorRegistrationPaymentApprovalsController@getPaymentAdditionalAttachments'));
                    Route::post('/uploadPaymentAdditionalAttachments', array('as' => 'vendorManagement.approval.payment.additional.attachments.upload', 'uses' => 'VendorRegistrationPaymentApprovalsController@uploadPaymentAdditionalAttachments'));
                    Route::get('/getPaymentAdditionalAttachmentsCount', array('as' => 'vendorManagement.approval.payment.additional.attachments.count.get', 'uses' => 'VendorRegistrationPaymentApprovalsController@getPaymentAdditionalAttachmentsCount'));
                });
            });

            Route::post('reject', array('as' => 'vendorManagement.approval.paymentSection.reject', 'uses' => 'VendorRegistrationPaymentApprovalsController@vendorRegistrationPaymentSectionReject'));
            Route::post('resolve', array('as' => 'vendorManagement.approval.paymentSection.resolve', 'uses' => 'VendorRegistrationPaymentApprovalsController@resolve'));
        });
    });

    Route::post('{companyId}/send-renewal-reminder', array('before' => 'vendorManagement.hasPermission:'.\PCK\VendorManagement\VendorManagementUserPermission::TYPE_DEACTIVATED_VENDOR_LIST, 'as' => 'vendorManagement.renewalReminder', 'uses' => 'DeactivatedVendorsController@sendRenewalReminder'));
    Route::post('{companyId}/send-update-reminder', array('before' => 'vendorManagement.hasPermission:'.\PCK\VendorManagement\VendorManagementUserPermission::TYPE_DEACTIVATED_VENDOR_LIST, 'as' => 'vendorManagement.updateReminder', 'uses' => 'DeactivatedVendorsController@sendUpdateReminder'));

    Route::group(array('prefix'=>'lists'), function()
    {
        Route::group(array('prefix'=>'active-vendor-list', 'before' => 'vendorManagement.hasPermission:'.\PCK\VendorManagement\VendorManagementUserPermission::TYPE_ACTIVE_VENDOR_LIST), function()
        {
            Route::get('/', array('as' => 'vendorManagement.activeVendorList.index', 'uses' => 'ActiveVendorsController@index'));
            Route::get('list', array('as' => 'vendorManagement.activeVendorList.ajax.list', 'uses' => 'ActiveVendorsController@list'));
            Route::get('{companyId}/vendors', array('as' => 'vendorManagement.activeVendorList.vendors.ajax.list', 'uses' => 'ActiveVendorsController@breakdownList'));

            Route::get('scores/list', array('as' => 'vendorManagement.activeVendorList.scores.list', 'uses' => 'ActiveVendorsController@scoresList'));
            Route::get('scores/export', array('as' => 'vendorManagement.activeVendorList.scores.export', 'uses' => 'ActiveVendorsController@scoresExport'));

            Route::get('scoresWithSubWorkCategories/list', array('as' => 'vendorManagement.activeVendorList.scores.subWorkCategories.list', 'uses' => 'ActiveVendorsController@scoresWithSubWorkCategoriesList'));
            Route::get('scoresWithSubWorkCategories/export', array('as' => 'vendorManagement.activeVendorList.scores.subWorkCategories.export', 'uses' => 'ActiveVendorsController@scoresWithSubWorkCategoriesExport'));

            Route::get('summary/contract-group-categories', array('as' => 'vendorManagement.activeVendorList.summary.contractGroupCategories', 'uses' => 'ActiveVendorsController@contractGroupCategoriesSummary'));
            Route::get('summary/vendor-categories', array('as' => 'vendorManagement.activeVendorList.summary.vendorCategories', 'uses' => 'ActiveVendorsController@vendorCategoriesSummary'));
            Route::get('summary/vendor-work-categories', array('as' => 'vendorManagement.activeVendorList.summary.vendorWorkCategories', 'uses' => 'ActiveVendorsController@vendorWorkCategoriesSummary'));
        });

        Route::group(array('prefix'=>'nominated-watch-list', 'before' => 'vendorManagement.hasPermission:'.\PCK\VendorManagement\VendorManagementUserPermission::TYPE_NOMINATED_WATCH_LIST_VIEW), function()
        {
            Route::get('/', array('as' => 'vendorManagement.nominatedWatchList', 'uses' => 'NominatedWatchListVendorsController@index'));
            Route::get('list', array('as' => 'vendorManagement.nominatedWatchList.list', 'uses' => 'NominatedWatchListVendorsController@list'));

            Route::get('scores/list', array('as' => 'vendorManagement.nominatedWatchList.scores.list', 'uses' => 'NominatedWatchListVendorsController@scoresList'));
            Route::get('scores/export', array('as' => 'vendorManagement.nominatedWatchList.scores.export', 'uses' => 'NominatedWatchListVendorsController@scoresExport'));

            Route::get('scoresWithSubWorkCategories/list', array('as' => 'vendorManagement.nominatedWatchList.scores.subWorkCategories.list', 'uses' => 'NominatedWatchListVendorsController@scoresWithSubWorkCategoriesList'));
            Route::get('scoresWithSubWorkCategories/export', array('as' => 'vendorManagement.nominatedWatchList.scores.subWorkCategories.export', 'uses' => 'NominatedWatchListVendorsController@scoresWithSubWorkCategoriesExport'));

            Route::get('summary/contract-group-categories', array('as' => 'vendorManagement.nominatedWatchList.summary.contractGroupCategories', 'uses' => 'NominatedWatchListVendorsController@contractGroupCategoriesSummary'));
            Route::get('summary/vendor-categories', array('as' => 'vendorManagement.nominatedWatchList.summary.vendorCategories', 'uses' => 'NominatedWatchListVendorsController@vendorCategoriesSummary'));
            Route::get('summary/vendor-work-categories', array('as' => 'vendorManagement.nominatedWatchList.summary.vendorWorkCategories', 'uses' => 'NominatedWatchListVendorsController@vendorWorkCategoriesSummary'));

            Route::get('{vendorId}/vendor-performance-evaluations/cycle/{cycleId}/evaluations', array( 'as' => 'vendorManagement.nominatedWatchList.cycleEvaluations', 'uses' => 'NominatedWatchListVendorsController@vendorPerformanceEvaluationCycleEvaluations' ));
            Route::get('{vendorId}/vendor-performance-evaluations/evaluation/{evaluationId}/forms', array( 'as' => 'vendorManagement.nominatedWatchList.forms', 'uses' => 'NominatedWatchListVendorsController@evaluationForms' ));
            Route::get('{vendorId}/vendor-performance-evaluations/evaluation-form/{companyEvaluationFormId}/evaluator-log', array( 'as' => 'vendorManagement.nominatedWatchList.form.evaluatorLog', 'uses' => 'NominatedWatchListVendorsController@evaluationFormEvaluationEvaluatorLog' ));
            Route::get('{vendorId}/vendor-performance-evaluations/evaluation-form/{companyEvaluationFormId}/verifier-log', array( 'as' => 'vendorManagement.nominatedWatchList.form.verifierLog', 'uses' => 'NominatedWatchListVendorsController@evaluationFormEvaluationVerifierLog' ));
            Route::get('{vendorId}/vendor-performance-evaluations/evaluation-form/{companyEvaluationFormId}/edit-log', array( 'as' => 'vendorManagement.nominatedWatchList.form.editLog', 'uses' => 'NominatedWatchListVendorsController@evaluationFormEvaluationEditLog' ));
            Route::get('{vendorId}/vendor-performance-evaluations/evaluation-form/{companyEvaluationFormId}/edit-log/{editLogId}/edit-details-log', array( 'as' => 'vendorManagement.nominatedWatchList.form.editDetailsLog', 'uses' => 'NominatedWatchListVendorsController@evaluationFormEvaluationEditDetailsLog' ));
            Route::get('{vendorId}/vendor-performance-evaluations/evaluation-form/{companyEvaluationFormId}/export', array( 'as' => 'vendorManagement.nominatedWatchList.form.export', 'uses' => 'NominatedWatchListVendorsController@evaluationFormExport' ));
            Route::get('{vendorId}/vendor-performance-evaluations/evaluation-form/{companyEvaluationFormId}/form-information', array( 'as' => 'vendorManagement.nominatedWatchList.form.information', 'uses' => 'NominatedWatchListVendorsController@evaluationFormInformation' ));
            Route::get('{vendorId}/vendor-performance-evaluations/evaluation-form/{companyEvaluationFormId}/show', array( 'as' => 'vendorManagement.nominatedWatchList.form.show', 'uses' => 'NominatedWatchListVendorsController@evaluationForm' ));
            Route::get('{vendorId}/vendor-performance-evaluations/evaluation-form/{companyEvaluationFormId}/attachments', array( 'as' => 'vendorManagement.nominatedWatchList.form.attachments', 'uses' => 'NominatedWatchListVendorsController@evaluationFormAttachments' ));

            Route::group(array('prefix'=>'nominee/{vendorId}', 'before' => 'vendorManagement.hasPermission:'.\PCK\VendorManagement\VendorManagementUserPermission::TYPE_NOMINATED_WATCH_LIST_EDIT), function()
            {
                Route::get('edit', array('as' => 'vendorManagement.nominatedWatchList.edit', 'uses' => 'NominatedWatchListVendorsController@edit'));
                Route::post('/', array('as' => 'vendorManagement.nominatedWatchList.update', 'uses' => 'NominatedWatchListVendorsController@update'));
            });
        });

        Route::group(array('prefix'=>'watch-list', 'before' => 'vendorManagement.hasPermission:'.\PCK\VendorManagement\VendorManagementUserPermission::TYPE_WATCH_LIST_VIEW), function()
        {
            Route::get('/', array('as' => 'vendorManagement.watchList.index', 'uses' => 'WatchListVendorsController@index'));
            Route::get('list', array('as' => 'vendorManagement.watchList.ajax.list', 'uses' => 'WatchListVendorsController@list'));

            Route::group(array('prefix'=>'vendor/{vendorId}', 'before' => 'vendorManagement.hasPermission:'.\PCK\VendorManagement\VendorManagementUserPermission::TYPE_WATCH_LIST_EDIT), function()
            {
                Route::get('edit', array('as' => 'vendorManagement.watchList.edit', 'uses' => 'WatchListVendorsController@edit'));
                Route::post('store', array('as' => 'vendorManagement.watchList.update', 'uses' => 'WatchListVendorsController@update'));
            });

            Route::get('scores/list', array('as' => 'vendorManagement.watchList.scores.list', 'uses' => 'WatchListVendorsController@scoresList'));
            Route::get('scores/export', array('as' => 'vendorManagement.watchList.scores.export', 'uses' => 'WatchListVendorsController@scoresExport'));

            Route::get('scoresWithSubWorkCategories/list', array('as' => 'vendorManagement.watchList.scores.subWorkCategories.list', 'uses' => 'WatchListVendorsController@scoresWithSubWorkCategoriesList'));
            Route::get('scoresWithSubWorkCategories/export', array('as' => 'vendorManagement.watchList.scores.subWorkCategories.export', 'uses' => 'WatchListVendorsController@scoresWithSubWorkCategoriesExport'));

            Route::get('summary/contract-group-categories', array('as' => 'vendorManagement.watchList.summary.contractGroupCategories', 'uses' => 'WatchListVendorsController@contractGroupCategoriesSummary'));
            Route::get('summary/vendor-categories', array('as' => 'vendorManagement.watchList.summary.vendorCategories', 'uses' => 'WatchListVendorsController@vendorCategoriesSummary'));
            Route::get('summary/vendor-work-categories', array('as' => 'vendorManagement.watchList.summary.vendorWorkCategories', 'uses' => 'WatchListVendorsController@vendorWorkCategoriesSummary'));

            Route::get('vendor-profile/{vendorProfileId}/remarks', array( 'as' => 'vendorManagement.watchList.vendorProfile.remarks.ajax.list', 'uses' => 'VendorProfilesController@remarks' ));
        });

        Route::group(array('prefix'=>'deactivated-vendor-list', 'before' => 'vendorManagement.hasPermission:'.\PCK\VendorManagement\VendorManagementUserPermission::TYPE_DEACTIVATED_VENDOR_LIST), function()
        {
            Route::get('/', array('as' => 'vendorManagement.deactivatedVendorList', 'uses' => 'DeactivatedVendorsController@index'));
            Route::get('list', array('as' => 'vendorManagement.deactivatedVendorList.ajax.list', 'uses' => 'DeactivatedVendorsController@list'));
        });

        Route::group(array('prefix'=>'unsuccessful-vendor-list', 'before' => 'vendorManagement.hasPermission:'.\PCK\VendorManagement\VendorManagementUserPermission::TYPE_UNSUCCESSFUL_VENDOR_LIST), function()
        {
            Route::get('/', array('as' => 'vendorManagement.unsuccessfulVendorList', 'uses' => 'UnsuccessfulRegisteredVendorsController@index'));
            Route::get('list', array('as' => 'vendorManagement.unsuccessfulVendorList.ajax.list', 'uses' => 'UnsuccessfulRegisteredVendorsController@list'));
        });
    });

    Route::group(array('prefix'=>'users', 'before' => 'superAdminAccessLevel'), function()
    {
        Route::get('', array('as' => 'vendorManagement.users', 'uses' => 'VendorManagementUsersController@index'));
        Route::get('list', array('as' => 'vendorManagement.users.list', 'uses' => 'VendorManagementUsersController@list'));
        Route::post('user/{userId}/update-permission', array('as' => 'vendorManagement.users.updatePermission', 'uses' => 'VendorManagementUsersController@updatePermission'));
        Route::post('update-permissions', array('as' => 'vendorManagement.users.updatePermissions', 'uses' => 'VendorManagementUsersController@updatePermissions'));
        Route::get('permissions-list', array('as' => 'vendorManagement.users.permissionsList', 'uses' => 'VendorManagementUsersController@permissionsList'));
        Route::get('tags-list', array('as' => 'vendorManagement.users.tags.list', 'uses' => 'VendorManagementUsersController@getTagList'));
        Route::post('batch-update-permissions', array('as' => 'vendorManagement.users.batchUpdatePermissions', 'uses' => 'VendorManagementUsersController@batchUpdatePermissions'));
    });

    Route::get('vendor/{vendorId}/getVendorProfile', ['as' => 'vendorManagement.vendorProfile.get', 'uses' => 'ConsultantManagementController@vendorProfileInfo']);
});