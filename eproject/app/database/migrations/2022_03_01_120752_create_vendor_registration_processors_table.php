<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVendorRegistrationProcessorsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('vendor_registration_processors', function(Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('vendor_registration_id');
			$table->unsignedInteger('user_id');
			$table->timestamps();
			$table->softDeletes();

			$table->foreign('vendor_registration_id')->references('id')->on('vendor_registrations')->onDelete('cascade');
			$table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

			$table->index('vendor_registration_id');
			$table->index('user_id');
		});

		\DB::statement('CREATE UNIQUE INDEX vendor_registration_processors_unique ON vendor_registration_processors(vendor_registration_id) WHERE deleted_at IS NULL');

		$this->seedTable();
	}

	private function seedTable()
	{
		$now = \Carbon\Carbon::now();

		$records = \DB::table('vendor_registrations')
	        ->select('id', 'processor_id')
	        ->whereNotNull('processor_id')
	        ->get();

	    $rows = [];

        foreach($records as $record)
        {
        	$rows[] = [
        	    'vendor_registration_id' => $record->id,
        	    'user_id'    			 => $record->processor_id,
        	    'created_at' 			 => $now,
        	    'updated_at' 			 => $now,
        	];
        }

        if(!empty($rows)) \DB::table('vendor_registration_processors')->insert($rows);
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('vendor_registration_processors');
	}

}
