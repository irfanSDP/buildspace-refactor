<?php

Route::get('projectDocument/{folderId}', array( 'as' => 'projectDocument.index', 'uses' => 'DocumentManagementsController@index' ));
Route::post('projectDocumentNewFolder', array( 'as' => 'projectDocument.newFolder', 'uses' => 'DocumentManagementsController@folderCreate' ));
Route::post('projectDocumentRenameFolder', array( 'as' => 'projectDocument.renameFolder', 'uses' => 'DocumentManagementsController@folderUpdate' ));
Route::post('projectDocumentDeleteFolder', array( 'as' => 'projectDocument.deleteFolder', 'uses' => 'DocumentManagementsController@folderDelete' ));
Route::get('folderInfo/{folderId}', array( 'as' => 'projectDocument.folderInfo', 'uses' => 'DocumentManagementsController@folderInfo' ));
Route::get('sharedFolderInfo/{folderId}', array( 'as' => 'projectDocument.sharedFolderInfo', 'uses' => 'DocumentManagementsController@sharedFolderInfo' ));
Route::post('projectDocumentShareFolder', array( 'as' => 'projectDocument.shareFolder', 'uses' => 'DocumentManagementsController@folderShare' ));
Route::get('myProjectDocumentFolder/{folderId}', array( 'as' => 'projectDocument.myFolder', 'uses' => 'DocumentManagementsController@myFolder' ));
Route::get('myProjectDocumentSharedFolder/{folderId}', array( 'as' => 'projectDocument.mySharedFolder', 'uses' => 'DocumentManagementsController@mySharedFolder' ));
Route::post('notifications/{folderId}/send', array( 'as' => 'projectDocument.sendNotifications', 'uses' => 'DocumentManagementsController@sendNotifications' ));

Route::post('repositionFolder/{folderId}', array( 'as' => 'projectDocument.reposition', 'uses' => 'DocumentManagementsController@saveNewFolderPosition' ));