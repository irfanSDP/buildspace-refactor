<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddContractGroupIdUniqueConstraintToCompanyProjectTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        foreach(\PCK\Projects\Project::whereNotNull('parent_project_id')->get() as $subProject)
        {
            if( $subProject->selectedCompanies()->wherePivot('contract_group_id', '=', 2)->get()->count() <= 1 ) continue;

            if( ! $parentProjectSelectedContractor = $subProject->parentProject->getSelectedContractor() ) continue;

            $subProject->selectedCompanies()->detach($parentProjectSelectedContractor->id);
        }

        Schema::table('company_project', function(Blueprint $table)
        {
            $table->unique(array( 'project_id', 'contract_group_id' ));
        });
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
            $table->dropUnique('company_project_project_id_contract_group_id_unique');
        });
    }

}
