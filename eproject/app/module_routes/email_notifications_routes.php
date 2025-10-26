<?php

Route::group(array( 'prefix' => 'email_notifications' ), function ()
{
    Route::get('/', array( 'as' => 'email_notifications', 'uses' => 'EmailNotificationsController@index' ));
    Route::get('create', array( 'as' => 'email_notifications.create', 'uses' => 'EmailNotificationsController@create' ));
	Route::post('create', array( 'uses' => 'EmailNotificationsController@store' ));
	Route::get('show/{emailId}', array( 'as' => 'email_notifications.show', 'uses' => 'EmailNotificationsController@show' ));
	Route::get('edit/{emailId}', array( 'as' => 'email_notifications.edit', 'uses' => 'EmailNotificationsController@edit' ));
	Route::put('edit/{emailId}', array( 'uses' => 'EmailNotificationsController@update' ));
	Route::delete('delete/{emailId}', array( 'as' => 'email_notifications.delete', 'uses' => 'EmailNotificationsController@destroy' ));
});

