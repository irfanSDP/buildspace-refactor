<?php namespace PCK\InterimClaims;

use PCK\ContractGroups\Types\Role;

trait ReminderRelations {

	public function architectInstructionInterimClaims()
	{
		return $this->hasMany('PCK\ArchitectInstructionInterimClaims\ArchitectInstructionInterimClaim');
	}

	public function lossOrAndExpenseInterimClaims()
	{
		return $this->hasMany('PCK\LossOrAndExpenseInterimClaims\LossOrAndExpenseInterimClaim');
	}

	public function additionalExpenseInterimClaims()
	{
		return $this->hasMany('PCK\AdditionalExpenseInterimClaims\AdditionalExpenseInterimClaim');
	}

	public function architectClaimInformation()
	{
		return $this->hasOne('PCK\InterimClaimInformation\InterimClaimInformation')->where('type', '=', Role::INSTRUCTION_ISSUER);
	}

	public function contractorClaimInformation()
	{
		return $this->hasOne('PCK\InterimClaimInformation\InterimClaimInformation')->where('type', '=', Role::CONTRACTOR);
	}

	public function qsConsultantClaimInformation()
	{
		return $this->hasOne('PCK\InterimClaimInformation\InterimClaimInformation')->where('type', '=', Role::CLAIM_VERIFIER);
	}

}