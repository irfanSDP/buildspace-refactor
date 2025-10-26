<?php


Route::group(array( 'before' => 'moduleAccess:' . \PCK\ModulePermission\ModulePermission::MODULE_ID_DEFECTS ,'prefix' => 'defect-categories' ), function()
{
    Route::get('/',array( 'as' => 'defect-categories', 'uses' => 'DefectCategoryController@index' ));
    Route::get('create', array('as' => 'defect-categories.create', 'uses' =>'DefectCategoryController@create')); 
    Route::post('create',array( 'as' => 'defect-categories.store', 'uses' => 'DefectCategoryController@store' ));
    Route::get('edit/{id}', array('as' => 'defect-categories.edit', 'uses' =>'DefectCategoryController@edit')); 
    Route::put('update/{id}',array( 'as' => 'defect-categories.update', 'uses' => 'DefectCategoryController@update' ));
    Route::delete('delete/{id}', array('as' => 'defect-categories.delete', 'uses' =>'DefectCategoryController@destroy')); 
    
    Route::group(array( 'prefix' => '{categoryId}/defects' ), function()
    {
        Route::get('/',array( 'as' => 'defect-categories.defects', 'uses' => 'DefectController@index' ));
        Route::get('create', array('as' => 'defect-categories.defects.create', 'uses' =>'DefectController@create')); 
        Route::post('create',array( 'as' => 'defect-categories.defects.store', 'uses' => 'DefectController@store' ));
        Route::get('edit/{id}', array('as' => 'defect-categories.defects.edit', 'uses' =>'DefectController@edit')); 
        Route::put('update/{id}',array( 'as' => 'defect-categories.defects.update', 'uses' => 'DefectController@update' ));
        Route::delete('delete/{id}', array('as' => 'defect-categories.defects.delete', 'uses' =>'DefectController@destroy')); 
    });

    // Mapping of Defect Category and Trade
    Route::group(array( 'prefix' => 'defect-trade-mapping' ), function()
    {
        Route::get('/',array( 'as' => 'projects.defect-trade-mapping.index', 'uses' => 'DefectCategoryTradeMappingController@index' ));
        Route::post('store',array( 'as' => 'projects.defect-trade-mapping.store', 'uses' => 'DefectCategoryTradeMappingController@store' ));
    });
});