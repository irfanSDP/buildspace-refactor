<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIsEditorColumnToModulePermissionsTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('module_permissions', function(Blueprint $table)
        {
            $table->boolean('is_editor')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('module_permissions', function(Blueprint $table)
        {
            $table->dropColumn('is_editor');
        });
    }

}
