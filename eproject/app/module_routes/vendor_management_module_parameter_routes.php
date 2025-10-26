<?php 

Route::group(['before' => 'systemModule.vendorManagement.enabled'], function(){
    Route::group(['prefix' => 'vendor_management_grade', 'before' => 'vendorManagement.hasPermission:'.\PCK\VendorManagement\VendorManagementUserPermission::TYPE_GRADE_MAINTENANCE], function() {
        Route::get('/', ['as' => 'vendor.management.grade.index', 'uses' => 'VendorManagementGradesController@index']);
        Route::get('/getAllGrades', ['as' => 'vendor.management.grades.get', 'uses' => 'VendorManagementGradesController@getAllGrades']);
        Route::post('/store', ['as' => 'vendor.management.grade.store', 'uses' => 'VendorManagementGradesController@store']);

        Route::group(['prefix' => 'grade/{gradeId}'], function() {
            Route::post('/update', ['as' => 'vendor.management.grade.name.update', 'uses' => 'VendorManagementGradesController@update']);
            Route::delete('/delete', ['as' => 'vendor.management.grade.name.delete', 'uses' => 'VendorManagementGradesController@delete']);
            Route::get('/show', ['as' => 'vendor.management.grade.levels.show', 'uses' => 'VendorManagementGradeLevelsController@show']);

            Route::group(['prefix' => 'level'], function() {
                Route::get('/getLevels', ['as' => 'vendor.management.grade.levels.get', 'uses' => 'VendorManagementGradeLevelsController@getLevels']);
                Route::post('/store', ['as' => 'vendor.management.grade.level.store', 'uses' => 'VendorManagementGradeLevelsController@store']);
            });
        });

        Route::group(['prefix' => 'level/{levelId}'], function() {
            Route::post('/update', ['as' => 'vendor.management.grade.level.update', 'uses' => 'VendorManagementGradeLevelsController@update']);
            Route::delete('/delete', ['as' => 'vendor.management.grade.level.delete', 'uses' => 'VendorManagementGradeLevelsController@delete']);
        });
    });

    Route::group(['before' => 'vendorManagement.hasPermission:'.\PCK\VendorManagement\VendorManagementUserPermission::TYPE_SETTINGS_AND_MAINTENANCE], function(){
        Route::group(['prefix' => 'vendor_profile_module_parameter'], function() {
            Route::get('/', ['as' => 'vendor.profile.module.parameter.edit', 'uses' => 'VendorProfileModuleParametersController@edit']);
            Route::post('/update', ['as' => 'vendor.profile.module.parameter.update', 'uses' => 'VendorProfileModuleParametersController@update']);
        });

        Route::group(['prefix' => 'vendor_performance_evaluation_module_parameter'], function() {
            Route::get('/', ['as' => 'vendor.performance.evaluation.module.parameter.edit', 'uses' => 'VendorPerformanceEvaluationModuleParameterController@edit']);
            Route::post('/update', ['as' => 'vendor.performance.evaluation.module.parameter.update', 'uses' => 'VendorPerformanceEvaluationModuleParameterController@update']);
        });

        Route::group(['prefix' => 'vendor_registration_and_prequalification_module_parameter'], function() {
            Route::get('/', ['as' => 'vendor.registration.and.prequalification.module.parameter.edit', 'uses' => 'VendorRegistrationAndPrequalificationModuleParametersController@edit']);
            Route::post('/update', ['as' => 'vendor.registration.and.prequalification.module.parameter.update', 'uses' => 'VendorRegistrationAndPrequalificationModuleParametersController@update']);
        });

        Route::group(['prefix' => 'vendor-details/settings'], function() {
            Route::get('/', ['as' => 'vendorRegistration.vendorDetails.settings.edit', 'uses' => 'VendorDetailSettingsController@edit']);
            Route::post('/instructionSettingsUpdate', ['as' => 'vendorRegistration.vendorDetails.instructions.settings.update', 'uses' => 'VendorDetailSettingsController@update']);
            Route::post('/attachmentSettingsUpdate', ['as' => 'vendorRegistration.vendorDetails.attachment.settings.update', 'uses' => 'VendorDetailSettingsController@attachmentSettingsUpdate']);
            Route::post('section-instructions', ['as' => 'vendorRegistration.settings.sectionInstructions.update', 'uses' => 'VendorDetailSettingsController@sectionInstructionsUpdate']);
        });

        Route::group(['prefix' => 'company-personnel/settings'], function() {
            Route::get('/', ['as' => 'company.personnel.settings.edit', 'uses' => 'CompanyPersonnelSettingsController@edit']);
            Route::post('/update', ['as' => 'company.personnel.settings.update', 'uses' => 'CompanyPersonnelSettingsController@update']);
        });

        Route::group(['prefix' => 'project-track-record/settings'], function() {
            Route::get('/', ['as' => 'project.track.record.settings.edit', 'uses' => 'ProjectTrackRecordSettingsController@edit']);
            Route::post('/update', ['as' => 'project.track.record.settings.update', 'uses' => 'ProjectTrackRecordSettingsController@update']);
        });

        Route::group(['prefix' => 'supplier-credit-facility/settings'], function() {
            Route::get('/', ['as' => 'supplier.credit.facility.settings.edit', 'uses' => 'SupplierCreditFacilitySettingsController@edit']);
            Route::post('/update', ['as' => 'supplier.credit.facility.settings.update', 'uses' => 'SupplierCreditFacilitySettingsController@update']);
        });

        Route::group(['prefix' => 'login-request-form/settings'], function() {
            Route::get('/', ['as' => 'loginRequestForm.settings.edit', 'uses' => 'LoginRequestFormSettingsController@edit']);
            Route::post('update', ['as' => 'loginRequestForm.settings.update', 'uses' => 'LoginRequestFormSettingsController@update']);
        });
    });
});