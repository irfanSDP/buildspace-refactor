<?php

Route::group(['prefix' => 'letter_of_award_template'], function() {
    Route::get('/', ['as' => 'letterOfAward.templates.selection', 'uses' => 'LetterOfAwardTemplateSelectionController@index']);
    Route::get('/allTemplates', ['as' => 'letterOfAward.templates.all.get', 'uses' => 'LetterOfAwardTemplateSelectionController@getAllTemplates']);
    Route::post('/store', ['as' => 'letterOfAward.template.store', 'uses' => 'LetterOfAwardTemplateController@store']);

    Route::group(['prefix' => 'template/{templateId}'], function() {
        Route::get('/', ['as' => 'letterOfAward.template.index', 'uses' => 'LetterOfAwardTemplateController@index']);
        Route::post('/update', ['as' => 'letterOfAward.template.update', 'uses' => 'LetterOfAwardTemplateSelectionController@update']);
        Route::delete('/delete', ['as' => 'letterOfAward.template.delete', 'uses' => 'LetterOfAwardTemplateSelectionController@destroy']);
    
        Route::group(['prefix' => 'contractDetails'], function() {
            Route::get('/edit', ['as' => 'letterOfAward.template.contractDetails.edit', 'uses' => 'LetterOfAwardTemplateController@contractDetailsEdit']);
            Route::get('/get', ['as' => 'letterOfAward.template.contractDetails.get', 'uses' => 'LetterOfAwardTemplateController@getContractDetails']);
            Route::post('/save', ['as' => 'letterOfAward.template.contractDetails.save', 'uses' => 'LetterOfAwardTemplateController@saveContractDetails']);
        });

        Route::group(['prefix' => 'signatory'], function() {
            Route::get('/edit', ['as' => 'letterOfAward.template.signatory.edit', 'uses' => 'LetterOfAwardTemplateController@signatoryEdit']);
            Route::get('/get', ['as' => 'letterOfAward.template.signatory.get', 'uses' => 'LetterOfAwardTemplateController@getSignatory']);
            Route::post('/save', ['as' => 'letterOfAward.template.signatory.save', 'uses' => 'LetterOfAwardTemplateController@saveSignatory']);
        });

        Route::group(['prefix' => 'clause'], function() {
            Route::get('/edit', ['as' => 'letterOfAward.template.clause.edit', 'uses' => 'LetterOfAwardTemplateController@clausesEdit']);
            Route::get('/get', ['as' => 'letterOfAward.template.clause.get', 'uses' => 'LetterOfAwardTemplateController@getclauses']);
            Route::post('/save', ['as' => 'letterOfAward.template.clause.save', 'uses' => 'LetterOfAwardTemplateController@saveclauses']);
        });

        Route::get('/print', ['as' => 'letterOfAward.template.print', 'uses' => 'LetterOfAwardTemplateController@print']);
        Route::get('/settings', ['as' => 'letterOfAward.template.print.settings.edit', 'uses' => 'LetterOfAwardTemplateController@editPrintSettings']);
        Route::post('/save', ['as' => 'letterOfAward.template.print.settings.save', 'uses' => 'LetterOfAwardTemplateController@savePrintSettings']);
        Route::get('/process', ['as' => 'letterOfAward.template.process', 'uses' => 'LetterOfAwardTemplateController@processLetterOfAward']);
    
        Route::group(['prefix' => 'log'], function() {
            Route::post('getLogs', ['as' => 'letterOfAward.template.log.get', 'uses' => 'LetterOfAwardTemplateController@getLogs']);
        });
    });
});

