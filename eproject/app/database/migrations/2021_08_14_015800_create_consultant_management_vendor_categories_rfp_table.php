<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateConsultantManagementVendorCategoriesRfpTable extends Migration
{
    public function up()
    {
        Schema::create('consultant_management_vendor_categories_rfp', function(Blueprint $table)
        {
            $table->bigIncrements('id');
            $table->unsignedInteger('vendor_category_id')->index('consultant_management_vcrfp_vendor_category_id_idx');
            $table->unsignedInteger('consultant_management_contract_id')->index('consultant_management_vcrfp_contract_id_idx');
            $table->unsignedInteger('cost_type');
            $table->unsignedInteger('created_by')->index();
            $table->unsignedInteger('updated_by')->index();
            $table->timestamps();

            $table->unique(['vendor_category_id', 'consultant_management_contract_id'], 'consultant_management_vcrfp_unique');

            $table->foreign('vendor_category_id', 'consultant_management_vcrfp_vendor_category_id_fk')->references('id')->on('vendor_categories');
            $table->foreign('consultant_management_contract_id', 'consultant_management_vcrfp_contract_id_fk')->references('id')->on('consultant_management_contracts');
            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('updated_by')->references('id')->on('users');
        });
    }

    public function down()
    {
        Schema::dropIfExists('consultant_management_vendor_categories_rfp');
    }
}
