<?php

/**
 * postContractSubPackageRemeasurementReport actions.
 *
 * @package    buildspace
 * @subpackage postContractSubPackageRemeasurementReport
 * @author     1337 developers
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class postContractSubPackageRemeasurementReportActions extends BaseActions {

	public function executeGetElementsAndItemsByTypes(sfWebRequest $request)
	{
		$this->forward404Unless(
			$request->isXmlHttpRequest() AND
			$request->hasParameter('type_ids') AND
			$request->hasParameter('opt') AND
			$subPackage = Doctrine_Core::getTable('SubPackage')->find($request->getPostParameter('subPackageId')) and
			$bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getPostParameter('bill_id'))
		);

		$pdo           = $subPackage->getTable()->getConnection()->getDbh();
		$typeIds       = json_decode($request->getParameter('type_ids'), true);
		$data          = array();
		$filterByQuery = null;
		$filterBy      = $request->getPostParameter('opt');

		if ( !empty( $typeIds ) )
		{
			// if current bill types is standard but provisional then list all the items associated with it
			if ( $bill->BillType->type == BillType::TYPE_PROVISIONAL )
			{
				$filterBy = PostContractBillItemRate::REMEASUREMENT_FILTER_BY_ALL_ITEMS;
			}

			if ( $filterBy != PostContractBillItemRate::REMEASUREMENT_FILTER_BY_ALL_ITEMS )
			{
				$filterByQuery = "AND i.type = " . BillItem::TYPE_ITEM_PROVISIONAL;
			}

			$stmt = $pdo->prepare("SELECT DISTINCT i.id as item_id, e.id as element_id, e.priority
			FROM " . SubPackagePostContractBillItemRateTable::getInstance()->getTableName() . " rate
			JOIN " . BillItemTable::getInstance()->getTableName() . " i ON rate.bill_item_id  = i.id AND i.deleted_at IS NULL
			JOIN " . BillElementTable::getInstance()->getTableName() . " e ON i.element_id =  e.id AND e.deleted_at IS NULL
			JOIN " . ProjectStructureTable::getInstance()->getTableName() . " s ON e.project_structure_id = s.id AND s.deleted_at IS NULL
			JOIN " . BillColumnSettingTable::getInstance()->getTableName() . " bcs ON bcs.project_structure_id = s.id AND bcs.deleted_at IS NULL
			JOIN " . BillTypeTable::getInstance()->getTableName() . " t ON t.project_structure_id = s.id
			JOIN " . BillLayoutSettingTable::getInstance()->getTableName() . " bls ON bls.bill_id = s.id
			WHERE s.id = " . $bill->id . " AND e.project_structure_id = " . $bill->id . "
			AND bcs.id IN (" . implode(',', $typeIds) . ") AND rate.sub_package_id = " . $subPackage->id . " {$filterByQuery}
			ORDER BY e.priority ASC");

			$stmt->execute();
			$elements = $stmt->fetchAll(PDO::FETCH_ASSOC);

			foreach ( $typeIds as $typeId )
			{
				if ( count($elements) == 0 )
				{
					$data[$typeId] = array();
					continue;
				}

				$this->generateSelectionKeyArray($elements, $data, $typeId);
			}

			unset( $elements );
		}

		return $this->renderJson($data);
	}

	public function executeGetTypesAndItemsByElement(sfWebRequest $request)
	{
		$this->forward404Unless(
			$request->isXmlHttpRequest() AND
			$request->hasParameter('element_ids') AND
			$request->hasParameter('opt') AND
			$billType = Doctrine_Core::getTable('BillColumnSetting')->find($request->getPostParameter('bill_type_id')) and
			$subPackage = Doctrine_Core::getTable('SubPackage')->find($request->getPostParameter('subPackageId')) and
			$bill = Doctrine_Core::getTable('ProjectStructure')->find($billType->project_structure_id)
		);

		$pdo           = $subPackage->getTable()->getConnection()->getDbh();
		$elementIds    = json_decode($request->getPostParameter('element_ids'), true);
		$data          = array();
		$filterBy      = $request->getPostParameter('opt');
		$typeId        = $billType->id;
		$filterByQuery = null;

		if ( !empty( $elementIds ) )
		{
			// if current bill types is standard but provisional then list all the items associated with it
			if ( $bill->BillType->type == BillType::TYPE_PROVISIONAL )
			{
				$filterBy = PostContractBillItemRate::REMEASUREMENT_FILTER_BY_ALL_ITEMS;
			}

			if ( $filterBy != PostContractBillItemRate::REMEASUREMENT_FILTER_BY_ALL_ITEMS )
			{
				$filterByQuery = "AND i.type = " . BillItem::TYPE_ITEM_PROVISIONAL;
			}

			$stmt = $pdo->prepare("SELECT DISTINCT i.id as item_id, e.id as element_id, e.priority
			FROM " . SubPackagePostContractBillItemRateTable::getInstance()->getTableName() . " rate
			JOIN " . BillItemTable::getInstance()->getTableName() . " i ON rate.bill_item_id  = i.id AND i.deleted_at IS NULL
			JOIN " . BillElementTable::getInstance()->getTableName() . " e ON i.element_id =  e.id AND e.deleted_at IS NULL
			JOIN " . ProjectStructureTable::getInstance()->getTableName() . " s ON e.project_structure_id = s.id AND s.deleted_at IS NULL
			JOIN " . BillColumnSettingTable::getInstance()->getTableName() . " bcs ON bcs.project_structure_id = s.id AND bcs.deleted_at IS NULL
			JOIN " . BillTypeTable::getInstance()->getTableName() . " t ON t.project_structure_id = s.id
			JOIN " . BillLayoutSettingTable::getInstance()->getTableName() . " bls ON bls.bill_id = s.id
			WHERE s.id = " . $bill->id . " AND e.project_structure_id = " . $bill->id . "
			AND e.id IN (" . implode(',', $elementIds) . ") AND rate.sub_package_id = " . $subPackage->id . " {$filterByQuery}
			ORDER BY e.priority ASC");

			$stmt->execute();
			$elements = $stmt->fetchAll(PDO::FETCH_ASSOC);

			if ( count($elements) == 0 )
			{
				$data[$typeId] = array();
			}

			$this->generateSelectionKeyArray($elements, $data, $typeId);

			unset( $elements );
		}

		return $this->renderJson($data);
	}

	public function executeGetTypesAndElementsByItem(sfWebRequest $request)
	{
		$this->forward404Unless(
			$request->isXmlHttpRequest() AND
			$request->hasParameter('item_ids') AND
			$request->hasParameter('opt') AND
			$billType = Doctrine_Core::getTable('BillColumnSetting')->find($request->getPostParameter('bill_type_id')) and
			$subPackage = Doctrine_Core::getTable('SubPackage')->find($request->getPostParameter('subPackageId')) and
			$bill = Doctrine_Core::getTable('ProjectStructure')->find($billType->project_structure_id)
		);

		$pdo           = $subPackage->getTable()->getConnection()->getDbh();
		$itemIds       = json_decode($request->getPostParameter('item_ids'), true);
		$data          = array();
		$typeId        = $billType->id;
		$filterBy      = $request->getPostParameter('opt');
		$filterByQuery = null;

		if ( !empty( $itemIds ) )
		{
			// if current bill types is standard but provisional then list all the items associated with it
			if ( $bill->BillType->type == BillType::TYPE_PROVISIONAL )
			{
				$filterBy = PostContractBillItemRate::REMEASUREMENT_FILTER_BY_ALL_ITEMS;
			}

			if ( $filterBy != PostContractBillItemRate::REMEASUREMENT_FILTER_BY_ALL_ITEMS )
			{
				$filterByQuery = "AND i.type = " . BillItem::TYPE_ITEM_PROVISIONAL;
			}

			$stmt = $pdo->prepare("SELECT DISTINCT i.id as item_id, e.id as element_id, e.priority
			FROM " . SubPackagePostContractBillItemRateTable::getInstance()->getTableName() . " rate
			JOIN " . BillItemTable::getInstance()->getTableName() . " i ON rate.bill_item_id  = i.id AND i.deleted_at IS NULL
			JOIN " . BillElementTable::getInstance()->getTableName() . " e ON i.element_id =  e.id AND e.deleted_at IS NULL
			JOIN " . ProjectStructureTable::getInstance()->getTableName() . " s ON e.project_structure_id = s.id AND s.deleted_at IS NULL
			JOIN " . BillColumnSettingTable::getInstance()->getTableName() . " bcs ON bcs.project_structure_id = s.id AND bcs.deleted_at IS NULL
			JOIN " . BillTypeTable::getInstance()->getTableName() . " t ON t.project_structure_id = s.id
			JOIN " . BillLayoutSettingTable::getInstance()->getTableName() . " bls ON bls.bill_id = s.id
			WHERE s.id = " . $bill->id . " AND e.project_structure_id = " . $bill->id . "
			AND i.id IN (" . implode(',', $itemIds) . ") AND rate.sub_package_id = " . $subPackage->id . " {$filterByQuery}
			ORDER BY e.priority ASC");

			$stmt->execute();
			$elements = $stmt->fetchAll(PDO::FETCH_ASSOC);

			if ( count($elements) == 0 )
			{
				$data[$typeId] = array();
			}

			$this->generateSelectionKeyArray($elements, $data, $typeId);

			unset( $elements );
		}

		return $this->renderJson($data);
	}

	public function executeGetPrintingSelectedTypes(sfWebRequest $request)
	{
		$this->forward404Unless(
			$request->isXmlHttpRequest() AND
			$request->hasParameter('type_ids') AND
			$subPackage = Doctrine_Core::getTable('SubPackage')->find($request->getPostParameter('subPackageId')) AND
			$bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getPostParameter('bill_id'))
		);

		$pdo           = $subPackage->getTable()->getConnection()->getDbh();
		$typeIds       = json_decode($request->getPostParameter('type_ids'), true);
		$filterBy      = $request->getPostParameter('opt');
		$records       = array();
		$filterByQuery = null;

		if ( !empty( $typeIds ) )
		{
			// if current bill types is standard but provisional then list all the items associated with it
			if ( $bill->BillType->type == BillType::TYPE_PROVISIONAL )
			{
				$filterBy = PostContractBillItemRate::REMEASUREMENT_FILTER_BY_ALL_ITEMS;
			}

			if ( $filterBy != PostContractBillItemRate::REMEASUREMENT_FILTER_BY_ALL_ITEMS )
			{
				$filterByQuery = "AND i.type = " . BillItem::TYPE_ITEM_PROVISIONAL;
			}

			$stmt = $pdo->prepare("SELECT DISTINCT bcs.id, bcs.name, bcs.quantity
			FROM " . SubPackagePostContractBillItemRateTable::getInstance()->getTableName() . " rate
			JOIN " . BillItemTable::getInstance()->getTableName() . " i ON rate.bill_item_id  = i.id AND i.deleted_at IS NULL
			JOIN " . BillElementTable::getInstance()->getTableName() . " e ON i.element_id =  e.id AND e.deleted_at IS NULL
			JOIN " . ProjectStructureTable::getInstance()->getTableName() . " s ON e.project_structure_id = s.id AND s.deleted_at IS NULL
			JOIN " . BillColumnSettingTable::getInstance()->getTableName() . " bcs ON bcs.project_structure_id = s.id AND bcs.deleted_at IS NULL
			JOIN " . BillTypeTable::getInstance()->getTableName() . " t ON t.project_structure_id = s.id
			JOIN " . BillLayoutSettingTable::getInstance()->getTableName() . " bls ON bls.bill_id = s.id
			WHERE s.id = " . $bill->id . " AND e.project_structure_id = " . $bill->id . "
			AND bcs.id IN (" . implode(',', $typeIds) . ") AND rate.sub_package_id = " . $subPackage->id . " {$filterByQuery}
			ORDER BY bcs.id ASC");

			$stmt->execute();
			$records = $stmt->fetchAll(PDO::FETCH_ASSOC);

			foreach ( $records as $key => $billType )
			{
				$omission = 0;
				$addition = 0;

				$billColumnSetting     = new BillColumnSetting();
				$billColumnSetting->id = $billType['id'];

				$remeasurementClaims = SubPackagePostContractBillItemRateTable::getPostContractSubPackageRemeasurementTotalItemRateGroupByElement($bill, $subPackage, $billColumnSetting, $filterBy);

				foreach ( $remeasurementClaims as $remeasurementClaim )
				{
					$omission += $remeasurementClaim[0]['omission'];
					$addition += $remeasurementClaim[0]['addition'];
				}

				$records[$key]['omission']             = $omission * $billType['quantity'];
				$records[$key]['addition']             = $addition * $billType['quantity'];
				$records[$key]['nettAdditionOmission'] = $records[$key]['addition'] - $records[$key]['omission'];

				unset( $billColumnSetting, $billType );
			}
		}

		$defaultLastRow = array(
			'id'   => Constants::GRID_LAST_ROW,
			'name' => '',
		);

		array_push($records, $defaultLastRow);

		return $this->renderJson(array(
			'identifier' => 'id',
			'items'      => $records
		));
	}

	public function executeGetPrintingSelectedElementByTypes(sfWebRequest $request)
	{
		$this->forward404Unless(
			$request->isXmlHttpRequest() AND
			$request->hasParameter('type_ids') AND
			$subPackage = Doctrine_Core::getTable('SubPackage')->find($request->getPostParameter('subPackageId')) AND
			$bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getPostParameter('bill_id'))
		);

		$pdo           = $subPackage->getTable()->getConnection()->getDbh();
		$typeIds       = json_decode($request->getPostParameter('type_ids'), true);
		$filterBy      = $request->getPostParameter('opt');
		$records       = array();
		$filterByQuery = null;

		if ( !empty( $typeIds ) )
		{
			$types = DoctrineQuery::create()
				->select('s.id, s.name, s.quantity')
				->from('BillColumnSetting s')
				->where('s.project_structure_id = ?', $bill->id)
				->andWhereIn('s.id', $typeIds)
				->addOrderBy('s.id ASC')
				->fetchArray();

			// if current bill types is standard but provisional then list all the items associated with it
			if ( $bill->BillType->type == BillType::TYPE_PROVISIONAL )
			{
				$filterBy = PostContractBillItemRate::REMEASUREMENT_FILTER_BY_ALL_ITEMS;
			}

			if ( $filterBy != PostContractBillItemRate::REMEASUREMENT_FILTER_BY_ALL_ITEMS )
			{
				$filterByQuery = "AND i.type = " . BillItem::TYPE_ITEM_PROVISIONAL;
			}

			$stmt = $pdo->prepare("SELECT DISTINCT e.id, e.description, e.priority
			FROM " . SubPackagePostContractBillItemRateTable::getInstance()->getTableName() . " rate
			JOIN " . BillItemTable::getInstance()->getTableName() . " i ON rate.bill_item_id  = i.id AND i.deleted_at IS NULL
			JOIN " . BillElementTable::getInstance()->getTableName() . " e ON i.element_id =  e.id AND e.deleted_at IS NULL
			JOIN " . ProjectStructureTable::getInstance()->getTableName() . " s ON e.project_structure_id = s.id AND s.deleted_at IS NULL
			JOIN " . BillColumnSettingTable::getInstance()->getTableName() . " bcs ON bcs.project_structure_id = s.id AND bcs.deleted_at IS NULL
			JOIN " . BillTypeTable::getInstance()->getTableName() . " t ON t.project_structure_id = s.id
			JOIN " . BillLayoutSettingTable::getInstance()->getTableName() . " bls ON bls.bill_id = s.id
			WHERE s.id = " . $bill->id . " AND e.project_structure_id = " . $bill->id . "
			AND bcs.id IN (" . implode(',', $typeIds) . ") AND rate.sub_package_id = " . $subPackage->id . " {$filterByQuery}
			ORDER BY e.priority ASC");

			$stmt->execute();
			$elements = $stmt->fetchAll(PDO::FETCH_ASSOC);

			$attachRowHeader = ( count($elements) > 0 ) ? true : false;

			foreach ( $types as $type )
			{
				$billType     = new BillColumnSetting();
				$billType->id = $type['id'];

				if ( $attachRowHeader )
				{
					$typeRow = array(
						'id'                         => "type-{$type['id']}",
						'name'                       => $type['name'],
						'type'                       => 0,
						'has_note'                   => false,
						'grand_total'                => 0,
						'original_grand_total'       => 0,
						'overall_total_after_markup' => 0,
						'element_sum_total'          => 0,
						'relation_id'                => $bill->id,
					);

					array_push($records, $typeRow);

					unset( $typeRow );
				}

				// get element remeasurement's claim costing
				$elementTotalRates = SubPackagePostContractBillItemRateTable::getPostContractSubPackageRemeasurementTotalItemRateGroupByElement($bill, $subPackage, $billType, $filterBy);

				foreach ( $elements as $element )
				{
					$omission = 0;
					$addition = 0;

					if ( array_key_exists($element['id'], $elementTotalRates) )
					{
						$omission = $elementTotalRates[$element['id']][0]['omission'];
						$addition = $elementTotalRates[$element['id']][0]['addition'];
					}

					$element['id']                   = "bill_type-{$type['id']}-{$element['id']}";
					$element['name']                 = $element['description'];
					$element['omission']             = $omission;
					$element['addition']             = $addition;
					$element['nettAdditionOmission'] = $element['addition'] - $element['omission'];

					$records[] = $element;

					unset( $element );
				}

				unset( $billType, $elementTotalRates );
			}
		}

		$defaultLastRow = array(
			'id'                         => Constants::GRID_LAST_ROW,
			'description'                => '',
			'has_note'                   => false,
			'grand_total'                => 0,
			'original_grand_total'       => 0,
			'overall_total_after_markup' => 0,
			'element_sum_total'          => 0,
		);

		array_push($records, $defaultLastRow);

		return $this->renderJson(array(
			'identifier' => 'id',
			'items'      => $records
		));
	}

	public function executeGetPrintingSelectedElements(sfWebRequest $request)
	{
		$this->forward404Unless(
			$request->isXmlHttpRequest() AND
			$request->hasParameter('element_ids') AND
			$billColumnSetting = Doctrine_Core::getTable('BillColumnSetting')->find($request->getPostParameter('bill_type_id')) AND
			$subPackage = Doctrine_Core::getTable('SubPackage')->find($request->getPostParameter('subPackageId')) AND
			$bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getPostParameter('bill_id'))
		);

		$pdo           = $subPackage->getTable()->getConnection()->getDbh();
		$elementIds    = json_decode($request->getPostParameter('element_ids'), true);
		$records       = array();
		$filterBy      = $request->getPostParameter('opt');
		$filterByQuery = null;

		if ( !empty( $elementIds ) )
		{
			// if current bill types is standard but provisional then list all the items associated with it
			if ( $bill->BillType->type == BillType::TYPE_PROVISIONAL )
			{
				$filterBy = PostContractBillItemRate::REMEASUREMENT_FILTER_BY_ALL_ITEMS;
			}

			if ( $filterBy != PostContractBillItemRate::REMEASUREMENT_FILTER_BY_ALL_ITEMS )
			{
				$filterByQuery = "AND i.type = " . BillItem::TYPE_ITEM_PROVISIONAL;
			}

			$stmt = $pdo->prepare("SELECT DISTINCT e.id, e.description, e.priority
			FROM " . SubPackagePostContractBillItemRateTable::getInstance()->getTableName() . " rate
			JOIN " . BillItemTable::getInstance()->getTableName() . " i ON rate.bill_item_id  = i.id AND i.deleted_at IS NULL
			JOIN " . BillElementTable::getInstance()->getTableName() . " e ON i.element_id =  e.id AND e.deleted_at IS NULL
			JOIN " . ProjectStructureTable::getInstance()->getTableName() . " s ON e.project_structure_id = s.id AND s.deleted_at IS NULL
			JOIN " . BillColumnSettingTable::getInstance()->getTableName() . " bcs ON bcs.project_structure_id = s.id AND bcs.deleted_at IS NULL
			JOIN " . BillTypeTable::getInstance()->getTableName() . " t ON t.project_structure_id = s.id
			JOIN " . BillLayoutSettingTable::getInstance()->getTableName() . " bls ON bls.bill_id = s.id
			WHERE s.id = " . $bill->id . " AND e.project_structure_id = " . $bill->id . " AND bcs.id = " . $billColumnSetting->id . "
			AND e.id IN (" . implode(',', $elementIds) . ") AND rate.sub_package_id = " . $subPackage->id . " {$filterByQuery}
			ORDER BY e.priority ASC");

			$stmt->execute();
			$elements = $stmt->fetchAll(PDO::FETCH_ASSOC);

			// get element remeasurement's claim costing
			$elementTotalRates = SubPackagePostContractBillItemRateTable::getPostContractSubPackageRemeasurementTotalItemRateGroupByElement($bill, $subPackage, $billColumnSetting, $filterBy);

			foreach ( $elements as $element )
			{
				$omission = 0;
				$addition = 0;

				if ( array_key_exists($element['id'], $elementTotalRates) )
				{
					$omission = $elementTotalRates[$element['id']][0]['omission'];
					$addition = $elementTotalRates[$element['id']][0]['addition'];
				}

				$element['name']                 = $element['description'];
				$element['omission']             = $omission;
				$element['addition']             = $addition;
				$element['nettAdditionOmission'] = $element['addition'] - $element['omission'];

				$records[] = $element;

				unset( $element );
			}

			unset( $elementTotalRates );
		}

		$defaultLastRow = array(
			'id'                         => Constants::GRID_LAST_ROW,
			'description'                => '',
			'has_note'                   => false,
			'grand_total'                => 0,
			'original_grand_total'       => 0,
			'overall_total_after_markup' => 0,
			'element_sum_total'          => 0,
		);

		array_push($records, $defaultLastRow);

		return $this->renderJson(array(
			'identifier' => 'id',
			'items'      => $records
		));
	}

	public function executeGetPrintingElementsWithAddition(sfWebRequest $request)
	{
		$this->forward404Unless(
			$request->isXmlHttpRequest() AND
			$billColumnSetting = Doctrine_Core::getTable('BillColumnSetting')->find($request->getPostParameter('bill_type_id')) AND
			$subPackage = Doctrine_Core::getTable('SubPackage')->find($request->getPostParameter('subPackageId')) AND
			$bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getPostParameter('bill_id'))
		);

		$pdo           = $subPackage->getTable()->getConnection()->getDbh();
		$filterBy      = $request->getPostParameter('opt');
		$records       = array();
		$filterByQuery = null;

		// if current bill types is standard but provisional then list all the items associated with it
		if ( $bill->BillType->type == BillType::TYPE_PROVISIONAL )
		{
			$filterBy = PostContractBillItemRate::REMEASUREMENT_FILTER_BY_ALL_ITEMS;
		}

		if ( $filterBy != PostContractBillItemRate::REMEASUREMENT_FILTER_BY_ALL_ITEMS )
		{
			$filterByQuery = "AND i.type = " . BillItem::TYPE_ITEM_PROVISIONAL;
		}

		$stmt = $pdo->prepare("SELECT DISTINCT e.id, e.description, e.priority
		FROM " . SubPackagePostContractBillItemRateTable::getInstance()->getTableName() . " rate
		JOIN " . BillItemTable::getInstance()->getTableName() . " i ON rate.bill_item_id  = i.id AND i.deleted_at IS NULL
		JOIN " . BillElementTable::getInstance()->getTableName() . " e ON i.element_id =  e.id AND e.deleted_at IS NULL
		JOIN " . ProjectStructureTable::getInstance()->getTableName() . " s ON e.project_structure_id = s.id AND s.deleted_at IS NULL
		JOIN " . BillColumnSettingTable::getInstance()->getTableName() . " bcs ON bcs.project_structure_id = s.id AND bcs.deleted_at IS NULL
		JOIN " . BillTypeTable::getInstance()->getTableName() . " t ON t.project_structure_id = s.id
		JOIN " . BillLayoutSettingTable::getInstance()->getTableName() . " bls ON bls.bill_id = s.id
		WHERE s.id = " . $bill->id . " AND e.project_structure_id = " . $bill->id . " AND bcs.id = " . $billColumnSetting->id . "
		AND rate.sub_package_id = " . $subPackage->id . " {$filterByQuery}
		ORDER BY e.priority ASC");

		$stmt->execute();
		$elements = $stmt->fetchAll(PDO::FETCH_ASSOC);

		// get element remeasurement's claim costing
		$elementTotalRates = SubPackagePostContractBillItemRateTable::getPostContractSubPackageRemeasurementTotalItemRateGroupByElement($bill, $subPackage, $billColumnSetting, $filterBy);

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
				$element['name']                 = $element['description'];
				$element['omission']             = $omission;
				$element['addition']             = $addition;
				$element['nettAdditionOmission'] = $element['addition'] - $element['omission'];

				$records[] = $element;
			}

			unset( $element );
		}

		unset( $elementTotalRates );

		$defaultLastRow = array(
			'id'                         => Constants::GRID_LAST_ROW,
			'description'                => '',
			'has_note'                   => false,
			'grand_total'                => 0,
			'original_grand_total'       => 0,
			'overall_total_after_markup' => 0,
			'element_sum_total'          => 0,
		);

		array_push($records, $defaultLastRow);

		return $this->renderJson(array(
			'identifier' => 'id',
			'items'      => $records
		));
	}

	public function executeGetPrintingSelectedItems(sfWebRequest $request)
	{
		$this->forward404Unless(
			$request->isXmlHttpRequest() AND
			$request->hasParameter('item_ids') AND
			$billColumnSetting = Doctrine_Core::getTable('BillColumnSetting')->find($request->getPostParameter('bill_type_id')) AND
			$subPackage = Doctrine_Core::getTable('SubPackage')->find($request->getPostParameter('subPackageId')) AND
			$bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getPostParameter('bill_id'))
		);

		$pdo           = $subPackage->getTable()->getConnection()->getDbh();
		$itemIds       = json_decode($request->getPostParameter('item_ids'), true);
		$filterBy      = $request->getPostParameter('opt');
		$pageNoPrefix  = $bill->BillLayoutSetting->page_no_prefix;
		$records       = array();
		$filterByQuery = null;

		if ( !empty( $itemIds ) )
		{
			// if current bill types is standard but provisional then list all the items associated with it
			if ( $bill->BillType->type == BillType::TYPE_PROVISIONAL )
			{
				$filterBy = PostContractBillItemRate::REMEASUREMENT_FILTER_BY_ALL_ITEMS;
			}

			if ( $filterBy != PostContractBillItemRate::REMEASUREMENT_FILTER_BY_ALL_ITEMS )
			{
				$filterByQuery = "AND i.type = " . BillItem::TYPE_ITEM_PROVISIONAL;
			}

			list(
				$billItems, $remeasurementClaims, $billItemTypeReferences, $billItemTypeRefFormulatedColumns
				) = SubPackagePostContractBillItemRateTable::getRemeasurementItemListByItemIds($subPackage, $bill, $billColumnSetting, $itemIds, $filterBy);

			$stmt = $pdo->prepare("SELECT DISTINCT e.id, e.description, e.priority
			FROM " . SubPackagePostContractBillItemRateTable::getInstance()->getTableName() . " rate
			JOIN " . BillItemTable::getInstance()->getTableName() . " i ON rate.bill_item_id  = i.id AND i.deleted_at IS NULL
			JOIN " . BillElementTable::getInstance()->getTableName() . " e ON i.element_id =  e.id AND e.deleted_at IS NULL
			JOIN " . ProjectStructureTable::getInstance()->getTableName() . " s ON e.project_structure_id = s.id AND s.deleted_at IS NULL
			JOIN " . BillColumnSettingTable::getInstance()->getTableName() . " bcs ON bcs.project_structure_id = s.id AND bcs.deleted_at IS NULL
			JOIN " . BillTypeTable::getInstance()->getTableName() . " t ON t.project_structure_id = s.id
			JOIN " . BillLayoutSettingTable::getInstance()->getTableName() . " bls ON bls.bill_id = s.id
			WHERE s.id = " . $bill->id . " AND e.project_structure_id = " . $bill->id . " AND bcs.id = " . $billColumnSetting->id . "
			AND rate.sub_package_id = " . $subPackage->id . " {$filterByQuery}
			ORDER BY e.priority ASC");

			$stmt->execute();
			$elements = $stmt->fetchAll(PDO::FETCH_ASSOC);

			foreach ( $elements as $element )
			{
				$elementHeaderRow = array(
					'id'                       => "element_id-{$element['id']}",
					'bill_ref'                 => '',
					'description'              => $element['description'],
					'note'                     => '',
					'has_note'                 => false,
					'type'                     => (string) 0,
					'uom_id'                   => '-1',
					'uom_symbol'               => '',
					'rate'                     => 0,
					'omission-qty_per_unit'    => 0,
					'omission-total_per_unit'  => 0,
					'addition-qty_per_unit'    => 0,
					'addition-total_per_unit'  => 0,
					'nett_addition_omission'   => 0,
					'level'                    => 0,
					'linked'                   => false,
					'rate_after_markup'        => 0,
					'grand_total_after_markup' => 0,
				);

				$records[] = $elementHeaderRow;

				unset( $elementHeaderRow );

				foreach ( $billItems as $key => $billItem )
				{
					if ( $billItem['element_id'] != $element['id'] )
					{
						continue;
					}

					$itemTotal                           = $billItem['rate'] * $billItem['qty_per_unit'];
					$billItem['bill_ref']                = BillItemTable::generateBillRef($pageNoPrefix, $billItem['bill_ref_element_no'], $billItem['bill_ref_page_no'], $billItem['bill_ref_char']);
					$billItem['type']                    = (string) $billItem['type'];
					$billItem['uom_id']                  = $billItem['uom_id'] > 0 ? (string) $billItem['uom_id'] : '-1';
					$billItem['linked']                  = false;
					$billItem['has_note']                = ( $billItem['note'] != null && $billItem['note'] != '' ) ? true : false;
					$billItem['item_total']              = Utilities::prelimRounding($itemTotal);
					$billItem['omission-qty_per_unit']   = Utilities::prelimRounding($billItem['qty_per_unit']);
					$billItem['omission-total_per_unit'] = Utilities::prelimRounding($billItem['rate'] * $billItem['qty_per_unit']);
					$billItem['omission-has_build_up']   = false;
					$billItem['addition-qty_per_unit']   = 0;
					$billItem['addition-total_per_unit'] = 0;
					$billItem['addition-has_build_up']   = false;

					if ( array_key_exists($billItem['sub_package_post_contract_bill_item_rate_id'], $remeasurementClaims) )
					{
						$costing = $remeasurementClaims[$billItem['sub_package_post_contract_bill_item_rate_id']];

						$billItem['addition-qty_per_unit']   = Utilities::prelimRounding($costing['qty_per_unit']);
						$billItem['addition-total_per_unit'] = Utilities::prelimRounding($costing['total_per_unit']);
						$billItem['addition-has_build_up']   = $costing['has_build_up'];

						unset( $costing );
					}

					$billItem['nett_addition_omission'] = Utilities::prelimRounding($billItem['addition-total_per_unit'] - $billItem['omission-total_per_unit']);

					if ( array_key_exists($billColumnSetting->id, $billItemTypeReferences) && array_key_exists($billItem['id'], $billItemTypeReferences[$billColumnSetting->id]) )
					{
						$billItemTypeRef = $billItemTypeReferences[$billColumnSetting->id][$billItem['id']];

						unset( $billItemTypeReferences[$billColumnSetting->id][$billItem['id']] );

						if ( array_key_exists($billItemTypeRef['id'], $billItemTypeRefFormulatedColumns) )
						{
							foreach ( $billItemTypeRefFormulatedColumns[$billItemTypeRef['id']] as $billItemTypeRefFormulatedColumn )
							{
								$billItem['omission-has_build_up'] = $billItemTypeRefFormulatedColumn['has_build_up'];

								unset( $billItemTypeRefFormulatedColumn );
							}
						}
					}

					array_push($records, $billItem);
					unset( $billItem, $billItems[$key] );
				}
			}

			unset( $elements );
		}

		$defaultLastRow = array(
			'id'                       => Constants::GRID_LAST_ROW,
			'bill_ref'                 => '',
			'description'              => '',
			'note'                     => '',
			'has_note'                 => false,
			'type'                     => (string) ProjectStructure::getDefaultItemType($bill->BillType->type),
			'uom_id'                   => '-1',
			'uom_symbol'               => '',
			'rate'                     => 0,
			'omission-qty_per_unit'    => 0,
			'omission-total_per_unit'  => 0,
			'addition-qty_per_unit'    => 0,
			'addition-total_per_unit'  => 0,
			'nett_addition_omission'   => 0,
			'level'                    => 0,
			'linked'                   => false,
			'rate_after_markup'        => 0,
			'grand_total_after_markup' => 0,
		);

		array_push($records, $defaultLastRow);

		return $this->renderJson(array(
			'identifier' => 'id',
			'items'      => $records
		));
	}

	public function executeGetPrintingItemsWithAdditionOnly(sfWebRequest $request)
	{
		$this->forward404Unless(
			$request->isXmlHttpRequest() AND
			$billColumnSetting = Doctrine_Core::getTable('BillColumnSetting')->find($request->getPostParameter('bill_type_id')) AND
			$subPackage = Doctrine_Core::getTable('SubPackage')->find($request->getPostParameter('subPackageId')) AND
			$bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getPostParameter('bill_id'))
		);

		$pdo          = $subPackage->getTable()->getConnection()->getDbh();
		$pageNoPrefix = $bill->BillLayoutSetting->page_no_prefix;
		$records      = array();
		$filterBy     = $request->getPostParameter('opt');

		// if current bill types is standard but provisional then list all the items associated with it
		if ( $bill->BillType->type == BillType::TYPE_PROVISIONAL )
		{
			$filterBy = PostContractBillItemRate::REMEASUREMENT_FILTER_BY_ALL_ITEMS;
		}

		list(
			$elementIds, $billItems, $remeasurementClaims, $billItemTypeReferences, $billItemTypeRefFormulatedColumns
			) = SubPackagePostContractBillItemRateTable::getRemeasurementItemListWithAdditionOnly($subPackage, $bill, $billColumnSetting, $filterBy);

		if ( !empty( $elementIds ) )
		{
			$stmt = $pdo->prepare("SELECT e.id, e.description FROM " . BillElementTable::getInstance()->getTableName() . " e
			WHERE e.id IN (" . implode(',', $elementIds) . ") AND e.deleted_at IS NULL
			ORDER BY e.priority ASC");

			$stmt->execute();
			$elements = $stmt->fetchAll(PDO::FETCH_ASSOC);

			foreach ( $elements as $element )
			{
				$elementHeaderRow = array(
					'id'                       => "element_id-{$element['id']}",
					'bill_ref'                 => '',
					'description'              => $element['description'],
					'note'                     => '',
					'has_note'                 => false,
					'type'                     => (string) 0,
					'uom_id'                   => '-1',
					'uom_symbol'               => '',
					'rate'                     => 0,
					'omission-qty_per_unit'    => 0,
					'omission-total_per_unit'  => 0,
					'addition-qty_per_unit'    => 0,
					'addition-total_per_unit'  => 0,
					'nett_addition_omission'   => 0,
					'level'                    => 0,
					'linked'                   => false,
					'rate_after_markup'        => 0,
					'grand_total_after_markup' => 0,
				);

				$records[] = $elementHeaderRow;

				unset( $elementHeaderRow );

				foreach ( $billItems as $key => $billItem )
				{
					if ( $billItem['element_id'] != $element['id'] )
					{
						continue;
					}

					$itemTotal                           = $billItem['rate'] * $billItem['qty_per_unit'];
					$billItem['bill_ref']                = BillItemTable::generateBillRef($pageNoPrefix, $billItem['bill_ref_element_no'], $billItem['bill_ref_page_no'], $billItem['bill_ref_char']);
					$billItem['type']                    = (string) $billItem['type'];
					$billItem['uom_id']                  = $billItem['uom_id'] > 0 ? (string) $billItem['uom_id'] : '-1';
					$billItem['linked']                  = false;
					$billItem['has_note']                = ( $billItem['note'] != null && $billItem['note'] != '' ) ? true : false;
					$billItem['item_total']              = Utilities::prelimRounding($itemTotal);
					$billItem['omission-qty_per_unit']   = Utilities::prelimRounding($billItem['qty_per_unit']);
					$billItem['omission-total_per_unit'] = Utilities::prelimRounding($billItem['rate'] * $billItem['qty_per_unit']);
					$billItem['omission-has_build_up']   = false;
					$billItem['addition-qty_per_unit']   = 0;
					$billItem['addition-total_per_unit'] = 0;
					$billItem['addition-has_build_up']   = false;

					if ( array_key_exists($billItem['sub_package_post_contract_bill_item_rate_id'], $remeasurementClaims) )
					{
						$costing = $remeasurementClaims[$billItem['sub_package_post_contract_bill_item_rate_id']];

						$billItem['addition-qty_per_unit']   = Utilities::prelimRounding($costing['qty_per_unit']);
						$billItem['addition-total_per_unit'] = Utilities::prelimRounding($costing['total_per_unit']);
						$billItem['addition-has_build_up']   = $costing['has_build_up'];

						unset( $costing );
					}

					$billItem['nett_addition_omission'] = Utilities::prelimRounding($billItem['addition-total_per_unit'] - $billItem['omission-total_per_unit']);

					if ( array_key_exists($billColumnSetting->id, $billItemTypeReferences) && array_key_exists($billItem['id'], $billItemTypeReferences[$billColumnSetting->id]) )
					{
						$billItemTypeRef = $billItemTypeReferences[$billColumnSetting->id][$billItem['id']];

						unset( $billItemTypeReferences[$billColumnSetting->id][$billItem['id']] );

						if ( array_key_exists($billItemTypeRef['id'], $billItemTypeRefFormulatedColumns) )
						{
							foreach ( $billItemTypeRefFormulatedColumns[$billItemTypeRef['id']] as $billItemTypeRefFormulatedColumn )
							{
								$billItem['omission-has_build_up'] = $billItemTypeRefFormulatedColumn['has_build_up'];

								unset( $billItemTypeRefFormulatedColumn );
							}
						}
					}

					array_push($records, $billItem);
					unset( $billItem, $billItems[$key] );
				}
			}

			unset( $elements );
		}

		$defaultLastRow = array(
			'id'                       => Constants::GRID_LAST_ROW,
			'bill_ref'                 => '',
			'description'              => '',
			'note'                     => '',
			'has_note'                 => false,
			'type'                     => (string) ProjectStructure::getDefaultItemType($bill->BillType->type),
			'uom_id'                   => '-1',
			'uom_symbol'               => '',
			'rate'                     => 0,
			'omission-qty_per_unit'    => 0,
			'omission-total_per_unit'  => 0,
			'addition-qty_per_unit'    => 0,
			'addition-total_per_unit'  => 0,
			'nett_addition_omission'   => 0,
			'level'                    => 0,
			'linked'                   => false,
			'rate_after_markup'        => 0,
			'grand_total_after_markup' => 0,
		);

		array_push($records, $defaultLastRow);

		return $this->renderJson(array(
			'identifier' => 'id',
			'items'      => $records
		));
	}

	public function executeGetPrintingSelectedItemsWithBuildUpQty(sfWebRequest $request)
	{
		$this->forward404Unless(
			$request->isXmlHttpRequest() AND
			$request->hasParameter('item_ids') AND
			$billColumnSetting = Doctrine_Core::getTable('BillColumnSetting')->find($request->getPostParameter('bill_type_id')) AND
			$subPackage = Doctrine_Core::getTable('SubPackage')->find($request->getPostParameter('subPackageId')) AND
			$bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getPostParameter('bill_id'))
		);

		$filterBy     = $request->getPostParameter('opt');
		$itemIds      = json_decode($request->getPostParameter('item_ids'), true);
		$pdo          = $subPackage->getTable()->getConnection()->getDbh();
		$pageNoPrefix = $bill->BillLayoutSetting->page_no_prefix;
		$records      = array();

		if ( !empty( $itemIds ) )
		{
			// if current bill types is standard but provisional then list all the items associated with it
			if ( $bill->BillType->type == BillType::TYPE_PROVISIONAL )
			{
				$filterBy = PostContractBillItemRate::REMEASUREMENT_FILTER_BY_ALL_ITEMS;
			}

			list(
				$elementIds, $billItems, $remeasurementClaims,
				$billItemTypeReferences, $billItemTypeRefFormulatedColumns
				) = SubPackagePostContractBillItemRateTable::getRemeasurementItemListWithBuildUpQtyOnly($subPackage, $bill, $billColumnSetting, $itemIds, $filterBy);

			if ( count($elementIds) > 0 )
			{
				$stmt = $pdo->prepare("SELECT e.id, e.description FROM " . BillElementTable::getInstance()->getTableName() . " e
				WHERE e.id IN (" . implode(',', $elementIds) . ") AND e.deleted_at IS NULL
				ORDER BY e.priority ASC");

				$stmt->execute();
				$elements = $stmt->fetchAll(PDO::FETCH_ASSOC);

				foreach ( $elements as $element )
				{
					$elementHeaderRow = array(
						'id'                       => "element_id-{$element['id']}",
						'bill_ref'                 => '',
						'description'              => $element['description'],
						'note'                     => '',
						'has_note'                 => false,
						'type'                     => (string) 0,
						'uom_id'                   => '-1',
						'uom_symbol'               => '',
						'rate'                     => 0,
						'omission-qty_per_unit'    => 0,
						'omission-total_per_unit'  => 0,
						'addition-qty_per_unit'    => 0,
						'addition-total_per_unit'  => 0,
						'nett_addition_omission'   => 0,
						'level'                    => 0,
						'linked'                   => false,
						'rate_after_markup'        => 0,
						'grand_total_after_markup' => 0,
					);

					$records[] = $elementHeaderRow;

					unset( $elementHeaderRow );

					foreach ( $billItems as $key => $billItem )
					{
						if ( $billItem['element_id'] != $element['id'] )
						{
							continue;
						}

						$itemTotal                           = $billItem['rate'] * $billItem['qty_per_unit'];
						$billItem['bill_ref']                = BillItemTable::generateBillRef($pageNoPrefix, $billItem['bill_ref_element_no'], $billItem['bill_ref_page_no'], $billItem['bill_ref_char']);
						$billItem['type']                    = (string) $billItem['type'];
						$billItem['uom_id']                  = $billItem['uom_id'] > 0 ? (string) $billItem['uom_id'] : '-1';
						$billItem['linked']                  = false;
						$billItem['has_note']                = ( $billItem['note'] != null && $billItem['note'] != '' ) ? true : false;
						$billItem['item_total']              = Utilities::prelimRounding($itemTotal);
						$billItem['omission-qty_per_unit']   = Utilities::prelimRounding($billItem['qty_per_unit']);
						$billItem['omission-total_per_unit'] = Utilities::prelimRounding($billItem['rate'] * $billItem['qty_per_unit']);
						$billItem['omission-has_build_up']   = false;
						$billItem['addition-qty_per_unit']   = 0;
						$billItem['addition-total_per_unit'] = 0;
						$billItem['addition-has_build_up']   = false;

						if ( array_key_exists($billColumnSetting->id, $billItemTypeReferences) && array_key_exists($billItem['id'], $billItemTypeReferences[$billColumnSetting->id]) )
						{
							$billItemTypeRef = $billItemTypeReferences[$billColumnSetting->id][$billItem['id']];

							unset( $billItemTypeReferences[$billColumnSetting->id][$billItem['id']] );

							if ( array_key_exists($billItemTypeRef['id'], $billItemTypeRefFormulatedColumns) )
							{
								foreach ( $billItemTypeRefFormulatedColumns[$billItemTypeRef['id']] as $billItemTypeRefFormulatedColumn )
								{
									$billItem['omission-has_build_up'] = $billItemTypeRefFormulatedColumn['has_build_up'];

									unset( $billItemTypeRefFormulatedColumn );
								}
							}
						}

						if ( array_key_exists($billItem['sub_package_post_contract_bill_item_rate_id'], $remeasurementClaims) )
						{
							$costing = $remeasurementClaims[$billItem['sub_package_post_contract_bill_item_rate_id']];

							$billItem['addition-qty_per_unit']   = Utilities::prelimRounding($costing['qty_per_unit']);
							$billItem['addition-total_per_unit'] = Utilities::prelimRounding($costing['total_per_unit']);
							$billItem['addition-has_build_up']   = $costing['has_build_up'];

							unset( $costing );
						}

						$billItem['nett_addition_omission'] = Utilities::prelimRounding($billItem['addition-total_per_unit'] - $billItem['omission-total_per_unit']);

						array_push($records, $billItem);
						unset( $billItem, $billItems[$key] );
					}
				}

				unset( $elements );
			}
		}

		$defaultLastRow = array(
			'id'                       => Constants::GRID_LAST_ROW,
			'bill_ref'                 => '',
			'description'              => '',
			'note'                     => '',
			'has_note'                 => false,
			'type'                     => (string) ProjectStructure::getDefaultItemType($bill->BillType->type),
			'uom_id'                   => '-1',
			'uom_symbol'               => '',
			'rate'                     => 0,
			'omission-qty_per_unit'    => 0,
			'omission-total_per_unit'  => 0,
			'addition-qty_per_unit'    => 0,
			'addition-total_per_unit'  => 0,
			'nett_addition_omission'   => 0,
			'level'                    => 0,
			'linked'                   => false,
			'rate_after_markup'        => 0,
			'grand_total_after_markup' => 0,
		);

		array_push($records, $defaultLastRow);

		return $this->renderJson(array(
			'identifier' => 'id',
			'items'      => $records
		));
	}

	// ======================================================================================================================================
	// Generate Printout Report
	// ======================================================================================================================================
	public function executePrintSelectedTypes(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless(
			$request->isMethod('POST') AND
			$subPackage = Doctrine_Core::getTable('SubPackage')->find($request->getPostParameter('subPackageId')) AND
			$bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getPostParameter('billId'))
		);

		session_write_close();

		$typeIds  = json_decode($request->getPostParameter('selectedRows'), true);
		$filterBy = $request->getPostParameter('opt');
		$records  = array();

		$pdo               = $subPackage->getTable()->getConnection()->getDbh();
		$postContract      = $subPackage->ProjectStructure->PostContract;
		$printingPageTitle = $request->getPostParameter('printingPageTitle');
		$descriptionFormat = $request->getPostParameter('descriptionFormat');
		$priceFormat       = $request->getPostParameter('priceFormat');
		$printNoCents      = ( $request->getPostParameter('printNoCents') == 't' ) ? true : false;
		$filterByQuery     = null;

		if ( !empty( $typeIds ) )
		{
			// if current bill types is standard but provisional then list all the items associated with it
			if ( $bill->BillType->type == BillType::TYPE_PROVISIONAL )
			{
				$filterBy = PostContractBillItemRate::REMEASUREMENT_FILTER_BY_ALL_ITEMS;
			}

			if ( $filterBy != PostContractBillItemRate::REMEASUREMENT_FILTER_BY_ALL_ITEMS )
			{
				$filterByQuery = "AND i.type = " . BillItem::TYPE_ITEM_PROVISIONAL;
			}

			$stmt = $pdo->prepare("SELECT DISTINCT bcs.id, bcs.name, bcs.quantity
			FROM " . SubPackagePostContractBillItemRateTable::getInstance()->getTableName() . " rate
			JOIN " . BillItemTable::getInstance()->getTableName() . " i ON rate.bill_item_id  = i.id AND i.deleted_at IS NULL
			JOIN " . BillElementTable::getInstance()->getTableName() . " e ON i.element_id =  e.id AND e.deleted_at IS NULL
			JOIN " . ProjectStructureTable::getInstance()->getTableName() . " s ON e.project_structure_id = s.id AND s.deleted_at IS NULL
			JOIN " . BillColumnSettingTable::getInstance()->getTableName() . " bcs ON bcs.project_structure_id = s.id AND bcs.deleted_at IS NULL
			JOIN " . BillTypeTable::getInstance()->getTableName() . " t ON t.project_structure_id = s.id
			JOIN " . BillLayoutSettingTable::getInstance()->getTableName() . " bls ON bls.bill_id = s.id
			WHERE s.id = " . $bill->id . " AND e.project_structure_id = " . $bill->id . "
			AND bcs.id IN (" . implode(',', $typeIds) . ") AND rate.sub_package_id = " . $subPackage->id . " {$filterByQuery}
			ORDER BY bcs.id ASC");

			$stmt->execute();
			$records = $stmt->fetchAll(PDO::FETCH_ASSOC);

			foreach ( $records as $key => $billType )
			{
				$omission = 0;
				$addition = 0;

				$billColumnSetting     = new BillColumnSetting();
				$billColumnSetting->id = $billType['id'];

				$remeasurementClaims = SubPackagePostContractBillItemRateTable::getPostContractSubPackageRemeasurementTotalItemRateGroupByElement($bill, $subPackage, $billColumnSetting, $filterBy);

				foreach ( $remeasurementClaims as $remeasurementClaim )
				{
					$omission += $remeasurementClaim[0]['omission'];
					$addition += $remeasurementClaim[0]['addition'];
				}

				$records[$key]['omission']             = $omission * $billType['quantity'];
				$records[$key]['addition']             = $addition * $billType['quantity'];
				$records[$key]['nettAdditionOmission'] = $records[$key]['addition'] - $records[$key]['omission'];

				unset( $billColumnSetting, $billType );
			}
		}

		$reportGenerator = new sfRemeasurementTypeReportGenerator($postContract, $bill, $records, $descriptionFormat);
		$pages           = $reportGenerator->generatePages();
		$maxRows         = $reportGenerator->getMaxRows();
		$currency        = $reportGenerator->getCurrency();
		$withoutPrice    = false;

		$params = array(
			'disable-smart-shrinking',
			'disable-javascript',
			'no-outline',
			'no-background',
			'enableEscaping' => true,
			'binPath'        => sfConfig::get('app_wkhtmltopdf_bin_path'),
			'margin-top'     => $reportGenerator->getMarginTop(),
			'margin-right'   => $reportGenerator->getMarginRight(),
			'margin-bottom'  => $reportGenerator->getMarginBottom(),
			'margin-left'    => $reportGenerator->getMarginLeft(),
			'page-size'      => $reportGenerator->getPageSize(),
			'orientation'    => $reportGenerator->getOrientation()
		);

		$stylesheet = $this->getBQStyling();
		$pdfGen     = new WkHtmlToPdf($params);
		$pageCount  = 1;

		if ( $pages instanceof SplFixedArray )
		{
			foreach ( $pages as $page )
			{
				if ( empty( $page ) )
				{
					continue;
				}

				$lastPage = ( $pageCount == $pages->count() - 1 ) ? true : false;

				$layout = $this->getPartial('printReport/pageLayout', array(
					'stylesheet'    => $stylesheet,
					'layoutStyling' => $reportGenerator->getLayoutStyling()
				));

				$layoutParams = array(
					'itemPage'                   => $page,
					'maxRows'                    => $maxRows - 3,
					'lastPage'                   => $lastPage,
					'currency'                   => $currency,
					'totalOmission'              => $reportGenerator->totalOmission,
					'totalAddition'              => $reportGenerator->totalAddition,
					'elementHeaderDescription'   => null,
					'elementCount'               => null,
					'printQty'                   => true,
					'pageCount'                  => $pageCount,
					'reportTitle'                => $printingPageTitle,
					'topLeftRow1'                => $postContract->ProjectStructure->MainInformation->title,
					'topLeftRow2'                => $bill->title,
					'botLeftRow1'                => $reportGenerator->getBottomLeftFirstRowHeader(),
					'botLeftRow2'                => $reportGenerator->getBottomLeftSecondRowHeader(),
					'descHeader'                 => $reportGenerator->getTableHeaderDescriptionPrefix(),
					'unitHeader'                 => $reportGenerator->getTableHeaderUnitPrefix(),
					'rateHeader'                 => $reportGenerator->getTableHeaderRatePrefix(),
					'qtyHeader'                  => $reportGenerator->getTableHeaderQtyPrefix(),
					'amtHeader'                  => $reportGenerator->getTableHeaderAmtPrefix(),
					'toCollection'               => $reportGenerator->getToCollectionPrefix(),
					'priceFormatting'            => $reportGenerator->generatePriceFormat($priceFormat, $printNoCents),
					'printNoPrice'               => $withoutPrice,
					'toggleColumnArrangement'    => $reportGenerator->getToggleColumnArrangement(),
					'printElementTitle'          => $reportGenerator->getPrintElementTitle(),
					'printDollarAndCentColumn'   => $reportGenerator->getPrintDollarAndCentColumn(),
					'currencyFormat'             => $reportGenerator->getCurrencyFormat(),
					'rateCommaRemove'            => $reportGenerator->getRateCommaRemove(),
					'qtyCommaRemove'             => $reportGenerator->getQtyCommaRemove(),
					'amtCommaRemove'             => $reportGenerator->getAmtCommaRemove(),
					'printAmountOnly'            => $reportGenerator->getPrintAmountOnly(),
					'printElementInGridOnce'     => $reportGenerator->getPrintElementInGridOnce(),
					'indentItem'                 => $reportGenerator->getIndentItem(),
					'printElementInGrid'         => $reportGenerator->getPrintElementInGrid(),
					'pageNoPrefix'               => $reportGenerator->getPageNoPrefix(),
					'printDateOfPrinting'        => $reportGenerator->getPrintDateOfPrinting(),
					'alignElementTitleToTheLeft' => $reportGenerator->getAlignElementToLeft(),
				);

				$layout .= $this->getPartial('postContractRemeasurementReport/reportRemeasurementByTypes', $layoutParams);

				unset( $page );

				$pdfGen->addPage($layout);

				$pageCount ++;
			}
		}

		unset( $records );

		return $pdfGen->send();
	}

	public function executePrintingSelectedElementByTypes(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless(
			$request->isMethod('POST') AND
			$subPackage = Doctrine_Core::getTable('SubPackage')->find($request->getPostParameter('subPackageId')) AND
			$bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getPostParameter('billId'))
		);

		session_write_close();

		$typeIds  = json_decode($request->getPostParameter('selectedRows'), true);
		$filterBy = $request->getPostParameter('opt');
		$types    = array();
		$records  = array();

		$pdo               = $subPackage->getTable()->getConnection()->getDbh();
		$postContract      = $subPackage->ProjectStructure->PostContract;
		$printingPageTitle = $request->getPostParameter('printingPageTitle');
		$descriptionFormat = $request->getPostParameter('descriptionFormat');
		$priceFormat       = $request->getPostParameter('priceFormat');
		$printNoCents      = ( $request->getPostParameter('printNoCents') == 't' ) ? true : false;
		$filterByQuery     = null;

		if ( !empty( $typeIds ) )
		{
			$types = DoctrineQuery::create()
				->select('s.id, s.name, s.quantity')
				->from('BillColumnSetting s')
				->where('s.project_structure_id = ?', $bill->id)
				->andWhereIn('s.id', $typeIds)
				->addOrderBy('s.id ASC')
				->fetchArray();

			// if current bill types is standard but provisional then list all the items associated with it
			if ( $bill->BillType->type == BillType::TYPE_PROVISIONAL )
			{
				$filterBy = PostContractBillItemRate::REMEASUREMENT_FILTER_BY_ALL_ITEMS;
			}

			if ( $filterBy != PostContractBillItemRate::REMEASUREMENT_FILTER_BY_ALL_ITEMS )
			{
				$filterByQuery = "AND i.type = " . BillItem::TYPE_ITEM_PROVISIONAL;
			}

			$stmt = $pdo->prepare("SELECT DISTINCT e.id, e.description, e.priority
			FROM " . SubPackagePostContractBillItemRateTable::getInstance()->getTableName() . " rate
			JOIN " . BillItemTable::getInstance()->getTableName() . " i ON rate.bill_item_id  = i.id AND i.deleted_at IS NULL
			JOIN " . BillElementTable::getInstance()->getTableName() . " e ON i.element_id =  e.id AND e.deleted_at IS NULL
			JOIN " . ProjectStructureTable::getInstance()->getTableName() . " s ON e.project_structure_id = s.id AND s.deleted_at IS NULL
			JOIN " . BillColumnSettingTable::getInstance()->getTableName() . " bcs ON bcs.project_structure_id = s.id AND bcs.deleted_at IS NULL
			JOIN " . BillTypeTable::getInstance()->getTableName() . " t ON t.project_structure_id = s.id
			JOIN " . BillLayoutSettingTable::getInstance()->getTableName() . " bls ON bls.bill_id = s.id
			WHERE s.id = " . $bill->id . " AND e.project_structure_id = " . $bill->id . "
			AND bcs.id IN (" . implode(',', $typeIds) . ") AND rate.sub_package_id = " . $subPackage->id . " {$filterByQuery}
			ORDER BY e.priority ASC");

			$stmt->execute();
			$elements = $stmt->fetchAll(PDO::FETCH_ASSOC);

			$attachRowHeader = ( count($elements) > 0 ) ? true : false;

			foreach ( $types as $type )
			{
				$billType     = new BillColumnSetting();
				$billType->id = $type['id'];

				if ( $attachRowHeader )
				{
					$typeRow = array(
						'id'                         => "type-{$type['id']}",
						'name'                       => $type['name'],
						'type'                       => 0,
						'has_note'                   => false,
						'grand_total'                => 0,
						'original_grand_total'       => 0,
						'overall_total_after_markup' => 0,
						'element_sum_total'          => 0,
						'relation_id'                => $bill->id,
					);

					array_push($records, $typeRow);

					unset( $typeRow );
				}

				// get element remeasurement's claim costing
				$elementTotalRates = SubPackagePostContractBillItemRateTable::getPostContractSubPackageRemeasurementTotalItemRateGroupByElement($bill, $subPackage, $billType, $filterBy);

				foreach ( $elements as $element )
				{
					$omission = 0;
					$addition = 0;

					if ( array_key_exists($element['id'], $elementTotalRates) )
					{
						$omission = $elementTotalRates[$element['id']][0]['omission'];
						$addition = $elementTotalRates[$element['id']][0]['addition'];
					}

					$element['id']                   = "bill_type-{$type['id']}-{$element['id']}";
					$element['name']                 = $element['description'];
					$element['omission']             = $omission;
					$element['addition']             = $addition;
					$element['nettAdditionOmission'] = $element['addition'] - $element['omission'];

					$records[$type['id']][] = $element;

					unset( $element );
				}

				unset( $billType, $elementTotalRates );
			}
		}

		if ( empty( $types ) )
		{
			$this->message     = 'Error';
			$this->explanation = 'Nothing can be printed because there were no selected type(s) detected.';

			$this->setTemplate('nothingToPrint');

			return sfView::SUCCESS;
		}

		$reportGenerator = new sfRemeasurementTypesElementReportGenerator($postContract, $bill, $descriptionFormat);
		$currency        = $reportGenerator->getCurrency();
		$maxRows         = $reportGenerator->getMaxRows();
		$withoutPrice    = false;

		$params = array(
			'disable-smart-shrinking',
			'disable-javascript',
			'no-outline',
			'no-background',
			'enableEscaping' => true,
			'binPath'        => sfConfig::get('app_wkhtmltopdf_bin_path'),
			'margin-top'     => $reportGenerator->getMarginTop(),
			'margin-right'   => $reportGenerator->getMarginRight(),
			'margin-bottom'  => $reportGenerator->getMarginBottom(),
			'margin-left'    => $reportGenerator->getMarginLeft(),
			'page-size'      => $reportGenerator->getPageSize(),
			'orientation'    => $reportGenerator->getOrientation()
		);

		$stylesheet = $this->getBQStyling();
		$pdfGen     = new WkHtmlToPdf($params);
		$pageCount  = 1;

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

			$currentTypePageCount = 1;

			foreach ( $pages as $page )
			{
				if ( count($page) == 0 )
				{
					continue;
				}

				$lastPage = ( $currentTypePageCount == $pages->count() - 1 ) ? true : false;

				$layout = $this->getPartial('printReport/pageLayout', array(
					'stylesheet'    => $stylesheet,
					'layoutStyling' => $reportGenerator->getLayoutStyling()
				));

				$layoutParams = array(
					'itemPage'                   => $page,
					'maxRows'                    => $maxRows - 3,
					'lastPage'                   => $lastPage,
					'currency'                   => $currency,
					'totalOmission'              => $reportGenerator->totalOmission,
					'totalAddition'              => $reportGenerator->totalAddition,
					'elementHeaderDescription'   => null,
					'elementCount'               => null,
					'printQty'                   => true,
					'pageCount'                  => $pageCount,
					'reportTitle'                => $printingPageTitle,
					'topLeftRow1'                => $postContract->ProjectStructure->MainInformation->title,
					'topLeftRow2'                => "{$bill->title} > {$type['name']}",
					'botLeftRow1'                => $reportGenerator->getBottomLeftFirstRowHeader(),
					'botLeftRow2'                => $reportGenerator->getBottomLeftSecondRowHeader(),
					'descHeader'                 => $reportGenerator->getTableHeaderDescriptionPrefix(),
					'unitHeader'                 => $reportGenerator->getTableHeaderUnitPrefix(),
					'rateHeader'                 => $reportGenerator->getTableHeaderRatePrefix(),
					'qtyHeader'                  => $reportGenerator->getTableHeaderQtyPrefix(),
					'amtHeader'                  => $reportGenerator->getTableHeaderAmtPrefix(),
					'toCollection'               => $reportGenerator->getToCollectionPrefix(),
					'priceFormatting'            => $reportGenerator->generatePriceFormat($priceFormat, $printNoCents),
					'printNoPrice'               => $withoutPrice,
					'toggleColumnArrangement'    => $reportGenerator->getToggleColumnArrangement(),
					'printElementTitle'          => $reportGenerator->getPrintElementTitle(),
					'printDollarAndCentColumn'   => $reportGenerator->getPrintDollarAndCentColumn(),
					'currencyFormat'             => $reportGenerator->getCurrencyFormat(),
					'rateCommaRemove'            => $reportGenerator->getRateCommaRemove(),
					'qtyCommaRemove'             => $reportGenerator->getQtyCommaRemove(),
					'amtCommaRemove'             => $reportGenerator->getAmtCommaRemove(),
					'printAmountOnly'            => $reportGenerator->getPrintAmountOnly(),
					'printElementInGridOnce'     => $reportGenerator->getPrintElementInGridOnce(),
					'indentItem'                 => $reportGenerator->getIndentItem(),
					'printElementInGrid'         => $reportGenerator->getPrintElementInGrid(),
					'pageNoPrefix'               => $reportGenerator->getPageNoPrefix(),
					'printDateOfPrinting'        => $reportGenerator->getPrintDateOfPrinting(),
					'alignElementTitleToTheLeft' => $reportGenerator->getAlignElementToLeft(),
				);

				$layout .= $this->getPartial('postContractRemeasurementReport/reportRemeasurementByTypes', $layoutParams);

				unset( $page );

				$pdfGen->addPage($layout);

				$currentTypePageCount ++;
				$pageCount ++;
			}
		}

		return $pdfGen->send();
	}

	public function executePrintingSelectedElements(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless(
			$request->isMethod('POST') AND
			$billColumnSetting = Doctrine_Core::getTable('BillColumnSetting')->find($request->getPostParameter('bill_type_id')) AND
			$subPackage = Doctrine_Core::getTable('SubPackage')->find($request->getPostParameter('subPackageId')) AND
			$bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getPostParameter('billId'))
		);

		session_write_close();

		$elementIds = json_decode($request->getPostParameter('selectedRows'), true);
		$records    = array();
		$filterBy   = $request->getPostParameter('opt');

		$pdo               = $subPackage->getTable()->getConnection()->getDbh();
		$postContract      = $subPackage->ProjectStructure->PostContract;
		$printingPageTitle = $request->getPostParameter('printingPageTitle');
		$descriptionFormat = $request->getPostParameter('descriptionFormat');
		$priceFormat       = $request->getPostParameter('priceFormat');
		$printNoCents      = ( $request->getPostParameter('printNoCents') == 't' ) ? true : false;
		$filterByQuery     = null;

		if ( !empty( $elementIds ) )
		{
			// if current bill types is standard but provisional then list all the items associated with it
			if ( $bill->BillType->type == BillType::TYPE_PROVISIONAL )
			{
				$filterBy = PostContractBillItemRate::REMEASUREMENT_FILTER_BY_ALL_ITEMS;
			}

			if ( $filterBy != PostContractBillItemRate::REMEASUREMENT_FILTER_BY_ALL_ITEMS )
			{
				$filterByQuery = "AND i.type = " . BillItem::TYPE_ITEM_PROVISIONAL;
			}

			$stmt = $pdo->prepare("SELECT DISTINCT e.id, e.description, e.priority
			FROM " . SubPackagePostContractBillItemRateTable::getInstance()->getTableName() . " rate
			JOIN " . BillItemTable::getInstance()->getTableName() . " i ON rate.bill_item_id  = i.id AND i.deleted_at IS NULL
			JOIN " . BillElementTable::getInstance()->getTableName() . " e ON i.element_id =  e.id AND e.deleted_at IS NULL
			JOIN " . ProjectStructureTable::getInstance()->getTableName() . " s ON e.project_structure_id = s.id AND s.deleted_at IS NULL
			JOIN " . BillColumnSettingTable::getInstance()->getTableName() . " bcs ON bcs.project_structure_id = s.id AND bcs.deleted_at IS NULL
			JOIN " . BillTypeTable::getInstance()->getTableName() . " t ON t.project_structure_id = s.id
			JOIN " . BillLayoutSettingTable::getInstance()->getTableName() . " bls ON bls.bill_id = s.id
			WHERE s.id = " . $bill->id . " AND e.project_structure_id = " . $bill->id . " AND bcs.id = " . $billColumnSetting->id . "
			AND e.id IN (" . implode(',', $elementIds) . ") AND rate.sub_package_id = " . $subPackage->id . " {$filterByQuery}
			ORDER BY e.priority ASC");

			$stmt->execute();
			$elements = $stmt->fetchAll(PDO::FETCH_ASSOC);

			// get element remeasurement's claim costing
			$elementTotalRates = SubPackagePostContractBillItemRateTable::getPostContractSubPackageRemeasurementTotalItemRateGroupByElement($bill, $subPackage, $billColumnSetting, $filterBy);

			foreach ( $elements as $element )
			{
				$omission = 0;
				$addition = 0;

				if ( array_key_exists($element['id'], $elementTotalRates) )
				{
					$omission = $elementTotalRates[$element['id']][0]['omission'];
					$addition = $elementTotalRates[$element['id']][0]['addition'];
				}

				$element['name']                 = $element['description'];
				$element['omission']             = $omission;
				$element['addition']             = $addition;
				$element['nettAdditionOmission'] = $element['addition'] - $element['omission'];

				$records[] = $element;

				unset( $element );
			}

			unset( $elementTotalRates );
		}

		$reportGenerator = new sfRemeasurementElementReportGenerator($postContract, $bill, $records, $descriptionFormat);
		$pages           = $reportGenerator->generatePages();
		$maxRows         = $reportGenerator->getMaxRows();
		$currency        = $reportGenerator->getCurrency();
		$withoutPrice    = false;

		$params = array(
			'disable-smart-shrinking',
			'disable-javascript',
			'no-outline',
			'no-background',
			'enableEscaping' => true,
			'binPath'        => sfConfig::get('app_wkhtmltopdf_bin_path'),
			'margin-top'     => $reportGenerator->getMarginTop(),
			'margin-right'   => $reportGenerator->getMarginRight(),
			'margin-bottom'  => $reportGenerator->getMarginBottom(),
			'margin-left'    => $reportGenerator->getMarginLeft(),
			'page-size'      => $reportGenerator->getPageSize(),
			'orientation'    => $reportGenerator->getOrientation()
		);

		$stylesheet = $this->getBQStyling();
		$pdfGen     = new WkHtmlToPdf($params);
		$pageCount  = 1;

		if ( $pages instanceof SplFixedArray )
		{
			foreach ( $pages as $page )
			{
				if ( empty( $page ) )
				{
					continue;
				}

				$lastPage = ( $pageCount == $pages->count() - 1 ) ? true : false;

				$layout = $this->getPartial('printReport/pageLayout', array(
					'stylesheet'    => $stylesheet,
					'layoutStyling' => $reportGenerator->getLayoutStyling()
				));

				$layoutParams = array(
					'itemPage'                   => $page,
					'maxRows'                    => $maxRows - 3,
					'lastPage'                   => $lastPage,
					'currency'                   => $currency,
					'totalOmission'              => $reportGenerator->totalOmission,
					'totalAddition'              => $reportGenerator->totalAddition,
					'elementHeaderDescription'   => null,
					'elementCount'               => null,
					'printQty'                   => true,
					'pageCount'                  => $pageCount,
					'reportTitle'                => $printingPageTitle,
					'topLeftRow1'                => $postContract->ProjectStructure->MainInformation->title,
					'topLeftRow2'                => "{$bill->title} > {$billColumnSetting->name}",
					'botLeftRow1'                => $reportGenerator->getBottomLeftFirstRowHeader(),
					'botLeftRow2'                => $reportGenerator->getBottomLeftSecondRowHeader(),
					'descHeader'                 => $reportGenerator->getTableHeaderDescriptionPrefix(),
					'unitHeader'                 => $reportGenerator->getTableHeaderUnitPrefix(),
					'rateHeader'                 => $reportGenerator->getTableHeaderRatePrefix(),
					'qtyHeader'                  => $reportGenerator->getTableHeaderQtyPrefix(),
					'amtHeader'                  => $reportGenerator->getTableHeaderAmtPrefix(),
					'toCollection'               => $reportGenerator->getToCollectionPrefix(),
					'priceFormatting'            => $reportGenerator->generatePriceFormat($priceFormat, $printNoCents),
					'printNoPrice'               => $withoutPrice,
					'toggleColumnArrangement'    => $reportGenerator->getToggleColumnArrangement(),
					'printElementTitle'          => $reportGenerator->getPrintElementTitle(),
					'printDollarAndCentColumn'   => $reportGenerator->getPrintDollarAndCentColumn(),
					'currencyFormat'             => $reportGenerator->getCurrencyFormat(),
					'rateCommaRemove'            => $reportGenerator->getRateCommaRemove(),
					'qtyCommaRemove'             => $reportGenerator->getQtyCommaRemove(),
					'amtCommaRemove'             => $reportGenerator->getAmtCommaRemove(),
					'printAmountOnly'            => $reportGenerator->getPrintAmountOnly(),
					'printElementInGridOnce'     => $reportGenerator->getPrintElementInGridOnce(),
					'indentItem'                 => $reportGenerator->getIndentItem(),
					'printElementInGrid'         => $reportGenerator->getPrintElementInGrid(),
					'pageNoPrefix'               => $reportGenerator->getPageNoPrefix(),
					'printDateOfPrinting'        => $reportGenerator->getPrintDateOfPrinting(),
					'alignElementTitleToTheLeft' => $reportGenerator->getAlignElementToLeft(),
				);

				$layout .= $this->getPartial('postContractRemeasurementReport/reportRemeasurementByElements', $layoutParams);

				unset( $page );

				$pdfGen->addPage($layout);

				$pageCount ++;
			}
		}

		return $pdfGen->send();
	}

	public function executePrintingElementsWithAddition(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless(
			$request->isMethod('POST') AND
			$billColumnSetting = Doctrine_Core::getTable('BillColumnSetting')->find($request->getPostParameter('bill_type_id')) AND
			$subPackage = Doctrine_Core::getTable('SubPackage')->find($request->getPostParameter('subPackageId')) AND
			$bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getPostParameter('billId'))
		);

		session_write_close();

		$pdo           = $subPackage->getTable()->getConnection()->getDbh();
		$postContract  = $subPackage->ProjectStructure->PostContract;
		$filterBy      = $request->getPostParameter('opt');
		$records       = array();
		$filterByQuery = null;

		$printingPageTitle = $request->getPostParameter('printingPageTitle');
		$descriptionFormat = $request->getPostParameter('descriptionFormat');
		$priceFormat       = $request->getPostParameter('priceFormat');
		$printNoCents      = ( $request->getPostParameter('printNoCents') == 't' ) ? true : false;

		// if current bill types is standard but provisional then list all the items associated with it
		if ( $bill->BillType->type == BillType::TYPE_PROVISIONAL )
		{
			$filterBy = PostContractBillItemRate::REMEASUREMENT_FILTER_BY_ALL_ITEMS;
		}

		if ( $filterBy != PostContractBillItemRate::REMEASUREMENT_FILTER_BY_ALL_ITEMS )
		{
			$filterByQuery = "AND i.type = " . BillItem::TYPE_ITEM_PROVISIONAL;
		}

		$stmt = $pdo->prepare("SELECT DISTINCT e.id, e.description, e.priority
		FROM " . SubPackagePostContractBillItemRateTable::getInstance()->getTableName() . " rate
		JOIN " . BillItemTable::getInstance()->getTableName() . " i ON rate.bill_item_id  = i.id AND i.deleted_at IS NULL
		JOIN " . BillElementTable::getInstance()->getTableName() . " e ON i.element_id =  e.id AND e.deleted_at IS NULL
		JOIN " . ProjectStructureTable::getInstance()->getTableName() . " s ON e.project_structure_id = s.id AND s.deleted_at IS NULL
		JOIN " . BillColumnSettingTable::getInstance()->getTableName() . " bcs ON bcs.project_structure_id = s.id AND bcs.deleted_at IS NULL
		JOIN " . BillTypeTable::getInstance()->getTableName() . " t ON t.project_structure_id = s.id
		JOIN " . BillLayoutSettingTable::getInstance()->getTableName() . " bls ON bls.bill_id = s.id
		WHERE s.id = " . $bill->id . " AND e.project_structure_id = " . $bill->id . " AND bcs.id = " . $billColumnSetting->id . "
		AND rate.sub_package_id = " . $subPackage->id . " {$filterByQuery}
		ORDER BY e.priority ASC");

		$stmt->execute();
		$elements = $stmt->fetchAll(PDO::FETCH_ASSOC);

		// get element remeasurement's claim costing
		$elementTotalRates = SubPackagePostContractBillItemRateTable::getPostContractSubPackageRemeasurementTotalItemRateGroupByElement($bill, $subPackage, $billColumnSetting, $filterBy);

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
				$element['name']                 = $element['description'];
				$element['omission']             = $omission;
				$element['addition']             = $addition;
				$element['nettAdditionOmission'] = $element['addition'] - $element['omission'];

				$records[] = $element;
			}

			unset( $element );
		}

		unset( $elementTotalRates );

		$reportGenerator = new sfRemeasurementElementReportGenerator($postContract, $bill, $records, $descriptionFormat);
		$pages           = $reportGenerator->generatePages();
		$maxRows         = $reportGenerator->getMaxRows();
		$currency        = $reportGenerator->getCurrency();
		$withoutPrice    = false;

		$params = array(
			'disable-smart-shrinking',
			'disable-javascript',
			'no-outline',
			'no-background',
			'enableEscaping' => true,
			'binPath'        => sfConfig::get('app_wkhtmltopdf_bin_path'),
			'margin-top'     => $reportGenerator->getMarginTop(),
			'margin-right'   => $reportGenerator->getMarginRight(),
			'margin-bottom'  => $reportGenerator->getMarginBottom(),
			'margin-left'    => $reportGenerator->getMarginLeft(),
			'page-size'      => $reportGenerator->getPageSize(),
			'orientation'    => $reportGenerator->getOrientation()
		);

		$stylesheet = $this->getBQStyling();
		$pdfGen     = new WkHtmlToPdf($params);
		$pageCount  = 1;

		if ( $pages instanceof SplFixedArray )
		{
			foreach ( $pages as $page )
			{
				if ( empty( $page ) )
				{
					continue;
				}

				$lastPage = ( $pageCount == $pages->count() - 1 ) ? true : false;

				$layout = $this->getPartial('printReport/pageLayout', array(
					'stylesheet'    => $stylesheet,
					'layoutStyling' => $reportGenerator->getLayoutStyling()
				));

				$layoutParams = array(
					'itemPage'                   => $page,
					'maxRows'                    => $maxRows - 3,
					'lastPage'                   => $lastPage,
					'currency'                   => $currency,
					'totalOmission'              => $reportGenerator->totalOmission,
					'totalAddition'              => $reportGenerator->totalAddition,
					'elementHeaderDescription'   => null,
					'elementCount'               => null,
					'printQty'                   => true,
					'pageCount'                  => $pageCount,
					'reportTitle'                => $printingPageTitle,
					'topLeftRow1'                => $postContract->ProjectStructure->MainInformation->title,
					'topLeftRow2'                => "{$bill->title} > {$billColumnSetting->name}",
					'botLeftRow1'                => $reportGenerator->getBottomLeftFirstRowHeader(),
					'botLeftRow2'                => $reportGenerator->getBottomLeftSecondRowHeader(),
					'descHeader'                 => $reportGenerator->getTableHeaderDescriptionPrefix(),
					'unitHeader'                 => $reportGenerator->getTableHeaderUnitPrefix(),
					'rateHeader'                 => $reportGenerator->getTableHeaderRatePrefix(),
					'qtyHeader'                  => $reportGenerator->getTableHeaderQtyPrefix(),
					'amtHeader'                  => $reportGenerator->getTableHeaderAmtPrefix(),
					'toCollection'               => $reportGenerator->getToCollectionPrefix(),
					'priceFormatting'            => $reportGenerator->generatePriceFormat($priceFormat, $printNoCents),
					'printNoPrice'               => $withoutPrice,
					'toggleColumnArrangement'    => $reportGenerator->getToggleColumnArrangement(),
					'printElementTitle'          => $reportGenerator->getPrintElementTitle(),
					'printDollarAndCentColumn'   => $reportGenerator->getPrintDollarAndCentColumn(),
					'currencyFormat'             => $reportGenerator->getCurrencyFormat(),
					'rateCommaRemove'            => $reportGenerator->getRateCommaRemove(),
					'qtyCommaRemove'             => $reportGenerator->getQtyCommaRemove(),
					'amtCommaRemove'             => $reportGenerator->getAmtCommaRemove(),
					'printAmountOnly'            => $reportGenerator->getPrintAmountOnly(),
					'printElementInGridOnce'     => $reportGenerator->getPrintElementInGridOnce(),
					'indentItem'                 => $reportGenerator->getIndentItem(),
					'printElementInGrid'         => $reportGenerator->getPrintElementInGrid(),
					'pageNoPrefix'               => $reportGenerator->getPageNoPrefix(),
					'printDateOfPrinting'        => $reportGenerator->getPrintDateOfPrinting(),
					'alignElementTitleToTheLeft' => $reportGenerator->getAlignElementToLeft(),
				);

				$layout .= $this->getPartial('postContractRemeasurementReport/reportRemeasurementByElements', $layoutParams);

				unset( $page );

				$pdfGen->addPage($layout);

				$pageCount ++;
			}
		}

		return $pdfGen->send();
	}

	public function executePrintingSelectedItems(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless(
			$request->isMethod('POST') AND
			$billColumnSetting = Doctrine_Core::getTable('BillColumnSetting')->find($request->getPostParameter('bill_type_id')) AND
			$subPackage = Doctrine_Core::getTable('SubPackage')->find($request->getPostParameter('subPackageId')) AND
			$bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getPostParameter('billId'))
		);

		session_write_close();

		$pdo          = $subPackage->getTable()->getConnection()->getDbh();
		$postContract = $subPackage->ProjectStructure->PostContract;
		$filterBy     = $request->getPostParameter('opt');
		$itemIds      = json_decode($request->getPostParameter('selectedRows'), true);
		$pageNoPrefix = $bill->BillLayoutSetting->page_no_prefix;
		$elements     = array();
		$records      = array();

		$printingPageTitle = $request->getPostParameter('printingPageTitle');
		$descriptionFormat = $request->getPostParameter('descriptionFormat');
		$priceFormat       = $request->getPostParameter('priceFormat');
		$printNoCents      = ( $request->getPostParameter('printNoCents') == 't' ) ? true : false;
		$filterByQuery     = null;

		if ( !empty( $itemIds ) )
		{
			// if current bill types is standard but provisional then list all the items associated with it
			if ( $bill->BillType->type == BillType::TYPE_PROVISIONAL )
			{
				$filterBy = PostContractBillItemRate::REMEASUREMENT_FILTER_BY_ALL_ITEMS;
			}

			if ( $filterBy != PostContractBillItemRate::REMEASUREMENT_FILTER_BY_ALL_ITEMS )
			{
				$filterByQuery = "AND i.type = " . BillItem::TYPE_ITEM_PROVISIONAL;
			}

			list(
				$billItems, $remeasurementClaims, $billItemTypeReferences, $billItemTypeRefFormulatedColumns
				) = SubPackagePostContractBillItemRateTable::getRemeasurementItemListByItemIds($subPackage, $bill, $billColumnSetting, $itemIds, $filterBy);

			$stmt = $pdo->prepare("SELECT DISTINCT e.id, e.description, e.priority
			FROM " . SubPackagePostContractBillItemRateTable::getInstance()->getTableName() . " rate
			JOIN " . BillItemTable::getInstance()->getTableName() . " i ON rate.bill_item_id  = i.id AND i.deleted_at IS NULL
			JOIN " . BillElementTable::getInstance()->getTableName() . " e ON i.element_id =  e.id AND e.deleted_at IS NULL
			JOIN " . ProjectStructureTable::getInstance()->getTableName() . " s ON e.project_structure_id = s.id AND s.deleted_at IS NULL
			JOIN " . BillColumnSettingTable::getInstance()->getTableName() . " bcs ON bcs.project_structure_id = s.id AND bcs.deleted_at IS NULL
			JOIN " . BillTypeTable::getInstance()->getTableName() . " t ON t.project_structure_id = s.id
			JOIN " . BillLayoutSettingTable::getInstance()->getTableName() . " bls ON bls.bill_id = s.id
			WHERE s.id = " . $bill->id . " AND e.project_structure_id = " . $bill->id . " AND bcs.id = " . $billColumnSetting->id . "
			AND rate.sub_package_id = " . $subPackage->id . " {$filterByQuery}
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
					$billItem['has_note']                = ( $billItem['note'] != null && $billItem['note'] != '' ) ? true : false;
					$billItem['item_total']              = Utilities::prelimRounding($itemTotal);
					$billItem['omission-qty_per_unit']   = Utilities::prelimRounding($billItem['qty_per_unit']);
					$billItem['omission-total_per_unit'] = Utilities::prelimRounding($billItem['rate'] * $billItem['qty_per_unit']);
					$billItem['omission-has_build_up']   = false;
					$billItem['addition-qty_per_unit']   = 0;
					$billItem['addition-total_per_unit'] = 0;
					$billItem['addition-has_build_up']   = false;

					if ( array_key_exists($billItem['sub_package_post_contract_bill_item_rate_id'], $remeasurementClaims) )
					{
						$costing = $remeasurementClaims[$billItem['sub_package_post_contract_bill_item_rate_id']];

						$billItem['addition-qty_per_unit']   = Utilities::prelimRounding($costing['qty_per_unit']);
						$billItem['addition-total_per_unit'] = Utilities::prelimRounding($costing['total_per_unit']);
						$billItem['addition-has_build_up']   = $costing['has_build_up'];

						unset( $costing );
					}

					$billItem['nett_addition_omission'] = Utilities::prelimRounding($billItem['addition-total_per_unit'] - $billItem['omission-total_per_unit']);

					if ( array_key_exists($billColumnSetting->id, $billItemTypeReferences) && array_key_exists($billItem['id'], $billItemTypeReferences[$billColumnSetting->id]) )
					{
						$billItemTypeRef = $billItemTypeReferences[$billColumnSetting->id][$billItem['id']];

						unset( $billItemTypeReferences[$billColumnSetting->id][$billItem['id']] );

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

		$elementPages = $reportGenerator->generatePages();
		$maxRows      = $reportGenerator->getMaxRows();
		$currency     = $reportGenerator->getCurrency();
		$withoutPrice = false;

		$params = array(
			'disable-smart-shrinking',
			'disable-javascript',
			'no-outline',
			'no-background',
			'enableEscaping' => true,
			'binPath'        => sfConfig::get('app_wkhtmltopdf_bin_path'),
			'margin-top'     => $reportGenerator->getMarginTop(),
			'margin-right'   => $reportGenerator->getMarginRight(),
			'margin-bottom'  => $reportGenerator->getMarginBottom(),
			'margin-left'    => $reportGenerator->getMarginLeft(),
			'page-size'      => $reportGenerator->getPageSize(),
			'orientation'    => $reportGenerator->getOrientation()
		);

		$stylesheet = $this->getBQStyling();
		$pdfGen     = new WkHtmlToPdf($params);
		$pageCount  = 1;

		foreach ( $elementPages as $elementId => $pages )
		{
			for ( $i = 1; $i <= $pages['item_pages']->count(); $i ++ )
			{
				if ( $pages['item_pages'] instanceof SplFixedArray and $pages['item_pages']->offsetExists($i) )
				{
					$printGrandTotal = ( ( $i + 1 ) == $pages['item_pages']->count() ) ? true : false;

					$layout = $this->getPartial('printReport/pageLayout', array(
						'stylesheet'    => $stylesheet,
						'layoutStyling' => $reportGenerator->getLayoutStyling()
					));

					$billItemsLayoutParams = array(
						'itemPage'                   => $pages['item_pages']->offsetGet($i),
						'maxRows'                    => $maxRows,
						'currency'                   => $currency,
						'headerDescription'          => null,
						'pageCount'                  => $pageCount,
						'totalPage'                  => $reportGenerator->totalPage,
						'totalOmissionByElement'     => ( isset( $reportGenerator->totalOmissionByElement[$elementId] ) ) ? $reportGenerator->totalOmissionByElement[$elementId] : 0,
						'totalAdditionByElement'     => ( isset( $reportGenerator->totalAdditionByElement[$elementId] ) ) ? $reportGenerator->totalAdditionByElement[$elementId] : 0,
						'printGrandTotal'            => $printGrandTotal,
						'reportTitle'                => $printingPageTitle,
						'topLeftRow1'                => ( strlen($reportGenerator->printSettings['layoutSetting']['pageNoPrefix']) ) ? 'Prefix: ' . $reportGenerator->printSettings['layoutSetting']['pageNoPrefix'] : '',
						'topLeftRow2'                => $bill->title . ' > ' . $billColumnSetting->name,
						'botLeftRow1'                => $reportGenerator->getBottomLeftFirstRowHeader(),
						'botLeftRow2'                => $reportGenerator->getBottomLeftSecondRowHeader(),
						'descHeader'                 => $reportGenerator->getTableHeaderDescriptionPrefix(),
						'unitHeader'                 => $reportGenerator->getTableHeaderUnitPrefix(),
						'rateHeader'                 => $reportGenerator->getTableHeaderRatePrefix(),
						'qtyHeader'                  => $reportGenerator->getTableHeaderQtyPrefix(),
						'amtHeader'                  => $reportGenerator->getTableHeaderAmtPrefix(),
						'toCollection'               => $reportGenerator->getToCollectionPrefix(),
						'priceFormatting'            => $reportGenerator->generatePriceFormat($priceFormat, $printNoCents),
						'printNoPrice'               => $withoutPrice,
						'toggleColumnArrangement'    => $reportGenerator->getToggleColumnArrangement(),
						'printElementTitle'          => $reportGenerator->getPrintElementTitle(),
						'printDollarAndCentColumn'   => $reportGenerator->getPrintDollarAndCentColumn(),
						'currencyFormat'             => $reportGenerator->getCurrencyFormat(),
						'rateCommaRemove'            => $reportGenerator->getRateCommaRemove(),
						'qtyCommaRemove'             => $reportGenerator->getQtyCommaRemove(),
						'amtCommaRemove'             => $reportGenerator->getAmtCommaRemove(),
						'printAmountOnly'            => $reportGenerator->getPrintAmountOnly(),
						'printElementInGridOnce'     => $reportGenerator->getPrintElementInGridOnce(),
						'indentItem'                 => $reportGenerator->getIndentItem(),
						'printElementInGrid'         => $reportGenerator->getPrintElementInGrid(),
						'pageNoPrefix'               => $reportGenerator->getPageNoPrefix(),
						'printDateOfPrinting'        => $reportGenerator->getPrintDateOfPrinting(),
						'alignElementTitleToTheLeft' => $reportGenerator->getAlignElementToLeft(),
					);

					$layout .= $this->getPartial('postContractRemeasurementReport/reportRemeasurementByItems', $billItemsLayoutParams);

					$pages['item_pages']->offsetUnset($i);

					$pdfGen->addPage($layout);

					$pageCount ++;
				}
			}
		}

		return $pdfGen->send();
	}

	public function executePrintingItemsWithAdditionOnly(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless(
			$request->isMethod('POST') AND
			$billColumnSetting = Doctrine_Core::getTable('BillColumnSetting')->find($request->getPostParameter('bill_type_id')) AND
			$subPackage = Doctrine_Core::getTable('SubPackage')->find($request->getPostParameter('subPackageId')) AND
			$bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getPostParameter('billId'))
		);

		session_write_close();

		$pdo          = $subPackage->getTable()->getConnection()->getDbh();
		$postContract = $subPackage->ProjectStructure->PostContract;
		$pageNoPrefix = $bill->BillLayoutSetting->page_no_prefix;
		$elements     = array();
		$records      = array();
		$filterBy     = $request->getPostParameter('opt');

		$printingPageTitle = $request->getPostParameter('printingPageTitle');
		$descriptionFormat = $request->getPostParameter('descriptionFormat');
		$priceFormat       = $request->getPostParameter('priceFormat');
		$printNoCents      = ( $request->getPostParameter('printNoCents') == 't' ) ? true : false;

		// if current bill types is standard but provisional then list all the items associated with it
		if ( $bill->BillType->type == BillType::TYPE_PROVISIONAL )
		{
			$filterBy = PostContractBillItemRate::REMEASUREMENT_FILTER_BY_ALL_ITEMS;
		}

		list(
			$elementIds, $billItems, $remeasurementClaims, $billItemTypeReferences, $billItemTypeRefFormulatedColumns
			) = SubPackagePostContractBillItemRateTable::getRemeasurementItemListWithAdditionOnly($subPackage, $bill, $billColumnSetting, $filterBy);

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
					$billItem['has_note']                = ( $billItem['note'] != null && $billItem['note'] != '' ) ? true : false;
					$billItem['item_total']              = Utilities::prelimRounding($itemTotal);
					$billItem['omission-qty_per_unit']   = Utilities::prelimRounding($billItem['qty_per_unit']);
					$billItem['omission-total_per_unit'] = Utilities::prelimRounding($billItem['rate'] * $billItem['qty_per_unit']);
					$billItem['omission-has_build_up']   = false;
					$billItem['addition-qty_per_unit']   = 0;
					$billItem['addition-total_per_unit'] = 0;
					$billItem['addition-has_build_up']   = false;

					if ( array_key_exists($billItem['sub_package_post_contract_bill_item_rate_id'], $remeasurementClaims) )
					{
						$costing = $remeasurementClaims[$billItem['sub_package_post_contract_bill_item_rate_id']];

						$billItem['addition-qty_per_unit']   = Utilities::prelimRounding($costing['qty_per_unit']);
						$billItem['addition-total_per_unit'] = Utilities::prelimRounding($costing['total_per_unit']);
						$billItem['addition-has_build_up']   = $costing['has_build_up'];

						unset( $costing );
					}

					$billItem['nett_addition_omission'] = Utilities::prelimRounding($billItem['addition-total_per_unit'] - $billItem['omission-total_per_unit']);

					if ( array_key_exists($billColumnSetting->id, $billItemTypeReferences) && array_key_exists($billItem['id'], $billItemTypeReferences[$billColumnSetting->id]) )
					{
						$billItemTypeRef = $billItemTypeReferences[$billColumnSetting->id][$billItem['id']];

						unset( $billItemTypeReferences[$billColumnSetting->id][$billItem['id']] );

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

		$elementPages = $reportGenerator->generatePages();
		$maxRows      = $reportGenerator->getMaxRows();
		$currency     = $reportGenerator->getCurrency();
		$withoutPrice = false;

		$params = array(
			'disable-smart-shrinking',
			'disable-javascript',
			'no-outline',
			'no-background',
			'enableEscaping' => true,
			'binPath'        => sfConfig::get('app_wkhtmltopdf_bin_path'),
			'margin-top'     => $reportGenerator->getMarginTop(),
			'margin-right'   => $reportGenerator->getMarginRight(),
			'margin-bottom'  => $reportGenerator->getMarginBottom(),
			'margin-left'    => $reportGenerator->getMarginLeft(),
			'page-size'      => $reportGenerator->getPageSize(),
			'orientation'    => $reportGenerator->getOrientation()
		);

		$stylesheet = $this->getBQStyling();
		$pdfGen     = new WkHtmlToPdf($params);
		$pageCount  = 1;

		foreach ( $elementPages as $elementId => $pages )
		{
			for ( $i = 1; $i <= $pages['item_pages']->count(); $i ++ )
			{
				if ( $pages['item_pages'] instanceof SplFixedArray and $pages['item_pages']->offsetExists($i) )
				{
					$printGrandTotal = ( ( $i + 1 ) == $pages['item_pages']->count() ) ? true : false;

					$layout = $this->getPartial('printReport/pageLayout', array(
						'stylesheet'    => $stylesheet,
						'layoutStyling' => $reportGenerator->getLayoutStyling()
					));

					$billItemsLayoutParams = array(
						'itemPage'                   => $pages['item_pages']->offsetGet($i),
						'maxRows'                    => $maxRows,
						'currency'                   => $currency,
						'headerDescription'          => null,
						'pageCount'                  => $pageCount,
						'totalPage'                  => $reportGenerator->totalPage,
						'totalOmissionByElement'     => ( isset( $reportGenerator->totalOmissionByElement[$elementId] ) ) ? $reportGenerator->totalOmissionByElement[$elementId] : 0,
						'totalAdditionByElement'     => ( isset( $reportGenerator->totalAdditionByElement[$elementId] ) ) ? $reportGenerator->totalAdditionByElement[$elementId] : 0,
						'printGrandTotal'            => $printGrandTotal,
						'reportTitle'                => $printingPageTitle,
						'topLeftRow1'                => ( strlen($reportGenerator->printSettings['layoutSetting']['pageNoPrefix']) ) ? 'Prefix: ' . $reportGenerator->printSettings['layoutSetting']['pageNoPrefix'] : '',
						'topLeftRow2'                => $bill->title . ' > ' . $billColumnSetting->name,
						'botLeftRow1'                => $reportGenerator->getBottomLeftFirstRowHeader(),
						'botLeftRow2'                => $reportGenerator->getBottomLeftSecondRowHeader(),
						'descHeader'                 => $reportGenerator->getTableHeaderDescriptionPrefix(),
						'unitHeader'                 => $reportGenerator->getTableHeaderUnitPrefix(),
						'rateHeader'                 => $reportGenerator->getTableHeaderRatePrefix(),
						'qtyHeader'                  => $reportGenerator->getTableHeaderQtyPrefix(),
						'amtHeader'                  => $reportGenerator->getTableHeaderAmtPrefix(),
						'toCollection'               => $reportGenerator->getToCollectionPrefix(),
						'priceFormatting'            => $reportGenerator->generatePriceFormat($priceFormat, $printNoCents),
						'printNoPrice'               => $withoutPrice,
						'toggleColumnArrangement'    => $reportGenerator->getToggleColumnArrangement(),
						'printElementTitle'          => $reportGenerator->getPrintElementTitle(),
						'printDollarAndCentColumn'   => $reportGenerator->getPrintDollarAndCentColumn(),
						'currencyFormat'             => $reportGenerator->getCurrencyFormat(),
						'rateCommaRemove'            => $reportGenerator->getRateCommaRemove(),
						'qtyCommaRemove'             => $reportGenerator->getQtyCommaRemove(),
						'amtCommaRemove'             => $reportGenerator->getAmtCommaRemove(),
						'printAmountOnly'            => $reportGenerator->getPrintAmountOnly(),
						'printElementInGridOnce'     => $reportGenerator->getPrintElementInGridOnce(),
						'indentItem'                 => $reportGenerator->getIndentItem(),
						'printElementInGrid'         => $reportGenerator->getPrintElementInGrid(),
						'pageNoPrefix'               => $reportGenerator->getPageNoPrefix(),
						'printDateOfPrinting'        => $reportGenerator->getPrintDateOfPrinting(),
						'alignElementTitleToTheLeft' => $reportGenerator->getAlignElementToLeft(),
					);

					$layout .= $this->getPartial('postContractRemeasurementReport/reportRemeasurementByItems', $billItemsLayoutParams);

					$pages['item_pages']->offsetUnset($i);

					$pdfGen->addPage($layout);

					$pageCount ++;
				}
			}
		}

		return $pdfGen->send();
	}

	public function executePrintingSelectedItemsWithBuildUpQty(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless(
			$request->isMethod('POST') AND
			$billType = Doctrine_Core::getTable('BillColumnSetting')->find($request->getPostParameter('bill_type_id')) AND
			$subPackage = Doctrine_Core::getTable('SubPackage')->find($request->getPostParameter('subPackageId')) AND
			$bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getPostParameter('billId'))
		);

		session_write_close();

		$project            = $subPackage->ProjectStructure;
		$billColumnSettings = array( $billType );
		$itemIds            = (array) json_decode($request->getPostParameter('selectedRows'), true);
		$pageNoPrefix       = $bill->BillLayoutSetting->page_no_prefix;
		$postContract       = $subPackage->ProjectStructure->PostContract;
		$typesArray         = array( PostContractRemeasurementBuildUpQuantityItem::OMISSION_TYPE_TEXT, PostContractRemeasurementBuildUpQuantityItem::ADDITION_TYPE_TEXT );

		$printingPageTitle = $request->getPostParameter('printingPageTitle');
		$descriptionFormat = $request->getPostParameter('descriptionFormat');
		$priceFormat       = $request->getPostParameter('priceFormat');
		$printNoCents      = ( $request->getPostParameter('printNoCents') == 't' ) ? true : false;
		$filterBy          = $request->getPostParameter('opt');
		$withoutPrice      = false;
		$stylesheet        = $this->getBQStyling();

		// if current bill types is standard but provisional then list all the items associated with it
		if ( $bill->BillType->type == BillType::TYPE_PROVISIONAL )
		{
			$filterBy = PostContractBillItemRate::REMEASUREMENT_FILTER_BY_ALL_ITEMS;
		}

		list(
			$elementIds, $billItems, $remeasurementClaims,
			$billItemTypeReferences, $billItemTypeRefFormulatedColumns, $buildUpQuantityItems,
			$billBuildUpQuantitySummaries, $quantityPerUnitByColumns, $unitsDimensions
			) = SubPackagePostContractBillItemRateTable::getRemeasurementItemListWithBuildUpQtyOnly($subPackage, $bill, $billType, $itemIds, $filterBy);

		list(
			$soqItemsData, $soqFormulatedColumns, $manualBuildUpQuantityItems, $importedBuildUpQuantityItems
			) = ScheduleOfQuantityBillItemXrefTable::getSelectedItemsBuildUpQuantity($project, $billColumnSettings, $billItems);

		$reportPrintGenerator = new sfPostContractRemeasurementItemBuildUpQtyReportGenerator($postContract, $bill, $descriptionFormat);
		$currency             = $reportPrintGenerator->getCurrency();
		$pageCount            = 1;

		$reportPrintGenerator->setOrientationAndSize('portrait');

		if ( empty( $billItems ) )
		{
			$this->message     = 'ERROR';
			$this->explanation = "Nothing can be printed because there are no item(s) selection detected.";

			$this->setTemplate('nothingToPrint');

			return sfView::SUCCESS;
		}

		$pdfGen = new WkHtmlToPdf(array(
			'disable-smart-shrinking',
			'disable-javascript',
			'no-outline',
			'no-background',
			'enableEscaping' => true,
			'binPath'        => sfConfig::get('app_wkhtmltopdf_bin_path'),
			'margin-top'     => $reportPrintGenerator->getMarginTop(),
			'margin-right'   => $reportPrintGenerator->getMarginRight(),
			'margin-bottom'  => $reportPrintGenerator->getMarginBottom(),
			'margin-left'    => $reportPrintGenerator->getMarginLeft(),
			'page-size'      => $reportPrintGenerator->getPageSize(),
			'orientation'    => $reportPrintGenerator->getOrientation(),
		));

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

			if ( array_key_exists($billItem['sub_package_post_contract_bill_item_rate_id'], $remeasurementClaims) )
			{
				$costing = $remeasurementClaims[$billItem['sub_package_post_contract_bill_item_rate_id']];

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

			$maxRows = $reportPrintGenerator->getMaxRows();

			foreach ( $typesArray as $type )
			{
				$typeName                   = ucfirst($type);
				$billItemId                 = ( $type == PostContractRemeasurementBuildUpQuantityItem::OMISSION_TYPE_TEXT ) ? $billItem['id'] : $billItem['sub_package_post_contract_bill_item_rate_id'];
				$columnPageCount            = 1;
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

				if ( !( $pages instanceof SplFixedArray ) )
				{
					continue;
				}

				foreach ( $pages as $page )
				{
					if ( count($page) == 0 )
					{
						continue;
					}

					$lastPage = ( $columnPageCount == $pages->count() - 1 ) ? true : false;

					$layout = $this->getPartial('printReport/pageLayout', array(
						'stylesheet'    => $stylesheet,
						'layoutStyling' => $reportPrintGenerator->getLayoutStyling()
					));

					$billItemsLayoutParams = array(
						'buildUpQuantitySummary'     => $buildUpQuantitySummaryInfo,
						'lastPage'                   => $lastPage,
						'totalQtyPerColumnSetting'   => $quantityPerUnit,
						'billItemInfos'              => $billItemInfo,
						'billItemUOM'                => $billItem['uom_symbol'],
						'itemPage'                   => $page,
						'maxRows'                    => $maxRows + 2,
						'currency'                   => $currency,
						'pageCount'                  => $pageCount,
						'elementTitle'               => $billItem['element_description'],
						'printingPageTitle'          => $printingPageTitle,
						'billDescription'            => "{$bill->title} > {$billType['name']} > {$typeName}",
						'columnDescription'          => null,
						'dimensions'                 => $dimensions,
						'priceFormatting'            => $reportPrintGenerator->generatePriceFormat($priceFormat, $printNoCents),
						'printNoPrice'               => $withoutPrice,
						'toggleColumnArrangement'    => $reportPrintGenerator->getToggleColumnArrangement(),
						'printElementTitle'          => $reportPrintGenerator->getPrintElementTitle(),
						'printDollarAndCentColumn'   => $reportPrintGenerator->getPrintDollarAndCentColumn(),
						'currencyFormat'             => $reportPrintGenerator->getCurrencyFormat(),
						'rateCommaRemove'            => $reportPrintGenerator->getRateCommaRemove(),
						'qtyCommaRemove'             => $reportPrintGenerator->getQtyCommaRemove(),
						'amtCommaRemove'             => $reportPrintGenerator->getAmtCommaRemove(),
						'printAmountOnly'            => $reportPrintGenerator->getPrintAmountOnly(),
						'printElementInGridOnce'     => $reportPrintGenerator->getPrintElementInGridOnce(),
						'indentItem'                 => $reportPrintGenerator->getIndentItem(),
						'printElementInGrid'         => $reportPrintGenerator->getPrintElementInGrid(),
						'pageNoPrefix'               => $reportPrintGenerator->getPageNoPrefix(),
						'printDateOfPrinting'        => $reportPrintGenerator->getPrintDateOfPrinting(),
						'alignElementTitleToTheLeft' => $reportPrintGenerator->getAlignElementToLeft(),
					);

					$layout .= $this->getPartial('postContractRemeasurementReport/buildUpQtyReport', $billItemsLayoutParams);

					$pdfGen->addPage($layout);

					unset( $layout, $page );

					$pageCount ++;
					$columnPageCount ++;
				}
			}

			unset( $billItem );
		}

		return $pdfGen->send();
	}

	public function executePrintProjectClaimSummary(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless(
			$subPackage = Doctrine_Core::getTable('SubPackage')->find($request->getParameter('subPackageId')) and
			$project = Doctrine_Core::getTable('ProjectStructure')->find($subPackage->project_structure_id)
		);

		$records      = array();
		$count        = 0;
		$revision     = SubPackagePostContractClaimRevisionTable::getCurrentSelectedProjectRevision($subPackage);
		$stylesheet   = file_get_contents(sfConfig::get('sf_web_dir') . '/css/projectSummary.css');
		$pageTitle    = $request->getPostParameter('printingPageTitle');
		$pageNoPrefix = $request->getPostParameter('pageNoPrefix');

		$pdo = $subPackage->getTable()->getConnection()->getDbh();

		$stmt = $pdo->prepare("SELECT s.id, s.title, s.type, s.level, t.type AS bill_type, t.status AS bill_status,
		bls.id AS layout_id, COALESCE(SUM(rate.single_unit_grand_total), 0) AS overall_total_after_markup
		FROM " . SubPackagePostContractBillItemRateTable::getInstance()->getTableName() . " rate
		JOIN " . BillItemTable::getInstance()->getTableName() . " i ON rate.bill_item_id  = i.id AND i.deleted_at IS NULL
		JOIN " . BillElementTable::getInstance()->getTableName() . " e ON i.element_id =  e.id AND e.deleted_at IS NULL
		JOIN " . ProjectStructureTable::getInstance()->getTableName() . " s ON e.project_structure_id = s.id AND s.deleted_at IS NULL
		JOIN " . BillTypeTable::getInstance()->getTableName() . " t ON t.project_structure_id = s.id
		JOIN " . BillLayoutSettingTable::getInstance()->getTableName() . " bls ON bls.bill_id = s.id
		WHERE rate.sub_package_id = " . $subPackage->id . " GROUP BY s.id, s.title, s.type, s.level, t.type,
		t.status, bls.id ORDER BY s.id ASC");

		$stmt->execute();

		$bills = $stmt->fetchAll(PDO::FETCH_ASSOC);

		foreach ( $bills as $key => $record )
		{
			$count ++;

			$bills[$key]['count']                 = $count;
			$bills[$key]['billLayoutSettingId']   = $record['layout_id'];
			$bills[$key]['up_to_date_percentage'] = 0;
			$bills[$key]['up_to_date_amount']     = 0;

			if ( $bills[$key]['bill_type'] == BillType::TYPE_PRELIMINARY )
			{
				list( $billTotal, $upToDateAmount ) = SubPackagePreliminariesClaimTable::getUpToDateAmountByBillId($subPackage, $record['id'], $revision);
			}
			else
			{
				$bills[$key]['overall_total_after_markup'] = SubPackagePostContractStandardClaimTable::getOverallTotalByBillId($record['id'], $revision);
				$upToDateAmount                            = SubPackagePostContractStandardClaimTable::getUpToDateAmountByBillId($record['id'], $revision);
			}

			$percentage = ( $bills[$key]['overall_total_after_markup'] > 0 ) ? number_format(( $upToDateAmount / $bills[$key]['overall_total_after_markup'] ) * 100, 2, '.', '') : 0;

			$bills[$key]['up_to_date_percentage'] = ( $percentage ) ? $percentage : 0;
			$bills[$key]['up_to_date_amount']     = ( $upToDateAmount ) ? $upToDateAmount : 0;

			array_push($records, $bills[$key]);

			unset( $record, $bills['layout_id'], $bills['quantity'] );
		}

		$additionalAutoBills = $this->generateDefaultPostContractSubPackageBills($subPackage);
		$reportGenerator     = new sfBuildSpacePostContractClaimReportGenerator($project->PostContract, $records);

		$reportGenerator->setTitle($pageTitle);

		$page = $reportGenerator->generatePage();

		$pdfGen = new WkHtmlToPdf(array(
			'disable-smart-shrinking',
			'disable-javascript',
			'no-outline',
			'no-background',
			'enableEscaping' => true,
			'binPath'        => sfConfig::get('app_wkhtmltopdf_bin_path'),
			'margin-top'     => 8,
			'margin-right'   => 7,
			'margin-bottom'  => 3,
			'margin-left'    => 24,
			'page-size'      => 'A4',
			'orientation'    => "Portrait"
		));

		if ( $page['summary_items'] instanceof SplFixedArray )
		{
			$pageNumberPrefix = $pageNoPrefix;

			foreach ( $page['summary_items'] as $pageCount => $summaryItems )
			{
				$layout = $this->getPartial('postContractReport/pageLayout', array(
					'title'      => $project->ProjectSummaryGeneralSetting->summary_title,
					'stylesheet' => $stylesheet
				));

				$pageCount += 1;

				$isLastPage = $pageCount == count($page['summary_items']) ? true : false;
				$maxRow     = $reportGenerator->MAX_ROWS;

				if ( !$isLastPage )
				{
					$maxRow = $reportGenerator->DEFAULT_MAX_ROWS;
				}

				$layout .= $this->getPartial('postContractReport/itemPageLayout', array(
					'pageNumber'                => $pageNumberPrefix . "&nbsp;" . $pageCount,
					'summaryTitleRows'          => $page['header'],
					'itemPage'                  => $summaryItems,
					'withPrice'                 => true,
					'currency'                  => $project->MainInformation->Currency->currency_code,
					'overallTotalProjectAmount' => $reportGenerator->getOverallContractAmount(),
					'overallTotalClaimAmount'   => $reportGenerator->getOverallTotalClaimAmount(),
					'projectSummaryFooter'      => null,
					'isLastPage'                => $isLastPage,
					'MAX_ROWS'                  => $maxRow,
					'additionalDescriptions'    => array(),
					'additionalAutoBills'       => $additionalAutoBills,
					'revision'                  => $revision,
				));

				unset( $summaryItems );

				$pdfGen->addPage($layout);
			}
		}

		// ... send to client as file download
		return $pdfGen->send();
	}

	public function executeExportExcelProjectClaimReport(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless(
			$subPackage = Doctrine_Core::getTable('SubPackage')->find($request->getParameter('subPackageId')) and
			$project = Doctrine_Core::getTable('ProjectStructure')->find($subPackage->project_structure_id)
		);

		$records      = array();
		$count        = 0;
		$revision     = SubPackagePostContractClaimRevisionTable::getCurrentSelectedProjectRevision($subPackage);
		$fileName     = $request->getPostParameter('exportFileName') ? : 'Project Claim Summary';
		$pageTitle    = $request->getPostParameter('printingPageTitle');
		$pageNoPrefix = $request->getPostParameter('pageNoPrefix');

		$pdo = $subPackage->getTable()->getConnection()->getDbh();

		$stmt = $pdo->prepare("SELECT s.id, s.title, s.type, s.level, t.type AS bill_type, t.status AS bill_status,
		bls.id AS layout_id, COALESCE(SUM(rate.single_unit_grand_total), 0) AS overall_total_after_markup
		FROM " . SubPackagePostContractBillItemRateTable::getInstance()->getTableName() . " rate
		JOIN " . BillItemTable::getInstance()->getTableName() . " i ON rate.bill_item_id  = i.id AND i.deleted_at IS NULL
		JOIN " . BillElementTable::getInstance()->getTableName() . " e ON i.element_id =  e.id AND e.deleted_at IS NULL
		JOIN " . ProjectStructureTable::getInstance()->getTableName() . " s ON e.project_structure_id = s.id AND s.deleted_at IS NULL
		JOIN " . BillTypeTable::getInstance()->getTableName() . " t ON t.project_structure_id = s.id
		JOIN " . BillLayoutSettingTable::getInstance()->getTableName() . " bls ON bls.bill_id = s.id
		WHERE rate.sub_package_id = " . $subPackage->id . " GROUP BY s.id, s.title, s.type, s.level, t.type,
		t.status, bls.id ORDER BY s.id ASC");

		$stmt->execute();

		$bills = $stmt->fetchAll(PDO::FETCH_ASSOC);

		foreach ( $bills as $key => $record )
		{
			$count ++;

			$bills[$key]['count']                 = $count;
			$bills[$key]['billLayoutSettingId']   = $record['layout_id'];
			$bills[$key]['up_to_date_percentage'] = 0;
			$bills[$key]['up_to_date_amount']     = 0;

			if ( $bills[$key]['bill_type'] == BillType::TYPE_PRELIMINARY )
			{
				list( $billTotal, $upToDateAmount ) = SubPackagePreliminariesClaimTable::getUpToDateAmountByBillId($subPackage, $record['id'], $revision);
			}
			else
			{
				$bills[$key]['overall_total_after_markup'] = SubPackagePostContractStandardClaimTable::getOverallTotalByBillId($record['id'], $revision);
				$upToDateAmount                            = SubPackagePostContractStandardClaimTable::getUpToDateAmountByBillId($record['id'], $revision);
			}

			$percentage = ( $bills[$key]['overall_total_after_markup'] > 0 ) ? number_format(( $upToDateAmount / $bills[$key]['overall_total_after_markup'] ) * 100, 2, '.', '') : 0;

			$bills[$key]['up_to_date_percentage'] = ( $percentage ) ? $percentage : 0;
			$bills[$key]['up_to_date_amount']     = ( $upToDateAmount ) ? $upToDateAmount : 0;

			array_push($records, $bills[$key]);

			unset( $record, $bills['layout_id'], $bills['quantity'] );
		}

		$additionalAutoBills = $this->generateDefaultPostContractSubPackageBills($subPackage);
		$reportGenerator     = new sfBuildSpacePostContractClaimReportGenerator($project->PostContract, $records);

		$reportGenerator->setTitle($pageTitle);

		$page = $reportGenerator->generatePage();

		// will pump the generated page data into excel exporter
		$excelGenerator = new sfBuildSpacePostContractClaimExcelExportGenerator($project->PostContract, $revision, $page, $additionalAutoBills, $pageNoPrefix);
		$tmpFile        = $excelGenerator->write();
		$fileSize       = filesize($tmpFile);
		$fileContents   = file_get_contents($tmpFile);

		$this->getResponse()->clearHttpHeaders();
		$this->getResponse()->setStatusCode(200);
		$this->getResponse()->setContentType('application/vnd.ms-excel');
		$this->getResponse()->setHttpHeader('Content-Disposition', "attachment; filename={$fileName}.xlsx");
		$this->getResponse()->setHttpHeader('Content-Transfer-Encoding', 'binary');
		$this->getResponse()->setHttpHeader('Content-Length', $fileSize);

		unlink($tmpFile);

		return $this->renderText($fileContents);
	}
	// ======================================================================================================================================

	/**
	 * @param $elements
	 * @param $data
	 * @param $typeId
	 */
	private function generateSelectionKeyArray($elements, &$data, $typeId)
	{
		foreach ( $elements as $element )
		{
			if ( !isset( $data[$typeId][$element['element_id']] ) )
			{
				$data[$typeId][$element['element_id']] = array();
			}

			$data[$typeId][$element['element_id']][] = $element['item_id'];
		}
	}

	private function generateDefaultPostContractSubPackageBills(SubPackage $subPackage)
	{
        $data[ PostContractClaim::TYPE_VARIATION_ORDER ]['title']                      = PostContractClaim::TYPE_VARIATION_ORDER_TEXT;
        $data[ PostContractClaim::TYPE_VARIATION_ORDER ]['up_to_date_amount']          = $subPackage->getVariationOrderUpToDateClaimAmount() ?: 0;
        $data[ PostContractClaim::TYPE_VARIATION_ORDER ]['up_to_date_percentage']      = $subPackage->getVariationOrderUpToDateClaimAmountPercentage() ?: 0;
        $data[ PostContractClaim::TYPE_VARIATION_ORDER ]['overall_total_after_markup'] = $subPackage->getVariationOrderOverallTotal();

		$data[PostContractClaim::TYPE_MATERIAL_ON_SITE]['title']                      = PostContractClaim::TYPE_MATERIAL_ON_SITE_TEXT;
		$data[PostContractClaim::TYPE_MATERIAL_ON_SITE]['up_to_date_amount']          = 0;
		$data[PostContractClaim::TYPE_MATERIAL_ON_SITE]['overall_total_after_markup'] = $subPackage->getMaterialOnSiteUpToDateClaimAmount();

		return $data;
	}

}