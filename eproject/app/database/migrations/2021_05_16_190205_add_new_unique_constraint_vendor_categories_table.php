<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNewUniqueConstraintVendorCategoriesTable extends Migration {
    public function up()
    {
        Schema::table('vendor_categories', function(Blueprint $table)
        {
            $table->dropUnique('vendor_categories_name_unique');

            $table->unique(array( 'contract_group_category_id', 'name' ));
        });
    }

    public function down()
    {
        Schema::table('vendor_categories', function(Blueprint $table)
        {
            $table->dropUnique('vendor_categories_contract_group_category_id_name_unique');
            $table->unique('name');
        });
    }
}
