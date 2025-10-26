<?php

Route::group(array( 'prefix' => 'module_uploads' ), function ()
{
	Route::get('download/{fileId}', array( 'as' => 'moduleUploads.download', 'uses' => 'ModuleUploadsController@download' ));

	Route::post('create', array( 'as' => 'moduleUploads.upload', 'uses' => 'ModuleUploadsController@store' ));

	Route::post('create-announcement', array( 'as' => 'moduleUploads.uploadAnnouncement', 'uses' => 'ModuleUploadsController@storeAnnouncement' ));

	Route::post('delete/{fileId}', array( 'as' => 'moduleUploads.delete', 'uses' => 'ModuleUploadsController@destroy' ));

	Route::post('delete-announcement/{fileId}', array( 'as' => 'moduleUploads.deleteAnnouncement', 'uses' => 'ModuleUploadsController@destroyAnnouncement' ));
});