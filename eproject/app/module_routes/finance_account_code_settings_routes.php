<?php

Route::group([ 'before' => 'moduleAccess:' . \PCK\ModulePermission\ModulePermission::MODULE_ID_FINANCE, 'prefix' => 'finance/account-code-settings' ], function()
{
    Route::get('/', [ 'as' => 'finance.account.code.settings.index', 'uses' => 'AccountCodeSettingsController@index' ]);
    Route::get('/getProjectsList', [ 'as' => 'finance.account.code.settings.projects.list.get', 'uses' => 'AccountCodeSettingsController@getProjectsList' ]);

    Route::group([ 'prefix' => 'project/{projectId}' ], function() {
        Route::get('/show', [ 'as' => 'finance.account.code.settings.show', 'uses' => 'AccountCodeSettingsController@show' ]);
        Route::get('/getProjectCodeSettings', [ 'as' => 'project.code.settings.get', 'uses' => 'AccountCodeSettingsController@getProjectCodeSettingRecords' ]);
        Route::get('/getSubsidiaryHierarchy', [ 'as' => 'subsidiary.hierarchy.get', 'uses' => 'AccountCodeSettingsController@getSubsidiaryHierarchy' ]);
        Route::get('/getSelectedSubsidiaries', [ 'as' => 'project.code.settings.selected.subsidiaries.get', 'uses' => 'AccountCodeSettingsController@getSelectedSubsidiaries' ]);
        Route::get('/saveSelectedSubsidiaries', [ 'as' => 'project.code.settings.selected.subsidiaries.save', 'uses' => 'AccountCodeSettingsController@saveSelectedSubsidiaries' ]);
        Route::post('/updateSelectedSubsidiaries', [ 'as' => 'project.code.settings.update', 'uses' => 'AccountCodeSettingsController@updateSelectedSubsidiaries' ]);
        Route::post('/saveApportionmentType', [ 'as' => 'account.code.settings.apportionment.type.save', 'uses' => 'AccountCodeSettingsController@saveApportionmentType' ]);
        Route::get('/getSelectedAccountGroup', [ 'as' => 'account.code.settings.account.group.selected.get', 'uses' => 'AccountCodeSettingsController@getSelectedAccountGroup' ]);
        Route::post('/saveSelectedAccountCodes', [ 'as' => 'account.code.settings.account.codes.save', 'uses' => 'AccountCodeSettingsController@saveSelectedAccountCodes' ]);
        Route::get('/getSelectedAccountCodes', [ 'as' => 'account.code.settings.account.codes.selected.get', 'uses' => 'AccountCodeSettingsController@getSelectedAccountCodes' ]);
        Route::get('/getSavedItemCodes', [ 'as' => 'account.code.settings.saved.item.codes.get', 'uses' => 'AccountCodeSettingsController@getSavedItemCodes' ]);
        Route::post('/updateSupplierCode', [ 'as' => 'supplier.code.update', 'uses' => 'AccountCodeSettingsController@updateSupplierCode' ]);
        Route::post('/submitForApproval', [ 'as' => 'account.code.settings.approval.submit', 'uses' => 'AccountCodeSettingsController@submitForApproval' ]);
        Route::post('/submitForApprovalCheck', [ 'as' => 'account.code.settings.approval.submit.check', 'uses' => 'AccountCodeSettingsController@submitForApprovalCheck' ]);
        Route::get('/getApprovedPhaseSubsidiaries', [ 'as' => 'project.code.approved.phase.subsidiaries.get', 'uses' => 'AccountCodeSettingsController@getApprovedPhaseSubsidiaries' ]);
        Route::get('/getProportion', array( 'as' => 'project.code.settings.proportion.get', 'uses' => 'AccountCodeSettingsController@getProportionsGroupedByIds' ));
        Route::post('beneficiary-bank-account-number', array( 'as' => 'project.accountCodeSetting.beneficiaryBankAccountNumber.update', 'uses' => 'AccountCodeSettingsController@updateBeneficiaryBankAccountNumber' ));
        Route::post('vendor-category', array( 'as' => 'project.accountCodeSetting.vendorCategory.update', 'uses' => 'AccountCodeSettingsController@updateVendorCategory' ));
        Route::post('save-item-code-settings-amount', [ 'as' => 'account.code.settings.itemCodeSettings.amount.save', 'uses' => 'AccountCodeSettingsController@saveItemCodeSettingsAmounts' ]);

        Route::group([ 'prefix' => 'projectCodeSettingId/{projectCodeSettingId}' ], function() {
            Route::post('/deleteProjectCodeSettingRecord', [ 'as' => 'project.code.setting.delete', 'uses' => 'AccountCodeSettingsController@deleteProjectCodeSettingRecord' ]);
        });

        Route::group([ 'prefix' => 'itemCodeSettingId/{itemCodeSettingId}' ], function() {
            Route::post('/deleteItemCodeSetting', [ 'as' => 'item.code.setting.delete', 'uses' => 'AccountCodeSettingsController@deleteItemCodeSetting' ]);
        });
    });

    Route::get('/getListOfAccountCodes', [ 'as' => 'account.codes.list.get', 'uses' => 'AccountCodeSettingsController@getListOfAccountCodes' ]);
});

// general access to system user to manage account groups & account codes
// can be used by all users with different access roles & from different modules
Route::group(['prefix' => 'account-groups', 'before' => 'guest|appLicenseValid|passwordUpdated|temporaryLogin'], function()
{
    Route::get('/', [ 'as' => 'account.group.index', 'uses' => 'AccountGroupController@index' ]);
    Route::get('{accountGroupId}', ['as' => 'account.group.info', 'uses' => 'AccountGroupController@accountGroupInfo']);
    Route::post('store', ['as' => 'account.group.store', 'uses' => 'AccountGroupController@accountGroupStore']);
    Route::delete('{accountGroupId}', ['as' => 'account.group.delete', 'uses' => 'AccountGroupController@accountGroupDelete']);

    Route::group(['prefix' => 'account-codes/{accountGroupId}'], function(){
        Route::get('list', ['as' => 'account.group.account.codes.ajax.list', 'uses' => 'AccountGroupController@accountCodeList']);
        Route::post('store', ['as' => 'account.group.account.codes.store', 'uses' => 'AccountGroupController@accountCodeStore']);
        Route::get('{id}', ['as' => 'account.group.account.codes.info', 'uses' => 'AccountGroupController@accountCodeInfo']);
        Route::delete('{id}', ['as' => 'account.group.account.codes.delete', 'uses' => 'AccountGroupController@accountCodeDelete']);
    });
});
