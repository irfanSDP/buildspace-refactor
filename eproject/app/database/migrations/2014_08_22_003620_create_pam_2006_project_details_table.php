<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreatePam2006ProjectDetailsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('pam_2006_project_details', function (Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('project_id')->index();
			$table->date('commencement_date');
			$table->date('completion_date');
			$table->decimal('contract_sum', 19, 2);
			$table->decimal('liquidate_damages', 19, 2);
			$table->decimal('amount_performance_bond', 19, 2);
			$table->tinyInteger('interim_claim_interval', false, true);
			$table->tinyInteger('period_of_honouring_certificate', false, true);
			$table->tinyInteger('min_days_to_comply_with_ai', false, true);
			$table->tinyInteger('deadline_submitting_notice_of_intention_claim_eot', false, true);
			$table->tinyInteger('deadline_submitting_final_claim_eot', false, true);
			$table->tinyInteger('deadline_architect_request_info_from_contractor_eot_claim', false, true);
			$table->tinyInteger('deadline_architect_decide_on_contractor_eot_claim', false, true);
			$table->tinyInteger('deadline_submitting_note_of_intention_claim_l_and_e', false, true);
			$table->tinyInteger('deadline_submitting_final_claim_l_and_e', false, true);
			$table->tinyInteger('deadline_submitting_note_of_intention_claim_ae', false, true);
			$table->tinyInteger('deadline_submitting_final_claim_ae', false, true);
			$table->tinyInteger('percentage_of_certified_value_retained', false, true);
			$table->tinyInteger('limit_retention_fund', false, true);
			$table->tinyInteger('percentage_value_of_materials_and_goods_included_in_certificate', false, true);
			$table->timestamps();

			$table->foreign('project_id')->references('id')->on('projects');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('pam_2006_project_details');
	}

}