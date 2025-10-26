<?php

class SkipProjectStageTest extends RollbackTestCase {

    /**
     * A basic functional test example.
     *
     * @return void
     */
    public function testBasicExample()
    {
        self::assertTrue(true);
    }

    public function testPushToPostContract()
    {
        $companyRepository  = \App::make('PCK\Companies\CompanyRepository');
        $selectedContractor = $companyRepository->findWithRoleType(\PCK\ContractGroups\Types\Role::CONTRACTOR)->first();
        $trade              = \PCK\Buildspace\PreDefinedLocationCode::where('level', '0')->first();

        $user = \PCK\Users\User::where('username', '=', 'sky@buildspace.my')->first();
        Auth::loginUsingId($user->id);

        if( ( ! $selectedContractor ) || ( ! $trade ) )
        {
            self::assertTrue(false);
        }

        $postContractData = array(
            'selectedContractorId'  => $selectedContractor->id,
            'postContractFormInput' => array(
                'contractor_id'                                                   => $selectedContractor->id,
                'trade'                                                           => $trade->id,
                'commencement_date'                                               => \Carbon\Carbon::now()->addDays(1)->format('Y-m-d'),
                'completion_date'                                                 => \Carbon\Carbon::now()->addDays(10)->format('Y-m-d'),
                'contract_sum'                                                    => rand(100, 10000000),
                'liquidate_damages'                                               => rand(100, 10000000),
                'amount_performance_bond'                                         => rand(100, 10000000),
                'interim_claim_interval'                                          => 1,
                'period_of_honouring_certificate'                                 => 21,
                'min_days_to_comply_with_ai'                                      => 7,
                'deadline_submitting_notice_of_intention_claim_eot'               => 28,
                'deadline_submitting_final_claim_eot'                             => 28,
                'deadline_architect_request_info_from_contractor_eot_claim'       => 28,
                'deadline_architect_decide_on_contractor_eot_claim'               => 6,
                'deadline_submitting_note_of_intention_claim_l_and_e'             => 28,
                'deadline_submitting_final_claim_l_and_e'                         => 28,
                'deadline_submitting_note_of_intention_claim_ae'                  => 28,
                'deadline_submitting_final_claim_ae'                              => 28,
                'percentage_of_certified_value_retained'                          => 10,
                'limit_retention_fund'                                            => 5,
                'percentage_value_of_materials_and_goods_included_in_certificate' => 100,
                'period_of_architect_issue_interim_certificate'                   => 21,
            ),
        );

        $allSuccess = true;

        // Test a random project from each stage.
        foreach(\PCK\Projects\Project::getStagesSequence() as $stageStatusId)
        {
            $project = \PCK\Projects\Project::has('latestTender')->where('status_id', '=', $stageStatusId)->first();

            if( ( ! $project ) || $project->stageSequenceCompare('>=', \PCK\Projects\Project::STATUS_TYPE_POST_CONTRACT) ) continue;

            $success = $project->skipToStage(\PCK\Projects\Project::STATUS_TYPE_POST_CONTRACT, $postContractData);

            $allSuccess = $allSuccess && $success;
        }

        self::assertTrue($allSuccess);
    }

}
