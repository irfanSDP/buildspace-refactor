<?php

Route::group(['prefix' => 'project_report/user_permissions', 'before' => 'projectReport.userPermissionAccessCheck'], function() {
    Route::get('/', ['as' => 'projectReport.userPermissions.index', 'uses' => 'ProjectReport\ProjectReportUserPermissionsController@index']);
    Route::get('list', ['as' => 'projectReport.userPermissions.reportTypes.list', 'uses' => 'ProjectReport\ProjectReportUserPermissionsController@projectReportTypesList']);
    Route::get('getAssignedUsers', ['as' => 'projectReport.userPermissions.assignedUsers.get', 'uses' => 'ProjectReport\ProjectReportUserPermissionsController@getAssignedUsers']);
    Route::get('getAssignableUsers', ['as' => 'projectReport.userPermissions.assignableUsers.get', 'uses' => 'ProjectReport\ProjectReportUserPermissionsController@getAssignableUsers']);
    Route::post('grant', ['as' => 'projectReport.userPermissions.grant', 'uses' => 'ProjectReport\ProjectReportUserPermissionsController@grant']);
    Route::get('checkFuturePendingTasks', ['as' => 'projectReport.userPermissions.futurePendingTasks.check', 'uses' => 'ProjectReport\ProjectReportUserPermissionsController@checkFuturePendingTasks']);
    Route::post('revoke', ['as' => 'projectReport.userPermissions.revoke', 'uses' => 'ProjectReport\ProjectReportUserPermissionsController@revoke']);
});