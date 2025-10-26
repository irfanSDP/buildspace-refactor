<?php

Route::get('tenderDocument/{folderId?}', array( 'as' => 'projects.tenderDocument.index', 'uses' => 'TenderDocumentFoldersController@index' ));
Route::post('tenderDocumentNewFolder', array( 'as' => 'projects.tenderDocument.newFolder', 'uses' => 'TenderDocumentFoldersController@folderCreate' ));
Route::post('tenderDocumentRenameFolder', array( 'as' => 'projects.tenderDocument.renameFolder', 'uses' => 'TenderDocumentFoldersController@folderUpdate' ));
Route::post('tenderDocumentDeleteFolder', array( 'as' => 'projects.tenderDocument.deleteFolder', 'uses' => 'TenderDocumentFoldersController@folderDelete' ));
Route::get('myTenderDocumentFolder/{folderId}', array( 'as' => 'projects.tenderDocument.myFolder', 'uses' => 'TenderDocumentFoldersController@myFolder' ));
Route::get('tenderDocument/folderInfo/{folderId}', array( 'as' => 'projects.tenderDocument.folderInfo', 'uses' => 'TenderDocumentFoldersController@folderInfo' ));

Route::get('tenderDocumentSendNotification/{folderId}', array( 'as' => 'projects.tenderDocument.sendNotification', 'uses' => 'TenderDocumentFoldersController@sendNotification' ));

Route::post('tenderDocument/create', array( 'as' => 'projects.tenderDocument.create', 'uses' => 'TenderDocumentFoldersController@store' ));

Route::group(array( 'prefix' => 'folder/{folderId}/structured-documents' ), function()
{
    Route::get('{id}/print', array( 'as' => 'structured_documents.print', 'uses' => 'StructuredDocumentsController@printDocument' ));

    Route::group(array( 'before' => 'checkTenderAccessLevelPermission' ), function()
    {
        Route::get('{id}', array( 'as' => 'structured_documents.edit', 'uses' => 'StructuredDocumentsController@edit' ));
        Route::post('{id}/update', array( 'as' => 'structured_documents.update', 'uses' => 'StructuredDocumentsController@update' ));
        Route::get('{id}/clauses', array( 'as' => 'structured_documents.clauses.edit', 'uses' => 'StructuredDocumentsController@editClauses' ));
        Route::post('{id}/clauses/update', array( 'as' => 'structured_documents.clauses.update', 'uses' => 'StructuredDocumentsController@updateClauses' ));
    });
});