<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RedefineContentColumnInAcknowledgementLetterTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('acknowledgement_letters', function(Blueprint $table)
        {
        	$table->dropColumn('content');
            $table->text('letter_content')->nullable(); 
        });
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('acknowledgement_letters', function(Blueprint $table)
        {
        	$table->dropColumn('letter_content');
            $table->string('content')->nullable(); 
        });
	}

}
