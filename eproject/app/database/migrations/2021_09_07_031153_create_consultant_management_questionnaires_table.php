<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateConsultantManagementQuestionnairesTable extends Migration
{
    public function up()
    {
        Schema::create('consultant_management_questionnaires', function(Blueprint $table)
        {
            $table->bigIncrements('id');
            $table->unsignedInteger('consultant_management_contract_id')->index('cmq_contract_id_idx');
            $table->text('question');
            $table->string('type')->default('text');
            $table->boolean('required')->default(true);
            $table->boolean('with_attachment')->default(false);
            $table->unsignedInteger('created_by');
            $table->unsignedInteger('updated_by');
            $table->timestamps();

            $table->foreign('consultant_management_contract_id', 'cmq_contract_id_fk')->references('id')->on('consultant_management_contracts');
            $table->foreign('created_by', 'cmq_created_by_fk')->references('id')->on('users');
            $table->foreign('updated_by', 'cmq_updated_by_fk')->references('id')->on('users');
        });

        Schema::create('consultant_management_questionnaire_options', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('consultant_management_questionnaire_id')->index('cmqo_questionnaire_id');
            $table->text('text');
            $table->string('value')->nullable();
            $table->integer('order')->nullable();
            $table->unsignedInteger('created_by');
            $table->unsignedInteger('updated_by');
            $table->timestamps();

            $table->foreign('consultant_management_questionnaire_id', 'cmqo_questionnaire_id_fk')->references('id')->on('consultant_management_questionnaires');
            $table->foreign('created_by', 'cmqo_created_by_fk')->references('id')->on('users');
            $table->foreign('updated_by', 'cmqo_updated_by_fk')->references('id')->on('users');
        });

        Schema::create('consultant_management_exclude_questionnaires', function(Blueprint $table)
        {
            $table->bigIncrements('id');
            $table->unsignedInteger('consultant_management_questionnaire_id')->index('cmeq_questionnaire_id_idx');
            $table->unsignedInteger('vendor_category_rfp_id')->index('cmeq_vcrfp_id_idx');
            $table->unsignedInteger('company_id')->index('cmeq_company_id_idx');
            $table->unsignedInteger('created_by');
            $table->unsignedInteger('updated_by');
            $table->timestamps();

            $table->unique(['consultant_management_questionnaire_id', 'vendor_category_rfp_id', 'company_id'], 'cmeq_questionnaire_id_unique');

            $table->foreign('consultant_management_questionnaire_id', 'cmeq_questionnaire_id_fk')->references('id')->on('consultant_management_questionnaires');
            $table->foreign('vendor_category_rfp_id', 'cmeq_vcrfp_id_fk')->references('id')->on('consultant_management_vendor_categories_rfp');
            $table->foreign('company_id', 'cmeq_company_id_fk')->references('id')->on('companies');
            $table->foreign('created_by', 'cmeq_created_by_fk')->references('id')->on('users');
            $table->foreign('updated_by', 'cmeq_updated_by_fk')->references('id')->on('users');
        });

        Schema::create('consultant_management_rfp_questionnaires', function(Blueprint $table)
        {
            $table->bigIncrements('id');
            $table->unsignedInteger('vendor_category_rfp_id')->index('cmrq_vcrfp_id_idx');
            $table->unsignedInteger('company_id')->index('cmrq_company_id_idx');
            $table->text('question');
            $table->string('type')->default('text');
            $table->boolean('required')->default(true);
            $table->boolean('with_attachment')->default(false);
            $table->unsignedInteger('created_by');
            $table->unsignedInteger('updated_by');
            $table->timestamps();

            $table->foreign('vendor_category_rfp_id', 'cmrq_vcrfp_id_fk')->references('id')->on('consultant_management_vendor_categories_rfp');
            $table->foreign('company_id', 'cmrq_company_id_fk')->references('id')->on('companies');
            $table->foreign('created_by', 'cmrq_created_by_fk')->references('id')->on('users');
            $table->foreign('updated_by', 'cmrq_updated_by_fk')->references('id')->on('users');
        });

        Schema::create('consultant_management_rfp_questionnaire_options', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('consultant_management_rfp_questionnaire_id')->index('cmrqo_questionnaire_id');
            $table->text('text');
            $table->string('value')->nullable();
            $table->integer('order')->nullable();
            $table->unsignedInteger('created_by');
            $table->unsignedInteger('updated_by');
            $table->timestamps();

            $table->foreign('consultant_management_rfp_questionnaire_id', 'cmrqo_questionnaire_id_fk')->references('id')->on('consultant_management_rfp_questionnaires');
            $table->foreign('created_by', 'cmrqo_created_by_fk')->references('id')->on('users');
            $table->foreign('updated_by', 'cmrqo_updated_by_fk')->references('id')->on('users');
        });

        Schema::create('consultant_management_consultant_questionnaires', function(Blueprint $table)
        {
            $table->bigIncrements('id');
            $table->unsignedInteger('vendor_category_rfp_id')->index('cmcq_vcrfp_id_idx');
            $table->unsignedInteger('company_id')->index('cmcq_company_id_idx');
            $table->integer('status');
            $table->dateTime('published_date')->nullable();
            $table->dateTime('unpublished_date')->nullable();
            $table->unsignedInteger('created_by');
            $table->unsignedInteger('updated_by');
            $table->timestamps();

            $table->unique(['vendor_category_rfp_id', 'company_id'], 'cm_consultant_questionnaire_id_unique');

            $table->foreign('vendor_category_rfp_id', 'cmcq_vcrfp_id_fk')->references('id')->on('consultant_management_vendor_categories_rfp');
            $table->foreign('company_id', 'cmcq_company_id_fk')->references('id')->on('companies');
            $table->foreign('created_by', 'cmcq_created_by_fk')->references('id')->on('users');
            $table->foreign('updated_by', 'cmcq_updated_by_fk')->references('id')->on('users');
        });

        Schema::create('consultant_management_consultant_questionnaire_replies', function(Blueprint $table)
        {
            $table->bigIncrements('id');
            $table->unsignedInteger('consultant_management_questionnaire_id')->index('cmcqr_questionnaire_id_idx');
            $table->unsignedInteger('consultant_management_consultant_questionnaire_id')->index('cmcqr_consultant_questionnaire_id_idx');
            $table->text('text')->nullable();
            $table->unsignedInteger('consultant_management_questionnaire_option_id')->nullable()->index('cmcqr_option_id_idx');
            $table->unsignedInteger('created_by');
            $table->unsignedInteger('updated_by');
            $table->timestamps();

            $table->foreign('consultant_management_questionnaire_id', 'cmcqr_questionnaire_id_fk')->references('id')->on('consultant_management_questionnaires');
            $table->foreign('consultant_management_consultant_questionnaire_id', 'cmcqr_consultant_questionnaire_id_fk')->references('id')->on('consultant_management_consultant_questionnaires');
            $table->foreign('consultant_management_questionnaire_option_id', 'cmcqr_option_id_fk')->references('id')->on('consultant_management_questionnaire_options');
            $table->foreign('created_by', 'cmcqr_created_by_fk')->references('id')->on('users');
            $table->foreign('updated_by', 'cmcqr_updated_by_fk')->references('id')->on('users');
        });

        Schema::create('consultant_management_consultant_reply_attachments', function(Blueprint $table)
        {
            $table->bigIncrements('id');
            $table->unsignedInteger('consultant_management_questionnaire_id')->index('cmcqra_questionnaire_id_idx');
            $table->unsignedInteger('consultant_management_consultant_questionnaire_id')->index('cmcqra_consultant_questionnaire_id_idx');
            $table->unsignedInteger('created_by');
            $table->unsignedInteger('updated_by');
            $table->timestamps();

            $table->unique(['consultant_management_questionnaire_id', 'consultant_management_consultant_questionnaire_id'], 'cmcqra_unique');

            $table->foreign('consultant_management_questionnaire_id', 'cmcqra_questionnaire_id_fk')->references('id')->on('consultant_management_questionnaires');
            $table->foreign('consultant_management_consultant_questionnaire_id', 'cmcqra_consultant_questionnaire_id_fk')->references('id')->on('consultant_management_consultant_questionnaires');
            $table->foreign('created_by', 'cmcqra_created_by_fk')->references('id')->on('users');
            $table->foreign('updated_by', 'cmcqra_updated_by_fk')->references('id')->on('users');
        });

        Schema::create('consultant_management_consultant_rfp_questionnaire_replies', function(Blueprint $table)
        {
            $table->bigIncrements('id');
            $table->unsignedInteger('consultant_management_rfp_questionnaire_id')->index('cmcrqr_questionnaire_id_idx');
            $table->unsignedInteger('consultant_management_consultant_questionnaire_id')->index('cmcrqr_consultant_questionnaire_id_idx');
            $table->text('text')->nullable();
            $table->unsignedInteger('consultant_management_rfp_questionnaire_option_id')->nullable()->index('cmcrqr_option_id_idx');
            $table->unsignedInteger('created_by');
            $table->unsignedInteger('updated_by');
            $table->timestamps();

            $table->foreign('consultant_management_rfp_questionnaire_id', 'cmcrqr_questionnaire_id_fk')->references('id')->on('consultant_management_rfp_questionnaires');
            $table->foreign('consultant_management_consultant_questionnaire_id', 'cmcrqr_consultant_questionnaire_id_fk')->references('id')->on('consultant_management_consultant_questionnaires');
            $table->foreign('consultant_management_rfp_questionnaire_option_id', 'cmcrqr_option_id_fk')->references('id')->on('consultant_management_rfp_questionnaire_options');
            $table->foreign('created_by', 'cmcrqr_created_by_fk')->references('id')->on('users');
            $table->foreign('updated_by', 'cmcrqr_updated_by_fk')->references('id')->on('users');
        });

        Schema::create('consultant_management_consultant_rfp_reply_attachments', function(Blueprint $table)
        {
            $table->bigIncrements('id');
            $table->unsignedInteger('consultant_management_rfp_questionnaire_id')->index('cmcrqra_questionnaire_id_idx');
            $table->unsignedInteger('consultant_management_consultant_questionnaire_id')->index('cmcrqra_consultant_questionnaire_id_idx');
            $table->unsignedInteger('created_by');
            $table->unsignedInteger('updated_by');
            $table->timestamps();

            $table->unique(['consultant_management_rfp_questionnaire_id', 'consultant_management_consultant_questionnaire_id'], 'cmcrqra_unique');

            $table->foreign('consultant_management_rfp_questionnaire_id', 'cmcrqra_questionnaire_id_fk')->references('id')->on('consultant_management_rfp_questionnaires');
            $table->foreign('consultant_management_consultant_questionnaire_id', 'cmcrqra_consultant_questionnaire_id_fk')->references('id')->on('consultant_management_consultant_questionnaires');
            $table->foreign('created_by', 'cmcrqra_created_by_fk')->references('id')->on('users');
            $table->foreign('updated_by', 'cmcrqra_updated_by_fk')->references('id')->on('users');
        });
    }

    public function down()
    {
        Schema::dropIfExists('consultant_management_consultant_rfp_reply_attachments');
        Schema::dropIfExists('consultant_management_consultant_reply_attachments');
        Schema::dropIfExists('consultant_management_consultant_rfp_questionnaire_replies');
        Schema::dropIfExists('consultant_management_consultant_questionnaire_replies');
        Schema::dropIfExists('consultant_management_consultant_questionnaires');
        Schema::dropIfExists('consultant_management_rfp_questionnaire_options');
        Schema::dropIfExists('consultant_management_rfp_questionnaires');
        Schema::dropIfExists('consultant_management_exclude_questionnaires');
        Schema::dropIfExists('consultant_management_questionnaire_options');
        Schema::dropIfExists('consultant_management_questionnaires');
    }
}
