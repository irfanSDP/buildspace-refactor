<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddUniqueIndexToReferenceNoColumnInCompaniesTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // We try to assign each company with a unique Reference Number.
        foreach(\PCK\Companies\Company::all() as $company)
        {
            // First we try with company id.
            if( is_null($company->reference_no) && ( ! \PCK\Helpers\Key::keyInTable('companies', $company->id, 'reference_no') ) )
            {
                $company->reference_no = $company->id;
            }
            // As a last resort we generate a random string.
            elseif( is_null($company->reference_no) || ( ! \PCK\Helpers\Key::isUnique('companies', $company->reference_no, 'reference_no') ) )
            {
                $company->reference_no = \PCK\Helpers\Key::createKey('companies', 'reference_no');
            }

            $company->save();
        }

        Schema::table('companies', function (Blueprint $table)
        {
            $table->unique('reference_no');
        });
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
            $table->dropUnique('companies_reference_no_unique');
        });
    }

}
