<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCodeColumnToContractGroupCategoriesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('contract_group_categories', function(Blueprint $table)
		{
			$table->string('code', 50)->nullable();
			$table->boolean('hidden')->default(false);
		});

		\DB::statement('UPDATE contract_group_categories SET code = name;');
		\DB::statement('ALTER TABLE contract_group_categories ALTER COLUMN code SET NOT NULL');

		Schema::table('contract_group_categories', function(Blueprint $table)
		{
			$table->unique('code');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('contract_group_categories', function(Blueprint $table)
		{
			$table->dropColumn('code');
			$table->dropColumn('hidden');
		});
	}

}
