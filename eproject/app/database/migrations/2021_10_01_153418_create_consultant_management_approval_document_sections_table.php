<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateConsultantManagementApprovalDocumentSectionsTable extends Migration
{
    public function up()
    {
        Schema::create('consultant_management_section_c_details', function(Blueprint $table)
        {
            $table->bigIncrements('id');
            $table->unsignedInteger('consultant_management_approval_document_section_c_id')->index('cmad_section_c_details_id_idx');
            $table->unsignedInteger('consultant_management_subsidiary_id')->index('cmad_section_c_details_subsidiary_id_idx');
            $table->unsignedInteger('company_id')->index('cmad_section_c_details_company_id_idx');
            $table->text('remarks')->nullable();
            $table->unsignedInteger('created_by');
            $table->unsignedInteger('updated_by');
            $table->timestamps();

            $table->unique(['consultant_management_approval_document_section_c_id', 'consultant_management_subsidiary_id', 'company_id'], 'cmad_section_c_details_unique');

            $table->foreign('consultant_management_approval_document_section_c_id', 'cmad_section_c_details_id_fk')->references('id')->on('consultant_management_approval_document_section_c');
            $table->foreign('consultant_management_subsidiary_id', 'cmad_section_c_details_subsidiary_id_fk')->references('id')->on('consultant_management_subsidiaries');
            $table->foreign('company_id', 'cmad_section_c_details_company_id_fk')->references('id')->on('companies');
            $table->foreign('created_by', 'cmad_section_c_details_created_by_fk')->references('id')->on('users');
            $table->foreign('updated_by', 'cmad_section_c_details_updated_by_fk')->references('id')->on('users');
        });

        Schema::create('consultant_management_section_d_details', function(Blueprint $table)
        {
            $table->bigIncrements('id');
            $table->unsignedInteger('consultant_management_approval_document_section_d_id')->index('cmad_section_d_details_id_idx');
            $table->unsignedInteger('company_id')->index('cmad_section_d_details_company_id_idx');
            $table->text('scope_of_services')->nullable();
            $table->unsignedInteger('created_by');
            $table->unsignedInteger('updated_by');
            $table->timestamps();

            $table->unique(['consultant_management_approval_document_section_d_id', 'company_id'], 'cmad_section_d_details_unique');

            $table->foreign('consultant_management_approval_document_section_d_id', 'cmad_section_d_details_id_fk')->references('id')->on('consultant_management_approval_document_section_d');
            $table->foreign('company_id', 'cmad_section_d_details_company_id_fk')->references('id')->on('companies');
            $table->foreign('created_by', 'cmad_section_d_details_created_by_fk')->references('id')->on('users');
            $table->foreign('updated_by', 'cmad_section_d_details_updated_by_fk')->references('id')->on('users');
        });

        Schema::create('consultant_management_section_d_service_fees', function(Blueprint $table)
        {
            $table->bigIncrements('id');
            $table->unsignedInteger('consultant_management_approval_document_section_d_id')->index('cmad_section_d_service_fees_id_idx');
            $table->unsignedInteger('consultant_management_subsidiary_id')->index('cmad_section_d_service_fees_subsidiary_id_idx');
            $table->unsignedInteger('company_id')->index('cmad_section_d_service_fees_company_id_idx');
            $table->string('board_scale_of_fee')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedInteger('created_by');
            $table->unsignedInteger('updated_by');
            $table->timestamps();

            $table->unique(['consultant_management_approval_document_section_d_id', 'consultant_management_subsidiary_id', 'company_id'], 'cmad_section_d_service_fees_unique');

            $table->foreign('consultant_management_approval_document_section_d_id', 'cmad_section_d_service_fees_id_fk')->references('id')->on('consultant_management_approval_document_section_d');
            $table->foreign('consultant_management_subsidiary_id', 'cmad_section_d_service_fees_subsidiary_id_fk')->references('id')->on('consultant_management_subsidiaries');
            $table->foreign('company_id', 'cmad_section_d_service_fees_company_id_fk')->references('id')->on('companies');
            $table->foreign('created_by', 'cmad_section_d_service_fees_created_by_fk')->references('id')->on('users');
            $table->foreign('updated_by', 'cmad_section_d_service_fees_updated_by_fk')->references('id')->on('users');
        });

        Schema::create('consultant_management_section_appendix_details', function(Blueprint $table)
        {
            $table->bigIncrements('id');
            $table->unsignedInteger('consultant_management_approval_document_section_appendix_id')->index('cmad_section_appendix_details_id_idx');
            $table->string('title');
            $table->string('attachment_filename')->nullable();
            $table->unsignedInteger('created_by');
            $table->unsignedInteger('updated_by');
            $table->timestamps();

            $table->foreign('consultant_management_approval_document_section_appendix_id', 'cmad_section_appendix_details_id_fk')->references('id')->on('consultant_management_approval_document_section_appendix');
            $table->foreign('created_by', 'cmad_section_appendix_details_created_by_fk')->references('id')->on('users');
            $table->foreign('updated_by', 'cmad_section_appendix_details_updated_by_fk')->references('id')->on('users');
        });
    }

    public function down()
    {
        Schema::dropIfExists('consultant_management_section_c_details');
        Schema::dropIfExists('consultant_management_section_d_details');
        Schema::dropIfExists('consultant_management_section_d_service_fees');
        Schema::dropIfExists('consultant_management_section_appendix_details');
    }
}
