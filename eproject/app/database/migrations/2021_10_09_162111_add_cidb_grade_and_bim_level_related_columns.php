<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddCidbGradeAndBimLevelRelatedColumns extends Migration
{
	public function up()
	{
		Schema::table('companies', function(Blueprint $table)
		{
			$table->integer('cidb_grade')->nullable();
			$table->integer('bim_level_id')->nullable();

			$table->index('bim_level_id');
			$table->foreign('bim_level_id')->references('id')->on('building_information_modelling_levels')->onDelete('cascade');
		});

		Schema::table('company_temporary_details', function(Blueprint $table)
		{
			$table->integer('cidb_grade')->nullable();
			$table->integer('bim_level_id')->nullable();

			$table->index('bim_level_id');
			$table->foreign('bim_level_id')->references('id')->on('building_information_modelling_levels')->onDelete('cascade');
		});

		Schema::table('vendor_detail_settings', function(Blueprint $table) {
			$table->text('cidb_grade_instructions')->nullable();
			$table->text('bim_level_instructions')->nullable();
		});

		Schema::table('company_detail_attachment_settings', function(Blueprint $table) {
			$table->text('cidb_grade_attachments')->nullable();
			$table->text('bim_level_attachments')->nullable();
		});
	}

	public function down()
	{
		Schema::table('companies', function(Blueprint $table)
		{
			$table->dropColumn('cidb_grade');
			$table->dropColumn('bim_level_id');
		});

		Schema::table('company_temporary_details', function(Blueprint $table)
		{
			$table->dropColumn('cidb_grade');
			$table->dropColumn('bim_level_id');
		});

		Schema::table('vendor_detail_settings', function(Blueprint $table) {
			$table->dropColumn('cidb_grade_instructions');
			$table->dropColumn('bim_level_instructions');
		});

		Schema::table('company_detail_attachment_settings', function(Blueprint $table) {
			$table->dropColumn('cidb_grade_attachments');
			$table->dropColumn('bim_level_attachments');
		});
	}
}
