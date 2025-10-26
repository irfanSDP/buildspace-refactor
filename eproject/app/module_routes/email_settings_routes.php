<?php

Route::group(['prefix' => 'email-settings'], function() {
    Route::get('edit', ['as' => 'email.setttings.edit', 'uses' => 'EmailSettingsController@edit']);
    Route::post('update', ['as' => 'email.settings.update', 'uses' => 'EmailSettingsController@update']);
    Route::delete('delete-footer-logo', ['as' => 'email.settings.delete.footer.logo', 'uses' => 'EmailSettingsController@footerLogoDelete']);

    Route::post('emailReminderSettingsUpdate', ['as' => 'email.reminder.settings.update', 'uses' => 'EmailSettingsController@emailReminderSettingsUpdate']);
});