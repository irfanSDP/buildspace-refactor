<?php

Route::group(['prefix' => 'cidb-grades'], function() {
    Route::get('/', array( 'as' => 'cidb_grades.index', 'uses' => 'CIDBGradeController@index' ));
    Route::get('create', array( 'as' => 'cidb_grades.create', 'uses' => 'CIDBGradeController@create' ));
    Route::get('/list', array('as' => 'cidb_grades.list', 'uses' => 'CIDBGradeController@list'));
    Route::post('create', array( 'as' => 'cidb_grades.store', 'uses' => 'CIDBGradeController@store' ));
    Route::get('edit/{id}', array( 'as' => 'cidb_grades.edit', 'uses' => 'CIDBGradeController@edit' ));
    Route::put('update/{id}', array( 'as' => 'cidb_grades.update', 'uses' => 'CIDBGradeController@update' ));
    Route::delete('delete/{id}', array( 'as' => 'cidb_grades.delete', 'uses' => 'CIDBGradeController@destroy' ));
});