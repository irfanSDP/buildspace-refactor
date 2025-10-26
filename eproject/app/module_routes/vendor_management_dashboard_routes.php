<?php
Route::group(array( 'prefix' => 'vendor-management-dashboard', 'before' => 'systemModule.vendorManagement.enabled|vendorManagement.hasPermission:'.\PCK\VendorManagement\VendorManagementUserPermission::TYPE_DASHBOARD ), function()
{
    Route::get('/vpestatistics', ['as' => 'vendor.management.dashboard.index', 'uses' => 'VendorManagementDashboardController@vpeStatisticsIndex']);
    Route::get('/vendorStatistics', ['as' => 'vendor.management.dashboard.vendorStatistics', 'uses' => 'VendorManagementDashboardController@vendorStatisticsIndex']);

    Route::get('evaluated-projects-total', ['as' => 'vendorManagement.dashboard.evaluatedProjectsTotal', 'uses' => 'VendorManagementDashboardController@evaluatedProjectsTotal']);
    Route::get('overall-vendor-performance-statistics', ['as' => 'vendorManagement.dashboard.overallVendorPerformanceStatisticsTable', 'uses' => 'VendorManagementDashboardController@overallVendorPerformanceStatisticsTable']);
    Route::get('overall-vendor-performance-statistics-export', ['as' => 'vendorManagement.dashboard.overallVendorPerformanceStatistics.excel.export', 'uses' => 'VendorManagementDashboardController@overallVendorPerformanceStatisticsExcelExport']);
    Route::get('total-evaluations-by-rating/vendor-group', ['as' => 'vendorManagement.dashboard.totalEvaluationsByRating.vendorGroup', 'uses' => 'VendorManagementDashboardController@vendorGroupTotalEvaluationsByRating']);
    Route::get('total-evaluations-by-rating/vendor-category', ['as' => 'vendorManagement.dashboard.totalEvaluationsByRating.vendorCategory', 'uses' => 'VendorManagementDashboardController@vendorCategoryTotalEvaluationsByRating']);

    Route::get('top-evaluation-scorers', ['as' => 'vendorManagement.dashboard.topEvaluationScorers', 'uses' => 'VendorManagementDashboardController@topEvaluationScorers']);

    Route::get('total-evaluated/vendor-group', ['as' => 'vendorManagement.dashboard.totalEvaluated.vendorGroup', 'uses' => 'VendorManagementDashboardController@totalEvaluatedByVendorGroup']);
    Route::get('total-evaluated/vendor-category', ['as' => 'vendorManagement.dashboard.totalEvaluated.vendorCategory', 'uses' => 'VendorManagementDashboardController@totalEvaluatedByVendorCategory']);
    Route::get('average-scores', ['as' => 'vendorManagement.dashboard.averageScores', 'uses' => 'VendorManagementDashboardController@averageScores']);

    Route::get('export/vendor-performance-evaluation/forms', ['as' => 'vendorManagement.dashboard.export.vendorPerformanceEvaluation.forms', 'uses' => 'VendorManagementDashboardController@exportVendorPerformanceEvaluationForms']);
    Route::get('export/vendor-performance-evaluation/vendor-work-category-scores', ['as' => 'vendorManagement.dashboard.export.vendorPerformanceEvaluation.vendorWorkCategoryScores', 'uses' => 'VendorManagementDashboardController@exportVendorPerformanceEvaluationVendorWorkCategoryScores']);
    Route::get('export/vendor-performance-evaluation/vendor-category-scores', ['as' => 'vendorManagement.dashboard.export.vendorPerformanceEvaluation.vendorCategoryScores', 'uses' => 'VendorManagementDashboardController@exportVendorPerformanceEvaluationVendorCategoryScores']);

    Route::get('progress/vendor-performance-evaluation/forms', ['as' => 'vendorManagement.dashboard.progress.vendorPerformanceEvaluation.forms', 'uses' => 'VendorManagementDashboardController@getVendorPerformanceEvaluationFormsProgress']);

    Route::get('/getStatesByCountry', ['as' => 'country.states.get', 'uses' => 'CountriesController@getStatesByCountry']);
    Route::get('/getVendorCategoriesByVendorGroup', ['as' => 'vendor.categories.get', 'uses' => 'ContractGroupCategoriesController@getVendorCategoriesByVendorGroup']);

    Route::get('getVendorStatistics', ['as' => 'vendorManagement.dashboard.vendorStatistics', 'uses' => 'VendorManagementDashboardController@getVendorStatistics']);

    Route::group(['prefix' => 'registration-statistics'], function(){
        Route::get('newly-registered-vendors-by-date', ['as' => 'vendorManagement.dashboard.registrationStatistics.newlyRegisteredVendorsByDate', 'uses' => 'VendorManagementDashboardController@registrationStatisticsNewlyRegisteredVendorsByDate']);
        Route::get('vendor-list', ['as' => 'vendorManagement.dashboard.registrationStatistics.vendorList', 'uses' => 'VendorManagementDashboardController@registrationStatisticsNewlyRegisteredVendorsList']);
    });
});