<?php

Route::group(['prefix' => 'payment-settings'], function() {
    Route::get('/', ['as' => 'payment.settings.index', 'uses' => 'PaymentSettingsController@index']);
    Route::get('/getAllRecords', ['as' => 'payment.settings.records.get', 'uses' => 'PaymentSettingsController@getAllRecords']);
    Route::post('/store', ['as' => 'payment.settings.store', 'uses' => 'PaymentSettingsController@store']);

    Route::group(['prefix' => '{paymentSettingId}'], function() {
        Route::post('/update', ['as' => 'payment.settings.update', 'uses' => 'PaymentSettingsController@update']);
        Route::post('/delete', ['as' => 'payment.settings.delete', 'uses' => 'PaymentSettingsController@delete']);
    });
});