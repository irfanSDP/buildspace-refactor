<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AlterCompaniesTableAddBumiputeraStatusRelatedColumns extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('companies', function(Blueprint $table)
		{
			$table->boolean('is_bumiputera')->nullable();
			$table->decimal('bumiputera_equity', 5, 2)->default(0.0);
			$table->decimal('non_bumiputera_equity', 5, 2)->default(0.0);
			$table->decimal('foreigner_equity', 5, 2)->default(0.0);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('companies', function(Blueprint $table)
		{
			$table->dropColumn('is_bumiputera');
			$table->dropColumn('bumiputera_equity');
			$table->dropColumn('non_bumiputera_equity');
			$table->dropColumn('foreigner_equity');
		});
	}

}
