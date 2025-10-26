<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\ConsoleOutput;

use Carbon\Carbon;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

use PCK\Projects\Project;
use PCK\Helpers\StringOperations;

use PCK\Buildspace\Project as BsProject;
use PCK\Buildspace\PostContract;
use PCK\Buildspace\ClaimCertificate;
use PCK\Buildspace\BillType;
use PCK\Buildspace\BillItem;
use PCK\Buildspace\VariationOrderItem;

class GenerateS4HanaContractDataFiles extends Command {

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 's4hana:generate-contract';

    protected $projectId = null;//6037;//11666;//;
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate contract files for SAP S/4Hana integration.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function fire()
    {
        $output = new ConsoleOutput();

        ProgressBar::setFormatDefinition('custom', "%status%\n%current%/%max% [%bar%] %percent:3s%%\n  %estimated:-6s%  %memory:6s%");

        $progressBar = new ProgressBar($output, 9);
        $progressBar->setFormat('custom');

        $progressBar->setMessage('Querying latest claim certificates...', 'status');
        $progressBar->start();

        $latestClaimCertificateQuery = ClaimCertificate::getClaimCertificateQuery();

        $latestClaimCertificateQuery->join(\DB::raw("(SELECT post_contract_id, MAX(version) AS version
        FROM bs_post_contract_claim_revisions
        WHERE locked_status IS TRUE
        AND deleted_at IS NULL
        GROUP BY post_contract_id) b"), function($join)
        {
            $join->on('bs_post_contract_claim_revisions.post_contract_id', '=', 'b.post_contract_id');
            $join->on('bs_post_contract_claim_revisions.version','=', 'b.version');
        });

        if($this->projectId)
        {
            $latestClaimCertificateQuery->where('bs_project_structures.id', $this->projectId);
        }

        $latestClaimCertificateQuery->where('bs_claim_certificates.status', '=', ClaimCertificate::STATUS_TYPE_APPROVED)
        ->whereRaw('bs_post_contract_claim_revisions.locked_status IS TRUE');

        $latestClaimCertificateQuery->orderByRaw('bs_post_contract_claim_revisions.post_contract_id, bs_claim_certificates.updated_at, bs_post_contract_claim_revisions.version DESC');

        $latestClaimCertificateIds = $latestClaimCertificateQuery->lists("id");

        $query = \DB::connection('buildspace')->table("bs_project_structures")
        ->select(\DB::raw('bs_claim_certificates.id, bs_claim_certificate_approval_logs.created_at'))
        ->join('bs_project_main_information', 'bs_project_structures.id', '=', 'bs_project_main_information.project_structure_id')
        ->join('bs_new_post_contract_form_information', 'bs_new_post_contract_form_information.project_structure_id', '=', 'bs_project_structures.id')
        ->join('bs_post_contracts', 'bs_project_main_information.project_structure_id', '=', 'bs_post_contracts.project_structure_id')
        ->join('bs_post_contract_claim_revisions', 'bs_post_contracts.id', '=', 'bs_post_contract_claim_revisions.post_contract_id')
        ->join('bs_claim_certificates', 'bs_post_contract_claim_revisions.id', '=', 'bs_claim_certificates.post_contract_claim_revision_id')
        ->join('bs_claim_certificate_approval_logs', 'bs_claim_certificates.id', '=', 'bs_claim_certificate_approval_logs.claim_certificate_id')
        ->whereIn('bs_claim_certificates.id', $latestClaimCertificateIds)
        ->where('bs_claim_certificate_approval_logs.status', ClaimCertificate::STATUS_TYPE_APPROVED);

        if($this->projectId)
        {
            $query->where('bs_project_structures.id', $this->projectId);
        }

        $approvedClaimCertLogs = $query->where('bs_project_structures.type', BsProject::TYPE_ROOT)
        ->where('bs_claim_certificates.status', '=', ClaimCertificate::STATUS_TYPE_APPROVED)
        ->where('bs_post_contract_claim_revisions.locked_status', true)
        ->whereNull('bs_project_structures.deleted_at')
        ->whereNull('bs_post_contract_claim_revisions.deleted_at')
        ->lists("created_at", "id");

        $progressBar->advance();//get claim certs

        $progressBar->setMessage('Preparing non preliminary claims...', 'status');

        list($nonPrelimProjectCurrentRevisions, $nonPrelimProjectPreviousRevisions) = $this->getProjectRevisions($latestClaimCertificateIds);

        $progressBar->advance();//get non prelim project revisions

        $progressBar->setMessage('Preparing preliminary claims...', 'status');

        list($prelimProjectCurrentRevisions, $prelimProjectPreviousRevisions) = $this->getProjectRevisions($latestClaimCertificateIds, true);

        $progressBar->advance();//get prelim project revisions

        $progressBar->setMessage('Processing claims data...', 'status');

        $currentClaimBills = $this->getClaimedBillsByRevisions($nonPrelimProjectCurrentRevisions);

        $previousClaimBills = $this->getClaimedBillsByRevisions($nonPrelimProjectPreviousRevisions);

        $currentClaimPrelimBills  = $this->getClaimedPreliminaryBillsByRevisions($prelimProjectCurrentRevisions);
        $previousClaimPrelimBills = $this->getClaimedPreliminaryBillsByRevisions($prelimProjectPreviousRevisions);

        foreach($currentClaimPrelimBills as $projectId => $bills)
        {
            foreach($bills as $billId => $data)
            {
                $currentClaim  = 0;
                $previousClaim = 0;
                if(array_key_exists($projectId, $previousClaimPrelimBills) && array_key_exists($billId, $previousClaimPrelimBills[$projectId]))
                {
                    $currentClaim  = $data['up_to_date_claim'] - $previousClaimPrelimBills[$projectId][$billId]['up_to_date_claim'];
                    $previousClaim = $previousClaimPrelimBills[$projectId][$billId]['up_to_date_claim'];

                    unset($previousClaimPrelimBills[$projectId][$billId]);
                }

                $currentClaimPrelimBills[$projectId][$billId]['current_claim']  = $currentClaim;
                $currentClaimPrelimBills[$projectId][$billId]['previous_claim'] = $previousClaim;
            }
        }

        $progressBar->advance();//process bills

        unset($previousClaimPrelimBills);

        $progressBar->setMessage('Querying variation orders...', 'status');

        $variationOrders = $this->getVariationOrders($latestClaimCertificateIds);

        $progressBar->advance(); // get variation orders

        $progressBar->setMessage('Querying projects data...', 'status');

        $projects = $this->getProjects($latestClaimCertificateIds);

        $progressBar->advance();//get list of projects

        $progressBar->setMessage('Generating header file...', 'status');

        $filenameUnique = date('dmYHis');

        $spreadsheet = $this->generateHeaderExcel($projects);
        $this->outputExcel($spreadsheet, $filenameUnique."-Header");

        $progressBar->advance();//generate header file

        $progressBar->setMessage('Generating item file...', 'status');

        $spreadsheet = $this->generateItemExcel($projects, $currentClaimBills, $previousClaimBills, $currentClaimPrelimBills, $variationOrders, $approvedClaimCertLogs);
        $this->outputExcel($spreadsheet, $filenameUnique."-Item");

        $progressBar->advance();//generate item file

        $progressBar->setMessage('Archiving all files...', 'status');

        $path = storage_path('s4hana'.DIRECTORY_SEPARATOR.'contracts');

        $zipname = $path.DIRECTORY_SEPARATOR.$filenameUnique.'-contracts.'.\PCK\Helpers\Files::EXTENSION_ZIP;
        $zip = new \ZipArchive;
        $zip->open($zipname, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);

        $toBeUnlinked = [];

        foreach (new \DirectoryIterator($path) as $file)
        {
            if($file->isFile() && $file->getExtension() == \PCK\Helpers\Files::EXTENSION_EXCEL && ($file->getFilename() == $filenameUnique."-Header.".\PCK\Helpers\Files::EXTENSION_EXCEL or $file->getFilename() == $filenameUnique."-Item.".\PCK\Helpers\Files::EXTENSION_EXCEL))
            {
                $filePath = $file->getRealPath();
                $relativePath = substr($filePath, strlen($path) + 1);

                $zip->addFile($filePath, $relativePath);

                $toBeUnlinked [] = $filePath;
            }
        }

        $zip->close();
        
        foreach($toBeUnlinked as $file)
        {
            if (File::exists($file))
                File::delete($file);
        }

        $progressBar->advance();//generate item file

        $progressBar->setMessage('Done!', 'status');

        $progressBar->finish();
    }

    protected function generateHeaderExcel(Array $projects)
    {
        $spreadsheet = new Spreadsheet();
        $activeSheet = $spreadsheet->getActiveSheet();
        $activeSheet->setTitle("Contract Header");

        $headerStyle = [
            'font' => [
                'bold' => true
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER
            ]
        ];

        $headers = [
            'published_date' => "Approved Time Stamp",
            'reference' => "Contract Number",
            'title' => "Title",
            'business_unit' => "Business Unit",
            'letter_of_award_number' => "Letter of Award No.",
            'date_of_award' => "Date of Award",
            'work_category' => "Work Category",
            'selected_contractor' => "Selected Contractor",
            'commencement_date' => "Commencement Date",
            'completion_date' => "Completion Date",
            'currency' => "Currency",
            'contract_sum' => "Contract Sum",
            'retention_sum' => "Percentage of certified value retained",
            'max_retention_sum' => "Limit Of Retention Fund"
        ];

        $headerCount = 1;
        foreach($headers as $key => $val)
        {
            $cell = StringOperations::numberToAlphabet($headerCount)."1";
            $activeSheet->setCellValue($cell, $val);
            $activeSheet->getStyle($cell)->applyFromArray($headerStyle);

            $activeSheet->getColumnDimension(StringOperations::numberToAlphabet($headerCount))->setAutoSize(true);
            
            $headerCount++;
        }

        $row = 2;

        foreach($projects as $projectId => $project)
        {
            $headerCount = 1;
            foreach($headers as $key => $val)
            {
                $cell = StringOperations::numberToAlphabet($headerCount).$row;
                $activeSheet->setCellValue($cell, $project[$key]);
                
                if($key == 'contract_sum' || $key == 'retention_sum' || $key == 'max_retention_sum')
                {
                    $activeSheet->getStyle($cell)->getNumberFormat()->setFormatCode("#,##0.00");
                }

                if($key == 'title' || $key == 'selected_contractor' || $key == 'business_unit')
                {
                    $activeSheet->getStyle($cell)->getAlignment()->setWrapText(true);
                }

                $headerCount++;
            }

            $row++;
        }

        return $spreadsheet;
    }

    protected function generateItemExcel(Array $projects, Array $currentStandardBillClaims, Array $previousStandardBillClaims, Array $prelimBillClaims, Array $variationOrders, Array $approvedClaimCertLogs)
    {
        $spreadsheet = new Spreadsheet();
        $activeSheet = $spreadsheet->getActiveSheet();
        $activeSheet->setTitle("Contract Item");

        $headerStyle = [
            'font' => [
                'bold' => true
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER
            ]
        ];

        $headers = [
            'A' => "Approved Time Stamp",
            'B' => "Contract Number",
            'C' => "Type",
            'D' => "BuildSpace Unique Key",
            'E' => "Description",
            'F' => "Status",
            'G' => "VO Budget",
            'H' => "Total",
            'I' => "Nett (Omission / Addition)",
            'J' => "Previous Claim (Amount)",
            'K' => "Current Claim (Amount)",
            'L' => "Up To Date Claim (Amount)"
        ];

        foreach($headers as $col => $val)
        {
            $cell = $col."1";
            $activeSheet->setCellValue($cell, $val);
            $activeSheet->getStyle($cell)->applyFromArray($headerStyle);

            $activeSheet->getColumnDimension($col)->setAutoSize(true);
        }

        $row = 2;

        $query = \DB::connection('buildspace')->table("bs_project_structures")
        ->select(\DB::raw('bill.id, ROUND(COALESCE(SUM(pi.grand_total),0),2) AS grand_total'))
        ->join(\DB::raw('bs_project_structures AS bill'), 'bill.root_id', '=', 'bs_project_structures.id')
        ->join('bs_bill_types', 'bill.id', '=', 'bs_bill_types.project_structure_id')
        ->join('bs_bill_elements', 'bs_bill_elements.project_structure_id', '=', 'bs_bill_types.project_structure_id')
        ->join('bs_bill_items', 'bs_bill_items.element_id', '=', 'bs_bill_elements.id')
        ->leftJoin(\DB::raw('bs_post_contract_bill_item_rates AS pi'), 'pi.bill_item_id', '=', 'bs_bill_items.id');

        if($this->projectId)
        {
            $query->where('bs_project_structures.id', $this->projectId);
        }

        $overallTotalBills = $query->where('bs_project_structures.type', BsProject::TYPE_ROOT)
        ->where('bill.root_id','=', \DB::raw('bs_project_structures.id'))
        ->where('bill.type', '=', BsProject::TYPE_BILL)
        ->where('bs_bill_types.type', '<>', BillType::TYPE_PRELIMINARY)
        ->whereNull('bs_project_structures.deleted_at')
        ->whereNull('bill.deleted_at')
        ->whereNull('bs_bill_types.deleted_at')
        ->whereNull('bs_bill_elements.deleted_at')
        ->whereNull('bs_bill_items.deleted_at')
        ->whereNull('bs_bill_items.project_revision_deleted_at')
        ->groupBy("bill.id")
        ->lists("grand_total", "id");

        foreach($projects as $projectId => $project)
        {
            foreach($project['bills'] as $billId => $billTitle)
            {
                if(array_key_exists($projectId, $currentStandardBillClaims) && array_key_exists($billId, $currentStandardBillClaims[$projectId]))
                {
                    $data = $currentStandardBillClaims[$projectId][$billId];
                    if(array_key_exists($data['claim_certificate_id'], $approvedClaimCertLogs))
                    {
                        $activeSheet->setCellValue('A'.$row,  Carbon::parse($approvedClaimCertLogs[$data['claim_certificate_id']])->format('d.m.Y'));
                    }

                    $activeSheet->setCellValue('B'.$row, $project['reference']);
                    $activeSheet->setCellValue('C'.$row, "Bill");
                    $activeSheet->setCellValue('D'.$row, $billId);
                    $activeSheet->setCellValue('E'.$row, $billTitle);
                    $activeSheet->getStyle('E'.$row)->getAlignment()->setWrapText(true);

                    $activeSheet->setCellValue('F'.$row, "Approved");

                    if(array_key_exists($billId, $overallTotalBills))
                    {
                        $activeSheet->setCellValue('H'.$row, $overallTotalBills[$billId]);
                        $activeSheet->getStyle('H'.$row)->getNumberFormat()->setFormatCode("#,##0.00");

                        unset($overallTotalBills[$billId]);
                    }

                    $previousClaimAmount = 0;
                    if(array_key_exists($projectId, $previousStandardBillClaims) && array_key_exists($billId, $previousStandardBillClaims[$projectId]))
                    {
                        $previousData = $previousStandardBillClaims[$projectId][$billId];
                        $previousClaimAmount = $previousData['up_to_date'];

                        unset($previousStandardBillClaims[$projectId][$billId]);
                    }

                    $activeSheet->setCellValue('J'.$row, $previousClaimAmount);
                    $activeSheet->getStyle('J'.$row)->getNumberFormat()->setFormatCode("#,##0.00");

                    $activeSheet->setCellValue('K'.$row, $data['current']);
                    $activeSheet->getStyle('K'.$row)->getNumberFormat()->setFormatCode("#,##0.00");

                    $activeSheet->setCellValue('L'.$row, $data['up_to_date']);
                    $activeSheet->getStyle('L'.$row)->getNumberFormat()->setFormatCode("#,##0.00");

                    unset($currentStandardBillClaims[$projectId][$billId]);

                    $row++;
                }

                if(array_key_exists($projectId, $prelimBillClaims) && array_key_exists($billId, $prelimBillClaims[$projectId]))
                {
                    $data = $prelimBillClaims[$projectId][$billId];

                    if(array_key_exists($data['claim_certificate_id'], $approvedClaimCertLogs))
                    {
                        $activeSheet->setCellValue('A'.$row,  Carbon::parse($approvedClaimCertLogs[$data['claim_certificate_id']])->format('d.m.Y'));
                    }

                    $activeSheet->setCellValue('B'.$row, $project['reference']);
                    $activeSheet->setCellValue('C'.$row, "Bill");
                    $activeSheet->setCellValue('D'.$row, $billId);
                    $activeSheet->setCellValue('E'.$row, $billTitle);
                    $activeSheet->getStyle('E'.$row)->getAlignment()->setWrapText(true);

                    $activeSheet->setCellValue('F'.$row, "Approved");

                    $activeSheet->setCellValue('H'.$row, $data['overall_total']);
                    $activeSheet->getStyle('H'.$row)->getNumberFormat()->setFormatCode("#,##0.00");

                    $activeSheet->setCellValue('J'.$row, $data['previous_claim']);
                    $activeSheet->getStyle('J'.$row)->getNumberFormat()->setFormatCode("#,##0.00");

                    $activeSheet->setCellValue('K'.$row, $data['current_claim']);
                    $activeSheet->getStyle('K'.$row)->getNumberFormat()->setFormatCode("#,##0.00");

                    $activeSheet->setCellValue('L'.$row, $data['up_to_date_claim']);
                    $activeSheet->getStyle('L'.$row)->getNumberFormat()->setFormatCode("#,##0.00");

                    unset($prelimBillClaims[$projectId][$billId]);

                    $row++;
                }
            }

            if(array_key_exists($projectId, $variationOrders))
            {
                foreach($variationOrders[$projectId] as $voId => $variationOrder)
                {
                    if(array_key_exists($data['claim_certificate_id'], $approvedClaimCertLogs))
                    {
                        $activeSheet->setCellValue('A'.$row,  Carbon::parse($approvedClaimCertLogs[$data['claim_certificate_id']])->format('d.m.Y'));
                    }

                    $activeSheet->setCellValue('B'.$row, $project['reference']);
                    $activeSheet->setCellValue('C'.$row, "VO");
                    $activeSheet->setCellValue('D'.$row, $voId);
                    $activeSheet->setCellValue('E'.$row, $variationOrder['title']);
                    $activeSheet->getStyle('E'.$row)->getAlignment()->setWrapText(true);

                    $activeSheet->setCellValue('F'.$row, "Approved");

                    $activeSheet->setCellValue('G'.$row, $variationOrder['reference_amount']);
                    $activeSheet->getStyle('G'.$row)->getNumberFormat()->setFormatCode("#,##0.00");

                    $activeSheet->setCellValue('H'.$row, $variationOrder['nett_omission_addition']);
                    $activeSheet->getStyle('H'.$row)->getNumberFormat()->setFormatCode("#,##0.00");

                    $activeSheet->setCellValue('I'.$row, $variationOrder['nett_omission_addition']);
                    $activeSheet->getStyle('I'.$row)->getNumberFormat()->setFormatCode("#,##0.00");

                    $activeSheet->setCellValue('J'.$row, $variationOrder['previous_amount']);
                    $activeSheet->getStyle('J'.$row)->getNumberFormat()->setFormatCode("#,##0.00");

                    $activeSheet->setCellValue('K'.$row, $variationOrder['current_amount']);
                    $activeSheet->getStyle('K'.$row)->getNumberFormat()->setFormatCode("#,##0.00");

                    $activeSheet->setCellValue('L'.$row, $variationOrder['up_to_date_amount']);
                    $activeSheet->getStyle('L'.$row)->getNumberFormat()->setFormatCode("#,##0.00");

                    unset($variationOrders[$projectId][$voId]);

                    $row++;
                }
            }
        }

        return $spreadsheet;
    }

    protected function getProjects(Array $claimCertificateIds)
    {
        if(empty($claimCertificateIds))
        {
            return [];
        }

        $query = \DB::connection('buildspace')->table("bs_project_structures")
        ->select(\DB::raw('bs_project_structures.id AS project_id,
        bs_project_main_information.eproject_origin_id, bill.id AS bill_id,
        bs_project_structures.title AS project_title, bill.title AS bill_title,
        bs_new_post_contract_form_information.awarded_date, bs_new_post_contract_form_information.contract_period_from,
        bs_new_post_contract_form_information.contract_period_to,
        bs_post_contracts.published_at, bs_new_post_contract_form_information.retention,
        bs_new_post_contract_form_information.max_retention_sum, bs_new_post_contract_form_information.reference AS la_number'))
        ->join('bs_project_main_information', 'bs_project_structures.id', '=', 'bs_project_main_information.project_structure_id')
        ->join('bs_new_post_contract_form_information', 'bs_new_post_contract_form_information.project_structure_id', '=', 'bs_project_structures.id')
        ->join('bs_post_contracts', 'bs_project_main_information.project_structure_id', '=', 'bs_post_contracts.project_structure_id')
        ->join('bs_post_contract_claim_revisions', 'bs_post_contracts.id', '=', 'bs_post_contract_claim_revisions.post_contract_id')
        ->join('bs_claim_certificates', 'bs_post_contract_claim_revisions.id', '=', 'bs_claim_certificates.post_contract_claim_revision_id')
        ->join(\DB::raw('bs_project_structures AS bill'), 'bill.root_id', '=', 'bs_project_structures.id')
        ->join('bs_bill_types', 'bill.id', '=', 'bs_bill_types.project_structure_id')
        ->whereIn('bs_claim_certificates.id', $claimCertificateIds);

        if($this->projectId)
        {
            $query->where('bs_project_structures.id', $this->projectId);
        }

        $records = $query->where('bs_project_structures.type', BsProject::TYPE_ROOT)
        ->where('bill.root_id','=', \DB::raw('bs_project_structures.id'))
        ->where('bill.type', '=', BsProject::TYPE_BILL)
        ->where('bs_claim_certificates.status', '=', ClaimCertificate::STATUS_TYPE_APPROVED)
        ->where('bs_post_contract_claim_revisions.locked_status', true)
        ->whereNotNull('bs_project_main_information.eproject_origin_id')
        ->whereNull('bs_project_structures.deleted_at')
        ->whereNull('bs_post_contract_claim_revisions.deleted_at')
        ->whereNull('bill.deleted_at')
        ->whereNull('bs_bill_types.deleted_at')
        ->orderBy(\DB::raw('bs_post_contracts.published_at DESC, bs_project_structures.id, bill.priority, bill.lft, bill.level'))
        ->groupBy(\DB::raw('bs_project_structures.id, bs_post_contracts.id, bs_project_main_information.id, bs_new_post_contract_form_information.id, bill.id'))
        ->get();

        $projects = [];
        $eprojectOriginIds = [];

        foreach($records as $record)
        {
            if(!array_key_exists($record->project_id, $projects))
            {
                $eprojectOriginIds[] = $record->eproject_origin_id;

                $projects[$record->project_id] = [
                    'title'                  => trim($record->project_title),
                    'reference'              => null,
                    'date_of_award'          => Carbon::parse($record->awarded_date)->format('d.m.Y'),
                    'commencement_date'      => Carbon::parse($record->contract_period_from)->format('d.m.Y'),
                    'completion_date'        => Carbon::parse($record->contract_period_to)->format('d.m.Y'),
                    'published_date'         => Carbon::parse($record->published_at)->format('d.m.Y'),
                    'max_retention_sum'      => $record->max_retention_sum,
                    'retention_sum'          => $record->retention,
                    'letter_of_award_number' => $record->la_number,
                    'business_unit'          => null,
                    'work_category'          => null,
                    'selected_contractor'    => null,
                    'currency'               => null,
                    'contract_sum'           => 0,
                    'eproject_origin_id'     => $record->eproject_origin_id,
                    'bills'                  => []
                ];
            }

            $projects[$record->project_id]['bills'][$record->bill_id] = trim($record->bill_title);
        }

        $eprojects = [];
        $contractSumRecords = [];

        if(!empty($eprojectOriginIds))
        {
            $eprojects = Project::join("companies", "projects.business_unit_id", "=", "companies.id")
                ->join('work_categories', 'projects.work_category_id', '=', 'work_categories.id')
                ->leftJoin('countries', 'projects.country_id', '=', 'countries.id')
                ->join(\DB::raw("(SELECT max(id) AS id, project_id
                FROM tenders
                GROUP BY project_id) tx"), 'tx.project_id', '=', 'projects.id')
                ->join('tenders', 'tenders.id', '=', 'tx.id')
                ->join('company_tender', 'company_tender.tender_id', '=', 'tenders.id')
                ->join(\DB::raw('companies AS awarded_contractor'), 'awarded_contractor.id', '=', 'company_tender.company_id')
                ->select(\DB::raw('projects.id, projects.reference, companies.name AS business_unit,
                work_categories.name AS work_category, awarded_contractor.name AS selected_contractor,
                projects.modified_currency_code AS mod_currency_code, countries.currency_code'))
                ->whereIn('projects.id', $eprojectOriginIds)
                ->where('company_tender.selected_contractor', true)
                ->get()
                ->toArray();
            
            $contractSumRecords = \DB::connection('buildspace')
                ->table('bs_post_contract_bill_item_rates')
                ->join('bs_post_contracts', 'bs_post_contracts.id', '=', 'bs_post_contract_bill_item_rates.post_contract_id')
                ->join('bs_project_structures', 'bs_post_contracts.project_structure_id', '=', 'bs_project_structures.id')
                ->join('bs_project_main_information', 'bs_project_main_information.project_structure_id', '=', 'bs_project_structures.id')
                ->select(\DB::raw("bs_project_structures.id, SUM(bs_post_contract_bill_item_rates.grand_total) AS contract_sum"))
                ->whereIn('bs_project_main_information.eproject_origin_id', $eprojectOriginIds)
                ->where('bs_post_contract_bill_item_rates.grand_total', '<>', 0)
                ->groupBy('bs_project_structures.id')
                ->lists('contract_sum', 'id');
        }

        foreach($projects as $projectId => $data)
        {
            if(array_key_exists($projectId, $contractSumRecords))
            {
                $projects[$projectId]['contract_sum'] = $contractSumRecords[$projectId];

                unset($contractSumRecords[$projectId]);
            }

            foreach($eprojects as $idx => $eproject)
            {
                if($eproject['id'] == $data['eproject_origin_id'])
                {
                    $currencyCode = ($eproject['mod_currency_code']) ? $eproject['mod_currency_code'] : $eproject['currency_code'];
                    $projects[$projectId]['reference']           = trim($eproject['reference']);
                    $projects[$projectId]['business_unit']       = trim($eproject['business_unit']);
                    $projects[$projectId]['work_category']       = trim($eproject['work_category']);
                    $projects[$projectId]['selected_contractor'] = trim($eproject['selected_contractor']);
                    $projects[$projectId]['currency']            = trim($currencyCode);

                    unset($eprojects[$idx]);
                }
            }
            
        }

        unset($eprojects);

        return $projects;
    }

    protected function getClaimedBillsByRevisions(Array $projectRevisions)
    {
        if(empty($projectRevisions))
        {
            return [];
        }
        
        $query = \DB::connection('buildspace')->table("bs_project_structures")
        ->select(\DB::raw('bs_project_structures.id AS id, bill.id AS bill_id, bs_claim_certificates.id AS claim_certificate_id,
        ROUND(COALESCE(SUM(bs_post_contract_standard_claim.current_amount), 0), 2) AS current_amount,
        ROUND(COALESCE(SUM(bs_post_contract_standard_claim.up_to_date_amount), 0), 2) AS up_to_date_amount'))
        ->join('bs_post_contracts', 'bs_project_structures.id', '=', 'bs_post_contracts.project_structure_id')
        ->join('bs_post_contract_claim_revisions', 'bs_post_contracts.id', '=', 'bs_post_contract_claim_revisions.post_contract_id')
        ->join('bs_post_contract_bill_item_rates', 'bs_post_contract_claim_revisions.post_contract_id', '=', 'bs_post_contract_bill_item_rates.post_contract_id')
        ->join('bs_claim_certificates', 'bs_post_contract_claim_revisions.id', '=', 'bs_claim_certificates.post_contract_claim_revision_id')
        ->join('bs_bill_items', 'bs_bill_items.id', '=', 'bs_post_contract_bill_item_rates.bill_item_id')
        ->join('bs_bill_elements', 'bs_bill_items.element_id', '=', 'bs_bill_elements.id')
        ->join(\DB::raw('bs_project_structures AS bill'), 'bill.id', '=', 'bs_bill_elements.project_structure_id')
        ->join('bs_bill_types', 'bill.id', '=', 'bs_bill_types.project_structure_id')
        ->join('bs_post_contract_standard_claim', function($join){
            $join->on('bs_post_contract_standard_claim.revision_id', '=', 'bs_post_contract_claim_revisions.id');
            $join->on('bs_post_contract_standard_claim.bill_item_id','=', 'bs_bill_items.id');
        });

        if($this->projectId)
        {
            $query->where('bs_project_structures.id', $this->projectId);
        }else
        {
            $query->whereIn('bs_project_structures.id', array_keys($projectRevisions));
        }

        $records = $query->where('bs_project_structures.type', BsProject::TYPE_ROOT)
        ->whereIn('bs_post_contract_claim_revisions.id', array_values($projectRevisions))
        ->where('bill.root_id','=', \DB::raw('bs_project_structures.id'))
        ->where('bs_post_contract_standard_claim.up_to_date_amount', '<>', 0)
        ->where('bill.type', '=', BsProject::TYPE_BILL)
        ->where('bs_bill_types.type', '<>', BillType::TYPE_PRELIMINARY)
        ->where('bs_claim_certificates.status', '=', ClaimCertificate::STATUS_TYPE_APPROVED)
        ->where('bs_post_contract_claim_revisions.locked_status', true)
        ->whereNull('bs_post_contract_claim_revisions.deleted_at')
        ->whereNull('bill.deleted_at')
        ->whereNull('bs_bill_types.deleted_at')
        ->whereNull('bs_bill_elements.deleted_at')
        ->whereNull('bs_bill_items.deleted_at')
        ->whereNull('bs_bill_items.project_revision_deleted_at')
        ->groupBy(\DB::raw('bs_project_structures.id, bill.id, bs_claim_certificates.id'))
        ->get();

        $claims = [];

        foreach($records as $idx => $record)
        {
            if(!array_key_exists($record->id, $claims))
            {
                $claims[$record->id] = [];
            }

            $claims[$record->id][$record->bill_id] = [
                'current'              => $record->current_amount,
                'up_to_date'           => $record->up_to_date_amount,
                'claim_certificate_id' => $record->claim_certificate_id
            ];

            unset($records[$idx]);
        }

        unset($records);

        return $claims;
    }

    protected function getClaimedPreliminaryBillsByRevisions(Array $projectRevisions)
    {
        if(empty($projectRevisions))
        {
            return [];
        }

        $billItems            = [];
        $includeFinalClaims   = [];
        $includeInitialClaims = [];

        foreach($projectRevisions as $projectId => $revisionId)
        {
            $postContract = \DB::connection('buildspace')->table("bs_post_contracts")
                ->select("id", "selected_type_rate")
                ->where("project_structure_id", $projectId)
                ->first();
            
            $queryBillItems = \DB::connection('buildspace')->table("bs_post_contract_bill_item_rates")
                ->join('bs_bill_items', 'bs_bill_items.id', '=', 'bs_post_contract_bill_item_rates.bill_item_id')
                ->leftJoin('bs_prelim_claims', function($join){
                    $join->on('bs_prelim_claims.post_contract_bill_item_rate_id', '=', 'bs_post_contract_bill_item_rates.id');
                    $join->on('bs_prelim_claims.deleted_at', \DB::raw('IS'), \DB::raw('NULL'));
                });
            
            switch((int)$postContract->selected_type_rate)
            {
                case PostContract::RATE_TYPE_CONTRACTOR:
                    $tenderCompany = \DB::connection('buildspace')->table("bs_tender_settings")
                        ->join('bs_tender_companies', function($join){
                            $join->on('bs_tender_companies.company_id', '=', 'bs_tender_settings.awarded_company_id');
                            $join->on('bs_tender_companies.project_structure_id','=', 'bs_tender_settings.project_structure_id');
                        })
                        ->select("bs_tender_companies.id", "bs_tender_companies.company_id")
                        ->where("bs_tender_settings.project_structure_id", $projectId)
                        ->whereNull('bs_tender_settings.deleted_at')
                        ->first();

                    $queryBillItems->leftJoin('bs_tender_bill_item_not_listed', function($join) use($tenderCompany){
                        $join->on('bs_tender_bill_item_not_listed.bill_item_id', '=', 'bs_bill_items.id');
                        $join->on('bs_tender_bill_item_not_listed.tender_company_id', '=', \DB::raw($tenderCompany->id));
                    })
                    ->leftJoin('bs_tender_bill_item_not_listed_quantities', 'bs_tender_bill_item_not_listed_quantities.tender_bill_item_not_listed_id', '=', 'bs_tender_bill_item_not_listed.id');

                    $queryBillItems->select(\DB::raw('bs_bill_items.id, bs_post_contract_bill_item_rates.id AS post_contract_bill_item_rate_id,
                        COALESCE(bs_post_contract_bill_item_rates.rate, 0) as rate,
                        (CASE bs_bill_items.type WHEN '.BillItem::TYPE_ITEM_NOT_LISTED.'
                            THEN bs_tender_bill_item_not_listed_quantities.final_value
                            ELSE bs_bill_items.grand_total_quantity
                            END
                        ) AS grand_total_quantity'));
                    break;
                case PostContract::RATE_TYPE_RATIONALIZED:
                    $queryBillItems->leftJoin('bs_tender_bill_item_not_listed_rationalized', 'bs_tender_bill_item_not_listed_rationalized.bill_item_id', '=', 'bs_bill_items.id')
                    ->leftJoin('bs_tender_bill_item_not_listed_rationalized_quantities', 'bs_tender_bill_item_not_listed_rationalized_quantities.tender_bill_not_listed_item_rationalized_id', '=', 'bs_tender_bill_item_not_listed_rationalized.id');

                    $queryBillItems->select(\DB::raw('bs_bill_items.id, bs_post_contract_bill_item_rates.id AS post_contract_bill_item_rate_id,
                        COALESCE(bs_post_contract_bill_item_rates.rate, 0) as rate,
                        (CASE bs_bill_items.type WHEN '.BillItem::TYPE_ITEM_NOT_LISTED.'
                            THEN bs_tender_bill_item_not_listed_rationalized_quantities.final_value
                            ELSE bs_bill_items.grand_total_quantity
                            END
                        ) AS grand_total_quantity'));
                    break;
                default:
                    $queryBillItems->select(\DB::raw('bs_bill_items.id, bs_post_contract_bill_item_rates.id AS post_contract_bill_item_rate_id,
                        COALESCE(bs_post_contract_bill_item_rates.rate, 0) as rate,
                        bs_bill_items.grand_total_quantity as grand_total_quantity'));
                    break;
            }

            $billItemRecords = $queryBillItems->join('bs_bill_elements', 'bs_bill_items.element_id', '=', 'bs_bill_elements.id')
                ->join(\DB::raw('bs_project_structures AS bill'), 'bill.id', '=', 'bs_bill_elements.project_structure_id')
                ->join('bs_bill_types', 'bill.id', '=', 'bs_bill_types.project_structure_id')
                ->where('bs_post_contract_bill_item_rates.post_contract_id', $postContract->id)
                ->where('bs_post_contract_bill_item_rates.rate', '<>', 0)
                ->where('bill.type', '=', BsProject::TYPE_BILL)
                ->where('bs_bill_types.type', '=', BillType::TYPE_PRELIMINARY)
                ->whereNull('bill.deleted_at')
                ->whereNull('bs_bill_types.deleted_at')
                ->whereNull('bs_bill_elements.deleted_at')
                ->whereNull('bs_bill_items.deleted_at')
                ->whereNull('bs_bill_items.project_revision_deleted_at')
                ->orderBy(\DB::raw('bs_bill_elements.project_structure_id, bs_bill_elements.priority, bs_bill_items.priority, bs_bill_items.lft, bs_bill_items.level'))
                ->get();
            
            foreach($billItemRecords as $billItemRecord)
            {
                $total = ($billItemRecord->rate * $billItemRecord->grand_total_quantity);

                if($total != 0)
                {
                    $billItems[$billItemRecord->id] = [
                        'total'             => $total,
                        'initial_amount'    => 0,
                        'final_amount'      => 0,
                        'time_based_amount' => 0,
                        'work_based_amount' => 0
                    ];
                }
            }

            unset($billItemRecords);

            // get include initial
            $includeInitialRecords = \DB::connection('buildspace')->table("bs_prelim_include_initials")
                ->join('bs_post_contract_bill_item_rates', 'bs_post_contract_bill_item_rates.id', '=', 'bs_prelim_include_initials.post_contract_bill_item_rate_id')
                ->select("bs_post_contract_bill_item_rates.bill_item_id", "bs_prelim_include_initials.include_at_revision_id")
                ->where('bs_post_contract_bill_item_rates.post_contract_id', $postContract->id)
                ->where('bs_prelim_include_initials.include_at_revision_id', '<=', $revisionId)
                ->get();
            
            foreach($includeInitialRecords as $includeInitialRecord)
            {
                $includeInitialClaims[$includeInitialRecord->bill_item_id] = $includeInitialRecord->include_at_revision_id;
            }

            unset($includeInitialRecords);

            // get initial default claim
            $initialClaims = \DB::connection('buildspace')->table("bs_prelim_initial_claims")
            ->join('bs_post_contract_bill_item_rates', 'bs_post_contract_bill_item_rates.id', '=', 'bs_prelim_initial_claims.post_contract_bill_item_rate_id')
            ->select(\DB::raw("bs_post_contract_bill_item_rates.bill_item_id, COALESCE(bs_prelim_initial_claims.amount, 0) AS amount"))
            ->where('bs_post_contract_bill_item_rates.post_contract_id', $postContract->id)
            ->where('bs_prelim_initial_claims.revision_id', '<=', $revisionId)
            ->where('bs_prelim_initial_claims.amount', '<>', 0)
            ->whereNull('bs_prelim_initial_claims.deleted_at')
            ->get();
        
            foreach ($initialClaims as $initialClaim)
            {
                if(array_key_exists($initialClaim->bill_item_id, $billItems))
                {
                    $billItems[$initialClaim->bill_item_id]['initial_amount'] = $initialClaim->amount;
                }
            }
            
            // get include final
            $includeFinalRecords = \DB::connection('buildspace')->table("bs_prelim_include_finals")
                ->join('bs_post_contract_bill_item_rates', 'bs_post_contract_bill_item_rates.id', '=', 'bs_prelim_include_finals.post_contract_bill_item_rate_id')
                ->select(\DB::raw("bs_post_contract_bill_item_rates.bill_item_id, bs_prelim_include_finals.include_at_revision_id"))
                ->where('bs_post_contract_bill_item_rates.post_contract_id', $postContract->id)
                ->where('bs_prelim_include_finals.include_at_revision_id', '<=', $revisionId)
                ->get();
            
            foreach($includeFinalRecords as $includeFinalRecord)
            {
                $includeFinalClaims[$includeFinalRecord->bill_item_id] = $includeFinalRecord->include_at_revision_id;
            }

            unset($includeFinalRecords);

            // get final default claim
            $finalClaims = \DB::connection('buildspace')->table("bs_prelim_final_claims")
            ->join('bs_post_contract_bill_item_rates', 'bs_post_contract_bill_item_rates.id', '=', 'bs_prelim_final_claims.post_contract_bill_item_rate_id')
            ->select(\DB::raw("bs_post_contract_bill_item_rates.bill_item_id, COALESCE(bs_prelim_final_claims.amount, 0) AS amount"))
            ->where('bs_post_contract_bill_item_rates.post_contract_id', $postContract->id)
            ->where('bs_prelim_final_claims.revision_id', '<=', $revisionId)
            ->where('bs_prelim_final_claims.amount', '<>', 0)
            ->whereNull('bs_prelim_final_claims.deleted_at')
            ->get();
        
            foreach ($finalClaims as $finalClaim)
            {
                if(array_key_exists($finalClaim->bill_item_id, $billItems))
                {
                    $billItems[$finalClaim->bill_item_id]['final_amount'] = $finalClaim->amount;
                }
            }
            
            // get time based claim
            $timeBasedClaims = \DB::connection('buildspace')->table("bs_prelim_time_based_claims")
                ->join('bs_post_contract_bill_item_rates', 'bs_post_contract_bill_item_rates.id', '=', 'bs_prelim_time_based_claims.post_contract_bill_item_rate_id')
                ->select(\DB::raw("bs_post_contract_bill_item_rates.bill_item_id,
                    SUM(COALESCE(bs_prelim_time_based_claims.total, 0)) AS total"))
                ->where('bs_post_contract_bill_item_rates.post_contract_id', $postContract->id)
                ->where('bs_prelim_time_based_claims.revision_id', '=', $revisionId)
                ->where('bs_prelim_time_based_claims.total', '<>', 0)
                ->whereNull('bs_prelim_time_based_claims.deleted_at')
                ->groupBy("bs_post_contract_bill_item_rates.bill_item_id")
                ->get();
            
            foreach ( $timeBasedClaims as $timeBasedClaim )
            {
                if(array_key_exists($timeBasedClaim->bill_item_id, $billItems))
                {
                    $recurringAmt                 = ($billItems[$timeBasedClaim->bill_item_id]['total'] > 0) ? $billItems[$timeBasedClaim->bill_item_id]['total'] - $billItems[$timeBasedClaim->bill_item_id]['initial_amount'] - $billItems[$timeBasedClaim->bill_item_id]['final_amount'] : 0;
                    $billItem['recurring-amount'] = round($recurringAmt, 2);

                    $calculatedCosting = round($timeBasedClaim->total * 100, 2);
                    $billItems[$timeBasedClaim->bill_item_id]['time_based_amount'] = round($recurringAmt * ($calculatedCosting / 100), 2);
                }
            }

            // get work based claim
            $workBasedClaims = \DB::connection('buildspace')->table("bs_prelim_work_based_claims")
                ->join('bs_post_contract_bill_item_rates', 'bs_post_contract_bill_item_rates.id', '=', 'bs_prelim_work_based_claims.post_contract_bill_item_rate_id')
                ->select(\DB::raw("bs_post_contract_bill_item_rates.bill_item_id,
                    SUM(COALESCE(bs_prelim_work_based_claims.total, 0)) AS total"))
                ->where('bs_post_contract_bill_item_rates.post_contract_id', $postContract->id)
                ->where('bs_prelim_work_based_claims.revision_id', '=', $revisionId)
                ->where('bs_prelim_work_based_claims.total', '<>', 0)
                ->whereNull('bs_prelim_work_based_claims.deleted_at')
                ->groupBy("bs_post_contract_bill_item_rates.bill_item_id")
                ->get();
            
            foreach ( $workBasedClaims as $workBasedClaim )
            {
                if(array_key_exists($workBasedClaim->bill_item_id, $billItems))
                {
                    $recurringAmt                 = ($billItems[$workBasedClaim->bill_item_id]['total'] > 0) ? $billItems[$workBasedClaim->bill_item_id]['total'] - $billItems[$workBasedClaim->bill_item_id]['initial_amount'] - $billItems[$workBasedClaim->bill_item_id]['final_amount'] : 0;
                    $billItem['recurring-amount'] = round($recurringAmt, 2);

                    $calculatedCosting = round($workBasedClaim->total * 100, 2);
                    $billItems[$workBasedClaim->bill_item_id]['work_based_amount'] = round($recurringAmt * ($calculatedCosting / 100), 2);
                }
            }
        }
        
        $query = \DB::connection('buildspace')->table("bs_project_structures")
        ->select(\DB::raw('bs_project_structures.id AS project_id, bill.id AS bill_id, bs_claim_certificates.id AS claim_certificate_id, bs_bill_items.id AS bill_item_id'))
        ->join('bs_post_contracts', 'bs_project_structures.id', '=', 'bs_post_contracts.project_structure_id')
        ->join('bs_post_contract_claim_revisions', 'bs_post_contracts.id', '=', 'bs_post_contract_claim_revisions.post_contract_id')
        ->join('bs_claim_certificates', 'bs_post_contract_claim_revisions.id', '=', 'bs_claim_certificates.post_contract_claim_revision_id')
        ->join(\DB::raw('bs_project_structures AS bill'), 'bill.root_id', '=', 'bs_project_structures.id')
        ->join('bs_bill_types', 'bill.id', '=', 'bs_bill_types.project_structure_id')
        ->join('bs_bill_elements', 'bs_bill_types.project_structure_id', '=', 'bs_bill_elements.project_structure_id')
        ->join('bs_bill_items', 'bs_bill_items.element_id', '=', 'bs_bill_elements.id');

        if($this->projectId)
        {
            $query->where('bs_project_structures.id', $this->projectId);
        }
        else
        {
            $query->whereIn('bs_project_structures.id', array_keys($projectRevisions));
        }

        $records = $query->where('bs_project_structures.type', BsProject::TYPE_ROOT)
        ->whereIn('bs_post_contract_claim_revisions.id', array_values($projectRevisions))
        ->where('bill.root_id','=', \DB::raw('bs_project_structures.id'))
        ->where('bill.type', '=', BsProject::TYPE_BILL)
        ->where('bs_bill_types.type', '=', BillType::TYPE_PRELIMINARY)
        ->where('bs_claim_certificates.status', '=', ClaimCertificate::STATUS_TYPE_APPROVED)
        ->where('bs_post_contract_claim_revisions.locked_status', true)
        ->whereNull('bs_post_contract_claim_revisions.deleted_at')
        ->whereNull('bill.deleted_at')
        ->whereNull('bs_bill_types.deleted_at')
        ->whereNull('bs_bill_elements.deleted_at')
        ->whereNull('bs_bill_items.deleted_at')
        ->whereNull('bs_bill_items.project_revision_deleted_at')
        ->get();

        $projectPrelimBillItems = [];

        $billIds = [];

        foreach($records as $idx => $record)
        {
            if(!array_key_exists($record->project_id, $projectPrelimBillItems))
            {
                $projectPrelimBillItems[$record->project_id] = [];
            }

            if(!array_key_exists($record->bill_id, $projectPrelimBillItems[$record->project_id]))
            {
                $projectPrelimBillItems[$record->project_id][$record->bill_id] = [
                    'items'            => [],
                    'overall_total'    => 0,
                    'up_to_date_claim' => 0,
                    'claim_certificate_id' => $record->claim_certificate_id
                ];

                $billIds[] = $record->bill_id;
            }

            $projectPrelimBillItems[$record->project_id][$record->bill_id]['items'][] = $record->bill_item_id;

            unset($records[$idx]);
        }

        unset($records);

        if(!empty($billIds))
        {
            $query = \DB::connection('buildspace')->table("bs_project_structures")
            ->select(\DB::raw('bs_bill_items.id, bs_project_structures.id AS project_id, bill.id AS bill_id'))
            ->join(\DB::raw('bs_project_structures AS bill'), 'bill.root_id', '=', 'bs_project_structures.id')
            ->join('bs_bill_types', 'bill.id', '=', 'bs_bill_types.project_structure_id')
            ->join('bs_bill_elements', 'bs_bill_types.project_structure_id', '=', 'bs_bill_elements.project_structure_id')
            ->join('bs_bill_items', 'bs_bill_items.element_id', '=', 'bs_bill_elements.id');

            if($this->projectId)
            {
                $query->where('bs_project_structures.id', $this->projectId);
            }

            $records = $query->where('bs_project_structures.type', BsProject::TYPE_ROOT)
            ->whereIn('bill.id', $billIds)
            ->where('bill.root_id','=', \DB::raw('bs_project_structures.id'))
            ->where('bill.type', '=', BsProject::TYPE_BILL)
            ->where('bs_bill_types.type', '=', BillType::TYPE_PRELIMINARY)
            ->whereNull('bill.deleted_at')
            ->whereNull('bs_bill_types.deleted_at')
            ->whereNull('bs_bill_elements.deleted_at')
            ->whereNull('bs_bill_items.deleted_at')
            ->whereNull('bs_bill_items.project_revision_deleted_at')
            ->get();

            foreach($records as $idx => $record)
            {
                if(array_key_exists($record->id, $billItems) && array_key_exists($record->project_id, $projectPrelimBillItems) && array_key_exists($record->bill_id, $projectPrelimBillItems[$record->project_id]))
                {
                    $projectPrelimBillItems[$record->project_id][$record->bill_id]['overall_total'] += $billItems[$record->id]['total'];

                    unset($records[$idx]);
                }
            }
        }

        foreach($projectPrelimBillItems as $projectId => $bills)
        {
            foreach($bills as $billId => $prelimBillItems)
            {
                foreach($prelimBillItems['items'] as $itemId)
                {
                    if(array_key_exists($itemId, $billItems) && ($billItems[$itemId]['time_based_amount'] + $billItems[$itemId]['work_based_amount']) != 0)
                    {
                        $upToDateAmount = $billItems[$itemId]['time_based_amount'] + $billItems[$itemId]['work_based_amount'];

                        if(array_key_exists($itemId, $includeInitialClaims))
                        {
                            $upToDateAmount += $billItems[$itemId]['initial_amount'];
                        }

                        if(array_key_exists($itemId, $includeFinalClaims))
                        {
                            $upToDateAmount += $billItems[$itemId]['final_amount'];
                        }

                        $projectPrelimBillItems[$projectId][$billId]['up_to_date_claim'] += $upToDateAmount;
                    }
                }

                unset($prelimBillItems['items'], $bills[$billId], $projectPrelimBillItems[$projectId][$billId]['items']);

                if($projectPrelimBillItems[$projectId][$billId]['up_to_date_claim'] == 0)
                {
                    unset($projectPrelimBillItems[$projectId][$billId]);
                }

                if(empty($projectPrelimBillItems[$projectId]))
                {
                    unset($projectPrelimBillItems[$projectId]);
                }
            }
        }

        unset($includeInitialClaims, $includeFinalClaims);

        return $projectPrelimBillItems;
    }

    protected function getVariationOrders(Array $claimCertificateIds)
    {
        if(empty($claimCertificateIds))
        {
            return [];
        }

        $query = \DB::connection('buildspace')->table("bs_post_contract_claim_revisions")
        ->select(\DB::raw("bs_post_contract_claim_revisions.id, COALESCE(MAX(bs_post_contract_claim_revisions.version), 0) AS version"))
        ->join('bs_claim_certificates', 'bs_claim_certificates.post_contract_claim_revision_id', '=', 'bs_post_contract_claim_revisions.id')
        ->join('bs_post_contracts', 'bs_post_contracts.id', '=', 'bs_post_contract_claim_revisions.post_contract_id');

        if($this->projectId)
        {
            $query->where('bs_post_contracts.project_structure_id', $this->projectId);
        }

        $claimRevisions = $query->whereIn('bs_claim_certificates.id', $claimCertificateIds)
        ->whereNull('bs_post_contract_claim_revisions.deleted_at')
        ->groupBy("bs_post_contract_claim_revisions.id")
        ->lists('version', 'id');

        $variationOrders = [];

        foreach($claimRevisions as $claimRevisionId => $version)
        {
            $query = \DB::connection('buildspace')->table("bs_variation_orders")
            ->select(\DB::raw("
            bs_variation_orders.id AS variation_order_id, bs_variation_orders.project_structure_id,
            bs_variation_orders.description, SUM(i.reference_amount) as reference_amount,
            cert.id AS claim_certificate_id,
            ROUND(COALESCE(SUM(
                (i.total_unit * i.addition_quantity * i.rate) - (i.total_unit * i.omission_quantity * i.rate)
            )), 2) AS nett_omission_addition,
            CASE WHEN (SUM((i.rate * i.addition_quantity) - (i.rate * i.omission_quantity)) < 0)
                THEN -1 * SUM(ABS(ci.current_amount))
                ELSE SUM(ci.current_amount)
            END AS current_amount,
            CASE WHEN (SUM((i.rate * i.addition_quantity) - (i.rate * i.omission_quantity)) < 0)
                THEN -1 * SUM(ABS(ci.up_to_date_amount))
                ELSE SUM(ci.up_to_date_amount)
            END AS up_to_date_amount,
            CASE WHEN (SUM((pvoi.rate * pvoi.addition_quantity) - (pvoi.rate * pvoi.omission_quantity)) < 0)
                THEN -1 * SUM(ABS(pci.up_to_date_amount))
                ELSE SUM(pci.up_to_date_amount)
            END AS previous_amount
            "))
            ->join(\DB::raw('bs_variation_order_items AS i'), 'bs_variation_orders.id', '=', 'i.variation_order_id')
            ->join(\DB::raw('bs_variation_order_claims AS c'), 'i.variation_order_id', '=', 'c.variation_order_id')
            ->leftJoin(\DB::raw('bs_variation_order_claim_items AS ci'), function($join){
                $join->on('ci.variation_order_claim_id', '=', 'c.id');
                $join->on('ci.variation_order_item_id', '=', 'i.id');
            })
            ->leftJoin(\DB::raw('bs_variation_order_claims AS pc'), function($join){
                $join->on('pc.variation_order_id', '=', 'c.variation_order_id');
                $join->on('pc.revision', '=', \DB::raw('c.revision - 1'));
            })
            ->leftJoin(\DB::raw('bs_variation_order_claim_items AS pci'), function($join){
                $join->on('pci.variation_order_claim_id', '=', 'pc.id');
                $join->on('pci.variation_order_item_id', '=', 'i.id');
            })
            ->leftJoin(\DB::raw('bs_variation_order_items AS pvoi'), 'pci.variation_order_item_id', '=', 'pvoi.id')
            ->join(\DB::raw('bs_variation_order_claims_claim_certificates AS xref'), 'xref.variation_order_claim_id', '=', 'c.id')
            ->join(\DB::raw('bs_claim_certificates AS cert'), 'cert.id', '=', 'xref.claim_certificate_id')
            ->join(\DB::raw('bs_post_contract_claim_revisions AS rev'), 'rev.id', '=', 'cert.post_contract_claim_revision_id');

            if($this->projectId)
            {
                $query->where('bs_variation_orders.project_structure_id', $this->projectId);
            }

            $records = $query->where('rev.version', '<=', $version)
                ->where('i.type', '<>', VariationOrderItem::TYPE_HEADER)
                ->where('cert.status', '=', ClaimCertificate::STATUS_TYPE_APPROVED)
                ->where('rev.locked_status', true)
                ->where('bs_variation_orders.is_approved', true)
                ->whereNull('bs_variation_orders.deleted_at')
                ->whereNull('i.deleted_at')
                ->whereNull('c.deleted_at')
                ->whereNull('ci.deleted_at')
                ->whereNull('pc.deleted_at')
                ->whereNull('pci.deleted_at')
                ->orderBy(\DB::raw('bs_variation_orders.project_structure_id, bs_variation_orders.priority'))
                ->groupBy(\DB::raw('cert.id, bs_variation_orders.id, bs_variation_orders.project_structure_id'))
                ->get();

            foreach($records as $record)
            {
                if(!array_key_exists($record->project_structure_id, $variationOrders))
                {
                    $variationOrders[$record->project_structure_id] = [];
                }

                $variationOrders[$record->project_structure_id][$record->variation_order_id] = [
                    'title'                  => trim($record->description),
                    'claim_certificate_id'   => $record->claim_certificate_id,
                    'reference_amount'       => ($record->reference_amount) ? $record->reference_amount : 0,
                    'previous_amount'        => ($record->previous_amount) ? $record->previous_amount : 0,
                    'current_amount'         => ($record->current_amount) ? $record->current_amount : 0,
                    'up_to_date_amount'      => ($record->up_to_date_amount) ? $record->up_to_date_amount : 0,
                    'nett_omission_addition' => ($record->nett_omission_addition) ? $record->nett_omission_addition : 0
                ];
            }
        }

        return $variationOrders;
    }

    protected function getProjectRevisions(Array $claimCertificateIds, $onlyPrelimBills=false)
    {
        if(empty($claimCertificateIds))
        {
            return [];
        }

        $query = \DB::connection('buildspace')->table("bs_project_structures")
        ->select(\DB::raw('bs_project_structures.id AS id, bs_post_contract_claim_revisions.id AS current_revision, prev_claim_revision.id AS previous_revision'))
        ->join('bs_post_contracts', 'bs_project_structures.id', '=', 'bs_post_contracts.project_structure_id')
        ->join('bs_post_contract_claim_revisions', 'bs_post_contracts.id', '=', 'bs_post_contract_claim_revisions.post_contract_id')
        ->join('bs_post_contract_bill_item_rates', 'bs_post_contract_claim_revisions.post_contract_id', '=', 'bs_post_contract_bill_item_rates.post_contract_id')
        ->join('bs_bill_items', 'bs_bill_items.id', '=', 'bs_post_contract_bill_item_rates.bill_item_id')
        ->join('bs_bill_elements', 'bs_bill_elements.id', '=', 'bs_bill_items.element_id')
        ->join(\DB::raw('bs_project_structures AS bill'), 'bs_bill_elements.project_structure_id', '=', 'bill.id')
        ->join('bs_bill_types', 'bs_bill_types.project_structure_id', '=', 'bill.id')
        ->leftJoin(\DB::raw('bs_post_contract_claim_revisions AS prev_claim_revision'), function($join){
            $join->on('prev_claim_revision.post_contract_id', '=', 'bs_post_contracts.id');
            $join->on('prev_claim_revision.version','=', \DB::raw('(bs_post_contract_claim_revisions.version - 1)'));
        })
        ->join('bs_claim_certificates', 'bs_post_contract_claim_revisions.id', '=', 'bs_claim_certificates.post_contract_claim_revision_id')
        ->where('bs_project_structures.type', BsProject::TYPE_ROOT)
        ->where('bill.type', BsProject::TYPE_BILL);

        if($this->projectId)
        {
            $query->where('bs_project_structures.id', $this->projectId);
        }

        if($onlyPrelimBills)
        {
            $query->where('bs_bill_types.type', BillType::TYPE_PRELIMINARY);
        }
        else
        {
            $query->where('bs_bill_types.type', '<>', BillType::TYPE_PRELIMINARY);
        }

        $query->whereIn('bs_claim_certificates.id', $claimCertificateIds)
        ->where('bs_claim_certificates.status', '=', ClaimCertificate::STATUS_TYPE_APPROVED)
        ->where('bs_post_contract_claim_revisions.locked_status', true)
        ->whereNull('bs_project_structures.deleted_at')
        ->whereNull('bs_post_contract_claim_revisions.deleted_at')
        ->whereNull('bill.deleted_at')
        ->whereNull('bs_bill_types.deleted_at')
        ->whereNull('bs_bill_elements.deleted_at')
        ->whereNull('bs_bill_items.deleted_at')
        ->whereNull('bs_bill_items.project_revision_deleted_at')
        ->groupBy(\DB::raw('bs_project_structures.id, bs_post_contract_claim_revisions.id, prev_claim_revision.id'));

        $records = $query->get();

        $projectCurrentRevisions  = [];
        $projectPreviousRevisions = [];

        foreach($records as $idx => $record)
        {
            $projectCurrentRevisions[$record->id]  = $record->current_revision;

            if($record->previous_revision)
            {
                $projectPreviousRevisions[$record->id] = $record->previous_revision;
            }

            unset($records[$idx]);
        }

        unset($records);

        return [$projectCurrentRevisions, $projectPreviousRevisions];
    }

    protected function outputExcel(Spreadsheet $spreadsheet, $filename)
    {
        $writer = new Xlsx($spreadsheet);

        $filename = $filename.".".\PCK\Helpers\Files::EXTENSION_EXCEL;

        $filepath = \PCK\Helpers\Files::getTmpFileUri();

        $writer->save($filepath);
        
        $path = storage_path('s4hana'.DIRECTORY_SEPARATOR.'contracts');

        if(!File::isDirectory($path))
        {
            File::makeDirectory($path, 0777, true, true);
        }

        File::move($filepath, $path.DIRECTORY_SEPARATOR.$filename);
    }
}
