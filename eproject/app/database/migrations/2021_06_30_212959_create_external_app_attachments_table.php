<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateExternalAppAttachmentsTable extends Migration {

    public function up()
    {
        Schema::create('external_app_attachments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('reference_id');
            $table->text('remarks')->nullable();
            $table->text('filename');
            $table->text('file_path');
            $table->timestamp('created_at');
            $table->timestamp('updated_at');
        });

        Schema::create('external_app_company_attachments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('reference_id');
            $table->text('document_type');
            $table->text('filename');
            $table->text('file_path');
            $table->timestamp('created_at');
            $table->timestamp('updated_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('external_app_attachments');
        Schema::dropIfExists('external_app_company_attachments');
    }
}
