<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveUniqueConstraintsForReferencesInProjectsTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('projects', function(Blueprint $table)
        {
            $table->dropUnique('projects_reference_suffix_running_number_subsidiary_id_unique');
            $table->dropUnique('projects_reference_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Will likely cause errors due to non-unique values in the table.
        Schema::table('projects', function(Blueprint $table)
        {
            $table->unique('reference');
            $table->unique(array( 'reference_suffix', 'running_number', 'subsidiary_id' ));
        });
    }

}
