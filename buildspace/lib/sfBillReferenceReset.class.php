<?php

class sfBillReferenceReset extends sfBillReferenceGenerator
{
    protected $selectedRateType;
    protected $postContract;
    protected $withoutNotListedItem;

    function __construct(PostContract $postContract, $withoutNotListedItem=false)
    {
        $project = $postContract->ProjectStructure;

        parent::__construct($project, true);

        $this->postContract                         = $postContract;
        $this->selectedRateType                     = $postContract->selected_type_rate;
        $this->saveOriginalBillInformation          = true;
        $this->publishToPostContract                = true;
        $this->withoutNotListedItem                 = $withoutNotListedItem;
        $this->dontSetLumpSumPercentageAsQtyPerUnit = true;
    }

    public function process()
    {
        $tenderAlternativeProjectStructureIds = [];
        $tenderAlternative = $this->project->getAwardedTenderAlternative();

        if($tenderAlternative)
        {
            //we set default to -1 so it should return empty query if there is no assigned bill for this tender alternative;
            $tenderAlternativeProjectStructureIds = [-1];
            $tenderAlternativesBills = $tenderAlternative->getAssignedBills();

            if($tenderAlternativesBills)
            {
                $tenderAlternativeProjectStructureIds = array_column($tenderAlternativesBills, 'id');
            }
        }

        $queryBills = DoctrineQuery::create()->select()
            ->from('ProjectStructure s')
            ->leftJoin('s.BillColumnSettings c')
            ->leftJoin('s.BillLayoutSetting bls')
            ->leftJoin('s.BillMarkupSetting m')
            ->where('s.lft >= ? AND s.rgt <= ?', array($this->project->lft, $this->project->rgt))
            ->andWhere('s.root_id = ?', $this->project->id)
            ->andWhere('s.type = ?', ProjectStructure::TYPE_BILL);
        
        if(!empty($tenderAlternativeProjectStructureIds))
        {
            $queryBills->whereIn('s.id', $tenderAlternativeProjectStructureIds);
        }

        $bills = $queryBills->addOrderBy('s.lft ASC')->execute();
        
        foreach ($bills as $bill)
        {
            $this->bill                     = $bill;
            $this->printSettings            = BillLayoutSettingTable::getInstance()->getPrintingLayoutSettings($bill->BillLayoutSetting->id, true);
            $this->elementsOrder            = $this->getElementOrder();
            $this->billReferenceToUpdate    = [];
            $this->itemIdsToRemoveReference = [];
            $this->billColumnSettings       = $this->getBillColumnSettings();

            $numberOfBillColumns            = $bill->getBillColumnSettings()->count();

            $this->fontType                 = self::setFontType($this->printSettings['layoutSetting']['fontTypeName']);
            $this->fontSize                 = $this->printSettings['layoutSetting']['fontSize'];

            $this->headSettings             = $this->printSettings['headSettings'];
            $this->printGrandTotalQty       = false;//set to false so that generateBillItemPages will group the item qty by bill column settings. This is needed to merge the qty from contractor's rate
            $this->currency                 = $this->project->MainInformation->Currency;
            $this->numberOfBillColumns      = $numberOfBillColumns;
            $this->orientation              = ($numberOfBillColumns > 1 and !$this->printGrandTotalQty) ? self::ORIENTATION_LANDSCAPE : self::ORIENTATION_PORTRAIT;
            $this->pageFormat               = $this->setPageFormat(self::PAGE_FORMAT_A4);

            self::setMaxCharactersPerLine($this->printSettings['layoutSetting']['printAmountOnly']);

            $this->billStructure = $this->queryBillStructure();

            $this->processBill($bill);

            $this->cloneBillItemRatesToPostContract();
        }
    }

    protected function cloneBillItemRatesToPostContract()
    {
        $elementToItemPages = $this->getElementToItemPages();
        
        $columnQty      = [];
        $bill           = $this->bill;
        $postContractId = $this->postContract->id;

        foreach($bill->BillColumnSettings as $columnSetting)
        {
            $columnQty[$columnSetting->id] = $columnSetting->quantity;

            unset($columnSetting);
        }

        $elementNo      = null;
        $itemChar       = null;
        $pageNoToInsert = null;
        $quantities     = null;

        $stmt             = new sfImportStatementGenerator();
        $typeRefStatement = new sfImportStatementGenerator();

        $stmt->createInsert(PostContractBillItemRateTable::getInstance()->getTableName(), array(
            'post_contract_id', 'bill_item_id', 'bill_ref_element_no', 'bill_ref_page_no', 'bill_ref_char', 'rate', 'grand_total'
        ));

        $typeRefStatement->createInsert(PostContractBillItemTypeTable::getInstance()->getTableName(), array(
            'post_contract_id', 'bill_item_id', 'bill_column_setting_id', 'total_quantity', 'grand_total', 'total_per_unit', 'qty_per_unit', 'include'
        ));

        $billItemKeys = [];

        foreach($elementToItemPages as $elementPages)
        {
            $elementNo = $elementPages['element_count'];

            foreach($elementPages['itemPages'] as $pageNo => $items)
            {
                foreach($items as $item)
                {
                    if($item[sfBuildspaceBQMasterFunction::ROW_BILL_ITEM_TYPE] != sfBuildspaceBQMasterFunction::ROW_TYPE_ELEMENT && $item[sfBuildspaceBQMasterFunction::ROW_BILL_ITEM_TYPE] != sfBuildspaceBQMasterFunction::ROW_TYPE_BLANK )
                    {
                        $pageNoToInsert = $pageNo;

                        if($item[sfBuildspaceBQMasterFunction::ROW_BILL_ITEM_ROW_IDX])
                            $itemChar = $item[sfBuildspaceBQMasterFunction::ROW_BILL_ITEM_ROW_IDX];

                        $itemId = $item[sfBuildspaceBQMasterFunction::ROW_BILL_ITEM_ID];

                        if(!array_key_exists($postContractId.'_'.$itemId, $billItemKeys) && !is_null($item[sfBuildspaceBQMasterFunction::ROW_BILL_ITEM_RATE]))
                        {
                            $billItemKeys[$postContractId.'_'.$itemId] = $postContractId.'_'.$itemId;

                            $grandTotalItem = 0;
                            $rate = 0;

                            if($itemChar && $item[sfBuildspaceBQMasterFunction::ROW_BILL_ITEM_TYPE] != BillItem::TYPE_HEADER && $item[sfBuildspaceBQMasterFunction::ROW_BILL_ITEM_TYPE] != BillItem::TYPE_HEADER_N && $item[sfBuildspaceBQMasterFunction::ROW_BILL_ITEM_TYPE] != BillItem::TYPE_NOID)
                            {
                                $rate = $item[sfBuildspaceBQMasterFunction::ROW_BILL_ITEM_RATE];

                                $quantities = $item[sfBuildspaceBQMasterFunction::ROW_BILL_ITEM_QTY_PER_UNIT];

                                if(is_array($quantities))
                                {
                                    foreach($quantities as $columnId => $qty)
                                    {
                                        $totalQty = $qty * $columnQty[$columnId];

                                        $totalPerUnit = round($rate * $qty, 2);

                                        $grandTotalItem += $grandTotalPerUnit = round($totalPerUnit * $columnQty[$columnId], 2);

                                        $grandTotal = round($totalQty * $rate, 2);

                                        $include = ($item[sfBuildspaceBQMasterFunction::ROW_BILL_ITEM_INCLUDE][$columnId]) ? 1 : 0 ;

                                        if(!($item[sfBuildspaceBQMasterFunction::ROW_BILL_ITEM_TYPE] == BillItem::TYPE_ITEM_NOT_LISTED && ($rate == 0 || $rate == '')) && !($item[sfBuildspaceBQMasterFunction::ROW_BILL_ITEM_TYPE] == BillItem::TYPE_ITEM_NOT_LISTED && $this->withoutNotListedItem))
                                        {
                                            $typeRefStatement->addRecord(
                                                array($postContractId, $itemId, $columnId, $totalQty, $grandTotal, $totalPerUnit, $qty, $include)
                                            );
                                        }
                                    }
                                }

                            }

                            if(!is_null($itemId) && !($item[sfBuildspaceBQMasterFunction::ROW_BILL_ITEM_TYPE] == BillItem::TYPE_ITEM_NOT_LISTED && ($rate == 0 || $rate == '')) && !($item[sfBuildspaceBQMasterFunction::ROW_BILL_ITEM_TYPE] == BillItem::TYPE_ITEM_NOT_LISTED && $this->withoutNotListedItem))
                            {
                                $stmt->addRecord(array( $postContractId, $itemId, $elementNo, $pageNoToInsert, $itemChar, $rate, $grandTotalItem ));
                            }

                            $itemChar = null;

                            unset($item);
                        }
                    }
                }
            }
        }

        unset($elementToItemPages, $billItemKeys);

        if(count($stmt->records))
            $stmt->save();

        if(count($typeRefStatement->records))
            $typeRefStatement->save();
    }

    protected function getElementToItemPages()
    {
        $elementCount       = $this->elementsOrder;
        $billStructure      = $this->billStructure;
        $billColumnSettings = $this->bill->BillColumnSettings;
        $elementToItemPages = [];

        switch($this->selectedRateType)
        {
            case PostContract::RATE_TYPE_RATIONALIZED:
                $ratesAfterMarkup = $this->getRationalizedRates();
                break;
            case PostContract::RATE_TYPE_CONTRACTOR:
                $ratesAfterMarkup = $this->getContractorRates();
                break;
            default:
                $ratesAfterMarkup = $this->getRatesAfterMarkup();
                break;
        }

        $lumpSumPercents   = $this->getLumpSumPercent();
        $itemQuantities    = $this->getItemQuantities();
        $itemIncludeStatus = $this->getItemIncludeStatus();
        $nlQty             = null;

        if($this->selectedRateType == PostContract::RATE_TYPE_CONTRACTOR)
        {
            $nlQty = $this->getContractNotListedQty();

            $itemQuantities = $this->mergeQty($itemQuantities, $nlQty);
        }

        if($this->selectedRateType == PostContract::RATE_TYPE_RATIONALIZED)
        {
            $nlQty = $this->getRationalizedNotListedQty();

            $itemQuantities = $this->mergeQty($itemQuantities, $nlQty);
        }

        if(is_array($nlQty) && count($nlQty))
        {
            $itemIncludeStatus = $this->mergeIncludeStatus($itemIncludeStatus, $nlQty);
        }

        foreach($billColumnSettings as $billColumnSetting)
        {
            $totalAmount[$billColumnSetting['id']] = 0;
            $totalPerUnit[$billColumnSetting['id']] = 0;
        }

        foreach($billStructure as $element)
        {
            $this->lastPageCount = 1;
            $itemPages           = [];
            $elemCount           = $elementCount[$element['id']]['order'];

            $elementInfo = [
                'id'            => $element['id'],
                'description'   => $element['description'],
                'element_count' => $elemCount
            ];

            $this->generateBillItemPages($element['items'], $billColumnSettings->toArray(), $elementInfo, 1, [], $itemPages, $ratesAfterMarkup, $lumpSumPercents, $itemIncludeStatus, $itemQuantities);
            
            $elementInfo['itemPages'] = $itemPages;

            array_push($elementToItemPages, $elementInfo);
        }

        return $elementToItemPages;
    }

    protected function getContractNotListedQty()
    {
        $pdo = $this->pdo;
        $bill = $this->bill;

        $stmt = $pdo->prepare("SELECT tc.id, tc.company_id
        FROM ".TenderSettingTable::getInstance()->getTableName()." ts
        JOIN ".TenderCompanyTable::getInstance()->getTableName()." tc ON tc.company_id = ts.awarded_company_id AND tc.project_structure_id = ts.project_structure_id
        WHERE ts.project_structure_id = ".$bill->root_id." AND ts.deleted_at IS NULL");

        $stmt->execute();
        $tenderCompany = $stmt->fetch(PDO::FETCH_ASSOC);

        $stmt = $pdo->prepare("SELECT tnl_qty.bill_column_setting_id, tnl.bill_item_id, COALESCE(tnl_qty.final_value, 0) AS value
        FROM ".TenderBillItemNotListedQuantityTable::getInstance()->getTableName()." tnl_qty
        JOIN ".TenderBillItemNotListedTable::getInstance()->getTableName()." tnl ON tnl_qty.tender_bill_item_not_listed_id = tnl.id
        JOIN ".BillItemTable::getInstance()->getTableName()." i ON i.id = tnl.bill_item_id
        JOIN ".BillElementTable::getInstance()->getTableName()." e ON e.id = i.element_id WHERE 
        i.project_revision_deleted_at IS NULL AND i.deleted_at IS NULL AND e.deleted_at IS NULL 
        AND tnl.tender_company_id = ".$tenderCompany['id']);

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC|PDO::FETCH_GROUP);
    }

    protected function getRationalizedNotListedQty()
    {
        $pdo = $this->pdo;
        $bill = $this->bill;

        $stmt = $pdo->prepare("SELECT tnl_qty.bill_column_setting_id, tnl.bill_item_id, COALESCE(tnl_qty.final_value, 0) AS value 
        FROM ".TenderBillItemNotListedRationalizedQuantityTable::getInstance()->getTableName()." tnl_qty
        JOIN ".TenderBillItemNotListedRationalizedTable::getInstance()->getTableName()." tnl ON tnl_qty.tender_bill_not_listed_item_rationalized_id = tnl.id
        JOIN ".BillItemTable::getInstance()->getTableName()." i ON i.id = tnl.bill_item_id
        JOIN ".BillElementTable::getInstance()->getTableName()." e ON e.id = i.element_id WHERE 
        i.project_revision_deleted_at IS NULL AND i.deleted_at IS NULL AND e.deleted_at IS NULL 
        AND e.project_structure_id = ".$bill->id);

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC|PDO::FETCH_GROUP);
    }

    protected function mergeQty($originalQty, $newQty)
    {
        foreach($newQty as $columnId => $items)
        {
            if(array_key_exists($columnId, $originalQty))
            {
                foreach($items as $item)
                {
                    $originalQty[$columnId][$item['bill_item_id']][0] = $item['value'];
                }
            }
        }

        return $originalQty;
    }

    protected function mergeIncludeStatus($originalStatus, $newQty)
    {
        foreach($newQty as $columnId => $items)
        {
            if(array_key_exists($columnId, $originalStatus))
            {
                foreach($items as $item)
                {
                    $originalStatus[$columnId][$item['bill_item_id']] = 1;
                }
            }
        }

        return $originalStatus;
    }

    public function getContractorRates()
    {
        $pdo = $this->pdo;

        $bill = $this->bill;

        $stmt = $pdo->prepare("SELECT t.project_structure_id, t.awarded_company_id, t.original_tender_value
        FROM ".TenderSettingTable::getInstance()->getTableName()." t
        WHERE t.project_structure_id = ".$bill->root_id." AND t.deleted_at IS NULL");

        $stmt->execute();

        $tenderSetting = $stmt->fetch(PDO::FETCH_ASSOC);

        if($tenderSetting['awarded_company_id'])
        {
            //Get Tender Companies
            $tenderCompanyXref = TenderCompanyTable::getByProjectIdAndCompanyId($bill->root_id, $tenderSetting['awarded_company_id'], Doctrine_Core::HYDRATE_ARRAY);

            //Get Contractor Rates
            $stmt = $pdo->prepare("SELECT t.bill_item_id, COALESCE(t.rate, 0) AS value FROM ".TenderBillItemRateTable::getInstance()->getTableName()." t
            JOIN ".BillItemTable::getInstance()->getTableName()." i ON t.bill_item_id = i.id
            JOIN ".BillElementTable::getInstance()->getTableName()." e ON i.element_id = e.id
            WHERE e.project_structure_id = ".$bill->id." AND t.tender_company_id = ".$tenderCompanyXref['id']." 
            AND i.project_revision_deleted_at IS NULL AND i.deleted_at IS NULL AND e.deleted_at IS NULL ORDER BY i.id");

            $stmt->execute();

            $result = $stmt->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_COLUMN);

            $result = array_map('reset', $result);

            return $result;
        }
        else
        {
            return false;
        }
        
    }

    protected function getRationalizedRates()
    {
        $pdo = $this->pdo;

        $bill = $this->bill;

        //Get Contractor Rates
        $stmt = $pdo->prepare("SELECT t.bill_item_id, COALESCE(t.rate, 0) AS value FROM ".TenderBillItemRationalizedRatesTable::getInstance()->getTableName()." t
        JOIN ".BillItemTable::getInstance()->getTableName()." i ON t.bill_item_id = i.id
        JOIN ".BillElementTable::getInstance()->getTableName()." e ON i.element_id = e.id
        WHERE e.project_structure_id = ".$bill->id." AND i.project_revision_deleted_at IS NULL 
        AND i.deleted_at IS NULL AND e.deleted_at IS NULL ORDER BY i.id");

        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_COLUMN);

        $result = array_map('reset', $result);

        return $result;
    }

}