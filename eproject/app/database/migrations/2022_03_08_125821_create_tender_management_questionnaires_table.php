<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTenderManagementQuestionnairesTable extends Migration
{
    public function up()
    {
        Schema::create('contractor_questionnaires', function(Blueprint $table)
        {
            $table->bigIncrements('id');
            $table->unsignedInteger('project_id')->index('cq_project_id_idx');
            $table->unsignedInteger('company_id')->index('cq_company_id_idx');
            $table->integer('status');
            $table->dateTime('published_date')->nullable();
            $table->dateTime('unpublished_date')->nullable();
            $table->unsignedInteger('created_by');
            $table->unsignedInteger('updated_by');
            $table->timestamps();

            $table->unique(['project_id', 'company_id'], 'contractor_questionnaire_id_unique');

            $table->foreign('project_id', 'cq_project_id_fk')->references('id')->on('projects');
            $table->foreign('company_id', 'cq_company_id_fk')->references('id')->on('companies');
            $table->foreign('created_by', 'cq_created_by_fk')->references('id')->on('users');
            $table->foreign('updated_by', 'cq_updated_by_fk')->references('id')->on('users');
        });

        Schema::create('contractor_questionnaire_questions', function(Blueprint $table)
        {
            $table->bigIncrements('id');
            $table->unsignedInteger('contractor_questionnaire_id')->index('cqq_contractor_questionnaire_id');
            $table->text('question');
            $table->string('type')->default('text');
            $table->boolean('required')->default(true);
            $table->boolean('with_attachment')->default(false);
            $table->unsignedInteger('created_by');
            $table->unsignedInteger('updated_by');
            $table->timestamps();

            $table->foreign('contractor_questionnaire_id', 'cqq_contractor_questionnaire_id_fk')->references('id')->on('contractor_questionnaires');
            $table->foreign('created_by', 'cqq_created_by_fk')->references('id')->on('users');
            $table->foreign('updated_by', 'cqq_updated_by_fk')->references('id')->on('users');
        });

        Schema::create('contractor_questionnaire_options', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('contractor_questionnaire_question_id')->index('cqo_contractor_qq_id');
            $table->text('text');
            $table->string('value')->nullable();
            $table->integer('order')->nullable();
            $table->unsignedInteger('created_by');
            $table->unsignedInteger('updated_by');
            $table->timestamps();

            $table->foreign('contractor_questionnaire_question_id', 'cqo_contractor_qq_id_fk')->references('id')->on('contractor_questionnaire_questions');
            $table->foreign('created_by', 'cqo_created_by_fk')->references('id')->on('users');
            $table->foreign('updated_by', 'cqo_updated_by_fk')->references('id')->on('users');
        });

        Schema::create('contractor_questionnaire_replies', function(Blueprint $table)
        {
            $table->bigIncrements('id');
            $table->unsignedInteger('contractor_questionnaire_question_id')->index('cqr_contractor_qq_id_idx');
            $table->text('text')->nullable();
            $table->unsignedInteger('contractor_questionnaire_option_id')->nullable()->index('cqr_option_id_idx');
            $table->unsignedInteger('created_by');
            $table->unsignedInteger('updated_by');
            $table->timestamps();

            $table->foreign('contractor_questionnaire_question_id', 'cqr_contractor_qq_id_fk')->references('id')->on('contractor_questionnaire_questions');
            $table->foreign('contractor_questionnaire_option_id', 'cqr_option_id_fk')->references('id')->on('contractor_questionnaire_options');
            $table->foreign('created_by', 'cqr_created_by_fk')->references('id')->on('users');
            $table->foreign('updated_by', 'cqr_updated_by_fk')->references('id')->on('users');
        });

        Schema::create('contractor_questionnaire_reply_attachments', function(Blueprint $table)
        {
            $table->bigIncrements('id');
            $table->unsignedInteger('contractor_questionnaire_question_id')->index('cqra_contractor_qq_id_idx');
            $table->unsignedInteger('created_by');
            $table->unsignedInteger('updated_by');
            $table->timestamps();

            $table->unique(['contractor_questionnaire_question_id'], 'cqra_unique');

            $table->foreign('contractor_questionnaire_question_id', 'cqra_contractor_qq_id_fk')->references('id')->on('contractor_questionnaire_questions');
            $table->foreign('created_by', 'cqra_created_by_fk')->references('id')->on('users');
            $table->foreign('updated_by', 'cqra_updated_by_fk')->references('id')->on('users');
        });
    }

    public function down()
    {
        Schema::dropIfExists('contractor_questionnaire_reply_attachments');
        Schema::dropIfExists('contractor_questionnaire_replies');
        Schema::dropIfExists('contractor_questionnaire_options');
        Schema::dropIfExists('contractor_questionnaire_questions');
        Schema::dropIfExists('contractor_questionnaires');
    }
}
