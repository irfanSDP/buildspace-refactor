<?php namespace PCK\Forms;

use Laracasts\Validation\FormValidator;
use PCK\Contracts\Contract;
use PCK\Projects\Project;

class ProjectFormPostContract extends CustomFormValidator {

    protected $project;

    protected $rules = [];

    protected $pam2006Rules = [
        'commencement_date'                                               => 'required|date',
        'completion_date'                                                 => 'required|date',
        'contract_sum'                                                    => 'numeric',
        'liquidate_damages'                                               => 'numeric',
        'amount_performance_bond'                                         => 'numeric',
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
        'percentage_of_certified_value_retained'                          => 'required|numeric|between:0,100',
        'limit_retention_fund'                                            => 'required|numeric|between:0,100',
        'percentage_value_of_materials_and_goods_included_in_certificate' => 'required|numeric|between:0,100',
        'cpc_date'                                                        => 'date',
        'extension_of_time_date'                                          => 'date',
        'certificate_of_making_good_defect_date'                          => 'date',
        'cnc_date'                                                        => 'date',
        'performance_bond_validity_date'                                  => 'date',
        'insurance_policy_coverage_date'                                  => 'date',
        'defect_liability_period'                                         => 'required|integer',
        'defect_liability_period_unit'                                    => 'required|integer'
    ];

    protected $indonesiaCivilContractRules = [
        'commencement_date' => 'required|date',
        'completion_date'   => 'required|date',
        'contract_sum'      => 'numeric',
    ];

    public function setProject(Project $project)
    {
        $this->project = $project;
    }

    protected function setRules($formData)
    {
        switch($this->project->contract->type)
        {
            case Contract::TYPE_PAM2006:
                $this->rules = $this->pam2006Rules;
                break;
            case Contract::TYPE_INDONESIA_CIVIL_CONTRACT:
                $this->rules = $this->indonesiaCivilContractRules;
                break;
            default:
                throw new \Exception('Invalid contract type');
        }
    }
}