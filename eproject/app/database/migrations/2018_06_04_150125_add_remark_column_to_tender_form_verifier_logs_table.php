<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRemarkColumnToTenderFormVerifierLogsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('tender_form_verifier_logs', function(Blueprint $table)
        {
            $table->string('remark')->nullable(); 
        });
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('tender_form_verifier_logs', function(Blueprint $table)
        {
            $table->dropColumn('remark');
        });
	}

}
