<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateExternalApplicationsOutboundTable extends Migration
{
    public function up()
    {
        Schema::create('external_application_client_outbound_authorizations', function(Blueprint $table)
        {
            $table->unsignedInteger('client_id')->index('ext_app_coa_client_id_idx');
            $table->string('url');
            $table->integer('type')->index('ext_app_coa_type_idx');
            $table->text('options')->index('ext_app_coa_options_idx')->unique();
            $table->timestamps();

            $table->primary(['client_id']);

            $table->foreign('client_id', 'ext_app_coa_client_id_fk')->references('id')->on('external_application_clients');
        });

        \DB::statement('ALTER TABLE external_application_client_outbound_authorizations ALTER COLUMN options TYPE jsonb USING options::text::jsonb');

        Schema::table('external_application_client_modules', function($table) {
            $table->integer('outbound_status')->default(1)->index('ext_app_cm_outbound_status_idx');
            $table->boolean('outbound_only_same_source')->default(true);
            $table->string('outbound_url_path')->nullable();
        });

    }

    public function down()
    {
        Schema::dropIfExists('external_application_client_outbound_authorizations');

        Schema::table('external_application_client_modules', function($table) {
            $table->dropColumn('outbound_status');
            $table->dropColumn('outbound_only_same_source');
            $table->dropColumn('outbound_url_path');
        });
    }
}
