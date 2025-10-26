<?php

Route::group(array( 'before' => 'moduleAccess:' . \PCK\ModulePermission\ModulePermission::MODULE_ID_MASTER_COST_DATA, 'prefix' => 'cost-data/master' ), function()
{
    Route::get('/', array( 'as' => 'costData.master', 'uses' => 'MasterCostDataController@index' ));
    Route::get('list', array( 'as' => 'costData.master.list', 'uses' => 'MasterCostDataController@list' ));

    Route::group(array( 'before' => 'moduleEditorAccess:' . \PCK\ModulePermission\ModulePermission::MODULE_ID_MASTER_COST_DATA ), function()
    {
        Route::get('create', array( 'as' => 'costData.master.create', 'uses' => 'MasterCostDataController@create' ));
        Route::post('/', array( 'as' => 'costData.master.store', 'uses' => 'MasterCostDataController@store' ));
        Route::get('{masterCostDataId}', array( 'as' => 'costData.master.edit', 'uses' => 'MasterCostDataController@edit' ));
        Route::put('{masterCostDataId}', array( 'as' => 'costData.master.update', 'uses' => 'MasterCostDataController@update' ));
        Route::delete('{masterCostDataId}', array( 'as' => 'costData.master.delete', 'uses' => 'MasterCostDataController@delete' ));
    });
});

Route::group(array( 'before' => 'moduleOrObjectAccess:' . \PCK\ModulePermission\ModulePermission::MODULE_ID_COST_DATA . ',' . get_class(new \PCK\Buildspace\CostData), 'prefix' => 'cost-data' ), function()
{
    Route::get('/', array( 'as' => 'costData', 'uses' => 'CostDataController@index' ));
    Route::get('list', array( 'as' => 'costData.list', 'uses' => 'CostDataController@list' ));
    Route::get('show/{costDataId}', array( 'as' => 'costData.show', 'uses' => 'CostDataController@show' ));

    Route::group(array( 'before' => 'moduleEditorAccess:' . \PCK\ModulePermission\ModulePermission::MODULE_ID_COST_DATA ), function()
    {
        Route::get('create', array( 'as' => 'costData.create', 'uses' => 'CostDataController@create' ));
        Route::get('list-projects', array( 'as' => 'costData.listProjects', 'uses' => 'CostDataController@listProjects' ));
        Route::get('list-project-options', array( 'as' => 'costData.listProjectOptions', 'uses' => 'CostDataController@listProjectOptions' ));
        Route::get('options/regions', array( 'as' => 'costData.options.regions', 'uses' => 'CostDataController@getRegionOptions' ));
        Route::get('options/subregions/{id?}', array( 'as' => 'costData.options.subregions', 'uses' => 'CostDataController@getSubregionOptions' ));
        Route::post('/', array( 'as' => 'costData.store', 'uses' => 'CostDataController@store' ));
        Route::get('{costDataId}', array( 'as' => 'costData.edit', 'uses' => 'CostDataController@edit' ));
        Route::put('{costDataId}', array( 'as' => 'costData.update', 'uses' => 'CostDataController@update' ));
        Route::delete('{costDataId}', array( 'as' => 'costData.delete', 'uses' => 'CostDataController@delete' ));
        Route::get('users/{costDataId}', array( 'as' => 'costData.users', 'uses' => 'CostDataController@usersIndex' ));
        Route::post('users/{costDataId}', array( 'as' => 'costData.users.update', 'uses' => 'CostDataController@assignUsers' ));
        Route::get('users/{costDataId}/assignable', array( 'as' => 'costData.users.assignable', 'uses' => 'CostDataController@getAssignableUsers' ));
        Route::get('users/{costDataId}/assigned', array( 'as' => 'costData.users.assigned', 'uses' => 'CostDataController@getAssignedUsers' ));
        Route::delete('{costDataId}/revoke/{userId}', array( 'as' => 'costData.users.revoke', 'uses' => 'CostDataController@revoke' ));
        Route::post('{costDataId}/editor/{userId}', array( 'as' => 'costData.users.editor.toggle', 'uses' => 'CostDataController@toggleEditorStatus' ));
        Route::post('{costDataId}/resend-notification/{userId}', array( 'as' => 'costData.users.resendNotification', 'uses' => 'CostDataController@resendNotification' ));
    });
});

Route::group(array( 'before' => 'superAdminAccessLevel', 'prefix' => 'cost-data-types' ), function()
{
    Route::get('/', array( 'as' => 'costDataTypes.index', 'uses' => 'CostDataTypesController@index' ));
    Route::get('list', array( 'as' => 'costDataTypes.list', 'uses' => 'CostDataTypesController@list' ));
    Route::post('update', array( 'as' => 'costDataTypes.update', 'uses' => 'CostDataTypesController@update' ));
    Route::delete('{costDataTypeId}', array( 'as' => 'costDataTypes.destroy', 'uses' => 'CostDataTypesController@destroy' ));
});