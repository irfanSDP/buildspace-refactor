<?php namespace PCK\ProjectContractGroupTenderDocumentPermissions;

use Illuminate\Database\Eloquent\Model;

class ProjectContractGroupTenderDocumentPermission extends Model {

	protected $with = array( 'contractGroup' );

	public function project()
	{
		return $this->belongsTo('PCK\Projects\Project');
	}

	public function contractGroup()
	{
		return $this->belongsTo('PCK\ContractGroups\ContractGroup');
	}

}