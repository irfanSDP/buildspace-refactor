<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use PCK\ContractGroups\ContractGroup;
use PCK\ContractGroups\Types\Role;

class DropPam2006ContractGroupIdColumnFromCompaniesTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('companies', function(Blueprint $table)
        {
            $table->dropColumn('pam_2006_contract_group_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('companies', function(Blueprint $table)
        {
            $table->unsignedInteger('pam_2006_contract_group_id')->default(ContractGroup::getIdByGroup(Role::CONTRACTOR));
            $table->foreign('pam_2006_contract_group_id')->references('id')->on('contract_groups');
        });
    }

}
