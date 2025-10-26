<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use PCK\Companies\Company;
use Carbon\Carbon;
use PCK\VendorRegistration\VendorRegistration;
use PCK\VendorPreQualification\VendorPreQualification;
use PCK\VendorPreQualification\TemplateForm;
use PCK\WeightedNode\WeightedNode;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class ImportVendorPreQualifications extends Command {

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'vendor-management:import-vendor-pre-qualifications';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sets/updates the pre-qualifications of vendors. Removes the previous records';

    const COLUMN_VENDOR_ROC = 2;
    const COLUMN_VENDOR_SCORE = 3;

    protected $data = [];
    protected $companies = [];
    protected $templateWeightedNode;

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function fire()
    {
        $transaction = new \PCK\Helpers\DBTransaction();
        $transaction->begin();

        try
        {
            $this->readData();
            $this->process();

            $transaction->commit();
        }
        catch(\Exception $e)
        {
            $transaction->rollback();

            $this->error($e->getMessage());
            $this->error($e->getTraceAsString());
        }
    }

    public function readData()
    {
        $filePath = $this->argument('file');

        if(!file_exists($filePath))
        {
            $this->info("File not found.");

            return false;
        }

        $inputFileType = \PhpOffice\PhpSpreadsheet\IOFactory::identify($filePath);
        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);

        $reader->setReadDataOnly(true);

        $spreadsheet = $reader->load($filePath);

        if(!$spreadsheet)
        {
            $this->info('Unable to load spreadsheet');

            return false;
        }

        $spreadsheet->setActiveSheetIndex(0);
        $sheet = $spreadsheet->getActiveSheet();

        $highestRow = $sheet->getHighestRow(); 
        $highestColumn = $sheet->getHighestColumn();

        for ($row = 2; $row <= $highestRow; $row++)
        {
            $rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row, NULL, TRUE, FALSE);
            $rowData = $rowData[0];

            if(empty($rowData[self::COLUMN_VENDOR_SCORE]) && $rowData[self::COLUMN_VENDOR_SCORE] !== 0) continue;

            $this->data[trim($rowData[self::COLUMN_VENDOR_ROC])] = $rowData[self::COLUMN_VENDOR_SCORE];
        }
    }

    protected function getCompaniesWithoutPreQs()
    {
        $bindings = [];
        $params = [];

        $count = 0;

        foreach($this->data as $rocNumber => $score)
        {
            $bindings["roc_{$count}"] = $rocNumber;
            $params[] = ":roc_{$count}";
            $count++;
        }

        $implodedParams = implode(',', $params);

        $this->companies = \DB::select(\DB::raw("
            select id
            from companies c
            where not exists (
                select 1
                from vendor_pre_qualifications vpq
                join vendor_registrations vr on vr.id = vpq.vendor_registration_id
                where vr.company_id = c.id
                and vr.deleted_at is null
                and vpq.deleted_at is null
            )
            and exists (
                select 1 from vendors v
                where v.company_id = c.id
            )
            and c.reference_no IN ({$implodedParams})
            "), $bindings);
    }

    public function process()
    {
        $this->getCompaniesWithoutPreQs();

        $count = 0;

        $companyIds = [];

        foreach($this->companies as $company)
        {
            $companyIds[] = $company->id;
        }

        $companies = Company::with('vendorRegistration')->with('vendors')->whereIn('id', $companyIds)->get();

        foreach($companies as $company)
        {
            print_r(++$count."/".count($companies));
            print_r(PHP_EOL);

            $this->createCompanyPreQs($company);
        }
    }

    protected $vendorRegistrationStatusToPreQualificationStatusMap = [
        VendorRegistration::STATUS_DRAFT => VendorPreQualification::STATUS_DRAFT,
        VendorRegistration::STATUS_SUBMITTED => VendorPreQualification::STATUS_SUBMITTED,
        VendorRegistration::STATUS_PROCESSING => VendorPreQualification::STATUS_SUBMITTED,
        VendorRegistration::STATUS_PENDING_VERIFICATION => VendorPreQualification::STATUS_PENDING_VERIFICATION,
        VendorRegistration::STATUS_COMPLETED => VendorPreQualification::STATUS_COMPLETED,
    ];

    protected function createCompanyPreQs(Company $company)
    {
        foreach($company->vendors as $vendor)
        {
            $vendorPreQualification = VendorPreQualification::firstOrNew(array(
                'vendor_registration_id' => $company->vendorRegistration->id,
                'vendor_work_category_id' => $vendor->vendor_work_category_id,
            ));

            $weightedNode = WeightedNode::create([]);

            $vendorPreQualification->weighted_node_id = $weightedNode->id;

            $vendorPreQualification->score = $this->data[$company->reference_no] ?? 0;

            $vendorPreQualification->status_id = $this->vendorRegistrationStatusToPreQualificationStatusMap[$company->vendorRegistration->status];

            $vendorPreQualification->save();
        }
    }

    protected function getArguments()
    {
        return array(
                array('file', InputArgument::REQUIRED, 'Path of the file to read.'),
        );
    }
}