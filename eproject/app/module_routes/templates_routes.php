<?php

Route::group(array( 'prefix' => 'template' ), function()
{    
    Route::group(array( 'prefix' => 'form_of_tender' ), function()
    {
        Route::group(array( 'before' => 'moduleAccess:' . \PCK\ModulePermission\ModulePermission::MODULE_ID_FORM_OF_TENDER_TEMPLATE ), function()
        {
            Route::get('/', ['as' => 'form_of_tender.template.selection', 'uses' => 'FormOfTenderTemplateSelectionController@index']);
            Route::get('/allTemplates', ['as' => 'form_of_tender.templates.all.get', 'uses' => 'FormOfTenderTemplateSelectionController@getAllTemplates']);
            Route::post('/store', ['as' => 'form_of_tender.template.store', 'uses' => 'FormOfTenderTemplateSelectionController@store']);

            Route::group(['prefix' => 'template/{templateId}'], function() {
                Route::get('/', array( 'as' => 'form_of_tender.template.edit', 'uses' => 'FormOfTenderController@editTemplate' ));
                Route::post('/update', ['as' => 'form_of_tender.template.update', 'uses' => 'FormOfTenderTemplateSelectionController@update']);
                Route::delete('/delete', ['as' => 'form_of_tender.template.delete', 'uses' => 'FormOfTenderTemplateSelectionController@destroy']);

                Route::group(array( 'prefix' => 'address' ), function()
                {
                    Route::get('/edit', array( 'as' => 'form_of_tender.address.template.edit', 'uses' => 'FormOfTenderAddressController@editAddressTemplate' ));
                    Route::post('/update', array( 'as' => 'form_of_tender.address.template.update', 'uses' => 'FormOfTenderAddressController@updateAddressTemplate' ));
                });

                Route::group(array( 'prefix' => 'clauses' ), function()
                {
                    Route::get('/edit', array( 'as' => 'form_of_tender.clauses.template.edit', 'uses' => 'FormOfTenderClausesController@editClausesTemplate' ));
                    Route::post('/update', array( 'as' => 'form_of_tender.clauses.template.update', 'uses' => 'FormOfTenderClausesController@updateClausesTemplate' ));
                });

                Route::group(array( 'prefix' => 'print-settings' ), function()
                {
                    Route::get('/', array( 'as' => 'form_of_tender.printSettings.template.edit', 'uses' => 'FormOfTenderPrintSettingsController@editPrintSettingsTemplate' ));
                    Route::post('/update', array( 'as' => 'form_of_tender.printSettings.template.update', 'uses' => 'FormOfTenderPrintSettingsController@updatePrintSettingsTemplate' ));
                });

                Route::group(array( 'prefix' => 'tender-alternatives' ), function()
                {
                    Route::get('edit', array( 'as' => 'form_of_tender.tenderAlternatives.template.edit', 'uses' => 'FormOfTenderTenderAlternativesController@editTenderAlternativesTemplate' ));
                    Route::post('update', array( 'as' => 'form_of_tender.tenderAlternatives.template.update', 'uses' => 'FormOfTenderTenderAlternativesController@updateTenderAlternativesTemplate' ));
                });
            });
        });

        Route::group(array('before' => 'canPreviewFormOfTenderTemplate'), function() {
            Route::group(['prefix' => 'template/{templateId}'], function() {
                Route::get('generate', array( 'as' => 'form_of_tender.template.generate', 'uses' => 'FormOfTenderPrintController@generateTemplateFormOfTender' ));
                Route::get('print', array( 'as' => 'form_of_tender.template.print', 'uses' => 'FormOfTenderPrintController@processTemplateFormOfTender' ));
            });
        });
    });

    Route::group(array( 'before' => 'moduleAccess:' . \PCK\ModulePermission\ModulePermission::MODULE_ID_TENDER_DOCUMENTS_TEMPLATE ), function()
    {
        Route::group(array( 'prefix' => 'tender-documents' ), function()
        {
            Route::get('directory', array( 'as' => 'tender_documents.template.directory', 'uses' => 'TemplateTenderDocumentFoldersController@directory' ));

            Route::get('set/create', array( 'as' => 'tender_documents.template.set.create', 'uses' => 'TemplateTenderDocumentFoldersController@createNewSet' ));

            Route::group(array( 'prefix' => 'set/{rootId}' ), function()
            {
                Route::get('/', array( 'as' => 'tender_documents.template.index', 'uses' => 'TemplateTenderDocumentFoldersController@index' ));
                Route::post('create', array( 'as' => 'tender_documents.template.create', 'uses' => 'TemplateTenderDocumentFoldersController@folderCreate' ));
                Route::post('repositionFolder', array( 'as' => 'tender_documents.template.reposition', 'uses' => 'TemplateTenderDocumentFoldersController@saveNewFolderPosition' ));
                Route::post('assign-work-category', array( 'as' => 'tender_documents.template.assign.workCategory', 'uses' => 'TemplateTenderDocumentFoldersController@assignWorkCategory' ));
                Route::delete('delete', array( 'as' => 'tender_documents.template.set.delete', 'uses' => 'TemplateTenderDocumentFoldersController@deleteSet' ));
            });

            Route::post('folder-info', array( 'as' => 'tender_documents.template.getFolderInfo', 'uses' => 'TemplateTenderDocumentFoldersController@getFolderInfo' ));

            Route::post('rename', array( 'as' => 'tender_documents.template.rename', 'uses' => 'TemplateTenderDocumentFoldersController@rename' ));
            Route::post('delete', array( 'as' => 'tender_documents.template.delete', 'uses' => 'TemplateTenderDocumentFoldersController@delete' ));
            Route::get('folder/{folderId}', array( 'as' => 'tender_documents.template.show', 'uses' => 'TemplateTenderDocumentFoldersController@show' ));

            Route::get('fileList/{folderId}', array( 'as' => 'tender_documents.template.fileList', 'uses' => 'TemplateTenderDocumentFoldersController@fileList' ));
            Route::get('fileInfo/{fileId}', array( 'as' => 'tender_documents.template.fileInfo', 'uses' => 'TemplateTenderDocumentFoldersController@fileInfo' ));
            Route::post('fileDelete/{fileId}', array( 'as' => 'tender_documents.template.file.delete', 'uses' => 'TemplateTenderDocumentFoldersController@fileDelete' ));

            Route::post('upload/{folderId}', array( 'as' => 'tender_documents.template.upload', 'uses' => 'TemplateTenderDocumentFoldersController@upload' ));
            Route::post('uploadDelete/{fileId}', array( 'as' => 'tender_documents.template.uploadDelete', 'uses' => 'TemplateTenderDocumentFoldersController@uploadDelete' ));
            Route::post('uploadUpdate', array( 'as' => 'tender_documents.template.uploadUpdate', 'uses' => 'TemplateTenderDocumentFoldersController@uploadUpdate' ));
            Route::get('fileDownload/{fileId}', array( 'as' => 'tender_documents.template.fileDownload', 'uses' => 'TemplateTenderDocumentFoldersController@fileDownload' ));

            Route::group(array( 'prefix' => 'folder/{folderId}/structured-documents' ), function()
            {
                Route::get('{id}', array( 'as' => 'structured_documents.template.edit', 'uses' => 'StructuredDocumentsController@editTemplate' ));
                Route::post('{id}/update', array( 'as' => 'structured_documents.template.update', 'uses' => 'StructuredDocumentsController@updateTemplate' ));
                Route::get('{id}/clauses', array( 'as' => 'structured_documents.template.clauses.edit', 'uses' => 'StructuredDocumentsController@editTemplateClauses' ));
                Route::post('{id}/clauses/update', array( 'as' => 'structured_documents.template.clauses.update', 'uses' => 'StructuredDocumentsController@updateTemplateClauses' ));
                Route::get('{id}/print', array( 'as' => 'structured_documents.template.print', 'uses' => 'StructuredDocumentsController@printTemplateDocument' ));
            });
        });
    });
});

Route::group(array( 'prefix' => 'technical-evaluation' ), function()
{
    Route::group(array( 'before' => 'moduleAccess:' . \PCK\ModulePermission\ModulePermission::MODULE_ID_TECHNICAL_EVALUATION_TEMPLATE ), function()
    {
        Route::get('/', array( 'as' => 'technicalEvaluation.sets', 'uses' => 'TechnicalEvaluationController@setsIndex' ));
        Route::get('/form-responses', array( 'as' => 'technicalEvaluation.getFormResponsesWithoutProject', 'uses' => 'TechnicalEvaluationController@getFormResponsesWithoutProject' ));
        Route::post('set/store', array( 'as' => 'technicalEvaluation.sets.store', 'uses' => 'TechnicalEvaluationController@storeSet' ));

        Route::group(array( 'prefix' => 'items' ), function()
        {
            Route::get('{itemId}/show', array( 'as' => 'technicalEvaluation.item.show', 'uses' => 'TechnicalEvaluationController@show' ));
            Route::post('store', array( 'as' => 'technicalEvaluation.item.store', 'uses' => 'TechnicalEvaluationController@store' ));
            Route::post('update', array( 'as' => 'technicalEvaluation.item.update', 'uses' => 'TechnicalEvaluationController@update' ));
            Route::delete('{itemId}/delete', array( 'as' => 'technicalEvaluation.item.delete', 'uses' => 'TechnicalEvaluationController@destroy' ));
        });

        Route::group(array( 'prefix' => '{setReferenceId}' ), function()
        {
            Route::post('technical-evaluation/companies/{companyId}/form/update', array('as' => 'technicalEvaluation.form.update.without.project', 'uses' => 'TechnicalEvaluationController@formUpdateWithoutProject' ));
            Route::delete('set/delete', array( 'as' => 'technicalEvaluation.sets.delete', 'uses' => 'TechnicalEvaluationController@storeDestroy' ));
            Route::get('attachments', array( 'as' => 'technicalEvaluation.attachments.listItem.index', 'uses' => 'TechnicalEvaluationAttachmentsController@show' ));
            Route::post('attachments/item/save', array( 'as' => 'technicalEvaluation.attachments.listItem.save', 'uses' => 'TechnicalEvaluationAttachmentsController@saveListItem' ));
            Route::delete('attachments/item/{listItemId}/delete-list-item', array( 'as' => 'technicalEvaluation.attachments.listItem.delete', 'uses' => 'TechnicalEvaluationAttachmentsController@deleteListItem' ));
            Route::post('toggle-hide', array( 'as' => 'technicalEvaluation.toggleHide', 'uses' => 'TechnicalEvaluationController@toggleHide' ));
        });

    });
});

Route::group(array( 'before' => 'moduleAccess:' . \PCK\ModulePermission\ModulePermission::MODULE_ID_TECHNICAL_EVALUATION_TEMPLATE ), function()
{
    Route::resource('contract-limit', 'ContractLimitController', array(
        'names' => array(
            'index'   => 'contractLimit.index',
            'create'  => 'contractLimit.create',
            'store'   => 'contractLimit.store',
            'edit'    => 'contractLimit.edit',
            'update'  => 'contractLimit.update',
            'destroy' => 'contractLimit.destroy',
        )
    ));
});