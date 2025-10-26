<?php

Route::group(['prefix' => 'vm-vendor-migration', 'before' => 'superAdminAccessLevel|vendorManagementMigrationModeAccess'], function() {
  Route::get('/', ['as' => 'vm.vendor.migration.index', 'uses' => 'VendorManagement\VendorMigrationController@index']);
  Route::get('list', ['as' => 'vm.vendor.migration.list', 'uses' => 'VendorManagement\VendorMigrationController@list']);
  Route::post('submit', ['as' => 'vm.vendor.migrate.submit', 'uses' => 'VendorManagement\VendorMigrationController@migrateSubmit']);
});