<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DropContractIdColumnFromContractGroupsTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('contract_groups', function(Blueprint $table)
        {
            $table->dropColumn('contract_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('contract_groups', function(Blueprint $table)
        {
            // Automatically assign to the first Contract.
            $defaultContractId = PCK\ContractGroups\ContractGroup::first()->id;

            $table->unsignedInteger('contract_id')->index()->default($defaultContractId);

            $table->foreign('contract_id')->references('id')->on('contracts');
        });
    }

}
