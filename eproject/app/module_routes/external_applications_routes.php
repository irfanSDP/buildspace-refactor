<?php

Route::group(['prefix' => 'api-v2', 'before' => 'guest|appLicenseValid|passwordUpdated|temporaryLogin|superAdminAccessLevel'], function()
{
    Route::group(['prefix' => 'clients'], function(){
        Route::get('/', ['as' => 'api.v2.clients.index', 'uses' => 'ExternalApplications\ClientController@index']);
        Route::get('list', ['as' => 'api.v2.clients.list', 'uses' => 'ExternalApplications\ClientController@list']);
        Route::post('store', ['as' => 'api.v2.clients.store', 'uses' => 'ExternalApplications\ClientController@store']);
        Route::get('show/{extAppClientId}', ['as' => 'api.v2.clients.show', 'uses' => 'ExternalApplications\ClientController@show']);
        Route::get('show/{extAppClientId}/{module}', ['as' => 'api.v2.clients.module.show', 'uses' => 'ExternalApplications\ClientController@show']);
        Route::get('outbound-settings/{extAppClientId}', ['as' => 'api.v2.clients.outbound.show', 'uses' => 'ExternalApplications\ClientController@outboundShow']);
        Route::get('outbound-settings/{extAppClientId}/{type}', ['as' => 'api.v2.clients.outbound.type.show', 'uses' => 'ExternalApplications\ClientController@outboundShow']);
        Route::post('outbound-settings-store/{extAppClientId}', ['as' => 'api.v2.clients.outbound.auth.store', 'uses' => 'ExternalApplications\ClientController@outboundAuthStore']);
        Route::delete('delete/{extAppClientId}', ['as' => 'api.v2.clients.delete', 'uses' => 'ExternalApplications\ClientController@delete']);
        Route::post('module-settings-store/{extAppClientModuleId}', ['as' => 'api.v2.clients.module.settings.store', 'uses' => 'ExternalApplications\ClientController@moduleSettingsStore']);
        Route::get('module-records/{extAppClientModuleId}', ['as' => 'api.v2.clients.module.records', 'uses' => 'ExternalApplications\ClientController@moduleRecords']);
        Route::post('outbound-store/{extAppClientModuleId}', ['as' => 'api.v2.clients.module.outbound.store', 'uses' => 'ExternalApplications\ClientController@outboundModuleStore']);
        Route::get('outbound-logs/{extAppClientModuleId}', ['as' => 'api.v2.clients.module.outbound.logs', 'uses' => 'ExternalApplications\ClientController@outboundModuleLogs']);
        Route::post('resync-outbound-logs/{extAppClientModuleId}', ['as' => 'api.v2.clients.module.outbound.logs.resync', 'uses' => 'ExternalApplications\ClientController@resyncOutboundModuleLogs']);

    });
});

Route::group(['prefix' => 'api/v2', 'before' => 'auth.api.token'], function()
{
    Route::get('{module}', ['as' => 'api.v2.list', 'uses' => 'ExternalApplications\ApiController@list']);
    Route::post('{module}', ['as' => 'api.v2.create', 'uses' => 'ExternalApplications\ApiController@create']);
    Route::get('{module}/{id}', ['as' => 'api.v2.retrieve', 'uses' => 'ExternalApplications\ApiController@retrieve']);
    Route::put('{module}/{id}', ['as' => 'api.v2.update', 'uses' => 'ExternalApplications\ApiController@update']);
    Route::delete('{module}/{id}', ['as' => 'api.v2.delete', 'uses' => 'ExternalApplications\ApiController@delete']);
});