<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyProjectReportsTableAddProjectReportTypeMappingIdColumn extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        Schema::table('project_reports', function(Blueprint $table)
        {
            $table->unsignedInteger('project_report_type_mapping_id')->nullable();
    
            $table->index('project_report_type_mapping_id');
    
            $table->foreign('project_report_type_mapping_id')->references('id')->on('project_report_type_mappings')->onDelete('cascade');
        });
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
        Schema::table('project_reports', function(Blueprint $table)
        {
		    $table->dropColumn('project_report_type_mapping_id');
        });
	}

}
