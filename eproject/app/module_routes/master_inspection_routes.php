<?php

Route::group(['prefix' => 'master-inspection-list', 'before' => 'systemModule.inspection.enabled'], function () {
    Route::group(['before' => 'moduleAccess:' . \PCK\ModulePermission\ModulePermission::MODULE_ID_INSPECTION], function() {
        Route::get('/getMasterInspectionListsSelection', ['as' => 'master.inspection.lists.selection.get', 'uses' => 'MasterInspectionListsController@getMasterInspectionListsSelection']);
        
        Route::group(['prefix' => '/{inspectionListId}'], function() {
            Route::get('/getMasterInspectionListCategoriesSelection', ['as' => 'master.inspection.list.categories.selection.get', 'uses' => 'MasterInspectionListsController@getMasterInspectionListCategoriesSelection']);
        });

         Route::group(['prefix' => 'inspectionListCategory/{inspectionListCategoryId}'], function() {
            Route::get('/getMasterCategoryChildrenSelection', ['as' => 'master.inspection.list.category.children.selection.get', 'uses' => 'MasterInspectionListCategoriesController@getMasterCategoryChildrenSelection']);
        });
    });

    Route::group(['before' => 'moduleAccess:' . \PCK\ModulePermission\ModulePermission::MODULE_ID_INSPECTION_TEMPLATE], function() {
        Route::get('/', ['as' => 'master.inspection.list.index', 'uses' => 'MasterInspectionListsController@index']);
        Route::get('/getMasterInspectionLists', ['as' => 'master.inspection.lists.get', 'uses' => 'MasterInspectionListsController@getMasterInspectionLists']);
        Route::post('/store', ['as' => 'master.inspection.list.store', 'uses' => 'MasterInspectionListsController@store']);
    
        Route::group(['prefix' => '/{inspectionListId}'], function() {
            Route::post('/update', ['as' => 'master.inspection.list.update', 'uses' => 'MasterInspectionListsController@update']);
            Route::post('/delete', ['as' => 'master.inspection.list.delete', 'uses' => 'MasterInspectionListsController@destroy']);
            Route::get('/getInspectionListCategory', ['as' => 'master.inspection.list.categories.get', 'uses' => 'MasterInspectionListsController@getInspectionListCategories']);
        });
    
        Route::group(['prefix' => '/inspectionListCategory'], function() {
            Route::group(['prefix' => '/{inspectionListCategoryId}'], function() {
                Route::get('/getCategoryChildren', ['as' => 'master.inspection.list.category.children.get', 'uses' => 'MasterInspectionListCategoriesController@getCategoryChildren']);
                Route::post('/categoryDelete', ['as' => 'master.inspection.list.category.delete', 'uses' => 'MasterInspectionListCategoriesController@categoryDelete']);
                Route::get('/getAdditionalFields', ['as' => 'master.inspection.list.category.additional.fields.get', 'uses' => 'MasterInspectionListsCategoryAdditionalFieldsController@getAdditionalFields']);
                Route::get('/getInspectionListItems', ['as' => 'master.inspection.list.items.get', 'uses' => 'MasterInspectionListItemsController@getInspectionListItems']);
                Route::get('/changeListCategoryTypeCheck', ['as' => 'master.inspection.list.category.change.type.check', 'uses' => 'MasterInspectionListCategoriesController@changeListCategoryTypeCheck']);
            });
            
            Route::post('/categoryAdd', ['as' => 'master.inspection.list.category.add', 'uses' => 'MasterInspectionListCategoriesController@categoryAdd']);
            Route::post('/categoryUpdate', ['as' => 'master.inspection.list.category.update', 'uses' => 'MasterInspectionListCategoriesController@categoryUpdate']);
        });
    
        Route::group(['prefix' => '/inspectionListItem'], function() {
            Route::group(['prefix' => '/{inspectionListItemId}'], function() {
                Route::get('/getInspectionListItemChildren', ['as' => 'master.inspection.list.item.children.get', 'uses' => 'MasterInspectionListItemsController@getInspectionListItemChildren']);
                Route::post('/itemDelete', ['as' => 'master.inspection.list.item.delete', 'uses' => 'MasterInspectionListItemsController@itemDelete']);
                Route::get('/changeListItemTypeCheck', ['as' => 'master.inspection.list.item.change.type.check', 'uses' => 'MasterInspectionListItemsController@changeListItemTypeCheck']);
            });
            Route::post('/itemAdd', ['as' => 'master.inspection.list.item.add', 'uses' => 'MasterInspectionListItemsController@itemAdd']);
            Route::post('/itemUpdate', ['as' => 'master.inspection.list.item.update', 'uses' => 'MasterInspectionListItemsController@itemUpdate']);
        });
    
        Route::group(['prefix' => '/additionalField'], function() {
            Route::group(['prefix' => '/{additionalFieldId}'], function() {
                Route::post('/fieldDelete', ['as' => 'master.inspection.list.category.additional.field.delete', 'uses' => 'MasterInspectionListsCategoryAdditionalFieldsController@fieldDelete']);
            });
    
            Route::post('/fieldAdd', ['as' => 'master.inspection.list.category.additional.field.add', 'uses' => 'MasterInspectionListsCategoryAdditionalFieldsController@fieldAdd']);
            Route::post('/fieldUpdate', ['as' => 'master.inspection.list.category.additional.field.update', 'uses' => 'MasterInspectionListsCategoryAdditionalFieldsController@fieldUpdate']);
        });
    });
});

