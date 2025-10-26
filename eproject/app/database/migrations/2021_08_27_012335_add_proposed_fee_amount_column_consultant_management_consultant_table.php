<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddProposedFeeAmountColumnConsultantManagementConsultantTable extends Migration
{
    public function up()
    {
        Schema::table('consultant_management_consultant_rfp_proposed_fees', function(Blueprint $table)
        {
            $table->decimal('proposed_fee_amount', 19, 5)->default(0);
        });
    }

    public function down()
    {
        //no need to revert changes
    }
}
