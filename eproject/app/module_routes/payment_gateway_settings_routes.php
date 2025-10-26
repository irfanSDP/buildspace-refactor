<?php

Route::group(['prefix' => 'payment-gateway/settings'], function()
{
    Route::get('edit', ['as' => 'payment-gateway.settings.edit', 'uses' => 'PaymentGateway\PaymentGatewaySettingController@edit']);
    Route::post('update/{id}', ['as' => 'payment-gateway.settings.update', 'uses' => 'PaymentGateway\PaymentGatewaySettingController@update']);
});
