<?php
Route::group(['before' => 'systemModule.digitalStar.enabled'], function()
{
    Route::group(array('prefix' => 'digital-star-evaluation'), function()
    {
        Route::group(array('prefix' => 'template-forms', 'before' => 'vendorManagement.hasPermission:' . \PCK\VendorManagement\VendorManagementUserPermission::TYPE_DIGITAL_STAR_TEMPLATE), function()
        {
            Route::get('/', array('as' => 'digital-star.templateForm', 'uses' => 'DigitalStar\DsTemplateFormController@index'));
            Route::get('create', array('as' => 'digital-star.templateForm.create', 'uses' => 'DigitalStar\DsTemplateFormController@create'));
            Route::post('/', array('as' => 'digital-star.templateForm.store', 'uses' => 'DigitalStar\DsTemplateFormController@store'));
            Route::get('{templateFormId}', array('as' => 'digital-star.templateForm.edit', 'uses' => 'DigitalStar\DsTemplateFormController@edit'));
            Route::post('{templateFormId}', array('as' => 'digital-star.templateForm.update', 'uses' => 'DigitalStar\DsTemplateFormController@update'));
            Route::get('{templateFormId}/approval', array('as' => 'digital-star.templateForm.approval', 'uses' => 'DigitalStar\DsTemplateFormController@approval'));
            Route::post('{templateFormId}/approval', array('as' => 'digital-star.templateForm.approve', 'uses' => 'DigitalStar\DsTemplateFormController@approve'));
            Route::get('{templateFormId}/template', array('as' => 'digital-star.templateForm.template', 'uses' => 'DigitalStar\DsTemplateFormController@template'));
            Route::get('{templateFormId}/clone', array('as' => 'digital-star.templateForm.clone', 'uses' => 'DigitalStar\DsTemplateFormController@clone'));

            Route::group(array('prefix' => '{templateFormId}'), function()
            {
                Route::group(array('prefix' => 'parent-nodes/{parentNodeId}/nodes'), function()
                {
                    Route::get('/', array('as' => 'digital-star.templateForm.nodes', 'uses' => 'DigitalStar\DsTemplateFormNodeController@index'));
                    Route::get('list', array('as' => 'digital-star.templateForm.nodes.list', 'uses' => 'DigitalStar\DsTemplateFormNodeController@list'));
                    Route::post('/', array('as' => 'digital-star.templateForm.nodes.storeOrUpdate', 'uses' => 'DigitalStar\DsTemplateFormNodeController@storeOrUpdate'));
                    Route::delete('{weightedNodeId}', array('as' => 'digital-star.templateForm.delete', 'uses' => 'DigitalStar\DsTemplateFormNodeController@destroy'));
                });

                Route::group(array('prefix' => 'nodes/{weightedNodeId}/scores'), function()
                {
                    Route::get('/', array('as' => 'digital-star.templateForm.node.scores', 'uses' => 'DigitalStar\DsTemplateFormScoreController@index'));
                    Route::get('list', array('as' => 'digital-star.templateForm.nodes.scores.list', 'uses' => 'DigitalStar\DsTemplateFormScoreController@list'));
                    Route::post('/', array('as' => 'digital-star.templateForm.nodes.scores.storeOrUpdate', 'uses' => 'DigitalStar\DsTemplateFormScoreController@storeOrUpdate'));
                    Route::delete('{scoreId}', array('as' => 'digital-star.templateForm.node.scores.delete', 'uses' => 'DigitalStar\DsTemplateFormScoreController@destroy'));
                });
            });
        });

        Route::group(array( 'prefix' => 'cycles', 'before' => 'vendorManagement.hasPermission:'.\PCK\VendorManagement\VendorManagementUserPermission::TYPE_DIGITAL_STAR ), function()
        {
            Route::get('/', array( 'as' => 'digital-star.cycle.index', 'uses' => 'DigitalStar\DsCycleController@index' ));
            Route::get('list', array( 'as' => 'digital-star.cycle.list', 'uses' => 'DigitalStar\DsCycleController@list' ));
            Route::get('create', array( 'as' => 'digital-star.cycle.create', 'uses' => 'DigitalStar\DsCycleController@create' ));
            Route::post('store', array( 'as' => 'digital-star.cycle.store', 'uses' => 'DigitalStar\DsCycleController@store' ));
            Route::get('{cycleId}/edit', array( 'as' => 'digital-star.cycle.edit', 'uses' => 'DigitalStar\DsCycleController@edit' ));
            Route::post('{cycleId}', array( 'as' => 'digital-star.cycle.update', 'uses' => 'DigitalStar\DsCycleController@update' ));

            Route::get('{cycleId}/assigned-companies', array( 'as' => 'digital-star.cycle.assignedCompanies', 'uses' => 'DigitalStar\DsCycleController@assignedCompanies' ));
            Route::get('{cycleId}/unassigned-companies', array( 'as' => 'digital-star.cycle.unassignedCompanies', 'uses' => 'DigitalStar\DsCycleController@unassignedCompanies' ));
            Route::post('{cycleId}/company/{companyId}/add', array( 'as' => 'digital-star.cycle.addCompany', 'uses' => 'DigitalStar\DsCycleController@addCompany' ));
            Route::post('{cycleId}/company/{companyId}/remove', array( 'as' => 'digital-star.cycle.removeCompany', 'uses' => 'DigitalStar\DsCycleController@removeCompany' ));

            Route::group(array('prefix' => '{cycleId}/assign-forms'), function()
            {
                Route::get('/', array( 'as' => 'digital-star.cycle.assign-form.index', 'uses' => 'DigitalStar\DsCycleAssignFormController@index' ));
                Route::get('list', array( 'as' => 'digital-star.cycle.assign-form.list', 'uses' => 'DigitalStar\DsCycleAssignFormController@list' ));
                Route::get('assignable-forms', array( 'as' => 'digital-star.cycle.assign-form.assignable-forms', 'uses' => 'DigitalStar\DsCycleAssignFormController@assignableForms' ));
                Route::post('{type}/assign', array( 'as' => 'digital-star.cycle.assign-form.assign', 'uses' => 'DigitalStar\DsCycleAssignFormController@assign' ));
            });
        });

        Route::group(array( 'prefix' => 'setups', 'before' => 'vendorManagement.hasPermission:'.\PCK\VendorManagement\VendorManagementUserPermission::TYPE_DIGITAL_STAR ), function()
        {
            Route::get('/', array( 'as' => 'digital-star.setups.index', 'uses' => 'DigitalStar\DsSetupController@index' ));
            Route::get('list', array( 'as' => 'digital-star.setups.list', 'uses' => 'DigitalStar\DsSetupController@list' ));

            Route::group(array('prefix' => '{evaluationId}'), function()
            {
                Route::post('notification/form-assigned/email/send', ['as' => 'digital-star.setups.notification.form-assigned.email.send', 'uses' => 'DigitalStar\DsSetupController@sendFormAssignedEmailNotification']);

                Route::group(array('prefix' => 'evaluators'), function()
                {
                    Route::get('/', array('as' => 'digital-star.setups.evaluators.index', 'uses' => 'DigitalStar\DsSetupCompanyEvaluatorController@index'));
                    Route::get('assigned', array('as' => 'digital-star.setups.evaluators.company.assigned', 'uses' => 'DigitalStar\DsSetupCompanyEvaluatorController@assigned'));

                    Route::get('projects/list', array('as' => 'digital-star.setups.evaluators.projects', 'uses' => 'DigitalStar\DsSetupProjectEvaluatorController@projects'));

                    Route::group(array('prefix' => 'project/{pid}'), function()
                    {
                        Route::get('/', array('as' => 'digital-star.setups.evaluators.project.index', 'uses' => 'DigitalStar\DsSetupProjectEvaluatorController@index'));
                        Route::get('assigned', array('as' => 'digital-star.setups.evaluators.project.assigned', 'uses' => 'DigitalStar\DsSetupProjectEvaluatorController@assigned'));
                        Route::get('unassigned', array('as' => 'digital-star.setups.evaluators.project.unassigned', 'uses' => 'DigitalStar\DsSetupProjectEvaluatorController@unassigned'));
                        Route::post('assign', array('as' => 'digital-star.setups.evaluators.project.assign', 'uses' => 'DigitalStar\DsSetupProjectEvaluatorController@assign'));
                        Route::post('unassign', array('as' => 'digital-star.setups.evaluators.project.unassign', 'uses' => 'DigitalStar\DsSetupProjectEvaluatorController@unassign'));
                    });
                });

                Route::group(array('prefix' => 'processors/company'), function()
                {
                    Route::get('/', array('as' => 'digital-star.setups.processors.company.index', 'uses' => 'DigitalStar\DsSetupProcessorController@index'));
                    Route::get('assigned', array('as' => 'digital-star.setups.processors.company.assigned', 'uses' => 'DigitalStar\DsSetupProcessorController@assigned'));
                    Route::get('unassigned', array('as' => 'digital-star.setups.processors.company.unassigned', 'uses' => 'DigitalStar\DsSetupProcessorController@unassigned'));
                    Route::post('assign', array('as' => 'digital-star.setups.processors.company.assign', 'uses' => 'DigitalStar\DsSetupProcessorController@assign'));
                    Route::post('unassign', array('as' => 'digital-star.setups.processors.company.unassign', 'uses' => 'DigitalStar\DsSetupProcessorController@unassign'));
                });
            });
        });

        // Company evaluation
        Route::group(array( 'prefix' => 'company-evaluations' ), function()
        {
            Route::get('/', array('as' => 'digital-star.evaluation.company.index', 'uses' => 'DigitalStar\DsCompanyEvaluationController@index'));
            Route::get('list', array('as' => 'digital-star.evaluation.company.list', 'uses' => 'DigitalStar\DsCompanyEvaluationController@list'));

            Route::group(array('prefix' => '{evaluationId}'), function()
            {
                Route::group(array('prefix' => 'form/{formId}', 'digitalStar.isCompanyEvaluator'), function()
                {
                    Route::get('score', array('as' => 'digital-star.evaluation.company.score', 'uses' => 'DigitalStar\DsCompanyEvaluationController@getScore'));

                    Route::get('edit', array('as' => 'digital-star.evaluation.company.edit', 'uses' => 'DigitalStar\DsCompanyEvaluationController@edit'));
                    Route::post('update', array('as' => 'digital-star.evaluation.company.update', 'uses' => 'DigitalStar\DsCompanyEvaluationController@update'));

                    Route::get('item/{nodeId}/downloads', array( 'as' => 'digital-star.evaluation.company.node.downloads', 'uses' => 'DigitalStar\DsCompanyEvaluationController@getDownloads'));
                    Route::get('item/{nodeId}/uploads', array( 'as' => 'digital-star.evaluation.company.node.uploads', 'uses' => 'DigitalStar\DsCompanyEvaluationController@getUploads'));
                    Route::post('item/{nodeId}/do-upload', array( 'as' => 'digital-star.evaluation.company.node.doUpload', 'uses' => 'DigitalStar\DsCompanyEvaluationController@doUpload'));
                });
            });
        });

        // Project evaluation
        Route::group(array( 'prefix' => 'project-evaluations' ), function()
        {
            Route::get('/', array('as' => 'digital-star.evaluation.project.index', 'uses' => 'DigitalStar\DsProjectEvaluationController@index'));
            Route::get('list', array('as' => 'digital-star.evaluation.project.list', 'uses' => 'DigitalStar\DsProjectEvaluationController@list'));

            Route::group(array('prefix' => '{evaluationId}'), function()
            {
                Route::group(array('prefix' => 'form/{formId}', 'before' => 'digitalStar.isProjectEvaluator'), function()
                {
                    Route::get('score', array('as' => 'digital-star.evaluation.company.score', 'uses' => 'DigitalStar\DsProjectEvaluationController@getScore'));

                    Route::get('edit', array('as' => 'digital-star.evaluation.project.edit', 'uses' => 'DigitalStar\DsProjectEvaluationController@edit'));
                    Route::post('update', array('as' => 'digital-star.evaluation.project.update', 'uses' => 'DigitalStar\DsProjectEvaluationController@update'));

                    Route::get('item/{nodeId}/downloads', array( 'as' => 'digital-star.evaluation.project.node.downloads', 'uses' => 'DigitalStar\DsProjectEvaluationController@getDownloads'));
                    Route::get('item/{nodeId}/uploads', array( 'as' => 'digital-star.evaluation.project.node.uploads', 'uses' => 'DigitalStar\DsProjectEvaluationController@getUploads'));
                    Route::post('item/{nodeId}/do-upload', array( 'as' => 'digital-star.evaluation.project.node.doUpload', 'uses' => 'DigitalStar\DsProjectEvaluationController@doUpload'));
                });
            });
        });

        // Assign verifiers & Approvals
        Route::group(array( 'prefix' => 'approvals' ), function() {
            // Company
            Route::group(array('prefix' => 'company'), function() {
                // Processor - Assign verifiers
                Route::group(array('prefix' => 'assign-verifiers'), function() {
                    Route::get('/', array('as' => 'digital-star.approval.company.assign-verifiers.index', 'uses' => 'DigitalStar\DsCompanyFormApprovalController@index'));
                    Route::get('list', array('as' => 'digital-star.approval.company.assign-verifiers.list', 'uses' => 'DigitalStar\DsCompanyFormApprovalController@list'));

                    Route::group(array('prefix' => 'form/{formId}', 'before' => 'digitalStar.isVerifierSelector'), function() {
                        Route::get('edit', array('as' => 'digital-star.approval.company.assign-verifiers.edit', 'uses' => 'DigitalStar\DsCompanyFormApprovalController@edit'));
                        Route::post('update', array('as' => 'digital-star.approval.company.assign-verifiers.update', 'uses' => 'DigitalStar\DsCompanyFormApprovalController@update'));
                    });
                });

                // Verifier - Approval
                Route::group(array('prefix' => 'approve'), function() {
                    Route::get('/', array('as' => 'digital-star.approval.company.approve.index', 'uses' => 'DigitalStar\DsCompanyFormApprovalController@index'));
                    Route::get('list', array('as' => 'digital-star.approval.company.approve.list', 'uses' => 'DigitalStar\DsCompanyFormApprovalController@list'));

                    Route::group(array('prefix' => 'form/{formId}', 'before' => 'digitalStar.isVerifier'), function() {
                        Route::get('edit', array('as' => 'digital-star.approval.company.approve.edit', 'uses' => 'DigitalStar\DsCompanyFormApprovalController@edit'));
                    });
                });
            });

            // Project
            Route::group(array('prefix' => 'project'), function() {
                // Evaluator - Assign verifiers
                Route::group(array('prefix' => 'assign-verifiers'), function() {
                    Route::get('/', array('as' => 'digital-star.approval.project.assign-verifiers.index', 'uses' => 'DigitalStar\DsProjectFormApprovalController@index'));
                    Route::get('list', array('as' => 'digital-star.approval.project.assign-verifiers.list', 'uses' => 'DigitalStar\DsProjectFormApprovalController@list'));

                    Route::group(array('prefix' => 'form/{formId}', 'before' => 'digitalStar.isVerifierSelector'), function() {
                        Route::get('edit', array('as' => 'digital-star.approval.project.assign-verifiers.edit', 'uses' => 'DigitalStar\DsProjectFormApprovalController@edit'));
                        Route::post('update', array('as' => 'digital-star.approval.project.assign-verifiers.update', 'uses' => 'DigitalStar\DsProjectFormApprovalController@update'));
                    });
                });

                // Verifier - Approval
                Route::group(array('prefix' => 'approve'), function() {
                    Route::get('/', array('as' => 'digital-star.approval.project.approve.index', 'uses' => 'DigitalStar\DsProjectFormApprovalController@index'));
                    Route::get('list', array('as' => 'digital-star.approval.project.approve.list', 'uses' => 'DigitalStar\DsProjectFormApprovalController@list'));

                    Route::group(array('prefix' => 'form/{formId}', 'before' => 'digitalStar.isVerifier'), function() {
                        Route::get('edit', array('as' => 'digital-star.approval.project.approve.edit', 'uses' => 'DigitalStar\DsProjectFormApprovalController@edit'));
                    });
                });
            });
        });

        Route::group(array('prefix' => 'star-rating'), function() {
            Route::group(array('prefix' => '{companyId}'), function() {
                Route::get('list', array('as' => 'digital-star.star-rating.list', 'uses' => 'DigitalStar\DsStarRatingController@list'));

                Route::group(array('prefix' => 'cycle/{cycleId}'), function() {
                    Route::group(array('prefix' => 'company'), function() {
                        Route::get('/', array('as' => 'digital-star.star-rating.cycle.company', 'uses' => 'DigitalStar\DsStarRatingController@company'));
                    });

                    Route::group(array('prefix' => 'project'), function() {
                        Route::get('/', array('as' => 'digital-star.star-rating.cycle.project', 'uses' => 'DigitalStar\DsStarRatingController@project'));
                    });

                    Route::group(array('prefix' => 'form/{formId}'), function() {
                        Route::get('form', array('as' => 'digital-star.star-rating.cycle.form', 'uses' => 'DigitalStar\DsStarRatingController@evaluationForm'));
                        Route::get('form-info', array('as' => 'digital-star.star-rating.cycle.form-info', 'uses' => 'DigitalStar\DsStarRatingController@evaluationFormInfo'));
                    });
                });
            });
        });

        Route::group(array('prefix' => 'logs'), function() {
            Route::group(array('prefix' => 'form/{formId}'), function() {
                Route::get('evaluation-log', array('as' => 'digital-star.log.evaluation', 'uses' => 'DigitalStar\DsLogController@evaluationLog'));
                Route::get('verifier-log', array('as' => 'digital-star.log.verifier', 'uses' => 'DigitalStar\DsLogController@verifierLog'));
            });
        });

        Route::group(array('prefix' => 'dashboard'), function() {
            Route::get('/', array('as' => 'digital-star.dashboard.index', 'uses' => 'DigitalStar\DsDashboardController@index'));
            Route::get('charts', array('as' => 'digital-star.dashboard.charts', 'uses' => 'DigitalStar\DsDashboardController@getCharts'));
            Route::get('stats/{type}', array('as' => 'digital-star.dashboard.stats', 'uses' => 'DigitalStar\DsDashboardController@getStats'));
        });
    });
});