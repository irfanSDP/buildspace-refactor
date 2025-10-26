<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateConsultantManagementApprovalDocumentsTable extends Migration
{
    public function up()
    {
        Schema::create('consultant_management_approval_documents', function(Blueprint $table)
        {
            $table->bigIncrements('id');
            $table->unsignedInteger('vendor_category_rfp_id')->unique()->index('cmad_vcrfp_id_idx');
            $table->string('document_reference_no', 100)->unique();
            $table->integer('status')->index();
            $table->unsignedInteger('created_by');
            $table->unsignedInteger('updated_by');
            $table->timestamps();

            $table->foreign('vendor_category_rfp_id', 'cmad_vcrfp_id_fk')->references('id')->on('consultant_management_vendor_categories_rfp');
            $table->foreign('created_by', 'cmad_created_by_fk')->references('id')->on('users');
            $table->foreign('updated_by', 'cmad_updated_by_fk')->references('id')->on('users');
        });

        Schema::create('consultant_management_approval_document_verifiers', function(Blueprint $table)
        {
            $table->bigIncrements('id');
            $table->unsignedInteger('consultant_management_approval_document_id')->index('cmadv_approval_document_id_idx');
            $table->unsignedInteger('user_id')->index('cmadv_user_id_idx');
            $table->unsignedInteger('created_by');
            $table->unsignedInteger('updated_by');
            $table->timestamps();

            $table->softDeletes();

            $table->unique(['consultant_management_approval_document_id', 'user_id', 'deleted_at'], 'cmad_verifiers_unique');

            $table->foreign('consultant_management_approval_document_id', 'cmadv_approval_document_id_fk')->references('id')->on('consultant_management_approval_documents');
            $table->foreign('user_id', 'cmadv_user_id_fk')->references('id')->on('users');
            $table->foreign('created_by', 'cmadv_created_by_fk')->references('id')->on('users');
            $table->foreign('updated_by', 'cmadv_updated_by_fk')->references('id')->on('users');
        });

        Schema::create('consultant_management_approval_document_verifier_versions', function(Blueprint $table)
        {
            $table->bigIncrements('id');
            $table->unsignedInteger('consultant_management_approval_document_verifier_id')->index('cmadvv_verifier_id_idx');
            $table->unsignedInteger('user_id')->index('cmadvv_user_id_idx');
            $table->integer('version')->index('cmadvv_version_idx');
            $table->integer('status')->index('cmadvv_status_idx');
            $table->text('remarks')->nullable();
            $table->unsignedInteger('created_by');
            $table->unsignedInteger('updated_by');
            $table->timestamps();

            $table->unique(['consultant_management_approval_document_verifier_id', 'user_id', 'version'], 'cmadv_version_unique');

            $table->foreign('consultant_management_approval_document_verifier_id', 'cmadvv_verifier_id_fk')->references('id')->on('consultant_management_approval_document_verifiers');
            $table->foreign('user_id', 'cmadvv_user_id_fk')->references('id')->on('users');
            $table->foreign('created_by', 'cmadvv_created_by_fk')->references('id')->on('users');
            $table->foreign('updated_by', 'cmadvv_updated_by_fk')->references('id')->on('users');
        });

        Schema::create('consultant_management_approval_document_section_a', function(Blueprint $table)
        {
            $table->bigIncrements('id');
            $table->unsignedInteger('consultant_management_approval_document_id')->unique()->index('cmadsa_approval_document_id_idx');
            $table->integer('approving_authority')->index('cmadsa_approving_authority_idx');
            $table->unsignedInteger('created_by')->nullable();
            $table->unsignedInteger('updated_by')->nullable();
            $table->timestamps();

            $table->foreign('consultant_management_approval_document_id', 'cmadsa_approval_document_id_fk')->references('id')->on('consultant_management_approval_documents');
            $table->foreign('created_by', 'cmadsa_created_by_fk')->references('id')->on('users');
            $table->foreign('updated_by', 'cmadsa_updated_by_fk')->references('id')->on('users');
        });

        Schema::create('consultant_management_approval_document_section_b', function(Blueprint $table)
        {
            $table->bigIncrements('id');
            $table->unsignedInteger('consultant_management_approval_document_id')->unique()->index('cmadsb_approval_document_id_idx');
            $table->text('project_brief')->nullable();
            $table->unsignedInteger('created_by')->nullable();
            $table->unsignedInteger('updated_by')->nullable();
            $table->timestamps();

            $table->foreign('consultant_management_approval_document_id', 'cmadsb_approval_document_id_fk')->references('id')->on('consultant_management_approval_documents');
            $table->foreign('created_by', 'cmadsb_created_by_fk')->references('id')->on('users');
            $table->foreign('updated_by', 'cmadsb_updated_by_fk')->references('id')->on('users');
        });

        Schema::create('consultant_management_approval_document_section_c', function(Blueprint $table)
        {
            $table->bigIncrements('id');
            $table->unsignedInteger('consultant_management_approval_document_id')->unique()->index('cmadsc_approval_document_id_idx');
            $table->unsignedInteger('created_by')->nullable();
            $table->unsignedInteger('updated_by')->nullable();
            $table->timestamps();

            $table->foreign('consultant_management_approval_document_id', 'cmadsc_approval_document_id_fk')->references('id')->on('consultant_management_approval_documents');
            $table->foreign('created_by', 'cmadsc_created_by_fk')->references('id')->on('users');
            $table->foreign('updated_by', 'cmadsc_updated_by_fk')->references('id')->on('users');
        });

        Schema::create('consultant_management_approval_document_section_d', function(Blueprint $table)
        {
            $table->bigIncrements('id');
            $table->unsignedInteger('consultant_management_approval_document_id')->unique()->index('cmadsd_approval_document_id_idx');
            $table->unsignedInteger('created_by')->nullable();
            $table->unsignedInteger('updated_by')->nullable();
            $table->timestamps();

            $table->foreign('consultant_management_approval_document_id', 'cmadsd_approval_document_id_fk')->references('id')->on('consultant_management_approval_documents');
            $table->foreign('created_by', 'cmadsd_created_by_fk')->references('id')->on('users');
            $table->foreign('updated_by', 'cmadsd_updated_by_fk')->references('id')->on('users');
        });

        Schema::create('consultant_management_approval_document_section_e', function(Blueprint $table)
        {
            $table->bigIncrements('id');
            $table->unsignedInteger('consultant_management_approval_document_id')->unique()->index('cmadse_approval_document_id_idx');
            $table->unsignedInteger('created_by')->nullable();
            $table->unsignedInteger('updated_by')->nullable();
            $table->timestamps();

            $table->foreign('consultant_management_approval_document_id', 'cmadse_approval_document_id_fk')->references('id')->on('consultant_management_approval_documents');
            $table->foreign('created_by', 'cmadse_created_by_fk')->references('id')->on('users');
            $table->foreign('updated_by', 'cmadse_updated_by_fk')->references('id')->on('users');
        });

        Schema::create('consultant_management_approval_document_section_appendix', function(Blueprint $table)
        {
            $table->bigIncrements('id');
            $table->unsignedInteger('consultant_management_approval_document_id')->unique()->index('cmadsapx_approval_document_id_idx');
            $table->unsignedInteger('created_by')->nullable();
            $table->unsignedInteger('updated_by')->nullable();
            $table->timestamps();

            $table->foreign('consultant_management_approval_document_id', 'cmadsapx_approval_document_id_fk')->references('id')->on('consultant_management_approval_documents');
            $table->foreign('created_by', 'cmadsapx_created_by_fk')->references('id')->on('users');
            $table->foreign('updated_by', 'cmadsapx_updated_by_fk')->references('id')->on('users');
        });
    }

    public function down()
    {
        Schema::dropIfExists('consultant_management_approval_document_section_appendix');
        Schema::dropIfExists('consultant_management_approval_document_section_e');
        Schema::dropIfExists('consultant_management_approval_document_section_d');
        Schema::dropIfExists('consultant_management_approval_document_section_c');
        Schema::dropIfExists('consultant_management_approval_document_section_b');
        Schema::dropIfExists('consultant_management_approval_document_section_a');
        Schema::dropIfExists('consultant_management_approval_document_verifier_versions');
        Schema::dropIfExists('consultant_management_approval_document_verifiers');
        Schema::dropIfExists('consultant_management_approval_documents');
    }
}
