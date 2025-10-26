<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDefaultAccessToBuildspaceColumnToContractGroupCategoriesTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('contract_group_categories', function (Blueprint $table)
        {
            $table->boolean('default_buildspace_access')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('contract_group_categories', function (Blueprint $table)
        {
            $table->dropColumn('default_buildspace_access');
        });
    }

}
