<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AlterFormObjectMappingsTableAddColumns extends Migration
{
	public function up()
	{
		Schema::table('form_object_mappings', function(Blueprint $table)
		{
			$table->unsignedInteger('created_by');
			$table->unsignedInteger('updated_by');

			$table->index('created_by');
			$table->index('updated_by');

			$table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
			$table->foreign('updated_by')->references('id')->on('users')->onDelete('cascade');
		});
	}

	public function down()
	{
		Schema::table('form_object_mappings', function(Blueprint $table)
		{
			$table->dropColumn('created_by');
			$table->dropColumn('updated_by');
		});
	}
}
