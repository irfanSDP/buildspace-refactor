<?php

Route::group(array( 'prefix' => 'contract-management' ), function()
{
    Route::group(array( 'prefix' => 'permissions', 'before' => 'contractManagement.isUserManager' ), function()
    {
        Route::get('/', array( 'as' => 'contractManagement.permissions.index', 'uses' => 'ContractManagementModulePermissionsController@index' ));
        Route::get('assigned/get', array( 'as' => 'contractManagement.permissions.assigned', 'uses' => 'ContractManagementModulePermissionsController@getAssignedUsers' ));
        Route::get('assignable/get', array( 'as' => 'contractManagement.permissions.assignable', 'uses' => 'ContractManagementModulePermissionsController@getAssignableUsers' ));
        Route::post('assign', array( 'as' => 'contractManagement.permissions.assign', 'uses' => 'ContractManagementModulePermissionsController@assign' ));
        Route::post('verifier-status/toggle/user/{userId}/module/{moduleId}', array( 'as' => 'contractManagement.permissions.verifier.toggle', 'uses' => 'ContractManagementModulePermissionsController@toggleVerifierStatus' ));
        Route::delete('user/{userId}/module/{moduleId}', array( 'as' => 'contractManagement.permissions.revoke', 'uses' => 'ContractManagementModulePermissionsController@revoke' ));

        Route::group(array( 'prefix' => 'verifiers/module/{moduleId}' ), function()
        {
            Route::get('/', array( 'as' => 'contractManagement.permissions.verifiers.index', 'uses' => 'ContractManagementModulePermissionsController@verifiersIndex' ));
            Route::post('assign', array( 'as' => 'contractManagement.permissions.verifiers.assign', 'uses' => 'ContractManagementModulePermissionsController@verifiersAssign' ));
            Route::post('reset', array( 'as' => 'contractManagement.permissions.verifiers.reset', 'uses' => 'ContractManagementModulePermissionsController@verifiersReset' ));
        });
    });

    Route::group(array( 'prefix' => 'letter-of-award' ), function()
    {
        Route::get('/', array( 'as' => 'contractManagement.letterOfAward.index', 'uses' => 'PostContractLetterOfAwardController@index' ));

        Route::group(array( 'prefix' => 'substitute/{currentVerifierUserId}', 'before' => 'letterOfAward.isValidSubstitute' ), function()
        {
            Route::get('approve', array( 'as' => 'contractManagement.letterOfAward.substituteAndApprove', 'uses' => 'PostContractLetterOfAwardController@substituteAndApprove' ));
            Route::get('reject', array( 'as' => 'contractManagement.letterOfAward.substituteAndReject', 'uses' => 'PostContractLetterOfAwardController@substituteAndReject' ));
        });
    });

    Route::group(array( 'prefix' => 'claim-certificate' ), function()
    {
        Route::get('/', array( 'as' => 'contractManagement.claimCertificate.index', 'uses' => 'ClaimCertificateController@index' ));

        Route::group(array( 'prefix' => 'substitute/{currentVerifierUserId}/item/{objectId}', 'before' => 'claim.claimCertificate.isValidSubstitute' ), function()
        {
            Route::get('approve', array( 'as' => 'contractManagement.claimCertificate.substituteAndApprove', 'uses' => 'ClaimCertificateController@substituteAndApprove' ));
            Route::get('reject', array( 'as' => 'contractManagement.claimCertificate.substituteAndReject', 'uses' => 'ClaimCertificateController@substituteAndReject' ));
        });
    });

    Route::group(array( 'prefix' => 'variation-order' ), function()
    {
        Route::get('/', array( 'as' => 'contractManagement.variationOrder.index', 'uses' => 'ClaimVariationOrderController@index' ));

        Route::group(array( 'prefix' => 'substitute/{currentVerifierUserId}/item/{objectId}', 'before' => 'claim.variationOrder.isValidSubstitute' ), function()
        {
            Route::get('approve', array( 'as' => 'contractManagement.variationOrder.substituteAndApprove', 'uses' => 'ClaimVariationOrderController@substituteAndApprove' ));
            Route::get('reject', array( 'as' => 'contractManagement.variationOrder.substituteAndReject', 'uses' => 'ClaimVariationOrderController@substituteAndReject' ));
        });
    });

    Route::group(array( 'prefix' => 'material-on-site' ), function()
    {
        Route::get('/', array( 'as' => 'contractManagement.materialOnSite.index', 'uses' => 'ClaimMaterialOnSiteController@index' ));

        Route::group(array( 'prefix' => 'substitute/{currentVerifierUserId}/item/{objectId}', 'before' => 'claim.materialOnSite.isValidSubstitute' ), function()
        {
            Route::get('approve', array( 'as' => 'contractManagement.materialOnSite.substituteAndApprove', 'uses' => 'ClaimMaterialOnSiteController@substituteAndApprove' ));
            Route::get('reject', array( 'as' => 'contractManagement.materialOnSite.substituteAndReject', 'uses' => 'ClaimMaterialOnSiteController@substituteAndReject' ));
        });
    });

    Route::group(array( 'prefix' => 'deposit' ), function()
    {
        Route::get('/', array( 'as' => 'contractManagement.deposit.index', 'uses' => 'ClaimDepositController@index' ));

        Route::group(array( 'prefix' => 'substitute/{currentVerifierUserId}/item/{objectId}', 'before' => 'claim.deposit.isValidSubstitute' ), function()
        {
            Route::get('approve', array( 'as' => 'contractManagement.deposit.substituteAndApprove', 'uses' => 'ClaimDepositController@substituteAndApprove' ));
            Route::get('reject', array( 'as' => 'contractManagement.deposit.substituteAndReject', 'uses' => 'ClaimDepositController@substituteAndReject' ));
        });
    });

    Route::group(array( 'prefix' => 'out-of-contract-items' ), function()
    {
        Route::get('/', array( 'as' => 'contractManagement.outOfContractItems.index', 'uses' => 'ClaimOutOfContractItemsController@index' ));

        Route::group(array( 'prefix' => 'substitute/{currentVerifierUserId}/item/{objectId}', 'before' => 'claim.outOfContractItems.isValidSubstitute' ), function()
        {
            Route::get('approve', array( 'as' => 'contractManagement.outOfContractItems.substituteAndApprove', 'uses' => 'ClaimOutOfContractItemsController@substituteAndApprove' ));
            Route::get('reject', array( 'as' => 'contractManagement.outOfContractItems.substituteAndReject', 'uses' => 'ClaimOutOfContractItemsController@substituteAndReject' ));
        });
    });

    Route::group(array( 'prefix' => 'purchase-on-behalf' ), function()
    {
        Route::get('/', array( 'as' => 'contractManagement.purchaseOnBehalf.index', 'uses' => 'ClaimPurchaseOnBehalfController@index' ));

        Route::group(array( 'prefix' => 'substitute/{currentVerifierUserId}/item/{objectId}', 'before' => 'claim.purchaseOnBehalf.isValidSubstitute' ), function()
        {
            Route::get('approve', array( 'as' => 'contractManagement.purchaseOnBehalf.substituteAndApprove', 'uses' => 'ClaimPurchaseOnBehalfController@substituteAndApprove' ));
            Route::get('reject', array( 'as' => 'contractManagement.purchaseOnBehalf.substituteAndReject', 'uses' => 'ClaimPurchaseOnBehalfController@substituteAndReject' ));
        });
    });

    Route::group(array( 'prefix' => 'advanced-payment' ), function()
    {
        Route::get('/', array( 'as' => 'contractManagement.advancedPayment.index', 'uses' => 'ClaimAdvancedPaymentController@index' ));

        Route::group(array( 'prefix' => 'substitute/{currentVerifierUserId}/item/{objectId}', 'before' => 'claim.advancedPayment.isValidSubstitute' ), function()
        {
            Route::get('approve', array( 'as' => 'contractManagement.advancedPayment.substituteAndApprove', 'uses' => 'ClaimAdvancedPaymentController@substituteAndApprove' ));
            Route::get('reject', array( 'as' => 'contractManagement.advancedPayment.substituteAndReject', 'uses' => 'ClaimAdvancedPaymentController@substituteAndReject' ));
        });
    });

    Route::group(array( 'prefix' => 'work-on-behalf' ), function()
    {
        Route::get('/', array( 'as' => 'contractManagement.workOnBehalf.index', 'uses' => 'ClaimWorkOnBehalfController@index' ));

        Route::group(array( 'prefix' => 'substitute/{currentVerifierUserId}/item/{objectId}', 'before' => 'claim.workOnBehalf.isValidSubstitute' ), function()
        {
            Route::get('approve', array( 'as' => 'contractManagement.workOnBehalf.substituteAndApprove', 'uses' => 'ClaimWorkOnBehalfController@substituteAndApprove' ));
            Route::get('reject', array( 'as' => 'contractManagement.workOnBehalf.substituteAndReject', 'uses' => 'ClaimWorkOnBehalfController@substituteAndReject' ));
        });
    });

    Route::group(array( 'prefix' => 'work-on-behalf-back-charge' ), function()
    {
        Route::get('/', array( 'as' => 'contractManagement.workOnBehalfBackCharge.index', 'uses' => 'ClaimWorkOnBehalfBackChargeController@index' ));

        Route::group(array( 'prefix' => 'substitute/{currentVerifierUserId}/item/{objectId}', 'before' => 'claim.workOnBehalfBackCharge.isValidSubstitute' ), function()
        {
            Route::get('approve', array( 'as' => 'contractManagement.workOnBehalfBackCharge.substituteAndApprove', 'uses' => 'ClaimWorkOnBehalfBackChargeController@substituteAndApprove' ));
            Route::get('reject', array( 'as' => 'contractManagement.workOnBehalfBackCharge.substituteAndReject', 'uses' => 'ClaimWorkOnBehalfBackChargeController@substituteAndReject' ));
        });
    });

    Route::group(array( 'prefix' => 'penalty' ), function()
    {
        Route::get('/', array( 'as' => 'contractManagement.penalty.index', 'uses' => 'ClaimPenaltyController@index' ));

        Route::group(array( 'prefix' => 'substitute/{currentVerifierUserId}/item/{objectId}', 'before' => 'claim.penalty.isValidSubstitute' ), function()
        {
            Route::get('approve', array( 'as' => 'contractManagement.penalty.substituteAndApprove', 'uses' => 'ClaimPenaltyController@substituteAndApprove' ));
            Route::get('reject', array( 'as' => 'contractManagement.penalty.substituteAndReject', 'uses' => 'ClaimPenaltyController@substituteAndReject' ));
        });
    });

    Route::group(array( 'prefix' => 'permit' ), function()
    {
        Route::get('/', array( 'as' => 'contractManagement.permit.index', 'uses' => 'ClaimPermitController@index' ));

        Route::group(array( 'prefix' => 'substitute/{currentVerifierUserId}/item/{objectId}', 'before' => 'claim.permit.isValidSubstitute' ), function()
        {
            Route::get('approve', array( 'as' => 'contractManagement.permit.substituteAndApprove', 'uses' => 'ClaimPermitController@substituteAndApprove' ));
            Route::get('reject', array( 'as' => 'contractManagement.permit.substituteAndReject', 'uses' => 'ClaimPermitController@substituteAndReject' ));
        });
    });

    Route::group(array( 'prefix' => 'water-deposit' ), function()
    {
        Route::get('/', array( 'as' => 'contractManagement.waterDeposit.index', 'uses' => 'ClaimWaterDepositController@index' ));

        Route::group(array( 'prefix' => 'substitute/{currentVerifierUserId}/item/{objectId}', 'before' => 'claim.waterDeposit.isValidSubstitute' ), function()
        {
            Route::get('approve', array( 'as' => 'contractManagement.waterDeposit.substituteAndApprove', 'uses' => 'ClaimWaterDepositController@substituteAndApprove' ));
            Route::get('reject', array( 'as' => 'contractManagement.waterDeposit.substituteAndReject', 'uses' => 'ClaimWaterDepositController@substituteAndReject' ));
        });
    });

});
