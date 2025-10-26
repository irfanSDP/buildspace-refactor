<?php

Route::group(['before' => 'superAdminAccessLevel'], function () {
    Route::group(['prefix' => 'all_users'], function () {
        Route::get('/', ['as' => 'users.all.index', 'uses' => 'UsersController@allUsersIndex']);
        Route::get('/getAllUsers', ['as' => 'users.all.get', 'uses' => 'UsersController@getAllUsers']);
        Route::get('/allUsers/export', ['as' => 'users.all.export', 'uses' => 'UsersController@exportAllUsers']);
        Route::group(['prefix' => 'user/{userId}'], function () {
            Route::get('/edit', ['as' => 'user.edit', 'uses' => 'UsersController@edit']);
            Route::put('/edit', ['uses' => 'UsersController@update']);
            Route::delete('/delete', ['as' => 'user.delete', 'uses' => 'UsersController@delete']);
            Route::post('/resendValidationEmail', ['as' => 'user.validation.email.resend', 'uses' => 'UsersController@resendValidationEmail']);
            Route::get('blockUserPendingTasksCheck', ['as' => 'user.block.pending.tasks.check', 'uses' => 'UsersController@checkUserIsTransferable']);
            Route::get('getListOfFosterCompanies', ['as' => 'user.foster.companies.get', 'uses' => 'UsersController@getListOfFosterCompanies']);
        });
    });
});
