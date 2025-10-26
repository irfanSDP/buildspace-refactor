<?php

Route::group(['prefix' => 'projectReport/dashboard', 'before' => 'projectReport.dashboard.permissionCheck'], function() {
    Route::get('/', ['as' => 'projectReport.dashboard.index', 'uses' => 'ProjectReport\ProjectReportsDashboardController@index']);
    Route::get('/listProjectReportTypes', ['as' => 'projectReport.dashboard.projectTypes.list', 'uses' => 'ProjectReport\ProjectReportsDashboardController@listProjectReportTypes']);

    Route::group(['prefix' => 'type/{mappingId}'], function() {
        Route::get('/show', ['as' => 'projectReport.dashboard.projectReport.show', 'uses' => 'ProjectReport\ProjectReportsDashboardController@show']);

        Route::group(['prefix' => 'template/{templateId}'], function() {
            Route::get('getColumnDefinitions', ['as' => 'projectReport.dashboard.column.definitions.get', 'uses' => 'ProjectReport\ProjectReportsDashboardController@getColumnDefinitions']);
            Route::group(['prefix' => 'projectType/{projectType}'], function() {
                Route::get('getColumnContents', ['as' => 'projectReport.dashboard.column.contents.get', 'uses' => 'ProjectReport\ProjectReportsDashboardController@getColumnContents']);
                Route::get('excelExport', ['as' => 'projectReport.dashboard.excel.export', 'uses' => 'ProjectReport\ProjectReportsDashboardController@exportExcel']);
            });
        });
    });

    Route::group(['prefix' => 'projectReport/{projectReportId}'], function() {
        Route::post('remarks', ['as' => 'projectReport.dashboard.remarks.update', 'uses' => 'ProjectReport\ProjectReportsDashboardController@updateRemarks']);
    
        Route::get('listAllReportsInLine', ['as' => 'projectReport.dashboard.allReportsInLine.get', 'uses' => 'ProjectReport\ProjectReportsDashboardController@listAllReportsInLine']);
        
        Route::group(['prefix' => 'attachments/field/{field}'], function() {
            Route::get('/getAttachmentsList', ['as' => 'projectReport.dashboard.attachments.get', 'uses' => 'ProjectReport\ProjectReportsDashboardController@getAttachmentsList']);
            Route::get('/downloadAttachmentsAsZip', ['as' => 'projectReport.dashboard.attachments.downloadAsZip', 'uses' => 'ProjectReport\ProjectReportsDashboardController@downloadAttachmentsAsZip']);
        });
    });
    
    Route::group(['prefix' => 'subPackage/{projectReportId}'], function() {        
        Route::get('/show', ['as' => 'projectReport.dashboard.projectReport.subpackage.show', 'uses' => 'ProjectReport\ProjectReportsDashboardController@subPackageShow']);
    });
});