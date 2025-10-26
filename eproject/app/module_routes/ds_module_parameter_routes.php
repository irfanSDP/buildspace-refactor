<?php

Route::group(['before' => 'systemModule.vendorManagement.enabled'], function() {
    Route::group(['before' => 'systemModule.digitalStar.enabled'], function () {
        Route::group(['before' => 'vendorManagement.hasPermission:' . \PCK\VendorManagement\VendorManagementUserPermission::TYPE_SETTINGS_AND_MAINTENANCE], function () {
            Route::group(['prefix' => 'digital-star-module-parameter'], function () {
                Route::get('/', ['as' => 'digital-star.module-parameter.edit', 'uses' => 'DigitalStar\DsModuleParameterController@edit']);
                Route::post('/update', ['as' => 'digital-star.module-parameter.update', 'uses' => 'DigitalStar\DsModuleParameterController@update']);
            });
        });
    });
});