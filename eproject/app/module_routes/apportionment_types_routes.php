<?php

Route::group([ 'prefix' => 'apportionment_types' ], function()
{
    Route::group([ 'before' => 'superAdminAccessLevel' ], function()
    {
        Route::get('/', [ 'as' => 'apportionment.types.index', 'uses' => 'ApportionmentTypeController@index' ]);
        Route::get('/getApportionmentTypesTableData', [ 'as' => 'apportionment.types.table.data.get', 'uses' => 'ApportionmentTypeController@getApportionmentTypesTableData' ]);
        Route::post('/store', [ 'as' => 'apportionment.type.store', 'uses' => 'ApportionmentTypeController@store' ]);
        
        Route::group([ 'prefix' => '{apportionmentTypeId}' ], function() {
            Route::get('/editableCheck', [ 'as' => 'apportionment.editable.check', 'uses' => 'ApportionmentTypeController@editableCheck' ]);
            Route::post('/update', [ 'as' => 'apportionment.type.update', 'uses' => 'ApportionmentTypeController@update' ]);
            Route::post('/delete', [ 'as' => 'apportionment.type.delete', 'uses' => 'ApportionmentTypeController@destroy' ]);
        });
    });
});

