<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMembershipPricesToVendorProfileModuleParameters extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        if (! Schema::hasColumn('vendor_profile_module_parameters', 'registration_price'))
        {
            Schema::table('vendor_profile_module_parameters', function(Blueprint $table)
            {
                $table->decimal('registration_price', 24, 2)->unsigned()->default(0.00)->after('updated_at');
            });
        }

        if (! Schema::hasColumn('vendor_profile_module_parameters', 'renewal_price'))
        {
            Schema::table('vendor_profile_module_parameters', function(Blueprint $table)
            {
                $table->decimal('renewal_price', 24, 2)->unsigned()->default(0.00)->after('registration_price');
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
        if (Schema::hasColumn('vendor_profile_module_parameters', 'registration_price'))
        {
            Schema::table('vendor_profile_module_parameters', function (Blueprint $table) {
                $table->dropColumn('registration_price');
            });
        }

        if (Schema::hasColumn('vendor_profile_module_parameters', 'renewal_price'))
        {
            Schema::table('vendor_profile_module_parameters', function (Blueprint $table) {
                $table->dropColumn('renewal_price');
            });
        }
	}

}
