<?php

class sfBuildSpaceScheduleOfRateXMLGenerator extends sfBuildspaceXMLGenerator {

	private $scheduleOfRate;

	private $conn;

	private $pdo;

	private $tradeIds = array();

	private $trades;

	private $items;

	private $currentTradeChild;

	private $currentItemChild;

	private $usedUnits = array();

	const TAG_BILL = "BILL";
	const TAG_ITEM = "item";
	const TAG_ITEMS = "ITEMS";
	const TAG_TRADES = "TRADES";
	const TAG_RATES = "RATES";

	const XML_FILENAME = "scheduleofrate";

	public function __construct(ScheduleOfRate $scheduleOfRate, Doctrine_Connection $conn)
	{
		parent::__construct(self::XML_FILENAME, sfConfig::get('sf_upload_dir') . DIRECTORY_SEPARATOR . 'temp' . DIRECTORY_SEPARATOR, 'xml', false);

		$this->scheduleOfRate = $scheduleOfRate;
		$this->conn           = $conn;
		$this->pdo            = $conn->getDbh();
	}

	public function generateXMLFile()
	{
		$trades = $this->getTrades();

		$this->guardAgainstEmptyTrade($trades);

		$items = $this->getItems();

		$this->guardAgainstEmptyItem($items);

		$this->buildXMLFile($trades, $items);
	}

	private function getTrades()
	{
		// will get element(s) based on current BQ Library's ID
		$trades = DoctrineQuery::create()
			->select('t.id, t.description')
			->from('ScheduleOfRateTrade t')
			->andWhere('t.schedule_of_rate_id = ?', $this->scheduleOfRate->id)
			->addOrderBy('t.priority ASC')
			->fetchArray();

		foreach ( $trades as $trade )
		{
			$this->tradeIds[$trade['id']] = $trade['id'];

			unset( $trade );
		}

		return $trades;
	}

	private function getItems()
	{
		$itemsArray = array();

		// get associated item(s) with formulated column associated with all the element(s) from above
		$sorItems = DoctrineQuery::create()
			->select('c.id, c.description, c.type, c.uom_id, c.trade_id,
			c.priority, c.root_id, c.lft, c.rgt, c.level, uom.id, uom.name, uom.symbol, uom.type, type_fc.id,
			type_fc.relation_id, type_fc.column_name, type_fc.value, type_fc.final_value, type_fc.created_at')
			->from('ScheduleOfRateItem c')
			->leftJoin('c.FormulatedColumns type_fc')
			->leftJoin('c.UnitOfMeasurement uom')
			->whereIn('c.trade_id', $this->tradeIds)
			->andWhere('c.deleted_at IS NULL')
			->orderBy('c.priority, c.lft, c.level')
			->fetchArray();

		foreach ( $sorItems as $sorItem )
		{
			$sorItem['uom_symbol'] = $sorItem['UnitOfMeasurement']['symbol'];

			$itemsArray[$sorItem['trade_id']][] = $sorItem;

			unset( $sorItem );
		}

		unset( $sorItems );

		return $itemsArray;
	}

	/**
	 * @param $trades
	 * @throws InvalidArgumentException
	 */
	private function guardAgainstEmptyTrade($trades)
	{
		if ( count($trades) === 0 )
		{
			throw new InvalidArgumentException('No trade\'s record can be exported!');
		}
	}

	/**
	 * @param $items
	 * @throws InvalidArgumentException
	 */
	private function guardAgainstEmptyItem($items)
	{
		if ( count($items) === 0 )
		{
			throw new InvalidArgumentException('No item\'s record can be exported!');
		}
	}

	private function buildXMLFile(array $trades, array $items)
	{
		$this->create(self::TAG_BILL, array(
			'buildspaceId' => sfConfig::get('app_register_buildspace_id'),
			'billId'       => $this->scheduleOfRate->id,
		));

		$this->createTradeTag();

		$this->createItemTag();

		foreach ( $trades as $trade )
		{
			$tradeId = $trade['id'];

			$currentItems = isset( $items[$tradeId] ) ? $items[$tradeId] : array();

			unset( $items[$tradeId] );

			$this->addTradeChildren($trade);

			$this->processItems($currentItems);

			unset( $tradeId, $currentItems );
		}

		parent::write();
	}

	public function createTradeTag()
	{
		$this->trades = parent::createTag(self::TAG_TRADES);
	}

	public function createItemTag()
	{
		$this->items = parent::createTag(self::TAG_ITEMS);
	}

	public function addTradeChildren(array $fieldAndValues)
	{
		$this->currentTradeChild = parent::addChildTag($this->trades, self::TAG_ITEM, $fieldAndValues);
	}

	public function processItems($items)
	{
		foreach ( $items as $item )
		{
			$rate = false;

			if ( isset( $item['UnitOfMeasurement'] ) AND count($item['UnitOfMeasurement']) > 0 )
			{
				$uom = $item['UnitOfMeasurement'];

				$this->checkUnitUsage($uom);

				unset( $item['UnitOfMeasurement'] );
			}

			if ( isset( $item['FormulatedColumns'] ) AND count($item['FormulatedColumns']) > 0 )
			{
				$rate = $this->createRateDataStructure($item);

				unset( $item['FormulatedColumns'] );
			}

			$this->addItemChildren($item);

			if ( is_array($rate) )
			{
				$this->addRateChild($rate);
			}

			unset( $item, $rate );
		}
	}

	public function addRateChild(array $fieldAndValues)
	{
		return parent::addChildTag($this->currentItemChild, self::TAG_RATES, $fieldAndValues);
	}

	public function addItemChildren(array $fieldAndValues)
	{
		$this->currentItemChild = parent::addChildTag($this->items, self::TAG_ITEM, $fieldAndValues);
	}

	/**
	 * @param $uom
	 */
	private function checkUnitUsage(array $uom)
	{
		if ( $uom['id'] && !array_key_exists($uom['id'], $this->usedUnits) )
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
			'column_name' => ScheduleOfRateItem::FORMULATED_COLUMN_RATE,
		);
	}

}