<?php

use PCK\Companies\Company;
use PCK\ContractGroups\Types\Role;
use PCK\Contractors\ContractorDetails\JobLimitSymbol;
use PCK\Contractors\ContractorDetails\RegistrationStatus;
use PCK\CPEGrades\CurrentCPEGrade;
use PCK\CPEGrades\PreviousCPEGrade;
use PCK\WorkCategories\WorkCategory;
use PCK\WorkCategories\WorkSubcategory;

class AddDefaultContractorToContractorCompaniesTableSeeder extends Seeder {

    public function run()
    {
        $contractorCompanies = Company::where('pam_2006_contract_group_id', '=', Role::CONTRACTOR)->get();

        $contractorCompaniesCount = 0;
        $contractorsCount = 0;
        foreach($contractorCompanies as $company)
        {
            $contractorCompaniesCount++;
            if( is_null($company->contractor) )
            {
                $contractorsCount++;
                $inputs = array();
                $inputs['work_category'] = array(
                    WorkCategory::where('name', '=', WorkCategory::UNSPECIFIED_RECORD_NAME)->first()->id
                );
                $inputs['work_subcategory'] = array(
                    WorkSubcategory::where('name', '=', WorkSubcategory::UNSPECIFIED_RECORD_NAME)->first()->id
                );
                $inputs['previous_cpe_grade_id'] = PreviousCPEGrade::where('grade', '=', PreviousCPEGrade::UNSPECIFIED_RECORD_GRADE)->first()->id;
                $inputs['current_cpe_grade_id'] = CurrentCPEGrade::where('grade', '=', CurrentCPEGrade::UNSPECIFIED_RECORD_GRADE)->first()->id;
                $inputs['registration_status_id'] = RegistrationStatus::where('name', '=', RegistrationStatus::UNSPECIFIED_RECORD_NAME)->first()->id;
                $inputs['job_limit_sign'] = JobLimitSymbol::JOB_LIMIT_SYMBOL_GREATER_THAN;
                $inputs['job_limit_number'] = 0;
                $inputs['cidb_category'] = null;
                $inputs['registered_date'] = null;
                $inputs['remarks'] = null;

                $contractorRepo = App::make('\PCK\Contractors\ContractorRepository');
                $contractorRepo->add($inputs, Company::find($company->id));
            }
        }
    }
}