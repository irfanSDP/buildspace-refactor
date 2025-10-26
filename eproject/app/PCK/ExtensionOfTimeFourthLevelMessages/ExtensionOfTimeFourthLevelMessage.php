<?php namespace PCK\ExtensionOfTimeFourthLevelMessages;

use PCK\Base\ModuleAttachmentTrait;
use PCK\Base\TimestampFormatterTrait;
use Illuminate\Database\Eloquent\Model;
use PCK\ExtensionOfTimes\ExtensionOfTime;

class ExtensionOfTimeFourthLevelMessage extends Model implements DecisionTypes {

	use TimestampFormatterTrait, ModuleAttachmentTrait;

	protected $table = 'eot_fourth_level_messages';

	protected static function boot()
	{
		parent::boot();

		static::saved(function ($model)
		{
			$eot               = $model->extensionOfTime;
			$eot->days_granted = 0;
			$status            = ExtensionOfTime::PENDING;

			if ( $model->decision )
			{
				switch ($model->decision)
				{
					case ExtensionOfTimeFourthLevelMessage::GRANT_DIFF_DEADLINE:
					case ExtensionOfTimeFourthLevelMessage::EXTEND_DEADLINE:
						$eot->days_granted = $model->grant_different_days;
						$status            = ExtensionOfTime::GRANTED;
						break;

					case ExtensionOfTimeFourthLevelMessage::REJECT_DEADLINE:
						$status = ExtensionOfTime::REJECTED;
						break;

					default:
						throw new \InvalidArgumentException('Invalid decision');
				}
			}

			$eot->status = $status;

			$eot->save();
		});
	}

	public function extensionOfTime()
	{
		return $this->belongsTo('PCK\ExtensionOfTimes\ExtensionOfTime');
	}

	public function createdBy()
	{
		return $this->belongsTo('PCK\Users\User', 'created_by');
	}

}