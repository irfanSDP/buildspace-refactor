<?php

Route::group(['prefix' => 'email_notification_settings'], function() {
    Route::get('/', ['as' => 'email.notification.settings.index', 'uses' => 'EmailNotificationSettingsController@index']);
    Route::get('/getExternalUsersEmailNotificationSettings', ['as' => 'external.users.email.notification.settings.get', 'uses' => 'EmailNotificationSettingsController@getExternalUsersEmailNotificationSettings']);
    Route::get('/getInternalUsersEmailNotificationSettings', ['as' => 'internal.users.email.notification.settings.get', 'uses' => 'EmailNotificationSettingsController@getInternalUsersEmailNotificationSettings']);

    Route::group(['prefix' => '{settingId}'], function() {
        Route::post('/updateActivationStatus', ['as' => 'email.notification.setting.activation.status.update', 'uses' => 'EmailNotificationSettingsController@updateActivationStatus']);
        Route::get('/getModifiableContents', ['as' => 'email.notification.setting.modifiable.contents.get', 'uses' => 'EmailNotificationSettingsController@getModifiableContents']);
        Route::post('/updateModifiableContents', ['as' => 'email.notification.setting.modifiable.contents.update', 'uses' => 'EmailNotificationSettingsController@updateModifiableContents']);
        Route::get('/getEmailContentsPreview', ['as' => 'email.notification.setting.email.contents.preview', 'uses' => 'EmailNotificationSettingsController@getEmailContentsPreview']);
    });
});