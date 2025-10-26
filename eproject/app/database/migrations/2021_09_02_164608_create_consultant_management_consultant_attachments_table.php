<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateConsultantManagementConsultantAttachmentsTable extends Migration
{
    public function up()
    {
        Schema::create('consultant_management_consultant_attachments', function(Blueprint $table)
        {
            $table->bigIncrements('id');
            $table->unsignedInteger('consultant_management_attachment_setting_id')->index('cmca_attachment_setting_id_idx');
            $table->unsignedInteger('vendor_category_rfp_id')->index('cmca_vcrfp_id_idx');
            $table->unsignedInteger('company_id')->index('cmca_company_id_idx');
            $table->text('remarks')->nullable();
            $table->unsignedInteger('created_by');
            $table->unsignedInteger('updated_by');
            $table->timestamps();

            $table->foreign('consultant_management_attachment_setting_id', 'cmca_attachment_setting_id_fk')->references('id')->on('consultant_management_attachment_settings');
            $table->foreign('created_by', 'cmca_created_by_fk')->references('id')->on('users');
            $table->foreign('updated_by', 'cmca_updated_by_fk')->references('id')->on('users');
        });

        Schema::create('consultant_management_consultant_rfp_attachments', function(Blueprint $table)
        {
            $table->bigIncrements('id');
            $table->unsignedInteger('consultant_management_rfp_attachment_setting_id')->index('cmcrfpa_rfp_attachment_setting_id_idx');
            $table->unsignedInteger('vendor_category_rfp_id')->index('cmcrfpa_vcrfp_id_idx');
            $table->unsignedInteger('company_id')->index('cmcrfpa_company_id_idx');
            $table->text('remarks')->nullable();
            $table->unsignedInteger('created_by');
            $table->unsignedInteger('updated_by');
            $table->timestamps();
            
            $table->foreign('consultant_management_rfp_attachment_setting_id', 'cmcrfpa_rfp_attachment_setting_id_fk')->references('id')->on('consultant_management_rfp_attachment_settings');
            $table->foreign('created_by', 'cmcrfpa_created_by_fk')->references('id')->on('users');
            $table->foreign('updated_by', 'cmcrfpa_updated_by_fk')->references('id')->on('users');
        });
    }

    public function down()
    {
        Schema::dropIfExists('consultant_management_consultant_rfp_attachments');
        Schema::dropIfExists('consultant_management_consultant_attachments');
    }
}
