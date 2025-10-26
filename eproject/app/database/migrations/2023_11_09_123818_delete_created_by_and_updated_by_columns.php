<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class DeleteCreatedByAndUpdatedByColumns extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('dynamic_forms', function(Blueprint $table)
		{
			$table->dropColumn('created_by');
			$table->dropColumn('updated_by');
		});

        Schema::table('form_columns', function(Blueprint $table)
		{
			$table->dropColumn('created_by');
			$table->dropColumn('updated_by');
		});

        Schema::table('form_column_sections', function(Blueprint $table)
		{
			$table->dropColumn('created_by');
			$table->dropColumn('updated_by');
		});

        Schema::table('elements', function(Blueprint $table)
		{
			$table->dropColumn('created_by');
			$table->dropColumn('updated_by');
		});

        Schema::table('system_module_elements', function(Blueprint $table)
		{
			$table->dropColumn('created_by');
			$table->dropColumn('updated_by');
		});

        Schema::table('element_values', function(Blueprint $table)
		{
			$table->dropColumn('created_by');
			$table->dropColumn('updated_by');
		});

        Schema::table('element_rejections', function(Blueprint $table)
		{
			$table->dropColumn('created_by');
			$table->dropColumn('updated_by');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('dynamic_forms', function(Blueprint $table)
		{
			$table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
		});

        Schema::table('form_columns', function(Blueprint $table)
		{
			$table->unsignedBigInteger('created_by')->nullable();
			$table->unsignedBigInteger('updated_by')->nullable();
		});

        Schema::table('form_column_sections', function(Blueprint $table)
		{
			$table->unsignedBigInteger('created_by')->nullable();
			$table->unsignedBigInteger('updated_by')->nullable();
		});

        Schema::table('elements', function(Blueprint $table)
		{
			$table->unsignedBigInteger('created_by')->nullable();
			$table->unsignedBigInteger('updated_by')->nullable();
		});

        Schema::table('system_module_elements', function(Blueprint $table)
		{
			$table->unsignedBigInteger('created_by')->nullable();
			$table->unsignedBigInteger('updated_by')->nullable();
		});

        Schema::table('element_values', function(Blueprint $table)
		{
			$table->unsignedBigInteger('created_by')->nullable();
			$table->unsignedBigInteger('updated_by')->nullable();
		});

        Schema::table('element_rejections', function(Blueprint $table)
		{
			$table->unsignedBigInteger('created_by')->nullable();
			$table->unsignedBigInteger('updated_by')->nullable();
		});
	}

}
