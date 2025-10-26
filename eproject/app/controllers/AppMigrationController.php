<?php

use Carbon\Carbon;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use PCK\Helpers\Files;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

use PCK\ContractGroupCategory\ContractGroupCategory;
use PCK\VendorCategory\VendorCategory;
use PCK\VendorWorkCategory\VendorWorkCategory;
use PCK\VendorWorkSubcategory\VendorWorkSubcategory;
use PCK\Vendor\Vendor;
use PCK\CompanyPersonnel\CompanyPersonnel;
use PCK\Companies\Company;
use PCK\States\State;
use PCK\Countries\Country;
use PCK\ModuleParameters\VendorManagement\VendorProfileModuleParameter;
use PCK\VendorRegistration\VendorRegistration;
use PCK\VendorRegistration\Section;
use PCK\VendorRegistration\VendorProfile;
use PCK\BusinessEntityType\BusinessEntityType;
use PCK\Settings\SystemSettings;
use PCK\ModuleParameters\VendorManagement\VendorRegistrationAndPrequalificationModuleParameter;

use PCK\Forms\VendorCompanyDetailsForm;

class AppMigrationController extends \BaseController
{
    protected static $SD_CODE_COLUMN;
    protected static $ROC_NO_COLUMN;
    protected static $COMPANY_NAME_COLUMN;
    protected static $ADDRESS_COLUMN;
    protected static $POSTCODE_COLUMN;
    protected static $CITY_COLUMN;
    protected static $STATE_COLUMN;
    protected static $COUNTRY_COLUMN;
    protected static $EMAIL_COLUMN;
    protected static $DIRECTOR_COLUMN;
    protected static $SHAREHOLDER_COLUMN;
    protected static $CONTACT_NO_COLUMN;
    protected static $BUMIPUTERA_STATUS_COLUMN;
    protected static $EQUITY_STATUS_COLUMN;
    protected static $BUMIPUTERA_HOLDING_COLUMN;
    protected static $VENDOR_CATEGORY_LVL_1_COLUMN;
    protected static $VENDOR_CATEGORY_LVL_2_COLUMN;
    protected static $VENDOR_CATEGORY_LVL_3_COLUMN;
    protected static $VENDOR_CATEGORY_LVL_4_COLUMN;
    protected static $APPROVAL_DATE_COLUMN;

    protected $companyDetailsForm;

    public function __construct(VendorCompanyDetailsForm $companyDetailsForm)
    {
        $this->companyDetailsForm = $companyDetailsForm;
    }

    public function sdpIndex()
    {
        $selectedType = 1;//default supplier
        $selectedStatus = VendorRegistration::STATUS_COMPLETED;
        $migrationErrors = [];
        $records = [];

        return View::make('app_migration.sdp.index', compact('selectedStatus', 'selectedType', 'migrationErrors', 'records'));
    }

    public function sdpImportMasterList()
    {
        $request = Request::instance();

        $migrationErrors = [];
        $records = [];

        switch($request->get('type'))
        {
            case 1:
                self::$SD_CODE_COLUMN = 1;
                self::$ROC_NO_COLUMN = 2;
                self::$COMPANY_NAME_COLUMN = 3;
                self::$ADDRESS_COLUMN = 4;
                self::$POSTCODE_COLUMN = 5;
                self::$CITY_COLUMN = 6;
                self::$STATE_COLUMN = 7;
                self::$COUNTRY_COLUMN = 8;
                self::$EMAIL_COLUMN = 9;
                self::$DIRECTOR_COLUMN = 10;
                self::$SHAREHOLDER_COLUMN = 11;
                self::$CONTACT_NO_COLUMN = 12;
                self::$BUMIPUTERA_STATUS_COLUMN = 13;
                self::$EQUITY_STATUS_COLUMN = 15;
                self::$BUMIPUTERA_HOLDING_COLUMN = 16;
                self::$VENDOR_CATEGORY_LVL_1_COLUMN = 18;
                self::$VENDOR_CATEGORY_LVL_2_COLUMN = 19;
                self::$VENDOR_CATEGORY_LVL_3_COLUMN = 20;
                self::$VENDOR_CATEGORY_LVL_4_COLUMN = 21;
                self::$APPROVAL_DATE_COLUMN = 22;

                $typeTxt = 'supplier';
                break;
            case 2:
                self::$SD_CODE_COLUMN = 1;
                self::$ROC_NO_COLUMN = 2;
                self::$COMPANY_NAME_COLUMN = 3;
                self::$ADDRESS_COLUMN = 4;
                self::$POSTCODE_COLUMN = 5;
                self::$CITY_COLUMN = 6;
                self::$STATE_COLUMN = 7;
                self::$COUNTRY_COLUMN = 8;
                self::$EMAIL_COLUMN = 9;
                self::$DIRECTOR_COLUMN = 10;
                self::$SHAREHOLDER_COLUMN = 11;
                self::$CONTACT_NO_COLUMN = 12;
                self::$BUMIPUTERA_STATUS_COLUMN = 13;
                self::$EQUITY_STATUS_COLUMN = 15;
                self::$BUMIPUTERA_HOLDING_COLUMN = 16;
                self::$VENDOR_CATEGORY_LVL_1_COLUMN = 21;
                self::$VENDOR_CATEGORY_LVL_2_COLUMN = 22;
                self::$VENDOR_CATEGORY_LVL_3_COLUMN = 23;
                self::$VENDOR_CATEGORY_LVL_4_COLUMN = 24;
                self::$APPROVAL_DATE_COLUMN = 26;

                $typeTxt = 'contractor';
                break;
            case 3:
                self::$SD_CODE_COLUMN = 1;
                self::$ROC_NO_COLUMN = 2;
                self::$COMPANY_NAME_COLUMN = 3;
                self::$ADDRESS_COLUMN = 4;
                self::$POSTCODE_COLUMN = 5;
                self::$CITY_COLUMN = 6;
                self::$STATE_COLUMN = 7;
                self::$COUNTRY_COLUMN = 8;
                self::$EMAIL_COLUMN = 9;
                self::$DIRECTOR_COLUMN = 10;
                self::$SHAREHOLDER_COLUMN = 11;
                self::$CONTACT_NO_COLUMN = 12;
                self::$BUMIPUTERA_STATUS_COLUMN = 13;
                self::$EQUITY_STATUS_COLUMN = 15;
                self::$BUMIPUTERA_HOLDING_COLUMN = 16;
                self::$VENDOR_CATEGORY_LVL_1_COLUMN = 21;
                self::$VENDOR_CATEGORY_LVL_2_COLUMN = 22;
                self::$VENDOR_CATEGORY_LVL_3_COLUMN = 23;
                self::$VENDOR_CATEGORY_LVL_4_COLUMN = 24;
                self::$APPROVAL_DATE_COLUMN = 26;

                $typeTxt = 'consultant';
                break;
        }
        
        list($migrationErrors, $records) = $this->validateSdpFiles($request->file('masterlist'), $typeTxt);
        
        if(empty($migrationErrors))
        {
            $this->importRecords($records, $request->get('status'));
        }

        $selectedType = ($request->has('type')) ? (int)$request->get('type') : 1;
        $selectedStatus = ($request->has('status')) ? (int)$request->get('status') : VendorRegistration::STATUS_COMPLETED;

        if(empty($migrationErrors))
        {
            Flash::success('Successfully migrated '.count($records).' vendor records');
        }

        return View::make('app_migration.sdp.index', compact('selectedStatus', 'selectedType', 'migrationErrors', 'records'));
    }

    protected function validateSdpFiles(UploadedFile $file, $typeTxt)
    {
        $filename = 'masterlist-'.$typeTxt.'-' . time() . '.' . $file->getClientOriginalExtension();
        $path = storage_path() .DIRECTORY_SEPARATOR.'sdp-masterlist';

        Files::mkdirIfDoesNotExist($path);
        $file->move($path, $filename);

        $inputFileType = \PhpOffice\PhpSpreadsheet\IOFactory::identify($path.DIRECTORY_SEPARATOR.$filename);
        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);
        $reader->setReadDataOnly(false);

        $spreadsheet = $reader->load($path.DIRECTORY_SEPARATOR.$filename);

        $spreadsheet->setActiveSheetIndex(0);
        $sheet = $spreadsheet->getActiveSheet();

        $highestRow = $sheet->getHighestRow(); 
        $highestColumn = $sheet->getHighestColumn();

        $compulsoryFields = [
            self::$SD_CODE_COLUMN,
            self::$ROC_NO_COLUMN,
            self::$COMPANY_NAME_COLUMN,
            self::$ADDRESS_COLUMN,
            self::$POSTCODE_COLUMN,
            self::$CITY_COLUMN,
            self::$STATE_COLUMN,
            self::$COUNTRY_COLUMN,
            self::$EMAIL_COLUMN,
            self::$CONTACT_NO_COLUMN,
            self::$BUMIPUTERA_STATUS_COLUMN,
            self::$EQUITY_STATUS_COLUMN,
            self::$VENDOR_CATEGORY_LVL_1_COLUMN,
            self::$VENDOR_CATEGORY_LVL_2_COLUMN,
            self::$APPROVAL_DATE_COLUMN
        ];

        $errors = [];

        $records = [];
        $states = [];
        $countries = [];

        $defaultStates = [
            'india' => 'Mahārāshtra',
            'cyberjaya' => 'Selangor',
            'malacca' => 'Melaka',
            'penang' => 'Pulau Pinang',
            'johor bahru' => 'Johor',
            'wp- kuala lumpur' => 'Kuala Lumpur',
            'w.p. kuala lumpur' => 'Kuala Lumpur',
            'wp-putrajaya' => 'Putrajaya',
            'wp- putrajaya' => 'Putrajaya'
        ];

        $stateRecords = \DB::table('states')->orderBy('country_id', 'asc')->lists('name', 'id');

        foreach($stateRecords as $stateId => $stateName)
        {
            $states[trim(strtolower($stateName))] = $stateId;
        }

        $countryRecords = \DB::table('countries')->orderBy('id', 'asc')->lists('country', 'id');

        foreach($countryRecords as $countryId => $countryName)
        {
            $countries[trim(strtolower($countryName))] = $countryId;
        }

        $businessEntityType = \DB::table("business_entity_types")
            ->select('id', 'name')
            ->whereRaw("LOWER(name) = 'sdn bhd' ")
            ->first();

        $latestReference = Company::select('reference_id')
            ->whereRaw("reference_id ILIKE 'SDPRP%' ")
            ->orderBy('reference_id', 'desc')
            ->first();

        $referenceIdCount = ($latestReference) ? (int)str_replace('SDPRP', '', $latestReference->reference_id)+1 : 1;

        for ($row = 4; $row <= $highestRow; $row++)
        {
            $rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row, NULL, TRUE, FALSE);
            $rowData = $rowData[0];

            $bumiputeraHoldingColumnLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(self::$BUMIPUTERA_HOLDING_COLUMN+1);
            $cellValue = $sheet->getCell($bumiputeraHoldingColumnLetter.$row)->getFormattedValue();
            $rowData[self::$BUMIPUTERA_HOLDING_COLUMN]= $cellValue;

            if(trim($rowData[self::$SD_CODE_COLUMN]) && trim($rowData[self::$ROC_NO_COLUMN]) && trim(strtolower($rowData[self::$SD_CODE_COLUMN])) != 'sd code')
            {
                foreach($rowData as $fieldIdx => $data)
                {
                    if(in_array($fieldIdx, $compulsoryFields) && strlen(trim($data)) == 0 )
                    {
                        $columnLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($fieldIdx+1);
                        $errors[$row] = 'Field index '.$columnLetter.' cannot be empty';

                        continue 2;
                    }
                }

                if(array_key_exists(trim(strtolower($rowData[self::$STATE_COLUMN])), $defaultStates))
                {
                    $rowData[self::$STATE_COLUMN] = $defaultStates[trim(strtolower($rowData[self::$STATE_COLUMN]))];
                }

                if(!array_key_exists(trim(strtolower($rowData[self::$STATE_COLUMN])), $states))
                {
                    $errors[$row] = 'State '.trim($rowData[self::$STATE_COLUMN]).' not exist in the system';

                    continue;
                }

                if(!array_key_exists(trim(strtolower($rowData[self::$COUNTRY_COLUMN])), $countries))
                {
                    $errors[$row] = 'Country '.trim($rowData[self::$COUNTRY_COLUMN]).' not exist in the system';

                    continue;
                }

                $lvl1 = ContractGroupCategory::whereRaw("LOWER(name) = '".trim(strtolower($rowData[self::$VENDOR_CATEGORY_LVL_1_COLUMN]))."' ")
                    ->first();

                if(!$lvl1)
                {
                    $latestCode = ContractGroupCategory::select('code')
                    ->whereRaw("code ILIKE 'VG0%' ")
                    ->orderBy('code', 'desc')
                    ->first();

                    $codeCount = ($latestCode) ? (int)str_replace('VG', '', $latestCode->code) : 1;

                    $lvl1 = new ContractGroupCategory;
                    $lvl1->name = trim($rowData[self::$VENDOR_CATEGORY_LVL_1_COLUMN]);
                    $lvl1->code = 'VG'.sprintf('%06d', $codeCount+1);
                    $lvl1->type = ContractGroupCategory::TYPE_EXTERNAL;

                    $lvl1->save();
                }

                $lvl2 = VendorCategory::whereRaw("LOWER(name) = '".trim(strtolower($rowData[self::$VENDOR_CATEGORY_LVL_2_COLUMN]))."' ")
                    ->where('contract_group_category_id', $lvl1->id)
                    ->first();

                if(!$lvl2)
                {
                    $latestCode = VendorCategory::select('code')
                    ->whereRaw("code ILIKE 'BSVCC0%' ")
                    ->orderBy('code', 'desc')
                    ->first();

                    $codeCount = ($latestCode) ? (int)str_replace('BSVCC', '', $latestCode->code) : 1;
                    
                    $lvl2 = new VendorCategory;
                    $lvl2->name = trim($rowData[self::$VENDOR_CATEGORY_LVL_2_COLUMN]);
                    $lvl2->contract_group_category_id = $lvl1->id;
                    $lvl2->code = 'BSVCC'.sprintf('%06d', $codeCount+1);

                    $lvl2->save();
                }

                $lvl3 = null;
                if(strlen(trim($rowData[self::$VENDOR_CATEGORY_LVL_3_COLUMN])) > 0)
                {
                    $lvl3 = VendorWorkCategory::whereRaw("LOWER(name) = '".trim(strtolower($rowData[self::$VENDOR_CATEGORY_LVL_3_COLUMN]))."' ")
                    ->first();

                    if(!$lvl3)
                    {
                        $latestCode = VendorWorkCategory::select('code')
                        ->whereRaw("code ILIKE 'BSWC0%' ")
                        ->orderBy('code', 'desc')
                        ->first();

                        $codeCount = ($latestCode) ? (int)str_replace('BSWC', '', $latestCode->code) : 1;

                        $lvl3 = new VendorWorkCategory;
                        $lvl3->name = trim($rowData[self::$VENDOR_CATEGORY_LVL_3_COLUMN]);
                        $lvl3->code = 'BSWC'.sprintf('%07d', $codeCount+1);

                        $lvl3->save();
                    }
                }

                $lvl4 = null;
                if($lvl3 && strlen(trim($rowData[self::$VENDOR_CATEGORY_LVL_4_COLUMN])) > 0)
                {
                    $lvl4 = VendorWorkSubcategory::whereRaw("LOWER(name) = '".trim(strtolower($rowData[self::$VENDOR_CATEGORY_LVL_4_COLUMN]))."' ")
                    ->where('vendor_work_category_id', $lvl3->id)
                    ->first();

                    if(!$lvl4)
                    {
                        $latestCode = VendorWorkSubcategory::select('code')
                        ->whereRaw("code ILIKE 'BSWSC0%' ")
                        ->orderBy('code', 'desc')
                        ->first();

                        $codeCount = ($latestCode) ? (int)str_replace('BSWSC', '', $latestCode->code) : 1;

                        $lvl4 = new VendorWorkSubcategory;
                        $lvl4->name = trim($rowData[self::$VENDOR_CATEGORY_LVL_4_COLUMN]);
                        $lvl4->vendor_work_category_id = $lvl3->id;
                        $lvl4->code = 'BSWSC'.sprintf('%07d', $codeCount+1);

                        $lvl4->save();
                    }
                }

                $referenceNo = str_replace(',', '', trim($rowData[self::$ROC_NO_COLUMN]));

                if(!array_key_exists($referenceNo, $records))
                {
                    $referenceId = 'SDPRP'.sprintf('%011d', $referenceIdCount);
                    
                    $approvalDate = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject(trim($rowData[self::$APPROVAL_DATE_COLUMN]));
                    $activationDate = $approvalDate->format("Y-m-d H:i:s");

                    $bumiputeraEquity = preg_replace("/[^0-9.]/", "", $rowData[self::$BUMIPUTERA_HOLDING_COLUMN]);

                    $records[$referenceNo] = [
                        'name' => trim($rowData[self::$COMPANY_NAME_COLUMN]),
                        'reference_no' => mb_strtoupper(trim($referenceNo), 'UTF-8'),
                        'address' => trim($rowData[self::$ADDRESS_COLUMN])."\n".trim($rowData[self::$POSTCODE_COLUMN])."\n".trim($rowData[self::$CITY_COLUMN]),
                        'state_id' => $states[trim(strtolower($rowData[self::$STATE_COLUMN]))],
                        'state_name' => trim(strtolower($rowData[self::$STATE_COLUMN])),
                        'country_id' => $countries[trim(strtolower($rowData[self::$COUNTRY_COLUMN]))],
                        'country_name' => trim(strtolower($rowData[self::$COUNTRY_COLUMN])),
                        'email' => trim($rowData[self::$EMAIL_COLUMN]),
                        'telephone_number' => trim($rowData[self::$CONTACT_NO_COLUMN]),
                        'business_entity_type_id' => $businessEntityType->id,
                        'activation_date' => $activationDate,
                        'reference_id' => $referenceId,
                        'is_bumiputera' => (trim(strtolower($rowData[self::$BUMIPUTERA_STATUS_COLUMN])) == 'bumiputera'),
                        'bumiputera_equity' => $bumiputeraEquity,
                        'contract_group_category_id' => $lvl1->id,
                        'vendor_categories' => [],
                        'vendor_work_categories' => [],
                        'vendor_work_subcategories' => [],
                        'directors' => [],
                        'shareholders' => []
                    ];

                    $referenceIdCount++;
                }

                if(!in_array($lvl2->id, $records[$referenceNo]['vendor_categories']))
                {
                    $records[$referenceNo]['vendor_categories'][] = $lvl2->id;
                }

                if(($lvl3) && !in_array($lvl3->id, $records[$referenceNo]['vendor_work_categories']))
                {
                    $records[$referenceNo]['vendor_work_categories'][] = $lvl3->id;
                }

                if(($lvl4) && !in_array($lvl4->id, $records[$referenceNo]['vendor_work_subcategories']))
                {
                    $records[$referenceNo]['vendor_work_subcategories'][] = $lvl4->id;
                }

                if(strlen(trim($rowData[self::$DIRECTOR_COLUMN])) > 0 && !in_array(trim($rowData[self::$DIRECTOR_COLUMN]), $records[$referenceNo]['directors']))
                {
                    $records[$referenceNo]['directors'][] = trim($rowData[self::$DIRECTOR_COLUMN]);
                }
                
                if(strlen(trim($rowData[self::$SHAREHOLDER_COLUMN])) > 0 && !in_array(trim($rowData[self::$SHAREHOLDER_COLUMN]), $records[$referenceNo]['shareholders']))
                {
                    $records[$referenceNo]['shareholders'][] = trim($rowData[self::$SHAREHOLDER_COLUMN]);
                }
            }
            
        }

        Files::deleteFile($path.DIRECTORY_SEPARATOR.$filename);

        return [$errors, $records];
    }

    protected function importRecords(Array $records, $vendorRegistrationStatus)
    {
        $existingReferenceNos = Company::select('id', 'reference_no')->whereIn('reference_no', array_keys($records))->lists('reference_no');

        $existingCompanies = [];
        foreach($existingReferenceNos as $existingReferenceNo)
        {
            if(!array_key_exists($existingReferenceNo, $existingCompanies))
            {
                $existingCompanies[$existingReferenceNo] = $records[$existingReferenceNo];

                unset($records[$existingReferenceNo]);
            }
        }

        if($existingCompanies)
        {
            $companies = Company::whereIn('reference_no', array_keys($existingCompanies))->get();

            $existingVendors = Vendor::select('vendors.company_id', 'vendors.vendor_work_category_id')
                ->join('companies', 'vendors.company_id', '=', 'companies.id')
                ->whereIn('companies.reference_no', array_keys($existingCompanies))
                ->get();
            
            $existingVendorWorkCategories = [];

            foreach($existingVendors as $existingVendor)
            {
                if(!array_key_exists($existingVendor->company_id, $existingVendorWorkCategories))
                {
                    $existingVendorWorkCategories[$existingVendor->company_id] = [];
                }

                $existingVendorWorkCategories[$existingVendor->company_id][] = $existingVendor->vendor_work_category_id;
            }

            unset($existingVendors);

            foreach($companies as $company)
            {
                if(array_key_exists($company->reference_no, $existingCompanies))
                {
                    $updateValues = $existingCompanies[$company->reference_no];

                    $company->name = mb_strtoupper($updateValues['name'], 'UTF-8');
                    $company->address = $updateValues['address'];
                    $company->email = $updateValues['email'];
                    $company->telephone_number = $updateValues['telephone_number'];
                    $company->country_id = $updateValues['country_id'];
                    $company->state_id = $updateValues['state_id'];
                    $company->reference_no = mb_strtoupper($updateValues['reference_no'], 'UTF-8');
                    $company->contract_group_category_id = $updateValues['contract_group_category_id'];
                    $company->is_bumiputera = $updateValues['is_bumiputera'];
                    $company->bumiputera_equity = $updateValues['bumiputera_equity'];
                    $company->activation_date = $updateValues['activation_date'];
                    
                    $company->save();

                    if(!empty($updateValues['vendor_work_categories']))
                    {
                        foreach($updateValues['vendor_work_categories'] as $workCategoryId)
                        {
                            //check for existing vwc
                            if(array_key_exists($company->id, $existingVendorWorkCategories) && in_array($workCategoryId, $existingVendorWorkCategories[$company->id]))
                            {
                                continue;
                            }

                            $vendor = new Vendor;
                            $vendor->company_id = $company->id;
                            $vendor->vendor_work_category_id = $workCategoryId;
                            $vendor->type = Vendor::TYPE_ACTIVE;

                            $vendor->save();
                        }
                    }

                    $company->vendorCategories()->detach();

                    if(!empty($updateValues['vendor_categories']))
                    {
                        $company->vendorCategories()->sync($updateValues['vendor_categories']);
                    }

                    $vendorRegistration = $company->finalVendorRegistration;

                    if($vendorRegistration)
                    {
                        $vendorRegistration->companyPersonnel()->delete();
                    }
                    else
                    {
                        $vendorRegistration = new VendorRegistration;
                        $vendorRegistration->company_id = $company->id;
                        $vendorRegistration->status = $vendorRegistrationStatus;
                        
                        $vendorRegistration->save();

                        $now = date('Y-m-d H:i:s');
                        $sections = Section::getSections();

                        foreach($sections as $section)
                        {
                            $insertRecords = [];
                            $questionMarks = [];

                            $record = [
                                $vendorRegistration->id,
                                $section,
                                $vendorRegistrationStatus,
                                Section::AMENDMENT_STATUS_NOT_REQUIRED, //ammendment status not required
                                $now,
                                $now
                            ];

                            $insertRecords = array_merge($insertRecords, $record);
                            $questionMarks[] = '('.implode(',', array_fill(0, count($record), '?')).')';

                            if($insertRecords)
                            {
                                \DB::select("INSERT INTO vendor_registration_sections
                                (vendor_registration_id, section, status_id, amendment_status, created_at, updated_at)
                                VALUES ".implode(',', $questionMarks)." RETURNING id",
                                $insertRecords);
                            }
                        }
                    }

                    if(!$company->vendorProfile && $vendorRegistrationStatus == VendorRegistration::STATUS_COMPLETED)
                    {
                        VendorProfile::createIfNotExists($company);
                    }

                    foreach($updateValues['directors'] as $director)
                    {
                        $companyPersonnel = new CompanyPersonnel;
                        $companyPersonnel->type = CompanyPersonnel::TYPE_DIRECTOR;
                        $companyPersonnel->name = mb_strtoupper($director, 'UTF-8');
                        $companyPersonnel->identification_number = '-';
                        $companyPersonnel->vendor_registration_id = $vendorRegistration->id;
                        
                        $companyPersonnel->save();
                    }

                    foreach($updateValues['shareholders'] as $shareholder)
                    {
                        $companyPersonnel = new CompanyPersonnel;
                        $companyPersonnel->type = CompanyPersonnel::TYPE_SHAREHOLDERS;
                        $companyPersonnel->name = mb_strtoupper($shareholder, 'UTF-8');
                        $companyPersonnel->identification_number = '-';
                        $companyPersonnel->vendor_registration_id = $vendorRegistration->id;
                        
                        $companyPersonnel->save();
                    }
                }
            }
        }

        $now = date('Y-m-d H:i:s');

        self::arrayBatch($records, 200, function($batch) use($now, $vendorRegistrationStatus) {

            $insertRecords = [];
            $questionMarks = [];

            foreach($batch as $company)
            {
                $record = [
                    mb_strtoupper($company['name'], 'UTF-8'),
                    $company['address'],
                    '-',//main contact
                    $company['email'],
                    $company['telephone_number'],
                    $company['country_id'],
                    $company['state_id'],
                    mb_strtoupper($company['reference_no'], 'UTF-8'),
                    $company['reference_id'],
                    $company['contract_group_category_id'],
                    true,//confirmed
                    $company['business_entity_type_id'],
                    $company['is_bumiputera'],
                    $company['bumiputera_equity'],
                    $company['activation_date'],
                    'SDPRP-MANUAL',//third_party_app_identifier,
                    $now,
                    $now
                ];


                $insertRecords = array_merge($insertRecords, $record);
                $questionMarks[] = '('.implode(',', array_fill(0, 19, '?')).')';
            }

            try
            {
                \DB::beginTransaction();

                $insertion = \DB::select("INSERT INTO companies
                (name, address, main_contact, email, telephone_number, country_id, state_id,
                reference_no, reference_id, contract_group_category_id, confirmed, business_entity_type_id,
                is_bumiputera, bumiputera_equity, activation_date, third_party_app_identifier, created_at, updated_at)
                VALUES ".implode(',', $questionMarks)." RETURNING id, reference_no",
                $insertRecords);

                $migratedCompanyIds  = [];
                $insertRecords       = [];
                $questionMarks       = [];

                foreach($insertion as $company)
                {
                    $migratedCompanyIds[$company->reference_no] = $company->id;

                    if($vendorRegistrationStatus == VendorRegistration::STATUS_COMPLETED)
                    {
                        $record = [
                            $company->id,
                            $now,
                            $now
                        ];
    
                        $insertRecords = array_merge($insertRecords, $record);
                        $questionMarks[] = '('.implode(',', array_fill(0, count($record), '?')).')';
                    }
                }

                if($insertRecords && $vendorRegistrationStatus == VendorRegistration::STATUS_COMPLETED)
                {
                    \DB::select("INSERT INTO vendor_profiles
                    (company_id, created_at, updated_at)
                    VALUES ".implode(',', $questionMarks)." RETURNING id",
                    $insertRecords);
                }

                unset($insertion);

                $this->createCompanyVendorCategories($migratedCompanyIds, $batch);
                $this->createVendorRegistrations($migratedCompanyIds, $batch, $vendorRegistrationStatus);
                $this->createVendors($migratedCompanyIds, $batch);

                \DB::commit();
            }
            catch(\Exception $e)
            {
                \DB::rollBack();

                throw $e;
            }

            $this->createBuildspaceCompanies($batch);
        });
    }

    protected function createBuildspaceCompanies(Array $records)
    {
        if(empty($records))
        {
            return null;
        }

        $stateRecords = \DB::connection('buildspace')->table('bs_subregions')->orderBy(\DB::raw('region_id, name'), 'asc')->lists('name', 'id');

        foreach($stateRecords as $stateId => $stateName)
        {
            $states[trim(strtolower($stateName))] = $stateId;
        }

        $countryRecords = \DB::connection('buildspace')->table('bs_regions')->orderBy('id', 'asc')->lists('country', 'id');

        foreach($countryRecords as $countryId => $countryName)
        {
            $countries[trim(strtolower($countryName))] = $countryId;
        }

        $now = date('Y-m-d H:i:s');

        $insertRecords = [];
        $questionMarks = [];

        foreach($records as $company)
        {
            $shortname = substr($company['name'], 0, 20);
            $phoneNumber = substr($company['telephone_number'], 0, 20);
            $record = [
                $company['reference_id'],
                mb_strtoupper($company['name'], 'UTF-8'),
                $shortname,
                mb_strtoupper($company['reference_no'], 'UTF-8'),
                '-',
                $company['email'],
                ($phoneNumber) ? $phoneNumber : '-',
                '-',
                ($phoneNumber) ? $phoneNumber : '-',
                $company['address'],
                $states[$company['state_name']],
                $countries[$company['country_name']],
                $now,
                $now
            ];

            $insertRecords = array_merge($insertRecords, $record);
            $questionMarks[] = '('.implode(',', array_fill(0, 14, '?')).')';
        }

        if(!empty($insertRecords))
        {
            try
            {
                \DB::connection('buildspace')->beginTransaction();

                $insertion = \DB::connection('buildspace')->select("INSERT INTO bs_companies
                (reference_id, name, shortname, registration_no, contact_person_name, contact_person_email, phone_number,
                fax_number, contact_person_direct_line, address, sub_region_id, region_id, created_at, updated_at)
                VALUES ".implode(',', $questionMarks)." RETURNING id",
                $insertRecords);

                \DB::connection('buildspace')->commit();
            }
            catch(\Exception $e)
            {
                \DB::connection('buildspace')->rollBack();

                throw $e;
            }
        }
    }

    protected function createVendors(Array $migratedCompanyIds, Array $records)
    {
        if(empty($migratedCompanyIds))
        {
            return null;
        }

        $insertRecords = [];
        $questionMarks = [];

        $date = date('Y-m-d H:i:s');

        foreach($records as $companyRecord)
        {
            if(array_key_exists($companyRecord['reference_no'], $migratedCompanyIds))
            {
                foreach($companyRecord['vendor_work_categories'] as $vendorWorkCategoryId)
                {
                    $record = [
                        $migratedCompanyIds[$companyRecord['reference_no']],
                        $vendorWorkCategoryId,
                        Vendor::TYPE_ACTIVE,
                        $date,
                        $date
                    ];
    
                    $insertRecords = array_merge($insertRecords, $record);
                    $questionMarks[] = '('.implode(',', array_fill(0, count($record), '?')).')';
                }
            }
        }

        if($insertRecords)
        {
            $vendors = \DB::select("INSERT INTO vendors
            (company_id, vendor_work_category_id, type, created_at, updated_at)
            VALUES ".implode(',', $questionMarks)." RETURNING id",
            $insertRecords);
        }
    }

    protected function createVendorRegistrations(Array $migratedCompanyIds, Array $records, $vendorRegistrationStatus)
    {
        if(empty($migratedCompanyIds))
        {
            return null;
        }

        $insertRecords = [];
        $questionMarks = [];

        $date = date('Y-m-d H:i:s');

        foreach($records as $companyRecord)
        {
            if(array_key_exists($companyRecord['reference_no'], $migratedCompanyIds))
            {
                $record = [
                    $migratedCompanyIds[$companyRecord['reference_no']],
                    $vendorRegistrationStatus,
                    $date,
                    $date,
                    "EXPORTED FROM DATA MIGRATION"
                ];

                $insertRecords = array_merge($insertRecords, $record);
                $questionMarks[] = '('.implode(',', array_fill(0, count($record), '?')).')';
            }
        }

        if($insertRecords)
        {
            $vendorRegistrations = \DB::select("INSERT INTO vendor_registrations
            (company_id, status, created_at, updated_at, processor_remarks)
            VALUES ".implode(',', $questionMarks)." RETURNING id, company_id",
            $insertRecords);

            $sections = Section::getSections();

            $insertRecords = [];
            $questionMarks = [];
            
            foreach($sections as $section)
            {
                $insertRecords = [];
                $questionMarks = [];

                foreach($vendorRegistrations as $vendorRegistration)
                {
                    $record = [
                        $vendorRegistration->id,
                        $section,
                        $vendorRegistrationStatus,
                        Section::AMENDMENT_STATUS_NOT_REQUIRED, //ammendment status not required
                        $date,
                        $date
                    ];

                    $insertRecords = array_merge($insertRecords, $record);
                    $questionMarks[] = '('.implode(',', array_fill(0, count($record), '?')).')';
                }

                if($insertRecords)
                {
                    \DB::select("INSERT INTO vendor_registration_sections
                    (vendor_registration_id, section, status_id, amendment_status, created_at, updated_at)
                    VALUES ".implode(',', $questionMarks)." RETURNING id",
                    $insertRecords);
                }
            }

            $insertRecords = [];
            $questionMarks = [];

            $insertShareholderRecords = [];
            $shareholderQuestionMarks = [];

            $vendorRegistrationByCompanyIds = [];

            foreach($vendorRegistrations as $vendorRegistration)
            {
                $vendorRegistrationByCompanyIds[$vendorRegistration->company_id] = $vendorRegistration->id;
            }

            unset($vendorRegistrations);

            foreach($records as $companyRecord)
            {
                if(array_key_exists($companyRecord['reference_no'], $migratedCompanyIds))
                {
                    foreach($companyRecord['directors'] as $director)
                    {
                        $record = [
                            CompanyPersonnel::TYPE_DIRECTOR,
                            mb_strtoupper($director, 'UTF-8'),
                            $vendorRegistrationByCompanyIds[$migratedCompanyIds[$companyRecord['reference_no']]],
                            '-',//id no
                            $date,
                            $date
                        ];
        
                        $insertRecords = array_merge($insertRecords, $record);
                        $questionMarks[] = '('.implode(',', array_fill(0, count($record), '?')).')';
                    }

                    foreach($companyRecord['shareholders'] as $shareholder)
                    {
                        $record = [
                            CompanyPersonnel::TYPE_SHAREHOLDERS,
                            mb_strtoupper($shareholder, 'UTF-8'),
                            $vendorRegistrationByCompanyIds[$migratedCompanyIds[$companyRecord['reference_no']]],
                            '-',//id no
                            $date,
                            $date
                        ];
        
                        $insertShareholderRecords = array_merge($insertShareholderRecords, $record);
                        $shareholderQuestionMarks[] = '('.implode(',', array_fill(0, count($record), '?')).')';
                    }
                }
            }

            if($insertRecords)
            {
                \DB::select("INSERT INTO company_personnel
                    (type, name, vendor_registration_id, identification_number, created_at, updated_at)
                    VALUES ".implode(',', $questionMarks)." RETURNING id",
                    $insertRecords);
            }

            if($insertShareholderRecords)
            {
                \DB::select("INSERT INTO company_personnel
                    (type, name, vendor_registration_id, identification_number, created_at, updated_at)
                    VALUES ".implode(',', $shareholderQuestionMarks)." RETURNING id",
                    $insertShareholderRecords);
            }
        }
    }

    protected function createCompanyVendorCategories(Array $migratedCompanyIds, Array $records)
    {
        if(empty($migratedCompanyIds))
        {
            return null;
        }

        $insertRecords = [];
        $questionMarks = [];

        foreach($records as $companyRecord)
        {
            if(array_key_exists($companyRecord['reference_no'], $migratedCompanyIds))
            {
                foreach($companyRecord['vendor_categories'] as $vendorCategoryId)
                {
                    $record = [
                        $migratedCompanyIds[$companyRecord['reference_no']],
                        $vendorCategoryId
                    ];
    
                    $insertRecords = array_merge($insertRecords, $record);
                    $questionMarks[] = '('.implode(',', array_fill(0, count($record), '?')).')';
                }
            }
        }

        if($insertRecords)
        {
            $insertion = \DB::select("INSERT INTO company_vendor_category
            (company_id, vendor_category_id)
            VALUES ".implode(',', $questionMarks)." RETURNING company_id",
            $insertRecords);
        }
    }

    public function vendorCreate()
    {
        $user       = \Confide::user();
        $urlCountry = route('country');
        $urlStates  = route('country.states');
        $countryId  = Input::old('country_id');
        $stateId    = Input::old('state_id');

        $urlContractGroupCategories = route('registration.contractGroupCategories');
        $urlVendorCategories        = route('registration.vendorCategories');
        $contractGroupCategoryId    = Input::old('contract_group_category_id');
        $vendorCategoryId           = Input::old('vendor_category_id');
        $businessEntityTypeId       = Input::old('business_entity_type_id');
        $businessEntityTypeName     = Input::old('business_entity_type_other');
        $businessEntityTypes        = BusinessEntityType::where('hidden', false)->orderBy('id', 'asc')->get();

        $multipleVendorCategories = VendorRegistrationAndPrequalificationModuleParameter::getValue('allow_only_one_comp_to_reg_under_multi_vendor_category');

        $allowOtherBusinessEntityTypes = SystemSettings::getValue('allow_other_business_entity_types');

        JavaScript::put(compact('urlCountry', 'urlStates', 'countryId', 'stateId', 'urlContractGroupCategories', 'urlVendorCategories', 'contractGroupCategoryId', 'vendorCategoryId', 'businessEntityTypeId', 'businessEntityTypeName'));

        return View::make('app_migration.vendor.edit', compact(
            'user',
            'multipleVendorCategories',
            'businessEntityTypes',
            'allowOtherBusinessEntityTypes'
        ));
    }

    public function vendorStore()
    {
        $request = Request::instance();

        try
        {
            $this->companyDetailsForm->validate($request->all());
        }
        catch(\Exception $e)
        {
            return Redirect::back()
                ->withErrors($this->companyDetailsForm->getErrors(), 'company')
                ->withInput(Input::all());
        }

        $user    = \Confide::user();
        $company = new Company();

        $company->name = mb_strtoupper(trim($request->get('name')));
        $company->confirmed = true;
        $company->reference_no = mb_strtoupper(trim($request->get('reference_no')));
        $company->address = $request->get('address');
        $company->country_id = (int)$request->get('country_id');
        $company->state_id = (int)$request->get('state_id');
        $company->contract_group_category_id = (int)$request->get('contract_group_category_id');
        $company->tax_registration_no = mb_strtoupper(trim($request->get('tax_registration_no')));
        $company->main_contact = mb_strtoupper(trim($request->get('main_contact')));
        $company->email = trim($request->get('email'));
        $company->telephone_number = trim($request->get('telephone_number'));
        $company->fax_number = trim($request->get('fax_number'));
        $company->is_bumiputera = $request->has('is_bumiputera');
        $company->bumiputera_equity = $request->get('bumiputera_equity');
        $company->non_bumiputera_equity = $request->get('non_bumiputera_equity');
        $company->foreigner_equity = $request->get('foreigner_equity');
        $company->activation_date = date('Y-m-d H:i:s', strtotime($request->get('activation_date')));
        $company->expiry_date = date('Y-m-d H:i:s', strtotime($request->get('expiry_date')));

        $company->company_status = ($request->has('is_bumiputera')) ? Company::COMPANY_STATUS_BUMIPUTERA : Company::COMPANY_STATUS_NON_BUMIPUTERA;

        $company->save();

        $company->vendorCategories()->sync($request->get('vendor_category_id'));

        VendorProfile::createIfNotExists($company);

        $vendorRegistration = new VendorRegistration();

        $vendorRegistration->company_id = $company->id;
        $vendorRegistration->status = VendorRegistration::STATUS_COMPLETED;

        $vendorRegistration->save();

        \Flash::success(trans('forms.saved'));

        return Redirect::route('vendorProfile.show', [$company->id]);
    }

    protected static function arrayBatch($arr, $batchSize, $closure)
    {
        $batch = [];
        foreach($arr as $i)
        {
            $batch[] = $i;
            // See if we have the right amount in the batch
            if(count($batch) === $batchSize)
            {
                // Pass the batch into the Closure
                $closure($batch);
                // Reset the batch
                $batch = [];
            }
        }
        // See if we have any leftover ids to process
        if(count($batch)) $closure($batch);
    }
}