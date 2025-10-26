<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterPam2006ProjectDetailsTable extends Migration
{
    public function up()
    {
        \DB::statement('ALTER TABLE pam_2006_project_details ALTER COLUMN percentage_of_certified_value_retained TYPE DECIMAL(19,2)');
        \DB::statement('ALTER TABLE pam_2006_project_details ALTER COLUMN limit_retention_fund TYPE DECIMAL(19,2)');
        \DB::statement('ALTER TABLE pam_2006_project_details ALTER COLUMN percentage_value_of_materials_and_goods_included_in_certificate TYPE DECIMAL(19,2)');

        Schema::table('pam_2006_project_details', function ($table) {

            if (!Schema::hasColumn('pam_2006_project_details', 'cpc_date'))
            {
                $table->date('cpc_date')->nullable();
            }

            if (!Schema::hasColumn('pam_2006_project_details', 'extension_of_time_date'))
            {
                $table->date('extension_of_time_date')->nullable();
            }

            if (!Schema::hasColumn('pam_2006_project_details', 'defect_liability_period'))
            {
                $table->unsignedInteger('defect_liability_period')->default(0);
            }

            if (!Schema::hasColumn('pam_2006_project_details', 'defect_liability_period_unit'))
            {
                $table->unsignedInteger('defect_liability_period_unit')->default(1);
            }

            if (!Schema::hasColumn('pam_2006_project_details', 'certificate_of_making_good_defect_date'))
            {
                $table->date('certificate_of_making_good_defect_date')->nullable();
            }

            if (!Schema::hasColumn('pam_2006_project_details', 'cnc_date'))
            {
                $table->date('cnc_date')->nullable();
            }

            if (!Schema::hasColumn('pam_2006_project_details', 'performance_bond_validity_date'))
            {
                $table->date('performance_bond_validity_date')->nullable();
            }

            if (!Schema::hasColumn('pam_2006_project_details', 'insurance_policy_coverage_date'))
            {
                $table->date('insurance_policy_coverage_date')->nullable();
            }
        });
    }

    public function down()
    {
        Schema::table('pam_2006_project_details', function ($table) {

            if (Schema::hasColumn('pam_2006_project_details', 'cpc_date'))
            {
                $table->dropColumn('cpc_date');
            }

            if (Schema::hasColumn('pam_2006_project_details', 'extension_of_time_date'))
            {
                $table->dropColumn('extension_of_time_date');
            }

            if (Schema::hasColumn('pam_2006_project_details', 'defect_liability_period'))
            {
                $table->dropColumn('defect_liability_period');
            }

            if (Schema::hasColumn('pam_2006_project_details', 'defect_liability_period_unit'))
            {
                $table->dropColumn('defect_liability_period_unit');
            }

            if (Schema::hasColumn('pam_2006_project_details', 'certificate_of_making_good_defect_date'))
            {
                $table->dropColumn('certificate_of_making_good_defect_date');
            }

            if (Schema::hasColumn('pam_2006_project_details', 'cnc_date'))
            {
                $table->dropColumn('cnc_date');
            }

            if (Schema::hasColumn('pam_2006_project_details', 'performance_bond_validity_date'))
            {
                $table->dropColumn('performance_bond_validity_date');
            }

            if (Schema::hasColumn('pam_2006_project_details', 'insurance_policy_coverage_date'))
            {
                $table->dropColumn('insurance_policy_coverage_date');
            }
        });
    }
}
