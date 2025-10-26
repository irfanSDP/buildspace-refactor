<?php

class sfRemeasurementItemReportGenerator extends sfBuildspaceBQMasterFunction {

	use sfBuildspaceReportPageFormat;

	const TOTAL_BILL_ITEM_PROPERTY = 11;
	const ROW_BILL_ITEM_ID = 0;
	const ROW_BILL_ITEM_ROW_IDX = 1;
	const ROW_BILL_ITEM_DESCRIPTION = 2;
	const ROW_BILL_ITEM_LEVEL = 3;
	const ROW_BILL_ITEM_TYPE = 4;
	const ROW_BILL_ITEM_UNIT = 5;
	const ROW_BILL_ITEM_RATE = 6;
	const ROW_BILL_ITEM_QTY_OMISSION = 7;
	const ROW_BILL_ITEM_AMT_OMISSION = 8;
	const ROW_BILL_ITEM_QTY_ADDITION = 9;
	const ROW_BILL_ITEM_AMT_ADDITION = 10;

	public $totalOmissionByElement = array();
	public $totalAdditionByElement = array();

	protected $elements = array();
	protected $items = array();

	public function __construct(PostContract $postContract, ProjectStructure $bill, $descriptionFormat = self::DESC_FORMAT_FULL_LINE)
	{
		$this->postContract      = $postContract;
		$this->bill              = $bill;
		$this->descriptionFormat = $descriptionFormat;
		$this->currency          = $postContract->ProjectStructure->MainInformation->Currency->currency_code;

		$this->printSettings = BillLayoutSettingTable::getInstance()->getPrintingLayoutSettings($bill->BillLayoutSetting->id, true);
		$this->fontSize      = $this->printSettings['layoutSetting']['fontSize'];
		$this->fontType      = self::setFontType($this->printSettings['layoutSetting']['fontTypeName']);
		$this->headSettings  = $this->printSettings['headSettings'];

		$this->setOrientationAndSize();

		self::setMaxCharactersPerLine();
	}

	public function generatePages()
	{
		$pages     = array();
		$totalPage = 0;
		$itemPages = array();

		if ( !empty( $this->elements ) )
		{
			foreach ( $this->elements as $elementKey => $affectedElement )
			{
				$elementId = $affectedElement['id'];

				$this->totalOmissionByElement[$elementId] = 0;
				$this->totalAdditionByElement[$elementId] = 0;

				if ( !isset( $this->items[$elementId] ) )
				{
					continue;
				}

				$elementInfo = array( 'id' => $elementId, 'description' => $affectedElement['description'] );

				$this->generateBillItemPages($this->items[$elementId], $elementInfo, 1, array(), $itemPages);

				$page = array(
					'id'          => $elementId,
					'description' => $affectedElement['description'],
					'item_pages'  => SplFixedArray::fromArray($itemPages),
				);

				$totalPage += count($itemPages);

				$pages[$elementId] = $page;
			}
		}
		else
		{
			$elementInfo = array( 'id' => - 1, 'description' => '' );

			$this->generateBillItemPages(array(), $elementInfo, 1, array(), $itemPages);

			$page = array(
				'description' => '',
				'item_pages'  => SplFixedArray::fromArray($itemPages)
			);

			$totalPage += count($itemPages);

			$pages[0] = $page;
		}

		$this->totalPage = $totalPage;

		return $pages;
	}

	public function generateBillItemPages(Array $billItems, $elementInfo, $pageCount, $ancestors, &$itemPages)
	{
		$itemPages[$pageCount] = array();
		$maxRows               = $this->getMaxRows();
		$ancestors             = ( is_array($ancestors) && count($ancestors) ) ? $ancestors : array();

		$blankRow                                   = new SplFixedArray(self::TOTAL_BILL_ITEM_PROPERTY);
		$blankRow[self::ROW_BILL_ITEM_ID]           = - 1;//id
		$blankRow[self::ROW_BILL_ITEM_ROW_IDX]      = null;//row index
		$blankRow[self::ROW_BILL_ITEM_DESCRIPTION]  = null;//description
		$blankRow[self::ROW_BILL_ITEM_LEVEL]        = 0;//level
		$blankRow[self::ROW_BILL_ITEM_TYPE]         = self::ROW_TYPE_BLANK;//type
		$blankRow[self::ROW_BILL_ITEM_UNIT]         = null;
		$blankRow[self::ROW_BILL_ITEM_RATE]         = null;
		$blankRow[self::ROW_BILL_ITEM_QTY_OMISSION] = null;
		$blankRow[self::ROW_BILL_ITEM_AMT_OMISSION] = null;
		$blankRow[self::ROW_BILL_ITEM_QTY_ADDITION] = null;
		$blankRow[self::ROW_BILL_ITEM_AMT_ADDITION] = null;

		//blank row
		array_push($itemPages[$pageCount], $blankRow);//starts with a blank row
		$rowCount = 1;

		$occupiedRows = Utilities::justify($elementInfo['description'], $this->MAX_CHARACTERS);

		if ( $this->descriptionFormat == self::DESC_FORMAT_ONE_LINE )
		{
			$oneLineDesc     = $occupiedRows[0];
			$occupiedRows    = new SplFixedArray(1);
			$occupiedRows[0] = $oneLineDesc;
		}

		foreach ( $occupiedRows as $occupiedRow )
		{
			$row                                   = new SplFixedArray(self::TOTAL_BILL_ITEM_PROPERTY);
			$row[self::ROW_BILL_ITEM_ID]           = - 1;//id
			$row[self::ROW_BILL_ITEM_ROW_IDX]      = null;//row index
			$row[self::ROW_BILL_ITEM_DESCRIPTION]  = $occupiedRow;//description
			$row[self::ROW_BILL_ITEM_LEVEL]        = 0;//level
			$row[self::ROW_BILL_ITEM_TYPE]         = BillItem::TYPE_HEADER;//type
			$row[self::ROW_BILL_ITEM_UNIT]         = null;
			$row[self::ROW_BILL_ITEM_RATE]         = null;
			$row[self::ROW_BILL_ITEM_QTY_OMISSION] = null;
			$row[self::ROW_BILL_ITEM_AMT_OMISSION] = null;
			$row[self::ROW_BILL_ITEM_QTY_ADDITION] = null;
			$row[self::ROW_BILL_ITEM_AMT_ADDITION] = null;

			array_push($itemPages[$pageCount], $row);

			unset( $row );
		}

		//blank row
		array_push($itemPages[$pageCount], $blankRow);

		$rowCount += count($occupiedRows) + 1;//plus one blank row

		foreach ( $ancestors as $k => $row )
		{
			array_push($itemPages[$pageCount], $row);
			$rowCount += 1;
			unset( $row );
		}

		$ancestors    = array();
		$itemIndex    = 1;
		$counterIndex = 0;//display item's index in BQ

		foreach ( $billItems as $x => $billItem )
		{
			$occupiedRows = ( $billItems[$x]['type'] == BillItem::TYPE_ITEM_HTML_EDITOR or $billItems[$x]['type'] == BillItem::TYPE_NOID ) ? Utilities::justifyHtmlString($billItems[$x]['description'], ( strtoupper($billItems[$x]['description']) == $billItems[$x]['description'] ) ? $this->MAX_CHARACTERS - 10 : $this->MAX_CHARACTERS) : Utilities::justify($billItems[$x]['description'], ( strtoupper($billItems[$x]['description']) == $billItems[$x]['description'] ) ? $this->MAX_CHARACTERS - 10 : $this->MAX_CHARACTERS);

			if ( $this->descriptionFormat == self::DESC_FORMAT_ONE_LINE )
			{
				$oneLineDesc     = $occupiedRows[0];
				$occupiedRows    = new SplFixedArray(1);
				$occupiedRows[0] = $oneLineDesc;
			}

			$rowCount += count($occupiedRows);

			if ( $rowCount >= $maxRows )
			{
				unset( $occupiedRows );

				$pageCount ++;
				$this->generateBillItemPages($billItems, $elementInfo, $pageCount, $ancestors, $itemPages, true);
				break;
			}

			foreach ( $occupiedRows as $key => $occupiedRow )
			{
				if ( $key == 0 && $billItem['type'] != BillItem::TYPE_HEADER && $billItem['type'] != BillItem::TYPE_HEADER_N && $billItem['type'] != BillItem::TYPE_NOID )
				{
					$counterIndex ++;
				}

				$row                                   = new SplFixedArray(self::TOTAL_BILL_ITEM_PROPERTY);
				$row[self::ROW_BILL_ITEM_ID]           = - 1;//id
				$row[self::ROW_BILL_ITEM_ROW_IDX]      = ( $key == 0 && $billItem['type'] != BillItem::TYPE_HEADER && $billItem['type'] != BillItem::TYPE_HEADER_N && $billItem['type'] != BillItem::TYPE_NOID ) ? $billItem['bill_ref_element_no'] . '/' . $billItem['bill_ref_page_no'] . ' ' . $billItem['bill_ref_char'] : null;
				$row[self::ROW_BILL_ITEM_DESCRIPTION]  = $occupiedRow;//description
				$row[self::ROW_BILL_ITEM_LEVEL]        = $billItem['level'];
				$row[self::ROW_BILL_ITEM_TYPE]         = $billItem['type'];
				$row[self::ROW_BILL_ITEM_UNIT]         = null;
				$row[self::ROW_BILL_ITEM_RATE]         = null;
				$row[self::ROW_BILL_ITEM_QTY_OMISSION] = null;
				$row[self::ROW_BILL_ITEM_AMT_OMISSION] = null;
				$row[self::ROW_BILL_ITEM_QTY_ADDITION] = null;
				$row[self::ROW_BILL_ITEM_AMT_ADDITION] = null;

				if ( $key + 1 == $occupiedRows->count() && $billItem['type'] != BillItem::TYPE_HEADER && $billItem['type'] != BillItem::TYPE_HEADER_N && $billItem['type'] != BillItem::TYPE_NOID )
				{
					$row[self::ROW_BILL_ITEM_ID]           = $billItem['id'];
					$row[self::ROW_BILL_ITEM_UNIT]         = $billItem['uom_symbol'];
					$row[self::ROW_BILL_ITEM_RATE]         = self::gridCurrencyRoundingFormat($billItem['rate']);
					$row[self::ROW_BILL_ITEM_QTY_OMISSION] = self::gridCurrencyRoundingFormat($billItem['omission-qty_per_unit']);
					$row[self::ROW_BILL_ITEM_AMT_OMISSION] = self::gridCurrencyRoundingFormat($billItem['omission-total_per_unit']);
					$row[self::ROW_BILL_ITEM_QTY_ADDITION] = self::gridCurrencyRoundingFormat($billItem['addition-qty_per_unit']);
					$row[self::ROW_BILL_ITEM_AMT_ADDITION] = self::gridCurrencyRoundingFormat($billItem['addition-total_per_unit']);

					$this->totalOmissionByElement[$elementInfo['id']] += $row[self::ROW_BILL_ITEM_AMT_OMISSION];
					$this->totalAdditionByElement[$elementInfo['id']] += $row[self::ROW_BILL_ITEM_AMT_ADDITION];
				}
				else
				{
					if ( $key + 1 == $occupiedRows->count() && $billItem['type'] == BillItem::TYPE_NOID )
					{
						$row[self::ROW_BILL_ITEM_UNIT] = $billItem['uom_symbol'];//unit
					}
				}

				array_push($itemPages[$pageCount], $row);

				unset( $row );
			}

			//blank row
			array_push($itemPages[$pageCount], $blankRow);

			$rowCount ++;//plus one blank row;
			$itemIndex ++;

			unset( $billItems[$x], $occupiedRows );
		}
	}

	public function setAffectedElements($elements)
	{
		$this->elements = $elements;
	}

	public function setItems($items)
	{
		$this->items = $items;
	}

	public function setMaxCharactersPerLine()
	{
		$this->MAX_CHARACTERS = 45;
	}

	public function getMaxRows()
	{
		return $maxRows = 63;
	}

	protected function setOrientationAndSize()
	{
		$this->orientation = self::ORIENTATION_PORTRAIT;

		$this->setPageFormat($this->generatePageFormat(self::PAGE_FORMAT_A4));
	}

	protected function generatePageFormat()
	{
		return $pf = array(
			'page_format'       => self::PAGE_FORMAT_A4,
			'minimum-font-size' => $this->fontSize,
			'width'             => 595,
			'height'            => 800,
			'pdf_margin_top'    => 8,
			'pdf_margin_right'  => 8,
			'pdf_margin_bottom' => 3,
			'pdf_margin_left'   => 8
		);
	}

}