<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddThirdPartyVendorIdColumnCompaniesTable extends Migration {

    public function up()
    {
        Schema::table('companies', function(Blueprint $table)
        {
            $table->string('third_party_app_identifier', 20)->nullable();
            $table->unsignedInteger('third_party_vendor_id')->nullable();

            $table->unique(array('third_party_app_identifier', 'third_party_vendor_id' ));
        });
    }

    public function down()
    {
        Schema::table('companies', function(Blueprint $table)
        {
            $table->dropColumn('third_party_app_identifier');
            $table->dropColumn('third_party_vendor_id');

            $table->dropUnique('companies_third_party_app_identifier_third_party_vendor_id_uniq');
        });
    }
}
