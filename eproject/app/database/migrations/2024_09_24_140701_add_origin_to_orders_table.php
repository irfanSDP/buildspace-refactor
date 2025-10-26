<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddOriginToOrdersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        if (! Schema::hasColumn('orders', 'origin'))
        {
            Schema::table('orders', function (Blueprint $table) {
                $table->string('origin', 15)->nullable()->after('company_id');
            });
        }
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
        if (Schema::hasColumn('orders', 'origin'))
        {
            Schema::table('orders', function (Blueprint $table) {
                $table->dropColumn('origin');
            });
        }
	}

}
