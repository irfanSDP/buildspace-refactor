<?php

Route::group(['prefix' => 'project_report', 'before' => 'projectReport.hasProjectReportPermission'], function() {
    Route::group(['prefix' => 'submit'], function() {
        Route::get('/', ['as' => 'projectReport.index', 'uses' => 'ProjectReport\ProjectReportsController@index']);
        Route::get('/list', ['as' => 'projectReport.mappings.list', 'uses' => 'ProjectReport\ProjectReportsController@list']);

        Route::group(['prefix' => '/type/{mappingId}', 'before' => 'projectReport.hasProjectTypePermission'], function() {
            Route::get('/showAll', ['as' => 'projectReport.showAll', 'uses' => 'ProjectReport\ProjectReportsController@showAll']);
            Route::get('/show', ['as' => 'projectReport.show', 'uses' => 'ProjectReport\ProjectReportsController@show']);
            Route::get('/verify', ['as' => 'projectReport.verify', 'before' => 'projectReport.isCurrentVerifier', 'uses' => 'ProjectReport\ProjectReportsController@show']);
            Route::post('/newRevision', ['as' => 'projectReport.newRevision.create', 'uses' => 'ProjectReport\ProjectReportsController@createNewRevision']);

            Route::group(['prefix' => 'previousRevision'], function() {
                Route::get('/listPreviousRevisions', ['as' => 'projectReport.previousRevisions.list', 'uses' => 'ProjectReport\ProjectReportsController@listPreviousRevisions']);
            });

            Route::group(['prefix' => 'template/{templateId}'], function() {
                Route::get('/getColumnDefinitions', ['as' => 'projectReport.column.definitions.get', 'uses' => 'ProjectReport\ProjectReportsController@getColumnDefinitions']);

                Route::group(['prefix' => 'projectType/{projectType}'], function() {
                    Route::get('/getColumnContents', ['as' => 'projectReport.column.contents.get', 'uses' => 'ProjectReport\ProjectReportsController@getColumnContents']);
                });
            });
        });

        Route::group(['prefix' => '{projectReportId}'], function() {
            Route::get('/getColumns', ['as' => 'projectReport.columns.get', 'uses' => 'ProjectReport\ProjectReportsController@getProjectProjectReportColumns']);
            Route::get('/getActionLogs', ['as' => 'projectReport.actionLogs.get', 'uses' => 'ProjectReport\ProjectReportsController@getActionLogs']);

            Route::group(['before' => 'projectReport.isDraftCheck'], function() {
                Route::post('/delete', ['as' => 'projectReport.delete', 'uses' => 'ProjectReport\ProjectReportsController@delete']);
                Route::post('/saveColumnContents', ['as' => 'projectReport.columns.contents.save', 'uses' => 'ProjectReport\ProjectReportsController@saveColumnContents']);
            });
        });
    });

    Route::get('/previousRevision/{projectReportId}/show', ['as' => 'projectReport.previousRevision.show', 'uses' => 'ProjectReport\ProjectReportsController@previousRevisionShow']);

    Route::group(['prefix' => '{projectReportId}/attachments/field/{field}'], function() {
        Route::get('/getAttachmentCount', ['as' => 'projectReport.attachements.count.get', 'uses' => 'ProjectReport\ProjectReportsController@getAttachmentCount']);
        Route::get('/getAttachmentsList', ['as' => 'projectReport.attachements.get', 'uses' => 'ProjectReport\ProjectReportsController@getAttachmentsList']);
        Route::post('/update', ['as' => 'projectReport.attachements.update', 'before' => 'projectReport.isDraftCheck', 'uses' => 'ProjectReport\ProjectReportsController@attachmentsUpdate']);
    });

    Route::group(['prefix' => 'notification'], function() {
        Route::get('/', ['as' => 'projectReport.notification.reportTypes', 'uses' => 'ProjectReport\ProjectReportsController@index']);

        Route::group(['prefix' => '/type/{mappingId}', 'before' => 'projectReport.hasProjectTypePermission'], function() {
            Route::get('/', ['as' => 'projectReport.notification.index', 'uses' => 'ProjectReport\ProjectReportNotificationController@index']);
            Route::get('/list', ['as' => 'projectReport.notification.list', 'uses' => 'ProjectReport\ProjectReportNotificationController@getList']);
            Route::get('/partials', ['as' => 'projectReport.notification.partials', 'uses' => 'ProjectReport\ProjectReportNotificationController@getPartials']);
            Route::get('/create', ['as' => 'projectReport.notification.create', 'uses' => 'ProjectReport\ProjectReportNotificationController@create']);
            Route::post('/store', ['as' => 'projectReport.notification.store', 'uses' => 'ProjectReport\ProjectReportNotificationController@store']);

            Route::group(['prefix' => '/template/{templateId}'], function() {
                Route::get('/preview', ['as' => 'projectReport.notification.preview', 'uses' => 'ProjectReport\ProjectReportNotificationController@getPreview']);
                Route::get('/edit', ['as' => 'projectReport.notification.edit', 'uses' => 'ProjectReport\ProjectReportNotificationController@edit']);
                Route::post('/update', ['as' => 'projectReport.notification.update', 'uses' => 'ProjectReport\ProjectReportNotificationController@update']);
                Route::post('/publish', ['as' => 'projectReport.notification.publish', 'uses' => 'ProjectReport\ProjectReportNotificationController@publish']);
                Route::post('/delete', ['as' => 'projectReport.notification.delete', 'uses' => 'ProjectReport\ProjectReportNotificationController@destroy']);
            });
        });
    });
});

