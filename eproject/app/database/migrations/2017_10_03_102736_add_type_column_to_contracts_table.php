<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use PCK\Contracts\Contract;

class AddTypeColumnToContractsTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('contracts', function(Blueprint $table)
        {
            $table->unsignedInteger('type')->default(0);
        });

        foreach(Contract::all() as $contract)
        {
            $contract->type = Contract::getContractTypeIdByName($contract->name);
            $contract->save();
        }

        Schema::table('contracts', function(Blueprint $table)
        {
            $table->unique('type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('contracts', function(Blueprint $table)
        {
            $table->dropColumn('type');
        });
    }

}
