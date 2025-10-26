<?php namespace PCK\Contractors;

use PCK\Base\BaseModuleRepository;
use PCK\Companies\Company;
use PCK\ContractGroups\Types\Role;
use PCK\Contractors\ContractorDetails\JobLimitSymbol;
use PCK\Contractors\ContractorDetails\RegistrationStatus;
use PCK\CPEGrades\CurrentCPEGrade;
use PCK\CPEGrades\PreviousCPEGrade;
use PCK\Helpers\DataTables;
use PCK\WorkCategories\WorkCategory;
use PCK\WorkCategories\WorkSubcategory;

class ContractorRepository extends BaseModuleRepository {

    private $contractor;

    public function __construct(
        Contractor $contractor
    )
    {
        $this->contractor = $contractor;
    }

    /**
     * Get all contractors together with associated objects
     *
     * @return mixed
     */
    public function all()
    {
        return Contractor::with(
            'company.country',
            'company.state',
            'workCategories',
            'workSubcategories',
            'registrationStatus',
            'previousCPEGrade',
            'currentCPEGrade')
            ->orderBy('id', 'DESC')
            ->get();
    }

    /**
     * Returns the dataTable response for contractors.
     *
     * @param array $inputs
     *
     * @return array
     */
    public function dataTableResponse(array $inputs)
    {
        $companyColumns            = array(
            'name'             => 1,
            'address'          => 2,
            'main_contact'     => 3,
            'email'            => 4,
            'telephone_number' => 5,
            'fax_number'       => 6
        );
        $countryColumns            = array( 'country' => 7 );
        $stateColumns              = array( 'name' => 8 );
        $workCategoryColumns       = array( 'name' => 9 );
        $workSubCategoryColumns    = array( 'name' => 10 );
        $registrationStatusColumns = array( 'name' => 11 );
        $previousCPEGradeColumns   = array( 'grade' => 12 );
        $currentCPEGradeColumns    = array( 'grade' => 13 );
        $contractorColumns         = array(
            'job_limit_number' => 14,
            'cidb_category'    => 15,
            'remarks'          => 16,
            'registered_date'  => 17
        );
        $allColumns                = array(
            'companies'                        => $companyColumns,
            'countries'                        => $countryColumns,
            'states'                           => $stateColumns,
            'work_categories'                  => $workCategoryColumns,
            'work_subcategories'               => $workSubCategoryColumns,
            'previous_cpe_grades'              => $previousCPEGradeColumns,
            'current_cpe_grades'               => $currentCPEGradeColumns,
            'contractor_registration_statuses' => $registrationStatusColumns,
            'contractors'                      => $contractorColumns
        );

        $idColumn      = 'companies.id';
        $selectColumns = array( $idColumn );

        $query = \DB::table("companies as companies");

        $datatable = new DataTables($query, $inputs, $allColumns, $idColumn, $selectColumns);

        $datatable->properties->query->leftJoin('contractors as contractors', 'contractors.company_id', '=', 'companies.id')
            ->join('contract_group_categories as contract_group_categories', 'contract_group_categories.id', '=', 'companies.contract_group_category_id')
            ->join('contract_group_contract_group_category as contract_group_contract_group_category', 'contract_group_contract_group_category.contract_group_category_id', '=', 'contract_group_categories.id')
            ->join('contract_groups as contract_groups', 'contract_groups.id', '=', 'contract_group_contract_group_category.contract_group_id')
            ->leftJoin('countries as countries', 'companies.country_id', '=', 'countries.id')
            ->leftJoin('states as states', 'companies.state_id', '=', 'states.id')
            ->leftJoin('previous_cpe_grades as previous_cpe_grades', 'contractors.previous_cpe_grade_id', '=', 'previous_cpe_grades.id')
            ->leftJoin('current_cpe_grades as current_cpe_grades', 'contractors.current_cpe_grade_id', '=', 'current_cpe_grades.id')
            ->leftJoin('contractor_registration_statuses as contractor_registration_statuses', 'contractors.registration_status_id', '=', 'contractor_registration_statuses.id')
            ->leftJoin('contractor_work_category as contractor_work_category', 'contractors.id', '=', 'contractor_work_category.contractor_id')
            ->leftJoin('work_categories as work_categories', 'work_categories.id', '=', 'contractor_work_category.work_category_id')
            ->leftJoin('contractor_work_subcategory as contractor_work_subcategory', 'contractors.id', '=', 'contractor_work_subcategory.contractor_id')
            ->leftJoin('work_subcategories as work_subcategories', 'work_subcategories.id', '=', 'contractor_work_subcategory.work_subcategory_id');

        $datatable->properties->query->where('contract_groups.group', '=', Role::CONTRACTOR);

        $datatable->addAllStatements();

        $results = $datatable->getResults();

        $contractorsArray = array();

        foreach($results as $arrayIndex => $arrayItem)
        {
            $indexNo = ( $arrayIndex + 1 ) + ( $datatable->properties->pagingOffset );

            $record = Company::find($arrayItem->id);

            if( $contractorDetails = $record->contractor )
            {
                $workCategories = array();
                foreach($contractorDetails->workCategories as $index => $workCategory)
                {
                    array_push($workCategories, ( $workCategory->name ));
                }

                $workSubcategories = array();
                foreach($contractorDetails->workSubcategories as $index => $workSubcategory)
                {
                    array_push($workSubcategories, ( $workSubcategory->name ));
                }
            }

            $contractorsArray[] = array(
                'indexNo'                => $indexNo,
                'name'                   => $record->name,
                'address'                => $record->address,
                'mainContact'            => $record->main_contact,
                'email'                  => $record->email,
                'telephoneNo'            => $record->telephone_number,
                'faxNo'                  => $record->fax_number,
                'country'                => $record->country->country,
                'state'                  => $record->state->name,
                'workCategories'         => isset( $workCategories ) ? $workCategories : array(),
                'workSubcategories'      => isset( $workSubcategories ) ? $workSubcategories : array(),
                'registrationStatus'     => $contractorDetails ? $contractorDetails->registrationStatus->name : null,
                'previousCPE'            => $contractorDetails ? $contractorDetails->previousCPEGrade->grade : null,
                'currentCPE'             => $contractorDetails ? $contractorDetails->currentCPEGrade->grade : null,
                'jobLimit'               => $contractorDetails ? $contractorDetails->job_limit_number : null,
                'cidbCategory'           => $contractorDetails ? $contractorDetails->cidb_category : null,
                'remarks'                => $contractorDetails ? $contractorDetails->remarks : null,
                'registeredDate'         => $contractorDetails ? $contractorDetails->registered_date : null,
                'route:contractors.show' => route('contractors.show', array( $record->id )),
                'jobLimitSymbol'         => $contractorDetails ? $contractorDetails->job_limit_sign : null,
            );
        }

        return $datatable->dataTableResponse($contractorsArray);
    }

    public function find($id)
    {
        return $this->contractor->find($id);
    }

    /**
     * Adds a Contractor record to storage
     *
     * @param         $input
     * @param Company $company
     *
     * @return bool
     */
    public function add($input, Company $company)
    {
        $this->contractor = $this->insertData($this->contractor, $input);

        $this->contractor->company()->associate($company);

        if( $save = $this->contractor->save() )
        {
            $this->contractor->workCategories()->attach($input['work_category']);
            $this->contractor->workSubcategories()->attach($input['work_subcategory']);
        }

        return $save;
    }

    /**
     * Returns default data for a Contractor record.
     *
     * @return array
     */
    public function getDefaultContractorInput()
    {
        $input                           = array();
        $input['work_category']          = array(
            WorkCategory::where('name', '=', WorkCategory::UNSPECIFIED_RECORD_NAME)->first()->id
        );
        $input['work_subcategory']       = array(
            WorkSubcategory::where('name', '=', WorkSubcategory::UNSPECIFIED_RECORD_NAME)->first()->id
        );
        $input['previous_cpe_grade_id']  = PreviousCPEGrade::where('grade', '=', PreviousCPEGrade::UNSPECIFIED_RECORD_GRADE)->first()->id;
        $input['current_cpe_grade_id']   = CurrentCPEGrade::where('grade', '=', CurrentCPEGrade::UNSPECIFIED_RECORD_GRADE)->first()->id;
        $input['registration_status_id'] = RegistrationStatus::where('name', '=', RegistrationStatus::UNSPECIFIED_RECORD_NAME)->first()->id;
        $input['job_limit_sign']         = JobLimitSymbol::JOB_LIMIT_SYMBOL_GREATER_THAN;
        $input['job_limit_number']       = 0;
        $input['cidb_category']          = null;
        $input['registered_date']        = null;
        $input['remarks']                = null;

        return $input;
    }

    /**
     * Updates a Contractor record to storage
     *
     * @param Contractor $contractor
     * @param            $input
     *
     * @return mixed
     */
    public function update(Contractor $contractor, $input)
    {
        $contractor = $this->insertData($contractor, $input);

        if( $save = $contractor->save() )
        {
            $contractor->workCategories()->sync($input['work_category']);
            $contractor->workSubcategories()->sync($input['work_subcategory']);
        }

        return $save;
    }

    /**
     * Generic function to insert data into the contractor object
     *
     * @param $contractor
     * @param $input
     *
     * @return mixed
     */
    public function insertData($contractor, $input)
    {
        //check if exists before associating
        $contractor->currentCPEGrade()->associate(CurrentCPEGrade::find($input['current_cpe_grade_id']));
        $contractor->previousCPEGrade()->associate(PreviousCPEGrade::find($input['previous_cpe_grade_id']));
        $contractor->registrationStatus()->associate(RegistrationStatus::find($input['registration_status_id']));

        $contractor->job_limit_sign   = $input['job_limit_sign'];
        $contractor->job_limit_number = $input['job_limit_number'] ?: 0;
        $contractor->cidb_category    = $input['cidb_category'];
        $contractor->registered_date  = $input['registered_date'] ?: null;
        $contractor->remarks          = $input['remarks'];

        return $contractor;
    }

    public function deleteUnassociatedContractors()
    {
        foreach(Contractor::all() as $contractor)
        {
            if( ! $contractor->hasCompany() )
            {
                $contractor->delete();
            }
        }
    }

}