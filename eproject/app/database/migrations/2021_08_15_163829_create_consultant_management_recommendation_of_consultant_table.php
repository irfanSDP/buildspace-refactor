<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateConsultantManagementRecommendationOfConsultantTable extends Migration
{
    public function up()
    {
        Schema::create('consultant_management_recommendation_of_consultants', function(Blueprint $table)
        {
            $table->bigIncrements('id');
            $table->unsignedInteger('vendor_category_rfp_id')->index('consultant_management_roc_vcrfp_id_idx');
            $table->decimal('proposed_fee', 19, 5)->default(0);
            $table->date('calling_rfp_proposed_date');
            $table->date('closing_rfp_proposed_date');
            $table->text('remarks')->nullable();
            $table->integer('status')->index('consultant_management_roc_status_idx');
            $table->unsignedInteger('created_by');
            $table->unsignedInteger('updated_by');
            $table->timestamps();

            $table->unique(['vendor_category_rfp_id']);

            $table->foreign('vendor_category_rfp_id', 'consultant_management_roc_vcrfp_id_fk')->references('id')->on('consultant_management_vendor_categories_rfp');
            $table->foreign('created_by', 'consultant_management_roc_created_by_fk')->references('id')->on('users');
            $table->foreign('updated_by', 'consultant_management_roc_updated_by_fk')->references('id')->on('users');
        });

        Schema::create('consultant_management_recommendation_of_consultant_verifiers', function(Blueprint $table)
        {
            $table->bigIncrements('id');
            $table->unsignedInteger('consultant_management_recommendation_of_consultant_id')->index('consultant_management_roc_verifiers_roc_id_idx');
            $table->unsignedInteger('user_id')->index('consultant_management_roc_verifiers_user_id_idx');
            $table->unsignedInteger('created_by');
            $table->unsignedInteger('updated_by');
            $table->timestamps();

            $table->unique(['consultant_management_recommendation_of_consultant_id', 'user_id'], 'consultant_management_roc_verifiers_unique');

            $table->foreign('consultant_management_recommendation_of_consultant_id', 'consultant_management_roc_verifiers_roc_id_fk')->references('id')->on('consultant_management_recommendation_of_consultants');
            $table->foreign('user_id', 'consultant_management_roc_verifiers_user_id_fk')->references('id')->on('users');
            $table->foreign('created_by', 'consultant_management_rocv_created_by_fk')->references('id')->on('users');
            $table->foreign('updated_by', 'consultant_management_rocv_updated_by_fk')->references('id')->on('users');
        });

        Schema::create('consultant_management_roc_verifier_versions', function(Blueprint $table)
        {
            $table->bigIncrements('id');
            $table->unsignedInteger('consultant_management_recommendation_of_consultant_verifier_id')->index('consultant_management_rocvv_verifier_id_idx');
            $table->unsignedInteger('user_id')->index('consultant_management_rocvv_user_id_idx');
            $table->integer('version')->index('consultant_management_rocvv_version_idx');
            $table->integer('status')->index('consultant_management_rocvv_status_idx');
            $table->text('remarks')->nullable();
            $table->unsignedInteger('created_by');
            $table->unsignedInteger('updated_by');
            $table->timestamps();

            $table->unique(['consultant_management_recommendation_of_consultant_verifier_id', 'user_id', 'version'], 'consultant_management_rocvv_unique');

            $table->foreign('consultant_management_recommendation_of_consultant_verifier_id', 'consultant_management_rocvv_verifier_id_fk')->references('id')->on('consultant_management_recommendation_of_consultant_verifiers');
            $table->foreign('user_id', 'consultant_management_rocvv_user_id_fk')->references('id')->on('users');
            $table->foreign('created_by', 'consultant_management_rocvv_created_by_fk')->references('id')->on('users');
            $table->foreign('updated_by', 'consultant_management_rocvv_updated_by_fk')->references('id')->on('users');
        });

        Schema::create('consultant_management_recommendation_of_consultant_companies', function(Blueprint $table)
        {
            $table->bigIncrements('id');
            $table->unsignedInteger('vendor_category_rfp_id')->index('consultant_management_rocc_vcrfp_id_idx');
            $table->unsignedInteger('company_id')->index('consultant_management_rocc_company_id_idx');
            $table->integer('status')->index('consultant_management_rocc_status_idx');
            $table->unsignedInteger('created_by');
            $table->unsignedInteger('updated_by');
            $table->timestamps();

            $table->unique(['vendor_category_rfp_id', 'company_id'], 'consultant_management_rocc_unique');

            $table->foreign('vendor_category_rfp_id', 'consultant_management_rocc_vcrfp_id_fk')->references('id')->on('consultant_management_vendor_categories_rfp');
            $table->foreign('company_id', 'consultant_management_rocc_company_id_fk')->references('id')->on('companies');
            $table->foreign('created_by', 'consultant_management_rocc_created_by_fk')->references('id')->on('users');
            $table->foreign('updated_by', 'consultant_management_rocc_updated_by_fk')->references('id')->on('users');
        });
    }

    public function down()
    {
        Schema::dropIfExists('consultant_management_recommendation_of_consultant_companies');
        Schema::dropIfExists('consultant_management_recommendation_of_consultant_verifier_versions');
        Schema::dropIfExists('consultant_management_recommendation_of_consultant_verifiers');
        Schema::dropIfExists('consultant_management_recommendation_of_consultants');
    }
}
