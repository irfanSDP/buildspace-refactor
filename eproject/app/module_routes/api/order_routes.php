<?php

Route::group(array( 'prefix' => 'order', 'before' => 'auth.basic' ), function()
{
    Route::post('create', ['as' => 'api.order.store', 'uses' => 'Api\OrderController@store']);
});