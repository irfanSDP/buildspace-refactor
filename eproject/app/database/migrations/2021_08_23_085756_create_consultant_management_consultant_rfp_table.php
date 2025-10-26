<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateConsultantManagementConsultantRfpTable extends Migration
{
    public function up()
    {
        Schema::create('consultant_management_consultant_rfp', function(Blueprint $table)
        {
            $table->bigIncrements('id');
            $table->unsignedInteger('consultant_management_rfp_revision_id')->index('cm_consultant_rfp_rfp_rev_id_idx');
            $table->unsignedInteger('company_id')->index('cm_consultant_rfp_company_id_idx');
            $table->boolean('awarded')->default(false)->index('cm_consultant_rfp_awarded_idx');
            $table->unsignedInteger('created_by');
            $table->unsignedInteger('updated_by');
            $table->timestamps();

            $table->unique(['consultant_management_rfp_revision_id', 'company_id']);

            $table->foreign('consultant_management_rfp_revision_id', 'cm_consultant_rfp_rfp_rev_id_fk')->references('id')->on('consultant_management_rfp_revisions');
            $table->foreign('company_id', 'cm_consultant_rfp_company_id_fk')->references('id')->on('companies');
            $table->foreign('created_by', 'cm_consultant_rfp_created_by_fk')->references('id')->on('users');
            $table->foreign('updated_by', 'cm_consultant_rfp_updated_by_fk')->references('id')->on('users');
        });

        Schema::create('consultant_management_consultant_rfp_proposed_fees', function(Blueprint $table)
        {
            $table->bigIncrements('id');
            $table->unsignedInteger('consultant_management_consultant_rfp_id')->index('cm_consultant_rfp_crfp_id_idx');
            $table->unsignedInteger('consultant_management_subsidiary_id')->index('cm_consultant_rfp_subsidiary_id_idx');
            $table->decimal('proposed_fee_percentage', 5, 2)->default(0);
            $table->unsignedInteger('created_by');
            $table->unsignedInteger('updated_by');
            $table->timestamps();

            $table->unique(['consultant_management_consultant_rfp_id', 'consultant_management_subsidiary_id']);

            $table->foreign('consultant_management_consultant_rfp_id', 'cm_consultant_rfp_crfp_id_fk')->references('id')->on('consultant_management_consultant_rfp');
            $table->foreign('consultant_management_subsidiary_id', 'cm_consultant_rfp_subsidiary_id_fk')->references('id')->on('consultant_management_subsidiaries');
            $table->foreign('created_by', 'cm_consultant_rfp_created_by_fk')->references('id')->on('users');
            $table->foreign('updated_by', 'cm_consultant_rfp_updated_by_fk')->references('id')->on('users');
        });

        Schema::create('consultant_management_consultant_rfp_common_information', function(Blueprint $table)
        {
            $table->bigIncrements('id');
            $table->unsignedInteger('consultant_management_consultant_rfp_id')->index('cm_consultant_rfp_cinfo_crfp_id_idx');
            $table->string('name_in_loa', 255);
            $table->text('remarks')->nullable();
            $table->string('contact_name', 255);
            $table->string('contact_number', 50);
            $table->string('contact_email', 255)->nullable();
            $table->unsignedInteger('created_by');
            $table->unsignedInteger('updated_by');
            $table->timestamps();

            $table->unique(['consultant_management_consultant_rfp_id']);

            $table->foreign('consultant_management_consultant_rfp_id', 'cm_consultant_rfp_cinfo_crfp_id_fk')->references('id')->on('consultant_management_consultant_rfp');
            $table->foreign('created_by', 'cm_consultant_rfp_cinfo_created_by_fk')->references('id')->on('users');
            $table->foreign('updated_by', 'cm_consultant_rfp_cinfo_updated_by_fk')->references('id')->on('users');
        });
    }

    public function down()
    {
        Schema::dropIfExists('consultant_management_consultant_rfp_common_information');
        Schema::dropIfExists('consultant_management_consultant_rfp_proposed_fees');
        Schema::dropIfExists('consultant_management_consultant_rfp');
    }
}
