<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFileNodePermissionsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('file_node_permissions', function(Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('user_id');
			$table->unsignedInteger('file_node_id');
			$table->boolean('is_editor')->default(false);
			$table->unsignedInteger('created_by');
			$table->unsignedInteger('updated_by');
			$table->timestamps();
			$table->softDeletes();

			$table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
			$table->foreign('file_node_id')->references('id')->on('file_nodes')->onDelete('cascade');
			$table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
			$table->foreign('updated_by')->references('id')->on('users')->onDelete('cascade');

			$table->index('user_id');
			$table->index('file_node_id');
		});

		\DB::statement('CREATE UNIQUE INDEX file_node_permissions_unique ON file_node_permissions(file_node_id, user_id) WHERE deleted_at IS NULL');
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('file_node_permissions');
	}

}
