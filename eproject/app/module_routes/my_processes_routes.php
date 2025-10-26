<?php

Route::group(array( 'prefix' => 'my-processes' ), function ()
{
    Route::get('my-processes/count', array( 'as' => 'home.myProcesses.count', 'uses' => 'MyProcessesController@getProcessesCount' ));
    Route::get('recommendation-of-tenderer', array( 'as' => 'home.myProcesses.recommendationOfTenderer', 'uses' => 'MyProcessesController@getRecommendationOfTendererList' ));
    Route::get('recommendation-of-tenderer/{recommendationOfTendererId}/verifiers', array( 'as' => 'home.myProcesses.recommendationOfTenderer.verifiers', 'uses' => 'MyProcessesController@getRecommendationOfTendererVerifierList' ));
    Route::get('list-of-tenderer', array( 'as' => 'home.myProcesses.listOfTenderer', 'uses' => 'MyProcessesController@getListOfTendererList' ));
    Route::get('list-of-tenderer/{listOfTendererId}/verifiers', array( 'as' => 'home.myProcesses.listOfTenderer.verifiers', 'uses' => 'MyProcessesController@getListOfTendererVerifierList' ));
    Route::get('calling-tender', array( 'as' => 'home.myProcesses.callingTender', 'uses' => 'MyProcessesController@getCallingTenderList' ));
    Route::get('calling-tender/{callingTenderId}/verifiers', array( 'as' => 'home.myProcesses.callingTender.verifiers', 'uses' => 'MyProcessesController@getCallingTenderVerifierList' ));
    Route::get('open-tender', array( 'as' => 'home.myProcesses.openTender', 'uses' => 'MyProcessesController@getOpenTenderList' ));
    Route::get('open-tender/{tenderId}/verifiers', array( 'as' => 'home.myProcesses.openTender.verifiers', 'uses' => 'MyProcessesController@getOpenTenderVerifierList' ));
    Route::get('technical-evaluation', array( 'as' => 'home.myProcesses.technicalEvaluation', 'uses' => 'MyProcessesController@getTechnicalEvaluationList' ));
    Route::get('technical-evaluation/{tenderId}/verifiers', array( 'as' => 'home.myProcesses.technicalEvaluation.verifiers', 'uses' => 'MyProcessesController@getTechnicalEvaluationVerifierList' ));
    Route::get('technical-assessment', array( 'as' => 'home.myProcesses.technicalAssessment', 'uses' => 'MyProcessesController@getTechnicalAssessmentList' ));
    Route::get('technical-assessment/{technicalEvaluationId}/verifiers', array( 'as' => 'home.myProcesses.technicalAssessment.verifiers', 'uses' => 'MyProcessesController@getTechnicalAssessmentVerifierList' ));
    Route::get('award-recommendation', array( 'as' => 'home.myProcesses.awardRecommendation', 'uses' => 'MyProcessesController@getAwardRecommendationList' ));
    Route::get('award-recommendation/{openTenderAwardRecommendationId}/verifiers', array( 'as' => 'home.myProcesses.awardRecommendation.verifiers', 'uses' => 'MyProcessesController@getAwardRecommendationVerifierList' ));
    Route::get('letter-of-award', array( 'as' => 'home.myProcesses.letterOfAward', 'uses' => 'MyProcessesController@getLetterOfAwardList' ));
    Route::get('letter-of-award/{letterOfAwardId}/verifiers', array( 'as' => 'home.myProcesses.letterOfAward.verifiers', 'uses' => 'MyProcessesController@getLetterOfAwardVerifierList' ));
    Route::get('tender-resubmission', array( 'as' => 'home.myProcesses.tenderResubmission', 'uses' => 'MyProcessesController@getTenderResubmissionList' ));
    Route::get('tender-resubmission/{tenderId}/verifiers', array( 'as' => 'home.myProcesses.tenderResubmission.verifiers', 'uses' => 'MyProcessesController@getTenderResubmissionVerifierList' ));
    Route::get('request-for-information-message', array( 'as' => 'home.myProcesses.requestForInformationMessage', 'uses' => 'MyProcessesController@getRequestForInformationMessageList' ));
    Route::get('request-for-information-message/{requestForInformationMessageId}/verifiers', array( 'as' => 'home.myProcesses.requestForInformationMessage.verifiers', 'uses' => 'MyProcessesController@getRequestForInformationMessageVerifierList' ));
    Route::get('risk-register-message', array( 'as' => 'home.myProcesses.riskRegisterMessage', 'uses' => 'MyProcessesController@getRiskRegisterMessageList' ));
    Route::get('risk-register-message/{riskRegisterMessageId}/verifiers', array( 'as' => 'home.myProcesses.riskRegisterMessage.verifiers', 'uses' => 'MyProcessesController@getRiskRegisterMessageVerifierList' ));

    Route::get('publish-to-post-contract', array( 'as' => 'home.myProcesses.publishToPostContract', 'uses' => 'MyProcessesController@getPublishToPostContractList' ));
    Route::get('publish-to-post-contract/{projectId}/verifiers', array( 'as' => 'home.myProcesses.publishToPostContract.verifiers', 'uses' => 'MyProcessesController@getPublishToPostContractVerifierList' ));
    Route::get('water-deposit', array( 'as' => 'home.myProcesses.waterDeposit', 'uses' => 'MyProcessesController@getWaterDepositList' ));
    Route::get('water-deposit/{objectId}/verifiers', array( 'as' => 'home.myProcesses.waterDeposit.verifiers', 'uses' => 'MyProcessesController@getWaterDepositVerifierList' ));
    Route::get('deposit', array( 'as' => 'home.myProcesses.deposit', 'uses' => 'MyProcessesController@getDepositList' ));
    Route::get('deposit/{objectId}/verifiers', array( 'as' => 'home.myProcesses.deposit.verifiers', 'uses' => 'MyProcessesController@getDepositVerifierList' ));
    Route::get('out-of-contract-item', array( 'as' => 'home.myProcesses.outOfContractItems', 'uses' => 'MyProcessesController@getOutOfContractItemsList' ));
    Route::get('out-of-contract-item/{objectId}/verifiers', array( 'as' => 'home.myProcesses.outOfContractItems.verifiers', 'uses' => 'MyProcessesController@getOutOfContractItemsVerifierList' ));
    Route::get('purchase-on-behalf', array( 'as' => 'home.myProcesses.purchaseOnBehalf', 'uses' => 'MyProcessesController@getPurchaseOnBehalfList' ));
    Route::get('purchase-on-behalf/{objectId}/verifiers', array( 'as' => 'home.myProcesses.purchaseOnBehalf.verifiers', 'uses' => 'MyProcessesController@getPurchaseOnBehalfVerifierList' ));
    Route::get('advanced-payment', array( 'as' => 'home.myProcesses.advancedPayment', 'uses' => 'MyProcessesController@getAdvancedPaymentList' ));
    Route::get('advanced-payment/{objectId}/verifiers', array( 'as' => 'home.myProcesses.advancedPayment.verifiers', 'uses' => 'MyProcessesController@getAdvancedPaymentVerifierList' ));
    Route::get('work-on-behalf', array( 'as' => 'home.myProcesses.workOnBehalf', 'uses' => 'MyProcessesController@getWorkOnBehalfList' ));
    Route::get('work-on-behalf/{objectId}/verifiers', array( 'as' => 'home.myProcesses.workOnBehalf.verifiers', 'uses' => 'MyProcessesController@getWorkOnBehalfVerifierList' ));
    Route::get('work-on-behalf-back-charge', array( 'as' => 'home.myProcesses.workOnBehalfBackCharge', 'uses' => 'MyProcessesController@getWorkOnBehalfBackChargeList' ));
    Route::get('work-on-behalf-back-charge/{objectId}/verifiers', array( 'as' => 'home.myProcesses.workOnBehalfBackCharge.verifiers', 'uses' => 'MyProcessesController@getWorkOnBehalfBackChargeVerifierList' ));
    Route::get('penalty', array( 'as' => 'home.myProcesses.penalty', 'uses' => 'MyProcessesController@getPenaltyList' ));
    Route::get('penalty/{objectId}/verifiers', array( 'as' => 'home.myProcesses.penalty.verifiers', 'uses' => 'MyProcessesController@getPenaltyVerifierList' ));
    Route::get('permit', array( 'as' => 'home.myProcesses.permit', 'uses' => 'MyProcessesController@getPermitList' ));
    Route::get('permit/{objectId}/verifiers', array( 'as' => 'home.myProcesses.permit.verifiers', 'uses' => 'MyProcessesController@getPermitVerifierList' ));
    Route::get('variation-order', array( 'as' => 'home.myProcesses.variationOrder', 'uses' => 'MyProcessesController@getVariationOrderList' ));
    Route::get('variation-order/{objectId}/verifiers', array( 'as' => 'home.myProcesses.variationOrder.verifiers', 'uses' => 'MyProcessesController@getVariationOrderVerifierList' ));
    Route::get('material-on-site', array( 'as' => 'home.myProcesses.materialOnSite', 'uses' => 'MyProcessesController@getMaterialOnSiteList' ));
    Route::get('material-on-site/{objectId}/verifiers', array( 'as' => 'home.myProcesses.materialOnSite.verifiers', 'uses' => 'MyProcessesController@getMaterialOnSiteVerifierList' ));
    Route::get('claim-certificate', array( 'as' => 'home.myProcesses.claimCertificate', 'uses' => 'MyProcessesController@getClaimCertificateList' ));
    Route::get('claim-certificate/{claimCertificateId}/verifiers', array( 'as' => 'home.myProcesses.claimCertificate.verifiers', 'uses' => 'MyProcessesController@getClaimCertificateVerifierList' ));
    Route::get('request-for-variation', array( 'as' => 'home.myProcesses.requestForVariation', 'uses' => 'MyProcessesController@getRequestForVariationList' ));
    Route::get('request-for-variation/{requestForVariationId}/verifiers', array( 'as' => 'home.myProcesses.requestForVariation.verifiers', 'uses' => 'MyProcessesController@getRequestForVariationVerifierList' ));
    Route::get('account-code-setting', array( 'as' => 'home.myProcesses.accountCodeSetting', 'uses' => 'MyProcessesController@getAccountCodeSettingList' ));
    Route::get('account-code-setting/{accountCodeSettingId}/verifiers', array( 'as' => 'home.myProcesses.accountCodeSetting.verifiers', 'uses' => 'MyProcessesController@getAccountCodeSettingVerifierList' ));
    Route::get('site-management-defect-backcharge-detail', array( 'as' => 'home.myProcesses.siteManagementDefectBackchargeDetail', 'uses' => 'MyProcessesController@getSiteManagementDefectBackchargeDetailList' ));
    Route::get('site-management-defect-backcharge-detail/{accountCodeSettingId}/verifiers', array( 'as' => 'home.myProcesses.siteManagementDefectBackchargeDetail.verifiers', 'uses' => 'MyProcessesController@getSiteManagementDefectBackchargeDetailVerifierList' ));

    Route::get('site-management-site-diary', array( 'as' => 'home.myProcesses.siteManagementSiteDiaryList', 'uses' => 'MyProcessesController@getSiteManagementSiteDiaryList' ));
    Route::get('site-management-site-diary/{siteDiaryId}/verifiers', array( 'as' => 'home.myProcesses.siteManagementSiteDiaryList.verifiers', 'uses' => 'MyProcessesController@getSiteManagementSiteDiaryVerifierList' ));

    Route::get('instruction-to-contractor', array( 'as' => 'home.myProcesses.instructionToContractorList', 'uses' => 'MyProcessesController@getInstructionToContractorList' ));
    Route::get('instruction-to-contractor/{instructionToContractorId}/verifiers', array( 'as' => 'home.myProcesses.instructionToContractor.verifiers', 'uses' => 'MyProcessesController@getInstructionToContractorVerifierList' ));

    Route::get('daily-report', array( 'as' => 'home.myProcesses.dailyReportList', 'uses' => 'MyProcessesController@getDailyReportList' ));
    Route::get('daily-report/{dailyReportId}/verifiers', array( 'as' => 'home.myProcesses.dailyReportList.verifiers', 'uses' => 'MyProcessesController@getDailyReportVerifierList' ));

    Route::get('request-for-inspection', array( 'as' => 'home.myProcesses.requestForInspection', 'uses' => 'MyProcessesController@getRequestForInspectionList' ));
    Route::get('request-for-inspection/{inspectionId}/verifiers', array( 'as' => 'home.myProcesses.requestForInspection.verifiers', 'uses' => 'MyProcessesController@getRequestForInspectionVerifierList' ));

    Route::get('vendor-registration', array( 'as' => 'home.myProcesses.vendorRegistration', 'uses' => 'MyProcessesController@getVendorRegistrationList' ));
    Route::get('vendor-registration/{vendorRegistrationId}/verifiers', array( 'as' => 'home.myProcesses.vendorRegistration.verifiers', 'uses' => 'MyProcessesController@getVendorRegistrationVerifierList' ));
    Route::get('vendor-evaluation', array( 'as' => 'home.myProcesses.vendorEvaluation', 'uses' => 'MyProcessesController@getVendorEvaluationList' ));
    Route::get('vendor-evaluation/{companyFormId}/verifiers', array( 'as' => 'home.myProcesses.vendorEvaluation.verifiers', 'uses' => 'MyProcessesController@getVendorEvaluationVerifierList' ));

    Route::get('recommendation-of-consultant', array( 'as' => 'home.myProcesses.recommendationOfConsultant', 'uses' => 'MyProcessesController@getRecommendationOfConsultantList' ));
    Route::get('recommendation-of-consultant/{recommendationOfConsultantId}/verifiers', array( 'as' => 'home.myProcesses.recommendationOfConsultant.verifiers', 'uses' => 'MyProcessesController@getRecommendationOfConsultantVerifierList' ));
    Route::get('list-of-consultant', array( 'as' => 'home.myProcesses.listOfConsultant', 'uses' => 'MyProcessesController@getListOfConsultantList' ));
    Route::get('list-of-consultant/{listOfConsultantId}/verifiers', array( 'as' => 'home.myProcesses.listOfConsultant.verifiers', 'uses' => 'MyProcessesController@getListOfConsultantVerifierList' ));
    Route::get('calling-rfp', array( 'as' => 'home.myProcesses.callingRfp', 'uses' => 'MyProcessesController@getCallingRfpList' ));
    Route::get('calling-rfp/{callingRfpId}/verifiers', array( 'as' => 'home.myProcesses.callingRfp.verifiers', 'uses' => 'MyProcessesController@getCallingRfpVerifierList' ));
    Route::get('open-rfp', array( 'as' => 'home.myProcesses.openRfp', 'uses' => 'MyProcessesController@getOpenRfpList' ));
    Route::get('open-rfp/{openRfpId}/verifiers', array( 'as' => 'home.myProcesses.openRfp.verifiers', 'uses' => 'MyProcessesController@getOpenRfpVerifierList' ));
    Route::get('rfp-resubmission', array( 'as' => 'home.myProcesses.rfpResubmission', 'uses' => 'MyProcessesController@getRfpResubmissionList' ));
    Route::get('rfp-resubmission/{openRfdId}/verifiers', array( 'as' => 'home.myProcesses.rfpResubmission.verifiers', 'uses' => 'MyProcessesController@getRfpResubmissionVerifierList' ));
    Route::get('approval-documents', array( 'as' => 'home.myProcesses.approvalDocument', 'uses' => 'MyProcessesController@getApprovalDocumentList' ));
    Route::get('approval-documents/{approvalDocumentId}/verifiers', array( 'as' => 'home.myProcesses.approvalDocument.verifiers', 'uses' => 'MyProcessesController@getApprovalDocumentVerifierList' ));
    Route::get('consultant-management-letter-of-award', array( 'as' => 'home.myProcesses.consultantManagementLetterOfAward', 'uses' => 'MyProcessesController@getConsultantManagementLetterOfAwardList' ));
    Route::get('consultant-management-letter-of-award/{letterOfAwardId}/verifiers', array( 'as' => 'home.myProcesses.consultantManagementLetterOfAward.verifiers', 'uses' => 'MyProcessesController@getConsultantManagementLetterOfAwardVerifierList' ));
});