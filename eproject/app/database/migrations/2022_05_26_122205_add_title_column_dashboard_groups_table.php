<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTitleColumnDashboardGroupsTable extends Migration
{
    public function up()
    {
        Schema::table('dashboard_groups', function(Blueprint $table)
        {
            $table->string('title', 150)->nullable();
        });
    }

    public function down()
    {
        Schema::table('dashboard_groups', function(Blueprint $table)
        {
            $table->dropColumn('title');
        });
    }
}
