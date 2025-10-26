<?php namespace PCK\ProjectDetails;

use Carbon\Carbon;
use PCK\Base\TimestampFormatterTrait;
use Illuminate\Database\Eloquent\Model;

class PAM2006ProjectDetail extends Model {

	use TimestampFormatterTrait;

	const DLP_PERIOD_UNIT_MONTH = 1;
	const DLP_PERIOD_UNIT_WEEK = 2;
	const DLP_PERIOD_UNIT_DAY = 4;

	protected $fillable = array(
		'commencement_date',
		'completion_date',
		'contract_sum',
		'liquidate_damages',
		'amount_performance_bond',
		'interim_claim_interval',
		'period_of_honouring_certificate',
		'min_days_to_comply_with_ai',
		'deadline_submitting_notice_of_intention_claim_eot',
		'deadline_submitting_final_claim_eot',
		'deadline_architect_request_info_from_contractor_eot_claim',
		'deadline_architect_decide_on_contractor_eot_claim',
		'deadline_submitting_note_of_intention_claim_l_and_e',
		'deadline_submitting_final_claim_l_and_e',
		'deadline_submitting_note_of_intention_claim_ae',
		'deadline_submitting_final_claim_ae',
		'percentage_of_certified_value_retained',
		'limit_retention_fund',
		'percentage_value_of_materials_and_goods_included_in_certificate',
		'period_of_architect_issue_interim_certificate',
		'pre_defined_location_code_id',
		'cpc_date',
		'extension_of_time_date',
		'defect_liability_period',
		'defect_liability_period_unit',
		'certificate_of_making_good_defect_date',
		'cnc_date',
		'performance_bond_validity_date',
		'insurance_policy_coverage_date'
	);

	protected $table = 'pam_2006_project_details';

	public function project()
	{
		return $this->belongsTo('PCK\Projects\Project');
	}

	public function preDefinedLocationCode()
	{
		return $this->belongsTo('PCK\Buildspace\PreDefinedLocationCode','pre_defined_location_code_id');
	}

	public function getCommencementDateAttribute($value)
	{
		return Carbon::parse($value)->format(\Config::get('dates.submission_date_formatting'));
	}

	public function getCompletionDateAttribute($value)
	{
		return Carbon::parse($value)->format(\Config::get('dates.submission_date_formatting'));
	}

	public function getDefectLiabilityPeriodUnitText()
	{
		switch($this->defect_liability_period_unit)
		{
			case self::DLP_PERIOD_UNIT_MONTH:
				return trans('tenders.months');
			case self::DLP_PERIOD_UNIT_WEEK:
				return trans('tenders.weeks');
			case self::DLP_PERIOD_UNIT_DAY:
				return trans('projects.days');
			default:
				throw new \Exception('Invalid defect_liability_period_unit');
		}
	}
}