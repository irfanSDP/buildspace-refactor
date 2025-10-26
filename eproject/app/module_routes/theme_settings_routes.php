<?php

Route::group(['prefix' => 'theme-settings'], function() {
    Route::get('/', ['as' => 'theme.settings.index', 'uses' => 'ThemeSettingsController@index']);
    Route::get('/edit', ['as' => 'theme.settings.edit', 'uses' => 'ThemeSettingsController@edit']);
    Route::post('/update', ['as' => 'theme.settings.update', 'uses' => 'ThemeSettingsController@update']);
    Route::post('/reset-images', ['as' => 'theme.settings.reset-images', 'uses' => 'ThemeSettingsController@resetImages']);
});