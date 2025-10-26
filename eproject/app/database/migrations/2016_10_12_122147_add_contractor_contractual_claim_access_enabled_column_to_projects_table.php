<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddContractorContractualClaimAccessEnabledColumnToProjectsTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('projects', function (Blueprint $table)
        {
            $table->boolean('contractor_contractual_claim_access_enabled')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('projects', function (Blueprint $table)
        {
            $table->dropColumn('contractor_contractual_claim_access_enabled');
        });
    }

}
