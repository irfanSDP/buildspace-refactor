<?php

Route::group(['prefix' => 'rfv-categories', 'before' => 'moduleAccess:' . \PCK\ModulePermission\ModulePermission::MODULE_ID_RFV_CATEGORY,], function()
{
    Route::get('/', [ 'as' => 'requestForVariation.categories.index', 'uses' => 'RequestForVariationCategoryController@index' ]);
    Route::get('/categories', [ 'as' => 'requestForVariation.categories.get', 'uses' => 'RequestForVariationCategoryController@getRfvCategories' ]);
    Route::post('/store', [ 'as' => 'requestForVariation.category.store', 'uses' => 'RequestForVariationCategoryController@store' ]);

    Route::group(['prefix' => 'kpi/{rfvCategoryId}'], function()
    {
        Route::get('editableCheck', [ 'as' => 'requestForVariation.category.editable.check', 'uses' => 'RequestForVariationCategoryController@editableCheck' ]);
        Route::post('update', [ 'as' => 'requestForVariation.category.update', 'uses' => 'RequestForVariationCategoryController@rfvCategoryDescriptionUpdate' ]);
        Route::post('delete', [ 'as' => 'requestForVariation.category.delete', 'uses' => 'RequestForVariationCategoryController@destroy' ]);
        Route::get('edit', [ 'as' => 'requestForVariation.category.kpi.edit', 'uses' => 'RequestForVariationCategoryController@kpiLimitEdit' ]);
        Route::post('kpiUpdate', [ 'as' => 'requestForVariation.category.kpi.update', 'uses' => 'RequestForVariationCategoryController@kpiLimitUpdate' ]);
        Route::get('kpiUpdateLogs', [ 'as' => 'requestForVariation.category.kpi.update.logs.get', 'uses' => 'RequestForVariationCategoryController@getKpiLimitUpdateLogs' ]);
    });
});

