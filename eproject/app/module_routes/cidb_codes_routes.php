<?php

Route::group(['prefix' => 'cidb-codes'], function() {
    Route::get('/', array( 'as' => 'cidb_codes.index', 'uses' => 'CIDBCodeController@index' ));
    Route::get('create', array( 'as' => 'cidb_codes.create', 'uses' => 'CIDBCodeController@create' ));
    Route::get('/list', array('as' => 'cidb_codes.list', 'uses' => 'CIDBCodeController@list'));
    Route::post('create', array( 'as' => 'cidb_codes.store', 'uses' => 'CIDBCodeController@store' ));
    Route::get('edit/{id}', array( 'as' => 'cidb_codes.edit', 'uses' => 'CIDBCodeController@edit' ));
    Route::get('show/{id}', array( 'as' => 'cidb_codes.show', 'uses' => 'CIDBCodeController@show' ));
    Route::put('update/{id}', array( 'as' => 'cidb_codes.update', 'uses' => 'CIDBCodeController@update' ));
    Route::delete('delete/{id}', array( 'as' => 'cidb_codes.delete', 'uses' => 'CIDBCodeController@destroy' ));

    Route::group(['prefix' => 'cidb-codes-children'], function() {
        Route::get('/{parentId}', array( 'as' => 'cidb_codes_children.index', 'uses' => 'CIDBCodeController@childrenIndex' ));
        Route::get('{parentId}/create', array( 'as' => 'cidb_codes_children.create', 'uses' => 'CIDBCodeController@childrenCreate' ));
        Route::post('{parentId}/create', array( 'as' => 'cidb_codes_children.store', 'uses' => 'CIDBCodeController@childrenStore' ));
        Route::get('{parentId}/edit/{id}', array( 'as' => 'cidb_codes_children.edit', 'uses' => 'CIDBCodeController@childrenEdit' ));
        Route::get('{parentId}/show/{id}', array( 'as' => 'cidb_codes_children.show', 'uses' => 'CIDBCodeController@childrenShow' ));
        Route::put('{parentId}/update/{id}', array( 'as' => 'cidb_codes_children.update', 'uses' => 'CIDBCodeController@childrenUpdate' ));
        Route::delete('{parentId}/delete/{id}', array( 'as' => 'cidb_codes_children.delete', 'uses' => 'CIDBCodeController@childrenDestroy' ));
    });
});