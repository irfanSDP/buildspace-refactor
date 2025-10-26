<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateEBiddingCommitteesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('e_bidding_committees', function (Blueprint $table)
		{
			$table->unsignedInteger('contract_group_id');
			$table->unsignedInteger('project_id');
			$table->unsignedInteger('user_id');
			$table->boolean('is_committee')->default(false);
			$table->timestamps();

			$table->foreign('contract_group_id')->references('id')->on('contract_groups');
			$table->foreign('project_id')->references('id')->on('projects');
			$table->foreign('user_id')->references('id')->on('users');

			$table->unique(array( 'contract_group_id', 'project_id', 'user_id'));
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('e_bidding_committees');
	}

}