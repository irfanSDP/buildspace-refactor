<?php

Route::group(array( 'before' => 'checkForTenderDocumentsAllowedRoleViewCompleteFormOfTender' ), function()
{
    //print form of tender with contractor details
    Route::get('tender/{tenderId}/company/{companyId}/form_of_tender_print', array( 'as' => 'form_of_tender.contractorInput.print', 'uses' => 'FormOfTenderPrintController@processContractorFormOfTender' ));
    Route::get('tender/{tenderId}/company/{companyId}/form_of_tender_generate', array( 'as' => 'form_of_tender.contractorInput.generate', 'uses' => 'FormOfTenderPrintController@generateContractorFormOfTender' ));
});

Route::group(array( 'before' => 'canEditFormOfTender' ), function()
{
    Route::group(array( 'prefix' => 'tenders/{tenderId}' ), function()
    {
        Route::get('form_of_tender', array( 'as' => 'form_of_tender.edit', 'uses' => 'FormOfTenderController@edit' ));
        Route::get('form_of_tender_header_edit', array( 'as' => 'form_of_tender.header.edit', 'uses' => 'FormOfTenderHeaderController@editHeader' ));
        Route::get('form_of_tender_address_edit', array( 'as' => 'form_of_tender.address.edit', 'uses' => 'FormOfTenderAddressController@editAddress' ));
        Route::get('form_of_tender_clauses_edit', array( 'as' => 'form_of_tender.clauses.edit', 'uses' => 'FormOfTenderClausesController@editClauses' ));
        Route::get('form_of_tender_print_settings_edit', array( 'as' => 'form_of_tender.printSettings.edit', 'uses' => 'FormOfTenderPrintSettingsController@editPrintSettings' ));
        Route::get('form_of_tender_tender_alternatives_edit', array( 'as' => 'form_of_tender.tenderAlternatives.edit', 'uses' => 'FormOfTenderTenderAlternativesController@editTenderAlternatives' ));

        Route::post('/form_of_tender_header_update', array( 'as' => 'form_of_tender.header.update', 'uses' => 'FormOfTenderHeaderController@updateHeader' ));
        Route::post('/form_of_tender_address_update', array( 'as' => 'form_of_tender.address.update', 'uses' => 'FormOfTenderAddressController@updateAddress' ));
        Route::post('/form_of_tender_clauses_update', array( 'as' => 'form_of_tender.clauses.update', 'uses' => 'FormOfTenderClausesController@updateClauses' ));
        Route::post('/form_of_tender_print_settings_update', array( 'as' => 'form_of_tender.printSettings.update', 'uses' => 'FormOfTenderPrintSettingsController@updatePrintSettings' ));
        Route::post('/form_of_tender_tender_alternatives_update', array( 'as' => 'form_of_tender.tenderAlternatives.update', 'uses' => 'FormOfTenderTenderAlternativesController@updateTenderAlternatives' ));
    });
});

Route::group(array( 'prefix' => 'tenders/{tenderId}', 'before' => 'canViewBlankFormOfTender' ), function()
{
    // form of tender generate and print pdf
    Route::get('form_of_tender_generate', array( 'as' => 'form_of_tender.generate', 'uses' => 'FormOfTenderPrintController@generateFormOfTender' ));
    Route::get('form_of_tender_print', array( 'as' => 'form_of_tender.print', 'uses' => 'FormOfTenderPrintController@processFormOfTender' ));
});