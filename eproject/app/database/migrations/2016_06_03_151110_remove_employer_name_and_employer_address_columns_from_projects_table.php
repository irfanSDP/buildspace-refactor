<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveEmployerNameAndEmployerAddressColumnsFromProjectsTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('projects', function (Blueprint $table)
        {
            $table->dropColumn('employer_address');
            $table->dropColumn('employer_name');
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
            $table->string('employer_name')->nullable();
            $table->string('employer_address')->nullable();
        });
    }

}
