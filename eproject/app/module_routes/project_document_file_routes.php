<?php

Route::group(array( 'prefix' => 'project_documents' ), function()
{
    Route::post('upload/{folderId}', array( 'before' => 'projectDocument.folder.modify', 'as' => 'projectDocument.upload', 'uses' => 'DocumentManagementsController@upload' ));
    Route::get('fileList/{folderId}', array( 'before' => 'projectDocument.folder.access', 'as' => 'projectDocument.fileList', 'uses' => 'DocumentManagementsController@fileList' ));

    Route::group(array( 'before' => 'projectDocument.file.access' ), function()
    {
        Route::get('fileInfo/{fileId}', array( 'as' => 'projectDocument.fileInfo', 'uses' => 'DocumentManagementsController@fileInfo' ));
        Route::get('revisionList/{fileId}', array( 'as' => 'projectDocument.revisionList', 'uses' => 'DocumentManagementsController@revisionList' ));
        Route::get('fileRevisions/{fileId}', array( 'as' => 'projectDocument.fileRevisions', 'uses' => 'DocumentManagementsController@fileRevisions' ));
        Route::get('fileRevisionList/{fileId}', array( 'as' => 'projectDocument.fileRevisionList', 'uses' => 'DocumentManagementsController@fileRevisionList' ));
        Route::get('fileDownload/{fileId}', array( 'as' => 'projectDocument.fileDownload', 'uses' => 'DocumentManagementsController@fileDownload' ));
    });

    Route::post('uploadUpdate', array( 'as' => 'projectDocument.uploadUpdate', 'uses' => 'DocumentManagementsController@uploadUpdate' ));

    Route::post('uploadDelete/{uploadId}', array( 'before' => 'projectDocument.upload.modify', 'as' => 'projectDocument.uploadDelete', 'uses' => 'DocumentManagementsController@uploadDelete' ));

    Route::post('fileDelete/{fileId}', array( 'before' => 'projectDocument.file.modify', 'as' => 'projectDocument.file.delete', 'uses' => 'DocumentManagementsController@fileDelete' ));
});