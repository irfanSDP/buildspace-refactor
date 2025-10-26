<?php

Route::group(array('before' => 'moduleAccess:' . \PCK\ModulePermission\ModulePermission::MODULE_ID_SITE_DIARY_MAINTENANCE, 'prefix' => 'machinery' ), function()
{
    Route::get('/', array( 'as' => 'machinery.index', 'uses' => 'MachineryController@index' ));
    Route::get('create', array( 'as' => 'machinery.create', 'uses' => 'MachineryController@create' ));
    Route::post('create', array( 'as' => 'machinery.store', 'uses' => 'MachineryController@store' ));
    Route::get('edit/{id}', array( 'as' => 'machinery.edit', 'uses' => 'MachineryController@edit' ));
    Route::put('update/{id}', array( 'as' => 'machinery.update', 'uses' => 'MachineryController@update' ));
    Route::delete('delete/{id}', array( 'as' => 'machinery.delete', 'uses' => 'MachineryController@destroy' ));
});