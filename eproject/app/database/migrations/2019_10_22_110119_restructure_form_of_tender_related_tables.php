<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use PCK\FormOfTender\FormOfTender;

class RestructureFormOfTenderRelatedTables extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		$this->seedFormOfTendersForAllTenders();
		$this->migrateFormOfTendersTable();
		$this->migrateFormOfTenderHeadersTable();
		$this->migrateFormOfTenderAddressesTable();
		$this->migrateFormOfTenderPrintSettingsTable();
		$this->migrateFormOfTenderClausesTable();
		$this->migrateFormOfTenderTenderAlternatives();
		$this->migrateFormOfTenderLogs();
		$this->migrateFormOfTenderTenderAlternativePositions();
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		$this->rollbackFormOfTenderTenderAlternativePositions();
		$this->rollbackFormOfTenderLogs();
		$this->rollbackFormOfTenderTenderAlternatives();
		$this->rollbackFormOfTenderClausesTable();
		$this->rollbackFormOfTenderPrintSettingsTable();
		$this->rollbackFormOfTenderAddressesTable();
		$this->rollbackFormOfTenderHeadersTable();
		$this->rollbackFormOfTendersTable();
	}

	// create a form_of_tender and all its components for all tenders
	public function seedFormOfTendersForAllTenders()
	{
		$seeder = new CreateFormOfTendersForAllTendersTableSeeder();
		$seeder->run();
	}

	public function migrateFormOfTendersTable()
	{
		Schema::table('form_of_tenders', function(Blueprint $table)
		{
			$table->boolean('is_template')->default(false);
			$table->string('name')->nullable();
		});

		// insert a row and set it as template (no such row exists previously)
		$formOfTendersTableTemplateSeeder = new FormOfTendersTableTemplateSeeder();
		$formOfTendersTableTemplateSeeder->run();
	}

	public function rollbackFormOfTendersTable()
	{
		Schema::table('form_of_tenders', function(Blueprint $table)
		{
			$table->dropColumn('is_template');
		});

		FormOfTender::whereNull('tender_id')->delete();
	}

	public function migrateFormOfTenderHeadersTable()
	{
		Schema::table('form_of_tender_headers', function(Blueprint $table)
		{
			$table->unsignedInteger('form_of_tender_id')->nullable();
		});

		$formOfTenderHeadersTableSeeder = new FormOfTenderHeadersTableSeeder();
		$formOfTenderHeadersTableSeeder->run();

		DB::statement('ALTER TABLE form_of_tender_headers ALTER COLUMN form_of_tender_id SET NOT NULL');

		Schema::table('form_of_tender_headers', function(Blueprint $table)
		{
			$table->index('form_of_tender_id');
			$table->foreign('form_of_tender_id')->references('id')->on('form_of_tenders')->onDelete('cascade');
			$table->dropColumn('tender_id');
			$table->dropColumn('is_template');
		});
	}

	public function rollbackFormOfTenderHeadersTable()
	{
		Schema::table('form_of_tender_headers', function(Blueprint $table)
		{
			$table->unsignedInteger('tender_id')->nullable();
			$table->boolean('is_template')->default(false);
			$table->index('tender_id');
		});

		$formOfTenderHeadersTableSeeder = new FormOfTenderHeadersTableSeeder();
		$formOfTenderHeadersTableSeeder->rollback();

		Schema::table('form_of_tender_headers', function(Blueprint $table)
		{
			$table->dropColumn('form_of_tender_id');
		});
	}

	public function migrateFormOfTenderAddressesTable()
	{
		Schema::table('form_of_tender_addresses', function(Blueprint $table)
		{
			$table->unsignedInteger('form_of_tender_id')->nullable();
		});

		$formOfTenderAddressesTableSeeder = new FormOfTenderAddressesTableSeeder();
		$formOfTenderAddressesTableSeeder->run();

		DB::statement('ALTER TABLE form_of_tender_addresses ALTER COLUMN form_of_tender_id SET NOT NULL');

		Schema::table('form_of_tender_addresses', function(Blueprint $table)
		{
			$table->index('form_of_tender_id');
			$table->foreign('form_of_tender_id')->references('id')->on('form_of_tenders')->onDelete('cascade');
			$table->dropColumn('tender_id');
			$table->dropColumn('is_template');
		});
	}

	public function rollbackFormOfTenderAddressesTable()
	{
		Schema::table('form_of_tender_addresses', function(Blueprint $table)
		{
			$table->unsignedInteger('tender_id')->nullable();
			$table->boolean('is_template')->default(false);
			$table->index('tender_id');
		});

		$formOfTenderAddressesTableSeeder = new FormOfTenderAddressesTableSeeder();
		$formOfTenderAddressesTableSeeder->rollback();

		Schema::table('form_of_tender_addresses', function(Blueprint $table)
		{
			$table->dropColumn('form_of_tender_id');
		});
	}

	public function migrateFormOfTenderPrintSettingsTable()
	{
		Schema::table('form_of_tender_print_settings', function(Blueprint $table)
		{
			$table->unsignedInteger('form_of_tender_id')->nullable();
		});

		$formOfTenderPrintSettingsTableSeeder = new FormOfTenderPrintSettingsTableSeeder();
		$formOfTenderPrintSettingsTableSeeder->run();

		DB::statement('ALTER TABLE form_of_tender_print_settings ALTER COLUMN form_of_tender_id SET NOT NULL');

		Schema::table('form_of_tender_print_settings', function(Blueprint $table)
		{
			$table->index('form_of_tender_id');
			$table->foreign('form_of_tender_id')->references('id')->on('form_of_tenders')->onDelete('cascade');
			$table->dropColumn('tender_id');
			$table->dropColumn('is_template');
		});
	}

	public function rollbackFormOfTenderPrintSettingsTable()
	{
		Schema::table('form_of_tender_print_settings', function(Blueprint $table)
		{
			$table->unsignedInteger('tender_id')->nullable();
			$table->boolean('is_template')->default(false);
			$table->index('tender_id');
		});

		$formOfTenderPrintSettingsTableSeeder = new FormOfTenderPrintSettingsTableSeeder();
		$formOfTenderPrintSettingsTableSeeder->rollback();

		Schema::table('form_of_tender_print_settings', function(Blueprint $table)
		{
			$table->dropColumn('form_of_tender_id');
		});
	}

	public function migrateFormOfTenderClausesTable()
	{
		Schema::table('form_of_tender_clauses', function(Blueprint $table)
		{
			$table->unsignedInteger('form_of_tender_id')->nullable();
		});

		$formOfTenderClausesTableSeeder = new FormOfTenderClausesTableSeeder();
		$formOfTenderClausesTableSeeder->run();

		DB::statement('ALTER TABLE form_of_tender_clauses ALTER COLUMN form_of_tender_id SET NOT NULL');

		Schema::table('form_of_tender_clauses', function(Blueprint $table)
		{
			$table->index('form_of_tender_id');
			$table->foreign('form_of_tender_id')->references('id')->on('form_of_tenders')->onDelete('cascade');
			$table->dropColumn('tender_id');
			$table->dropColumn('is_template');
		});
	}

	public function rollbackFormOfTenderClausesTable()
	{
		Schema::table('form_of_tender_clauses', function(Blueprint $table)
		{
			$table->unsignedInteger('tender_id')->nullable();
			$table->boolean('is_template')->default(false);
			$table->index('tender_id');
		});

		$formOfTenderClausesTableSeeder = new FormOfTenderClausesTableSeeder();
		$formOfTenderClausesTableSeeder->rollback();

		Schema::table('form_of_tender_clauses', function(Blueprint $table)
		{
			$table->dropColumn('form_of_tender_id');
		});
	}

	public function migrateFormOfTenderTenderAlternatives()
	{
		Schema::table('form_of_tender_tender_alternatives', function(Blueprint $table)
		{
			$table->unsignedInteger('form_of_tender_id')->nullable();
		});

		$formOfTenderTenderAlternativesTableSeeder = new FormOfTenderTenderAlternativesTableSeeder();
		$formOfTenderTenderAlternativesTableSeeder->run();

		DB::statement('ALTER TABLE form_of_tender_tender_alternatives ALTER COLUMN form_of_tender_id SET NOT NULL');

		Schema::table('form_of_tender_tender_alternatives', function(Blueprint $table)
		{
			$table->index('form_of_tender_id');
			$table->foreign('form_of_tender_id')->references('id')->on('form_of_tenders')->onDelete('cascade');
			$table->dropColumn('tender_id');
			$table->dropColumn('is_template');
		});
	}

	public function rollbackFormOfTenderTenderAlternatives()
	{
		Schema::table('form_of_tender_tender_alternatives', function(Blueprint $table)
		{
			$table->unsignedInteger('tender_id')->nullable();
			$table->boolean('is_template')->default(false);
			$table->index('tender_id');
		});

		$formOfTenderTenderAlternativesTableSeeder = new FormOfTenderTenderAlternativesTableSeeder();
		$formOfTenderTenderAlternativesTableSeeder->rollback();

		Schema::table('form_of_tender_tender_alternatives', function(Blueprint $table)
		{
			$table->dropColumn('form_of_tender_id');
		});
	}

	public function migrateFormOfTenderLogs()
	{
		Schema::table('form_of_tender_logs', function(Blueprint $table)
		{
			$table->unsignedInteger('form_of_tender_id')->nullable();
		});

		$formOfTenderLogsTableSeeder = new FormOfTenderLogsTableSeeder();
		$formOfTenderLogsTableSeeder->run();

		DB::statement('ALTER TABLE form_of_tender_logs ALTER COLUMN form_of_tender_id SET NOT NULL');

		Schema::table('form_of_tender_logs', function(Blueprint $table)
		{
			$table->index('form_of_tender_id');
			$table->foreign('form_of_tender_id')->references('id')->on('form_of_tenders')->onDelete('cascade');
			$table->dropColumn('tender_id');
			$table->dropColumn('is_template');
		});
	}

	public function rollbackFormOfTenderLogs()
	{
		Schema::table('form_of_tender_logs', function(Blueprint $table)
		{
			$table->unsignedInteger('tender_id')->nullable();
			$table->boolean('is_template')->default(false);
			$table->index('tender_id');
		});

		$formOfTenderLogsTableSeeder = new FormOfTenderLogsTableSeeder();
		$formOfTenderLogsTableSeeder->rollback();

		Schema::table('form_of_tender_logs', function(Blueprint $table)
		{
			$table->dropColumn('form_of_tender_id');
		});
	}

	public function migrateFormOfTenderTenderAlternativePositions()
	{
		Schema::table('tender_alternatives_position', function(Blueprint $table)
		{
			$table->unsignedInteger('form_of_tender_id')->nullable();
		});

		$formOfTenderTenderAlternativePositionsTableSeeder = new FormOfTenderTenderAlternativePositionsTableSeeder();
		$formOfTenderTenderAlternativePositionsTableSeeder->run();

		DB::statement('ALTER TABLE tender_alternatives_position ALTER COLUMN form_of_tender_id SET NOT NULL');

		Schema::table('tender_alternatives_position', function(Blueprint $table)
		{
			$table->index('form_of_tender_id');
			$table->foreign('form_of_tender_id')->references('id')->on('form_of_tenders')->onDelete('cascade');
			$table->dropColumn('tender_id');
			$table->dropColumn('is_template');
		});
	}

	public function rollbackFormOfTenderTenderAlternativePositions()
	{
		Schema::table('tender_alternatives_position', function(Blueprint $table)
		{
			$table->unsignedInteger('tender_id')->nullable();
			$table->boolean('is_template')->default(false);
			$table->index('tender_id');
		});

		$formOfTenderTenderAlternativePositionsTableSeeder = new FormOfTenderTenderAlternativePositionsTableSeeder();
		$formOfTenderTenderAlternativePositionsTableSeeder->rollback();

		Schema::table('tender_alternatives_position', function(Blueprint $table)
		{
			$table->dropColumn('form_of_tender_id');
		});
	}
}
