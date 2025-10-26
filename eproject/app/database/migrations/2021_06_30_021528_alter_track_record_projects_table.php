<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTrackRecordProjectsTable extends Migration
{
    public function up()
    {
        \DB::statement('ALTER TABLE track_record_projects ALTER COLUMN title TYPE TEXT;');

        Schema::table('track_record_projects', function ($table) {

            if (!Schema::hasColumn('track_record_projects', 'remarks'))
            {
                $table->text('remarks')->nullable();
            }
        });
    }

    public function down()
    {
       //no need to rollback anything
    }
}
