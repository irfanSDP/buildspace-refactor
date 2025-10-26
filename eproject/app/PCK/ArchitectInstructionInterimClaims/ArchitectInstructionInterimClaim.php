<?php namespace PCK\ArchitectInstructionInterimClaims; 

use PCK\Base\ModuleAttachmentTrait;
use PCK\Base\TimestampFormatterTrait;
use Illuminate\Database\Eloquent\Model;

class ArchitectInstructionInterimClaim extends Model {

	use TimestampFormatterTrait, ModuleAttachmentTrait;

	public function architectInstruction()
	{
		return $this->belongsTo('PCK\ArchitectInstructions\ArchitectInstruction');
	}

	public function interimClaim()
	{
		return $this->belongsTo('PCK\InterimClaims\InterimClaim');
	}

	public function createdBy()
	{
		return $this->belongsTo('PCK\Users\User', 'created_by');
	}

}