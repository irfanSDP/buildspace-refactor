<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddReadColumnIntoContractGroupConversationTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('contract_group_conversation', function (Blueprint $table)
		{
			$table->boolean('read')->default(false)->index();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('contract_group_conversation', function (Blueprint $table)
		{
			$table->dropColumn('read');
		});
	}

}