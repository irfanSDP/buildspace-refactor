<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeYearsOfExperienceAndAmountOfShareAndHoldingPercentageColumnsToUseNumericValueInCompanyPersonnelTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		$this->migrateColumn('years_of_experience');
		$this->migrateColumn('amount_of_share');
		$this->migrateColumn('holding_percentage');
	}

	protected function migrateColumn($column)
	{
		Schema::table('company_personnel', function(Blueprint $table) use ($column)
		{
			$table->renameColumn($column, "{$column}_remarks");
		});

		Schema::table('company_personnel', function(Blueprint $table) use ($column)
		{
			$table->decimal($column, 19, 2)->default(0);
		});

		$this->seed($column);

		print_r("All records computed for column {$column}");
		print_r(PHP_EOL);
	}

	protected function seed($column)
	{
		$records = \PCK\CompanyPersonnel\CompanyPersonnel::all();

		foreach($records as $record)
		{
			$originalColumn = "{$column}_remarks";
			$originalValue = $record->{$originalColumn};

			if(empty(trim($originalValue))) continue;

			$newValue = $this->compute($originalValue);

			DB::statement("UPDATE company_personnel SET {$column} = ? WHERE id = ?", [$newValue, $record->id]);
		}
	}

	public function compute($string)
	{
		$string = trim($string);

	    $numericValue = preg_replace('/[^0-9\.]/', '', $string);

	    return floatval($numericValue);
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		$this->unMigrateColumn('years_of_experience');
		$this->unMigrateColumn('amount_of_share');
		$this->unMigrateColumn('holding_percentage');
	}

	protected function unMigrateColumn($column)
	{
		Schema::table('company_personnel', function(Blueprint $table) use ($column)
		{
			$table->dropColumn($column);
		});

		Schema::table('company_personnel', function(Blueprint $table) use ($column)
		{
			$table->renameColumn("{$column}_remarks", $column);
		});
	}

}
