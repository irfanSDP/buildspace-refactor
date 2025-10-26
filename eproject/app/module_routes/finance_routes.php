<?php

Route::group(array( 'before' => 'moduleAccess:' . \PCK\ModulePermission\ModulePermission::MODULE_ID_FINANCE, 'prefix' => 'finance/claim-certificate' ), function()
{
    Route::get('/', array( 'as' => 'finance.claim-certificate', 'uses' => 'ClaimCertificatePaymentController@list' ));
    Route::get('list', array( 'as' => 'finance.claim-certificate.data', 'uses' => 'ClaimCertificatePaymentController@getClaimCertificateList' ));
    Route::get('{claimCertificateId}/send/log', array( 'as' => 'finance.claim-certificate.email.send.log', 'uses' => 'ClaimCertificatePaymentController@sendCertificateLog' ));
    Route::get('{claimCertificateId}/print/log', array( 'as' => 'finance.claim-certificate.print.log', 'uses' => 'ClaimCertificatePaymentController@certificatePrintLog' ));
    Route::get('{claimCertificateId}/payment/notification', array( 'as' => 'finance.claim-certificate.payment.notification', 'uses' => 'ClaimCertificatePaymentController@sendPaymentCollectionNotification' ));
    Route::get('{claimCertificateId}/payment/notification/log', array( 'as' => 'finance.claim-certificate.payment.notification.log', 'uses' => 'ClaimCertificatePaymentController@paymentCollectionNotificationLog' ));
    Route::get('{claimCertificateId}/accountingReport/export/lastSelectedOptions', array('as' => 'finance.claim-certificate.account.report.export.last.selected.options.get', 'uses' => 'ClaimCertificatePaymentController@getExportAccountingReportLastSelectedOptions'));
    Route::get('{claimCertificateId}/accountingReport/export/logs/get', array('as' => 'finance.claim-certificate.account.report.export.logs.get', 'uses' => 'ClaimCertificatePaymentController@getExportAccountingReportLogs'));
    Route::get('{claimCertificateId}/accountingReport/export/log/{logId}/details/get', array('as' => 'finance.claim-certificate.account.report.export.log.details.get', 'uses' => 'ClaimCertificatePaymentController@getExportAccountingReportLogDetails'));

    Route::get('projects', array( 'as' => 'finance.claim-certificate.projects', 'uses' => 'ClaimCertificatePaymentController@listProjects' ));
    Route::get('projects/getList', array( 'as' => 'finance.claim-certificate.projects.getList', 'uses' => 'ClaimCertificatePaymentController@getProjectsList' ));
    Route::get('projects/exportReport', array( 'as' => 'finance.claim-certificate.projects.exportReport', 'uses' => 'ClaimCertificatePaymentController@exportReport' ));
    Route::get('projects/exportReportWithDebitCreditNotes', array( 'as' => 'finance.claim-certificate.projects.exportReport.with.creditDebitNotes', 'uses' => 'ClaimCertificatePaymentController@exportReportWithCreditDebitNotes' ));

    Route::group(array( 'before' => 'moduleEditorAccess:' . \PCK\ModulePermission\ModulePermission::MODULE_ID_FINANCE ), function()
    {
        Route::get('{claimCertificateId}/claimCertificatePaymentAmounts/get', [ 'as' => 'finance.claim-certificate.payment.amounts.get', 'uses' => 'ClaimCertificatePaymentController@getClaimCertificatePaymentAmounts' ]);
        Route::get('{claimCertificateId}/payments/get', [ 'as' => 'finance.claim-certificate.payments.get', 'uses' => 'ClaimCertificatePaymentController@getClaimCertificatePayments' ]);
        Route::post('{claimCertificateId}', array( 'as' => 'finance.claim-certificate.payment.store', 'uses' => 'ClaimCertificatePaymentController@store' ));
        Route::get('{claimCertificateId}/print', array( 'as' => 'finance.claim-certificate.print', 'uses' => 'ClaimCertificatePaymentController@printClaimCertificate' ));
        Route::get('{claimCertificateId}/send', array( 'as' => 'finance.claim-certificate.email.send', 'uses' => 'ClaimCertificatePaymentController@sendCertificate' ));

        Route::get('{claimCertificateId}/send/log/count', array( 'as' => 'finance.claim-certificate.sent.log.count.get', 'uses' => 'ClaimCertificatePaymentController@getUpdatedSentClaimCertificateLogCount'));
        Route::get('{claimCertificateId}/print/log/count', array( 'as' => 'finance.claim-certificate.print.log.count.get', 'uses' => 'ClaimCertificatePaymentController@getUpdatedPrintLogCount'));

        Route::get('{claimCertificateId}/accountingReport/validate', array('as' => 'finance.claim-certificate.account.report.validate', 'uses' => 'ClaimCertificatePaymentController@validateAccountingReport'));
        Route::get('{claimCertificateId}/accountingReport/export', array('as' => 'finance.claim-certificate.account.report.export', 'uses' => 'ClaimCertificatePaymentController@exportAccountingReport'));
        Route::get('{claimCertificateId}/accountingReport/export/log/count', array('as' => 'finance.claim-certificate.account.report.export.log.count.get', 'uses' => 'ClaimCertificatePaymentController@getUpdatedAccountingExportLogCount'));

        Route::get('{claimCertificteId}/claimCertificate/invoiceInformation/get', [ 'as' => 'finance.claim-certificate.invoice.information.get', 'uses' => 'ClaimCertificatePaymentController@getClaimCertificateInvoiceInformation' ]);
        Route::post('{claimCertificateId}/claimCertificate/invoiceInformation', array('as' => 'finance.claim-certificate.invoce.information.store', 'uses' => 'ClaimCertificatePaymentController@claimCertificateInvoiceInformationStore'));

        Route::get('{claimCertificateId}/claimCertificate/payment/notification/updatedLogCount', array( 'as' => 'finance.claim-certificate.payment.notification.log.count.get', 'uses' => 'ClaimCertificatePaymentController@getUpdatePaymentNotificationLogCount' ));
    });

    Route::group(array('before' => 'moduleAccess:' . \PCK\ModulePermission\ModulePermission::MODULE_ID_FINANCE ), function()
    {
        Route::get('project/{projectId}/claimRevision/{claimRevisionId}/contractorClaims/invoices/list', ['as' => 'finance.contractor.claims.invoices.list', 'uses' => 'SubmitClaimsController@invoiceAttachmentList']);
    });
});