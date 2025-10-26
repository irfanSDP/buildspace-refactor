<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddContractorsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('contractors', function(Blueprint $table)
		{
			$table->increments('id');

            $table->integer('company_id')->unsigned();
            $table->foreign('company_id')
                ->references('id')
                ->on('companies')
                ->onDelete('cascade');

            $table->integer('previous_cpe_grade_id')->unsigned();
            $table->foreign('previous_cpe_grade_id')
                ->references('id')
                ->on('previous_cpe_grades')
                ->onDelete('cascade');

            $table->integer('current_cpe_grade_id')->unsigned();
            $table->foreign('current_cpe_grade_id')
                ->references('id')
                ->on('current_cpe_grades')
                ->onDelete('cascade');

            $table->integer('registration_status_id')->unsigned();
            $table->foreign('registration_status_id')
                ->references('id')
                ->on('contractor_registration_statuses')
                ->onDelete('cascade');

            $table->integer('job_limit_sign')->nullable();

            $table->decimal('job_limit_number', 19, 0)->nullable()->unsigned();

            $table->string('cidb_category')->nullable();

            $table->string('remarks')->nullable();

            $table->date('registered_date')->nullable();

            $table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('contractors');
	}

}
