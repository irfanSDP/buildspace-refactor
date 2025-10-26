<?php

class sfImportScheduleOfRateBillExcelBuildspace
{

    protected $objPHPExcel;

    protected $buildSpaceSheets;

    public function __construct($filename)
    {
        try
        {
            $inputFileType = PHPExcel_IOFactory::identify($filename);
            $objReader     = PHPExcel_IOFactory::createReader($inputFileType);
            
            $objReader->setReadDataOnly(true);

            $this->objPHPExcel = $objReader->load($filename);

            $this->buildSpaceSheets = new SplFixedArray($this->objPHPExcel->getSheetCount());
        } catch (Exception $e)
        {
            throw new Exception('Error loading file "' . $filename . '": ' . $e->getMessage());
        }
    }

    public function process()
    {
        try
        {
            $this->validate();

            foreach ($this->objPHPExcel->getAllSheets() as $idx => $sheet)
            {
                $this->buildSpaceSheets[$idx] = new sfImportScheduleOfRateBillExcelBuildSpaceSheet($sheet);
            }
        } catch (Exception $e)
        {
            throw $e;
        }
    }

    private function validate()
    {
        $billInfo[sfBuildspaceBQExcelGenerator::EXCEL_PROPERTIES_TITLE]                  = null;
        $billInfo[sfBuildspaceBQExcelGenerator::EXCEL_PROPERTIES_DESCRIPTION]            = null;
        $billInfo[sfBuildspaceBQExcelGenerator::EXCEL_PROPERTIES_PROJECT_STRUCTURE_TYPE] = null;
        $billInfo[sfBuildspaceBQExcelGenerator::EXCEL_PROPERTIES_UNIT_TYPE]              = null;

        $count = 0;

        foreach ($this->objPHPExcel->getProperties()->getCustomProperties() as $customProperty)
        {
            if (array_key_exists($customProperty, $billInfo))
            {
                $count ++;
            }
        }

        if ($count != count($billInfo))
        {
            throw new Exception('Invalid excel file');
        }
    }

    public function saveAsNewBill(
        ProjectStructure $projectStructure,
        Array $selectElementIds,
        $withRate = false,
        Doctrine_Connection $conn = null
    ) {
        $conn = $conn ? $conn : Doctrine_Manager::getInstance()->getCurrentConnection();

        $billInfo[sfBuildspaceBQExcelGenerator::EXCEL_PROPERTIES_TITLE]                       = null;
        $billInfo[sfBuildspaceBQExcelGenerator::EXCEL_PROPERTIES_DESCRIPTION]                 = null;
        $billInfo[sfBuildspaceBQExcelGenerator::EXCEL_PROPERTIES_PROJECT_STRUCTURE_TYPE]      = null;
        $billInfo[sfBuildspaceBQExcelGenerator::EXCEL_PROPERTIES_BILL_TYPE]                   = null;
        $billInfo[sfBuildspaceBQExcelGenerator::EXCEL_PROPERTIES_BUILD_UP_QTY_ROUNDING_TYPE]  = null;
        $billInfo[sfBuildspaceBQExcelGenerator::EXCEL_PROPERTIES_BUILD_UP_RATE_ROUNDING_TYPE] = null;
        $billInfo[sfBuildspaceBQExcelGenerator::EXCEL_PROPERTIES_UNIT_TYPE]                   = null;
        $billInfo[sfBuildspaceBQExcelGenerator::EXCEL_PROPERTIES_ELEMENT_MARKUP_ENABLED]      = null;
        $billInfo[sfBuildspaceBQExcelGenerator::EXCEL_PROPERTIES_ITEM_MARKUP_ENABLED]         = null;
        $billInfo[sfBuildspaceBQExcelGenerator::EXCEL_PROPERTIES_MARKUP_ROUNDING_TYPE]        = null;

        foreach ($this->objPHPExcel->getProperties()->getCustomProperties() as $customProperty)
        {
            if (!array_key_exists($customProperty, $billInfo))
            {
                continue;
            }

            $billInfo[$customProperty] = $this->objPHPExcel->getProperties()->getCustomPropertyValue($customProperty);
        }

        $somBill = $this->createScheduleOfRateBill($billInfo, $projectStructure, $conn);

        $defaultPrintingSetting = SupplyOfMaterialLayoutSettingTable::getInstance()->find(1);//we just use supply of material layout setting
        $defaultSetting         = $defaultPrintingSetting->toArray();

        // get global default printing setting
        $billPhraseSetting = $defaultPrintingSetting->getSOMBillPhrase()->toArray();
        $headSettings      = $defaultPrintingSetting->getSOMBillHeadSettings()->toArray();

        ScheduleOfRateBillLayoutSettingTable::cloneExistingPrintingLayoutSettingsForBill($somBill, $defaultSetting, $billPhraseSetting, $headSettings);

        $this->saveIntoBill($somBill->ProjectStructure, $selectElementIds, $withRate, $conn);
    }

    private function createScheduleOfRateBill(
        Array $billInfo,
        ProjectStructure $projectStructure,
        Doctrine_Connection $conn = null
    ) {
        $conn   = $conn ? $conn : Doctrine_Manager::getInstance()->getCurrentConnection();
        $userId = sfContext::getInstance()->getUser()->getAttribute('user_id', null, 'sfGuardSecurityUser');

        $count = DoctrineQuery::create()->select('s.id, s.title')->from('ProjectStructure s')
            ->where('LOWER(s.title) = ?', strtolower($billInfo[sfBuildspaceBQExcelGenerator::EXCEL_PROPERTIES_TITLE]))
            ->andWhere('s.root_id = ?', $projectStructure->root_id)
            ->count();

        if ($count > 0)
        {
            $count += DoctrineQuery::create()->select('s.id, s.title')->from('ProjectStructure s')
                ->where('LOWER(s.title) LIKE ?',
                    strtolower($billInfo[sfBuildspaceBQExcelGenerator::EXCEL_PROPERTIES_TITLE]) . ' (%')
                ->andWhere('s.root_id = ?', $projectStructure->root_id)
                ->count();
        }

        $title       = $count > 0 ? $billInfo[sfBuildspaceBQExcelGenerator::EXCEL_PROPERTIES_TITLE] . " (" . ( $count ) . ")" : $billInfo[sfBuildspaceBQExcelGenerator::EXCEL_PROPERTIES_TITLE];
        $description = $billInfo[sfBuildspaceBQExcelGenerator::EXCEL_PROPERTIES_DESCRIPTION];
        $unitType    = $billInfo[sfBuildspaceBQExcelGenerator::EXCEL_PROPERTIES_UNIT_TYPE];

        $pdo = $conn->getDbh();

        $stmt = $pdo->prepare("INSERT INTO " . ProjectStructureTable::getInstance()->getTableName() . "
        (title, type, root_id, created_at, updated_at, created_by, updated_by)
        VALUES
        ('" . pg_escape_string(trim($title)) . "', " . (int) $billInfo[sfBuildspaceBQExcelGenerator::EXCEL_PROPERTIES_PROJECT_STRUCTURE_TYPE] . ", " . $projectStructure->root_id . ", NOW(), NOW(), " . $userId . ", " . $userId . ") RETURNING id");

        $stmt->execute();

        $returnedId = $stmt->fetch(PDO::FETCH_COLUMN, 0);

        $bill = Doctrine_Core::getTable('ProjectStructure')->find($returnedId);

        if ($projectStructure->node->isRoot() or $projectStructure->type == ProjectStructure::TYPE_LEVEL)
        {
            $bill->node->insertAsFirstChildOf($projectStructure);
        }
        else
        {
            $bill->node->insertAsNextSiblingOf($projectStructure);
        }

        $stmt = $pdo->prepare("INSERT INTO " . ScheduleOfRateBillTable::getInstance()->getTableName() . "
        (title, description, project_structure_id, unit_type, created_at, updated_at, created_by, updated_by)
        VALUES
        ('" . pg_escape_string(trim($title)) . "', '" . pg_escape_string(trim($description)) . "', " . $bill->id . ", " . (int) $unitType . ", NOW(), NOW(), " . $userId . ", " . $userId . ") RETURNING id");

        $stmt->execute();

        $scheduleOfRateBillReturnedId = $stmt->fetch(PDO::FETCH_COLUMN, 0);

        return ScheduleOfRateBillTable::getInstance()->find($scheduleOfRateBillReturnedId);
    }

    public function saveIntoBill(
        ProjectStructure $bill,
        Array $selectElementIds,
        $withRate = false,
        Doctrine_Connection $conn = null
    ) {
        $conn = $conn ? $conn : Doctrine_Manager::getInstance()->getCurrentConnection();

        if ($this->buildSpaceSheets->getSize() == count($selectElementIds))
        {
            $buildSpaceSheets = $this->buildSpaceSheets;
        }
        else
        {
            $buildSpaceSheets = new SplFixedArray(0);

            foreach ($this->buildSpaceSheets as $key => $sheet)
            {
                if (in_array($key, $selectElementIds))//element id is actually sheet index
                {
                    $buildSpaceSheets->setSize($buildSpaceSheets->getSize() + 1);
                    $buildSpaceSheets[$buildSpaceSheets->getSize() - 1] = $sheet;
                }
            }
        }

        $userId          = sfContext::getInstance()->getUser()->getAttribute('user_id', null, 'sfGuardSecurityUser');
        $elementPriority = SupplyOfMaterialElementTable::getMaxPriorityByBillId($bill->id) + 1;

        $stmt = new sfImportExcelStatementGenerator();

        $stmt->createInsert(
            ScheduleOfRateBillElementTable::getInstance()->getTableName(),
            array(
                'description',
                'project_structure_id',
                'priority',
                'created_at',
                'updated_at',
                'created_by',
                'updated_by'
            )
        );

        $items = array();

        foreach ($buildSpaceSheets as $sheet)
        {
            $elementInfo = $sheet->getElementInfo();

            $stmt->addRecord(array(
                pg_escape_string((string) $elementInfo['description']),
                $bill->id,
                $elementPriority,
                'NOW()',
                'NOW()',
                $userId,
                $userId
            ), (int) $elementInfo['id']);

            $items[$elementInfo['id']] = $sheet->getDataStructure()->toArray();

            $elementPriority ++;
        }

        $stmt->save();

        $importedElementToElementIds = $stmt->returningIds;

        $this->saveBillItems($bill, $items, $importedElementToElementIds, $withRate, $conn);
    }

    private function saveBillItems(
        ProjectStructure $bill,
        Array $items,
        Array $savedElementIds,
        $withRate = false,
        Doctrine_Connection $conn = null
    ) {
        $conn     = $conn ? $conn : Doctrine_Manager::getInstance()->getCurrentConnection();
        $userId   = sfContext::getInstance()->getUser()->getAttribute('user_id', null, 'sfGuardSecurityUser');
        $stmt     = new sfImportExcelStatementGenerator();
        $rootId   = null;
        $priority = 0;

        // will get existing unit first
        $unitGenerator = new ScheduleOfQuantityUnitGetter($conn);

        $availableUnits = $unitGenerator->getAvailableUnitOfMeasurements();

        $childrenForRoots = array();

        $stmt->createInsert(
            ScheduleOfRateBillItemTable::getInstance()->getTableName(),
            array(
                'element_id',
                'description',
                'type',
                'uom_id',
                'estimation_rate',
                'contractor_rate',
                'level',
                'root_id',
                'lft',
                'rgt',
                'priority',
                'created_at',
                'updated_at',
                'created_by',
                'updated_by'
            )
        );

        foreach ($items as $elementId => $itemData)
        {
            foreach ($itemData as $item)
            {
                if ($item['level'] == 0)
                {
                    $rootId = $item['id'];
                    $priority ++;
                }

                $childrenForRoots[$rootId][] = $item['id'];

                if (!is_numeric($item['estimation_rate']))
                {
                    $item['estimation_rate'] = 0;
                }

                if (!is_null($item['uom_symbol']) && strlen($item['uom_symbol']) > 0 && !array_key_exists(strtolower($item['uom_symbol']),
                        $availableUnits)
                )
                {
                    // we will insert the new uom symbol
                    $availableUnits = $unitGenerator->insertNewUnitOfMeasurementWithoutDimension($availableUnits,
                        $item['uom_symbol']);
                }

                $uomId = ( !is_null($item['uom_symbol']) && strlen($item['uom_symbol']) > 0 ) ? $availableUnits[strtolower($item['uom_symbol'])] : null;

                $stmt->addRecord(array(
                    $savedElementIds[(int) $elementId],
                    pg_escape_string((string) $item['description']),
                    (int) $item['type'],
                    $uomId,
                    $item['estimation_rate'],
                    $item['rate'],
                    $item['level'],
                    null,
                    $item['lft'],
                    $item['rgt'],
                    $priority,
                    'NOW()',
                    'NOW()',
                    $userId,
                    $userId,
                ), $item['id']);
            }
        }

        $stmt->save();

        $importedItemToItemIds = $stmt->returningIds;

        $this->reassignRootIds($childrenForRoots, $importedItemToItemIds);
    }

    private function reassignRootIds(Array $roots, Array $importedItemToItemIds, Doctrine_Connection $conn = null)
    {
        $conn = $conn ? $conn : Doctrine_Manager::getInstance()->getCurrentConnection();
        $pdo  = $conn->getDbh();

        $rootIds = array();
        $itemIds = array();

        foreach ($roots as $rootId => $root)
        {
            foreach ($root as $itemId)
            {
                if (array_key_exists($rootId, $importedItemToItemIds) && array_key_exists($itemId,
                        $importedItemToItemIds)
                )
                {
                    $itemIds[] = $importedItemToItemIds[$itemId];
                    $rootIds[] = $importedItemToItemIds[$rootId];
                }
            }
        }

        if ($rootIds && $itemIds)
        {
            $stmt = $pdo->prepare("UPDATE " . ScheduleOfRateBillItemTable::getInstance()->getTableName() . " SET root_id = cast(virtual_table.root_id AS int)
            FROM
            (SELECT UNNEST(ARRAY[" . implode(",", $itemIds) . "]) AS id,
                UNNEST(ARRAY['" . implode("','", $rootIds) . "']) AS root_id
            ) AS virtual_table WHERE " . ScheduleOfRateBillItemTable::getInstance()->getTableName() . ".id = virtual_table.id
            AND " . ScheduleOfRateBillItemTable::getInstance()->getTableName() . ".root_id IS NULL");

            $stmt->execute();
        }
    }

    public function getPreviewFormatData()
    {
        $data = array();

        foreach ($this->buildSpaceSheets as $key => $sheet)
        {
            $dataStructure = $sheet->getDataStructure()->toArray();

            foreach ($dataStructure as $idx => $item)
            {
                $dataStructure[$idx]['type']              = (string) $item['type'];
                $dataStructure[$idx]['rate-final_value']  = $item['rate'];
                $dataStructure[$idx]['rate-value']        = $item['rate'];
                $dataStructure[$idx]['rate-has_formula']  = false;
                $dataStructure[$idx]['rate-has_build_up'] = false;

                unset( $dataStructure[$idx]['rate'] );
            }

            $data['elements'][$key] = array(
                'info'  => $sheet->getElementInfo(),
                'items' => $dataStructure
            );
        }

        return $data;
    }
}

class sfImportScheduleOfRateBillExcelBuildSpaceSheet
{

    private $sheet;
    private $highestRow;
    private $highestColumn;
    private $excelProperties;

    private $firstCol;
    private $colDescription;
    private $colUnit;
    private $colRate;
    private $lastCol;

    private $dataStructure;
    private $elementInfo;

    const ROW_BILL_REF = 0;
    const ROW_DESCRIPTION = 1;
    const ROW_UNIT = 2;

    public function __construct(PHPExcel_Worksheet $sheet)
    {
        $this->excelProperties = $sheet->getParent()->getProperties();
        $this->sheet           = $sheet;
        $this->highestRow      = $sheet->getHighestRow();
        $this->highestColumn   = $sheet->getHighestColumn();

        $this->dataStructure = new SplFixedArray(0);

        $this->prepare();

        $this->createDataStructure();
    }

    private function prepare()
    {
        $this->colRate = 5;

        $this->firstCol       = $this->colItem = Utilities::generateCharFromNumber(2, true);
        $this->colDescription = Utilities::generateCharFromNumber(3, true);
        $this->colUnit        = Utilities::generateCharFromNumber(4, true);

        //each column setting has qty and rate and at the end we need to add 2 more columns for total qty and total amount (multitype columns)
        $this->lastCol = $this->colRate;
    }

    private function createDataStructure()
    {
        $startRow = 5;

        for ($row = $startRow; $row <= $this->highestRow; $row ++)
        {
            $rangeArray = $this->sheet->rangeToArray($this->firstCol . $row . ':' . $this->highestColumn . $row, null,
                false, false, false);

            $isEmpty = false;

            //to check either all columns in row are empty or not. We skip if all columns in row are empty
            foreach ($rangeArray[0] as $arr)
            {
                if (empty( $arr ))
                {
                    $isEmpty = true;
                }
                else
                {
                    $isEmpty = false;
                    break;//even if one column is not empty we will insert it into data struct
                }
            }

            if (!$isEmpty)
            {
                $this->dataStructure->setSize($this->dataStructure->getSize() + 1);
                $this->dataStructure[$this->dataStructure->getSize() - 1] = $rangeArray[0];
            }
        }

        $this->massageDataStructure();
    }

    private function massageDataStructure()
    {
        $rateIdx     = $this->colRate - 2;
        $colRowType  = $rateIdx + 1;
        $colItemType = $colRowType + 1;
        $colLft      = $colItemType + 1;
        $colRgt      = $colLft + 1;
        $colLevel    = $colRgt + 1;
        $colEstimationRate = $colLevel + 1;

        $items = new SplFixedArray(0);

        foreach ($this->dataStructure as $idx => $data)
        {
            $item = array(
                'id'          => $idx . '-' . $this->sheet->getParent()->getIndex($this->sheet),
                'description' => preg_replace('!\s+!', ' ', trim($data[self::ROW_DESCRIPTION]))
            );

            switch (strtolower($data[$colRowType]))
            {
                case 'element':
                    if (!is_array($this->elementInfo))
                    {
                        $item['id']        = $this->sheet->getParent()->getIndex($this->sheet);
                        $this->elementInfo = $item;
                    }
                    break;
                case 'item':
                    $item['bill_ref']        = $data[self::ROW_BILL_REF];
                    $item['uom_symbol']      = $data[self::ROW_UNIT];
                    $item['rate']            = !is_numeric($data[$rateIdx]) ? 0.00 : $data[$rateIdx];
                    $item['estimation_rate'] = $data[$colEstimationRate];
                    $item['type']            = $data[$colItemType];
                    $item['lft']             = $data[$colLft];
                    $item['rgt']             = $data[$colRgt];
                    $item['level']           = $data[$colLevel];

                    $items->setSize($items->getSize() + 1);
                    $items[$items->getSize() - 1] = $item;

                    break;
                default:
                    break;
            }
        }

        $this->elementInfo['count'] = $items->getSize();
        $this->elementInfo['error'] = 0;

        $this->dataStructure = $items;

        unset( $items );
    }

    public function getElementInfo()
    {
        return $this->elementInfo;
    }

    public function getDataStructure()
    {
        return $this->dataStructure;
    }

}