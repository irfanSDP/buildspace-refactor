<?php

Route::group(array( 'before' => 'moduleAccess:' . \PCK\ModulePermission\ModulePermission::MODULE_ID_SITE_DIARY_MAINTENANCE, 'prefix' => 'rejected_materials' ), function()
{
    Route::get('/', array( 'as' => 'rejected-materials.index', 'uses' => 'RejectedMaterialController@index' ));
    Route::get('create', array( 'as' => 'rejected-materials.create', 'uses' => 'RejectedMaterialController@create' ));
    Route::post('create', array( 'as' => 'rejected-materials.store', 'uses' => 'RejectedMaterialController@store' ));
    Route::get('edit/{id}', array( 'as' => 'rejected-materials.edit', 'uses' => 'RejectedMaterialController@edit' ));
    Route::put('update/{id}', array( 'as' => 'rejected-materials.update', 'uses' => 'RejectedMaterialController@update' ));
    Route::delete('delete/{id}', array( 'as' => 'rejected-materials.delete', 'uses' => 'RejectedMaterialController@destroy' ));
});