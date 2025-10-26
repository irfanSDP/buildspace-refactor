<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

use PCK\Reports\VendorPerformanceEvaluationFormExcelGenerator;
use PCK\VendorPerformanceEvaluation\Cycle;
use PCK\VendorPerformanceEvaluation\VendorPerformanceEvaluationCompanyForm;
use PCK\Base\Helpers;
use PCK\Helpers\Files;
use PCK\Helpers\Zip;
use PCK\Helpers\PathRegistry;

class GenerateVendorEvaluationForms extends Command {

    protected $cycle;
    protected $dir;
    protected $logFilepath;
    protected $filesToZip = [];

    protected $name = 'report:generate-vpe-forms';

    protected $description = 'Generates VPE forms.';

    public function __construct()
    {
        parent::__construct();
    }

    public function fire()
    {
        ini_set('memory_limit','2048M');

        \Log::info('Running Command:GenerateVendorEvaluationForms');

        $this->setup();

        if(is_null($this->cycle))
        {
            $this->error("Invalid cycle. Cycle is incomplete or non-existant.");

            return;
        }

        if(file_exists($this->logFilepath))
        {
            $this->error('Process already running.');

            return;
        }

        $this->generate();

        \Log::info('Finished Command:GenerateVendorEvaluationForms');
    }

    protected function setup()
    {
        $this->cycle = Cycle::find($this->argument('cycle_id'));

        if(is_null($this->cycle)) return;

        $this->dir = PathRegistry::vendorPerformanceEvaluationFormReportsDir($this->cycle->id);

        Files::mkdirIfDoesNotExist($this->dir);

        $this->logFilepath = PathRegistry::vendorPerformanceEvaluationFormReportsProgressLog($this->cycle->id);
    }

    protected function log($count, $total, $companyId, $companyName)
    {
        $logFilePointer = fopen($this->logFilepath, 'a');

        $completionPercentage = round(Helpers::divide($count, $total) * 100);

        $logMessage = "[{$completionPercentage}%] ({$count}/{$total}) - Generated forms for [id: {$companyId}] {$companyName}";

        fwrite($logFilePointer, $logMessage);
        fwrite($logFilePointer, PHP_EOL);
        fclose($logFilePointer);
    }

    protected function generate()
    {
        $cycle = $this->cycle;

        $companies = VendorPerformanceEvaluationCompanyForm::select('companies.reference_no', 'companies.name', 'companies.id')
            ->join('companies', 'companies.id', '=', 'vendor_performance_evaluation_company_forms.company_id')
            ->whereHas('vendorPerformanceEvaluation', function($query) use ($cycle){
                $query->where('vendor_performance_evaluation_cycle_id', '=', $cycle->id);
            })
            ->get();

        if(!is_null($cycle))
        {
            $formsByCompany = VendorPerformanceEvaluationCompanyForm::join('companies', 'companies.id', '=', 'vendor_performance_evaluation_company_forms.company_id')
            ->join('vendor_work_categories', 'vendor_work_categories.id', '=', 'vendor_performance_evaluation_company_forms.vendor_work_category_id')
            ->whereHas('vendorPerformanceEvaluation', function($query) use ($cycle){
                $query->where('vendor_performance_evaluation_cycle_id', '=', $cycle->id);
            })
            ->orderBy('companies.name')
            ->orderBy('vendor_work_categories.name')
            ->orderBy('vendor_performance_evaluation_company_forms.status_id')
            ->get()
            ->groupBy('company_id');

            $count = 0;
            $total = $formsByCompany->count();

            foreach($formsByCompany as $companyId => $companyForms)
            {
                $reportGenerator = new VendorPerformanceEvaluationFormExcelGenerator();

                $title = trans('vendorManagement.vendorPerformanceEvaluation').' - '.$cycle->remarks;

                $reportGenerator->setSpreadsheetTitle($title);

                $filename = "{$companies->find($companyId)->name} ({$companies->find($companyId)->reference_no}).".Files::EXTENSION_EXCEL;

                $filepath = $this->dir.$filename;

                $reportGenerator->addWorkSheet($companyForms, $companies->find($companyId)->name);

                $reportGenerator->saveTo($filepath);

                unset($reportGenerator);

                $this->filesToZip[$filename] = $filepath;

                $this->log(++$count, $total, $companyId, $companies->find($companyId)->name);
            }

            $this->zip();
        }

        Files::deleteFile($this->logFilepath);
    }

    protected function zip()
    {
        $zipFilePath = PathRegistry::vendorPerformanceEvaluationFormReports($this->cycle->id);

        if( ! empty($this->filesToZip) )
        {
            Zip::zip($this->filesToZip, $zipFilePath);

            foreach($this->filesToZip as $filepath)
            {
                Files::deleteFile($filepath);
            }
        }
        else
        {
            \Log::info('No files to zip. Creating empty zip file as a placeholder');

            Zip::createEmptyZip($zipFilePath);
        }
    }

    protected function getArguments()
    {
        return array(
            array('cycle_id', InputArgument::REQUIRED, 'Id of the target cycle.'),
        );
    }
}
