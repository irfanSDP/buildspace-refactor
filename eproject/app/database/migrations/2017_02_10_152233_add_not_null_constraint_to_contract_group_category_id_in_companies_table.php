<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNotNullConstraintToContractGroupCategoryIdInCompaniesTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('ALTER TABLE companies ALTER COLUMN contract_group_category_id SET NOT NULL');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('ALTER TABLE companies ALTER COLUMN contract_group_category_id DROP NOT NULL');
    }

}
