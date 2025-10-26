<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterColumnsLengthInCompaniesTable extends Migration {
    public function up()
    {
        DB::statement('ALTER TABLE companies ALTER COLUMN reference_no TYPE VARCHAR(50)');
        DB::statement('ALTER TABLE companies ALTER COLUMN tax_registration_no TYPE VARCHAR(50)');
        DB::statement('ALTER TABLE companies ALTER COLUMN tax_registration_id TYPE VARCHAR(50)');
    }
    
    public function down()
    {
        DB::statement('ALTER TABLE companies ALTER COLUMN reference_no TYPE VARCHAR(20)');
        DB::statement('ALTER TABLE companies ALTER COLUMN tax_registration_no TYPE VARCHAR(20)');
        DB::statement('ALTER TABLE companies ALTER COLUMN tax_registration_id TYPE VARCHAR(20)');
    }
}
