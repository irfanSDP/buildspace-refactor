<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateActiveVendorsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('active_vendors', function(Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('vendor_work_category_id');
			$table->unsignedInteger('company_id');
			$table->timestamp('expire_at');
			$table->timestamps();

			$table->foreign('vendor_work_category_id')->references('id')->on('vendor_work_categories')->onDelete('cascade');
			$table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');

			$table->unique(array('vendor_work_category_id', 'company_id'));
			$table->index('company_id');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('active_vendors');
	}

}
