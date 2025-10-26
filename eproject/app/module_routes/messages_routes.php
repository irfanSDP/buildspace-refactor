<?php

Route::group(array( 'prefix' => 'messages' ), function ()
{
	Route::get('/', array( 'as' => 'messages', 'uses' => 'MessagesController@index' ));
	Route::get('foldersUnreadMessageCount', array( 'as' => 'messages.unreadMessageCount', 'uses' => 'MessagesController@foldersUnreadMessageCount' ));
	Route::get('show/{messageId}', array( 'as' => 'message.show', 'uses' => 'MessagesController@show' ));

	// only allow project editor to post new/edit messages
	Route::group(array( 'before' => 'isEditor' ), function ()
	{
		Route::get('create', array( 'as' => 'message.create', 'uses' => 'MessagesController@create' ));
		Route::post('create', array( 'uses' => 'MessagesController@store' ));

		Route::get('edit/{messageId}', array( 'as' => 'message.edit', 'uses' => 'MessagesController@edit' ));
		Route::put('edit/{messageId}', array( 'uses' => 'MessagesController@update' ));

		Route::post('replyMessage/{messageId}', array( 'as' => 'message.reply', 'uses' => 'MessagesController@replyMessage' ));

		Route::delete('delete/{messageId}', array( 'as' => 'message.delete', 'uses' => 'MessagesController@destroy' ));
	});
});