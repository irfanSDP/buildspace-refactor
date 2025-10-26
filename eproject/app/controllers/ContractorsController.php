<?php

use PCK\Companies\Company;
use PCK\Companies\CompanyRepository;
use PCK\Contractors\Contractor;
use PCK\Contractors\ContractorDetails\JobLimitSymbol;
use PCK\Contractors\ContractorDetails\RegistrationStatus;
use PCK\Contractors\ContractorRepository;
use PCK\CPEGrades\CurrentCPEGrade;
use PCK\CPEGrades\PreviousCPEGrade;
use PCK\Forms\ContractorDetailsForm;
use PCK\WorkCategories\WorkCategory;
use PCK\WorkCategories\WorkSubcategory;

class ContractorsController extends \BaseController {

    private $contractorDetailsForm;
    private $contractorRepo;
    private $companyRepo;

    public function __construct(
        ContractorDetailsForm $contractorDetailsForm,
        ContractorRepository $contractorRepo,
        CompanyRepository $companyRepo
    )
    {
        $this->contractorDetailsForm = $contractorDetailsForm;
        $this->contractorRepo        = $contractorRepo;
        $this->companyRepo           = $companyRepo;
    }

    public function index()
    {
        $this->contractorRepo->deleteUnassociatedContractors();

        return View::make('contractors.index');
    }

    public function ajaxGetContractorsDataInJson()
    {
        $records = $this->contractorRepo->dataTableResponse(Input::all());

        foreach($records['aaData'] as &$record)
        {
            $record["jobLimit"]           = number_format($record["jobLimit"], 2, '.', ',');
            $record["cidbCategory"]       = ( trim($record["cidbCategory"]) != '' ? $record["cidbCategory"] : '-' );
            $record["remarks"]            = ( trim($record["remarks"]) != '' ? $record["remarks"] : '-' );
            $record["jobLimitSymbol"]     = $record["jobLimitSymbol"] ? Contractor::getJobLimitSymbolSymbolById($record["jobLimitSymbol"]) : '';
            $record["registrationStatus"] = $record["registrationStatus"] ?? '-';
            $record["previousCPE"]        = $record["previousCPE"] ?? '-';
            $record["currentCPE"]         = $record["currentCPE"] ?? '-';
            $record["registeredDate"]     = isset( $record["registeredDate"] ) ? \Carbon\Carbon::parse($record["registeredDate"])->format(\Config::get('dates.submission_date_formatting')) : '-';
        }

        return Response::json($records);
    }

    public function create($company_id)
    {
        $user = \Confide::user();

        $company               = Company::find($company_id);
        $workCategories        = WorkCategory::lists('name', 'id');
        $workSubcategories     = WorkSubcategory::lists('name', 'id');
        $registration_statuses = RegistrationStatus::lists('name', 'id');
        $previous_cpe_grades   = PreviousCPEGrade::lists('grade', 'id');
        $current_cpe_grades    = CurrentCPEGrade::lists('grade', 'id');
        $job_limit_symbol      = [
            JobLimitSymbol::JOB_LIMIT_SYMBOL_GREATER_THAN => JobLimitSymbol::JOB_LIMIT_SYMBOL_GREATER_THAN_TEXT,
            JobLimitSymbol::JOB_LIMIT_SYMBOL_LESS_THAN    => JobLimitSymbol::JOB_LIMIT_SYMBOL_LESS_THAN_TEXT
        ];

        return View::make('contractors.create', compact(
            'company',
            'company_id',
            'user',
            'workCategories',
            'workSubcategories',
            'registration_statuses',
            'job_limit_symbol',
            'current_cpe_grades',
            'previous_cpe_grades'
        ));
    }

    public function store($companyId)
    {
        $inputs = Input::all();

        $this->contractorDetailsForm->validate($inputs);

        $this->contractorRepo->add($inputs, $this->companyRepo->find($companyId));

        Flash::success("Company {$this->companyRepo->find($companyId)->name}'s Contractor Details successfully added!");

        return Redirect::route('companies');
    }

    /**
     * Store a newly created resource in storage with the default details.
     *
     * @param $companyId
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeDefault($companyId)
    {
        $input = $this->contractorRepo->getDefaultContractorInput();

        $this->contractorDetailsForm->validate($input);

        $this->contractorRepo->add($input, $this->companyRepo->find($companyId));

        return Redirect::route('companies');
    }

    public function show($companyId)
    {
        $company    = Company::findOrFail($companyId);
        $contractor = $company->contractor;

        return View::make('contractors.show', compact('contractor', 'company'));
    }

    public function edit($company_id, $id)
    {
        $user = \Confide::user();

        $company               = Company::find($company_id);
        $contractor            = $this->contractorRepo->find($id);
        $workCategories        = WorkCategory::lists('name', 'id');
        $workSubcategories     = WorkSubcategory::lists('name', 'id');
        $registration_statuses = RegistrationStatus::lists('name', 'id');
        $previous_cpe_grades   = PreviousCPEGrade::lists('grade', 'id');
        $current_cpe_grades    = CurrentCPEGrade::lists('grade', 'id');
        $job_limit_symbol      = [
            JobLimitSymbol::JOB_LIMIT_SYMBOL_GREATER_THAN => JobLimitSymbol::JOB_LIMIT_SYMBOL_GREATER_THAN_TEXT,
            JobLimitSymbol::JOB_LIMIT_SYMBOL_LESS_THAN    => JobLimitSymbol::JOB_LIMIT_SYMBOL_LESS_THAN_TEXT
        ];

        return View::make('contractors.edit', compact(
            'company',
            'contractor',
            'company_id',
            'user',
            'workCategories',
            'workSubcategories',
            'registration_statuses',
            'job_limit_symbol',
            'current_cpe_grades',
            'previous_cpe_grades'
        ));
    }

    public function update($compId, $id)
    {
        $input = Input::all();

        $this->contractorDetailsForm->validate($input);

        $contractor = $this->contractorRepo->find($id);
        $this->contractorRepo->update($contractor, $input);
        $company = $this->companyRepo->find($compId);

        Flash::success("Company {$company->name}'s Contractor Details successfully updated!");

        if( \Confide::user()->isSuperAdmin() )
        {
            return Redirect::route('companies');
        }

        return Redirect::route('companies.profile');
    }

}