<?php namespace PCK\Forms;

use Laracasts\Validation\FormValidator;

class ProjectForm extends FormValidator {

	/**
	 * Validation rules for creating or updating Project
	 *
	 * @var array
	 */
	protected $rules = [
		'title'                                                           => 'required',
		'reference'                                                       => 'required',
		'address'                                                         => 'required',
		'description'                                                     => 'required',
		'commencement_date'                                               => 'required|date',
		'completion_date'                                                 => 'required|date',
		'contract_sum'                                                    => 'required|numeric',
		'liquidate_damages'                                               => 'required|numeric',
		'amount_performance_bond'                                         => 'required|numeric',
		'interim_claim_interval'                                          => 'required|integer',
		'period_of_honouring_certificate'                                 => 'required|integer',
		'min_days_to_comply_with_ai'                                      => 'required|integer',
		'deadline_submitting_notice_of_intention_claim_eot'               => 'required|integer',
		'deadline_submitting_final_claim_eot'                             => 'required|integer',
		'deadline_architect_request_info_from_contractor_eot_claim'       => 'required|integer',
		'deadline_architect_decide_on_contractor_eot_claim'               => 'required|integer',
		'deadline_submitting_note_of_intention_claim_l_and_e'             => 'required|integer',
		'deadline_submitting_final_claim_l_and_e'                         => 'required|integer',
		'deadline_submitting_note_of_intention_claim_ae'                  => 'required|integer',
		'deadline_submitting_final_claim_ae'                              => 'required|integer',
		'percentage_of_certified_value_retained'                          => 'required|integer|between:0,100',
		'limit_retention_fund'                                            => 'required|integer|between:0,100',
		'percentage_value_of_materials_and_goods_included_in_certificate' => 'required|integer|between:0,100',
	];

}