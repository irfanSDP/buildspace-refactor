<?php 

 // Site Management Instruction to Contractor Module Routes
 Route::group(array( 'prefix' => 'instruction-to-contractor','before' => 'siteManagement.hasInstructionToContractorPermission'), function()
 {
     Route::get('/', array( 'as' => 'instruction-to-contractor.index', 'uses' => 'InstructionToContractorController@index' ));
     Route::get('create', array( 'as' => 'instruction-to-contractor.create', 'uses' => 'InstructionToContractorController@create' ));
     Route::post('create', array( 'as' => 'instruction-to-contractor.store', 'uses' => 'InstructionToContractorController@store' ));
     Route::get('{id}/show', array( 'as' => 'instruction-to-contractor.show', 'uses' => 'InstructionToContractorController@show' ));
     Route::get('{id}/edit', array( 'as' => 'instruction-to-contractor.edit', 'uses' => 'InstructionToContractorController@edit' ));
     Route::put('{id}/update', array( 'as' => 'instruction-to-contractor.update', 'uses' => 'InstructionToContractorController@update' ));
     Route::delete('{id}/delete', array( 'as' => 'instruction-to-contractor.delete', 'uses' => 'InstructionToContractorController@destroy' ));
     Route::get('{id}/getAttachmentsList', ['as' => 'instruction-to-contractor.attachements.get', 'uses' => 'InstructionToContractorController@getAttachmentsList']);
     Route::delete('{uploadedItemId}/attachmentsDelete/{id}', ['as' => 'instruction-to-contractor.attachements.delete', 'uses' => 'InstructionToContractorController@attachmentDelete']);
 });