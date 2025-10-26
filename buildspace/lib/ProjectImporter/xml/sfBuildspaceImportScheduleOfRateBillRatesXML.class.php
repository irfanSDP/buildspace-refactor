<?php

class sfBuildspaceImportScheduleOfRateBillRatesXML extends sfBuildspaceXMLParser
{

    public $project;
    public $company;
    public $billId;
    public $userId;
    public $companyId;
    public $projectId;
    public $projectUniqueId;
    public $buildspaceId;

    protected $billSetting;
    protected $elements;
    protected $items;
    protected $tenderCompanyInfo;

    public function __construct(
        $userId,
        $project,
        $company,
        $tenderCompanyInfo = array(),
        $filename = null,
        $uploadPath = null,
        $extension = null,
        $deleteFile = null
    ) {
        $this->pdo = ProjectStructureTable::getInstance()->getConnection()->getDbh();

        $this->userId = $userId;

        $this->project = $project;

        $this->company = $company;

        parent::__construct($filename, $uploadPath, $extension, $deleteFile);

        $this->extractData();

        if ($project)
        {
            $this->projectId = $project['id'];

            $this->projectUniqueId = $project['MainInformation']['unique_id'];
        }

        if ($company)
        {
            $this->companyId = $company['id'];
        }

        if (!$tenderCompanyInfo)
        {
            $this->tenderCompanyInfo = ( $this->getTenderCompany() ) ? $this->getTenderCompany() : false;
        }
        else
        {
            $this->tenderCompanyInfo = $tenderCompanyInfo;
        }
    }

    public function extractData()
    {
        parent::read();

        $xmlData = parent::getProcessedData();

        $this->buildspaceId = $xmlData->attributes()->buildspaceId;

        $this->billId = (int) $xmlData->attributes()->billId;

        $this->elements = ( $xmlData->{sfBuildspaceExportBillXML::TAG_ELEMENTS}->count() > 0 ) ? $xmlData->{sfBuildspaceExportBillXML::TAG_ELEMENTS}->children() : false;

        $this->items = ( $xmlData->{sfBuildspaceExportBillXML::TAG_ITEMS}->count() > 0 ) ? $xmlData->{sfBuildspaceExportBillXML::TAG_ITEMS}->children() : false;

    }

    public function process()
    {
        if (!$this->tenderCompanyInfo)
        {
            return;
        }

        if ($this->items)
        {
            $this->processItems();
        }
    }

    public function processItems()
    {
        if (!$this->tenderCompanyInfo)
        {
            return;
        }

        $insertedData = array();

        $estimationRates = $this->getEstimationRatesFromItems();

        foreach ($this->items as $item)
        {
            if (!$item->id)
            {
                return;
            }

            $itemId = (int) $item->id;

            unset( $item->type, $item->origin_id );

            $estimationRate = array_key_exists($itemId, $estimationRates) ? $estimationRates[$itemId] : 0;

            $data = new stdClass();

            $data->tender_company_id             = $this->tenderCompanyInfo['id'];
            $data->schedule_of_rate_bill_item_id = $itemId;
            $data->contractor_rate               = (double) $item->contractor_rate;
            $data->estimation_rate               = $estimationRate;
            $data->difference                    = $estimationRate - $data->contractor_rate;
            $data->created_at                    = 'NOW()';
            $data->updated_at                    = 'NOW()';
            $data->created_by                    = $this->userId;
            $data->updated_by                    = $this->userId;

            $dataAndStructure = parent::generateArrayOfSingleData($data, true);

            $stmt = new sfImportStatementGenerator();

            $stmt->createInsert(
                TenderScheduleOfRateTable::getInstance()->getTableName(),
                $dataAndStructure['structure']
            );

            $stmt->addRecord($dataAndStructure['data']);

            $stmt->save();

            $insertedData[] = array(
                'tender_company_id'             => $data->tender_company_id,
                'schedule_of_rate_bill_item_id' => $data->schedule_of_rate_bill_item_id,
                'contractor_rate'               => $data->contractor_rate,
                'estimation_rate'               => $data->estimation_rate,
                'difference'                    => $data->difference
            );

            unset( $data, $item );
        }

        if ($insertedData)
        {
            TenderScheduleOfRateBillItemRateLogTable::insertBatchLog($insertedData, 'IMPORT');
        }
    }

    protected  function getEstimationRatesFromItems()
    {
        $itemsIds = new SplFixedArray(0);
        $result = array();

        foreach ($this->items as $item)
        {
            if (!$item->id)
            {
                continue;
            }

            $itemsIds->setSize($itemsIds->getSize()+1);
            $itemsIds[$itemsIds->getSize()-1] = (int) $item->id;
        }

        if($itemsIds->getSize() > 0)
        {
            $stmt = $this->pdo->prepare("SELECT DISTINCT i.id AS id, COALESCE(i.estimation_rate, 0) AS rate
                    FROM ".ScheduleOfRateBillItemTable::getInstance()->getTableName()." i
                    WHERE i.id IN (".implode(',', $itemsIds->toArray()).")
                    AND i.type = ".ScheduleOfRateBillItem::TYPE_WORK_ITEM." AND i.deleted_at IS NULL");

            $stmt->execute();

            $result = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        }


        return $result;
    }

    public function getTenderCompany()
    {
        if (!$this->companyId && !$this->projectId)
        {
            return;
        }

        $query = DoctrineQuery::create()
            ->select('tc.id, tc.project_structure_id, tc.company_id, tc.total_amount')
            ->from('TenderCompany tc')
            ->where('tc.project_structure_id = ?', $this->projectId)
            ->andWhere('tc.company_id = ?', $this->companyId)
            ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY);

        return $tenderCompanyInfo = ( $query->count() > 0 ) ? $query->fetchOne() : false;
    }

}