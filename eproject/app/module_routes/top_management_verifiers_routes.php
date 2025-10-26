<?php

Route::group(['prefix' => 'top-management-verifiers'], function() {
    Route::get('company/{companyId}/get_vendor_profile_info', ['as' => 'topManagementVerifiers.vendor.profile.info', 'uses' => 'ConsultantManagementController@vendorProfileInfo']);

    Route::group(['prefix' => 'project/{projectId}'], function() {
        Route::group(['prefix' => 'tender/{tenderId}'], function() {
            Route::group(['before' => 'topManagementVerifierTenderingStageApprovalCheck'], function() {
                Route::get('/', ['as' => 'topManagementVerifiers.projects.tender.show', 'uses' => 'ProjectTendersController@show']);
                Route::get('get_contractors_commitment_status_log', array( 'as' => 'topManagementVerifiers.projects.tender.get_contractors_commitment_status_log', 'uses' => 'ProjectTendersController@getContractorsCommitmentStatusLog' ));
    
                Route::group(array( 'before' => 'checkROTSubmissionStatus' ), function() {
                    Route::put('update_rot', array( 'as' => 'topManagementVerifiers.projects.tender.update_rot_information', 'uses' => 'ProjectTendersController@updateROTInformation' ));
                });
    
                Route::group(array( 'before' => 'checkLOTSubmissionStatus' ), function() {
                    Route::put('update_lot', array( 'as' => 'topManagementVerifiers.projects.tender.update_lot_information', 'uses' => 'ProjectTendersController@updateLOTInformation' ));
                });
    
                Route::group(array( 'before' => 'checkCallingTenderSubmissionStatus' ), function() {
                    Route::put('update_calling_tender', array( 'as' => 'topManagementVerifiers.projects.tender.update_calling_tender_information', 'uses' => 'ProjectTendersController@updateCallingTenderInformation' ));
                });
            });

            Route::group(['prefix' => 'award_recommendation'], function() {
                Route::group(['before' => 'topManagementVerifierOpenTenderAwardRecommendationApprovalCheck'], function() {
                    Route::get('report/show', ['as' => 'topManagementVerifiers.open_tender.award_recommendation.report.show', 'uses' => 'OpenTenderAwardRecommendationController@show' ]);
                    Route::get('reportLogs/get', [ 'as' => 'topManagementVerifiers.open_tender.award_recommendation.report.edit.logs.get', 'uses' => 'OpenTenderAwardRecommendationController@getReportEditLogs' ]);
            
                    Route::get('report/tenderAnalysis/', [ 'as' => 'topManagementVerifiers.open_tender.award_recommendation.report.tender_analysis_table.index', 'uses' => 'OpenTenderAwardRecommendationTenderAnalysisController@index' ]);
                    Route::get('report/tenderAnalysis/statusOfParticipants', [ 'as' => 'topManagementVerifiers.open_tender.award_recommendation.report.tender_analysis_table.statusOfParticipants', 'uses' => 'OpenTenderAwardRecommendationTenderAnalysisController@getStatusOfParticipants' ]);
                    Route::get('report/tenderAnalysis/originalTenderSummary', [ 'as' => 'topManagementVerifiers.open_tender.award_recommendation.report.tender_analysis_table.originalTenderSummary', 'uses' => 'OpenTenderAwardRecommendationTenderAnalysisController@getOriginalTenderSummary' ]);
                    Route::get('report/tenderAnalysis/tenderResubmissionSummary', [ 'as' => 'topManagementVerifiers.open_tender.award_recommendation.report.tender_analysis_table.tenderResubmissionSummary', 'uses' => 'OpenTenderAwardRecommendationTenderAnalysisController@getTenderResubmissionSummary' ]);
                    Route::get('report/tenderAnalysis/pteVsAward', [ 'as' => 'topManagementVerifiers.open_tender.award_recommendation.report.tender_analysis_table.ptevsaward', 'uses' => 'OpenTenderAwardRecommendationTenderAnalysisController@getPteVsAwardSummary' ]);
                    Route::get('report/tenderAnalysis/budgetVsAward', [ 'as' => 'topManagementVerifiers.open_tender.award_recommendation.report.tender_analysis_table.budgetvsaward', 'uses' => 'OpenTenderAwardRecommendationTenderAnalysisController@getBudgetVsAwardSummary' ]);
                    Route::get('report/tenderAnalysis/contractSum', [ 'as' => 'topManagementVerifiers.open_tender.award_recommendation.report.tender_analysis_table.contractsum', 'uses' => 'OpenTenderAwardRecommendationTenderAnalysisController@getContractSumSummary' ]);
                    Route::get('report/tenderAnalysis/logs', [ 'as' => 'topManagementVerifiers.open_tender.award_recommendation.report.tender_analysis_table.logs.get', 'uses' => 'OpenTenderAwardRecommendationTenderAnalysisController@getTenderAnalaysisEditLogs' ]);
                });
        
                Route::get('report/attachments/', [ 'as' => 'topManagementVerifiers.open_tender.award_recommendation.report.attachment.index', 'uses' => 'OpenTenderAwardRecommendationAttachmentsController@index' ]);
                Route::get('report/attachments/get', [ 'as' => 'topManagementVerifiers.open_tender.award_recommendation.report.attachment.get', 'uses' => 'OpenTenderAwardRecommendationAttachmentsController@getUploadedFiles' ]);
                Route::get('report/attachments/download', [ 'as' => 'topManagementVerifiers.open_tender.award_recommendation.report.attachment.download', 'uses' => 'OpenTenderAwardRecommendationAttachmentsController@download' ]);
            });
        });

        Route::group(['prefix' => 'request_for_variation', 'before' => 'topManagementVerifierRequestForVariationApprovalCheck'], function() {
            Route::get('/getActionLogs/{rfvId}', [ 'as' => 'topManagementVerifiers.requestForVariation.logs.get', 'uses' => 'RequestForVariationController@getActionLogs' ]);
            Route::post('/submit', ['as' => 'topManagementVerifiers.requestForVariation.submit', 'uses' => 'RequestForVariationController@submit']);
    
            Route::group(['prefix' => '{requestForVariationId}'], function() {
                Route::get('/', ['as' => 'topManagementVerifiers.requestForVariation.form.show', 'uses' => 'RequestForVariationController@show']);
                Route::get('/uploadedFiles', [ 'as' => 'topManagementVerifiers.requestForVariation.uploaded.files.get', 'uses' => 'RequestForVariationController@getUploadedFiles' ]);
            });
    
            Route::group(['prefix' => 'cost_estimate'], function(){
                Route::get('list/{rfvId}', [ 'as' => 'topManagementVerifiers.requestForVariation.cost.estimate.list', 'uses' => 'RequestForVariationCostEstimateController@getCostEstimateList' ]);
            });
        });

        Route::group(array( 'prefix' => 'contract-management' ), function() {
            Route::group(array( 'prefix' => 'claim-certificate' ), function() {
                Route::get('/', array( 'as' => 'topManagementVerifiers.contractManagement.claimCertificate.index', 'uses' => 'ClaimCertificateController@index' ));
            });
            Route::group(array( 'prefix' => 'variation-order' ), function() {
                Route::get('/', array( 'as' => 'topManagementVerifiers.contractManagement.variationOrder.index', 'uses' => 'ClaimVariationOrderController@index' ));
            });
        });
    });
});