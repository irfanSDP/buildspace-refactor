<?php

class generateClaimsTask extends sfBaseTask
{
    protected $startTime;

    protected function configure()
    {
        $this->addOptions(array(
            new sfCommandOption('application', "app", sfCommandOption::PARAMETER_REQUIRED, 'The application name', "backend")
        ));

        $this->namespace        = 's4hana';
        $this->name             = 'generateClaims';
        $this->briefDescription = '';
        $this->detailedDescription = <<<EOF
The [s4hana:generateClaims|INFO] generates integration claim files for S4Hana.
Call it with:

  [php symfony s4hana:generateClaims|INFO]
EOF;
    }
    
    protected function execute($arguments = array(), $options = array())
    {
        ini_set('memory_limit','2048M');

        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $mainConn = $databaseManager->getDatabase('main_conn')->getConnection();

        $this->startTime = microtime(true); 

        $stmt = $mainConn->prepare("SELECT COALESCE(MAX(h.batch_number), 0) AS batch_number
        FROM bs_contract_headers h
        WHERE NOT EXISTS (
            SELECT FROM bs_claim_headers
            WHERE bs_claim_headers.batch_number = h.batch_number
        )");
        
        $stmt->execute();

        $latestBatchNumber = $stmt->fetch(PDO::FETCH_COLUMN, 0);

        if(!$latestBatchNumber)
        {
            $this->logSection('say', 'Batch Number does not exists in contract header records');

            return $this->logSection('say', 'END');
        }

        $this->generateClaimData($latestBatchNumber, $mainConn);

        $this->generateExcel($latestBatchNumber, $mainConn);

        $endTime = microtime(true); 

        $executionTime = ($endTime - $this->startTime); 

        $this->logSection('say', $executionTime.' seconds');

        $this->logSection('say', 'END');
    }

    protected function generateClaimData(int $batchNumber, $con)
    {
        $this->logSection('Claim Headers', 'Preparing claim header data...');

        $stmt = $con->prepare("SELECT i.claim_certificate_id
        FROM bs_integration_exported_claim_certificates i
        WHERE i.batch_number = ".$batchNumber."
        AND i.claim_certificate_id NOT IN (
            SELECT iecc.claim_certificate_id FROM bs_integration_exported_claim_certificates iecc
            WHERE iecc.batch_number < i.batch_number
        ) ORDER BY i.claim_certificate_id");

        $stmt->execute();

        $batchClaimCertificateIds = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);

        if(empty($batchClaimCertificateIds))
        {
            $this->logSection('say', 'No claim certificate records');

            return $this->logSection('say', 'END');
        }

        $records = [];

        $claimCertCollection = Doctrine_Query::create()
        ->from('ClaimCertificate c')
        ->leftJoin('c.PostContractClaimRevision r')
        ->whereIn('c.id', $batchClaimCertificateIds)
        ->orderBy('r.version DESC')
        ->execute();

        foreach($claimCertCollection as $record)
        {
            $records[] = $record;
        }

        unset($claimCertCollection, $batchClaimCertificateIds);

        $claimCertificates = ClaimCertificateTable::getClaimCertInfo($records, false, true);

        $createdDate = date('Y-m-d H:i:s');

        self::arrayBatch($claimCertificates, 500, function($batch)use($con, $batchNumber, $createdDate) {
            $insertRecords = [];
            $questionMarks = [];
            $fields = [];

            foreach($batch as $claimCertificate)
            {
                $dueDate = ($claimCertificate['dueDate']) ? str_replace('/', '-', $claimCertificate['dueDate']) : null;

                $record = [
                    'batch_number' => $batchNumber,
                    'project_id' => $claimCertificate['projectId'],
                    'claim_certificate_id' => $claimCertificate['certId'],
                    'contract_number' => $claimCertificate['projectCode'],
                    'claim_number' => $claimCertificate['claimNo'],
                    'contractor_submitted_date' => ($claimCertificate['contractorSubmittedDate']) ? date('Y-m-d', strtotime($claimCertificate['contractorSubmittedDate'])) : null,
                    'site_verified_date' => ($claimCertificate['siteVerifiedDate']) ? date('Y-m-d', strtotime($claimCertificate['siteVerifiedDate'])) : null,
                    'certificate_received_date' => ($claimCertificate['certificateDate']) ? date('Y-m-d', strtotime($claimCertificate['certificateDate'])) : null,
                    'payment_due_date' => ($dueDate) ? date('Y-m-d', strtotime($dueDate)) : null,
                    'currency' => $claimCertificate['currencyCode'],
                    'contract_sum' => $claimCertificate['contractSum'],
                    'work_done' => $claimCertificate['totalWorkDone'],
                    'amount_certified' => $claimCertificate['amountCertified'],
                    'percentage_completion' => $claimCertificate['completionPercentage'],
                    'acc_retention_sum' => $claimCertificate['cumulativeRetentionSum'],
                    'retention_sum' => $claimCertificate['currentRetentionSum'],
                    'release_retention_percentage' => $claimCertificate['retention_tax_percentage'],
                    'acc_release_retention' => $claimCertificate['cumulativeReleasedRetentionAmount'],
                    'release_retention' => $claimCertificate['currentReleaseRetentionAmount'],
                    'approved_date' => $claimCertificate['approvalDate'],
                    'creation_date' => $claimCertificate['createdDate'],
                    'created_at' => $createdDate
                ];

                $fields = array_keys($record);
                $insertRecords = array_merge($insertRecords, array_values($record));
                $questionMarks[] = '('.implode(',', array_fill(0, count($record), '?')).')';
            }

            if($insertRecords)
            {
                try
                {
                    $this->logSection('Claim Headers', 'Inserting claim header records...');

                    $con->beginTransaction();
                    
                    $stmt = $con->prepare("INSERT INTO bs_claim_headers
                    (".implode(',', $fields).")
                    VALUES " . implode(',', $questionMarks));

                    $stmt->execute($insertRecords);
                    
                    $con->commit();

                    $this->logSection('Claim Headers', 'Successfully inserted claim headers!');
                }
                catch(Exception $e)
                {
                    $con->rollBack();

                    return $this->logSection('Contract Headers', $e->getMessage());
                }

                    unset($insertRecords);
            }
        });

        $this->generateItemData($batchNumber, $claimCertificates, $createdDate, $con);
    }

    protected function generateItemData(int $batchNumber, Array $claimCertificates, $createdDate, $con)
    {
        $standardClaimBills = ClaimCertificateTable::getStandardBillClaimsByClaimCertificateIds(array_keys($claimCertificates));

        $endTime = microtime(true); 
        $executionTime = ($endTime - $this->startTime); 
        $this->logSection('say', 'done querying standard claim items data in '.$executionTime.' seconds');

        $premliminaryClaimBills = ClaimCertificateTable::getPreliminaryBillClaimsByClaimCertificateIds(array_keys($claimCertificates));

        $endTime = microtime(true); 
        $executionTime = ($endTime - $this->startTime); 
        $this->logSection('say', 'done querying prelim claim items data in '.$executionTime.' seconds');

        $variationOrders = ClaimCertificateTable::getVariationOrdersByClaimCertificateIds(array_keys($claimCertificates));

        $endTime = microtime(true); 
        $executionTime = ($endTime - $this->startTime); 
        $this->logSection('say', 'done querying variation order items data in '.$executionTime.' seconds');
        
        $materialOnSites  = ClaimCertificateTable::getMaterialOnSitesByClaimCertificateIds(array_keys($claimCertificates));
        $debitCreditNotes = ClaimCertificateTable::getDebitCreditNotesByClaimCertificateIds(array_keys($claimCertificates));
        $advancePayments  = ClaimCertificateTable::getAdvancePaymentsByClaimCertificateIds(array_keys($claimCertificates));

        $endTime = microtime(true); 

        $executionTime = ($endTime - $this->startTime); 

        $this->logSection('say', 'done querying items data in '.$executionTime.' seconds');

        $items = [];
        $projects = [];

        foreach($claimCertificates as $claimCertId => $claimCertificate)
        {
            if(!array_key_exists($claimCertificate['projectId'], $projects))
            {
                $projects[$claimCertificate['projectId']] = [
                    'contract_number' => $claimCertificate['projectCode'],
                    'claims'          => []
                ];
            }

            $projects[$claimCertificate['projectId']]['claims'][$claimCertId] = [
                'approved_claim_date' => $claimCertificate['approvalDate'],
                'claim_number'        => $claimCertificate['claimNo']
            ];
        }

        unset($claimCertificates);

        foreach($projects as $projectId => $projectData)
        {
            foreach($projectData['claims'] as $projectClaimCertId => $claimCertificate)
            {
                if(array_key_exists($projectClaimCertId, $premliminaryClaimBills))
                {
                    foreach($premliminaryClaimBills[$projectClaimCertId] as $claim)
                    {
                        if($claim['current_amount'] != 0)
                        {
                            $items[] = [
                                $batchNumber,
                                $projectId,
                                $claim['claim_certificate_id'],
                                $projectData['contract_number'],
                                $claimCertificate['claim_number'],
                                $claim['bill_id'],
                                $claim['bill_title'],
                                'BILL',
                                $claim['up_to_date_amount'],
                                $claim['current_amount'],
                                $claimCertificate['approved_claim_date'],
                                $createdDate
                            ];
                        }
                    }
                }

                if(array_key_exists($projectClaimCertId, $standardClaimBills))
                {
                    foreach($standardClaimBills[$projectClaimCertId] as $idx => $claim)
                    {
                        if($claim['current_amount'] != 0)
                        {
                            $items[] = [
                                $batchNumber,
                                $projectId,
                                $claim['claim_certificate_id'],
                                $projectData['contract_number'],
                                $claimCertificate['claim_number'],
                                $claim['bill_id'],
                                $claim['bill_title'],
                                'BILL',
                                $claim['up_to_date_amount'],
                                $claim['current_amount'],
                                $claimCertificate['approved_claim_date'],
                                $createdDate
                            ];
                            unset($standardClaimBills[$projectClaimCertId][$idx]);
                        }
                    }
                }
                
                if(array_key_exists($projectClaimCertId, $variationOrders))
                {
                    foreach($variationOrders[$projectClaimCertId] as $idx => $claim)
                    {
                        $items[] = [
                            $batchNumber,
                            $projectId,
                            $claim['claim_certificate_id'],
                            $projectData['contract_number'],
                            $claimCertificate['claim_number'],
                            $claim['bill_id'],
                            $claim['bill_title'],
                            'VO',
                            $claim['up_to_date_amount'],
                            $claim['current_amount'],
                            $claim['created_at'],
                            $createdDate
                        ];

                        unset($variationOrders[$projectClaimCertId][$idx]);
                    }
                }

                if(array_key_exists($projectClaimCertId, $materialOnSites))
                {
                    $items[] = [
                        $batchNumber,
                        $projectId,
                        $materialOnSites[$projectClaimCertId]['claim_certificate_id'],
                        $projectData['contract_number'],
                        $claimCertificate['claim_number'],
                        $materialOnSites[$projectClaimCertId]['claim_certificate_id'],
                        'MATERIAL ON SITE',
                        'MATERIAL ON SITE',
                        $materialOnSites[$projectClaimCertId]['up_to_date_amount'],
                        $materialOnSites[$projectClaimCertId]['current_amount'],
                        $claimCertificate['approved_claim_date'],
                        $createdDate
                    ];

                    unset($materialOnSites[$projectClaimCertId]);
                }

                if(array_key_exists($projectClaimCertId, $debitCreditNotes))
                {
                    foreach($debitCreditNotes[$projectClaimCertId] as $claim)
                    {
                        $items[] = [
                            $batchNumber,
                            $projectId,
                            $claim['claim_certificate_id'],
                            $projectData['contract_number'],
                            $claimCertificate['claim_number'],
                            $claim['bill_id'],
                            $claim['bill_title'],
                            'DEBIT AND CREDIT NOTE',
                            $claim['up_to_date_amount'],
                            $claim['current_amount'],
                            $claimCertificate['approved_claim_date'],
                            $createdDate
                        ];
                    }
                }

                if(array_key_exists($projectClaimCertId, $advancePayments))
                {
                    $items[] = [
                        $batchNumber,
                        $projectId,
                        $advancePayments[$projectClaimCertId]['claim_certificate_id'],
                        $projectData['contract_number'],
                        $claimCertificate['claim_number'],
                        $advancePayments[$projectClaimCertId]['claim_certificate_id'],
                        'ADVANCE PAYMENT',
                        'ADVANCE PAYMENT',
                        $advancePayments[$projectClaimCertId]['up_to_date_amount'],
                        $advancePayments[$projectClaimCertId]['current_amount'],
                        $claimCertificate['approved_claim_date'],
                        $createdDate
                    ];
                }
            }
        }

        unset($premliminaryClaimBills, $standardClaimBills, $variationOrders, $materialOnSites, $debitCreditNotes, $advancePayments);

        self::arrayBatch($items, 500, function($batch)use($con, $batchNumber, $createdDate) {
            $insertRecords = [];
            $questionMarks = [];

            foreach($batch as $record)
            {
                $insertRecords = array_merge($insertRecords, $record);
                $questionMarks[] = '('.implode(',', array_fill(0, count($record), '?')).')';
            }

            if($insertRecords)
            {
                try
                {
                    $this->logSection('Claim Items', 'Inserting claim items records...');

                    $con->beginTransaction();
                    
                    $stmt = $con->prepare("INSERT INTO bs_claim_items
                    (batch_number, project_id, claim_certificate_id, contract_number, claim_number, item_id, item_title, item_type, total, claim_amount, approved_date, created_at)
                    VALUES " . implode(',', $questionMarks));

                    $stmt->execute($insertRecords);
                    
                    $con->commit();

                    $this->logSection('Claim Items', 'Successfully inserted claim items!');
                }
                catch(Exception $e)
                {
                    $con->rollBack();

                    return $this->logSection('Contract Items', $e->getMessage());
                }

                    unset($insertRecords);
            }
        });
    }

    protected function generateExcel(int $batchNumber, $con)
    {
        $stmt = $con->prepare("SELECT h.created_at
        FROM bs_claim_headers h
        JOIN bs_project_structures p ON h.project_id = p.id
        WHERE h.batch_number = ".$batchNumber."
        AND p.type = ".ProjectStructure::TYPE_ROOT."
        AND p.deleted_at IS NULL
        ORDER BY p.priority
        LIMIT 1");

        $stmt->execute();

        $createdDate = $stmt->fetch(PDO::FETCH_COLUMN, 0);

        $stmt = $con->prepare("SELECT p.title, h.*
        FROM bs_claim_headers h
        JOIN bs_project_structures p ON h.project_id = p.id
        JOIN bs_claim_certificates c ON h.claim_certificate_id = c.id
        WHERE h.batch_number = ".$batchNumber."
        AND p.type = ".ProjectStructure::TYPE_ROOT."
        AND p.deleted_at IS NULL
        ORDER BY h.batch_number, p.priority, c.post_contract_claim_revision_id");

        $stmt->execute();

        $headers = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if($headers)
        {
            $this->logSection('say', 'Generating header excel file...');

            $spreadsheet = $this->generateHeaderExcel($headers, $con);

            $filename = "000-".sprintf('%03d', count($headers));
            $tmpFile = $this->write($spreadsheet, 'BUILDSPACE_CLH_'.$filename.'_'.date('YmdHis', strtotime($createdDate)));

            $this->logSection('say', $tmpFile.' generated');

            $this->logSection('say', 'Uploading header file...');

            $this->uploadToEndpoint($tmpFile, sfConfig::get('app_s4hana_claim_header_directory', '/CLH'));
        }

        $stmt = $con->prepare("SELECT p.title, i.*
        FROM bs_claim_items i
        JOIN bs_project_structures p ON i.project_id = p.id
        JOIN bs_claim_certificates c ON i.claim_certificate_id = c.id
        WHERE i.batch_number = ".$batchNumber."
        AND p.type = ".ProjectStructure::TYPE_ROOT."
        AND p.deleted_at IS NULL
        ORDER BY i.contract_number, i.claim_number, p.priority, c.post_contract_claim_revision_id");

        $stmt->execute();

        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if($items)
        {
            $this->logSection('say', 'Generating item excel file...');

            $spreadsheet = $this->generateItemExcel($items);

            $filename = "000-".sprintf('%03d', count($items));
            $tmpFile = $this->write($spreadsheet, 'BUILDSPACE_CLI_'.$filename.'_'.date('YmdHis', strtotime($createdDate)));

            $this->logSection('say', $tmpFile.' generated');

            $this->logSection('say', 'Uploading item file...');

            $this->uploadToEndpoint($tmpFile, sfConfig::get('app_s4hana_claim_item_directory', '/CLI'));
        }
    }

    protected function generateHeaderExcel(Array $records, $con)
    {
        $claimCertificateIds = array_column($records, 'claim_certificate_id');

        $claimCertificateInvoices = [];

        if(!empty($claimCertificateIds))
        {
            $stmt = $con->prepare("SELECT i.claim_certificate_id, i.invoice_number, i.invoice_date
            FROM bs_claim_certificate_invoices i
            WHERE i.claim_certificate_id IN (".implode(',', $claimCertificateIds).")");
            
            $stmt->execute();
            
            $invoiceRecords = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach($invoiceRecords as $k => $invoiceRecord)
            {
                $claimCertificateInvoices[$invoiceRecord['claim_certificate_id']] = $invoiceRecord;

                unset($invoiceRecords[$k]);
            }
        }

        $objPHPExcel = new sfPhpExcel();
        $objPHPExcel->getProperties()->setCreator("Buildspace");

        $headers = [
            'A' => "Approval Date",
            'B' => "Creation Date",
            'C' => "Contract Number",
            'D' => "Claim No.",
            'E' => "Contractor Submitted Date",
            'F' => "Site Verified Date",
            'G' => "Certificate Received Date",
            'H' => "Payment Due Date",
            'I' => "Status",
            'J' => "Currency",
            'K' => "Contract Sum",
            'L' => "Work Done Amount",
            'M' => "Amount Certified",
            'N' => "% Completion",
            'O' => "Invoice Date",
            'P' => "Invoice Reference No.",
            'Q' => "Accm. Retention Sum",
            'R' => "This Retention Sum",
            'S' => "Release Retention (%)",
            'T' => "Accm. Release Retention (Amt)",
            'U' => "This Release Retention (Amt)"
        ];

        $activeSheet = $objPHPExcel->getActiveSheet();

        $activeSheet->setTitle("Headers");
        $activeSheet->setAutoFilter('A1:U1');

        $headerStyle = [
            'font' => [
                'bold' => true
            ],
            'alignment' => [
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical'   => PHPExcel_Style_Alignment::VERTICAL_CENTER
            ]
        ];

        foreach($headers as $col => $val)
        {
            $cell = $col."1";
            $activeSheet->setCellValue($cell, $val);
            $activeSheet->getStyle($cell)->applyFromArray($headerStyle);

            $activeSheet->getColumnDimension($col)->setAutoSize(true);
        }

        $activeSheet->getColumnDimension('C')->setAutoSize(false);
        $activeSheet->getColumnDimension('C')->setWidth(38);

        foreach(['K', 'L', 'M', 'N', 'Q', 'R', 'S', 'T', 'U'] as $column)
        {
            $activeSheet->getStyle($column)->getNumberFormat()->setFormatCode("#,##0.00");
        }

        $data = [];

        foreach($records as $idx => $record)
        {
            $approvedDate            = ($record['approved_date']) ? date('d.m.Y', strtotime($record['approved_date'])) : null;
            $creationDate            = ($record['creation_date']) ? date('d.m.Y', strtotime($record['creation_date'])) : null;
            $contractorSubmittedDate = ($record['contractor_submitted_date']) ? date('d.m.Y', strtotime($record['contractor_submitted_date'])) : null;
            $siteVerifiedDate        = ($record['site_verified_date']) ? date('d.m.Y', strtotime($record['site_verified_date'])) : null;
            $certificateReceivedDate = ($record['certificate_received_date']) ? date('d.m.Y', strtotime($record['certificate_received_date'])) : null;
            $paymentDueDate          = ($record['payment_due_date']) ? date('d.m.Y', strtotime($record['payment_due_date'])) : null;
            $invoiceDate             = (array_key_exists($record['claim_certificate_id'], $claimCertificateInvoices)) ? date('d.m.Y', strtotime($claimCertificateInvoices[$record['claim_certificate_id']]['invoice_date'])) : null;
            
            $invoiceNumber           = (array_key_exists($record['claim_certificate_id'], $claimCertificateInvoices)) ? mb_strtoupper($claimCertificateInvoices[$record['claim_certificate_id']]['invoice_number']) : null;

            $data[] = [
                $approvedDate,
                $creationDate,
                $record['contract_number'],
                $record['claim_number'],
                $contractorSubmittedDate,
                $siteVerifiedDate,
                $certificateReceivedDate,
                $paymentDueDate,
                'APPROVED',
                $record['currency'],
                round($record['contract_sum'], 2),
                round($record['work_done'], 2),
                round($record['amount_certified'], 2),
                round($record['percentage_completion'], 2),
                $invoiceDate,
                $invoiceNumber,
                round($record['acc_retention_sum'], 2),
                round($record['retention_sum'], 2),
                round($record['release_retention_percentage'], 2),
                round($record['acc_release_retention'], 2),
                round($record['release_retention'], 2)
            ];
            
            unset($records[$idx]);
        }

        $activeSheet->fromArray($data, null, 'A2');

        return $objPHPExcel;
    }

    protected function generateItemExcel(Array $records)
    {
        $objPHPExcel = new sfPhpExcel();
        $objPHPExcel->getProperties()->setCreator("Buildspace");

        $headers = [
            'A' => "Approved Claim Date",
            'B' => "Contract Number",
            'C' => "Claim No.",
            'D' => "Type",
            'E' => "BuildSpace Unique Key",
            'F' => "Description",
            'G' => "Status",
            'H' => "ACCM Total (Amount)",
            'I' => "This Claim (Amount)"
        ];

        $activeSheet = $objPHPExcel->getActiveSheet();

        $activeSheet->setTitle("Items");
        $activeSheet->setAutoFilter('A1:I1');

        $headerStyle = [
            'font' => [
                'bold' => true
            ],
            'alignment' => [
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical'   => PHPExcel_Style_Alignment::VERTICAL_CENTER
            ]
        ];

        foreach($headers as $col => $val)
        {
            $cell = $col."1";
            $activeSheet->setCellValue($cell, $val);
            $activeSheet->getStyle($cell)->applyFromArray($headerStyle);

            $activeSheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        $activeSheet->getColumnDimension('B')->setAutoSize(false);
        $activeSheet->getColumnDimension('B')->setWidth(38);

        $activeSheet->getColumnDimension('F')->setAutoSize(false);
        $activeSheet->getColumnDimension('F')->setWidth(128);

        foreach(['H', 'I'] as $column)
        {
            $activeSheet->getStyle($column)->getNumberFormat()->setFormatCode("#,##0.00");
        }

        $data = [];

        foreach($records as $idx => $record)
        {
            $approvedDate = ($record['approved_date']) ? date('d.m.Y', strtotime($record['approved_date'])) : null;

            $data[] = [
                $approvedDate,
                $record['contract_number'],
                $record['claim_number'],
                $record['item_type'],
                $record['item_id'],
                $record['item_title'],
                'APPROVED',
                round($record['total'], 2),
                round($record['claim_amount'], 2)
            ];

            unset($records[$idx]);
        }

        $activeSheet->fromArray($data, null, 'A2');

        return $objPHPExcel;
    }

    protected function uploadToEndpoint($dataFile, $remoteDir)
    {
        $host = sfConfig::get('app_s4hana_host', '127.0.0.1');
        $port = sfConfig::get('app_s4hana_port', 22);
        $username = sfConfig::get('app_s4hana_username', 'username');
        $password = sfConfig::get('app_s4hana_password', 'password');
        
        $ch = curl_init('sftp://' . $host . ':' . $port . $remoteDir . '/' . basename($dataFile));
        
        $fh = fopen($dataFile, 'r');
        
        if ($fh)
        {
            curl_setopt($ch, CURLOPT_USERPWD, $username . ':' . $password);
            curl_setopt($ch, CURLOPT_UPLOAD, true);
            curl_setopt($ch, CURLOPT_PROTOCOLS, CURLPROTO_SFTP);
            curl_setopt($ch, CURLOPT_INFILE, $fh);
            curl_setopt($ch, CURLOPT_INFILESIZE, filesize($dataFile));
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);//insecure mode because of ssl cert from host was not valid
            curl_setopt($ch, CURLOPT_VERBOSE, true);
        
            $verbose = fopen('php://temp', 'w+');
            curl_setopt($ch, CURLOPT_STDERR, $verbose);
        
            $response = curl_exec($ch);
            $error = curl_error($ch);
            curl_close($ch);
        
            if ($response)
            {
                $this->logSection('say', 'Successfully uploaded '.basename($dataFile).'...');
            }
            else
            {
                rewind($verbose);
                $verboseLog = stream_get_contents($verbose);

                $this->logSection('Endpoint error', $verboseLog);
            }
        }
    }

    protected function write(sfPhpExcel $objPHPExcel, $fileName)
    {
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $tmpName = $fileName.".xlsx";

        $s4hanaDir = sfConfig::get('sf_data_dir') . DIRECTORY_SEPARATOR . 's4hana';
        if(!is_dir($s4hanaDir) )
        {
            mkdir($s4hanaDir, 0777, true);
        }

        $claimsDir = sfConfig::get('sf_data_dir') . DIRECTORY_SEPARATOR . 's4hana' . DIRECTORY_SEPARATOR . 'claims';
        if(!is_dir($claimsDir) )
        {
            mkdir($claimsDir, 0777, true);
        }

        $tmpFile = $claimsDir.DIRECTORY_SEPARATOR.$tmpName;

        $objWriter->save($tmpFile);

        unset($objPHPExcel);

        return $tmpFile;
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
