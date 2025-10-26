<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddReferenceSuffixColumnToProjectsTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('projects', function (Blueprint $table)
        {
            $table->string('reference_suffix')->default('');

            $table->dropUnique('projects_running_number_subsidiary_id_unique');

            $table->unique(array( 'reference_suffix', 'running_number', 'subsidiary_id' ));
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('projects', function (Blueprint $table)
        {
            $table->dropUnique('projects_reference_suffix_running_number_subsidiary_id_unique');

            $table->unique(array( 'running_number', 'subsidiary_id' ));

            $table->dropColumn('reference_suffix');
        });
    }

}
