<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AlterElementsTableAddHasAttachmentsColumn extends Migration
{
	public function up()
	{
		Schema::table('elements', function(Blueprint $table)
		{
			$table->boolean('has_attachments')->default(false);
		});
	}

	public function down()
	{
		Schema::table('elements', function(Blueprint $table)
		{
			
			$table->dropColumn('has_attachments');
		});
	}
}
