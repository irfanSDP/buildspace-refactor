<?php

class sfVariationOrderExcelReportGenerator extends sfBuildspaceExcelReportGenerator {

    public $colNo                             = "B";
    public $colRequestForVariationNumber      = "C";
    public $colRequestForVariationDescription = "D";
    public $colRequestForVariationAmount      = "E";
    public $colVariationOrderAmount           = "F";
    public $colDifference                     = "G";
    public $colPendingApprovalVoAmount        = "H";
    public $colVariationOrderClaimAmount      = "I";
    public $colVariationOrderDescription      = "J";
    public $colRemarks                        = "K";

    const VARIATION_ORDER_REPORT                = 'Variation Order Report';
    const COL_REQUEST_FOR_VARIATION_NUMBER      = 'RFV No.';
    const COL_REQUEST_FOR_VARIATION_DESCRIPTION = 'RFV Description';
    const COL_REQUEST_FOR_VARIATION_AMOUNT      = 'RFV Amount';
    const COL_VARIATION_ORDER_CLAIM_AMOUNT      = 'VO Claim Amount';
    const COL_DIFFERENCE                        = 'Difference';
    const COL_VARIATION_ORDER_AMOUNT            = 'Approved VO Amount';
    const COL_PENDING_APPROVAL_VO_AMOUNT        = 'Pending VO Approval Amount';
    const COL_VARIATION_ORDER_DESCRIPTION       = 'VO Description';
    const COL_REMARKS                           = 'Remarks';
    const TOTAL                                 = 'Total';

    protected $currencyCode;
    protected $variationOrders                = array();
    protected $totalRequestForVariationAmount = 0;
    protected $totalVariationOrderAmount      = 0;
    protected $totalVariationOrderClaimAmount = 0;
    protected $totalDifference                = 0;
    protected $totalVoPendingAmount           = 0;

    public $itemCount = 0;

    function __construct($project = null, $rfvIds = null, $savePath = null, $filename = null)
    {
        $filename = ( $filename ) ? $filename : self::VARIATION_ORDER_REPORT. " - " . $project->title . '-' . date('dmY H_i_s');

        $savePath = ( $savePath ) ? $savePath : sfConfig::get('sf_web_dir') . DIRECTORY_SEPARATOR . 'uploads';

        $this->pdo = ProjectStructureTable::getInstance()->getConnection()->getDbh();

        $this->project = $project;

        $this->currencyCode = $this->project->MainInformation->Currency->currency_code;

        $this->startBillCounter();

        $this->variationOrders = $this->getVariationOrders($rfvIds);

        parent::__construct($project, $savePath, $filename, array());
    }

    protected function getVariationOrders($rfvIds)
    {
        $bsTableFormat = Doctrine_Manager::getInstance()->getConnectionForComponent('VariationOrder')->getAttribute(Doctrine_Core::ATTR_TBLNAME_FORMAT);

        $records = [];
        $requestForVariations = [];

        if( $this->project->MainInformation->eproject_origin_id )
        {
            $eprojectPdo = Doctrine_Manager::getInstance()->getConnection('eproject_conn')->getDbh();
            Doctrine_Manager::getInstance()->getConnectionForComponent('EProjectRequestForVariation')->setAttribute(Doctrine_Core::ATTR_TBLNAME_FORMAT, '%s');

            $rfvQuery = "SELECT r.id, r.description as rfv_description, r.rfv_number, r.request_for_variation_user_permission_group_id, r.nett_omission_addition 
                            FROM " . EProjectRequestForVariationTable::getInstance()->getTableName() . " r
                            WHERE r.project_id = {$this->project->MainInformation->eproject_origin_id} 
                            AND r.status = " . EProjectRequestForVariation::STATUS_APPROVED . "
                            AND r.id IN (" . $rfvIds . ") 
                            ORDER BY r.created_at DESC";

            $stmt = $eprojectPdo->prepare($rfvQuery);

            $stmt->execute();

            $requestForVariations = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $requestForVariations = Utilities::setAttributeAsKey($requestForVariations, 'id');
            $rfvIds = array_keys($requestForVariations);
        }

        Doctrine_Manager::getInstance()->getConnectionForComponent('VariationOrder')->setAttribute(Doctrine_Core::ATTR_TBLNAME_FORMAT, $bsTableFormat);

        if(!empty($requestForVariations))
        {
            // get approved and pending for verification VOs by approved RFVs
            $stmt = $this->pdo->prepare("SELECT vo.id, vo.description, vo.eproject_rfv_id, SUM(i.reference_amount) as reference_amount, vo.status
                FROM " . VariationOrderTable::getInstance()->getTableName() . " vo
                LEFT JOIN " . VariationOrderClaimCertificateTable::getInstance()->getTableName() . " x ON vo.id = x.variation_order_id
                LEFT JOIN " . VariationOrderItemTable::getInstance()->getTableName() . " i ON vo.id = i.variation_order_id
                WHERE vo.project_structure_id = {$this->project->id}
                AND vo.deleted_at IS NULL AND i.deleted_at IS NULL
                AND vo.eproject_rfv_id IN (" . implode(',', $rfvIds) . ")
                AND vo.status IN (" . VariationOrder::STATUS_APPROVED . ", " . VariationOrder::STATUS_PENDING . ")
                GROUP BY vo.id, x.claim_certificate_id ORDER BY vo.priority ASC");

            $stmt->execute();

            $rfvVo = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $rfvVo = Utilities::setAttributeAsKey($rfvVo, 'eproject_rfv_id');

            foreach($requestForVariations as $key => $rfv)
            {
                $rfvVoIsApproved = array_key_exists($key, $rfvVo);

                array_push($records, [
                    'rfv_id'                 => $key,
                    'rfv_number'             => $rfv['rfv_number'],
                    'rfv_description'        => $rfv['rfv_description'],
                    'vo_id'                  => $rfvVoIsApproved ? $rfvVo[$key]['id'] : null,
                    'vo_description'         => $rfvVoIsApproved ? $rfvVo[$key]['description'] : null,
                    'vo_reference_amount'    => $rfv['nett_omission_addition'],
                    'addition'               => 0.0,
                    'omission'               => 0.0,
                    'nett_omission_addition' => $rfvVoIsApproved ? $rfvVo[$key]['reference_amount'] : 0.0,
                    'total_claim'            => 0.0,
                    'status'                 => $rfvVoIsApproved ? $rfvVo[$key]['status'] : null,
                    'pending_amount'         => null,
                ]);
            }
        }

        // approved and pending for verification manual VOs
        $stmt = $this->pdo->prepare("SELECT vo.id, vo.description, vo.eproject_rfv_id, SUM(i.reference_amount) as reference_amount, vo.status
            FROM " . VariationOrderTable::getInstance()->getTableName() . " vo
            LEFT JOIN " . VariationOrderClaimCertificateTable::getInstance()->getTableName() . " x ON vo.id = x.variation_order_id
            LEFT JOIN " . VariationOrderItemTable::getInstance()->getTableName() . " i ON vo.id = i.variation_order_id
            WHERE vo.project_structure_id = {$this->project->id}
            AND vo.deleted_at IS NULL AND i.deleted_at IS NULL
            AND vo.eproject_rfv_id IS NULL
            AND vo.status IN (" . VariationOrder::STATUS_APPROVED . ", " . VariationOrder::STATUS_PENDING . ")
            GROUP BY vo.id, x.claim_certificate_id ORDER BY vo.priority ASC");

        $stmt->execute();

        $manualVo = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach($manualVo as $mvo)
        {
            array_push($records, [
                'rfv_number'             => null,
                'rfv_id'                 => null,
                'rfv_description'        => null,
                'vo_id'                  => $mvo['id'],
                'vo_description'         => $mvo['description'],
                'vo_reference_amount'    => $mvo['reference_amount'],
                'addition'               => 0.0,
                'omission'               => 0.0,
                'nett_omission_addition' => 0.0,
                'total_claim'            => 0.0,
                'status'                 => $mvo['status'],
                'pending_amount'         => null,
            ]);
        }

        // omission and addition amount
        $stmt = $this->pdo->prepare("SELECT i.variation_order_id, ROUND(COALESCE(SUM(i.total_unit * i.omission_quantity * i.rate), 0), 2) AS omission,
        ROUND(COALESCE(SUM(i.total_unit * i.addition_quantity * i.rate), 0), 2) AS addition,
        ROUND(COALESCE(SUM((i.total_unit * i.addition_quantity * i.rate) - (i.total_unit * i.omission_quantity * i.rate))), 2) AS nett_omission_addition
        FROM " . VariationOrderItemTable::getInstance()->getTableName() . " i
        JOIN " . VariationOrderTable::getInstance()->getTableName() . " vo ON i.variation_order_id = vo.id
        WHERE vo.project_structure_id = " . $this->project->id . " AND i.type <> " . VariationOrderItem::TYPE_HEADER . " AND i.rate <> 0
        AND vo.deleted_at IS NULL AND i.deleted_at IS NULL GROUP BY i.variation_order_id");

        $stmt->execute();

        $quantities = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $quantities = Utilities::setAttributeAsKey($quantities, 'variation_order_id');

        foreach($records as $key => $record)
        {
            if($record['vo_id'])
            {
                $hasQuantity = isset($quantities[$record['vo_id']]);
                $isPendingVO = ($record['status'] == VariationOrder::STATUS_PENDING);

                $records[$key]['omission'] = $hasQuantity ? $quantities[$record['vo_id']]['omission'] : 0.0;
                $records[$key]['addition'] = $hasQuantity ? $quantities[$record['vo_id']]['addition'] : 0.0;
                $records[$key]['nett_omission_addition'] = $isPendingVO ? 0.0 : ($hasQuantity ? $quantities[$record['vo_id']]['nett_omission_addition'] : 0.0);
                $records[$key]['pending_amount'] = ($hasQuantity && $isPendingVO) ? $quantities[$record['vo_id']]['nett_omission_addition'] : 0.0;
            }
        }

        // up to date VO claims
        $stmt = $this->pdo->prepare("SELECT vo.id AS variation_order_id, ROUND(COALESCE(SUM(i.up_to_date_amount), 0), 2) AS amount
                FROM " . VariationOrderTable::getInstance()->getTableName() . " vo
                JOIN " . VariationOrderClaimTable::getInstance()->getTableName() . " c ON c.variation_order_id = vo.id
                JOIN " . VariationOrderClaimItemTable::getInstance()->getTableName() . " i ON i.variation_order_claim_id = c.id
                LEFT JOIN " . VariationOrderClaimClaimCertificateTable::getInstance()->getTableName() . " xref ON xref.variation_order_claim_id = c.id
                LEFT JOIN " . ClaimCertificateTable::getInstance()->getTableName() . " cert ON cert.id = xref.claim_certificate_id
                LEFT JOIN " . PostContractClaimRevisionTable::getInstance()->getTableName() . " rev ON rev.id = cert.post_contract_claim_revision_id
                WHERE vo.project_structure_id = " . $this->project->id . "
                AND c.is_viewing IS TRUE
                AND vo.deleted_at IS NULL AND c.deleted_at IS NULL AND i.deleted_at IS NULL GROUP BY vo.id");

        $stmt->execute();

        $upToDateClaims = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $upToDateClaims = Utilities::setAttributeAsKey($upToDateClaims, 'variation_order_id');

        foreach($records as $key => $record)
        {
            if($record['vo_id'])
            {
                $hasUpToDateClaim = isset($upToDateClaims[$record['vo_id']]);

                $records[$key]['total_claim'] = $hasUpToDateClaim ? $upToDateClaims[$record['vo_id']]['amount'] : 0.0;
            }
        }

        return $records;
    }

    /**
     * Starts the bill counter.
     * This sets the first row and currentRow to the starting.
     * Also determines the first and last column.
     */
    public function startBillCounter()
    {
        $this->currentRow = $this->startRow;
        $this->firstCol   = $this->colNo;
        $this->lastCol    = $this->colRemarks;
    }

    protected function setColumnDimensions()
    {
        $this->activeSheet->getColumnDimension("A")->setWidth(1.3);
        $this->activeSheet->getColumnDimension($this->colNo)->setWidth(7.5);
        $this->activeSheet->getColumnDimension($this->colRequestForVariationNumber)->setWidth(10);
        $this->activeSheet->getColumnDimension($this->colRequestForVariationDescription)->setWidth(45);
        $this->activeSheet->getColumnDimension($this->colRequestForVariationAmount)->setWidth(20);
        $this->activeSheet->getColumnDimension($this->colVariationOrderAmount)->setWidth(30);
        $this->activeSheet->getColumnDimension($this->colDifference)->setWidth(20);
        $this->activeSheet->getColumnDimension($this->colPendingApprovalVoAmount)->setWidth(40);
        $this->activeSheet->getColumnDimension($this->colVariationOrderClaimAmount)->setWidth(30);
        $this->activeSheet->getColumnDimension($this->colVariationOrderDescription)->setWidth(45);
        $this->activeSheet->getColumnDimension($this->colRemarks)->setWidth(20);
    }

    public function createHeader($new = false)
    {
        $this->currentRow++;

        $this->activeSheet->setCellValue($this->colNo . $this->currentRow, self::COL_NAME_NO);
        $this->activeSheet->setCellValue($this->colRequestForVariationNumber . $this->currentRow, self::COL_REQUEST_FOR_VARIATION_NUMBER);
        $this->activeSheet->setCellValue($this->colRequestForVariationDescription . $this->currentRow, self::COL_REQUEST_FOR_VARIATION_DESCRIPTION);
        $this->activeSheet->setCellValue($this->colRequestForVariationAmount . $this->currentRow, self::COL_REQUEST_FOR_VARIATION_AMOUNT . " ({$this->currencyCode})");
        $this->activeSheet->setCellValue($this->colVariationOrderAmount . $this->currentRow, self::COL_VARIATION_ORDER_AMOUNT . " ({$this->currencyCode})");
        $this->activeSheet->setCellValue($this->colDifference . $this->currentRow, self::COL_DIFFERENCE . " ({$this->currencyCode})");
        $this->activeSheet->setCellValue($this->colPendingApprovalVoAmount . $this->currentRow, self::COL_PENDING_APPROVAL_VO_AMOUNT . " ({$this->currencyCode})");
        $this->activeSheet->setCellValue($this->colVariationOrderClaimAmount . $this->currentRow, self::COL_VARIATION_ORDER_CLAIM_AMOUNT . " ({$this->currencyCode})");
        $this->activeSheet->setCellValue($this->colVariationOrderDescription . $this->currentRow, self::COL_VARIATION_ORDER_DESCRIPTION);
        $this->activeSheet->setCellValue($this->colRemarks . $this->currentRow, self::COL_REMARKS);

        //Set header styling
        $this->activeSheet->getStyle("{$this->firstCol}{$this->currentRow}:{$this->lastCol}{$this->currentRow}")->applyFromArray($this->getColumnHeaderStyle());

        $this->objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);

        $this->setColumnDimensions();
    }

    protected function processItem($variationOrder)
    {
        $this->activeSheet->setCellValue($this->colNo . $this->currentRow, ++$this->itemCount);
        $this->activeSheet->setCellValue($this->colRequestForVariationNumber . $this->currentRow, $variationOrder['rfv_number']);
        $this->activeSheet->setCellValue($this->colRequestForVariationDescription . $this->currentRow, $variationOrder['rfv_description']);
        $this->activeSheet->setCellValue($this->colVariationOrderDescription . $this->currentRow, $variationOrder['vo_description']);

        parent::setValue($this->colRequestForVariationAmount, ($variationOrder['vo_reference_amount'] == 0) ? 0.0 : number_format($variationOrder['vo_reference_amount'], 2));
        parent::setValue($this->colVariationOrderAmount, ($variationOrder['nett_omission_addition'] == 0) ? 0.0 : number_format($variationOrder['nett_omission_addition'], 2));
        parent::setValue($this->colVariationOrderClaimAmount, ($variationOrder['total_claim'] == 0) ? 0.0 : number_format($variationOrder['total_claim'], 2));
        parent::setValue($this->colPendingApprovalVoAmount, ($variationOrder['pending_amount'] == 0) ? 0.0 : number_format($variationOrder['pending_amount'], 2));

        $difference = ($variationOrder['status'] == VariationOrder::STATUS_PENDING) ? 0.0 : $variationOrder['vo_reference_amount'] - $variationOrder['nett_omission_addition'];

        parent::setValue($this->colDifference, ( $difference == 0 ) ? 0.0 : number_format($difference, 2));

        if( ( $difference ) < 0 )
        {
            $this->activeSheet->getStyle($this->colDifference . $this->currentRow)->applyFromArray($this->getRedRateStyling());
        }

        $this->activeSheet->getStyle($this->colNo . $this->currentRow)->applyFromArray(array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical'   => PHPExcel_Style_Alignment::VERTICAL_CENTER
            )
        ));

        $this->activeSheet->getStyle($this->colRequestForVariationDescription . $this->currentRow)->applyFromArray($this->getDescriptionStyle());
        $this->activeSheet->getStyle($this->colVariationOrderDescription . $this->currentRow)->applyFromArray($this->getDescriptionStyle());

        $this->totalRequestForVariationAmount += $variationOrder['vo_reference_amount'];
        $this->totalVariationOrderAmount += $variationOrder['nett_omission_addition'];
        $this->totalVariationOrderClaimAmount += $variationOrder['total_claim'];
        $this->totalDifference += $difference;
        $this->totalVoPendingAmount += $variationOrder['pending_amount'];
    }

    public function generateReport()
    {
        $this->createSheet(self::VARIATION_ORDER_REPORT, null, $this->project->title);

        $this->createHeader();

        $numberOfItems = count($this->variationOrders);

        $count = 0;

        foreach($this->variationOrders as $variationOrder)
        {
            $addBottomLine = ++$count == $numberOfItems;

            $this->newLine($addBottomLine);

            $this->processItem($variationOrder);
        }

        $this->addGrandTotal();

        $this->generateExcelFile();
    }

    protected function addGrandTotal()
    {
        $this->currentRow++;

        $this->activeSheet->setCellValue($this->colRequestForVariationDescription . $this->currentRow, self::TOTAL);
        $this->activeSheet->getStyle($this->colRequestForVariationDescription . $this->currentRow)->applyFromArray($this->getTotalStyle());

        parent::setValue($this->colRequestForVariationAmount, number_format($this->totalRequestForVariationAmount, 2));
        parent::setValue($this->colVariationOrderAmount, number_format($this->totalVariationOrderAmount, 2));
        parent::setValue($this->colDifference, number_format($this->totalDifference, 2));
        parent::setValue($this->colPendingApprovalVoAmount, number_format($this->totalVoPendingAmount, 2));
        parent::setValue($this->colVariationOrderClaimAmount, number_format($this->totalVariationOrderClaimAmount, 2));

        if( ( $this->totalDifference ) < 0 )
        {
            $this->activeSheet->getStyle($this->colDifference . $this->currentRow)->applyFromArray($this->getRedTotalStyle());
        }

        $this->activeSheet->getStyle($this->colRequestForVariationAmount . $this->currentRow)->applyFromArray($this->getTotalStyle());
        $this->activeSheet->getStyle($this->colVariationOrderAmount . $this->currentRow)->applyFromArray($this->getTotalStyle());
        $this->activeSheet->getStyle($this->colDifference . $this->currentRow, $this->currentRow)->applyFromArray($this->getTotalStyle());
        $this->activeSheet->getStyle($this->colPendingApprovalVoAmount . $this->currentRow, $this->currentRow)->applyFromArray($this->getTotalStyle());
        $this->activeSheet->getStyle($this->colVariationOrderClaimAmount . $this->currentRow)->applyFromArray($this->getTotalStyle());

        $this->activeSheet->getStyle($this->colRequestForVariationAmount . $this->currentRow . ":" . $this->colVariationOrderClaimAmount . $this->currentRow)->applyFromArray($this->getNewLineStyle(true));
    }

}