<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAccountCodeIdColumnInConsultantManagementVendorCategoriesRfp extends Migration
{
    public function up()
    {
        Schema::table('consultant_management_vendor_categories_rfp', function(Blueprint $table)
        {
            $table->unsignedInteger('account_code_id')->nullable()->index('cm_vcrfp_account_code_id_idx');
        });
    }

    public function down()
    {
        if (Schema::hasColumn('consultant_management_vendor_categories_rfp', 'account_code_id'))
        {
            Schema::table('consultant_management_vendor_categories_rfp', function (Blueprint $table)
            {
                $table->dropColumn('account_code_id');
            });
        }
    }
}
