<?php

class migrateSdprpVendorCategoriesTask extends sfBaseTask
{
    protected $sdprpCon;
    protected $eprojectCon;

    protected function configure()
    {
        $this->namespace           = 'sdprp';
        $this->name                = 'migrate-vendor-categories';
        $this->briefDescription    = '';
        $this->detailedDescription = <<<EOF
The [{$this->name}|INFO] task does things.
Call it with:

  [php symfony {$this->name}|INFO]
EOF;
    }

    protected function getSdprpConnection()
    {
        if(!$this->sdprpCon)
        {
            $databaseManager = new sfDatabaseManager($this->configuration);

            $this->sdprpCon = $databaseManager->getDatabase('sdprp_conn')->getConnection();
        }

        return $this->sdprpCon;
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
        $sdprpConn = $this->getSdprpConnection();
        
        $stmt = $sdprpConn->prepare("SELECT table_name FROM information_schema.tables WHERE table_schema='public'");

        $stmt->execute();

        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);

        foreach($tables as $tableName)
        {
            $stmt = $sdprpConn->prepare("SELECT column_name FROM information_schema.columns WHERE table_schema = 'public'
            AND LOWER(table_name) = '".strtolower($tableName)."'");

            $stmt->execute();

            $columns = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);

            foreach($columns as $columnName)
            {
                $columnNameConverted = str_replace(' ', '_', trim(strtolower($columnName)));
                $columnNameConverted = str_replace('/', '_', trim(strtolower($columnNameConverted)));
                $columnNameConverted = str_replace('-', '_', trim(strtolower($columnNameConverted)));
                $columnNameConverted = str_replace('(%)', '_percentage', trim(strtolower($columnNameConverted)));
                $columnNameConverted = str_replace('(s)', 's', trim(strtolower($columnNameConverted)));

                if($columnNameConverted != $columnName)
                {
                    $stmt = $sdprpConn->prepare('ALTER TABLE '.$tableName.' RENAME COLUMN "'.$columnName.'" TO '.$columnNameConverted.' ');

                    $stmt->execute();

                    $this->logSection('say', $tableName.' '.$columnName.' > '.$columnNameConverted);
                }
            }
        }

        //$this->migrateVendorCategoriesFromExcel();intentionally leave it here for references
        
        $this->migrateVendorCategoriesToEproject();
    }

    protected function migrateVendorCategoriesToEproject()
    {
        $sdprpConn = $this->getSdprpConnection();
        $eprojectConn = $this->getEprojectConnection();

        $stmt = $sdprpConn->prepare("SELECT * FROM vendor_categories ORDER BY new_code, level_1, level_2, level_3, level_4");

        $stmt->execute();

        $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $questionMarks = [];
        $contractGroupCategories = [];
        $vendorCategories = [];
        $vendorWorkCategories = [];

        $contractGroupCount = 1;
        $vendorCategoryCount = 1;
        $vendorWorkCategoryCount = 1;

        $uniqueRecords = [];
        $date = date('Y-m-d H:i:s');

        foreach($records as $record)
        {
            if(!array_key_exists($record['level_1'], $uniqueRecords))
            {
                $contractGroupCategory = [
                    $record['level_1'],
                    $date,
                    $date,
                    'VG'.sprintf('%06d', $contractGroupCount),
                    2
                ];

                $uniqueRecords[$record['level_1']] = $record['level_1'];

                $contractGroupCategories = array_merge($contractGroupCategories, $contractGroupCategory);

                $questionMarks[] = '('.implode(',', array_fill(0, count($contractGroupCategory), '?')).')';

                $contractGroupCount++;
            }

            if(!array_key_exists($record['level_1'], $vendorCategories))
            {
                $vendorCategories[$record['level_1']] = [];
            }

            if(!array_key_exists($record['level_2'], $vendorCategories[$record['level_1']]))
            {
                if(empty($record['level_3']))
                {
                    $vendorCategoryCode = $record['new_code'];
                }
                else
                {
                    $vendorCategoryCode = 'BSVCC'.sprintf('%06d', $vendorCategoryCount);

                    $vendorCategoryCount++;
                }

                $vendorCategories[$record['level_1']][$record['level_2']] = [
                    'code' => $vendorCategoryCode,
                    'wc'   => []
                ];
            }

            if(!empty($record['level_3']) && !array_key_exists($record['level_3'], $vendorCategories[$record['level_1']][$record['level_2']]['wc']))
            {
                if(empty($record['level_4']))
                {
                    $vendorWorkCategoryCode = $record['new_code'];
                }
                else
                {
                    $vendorWorkCategoryCode = 'BSWC'.sprintf('%07d', $vendorWorkCategoryCount);

                    $vendorWorkCategoryCount++;
                }

                $vendorCategories[$record['level_1']][$record['level_2']]['wc'][$record['level_3']] = [
                    'code' => $vendorWorkCategoryCode,
                    'swc'  => []
                ];
            }

            if(!empty($record['level_4']) && !array_key_exists($record['level_4'], $vendorCategories[$record['level_1']][$record['level_2']]['wc'][$record['level_3']]['swc']))
            {
                $vendorWorkSubCategoryCode = $record['new_code'];

                $vendorCategories[$record['level_1']][$record['level_2']]['wc'][$record['level_3']]['swc'][$record['level_4']] = $vendorWorkSubCategoryCode;
            }
        }

        if(!empty($contractGroupCategories))
        {
            try
            {
                $eprojectConn->beginTransaction();

                $contractGroupCategories = array_values($contractGroupCategories);

                $stmt = $eprojectConn->prepare("INSERT INTO contract_group_categories
                    (name, created_at, updated_at, code, type)
                    VALUES " . implode(',', $questionMarks)." RETURNING name, id");

                $stmt->execute($contractGroupCategories);

                $contractGroupReturnedRecords = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

                unset($contractGroupCategories);

                $insertVendorCategories = [];
                $questionMarks = [];

                foreach($vendorCategories as $contractGroupName => $records)
                {
                    foreach($records as $vendorCategoryName => $record)
                    {
                        if(array_key_exists($contractGroupName, $contractGroupReturnedRecords))
                        {
                            $vendorCategory = [$contractGroupReturnedRecords[$contractGroupName], $vendorCategoryName, $record['code'], $date, $date];

                            $insertVendorCategories = array_merge($insertVendorCategories, $vendorCategory);

                            $questionMarks[] = '('.implode(',', array_fill(0, count($vendorCategory), '?')).')';
                        }
                    }
                }

                if(!empty($insertVendorCategories))
                {
                    $stmt = $eprojectConn->prepare("INSERT INTO vendor_categories
                    (contract_group_category_id, name, code, created_at, updated_at)
                    VALUES " . implode(',', $questionMarks)." RETURNING CONCAT (name, '-', contract_group_category_id::text), id");

                    $stmt->execute($insertVendorCategories);

                    $returnedRecords = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

                    unset($insertVendorCategories);

                    $insertVendorWorkCategories = [];
                    $questionMarks = [];

                    $pivotTableIds = [];
                    $vendorWorkCategory = [];

                    foreach($vendorCategories as $contractGroupName => $vendorCategoryRecords)
                    {
                        foreach($vendorCategoryRecords as $vendorCategoryName => $records)
                        {
                            foreach($records['wc'] as $vendorWorkCategoryName => $vendorWorkCategoryRecords)
                            {
                                $vendorCategoryIdx = $vendorCategoryName.'-'.$contractGroupReturnedRecords[$contractGroupName];

                                if(array_key_exists($vendorCategoryIdx, $returnedRecords))
                                {
                                    if(!array_key_exists($returnedRecords[$vendorCategoryIdx], $pivotTableIds))
                                    {
                                        $pivotTableIds[$returnedRecords[$vendorCategoryIdx]] = [];
                                    }
                                   
                                    $pivotTableIds[$returnedRecords[$vendorCategoryIdx]][] = $vendorWorkCategoryName;

                                    if(!array_key_exists($vendorWorkCategoryName, $vendorWorkCategory))
                                    {
                                        $vendorWorkCategory[$vendorWorkCategoryName] = [$vendorWorkCategoryName, $vendorWorkCategoryRecords['code'], $date, $date];

                                        $insertVendorWorkCategories = array_merge($insertVendorWorkCategories, $vendorWorkCategory[$vendorWorkCategoryName]);

                                        $questionMarks[] = '('.implode(',', array_fill(0, count($vendorWorkCategory[$vendorWorkCategoryName]), '?')).')';
                                    }
                                }
                            }
                        }
                    }

                    $stmt = $eprojectConn->prepare("INSERT INTO vendor_work_categories
                    (name, code, created_at, updated_at)
                    VALUES " . implode(',', $questionMarks)." RETURNING name, id");

                    $stmt->execute($insertVendorWorkCategories);

                    $returnedRecords = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

                    $questionMarks = [];

                    $pivotRecords = [];
                    $pivotRecord = [];
                    foreach($pivotTableIds as $vendorCategoryId => $vendorWorkCategoryNames)
                    {
                        foreach($vendorWorkCategoryNames as $vendorWorkCategoryName)
                        {
                            $vendorWorkCategoryId = $returnedRecords[$vendorWorkCategoryName];

                            $pivotRecord = [$vendorCategoryId, $vendorWorkCategoryId, $date, $date];

                            $pivotRecords = array_merge($pivotRecords, $pivotRecord);

                            $questionMarks[] = '('.implode(',', array_fill(0, count($pivotRecord), '?')).')';
                        }
                    }

                    unset($pivotRecord);

                    $stmt = $eprojectConn->prepare("INSERT INTO vendor_category_vendor_work_category
                    (vendor_category_id, vendor_work_category_id, created_at, updated_at)
                    VALUES " . implode(',', $questionMarks));

                    $stmt->execute($pivotRecords);

                    $insertVendorWorkSubCategories = [];
                    $questionMarks = [];

                    foreach($vendorCategories as $contractGroupName => $vendorCategoryRecords)
                    {
                        foreach($vendorCategoryRecords as $vendorCategoryName => $records)
                        {
                            foreach($records['wc'] as $vendorWorkCategoryName => $vendorWorkCategoryRecords)
                            {
                                if(count($vendorWorkCategoryRecords['swc']))
                                {
                                    $vendorWorkCategoryId = $returnedRecords[$vendorWorkCategoryName];

                                    foreach($vendorWorkCategoryRecords['swc'] as $vendorWorkSubCategoryName => $vendorWorkSubCategoryCode)
                                    {
                                        $vendorWorkSubCategory = [$vendorWorkCategoryId, $vendorWorkSubCategoryName, $vendorWorkSubCategoryCode, $date, $date];

                                        $insertVendorWorkSubCategories = array_merge($insertVendorWorkSubCategories, $vendorWorkSubCategory);

                                        $questionMarks[] = '('.implode(',', array_fill(0, count($vendorWorkSubCategory), '?')).')';
                                    }
                                }
                            }
                        }
                    }

                    if(!empty($insertVendorWorkSubCategories))
                    {
                        $stmt = $eprojectConn->prepare("INSERT INTO vendor_work_subcategories
                        (vendor_work_category_id, name, code, created_at, updated_at)
                        VALUES " . implode(',', $questionMarks));

                        $stmt->execute($insertVendorWorkSubCategories);
                    }

                    unset($insertVendorWorkSubCategories);
                }

                $eprojectConn->commit();

                return $this->logSection('Vendor Categories Migration', 'Successfully migrated Vendor Categories and all the related info!');
            }
            catch(Exception $e)
            {
                $eprojectConn->rollBack();

                return $this->logSection('Vendor Categories Migration', $e);
            }
        }
    }

    protected function migrateVendorCategoriesFromExcel()
    {
        $sdprpConn = $this->getSdprpConnection();

        $stmt = $sdprpConn->prepare("CREATE TABLE IF NOT EXISTS vendor_categories (new_code VARCHAR(20) NOT NULL, old_code VARCHAR(20) NOT NULL, level_1 TEXT, level_2 TEXT, level_3 TEXT, level_4 TEXT, PRIMARY KEY(new_code, old_code));");
        
        $stmt->execute();

        $pathFile = DIRECTORY_SEPARATOR."Users".DIRECTORY_SEPARATOR."ahmadhazli".DIRECTORY_SEPARATOR."Downloads".DIRECTORY_SEPARATOR."Vendor_Category_Code.xlsx";
        $objReader = PHPExcel_IOFactory::createReader( "Excel2007" );
        $objReader->setReadDataOnly(true);

        $objPHPExcel = $objReader->load($pathFile);
        
        $objPHPExcel->setActiveSheetIndex(0);
        $sheet = $objPHPExcel->getActiveSheet();

        $highestRow = $sheet->getHighestRow(); 
        $highestColumn = $sheet->getHighestColumn();

        $newCodes = [];
        //  Loop through each row of the worksheet in turn
        for ($row = 4; $row <= $highestRow; $row++)
        { 
            //  Read a row of data into an array
            $rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row, NULL, TRUE, FALSE);
            
            $oldCode = trim($rowData[0][0]);
            $newCode = trim($rowData[0][1]);

            if(strtolower($newCode) != 'delete')
            {
                if(array_key_exists($oldCode, $newCodes))
                {
                    $lastChar = substr($oldCode, -1);
                    $lastInt = (int)$lastChar + 1;

                    $oldCode = rtrim($oldCode, $lastChar);
                    $oldCode .= $lastInt;
                }

                $newCodes[$oldCode] = $newCode;
            }
        }

        $objPHPExcel->setActiveSheetIndex(1);
        $sheet = $objPHPExcel->getActiveSheet();

        $highestRow = $sheet->getHighestRow(); 
        $highestColumn = $sheet->getHighestColumn();

        $newMaps = [];
        $questionMarks = [];

        $newKeyCount = 1;

        for ($row = 4; $row <= $highestRow; $row++)
        { 
            //  Read a row of data into an array
            $rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row, NULL, TRUE, FALSE);
            
            $newCode = trim($rowData[0][0]);

            $key = array_search($newCode, $newCodes);
            if(!$key)
            {
                $newKey = 'NVCC'.sprintf('%08d', $newKeyCount);
                $newKeyCount++;

                $key = $newKey;
            }

            $lvl1 = trim($rowData[0][1]);
            $lvl2 = trim($rowData[0][2]);
            $lvl3 = (trim($rowData[0][3])) ? trim($rowData[0][3]) : null;
            $lvl4 = (trim($rowData[0][4])) ? trim($rowData[0][4]) : null;

            $newMap = [$rowData[0][0], $key, $lvl1, $lvl2, $lvl3, $lvl4];

            $newMaps = array_merge($newMaps, $newMap);

            $questionMarks[] = '('.implode(',', array_fill(0, count($newMap), '?')).')';
        }

        if(!empty($newMaps))
        {
            $stmt = $sdprpConn->prepare("TRUNCATE TABLE vendor_categories");

            $stmt->execute();

            $stmt = $sdprpConn->prepare("INSERT INTO vendor_categories
                (new_code, old_code, level_1, level_2, level_3, level_4)
                VALUES " . implode(',', $questionMarks));

            $stmt->execute($newMaps);
        }

        $this->logSection('migration', 'Successfully inserted Vendor Categories');
    }
}