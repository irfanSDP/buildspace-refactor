<?php

Route::group(array('prefix' => 'projectReport/charts', 'before' => 'ProjectChart.chartPermission'), function() {
    Route::get('/', array('as' => 'projectReport.charts.index', 'uses' => 'ProjectReport\ProjectReportChartController@index'));
    Route::get('/list', array('as' => 'projectReport.charts.list', 'uses' => 'ProjectReport\ProjectReportChartController@getList'));

    Route::get('/showAll', array('as' => 'projectReport.charts.showAll', 'uses' => 'ProjectReport\ProjectReportChartController@showAll'));

    Route::group(array('prefix' => '{chartId}'), function() {
        Route::get('/filters/subsidiaries', array('as' => 'projectReport.charts.filters.subsidiaries', 'uses' => 'ProjectReport\ProjectReportChartController@getSubsidiariesFilter'));
        Route::get('/data', array('as' => 'projectReport.charts.data', 'uses' => 'ProjectReport\ProjectReportChartController@getChartData'));
        Route::get('/show', array('as' => 'projectReport.charts.show', 'uses' => 'ProjectReport\ProjectReportChartController@show'));
    });
});