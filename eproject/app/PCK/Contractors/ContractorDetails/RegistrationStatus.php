<?php namespace PCK\Contractors\ContractorDetails;

use Illuminate\Database\Eloquent\Model;

class RegistrationStatus extends Model {

    const UNSPECIFIED_RECORD_NAME = 'Unspecified';

	protected $table = 'contractor_registration_statuses';

	protected $fillable = [
		'name'
	];

	public function contractors()
	{
		return $this->hasMany('PCK\Contractors\Contractor');
	}

}