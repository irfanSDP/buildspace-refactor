<?php
Route::group(array( 'prefix' => 'vendor-performance-evaluation' ), function()
{
    Route::group(array( 'prefix' => 'form-approvals/forms' ), function(){
        Route::get('/', array( 'as' => 'vendorPerformanceEvaluation.companyForms.approval', 'uses' => 'VendorPerformanceEvaluationCompanyFormsApprovalController@index' ));
        Route::get('/list', array( 'as' => 'vendorPerformanceEvaluation.companyForms.approval.list', 'uses' => 'VendorPerformanceEvaluationCompanyFormsApprovalController@list' ));

        Route::group(array('before' => 'vendorPerformanceEvaluation.isFormApprover'), function(){
            Route::get('{companyFormId}/edit', array( 'as' => 'vendorPerformanceEvaluation.companyForms.approval.edit', 'uses' => 'VendorPerformanceEvaluationCompanyFormsApprovalController@edit' ));
            Route::get('{companyFormId}/submitter/edit', array( 'as' => 'vendorPerformanceEvaluation.companyForms.approval.submitter.edit', 'uses' => 'VendorPerformanceEvaluationCompanyFormsApprovalController@submitterEdit' ));
            Route::post('evaluation/{evaluationId}/company/{companyFormId}/submitter/update', array( 'as' => 'vendorPerformanceEvaluation.companyForms.approval.submitter.update', 'uses' => 'VendorPerformanceEvaluationCompanyFormsController@update' ));
            Route::post('{companyFormId}', array( 'as' => 'vendorPerformanceEvaluation.companyForms.approval.update', 'uses' => 'VendorPerformanceEvaluationCompanyFormsApprovalController@update' ));
        });
    
        Route::group(array( 'prefix' => '{companyFormId}/editLog' ), function() {
            Route::get('/', array( 'as' => 'vendorPerformanceEvaluation.processor.edit.logs.get', 'uses' => 'VendorPerformanceEvaluationCompanyFormsApprovalController@getProcessorEditLogs' ));
            Route::get('{editLogId}/getEditDetails', array( 'as' => 'vendorPerformanceEvaluation.processor.edit.details.get', 'uses' => 'VendorPerformanceEvaluationCompanyFormsApprovalController@getEditDetails' ));
        });

        Route::group(array( 'prefix' => '{companyFormId}/evaluationLog' ), function() {
            Route::get('/', array( 'as' => 'vendorPerformanceEvaluation.evaluation.logs.get', 'uses' => 'VendorPerformanceEvaluationCompanyFormsApprovalController@getCompanyFormEvaluationLogs' ));
        });
    });

    Route::group(array( 'prefix' => 'template-forms', 'before' => 'vendorManagement.hasPermission:'.\PCK\VendorManagement\VendorManagementUserPermission::TYPE_FORM_TEMPLATES ), function()
    {
        Route::get('/', array( 'as' => 'vendorPerformanceEvaluation.templateForms', 'uses' => 'VendorPerformanceEvaluationTemplateFormsController@index' ));
        Route::get('create', array( 'as' => 'vendorPerformanceEvaluation.templateForms.create', 'uses' => 'VendorPerformanceEvaluationTemplateFormsController@create' ));
        Route::post('/', array( 'as' => 'vendorPerformanceEvaluation.templateForms.store', 'uses' => 'VendorPerformanceEvaluationTemplateFormsController@store' ));
        Route::get('{templateFormId}', array( 'as' => 'vendorPerformanceEvaluation.templateForms.edit', 'uses' => 'VendorPerformanceEvaluationTemplateFormsController@edit' ));
        Route::post('{templateFormId}', array( 'as' => 'vendorPerformanceEvaluation.templateForms.update', 'uses' => 'VendorPerformanceEvaluationTemplateFormsController@update' ));
        Route::get('{templateFormId}/approval', array( 'as' => 'vendorPerformanceEvaluation.templateForms.approval', 'uses' => 'VendorPerformanceEvaluationTemplateFormsController@approval' ));
        Route::post('{templateFormId}/approval', array( 'as' => 'vendorPerformanceEvaluation.templateForms.approve', 'uses' => 'VendorPerformanceEvaluationTemplateFormsController@approve' ));
        Route::get('{templateFormId}/template', array( 'as' => 'vendorPerformanceEvaluation.templateForms.template', 'uses' => 'VendorPerformanceEvaluationTemplateFormsController@template' ));
        Route::get('{templateFormId}/new-revision', array( 'as' => 'vendorPerformanceEvaluation.templateForms.newRevision', 'uses' => 'VendorPerformanceEvaluationTemplateFormsController@newRevision' ));
        Route::get('{templateFormId}/clone', array( 'as' => 'vendorPerformanceEvaluation.templateForms.clone', 'uses' => 'VendorPerformanceEvaluationTemplateFormsController@clone' ));
        Route::get('{templateFormId}/grade', array( 'as' => 'vendorPerformanceEvaluation.templateForms.grade', 'uses' => 'VendorPerformanceEvaluationTemplateFormsController@grade' ));
        Route::post('{templateFormId}/grade', array( 'as' => 'vendorPerformanceEvaluation.templateForms.updateGrade', 'uses' => 'VendorPerformanceEvaluationTemplateFormsController@updateGrade' ));

        Route::group( array('prefix' => '{templateFormId}'), function()
        {
            Route::group( array('prefix' => 'parent-nodes/{parentNodeId}/nodes'), function()
            {
                Route::get('/', array( 'as' => 'vendorPerformanceEvaluation.templateForm.nodes', 'uses' => 'VendorPerformanceEvaluationTemplateFormNodesController@index' ));
                Route::get('list', array( 'as' => 'vendorPerformanceEvaluation.templateForm.nodes.list', 'uses' => 'VendorPerformanceEvaluationTemplateFormNodesController@list' ));
                Route::post('/', array( 'as' => 'vendorPerformanceEvaluation.templateForm.nodes.storeOrUpdate', 'uses' => 'VendorPerformanceEvaluationTemplateFormNodesController@storeOrUpdate' ));
                Route::delete('{weightedNodeId}', array( 'as' => 'vendorPerformanceEvaluation.templateForm.delete', 'uses' => 'VendorPerformanceEvaluationTemplateFormNodesController@destroy' ));
            });

            Route::group(array( 'prefix' => 'nodes/{weightedNodeId}/scores' ), function()
            {
                Route::get('/', array( 'as' => 'vendorPerformanceEvaluation.templateForm.node.scores', 'uses' => 'VendorPerformanceEvaluationTemplateFormScoresController@index' ));
                Route::get('list', array( 'as' => 'vendorPerformanceEvaluation.templateForm.nodes.scores.list', 'uses' => 'VendorPerformanceEvaluationTemplateFormScoresController@list' ));
                Route::post('/', array( 'as' => 'vendorPerformanceEvaluation.templateForm.nodes.scores.storeOrUpdate', 'uses' => 'VendorPerformanceEvaluationTemplateFormScoresController@storeOrUpdate' ));
                Route::delete('{scoreId}', array( 'as' => 'vendorPerformanceEvaluation.templateForm.node.scores.delete', 'uses' => 'VendorPerformanceEvaluationTemplateFormScoresController@destroy' ));
            });
        });
    });

    Route::group(array( 'prefix' => 'cycles', 'before' => 'vendorManagement.hasPermission:'.\PCK\VendorManagement\VendorManagementUserPermission::TYPE_PERFORMANCE_EVALUATION ), function()
    {
        Route::get('/', array( 'as' => 'vendorPerformanceEvaluation.cycle.index', 'uses' => 'VendorPerformanceEvaluationCyclesController@index' ));
        Route::get('list', array( 'as' => 'vendorPerformanceEvaluation.cycle.list', 'uses' => 'VendorPerformanceEvaluationCyclesController@list' ));
        Route::get('create', array( 'as' => 'vendorPerformanceEvaluation.cycle.create', 'uses' => 'VendorPerformanceEvaluationCyclesController@create' ));
        Route::post('store', array( 'as' => 'vendorPerformanceEvaluation.cycle.store', 'uses' => 'VendorPerformanceEvaluationCyclesController@store' ));
        Route::get('{cycleId}/edit', array( 'as' => 'vendorPerformanceEvaluation.cycle.edit', 'uses' => 'VendorPerformanceEvaluationCyclesController@edit' ));
        Route::post('{cycleId}', array( 'as' => 'vendorPerformanceEvaluation.cycle.update', 'uses' => 'VendorPerformanceEvaluationCyclesController@update' ));
        Route::get('{cycleId}/assigned-projects', array( 'as' => 'vendorPerformanceEvaluation.cycle.assignedProjects', 'uses' => 'VendorPerformanceEvaluationCyclesController@assignedProjects' ));
        Route::get('{cycleId}/unassigned-projects', array( 'as' => 'vendorPerformanceEvaluation.cycle.unassignedProjects', 'uses' => 'VendorPerformanceEvaluationCyclesController@unassignedProjects' ));
        Route::post('{cycleId}/projects/{projectId}/add', array( 'as' => 'vendorPerformanceEvaluation.cycle.addProject', 'uses' => 'VendorPerformanceEvaluationCyclesController@addProject' ));
        Route::post('{cycleId}/projects/{projectId}/remove', array( 'as' => 'vendorPerformanceEvaluation.cycle.removeProject', 'uses' => 'VendorPerformanceEvaluationCyclesController@removeProject' ));

        Route::get('{cycleId}/form-change-requests', array( 'as' => 'vendorPerformanceEvaluation.cycle.formChangeRequests', 'uses' => 'VendorPerformanceEvaluationCyclesController@formChangeRequests' ));
        Route::get('{cycleId}/form-changes', array( 'as' => 'vendorPerformanceEvaluation.cycle.formChanges', 'uses' => 'VendorPerformanceEvaluationCyclesController@formChanges' ));
        Route::get('{cycleId}/removal-requests', array( 'as' => 'vendorPerformanceEvaluation.cycle.removalRequests', 'uses' => 'VendorPerformanceEvaluationCyclesController@removalRequests' ));
    });

    Route::group(array( 'prefix' => 'setups', 'before' => 'vendorManagement.hasPermission:'.\PCK\VendorManagement\VendorManagementUserPermission::TYPE_PERFORMANCE_EVALUATION ), function()
    {
        Route::get('/', array( 'as' => 'vendorPerformanceEvaluation.setups.index', 'uses' => 'VendorPerformanceEvaluationSetupsController@index' ));
        Route::get('list', array( 'as' => 'vendorPerformanceEvaluation.setups.list', 'uses' => 'VendorPerformanceEvaluationSetupsController@list' ));
        Route::get('list/export', array( 'as' => 'vendorPerformanceEvaluation.setups.list.export', 'uses' => 'VendorPerformanceEvaluationSetupsController@listExport' ));
        Route::get('{evaluationId}/edit', array( 'as' => 'vendorPerformanceEvaluation.setups.edit', 'uses' => 'VendorPerformanceEvaluationSetupsController@edit' ));
        Route::post('{evaluationId}/resendVpeFormAssignedEmailNotification', ['as' => 'vendorPerformanceEvaluation.setups.vpe.form.assigned.email.send', 'uses' => 'VendorPerformanceEvaluationSetupsController@resendVpeFormAssignedEmailNotification']);
        Route::post('{evaluationId}', array( 'as' => 'vendorPerformanceEvaluation.setups.update', 'uses' => 'VendorPerformanceEvaluationSetupsController@update' ));
        Route::group(array( 'prefix' => '{evaluationId}/vendors'), function(){
            Route::get('/', array( 'as' => 'vendorPerformanceEvaluation.setups.evaluations.vendors.index', 'uses' => 'VendorPerformanceEvaluationSetupVendorsController@index' ));
            Route::get('list', array( 'as' => 'vendorPerformanceEvaluation.setups.evaluations.vendors.list', 'uses' => 'VendorPerformanceEvaluationSetupVendorsController@list' ));
            Route::get('{companyId}/edit', array( 'as' => 'vendorPerformanceEvaluation.setups.evaluations.vendors.edit', 'uses' => 'VendorPerformanceEvaluationSetupVendorsController@edit' ));
            Route::post('{companyId}', array( 'as' => 'vendorPerformanceEvaluation.setups.evaluations.vendors.update', 'uses' => 'VendorPerformanceEvaluationSetupVendorsController@update' ));
            Route::get('{companyId}/forms', array( 'as' => 'vendorPerformanceEvaluation.setups.evaluations.forms', 'uses' => 'VendorPerformanceEvaluationSetupVendorsController@listForms' ));
            Route::get('{companyId}/forms/options', array( 'as' => 'vendorPerformanceEvaluation.setups.evaluations.forms.options', 'uses' => 'VendorPerformanceEvaluationSetupVendorsController@listFormsOptions' ));
            Route::post('{companyId}/forms/{setupId}', array( 'as' => 'vendorPerformanceEvaluation.setups.evaluations.forms.update', 'uses' => 'VendorPerformanceEvaluationSetupVendorsController@updateForm' ));
            Route::delete('{companyId}/forms/{setupId}', array( 'as' => 'vendorPerformanceEvaluation.setups.evaluations.forms.delete', 'uses' => 'VendorPerformanceEvaluationSetupVendorsController@deleteForm' ));
        });
    });

    Route::group(array( 'prefix' => 'evaluations' ), function()
    {
        Route::get('/', array( 'as' => 'vendorPerformanceEvaluation.index', 'uses' => 'VendorPerformanceEvaluationsController@index' ));

        Route::group(array( 'prefix' => '{evaluationId}'), function(){
            Route::get('evaluators', array( 'before' => 'vendorPerformanceEvaluation.isProjectEditor', 'as' => 'vendorPerformanceEvaluation.evaluations.evaluators.edit', 'uses' => 'VendorPerformanceEvaluatorsController@edit' ));
            Route::post('evaluators', array( 'before' => 'vendorPerformanceEvaluation.isProjectEditor', 'as' => 'vendorPerformanceEvaluation.evaluations.evaluators.update', 'uses' => 'VendorPerformanceEvaluatorsController@update' ));

            Route::get('forms', array( 'as' => 'vendorPerformanceEvaluation.evaluations.forms', 'uses' => 'VendorPerformanceEvaluationCompanyFormsController@index' ));
            Route::get('forms/list', array( 'as' => 'vendorPerformanceEvaluation.evaluations.forms.list', 'uses' => 'VendorPerformanceEvaluationCompanyFormsController@list' ));
            Route::get('forms/{formId}', array( 'before' => 'vendorPerformanceEvaluation.isEvaluator', 'as' => 'vendorPerformanceEvaluation.evaluations.forms.edit', 'uses' => 'VendorPerformanceEvaluationCompanyFormsController@edit' ));
            Route::post('forms/{formId}', array( 'before' => 'vendorPerformanceEvaluation.isEvaluator', 'as' => 'vendorPerformanceEvaluation.evaluations.forms.update', 'uses' => 'VendorPerformanceEvaluationCompanyFormsController@update' ));
            Route::post('forms/{formId}/change-request', array( 'before' => 'companyAdminAccessLevel|vendorPerformanceEvaluation.isEvaluator', 'as' => 'vendorPerformanceEvaluation.evaluations.forms.changeRequest', 'uses' => 'VendorPerformanceEvaluationCompanyFormsController@formChangeRequest' ));
            Route::get('forms/{formId}/getLiveVpeScore', array( 'as' => 'vendorPerformanceEvaluation.evaluations.forms.vpe.live.score.get', 'uses' => 'VendorPerformanceEvaluationCompanyFormsController@getLiveVpeScore' ));

            Route::get('removal-request', array( 'before' => 'vendorPerformanceEvaluation.isProjectEditor|vendorPerformanceEvaluation.isEvaluator', 'as' => 'vendorPerformanceEvaluation.evaluations.removalRequest.create', 'uses' => 'VendorPerformanceEvaluationRemovalRequestsController@create' ));
            Route::post('removal-request', array( 'before' => 'vendorPerformanceEvaluation.isProjectEditor|vendorPerformanceEvaluation.isEvaluator', 'as' => 'vendorPerformanceEvaluation.evaluations.removalRequest.store', 'uses' => 'VendorPerformanceEvaluationRemovalRequestsController@store' ));
        });
    });

    Route::get('evaluation-removal-requests', array( 'before' => 'vendorManagement.hasPermission:'.\PCK\VendorManagement\VendorManagementUserPermission::TYPE_PERFORMANCE_EVALUATION, 'as' => 'vendorPerformanceEvaluation.evaluations.removalRequest.index', 'uses' => 'VendorPerformanceEvaluationRemovalRequestsController@index' ));
    Route::post('evaluation-removal-requests/{removalRequestId}', array( 'before' => 'vendorManagement.hasPermission:'.\PCK\VendorManagement\VendorManagementUserPermission::TYPE_PERFORMANCE_EVALUATION, 'as' => 'vendorPerformanceEvaluation.evaluations.removalRequest.destroy', 'uses' => 'VendorPerformanceEvaluationRemovalRequestsController@destroy' ));
});