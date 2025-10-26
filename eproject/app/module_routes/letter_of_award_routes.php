<?php

Route::group(array( 'prefix' => 'letter-of-award'), function() {
    Route::group(['prefix' =>  'user_permissions'], function() {
        Route::get('/', ['as' => 'letterOfAward.user.permissions.index', 'uses' => 'LetterOfAwardUserPermissionsController@index']);
        Route::get('assignable/get', ['as' => 'letterOfAward.user.permissions.assignable', 'uses' => 'LetterOfAwardUserPermissionsController@getAssignableUsers']);
        Route::get('assigned/get', ['as' => 'letterOfAward.user.permissions.assigned', 'uses' => 'LetterOfAwardUserPermissionsController@getAssignedUsers']);
        Route::post('assign', ['as' => 'letterOfAward.user.permissions.assign', 'uses' => 'LetterOfAwardUserPermissionsController@assign']);
        Route::post('editor_status/toggle/user/{userId}/module/{moduleId}', ['as' => 'letterOfAward.user.permissions.editor.toggle', 'uses' => 'LetterOfAwardUserPermissionsController@toggleEditorStatus']);
        Route::get('user/{userId}/module/{moduleId}/revokePendingCheck', ['as' => 'letterOfAward.pending.check', 'uses' => 'LetterOfAwardController@getUserHasPendingApprovalLetterOfAward']);
        Route::delete('user/{userId}/module/{moduleId}', ['as' => 'letterOfAward.user.permissions.revoke', 'uses' => 'LetterOfAwardUserPermissionsController@revoke']);
    });

    Route::group(['before' => 'checkForLetterOfAwardPermission', 'prefix' => 'letterOfAward'], function() {
        Route::get('/', ['as' => 'letterOfAward.index', 'uses' => 'LetterOfAwardController@index']);

        Route::group(['prefix' => 'contractDetails'], function() {
            Route::get('/edit', ['as' => 'letterOfAward.contractDetails.edit', 'uses' => 'LetterOfAwardController@contractDetailsEdit']);
            Route::get('/get', ['as' => 'letterOfAward.contractDetails.get', 'uses' => 'LetterOfAwardController@getContractDetails']);
            Route::post('/save', ['as' => 'letterOfAward.contractDetails.save', 'uses' => 'LetterOfAwardController@saveContractDetails']);
        });

        Route::group(['prefix' => 'signatory'], function() {
            Route::get('/edit', ['as' => 'letterOfAward.signatory.edit', 'uses' => 'LetterOfAwardController@signatoryEdit']);
            Route::get('/get', ['as' => 'letterOfAward.signatory.get', 'uses' => 'LetterOfAwardController@getSignatory']);
            Route::post('/save', ['as' => 'letterOfAward.signatory.save', 'uses' => 'LetterOfAwardController@saveSignatory']);
        });
    
        Route::group(['prefix' => 'clause'], function() {
            Route::get('/edit', ['as' => 'letterOfAward.clause.edit', 'uses' => 'LetterOfAwardController@clausesEdit']);
            Route::get('/get', ['as' => 'letterOfAward.clause.get', 'uses' => 'LetterOfAwardController@getclauses']);
            Route::post('/save', ['as' => 'letterOfAward.clause.save', 'uses' => 'LetterOfAwardController@saveclauses']);

            Route::group(['prefix' => 'comments'], function() {
                Route::get('/get', ['as' => 'letterOfAward.clause.comments.get', 'uses' => 'LetterOfAwardClauseCommentController@getClauseComments']);
                Route::post('/save', ['as' => 'letterOfAward.clause.comments.save', 'uses' => 'LetterOfAwardClauseCommentController@saveClauseComment']);
            });

            Route::group(['prefix' => 'verification'], function() {
                Route::post('/submitForApproval', ['as' => 'letterOfAward.approval.submit', 'uses' => 'LetterOfAwardController@submit']);
                Route::post('/verify', ['as' => 'letterOfAward.verify', 'uses' => 'LetterOfAwardController@verify']);
            });
        });

        Route::get('/print', ['as' => 'letterOfAward.print', 'uses' => 'LetterOfAwardController@print']);
        Route::get('/settings', ['as' => 'letterOfAward.print.settings.edit', 'uses' => 'LetterOfAwardController@editPrintSettings']);
        Route::post('/save', ['as' => 'letterOfAward.print.settings.save', 'uses' => 'LetterOfAwardController@savePrintSettings']);
        Route::get('/process', ['as' => 'letterOfAward.process', 'uses' => 'LetterOfAwardController@processLetterOfAward']);
        Route::post('/notifyReviewer', ['as' => 'letterOfAward.reviewer.notify', 'uses' => 'LetterOfAwardController@notifyReviewer']);
        Route::post('/sendCommentNotification', ['as' => 'letterOfAward.comment.notification.send', 'uses' => 'LetterOfAwardController@sendCommentNotification']);
        
        Route::group(['prefix' => 'log'], function() {
            Route::post('getLogs', ['as' => 'letterOfAward.log.get', 'uses' => 'LetterOfAwardController@getLogs']);
        });
    });
});

