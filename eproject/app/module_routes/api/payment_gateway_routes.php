<?php

Route::group(array( 'prefix' => 'payment' ), function()
{
    Route::get('result/{paymentGateway}', ['as' => 'api.payment-gateway.result', 'uses' => 'Api\PaymentGatewayController@returnUrl']);
    //Route::post('notify', ['as' => 'api.payment-gateway.notify', 'uses' => 'Api\PaymentGatewayController@notifyUrl']);
    Route::post('callback/{paymentGateway}', array( 'as' => 'api.payment-gateway.callback', 'uses' => 'Api\PaymentGatewayController@callbackUrl' ));

    Route::group(['prefix' => 'html'], function() {
        Route::get('payment-btn', ['as' => 'api.payment-gateway.html.payment-btn', 'uses' => 'Api\PaymentGatewayController@getPaymentBtn']);
        Route::get('payment-form', ['as' => 'api.payment-gateway.html.payment-form', 'uses' => 'Api\PaymentGatewayController@getPaymentForm']);
    });
});