<?php

Route::group(array( 'prefix' => 'order' ), function()
{
    Route::get('/', ['as' => 'order.index', 'uses' => 'OrderController@index']);
    Route::get('list', ['as' => 'order.list', 'uses' => 'OrderController@getList']);
});