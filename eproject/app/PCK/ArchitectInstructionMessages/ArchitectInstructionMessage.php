<?php namespace PCK\ArchitectInstructionMessages; 

use PCK\Base\ModuleAttachmentTrait;
use PCK\Base\TimestampFormatterTrait;
use Illuminate\Database\Eloquent\Model;

class ArchitectInstructionMessage extends Model {

	use TimestampFormatterTrait, ModuleAttachmentTrait;

	public function architectInstruction()
	{
		return $this->belongsTo('PCK\ArchitectInstructions\ArchitectInstruction');
	}

	public function createdBy()
	{
		return $this->belongsTo('PCK\Users\User', 'created_by');
	}

    public function attachedClauses()
    {
        return $this->morphMany('PCK\ClauseItems\AttachedClauseItem', 'attachable')->orderBy('priority', 'asc');
    }

}