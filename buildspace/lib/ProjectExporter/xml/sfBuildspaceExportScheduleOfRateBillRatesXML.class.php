<?php

class sfBuildspaceExportScheduleOfRateBillRatesXML extends sfBuildspaceXMLGenerator
{

    public $xml;
    public $elements = false;
    public $items = false;
    public $currentElementChild;
    public $currentItemChild;
    public $billId;

    protected $usedUnits = array();

    const TAG_BILL = "BILL";
    const TAG_ITEM = "item";
    const TAG_ITEMS = "ITEMS";
    const TAG_ELEMENTS = "ELEMENTS";
    const TAG_UNIT = "UNIT";
    const TAG_TYPE = "TYPE";

    public function __construct($filename = null, $uploadPath = null, $billId, $extension = null, $deleteFile = null)
    {
        $this->pdo = ProjectStructureTable::getInstance()->getConnection()->getDbh();

        $this->billId = $billId;

        parent::__construct($filename, $uploadPath, $extension, $deleteFile);
    }

    public function process($billData = array(), $write = true)
    {
        parent::create(
            self::TAG_BILL,
            array(
                'buildspaceId'         => sfConfig::get('app_register_buildspace_id'),
                'billId'               => $this->billId,
                'isScheduleOfRateBill' => true
            )
        );

        if (array_key_exists('elementsAndItems', $billData))
        {
            $this->processElementAndItems($billData['elementsAndItems']);
        }

        if ($write)
        {
            parent::write();
        }
    }

    public function processElementAndItems($elementsAndItems)
    {
        if (count($elementsAndItems) > 0)
        {
            $this->createElementTag();

            $this->createItemTag();

            foreach ($elementsAndItems as $element)
            {
                $items = $element['items'];

                unset( $element['items'] );

                $this->addElementChildren($element);

                if (count($items))
                {
                    $this->processItems($items);
                }

            }
        }
    }

    public function processItems($items)
    {
        foreach ($items as $item)
        {
            if (!( $item['uom_id'] == '' && $item['description'] == '' && $item['grand_total'] == 0 ))
            {
                unset( $item['description'], $item['uom_id'] );

                $this->addItemChildren($item);
            }
        }
    }

    public function createElementTag()
    {
        $this->elements = parent::createTag(self::TAG_ELEMENTS);
    }

    public function addElementChildren($fieldAndValues)
    {
        $this->currentElementChild = parent::addChildTag($this->elements, self::TAG_ITEM, $fieldAndValues);
    }

    public function createItemTag()
    {
        $this->items = parent::createTag(self::TAG_ITEMS);
    }

    public function addItemChildren($fieldAndValues)
    {
        $this->currentItemChild = parent::addChildTag($this->items, self::TAG_ITEM, $fieldAndValues);
    }

}