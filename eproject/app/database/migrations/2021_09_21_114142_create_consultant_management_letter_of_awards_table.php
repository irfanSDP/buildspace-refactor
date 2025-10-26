<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateConsultantManagementLetterOfAwardsTable extends Migration
{
    public function up()
    {
        Schema::create('consultant_management_letter_of_award_templates', function(Blueprint $table)
        {
            $table->bigIncrements('id');
            $table->string('title', 120)->unique();
            $table->text('content')->nullable();
            $table->unsignedInteger('created_by');
            $table->unsignedInteger('updated_by');
            $table->timestamps();

            $table->foreign('created_by', 'cmloat_created_by_fk')->references('id')->on('users');
            $table->foreign('updated_by', 'cmloat_updated_by_fk')->references('id')->on('users');
        });

        Schema::create('consultant_management_letter_of_awards', function(Blueprint $table)
        {
            $table->bigIncrements('id');
            $table->unsignedInteger('vendor_category_rfp_id')->unique()->index('cmloa_vcrfp_id_idx');
            $table->text('content')->nullable();
            $table->integer('status')->index();
            $table->unsignedInteger('created_by');
            $table->unsignedInteger('updated_by');
            $table->timestamps();

            $table->foreign('vendor_category_rfp_id', 'cmloa_vcrfp_id_fk')->references('id')->on('consultant_management_vendor_categories_rfp');
            $table->foreign('created_by', 'cmloa_created_by_fk')->references('id')->on('users');
            $table->foreign('updated_by', 'cmloa_updated_by_fk')->references('id')->on('users');
        });

        Schema::create('consultant_management_letter_of_award_verifiers', function(Blueprint $table)
        {
            $table->bigIncrements('id');
            $table->unsignedInteger('consultant_management_letter_of_award_id')->index('cmloav_loa_id_idx');
            $table->unsignedInteger('user_id')->index('cmloav_user_id_idx');
            $table->unsignedInteger('created_by');
            $table->unsignedInteger('updated_by');
            $table->timestamps();

            $table->softDeletes();

            $table->unique(['consultant_management_letter_of_award_id', 'user_id', 'deleted_at'], 'cmloa_verifiers_unique');

            $table->foreign('consultant_management_letter_of_award_id', 'cmloav_loa_id_fk')->references('id')->on('consultant_management_letter_of_awards');
            $table->foreign('user_id', 'cmloav_user_id_fk')->references('id')->on('users');
            $table->foreign('created_by', 'cmloav_created_by_fk')->references('id')->on('users');
            $table->foreign('updated_by', 'cmloav_updated_by_fk')->references('id')->on('users');
        });

        Schema::create('consultant_management_letter_of_award_verifier_versions', function(Blueprint $table)
        {
            $table->bigIncrements('id');
            $table->unsignedInteger('consultant_management_letter_of_award_verifier_id')->index('cmloavv_verifier_id_idx');
            $table->unsignedInteger('user_id')->index('cmloavv_user_id_idx');
            $table->integer('version')->index('cmloavv_version_idx');
            $table->integer('status')->index('cmloavv_status_idx');
            $table->text('remarks')->nullable();
            $table->unsignedInteger('created_by');
            $table->unsignedInteger('updated_by');
            $table->timestamps();

            $table->unique(['consultant_management_letter_of_award_verifier_id', 'user_id', 'version'], 'cmloav_version_unique');

            $table->foreign('consultant_management_letter_of_award_verifier_id', 'cmloavv_verifier_id_fk')->references('id')->on('consultant_management_letter_of_award_verifiers');
            $table->foreign('user_id', 'cmloavv_user_id_fk')->references('id')->on('users');
            $table->foreign('created_by', 'cmloavv_created_by_fk')->references('id')->on('users');
            $table->foreign('updated_by', 'cmloavv_updated_by_fk')->references('id')->on('users');
        });
    }

    public function down()
    {
        Schema::dropIfExists('consultant_management_letter_of_award_verifier_versions');
        Schema::dropIfExists('consultant_management_letter_of_award_verifiers');
        Schema::dropIfExists('consultant_management_letter_of_awards');
        Schema::dropIfExists('consultant_management_letter_of_award_templates');
    }
}
