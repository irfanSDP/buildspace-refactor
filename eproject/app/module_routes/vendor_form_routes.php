<?php

Route::group(['prefix' => 'vendor_registration_form'], function() {
    Route::group(['prefix' => 'form/{formId}'], function() {
        Route::get('/getFormContents', ['as' => 'vendor.form.contents.get', 'uses' => 'DynamicFormsController@getVendorFormContents']);
    });

    Route::group(['prefix' => 'section'], function() {
        Route::group(['prefix' => '{sectionId}'], function() {
            Route::post('/submitFormSection', ['as' => 'vendor.form.section.submit', 'uses' => 'DynamicFormsController@submitFormSection']);
        });
    });

    Route::group(['prefix' => 'element/{elementId}/elementType/{elementType}'], function() {
        Route::get('/getElementRejection', ['as' => 'element.rejection.get', 'uses' => 'ElementsController@getElementRejection']);
    
        Route::group(['prefix' => 'rejection', 'before' => 'element.canBeRejected'], function() {
            Route::post('/saveRejection', ['as' => 'element.rejection.save', 'uses' => 'ElementsController@saveRejection']);
            Route::post('/deleteRejection', ['as' => 'element.rejection.delete', 'uses' => 'ElementsController@deleteRejection']);
        });
    
        Route::group(['prefix' => 'attachment'], function() {
            Route::get('/getAttachmentCount', ['as' => 'form.column.section.element.attachments.count.get', 'uses' => 'ElementsController@getAttachmentCount']);
            Route::get('/getAttachmentsList', ['as' => 'form.column.section.element.attachments.list.get', 'uses' => 'ElementsController@getAttachmentsList']);
            Route::post('/update', ['as' => 'form.column.section.element.attachments.update', 'uses' => 'ElementsController@attachmentsUpdate']);
        });
    });
});