<?php

Route::group(array('before' => 'moduleAccess:' . \PCK\ModulePermission\ModulePermission::MODULE_ID_SITE_DIARY_MAINTENANCE, 'prefix' => 'labours' ), function()
{
    Route::get('/', array( 'as' => 'labours.index', 'uses' => 'LabourController@index' ));
    Route::get('create', array( 'as' => 'labours.create', 'uses' => 'LabourController@create' ));
    Route::post('create', array( 'as' => 'labours.store', 'uses' => 'LabourController@store' ));
    Route::get('edit/{id}', array( 'as' => 'labours.edit', 'uses' => 'LabourController@edit' ));
    Route::put('update/{id}', array( 'as' => 'labours.update', 'uses' => 'LabourController@update' ));
    Route::delete('delete/{id}', array( 'as' => 'labours.delete', 'uses' => 'LabourController@destroy' ));
});