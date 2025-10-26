<?php

class sfSubPackageExcelImport extends sfSubPackageExcelParser {

    public $subPackageCompany;

    public $bill;

    public function __construct(SubPackageCompany $subPackageCompany, ProjectStructure $bill)
    {
        $this->subPackageCompany  = $subPackageCompany;
        $this->bill               = $bill;
        $this->billColumnSettings = $this->bill->BillColumnSettings;
        $this->pdo                = $subPackageCompany->getTable()->getConnection()->getDbh();
    }

    public function startRead()
    {
        $this->loadBook(); // load objPHPExcel
        $this->setActiveSheet(); //For now we'll defaulted to 0

        $this->getSubPackageCredentials();

        $this->setupImportDataStructure();

        $this->iterateSheet();
        $this->endReader();
    }

    public function getSubPackageCredentials()
    {
        $this->subPackageCompanyId  = $this->getSubPackageCompanyIdFromExcel();
        $this->billId               = $this->getBillIdFromExcel();
        $this->billColumnSettingIds = $this->getBillColumnSettingIdsFromExcel();
        $this->billColumnsUnits     = $this->getColumnSettingsUnits();

        if ( $this->subPackageCompany->id != $this->subPackageCompanyId )
        {
            throw new InvalidArgumentException('Invalid Sub Package Company Id from imported Excel file.');
        }

        if ( $this->bill->id != $this->billId )
        {
            throw new InvalidArgumentException('Invalid Bill Id from imported Excel file.');
        }
    }

    public function setupImportDataStructure()
    {
        $this->data['subPackageInformation'] = array(
            'billColumnSettingIds' => unserialize($this->billColumnSettingIds),
        );

        return $this->data;
    }

    public function setItem()
    {
        $item = array(
            'id'      => $this->getItemId(),
            'rowType' => sfBuildspaceRFQExcelGenerator::ROW_TYPE_ITEM_TEXT,
        );

        switch(strtolower($this->getItemType()))
        {
            case strtolower(self::BUILDSOFT_ITEM_TYPE_HEADA_TEXT):
            case strtolower(self::BUILDSOFT_ITEM_TYPE_HEADB_TEXT):
            case strtolower(self::BUILDSOFT_ITEM_TYPE_HEADC_TEXT):
            case strtolower(self::BUILDSOFT_ITEM_TYPE_HEAD1_TEXT):
            case strtolower(self::BUILDSOFT_ITEM_TYPE_HEAD2_TEXT):
            case strtolower(self::BUILDSOFT_ITEM_TYPE_HEAD3_TEXT):
            case strtolower(self::BUILDSOFT_ITEM_TYPE_HEAD4_TEXT):
                //If Type Header
                $item['type'] = (string) BillItem::TYPE_HEADER;
                break;
            case strtolower(self::BUILDSOFT_ITEM_TYPE_NOID_TEXT):
            case strtolower(self::BUILDSOFT_ITEM_TYPE_NOIDI_TEXT):
            case strtolower(self::BUILDSOFT_ITEM_TYPE_NOIDN_TEXT):
                $item['type'] = (string) BillItem::TYPE_NOID;
                //case type NoID
                break;
            default:
                $rate              = $this->getValidatedRate();
                $billColumnsAmount = $this->getValidatedAmountByBillColumnSettings();

                $item['type']              = (string) BillItem::TYPE_WORK_ITEM;
                $item['rate-final_value']  = 0;
                $item['rate-value']        = 0;
                $item['rate-has_error']    = 0;
                $item['rate-msg']          = 0;
                $item['billColumnsAmount'] = $billColumnsAmount;

                if ( ! $rate['is_empty'] )
                {
                    $item['rate-final_value'] = $rate['value'];
                    $item['rate-value']       = $rate['value'];
                    $item['rate-has_error']   = $rate['has_error'];
                    $item['rate-msg']         = $rate['msg'];
                }

                $this->data['items'][] = $item;
                break;
        }

        unset($item);
    }

    public function getValidatedAmountByBillColumnSettings()
    {
        $billColumnsAmount = array();
        $currentRow        = $this->currentRow;
        $currCol           = $this->colRate;

        if(count($this->billColumnsUnits))
        {
            foreach ( $this->billColumnSettings as $column )
            {
                if ( ! array_key_exists($column['id'], $this->billColumnsUnits) ) continue;

                $currCol++;
                $currCol++;

                $billColumnAmount = $this->currentSheet->getCell($currCol.$this->currentRow)->getCalculatedValue();

                $billColumnsAmount[$column['id']] = (!$this->isEmpty($billColumnAmount)) ? $billColumnAmount : 0;

                unset($billColumnAmount, $column);
            }
        }

        return $billColumnsAmount;
    }

    public function getSubPackageCompanyIdFromExcel()
    {
        return $this->currentSheet->getCell(sfSubPackageExcelExporter::SUBPACKAGECOMPANYHIDDENID)->getCalculatedValue();
    }

    public function getBillIdFromExcel()
    {
        return $this->currentSheet->getCell(sfSubPackageExcelExporter::BILLHIDDENID)->getCalculatedValue();
    }

    public function getBillColumnSettingIdsFromExcel()
    {
        return $this->currentSheet->getCell(sfSubPackageExcelExporter::BILLCOLUMNSETTINGSHIDDENID)->getCalculatedValue();
    }

    public function getColumnSettingsUnits()
    {
        $billColumnSettingIds = array();

        foreach ( $this->billColumnSettings as $billColumnSetting )
        {
            $billColumnSettingIds[] = $billColumnSetting['id'];
        }

        $stmt = $this->pdo->prepare("SELECT r.bill_column_setting_id, COALESCE(COUNT(r.id), 0) FROM ".SubPackageTypeReferenceTable::getInstance()->getTableName()." r
        JOIN ".SubPackageCompanyTable::getInstance()->getTableName()." spc ON spc.sub_package_id = r.sub_package_id
        WHERE spc.id = ".$this->subPackageCompany->id." AND r.bill_column_setting_id IN (".implode(',', $billColumnSettingIds).") GROUP BY r.bill_column_setting_id");

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_COLUMN);
    }

}