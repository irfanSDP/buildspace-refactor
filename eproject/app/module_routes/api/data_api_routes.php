<?php

    //insert and update lms users
    Route::post('insert-lms-user', array('as' => 'api.insert-lms-user', 'uses' => 'Api\PostDataApiController@insertLmsUser' ));

    //account_code_settings
    Route::get('account-code-settings', array('as' => 'api.account-code-settings', 'uses' => 'Api\GetDataApiController@accountCodeSettings' ));
    Route::post('account-code-settings', array('as' => 'api.account-code-settings.create', 'uses' => 'Api\PostDataApiController@accountCodeSettings' ));
    Route::put('account-code-settings/{Id}', array('as' => 'api.account-code-settings.update', 'uses' => 'Api\PutDataApiController@accountCodeSettings' ));
    Route::delete('account-code-settings/{Id}', array('as' => 'api.account-code-settings.delete', 'uses' => 'Api\DeleteDataApiController@accountCodeSettings' ));

    //additional_element_values
    Route::get('additional-element-values', array('as' => 'api.additional-element-values', 'uses' => 'Api\GetDataApiController@additionalElementValues' ));
    Route::post('additional-element-values', array('as' => 'api.additional-element-values.create', 'uses' => 'Api\PostDataApiController@additionalElementValues' ));
    Route::put('additional-element-values/{Id}', array('as' => 'api.additional-element-values.update', 'uses' => 'Api\PutDataApiController@additionalElementValues' ));
    Route::delete('additional-element-values/{Id}', array('as' => 'api.additional-element-values.delete', 'uses' => 'Api\DeleteDataApiController@additionalElementValues' ));

    //additional_expenses
    Route::get('additional-expenses', array('as' => 'api.additional-expenses', 'uses' => 'Api\GetDataApiController@additionalExpenses' ));
    Route::post('additional-expenses', array('as' => 'api.additional-expenses.create', 'uses' => 'Api\PostDataApiController@additionalExpenses' ));
    Route::put('additional-expenses/{Id}', array('as' => 'api.additional-expenses.update', 'uses' => 'Api\PutDataApiController@additionalExpenses' ));
    Route::delete('additional-expenses/{Id}', array('as' => 'api.additional-expenses.delete', 'uses' => 'Api\DeleteDataApiController@additionalExpenses' ));

    //additional_expense_interim_claims
    Route::get('additional-expense-interim-claims', array('as' => 'api.additional-expense-interim-claims', 'uses' => 'Api\GetDataApiController@additionalExpenseInterimClaims' ));
    Route::post('additional-expense-interim-claims', array('as' => 'api.additional-expense-interim-claims.create', 'uses' => 'Api\PostDataApiController@additionalExpenseInterimClaims' ));
    Route::put('additional-expense-interim-claims/{Id}', array('as' => 'api.additional-expense-interim-claims.update', 'uses' => 'Api\PutDataApiController@additionalExpenseInterimClaims' ));
    Route::delete('additional-expense-interim-claims/{Id}', array('as' => 'api.additional-expense-interim-claims.delete', 'uses' => 'Api\DeleteDataApiController@additionalExpenseInterimClaims' ));

    //ae_contractor_confirm_delays
    Route::get('ae-contractor-confirm-delays', array('as' => 'api.ae-contractor-confirm-delays', 'uses' => 'Api\GetDataApiController@aeContractorConfirmDelays' ));
    Route::post('ae-contractor-confirm-delays', array('as' => 'api.ae-contractor-confirm-delays.create', 'uses' => 'Api\PostDataApiController@aeContractorConfirmDelays' ));
    Route::put('ae-contractor-confirm-delays/{Id}', array('as' => 'api.ae-contractor-confirm-delays.update', 'uses' => 'Api\PutDataApiController@aeContractorConfirmDelays' ));
    Route::delete('ae-contractor-confirm-delays/{Id}', array('as' => 'api.ae-contractor-confirm-delays.delete', 'uses' => 'Api\DeleteDataApiController@aeContractorConfirmDelays' ));

    //ae_first_level_messages
    Route::get('ae-first-level-messages', array('as' => 'api.ae-first-level-messages', 'uses' => 'Api\GetDataApiController@aeFirstLevelMessages' ));
    Route::post('ae-first-level-messages', array('as' => 'api.ae-first-level-messages.create', 'uses' => 'Api\PostDataApiController@aeFirstLevelMessages' ));
    Route::put('ae-first-level-messages/{Id}', array('as' => 'api.ae-first-level-messages.update', 'uses' => 'Api\PutDataApiController@aeFirstLevelMessages' ));
    Route::delete('ae-first-level-messages/{Id}', array('as' => 'api.ae-first-level-messages.delete', 'uses' => 'Api\DeleteDataApiController@aeFirstLevelMessages' ));

    //ae_fourth_level_messages
    Route::get('ae-fourth-level-messages', array('as' => 'api.ae-fourth-level-messages', 'uses' => 'Api\GetDataApiController@aeFourthLevelMessages' ));
    Route::post('ae-fourth-level-messages', array('as' => 'api.ae-fourth-level-messages.create', 'uses' => 'Api\PostDataApiController@aeFourthLevelMessages' ));
    Route::put('ae-fourth-level-messages/{Id}', array('as' => 'api.ae-fourth-level-messages.update', 'uses' => 'Api\PutDataApiController@aeFourthLevelMessages' ));
    Route::delete('ae-fourth-level-messages/{Id}', array('as' => 'api.ae-fourth-level-messages.delete', 'uses' => 'Api\DeleteDataApiController@aeFourthLevelMessages' ));

    //accounting_report_export_logs
    Route::get('accounting-report-export-logs', array('as' => 'api.accounting-report-export-logs', 'uses' => 'Api\GetDataApiController@accountingReportExportLogs' ));
    Route::post('accounting-report-export-logs', array('as' => 'api.accounting-report-export-logs.create', 'uses' => 'Api\PostDataApiController@accountingReportExportLogs' ));
    Route::put('accounting-report-export-logs/{Id}', array('as' => 'api.accounting-report-export-logs.update', 'uses' => 'Api\PutDataApiController@accountingReportExportLogs' ));
    Route::delete('accounting-report-export-logs/{Id}', array('as' => 'api.accounting-report-export-logs.delete', 'uses' => 'Api\DeleteDataApiController@accountingReportExportLogs' ));

    //accounting_report_export_log_item_codes
    Route::get('accounting-report-export-log-item-codes', array('as' => 'api.accounting-report-export-log-item-codes', 'uses' => 'Api\GetDataApiController@accountingReportExportLogItemCodes' ));
    Route::post('accounting-report-export-log-item-codes', array('as' => 'api.accounting-report-export-log-item-codes.create', 'uses' => 'Api\PostDataApiController@accountingReportExportLogItemCodes' ));
    Route::put('accounting-report-export-log-item-codes/{Id}', array('as' => 'api.accounting-report-export-log-item-codes.update', 'uses' => 'Api\PutDataApiController@accountingReportExportLogItemCodes' ));
    Route::delete('accounting-report-export-log-item-codes/{Id}', array('as' => 'api.accounting-report-export-log-item-codes.delete', 'uses' => 'Api\DeleteDataApiController@accountingReportExportLogItemCodes' ));

    //acknowledgement_letters
    Route::get('acknowledgement-letters', array('as' => 'api.acknowledgement-letters', 'uses' => 'Api\GetDataApiController@acknowledgementLetters' ));
    Route::post('acknowledgement-letters', array('as' => 'api.acknowledgement-letters.create', 'uses' => 'Api\PostDataApiController@acknowledgementLetters' ));
    Route::put('acknowledgement-letters/{Id}', array('as' => 'api.acknowledgement-letters.update', 'uses' => 'Api\PutDataApiController@acknowledgementLetters' ));
    Route::delete('acknowledgement-letters/{Id}', array('as' => 'api.acknowledgement-letters.delete', 'uses' => 'Api\DeleteDataApiController@acknowledgementLetters' ));

    //additional_expense_claims
    Route::get('additional-expense-claims', array('as' => 'api.additional-expense-claims', 'uses' => 'Api\GetDataApiController@additionalExpenseClaims' ));
    Route::post('additional-expense-claims', array('as' => 'api.additional-expense-claims.create', 'uses' => 'Api\PostDataApiController@additionalExpenseClaims' ));
    Route::put('additional-expense-claims/{Id}', array('as' => 'api.additional-expense-claims.update', 'uses' => 'Api\PutDataApiController@additionalExpenseClaims' ));
    Route::delete('additional-expense-claims/{Id}', array('as' => 'api.additional-expense-claims.delete', 'uses' => 'Api\DeleteDataApiController@additionalExpenseClaims' ));

    //ae_third_level_messages
    Route::get('ae-third-level-messages', array('as' => 'api.ae-third-level-messages', 'uses' => 'Api\GetDataApiController@aeThirdLevelMessages' ));
    Route::post('ae-third-level-messages', array('as' => 'api.ae-third-level-messages.create', 'uses' => 'Api\PostDataApiController@aeThirdLevelMessages' ));
    Route::put('ae-third-level-messages/{Id}', array('as' => 'api.ae-third-level-messages.update', 'uses' => 'Api\PutDataApiController@aeThirdLevelMessages' ));
    Route::delete('ae-third-level-messages/{Id}', array('as' => 'api.ae-third-level-messages.delete', 'uses' => 'Api\DeleteDataApiController@aeThirdLevelMessages' ));

    //ai_third_level_messages
    Route::get('ai-third-level-messages', array('as' => 'api.ai-third-level-messages', 'uses' => 'Api\GetDataApiController@aiThirdLevelMessages' ));
    Route::post('ai-third-level-messages', array('as' => 'api.ai-third-level-messages.create', 'uses' => 'Api\PostDataApiController@aiThirdLevelMessages' ));
    Route::put('ai-third-level-messages/{Id}', array('as' => 'api.ai-third-level-messages.update', 'uses' => 'Api\PutDataApiController@aiThirdLevelMessages' ));
    Route::delete('ai-third-level-messages/{Id}', array('as' => 'api.ai-third-level-messages.delete', 'uses' => 'Api\DeleteDataApiController@aiThirdLevelMessages' ));

    //architect_instruction_engineer_instruction
    Route::get('architect-instruction-engineer-instruction', array('as' => 'api.architect-instruction-engineer-instruction', 'uses' => 'Api\GetDataApiController@architectInstructionEngineerInstruction' ));
    Route::post('architect-instruction-engineer-instruction', array('as' => 'api.architect-instruction-engineer-instruction.create', 'uses' => 'Api\PostDataApiController@architectInstructionEngineerInstruction' ));
    Route::put('architect-instruction-engineer-instruction/{Id}', array('as' => 'api.architect-instruction-engineer-instruction.update', 'uses' => 'Api\PutDataApiController@architectInstructionEngineerInstruction' ));
    Route::delete('architect-instruction-engineer-instruction/{Id}', array('as' => 'api.architect-instruction-engineer-instruction.delete', 'uses' => 'Api\DeleteDataApiController@architectInstructionEngineerInstruction' ));

    //apportionment_types
    Route::get('apportionment-types', array('as' => 'api.apportionment-types', 'uses' => 'Api\GetDataApiController@apportionmentTypes' ));
    Route::post('apportionment-types', array('as' => 'api.apportionment-types.create', 'uses' => 'Api\PostDataApiController@apportionmentTypes' ));
    Route::put('apportionment-types/{Id}', array('as' => 'api.apportionment-types.update', 'uses' => 'Api\PutDataApiController@apportionmentTypes' ));
    Route::delete('apportionment-types/{Id}', array('as' => 'api.apportionment-types.delete', 'uses' => 'Api\DeleteDataApiController@apportionmentTypes' ));

    //architect_instruction_interim_claims
    Route::get('architect-instruction-interim-claims', array('as' => 'api.architect-instruction-interim-claims', 'uses' => 'Api\GetDataApiController@architectInstructionInterimClaims' ));
    Route::post('architect-instruction-interim-claims', array('as' => 'api.architect-instruction-interim-claims.create', 'uses' => 'Api\PostDataApiController@architectInstructionInterimClaims' ));
    Route::put('architect-instruction-interim-claims/{Id}', array('as' => 'api.architect-instruction-interim-claims.update', 'uses' => 'Api\PutDataApiController@architectInstructionInterimClaims' ));
    Route::delete('architect-instruction-interim-claims/{Id}', array('as' => 'api.architect-instruction-interim-claims.delete', 'uses' => 'Api\DeleteDataApiController@architectInstructionInterimClaims' ));

    //architect_instruction_messages
    Route::get('architect-instruction-messages', array('as' => 'api.architect-instruction-messages', 'uses' => 'Api\GetDataApiController@architectInstructionMessages' ));
    Route::post('architect-instruction-messages', array('as' => 'api.architect-instruction-messages.create', 'uses' => 'Api\PostDataApiController@architectInstructionMessages' ));
    Route::put('architect-instruction-messages/{Id}', array('as' => 'api.architect-instruction-messages.update', 'uses' => 'Api\PutDataApiController@architectInstructionMessages' ));
    Route::delete('architect-instruction-messages/{Id}', array('as' => 'api.architect-instruction-messages.delete', 'uses' => 'Api\DeleteDataApiController@architectInstructionMessages' ));

    //attached_clause_items
    Route::get('attached-clause-items', array('as' => 'api.attached-clause-items', 'uses' => 'Api\GetDataApiController@attachedClauseItems' ));
    Route::post('attached-clause-items', array('as' => 'api.attached-clause-items.create', 'uses' => 'Api\PostDataApiController@attachedClauseItems' ));
    Route::put('attached-clause-items/{Id}', array('as' => 'api.attached-clause-items.update', 'uses' => 'Api\PutDataApiController@attachedClauseItems' ));
    Route::delete('attached-clause-items/{Id}', array('as' => 'api.attached-clause-items.delete', 'uses' => 'Api\DeleteDataApiController@attachedClauseItems' ));

    //calendar_settings
    Route::get('calendar-settings', array('as' => 'api.calendar-settings', 'uses' => 'Api\GetDataApiController@calendarSettings' ));
    Route::post('calendar-settings', array('as' => 'api.calendar-settings.create', 'uses' => 'Api\PostDataApiController@calendarSettings' ));
    Route::put('calendar-settings/{Id}', array('as' => 'api.calendar-settings.update', 'uses' => 'Api\PutDataApiController@calendarSettings' ));
    Route::delete('calendar-settings/{Id}', array('as' => 'api.calendar-settings.delete', 'uses' => 'Api\DeleteDataApiController@calendarSettings' ));

    //claim_certificate_email_logs
    Route::get('claim-certificate-email-logs', array('as' => 'api.claim-certificate-email-logs', 'uses' => 'Api\GetDataApiController@claimCertificateEmailLogs' ));
    Route::post('claim-certificate-email-logs', array('as' => 'api.claim-certificate-email-logs.create', 'uses' => 'Api\PostDataApiController@claimCertificateEmailLogs' ));
    Route::put('claim-certificate-email-logs/{Id}', array('as' => 'api.claim-certificate-email-logs.update', 'uses' => 'Api\PutDataApiController@claimCertificateEmailLogs' ));
    Route::delete('claim-certificate-email-logs/{Id}', array('as' => 'api.claim-certificate-email-logs.delete', 'uses' => 'Api\DeleteDataApiController@claimCertificateEmailLogs' ));

    //cidb_grades
    Route::get('cidb-grades', array('as' => 'api.cidb-grades', 'uses' => 'Api\GetDataApiController@cidbGrades' ));
    Route::post('cidb-grades', array('as' => 'api.cidb-grades.create', 'uses' => 'Api\PostDataApiController@cidbGrades' ));
    Route::put('cidb-grades/{Id}', array('as' => 'api.cidb-grades.update', 'uses' => 'Api\PutDataApiController@cidbGrades' ));
    Route::delete('cidb-grades/{Id}', array('as' => 'api.cidb-grades.delete', 'uses' => 'Api\DeleteDataApiController@cidbGrades' ));

    //assign_companies_logs
    Route::get('assign-companies-logs', array('as' => 'api.assign-companies-logs', 'uses' => 'Api\GetDataApiController@assignCompaniesLogs' ));
    Route::post('assign-companies-logs', array('as' => 'api.assign-companies-logs.create', 'uses' => 'Api\PostDataApiController@assignCompaniesLogs' ));
    Route::put('assign-companies-logs/{Id}', array('as' => 'api.assign-companies-logs.update', 'uses' => 'Api\PutDataApiController@assignCompaniesLogs' ));
    Route::delete('assign-companies-logs/{Id}', array('as' => 'api.assign-companies-logs.delete', 'uses' => 'Api\DeleteDataApiController@assignCompaniesLogs' ));

    //assign_company_in_detail_logs
    Route::get('assign-company-in-detail-logs', array('as' => 'api.assign-company-in-detail-logs', 'uses' => 'Api\GetDataApiController@assignCompanyInDetailLogs' ));
    Route::post('assign-company-in-detail-logs', array('as' => 'api.assign-company-in-detail-logs.create', 'uses' => 'Api\PostDataApiController@assignCompanyInDetailLogs' ));
    Route::put('assign-company-in-detail-logs/{Id}', array('as' => 'api.assign-company-in-detail-logs.update', 'uses' => 'Api\PutDataApiController@assignCompanyInDetailLogs' ));
    Route::delete('assign-company-in-detail-logs/{Id}', array('as' => 'api.assign-company-in-detail-logs.delete', 'uses' => 'Api\DeleteDataApiController@assignCompanyInDetailLogs' ));

    //authentication_logs
    Route::get('authentication-logs', array('as' => 'api.authentication-logs', 'uses' => 'Api\GetDataApiController@authenticationLogs' ));
    Route::post('authentication-logs', array('as' => 'api.authentication-logs.create', 'uses' => 'Api\PostDataApiController@authenticationLogs' ));
    Route::put('authentication-logs/{Id}', array('as' => 'api.authentication-logs.update', 'uses' => 'Api\PutDataApiController@authenticationLogs' ));
    Route::delete('authentication-logs/{Id}', array('as' => 'api.authentication-logs.delete', 'uses' => 'Api\DeleteDataApiController@authenticationLogs' ));

    //calendars
    Route::get('calendars', array('as' => 'api.calendars', 'uses' => 'Api\GetDataApiController@calendars' ));
    Route::post('calendars', array('as' => 'api.calendars.create', 'uses' => 'Api\PostDataApiController@calendars' ));
    Route::put('calendars/{Id}', array('as' => 'api.calendars.update', 'uses' => 'Api\PutDataApiController@calendars' ));
    Route::delete('calendars/{Id}', array('as' => 'api.calendars.delete', 'uses' => 'Api\DeleteDataApiController@calendars' ));

    //claim_certificate_invoice_information_update_logs
    Route::get('claim-certificate-invoice-information-update-logs', array('as' => 'api.claim-certificate-invoice-information-update-logs', 'uses' => 'Api\GetDataApiController@claimCertificateInvoiceInformationUpdateLogs' ));
    Route::post('claim-certificate-invoice-information-update-logs', array('as' => 'api.claim-certificate-invoice-information-update-logs.create', 'uses' => 'Api\PostDataApiController@claimCertificateInvoiceInformationUpdateLogs' ));
    Route::put('claim-certificate-invoice-information-update-logs/{Id}', array('as' => 'api.claim-certificate-invoice-information-update-logs.update', 'uses' => 'Api\PutDataApiController@claimCertificateInvoiceInformationUpdateLogs' ));
    Route::delete('claim-certificate-invoice-information-update-logs/{Id}', array('as' => 'api.claim-certificate-invoice-information-update-logs.delete', 'uses' => 'Api\DeleteDataApiController@claimCertificateInvoiceInformationUpdateLogs' ));

    //building_information_modelling_levels
    Route::get('building-information-modelling-levels', array('as' => 'api.building-information-modelling-levels', 'uses' => 'Api\GetDataApiController@buildingInformationModellingLevels' ));
    Route::post('building-information-modelling-levels', array('as' => 'api.building-information-modelling-levels.create', 'uses' => 'Api\PostDataApiController@buildingInformationModellingLevels' ));
    Route::put('building-information-modelling-levels/{Id}', array('as' => 'api.building-information-modelling-levels.update', 'uses' => 'Api\PutDataApiController@buildingInformationModellingLevels' ));
    Route::delete('building-information-modelling-levels/{Id}', array('as' => 'api.building-information-modelling-levels.delete', 'uses' => 'Api\DeleteDataApiController@buildingInformationModellingLevels' ));

    //business_entity_types
    Route::get('business-entity-types', array('as' => 'api.business-entity-types', 'uses' => 'Api\GetDataApiController@businessEntityTypes' ));
    Route::post('business-entity-types', array('as' => 'api.business-entity-types.create', 'uses' => 'Api\PostDataApiController@businessEntityTypes' ));
    Route::put('business-entity-types/{Id}', array('as' => 'api.business-entity-types.update', 'uses' => 'Api\PutDataApiController@businessEntityTypes' ));
    Route::delete('business-entity-types/{Id}', array('as' => 'api.business-entity-types.delete', 'uses' => 'Api\DeleteDataApiController@businessEntityTypes' ));

    //cidb_codes
    Route::get('cidb-codes', array('as' => 'api.cidb-codes', 'uses' => 'Api\GetDataApiController@cidbCodes' ));
    Route::post('cidb-codes', array('as' => 'api.cidb-codes.create', 'uses' => 'Api\PostDataApiController@cidbCodes' ));
    Route::put('cidb-codes/{Id}', array('as' => 'api.cidb-codes.update', 'uses' => 'Api\PutDataApiController@cidbCodes' ));
    Route::delete('cidb-codes/{Id}', array('as' => 'api.cidb-codes.delete', 'uses' => 'Api\DeleteDataApiController@cidbCodes' ));

    //company_cidb_code
    Route::get('company-cidb-code', array('as' => 'api.company-cidb-code', 'uses' => 'Api\GetDataApiController@companyCidbCode' ));
    Route::post('company-cidb-code', array('as' => 'api.company-cidb-code.create', 'uses' => 'Api\PostDataApiController@companyCidbCode' ));
    Route::put('company-cidb-code/{Id}', array('as' => 'api.company-cidb-code.update', 'uses' => 'Api\PutDataApiController@companyCidbCode' ));
    Route::delete('company-cidb-code/{Id}', array('as' => 'api.company-cidb-code.delete', 'uses' => 'Api\DeleteDataApiController@companyCidbCode' ));

    //claim_certificate_payment_notification_logs
    Route::get('claim-certificate-payment-notification-logs', array('as' => 'api.claim-certificate-payment-notification-logs', 'uses' => 'Api\GetDataApiController@claimCertificatePaymentNotificationLogs' ));
    Route::post('claim-certificate-payment-notification-logs', array('as' => 'api.claim-certificate-payment-notification-logs.create', 'uses' => 'Api\PostDataApiController@claimCertificatePaymentNotificationLogs' ));
    Route::put('claim-certificate-payment-notification-logs/{Id}', array('as' => 'api.claim-certificate-payment-notification-logs.update', 'uses' => 'Api\PutDataApiController@claimCertificatePaymentNotificationLogs' ));
    Route::delete('claim-certificate-payment-notification-logs/{Id}', array('as' => 'api.claim-certificate-payment-notification-logs.delete', 'uses' => 'Api\DeleteDataApiController@claimCertificatePaymentNotificationLogs' ));

    //company_detail_attachment_settings
    Route::get('company-detail-attachment-settings', array('as' => 'api.company-detail-attachment-settings', 'uses' => 'Api\GetDataApiController@companyDetailAttachmentSettings' ));
    Route::post('company-detail-attachment-settings', array('as' => 'api.company-detail-attachment-settings.create', 'uses' => 'Api\PostDataApiController@companyDetailAttachmentSettings' ));
    Route::put('company-detail-attachment-settings/{Id}', array('as' => 'api.company-detail-attachment-settings.update', 'uses' => 'Api\PutDataApiController@companyDetailAttachmentSettings' ));
    Route::delete('company-detail-attachment-settings/{Id}', array('as' => 'api.company-detail-attachment-settings.delete', 'uses' => 'Api\DeleteDataApiController@companyDetailAttachmentSettings' ));

    //claim_certificate_payments
    Route::get('claim-certificate-payments', array('as' => 'api.claim-certificate-payments', 'uses' => 'Api\GetDataApiController@claimCertificatePayments' ));
    Route::post('claim-certificate-payments', array('as' => 'api.claim-certificate-payments.create', 'uses' => 'Api\PostDataApiController@claimCertificatePayments' ));
    Route::put('claim-certificate-payments/{Id}', array('as' => 'api.claim-certificate-payments.update', 'uses' => 'Api\PutDataApiController@claimCertificatePayments' ));
    Route::delete('claim-certificate-payments/{Id}', array('as' => 'api.claim-certificate-payments.delete', 'uses' => 'Api\DeleteDataApiController@claimCertificatePayments' ));

    //claim_certificate_print_logs
    Route::get('claim-certificate-print-logs', array('as' => 'api.claim-certificate-print-logs', 'uses' => 'Api\GetDataApiController@claimCertificatePrintLogs' ));
    Route::post('claim-certificate-print-logs', array('as' => 'api.claim-certificate-print-logs.create', 'uses' => 'Api\PostDataApiController@claimCertificatePrintLogs' ));
    Route::put('claim-certificate-print-logs/{Id}', array('as' => 'api.claim-certificate-print-logs.update', 'uses' => 'Api\PutDataApiController@claimCertificatePrintLogs' ));
    Route::delete('claim-certificate-print-logs/{Id}', array('as' => 'api.claim-certificate-print-logs.delete', 'uses' => 'Api\DeleteDataApiController@claimCertificatePrintLogs' ));

    //clauses
    Route::get('clauses', array('as' => 'api.clauses', 'uses' => 'Api\GetDataApiController@clauses' ));
    Route::post('clauses', array('as' => 'api.clauses.create', 'uses' => 'Api\PostDataApiController@clauses' ));
    Route::put('clauses/{Id}', array('as' => 'api.clauses.update', 'uses' => 'Api\PutDataApiController@clauses' ));
    Route::delete('clauses/{Id}', array('as' => 'api.clauses.delete', 'uses' => 'Api\DeleteDataApiController@clauses' ));

    //company_imported_users
    Route::get('company-imported-users', array('as' => 'api.company-imported-users', 'uses' => 'Api\GetDataApiController@companyImportedUsers' ));
    Route::post('company-imported-users', array('as' => 'api.company-imported-users.create', 'uses' => 'Api\PostDataApiController@companyImportedUsers' ));
    Route::put('company-imported-users/{Id}', array('as' => 'api.company-imported-users.update', 'uses' => 'Api\PutDataApiController@companyImportedUsers' ));
    Route::delete('company-imported-users/{Id}', array('as' => 'api.company-imported-users.delete', 'uses' => 'Api\DeleteDataApiController@companyImportedUsers' ));

    //company_imported_users_log
    Route::get('company-imported-users-log', array('as' => 'api.company-imported-users-log', 'uses' => 'Api\GetDataApiController@companyImportedUsersLog' ));
    Route::post('company-imported-users-log', array('as' => 'api.company-imported-users-log.create', 'uses' => 'Api\PostDataApiController@companyImportedUsersLog' ));
    Route::put('company-imported-users-log/{Id}', array('as' => 'api.company-imported-users-log.update', 'uses' => 'Api\PutDataApiController@companyImportedUsersLog' ));
    Route::delete('company-imported-users-log/{Id}', array('as' => 'api.company-imported-users-log.delete', 'uses' => 'Api\DeleteDataApiController@companyImportedUsersLog' ));

    //company_personnel_settings
    Route::get('company-personnel-settings', array('as' => 'api.company-personnel-settings', 'uses' => 'Api\GetDataApiController@companyPersonnelSettings' ));
    Route::post('company-personnel-settings', array('as' => 'api.company-personnel-settings.create', 'uses' => 'Api\PostDataApiController@companyPersonnelSettings' ));
    Route::put('company-personnel-settings/{Id}', array('as' => 'api.company-personnel-settings.update', 'uses' => 'Api\PutDataApiController@companyPersonnelSettings' ));
    Route::delete('company-personnel-settings/{Id}', array('as' => 'api.company-personnel-settings.delete', 'uses' => 'Api\DeleteDataApiController@companyPersonnelSettings' ));

    //company_tender_calling_tender_information
    Route::get('company-tender-calling-tender-information', array('as' => 'api.company-tender-calling-tender-information', 'uses' => 'Api\GetDataApiController@companyTenderCallingTenderInformation' ));
    Route::post('company-tender-calling-tender-information', array('as' => 'api.company-tender-calling-tender-information.create', 'uses' => 'Api\PostDataApiController@companyTenderCallingTenderInformation' ));
    Route::put('company-tender-calling-tender-information/{Id}', array('as' => 'api.company-tender-calling-tender-information.update', 'uses' => 'Api\PutDataApiController@companyTenderCallingTenderInformation' ));
    Route::delete('company-tender-calling-tender-information/{Id}', array('as' => 'api.company-tender-calling-tender-information.delete', 'uses' => 'Api\DeleteDataApiController@companyTenderCallingTenderInformation' ));

    //company_tender_lot_information
    Route::get('company-tender-lot-information', array('as' => 'api.company-tender-lot-information', 'uses' => 'Api\GetDataApiController@companyTenderLotInformation' ));
    Route::post('company-tender-lot-information', array('as' => 'api.company-tender-lot-information.create', 'uses' => 'Api\PostDataApiController@companyTenderLotInformation' ));
    Route::put('company-tender-lot-information/{Id}', array('as' => 'api.company-tender-lot-information.update', 'uses' => 'Api\PutDataApiController@companyTenderLotInformation' ));
    Route::delete('company-tender-lot-information/{Id}', array('as' => 'api.company-tender-lot-information.delete', 'uses' => 'Api\DeleteDataApiController@companyTenderLotInformation' ));

    //company_tender_rot_information
    Route::get('company-tender-rot-information', array('as' => 'api.company-tender-rot-information', 'uses' => 'Api\GetDataApiController@companyTenderRotInformation' ));
    Route::post('company-tender-rot-information', array('as' => 'api.company-tender-rot-information.create', 'uses' => 'Api\PostDataApiController@companyTenderRotInformation' ));
    Route::put('company-tender-rot-information/{Id}', array('as' => 'api.company-tender-rot-information.update', 'uses' => 'Api\PutDataApiController@companyTenderRotInformation' ));
    Route::delete('company-tender-rot-information/{Id}', array('as' => 'api.company-tender-rot-information.delete', 'uses' => 'Api\DeleteDataApiController@companyTenderRotInformation' ));

    //company_tender_tender_alternatives
    Route::get('company-tender-tender-alternatives', array('as' => 'api.company-tender-tender-alternatives', 'uses' => 'Api\GetDataApiController@companyTenderTenderAlternatives' ));
    Route::post('company-tender-tender-alternatives', array('as' => 'api.company-tender-tender-alternatives.create', 'uses' => 'Api\PostDataApiController@companyTenderTenderAlternatives' ));
    Route::put('company-tender-tender-alternatives/{Id}', array('as' => 'api.company-tender-tender-alternatives.update', 'uses' => 'Api\PutDataApiController@companyTenderTenderAlternatives' ));
    Route::delete('company-tender-tender-alternatives/{Id}', array('as' => 'api.company-tender-tender-alternatives.delete', 'uses' => 'Api\DeleteDataApiController@companyTenderTenderAlternatives' ));

    //company_project
    Route::get('company-project', array('as' => 'api.company-project', 'uses' => 'Api\GetDataApiController@companyProject' ));
    Route::post('company-project', array('as' => 'api.company-project.create', 'uses' => 'Api\PostDataApiController@companyProject' ));
    Route::put('company-project/{Id}', array('as' => 'api.company-project.update', 'uses' => 'Api\PutDataApiController@companyProject' ));
    Route::delete('company-project/{Id}', array('as' => 'api.company-project.delete', 'uses' => 'Api\DeleteDataApiController@companyProject' ));

    //company_property_developers
    Route::get('company-property-developers', array('as' => 'api.company-property-developers', 'uses' => 'Api\GetDataApiController@companyPropertyDevelopers' ));
    Route::post('company-property-developers', array('as' => 'api.company-property-developers.create', 'uses' => 'Api\PostDataApiController@companyPropertyDevelopers' ));
    Route::put('company-property-developers/{Id}', array('as' => 'api.company-property-developers.update', 'uses' => 'Api\PutDataApiController@companyPropertyDevelopers' ));
    Route::delete('company-property-developers/{Id}', array('as' => 'api.company-property-developers.delete', 'uses' => 'Api\DeleteDataApiController@companyPropertyDevelopers' ));

    //company_temporary_details
    Route::get('company-temporary-details', array('as' => 'api.company-temporary-details', 'uses' => 'Api\GetDataApiController@companyTemporaryDetails' ));
    Route::post('company-temporary-details', array('as' => 'api.company-temporary-details.create', 'uses' => 'Api\PostDataApiController@companyTemporaryDetails' ));
    Route::put('company-temporary-details/{Id}', array('as' => 'api.company-temporary-details.update', 'uses' => 'Api\PutDataApiController@companyTemporaryDetails' ));
    Route::delete('company-temporary-details/{Id}', array('as' => 'api.company-temporary-details.delete', 'uses' => 'Api\DeleteDataApiController@companyTemporaryDetails' ));

    //company_tender
    Route::get('company-tender', array('as' => 'api.company-tender', 'uses' => 'Api\GetDataApiController@companyTender' ));
    Route::post('company-tender', array('as' => 'api.company-tender.create', 'uses' => 'Api\PostDataApiController@companyTender' ));
    Route::put('company-tender/{Id}', array('as' => 'api.company-tender.update', 'uses' => 'Api\PutDataApiController@companyTender' ));
    Route::delete('company-tender/{Id}', array('as' => 'api.company-tender.delete', 'uses' => 'Api\DeleteDataApiController@companyTender' ));

    //company_vendor_category
    Route::get('company-vendor-category', array('as' => 'api.company-vendor-category', 'uses' => 'Api\GetDataApiController@companyVendorCategory' ));
    Route::post('company-vendor-category', array('as' => 'api.company-vendor-category.create', 'uses' => 'Api\PostDataApiController@companyVendorCategory' ));
    Route::put('company-vendor-category/{Id}', array('as' => 'api.company-vendor-category.update', 'uses' => 'Api\PutDataApiController@companyVendorCategory' ));
    Route::delete('company-vendor-category/{Id}', array('as' => 'api.company-vendor-category.delete', 'uses' => 'Api\DeleteDataApiController@companyVendorCategory' ));

    //consultant_management_approval_document_section_e
    Route::get('consultant-management-approval-document-section-e', array('as' => 'api.consultant-management-approval-document-section-e', 'uses' => 'Api\GetDataApiController@consultantManagementApprovalDocumentSectionE' ));
    Route::post('consultant-management-approval-document-section-e', array('as' => 'api.consultant-management-approval-document-section-e.create', 'uses' => 'Api\PostDataApiController@consultantManagementApprovalDocumentSectionE' ));
    Route::put('consultant-management-approval-document-section-e/{Id}', array('as' => 'api.consultant-management-approval-document-section-e.update', 'uses' => 'Api\PutDataApiController@consultantManagementApprovalDocumentSectionE' ));
    Route::delete('consultant-management-approval-document-section-e/{Id}', array('as' => 'api.consultant-management-approval-document-section-e.delete', 'uses' => 'Api\DeleteDataApiController@consultantManagementApprovalDocumentSectionE' ));

    //consultant_management_consultant_attachments
    Route::get('consultant-management-consultant-attachments', array('as' => 'api.consultant-management-consultant-attachments', 'uses' => 'Api\GetDataApiController@consultantManagementConsultantAttachments' ));
    Route::post('consultant-management-consultant-attachments', array('as' => 'api.consultant-management-consultant-attachments.create', 'uses' => 'Api\PostDataApiController@consultantManagementConsultantAttachments' ));
    Route::put('consultant-management-consultant-attachments/{Id}', array('as' => 'api.consultant-management-consultant-attachments.update', 'uses' => 'Api\PutDataApiController@consultantManagementConsultantAttachments' ));
    Route::delete('consultant-management-consultant-attachments/{Id}', array('as' => 'api.consultant-management-consultant-attachments.delete', 'uses' => 'Api\DeleteDataApiController@consultantManagementConsultantAttachments' ));

    //consultant_management_consultant_questionnaires
    Route::get('consultant-management-consultant-questionnaires', array('as' => 'api.consultant-management-consultant-questionnaires', 'uses' => 'Api\GetDataApiController@consultantManagementConsultantQuestionnaires' ));
    Route::post('consultant-management-consultant-questionnaires', array('as' => 'api.consultant-management-consultant-questionnaires.create', 'uses' => 'Api\PostDataApiController@consultantManagementConsultantQuestionnaires' ));
    Route::put('consultant-management-consultant-questionnaires/{Id}', array('as' => 'api.consultant-management-consultant-questionnaires.update', 'uses' => 'Api\PutDataApiController@consultantManagementConsultantQuestionnaires' ));
    Route::delete('consultant-management-consultant-questionnaires/{Id}', array('as' => 'api.consultant-management-consultant-questionnaires.delete', 'uses' => 'Api\DeleteDataApiController@consultantManagementConsultantQuestionnaires' ));

    //consultant_management_calling_rfp
    Route::get('consultant-management-calling-rfp', array('as' => 'api.consultant-management-calling-rfp', 'uses' => 'Api\GetDataApiController@consultantManagementCallingRfp' ));
    Route::post('consultant-management-calling-rfp', array('as' => 'api.consultant-management-calling-rfp.create', 'uses' => 'Api\PostDataApiController@consultantManagementCallingRfp' ));
    Route::put('consultant-management-calling-rfp/{Id}', array('as' => 'api.consultant-management-calling-rfp.update', 'uses' => 'Api\PutDataApiController@consultantManagementCallingRfp' ));
    Route::delete('consultant-management-calling-rfp/{Id}', array('as' => 'api.consultant-management-calling-rfp.delete', 'uses' => 'Api\DeleteDataApiController@consultantManagementCallingRfp' ));

    //consultant_management_company_role_logs
    Route::get('consultant-management-company-role-logs', array('as' => 'api.consultant-management-company-role-logs', 'uses' => 'Api\GetDataApiController@consultantManagementCompanyRoleLogs' ));
    Route::post('consultant-management-company-role-logs', array('as' => 'api.consultant-management-company-role-logs.create', 'uses' => 'Api\PostDataApiController@consultantManagementCompanyRoleLogs' ));
    Route::put('consultant-management-company-role-logs/{Id}', array('as' => 'api.consultant-management-company-role-logs.update', 'uses' => 'Api\PutDataApiController@consultantManagementCompanyRoleLogs' ));
    Route::delete('consultant-management-company-role-logs/{Id}', array('as' => 'api.consultant-management-company-role-logs.delete', 'uses' => 'Api\DeleteDataApiController@consultantManagementCompanyRoleLogs' ));

    //consultant_management_approval_documents
    Route::get('consultant-management-approval-documents', array('as' => 'api.consultant-management-approval-documents', 'uses' => 'Api\GetDataApiController@consultantManagementApprovalDocuments' ));
    Route::post('consultant-management-approval-documents', array('as' => 'api.consultant-management-approval-documents.create', 'uses' => 'Api\PostDataApiController@consultantManagementApprovalDocuments' ));
    Route::put('consultant-management-approval-documents/{Id}', array('as' => 'api.consultant-management-approval-documents.update', 'uses' => 'Api\PutDataApiController@consultantManagementApprovalDocuments' ));
    Route::delete('consultant-management-approval-documents/{Id}', array('as' => 'api.consultant-management-approval-documents.delete', 'uses' => 'Api\DeleteDataApiController@consultantManagementApprovalDocuments' ));

    //consultant_management_approval_document_section_appendix
    Route::get('consultant-management-approval-document-section-appendix', array('as' => 'api.consultant-management-approval-document-section-appendix', 'uses' => 'Api\GetDataApiController@consultantManagementApprovalDocumentSectionAppendix' ));
    Route::post('consultant-management-approval-document-section-appendix', array('as' => 'api.consultant-management-approval-document-section-appendix.create', 'uses' => 'Api\PostDataApiController@consultantManagementApprovalDocumentSectionAppendix' ));
    Route::put('consultant-management-approval-document-section-appendix/{Id}', array('as' => 'api.consultant-management-approval-document-section-appendix.update', 'uses' => 'Api\PutDataApiController@consultantManagementApprovalDocumentSectionAppendix' ));
    Route::delete('consultant-management-approval-document-section-appendix/{Id}', array('as' => 'api.consultant-management-approval-document-section-appendix.delete', 'uses' => 'Api\DeleteDataApiController@consultantManagementApprovalDocumentSectionAppendix' ));

    //consultant_management_approval_document_section_c
    Route::get('consultant-management-approval-document-section-c', array('as' => 'api.consultant-management-approval-document-section-c', 'uses' => 'Api\GetDataApiController@consultantManagementApprovalDocumentSectionC' ));
    Route::post('consultant-management-approval-document-section-c', array('as' => 'api.consultant-management-approval-document-section-c.create', 'uses' => 'Api\PostDataApiController@consultantManagementApprovalDocumentSectionC' ));
    Route::put('consultant-management-approval-document-section-c/{Id}', array('as' => 'api.consultant-management-approval-document-section-c.update', 'uses' => 'Api\PutDataApiController@consultantManagementApprovalDocumentSectionC' ));
    Route::delete('consultant-management-approval-document-section-c/{Id}', array('as' => 'api.consultant-management-approval-document-section-c.delete', 'uses' => 'Api\DeleteDataApiController@consultantManagementApprovalDocumentSectionC' ));

    //consultant_management_approval_document_section_d
    Route::get('consultant-management-approval-document-section-d', array('as' => 'api.consultant-management-approval-document-section-d', 'uses' => 'Api\GetDataApiController@consultantManagementApprovalDocumentSectionD' ));
    Route::post('consultant-management-approval-document-section-d', array('as' => 'api.consultant-management-approval-document-section-d.create', 'uses' => 'Api\PostDataApiController@consultantManagementApprovalDocumentSectionD' ));
    Route::put('consultant-management-approval-document-section-d/{Id}', array('as' => 'api.consultant-management-approval-document-section-d.update', 'uses' => 'Api\PutDataApiController@consultantManagementApprovalDocumentSectionD' ));
    Route::delete('consultant-management-approval-document-section-d/{Id}', array('as' => 'api.consultant-management-approval-document-section-d.delete', 'uses' => 'Api\DeleteDataApiController@consultantManagementApprovalDocumentSectionD' ));

    //consultant_management_approval_document_section_b
    Route::get('consultant-management-approval-document-section-b', array('as' => 'api.consultant-management-approval-document-section-b', 'uses' => 'Api\GetDataApiController@consultantManagementApprovalDocumentSectionB' ));
    Route::post('consultant-management-approval-document-section-b', array('as' => 'api.consultant-management-approval-document-section-b.create', 'uses' => 'Api\PostDataApiController@consultantManagementApprovalDocumentSectionB' ));
    Route::put('consultant-management-approval-document-section-b/{Id}', array('as' => 'api.consultant-management-approval-document-section-b.update', 'uses' => 'Api\PutDataApiController@consultantManagementApprovalDocumentSectionB' ));
    Route::delete('consultant-management-approval-document-section-b/{Id}', array('as' => 'api.consultant-management-approval-document-section-b.delete', 'uses' => 'Api\DeleteDataApiController@consultantManagementApprovalDocumentSectionB' ));

    //consultant_management_approval_document_verifiers
    Route::get('consultant-management-approval-document-verifiers', array('as' => 'api.consultant-management-approval-document-verifiers', 'uses' => 'Api\GetDataApiController@consultantManagementApprovalDocumentVerifiers' ));
    Route::post('consultant-management-approval-document-verifiers', array('as' => 'api.consultant-management-approval-document-verifiers.create', 'uses' => 'Api\PostDataApiController@consultantManagementApprovalDocumentVerifiers' ));
    Route::put('consultant-management-approval-document-verifiers/{Id}', array('as' => 'api.consultant-management-approval-document-verifiers.update', 'uses' => 'Api\PutDataApiController@consultantManagementApprovalDocumentVerifiers' ));
    Route::delete('consultant-management-approval-document-verifiers/{Id}', array('as' => 'api.consultant-management-approval-document-verifiers.delete', 'uses' => 'Api\DeleteDataApiController@consultantManagementApprovalDocumentVerifiers' ));

    //consultant_management_approval_document_verifier_versions
    Route::get('consultant-management-approval-document-verifier-versions', array('as' => 'api.consultant-management-approval-document-verifier-versions', 'uses' => 'Api\GetDataApiController@consultantManagementApprovalDocumentVerifierVersions' ));
    Route::post('consultant-management-approval-document-verifier-versions', array('as' => 'api.consultant-management-approval-document-verifier-versions.create', 'uses' => 'Api\PostDataApiController@consultantManagementApprovalDocumentVerifierVersions' ));
    Route::put('consultant-management-approval-document-verifier-versions/{Id}', array('as' => 'api.consultant-management-approval-document-verifier-versions.update', 'uses' => 'Api\PutDataApiController@consultantManagementApprovalDocumentVerifierVersions' ));
    Route::delete('consultant-management-approval-document-verifier-versions/{Id}', array('as' => 'api.consultant-management-approval-document-verifier-versions.delete', 'uses' => 'Api\DeleteDataApiController@consultantManagementApprovalDocumentVerifierVersions' ));

    //consultant_management_consultant_questionnaire_replies
    Route::get('consultant-management-consultant-questionnaire-replies', array('as' => 'api.consultant-management-consultant-questionnaire-replies', 'uses' => 'Api\GetDataApiController@consultantManagementConsultantQuestionnaireReplies' ));
    Route::post('consultant-management-consultant-questionnaire-replies', array('as' => 'api.consultant-management-consultant-questionnaire-replies.create', 'uses' => 'Api\PostDataApiController@consultantManagementConsultantQuestionnaireReplies' ));
    Route::put('consultant-management-consultant-questionnaire-replies/{Id}', array('as' => 'api.consultant-management-consultant-questionnaire-replies.update', 'uses' => 'Api\PutDataApiController@consultantManagementConsultantQuestionnaireReplies' ));
    Route::delete('consultant-management-consultant-questionnaire-replies/{Id}', array('as' => 'api.consultant-management-consultant-questionnaire-replies.delete', 'uses' => 'Api\DeleteDataApiController@consultantManagementConsultantQuestionnaireReplies' ));

    //consultant_management_calling_rfp_verifiers
    Route::get('consultant-management-calling-rfp-verifiers', array('as' => 'api.consultant-management-calling-rfp-verifiers', 'uses' => 'Api\GetDataApiController@consultantManagementCallingRfpVerifiers' ));
    Route::post('consultant-management-calling-rfp-verifiers', array('as' => 'api.consultant-management-calling-rfp-verifiers.create', 'uses' => 'Api\PostDataApiController@consultantManagementCallingRfpVerifiers' ));
    Route::put('consultant-management-calling-rfp-verifiers/{Id}', array('as' => 'api.consultant-management-calling-rfp-verifiers.update', 'uses' => 'Api\PutDataApiController@consultantManagementCallingRfpVerifiers' ));
    Route::delete('consultant-management-calling-rfp-verifiers/{Id}', array('as' => 'api.consultant-management-calling-rfp-verifiers.delete', 'uses' => 'Api\DeleteDataApiController@consultantManagementCallingRfpVerifiers' ));

    //consultant_management_calling_rfp_companies
    Route::get('consultant-management-calling-rfp-companies', array('as' => 'api.consultant-management-calling-rfp-companies', 'uses' => 'Api\GetDataApiController@consultantManagementCallingRfpCompanies' ));
    Route::post('consultant-management-calling-rfp-companies', array('as' => 'api.consultant-management-calling-rfp-companies.create', 'uses' => 'Api\PostDataApiController@consultantManagementCallingRfpCompanies' ));
    Route::put('consultant-management-calling-rfp-companies/{Id}', array('as' => 'api.consultant-management-calling-rfp-companies.update', 'uses' => 'Api\PutDataApiController@consultantManagementCallingRfpCompanies' ));
    Route::delete('consultant-management-calling-rfp-companies/{Id}', array('as' => 'api.consultant-management-calling-rfp-companies.delete', 'uses' => 'Api\DeleteDataApiController@consultantManagementCallingRfpCompanies' ));

    //consultant_management_call_rfp_verifier_versions
    Route::get('consultant-management-call-rfp-verifier-versions', array('as' => 'api.consultant-management-call-rfp-verifier-versions', 'uses' => 'Api\GetDataApiController@consultantManagementCallRfpVerifierVersions' ));
    Route::post('consultant-management-call-rfp-verifier-versions', array('as' => 'api.consultant-management-call-rfp-verifier-versions.create', 'uses' => 'Api\PostDataApiController@consultantManagementCallRfpVerifierVersions' ));
    Route::put('consultant-management-call-rfp-verifier-versions/{Id}', array('as' => 'api.consultant-management-call-rfp-verifier-versions.update', 'uses' => 'Api\PutDataApiController@consultantManagementCallRfpVerifierVersions' ));
    Route::delete('consultant-management-call-rfp-verifier-versions/{Id}', array('as' => 'api.consultant-management-call-rfp-verifier-versions.delete', 'uses' => 'Api\DeleteDataApiController@consultantManagementCallRfpVerifierVersions' ));

    //consultant_management_consultant_rfp_questionnaire_replies
    Route::get('consultant-management-consultant-rfp-questionnaire-replies', array('as' => 'api.consultant-management-consultant-rfp-questionnaire-replies', 'uses' => 'Api\GetDataApiController@consultantManagementConsultantRfpQuestionnaireReplies' ));
    Route::post('consultant-management-consultant-rfp-questionnaire-replies', array('as' => 'api.consultant-management-consultant-rfp-questionnaire-replies.create', 'uses' => 'Api\PostDataApiController@consultantManagementConsultantRfpQuestionnaireReplies' ));
    Route::put('consultant-management-consultant-rfp-questionnaire-replies/{Id}', array('as' => 'api.consultant-management-consultant-rfp-questionnaire-replies.update', 'uses' => 'Api\PutDataApiController@consultantManagementConsultantRfpQuestionnaireReplies' ));
    Route::delete('consultant-management-consultant-rfp-questionnaire-replies/{Id}', array('as' => 'api.consultant-management-consultant-rfp-questionnaire-replies.delete', 'uses' => 'Api\DeleteDataApiController@consultantManagementConsultantRfpQuestionnaireReplies' ));

    //consultant_management_consultant_rfp
    Route::get('consultant-management-consultant-rfp', array('as' => 'api.consultant-management-consultant-rfp', 'uses' => 'Api\GetDataApiController@consultantManagementConsultantRfp' ));
    Route::post('consultant-management-consultant-rfp', array('as' => 'api.consultant-management-consultant-rfp.create', 'uses' => 'Api\PostDataApiController@consultantManagementConsultantRfp' ));
    Route::put('consultant-management-consultant-rfp/{Id}', array('as' => 'api.consultant-management-consultant-rfp.update', 'uses' => 'Api\PutDataApiController@consultantManagementConsultantRfp' ));
    Route::delete('consultant-management-consultant-rfp/{Id}', array('as' => 'api.consultant-management-consultant-rfp.delete', 'uses' => 'Api\DeleteDataApiController@consultantManagementConsultantRfp' ));

    //consultant_management_consultant_rfp_reply_attachments
    Route::get('consultant-management-consultant-rfp-reply-attachments', array('as' => 'api.consultant-management-consultant-rfp-reply-attachments', 'uses' => 'Api\GetDataApiController@consultantManagementConsultantRfpReplyAttachments' ));
    Route::post('consultant-management-consultant-rfp-reply-attachments', array('as' => 'api.consultant-management-consultant-rfp-reply-attachments.create', 'uses' => 'Api\PostDataApiController@consultantManagementConsultantRfpReplyAttachments' ));
    Route::put('consultant-management-consultant-rfp-reply-attachments/{Id}', array('as' => 'api.consultant-management-consultant-rfp-reply-attachments.update', 'uses' => 'Api\PutDataApiController@consultantManagementConsultantRfpReplyAttachments' ));
    Route::delete('consultant-management-consultant-rfp-reply-attachments/{Id}', array('as' => 'api.consultant-management-consultant-rfp-reply-attachments.delete', 'uses' => 'Api\DeleteDataApiController@consultantManagementConsultantRfpReplyAttachments' ));

    //consultant_management_consultant_rfp_common_information
    Route::get('consultant-management-consultant-rfp-common-information', array('as' => 'api.consultant-management-consultant-rfp-common-information', 'uses' => 'Api\GetDataApiController@consultantManagementConsultantRfpCommonInformation' ));
    Route::post('consultant-management-consultant-rfp-common-information', array('as' => 'api.consultant-management-consultant-rfp-common-information.create', 'uses' => 'Api\PostDataApiController@consultantManagementConsultantRfpCommonInformation' ));
    Route::put('consultant-management-consultant-rfp-common-information/{Id}', array('as' => 'api.consultant-management-consultant-rfp-common-information.update', 'uses' => 'Api\PutDataApiController@consultantManagementConsultantRfpCommonInformation' ));
    Route::delete('consultant-management-consultant-rfp-common-information/{Id}', array('as' => 'api.consultant-management-consultant-rfp-common-information.delete', 'uses' => 'Api\DeleteDataApiController@consultantManagementConsultantRfpCommonInformation' ));

    //consultant_management_letter_of_award_clauses
    Route::get('consultant-management-letter-of-award-clauses', array('as' => 'api.consultant-management-letter-of-award-clauses', 'uses' => 'Api\GetDataApiController@consultantManagementLetterOfAwardClauses' ));
    Route::post('consultant-management-letter-of-award-clauses', array('as' => 'api.consultant-management-letter-of-award-clauses.create', 'uses' => 'Api\PostDataApiController@consultantManagementLetterOfAwardClauses' ));
    Route::put('consultant-management-letter-of-award-clauses/{Id}', array('as' => 'api.consultant-management-letter-of-award-clauses.update', 'uses' => 'Api\PutDataApiController@consultantManagementLetterOfAwardClauses' ));
    Route::delete('consultant-management-letter-of-award-clauses/{Id}', array('as' => 'api.consultant-management-letter-of-award-clauses.delete', 'uses' => 'Api\DeleteDataApiController@consultantManagementLetterOfAwardClauses' ));

    //consultant_management_letter_of_award_template_clauses
    Route::get('consultant-management-letter-of-award-template-clauses', array('as' => 'api.consultant-management-letter-of-award-template-clauses', 'uses' => 'Api\GetDataApiController@consultantManagementLetterOfAwardTemplateClauses' ));
    Route::post('consultant-management-letter-of-award-template-clauses', array('as' => 'api.consultant-management-letter-of-award-template-clauses.create', 'uses' => 'Api\PostDataApiController@consultantManagementLetterOfAwardTemplateClauses' ));
    Route::put('consultant-management-letter-of-award-template-clauses/{Id}', array('as' => 'api.consultant-management-letter-of-award-template-clauses.update', 'uses' => 'Api\PutDataApiController@consultantManagementLetterOfAwardTemplateClauses' ));
    Route::delete('consultant-management-letter-of-award-template-clauses/{Id}', array('as' => 'api.consultant-management-letter-of-award-template-clauses.delete', 'uses' => 'Api\DeleteDataApiController@consultantManagementLetterOfAwardTemplateClauses' ));

    //consultant_management_contracts
    Route::get('consultant-management-contracts', array('as' => 'api.consultant-management-contracts', 'uses' => 'Api\GetDataApiController@consultantManagementContracts' ));
    Route::post('consultant-management-contracts', array('as' => 'api.consultant-management-contracts.create', 'uses' => 'Api\PostDataApiController@consultantManagementContracts' ));
    Route::put('consultant-management-contracts/{Id}', array('as' => 'api.consultant-management-contracts.update', 'uses' => 'Api\PutDataApiController@consultantManagementContracts' ));
    Route::delete('consultant-management-contracts/{Id}', array('as' => 'api.consultant-management-contracts.delete', 'uses' => 'Api\DeleteDataApiController@consultantManagementContracts' ));

    //consultant_management_consultant_rfp_proposed_fees
    Route::get('consultant-management-consultant-rfp-proposed-fees', array('as' => 'api.consultant-management-consultant-rfp-proposed-fees', 'uses' => 'Api\GetDataApiController@consultantManagementConsultantRfpProposedFees' ));
    Route::post('consultant-management-consultant-rfp-proposed-fees', array('as' => 'api.consultant-management-consultant-rfp-proposed-fees.create', 'uses' => 'Api\PostDataApiController@consultantManagementConsultantRfpProposedFees' ));
    Route::put('consultant-management-consultant-rfp-proposed-fees/{Id}', array('as' => 'api.consultant-management-consultant-rfp-proposed-fees.update', 'uses' => 'Api\PutDataApiController@consultantManagementConsultantRfpProposedFees' ));
    Route::delete('consultant-management-consultant-rfp-proposed-fees/{Id}', array('as' => 'api.consultant-management-consultant-rfp-proposed-fees.delete', 'uses' => 'Api\DeleteDataApiController@consultantManagementConsultantRfpProposedFees' ));

    //consultant_management_consultant_users
    Route::get('consultant-management-consultant-users', array('as' => 'api.consultant-management-consultant-users', 'uses' => 'Api\GetDataApiController@consultantManagementConsultantUsers' ));
    Route::post('consultant-management-consultant-users', array('as' => 'api.consultant-management-consultant-users.create', 'uses' => 'Api\PostDataApiController@consultantManagementConsultantUsers' ));
    Route::put('consultant-management-consultant-users/{Id}', array('as' => 'api.consultant-management-consultant-users.update', 'uses' => 'Api\PutDataApiController@consultantManagementConsultantUsers' ));
    Route::delete('consultant-management-consultant-users/{Id}', array('as' => 'api.consultant-management-consultant-users.delete', 'uses' => 'Api\DeleteDataApiController@consultantManagementConsultantUsers' ));

    //consultant_management_exclude_attachment_settings
    Route::get('consultant-management-exclude-attachment-settings', array('as' => 'api.consultant-management-exclude-attachment-settings', 'uses' => 'Api\GetDataApiController@consultantManagementExcludeAttachmentSettings' ));
    Route::post('consultant-management-exclude-attachment-settings', array('as' => 'api.consultant-management-exclude-attachment-settings.create', 'uses' => 'Api\PostDataApiController@consultantManagementExcludeAttachmentSettings' ));
    Route::put('consultant-management-exclude-attachment-settings/{Id}', array('as' => 'api.consultant-management-exclude-attachment-settings.update', 'uses' => 'Api\PutDataApiController@consultantManagementExcludeAttachmentSettings' ));
    Route::delete('consultant-management-exclude-attachment-settings/{Id}', array('as' => 'api.consultant-management-exclude-attachment-settings.delete', 'uses' => 'Api\DeleteDataApiController@consultantManagementExcludeAttachmentSettings' ));

    //consultant_management_consultant_reply_attachments
    Route::get('consultant-management-consultant-reply-attachments', array('as' => 'api.consultant-management-consultant-reply-attachments', 'uses' => 'Api\GetDataApiController@consultantManagementConsultantReplyAttachments' ));
    Route::post('consultant-management-consultant-reply-attachments', array('as' => 'api.consultant-management-consultant-reply-attachments.create', 'uses' => 'Api\PostDataApiController@consultantManagementConsultantReplyAttachments' ));
    Route::put('consultant-management-consultant-reply-attachments/{Id}', array('as' => 'api.consultant-management-consultant-reply-attachments.update', 'uses' => 'Api\PutDataApiController@consultantManagementConsultantReplyAttachments' ));
    Route::delete('consultant-management-consultant-reply-attachments/{Id}', array('as' => 'api.consultant-management-consultant-reply-attachments.delete', 'uses' => 'Api\DeleteDataApiController@consultantManagementConsultantReplyAttachments' ));

    //consultant_management_consultant_rfp_attachments
    Route::get('consultant-management-consultant-rfp-attachments', array('as' => 'api.consultant-management-consultant-rfp-attachments', 'uses' => 'Api\GetDataApiController@consultantManagementConsultantRfpAttachments' ));
    Route::post('consultant-management-consultant-rfp-attachments', array('as' => 'api.consultant-management-consultant-rfp-attachments.create', 'uses' => 'Api\PostDataApiController@consultantManagementConsultantRfpAttachments' ));
    Route::put('consultant-management-consultant-rfp-attachments/{Id}', array('as' => 'api.consultant-management-consultant-rfp-attachments.update', 'uses' => 'Api\PutDataApiController@consultantManagementConsultantRfpAttachments' ));
    Route::delete('consultant-management-consultant-rfp-attachments/{Id}', array('as' => 'api.consultant-management-consultant-rfp-attachments.delete', 'uses' => 'Api\DeleteDataApiController@consultantManagementConsultantRfpAttachments' ));

    //consultant_management_exclude_questionnaires
    Route::get('consultant-management-exclude-questionnaires', array('as' => 'api.consultant-management-exclude-questionnaires', 'uses' => 'Api\GetDataApiController@consultantManagementExcludeQuestionnaires' ));
    Route::post('consultant-management-exclude-questionnaires', array('as' => 'api.consultant-management-exclude-questionnaires.create', 'uses' => 'Api\PostDataApiController@consultantManagementExcludeQuestionnaires' ));
    Route::put('consultant-management-exclude-questionnaires/{Id}', array('as' => 'api.consultant-management-exclude-questionnaires.update', 'uses' => 'Api\PutDataApiController@consultantManagementExcludeQuestionnaires' ));
    Route::delete('consultant-management-exclude-questionnaires/{Id}', array('as' => 'api.consultant-management-exclude-questionnaires.delete', 'uses' => 'Api\DeleteDataApiController@consultantManagementExcludeQuestionnaires' ));

    //consultant_management_letter_of_award_attachments
    Route::get('consultant-management-letter-of-award-attachments', array('as' => 'api.consultant-management-letter-of-award-attachments', 'uses' => 'Api\GetDataApiController@consultantManagementLetterOfAwardAttachments' ));
    Route::post('consultant-management-letter-of-award-attachments', array('as' => 'api.consultant-management-letter-of-award-attachments.create', 'uses' => 'Api\PostDataApiController@consultantManagementLetterOfAwardAttachments' ));
    Route::put('consultant-management-letter-of-award-attachments/{Id}', array('as' => 'api.consultant-management-letter-of-award-attachments.update', 'uses' => 'Api\PutDataApiController@consultantManagementLetterOfAwardAttachments' ));
    Route::delete('consultant-management-letter-of-award-attachments/{Id}', array('as' => 'api.consultant-management-letter-of-award-attachments.delete', 'uses' => 'Api\DeleteDataApiController@consultantManagementLetterOfAwardAttachments' ));

    //consultant_management_list_of_consultants
    Route::get('consultant-management-list-of-consultants', array('as' => 'api.consultant-management-list-of-consultants', 'uses' => 'Api\GetDataApiController@consultantManagementListOfConsultants' ));
    Route::post('consultant-management-list-of-consultants', array('as' => 'api.consultant-management-list-of-consultants.create', 'uses' => 'Api\PostDataApiController@consultantManagementListOfConsultants' ));
    Route::put('consultant-management-list-of-consultants/{Id}', array('as' => 'api.consultant-management-list-of-consultants.update', 'uses' => 'Api\PutDataApiController@consultantManagementListOfConsultants' ));
    Route::delete('consultant-management-list-of-consultants/{Id}', array('as' => 'api.consultant-management-list-of-consultants.delete', 'uses' => 'Api\DeleteDataApiController@consultantManagementListOfConsultants' ));

    //consultant_management_letter_of_award_templates
    Route::get('consultant-management-letter-of-award-templates', array('as' => 'api.consultant-management-letter-of-award-templates', 'uses' => 'Api\GetDataApiController@consultantManagementLetterOfAwardTemplates' ));
    Route::post('consultant-management-letter-of-award-templates', array('as' => 'api.consultant-management-letter-of-award-templates.create', 'uses' => 'Api\PostDataApiController@consultantManagementLetterOfAwardTemplates' ));
    Route::put('consultant-management-letter-of-award-templates/{Id}', array('as' => 'api.consultant-management-letter-of-award-templates.update', 'uses' => 'Api\PutDataApiController@consultantManagementLetterOfAwardTemplates' ));
    Route::delete('consultant-management-letter-of-award-templates/{Id}', array('as' => 'api.consultant-management-letter-of-award-templates.delete', 'uses' => 'Api\DeleteDataApiController@consultantManagementLetterOfAwardTemplates' ));

    //consultant_management_list_of_consultant_verifiers
    Route::get('consultant-management-list-of-consultant-verifiers', array('as' => 'api.consultant-management-list-of-consultant-verifiers', 'uses' => 'Api\GetDataApiController@consultantManagementListOfConsultantVerifiers' ));
    Route::post('consultant-management-list-of-consultant-verifiers', array('as' => 'api.consultant-management-list-of-consultant-verifiers.create', 'uses' => 'Api\PostDataApiController@consultantManagementListOfConsultantVerifiers' ));
    Route::put('consultant-management-list-of-consultant-verifiers/{Id}', array('as' => 'api.consultant-management-list-of-consultant-verifiers.update', 'uses' => 'Api\PutDataApiController@consultantManagementListOfConsultantVerifiers' ));
    Route::delete('consultant-management-list-of-consultant-verifiers/{Id}', array('as' => 'api.consultant-management-list-of-consultant-verifiers.delete', 'uses' => 'Api\DeleteDataApiController@consultantManagementListOfConsultantVerifiers' ));

    //consultant_management_product_types
    Route::get('consultant-management-product-types', array('as' => 'api.consultant-management-product-types', 'uses' => 'Api\GetDataApiController@consultantManagementProductTypes' ));
    Route::post('consultant-management-product-types', array('as' => 'api.consultant-management-product-types.create', 'uses' => 'Api\PostDataApiController@consultantManagementProductTypes' ));
    Route::put('consultant-management-product-types/{Id}', array('as' => 'api.consultant-management-product-types.update', 'uses' => 'Api\PutDataApiController@consultantManagementProductTypes' ));
    Route::delete('consultant-management-product-types/{Id}', array('as' => 'api.consultant-management-product-types.delete', 'uses' => 'Api\DeleteDataApiController@consultantManagementProductTypes' ));

    //consultant_management_loa_subsidiary_running_numbers
    Route::get('consultant-management-loa-subsidiary-running-numbers', array('as' => 'api.consultant-management-loa-subsidiary-running-numbers', 'uses' => 'Api\GetDataApiController@consultantManagementLoaSubsidiaryRunningNumbers' ));
    Route::post('consultant-management-loa-subsidiary-running-numbers', array('as' => 'api.consultant-management-loa-subsidiary-running-numbers.create', 'uses' => 'Api\PostDataApiController@consultantManagementLoaSubsidiaryRunningNumbers' ));
    Route::put('consultant-management-loa-subsidiary-running-numbers/{Id}', array('as' => 'api.consultant-management-loa-subsidiary-running-numbers.update', 'uses' => 'Api\PutDataApiController@consultantManagementLoaSubsidiaryRunningNumbers' ));
    Route::delete('consultant-management-loa-subsidiary-running-numbers/{Id}', array('as' => 'api.consultant-management-loa-subsidiary-running-numbers.delete', 'uses' => 'Api\DeleteDataApiController@consultantManagementLoaSubsidiaryRunningNumbers' ));

    //consultant_management_questionnaires
    Route::get('consultant-management-questionnaires', array('as' => 'api.consultant-management-questionnaires', 'uses' => 'Api\GetDataApiController@consultantManagementQuestionnaires' ));
    Route::post('consultant-management-questionnaires', array('as' => 'api.consultant-management-questionnaires.create', 'uses' => 'Api\PostDataApiController@consultantManagementQuestionnaires' ));
    Route::put('consultant-management-questionnaires/{Id}', array('as' => 'api.consultant-management-questionnaires.update', 'uses' => 'Api\PutDataApiController@consultantManagementQuestionnaires' ));
    Route::delete('consultant-management-questionnaires/{Id}', array('as' => 'api.consultant-management-questionnaires.delete', 'uses' => 'Api\DeleteDataApiController@consultantManagementQuestionnaires' ));

    //consultant_management_letter_of_awards
    Route::get('consultant-management-letter-of-awards', array('as' => 'api.consultant-management-letter-of-awards', 'uses' => 'Api\GetDataApiController@consultantManagementLetterOfAwards' ));
    Route::post('consultant-management-letter-of-awards', array('as' => 'api.consultant-management-letter-of-awards.create', 'uses' => 'Api\PostDataApiController@consultantManagementLetterOfAwards' ));
    Route::put('consultant-management-letter-of-awards/{Id}', array('as' => 'api.consultant-management-letter-of-awards.update', 'uses' => 'Api\PutDataApiController@consultantManagementLetterOfAwards' ));
    Route::delete('consultant-management-letter-of-awards/{Id}', array('as' => 'api.consultant-management-letter-of-awards.delete', 'uses' => 'Api\DeleteDataApiController@consultantManagementLetterOfAwards' ));

    //consultant_management_open_rfp_verifiers
    Route::get('consultant-management-open-rfp-verifiers', array('as' => 'api.consultant-management-open-rfp-verifiers', 'uses' => 'Api\GetDataApiController@consultantManagementOpenRfpVerifiers' ));
    Route::post('consultant-management-open-rfp-verifiers', array('as' => 'api.consultant-management-open-rfp-verifiers.create', 'uses' => 'Api\PostDataApiController@consultantManagementOpenRfpVerifiers' ));
    Route::put('consultant-management-open-rfp-verifiers/{Id}', array('as' => 'api.consultant-management-open-rfp-verifiers.update', 'uses' => 'Api\PutDataApiController@consultantManagementOpenRfpVerifiers' ));
    Route::delete('consultant-management-open-rfp-verifiers/{Id}', array('as' => 'api.consultant-management-open-rfp-verifiers.delete', 'uses' => 'Api\DeleteDataApiController@consultantManagementOpenRfpVerifiers' ));

    //consultant_management_open_rfp_verifier_versions
    Route::get('consultant-management-open-rfp-verifier-versions', array('as' => 'api.consultant-management-open-rfp-verifier-versions', 'uses' => 'Api\GetDataApiController@consultantManagementOpenRfpVerifierVersions' ));
    Route::post('consultant-management-open-rfp-verifier-versions', array('as' => 'api.consultant-management-open-rfp-verifier-versions.create', 'uses' => 'Api\PostDataApiController@consultantManagementOpenRfpVerifierVersions' ));
    Route::put('consultant-management-open-rfp-verifier-versions/{Id}', array('as' => 'api.consultant-management-open-rfp-verifier-versions.update', 'uses' => 'Api\PutDataApiController@consultantManagementOpenRfpVerifierVersions' ));
    Route::delete('consultant-management-open-rfp-verifier-versions/{Id}', array('as' => 'api.consultant-management-open-rfp-verifier-versions.delete', 'uses' => 'Api\DeleteDataApiController@consultantManagementOpenRfpVerifierVersions' ));

    //consultant_management_questionnaire_options
    Route::get('consultant-management-questionnaire-options', array('as' => 'api.consultant-management-questionnaire-options', 'uses' => 'Api\GetDataApiController@consultantManagementQuestionnaireOptions' ));
    Route::post('consultant-management-questionnaire-options', array('as' => 'api.consultant-management-questionnaire-options.create', 'uses' => 'Api\PostDataApiController@consultantManagementQuestionnaireOptions' ));
    Route::put('consultant-management-questionnaire-options/{Id}', array('as' => 'api.consultant-management-questionnaire-options.update', 'uses' => 'Api\PutDataApiController@consultantManagementQuestionnaireOptions' ));
    Route::delete('consultant-management-questionnaire-options/{Id}', array('as' => 'api.consultant-management-questionnaire-options.delete', 'uses' => 'Api\DeleteDataApiController@consultantManagementQuestionnaireOptions' ));

    //consultant_management_letter_of_award_verifiers
    Route::get('consultant-management-letter-of-award-verifiers', array('as' => 'api.consultant-management-letter-of-award-verifiers', 'uses' => 'Api\GetDataApiController@consultantManagementLetterOfAwardVerifiers' ));
    Route::post('consultant-management-letter-of-award-verifiers', array('as' => 'api.consultant-management-letter-of-award-verifiers.create', 'uses' => 'Api\PostDataApiController@consultantManagementLetterOfAwardVerifiers' ));
    Route::put('consultant-management-letter-of-award-verifiers/{Id}', array('as' => 'api.consultant-management-letter-of-award-verifiers.update', 'uses' => 'Api\PutDataApiController@consultantManagementLetterOfAwardVerifiers' ));
    Route::delete('consultant-management-letter-of-award-verifiers/{Id}', array('as' => 'api.consultant-management-letter-of-award-verifiers.delete', 'uses' => 'Api\DeleteDataApiController@consultantManagementLetterOfAwardVerifiers' ));

    //consultant_management_letter_of_award_verifier_versions
    Route::get('consultant-management-letter-of-award-verifier-versions', array('as' => 'api.consultant-management-letter-of-award-verifier-versions', 'uses' => 'Api\GetDataApiController@consultantManagementLetterOfAwardVerifierVersions' ));
    Route::post('consultant-management-letter-of-award-verifier-versions', array('as' => 'api.consultant-management-letter-of-award-verifier-versions.create', 'uses' => 'Api\PostDataApiController@consultantManagementLetterOfAwardVerifierVersions' ));
    Route::put('consultant-management-letter-of-award-verifier-versions/{Id}', array('as' => 'api.consultant-management-letter-of-award-verifier-versions.update', 'uses' => 'Api\PutDataApiController@consultantManagementLetterOfAwardVerifierVersions' ));
    Route::delete('consultant-management-letter-of-award-verifier-versions/{Id}', array('as' => 'api.consultant-management-letter-of-award-verifier-versions.delete', 'uses' => 'Api\DeleteDataApiController@consultantManagementLetterOfAwardVerifierVersions' ));

    //consultant_management_list_of_consultant_companies
    Route::get('consultant-management-list-of-consultant-companies', array('as' => 'api.consultant-management-list-of-consultant-companies', 'uses' => 'Api\GetDataApiController@consultantManagementListOfConsultantCompanies' ));
    Route::post('consultant-management-list-of-consultant-companies', array('as' => 'api.consultant-management-list-of-consultant-companies.create', 'uses' => 'Api\PostDataApiController@consultantManagementListOfConsultantCompanies' ));
    Route::put('consultant-management-list-of-consultant-companies/{Id}', array('as' => 'api.consultant-management-list-of-consultant-companies.update', 'uses' => 'Api\PutDataApiController@consultantManagementListOfConsultantCompanies' ));
    Route::delete('consultant-management-list-of-consultant-companies/{Id}', array('as' => 'api.consultant-management-list-of-consultant-companies.delete', 'uses' => 'Api\DeleteDataApiController@consultantManagementListOfConsultantCompanies' ));

    //consultant_management_loc_verifier_versions
    Route::get('consultant-management-loc-verifier-versions', array('as' => 'api.consultant-management-loc-verifier-versions', 'uses' => 'Api\GetDataApiController@consultantManagementLocVerifierVersions' ));
    Route::post('consultant-management-loc-verifier-versions', array('as' => 'api.consultant-management-loc-verifier-versions.create', 'uses' => 'Api\PostDataApiController@consultantManagementLocVerifierVersions' ));
    Route::put('consultant-management-loc-verifier-versions/{Id}', array('as' => 'api.consultant-management-loc-verifier-versions.update', 'uses' => 'Api\PutDataApiController@consultantManagementLocVerifierVersions' ));
    Route::delete('consultant-management-loc-verifier-versions/{Id}', array('as' => 'api.consultant-management-loc-verifier-versions.delete', 'uses' => 'Api\DeleteDataApiController@consultantManagementLocVerifierVersions' ));

    //consultant_management_recommendation_of_consultant_companies
    Route::get('consultant-management-recommendation-of-consultant-companies', array('as' => 'api.consultant-management-recommendation-of-consultant-companies', 'uses' => 'Api\GetDataApiController@consultantManagementRecommendationOfConsultantCompanies' ));
    Route::post('consultant-management-recommendation-of-consultant-companies', array('as' => 'api.consultant-management-recommendation-of-consultant-companies.create', 'uses' => 'Api\PostDataApiController@consultantManagementRecommendationOfConsultantCompanies' ));
    Route::put('consultant-management-recommendation-of-consultant-companies/{Id}', array('as' => 'api.consultant-management-recommendation-of-consultant-companies.update', 'uses' => 'Api\PutDataApiController@consultantManagementRecommendationOfConsultantCompanies' ));
    Route::delete('consultant-management-recommendation-of-consultant-companies/{Id}', array('as' => 'api.consultant-management-recommendation-of-consultant-companies.delete', 'uses' => 'Api\DeleteDataApiController@consultantManagementRecommendationOfConsultantCompanies' ));

    //consultant_management_recommendation_of_consultant_verifiers
    Route::get('consultant-management-recommendation-of-consultant-verifiers', array('as' => 'api.consultant-management-recommendation-of-consultant-verifiers', 'uses' => 'Api\GetDataApiController@consultantManagementRecommendationOfConsultantVerifiers' ));
    Route::post('consultant-management-recommendation-of-consultant-verifiers', array('as' => 'api.consultant-management-recommendation-of-consultant-verifiers.create', 'uses' => 'Api\PostDataApiController@consultantManagementRecommendationOfConsultantVerifiers' ));
    Route::put('consultant-management-recommendation-of-consultant-verifiers/{Id}', array('as' => 'api.consultant-management-recommendation-of-consultant-verifiers.update', 'uses' => 'Api\PutDataApiController@consultantManagementRecommendationOfConsultantVerifiers' ));
    Route::delete('consultant-management-recommendation-of-consultant-verifiers/{Id}', array('as' => 'api.consultant-management-recommendation-of-consultant-verifiers.delete', 'uses' => 'Api\DeleteDataApiController@consultantManagementRecommendationOfConsultantVerifiers' ));

    //consultant_management_rfp_resubmission_verifiers
    Route::get('consultant-management-rfp-resubmission-verifiers', array('as' => 'api.consultant-management-rfp-resubmission-verifiers', 'uses' => 'Api\GetDataApiController@consultantManagementRfpResubmissionVerifiers' ));
    Route::post('consultant-management-rfp-resubmission-verifiers', array('as' => 'api.consultant-management-rfp-resubmission-verifiers.create', 'uses' => 'Api\PostDataApiController@consultantManagementRfpResubmissionVerifiers' ));
    Route::put('consultant-management-rfp-resubmission-verifiers/{Id}', array('as' => 'api.consultant-management-rfp-resubmission-verifiers.update', 'uses' => 'Api\PutDataApiController@consultantManagementRfpResubmissionVerifiers' ));
    Route::delete('consultant-management-rfp-resubmission-verifiers/{Id}', array('as' => 'api.consultant-management-rfp-resubmission-verifiers.delete', 'uses' => 'Api\DeleteDataApiController@consultantManagementRfpResubmissionVerifiers' ));

    //consultant_management_rfp_interview_tokens
    Route::get('consultant-management-rfp-interview-tokens', array('as' => 'api.consultant-management-rfp-interview-tokens', 'uses' => 'Api\GetDataApiController@consultantManagementRfpInterviewTokens' ));
    Route::post('consultant-management-rfp-interview-tokens', array('as' => 'api.consultant-management-rfp-interview-tokens.create', 'uses' => 'Api\PostDataApiController@consultantManagementRfpInterviewTokens' ));
    Route::put('consultant-management-rfp-interview-tokens/{Id}', array('as' => 'api.consultant-management-rfp-interview-tokens.update', 'uses' => 'Api\PutDataApiController@consultantManagementRfpInterviewTokens' ));
    Route::delete('consultant-management-rfp-interview-tokens/{Id}', array('as' => 'api.consultant-management-rfp-interview-tokens.delete', 'uses' => 'Api\DeleteDataApiController@consultantManagementRfpInterviewTokens' ));

    //consultant_management_rfp_interview_consultants
    Route::get('consultant-management-rfp-interview-consultants', array('as' => 'api.consultant-management-rfp-interview-consultants', 'uses' => 'Api\GetDataApiController@consultantManagementRfpInterviewConsultants' ));
    Route::post('consultant-management-rfp-interview-consultants', array('as' => 'api.consultant-management-rfp-interview-consultants.create', 'uses' => 'Api\PostDataApiController@consultantManagementRfpInterviewConsultants' ));
    Route::put('consultant-management-rfp-interview-consultants/{Id}', array('as' => 'api.consultant-management-rfp-interview-consultants.update', 'uses' => 'Api\PutDataApiController@consultantManagementRfpInterviewConsultants' ));
    Route::delete('consultant-management-rfp-interview-consultants/{Id}', array('as' => 'api.consultant-management-rfp-interview-consultants.delete', 'uses' => 'Api\DeleteDataApiController@consultantManagementRfpInterviewConsultants' ));

    //consultant_management_rfp_questionnaires
    Route::get('consultant-management-rfp-questionnaires', array('as' => 'api.consultant-management-rfp-questionnaires', 'uses' => 'Api\GetDataApiController@consultantManagementRfpQuestionnaires' ));
    Route::post('consultant-management-rfp-questionnaires', array('as' => 'api.consultant-management-rfp-questionnaires.create', 'uses' => 'Api\PostDataApiController@consultantManagementRfpQuestionnaires' ));
    Route::put('consultant-management-rfp-questionnaires/{Id}', array('as' => 'api.consultant-management-rfp-questionnaires.update', 'uses' => 'Api\PutDataApiController@consultantManagementRfpQuestionnaires' ));
    Route::delete('consultant-management-rfp-questionnaires/{Id}', array('as' => 'api.consultant-management-rfp-questionnaires.delete', 'uses' => 'Api\DeleteDataApiController@consultantManagementRfpQuestionnaires' ));

    //consultant_management_rfp_interviews
    Route::get('consultant-management-rfp-interviews', array('as' => 'api.consultant-management-rfp-interviews', 'uses' => 'Api\GetDataApiController@consultantManagementRfpInterviews' ));
    Route::post('consultant-management-rfp-interviews', array('as' => 'api.consultant-management-rfp-interviews.create', 'uses' => 'Api\PostDataApiController@consultantManagementRfpInterviews' ));
    Route::put('consultant-management-rfp-interviews/{Id}', array('as' => 'api.consultant-management-rfp-interviews.update', 'uses' => 'Api\PutDataApiController@consultantManagementRfpInterviews' ));
    Route::delete('consultant-management-rfp-interviews/{Id}', array('as' => 'api.consultant-management-rfp-interviews.delete', 'uses' => 'Api\DeleteDataApiController@consultantManagementRfpInterviews' ));

    //consultant_management_rfp_questionnaire_options
    Route::get('consultant-management-rfp-questionnaire-options', array('as' => 'api.consultant-management-rfp-questionnaire-options', 'uses' => 'Api\GetDataApiController@consultantManagementRfpQuestionnaireOptions' ));
    Route::post('consultant-management-rfp-questionnaire-options', array('as' => 'api.consultant-management-rfp-questionnaire-options.create', 'uses' => 'Api\PostDataApiController@consultantManagementRfpQuestionnaireOptions' ));
    Route::put('consultant-management-rfp-questionnaire-options/{Id}', array('as' => 'api.consultant-management-rfp-questionnaire-options.update', 'uses' => 'Api\PutDataApiController@consultantManagementRfpQuestionnaireOptions' ));
    Route::delete('consultant-management-rfp-questionnaire-options/{Id}', array('as' => 'api.consultant-management-rfp-questionnaire-options.delete', 'uses' => 'Api\DeleteDataApiController@consultantManagementRfpQuestionnaireOptions' ));

    //consultant_management_roles_contract_group_categories
    Route::get('consultant-management-roles-contract-group-categories', array('as' => 'api.consultant-management-roles-contract-group-categories', 'uses' => 'Api\GetDataApiController@consultantManagementRolesContractGroupCategories' ));
    Route::post('consultant-management-roles-contract-group-categories', array('as' => 'api.consultant-management-roles-contract-group-categories.create', 'uses' => 'Api\PostDataApiController@consultantManagementRolesContractGroupCategories' ));
    Route::put('consultant-management-roles-contract-group-categories/{Id}', array('as' => 'api.consultant-management-roles-contract-group-categories.update', 'uses' => 'Api\PutDataApiController@consultantManagementRolesContractGroupCategories' ));
    Route::delete('consultant-management-roles-contract-group-categories/{Id}', array('as' => 'api.consultant-management-roles-contract-group-categories.delete', 'uses' => 'Api\DeleteDataApiController@consultantManagementRolesContractGroupCategories' ));

    //consultant_management_rfp_revisions
    Route::get('consultant-management-rfp-revisions', array('as' => 'api.consultant-management-rfp-revisions', 'uses' => 'Api\GetDataApiController@consultantManagementRfpRevisions' ));
    Route::post('consultant-management-rfp-revisions', array('as' => 'api.consultant-management-rfp-revisions.create', 'uses' => 'Api\PostDataApiController@consultantManagementRfpRevisions' ));
    Route::put('consultant-management-rfp-revisions/{Id}', array('as' => 'api.consultant-management-rfp-revisions.update', 'uses' => 'Api\PutDataApiController@consultantManagementRfpRevisions' ));
    Route::delete('consultant-management-rfp-revisions/{Id}', array('as' => 'api.consultant-management-rfp-revisions.delete', 'uses' => 'Api\DeleteDataApiController@consultantManagementRfpRevisions' ));

    //consultant_management_rfp_attachment_settings
    Route::get('consultant-management-rfp-attachment-settings', array('as' => 'api.consultant-management-rfp-attachment-settings', 'uses' => 'Api\GetDataApiController@consultantManagementRfpAttachmentSettings' ));
    Route::post('consultant-management-rfp-attachment-settings', array('as' => 'api.consultant-management-rfp-attachment-settings.create', 'uses' => 'Api\PostDataApiController@consultantManagementRfpAttachmentSettings' ));
    Route::put('consultant-management-rfp-attachment-settings/{Id}', array('as' => 'api.consultant-management-rfp-attachment-settings.update', 'uses' => 'Api\PutDataApiController@consultantManagementRfpAttachmentSettings' ));
    Route::delete('consultant-management-rfp-attachment-settings/{Id}', array('as' => 'api.consultant-management-rfp-attachment-settings.delete', 'uses' => 'Api\DeleteDataApiController@consultantManagementRfpAttachmentSettings' ));

    //consultant_management_rfp_documents
    Route::get('consultant-management-rfp-documents', array('as' => 'api.consultant-management-rfp-documents', 'uses' => 'Api\GetDataApiController@consultantManagementRfpDocuments' ));
    Route::post('consultant-management-rfp-documents', array('as' => 'api.consultant-management-rfp-documents.create', 'uses' => 'Api\PostDataApiController@consultantManagementRfpDocuments' ));
    Route::put('consultant-management-rfp-documents/{Id}', array('as' => 'api.consultant-management-rfp-documents.update', 'uses' => 'Api\PutDataApiController@consultantManagementRfpDocuments' ));
    Route::delete('consultant-management-rfp-documents/{Id}', array('as' => 'api.consultant-management-rfp-documents.delete', 'uses' => 'Api\DeleteDataApiController@consultantManagementRfpDocuments' ));

    //consultant_management_rfp_resubmission_verifier_versions
    Route::get('consultant-management-rfp-resubmission-verifier-versions', array('as' => 'api.consultant-management-rfp-resubmission-verifier-versions', 'uses' => 'Api\GetDataApiController@consultantManagementRfpResubmissionVerifierVersions' ));
    Route::post('consultant-management-rfp-resubmission-verifier-versions', array('as' => 'api.consultant-management-rfp-resubmission-verifier-versions.create', 'uses' => 'Api\PostDataApiController@consultantManagementRfpResubmissionVerifierVersions' ));
    Route::put('consultant-management-rfp-resubmission-verifier-versions/{Id}', array('as' => 'api.consultant-management-rfp-resubmission-verifier-versions.update', 'uses' => 'Api\PutDataApiController@consultantManagementRfpResubmissionVerifierVersions' ));
    Route::delete('consultant-management-rfp-resubmission-verifier-versions/{Id}', array('as' => 'api.consultant-management-rfp-resubmission-verifier-versions.delete', 'uses' => 'Api\DeleteDataApiController@consultantManagementRfpResubmissionVerifierVersions' ));

    //consultant_management_recommendation_of_consultants
    Route::get('consultant-management-recommendation-of-consultants', array('as' => 'api.consultant-management-recommendation-of-consultants', 'uses' => 'Api\GetDataApiController@consultantManagementRecommendationOfConsultants' ));
    Route::post('consultant-management-recommendation-of-consultants', array('as' => 'api.consultant-management-recommendation-of-consultants.create', 'uses' => 'Api\PostDataApiController@consultantManagementRecommendationOfConsultants' ));
    Route::put('consultant-management-recommendation-of-consultants/{Id}', array('as' => 'api.consultant-management-recommendation-of-consultants.update', 'uses' => 'Api\PutDataApiController@consultantManagementRecommendationOfConsultants' ));
    Route::delete('consultant-management-recommendation-of-consultants/{Id}', array('as' => 'api.consultant-management-recommendation-of-consultants.delete', 'uses' => 'Api\DeleteDataApiController@consultantManagementRecommendationOfConsultants' ));

    //consultant_management_roc_verifier_versions
    Route::get('consultant-management-roc-verifier-versions', array('as' => 'api.consultant-management-roc-verifier-versions', 'uses' => 'Api\GetDataApiController@consultantManagementRocVerifierVersions' ));
    Route::post('consultant-management-roc-verifier-versions', array('as' => 'api.consultant-management-roc-verifier-versions.create', 'uses' => 'Api\PostDataApiController@consultantManagementRocVerifierVersions' ));
    Route::put('consultant-management-roc-verifier-versions/{Id}', array('as' => 'api.consultant-management-roc-verifier-versions.update', 'uses' => 'Api\PutDataApiController@consultantManagementRocVerifierVersions' ));
    Route::delete('consultant-management-roc-verifier-versions/{Id}', array('as' => 'api.consultant-management-roc-verifier-versions.delete', 'uses' => 'Api\DeleteDataApiController@consultantManagementRocVerifierVersions' ));

    //consultant_management_section_d_details
    Route::get('consultant-management-section-d-details', array('as' => 'api.consultant-management-section-d-details', 'uses' => 'Api\GetDataApiController@consultantManagementSectionDDetails' ));
    Route::post('consultant-management-section-d-details', array('as' => 'api.consultant-management-section-d-details.create', 'uses' => 'Api\PostDataApiController@consultantManagementSectionDDetails' ));
    Route::put('consultant-management-section-d-details/{Id}', array('as' => 'api.consultant-management-section-d-details.update', 'uses' => 'Api\PutDataApiController@consultantManagementSectionDDetails' ));
    Route::delete('consultant-management-section-d-details/{Id}', array('as' => 'api.consultant-management-section-d-details.delete', 'uses' => 'Api\DeleteDataApiController@consultantManagementSectionDDetails' ));

    //consultant_management_section_d_service_fees
    Route::get('consultant-management-section-d-service-fees', array('as' => 'api.consultant-management-section-d-service-fees', 'uses' => 'Api\GetDataApiController@consultantManagementSectionDServiceFees' ));
    Route::post('consultant-management-section-d-service-fees', array('as' => 'api.consultant-management-section-d-service-fees.create', 'uses' => 'Api\PostDataApiController@consultantManagementSectionDServiceFees' ));
    Route::put('consultant-management-section-d-service-fees/{Id}', array('as' => 'api.consultant-management-section-d-service-fees.update', 'uses' => 'Api\PutDataApiController@consultantManagementSectionDServiceFees' ));
    Route::delete('consultant-management-section-d-service-fees/{Id}', array('as' => 'api.consultant-management-section-d-service-fees.delete', 'uses' => 'Api\DeleteDataApiController@consultantManagementSectionDServiceFees' ));

    //contract_group_categories
    Route::get('contract-group-categories', array('as' => 'api.contract-group-categories', 'uses' => 'Api\GetDataApiController@contractGroupCategories' ));
    Route::post('contract-group-categories', array('as' => 'api.contract-group-categories.create', 'uses' => 'Api\PostDataApiController@contractGroupCategories' ));
    Route::put('contract-group-categories/{Id}', array('as' => 'api.contract-group-categories.update', 'uses' => 'Api\PutDataApiController@contractGroupCategories' ));
    Route::delete('contract-group-categories/{Id}', array('as' => 'api.contract-group-categories.delete', 'uses' => 'Api\DeleteDataApiController@contractGroupCategories' ));

    //consultant_management_vendor_categories_rfp
    Route::get('consultant-management-vendor-categories-rfp', array('as' => 'api.consultant-management-vendor-categories-rfp', 'uses' => 'Api\GetDataApiController@consultantManagementVendorCategoriesRfp' ));
    Route::post('consultant-management-vendor-categories-rfp', array('as' => 'api.consultant-management-vendor-categories-rfp.create', 'uses' => 'Api\PostDataApiController@consultantManagementVendorCategoriesRfp' ));
    Route::put('consultant-management-vendor-categories-rfp/{Id}', array('as' => 'api.consultant-management-vendor-categories-rfp.update', 'uses' => 'Api\PutDataApiController@consultantManagementVendorCategoriesRfp' ));
    Route::delete('consultant-management-vendor-categories-rfp/{Id}', array('as' => 'api.consultant-management-vendor-categories-rfp.delete', 'uses' => 'Api\DeleteDataApiController@consultantManagementVendorCategoriesRfp' ));

    //contract_group_category_privileges
    Route::get('contract-group-category-privileges', array('as' => 'api.contract-group-category-privileges', 'uses' => 'Api\GetDataApiController@contractGroupCategoryPrivileges' ));
    Route::post('contract-group-category-privileges', array('as' => 'api.contract-group-category-privileges.create', 'uses' => 'Api\PostDataApiController@contractGroupCategoryPrivileges' ));
    Route::put('contract-group-category-privileges/{Id}', array('as' => 'api.contract-group-category-privileges.update', 'uses' => 'Api\PutDataApiController@contractGroupCategoryPrivileges' ));
    Route::delete('contract-group-category-privileges/{Id}', array('as' => 'api.contract-group-category-privileges.delete', 'uses' => 'Api\DeleteDataApiController@contractGroupCategoryPrivileges' ));

    //contract_groups
    Route::get('contract-groups', array('as' => 'api.contract-groups', 'uses' => 'Api\GetDataApiController@contractGroups' ));
    Route::post('contract-groups', array('as' => 'api.contract-groups.create', 'uses' => 'Api\PostDataApiController@contractGroups' ));
    Route::put('contract-groups/{Id}', array('as' => 'api.contract-groups.update', 'uses' => 'Api\PutDataApiController@contractGroups' ));
    Route::delete('contract-groups/{Id}', array('as' => 'api.contract-groups.delete', 'uses' => 'Api\DeleteDataApiController@contractGroups' ));

    //consultant_management_subsidiaries
    Route::get('consultant-management-subsidiaries', array('as' => 'api.consultant-management-subsidiaries', 'uses' => 'Api\GetDataApiController@consultantManagementSubsidiaries' ));
    Route::post('consultant-management-subsidiaries', array('as' => 'api.consultant-management-subsidiaries.create', 'uses' => 'Api\PostDataApiController@consultantManagementSubsidiaries' ));
    Route::put('consultant-management-subsidiaries/{Id}', array('as' => 'api.consultant-management-subsidiaries.update', 'uses' => 'Api\PutDataApiController@consultantManagementSubsidiaries' ));
    Route::delete('consultant-management-subsidiaries/{Id}', array('as' => 'api.consultant-management-subsidiaries.delete', 'uses' => 'Api\DeleteDataApiController@consultantManagementSubsidiaries' ));

    //consultant_management_section_appendix_details
    Route::get('consultant-management-section-appendix-details', array('as' => 'api.consultant-management-section-appendix-details', 'uses' => 'Api\GetDataApiController@consultantManagementSectionAppendixDetails' ));
    Route::post('consultant-management-section-appendix-details', array('as' => 'api.consultant-management-section-appendix-details.create', 'uses' => 'Api\PostDataApiController@consultantManagementSectionAppendixDetails' ));
    Route::put('consultant-management-section-appendix-details/{Id}', array('as' => 'api.consultant-management-section-appendix-details.update', 'uses' => 'Api\PutDataApiController@consultantManagementSectionAppendixDetails' ));
    Route::delete('consultant-management-section-appendix-details/{Id}', array('as' => 'api.consultant-management-section-appendix-details.delete', 'uses' => 'Api\DeleteDataApiController@consultantManagementSectionAppendixDetails' ));

    //consultant_management_section_c_details
    Route::get('consultant-management-section-c-details', array('as' => 'api.consultant-management-section-c-details', 'uses' => 'Api\GetDataApiController@consultantManagementSectionCDetails' ));
    Route::post('consultant-management-section-c-details', array('as' => 'api.consultant-management-section-c-details.create', 'uses' => 'Api\PostDataApiController@consultantManagementSectionCDetails' ));
    Route::put('consultant-management-section-c-details/{Id}', array('as' => 'api.consultant-management-section-c-details.update', 'uses' => 'Api\PutDataApiController@consultantManagementSectionCDetails' ));
    Route::delete('consultant-management-section-c-details/{Id}', array('as' => 'api.consultant-management-section-c-details.delete', 'uses' => 'Api\DeleteDataApiController@consultantManagementSectionCDetails' ));

    //consultant_management_user_roles
    Route::get('consultant-management-user-roles', array('as' => 'api.consultant-management-user-roles', 'uses' => 'Api\GetDataApiController@consultantManagementUserRoles' ));
    Route::post('consultant-management-user-roles', array('as' => 'api.consultant-management-user-roles.create', 'uses' => 'Api\PostDataApiController@consultantManagementUserRoles' ));
    Route::put('consultant-management-user-roles/{Id}', array('as' => 'api.consultant-management-user-roles.update', 'uses' => 'Api\PutDataApiController@consultantManagementUserRoles' ));
    Route::delete('consultant-management-user-roles/{Id}', array('as' => 'api.consultant-management-user-roles.delete', 'uses' => 'Api\DeleteDataApiController@consultantManagementUserRoles' ));

    //consultant_management_vendor_categories_rfp_account_code
    Route::get('consultant-management-vendor-categories-rfp-account-code', array('as' => 'api.consultant-management-vendor-categories-rfp-account-code', 'uses' => 'Api\GetDataApiController@consultantManagementVendorCategoriesRfpAccountCode' ));
    Route::post('consultant-management-vendor-categories-rfp-account-code', array('as' => 'api.consultant-management-vendor-categories-rfp-account-code.create', 'uses' => 'Api\PostDataApiController@consultantManagementVendorCategoriesRfpAccountCode' ));
    Route::put('consultant-management-vendor-categories-rfp-account-code/{Id}', array('as' => 'api.consultant-management-vendor-categories-rfp-account-code.update', 'uses' => 'Api\PutDataApiController@consultantManagementVendorCategoriesRfpAccountCode' ));
    Route::delete('consultant-management-vendor-categories-rfp-account-code/{Id}', array('as' => 'api.consultant-management-vendor-categories-rfp-account-code.delete', 'uses' => 'Api\DeleteDataApiController@consultantManagementVendorCategoriesRfpAccountCode' ));

    //contract_group_contract_group_category
    Route::get('contract-group-contract-group-category', array('as' => 'api.contract-group-contract-group-category', 'uses' => 'Api\GetDataApiController@contractGroupContractGroupCategory' ));
    Route::post('contract-group-contract-group-category', array('as' => 'api.contract-group-contract-group-category.create', 'uses' => 'Api\PostDataApiController@contractGroupContractGroupCategory' ));
    Route::put('contract-group-contract-group-category/{Id}', array('as' => 'api.contract-group-contract-group-category.update', 'uses' => 'Api\PutDataApiController@contractGroupContractGroupCategory' ));
    Route::delete('contract-group-contract-group-category/{Id}', array('as' => 'api.contract-group-contract-group-category.delete', 'uses' => 'Api\DeleteDataApiController@contractGroupContractGroupCategory' ));

    //contract_group_conversation
    Route::get('contract-group-conversation', array('as' => 'api.contract-group-conversation', 'uses' => 'Api\GetDataApiController@contractGroupConversation' ));
    Route::post('contract-group-conversation', array('as' => 'api.contract-group-conversation.create', 'uses' => 'Api\PostDataApiController@contractGroupConversation' ));
    Route::put('contract-group-conversation/{Id}', array('as' => 'api.contract-group-conversation.update', 'uses' => 'Api\PutDataApiController@contractGroupConversation' ));
    Route::delete('contract-group-conversation/{Id}', array('as' => 'api.contract-group-conversation.delete', 'uses' => 'Api\DeleteDataApiController@contractGroupConversation' ));

    //contract_group_document_management_folder
    Route::get('contract-group-document-management-folder', array('as' => 'api.contract-group-document-management-folder', 'uses' => 'Api\GetDataApiController@contractGroupDocumentManagementFolder' ));
    Route::post('contract-group-document-management-folder', array('as' => 'api.contract-group-document-management-folder.create', 'uses' => 'Api\PostDataApiController@contractGroupDocumentManagementFolder' ));
    Route::put('contract-group-document-management-folder/{Id}', array('as' => 'api.contract-group-document-management-folder.update', 'uses' => 'Api\PutDataApiController@contractGroupDocumentManagementFolder' ));
    Route::delete('contract-group-document-management-folder/{Id}', array('as' => 'api.contract-group-document-management-folder.delete', 'uses' => 'Api\DeleteDataApiController@contractGroupDocumentManagementFolder' ));

    //contract_group_project_users
    Route::get('contract-group-project-users', array('as' => 'api.contract-group-project-users', 'uses' => 'Api\GetDataApiController@contractGroupProjectUsers' ));
    Route::post('contract-group-project-users', array('as' => 'api.contract-group-project-users.create', 'uses' => 'Api\PostDataApiController@contractGroupProjectUsers' ));
    Route::put('contract-group-project-users/{Id}', array('as' => 'api.contract-group-project-users.update', 'uses' => 'Api\PutDataApiController@contractGroupProjectUsers' ));
    Route::delete('contract-group-project-users/{Id}', array('as' => 'api.contract-group-project-users.delete', 'uses' => 'Api\DeleteDataApiController@contractGroupProjectUsers' ));

    //contract_group_tender_document_permission_logs
    Route::get('contract-group-tender-document-permission-logs', array('as' => 'api.contract-group-tender-document-permission-logs', 'uses' => 'Api\GetDataApiController@contractGroupTenderDocumentPermissionLogs' ));
    Route::post('contract-group-tender-document-permission-logs', array('as' => 'api.contract-group-tender-document-permission-logs.create', 'uses' => 'Api\PostDataApiController@contractGroupTenderDocumentPermissionLogs' ));
    Route::put('contract-group-tender-document-permission-logs/{Id}', array('as' => 'api.contract-group-tender-document-permission-logs.update', 'uses' => 'Api\PutDataApiController@contractGroupTenderDocumentPermissionLogs' ));
    Route::delete('contract-group-tender-document-permission-logs/{Id}', array('as' => 'api.contract-group-tender-document-permission-logs.delete', 'uses' => 'Api\DeleteDataApiController@contractGroupTenderDocumentPermissionLogs' ));

    //contract_management_user_permissions
    Route::get('contract-management-user-permissions', array('as' => 'api.contract-management-user-permissions', 'uses' => 'Api\GetDataApiController@contractManagementUserPermissions' ));
    Route::post('contract-management-user-permissions', array('as' => 'api.contract-management-user-permissions.create', 'uses' => 'Api\PostDataApiController@contractManagementUserPermissions' ));
    Route::put('contract-management-user-permissions/{Id}', array('as' => 'api.contract-management-user-permissions.update', 'uses' => 'Api\PutDataApiController@contractManagementUserPermissions' ));
    Route::delete('contract-management-user-permissions/{Id}', array('as' => 'api.contract-management-user-permissions.delete', 'uses' => 'Api\DeleteDataApiController@contractManagementUserPermissions' ));

    //contract_limits
    Route::get('contract-limits', array('as' => 'api.contract-limits', 'uses' => 'Api\GetDataApiController@contractLimits' ));
    Route::post('contract-limits', array('as' => 'api.contract-limits.create', 'uses' => 'Api\PostDataApiController@contractLimits' ));
    Route::put('contract-limits/{Id}', array('as' => 'api.contract-limits.update', 'uses' => 'Api\PutDataApiController@contractLimits' ));
    Route::delete('contract-limits/{Id}', array('as' => 'api.contract-limits.delete', 'uses' => 'Api\DeleteDataApiController@contractLimits' ));

    //contractor_questionnaire_replies
    Route::get('contractor-questionnaire-replies', array('as' => 'api.contractor-questionnaire-replies', 'uses' => 'Api\GetDataApiController@contractorQuestionnaireReplies' ));
    Route::post('contractor-questionnaire-replies', array('as' => 'api.contractor-questionnaire-replies.create', 'uses' => 'Api\PostDataApiController@contractorQuestionnaireReplies' ));
    Route::put('contractor-questionnaire-replies/{Id}', array('as' => 'api.contractor-questionnaire-replies.update', 'uses' => 'Api\PutDataApiController@contractorQuestionnaireReplies' ));
    Route::delete('contractor-questionnaire-replies/{Id}', array('as' => 'api.contractor-questionnaire-replies.delete', 'uses' => 'Api\DeleteDataApiController@contractorQuestionnaireReplies' ));

    //contractor_questionnaire_reply_attachments
    Route::get('contractor-questionnaire-reply-attachments', array('as' => 'api.contractor-questionnaire-reply-attachments', 'uses' => 'Api\GetDataApiController@contractorQuestionnaireReplyAttachments' ));
    Route::post('contractor-questionnaire-reply-attachments', array('as' => 'api.contractor-questionnaire-reply-attachments.create', 'uses' => 'Api\PostDataApiController@contractorQuestionnaireReplyAttachments' ));
    Route::put('contractor-questionnaire-reply-attachments/{Id}', array('as' => 'api.contractor-questionnaire-reply-attachments.update', 'uses' => 'Api\PutDataApiController@contractorQuestionnaireReplyAttachments' ));
    Route::delete('contractor-questionnaire-reply-attachments/{Id}', array('as' => 'api.contractor-questionnaire-reply-attachments.delete', 'uses' => 'Api\DeleteDataApiController@contractorQuestionnaireReplyAttachments' ));

    //contractor_work_subcategory
    Route::get('contractor-work-subcategory', array('as' => 'api.contractor-work-subcategory', 'uses' => 'Api\GetDataApiController@contractorWorkSubcategory' ));
    Route::post('contractor-work-subcategory', array('as' => 'api.contractor-work-subcategory.create', 'uses' => 'Api\PostDataApiController@contractorWorkSubcategory' ));
    Route::put('contractor-work-subcategory/{Id}', array('as' => 'api.contractor-work-subcategory.update', 'uses' => 'Api\PutDataApiController@contractorWorkSubcategory' ));
    Route::delete('contractor-work-subcategory/{Id}', array('as' => 'api.contractor-work-subcategory.delete', 'uses' => 'Api\DeleteDataApiController@contractorWorkSubcategory' ));

    //contractor_registration_statuses
    Route::get('contractor-registration-statuses', array('as' => 'api.contractor-registration-statuses', 'uses' => 'Api\GetDataApiController@contractorRegistrationStatuses' ));
    Route::post('contractor-registration-statuses', array('as' => 'api.contractor-registration-statuses.create', 'uses' => 'Api\PostDataApiController@contractorRegistrationStatuses' ));
    Route::put('contractor-registration-statuses/{Id}', array('as' => 'api.contractor-registration-statuses.update', 'uses' => 'Api\PutDataApiController@contractorRegistrationStatuses' ));
    Route::delete('contractor-registration-statuses/{Id}', array('as' => 'api.contractor-registration-statuses.delete', 'uses' => 'Api\DeleteDataApiController@contractorRegistrationStatuses' ));

    //contractor_questionnaires
    Route::get('contractor-questionnaires', array('as' => 'api.contractor-questionnaires', 'uses' => 'Api\GetDataApiController@contractorQuestionnaires' ));
    Route::post('contractor-questionnaires', array('as' => 'api.contractor-questionnaires.create', 'uses' => 'Api\PostDataApiController@contractorQuestionnaires' ));
    Route::put('contractor-questionnaires/{Id}', array('as' => 'api.contractor-questionnaires.update', 'uses' => 'Api\PutDataApiController@contractorQuestionnaires' ));
    Route::delete('contractor-questionnaires/{Id}', array('as' => 'api.contractor-questionnaires.delete', 'uses' => 'Api\DeleteDataApiController@contractorQuestionnaires' ));

    //contractors
    Route::get('contractors', array('as' => 'api.contractors', 'uses' => 'Api\GetDataApiController@contractors' ));
    Route::post('contractors', array('as' => 'api.contractors.create', 'uses' => 'Api\PostDataApiController@contractors' ));
    Route::put('contractors/{Id}', array('as' => 'api.contractors.update', 'uses' => 'Api\PutDataApiController@contractors' ));
    Route::delete('contractors/{Id}', array('as' => 'api.contractors.delete', 'uses' => 'Api\DeleteDataApiController@contractors' ));

    //cost_data
    Route::get('cost-data', array('as' => 'api.cost-data', 'uses' => 'Api\GetDataApiController@costData' ));
    Route::post('cost-data', array('as' => 'api.cost-data.create', 'uses' => 'Api\PostDataApiController@costData' ));
    Route::put('cost-data/{Id}', array('as' => 'api.cost-data.update', 'uses' => 'Api\PutDataApiController@costData' ));
    Route::delete('cost-data/{Id}', array('as' => 'api.cost-data.delete', 'uses' => 'Api\DeleteDataApiController@costData' ));

    //project_statuses
    Route::get('project-statuses', array('as' => 'api.project-statuses', 'uses' => 'Api\GetDataApiController@projectStatuses' ));
    Route::post('project-statuses', array('as' => 'api.project-statuses.create', 'uses' => 'Api\PostDataApiController@projectStatuses' ));
    Route::put('project-statuses/{Id}', array('as' => 'api.project-statuses.update', 'uses' => 'Api\PutDataApiController@projectStatuses' ));
    Route::delete('project-statuses/{Id}', array('as' => 'api.project-statuses.delete', 'uses' => 'Api\DeleteDataApiController@projectStatuses' ));

    //contracts
    Route::get('contracts', array('as' => 'api.contracts', 'uses' => 'Api\GetDataApiController@contracts' ));
    Route::post('contracts', array('as' => 'api.contracts.create', 'uses' => 'Api\PostDataApiController@contracts' ));
    Route::put('contracts/{Id}', array('as' => 'api.contracts.update', 'uses' => 'Api\PutDataApiController@contracts' ));
    Route::delete('contracts/{Id}', array('as' => 'api.contracts.delete', 'uses' => 'Api\DeleteDataApiController@contracts' ));

    //conversations
    Route::get('conversations', array('as' => 'api.conversations', 'uses' => 'Api\GetDataApiController@conversations' ));
    Route::post('conversations', array('as' => 'api.conversations.create', 'uses' => 'Api\PostDataApiController@conversations' ));
    Route::put('conversations/{Id}', array('as' => 'api.conversations.update', 'uses' => 'Api\PutDataApiController@conversations' ));
    Route::delete('conversations/{Id}', array('as' => 'api.conversations.delete', 'uses' => 'Api\DeleteDataApiController@conversations' ));

    //contractors_commitment_status_logs
    Route::get('contractors-commitment-status-logs', array('as' => 'api.contractors-commitment-status-logs', 'uses' => 'Api\GetDataApiController@contractorsCommitmentStatusLogs' ));
    Route::post('contractors-commitment-status-logs', array('as' => 'api.contractors-commitment-status-logs.create', 'uses' => 'Api\PostDataApiController@contractorsCommitmentStatusLogs' ));
    Route::put('contractors-commitment-status-logs/{Id}', array('as' => 'api.contractors-commitment-status-logs.update', 'uses' => 'Api\PutDataApiController@contractorsCommitmentStatusLogs' ));
    Route::delete('contractors-commitment-status-logs/{Id}', array('as' => 'api.contractors-commitment-status-logs.delete', 'uses' => 'Api\DeleteDataApiController@contractorsCommitmentStatusLogs' ));

    //conversation_reply_messages
    Route::get('conversation-reply-messages', array('as' => 'api.conversation-reply-messages', 'uses' => 'Api\GetDataApiController@conversationReplyMessages' ));
    Route::post('conversation-reply-messages', array('as' => 'api.conversation-reply-messages.create', 'uses' => 'Api\PostDataApiController@conversationReplyMessages' ));
    Route::put('conversation-reply-messages/{Id}', array('as' => 'api.conversation-reply-messages.update', 'uses' => 'Api\PutDataApiController@conversationReplyMessages' ));
    Route::delete('conversation-reply-messages/{Id}', array('as' => 'api.conversation-reply-messages.delete', 'uses' => 'Api\DeleteDataApiController@conversationReplyMessages' ));

    //contractor_questionnaire_questions
    Route::get('contractor-questionnaire-questions', array('as' => 'api.contractor-questionnaire-questions', 'uses' => 'Api\GetDataApiController@contractorQuestionnaireQuestions' ));
    Route::post('contractor-questionnaire-questions', array('as' => 'api.contractor-questionnaire-questions.create', 'uses' => 'Api\PostDataApiController@contractorQuestionnaireQuestions' ));
    Route::put('contractor-questionnaire-questions/{Id}', array('as' => 'api.contractor-questionnaire-questions.update', 'uses' => 'Api\PutDataApiController@contractorQuestionnaireQuestions' ));
    Route::delete('contractor-questionnaire-questions/{Id}', array('as' => 'api.contractor-questionnaire-questions.delete', 'uses' => 'Api\DeleteDataApiController@contractorQuestionnaireQuestions' ));

    //contractor_questionnaire_options
    Route::get('contractor-questionnaire-options', array('as' => 'api.contractor-questionnaire-options', 'uses' => 'Api\GetDataApiController@contractorQuestionnaireOptions' ));
    Route::post('contractor-questionnaire-options', array('as' => 'api.contractor-questionnaire-options.create', 'uses' => 'Api\PostDataApiController@contractorQuestionnaireOptions' ));
    Route::put('contractor-questionnaire-options/{Id}', array('as' => 'api.contractor-questionnaire-options.update', 'uses' => 'Api\PutDataApiController@contractorQuestionnaireOptions' ));
    Route::delete('contractor-questionnaire-options/{Id}', array('as' => 'api.contractor-questionnaire-options.delete', 'uses' => 'Api\DeleteDataApiController@contractorQuestionnaireOptions' ));

    //daily_report
    Route::get('daily-report', array('as' => 'api.daily-report', 'uses' => 'Api\GetDataApiController@dailyReport' ));
    Route::post('daily-report', array('as' => 'api.daily-report.create', 'uses' => 'Api\PostDataApiController@dailyReport' ));
    Route::put('daily-report/{Id}', array('as' => 'api.daily-report.update', 'uses' => 'Api\PutDataApiController@dailyReport' ));
    Route::delete('daily-report/{Id}', array('as' => 'api.daily-report.delete', 'uses' => 'Api\DeleteDataApiController@dailyReport' ));

    //dashboard_groups
    Route::get('dashboard-groups', array('as' => 'api.dashboard-groups', 'uses' => 'Api\GetDataApiController@dashboardGroups' ));
    Route::post('dashboard-groups', array('as' => 'api.dashboard-groups.create', 'uses' => 'Api\PostDataApiController@dashboardGroups' ));
    Route::put('dashboard-groups/{Id}', array('as' => 'api.dashboard-groups.update', 'uses' => 'Api\PutDataApiController@dashboardGroups' ));
    Route::delete('dashboard-groups/{Id}', array('as' => 'api.dashboard-groups.delete', 'uses' => 'Api\DeleteDataApiController@dashboardGroups' ));

    //directed_to
    Route::get('directed-to', array('as' => 'api.directed-to', 'uses' => 'Api\GetDataApiController@directedTo' ));
    Route::post('directed-to', array('as' => 'api.directed-to.create', 'uses' => 'Api\PostDataApiController@directedTo' ));
    Route::put('directed-to/{Id}', array('as' => 'api.directed-to.update', 'uses' => 'Api\PutDataApiController@directedTo' ));
    Route::delete('directed-to/{Id}', array('as' => 'api.directed-to.delete', 'uses' => 'Api\DeleteDataApiController@directedTo' ));

    //dynamic_forms
    Route::get('dynamic-forms', array('as' => 'api.dynamic-forms', 'uses' => 'Api\GetDataApiController@dynamicForms' ));
    Route::post('dynamic-forms', array('as' => 'api.dynamic-forms.create', 'uses' => 'Api\PostDataApiController@dynamicForms' ));
    Route::put('dynamic-forms/{Id}', array('as' => 'api.dynamic-forms.update', 'uses' => 'Api\PutDataApiController@dynamicForms' ));
    Route::delete('dynamic-forms/{Id}', array('as' => 'api.dynamic-forms.delete', 'uses' => 'Api\DeleteDataApiController@dynamicForms' ));

    //e_bidding_email_reminders
    Route::get('e-bidding-email-reminders', array('as' => 'api.e-bidding-email-reminders', 'uses' => 'Api\GetDataApiController@eBiddingEmailReminders' ));
    Route::post('e-bidding-email-reminders', array('as' => 'api.e-bidding-email-reminders.create', 'uses' => 'Api\PostDataApiController@eBiddingEmailReminders' ));
    Route::put('e-bidding-email-reminders/{Id}', array('as' => 'api.e-bidding-email-reminders.update', 'uses' => 'Api\PutDataApiController@eBiddingEmailReminders' ));
    Route::delete('e-bidding-email-reminders/{Id}', array('as' => 'api.e-bidding-email-reminders.delete', 'uses' => 'Api\DeleteDataApiController@eBiddingEmailReminders' ));

    //document_management_folders
    Route::get('document-management-folders', array('as' => 'api.document-management-folders', 'uses' => 'Api\GetDataApiController@documentManagementFolders' ));
    Route::post('document-management-folders', array('as' => 'api.document-management-folders.create', 'uses' => 'Api\PostDataApiController@documentManagementFolders' ));
    Route::put('document-management-folders/{Id}', array('as' => 'api.document-management-folders.update', 'uses' => 'Api\PutDataApiController@documentManagementFolders' ));
    Route::delete('document-management-folders/{Id}', array('as' => 'api.document-management-folders.delete', 'uses' => 'Api\DeleteDataApiController@documentManagementFolders' ));

    //current_cpe_grades
    Route::get('current-cpe-grades', array('as' => 'api.current-cpe-grades', 'uses' => 'Api\GetDataApiController@currentCpeGrades' ));
    Route::post('current-cpe-grades', array('as' => 'api.current-cpe-grades.create', 'uses' => 'Api\PostDataApiController@currentCpeGrades' ));
    Route::put('current-cpe-grades/{Id}', array('as' => 'api.current-cpe-grades.update', 'uses' => 'Api\PutDataApiController@currentCpeGrades' ));
    Route::delete('current-cpe-grades/{Id}', array('as' => 'api.current-cpe-grades.delete', 'uses' => 'Api\DeleteDataApiController@currentCpeGrades' ));

    //currency_settings
    Route::get('currency-settings', array('as' => 'api.currency-settings', 'uses' => 'Api\GetDataApiController@currencySettings' ));
    Route::post('currency-settings', array('as' => 'api.currency-settings.create', 'uses' => 'Api\PostDataApiController@currencySettings' ));
    Route::put('currency-settings/{Id}', array('as' => 'api.currency-settings.update', 'uses' => 'Api\PutDataApiController@currencySettings' ));
    Route::delete('currency-settings/{Id}', array('as' => 'api.currency-settings.delete', 'uses' => 'Api\DeleteDataApiController@currencySettings' ));

    //daily_labour_reports
    Route::get('daily-labour-reports', array('as' => 'api.daily-labour-reports', 'uses' => 'Api\GetDataApiController@dailyLabourReports' ));
    Route::post('daily-labour-reports', array('as' => 'api.daily-labour-reports.create', 'uses' => 'Api\PostDataApiController@dailyLabourReports' ));
    Route::put('daily-labour-reports/{Id}', array('as' => 'api.daily-labour-reports.update', 'uses' => 'Api\PutDataApiController@dailyLabourReports' ));
    Route::delete('daily-labour-reports/{Id}', array('as' => 'api.daily-labour-reports.delete', 'uses' => 'Api\DeleteDataApiController@dailyLabourReports' ));

    //dashboard_groups_excluded_projects
    Route::get('dashboard-groups-excluded-projects', array('as' => 'api.dashboard-groups-excluded-projects', 'uses' => 'Api\GetDataApiController@dashboardGroupsExcludedProjects' ));
    Route::post('dashboard-groups-excluded-projects', array('as' => 'api.dashboard-groups-excluded-projects.create', 'uses' => 'Api\PostDataApiController@dashboardGroupsExcludedProjects' ));
    Route::put('dashboard-groups-excluded-projects/{Id}', array('as' => 'api.dashboard-groups-excluded-projects.update', 'uses' => 'Api\PutDataApiController@dashboardGroupsExcludedProjects' ));
    Route::delete('dashboard-groups-excluded-projects/{Id}', array('as' => 'api.dashboard-groups-excluded-projects.delete', 'uses' => 'Api\DeleteDataApiController@dashboardGroupsExcludedProjects' ));

    //dashboard_groups_users
    Route::get('dashboard-groups-users', array('as' => 'api.dashboard-groups-users', 'uses' => 'Api\GetDataApiController@dashboardGroupsUsers' ));
    Route::post('dashboard-groups-users', array('as' => 'api.dashboard-groups-users.create', 'uses' => 'Api\PostDataApiController@dashboardGroupsUsers' ));
    Route::put('dashboard-groups-users/{Id}', array('as' => 'api.dashboard-groups-users.update', 'uses' => 'Api\PutDataApiController@dashboardGroupsUsers' ));
    Route::delete('dashboard-groups-users/{Id}', array('as' => 'api.dashboard-groups-users.delete', 'uses' => 'Api\DeleteDataApiController@dashboardGroupsUsers' ));

    //defect_categories
    Route::get('defect-categories', array('as' => 'api.defect-categories', 'uses' => 'Api\GetDataApiController@defectCategories' ));
    Route::post('defect-categories', array('as' => 'api.defect-categories.create', 'uses' => 'Api\PostDataApiController@defectCategories' ));
    Route::put('defect-categories/{Id}', array('as' => 'api.defect-categories.update', 'uses' => 'Api\PutDataApiController@defectCategories' ));
    Route::delete('defect-categories/{Id}', array('as' => 'api.defect-categories.delete', 'uses' => 'Api\DeleteDataApiController@defectCategories' ));

    //defect_category_pre_defined_location_code
    Route::get('defect-category-pre-defined-location-code', array('as' => 'api.defect-category-pre-defined-location-code', 'uses' => 'Api\GetDataApiController@defectCategoryPreDefinedLocationCode' ));
    Route::post('defect-category-pre-defined-location-code', array('as' => 'api.defect-category-pre-defined-location-code.create', 'uses' => 'Api\PostDataApiController@defectCategoryPreDefinedLocationCode' ));
    Route::put('defect-category-pre-defined-location-code/{Id}', array('as' => 'api.defect-category-pre-defined-location-code.update', 'uses' => 'Api\PutDataApiController@defectCategoryPreDefinedLocationCode' ));
    Route::delete('defect-category-pre-defined-location-code/{Id}', array('as' => 'api.defect-category-pre-defined-location-code.delete', 'uses' => 'Api\DeleteDataApiController@defectCategoryPreDefinedLocationCode' ));

    //defects
    Route::get('defects', array('as' => 'api.defects', 'uses' => 'Api\GetDataApiController@defects' ));
    Route::post('defects', array('as' => 'api.defects.create', 'uses' => 'Api\PostDataApiController@defects' ));
    Route::put('defects/{Id}', array('as' => 'api.defects.update', 'uses' => 'Api\PutDataApiController@defects' ));
    Route::delete('defects/{Id}', array('as' => 'api.defects.delete', 'uses' => 'Api\DeleteDataApiController@defects' ));

    //development_types_product_types
    Route::get('development-types-product-types', array('as' => 'api.development-types-product-types', 'uses' => 'Api\GetDataApiController@developmentTypesProductTypes' ));
    Route::post('development-types-product-types', array('as' => 'api.development-types-product-types.create', 'uses' => 'Api\PostDataApiController@developmentTypesProductTypes' ));
    Route::put('development-types-product-types/{Id}', array('as' => 'api.development-types-product-types.update', 'uses' => 'Api\PutDataApiController@developmentTypesProductTypes' ));
    Route::delete('development-types-product-types/{Id}', array('as' => 'api.development-types-product-types.delete', 'uses' => 'Api\DeleteDataApiController@developmentTypesProductTypes' ));

    //document_control_objects
    Route::get('document-control-objects', array('as' => 'api.document-control-objects', 'uses' => 'Api\GetDataApiController@documentControlObjects' ));
    Route::post('document-control-objects', array('as' => 'api.document-control-objects.create', 'uses' => 'Api\PostDataApiController@documentControlObjects' ));
    Route::put('document-control-objects/{Id}', array('as' => 'api.document-control-objects.update', 'uses' => 'Api\PutDataApiController@documentControlObjects' ));
    Route::delete('document-control-objects/{Id}', array('as' => 'api.document-control-objects.delete', 'uses' => 'Api\DeleteDataApiController@documentControlObjects' ));

    //e_bidding_committees
    Route::get('e-bidding-committees', array('as' => 'api.e-bidding-committees', 'uses' => 'Api\GetDataApiController@eBiddingCommittees' ));
    Route::post('e-bidding-committees', array('as' => 'api.e-bidding-committees.create', 'uses' => 'Api\PostDataApiController@eBiddingCommittees' ));
    Route::put('e-bidding-committees/{Id}', array('as' => 'api.e-bidding-committees.update', 'uses' => 'Api\PutDataApiController@eBiddingCommittees' ));
    Route::delete('e-bidding-committees/{Id}', array('as' => 'api.e-bidding-committees.delete', 'uses' => 'Api\DeleteDataApiController@eBiddingCommittees' ));

    //element_attributes
    Route::get('element-attributes', array('as' => 'api.element-attributes', 'uses' => 'Api\GetDataApiController@elementAttributes' ));
    Route::post('element-attributes', array('as' => 'api.element-attributes.create', 'uses' => 'Api\PostDataApiController@elementAttributes' ));
    Route::put('element-attributes/{Id}', array('as' => 'api.element-attributes.update', 'uses' => 'Api\PutDataApiController@elementAttributes' ));
    Route::delete('element-attributes/{Id}', array('as' => 'api.element-attributes.delete', 'uses' => 'Api\DeleteDataApiController@elementAttributes' ));

    //email_announcement_recipients
    Route::get('email-announcement-recipients', array('as' => 'api.email-announcement-recipients', 'uses' => 'Api\GetDataApiController@emailAnnouncementRecipients' ));
    Route::post('email-announcement-recipients', array('as' => 'api.email-announcement-recipients.create', 'uses' => 'Api\PostDataApiController@emailAnnouncementRecipients' ));
    Route::put('email-announcement-recipients/{Id}', array('as' => 'api.email-announcement-recipients.update', 'uses' => 'Api\PutDataApiController@emailAnnouncementRecipients' ));
    Route::delete('email-announcement-recipients/{Id}', array('as' => 'api.email-announcement-recipients.delete', 'uses' => 'Api\DeleteDataApiController@emailAnnouncementRecipients' ));

    //engineer_instructions
    Route::get('engineer-instructions', array('as' => 'api.engineer-instructions', 'uses' => 'Api\GetDataApiController@engineerInstructions' ));
    Route::post('engineer-instructions', array('as' => 'api.engineer-instructions.create', 'uses' => 'Api\PostDataApiController@engineerInstructions' ));
    Route::put('engineer-instructions/{Id}', array('as' => 'api.engineer-instructions.update', 'uses' => 'Api\PutDataApiController@engineerInstructions' ));
    Route::delete('engineer-instructions/{Id}', array('as' => 'api.engineer-instructions.delete', 'uses' => 'Api\DeleteDataApiController@engineerInstructions' ));

    //elements
    Route::get('elements', array('as' => 'api.elements', 'uses' => 'Api\GetDataApiController@elements' ));
    Route::post('elements', array('as' => 'api.elements.create', 'uses' => 'Api\PostDataApiController@elements' ));
    Route::put('elements/{Id}', array('as' => 'api.elements.update', 'uses' => 'Api\PutDataApiController@elements' ));
    Route::delete('elements/{Id}', array('as' => 'api.elements.delete', 'uses' => 'Api\DeleteDataApiController@elements' ));

    //email_notifications
    Route::get('email-notifications', array('as' => 'api.email-notifications', 'uses' => 'Api\GetDataApiController@emailNotifications' ));
    Route::post('email-notifications', array('as' => 'api.email-notifications.create', 'uses' => 'Api\PostDataApiController@emailNotifications' ));
    Route::put('email-notifications/{Id}', array('as' => 'api.email-notifications.update', 'uses' => 'Api\PutDataApiController@emailNotifications' ));
    Route::delete('email-notifications/{Id}', array('as' => 'api.email-notifications.delete', 'uses' => 'Api\DeleteDataApiController@emailNotifications' ));

    //email_notification_settings
    Route::get('email-notification-settings', array('as' => 'api.email-notification-settings', 'uses' => 'Api\GetDataApiController@emailNotificationSettings' ));
    Route::post('email-notification-settings', array('as' => 'api.email-notification-settings.create', 'uses' => 'Api\PostDataApiController@emailNotificationSettings' ));
    Route::put('email-notification-settings/{Id}', array('as' => 'api.email-notification-settings.update', 'uses' => 'Api\PutDataApiController@emailNotificationSettings' ));
    Route::delete('email-notification-settings/{Id}', array('as' => 'api.email-notification-settings.delete', 'uses' => 'Api\DeleteDataApiController@emailNotificationSettings' ));

    //element_definitions
    Route::get('element-definitions', array('as' => 'api.element-definitions', 'uses' => 'Api\GetDataApiController@elementDefinitions' ));
    Route::post('element-definitions', array('as' => 'api.element-definitions.create', 'uses' => 'Api\PostDataApiController@elementDefinitions' ));
    Route::put('element-definitions/{Id}', array('as' => 'api.element-definitions.update', 'uses' => 'Api\PutDataApiController@elementDefinitions' ));
    Route::delete('element-definitions/{Id}', array('as' => 'api.element-definitions.delete', 'uses' => 'Api\DeleteDataApiController@elementDefinitions' ));

    //email_reminder_settings
    Route::get('email-reminder-settings', array('as' => 'api.email-reminder-settings', 'uses' => 'Api\GetDataApiController@emailReminderSettings' ));
    Route::post('email-reminder-settings', array('as' => 'api.email-reminder-settings.create', 'uses' => 'Api\PostDataApiController@emailReminderSettings' ));
    Route::put('email-reminder-settings/{Id}', array('as' => 'api.email-reminder-settings.update', 'uses' => 'Api\PutDataApiController@emailReminderSettings' ));
    Route::delete('email-reminder-settings/{Id}', array('as' => 'api.email-reminder-settings.delete', 'uses' => 'Api\DeleteDataApiController@emailReminderSettings' ));

    //email_settings
    Route::get('email-settings', array('as' => 'api.email-settings', 'uses' => 'Api\GetDataApiController@emailSettings' ));
    Route::post('email-settings', array('as' => 'api.email-settings.create', 'uses' => 'Api\PostDataApiController@emailSettings' ));
    Route::put('email-settings/{Id}', array('as' => 'api.email-settings.update', 'uses' => 'Api\PutDataApiController@emailSettings' ));
    Route::delete('email-settings/{Id}', array('as' => 'api.email-settings.delete', 'uses' => 'Api\DeleteDataApiController@emailSettings' ));

    //element_values
    Route::get('element-values', array('as' => 'api.element-values', 'uses' => 'Api\GetDataApiController@elementValues' ));
    Route::post('element-values', array('as' => 'api.element-values.create', 'uses' => 'Api\PostDataApiController@elementValues' ));
    Route::put('element-values/{Id}', array('as' => 'api.element-values.update', 'uses' => 'Api\PutDataApiController@elementValues' ));
    Route::delete('element-values/{Id}', array('as' => 'api.element-values.delete', 'uses' => 'Api\DeleteDataApiController@elementValues' ));

    //element_rejections
    Route::get('element-rejections', array('as' => 'api.element-rejections', 'uses' => 'Api\GetDataApiController@elementRejections' ));
    Route::post('element-rejections', array('as' => 'api.element-rejections.create', 'uses' => 'Api\PostDataApiController@elementRejections' ));
    Route::put('element-rejections/{Id}', array('as' => 'api.element-rejections.update', 'uses' => 'Api\PutDataApiController@elementRejections' ));
    Route::delete('element-rejections/{Id}', array('as' => 'api.element-rejections.delete', 'uses' => 'Api\DeleteDataApiController@elementRejections' ));

    //email_announcements
    Route::get('email-announcements', array('as' => 'api.email-announcements', 'uses' => 'Api\GetDataApiController@emailAnnouncements' ));
    Route::post('email-announcements', array('as' => 'api.email-announcements.create', 'uses' => 'Api\PostDataApiController@emailAnnouncements' ));
    Route::put('email-announcements/{Id}', array('as' => 'api.email-announcements.update', 'uses' => 'Api\PutDataApiController@emailAnnouncements' ));
    Route::delete('email-announcements/{Id}', array('as' => 'api.email-announcements.delete', 'uses' => 'Api\DeleteDataApiController@emailAnnouncements' ));

    //e_biddings
    Route::get('e-biddings', array('as' => 'api.e-biddings', 'uses' => 'Api\GetDataApiController@eBiddings' ));
    Route::post('e-biddings', array('as' => 'api.e-biddings.create', 'uses' => 'Api\PostDataApiController@eBiddings' ));
    Route::put('e-biddings/{Id}', array('as' => 'api.e-biddings.update', 'uses' => 'Api\PutDataApiController@eBiddings' ));
    Route::delete('e-biddings/{Id}', array('as' => 'api.e-biddings.delete', 'uses' => 'Api\DeleteDataApiController@eBiddings' ));

    //extension_of_times
    Route::get('extension-of-times', array('as' => 'api.extension-of-times', 'uses' => 'Api\GetDataApiController@extensionOfTimes' ));
    Route::post('extension-of-times', array('as' => 'api.extension-of-times.create', 'uses' => 'Api\PostDataApiController@extensionOfTimes' ));
    Route::put('extension-of-times/{Id}', array('as' => 'api.extension-of-times.update', 'uses' => 'Api\PutDataApiController@extensionOfTimes' ));
    Route::delete('extension-of-times/{Id}', array('as' => 'api.extension-of-times.delete', 'uses' => 'Api\DeleteDataApiController@extensionOfTimes' ));

    //eot_fourth_level_messages
    Route::get('eot-fourth-level-messages', array('as' => 'api.eot-fourth-level-messages', 'uses' => 'Api\GetDataApiController@eotFourthLevelMessages' ));
    Route::post('eot-fourth-level-messages', array('as' => 'api.eot-fourth-level-messages.create', 'uses' => 'Api\PostDataApiController@eotFourthLevelMessages' ));
    Route::put('eot-fourth-level-messages/{Id}', array('as' => 'api.eot-fourth-level-messages.update', 'uses' => 'Api\PutDataApiController@eotFourthLevelMessages' ));
    Route::delete('eot-fourth-level-messages/{Id}', array('as' => 'api.eot-fourth-level-messages.delete', 'uses' => 'Api\DeleteDataApiController@eotFourthLevelMessages' ));

    //eot_second_level_messages
    Route::get('eot-second-level-messages', array('as' => 'api.eot-second-level-messages', 'uses' => 'Api\GetDataApiController@eotSecondLevelMessages' ));
    Route::post('eot-second-level-messages', array('as' => 'api.eot-second-level-messages.create', 'uses' => 'Api\PostDataApiController@eotSecondLevelMessages' ));
    Route::put('eot-second-level-messages/{Id}', array('as' => 'api.eot-second-level-messages.update', 'uses' => 'Api\PutDataApiController@eotSecondLevelMessages' ));
    Route::delete('eot-second-level-messages/{Id}', array('as' => 'api.eot-second-level-messages.delete', 'uses' => 'Api\DeleteDataApiController@eotSecondLevelMessages' ));

    //eot_third_level_messages
    Route::get('eot-third-level-messages', array('as' => 'api.eot-third-level-messages', 'uses' => 'Api\GetDataApiController@eotThirdLevelMessages' ));
    Route::post('eot-third-level-messages', array('as' => 'api.eot-third-level-messages.create', 'uses' => 'Api\PostDataApiController@eotThirdLevelMessages' ));
    Route::put('eot-third-level-messages/{Id}', array('as' => 'api.eot-third-level-messages.update', 'uses' => 'Api\PutDataApiController@eotThirdLevelMessages' ));
    Route::delete('eot-third-level-messages/{Id}', array('as' => 'api.eot-third-level-messages.delete', 'uses' => 'Api\DeleteDataApiController@eotThirdLevelMessages' ));

    //external_application_attributes
    Route::get('external-application-attributes', array('as' => 'api.external-application-attributes', 'uses' => 'Api\GetDataApiController@externalApplicationAttributes' ));
    Route::post('external-application-attributes', array('as' => 'api.external-application-attributes.create', 'uses' => 'Api\PostDataApiController@externalApplicationAttributes' ));
    Route::put('external-application-attributes/{Id}', array('as' => 'api.external-application-attributes.update', 'uses' => 'Api\PutDataApiController@externalApplicationAttributes' ));
    Route::delete('external-application-attributes/{Id}', array('as' => 'api.external-application-attributes.delete', 'uses' => 'Api\DeleteDataApiController@externalApplicationAttributes' ));

    //expression_of_interest_tokens
    Route::get('expression-of-interest-tokens', array('as' => 'api.expression-of-interest-tokens', 'uses' => 'Api\GetDataApiController@expressionOfInterestTokens' ));
    Route::post('expression-of-interest-tokens', array('as' => 'api.expression-of-interest-tokens.create', 'uses' => 'Api\PostDataApiController@expressionOfInterestTokens' ));
    Route::put('expression-of-interest-tokens/{Id}', array('as' => 'api.expression-of-interest-tokens.update', 'uses' => 'Api\PutDataApiController@expressionOfInterestTokens' ));
    Route::delete('expression-of-interest-tokens/{Id}', array('as' => 'api.expression-of-interest-tokens.delete', 'uses' => 'Api\DeleteDataApiController@expressionOfInterestTokens' ));

    //eot_first_level_messages
    Route::get('eot-first-level-messages', array('as' => 'api.eot-first-level-messages', 'uses' => 'Api\GetDataApiController@eotFirstLevelMessages' ));
    Route::post('eot-first-level-messages', array('as' => 'api.eot-first-level-messages.create', 'uses' => 'Api\PostDataApiController@eotFirstLevelMessages' ));
    Route::put('eot-first-level-messages/{Id}', array('as' => 'api.eot-first-level-messages.update', 'uses' => 'Api\PutDataApiController@eotFirstLevelMessages' ));
    Route::delete('eot-first-level-messages/{Id}', array('as' => 'api.eot-first-level-messages.delete', 'uses' => 'Api\DeleteDataApiController@eotFirstLevelMessages' ));

    //external_app_attachments
    Route::get('external-app-attachments', array('as' => 'api.external-app-attachments', 'uses' => 'Api\GetDataApiController@externalAppAttachments' ));
    Route::post('external-app-attachments', array('as' => 'api.external-app-attachments.create', 'uses' => 'Api\PostDataApiController@externalAppAttachments' ));
    Route::put('external-app-attachments/{Id}', array('as' => 'api.external-app-attachments.update', 'uses' => 'Api\PutDataApiController@externalAppAttachments' ));
    Route::delete('external-app-attachments/{Id}', array('as' => 'api.external-app-attachments.delete', 'uses' => 'Api\DeleteDataApiController@externalAppAttachments' ));

    //external_app_company_attachments
    Route::get('external-app-company-attachments', array('as' => 'api.external-app-company-attachments', 'uses' => 'Api\GetDataApiController@externalAppCompanyAttachments' ));
    Route::post('external-app-company-attachments', array('as' => 'api.external-app-company-attachments.create', 'uses' => 'Api\PostDataApiController@externalAppCompanyAttachments' ));
    Route::put('external-app-company-attachments/{Id}', array('as' => 'api.external-app-company-attachments.update', 'uses' => 'Api\PutDataApiController@externalAppCompanyAttachments' ));
    Route::delete('external-app-company-attachments/{Id}', array('as' => 'api.external-app-company-attachments.delete', 'uses' => 'Api\DeleteDataApiController@externalAppCompanyAttachments' ));

    //eot_contractor_confirm_delays
    Route::get('eot-contractor-confirm-delays', array('as' => 'api.eot-contractor-confirm-delays', 'uses' => 'Api\GetDataApiController@eotContractorConfirmDelays' ));
    Route::post('eot-contractor-confirm-delays', array('as' => 'api.eot-contractor-confirm-delays.create', 'uses' => 'Api\PostDataApiController@eotContractorConfirmDelays' ));
    Route::put('eot-contractor-confirm-delays/{Id}', array('as' => 'api.eot-contractor-confirm-delays.update', 'uses' => 'Api\PutDataApiController@eotContractorConfirmDelays' ));
    Route::delete('eot-contractor-confirm-delays/{Id}', array('as' => 'api.eot-contractor-confirm-delays.delete', 'uses' => 'Api\DeleteDataApiController@eotContractorConfirmDelays' ));

    //extension_of_time_claims
    Route::get('extension-of-time-claims', array('as' => 'api.extension-of-time-claims', 'uses' => 'Api\GetDataApiController@extensionOfTimeClaims' ));
    Route::post('extension-of-time-claims', array('as' => 'api.extension-of-time-claims.create', 'uses' => 'Api\PostDataApiController@extensionOfTimeClaims' ));
    Route::put('extension-of-time-claims/{Id}', array('as' => 'api.extension-of-time-claims.update', 'uses' => 'Api\PutDataApiController@extensionOfTimeClaims' ));
    Route::delete('extension-of-time-claims/{Id}', array('as' => 'api.extension-of-time-claims.delete', 'uses' => 'Api\DeleteDataApiController@extensionOfTimeClaims' ));

    //external_application_client_outbound_logs
    Route::get('external-application-client-outbound-logs', array('as' => 'api.external-application-client-outbound-logs', 'uses' => 'Api\GetDataApiController@externalApplicationClientOutboundLogs' ));
    Route::post('external-application-client-outbound-logs', array('as' => 'api.external-application-client-outbound-logs.create', 'uses' => 'Api\PostDataApiController@externalApplicationClientOutboundLogs' ));
    Route::put('external-application-client-outbound-logs/{Id}', array('as' => 'api.external-application-client-outbound-logs.update', 'uses' => 'Api\PutDataApiController@externalApplicationClientOutboundLogs' ));
    Route::delete('external-application-client-outbound-logs/{Id}', array('as' => 'api.external-application-client-outbound-logs.delete', 'uses' => 'Api\DeleteDataApiController@externalApplicationClientOutboundLogs' ));

    //external_application_identifiers
    Route::get('external-application-identifiers', array('as' => 'api.external-application-identifiers', 'uses' => 'Api\GetDataApiController@externalApplicationIdentifiers' ));
    Route::post('external-application-identifiers', array('as' => 'api.external-application-identifiers.create', 'uses' => 'Api\PostDataApiController@externalApplicationIdentifiers' ));
    Route::put('external-application-identifiers/{Id}', array('as' => 'api.external-application-identifiers.update', 'uses' => 'Api\PutDataApiController@externalApplicationIdentifiers' ));
    Route::delete('external-application-identifiers/{Id}', array('as' => 'api.external-application-identifiers.delete', 'uses' => 'Api\DeleteDataApiController@externalApplicationIdentifiers' ));

    //external_application_client_outbound_authorizations
    Route::get('external-application-client-outbound-authorizations', array('as' => 'api.external-application-client-outbound-authorizations', 'uses' => 'Api\GetDataApiController@externalApplicationClientOutboundAuthorizations' ));
    Route::post('external-application-client-outbound-authorizations', array('as' => 'api.external-application-client-outbound-authorizations.create', 'uses' => 'Api\PostDataApiController@externalApplicationClientOutboundAuthorizations' ));
    Route::put('external-application-client-outbound-authorizations/{Id}', array('as' => 'api.external-application-client-outbound-authorizations.update', 'uses' => 'Api\PutDataApiController@externalApplicationClientOutboundAuthorizations' ));
    Route::delete('external-application-client-outbound-authorizations/{Id}', array('as' => 'api.external-application-client-outbound-authorizations.delete', 'uses' => 'Api\DeleteDataApiController@externalApplicationClientOutboundAuthorizations' ));

    //file_node_permissions
    Route::get('file-node-permissions', array('as' => 'api.file-node-permissions', 'uses' => 'Api\GetDataApiController@fileNodePermissions' ));
    Route::post('file-node-permissions', array('as' => 'api.file-node-permissions.create', 'uses' => 'Api\PostDataApiController@fileNodePermissions' ));
    Route::put('file-node-permissions/{Id}', array('as' => 'api.file-node-permissions.update', 'uses' => 'Api\PutDataApiController@fileNodePermissions' ));
    Route::delete('file-node-permissions/{Id}', array('as' => 'api.file-node-permissions.delete', 'uses' => 'Api\DeleteDataApiController@fileNodePermissions' ));

    //failed_jobs
    Route::get('failed-jobs', array('as' => 'api.failed-jobs', 'uses' => 'Api\GetDataApiController@failedJobs' ));
    Route::post('failed-jobs', array('as' => 'api.failed-jobs.create', 'uses' => 'Api\PostDataApiController@failedJobs' ));
    Route::put('failed-jobs/{Id}', array('as' => 'api.failed-jobs.update', 'uses' => 'Api\PutDataApiController@failedJobs' ));
    Route::delete('failed-jobs/{Id}', array('as' => 'api.failed-jobs.delete', 'uses' => 'Api\DeleteDataApiController@failedJobs' ));

    //finance_user_subsidiaries
    Route::get('finance-user-subsidiaries', array('as' => 'api.finance-user-subsidiaries', 'uses' => 'Api\GetDataApiController@financeUserSubsidiaries' ));
    Route::post('finance-user-subsidiaries', array('as' => 'api.finance-user-subsidiaries.create', 'uses' => 'Api\PostDataApiController@financeUserSubsidiaries' ));
    Route::put('finance-user-subsidiaries/{Id}', array('as' => 'api.finance-user-subsidiaries.update', 'uses' => 'Api\PutDataApiController@financeUserSubsidiaries' ));
    Route::delete('finance-user-subsidiaries/{Id}', array('as' => 'api.finance-user-subsidiaries.delete', 'uses' => 'Api\DeleteDataApiController@financeUserSubsidiaries' ));

    //form_of_tender_clauses
    Route::get('form-of-tender-clauses', array('as' => 'api.form-of-tender-clauses', 'uses' => 'Api\GetDataApiController@formOfTenderClauses' ));
    Route::post('form-of-tender-clauses', array('as' => 'api.form-of-tender-clauses.create', 'uses' => 'Api\PostDataApiController@formOfTenderClauses' ));
    Route::put('form-of-tender-clauses/{Id}', array('as' => 'api.form-of-tender-clauses.update', 'uses' => 'Api\PutDataApiController@formOfTenderClauses' ));
    Route::delete('form-of-tender-clauses/{Id}', array('as' => 'api.form-of-tender-clauses.delete', 'uses' => 'Api\DeleteDataApiController@formOfTenderClauses' ));

    //external_application_clients
    Route::get('external-application-clients', array('as' => 'api.external-application-clients', 'uses' => 'Api\GetDataApiController@externalApplicationClients' ));
    Route::post('external-application-clients', array('as' => 'api.external-application-clients.create', 'uses' => 'Api\PostDataApiController@externalApplicationClients' ));
    Route::put('external-application-clients/{Id}', array('as' => 'api.external-application-clients.update', 'uses' => 'Api\PutDataApiController@externalApplicationClients' ));
    Route::delete('external-application-clients/{Id}', array('as' => 'api.external-application-clients.delete', 'uses' => 'Api\DeleteDataApiController@externalApplicationClients' ));

    //file_nodes
    Route::get('file-nodes', array('as' => 'api.file-nodes', 'uses' => 'Api\GetDataApiController@fileNodes' ));
    Route::post('file-nodes', array('as' => 'api.file-nodes.create', 'uses' => 'Api\PostDataApiController@fileNodes' ));
    Route::put('file-nodes/{Id}', array('as' => 'api.file-nodes.update', 'uses' => 'Api\PutDataApiController@fileNodes' ));
    Route::delete('file-nodes/{Id}', array('as' => 'api.file-nodes.delete', 'uses' => 'Api\DeleteDataApiController@fileNodes' ));

    //form_columns
    Route::get('form-columns', array('as' => 'api.form-columns', 'uses' => 'Api\GetDataApiController@formColumns' ));
    Route::post('form-columns', array('as' => 'api.form-columns.create', 'uses' => 'Api\PostDataApiController@formColumns' ));
    Route::put('form-columns/{Id}', array('as' => 'api.form-columns.update', 'uses' => 'Api\PutDataApiController@formColumns' ));
    Route::delete('form-columns/{Id}', array('as' => 'api.form-columns.delete', 'uses' => 'Api\DeleteDataApiController@formColumns' ));

    //form_element_mappings
    Route::get('form-element-mappings', array('as' => 'api.form-element-mappings', 'uses' => 'Api\GetDataApiController@formElementMappings' ));
    Route::post('form-element-mappings', array('as' => 'api.form-element-mappings.create', 'uses' => 'Api\PostDataApiController@formElementMappings' ));
    Route::put('form-element-mappings/{Id}', array('as' => 'api.form-element-mappings.update', 'uses' => 'Api\PutDataApiController@formElementMappings' ));
    Route::delete('form-element-mappings/{Id}', array('as' => 'api.form-element-mappings.delete', 'uses' => 'Api\DeleteDataApiController@formElementMappings' ));

    //form_object_mappings
    Route::get('form-object-mappings', array('as' => 'api.form-object-mappings', 'uses' => 'Api\GetDataApiController@formObjectMappings' ));
    Route::post('form-object-mappings', array('as' => 'api.form-object-mappings.create', 'uses' => 'Api\PostDataApiController@formObjectMappings' ));
    Route::put('form-object-mappings/{Id}', array('as' => 'api.form-object-mappings.update', 'uses' => 'Api\PutDataApiController@formObjectMappings' ));
    Route::delete('form-object-mappings/{Id}', array('as' => 'api.form-object-mappings.delete', 'uses' => 'Api\DeleteDataApiController@formObjectMappings' ));

    //form_of_tenders
    Route::get('form-of-tenders', array('as' => 'api.form-of-tenders', 'uses' => 'Api\GetDataApiController@formOfTenders' ));
    Route::post('form-of-tenders', array('as' => 'api.form-of-tenders.create', 'uses' => 'Api\PostDataApiController@formOfTenders' ));
    Route::put('form-of-tenders/{Id}', array('as' => 'api.form-of-tenders.update', 'uses' => 'Api\PutDataApiController@formOfTenders' ));
    Route::delete('form-of-tenders/{Id}', array('as' => 'api.form-of-tenders.delete', 'uses' => 'Api\DeleteDataApiController@formOfTenders' ));

    //form_of_tender_addresses
    Route::get('form-of-tender-addresses', array('as' => 'api.form-of-tender-addresses', 'uses' => 'Api\GetDataApiController@formOfTenderAddresses' ));
    Route::post('form-of-tender-addresses', array('as' => 'api.form-of-tender-addresses.create', 'uses' => 'Api\PostDataApiController@formOfTenderAddresses' ));
    Route::put('form-of-tender-addresses/{Id}', array('as' => 'api.form-of-tender-addresses.update', 'uses' => 'Api\PutDataApiController@formOfTenderAddresses' ));
    Route::delete('form-of-tender-addresses/{Id}', array('as' => 'api.form-of-tender-addresses.delete', 'uses' => 'Api\DeleteDataApiController@formOfTenderAddresses' ));

    //form_of_tender_logs
    Route::get('form-of-tender-logs', array('as' => 'api.form-of-tender-logs', 'uses' => 'Api\GetDataApiController@formOfTenderLogs' ));
    Route::post('form-of-tender-logs', array('as' => 'api.form-of-tender-logs.create', 'uses' => 'Api\PostDataApiController@formOfTenderLogs' ));
    Route::put('form-of-tender-logs/{Id}', array('as' => 'api.form-of-tender-logs.update', 'uses' => 'Api\PutDataApiController@formOfTenderLogs' ));
    Route::delete('form-of-tender-logs/{Id}', array('as' => 'api.form-of-tender-logs.delete', 'uses' => 'Api\DeleteDataApiController@formOfTenderLogs' ));

    //form_of_tender_tender_alternatives
    Route::get('form-of-tender-tender-alternatives', array('as' => 'api.form-of-tender-tender-alternatives', 'uses' => 'Api\GetDataApiController@formOfTenderTenderAlternatives' ));
    Route::post('form-of-tender-tender-alternatives', array('as' => 'api.form-of-tender-tender-alternatives.create', 'uses' => 'Api\PostDataApiController@formOfTenderTenderAlternatives' ));
    Route::put('form-of-tender-tender-alternatives/{Id}', array('as' => 'api.form-of-tender-tender-alternatives.update', 'uses' => 'Api\PutDataApiController@formOfTenderTenderAlternatives' ));
    Route::delete('form-of-tender-tender-alternatives/{Id}', array('as' => 'api.form-of-tender-tender-alternatives.delete', 'uses' => 'Api\DeleteDataApiController@formOfTenderTenderAlternatives' ));

    //forum_posts
    Route::get('forum-posts', array('as' => 'api.forum-posts', 'uses' => 'Api\GetDataApiController@forumPosts' ));
    Route::post('forum-posts', array('as' => 'api.forum-posts.create', 'uses' => 'Api\PostDataApiController@forumPosts' ));
    Route::put('forum-posts/{Id}', array('as' => 'api.forum-posts.update', 'uses' => 'Api\PutDataApiController@forumPosts' ));
    Route::delete('forum-posts/{Id}', array('as' => 'api.forum-posts.delete', 'uses' => 'Api\DeleteDataApiController@forumPosts' ));

    //forum_posts_read_log
    Route::get('forum-posts-read-log', array('as' => 'api.forum-posts-read-log', 'uses' => 'Api\GetDataApiController@forumPostsReadLog' ));
    Route::post('forum-posts-read-log', array('as' => 'api.forum-posts-read-log.create', 'uses' => 'Api\PostDataApiController@forumPostsReadLog' ));
    Route::put('forum-posts-read-log/{Id}', array('as' => 'api.forum-posts-read-log.update', 'uses' => 'Api\PutDataApiController@forumPostsReadLog' ));
    Route::delete('forum-posts-read-log/{Id}', array('as' => 'api.forum-posts-read-log.delete', 'uses' => 'Api\DeleteDataApiController@forumPostsReadLog' ));

    //general_settings
    Route::get('general-settings', array('as' => 'api.general-settings', 'uses' => 'Api\GetDataApiController@generalSettings' ));
    Route::post('general-settings', array('as' => 'api.general-settings.create', 'uses' => 'Api\PostDataApiController@generalSettings' ));
    Route::put('general-settings/{Id}', array('as' => 'api.general-settings.update', 'uses' => 'Api\PutDataApiController@generalSettings' ));
    Route::delete('general-settings/{Id}', array('as' => 'api.general-settings.delete', 'uses' => 'Api\DeleteDataApiController@generalSettings' ));

    //indonesia_civil_contract_contractual_claim_responses
    Route::get('indonesia-civil-contract-contractual-claim-responses', array('as' => 'api.indonesia-civil-contract-contractual-claim-responses', 'uses' => 'Api\GetDataApiController@indonesiaCivilContractContractualClaimResponses' ));
    Route::post('indonesia-civil-contract-contractual-claim-responses', array('as' => 'api.indonesia-civil-contract-contractual-claim-responses.create', 'uses' => 'Api\PostDataApiController@indonesiaCivilContractContractualClaimResponses' ));
    Route::put('indonesia-civil-contract-contractual-claim-responses/{Id}', array('as' => 'api.indonesia-civil-contract-contractual-claim-responses.update', 'uses' => 'Api\PutDataApiController@indonesiaCivilContractContractualClaimResponses' ));
    Route::delete('indonesia-civil-contract-contractual-claim-responses/{Id}', array('as' => 'api.indonesia-civil-contract-contractual-claim-responses.delete', 'uses' => 'Api\DeleteDataApiController@indonesiaCivilContractContractualClaimResponses' ));

    //form_of_tender_headers
    Route::get('form-of-tender-headers', array('as' => 'api.form-of-tender-headers', 'uses' => 'Api\GetDataApiController@formOfTenderHeaders' ));
    Route::post('form-of-tender-headers', array('as' => 'api.form-of-tender-headers.create', 'uses' => 'Api\PostDataApiController@formOfTenderHeaders' ));
    Route::put('form-of-tender-headers/{Id}', array('as' => 'api.form-of-tender-headers.update', 'uses' => 'Api\PutDataApiController@formOfTenderHeaders' ));
    Route::delete('form-of-tender-headers/{Id}', array('as' => 'api.form-of-tender-headers.delete', 'uses' => 'Api\DeleteDataApiController@formOfTenderHeaders' ));

    //form_of_tender_print_settings
    Route::get('form-of-tender-print-settings', array('as' => 'api.form-of-tender-print-settings', 'uses' => 'Api\GetDataApiController@formOfTenderPrintSettings' ));
    Route::post('form-of-tender-print-settings', array('as' => 'api.form-of-tender-print-settings.create', 'uses' => 'Api\PostDataApiController@formOfTenderPrintSettings' ));
    Route::put('form-of-tender-print-settings/{Id}', array('as' => 'api.form-of-tender-print-settings.update', 'uses' => 'Api\PutDataApiController@formOfTenderPrintSettings' ));
    Route::delete('form-of-tender-print-settings/{Id}', array('as' => 'api.form-of-tender-print-settings.delete', 'uses' => 'Api\DeleteDataApiController@formOfTenderPrintSettings' ));

    //forum_threads
    Route::get('forum-threads', array('as' => 'api.forum-threads', 'uses' => 'Api\GetDataApiController@forumThreads' ));
    Route::post('forum-threads', array('as' => 'api.forum-threads.create', 'uses' => 'Api\PostDataApiController@forumThreads' ));
    Route::put('forum-threads/{Id}', array('as' => 'api.forum-threads.update', 'uses' => 'Api\PutDataApiController@forumThreads' ));
    Route::delete('forum-threads/{Id}', array('as' => 'api.forum-threads.delete', 'uses' => 'Api\DeleteDataApiController@forumThreads' ));

    //forum_thread_privacy_log
    Route::get('forum-thread-privacy-log', array('as' => 'api.forum-thread-privacy-log', 'uses' => 'Api\GetDataApiController@forumThreadPrivacyLog' ));
    Route::post('forum-thread-privacy-log', array('as' => 'api.forum-thread-privacy-log.create', 'uses' => 'Api\PostDataApiController@forumThreadPrivacyLog' ));
    Route::put('forum-thread-privacy-log/{Id}', array('as' => 'api.forum-thread-privacy-log.update', 'uses' => 'Api\PutDataApiController@forumThreadPrivacyLog' ));
    Route::delete('forum-thread-privacy-log/{Id}', array('as' => 'api.forum-thread-privacy-log.delete', 'uses' => 'Api\DeleteDataApiController@forumThreadPrivacyLog' ));

    //forum_thread_user
    Route::get('forum-thread-user', array('as' => 'api.forum-thread-user', 'uses' => 'Api\GetDataApiController@forumThreadUser' ));
    Route::post('forum-thread-user', array('as' => 'api.forum-thread-user.create', 'uses' => 'Api\PostDataApiController@forumThreadUser' ));
    Route::put('forum-thread-user/{Id}', array('as' => 'api.forum-thread-user.update', 'uses' => 'Api\PutDataApiController@forumThreadUser' ));
    Route::delete('forum-thread-user/{Id}', array('as' => 'api.forum-thread-user.delete', 'uses' => 'Api\DeleteDataApiController@forumThreadUser' ));

    //ic_info_gross_values_attachments
    Route::get('ic-info-gross-values-attachments', array('as' => 'api.ic-info-gross-values-attachments', 'uses' => 'Api\GetDataApiController@icInfoGrossValuesAttachments' ));
    Route::post('ic-info-gross-values-attachments', array('as' => 'api.ic-info-gross-values-attachments.create', 'uses' => 'Api\PostDataApiController@icInfoGrossValuesAttachments' ));
    Route::put('ic-info-gross-values-attachments/{Id}', array('as' => 'api.ic-info-gross-values-attachments.update', 'uses' => 'Api\PutDataApiController@icInfoGrossValuesAttachments' ));
    Route::delete('ic-info-gross-values-attachments/{Id}', array('as' => 'api.ic-info-gross-values-attachments.delete', 'uses' => 'Api\DeleteDataApiController@icInfoGrossValuesAttachments' ));

    //ic_info_nett_addition_omission_attachments
    Route::get('ic-info-nett-addition-omission-attachments', array('as' => 'api.ic-info-nett-addition-omission-attachments', 'uses' => 'Api\GetDataApiController@icInfoNettAdditionOmissionAttachments' ));
    Route::post('ic-info-nett-addition-omission-attachments', array('as' => 'api.ic-info-nett-addition-omission-attachments.create', 'uses' => 'Api\PostDataApiController@icInfoNettAdditionOmissionAttachments' ));
    Route::put('ic-info-nett-addition-omission-attachments/{Id}', array('as' => 'api.ic-info-nett-addition-omission-attachments.update', 'uses' => 'Api\PutDataApiController@icInfoNettAdditionOmissionAttachments' ));
    Route::delete('ic-info-nett-addition-omission-attachments/{Id}', array('as' => 'api.ic-info-nett-addition-omission-attachments.delete', 'uses' => 'Api\DeleteDataApiController@icInfoNettAdditionOmissionAttachments' ));

    //indonesia_civil_contract_architect_instructions
    Route::get('indonesia-civil-contract-architect-instructions', array('as' => 'api.indonesia-civil-contract-architect-instructions', 'uses' => 'Api\GetDataApiController@indonesiaCivilContractArchitectInstructions' ));
    Route::post('indonesia-civil-contract-architect-instructions', array('as' => 'api.indonesia-civil-contract-architect-instructions.create', 'uses' => 'Api\PostDataApiController@indonesiaCivilContractArchitectInstructions' ));
    Route::put('indonesia-civil-contract-architect-instructions/{Id}', array('as' => 'api.indonesia-civil-contract-architect-instructions.update', 'uses' => 'Api\PutDataApiController@indonesiaCivilContractArchitectInstructions' ));
    Route::delete('indonesia-civil-contract-architect-instructions/{Id}', array('as' => 'api.indonesia-civil-contract-architect-instructions.delete', 'uses' => 'Api\DeleteDataApiController@indonesiaCivilContractArchitectInstructions' ));

    //indonesia_civil_contract_ai_rfi
    Route::get('indonesia-civil-contract-ai-rfi', array('as' => 'api.indonesia-civil-contract-ai-rfi', 'uses' => 'Api\GetDataApiController@indonesiaCivilContractAiRfi' ));
    Route::post('indonesia-civil-contract-ai-rfi', array('as' => 'api.indonesia-civil-contract-ai-rfi.create', 'uses' => 'Api\PostDataApiController@indonesiaCivilContractAiRfi' ));
    Route::put('indonesia-civil-contract-ai-rfi/{Id}', array('as' => 'api.indonesia-civil-contract-ai-rfi.update', 'uses' => 'Api\PutDataApiController@indonesiaCivilContractAiRfi' ));
    Route::delete('indonesia-civil-contract-ai-rfi/{Id}', array('as' => 'api.indonesia-civil-contract-ai-rfi.delete', 'uses' => 'Api\DeleteDataApiController@indonesiaCivilContractAiRfi' ));

    //e_bidding_rankings
    Route::get('e-bidding-rankings', array('as' => 'api.e-bidding-rankings', 'uses' => 'Api\GetDataApiController@eBiddingRankings' ));
    Route::post('e-bidding-rankings', array('as' => 'api.e-bidding-rankings.create', 'uses' => 'Api\PostDataApiController@eBiddingRankings' ));
    Route::put('e-bidding-rankings/{Id}', array('as' => 'api.e-bidding-rankings.update', 'uses' => 'Api\PutDataApiController@eBiddingRankings' ));
    Route::delete('e-bidding-rankings/{Id}', array('as' => 'api.e-bidding-rankings.delete', 'uses' => 'Api\DeleteDataApiController@eBiddingRankings' ));

    //indonesia_civil_contract_ew_le
    Route::get('indonesia-civil-contract-ew-le', array('as' => 'api.indonesia-civil-contract-ew-le', 'uses' => 'Api\GetDataApiController@indonesiaCivilContractEwLe' ));
    Route::post('indonesia-civil-contract-ew-le', array('as' => 'api.indonesia-civil-contract-ew-le.create', 'uses' => 'Api\PostDataApiController@indonesiaCivilContractEwLe' ));
    Route::put('indonesia-civil-contract-ew-le/{Id}', array('as' => 'api.indonesia-civil-contract-ew-le.update', 'uses' => 'Api\PutDataApiController@indonesiaCivilContractEwLe' ));
    Route::delete('indonesia-civil-contract-ew-le/{Id}', array('as' => 'api.indonesia-civil-contract-ew-le.delete', 'uses' => 'Api\DeleteDataApiController@indonesiaCivilContractEwLe' ));

    //indonesia_civil_contract_information
    Route::get('indonesia-civil-contract-information', array('as' => 'api.indonesia-civil-contract-information', 'uses' => 'Api\GetDataApiController@indonesiaCivilContractInformation' ));
    Route::post('indonesia-civil-contract-information', array('as' => 'api.indonesia-civil-contract-information.create', 'uses' => 'Api\PostDataApiController@indonesiaCivilContractInformation' ));
    Route::put('indonesia-civil-contract-information/{Id}', array('as' => 'api.indonesia-civil-contract-information.update', 'uses' => 'Api\PutDataApiController@indonesiaCivilContractInformation' ));
    Route::delete('indonesia-civil-contract-information/{Id}', array('as' => 'api.indonesia-civil-contract-information.delete', 'uses' => 'Api\DeleteDataApiController@indonesiaCivilContractInformation' ));

    //inspection_results
    Route::get('inspection-results', array('as' => 'api.inspection-results', 'uses' => 'Api\GetDataApiController@inspectionResults' ));
    Route::post('inspection-results', array('as' => 'api.inspection-results.create', 'uses' => 'Api\PostDataApiController@inspectionResults' ));
    Route::put('inspection-results/{Id}', array('as' => 'api.inspection-results.update', 'uses' => 'Api\PutDataApiController@inspectionResults' ));
    Route::delete('inspection-results/{Id}', array('as' => 'api.inspection-results.delete', 'uses' => 'Api\DeleteDataApiController@inspectionResults' ));

    //inspection_submitters
    Route::get('inspection-submitters', array('as' => 'api.inspection-submitters', 'uses' => 'Api\GetDataApiController@inspectionSubmitters' ));
    Route::post('inspection-submitters', array('as' => 'api.inspection-submitters.create', 'uses' => 'Api\PostDataApiController@inspectionSubmitters' ));
    Route::put('inspection-submitters/{Id}', array('as' => 'api.inspection-submitters.update', 'uses' => 'Api\PutDataApiController@inspectionSubmitters' ));
    Route::delete('inspection-submitters/{Id}', array('as' => 'api.inspection-submitters.delete', 'uses' => 'Api\DeleteDataApiController@inspectionSubmitters' ));

    //inspection_lists
    Route::get('inspection-lists', array('as' => 'api.inspection-lists', 'uses' => 'Api\GetDataApiController@inspectionLists' ));
    Route::post('inspection-lists', array('as' => 'api.inspection-lists.create', 'uses' => 'Api\PostDataApiController@inspectionLists' ));
    Route::put('inspection-lists/{Id}', array('as' => 'api.inspection-lists.update', 'uses' => 'Api\PutDataApiController@inspectionLists' ));
    Route::delete('inspection-lists/{Id}', array('as' => 'api.inspection-lists.delete', 'uses' => 'Api\DeleteDataApiController@inspectionLists' ));

    //indonesia_civil_contract_extensions_of_time
    Route::get('indonesia-civil-contract-extensions-of-time', array('as' => 'api.indonesia-civil-contract-extensions-of-time', 'uses' => 'Api\GetDataApiController@indonesiaCivilContractExtensionsOfTime' ));
    Route::post('indonesia-civil-contract-extensions-of-time', array('as' => 'api.indonesia-civil-contract-extensions-of-time.create', 'uses' => 'Api\PostDataApiController@indonesiaCivilContractExtensionsOfTime' ));
    Route::put('indonesia-civil-contract-extensions-of-time/{Id}', array('as' => 'api.indonesia-civil-contract-extensions-of-time.update', 'uses' => 'Api\PutDataApiController@indonesiaCivilContractExtensionsOfTime' ));
    Route::delete('indonesia-civil-contract-extensions-of-time/{Id}', array('as' => 'api.indonesia-civil-contract-extensions-of-time.delete', 'uses' => 'Api\DeleteDataApiController@indonesiaCivilContractExtensionsOfTime' ));

    //indonesia_civil_contract_loss_and_expenses
    Route::get('indonesia-civil-contract-loss-and-expenses', array('as' => 'api.indonesia-civil-contract-loss-and-expenses', 'uses' => 'Api\GetDataApiController@indonesiaCivilContractLossAndExpenses' ));
    Route::post('indonesia-civil-contract-loss-and-expenses', array('as' => 'api.indonesia-civil-contract-loss-and-expenses.create', 'uses' => 'Api\PostDataApiController@indonesiaCivilContractLossAndExpenses' ));
    Route::put('indonesia-civil-contract-loss-and-expenses/{Id}', array('as' => 'api.indonesia-civil-contract-loss-and-expenses.update', 'uses' => 'Api\PutDataApiController@indonesiaCivilContractLossAndExpenses' ));
    Route::delete('indonesia-civil-contract-loss-and-expenses/{Id}', array('as' => 'api.indonesia-civil-contract-loss-and-expenses.delete', 'uses' => 'Api\DeleteDataApiController@indonesiaCivilContractLossAndExpenses' ));

    //inspection_groups
    Route::get('inspection-groups', array('as' => 'api.inspection-groups', 'uses' => 'Api\GetDataApiController@inspectionGroups' ));
    Route::post('inspection-groups', array('as' => 'api.inspection-groups.create', 'uses' => 'Api\PostDataApiController@inspectionGroups' ));
    Route::put('inspection-groups/{Id}', array('as' => 'api.inspection-groups.update', 'uses' => 'Api\PutDataApiController@inspectionGroups' ));
    Route::delete('inspection-groups/{Id}', array('as' => 'api.inspection-groups.delete', 'uses' => 'Api\DeleteDataApiController@inspectionGroups' ));

    //inspection_group_inspection_list_category
    Route::get('inspection-group-inspection-list-category', array('as' => 'api.inspection-group-inspection-list-category', 'uses' => 'Api\GetDataApiController@inspectionGroupInspectionListCategory' ));
    Route::post('inspection-group-inspection-list-category', array('as' => 'api.inspection-group-inspection-list-category.create', 'uses' => 'Api\PostDataApiController@inspectionGroupInspectionListCategory' ));
    Route::put('inspection-group-inspection-list-category/{Id}', array('as' => 'api.inspection-group-inspection-list-category.update', 'uses' => 'Api\PutDataApiController@inspectionGroupInspectionListCategory' ));
    Route::delete('inspection-group-inspection-list-category/{Id}', array('as' => 'api.inspection-group-inspection-list-category.delete', 'uses' => 'Api\DeleteDataApiController@inspectionGroupInspectionListCategory' ));

    //inspection_list_categories
    Route::get('inspection-list-categories', array('as' => 'api.inspection-list-categories', 'uses' => 'Api\GetDataApiController@inspectionListCategories' ));
    Route::post('inspection-list-categories', array('as' => 'api.inspection-list-categories.create', 'uses' => 'Api\PostDataApiController@inspectionListCategories' ));
    Route::put('inspection-list-categories/{Id}', array('as' => 'api.inspection-list-categories.update', 'uses' => 'Api\PutDataApiController@inspectionListCategories' ));
    Route::delete('inspection-list-categories/{Id}', array('as' => 'api.inspection-list-categories.delete', 'uses' => 'Api\DeleteDataApiController@inspectionListCategories' ));

    //inspection_group_users
    Route::get('inspection-group-users', array('as' => 'api.inspection-group-users', 'uses' => 'Api\GetDataApiController@inspectionGroupUsers' ));
    Route::post('inspection-group-users', array('as' => 'api.inspection-group-users.create', 'uses' => 'Api\PostDataApiController@inspectionGroupUsers' ));
    Route::put('inspection-group-users/{Id}', array('as' => 'api.inspection-group-users.update', 'uses' => 'Api\PutDataApiController@inspectionGroupUsers' ));
    Route::delete('inspection-group-users/{Id}', array('as' => 'api.inspection-group-users.delete', 'uses' => 'Api\DeleteDataApiController@inspectionGroupUsers' ));

    //inspection_roles
    Route::get('inspection-roles', array('as' => 'api.inspection-roles', 'uses' => 'Api\GetDataApiController@inspectionRoles' ));
    Route::post('inspection-roles', array('as' => 'api.inspection-roles.create', 'uses' => 'Api\PostDataApiController@inspectionRoles' ));
    Route::put('inspection-roles/{Id}', array('as' => 'api.inspection-roles.update', 'uses' => 'Api\PutDataApiController@inspectionRoles' ));
    Route::delete('inspection-roles/{Id}', array('as' => 'api.inspection-roles.delete', 'uses' => 'Api\DeleteDataApiController@inspectionRoles' ));

    //inspection_list_items
    Route::get('inspection-list-items', array('as' => 'api.inspection-list-items', 'uses' => 'Api\GetDataApiController@inspectionListItems' ));
    Route::post('inspection-list-items', array('as' => 'api.inspection-list-items.create', 'uses' => 'Api\PostDataApiController@inspectionListItems' ));
    Route::put('inspection-list-items/{Id}', array('as' => 'api.inspection-list-items.update', 'uses' => 'Api\PutDataApiController@inspectionListItems' ));
    Route::delete('inspection-list-items/{Id}', array('as' => 'api.inspection-list-items.delete', 'uses' => 'Api\DeleteDataApiController@inspectionListItems' ));

    //inspection_item_results
    Route::get('inspection-item-results', array('as' => 'api.inspection-item-results', 'uses' => 'Api\GetDataApiController@inspectionItemResults' ));
    Route::post('inspection-item-results', array('as' => 'api.inspection-item-results.create', 'uses' => 'Api\PostDataApiController@inspectionItemResults' ));
    Route::put('inspection-item-results/{Id}', array('as' => 'api.inspection-item-results.update', 'uses' => 'Api\PutDataApiController@inspectionItemResults' ));
    Route::delete('inspection-item-results/{Id}', array('as' => 'api.inspection-item-results.delete', 'uses' => 'Api\DeleteDataApiController@inspectionItemResults' ));

    //inspection_list_category_additional_fields
    Route::get('inspection-list-category-additional-fields', array('as' => 'api.inspection-list-category-additional-fields', 'uses' => 'Api\GetDataApiController@inspectionListCategoryAdditionalFields' ));
    Route::post('inspection-list-category-additional-fields', array('as' => 'api.inspection-list-category-additional-fields.create', 'uses' => 'Api\PostDataApiController@inspectionListCategoryAdditionalFields' ));
    Route::put('inspection-list-category-additional-fields/{Id}', array('as' => 'api.inspection-list-category-additional-fields.update', 'uses' => 'Api\PutDataApiController@inspectionListCategoryAdditionalFields' ));
    Route::delete('inspection-list-category-additional-fields/{Id}', array('as' => 'api.inspection-list-category-additional-fields.delete', 'uses' => 'Api\DeleteDataApiController@inspectionListCategoryAdditionalFields' ));

    //inspection_verifier_template
    Route::get('inspection-verifier-template', array('as' => 'api.inspection-verifier-template', 'uses' => 'Api\GetDataApiController@inspectionVerifierTemplate' ));
    Route::post('inspection-verifier-template', array('as' => 'api.inspection-verifier-template.create', 'uses' => 'Api\PostDataApiController@inspectionVerifierTemplate' ));
    Route::put('inspection-verifier-template/{Id}', array('as' => 'api.inspection-verifier-template.update', 'uses' => 'Api\PutDataApiController@inspectionVerifierTemplate' ));
    Route::delete('inspection-verifier-template/{Id}', array('as' => 'api.inspection-verifier-template.delete', 'uses' => 'Api\DeleteDataApiController@inspectionVerifierTemplate' ));

    //letter_of_award_clause_comments
    Route::get('letter-of-award-clause-comments', array('as' => 'api.letter-of-award-clause-comments', 'uses' => 'Api\GetDataApiController@letterOfAwardClauseComments' ));
    Route::post('letter-of-award-clause-comments', array('as' => 'api.letter-of-award-clause-comments.create', 'uses' => 'Api\PostDataApiController@letterOfAwardClauseComments' ));
    Route::put('letter-of-award-clause-comments/{Id}', array('as' => 'api.letter-of-award-clause-comments.update', 'uses' => 'Api\PutDataApiController@letterOfAwardClauseComments' ));
    Route::delete('letter-of-award-clause-comments/{Id}', array('as' => 'api.letter-of-award-clause-comments.delete', 'uses' => 'Api\DeleteDataApiController@letterOfAwardClauseComments' ));

    //inspections
    Route::get('inspections', array('as' => 'api.inspections', 'uses' => 'Api\GetDataApiController@inspections' ));
    Route::post('inspections', array('as' => 'api.inspections.create', 'uses' => 'Api\PostDataApiController@inspections' ));
    Route::put('inspections/{Id}', array('as' => 'api.inspections.update', 'uses' => 'Api\PutDataApiController@inspections' ));
    Route::delete('inspections/{Id}', array('as' => 'api.inspections.delete', 'uses' => 'Api\DeleteDataApiController@inspections' ));

    //interim_claim_informations
    Route::get('interim-claim-informations', array('as' => 'api.interim-claim-informations', 'uses' => 'Api\GetDataApiController@interimClaimInformations' ));
    Route::post('interim-claim-informations', array('as' => 'api.interim-claim-informations.create', 'uses' => 'Api\PostDataApiController@interimClaimInformations' ));
    Route::put('interim-claim-informations/{Id}', array('as' => 'api.interim-claim-informations.update', 'uses' => 'Api\PutDataApiController@interimClaimInformations' ));
    Route::delete('interim-claim-informations/{Id}', array('as' => 'api.interim-claim-informations.delete', 'uses' => 'Api\DeleteDataApiController@interimClaimInformations' ));

    //letter_of_award_clauses
    Route::get('letter-of-award-clauses', array('as' => 'api.letter-of-award-clauses', 'uses' => 'Api\GetDataApiController@letterOfAwardClauses' ));
    Route::post('letter-of-award-clauses', array('as' => 'api.letter-of-award-clauses.create', 'uses' => 'Api\PostDataApiController@letterOfAwardClauses' ));
    Route::put('letter-of-award-clauses/{Id}', array('as' => 'api.letter-of-award-clauses.update', 'uses' => 'Api\PutDataApiController@letterOfAwardClauses' ));
    Route::delete('letter-of-award-clauses/{Id}', array('as' => 'api.letter-of-award-clauses.delete', 'uses' => 'Api\DeleteDataApiController@letterOfAwardClauses' ));

    //letter_of_award_contract_details
    Route::get('letter-of-award-contract-details', array('as' => 'api.letter-of-award-contract-details', 'uses' => 'Api\GetDataApiController@letterOfAwardContractDetails' ));
    Route::post('letter-of-award-contract-details', array('as' => 'api.letter-of-award-contract-details.create', 'uses' => 'Api\PostDataApiController@letterOfAwardContractDetails' ));
    Route::put('letter-of-award-contract-details/{Id}', array('as' => 'api.letter-of-award-contract-details.update', 'uses' => 'Api\PutDataApiController@letterOfAwardContractDetails' ));
    Route::delete('letter-of-award-contract-details/{Id}', array('as' => 'api.letter-of-award-contract-details.delete', 'uses' => 'Api\DeleteDataApiController@letterOfAwardContractDetails' ));

    //letter_of_award_logs
    Route::get('letter-of-award-logs', array('as' => 'api.letter-of-award-logs', 'uses' => 'Api\GetDataApiController@letterOfAwardLogs' ));
    Route::post('letter-of-award-logs', array('as' => 'api.letter-of-award-logs.create', 'uses' => 'Api\PostDataApiController@letterOfAwardLogs' ));
    Route::put('letter-of-award-logs/{Id}', array('as' => 'api.letter-of-award-logs.update', 'uses' => 'Api\PutDataApiController@letterOfAwardLogs' ));
    Route::delete('letter-of-award-logs/{Id}', array('as' => 'api.letter-of-award-logs.delete', 'uses' => 'Api\DeleteDataApiController@letterOfAwardLogs' ));

    //labours
    Route::get('labours', array('as' => 'api.labours', 'uses' => 'Api\GetDataApiController@labours' ));
    Route::post('labours', array('as' => 'api.labours.create', 'uses' => 'Api\PostDataApiController@labours' ));
    Route::put('labours/{Id}', array('as' => 'api.labours.update', 'uses' => 'Api\PutDataApiController@labours' ));
    Route::delete('labours/{Id}', array('as' => 'api.labours.delete', 'uses' => 'Api\DeleteDataApiController@labours' ));

    //instructions_to_contractors
    Route::get('instructions-to-contractors', array('as' => 'api.instructions-to-contractors', 'uses' => 'Api\GetDataApiController@instructionsToContractors' ));
    Route::post('instructions-to-contractors', array('as' => 'api.instructions-to-contractors.create', 'uses' => 'Api\PostDataApiController@instructionsToContractors' ));
    Route::put('instructions-to-contractors/{Id}', array('as' => 'api.instructions-to-contractors.update', 'uses' => 'Api\PutDataApiController@instructionsToContractors' ));
    Route::delete('instructions-to-contractors/{Id}', array('as' => 'api.instructions-to-contractors.delete', 'uses' => 'Api\DeleteDataApiController@instructionsToContractors' ));

    //letter_of_award_print_settings
    Route::get('letter-of-award-print-settings', array('as' => 'api.letter-of-award-print-settings', 'uses' => 'Api\GetDataApiController@letterOfAwardPrintSettings' ));
    Route::post('letter-of-award-print-settings', array('as' => 'api.letter-of-award-print-settings.create', 'uses' => 'Api\PostDataApiController@letterOfAwardPrintSettings' ));
    Route::put('letter-of-award-print-settings/{Id}', array('as' => 'api.letter-of-award-print-settings.update', 'uses' => 'Api\PutDataApiController@letterOfAwardPrintSettings' ));
    Route::delete('letter-of-award-print-settings/{Id}', array('as' => 'api.letter-of-award-print-settings.delete', 'uses' => 'Api\DeleteDataApiController@letterOfAwardPrintSettings' ));

    //letter_of_award_signatories
    Route::get('letter-of-award-signatories', array('as' => 'api.letter-of-award-signatories', 'uses' => 'Api\GetDataApiController@letterOfAwardSignatories' ));
    Route::post('letter-of-award-signatories', array('as' => 'api.letter-of-award-signatories.create', 'uses' => 'Api\PostDataApiController@letterOfAwardSignatories' ));
    Route::put('letter-of-award-signatories/{Id}', array('as' => 'api.letter-of-award-signatories.update', 'uses' => 'Api\PutDataApiController@letterOfAwardSignatories' ));
    Route::delete('letter-of-award-signatories/{Id}', array('as' => 'api.letter-of-award-signatories.delete', 'uses' => 'Api\DeleteDataApiController@letterOfAwardSignatories' ));

    //languages
    Route::get('languages', array('as' => 'api.languages', 'uses' => 'Api\GetDataApiController@languages' ));
    Route::post('languages', array('as' => 'api.languages.create', 'uses' => 'Api\PostDataApiController@languages' ));
    Route::put('languages/{Id}', array('as' => 'api.languages.update', 'uses' => 'Api\PutDataApiController@languages' ));
    Route::delete('languages/{Id}', array('as' => 'api.languages.delete', 'uses' => 'Api\DeleteDataApiController@languages' ));

    //licenses
    Route::get('licenses', array('as' => 'api.licenses', 'uses' => 'Api\GetDataApiController@licenses' ));
    Route::post('licenses', array('as' => 'api.licenses.create', 'uses' => 'Api\PostDataApiController@licenses' ));
    Route::put('licenses/{Id}', array('as' => 'api.licenses.update', 'uses' => 'Api\PutDataApiController@licenses' ));
    Route::delete('licenses/{Id}', array('as' => 'api.licenses.delete', 'uses' => 'Api\DeleteDataApiController@licenses' ));

    //loss_or_and_expenses
    Route::get('loss-or-and-expenses', array('as' => 'api.loss-or-and-expenses', 'uses' => 'Api\GetDataApiController@lossOrAndExpenses' ));
    Route::post('loss-or-and-expenses', array('as' => 'api.loss-or-and-expenses.create', 'uses' => 'Api\PostDataApiController@lossOrAndExpenses' ));
    Route::put('loss-or-and-expenses/{Id}', array('as' => 'api.loss-or-and-expenses.update', 'uses' => 'Api\PutDataApiController@lossOrAndExpenses' ));
    Route::delete('loss-or-and-expenses/{Id}', array('as' => 'api.loss-or-and-expenses.delete', 'uses' => 'Api\DeleteDataApiController@lossOrAndExpenses' ));

    //loe_fourth_level_messages
    Route::get('loe-fourth-level-messages', array('as' => 'api.loe-fourth-level-messages', 'uses' => 'Api\GetDataApiController@loeFourthLevelMessages' ));
    Route::post('loe-fourth-level-messages', array('as' => 'api.loe-fourth-level-messages.create', 'uses' => 'Api\PostDataApiController@loeFourthLevelMessages' ));
    Route::put('loe-fourth-level-messages/{Id}', array('as' => 'api.loe-fourth-level-messages.update', 'uses' => 'Api\PutDataApiController@loeFourthLevelMessages' ));
    Route::delete('loe-fourth-level-messages/{Id}', array('as' => 'api.loe-fourth-level-messages.delete', 'uses' => 'Api\DeleteDataApiController@loeFourthLevelMessages' ));

    //loe_second_level_messages
    Route::get('loe-second-level-messages', array('as' => 'api.loe-second-level-messages', 'uses' => 'Api\GetDataApiController@loeSecondLevelMessages' ));
    Route::post('loe-second-level-messages', array('as' => 'api.loe-second-level-messages.create', 'uses' => 'Api\PostDataApiController@loeSecondLevelMessages' ));
    Route::put('loe-second-level-messages/{Id}', array('as' => 'api.loe-second-level-messages.update', 'uses' => 'Api\PutDataApiController@loeSecondLevelMessages' ));
    Route::delete('loe-second-level-messages/{Id}', array('as' => 'api.loe-second-level-messages.delete', 'uses' => 'Api\DeleteDataApiController@loeSecondLevelMessages' ));

    //loe_third_level_messages
    Route::get('loe-third-level-messages', array('as' => 'api.loe-third-level-messages', 'uses' => 'Api\GetDataApiController@loeThirdLevelMessages' ));
    Route::post('loe-third-level-messages', array('as' => 'api.loe-third-level-messages.create', 'uses' => 'Api\PostDataApiController@loeThirdLevelMessages' ));
    Route::put('loe-third-level-messages/{Id}', array('as' => 'api.loe-third-level-messages.update', 'uses' => 'Api\PutDataApiController@loeThirdLevelMessages' ));
    Route::delete('loe-third-level-messages/{Id}', array('as' => 'api.loe-third-level-messages.delete', 'uses' => 'Api\DeleteDataApiController@loeThirdLevelMessages' ));

    //loss_or_and_expense_claims
    Route::get('loss-or-and-expense-claims', array('as' => 'api.loss-or-and-expense-claims', 'uses' => 'Api\GetDataApiController@lossOrAndExpenseClaims' ));
    Route::post('loss-or-and-expense-claims', array('as' => 'api.loss-or-and-expense-claims.create', 'uses' => 'Api\PostDataApiController@lossOrAndExpenseClaims' ));
    Route::put('loss-or-and-expense-claims/{Id}', array('as' => 'api.loss-or-and-expense-claims.update', 'uses' => 'Api\PutDataApiController@lossOrAndExpenseClaims' ));
    Route::delete('loss-or-and-expense-claims/{Id}', array('as' => 'api.loss-or-and-expense-claims.delete', 'uses' => 'Api\DeleteDataApiController@lossOrAndExpenseClaims' ));

    //login_request_form_settings
    Route::get('login-request-form-settings', array('as' => 'api.login-request-form-settings', 'uses' => 'Api\GetDataApiController@loginRequestFormSettings' ));
    Route::post('login-request-form-settings', array('as' => 'api.login-request-form-settings.create', 'uses' => 'Api\PostDataApiController@loginRequestFormSettings' ));
    Route::put('login-request-form-settings/{Id}', array('as' => 'api.login-request-form-settings.update', 'uses' => 'Api\PutDataApiController@loginRequestFormSettings' ));
    Route::delete('login-request-form-settings/{Id}', array('as' => 'api.login-request-form-settings.delete', 'uses' => 'Api\DeleteDataApiController@loginRequestFormSettings' ));

    //loss_or_and_expense_interim_claims
    Route::get('loss-or-and-expense-interim-claims', array('as' => 'api.loss-or-and-expense-interim-claims', 'uses' => 'Api\GetDataApiController@lossOrAndExpenseInterimClaims' ));
    Route::post('loss-or-and-expense-interim-claims', array('as' => 'api.loss-or-and-expense-interim-claims.create', 'uses' => 'Api\PostDataApiController@lossOrAndExpenseInterimClaims' ));
    Route::put('loss-or-and-expense-interim-claims/{Id}', array('as' => 'api.loss-or-and-expense-interim-claims.update', 'uses' => 'Api\PutDataApiController@lossOrAndExpenseInterimClaims' ));
    Route::delete('loss-or-and-expense-interim-claims/{Id}', array('as' => 'api.loss-or-and-expense-interim-claims.delete', 'uses' => 'Api\DeleteDataApiController@lossOrAndExpenseInterimClaims' ));

    //loe_first_level_messages
    Route::get('loe-first-level-messages', array('as' => 'api.loe-first-level-messages', 'uses' => 'Api\GetDataApiController@loeFirstLevelMessages' ));
    Route::post('loe-first-level-messages', array('as' => 'api.loe-first-level-messages.create', 'uses' => 'Api\PostDataApiController@loeFirstLevelMessages' ));
    Route::put('loe-first-level-messages/{Id}', array('as' => 'api.loe-first-level-messages.update', 'uses' => 'Api\PutDataApiController@loeFirstLevelMessages' ));
    Route::delete('loe-first-level-messages/{Id}', array('as' => 'api.loe-first-level-messages.delete', 'uses' => 'Api\DeleteDataApiController@loeFirstLevelMessages' ));

    //letter_of_awards
    Route::get('letter-of-awards', array('as' => 'api.letter-of-awards', 'uses' => 'Api\GetDataApiController@letterOfAwards' ));
    Route::post('letter-of-awards', array('as' => 'api.letter-of-awards.create', 'uses' => 'Api\PostDataApiController@letterOfAwards' ));
    Route::put('letter-of-awards/{Id}', array('as' => 'api.letter-of-awards.update', 'uses' => 'Api\PutDataApiController@letterOfAwards' ));
    Route::delete('letter-of-awards/{Id}', array('as' => 'api.letter-of-awards.delete', 'uses' => 'Api\DeleteDataApiController@letterOfAwards' ));

    //letter_of_award_user_permissions
    Route::get('letter-of-award-user-permissions', array('as' => 'api.letter-of-award-user-permissions', 'uses' => 'Api\GetDataApiController@letterOfAwardUserPermissions' ));
    Route::post('letter-of-award-user-permissions', array('as' => 'api.letter-of-award-user-permissions.create', 'uses' => 'Api\PostDataApiController@letterOfAwardUserPermissions' ));
    Route::put('letter-of-award-user-permissions/{Id}', array('as' => 'api.letter-of-award-user-permissions.update', 'uses' => 'Api\PutDataApiController@letterOfAwardUserPermissions' ));
    Route::delete('letter-of-award-user-permissions/{Id}', array('as' => 'api.letter-of-award-user-permissions.delete', 'uses' => 'Api\DeleteDataApiController@letterOfAwardUserPermissions' ));

    //loe_contractor_confirm_delays
    Route::get('loe-contractor-confirm-delays', array('as' => 'api.loe-contractor-confirm-delays', 'uses' => 'Api\GetDataApiController@loeContractorConfirmDelays' ));
    Route::post('loe-contractor-confirm-delays', array('as' => 'api.loe-contractor-confirm-delays.create', 'uses' => 'Api\PostDataApiController@loeContractorConfirmDelays' ));
    Route::put('loe-contractor-confirm-delays/{Id}', array('as' => 'api.loe-contractor-confirm-delays.update', 'uses' => 'Api\PutDataApiController@loeContractorConfirmDelays' ));
    Route::delete('loe-contractor-confirm-delays/{Id}', array('as' => 'api.loe-contractor-confirm-delays.delete', 'uses' => 'Api\DeleteDataApiController@loeContractorConfirmDelays' ));

    //machinery
    Route::get('machinery', array('as' => 'api.machinery', 'uses' => 'Api\GetDataApiController@machinery' ));
    Route::post('machinery', array('as' => 'api.machinery.create', 'uses' => 'Api\PostDataApiController@machinery' ));
    Route::put('machinery/{Id}', array('as' => 'api.machinery.update', 'uses' => 'Api\PutDataApiController@machinery' ));
    Route::delete('machinery/{Id}', array('as' => 'api.machinery.delete', 'uses' => 'Api\DeleteDataApiController@machinery' ));

    //e_bidding_bids
    Route::get('e-bidding-bids', array('as' => 'api.e-bidding-bids', 'uses' => 'Api\GetDataApiController@eBiddingBids' ));
    Route::post('e-bidding-bids', array('as' => 'api.e-bidding-bids.create', 'uses' => 'Api\PostDataApiController@eBiddingBids' ));
    Route::put('e-bidding-bids/{Id}', array('as' => 'api.e-bidding-bids.update', 'uses' => 'Api\PutDataApiController@eBiddingBids' ));
    Route::delete('e-bidding-bids/{Id}', array('as' => 'api.e-bidding-bids.delete', 'uses' => 'Api\DeleteDataApiController@eBiddingBids' ));

    //migrations
    Route::get('migrations', array('as' => 'api.migrations', 'uses' => 'Api\GetDataApiController@migrations' ));
    Route::post('migrations', array('as' => 'api.migrations.create', 'uses' => 'Api\PostDataApiController@migrations' ));
    Route::put('migrations/{Id}', array('as' => 'api.migrations.update', 'uses' => 'Api\PutDataApiController@migrations' ));
    Route::delete('migrations/{Id}', array('as' => 'api.migrations.delete', 'uses' => 'Api\DeleteDataApiController@migrations' ));

    //my_company_profiles
    Route::get('my-company-profiles', array('as' => 'api.my-company-profiles', 'uses' => 'Api\GetDataApiController@myCompanyProfiles' ));
    Route::post('my-company-profiles', array('as' => 'api.my-company-profiles.create', 'uses' => 'Api\PostDataApiController@myCompanyProfiles' ));
    Route::put('my-company-profiles/{Id}', array('as' => 'api.my-company-profiles.update', 'uses' => 'Api\PutDataApiController@myCompanyProfiles' ));
    Route::delete('my-company-profiles/{Id}', array('as' => 'api.my-company-profiles.delete', 'uses' => 'Api\DeleteDataApiController@myCompanyProfiles' ));

    //notification_groups
    Route::get('notification-groups', array('as' => 'api.notification-groups', 'uses' => 'Api\GetDataApiController@notificationGroups' ));
    Route::post('notification-groups', array('as' => 'api.notification-groups.create', 'uses' => 'Api\PostDataApiController@notificationGroups' ));
    Route::put('notification-groups/{Id}', array('as' => 'api.notification-groups.update', 'uses' => 'Api\PutDataApiController@notificationGroups' ));
    Route::delete('notification-groups/{Id}', array('as' => 'api.notification-groups.delete', 'uses' => 'Api\DeleteDataApiController@notificationGroups' ));

    //object_forum_threads
    Route::get('object-forum-threads', array('as' => 'api.object-forum-threads', 'uses' => 'Api\GetDataApiController@objectForumThreads' ));
    Route::post('object-forum-threads', array('as' => 'api.object-forum-threads.create', 'uses' => 'Api\PostDataApiController@objectForumThreads' ));
    Route::put('object-forum-threads/{Id}', array('as' => 'api.object-forum-threads.update', 'uses' => 'Api\PutDataApiController@objectForumThreads' ));
    Route::delete('object-forum-threads/{Id}', array('as' => 'api.object-forum-threads.delete', 'uses' => 'Api\DeleteDataApiController@objectForumThreads' ));

    //object_fields
    Route::get('object-fields', array('as' => 'api.object-fields', 'uses' => 'Api\GetDataApiController@objectFields' ));
    Route::post('object-fields', array('as' => 'api.object-fields.create', 'uses' => 'Api\PostDataApiController@objectFields' ));
    Route::put('object-fields/{Id}', array('as' => 'api.object-fields.update', 'uses' => 'Api\PutDataApiController@objectFields' ));
    Route::delete('object-fields/{Id}', array('as' => 'api.object-fields.delete', 'uses' => 'Api\DeleteDataApiController@objectFields' ));

    //mobile_sync_companies
    Route::get('mobile-sync-companies', array('as' => 'api.mobile-sync-companies', 'uses' => 'Api\GetDataApiController@mobileSyncCompanies' ));
    Route::post('mobile-sync-companies', array('as' => 'api.mobile-sync-companies.create', 'uses' => 'Api\PostDataApiController@mobileSyncCompanies' ));
    Route::put('mobile-sync-companies/{Id}', array('as' => 'api.mobile-sync-companies.update', 'uses' => 'Api\PutDataApiController@mobileSyncCompanies' ));
    Route::delete('mobile-sync-companies/{Id}', array('as' => 'api.mobile-sync-companies.delete', 'uses' => 'Api\DeleteDataApiController@mobileSyncCompanies' ));

    //mobile_sync_defect_categories
    Route::get('mobile-sync-defect-categories', array('as' => 'api.mobile-sync-defect-categories', 'uses' => 'Api\GetDataApiController@mobileSyncDefectCategories' ));
    Route::post('mobile-sync-defect-categories', array('as' => 'api.mobile-sync-defect-categories.create', 'uses' => 'Api\PostDataApiController@mobileSyncDefectCategories' ));
    Route::put('mobile-sync-defect-categories/{Id}', array('as' => 'api.mobile-sync-defect-categories.update', 'uses' => 'Api\PutDataApiController@mobileSyncDefectCategories' ));
    Route::delete('mobile-sync-defect-categories/{Id}', array('as' => 'api.mobile-sync-defect-categories.delete', 'uses' => 'Api\DeleteDataApiController@mobileSyncDefectCategories' ));

    //mobile_sync_defect_category_trades
    Route::get('mobile-sync-defect-category-trades', array('as' => 'api.mobile-sync-defect-category-trades', 'uses' => 'Api\GetDataApiController@mobileSyncDefectCategoryTrades' ));
    Route::post('mobile-sync-defect-category-trades', array('as' => 'api.mobile-sync-defect-category-trades.create', 'uses' => 'Api\PostDataApiController@mobileSyncDefectCategoryTrades' ));
    Route::put('mobile-sync-defect-category-trades/{Id}', array('as' => 'api.mobile-sync-defect-category-trades.update', 'uses' => 'Api\PutDataApiController@mobileSyncDefectCategoryTrades' ));
    Route::delete('mobile-sync-defect-category-trades/{Id}', array('as' => 'api.mobile-sync-defect-category-trades.delete', 'uses' => 'Api\DeleteDataApiController@mobileSyncDefectCategoryTrades' ));

    //mobile_sync_defects
    Route::get('mobile-sync-defects', array('as' => 'api.mobile-sync-defects', 'uses' => 'Api\GetDataApiController@mobileSyncDefects' ));
    Route::post('mobile-sync-defects', array('as' => 'api.mobile-sync-defects.create', 'uses' => 'Api\PostDataApiController@mobileSyncDefects' ));
    Route::put('mobile-sync-defects/{Id}', array('as' => 'api.mobile-sync-defects.update', 'uses' => 'Api\PutDataApiController@mobileSyncDefects' ));
    Route::delete('mobile-sync-defects/{Id}', array('as' => 'api.mobile-sync-defects.delete', 'uses' => 'Api\DeleteDataApiController@mobileSyncDefects' ));

    //mobile_sync_project_labour_rate_contractors
    Route::get('mobile-sync-project-labour-rate-contractors', array('as' => 'api.mobile-sync-project-labour-rate-contractors', 'uses' => 'Api\GetDataApiController@mobileSyncProjectLabourRateContractors' ));
    Route::post('mobile-sync-project-labour-rate-contractors', array('as' => 'api.mobile-sync-project-labour-rate-contractors.create', 'uses' => 'Api\PostDataApiController@mobileSyncProjectLabourRateContractors' ));
    Route::put('mobile-sync-project-labour-rate-contractors/{Id}', array('as' => 'api.mobile-sync-project-labour-rate-contractors.update', 'uses' => 'Api\PutDataApiController@mobileSyncProjectLabourRateContractors' ));
    Route::delete('mobile-sync-project-labour-rate-contractors/{Id}', array('as' => 'api.mobile-sync-project-labour-rate-contractors.delete', 'uses' => 'Api\DeleteDataApiController@mobileSyncProjectLabourRateContractors' ));

    //mobile_sync_project_labour_rate_trades
    Route::get('mobile-sync-project-labour-rate-trades', array('as' => 'api.mobile-sync-project-labour-rate-trades', 'uses' => 'Api\GetDataApiController@mobileSyncProjectLabourRateTrades' ));
    Route::post('mobile-sync-project-labour-rate-trades', array('as' => 'api.mobile-sync-project-labour-rate-trades.create', 'uses' => 'Api\PostDataApiController@mobileSyncProjectLabourRateTrades' ));
    Route::put('mobile-sync-project-labour-rate-trades/{Id}', array('as' => 'api.mobile-sync-project-labour-rate-trades.update', 'uses' => 'Api\PutDataApiController@mobileSyncProjectLabourRateTrades' ));
    Route::delete('mobile-sync-project-labour-rate-trades/{Id}', array('as' => 'api.mobile-sync-project-labour-rate-trades.delete', 'uses' => 'Api\DeleteDataApiController@mobileSyncProjectLabourRateTrades' ));

    //mobile_sync_project_labour_rates
    Route::get('mobile-sync-project-labour-rates', array('as' => 'api.mobile-sync-project-labour-rates', 'uses' => 'Api\GetDataApiController@mobileSyncProjectLabourRates' ));
    Route::post('mobile-sync-project-labour-rates', array('as' => 'api.mobile-sync-project-labour-rates.create', 'uses' => 'Api\PostDataApiController@mobileSyncProjectLabourRates' ));
    Route::put('mobile-sync-project-labour-rates/{Id}', array('as' => 'api.mobile-sync-project-labour-rates.update', 'uses' => 'Api\PutDataApiController@mobileSyncProjectLabourRates' ));
    Route::delete('mobile-sync-project-labour-rates/{Id}', array('as' => 'api.mobile-sync-project-labour-rates.delete', 'uses' => 'Api\DeleteDataApiController@mobileSyncProjectLabourRates' ));

    //mobile_sync_project_structure_location_codes
    Route::get('mobile-sync-project-structure-location-codes', array('as' => 'api.mobile-sync-project-structure-location-codes', 'uses' => 'Api\GetDataApiController@mobileSyncProjectStructureLocationCodes' ));
    Route::post('mobile-sync-project-structure-location-codes', array('as' => 'api.mobile-sync-project-structure-location-codes.create', 'uses' => 'Api\PostDataApiController@mobileSyncProjectStructureLocationCodes' ));
    Route::put('mobile-sync-project-structure-location-codes/{Id}', array('as' => 'api.mobile-sync-project-structure-location-codes.update', 'uses' => 'Api\PutDataApiController@mobileSyncProjectStructureLocationCodes' ));
    Route::delete('mobile-sync-project-structure-location-codes/{Id}', array('as' => 'api.mobile-sync-project-structure-location-codes.delete', 'uses' => 'Api\DeleteDataApiController@mobileSyncProjectStructureLocationCodes' ));

    //mobile_sync_projects
    Route::get('mobile-sync-projects', array('as' => 'api.mobile-sync-projects', 'uses' => 'Api\GetDataApiController@mobileSyncProjects' ));
    Route::post('mobile-sync-projects', array('as' => 'api.mobile-sync-projects.create', 'uses' => 'Api\PostDataApiController@mobileSyncProjects' ));
    Route::put('mobile-sync-projects/{Id}', array('as' => 'api.mobile-sync-projects.update', 'uses' => 'Api\PutDataApiController@mobileSyncProjects' ));
    Route::delete('mobile-sync-projects/{Id}', array('as' => 'api.mobile-sync-projects.delete', 'uses' => 'Api\DeleteDataApiController@mobileSyncProjects' ));

    //mobile_sync_site_management_defects
    Route::get('mobile-sync-site-management-defects', array('as' => 'api.mobile-sync-site-management-defects', 'uses' => 'Api\GetDataApiController@mobileSyncSiteManagementDefects' ));
    Route::post('mobile-sync-site-management-defects', array('as' => 'api.mobile-sync-site-management-defects.create', 'uses' => 'Api\PostDataApiController@mobileSyncSiteManagementDefects' ));
    Route::put('mobile-sync-site-management-defects/{Id}', array('as' => 'api.mobile-sync-site-management-defects.update', 'uses' => 'Api\PutDataApiController@mobileSyncSiteManagementDefects' ));
    Route::delete('mobile-sync-site-management-defects/{Id}', array('as' => 'api.mobile-sync-site-management-defects.delete', 'uses' => 'Api\DeleteDataApiController@mobileSyncSiteManagementDefects' ));

    //mobile_sync_trades
    Route::get('mobile-sync-trades', array('as' => 'api.mobile-sync-trades', 'uses' => 'Api\GetDataApiController@mobileSyncTrades' ));
    Route::post('mobile-sync-trades', array('as' => 'api.mobile-sync-trades.create', 'uses' => 'Api\PostDataApiController@mobileSyncTrades' ));
    Route::put('mobile-sync-trades/{Id}', array('as' => 'api.mobile-sync-trades.update', 'uses' => 'Api\PutDataApiController@mobileSyncTrades' ));
    Route::delete('mobile-sync-trades/{Id}', array('as' => 'api.mobile-sync-trades.delete', 'uses' => 'Api\DeleteDataApiController@mobileSyncTrades' ));

    //mobile_sync_uploads
    Route::get('mobile-sync-uploads', array('as' => 'api.mobile-sync-uploads', 'uses' => 'Api\GetDataApiController@mobileSyncUploads' ));
    Route::post('mobile-sync-uploads', array('as' => 'api.mobile-sync-uploads.create', 'uses' => 'Api\PostDataApiController@mobileSyncUploads' ));
    Route::put('mobile-sync-uploads/{Id}', array('as' => 'api.mobile-sync-uploads.update', 'uses' => 'Api\PutDataApiController@mobileSyncUploads' ));
    Route::delete('mobile-sync-uploads/{Id}', array('as' => 'api.mobile-sync-uploads.delete', 'uses' => 'Api\DeleteDataApiController@mobileSyncUploads' ));

    //module_permissions
    Route::get('module-permissions', array('as' => 'api.module-permissions', 'uses' => 'Api\GetDataApiController@modulePermissions' ));
    Route::post('module-permissions', array('as' => 'api.module-permissions.create', 'uses' => 'Api\PostDataApiController@modulePermissions' ));
    Route::put('module-permissions/{Id}', array('as' => 'api.module-permissions.update', 'uses' => 'Api\PutDataApiController@modulePermissions' ));
    Route::delete('module-permissions/{Id}', array('as' => 'api.module-permissions.delete', 'uses' => 'Api\DeleteDataApiController@modulePermissions' ));

    //module_uploaded_files
    Route::get('module-uploaded-files', array('as' => 'api.module-uploaded-files', 'uses' => 'Api\GetDataApiController@moduleUploadedFiles' ));
    Route::post('module-uploaded-files', array('as' => 'api.module-uploaded-files.create', 'uses' => 'Api\PostDataApiController@moduleUploadedFiles' ));
    Route::put('module-uploaded-files/{Id}', array('as' => 'api.module-uploaded-files.update', 'uses' => 'Api\PutDataApiController@moduleUploadedFiles' ));
    Route::delete('module-uploaded-files/{Id}', array('as' => 'api.module-uploaded-files.delete', 'uses' => 'Api\DeleteDataApiController@moduleUploadedFiles' ));

    //notification_categories
    Route::get('notification-categories', array('as' => 'api.notification-categories', 'uses' => 'Api\GetDataApiController@notificationCategories' ));
    Route::post('notification-categories', array('as' => 'api.notification-categories.create', 'uses' => 'Api\PostDataApiController@notificationCategories' ));
    Route::put('notification-categories/{Id}', array('as' => 'api.notification-categories.update', 'uses' => 'Api\PutDataApiController@notificationCategories' ));
    Route::delete('notification-categories/{Id}', array('as' => 'api.notification-categories.delete', 'uses' => 'Api\DeleteDataApiController@notificationCategories' ));

    //notifications
    Route::get('notifications', array('as' => 'api.notifications', 'uses' => 'Api\GetDataApiController@notifications' ));
    Route::post('notifications', array('as' => 'api.notifications.create', 'uses' => 'Api\PostDataApiController@notifications' ));
    Route::put('notifications/{Id}', array('as' => 'api.notifications.update', 'uses' => 'Api\PutDataApiController@notifications' ));
    Route::delete('notifications/{Id}', array('as' => 'api.notifications.delete', 'uses' => 'Api\DeleteDataApiController@notifications' ));

    //object_logs
    Route::get('object-logs', array('as' => 'api.object-logs', 'uses' => 'Api\GetDataApiController@objectLogs' ));
    Route::post('object-logs', array('as' => 'api.object-logs.create', 'uses' => 'Api\PostDataApiController@objectLogs' ));
    Route::put('object-logs/{Id}', array('as' => 'api.object-logs.update', 'uses' => 'Api\PutDataApiController@objectLogs' ));
    Route::delete('object-logs/{Id}', array('as' => 'api.object-logs.delete', 'uses' => 'Api\DeleteDataApiController@objectLogs' ));

    //open_tender_banners
    Route::get('open-tender-banners', array('as' => 'api.open-tender-banners', 'uses' => 'Api\GetDataApiController@openTenderBanners' ));
    Route::post('open-tender-banners', array('as' => 'api.open-tender-banners.create', 'uses' => 'Api\PostDataApiController@openTenderBanners' ));
    Route::put('open-tender-banners/{Id}', array('as' => 'api.open-tender-banners.update', 'uses' => 'Api\PutDataApiController@openTenderBanners' ));
    Route::delete('open-tender-banners/{Id}', array('as' => 'api.open-tender-banners.delete', 'uses' => 'Api\DeleteDataApiController@openTenderBanners' ));

    //open_tender_award_recommendation_tender_analysis_table_edit_log
    Route::get('open-tender-award-recommendation-tender-analysis-table-edit-log', array('as' => 'api.open-tender-award-recommendation-tender-analysis-table-edit-log', 'uses' => 'Api\GetDataApiController@openTenderAwardRecommendationTenderAnalysisTableEditLog' ));
    Route::post('open-tender-award-recommendation-tender-analysis-table-edit-log', array('as' => 'api.open-tender-award-recommendation-tender-analysis-table-edit-log.create', 'uses' => 'Api\PostDataApiController@openTenderAwardRecommendationTenderAnalysisTableEditLog' ));
    Route::put('open-tender-award-recommendation-tender-analysis-table-edit-log/{Id}', array('as' => 'api.open-tender-award-recommendation-tender-analysis-table-edit-log.update', 'uses' => 'Api\PutDataApiController@openTenderAwardRecommendationTenderAnalysisTableEditLog' ));
    Route::delete('open-tender-award-recommendation-tender-analysis-table-edit-log/{Id}', array('as' => 'api.open-tender-award-recommendation-tender-analysis-table-edit-log.delete', 'uses' => 'Api\DeleteDataApiController@openTenderAwardRecommendationTenderAnalysisTableEditLog' ));

    //e_bidding_email_reminder_recipients
    Route::get('e-bidding-email-reminder-recipients', array('as' => 'api.e-bidding-email-reminder-recipients', 'uses' => 'Api\GetDataApiController@eBiddingEmailReminderRecipients' ));
    Route::post('e-bidding-email-reminder-recipients', array('as' => 'api.e-bidding-email-reminder-recipients.create', 'uses' => 'Api\PostDataApiController@eBiddingEmailReminderRecipients' ));
    Route::put('e-bidding-email-reminder-recipients/{Id}', array('as' => 'api.e-bidding-email-reminder-recipients.update', 'uses' => 'Api\PutDataApiController@eBiddingEmailReminderRecipients' ));
    Route::delete('e-bidding-email-reminder-recipients/{Id}', array('as' => 'api.e-bidding-email-reminder-recipients.delete', 'uses' => 'Api\DeleteDataApiController@eBiddingEmailReminderRecipients' ));

    //open_tender_award_recommendation_tender_summary
    Route::get('open-tender-award-recommendation-tender-summary', array('as' => 'api.open-tender-award-recommendation-tender-summary', 'uses' => 'Api\GetDataApiController@openTenderAwardRecommendationTenderSummary' ));
    Route::post('open-tender-award-recommendation-tender-summary', array('as' => 'api.open-tender-award-recommendation-tender-summary.create', 'uses' => 'Api\PostDataApiController@openTenderAwardRecommendationTenderSummary' ));
    Route::put('open-tender-award-recommendation-tender-summary/{Id}', array('as' => 'api.open-tender-award-recommendation-tender-summary.update', 'uses' => 'Api\PutDataApiController@openTenderAwardRecommendationTenderSummary' ));
    Route::delete('open-tender-award-recommendation-tender-summary/{Id}', array('as' => 'api.open-tender-award-recommendation-tender-summary.delete', 'uses' => 'Api\DeleteDataApiController@openTenderAwardRecommendationTenderSummary' ));

    //open_tender_page_information
    Route::get('open-tender-page-information', array('as' => 'api.open-tender-page-information', 'uses' => 'Api\GetDataApiController@openTenderPageInformation' ));
    Route::post('open-tender-page-information', array('as' => 'api.open-tender-page-information.create', 'uses' => 'Api\PostDataApiController@openTenderPageInformation' ));
    Route::put('open-tender-page-information/{Id}', array('as' => 'api.open-tender-page-information.update', 'uses' => 'Api\PutDataApiController@openTenderPageInformation' ));
    Route::delete('open-tender-page-information/{Id}', array('as' => 'api.open-tender-page-information.delete', 'uses' => 'Api\DeleteDataApiController@openTenderPageInformation' ));

    //open_tender_person_in_charges
    Route::get('open-tender-person-in-charges', array('as' => 'api.open-tender-person-in-charges', 'uses' => 'Api\GetDataApiController@openTenderPersonInCharges' ));
    Route::post('open-tender-person-in-charges', array('as' => 'api.open-tender-person-in-charges.create', 'uses' => 'Api\PostDataApiController@openTenderPersonInCharges' ));
    Route::put('open-tender-person-in-charges/{Id}', array('as' => 'api.open-tender-person-in-charges.update', 'uses' => 'Api\PutDataApiController@openTenderPersonInCharges' ));
    Route::delete('open-tender-person-in-charges/{Id}', array('as' => 'api.open-tender-person-in-charges.delete', 'uses' => 'Api\DeleteDataApiController@openTenderPersonInCharges' ));

    //open_tender_tender_documents
    Route::get('open-tender-tender-documents', array('as' => 'api.open-tender-tender-documents', 'uses' => 'Api\GetDataApiController@openTenderTenderDocuments' ));
    Route::post('open-tender-tender-documents', array('as' => 'api.open-tender-tender-documents.create', 'uses' => 'Api\PostDataApiController@openTenderTenderDocuments' ));
    Route::put('open-tender-tender-documents/{Id}', array('as' => 'api.open-tender-tender-documents.update', 'uses' => 'Api\PutDataApiController@openTenderTenderDocuments' ));
    Route::delete('open-tender-tender-documents/{Id}', array('as' => 'api.open-tender-tender-documents.delete', 'uses' => 'Api\DeleteDataApiController@openTenderTenderDocuments' ));

    //open_tender_award_recommendation_bill_details
    Route::get('open-tender-award-recommendation-bill-details', array('as' => 'api.open-tender-award-recommendation-bill-details', 'uses' => 'Api\GetDataApiController@openTenderAwardRecommendationBillDetails' ));
    Route::post('open-tender-award-recommendation-bill-details', array('as' => 'api.open-tender-award-recommendation-bill-details.create', 'uses' => 'Api\PostDataApiController@openTenderAwardRecommendationBillDetails' ));
    Route::put('open-tender-award-recommendation-bill-details/{Id}', array('as' => 'api.open-tender-award-recommendation-bill-details.update', 'uses' => 'Api\PutDataApiController@openTenderAwardRecommendationBillDetails' ));
    Route::delete('open-tender-award-recommendation-bill-details/{Id}', array('as' => 'api.open-tender-award-recommendation-bill-details.delete', 'uses' => 'Api\DeleteDataApiController@openTenderAwardRecommendationBillDetails' ));

    //object_tags
    Route::get('object-tags', array('as' => 'api.object-tags', 'uses' => 'Api\GetDataApiController@objectTags' ));
    Route::post('object-tags', array('as' => 'api.object-tags.create', 'uses' => 'Api\PostDataApiController@objectTags' ));
    Route::put('object-tags/{Id}', array('as' => 'api.object-tags.update', 'uses' => 'Api\PutDataApiController@objectTags' ));
    Route::delete('object-tags/{Id}', array('as' => 'api.object-tags.delete', 'uses' => 'Api\DeleteDataApiController@objectTags' ));

    //open_tender_announcements
    Route::get('open-tender-announcements', array('as' => 'api.open-tender-announcements', 'uses' => 'Api\GetDataApiController@openTenderAnnouncements' ));
    Route::post('open-tender-announcements', array('as' => 'api.open-tender-announcements.create', 'uses' => 'Api\PostDataApiController@openTenderAnnouncements' ));
    Route::put('open-tender-announcements/{Id}', array('as' => 'api.open-tender-announcements.update', 'uses' => 'Api\PutDataApiController@openTenderAnnouncements' ));
    Route::delete('open-tender-announcements/{Id}', array('as' => 'api.open-tender-announcements.delete', 'uses' => 'Api\DeleteDataApiController@openTenderAnnouncements' ));

    //open_tender_industry_codes
    Route::get('open-tender-industry-codes', array('as' => 'api.open-tender-industry-codes', 'uses' => 'Api\GetDataApiController@openTenderIndustryCodes' ));
    Route::post('open-tender-industry-codes', array('as' => 'api.open-tender-industry-codes.create', 'uses' => 'Api\PostDataApiController@openTenderIndustryCodes' ));
    Route::put('open-tender-industry-codes/{Id}', array('as' => 'api.open-tender-industry-codes.update', 'uses' => 'Api\PutDataApiController@openTenderIndustryCodes' ));
    Route::delete('open-tender-industry-codes/{Id}', array('as' => 'api.open-tender-industry-codes.delete', 'uses' => 'Api\DeleteDataApiController@openTenderIndustryCodes' ));

    //open_tender_news
    Route::get('open-tender-news', array('as' => 'api.open-tender-news', 'uses' => 'Api\GetDataApiController@openTenderNews' ));
    Route::post('open-tender-news', array('as' => 'api.open-tender-news.create', 'uses' => 'Api\PostDataApiController@openTenderNews' ));
    Route::put('open-tender-news/{Id}', array('as' => 'api.open-tender-news.update', 'uses' => 'Api\PutDataApiController@openTenderNews' ));
    Route::delete('open-tender-news/{Id}', array('as' => 'api.open-tender-news.delete', 'uses' => 'Api\DeleteDataApiController@openTenderNews' ));

    //open_tender_award_recommendation_files
    Route::get('open-tender-award-recommendation-files', array('as' => 'api.open-tender-award-recommendation-files', 'uses' => 'Api\GetDataApiController@openTenderAwardRecommendationFiles' ));
    Route::post('open-tender-award-recommendation-files', array('as' => 'api.open-tender-award-recommendation-files.create', 'uses' => 'Api\PostDataApiController@openTenderAwardRecommendationFiles' ));
    Route::put('open-tender-award-recommendation-files/{Id}', array('as' => 'api.open-tender-award-recommendation-files.update', 'uses' => 'Api\PutDataApiController@openTenderAwardRecommendationFiles' ));
    Route::delete('open-tender-award-recommendation-files/{Id}', array('as' => 'api.open-tender-award-recommendation-files.delete', 'uses' => 'Api\DeleteDataApiController@openTenderAwardRecommendationFiles' ));

    //open_tender_award_recommendation
    Route::get('open-tender-award-recommendation', array('as' => 'api.open-tender-award-recommendation', 'uses' => 'Api\GetDataApiController@openTenderAwardRecommendation' ));
    Route::post('open-tender-award-recommendation', array('as' => 'api.open-tender-award-recommendation.create', 'uses' => 'Api\PostDataApiController@openTenderAwardRecommendation' ));
    Route::put('open-tender-award-recommendation/{Id}', array('as' => 'api.open-tender-award-recommendation.update', 'uses' => 'Api\PutDataApiController@openTenderAwardRecommendation' ));
    Route::delete('open-tender-award-recommendation/{Id}', array('as' => 'api.open-tender-award-recommendation.delete', 'uses' => 'Api\DeleteDataApiController@openTenderAwardRecommendation' ));

    //open_tender_verifier_logs
    Route::get('open-tender-verifier-logs', array('as' => 'api.open-tender-verifier-logs', 'uses' => 'Api\GetDataApiController@openTenderVerifierLogs' ));
    Route::post('open-tender-verifier-logs', array('as' => 'api.open-tender-verifier-logs.create', 'uses' => 'Api\PostDataApiController@openTenderVerifierLogs' ));
    Route::put('open-tender-verifier-logs/{Id}', array('as' => 'api.open-tender-verifier-logs.update', 'uses' => 'Api\PutDataApiController@openTenderVerifierLogs' ));
    Route::delete('open-tender-verifier-logs/{Id}', array('as' => 'api.open-tender-verifier-logs.delete', 'uses' => 'Api\DeleteDataApiController@openTenderVerifierLogs' ));

    //order_item_project_tenders
    Route::get('order-item-project-tenders', array('as' => 'api.order-item-project-tenders', 'uses' => 'Api\GetDataApiController@orderItemProjectTenders' ));
    Route::post('order-item-project-tenders', array('as' => 'api.order-item-project-tenders.create', 'uses' => 'Api\PostDataApiController@orderItemProjectTenders' ));
    Route::put('order-item-project-tenders/{Id}', array('as' => 'api.order-item-project-tenders.update', 'uses' => 'Api\PutDataApiController@orderItemProjectTenders' ));
    Route::delete('order-item-project-tenders/{Id}', array('as' => 'api.order-item-project-tenders.delete', 'uses' => 'Api\DeleteDataApiController@orderItemProjectTenders' ));

    //order_item_vendor_reg_payments
    Route::get('order-item-vendor-reg-payments', array('as' => 'api.order-item-vendor-reg-payments', 'uses' => 'Api\GetDataApiController@orderItemVendorRegPayments' ));
    Route::post('order-item-vendor-reg-payments', array('as' => 'api.order-item-vendor-reg-payments.create', 'uses' => 'Api\PostDataApiController@orderItemVendorRegPayments' ));
    Route::put('order-item-vendor-reg-payments/{Id}', array('as' => 'api.order-item-vendor-reg-payments.update', 'uses' => 'Api\PutDataApiController@orderItemVendorRegPayments' ));
    Route::delete('order-item-vendor-reg-payments/{Id}', array('as' => 'api.order-item-vendor-reg-payments.delete', 'uses' => 'Api\DeleteDataApiController@orderItemVendorRegPayments' ));

    //order_items
    Route::get('order-items', array('as' => 'api.order-items', 'uses' => 'Api\GetDataApiController@orderItems' ));
    Route::post('order-items', array('as' => 'api.order-items.create', 'uses' => 'Api\PostDataApiController@orderItems' ));
    Route::put('order-items/{Id}', array('as' => 'api.order-items.update', 'uses' => 'Api\PutDataApiController@orderItems' ));
    Route::delete('order-items/{Id}', array('as' => 'api.order-items.delete', 'uses' => 'Api\DeleteDataApiController@orderItems' ));

    //order_payments
    Route::get('order-payments', array('as' => 'api.order-payments', 'uses' => 'Api\GetDataApiController@orderPayments' ));
    Route::post('order-payments', array('as' => 'api.order-payments.create', 'uses' => 'Api\PostDataApiController@orderPayments' ));
    Route::put('order-payments/{Id}', array('as' => 'api.order-payments.update', 'uses' => 'Api\PutDataApiController@orderPayments' ));
    Route::delete('order-payments/{Id}', array('as' => 'api.order-payments.delete', 'uses' => 'Api\DeleteDataApiController@orderPayments' ));

    //order_subs
    Route::get('order-subs', array('as' => 'api.order-subs', 'uses' => 'Api\GetDataApiController@orderSubs' ));
    Route::post('order-subs', array('as' => 'api.order-subs.create', 'uses' => 'Api\PostDataApiController@orderSubs' ));
    Route::put('order-subs/{Id}', array('as' => 'api.order-subs.update', 'uses' => 'Api\PutDataApiController@orderSubs' ));
    Route::delete('order-subs/{Id}', array('as' => 'api.order-subs.delete', 'uses' => 'Api\DeleteDataApiController@orderSubs' ));

    //orders
    Route::get('orders', array('as' => 'api.orders', 'uses' => 'Api\GetDataApiController@orders' ));
    Route::post('orders', array('as' => 'api.orders.create', 'uses' => 'Api\PostDataApiController@orders' ));
    Route::put('orders/{Id}', array('as' => 'api.orders.update', 'uses' => 'Api\PutDataApiController@orders' ));
    Route::delete('orders/{Id}', array('as' => 'api.orders.delete', 'uses' => 'Api\DeleteDataApiController@orders' ));

    //password_reminders
    Route::get('password-reminders', array('as' => 'api.password-reminders', 'uses' => 'Api\GetDataApiController@passwordReminders' ));
    Route::post('password-reminders', array('as' => 'api.password-reminders.create', 'uses' => 'Api\PostDataApiController@passwordReminders' ));
    Route::put('password-reminders/{Id}', array('as' => 'api.password-reminders.update', 'uses' => 'Api\PutDataApiController@passwordReminders' ));
    Route::delete('password-reminders/{Id}', array('as' => 'api.password-reminders.delete', 'uses' => 'Api\DeleteDataApiController@passwordReminders' ));

    //payment_gateway_results
    Route::get('payment-gateway-results', array('as' => 'api.payment-gateway-results', 'uses' => 'Api\GetDataApiController@paymentGatewayResults' ));
    Route::post('payment-gateway-results', array('as' => 'api.payment-gateway-results.create', 'uses' => 'Api\PostDataApiController@paymentGatewayResults' ));
    Route::put('payment-gateway-results/{Id}', array('as' => 'api.payment-gateway-results.update', 'uses' => 'Api\PutDataApiController@paymentGatewayResults' ));
    Route::delete('payment-gateway-results/{Id}', array('as' => 'api.payment-gateway-results.delete', 'uses' => 'Api\DeleteDataApiController@paymentGatewayResults' ));

    //payment_gateway_settings
    Route::get('payment-gateway-settings', array('as' => 'api.payment-gateway-settings', 'uses' => 'Api\GetDataApiController@paymentGatewaySettings' ));
    Route::post('payment-gateway-settings', array('as' => 'api.payment-gateway-settings.create', 'uses' => 'Api\PostDataApiController@paymentGatewaySettings' ));
    Route::put('payment-gateway-settings/{Id}', array('as' => 'api.payment-gateway-settings.update', 'uses' => 'Api\PutDataApiController@paymentGatewaySettings' ));
    Route::delete('payment-gateway-settings/{Id}', array('as' => 'api.payment-gateway-settings.delete', 'uses' => 'Api\DeleteDataApiController@paymentGatewaySettings' ));

    //processor_delete_company_logs
    Route::get('processor-delete-company-logs', array('as' => 'api.processor-delete-company-logs', 'uses' => 'Api\GetDataApiController@processorDeleteCompanyLogs' ));
    Route::post('processor-delete-company-logs', array('as' => 'api.processor-delete-company-logs.create', 'uses' => 'Api\PostDataApiController@processorDeleteCompanyLogs' ));
    Route::put('processor-delete-company-logs/{Id}', array('as' => 'api.processor-delete-company-logs.update', 'uses' => 'Api\PutDataApiController@processorDeleteCompanyLogs' ));
    Route::delete('processor-delete-company-logs/{Id}', array('as' => 'api.processor-delete-company-logs.delete', 'uses' => 'Api\DeleteDataApiController@processorDeleteCompanyLogs' ));

    //procurement_methods
    Route::get('procurement-methods', array('as' => 'api.procurement-methods', 'uses' => 'Api\GetDataApiController@procurementMethods' ));
    Route::post('procurement-methods', array('as' => 'api.procurement-methods.create', 'uses' => 'Api\PostDataApiController@procurementMethods' ));
    Route::put('procurement-methods/{Id}', array('as' => 'api.procurement-methods.update', 'uses' => 'Api\PutDataApiController@procurementMethods' ));
    Route::delete('procurement-methods/{Id}', array('as' => 'api.procurement-methods.delete', 'uses' => 'Api\DeleteDataApiController@procurementMethods' ));

    //open_tender_tender_requirements
    Route::get('open-tender-tender-requirements', array('as' => 'api.open-tender-tender-requirements', 'uses' => 'Api\GetDataApiController@openTenderTenderRequirements' ));
    Route::post('open-tender-tender-requirements', array('as' => 'api.open-tender-tender-requirements.create', 'uses' => 'Api\PostDataApiController@openTenderTenderRequirements' ));
    Route::put('open-tender-tender-requirements/{Id}', array('as' => 'api.open-tender-tender-requirements.update', 'uses' => 'Api\PutDataApiController@openTenderTenderRequirements' ));
    Route::delete('open-tender-tender-requirements/{Id}', array('as' => 'api.open-tender-tender-requirements.delete', 'uses' => 'Api\DeleteDataApiController@openTenderTenderRequirements' ));

    //pam_2006_project_details
    Route::get('pam-2006-project-details', array('as' => 'api.pam-2006-project-details', 'uses' => 'Api\GetDataApiController@pam2006ProjectDetails' ));
    Route::post('pam-2006-project-details', array('as' => 'api.pam-2006-project-details.create', 'uses' => 'Api\PostDataApiController@pam2006ProjectDetails' ));
    Route::put('pam-2006-project-details/{Id}', array('as' => 'api.pam-2006-project-details.update', 'uses' => 'Api\PutDataApiController@pam2006ProjectDetails' ));
    Route::delete('pam-2006-project-details/{Id}', array('as' => 'api.pam-2006-project-details.delete', 'uses' => 'Api\DeleteDataApiController@pam2006ProjectDetails' ));

    //payment_settings
    Route::get('payment-settings', array('as' => 'api.payment-settings', 'uses' => 'Api\GetDataApiController@paymentSettings' ));
    Route::post('payment-settings', array('as' => 'api.payment-settings.create', 'uses' => 'Api\PostDataApiController@paymentSettings' ));
    Route::put('payment-settings/{Id}', array('as' => 'api.payment-settings.update', 'uses' => 'Api\PutDataApiController@paymentSettings' ));
    Route::delete('payment-settings/{Id}', array('as' => 'api.payment-settings.delete', 'uses' => 'Api\DeleteDataApiController@paymentSettings' ));

    //project_module_permissions
    Route::get('project-module-permissions', array('as' => 'api.project-module-permissions', 'uses' => 'Api\GetDataApiController@projectModulePermissions' ));
    Route::post('project-module-permissions', array('as' => 'api.project-module-permissions.create', 'uses' => 'Api\PostDataApiController@projectModulePermissions' ));
    Route::put('project-module-permissions/{Id}', array('as' => 'api.project-module-permissions.update', 'uses' => 'Api\PutDataApiController@projectModulePermissions' ));
    Route::delete('project-module-permissions/{Id}', array('as' => 'api.project-module-permissions.delete', 'uses' => 'Api\DeleteDataApiController@projectModulePermissions' ));

    //project_report_chart_plots
    Route::get('project-report-chart-plots', array('as' => 'api.project-report-chart-plots', 'uses' => 'Api\GetDataApiController@projectReportChartPlots' ));
    Route::post('project-report-chart-plots', array('as' => 'api.project-report-chart-plots.create', 'uses' => 'Api\PostDataApiController@projectReportChartPlots' ));
    Route::put('project-report-chart-plots/{Id}', array('as' => 'api.project-report-chart-plots.update', 'uses' => 'Api\PutDataApiController@projectReportChartPlots' ));
    Route::delete('project-report-chart-plots/{Id}', array('as' => 'api.project-report-chart-plots.delete', 'uses' => 'Api\DeleteDataApiController@projectReportChartPlots' ));

    //project_report_charts
    Route::get('project-report-charts', array('as' => 'api.project-report-charts', 'uses' => 'Api\GetDataApiController@projectReportCharts' ));
    Route::post('project-report-charts', array('as' => 'api.project-report-charts.create', 'uses' => 'Api\PostDataApiController@projectReportCharts' ));
    Route::put('project-report-charts/{Id}', array('as' => 'api.project-report-charts.update', 'uses' => 'Api\PutDataApiController@projectReportCharts' ));
    Route::delete('project-report-charts/{Id}', array('as' => 'api.project-report-charts.delete', 'uses' => 'Api\DeleteDataApiController@projectReportCharts' ));

    //project_report_type_mappings
    Route::get('project-report-type-mappings', array('as' => 'api.project-report-type-mappings', 'uses' => 'Api\GetDataApiController@projectReportTypeMappings' ));
    Route::post('project-report-type-mappings', array('as' => 'api.project-report-type-mappings.create', 'uses' => 'Api\PostDataApiController@projectReportTypeMappings' ));
    Route::put('project-report-type-mappings/{Id}', array('as' => 'api.project-report-type-mappings.update', 'uses' => 'Api\PutDataApiController@projectReportTypeMappings' ));
    Route::delete('project-report-type-mappings/{Id}', array('as' => 'api.project-report-type-mappings.delete', 'uses' => 'Api\DeleteDataApiController@projectReportTypeMappings' ));

    //project_report_notification_contents
    Route::get('project-report-notification-contents', array('as' => 'api.project-report-notification-contents', 'uses' => 'Api\GetDataApiController@projectReportNotificationContents' ));
    Route::post('project-report-notification-contents', array('as' => 'api.project-report-notification-contents.create', 'uses' => 'Api\PostDataApiController@projectReportNotificationContents' ));
    Route::put('project-report-notification-contents/{Id}', array('as' => 'api.project-report-notification-contents.update', 'uses' => 'Api\PutDataApiController@projectReportNotificationContents' ));
    Route::delete('project-report-notification-contents/{Id}', array('as' => 'api.project-report-notification-contents.delete', 'uses' => 'Api\DeleteDataApiController@projectReportNotificationContents' ));

    //project_report_notification_periods
    Route::get('project-report-notification-periods', array('as' => 'api.project-report-notification-periods', 'uses' => 'Api\GetDataApiController@projectReportNotificationPeriods' ));
    Route::post('project-report-notification-periods', array('as' => 'api.project-report-notification-periods.create', 'uses' => 'Api\PostDataApiController@projectReportNotificationPeriods' ));
    Route::put('project-report-notification-periods/{Id}', array('as' => 'api.project-report-notification-periods.update', 'uses' => 'Api\PutDataApiController@projectReportNotificationPeriods' ));
    Route::delete('project-report-notification-periods/{Id}', array('as' => 'api.project-report-notification-periods.delete', 'uses' => 'Api\DeleteDataApiController@projectReportNotificationPeriods' ));

    //project_report_notification_recipients
    Route::get('project-report-notification-recipients', array('as' => 'api.project-report-notification-recipients', 'uses' => 'Api\GetDataApiController@projectReportNotificationRecipients' ));
    Route::post('project-report-notification-recipients', array('as' => 'api.project-report-notification-recipients.create', 'uses' => 'Api\PostDataApiController@projectReportNotificationRecipients' ));
    Route::put('project-report-notification-recipients/{Id}', array('as' => 'api.project-report-notification-recipients.update', 'uses' => 'Api\PutDataApiController@projectReportNotificationRecipients' ));
    Route::delete('project-report-notification-recipients/{Id}', array('as' => 'api.project-report-notification-recipients.delete', 'uses' => 'Api\DeleteDataApiController@projectReportNotificationRecipients' ));

    //project_report_notifications
    Route::get('project-report-notifications', array('as' => 'api.project-report-notifications', 'uses' => 'Api\GetDataApiController@projectReportNotifications' ));
    Route::post('project-report-notifications', array('as' => 'api.project-report-notifications.create', 'uses' => 'Api\PostDataApiController@projectReportNotifications' ));
    Route::put('project-report-notifications/{Id}', array('as' => 'api.project-report-notifications.update', 'uses' => 'Api\PutDataApiController@projectReportNotifications' ));
    Route::delete('project-report-notifications/{Id}', array('as' => 'api.project-report-notifications.delete', 'uses' => 'Api\DeleteDataApiController@projectReportNotifications' ));

    //project_report_columns
    Route::get('project-report-columns', array('as' => 'api.project-report-columns', 'uses' => 'Api\GetDataApiController@projectReportColumns' ));
    Route::post('project-report-columns', array('as' => 'api.project-report-columns.create', 'uses' => 'Api\PostDataApiController@projectReportColumns' ));
    Route::put('project-report-columns/{Id}', array('as' => 'api.project-report-columns.update', 'uses' => 'Api\PutDataApiController@projectReportColumns' ));
    Route::delete('project-report-columns/{Id}', array('as' => 'api.project-report-columns.delete', 'uses' => 'Api\DeleteDataApiController@projectReportColumns' ));

    //project_labour_rates
    Route::get('project-labour-rates', array('as' => 'api.project-labour-rates', 'uses' => 'Api\GetDataApiController@projectLabourRates' ));
    Route::post('project-labour-rates', array('as' => 'api.project-labour-rates.create', 'uses' => 'Api\PostDataApiController@projectLabourRates' ));
    Route::put('project-labour-rates/{Id}', array('as' => 'api.project-labour-rates.update', 'uses' => 'Api\PutDataApiController@projectLabourRates' ));
    Route::delete('project-labour-rates/{Id}', array('as' => 'api.project-labour-rates.delete', 'uses' => 'Api\DeleteDataApiController@projectLabourRates' ));

    //project_contract_group_tender_document_permissions
    Route::get('project-contract-group-tender-document-permissions', array('as' => 'api.project-contract-group-tender-document-permissions', 'uses' => 'Api\GetDataApiController@projectContractGroupTenderDocumentPermissions' ));
    Route::post('project-contract-group-tender-document-permissions', array('as' => 'api.project-contract-group-tender-document-permissions.create', 'uses' => 'Api\PostDataApiController@projectContractGroupTenderDocumentPermissions' ));
    Route::put('project-contract-group-tender-document-permissions/{Id}', array('as' => 'api.project-contract-group-tender-document-permissions.update', 'uses' => 'Api\PutDataApiController@projectContractGroupTenderDocumentPermissions' ));
    Route::delete('project-contract-group-tender-document-permissions/{Id}', array('as' => 'api.project-contract-group-tender-document-permissions.delete', 'uses' => 'Api\DeleteDataApiController@projectContractGroupTenderDocumentPermissions' ));

    //project_contract_management_modules
    Route::get('project-contract-management-modules', array('as' => 'api.project-contract-management-modules', 'uses' => 'Api\GetDataApiController@projectContractManagementModules' ));
    Route::post('project-contract-management-modules', array('as' => 'api.project-contract-management-modules.create', 'uses' => 'Api\PostDataApiController@projectContractManagementModules' ));
    Route::put('project-contract-management-modules/{Id}', array('as' => 'api.project-contract-management-modules.update', 'uses' => 'Api\PutDataApiController@projectContractManagementModules' ));
    Route::delete('project-contract-management-modules/{Id}', array('as' => 'api.project-contract-management-modules.delete', 'uses' => 'Api\DeleteDataApiController@projectContractManagementModules' ));

    //project_document_files
    Route::get('project-document-files', array('as' => 'api.project-document-files', 'uses' => 'Api\GetDataApiController@projectDocumentFiles' ));
    Route::post('project-document-files', array('as' => 'api.project-document-files.create', 'uses' => 'Api\PostDataApiController@projectDocumentFiles' ));
    Route::put('project-document-files/{Id}', array('as' => 'api.project-document-files.update', 'uses' => 'Api\PutDataApiController@projectDocumentFiles' ));
    Route::delete('project-document-files/{Id}', array('as' => 'api.project-document-files.delete', 'uses' => 'Api\DeleteDataApiController@projectDocumentFiles' ));

    //project_report_action_logs
    Route::get('project-report-action-logs', array('as' => 'api.project-report-action-logs', 'uses' => 'Api\GetDataApiController@projectReportActionLogs' ));
    Route::post('project-report-action-logs', array('as' => 'api.project-report-action-logs.create', 'uses' => 'Api\PostDataApiController@projectReportActionLogs' ));
    Route::put('project-report-action-logs/{Id}', array('as' => 'api.project-report-action-logs.update', 'uses' => 'Api\PutDataApiController@projectReportActionLogs' ));
    Route::delete('project-report-action-logs/{Id}', array('as' => 'api.project-report-action-logs.delete', 'uses' => 'Api\DeleteDataApiController@projectReportActionLogs' ));

    //project_reports
    Route::get('project-reports', array('as' => 'api.project-reports', 'uses' => 'Api\GetDataApiController@projectReports' ));
    Route::post('project-reports', array('as' => 'api.project-reports.create', 'uses' => 'Api\PostDataApiController@projectReports' ));
    Route::put('project-reports/{Id}', array('as' => 'api.project-reports.update', 'uses' => 'Api\PutDataApiController@projectReports' ));
    Route::delete('project-reports/{Id}', array('as' => 'api.project-reports.delete', 'uses' => 'Api\DeleteDataApiController@projectReports' ));

    //project_report_types
    Route::get('project-report-types', array('as' => 'api.project-report-types', 'uses' => 'Api\GetDataApiController@projectReportTypes' ));
    Route::post('project-report-types', array('as' => 'api.project-report-types.create', 'uses' => 'Api\PostDataApiController@projectReportTypes' ));
    Route::put('project-report-types/{Id}', array('as' => 'api.project-report-types.update', 'uses' => 'Api\PutDataApiController@projectReportTypes' ));
    Route::delete('project-report-types/{Id}', array('as' => 'api.project-report-types.delete', 'uses' => 'Api\DeleteDataApiController@projectReportTypes' ));

    //project_report_user_permissions
    Route::get('project-report-user-permissions', array('as' => 'api.project-report-user-permissions', 'uses' => 'Api\GetDataApiController@projectReportUserPermissions' ));
    Route::post('project-report-user-permissions', array('as' => 'api.project-report-user-permissions.create', 'uses' => 'Api\PostDataApiController@projectReportUserPermissions' ));
    Route::put('project-report-user-permissions/{Id}', array('as' => 'api.project-report-user-permissions.update', 'uses' => 'Api\PutDataApiController@projectReportUserPermissions' ));
    Route::delete('project-report-user-permissions/{Id}', array('as' => 'api.project-report-user-permissions.delete', 'uses' => 'Api\DeleteDataApiController@projectReportUserPermissions' ));

    //project_roles
    Route::get('project-roles', array('as' => 'api.project-roles', 'uses' => 'Api\GetDataApiController@projectRoles' ));
    Route::post('project-roles', array('as' => 'api.project-roles.create', 'uses' => 'Api\PostDataApiController@projectRoles' ));
    Route::put('project-roles/{Id}', array('as' => 'api.project-roles.update', 'uses' => 'Api\PutDataApiController@projectRoles' ));
    Route::delete('project-roles/{Id}', array('as' => 'api.project-roles.delete', 'uses' => 'Api\DeleteDataApiController@projectRoles' ));

    //request_for_information_messages
    Route::get('request-for-information-messages', array('as' => 'api.request-for-information-messages', 'uses' => 'Api\GetDataApiController@requestForInformationMessages' ));
    Route::post('request-for-information-messages', array('as' => 'api.request-for-information-messages.create', 'uses' => 'Api\PostDataApiController@requestForInformationMessages' ));
    Route::put('request-for-information-messages/{Id}', array('as' => 'api.request-for-information-messages.update', 'uses' => 'Api\PutDataApiController@requestForInformationMessages' ));
    Route::delete('request-for-information-messages/{Id}', array('as' => 'api.request-for-information-messages.delete', 'uses' => 'Api\DeleteDataApiController@requestForInformationMessages' ));

    //project_track_record_settings
    Route::get('project-track-record-settings', array('as' => 'api.project-track-record-settings', 'uses' => 'Api\GetDataApiController@projectTrackRecordSettings' ));
    Route::post('project-track-record-settings', array('as' => 'api.project-track-record-settings.create', 'uses' => 'Api\PostDataApiController@projectTrackRecordSettings' ));
    Route::put('project-track-record-settings/{Id}', array('as' => 'api.project-track-record-settings.update', 'uses' => 'Api\PutDataApiController@projectTrackRecordSettings' ));
    Route::delete('project-track-record-settings/{Id}', array('as' => 'api.project-track-record-settings.delete', 'uses' => 'Api\DeleteDataApiController@projectTrackRecordSettings' ));

    //property_developers
    Route::get('property-developers', array('as' => 'api.property-developers', 'uses' => 'Api\GetDataApiController@propertyDevelopers' ));
    Route::post('property-developers', array('as' => 'api.property-developers.create', 'uses' => 'Api\PostDataApiController@propertyDevelopers' ));
    Route::put('property-developers/{Id}', array('as' => 'api.property-developers.update', 'uses' => 'Api\PutDataApiController@propertyDevelopers' ));
    Route::delete('property-developers/{Id}', array('as' => 'api.property-developers.delete', 'uses' => 'Api\DeleteDataApiController@propertyDevelopers' ));

    //purged_vendors
    Route::get('purged-vendors', array('as' => 'api.purged-vendors', 'uses' => 'Api\GetDataApiController@purgedVendors' ));
    Route::post('purged-vendors', array('as' => 'api.purged-vendors.create', 'uses' => 'Api\PostDataApiController@purgedVendors' ));
    Route::put('purged-vendors/{Id}', array('as' => 'api.purged-vendors.update', 'uses' => 'Api\PutDataApiController@purgedVendors' ));
    Route::delete('purged-vendors/{Id}', array('as' => 'api.purged-vendors.delete', 'uses' => 'Api\DeleteDataApiController@purgedVendors' ));

    //request_for_inspection_replies
    Route::get('request-for-inspection-replies', array('as' => 'api.request-for-inspection-replies', 'uses' => 'Api\GetDataApiController@requestForInspectionReplies' ));
    Route::post('request-for-inspection-replies', array('as' => 'api.request-for-inspection-replies.create', 'uses' => 'Api\PostDataApiController@requestForInspectionReplies' ));
    Route::put('request-for-inspection-replies/{Id}', array('as' => 'api.request-for-inspection-replies.update', 'uses' => 'Api\PutDataApiController@requestForInspectionReplies' ));
    Route::delete('request-for-inspection-replies/{Id}', array('as' => 'api.request-for-inspection-replies.delete', 'uses' => 'Api\DeleteDataApiController@requestForInspectionReplies' ));

    //request_for_variation_category_kpi_limit_update_logs
    Route::get('request-for-variation-category-kpi-limit-update-logs', array('as' => 'api.request-for-variation-category-kpi-limit-update-logs', 'uses' => 'Api\GetDataApiController@requestForVariationCategoryKpiLimitUpdateLogs' ));
    Route::post('request-for-variation-category-kpi-limit-update-logs', array('as' => 'api.request-for-variation-category-kpi-limit-update-logs.create', 'uses' => 'Api\PostDataApiController@requestForVariationCategoryKpiLimitUpdateLogs' ));
    Route::put('request-for-variation-category-kpi-limit-update-logs/{Id}', array('as' => 'api.request-for-variation-category-kpi-limit-update-logs.update', 'uses' => 'Api\PutDataApiController@requestForVariationCategoryKpiLimitUpdateLogs' ));
    Route::delete('request-for-variation-category-kpi-limit-update-logs/{Id}', array('as' => 'api.request-for-variation-category-kpi-limit-update-logs.delete', 'uses' => 'Api\DeleteDataApiController@requestForVariationCategoryKpiLimitUpdateLogs' ));

    //request_for_variation_contract_and_contingency_sum
    Route::get('request-for-variation-contract-and-contingency-sum', array('as' => 'api.request-for-variation-contract-and-contingency-sum', 'uses' => 'Api\GetDataApiController@requestForVariationContractAndContingencySum' ));
    Route::post('request-for-variation-contract-and-contingency-sum', array('as' => 'api.request-for-variation-contract-and-contingency-sum.create', 'uses' => 'Api\PostDataApiController@requestForVariationContractAndContingencySum' ));
    Route::put('request-for-variation-contract-and-contingency-sum/{Id}', array('as' => 'api.request-for-variation-contract-and-contingency-sum.update', 'uses' => 'Api\PutDataApiController@requestForVariationContractAndContingencySum' ));
    Route::delete('request-for-variation-contract-and-contingency-sum/{Id}', array('as' => 'api.request-for-variation-contract-and-contingency-sum.delete', 'uses' => 'Api\DeleteDataApiController@requestForVariationContractAndContingencySum' ));

    //request_for_variation_categories
    Route::get('request-for-variation-categories', array('as' => 'api.request-for-variation-categories', 'uses' => 'Api\GetDataApiController@requestForVariationCategories' ));
    Route::post('request-for-variation-categories', array('as' => 'api.request-for-variation-categories.create', 'uses' => 'Api\PostDataApiController@requestForVariationCategories' ));
    Route::put('request-for-variation-categories/{Id}', array('as' => 'api.request-for-variation-categories.update', 'uses' => 'Api\PutDataApiController@requestForVariationCategories' ));
    Route::delete('request-for-variation-categories/{Id}', array('as' => 'api.request-for-variation-categories.delete', 'uses' => 'Api\DeleteDataApiController@requestForVariationCategories' ));

    //request_for_inspections
    Route::get('request-for-inspections', array('as' => 'api.request-for-inspections', 'uses' => 'Api\GetDataApiController@requestForInspections' ));
    Route::post('request-for-inspections', array('as' => 'api.request-for-inspections.create', 'uses' => 'Api\PostDataApiController@requestForInspections' ));
    Route::put('request-for-inspections/{Id}', array('as' => 'api.request-for-inspections.update', 'uses' => 'Api\PutDataApiController@requestForInspections' ));
    Route::delete('request-for-inspections/{Id}', array('as' => 'api.request-for-inspections.delete', 'uses' => 'Api\DeleteDataApiController@requestForInspections' ));

    //project_sectional_completion_dates
    Route::get('project-sectional-completion-dates', array('as' => 'api.project-sectional-completion-dates', 'uses' => 'Api\GetDataApiController@projectSectionalCompletionDates' ));
    Route::post('project-sectional-completion-dates', array('as' => 'api.project-sectional-completion-dates.create', 'uses' => 'Api\PostDataApiController@projectSectionalCompletionDates' ));
    Route::put('project-sectional-completion-dates/{Id}', array('as' => 'api.project-sectional-completion-dates.update', 'uses' => 'Api\PutDataApiController@projectSectionalCompletionDates' ));
    Route::delete('project-sectional-completion-dates/{Id}', array('as' => 'api.project-sectional-completion-dates.delete', 'uses' => 'Api\DeleteDataApiController@projectSectionalCompletionDates' ));

    //request_for_inspection_inspections
    Route::get('request-for-inspection-inspections', array('as' => 'api.request-for-inspection-inspections', 'uses' => 'Api\GetDataApiController@requestForInspectionInspections' ));
    Route::post('request-for-inspection-inspections', array('as' => 'api.request-for-inspection-inspections.create', 'uses' => 'Api\PostDataApiController@requestForInspectionInspections' ));
    Route::put('request-for-inspection-inspections/{Id}', array('as' => 'api.request-for-inspection-inspections.update', 'uses' => 'Api\PutDataApiController@requestForInspectionInspections' ));
    Route::delete('request-for-inspection-inspections/{Id}', array('as' => 'api.request-for-inspection-inspections.delete', 'uses' => 'Api\DeleteDataApiController@requestForInspectionInspections' ));

    //request_for_variation_action_logs
    Route::get('request-for-variation-action-logs', array('as' => 'api.request-for-variation-action-logs', 'uses' => 'Api\GetDataApiController@requestForVariationActionLogs' ));
    Route::post('request-for-variation-action-logs', array('as' => 'api.request-for-variation-action-logs.create', 'uses' => 'Api\PostDataApiController@requestForVariationActionLogs' ));
    Route::put('request-for-variation-action-logs/{Id}', array('as' => 'api.request-for-variation-action-logs.update', 'uses' => 'Api\PutDataApiController@requestForVariationActionLogs' ));
    Route::delete('request-for-variation-action-logs/{Id}', array('as' => 'api.request-for-variation-action-logs.delete', 'uses' => 'Api\DeleteDataApiController@requestForVariationActionLogs' ));

    //request_for_variation_files
    Route::get('request-for-variation-files', array('as' => 'api.request-for-variation-files', 'uses' => 'Api\GetDataApiController@requestForVariationFiles' ));
    Route::post('request-for-variation-files', array('as' => 'api.request-for-variation-files.create', 'uses' => 'Api\PostDataApiController@requestForVariationFiles' ));
    Route::put('request-for-variation-files/{Id}', array('as' => 'api.request-for-variation-files.update', 'uses' => 'Api\PutDataApiController@requestForVariationFiles' ));
    Route::delete('request-for-variation-files/{Id}', array('as' => 'api.request-for-variation-files.delete', 'uses' => 'Api\DeleteDataApiController@requestForVariationFiles' ));

    //rejected_materials
    Route::get('rejected-materials', array('as' => 'api.rejected-materials', 'uses' => 'Api\GetDataApiController@rejectedMaterials' ));
    Route::post('rejected-materials', array('as' => 'api.rejected-materials.create', 'uses' => 'Api\PostDataApiController@rejectedMaterials' ));
    Route::put('rejected-materials/{Id}', array('as' => 'api.rejected-materials.update', 'uses' => 'Api\PutDataApiController@rejectedMaterials' ));
    Route::delete('rejected-materials/{Id}', array('as' => 'api.rejected-materials.delete', 'uses' => 'Api\DeleteDataApiController@rejectedMaterials' ));

    //request_for_variation_user_permission_groups
    Route::get('request-for-variation-user-permission-groups', array('as' => 'api.request-for-variation-user-permission-groups', 'uses' => 'Api\GetDataApiController@requestForVariationUserPermissionGroups' ));
    Route::post('request-for-variation-user-permission-groups', array('as' => 'api.request-for-variation-user-permission-groups.create', 'uses' => 'Api\PostDataApiController@requestForVariationUserPermissionGroups' ));
    Route::put('request-for-variation-user-permission-groups/{Id}', array('as' => 'api.request-for-variation-user-permission-groups.update', 'uses' => 'Api\PutDataApiController@requestForVariationUserPermissionGroups' ));
    Route::delete('request-for-variation-user-permission-groups/{Id}', array('as' => 'api.request-for-variation-user-permission-groups.delete', 'uses' => 'Api\DeleteDataApiController@requestForVariationUserPermissionGroups' ));

    //request_for_variations
    Route::get('request-for-variations', array('as' => 'api.request-for-variations', 'uses' => 'Api\GetDataApiController@requestForVariations' ));
    Route::post('request-for-variations', array('as' => 'api.request-for-variations.create', 'uses' => 'Api\PostDataApiController@requestForVariations' ));
    Route::put('request-for-variations/{Id}', array('as' => 'api.request-for-variations.update', 'uses' => 'Api\PutDataApiController@requestForVariations' ));
    Route::delete('request-for-variations/{Id}', array('as' => 'api.request-for-variations.delete', 'uses' => 'Api\DeleteDataApiController@requestForVariations' ));

    //scheduled_maintenance
    Route::get('scheduled-maintenance', array('as' => 'api.scheduled-maintenance', 'uses' => 'Api\GetDataApiController@scheduledMaintenance' ));
    Route::post('scheduled-maintenance', array('as' => 'api.scheduled-maintenance.create', 'uses' => 'Api\PostDataApiController@scheduledMaintenance' ));
    Route::put('scheduled-maintenance/{Id}', array('as' => 'api.scheduled-maintenance.update', 'uses' => 'Api\PutDataApiController@scheduledMaintenance' ));
    Route::delete('scheduled-maintenance/{Id}', array('as' => 'api.scheduled-maintenance.delete', 'uses' => 'Api\DeleteDataApiController@scheduledMaintenance' ));

    //sent_tender_reminders_log
    Route::get('sent-tender-reminders-log', array('as' => 'api.sent-tender-reminders-log', 'uses' => 'Api\GetDataApiController@sentTenderRemindersLog' ));
    Route::post('sent-tender-reminders-log', array('as' => 'api.sent-tender-reminders-log.create', 'uses' => 'Api\PostDataApiController@sentTenderRemindersLog' ));
    Route::put('sent-tender-reminders-log/{Id}', array('as' => 'api.sent-tender-reminders-log.update', 'uses' => 'Api\PutDataApiController@sentTenderRemindersLog' ));
    Route::delete('sent-tender-reminders-log/{Id}', array('as' => 'api.sent-tender-reminders-log.delete', 'uses' => 'Api\DeleteDataApiController@sentTenderRemindersLog' ));

    //site_management_mcar
    Route::get('site-management-mcar', array('as' => 'api.site-management-mcar', 'uses' => 'Api\GetDataApiController@siteManagementMcar' ));
    Route::post('site-management-mcar', array('as' => 'api.site-management-mcar.create', 'uses' => 'Api\PostDataApiController@siteManagementMcar' ));
    Route::put('site-management-mcar/{Id}', array('as' => 'api.site-management-mcar.update', 'uses' => 'Api\PutDataApiController@siteManagementMcar' ));
    Route::delete('site-management-mcar/{Id}', array('as' => 'api.site-management-mcar.delete', 'uses' => 'Api\DeleteDataApiController@siteManagementMcar' ));

    //requests_for_inspection
    Route::get('requests-for-inspection', array('as' => 'api.requests-for-inspection', 'uses' => 'Api\GetDataApiController@requestsForInspection' ));
    Route::post('requests-for-inspection', array('as' => 'api.requests-for-inspection.create', 'uses' => 'Api\PostDataApiController@requestsForInspection' ));
    Route::put('requests-for-inspection/{Id}', array('as' => 'api.requests-for-inspection.update', 'uses' => 'Api\PutDataApiController@requestsForInspection' ));
    Route::delete('requests-for-inspection/{Id}', array('as' => 'api.requests-for-inspection.delete', 'uses' => 'Api\DeleteDataApiController@requestsForInspection' ));

    //site_management_mcar_form_responses
    Route::get('site-management-mcar-form-responses', array('as' => 'api.site-management-mcar-form-responses', 'uses' => 'Api\GetDataApiController@siteManagementMcarFormResponses' ));
    Route::post('site-management-mcar-form-responses', array('as' => 'api.site-management-mcar-form-responses.create', 'uses' => 'Api\PostDataApiController@siteManagementMcarFormResponses' ));
    Route::put('site-management-mcar-form-responses/{Id}', array('as' => 'api.site-management-mcar-form-responses.update', 'uses' => 'Api\PutDataApiController@siteManagementMcarFormResponses' ));
    Route::delete('site-management-mcar-form-responses/{Id}', array('as' => 'api.site-management-mcar-form-responses.delete', 'uses' => 'Api\DeleteDataApiController@siteManagementMcarFormResponses' ));

    //site_management_site_diary_general_form_responses
    Route::get('site-management-site-diary-general-form-responses', array('as' => 'api.site-management-site-diary-general-form-responses', 'uses' => 'Api\GetDataApiController@siteManagementSiteDiaryGeneralFormResponses' ));
    Route::post('site-management-site-diary-general-form-responses', array('as' => 'api.site-management-site-diary-general-form-responses.create', 'uses' => 'Api\PostDataApiController@siteManagementSiteDiaryGeneralFormResponses' ));
    Route::put('site-management-site-diary-general-form-responses/{Id}', array('as' => 'api.site-management-site-diary-general-form-responses.update', 'uses' => 'Api\PutDataApiController@siteManagementSiteDiaryGeneralFormResponses' ));
    Route::delete('site-management-site-diary-general-form-responses/{Id}', array('as' => 'api.site-management-site-diary-general-form-responses.delete', 'uses' => 'Api\DeleteDataApiController@siteManagementSiteDiaryGeneralFormResponses' ));

    //site_management_site_diary_labours
    Route::get('site-management-site-diary-labours', array('as' => 'api.site-management-site-diary-labours', 'uses' => 'Api\GetDataApiController@siteManagementSiteDiaryLabours' ));
    Route::post('site-management-site-diary-labours', array('as' => 'api.site-management-site-diary-labours.create', 'uses' => 'Api\PostDataApiController@siteManagementSiteDiaryLabours' ));
    Route::put('site-management-site-diary-labours/{Id}', array('as' => 'api.site-management-site-diary-labours.update', 'uses' => 'Api\PutDataApiController@siteManagementSiteDiaryLabours' ));
    Route::delete('site-management-site-diary-labours/{Id}', array('as' => 'api.site-management-site-diary-labours.delete', 'uses' => 'Api\DeleteDataApiController@siteManagementSiteDiaryLabours' ));

    //request_for_variation_user_permissions
    Route::get('request-for-variation-user-permissions', array('as' => 'api.request-for-variation-user-permissions', 'uses' => 'Api\GetDataApiController@requestForVariationUserPermissions' ));
    Route::post('request-for-variation-user-permissions', array('as' => 'api.request-for-variation-user-permissions.create', 'uses' => 'Api\PostDataApiController@requestForVariationUserPermissions' ));
    Route::put('request-for-variation-user-permissions/{Id}', array('as' => 'api.request-for-variation-user-permissions.update', 'uses' => 'Api\PutDataApiController@requestForVariationUserPermissions' ));
    Route::delete('request-for-variation-user-permissions/{Id}', array('as' => 'api.request-for-variation-user-permissions.delete', 'uses' => 'Api\DeleteDataApiController@requestForVariationUserPermissions' ));

    //risk_register_messages
    Route::get('risk-register-messages', array('as' => 'api.risk-register-messages', 'uses' => 'Api\GetDataApiController@riskRegisterMessages' ));
    Route::post('risk-register-messages', array('as' => 'api.risk-register-messages.create', 'uses' => 'Api\PostDataApiController@riskRegisterMessages' ));
    Route::put('risk-register-messages/{Id}', array('as' => 'api.risk-register-messages.update', 'uses' => 'Api\PutDataApiController@riskRegisterMessages' ));
    Route::delete('risk-register-messages/{Id}', array('as' => 'api.risk-register-messages.delete', 'uses' => 'Api\DeleteDataApiController@riskRegisterMessages' ));

    //site_management_defect_backcharge_details
    Route::get('site-management-defect-backcharge-details', array('as' => 'api.site-management-defect-backcharge-details', 'uses' => 'Api\GetDataApiController@siteManagementDefectBackchargeDetails' ));
    Route::post('site-management-defect-backcharge-details', array('as' => 'api.site-management-defect-backcharge-details.create', 'uses' => 'Api\PostDataApiController@siteManagementDefectBackchargeDetails' ));
    Route::put('site-management-defect-backcharge-details/{Id}', array('as' => 'api.site-management-defect-backcharge-details.update', 'uses' => 'Api\PutDataApiController@siteManagementDefectBackchargeDetails' ));
    Route::delete('site-management-defect-backcharge-details/{Id}', array('as' => 'api.site-management-defect-backcharge-details.delete', 'uses' => 'Api\DeleteDataApiController@siteManagementDefectBackchargeDetails' ));

    //site_management_defect_form_responses
    Route::get('site-management-defect-form-responses', array('as' => 'api.site-management-defect-form-responses', 'uses' => 'Api\GetDataApiController@siteManagementDefectFormResponses' ));
    Route::post('site-management-defect-form-responses', array('as' => 'api.site-management-defect-form-responses.create', 'uses' => 'Api\PostDataApiController@siteManagementDefectFormResponses' ));
    Route::put('site-management-defect-form-responses/{Id}', array('as' => 'api.site-management-defect-form-responses.update', 'uses' => 'Api\PutDataApiController@siteManagementDefectFormResponses' ));
    Route::delete('site-management-defect-form-responses/{Id}', array('as' => 'api.site-management-defect-form-responses.delete', 'uses' => 'Api\DeleteDataApiController@siteManagementDefectFormResponses' ));

    //site_management_site_diary_weathers
    Route::get('site-management-site-diary-weathers', array('as' => 'api.site-management-site-diary-weathers', 'uses' => 'Api\GetDataApiController@siteManagementSiteDiaryWeathers' ));
    Route::post('site-management-site-diary-weathers', array('as' => 'api.site-management-site-diary-weathers.create', 'uses' => 'Api\PostDataApiController@siteManagementSiteDiaryWeathers' ));
    Route::put('site-management-site-diary-weathers/{Id}', array('as' => 'api.site-management-site-diary-weathers.update', 'uses' => 'Api\PutDataApiController@siteManagementSiteDiaryWeathers' ));
    Route::delete('site-management-site-diary-weathers/{Id}', array('as' => 'api.site-management-site-diary-weathers.delete', 'uses' => 'Api\DeleteDataApiController@siteManagementSiteDiaryWeathers' ));

    //site_management_user_permissions
    Route::get('site-management-user-permissions', array('as' => 'api.site-management-user-permissions', 'uses' => 'Api\GetDataApiController@siteManagementUserPermissions' ));
    Route::post('site-management-user-permissions', array('as' => 'api.site-management-user-permissions.create', 'uses' => 'Api\PostDataApiController@siteManagementUserPermissions' ));
    Route::put('site-management-user-permissions/{Id}', array('as' => 'api.site-management-user-permissions.update', 'uses' => 'Api\PutDataApiController@siteManagementUserPermissions' ));
    Route::delete('site-management-user-permissions/{Id}', array('as' => 'api.site-management-user-permissions.delete', 'uses' => 'Api\DeleteDataApiController@siteManagementUserPermissions' ));

    //subsidiaries
    Route::get('subsidiaries', array('as' => 'api.subsidiaries', 'uses' => 'Api\GetDataApiController@subsidiaries' ));
    Route::post('subsidiaries', array('as' => 'api.subsidiaries.create', 'uses' => 'Api\PostDataApiController@subsidiaries' ));
    Route::put('subsidiaries/{Id}', array('as' => 'api.subsidiaries.update', 'uses' => 'Api\PutDataApiController@subsidiaries' ));
    Route::delete('subsidiaries/{Id}', array('as' => 'api.subsidiaries.delete', 'uses' => 'Api\DeleteDataApiController@subsidiaries' ));

    //subsidiary_apportionment_records
    Route::get('subsidiary-apportionment-records', array('as' => 'api.subsidiary-apportionment-records', 'uses' => 'Api\GetDataApiController@subsidiaryApportionmentRecords' ));
    Route::post('subsidiary-apportionment-records', array('as' => 'api.subsidiary-apportionment-records.create', 'uses' => 'Api\PostDataApiController@subsidiaryApportionmentRecords' ));
    Route::put('subsidiary-apportionment-records/{Id}', array('as' => 'api.subsidiary-apportionment-records.update', 'uses' => 'Api\PutDataApiController@subsidiaryApportionmentRecords' ));
    Route::delete('subsidiary-apportionment-records/{Id}', array('as' => 'api.subsidiary-apportionment-records.delete', 'uses' => 'Api\DeleteDataApiController@subsidiaryApportionmentRecords' ));

    //supplier_credit_facilities
    Route::get('supplier-credit-facilities', array('as' => 'api.supplier-credit-facilities', 'uses' => 'Api\GetDataApiController@supplierCreditFacilities' ));
    Route::post('supplier-credit-facilities', array('as' => 'api.supplier-credit-facilities.create', 'uses' => 'Api\PostDataApiController@supplierCreditFacilities' ));
    Route::put('supplier-credit-facilities/{Id}', array('as' => 'api.supplier-credit-facilities.update', 'uses' => 'Api\PutDataApiController@supplierCreditFacilities' ));
    Route::delete('supplier-credit-facilities/{Id}', array('as' => 'api.supplier-credit-facilities.delete', 'uses' => 'Api\DeleteDataApiController@supplierCreditFacilities' ));

    //site_management_site_diary_machinery
    Route::get('site-management-site-diary-machinery', array('as' => 'api.site-management-site-diary-machinery', 'uses' => 'Api\GetDataApiController@siteManagementSiteDiaryMachinery' ));
    Route::post('site-management-site-diary-machinery', array('as' => 'api.site-management-site-diary-machinery.create', 'uses' => 'Api\PostDataApiController@siteManagementSiteDiaryMachinery' ));
    Route::put('site-management-site-diary-machinery/{Id}', array('as' => 'api.site-management-site-diary-machinery.update', 'uses' => 'Api\PutDataApiController@siteManagementSiteDiaryMachinery' ));
    Route::delete('site-management-site-diary-machinery/{Id}', array('as' => 'api.site-management-site-diary-machinery.delete', 'uses' => 'Api\DeleteDataApiController@siteManagementSiteDiaryMachinery' ));

    //system_module_elements
    Route::get('system-module-elements', array('as' => 'api.system-module-elements', 'uses' => 'Api\GetDataApiController@systemModuleElements' ));
    Route::post('system-module-elements', array('as' => 'api.system-module-elements.create', 'uses' => 'Api\PostDataApiController@systemModuleElements' ));
    Route::put('system-module-elements/{Id}', array('as' => 'api.system-module-elements.update', 'uses' => 'Api\PutDataApiController@systemModuleElements' ));
    Route::delete('system-module-elements/{Id}', array('as' => 'api.system-module-elements.delete', 'uses' => 'Api\DeleteDataApiController@systemModuleElements' ));

    //supplier_credit_facility_settings
    Route::get('supplier-credit-facility-settings', array('as' => 'api.supplier-credit-facility-settings', 'uses' => 'Api\GetDataApiController@supplierCreditFacilitySettings' ));
    Route::post('supplier-credit-facility-settings', array('as' => 'api.supplier-credit-facility-settings.create', 'uses' => 'Api\PostDataApiController@supplierCreditFacilitySettings' ));
    Route::put('supplier-credit-facility-settings/{Id}', array('as' => 'api.supplier-credit-facility-settings.update', 'uses' => 'Api\PutDataApiController@supplierCreditFacilitySettings' ));
    Route::delete('supplier-credit-facility-settings/{Id}', array('as' => 'api.supplier-credit-facility-settings.delete', 'uses' => 'Api\DeleteDataApiController@supplierCreditFacilitySettings' ));

    //system_module_configurations
    Route::get('system-module-configurations', array('as' => 'api.system-module-configurations', 'uses' => 'Api\GetDataApiController@systemModuleConfigurations' ));
    Route::post('system-module-configurations', array('as' => 'api.system-module-configurations.create', 'uses' => 'Api\PostDataApiController@systemModuleConfigurations' ));
    Route::put('system-module-configurations/{Id}', array('as' => 'api.system-module-configurations.update', 'uses' => 'Api\PutDataApiController@systemModuleConfigurations' ));
    Route::delete('system-module-configurations/{Id}', array('as' => 'api.system-module-configurations.delete', 'uses' => 'Api\DeleteDataApiController@systemModuleConfigurations' ));

    //site_management_site_diary_rejected_materials
    Route::get('site-management-site-diary-rejected-materials', array('as' => 'api.site-management-site-diary-rejected-materials', 'uses' => 'Api\GetDataApiController@siteManagementSiteDiaryRejectedMaterials' ));
    Route::post('site-management-site-diary-rejected-materials', array('as' => 'api.site-management-site-diary-rejected-materials.create', 'uses' => 'Api\PostDataApiController@siteManagementSiteDiaryRejectedMaterials' ));
    Route::put('site-management-site-diary-rejected-materials/{Id}', array('as' => 'api.site-management-site-diary-rejected-materials.update', 'uses' => 'Api\PutDataApiController@siteManagementSiteDiaryRejectedMaterials' ));
    Route::delete('site-management-site-diary-rejected-materials/{Id}', array('as' => 'api.site-management-site-diary-rejected-materials.delete', 'uses' => 'Api\DeleteDataApiController@siteManagementSiteDiaryRejectedMaterials' ));

    //site_management_site_diary_visitors
    Route::get('site-management-site-diary-visitors', array('as' => 'api.site-management-site-diary-visitors', 'uses' => 'Api\GetDataApiController@siteManagementSiteDiaryVisitors' ));
    Route::post('site-management-site-diary-visitors', array('as' => 'api.site-management-site-diary-visitors.create', 'uses' => 'Api\PostDataApiController@siteManagementSiteDiaryVisitors' ));
    Route::put('site-management-site-diary-visitors/{Id}', array('as' => 'api.site-management-site-diary-visitors.update', 'uses' => 'Api\PutDataApiController@siteManagementSiteDiaryVisitors' ));
    Route::delete('site-management-site-diary-visitors/{Id}', array('as' => 'api.site-management-site-diary-visitors.delete', 'uses' => 'Api\DeleteDataApiController@siteManagementSiteDiaryVisitors' ));

    //structured_documents
    Route::get('structured-documents', array('as' => 'api.structured-documents', 'uses' => 'Api\GetDataApiController@structuredDocuments' ));
    Route::post('structured-documents', array('as' => 'api.structured-documents.create', 'uses' => 'Api\PostDataApiController@structuredDocuments' ));
    Route::put('structured-documents/{Id}', array('as' => 'api.structured-documents.update', 'uses' => 'Api\PutDataApiController@structuredDocuments' ));
    Route::delete('structured-documents/{Id}', array('as' => 'api.structured-documents.delete', 'uses' => 'Api\DeleteDataApiController@structuredDocuments' ));

    //system_settings
    Route::get('system-settings', array('as' => 'api.system-settings', 'uses' => 'Api\GetDataApiController@systemSettings' ));
    Route::post('system-settings', array('as' => 'api.system-settings.create', 'uses' => 'Api\PostDataApiController@systemSettings' ));
    Route::put('system-settings/{Id}', array('as' => 'api.system-settings.update', 'uses' => 'Api\PutDataApiController@systemSettings' ));
    Route::delete('system-settings/{Id}', array('as' => 'api.system-settings.delete', 'uses' => 'Api\DeleteDataApiController@systemSettings' ));

    //technical_evaluation_response_log
    Route::get('technical-evaluation-response-log', array('as' => 'api.technical-evaluation-response-log', 'uses' => 'Api\GetDataApiController@technicalEvaluationResponseLog' ));
    Route::post('technical-evaluation-response-log', array('as' => 'api.technical-evaluation-response-log.create', 'uses' => 'Api\PostDataApiController@technicalEvaluationResponseLog' ));
    Route::put('technical-evaluation-response-log/{Id}', array('as' => 'api.technical-evaluation-response-log.update', 'uses' => 'Api\PutDataApiController@technicalEvaluationResponseLog' ));
    Route::delete('technical-evaluation-response-log/{Id}', array('as' => 'api.technical-evaluation-response-log.delete', 'uses' => 'Api\DeleteDataApiController@technicalEvaluationResponseLog' ));

    //technical_evaluations
    Route::get('technical-evaluations', array('as' => 'api.technical-evaluations', 'uses' => 'Api\GetDataApiController@technicalEvaluations' ));
    Route::post('technical-evaluations', array('as' => 'api.technical-evaluations.create', 'uses' => 'Api\PostDataApiController@technicalEvaluations' ));
    Route::put('technical-evaluations/{Id}', array('as' => 'api.technical-evaluations.update', 'uses' => 'Api\PutDataApiController@technicalEvaluations' ));
    Route::delete('technical-evaluations/{Id}', array('as' => 'api.technical-evaluations.delete', 'uses' => 'Api\DeleteDataApiController@technicalEvaluations' ));

    //template_tender_document_folders
    Route::get('template-tender-document-folders', array('as' => 'api.template-tender-document-folders', 'uses' => 'Api\GetDataApiController@templateTenderDocumentFolders' ));
    Route::post('template-tender-document-folders', array('as' => 'api.template-tender-document-folders.create', 'uses' => 'Api\PostDataApiController@templateTenderDocumentFolders' ));
    Route::put('template-tender-document-folders/{Id}', array('as' => 'api.template-tender-document-folders.update', 'uses' => 'Api\PutDataApiController@templateTenderDocumentFolders' ));
    Route::delete('template-tender-document-folders/{Id}', array('as' => 'api.template-tender-document-folders.delete', 'uses' => 'Api\DeleteDataApiController@templateTenderDocumentFolders' ));

    //tender_document_download_logs
    Route::get('tender-document-download-logs', array('as' => 'api.tender-document-download-logs', 'uses' => 'Api\GetDataApiController@tenderDocumentDownloadLogs' ));
    Route::post('tender-document-download-logs', array('as' => 'api.tender-document-download-logs.create', 'uses' => 'Api\PostDataApiController@tenderDocumentDownloadLogs' ));
    Route::put('tender-document-download-logs/{Id}', array('as' => 'api.tender-document-download-logs.update', 'uses' => 'Api\PutDataApiController@tenderDocumentDownloadLogs' ));
    Route::delete('tender-document-download-logs/{Id}', array('as' => 'api.tender-document-download-logs.delete', 'uses' => 'Api\DeleteDataApiController@tenderDocumentDownloadLogs' ));

    //tender_calling_tender_information
    Route::get('tender-calling-tender-information', array('as' => 'api.tender-calling-tender-information', 'uses' => 'Api\GetDataApiController@tenderCallingTenderInformation' ));
    Route::post('tender-calling-tender-information', array('as' => 'api.tender-calling-tender-information.create', 'uses' => 'Api\PostDataApiController@tenderCallingTenderInformation' ));
    Route::put('tender-calling-tender-information/{Id}', array('as' => 'api.tender-calling-tender-information.update', 'uses' => 'Api\PutDataApiController@tenderCallingTenderInformation' ));
    Route::delete('tender-calling-tender-information/{Id}', array('as' => 'api.tender-calling-tender-information.delete', 'uses' => 'Api\DeleteDataApiController@tenderCallingTenderInformation' ));

    //tags
    Route::get('tags', array('as' => 'api.tags', 'uses' => 'Api\GetDataApiController@tags' ));
    Route::post('tags', array('as' => 'api.tags.create', 'uses' => 'Api\PostDataApiController@tags' ));
    Route::put('tags/{Id}', array('as' => 'api.tags.update', 'uses' => 'Api\PutDataApiController@tags' ));
    Route::delete('tags/{Id}', array('as' => 'api.tags.delete', 'uses' => 'Api\DeleteDataApiController@tags' ));

    //technical_evaluation_set_references
    Route::get('technical-evaluation-set-references', array('as' => 'api.technical-evaluation-set-references', 'uses' => 'Api\GetDataApiController@technicalEvaluationSetReferences' ));
    Route::post('technical-evaluation-set-references', array('as' => 'api.technical-evaluation-set-references.create', 'uses' => 'Api\PostDataApiController@technicalEvaluationSetReferences' ));
    Route::put('technical-evaluation-set-references/{Id}', array('as' => 'api.technical-evaluation-set-references.update', 'uses' => 'Api\PutDataApiController@technicalEvaluationSetReferences' ));
    Route::delete('technical-evaluation-set-references/{Id}', array('as' => 'api.technical-evaluation-set-references.delete', 'uses' => 'Api\DeleteDataApiController@technicalEvaluationSetReferences' ));

    //technical_evaluation_attachments
    Route::get('technical-evaluation-attachments', array('as' => 'api.technical-evaluation-attachments', 'uses' => 'Api\GetDataApiController@technicalEvaluationAttachments' ));
    Route::post('technical-evaluation-attachments', array('as' => 'api.technical-evaluation-attachments.create', 'uses' => 'Api\PostDataApiController@technicalEvaluationAttachments' ));
    Route::put('technical-evaluation-attachments/{Id}', array('as' => 'api.technical-evaluation-attachments.update', 'uses' => 'Api\PutDataApiController@technicalEvaluationAttachments' ));
    Route::delete('technical-evaluation-attachments/{Id}', array('as' => 'api.technical-evaluation-attachments.delete', 'uses' => 'Api\DeleteDataApiController@technicalEvaluationAttachments' ));

    //technical_evaluation_items
    Route::get('technical-evaluation-items', array('as' => 'api.technical-evaluation-items', 'uses' => 'Api\GetDataApiController@technicalEvaluationItems' ));
    Route::post('technical-evaluation-items', array('as' => 'api.technical-evaluation-items.create', 'uses' => 'Api\PostDataApiController@technicalEvaluationItems' ));
    Route::put('technical-evaluation-items/{Id}', array('as' => 'api.technical-evaluation-items.update', 'uses' => 'Api\PutDataApiController@technicalEvaluationItems' ));
    Route::delete('technical-evaluation-items/{Id}', array('as' => 'api.technical-evaluation-items.delete', 'uses' => 'Api\DeleteDataApiController@technicalEvaluationItems' ));

    //technical_evaluation_tenderer_options
    Route::get('technical-evaluation-tenderer-options', array('as' => 'api.technical-evaluation-tenderer-options', 'uses' => 'Api\GetDataApiController@technicalEvaluationTendererOptions' ));
    Route::post('technical-evaluation-tenderer-options', array('as' => 'api.technical-evaluation-tenderer-options.create', 'uses' => 'Api\PostDataApiController@technicalEvaluationTendererOptions' ));
    Route::put('technical-evaluation-tenderer-options/{Id}', array('as' => 'api.technical-evaluation-tenderer-options.update', 'uses' => 'Api\PutDataApiController@technicalEvaluationTendererOptions' ));
    Route::delete('technical-evaluation-tenderer-options/{Id}', array('as' => 'api.technical-evaluation-tenderer-options.delete', 'uses' => 'Api\DeleteDataApiController@technicalEvaluationTendererOptions' ));

    //technical_evaluation_verifier_logs
    Route::get('technical-evaluation-verifier-logs', array('as' => 'api.technical-evaluation-verifier-logs', 'uses' => 'Api\GetDataApiController@technicalEvaluationVerifierLogs' ));
    Route::post('technical-evaluation-verifier-logs', array('as' => 'api.technical-evaluation-verifier-logs.create', 'uses' => 'Api\PostDataApiController@technicalEvaluationVerifierLogs' ));
    Route::put('technical-evaluation-verifier-logs/{Id}', array('as' => 'api.technical-evaluation-verifier-logs.update', 'uses' => 'Api\PutDataApiController@technicalEvaluationVerifierLogs' ));
    Route::delete('technical-evaluation-verifier-logs/{Id}', array('as' => 'api.technical-evaluation-verifier-logs.delete', 'uses' => 'Api\DeleteDataApiController@technicalEvaluationVerifierLogs' ));

    //template_tender_document_folder_work_category
    Route::get('template-tender-document-folder-work-category', array('as' => 'api.template-tender-document-folder-work-category', 'uses' => 'Api\GetDataApiController@templateTenderDocumentFolderWorkCategory' ));
    Route::post('template-tender-document-folder-work-category', array('as' => 'api.template-tender-document-folder-work-category.create', 'uses' => 'Api\PostDataApiController@templateTenderDocumentFolderWorkCategory' ));
    Route::put('template-tender-document-folder-work-category/{Id}', array('as' => 'api.template-tender-document-folder-work-category.update', 'uses' => 'Api\PutDataApiController@templateTenderDocumentFolderWorkCategory' ));
    Route::delete('template-tender-document-folder-work-category/{Id}', array('as' => 'api.template-tender-document-folder-work-category.delete', 'uses' => 'Api\DeleteDataApiController@templateTenderDocumentFolderWorkCategory' ));

    //template_tender_document_files
    Route::get('template-tender-document-files', array('as' => 'api.template-tender-document-files', 'uses' => 'Api\GetDataApiController@templateTenderDocumentFiles' ));
    Route::post('template-tender-document-files', array('as' => 'api.template-tender-document-files.create', 'uses' => 'Api\PostDataApiController@templateTenderDocumentFiles' ));
    Route::put('template-tender-document-files/{Id}', array('as' => 'api.template-tender-document-files.update', 'uses' => 'Api\PutDataApiController@templateTenderDocumentFiles' ));
    Route::delete('template-tender-document-files/{Id}', array('as' => 'api.template-tender-document-files.delete', 'uses' => 'Api\DeleteDataApiController@templateTenderDocumentFiles' ));

    //template_tender_document_files_roles_readonly
    Route::get('template-tender-document-files-roles-readonly', array('as' => 'api.template-tender-document-files-roles-readonly', 'uses' => 'Api\GetDataApiController@templateTenderDocumentFilesRolesReadonly' ));
    Route::post('template-tender-document-files-roles-readonly', array('as' => 'api.template-tender-document-files-roles-readonly.create', 'uses' => 'Api\PostDataApiController@templateTenderDocumentFilesRolesReadonly' ));
    Route::put('template-tender-document-files-roles-readonly/{Id}', array('as' => 'api.template-tender-document-files-roles-readonly.update', 'uses' => 'Api\PutDataApiController@templateTenderDocumentFilesRolesReadonly' ));
    Route::delete('template-tender-document-files-roles-readonly/{Id}', array('as' => 'api.template-tender-document-files-roles-readonly.delete', 'uses' => 'Api\DeleteDataApiController@templateTenderDocumentFilesRolesReadonly' ));

    //tender_alternatives_position
    Route::get('tender-alternatives-position', array('as' => 'api.tender-alternatives-position', 'uses' => 'Api\GetDataApiController@tenderAlternativesPosition' ));
    Route::post('tender-alternatives-position', array('as' => 'api.tender-alternatives-position.create', 'uses' => 'Api\PostDataApiController@tenderAlternativesPosition' ));
    Route::put('tender-alternatives-position/{Id}', array('as' => 'api.tender-alternatives-position.update', 'uses' => 'Api\PutDataApiController@tenderAlternativesPosition' ));
    Route::delete('tender-alternatives-position/{Id}', array('as' => 'api.tender-alternatives-position.delete', 'uses' => 'Api\DeleteDataApiController@tenderAlternativesPosition' ));

    //tender_calling_tender_information_user
    Route::get('tender-calling-tender-information-user', array('as' => 'api.tender-calling-tender-information-user', 'uses' => 'Api\GetDataApiController@tenderCallingTenderInformationUser' ));
    Route::post('tender-calling-tender-information-user', array('as' => 'api.tender-calling-tender-information-user.create', 'uses' => 'Api\PostDataApiController@tenderCallingTenderInformationUser' ));
    Route::put('tender-calling-tender-information-user/{Id}', array('as' => 'api.tender-calling-tender-information-user.update', 'uses' => 'Api\PutDataApiController@tenderCallingTenderInformationUser' ));
    Route::delete('tender-calling-tender-information-user/{Id}', array('as' => 'api.tender-calling-tender-information-user.delete', 'uses' => 'Api\DeleteDataApiController@tenderCallingTenderInformationUser' ));

    //tender_interview_information
    Route::get('tender-interview-information', array('as' => 'api.tender-interview-information', 'uses' => 'Api\GetDataApiController@tenderInterviewInformation' ));
    Route::post('tender-interview-information', array('as' => 'api.tender-interview-information.create', 'uses' => 'Api\PostDataApiController@tenderInterviewInformation' ));
    Route::put('tender-interview-information/{Id}', array('as' => 'api.tender-interview-information.update', 'uses' => 'Api\PutDataApiController@tenderInterviewInformation' ));
    Route::delete('tender-interview-information/{Id}', array('as' => 'api.tender-interview-information.delete', 'uses' => 'Api\DeleteDataApiController@tenderInterviewInformation' ));

    //tender_lot_information_user
    Route::get('tender-lot-information-user', array('as' => 'api.tender-lot-information-user', 'uses' => 'Api\GetDataApiController@tenderLotInformationUser' ));
    Route::post('tender-lot-information-user', array('as' => 'api.tender-lot-information-user.create', 'uses' => 'Api\PostDataApiController@tenderLotInformationUser' ));
    Route::put('tender-lot-information-user/{Id}', array('as' => 'api.tender-lot-information-user.update', 'uses' => 'Api\PutDataApiController@tenderLotInformationUser' ));
    Route::delete('tender-lot-information-user/{Id}', array('as' => 'api.tender-lot-information-user.delete', 'uses' => 'Api\DeleteDataApiController@tenderLotInformationUser' ));

    //tender_rot_information
    Route::get('tender-rot-information', array('as' => 'api.tender-rot-information', 'uses' => 'Api\GetDataApiController@tenderRotInformation' ));
    Route::post('tender-rot-information', array('as' => 'api.tender-rot-information.create', 'uses' => 'Api\PostDataApiController@tenderRotInformation' ));
    Route::put('tender-rot-information/{Id}', array('as' => 'api.tender-rot-information.update', 'uses' => 'Api\PutDataApiController@tenderRotInformation' ));
    Route::delete('tender-rot-information/{Id}', array('as' => 'api.tender-rot-information.delete', 'uses' => 'Api\DeleteDataApiController@tenderRotInformation' ));

    //tender_rot_information_user
    Route::get('tender-rot-information-user', array('as' => 'api.tender-rot-information-user', 'uses' => 'Api\GetDataApiController@tenderRotInformationUser' ));
    Route::post('tender-rot-information-user', array('as' => 'api.tender-rot-information-user.create', 'uses' => 'Api\PostDataApiController@tenderRotInformationUser' ));
    Route::put('tender-rot-information-user/{Id}', array('as' => 'api.tender-rot-information-user.update', 'uses' => 'Api\PutDataApiController@tenderRotInformationUser' ));
    Route::delete('tender-rot-information-user/{Id}', array('as' => 'api.tender-rot-information-user.delete', 'uses' => 'Api\DeleteDataApiController@tenderRotInformationUser' ));

    //tender_document_files
    Route::get('tender-document-files', array('as' => 'api.tender-document-files', 'uses' => 'Api\GetDataApiController@tenderDocumentFiles' ));
    Route::post('tender-document-files', array('as' => 'api.tender-document-files.create', 'uses' => 'Api\PostDataApiController@tenderDocumentFiles' ));
    Route::put('tender-document-files/{Id}', array('as' => 'api.tender-document-files.update', 'uses' => 'Api\PutDataApiController@tenderDocumentFiles' ));
    Route::delete('tender-document-files/{Id}', array('as' => 'api.tender-document-files.delete', 'uses' => 'Api\DeleteDataApiController@tenderDocumentFiles' ));

    //tender_lot_information
    Route::get('tender-lot-information', array('as' => 'api.tender-lot-information', 'uses' => 'Api\GetDataApiController@tenderLotInformation' ));
    Route::post('tender-lot-information', array('as' => 'api.tender-lot-information.create', 'uses' => 'Api\PostDataApiController@tenderLotInformation' ));
    Route::put('tender-lot-information/{Id}', array('as' => 'api.tender-lot-information.update', 'uses' => 'Api\PutDataApiController@tenderLotInformation' ));
    Route::delete('tender-lot-information/{Id}', array('as' => 'api.tender-lot-information.delete', 'uses' => 'Api\DeleteDataApiController@tenderLotInformation' ));

    //tender_document_files_roles_readonly
    Route::get('tender-document-files-roles-readonly', array('as' => 'api.tender-document-files-roles-readonly', 'uses' => 'Api\GetDataApiController@tenderDocumentFilesRolesReadonly' ));
    Route::post('tender-document-files-roles-readonly', array('as' => 'api.tender-document-files-roles-readonly.create', 'uses' => 'Api\PostDataApiController@tenderDocumentFilesRolesReadonly' ));
    Route::put('tender-document-files-roles-readonly/{Id}', array('as' => 'api.tender-document-files-roles-readonly.update', 'uses' => 'Api\PutDataApiController@tenderDocumentFilesRolesReadonly' ));
    Route::delete('tender-document-files-roles-readonly/{Id}', array('as' => 'api.tender-document-files-roles-readonly.delete', 'uses' => 'Api\DeleteDataApiController@tenderDocumentFilesRolesReadonly' ));

    //tender_document_folders
    Route::get('tender-document-folders', array('as' => 'api.tender-document-folders', 'uses' => 'Api\GetDataApiController@tenderDocumentFolders' ));
    Route::post('tender-document-folders', array('as' => 'api.tender-document-folders.create', 'uses' => 'Api\PostDataApiController@tenderDocumentFolders' ));
    Route::put('tender-document-folders/{Id}', array('as' => 'api.tender-document-folders.update', 'uses' => 'Api\PutDataApiController@tenderDocumentFolders' ));
    Route::delete('tender-document-folders/{Id}', array('as' => 'api.tender-document-folders.delete', 'uses' => 'Api\DeleteDataApiController@tenderDocumentFolders' ));

    //tender_form_verifier_logs
    Route::get('tender-form-verifier-logs', array('as' => 'api.tender-form-verifier-logs', 'uses' => 'Api\GetDataApiController@tenderFormVerifierLogs' ));
    Route::post('tender-form-verifier-logs', array('as' => 'api.tender-form-verifier-logs.create', 'uses' => 'Api\PostDataApiController@tenderFormVerifierLogs' ));
    Route::put('tender-form-verifier-logs/{Id}', array('as' => 'api.tender-form-verifier-logs.update', 'uses' => 'Api\PutDataApiController@tenderFormVerifierLogs' ));
    Route::delete('tender-form-verifier-logs/{Id}', array('as' => 'api.tender-form-verifier-logs.delete', 'uses' => 'Api\DeleteDataApiController@tenderFormVerifierLogs' ));

    //tender_interviews
    Route::get('tender-interviews', array('as' => 'api.tender-interviews', 'uses' => 'Api\GetDataApiController@tenderInterviews' ));
    Route::post('tender-interviews', array('as' => 'api.tender-interviews.create', 'uses' => 'Api\PostDataApiController@tenderInterviews' ));
    Route::put('tender-interviews/{Id}', array('as' => 'api.tender-interviews.update', 'uses' => 'Api\PutDataApiController@tenderInterviews' ));
    Route::delete('tender-interviews/{Id}', array('as' => 'api.tender-interviews.delete', 'uses' => 'Api\DeleteDataApiController@tenderInterviews' ));

    //tender_reminders
    Route::get('tender-reminders', array('as' => 'api.tender-reminders', 'uses' => 'Api\GetDataApiController@tenderReminders' ));
    Route::post('tender-reminders', array('as' => 'api.tender-reminders.create', 'uses' => 'Api\PostDataApiController@tenderReminders' ));
    Route::put('tender-reminders/{Id}', array('as' => 'api.tender-reminders.update', 'uses' => 'Api\PutDataApiController@tenderReminders' ));
    Route::delete('tender-reminders/{Id}', array('as' => 'api.tender-reminders.delete', 'uses' => 'Api\DeleteDataApiController@tenderReminders' ));

    //tender_user_technical_evaluation_verifier
    Route::get('tender-user-technical-evaluation-verifier', array('as' => 'api.tender-user-technical-evaluation-verifier', 'uses' => 'Api\GetDataApiController@tenderUserTechnicalEvaluationVerifier' ));
    Route::post('tender-user-technical-evaluation-verifier', array('as' => 'api.tender-user-technical-evaluation-verifier.create', 'uses' => 'Api\PostDataApiController@tenderUserTechnicalEvaluationVerifier' ));
    Route::put('tender-user-technical-evaluation-verifier/{Id}', array('as' => 'api.tender-user-technical-evaluation-verifier.update', 'uses' => 'Api\PutDataApiController@tenderUserTechnicalEvaluationVerifier' ));
    Route::delete('tender-user-technical-evaluation-verifier/{Id}', array('as' => 'api.tender-user-technical-evaluation-verifier.delete', 'uses' => 'Api\DeleteDataApiController@tenderUserTechnicalEvaluationVerifier' ));

    //tender_user_verifier_open_tender
    Route::get('tender-user-verifier-open-tender', array('as' => 'api.tender-user-verifier-open-tender', 'uses' => 'Api\GetDataApiController@tenderUserVerifierOpenTender' ));
    Route::post('tender-user-verifier-open-tender', array('as' => 'api.tender-user-verifier-open-tender.create', 'uses' => 'Api\PostDataApiController@tenderUserVerifierOpenTender' ));
    Route::put('tender-user-verifier-open-tender/{Id}', array('as' => 'api.tender-user-verifier-open-tender.update', 'uses' => 'Api\PutDataApiController@tenderUserVerifierOpenTender' ));
    Route::delete('tender-user-verifier-open-tender/{Id}', array('as' => 'api.tender-user-verifier-open-tender.delete', 'uses' => 'Api\DeleteDataApiController@tenderUserVerifierOpenTender' ));

    //tender_user_verifier_retender
    Route::get('tender-user-verifier-retender', array('as' => 'api.tender-user-verifier-retender', 'uses' => 'Api\GetDataApiController@tenderUserVerifierRetender' ));
    Route::post('tender-user-verifier-retender', array('as' => 'api.tender-user-verifier-retender.create', 'uses' => 'Api\PostDataApiController@tenderUserVerifierRetender' ));
    Route::put('tender-user-verifier-retender/{Id}', array('as' => 'api.tender-user-verifier-retender.update', 'uses' => 'Api\PutDataApiController@tenderUserVerifierRetender' ));
    Route::delete('tender-user-verifier-retender/{Id}', array('as' => 'api.tender-user-verifier-retender.delete', 'uses' => 'Api\DeleteDataApiController@tenderUserVerifierRetender' ));

    //tenderer_technical_evaluation_information
    Route::get('tenderer-technical-evaluation-information', array('as' => 'api.tenderer-technical-evaluation-information', 'uses' => 'Api\GetDataApiController@tendererTechnicalEvaluationInformation' ));
    Route::post('tenderer-technical-evaluation-information', array('as' => 'api.tenderer-technical-evaluation-information.create', 'uses' => 'Api\PostDataApiController@tendererTechnicalEvaluationInformation' ));
    Route::put('tenderer-technical-evaluation-information/{Id}', array('as' => 'api.tenderer-technical-evaluation-information.update', 'uses' => 'Api\PutDataApiController@tendererTechnicalEvaluationInformation' ));
    Route::delete('tenderer-technical-evaluation-information/{Id}', array('as' => 'api.tenderer-technical-evaluation-information.delete', 'uses' => 'Api\DeleteDataApiController@tendererTechnicalEvaluationInformation' ));

    //tenderer_technical_evaluation_information_log
    Route::get('tenderer-technical-evaluation-information-log', array('as' => 'api.tenderer-technical-evaluation-information-log', 'uses' => 'Api\GetDataApiController@tendererTechnicalEvaluationInformationLog' ));
    Route::post('tenderer-technical-evaluation-information-log', array('as' => 'api.tenderer-technical-evaluation-information-log.create', 'uses' => 'Api\PostDataApiController@tendererTechnicalEvaluationInformationLog' ));
    Route::put('tenderer-technical-evaluation-information-log/{Id}', array('as' => 'api.tenderer-technical-evaluation-information-log.update', 'uses' => 'Api\PutDataApiController@tendererTechnicalEvaluationInformationLog' ));
    Route::delete('tenderer-technical-evaluation-information-log/{Id}', array('as' => 'api.tenderer-technical-evaluation-information-log.delete', 'uses' => 'Api\DeleteDataApiController@tendererTechnicalEvaluationInformationLog' ));

    //theme_settings
    Route::get('theme-settings', array('as' => 'api.theme-settings', 'uses' => 'Api\GetDataApiController@themeSettings' ));
    Route::post('theme-settings', array('as' => 'api.theme-settings.create', 'uses' => 'Api\PostDataApiController@themeSettings' ));
    Route::put('theme-settings/{Id}', array('as' => 'api.theme-settings.update', 'uses' => 'Api\PutDataApiController@themeSettings' ));
    Route::delete('theme-settings/{Id}', array('as' => 'api.theme-settings.delete', 'uses' => 'Api\DeleteDataApiController@themeSettings' ));

    //users
    Route::get('users', array('as' => 'api.users', 'uses' => 'Api\GetDataApiController@users' ));
    Route::post('users', array('as' => 'api.users.create', 'uses' => 'Api\PostDataApiController@users' ));
    Route::put('users/{Id}', array('as' => 'api.users.update', 'uses' => 'Api\PutDataApiController@users' ));
    Route::delete('users/{Id}', array('as' => 'api.users.delete', 'uses' => 'Api\DeleteDataApiController@users' ));

    //user_company_log
    Route::get('user-company-log', array('as' => 'api.user-company-log', 'uses' => 'Api\GetDataApiController@userCompanyLog' ));
    Route::post('user-company-log', array('as' => 'api.user-company-log.create', 'uses' => 'Api\PostDataApiController@userCompanyLog' ));
    Route::put('user-company-log/{Id}', array('as' => 'api.user-company-log.update', 'uses' => 'Api\PutDataApiController@userCompanyLog' ));
    Route::delete('user-company-log/{Id}', array('as' => 'api.user-company-log.delete', 'uses' => 'Api\DeleteDataApiController@userCompanyLog' ));

    //track_record_projects
    Route::get('track-record-projects', array('as' => 'api.track-record-projects', 'uses' => 'Api\GetDataApiController@trackRecordProjects' ));
    Route::post('track-record-projects', array('as' => 'api.track-record-projects.create', 'uses' => 'Api\PostDataApiController@trackRecordProjects' ));
    Route::put('track-record-projects/{Id}', array('as' => 'api.track-record-projects.update', 'uses' => 'Api\PutDataApiController@trackRecordProjects' ));
    Route::delete('track-record-projects/{Id}', array('as' => 'api.track-record-projects.delete', 'uses' => 'Api\DeleteDataApiController@trackRecordProjects' ));

    //vendor_categories
    Route::get('vendor-categories', array('as' => 'api.vendor-categories', 'uses' => 'Api\GetDataApiController@vendorCategories' ));
    Route::post('vendor-categories', array('as' => 'api.vendor-categories.create', 'uses' => 'Api\PostDataApiController@vendorCategories' ));
    Route::put('vendor-categories/{Id}', array('as' => 'api.vendor-categories.update', 'uses' => 'Api\PutDataApiController@vendorCategories' ));
    Route::delete('vendor-categories/{Id}', array('as' => 'api.vendor-categories.delete', 'uses' => 'Api\DeleteDataApiController@vendorCategories' ));

    //vendor_detail_settings
    Route::get('vendor-detail-settings', array('as' => 'api.vendor-detail-settings', 'uses' => 'Api\GetDataApiController@vendorDetailSettings' ));
    Route::post('vendor-detail-settings', array('as' => 'api.vendor-detail-settings.create', 'uses' => 'Api\PostDataApiController@vendorDetailSettings' ));
    Route::put('vendor-detail-settings/{Id}', array('as' => 'api.vendor-detail-settings.update', 'uses' => 'Api\PutDataApiController@vendorDetailSettings' ));
    Route::delete('vendor-detail-settings/{Id}', array('as' => 'api.vendor-detail-settings.delete', 'uses' => 'Api\DeleteDataApiController@vendorDetailSettings' ));

    //tenders
    Route::get('tenders', array('as' => 'api.tenders', 'uses' => 'Api\GetDataApiController@tenders' ));
    Route::post('tenders', array('as' => 'api.tenders.create', 'uses' => 'Api\PostDataApiController@tenders' ));
    Route::put('tenders/{Id}', array('as' => 'api.tenders.update', 'uses' => 'Api\PutDataApiController@tenders' ));
    Route::delete('tenders/{Id}', array('as' => 'api.tenders.delete', 'uses' => 'Api\DeleteDataApiController@tenders' ));

    //uploads
    Route::get('uploads', array('as' => 'api.uploads', 'uses' => 'Api\GetDataApiController@uploads' ));
    Route::post('uploads', array('as' => 'api.uploads.create', 'uses' => 'Api\PostDataApiController@uploads' ));
    Route::put('uploads/{Id}', array('as' => 'api.uploads.update', 'uses' => 'Api\PutDataApiController@uploads' ));
    Route::delete('uploads/{Id}', array('as' => 'api.uploads.delete', 'uses' => 'Api\DeleteDataApiController@uploads' ));

    //user_logins
    Route::get('user-logins', array('as' => 'api.user-logins', 'uses' => 'Api\GetDataApiController@userLogins' ));
    Route::post('user-logins', array('as' => 'api.user-logins.create', 'uses' => 'Api\PostDataApiController@userLogins' ));
    Route::put('user-logins/{Id}', array('as' => 'api.user-logins.update', 'uses' => 'Api\PutDataApiController@userLogins' ));
    Route::delete('user-logins/{Id}', array('as' => 'api.user-logins.delete', 'uses' => 'Api\DeleteDataApiController@userLogins' ));

    //user_settings
    Route::get('user-settings', array('as' => 'api.user-settings', 'uses' => 'Api\GetDataApiController@userSettings' ));
    Route::post('user-settings', array('as' => 'api.user-settings.create', 'uses' => 'Api\PostDataApiController@userSettings' ));
    Route::put('user-settings/{Id}', array('as' => 'api.user-settings.update', 'uses' => 'Api\PutDataApiController@userSettings' ));
    Route::delete('user-settings/{Id}', array('as' => 'api.user-settings.delete', 'uses' => 'Api\DeleteDataApiController@userSettings' ));

    //users_company_verification_privileges
    Route::get('users-company-verification-privileges', array('as' => 'api.users-company-verification-privileges', 'uses' => 'Api\GetDataApiController@usersCompanyVerificationPrivileges' ));
    Route::post('users-company-verification-privileges', array('as' => 'api.users-company-verification-privileges.create', 'uses' => 'Api\PostDataApiController@usersCompanyVerificationPrivileges' ));
    Route::put('users-company-verification-privileges/{Id}', array('as' => 'api.users-company-verification-privileges.update', 'uses' => 'Api\PutDataApiController@usersCompanyVerificationPrivileges' ));
    Route::delete('users-company-verification-privileges/{Id}', array('as' => 'api.users-company-verification-privileges.delete', 'uses' => 'Api\DeleteDataApiController@usersCompanyVerificationPrivileges' ));

    //vendor_category_temporary_records
    Route::get('vendor-category-temporary-records', array('as' => 'api.vendor-category-temporary-records', 'uses' => 'Api\GetDataApiController@vendorCategoryTemporaryRecords' ));
    Route::post('vendor-category-temporary-records', array('as' => 'api.vendor-category-temporary-records.create', 'uses' => 'Api\PostDataApiController@vendorCategoryTemporaryRecords' ));
    Route::put('vendor-category-temporary-records/{Id}', array('as' => 'api.vendor-category-temporary-records.update', 'uses' => 'Api\PutDataApiController@vendorCategoryTemporaryRecords' ));
    Route::delete('vendor-category-temporary-records/{Id}', array('as' => 'api.vendor-category-temporary-records.delete', 'uses' => 'Api\DeleteDataApiController@vendorCategoryTemporaryRecords' ));

    //vendor_category_vendor_work_category
    Route::get('vendor-category-vendor-work-category', array('as' => 'api.vendor-category-vendor-work-category', 'uses' => 'Api\GetDataApiController@vendorCategoryVendorWorkCategory' ));
    Route::post('vendor-category-vendor-work-category', array('as' => 'api.vendor-category-vendor-work-category.create', 'uses' => 'Api\PostDataApiController@vendorCategoryVendorWorkCategory' ));
    Route::put('vendor-category-vendor-work-category/{Id}', array('as' => 'api.vendor-category-vendor-work-category.update', 'uses' => 'Api\PutDataApiController@vendorCategoryVendorWorkCategory' ));
    Route::delete('vendor-category-vendor-work-category/{Id}', array('as' => 'api.vendor-category-vendor-work-category.delete', 'uses' => 'Api\DeleteDataApiController@vendorCategoryVendorWorkCategory' ));

    //vendor_evaluation_cycle_scores
    Route::get('vendor-evaluation-cycle-scores', array('as' => 'api.vendor-evaluation-cycle-scores', 'uses' => 'Api\GetDataApiController@vendorEvaluationCycleScores' ));
    Route::post('vendor-evaluation-cycle-scores', array('as' => 'api.vendor-evaluation-cycle-scores.create', 'uses' => 'Api\PostDataApiController@vendorEvaluationCycleScores' ));
    Route::put('vendor-evaluation-cycle-scores/{Id}', array('as' => 'api.vendor-evaluation-cycle-scores.update', 'uses' => 'Api\PutDataApiController@vendorEvaluationCycleScores' ));
    Route::delete('vendor-evaluation-cycle-scores/{Id}', array('as' => 'api.vendor-evaluation-cycle-scores.delete', 'uses' => 'Api\DeleteDataApiController@vendorEvaluationCycleScores' ));

    //vendor_evaluation_scores
    Route::get('vendor-evaluation-scores', array('as' => 'api.vendor-evaluation-scores', 'uses' => 'Api\GetDataApiController@vendorEvaluationScores' ));
    Route::post('vendor-evaluation-scores', array('as' => 'api.vendor-evaluation-scores.create', 'uses' => 'Api\PostDataApiController@vendorEvaluationScores' ));
    Route::put('vendor-evaluation-scores/{Id}', array('as' => 'api.vendor-evaluation-scores.update', 'uses' => 'Api\PutDataApiController@vendorEvaluationScores' ));
    Route::delete('vendor-evaluation-scores/{Id}', array('as' => 'api.vendor-evaluation-scores.delete', 'uses' => 'Api\DeleteDataApiController@vendorEvaluationScores' ));

    //vendor_management_instruction_settings
    Route::get('vendor-management-instruction-settings', array('as' => 'api.vendor-management-instruction-settings', 'uses' => 'Api\GetDataApiController@vendorManagementInstructionSettings' ));
    Route::post('vendor-management-instruction-settings', array('as' => 'api.vendor-management-instruction-settings.create', 'uses' => 'Api\PostDataApiController@vendorManagementInstructionSettings' ));
    Route::put('vendor-management-instruction-settings/{Id}', array('as' => 'api.vendor-management-instruction-settings.update', 'uses' => 'Api\PutDataApiController@vendorManagementInstructionSettings' ));
    Route::delete('vendor-management-instruction-settings/{Id}', array('as' => 'api.vendor-management-instruction-settings.delete', 'uses' => 'Api\DeleteDataApiController@vendorManagementInstructionSettings' ));

    //vendor_performance_evaluation_form_change_logs
    Route::get('vendor-performance-evaluation-form-change-logs', array('as' => 'api.vendor-performance-evaluation-form-change-logs', 'uses' => 'Api\GetDataApiController@vendorPerformanceEvaluationFormChangeLogs' ));
    Route::post('vendor-performance-evaluation-form-change-logs', array('as' => 'api.vendor-performance-evaluation-form-change-logs.create', 'uses' => 'Api\PostDataApiController@vendorPerformanceEvaluationFormChangeLogs' ));
    Route::put('vendor-performance-evaluation-form-change-logs/{Id}', array('as' => 'api.vendor-performance-evaluation-form-change-logs.update', 'uses' => 'Api\PutDataApiController@vendorPerformanceEvaluationFormChangeLogs' ));
    Route::delete('vendor-performance-evaluation-form-change-logs/{Id}', array('as' => 'api.vendor-performance-evaluation-form-change-logs.delete', 'uses' => 'Api\DeleteDataApiController@vendorPerformanceEvaluationFormChangeLogs' ));

    //vendor_management_grade_levels
    Route::get('vendor-management-grade-levels', array('as' => 'api.vendor-management-grade-levels', 'uses' => 'Api\GetDataApiController@vendorManagementGradeLevels' ));
    Route::post('vendor-management-grade-levels', array('as' => 'api.vendor-management-grade-levels.create', 'uses' => 'Api\PostDataApiController@vendorManagementGradeLevels' ));
    Route::put('vendor-management-grade-levels/{Id}', array('as' => 'api.vendor-management-grade-levels.update', 'uses' => 'Api\PutDataApiController@vendorManagementGradeLevels' ));
    Route::delete('vendor-management-grade-levels/{Id}', array('as' => 'api.vendor-management-grade-levels.delete', 'uses' => 'Api\DeleteDataApiController@vendorManagementGradeLevels' ));

    //vendor_performance_evaluation_module_parameters
    Route::get('vendor-performance-evaluation-module-parameters', array('as' => 'api.vendor-performance-evaluation-module-parameters', 'uses' => 'Api\GetDataApiController@vendorPerformanceEvaluationModuleParameters' ));
    Route::post('vendor-performance-evaluation-module-parameters', array('as' => 'api.vendor-performance-evaluation-module-parameters.create', 'uses' => 'Api\PostDataApiController@vendorPerformanceEvaluationModuleParameters' ));
    Route::put('vendor-performance-evaluation-module-parameters/{Id}', array('as' => 'api.vendor-performance-evaluation-module-parameters.update', 'uses' => 'Api\PutDataApiController@vendorPerformanceEvaluationModuleParameters' ));
    Route::delete('vendor-performance-evaluation-module-parameters/{Id}', array('as' => 'api.vendor-performance-evaluation-module-parameters.delete', 'uses' => 'Api\DeleteDataApiController@vendorPerformanceEvaluationModuleParameters' ));

    //vendor_performance_evaluation_project_removal_reasons
    Route::get('vendor-performance-evaluation-project-removal-reasons', array('as' => 'api.vendor-performance-evaluation-project-removal-reasons', 'uses' => 'Api\GetDataApiController@vendorPerformanceEvaluationProjectRemovalReasons' ));
    Route::post('vendor-performance-evaluation-project-removal-reasons', array('as' => 'api.vendor-performance-evaluation-project-removal-reasons.create', 'uses' => 'Api\PostDataApiController@vendorPerformanceEvaluationProjectRemovalReasons' ));
    Route::put('vendor-performance-evaluation-project-removal-reasons/{Id}', array('as' => 'api.vendor-performance-evaluation-project-removal-reasons.update', 'uses' => 'Api\PutDataApiController@vendorPerformanceEvaluationProjectRemovalReasons' ));
    Route::delete('vendor-performance-evaluation-project-removal-reasons/{Id}', array('as' => 'api.vendor-performance-evaluation-project-removal-reasons.delete', 'uses' => 'Api\DeleteDataApiController@vendorPerformanceEvaluationProjectRemovalReasons' ));

    //vendor_performance_evaluation_submission_reminder_settings
    Route::get('vendor-performance-evaluation-submission-reminder-settings', array('as' => 'api.vendor-performance-evaluation-submission-reminder-settings', 'uses' => 'Api\GetDataApiController@vendorPerformanceEvaluationSubmissionReminderSettings' ));
    Route::post('vendor-performance-evaluation-submission-reminder-settings', array('as' => 'api.vendor-performance-evaluation-submission-reminder-settings.create', 'uses' => 'Api\PostDataApiController@vendorPerformanceEvaluationSubmissionReminderSettings' ));
    Route::put('vendor-performance-evaluation-submission-reminder-settings/{Id}', array('as' => 'api.vendor-performance-evaluation-submission-reminder-settings.update', 'uses' => 'Api\PutDataApiController@vendorPerformanceEvaluationSubmissionReminderSettings' ));
    Route::delete('vendor-performance-evaluation-submission-reminder-settings/{Id}', array('as' => 'api.vendor-performance-evaluation-submission-reminder-settings.delete', 'uses' => 'Api\DeleteDataApiController@vendorPerformanceEvaluationSubmissionReminderSettings' ));

    //vendor_management_grades
    Route::get('vendor-management-grades', array('as' => 'api.vendor-management-grades', 'uses' => 'Api\GetDataApiController@vendorManagementGrades' ));
    Route::post('vendor-management-grades', array('as' => 'api.vendor-management-grades.create', 'uses' => 'Api\PostDataApiController@vendorManagementGrades' ));
    Route::put('vendor-management-grades/{Id}', array('as' => 'api.vendor-management-grades.update', 'uses' => 'Api\PutDataApiController@vendorManagementGrades' ));
    Route::delete('vendor-management-grades/{Id}', array('as' => 'api.vendor-management-grades.delete', 'uses' => 'Api\DeleteDataApiController@vendorManagementGrades' ));

    //vendor_management_user_permissions
    Route::get('vendor-management-user-permissions', array('as' => 'api.vendor-management-user-permissions', 'uses' => 'Api\GetDataApiController@vendorManagementUserPermissions' ));
    Route::post('vendor-management-user-permissions', array('as' => 'api.vendor-management-user-permissions.create', 'uses' => 'Api\PostDataApiController@vendorManagementUserPermissions' ));
    Route::put('vendor-management-user-permissions/{Id}', array('as' => 'api.vendor-management-user-permissions.update', 'uses' => 'Api\PutDataApiController@vendorManagementUserPermissions' ));
    Route::delete('vendor-management-user-permissions/{Id}', array('as' => 'api.vendor-management-user-permissions.delete', 'uses' => 'Api\DeleteDataApiController@vendorManagementUserPermissions' ));

    //vendor_performance_evaluation_company_form_evaluation_logs
    Route::get('vendor-performance-evaluation-company-form-evaluation-logs', array('as' => 'api.vendor-performance-evaluation-company-form-evaluation-logs', 'uses' => 'Api\GetDataApiController@vendorPerformanceEvaluationCompanyFormEvaluationLogs' ));
    Route::post('vendor-performance-evaluation-company-form-evaluation-logs', array('as' => 'api.vendor-performance-evaluation-company-form-evaluation-logs.create', 'uses' => 'Api\PostDataApiController@vendorPerformanceEvaluationCompanyFormEvaluationLogs' ));
    Route::put('vendor-performance-evaluation-company-form-evaluation-logs/{Id}', array('as' => 'api.vendor-performance-evaluation-company-form-evaluation-logs.update', 'uses' => 'Api\PutDataApiController@vendorPerformanceEvaluationCompanyFormEvaluationLogs' ));
    Route::delete('vendor-performance-evaluation-company-form-evaluation-logs/{Id}', array('as' => 'api.vendor-performance-evaluation-company-form-evaluation-logs.delete', 'uses' => 'Api\DeleteDataApiController@vendorPerformanceEvaluationCompanyFormEvaluationLogs' ));

    //vendor_performance_evaluation_company_forms
    Route::get('vendor-performance-evaluation-company-forms', array('as' => 'api.vendor-performance-evaluation-company-forms', 'uses' => 'Api\GetDataApiController@vendorPerformanceEvaluationCompanyForms' ));
    Route::post('vendor-performance-evaluation-company-forms', array('as' => 'api.vendor-performance-evaluation-company-forms.create', 'uses' => 'Api\PostDataApiController@vendorPerformanceEvaluationCompanyForms' ));
    Route::put('vendor-performance-evaluation-company-forms/{Id}', array('as' => 'api.vendor-performance-evaluation-company-forms.update', 'uses' => 'Api\PutDataApiController@vendorPerformanceEvaluationCompanyForms' ));
    Route::delete('vendor-performance-evaluation-company-forms/{Id}', array('as' => 'api.vendor-performance-evaluation-company-forms.delete', 'uses' => 'Api\DeleteDataApiController@vendorPerformanceEvaluationCompanyForms' ));

    //vendor_performance_evaluation_form_change_requests
    Route::get('vendor-performance-evaluation-form-change-requests', array('as' => 'api.vendor-performance-evaluation-form-change-requests', 'uses' => 'Api\GetDataApiController@vendorPerformanceEvaluationFormChangeRequests' ));
    Route::post('vendor-performance-evaluation-form-change-requests', array('as' => 'api.vendor-performance-evaluation-form-change-requests.create', 'uses' => 'Api\PostDataApiController@vendorPerformanceEvaluationFormChangeRequests' ));
    Route::put('vendor-performance-evaluation-form-change-requests/{Id}', array('as' => 'api.vendor-performance-evaluation-form-change-requests.update', 'uses' => 'Api\PutDataApiController@vendorPerformanceEvaluationFormChangeRequests' ));
    Route::delete('vendor-performance-evaluation-form-change-requests/{Id}', array('as' => 'api.vendor-performance-evaluation-form-change-requests.delete', 'uses' => 'Api\DeleteDataApiController@vendorPerformanceEvaluationFormChangeRequests' ));

    //vendor_performance_evaluation_processor_edit_details
    Route::get('vendor-performance-evaluation-processor-edit-details', array('as' => 'api.vendor-performance-evaluation-processor-edit-details', 'uses' => 'Api\GetDataApiController@vendorPerformanceEvaluationProcessorEditDetails' ));
    Route::post('vendor-performance-evaluation-processor-edit-details', array('as' => 'api.vendor-performance-evaluation-processor-edit-details.create', 'uses' => 'Api\PostDataApiController@vendorPerformanceEvaluationProcessorEditDetails' ));
    Route::put('vendor-performance-evaluation-processor-edit-details/{Id}', array('as' => 'api.vendor-performance-evaluation-processor-edit-details.update', 'uses' => 'Api\PutDataApiController@vendorPerformanceEvaluationProcessorEditDetails' ));
    Route::delete('vendor-performance-evaluation-processor-edit-details/{Id}', array('as' => 'api.vendor-performance-evaluation-processor-edit-details.delete', 'uses' => 'Api\DeleteDataApiController@vendorPerformanceEvaluationProcessorEditDetails' ));

    //vendor_performance_evaluation_processor_edit_logs
    Route::get('vendor-performance-evaluation-processor-edit-logs', array('as' => 'api.vendor-performance-evaluation-processor-edit-logs', 'uses' => 'Api\GetDataApiController@vendorPerformanceEvaluationProcessorEditLogs' ));
    Route::post('vendor-performance-evaluation-processor-edit-logs', array('as' => 'api.vendor-performance-evaluation-processor-edit-logs.create', 'uses' => 'Api\PostDataApiController@vendorPerformanceEvaluationProcessorEditLogs' ));
    Route::put('vendor-performance-evaluation-processor-edit-logs/{Id}', array('as' => 'api.vendor-performance-evaluation-processor-edit-logs.update', 'uses' => 'Api\PutDataApiController@vendorPerformanceEvaluationProcessorEditLogs' ));
    Route::delete('vendor-performance-evaluation-processor-edit-logs/{Id}', array('as' => 'api.vendor-performance-evaluation-processor-edit-logs.delete', 'uses' => 'Api\DeleteDataApiController@vendorPerformanceEvaluationProcessorEditLogs' ));

    //vendor_performance_evaluation_removal_requests
    Route::get('vendor-performance-evaluation-removal-requests', array('as' => 'api.vendor-performance-evaluation-removal-requests', 'uses' => 'Api\GetDataApiController@vendorPerformanceEvaluationRemovalRequests' ));
    Route::post('vendor-performance-evaluation-removal-requests', array('as' => 'api.vendor-performance-evaluation-removal-requests.create', 'uses' => 'Api\PostDataApiController@vendorPerformanceEvaluationRemovalRequests' ));
    Route::put('vendor-performance-evaluation-removal-requests/{Id}', array('as' => 'api.vendor-performance-evaluation-removal-requests.update', 'uses' => 'Api\PutDataApiController@vendorPerformanceEvaluationRemovalRequests' ));
    Route::delete('vendor-performance-evaluation-removal-requests/{Id}', array('as' => 'api.vendor-performance-evaluation-removal-requests.delete', 'uses' => 'Api\DeleteDataApiController@vendorPerformanceEvaluationRemovalRequests' ));

    //vendor_performance_evaluation_setups
    Route::get('vendor-performance-evaluation-setups', array('as' => 'api.vendor-performance-evaluation-setups', 'uses' => 'Api\GetDataApiController@vendorPerformanceEvaluationSetups' ));
    Route::post('vendor-performance-evaluation-setups', array('as' => 'api.vendor-performance-evaluation-setups.create', 'uses' => 'Api\PostDataApiController@vendorPerformanceEvaluationSetups' ));
    Route::put('vendor-performance-evaluation-setups/{Id}', array('as' => 'api.vendor-performance-evaluation-setups.update', 'uses' => 'Api\PutDataApiController@vendorPerformanceEvaluationSetups' ));
    Route::delete('vendor-performance-evaluation-setups/{Id}', array('as' => 'api.vendor-performance-evaluation-setups.delete', 'uses' => 'Api\DeleteDataApiController@vendorPerformanceEvaluationSetups' ));

    //vendor_performance_evaluation_template_forms
    Route::get('vendor-performance-evaluation-template-forms', array('as' => 'api.vendor-performance-evaluation-template-forms', 'uses' => 'Api\GetDataApiController@vendorPerformanceEvaluationTemplateForms' ));
    Route::post('vendor-performance-evaluation-template-forms', array('as' => 'api.vendor-performance-evaluation-template-forms.create', 'uses' => 'Api\PostDataApiController@vendorPerformanceEvaluationTemplateForms' ));
    Route::put('vendor-performance-evaluation-template-forms/{Id}', array('as' => 'api.vendor-performance-evaluation-template-forms.update', 'uses' => 'Api\PutDataApiController@vendorPerformanceEvaluationTemplateForms' ));
    Route::delete('vendor-performance-evaluation-template-forms/{Id}', array('as' => 'api.vendor-performance-evaluation-template-forms.delete', 'uses' => 'Api\DeleteDataApiController@vendorPerformanceEvaluationTemplateForms' ));

    //vendor_profile_module_parameters
    Route::get('vendor-profile-module-parameters', array('as' => 'api.vendor-profile-module-parameters', 'uses' => 'Api\GetDataApiController@vendorProfileModuleParameters' ));
    Route::post('vendor-profile-module-parameters', array('as' => 'api.vendor-profile-module-parameters.create', 'uses' => 'Api\PostDataApiController@vendorProfileModuleParameters' ));
    Route::put('vendor-profile-module-parameters/{Id}', array('as' => 'api.vendor-profile-module-parameters.update', 'uses' => 'Api\PutDataApiController@vendorProfileModuleParameters' ));
    Route::delete('vendor-profile-module-parameters/{Id}', array('as' => 'api.vendor-profile-module-parameters.delete', 'uses' => 'Api\DeleteDataApiController@vendorProfileModuleParameters' ));

    //vendor_profiles
    Route::get('vendor-profiles', array('as' => 'api.vendor-profiles', 'uses' => 'Api\GetDataApiController@vendorProfiles' ));
    Route::post('vendor-profiles', array('as' => 'api.vendor-profiles.create', 'uses' => 'Api\PostDataApiController@vendorProfiles' ));
    Route::put('vendor-profiles/{Id}', array('as' => 'api.vendor-profiles.update', 'uses' => 'Api\PutDataApiController@vendorProfiles' ));
    Route::delete('vendor-profiles/{Id}', array('as' => 'api.vendor-profiles.delete', 'uses' => 'Api\DeleteDataApiController@vendorProfiles' ));

    //vendor_registration_and_prequalification_module_parameters
    Route::get('vendor-registration-and-prequalification-module-parameters', array('as' => 'api.vendor-registration-and-prequalification-module-parameters', 'uses' => 'Api\GetDataApiController@vendorRegistrationAndPrequalificationModuleParameters' ));
    Route::post('vendor-registration-and-prequalification-module-parameters', array('as' => 'api.vendor-registration-and-prequalification-module-parameters.create', 'uses' => 'Api\PostDataApiController@vendorRegistrationAndPrequalificationModuleParameters' ));
    Route::put('vendor-registration-and-prequalification-module-parameters/{Id}', array('as' => 'api.vendor-registration-and-prequalification-module-parameters.update', 'uses' => 'Api\PutDataApiController@vendorRegistrationAndPrequalificationModuleParameters' ));
    Route::delete('vendor-registration-and-prequalification-module-parameters/{Id}', array('as' => 'api.vendor-registration-and-prequalification-module-parameters.delete', 'uses' => 'Api\DeleteDataApiController@vendorRegistrationAndPrequalificationModuleParameters' ));

    //vendor_registration_form_template_mappings
    Route::get('vendor-registration-form-template-mappings', array('as' => 'api.vendor-registration-form-template-mappings', 'uses' => 'Api\GetDataApiController@vendorRegistrationFormTemplateMappings' ));
    Route::post('vendor-registration-form-template-mappings', array('as' => 'api.vendor-registration-form-template-mappings.create', 'uses' => 'Api\PostDataApiController@vendorRegistrationFormTemplateMappings' ));
    Route::put('vendor-registration-form-template-mappings/{Id}', array('as' => 'api.vendor-registration-form-template-mappings.update', 'uses' => 'Api\PutDataApiController@vendorRegistrationFormTemplateMappings' ));
    Route::delete('vendor-registration-form-template-mappings/{Id}', array('as' => 'api.vendor-registration-form-template-mappings.delete', 'uses' => 'Api\DeleteDataApiController@vendorRegistrationFormTemplateMappings' ));

    //vendor_registration_sections
    Route::get('vendor-registration-sections', array('as' => 'api.vendor-registration-sections', 'uses' => 'Api\GetDataApiController@vendorRegistrationSections' ));
    Route::post('vendor-registration-sections', array('as' => 'api.vendor-registration-sections.create', 'uses' => 'Api\PostDataApiController@vendorRegistrationSections' ));
    Route::put('vendor-registration-sections/{Id}', array('as' => 'api.vendor-registration-sections.update', 'uses' => 'Api\PutDataApiController@vendorRegistrationSections' ));
    Route::delete('vendor-registration-sections/{Id}', array('as' => 'api.vendor-registration-sections.delete', 'uses' => 'Api\DeleteDataApiController@vendorRegistrationSections' ));

    //vendor_registration_submission_logs
    Route::get('vendor-registration-submission-logs', array('as' => 'api.vendor-registration-submission-logs', 'uses' => 'Api\GetDataApiController@vendorRegistrationSubmissionLogs' ));
    Route::post('vendor-registration-submission-logs', array('as' => 'api.vendor-registration-submission-logs.create', 'uses' => 'Api\PostDataApiController@vendorRegistrationSubmissionLogs' ));
    Route::put('vendor-registration-submission-logs/{Id}', array('as' => 'api.vendor-registration-submission-logs.update', 'uses' => 'Api\PutDataApiController@vendorRegistrationSubmissionLogs' ));
    Route::delete('vendor-registration-submission-logs/{Id}', array('as' => 'api.vendor-registration-submission-logs.delete', 'uses' => 'Api\DeleteDataApiController@vendorRegistrationSubmissionLogs' ));

    //vendor_performance_evaluations
    Route::get('vendor-performance-evaluations', array('as' => 'api.vendor-performance-evaluations', 'uses' => 'Api\GetDataApiController@vendorPerformanceEvaluations' ));
    Route::post('vendor-performance-evaluations', array('as' => 'api.vendor-performance-evaluations.create', 'uses' => 'Api\PostDataApiController@vendorPerformanceEvaluations' ));
    Route::put('vendor-performance-evaluations/{Id}', array('as' => 'api.vendor-performance-evaluations.update', 'uses' => 'Api\PutDataApiController@vendorPerformanceEvaluations' ));
    Route::delete('vendor-performance-evaluations/{Id}', array('as' => 'api.vendor-performance-evaluations.delete', 'uses' => 'Api\DeleteDataApiController@vendorPerformanceEvaluations' ));

    //vendor_performance_evaluators
    Route::get('vendor-performance-evaluators', array('as' => 'api.vendor-performance-evaluators', 'uses' => 'Api\GetDataApiController@vendorPerformanceEvaluators' ));
    Route::post('vendor-performance-evaluators', array('as' => 'api.vendor-performance-evaluators.create', 'uses' => 'Api\PostDataApiController@vendorPerformanceEvaluators' ));
    Route::put('vendor-performance-evaluators/{Id}', array('as' => 'api.vendor-performance-evaluators.update', 'uses' => 'Api\PutDataApiController@vendorPerformanceEvaluators' ));
    Route::delete('vendor-performance-evaluators/{Id}', array('as' => 'api.vendor-performance-evaluators.delete', 'uses' => 'Api\DeleteDataApiController@vendorPerformanceEvaluators' ));

    //vendor_pre_qualification_setups
    Route::get('vendor-pre-qualification-setups', array('as' => 'api.vendor-pre-qualification-setups', 'uses' => 'Api\GetDataApiController@vendorPreQualificationSetups' ));
    Route::post('vendor-pre-qualification-setups', array('as' => 'api.vendor-pre-qualification-setups.create', 'uses' => 'Api\PostDataApiController@vendorPreQualificationSetups' ));
    Route::put('vendor-pre-qualification-setups/{Id}', array('as' => 'api.vendor-pre-qualification-setups.update', 'uses' => 'Api\PutDataApiController@vendorPreQualificationSetups' ));
    Route::delete('vendor-pre-qualification-setups/{Id}', array('as' => 'api.vendor-pre-qualification-setups.delete', 'uses' => 'Api\DeleteDataApiController@vendorPreQualificationSetups' ));

    //vendor_pre_qualification_template_forms
    Route::get('vendor-pre-qualification-template-forms', array('as' => 'api.vendor-pre-qualification-template-forms', 'uses' => 'Api\GetDataApiController@vendorPreQualificationTemplateForms' ));
    Route::post('vendor-pre-qualification-template-forms', array('as' => 'api.vendor-pre-qualification-template-forms.create', 'uses' => 'Api\PostDataApiController@vendorPreQualificationTemplateForms' ));
    Route::put('vendor-pre-qualification-template-forms/{Id}', array('as' => 'api.vendor-pre-qualification-template-forms.update', 'uses' => 'Api\PutDataApiController@vendorPreQualificationTemplateForms' ));
    Route::delete('vendor-pre-qualification-template-forms/{Id}', array('as' => 'api.vendor-pre-qualification-template-forms.delete', 'uses' => 'Api\DeleteDataApiController@vendorPreQualificationTemplateForms' ));

    //vendor_pre_qualification_vendor_group_grades
    Route::get('vendor-pre-qualification-vendor-group-grades', array('as' => 'api.vendor-pre-qualification-vendor-group-grades', 'uses' => 'Api\GetDataApiController@vendorPreQualificationVendorGroupGrades' ));
    Route::post('vendor-pre-qualification-vendor-group-grades', array('as' => 'api.vendor-pre-qualification-vendor-group-grades.create', 'uses' => 'Api\PostDataApiController@vendorPreQualificationVendorGroupGrades' ));
    Route::put('vendor-pre-qualification-vendor-group-grades/{Id}', array('as' => 'api.vendor-pre-qualification-vendor-group-grades.update', 'uses' => 'Api\PutDataApiController@vendorPreQualificationVendorGroupGrades' ));
    Route::delete('vendor-pre-qualification-vendor-group-grades/{Id}', array('as' => 'api.vendor-pre-qualification-vendor-group-grades.delete', 'uses' => 'Api\DeleteDataApiController@vendorPreQualificationVendorGroupGrades' ));

    //vendor_pre_qualifications
    Route::get('vendor-pre-qualifications', array('as' => 'api.vendor-pre-qualifications', 'uses' => 'Api\GetDataApiController@vendorPreQualifications' ));
    Route::post('vendor-pre-qualifications', array('as' => 'api.vendor-pre-qualifications.create', 'uses' => 'Api\PostDataApiController@vendorPreQualifications' ));
    Route::put('vendor-pre-qualifications/{Id}', array('as' => 'api.vendor-pre-qualifications.update', 'uses' => 'Api\PutDataApiController@vendorPreQualifications' ));
    Route::delete('vendor-pre-qualifications/{Id}', array('as' => 'api.vendor-pre-qualifications.delete', 'uses' => 'Api\DeleteDataApiController@vendorPreQualifications' ));

    //vendor_profile_remarks
    Route::get('vendor-profile-remarks', array('as' => 'api.vendor-profile-remarks', 'uses' => 'Api\GetDataApiController@vendorProfileRemarks' ));
    Route::post('vendor-profile-remarks', array('as' => 'api.vendor-profile-remarks.create', 'uses' => 'Api\PostDataApiController@vendorProfileRemarks' ));
    Route::put('vendor-profile-remarks/{Id}', array('as' => 'api.vendor-profile-remarks.update', 'uses' => 'Api\PutDataApiController@vendorProfileRemarks' ));
    Route::delete('vendor-profile-remarks/{Id}', array('as' => 'api.vendor-profile-remarks.delete', 'uses' => 'Api\DeleteDataApiController@vendorProfileRemarks' ));

    //vendor_registration_payments
    Route::get('vendor-registration-payments', array('as' => 'api.vendor-registration-payments', 'uses' => 'Api\GetDataApiController@vendorRegistrationPayments' ));
    Route::post('vendor-registration-payments', array('as' => 'api.vendor-registration-payments.create', 'uses' => 'Api\PostDataApiController@vendorRegistrationPayments' ));
    Route::put('vendor-registration-payments/{Id}', array('as' => 'api.vendor-registration-payments.update', 'uses' => 'Api\PutDataApiController@vendorRegistrationPayments' ));
    Route::delete('vendor-registration-payments/{Id}', array('as' => 'api.vendor-registration-payments.delete', 'uses' => 'Api\DeleteDataApiController@vendorRegistrationPayments' ));

    //vendor_registration_processors
    Route::get('vendor-registration-processors', array('as' => 'api.vendor-registration-processors', 'uses' => 'Api\GetDataApiController@vendorRegistrationProcessors' ));
    Route::post('vendor-registration-processors', array('as' => 'api.vendor-registration-processors.create', 'uses' => 'Api\PostDataApiController@vendorRegistrationProcessors' ));
    Route::put('vendor-registration-processors/{Id}', array('as' => 'api.vendor-registration-processors.update', 'uses' => 'Api\PutDataApiController@vendorRegistrationProcessors' ));
    Route::delete('vendor-registration-processors/{Id}', array('as' => 'api.vendor-registration-processors.delete', 'uses' => 'Api\DeleteDataApiController@vendorRegistrationProcessors' ));

    //weather_record_reports
    Route::get('weather-record-reports', array('as' => 'api.weather-record-reports', 'uses' => 'Api\GetDataApiController@weatherRecordReports' ));
    Route::post('weather-record-reports', array('as' => 'api.weather-record-reports.create', 'uses' => 'Api\PostDataApiController@weatherRecordReports' ));
    Route::put('weather-record-reports/{Id}', array('as' => 'api.weather-record-reports.update', 'uses' => 'Api\PutDataApiController@weatherRecordReports' ));
    Route::delete('weather-record-reports/{Id}', array('as' => 'api.weather-record-reports.delete', 'uses' => 'Api\DeleteDataApiController@weatherRecordReports' ));

    //vendor_type_change_logs
    Route::get('vendor-type-change-logs', array('as' => 'api.vendor-type-change-logs', 'uses' => 'Api\GetDataApiController@vendorTypeChangeLogs' ));
    Route::post('vendor-type-change-logs', array('as' => 'api.vendor-type-change-logs.create', 'uses' => 'Api\PostDataApiController@vendorTypeChangeLogs' ));
    Route::put('vendor-type-change-logs/{Id}', array('as' => 'api.vendor-type-change-logs.update', 'uses' => 'Api\PutDataApiController@vendorTypeChangeLogs' ));
    Route::delete('vendor-type-change-logs/{Id}', array('as' => 'api.vendor-type-change-logs.delete', 'uses' => 'Api\DeleteDataApiController@vendorTypeChangeLogs' ));

    //weighted_node_scores
    Route::get('weighted-node-scores', array('as' => 'api.weighted-node-scores', 'uses' => 'Api\GetDataApiController@weightedNodeScores' ));
    Route::post('weighted-node-scores', array('as' => 'api.weighted-node-scores.create', 'uses' => 'Api\PostDataApiController@weightedNodeScores' ));
    Route::put('weighted-node-scores/{Id}', array('as' => 'api.weighted-node-scores.update', 'uses' => 'Api\PutDataApiController@weightedNodeScores' ));
    Route::delete('weighted-node-scores/{Id}', array('as' => 'api.weighted-node-scores.delete', 'uses' => 'Api\DeleteDataApiController@weightedNodeScores' ));

    //work_categories
    Route::get('work-categories', array('as' => 'api.work-categories', 'uses' => 'Api\GetDataApiController@workCategories' ));
    Route::post('work-categories', array('as' => 'api.work-categories.create', 'uses' => 'Api\PostDataApiController@workCategories' ));
    Route::put('work-categories/{Id}', array('as' => 'api.work-categories.update', 'uses' => 'Api\PutDataApiController@workCategories' ));
    Route::delete('work-categories/{Id}', array('as' => 'api.work-categories.delete', 'uses' => 'Api\DeleteDataApiController@workCategories' ));

    //work_subcategories
    Route::get('work-subcategories', array('as' => 'api.work-subcategories', 'uses' => 'Api\GetDataApiController@workSubcategories' ));
    Route::post('work-subcategories', array('as' => 'api.work-subcategories.create', 'uses' => 'Api\PostDataApiController@workSubcategories' ));
    Route::put('work-subcategories/{Id}', array('as' => 'api.work-subcategories.update', 'uses' => 'Api\PutDataApiController@workSubcategories' ));
    Route::delete('work-subcategories/{Id}', array('as' => 'api.work-subcategories.delete', 'uses' => 'Api\DeleteDataApiController@workSubcategories' ));

    //weathers
    Route::get('weathers', array('as' => 'api.weathers', 'uses' => 'Api\GetDataApiController@weathers' ));
    Route::post('weathers', array('as' => 'api.weathers.create', 'uses' => 'Api\PostDataApiController@weathers' ));
    Route::put('weathers/{Id}', array('as' => 'api.weathers.update', 'uses' => 'Api\PutDataApiController@weathers' ));
    Route::delete('weathers/{Id}', array('as' => 'api.weathers.delete', 'uses' => 'Api\DeleteDataApiController@weathers' ));

    //vendor_work_categories
    Route::get('vendor-work-categories', array('as' => 'api.vendor-work-categories', 'uses' => 'Api\GetDataApiController@vendorWorkCategories' ));
    Route::post('vendor-work-categories', array('as' => 'api.vendor-work-categories.create', 'uses' => 'Api\PostDataApiController@vendorWorkCategories' ));
    Route::put('vendor-work-categories/{Id}', array('as' => 'api.vendor-work-categories.update', 'uses' => 'Api\PutDataApiController@vendorWorkCategories' ));
    Route::delete('vendor-work-categories/{Id}', array('as' => 'api.vendor-work-categories.delete', 'uses' => 'Api\DeleteDataApiController@vendorWorkCategories' ));

    //vendor_work_subcategories
    Route::get('vendor-work-subcategories', array('as' => 'api.vendor-work-subcategories', 'uses' => 'Api\GetDataApiController@vendorWorkSubcategories' ));
    Route::post('vendor-work-subcategories', array('as' => 'api.vendor-work-subcategories.create', 'uses' => 'Api\PostDataApiController@vendorWorkSubcategories' ));
    Route::put('vendor-work-subcategories/{Id}', array('as' => 'api.vendor-work-subcategories.update', 'uses' => 'Api\PutDataApiController@vendorWorkSubcategories' ));
    Route::delete('vendor-work-subcategories/{Id}', array('as' => 'api.vendor-work-subcategories.delete', 'uses' => 'Api\DeleteDataApiController@vendorWorkSubcategories' ));

    //weighted_nodes
    Route::get('weighted-nodes', array('as' => 'api.weighted-nodes', 'uses' => 'Api\GetDataApiController@weightedNodes' ));
    Route::post('weighted-nodes', array('as' => 'api.weighted-nodes.create', 'uses' => 'Api\PostDataApiController@weightedNodes' ));
    Route::put('weighted-nodes/{Id}', array('as' => 'api.weighted-nodes.update', 'uses' => 'Api\PutDataApiController@weightedNodes' ));
    Route::delete('weighted-nodes/{Id}', array('as' => 'api.weighted-nodes.delete', 'uses' => 'Api\DeleteDataApiController@weightedNodes' ));

    //vendors
    Route::get('vendors', array('as' => 'api.vendors', 'uses' => 'Api\GetDataApiController@vendors' ));
    Route::post('vendors', array('as' => 'api.vendors.create', 'uses' => 'Api\PostDataApiController@vendors' ));
    Route::put('vendors/{Id}', array('as' => 'api.vendors.update', 'uses' => 'Api\PutDataApiController@vendors' ));
    Route::delete('vendors/{Id}', array('as' => 'api.vendors.delete', 'uses' => 'Api\DeleteDataApiController@vendors' ));

    //vendor_work_category_work_category
    Route::get('vendor-work-category-work-category', array('as' => 'api.vendor-work-category-work-category', 'uses' => 'Api\GetDataApiController@vendorWorkCategoryWorkCategory' ));
    Route::post('vendor-work-category-work-category', array('as' => 'api.vendor-work-category-work-category.create', 'uses' => 'Api\PostDataApiController@vendorWorkCategoryWorkCategory' ));
    Route::put('vendor-work-category-work-category/{Id}', array('as' => 'api.vendor-work-category-work-category.update', 'uses' => 'Api\PutDataApiController@vendorWorkCategoryWorkCategory' ));
    Route::delete('vendor-work-category-work-category/{Id}', array('as' => 'api.vendor-work-category-work-category.delete', 'uses' => 'Api\DeleteDataApiController@vendorWorkCategoryWorkCategory' ));

    //verifiers
    Route::get('verifiers', array('as' => 'api.verifiers', 'uses' => 'Api\GetDataApiController@verifiers' ));
    Route::post('verifiers', array('as' => 'api.verifiers.create', 'uses' => 'Api\PostDataApiController@verifiers' ));
    Route::put('verifiers/{Id}', array('as' => 'api.verifiers.update', 'uses' => 'Api\PutDataApiController@verifiers' ));
    Route::delete('verifiers/{Id}', array('as' => 'api.verifiers.delete', 'uses' => 'Api\DeleteDataApiController@verifiers' ));

    //weather_records
    Route::get('weather-records', array('as' => 'api.weather-records', 'uses' => 'Api\GetDataApiController@weatherRecords' ));
    Route::post('weather-records', array('as' => 'api.weather-records.create', 'uses' => 'Api\PostDataApiController@weatherRecords' ));
    Route::put('weather-records/{Id}', array('as' => 'api.weather-records.update', 'uses' => 'Api\PutDataApiController@weatherRecords' ));
    Route::delete('weather-records/{Id}', array('as' => 'api.weather-records.delete', 'uses' => 'Api\DeleteDataApiController@weatherRecords' ));

    //access_log
    Route::get('access-log', array('as' => 'api.access-log', 'uses' => 'Api\GetDataApiController@accessLog' ));
    Route::post('access-log', array('as' => 'api.access-log.create', 'uses' => 'Api\PostDataApiController@accessLog' ));
    Route::put('access-log/{Id}', array('as' => 'api.access-log.update', 'uses' => 'Api\PutDataApiController@accessLog' ));
    Route::delete('access-log/{Id}', array('as' => 'api.access-log.delete', 'uses' => 'Api\DeleteDataApiController@accessLog' ));

    //projects
    Route::get('projects', array('as' => 'api.projects', 'uses' => 'Api\GetDataApiController@projects' ));
    Route::post('projects', array('as' => 'api.projects.create', 'uses' => 'Api\PostDataApiController@projects' ));
    Route::put('projects/{Id}', array('as' => 'api.projects.update', 'uses' => 'Api\PutDataApiController@projects' ));
    Route::delete('projects/{Id}', array('as' => 'api.projects.delete', 'uses' => 'Api\DeleteDataApiController@projects' ));

    //accounting_report_export_log_details
    Route::get('accounting-report-export-log-details', array('as' => 'api.accounting-report-export-log-details', 'uses' => 'Api\GetDataApiController@accountingReportExportLogDetails' ));
    Route::post('accounting-report-export-log-details', array('as' => 'api.accounting-report-export-log-details.create', 'uses' => 'Api\PostDataApiController@accountingReportExportLogDetails' ));
    Route::put('accounting-report-export-log-details/{Id}', array('as' => 'api.accounting-report-export-log-details.update', 'uses' => 'Api\PutDataApiController@accountingReportExportLogDetails' ));
    Route::delete('accounting-report-export-log-details/{Id}', array('as' => 'api.accounting-report-export-log-details.delete', 'uses' => 'Api\DeleteDataApiController@accountingReportExportLogDetails' ));

    //interim_claims
    Route::get('interim-claims', array('as' => 'api.interim-claims', 'uses' => 'Api\GetDataApiController@interimClaims' ));
    Route::post('interim-claims', array('as' => 'api.interim-claims.create', 'uses' => 'Api\PostDataApiController@interimClaims' ));
    Route::put('interim-claims/{Id}', array('as' => 'api.interim-claims.update', 'uses' => 'Api\PutDataApiController@interimClaims' ));
    Route::delete('interim-claims/{Id}', array('as' => 'api.interim-claims.delete', 'uses' => 'Api\DeleteDataApiController@interimClaims' ));

    //architect_instructions
    Route::get('architect-instructions', array('as' => 'api.architect-instructions', 'uses' => 'Api\GetDataApiController@architectInstructions' ));
    Route::post('architect-instructions', array('as' => 'api.architect-instructions.create', 'uses' => 'Api\PostDataApiController@architectInstructions' ));
    Route::put('architect-instructions/{Id}', array('as' => 'api.architect-instructions.update', 'uses' => 'Api\PutDataApiController@architectInstructions' ));
    Route::delete('architect-instructions/{Id}', array('as' => 'api.architect-instructions.delete', 'uses' => 'Api\DeleteDataApiController@architectInstructions' ));

    //ae_second_level_messages
    Route::get('ae-second-level-messages', array('as' => 'api.ae-second-level-messages', 'uses' => 'Api\GetDataApiController@aeSecondLevelMessages' ));
    Route::post('ae-second-level-messages', array('as' => 'api.ae-second-level-messages.create', 'uses' => 'Api\PostDataApiController@aeSecondLevelMessages' ));
    Route::put('ae-second-level-messages/{Id}', array('as' => 'api.ae-second-level-messages.update', 'uses' => 'Api\PutDataApiController@aeSecondLevelMessages' ));
    Route::delete('ae-second-level-messages/{Id}', array('as' => 'api.ae-second-level-messages.delete', 'uses' => 'Api\DeleteDataApiController@aeSecondLevelMessages' ));

    //companies
    Route::get('companies', array('as' => 'api.companies', 'uses' => 'Api\GetDataApiController@companies' ));
    Route::post('companies', array('as' => 'api.companies.create', 'uses' => 'Api\PostDataApiController@companies' ));
    Route::put('companies/{Id}', array('as' => 'api.companies.update', 'uses' => 'Api\PutDataApiController@companies' ));
    Route::delete('companies/{Id}', array('as' => 'api.companies.delete', 'uses' => 'Api\DeleteDataApiController@companies' ));

    //countries
    Route::get('countries', array('as' => 'api.countries', 'uses' => 'Api\GetDataApiController@countries' ));
    Route::post('countries', array('as' => 'api.countries.create', 'uses' => 'Api\PostDataApiController@countries' ));
    Route::put('countries/{Id}', array('as' => 'api.countries.update', 'uses' => 'Api\PutDataApiController@countries' ));
    Route::delete('countries/{Id}', array('as' => 'api.countries.delete', 'uses' => 'Api\DeleteDataApiController@countries' ));

    //states
    Route::get('states', array('as' => 'api.states', 'uses' => 'Api\GetDataApiController@states' ));
    Route::post('states', array('as' => 'api.states.create', 'uses' => 'Api\PostDataApiController@states' ));
    Route::put('states/{Id}', array('as' => 'api.states.update', 'uses' => 'Api\PutDataApiController@states' ));
    Route::delete('states/{Id}', array('as' => 'api.states.delete', 'uses' => 'Api\DeleteDataApiController@states' ));

    //clause_items
    Route::get('clause-items', array('as' => 'api.clause-items', 'uses' => 'Api\GetDataApiController@clauseItems' ));
    Route::post('clause-items', array('as' => 'api.clause-items.create', 'uses' => 'Api\PostDataApiController@clauseItems' ));
    Route::put('clause-items/{Id}', array('as' => 'api.clause-items.update', 'uses' => 'Api\PutDataApiController@clauseItems' ));
    Route::delete('clause-items/{Id}', array('as' => 'api.clause-items.delete', 'uses' => 'Api\DeleteDataApiController@clauseItems' ));

    //consultant_management_attachment_settings
    Route::get('consultant-management-attachment-settings', array('as' => 'api.consultant-management-attachment-settings', 'uses' => 'Api\GetDataApiController@consultantManagementAttachmentSettings' ));
    Route::post('consultant-management-attachment-settings', array('as' => 'api.consultant-management-attachment-settings.create', 'uses' => 'Api\PostDataApiController@consultantManagementAttachmentSettings' ));
    Route::put('consultant-management-attachment-settings/{Id}', array('as' => 'api.consultant-management-attachment-settings.update', 'uses' => 'Api\PutDataApiController@consultantManagementAttachmentSettings' ));
    Route::delete('consultant-management-attachment-settings/{Id}', array('as' => 'api.consultant-management-attachment-settings.delete', 'uses' => 'Api\DeleteDataApiController@consultantManagementAttachmentSettings' ));

    //consultant_management_open_rfp
    Route::get('consultant-management-open-rfp', array('as' => 'api.consultant-management-open-rfp', 'uses' => 'Api\GetDataApiController@consultantManagementOpenRfp' ));
    Route::post('consultant-management-open-rfp', array('as' => 'api.consultant-management-open-rfp.create', 'uses' => 'Api\PostDataApiController@consultantManagementOpenRfp' ));
    Route::put('consultant-management-open-rfp/{Id}', array('as' => 'api.consultant-management-open-rfp.update', 'uses' => 'Api\PutDataApiController@consultantManagementOpenRfp' ));
    Route::delete('consultant-management-open-rfp/{Id}', array('as' => 'api.consultant-management-open-rfp.delete', 'uses' => 'Api\DeleteDataApiController@consultantManagementOpenRfp' ));

    //consultant_management_approval_document_section_a
    Route::get('consultant-management-approval-document-section-a', array('as' => 'api.consultant-management-approval-document-section-a', 'uses' => 'Api\GetDataApiController@consultantManagementApprovalDocumentSectionA' ));
    Route::post('consultant-management-approval-document-section-a', array('as' => 'api.consultant-management-approval-document-section-a.create', 'uses' => 'Api\PostDataApiController@consultantManagementApprovalDocumentSectionA' ));
    Route::put('consultant-management-approval-document-section-a/{Id}', array('as' => 'api.consultant-management-approval-document-section-a.update', 'uses' => 'Api\PutDataApiController@consultantManagementApprovalDocumentSectionA' ));
    Route::delete('consultant-management-approval-document-section-a/{Id}', array('as' => 'api.consultant-management-approval-document-section-a.delete', 'uses' => 'Api\DeleteDataApiController@consultantManagementApprovalDocumentSectionA' ));

    //vendor_registrations
    Route::get('vendor-registrations', array('as' => 'api.vendor-registrations', 'uses' => 'Api\GetDataApiController@vendorRegistrations' ));
    Route::post('vendor-registrations', array('as' => 'api.vendor-registrations.create', 'uses' => 'Api\PostDataApiController@vendorRegistrations' ));
    Route::put('vendor-registrations/{Id}', array('as' => 'api.vendor-registrations.update', 'uses' => 'Api\PutDataApiController@vendorRegistrations' ));
    Route::delete('vendor-registrations/{Id}', array('as' => 'api.vendor-registrations.delete', 'uses' => 'Api\DeleteDataApiController@vendorRegistrations' ));

    //company_personnel
    Route::get('company-personnel', array('as' => 'api.company-personnel', 'uses' => 'Api\GetDataApiController@companyPersonnel' ));
    Route::post('company-personnel', array('as' => 'api.company-personnel.create', 'uses' => 'Api\PostDataApiController@companyPersonnel' ));
    Route::put('company-personnel/{Id}', array('as' => 'api.company-personnel.update', 'uses' => 'Api\PutDataApiController@companyPersonnel' ));
    Route::delete('company-personnel/{Id}', array('as' => 'api.company-personnel.delete', 'uses' => 'Api\DeleteDataApiController@companyPersonnel' ));

    //consultant_management_company_roles
    Route::get('consultant-management-company-roles', array('as' => 'api.consultant-management-company-roles', 'uses' => 'Api\GetDataApiController@consultantManagementCompanyRoles' ));
    Route::post('consultant-management-company-roles', array('as' => 'api.consultant-management-company-roles.create', 'uses' => 'Api\PostDataApiController@consultantManagementCompanyRoles' ));
    Route::put('consultant-management-company-roles/{Id}', array('as' => 'api.consultant-management-company-roles.update', 'uses' => 'Api\PutDataApiController@consultantManagementCompanyRoles' ));
    Route::delete('consultant-management-company-roles/{Id}', array('as' => 'api.consultant-management-company-roles.delete', 'uses' => 'Api\DeleteDataApiController@consultantManagementCompanyRoles' ));

    //product_types
    Route::get('product-types', array('as' => 'api.product-types', 'uses' => 'Api\GetDataApiController@productTypes' ));
    Route::post('product-types', array('as' => 'api.product-types.create', 'uses' => 'Api\PostDataApiController@productTypes' ));
    Route::put('product-types/{Id}', array('as' => 'api.product-types.update', 'uses' => 'Api\PutDataApiController@productTypes' ));
    Route::delete('product-types/{Id}', array('as' => 'api.product-types.delete', 'uses' => 'Api\DeleteDataApiController@productTypes' ));

    //development_types
    Route::get('development-types', array('as' => 'api.development-types', 'uses' => 'Api\GetDataApiController@developmentTypes' ));
    Route::post('development-types', array('as' => 'api.development-types.create', 'uses' => 'Api\PostDataApiController@developmentTypes' ));
    Route::put('development-types/{Id}', array('as' => 'api.development-types.update', 'uses' => 'Api\PutDataApiController@developmentTypes' ));
    Route::delete('development-types/{Id}', array('as' => 'api.development-types.delete', 'uses' => 'Api\DeleteDataApiController@developmentTypes' ));

    //contractor_work_category
    Route::get('contractor-work-category', array('as' => 'api.contractor-work-category', 'uses' => 'Api\GetDataApiController@contractorWorkCategory' ));
    Route::post('contractor-work-category', array('as' => 'api.contractor-work-category.create', 'uses' => 'Api\PostDataApiController@contractorWorkCategory' ));
    Route::put('contractor-work-category/{Id}', array('as' => 'api.contractor-work-category.update', 'uses' => 'Api\PutDataApiController@contractorWorkCategory' ));
    Route::delete('contractor-work-category/{Id}', array('as' => 'api.contractor-work-category.delete', 'uses' => 'Api\DeleteDataApiController@contractorWorkCategory' ));

    //previous_cpe_grades
    Route::get('previous-cpe-grades', array('as' => 'api.previous-cpe-grades', 'uses' => 'Api\GetDataApiController@previousCpeGrades' ));
    Route::post('previous-cpe-grades', array('as' => 'api.previous-cpe-grades.create', 'uses' => 'Api\PostDataApiController@previousCpeGrades' ));
    Route::put('previous-cpe-grades/{Id}', array('as' => 'api.previous-cpe-grades.update', 'uses' => 'Api\PutDataApiController@previousCpeGrades' ));
    Route::delete('previous-cpe-grades/{Id}', array('as' => 'api.previous-cpe-grades.delete', 'uses' => 'Api\DeleteDataApiController@previousCpeGrades' ));

    //daily_labour_report_labour_rates
    Route::get('daily-labour-report-labour-rates', array('as' => 'api.daily-labour-report-labour-rates', 'uses' => 'Api\GetDataApiController@dailyLabourReportLabourRates' ));
    Route::post('daily-labour-report-labour-rates', array('as' => 'api.daily-labour-report-labour-rates.create', 'uses' => 'Api\PostDataApiController@dailyLabourReportLabourRates' ));
    Route::put('daily-labour-report-labour-rates/{Id}', array('as' => 'api.daily-labour-report-labour-rates.update', 'uses' => 'Api\PutDataApiController@dailyLabourReportLabourRates' ));
    Route::delete('daily-labour-report-labour-rates/{Id}', array('as' => 'api.daily-labour-report-labour-rates.delete', 'uses' => 'Api\DeleteDataApiController@dailyLabourReportLabourRates' ));

    //email_notification_recipients
    Route::get('email-notification-recipients', array('as' => 'api.email-notification-recipients', 'uses' => 'Api\GetDataApiController@emailNotificationRecipients' ));
    Route::post('email-notification-recipients', array('as' => 'api.email-notification-recipients.create', 'uses' => 'Api\PostDataApiController@emailNotificationRecipients' ));
    Route::put('email-notification-recipients/{Id}', array('as' => 'api.email-notification-recipients.update', 'uses' => 'Api\PutDataApiController@emailNotificationRecipients' ));
    Route::delete('email-notification-recipients/{Id}', array('as' => 'api.email-notification-recipients.delete', 'uses' => 'Api\DeleteDataApiController@emailNotificationRecipients' ));

    //external_application_client_modules
    Route::get('external-application-client-modules', array('as' => 'api.external-application-client-modules', 'uses' => 'Api\GetDataApiController@externalApplicationClientModules' ));
    Route::post('external-application-client-modules', array('as' => 'api.external-application-client-modules.create', 'uses' => 'Api\PostDataApiController@externalApplicationClientModules' ));
    Route::put('external-application-client-modules/{Id}', array('as' => 'api.external-application-client-modules.update', 'uses' => 'Api\PutDataApiController@externalApplicationClientModules' ));
    Route::delete('external-application-client-modules/{Id}', array('as' => 'api.external-application-client-modules.delete', 'uses' => 'Api\DeleteDataApiController@externalApplicationClientModules' ));

    //form_column_sections
    Route::get('form-column-sections', array('as' => 'api.form-column-sections', 'uses' => 'Api\GetDataApiController@formColumnSections' ));
    Route::post('form-column-sections', array('as' => 'api.form-column-sections.create', 'uses' => 'Api\PostDataApiController@formColumnSections' ));
    Route::put('form-column-sections/{Id}', array('as' => 'api.form-column-sections.update', 'uses' => 'Api\PutDataApiController@formColumnSections' ));
    Route::delete('form-column-sections/{Id}', array('as' => 'api.form-column-sections.delete', 'uses' => 'Api\DeleteDataApiController@formColumnSections' ));

    //forum_thread_user_settings
    Route::get('forum-thread-user-settings', array('as' => 'api.forum-thread-user-settings', 'uses' => 'Api\GetDataApiController@forumThreadUserSettings' ));
    Route::post('forum-thread-user-settings', array('as' => 'api.forum-thread-user-settings.create', 'uses' => 'Api\PostDataApiController@forumThreadUserSettings' ));
    Route::put('forum-thread-user-settings/{Id}', array('as' => 'api.forum-thread-user-settings.update', 'uses' => 'Api\PutDataApiController@forumThreadUserSettings' ));
    Route::delete('forum-thread-user-settings/{Id}', array('as' => 'api.forum-thread-user-settings.delete', 'uses' => 'Api\DeleteDataApiController@forumThreadUserSettings' ));

    //indonesia_civil_contract_early_warnings
    Route::get('indonesia-civil-contract-early-warnings', array('as' => 'api.indonesia-civil-contract-early-warnings', 'uses' => 'Api\GetDataApiController@indonesiaCivilContractEarlyWarnings' ));
    Route::post('indonesia-civil-contract-early-warnings', array('as' => 'api.indonesia-civil-contract-early-warnings.create', 'uses' => 'Api\PostDataApiController@indonesiaCivilContractEarlyWarnings' ));
    Route::put('indonesia-civil-contract-early-warnings/{Id}', array('as' => 'api.indonesia-civil-contract-early-warnings.update', 'uses' => 'Api\PutDataApiController@indonesiaCivilContractEarlyWarnings' ));
    Route::delete('indonesia-civil-contract-early-warnings/{Id}', array('as' => 'api.indonesia-civil-contract-early-warnings.delete', 'uses' => 'Api\DeleteDataApiController@indonesiaCivilContractEarlyWarnings' ));

    //indonesia_civil_contract_ew_eot
    Route::get('indonesia-civil-contract-ew-eot', array('as' => 'api.indonesia-civil-contract-ew-eot', 'uses' => 'Api\GetDataApiController@indonesiaCivilContractEwEot' ));
    Route::post('indonesia-civil-contract-ew-eot', array('as' => 'api.indonesia-civil-contract-ew-eot.create', 'uses' => 'Api\PostDataApiController@indonesiaCivilContractEwEot' ));
    Route::put('indonesia-civil-contract-ew-eot/{Id}', array('as' => 'api.indonesia-civil-contract-ew-eot.update', 'uses' => 'Api\PutDataApiController@indonesiaCivilContractEwEot' ));
    Route::delete('indonesia-civil-contract-ew-eot/{Id}', array('as' => 'api.indonesia-civil-contract-ew-eot.delete', 'uses' => 'Api\DeleteDataApiController@indonesiaCivilContractEwEot' ));

    //letter_of_award_clause_comment_read_logs
    Route::get('letter-of-award-clause-comment-read-logs', array('as' => 'api.letter-of-award-clause-comment-read-logs', 'uses' => 'Api\GetDataApiController@letterOfAwardClauseCommentReadLogs' ));
    Route::post('letter-of-award-clause-comment-read-logs', array('as' => 'api.letter-of-award-clause-comment-read-logs.create', 'uses' => 'Api\PostDataApiController@letterOfAwardClauseCommentReadLogs' ));
    Route::put('letter-of-award-clause-comment-read-logs/{Id}', array('as' => 'api.letter-of-award-clause-comment-read-logs.update', 'uses' => 'Api\PutDataApiController@letterOfAwardClauseCommentReadLogs' ));
    Route::delete('letter-of-award-clause-comment-read-logs/{Id}', array('as' => 'api.letter-of-award-clause-comment-read-logs.delete', 'uses' => 'Api\DeleteDataApiController@letterOfAwardClauseCommentReadLogs' ));

    //menus
    Route::get('menus', array('as' => 'api.menus', 'uses' => 'Api\GetDataApiController@menus' ));
    Route::post('menus', array('as' => 'api.menus.create', 'uses' => 'Api\PostDataApiController@menus' ));
    Route::put('menus/{Id}', array('as' => 'api.menus.update', 'uses' => 'Api\PutDataApiController@menus' ));
    Route::delete('menus/{Id}', array('as' => 'api.menus.delete', 'uses' => 'Api\DeleteDataApiController@menus' ));

    //site_management_defects
    Route::get('site-management-defects', array('as' => 'api.site-management-defects', 'uses' => 'Api\GetDataApiController@siteManagementDefects' ));
    Route::post('site-management-defects', array('as' => 'api.site-management-defects.create', 'uses' => 'Api\PostDataApiController@siteManagementDefects' ));
    Route::put('site-management-defects/{Id}', array('as' => 'api.site-management-defects.update', 'uses' => 'Api\PutDataApiController@siteManagementDefects' ));
    Route::delete('site-management-defects/{Id}', array('as' => 'api.site-management-defects.delete', 'uses' => 'Api\DeleteDataApiController@siteManagementDefects' ));

    //module_permission_subsidiaries
    Route::get('module-permission-subsidiaries', array('as' => 'api.module-permission-subsidiaries', 'uses' => 'Api\GetDataApiController@modulePermissionSubsidiaries' ));
    Route::post('module-permission-subsidiaries', array('as' => 'api.module-permission-subsidiaries.create', 'uses' => 'Api\PostDataApiController@modulePermissionSubsidiaries' ));
    Route::put('module-permission-subsidiaries/{Id}', array('as' => 'api.module-permission-subsidiaries.update', 'uses' => 'Api\PutDataApiController@modulePermissionSubsidiaries' ));
    Route::delete('module-permission-subsidiaries/{Id}', array('as' => 'api.module-permission-subsidiaries.delete', 'uses' => 'Api\DeleteDataApiController@modulePermissionSubsidiaries' ));

    //notifications_categories_in_groups
    Route::get('notifications-categories-in-groups', array('as' => 'api.notifications-categories-in-groups', 'uses' => 'Api\GetDataApiController@notificationsCategoriesInGroups' ));
    Route::post('notifications-categories-in-groups', array('as' => 'api.notifications-categories-in-groups.create', 'uses' => 'Api\PostDataApiController@notificationsCategoriesInGroups' ));
    Route::put('notifications-categories-in-groups/{Id}', array('as' => 'api.notifications-categories-in-groups.update', 'uses' => 'Api\PutDataApiController@notificationsCategoriesInGroups' ));
    Route::delete('notifications-categories-in-groups/{Id}', array('as' => 'api.notifications-categories-in-groups.delete', 'uses' => 'Api\DeleteDataApiController@notificationsCategoriesInGroups' ));

    //object_permissions
    Route::get('object-permissions', array('as' => 'api.object-permissions', 'uses' => 'Api\GetDataApiController@objectPermissions' ));
    Route::post('object-permissions', array('as' => 'api.object-permissions.create', 'uses' => 'Api\PostDataApiController@objectPermissions' ));
    Route::put('object-permissions/{Id}', array('as' => 'api.object-permissions.update', 'uses' => 'Api\PutDataApiController@objectPermissions' ));
    Route::delete('object-permissions/{Id}', array('as' => 'api.object-permissions.delete', 'uses' => 'Api\DeleteDataApiController@objectPermissions' ));

    //open_tender_award_recommendation_report_edit_logs
    Route::get('open-tender-award-recommendation-report-edit-logs', array('as' => 'api.open-tender-award-recommendation-report-edit-logs', 'uses' => 'Api\GetDataApiController@openTenderAwardRecommendationReportEditLogs' ));
    Route::post('open-tender-award-recommendation-report-edit-logs', array('as' => 'api.open-tender-award-recommendation-report-edit-logs.create', 'uses' => 'Api\PostDataApiController@openTenderAwardRecommendationReportEditLogs' ));
    Route::put('open-tender-award-recommendation-report-edit-logs/{Id}', array('as' => 'api.open-tender-award-recommendation-report-edit-logs.update', 'uses' => 'Api\PutDataApiController@openTenderAwardRecommendationReportEditLogs' ));
    Route::delete('open-tender-award-recommendation-report-edit-logs/{Id}', array('as' => 'api.open-tender-award-recommendation-report-edit-logs.delete', 'uses' => 'Api\DeleteDataApiController@openTenderAwardRecommendationReportEditLogs' ));

    //structured_document_clauses
    Route::get('structured-document-clauses', array('as' => 'api.structured-document-clauses', 'uses' => 'Api\GetDataApiController@structuredDocumentClauses' ));
    Route::post('structured-document-clauses', array('as' => 'api.structured-document-clauses.create', 'uses' => 'Api\PostDataApiController@structuredDocumentClauses' ));
    Route::put('structured-document-clauses/{Id}', array('as' => 'api.structured-document-clauses.update', 'uses' => 'Api\PutDataApiController@structuredDocumentClauses' ));
    Route::delete('structured-document-clauses/{Id}', array('as' => 'api.structured-document-clauses.delete', 'uses' => 'Api\DeleteDataApiController@structuredDocumentClauses' ));

    //technical_evaluation_attachment_list_items
    Route::get('technical-evaluation-attachment-list-items', array('as' => 'api.technical-evaluation-attachment-list-items', 'uses' => 'Api\GetDataApiController@technicalEvaluationAttachmentListItems' ));
    Route::post('technical-evaluation-attachment-list-items', array('as' => 'api.technical-evaluation-attachment-list-items.create', 'uses' => 'Api\PostDataApiController@technicalEvaluationAttachmentListItems' ));
    Route::put('technical-evaluation-attachment-list-items/{Id}', array('as' => 'api.technical-evaluation-attachment-list-items.update', 'uses' => 'Api\PutDataApiController@technicalEvaluationAttachmentListItems' ));
    Route::delete('technical-evaluation-attachment-list-items/{Id}', array('as' => 'api.technical-evaluation-attachment-list-items.delete', 'uses' => 'Api\DeleteDataApiController@technicalEvaluationAttachmentListItems' ));

    //tender_interview_logs
    Route::get('tender-interview-logs', array('as' => 'api.tender-interview-logs', 'uses' => 'Api\GetDataApiController@tenderInterviewLogs' ));
    Route::post('tender-interview-logs', array('as' => 'api.tender-interview-logs.create', 'uses' => 'Api\PostDataApiController@tenderInterviewLogs' ));
    Route::put('tender-interview-logs/{Id}', array('as' => 'api.tender-interview-logs.update', 'uses' => 'Api\PutDataApiController@tenderInterviewLogs' ));
    Route::delete('tender-interview-logs/{Id}', array('as' => 'api.tender-interview-logs.delete', 'uses' => 'Api\DeleteDataApiController@tenderInterviewLogs' ));

    //track_record_project_vendor_work_subcategories
    Route::get('track-record-project-vendor-work-subcategories', array('as' => 'api.track-record-project-vendor-work-subcategories', 'uses' => 'Api\GetDataApiController@trackRecordProjectVendorWorkSubcategories' ));
    Route::post('track-record-project-vendor-work-subcategories', array('as' => 'api.track-record-project-vendor-work-subcategories.create', 'uses' => 'Api\PostDataApiController@trackRecordProjectVendorWorkSubcategories' ));
    Route::put('track-record-project-vendor-work-subcategories/{Id}', array('as' => 'api.track-record-project-vendor-work-subcategories.update', 'uses' => 'Api\PutDataApiController@trackRecordProjectVendorWorkSubcategories' ));
    Route::delete('track-record-project-vendor-work-subcategories/{Id}', array('as' => 'api.track-record-project-vendor-work-subcategories.delete', 'uses' => 'Api\DeleteDataApiController@trackRecordProjectVendorWorkSubcategories' ));

    //vendor_performance_evaluation_cycles
    Route::get('vendor-performance-evaluation-cycles', array('as' => 'api.vendor-performance-evaluation-cycles', 'uses' => 'Api\GetDataApiController@vendorPerformanceEvaluationCycles' ));
    Route::post('vendor-performance-evaluation-cycles', array('as' => 'api.vendor-performance-evaluation-cycles.create', 'uses' => 'Api\PostDataApiController@vendorPerformanceEvaluationCycles' ));
    Route::put('vendor-performance-evaluation-cycles/{Id}', array('as' => 'api.vendor-performance-evaluation-cycles.update', 'uses' => 'Api\PutDataApiController@vendorPerformanceEvaluationCycles' ));
    Route::delete('vendor-performance-evaluation-cycles/{Id}', array('as' => 'api.vendor-performance-evaluation-cycles.delete', 'uses' => 'Api\DeleteDataApiController@vendorPerformanceEvaluationCycles' ));

    //vendor_registration_processor_remarks
    Route::get('vendor-registration-processor-remarks', array('as' => 'api.vendor-registration-processor-remarks', 'uses' => 'Api\GetDataApiController@vendorRegistrationProcessorRemarks' ));
    Route::post('vendor-registration-processor-remarks', array('as' => 'api.vendor-registration-processor-remarks.create', 'uses' => 'Api\PostDataApiController@vendorRegistrationProcessorRemarks' ));
    Route::put('vendor-registration-processor-remarks/{Id}', array('as' => 'api.vendor-registration-processor-remarks.update', 'uses' => 'Api\PutDataApiController@vendorRegistrationProcessorRemarks' ));
    Route::delete('vendor-registration-processor-remarks/{Id}', array('as' => 'api.vendor-registration-processor-remarks.delete', 'uses' => 'Api\DeleteDataApiController@vendorRegistrationProcessorRemarks' ));
?>