<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateConsultantManagementOpenRfpTable extends Migration
{
    public function up()
    {
        Schema::create('consultant_management_open_rfp', function(Blueprint $table)
        {
            $table->bigIncrements('id');
            $table->unsignedInteger('consultant_management_rfp_revision_id')->index('cm_open_rfp_rfp_rev_id_idx');
            $table->integer('status')->index('cm_open_rfp_status_idx');
            $table->unsignedInteger('created_by');
            $table->unsignedInteger('updated_by');
            $table->timestamps();

            $table->unique(['consultant_management_rfp_revision_id']);

            $table->foreign('consultant_management_rfp_revision_id', 'cm_open_rfp_rfp_rev_id_fk')->references('id')->on('consultant_management_rfp_revisions');
            $table->foreign('created_by', 'cm_open_rfp_created_by_fk')->references('id')->on('users');
            $table->foreign('updated_by', 'cm_open_rfp_updated_by_fk')->references('id')->on('users');
        });

        Schema::create('consultant_management_open_rfp_verifiers', function(Blueprint $table)
        {
            $table->bigIncrements('id');
            $table->unsignedInteger('consultant_management_open_rfp_id')->index('cm_open_rfp_verifiers_rfp_id_idx');
            $table->unsignedInteger('user_id')->index('cm_open_rfp_verifiers_user_id_idx');
            $table->unsignedInteger('created_by');
            $table->unsignedInteger('updated_by');
            $table->timestamps();

            $table->unique(['consultant_management_open_rfp_id', 'user_id'], 'cm_open_rfp_verifiers_unique');

            $table->foreign('consultant_management_open_rfp_id', 'cm_open_rfp_verifiers_rfp_id_fk')->references('id')->on('consultant_management_open_rfp');
            $table->foreign('user_id', 'cm_open_rfp_verifiers_user_id_fk')->references('id')->on('users');
            $table->foreign('created_by', 'cm_open_rfpv_created_by_fk')->references('id')->on('users');
            $table->foreign('updated_by', 'cm_open_rfpv_updated_by_fk')->references('id')->on('users');
        });

        Schema::create('consultant_management_open_rfp_verifier_versions', function(Blueprint $table)
        {
            $table->bigIncrements('id');
            $table->unsignedInteger('consultant_management_open_rfp_verifier_id')->index('cm_open_rfpvv_verifier_id_idx');
            $table->unsignedInteger('user_id')->index('cm_open_rfpvv_user_id_idx');
            $table->integer('version')->index('cm_open_rfpvv_version_idx');
            $table->integer('status')->index('cm_open_rfpvv_status_idx');
            $table->text('remarks')->nullable();
            $table->unsignedInteger('created_by');
            $table->unsignedInteger('updated_by');
            $table->timestamps();

            $table->unique(['consultant_management_open_rfp_verifier_id', 'user_id', 'version'], 'cm_open_rfpvv_unique');

            $table->foreign('consultant_management_open_rfp_verifier_id', 'cm_open_rfpvv_verifier_id_fk')->references('id')->on('consultant_management_open_rfp_verifiers');
            $table->foreign('user_id', 'cm_open_rfpvv_user_id_fk')->references('id')->on('users');
            $table->foreign('created_by', 'cm_open_rfpvv_created_by_fk')->references('id')->on('users');
            $table->foreign('updated_by', 'cm_open_rfpvv_updated_by_fk')->references('id')->on('users');
        });

        Schema::create('consultant_management_rfp_resubmission_verifiers', function(Blueprint $table)
        {
            $table->bigIncrements('id');
            $table->unsignedInteger('consultant_management_open_rfp_id')->index('cm_rfp_resubmission_orfp_id_idx');
            $table->unsignedInteger('user_id')->index('cm_rfp_resubmission_verifiers_user_id_idx');
            $table->unsignedInteger('created_by');
            $table->unsignedInteger('updated_by');
            $table->timestamps();

            $table->unique(['consultant_management_open_rfp_id', 'user_id'], 'cm_rfp_resubmission_verifiers_unique');

            $table->foreign('consultant_management_open_rfp_id', 'cm_rfp_resubmission_orfp_id_fk')->references('id')->on('consultant_management_open_rfp');
            $table->foreign('user_id', 'cm_rfp_resubmission_verifiers_user_id_fk')->references('id')->on('users');
            $table->foreign('created_by', 'cm_rfp_resubmission_verifiers_created_by_fk')->references('id')->on('users');
            $table->foreign('updated_by', 'cm_rfp_resubmission_verifiers_updated_by_fk')->references('id')->on('users');
        });

        Schema::create('consultant_management_rfp_resubmission_verifier_versions', function(Blueprint $table)
        {
            $table->bigIncrements('id');
            $table->unsignedInteger('consultant_management_rfp_resubmission_verifier_id')->index('cm_rfp_resubmission_verifier_id_idx');
            $table->unsignedInteger('user_id')->index('cm_rfp_resubmission_user_id_idx');
            $table->integer('version')->index('cm_rfp_resubmission_version_idx');
            $table->integer('status')->index('cm_rfp_resubmission_status_idx');
            $table->text('remarks')->nullable();
            $table->unsignedInteger('created_by');
            $table->unsignedInteger('updated_by');
            $table->timestamps();

            $table->unique(['consultant_management_rfp_resubmission_verifier_id', 'user_id', 'version'], 'cm_rfp_resubmission_verifier_ver_unique');

            $table->foreign('consultant_management_rfp_resubmission_verifier_id', 'cm_rfp_resubmission_verifier_id_fk')->references('id')->on('consultant_management_rfp_resubmission_verifiers');
            $table->foreign('user_id', 'cm_rfp_resubmission_verifier_version_user_id_fk')->references('id')->on('users');
            $table->foreign('created_by', 'cm_rfp_resubmission_verifier_version_created_by_fk')->references('id')->on('users');
            $table->foreign('updated_by', 'cm_rfp_resubmission_verifier_version_updated_by_fk')->references('id')->on('users');
        });
    }

    public function down()
    {
        Schema::dropIfExists('consultant_management_rfp_resubmission_verifier_versions');
        Schema::dropIfExists('consultant_management_rfp_resubmission_verifiers');
        Schema::dropIfExists('consultant_management_open_rfp_verifier_versions');
        Schema::dropIfExists('consultant_management_open_rfp_verifiers');
        Schema::dropIfExists('consultant_management_open_rfp');
    }
}
