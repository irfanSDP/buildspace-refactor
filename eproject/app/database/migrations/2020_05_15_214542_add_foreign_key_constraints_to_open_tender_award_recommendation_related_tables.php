<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use PCK\Helpers\DBHelper;

class AddForeignKeyConstraintsToOpenTenderAwardRecommendationRelatedTables extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('open_tender_award_recommendation', function(Blueprint $table)
		{
			DB::statement('CREATE INDEX IF NOT EXISTS ot_ar_tender_id_idx ON open_tender_award_recommendation (tender_id);');
			
			if(!DBHelper::constraintExists('open_tender_award_recommendation', 'ot_ar_tender_id_fk', DBHelper::CONSTRAINT_TYPE_FOREIGN))
			{
				DB::statement('ALTER TABLE open_tender_award_recommendation ADD CONSTRAINT ot_ar_tender_id_fk FOREIGN KEY (tender_id) REFERENCES tenders(id) ON DELETE CASCADE;');
			}
		});

		Schema::table('open_tender_award_recommendation_bill_details', function(Blueprint $table)
		{
			DB::statement('CREATE INDEX IF NOT EXISTS ot_ar_bill_details_tender_id_idx ON open_tender_award_recommendation_bill_details (tender_id);');

			if(!DBHelper::constraintExists('open_tender_award_recommendation_bill_details', 'ot_ar_bill_details_tender_id_fk', DBHelper::CONSTRAINT_TYPE_FOREIGN))
			{
				DB::statement('ALTER TABLE open_tender_award_recommendation_bill_details ADD CONSTRAINT ot_ar_bill_details_tender_id_fk FOREIGN KEY (tender_id) REFERENCES tenders(id) ON DELETE CASCADE;');
			}

		});

		Schema::table('open_tender_award_recommendation_files', function(Blueprint $table)
		{
			DB::statement('CREATE INDEX IF NOT EXISTS ot_ar_files_tender_id_idx ON open_tender_award_recommendation_files (tender_id);');
			DB::statement('CREATE INDEX IF NOT EXISTS ot_ar_files_cabinet_file_id_idx ON open_tender_award_recommendation_files (cabinet_file_id);');

			if(!DBHelper::constraintExists('open_tender_award_recommendation_files', 'ot_ar_files_tender_id_fk', DBHelper::CONSTRAINT_TYPE_FOREIGN))
			{
				DB::statement('ALTER TABLE open_tender_award_recommendation_files ADD CONSTRAINT ot_ar_files_tender_id_fk FOREIGN KEY (tender_id) REFERENCES tenders(id) ON DELETE CASCADE;');
			}

			if(!DBHelper::constraintExists('open_tender_award_recommendation_files', 'ot_ar_files_cabinet_file_id_fk', DBHelper::CONSTRAINT_TYPE_FOREIGN))
			{
				DB::statement('ALTER TABLE open_tender_award_recommendation_files ADD CONSTRAINT ot_ar_files_cabinet_file_id_fk FOREIGN KEY (cabinet_file_id) REFERENCES uploads(id) ON DELETE CASCADE;');
			}
		});

		Schema::table('open_tender_award_recommendation_tender_summary', function(Blueprint $table)
		{
			DB::statement('CREATE INDEX IF NOT EXISTS ot_ar_tender_summary_tender_id_idx ON open_tender_award_recommendation_tender_summary (tender_id);');

			if(!DBHelper::constraintExists('open_tender_award_recommendation_tender_summary', 'ot_ar_tender_summary_tender_id_fk', DBHelper::CONSTRAINT_TYPE_FOREIGN))
			{
				DB::statement('ALTER TABLE open_tender_award_recommendation_tender_summary ADD CONSTRAINT ot_ar_tender_summary_tender_id_fk FOREIGN KEY (tender_id) REFERENCES tenders(id) ON DELETE CASCADE;');
			}
		});

		Schema::table('open_tender_award_recommendation_report_edit_logs', function(Blueprint $table)
		{
			DB::statement('CREATE INDEX IF NOT EXISTS ot_ar_report_edit_logs_ot_ar_id_idx ON open_tender_award_recommendation_report_edit_logs (open_tender_award_recommendation_id);');
			DB::statement('CREATE INDEX IF NOT EXISTS ot_ar_report_edit_logs_user_id_idx ON open_tender_award_recommendation_report_edit_logs (user_id);');

			if(!DBHelper::constraintExists('open_tender_award_recommendation_report_edit_logs', 'ot_ar_report_edit_logs_ot_ar_id_fk', DBHelper::CONSTRAINT_TYPE_FOREIGN))
			{
				DB::statement('ALTER TABLE open_tender_award_recommendation_report_edit_logs ADD CONSTRAINT ot_ar_report_edit_logs_ot_ar_id_fk FOREIGN KEY (open_tender_award_recommendation_id) REFERENCES open_tender_award_recommendation(id) ON DELETE CASCADE;');
			}

			if(!DBHelper::constraintExists('open_tender_award_recommendation_report_edit_logs', 'ot_ar_report_edit_logs_user_id_fk', DBHelper::CONSTRAINT_TYPE_FOREIGN))
			{
				DB::statement('ALTER TABLE open_tender_award_recommendation_report_edit_logs ADD CONSTRAINT ot_ar_report_edit_logs_user_id_fk FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE;');
			}
		});

		Schema::table('open_tender_award_recommendation_tender_analysis_table_edit_log', function(Blueprint $table)
		{
			DB::statement('CREATE INDEX IF NOT EXISTS ot_ar_tender_analysis_table_edit_log_tender_id_idx ON open_tender_award_recommendation_tender_analysis_table_edit_log (tender_id);');
			DB::statement('CREATE INDEX IF NOT EXISTS ot_ar_tender_analysis_table_edit_log_user_id_idx ON open_tender_award_recommendation_tender_analysis_table_edit_log (user_id);');

			if(!DBHelper::constraintExists('open_tender_award_recommendation_tender_analysis_table_edit_log', 'ot_ar_tender_analysis_table_edit_log_tender_id_fk', DBHelper::CONSTRAINT_TYPE_FOREIGN))
			{
				DB::statement('ALTER TABLE open_tender_award_recommendation_tender_analysis_table_edit_log ADD CONSTRAINT ot_ar_tender_analysis_table_edit_log_tender_id_fk FOREIGN KEY (tender_id) REFERENCES tenders(id) ON DELETE CASCADE;');
			}

			if(!DBHelper::constraintExists('open_tender_award_recommendation_tender_analysis_table_edit_log', 'ot_ar_tender_analysis_table_edit_log_user_id_fk', DBHelper::CONSTRAINT_TYPE_FOREIGN))
			{
				DB::statement('ALTER TABLE open_tender_award_recommendation_tender_analysis_table_edit_log ADD CONSTRAINT ot_ar_tender_analysis_table_edit_log_user_id_fk FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE;');
			}
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 */
	public function down()
	{
		Schema::table('open_tender_award_recommendation', function(Blueprint $table)
		{
			$indexes = Schema::getConnection()->getDoctrineSchemaManager()->listTableIndexes('open_tender_award_recommendation');

			if(array_key_exists('ot_ar_tender_id_idx', $indexes))
			{
				$table->dropIndex('ot_ar_tender_id_idx');
			}

			if(DBHelper::constraintExists('open_tender_award_recommendation', 'ot_ar_tender_id_fk', DBHelper::CONSTRAINT_TYPE_FOREIGN))
			{
				$table->dropForeign('ot_ar_tender_id_fk');
			}

		});

		Schema::table('open_tender_award_recommendation_bill_details', function(Blueprint $table)
		{
			$indexes = Schema::getConnection()->getDoctrineSchemaManager()->listTableIndexes('open_tender_award_recommendation_bill_details');

			if(array_key_exists('ot_ar_bill_details_tender_id_idx', $indexes))
			{
				$table->dropIndex('ot_ar_bill_details_tender_id_idx');
			}

			if(DBHelper::constraintExists('open_tender_award_recommendation_bill_details', 'ot_ar_bill_details_tender_id_fk', DBHelper::CONSTRAINT_TYPE_FOREIGN))
			{
				$table->dropForeign('ot_ar_bill_details_tender_id_fk');
			}
		});

		Schema::table('open_tender_award_recommendation_files', function(Blueprint $table)
		{
			$indexes = Schema::getConnection()->getDoctrineSchemaManager()->listTableIndexes('open_tender_award_recommendation_files');

			if(array_key_exists('ot_ar_files_tender_id_idx', $indexes))
			{
				$table->dropIndex('ot_ar_files_tender_id_idx');
			}

			if(array_key_exists('ot_ar_files_cabinet_file_id_idx', $indexes))
			{
				$table->dropIndex('ot_ar_files_cabinet_file_id_idx');
			}

			if(DBHelper::constraintExists('open_tender_award_recommendation_files', 'ot_ar_files_tender_id_fk', DBHelper::CONSTRAINT_TYPE_FOREIGN))
			{
				$table->dropForeign('ot_ar_files_tender_id_fk');
			}

			if(DBHelper::constraintExists('open_tender_award_recommendation_files', 'ot_ar_files_cabinet_file_id_fk', DBHelper::CONSTRAINT_TYPE_FOREIGN))
			{
				$table->dropForeign('ot_ar_files_cabinet_file_id_fk');
			}
		});

		Schema::table('open_tender_award_recommendation_tender_summary', function(Blueprint $table)
		{
			$indexes = Schema::getConnection()->getDoctrineSchemaManager()->listTableIndexes('open_tender_award_recommendation_tender_summary');

			if(array_key_exists('ot_ar_tender_summary_tender_id_idx', $indexes))
			{
				$table->dropIndex('ot_ar_tender_summary_tender_id_idx');
			}

			if(DBHelper::constraintExists('open_tender_award_recommendation_tender_summary', 'ot_ar_tender_summary_tender_id_fk', DBHelper::CONSTRAINT_TYPE_FOREIGN))
			{
				$table->dropForeign('ot_ar_tender_summary_tender_id_fk');
			}
		});

		Schema::table('open_tender_award_recommendation_report_edit_logs', function(Blueprint $table)
		{
			$indexes = Schema::getConnection()->getDoctrineSchemaManager()->listTableIndexes('open_tender_award_recommendation_report_edit_logs');

			if(array_key_exists('ot_ar_report_edit_logs_ot_ar_id_idx', $indexes))
			{
				$table->dropIndex('ot_ar_report_edit_logs_ot_ar_id_idx');
			}

			if(array_key_exists('ot_ar_report_edit_logs_user_id_idx', $indexes))
			{
				$table->dropIndex('ot_ar_report_edit_logs_user_id_idx');
			}

			if(DBHelper::constraintExists('open_tender_award_recommendation_report_edit_logs', 'ot_ar_report_edit_logs_ot_ar_id_fk', DBHelper::CONSTRAINT_TYPE_FOREIGN))
			{
				$table->dropForeign('ot_ar_report_edit_logs_ot_ar_id_fk');
			}

			if(DBHelper::constraintExists('open_tender_award_recommendation_report_edit_logs', 'ot_ar_report_edit_logs_user_id_fk', DBHelper::CONSTRAINT_TYPE_FOREIGN))
			{
				$table->dropForeign('ot_ar_report_edit_logs_user_id_fk');
			}
		});

		Schema::table('open_tender_award_recommendation_tender_analysis_table_edit_log', function(Blueprint $table)
		{
			$indexes = Schema::getConnection()->getDoctrineSchemaManager()->listTableIndexes('open_tender_award_recommendation_tender_analysis_table_edit_log');

			if(array_key_exists('ot_ar_tender_analysis_table_edit_log_tender_id_idx', $indexes))
			{
				$table->dropIndex('ot_ar_tender_analysis_table_edit_log_tender_id_idx');
			}

			if(array_key_exists('ot_ar_tender_analysis_table_edit_log_user_id_idx', $indexes))
			{
				$table->dropIndex('ot_ar_tender_analysis_table_edit_log_user_id_idx');
			}

			if(DBHelper::constraintExists('open_tender_award_recommendation_tender_analysis_table_edit_log', 'ot_ar_tender_analysis_table_edit_log_tender_id_fk', DBHelper::CONSTRAINT_TYPE_FOREIGN))
			{
				$table->dropForeign('ot_ar_tender_analysis_table_edit_log_tender_id_fk');
			}

			if(DBHelper::constraintExists('open_tender_award_recommendation_tender_analysis_table_edit_log', 'ot_ar_tender_analysis_table_edit_log_user_id_fk', DBHelper::CONSTRAINT_TYPE_FOREIGN))
			{
				$table->dropForeign('ot_ar_tender_analysis_table_edit_log_user_id_fk');
			}
		});
	}
}