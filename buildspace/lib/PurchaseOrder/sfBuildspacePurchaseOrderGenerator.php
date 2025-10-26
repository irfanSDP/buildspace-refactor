<?php

class sfBuildspacePurchaseOrderGenerator
{

	/**
	 * @var PurchaseOrder
	 */
	private $purchaseOrder;

	/**
	 * @var array
	 */
	private $itemList;

	const ADDRESS_MAX_CHAR = 45;

	const ADDRESS_MAX_ROW = 3;

	public $DEFAULT_MAX_ROWS = 36;

	public $MAX_ROWS = 27;

	const MAX_CHARACTERS = 43;

	const ITEM_PROPERTY = 7;
	const ITEM_PROPERTY_CHAR_REF = 0;
	const ITEM_PROPERTY_DESCRIPTION = 1;
	const ITEM_PROPERTY_RATE = 2;
	const ITEM_PROPERTY_AMOUNT = 3;
	const ITEM_PROPERTY_TYPE = 4;
	const ITEM_PROPERTY_QUANTITY = 5;
	const ITEM_PROPERTY_UNIT = 6;

	private $grandTotalBeforeTax = 0;

	public function __construct(PurchaseOrder $purchaseOrder, array $itemList)
	{
		$this->purchaseOrder = $purchaseOrder;
		$this->itemList      = $itemList;
	}

	public function generatePage()
	{
		$itemPages           = array();
		$sumAmountPages      = array();
		$descriptionRowCount = 0;

		$this->generatePOItemPages($this->itemList, 0, $itemPages, $sumAmountPages, $descriptionRowCount);

		return array(
			'summary_items'             => SplFixedArray::fromArray($itemPages),
			'sum_amount_pages'          => SplFixedArray::fromArray($sumAmountPages),
			'po_grand_total_before_tax' => $this->grandTotalBeforeTax,
		);
	}

	private function generatePOItemPages(Array $items, $pageCount, &$itemPages, &$sumAmountPages, $descriptionRowCount = 1, $counter = 1, $carriedTotalToNextPage = false)
	{
		$itemPages[$pageCount]      = array();
		$sumAmountPages[$pageCount] = 0;

		$blankRow                                  = new SplFixedArray(self::ITEM_PROPERTY);
		$blankRow[self::ITEM_PROPERTY_CHAR_REF]    = null; //character reference
		$blankRow[self::ITEM_PROPERTY_DESCRIPTION] = null; //title
		$blankRow[self::ITEM_PROPERTY_RATE]        = null; //last page summary
		$blankRow[self::ITEM_PROPERTY_AMOUNT]      = null; //total amount for bill
		$blankRow[self::ITEM_PROPERTY_TYPE]        = null;
		$blankRow[self::ITEM_PROPERTY_QUANTITY]    = null;
		$blankRow[self::ITEM_PROPERTY_UNIT]        = null;

		//blank row
		array_push($itemPages[$pageCount], $blankRow); //starts with a blank row

		$rowCount = $descriptionRowCount;

		if ( $carriedTotalToNextPage )
		{
			$lastPageTotal = $sumAmountPages[$pageCount - 1];

			$occupiedRows = Utilities::justify('Carried Forward From Previous Page', self::MAX_CHARACTERS);

			foreach ( $occupiedRows as $key => $occupiedRow )
			{
				$row                                  = new SplFixedArray(self::ITEM_PROPERTY);
				$row[self::ITEM_PROPERTY_CHAR_REF]    = null;
				$row[self::ITEM_PROPERTY_DESCRIPTION] = $occupiedRow; //title
				$row[self::ITEM_PROPERTY_TYPE]        = ResourceItem::TYPE_HEADER;
				$row[self::ITEM_PROPERTY_QUANTITY]    = null;
				$row[self::ITEM_PROPERTY_UNIT]        = null;
				$row[self::ITEM_PROPERTY_RATE]        = null; //last page summary
				$row[self::ITEM_PROPERTY_AMOUNT]      = null; //total amount for bill

				if ( $key + 1 == $occupiedRows->count() )
				{
					$row[self::ITEM_PROPERTY_AMOUNT] = $lastPageTotal; //total amount for bill
				}

				array_push($itemPages[$pageCount], $row);
			}

			//blank row
			array_push($itemPages[$pageCount], $blankRow);

			$rowCount ++; //plus one blank row;

			$sumAmountPages[$pageCount] += $lastPageTotal; //always update to the total sum of amount for each items
		}

		foreach ( $items as $x => $item )
		{
			if ( ! empty($item['remarks']) )
			{
				$description = "{$item['description']} ({$item['remarks']})";
			}
			else
			{
				$description = $item['description'];
			}

			$occupiedRows = Utilities::justify($description, self::MAX_CHARACTERS);
			$rowCount += count($occupiedRows);

			if ( $rowCount <= $this->MAX_ROWS )
			{
				foreach ( $occupiedRows as $key => $occupiedRow )
				{
					$row                                  = new SplFixedArray(self::ITEM_PROPERTY);
					$row[self::ITEM_PROPERTY_CHAR_REF]    = ( $key == 0 and $item['type'] != ResourceItem::TYPE_HEADER ) ? $counter : null;
					$row[self::ITEM_PROPERTY_DESCRIPTION] = $occupiedRow; //title
					$row[self::ITEM_PROPERTY_TYPE]        = $item['type'];
					$row[self::ITEM_PROPERTY_QUANTITY]    = null;
					$row[self::ITEM_PROPERTY_UNIT]        = null;
					$row[self::ITEM_PROPERTY_RATE]        = null; //last page summary
					$row[self::ITEM_PROPERTY_AMOUNT]      = null; //total amount for bill

					if ( $key == 0 and $item['type'] != ResourceItem::TYPE_HEADER )
					{
						$counter ++;
					}

					if ( $key + 1 == $occupiedRows->count() and $item['type'] != ResourceItem::TYPE_HEADER )
					{
						$row[self::ITEM_PROPERTY_QUANTITY] = $item['quantity'];
						$row[self::ITEM_PROPERTY_UNIT]     = $item['uom'];
						$row[self::ITEM_PROPERTY_RATE]     = $item['rates']; //last page summary
						$row[self::ITEM_PROPERTY_AMOUNT]   = $item['amount']; //total amount for bill
					}

					array_push($itemPages[$pageCount], $row);
				}

				//blank row
				array_push($itemPages[$pageCount], $blankRow);

				$rowCount ++; //plus one blank row;

				$sumAmountPages[$pageCount] += $item['amount']; //always update to the total sum of amount for each items

				$this->grandTotalBeforeTax += $item['amount'];

				unset( $items[$x], $row );
			}
			else
			{
				$pageCount ++;
				$this->generatePOItemPages($items, $pageCount, $itemPages, $sumAmountPages, $descriptionRowCount, $counter, true);
				break;
			}
		}
	}

} 