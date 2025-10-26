<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddBeneficiaryBankAccountNumberColumnToAccountCodeSettingsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('account_code_settings', function(Blueprint $table)
		{
			$table->string('beneficiary_bank_account_number', 100)->nullable();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('account_code_settings', function(Blueprint $table)
		{
			$table->dropColumn('beneficiary_bank_account_number');
		});
	}

}
