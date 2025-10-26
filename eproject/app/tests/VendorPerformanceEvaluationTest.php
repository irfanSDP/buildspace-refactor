<?php

use PCK\VendorWorkCategory\VendorWorkCategory;
use PCK\VendorPerformanceEvaluation\VendorPerformanceEvaluationCompanyForm;
use PCK\VendorPerformanceEvaluation\Cycle;
use PCK\VendorPerformanceEvaluation\CycleScore;
use PCK\VendorPerformanceEvaluation\EvaluationScore;
use PCK\VendorPerformanceEvaluation\VendorPerformanceEvaluation;
use PCK\Companies\Company;
use PCK\Projects\Project;
use PCK\WeightedNode\WeightedNode;
use PCK\WeightedNode\WeightedNodeScore;
use Faker\Factory as Faker;

class VendorPerformanceEvaluationTest extends RollbackTestCase {

    /**
     * A basic functional test example.
     *
     * @return void
     */
    public function testScoreCalculationFromCompanyForm()
    {
        $vwc1 = VendorWorkCategory::firstOrCreate([
            'code' => 'vwc1',
            'name' => 'vwc1',
        ]);
        $vwc2 = VendorWorkCategory::firstOrCreate([
            'code' => 'vwc2',
            'name' => 'vwc2',
        ]);
        $vwc3 = VendorWorkCategory::firstOrCreate([
            'code' => 'vwc3',
            'name' => 'vwc3',
        ]);
        $vwc4 = VendorWorkCategory::firstOrCreate([
            'code' => 'vwc4',
            'name' => 'vwc4',
        ]);
        $c1 = $this->getTestCompany();
        $c2 = $this->getTestCompany();
        $c3 = $this->getTestCompany();
        $c4 = $this->getTestCompany();
        $c5 = $this->getTestCompany();

        $now = Carbon\Carbon::now();
        $lastYear = Carbon\Carbon::now()->subYears(1);
        $nextYear = Carbon\Carbon::now()->addYears(1);

        $cycle = Cycle::create([
            'start_date' => $lastYear,
            'end_date' => $nextYear,
        ]);

        $this->createEvaluationWithCompanyForm($cycle, $vwc1, $c1, $c2, 80);
        $this->createEvaluationWithCompanyForm($cycle, $vwc1, $c1, $c2, 68);
        $this->createEvaluationWithCompanyForm($cycle, $vwc1, $c1, $c2, 71);
        $this->createEvaluationWithCompanyForm($cycle, $vwc1, $c1, $c2, 62);

        $this->createEvaluationWithCompanyForm($cycle, $vwc2, $c1, $c2, 70);

        $this->createEvaluationWithCompanyForm($cycle, $vwc3, $c1, $c2, 66);
        $this->createEvaluationWithCompanyForm($cycle, $vwc3, $c1, $c2, 73);
        $this->createEvaluationWithCompanyForm($cycle, $vwc3, $c1, $c2, 71);
        $this->createEvaluationWithCompanyForm($cycle, $vwc3, $c1, $c2, 76);

        $cycle = Cycle::find($cycle->id);

        $cycle->end_date = $now;
        $cycle->save();

        $vwc1Score = CycleScore::where('vendor_work_category_id', '=', $vwc1->id)
            ->where('company_id', '=', $c1->id)
            ->where('vendor_performance_evaluation_cycle_id', '=', $cycle->id)
            ->first();

        self::assertEquals(70, $vwc1Score->score);

        $vwc2Score = CycleScore::where('vendor_work_category_id', '=', $vwc2->id)
            ->where('company_id', '=', $c1->id)
            ->where('vendor_performance_evaluation_cycle_id', '=', $cycle->id)
            ->first();

        self::assertEquals(70, $vwc2Score->score);

        $vwc3Score = CycleScore::where('vendor_work_category_id', '=', $vwc3->id)
            ->where('company_id', '=', $c1->id)
            ->where('vendor_performance_evaluation_cycle_id', '=', $cycle->id)
            ->first();

        self::assertEquals(72, $vwc3Score->score);
    }

    public function createEvaluationWithCompanyForm($cycle, $vendorWorkCategory, $company, $evaluatorCompany, $formScore)
    {
        $eval = VendorPerformanceEvaluation::create([
            'vendor_performance_evaluation_cycle_id' => $cycle->id,
            'project_id' => Project::first()->id,
            'project_status_id' => VendorPerformanceEvaluation::PROJECT_STAGE_DESIGN,
            'person_in_charge_id' => 1,
            'start_date' => $cycle->start_date,
            'end_date' => $cycle->end_date,
            'status_id' => VendorPerformanceEvaluation::STATUS_IN_PROGRESS,
            'type' => VendorPerformanceEvaluation::TYPE_360,
        ]);

        $form = VendorPerformanceEvaluationCompanyForm::create([
            'vendor_performance_evaluation_id' => $eval->id,
            'company_id' => $company->id,
            'weighted_node_id' => $this->createWeightedNodes($formScore)->id,
            'evaluator_company_id' => $evaluatorCompany->id,
            'vendor_work_category_id' => $vendorWorkCategory->id,
        ]);

        $form->update(array('status_id' => VendorPerformanceEvaluationCompanyForm::STATUS_COMPLETED));
    }

    public function createWeightedNodes($formScore)
    {
        $node = WeightedNode::create([
            'weight' => 1,
        ]);

        $scoreMax = WeightedNodeScore::create([
            'node_id' => $node->id,
            'value' => 100,
        ]);
        $scoreSelected = WeightedNodeScore::create([
            'node_id' => $node->id,
            'value' => $formScore,
            'is_selected' => true,
        ]);

        return $node;
    }

    public function getTestCompany()
    {
        $faker = Faker::create();
        $timestamp = Carbon\Carbon::now();
        $companies = array();

        $referenceNo = str_random(50);

        $companies[] = array(
            'name'                       => $faker->company,
            'address'                    => $faker->address,
            'main_contact'               => $faker->name,
            'email'                      => $faker->companyEmail,
            'telephone_number'           => $faker->phoneNumber,
            'fax_number'                 => $faker->phoneNumber,
            'reference_no'               => $referenceNo,
            'reference_id'               => str_random(16),
            'contract_group_category_id' => \PCK\ContractGroupCategory\ContractGroupCategory::first()->id,
            'created_at'                 => $timestamp,
            'updated_at'                 => $timestamp,
        );

        Company::insert($companies);

        return Company::where('reference_no', '=', $referenceNo)->first();
    }

}
