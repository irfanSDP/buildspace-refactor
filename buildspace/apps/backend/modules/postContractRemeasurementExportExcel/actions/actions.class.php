<?php

/**
 * postContractRemeasurementExportExcel actions.
 *
 * @package    buildspace
 * @subpackage postContractRemeasurementExportExcel
 * @author     1337 developers
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class postContractRemeasurementExportExcelActions extends BaseActions {

	public function executeExportSelectedTypes(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless(
			$request->isMethod('POST') AND
			$bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('billId')) AND
			$postContract = Doctrine_Core::getTable('PostContract')->findOneBy('project_structure_id', $bill->root_id)
		);

		session_write_close();

		$project  = $postContract->ProjectStructure;
		$typeIds  = json_decode($request->getParameter('selectedRows'), true);
		$filterBy = $request->getParameter('opt');
		$records  = array();

		$printingPageTitle = $request->getParameter('printingPageTitle');
		$descriptionFormat = $request->getParameter('descriptionFormat');
		$printNoCents      = ( $request->getParameter('printNoCents') == 't' ) ? true : false;

		if ( !empty( $typeIds ) )
		{
			// if current bill types is standard but provisional then list all the items associated with it
			if ( $bill->BillType->type == BillType::TYPE_PROVISIONAL )
			{
				$filterBy = PostContractBillItemRate::REMEASUREMENT_FILTER_BY_ALL_ITEMS;
			}

			$query = DoctrineQuery::create()
				->select('s.id, s.name, s.quantity')
				->from('BillColumnSetting s')
				->where('s.project_structure_id = ?', $bill->id)
				->andWhereIn('s.id', $typeIds)
				->addOrderBy('s.id ASC');

			if ( $filterBy != PostContractBillItemRate::REMEASUREMENT_FILTER_BY_ALL_ITEMS )
			{
				$query->leftJoin('s.ProjectStructure p')
					->leftJoin('p.Elements e')
					->leftJoin('e.Items i')->andWhere('i.type = ?', BillItem::TYPE_ITEM_PROVISIONAL);
			}

			$records = $query->fetchArray();

			foreach ( $records as $key => $billType )
			{
				$omission = 0;
				$addition = 0;

				$remeasureClaims = ProjectStructureTable::getPostContractRemeasurementTotalItemRateByBillColumnSettingIdGroupByElement($bill, $billType, $filterBy);

				foreach ( $remeasureClaims as $remeasureClaim )
				{
					$omission += $remeasureClaim[0]['omission'];
					$addition += $remeasureClaim[0]['addition'];
				}

				$records[$key]['omission']             = $omission * $billType['quantity'];
				$records[$key]['addition']             = $addition * $billType['quantity'];
				$records[$key]['nettAdditionOmission'] = $records[$key]['addition'] - $records[$key]['omission'];

				unset( $billType );
			}
		}

		$reportGenerator = new sfRemeasurementTypeReportGenerator($postContract, $bill, $records, $descriptionFormat);
		$pages           = $reportGenerator->generatePages();

		$excelGenerator = new sfPostContractRemeasurementTypeExcelGenerator(
			$project,
			null,
			$printingPageTitle,
			$reportGenerator->getPrintSettings()
		);

		if ( empty( $records ) )
		{
			$excelGenerator->generateExcelFile();

			return $this->sendExportExcelHeader($excelGenerator->fileInfo['filename'], $excelGenerator->savePath . DIRECTORY_SEPARATOR . $excelGenerator->fileInfo['filename'] . $excelGenerator->fileInfo['extension']);
		}

		if ( $pages instanceof SplFixedArray )
		{
			$excelGenerator->setTotalOmission($reportGenerator->totalOmission);
			$excelGenerator->setTotalAddition($reportGenerator->totalAddition);

			$excelGenerator->process($pages, false, $printingPageTitle, null, "{$project->title} > {$bill->title}", $printNoCents, null);
		}

		$excelGenerator->generateExcelFile();

		return $this->sendExportExcelHeader($excelGenerator->fileInfo['filename'], $excelGenerator->savePath . DIRECTORY_SEPARATOR . $excelGenerator->fileInfo['filename'] . $excelGenerator->fileInfo['extension']);
	}

	public function executeExportExcelSelectedElementByTypes(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless(
			$request->isMethod('POST') AND
			$bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('billId')) AND
			$postContract = Doctrine_Core::getTable('PostContract')->findOneBy('project_structure_id', $bill->root_id)
		);

		session_write_close();

		$typeIds  = json_decode($request->getParameter('selectedRows'), true);
		$filterBy = $request->getParameter('opt');
		$types    = array();
		$records  = array();

		$printingPageTitle = $request->getParameter('printingPageTitle');
		$descriptionFormat = $request->getParameter('descriptionFormat');
		$printNoCents      = ( $request->getParameter('printNoCents') == 't' ) ? true : false;

		if ( !empty( $typeIds ) )
		{
			// if current bill types is standard but provisional then list all the items associated with it
			if ( $bill->BillType->type == BillType::TYPE_PROVISIONAL )
			{
				$filterBy = PostContractBillItemRate::REMEASUREMENT_FILTER_BY_ALL_ITEMS;
			}

			$types = DoctrineQuery::create()
				->select('s.id, s.name, s.quantity')
				->from('BillColumnSetting s')
				->where('s.project_structure_id = ?', $bill->id)
				->andWhereIn('s.id', $typeIds)
				->addOrderBy('s.id ASC')
				->fetchArray();

			$query = DoctrineQuery::create()
				->select('e.id, e.description')
				->from('BillElement e')
				->where('e.project_structure_id = ?', $bill->id)
				->addOrderBy('e.priority ASC');

			if ( $filterBy != PostContractBillItemRate::REMEASUREMENT_FILTER_BY_ALL_ITEMS )
			{
				$query->leftJoin('e.Items i')->andWhere('i.type = ?', BillItem::TYPE_ITEM_PROVISIONAL);
			}

			$elements = $query->fetchArray();

			foreach ( $types as $type )
			{
				// get element remeasurement's claim costing
				$elementTotalRates = ProjectStructureTable::getPostContractRemeasurementTotalItemRateByBillColumnSettingIdGroupByElement($bill, $type, $filterBy);

				foreach ( $elements as $element )
				{
					$omission = 0;
					$addition = 0;

					if ( array_key_exists($element['id'], $elementTotalRates) )
					{
						$omission = $elementTotalRates[$element['id']][0]['omission'];
						$addition = $elementTotalRates[$element['id']][0]['addition'];
					}

					$element['omission']             = Utilities::prelimRounding($omission);
					$element['addition']             = Utilities::prelimRounding($addition);
					$element['nettAdditionOmission'] = $element['addition'] - $element['omission'];

					$records[$type['id']][] = $element;

					unset( $element );
				}

				unset( $elementTotalRates );
			}
		}

		$project = $postContract->ProjectStructure;

		$reportGenerator = new sfRemeasurementTypesElementReportGenerator($postContract, $bill, $descriptionFormat);

		$excelGenerator = new sfPostContractRemeasurementTypeExcelGenerator(
			$project,
			null,
			$printingPageTitle,
			$reportGenerator->getPrintSettings()
		);

		if ( empty( $types ) )
		{
			$excelGenerator->generateExcelFile();

			return $this->sendExportExcelHeader($excelGenerator->fileInfo['filename'], $excelGenerator->savePath . DIRECTORY_SEPARATOR . $excelGenerator->fileInfo['filename'] . $excelGenerator->fileInfo['extension']);
		}

		foreach ( $types as $type )
		{
			if ( !isset( $records[$type['id']] ) )
			{
				continue;
			}

			// will pass the types and elements record into print out generator
			$reportGenerator->setElements($records[$type['id']]);

			$pages = $reportGenerator->generatePages();

			if ( !( $pages instanceof SplFixedArray ) )
			{
				continue;
			}

			$excelGenerator->setTotalOmission($reportGenerator->totalOmission);
			$excelGenerator->setTotalAddition($reportGenerator->totalAddition);

			$excelGenerator->process($pages, false, $printingPageTitle, null, "{$project->title} > {$bill->title} > {$type['name']}", $printNoCents, null);
		}

		$excelGenerator->generateExcelFile();

		return $this->sendExportExcelHeader($excelGenerator->fileInfo['filename'], $excelGenerator->savePath . DIRECTORY_SEPARATOR . $excelGenerator->fileInfo['filename'] . $excelGenerator->fileInfo['extension']);
	}

	public function executeExportExcelSelectedElements(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless(
			$request->isMethod('POST') AND
			$billType = Doctrine_Core::getTable('BillColumnSetting')->find($request->getParameter('bill_type_id')) and
			$bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('billId')) AND
			$postContract = Doctrine_Core::getTable('PostContract')->findOneBy('project_structure_id', $bill->root_id)
		);

		session_write_close();

		$elementIds = json_decode($request->getParameter('selectedRows'), true);
		$records    = array();

		$printingPageTitle = $request->getParameter('printingPageTitle');
		$descriptionFormat = $request->getParameter('descriptionFormat');
		$printNoCents      = ( $request->getParameter('printNoCents') == 't' ) ? true : false;

		if ( !empty( $elementIds ) )
		{
			$filterBy = $request->getParameter('opt');

			// if current bill types is standard but provisional then list all the items associated with it
			if ( $bill->BillType->type == BillType::TYPE_PROVISIONAL )
			{
				$filterBy = PostContractBillItemRate::REMEASUREMENT_FILTER_BY_ALL_ITEMS;
			}

			$query = DoctrineQuery::create()
				->select('e.id, e.description')
				->from('BillElement e')
				->where('e.project_structure_id = ?', $bill->id)
				->andWhereIn('e.id', $elementIds)
				->addOrderBy('e.priority ASC');

			if ( $filterBy != PostContractBillItemRate::REMEASUREMENT_FILTER_BY_ALL_ITEMS )
			{
				$query->leftJoin('e.Items i')->andWhere('i.type = ?', BillItem::TYPE_ITEM_PROVISIONAL);
			}

			$elements = $query->fetchArray();

			// get element remeasurement's claim costing
			$elementTotalRates = ProjectStructureTable::getPostContractRemeasurementTotalItemRateByBillColumnSettingIdGroupByElement($bill, $billType->toArray(), $filterBy);

			foreach ( $elements as $element )
			{
				$omission = 0;
				$addition = 0;

				if ( array_key_exists($element['id'], $elementTotalRates) )
				{
					$omission = $elementTotalRates[$element['id']][0]['omission'];
					$addition = $elementTotalRates[$element['id']][0]['addition'];
				}

				$element['omission']             = $omission;
				$element['addition']             = $addition;
				$element['nettAdditionOmission'] = $element['addition'] - $element['omission'];

				$records[] = $element;

				unset( $element );
			}

			unset( $elementTotalRates );
		}

		$project = $postContract->ProjectStructure;

		$reportGenerator = new sfRemeasurementElementReportGenerator($postContract, $bill, $records, $descriptionFormat);
		$pages           = $reportGenerator->generatePages();

		$excelGenerator = new sfPostContractRemeasurementTypeExcelGenerator(
			$project,
			null,
			$printingPageTitle,
			$reportGenerator->getPrintSettings()
		);

		if ( $pages instanceof SplFixedArray )
		{
			$excelGenerator->setTotalOmission($reportGenerator->totalOmission);
			$excelGenerator->setTotalAddition($reportGenerator->totalAddition);

			$excelGenerator->process($pages, false, $printingPageTitle, null, "{$project->title} > {$bill->title} > {$billType['name']}", $printNoCents, null);
		}

		$excelGenerator->generateExcelFile();

		return $this->sendExportExcelHeader($excelGenerator->fileInfo['filename'], $excelGenerator->savePath . DIRECTORY_SEPARATOR . $excelGenerator->fileInfo['filename'] . $excelGenerator->fileInfo['extension']);
	}

	public function executeExportExcelElementsWithAddition(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless(
			$request->isMethod('POST') AND
			$billType = Doctrine_Core::getTable('BillColumnSetting')->find($request->getParameter('bill_type_id')) and
			$bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('billId')) AND
			$postContract = Doctrine_Core::getTable('PostContract')->findOneBy('project_structure_id', $bill->root_id)
		);

		session_write_close();

		$filterBy = $request->getParameter('opt');
		$records  = array();

		$printingPageTitle = $request->getParameter('printingPageTitle');
		$descriptionFormat = $request->getParameter('descriptionFormat');
		$printNoCents      = ( $request->getParameter('printNoCents') == 't' ) ? true : false;

		// if current bill types is standard but provisional then list all the items associated with it
		if ( $bill->BillType->type == BillType::TYPE_PROVISIONAL )
		{
			$filterBy = PostContractBillItemRate::REMEASUREMENT_FILTER_BY_ALL_ITEMS;
		}

		$query = DoctrineQuery::create()
			->select('e.id, e.description')
			->from('BillElement e')
			->where('e.project_structure_id = ?', $bill->id)
			->addOrderBy('e.priority ASC');

		if ( $filterBy != PostContractBillItemRate::REMEASUREMENT_FILTER_BY_ALL_ITEMS )
		{
			$query->leftJoin('e.Items i')->andWhere('i.type = ?', BillItem::TYPE_ITEM_PROVISIONAL);
		}

		$elements = $query->fetchArray();

		// get element remeasurement's claim costing
		$elementTotalRates = ProjectStructureTable::getPostContractRemeasurementTotalItemRateByBillColumnSettingIdGroupByElement($bill, $billType->toArray(), $filterBy);

		foreach ( $elements as $element )
		{
			$omission = 0;
			$addition = 0;

			if ( array_key_exists($element['id'], $elementTotalRates) )
			{
				$omission = $elementTotalRates[$element['id']][0]['omission'];
				$addition = $elementTotalRates[$element['id']][0]['addition'];
			}

			if ( $addition > 0 )
			{
				$element['omission']             = $omission;
				$element['addition']             = $addition;
				$element['nettAdditionOmission'] = $element['addition'] - $element['omission'];

				$records[] = $element;
			}

			unset( $element );
		}

		unset( $elementTotalRates );

		$project = $postContract->ProjectStructure;

		$reportGenerator = new sfRemeasurementElementReportGenerator($postContract, $bill, $records, $descriptionFormat);
		$pages           = $reportGenerator->generatePages();

		$excelGenerator = new sfPostContractRemeasurementTypeExcelGenerator(
			$project,
			null,
			$printingPageTitle,
			$reportGenerator->getPrintSettings()
		);

		if ( $pages instanceof SplFixedArray )
		{
			$excelGenerator->setTotalOmission($reportGenerator->totalOmission);
			$excelGenerator->setTotalAddition($reportGenerator->totalAddition);

			$excelGenerator->process($pages, false, $printingPageTitle, null, "{$project->title} > {$bill->title} > {$billType['name']}", $printNoCents, null);
		}

		$excelGenerator->generateExcelFile();

		return $this->sendExportExcelHeader($excelGenerator->fileInfo['filename'], $excelGenerator->savePath . DIRECTORY_SEPARATOR . $excelGenerator->fileInfo['filename'] . $excelGenerator->fileInfo['extension']);
	}

	public function executeExportExcelSelectedItems(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless(
			$request->isMethod('POST') AND
			$billType = Doctrine_Core::getTable('BillColumnSetting')->find($request->getParameter('bill_type_id')) and
			$bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('billId')) AND
			$postContract = Doctrine_Core::getTable('PostContract')->findOneBy('project_structure_id', $bill->root_id)
		);

		session_write_close();

		$itemIds      = json_decode($request->getParameter('selectedRows'), true);
		$pdo          = $bill->getTable()->getConnection()->getDbh();
		$pageNoPrefix = $bill->BillLayoutSetting->page_no_prefix;
		$roundingType = $bill->BillMarkupSetting->rounding_type;
		$elements     = array();
		$records      = array();

		$printingPageTitle = $request->getParameter('printingPageTitle');
		$descriptionFormat = $request->getParameter('descriptionFormat');
		$printNoCents      = ( $request->getParameter('printNoCents') == 't' ) ? true : false;

		if ( !empty( $itemIds ) )
		{
			$filterBy = $request->getParameter('opt');

			if ( $bill->BillType->type == BillType::TYPE_PROVISIONAL )
			{
				$filterBy = PostContractBillItemRate::REMEASUREMENT_FILTER_BY_ALL_ITEMS;
			}

			list(
				$elementIds, $billItems, $remeasureClaims, $billItemTypeReferences, $billItemTypeRefFormulatedColumns
				) = PostContractBillItemRateTable::getRemeasurementItemListByItemIds($postContract, $billType, $itemIds, $filterBy);

			$stmt = $pdo->prepare("SELECT e.id, e.description FROM " . BillElementTable::getInstance()->getTableName() . " e
			WHERE e.id IN (" . implode(',', $elementIds) . ") AND e.deleted_at IS NULL
			ORDER BY e.priority ASC");

			$stmt->execute();

			$elements = $stmt->fetchAll(PDO::FETCH_ASSOC);

			foreach ( $elements as $element )
			{
				$elementId = $element['id'];

				foreach ( $billItems as $key => $billItem )
				{
					if ( $billItem['element_id'] != $elementId )
					{
						continue;
					}

					$itemTotal                           = $billItem['rate'] * $billItem['qty_per_unit'];
					$billItem['bill_ref']                = BillItemTable::generateBillRef($pageNoPrefix, $billItem['bill_ref_element_no'], $billItem['bill_ref_page_no'], $billItem['bill_ref_char']);
					$billItem['type']                    = (string) $billItem['type'];
					$billItem['uom_id']                  = $billItem['uom_id'] > 0 ? (string) $billItem['uom_id'] : '-1';
					$billItem['linked']                  = false;
					$billItem['markup_rounding_type']    = $roundingType;
					$billItem['has_note']                = ( $billItem['note'] != null && $billItem['note'] != '' ) ? true : false;
					$billItem['item_total']              = Utilities::prelimRounding($itemTotal);
					$billItem['omission-qty_per_unit']   = Utilities::prelimRounding($billItem['qty_per_unit']);
					$billItem['omission-total_per_unit'] = Utilities::prelimRounding($billItem['rate'] * $billItem['qty_per_unit']);
					$billItem['omission-has_build_up']   = false;
					$billItem['addition-qty_per_unit']   = 0;
					$billItem['addition-total_per_unit'] = 0;
					$billItem['addition-has_build_up']   = false;

					if ( array_key_exists($billItem['post_contract_bill_item_rate_id'], $remeasureClaims) )
					{
						$costing = $remeasureClaims[$billItem['post_contract_bill_item_rate_id']];

						$billItem['addition-qty_per_unit']   = Utilities::prelimRounding($costing['qty_per_unit']);
						$billItem['addition-total_per_unit'] = Utilities::prelimRounding($costing['total_per_unit']);
						$billItem['addition-has_build_up']   = $costing['has_build_up'];

						unset( $costing );
					}

					$billItem['nett_addition_omission'] = Utilities::prelimRounding($billItem['addition-total_per_unit'] - $billItem['omission-total_per_unit']);

					if ( array_key_exists($billType->id, $billItemTypeReferences) && array_key_exists($billItem['id'], $billItemTypeReferences[$billType->id]) )
					{
						$billItemTypeRef = $billItemTypeReferences[$billType->id][$billItem['id']];

						unset( $billItemTypeReferences[$billType->id][$billItem['id']] );

						if ( array_key_exists($billItemTypeRef['id'], $billItemTypeRefFormulatedColumns) )
						{
							foreach ( $billItemTypeRefFormulatedColumns[$billItemTypeRef['id']] as $billItemTypeRefFormulatedColumn )
							{
								$billItem['omission-has_build_up'] = $billItemTypeRefFormulatedColumn['has_build_up'];

								unset( $billItemTypeRefFormulatedColumn );
							}
						}
					}

					$records[$elementId][] = $billItem;

					unset( $billItem, $billItems[$key] );
				}
			}
		}

		$project = $postContract->ProjectStructure;

		$reportGenerator = new sfRemeasurementItemReportGenerator($postContract, $bill, $descriptionFormat);
		$reportGenerator->setAffectedElements($elements);
		$reportGenerator->setItems($records);

		unset( $elements, $records );

		$pages = $reportGenerator->generatePages();

		$sfItemExport = new sfPostContractRemeasurementItemExcelGenerator(
			$project,
			null,
			$printingPageTitle,
			$reportGenerator->printSettings
		);

		$sfItemExport->setTotalAddition($reportGenerator->totalAdditionByElement);
		$sfItemExport->setTotalOmission($reportGenerator->totalOmissionByElement);

		$sfItemExport->process($pages, false, $printingPageTitle, $project->title, '', $printNoCents, $reportGenerator->totalPage);

		return $this->sendExportExcelHeader($sfItemExport->fileInfo['filename'], $sfItemExport->savePath . DIRECTORY_SEPARATOR . $sfItemExport->fileInfo['filename'] . $sfItemExport->fileInfo['extension']);
	}

	public function executeExportExcelItemsWithAdditionOnly(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless(
			$request->isMethod('POST') AND
			$billType = Doctrine_Core::getTable('BillColumnSetting')->find($request->getParameter('bill_type_id')) and
			$bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('billId')) AND
			$postContract = Doctrine_Core::getTable('PostContract')->findOneBy('project_structure_id', $bill->root_id)
		);

		session_write_close();

		$pdo          = $bill->getTable()->getConnection()->getDbh();
		$pageNoPrefix = $bill->BillLayoutSetting->page_no_prefix;
		$roundingType = $bill->BillMarkupSetting->rounding_type;
		$elements     = array();
		$records      = array();
		$filterBy     = $request->getParameter('opt');

		$printingPageTitle = $request->getParameter('printingPageTitle');
		$descriptionFormat = $request->getParameter('descriptionFormat');
		$printNoCents      = ( $request->getParameter('printNoCents') == 't' ) ? true : false;

		if ( $bill->BillType->type == BillType::TYPE_PROVISIONAL )
		{
			$filterBy = PostContractBillItemRate::REMEASUREMENT_FILTER_BY_ALL_ITEMS;
		}

		list(
			$elementIds, $billItems, $remeasurementClaims, $billItemTypeReferences, $billItemTypeRefFormulatedColumns
			) = PostContractBillItemRateTable::getRemeasurementItemListWithAdditionOnly($bill, $postContract, $billType, $filterBy);

		if ( !empty( $elementIds ) )
		{
			$stmt = $pdo->prepare("SELECT e.id, e.description FROM " . BillElementTable::getInstance()->getTableName() . " e
			WHERE e.id IN (" . implode(',', $elementIds) . ") AND e.deleted_at IS NULL
			ORDER BY e.priority ASC");

			$stmt->execute();

			$elements = $stmt->fetchAll(PDO::FETCH_ASSOC);

			foreach ( $elements as $element )
			{
				$elementId = $element['id'];

				foreach ( $billItems as $key => $billItem )
				{
					if ( $billItem['element_id'] != $elementId )
					{
						continue;
					}

					$itemTotal                           = $billItem['rate'] * $billItem['qty_per_unit'];
					$billItem['bill_ref']                = BillItemTable::generateBillRef($pageNoPrefix, $billItem['bill_ref_element_no'], $billItem['bill_ref_page_no'], $billItem['bill_ref_char']);
					$billItem['type']                    = (string) $billItem['type'];
					$billItem['uom_id']                  = $billItem['uom_id'] > 0 ? (string) $billItem['uom_id'] : '-1';
					$billItem['linked']                  = false;
					$billItem['markup_rounding_type']    = $roundingType;
					$billItem['has_note']                = ( $billItem['note'] != null && $billItem['note'] != '' ) ? true : false;
					$billItem['item_total']              = Utilities::prelimRounding($itemTotal);
					$billItem['omission-qty_per_unit']   = Utilities::prelimRounding($billItem['qty_per_unit']);
					$billItem['omission-total_per_unit'] = Utilities::prelimRounding($billItem['rate'] * $billItem['qty_per_unit']);
					$billItem['omission-has_build_up']   = false;
					$billItem['addition-qty_per_unit']   = 0;
					$billItem['addition-total_per_unit'] = 0;
					$billItem['addition-has_build_up']   = false;

					if ( array_key_exists($billItem['post_contract_bill_item_rate_id'], $remeasurementClaims) )
					{
						$costing = $remeasurementClaims[$billItem['post_contract_bill_item_rate_id']];

						$billItem['addition-qty_per_unit']   = Utilities::prelimRounding($costing['qty_per_unit']);
						$billItem['addition-total_per_unit'] = Utilities::prelimRounding($costing['total_per_unit']);
						$billItem['addition-has_build_up']   = $costing['has_build_up'];

						unset( $costing );
					}

					$billItem['nett_addition_omission'] = Utilities::prelimRounding($billItem['addition-total_per_unit'] - $billItem['omission-total_per_unit']);

					if ( array_key_exists($billType->id, $billItemTypeReferences) && array_key_exists($billItem['id'], $billItemTypeReferences[$billType->id]) )
					{
						$billItemTypeRef = $billItemTypeReferences[$billType->id][$billItem['id']];

						unset( $billItemTypeReferences[$billType->id][$billItem['id']] );

						if ( array_key_exists($billItemTypeRef['id'], $billItemTypeRefFormulatedColumns) )
						{
							foreach ( $billItemTypeRefFormulatedColumns[$billItemTypeRef['id']] as $billItemTypeRefFormulatedColumn )
							{
								$billItem['omission-has_build_up'] = $billItemTypeRefFormulatedColumn['has_build_up'];

								unset( $billItemTypeRefFormulatedColumn );
							}
						}
					}

					$records[$elementId][] = $billItem;

					unset( $billItem, $billItems[$key] );
				}
			}
		}

		$reportGenerator = new sfRemeasurementItemReportGenerator($postContract, $bill, $descriptionFormat);
		$reportGenerator->setAffectedElements($elements);
		$reportGenerator->setItems($records);

		unset( $elements, $records );

		$project = $postContract->ProjectStructure;

		$pages = $reportGenerator->generatePages();

		$sfItemExport = new sfPostContractRemeasurementItemExcelGenerator(
			$project,
			null,
			$printingPageTitle,
			$reportGenerator->printSettings
		);

		$sfItemExport->setTotalAddition($reportGenerator->totalAdditionByElement);
		$sfItemExport->setTotalOmission($reportGenerator->totalOmissionByElement);

		$sfItemExport->process($pages, false, $printingPageTitle, $project->title, '', $printNoCents, $reportGenerator->totalPage);

		return $this->sendExportExcelHeader($sfItemExport->fileInfo['filename'], $sfItemExport->savePath . DIRECTORY_SEPARATOR . $sfItemExport->fileInfo['filename'] . $sfItemExport->fileInfo['extension']);
	}

	public function executeExportExcelSelectedItemsWithBuildUpQty(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless(
			$request->isMethod('POST') AND
			$billType = Doctrine_Core::getTable('BillColumnSetting')->find($request->getParameter('bill_type_id')) and
			$bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('billId')) AND
			$postContract = Doctrine_Core::getTable('PostContract')->findOneBy('project_structure_id', $bill->root_id)
		);

		session_write_close();

		$project            = $postContract->ProjectStructure;
		$billColumnSettings = array( $billType );
		$itemIds            = (array) json_decode($request->getParameter('selectedRows'), true);
		$pageNoPrefix       = $bill->BillLayoutSetting->page_no_prefix;
		$typesArray         = array( PostContractRemeasurementBuildUpQuantityItem::OMISSION_TYPE_TEXT, PostContractRemeasurementBuildUpQuantityItem::ADDITION_TYPE_TEXT );

		$printingPageTitle = $request->getParameter('printingPageTitle');
		$descriptionFormat = $request->getParameter('descriptionFormat');
		$printNoCents      = ( $request->getParameter('printNoCents') == 't' ) ? true : false;
		$filterBy          = $request->getParameter('opt');

		if ( $bill->BillType->type == BillType::TYPE_PROVISIONAL )
		{
			$filterBy = PostContractBillItemRate::REMEASUREMENT_FILTER_BY_ALL_ITEMS;
		}

		list(
			$elementIds, $billItems, $remeasureClaims,
			$billItemTypeReferences, $billItemTypeRefFormulatedColumns, $buildUpQuantityItems,
			$billBuildUpQuantitySummaries, $quantityPerUnitByColumns, $unitsDimensions
			) = PostContractBillItemRateTable::getRemeasurementItemListWithBuildUpQtyOnly($postContract, $billType, $itemIds, $filterBy);

		list(
			$soqItemsData, $soqFormulatedColumns, $manualBuildUpQuantityItems, $importedBuildUpQuantityItems
			) = ScheduleOfQuantityBillItemXrefTable::getSelectedItemsBuildUpQuantity($project, $billColumnSettings, $billItems);

		$reportPrintGenerator = new sfPostContractRemeasurementItemBuildUpQtyReportGenerator($postContract, $bill, $descriptionFormat);
		$reportPrintGenerator->setOrientationAndSize('portrait');

		$excelGenerator = new sfPostContractRemeasurementItemBuildUpQtyExcelGenerator($project, $printingPageTitle, $reportPrintGenerator->getPrintSettings());

		if ( empty( $billItems ) )
		{
			$excelGenerator->generateExcelFile();

			return $this->sendExportExcelHeader($excelGenerator->fileInfo['filename'], $excelGenerator->savePath . DIRECTORY_SEPARATOR . $excelGenerator->fileInfo['filename'] . $excelGenerator->fileInfo['extension']);
		}

		foreach ( $billItems as $billItem )
		{
			// only generate print-out for item level only
			if ( $billItem['type'] == BillItem::TYPE_HEADER OR $billItem['type'] == BillItem::TYPE_HEADER_N )
			{
				continue;
			}

			$dimensions                          = array();
			$itemTotal                           = $billItem['rate'] * $billItem['qty_per_unit'];
			$billItem['bill_ref']                = BillItemTable::generateBillRef($pageNoPrefix, $billItem['bill_ref_element_no'], $billItem['bill_ref_page_no'], $billItem['bill_ref_char']);
			$billItem['type']                    = (string) $billItem['type'];
			$billItem['uom_id']                  = $billItem['uom_id'] > 0 ? (string) $billItem['uom_id'] : '-1';
			$billItem['item_total']              = Utilities::prelimRounding($itemTotal);
			$billItem['omission-qty_per_unit']   = Utilities::prelimRounding($billItem['qty_per_unit']);
			$billItem['omission-total_per_unit'] = Utilities::prelimRounding($billItem['rate'] * $billItem['qty_per_unit']);
			$billItem['omission-has_build_up']   = false;
			$billItem['addition-qty_per_unit']   = 0;
			$billItem['addition-total_per_unit'] = 0;
			$billItem['addition-has_build_up']   = false;

			if ( array_key_exists($billItem['post_contract_bill_item_rate_id'], $remeasureClaims) )
			{
				$costing = $remeasureClaims[$billItem['post_contract_bill_item_rate_id']];

				$billItem['addition-qty_per_unit']   = Utilities::prelimRounding($costing['qty_per_unit']);
				$billItem['addition-total_per_unit'] = Utilities::prelimRounding($costing['total_per_unit']);
				$billItem['addition-has_build_up']   = $costing['has_build_up'];

				unset( $costing );
			}

			if ( array_key_exists($billType->id, $billItemTypeReferences) && array_key_exists($billItem['id'], $billItemTypeReferences[$billType->id]) )
			{
				$billItemTypeRef = $billItemTypeReferences[$billType->id][$billItem['id']];

				unset( $billItemTypeReferences[$billType->id][$billItem['id']] );

				if ( array_key_exists($billItemTypeRef['id'], $billItemTypeRefFormulatedColumns) )
				{
					foreach ( $billItemTypeRefFormulatedColumns[$billItemTypeRef['id']] as $billItemTypeRefFormulatedColumn )
					{
						$billItem['omission-has_build_up'] = $billItemTypeRefFormulatedColumn['has_build_up'];

						unset( $billItemTypeRefFormulatedColumn );
					}
				}
			}

			// get dimension based on bill item's UOM ID
			foreach ( $unitsDimensions as $unitsDimension )
			{
				if ( $billItem['uom_id'] != $unitsDimension['unit_of_measurement_id'] )
				{
					continue;
				}

				$dimensions[] = $unitsDimension['Dimension'];
			}

			// set available dimension
			$reportPrintGenerator->setAvailableTableHeaderDimensions($dimensions);
			$excelGenerator->setDimensions($dimensions);

			foreach ( $typesArray as $type )
			{
				$billItemId                 = ( $type == PostContractRemeasurementBuildUpQuantityItem::OMISSION_TYPE_TEXT ) ? $billItem['id'] : $billItem['post_contract_bill_item_rate_id'];
				$quantityPerUnit            = $billItem[$type . '-qty_per_unit'];
				$buildUpItems               = array();
				$buildUpQuantitySummaryInfo = array();
				$soqBuildUpItems            = array();

				if ( isset( $buildUpQuantityItems[$type][$billItemId] ) )
				{
					$buildUpItems = $buildUpQuantityItems[$type][$billItemId];

					unset( $buildUpQuantityItems[$type][$billItemId] );
				}

				if ( isset( $billBuildUpQuantitySummaries[$type][$billItemId] ) )
				{
					$buildUpQuantitySummaryInfo = $billBuildUpQuantitySummaries[$type][$billItemId];

					unset( $billBuildUpQuantitySummaries[$type][$billItemId] );
				}

				// only get SoQ's Build Up Item list for Omission type
				if ( $type == PostContractRemeasurementBuildUpQuantityItem::OMISSION_TYPE_TEXT AND isset( $soqItemsData[$billType->id][$billItemId] ) )
				{
					$soqBuildUpItems = $soqItemsData[$billType->id][$billItemId];

					unset( $soqItemsData[$billType->id][$billItemId] );
				}

				// don't generate page that has no manual build up and soq build up item(s)
				if ( count($buildUpItems) == 0 AND count($soqBuildUpItems) == 0 )
				{
					unset( $buildUpItems, $soqBuildUpItems, $buildUpQuantitySummaryInfo );

					continue;
				}

				// need to pass build up qty item(s) into generator to correctly generate the printout page
				$reportPrintGenerator->setBuildUpQuantityItems($buildUpItems);

				$reportPrintGenerator->setSOQBuildUpQuantityItems($soqBuildUpItems);

				$reportPrintGenerator->getSOQFormulatedColumn($soqFormulatedColumns);

				$reportPrintGenerator->setManualBuildUpQuantityMeasurements($manualBuildUpQuantityItems);
				$reportPrintGenerator->setImportedBuildUpQuantityMeasurements($importedBuildUpQuantityItems);

				$pages        = $reportPrintGenerator->generatePages();
				$billItemInfo = $reportPrintGenerator->setupBillItemHeader($billItem, $billItem['bill_ref']);

				if ( isset( $quantityPerUnitByColumns[$type][$billType->id][$billItemId][0] ) )
				{
					$quantityPerUnit = $quantityPerUnitByColumns[$type][$billType->id][$billItemId][0];

					unset( $quantityPerUnitByColumns[$type][$billType->id][$billItemId] );
				}

				$excelGenerator->setBillItemInfo($billItemInfo);
				$excelGenerator->setBillItemUOM($billItem['uom_symbol']);
				$excelGenerator->setBuildUpQuantitySummaryInfo($buildUpQuantitySummaryInfo);
				$excelGenerator->setQuantityPerUnit($quantityPerUnit);

				if ( !( $pages instanceof SplFixedArray ) )
				{
					continue;
				}

				$excelGenerator->process($pages, false, $printingPageTitle, null, $project['title'] . ' > ' . $bill['title'] . ' > ' . $billType['name'] . ' > ' . ucfirst($type), $printNoCents, null);
			}

			unset( $billItem );
		}

		$excelGenerator->generateExcelFile();

		return $this->sendExportExcelHeader($excelGenerator->fileInfo['filename'], $excelGenerator->savePath . DIRECTORY_SEPARATOR . $excelGenerator->fileInfo['filename'] . $excelGenerator->fileInfo['extension']);
	}

}