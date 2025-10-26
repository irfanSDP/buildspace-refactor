<?php

Route::group(array( 'prefix' => 'e-bidding' ), function()
{
    Route::group(array( 'prefix' => 'sessions', 'before' => 'eBidding.checkSessionListAccess' ), function()
    {
        Route::get('/', array( 'as' => 'e-bidding.sessions.index', 'uses' => 'EBiddingSessionController@index' ));
    });

    Route::group(array( 'prefix' => 'console/{eBiddingId}', 'before' => 'eBidding.checkConsoleAccess' ), function()
    {
        Route::get('/', array( 'as' => 'e-bidding.console.show', 'uses' => 'EBiddingSessionController@show' ));
        Route::get('bid-countdown', array( 'as' => 'e-bidding.console.bid-countdown', 'uses' => 'EBiddingSessionController@getCountdown' ));

        Route::post('bid', array( 'as' => 'e-bidding.console.bid', 'uses' => 'EBiddingSessionController@bid' ));
    });

    Route::group(array( 'prefix' => 'list/{eBiddingId}' ), function() {
        Route::get('bid-sessions', array('as' => 'e-bidding.list.sessions', 'uses' => 'EBiddingSessionController@getBidSessions', 'before' => 'eBidding.checkSessionListAccess'));
        Route::get('bid-rankings', array('as' => 'e-bidding.list.rankings', 'uses' => 'EBiddingSessionController@getBidRankings', 'before' => 'eBidding.checkRankingListAccess'));
        Route::get('bid-history', array('as' => 'e-bidding.list.bid-history', 'uses' => 'EBiddingSessionController@getBidHistory', 'before' => 'eBidding.checkBiddingHistoryAccess'));
        Route::get('bid-legend', array('as' => 'e-bidding.list.legend', 'uses' => 'EBiddingSessionController@getBidLegend'));
    });

    Route::group(array( 'prefix' => 'notification/{eBiddingId}', 'before' =>  'checkNotificationAccess'), function()
    {
        Route::post('send', array( 'as' => 'e-bidding.notify.email', 'uses' => 'EBiddingEmailNotificationController@notify' ));
    });

});
 