<?php

Route::group(['prefix' => 'open_tender_news'], function() {
    Route::get('/', array( 'as' => 'open_tender_news.index', 'uses' => 'OpenTenderNewsController@index' ));
    Route::get('create', array( 'as' => 'open_tender_news.create', 'uses' => 'OpenTenderNewsController@create' ));
    Route::post('store', array( 'as' => 'open_tender_news.store', 'uses' => 'OpenTenderNewsController@store' ));
    Route::get('edit/{id}', array( 'as' => 'open_tender_news.edit', 'uses' => 'OpenTenderNewsController@edit' ));
    Route::put('update/{id}', array( 'as' => 'open_tender_news.update', 'uses' => 'OpenTenderNewsController@update' ));
    Route::delete('delete/{id}', array( 'as' => 'open_tender_news.delete', 'uses' => 'OpenTenderNewsController@destroy' ));
});