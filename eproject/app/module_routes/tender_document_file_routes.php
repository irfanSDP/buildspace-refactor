<?php

Route::group(array( 'prefix' => 'tender_documents' ), function()
{
    Route::post('upload/{folderId}', array( 'before' => 'tenderDocument.folder.modify', 'as' => 'tenderDocument.upload', 'uses' => 'TenderDocumentFoldersController@upload' ));
    Route::get('fileList/{folderId}', array( 'before' => 'tenderDocument.folder.access', 'as' => 'tenderDocument.fileList', 'uses' => 'TenderDocumentFoldersController@fileList' ));
    Route::get('{fileId}/log', array('as' => 'tenderDocument.file.log.get', 'uses' => 'TenderDocumentFoldersController@getFileDownloadLogs'));

    Route::group(array( 'before' => 'tenderDocument.file.access' ), function()
    {
        Route::get('fileInfo/{fileId}', array( 'as' => 'tenderDocument.fileInfo', 'uses' => 'TenderDocumentFoldersController@fileInfo' ));
        Route::get('revisionList/{fileId}', array( 'as' => 'tenderDocument.revisionList', 'uses' => 'TenderDocumentFoldersController@revisionList' ));
        Route::get('fileRevisions/{fileId}', array( 'as' => 'tenderDocument.fileRevisions', 'uses' => 'TenderDocumentFoldersController@fileRevisions' ));
        Route::get('fileRevisionList/{fileId}', array( 'as' => 'tenderDocument.fileRevisionList', 'uses' => 'TenderDocumentFoldersController@fileRevisionList' ));
        Route::get('fileDownload/{fileId}', array( 'before' => 'tenderDocument.file.download', 'as' => 'tenderDocument.fileDownload', 'uses' => 'TenderDocumentFoldersController@fileDownload' ));
    });

    Route::get('folderDownload/{folderId}', array( 'before' => 'tenderDocument.folder.access|tenderDocument.folder.download', 'as' => 'tenderDocument.folderDownload', 'uses' => 'TenderDocumentFoldersController@folderDownload' ));

    Route::post('uploadUpdate', array( 'as' => 'tenderDocument.uploadUpdate', 'uses' => 'TenderDocumentFoldersController@uploadUpdate' ));

    Route::group(array( 'before' => 'tenderDocument.file.modify' ), function()
    {
        Route::post('uploadDelete/{fileId}', array( 'as' => 'tenderDocument.uploadDelete', 'uses' => 'TenderDocumentFoldersController@uploadDelete' ));

        Route::post('fileDelete/{fileId}', array( 'as' => 'tenderDocument.file.delete', 'uses' => 'TenderDocumentFoldersController@fileDelete' ));
    });
});