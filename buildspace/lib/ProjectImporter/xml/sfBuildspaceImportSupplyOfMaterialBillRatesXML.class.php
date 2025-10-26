<?php

class sfBuildspaceImportSupplyOfMaterialBillRatesXML extends sfBuildspaceXMLParser
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

        foreach ($this->items as $item)
        {
            if (!$item->id)
            {
                return;
            }

            $itemId = (int) $item->id;

            unset( $item->type, $item->origin_id );

            $supplyRate           = (double) $item->supply_rate;
            $contractorSupplyRate = (double) $item->contractor_supply_rate;
            $estimatedQty         = (double) $item->estimated_qty;
            $percentageOfWastage  = (double) $item->percentage_of_wastage;
            $difference           = (double) $item->difference;
            $amount               = (double) $item->amount;

            $data = new stdClass();

            $data->tender_company_id          = $this->tenderCompanyInfo['id'];
            $data->supply_of_material_item_id = $itemId;
            $data->supply_rate                = $supplyRate;
            $data->contractor_supply_rate     = $contractorSupplyRate;
            $data->estimated_qty              = $estimatedQty;
            $data->percentage_of_wastage      = $percentageOfWastage;
            $data->difference                 = $difference;
            $data->amount                     = $amount;
            $data->created_at                 = 'NOW()';
            $data->updated_at                 = 'NOW()';
            $data->created_by                 = $this->userId;
            $data->updated_by                 = $this->userId;

            $dataAndStructure = parent::generateArrayOfSingleData($data, true);

            $stmt = new sfImportStatementGenerator();

            $stmt->createInsert(
                TenderSupplyOfMaterialRateTable::getInstance()->getTableName(),
                $dataAndStructure['structure']
            );

            $stmt->addRecord($dataAndStructure['data']);

            $stmt->save();

            $insertedData[] = array(
                'tender_company_id'          => $data->tender_company_id,
                'supply_of_material_item_id' => $data->supply_of_material_item_id,
                'supply_rate'                => $data->supply_rate,
                'contractor_supply_rate'     => $data->contractor_supply_rate,
                'estimated_qty'              => $data->estimated_qty,
                'percentage_of_wastage'      => $data->percentage_of_wastage,
                'difference'                 => $data->difference,
                'amount'                     => $data->amount,
            );

            unset( $data, $item );
        }

        if ($insertedData)
        {
            TenderSupplyOfMaterialItemRateLogTable::insertBatchLog($insertedData, 'IMPORT');
        }
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