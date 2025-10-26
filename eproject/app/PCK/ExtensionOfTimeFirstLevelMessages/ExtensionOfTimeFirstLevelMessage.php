<?php namespace PCK\ExtensionOfTimeFirstLevelMessages;

use PCK\Base\ModuleAttachmentTrait;
use PCK\Base\TimestampFormatterTrait;
use Illuminate\Database\Eloquent\Model;

class ExtensionOfTimeFirstLevelMessage extends Model {

	use TimestampFormatterTrait, ModuleAttachmentTrait;

	protected $table = 'eot_first_level_messages';

	public function extensionOfTime()
	{
		return $this->belongsTo('PCK\ExtensionOfTimes\ExtensionOfTime');
	}

	public function createdBy()
	{
		return $this->belongsTo('PCK\Users\User', 'created_by');
	}

}