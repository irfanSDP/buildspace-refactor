<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddOriginalFileNameColumnToUploadsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('uploads', function(Blueprint $table)
		{
			$table->string('original_file_name')->default('');
		});

		\DB::statement("UPDATE uploads SET original_file_name = filename;");
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('uploads', function(Blueprint $table)
		{
			$table->dropColumn('original_file_name');
		});
	}

}
