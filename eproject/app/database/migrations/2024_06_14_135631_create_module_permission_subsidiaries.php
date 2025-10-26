<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateModulePermissionSubsidiaries extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('module_permission_subsidiaries', function(Blueprint $table)
		{
            $table->integer('module_permission_id')->unsigned();
            $table->integer('subsidiary_id')->unsigned();
			$table->timestamps();

            $table->foreign('module_permission_id')->references('id')->on('module_permissions')->onDelete('cascade');
            $table->foreign('subsidiary_id')->references('id')->on('subsidiaries')->onDelete('cascade');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('module_permission_subsidiaries');
	}

}
