<?php

Route::group(['prefix' => 'building-information-modelling-level'], function() {
    Route::group(['prefix' => 'level'], function() {
        Route::get('/', ['as' => 'buildingInformationModellingLevel.index', 'uses' => 'BuildingInformationModellingLevelsController@index']);
        Route::get('/list', ['as' => 'buildingInformationModellingLevel.list', 'uses' => 'BuildingInformationModellingLevelsController@list']);
        Route::post('/store', ['as' => 'buildingInformationModellingLevel.store', 'uses' => 'BuildingInformationModellingLevelsController@store']);

        Route::group(['before' => 'bimLevelEditable', 'prefix' => '{bimLevelId}'], function() {
            Route::post('/update', ['as' => 'buildingInformationModellingLevel.update', 'uses' => 'BuildingInformationModellingLevelsController@update']);
            Route::delete('/delete', ['as' => 'buildingInformationModellingLevel.delete', 'uses' => 'BuildingInformationModellingLevelsController@destroy']);
        });
    });
});