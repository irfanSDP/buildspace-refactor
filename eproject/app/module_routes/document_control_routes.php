<?php

Route::group(array( 'before' => 'hasDocumentControlAccess' ), function()
{
    Route::group(array( 'prefix' => 'request-for-information' ), function()
    {
        Route::get('/', array( 'as' => 'requestForInformation.index', 'uses' => 'RequestForInformationController@index' ));

        Route::group(array( 'before' => 'canCreateRfiMessage' ), function()
        {
            Route::get('create', array( 'as' => 'requestForInformation.create', 'uses' => 'RequestForInformationController@create' ));

            Route::post('store', array( 'as' => 'requestForInformation.store', 'uses' => 'RequestForInformationController@store' ));
        });

        Route::get('{requestForInformationId}/show', array( 'as' => 'requestForInformation.show', 'uses' => 'RequestForInformationController@show' ));

        Route::group(array( 'before' => 'canPushRfiMessage' ), function()
        {
            Route::post('{requestForInformationId}/pushMessage', array( 'as' => 'requestForInformation.message.create', 'uses' => 'RequestForInformationController@pushMessage' ));
        });
    });

    Route::group(array( 'prefix' => 'risk-register' ), function()
    {
        Route::get('/', array( 'as' => 'riskRegister.index', 'uses' => 'RiskRegisterController@index' ));

        Route::group(array( 'before' => 'canPostRisk' ), function()
        {
            Route::get('create', array( 'as' => 'riskRegister.create', 'uses' => 'RiskRegisterController@create' ));

            Route::post('store', array( 'as' => 'riskRegister.store', 'uses' => 'RiskRegisterController@store' ));
        });

        Route::get('{riskRegisterId}/show', array( 'as' => 'riskRegister.show', 'uses' => 'RiskRegisterController@show' ));

        Route::post('{riskRegisterMessageId}/reviseRejectedRisk', array( 'before' => 'canReviseRejectedRiskMessage', 'as' => 'riskRegister.risk.rejected.update', 'uses' => 'RiskRegisterController@reviseRejectedRisk' ));

        Route::post('{riskRegisterId}/updateRisk', array( 'before' => 'canUpdatePublishedRisk', 'as' => 'riskRegister.risk.update', 'uses' => 'RiskRegisterController@updatePublishedRisk' ));

        Route::post('{riskRegisterId}/addComment', array( 'before' => 'canComment', 'as' => 'riskRegister.comment.create', 'uses' => 'RiskRegisterController@addComment' ));

        Route::post('{riskRegisterMessageId}/updateComment', array( 'before' => 'canUpdateCommentMessage', 'as' => 'riskRegister.comment.update', 'uses' => 'RiskRegisterController@updateComment' ));
    });
});