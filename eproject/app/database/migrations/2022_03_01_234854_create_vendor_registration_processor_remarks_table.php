<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVendorRegistrationProcessorRemarksTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('vendor_registration_processor_remarks', function(Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('vendor_registration_processor_id');
			$table->text('remarks')->nullable();
			$table->timestamps();
			$table->softDeletes();

			$table->foreign('vendor_registration_processor_id')->references('id')->on('vendor_registration_processors')->onDelete('cascade');

			$table->index('vendor_registration_processor_id');
		});

		$this->seedTable();
	}

	private function seedTable()
	{
		$now = \Carbon\Carbon::now();

		$records = \DB::table('vendor_registration_processors')
            ->select(
            	'vendor_registration_processors.id',
            	'vendor_registrations.processor_remarks')
            ->join('vendor_registrations', 'vendor_registrations.id', '=', 'vendor_registration_processors.vendor_registration_id')
            ->whereNotNull('vendor_registrations.processor_remarks')
            ->get();

	    $rows = [];

        foreach($records as $record)
        {
        	$rows[] = [
        	    'vendor_registration_processor_id' => $record->id,
        	    'remarks'    			 		   => $record->processor_remarks,
        	    'created_at' 			 		   => $now,
        	    'updated_at' 			 		   => $now,
        	];
        }

        if(!empty($rows)) \DB::table('vendor_registration_processor_remarks')->insert($rows);
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('vendor_registration_processor_remarks');
	}

}
