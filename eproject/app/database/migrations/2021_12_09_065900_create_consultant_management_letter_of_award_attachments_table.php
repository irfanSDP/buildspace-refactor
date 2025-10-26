<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateConsultantManagementLetterOfAwardAttachmentsTable extends Migration
{
    public function up()
    {
        Schema::create('consultant_management_letter_of_award_attachments', function(Blueprint $table)
        {
            $table->bigIncrements('id');
            $table->unsignedInteger('consultant_management_letter_of_award_id')->index('cmloa_attachments_loa_id_idx');
            $table->string('title');
            $table->string('attachment_filename')->nullable();
            $table->unsignedInteger('created_by');
            $table->unsignedInteger('updated_by');
            $table->timestamps();

            $table->foreign('consultant_management_letter_of_award_id', 'cmloa_attachments_loa_id_fk')->references('id')->on('consultant_management_letter_of_awards');
            $table->foreign('created_by', 'cmloa_attachments_created_by_fk')->references('id')->on('users');
            $table->foreign('updated_by', 'cmloa_attachments_updated_by_fk')->references('id')->on('users');
        });
    }

    public function down()
    {
        Schema::dropIfExists('consultant_management_letter_of_award_attachments');
    }
}
