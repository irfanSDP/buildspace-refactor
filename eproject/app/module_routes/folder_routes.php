<?php
Route::get('folders/overview', array('as' => 'folders.overviewList', 'uses' => 'FoldersController@overviewList'));
Route::get('folders/{folderId}', array('before' => 'folders.canView', 'as' => 'folders', 'uses' => 'FoldersController@index'));
Route::get('folders/{folderId}/list', array('before' => 'folders.canView', 'as' => 'folders.list', 'uses' => 'FoldersController@list'));
Route::post('folders/{folderId}/store-or-update', array('as' => 'folders.storeOrUpdate', 'uses' => 'FoldersController@storeOrUpdate'));
Route::delete('folders/{folderId}/delete', array('before' => 'folders.canEdit', 'as' => 'folders.delete', 'uses' => 'FoldersController@delete'));
Route::get('folders/{folderId}/get-attachments', array('as' => 'folders.getAttachments', 'uses' => 'FoldersController@getAttachments'));
Route::post('folders/{folderId}/upload', array('before' => 'folders.canEdit', 'as' => 'folders.upload', 'uses' => 'FoldersController@upload'));
Route::post('folders/{folderId}/reposition', array('before' => 'folders.canEdit', 'as' => 'folders.reposition', 'uses' => 'FoldersController@reposition'));
Route::get('folders/{folderId}/move-options', array('as' => 'folders.moveOptionList', 'uses' => 'FoldersController@moveOptionList'));
Route::post('folders/{folderId}/move', array('as' => 'folders.move', 'uses' => 'FoldersController@move'));
Route::get('folders/{folderId}/permissions/get', array('before' => 'folders.canEdit', 'as' => 'folders.permissions.list', 'uses' => 'FoldersController@permissionsList'));
Route::post('folders/{folderId}/user/{userId}/permissions/update', array('before' => 'folders.canEdit', 'as' => 'folders.permissions.update', 'uses' => 'FoldersController@updatePermission'));