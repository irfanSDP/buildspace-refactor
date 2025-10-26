<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use PCK\FormBuilder\DynamicForm;

class CreateDynamicFormsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('dynamic_forms', function(Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('root_id')->nullable();
			$table->integer('module_identifier');
			$table->string('name');
			$table->boolean('is_template')->default(false);
			$table->integer('revision');
			$table->integer('status')->default(DynamicForm::STATUS_OPEN);
			$table->unsignedInteger('submitted_for_approval_by')->nullable();
			$table->unsignedInteger('created_by');
			$table->unsignedInteger('updated_by');
			$table->timestamps();

			$table->index('root_id');
			$table->index('submitted_for_approval_by');
			$table->index('created_by');
			$table->index('updated_by');
			
			$table->foreign('submitted_for_approval_by')->references('id')->on('users')->onDelete('cascade');
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
		Schema::drop('dynamic_forms');
	}

}
