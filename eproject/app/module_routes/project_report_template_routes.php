<?php

Route::group(['prefix' => 'project_report_template', 'before' => 'projectReport.templatePermissionCheck'], function() {
    Route::get('/', ['as' => 'projectReport.template.index', 'uses' => 'ProjectReport\ProjectReportTemplatesController@index']);
    Route::get('/list', ['as' => 'projectReport.template.list', 'uses' => 'ProjectReport\ProjectReportTemplatesController@list']);
    Route::post('/store', ['as' => 'projectReport.template.store', 'uses' => 'ProjectReport\ProjectReportTemplatesController@store']);
    Route::group(['prefix' => '{projectReportId}'], function() {
        Route::get('/show', ['as' => 'projectReport.template.show', 'uses' => 'ProjectReport\ProjectReportTemplatesController@show']);
        Route::post('/clone', ['as' => 'projectReport.template.clone', 'uses' => 'ProjectReport\ProjectReportTemplatesController@cloneTemplate']);

        Route::post('/update', ['as' => 'projectReport.template.update', 'uses' => 'ProjectReport\ProjectReportTemplatesController@update']);
        
        Route::group(['before' => 'projectReport.isTemplateCheck|projectReport.isDraftCheck'], function() {
            Route::post('/lockRevision', ['as' => 'projectReport.template.lockRevision', 'uses' => 'ProjectReport\ProjectReportTemplatesController@lockRevision']);
        });

        Route::group(['before' => 'canDeleteTemplateCheck'], function() {
            Route::post('/delete', ['as' => 'projectReport.template.delete', 'uses' => 'ProjectReport\ProjectReportTemplatesController@destroy']);
        });

        Route::post('/newRevision', ['before' => 'projectReport.isCompletedCheck', 'as' => 'projectReport.template.newRevision', 'uses' => 'ProjectReport\ProjectReportTemplatesController@createNewRevision']);
    
        Route::group(['prefix' => 'column'], function() {
            Route::get('/getColumns', ['as' => 'projectReport.template.columns.get', 'uses' => 'ProjectReport\ProjectReportColumnsController@getColumns']);
            
            Route::group(['before' => 'projectReport.isTemplateCheck|projectReport.isDraftCheck'], function() {
                Route::post('/store', ['as' => 'projectReport.template.column.store', 'uses' => 'ProjectReport\ProjectReportColumnsController@store']);

                Route::group(['prefix' => '{reportColumnId}'], function() {
                    Route::post('/update', ['as' => 'projectReport.template.column.update', 'uses' => 'ProjectReport\ProjectReportColumnsController@update']);
                    Route::post('/delete', ['as' => 'projectReport.template.column.delete', 'uses' => 'ProjectReport\ProjectReportColumnsController@destroy']);
                    Route::post('/swap', ['as' => 'projectReport.template.column.swap', 'uses' => 'ProjectReport\ProjectReportColumnsController@swap']);
                });
            });
        });
    });

    Route::group(['prefix' => 'reportTypeMapping'], function() {
        Route::get('/', ['as' => 'projectReport.type.index', 'uses' => 'ProjectReport\ProjectReportTypesController@index']);
        Route::get('/list', ['as' => 'projectReport.types.list', 'uses' => 'ProjectReport\ProjectReportTypesController@list']);
        Route::post('/store', ['as' => 'projectReport.type.store', 'uses' => 'ProjectReport\ProjectReportTypesController@store']);
        Route::get('latestApprovedTemplates', ['as' => 'projectReport.latest.approved.templates.list', 'uses' => 'ProjectReport\ProjectReportTypesController@listLatestApprovedTemplates']);
        
        Route::group(['prefix' => '{reportTypeId}'], function() {
            Route::post('/update', ['as' => 'projectReport.type.update', 'uses' => 'ProjectReport\ProjectReportTypesController@update']);
            Route::post('/delete', ['as' => 'projectReport.type.delete', 'uses' => 'ProjectReport\ProjectReportTypesController@delete']);

            Route::group(['prefix' => 'mapping'], function() {
                Route::get('/', ['as' => 'projectReport.type.mapping.index', 'uses' => 'ProjectReport\ProjectReportTypeMappingsController@index']);
                Route::get('/list', ['as' => 'projectReport.type.mappings.list', 'uses' => 'ProjectReport\ProjectReportTypeMappingsController@list']);
                
                Route::group(['prefix' => '{mappingId}'], function() {
                    Route::post('/bind', ['as' => 'projectReport.type.mapping.bind', 'uses' => 'ProjectReport\ProjectReportTypeMappingsController@bind']);
                    Route::post('/toggleLatestRev', ['as' => 'projectReport.type.mapping.toggleLatestRev', 'uses' => 'ProjectReport\ProjectReportTypeMappingsController@updateLatestRevSetting']);
                    Route::post('/lock', ['as' => 'projectReport.type.mapping.lock', 'uses' => 'ProjectReport\ProjectReportTypeMappingsController@lock']);
                });
            });
        });
    });
});