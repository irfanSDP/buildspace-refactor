<?php

Route::group(array( 'prefix' => 'request-for-variation' ), function()
{
    Route::get('getActionLogs/{rfvId}', [ 'as' => 'requestForVariation.logs.get', 'uses' => 'RequestForVariationController@getActionLogs' ]);

    Route::group([ 'prefix' => 'user_permissions' ], function()
    {
        Route::get('/', [ 'as' => 'requestForVariation.user.permissions.index', 'uses' => 'RequestForVariationUserPermissionsController@index' ]);
        Route::get('show/{id}', [ 'as' => 'requestForVariation.user.permissions.show', 'uses' => 'RequestForVariationUserPermissionsController@show' ]);
        Route::get('create', [ 'as' => 'requestForVariation.user.permissions.create', 'uses' => 'RequestForVariationUserPermissionsController@create' ]);
        Route::post('store', [ 'as' => 'requestForVariation.user.permissions.store', 'uses' => 'RequestForVariationUserPermissionsController@store' ]);
        Route::get('edit/{id}', [ 'as' => 'requestForVariation.user.permissions.edit', 'uses' => 'RequestForVariationUserPermissionsController@edit' ]);
        Route::post('update', [ 'as' => 'requestForVariation.user.permissions.update', 'uses' => 'RequestForVariationUserPermissionsController@update' ]);
        Route::get('getUserInfo', [ 'as' => 'requestForVariation.user.permissions.user.info', 'uses' => 'RequestForVariationUserPermissionsController@getUserInfo' ]);
        Route::get('assignable/get', [ 'as' => 'requestForVariation.user.permissions.assignable', 'uses' => 'RequestForVariationUserPermissionsController@getAssignableUsers' ]);
        Route::delete('delete/{id}', [ 'as' => 'requestForVariation.user.permissions.delete', 'uses' => 'RequestForVariationUserPermissionsController@userPermissionDelete' ]);
        Route::delete('groupDelete/{id}', [ 'as' => 'requestForVariation.user.permissions.group.delete', 'uses' => 'RequestForVariationUserPermissionsController@userPermissionGroupDelete' ]);
    });

    Route::group([ 'prefix' => 'rfv-form' ], function()
    {
        Route::get('/', [ 'as' => 'requestForVariation.index', 'uses' => 'RequestForVariationController@index' ]);
        Route::get('/list', [ 'as' => 'requestForVariation.list', 'uses' => 'RequestForVariationController@listRequestForVariationByGroup' ]);
        Route::get('/getRfvAmountInfo', [ 'as' => 'requestForVariation.amount.info.get', 'uses' => 'RequestForVariationController@getRfvAmountInfo' ]);
        Route::get('/create', [ 'as' => 'requestForVariation.new.create', 'uses' => 'RequestForVariationController@create' ]);
        Route::get('/{requestForVariationId}/show', [ 'as' => 'requestForVariation.form.show', 'uses' => 'RequestForVariationController@show' ]);
        Route::get('/{requestForVariationId}/uploadedFiles', [ 'as' => 'requestForVariation.uploaded.files.get', 'uses' => 'RequestForVariationController@getUploadedFiles' ]);
        Route::post('/{requestForVariationId}/upload', [ 'as' => 'requestForVariation.document.upload', 'uses' => 'RequestForVariationController@upload' ]);
        Route::post('/{cabinetFileId}/uploadDelete', [ 'as' => 'requestForVariation.document.uploadDelete', 'uses' => 'RequestForVariationController@uploadDelete' ]);
        Route::get('/{cabinetFileId}/downloadFile', [ 'as' => 'requestForVariation.document.download', 'uses' => 'RequestForVariationController@fileDownload' ]);
        Route::post('/submit', [ 'as' => 'requestForVariation.submit', 'uses' => 'RequestForVariationController@submit' ]);
        Route::get('/contractAndContingencySum', [ 'as' => 'requestForVariation.cncsum.show', 'uses' => 'RequestForVariationController@contractAndContingencySumFormShow' ]);
        Route::post('/contractAndContingencySum/save', [ 'as' => 'requestForVariation.cncsum.save', 'uses' => 'RequestForVariationController@contractAndContingencySumSave' ]);
        Route::delete('{requestForVariationId}', [ 'as' => 'requestForVariation.delete', 'uses' => 'RequestForVariationController@destroy' ]);
        Route::get('/printAll', ['as' => 'requestForVariation.all.print', 'uses' => 'RequestForVariationController@printListOfRequestForVariations']);

        Route::group(['before' => 'requestForVariation.status.approved.check'], function() {
            Route::post('rfvAiNumber/{rfvId}', [ 'as' => 'requestForVariation.ainumber.save', 'uses' => 'RequestForVariationController@saveRfvAiNumber' ]);
            Route::get('{rfvId}/print', ['as' => 'requestForVariation.print', 'uses' => 'RequestForVariationController@printRequestForVariation']);
        });
    });

    Route::group([ 'prefix' => 'cost_estimate' ], function()
    {
        Route::get('list/{rfvId}', [ 'as' => 'requestForVariation.cost.estimate.list', 'uses' => 'RequestForVariationCostEstimateController@getCostEstimateList' ]);
        Route::post('add', [ 'as' => 'requestForVariation.cost.estimate.add', 'uses' => 'RequestForVariationCostEstimateController@itemAdd' ]);
        Route::post('update', [ 'as' => 'requestForVariation.cost.estimate.update', 'uses' => 'RequestForVariationCostEstimateController@itemUpdate' ]);
        Route::post('delete', [ 'as' => 'requestForVariation.cost.estimate.delete', 'uses' => 'RequestForVariationCostEstimateController@itemDelete' ]);
        Route::post('import/{rfvId}', [ 'as' => 'requestForVariation.cost.estimate.import', 'uses' => 'RequestForVariationCostEstimateController@import' ]);
    });

    Route::get('variationOrder/downloadExcelReport', array( 'as' => 'variationOrder.report.download', 'uses' => 'RequestForVariationController@downloadVariationOrderExcelReport' ));
});