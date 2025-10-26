<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DropInvoiceSystemTendersTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $originalMigration = new CreateInvoiceSystemTendersTable;
        $originalMigration->down();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $originalMigration = new CreateInvoiceSystemTendersTable;
        $originalMigration->up();
    }

}
