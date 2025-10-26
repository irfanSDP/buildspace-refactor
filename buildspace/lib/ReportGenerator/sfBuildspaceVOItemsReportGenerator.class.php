<?php

class sfBuildspaceVOItemsReportGenerator extends sfBuildspaceBQMasterFunction {

	public $pageTitle;
	public $itemIds;
	public $fontSize;
	public $headSettings;

	public $variationTotals;

	const CLAIM_PREFIX = "Valuation No: ";

	const TOTAL_BILL_ITEM_PROPERTY = 12;
	const ROW_APPROVED = 9;
	const ROW_OMISSION = 10;
	const ROW_ADDITION = 11;

	public function __construct($project = false, $itemIds, $pageTitle, $descriptionFormat = self::DESC_FORMAT_FULL_LINE)
	{
		$this->pdo     = ProjectStructureTable::getInstance()->getConnection()->getDbh();
		$this->project = $project;
		$this->itemIds = $itemIds;

		$this->pageTitle         = $pageTitle;
		$this->currency          = $this->project->MainInformation->Currency;
		$this->descriptionFormat = $descriptionFormat;

		$this->setOrientationAndSize();

		$this->printSettings = BillLayoutSettingTable::getInstance()->getPrintingLayoutSettings(1, true);
		$this->fontSize      = $this->printSettings['layoutSetting']['fontSize'];
		$this->fontType      = self::setFontType($this->printSettings['layoutSetting']['fontTypeName']);
		$this->headSettings  = $this->printSettings['headSettings'];

		self::setMaxCharactersPerLine();
	}

	public function generatePages()
	{
        $itemPages       = array();
		$pages           = array();
		$data            = array();
		$voIds           = array();
		$variationTotals = array();

		$totalPage = 0;

		if ( count($this->itemIds) > 0 )
		{
			$stmt = $this->pdo->prepare("SELECT DISTINCT p.id, p.variation_order_id, p.description, p.type, p.lft, p.level, p.total_unit, p.rate,
            p.bill_ref, p.bill_item_id, p.omission_quantity, p.has_omission_build_up_quantity,
            p.addition_quantity, p.has_addition_build_up_quantity, uom.id AS uom_id, uom.symbol AS uom_symbol,
            p.priority, p.lft, p.level
            FROM " . VariationOrderItemTable::getInstance()->getTableName() . " i
            JOIN " . VariationOrderItemTable::getInstance()->getTableName() . " p ON (i.lft BETWEEN p.lft AND p.rgt AND p.deleted_at IS NULL)
            JOIN " . VariationOrderTable::getInstance()->getTableName() . " vo ON (p.variation_order_id = vo.id AND vo.deleted_at IS NULL)
            LEFT JOIN " . UnitOfMeasurementTable::getInstance()->getTableName() . " uom ON p.uom_id = uom.id AND uom.deleted_at IS NULL
            WHERE vo.project_structure_id = " . $this->project->id . " AND i.id IN (" . implode(',', $this->itemIds) . ")
            AND i.root_id = p.root_id
            AND i.type <> " . VariationOrderItem::TYPE_HEADER . "
            ORDER BY p.priority, p.lft, p.level");
			$stmt->execute();

			$variationOrderItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

			foreach ( $variationOrderItems as $variationOrderItem )
			{
				$voIds[$variationOrderItem['variation_order_id']] = $variationOrderItem['variation_order_id'];
			}

			// get VO's information
			$stmt = $this->pdo->prepare("SELECT vo.id, vo.description FROM " . VariationOrderTable::getInstance()->getTableName() . " vo
            WHERE vo.id IN (" . implode(',', $voIds) . ") AND vo.project_structure_id = " . $this->project->id . " AND vo.deleted_at IS NULL
            ORDER BY vo.priority");

			$stmt->execute();

			$variationOrders = $stmt->fetchAll(PDO::FETCH_ASSOC);

			foreach ( $variationOrders as $variationOrder )
			{
                $itemPages       = array();

				$generatedVOHeader = false;

				$stmt = $this->pdo->prepare("SELECT DISTINCT i.id AS variation_order_item_id,
				CASE WHEN ((i.rate * i.addition_quantity) - (i.rate * i.omission_quantity) < 0)
                    THEN -1 * ABS(ci.current_amount)
                    ELSE ci.current_amount
                END AS current_amount,
				CASE WHEN ((i.rate * i.addition_quantity) - (i.rate * i.omission_quantity) < 0)
                    THEN -1 * ABS(ci.current_percentage)
                    ELSE ci.current_percentage
                END AS current_percentage,
				CASE WHEN ((i.rate * i.addition_quantity) - (i.rate * i.omission_quantity) < 0)
                    THEN -1 * ABS(ci.up_to_date_amount)
                    ELSE ci.up_to_date_amount
                END AS up_to_date_amount,
				CASE WHEN ((i.rate * i.addition_quantity) - (i.rate * i.omission_quantity) < 0)
                    THEN -1 * ABS(ci.up_to_date_percentage)
                    ELSE ci.up_to_date_percentage
                END AS up_to_date_percentage,
				CASE WHEN ((pvoi.rate * pvoi.addition_quantity) - (pvoi.rate * pvoi.omission_quantity) < 0)
                    THEN -1 * ABS(pci.up_to_date_amount)
                    ELSE pci.up_to_date_amount
                END AS previous_amount,
				CASE WHEN ((pvoi.rate * pvoi.addition_quantity) - (pvoi.rate * pvoi.omission_quantity) < 0)
                    THEN -1 * ABS(pci.up_to_date_percentage)
                    ELSE pci.up_to_date_percentage
                END AS previous_percentage
                FROM " . VariationOrderItemTable::getInstance()->getTableName() . " i
                JOIN " . VariationOrderClaimTable::getInstance()->getTableName() . " c ON i.variation_order_id = c.variation_order_id
                LEFT JOIN " . VariationOrderClaimItemTable::getInstance()->getTableName() . " ci ON ci.variation_order_claim_id = c.id AND ci.variation_order_item_id = i.id
                LEFT JOIN " . VariationOrderClaimTable::getInstance()->getTableName() . " pc ON pc.variation_order_id = c.variation_order_id AND pc.revision = c.revision - 1
				LEFT JOIN " . VariationOrderClaimItemTable::getInstance()->getTableName() . " pci ON pci.variation_order_claim_id = pc.id AND pci.variation_order_item_id = i.id
				LEFT JOIN " . VariationOrderItemTable::getInstance()->getTableName() . " pvoi ON pci.variation_order_item_id = pvoi.id
				WHERE i.variation_order_id = " . $variationOrder['id'] . "
                AND i.deleted_at IS NULL AND c.deleted_at IS NULL AND ci.deleted_at IS NULL AND pc.deleted_at IS NULL AND pci.deleted_at IS NULL");

				$stmt->execute();
				$claimItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

				if ( !array_key_exists($variationOrder['id'], $variationTotals) )
				{
					$variationTotals[$variationOrder['id']] = array(
						'rate'              => 0,
						'omission_quantity' => 0,
						'omission_amount'   => 0,
						'addition_quantity' => 0,
						'addition_amount'   => 0
					);
				}

				foreach ( $variationOrderItems as $key => $variationOrderItem )
				{
					if ( !$generatedVOHeader )
					{
						$voInformation = array(
							'id'                             => "vo-{$variationOrder['id']}",
							'description'                    => $variationOrder['description'],
							'bill_ref'                       => '',
							'total_unit'                     => '',
							'bill_item_id'                   => - 1,
							'type'                           => (string) 0,
							'uom_id'                         => '-1',
							'uom_symbol'                     => '',
							'updated_at'                     => '-',
							'level'                          => 0,
							'rate-value'                     => 0,
							'omission_quantity-value'        => 0,
							'has_omission_build_up_quantity' => false,
							'addition_quantity-value'        => 0,
							'has_addition_build_up_quantity' => false,
							'previous_percentage-value'      => 0,
							'previous_amount-value'          => 0,
							'current_percentage-value'       => 0,
							'current_amount-value'           => 0,
							'up_to_date_percentage-value'    => 0,
							'up_to_date_amount-value'        => 0,
						);

						$generatedVOHeader = true;
					}

					if ( !array_key_exists($variationOrder['id'], $data) )
					{
						$data[$variationOrder['id']] = array();
					}

					if ( !array_key_exists($variationOrder['id'], $pages) )
					{
						$pages[$variationOrder['id']] = array();
					}

					if ( $variationOrderItem['variation_order_id'] != $variationOrder['id'] )
					{
						continue;
					}

					$variationTotals[$variationOrder['id']]['rate'] += $variationOrderItem['rate'];

					$variationTotals[$variationOrder['id']]['omission_quantity'] += $variationOrderItem['omission_quantity'];
					$variationTotals[$variationOrder['id']]['omission_amount'] += ( $variationOrderItem['total_unit'] * $variationOrderItem['omission_quantity'] * $variationOrderItem['rate'] );

					$variationTotals[$variationOrder['id']]['addition_quantity'] += $variationOrderItem['addition_quantity'];
					$variationTotals[$variationOrder['id']]['addition_amount'] += ( $variationOrderItem['total_unit'] * $variationOrderItem['addition_quantity'] * $variationOrderItem['rate'] );

					$variationOrderItem['omission_quantity-value'] = $variationOrderItem['omission_quantity'];
					$variationOrderItem['addition_quantity-value'] = $variationOrderItem['addition_quantity'];
					$variationOrderItem['rate-value']              = $variationOrderItem['rate'];
					$variationOrderItem['type']                    = (string) $variationOrderItem['type'];
					$variationOrderItem['uom_id']                  = $variationOrderItem['uom_id'] > 0 ? (string) $variationOrderItem['uom_id'] : '-1';
					$variationOrderItem['uom_symbol']              = $variationOrderItem['uom_id'] > 0 ? $variationOrderItem['uom_symbol'] : '';

					$variationOrderItem['previous_percentage-value']   = 0;
					$variationOrderItem['previous_amount-value']       = 0;
					$variationOrderItem['current_percentage-value']    = 0;
					$variationOrderItem['current_amount-value']        = 0;
					$variationOrderItem['up_to_date_percentage-value'] = 0;
					$variationOrderItem['up_to_date_amount-value']     = 0;

					foreach ( $claimItems as $claimItem )
					{
						if ( $claimItem['variation_order_item_id'] != $variationOrderItem['id'] )
						{
							continue;
						}

						$variationOrderItem['previous_percentage-value']   = $claimItem['previous_percentage'];
						$variationOrderItem['previous_amount-value']       = $claimItem['previous_amount'];
						$variationOrderItem['current_percentage-value']    = $claimItem['current_percentage'];
						$variationOrderItem['current_amount-value']        = $claimItem['current_amount'];
						$variationOrderItem['up_to_date_percentage-value'] = $claimItem['up_to_date_percentage'];
						$variationOrderItem['up_to_date_amount-value']     = $claimItem['up_to_date_amount'];

						unset( $claimItem );
					}

					$data[$variationOrder['id']][] = $variationOrderItem;

					unset( $variationOrderItem, $variationOrderItems[$key] );
				}

				$this->generateItemPages($data[$variationOrder['id']], $voInformation, 1, array(), $itemPages);

				$page = array(
					'description' => $voInformation['description'],
					'item_pages'  => SplFixedArray::fromArray($itemPages)
				);

				$totalPage += count($itemPages);

				$pages[$variationOrder['id']] = $page;

				unset( $claimItems );
			}

			unset( $variationOrders );
		}
		else
		{
			$this->generateItemPages(array(), null, 1, array(), $itemPages);

			$page = array(
				'description'   => "",
				'element_count' => 1,
				'item_pages'    => SplFixedArray::fromArray($itemPages)
			);

			$totalPage += count($itemPages);

			$pages[0] = $page;
		}

		$this->totalPage       = $totalPage;
		$this->variationTotals = $variationTotals;

		return $pages;
	}

	public function generateItemPages(Array $voItems, $tradeInfo, $pageCount, $ancestors, &$itemPages)
	{
		$itemPages[$pageCount] = array();
		$maxRows               = $this->getMaxRows();
		$ancestors             = ( is_array($ancestors) && count($ancestors) ) ? $ancestors : array();

		$blankRow                                   = new SplFixedArray(self::TOTAL_BILL_ITEM_PROPERTY);
		$blankRow[self::ROW_BILL_ITEM_ID]           = - 1;//id
		$blankRow[self::ROW_BILL_ITEM_ROW_IDX]      = null;//row index
		$blankRow[self::ROW_BILL_ITEM_DESCRIPTION]  = null;//description
		$blankRow[self::ROW_BILL_ITEM_LEVEL]        = 0;//level
		$blankRow[self::ROW_BILL_ITEM_TYPE]         = self::ROW_TYPE_BLANK;
		$blankRow[self::ROW_BILL_ITEM_UNIT]         = null;
		$blankRow[self::ROW_BILL_ITEM_RATE]         = null;
		$blankRow[self::ROW_BILL_ITEM_QTY_PER_UNIT] = null;
		$blankRow[self::ROW_BILL_ITEM_INCLUDE]      = null;
		$blankRow[self::ROW_APPROVED]               = null;
		$blankRow[self::ROW_OMISSION]               = null;
		$blankRow[self::ROW_ADDITION]               = null;

		//blank row
		array_push($itemPages[$pageCount], $blankRow);//starts with a blank row
		$rowCount = 1;

		$occupiedRows = Utilities::justify($tradeInfo['description'], $this->MAX_CHARACTERS);

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
			$row[self::ROW_BILL_ITEM_TYPE]         = self::ROW_TYPE_ELEMENT;//type
			$row[self::ROW_BILL_ITEM_UNIT]         = null;//unit
			$row[self::ROW_BILL_ITEM_RATE]         = null;//rate
			$row[self::ROW_BILL_ITEM_QTY_PER_UNIT] = null;
			$row[self::ROW_BILL_ITEM_INCLUDE]      = null;//include
			$row[self::ROW_APPROVED]               = null;
			$row[self::ROW_OMISSION]               = null;
			$row[self::ROW_ADDITION]               = null;

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

		foreach ( $voItems as $x => $voItem )
		{
			$description = $voItems[$x]['description'];

			if ( $voItem['total_unit'] > 1 )
			{
				$description = $description . " ({$voItem['total_unit']} units)";
			}

			$descriptionBillRef = ( strlen($voItem['bill_ref']) ) ? '<b>(' . $voItem['bill_ref'] . ') - </b>' : '';
			$occupiedRows       = Utilities::justify($descriptionBillRef . $description, $this->MAX_CHARACTERS);

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
				$this->generateItemPages($voItems, $tradeInfo, $pageCount, $ancestors, $itemPages);
				break;
			}

			foreach ( $occupiedRows as $key => $occupiedRow )
			{
				if ( $key == 0 && $voItem['type'] != VariationOrderItem::TYPE_HEADER )
				{
					$counterIndex ++;
				}

				$row = new SplFixedArray(self::TOTAL_BILL_ITEM_PROPERTY);

				$row[self::ROW_BILL_ITEM_ROW_IDX]     = ( $key == 0 && $voItem['type'] != VariationOrderItem::TYPE_HEADER ) ? Utilities::generateCharFromNumber($counterIndex, $this->printSettings['layoutSetting']['includeIandO']) : null;
				$row[self::ROW_BILL_ITEM_DESCRIPTION] = $occupiedRow;
				$row[self::ROW_BILL_ITEM_LEVEL]       = $voItem['level'];
				$row[self::ROW_BILL_ITEM_TYPE]        = $voItem['type'];
				$row[self::ROW_BILL_ITEM_ID]          = null;
				$row[self::ROW_BILL_ITEM_UNIT]        = null;
				$row[self::ROW_BILL_ITEM_RATE]        = null;
				$row[self::ROW_APPROVED]              = null;
				$row[self::ROW_OMISSION]              = null;
				$row[self::ROW_ADDITION]              = null;

				if ( $key + 1 == $occupiedRows->count() && $voItem['type'] != VariationOrderItem::TYPE_HEADER )
				{
					$row[self::ROW_BILL_ITEM_ID]   = $voItem['id'];//only work item will have id set so we can use it to display rates and quantities
					$row[self::ROW_BILL_ITEM_UNIT] = $voItem['uom_symbol'];
					$row[self::ROW_BILL_ITEM_RATE] = $voItem['rate'];
					$row[self::ROW_APPROVED]       = null;
					$row[self::ROW_OMISSION]       = array(
						'qty'    => array_key_exists('omission_quantity', $voItem) ? $voItem['omission_quantity'] : 0,
						'amount' => array_key_exists('omission_quantity', $voItem) ? $voItem['total_unit'] * $voItem['omission_quantity'] * $voItem['rate'] : 0
					);
					$row[self::ROW_ADDITION]       = array(
						'qty'    => ( array_key_exists('addition_quantity', $voItem) ) ? $voItem['addition_quantity'] : 0,
						'amount' => array_key_exists('addition_quantity', $voItem) ? $voItem['total_unit'] * $voItem['addition_quantity'] * $voItem['rate'] : 0
					);
				}

				array_push($itemPages[$pageCount], $row);

				unset( $row );
			}

			//blank row
			array_push($itemPages[$pageCount], $blankRow);

			$rowCount ++;//plus one blank row;
			$itemIndex ++;

			unset( $voItems[$x], $occupiedRows );
		}
	}

	protected function setOrientationAndSize()
	{
		$this->orientation = self::ORIENTATION_PORTRAIT;
		$this->setPageFormat($this->generatePageFormat(self::PAGE_FORMAT_A4));
	}

	public function setPageFormat($pageFormat)
	{
		$this->pageFormat = $pageFormat;
	}

	protected function generatePageFormat()
	{
		$width  = 595;
		$height = 800;

		return $pf = array(
			'page_format'       => self::PAGE_FORMAT_A4,
			'minimum-font-size' => $this->fontSize,
			'width'             => $width,
			'height'            => $height,
			'pdf_margin_top'    => 8,
			'pdf_margin_right'  => 10,
			'pdf_margin_bottom' => 3,
			'pdf_margin_left'   => 10
		);
	}

	public function setMaxCharactersPerLine()
	{
		$this->MAX_CHARACTERS = 48;
	}

	public function getMaxRows()
	{
		return $maxRows = 68;
	}

}