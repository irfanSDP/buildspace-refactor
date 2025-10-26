<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterFaxNumberColumnNullableBsCompaniesTable extends Migration {

    public function up()
    {
        \DB::connection('buildspace')->statement('ALTER TABLE bs_companies ALTER COLUMN fax_number DROP NOT NULL');
    }
    
    public function down()
    {
        \DB::connection('buildspace')->statement('ALTER TABLE bs_companies ALTER COLUMN fax_number SET NOT NULL');
    }
}
