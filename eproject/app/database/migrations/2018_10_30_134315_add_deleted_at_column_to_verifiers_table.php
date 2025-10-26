<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDeletedAtColumnToVerifiersTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('verifiers', function(Blueprint $table)
        {
            $table->dropUnique('verifiers_object_id_object_type_verifier_id_unique');
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('verifiers', function(Blueprint $table)
        {
            $table->dropSoftDeletes();
            $table->unique(array( 'object_id', 'object_type', 'verifier_id' ));
        });
    }

}
