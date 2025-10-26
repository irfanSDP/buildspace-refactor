<?php

use Illuminate\Database\Migrations\Migration;

class DropRequestsForInformationTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $migrationClass = new CreateRequestsForInformationTable();

        $migrationClass->down();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $migrationClass = new CreateRequestsForInformationTable();

        $migrationClass->up();
    }

}
