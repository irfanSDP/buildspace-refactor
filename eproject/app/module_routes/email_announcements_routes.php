<?php
Route::group(['prefix' => 'email_announcements' , 'before' => 'superAdminAccessLevel' ], function() {
    Route::get('/', array( 'as' => 'email_announcements', 'uses' => 'EmailAnnouncementsController@index' ));
    Route::get('create', array( 'as' => 'email_announcements.create', 'uses' => 'EmailAnnouncementsController@create' ));
	Route::post('create', array( 'uses' => 'EmailAnnouncementsController@store' ));
	Route::get('show/{emailId}', array( 'as' => 'email_announcements.show', 'uses' => 'EmailAnnouncementsController@show' ));
	Route::get('edit/{emailId}', array( 'as' => 'email_announcements.edit', 'uses' => 'EmailAnnouncementsController@edit' ));
	Route::put('edit/{emailId}', array( 'uses' => 'EmailAnnouncementsController@update' ));
	Route::delete('delete/{emailId}', array( 'as' => 'email_announcements.delete', 'uses' => 'EmailAnnouncementsController@destroy' ));
	Route::get('/main', array( 'as' => 'email_announcements.main', 'uses' => 'EmailAnnouncementsController@main' ));
});