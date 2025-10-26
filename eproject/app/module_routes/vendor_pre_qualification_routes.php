<?php
Route::group(array( 'prefix' => 'vendor-pre-qualification', 'before' => 'vendorManagement.hasPermission:'.\PCK\VendorManagement\VendorManagementUserPermission::TYPE_FORM_TEMPLATES ), function()
{
    Route::group(array( 'prefix' => 'form-library/vendor-groups' ), function()
    {
        Route::get('/', array( 'as' => 'vendorPreQualification.formLibrary.index', 'uses' => 'VendorPreQualificationFormLibraryVendorGroupsController@index' ));

        Route::group(array( 'prefix' => '{vendorGroupId}/vendor-work-category-forms' ), function()
        {
            Route::get('/', array( 'as' => 'vendorPreQualification.formLibrary.vendorWorkCategories.index', 'uses' => 'VendorPreQualificationFormLibraryVendorWorkCategoriesController@index' ));

            Route::group(array( 'prefix' => '{vendorWorkCategoryId}' ), function()
            {
                Route::get('forms/create', array( 'as' => 'vendorPreQualification.formLibrary.form.create', 'uses' => 'VendorPreQualificationTemplateFormsController@createForm' ));
                Route::post('forms/store', array( 'as' => 'vendorPreQualification.formLibrary.form.store', 'uses' => 'VendorPreQualificationTemplateFormsController@storeForm' ));
                Route::get('forms/clone-form', array( 'as' => 'vendorPreQualification.formLibrary.form.clone-form', 'uses' => 'VendorPreQualificationTemplateFormsController@cloneForm' ));
                Route::post('forms/clone', array( 'as' => 'vendorPreQualification.formLibrary.form.clone', 'uses' => 'VendorPreQualificationTemplateFormsController@saveCloneForm' ));
                Route::get('forms/{formId}/edit', array( 'as' => 'vendorPreQualification.formLibrary.form.edit', 'uses' => 'VendorPreQualificationTemplateFormsController@editForm' ));
                Route::post('forms/{formId}/update', array( 'as' => 'vendorPreQualification.formLibrary.form.update', 'uses' => 'VendorPreQualificationTemplateFormsController@updateForm' ));

                Route::get('template', array( 'as' => 'vendorPreQualification.formLibrary.form.template', 'uses' => 'VendorPreQualificationTemplateFormsController@template' ));
                Route::get('new-revision', array( 'as' => 'vendorPreQualification.formLibrary.form.newRevision', 'uses' => 'VendorPreQualificationTemplateFormsController@newRevision' ));
                
                // Show form
                Route::group(array( 'prefix' => 'parent-nodes/{parentNodeId}' ), function(){
                    Route::get('/', array( 'as' => 'vendorPreQualification.formLibrary.form.node', 'uses' => 'VendorPreQualificationTemplateFormsController@index' ));
                    Route::get('list', array( 'as' => 'vendorPreQualification.formLibrary.form.node.children', 'uses' => 'VendorPreQualificationTemplateFormsController@list' ));
                    Route::post('/', array( 'as' => 'vendorPreQualification.formLibrary.form.node.storeOrUpdate', 'uses' => 'VendorPreQualificationTemplateFormsController@storeOrUpdate' ));
                    Route::delete('{weightedNodeId}', array( 'as' => 'vendorPreQualification.formLibrary.form.node.delete', 'uses' => 'VendorPreQualificationTemplateFormsController@destroy' ));
                });

                Route::get('approval', array( 'as' => 'vendorPreQualification.formLibrary.form.approval', 'uses' => 'VendorPreQualificationTemplateFormsController@approval' ));
                Route::post('approval', array( 'as' => 'vendorPreQualification.formLibrary.form.approval.submit', 'uses' => 'VendorPreQualificationTemplateFormsController@submitForApproval' ));
                Route::post('approval/verify', array( 'as' => 'vendorPreQualification.formLibrary.form.verify', 'uses' => 'VendorPreQualificationTemplateFormsController@verify' ));

                Route::group(array( 'prefix' => 'nodes/{weightedNodeId}/scores' ), function(){
                    Route::get('/', array( 'as' => 'vendorPreQualification.formLibrary.form.node.scores', 'uses' => 'VendorPreQualificationTemplateFormScoresController@index' ));
                    Route::get('list', array( 'as' => 'vendorPreQualification.formLibrary.form.node.scores.list', 'uses' => 'VendorPreQualificationTemplateFormScoresController@list' ));
                    Route::post('/', array( 'as' => 'vendorPreQualification.formLibrary.form.node.scores.storeOrUpdate', 'uses' => 'VendorPreQualificationTemplateFormScoresController@storeOrUpdate' ));
                    Route::delete('{scoreId}', array( 'as' => 'vendorPreQualification.formLibrary.form.node.scores.delete', 'uses' => 'VendorPreQualificationTemplateFormScoresController@destroy' ));
                });
            });
        });
    });

    Route::group(array( 'prefix' => 'form-mappings' ), function()
    {
        Route::get('vendor-pre-qualification', array( 'as' => 'vendorPreQualification.formMapping', 'uses' => 'VendorPreQualificationFormMappingsController@index' ));
        Route::get('vendor-pre-qualification/setup/edit', array( 'as' => 'vendorPreQualification.formMapping.edit', 'uses' => 'VendorPreQualificationFormMappingsController@edit' ));
        Route::post('vendor-pre-qualification/setup/update', array( 'as' => 'vendorPreQualification.formMapping.update', 'uses' => 'VendorPreQualificationFormMappingsController@update' ));
    });

    Route::group(array( 'prefix' => 'grades' ), function()
    {
        Route::get('/', array( 'as' => 'vendorPreQualification.grades', 'uses' => 'VendorPreQualificationVendorGroupGradesController@index' ));
        Route::get('list', array( 'as' => 'vendorPreQualification.grades.list', 'uses' => 'VendorPreQualificationVendorGroupGradesController@list' ));
        Route::post('contract-group-category/{contractGroupCategoryId}', array( 'as' => 'vendorPreQualification.grades.update', 'uses' => 'VendorPreQualificationVendorGroupGradesController@update' ));
        Route::delete('contract-group-category/{contractGroupCategoryId}', array( 'as' => 'vendorPreQualification.grades.delete', 'uses' => 'VendorPreQualificationVendorGroupGradesController@delete' ));

        Route::get('grades-list', array( 'as' => 'vendorPreQualification.grades.gradesList', 'uses' => 'VendorPreQualificationVendorGroupGradesController@gradesList' ));
        Route::get('grade/{vendorManagementGradeId}/preview', array( 'as' => 'vendorPreQualification.grades.gradePreview', 'uses' => 'VendorPreQualificationVendorGroupGradesController@gradePreview' ));
    });
});