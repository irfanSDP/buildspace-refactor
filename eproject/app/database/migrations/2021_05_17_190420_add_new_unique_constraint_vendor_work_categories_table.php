<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNewUniqueConstraintVendorWorkCategoriesTable extends Migration {
    public function up()
    {
        Schema::table('vendor_work_categories', function(Blueprint $table)
        {
            $table->dropUnique('vendor_work_categories_code_unique');
            $table->dropUnique('vendor_work_categories_name_unique');

            $table->unique(array( 'code', 'name' ));
        });

        Schema::table('vendor_work_subcategories', function(Blueprint $table)
        {
            $table->dropUnique('vendor_work_subcategories_code_unique');

            $table->unique(array('vendor_work_category_id', 'code', 'name' ));
        });
    }

    public function down()
    {
        Schema::table('vendor_work_categories', function(Blueprint $table)
        {
            $table->dropUnique('vendor_work_categories_code_name_unique');

            $table->unique('code');
            $table->unique('name');
        });

        Schema::table('vendor_work_subcategories', function(Blueprint $table)
        {
            $table->dropUnique('vendor_work_subcategories_vendor_work_category_id_code_name_uni');

            $table->unique('code');
        });
    }
}
