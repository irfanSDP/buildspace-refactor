<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNewUniqueConstraintBusinessEntityTypesTable extends Migration {
    public function up()
    {
        Schema::table('business_entity_types', function(Blueprint $table)
        {
            $table->unique('name');
        });
    }

    public function down()
    {
        Schema::table('business_entity_types', function(Blueprint $table)
        {
            $table->dropUnique('business_entity_types_name_unique');
        });
    }
}
