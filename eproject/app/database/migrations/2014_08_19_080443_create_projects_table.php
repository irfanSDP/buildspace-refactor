<?php

use PCK\Helpers\CustomBlueprint;
use PCK\Helpers\CustomMigration;

class CreateProjectsTable extends CustomMigration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		$this->schema->create('projects', function (CustomBlueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('business_unit_id')->index();
			$table->unsignedInteger('contract_id')->index();
			$table->string('title');
			$table->string('reference');
			$table->text('address');
			$table->text('description');
			$table->unsignedInteger('running_number')->default(1);
			$table->signAbleColumns();
			$table->timestamps();

			$table->foreign('business_unit_id')->references('id')->on('companies');
			$table->foreign('contract_id')->references('id')->on('contracts');

			$table->unique(array( 'business_unit_id', 'running_number' ));
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		$this->schema->drop('projects');
	}

}