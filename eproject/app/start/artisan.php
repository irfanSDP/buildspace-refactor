<?php

/*
|--------------------------------------------------------------------------
| Register The Artisan Commands
|--------------------------------------------------------------------------
|
| Each available Artisan command must be registered with the console so
| that it is available to be called. We'll register every command so
| the console gets access to each of the command object instances.
|
*/

Artisan::add(\App::make('UpdateProjectCallingTenderToClosedTender'));
Artisan::add(\App::make('DeactivateVendors'));
Artisan::add(\App::make('ProcessUnsuccessfulRegistrations'));
Artisan::add(\App::make('FlushExpiredTemporaryAccounts'));
Artisan::add(\App::make('StartAndEndVendorPerformanceEvaluations'));
Artisan::add(\App::make('StartAndEndDigitalStarCycles'));
Artisan::add(\App::make('AddReferenceIdToExistingCompanies'));
Artisan::add(\App::make('SyncCompanyListingWithBuildSpace'));
Artisan::add(\App::make('UpdateLanguageList'));
Artisan::add(\App::make('SystemModuleInfo'));
Artisan::add(\App::make('EnableSystemModule'));
Artisan::add(\App::make('DisableSystemModule'));
Artisan::add(\App::make('GenerateS4HanaContractDataFiles'));
Artisan::add(\App::make('MigrateSDPTrackRecords'));
Artisan::add(\App::make('AddCompanyAdmins'));
Artisan::add(\App::make('RevertVendorStatusToRegistering'));
Artisan::add(\App::make('SetVendorExpiryDate'));
Artisan::add(\App::make('ReassignVendorWorkCategoriesForProjectTrackRecord'));
Artisan::add(\App::make('ReassignVendorWorkCategoriesForVendorPreQualificationSetups'));
Artisan::add(\App::make('ReassignVendorWorkCategoriesForVendorPreQualifications'));
Artisan::add(\App::make('ReassignVendorWorkCategoriesForVendors'));
Artisan::add(\App::make('SeedVendorManagementVendorPrequalifications'));
Artisan::add(\App::make('ImportVendorPreQualifications'));
Artisan::add(\App::make('RecalculateVendorPerformanceEvaluationScore'));
Artisan::add(\App::make('VendorRenewalReminder'));
Artisan::add(\App::make('ExtendCompletedVendorPerformanceEvaluationCycle'));
Artisan::add(\App::make('UpdateVendorPerformanceEvaluationVendorWorkCategories'));
Artisan::add(\App::make('SendEmailRemindersBeforeVendorPerformanceEvaluationCycleEndDate'));
Artisan::add(\App::make('SendEmailRemindersBeforeDigitalStarCycleStartDate'));
Artisan::add(\App::make('SendEmailRemindersBeforeDigitalStarCycleEndDate'));
Artisan::add(\App::make('SendEmailRemindersForPendingApprovalTasks'));
Artisan::add(\App::make('SendTenderClosingReminders'));
Artisan::add(\App::make('UpdateTenderStatusesCommand'));
Artisan::add(\App::make('ExternalAppManager'));
Artisan::add(\App::make('CreateAwardRecommendationBillDetails'));
Artisan::add(\App::make('GenerateVendorEvaluationForms'));
Artisan::add(\App::make('SendProjectReportReminders'));
Artisan::add(\App::make('EBiddingEmailReminder'));
Artisan::add(\App::make('EmailAnnouncementSendAsync'));
Artisan::add(\App::make('DsEvaluationGenerateFormsWhenInProgress'));

//testing
Artisan::add(\App::make('TestEmailConnection'));
Artisan::add(\App::make('TestEmailSend'));