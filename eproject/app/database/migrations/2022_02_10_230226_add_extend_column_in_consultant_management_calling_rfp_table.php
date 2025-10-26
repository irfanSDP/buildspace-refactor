<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddExtendColumnInConsultantManagementCallingRfpTable extends Migration
{
    public function up()
    {
        Schema::table('consultant_management_calling_rfp', function(Blueprint $table)
        {
            $table->boolean('is_extend')->default(false);
        });
    }

    public function down()
    {
        if (Schema::hasColumn('consultant_management_calling_rfp', 'is_extend'))
        {
            Schema::table('consultant_management_calling_rfp', function (Blueprint $table)
            {
                $table->dropColumn('is_extend');
            });
        }
    }
}
