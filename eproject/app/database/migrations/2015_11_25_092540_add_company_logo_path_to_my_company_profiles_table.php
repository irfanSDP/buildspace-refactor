<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCompanyLogoPathToMyCompanyProfilesTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('my_company_profiles', function (Blueprint $table)
        {
            $table->string('company_logo_path')->nullable();
            $table->string('company_logo_filename')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('my_company_profiles', function (Blueprint $table)
        {
            $table->dropColumn('company_logo_path');
            $table->dropColumn('company_logo_filename');
        });
    }

}
