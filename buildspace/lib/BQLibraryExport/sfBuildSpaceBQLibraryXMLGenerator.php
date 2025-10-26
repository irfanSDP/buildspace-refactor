<?php

class sfBuildSpaceBQLibraryXMLGenerator extends sfBuildspaceXMLGenerator {

	/**
	 * @var BQLibrary
	 */
	private $library;

	/**
	 * @var Doctrine_Connection
	 */
	private $conn;

	/**
	 * @var PDO
	 */
	private $pdo;

	private $elementIds = array();

	private $elements;

	private $items;

	private $currentElementChild;

	private $currentItemChild;

	private $usedUnits = array();

	const TAG_BILL              = "BILL";
	const TAG_ITEM              = "item";
	const TAG_ITEMS             = "ITEMS";
	const TAG_ELEMENTS          = "ELEMENTS";
	const TAG_BILLSETTING       = "BILLSETTING";
	const TAG_BILLCOLUMNSETTING = "BILLCOLUMNSETTING";
	const TAG_COLUMN            = "COLUMN";
	const TAG_LAYOUTSETTING     = "LAYOUTSETTING";
	const TAG_PHRASE            = "PHRASE";
	const TAG_HEADSETTING       = "HEADSETTING";
	const TAG_BILLTYPE          = "BILLTYPE";
	const TAG_TYPEREFERENCES    = "TYPEREFERENCES";
	const TAG_TYPE              = "TYPE";
	const TAG_QTY               = "QTY";
	const TAG_UNITOFMEASUREMENT = "UNITOFMEASUREMENT";
	const TAG_UNIT              = "UNIT";
	const TAG_ITEM_LS_PERCENT   = "LS_PERCENT";
	const TAG_ITEM_PC_RATE      = "PC_RATE";
	const TAG_RATES             = "RATES";
	const TAG_RATE              = "RATE";
	const TAG_BILLPAGES         = "BILLPAGES";
	const TAG_BILLPAGE          = "BILLPAGE";
	const TAG_BILLPAGEITEMS     = "BILLPAGEITEMS";
	const TAG_COLLECTIONPAGES   = "COLLECTIONPAGES";

	const XML_FILENAME = "bqlibrary";

	public function __construct(BQLibrary $library, Doctrine_Connection $conn)
	{
		parent::__construct( self::XML_FILENAME, sfConfig::get('sf_upload_dir').DIRECTORY_SEPARATOR.'temp'.DIRECTORY_SEPARATOR, 'xml', false );

		$this->library = $library;
		$this->conn    = $conn;
		$this->pdo     = $conn->getDbh();
	}

	public function generateXMLFile()
	{
		$elements = $this->getElements();

		$this->guardAgainstEmptyElement($elements);

		$items = $this->getItems();

		$this->guardAgainstEmptyItem($items);

		$this->buildXMLFile($elements, $items);
	}

	private function getElements()
	{
		// will get element(s) based on current BQ Library's ID
		$elements = DoctrineQuery::create()->select('t.id, t.description')
		->from('BQElement t')
		->andWhere('t.library_id = ?', $this->library->id)
		->addOrderBy('t.priority ASC')
		->fetchArray();

		foreach ( $elements as $element )
		{
			$this->elementIds[$element['id']] = $element['id'];

			unset($element);
		}

		return $elements;
	}

	private function getItems()
	{
		$itemsArray = array();

		// get associated item(s) with formulated column associated with all the element(s) from above
		$bqItems = DoctrineQuery::create()->select('c.id, c.description, c.type, c.uom_id, c.element_id,
		c.priority, c.root_id, c.lft, c.rgt, c.level, uom.id, uom.name, uom.symbol, uom.type, type_fc.id,
		type_fc.relation_id, type_fc.column_name, type_fc.value, type_fc.final_value, type_fc.created_at')
		->from('BQItem c')
		->leftJoin('c.FormulatedColumns type_fc')
		->leftJoin('c.UnitOfMeasurement uom')
		->whereIn('c.element_id', $this->elementIds)
		->andWhere('c.deleted_at IS NULL')
		->orderBy('c.priority, c.lft, c.level')
		->fetchArray();

		foreach ( $bqItems as $bqItem )
		{
			$bqItem['uom_symbol'] = $bqItem['UnitOfMeasurement']['symbol'];

			$itemsArray[$bqItem['element_id']][] = $bqItem;

			unset($bqItem);
		}

		unset($bqItems);

		return $itemsArray;
	}

	/**
	 * @param $elements
	 * @throws InvalidArgumentException
	 */
	private function guardAgainstEmptyElement($elements)
	{
		if (count($elements) == 0)
		{
			throw new InvalidArgumentException('No element\'s record can be exported!');
		}
	}

	/**
	 * @param $items
	 * @throws InvalidArgumentException
	 */
	private function guardAgainstEmptyItem($items)
	{
		if (count($items) == 0)
		{
			throw new InvalidArgumentException('No item\'s record can be exported!');
		}
	}

	private function buildXMLFile(array $elements, array $items)
	{
		$this->create( self::TAG_BILL, array(
			'buildspaceId' => sfConfig::get('app_register_buildspace_id'),
			'billId'       => $this->library->id,
		));

		$this->createElementTag();

		$this->createItemTag();

		foreach ( $elements as $element )
		{
			$elementId = $element['id'];

			$currentItems = isset($items[$elementId]) ? $items[$elementId] : array();

			unset($items[$elementId]);

			$this->addElementChildren( $element );

			$this->processItems($currentItems);

			unset($elementId, $currentItems);
		}

		unset($elements, $items);

		parent::write();
	}

	public function createElementTag()
	{
		$this->elements = parent::createTag( self::TAG_ELEMENTS );
	}

	public function createItemTag()
	{
		$this->items = parent::createTag( self::TAG_ITEMS );
	}

	public function addElementChildren( array $fieldAndValues )
	{
		$this->currentElementChild = parent::addChildTag( $this->elements, self::TAG_ITEM, $fieldAndValues );
	}

	public function processItems($items)
	{
		foreach($items as $item)
		{
			$rate = FALSE;

			if ( isset($item['UnitOfMeasurement']) AND count($item['UnitOfMeasurement']) > 0 )
			{
				$uom = $item['UnitOfMeasurement'];

				$this->checkUnitUsage($uom);

				unset($item['UnitOfMeasurement']);
			}

			if ( isset($item['FormulatedColumns']) AND count($item['FormulatedColumns']) > 0 )
			{
				$rate = $this->createRateDataStructure($item);

				unset($item['FormulatedColumns']);
			}

			$this->addItemChildren($item);

			if (is_array($rate))
			{
				$this->addRateChild($rate);
			}

			unset($item, $rate);
		}
	}

	public function addRateChild(array $fieldAndValues)
	{
		return parent::addChildTag( $this->currentItemChild, self::TAG_RATES, $fieldAndValues );
	}

	public function addItemChildren(array $fieldAndValues)
	{
		$this->currentItemChild = parent::addChildTag( $this->items, self::TAG_ITEM, $fieldAndValues );
	}

	/**
	 * @param $uom
	 */
	private function checkUnitUsage(array $uom)
	{
		if ($uom['id'] && ! array_key_exists($uom['id'], $this->usedUnits))
		{
			$this->usedUnits[$uom['id']] = $uom;
		}
	}

	/**
	 * @param $item
	 * @return array
	 */
	private function createRateDataStructure(array $item)
	{
		return array(
			'relation_id' => $item['id'],
			'value'       => $item['FormulatedColumns'][0]['value'],
			'final_value' => $item['FormulatedColumns'][0]['final_value'],
			'column_name' => BQItem::FORMULATED_COLUMN_RATE,
		);
	}

}