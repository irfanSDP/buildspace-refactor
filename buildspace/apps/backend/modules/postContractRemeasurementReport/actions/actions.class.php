<?php

/**
 * postContractRemeasurementReport actions.
 *
 * @package    buildspace
 * @subpackage postContractRemeasurementReport
 * @author     1337 developers
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class postContractRemeasurementReportActions extends BaseActions {

	public function executeGetElementsAndItemsByTypes(sfWebRequest $request)
	{
		$this->forward404Unless(
			$request->isXmlHttpRequest() AND
			$request->hasParameter('type_ids') AND
			$request->hasParameter('opt') AND
			$bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('bill_id')) AND
			$postContract = Doctrine_Core::getTable('PostContract')->findOneBy('project_structure_id', $bill->root_id)
		);

		$typeIds = json_decode($request->getParameter('type_ids'), true);
		$data    = array();

		if ( !empty( $typeIds ) )
		{
			$filterBy = $request->getParameter('opt');

			// if current bill types is standard but provisional then list all the items associated with it
			if ( $bill->BillType->type == BillType::TYPE_PROVISIONAL )
			{
				$filterBy = PostContractBillItemRate::REMEASUREMENT_FILTER_BY_ALL_ITEMS;
			}

			$query = DoctrineQuery::create()
				->select('i.id as item_id, e.id as element_id')
				->from('BillElement e')
				->leftJoin('e.Items i')
				->leftJoin('e.FormulatedColumns fc')
				->where('e.project_structure_id = ?', $bill->id)
				->addOrderBy('e.priority ASC');

			if ( $filterBy != PostContractBillItemRate::REMEASUREMENT_FILTER_BY_ALL_ITEMS )
			{
				$query->andWhere('i.type = ?', BillItem::TYPE_ITEM_PROVISIONAL);
			}

			$elements = $query->fetchArray();

			foreach ( $typeIds as $typeId )
			{
				if ( count($elements) == 0 )
				{
					$data[$typeId] = array();
					continue;
				}

				foreach ( $elements as $element )
				{
					$data[$typeId][$element['element_id']] = array();

					foreach ( $element['Items'] as $item )
					{
						$data[$typeId][$element['element_id']][] = $item['item_id'];
					}
				}
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
			$billType = Doctrine_Core::getTable('BillColumnSetting')->find($request->getParameter('bill_type_id')) and
			$bill = Doctrine_Core::getTable('ProjectStructure')->find($billType->project_structure_id) and
			$postContract = Doctrine_Core::getTable('PostContract')->findOneBy('project_structure_id', $bill->root_id)
		);

		$elementIds = json_decode($request->getParameter('element_ids'), true);
		$data       = array();

		if ( !empty( $elementIds ) )
		{
			$typeId   = $billType->id;
			$filterBy = $request->getParameter('opt');

			// if current bill types is standard but provisional then list all the items associated with it
			if ( $bill->BillType->type == BillType::TYPE_PROVISIONAL )
			{
				$filterBy = PostContractBillItemRate::REMEASUREMENT_FILTER_BY_ALL_ITEMS;
			}

			$query = DoctrineQuery::create()
				->select('i.id as item_id, e.id as element_id')
				->from('BillElement e')
				->leftJoin('e.Items i')
				->leftJoin('e.FormulatedColumns fc')
				->where('e.project_structure_id = ?', $bill->id)
				->andWhereIn('e.id', $elementIds)
				->addOrderBy('e.priority ASC');

			if ( $filterBy != PostContractBillItemRate::REMEASUREMENT_FILTER_BY_ALL_ITEMS )
			{
				$query->andWhere('i.type = ?', BillItem::TYPE_ITEM_PROVISIONAL);
			}

			$elements = $query->fetchArray();

			if ( count($elements) == 0 )
			{
				$data[$typeId] = array();
			}

			foreach ( $elements as $element )
			{
				$data[$typeId][$element['element_id']] = array();

				foreach ( $element['Items'] as $item )
				{
					$data[$typeId][$element['element_id']][] = $item['item_id'];
				}
			}

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
			$billType = Doctrine_Core::getTable('BillColumnSetting')->find($request->getParameter('bill_type_id')) and
			$bill = Doctrine_Core::getTable('ProjectStructure')->find($billType->project_structure_id) and
			$postContract = Doctrine_Core::getTable('PostContract')->findOneBy('project_structure_id', $bill->root_id)
		);

		$itemIds = json_decode($request->getParameter('item_ids'), true);
		$data    = array();

		if ( !empty( $itemIds ) )
		{
			$typeId   = $billType->id;
			$filterBy = $request->getParameter('opt');

			// if current bill types is standard but provisional then list all the items associated with it
			if ( $bill->BillType->type == BillType::TYPE_PROVISIONAL )
			{
				$filterBy = PostContractBillItemRate::REMEASUREMENT_FILTER_BY_ALL_ITEMS;
			}

			$query = DoctrineQuery::create()
				->select('i.id as item_id, e.id as element_id')
				->from('BillElement e')
				->leftJoin('e.Items i')
				->leftJoin('e.FormulatedColumns fc')
				->where('e.project_structure_id = ?', $bill->id)
				->andWhereIn('i.id', $itemIds)
				->addOrderBy('e.priority ASC');

			if ( $filterBy != PostContractBillItemRate::REMEASUREMENT_FILTER_BY_ALL_ITEMS )
			{
				$query->andWhere('i.type = ?', BillItem::TYPE_ITEM_PROVISIONAL);
			}

			$elements = $query->fetchArray();

			if ( count($elements) == 0 )
			{
				$data[$typeId] = array();
			}

			foreach ( $elements as $element )
			{
				$data[$typeId][$element['element_id']] = array();

				foreach ( $element['Items'] as $item )
				{
					$data[$typeId][$element['element_id']][] = $item['item_id'];
				}
			}

			unset( $elements );
		}

		return $this->renderJson($data);
	}

	public function executeGetPrintingSelectedTypes(sfWebRequest $request)
	{
		$this->forward404Unless(
			$request->isXmlHttpRequest() AND
			$request->hasParameter('type_ids') AND
			$bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('bill_id')) AND
			$postContract = Doctrine_Core::getTable('PostContract')->findOneBy('project_structure_id', $bill->root_id)
		);

		$typeIds  = json_decode($request->getParameter('type_ids'), true);
		$filterBy = $request->getParameter('opt');
		$records  = array();

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
			$bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('bill_id')) AND
			$postContract = Doctrine_Core::getTable('PostContract')->findOneBy('project_structure_id', $bill->root_id)
		);

		$typeIds  = json_decode($request->getParameter('type_ids'), true);
		$filterBy = $request->getParameter('opt');
		$records  = array();

		$billMarkupSetting = $bill->BillMarkupSetting;

		//We get All Element Sum Group By Element Here so that we don't have to reapeat query within element loop
		$markupSettingsInfo = array(
			'bill_markup_enabled'    => $billMarkupSetting->bill_markup_enabled,
			'bill_markup_percentage' => $billMarkupSetting->bill_markup_percentage,
			'element_markup_enabled' => $billMarkupSetting->element_markup_enabled,
			'item_markup_enabled'    => $billMarkupSetting->item_markup_enabled,
			'rounding_type'          => $billMarkupSetting->rounding_type > 0 ? $billMarkupSetting->rounding_type : BillMarkupSetting::ROUNDING_TYPE_DISABLED
		);

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
				->select('e.id, e.description, e.note, fc.column_name, fc.value, fc.final_value')
				->from('BillElement e')
				->leftJoin('e.FormulatedColumns fc')
				->where('e.project_structure_id = ?', $bill->id)
				->addOrderBy('e.priority ASC');

			if ( $filterBy != PostContractBillItemRate::REMEASUREMENT_FILTER_BY_ALL_ITEMS )
			{
				$query->leftJoin('e.Items i')->andWhere('i.type = ?', BillItem::TYPE_ITEM_PROVISIONAL);
			}

			$elements = $query->fetchArray();

			$attachRowHeader = ( count($elements) > 0 ) ? true : false;

			foreach ( $types as $type )
			{
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
						'markup_rounding_type'       => $billMarkupSetting->rounding_type,
					);

					array_push($records, $typeRow);

					unset( $typeRow );
				}

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

					$element['id']                   = "bill_type-{$type['id']}-{$element['id']}";
					$element['name']                 = $element['description'];
					$element['omission']             = $omission;
					$element['addition']             = $addition;
					$element['nettAdditionOmission'] = $element['addition'] - $element['omission'];
					$element['markup_rounding_type'] = $markupSettingsInfo['rounding_type'];
					$element['has_note']             = ( $element['note'] != null && $element['note'] != '' ) ? true : false;
					$element['note']                 = (string) $element['note'];
					$element['relation_id']          = $bill->id;

					$records[] = $element;

					unset( $element );
				}

				unset( $elementTotalRates );
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
			'relation_id'                => $bill->id,
			'markup_rounding_type'       => $billMarkupSetting->rounding_type,
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
			$billType = Doctrine_Core::getTable('BillColumnSetting')->find($request->getParameter('bill_type_id')) and
			$bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('bill_id')) AND
			$postContract = Doctrine_Core::getTable('PostContract')->findOneBy('project_structure_id', $bill->root_id)
		);

		$elementIds = json_decode($request->getParameter('element_ids'), true);
		$records    = array();

		$billMarkupSetting = $bill->BillMarkupSetting;

		//We get All Element Sum Group By Element Here so that we don't have to reapeat query within element loop
		$markupSettingsInfo = array(
			'bill_markup_enabled'    => $billMarkupSetting->bill_markup_enabled,
			'bill_markup_percentage' => $billMarkupSetting->bill_markup_percentage,
			'element_markup_enabled' => $billMarkupSetting->element_markup_enabled,
			'item_markup_enabled'    => $billMarkupSetting->item_markup_enabled,
			'rounding_type'          => $billMarkupSetting->rounding_type > 0 ? $billMarkupSetting->rounding_type : BillMarkupSetting::ROUNDING_TYPE_DISABLED
		);

		if ( !empty( $elementIds ) )
		{
			$filterBy = $request->getParameter('opt');

			// if current bill types is standard but provisional then list all the items associated with it
			if ( $bill->BillType->type == BillType::TYPE_PROVISIONAL )
			{
				$filterBy = PostContractBillItemRate::REMEASUREMENT_FILTER_BY_ALL_ITEMS;
			}

			$query = DoctrineQuery::create()
				->select('e.id, e.description, e.note, fc.column_name, fc.value, fc.final_value')
				->from('BillElement e')
				->leftJoin('e.FormulatedColumns fc')
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

				$element['name']                 = $element['description'];
				$element['omission']             = $omission;
				$element['addition']             = $addition;
				$element['nettAdditionOmission'] = $element['addition'] - $element['omission'];
				$element['markup_rounding_type'] = $markupSettingsInfo['rounding_type'];
				$element['has_note']             = ( $element['note'] != null && $element['note'] != '' ) ? true : false;
				$element['note']                 = (string) $element['note'];
				$element['relation_id']          = $bill->id;

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
			'relation_id'                => $bill->id,
			'markup_rounding_type'       => $billMarkupSetting->rounding_type,
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
			$billType = Doctrine_Core::getTable('BillColumnSetting')->find($request->getParameter('bill_type_id')) and
			$bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('bill_id')) AND
			$postContract = Doctrine_Core::getTable('PostContract')->findOneBy('project_structure_id', $bill->root_id)
		);

		$filterBy = $request->getParameter('opt');
		$records  = array();

		$billMarkupSetting = $bill->BillMarkupSetting;

		//We get All Element Sum Group By Element Here so that we don't have to reapeat query within element loop
		$markupSettingsInfo = array(
			'bill_markup_enabled'    => $billMarkupSetting->bill_markup_enabled,
			'bill_markup_percentage' => $billMarkupSetting->bill_markup_percentage,
			'element_markup_enabled' => $billMarkupSetting->element_markup_enabled,
			'item_markup_enabled'    => $billMarkupSetting->item_markup_enabled,
			'rounding_type'          => $billMarkupSetting->rounding_type > 0 ? $billMarkupSetting->rounding_type : BillMarkupSetting::ROUNDING_TYPE_DISABLED
		);

		// if current bill types is standard but provisional then list all the items associated with it
		if ( $bill->BillType->type == BillType::TYPE_PROVISIONAL )
		{
			$filterBy = PostContractBillItemRate::REMEASUREMENT_FILTER_BY_ALL_ITEMS;
		}

		$query = DoctrineQuery::create()
			->select('e.id, e.description, e.note, fc.column_name, fc.value, fc.final_value')
			->from('BillElement e')
			->leftJoin('e.FormulatedColumns fc')
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
				$element['name']                 = $element['description'];
				$element['omission']             = $omission;
				$element['addition']             = $addition;
				$element['nettAdditionOmission'] = $element['addition'] - $element['omission'];
				$element['markup_rounding_type'] = $markupSettingsInfo['rounding_type'];
				$element['has_note']             = ( $element['note'] != null && $element['note'] != '' ) ? true : false;
				$element['note']                 = (string) $element['note'];
				$element['relation_id']          = $bill->id;

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
			'relation_id'                => $bill->id,
			'markup_rounding_type'       => $billMarkupSetting->rounding_type,
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
			$billType = Doctrine_Core::getTable('BillColumnSetting')->find($request->getParameter('bill_type_id')) and
			$bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('bill_id')) AND
			$postContract = Doctrine_Core::getTable('PostContract')->findOneBy('project_structure_id', $bill->root_id)
		);

		$itemIds      = json_decode($request->getParameter('item_ids'), true);
		$pdo          = $bill->getTable()->getConnection()->getDbh();
		$pageNoPrefix = $bill->BillLayoutSetting->page_no_prefix;
		$roundingType = $bill->BillMarkupSetting->rounding_type;
		$records      = array();

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
					'markup_rounding_type'     => $roundingType,
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
			'markup_rounding_type'     => $roundingType,
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
			$billType = Doctrine_Core::getTable('BillColumnSetting')->find($request->getParameter('bill_type_id')) and
			$bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('bill_id')) AND
			$postContract = Doctrine_Core::getTable('PostContract')->findOneBy('project_structure_id', $bill->root_id)
		);

		$pdo          = $bill->getTable()->getConnection()->getDbh();
		$pageNoPrefix = $bill->BillLayoutSetting->page_no_prefix;
		$roundingType = $bill->BillMarkupSetting->rounding_type;
		$records      = array();
		$filterBy     = $request->getParameter('opt');

		if ( $bill->BillType->type == BillType::TYPE_PROVISIONAL )
		{
			$filterBy = PostContractBillItemRate::REMEASUREMENT_FILTER_BY_ALL_ITEMS;
		}

		list(
			$elementIds, $billItems, $remeasureClaims, $billItemTypeReferences, $billItemTypeRefFormulatedColumns
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
					'markup_rounding_type'     => $roundingType,
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
			'markup_rounding_type'     => $roundingType,
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
			$billType = Doctrine_Core::getTable('BillColumnSetting')->find($request->getParameter('bill_type_id')) and
			$bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('bill_id')) AND
			$postContract = Doctrine_Core::getTable('PostContract')->findOneBy('project_structure_id', $bill->root_id)
		);

		$itemIds      = json_decode($request->getParameter('item_ids'), true);
		$pdo          = $bill->getTable()->getConnection()->getDbh();
		$pageNoPrefix = $bill->BillLayoutSetting->page_no_prefix;
		$roundingType = $bill->BillMarkupSetting->rounding_type;
		$records      = array();

		if ( !empty( $itemIds ) )
		{
			$filterBy = $request->getParameter('opt');

			if ( $bill->BillType->type == BillType::TYPE_PROVISIONAL )
			{
				$filterBy = PostContractBillItemRate::REMEASUREMENT_FILTER_BY_ALL_ITEMS;
			}

			list(
				$elementIds, $billItems, $remeasureClaims,
				$billItemTypeReferences, $billItemTypeRefFormulatedColumns
				) = PostContractBillItemRateTable::getRemeasurementItemListWithBuildUpQtyOnly($postContract, $billType, $itemIds, $filterBy);

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
						'markup_rounding_type'     => $roundingType,
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
						$billItem['markup_rounding_type']    = $roundingType;
						$billItem['has_note']                = ( $billItem['note'] != null && $billItem['note'] != '' ) ? true : false;
						$billItem['item_total']              = Utilities::prelimRounding($itemTotal);
						$billItem['omission-qty_per_unit']   = Utilities::prelimRounding($billItem['qty_per_unit']);
						$billItem['omission-total_per_unit'] = Utilities::prelimRounding($billItem['rate'] * $billItem['qty_per_unit']);
						$billItem['omission-has_build_up']   = false;
						$billItem['addition-qty_per_unit']   = 0;
						$billItem['addition-total_per_unit'] = 0;
						$billItem['addition-has_build_up']   = false;

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

						if ( array_key_exists($billItem['post_contract_bill_item_rate_id'], $remeasureClaims) )
						{
							$costing = $remeasureClaims[$billItem['post_contract_bill_item_rate_id']];

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
			'markup_rounding_type'     => $roundingType,
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
			$bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('billId')) AND
			$postContract = Doctrine_Core::getTable('PostContract')->findOneBy('project_structure_id', $bill->root_id)
		);

		session_write_close();

		$typeIds  = json_decode($request->getParameter('selectedRows'), true);
		$filterBy = $request->getParameter('opt');
		$records  = array();

		$printingPageTitle = $request->getParameter('printingPageTitle');
		$descriptionFormat = $request->getParameter('descriptionFormat');
		$priceFormat       = $request->getParameter('priceFormat');
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
		$maxRows         = $reportGenerator->getMaxRows();
		$currency        = $reportGenerator->getCurrency();
		$withoutPrice    = false;
		$stylesheet      = $this->getBQStyling();
		$pdfGen          = $this->createNewPDFGenerator($reportGenerator);
		$pageCount       = 1;

		if ( $pages instanceof SplFixedArray )
		{
			foreach ( $pages as $page )
			{
				if ( count($page) == 0 )
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

				$layout .= $this->getPartial('reportRemeasurementByTypes', $layoutParams);

				unset( $page );

				$pdfGen->addPage($layout);

				$pageCount ++;
			}
		}

		return $pdfGen->send();
	}

	public function executePrintingSelectedElementByTypes(sfWebRequest $request)
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
		$priceFormat       = $request->getParameter('priceFormat');
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
		$stylesheet      = $this->getBQStyling();
		$pdfGen          = $this->createNewPDFGenerator($reportGenerator);
		$pageCount       = 1;

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

				$layout .= $this->getPartial('reportRemeasurementByTypes', $layoutParams);

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
			$billType = Doctrine_Core::getTable('BillColumnSetting')->find($request->getParameter('bill_type_id')) and
			$bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('billId')) AND
			$postContract = Doctrine_Core::getTable('PostContract')->findOneBy('project_structure_id', $bill->root_id)
		);

		session_write_close();

		$elementIds = json_decode($request->getParameter('selectedRows'), true);
		$records    = array();

		$printingPageTitle = $request->getParameter('printingPageTitle');
		$descriptionFormat = $request->getParameter('descriptionFormat');
		$priceFormat       = $request->getParameter('priceFormat');
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

		$reportGenerator = new sfRemeasurementElementReportGenerator($postContract, $bill, $records, $descriptionFormat);
		$pages           = $reportGenerator->generatePages();
		$maxRows         = $reportGenerator->getMaxRows();
		$currency        = $reportGenerator->getCurrency();
		$withoutPrice    = false;
		$stylesheet      = $this->getBQStyling();
		$pdfGen          = $this->createNewPDFGenerator($reportGenerator);
		$pageCount       = 1;

		if ( $pages instanceof SplFixedArray )
		{
			foreach ( $pages as $page )
			{
				if ( empty($page) )
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
					'topLeftRow2'                => "{$bill->title} > {$billType->name}",
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

				$layout .= $this->getPartial('reportRemeasurementByElements', $layoutParams);

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
			$billType = Doctrine_Core::getTable('BillColumnSetting')->find($request->getParameter('bill_type_id')) and
			$bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('billId')) AND
			$postContract = Doctrine_Core::getTable('PostContract')->findOneBy('project_structure_id', $bill->root_id)
		);

		session_write_close();

		$filterBy = $request->getParameter('opt');
		$records  = array();

		$printingPageTitle = $request->getParameter('printingPageTitle');
		$descriptionFormat = $request->getParameter('descriptionFormat');
		$priceFormat       = $request->getParameter('priceFormat');
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

		$reportGenerator = new sfRemeasurementElementReportGenerator($postContract, $bill, $records, $descriptionFormat);
		$pages           = $reportGenerator->generatePages();
		$maxRows         = $reportGenerator->getMaxRows();
		$currency        = $reportGenerator->getCurrency();
		$withoutPrice    = false;
		$stylesheet      = $this->getBQStyling();
		$pdfGen          = $this->createNewPDFGenerator($reportGenerator);
		$pageCount       = 1;

		if ( $pages instanceof SplFixedArray )
		{
			foreach ( $pages as $page )
			{
				if ( count($page) == 0 )
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
					'topLeftRow2'                => "{$bill->title} > {$billType->name}",
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

				$layout .= $this->getPartial('reportRemeasurementByElements', $layoutParams);

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
		$priceFormat       = $request->getParameter('priceFormat');
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

		$reportGenerator = new sfRemeasurementItemReportGenerator($postContract, $bill, $descriptionFormat);
		$reportGenerator->setAffectedElements($elements);
		$reportGenerator->setItems($records);

		unset( $elements, $records );

		$elementPages = $reportGenerator->generatePages();
		$maxRows      = $reportGenerator->getMaxRows();
		$currency     = $reportGenerator->getCurrency();
		$withoutPrice = false;
		$stylesheet   = $this->getBQStyling();
		$pdfGen       = $this->createNewPDFGenerator($reportGenerator);
		$pageCount    = 1;

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
						'topLeftRow2'                => $bill->title . ' > ' . $billType->name,
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

					$layout .= $this->getPartial('reportRemeasurementByItems', $billItemsLayoutParams);

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
		$priceFormat       = $request->getParameter('priceFormat');
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

		$elementPages = $reportGenerator->generatePages();
		$maxRows      = $reportGenerator->getMaxRows();
		$currency     = $reportGenerator->getCurrency();
		$withoutPrice = false;
		$stylesheet   = $this->getBQStyling();
		$pdfGen       = $this->createNewPDFGenerator($reportGenerator);
		$pageCount    = 1;

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
						'topLeftRow2'                => $bill->title . ' > ' . $billType->name,
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

					$layout .= $this->getPartial('reportRemeasurementByItems', $billItemsLayoutParams);

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
		$priceFormat       = $request->getParameter('priceFormat');
		$printNoCents      = ( $request->getParameter('printNoCents') == 't' ) ? true : false;
		$filterBy          = $request->getParameter('opt');
		$withoutPrice      = false;
		$stylesheet        = $this->getBQStyling();

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

		$pdfGen = $this->createNewPDFGenerator($reportPrintGenerator);

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

			$maxRows = $reportPrintGenerator->getMaxRows();

			foreach ( $typesArray as $type )
			{
				$typeName                   = ucfirst($type);
				$billItemId                 = ( $type == PostContractRemeasurementBuildUpQuantityItem::OMISSION_TYPE_TEXT ) ? $billItem['id'] : $billItem['post_contract_bill_item_rate_id'];
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

					$layout .= $this->getPartial('buildUpQtyReport', $billItemsLayoutParams);

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
	// ======================================================================================================================================

}