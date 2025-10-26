<?php
class sfImportExcelStatementGenerator
{

    public $tableName;
    public $tblClass;
    public $sql;
    public $rawSql;
    public $preQuery;
    public $stmt;
    public $records;
    public $originalIds;
    public $returningIds;

    private $conn;

    function __construct(Doctrine_Connection $conn=null)
    {
        $this->conn = $conn ? $conn : Doctrine_Manager::getInstance()->getCurrentConnection();

        $this->pdo = $this->conn->getDbh();
        $this->records = new SplFixedArray(0);
        $this->originalIds = new SplFixedArray(0);
    }

    public function createInsert($tblName, array $records)
    {
        if(!(count($records) > 0))
            return;

        $this->resetRecord();

        $this->tableName = $tblName;

        $fields = implode(',', $records);

        $this->preQuery = "INSERT INTO ".$this->tableName.' ('.$fields.')';
    }

    public function createUpdate($tblName, array $records)
    {
        if(!(count($records) > 0))
            return;

        $this->resetRecord();

        $this->tableName = $tblName;

        $fields = implode(',', $records);

        $this->preQuery = "UPDATE ".$this->tableName.' ('.$fields.')';
    }

    public function resetRecord()
    {
        $this->tableName = null;
        $this->sql = $this->rawSql = $this->preQuery = null;
        $this->returningIds = null;
        $this->records = new SplFixedArray(0);
        $this->originalIds = new SplFixedArray(0);
        $this->stmt = null;
    }

    public function addRecord($record = array(), $originalId = null)
    {
        if(!(count($record) > 0))
            return;

        $latestSize = $this->records->getSize() + 1;

        $this->records->setSize($latestSize);

        $this->records[$latestSize -1] = $record;

        if(!is_null($originalId))
        {
            $latestSize = $this->originalIds->getSize() + 1;

            $this->originalIds->setSize($latestSize);

            $this->originalIds[$latestSize -1] = $originalId;
        }
    }

    public function generateSql()
    {
        if(empty($this->records->count()))
            return;
        
        $query = $this->preQuery;

        $this->fieldCount = count($this->records[0]);

        $fPart = array_fill(0, $this->fieldCount, '?');

        $qPart = array_fill(0, $this->records->count(), "(".implode(',', $fPart).")");

        $query .=  ' VALUES '.implode(",",$qPart).' RETURNING id';

        $this->rawSql = $query;

        unset($query);
    }

    public function bindDataQuery()
    {
        $this->stmt = $this->conn->prepare($this->rawSql);

        $i = 1;
        foreach($this->records as $item)
        {
            //bind the values one by one
            foreach($item as $k => $fieldValue)
            {
                $this->stmt->bindValue($i++, $item[$k]);
            }

            unset($item);
        }
    }

    public function massageReturningIds(SplFixedArray $returnedIds)
    {
        $this->returningIds = array();
        $useOriginalIds = false;

        if($this->originalIds->count() == $returnedIds->count())
        {
            $useOriginalIds = true;
        }

        foreach($returnedIds as $k => $v)
        {
            if($useOriginalIds)
            {
                $this->returningIds[$this->originalIds[$k]] = $v;
            }
            else
            {
                $this->returningIds[] = $v;
            }
        }
    }

    public function setReturningIds()
    {
        $rowCount = $this->stmt->rowCount();
        $returnedIds = new SplFixedArray($rowCount);

        while(($row = $this->stmt->fetch(\PDO::FETCH_ASSOC)) !== false)
        {
            $returnedIds[$returnedIds->key()] = $row['id'];
            $returnedIds->next();
        }

        $this->massageReturningIds($returnedIds);
    }

    public function getSql()
    {
        $this->generateSql();
        return $this->sql;
    }

    public function save()
    {
        if(empty($this->records->count()))
            return;
        
        $fieldCount = count($this->records[0]);
        $tmpRecords= $this->records;

        $totalQueryParams = $fieldCount * $this->records->count();

        if($totalQueryParams > Constants::MAX_PDO_QUERY_PARAMETERS)
        {
            $maxPerChunk = floor(Constants::MAX_PDO_QUERY_PARAMETERS / $fieldCount);

            $chunks = Utilities::arrayChunkSPL($this->records, $maxPerChunk);

            $returnedIds = new SplFixedArray(0);

            foreach($chunks as $chunk)
            {
                $this->records = $chunk;//override $this->records so it can be used by functions to save per chunk

                $this->generateSql();
                $this->bindDataQuery();
                $this->stmt->execute();

                $rowCount = $this->stmt->rowCount();
                $returnedIds->setSize($returnedIds->getSize() + $rowCount);

                while(($row = $this->stmt->fetch(\PDO::FETCH_ASSOC)) !== false)
                {
                    $returnedIds[$returnedIds->key()] = $row['id'];
                    $returnedIds->next();
                }
            }

            $this->records = $tmpRecords;//set back $this->records to its own original values;

            $tmpRecords = null;
            unset($tmpRecords);

            $this->massageReturningIds($returnedIds);
        }
        else
        {
            $this->generateSql();
            $this->bindDataQuery();
            $this->stmt->execute();
            $this->setReturningIds();
        }

        unset($this->stmt, $this->records);
    }

    public function rebuildTree($parentId, $left, $firstRootId, &$rootIdToItemIds)
    {
        $right = $left+1;

        if(array_key_exists($parentId, $rootIdToItemIds) && count($rootIdToItemIds[$parentId]))
        {
            $stmt = $this->pdo->prepare("SELECT i.id, i.root_id, i.lft, i.rgt FROM ".$this->tableName." i
            WHERE i.id IN (".implode(',', $rootIdToItemIds[$parentId]).")");

            $stmt->execute();

            while ($child = $stmt->fetch(PDO::FETCH_ASSOC))
            {
                $right = $this->rebuildTree($child['id'], $right, $firstRootId, $rootIdToItemIds);
            }
        }

        $stmt = $this->conn->prepare("UPDATE ".$this->tableName." SET
            lft = ".$left.", rgt = ".$right." WHERE id = ".$parentId." AND deleted_at IS NULL");

        $stmt->execute();

        return $right+1;
    }

    public function updateChildRootId($firstRootId, $parentId)
    {
        $stmt = $this->conn->prepare("UPDATE ".$this->tableName." SET
            root_id = ".$firstRootId." WHERE root_id = ".$parentId." AND deleted_at IS NULL");

        $stmt->execute();
    }

    public function updateRootId($itemId = null, $rootId = null)
    {
        $stmt = $this->conn->prepare("UPDATE ".$this->tableName." SET
            root_id = :rootId WHERE id = :itemId AND deleted_at IS NULL");

        $stmt->execute(array(
            'itemId' => $itemId,
            'rootId' => $rootId
        ));
    }

    public function setAsRoot($tblName = null, $itemId = null){
        if(!$itemId)
            return;

        if($tblName)
            $this->tableName = $tblName;

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

    public function rebuildItemTreeStructureBySorTradeIds($tblClass, $tableName = null, array $tradeIds, $rootIdToItemIds = array()){
        if(!($tableName && $tblClass))
            return;

        $this->tableName = $tableName;
        $this->tblClass = $tblClass;

        $rootItems = DoctrineQuery::create()
            ->select('i.id, i.root_id, i.lft, i.rgt')
            ->from($this->tblClass.' i')
            ->where('i.trade_id IN ?', array( $tradeIds ) )
            ->andWhere('i.level = 0')
            ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
            ->execute();

        foreach($rootItems as $item)
        {
            $this->rebuildTree($item['id'], $item['lft'], $item['id'], $rootIdToItemIds);
        }
    }

    public function rebuildItemTreeStructureByResourceTradeIds($tblClass, $tableName = null, array $tradeIds, $rootIdToItemIds = array()){
        if(!($tableName && $tblClass))
            return;

        $this->tableName = $tableName;
        $this->tblClass = $tblClass;

        $rootItems = DoctrineQuery::create()
            ->select('i.id, i.root_id, i.lft, i.rgt')
            ->from($this->tblClass.' i')
            ->where('i.resource_trade_id IN ?', array( $tradeIds ) )
            ->andWhere('i.level = 0')
            ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
            ->execute();

        foreach($rootItems as $item)
        {
            $this->rebuildTree($item['id'], $item['lft'], $item['id'], $rootIdToItemIds);
        }
    }

    public function rebuildItemTreeStructureByElementIds($tblClass, $tableName = null, array $elementIds, $rootIdToItemIds = array()){
        if(!($tableName && $tblClass))
            return;

        $this->tableName = $tableName;
        $this->tblClass = $tblClass;

        $rootItems = DoctrineQuery::create()
            ->select('i.id, i.root_id, i.lft, i.rgt')
            ->from($this->tblClass.' i')
            ->where('i.element_id IN ?', array( $elementIds ) )
            ->andWhere('i.level = 0')
            ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
            ->execute();

        foreach($rootItems as $item)
        {
            $this->rebuildTree($item['id'], $item['lft'], $item['id'], $rootIdToItemIds);
        }
    }
}
