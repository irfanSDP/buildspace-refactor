<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

use PCK\Users\User;
use PCK\ConsultantManagement\ConsultantManagementListOfConsultantCompany;
use PCK\ConsultantManagement\ConsultantUser;

class CreateConsultantManagementCompanyVendorUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('consultant_management_consultant_users', function(Blueprint $table)
        {
            $table->bigIncrements('id');
            $table->unsignedInteger('user_id')->index('cm_consultant_users_uid_idx');
            $table->boolean('is_admin')->default(false);
            $table->unsignedInteger('created_by')->index();
            $table->unsignedInteger('updated_by')->index();
            $table->timestamps();

            $table->unique(['user_id'], 'cm_consultant_users_uid_idx_unique');

            $table->foreign('user_id', 'cm_consultant_users_uid_fk')->references('id')->on('users');
            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('updated_by')->references('id')->on('users');
        });

        $adminUsers = User::selectRaw('DISTINCT users.id AS id')
        ->join('companies', 'users.company_id', '=', 'companies.id')
        ->join('consultant_management_recommendation_of_consultant_companies', 'companies.id', '=', 'consultant_management_recommendation_of_consultant_companies.company_id')
        ->whereRaw('users.is_admin IS TRUE')
        ->get()
        ->toArray();

        $users = [];

        foreach($adminUsers as $adminUser)
        {
            $users[] = [
                'user_id'     => $adminUser['id'],
                'is_admin'    => true,
                'created_by'  => $adminUser['id'],
                'updated_by'  => $adminUser['id'],
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s')
            ];
        }

        if(!empty($users))
        {
            ConsultantUser::insert($users);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('consultant_management_consultant_users');
    }
}
