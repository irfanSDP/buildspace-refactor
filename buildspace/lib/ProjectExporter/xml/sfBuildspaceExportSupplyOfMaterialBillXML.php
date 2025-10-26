<?php

class sfBuildspaceExportSupplyOfMaterialBillXML extends sfBuildspaceXMLGenerator
{
    public $xml;
    public $elements = false;
    public $items = false;
    public $units = false;
    public $currentElementChild;
    public $currentItemChild;
    public $billColumnSettings;
    public $columnName = array();
    public $usedUnits = array();
    public $billId;
    public $exportElementsAndItems;
    public $billLayoutSetting;
    public $billType;

    const TAG_SUPPLY_OF_MATERIAL_BILL = "SUPPLY_OF_MATERIAL_BILL";
    const TAG_ITEM = "item";
    const TAG_ITEMS = "ITEMS";
    const TAG_ELEMENTS = "ELEMENTS";
    const TAG_COLUMN = "COLUMN";
    const TAG_LAYOUTSETTING = "LAYOUTSETTING";
    const TAG_PHRASE = "PHRASE";
    const TAG_HEADSETTING = "HEADSETTING";
    const TAG_BILLTYPE = "BILLTYPE";
    const TAG_TYPE = "TYPE";
    const TAG_UNITOFMEASUREMENT = "UNITOFMEASUREMENT";
    const TAG_UNIT = "UNIT";
    const TAG_BILLSETTING = 'BILLSETTING';

    public function __construct($filename = null, $uploadPath = null, $billId, $extension = null, $deleteFile = false)
    {
        $this->pdo = ProjectStructureTable::getInstance()->getConnection()->getDbh();

        $this->billId = $billId;

        parent::__construct($filename, $uploadPath, $extension, $deleteFile);
    }

    public function process($billData = array(), $exportElementsAndItems = true, $write = true)
    {
        parent::create(self::TAG_SUPPLY_OF_MATERIAL_BILL,
            array(
                'buildspaceId'           => sfConfig::get('app_register_buildspace_id'),
                'billId'                 => $this->billId,
                'isSupplyOfMaterialBill' => true,
            )
        );

        if (array_key_exists('billSetting', $billData) && count($billData['billSetting']) > 0)
        {
            parent::addChildren(parent::createTag(self::TAG_BILLSETTING), $billData['billSetting']);
        }

        $this->exportElementsAndItems = $exportElementsAndItems;

        if (array_key_exists('billLayoutSetting', $billData) && count($billData['billLayoutSetting']) > 0)
        {
            $this->processBillLayoutSetting($billData['billLayoutSetting']);
        }

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

                if (count($items) > 0)
                {
                    if ($this->exportElementsAndItems)
                    {
                        $this->processItems($items);
                    }
                    else
                    {
                        $this->exportRates($items);
                    }
                }
            }
        }

        $this->processUnits();
    }

    public function exportRates($items)
    {
        if (is_array($items))
        {
            foreach ($items as $item)
            {
                $this->addItemChildren($item);
            }
        }
    }

    public function processItems($items)
    {
        foreach ($items as $item)
        {
            $uom = ( array_key_exists('UnitOfMeasurement', $item) ) ? true : false;

            if ($uom)
            {
                $uom = $item['UnitOfMeasurement'];

                if ($uom['id'] && !array_key_exists($uom['id'], $this->usedUnits))
                {
                    $this->usedUnits[$uom['id']] = $uom;
                }

                unset( $item['UnitOfMeasurement'] );
            }

            $this->addItemChildren($item);
        }
    }

    public function processUnits()
    {
        if (!count($this->usedUnits))
        {
            return false;
        }

        $this->createUnitOfMeasurementTag();

        foreach ($this->usedUnits as $unit)
        {
            $this->addUnitChildren($unit);
        }
    }

    public function processBillLayoutSetting($billLayoutSetting)
    {
        $this->createLayoutSettingTag();

        $billPhrase = ( array_key_exists('SOMBillPhrase', $billLayoutSetting) ) ? true : false;

        if ($billPhrase)
        {
            $billPhrase = $billLayoutSetting['SOMBillPhrase'];

            unset( $billLayoutSetting['SOMBillPhrase'] );
        }
        else
        {
            $billPhrase = false;
        }

        $headSetting = ( array_key_exists('SOMBillHeadSettings', $billLayoutSetting) ) ? true : false;

        if ($headSetting && count($headSetting))
        {
            $headSetting = $billLayoutSetting['SOMBillHeadSettings'];

            unset( $billLayoutSetting['SOMBillHeadSettings'] );
        }
        else
        {
            $headSetting = false;
        }

        parent::addChildren($this->billLayoutSetting, $billLayoutSetting);

        if ($billPhrase)
        {
            parent::addChildTag($this->billLayoutSetting, self::TAG_PHRASE, $billPhrase);
        }

        if ($headSetting)
        {
            $this->processHeadSetting($headSetting);
        }
    }

    public function processHeadSetting($headSetting)
    {
        $headSettingNode = $this->createHeadSettingTag();

        foreach ($headSetting as $head)
        {
            parent::addChildTag($headSettingNode, self::TAG_ITEM, $head);
        }
    }

    public function createBillXML($elements = false, $items = false)
    {
        //@deprecated: later to change to use constant
        parent::create('bill');

        if ($elements)
        {
            $this->createElementTag();
        }

        if ($items)
        {
            $this->createItemTag();
        }
    }

    public function createElementTag()
    {
        $this->elements = parent::createTag(self::TAG_ELEMENTS);
    }

    public function createLayoutSettingTag()
    {
        $this->billLayoutSetting = parent::createTag(self::TAG_LAYOUTSETTING);
    }

    public function createHeadSettingTag()
    {
        return parent::addChildTag($this->billLayoutSetting, self::TAG_HEADSETTING);
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

    public function createUnitOfMeasurementTag()
    {
        $this->units = parent::createTag(self::TAG_UNITOFMEASUREMENT);
    }

    public function addUnitChildren($fieldAndValues)
    {
        $this->currentUnitChildTag = parent::addChildTag($this->units, self::TAG_UNIT, $fieldAndValues);
    }

}