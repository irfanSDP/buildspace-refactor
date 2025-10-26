<?php

Route::group(['prefix' => 'open_tender_banners'], function() {
    Route::get('/', array( 'as' => 'open_tender_banners.index', 'uses' => 'OpenTenderBannersController@index' ));
    Route::get('create', array( 'as' => 'open_tender_banners.create', 'uses' => 'OpenTenderBannersController@create' ));
    Route::post('store', array( 'as' => 'open_tender_banners.store', 'uses' => 'OpenTenderBannersController@store' ));
    Route::get('edit/{id}', array( 'as' => 'open_tender_banners.edit', 'uses' => 'OpenTenderBannersController@edit' ));
    Route::put('update/{id}', array( 'as' => 'open_tender_banners.update', 'uses' => 'OpenTenderBannersController@update' ));
    Route::delete('delete/{id}', array( 'as' => 'open_tender_banners.delete', 'uses' => 'OpenTenderBannersController@destroy' ));
});