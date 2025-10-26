<?php
Route::group(array( 'before' => 'systemModule.inspection.enabled' ), function()
{
    Route::group(array( 'prefix' => 'inspection-list' ), function()
    {
        Route::get('/', ['as' => 'project.inspection.list.index', 'uses' => 'InspectionListsController@index']);
        Route::get('/getProjectInspectionLists', ['as' => 'project.inspection.lists.get', 'uses' => 'InspectionListsController@getProjectInspectionLists']);
        Route::post('/store', ['as' => 'project.inspection.list.store', 'uses' => 'InspectionListsController@store']);
        Route::post('/cloneToProjectInpsectionList', ['as' => 'clone.master.inspection.list.categories', 'uses' => 'InspectionListCategoriesController@cloneToProjectInspectionList']);

        Route::group(['prefix' => '/{inspectionListId}'], function() {
            Route::post('/update', ['as' => 'project.inspection.list.update', 'uses' => 'InspectionListsController@update']);
            Route::post('/delete', ['as' => 'project.inspection.list.delete', 'uses' => 'InspectionListsController@destroy']);
            Route::get('/getInspectionListCategory', ['as' => 'project.inspection.list.categories.get', 'uses' => 'InspectionListsController@getInspectionListCategories']);
        });

        Route::group(['prefix' => '/inspectionListCategory'], function() {
            Route::group(['prefix' => '/{inspectionListCategoryId}'], function() {
                Route::get('/getCategoryChildren', ['as' => 'project.inspection.list.category.children.get', 'uses' => 'InspectionListCategoriesController@getCategoryChildren']);
                Route::get('/getAdditionalFields', ['as' => 'project.inspection.list.category.additional.fields.get', 'uses' => 'InspectionListsCategoryAdditionalFieldsController@getAdditionalFields']);
                Route::get('/getInspectionListItems', ['as' => 'project.inspection.list.items.get', 'uses' => 'InspectionListItemsController@getInspectionListItems']);
                Route::post('/categoryDelete', ['as' => 'project.inspection.list.category.delete', 'uses' => 'InspectionListCategoriesController@categoryDelete']);
                Route::get('changeListCategoryTypeCheck', ['as' => 'project.inspection.list.category.change.type.check', 'uses' => 'InspectionListCategoriesController@changeListCategoryTypeCheck']);
            });

            Route::post('/categoryAdd', ['as' => 'project.inspection.list.category.add', 'uses' => 'InspectionListCategoriesController@categoryAdd']);
            Route::post('/categoryUpdate', ['as' => 'project.inspection.list.category.update', 'uses' => 'InspectionListCategoriesController@categoryUpdate']);
        });

        Route::group(['prefix' => '/inspectionListItem'], function() {
            Route::group(['prefix' => '/{inspectionListItemId}'], function() {
                Route::get('/getInspectionListItemChildren', ['as' => 'project.inspection.list.item.children.get', 'uses' => 'InspectionListItemsController@getInspectionListItemChildren']);
                Route::post('/itemDelete', ['as' => 'project.inspection.list.item.delete', 'uses' => 'InspectionListItemsController@itemDelete']);
                Route::get('/changeListItemTypeCheck', ['as' => 'project.inspection.list.item.change.type.check', 'uses' => 'InspectionListItemsController@changeListItemTypeCheck']);
            });

            Route::post('/itemAdd', ['as' => 'project.inspection.list.item.add', 'uses' => 'InspectionListItemsController@itemAdd']);
            Route::post('/itemUpdate', ['as' => 'project.inspection.list.item.update', 'uses' => 'InspectionListItemsController@itemUpdate']);
        });

        Route::group(['prefix' => '/additionalField'], function() {
            Route::group(['prefix' => '/{additionalFieldId}'], function() {
                Route::post('/fieldDelete', ['as' => 'project.inspection.list.category.additional.field.delete', 'uses' => 'InspectionListsCategoryAdditionalFieldsController@fieldDelete']);
            });

            Route::post('/fieldAdd', ['as' => 'project.inspection.list.category.additional.field.add', 'uses' => 'InspectionListsCategoryAdditionalFieldsController@fieldAdd']);
            Route::post('/fieldUpdate', ['as' => 'project.inspection.list.category.additional.field.update', 'uses' => 'InspectionListsCategoryAdditionalFieldsController@fieldUpdate']);
        });
    });

    Route::group(array('prefix' => 'inspection', 'before' => 'moduleAccess:' . \PCK\ModulePermission\ModulePermission::MODULE_ID_INSPECTION), function(){
        Route::get('user-management', array( 'as' => 'inspection.userManagement', 'uses' => 'InspectionUserManagementController@edit'));
        Route::get('groups', array( 'as' => 'inspection.groups', 'uses' => 'InspectionGroupsController@index'));
        Route::post('groups', array( 'as' => 'inspection.groups.store', 'uses' => 'InspectionGroupsController@store'));
        Route::post('groups/{groupId}', array( 'as' => 'inspection.groups.update', 'uses' => 'InspectionGroupsController@update'));
        Route::delete('groups/{groupId}', array( 'as' => 'inspection.groups.delete', 'uses' => 'InspectionGroupsController@destroy'));
        Route::get('roles', array( 'as' => 'inspection.roles', 'uses' => 'InspectionRolesController@index'));
        Route::post('roles', array( 'as' => 'inspection.roles.store', 'uses' => 'InspectionRolesController@store'));
        Route::post('roles/{roleId}', array( 'as' => 'inspection.roles.update', 'uses' => 'InspectionRolesController@update'));
        Route::delete('roles/{roleId}', array( 'as' => 'inspection.roles.delete', 'uses' => 'InspectionRolesController@destroy'));
        Route::get('groups-users', array( 'as' => 'inspection.groups.users', 'uses' => 'InspectionGroupUsersController@index'));
        Route::post('groups-users/{userId}', array( 'as' => 'inspection.groups.users.update', 'uses' => 'InspectionGroupUsersController@update'));
        Route::get('submitters', array( 'as' => 'inspection.submitters', 'uses' => 'InspectionSubmittersController@index'));
        Route::post('submitters/{userId}', array( 'as' => 'inspection.submitters.update', 'uses' => 'InspectionSubmittersController@update'));
        Route::get('verifier-template', array( 'as' => 'inspection.verifierTemplate.users', 'uses' => 'InspectionVerifierTemplateController@index'));
        Route::get('verifier-template/unassigned', array( 'as' => 'inspection.verifierTemplate.users.unassigned', 'uses' => 'InspectionVerifierTemplateController@unassignedIndex'));
        Route::post('verifier-template', array( 'as' => 'inspection.verifierTemplate.users.update', 'uses' => 'InspectionVerifierTemplateController@update'));
        Route::get('group-list-categories', array( 'as' => 'inspection.groups.listCategories', 'uses' => 'InspectionGroupInspectionListCategoryController@index'));
        Route::get('group-list-categories/not', array( 'as' => 'inspection.groups.listCategories.not', 'uses' => 'InspectionGroupInspectionListCategoryController@notIndex'));
        Route::post('group-list-categories', array( 'as' => 'inspection.groups.listCategories.update', 'uses' => 'InspectionGroupInspectionListCategoryController@update'));
    });

    Route::group(array('prefix' => 'request-for-inspection'), function(){
        Route::group(array('before' => 'inspection.hasModuleAccess'), function(){
            Route::get('/', array( 'as' => 'inspection.request', 'uses' => 'RequestForInspectionController@index'));
            Route::get('getRequestForInspections', array( 'as' => 'inspection.requests.all.get', 'uses' => 'RequestForInspectionController@getRequestForInspections' ));
        });

        Route::group(array('before' => 'inspection.canRequestInspection'), function(){
            Route::get('getLocationByLevel', array( 'as' => 'inspection.getLocationByLevel', 'uses' => 'RequestForInspectionController@getLocationByLevel' ));
            Route::get('getInspectionListByLevel', array( 'as' => 'inspection.getInspectionListByLevel', 'uses' => 'RequestForInspectionController@getInspectionListByLevel' ));
            Route::get('list-category-form-details', array( 'as' => 'inspection.request.listCategoryFormDetails', 'uses' => 'RequestForInspectionController@listCategoryFormDetails'));
            Route::get('create', array( 'as' => 'inspection.request.create', 'uses' => 'RequestForInspectionController@create'));
            Route::post('create', array( 'as' => 'inspection.request.store', 'uses' => 'RequestForInspectionController@store'));

            Route::group(array('before' => 'inspection.requestForInspectionInDraft'), function(){
                Route::get('{requestForInspectionId}/edit', array( 'as' => 'inspection.request.edit', 'uses' => 'RequestForInspectionController@edit'));
                Route::put('{requestForInspectionId}', array( 'as' => 'inspection.request.update', 'uses' => 'RequestForInspectionController@update'));
            });
        });

        Route::group(array('prefix' => '{requestForInspectionId}', 'before' => 'inspection.hasModuleAccess'), function(){
            Route::get('overview', array( 'as' => 'inspection.overview', 'uses' => 'InspectionOverviewController@show' ));
        });

        Route::group(array('prefix' => '{requestForInspectionId}/inspection'), function(){
            Route::group(array('prefix' => '{inspectionId}'), function(){
                Route::group(array('before' => 'inspection.isDraft|inspection.canRequestInspection'), function(){
                    Route::get('ready-date', array( 'as' => 'inspection.edit', 'uses' => 'InspectionsController@edit'));
                    Route::post('ready-date', array( 'as' => 'inspection.update', 'uses' => 'InspectionsController@update'));
                });

                Route::group(array('before' => 'inspection.isNotDraft|inspection.hasInspectorRole'), function(){
                    Route::get('inspect', array( 'as' => 'inspection.inspect', 'uses' => 'InspectionsController@inspect'));
                    Route::post('inspect', array( 'as' => 'inspection.inspect.update', 'uses' => 'InspectionsController@inspectUpdate'));
                    Route::post('item/{itemId}', array( 'as' => 'inspection.inspect.itemUpdate', 'uses' => 'InspectionItemResultsController@update'));
                    Route::get('item/{itemId}/attachments', array( 'as' => 'inspection.inspect.item.attachmentsList', 'uses' => 'InspectionItemResultsController@attachmentsList'));
                    Route::get('item/{itemId}/uploads', array( 'as' => 'inspection.inspect.item.uploads', 'uses' => 'InspectionItemResultsController@getUploads'));
                    Route::post('item/{itemId}/attachments', array( 'as' => 'inspection.inspect.item.attachmentsUpdate', 'uses' => 'InspectionItemResultsController@attachmentsUpdate'));
                    Route::get('item/{itemId}/updatedAttacementCount', array( 'as' => 'inspection.inspect.item.attachments.updated.count.get', 'uses' => 'InspectionsController@getUpdatedAttachmentCount'));
                });

                Route::group(array('before' => 'inspection.isNotDraft|inspection.hasModuleAccess'), function(){
                    Route::get('submit', array( 'as' => 'inspection.submit.form', 'uses' => 'InspectionsController@submissionForm'));
                    Route::post('submit', array( 'as' => 'inspection.submit', 'before' => 'inspection.readyForSubmission|inspection.isSubmitter', 'uses' => 'InspectionsController@submit'));
                    Route::get('approval-logs', array( 'as' => 'inspection.approvalLogs', 'uses' => 'InspectionsController@approvalLogs'));
                    Route::get('submission-logs', array( 'as' => 'inspection.submissionLogs', 'uses' => 'InspectionsController@submissionLogs'));
                    Route::get('item/{itemId}/role-uploads', array( 'as' => 'inspection.inspect.item.role.uploads', 'uses' => 'InspectionItemResultsController@getUploadsByRole'));
                });
            });
        });
    });
});