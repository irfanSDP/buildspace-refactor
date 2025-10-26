<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AlterTablesChangeDateToTimestamp extends Migration {

	private $tableColumnArray = [
		'additional_expenses'								=> 'commencement_date_of_event',
		'ae_contractor_confirm_delays'						=> [
			'date_on_which_delay_is_over',
			'deadline_to_submit_final_claim',
		],
		'ae_second_level_messages' 							=> [
			'requested_new_deadline',
			'grant_different_deadline',
		],
		'ae_third_level_messages'							=> 'deadline_to_comply_with',
		'ai_third_level_messages'							=> 'compliance_date',
		'architect_instructions'							=> 'deadline_to_comply',
		'conversations'										=> 'deadline_to_reply',
		'daily_labour_reports'								=> 'date',
		'engineer_instructions'								=> 'deadline_to_comply_with',
		'eot_contractor_confirm_delays'						=> [
			'date_on_which_delay_is_over',
			'deadline_to_submit_final_eot_claim',
		],
		'eot_second_level_messages'							=> [
			'requested_new_deadline',
			'grant_different_deadline',
		],
		'eot_third_level_messages'							=> 'deadline_to_comply_with',
		'extension_of_times'								=> 'commencement_date_of_event',
		'indonesia_civil_contract_architect_instructions'	=> 'deadline_to_comply',
		'indonesia_civil_contract_early_warnings'			=> 'commencement_date',
		'indonesia_civil_contract_information'				=> [
			'commencement_date',
			'completion_date',
		],
		'interim_claim_informations'						=> [
			'date',
			'date_of_certificate',
		],
		'interim_claims'									=> 'issue_certificate_deadline',
		'loe_contractor_confirm_delays'						=> [
			'date_on_which_delay_is_over',
			'deadline_to_submit_final_claim',
		],
		'loe_second_level_messages'							=> [
			'requested_new_deadline',
			'grant_different_deadline',
		],
		'loe_third_level_messages'							=> 'deadline_to_comply_with',
		'loss_or_and_expenses'								=> 'commencement_date_of_event',
		'pam_2006_project_details'							=> [
			'commencement_date',
			'completion_date',
		],
		'projects'											=> 'completion_date',
		'site_management_mcar_form_responses'				=> [
			'reinspection_date',
			'commitment_date',
		],
		'technical_evaluations'								=> 'targeted_date_of_award',
		'weather_records'									=> 'date',
	];

	private function runTask($dataType)
	{
		foreach($this->tableColumnArray as $table => $column)
		{
			if(is_array($column))
			{
				foreach($column as $col) {
					DB::statement("ALTER TABLE {$table} ALTER COLUMN {$col} TYPE {$dataType} USING {$col}::{$dataType}");
				}

				continue;
			}

			DB::statement("ALTER TABLE {$table} ALTER COLUMN {$column} TYPE {$dataType} USING {$column}::{$dataType}");
		}
	}

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		$this->runTask('TIMESTAMP');
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		$this->runTask('DATE');
	}

}
