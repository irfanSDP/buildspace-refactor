<?php namespace PCK\ContractGroupTenderDocumentPermissionLogs;

use Illuminate\Database\Eloquent\Model;

class ContractGroupTenderDocumentPermissionLog extends Model {

	protected $table = 'contract_group_tender_document_permission_logs';

	protected $with = array( 'contractGroup' );

	public function assignCompanyLog()
	{
		return $this->belongsTo('PCK\AssignCompaniesLogs\AssignCompaniesLog');
	}

	public function contractGroup()
	{
		return $this->belongsTo('PCK\ContractGroups\ContractGroup');
	}

}