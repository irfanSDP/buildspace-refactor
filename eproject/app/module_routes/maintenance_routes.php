<?php

Route::group(array( 'prefix' => 'modules/permissions', 'before' => 'superAdminAccessLevel' ), function()
{
    Route::get('/', array( 'as' => 'module.permissions.index', 'uses' => 'ModulePermissionsController@index' ));
    Route::get('assigned/get', array( 'as' => 'module.permissions.assigned', 'uses' => 'ModulePermissionsController@getAssignedUsers' ));
    Route::get('assignable/get', array( 'as' => 'module.permissions.assignable', 'uses' => 'ModulePermissionsController@getAssignableUsers' ));
    Route::post('assign', array( 'as' => 'module.permissions.assign', 'uses' => 'ModulePermissionsController@assign' ));
    Route::post('user/{userId}/module/{moduleId}', array( 'as' => 'module.permissions.editor.toggle', 'uses' => 'ModulePermissionsController@toggleEditorStatus' ));
    Route::delete('user/{userId}/module/{moduleId}', array( 'as' => 'module.permissions.revoke', 'uses' => 'ModulePermissionsController@revoke' ));

    Route::group(array('prefix' => 'subsidiaries'), function() {
        Route::get('getList', array( 'as' => 'module.permissions.subsidiary.getList', 'uses' => 'ModulePermissionSubsidiariesController@getSubsidiariesList' ));
        Route::get('getAssigned', array( 'as' => 'module.permissions.subsidiary.getAssigned', 'uses' => 'ModulePermissionSubsidiariesController@getAssignedSubsidiaries' ));
        Route::post('assignToUser', array( 'as' => 'module.permissions.subsidiary.assignToUser', 'uses' => 'ModulePermissionSubsidiariesController@assignSubsidiariesToUser' ));
    });
});

Route::group(array( 'prefix' => 'dashboard/group', 'before' => 'superAdminAccessLevel' ), function()
{
    Route::get('/', array( 'as' => 'dashboard.group.index', 'uses' => 'DashboardGroupController@index' ));
    Route::get('show/{id}', array( 'as' => 'dashboard.group.show', 'uses' => 'DashboardGroupController@show' ));
    Route::post('store/{id}', array( 'as' => 'dashboard.group.store', 'uses' => 'DashboardGroupController@store' ));

    Route::post('users', array( 'as' => 'dashboard.group.assign.user', 'uses' => 'DashboardGroupController@assignUsers' ));
    Route::get('assigned/{id}', array( 'as' => 'dashboard.group.assigned.user', 'uses' => 'DashboardGroupController@getAssignedUsers' ));
    Route::delete('user/{type}/{id}', array( 'as' => 'dashboard.group.user.remove', 'uses' => 'DashboardGroupController@removeUser' ));

    Route::get('assignable', array( 'as' => 'dashboard.group.assignable', 'uses' => 'DashboardGroupController@getAssignableUsers' ));
    Route::get('excludable', array( 'as' => 'dashboard.group.excludable', 'uses' => 'DashboardGroupController@getExcludableProjects' ));

    Route::post('projects', array( 'as' => 'dashboard.group.exclude.project', 'uses' => 'DashboardGroupController@excludeProjects' ));
    Route::get('excluded/{id}', array( 'as' => 'dashboard.group.excluded.project', 'uses' => 'DashboardGroupController@getExcludedProjects' ));
    Route::delete('project/{type}/{id}', array( 'as' => 'dashboard.group.project.remove', 'uses' => 'DashboardGroupController@removeProject' ));

});

Route::group(array( 'prefix' => 'calendars', 'before' => 'superAdminAccessLevel' ), function()
{
    Route::get('/', array( 'as' => 'calendars', 'uses' => 'CalendarController@index' ));
});

Route::group(array( 'prefix' => 'business-entity-types' ), function(){
    Route::get('/', array( 'as' => 'businessEntityTypes.index', 'uses' => 'BusinessEntityTypesController@index' ));
    Route::get('create', array( 'as' => 'businessEntityTypes.create', 'uses' => 'BusinessEntityTypesController@create' ));
    Route::post('/', array( 'as' => 'businessEntityTypes.store', 'uses' => 'BusinessEntityTypesController@store' ));
    Route::post('update', array( 'as' => 'businessEntityTypes.update', 'uses' => 'BusinessEntityTypesController@update' ));
});

Route::group(array( 'prefix' => 'property-developers' ), function(){
    Route::get('/', array( 'as' => 'propertyDevelopers.index', 'uses' => 'PropertyDevelopersController@index' ));
    Route::get('create', array( 'as' => 'propertyDevelopers.create', 'uses' => 'PropertyDevelopersController@create' ));
    Route::post('/', array( 'as' => 'propertyDevelopers.store', 'uses' => 'PropertyDevelopersController@store' ));
    Route::post('update', array( 'as' => 'propertyDevelopers.update', 'uses' => 'PropertyDevelopersController@update' ));
});

Route::group(array( 'prefix' => 'vendor-performance-evaluation/project-removal-reasons' ), function(){
    Route::get('/', array( 'as' => 'vendorPerformanceEvaluation.projectRemovalReasons.index', 'uses' => 'VendorPerformanceEvaluationProjectRemovalReasonsController@index' ));
    Route::get('create', array( 'as' => 'vendorPerformanceEvaluation.projectRemovalReasons.create', 'uses' => 'VendorPerformanceEvaluationProjectRemovalReasonsController@create' ));
    Route::post('/', array( 'as' => 'vendorPerformanceEvaluation.projectRemovalReasons.store', 'uses' => 'VendorPerformanceEvaluationProjectRemovalReasonsController@store' ));
    Route::post('update', array( 'as' => 'vendorPerformanceEvaluation.projectRemovalReasons.update', 'uses' => 'VendorPerformanceEvaluationProjectRemovalReasonsController@update' ));
});

Route::group(array( 'prefix' => 'contract-group-categories', 'before' => 'superAdminAccessLevel' ), function()
{
    Route::get('match', array( 'as' => 'contractGroupCategories.match', 'uses' => 'ContractGroupCategoriesController@match' ));
    Route::post('match/update', array( 'as' => 'contractGroupCategories.match.update', 'uses' => 'ContractGroupCategoriesController@matchUpdate' ));

    Route::group(array( 'prefix' => 'privileges' ), function()
    {
        Route::get('/', array( 'as' => 'contractGroupCategories.privileges.index', 'uses' => 'ContractGroupCategoriesController@privilegesPage' ));
        Route::post('update', array( 'as' => 'contractGroupCategories.privileges.update', 'uses' => 'ContractGroupCategoriesController@togglePrivileges' ));
    });
});

Route::group(array( 'prefix' => 'maintenance', 'before' => 'superAdminAccessLevel' ), function()
{
    Route::group(array( 'prefix' => 'consultant-management' ), function()
    {
        Route::group(array( 'prefix' => 'roles' ), function()
        {
            Route::get('/', array( 'as' => 'consultant.management.maintenance.roles.index', 'uses' => 'ConsultantManagementMaintenanceController@roles' ));
            Route::post('store', array( 'as' => 'consultant.management.maintenance.roles.store', 'uses' => 'ConsultantManagementMaintenanceController@rolesStore' ));
        });

        Route::group(array( 'prefix' => 'development-type' ), function()
        {
            Route::get('/', array( 'as' => 'consultant.management.maintenance.development.type.index', 'uses' => 'ConsultantManagementMaintenanceController@developmentType' ));
            Route::get('list', array( 'as' => 'consultant.management.maintenance.development.type.ajax.list', 'uses' => 'ConsultantManagementMaintenanceController@developmentTypeList' ));
            Route::get('create', array( 'as' => 'consultant.management.maintenance.development.type.create', 'uses' => 'ConsultantManagementMaintenanceController@developmentTypeCreate' ));
            Route::get('{developmentTypeId}/edit', array( 'as' => 'consultant.management.maintenance.development.type.edit', 'uses' => 'ConsultantManagementMaintenanceController@developmentTypeEdit' ));
            Route::post('store', array( 'as' => 'consultant.management.maintenance.development.type.store', 'uses' => 'ConsultantManagementMaintenanceController@developmentTypeStore' ));
            Route::delete('{developmentTypeId}/delete', array( 'as' => 'consultant.management.maintenance.development.type.delete', 'uses' => 'ConsultantManagementMaintenanceController@developmentTypeDelete' ));
        });

        Route::group(array( 'prefix' => 'product-type' ), function()
        {
            Route::get('/', array( 'as' => 'consultant.management.maintenance.product.type.index', 'uses' => 'ConsultantManagementMaintenanceController@productType' ));
            Route::get('list', array( 'as' => 'consultant.management.maintenance.product.type.ajax.list', 'uses' => 'ConsultantManagementMaintenanceController@productTypeList' ));
            Route::get('create', array( 'as' => 'consultant.management.maintenance.product.type.create', 'uses' => 'ConsultantManagementMaintenanceController@productTypeCreate' ));
            Route::get('{productTypeId}/edit', array( 'as' => 'consultant.management.maintenance.product.type.edit', 'uses' => 'ConsultantManagementMaintenanceController@productTypeEdit' ));
            Route::post('store', array( 'as' => 'consultant.management.maintenance.product.type.store', 'uses' => 'ConsultantManagementMaintenanceController@productTypeStore' ));
            Route::delete('{productTypeId}/delete', array( 'as' => 'consultant.management.maintenance.product.type.delete', 'uses' => 'ConsultantManagementMaintenanceController@productTypeDelete' ));
        });
    });
});

Route::group(array( 'prefix' => 'vendor-groups' ), function()
{
    Route::group(array( 'prefix' => 'internal' ), function()
    {
        Route::get('/', array( 'as' => 'vendorGroups.internal.index', 'uses' => 'InternalVendorGroupsController@index' ));
        Route::get('list', array( 'as' => 'vendorGroups.internal.ajax.list', 'uses' => 'InternalVendorGroupsController@list' ));
        Route::get('create', array( 'as' => 'vendorGroups.internal.create', 'uses' => 'InternalVendorGroupsController@create' ));
        Route::get('{contractGroupCategoryId}/edit', array( 'as' => 'vendorGroups.internal.edit', 'uses' => 'InternalVendorGroupsController@edit' ));
        Route::post('/', array( 'as' => 'vendorGroups.internal.store', 'uses' => 'InternalVendorGroupsController@store' ));
        Route::post('update-settings', array( 'as' => 'vendorGroups.internal.updateSettings', 'uses' => 'InternalVendorGroupsController@updateSettings' ));
    });

    Route::get('vendor-category/{vendorCategoryId}/summary/vendor-work-categories', array( 'as' => 'vendorCategories.summary.vendorWorkCategories', 'uses' => 'VendorCategoriesController@vendorWorkCategories' ));
    Route::get('vendor-work-category/{vendorWorkCategoryId}/summary/vendor-categories', array( 'as' => 'vendorCategories.vendorWorkCategories.summary.vendorCategories', 'uses' => 'VendorCategoryVendorWorkCategoryController@vendorCategoriesByVendorWorkCategory' ));

    Route::group(array( 'prefix' => 'external' ), function()
    {
        Route::get('/', array( 'as' => 'vendorGroups.external.index', 'uses' => 'ExternalVendorGroupsController@index' ));
        Route::get('list', array( 'as' => 'vendorGroups.external.ajax.list', 'uses' => 'ExternalVendorGroupsController@list' ));
        Route::get('create', array( 'as' => 'vendorGroups.external.create', 'uses' => 'ExternalVendorGroupsController@create' ));
        Route::get('{contractGroupCategoryId}/edit', array( 'as' => 'vendorGroups.external.edit', 'uses' => 'ExternalVendorGroupsController@edit' ));
        Route::get('{contractGroupCategoryId}/vendor-list', array( 'as' => 'vendorGroups.external.ajax.vendor.list', 'uses' => 'ExternalVendorGroupsController@vendorList' ));
        Route::post('/', array( 'as' => 'vendorGroups.external.store', 'uses' => 'ExternalVendorGroupsController@store' ));
        Route::post('hide', array( 'as' => 'vendorGroups.external.updateSettings', 'uses' => 'ExternalVendorGroupsController@updateSettings' ));
        Route::post('export-excel', array( 'as' => 'vendorGroups.external.export.excel', 'uses' => 'ExternalVendorGroupsController@exportExcel' ));

        Route::group(['prefix' => '{contractGroupCategoryId}/vendor-categories'], function()
        {
            Route::get('/', array( 'as' => 'vendorCategories.index', 'uses' => 'VendorCategoriesController@index' ));
            Route::get('list', array( 'as' => 'vendorCategories.ajax.list', 'uses' => 'VendorCategoriesController@list' ));
            Route::get('create', array( 'as' => 'vendorCategories.create', 'uses' => 'VendorCategoriesController@create' ));
            Route::post('/', array( 'as' => 'vendorCategories.store', 'uses' => 'VendorCategoriesController@store' ));
            Route::post('hide', array( 'as' => 'vendorCategories.hide', 'uses' => 'VendorCategoriesController@hide' ));
            Route::get('{vendorCategoryId}/edit', array( 'as' => 'vendorCategories.edit', 'uses' => 'VendorCategoriesController@edit' ));
            Route::get('{vendorCategoryId}/vendor-list', array( 'as' => 'vendorCategories.ajax.vendor.list', 'uses' => 'VendorCategoriesController@vendorList' ));
            Route::post('{vendorCategoryId}', array( 'as' => 'vendorCategories.update', 'uses' => 'VendorCategoriesController@update' ));

            Route::group(['prefix' => '{vendorCategoryId}/vendor-work-categories'], function()
            {
                Route::get('/', array( 'as' => 'vendorCategories.vendorWorkCategories.index', 'uses' => 'VendorCategoryVendorWorkCategoryController@vendorWorkCategoryIndex' ));
                Route::get('list', array( 'as' => 'vendorCategories.vendorWorkCategories.ajax.list', 'uses' => 'VendorCategoryVendorWorkCategoryController@vendorWorkCategoryList' ));
                Route::post('include', array( 'as' => 'vendorCategories.vendorWorkCategories.include', 'uses' => 'VendorCategoryVendorWorkCategoryController@vendorWorkCategoryInclude' ));
                Route::post('store', array( 'as' => 'vendorCategories.vendorWorkCategories.store', 'uses' => 'VendorCategoryVendorWorkCategoryController@vendorWorkCategoryStore' ));
            });
        });
    });
});

Route::group(['prefix' => 'vendor-work-categories'], function()
{
    Route::get('/', array( 'as' => 'vendorWorkCategories.index', 'uses' => 'VendorWorkCategoriesController@index' ));
    Route::get('create', array( 'as' => 'vendorWorkCategories.create', 'uses' => 'VendorWorkCategoriesController@create' ));
    Route::get('{vendorWorkCategoryId}/edit', array( 'as' => 'vendorWorkCategories.edit', 'uses' => 'VendorWorkCategoriesController@edit' ));
    Route::post('/', array( 'as' => 'vendorWorkCategories.store', 'uses' => 'VendorWorkCategoriesController@store' ));
    Route::get('list', array( 'as' => 'vendorWorkCategories.ajax.list', 'uses' => 'VendorWorkCategoriesController@list' ));
    Route::post('hide', array( 'as' => 'vendorWorkCategories.hide', 'uses' => 'VendorWorkCategoriesController@hide' ));

    Route::group(['prefix' => '{vendorWorkCategoryId}/vendor-categories'], function()
    {
        Route::get('/', array( 'as' => 'vendorWorkCategories.vendorCategories.index', 'uses' => 'VendorCategoryVendorWorkCategoryController@vendorCategoryIndex' ));
        Route::get('list', array( 'as' => 'vendorWorkCategories.vendorCategories.ajax.list', 'uses' => 'VendorCategoryVendorWorkCategoryController@vendorCategoryList' ));
        Route::post('include', array( 'as' => 'vendorWorkCategories.vendorCategories.include', 'uses' => 'VendorCategoryVendorWorkCategoryController@vendorCategoryInclude' ));
    });

    Route::group(['prefix' => '{vendorWorkCategoryId}/vendor-work-subcategories'], function()
    {
        Route::get('/', array( 'as' => 'vendorWorkSubcategories.index', 'uses' => 'VendorWorkSubcategoriesController@index' ));
        Route::get('create', array( 'as' => 'vendorWorkSubcategories.create', 'uses' => 'VendorWorkSubcategoriesController@create' ));
        Route::get('list', array( 'as' => 'vendorWorkSubcategories.ajax.list', 'uses' => 'VendorWorkSubcategoriesController@list' ));
        Route::post('/', array( 'as' => 'vendorWorkSubcategories.store', 'uses' => 'VendorWorkSubcategoriesController@store' ));
        Route::post('hide', array( 'as' => 'vendorWorkSubcategories.hide', 'uses' => 'VendorWorkSubcategoriesController@hide' ));
        Route::get('{vendorWorkSubcategoryId}/edit', array( 'as' => 'vendorWorkSubcategories.edit', 'uses' => 'VendorWorkSubcategoriesController@edit' ));
        Route::get('{vendorWorkSubcategoryId}/reassign', array( 'as' => 'vendorWorkSubcategories.reassign', 'uses' => 'VendorWorkSubcategoriesController@reassign' ));
        Route::post('reassign', array( 'as' => 'vendorWorkSubcategories.reassign.store', 'uses' => 'VendorWorkSubcategoriesController@reassignStore' ));
    });
});

Route::group(array( 'prefix' => 'contracts', 'before' => 'superAdminAccessLevel' ), function()
{
    Route::get('/', array( 'as' => 'contracts', 'uses' => 'ContractsController@index' ));

    Route::group(array( 'prefix' => '{contractId}' ), function()
    {
        Route::group(array( 'prefix' => 'clauses' ), function()
        {
            Route::get('/', array( 'as' => 'clauses', 'uses' => 'ClausesController@index' ));

            Route::get('create', array( 'as' => 'clauses.create', 'uses' => 'ClausesController@create' ));
            Route::post('{clauseId}/store', array( 'as' => 'clauses.store', 'uses' => 'ClausesController@store' ));
            Route::get('{clauseId}/edit', array( 'as' => 'clauses.edit', 'uses' => 'ClausesController@edit' ));
            Route::put('{clauseId}/update', array( 'as' => 'clauses.update', 'uses' => 'ClausesController@update' ));

            Route::get('{clauseId}/items/{itemId}/up', array( 'as' => 'clauses.items.up', 'uses' => 'ClauseItemsController@moveToUp' ));
            Route::get('{clauseId}/items/{itemId}/down', array( 'as' => 'clauses.items.down', 'uses' => 'ClauseItemsController@moveToBottom' ));

            Route::get('{clauseId}/clauseItems', array( 'as' => 'clauses.items.index', 'uses' => 'ClauseItemsController@index' ));
            Route::get('{clauseId}/clauseItems/create', array( 'as' => 'clauses.items.create', 'uses' => 'ClauseItemsController@create' ));
            Route::post('{clauseId}/clauseItems/store', array( 'as' => 'clauses.items.store', 'uses' => 'ClauseItemsController@store' ));
            Route::get('{clauseId}/clauseItems/{itemId}/edit', array( 'as' => 'clauses.items.edit', 'uses' => 'ClauseItemsController@edit' ));
            Route::put('{clauseId}/clauseItems/{itemId}/update', array( 'as' => 'clauses.items.update', 'uses' => 'ClauseItemsController@update' ));
        });
    });
});

Route::group(array( 'before' => 'superAdminAccessLevel', 'prefix' => 'work-categories' ), function()
{
    Route::get('/', array( 'as' => 'workCategories.index', 'uses' => 'WorkCategoriesController@index' ));
    Route::post('store', array( 'as' => 'workCategories.store', 'uses' => 'WorkCategoriesController@store' ));
    Route::post('update', array( 'as' => 'workCategories.update', 'uses' => 'WorkCategoriesController@update' ));
    Route::get('list', array( 'as' => 'workCategories.list', 'uses' => 'WorkCategoriesController@list' ));
    Route::post('enabledStateToggle', array( 'as' => 'workCategories.enabledState.toggle', 'uses' => 'WorkCategoriesController@enabledStateToggle' ));
});

Route::group(array( 'before' => 'superAdminAccessLevel', 'prefix' => 'my_company_profiles' ), function()
{
    Route::get('/', array( 'as' => 'myCompanyProfiles.edit', 'uses' => 'MyCompanyProfilesController@edit' ));
    Route::put('/', array( 'uses' => 'MyCompanyProfilesController@update' ));
});

Route::group(array( 'before' => 'superAdminAccessLevel' ), function()
{
    Route::resource('procurement-methods', 'ProcurementMethodController');
});

Route::group(array( 'prefix' => 'app-migration', 'before' => 'superAdminAccessLevel' ), function()
{
    Route::get('sdp-masterlist', array( 'as' => 'app.migration.sdp.index', 'uses' => 'AppMigrationController@sdpIndex' ));
    Route::post('sdp-masterlist', array( 'as' => 'app.migration.sdp.masterlist.import', 'uses' => 'AppMigrationController@sdpImportMasterlist' ));

    Route::get('vendor/create', array( 'as' => 'app.migration.vendor.create', 'uses' => 'AppMigrationController@vendorCreate' ));
    Route::post('vendor/store', array( 'as' => 'app.migration.vendor.store', 'uses' => 'AppMigrationController@vendorStore' ));

});

Route::group(array( 'prefix' => 'app-integration', 'before' => 'superAdminAccessLevel' ), function()
{
    Route::get('s4hana', array( 'as' => 'app.integration.s4hana.index', 'uses' => 'AppIntegrationController@sapHanaIndex' ));
    Route::get('list', array( 'as' => 'app.integration.s4hana.ajax.list', 'uses' => 'AppIntegrationController@list' ));
    Route::get('s4hana/contract-download/{type}/{batchNumber}', array( 'as' => 'app.integration.s4hana.contract.download', 'uses' => 'AppIntegrationController@sapHanaContractDownload' ));
    Route::get('s4hana/claim-download/{type}/{batchNumber}', array( 'as' => 'app.integration.s4hana.claim.download', 'uses' => 'AppIntegrationController@sapHanaClaimDownload' ));
    Route::get('s4hana/sync/{batchNumber}', array( 'as' => 'app.integration.s4hana.sync', 'uses' => 'AppIntegrationController@sync' ));
});

Route::group(array( 'prefix' => 'log', 'before' => 'superAdminAccessLevel' ), function()
{
    Route::group(array( 'prefix' => 'authentication', 'before' => 'superAdminAccessLevel' ), function()
    {
        Route::get('/', array( 'as' => 'log.authentication.index', 'uses' => 'AuthenticationLogController@index' ));
        Route::get('list', array( 'as' => 'log.authentication.ajax.list', 'uses' => 'AuthenticationLogController@list' ));
        Route::get('export-excel', array( 'as' => 'log.authentication.export.excel', 'uses' => 'AuthenticationLogController@exportExcel' ));
    });

    Route::group(array( 'prefix' => 'access', 'before' => 'superAdminAccessLevel' ), function()
    {
        Route::get('/', array( 'as' => 'log.access.index', 'uses' => 'AccessLogController@index' ));
        Route::get('list', array( 'as' => 'log.access.ajax.list', 'uses' => 'AccessLogController@list' ));
    });

    Route::group(array( 'prefix' => 'project_report', 'before' => 'superAdminAccessLevel' ), function()
    {
        Route::group(array( 'prefix' => 'notification', 'before' => 'superAdminAccessLevel' ), function()
        {
            Route::get('/', array( 'as' => 'log.projectReport.notification.index', 'uses' => 'ProjectReport\ProjectReportNotificationLogController@index' ));
            Route::get('list', array( 'as' => 'log.projectReport.notification.list', 'uses' => 'ProjectReport\ProjectReportNotificationLogController@getList' ));
        });
    });
});
