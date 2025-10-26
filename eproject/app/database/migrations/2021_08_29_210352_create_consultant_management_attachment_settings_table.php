<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateConsultantManagementAttachmentSettingsTable extends Migration
{
    public function up()
    {
        Schema::create('consultant_management_attachment_settings', function(Blueprint $table)
        {
            $table->bigIncrements('id');
            $table->unsignedInteger('consultant_management_contract_id')->index('cm_att_stt_contract_id_idx');
            $table->string('title', 255);
            $table->boolean('mandatory')->default(false)->index('cm_att_stt_mandatory_idx');
            $table->unsignedInteger('created_by');
            $table->unsignedInteger('updated_by');
            $table->timestamps();

            $table->unique(['consultant_management_contract_id', 'title'], 'cm_att_stt_idx_unique');

            $table->foreign('consultant_management_contract_id', 'cm_att_stt_contract_id_fk')->references('id')->on('consultant_management_contracts');
            $table->foreign('created_by', 'cm_att_stt_created_by_fk')->references('id')->on('users');
            $table->foreign('updated_by', 'cm_att_stt_updated_by_fk')->references('id')->on('users');
        });

        Schema::create('consultant_management_exclude_attachment_settings', function(Blueprint $table)
        {
            $table->bigIncrements('id');
            $table->unsignedInteger('consultant_management_attachment_setting_id')->index('cm_exclude_att_stt_cmas_id_idx');
            $table->unsignedInteger('vendor_category_rfp_id')->index('cm_exclude_att_stt_vcrfp_id_idx');
            $table->unsignedInteger('created_by');
            $table->unsignedInteger('updated_by');
            $table->timestamps();

            $table->unique(['consultant_management_attachment_setting_id', 'vendor_category_rfp_id'], 'cm_exclude_att_stt_idx_unique');

            $table->foreign('consultant_management_attachment_setting_id', 'cm_exclude_att_stt_cmas_id_fk')->references('id')->on('consultant_management_attachment_settings');
            $table->foreign('vendor_category_rfp_id', 'cm_exclude_att_stt_vcrfp_id_fk')->references('id')->on('consultant_management_vendor_categories_rfp');
            $table->foreign('created_by', 'cm_exclude_att_stt_created_by_fk')->references('id')->on('users');
            $table->foreign('updated_by', 'cm_exclude_att_stt_updated_by_fk')->references('id')->on('users');
        });

        Schema::create('consultant_management_rfp_attachment_settings', function(Blueprint $table)
        {
            $table->bigIncrements('id');
            $table->unsignedInteger('vendor_category_rfp_id')->index('cm_rfp_att_stt_vcrfp_id_idx');
            $table->string('title', 255);
            $table->boolean('mandatory')->default(false)->index('cm_rfp_att_stt_mandatory_idx');
            $table->unsignedInteger('created_by');
            $table->unsignedInteger('updated_by');
            $table->timestamps();

            $table->unique(['vendor_category_rfp_id', 'title'], 'cm_rfp_att_stt_idx_unique');

            $table->foreign('vendor_category_rfp_id', 'cm_rfp_att_stt_vcrfp_id_fk')->references('id')->on('consultant_management_vendor_categories_rfp');
            $table->foreign('created_by', 'cm_rfp_att_stt_created_by_fk')->references('id')->on('users');
            $table->foreign('updated_by', 'cm_rfp_att_stt_updated_by_fk')->references('id')->on('users');
        });
    }

    public function down()
    {
        Schema::dropIfExists('consultant_management_rfp_attachment_settings');
        Schema::dropIfExists('consultant_management_exclude_attachment_settings');
        Schema::dropIfExists('consultant_management_attachment_settings');
    }
}
