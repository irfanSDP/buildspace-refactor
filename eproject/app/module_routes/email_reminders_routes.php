<?php
Route::group(['prefix' => 'email_reminders'], function() {
    Route::get('create', array( 'as' => 'email_reminders.create', 'uses' => 'EBiddingEmailRemindersController@create' ));
    Route::post('create', array( 'uses' => 'EBiddingEmailRemindersController@store' ));
    Route::get('edit/{emailId}', array( 'as' => 'email_reminders.edit', 'uses' => 'EBiddingEmailRemindersController@edit' ));
	Route::put('edit/{emailId}', array( 'uses' => 'EBiddingEmailRemindersController@update' ));
	Route::get('/main', array( 'as' => 'email_reminders.main', 'uses' => 'EBiddingEmailRemindersController@main' ));
});