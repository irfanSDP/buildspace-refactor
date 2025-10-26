<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AlterDynamicFormsTableAddRenewalRelatedColumns extends Migration
{
	public function up()
	{
		Schema::table('dynamic_forms', function(Blueprint $table)
		{
			$table->boolean('is_renewal_form')->default(false);
			$table->boolean('renewal_approval_required')->default(false);
		});
	}

	public function down()
	{
		Schema::table('dynamic_forms', function(Blueprint $table)
		{
			$table->dropColumn('is_renewal_form');
			$table->dropColumn('renewal_approval_required');
		});
	}
}