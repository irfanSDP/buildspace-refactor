<?php

class generateContractsTask extends sfBaseTask
{
    protected $startTime;
    protected $buildspaceCon;
    protected $eprojectCon;
    //protected $batchNumber;

    protected function configure()
    {
        $this->addOptions(array(
            new sfCommandOption('application', "app", sfCommandOption::PARAMETER_REQUIRED, 'The application name', "backend")
        ));

        $this->namespace        = 's4hana';
        $this->name             = 'generateContracts';
        $this->briefDescription = '';
        $this->detailedDescription = <<<EOF
The [s4hana:generateContracts|INFO] generates integration contract files for S4Hana.
Call it with:

  [php symfony s4hana:generateContracts|INFO]
EOF;
}

    protected function getBuildspaceConnection()
    {
        if(!$this->buildspaceCon)
        {
            $databaseManager = new sfDatabaseManager($this->configuration);

            $this->buildspaceCon = $databaseManager->getDatabase('main_conn')->getConnection();
        }

        return $this->buildspaceCon;
    }

    protected function getEprojectConnection()
    {
        if(!$this->eprojectCon)
        {
            $databaseManager = new sfDatabaseManager($this->configuration);

            $this->eprojectCon = $databaseManager->getDatabase('eproject_conn')->getConnection();
        }

        return $this->eprojectCon;
    }

    protected function execute($arguments = array(), $options = array())
    {
        ini_set('memory_limit','2048M');

        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $mainConn = $databaseManager->getDatabase('main_conn')->getConnection();
        
        $project = null;//ProjectStructureTable::getInstance()->find(5416);//3765);//1275);//7362);//945);

        $this->startTime = microtime(true); 

        $batchNumber = $this->generateContractData($project);

        $this->generateExcel($batchNumber);

        $endTime = microtime(true); 

        $executionTime = ($endTime - $this->startTime); 

        $this->logSection('say', $executionTime.' seconds');

        $this->logSection('say', 'END');
    }

    protected function generateExcel(int $batchNumber)
    {
        $buildspaceConn = $this->getBuildspaceConnection();

        $stmt = $buildspaceConn->prepare("SELECT h.created_at
        FROM bs_contract_headers h
        JOIN bs_project_structures p ON h.project_id = p.id
        WHERE h.batch_number = ".$batchNumber."
        AND p.type = ".ProjectStructure::TYPE_ROOT."
        AND p.deleted_at IS NULL
        ORDER BY p.priority
        LIMIT 1");

        $stmt->execute();

        $createdDate = $stmt->fetch(PDO::FETCH_COLUMN, 0);

        $stmt = $buildspaceConn->prepare("SELECT p.title, h.*
        FROM bs_contract_headers h
        JOIN bs_project_structures p ON h.project_id = p.id
        WHERE h.batch_number = ".$batchNumber."
        AND p.type = ".ProjectStructure::TYPE_ROOT."
        AND p.deleted_at IS NULL
        ORDER BY p.priority");

        $stmt->execute();

        $headers = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if($headers)
        {
            $this->logSection('say', 'Generating header excel file...');

            $spreadsheet = $this->generateHeaderExcel($headers, $batchNumber);

            $filename = "000-".sprintf('%03d', count($headers));
            $tmpFile = $this->write($spreadsheet, 'BUILDSPACE_CTH_'.$filename.'_'.date('YmdHis', strtotime($createdDate)));

            $this->logSection('say', $tmpFile.' generated');

            $this->logSection('say', 'Uploading header file...');

            $this->uploadToEndpoint($tmpFile, sfConfig::get('app_s4hana_contract_header_directory', '/CTH'));
        }

        $stmt = $buildspaceConn->prepare("SELECT p.title, i.*
        FROM bs_contract_items i
        JOIN bs_project_structures p ON i.project_id = p.id
        WHERE i.batch_number = ".$batchNumber."
        AND p.type = ".ProjectStructure::TYPE_ROOT."
        AND p.deleted_at IS NULL
        ORDER BY p.priority");

        $stmt->execute();

        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if($items)
        {
            $this->logSection('say', 'Generating item excel file...');

            $spreadsheet = $this->generateItemExcel($items);

            $filename = "000-".sprintf('%03d', count($items));
            $tmpFile = $this->write($spreadsheet, 'BUILDSPACE_CTI_'.$filename.'_'.date('YmdHis', strtotime($createdDate)));

            $this->logSection('say', $tmpFile.' generated');

            $this->logSection('say', 'Uploading item file...');

            $this->uploadToEndpoint($tmpFile, sfConfig::get('app_s4hana_contract_item_directory', '/CTI'));
        }
    }

    protected function generateHeaderExcel(Array $projects, int $batchNumber)
    {
        $buildspaceConn = $this->getBuildspaceConnection();

        $stmt = $buildspaceConn->prepare("SELECT m.eproject_origin_id, p.id
        FROM bs_project_main_information m
        JOIN bs_project_structures p ON m.project_structure_id = p.id
        JOIN bs_contract_headers h ON h.project_id = p.id
        WHERE h.batch_number = ".$batchNumber."
        AND p.type = ".ProjectStructure::TYPE_ROOT."
        AND p.deleted_at IS NULL AND m.deleted_at IS NULL
        ORDER BY p.priority");

        $stmt->execute();

        $projectIds = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

        $contracts = [];
        if($projectIds)
        {
            $eprojectConn = $this->getEprojectConnection();
            
            $stmt = $eprojectConn->prepare("SELECT c.project_id, c.amount_performance_bond, c.cpc_date,
            c.extension_of_time_date, c.defect_liability_period, c.defect_liability_period_unit,
            c.certificate_of_making_good_defect_date, c.cnc_date, c.liquidate_damages,
            c.performance_bond_validity_date, c.insurance_policy_coverage_date
            FROM projects p
            JOIN pam_2006_project_details c ON p.id = c.project_id
            WHERE p.id IN (".implode(',', array_keys($projectIds)).")
            AND p.deleted_at IS NULL");

            $stmt->execute();

            $contractInfo = $stmt->fetchAll(PDO::FETCH_ASSOC);


            foreach($contractInfo as $info)
            {
                if(array_key_exists($info['project_id'], $projectIds))
                {
                    $contracts[$projectIds[$info['project_id']]] = $info;
                }
            }
        }

        $objPHPExcel = new sfPhpExcel();
        $objPHPExcel->getProperties()->setCreator("Buildspace");
        
        $headers = [
            'A' => "Approved Time Stamp",
            'B' => "Contract Number",
            'C' => "Title",
            'D' => "Business Unit",
            'E' => "Letter of Award No.",
            'F' => "Date of Award",
            'G' => "Work Category",
            'H' => "Selected Contractor",
            'I' => "Commencement Date",
            'J' => "Completion Date",
            'K' => "Currency",
            'L' => "Contract Sum",
            'M' => "Percentage of certified value retained",
            'N' => "Limit Of Retention Fund",
            'O' => "Amount Of Performance Bond",
            'P' => "CPC Date",
            'Q' => "E.O.T Date",
            'R' => "DLP Period",
            'S' => "CMGD Date",
            'T' => "CNC Date",
            'U' => "LD - RM",
            'V' => "Performance Bond Validity Date",
            'W' => "Insurance Policy Coverage Date"
        ];

        $activeSheet = $objPHPExcel->getActiveSheet();

        $activeSheet->setTitle("Headers");
        $activeSheet->setAutoFilter('A1:W1');

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

        foreach(['B', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W'] as $column)
        {
            $activeSheet->getColumnDimension($column)->setAutoSize(false);
            $activeSheet->getColumnDimension($column)->setWidth(32);
        }

        $activeSheet->getColumnDimension('C')->setAutoSize(false);
        $activeSheet->getColumnDimension('C')->setWidth(128);

        foreach(['D', 'E'] as $column)
        {
            $activeSheet->getColumnDimension($column)->setAutoSize(false);
            $activeSheet->getColumnDimension($column)->setWidth(74);
        }

        foreach(['L', 'M', 'N', 'O', 'U'] as $column)
        {
            $activeSheet->getStyle($column)->getNumberFormat()->setFormatCode("#,##0.00");
        }

        $records = [];

        foreach($projects as $idx => $project)
        {
            $publishedDate = ($project['published_date']) ? date('d.m.Y', strtotime($project['published_date'])) : null;
            $dateOfAward = ($project['date_of_award']) ? date('d.m.Y', strtotime($project['date_of_award']))  : null;
            $commencementDate = ($project['commencement_date']) ? date('d.m.Y', strtotime($project['commencement_date'])) : null;
            $completionDate = ($project['completion_date']) ? date('d.m.Y', strtotime($project['completion_date'])) : null;
            $amountPerformanceBond = (array_key_exists($project['project_id'], $contracts) && !empty($contracts[$project['project_id']]['amount_performance_bond'])) ? round($contracts[$project['project_id']]['amount_performance_bond'], 2) : 0;
            $cpcDate = (array_key_exists($project['project_id'], $contracts) && !empty($contracts[$project['project_id']]['cpc_date'])) ? date('d.m.Y', strtotime($contracts[$project['project_id']]['cpc_date'])) : null;
            $extensionOfTimeDate = (array_key_exists($project['project_id'], $contracts) && !empty($contracts[$project['project_id']]['extension_of_time_date'])) ? date('d.m.Y', strtotime($contracts[$project['project_id']]['extension_of_time_date'])) : null;
            
            $dlpPeriod = null;
            $dlpPeriodUnit = null;
            if((array_key_exists($project['project_id'], $contracts) && !empty($contracts[$project['project_id']]['defect_liability_period'])))
            {
                $dlpPeriod = $contracts[$project['project_id']]['defect_liability_period'];
                switch($contracts[$project['project_id']]['defect_liability_period_unit'])
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

            $cmgdDate = (array_key_exists($project['project_id'], $contracts) && !empty($contracts[$project['project_id']]['certificate_of_making_good_defect_date'])) ? date('d.m.Y', strtotime($contracts[$project['project_id']]['certificate_of_making_good_defect_date'])) : null;
            $cncDate = (array_key_exists($project['project_id'], $contracts) && !empty($contracts[$project['project_id']]['cnc_date'])) ? date('d.m.Y', strtotime($contracts[$project['project_id']]['cnc_date'])) : null;
            $liquidateDamages = (array_key_exists($project['project_id'], $contracts) && !empty($contracts[$project['project_id']]['liquidate_damages'])) ? round($contracts[$project['project_id']]['liquidate_damages'], 2) : 0;
            $performanceBondValidityDate = (array_key_exists($project['project_id'], $contracts) && !empty($contracts[$project['project_id']]['performance_bond_validity_date'])) ? date('d.m.Y', strtotime($contracts[$project['project_id']]['performance_bond_validity_date'])) : null;
            $insurancePolicyCoverageDate = (array_key_exists($project['project_id'], $contracts) && !empty($contracts[$project['project_id']]['insurance_policy_coverage_date'])) ? date('d.m.Y', strtotime($contracts[$project['project_id']]['insurance_policy_coverage_date'])) : null;

            $records[] = [
                $publishedDate,
                $project['reference'],
                $project['title'],
                $project['business_unit'],
                $project['letter_of_award_number'],
                $dateOfAward,
                $project['work_category'],
                $project['contractor'],
                $commencementDate,
                $completionDate,
                $project['currency'],
                round($project['contract_sum'], 2),
                round($project['retention'], 2),
                round($project['max_retention_sum'], 2),
                $amountPerformanceBond,
                $cpcDate,
                $extensionOfTimeDate,
                $dlpPeriod." ".$dlpPeriodUnit,
                $cmgdDate,
                $cncDate,
                $liquidateDamages,
                $performanceBondValidityDate,
                $insurancePolicyCoverageDate
            ];

            unset($projects[$idx]);
        }

        $activeSheet->fromArray($records, null, 'A2');

        return $objPHPExcel;
    }

    protected function insertContractHeaderRecords(Array $bsProjectRecords, int $latestBatchNumber, $createdDate)
    {
        if(empty($bsProjectRecords))
        {
            throw new Exception('No project records');
        }

        $eprojectConn = $this->getEprojectConnection();
        $buildspaceConn = $this->getBuildspaceConnection();

        $stmt = $buildspaceConn->prepare("SELECT mi.eproject_origin_id, c.name
            FROM bs_project_structures p
            JOIN bs_project_main_information mi ON mi.project_structure_id = p.id
            JOIN bs_tender_settings ts ON ts.project_structure_id = mi.project_structure_id
            JOIN bs_companies c ON ts.awarded_company_id = c.id
            WHERE p.type = ".ProjectStructure::TYPE_ROOT."
            AND mi.status = ".ProjectMainInformation::STATUS_POSTCONTRACT." AND mi.eproject_origin_id IS NOT NULL
            AND mi.eproject_origin_id IN (".implode(',', array_keys($bsProjectRecords)).")
            AND mi.deleted_at IS NULL AND p.deleted_at IS NULL AND ts.deleted_at IS NULL AND c.deleted_at IS NULL
            GROUP BY mi.eproject_origin_id, c.id");

        $stmt->execute();

        $awardedCompanies = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

        $stmt = $eprojectConn->prepare("SELECT DISTINCT p.id, p.title, p.reference, s.name AS business_unit,
        wc.name AS work_category, p.modified_currency_code AS mod_currency_code, countries.currency_code
        FROM projects p
        JOIN subsidiaries s ON p.subsidiary_id = s.id
        JOIN companies c ON s.company_id = c.id
        JOIN work_categories wc ON p.work_category_id = wc.id
        LEFT JOIN countries ON p.country_id = countries.id
        WHERE p.id IN (".implode(',', array_keys($bsProjectRecords)).")
        AND p.deleted_at IS NULL");

        $stmt->execute();

        $eprojects = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        self::arrayBatch($eprojects, 500, function($batch)use($buildspaceConn, $bsProjectRecords, $awardedCompanies, $latestBatchNumber, $createdDate) {
            $insertRecords = [];
            $questionMarks = [];
            $fields        = [];

            foreach($batch as $eproject)
            {
                $currencyCode = ($eproject['mod_currency_code']) ? $eproject['mod_currency_code'] : $eproject['currency_code'];

                $record = [
                    'batch_number'           => $latestBatchNumber,
                    'project_id'             => $bsProjectRecords[$eproject['id']]['id'],
                    'reference'              => trim($eproject['reference']),
                    'date_of_award'          => (array_key_exists($eproject['id'], $bsProjectRecords)) ? date('Y-m-d', strtotime($bsProjectRecords[$eproject['id']]['awarded_date'])) : null,
                    'commencement_date'      => (array_key_exists($eproject['id'], $bsProjectRecords)) ? date('Y-m-d', strtotime($bsProjectRecords[$eproject['id']]['contract_period_from'])) : null,
                    'completion_date'        => (array_key_exists($eproject['id'], $bsProjectRecords)) ? date('Y-m-d', strtotime($bsProjectRecords[$eproject['id']]['contract_period_to'])) : null,
                    'published_date'         => (array_key_exists($eproject['id'], $bsProjectRecords)) ? date('Y-m-d', strtotime($bsProjectRecords[$eproject['id']]['published_at'])) : null,
                    'max_retention_sum'      => (array_key_exists($eproject['id'], $bsProjectRecords)) ? $bsProjectRecords[$eproject['id']]['max_retention_sum'] : 0,
                    'retention'              => (array_key_exists($eproject['id'], $bsProjectRecords)) ? $bsProjectRecords[$eproject['id']]['retention'] : 0,
                    'letter_of_award_number' => (array_key_exists($eproject['id'], $bsProjectRecords)) ? $bsProjectRecords[$eproject['id']]['reference'] : null,
                    'business_unit'          => mb_strtoupper(trim($eproject['business_unit'])),
                    'work_category'          => trim($eproject['work_category']),
                    'contractor'             => (array_key_exists($eproject['id'], $awardedCompanies)) ? trim($awardedCompanies[$eproject['id']]) : null,
                    'currency'               => $currencyCode,
                    'contract_sum'           => (array_key_exists($eproject['id'], $bsProjectRecords)) ? $bsProjectRecords[$eproject['id']]['contract_sum'] : 0,
                    'created_at'             => $createdDate
                ];

                $fields = array_keys($record);

                $insertRecords = array_merge($insertRecords, array_values($record));
                $questionMarks[] = '('.implode(',', array_fill(0, count($record), '?')).')';
            }

            if($insertRecords)
            {
                try
                {
                    $this->logSection('Contract Headers', 'Inserting records...');

                    $buildspaceConn->beginTransaction();
                    
                    $stmt = $buildspaceConn->prepare("INSERT INTO bs_contract_headers
                    (".implode(',', $fields).")
                    VALUES " . implode(',', $questionMarks));

                    $stmt->execute($insertRecords);
                    
                    $buildspaceConn->commit();

                    $this->logSection('Contract Headers', 'Successfully inserted items!');
                }
                catch(Exception $e)
                {
                    $buildspaceConn->rollBack();

                    throw $e;
                }

                unset($insertRecords);
            }
        });

        $stmt = $eprojectConn->prepare("SELECT DISTINCT p.id, p.reference
        FROM projects p
        WHERE p.id IN (".implode(',', array_keys($bsProjectRecords)).")
        AND p.deleted_at IS NULL");

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    }

    protected function generateContractData(ProjectStructure $project=null)//Array $projects, ProjectStructure $project=null)
    {
        $this->logSection('say', 'preparing header and item data');

        $buildspaceConn = $this->getBuildspaceConnection();
        $eprojectConn = $this->getEprojectConnection();

        $sql  = ($project) ? " AND p.id = ".$project->id." " : "";

        $stmt = $buildspaceConn->prepare("SELECT mi.eproject_origin_id, c.id, cl.created_at
        FROM bs_project_structures p
        JOIN bs_project_main_information mi ON mi.project_structure_id = p.id
        JOIN bs_new_post_contract_form_information fi ON fi.project_structure_id = mi.project_structure_id
        JOIN bs_post_contracts pc ON fi.project_structure_id = pc.project_structure_id
        JOIN bs_post_contract_claim_revisions cr ON pc.id = cr.post_contract_id
        JOIN (SELECT bs_post_contract_claim_revisions.post_contract_id, MAX(version) AS version
        FROM bs_post_contract_claim_revisions
        JOIN bs_claim_certificates ON bs_post_contract_claim_revisions.id = bs_claim_certificates.post_contract_claim_revision_id
        JOIN bs_post_contracts ON bs_post_contract_claim_revisions.post_contract_id = bs_post_contracts.id
        JOIN bs_project_main_information ON bs_project_main_information.project_structure_id = bs_post_contracts.project_structure_id
        WHERE bs_project_main_information.eproject_origin_id IS NOT NULL
        AND bs_post_contract_claim_revisions.locked_status IS TRUE
        AND bs_post_contract_claim_revisions.deleted_at IS NULL
        GROUP BY bs_post_contract_claim_revisions.post_contract_id) b ON b.post_contract_id = cr.post_contract_id AND cr.version <= b.version
        JOIN bs_claim_certificates c ON cr.id = c.post_contract_claim_revision_id
        JOIN bs_claim_certificate_approval_logs cl ON c.id = cl.claim_certificate_id
        WHERE p.type = ".ProjectStructure::TYPE_ROOT."
        ".$sql."
        AND c.status = ".ClaimCertificate::STATUS_TYPE_APPROVED." AND cr.locked_status IS TRUE
        AND NOT EXISTS (
            SELECT iecc.claim_certificate_id FROM bs_integration_exported_claim_certificates iecc
            WHERE iecc.claim_certificate_id = c.id
        )
        AND cl.status = ".ClaimCertificate::STATUS_TYPE_APPROVED."
        AND mi.status = ".ProjectMainInformation::STATUS_POSTCONTRACT."
        AND mi.eproject_origin_id IS NOT NULL
        AND mi.deleted_at IS NULL AND p.deleted_at IS NULL AND cr.deleted_at IS NULL
        ORDER BY pc.id, c.updated_at, cr.version DESC");

        $stmt->execute();

        $claimCertificates = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $latestClaimCertificateLogs = [];
        $projectClaimCertificates = [];

        foreach($claimCertificates as $claimCertificate)
        {
            $latestClaimCertificateLogs[$claimCertificate['id']] = date('Y-m-d H:i:s', strtotime($claimCertificate['created_at']));
            $projectClaimCertificates[$claimCertificate['eproject_origin_id']] = $claimCertificate['id'];
        }

        unset($claimCertificates);

        $bsProjectRecords = [];
        $notInClaimCertSql = "";

        if(!empty($projectClaimCertificates))
        {
            //get project id from claim
            //get proejct id from latest claim with vo
            //get prject without claim does not exists in contract header table

            $projectSql  = ($project) ? " AND p.id = ".$project->id." " : " AND mi.eproject_origin_id IN (".implode(',', array_keys($projectClaimCertificates)).") ";

            $stmt = $buildspaceConn->prepare("SELECT p.id, mi.eproject_origin_id, fi.awarded_date, fi.contract_period_from,
            fi.contract_period_to, pc.published_at, fi.retention, fi.max_retention_sum,
            fi.reference, SUM(r.grand_total) AS contract_sum
            FROM bs_post_contract_bill_item_rates r
            JOIN bs_post_contracts pc ON pc.id = r.post_contract_id
            JOIN bs_new_post_contract_form_information fi ON fi.project_structure_id = pc.project_structure_id
            JOIN bs_project_main_information mi ON mi.project_structure_id = fi.project_structure_id
            JOIN bs_project_structures p ON mi.project_structure_id = p.id
            WHERE p.type = ".ProjectStructure::TYPE_ROOT."
            AND mi.status = ".ProjectMainInformation::STATUS_POSTCONTRACT." AND mi.eproject_origin_id IS NOT NULL
            ".$projectSql."
            AND mi.deleted_at IS NULL AND p.deleted_at IS NULL
            GROUP BY p.id, mi.id, pc.id, fi.id");

            $stmt->execute();

            $projectsWithClaims = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $records = [];

            $claimCertCollection = Doctrine_Query::create()
            ->from('ClaimCertificate c')
            ->whereIn('c.id', array_keys($latestClaimCertificateLogs))
            ->execute();

            foreach($claimCertCollection as $record)
            {
                $records[] = $record;
            }

            unset($claimCertCollection);

            $claimCertificates = ClaimCertificateTable::getClaimCertInfo($records, false, true);

            unset($records);

            foreach($projectsWithClaims as $idx => $projectWithClaim)
            {
                $bsProjectRecords[$projectWithClaim['eproject_origin_id']] = $projectWithClaim;

                foreach($claimCertificates as $claimCertId => $claimCert)
                {
                    if($claimCert['projectId'] == $projectWithClaim['id'])
                    {
                        $bsProjectRecords[$projectWithClaim['eproject_origin_id']]['contract_sum'] = $claimCert['contractSum'];

                        unset($claimCertificates[$claimCertId]);

                    }
                }
            }

            $notInClaimCertSql = " AND mi.eproject_origin_id NOT IN (".implode(',', array_keys($projectClaimCertificates)).") ";

            unset($projectsWithClaims, $claimCertificates);
        }
        
        $projectWhereSql = ($project) ? " AND p.id = ".$project->id." " : "";

        //post contract projects that are not in contract headers table and do not have any claims
        $stmt = $buildspaceConn->prepare("SELECT p.id, mi.eproject_origin_id, fi.awarded_date, fi.contract_period_from,
        fi.contract_period_to, pc.published_at, fi.retention, fi.max_retention_sum,
        fi.reference, SUM(r.grand_total) AS contract_sum
        FROM bs_post_contract_bill_item_rates r
        JOIN bs_post_contracts pc ON pc.id = r.post_contract_id
        JOIN bs_new_post_contract_form_information fi ON fi.project_structure_id = pc.project_structure_id
        JOIN bs_project_main_information mi ON mi.project_structure_id = fi.project_structure_id
        JOIN bs_project_structures p ON mi.project_structure_id = p.id
        WHERE p.type = ".ProjectStructure::TYPE_ROOT."
        AND mi.status = ".ProjectMainInformation::STATUS_POSTCONTRACT." AND mi.eproject_origin_id IS NOT NULL
        ".$notInClaimCertSql."
        ".$projectWhereSql."
        AND NOT EXISTS (
            SELECT FROM bs_contract_headers ch
            WHERE ch.project_id = p.id
        )
        AND mi.deleted_at IS NULL AND p.deleted_at IS NULL
        GROUP BY p.id, mi.id, pc.id, fi.id");

        $stmt->execute();

        $newPostContractProjects = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach($newPostContractProjects as $newPostContractProject)
        {
            $bsProjectRecords[$newPostContractProject['eproject_origin_id']] = $newPostContractProject;
        }

        unset($newPostContractProjects);

        $bills = [];

        $stmt = $buildspaceConn->prepare("SELECT COALESCE(MAX(batch_number), 0) AS batch_number FROM bs_contract_items");
        
        $stmt->execute();

        $latestBatchNumber = $stmt->fetch(PDO::FETCH_COLUMN, 0);

        $latestBatchNumber += 1;

        $createdDate = date('Y-m-d H:i:s');

        $eprojectContractNumbers = [];

        if(!empty($bsProjectRecords))
        {
            try
            {
                $eprojectContractNumbers = $this->insertContractHeaderRecords($bsProjectRecords, $latestBatchNumber, $createdDate);
            }
            catch(Exception $e)
            {
                return $this->logSection('Contract Headers', $e->getMessage());
            }

            $stmt = $buildspaceConn->prepare("SELECT b.id, b.title, bt.type AS bill_type, mi.eproject_origin_id, p.id AS project_id, ROUND(COALESCE(SUM(r.grand_total),0),2) AS grand_total
            FROM bs_project_structures p
            JOIN bs_project_main_information mi ON mi.project_structure_id = p.id
            JOIN bs_post_contracts pc ON pc.project_structure_id = mi.project_structure_id
            JOIN bs_post_contract_bill_item_rates r ON r.post_contract_id = pc.id
            JOIN bs_bill_items i ON r.bill_item_id = i.id
            JOIN bs_bill_elements e ON i.element_id = e.id
            JOIN bs_project_structures b ON e.project_structure_id = b.id AND b.root_id = p.id
            JOIN bs_bill_types bt ON bt.project_structure_id = b.id
            WHERE b.type = ".ProjectStructure::TYPE_BILL." AND p.type = ".ProjectStructure::TYPE_ROOT."
            AND mi.eproject_origin_id IS NOT NULL
            AND mi.status = ".ProjectMainInformation::STATUS_POSTCONTRACT."
            AND mi.eproject_origin_id IN (".implode(',', array_keys($bsProjectRecords)).")
            AND mi.deleted_at IS NULL AND p.deleted_at IS NULL
            AND i.deleted_at IS NULL AND i.project_revision_deleted_at IS NULL
            AND e.deleted_at IS NULL AND b.deleted_at IS NULL AND bt.deleted_at IS NULL
            GROUP BY p.id, mi.id, b.id, bt.id
            ORDER BY p.priority, b.priority, b.lft, b.level");

            $stmt->execute();

            $bills = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        
        $standardClaimBills = [];
        $premliminaryClaimBills = [];
        $variationOrders = [];
        $voClaimCertificateLogs = [];
        $voProjectClaimCertificates = [];

        if(!empty($latestClaimCertificateLogs))
        {
            $standardClaimBills = ClaimCertificateTable::getLatestStandardBillClaimsByClaimCertificateIds(array_keys($latestClaimCertificateLogs), $project);
            $premliminaryClaimBills = ClaimCertificateTable::getLatestPreliminaryBillClaimsByClaimCertificateIds(array_keys($latestClaimCertificateLogs), $project);
            $variationOrders = ClaimCertificateTable::getLatestVariationOrdersByClaimCertificateIds(array_keys($latestClaimCertificateLogs), $project);
        }

        $records = [];

        $removeBillItems = [];

        foreach($bills as $idx => $bill)
        {
            $claimCertificateId = array_key_exists($bill['eproject_origin_id'], $projectClaimCertificates) ? $projectClaimCertificates[$bill['eproject_origin_id']] : null;

            $projectId = $bsProjectRecords[$bill['eproject_origin_id']]['id'];

            $record = [
                'batch_number'           => $latestBatchNumber,
                'project_id'             => $projectId,
                'approved_date'          => ($claimCertificateId && array_key_exists($claimCertificateId, $latestClaimCertificateLogs)) ? $latestClaimCertificateLogs[$claimCertificateId] : null,
                'contract_number'        => array_key_exists($bill['eproject_origin_id'], $eprojectContractNumbers) ? $eprojectContractNumbers[$bill['eproject_origin_id']] : '',
                'bill_id'                => $bill['id'],
                'bill_title'             => $bill['title'],
                'type'                   => 'BILL',
                'reference_amount'       => 0,
                'total'                  => $bill['grand_total'],
                'nett_omission_addition' => 0,
                'previous_claim'         => 0,
                'current_claim'          => 0,
                'up_to_date_claim'       => 0,
                'created_at'             => $createdDate
            ];
            
            if($bill['bill_type'] != BillType::TYPE_PRELIMINARY && array_key_exists($bill['id'], $standardClaimBills))
            {
                $record['previous_claim']   = $standardClaimBills[$bill['id']]['previous_amount'];
                $record['current_claim']    = $standardClaimBills[$bill['id']]['current_amount'];
                $record['up_to_date_claim'] = $standardClaimBills[$bill['id']]['up_to_date_amount'];

                unset($standardClaimBills[$bill['id']]);
            }

            if($bill['bill_type'] == BillType::TYPE_PRELIMINARY && array_key_exists($bill['id'], $premliminaryClaimBills))
            {
                $record['previous_claim']   = $premliminaryClaimBills[$bill['id']]['up_to_date_amount'] - $premliminaryClaimBills[$bill['id']]['current_amount'];
                $record['current_claim']    = $premliminaryClaimBills[$bill['id']]['current_amount'];
                $record['up_to_date_claim'] = $premliminaryClaimBills[$bill['id']]['up_to_date_amount'];

                unset($premliminaryClaimBills[$bill['id']]);
            }

            $records[] = $record;

            if((array_key_exists($projectId, $variationOrders)) && (!array_key_exists($idx+1, $bills) or (array_key_exists($idx+1, $bills) && $bills[$idx+1]['eproject_origin_id'] != $bill['eproject_origin_id'])))
            {
                foreach($variationOrders[$projectId] as $variationOrderId => $variationOrder)
                {
                    $records[] = [
                        'batch_number'           => $latestBatchNumber,
                        'project_id'             => $projectId,
                        'approved_date'          => $variationOrder['created_at'],
                        'contract_number'        => array_key_exists($bill['eproject_origin_id'], $eprojectContractNumbers) ? $eprojectContractNumbers[$bill['eproject_origin_id']] : '',
                        'bill_id'                => $variationOrder['bill_id'],
                        'bill_title'             => $variationOrder['bill_title'],
                        'type'                   => 'VO',
                        'reference_amount'       => $variationOrder['reference_amount'],
                        'total'                  => $variationOrder['nett_omission_addition'],
                        'nett_omission_addition' => $variationOrder['nett_omission_addition'],
                        'previous_claim'         => $variationOrder['previous_amount'],
                        'current_claim'          => $variationOrder['current_amount'],
                        'up_to_date_claim'       => $variationOrder['up_to_date_amount'],
                        'created_at'             => $createdDate
                    ];

                    unset($variationOrders[$projectId][$variationOrderId]);
                }
            }
        }

        unset($bills);

        $latestClaimCertificateIds = array_unique(array_merge(array_keys($latestClaimCertificateLogs), array_keys($voClaimCertificateLogs)));

        $claimCertificateIds = [];

        if(!empty($latestClaimCertificateIds))
        {
            $stmt = $buildspaceConn->prepare("SELECT c.id
            FROM bs_claim_certificates c
            JOIN bs_post_contract_claim_revisions cr ON cr.id = c.post_contract_claim_revision_id
            JOIN bs_post_contract_claim_revisions cr1 ON cr1.post_contract_id = cr.post_contract_id
            JOIN bs_claim_certificates c1 ON c1.post_contract_claim_revision_id = cr1.id
            WHERE c1.id IN (".implode(',', $latestClaimCertificateIds).")
            AND NOT EXISTS (
                SELECT iecc.claim_certificate_id
                FROM bs_integration_exported_claim_certificates iecc
                WHERE iecc.claim_certificate_id = c.id
                AND iecc.batch_number = ".$latestBatchNumber."
            )
            AND cr.version <= cr1.version
            AND c.status = ".ClaimCertificate::STATUS_TYPE_APPROVED." AND cr.locked_status IS TRUE
            AND cr.deleted_at IS NULL
            GROUP BY c.id, cr.id
            ORDER BY cr.post_contract_id, c.updated_at, cr.version DESC");
            
            $stmt->execute();
            
            $claimCertificateIds = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
        }

        self::arrayBatch($claimCertificateIds, 500, function($batch)use($buildspaceConn, $latestBatchNumber, $createdDate) {
            $insertRecords = [];
            $questionMarks = [];

            foreach($batch as $claimCertificateId)
            {
                $record = [
                    $latestBatchNumber,
                    $claimCertificateId,
                    $createdDate
                ];

                $insertRecords = array_merge($insertRecords, $record);
                $questionMarks[] = '('.implode(',', array_fill(0, count($record), '?')).')';
            }

            if($insertRecords)
            {
                try
                {
                    $this->logSection('Contract Items', 'Inserting claim certificates records...');

                    $buildspaceConn->beginTransaction();
                    
                    $stmt = $buildspaceConn->prepare("INSERT INTO bs_integration_exported_claim_certificates
                    (batch_number, claim_certificate_id, created_at)
                    VALUES " . implode(',', $questionMarks));

                    $stmt->execute($insertRecords);
                    
                    $buildspaceConn->commit();

                    $this->logSection('Contract Items', 'Successfully inserted  claim certificates items!');
                }
                catch(Exception $e)
                {
                    $buildspaceConn->rollBack();

                    return $this->logSection('Contract Items', $e->getMessage());
                }

                    unset($insertRecords);
            }
        });

        self::arrayBatch($records, 500, function($batch)use($buildspaceConn) {
            $insertRecords = [];
            $questionMarks = [];

            foreach($batch as $record)
            {
                $insertRecords = array_merge($insertRecords, array_values($record));
                $questionMarks[] = '('.implode(',', array_fill(0, count($record), '?')).')';
            }

            if($insertRecords)
            {
                try
                {
                    $this->logSection('Contract Items', 'Inserting records...');

                    $buildspaceConn->beginTransaction();
                    
                    $stmt = $buildspaceConn->prepare("INSERT INTO bs_contract_items
                    (batch_number, project_id, approved_date, contract_number, item_id, item_title, item_type, reference_amount, total, nett_omission_addition, previous_claim, current_claim, up_to_date_claim, created_at)
                    VALUES " . implode(',', $questionMarks));

                    $stmt->execute($insertRecords);
                    
                    $buildspaceConn->commit();

                    $this->logSection('Contract Items', 'Successfully inserted items!');
                }
                catch(Exception $e)
                {
                    $buildspaceConn->rollBack();

                    return $this->logSection('Contract Items', $e->getMessage());
                }

                    unset($insertRecords);
            }
        });

        return $latestBatchNumber;
    }

    protected function generateItemExcel(Array $items)
    {
        $objPHPExcel = new sfPhpExcel();
        $objPHPExcel->getProperties()->setCreator("Buildspace");
        
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

        $activeSheet = $objPHPExcel->getActiveSheet();

        $activeSheet->setTitle("Items");
        $activeSheet->setAutoFilter('A1:L1');

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
        $activeSheet->getColumnDimension('C')->setWidth(12);

        $activeSheet->getColumnDimension('E')->setAutoSize(false);
        $activeSheet->getColumnDimension('E')->setWidth(128);

        foreach(['B', 'D', 'G', 'H', 'I', 'J', 'K', 'L'] as $column)
        {
            $activeSheet->getColumnDimension($column)->setAutoSize(false);
            $activeSheet->getColumnDimension($column)->setWidth(32);
        }

        foreach(['G', 'H', 'I', 'J', 'K', 'L'] as $column)
        {
            $activeSheet->getStyle($column)->getNumberFormat()->setFormatCode("#,##0.00");
        }

        $records = [];

        foreach($items as $idx => $item)
        {
            $approvedDate = ($item['approved_date']) ? date('d.m.Y', strtotime($item['approved_date'])) : null;
            
            $records[] = [
                $approvedDate,
                $item['contract_number'],
                $item['item_type'],
                $item['item_id'],
                $item['item_title'],
                'APPROVED',
                round($item['reference_amount'], 2),
                round($item['total'], 2),
                round($item['nett_omission_addition'], 2),
                round($item['previous_claim'], 2),
                round($item['current_claim'], 2),
                round($item['up_to_date_claim'], 2)
            ];

            unset($items[$idx]);
        }

        $activeSheet->fromArray($records, null, 'A2');

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

        $contractsDir = sfConfig::get('sf_data_dir') . DIRECTORY_SEPARATOR . 's4hana' . DIRECTORY_SEPARATOR . 'contracts';
        if(!is_dir($contractsDir) )
        {
            mkdir($contractsDir, 0777, true);
        }

        $tmpFile = $contractsDir.DIRECTORY_SEPARATOR.$tmpName;

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
