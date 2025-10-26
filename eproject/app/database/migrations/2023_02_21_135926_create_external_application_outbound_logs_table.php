<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateExternalApplicationOutboundLogsTable extends Migration
{
    public function up()
    {
        Schema::create('external_application_client_outbound_logs', function(Blueprint $table)
        {
            $table->bigIncrements('id');
            $table->unsignedInteger('client_id')->index('ext_app_col_client_id_idx');
            $table->string('module', 120);
            $table->text('data');
            $table->string('status_code', 120);
            $table->text('response_contents')->nullable();
            $table->timestamps();

            $table->foreign('client_id', 'ext_app_col_client_id_fk')->references('id')->on('external_application_clients');
        });

        \DB::statement('ALTER TABLE external_application_client_outbound_logs ALTER COLUMN data TYPE jsonb USING data::text::jsonb');
    }

    public function down()
    {
        Schema::dropIfExists('external_application_client_outbound_logs');
    }
}
