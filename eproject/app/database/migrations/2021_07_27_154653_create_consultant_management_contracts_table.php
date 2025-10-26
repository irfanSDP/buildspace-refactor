<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateConsultantManagementContractsTable extends Migration
{
    public function up()
    {
        Schema::create('consultant_management_contracts', function(Blueprint $table)
        {
                $table->bigIncrements('id');
                $table->unsignedInteger('subsidiary_id')->index('consultant_management_contracts_subsidiary_id_idx');
                $table->string('reference_no', 80)->index('consultant_management_contracts_reference_no_idx');
                $table->text('title');
                $table->text('description')->nullable();
                $table->text('address');
                $table->unsignedInteger('country_id')->index('consultant_management_contracts_contract_id_idx');
                $table->unsignedInteger('state_id')->index('consultant_management_contracts_state_id_idx');
                $table->string('modified_currency_code', 3)->nullable();
                $table->text('modified_currency_name')->nullable();
                $table->unsignedInteger('created_by')->index();
                $table->unsignedInteger('updated_by')->index();
                $table->timestamps();

                $table->unique(['reference_no']);

                $table->foreign('subsidiary_id', 'consultant_management_contracts_subsidiary_id_fk')->references('id')->on('subsidiaries');
                $table->foreign('created_by', 'consultant_management_contracts_created_by_fk')->references('id')->on('users');
                $table->foreign('updated_by', 'consultant_management_contracts_updated_by_fk')->references('id')->on('users');
        });

        Schema::create('consultant_management_subsidiaries', function(Blueprint $table)
        {
                $table->bigIncrements('id');
                $table->unsignedInteger('consultant_management_contract_id')->index('consultant_management_subsidiaries_contract_id_idx');
                $table->unsignedInteger('subsidiary_id')->index('consultant_management_subsidiaries_subsidiary_id_idx');
                $table->unsignedInteger('development_type_id')->index('consultant_management_subsidiaries_development_type_id_idx');
                $table->text('business_case');
                $table->decimal('gross_acreage', 19, 5)->default(0);
                $table->decimal('project_budget', 19, 5)->default(0);
                $table->decimal('total_construction_cost', 19, 5)->default(0);
                $table->decimal('total_landscape_cost', 19, 5)->default(0);
                $table->decimal('cost_per_square_feet', 19, 5)->default(0);
                $table->date('planning_permission_date');
                $table->date('building_plan_date');
                $table->date('launch_date');
                $table->integer('position')->default(0)->index('consultant_management_subsidiaries_position_idx');
                $table->unsignedInteger('created_by')->index();
                $table->unsignedInteger('updated_by')->index();
                $table->timestamps();

                $table->unique(['consultant_management_contract_id', 'subsidiary_id']);
                $table->unique(['consultant_management_contract_id', 'position'], 'consultant_management_subsidiaries_position_unique');

                $table->foreign('consultant_management_contract_id', 'consultant_management_subsidiaries_contract_id_fk')->references('id')->on('consultant_management_contracts');
                $table->foreign('subsidiary_id', 'consultant_management_subsidiaries_subsidiary_id_fk')->references('id')->on('subsidiaries');
                $table->foreign('development_type_id', 'consultant_management_subsidiaries_development_type_id_fk')->references('id')->on('development_types');
                $table->foreign('created_by', 'consultant_management_subsidiaries_created_by_fk')->references('id')->on('users');
                $table->foreign('updated_by', 'consultant_management_subsidiaries_updated_by_fk')->references('id')->on('users');
        });

        Schema::create('consultant_management_product_types', function(Blueprint $table)
        {
                $table->bigIncrements('id');
                $table->unsignedInteger('consultant_management_subsidiary_id')->index('consultant_management_product_types_subsidiary_id_idx');
                $table->unsignedInteger('product_type_id')->index('consultant_management_product_types_prod_type_id_idx');
                $table->integer('number_of_unit')->default(0);
                $table->decimal('lot_dimension_length', 19, 5)->default(0);
                $table->decimal('lot_dimension_width', 19, 5)->default(0);
                $table->decimal('proposed_built_up_area', 19, 5)->default(0);
                $table->decimal('proposed_average_selling_price', 19, 5)->default(0);
                $table->unsignedInteger('created_by')->index();
                $table->unsignedInteger('updated_by')->index();
                $table->timestamps();

                $table->foreign('consultant_management_subsidiary_id', 'consultant_management_product_types_subsidiary_fk')->references('id')->on('consultant_management_subsidiaries');
                $table->foreign('product_type_id', 'consultant_management_product_types_product_type_fk')->references('id')->on('product_types');
                $table->foreign('created_by', 'consultant_management_product_types_created_by_fk')->references('id')->on('users');
                $table->foreign('updated_by', 'consultant_management_product_types_updated_by_fk')->references('id')->on('users');
        });

        Schema::create('consultant_management_user_roles', function(Blueprint $table)
        {
            $table->unsignedInteger('role');
            $table->unsignedInteger('consultant_management_contract_id')->index('consultant_management_user_roles_contract_id_idx');
            $table->unsignedInteger('user_id');
            $table->boolean('editor')->default(false);
            $table->unsignedInteger('created_by')->index();
            $table->unsignedInteger('updated_by')->index();
            $table->timestamps();

            $table->unique(['role', 'consultant_management_contract_id', 'user_id'], 'consultant_management_user_roles_unique');

            $table->foreign('consultant_management_contract_id', 'consultant_management_user_roles_contract_id_fk')->references('id')->on('consultant_management_contracts');
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('updated_by')->references('id')->on('users');

            $table->primary(['role', 'consultant_management_contract_id', 'user_id']);
        });

        Schema::create('consultant_management_company_roles', function(Blueprint $table)
        {
            $table->unsignedInteger('role');
            $table->unsignedInteger('consultant_management_contract_id')->index('consultant_management_company_roles_contract_id_idx');
            $table->unsignedInteger('company_id');
            $table->boolean('calling_rfp')->default(false);
            $table->unsignedInteger('created_by')->index();
            $table->unsignedInteger('updated_by')->index();
            $table->timestamps();

            $table->unique(['role', 'consultant_management_contract_id', 'company_id'], 'consultant_management_company_roles_unique');

            $table->foreign('consultant_management_contract_id', 'consultant_management_company_roles_contract_id_fk')->references('id')->on('consultant_management_contracts');
            $table->foreign('company_id')->references('id')->on('companies');
            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('updated_by')->references('id')->on('users');

            $table->primary(['role', 'consultant_management_contract_id', 'company_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('consultant_management_company_roles');
        Schema::dropIfExists('consultant_management_user_roles');
        Schema::dropIfExists('consultant_management_product_types');
        Schema::dropIfExists('consultant_management_subsidiaries');
        Schema::dropIfExists('consultant_management_contracts');
    }
}
