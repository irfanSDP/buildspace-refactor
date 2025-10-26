<?php namespace PCK\ConversationReplyMessages;

use PCK\Base\ModuleAttachmentTrait;
use PCK\Base\TimestampFormatterTrait;
use Illuminate\Database\Eloquent\Model;

class ConversationReplyMessage extends Model {

	use TimestampFormatterTrait, ModuleAttachmentTrait;

	protected $touches = array('conversation');

	public function conversation()
	{
		return $this->belongsTo('PCK\Conversations\Conversation');
	}

	public function createdBy()
	{
		return $this->belongsTo('PCK\Users\User', 'created_by');
	}

}