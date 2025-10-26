<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CabinetSetupUploadsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return  void
	 */
	public function up()
	{
		// Creates the uploads table
		Schema::create('uploads', function (Blueprint $table)
		{
			$table->increments('id');
			$table->string('filename');
			$table->string('path');
			$table->unsignedInteger('size');
			$table->string('extension');
			$table->string('mimetype');
			$table->unsignedInteger('user_id')->index();
			$table->unsignedInteger('parent_id')->nullable()->index(); // If this is a child file, it'll be referenced here.
			$table->softDeletes();
			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return  void
	 */
	public function down()
	{
		Schema::drop('uploads');
	}

}
