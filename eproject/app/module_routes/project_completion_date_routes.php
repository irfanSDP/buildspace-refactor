<?php

Route::group(['prefix' => 'completion_date'], function() {
    Route::get('/getRecords', ['as' => 'project.sectionalCompletionDate.records.get', 'uses' => 'ProjectSectionalCompletionDatesController@getRecords']);
    
    Route::group(['before' => 'projectSectionalCompletionDate.maintain.permissionCheck'], function() {
        Route::post('/add', ['as' => 'project.sectionalCompletionDate.record.add', 'uses' => 'ProjectSectionalCompletionDatesController@add']);
    });

    Route::group(['prefix' => '{recordId}', 'before' => 'projectSectionalCompletionDate.maintain.permissionCheck'], function() {
        Route::post('/update', ['as' => 'project.sectionalCompletionDate.record.update', 'uses' => 'ProjectSectionalCompletionDatesController@update']);
        Route::delete('/delete', ['as' => 'project.sectionalCompletionDate.record.delete', 'uses' => 'ProjectSectionalCompletionDatesController@delete']);
    });
});