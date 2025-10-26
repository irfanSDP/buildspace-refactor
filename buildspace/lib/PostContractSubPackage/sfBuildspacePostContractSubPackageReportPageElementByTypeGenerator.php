<?php

class sfBuildspacePostContractSubPackageReportPageElementByTypeGenerator extends sfBuildspaceBQMasterFunction {

	public $pageTitle;
	public $fontSize;
	public $headSettings;
	public $affectedElements;
	public $revision;
	public $typeTotals;
	public $elementTotals;

	const CLAIM_PREFIX = 'Valuation No: ';

	public function __construct(SubPackage $subPackage, ProjectStructure $bill, $pageTitle, $descriptionFormat = self::DESC_FORMAT_FULL_LINE)
	{
		$this->pdo        = ProjectStructureTable::getInstance()->getConnection()->getDbh();
		$this->bill       = $bill;
		$this->subPackage = $subPackage;
		$this->project    = ProjectStructureTable::getInstance()->find($bill->root_id);

		$this->pageTitle         = $pageTitle;
		$this->currency          = $this->project->MainInformation->Currency;
		$this->descriptionFormat = $descriptionFormat;

		$this->setOrientationAndSize();

		$this->printSettings = BillLayoutSettingTable::getInstance()->getPrintingLayoutSettings($bill->BillLayoutSetting->id, true);
		$this->fontSize      = $this->printSettings['layoutSetting']['fontSize'];
		$this->fontType      = self::setFontType($this->printSettings['layoutSetting']['fontTypeName']);
		$this->headSettings  = $this->printSettings['headSettings'];

		self::setMaxCharactersPerLine();
	}

	public function generatePages()
	{
		$bill               = $this->bill;
		$elementGrandTotals = array();
		$this->revision     = $revision = SubPackagePostContractClaimRevisionTable::getCurrentSelectedProjectRevision($this->subPackage);

		$elements = $this->getElements();

		// Get Type List
		$typeItems = $this->getTypeItems();

		foreach ( $typeItems as $typeItem )
		{
			$object                         = new PostContractStandardClaimTypeReference();
			$object->id                     = $typeItem['id'];
			$object->bill_column_setting_id = $typeItem['bill_column_setting_id'];
			$object->post_contract_id       = $typeItem['post_contract_id'];

			$elementGrandTotals[$typeItem['bill_column_setting_id']][] = SubPackagePostContractStandardClaimTable::getTotalClaimRateGroupByElement($bill->id, $object, $revision, $this->subPackage->id);

			unset( $object );
		}

		$elementTotals    = array();//$elements;
		$this->typeTotals = array();

		foreach ( $bill->BillColumnSettings as $billColumnSetting )
		{
			$defaultElementsTotal = SubPackagePostContractStandardClaimTable::getTotalClaimRateGroupByTypeRef($bill->id, $revision, $this->project->PostContract->id, $this->subPackage->id);
			$typeQuantityCounter  = 0;

			$this->typeTotals[$billColumnSetting['id']]['total_per_unit']        = 0;
			$this->typeTotals[$billColumnSetting['id']]['up_to_date_percentage'] = 0;
			$this->typeTotals[$billColumnSetting['id']]['up_to_date_amount']     = 0;

			foreach ( $elements as $element )
			{
				$elementId = $element['id'];

				$elementTotals[$elementId][$billColumnSetting['id']]['grand_total']                  = 0;
				$elementTotals[$elementId][$billColumnSetting['id']]['type_total_percentage']        = 0;
				$elementTotals[$elementId][$billColumnSetting['id']]['type_total_up_to_date_amount'] = 0;
			}

			// use PostContractStandardClaimTypeReference's if available
			if ( isset( $elementGrandTotals[$billColumnSetting['id']] ) )
			{
				foreach ( $elementGrandTotals[$billColumnSetting['id']] as $elementGrandTotal )
				{
					foreach ( $elements as $element )
					{
						$elementId = $element['id'];

						if ( isset( $elementGrandTotal[$elementId] ) )
						{
							$elementTotals[$elementId][$billColumnSetting['id']]['grand_total'] += $elementGrandTotal[$elementId][0]['total_per_unit'];
							$elementTotals[$elementId][$billColumnSetting['id']]['type_total_up_to_date_amount'] += $elementGrandTotal[$elementId][0]['up_to_date_amount'];
						}
					}

					$typeQuantityCounter ++;
				}
			}

			// assign element total for unit that haven't been instantiate yet.
			while ($typeQuantityCounter < $billColumnSetting['quantity'])
			{
				foreach ( $elements as $element )
				{
					$elementId = $element['id'];

					if ( isset( $defaultElementsTotal[$element['id']] ) )
					{
						$elementTotals[$elementId][$billColumnSetting['id']]['grand_total'] += $defaultElementsTotal[$element['id']][0]['total_per_unit'];
					}
				}

				$typeQuantityCounter ++;
			}

			foreach ( $elements as $element )
			{
				$elementId = $element['id'];

				if ( $elementTotals[$elementId][$billColumnSetting['id']]['type_total_up_to_date_amount'] > 0 )
				{
					$elementTotals[$elementId][$billColumnSetting['id']]['type_total_percentage'] = Utilities::prelimRounding(Utilities::percent($elementTotals[$elementId][$billColumnSetting['id']]['type_total_up_to_date_amount'], $elementTotals[$elementId][$billColumnSetting['id']]['grand_total']));
				}

				$this->typeTotals[$billColumnSetting['id']]['up_to_date_amount'] += $elementTotals[$elementId][$billColumnSetting['id']]['type_total_up_to_date_amount'];
				$this->typeTotals[$billColumnSetting['id']]['total_per_unit'] += $elementTotals[$elementId][$billColumnSetting['id']]['grand_total'];
			}

			$this->typeTotals[$billColumnSetting['id']]['up_to_date_percentage'] = ( $this->typeTotals[$billColumnSetting['id']]['total_per_unit'] > 0 ) ? ( $this->typeTotals[$billColumnSetting['id']]['up_to_date_amount'] / $this->typeTotals[$billColumnSetting['id']]['total_per_unit'] * 100 ) : 0;

			unset( $billColumnSetting );
		}

		$this->elementTotals = $elementTotals;

		$this->generateBillElementPages($elements, 1, array(), $itemPages);

		$pages = SplFixedArray::fromArray($itemPages);

		return $pages;
	}

	public function generateBillElementPages(Array $billElements, $pageCount, $ancestors, &$itemPages)
	{
		$itemPages[$pageCount] = array();
		$maxRows               = $this->getMaxRows();
		$ancestors             = ( is_array($ancestors) && count($ancestors) ) ? $ancestors : array();

		$blankRow                                   = new SplFixedArray(self::TOTAL_BILL_ITEM_PROPERTY);
		$blankRow[self::ROW_BILL_ITEM_ID]           = - 1; //id
		$blankRow[self::ROW_BILL_ITEM_ROW_IDX]      = null; //row index
		$blankRow[self::ROW_BILL_ITEM_DESCRIPTION]  = null; //description
		$blankRow[self::ROW_BILL_ITEM_LEVEL]        = 0; //level
		$blankRow[self::ROW_BILL_ITEM_TYPE]         = self::ROW_TYPE_BLANK; //type
		$blankRow[self::ROW_BILL_ITEM_UNIT]         = null; //unit
		$blankRow[self::ROW_BILL_ITEM_QTY_PER_UNIT] = null; //unit
		$blankRow[self::ROW_BILL_ITEM_RATE]         = null; //unit

		//blank row
		array_push($itemPages[$pageCount], $blankRow);//starts with a blank row
		$rowCount = 1;

		foreach ( $ancestors as $row )
		{
			array_push($itemPages[$pageCount], $row);
			$rowCount += 1;
			unset( $row );
		}

		$ancestors    = array();
		$itemIndex    = 1;
		$counterIndex = 0;//display item's index in BQ

		foreach ( $billElements as $x => $billElement )
		{
			$occupiedRows = Utilities::justify($billElements[$x]['description'], $this->MAX_CHARACTERS);

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
				$this->generateBillElementPages($billElements, $pageCount, $ancestors, $itemPages);
				break;
			}

			foreach ( $occupiedRows as $key => $occupiedRow )
			{
				if ( $key == 0 )
				{
					$counterIndex ++;
				}

				$row                                   = new SplFixedArray(self::TOTAL_BILL_ITEM_PROPERTY);
				$row[self::ROW_BILL_ITEM_ROW_IDX]      = ( $key == 0 ) ? $counterIndex : null;
				$row[self::ROW_BILL_ITEM_DESCRIPTION]  = $occupiedRow;
				$row[self::ROW_BILL_ITEM_LEVEL]        = null;
				$row[self::ROW_BILL_ITEM_TYPE]         = null;
				$row[self::ROW_BILL_ITEM_ID]           = null;
				$row[self::ROW_BILL_ITEM_UNIT]         = null; //unit
				$row[self::ROW_BILL_ITEM_QTY_PER_UNIT] = null; //unit
				$row[self::ROW_BILL_ITEM_RATE]         = null; //unit

				if ( $key + 1 == $occupiedRows->count() )
				{
					$row[self::ROW_BILL_ITEM_ID]           = $billElement['id']; //only work item will have id set so we can use it to display rates and quantities
					$row[self::ROW_BILL_ITEM_UNIT]         = null;
					$row[self::ROW_BILL_ITEM_RATE]         = null;
					$row[self::ROW_BILL_ITEM_QTY_PER_UNIT] = null;
				}

				array_push($itemPages[$pageCount], $row);

				unset( $row );
			}

			//blank row
			array_push($itemPages[$pageCount], $blankRow);

			$rowCount ++;//plus one blank row;
			$itemIndex ++;

			unset( $billElements[$x], $occupiedRows );
		}
	}

	protected function setOrientationAndSize()
	{
		$this->orientation = self::ORIENTATION_LANDSCAPE;
		$this->setPageFormat($this->generatePageFormat());
	}

	public function setPageFormat($pageFormat)
	{
		$this->pageFormat = $pageFormat;
	}

	protected function generatePageFormat()
	{
		return $pf = array(
			'page_format'       => self::PAGE_FORMAT_A4,
			'minimum-font-size' => $this->fontSize,
			'width'             => 800,
			'height'            => 595,
			'pdf_margin_top'    => 8,
			'pdf_margin_right'  => 8,
			'pdf_margin_bottom' => 3,
			'pdf_margin_left'   => 8
		);
	}

	public function setMaxCharactersPerLine()
	{
		$this->MAX_CHARACTERS = 48;
	}

	public function getMaxRows()
	{
		return $maxRows = 33;
	}

	protected function getElements()
	{
		$stmt = $this->pdo->prepare("SELECT e.id, e.description, e.note
		FROM " . SubPackagePostContractBillItemRateTable::getInstance()->getTableName() . " rate
		JOIN " . BillItemTable::getInstance()->getTableName() . " i ON rate.bill_item_id  = i.id AND i.deleted_at IS NULL
		JOIN " . BillElementTable::getInstance()->getTableName() . " e ON i.element_id =  e.id AND e.deleted_at IS NULL
		JOIN " . ProjectStructureTable::getInstance()->getTableName() . " b ON b.id =  e.project_structure_id AND b.deleted_at IS NULL
		WHERE b.id = " . $this->bill->id . " AND rate.sub_package_id = " . $this->subPackage->id . " GROUP BY e.id ORDER BY e.priority ASC");

		$stmt->execute();

		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	protected function getTypeItems()
	{
		$stmt = $this->pdo->prepare("SELECT type_ref.id, stype.bill_column_setting_id, cs.name as bill_column_name,
		type_ref.new_name, stype.sub_package_id, stype.counter, type_ref.post_contract_id
		FROM " . SubPackageTypeReferenceTable::getInstance()->getTableName() . " stype
		JOIN " . PostContractStandardClaimTypeReferenceTable::getInstance()->getTableName() . "
		type_ref ON type_ref.bill_column_setting_id = stype.bill_column_setting_id AND type_ref.counter = stype.counter
		JOIN " . BillColumnSettingTable::getInstance()->getTableName() . " cs ON cs.id = stype.bill_column_setting_id
		WHERE cs.project_structure_id = " . $this->bill->id . " AND stype.sub_package_id = " . $this->subPackage->id . "
		ORDER BY stype.bill_column_setting_id, stype.counter ASC");

		$stmt->execute();

		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

}