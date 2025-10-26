<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddParentIdToSubsidiariesTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('subsidiaries', function(Blueprint $table)
        {
            $table->unsignedInteger('parent_id')->nullable();

            $table->foreign('parent_id')->references('id')->on('subsidiaries');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('subsidiaries', function(Blueprint $table)
        {
            $table->dropColumn('parent_id');
        });
    }

}
