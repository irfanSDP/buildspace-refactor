<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDsTemplateForms extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('ds_template_forms', function(Blueprint $table)
		{
			$table->increments('id');
            $table->integer('weighted_node_id')->unsigned();
            $table->integer('revision')->default(0);
            $table->integer('original_form_id')->unsigned()->default(0);
            $table->integer('status_id')->unsigned();
            $table->boolean('current_selected_revision')->default(false);
            $table->timestamps();

            $table->foreign('weighted_node_id')
                ->references('id')
                ->on('weighted_nodes')
                ->onDelete('cascade');

            $table->index('original_form_id');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('ds_template_forms');
	}

}
