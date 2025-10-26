<?php

class sfBillItemBuildUpRateWithMarkupReportGenerator extends sfBillItemBuildUpRateReportGenerator {

	private $itemMarkupPercentage = 0;
	private $markupSettingsInfo = array();
	private $buildUpRateSummaryInfo = array();

	const ROW_TYPE_BUILDUP_ITEM = 2;

	public function setItemMarkUpPercentage($itemMarkupPercentage)
	{
		$this->itemMarkupPercentage = $itemMarkupPercentage;

		unset( $itemMarkupPercentage );
	}

	public function setMarkupSettingsInfo($markupSettingsInfo)
	{
		$this->markupSettingsInfo = $markupSettingsInfo;

		unset( $markupSettingsInfo );
	}

	public function generateBillPages(Array $buildUpItems, $ancestors, &$itemPages, $billTotals)
	{
		if ( !isset ( $itemPages[$this->pageCount] ) )
		{
			$itemPages[$this->pageCount] = array();

			//blank row
			array_push($itemPages[$this->pageCount], $this->setBlankRow());//starts with a blank row
		}

		$maxRows   = $this->getMaxRows();
		$ancestors = ( is_array($ancestors) && count($ancestors) ) ? $ancestors : array();

		foreach ( $ancestors as $k => $row )
		{
			array_push($itemPages[$this->pageCount], $row);
			$this->rowCount += 1;
			unset( $row );
		}

		foreach ( $buildUpItems as $x => $item )
		{
			$occupiedRows = Utilities::justify($buildUpItems[$x]['description'], $this->MAX_CHARACTERS);

			if ( $this->descriptionFormat == sfBuildspaceReportBillPageGenerator::DESC_FORMAT_ONE_LINE )
			{
				$oneLineDesc     = $occupiedRows[0];
				$occupiedRows    = new SplFixedArray(1);
				$occupiedRows[0] = $oneLineDesc;
			}

			$this->rowCount += count($occupiedRows);

			if ( $this->rowCount <= $maxRows )
			{
				foreach ( $occupiedRows as $key => $occupiedRow )
				{
					$row                                  = new SplFixedArray(self::TOTAL_BILL_ITEM_PROPERTY);
					$row[self::ROW_BILL_ITEM_ROW_IDX]     = ( $key == 0 ) ? $this->itemIndex : null;
					$row[self::ROW_BILL_ITEM_DESCRIPTION] = $occupiedRow;
					$row[self::ROW_BILL_ITEM_TYPE]        = self::ROW_TYPE_BLANK;
					$row[self::ROW_BILL_ITEM_ID]          = null;
					$row[self::ROW_BILL_ITEM_NUMBER]      = null; // factor
					$row[self::ROW_BILL_ITEM_CONSTANT]    = null;
					$row[self::ROW_BILL_ITEM_QTY]         = null;
					$row[self::ROW_BILL_ITEM_UNIT]        = null;
					$row[self::ROW_BILL_ITEM_RATE]        = null;
					$row[self::ROW_BILL_ITEM_TOTAL]       = null; // total

					if ( $key + 1 == $occupiedRows->count() )
					{
						$number   = Utilities::array_recursive_search($item['FormulatedColumns'], 'column_name', 'number');
						$constant = Utilities::array_recursive_search($item['FormulatedColumns'], 'column_name', 'constant');
						$quantity = Utilities::array_recursive_search($item['FormulatedColumns'], 'column_name', 'quantity');
						$rate     = Utilities::array_recursive_search($item['FormulatedColumns'], 'column_name', 'rate');
						$wastage  = Utilities::array_recursive_search($item['FormulatedColumns'], 'column_name', 'wastage');

						if ( isset( $rate[0] ) AND isset( $wastage[0] ) )
						{
							$numberVal   = isset( $number[0]['final_value'] ) ? $number[0]['final_value'] : 1;
							$constantVal = isset( $constant[0]['final_value'] ) ? $constant[0]['final_value'] : 1;
							$quantityVal = isset( $quantity[0]['final_value'] ) ? $quantity[0]['final_value'] : 0;

							$newRate = BillBuildUpRateItemTable::calculateRateWithSummaryMarkupWithoutRounding($rate, $wastage, $this->buildUpRateSummaryInfo);

							$rate[0]['final_value'] = BillBuildUpRateItemTable::calculateRateAfterMarkupWithoutRounding($newRate, $this->itemMarkupPercentage, $this->markupSettingsInfo);

							$item['total'] = $quantityVal * $rate[0]['final_value'];

							// only apply number if there is quantity available
							if ( ( $quantityVal > 0 OR $quantityVal < 0 ) AND ( $numberVal > 0 OR $numberVal < 0 ) )
							{
								$item['total'] = $numberVal * $item['total'];
							}

							// only apply constant value if there is quantity available
							if ( ( $quantityVal > 0 OR $quantityVal < 0 ) AND ( $constantVal > 0 OR $constantVal < 0 ) )
							{
								$item['total'] = $constantVal * $item['total'];
							}

							unset( $numberVal, $constantVal, $quantityVal );
						}

						$row[self::ROW_BILL_ITEM_ID]       = $item['id'];
						$row[self::ROW_BILL_ITEM_NUMBER]   = ( isset( $number[0] ) ) ? $number[0] : 0;
						$row[self::ROW_BILL_ITEM_CONSTANT] = ( isset( $constant[0] ) ) ? $constant[0] : 0;
						$row[self::ROW_BILL_ITEM_QTY]      = ( isset( $quantity[0] ) ) ? $quantity[0] : 0;
						$row[self::ROW_BILL_ITEM_RATE]     = ( isset( $rate[0] ) ) ? $rate[0] : 0;
						$row[self::ROW_BILL_ITEM_UNIT]     = isset( $item['UnitOfMeasurement'] ) ? $item['UnitOfMeasurement']['symbol'] : null;
						$row[self::ROW_BILL_ITEM_TOTAL]    = $item['total'];
						$row[self::ROW_BILL_ITEM_TYPE]     = self::ROW_TYPE_BUILDUP_ITEM;

						unset( $number, $constant, $quantity, $rate, $wastage );
					}

					array_push($itemPages[$this->pageCount], $row);

					unset( $row );
				}

				//blank row
				array_push($itemPages[$this->pageCount], $this->setBlankRow());

				$this->rowCount ++;//plus one blank row;
				$this->itemIndex ++;

				unset( $buildUpItems[$x], $occupiedRows );
			}
			else
			{
				unset( $occupiedRows );

				$this->pageCount ++;
				$this->resetPageRowCount();
				$this->generateBillPages($buildUpItems, $ancestors, $itemPages, $billTotals);
				break;
			}
		}
	}

	public function setBuildUpRateInfo($buildUpRateSummaryInfo)
	{
		$this->buildUpRateSummaryInfo = $buildUpRateSummaryInfo;

		unset( $buildUpRateSummaryInfo );
	}

}