<?php

Route::group(['prefix' => 'scheduled_maintenance', 'before' => 'superAdminAccessLevel' ], function() {
    Route::get('/', array( 'as' => 'scheduled_maintenance.index', 'uses' => 'ScheduledMaintenanceController@index' ));

    Route::get('create', array( 'as' => 'scheduled_maintenance.create', 'uses' => 'ScheduledMaintenanceController@create' ));
    Route::post('store', array( 'as' => 'scheduled_maintenance.store', 'uses' => 'ScheduledMaintenanceController@store' ));
    Route::get('edit/{id}', array( 'as' => 'scheduled_maintenance.edit', 'uses' => 'ScheduledMaintenanceController@edit' ));
    Route::put('update/{id}', array( 'as' => 'scheduled_maintenance.update', 'uses' => 'ScheduledMaintenanceController@update' ));
    Route::delete('delete/{id}', array( 'as' => 'scheduled_maintenance.delete', 'uses' => 'ScheduledMaintenanceController@destroy' ));
});