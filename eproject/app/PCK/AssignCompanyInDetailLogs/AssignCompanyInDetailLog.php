<?php namespace PCK\AssignCompanyInDetailLogs;

use Illuminate\Database\Eloquent\Model;

class AssignCompanyInDetailLog extends Model {

	protected $table = 'assign_company_in_detail_logs';

	protected $with = array( 'contractGroup', 'company' );

	public function assignCompanyLog()
	{
		return $this->belongsTo('PCK\AssignCompaniesLogs\AssignCompaniesLog');
	}

	public function contractGroup()
	{
		return $this->belongsTo('PCK\ContractGroups\ContractGroup');
	}

	public function company()
	{
		return $this->belongsTo('PCK\Companies\Company');
	}

}