<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateConsultantManagementCallingRfpTable extends Migration
{
    public function up()
    {
        Schema::create('consultant_management_calling_rfp', function(Blueprint $table)
        {
            $table->bigIncrements('id');
            $table->unsignedInteger('consultant_management_rfp_revision_id')->index('consultant_management_call_rfp_rfp_rev_id_idx');
            $table->dateTime('calling_rfp_date');
            $table->dateTime('closing_rfp_date');
            $table->integer('status')->index('consultant_management_call_rfp_status_idx');
            $table->unsignedInteger('created_by');
            $table->unsignedInteger('updated_by');
            $table->timestamps();

            $table->unique(['consultant_management_rfp_revision_id']);

            $table->foreign('consultant_management_rfp_revision_id', 'consultant_management_call_rfp_rfp_rev_id_fk')->references('id')->on('consultant_management_rfp_revisions');
            $table->foreign('created_by', 'consultant_management_call_rfp_created_by_fk')->references('id')->on('users');
            $table->foreign('updated_by', 'consultant_management_call_rfp_updated_by_fk')->references('id')->on('users');
        });

        Schema::create('consultant_management_calling_rfp_verifiers', function(Blueprint $table)
        {
            $table->bigIncrements('id');
            $table->unsignedInteger('consultant_management_calling_rfp_id')->index('consultant_management_call_rfp_verifiers_id_idx');
            $table->unsignedInteger('user_id')->index('consultant_management_call_rfp_verifiers_user_id_idx');
            $table->unsignedInteger('created_by');
            $table->unsignedInteger('updated_by');
            $table->timestamps();

            $table->unique(['consultant_management_calling_rfp_id', 'user_id'], 'consultant_management_call_rfp_verifiers_unique');

            $table->foreign('consultant_management_calling_rfp_id', 'consultant_management_call_rfp_verifiers_id_fk')->references('id')->on('consultant_management_calling_rfp');
            $table->foreign('user_id', 'consultant_management_call_rfp_verifiers_user_id_fk')->references('id')->on('users');
            $table->foreign('created_by', 'consultant_management_call_rfpv_created_by_fk')->references('id')->on('users');
            $table->foreign('updated_by', 'consultant_management_call_rfpv_updated_by_fk')->references('id')->on('users');
        });

        Schema::create('consultant_management_call_rfp_verifier_versions', function(Blueprint $table)
        {
            $table->bigIncrements('id');
            $table->unsignedInteger('consultant_management_calling_rfp_verifier_id')->index('consultant_management_call_rfpvv_verifier_id_idx');
            $table->unsignedInteger('user_id')->index('consultant_management_call_rfpvv_user_id_idx');
            $table->integer('version')->index('consultant_management_call_rfpvv_version_idx');
            $table->integer('status')->index('consultant_management_call_rfpvv_status_idx');
            $table->text('remarks')->nullable();
            $table->unsignedInteger('created_by');
            $table->unsignedInteger('updated_by');
            $table->timestamps();

            $table->unique(['consultant_management_calling_rfp_verifier_id', 'user_id', 'version'], 'consultant_management_call_rfpvv_unique');

            $table->foreign('consultant_management_calling_rfp_verifier_id', 'consultant_management_call_rfpvv_verifier_id_fk')->references('id')->on('consultant_management_calling_rfp_verifiers');
            $table->foreign('user_id', 'consultant_management_call_rfpvv_user_id_fk')->references('id')->on('users');
            $table->foreign('created_by', 'consultant_management_call_rfpvv_created_by_fk')->references('id')->on('users');
            $table->foreign('updated_by', 'consultant_management_call_rfpvv_updated_by_fk')->references('id')->on('users');
        });

        Schema::create('consultant_management_calling_rfp_companies', function(Blueprint $table)
        {
            $table->bigIncrements('id');
            $table->unsignedInteger('consultant_management_calling_rfp_id')->index('consultant_management_call_rfpc_cr_id_idx');
            $table->unsignedInteger('company_id')->index('consultant_management_call_rfpc_company_id_idx');
            $table->integer('status')->index('consultant_management_call_rfpc_status_idx');
            $table->unsignedInteger('created_by');
            $table->unsignedInteger('updated_by');
            $table->timestamps();

            $table->unique(['consultant_management_calling_rfp_id', 'company_id'], 'consultant_management_call_rfpc_unique');

            $table->foreign('consultant_management_calling_rfp_id', 'consultant_management_call_rfpc_cr_id_fk')->references('id')->on('consultant_management_calling_rfp');
            $table->foreign('company_id', 'consultant_management_call_rfpc_company_id_fk')->references('id')->on('companies');
            $table->foreign('created_by', 'consultant_management_call_rfpc_created_by_fk')->references('id')->on('users');
            $table->foreign('updated_by', 'consultant_management_call_rfpc_updated_by_fk')->references('id')->on('users');
        });
    }

    public function down()
    {
        Schema::dropIfExists('consultant_management_calling_rfp_companies');
        Schema::dropIfExists('consultant_management_call_rfp_verifier_versions');
        Schema::dropIfExists('consultant_management_calling_rfp_verifiers');
        Schema::dropIfExists('consultant_management_calling_rfp');
    }
}
