<?php 

 // Site Management Daily Report Module Routes
 Route::group(array( 'prefix' => 'daily-report','before' => 'siteManagement.hasDailyReportPermission'), function()
 {
     Route::get('/', array( 'as' => 'daily-report.index', 'uses' => 'DailyReportController@index' ));
     Route::get('create', array( 'as' => 'daily-report.create', 'uses' => 'DailyReportController@create' ));
     Route::post('create', array( 'as' => 'daily-report.store', 'uses' => 'DailyReportController@store' ));
     Route::get('{id}/show', array( 'as' => 'daily-report.show', 'uses' => 'DailyReportController@show' ));
     Route::get('{id}/edit', array( 'as' => 'daily-report.edit', 'uses' => 'DailyReportController@edit' ));
     Route::put('{id}/update', array( 'as' => 'daily-report.update', 'uses' => 'DailyReportController@update' ));
     Route::delete('{id}/delete', array( 'as' => 'daily-report.delete', 'uses' => 'DailyReportController@destroy' ));
     Route::get('{id}/getAttachmentsList', ['as' => 'daily-report.attachements.get', 'uses' => 'DailyReportController@getAttachmentsList']);
     Route::delete('{uploadedItemId}/attachmentsDelete/{id}', ['as' => 'daily-report.attachements.delete', 'uses' => 'DailyReportController@attachmentDelete']);
 });