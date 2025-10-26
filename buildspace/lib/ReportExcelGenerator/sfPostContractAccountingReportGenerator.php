<?php

class sfPostContractAccountingReportGenerator extends sfBuildspaceExcelReportGenerator
{
    protected $objPHPExcel;

    private $claimCertificateData = null;
    private $claimCertificateInvoiceData = null;
    private $supplierData = null;
    private $projectCodeSettingSubsidiaryRecords = null;
    private $itemCodeSettingsData = null;
    private $debitNotesData = null;
    private $creditNotesData = null;
    private $retentionSumCode = null;
    private $generateRetentionRow = false;
    private $itemCodesIdProjectCodesIdPairings = null;

    public $currentRow = 1;
    private $subsidiariesLevelDepth = 0;
    private $proportionsGroupedByIds = [];

    private $colAccountCodeType = 'B1';
    private $colUniqueId = 'C1';

    const FILE_NAME = 'Accounting';
    const SHEET_NAME = 'ImportData';

    const COL_ACCOUNT_CODE_TYPE = 'JournalTypeCode';
    const COL_CLAIM_CERTIFICATE_ID = 'Unique Key';
    const COL_DESCRIPTION = 'Description';
    const COL_CURRENCY = 'Currency';
    const COL_CLAIM_REVISION_NUMBER = 'Claim No.';
    const COL_INVOICE_NUMBER = 'Invoice No.';
    const COL_INVOICE_DATE = 'Invoice Date';
    const COL_POST_MONTH = 'Post Month';
    const COL_PAYMENT_DUE_DATE = 'Payment Due Date';
    const COL_PERIOD_ENDING = 'Period Ending';
    const COL_SUPPLIER_ROC = 'Supplier ROC';
    const COL_SUPPLIER_CODE = 'Supplier Code';
    const COL_SUPPLIER_NAME = 'Supplier Name';
    const COL_ITEM_CODE = 'Account Code';
    const COL_ITEM_DESCRIPTION = 'Description';
    const COL_NETT_AMOUNT = 'NetAmount';
    const COL_RETENTION_SUM = 'Retention Sum';
    const COL_RELEASE_RETENTION_SUM = 'Release Retention';

    const ACCOUNT_CODE_TYPE_PIV = 1;
    const ACCOUNT_CODE_TYPE_PCN = 2;
    const ACCOUNT_CODE_TYPE_PDN = 4;

    const ACCOUNT_CODE_TYPE_PIV_TEXT = 'PIV';
    const ACCOUNT_CODE_TYPE_PCN_TEXT = 'PCN';
    const ACCOUNT_CODE_TYPE_PDN_TEXT = 'PDN';

    const UNIQUE_KEY_PREFIX_ITEM_PIV = 'V';
    const UNIQUE_KEY_PREFIX_ITEM_PCN = 'C';
    const UNIQUE_KEY_PREFIX_ITEM_PDN = 'D';

    const ADDITIONAL_SUBSIDIARY_COLUMN_CODE_PREFIX = 'Code';
    const ADDITIONAL_SUBSIDIARY_COLUMN_NAME_PREFIX = 'Name';

    public function __construct(ProjectStructure $projectStructure, ClaimCertificate $claimCertificate, $projectCodeSettingIds)
    {
        $this->objPHPExcel = new sfPhpExcel();
        $this->objPHPExcel->setActiveSheetIndex(0);

        $pcsIdIcsIdsPairings = [];
        $pcsIds              = [];
        
        foreach($projectCodeSettingIds as $pcsIdString)
        {
            $pcsId = substr($pcsIdString, 0, stripos($pcsIdString, '['));

            array_push($pcsIds, $pcsId);

            $pcsIdIcsIdsPairings[$pcsId] = explode('|', str_replace([$pcsId, '[', ']'], '', $pcsIdString ));
        }

        $itemCodes = array_unique(array_merge(...array_values($pcsIdIcsIdsPairings)));

        $this->itemCodesIdProjectCodesIdPairings = array_fill_keys($itemCodes, []);

        foreach($this->itemCodesIdProjectCodesIdPairings as $key => $result)
        {
            foreach($pcsIds as $pcsId)
            {
                if(in_array($key, $pcsIdIcsIdsPairings[$pcsId]))
                {
                    array_push($this->itemCodesIdProjectCodesIdPairings[$key], $pcsId);
                }
            }
        }

        $claimCertInfo = $claimCertificate->getClaimCertInfo();

        $this->claimCertificateData = [
            'currentTotalWorkDone'      => number_format($claimCertInfo['currentTotalWorkDone'], 2, '.', ''),
            'retentionAmount'           => number_format($claimCertInfo['currentRetentionSum'], 2, '.', ''),
            'releaseRetentionAmount'    => number_format($claimCertificate->release_retention_amount, 2, '.', ''),
            'amountCertified'           => number_format($claimCertInfo['amountCertified'], 2, '.', ''),
            'dueDate'                   => date('d/m/Y', strtotime($claimCertificate->due_date)),
            'periodEnding'              => date('d/m/Y', strtotime($claimCertificate->budget_due_date)),
            'currency'                  => $projectStructure->MainInformation->Currency,
            'claimRevision'             => $claimCertificate->PostContractClaimRevision,
        ];

        $this->claimCertificateInvoiceData = [
            'invoiceNumber'     => $claimCertificate->Invoice->isNew() ? '' : $claimCertificate->Invoice->invoice_number,
            'invoiceDate'       => $claimCertificate->Invoice->isNew() ? '' : date('d/m/Y', strtotime($claimCertificate->Invoice->invoice_date)),
            'postMonth'         => $claimCertificate->Invoice->isNew() ? '' : $claimCertificate->Invoice->post_month,
        ];

        $this->supplierData = [
            'roc'           => $projectStructure->TenderSetting->AwardedCompany->getEProjectCompany()->reference_no,
            'supplier_code' => $projectStructure->NewPostContractFormInformation->creditor_code,
            'name'          => $projectStructure->TenderSetting->AwardedCompany->name,
        ];

        $this->proportionsGroupedByIds = $this->readjustProportionPercentage(json_decode(json_encode($this->getProportionsGroupedByIds($projectStructure, $pcsIds)), true), $this->itemCodesIdProjectCodesIdPairings);
        $this->projectCodeSettingSubsidiaryRecords = $this->getProjectCodeSettingsSubsidiariesRecords($projectStructure, $pcsIds);

        if(count($this->projectCodeSettingSubsidiaryRecords) > 0)
        {
            $this->itemCodeSettingsData = $this->getItemCodeSettingsData($projectStructure, $claimCertificate, $itemCodes);
            $this->debitNotesData = $this->getDebitCreditNoteClaimsData($projectStructure, $claimCertificate, self::ACCOUNT_CODE_TYPE_PDN);
            $this->creditNotesData = $this->getDebitCreditNoteClaimsData($projectStructure, $claimCertificate, self::ACCOUNT_CODE_TYPE_PCN);
        }

        $this->generateRetentionRow = (($this->claimCertificateData['retentionAmount'] != 0.0) || ($this->claimCertificateData['releaseRetentionAmount'] != 0.0));
        $this->retentionSumCode = Doctrine_Query::create()->from('RetentionsumCode')->fetchOne()->code;
    
        $filename = self::FILE_NAME. "_" . date('dmY');
        $savePath = sfConfig::get('sf_web_dir') . DIRECTORY_SEPARATOR . 'uploads';
        parent::__construct($projectStructure, $savePath, $filename, array());
    }

    public function getProportionsGroupedByIds($projectStructure, $projectCodeSettingIds)
    {
        $client = new GuzzleHttp\Client(array(
            'debug'    => false,
            'verify'   => sfConfig::get('app_guzzle_ssl_verification'),
            'base_uri' => sfConfig::get('app_e_project_url')
        ));

        try
        {
            $res = $client->post('buildspace/getProportionsGroupedByIds', [
                'form_params' => [
                    'eProjectOriginId'      => $projectStructure->MainInformation->eproject_origin_id,
                    'projectCodeSettingIds' => implode(',', $projectCodeSettingIds),
                ]
            ]);

            $content                  = $res->getBody()->getContents();
            $jsonObj                  = json_decode($content);
            $proportionsGroupedByIds  = $jsonObj->proportionsGroupedByIds;
        }
        catch(Exception $e)
        {
            throw $e;
        }

        return $proportionsGroupedByIds;
    }

    // readjust proportion percentage when the total is not 100.0
    // happens when number of item codes is different for each selected phase subsidiary
    private function readjustProportionPercentage($proportionsGroupedByIds, $itemCodesIdProjectCodesIdPairings)
    {
        $icsPscPercentages = array_fill_keys(array_keys($itemCodesIdProjectCodesIdPairings), []);

        foreach($itemCodesIdProjectCodesIdPairings as $itemCodeId => $projectCodeSettingIds)
        {
            foreach($projectCodeSettingIds as $projectCodeSettingId)
            {
                $icsPscPercentages[$itemCodeId][$projectCodeSettingId] = $proportionsGroupedByIds[$projectCodeSettingId];
            }
        }

        $fullPercentage = 100.0;

        foreach($icsPscPercentages as $itemCodeId => &$icsPscPercentage)
        {
            $percentageSum = array_sum($icsPscPercentage);

            if($percentageSum === 100.0) continue;

            $count = 1;
            $currentTotalPercentage = 0.0;

            foreach($icsPscPercentage as $projectCodeId => $percentage)
            {
                $isLastLine = ($count === count($icsPscPercentage));

                if($isLastLine)
                {
                    $scaledPercentage = number_format((100.0 - $currentTotalPercentage), 2, '.', '');
                }
                else
                {
                    $scaledPercentage = number_format((($percentage / $percentageSum) * 100.0), 2, '.', '');
                    $currentTotalPercentage += number_format($scaledPercentage, 2, '.', '');
                }

                $icsPscPercentage[$projectCodeId] = $scaledPercentage;

                ++ $count;
            }
        }

        return $icsPscPercentages;
    }

    private function getProjectCodeSettingsSubsidiariesRecords(ProjectStructure $projectStructure, $projectCodeSettingIds)
    {
        $subsiadiariesByLevel = [];
        $subsidiaryIdsArray = [];
        $projectCodeSettingRecords = $projectStructure->ProjectCodeSettings;

        if(count($projectCodeSettingRecords) == 0) return [];

        foreach($projectCodeSettingIds as $projectCodeSettingId)
        {
            array_push($subsidiaryIdsArray, Doctrine_Core::getTable('ProjectCodeSettings')->find($projectCodeSettingId)->eproject_subsidiary_id);
        }

        $parentsHierarchyWithLevel = EProjectSubsidiary::constructHierarchyGroupedByLevel($subsidiaryIdsArray, true);

        foreach($parentsHierarchyWithLevel as $level => $parents)
        {
            $subsiadiariesByLevel[$level] = [];

            foreach($parents as $parentId)
            {
                $record = ProjectCodeSettings::getProjectCodeSettingRecord($projectStructure, $parentId);
                $eprojectSubsidiary = Doctrine_Core::getTable('EProjectSubsidiary')->find($parentId);

                array_push($subsiadiariesByLevel[$level], [
                    'id'                     => $record->id,
                    'project_structure_id'   => $projectStructure->id,
                    'eproject_subsidiary_id' => $eprojectSubsidiary->id,
                    'name'                   => $eprojectSubsidiary->name,
                    'subsidiary_code'        => $record->subsidiary_code,
                    'proportion'             => null,
                    'level'                  => $level,
                    'children'               => [],
                ]);

                $this->subsidiariesLevelDepth = $level;
            }
        }

        if(!empty($subsiadiariesByLevel))
        {
            foreach($subsiadiariesByLevel[$this->subsidiariesLevelDepth] as $index => $subsidiary)
            {
                $childrenSubsidiaryArray = [];
                $directChildren = EProjectSubsidiary::getDirectChildrenOf($subsidiary['eproject_subsidiary_id']);

                foreach($directChildren as $children)
                {
                    $record = ProjectCodeSettings::getProjectCodeSettingRecord($projectStructure, $children['id']);
    
                    if($record && (in_array($record->id, $projectCodeSettingIds)))
                    {
                        array_push($childrenSubsidiaryArray, [
                            'id'                     => $record->id,
                            'project_structure_id'   => $projectStructure->id,
                            'eproject_subsidiary_id' => $children['id'],
                            'name'                   => $children['name'],
                            'subsidiary_code'        => $record->subsidiary_code,
                            'proportion'             => null,
                            'level'                  => $this->subsidiariesLevelDepth,
                        ]);
                        
                    }
                }

                $subsiadiariesByLevel[$this->subsidiariesLevelDepth][$index]['children'] = $childrenSubsidiaryArray;
            }
        }

        return $subsiadiariesByLevel;
    }

    private function getItemCodeSettingsData(ProjectStructure $projectStructure, ClaimCertificate $claimCertificate, $itemCodes)
    {
        $pdo       = ItemCodeSettingTable::getInstance()->getConnection()->getDbh();
        $itemCodes = implode(',', $itemCodes);

        $itemCodeSettingsQuery = "SELECT ics.id, ac.code AS account_code, ac.description AS item_code_description, ac.type AS account_code_type " .
                                 "FROM " . ItemCodeSettingTable::getInstance()->getTableName() . " ics " .
                                 "INNER JOIN " . AccountCodeTable::getInstance()->getTableName() . " ac ON ics.account_code_id = ac.id " .
                                 "WHERE ics.id IN ($itemCodes) " .
                                 "ORDER by ics.id ASC";

        $stmt = $pdo->prepare($itemCodeSettingsQuery);
        $stmt->execute();

        $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $this->getProcessedItemCodeSettingsData($projectStructure, $claimCertificate, $records);
    }

    private function getProcessedItemCodeSettingsData(ProjectStructure $projectStructure, ClaimCertificate $claimCertificate, $itemCodeSettingRecords)
    {
        $data = [];
        $linesGroup = [];
        $amountsGroupedByItemCodeSettingIds = $this->getAmountsGroupedById($projectStructure, $claimCertificate);

        foreach($itemCodeSettingRecords as $record)
        {
            $linesGroup[$record['id']] = $this->generateBreakdown($projectStructure, $claimCertificate, $record);
        }

        foreach($amountsGroupedByItemCodeSettingIds as $id => $amount)
        {
            $currentAmount = 0.0;

            foreach($linesGroup[$id] as $index => &$line)
            {
                $isLastLine = ($index == (count($linesGroup[$id]) - 1));
                if($isLastLine)
                {
                    $nettAmount = number_format($amount - $currentAmount, 2, '.', '');
                }
                else
                {
                    $percentage = ($line['pcsProportion'] / 100.0);
                    $nettAmount = number_format(($amount * $percentage) ,2 , '.', '');
                    $currentAmount += $nettAmount;
                }

                $line['nettAmount'] = $nettAmount;
                array_push($data, $line);
            }
        }

        return $data;
    }

    private function getDebitCreditNoteClaimsData(ProjectStructure $projectStructure,  ClaimCertificate $claimCertificate, $type)
    {
        $pdo = DebitCreditNoteClaimTable::getInstance()->getConnection()->getDbh();

        $debitCreditNotesQuery = "SELECT MIN(ci.id) as id, ac.description AS item_code_description, ROUND(COALESCE(SUM((ci.quantity * ci.rate)), 0), 2)  AS nett_amount, ac.code as account_code, ac.type AS account_code_type " . 
                                 "FROM " . DebitCreditNoteClaimTable::getInstance()->getTableName() . " c " .
                                 "INNER JOIN " . DebitCreditNoteClaimItemTable::getInstance()->getTableName() . " ci ON ci.debit_credit_note_claim_id = c.id " .
                                 "INNER JOIN " . AccountCodeTable::getInstance()->getTableName() . " ac ON ci.account_code_id = ac.id " .
                                 "WHERE c.project_structure_id = {$projectStructure->id} AND " .
                                 "c.claim_certificate_id = {$claimCertificate->id} AND " .
                                 "c.deleted_at IS NULL AND " .
                                 "ci.deleted_at IS NULL AND " .
                                 "ac.type = {$type} " .
                                 "GROUP BY ac.description, ac.type, ac.code";

        $prefix = ($type === self::ACCOUNT_CODE_TYPE_PDN) ? self::UNIQUE_KEY_PREFIX_ITEM_PDN : self::UNIQUE_KEY_PREFIX_ITEM_PCN;

        $stmt = $pdo->prepare($debitCreditNotesQuery);
        $stmt->execute();
        $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $processedData = $this->getProcessedDebitCreditNoteClaimsData($projectStructure, $records, $prefix);

        return $processedData;
    }

    private function getProcessedDebitCreditNoteClaimsData(ProjectStructure $projectStructure, $records, $prefix)
    {
        $linesGroup = [];
        $phaseSubsidiary = null;

        // premature break due to only requiring (1) subsidiary
        foreach($this->projectCodeSettingSubsidiaryRecords[$this->subsidiariesLevelDepth] as $subsidiaryRecord)
        {
            if(count($subsidiaryRecord['children']) <= 0)
            {
                $phaseSubsidiary = $subsidiaryRecord;
                break;
            }
            else
            {
                foreach($subsidiaryRecord['children'] as $childSubsidiaryRecord)
                {
                    $phaseSubsidiary = $childSubsidiaryRecord;
                    break;
                }
            }
        }

        foreach($records as $record)
        {
            $linesGroup[$record['id']] = [
                'id'                => $record['id'],
                'accountCodeType' => $this->getAccountCodeTypeText($record['account_code_type']),
                'uniqueId' => $prefix . $record['id'],
                'currencyCode' => $this->claimCertificateData['currency']->currency_code,
                'claimRevisionNumber' => $this->claimCertificateData['claimRevision']->version,

                'description' => $projectStructure->MainInformation->title . ' (Claim no.' . $this->claimCertificateData['claimRevision']->version . ')',
                'accountCode' => $record['account_code'],
                'item_code_description' => $record['item_code_description'],

                'pcsId' => $phaseSubsidiary['id'],
                'pcsSubsidiaryId' => $phaseSubsidiary['eproject_subsidiary_id'],
                'pcsSubsidiaryCode' => $phaseSubsidiary['subsidiary_code'],
                'pcsProportion' => $phaseSubsidiary['proportion'],

                'subsidiaryGroup' => $this->generateSubsidiaryGroup($projectStructure, $phaseSubsidiary['eproject_subsidiary_id']),

                'invoiceNumber' => $this->claimCertificateInvoiceData['invoiceNumber'],
                'invoiceDate' => $this->claimCertificateInvoiceData['invoiceDate'],
                'postMonth' => $this->claimCertificateInvoiceData['postMonth'],
                'dueDate' => $this->claimCertificateData['dueDate'],
                'periodEnding' => $this->claimCertificateData['periodEnding'],
                'nettAmount' => $record['nett_amount'],
            ];
        }

        return $linesGroup;
    }

    private function generateBreakdown(ProjectStructure $projectStructure, $claimCertificate, $record)
    {
        $data = [];
        $records = [];

        foreach($this->projectCodeSettingSubsidiaryRecords[$this->subsidiariesLevelDepth] as $subsidiaryRecord)
        {
            $records['id'] = $record['id'];
            $records['account_code'] = $record['account_code'];
            $records['item_code_description'] = $record['item_code_description'];
            $records['account_code_type'] = $record['account_code_type'];
            $records['accountCodeType'] = $this->getAccountCodeTypeText($record['account_code_type']);
            $records['uniqueId'] = self::UNIQUE_KEY_PREFIX_ITEM_PIV . $claimCertificate->id;
            $records['currencyCode'] = $this->claimCertificateData['currency']->currency_code;
            $records['claimRevisionNumber'] = $this->claimCertificateData['claimRevision']->version;

            $records['description'] = $projectStructure->MainInformation->title . ' (Claim no.' . $this->claimCertificateData['claimRevision']->version . ')';
            $records['accountCode'] = $record['account_code'];
            $records['itemDescription'] = $record['item_code_description'];

            $records['pcsId'] = $subsidiaryRecord['id'];
            $records['pcsSubsidiaryId'] = $subsidiaryRecord['eproject_subsidiary_id'];
            $records['pcsSubsidiaryCode'] = $subsidiaryRecord['subsidiary_code'];
            $records['pcsProportion'] = $subsidiaryRecord['proportion'];

            $records['subsidiaryGroup'] = $this->generateSubsidiaryGroup($projectStructure, $subsidiaryRecord['eproject_subsidiary_id']);

            $records['invoiceNumber'] = $this->claimCertificateInvoiceData['invoiceNumber'];
            $records['invoiceDate'] = $this->claimCertificateInvoiceData['invoiceDate'];
            $records['postMonth'] = $this->claimCertificateInvoiceData['postMonth'];
            $records['dueDate'] = $this->claimCertificateData['dueDate'];
            $records['periodEnding'] = $this->claimCertificateData['periodEnding'];

            if(count($subsidiaryRecord['children']) <= 0)
            {
                array_push($data, $records);

                continue;
            }

            foreach($subsidiaryRecord['children'] as $phaseSubsidiaryRecord)
            {
                // skip if a given item code is not selected when exporting
                if(!in_array($phaseSubsidiaryRecord['id'], $this->itemCodesIdProjectCodesIdPairings[$record['id']])) continue;

                $records['id'] = $record['id'];
                $records['account_code'] = $record['account_code'];
                $records['item_code_description'] = $record['item_code_description'];
                $records['account_code_type'] = $record['account_code_type'];
                $records['accountCodeType'] = $this->getAccountCodeTypeText($record['account_code_type']);
                $records['uniqueId'] = self::UNIQUE_KEY_PREFIX_ITEM_PIV . $claimCertificate->id;
                $records['currencyCode'] = $this->claimCertificateData['currency']->currency_code;
                $records['claimRevisionNumber'] = $this->claimCertificateData['claimRevision']->version;

                $records['description'] = $projectStructure->MainInformation->title . ' (Claim no.' . $this->claimCertificateData['claimRevision']->version . ')';
                $records['accountCode'] = $record['account_code'];
                $records['itemDescription'] = $record['item_code_description'];

                $records['pcsId'] = $phaseSubsidiaryRecord['id'];
                $records['pcsSubsidiaryId'] = $phaseSubsidiaryRecord['eproject_subsidiary_id'];
                $records['pcsSubsidiaryCode'] = $phaseSubsidiaryRecord['subsidiary_code'];
                $records['pcsProportion'] = $this->proportionsGroupedByIds[$record['id']][$phaseSubsidiaryRecord['id']];

                $records['subsidiaryGroup'] = $this->generateSubsidiaryGroup($projectStructure, $phaseSubsidiaryRecord['eproject_subsidiary_id']);

                $records['invoiceNumber'] = $this->claimCertificateInvoiceData['invoiceNumber'];
                $records['invoiceDate'] = $this->claimCertificateInvoiceData['invoiceDate'];
                $records['postMonth'] = $this->claimCertificateInvoiceData['postMonth'];
                $records['dueDate'] = $this->claimCertificateData['dueDate'];
                $records['periodEnding'] = $this->claimCertificateData['periodEnding'];

                array_push($data, $records);
            }
        }

        return $data;
    }

    private function generateSubsidiaryGroup(ProjectStructure $projectStructure, $subsidiaryId)
    {
        $subsidiaryGroup = [];
        $codeKeyArray = ['companyCode', 'projectCode', 'phaseCode'];
        $nameKeyArray = ['Company Code', 'Project Code', 'Phase Code'];
        $compulsoryColumnsCount = count($codeKeyArray);
        $subsidiaries = [];
        $totalRequiredColumns = 0;

        $subsidiary = Doctrine_Core::getTable('EProjectSubsidiary')->find($subsidiaryId);
        $parentsOfSubsidiary = $subsidiary->getParentsOfSubsidiary();
        
        if($parentsOfSubsidiary)
        {
            foreach($parentsOfSubsidiary as $parentSub)
            {
                array_push($subsidiaries, $parentSub);
            }
        }

        array_push($subsidiaries, $subsidiary);

        $totalRequiredColumns = count($subsidiaries);

        // create additional rows
        $subsidiaryCountExceededFixedColumns = $totalRequiredColumns > count($codeKeyArray);

        if($subsidiaryCountExceededFixedColumns)
        {
            $difference = $totalRequiredColumns - count($codeKeyArray);

            for($i = 0; $i < $difference; $i++)
            {
                array_push($codeKeyArray, 'code' . ($i + 1));
                array_push($nameKeyArray, 'Name ' . ($i + 1));
            }
        }

        for($i = 0; $i < count($subsidiaries); $i++)
        {
            $projectCodeSettings = ProjectCodeSettings::getProjectCodeSettingRecord($projectStructure, $subsidiaries[$i]->id);
            $eprojectSubsidiary = Doctrine_Core::getTable('EProjectSubsidiary')->find($subsidiaries[$i]->id);
            $subsidiaryGroup[$codeKeyArray[$i]] = [
                'name_column' => $nameKeyArray[$i],
                'code'        => $projectCodeSettings->subsidiary_code,
                'name'        => $eprojectSubsidiary->name,
            ];
        }

        // generate additional columns if subsidiaries are less than compulsory columns
        $subsidiariesLessThanCompulsoryColumn = $totalRequiredColumns < $compulsoryColumnsCount;

        if($subsidiariesLessThanCompulsoryColumn)
        {
            $difference = $compulsoryColumnsCount - $totalRequiredColumns;
            $startIndex = $compulsoryColumnsCount - $difference;

            for($i = $startIndex; $i < $compulsoryColumnsCount; $i++)
            {
                $subsidiaryGroup[$codeKeyArray[$i]] = [
                    'name_column' => $nameKeyArray[$i],
                    'code'        => '',
                    'name'        => '',
                ];
            }
        }

        return $subsidiaryGroup;
    }

    private function getAmountsGroupedById(ProjectStructure $projectStructure, ClaimCertificate $claimCertificate)
    {
        $pdo  = $projectStructure->getTable()->getConnection()->getDbh();

        $stmt = $pdo->prepare("SELECT brk.item_code_setting_id, ROUND(COALESCE(SUM(brk.amount), 0), 2) AS amount
                FROM " . ItemCodeSettingObjectTable::getInstance()->getTableName() . " obj
                JOIN " . ItemCodeSettingObjectBreakdownTable::getInstance()->getTableName() . " brk ON brk.item_code_setting_object_id = obj.id
                WHERE obj.project_structure_id = " . $projectStructure->id . " 
                AND brk.claim_certificate_id = " . $claimCertificate->id . "
                GROUP BY brk.item_code_setting_id");

        $stmt->execute();
        
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $results = Utilities::setAttributeAsKey($results, 'item_code_setting_id');

        foreach($results as $id => $result)
        {
            $results[$id] = $result['amount'];
        }

        return $results;
    }

    private function getAccountCodeTypeText($typeId)
    {
        $accountCodeTypeText = '';

        switch($typeId)
        {
            case self::ACCOUNT_CODE_TYPE_PIV:
                $accountCodeTypeText =  self::ACCOUNT_CODE_TYPE_PIV_TEXT;
                break;
            case self::ACCOUNT_CODE_TYPE_PCN:
                $accountCodeTypeText =  self::ACCOUNT_CODE_TYPE_PCN_TEXT;
                break;
            case self::ACCOUNT_CODE_TYPE_PDN:
                $accountCodeTypeText =  self::ACCOUNT_CODE_TYPE_PDN_TEXT;
                break;
            default:
                // not going to happen
        }

        return $accountCodeTypeText;
    }

    private function generateSubsidiaryColumnHeaders()
    {
        $codeKeyArray = ['Company Code', 'Project Code', 'Phase Code'];
        $nameKeyArray = ['Company Name', 'Project Name', 'Phase Name'];

        $parentSubsidiariesLevelCount = count($this->projectCodeSettingSubsidiaryRecords);
        $totalRequiredColumns = $parentSubsidiariesLevelCount + 1; // +1 column for phase subsidiaries

        $additionalRequiredColumns = $totalRequiredColumns - count($codeKeyArray);
        
        $count = 0;

        while($additionalRequiredColumns > $count)
        {
            array_push($codeKeyArray, self::ADDITIONAL_SUBSIDIARY_COLUMN_CODE_PREFIX . ($count + 1));
            array_push($nameKeyArray, self::ADDITIONAL_SUBSIDIARY_COLUMN_NAME_PREFIX . ($count + 1));

            ++ $count;
        }

        return [
            'codeKeyArray' => $codeKeyArray,
            'nameKeyArray' => $nameKeyArray,
        ];
    }

    private function generateHeaders()
    {
        $this->objPHPExcel->getActiveSheet()->setCellValue($this->colAccountCodeType, self::COL_ACCOUNT_CODE_TYPE);
        $this->objPHPExcel->getActiveSheet()->setCellValue($this->colUniqueId, self::COL_CLAIM_CERTIFICATE_ID);

        $currentColumnIndex = 'C';

        $subsidiaryColumnHeaders = $this->generateSubsidiaryColumnHeaders();
        
        for($i = 0; $i < count($subsidiaryColumnHeaders['codeKeyArray']); $i++)
        {
            ++$currentColumnIndex;
            $this->objPHPExcel->getActiveSheet()->setCellValue($currentColumnIndex . $this->currentRow, $subsidiaryColumnHeaders['codeKeyArray'][$i]);

            ++$currentColumnIndex;
            $this->objPHPExcel->getActiveSheet()->setCellValue($currentColumnIndex . $this->currentRow, $subsidiaryColumnHeaders['nameKeyArray'][$i]);
        }

        $this->objPHPExcel->getActiveSheet()->setCellValue(++ $currentColumnIndex . $this->currentRow, self::COL_DESCRIPTION);
        $this->objPHPExcel->getActiveSheet()->setCellValue(++ $currentColumnIndex . $this->currentRow, self::COL_CURRENCY);
        $this->objPHPExcel->getActiveSheet()->setCellValue(++ $currentColumnIndex . $this->currentRow, self::COL_CLAIM_REVISION_NUMBER);
        $this->objPHPExcel->getActiveSheet()->setCellValue(++ $currentColumnIndex . $this->currentRow, self::COL_INVOICE_NUMBER);
        $this->objPHPExcel->getActiveSheet()->setCellValue(++ $currentColumnIndex . $this->currentRow, self::COL_INVOICE_DATE);
        $this->objPHPExcel->getActiveSheet()->setCellValue(++ $currentColumnIndex . $this->currentRow, self::COL_POST_MONTH);
        $this->objPHPExcel->getActiveSheet()->setCellValue(++ $currentColumnIndex . $this->currentRow, self::COL_PAYMENT_DUE_DATE);
        $this->objPHPExcel->getActiveSheet()->setCellValue(++ $currentColumnIndex . $this->currentRow, self::COL_PERIOD_ENDING);
        $this->objPHPExcel->getActiveSheet()->setCellValue(++ $currentColumnIndex . $this->currentRow, self::COL_SUPPLIER_ROC);
        $this->objPHPExcel->getActiveSheet()->setCellValue(++ $currentColumnIndex . $this->currentRow, self::COL_SUPPLIER_CODE);
        $this->objPHPExcel->getActiveSheet()->setCellValue(++ $currentColumnIndex . $this->currentRow, self::COL_SUPPLIER_NAME);
        $this->objPHPExcel->getActiveSheet()->setCellValue(++ $currentColumnIndex . $this->currentRow, self::COL_ITEM_CODE);
        $this->objPHPExcel->getActiveSheet()->setCellValue(++ $currentColumnIndex . $this->currentRow, self::COL_ITEM_DESCRIPTION);
        $this->objPHPExcel->getActiveSheet()->setCellValue(++ $currentColumnIndex . $this->currentRow, self::COL_NETT_AMOUNT);
        $this->objPHPExcel->getActiveSheet()->setCellValue(++ $currentColumnIndex . $this->currentRow, self::COL_RETENTION_SUM);
        $this->objPHPExcel->getActiveSheet()->setCellValue(++ $currentColumnIndex . $this->currentRow, self::COL_RELEASE_RETENTION_SUM);
    
        ++ $this->currentRow;
    }

    private function populateStandardInformation($info)
    {
        $currentColumnIndex = 'B';

        $this->objPHPExcel->getActiveSheet()->setCellValue($currentColumnIndex . $this->currentRow, $info['accountCodeType']);
        $this->objPHPExcel->getActiveSheet()->setCellValue(++ $currentColumnIndex . $this->currentRow, $info['uniqueId']);

        foreach($info['subsidiaryGroup'] as $key => $subsidiaryGroup)
        {
            $this->objPHPExcel->getActiveSheet()->setCellValueExplicit(++$currentColumnIndex . $this->currentRow, $subsidiaryGroup['code'], PHPExcel_Cell_DataType::TYPE_STRING);
            $this->objPHPExcel->getActiveSheet()->setCellValueExplicit(++$currentColumnIndex . $this->currentRow, $subsidiaryGroup['name'], PHPExcel_Cell_DataType::TYPE_STRING);
        }

        $this->objPHPExcel->getActiveSheet()->setCellValue(++ $currentColumnIndex . $this->currentRow, $info['description']);
        $this->objPHPExcel->getActiveSheet()->setCellValue(++ $currentColumnIndex . $this->currentRow, $info['currencyCode']);
        $this->objPHPExcel->getActiveSheet()->setCellValue(++ $currentColumnIndex . $this->currentRow, $info['claimRevisionNumber']);
        $this->objPHPExcel->getActiveSheet()->setCellValueExplicit(++ $currentColumnIndex . $this->currentRow, $info['invoiceNumber'], PHPExcel_Cell_DataType::TYPE_STRING);
        $this->objPHPExcel->getActiveSheet()->setCellValue(++ $currentColumnIndex . $this->currentRow, $info['invoiceDate']);
        $this->objPHPExcel->getActiveSheet()->setCellValue(++ $currentColumnIndex . $this->currentRow, $info['postMonth']);
        $this->objPHPExcel->getActiveSheet()->setCellValue(++ $currentColumnIndex . $this->currentRow, $info['dueDate']);
        $this->objPHPExcel->getActiveSheet()->setCellValue(++ $currentColumnIndex . $this->currentRow, $info['periodEnding']);
  
        return $currentColumnIndex;
    }

    private function populateItemCodeSettingInformationRows($info)
    {
        $currentColumnIndex = $this->populateStandardInformation($info);

        $this->objPHPExcel->getActiveSheet()->setCellValue(++$currentColumnIndex . $this->currentRow, '');
        $this->objPHPExcel->getActiveSheet()->setCellValue(++$currentColumnIndex . $this->currentRow, '');
        $this->objPHPExcel->getActiveSheet()->setCellValue(++$currentColumnIndex . $this->currentRow, '');
        $this->objPHPExcel->getActiveSheet()->setCellValueExplicit(++$currentColumnIndex . $this->currentRow, $info['account_code'], PHPExcel_Cell_DataType::TYPE_STRING);
        $this->objPHPExcel->getActiveSheet()->setCellValue(++$currentColumnIndex . $this->currentRow, $info['item_code_description']);
        $this->objPHPExcel->getActiveSheet()->setCellValue(++$currentColumnIndex . $this->currentRow, $info['nettAmount']);

        ++$this->currentRow;
    }

    private function populateItemCodeSettingInformationSummaryRows($info, $displayRetentionAmount = false)
    {
        $currentColumnIndex = $this->populateStandardInformation($info);

        if($displayRetentionAmount)
        {
            $this->objPHPExcel->getActiveSheet()->setCellValue(++$currentColumnIndex . $this->currentRow, '');
            $this->objPHPExcel->getActiveSheet()->setCellValue(++$currentColumnIndex . $this->currentRow, '');
            $this->objPHPExcel->getActiveSheet()->setCellValue(++$currentColumnIndex . $this->currentRow, '');
            $this->objPHPExcel->getActiveSheet()->setCellValueExplicit(++$currentColumnIndex . $this->currentRow, $this->retentionSumCode, PHPExcel_Cell_DataType::TYPE_STRING);
            $this->objPHPExcel->getActiveSheet()->setCellValue(++$currentColumnIndex . $this->currentRow, '');
            $this->objPHPExcel->getActiveSheet()->setCellValue(++$currentColumnIndex . $this->currentRow, ($this->claimCertificateData['releaseRetentionAmount'] - $this->claimCertificateData['retentionAmount']));
            $this->objPHPExcel->getActiveSheet()->setCellValue(++$currentColumnIndex . $this->currentRow, $this->claimCertificateData['retentionAmount']);
            $this->objPHPExcel->getActiveSheet()->setCellValue(++$currentColumnIndex . $this->currentRow, $this->claimCertificateData['releaseRetentionAmount']);
        }
        else
        {
            $this->objPHPExcel->getActiveSheet()->setCellValueExplicit(++$currentColumnIndex . $this->currentRow, $this->supplierData['roc'], PHPExcel_Cell_DataType::TYPE_STRING);
            $this->objPHPExcel->getActiveSheet()->setCellValueExplicit(++$currentColumnIndex . $this->currentRow, $this->supplierData['supplier_code'], PHPExcel_Cell_DataType::TYPE_STRING);
            $this->objPHPExcel->getActiveSheet()->setCellValue(++$currentColumnIndex . $this->currentRow, $this->supplierData['name']);
            $this->objPHPExcel->getActiveSheet()->setCellValue(++$currentColumnIndex . $this->currentRow, '');
            $this->objPHPExcel->getActiveSheet()->setCellValue(++$currentColumnIndex . $this->currentRow, '');
            $this->objPHPExcel->getActiveSheet()->setCellValue(++$currentColumnIndex . $this->currentRow, (0 - $this->claimCertificateData['amountCertified']));
        }

        ++$this->currentRow;
    }

    private function populateDebitCreditNoteInformationRows($info)
    {
        $currentColumnIndex = $this->populateStandardInformation($info);

        $this->objPHPExcel->getActiveSheet()->setCellValue(++$currentColumnIndex . $this->currentRow, '');
        $this->objPHPExcel->getActiveSheet()->setCellValue(++$currentColumnIndex . $this->currentRow, '');
        $this->objPHPExcel->getActiveSheet()->setCellValue(++$currentColumnIndex . $this->currentRow, '');
        $this->objPHPExcel->getActiveSheet()->setCellValueExplicit(++$currentColumnIndex . $this->currentRow, $info['accountCode'], PHPExcel_Cell_DataType::TYPE_STRING);
        $this->objPHPExcel->getActiveSheet()->setCellValue(++$currentColumnIndex . $this->currentRow, $info['item_code_description']);
        
        $this->objPHPExcel->getActiveSheet()->setCellValue(++$currentColumnIndex . $this->currentRow, (0 -$info['nettAmount']));
        
        ++$this->currentRow;
    }

    private function populateDebitCreditNoteSummaryInformationRow($info)
    {
        $currentColumnIndex = $this->populateStandardInformation($info);

        $this->objPHPExcel->getActiveSheet()->setCellValueExplicit(++$currentColumnIndex . $this->currentRow, $this->supplierData['roc'], PHPExcel_Cell_DataType::TYPE_STRING);
        $this->objPHPExcel->getActiveSheet()->setCellValueExplicit(++$currentColumnIndex . $this->currentRow, $this->supplierData['supplier_code'], PHPExcel_Cell_DataType::TYPE_STRING);
        $this->objPHPExcel->getActiveSheet()->setCellValue(++$currentColumnIndex . $this->currentRow, $this->supplierData['name']);
        $this->objPHPExcel->getActiveSheet()->setCellValue(++$currentColumnIndex . $this->currentRow, '');
        $this->objPHPExcel->getActiveSheet()->setCellValue(++$currentColumnIndex . $this->currentRow, '');

        $this->objPHPExcel->getActiveSheet()->setCellValue(++$currentColumnIndex . $this->currentRow, $info['nettAmount']);

        ++$this->currentRow;
    }

    private function populateInformationRows()
    {
        if(count($this->itemCodeSettingsData) > 0)
        {
            foreach($this->itemCodeSettingsData as $icsData)
            {
                // do not append line if nett amount is 0.0
                if($icsData['nettAmount'] == 0.0) continue;

                $this->populateItemCodeSettingInformationRows($icsData);
            }

            $this->populateItemCodeSettingInformationSummaryRows($this->itemCodeSettingsData[0]);

            if($this->generateRetentionRow)
            {
                $this->populateItemCodeSettingInformationSummaryRows($this->itemCodeSettingsData[0], true);
            }

            ++$this->currentRow;
        }

        if(count($this->debitNotesData) > 0)
        {
            foreach($this->debitNotesData as $id => $dnDataLine)
            {
                $this->populateDebitCreditNoteInformationRows($dnDataLine);
                $this->populateDebitCreditNoteSummaryInformationRow($dnDataLine);
            }

            ++$this->currentRow;
        }

        if(count($this->creditNotesData) > 0)
        {
            foreach($this->creditNotesData as $id => $cnDataLine)
            {
                $this->populateDebitCreditNoteInformationRows($cnDataLine);
                $this->populateDebitCreditNoteSummaryInformationRow($cnDataLine);
            }

            ++$this->currentRow;
        }
    }

    public function generateReport()
    {
        $this->objPHPExcel->getActiveSheet()->setTitle(self::SHEET_NAME);
        $this->generateHeaders();
        $this->populateInformationRows();
        $this->generateExcelFile();
    }
}

?>