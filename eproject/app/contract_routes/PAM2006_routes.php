<?php

use PCK\ContractGroups\Types\Role;

Route::group(array( 'prefix' => 'pam2006', 'before' => 'hasContractualClaimAccess|contractType:' . \PCK\Contracts\Contract::TYPE_PAM2006 ), function()
{
    Route::group(array( 'prefix' => 'architect_instructions' ), function()
    {
        Route::get('/', array( 'as' => 'ai', 'uses' => 'ArchitectInstructionsController@index' ));

        Route::group(array( 'before' => 'projectRoles:' . Role::INSTRUCTION_ISSUER ), function()
        {
            Route::get('create', array( 'as' => 'ai.create', 'uses' => 'ArchitectInstructionsController@create' ));
            Route::post('create', array( 'uses' => 'ArchitectInstructionsController@store' ));

            Route::delete('delete/{aiId}', array( 'as' => 'ai.delete', 'uses' => 'ArchitectInstructionsController@destroy' ));

            Route::put('show/{aiId}', array( 'as' => 'ai.show', 'uses' => 'ArchitectInstructionsController@update' ));
        });

        Route::get('show/{aiId}', array( 'as' => 'ai.show', 'uses' => 'ArchitectInstructionsController@show' ));

        // group route to correctly describe the API for messaging between Architect and Contractor
        Route::group(array( 'prefix' => '{aiId}/first_level_messages' ), function()
        {
            Route::group(array( 'before' => 'isEditor|projectRoles:' . Role::INSTRUCTION_ISSUER . ',' . Role::CONTRACTOR . '|checkPreviousAIFirstLevelMessage' ), function()
            {
                Route::get('create', array( 'as' => 'aiMessage.create', 'uses' => 'ArchitectInstructionMessagesController@create' ));
                Route::post('create', array( 'uses' => 'ArchitectInstructionMessagesController@store' ));
            });
        });

        // group route to correctly describe the API for third level messaging between Architect and Contractor
        Route::group(array( 'prefix' => '{aiId}/third_level_messages' ), function()
        {
            Route::group(array( 'before' => 'isEditor|projectRoles:' . Role::INSTRUCTION_ISSUER . ',' . Role::CONTRACTOR . '|checkPreviousAIThirdLevelMessage' ), function()
            {
                Route::get('create', array( 'as' => 'aiThirdLevelMessage.create', 'uses' => 'ArchitectInstructionThirdLevelMessagesController@create' ));
                Route::post('create', array( 'uses' => 'ArchitectInstructionThirdLevelMessagesController@store' ));
            });
        });

        Route::group(array( 'prefix' => '{aiId}/interim_claims' ), function()
        {
            Route::group(array( 'before' => 'isEditor|projectRoles:' . Role::INSTRUCTION_ISSUER . '|checkPreviousAIInterimClaim' ), function()
            {
                Route::get('create', array( 'as' => 'aiInterimClaim.create', 'uses' => 'ArchitectInstructionInterimClaimsController@create' ));
                Route::post('create', array( 'uses' => 'ArchitectInstructionInterimClaimsController@store' ));
            });
        });
    });

    Route::group(array( 'prefix' => 'engineer_instructions' ), function()
    {
        Route::get('/', array( 'as' => 'ei', 'uses' => 'EngineerInstructionsController@index' ));

        Route::group(array( 'before' => 'projectRoles:' . Role::CONSULTANT_1 . ',' . Role::CONSULTANT_2 ), function()
        {
            Route::get('create', array( 'as' => 'ei.create', 'uses' => 'EngineerInstructionsController@create' ));
            Route::post('create', array( 'uses' => 'EngineerInstructionsController@store' ));

            Route::delete('delete/{eiId}', array( 'as' => 'ei.delete', 'uses' => 'EngineerInstructionsController@destroy' ));

            Route::put('show/{eiId}', array( 'uses' => 'EngineerInstructionsController@update' ));
        });

        Route::get('show/{eiId}', array( 'as' => 'ei.show', 'uses' => 'EngineerInstructionsController@show' ));

        Route::group(array( 'before' => 'isEditor|projectRoles:' . Role::INSTRUCTION_ISSUER ), function()
        {
            Route::put('architect_update/{eiId}', array( 'as' => 'ei.architect_update', 'uses' => 'EngineerInstructionsController@architectUpdate' ));
        });
    });

    Route::group(array( 'prefix' => 'extension_of_times' ), function()
    {
        Route::get('/', array( 'as' => 'eot', 'uses' => 'ExtensionOfTimesController@index' ));

        Route::group(array( 'before' => 'projectRoles:' . Role::CONTRACTOR ), function()
        {
            Route::get('create/{aiId?}', array( 'as' => 'eot.create', 'uses' => 'ExtensionOfTimesController@create' ));
            Route::post('create/{aiId?}', array( 'uses' => 'ExtensionOfTimesController@store' ));

            Route::delete('delete/{eotId}', array( 'as' => 'eot.delete', 'uses' => 'ExtensionOfTimesController@destroy' ));

            Route::put('show/{eotId}', array( 'as' => 'eot.show', 'uses' => 'ExtensionOfTimesController@update' ));
        });

        Route::get('show/{eotId}', array( 'as' => 'eot.show', 'uses' => 'ExtensionOfTimesController@show' ));

        // group route to correctly describe the API for messaging between Architect and Contractor
        Route::group(array( 'prefix' => '{eotId}/first_level_messages' ), function()
        {
            Route::group(array( 'before' => 'isEditor|projectRoles:' . Role::INSTRUCTION_ISSUER . ',' . Role::CONTRACTOR . '|checkPreviousEOTFirstLevelMessage' ), function()
            {
                Route::get('create', array( 'as' => 'eotFirstLevelMessage.create', 'uses' => 'ExtensionOfTimeFirstLevelMessagesController@create' ));
                Route::post('create', array( 'uses' => 'ExtensionOfTimeFirstLevelMessagesController@store' ));
            });
        });

        Route::group(array( 'prefix' => '{eotId}/contractor_confirm_delay' ), function()
        {
            Route::group(array( 'before' => 'isEditor|projectRoles:' . Role::CONTRACTOR . '|checkPreviousEOTContractorConfirmDelay' ), function()
            {
                Route::get('create', array( 'as' => 'eotContractorConfirmDelay.create', 'uses' => 'ExtensionOfTimeContractorConfirmDelaysController@create' ));
                Route::post('create', array( 'uses' => 'ExtensionOfTimeContractorConfirmDelaysController@store' ));
            });
        });

        Route::group(array( 'prefix' => '{eotId}/second_level_messages' ), function()
        {
            Route::group(array( 'before' => 'isEditor|projectRoles:' . Role::INSTRUCTION_ISSUER . ',' . Role::CONTRACTOR . '|checkPreviousEOTSecondLevelMessage' ), function()
            {
                Route::get('create', array( 'as' => 'eotSecondLevelMessage.create', 'uses' => 'ExtensionOfTimeSecondLevelMessagesController@create' ));
                Route::post('create', array( 'uses' => 'ExtensionOfTimeSecondLevelMessagesController@store' ));
            });
        });

        Route::group(array( 'prefix' => '{eotId}/claims' ), function()
        {
            Route::group(array( 'before' => 'isEditor|projectRoles:' . Role::CONTRACTOR . '|checkPreviousEOTClaim' ), function()
            {
                Route::get('create', array( 'as' => 'eotClaim.create', 'uses' => 'ExtensionOfTimeClaimsController@create' ));
                Route::post('create', array( 'uses' => 'ExtensionOfTimeClaimsController@store' ));
            });
        });

        Route::group(array( 'prefix' => '{eotId}/third_level_messages' ), function()
        {
            Route::group(array( 'before' => 'isEditor|projectRoles:' . Role::INSTRUCTION_ISSUER . ',' . Role::CONTRACTOR . '|checkPreviousEOTThirdLevelMessage' ), function()
            {
                Route::get('create', array( 'as' => 'eotThirdLevelMessage.create', 'uses' => 'ExtensionOfTimeThirdLevelMessagesController@create' ));
                Route::post('create', array( 'uses' => 'ExtensionOfTimeThirdLevelMessagesController@store' ));
            });
        });

        Route::group(array( 'prefix' => '{eotId}/fourth_level_messages' ), function()
        {
            Route::group(array( 'before' => 'isEditor|projectRoles:' . Role::INSTRUCTION_ISSUER . ',' . Role::CONTRACTOR . '|checkPreviousEOTFourthLevelMessage' ), function()
            {
                Route::get('create', array( 'as' => 'eotFourthLevelMessage.create', 'uses' => 'ExtensionOfTimeFourthLevelMessagesController@create' ));
                Route::post('create', array( 'uses' => 'ExtensionOfTimeFourthLevelMessagesController@store' ));
            });
        });

        // ajax route to get deadline's data for selected AI
        Route::post('getAIDeadlineDateURL', array( 'as' => 'eot.getAIDeadlineDateURL', 'uses' => 'ExtensionOfTimesController@getDeadLineToComply' ));
    });

    Route::group(array( 'prefix' => 'loss_and_or_expenses' ), function()
    {
        Route::get('/', array( 'as' => 'loe', 'uses' => 'LossAndOrExpensesController@index' ));

        Route::group(array( 'before' => 'projectRoles:' . Role::CONTRACTOR ), function()
        {
            Route::get('create/{aiId?}', array( 'as' => 'loe.create', 'uses' => 'LossAndOrExpensesController@create' ));
            Route::post('create/{aiId?}', array( 'uses' => 'LossAndOrExpensesController@store' ));

            Route::delete('delete/{loeId}', array( 'as' => 'loe.delete', 'uses' => 'LossAndOrExpensesController@destroy' ));

            Route::put('show/{loeId}', array( 'as' => 'loe.show', 'uses' => 'LossAndOrExpensesController@update' ));
        });

        Route::get('show/{loeId}', array( 'as' => 'loe.show', 'uses' => 'LossAndOrExpensesController@show' ));

        // group route to correctly describe the API for messaging between Architect and Contractor
        Route::group(array( 'prefix' => '{loeId}/first_level_messages' ), function()
        {
            Route::group(array( 'before' => 'isEditor|projectRoles:' . Role::INSTRUCTION_ISSUER . ',' . Role::CONTRACTOR . '|checkPreviousLOEFirstLevelMessage' ), function()
            {
                Route::get('create', array( 'as' => 'loeFirstLevelMessage.create', 'uses' => 'LossAndOrExpenseFirstLevelMessagesController@create' ));
                Route::post('create', array( 'uses' => 'LossAndOrExpenseFirstLevelMessagesController@store' ));
            });
        });

        Route::group(array( 'prefix' => '{loeId}/contractor_confirm_delay' ), function()
        {
            Route::group(array( 'before' => 'isEditor|projectRoles:' . Role::CONTRACTOR . '|checkPreviousLOEContractorConfirmDelay' ), function()
            {
                Route::get('create', array( 'as' => 'loeContractorConfirmDelay.create', 'uses' => 'LossAndOrExpenseContractorConfirmDelaysController@create' ));
                Route::post('create', array( 'uses' => 'LossAndOrExpenseContractorConfirmDelaysController@store' ));
            });
        });

        Route::group(array( 'prefix' => '{loeId}/second_level_messages' ), function()
        {
            Route::group(array( 'before' => 'isEditor|projectRoles:' . Role::INSTRUCTION_ISSUER . ',' . Role::CONTRACTOR . '|checkPreviousLOESecondLevelMessage' ), function()
            {
                Route::get('create', array( 'as' => 'loeSecondLevelMessage.create', 'uses' => 'LossAndOrExpenseSecondLevelMessagesController@create' ));
                Route::post('create', array( 'uses' => 'LossAndOrExpenseSecondLevelMessagesController@store' ));
            });
        });

        Route::group(array( 'prefix' => '{loeId}/claims' ), function()
        {
            Route::group(array( 'before' => 'isEditor|projectRoles:' . Role::CONTRACTOR . '|checkPreviousLOEClaim' ), function()
            {
                Route::get('create', array( 'as' => 'loeClaim.create', 'uses' => 'LossAndOrExpenseClaimsController@create' ));
                Route::post('create', array( 'uses' => 'LossAndOrExpenseClaimsController@store' ));
            });
        });

        Route::group(array( 'prefix' => '{loeId}/third_level_messages' ), function()
        {
            Route::group(array( 'before' => 'isEditor|projectRoles:' . Role::INSTRUCTION_ISSUER . ',' . Role::CONTRACTOR . ',' . Role::CLAIM_VERIFIER . '|checkPreviousLOEThirdLevelMessage' ), function()
            {
                Route::get('create', array( 'as' => 'loeThirdLevelMessage.create', 'uses' => 'LossAndOrExpenseThirdLevelMessagesController@create' ));
                Route::post('create', array( 'uses' => 'LossAndOrExpenseThirdLevelMessagesController@store' ));
            });
        });

        Route::group(array( 'prefix' => '{loeId}/fourth_level_messages' ), function()
        {
            Route::group(array( 'before' => 'isEditor|projectRoles:' . Role::INSTRUCTION_ISSUER . ',' . Role::CONTRACTOR . ',' . Role::CLAIM_VERIFIER . '|checkPreviousLOEFourthLevelMessage' ), function()
            {
                Route::get('create', array( 'as' => 'loeFourthLevelMessage.create', 'uses' => 'LossAndOrExpenseFourthLevelMessagesController@create' ));
                Route::post('create', array( 'uses' => 'LossAndOrExpenseFourthLevelMessagesController@store' ));
            });
        });

        Route::group(array( 'prefix' => '{loeId}/interim_claims' ), function()
        {
            Route::group(array( 'before' => 'isEditor|projectRoles:' . Role::INSTRUCTION_ISSUER . '|checkPreviousLOEInterimClaim' ), function()
            {
                Route::get('create', array( 'as' => 'loeInterimClaim.create', 'uses' => 'LossAndOrExpenseInterimClaimsController@create' ));
                Route::post('create', array( 'uses' => 'LossAndOrExpenseInterimClaimsController@store' ));
            });
        });

        Route::post('getAIDeadlineDateURL', array( 'as' => 'loe.getAIDeadlineDateURL', 'uses' => 'LossAndOrExpensesController@getDeadLineToComply' ));
    });

    Route::group(array( 'prefix' => 'additional_expenses' ), function()
    {
        Route::get('/', array( 'as' => 'ae', 'uses' => 'AdditionalExpensesController@index' ));

        Route::group(array( 'before' => 'projectRoles:' . Role::CONTRACTOR ), function()
        {
            Route::get('create/{aiId?}', array( 'as' => 'ae.create', 'uses' => 'AdditionalExpensesController@create' ));
            Route::post('create/{aiId?}', array( 'uses' => 'AdditionalExpensesController@store' ));

            Route::delete('delete/{aeId}', array( 'as' => 'ae.delete', 'uses' => 'AdditionalExpensesController@destroy' ));

            Route::put('show/{aeId}', array( 'uses' => 'AdditionalExpensesController@update' ));
        });

        Route::get('show/{aeId}', array( 'as' => 'ae.show', 'uses' => 'AdditionalExpensesController@show' ));

        // group route to correctly describe the API for messaging between Architect and Contractor
        Route::group(array( 'prefix' => '{aeId}/first_level_messages' ), function()
        {
            Route::group(array( 'before' => 'isEditor|projectRoles:' . Role::INSTRUCTION_ISSUER . ',' . Role::CONTRACTOR . '|checkPreviousAEFirstLevelMessage' ), function()
            {
                Route::get('create', array( 'as' => 'aeFirstLevelMessage.create', 'uses' => 'AdditionalExpenseFirstLevelMessagesController@create' ));
                Route::post('create', array( 'uses' => 'AdditionalExpenseFirstLevelMessagesController@store' ));
            });
        });

        Route::group(array( 'prefix' => '{aeId}/contractor_confirm_delay' ), function()
        {
            Route::group(array( 'before' => 'isEditor|projectRoles:' . Role::CONTRACTOR . '|checkPreviousAEContractorConfirmDelay' ), function()
            {
                Route::get('create', array( 'as' => 'aeContractorConfirmDelay.create', 'uses' => 'AdditionalExpenseContractorConfirmDelaysController@create' ));
                Route::post('create', array( 'uses' => 'AdditionalExpenseContractorConfirmDelaysController@store' ));
            });
        });

        Route::group(array( 'prefix' => '{aeId}/second_level_messages' ), function()
        {
            Route::group(array( 'before' => 'isEditor|projectRoles:' . Role::INSTRUCTION_ISSUER . ',' . Role::CONTRACTOR . '|checkPreviousAESecondLevelMessage' ), function()
            {
                Route::get('create', array( 'as' => 'aeSecondLevelMessage.create', 'uses' => 'AdditionalExpenseSecondLevelMessagesController@create' ));
                Route::post('create', array( 'uses' => 'AdditionalExpenseSecondLevelMessagesController@store' ));
            });
        });

        Route::group(array( 'prefix' => '{aeId}/claims' ), function()
        {
            Route::group(array( 'before' => 'isEditor|projectRoles:' . Role::CONTRACTOR . '|checkPreviousAEClaim' ), function()
            {
                Route::get('create', array( 'as' => 'aeClaim.create', 'uses' => 'AdditionalExpenseClaimsController@create' ));
                Route::post('create', array( 'uses' => 'AdditionalExpenseClaimsController@store' ));
            });
        });

        Route::group(array( 'prefix' => '{aeId}/third_level_messages' ), function()
        {
            Route::group(array( 'before' => 'isEditor|projectRoles:' . Role::INSTRUCTION_ISSUER . ',' . Role::CONTRACTOR . ',' . Role::CLAIM_VERIFIER . '|checkPreviousAEThirdLevelMessage' ), function()
            {
                Route::get('create', array( 'as' => 'aeThirdLevelMessage.create', 'uses' => 'AdditionalExpenseThirdLevelMessagesController@create' ));
                Route::post('create', array( 'uses' => 'AdditionalExpenseThirdLevelMessagesController@store' ));
            });
        });

        Route::group(array( 'prefix' => '{aeId}/fourth_level_messages' ), function()
        {
            Route::group(array( 'before' => 'isEditor|projectRoles:' . Role::INSTRUCTION_ISSUER . ',' . Role::CONTRACTOR . ',' . Role::CLAIM_VERIFIER . '|checkPreviousAEFourthLevelMessage' ), function()
            {
                Route::get('create', array( 'as' => 'aeFourthLevelMessage.create', 'uses' => 'AdditionalExpenseFourthLevelMessagesController@create' ));
                Route::post('create', array( 'uses' => 'AdditionalExpenseFourthLevelMessagesController@store' ));
            });
        });

        Route::group(array( 'prefix' => '{aeId}/interim_claims' ), function()
        {
            Route::group(array( 'before' => 'isEditor|projectRoles:' . Role::INSTRUCTION_ISSUER . '|checkPreviousAEInterimClaim' ), function()
            {
                Route::get('create', array( 'as' => 'aeInterimClaim.create', 'uses' => 'AdditionalExpenseInterimClaimsController@create' ));
                Route::post('create', array( 'uses' => 'AdditionalExpenseInterimClaimsController@store' ));
            });
        });

        Route::post('getAIDeadlineDateURL', array( 'as' => 'ae.getAIDeadlineDateURL', 'uses' => 'AdditionalExpensesController@getDeadLineToComply' ));
    });

    Route::group(array( 'prefix' => 'weather_records' ), function()
    {
        Route::get('/', array( 'as' => 'wr', 'uses' => 'WeatherRecordsController@index' ));

        Route::get('create/{wrId?}', array( 'as' => 'wr.create', 'uses' => 'WeatherRecordsController@create' ));
        Route::post('create/{wrId?}', array( 'uses' => 'WeatherRecordsController@store' ));

        Route::delete('delete/{wrId}', array( 'as' => 'wr.delete', 'uses' => 'WeatherRecordsController@destroy' ));

        Route::get('show/{wrId}', array( 'as' => 'wr.show', 'uses' => 'WeatherRecordsController@show' ));
        Route::put('show/{wrId}', array( 'uses' => 'WeatherRecordsController@update' ));

        Route::group(array( 'before' => 'isEditor|projectRoles:' . Role::INSTRUCTION_ISSUER ), function()
        {
            Route::put('architect_update/{wrId}', array( 'as' => 'wr.architect_update', 'uses' => 'WeatherRecordsController@architectUpdate' ));
        });

        Route::group(array( 'prefix' => '{wrId?}/weather_reports' ), function()
        {
            Route::get('create/{mode}', array( 'as' => 'wrReport.create', 'uses' => 'WeatherRecordReportsController@create' ));
            Route::post('create/{mode}', array( 'uses' => 'WeatherRecordReportsController@store' ));

            Route::delete('delete/{wrrId}/{mode}', array( 'as' => 'wrReport.delete', 'uses' => 'WeatherRecordReportsController@destroy' ));
        });
    });

    Route::group(array( 'prefix' => 'interim_claims' ), function()
    {
        Route::get('/', array( 'as' => 'ic', 'uses' => 'InterimClaimsController@index' ));

        Route::group(array( 'before' => 'isEditor|projectRoles:' . Role::CONTRACTOR ), function()
        {
            Route::get('create', array( 'as' => 'ic.create', 'uses' => 'InterimClaimsController@create' ));
            Route::post('create', array( 'uses' => 'InterimClaimsController@store' ));
        });

        Route::get('show/{icId}', array( 'as' => 'ic.show', 'uses' => 'InterimClaimsController@show' ));

        Route::group(array( 'before' => 'isEditor|projectRoles:' . Role::INSTRUCTION_ISSUER . ',' . Role::CONTRACTOR . ',' . Role::CLAIM_VERIFIER ), function()
        {
            Route::post('create_additional_information/{icId}', array( 'as' => 'ic.additional_info_create', 'uses' => 'InterimClaimsController@createNewAdditionalInformation' ));
        });

        Route::get('generatePrintOut/{iciId}', array( 'as' => 'ic.additional_info_print', 'uses' => 'InterimClaimsController@generatePrintOutForAdditionalInformation' ));
    });
});