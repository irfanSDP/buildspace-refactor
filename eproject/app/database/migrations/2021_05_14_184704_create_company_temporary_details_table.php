<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCompanyTemporaryDetailsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('company_temporary_details', function(Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('vendor_registration_id');
			$table->text('address');
			$table->string('main_contact');
			$table->string('tax_registration_no', 20)->nullable();
			$table->string('email');
			$table->string('telephone_number');
			$table->string('fax_number');
			$table->boolean('is_bumiputera')->nullable();
			$table->decimal('bumiputera_equity', 5, 2)->default(0.0);
			$table->decimal('non_bumiputera_equity', 5, 2)->default(0.0);
			$table->decimal('foreigner_equity', 5, 2)->default(0.0);
			$table->timestamps();

			$table->foreign('vendor_registration_id')->references('id')->on('vendor_registrations')->onDelete('cascade');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('company_temporary_details');
	}

}
