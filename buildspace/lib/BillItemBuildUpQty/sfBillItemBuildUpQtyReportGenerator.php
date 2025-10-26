<?php

/**
 * @property int itemIndex
 * @property mixed dimensions
 */
class sfBillItemBuildUpQtyReportGenerator extends sfBuildspaceBQMasterFunction {

	use sfBuildspaceReportPageFormat;

	protected $bill;

	protected $buildUpQuantityItems = array();
	protected $soqBuildUpQuantityItems = array();
	protected $manualBuildUpQuantityItems = array();
	protected $importedBuildUpQuantityItems = array();
	protected $soqFormulatedColumns = array();

	private $soqItemAvailable = false;

	const ROW_BILL_ITEM_FACTOR     = 3;
	const ROW_FORMULATED_COLUMNS   = 5;
	const ROW_BILL_ITEM_TOTAL      = 7;
	const ROW_BILL_ITEM_SIGN       = 8;
	const ROW_SOQ_HEADER_TYPE      = - 8;
	const ROW_SOQ_ITEM_TYPE        = - 16;
	const ROW_SOQ_MEASUREMENT_TYPE = - 32;

	public function __construct(ProjectStructure $bill, $descriptionFormat = sfBuildspaceReportBillPageGenerator::DESC_FORMAT_FULL_LINE)
	{
		$this->bill              = $bill;
		$this->printSettings     = BillLayoutSettingTable::getInstance()->getPrintingLayoutSettings(1, TRUE);
		$this->fontSize          = $this->printSettings['layoutSetting']['fontSize'];
		$this->fontType          = self::setFontType($this->printSettings['layoutSetting']['fontTypeName']);
		$this->headSettings      = $this->printSettings['headSettings'];
		$this->descriptionFormat = $descriptionFormat;
		$this->currency          = $bill->MainInformation->Currency;

		unset($bill, $descriptionFormat);
	}

	public function generatePages()
	{
		$itemPages = array();
		$items     = $this->getPreCombinedItems();

		$this->itemIndex = 1;

		$this->setMaxCharactersPerLine();

		$this->checkForSOQItemAvailability();

		$this->generateBillPages($items, 1, $itemPages);

		$pages = SplFixedArray::fromArray($itemPages);

		unset($itemPages, $items, $this->buildUpQuantityItems, $this->soqBuildUpQuantityItems);

		return $pages;
	}

	public function generateBillPages(Array $buildUpQuantityItems, $pageCount, array &$itemPages, $printedSOQHead = false)
	{
		$itemPages[$pageCount] = array();
		$maxRows               = $this->getMaxRows();

		//blank row
		array_push($itemPages[$pageCount], $this->generateBlankRow());//starts with a blank row

		$rowCount = 1;

		foreach($buildUpQuantityItems as $x => &$item)
		{
			$maxCharacters                = $this->MAX_CHARACTERS;
			$manualBuildUpQuantityItems   = array();
			$importedBuildUpQuantityItems = array();

			if ( isset($item['is_soq_item']) AND $this->soqItemAvailable AND ! $printedSOQHead )
			{
				$printedSOQHead = true;

				$row = $this->generateSOQHeader($rowCount);

				array_push($itemPages[$pageCount], $row);

				//blank row
				array_push($itemPages[$pageCount], $this->generateBlankRow());
			}

			$occupiedRows = Utilities::justify($item['description'], $maxCharacters);

			if($this->descriptionFormat == sfBuildspaceReportBillPageGenerator::DESC_FORMAT_ONE_LINE)
			{
				$oneLineDesc     = $occupiedRows[0];
				$occupiedRows    = new SplFixedArray( 1 );
				$occupiedRows[0] = $oneLineDesc;
			}

			$rowCount += count($occupiedRows);

			if($rowCount >= $maxRows)
			{
				unset($occupiedRows, $manualBuildUpQuantityItems, $importedBuildUpQuantityItems);

				$pageCount++;
				$this->generateBillPages($buildUpQuantityItems, $pageCount, $itemPages, $printedSOQHead);
				break;
			}

			foreach($occupiedRows as $key => $occupiedRow)
			{
				$row                                  = new SplFixedArray(self::TOTAL_BILL_ITEM_PROPERTY);
				$row[self::ROW_BILL_ITEM_ROW_IDX]     = ($key == 0 AND ! isset($item['is_soq_item'])) ? $this->itemIndex : null;
				$row[self::ROW_BILL_ITEM_DESCRIPTION] = $occupiedRow;
				$row[self::ROW_BILL_ITEM_ID]          = null;
				$row[self::ROW_BILL_ITEM_FACTOR]      = null; // factor
				$row[self::ROW_BILL_ITEM_TOTAL]       = null; // total
				$row[self::ROW_FORMULATED_COLUMNS]    = null;
				$row[self::ROW_BILL_ITEM_SIGN]        = isset($item['sign']) ? BillBuildUpQuantityItemTable::getSignTextBySign($item['sign']) : NULL;
				$row[self::ROW_BILL_ITEM_TYPE]        = null;

				if ( isset($item['is_soq_item']) )
				{
					$row[self::ROW_BILL_ITEM_TYPE] = self::ROW_SOQ_ITEM_TYPE;
				}

				if ( isset($item['is_soq_measurement']) )
				{
					$row[self::ROW_BILL_ITEM_TYPE] = self::ROW_SOQ_MEASUREMENT_TYPE;
				}

				if($key+1 == $occupiedRows->count())
				{
					$factorCol             = $this->getFactorFormulatedColumn($item);
					$otherFormulatedColumn = $this->getOtherFormulatedColumn($item);

					$row[self::ROW_BILL_ITEM_ID]       = $item['id'];
					$row[self::ROW_BILL_ITEM_FACTOR]   = $factorCol;
					$row[self::ROW_BILL_ITEM_TOTAL]    = isset( $item['total'] ) ? $item['total'] : 0;
					$row[self::ROW_FORMULATED_COLUMNS] = $otherFormulatedColumn;

					// only display total qty for manual insert qty item(s)
					if ( isset($item['is_soq_item']) AND isset($this->soqFormulatedColumns[$item['id']]) )
					{
						$totalValue                     = $this->soqFormulatedColumns[$item['id']][0];
						$row[self::ROW_BILL_ITEM_TOTAL] = 0;

						if ( ! $totalValue['has_build_up'] )
						{
							$row[self::ROW_BILL_ITEM_TOTAL] = Utilities::prelimRounding($totalValue['final_value']);
						}

						unset($totalValue);
					}
				}

				array_push($itemPages[$pageCount], $row);

				unset($row);
			}

			//blank row
			array_push($itemPages[$pageCount], $this->generateBlankRow());

			$rowCount++;//plus one blank row;

			if ( ! isset($item['is_soq_item']) )
			{
				$this->itemIndex++;
			}

			unset($item, $buildUpQuantityItems[$x], $occupiedRows, $manualBuildUpQuantityItems, $importedBuildUpQuantityItems);
		}
	}

	public function setOrientationAndSize($orientation = false, $pageFormat = false)
	{
		if($orientation)
		{
			$this->orientation = $orientation;
			$this->setPageFormat($this->generatePageFormat( ($pageFormat) ? $pageFormat : self::PAGE_FORMAT_A4 ));
		}
		else
		{
			$this->orientation = self::ORIENTATION_LANDSCAPE;
			$this->setPageFormat($this->generatePageFormat(self::PAGE_FORMAT_A3));
		}
	}

	protected function generatePageFormat($format)
	{
		switch(strtoupper($format))
		{
			/*
			*  For now we only handle A4 format. If there's necessity to handle other page
			* format we need to add to this method
			*/
			case self::PAGE_FORMAT_A4 :
				$width = $this->orientation == self::ORIENTATION_PORTRAIT ? 595 : 800;
				$height = $this->orientation == self::ORIENTATION_PORTRAIT ? 800 : 595;
				$pf = array(
					'page_format'       => self::PAGE_FORMAT_A4,
					'minimum-font-size' => $this->fontSize,
					'width'             => $width,
					'height'            => $height,
					'pdf_margin_top'    => 8,
					'pdf_margin_right'  => 10,
					'pdf_margin_bottom' => 1,
					'pdf_margin_left'   => 10
				);
				break;
			case self::PAGE_FORMAT_A3 :
				$width = $this->orientation == self::ORIENTATION_PORTRAIT ? 800 : 1000;
				$height = $this->orientation == self::ORIENTATION_PORTRAIT ? 1000 : 800;
				$pf = array(
					'page_format'       => self::PAGE_FORMAT_A3,
					'minimum-font-size' => $this->fontSize,
					'width'             => $width,
					'height'            => $height,
					'pdf_margin_top'    => 8,
					'pdf_margin_right'  => 10,
					'pdf_margin_bottom' => 1,
					'pdf_margin_left'   => 10
				);
				break;
			// DEFAULT ISO A4
			default:
				$width = $this->orientation == self::ORIENTATION_PORTRAIT ? 595 : 800;
				$height = $this->orientation == self::ORIENTATION_PORTRAIT ? 800 : 595;
				$pf = array(
					'page_format'       => self::PAGE_FORMAT_A4,
					'minimum-font-size' => $this->fontSize,
					'width'             => $width,
					'height'            => $height,
					'pdf_margin_top'    => 8,
					'pdf_margin_right'  => 10,
					'pdf_margin_bottom' => 3,
					'pdf_margin_left'   => 10
				);
			break;
		}

		return $pf;
	}

	public function getFactorFormulatedColumn($item)
	{
		$column = array('final_value' => 0);

		if ( ! isset ($item['FormulatedColumns']) )
		{
			return $column;
		}

		foreach ( $item['FormulatedColumns'] as $formulatedColumn )
		{
			if ( $formulatedColumn['column_name'] == 'factor' )
			{
				$formulatedColumn['has_formula'] = $this->isFormulaFormulatedColumn($formulatedColumn);

				$column = $formulatedColumn;
				break;
			}
		}

		return $column;
	}

	public function getOtherFormulatedColumn($item)
	{
		$columns = array();

		if ( ! isset ($item['FormulatedColumns']) )
		{
			return $columns;
		}

		foreach ( $item['FormulatedColumns'] as $formulatedColumn )
		{
			$columnName = $formulatedColumn['column_name'];

			if ( $columnName == 'factor' ) continue;

			$formulatedColumn['has_formula'] = $this->isFormulaFormulatedColumn($formulatedColumn);

			$columns[$columnName] = $formulatedColumn;
		}

		return $columns;
	}

	public function getMaxRows()
	{
		$dimensionsCount = count($this->dimensions);

		switch ($dimensionsCount)
		{
			case 0:
				$row = 42;
				break;

			case 1:
				$row = 50;
				break;

			case 2:
				$row = 58;
				break;

			case 3:
				$row = 66;
				break;

			case 4:
				$row = 72;
				break;

			default:
				throw new InvalidArgumentException('Exceeded maximum 4 dimension types');
				break;
		}

		return $row;
	}

	public function setAvailableTableHeaderDimensions($dimensions)
	{
		$this->dimensions = $dimensions;
	}

	public function setupBillItemHeader($billItem, $billRef)
	{
		$itemRows = array();

		$blankRow                                  = new SplFixedArray(self::TOTAL_BILL_ITEM_PROPERTY);
		$blankRow[self::ROW_BILL_ITEM_ID]          = -1;//id
		$blankRow[self::ROW_BILL_ITEM_ROW_IDX]     = null;//row index
		$blankRow[self::ROW_BILL_ITEM_DESCRIPTION] = null;//description
		$blankRow[self::ROW_BILL_ITEM_LEVEL]       = 0;//level
		$blankRow[self::ROW_BILL_ITEM_TYPE]        = self::ROW_TYPE_BLANK;//type
		$blankRow[self::ROW_BILL_ITEM_FACTOR]      = null; // factor
		$blankRow[self::ROW_FORMULATED_COLUMNS]    = null;
		$blankRow[self::ROW_BILL_ITEM_TOTAL]       = null; // total
		$blankRow[self::ROW_BILL_ITEM_SIGN]        = null; // sign

		array_push($itemRows, $blankRow);

		$descriptionBillRef = (strlen($billRef)) ? '<b>('.$billRef.') -</b>' : '';
		$occupiedRows       = Utilities::justify("{$descriptionBillRef} {$billItem['description']}", $this->MAX_CHARACTERS);

		foreach($occupiedRows as $key => $occupiedRow)
		{
			$row                                  = new SplFixedArray(self::TOTAL_BILL_ITEM_PROPERTY);
			$row[self::ROW_BILL_ITEM_DESCRIPTION] = $occupiedRow;
			$row[self::ROW_BILL_ITEM_ID]          = null;
			$row[self::ROW_BILL_ITEM_FACTOR]      = null; // factor
			$row[self::ROW_BILL_ITEM_TOTAL]       = null; // total
			$row[self::ROW_FORMULATED_COLUMNS]    = null;
			$row[self::ROW_BILL_ITEM_SIGN]        = null; // sign

			if($key+1 == $occupiedRows->count())
			{
				$row[self::ROW_BILL_ITEM_ID]    = $billItem['id'];
				$row[self::ROW_BILL_ITEM_TOTAL] = 0;
			}

			array_push($itemRows, $row);

			unset($row);
		}

		//blank row
		array_push($itemRows, $blankRow);

		return $itemRows;
	}

	public function isFormulaFormulatedColumn($column)
	{
		return $column['value'] != $column['final_value'];
	}

	public function setBuildUpQuantityItems(array $buildUpQuantityItems)
	{
		$this->buildUpQuantityItems = $buildUpQuantityItems;

		unset($buildUpQuantityItems);
	}

	public function setSOQBuildUpQuantityItems(array $soqBuildUpQuantityItems)
	{
		$this->soqBuildUpQuantityItems = $soqBuildUpQuantityItems;

		unset($soqBuildUpQuantityItems);
	}

	public function setManualBuildUpQuantityMeasurements(array $manualBuildUpQuantityItems)
	{
		$this->manualBuildUpQuantityItems = $manualBuildUpQuantityItems;

		unset($manualBuildUpQuantityItems);
	}

	public function setImportedBuildUpQuantityMeasurements(array $importedBuildUpQuantityItems)
	{
		$this->importedBuildUpQuantityItems = $importedBuildUpQuantityItems;

		unset($importedBuildUpQuantityItems);
	}

	private function checkForSOQItemAvailability()
	{
		if ( count($this->soqBuildUpQuantityItems) > 0 )
		{
			$this->soqItemAvailable = true;
		}
	}

	/**
	 * @param $rowCount
	 * @return SplFixedArray
	 */
	private function generateSOQHeader(&$rowCount)
	{
		$rowCount++;

		$row                                  = new SplFixedArray( self::TOTAL_BILL_ITEM_PROPERTY );
		$row[self::ROW_BILL_ITEM_ROW_IDX]     = null;
		$row[self::ROW_BILL_ITEM_DESCRIPTION] = 'Schedule of Quantity';
		$row[self::ROW_BILL_ITEM_TYPE]        = self::ROW_SOQ_HEADER_TYPE;
		$row[self::ROW_BILL_ITEM_ID]          = null;
		$row[self::ROW_BILL_ITEM_FACTOR]      = null; // factor
		$row[self::ROW_BILL_ITEM_TOTAL]       = null; // total
		$row[self::ROW_FORMULATED_COLUMNS]    = null;
		$row[self::ROW_BILL_ITEM_SIGN]        = null;

		return $row;
	}

	/**
	 * @return SplFixedArray
	 */
	private function generateBlankRow()
	{
		$blankRow                                  = new SplFixedArray( self::TOTAL_BILL_ITEM_PROPERTY );
		$blankRow[self::ROW_BILL_ITEM_ID]          = - 1; //id
		$blankRow[self::ROW_BILL_ITEM_ROW_IDX]     = null; //row index
		$blankRow[self::ROW_BILL_ITEM_DESCRIPTION] = null; //description
		$blankRow[self::ROW_BILL_ITEM_LEVEL]       = 0; //level
		$blankRow[self::ROW_BILL_ITEM_TYPE]        = self::ROW_TYPE_BLANK; //type
		$blankRow[self::ROW_BILL_ITEM_FACTOR]      = null; // factor
		$blankRow[self::ROW_FORMULATED_COLUMNS]    = null;
		$blankRow[self::ROW_BILL_ITEM_TOTAL]       = null; // total
		$blankRow[self::ROW_BILL_ITEM_SIGN]        = null;

		return $blankRow;
	}

	/**
	 * Combine manual and SoQ Item and SoQ's Item Measurement into single array
	 *
	 * @return array
	 */
	private function getPreCombinedItems()
	{
		$newItems = array();
		$items    = array_merge($this->buildUpQuantityItems, $this->soqBuildUpQuantityItems);

		foreach ( $items as $item )
		{
			$newItems[] = $item;

			// if no manual and imported SoQ Build Up Quantity Item(s) for SoQ Item, then continue to the next item
			if ( ! isset($item['is_soq_item']) )
			{
				unset($item);

				continue;
			}

			$manualBuildUpQuantityItems = $this->getSOQBuildUpQuantityItemsByItemId($this->manualBuildUpQuantityItems, $item['id']);

			foreach ( $manualBuildUpQuantityItems as $manualBuildUpQuantityItem )
			{
				$manualBuildUpQuantityItem['is_soq_measurement'] = true;

				$newItems[] = $manualBuildUpQuantityItem;

				unset($manualBuildUpQuantityItem);
			}

			$importedBuildUpQuantityItems = $this->getSOQBuildUpQuantityItemsByItemId($this->importedBuildUpQuantityItems, $item['id']);

			foreach ( $importedBuildUpQuantityItems as $importedBuildUpQuantityItem )
			{
				$importedBuildUpQuantityItem['is_soq_measurement'] = true;

				$newItems[] = $importedBuildUpQuantityItem;

				unset($importedBuildUpQuantityItem);
			}

			unset($item, $importedBuildUpQuantityItems, $manualBuildUpQuantityItems);
		}

		unset($items);

		return $newItems;
	}

	private function getSOQBuildUpQuantityItemsByItemId(array $buildUpQuantityItems, $id)
	{
		$data = array();

		if ( isset($buildUpQuantityItems[$id]) )
		{
			$data = $buildUpQuantityItems[$id];
		}

		return $data;
	}

	public function getSOQFormulatedColumn($soqFormulatedColumns)
	{
		$this->soqFormulatedColumns = $soqFormulatedColumns;

		unset($soqFormulatedColumns);
	}

}