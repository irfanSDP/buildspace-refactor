<?php 

Route::group(['prefix' => 'form_designer'], function() {
    Route::post('/store', ['as' => 'new.form.template.store', 'uses' => 'DynamicFormsController@store']);

    Route::group(['prefix' => 'form/{formId}'], function() {
        Route::get('/getFormContents', ['as' => 'form.contents.get', 'uses' => 'DynamicFormsController@getFormContents']);

        Route::group(['before' => 'form.canCreateRevision'], function() {
            Route::post('/newRevision', ['as' => 'form.new.revision.create', 'uses' => 'DynamicFormsController@createNewRevision']);
        });

        Route::post('/clone', ['as' => 'form.clone', 'uses' => 'DynamicFormsController@clone']);
        Route::get('/formDesigner', ['as' => 'form.designer.show', 'uses' => 'DynamicFormsController@show']);

        Route::group(['before' => 'form.canBeEdited'], function() {
            Route::post('/swap', ['as' => 'form.column.swap', 'uses' => 'FormColumnsController@swap']);  
        });

        Route::group(['before' => 'form.canBeEditedAjax'],function() {
            Route::post('/update', ['as' => 'form.update', 'uses' => 'DynamicFormsController@update']);
            Route::delete('/delete', ['as' => 'form.delete', 'uses' => 'DynamicFormsController@delete']);

            Route::group(['prefix' => 'column'], function() {
                Route::post('/store', ['as' => 'form.column.store', 'uses' => 'FormColumnsController@createNewColumn']);
            });
        });
        
        Route::group(['before' => 'form.readyForSubmission'], function() {
            Route::get('/', ['as' => 'vendor.form.show', 'uses' => 'DynamicFormsController@show']);
            Route::post('/', ['as' => 'vendor.form.submit', 'uses' => 'DynamicFormsController@submitForm']);
        });

        Route::group(['before' => 'form.canSubmitForApproval'], function() {
            Route::post('/submitFormDesignForApproval', ['as' => 'form.submit.for.approval', 'uses' => 'DynamicFormsController@submitFormDesignForApproval']);
        });

        Route::get('/getPreviousRevisionForms', ['as' => 'previous.revision.forms.get', 'uses' => 'DynamicFormsController@getPreviousRevisionForms']);
    });

    Route::group(['prefix' => 'column/{columnId}', 'before' => 'form.column.canBeEdited',], function() {
        Route::post('/update', ['as' => 'form.column.update', 'uses' => 'FormColumnsController@update']);
        Route::post('/delete', ['as' => 'form.column.delete', 'uses' => 'FormColumnsController@delete']);
        Route::post('/sectionSwap', ['as' => 'form.column.section.swap', 'uses' => 'FormColumnSectionsController@swap']);

        Route::group(['prefix' => 'section'], function() {
            Route::post('/store', ['as' => 'form.column.section.store', 'uses' => 'FormColumnSectionsController@store']);
        });
    });

    Route::group(['prefix' => 'section/{sectionId}', 'before' => 'form.section.canBeEdited'], function() {
        Route::post('/update', ['as' => 'form.column.section.update', 'uses' => 'FormColumnSectionsController@update']);
        Route::post('/delete', ['as' => 'form.column.section.delete', 'uses' => 'FormColumnSectionsController@delete']);
        Route::post('/swap', ['as' => 'form.column.section.element.swap', 'uses' => 'ElementsController@swap']);

        Route::group(['prefix' => 'element'], function() {
            Route::post('/store', ['as' => 'form.column.section.element.store', 'uses' => 'ElementsController@store']);
        });
    });

    Route::group(['prefix' => 'element/{elementId}/elementType/{elementType}', 'before' => 'form.element.canBeEdited'], function() {
        Route::get('/getElementDetails', ['as' => 'form.column.section.element.details.get', 'uses' => 'ElementsController@getElementDetails']);
        Route::post('/update', ['as' => 'form.column.section.element.update', 'uses' => 'ElementsController@update']);
        Route::post('/delete', ['as' => 'form.column.section.element.delete', 'uses' => 'ElementsController@delete']);
    });
});

