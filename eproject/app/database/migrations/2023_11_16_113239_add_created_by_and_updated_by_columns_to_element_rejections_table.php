<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddCreatedByAndUpdatedByColumnsToElementRejectionsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('element_rejections', function(Blueprint $table)
		{
			$table->unsignedBigInteger('created_by')->nullable();
			$table->unsignedBigInteger('updated_by')->nullable();

            $table->index('created_by');
			$table->index('updated_by');

			$table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
			$table->foreign('updated_by')->references('id')->on('users')->onDelete('cascade');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('element_rejections', function(Blueprint $table)
		{
			$table->dropColumn('created_by');
			$table->dropColumn('updated_by');
		});
	}

}
