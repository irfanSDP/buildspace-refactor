<?php

Route::group(array('prefix' => 'project_report_chart_template', 'before' => 'ProjectChart.templatePermission'), function () {
    Route::get('/', array('as' => 'projectReport.chart.template.index', 'uses' => 'ProjectReport\ProjectReportChartTemplateController@index'));
    Route::get('/list', array('as' => 'projectReport.chart.template.list', 'uses' => 'ProjectReport\ProjectReportChartTemplateController@getList'));

    Route::get('/create', array('as' => 'projectReport.chart.template.create', 'uses' => 'ProjectReport\ProjectReportChartTemplateController@create'));
    Route::post('/store', array('as' => 'projectReport.chart.template.store', 'uses' => 'ProjectReport\ProjectReportChartTemplateController@store'));

    Route::post('/rearrange', array('as' => 'projectReport.chart.template.rearrange', 'uses' => 'ProjectReport\ProjectReportChartTemplateController@rearrange'));

    Route::group(array('prefix' => '{chartId}'), function () {
        Route::get('/edit', array('as' => 'projectReport.chart.template.edit', 'uses' => 'ProjectReport\ProjectReportChartTemplateController@edit'));
        Route::post('/update', array('as' => 'projectReport.chart.template.update', 'uses' => 'ProjectReport\ProjectReportChartTemplateController@update'));

        Route::post('/delete', array('as' => 'projectReport.chart.template.delete', 'uses' => 'ProjectReport\ProjectReportChartTemplateController@destroy'));

        Route::post('/lock', array('as' => 'projectReport.chart.template.lock', 'uses' => 'ProjectReport\ProjectReportChartTemplateController@lock'));

        Route::post('/publish', array('as' => 'projectReport.chart.template.publish', 'uses' => 'ProjectReport\ProjectReportChartTemplateController@publish'));

        Route::group(array('prefix' => 'plots'), function () {
            Route::get('/', array('as' => 'projectReport.chart.plot.template.index', 'uses' => 'ProjectReport\ProjectReportChartPlotTemplateController@index'));
            Route::get('/list', array('as' => 'projectReport.chart.plot.template.list', 'uses' => 'ProjectReport\ProjectReportChartPlotTemplateController@getList'));

            Route::get('/create', array('as' => 'projectReport.chart.plot.template.create', 'uses' => 'ProjectReport\ProjectReportChartPlotTemplateController@create'));
            Route::post('/store', array('as' => 'projectReport.chart.plot.template.store', 'uses' => 'ProjectReport\ProjectReportChartPlotTemplateController@store'));

            Route::get('/partials', array('as' => 'projectReport.chart.plot.template.partials', 'uses' => 'ProjectReport\ProjectReportChartPlotTemplateController@getPartials'));

            Route::group(array('prefix' => '{plotId}'), function () {
                Route::get('/edit', array('as' => 'projectReport.chart.plot.template.edit', 'uses' => 'ProjectReport\ProjectReportChartPlotTemplateController@edit'));
                Route::post('/update', array('as' => 'projectReport.chart.plot.template.update', 'uses' => 'ProjectReport\ProjectReportChartPlotTemplateController@update'));

                Route::post('/delete', array('as' => 'projectReport.chart.plot.template.delete', 'uses' => 'ProjectReport\ProjectReportChartPlotTemplateController@destroy'));
            });
        });
    });
});