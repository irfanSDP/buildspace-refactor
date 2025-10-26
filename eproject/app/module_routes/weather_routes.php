<?php

Route::group(array( 'before' => 'moduleAccess:' . \PCK\ModulePermission\ModulePermission::MODULE_ID_WEATHERS, 'prefix' => 'weathers' ), function()
{
    Route::get('/', array( 'as' => 'weathers', 'uses' => 'WeatherController@index' ));
    Route::get('create', array( 'as' => 'weathers.create', 'uses' => 'WeatherController@create' ));
    Route::post('create', array( 'as' => 'weathers.store', 'uses' => 'WeatherController@store' ));
    Route::get('edit/{id}', array( 'as' => 'weathers.edit', 'uses' => 'WeatherController@edit' ));
    Route::put('update/{id}', array( 'as' => 'weathers.update', 'uses' => 'WeatherController@update' ));
    Route::delete('delete/{id}', array( 'as' => 'weathers.delete', 'uses' => 'WeatherController@destroy' ));
});