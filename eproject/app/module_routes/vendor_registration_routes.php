<?php

Route::group(['prefix' => 'vendor_registration', 'before' => 'systemModule.vendorManagement.enabled'], function() {
    Route::group(['prefix' => 'forms_library', 'before' => 'vendorManagement.hasPermission:'.\PCK\VendorManagement\VendorManagementUserPermission::TYPE_FORM_TEMPLATES], function() {
        Route::get('/', ['as' => 'vendor.registration.forms.library.index', 'uses' => 'VendorRegistrationsController@index']);
        Route::get('/getVendorRegistrationForms', ['as' => 'vendor.registration.forms.get', 'uses' => 'VendorRegistrationsController@getVendorRegistrationForms']);
    });

    Route::group(['prefix' => 'form_mappings', 'before' => 'vendorManagement.hasPermission:'.\PCK\VendorManagement\VendorManagementUserPermission::TYPE_FORM_TEMPLATES], function() {
        Route::get('/', ['as' => 'vendor.registration.form.mapping.index', 'uses' => 'VendorRegistrationFormMappingsController@index']);
        Route::get('/getFormMappingRecords', ['as' => 'vendor.registration.form.mappings.get', 'uses' => 'VendorRegistrationFormMappingsController@getFormMappingRecords']);
        Route::get('/getFormSelections', ['as' => 'vendor.registration.form.selections.get', 'uses' => 'VendorRegistrationFormMappingsController@getFormSelections']);
        Route::post('/linkForm', ['as' => 'vendor.registration.form.mapping.form.link', 'uses' => 'VendorRegistrationFormMappingsController@linkForm']);
        
        Route::group(['prefix' => '{mappingId}'], function() {
            Route::delete('/unlinkForm', ['as' => 'vendor.registration.form.mapping.form.unlink', 'uses' => 'VendorRegistrationFormMappingsController@unlinkForm']);
        });
    });

    Route::group(['prefix' => 'payment'], function() {
        Route::group(['before' => 'vendorManagement.isVendor'], function() {
            Route::get('/', ['as' => 'vendor.registration.payment.index', 'uses' => 'VendorRegistrationPaymentsController@index']);
            Route::get('/selectedPaymentMethod', ['as' => 'vendor.registration.payment.method.select', 'uses' => 'VendorRegistrationPaymentsController@selectedPaymentMethod']);

            Route::group(['prefix' => '{paymentId}/attachments', 'before' => 'vendorManagement.payment.isOwner'], function() {
                Route::get('/getAttachmentsList', ['as' => 'vendor.registration.payment.attachements.get', 'uses' => 'VendorRegistrationPaymentsController@getAttachmentsList']);
                Route::post('/update', ['as' => 'vendor.registration.payment.attachements.update', 'uses' => 'VendorRegistrationPaymentsController@attachmentsUpdate']);
                Route::get('/getAttachmentCount', ['as' => 'vendor.registration.payment.attachements.count.get', 'uses' => 'VendorRegistrationPaymentsController@getAttachmentCount']);
            });
        });

        Route::group(['prefix' => 'master-list', 'before' => 'vendorManagement.hasPermission:'.\PCK\VendorManagement\VendorManagementUserPermission::TYPE_PAYMENT], function() {
            Route::get('/', ['as' => 'vendor.registration.payments.master.list.index', 'uses' => 'VendorRegistrationPaymentsController@masterListIndex']);
            Route::get('/getVendorPayments', ['as' => 'vendor.registration.payments.master.list.get', 'uses' => 'VendorRegistrationPaymentsController@getVendorPayments']);
            Route::get('company/{companyId}/payment/{paymentId}/payment-proof', ['as' => 'vendor.registration.payment.proof', 'uses' => 'VendorRegistrationPaymentsController@getPaymentProof']);

            Route::get('payment/{paymentId}/paid/uploads', array( 'as' => 'vendor.registration.payment.paid.uploads', 'uses' => 'VendorRegistrationPaymentsController@getUploadedPaidAttachments'));
            Route::post('payment/{paymentId}/paid/do-upload', array( 'as' => 'vendor.registration.payment.paid.doUpload', 'uses' => 'VendorRegistrationPaymentsController@doUploadPaidAttachments'));
            Route::get('payment/{paymentId}/completed/uploads', array( 'as' => 'vendor.registration.payment.completed.uploads', 'uses' => 'VendorRegistrationPaymentsController@getUploadedCompletedAttachments'));
            Route::post('payment/{paymentId}/completed/do-upload', array( 'as' => 'vendor.registration.payment.completed.doUpload', 'uses' => 'VendorRegistrationPaymentsController@doUploadCompletedAttachments'));
        });
    });
});

Route::group(array( 'prefix' => 'vendor-registration', 'before' => 'systemModule.vendorManagement.enabled' ), function()
{
    Route::group(array('prefix' => 'overview', 'before' => 'vendorManagement.isVendor'), function()
    {
        Route::get('/', array( 'as' => 'vendors.vendorRegistration.index', 'uses' => 'VendorsVendorRegistrationController@index' ));
        Route::get('edit', array( 'as' => 'vendors.vendorRegistration.edit', 'uses' => 'VendorsVendorRegistrationController@edit' ));
        Route::post('update', array( 'before' => 'vendorManagement.vendorRegistration.isDraft', 'as' => 'vendors.vendorRegistration.update', 'uses' => 'VendorsVendorRegistrationController@update' ));
        Route::group(['before' => 'vendorRegistration.canUpdateOrRenewCheck'], function() {
            Route::post('start-update', array( 'as' => 'vendors.vendorRegistration.startUpdate', 'uses' => 'VendorsVendorRegistrationController@startUpdate' ));
            Route::post('start-renewal', array( 'as' => 'vendors.vendorRegistration.startRenewal', 'uses' => 'VendorsVendorRegistrationController@startRenewal' ));
        });
        Route::post('discard-draft-revision', array( 'as' => 'vendors.vendorRegistration.discardDraftRevision', 'uses' => 'VendorsVendorRegistrationController@discardDraftRevision' ));

        Route::group(['prefix' => 'vendor-group', 'before' => 'vendorManagement.canChangeVendorGroup'], function() {
            Route::get('edit', ['as' => 'vendors.vendorRegistration.vendorGroup.edit', 'uses' => 'VendorsVendorRegistrationController@vendorGroupEdit']);
            Route::post('update', ['as' => 'vendors.vendorRegistration.vendorGroup.update', 'uses' => 'VendorsVendorRegistrationController@vendorGroupUpdate']);
        });

        Route::group(array( 'prefix' => 'project-track-record' ), function()
        {
            Route::get('/', array( 'as' => 'vendors.vendorRegistration.projectTrackRecord', 'uses' => 'ProjectTrackRecordController@index' ));
            Route::get('create', array( 'as' => 'vendors.vendorRegistration.projectTrackRecord.create', 'uses' => 'ProjectTrackRecordController@create' ));
            Route::post('/', array( 'as' => 'vendors.vendorRegistration.projectTrackRecord.store', 'uses' => 'ProjectTrackRecordController@store' ));
            Route::get('{trackRecordProjectId}', array('before' => 'vendorManagement.projectTrackRecord.isOwner', 'as' => 'vendors.vendorRegistration.projectTrackRecord.edit', 'uses' => 'ProjectTrackRecordController@edit' ));
            Route::post('{trackRecordProjectId}', array('before' => 'vendorManagement.projectTrackRecord.isOwner', 'as' => 'vendors.vendorRegistration.projectTrackRecord.update', 'uses' => 'ProjectTrackRecordController@update' ));
            Route::delete('{trackRecordProjectId}', array('before' => 'vendorManagement.projectTrackRecord.isOwner', 'as' => 'vendors.vendorRegistration.projectTrackRecord.destroy', 'uses' => 'ProjectTrackRecordController@destroy' ));
            Route::get('{trackRecordProjectId}/getAttachmentsList', array('before' => 'vendorManagement.projectTrackRecord.isOwner', 'as' => 'vendors.vendorRegistration.projectTrackRecord.attachments.get', 'uses' => 'ProjectTrackRecordController@getAttachmentsList' ));
        });

        Route::group(array( 'prefix' => 'supplier-credit-facilities' ), function()
        {
            Route::get('/', array( 'as' => 'vendors.vendorRegistration.supplierCreditFacilities', 'uses' => 'SupplierCreditFacilitiesController@index' ));
            Route::get('create', array( 'as' => 'vendors.vendorRegistration.supplierCreditFacilities.create', 'uses' => 'SupplierCreditFacilitiesController@create' ));
            Route::post('/', array( 'as' => 'vendors.vendorRegistration.supplierCreditFacilities.store', 'uses' => 'SupplierCreditFacilitiesController@store' ));
            Route::get('{supplierCreditFacilitiesId}', array( 'before' => 'vendorManagement.supplierCreditFacility.isOwner', 'as' => 'vendors.vendorRegistration.supplierCreditFacilities.edit', 'uses' => 'SupplierCreditFacilitiesController@edit' ));
            Route::post('{supplierCreditFacilitiesId}', array( 'before' => 'vendorManagement.supplierCreditFacility.isOwner', 'as' => 'vendors.vendorRegistration.supplierCreditFacilities.update', 'uses' => 'SupplierCreditFacilitiesController@update' ));
            Route::delete('{supplierCreditFacilitiesId}', array( 'before' => 'vendorManagement.supplierCreditFacility.isOwner', 'as' => 'vendors.vendorRegistration.supplierCreditFacilities.destroy', 'uses' => 'SupplierCreditFacilitiesController@destroy' ));
        });

        Route::group(array( 'prefix' => 'company-personnel' ), function()
        {
            Route::get('/', array( 'as' => 'vendors.vendorRegistration.companyPersonnel', 'uses' => 'CompanyPersonnelController@index' ));
            Route::get('create', array( 'as' => 'vendors.vendorRegistration.companyPersonnel.create', 'uses' => 'CompanyPersonnelController@create' ));
            Route::post('/', array( 'as' => 'vendors.vendorRegistration.companyPersonnel.store', 'uses' => 'CompanyPersonnelController@store' ));
            Route::get('{companyPersonnelId}', array( 'before' => 'vendorManagement.companyPersonnel.isOwner', 'as' => 'vendors.vendorRegistration.companyPersonnel.edit', 'uses' => 'CompanyPersonnelController@edit' ));
            Route::post('{companyPersonnelId}', array( 'before' => 'vendorManagement.companyPersonnel.isOwner', 'as' => 'vendors.vendorRegistration.companyPersonnel.update', 'uses' => 'CompanyPersonnelController@update' ));
            Route::delete('{companyPersonnelId}', array( 'before' => 'vendorManagement.companyPersonnel.isOwner', 'as' => 'vendors.vendorRegistration.companyPersonnel.destroy', 'uses' => 'CompanyPersonnelController@destroy' ));
            Route::get('{companyPersonnelId}/getAttachmentsList', array( 'before' => 'vendorManagement.companyPersonnel.isOwner', 'as' => 'vendors.vendorRegistration.companyPersonnel.attachments.get', 'uses' => 'CompanyPersonnelController@getAttachmentsList' ));

            Route::post('uploads/directors', array( 'as' => 'vendors.vendorRegistration.companyPersonnel.uploads.directors', 'uses' => 'CompanyPersonnelController@directorsUpload' ));
            Route::post('uploads/shareholder', array( 'as' => 'vendors.vendorRegistration.companyPersonnel.uploads.shareholder', 'uses' => 'CompanyPersonnelController@shareholdersUpload' ));
            Route::post('uploads/company-head', array( 'as' => 'vendors.vendorRegistration.companyPersonnel.uploads.companyHead', 'uses' => 'CompanyPersonnelController@companyHeadsUpload' ));

            Route::get('downloads/directors', array( 'as' => 'vendors.vendorRegistration.companyPersonnel.downloads.directors', 'uses' => 'CompanyPersonnelController@getDirectorsDownload' ));
            Route::get('downloads/shareholders', array( 'as' => 'vendors.vendorRegistration.companyPersonnel.downloads.shareholders', 'uses' => 'CompanyPersonnelController@getShareholdersDownload' ));
            Route::get('downloads/company-heads', array( 'as' => 'vendors.vendorRegistration.companyPersonnel.downloads.companyHeads', 'uses' => 'CompanyPersonnelController@getCompanyHeadsDownload' ));
        });
    });

    Route::get('overview/project-track-record/{trackRecordProjectId}/downloads', array('before' => 'vendorManagement.projectTrackRecord.isVendorManagementUserOrOwner', 'as' => 'vendors.vendorRegistration.projectTrackRecord.downloads.get', 'uses' => 'ProjectTrackRecordController@getDownloadList' ));
    Route::get('overview/supplier-credit-facilities/{supplierCreditFacilitiesId}/getAttachmentsList', array( 'before' => 'vendorManagement.supplierCreditFacility.isVendorManagementUserOrOwner', 'as' => 'vendors.vendorRegistration.supplierCreditFacilities.attachments.get', 'uses' => 'SupplierCreditFacilitiesController@getAttachmentsList' ));

    Route::group(['prefix' => 'vendor-details'], function()
    {
        Route::get('', ['as' => 'vendor.registration.details.edit', 'uses' => 'VendorsVendorRegistrationDetailsController@edit']);
        Route::post('', ['as' => 'vendor.registration.details.update', 'uses' => 'VendorsVendorRegistrationDetailsController@update']);
        Route::get('/{companyId}/getActionLogs', ['as' => 'vendor.registration.details.action.logs.get', 'uses' => 'VendorsVendorRegistrationDetailsController@getActionLogs']);
    });

    Route::group(['prefix' => '{companyId}/attachment/field/{field}', 'before' => 'vendorManagment.vendorRegistration.canView'], function()
    {
        Route::get('/getAttachmentCount', ['as' => 'vendor.registration.details.attachements.count.get', 'uses' => 'VendorsVendorRegistrationDetailsController@getAttachmentCount']);
        Route::get('/getAttachmentsList', ['as' => 'vendor.registration.details.attachements.get', 'uses' => 'VendorsVendorRegistrationDetailsController@getAttachmentsList']);
        Route::post('/update', ['as' => 'vendor.registration.details.attachements.update', 'uses' => 'VendorsVendorRegistrationDetailsController@attachmentsUpdate']);
    });

    Route::group(['prefix' => 'registration'], function()
    {
        Route::get('/vendorRegistrationForm', ['before' => 'vendorManagement.isVendor', 'as' => 'vendor.registration.form.show', 'uses' => 'FormObjectMappingController@vendorRegistrationFormShow']);
    });

    Route::group(array( 'prefix' => 'pre-qualification', 'before' => 'vendorManagement.isVendor'), function()
    {
        Route::get('/', array( 'as' => 'vendors.vendorPreQualification.index', 'uses' => 'VendorsVendorPreQualificationController@index' ));
        Route::get('{formId}/form', array( 'before' => 'vendorManagement.preQualification.isOwner', 'as' => 'vendors.vendorPreQualification.form', 'uses' => 'VendorsVendorPreQualificationController@form' ));
        Route::post('{formId}', array( 'before' => 'vendorManagement.preQualification.isOwner', 'as' => 'vendors.vendorPreQualification.formUpdate', 'uses' => 'VendorsVendorPreQualificationController@formUpdate' ));
    });

    Route::group(array( 'prefix' => 'pre-qualification', 'before' => 'vendorManagement.preQualification.node.isProcessorOrOwner'), function()
    {
        Route::get('item/{nodeId}/uploads', array( 'as' => 'preQualification.node.uploads', 'uses' => 'VendorsVendorPreQualificationController@getUploads'));
        Route::post('item/{nodeId}/do-upload', array( 'as' => 'preQualification.node.doUpload', 'uses' => 'VendorsVendorPreQualificationController@doUpload'));
        Route::get('item/{nodeId}/downloads', array( 'as' => 'preQualification.node.downloads', 'uses' => 'VendorsVendorPreQualificationController@getDownloads'));
    });

    Route::group(['prefix' => 'section/{sectionId}', 'before' => 'vendorManagement.isVendor'], function()
    {
        Route::post('applicability/toggle', ['as' => 'section.applicability.toggle', 'uses' => 'VendorsVendorRegistrationController@toggleSectionApplicability']);
    });
});