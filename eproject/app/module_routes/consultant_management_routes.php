<?php

Route::group(['prefix' => 'consultant-management', 'before' => 'validateConsultantManagementRoles'], function()
{
    Route::group(['prefix' => 'contracts'], function(){
        Route::get('/', ['as' => 'consultant.management.contracts.index', 'uses' => 'ConsultantManagementController@contractIndex']);
        Route::get('list', ['as' => 'consultant.management.contracts.ajax.list', 'uses' => 'ConsultantManagementController@contractList']);
        Route::get('rfp-list', ['as' => 'consultant.management.contracts.rfp.ajax.list', 'uses' => 'ConsultantManagementController@rfpList']);
        Route::get('create', ['as' => 'consultant.management.contracts.contract.create', 'uses' => 'ConsultantManagementController@contractCreate']);
        Route::get('{consultantManagementContractId}/edit', ['as' => 'consultant.management.contracts.contract.edit', 'uses' => 'ConsultantManagementController@contractEdit']);
        Route::get('{consultantManagementContractId}/show', ['as' => 'consultant.management.contracts.contract.show', 'uses' => 'ConsultantManagementController@contractShow']);
        Route::post('store', ['as' => 'consultant.management.contracts.contract.store', 'uses' => 'ConsultantManagementController@contractStore']);
        Route::delete('{consultantManagementContractId}/delete', ['as' => 'consultant.management.contracts.contract.delete', 'uses' => 'ConsultantManagementController@contractDelete']);
        
        Route::post('contract-number-gen', ['as' => 'consultant.management.contracts.generate.contract.number', 'uses' => 'ConsultantManagementController@generateContractNumber']);
    });

    Route::group(['prefix' => 'todo'], function(){
        Route::get('{id}/roc', ['as' => 'consultant.management.todo.list.roc', 'uses' => 'ConsultantManagementController@recommendationOfConsultantTodoList']);
        Route::get('{id}/loc', ['as' => 'consultant.management.todo.list.loc', 'uses' => 'ConsultantManagementController@listOfConsultantTodoList']);
        Route::get('{id}/calling_rfp', ['as' => 'consultant.management.todo.list.calling_rfp', 'uses' => 'ConsultantManagementController@callingRfpTodoList']);
        Route::get('{id}/open_rfp', ['as' => 'consultant.management.todo.list.open_rfp', 'uses' => 'ConsultantManagementController@openRfpTodoList']);
        Route::get('{id}/rfp_resubmission', ['as' => 'consultant.management.todo.list.rfp_resubmission', 'uses' => 'ConsultantManagementController@rfpResubmissionTodoList']);
        Route::get('{id}/approval_document', ['as' => 'consultant.management.todo.list.approval_document', 'uses' => 'ConsultantManagementController@approvalDocumentTodoList']);
        Route::get('{id}/loa', ['as' => 'consultant.management.todo.list.loa', 'uses' => 'ConsultantManagementController@letterOfAwardTodoList']);
    });

    Route::group(['prefix' => 'phase'], function(){
        Route::get('{consultantManagementContractId}/create', ['as' => 'consultant.management.contracts.phase.create', 'uses' => 'ConsultantManagementController@phaseCreate']);
        Route::get('{consultantManagementSubsidiaryId}/edit', ['as' => 'consultant.management.contracts.phase.edit', 'uses' => 'ConsultantManagementController@phaseEdit']);
        Route::post('store', ['as' => 'consultant.management.contracts.phase.store', 'uses' => 'ConsultantManagementController@phaseStore']);
        Route::delete('{consultantManagementSubsidiaryId}/delete', ['as' => 'consultant.management.contracts.phase.delete', 'uses' => 'ConsultantManagementController@phaseDelete']);

        Route::get('product-types/{developementTypeId}', ['as' => 'consultant.management.contracts.phase.product.type.list', 'uses' => 'ConsultantManagementController@productTypeList']);
    });

    Route::group(['prefix' => 'phase-general-attachment/{id}/{field}'], function(){
        Route::get('list', ['as' => 'consultant.management.contracts.phase.general.attachment.list', 'uses' => 'ConsultantManagementController@phaseGeneralAttachmentList']);
        Route::get('count', ['as' => 'consultant.management.contracts.phase.general.attachment.count', 'uses' => 'ConsultantManagementController@phaseGeneralAttachmentCount']);
        Route::post('store', ['as' => 'consultant.management.contracts.phase.general.attachment.store', 'uses' => 'ConsultantManagementController@phaseGeneralAttachmentStore']);
        Route::delete('{fileId}/delete', ['as' => 'consultant.management.contracts.phase.general.attachment.delete', 'uses' => 'ConsultantManagementController@phaseGeneralAttachmentDelete']);
    });

    Route::group(['prefix' => 'loc-attachment/{id}/{field}'], function(){
        Route::get('list', ['as' => 'consultant.management.list.of.consultant.attachment.list', 'uses' => 'ConsultantManagementListOfConsultantController@attachmentList']);
        Route::get('count', ['as' => 'consultant.management.list.of.consultant.attachment.count', 'uses' => 'ConsultantManagementListOfConsultantController@attachmentCount']);
        Route::post('store', ['as' => 'consultant.management.list.of.consultant.attachment.store', 'uses' => 'ConsultantManagementListOfConsultantController@attachmentStore']);
        Route::delete('{fileId}/delete', ['as' => 'consultant.management.list.of.consultant.attachment.delete', 'uses' => 'ConsultantManagementListOfConsultantController@attachmentDelete']);
    });

    Route::group(['prefix' => 'loa-templates'], function(){
        Route::get('/', ['as' => 'consultant.management.loa.templates.index', 'uses' => 'ConsultantManagement\LetterOfAwardController@templateIndex']);
        Route::get('list', ['as' => 'consultant.management.loa.templates.ajax.list', 'uses' => 'ConsultantManagement\LetterOfAwardController@templateList']);
        Route::get('create', ['as' => 'consultant.management.loa.templates.create', 'uses' => 'ConsultantManagement\LetterOfAwardController@templateCreate']);
        Route::get('{id}/edit', ['as' => 'consultant.management.loa.templates.edit', 'uses' => 'ConsultantManagement\LetterOfAwardController@templateEdit']);
        Route::get('{id}/letterhead-edit', ['as' => 'consultant.management.loa.templates.letterhead.edit', 'uses' => 'ConsultantManagement\LetterOfAwardController@templateLetterheadEdit']);
        Route::get('{id}/clause-edit', ['as' => 'consultant.management.loa.templates.clause.edit', 'uses' => 'ConsultantManagement\LetterOfAwardController@templateClauseEdit']);
        Route::get('{id}/signatory-edit', ['as' => 'consultant.management.loa.templates.signatory.edit', 'uses' => 'ConsultantManagement\LetterOfAwardController@templateSignatoryEdit']);

        Route::get('{id}/show', ['as' => 'consultant.management.loa.templates.show', 'uses' => 'ConsultantManagement\LetterOfAwardController@templateShow']);
        Route::get('{id}/preview', ['as' => 'consultant.management.loa.templates.preview', 'uses' => 'ConsultantManagement\LetterOfAwardController@templatePreview']);
        Route::post('store', ['as' => 'consultant.management.loa.templates.store', 'uses' => 'ConsultantManagement\LetterOfAwardController@templateStore']);
        Route::post('letterhead-store', ['as' => 'consultant.management.loa.templates.letterhead.store', 'uses' => 'ConsultantManagement\LetterOfAwardController@templateLetterheadStore']);
        Route::post('signatory-store', ['as' => 'consultant.management.loa.templates.signatory.store', 'uses' => 'ConsultantManagement\LetterOfAwardController@templateSignatoryStore']);
        Route::post('clause-store', ['as' => 'consultant.management.loa.templates.clause.store', 'uses' => 'ConsultantManagement\LetterOfAwardController@templateClauseStore']);

        Route::delete('{id}/delete', ['as' => 'consultant.management.loa.templates.delete', 'uses' => 'ConsultantManagement\LetterOfAwardController@templateDelete']);
    });

    Route::group(['prefix' => 'reports'], function(){
        Route::get('/', ['as' => 'consultant.management.reports.index', 'uses' => 'ConsultantManagementReportController@index']);
        Route::post('list', ['as' => 'consultant.management.reports.ajax.list', 'uses' => 'ConsultantManagementReportController@list']);
        Route::post('export-to-excel', ['as' => 'consultant.management.reports.export.excel', 'uses' => 'ConsultantManagementReportController@exportExcel']);
    });

    Route::group(['prefix' => 'consultant-payments', 'before' => 'validateConsultantPaymentsUserPermission'], function(){
        Route::get('/', ['as' => 'consultant.management.consultant.payments.index', 'uses' => 'ConsultantManagementConsultantPaymentController@index']);
        Route::get('list', ['as' => 'consultant.management.consultant.payments.list', 'uses' => 'ConsultantManagementConsultantPaymentController@list']);
        Route::get('{id}/show', ['as' => 'consultant.management.consultant.payments.show', 'uses' => 'ConsultantManagementConsultantPaymentController@show']);
        Route::get('{id}/consultant-list', ['as' => 'consultant.management.consultant.payments.consultant.list', 'uses' => 'ConsultantManagementConsultantPaymentController@consultantList']);
        Route::get('{rfpId}/{id}/consultant-details', ['as' => 'consultant.management.consultant.payments.consultant.details', 'uses' => 'ConsultantManagementConsultantPaymentController@consultantDetails']);
    });

    Route::group(['prefix' => 'loa-running-number'], function(){
        Route::get('/', ['as' => 'consultant.management.loa.running.number.index', 'uses' => 'ConsultantManagement\LetterOfAwardController@runningNumberIndex']);
        Route::get('list', ['as' => 'consultant.management.loa.running.number.list', 'uses' => 'ConsultantManagement\LetterOfAwardController@runningNumberList']);
        Route::get('{id}/show', ['as' => 'consultant.management.loa.running.number.show', 'uses' => 'ConsultantManagement\LetterOfAwardController@runningNumberShow']);
        Route::get('create', ['as' => 'consultant.management.loa.running.number.create', 'uses' => 'ConsultantManagement\LetterOfAwardController@runningNumberCreate']);
        Route::get('{id}/edit', ['as' => 'consultant.management.loa.running.number.edit', 'uses' => 'ConsultantManagement\LetterOfAwardController@runningNumberEdit']);
        Route::post('store', ['as' => 'consultant.management.loa.running.number.store', 'uses' => 'ConsultantManagement\LetterOfAwardController@runningNumberStore']);
    });

    Route::group(['prefix' => 'user-management/{consultantManagementContractId}'], function(){
        Route::get('/', ['as' => 'consultant.management.user.management.index', 'uses' => 'ConsultantManagementController@userManagementIndex']);

        Route::get('validate-roc-viewer-remove/{userId}', ['as' => 'consultant.management.user.management.roc.viewer.remove.validate', 'uses' => 'ConsultantManagementController@validateRemoveRecommendationOfConsultantViewer']);
        Route::get('validate-roc-editor-remove/{userId}', ['as' => 'consultant.management.user.management.roc.editor.remove.validate', 'uses' => 'ConsultantManagementController@validateRemoveRecommendationOfConsultantEditor']);
        Route::post('store', ['as' => 'consultant.management.user.management.store', 'uses' => 'ConsultantManagementController@userManagementStore']);

    });

    Route::group(['prefix' => 'company-role-assignment/{consultantManagementContractId}'], function(){
        Route::get('/', ['as' => 'consultant.management.company.role.assignment.index', 'uses' => 'ConsultantManagementController@companyRoleAssignmentIndex']);
        Route::post('store', ['as' => 'consultant.management.company.role.assignment.store', 'uses' => 'ConsultantManagementController@companyRoleAssignmentStore']);
        Route::get('locValidateCompany', ['as' => 'consultant.management.company.role.assignment.loc.validate', 'uses' => 'ConsultantManagementController@validateCompanyRoleLOCAssignment']);
        Route::get('callingRfpValidateCompany', ['as' => 'consultant.management.company.role.assignment.calling.rfp.validate', 'uses' => 'ConsultantManagementController@validateCompanyRoleCallingRfp']);
        Route::get('logs', ['as' => 'consultant.management.company.role.assignment.logs', 'uses' => 'ConsultantManagementController@companyRoleAssignmentLogs']);
    });

    Route::group(['prefix' => 'attachment-settings/{consultantManagementContractId}'], function(){
        Route::get('/', ['as' => 'consultant.management.attachment.settings.index', 'uses' => 'ConsultantManagementController@attachmentSettingIndex']);
        Route::get('list', ['as' => 'consultant.management.attachment.settings.ajax.list', 'uses' => 'ConsultantManagementController@attachmentSettingList']);
        Route::get('create', ['as' => 'consultant.management.attachment.settings.create', 'uses' => 'ConsultantManagementController@attachmentSettingCreate']);
        Route::get('{attachmentSettingId}/edit', ['as' => 'consultant.management.attachment.settings.edit', 'uses' => 'ConsultantManagementController@attachmentSettingEdit']);
        Route::get('{attachmentSettingId}/show', ['as' => 'consultant.management.attachment.settings.show', 'uses' => 'ConsultantManagementController@attachmentSettingShow']);
        Route::post('store', ['as' => 'consultant.management.attachment.settings.store', 'uses' => 'ConsultantManagementController@attachmentSettingStore']);
        Route::delete('{attachmentSettingId}/delete', ['as' => 'consultant.management.attachment.settings.delete', 'uses' => 'ConsultantManagementController@attachmentSettingDelete']);
    });

    Route::group(['prefix' => 'questionnaire-settings/{consultantManagementContractId}'], function(){
        Route::get('/', ['as' => 'consultant.management.questionnaire.settings.index', 'uses' => 'ConsultantManagementQuestionnaireController@index']);
        Route::get('list', ['as' => 'consultant.management.questionnaire.settings.ajax.list', 'uses' => 'ConsultantManagementQuestionnaireController@generalList']);
        Route::get('{id}/show', ['as' => 'consultant.management.questionnaire.settings.show', 'uses' => 'ConsultantManagementQuestionnaireController@generalShow']);
        Route::get('create', ['as' => 'consultant.management.questionnaire.settings.create', 'uses' => 'ConsultantManagementQuestionnaireController@generalCreate']);
        Route::get('{id}/edit', ['as' => 'consultant.management.questionnaire.settings.edit', 'uses' => 'ConsultantManagementQuestionnaireController@generalEdit']);
        Route::post('store', ['as' => 'consultant.management.questionnaire.settings.store', 'uses' => 'ConsultantManagementQuestionnaireController@generalStore']);
        Route::delete('{id}/delete', ['as' => 'consultant.management.questionnaire.settings.delete', 'uses' => 'ConsultantManagementQuestionnaireController@generalDelete']);
    });

    Route::group(['prefix' => 'vendor-category-rfp/{consultantManagementContractId}'], function(){
        Route::get('create', ['as' => 'consultant.management.vendor.category.rfp.create', 'uses' => 'ConsultantManagementController@vendorCategoryRfpCreate']);
        Route::get('{vendorCategoryRfpId}/edit', ['as' => 'consultant.management.vendor.category.rfp.edit', 'uses' => 'ConsultantManagementController@vendorCategoryRfpEdit']);
        Route::post('store', ['as' => 'consultant.management.vendor.category.rfp.store', 'uses' => 'ConsultantManagementController@vendorCategoryRfpStore']);
        Route::delete('{vendorCategoryRfpId}/delete', ['as' => 'consultant.management.vendor.category.rfp.delete', 'uses' => 'ConsultantManagementController@vendorCategoryRfpDelete']);
    });

    Route::group(['prefix' => 'vendor-category-rfp-account-codes/{vendorCategoryRfpId}'], function(){
        Route::get('/', ['as' => 'consultant.management.vendor.category.rfp.account.codes.index', 'uses' => 'ConsultantManagementController@vendorCategoryRfpAccountCodeIndex']);
        Route::get('{accountGroupId}/list', ['as' => 'consultant.management.vendor.category.rfp.account.codes.ajax.list', 'uses' => 'ConsultantManagementController@vendorCategoryRfpAccountCodeList']);
        Route::post('store', ['as' => 'consultant.management.vendor.category.rfp.account.codes.store', 'uses' => 'ConsultantManagementController@vendorCategoryRfpAccountCodeStore']);
    });

    Route::group(['prefix' => 'consultant-rfp/{vendorCategoryRfpId}'], function(){
        Route::group(['prefix' => 'rec-of-consultant'], function(){
            Route::get('/', ['as' => 'consultant.management.roc.index', 'uses' => 'ConsultantManagementRecommendationOfConsultantController@index']);
            Route::post('store', ['as' => 'consultant.management.roc.store', 'uses' => 'ConsultantManagementRecommendationOfConsultantController@store']);
            Route::get('consultant-list', ['as' => 'consultant.management.roc.consultants.ajax.list', 'uses' => 'ConsultantManagementRecommendationOfConsultantController@consultantList']);
            Route::get('verifier-log', ['as' => 'consultant.management.roc.verifier.ajax.log', 'uses' => 'ConsultantManagementRecommendationOfConsultantController@verifierLogs']);
            Route::get('selected-consultant-list', ['as' => 'consultant.management.roc.selected.consultants.ajax.list', 'uses' => 'ConsultantManagementRecommendationOfConsultantController@selectedConsultantList']);
            Route::post('select-consultant-store', ['as' => 'consultant.management.roc.select.consultant.store', 'uses' => 'ConsultantManagementRecommendationOfConsultantController@selectConsultantStore']);
            Route::post('{companyId}/select-consultant-update', ['as' => 'consultant.management.roc.select.consultant.update', 'uses' => 'ConsultantManagementRecommendationOfConsultantController@selectConsultantUpdate']);
            Route::post('verify', ['as' => 'consultant.management.roc.verify', 'uses' => 'ConsultantManagementRecommendationOfConsultantController@verify']);
            Route::delete('{companyId}/delete-consultant', ['as' => 'consultant.management.roc.select.consultant.delete', 'uses' => 'ConsultantManagementRecommendationOfConsultantController@selectConsultantDelete']);
        });

        Route::group(['prefix' => 'list-of-consultant'], function(){
            Route::get('/', ['as' => 'consultant.management.loc.index', 'uses' => 'ConsultantManagementListOfConsultantController@index']);
            Route::get('list', ['as' => 'consultant.management.loc.ajax.list', 'uses' => 'ConsultantManagementListOfConsultantController@list']);
            Route::get('{id}/show', ['as' => 'consultant.management.loc.show', 'uses' => 'ConsultantManagementListOfConsultantController@show']);
            Route::post('store', ['as' => 'consultant.management.loc.store', 'uses' => 'ConsultantManagementListOfConsultantController@store']);
            Route::get('{id}/consultant-list', ['as' => 'consultant.management.loc.consultants.ajax.list', 'uses' => 'ConsultantManagementListOfConsultantController@consultantList']);
            Route::get('{id}/selected-consultant-list', ['as' => 'consultant.management.loc.selected.consultants.ajax.list', 'uses' => 'ConsultantManagementListOfConsultantController@selectedConsultantList']);
            Route::post('{id}/select-consultant-store', ['as' => 'consultant.management.loc.select.consultant.store', 'uses' => 'ConsultantManagementListOfConsultantController@selectConsultantStore']);
            Route::get('{id}/verifier-log', ['as' => 'consultant.management.loc.verifier.ajax.log', 'uses' => 'ConsultantManagementListOfConsultantController@verifierLogs']);
            Route::post('{id}/select-consultant-update', ['as' => 'consultant.management.loc.select.consultant.update', 'uses' => 'ConsultantManagementListOfConsultantController@selectConsultantUpdate']);
            Route::delete('{id}/{companyId}/delete-consultant', ['as' => 'consultant.management.loc.select.consultant.delete', 'uses' => 'ConsultantManagementListOfConsultantController@selectConsultantDelete']);
            Route::post('verify', ['as' => 'consultant.management.loc.verify', 'uses' => 'ConsultantManagementListOfConsultantController@verify']);
        });

        Route::group(['prefix' => 'calling-rfp'], function(){
            Route::get('/', ['as' => 'consultant.management.calling.rfp.index', 'uses' => 'ConsultantManagementCallingRfpController@index']);
            Route::get('list', ['as' => 'consultant.management.calling.rfp.ajax.list', 'uses' => 'ConsultantManagementCallingRfpController@list']);
            Route::get('{id}/show', ['as' => 'consultant.management.calling.rfp.show', 'uses' => 'ConsultantManagementCallingRfpController@show']);
            Route::post('store', ['as' => 'consultant.management.calling.rfp.store', 'uses' => 'ConsultantManagementCallingRfpController@store']);
            Route::get('{id}/selected-consultant-list', ['as' => 'consultant.management.calling.rfp.selected.consultants.ajax.list', 'uses' => 'ConsultantManagementCallingRfpController@selectedConsultantList']);
            Route::post('{id}/select-consultant-update', ['as' => 'consultant.management.calling.rfp.select.consultant.update', 'uses' => 'ConsultantManagementCallingRfpController@selectConsultantUpdate']);
            Route::get('{id}/verifier-log', ['as' => 'consultant.management.calling.rfp.verifier.ajax.log', 'uses' => 'ConsultantManagementCallingRfpController@verifierLogs']);
            Route::post('verify', ['as' => 'consultant.management.calling.rfp.verify', 'uses' => 'ConsultantManagementCallingRfpController@verify']);
            Route::get('{id}/extend', ['as' => 'consultant.management.calling.rfp.extend.show', 'uses' => 'ConsultantManagementCallingRfpController@extendShow']);
            Route::post('extend', ['as' => 'consultant.management.calling.rfp.extend.store', 'uses' => 'ConsultantManagementCallingRfpController@extendStore']);
        });

        Route::group(['prefix' => 'open-rfp'], function(){
            Route::get('/', ['as' => 'consultant.management.open.rfp.index', 'uses' => 'ConsultantManagementOpenRfpController@index']);
            Route::get('list', ['as' => 'consultant.management.open.rfp.ajax.list', 'uses' => 'ConsultantManagementOpenRfpController@list']);
            Route::post('verifier-update', ['as' => 'consultant.management.open.rfp.verifier.update', 'uses' => 'ConsultantManagementOpenRfpController@verifierUpdate']);
            Route::post('verify', ['as' => 'consultant.management.open.rfp.verify', 'uses' => 'ConsultantManagementOpenRfpController@verify']);
            Route::post('resubmission-verifier-update', ['as' => 'consultant.management.rfp.resubmission.verifier.update', 'uses' => 'ConsultantManagementOpenRfpController@resubmissionVerifierUpdate']);
            Route::post('resubmission-verify', ['as' => 'consultant.management.rfp.resubmission.verify', 'uses' => 'ConsultantManagementOpenRfpController@resubmissionVerify']);
            Route::post('award-consultant', ['as' => 'consultant.management.open.rfp.award.consultant', 'uses' => 'ConsultantManagementOpenRfpController@awardConsultant']);

            Route::group(['prefix' => '{openRfpId}'], function(){
                Route::get('show', ['as' => 'consultant.management.open.rfp.show', 'uses' => 'ConsultantManagementOpenRfpController@show']);
                Route::get('consultant-list', ['as' => 'consultant.management.open.rfp.consultants.ajax.list', 'uses' => 'ConsultantManagementOpenRfpController@consultantList']);
                Route::get('verifier', ['as' => 'consultant.management.open.rfp.verifier', 'uses' => 'ConsultantManagementOpenRfpController@verifier']);
                Route::get('verifier-log', ['as' => 'consultant.management.open.rfp.verifier.ajax.log', 'uses' => 'ConsultantManagementOpenRfpController@verifierLogs']);
                Route::get('resubmission', ['as' => 'consultant.management.open.rfp.resubmission', 'uses' => 'ConsultantManagementOpenRfpController@resubmission']);
                Route::get('resubmission-verifier-log', ['as' => 'consultant.management.rfp.resubmission.verifier.ajax.log', 'uses' => 'ConsultantManagementOpenRfpController@resubmissionVerifierLogs']);    
                
                Route::group(['prefix' => 'approval-documents'], function(){
                    Route::get('/', ['as' => 'consultant.management.approval.document.index', 'uses' => 'ConsultantManagementApprovalDocumentController@index']);
                    Route::get('{id}/verifier-log', ['as' => 'consultant.management.approval.document.verifier.ajax.log', 'uses' => 'ConsultantManagementApprovalDocumentController@verifierLogs']);
                });
            });

            Route::group(['prefix' => 'approval-documents'], function(){
                Route::post('store', ['as' => 'consultant.management.approval.document.store', 'uses' => 'ConsultantManagementApprovalDocumentController@store']);
                Route::post('verifier-store', ['as' => 'consultant.management.approval.document.verifier.store', 'uses' => 'ConsultantManagementApprovalDocumentController@verifierStore']);
                Route::post('verify', ['as' => 'consultant.management.approval.document.verify', 'uses' => 'ConsultantManagementApprovalDocumentController@verify']);
                Route::post('section-a-store', ['as' => 'consultant.management.approval.document.section.a.store', 'uses' => 'ConsultantManagementApprovalDocumentController@sectionAStore']);
                Route::post('section-b-store', ['as' => 'consultant.management.approval.document.section.b.store', 'uses' => 'ConsultantManagementApprovalDocumentController@sectionBStore']);
                Route::post('section-c-store', ['as' => 'consultant.management.approval.document.section.c.store', 'uses' => 'ConsultantManagementApprovalDocumentController@sectionCStore']);
                Route::post('section-d-service-fee-store', ['as' => 'consultant.management.approval.document.section.d.service.fee.store', 'uses' => 'ConsultantManagementApprovalDocumentController@sectionDServiceFeeStore']);
                Route::post('section-d-details-store', ['as' => 'consultant.management.approval.document.section.d.details.store', 'uses' => 'ConsultantManagementApprovalDocumentController@sectionDDetailsStore']);

                Route::post('section-appendix-store', ['as' => 'consultant.management.approval.document.section.appendix.store', 'uses' => 'ConsultantManagementApprovalDocumentController@sectionAppendixStore']);
                Route::post('section-appendix-attachment-upload', ['as' => 'consultant.management.approval.document.section.appendix.attachment.upload', 'uses' => 'ConsultantManagementApprovalDocumentController@sectionAppendixAttachmentUpload']);

                Route::get('{openRfpId}/print', ['as' => 'consultant.management.approval.document.print', 'uses' => 'ConsultantManagementApprovalDocumentController@print']);

                Route::get('{openRfpId}/section-c-consultant-list/{id}', ['as' => 'consultant.management.approval.document.section.c.consultants.list', 'uses' => 'ConsultantManagementApprovalDocumentController@sectionCConsultantList']);
                Route::get('{openRfpId}/section-appendix-list', ['as' => 'consultant.management.approval.document.section.appendix.list', 'uses' => 'ConsultantManagementApprovalDocumentController@sectionAppendixList']);
                Route::get('appendix-details/{id}', ['as' => 'consultant.management.approval.document.section.appendix.details.info', 'uses' => 'ConsultantManagementApprovalDocumentController@appendixDetailsInfo']);
                Route::get('section-appendix-attachment-download/{id}', ['as' => 'consultant.management.approval.document.section.appendix.attachment.download', 'uses' => 'ConsultantManagementApprovalDocumentController@sectionAppendixAttachmentDownload']);
                Route::delete('{openRfpId}/appendix-details-delete/{id}', ['as' => 'consultant.management.approval.document.section.appendix.details.delete', 'uses' => 'ConsultantManagementApprovalDocumentController@appendixDetailsDelete']);

                Route::get('account-codes', ['as' => 'consultant.management.approval.document.accountCodes.list', 'uses' => 'ConsultantManagementApprovalDocumentController@getAccountCodesList']);
                Route::post('account-codes', ['as' => 'consultant.management.approval.document.accountCodes.amount.update', 'uses' => 'ConsultantManagementApprovalDocumentController@saveAccountCodeAmounts']);
            });
        });

        Route::group(['prefix' => 'letter-of-award'], function(){
            Route::get('/', ['as' => 'consultant.management.loa.index', 'uses' => 'ConsultantManagement\LetterOfAwardController@index']);
            Route::post('store', ['as' => 'consultant.management.loa.store', 'uses' => 'ConsultantManagement\LetterOfAwardController@store']);
            Route::post('verifier-store', ['as' => 'consultant.management.loa.verifier.store', 'uses' => 'ConsultantManagement\LetterOfAwardController@verifierStore']);
            Route::post('verify', ['as' => 'consultant.management.loa.verify', 'uses' => 'ConsultantManagement\LetterOfAwardController@verify']);
            Route::get('verifier-log', ['as' => 'consultant.management.loa.verifier.ajax.log', 'uses' => 'ConsultantManagement\LetterOfAwardController@verifierLogs']);
            Route::post('content-update', ['as' => 'consultant.management.loa.content.update', 'uses' => 'ConsultantManagement\LetterOfAwardController@contentUpdate']);

            Route::get('letterhead-edit', ['as' => 'consultant.management.loa.letterhead.edit', 'uses' => 'ConsultantManagement\LetterOfAwardController@letterheadEdit']);
            Route::get('clause-edit', ['as' => 'consultant.management.loa.clause.edit', 'uses' => 'ConsultantManagement\LetterOfAwardController@clauseEdit']);
            Route::get('signatory-edit', ['as' => 'consultant.management.loa.signatory.edit', 'uses' => 'ConsultantManagement\LetterOfAwardController@signatoryEdit']);

            Route::post('letterhead-store', ['as' => 'consultant.management.loa.letterhead.store', 'uses' => 'ConsultantManagement\LetterOfAwardController@letterheadStore']);
            Route::post('signatory-store', ['as' => 'consultant.management.loa.signatory.store', 'uses' => 'ConsultantManagement\LetterOfAwardController@signatoryStore']);
            Route::post('clause-store', ['as' => 'consultant.management.loa.clause.store', 'uses' => 'ConsultantManagement\LetterOfAwardController@clauseStore']);

            Route::get('preview', ['as' => 'consultant.management.loa.preview', 'uses' => 'ConsultantManagement\LetterOfAwardController@preview']);
            Route::get('print', ['as' => 'consultant.management.loa.print', 'uses' => 'ConsultantManagement\LetterOfAwardController@print']);

            Route::get('{id}/attachment-info', ['as' => 'consultant.management.loa.attachment.info', 'uses' => 'ConsultantManagement\LetterOfAwardController@attachmentInfo']);
            Route::get('attachment-list', ['as' => 'consultant.management.loa.attachment.list', 'uses' => 'ConsultantManagement\LetterOfAwardController@attachmentList']);
            Route::post('attachment-store', ['as' => 'consultant.management.loa.attachment.store', 'uses' => 'ConsultantManagement\LetterOfAwardController@attachmentStore']);
            Route::post('attachment-upload', ['as' => 'consultant.management.loa.attachment.upload', 'uses' => 'ConsultantManagement\LetterOfAwardController@attachmentUpload']);
            Route::get('{id}/attachment-download', ['as' => 'consultant.management.loa.attachment.download', 'uses' => 'ConsultantManagement\LetterOfAwardController@attachmentDownload']);
            Route::delete('{id}/attachment-delete', ['as' => 'consultant.management.loa.attachment.delete', 'uses' => 'ConsultantManagement\LetterOfAwardController@attachmentDelete']);
        });

        Route::group(['prefix' => 'attachment-settings'], function(){
            Route::get('/', ['as' => 'consultant.management.rfp.attachment.settings.index', 'uses' => 'ConsultantManagementRfpAttachmentController@index']);
            Route::get('general-list', ['as' => 'consultant.management.rfp.general.settings.ajax.list', 'uses' => 'ConsultantManagementRfpAttachmentController@generalList']);
            Route::post('store', ['as' => 'consultant.management.rfp.general.attachment.settings.store', 'uses' => 'ConsultantManagementRfpAttachmentController@generalSettingStore']);
            Route::get('rfp-list', ['as' => 'consultant.management.rfp.settings.ajax.list', 'uses' => 'ConsultantManagementRfpAttachmentController@rfpList']);
            Route::get('create', ['as' => 'consultant.management.rfp.attachment.settings.create', 'uses' => 'ConsultantManagementRfpAttachmentController@rfpSettingCreate']);
            Route::get('{rfpAttachmentSettingId}/edit', ['as' => 'consultant.management.rfp.attachment.settings.edit', 'uses' => 'ConsultantManagementRfpAttachmentController@rfpSettingEdit']);
            Route::get('{rfpAttachmentSettingId}/show', ['as' => 'consultant.management.rfp.attachment.settings.show', 'uses' => 'ConsultantManagementRfpAttachmentController@rfpSettingShow']);
            Route::post('rfp-store', ['as' => 'consultant.management.rfp.attachment.settings.store', 'uses' => 'ConsultantManagementRfpAttachmentController@rfpSettingStore']);
            Route::delete('{rfpAttachmentSettingId}/delete', ['as' => 'consultant.management.rfp.attachment.settings.delete', 'uses' => 'ConsultantManagementRfpAttachmentController@rfpSettingDelete']);
        });

        Route::group(['prefix' => 'documents'], function(){
            Route::get('/', ['as' => 'consultant.management.rfp.documents.index', 'uses' => 'ConsultantManagementRfpDocumentController@index']);
            Route::get('list', ['as' => 'consultant.management.rfp.documents.ajax.list', 'uses' => 'ConsultantManagementRfpDocumentController@list']);
            Route::post('upload', ['as' => 'consultant.management.rfp.documents.upload', 'uses' => 'ConsultantManagementRfpDocumentController@upload']);
            Route::get('{id}/download', ['as' => 'consultant.management.rfp.documents.download', 'uses' => 'ConsultantManagementRfpDocumentController@download']);
            Route::delete('{id}/delete', ['as' => 'consultant.management.rfp.documents.delete', 'uses' => 'ConsultantManagementRfpDocumentController@delete']);
            Route::post('remarks-store', ['as' => 'consultant.management.rfp.documents.remarks.store', 'uses' => 'ConsultantManagementRfpDocumentController@remarkStore']);

            Route::group(['prefix' => '{companyId}'], function(){
                Route::get('list', ['as' => 'consultant.management.consultant.rfp.documents.ajax.list', 'uses' => 'ConsultantManagementRfpDocumentController@consultantDocumentList']);
            });
        });

        Route::group(['prefix' => 'consultant-attachment/{companyId}'], function(){
            Route::get('directory-list', ['as' => 'consultant.management.consultant.attachment.directory.ajax.list', 'uses' => 'ConsultantManagementRfpAttachmentController@consultantAttachmentDirectoryList']);
            Route::get('attachment-list', ['as' => 'consultant.management.consultant.attachment.ajax.list', 'uses' => 'ConsultantManagementRfpAttachmentController@consultantAttachmentList']);
            Route::post('attachment-upload', ['as' => 'consultant.management.consultant.attachment.upload', 'uses' => 'ConsultantManagementRfpAttachmentController@consultantAttachmentUpload']);
            Route::get('{attachmentId}/attachment-download', ['as' => 'consultant.management.consultant.attachment.download', 'uses' => 'ConsultantManagementRfpAttachmentController@consultantAttachmentDownload']);
            Route::delete('{attachmentId}/delete', ['as' => 'consultant.management.consultant.attachment.delete', 'uses' => 'ConsultantManagementRfpAttachmentController@consultantAttachmentDelete']);
            Route::get('attachment-uploaded-list', ['as' => 'consultant.management.consultant.attachment.uploaded.ajax.list', 'uses' => 'ConsultantManagementRfpAttachmentController@consultantAttachmentUploadedList']);
        });

        Route::group(['prefix' => 'questionnaire'], function(){
            Route::group(['prefix' => '{companyId}'], function(){
                Route::get('show', ['as' => 'consultant.management.consultant.questionnaire.show', 'uses' => 'ConsultantManagementQuestionnaireController@consultantRfpShow']);
                Route::get('general-list', ['as' => 'consultant.management.consultant.questionnaire.general.ajax.list', 'uses' => 'ConsultantManagementQuestionnaireController@consultantGeneralList']);
                Route::get('rfp-list', ['as' => 'consultant.management.consultant.questionnaire.rfp.ajax.list', 'uses' => 'ConsultantManagementQuestionnaireController@consultantRfpList']);
                Route::get('{id}/general-show', ['as' => 'consultant.management.consultant.questionnaire.general.show', 'uses' => 'ConsultantManagementQuestionnaireController@consultantGeneralShow']);
                Route::post('general-exclude', ['as' => 'consultant.management.consultant.questionnaire.general.exclude', 'uses' => 'ConsultantManagementQuestionnaireController@generalExclude']);
                Route::get('{id}/rfp-show', ['as' => 'consultant.management.consultant.questionnaire.rfp.show', 'uses' => 'ConsultantManagementQuestionnaireController@rfpShow']);
                Route::get('rfp-create', ['as' => 'consultant.management.consultant.questionnaire.rfp.create', 'uses' => 'ConsultantManagementQuestionnaireController@rfpCreate']);
                Route::get('{id}/rfp-edit', ['as' => 'consultant.management.consultant.questionnaire.rfp.edit', 'uses' => 'ConsultantManagementQuestionnaireController@rfpEdit']);
                Route::delete('{id}/rfp-delete', ['as' => 'consultant.management.consultant.questionnaire.rfp.delete', 'uses' => 'ConsultantManagementQuestionnaireController@rfpDelete']);
                Route::get('replies', ['as' => 'consultant.management.consultant.questionnaire.rfp.replies', 'uses' => 'ConsultantManagementQuestionnaireController@consultantRfpReplies']);
                Route::get('consultant-attachments', ['as' => 'consultant.management.consultant.questionnaire.rfp.attachments', 'uses' => 'ConsultantManagementQuestionnaireController@consultantAttachmentList']);
            });
            
            Route::post('rfp-store', ['as' => 'consultant.management.consultant.questionnaire.rfp.store', 'uses' => 'ConsultantManagementQuestionnaireController@rfpStore']);
            Route::post('publish', ['as' => 'consultant.management.consultant.questionnaire.publish', 'uses' => 'ConsultantManagementQuestionnaireController@publish']);
        });

        Route::group(['prefix' => 'rfp-interview'], function(){
            Route::group(['prefix' => '{callingRfpId}'], function(){
                Route::get('/', ['as' => 'consultant.management.consultant.rfp.interview.index', 'uses' => 'ConsultantManagementRfpInterviewController@index']);
                Route::get('list', ['as' => 'consultant.management.consultant.rfp.interview.list', 'uses' => 'ConsultantManagementRfpInterviewController@list']);
                Route::get('create', ['as' => 'consultant.management.consultant.rfp.interview.create', 'uses' => 'ConsultantManagementRfpInterviewController@create']);
                Route::get('{id}/show', ['as' => 'consultant.management.consultant.rfp.interview.show', 'uses' => 'ConsultantManagementRfpInterviewController@show']);
                Route::get('{id}/edit', ['as' => 'consultant.management.consultant.rfp.interview.edit', 'uses' => 'ConsultantManagementRfpInterviewController@edit']);
                Route::delete('{id}/delete', ['as' => 'consultant.management.consultant.rfp.interview.delete', 'uses' => 'ConsultantManagementRfpInterviewController@delete']);
                Route::get('consultant-list', ['as' => 'consultant.management.consultant.rfp.interview.consultant.list', 'uses' => 'ConsultantManagementRfpInterviewController@consultantList']);
                Route::get('{id}/selected-consultant-list', ['as' => 'consultant.management.consultant.rfp.interview.selected.consultant.list', 'uses' => 'ConsultantManagementRfpInterviewController@selectedConsultantList']);
                Route::get('{id}/resend', ['as' => 'consultant.management.consultant.rfp.interview.resend', 'uses' => 'ConsultantManagementRfpInterviewController@resend']);
            });

            Route::post('store', ['as' => 'consultant.management.consultant.rfp.interview.store', 'uses' => 'ConsultantManagementRfpInterviewController@store']);
            Route::post('send', ['as' => 'consultant.management.consultant.rfp.interview.send', 'uses' => 'ConsultantManagementRfpInterviewController@send']);
        });

        Route::group(['prefix' => 'tracker'], function(){
            Route::get('/', ['as' => 'consultant.management.tracker.index', 'uses' => 'ConsultantManagementTrackerController@index']);
            Route::get('roc-verifier-log', ['as' => 'consultant.management.tracker.roc.verifier.log', 'uses' => 'ConsultantManagementTrackerController@recommendationOfConsultantVerifierLogs']);
            Route::get('loc-verifier-log', ['as' => 'consultant.management.tracker.loc.verifier.log', 'uses' => 'ConsultantManagementTrackerController@listOfConsultantVerifierLogs']);
            Route::get('calling_rfp-verifier-log', ['as' => 'consultant.management.tracker.calling.rfp.verifier.log', 'uses' => 'ConsultantManagementTrackerController@callingRfpVerifierLogs']);
            Route::get('open_rfp-verifier-log', ['as' => 'consultant.management.tracker.open.rfp.verifier.log', 'uses' => 'ConsultantManagementTrackerController@openRfpVerifierLogs']);

        });
    });

    Route::group(['prefix' => 'consultant-user-management'], function(){
        Route::get('/', ['as' => 'consultant.management.consultant.user.management.index', 'uses' => 'ConsultantManagementConsultantController@userManagementIndex']);
        Route::get('unassigned-list', ['as' => 'consultant.management.consultant.user.management.unassigned.list', 'uses' => 'ConsultantManagementConsultantController@userManagementUnassignedList']);
        Route::get('assigned-list', ['as' => 'consultant.management.consultant.user.management.assigned.list', 'uses' => 'ConsultantManagementConsultantController@userManagementAssignedList']);
        Route::post('assign', ['as' => 'consultant.management.consultant.user.management.assign', 'uses' => 'ConsultantManagementConsultantController@userManagementAssign']);
        Route::delete('{id}/unassign', ['as' => 'consultant.management.consultant.user.management.unassign', 'uses' => 'ConsultantManagementConsultantController@userManagementUnassign']);
    });

    Route::group(['prefix' => 'consultant-calling-rfp'], function(){
        Route::get('/', ['as' => 'consultant.management.consultant.calling.rfp.index', 'uses' => 'ConsultantManagementConsultantController@callingRfpIndex']);
        Route::get('list', ['as' => 'consultant.management.consultant.calling.rfp.ajax.list', 'uses' => 'ConsultantManagementConsultantController@callingRfpList']);
        Route::get('{consultantManagementRfpId}/show', ['as' => 'consultant.management.consultant.calling.rfp.show', 'uses' => 'ConsultantManagementConsultantController@callingRfpShow']);
        Route::post('proposed-fee-store', ['as' => 'consultant.management.consultant.calling.rfp.proposed.fee.store', 'uses' => 'ConsultantManagementConsultantController@proposedFeeStore']);
        Route::post('common-info-store', ['as' => 'consultant.management.consultant.calling.rfp.common.info.store', 'uses' => 'ConsultantManagementConsultantController@commonInfoStore']);
    });

    Route::group(['prefix' => 'consultant-rfp-questionnaire'], function(){
        Route::get('/', ['as' => 'consultant.management.consultant.rfp.questionnaire.index', 'uses' => 'ConsultantManagementConsultantController@questionnaireIndex']);
        Route::get('list', ['as' => 'consultant.management.consultant.rfp.questionnaire.ajax.list', 'uses' => 'ConsultantManagementConsultantController@questionnaireRfpList']);
        Route::get('{id}/show', ['as' => 'consultant.management.consultant.rfp.questionnaire.show', 'uses' => 'ConsultantManagementConsultantController@questionnaireShow']);
        Route::post('notify', ['as' => 'consultant.management.consultant.rfp.questionnaire.notify', 'uses' => 'ConsultantManagementConsultantController@questionnaireNotify']);
        Route::post('reply', ['as' => 'consultant.management.consultant.rfp.questionnaire.reply', 'uses' => 'ConsultantManagementConsultantController@questionnaireReply']);
        Route::post('reply-upload-attachments', ['as' => 'consultant.management.consultant.rfp.questionnaire.upload.attachments', 'uses' => 'ConsultantManagementConsultantController@attachmentUpload']);
        Route::get('{id}/attachment-list', ['as' => 'consultant.management.consultant.rfp.questionnaire.attachments.list', 'uses' => 'ConsultantManagementConsultantController@attachmentList']);
        Route::get('{id}/attachment-download', ['as' => 'consultant.management.consultant.rfp.questionnaire.attachments.download', 'uses' => 'ConsultantManagementConsultantController@attachmentDownload']);
        Route::delete('{id}/attachment-delete', ['as' => 'consultant.management.consultant.rfp.questionnaire.attachments.delete', 'uses' => 'ConsultantManagementConsultantController@attachmentDelete']);
    });

    Route::group(['prefix' => 'consultant-awarded-rfp'], function(){
        Route::get('/', ['as' => 'consultant.management.consultant.awarded.rfp.index', 'uses' => 'ConsultantManagementConsultantController@awardedRfpIndex']);
        Route::get('list', ['as' => 'consultant.management.consultant.awarded.rfp.ajax.list', 'uses' => 'ConsultantManagementConsultantController@awardedRfpList']);
        Route::get('{consultantRfpId}/show', ['as' => 'consultant.management.consultant.awarded.rfp.show', 'uses' => 'ConsultantManagementConsultantController@awardedRfpShow']);
        Route::get('{consultantRfpId}/print-loa', ['as' => 'consultant.management.consultant.loa.print', 'uses' => 'ConsultantManagementConsultantController@printLetterOfAward']);
    });

    Route::group(['prefix' => 'vendor-profile/{id}'], function(){
        Route::get('/', ['as' => 'consultant.management.vendor.profile.info', 'uses' => 'ConsultantManagementController@vendorProfileInfo']);
        Route::get('preq-list', ['as' => 'consultant.management.vendor.profile.preq.list', 'uses' => 'VendorProfilesController@vendorPrequalifictionList']);
    });
});