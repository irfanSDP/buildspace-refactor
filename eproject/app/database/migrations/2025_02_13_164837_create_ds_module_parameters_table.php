<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDsModuleParametersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('ds_module_parameters', function(Blueprint $table)
		{
			$table->increments('id');
            $table->integer('vendor_management_grade_id')->unsigned()->nullable();
            $table->boolean('email_reminder_before_cycle_end_date')->default(true);
            $table->integer('email_reminder_before_cycle_end_date_value')->unsigned()->default(3);
            $table->integer('email_reminder_before_cycle_end_date_unit')->unsigned()->default(4);
            $table->timestamps();

            $table->foreign('vendor_management_grade_id')->references('id')->on('vendor_management_grades')->onDelete('set null');
		});

        \DB::table('ds_module_parameters')->insert([
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ]);
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('ds_module_parameters');
	}

}
