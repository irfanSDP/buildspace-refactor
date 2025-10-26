<?php

Route::group(array( 'before' => 'moduleAccess:' . \PCK\ModulePermission\ModulePermission::MODULE_ID_PROJECTS_OVERVIEW, 'prefix' => 'projects-overview' ), function()
{
    Route::get('/', array( 'as' => 'projectsOverview', 'uses' => 'ProjectsOverviewController@index' ));
    Route::get('data', array( 'as' => 'projectsOverview.data', 'uses' => 'ProjectsOverviewController@getIndexData' ));
    Route::get('export',array('as' => 'projectsOverview.excel.export', 'uses' => 'ProjectsOverviewController@exportProjectsOverviewExcel'));
});