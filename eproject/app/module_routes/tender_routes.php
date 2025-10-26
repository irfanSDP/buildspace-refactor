<?php

Route::group(array( 'prefix' => 'tenders', 'before' => 'checkTenderAccessLevelPermission' ), function()
{
    Route::get('/', array( 'as' => 'projects.tender.index', 'uses' => 'ProjectTendersController@index' ));
    
    Route::group(array( 'prefix' => 'show/{tenderId}' ), function()
    {
        Route::get('/', array( 'before' => 'checkpoint', 'as' => 'projects.tender.show', 'uses' => 'ProjectTendersController@show' ));

        Route::get('export_lot_tender_info_excel', array('as' => 'projects.tender.lot.tenderer.info.excel.export', 'uses' => 'ProjectTendersController@exportListOfTendererInfoToExcel'));
        
        Route::get('get_contractors_commitment_status_log', array( 'as' => 'projects.tender.get_contractors_commitment_status_log', 'uses' => 'ProjectTendersController@getContractorsCommitmentStatusLog' ));

        Route::post('update_rot_budget', array( 'as' => 'projects.tender.update_rot_budget', 'uses' => 'ProjectTendersController@updateROTBudget' ));

        Route::get('getListOfContractors', array( 'as' => 'list.of.contractors.get', 'uses' => 'ProjectTendersController@getListOfContractors' ));

        Route::get('getListOfVMContractors', array( 'as' => 'list.of.vm.contractors.get', 'uses' => 'ProjectTendersController@getListOfVMContractors' ));

        Route::get('open_tender/{form}', array( 'as' => 'projects.tender.open_tender.get', 'uses' => 'ProjectTendersController@showOpenTender' ));
        Route::put('update_open_tender_info_page', array( 'as' => 'projects.tender.update_open_tender_info_page', 'uses' => 'ProjectTendersController@updateOpenTenderInfoPage' ));
        Route::put('update_open_tender_tender_requirements', array( 'as' => 'projects.tender.update_open_tender_tender_requirements', 'uses' => 'ProjectTendersController@updateOpenTenderRequirements' ));
        Route::post('approve_open_tender_info_page/{id}', array( 'as' => 'projects.tender.approve_open_tender_info_page', 'uses' => 'ProjectTendersController@approveOpenTenderInfoPage' ));

        // send email notification to pending contractor in lot form
        Route::post('lot-pending-contractor-email-notification', ['as' => 'open_tender.lot_pending_contractor_email_notification', 'uses' => 'ProjectTendersController@sendEmailNotificationToPendingContractorsForPayment']);

        Route::group(array('prefix' => 'open-tender-person-in-charge'), function() {
            Route::get('create', array( 'as' => 'open-tender-person-in-charge.create', 'uses' => 'OpenTenderPersonInChargeController@create'));
            Route::post('create', array( 'as' => 'open-tender-person-in-charge.create', 'uses' => 'OpenTenderPersonInChargeController@store'));
            Route::get('edit/{id}', array( 'as' => 'open-tender-person-in-charge.edit', 'uses' => 'OpenTenderPersonInChargeController@edit'));
            Route::put('update/{id}', array( 'as' => 'open-tender-person-in-charge.update', 'uses' => 'OpenTenderPersonInChargeController@update' ));
            Route::delete('delete/{id}', array( 'as' => 'open-tender-person-in-charge.delete', 'uses' => 'OpenTenderPersonInChargeController@destroy' ));
        });

        Route::group(array('prefix' => 'open-tender-announcement'), function() {
            Route::get('create', array( 'as' => 'open-tender-announcement.create', 'uses' => 'OpenTenderAnnouncementController@create'));
            Route::post('create', array( 'as' => 'open-tender-announcement.create', 'uses' => 'OpenTenderAnnouncementController@store'));
            Route::get('edit/{id}', array( 'as' => 'open-tender-announcement.edit', 'uses' => 'OpenTenderAnnouncementController@edit'));
            Route::put('update/{id}', array( 'as' => 'open-tender-announcement.update', 'uses' => 'OpenTenderAnnouncementController@update' ));
            Route::delete('delete/{id}', array( 'as' => 'open-tender-announcement.delete', 'uses' => 'OpenTenderAnnouncementController@destroy' ));
        });

        Route::group(array('prefix' => 'open-tender-industry-code'), function() {
            Route::get('create', array( 'as' => 'open-tender-industry-code.create', 'uses' => 'OpenTenderIndustryCodeController@create'));
            Route::post('create', array( 'as' => 'open-tender-industry-code.create', 'uses' => 'OpenTenderIndustryCodeController@store'));
            Route::get('edit/{id}', array( 'as' => 'open-tender-industry-code.edit', 'uses' => 'OpenTenderIndustryCodeController@edit'));
            Route::put('update/{id}', array( 'as' => 'open-tender-industry-code.update', 'uses' => 'OpenTenderIndustryCodeController@update' ));
            Route::delete('delete/{id}', array( 'as' => 'open-tender-industry-code.delete', 'uses' => 'OpenTenderIndustryCodeController@destroy' ));
            Route::get('get_vendor_work_categories', array( 'as' => 'open-tender-industry-code.get_vendor_work_categories', 'uses' => 'OpenTenderIndustryCodeController@getVendorWorkCategoriesOnDropdownSelect'));
        });

        Route::group(array( 'prefix' => 'open-tender-documents'), function()
        {
            Route::get('/', array( 'as' => 'open-tender-documents.index', 'uses' => 'OpenTenderTenderDocumentController@index' ));
            Route::get('create', array( 'as' => 'open-tender-documents.create', 'uses' => 'OpenTenderTenderDocumentController@create' ));
            Route::post('create', array( 'as' => 'open-tender-documents.store', 'uses' => 'OpenTenderTenderDocumentController@store' ));
            Route::get('{id}/show', array( 'as' => 'open-tender-documents.show', 'uses' => 'OpenTenderTenderDocumentController@show' ));
            Route::get('{id}/edit', array( 'as' => 'open-tender-documents.edit', 'uses' => 'OpenTenderTenderDocumentController@edit' ));
            Route::put('{id}/update', array( 'as' => 'open-tender-documents.update', 'uses' => 'OpenTenderTenderDocumentController@update' ));
            Route::delete('{id}/delete', array( 'as' => 'open-tender-documents.delete', 'uses' => 'OpenTenderTenderDocumentController@destroy' ));
            Route::get('{id}/getAttachmentsList', ['as' => 'open-tender-documents.attachements.get', 'uses' => 'OpenTenderTenderDocumentController@getAttachmentsList']);
            Route::delete('{uploadedItemId}/attachmentsDelete/{id}', ['as' => 'open-tender-documents.attachements.delete', 'uses' => 'OpenTenderTenderDocumentController@attachmentDelete']);
        });

        Route::group(array('prefix' => 'company/{companyId}'), function() {
            Route::get('getCompanyDuplicateCompanyPersonnels', array( 'as' => 'company.duplicated.company.personnels.get', 'uses' => 'ProjectTendersController@getCompanyDuplicateCompanyPersonnels' ));
        });

        Route::group(array( 'before' => 'checkROTSubmissionStatus' ), function()
        {
            Route::put('update_rot', array( 'as' => 'projects.tender.update_rot_information', 'uses' => 'ProjectTendersController@updateROTInformation' ));

            Route::put('sync_rot_contractors', array( 'as' => 'projects.tender.rot_selected_contractors', 'uses' => 'ProjectTendersController@syncROTSelectedContractors' ));

            Route::delete('delete_rot_contractor/{contractorId}', array( 'as' => 'projects.tender.delete_rot_contractor', 'uses' => 'ProjectTendersController@deleteROTContractor' ));

            Route::post('recommendation-of-tenderer/forum/init', array( 'as' => 'rot_information.forum.threads.initialise', 'uses' => 'RecommendationOfTenderersController@initiateThread' ));
        });

        Route::put('update_lot', array( 'as' => 'projects.tender.update_lot_information', 'uses' => 'ProjectTendersController@updateLOTInformation' ));

        Route::group(array( 'before' => 'checkLOTSubmissionStatus' ), function()
        {
            Route::put('sync_lot_contractors', array( 'as' => 'projects.tender.lot_selected_contractors', 'uses' => 'ProjectTendersController@syncLOTSelectedContractors' ));

            Route::get('re_enable_lot_contractor/{contractorId}', array( 'as' => 'projects.tender.reenable_lot_contractor', 'uses' => 'ProjectTendersController@reEnableLOTContractor' ));

            Route::delete('delete_lot_contractor/{contractorId}', array( 'as' => 'projects.tender.delete_lot_contractor', 'uses' => 'ProjectTendersController@deleteLOTContractor' ));

            Route::post('list-of-tenderer/forum/init', array( 'as' => 'lot_information.forum.threads.initialise', 'uses' => 'ListOfTenderersController@initiateThread' ));
        });

        Route::group(array( 'before' => 'checkCallingTenderSubmissionStatus' ), function()
        {
            Route::put('update_calling_tender', array( 'as' => 'projects.tender.update_calling_tender_information', 'uses' => 'ProjectTendersController@updateCallingTenderInformation' ));
        });

        Route::post('tender-interview/get', array( 'as' => 'projects.tender.tenderInterview.get', 'uses' => 'TenderInterviewsController@getTenderInterviewData' ));
        Route::post('tender-interview/update', array( 'as' => 'projects.tender.tenderInterview.update', 'uses' => 'TenderInterviewsController@updateTenderInterview' ));
        Route::post('tender-interview/send', array( 'as' => 'projects.tender.tenderInterview.send', 'uses' => 'TenderInterviewsController@sendTenderInterview' ));

        Route::post('tender-reminder/send-reminder-email', array( 'as' => 'projects.tender.reminder.email.send', 'uses' => 'ProjectTendersController@sendTenderReminderEmail' ));
        Route::post('tender-reminder/send', array( 'as' => 'projects.tender.reminder.send', 'uses' => 'ProjectTendersController@sendTenderReminder' ));
        Route::post('tender-reminder/save-draft', array( 'as' => 'projects.tender.reminder.saveDraft', 'uses' => 'ProjectTendersController@saveTenderReminderDraft' ));

        Route::get('tender/acknowledgement-check-enable-status', array( 'as' => 'projects.tender.acknowledgementLetter.checkEnableStatus', 'uses' => 'ProjectTendersController@checkEnableStatus' ));
        Route::post('tender/acknowledgement-save-draft', array( 'as' => 'projects.tender.acknowledgementLetter.saveDraft', 'uses' => 'ProjectTendersController@saveTenderAcknowledgementLetterDraft' ));
    });
});

Route::group(array( 'prefix' => 'open_tenders', 'before' => 'openTenderAccess' ), function()
{
    Route::get('/', array( 'as' => 'projects.openTender.index', 'uses' => 'ProjectOpenTendersController@index' ));

    Route::get('{tenderId}/open_tender_record', array( 'as' => 'projects.openTender.record', 'uses' => 'ProjectOpenTendersController@record' ));

    Route::post('{tenderId}/currentlySelectedTendererSave', [ 'as' => 'projects.openTender.currentlySelectedTenderer.save', 'uses' => 'ProjectOpenTendersController@saveCurrentlySelectedTenderer' ]);

    Route::group(array( 'before' => 'isEditor|allowBusinessUnitOrGCDToAccess' ), function()
    {
        Route::post('{tenderId}/updateSubmitTenderRateRemarks', array( 'as' => 'projects.openTender.submitTenderRate.remarks.update', 'uses' => 'ProjectOpenTendersController@updateSubmitTenderRateRemarks' ));
        Route::post('{tenderId}/updateSubmitTenderRateEarnestMoney', array( 'as' => 'projects.openTender.submitTenderRate.earnestMoney.update', 'uses' => 'ProjectOpenTendersController@updateSubmitTenderRateEarnestMoney' ));

        Route::put('{tenderId}/updateTenderValidityPeriod', array( 'as' => 'projects.openTender.validityPeriod.update', 'uses' => 'ProjectOpenTendersController@updateTenderValidityPeriod' ));
    });

    Route::group(array( 'before' => 'latestTenderOpenStatus' ), function()
    {
        Route::get('tenderer-report', array( 'as' => 'projects.openTender.report', 'uses' => 'ProjectOpenTendersController@tendererReport' ));
        Route::get('tenderer-report/list', array( 'as' => 'projects.openTender.report.list', 'uses' => 'ProjectOpenTendersController@tendererReportList' ));
        Route::get('tenderer-report/export', array( 'as' => 'projects.openTender.report.export', 'uses' => 'ProjectOpenTendersController@exportTendererReport' ));
        Route::get('tenderer-report/company/{companyId}/withdrawn-tenders/list', array( 'as' => 'projects.openTender.report.withdrawnTenders.list', 'uses' => 'ProjectOpenTendersController@tendererReportWithdrawnTendersList' ));
        Route::get('tenderer-report/company/{companyId}/participated-tenders/list', array( 'as' => 'projects.openTender.report.participatedTenders.list', 'uses' => 'ProjectOpenTendersController@tendererReportParticipatedTendersList' ));
        Route::get('tenderer-report/company/{companyId}/ongoing-projects/list', array( 'as' => 'projects.openTender.report.ongoingProjects.list', 'uses' => 'ProjectOpenTendersController@tendererReportOngoingProjectsList' ));
        Route::get('tenderer-report/company/{companyId}/completed-projects/list', array( 'as' => 'projects.openTender.report.completedProjects.list', 'uses' => 'ProjectOpenTendersController@tendererReportCompletedProjectsList' ));
        Route::get('tenderer-report/company/{companyId}/ongoing-projects/total-contract-sums', array( 'as' => 'projects.openTender.report.ongoingProjects.totalContractSums.list', 'uses' => 'ProjectOpenTendersController@tendererReportOngoingProjectsTotalContractSumList' ));
        Route::get('tenderer-report/company/{companyId}/completed-projects/total-contract-sums', array( 'as' => 'projects.openTender.report.completedProjects.totalContractSums.list', 'uses' => 'ProjectOpenTendersController@tendererReportCompletedProjectsTotalContractSumList' ));
    });

    Route::group(array( 'prefix' => 'show/{tenderId}' ), function()
    {
        Route::get('/', array( 'as' => 'projects.openTender.show', 'uses' => 'ProjectOpenTendersController@show' ));
        //Route::get('enable_bidding', array( 'as' => 'projects.openTender.enableBidding', 'uses' => 'ProjectOpenTendersController@enableBidding' ));
        //Route::get('disable_bidding', array( 'as' => 'projects.openTender.disableBidding', 'uses' => 'ProjectOpenTendersController@disableBidding' ));

        Route::group(array( 'before' => 'isEditor|allowBusinessUnitOrGCDToAccess|allowReTender', 'prefix' => 'retender' ), function()
        {
            Route::get('/', array( 'as' => 'projects.openTender.reTender', 'uses' => 'ProjectOpenTendersController@showReTender' ));
            Route::post('/', array( 'uses' => 'ProjectOpenTendersController@postReTender' ));
        });

        Route::get('open_tender_form_export', array('before' => 'openTender.isOpen', 'as' => 'projects.openTender.form.excel.export', 'uses' => 'ProjectOpenTendersController@exportExcelOpenTenderForm'));

        Route::get('open_tender_verifier_logs', array( 'as' => 'projects.openTender.viewOTVerifierLogs', 'uses' => 'ProjectOpenTendersController@viewOTVerifierLogs' ));

        Route::group(array( 'before' => 'isEditor|allowBusinessUnitOrGCDToAccess|checkOpenTenderStatus' ), function()
        {
            Route::get('resend_open_tender_verifier_email/{receiverId}', array( 'as' => 'projects.openTender.resendOTVerifierEmail', 'uses' => 'ProjectOpenTendersController@resendOTVerifierEmail' ));

            Route::get('assign_open_tender_verifiers', array( 'as' => 'projects.openTender.assignOTVerifiers', 'uses' => 'ProjectOpenTendersController@assignOTVerifiersForm' ));
            Route::put('assign_open_tender_verifiers', array( 'uses' => 'ProjectOpenTendersController@processOTVerifiersForm' ));

            Route::post('reassign_open_tender_verifiers', array( 'as' => 'projects.openTender.reassignOTVerifiers', 'uses' => 'ProjectOpenTendersController@reassignOTVerifiers' ));
        });

        Route::get('download_rates_file/{contractorId}', array( 'as' => 'projects.openTender.downloadRatesFile', 'uses' => 'ProjectOpenTendersController@downloadTenderRatesFile' ));
    });

    Route::group([ 'prefix' => '{tenderId}/award_recommendation' ], function()
    {
        Route::get('report/show', [ 'before' => 'checkpoint', 'as' => 'open_tender.award_recommendation.report.show', 'uses' => 'OpenTenderAwardRecommendationController@show' ]);
        Route::get('report/edit', [ 'as' => 'open_tender.award_recommendation.report.edit', 'uses' => 'OpenTenderAwardRecommendationController@edit' ]);
        Route::post('report/save', [ 'as' => 'open_tender.award_recommendation.report.save', 'uses' => 'OpenTenderAwardRecommendationController@save' ]);
        Route::post('report/getReportContents', [ 'as' => 'open_tender.award_recommendation.report.get', 'uses' => 'OpenTenderAwardRecommendationController@getReport' ]);
        Route::post('verifiers/submit', [ 'as' => 'open_tender.award_recommendation.report.verifiers.submit', 'uses' => 'OpenTenderAwardRecommendationController@submit' ]);
        Route::post('verifer/verify', [ 'as' => 'open_tender.award_recommendation.verify', 'uses' => 'VerifierController@verify' ]);
        Route::get('reportLogs/get', [ 'as' => 'open_tender.award_recommendation.report.edit.logs.get', 'uses' => 'OpenTenderAwardRecommendationController@getReportEditLogs' ]);

        Route::get('report/tenderAnalysis/', [ 'as' => 'open_tender.award_recommendation.report.tender_analysis_table.index', 'uses' => 'OpenTenderAwardRecommendationTenderAnalysisController@index' ]);
        Route::get('report/tenderAnalysis/statusOfParticipants', [ 'as' => 'open_tender.award_recommendation.report.tender_analysis_table.statusOfParticipants', 'uses' => 'OpenTenderAwardRecommendationTenderAnalysisController@getStatusOfParticipants' ]);
        Route::get('report/tenderAnalysis/originalTenderSummary', [ 'as' => 'open_tender.award_recommendation.report.tender_analysis_table.originalTenderSummary', 'uses' => 'OpenTenderAwardRecommendationTenderAnalysisController@getOriginalTenderSummary' ]);
        Route::get('report/tenderAnalysis/tenderResubmissionSummary', [ 'as' => 'open_tender.award_recommendation.report.tender_analysis_table.tenderResubmissionSummary', 'uses' => 'OpenTenderAwardRecommendationTenderAnalysisController@getTenderResubmissionSummary' ]);
        Route::post('report/tenderAnalysis/originalTenderSummary/consultantEstimate/update', [ 'as' => 'open_tender.award_recommendation.report.tender_analysis_table.originalTenderSummary.consultantEstimate.update', 'uses' => 'OpenTenderAwardRecommendationTenderAnalysisController@updateConsultantEstimate' ]);
        Route::post('report/tenderAnalysis/originalTenderSummary/budget/update', [ 'as' => 'open_tender.award_recommendation.report.tender_analysis_table.originalTenderSummary.budget.update', 'uses' => 'OpenTenderAwardRecommendationTenderAnalysisController@updateBudget' ]);
        Route::get('report/tenderAnalysis/pteVsAward', [ 'as' => 'open_tender.award_recommendation.report.tender_analysis_table.ptevsaward', 'uses' => 'OpenTenderAwardRecommendationTenderAnalysisController@getPteVsAwardSummary' ]);
        Route::post('report/tenderAnalysis/pteVsAward/update', [ 'as' => 'open_tender.award_recommendation.report.tender_analysis_table.ptevsaward.update', 'uses' => 'OpenTenderAwardRecommendationTenderAnalysisController@updatePteVsAwardSummary' ]);
        Route::get('report/tenderAnalysis/budgetVsAward', [ 'as' => 'open_tender.award_recommendation.report.tender_analysis_table.budgetvsaward', 'uses' => 'OpenTenderAwardRecommendationTenderAnalysisController@getBudgetVsAwardSummary' ]);
        Route::post('report/tenderAnalysis/budgetVsAward/update', [ 'as' => 'open_tender.award_recommendation.report.tender_analysis_table.budgetvsaward.update', 'uses' => 'OpenTenderAwardRecommendationTenderAnalysisController@updateBudgetVsAwardSummary' ]);
        Route::get('report/tenderAnalysis/contractSum', [ 'as' => 'open_tender.award_recommendation.report.tender_analysis_table.contractsum', 'uses' => 'OpenTenderAwardRecommendationTenderAnalysisController@getContractSumSummary' ]);
        Route::get('report/tenderAnalysis/logs', [ 'as' => 'open_tender.award_recommendation.report.tender_analysis_table.logs.get', 'uses' => 'OpenTenderAwardRecommendationTenderAnalysisController@getTenderAnalaysisEditLogs' ]);

        Route::get('report/attachments/', [ 'as' => 'open_tender.award_recommendation.report.attachment.index', 'uses' => 'OpenTenderAwardRecommendationAttachmentsController@index' ]);
        Route::get('report/attachments/get', [ 'as' => 'open_tender.award_recommendation.report.attachment.get', 'uses' => 'OpenTenderAwardRecommendationAttachmentsController@getUploadedFiles' ]);
        Route::post('report/attachments/upload', [ 'as' => 'open_tender.award_recommendation.report.attachment.upload', 'uses' => 'OpenTenderAwardRecommendationAttachmentsController@upload' ]);
        Route::get('report/attachments/download', [ 'as' => 'open_tender.award_recommendation.report.attachment.download', 'uses' => 'OpenTenderAwardRecommendationAttachmentsController@download' ]);
        Route::post('report/attachments/delete', [ 'as' => 'open_tender.award_recommendation.report.attachment.delete', 'uses' => 'OpenTenderAwardRecommendationAttachmentsController@uploadDelete' ]);
    });

    // route to generate queue in order to send files to be processed by BuildSpace
    Route::get('sync_rates_with_buildspace', array( 'before' => 'project.buildspace.contractorRates.canSync', 'as' => 'projects.openTender.syncContractorRatesIntoBuildSpace', 'uses' => 'ProjectOpenTenderBuildSpaceController@syncContractorRatesWithBuildSpace' ));
});

Route::group(array( 'prefix' => 'submit_tenders' ), function()
{
    Route::group(array( 'prefix' => 'show/{tenderId}' ), function()
    {
        Route::get('/', array( 'as' => 'projects.submitTender.rates', 'uses' => 'ProjectTendererTendersController@showSubmitTender' ));

        Route::group(array( 'before' => 'project.stage.callingTender.isOpen' ), function()
        {
            Route::post('/', array( 'uses' => 'ProjectTendererTendersController@saveSubmitTender' ));
            Route::post('tender-rates-information', array( 'as' => 'projects.submitTenderRates.information', 'uses' => 'ProjectTendererTendersController@saveTenderRateInformation' ));
        });

        Route::post('save_attachments', array( 'as' => 'projects.submitTender.saveAttachments', 'uses' => 'ProjectTendererTendersController@saveSubmitTenderAttachments' ));

        Route::get('download_rates_file/{contractorId}', array( 'as' => 'projects.submitTender.downloadRatesFile', 'uses' => 'ProjectTendererTendersController@downloadTenderRatesFile' ));

        Route::get('tender/acknowledgement-check-tender-submission', array( 'as' => 'projects.submitTender.acknowledgementLetter.checkTenderSubmission', 'uses' => 'ProjectTendererTendersController@checkTenderSubmission' ));

        Route::get('tender/acknowledgement-print-draft', array( 'as' => 'projects.submitTender.acknowledgementLetter.printDraft', 'uses' => 'ProjectTendererTendersController@printTenderAcknowledgementLetterDraft' ));
    });
});

Route::group([ 'prefix' => 'questionnaires', 'before' => 'checkTenderQuestionnaireAccessLevelPermission' ], function()
{
    Route::get('', ['as' => 'projects.questionnaires.index', 'uses' => 'ProjectQuestionnairesController@index']);
    Route::get('contractors', ['as' => 'projects.questionnaires.contractors.ajax.list', 'uses' => 'ProjectQuestionnairesController@contractorList']);

    Route::group(['prefix' => '{companyId}'], function(){
        Route::get('show', ['as' => 'projects.questionnaires.show', 'uses' => 'ProjectQuestionnairesController@show']);
        Route::get('questions', ['as' => 'projects.questionnaires.contractor.questions.ajax.list', 'uses' => 'ProjectQuestionnairesController@contractorQuestionList']);
        Route::get('replies', ['as' => 'projects.questionnaires.contractor.replies.ajax.list', 'uses' => 'ProjectQuestionnairesController@contractorReplyList']);
        Route::get('question-create', ['as' => 'projects.questionnaires.question.create', 'before' => 'tenderQuestionnaire.canCreate', 'uses' => 'ProjectQuestionnairesController@questionCreate']);
        Route::get('reply-print', ['as' => 'projects.questionnaires.contractor.reply.print', 'uses' => 'ProjectQuestionnairesController@contractorReplyPrint']);
    });

    Route::group(['prefix' => '{questionId}'], function(){
        Route::get('contractor-attachments', ['as' => 'projects.questionnaires.contractor.attachments', 'uses' => 'ProjectQuestionnairesController@contractorAttachmentList']);
        Route::get('question-show', ['as' => 'projects.questionnaires.question.show', 'uses' => 'ProjectQuestionnairesController@questionShow']);

        Route::group(['before' => 'tenderQuestionnaire.question.canEdit'], function() {
            Route::get('question-edit', ['as' => 'projects.questionnaires.question.edit', 'uses' => 'ProjectQuestionnairesController@questionEdit']);
            Route::delete('question-delete', ['as' => 'projects.questionnaires.question.delete', 'uses' => 'ProjectQuestionnairesController@questionDelete']);
        });
    });

    Route::group(['before' => 'tenderQuestionnaire.canCreate'], function() {
        Route::post('question-store', ['as' => 'projects.questionnaires.question.store', 'uses' => 'ProjectQuestionnairesController@questionStore']);
        Route::post('publish', ['as' => 'projects.questionnaires.publish', 'uses' => 'ProjectQuestionnairesController@publish']);
    });
});
 