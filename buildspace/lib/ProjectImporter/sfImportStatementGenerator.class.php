<?php
class sfImportStatementGenerator
{
    public $tableName;
    public $tblClass;
    public $sql;
    public $rawSql;
    public $preQuery;
    public $stmt;
    public $records = array();
    public $returningIds;

    function __construct() 
    {
        $this->pdo = Doctrine_Manager::getInstance()->connection()->getDbh();
    }

    public function createInsert($tblName, array $records)
    {
        if(!(count($records) > 0))
            return;

        $this->resetRecord();

        $this->tableName = strtolower($tblName);

        $fields = implode(',', $records);
        
        $this->preQuery = "INSERT INTO ".$this->tableName.' ('.$fields.')';
    }

    public function updateRecord($tblName, array $updateClause, array $fieldsAndValues)
    {
        if(!(count($fieldsAndValues) > 0) || !(count($updateClause) == 1))
            return;

        $this->resetRecord();

        $this->tableName = strtolower($tblName);
        
        $preQuery = "UPDATE ".$this->tableName.' '.$this->generateSetValue($fieldsAndValues);

        $preQuery.= ' '.$this->generateUpdateClause( $fieldsAndValues, $updateClause );

        $this->stmt = $this->pdo->prepare($preQuery);

        $this->stmt->execute($fieldsAndValues);
    }

    public function generateSetValue(array $fieldsAndValues)
    {
        $qString = 'SET';

        $fPart = array();

        foreach($fieldsAndValues as $field => $value)
        {
            array_push($fPart, $field.' = :'.$field);
        }

        $qString .=" ".implode(',', $fPart);

        return $qString;
    }

    public function generateUpdateClause(Array &$existingFieldAndValues, array $fieldsAndValues)
    {
        //Currently Supports only single where clause
        $qString = 'WHERE ';

        foreach($fieldsAndValues as $field => $value)
        {
            $qString.=$field.' = :'.$field;

            $existingFieldAndValues[$field] = $value;
        }

        return $qString;
    }

    public function resetRecord()
    {
        $this->tableName = null;
        $this->sql = $this->rawSql = $this->preQuery = null;
        $this->returningIds = null;
        $this->records = new SplFixedArray(0);
        $this->stmt = null;
    }

    public function addRecord($record = array())
    {
        if(!(count($record) > 0))
            return;

        Utilities::SplFixedArrayPush($this->records, $record);
    }

    public function generateSql()
    {
        $pdo = Doctrine_Manager::getInstance()->connection()->getDbh();

        $stmt = $pdo->prepare("SELECT EXISTS (SELECT 1 
        FROM information_schema.columns 
        WHERE table_schema='public' AND table_name=:tableName AND column_name='id')");
        
        $stmt->execute([
            'tableName' => $this->tableName
        ]);

        $hasIdColumn = $stmt->fetch(PDO::FETCH_COLUMN, 0);

        $query = $this->preQuery;

        $fieldCount = count($this->records[0]);

        $fPart = array_fill(0, $fieldCount, '?');

        $qPart = array_fill(0, count($this->records), "(".implode(',', $fPart).")");

        $returnIdSql = ($hasIdColumn) ? " RETURNING id" : "";
        
        $query .=  ' VALUES '.implode(",",$qPart).' '.$returnIdSql;

        $this->rawSql = $query;
    }

    public function bindDataQuery()
    {
        $this->stmt = $this->pdo->prepare($this->rawSql);

        $i = 1;

        foreach($this->records as $item)
        { //bind the values one by one
           foreach($item as $k => $fieldValue)
           {
                $this->stmt->bindValue($i++, $item[$k]);
           }
        }
    }

    public function massageReturningIds($returningIds)
    {
        $ids = [];

        foreach($returningIds as $v)
        {
            if(array_key_exists('id', $v))
            {
                $ids[] = $v['id'];
            }
        }

        return $ids;
    }

    public function getReturningIds()
    {
        $this->returningIds = $this->massageReturningIds($this->stmt->fetchAll());
    }

    public function getSql()
    {
        $this->generateSql();

        return $this->sql;
    }

    public function save()
    {
        if($this->records->count() < 1) return;

        $fieldCount = count($this->records[0]);

        // chunkSize*count(fields) cannot be more than MAX_PDO_QUERY_PARAMETERS
        $chunkSize = floor(Constants::MAX_PDO_QUERY_PARAMETERS/$fieldCount);

        $recordChunks = Utilities::arrayChunkSPL($this->records, $chunkSize);
        $returningIds = array();

        foreach($recordChunks as $recordChunk)
        {
            $this->records = $recordChunk;

            $this->generateSql();
            $this->bindDataQuery();
            $this->stmt->execute();
            $this->getReturningIds();

            $returningIds = array_merge($returningIds, $this->returningIds);
        }

        $this->returningIds = $returningIds;
    }

    public function rebuildTree($parentId, $left, $firstRootId)
    {
        $right = $left+1;

        $children = DoctrineQuery::create()
            ->select('i.id, i.root_id, i.lft, i.rgt')
            ->from($this->tblClass.' i')
            ->where('i.root_id = ?', $parentId)
            ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
            ->execute();

        if($children)
        {
            foreach($children as $child)
            {
                //Recursive Rebuild Tree
                $right = $this->rebuildTree($child['id'], $right, $firstRootId);

                unset($child);
            }

            unset($children);

            //Update all its child to follow Root
            //Since All Child Item will be using First (Level 0 ) Root Id
            $this->updateChildRootId($firstRootId, $parentId);
        }

        $stmt = $this->pdo->prepare("UPDATE ".$this->tableName." SET
            lft = ".$left.", rgt = ".$right." WHERE id = ".$parentId." AND deleted_at IS NULL");

        $stmt->execute();

        return $right+1;
    }

    public function updateChildRootId($firstRootId, $parentId)
    {
        $stmt = $this->pdo->prepare("UPDATE ".$this->tableName." SET
            root_id = ".$firstRootId." WHERE root_id = ".$parentId." AND deleted_at IS NULL");

        $stmt->execute();
    }

    public function updateRootId($itemId = null, $rootId = null)
    {
        $stmt = $this->pdo->prepare("UPDATE ".$this->tableName." SET
            root_id = ".$rootId." WHERE id = ".$itemId." AND deleted_at IS NULL");

        $stmt->execute();
    }

    public function setAsRoot($tblName = null, $itemId = null)
    {
        if(!$itemId)
            return;

        if($tblName)
            $this->tableName = strtolower($tblName);

        $this->updateRootId($itemId, $itemId);
    }

    /*
        ::Experimental

        Function to Rebuild back the tree left right structure 
        after raw sql insert

        Requirement : at least all item to be rebuild must have root Id pointed to
        the right parent, then we can rebuild back their left & right structure

        Should be compatible to BQ/Bill/Library Type Item structure
    */

    public function rebuildItemTreeStructureBySorTradeIds($tblClass, $tableName = null, array $tradeIds)
    {
        if(!($tableName && $tblClass))
            return; 

        $this->tableName = strtolower($tableName);
        $this->tblClass = $tblClass;

        $this->rebuildItemTree($tblClass, $tradeIds, 'ScheduleOfRateTrade');
    }

    public function rebuildItemTreeStructureByResourceTradeIds($tblClass, $tableName = null, array $tradeIds)
    {
        if(!($tableName && $tblClass))
            return; 

        $this->tableName = strtolower($tableName);
        $this->tblClass = $tblClass;

        $this->rebuildItemTree($tblClass, $tradeIds, 'ResourceTrade');
    }

    public function rebuildItemTreeStructureByElementIds($tblClass, $tableName = null, array $elementIds)
    {
        if(!($tableName && $tblClass))
            return; 

        $this->tableName = strtolower($tableName);
        $this->tblClass = $tblClass;

        $this->rebuildItemTree($tblClass, $elementIds, 'Element');
    }

    private function rebuildItemTree($tblClass, Array $tradeIds, $type)
    {
        switch($type)
        {
            case "ScheduleOfRateTrade":
                $columnToQuery = 'trade_id';
                break;
            case "ResourceTrade":
                $columnToQuery = 'resource_trade_id';
                break;
            case "Element":
                $columnToQuery = 'element_id';
                break;
            default:
                throw new Exception('Invalid type supplied');
        }

        $rootItems = DoctrineQuery::create()
            ->select('i.id, i.root_id, i.lft, i.rgt')
            ->from($tblClass.' i')
            ->where('i.'.$columnToQuery.' IN ?', array( $tradeIds ) )
            ->andWhere('i.level = 0')
            ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
            ->execute();

        if($rootItems)
        {
            foreach($rootItems as $item)
            {
                $this->rebuildTree($item['id'], $item['lft'], $item['id']);
                $this->updateRootId($item['id'], $item['id']);

                unset($item);
            }

            unset($rootItems);
        }
    }
}
