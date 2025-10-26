<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddConfirmedColumnToCompaniesTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('companies', function (Blueprint $table)
        {
            $table->boolean('confirmed')->default(false);
        });

        foreach(\PCK\Companies\Company::all() as $company)
        {
            $company->confirmed = true;
            $company->save();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('companies', function (Blueprint $table)
        {
            $table->dropColumn('confirmed');
        });
    }

}
