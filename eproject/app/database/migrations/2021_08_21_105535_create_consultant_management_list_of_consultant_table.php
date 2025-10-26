<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateConsultantManagementListOfConsultantTable extends Migration
{
    public function up()
    {
        Schema::create('consultant_management_rfp_revisions', function(Blueprint $table)
        {
            $table->bigIncrements('id');
            $table->unsignedInteger('vendor_category_rfp_id')->index('consultant_management_rfp_rev_vcrfp_id_idx');
            $table->unsignedInteger('revision')->index('consultant_management_rfp_rev_version_idx');
            $table->unsignedInteger('created_by');
            $table->unsignedInteger('updated_by');
            $table->timestamps();

            $table->unique(['vendor_category_rfp_id', 'revision'], 'consultant_management_rfp_rev_unique');

            $table->foreign('vendor_category_rfp_id', 'consultant_management_rfp_rev_vcrfp_id_fk')->references('id')->on('consultant_management_vendor_categories_rfp');
            $table->foreign('created_by', 'consultant_management_rfp_rev_created_by_fk')->references('id')->on('users');
            $table->foreign('updated_by', 'consultant_management_rfp_rev_updated_by_fk')->references('id')->on('users');
        });

        Schema::create('consultant_management_list_of_consultants', function(Blueprint $table)
        {
            $table->bigIncrements('id');
            $table->unsignedInteger('consultant_management_rfp_revision_id')->index('consultant_management_loc_rfp_rev_id_idx');
            $table->decimal('proposed_fee', 19, 5)->default(0);
            $table->dateTime('calling_rfp_date');
            $table->dateTime('closing_rfp_date');
            $table->text('remarks')->nullable();
            $table->integer('status')->index('consultant_management_loc_status_idx');
            $table->unsignedInteger('created_by');
            $table->unsignedInteger('updated_by');
            $table->timestamps();

            $table->unique(['consultant_management_rfp_revision_id']);

            $table->foreign('consultant_management_rfp_revision_id', 'consultant_management_loc_rfp_rev_id_fk')->references('id')->on('consultant_management_rfp_revisions');
            $table->foreign('created_by', 'consultant_management_loc_created_by_fk')->references('id')->on('users');
            $table->foreign('updated_by', 'consultant_management_loc_updated_by_fk')->references('id')->on('users');
        });

        Schema::create('consultant_management_list_of_consultant_verifiers', function(Blueprint $table)
        {
            $table->bigIncrements('id');
            $table->unsignedInteger('consultant_management_list_of_consultant_id')->index('consultant_management_loc_verifiers_loc_id_idx');
            $table->unsignedInteger('user_id')->index('consultant_management_loc_verifiers_user_id_idx');
            $table->unsignedInteger('created_by');
            $table->unsignedInteger('updated_by');
            $table->timestamps();

            $table->unique(['consultant_management_list_of_consultant_id', 'user_id'], 'consultant_management_loc_verifiers_unique');

            $table->foreign('consultant_management_list_of_consultant_id', 'consultant_management_loc_verifiers_loc_id_fk')->references('id')->on('consultant_management_list_of_consultants');
            $table->foreign('user_id', 'consultant_management_loc_verifiers_user_id_fk')->references('id')->on('users');
            $table->foreign('created_by', 'consultant_management_locv_created_by_fk')->references('id')->on('users');
            $table->foreign('updated_by', 'consultant_management_locv_updated_by_fk')->references('id')->on('users');
        });

        Schema::create('consultant_management_loc_verifier_versions', function(Blueprint $table)
        {
            $table->bigIncrements('id');
            $table->unsignedInteger('consultant_management_list_of_consultant_verifier_id')->index('consultant_management_locvv_verifier_id_idx');
            $table->unsignedInteger('user_id')->index('consultant_management_locvv_user_id_idx');
            $table->integer('version')->index('consultant_management_locvv_version_idx');
            $table->integer('status')->index('consultant_management_locvv_status_idx');
            $table->text('remarks')->nullable();
            $table->unsignedInteger('created_by');
            $table->unsignedInteger('updated_by');
            $table->timestamps();

            $table->unique(['consultant_management_list_of_consultant_verifier_id', 'user_id', 'version'], 'consultant_management_locvv_unique');

            $table->foreign('consultant_management_list_of_consultant_verifier_id', 'consultant_management_locvv_verifier_id_fk')->references('id')->on('consultant_management_list_of_consultant_verifiers');
            $table->foreign('user_id', 'consultant_management_locvv_user_id_fk')->references('id')->on('users');
            $table->foreign('created_by', 'consultant_management_locvv_created_by_fk')->references('id')->on('users');
            $table->foreign('updated_by', 'consultant_management_locvv_updated_by_fk')->references('id')->on('users');
        });

        Schema::create('consultant_management_list_of_consultant_companies', function(Blueprint $table)
        {
            $table->bigIncrements('id');
            $table->unsignedInteger('consultant_management_list_of_consultant_id')->index('consultant_management_locc_loc_id_idx');
            $table->unsignedInteger('company_id')->index('consultant_management_locc_company_id_idx');
            $table->integer('status')->index('consultant_management_locc_status_idx');
            $table->unsignedInteger('created_by');
            $table->unsignedInteger('updated_by');
            $table->timestamps();

            $table->unique(['consultant_management_list_of_consultant_id', 'company_id'], 'consultant_management_locc_unique');

            $table->foreign('consultant_management_list_of_consultant_id', 'consultant_management_locc_loc_id_fk')->references('id')->on('consultant_management_list_of_consultants');
            $table->foreign('company_id', 'consultant_management_locc_company_id_fk')->references('id')->on('companies');
            $table->foreign('created_by', 'consultant_management_locc_created_by_fk')->references('id')->on('users');
            $table->foreign('updated_by', 'consultant_management_locc_updated_by_fk')->references('id')->on('users');
        });
    }

    public function down()
    {
        Schema::dropIfExists('consultant_management_list_of_consultant_companies');
        Schema::dropIfExists('consultant_management_loc_verifier_versions');
        Schema::dropIfExists('consultant_management_list_of_consultant_verifiers');
        Schema::dropIfExists('consultant_management_list_of_consultants');
        Schema::dropIfExists('consultant_management_rfp_revisions');
    }
}
