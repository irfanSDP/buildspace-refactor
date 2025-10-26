<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use PCK\Companies\Company;
use PCK\Projects\Project;
use Carbon\Carbon;
use PCK\VendorPerformanceEvaluation\VendorPerformanceEvaluation;
use PCK\VendorPerformanceEvaluation\VendorPerformanceEvaluationSetup;
use PCK\VendorPerformanceEvaluation\VendorPerformanceEvaluationCompanyForm;
use PCK\VendorWorkCategory\VendorWorkCategory;
use PCK\VendorPerformanceEvaluation\Cycle;
use PCK\Vendor\Vendor;
use Illuminate\Database\Schema\Blueprint;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class UpdateVendorPerformanceEvaluationVendorWorkCategories extends Command {

    protected $currentRow;
    protected $removedProjects = [];
    protected $removedEvaluations = [];
    protected $mismatchingVendorWorkCategories = [];
    protected $vendorWorkCategoriesLevelValidatorErrorLog = [];

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'vendor-management:update-vendor-performance-evaluation-vendor-work-categories';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reassigns the vendor work categories for VPE.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function fire()
    {
        ini_set('memory_limit', '2048M');

        $transaction = new \PCK\Helpers\DBTransaction();
        $transaction->begin();

        try
        {
            \DB::statement("ALTER TABLE vendor_performance_evaluation_setups DROP CONSTRAINT vendor_performance_evaluation_setups_unique");

            $this->readDataAndProcess();

            Schema::table('vendor_performance_evaluation_setups', function(Blueprint $table)
            {
                $table->unique(['vendor_performance_evaluation_id', 'vendor_work_category_id', 'company_id'], 'vendor_performance_evaluation_setups_unique');
            });

            $transaction->commit();
        }
        catch(\Exception $e)
        {
            $transaction->rollback();

            $this->error($e->getMessage());
            $this->error($e->getTraceAsString());
        }

        $this->outputLogs();
    }

    protected function outputLogs()
    {
        foreach($this->removedProjects as $contractNumber)
        {
            print_r("Ignored project: {$contractNumber}");
            print_r(PHP_EOL);
        }

        foreach($this->removedEvaluations as $contractNumber)
        {
            print_r("Ignored evaluation for project: {$contractNumber}");
            print_r(PHP_EOL);
        }

        foreach($this->vendorWorkCategoriesLevelValidatorErrorLog as $data)
        {
            print_r("(Row: {$data['row']}) Vendor Work Categories level validator error. Mismatch [Vendor Group: {$data['contract_group_category_id']}, Vendor Category: {$data['vendor_category_id']}, Vendor Work Category: {$data['vendor_work_category_id']}]. Count: {$data['count']}.");
            print_r(PHP_EOL);
        }

        foreach($this->mismatchingVendorWorkCategories as $data)
        {
            print_r("(Row: {$data['row']}) Mismatching vendor work category [Company: {$data['company']} ({$data['company_id']}), Vendor Work Category: {$data['vendor_work_category']} ({$data['vendor_work_category_id']})]");
            print_r(PHP_EOL);
        }
    }

    const COLUMN_PROJECT_ID = 0;
    const COLUMN_VENDOR_ID = 1;
    const STARTING_ROW = 2;
    const COLUMN_PROJECT_CONTRACT_NUMBER = 3;
    const COLUMN_VENDOR_NAME = 4;
    const COLUMN_VENDOR_GROUP = 5;
    const COLUMN_VENDOR_CATEGORY = 6;
    const COLUMN_VENDOR_WORK_CATEGORY = 7;
    const COLUMN_REMARKS = 8;

    public function readDataAndProcess()
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

        $data = [];

        $startingRow = self::STARTING_ROW;

        for ($row = $startingRow; $row <= $highestRow; $row++)
        {
            $rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row, NULL, TRUE, FALSE);
            $rowData = $rowData[0];

            if(!empty($rowData[self::COLUMN_REMARKS])) continue;

            foreach($rowData as $key => $value)
            {
                $rowData[$key] = trim($rowData[$key]);
                $rowData['row'] = $row;
            }

            $data[] = $rowData;
        }

        foreach($data as $rowData)
        {
            $this->validateLevels($rowData);
        }

        foreach($data as $rowData)
        {
            $this->processRow($rowData);
        }
    }

    protected function validateLevels($rowData)
    {
        $matchingVendorWorkCategories = VendorWorkCategory::select('vendor_work_categories.id')
            ->join('vendor_category_vendor_work_category', 'vendor_category_vendor_work_category.vendor_work_category_id', '=', 'vendor_work_categories.id')
            ->join('vendor_categories', 'vendor_categories.id', '=', 'vendor_category_vendor_work_category.vendor_category_id')
            ->join('contract_group_categories', 'contract_group_categories.id', '=', 'vendor_categories.contract_group_category_id')
            ->where('vendor_work_categories.name', 'ilike', $rowData[self::COLUMN_VENDOR_WORK_CATEGORY])
            ->where('vendor_categories.name', 'ilike', $rowData[self::COLUMN_VENDOR_CATEGORY])
            ->where('contract_group_categories.name', 'ilike', $rowData[self::COLUMN_VENDOR_GROUP])
            ->get();

        // If count < 1, the vendor work category doesn't exist.
        // If count > 1, there are more than 1 possible vendor work categories to choose from, and we won't know which to choose.
        if($matchingVendorWorkCategories->count() !== 1)
        {
            $this->vendorWorkCategoriesLevelValidatorErrorLog[] = [
                'row' => $rowData['row'],
                'contract_group_category_id' => $rowData[self::COLUMN_VENDOR_GROUP],
                'vendor_category_id' => $rowData[self::COLUMN_VENDOR_CATEGORY],
                'vendor_work_category_id' => $rowData[self::COLUMN_VENDOR_WORK_CATEGORY],
                'count' => $matchingVendorWorkCategories->count(),
            ];
        }
    }

    protected function processRow($rowData)
    {
        if(!empty($rowData[self::COLUMN_REMARKS])) return;

        $project = Project::where('reference', '=', $rowData[self::COLUMN_PROJECT_CONTRACT_NUMBER])->first();

        if(!$project)
        {
            $this->removedProjects[$rowData[self::COLUMN_PROJECT_CONTRACT_NUMBER]] = $rowData[self::COLUMN_PROJECT_CONTRACT_NUMBER];
            return;
        }

        $evaluation = VendorPerformanceEvaluation::where('project_id', '=', $project->id)->first();

        if(!$evaluation)
        {
            $this->removedEvaluations[$rowData[self::COLUMN_PROJECT_CONTRACT_NUMBER]] = $rowData[self::COLUMN_PROJECT_CONTRACT_NUMBER];
            return;
        }

        $company = Company::where('id', '=', $rowData[self::COLUMN_VENDOR_ID])
            ->where('name', '=', $rowData[self::COLUMN_VENDOR_NAME])
            ->first();

        if(!$company) throw new Exception("No Company exists");

        $matchingVendorWorkCategories = VendorWorkCategory::select('vendor_work_categories.id')
            ->join('vendor_category_vendor_work_category', 'vendor_category_vendor_work_category.vendor_work_category_id', '=', 'vendor_work_categories.id')
            ->join('vendor_categories', 'vendor_categories.id', '=', 'vendor_category_vendor_work_category.vendor_category_id')
            ->join('contract_group_categories', 'contract_group_categories.id', '=', 'vendor_categories.contract_group_category_id')
            ->where('vendor_work_categories.name', 'ilike', $rowData[self::COLUMN_VENDOR_WORK_CATEGORY])
            ->where('vendor_categories.name', 'ilike', $rowData[self::COLUMN_VENDOR_CATEGORY])
            ->where('contract_group_categories.name', 'ilike', $rowData[self::COLUMN_VENDOR_GROUP])
            ->get();

        // If count < 1, the vendor work category doesn't exist.
        // If count > 1, there are more than 1 possible vendor work categories to choose from, and we won't know which to choose.
        if($matchingVendorWorkCategories->count() !== 1)
        {
            return;
        }

        $this->reassign($rowData, $company->id, $project->id, $matchingVendorWorkCategories->first()->id);
    }

    protected function reassign($rowData, $companyId, $projectId, $newVendorWorkCategoryId)
    {
        // Making sure the vendor has the vendor work category assigned.
        $vendorRecord = Vendor::where('vendor_work_category_id', '=', $newVendorWorkCategoryId)
            ->where('company_id', '=', $companyId)
            ->first();

        if(is_null($vendorRecord))
        {
            $this->mismatchingVendorWorkCategories[] = ['company_id' => $companyId, 'company' => Company::find($companyId)->name, 'vendor_work_category_id' => $newVendorWorkCategoryId, 'vendor_work_category' => VendorWorkCategory::find($newVendorWorkCategoryId)->name, 'row' => $rowData['row']];
            return;
        }

        // The setup randomly selected during the migration.
        $currentActiveSetups = VendorPerformanceEvaluationSetup::whereHas('vendorPerformanceEvaluation', function($q) use ($projectId)
        { 
            $q->where('project_id', '=', $projectId);
        })
        ->where('company_id', '=', $companyId)
        ->whereNotNull('template_node_id')
        ->get();

        if($currentActiveSetups->count() != 1)
        {
            // Must be new projects
            return;
        }

        $currentActiveSetup = $currentActiveSetups->first();

        $forms = VendorPerformanceEvaluationCompanyForm::whereHas('vendorPerformanceEvaluation', function($q) use ($projectId)
        { 
            $q->where('project_id', '=', $projectId);
        })
        ->where('company_id', '=', $companyId)
        ->get();

        if($forms->count() != 1)
        {
            throw new \Exception("Current active forms [c: {$companyId}, p: {$projectId}] is {$currentActiveSetups->count()}. Expecting 1.");
        }

        $currentActiveForm = $forms->first();

        $targetSetup = VendorPerformanceEvaluationSetup::whereHas('vendorPerformanceEvaluation', function($q) use ($projectId)
        { 
            $q->where('project_id', '=', $projectId);
        })
        ->where('company_id', '=', $companyId)
        ->where('vendor_work_category_id', '=', $newVendorWorkCategoryId)
        ->first();

        if($targetSetup && $targetSetup->id != $currentActiveSetup->id)
        {
            // It's not correctly assigned to the vendor work category.
            // Swap vendor_work_category_id.
            \DB::statement("UPDATE vendor_performance_evaluation_setups
                SET vendor_work_category_id =
                    CASE WHEN vendor_work_category_id = {$targetSetup->vendor_work_category_id}
                        THEN {$currentActiveSetup->vendor_work_category_id}
                        ELSE {$targetSetup->vendor_work_category_id}
                    END
                WHERE id IN ({$targetSetup->id}, {$currentActiveSetup->id})");
        }

        \DB::statement("UPDATE vendor_performance_evaluation_company_forms SET vendor_work_category_id = {$newVendorWorkCategoryId} WHERE id = {$currentActiveForm->id};");
    }

    protected function getArguments()
    {
        return array(
                array('file', InputArgument::REQUIRED, 'Path of the file to read.'),
        );
    }
}