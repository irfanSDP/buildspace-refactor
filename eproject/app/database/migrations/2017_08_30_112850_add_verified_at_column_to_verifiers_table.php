<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use PCK\Verifier\Verifier;

class AddVerifiedAtColumnToVerifiersTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('verifiers', function(Blueprint $table)
        {
            $table->timestamp('verified_at')->nullable();
        });

        // Populate field for current records.
        DB::table('verifiers')->whereNotNull('approved')->update(array( 'verified_at' => DB::raw('updated_at') ));
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
            $table->dropColumn('verified_at');
        });
    }

}
