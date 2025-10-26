<?php
Route::group(array('prefix' => 'e_bidding'), function()
{
    Route::get('create/{tenderId}', array( 'as' => 'projects.e_bidding.create', 'uses' => 'EBiddingsController@create' ));
    Route::post('store', array( 'as' => 'projects.e_bidding.store', 'uses' => 'EBiddingsController@store' ));

    Route::group(array('before' => 'eBidding.checkProjectEBiddingAccess'), function()
    {
        Route::get('/', array( 'as' => 'projects.e_bidding.index', 'uses' => 'EBiddingsController@index' ));
        Route::get('edit/{id}', array( 'as' => 'projects.e_bidding.edit', 'uses' => 'EBiddingsController@edit' ));
        Route::put('update/{id}', array( 'as' => 'projects.e_bidding.update', 'uses' => 'EBiddingsController@update' ));
        Route::put('disable', array( 'as' => 'projects.e_bidding.disable', 'uses' => 'EBiddingsController@disable' ));
        Route::put('enable', array( 'as' => 'projects.e_bidding.enable', 'uses' => 'EBiddingsController@enable' ));

        Route::get('assign_committee', array( 'as' => 'projects.e_bidding.assignCommittees', 'uses' => 'EBiddingCommitteesController@edit' ));
        Route::put('assign_committee', array( 'uses' => 'EBiddingCommitteesController@update' ));

        Route::get('assign_verifier', array( 'as' => 'projects.e_bidding.getVerifier', 'uses' => 'EBiddingsController@getVerifier' ));
        Route::post('assign_verifier', array( 'as' => 'projects.e_bidding.assignVerifier', 'uses' => 'EBiddingsController@assignVerifier' ));

        Route::get('create', array( 'as' => 'projects.e_bidding.email_reminders.create', 'uses' => 'EBiddingEmailRemindersController@create' ));
        Route::post('create', array( 'uses' => 'EBiddingEmailRemindersController@store' ));
        //Route::get('edit/reminder/{emailId}', array( 'as' => 'projects.e_bidding.email_reminders.edit', 'uses' => 'EBiddingEmailRemindersController@edit' ));
        Route::put('edit/reminder/{emailId}', array( 'as' => 'projects.e_bidding.email_reminders.update', 'uses' => 'EBiddingEmailRemindersController@update' ));

        Route::group(array('prefix' => 'setup/{eBiddingId}'), function()
        {
            Route::group(array('prefix' => 'zones'), function()
            {
                Route::get('/', array( 'as' => 'projects.e_bidding.zones.index', 'uses' => 'EBiddingZoneController@index' ));
                Route::get('list', array( 'as' => 'projects.e_bidding.zones.list', 'uses' => 'EBiddingZoneController@getList' ));
                Route::get('create', array( 'as' => 'projects.e_bidding.zones.create', 'uses' => 'EBiddingZoneController@create' ));
                Route::post('store', array( 'as' => 'projects.e_bidding.zones.store', 'uses' => 'EBiddingZoneController@store' ));
                Route::post('clone/{zoneId}', array( 'as' => 'projects.e_bidding.zones.clone', 'uses' => 'EBiddingZoneController@clone' ));
                Route::get('edit/{zoneId}', array( 'as' => 'projects.e_bidding.zones.edit', 'uses' => 'EBiddingZoneController@edit' ));
                Route::post('update/{zoneId}', array( 'as' => 'projects.e_bidding.zones.update', 'uses' => 'EBiddingZoneController@update' ));
                Route::delete('delete/{zoneId}', array( 'as' => 'projects.e_bidding.zones.delete', 'uses' => 'EBiddingZoneController@destroy' ));
            });
        });
    });
});