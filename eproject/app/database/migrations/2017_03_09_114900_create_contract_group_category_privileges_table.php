<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateContractGroupCategoryPrivilegesTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('contract_group_category_privileges', function(Blueprint $table)
        {
            $table->increments('id');
            $table->unsignedInteger('contract_group_category_id');
            $table->unsignedInteger('identifier');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('contract_group_category_privileges');
    }

}
