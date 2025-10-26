<?php

Route::group(array( 'prefix' => 'indonesia-civil-contract', 'before' => 'hasContractualClaimAccess|contractType:' . \PCK\Contracts\Contract::TYPE_INDONESIA_CIVIL_CONTRACT ), function()
{
    Route::group(array( 'prefix' => 'user-permissions', 'before' => 'indonesiaCivilContract.userPermissionManager' ), function()
    {
        Route::get('/', array( 'as' => 'indonesiaCivilContract.permissions.index', 'uses' => 'IndonesiaCivilContractUserPermissionsController@index' ));
        Route::get('assigned/get', array( 'as' => 'indonesiaCivilContract.permissions.assigned', 'uses' => 'IndonesiaCivilContractUserPermissionsController@getAssignedUsers' ));
        Route::get('assignable/get', array( 'as' => 'indonesiaCivilContract.permissions.assignable', 'uses' => 'IndonesiaCivilContractUserPermissionsController@getAssignableUsers' ));
        Route::post('assign', array( 'as' => 'indonesiaCivilContract.permissions.assign', 'uses' => 'IndonesiaCivilContractUserPermissionsController@assign' ));
        Route::delete('user/{userId}/module/{moduleId}', array( 'as' => 'indonesiaCivilContract.permissions.revoke', 'uses' => 'IndonesiaCivilContractUserPermissionsController@revoke' ));
    });

    Route::group(array( 'prefix' => 'architect-instructions', 'before' => 'resourceExists:PCK\IndonesiaCivilContract\ArchitectInstruction\ArchitectInstruction,aiId' ), function()
    {
        Route::get('/', array( 'as' => 'indonesiaCivilContract.architectInstructions', 'uses' => 'IndonesiaCivilContractArchitectInstructionsController@index' ));

        Route::group(array( 'before' => 'indonesiaCivilContract.architectInstructions.isEditor' ), function()
        {
            Route::get('create', array( 'as' => 'indonesiaCivilContract.architectInstructions.create', 'uses' => 'IndonesiaCivilContractArchitectInstructionsController@create' ));
            Route::post('create', array( 'uses' => 'IndonesiaCivilContractArchitectInstructionsController@store' ));

            Route::group(array( 'before' => 'indonesiaCivilContract.architectInstructions.isVisible' ), function()
            {
                Route::delete('delete/{aiId}', array( 'as' => 'indonesiaCivilContract.architectInstructions.delete', 'uses' => 'IndonesiaCivilContractArchitectInstructionsController@destroy' ));

                Route::put('show/{aiId}', array( 'as' => 'indonesiaCivilContract.architectInstructions.update', 'uses' => 'IndonesiaCivilContractArchitectInstructionsController@update' ));
            });
        });

        Route::group(array( 'before' => 'indonesiaCivilContract.architectInstructions.isVisible' ), function()
        {
            Route::get('show/{aiId}', array( 'as' => 'indonesiaCivilContract.architectInstructions.show', 'uses' => 'IndonesiaCivilContractArchitectInstructionsController@show' ));

            Route::group(array( 'before' => 'indonesiaCivilContract.architectInstructions.canRespond' ), function()
            {
                Route::post('submitResponse/{aiId}', array( 'as' => 'indonesiaCivilContract.architectInstructions.response.submit', 'uses' => 'IndonesiaCivilContractArchitectInstructionsController@submitResponse' ));
            });
        });
    });

    Route::group(array( 'prefix' => 'early-warning', 'before' => 'resourceExists:PCK\IndonesiaCivilContract\EarlyWarning\EarlyWarning,ewId' ), function()
    {
        Route::get('/', array( 'as' => 'indonesiaCivilContract.earlyWarning', 'uses' => 'IndonesiaCivilContractEarlyWarningsController@index' ));

        Route::group(array( 'before' => 'indonesiaCivilContract.earlyWarning.isEditor' ), function()
        {
            Route::get('create', array( 'as' => 'indonesiaCivilContract.earlyWarning.create', 'uses' => 'IndonesiaCivilContractEarlyWarningsController@create' ));
            Route::post('create', array( 'uses' => 'IndonesiaCivilContractEarlyWarningsController@store' ));
        });

        Route::get('show/{ewId}', array( 'as' => 'indonesiaCivilContract.earlyWarning.show', 'uses' => 'IndonesiaCivilContractEarlyWarningsController@show' ));
    });

    Route::group(array( 'prefix' => 'loss-and-expenses', 'before' => 'resourceExists:PCK\IndonesiaCivilContract\LossAndExpense\LossAndExpense,leId' ), function()
    {
        Route::get('/', array( 'as' => 'indonesiaCivilContract.lossOrAndExpenses', 'uses' => 'IndonesiaCivilContractLossAndExpensesController@index' ));

        Route::group(array( 'before' => 'indonesiaCivilContract.lossAndExpenses.isEditor' ), function()
        {
            Route::get('create', array( 'as' => 'indonesiaCivilContract.lossAndExpenses.create', 'uses' => 'IndonesiaCivilContractLossAndExpensesController@create' ));
            Route::post('create', array( 'uses' => 'IndonesiaCivilContractLossAndExpensesController@store' ));

            Route::delete('delete/{leId}', array( 'as' => 'indonesiaCivilContract.lossAndExpenses.delete', 'uses' => 'IndonesiaCivilContractLossAndExpensesController@destroy' ));

            Route::put('show/{leId}', array( 'as' => 'indonesiaCivilContract.lossAndExpenses.update', 'uses' => 'IndonesiaCivilContractLossAndExpensesController@update' ));
        });

        Route::get('show/{leId}', array( 'as' => 'indonesiaCivilContract.lossAndExpenses.show', 'uses' => 'IndonesiaCivilContractLossAndExpensesController@show' ));

        Route::group(array( 'before' => 'indonesiaCivilContract.lossAndExpenses.canRespond' ), function()
        {
            Route::post('submitResponse/{leId}/decision', array( 'as' => 'indonesiaCivilContract.lossAndExpenses.response.decision.submit', 'uses' => 'IndonesiaCivilContractLossAndExpensesController@submitDecisionResponse' ));
            Route::post('submitResponse/{leId}/plain', array( 'as' => 'indonesiaCivilContract.lossAndExpenses.response.plain.submit', 'uses' => 'IndonesiaCivilContractLossAndExpensesController@submitPlainResponse' ));
        });
    });

    Route::group(array( 'prefix' => 'extension-of-times', 'before' => 'resourceExists:PCK\IndonesiaCivilContract\ExtensionOfTime\ExtensionOfTime,eotId' ), function()
    {
        Route::get('/', array( 'as' => 'indonesiaCivilContract.extensionOfTime', 'uses' => 'IndonesiaCivilContractExtensionOfTimeController@index' ));

        Route::group(array( 'before' => 'indonesiaCivilContract.extensionOfTime.isEditor' ), function()
        {
            Route::get('create', array( 'as' => 'indonesiaCivilContract.extensionOfTime.create', 'uses' => 'IndonesiaCivilContractExtensionOfTimeController@create' ));
            Route::post('create', array( 'uses' => 'IndonesiaCivilContractExtensionOfTimeController@store' ));

            Route::delete('delete/{eotId}', array( 'as' => 'indonesiaCivilContract.extensionOfTime.delete', 'uses' => 'IndonesiaCivilContractExtensionOfTimeController@destroy' ));

            Route::put('show/{eotId}', array( 'as' => 'indonesiaCivilContract.extensionOfTime.update', 'uses' => 'IndonesiaCivilContractExtensionOfTimeController@update' ));
        });

        Route::get('show/{eotId}', array( 'as' => 'indonesiaCivilContract.extensionOfTime.show', 'uses' => 'IndonesiaCivilContractExtensionOfTimeController@show' ));

        Route::group(array( 'before' => 'indonesiaCivilContract.extensionOfTime.canRespond' ), function()
        {
            Route::post('submitResponse/{eotId}/decision', array( 'as' => 'indonesiaCivilContract.extensionOfTime.response.decision.submit', 'uses' => 'IndonesiaCivilContractExtensionOfTimeController@submitDecisionResponse' ));
            Route::post('submitResponse/{eotId}/plain', array( 'as' => 'indonesiaCivilContract.extensionOfTime.response.plain.submit', 'uses' => 'IndonesiaCivilContractExtensionOfTimeController@submitPlainResponse' ));
        });
    });
});