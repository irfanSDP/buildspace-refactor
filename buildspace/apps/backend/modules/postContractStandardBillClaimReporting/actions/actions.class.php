<?php

/**
 * postContractStandardBillClaimReporting actions.
 *
 * @package    buildspace
 * @subpackage postContractStandardBillClaimReporting
 * @author     1337 developers
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class postContractStandardBillClaimReportingActions extends BaseActions {

	public function executeGetAffectedElementsAndItems(sfWebRequest $request)
	{
		$this->forward404Unless(
			$request->isXmlHttpRequest() and
			$bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('id')) and
			$project = Doctrine_Core::getTable('ProjectStructure')->find($bill->root_id)
		);

		$typeIds = json_decode($request->getParameter('type_ids'), true);
		$records = array();

		// create row for non instantiate PostContractStandardClaimTypeReference
		if ( !empty( $typeIds ) )
		{
			$collection = new Doctrine_Collection('PostContractStandardClaimTypeReference');

			foreach ( $typeIds as $typeId )
			{
				$typeIdExploded = explode('-', $typeId);

				if ( is_numeric($typeIdExploded[0]) AND is_numeric($typeIdExploded[1]) )
				{
					$typeItem = DoctrineQuery::create()
						->select('t.id')
						->from('PostContractStandardClaimTypeReference t')
						->where('t.post_contract_id = ? AND t.bill_column_setting_id = ? AND t.counter = ?', array( $project->PostContract->id, $typeIdExploded[0], $typeIdExploded[1] ))
						->fetchOne();

					if ( !$typeItem )
					{
						$typeItem                         = new PostContractStandardClaimTypeReference();
						$typeItem->post_contract_id       = $project->PostContract->id;
						$typeItem->bill_column_setting_id = $typeIdExploded[0];
						$typeItem->counter                = $typeIdExploded[1];

						$collection->add($typeItem);
					}
				}
			}

			// batch saving
			if ( $collection->count() > 0 )
			{
				$collection->save();
			}
		}

		if ( !empty( $typeIds ) )
		{
			// Get affected element and items
			$records = BillElementTable::getAffectedElementsAndItemsByBillIdGroupByTypes($bill->id, $typeIds);
		}

		return $this->renderJson($records);
	}

	public function executeGetAffectedTypesAndElements(sfWebRequest $request)
	{
		$this->forward404Unless(
			$request->isXmlHttpRequest() AND
			$bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('bill_id'))
		);

		$typeId  = $request->getParameter('typeId');
		$itemIds = $request->getParameter('itemIds');

		return $this->renderJson(BillElementTable::getAffectedElementsAndTypeByItemIds($itemIds, $typeId));
	}

	public function executeGetAffectedTypesAndItems(sfWebRequest $request)
	{
		$this->forward404Unless(
			$request->isXmlHttpRequest() AND
			$bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('bill_id'))
		);

		$typeId     = $request->getParameter('typeId');
		$elementIds = $request->getParameter('element_ids');

		return $this->renderJson(BillElementTable::getAffectedItemsAndTypesByElementId($bill, $elementIds, $typeId));
	}

	// ============================================================================================================================================
	// Printing Preview
	// ============================================================================================================================================
	public function executeGetPrintingSelectedElementClaims(sfWebRequest $request)
	{
		$this->forward404Unless(
			$request->isXmlHttpRequest() AND
			$bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('bill_id')) AND
			$project = Doctrine_Core::getTable('ProjectStructure')->find($bill->root_id) AND
			$typeItem = Doctrine_Core::getTable('PostContractStandardClaimTypeReference')->find($request->getParameter('type_ref_id'))
		);

		$elementIds   = json_decode($request->getParameter('itemIds'), true);
		$elements     = array();
		$postContract = $project->PostContract;
		$revision     = PostContractClaimRevisionTable::getCurrentSelectedProjectRevision($project->PostContract);

		if ( count($elementIds) > 0 )
		{
			$elements = DoctrineQuery::create()
				->select('e.id, e.description, e.note')
				->from('BillElement e')
				->where('e.project_structure_id = ?', $bill->id)
				->andWhereIn('e.id', $elementIds)
				->addOrderBy('e.priority ASC')
				->fetchArray();

			$elementGrandTotals = PostContractTable::getTotalClaimRateGroupByElement($bill->id, $typeItem, $revision, $postContract->id);

			foreach ( $elements as $key => $element )
			{
				$elementId = $element['id'];

				if ( array_key_exists($elementId, $elementGrandTotals) )
				{
					$prevAmount     = $elementGrandTotals[$elementId][0]['prev_amount'];
					$currentAmount  = $elementGrandTotals[$elementId][0]['current_amount'];
					$totalPerUnit   = $elementGrandTotals[$elementId][0]['total_per_unit'];
					$upToDateAmount = $elementGrandTotals[$elementId][0]['up_to_date_amount'];

					$elements[$key]['total_per_unit']        = $totalPerUnit;
					$elements[$key]['prev_percentage']       = ( $totalPerUnit > 0 ) ? number_format(( $prevAmount / $totalPerUnit ) * 100, 2, '.', '') : 0;
					$elements[$key]['prev_amount']           = $prevAmount;
					$elements[$key]['current_percentage']    = ( $totalPerUnit > 0 ) ? number_format(( $currentAmount / $totalPerUnit ) * 100, 2, '.', '') : 0;
					$elements[$key]['current_amount']        = $currentAmount;
					$elements[$key]['up_to_date_percentage'] = ( $totalPerUnit > 0 ) ? number_format(( $upToDateAmount / $totalPerUnit ) * 100, 2, '.', '') : 0;
					$elements[$key]['up_to_date_amount']     = $upToDateAmount;
					$elements[$key]['up_to_date_qty']        = $elementGrandTotals[$elementId][0]['up_to_date_qty'];
				}

				$elements[$key]['has_note']          = ( $element['note'] != null && $element['note'] != '' ) ? true : false;
				$elements[$key]['claim_type_ref_id'] = $typeItem->id;
				$elements[$key]['relation_id']       = $bill->id;
			}
		}

		$defaultLastRow = array(
			'id'                    => Constants::GRID_LAST_ROW,
			'description'           => '',
			'total_per_unit'        => 0,
			'prev_percentage'       => 0,
			'prev_amount'           => 0,
			'current_percentage'    => 0,
			'current_amount'        => 0,
			'up_to_date_percentage' => 0,
			'up_to_date_amount'     => 0,
			'up_to_date_qty'        => 0,
			'claim_type_ref_id'     => - 1,
			'relation_id'           => $bill->id,
		);

		array_push($elements, $defaultLastRow);

		return $this->renderJson(array(
			'identifier' => 'id',
			'items'      => $elements
		));
	}

	public function executeGetPrintingElementWorkDoneOnlyClaims(sfWebRequest $request)
	{
		$this->forward404Unless(
			$request->isXmlHttpRequest() AND
			$bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('bill_id')) AND
			$project = Doctrine_Core::getTable('ProjectStructure')->find($bill->root_id) AND
			$typeItem = Doctrine_Core::getTable('PostContractStandardClaimTypeReference')->find($request->getParameter('type_ref_id'))
		);

		$items        = array();
		$postContract = $project->PostContract;
		$revision     = PostContractClaimRevisionTable::getCurrentSelectedProjectRevision($project->PostContract);

		$elements = DoctrineQuery::create()
			->select('e.id, e.description, e.note')
			->from('BillElement e')
			->where('e.project_structure_id = ?', $bill->id)
			->addOrderBy('e.priority ASC')
			->fetchArray();

		$elementGrandTotals = PostContractTable::getTotalClaimRateGroupByElement($bill->id, $typeItem, $revision, $postContract->id);

		foreach ( $elements as $key => $element )
		{
			$elementId                               = $element['id'];
			$elements[$key]['up_to_date_percentage'] = 0;
			$elements[$key]['up_to_date_amount']     = 0;
			$elements[$key]['up_to_date_qty']        = 0;

			if ( array_key_exists($elementId, $elementGrandTotals) )
			{
				$totalPerUnit   = $elementGrandTotals[$elementId][0]['total_per_unit'];
				$upToDateAmount = $elementGrandTotals[$elementId][0]['up_to_date_amount'];

				$elements[$key]['total_per_unit']        = $totalPerUnit;
				$elements[$key]['up_to_date_percentage'] = ( $totalPerUnit > 0 ) ? number_format(( $upToDateAmount / $totalPerUnit ) * 100, 2, '.', '') : 0;
				$elements[$key]['up_to_date_amount']     = $upToDateAmount;
				$elements[$key]['up_to_date_qty']        = $elementGrandTotals[$elementId][0]['up_to_date_qty'];
			}

			if ( $elements[$key]['up_to_date_amount'] > 0 )
			{
				$items[] = $elements[$key];
			}

			$elements[$key]['has_note']          = ( $element['note'] != null && $element['note'] != '' ) ? true : false;
			$elements[$key]['claim_type_ref_id'] = $typeItem->id;
			$elements[$key]['relation_id']       = $bill->id;
		}

		unset( $elements );

		$defaultLastRow = array(
			'id'                    => Constants::GRID_LAST_ROW,
			'description'           => '',
			'total_per_unit'        => 0,
			'prev_percentage'       => 0,
			'prev_amount'           => 0,
			'current_percentage'    => 0,
			'current_amount'        => 0,
			'up_to_date_percentage' => 0,
			'up_to_date_amount'     => 0,
			'up_to_date_qty'        => 0,
			'claim_type_ref_id'     => - 1,
			'relation_id'           => $bill->id,
		);

		array_push($items, $defaultLastRow);

		return $this->renderJson(array(
			'identifier' => 'id',
			'items'      => $items
		));
	}

	public function executeGetPrintingSelectedItemClaims(sfWebRequest $request)
	{
		$this->forward404Unless(
			$request->isXmlHttpRequest() AND
			$bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('bill_id')) AND
			$project = Doctrine_Core::getTable('ProjectStructure')->find($bill->root_id) AND
			$typeRef = Doctrine_Core::getTable('PostContractStandardClaimTypeReference')->find($request->getParameter('type_ref_id'))
		);

		$items        = array();
		$pageNoPrefix = $bill->BillLayoutSetting->page_no_prefix;
		$revision     = PostContractClaimRevisionTable::getCurrentSelectedProjectRevision($project->PostContract);
		$itemIds      = $request->getParameter('itemIds');

		$affectedElements = BillElementTable::getAffectedElementIdsByItemIds($itemIds);

		foreach ( $affectedElements as $elementId => $affectedElement )
		{
			$items[] = array(
				'id'          => 'element-' . $elementId,
				'bill_ref'    => null,
				'description' => $affectedElement['description'],
				'type'        => 0,
			);

			$element     = new BillElement();
			$element->id = $elementId;

			list(
				$billItems
				) = BillItemTable::getDataStructureForStandardClaimBillItemListFilteredByItemIds($element, $bill, $revision, $project->PostContract->id, $typeRef, $itemIds);

			foreach ( $billItems as $billItem )
			{
				$billItem['bill_ref']                  = BillItemTable::generateBillRef($pageNoPrefix, $billItem['bill_ref_element_no'], $billItem['bill_ref_page_no'], $billItem['bill_ref_char']);
				$billItem['type']                      = (string) $billItem['type'];
				$billItem['uom_id']                    = $billItem['uom_id'] > 0 ? (string) $billItem['uom_id'] : '-1';
				$billItem['relation_id']               = $element->id;
				$billItem['qty_per_unit-has_build_up'] = $billItem['has_build_up'];
				$billItem['has_note']                  = ( $billItem['note'] != null && $billItem['note'] != '' ) ? true : false;
				$billItem['include']                   = ( $billItem['include'] ) ? 1 : 0;

				unset( $billItem['has_build_up'] );

				array_push($items, $billItem);

				unset( $billItem );
			}

			unset( $billItems, $element );
		}

		$defaultLastRow = array(
			'id'                    => Constants::GRID_LAST_ROW,
			'bill_ref'              => '',
			'description'           => '',
			'type'                  => (string) ProjectStructure::getDefaultItemType($bill->BillType->type),
			'uom_id'                => '-1',
			'uom_symbol'            => '',
			'level'                 => 0,
			'qty_per_unit'          => 0,
			'total_per_unit'        => 0,
			'prev_percentage'       => 0,
			'prev_amount'           => 0,
			'current_percentage'    => 0,
			'current_amount'        => 0,
			'up_to_date_percentage' => 0,
			'up_to_date_amount'     => 0,
			'up_to_date_qty'        => 0,
			'include'               => 1,
		);

		array_push($items, $defaultLastRow);

		return $this->renderJson(array(
			'identifier' => 'id',
			'items'      => $items
		));
	}

	public function executeGetPrintingPreviewItemsWithCurrentClaim(sfWebRequest $request)
	{
		$this->forward404Unless(
			$request->isXmlHttpRequest() AND
			$bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('bill_id')) AND
			$project = Doctrine_Core::getTable('ProjectStructure')->find($bill->root_id) AND
			$typeRef = Doctrine_Core::getTable('PostContractStandardClaimTypeReference')->find($request->getParameter('type_ref_id'))
		);

		$items        = array();
		$pageNoPrefix = $bill->BillLayoutSetting->page_no_prefix;
		$revision     = PostContractClaimRevisionTable::getCurrentSelectedProjectRevision($project->PostContract);

		$affectedElements = DoctrineQuery::create()
			->select('e.id, e.description, e.note')
			->from('BillElement e')
			->where('e.project_structure_id = ?', $bill->id)
			->addOrderBy('e.priority ASC')
			->fetchArray();

		foreach ( $affectedElements as $affectedElement )
		{
			$addedTopElementHeader = false;
			$elementId             = $affectedElement['id'];

			$element     = new BillElement();
			$element->id = $elementId;

			list(
				$billItems
				) = BillItemTable::getDataForPrintingPreviewItemsByColumn($element, $bill, $revision, $project->PostContract->id, $typeRef, 'current_amount');

			foreach ( $billItems as $billItem )
			{
				if ( !$addedTopElementHeader )
				{
					$items[] = array(
						'id'          => 'element-' . $elementId,
						'bill_ref'    => null,
						'description' => $affectedElement['description'],
						'type'        => 0,
					);

					$addedTopElementHeader = true;
				}

				$billItem['bill_ref']                  = BillItemTable::generateBillRef($pageNoPrefix, $billItem['bill_ref_element_no'], $billItem['bill_ref_page_no'], $billItem['bill_ref_char']);
				$billItem['type']                      = (string) $billItem['type'];
				$billItem['uom_id']                    = $billItem['uom_id'] > 0 ? (string) $billItem['uom_id'] : '-1';
				$billItem['relation_id']               = $element->id;
				$billItem['qty_per_unit-has_build_up'] = $billItem['has_build_up'];
				$billItem['has_note']                  = ( $billItem['note'] != null && $billItem['note'] != '' ) ? true : false;
				$billItem['include']                   = ( $billItem['include'] ) ? 1 : 0;

				unset( $billItem['has_build_up'] );

				array_push($items, $billItem);

				unset( $billItem );
			}

			unset( $billItems, $element );
		}

		$defaultLastRow = array(
			'id'                    => Constants::GRID_LAST_ROW,
			'bill_ref'              => '',
			'description'           => '',
			'type'                  => (string) ProjectStructure::getDefaultItemType($bill->BillType->type),
			'uom_id'                => '-1',
			'uom_symbol'            => '',
			'level'                 => 0,
			'qty_per_unit'          => 0,
			'total_per_unit'        => 0,
			'prev_percentage'       => 0,
			'prev_amount'           => 0,
			'current_percentage'    => 0,
			'current_amount'        => 0,
			'up_to_date_percentage' => 0,
			'up_to_date_amount'     => 0,
			'up_to_date_qty'        => 0,
			'include'               => 1,
		);

		array_push($items, $defaultLastRow);

		return $this->renderJson(array(
			'identifier' => 'id',
			'items'      => $items
		));
	}

	public function executeGetPrintingPreviewItemsWithClaim(sfWebRequest $request)
	{
		$this->forward404Unless(
			$request->isXmlHttpRequest() AND
			$bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('bill_id')) AND
			$project = Doctrine_Core::getTable('ProjectStructure')->find($bill->root_id) AND
			$typeRef = Doctrine_Core::getTable('PostContractStandardClaimTypeReference')->find($request->getParameter('type_ref_id'))
		);

		$items        = array();
		$pageNoPrefix = $bill->BillLayoutSetting->page_no_prefix;
		$revision     = PostContractClaimRevisionTable::getCurrentSelectedProjectRevision($project->PostContract);

		$affectedElements = DoctrineQuery::create()
			->select('e.id, e.description, e.note')
			->from('BillElement e')
			->where('e.project_structure_id = ?', $bill->id)
			->addOrderBy('e.priority ASC')
			->fetchArray();

		foreach ( $affectedElements as $affectedElement )
		{
			$addedTopElementHeader = false;
			$elementId             = $affectedElement['id'];

			$element     = new BillElement();
			$element->id = $elementId;

			list(
				$billItems
				) = BillItemTable::getDataForPrintingPreviewItemsByColumn($element, $bill, $revision, $project->PostContract->id, $typeRef, 'up_to_date_amount');

			foreach ( $billItems as $billItem )
			{
				if ( !$addedTopElementHeader )
				{
					$items[] = array(
						'id'          => 'element-' . $elementId,
						'bill_ref'    => null,
						'description' => $affectedElement['description'],
						'type'        => 0,
					);

					$addedTopElementHeader = true;
				}

				$billItem['bill_ref']                  = BillItemTable::generateBillRef($pageNoPrefix, $billItem['bill_ref_element_no'], $billItem['bill_ref_page_no'], $billItem['bill_ref_char']);
				$billItem['type']                      = (string) $billItem['type'];
				$billItem['uom_id']                    = $billItem['uom_id'] > 0 ? (string) $billItem['uom_id'] : '-1';
				$billItem['relation_id']               = $element->id;
				$billItem['qty_per_unit-has_build_up'] = $billItem['has_build_up'];
				$billItem['has_note']                  = ( $billItem['note'] != null && $billItem['note'] != '' ) ? true : false;
				$billItem['include']                   = ( $billItem['include'] ) ? 1 : 0;

				unset( $billItem['has_build_up'] );

				array_push($items, $billItem);

				unset( $billItem );
			}

			unset( $billItems, $element );
		}

		$defaultLastRow = array(
			'id'                    => Constants::GRID_LAST_ROW,
			'bill_ref'              => '',
			'description'           => '',
			'type'                  => (string) ProjectStructure::getDefaultItemType($bill->BillType->type),
			'uom_id'                => '-1',
			'uom_symbol'            => '',
			'level'                 => 0,
			'qty_per_unit'          => 0,
			'total_per_unit'        => 0,
			'prev_percentage'       => 0,
			'prev_amount'           => 0,
			'current_percentage'    => 0,
			'current_amount'        => 0,
			'up_to_date_percentage' => 0,
			'up_to_date_amount'     => 0,
			'up_to_date_qty'        => 0,
			'include'               => 1,
		);

		array_push($items, $defaultLastRow);

		return $this->renderJson(array(
			'identifier' => 'id',
			'items'      => $items
		));
	}

	public function executeGetPrintingPreviewItemsWorkDoneWithQty(sfWebRequest $request)
	{
		$this->forward404Unless(
			$request->isXmlHttpRequest() AND
			$bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('bill_id')) AND
			$project = Doctrine_Core::getTable('ProjectStructure')->find($bill->root_id) AND
			$typeRef = Doctrine_Core::getTable('PostContractStandardClaimTypeReference')->find($request->getParameter('type_ref_id'))
		);

		$items        = array();
		$pageNoPrefix = $bill->BillLayoutSetting->page_no_prefix;
		$revision     = PostContractClaimRevisionTable::getCurrentSelectedProjectRevision($project->PostContract);

		$affectedElements = DoctrineQuery::create()
			->select('e.id, e.description, e.note')
			->from('BillElement e')
			->where('e.project_structure_id = ?', $bill->id)
			->addOrderBy('e.priority ASC')
			->fetchArray();

		foreach ( $affectedElements as $affectedElement )
		{
			$addedTopElementHeader = false;
			$elementId             = $affectedElement['id'];

			$element     = new BillElement();
			$element->id = $elementId;

			list(
				$billItems
				) = BillItemTable::getDataForPrintingPreviewItemsByColumn($element, $bill, $revision, $project->PostContract->id, $typeRef, 'up_to_date_amount');

			foreach ( $billItems as $billItem )
			{
				if ( !$addedTopElementHeader )
				{
					$items[] = array(
						'id'          => 'element-' . $elementId,
						'bill_ref'    => null,
						'description' => $affectedElement['description'],
						'type'        => 0,
					);

					$addedTopElementHeader = true;
				}

				$billItem['bill_ref']                  = BillItemTable::generateBillRef($pageNoPrefix, $billItem['bill_ref_element_no'], $billItem['bill_ref_page_no'], $billItem['bill_ref_char']);
				$billItem['type']                      = (string) $billItem['type'];
				$billItem['uom_id']                    = $billItem['uom_id'] > 0 ? (string) $billItem['uom_id'] : '-1';
				$billItem['relation_id']               = $element->id;
				$billItem['qty_per_unit-has_build_up'] = $billItem['has_build_up'];
				$billItem['has_note']                  = ( $billItem['note'] != null && $billItem['note'] != '' ) ? true : false;
				$billItem['include']                   = ( $billItem['include'] ) ? 1 : 0;

				unset( $billItem['has_build_up'] );

				array_push($items, $billItem);

				unset( $billItem );
			}

			unset( $billItems, $element );
		}

		$defaultLastRow = array(
			'id'                    => Constants::GRID_LAST_ROW,
			'bill_ref'              => '',
			'description'           => '',
			'type'                  => (string) ProjectStructure::getDefaultItemType($bill->BillType->type),
			'uom_id'                => '-1',
			'uom_symbol'            => '',
			'level'                 => 0,
			'qty_per_unit'          => 0,
			'total_per_unit'        => 0,
			'prev_percentage'       => 0,
			'prev_amount'           => 0,
			'current_percentage'    => 0,
			'current_amount'        => 0,
			'up_to_date_percentage' => 0,
			'up_to_date_amount'     => 0,
			'up_to_date_qty'        => 0,
			'include'               => 1,
		);

		array_push($items, $defaultLastRow);

		return $this->renderJson(array(
			'identifier' => 'id',
			'items'      => $items
		));
	}

	public function executeGetPrintingPreviewItemsWorkDoneWithPercentage(sfWebRequest $request)
	{
		$this->forward404Unless(
			$request->isXmlHttpRequest() AND
			$bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('bill_id')) AND
			$project = Doctrine_Core::getTable('ProjectStructure')->find($bill->root_id) AND
			$typeRef = Doctrine_Core::getTable('PostContractStandardClaimTypeReference')->find($request->getParameter('type_ref_id'))
		);

		$items        = array();
		$pageNoPrefix = $bill->BillLayoutSetting->page_no_prefix;
		$revision     = PostContractClaimRevisionTable::getCurrentSelectedProjectRevision($project->PostContract);

		$affectedElements = DoctrineQuery::create()
			->select('e.id, e.description, e.note')
			->from('BillElement e')
			->where('e.project_structure_id = ?', $bill->id)
			->addOrderBy('e.priority ASC')
			->fetchArray();

		foreach ( $affectedElements as $affectedElement )
		{
			$addedTopElementHeader = false;
			$elementId             = $affectedElement['id'];

			$element     = new BillElement();
			$element->id = $elementId;

			list(
				$billItems
				) = BillItemTable::getDataForPrintingPreviewItemsByColumn($element, $bill, $revision, $project->PostContract->id, $typeRef, 'up_to_date_amount');

			foreach ( $billItems as $billItem )
			{
				if ( !$addedTopElementHeader )
				{
					$items[] = array(
						'id'          => 'element-' . $elementId,
						'bill_ref'    => null,
						'description' => $affectedElement['description'],
						'type'        => 0,
					);

					$addedTopElementHeader = true;
				}

				$billItem['bill_ref']                  = BillItemTable::generateBillRef($pageNoPrefix, $billItem['bill_ref_element_no'], $billItem['bill_ref_page_no'], $billItem['bill_ref_char']);
				$billItem['type']                      = (string) $billItem['type'];
				$billItem['uom_id']                    = $billItem['uom_id'] > 0 ? (string) $billItem['uom_id'] : '-1';
				$billItem['relation_id']               = $element->id;
				$billItem['qty_per_unit-has_build_up'] = $billItem['has_build_up'];
				$billItem['has_note']                  = ( $billItem['note'] != null && $billItem['note'] != '' ) ? true : false;
				$billItem['include']                   = ( $billItem['include'] ) ? 1 : 0;

				unset( $billItem['has_build_up'] );

				array_push($items, $billItem);

				unset( $billItem );
			}

			unset( $billItems, $element );
		}

		$defaultLastRow = array(
			'id'                    => Constants::GRID_LAST_ROW,
			'bill_ref'              => '',
			'description'           => '',
			'type'                  => (string) ProjectStructure::getDefaultItemType($bill->BillType->type),
			'uom_id'                => '-1',
			'uom_symbol'            => '',
			'level'                 => 0,
			'qty_per_unit'          => 0,
			'total_per_unit'        => 0,
			'prev_percentage'       => 0,
			'prev_amount'           => 0,
			'current_percentage'    => 0,
			'current_amount'        => 0,
			'up_to_date_percentage' => 0,
			'up_to_date_amount'     => 0,
			'up_to_date_qty'        => 0,
			'include'               => 1,
		);

		array_push($items, $defaultLastRow);

		return $this->renderJson(array(
			'identifier' => 'id',
			'items'      => $items
		));
	}
	// ============================================================================================================================================
}