<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateExternalApplicationsTable extends Migration
{
    public function up()
    {
        Schema::create('external_application_clients', function(Blueprint $table)
        {
            $table->bigIncrements('id');
            $table->string('name')->index('ext_app_clients_name_idx')->unique();
            $table->string('token')->index('ext_app_clients_token_idx')->unique();
            $table->text('remarks')->nullable();
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('created_by')->index();
            $table->unsignedInteger('updated_by')->index();
            $table->timestamps();

            $table->foreign('user_id', 'ext_app_clients_user_id_fk')->references('id')->on('users');
            $table->foreign('created_by', 'ext_app_clients_created_by_fk')->references('id')->on('users');
            $table->foreign('updated_by', 'ext_app_clients_updated_by_fk')->references('id')->on('users');
        });

        Schema::create('external_application_client_modules', function(Blueprint $table)
        {
            $table->bigIncrements('id');
            $table->unsignedInteger('client_id')->index('ext_app_cm_client_id_idx');
            $table->string('module')->index('ext_app_cm_module_idx');
            $table->integer('downstream_permission')->index('ext_app_cm_downstream_perm_idx');
            $table->timestamps();

            $table->unique(['client_id', 'module'], 'ext_app_cm_module_idx_unique');

            $table->foreign('client_id', 'ext_app_cm_client_id_fk')->references('id')->on('external_application_clients');
        });

        Schema::create('external_application_identifiers', function(Blueprint $table)
        {
            $table->unsignedInteger('client_module_id')->index('ext_app_ident_client_module_id_idx');
            $table->string('class_name')->index('ext_app_ident_class_name_idx');
            $table->integer('internal_identifier')->index('ext_app_ident_int_ident_idx');
            $table->string('external_identifier')->index('ext_app_ident_ext_ident_idx');
            $table->timestamps();

            $table->unique(['client_module_id', 'class_name', 'external_identifier'], 'ext_app_ident_ext_idx_unique');

            $table->primary(['client_module_id', 'class_name', 'internal_identifier']);

            $table->foreign('client_module_id', 'ext_app_ident_client_module_id_fk')->references('id')->on('external_application_client_modules');
        });

        Schema::create('external_application_attributes', function(Blueprint $table)
        {
            $table->bigIncrements('id');
            $table->unsignedInteger('client_module_id')->index('ext_app_att_client_module_id_idx');
            $table->integer('internal_attribute')->index('ext_app_att_int_attribute_idx');
            $table->string('external_attribute')->index('ext_app_att_ext_attribute_idx');
            $table->boolean('is_identifier')->default(false)->index('ext_app_att_is_identifier_idx');
            $table->timestamps();

            $table->unique(['client_module_id', 'internal_attribute'], 'ext_app_att_int_idx_unique');

            $table->foreign('client_module_id', 'ext_app_att_client_module_id_fk')->references('id')->on('external_application_client_modules');
        });
    }

    public function down()
    {
        Schema::dropIfExists('external_application_identifiers');
        Schema::dropIfExists('external_application_attributes');
        Schema::dropIfExists('external_application_client_modules');
        Schema::dropIfExists('external_application_clients');

    }
}