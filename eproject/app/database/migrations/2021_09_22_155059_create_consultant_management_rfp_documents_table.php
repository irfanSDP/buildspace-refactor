<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateConsultantManagementRfpDocumentsTable extends Migration
{
    public function up()
    {
        Schema::create('consultant_management_rfp_documents', function(Blueprint $table)
        {
            $table->bigIncrements('id');
            $table->unsignedInteger('vendor_category_rfp_id')->index('cm_rfp_docs_vcrfp_id_idx');
            $table->text('remarks')->nullable();
            $table->unsignedInteger('created_by');
            $table->unsignedInteger('updated_by');
            $table->timestamps();

            $table->foreign('vendor_category_rfp_id', 'cm_rfp_docs_vcrfp_id_fk')->references('id')->on('consultant_management_vendor_categories_rfp');
            $table->foreign('created_by', 'cm_rfp_docs_created_by_fk')->references('id')->on('users');
            $table->foreign('updated_by', 'cm_rfp_docs_updated_by_fk')->references('id')->on('users');
        });
    }

    public function down()
    {
        Schema::dropIfExists('consultant_management_rfp_documents');
    }
}
