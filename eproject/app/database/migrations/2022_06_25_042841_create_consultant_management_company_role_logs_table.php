<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

use PCK\ConsultantManagement\ConsultantManagementCompanyRole;
use PCK\ConsultantManagement\ConsultantManagementCompanyRoleLog;

class CreateConsultantManagementCompanyRoleLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('consultant_management_company_role_logs', function(Blueprint $table)
        {
            $table->bigIncrements('id');
            $table->unsignedInteger('role');
            $table->unsignedInteger('consultant_management_contract_id')->index('cm_company_role_logs_contract_id_idx');
            $table->unsignedInteger('company_id');
            $table->boolean('calling_rfp')->default(false);
            $table->unsignedInteger('created_by')->index();
            $table->unsignedInteger('updated_by')->index();
            $table->timestamps();

            $table->foreign('consultant_management_contract_id', 'cm_company_role_logs_contract_id_fk')->references('id')->on('consultant_management_contracts');
            $table->foreign('company_id')->references('id')->on('companies');
            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('updated_by')->references('id')->on('users');
        });

        $companyRoles = ConsultantManagementCompanyRole::orderBy('consultant_management_company_roles.consultant_management_contract_id', 'asc')
        ->get()
        ->toArray();

        $logs = [];

        foreach($companyRoles as $companyRole)
        {
            $logs[] = [
                'role' => $companyRole['role'],
                'consultant_management_contract_id' => $companyRole['consultant_management_contract_id'],
                'company_id'  => $companyRole['company_id'],
                'calling_rfp' => ($companyRole['calling_rfp']),
                'created_by'  => $companyRole['updated_by'],
                'updated_by'  => $companyRole['updated_by'],
                'created_at'  => $companyRole['updated_at'],
                'updated_at'  => $companyRole['updated_at']
            ];
        }

        if(!empty($logs))
        {
            ConsultantManagementCompanyRoleLog::insert($logs);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('consultant_management_company_role_logs');
    }

}
