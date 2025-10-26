<?php

class ScheduleOfQuantityUnitGetter {

	protected $conn;

	protected $pdo;

	public function __construct(Doctrine_Connection $conn)
	{
		$this->conn = $conn;
		$this->pdo  = $conn->getDbh();
	}

	/**
	 * Get available unit of measurement(s)
	 *
	 * @return array
	 */
	public function getAvailableUnitOfMeasurements()
	{
		$unitsArray = array();

		$stmt = $this->pdo->prepare("SELECT u.id, LOWER(u.symbol) as symbol FROM ".UnitOfMeasurementTable::getInstance()->getTableName(). ' u WHERE u.display = true AND u.deleted_at IS NULL');

		$stmt->execute();
		$units = $stmt->fetchAll(PDO::FETCH_ASSOC);

		foreach ( $units as $unit )
		{
			$unitsArray[$unit['symbol']] = $unit['id'];
		}

		return $unitsArray;
	}

	/**
	 * Get unit of measurements by symbol
	 *
	 * @param $units
	 * @return array
	 */
	public function getExistingUnitsByUnitsSymbol(array $units)
	{
		$existingUnits = array();

		$stmt = $this->pdo->prepare('SELECT LOWER(u.symbol) as symbol FROM ' . UnitOfMeasurementTable::getInstance()->getTableName() . ' u
		WHERE LOWER(u.symbol) IN (\'' . implode('\', \'', $units) . '\') AND u.display = true AND u.deleted_at IS NULL');

		$stmt->execute();
		$availableUnits = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);

		foreach ($availableUnits as $availableUnit)
		{
			$existingUnits[$availableUnit] = $availableUnit;
		}

		unset($availableUnits);

		return $existingUnits;
	}

	/**
	 * Get current imported units
	 *
	 * @param $itemList
	 * @return array
	 */
	public function getImportedUnits(array $itemList)
	{
		$units = array();

		foreach ($itemList as $item)
		{
            if (!isset($item['isItem']) || !isset($item['unit']))
			{
				continue;
			}

			if ( ! is_null($item['unit']) )
			{
				$units[$item['unit']] = strtolower($item['unit']);
			}
		}

		return $units;
	}

	/**
	 * Get selected dimensions
	 *
	 * @return array
	 */
	public function getDimensions()
	{
		$data = array();

		$dimensionConstant = array('Length', 'Width', 'Depth');

		$stmt = $this->pdo->prepare('SELECT d.id, d.name FROM ' . DimensionTable::getInstance()->getTableName() . ' d WHERE d.name IN (\'' . implode('\', \'', $dimensionConstant) . '\') AND d.deleted_at IS NULL');

		$stmt->execute();
		$dimensions = $stmt->fetchAll(PDO::FETCH_ASSOC);

		foreach ( $dimensions as $dimension )
		{
			$data[$dimension['name']] = $dimension['id'];
		}

		return $data;
	}

	public function insertNewUnitOfMeasurementWithoutDimension(array $existingUnits, $newSymbol)
	{
		$uom          = new UnitOfMeasurement();
		$uom->name    = $newSymbol;
		$uom->symbol  = $newSymbol;
		$uom->type    = UnitOfMeasurement::UNIT_TYPE_METRIC;
		$uom->display = TRUE;

		$uom->save($this->conn);

		$existingUnits[strtolower($uom->symbol)] = $uom->id;

		return $existingUnits;
	}

}