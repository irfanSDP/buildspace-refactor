<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterUniqueConstraintsToRunningNumberAndSubsidiaryIdInProjectsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('projects', function(Blueprint $table)
		{
            // Check if constraint exists before dropping unique constraint.
            // Unique constraint is not set on rollback because the data may already have non-unique values (for business_unit_id + running_number).
            $result = DB::select("
                select constraint_name
                from information_schema.constraint_column_usage
                where constraint_name = 'projects_business_unit_id_running_number_unique'
                ");

            if( ! empty( $result ) )
            {
                $table->dropUnique('projects_business_unit_id_running_number_unique');
            }

            $table->unique(array('running_number', 'subsidiary_id'));

            $table->unique('reference');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('projects', function(Blueprint $table)
		{
            $table->dropUnique('projects_reference_unique');

            $table->dropUnique('projects_running_number_subsidiary_id_unique');
		});
	}

}
