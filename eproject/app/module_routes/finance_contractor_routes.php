<?php

    Route::group(array('prefix' => 'finance/contractor/{contractorId}'), function() {
        Route::get('/', ['as' => 'finance.contractor.claim-certificate', 'uses' => 'ContractorClaimCertificatePaymentController@list']);
        Route::get('/claim-certificates', ['as' => 'finance.contractor.module.claim-certificate.get', 'uses' => 'ContractorClaimCertificatePaymentController@getClaimCertificateList']);
    });

    Route::get('{claimCertificateId}/print', array( 'as' => 'contractor.finance.claim-certificate.print', 'uses' => 'ClaimCertificatePaymentController@printClaimCertificate' ));

