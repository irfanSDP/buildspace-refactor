<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddUserIdIndexForCompanyImportedUsersTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('company_imported_users', function(Blueprint $table)
        {
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('company_imported_users', function(Blueprint $table)
        {
            $table->dropIndex('company_imported_users_user_id_index');
        });
    }

}
