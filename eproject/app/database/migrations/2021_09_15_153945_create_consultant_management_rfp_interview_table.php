<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateConsultantManagementRfpInterviewTable extends Migration
{
    public function up()
    {
        Schema::create('consultant_management_rfp_interviews', function(Blueprint $table)
        {
            $table->bigIncrements('id');
            $table->unsignedInteger('vendor_category_rfp_id')->index('cmri_vcrfp_id_idx');
            $table->string('title');
            $table->text('details')->nullable();
            $table->date('interview_date');
            $table->integer('status')->index();
            $table->unsignedInteger('created_by');
            $table->unsignedInteger('updated_by');
            $table->timestamps();

            $table->foreign('vendor_category_rfp_id', 'cmri_vcrfp_id_fk')->references('id')->on('consultant_management_vendor_categories_rfp');
            $table->foreign('created_by', 'cmri_created_by_fk')->references('id')->on('users');
            $table->foreign('updated_by', 'cmri_updated_by_fk')->references('id')->on('users');
        });

        Schema::create('consultant_management_rfp_interview_consultants', function(Blueprint $table)
        {
            $table->bigIncrements('id');
            $table->unsignedInteger('consultant_management_rfp_interview_id')->index('cmric_vcrfp_id_idx');
            $table->unsignedInteger('company_id')->index('cmric_company_id_idx');
            $table->integer('status')->index();
            $table->text('remarks')->nullable();
            $table->dateTime('interview_timestamp');
            $table->unsignedInteger('created_by');
            $table->unsignedInteger('updated_by');
            $table->timestamps();

            $table->unique(['consultant_management_rfp_interview_id', 'company_id'], 'cmric_unique');

            $table->foreign('consultant_management_rfp_interview_id', 'cmric_vcrfp_id_fk')->references('id')->on('consultant_management_rfp_interviews');
            $table->foreign('company_id', 'cmric_company_id_fk')->references('id')->on('companies');
            $table->foreign('created_by', 'cmric_created_by_fk')->references('id')->on('users');
            $table->foreign('updated_by', 'cmric_updated_by_fk')->references('id')->on('users');
        });

        Schema::create('consultant_management_rfp_interview_tokens', function(Blueprint $table)
        {
            $table->bigIncrements('id');
            $table->unsignedInteger('consultant_management_rfp_interview_consultant_id')->index('cmrit_vcrfp_id_idx');
            $table->string('token', 16)->unique();
            $table->timestamps();

            $table->foreign('consultant_management_rfp_interview_consultant_id', 'cmrit_vcrfp_id_fk')->references('id')->on('consultant_management_rfp_interview_consultants');
        });
    }

    public function down()
    {
        Schema::dropIfExists('consultant_management_rfp_interview_tokens');
        Schema::dropIfExists('consultant_management_rfp_interview_consultants');
        Schema::dropIfExists('consultant_management_rfp_interviews');
    }
}
