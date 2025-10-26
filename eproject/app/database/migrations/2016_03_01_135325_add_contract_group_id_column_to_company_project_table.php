<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddContractGroupIdColumnToCompanyProjectTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('company_project', function(Blueprint $table)
		{
            $table->unsignedInteger('contract_group_id')->nullable();
            $table->foreign('contract_group_id')->references('id')->on('contract_groups');
		});

        // Fill columns to preserve project roles.
        foreach(\PCK\CompanyProject\CompanyProject::all() as $companyProject)
        {
            $companyProject->contract_group_id = $companyProject->company->pam_2006_contract_group_id;
            $companyProject->save();
        }

        DB::statement('ALTER TABLE company_project ALTER COLUMN contract_group_id SET NOT NULL');
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('company_project', function(Blueprint $table)
		{
            $table->dropColumn('contract_group_id');
		});
	}

}
