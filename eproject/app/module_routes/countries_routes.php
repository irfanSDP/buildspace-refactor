<?php

Route::group(array( 'prefix' => 'countries' ), function()
{
    // only allow super admin to view company's listing and adding new company
    Route::group(array( 'before' => 'superAdminAccessLevel' ), function()
    {
        Route::get('/', array( 'as' => 'countries', 'uses' => 'CountriesController@index' ));

        Route::get('create', array( 'as' => 'countries.create', 'uses' => 'CountriesController@create' ));
        Route::post('create', array( 'uses' => 'CountriesController@store' ));

        Route::get('{countryId}/edit', array( 'as' => 'countries.edit', 'uses' => 'CountriesController@edit' ));
        Route::put('{countryId}/edit', array( 'as' => 'countries.update', 'uses' => 'CountriesController@update' ));
    });

    Route::group(array( 'prefix' => '{countryId}/states', 'before' => 'superAdminCompanyAdminAccessLevel' ), function()
    {
        Route::get('/', array( 'as' => 'states', 'uses' => 'StatesController@index' ));

        Route::get('create', array( 'as' => 'states.create', 'uses' => 'StatesController@create' ));
        Route::post('create', array( 'uses' => 'StatesController@store' ));

        Route::get('{stateId}/edit', array( 'as' => 'states.edit', 'uses' => 'StatesController@edit' ));
        Route::put('{stateId}/edit', array( 'as' => 'states.update', 'uses' => 'StatesController@update' ));
    });
});