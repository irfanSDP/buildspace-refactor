<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateConsultantManagementRolesContractGroupCategoriesTable extends Migration
{
    public function up()
    {
        Schema::create('consultant_management_roles_contract_group_categories', function(Blueprint $table)
        {
            $table->unsignedInteger('role');
            $table->unsignedInteger('contract_group_category_id');
            $table->unsignedInteger('created_by')->index();
            $table->unsignedInteger('updated_by')->index();
            $table->timestamps();

            $table->index('contract_group_category_id');

            $table->foreign('contract_group_category_id')->references('id')->on('contract_group_categories')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('updated_by')->references('id')->on('users');

            $table->primary(['role', 'contract_group_category_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('consultant_management_roles_contract_group_categories');
    }
}
