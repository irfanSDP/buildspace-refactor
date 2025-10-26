<?php

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

use PCK\Projects\Project;
use PCK\ProjectDetails\PAM2006ProjectDetail;
use PCK\Helpers\StringOperations;
use PCK\Buildspace\AccountCode;

use Carbon\Carbon;

class AppIntegrationController extends \BaseController
{
    public function sapHanaIndex()
    {
        return View::make('app_integration.index');
    }

    public function list()
    {
        $request = Request::instance();

        $limit = Input::get('size', 1);
        $page = Input::get('page', 1);

        $totalContractItems = \DB::connection('buildspace')->table(\DB::raw("bs_contract_items AS i"))
        ->select(\DB::raw('i.batch_number, COUNT(i.project_id) AS total'))
        ->orderBy('i.batch_number', 'asc')
        ->groupBy(\DB::raw('i.batch_number'))
        ->lists("total", "batch_number");

        $totalClaimHeaders = \DB::connection('buildspace')->table(\DB::raw("bs_claim_headers AS h"))
        ->select(\DB::raw('h.batch_number, COUNT(h.project_id) AS total'))
        ->orderBy('h.batch_number', 'asc')
        ->groupBy(\DB::raw('h.batch_number'))
        ->lists("total", "batch_number");

        $totalClaimItems = \DB::connection('buildspace')->table(\DB::raw("bs_claim_items AS i"))
        ->select(\DB::raw('i.batch_number, COUNT(i.project_id) AS total'))
        ->orderBy('i.batch_number', 'asc')
        ->groupBy(\DB::raw('i.batch_number'))
        ->lists("total", "batch_number");

        $query = \DB::connection('buildspace')->table(\DB::raw("bs_contract_headers AS h"))
        ->select(\DB::raw('h.batch_number, COUNT(h.project_id) AS total_contract_headers, h.created_at'));

        if(Input::has('filters'))
        {
            foreach(Input::get('filters', []) as $filters)
            {
                $val = trim($filters['value']);
                switch(trim(strtolower($filters['field'])))
                {
                    case 'batch_number':
                        $batchNumber = (int)$val;
                        if($batchNumber)
                        {
                            $query->where('h.batch_number', $batchNumber);
                        }
                        break;
                }
            }
        }

        $query->orderBy('h.batch_number', 'desc')
        ->groupBy(\DB::raw('h.batch_number, h.created_at'));

        $rowCount = count($query->get());

        $records = $query->skip($limit * ($page - 1))
        ->take($limit)
        ->get();
        
        $data = [];

        foreach($records as $key => $record)
        {
            $data[] = [
                'batch_number'                 => $record->batch_number,
                'total_contract_headers'       => $record->total_contract_headers,
                'total_contract_items'         => array_key_exists($record->batch_number, $totalContractItems) ? $totalContractItems[$record->batch_number] : 0,
                'total_claim_headers'          => array_key_exists($record->batch_number, $totalClaimHeaders) ? $totalClaimHeaders[$record->batch_number] : 0,
                'total_claim_items'            => array_key_exists($record->batch_number, $totalClaimItems) ? $totalClaimItems[$record->batch_number] : 0,
                'route:contractHeaderDownload' => route('app.integration.s4hana.contract.download', ['h', $record->batch_number]),
                'route:contractItemDownload'   => route('app.integration.s4hana.contract.download', ['i', $record->batch_number]),
                'route:claimHeaderDownload'    => route('app.integration.s4hana.claim.download', ['h', $record->batch_number]),
                'route:claimItemDownload'      => route('app.integration.s4hana.claim.download', ['i', $record->batch_number]),
                'created_date'                 => date('d/m/Y', strtotime($record->created_at))
            ];
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }

    public function sapHanaContractDownload($type, $batchNumber)
    {
        $filePath = $this->generateContractFiles($type, $batchNumber);

        if($filePath){
            return \PCK\Helpers\Files::download($filePath);
        }
        
        return null;
    }

    public function sapHanaClaimDownload($type, $batchNumber)
    {
        $filePath = $this->generateClaimFiles($type, $batchNumber);

        if($filePath){
            return \PCK\Helpers\Files::download($filePath);
        }

        return null;
    }

    public function sync($batchNumber)
    {
        $path = getenv('S4HANA_FILES_PATH');

        if(!$path or !File::isDirectory($path))
        {
            return "Path does not exist.";
        }
        
        $returnCounter = 0;

        $contractHeaderFilePath = $this->generateContractFiles('h', $batchNumber);

        if($contractHeaderFilePath)
        {
            $fileInfo = new SplFileInfo($contractHeaderFilePath);
            $this->syncToEndS4HanaEndPoint($fileInfo, '/CTH');
            $returnCounter++;
        }

        $contractItemFilePath = $this->generateContractFiles('i', $batchNumber);

        if($contractItemFilePath)
        {
            $fileInfo = new SplFileInfo($contractItemFilePath);
            $this->syncToEndS4HanaEndPoint($fileInfo, '/CTI');
            $returnCounter++;
        }

        echo $returnCounter;

        $claimHeaderFilePath = $this->generateClaimFiles('h', $batchNumber);

        if($claimHeaderFilePath)
        {
            $fileInfo = new SplFileInfo($claimHeaderFilePath);
            $this->syncToEndS4HanaEndPoint($fileInfo, '/CLH');
            $returnCounter++;
        }

        $claimItemFilePath = $this->generateClaimFiles('i', $batchNumber);

        if($claimItemFilePath)
        {
            $fileInfo = new SplFileInfo($claimItemFilePath);
            $this->syncToEndS4HanaEndPoint($fileInfo, '/CLI');
            $returnCounter++;
        }

        echo $returnCounter;

        return 'End';
    }

    protected function syncToEndS4HanaEndPoint(SplFileInfo $file, $remoteDir)
    {

        $host = getenv('S4HANA_ENDPOINT_HOST', '127.0.0.1');
        $port = getenv('S4HANA_ENDPOINT_PORT', 22);
        $username = getenv('S4HANA_ENDPOINT_USERNAME', 'username');
        $password = getenv('S4HANA_ENDPOINT_PASSSWORD', 'password');
        
        $ch = curl_init('sftp://' . $host . ':' . $port . $remoteDir . '/' . $file->getBasename());

        $fh = fopen($file->getPathname(), 'r');
        
        if ($fh)
        {
            curl_setopt($ch, CURLOPT_USERPWD, $username . ':' . $password);
            curl_setopt($ch, CURLOPT_UPLOAD, true);
            curl_setopt($ch, CURLOPT_PROTOCOLS, CURLPROTO_SFTP);
            curl_setopt($ch, CURLOPT_INFILE, $fh);
            curl_setopt($ch, CURLOPT_INFILESIZE, $file->getSize());
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);//insecure mode because of ssl cert from host was not valid
            curl_setopt($ch, CURLOPT_VERBOSE, true);
        
            $verbose = fopen('php://temp', 'w+');
            curl_setopt($ch, CURLOPT_STDERR, $verbose);
        
            $response = curl_exec($ch);
            $error = curl_error($ch);
            curl_close($ch);
        
            if ($response)
            {
                return 'Uploaded '.$file->getBasename();
            }
            else
            {
                rewind($verbose);
                $verboseLog = stream_get_contents($verbose);

                throw new Exception($verboseLog);
            }
        }
    }

    protected function generateContractFiles($type, $batchNumber)
    {
        $records = [];
        $projectIds = [];

        switch($type)
        {
            case 'h':
                $query = \DB::connection('buildspace')
                ->table(\DB::raw("bs_contract_headers AS h"))
                ->join('bs_project_structures AS p', 'p.id', '=', 'h.project_id')
                ->select(\DB::raw('h.*'))
                ->where('h.batch_number', (int)$batchNumber)
                ->orderBy('p.priority', 'asc');

                $projectIds = $query->lists('h.project_id');
                $records = $query->get();

                $headers = [
                    "Approved Time Stamp",
                    "Contract Number",
                    "Title",
                    "Business Unit",
                    "Letter of Award No.",
                    "Date of Award",
                    "Work Category",
                    "Selected Contractor",
                    "Commencement Date",
                    "Completion Date",
                    "Currency",
                    "Contract Sum",
                    "Percentage of certified value retained",
                    "Limit Of Retention Fund",
                    "Amount Of Performance Bond",
                    "CPC Date",
                    "E.O.T Date",
                    "DLP Period",
                    "CMGD Date",
                    "CNC Date",
                    "LD - RM",
                    "Performance Bond Validity Date",
                    "Insurance Policy Coverage Date"
                ];

                $filenamePrefix = 'BUILDSPACE_CTH';
                $sheetTitle = 'Headers';
                $filterRange = 'A1:W1';
                break;
            case 'i':
                $query = \DB::connection('buildspace')
                ->table(\DB::raw("bs_contract_items AS i"))
                ->join('bs_project_structures AS p', 'p.id', '=', 'i.project_id')
                ->select(\DB::raw('i.*'))
                ->where('i.batch_number', (int)$batchNumber)
                ->orderBy('p.priority', 'asc');

                $projectIds = $query->lists('i.project_id');
                $records = $query->get();

                $headers = [
                    "Approved Time Stamp",
                    "Contract Number",
                    "Type",
                    "BuildSpace Unique Key",
                    "Description",
                    "Status",
                    "VO Budget",
                    "Total",
                    "Nett (Omission / Addition)",
                    "Previous Claim (Amount)",
                    "Current Claim (Amount)",
                    "Up To Date Claim (Amount)"
                ];

                $filenamePrefix = 'BUILDSPACE_CTI';
                $sheetTitle = 'Items';
                $filterRange = 'A1:L1';
                break;
            default:
                throw new Exception('Invalid file type');
        }

        if($projectIds)
        {
            $projects = \DB::connection('buildspace')
            ->table(\DB::raw("bs_project_structures AS p"))
            ->select('p.id', 'p.title')
            ->whereIn('p.id', $projectIds)
            ->orderBy('p.priority', 'asc')
            ->lists("title", "id");
        }

        $spreadsheet = new Spreadsheet();
        $spreadsheet->getProperties()->setCreator("Buildspace");

        $activeSheet = $spreadsheet->getActiveSheet();
        $activeSheet->setTitle($sheetTitle);
        $activeSheet->setAutoFilter($filterRange);

        $headerStyle = [
            'font' => [
                'bold' => true
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER
            ]
        ];

        $headerCount = 1;
        foreach($headers as $val)
        {
            $cell = StringOperations::numberToAlphabet($headerCount)."1";
            $activeSheet->setCellValue($cell, $val);
            $activeSheet->getStyle($cell)->applyFromArray($headerStyle);
            
            $activeSheet->getColumnDimension(StringOperations::numberToAlphabet($headerCount))->setAutoSize(true);
            
            $headerCount++;
        }

        if($type == 'h')
        {
            $createdDate = $this->setContractHeaderCells($records, $projects, $activeSheet);
        }
        elseif($type =='i')
        {
            $createdDate = $this->setContractItemCells($records, $projects, $activeSheet);
        }

        $path = getenv('S4HANA_FILES_PATH');

        if($path and File::isDirectory($path))
        {
            $writer = new Xlsx($spreadsheet);

            $filePath = $path.DIRECTORY_SEPARATOR.'contracts';

            $filenameIndicator = "000-".sprintf('%03d', count($records));

            $filename = $filenamePrefix.'_'.$filenameIndicator.'_'.Carbon::parse($createdDate)->format('YmdHis').".".\PCK\Helpers\Files::EXTENSION_EXCEL;

            $writer->save($filePath.DIRECTORY_SEPARATOR.$filename);

            return $filePath.DIRECTORY_SEPARATOR.$filename;
        }

        return null;
    }

    protected function generateClaimFiles($type, $batchNumber)
    {
        $records = [];
        $projectIds = [];

        switch($type)
        {
            case 'h':
                $query = \DB::connection('buildspace')
                ->table(\DB::raw("bs_claim_headers AS h"))
                ->join('bs_project_structures AS p', 'p.id', '=', 'h.project_id')
                ->join('bs_claim_certificates AS c', 'h.claim_certificate_id', '=', 'c.id')
                ->select(\DB::raw('h.*'))
                ->where('h.batch_number', (int)$batchNumber)
                ->orderBy('h.claim_number', 'asc')
                ->orderBy('p.priority', 'asc')
                ->orderBy('c.post_contract_claim_revision_id', 'asc');

                $projectIds = $query->lists('h.project_id');
                $records = $query->get();

                $headers = [
                    "Approval Date",
                    "Creation Date",
                    "Contract Number",
                    "Claim No.",
                    "Contractor Submitted Date",
                    "Site Verified Date",
                    "Certificate Received Date",
                    "Payment Due Date",
                    "Status",
                    "Currency",
                    "Contract Sum",
                    "Work Done Amount",
                    "Amount Certified",
                    "% Completion",
                    "Invoice Date",
                    "Invoice Reference No.",
                    "Accm. Retention Sum",
                    "This Retention Sum",
                    "Release Retention (%)",
                    "Accm. Release Retention (Amt)",
                    "This Release Retention (Amt)"
                ];

                $filenamePrefix = 'BUILDSPACE_CLH';
                $sheetTitle = 'Headers';
                $filterRange = 'A1:U1';
                break;
            case 'i':
                $query = \DB::connection('buildspace')
                ->table(\DB::raw("bs_claim_items AS i"))
                ->join('bs_project_structures AS p', 'p.id', '=', 'i.project_id')
                ->join('bs_claim_certificates AS c', 'i.claim_certificate_id', '=', 'c.id')
                ->select(\DB::raw('i.*'))
                ->where('i.batch_number', (int)$batchNumber)
                ->orderBy('i.contract_number', 'asc')
                ->orderBy('i.claim_number', 'asc')
                ->orderBy('p.priority', 'asc')
                ->orderBy('c.post_contract_claim_revision_id', 'asc');

                $projectIds = $query->lists('i.project_id');
                $records = $query->get();

                $headers = [
                    "Approved Claim Date",
                    "Contract Number",
                    "Claim No.",
                    "Type",
                    "BuildSpace Unique Key",
                    "Description",
                    "Status",
                    "ACCM Total (Amount)",
                    "This Claim (Amount)"
                ];

                $filenamePrefix = 'BUILDSPACE_CLI';
                $sheetTitle = 'Items';
                $filterRange = 'A1:I1';
                break;
            default:
                throw new Exception('Invalid file type');
        }

        if($projectIds)
        {
            $projects = \DB::connection('buildspace')
            ->table(\DB::raw("bs_project_structures AS p"))
            ->select('p.id', 'p.title')
            ->whereIn('p.id', $projectIds)
            ->orderBy('p.priority', 'asc')
            ->lists("title", "id");
        }

        $spreadsheet = new Spreadsheet();
        $spreadsheet->getProperties()->setCreator("Buildspace");

        $activeSheet = $spreadsheet->getActiveSheet();
        $activeSheet->setTitle($sheetTitle);
        $activeSheet->setAutoFilter($filterRange);

        $headerStyle = [
            'font' => [
                'bold' => true
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER
            ]
        ];

        $headerCount = 1;
        foreach($headers as $val)
        {
            $cell = StringOperations::numberToAlphabet($headerCount)."1";
            $activeSheet->setCellValue($cell, $val);
            $activeSheet->getStyle($cell)->applyFromArray($headerStyle);
            
            $activeSheet->getColumnDimension(StringOperations::numberToAlphabet($headerCount))->setAutoSize(true);
            
            $headerCount++;
        }

        if($type == 'h')
        {
            $createdDate = $this->setClaimHeaderCells($records, $projects, $activeSheet);
        }
        elseif($type =='i')
        {
            $createdDate = $this->setClaimItemCells($records, $projects, $activeSheet);
        }

        $path = getenv('S4HANA_FILES_PATH');

        if($path and File::isDirectory($path))
        {
            $writer = new Xlsx($spreadsheet);

            $filePath = $path.DIRECTORY_SEPARATOR.'claims';

            $filenameIndicator = "000-".sprintf('%03d', count($records));

            $filename = $filenamePrefix.'_'.$filenameIndicator.'_'.Carbon::parse($createdDate)->format('YmdHis').".".\PCK\Helpers\Files::EXTENSION_EXCEL;

            $writer->save($filePath.DIRECTORY_SEPARATOR.$filename);

            return $filePath.DIRECTORY_SEPARATOR.$filename;
        }

        return null;
    }

    protected function setContractHeaderCells(Array $records, Array $projects, Worksheet &$worksheet)
    {
        $createdDate = null;

        $data = [];
        $projectIds = [];
        $contracts = [];

        if($projects)
        {
            $projectIds = \DB::connection('buildspace')
            ->table(\DB::raw("bs_project_structures AS p"))
            ->join(\DB::raw("bs_project_main_information AS m"), "m.project_structure_id", "=", "p.id")
            ->select('p.id', 'm.eproject_origin_id')
            ->whereIn('p.id', array_keys($projects))
            ->orderBy('p.priority', 'asc')
            ->lists("id", "eproject_origin_id");
        }
        
        if($projectIds)
        {
            $pamRecords = PAM2006ProjectDetail::whereIn('project_id', array_keys($projectIds))->get();
            foreach($pamRecords as $pamRecord)
            {
                $contracts[$projectIds[$pamRecord->project_id]] = [
                    'liquidate_damages' => $pamRecord->liquidate_damages,
                    'amount_performance_bond' => $pamRecord->amount_performance_bond,
                    'cpc_date' => $pamRecord->cpc_date,
                    'extension_of_time_date' => $pamRecord->extension_of_time_date,
                    'defect_liability_period' => $pamRecord->defect_liability_period,
                    'defect_liability_period_unit' => $pamRecord->defect_liability_period_unit,
                    'certificate_of_making_good_defect_date' => $pamRecord->certificate_of_making_good_defect_date,
                    'cnc_date' => $pamRecord->cnc_date,
                    'performance_bond_validity_date' => $pamRecord->performance_bond_validity_date,
                    'insurance_policy_coverage_date' => $pamRecord->insurance_policy_coverage_date
                ];
            }

            unset($pamRecords);
        }
        
        foreach($records as $record)
        {
            $createdDate = $record->created_at;

            $publishedDate    = ($record->published_date) ? Carbon::parse($record->published_date)->format('d.m.Y') : null;
            $dateOfAward      = ($record->date_of_award) ? Carbon::parse($record->date_of_award)->format('d.m.Y') : null;
            $commencementDate = ($record->commencement_date) ? Carbon::parse($record->commencement_date)->format('d.m.Y') : null;
            $completionDate   = ($record->completion_date) ? Carbon::parse($record->completion_date)->format('d.m.Y') : null;

            $amountPerformanceBond = 0;
            $cpcDate = null;
            $extensionOfTimeDate = null;
            $dlpPeriod = null;
            $dlpPeriodUnit = null;
            $cmgdDate = null;
            $cncDate = null;
            $liquidateDamages = 0;
            $performanceBondValidityDate = null;
            $insurancePolicyCoverageDate = null;

            if(array_key_exists($record->project_id, $contracts))
            {
                $amountPerformanceBond = ($contracts[$record->project_id]['amount_performance_bond']) ? $contracts[$record->project_id]['amount_performance_bond'] : 0;
                $cpcDate = ($contracts[$record->project_id]['cpc_date']) ? Carbon::parse($contracts[$record->project_id]['cpc_date'])->format('d.m.Y') : null;
                $extensionOfTimeDate = ($contracts[$record->project_id]['extension_of_time_date']) ? Carbon::parse($contracts[$record->project_id]['extension_of_time_date'])->format('d.m.Y') : null;
                
                if(!empty($contracts[$record->project_id]['defect_liability_period']))
                {
                    $dlpPeriod = $contracts[$record->project_id]['defect_liability_period'];
                    switch($contracts[$record->project_id]['defect_liability_period_unit'])
                    {
                        case 2:
                            $dlpPeriodUnit = 'Weeks';
                            break;
                        case 4:
                            $dlpPeriodUnit = 'Days';
                            break;
                        default:
                            $dlpPeriodUnit = 'Months';
                    }
                }

                $cmgdDate = ($contracts[$record->project_id]['certificate_of_making_good_defect_date']) ? Carbon::parse($contracts[$record->project_id]['certificate_of_making_good_defect_date'])->format('d.m.Y') : null;
                $cncDate = ($contracts[$record->project_id]['cnc_date']) ? Carbon::parse($contracts[$record->project_id]['cnc_date'])->format('d.m.Y') : null;
                $liquidateDamages = ($contracts[$record->project_id]['liquidate_damages']) ? $contracts[$record->project_id]['liquidate_damages'] : 0;
                $performanceBondValidityDate = ($contracts[$record->project_id]['performance_bond_validity_date']) ? Carbon::parse($contracts[$record->project_id]['performance_bond_validity_date'])->format('d.m.Y') : null;
                $insurancePolicyCoverageDate = ($contracts[$record->project_id]['insurance_policy_coverage_date']) ? Carbon::parse($contracts[$record->project_id]['insurance_policy_coverage_date'])->format('d.m.Y') : null;
            }
            
            $data[] = [
                $publishedDate,
                $record->reference,
                (array_key_exists($record->project_id, $projects)) ? $projects[$record->project_id] : "",
                $record->business_unit,
                $record->letter_of_award_number,
                $dateOfAward,
                $record->work_category,
                $record->contractor,
                $commencementDate,
                $completionDate,
                $record->currency,
                round($record->contract_sum, 2),
                round($record->retention, 2),
                round($record->max_retention_sum, 2),
                round($amountPerformanceBond, 2),
                $cpcDate,
                $extensionOfTimeDate,
                $dlpPeriod." ".$dlpPeriodUnit,
                $cmgdDate,
                $cncDate,
                round($liquidateDamages, 2),
                $performanceBondValidityDate,
                $insurancePolicyCoverageDate
            ];
        }

        foreach(['B', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W'] as $column)
        {
            $worksheet->getColumnDimension($column)->setAutoSize(false);
            $worksheet->getColumnDimension($column)->setWidth(32);
        }

        $worksheet->getColumnDimension('C')->setAutoSize(false);
        $worksheet->getColumnDimension('C')->setWidth(128);

        foreach(['D', 'E'] as $column)
        {
            $worksheet->getColumnDimension($column)->setAutoSize(false);
            $worksheet->getColumnDimension($column)->setWidth(74);
        }

        foreach(['L', 'M', 'N', 'O', 'U'] as $column)
        {
            $worksheet->getStyle($column)->getNumberFormat()->setFormatCode("#,##0.00");
        }

        $worksheet->fromArray($data, null, 'A2');

        return ($createdDate) ? $createdDate : date('Y-m-d H:i:s');
    }

    protected function setContractItemCells(Array $records, Array $projects, Worksheet &$worksheet)
    {
        $createdDate = null;

        $data = [];

        foreach($records as $record)
        {
            $createdDate = $record->created_at;

            $approvedDate = ($record->approved_date) ? Carbon::parse($record->approved_date)->format('d.m.Y') : null;

            $data[] = [
                $approvedDate,
                $record->contract_number,
                $record->item_type,
                $record->item_id,
                $record->item_title,
                'APPROVED',
                round($record->reference_amount, 2),
                round($record->total, 2),
                round($record->nett_omission_addition, 2),
                round($record->previous_claim, 2),
                round($record->current_claim, 2),
                round($record->up_to_date_claim, 2)
            ];
        }

        $worksheet->getColumnDimension('C')->setAutoSize(false);
        $worksheet->getColumnDimension('C')->setWidth(12);

        $worksheet->getColumnDimension('E')->setAutoSize(false);
        $worksheet->getColumnDimension('E')->setWidth(128);

        foreach(['B', 'D', 'G', 'H', 'I', 'J', 'K', 'L'] as $column)
        {
            $worksheet->getColumnDimension($column)->setAutoSize(false);
            $worksheet->getColumnDimension($column)->setWidth(32);
        }

        foreach(['G', 'H', 'I', 'J', 'K', 'L'] as $column)
        {
            $worksheet->getStyle($column)->getNumberFormat()->setFormatCode("#,##0.00");
        }

        $worksheet->fromArray($data, null, 'A2');

        return ($createdDate) ? $createdDate : date('Y-m-d H:i:s');
    }

    protected function setClaimHeaderCells(Array $records, Array $projects, Worksheet &$worksheet)
    {
        $createdDate = null;

        $claimCertificateInvoices = [];

        if(!empty($records))
        {
            foreach($records as $record)
            {
                $claimCertificateIds[] = $record->claim_certificate_id;
            }

            $invoiceRecords = \DB::connection('buildspace')
                ->table(\DB::raw("bs_claim_certificate_invoices AS i"))
                ->select('i.claim_certificate_id', 'i.invoice_number', 'i.invoice_date')
                ->whereIn('i.claim_certificate_id', $claimCertificateIds)
                ->get();

            foreach($invoiceRecords as $k => $invoiceRecord)
            {
                $claimCertificateInvoices[$invoiceRecord->claim_certificate_id] = $invoiceRecord;

                unset($invoiceRecords[$k]);
            }
        }

        $data = [];

        foreach($records as $record)
        {
            $createdDate = $record->created_at;

            $contractorSubmittedDate = ($record->contractor_submitted_date) ? Carbon::parse($record->contractor_submitted_date)->format('d.m.Y') : null;
            $siteVerifiedDate = ($record->site_verified_date) ? Carbon::parse($record->site_verified_date)->format('d.m.Y') : null;
            $certificateReceivedDate = ($record->certificate_received_date) ? Carbon::parse($record->certificate_received_date)->format('d.m.Y') : null;
            $paymentDueDate = ($record->payment_due_date) ? Carbon::parse($record->payment_due_date)->format('d.m.Y') : null;

            $invoiceNumber = null;
            $invoiceDate = null;
            if(array_key_exists($record->claim_certificate_id, $claimCertificateInvoices))
            {
                $invoiceNumber = $claimCertificateInvoices[$record->claim_certificate_id]->invoice_number;
                $invoiceDate = ($claimCertificateInvoices[$record->claim_certificate_id]->invoice_date) ? Carbon::parse($claimCertificateInvoices[$record->claim_certificate_id]->invoice_date)->format('d.m.Y') : null;
            }

            $data[] = [
                Carbon::parse($record->approved_date)->format('d.m.Y'),
                Carbon::parse($record->creation_date)->format('d.m.Y'),
                $record->contract_number,
                $record->claim_number,
                $contractorSubmittedDate,
                $siteVerifiedDate,
                $certificateReceivedDate,
                $paymentDueDate,
                'APPROVED',
                $record->currency,
                round($record->contract_sum, 2),
                round($record->work_done, 2),
                round($record->amount_certified, 2),
                round($record->percentage_completion, 2),
                $invoiceDate,
                $invoiceNumber,
                round($record->acc_retention_sum, 2),
                round($record->retention_sum, 2),
                round($record->release_retention_percentage, 2),
                round($record->acc_release_retention, 2),
                round($record->release_retention, 2)
            ];
        }

        $worksheet->getColumnDimension('C')->setAutoSize(false);
        $worksheet->getColumnDimension('C')->setWidth(38);

        foreach(['K', 'L', 'M', 'N', 'Q', 'R', 'S', 'T', 'U'] as $column)
        {
            $worksheet->getStyle($column)->getNumberFormat()->setFormatCode("#,##0.00");
        }

        $worksheet->fromArray($data, null, 'A2');

        return ($createdDate) ? $createdDate : date('Y-m-d H:i:s');
    }

    protected function setClaimItemCells(Array $records, Array $projects, Worksheet &$worksheet)
    {
        $createdDate = null;

        $data = [];

        foreach($records as $record)
        {
            $createdDate = $record->created_at;

            $itemType = $record->item_type;
            
            $data[] = [
                Carbon::parse($record->approved_date)->format('d.m.Y'),
                $record->contract_number,
                $record->claim_number,
                $itemType,
                $record->item_id,
                $record->item_title,
                'APPROVED',
                round($record->total, 2),
                round($record->claim_amount, 2)
            ];
        }

        $worksheet->getColumnDimension('B')->setAutoSize(false);
        $worksheet->getColumnDimension('B')->setWidth(38);

        $worksheet->getColumnDimension('F')->setAutoSize(false);
        $worksheet->getColumnDimension('F')->setWidth(128);

        foreach(['H', 'I'] as $column)
        {
            $worksheet->getStyle($column)->getNumberFormat()->setFormatCode("#,##0.00");
        }

        $worksheet->fromArray($data, null, 'A2');

        return ($createdDate) ? $createdDate : date('Y-m-d H:i:s');
    }

    public function getAccountCodeType($itemId)
    {
        $accountCode = AccountCode::find($itemId);

        return AccountCode::getTypeText($accountCode->type);
    }
}