<?php namespace PCK\ArchitectInstructionThirdLevelMessages; 

use Carbon\Carbon;
use PCK\Base\ModuleAttachmentTrait;
use PCK\Base\TimestampFormatterTrait;
use Illuminate\Database\Eloquent\Model;

class ArchitectInstructionThirdLevelMessage extends Model implements MessageTypes {

	use TimestampFormatterTrait, ModuleAttachmentTrait;

	protected $table = 'ai_third_level_messages';

	protected static function boot()
	{
		parent::boot();

		// will update AI's status if there is Compliance Status available
		static::saved(function($model)
		{
			if ( $model->compliance_status )
			{
				$ai         = $model->architectInstruction;
				$ai->status = $model->compliance_status;

				$ai->save();
			}
		});
	}

	public function architectInstruction()
	{
		return $this->belongsTo('PCK\ArchitectInstructions\ArchitectInstruction');
	}

	public function createdBy()
	{
		return $this->belongsTo('PCK\Users\User', 'created_by');
	}

	public function getComplianceDateAttribute($value)
	{
		return Carbon::parse($value)->format(\Config::get('dates.submission_date_formatting'));
	}

}