<?php

/**
 * scheduleOfRateProjectAnalyzerReport actions.
 *
 * @package    buildspace
 * @subpackage scheduleOfRateProjectAnalyzerReport
 * @author     1337 developers
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class scheduleOfRateProjectAnalyzerReportActions extends BaseActions {

	public function executeGetAffectedSelectionTradeItemsAndBillItems(sfWebRequest $request)
	{
		$this->forward404Unless(
			$request->isXmlHttpRequest() and
			$project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('pid'))
		);

		$tradeIds = json_decode($request->getParameter('trade_ids'), true);
		$data     = array();
		$pdo      = $project->getTable()->getConnection()->getDbh();

		if ( !empty( $tradeIds ) )
		{
			$stmt = $pdo->prepare("SELECT DISTINCT i.id, i.trade_id FROM " . ScheduleOfRateItemTable::getInstance()->getTableName() . " AS i JOIN
			" . ScheduleOfRateItemFormulatedColumnTable::getInstance()->getTableName() . " AS ifc ON i.id = ifc.relation_id JOIN
			" . BillItemFormulatedColumnTable::getInstance()->getTableName() . " AS bifc ON ifc.id = bifc.schedule_of_rate_item_formulated_column_id JOIN
			" . BillItemTable::getInstance()->getTableName() . " AS bi ON bifc.relation_id = bi.id JOIN
			" . BillElementTable::getInstance()->getTableName() . " AS be ON bi.element_id = be.id JOIN
			" . ProjectStructureTable::getInstance()->getTableName() . " AS s ON be.project_structure_id = s.id
			WHERE s.root_id = " . $project->id . " AND i.trade_id IN (" . implode(', ', $tradeIds) . ")
			AND bifc.column_name = '" . BillItem::FORMULATED_COLUMN_RATE . "'
			AND i.deleted_at IS NULL AND ifc.deleted_at IS NULL AND bifc.deleted_at IS NULL AND bi.project_revision_deleted_at IS NULL
			AND bi.deleted_at IS NULL AND be.deleted_at IS NULL AND s.deleted_at IS NULL");

			$stmt->execute(array());

			$scheduleOfRateItemIds = $stmt->fetchAll(PDO::FETCH_ASSOC);

			if ( empty( $scheduleOfRateItemIds ) )
			{
				foreach ( $tradeIds as $tradeId )
				{
					$data[$tradeId] = array();
				}
			}
			else
			{
				$sorItemIds = array();

				foreach ( $scheduleOfRateItemIds as $scheduleOfRateItemId )
				{
					$data[$scheduleOfRateItemId['trade_id']][$scheduleOfRateItemId['id']] = array();

					$sorItemIds[] = $scheduleOfRateItemId['id'];
				}

				$billItems = ScheduleOfRateItemTable::getAffectedBillItemsByScheduleOfRateItemIds($project, $sorItemIds);

				// only append bill item id if there is record associated with SoR's Item ID
				foreach ( $billItems as $billItem )
				{
					foreach ( $scheduleOfRateItemIds as $scheduleOfRateItemId )
					{
						if ( $billItem['sor_item_id'] == $scheduleOfRateItemId['id'] )
						{
							$data[$scheduleOfRateItemId['trade_id']][$scheduleOfRateItemId['id']][] = $billItem['id'];
						}
					}
				}

				unset( $billItems, $scheduleOfRateItemIds );
			}
		}

		return $this->renderJson($data);
	}

	public function executeGetAffectedSelectionTradeAndBillItems(sfWebRequest $request)
	{
		$this->forward404Unless(
			$request->isXmlHttpRequest() and
			$project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('pid'))
		);

		$tradeItemIds = json_decode($request->getParameter('trade_item_ids'), true);
		$data         = array();
		$pdo          = $project->getTable()->getConnection()->getDbh();

		if ( !empty( $tradeItemIds ) )
		{
			$stmt = $pdo->prepare("SELECT DISTINCT i.id, i.trade_id FROM " . ScheduleOfRateItemTable::getInstance()->getTableName() . " AS i JOIN
			" . ScheduleOfRateItemFormulatedColumnTable::getInstance()->getTableName() . " AS ifc ON i.id = ifc.relation_id JOIN
			" . BillItemFormulatedColumnTable::getInstance()->getTableName() . " AS bifc ON ifc.id = bifc.schedule_of_rate_item_formulated_column_id JOIN
			" . BillItemTable::getInstance()->getTableName() . " AS bi ON bifc.relation_id = bi.id JOIN
			" . BillElementTable::getInstance()->getTableName() . " AS be ON bi.element_id = be.id JOIN
			" . ProjectStructureTable::getInstance()->getTableName() . " AS s ON be.project_structure_id = s.id
			WHERE s.root_id = " . $project->id . " AND i.id IN (" . implode(', ', $tradeItemIds) . ")
			AND bifc.column_name = '" . BillItem::FORMULATED_COLUMN_RATE . "'
			AND i.deleted_at IS NULL AND ifc.deleted_at IS NULL AND bifc.deleted_at IS NULL AND bi.project_revision_deleted_at IS NULL
			AND bi.deleted_at IS NULL AND be.deleted_at IS NULL AND s.deleted_at IS NULL");

			$stmt->execute(array());

			$scheduleOfRateItemIds = $stmt->fetchAll(PDO::FETCH_ASSOC);

			if ( empty( $scheduleOfRateItemIds ) )
			{
				$data = array();
			}
			else
			{
				$sorItemIds = array();

				foreach ( $scheduleOfRateItemIds as $scheduleOfRateItemId )
				{
					$data[$scheduleOfRateItemId['trade_id']][$scheduleOfRateItemId['id']] = array();

					$sorItemIds[] = $scheduleOfRateItemId['id'];
				}

				$billItems = ScheduleOfRateItemTable::getAffectedBillItemsByScheduleOfRateItemIds($project, $sorItemIds);

				// only append bill item id if there is record associated with SoR's Item ID
				foreach ( $billItems as $billItem )
				{
					foreach ( $scheduleOfRateItemIds as $scheduleOfRateItemId )
					{
						if ( $billItem['sor_item_id'] == $scheduleOfRateItemId['id'] )
						{
							$data[$scheduleOfRateItemId['trade_id']][$scheduleOfRateItemId['id']][] = $billItem['id'];
						}
					}
				}

				unset( $billItems, $scheduleOfRateItemIds );
			}
		}

		return $this->renderJson($data);
	}

	public function executeGetAffectedSelectionTradeAndTradeItems(sfWebRequest $request)
	{
		$this->forward404Unless(
			$request->isXmlHttpRequest() AND
			$project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('pid')) AND
			$trade = Doctrine_Core::getTable('ScheduleOfRateTrade')->find($request->getParameter('trade_id'))
		);

		$data         = array();
		$tradeItemIds = array();
		$billItemIds  = json_decode($request->getParameter('bill_item_ids'), true);
		$pdo          = $project->getTable()->getConnection()->getDbh();

		if ( !empty( $billItemIds ) )
		{
			$billItems = ScheduleOfRateItemTable::getAffectedScheduleOfRateBillItemsByBillItemIds($project, $billItemIds);

			if ( !empty( $billItems ) )
			{
				foreach ( $billItems as $tradeItem )
				{
					$tradeItemIds[] = $tradeItem['sor_item_id'];
				}

				$stmt = $pdo->prepare("SELECT DISTINCT i.id, i.trade_id FROM " . ScheduleOfRateItemTable::getInstance()->getTableName() . " AS i JOIN
				" . ScheduleOfRateItemFormulatedColumnTable::getInstance()->getTableName() . " AS ifc ON i.id = ifc.relation_id JOIN
				" . BillItemFormulatedColumnTable::getInstance()->getTableName() . " AS bifc ON ifc.id = bifc.schedule_of_rate_item_formulated_column_id JOIN
				" . BillItemTable::getInstance()->getTableName() . " AS bi ON bifc.relation_id = bi.id JOIN
				" . BillElementTable::getInstance()->getTableName() . " AS be ON bi.element_id = be.id JOIN
				" . ProjectStructureTable::getInstance()->getTableName() . " AS s ON be.project_structure_id = s.id
				WHERE s.root_id = " . $project->id . " AND i.id IN (" . implode(', ', $tradeItemIds) . ")
				AND bifc.column_name = '" . BillItem::FORMULATED_COLUMN_RATE . "'
				AND i.deleted_at IS NULL AND ifc.deleted_at IS NULL AND bifc.deleted_at IS NULL AND bi.project_revision_deleted_at IS NULL
				AND bi.deleted_at IS NULL AND be.deleted_at IS NULL AND s.deleted_at IS NULL");

				$stmt->execute(array());

				$scheduleOfRateItemIds = $stmt->fetchAll(PDO::FETCH_ASSOC);

				foreach ( $scheduleOfRateItemIds as $scheduleOfRateItemId )
				{
					$data[$scheduleOfRateItemId['trade_id']][$scheduleOfRateItemId['id']] = array();
				}

				// only append bill item id if there is record associated with SoR's Item ID
				foreach ( $billItems as $billItem )
				{
					foreach ( $scheduleOfRateItemIds as $scheduleOfRateItemId )
					{
						if ( $billItem['sor_item_id'] == $scheduleOfRateItemId['id'] )
						{
							$data[$scheduleOfRateItemId['trade_id']][$scheduleOfRateItemId['id']][] = $billItem['id'];
						}
					}
				}

				unset( $billItems, $scheduleOfRateItemIds );
			}
		}

		return $this->renderJson($data);
	}

	public function executeGetPrintingSelectedTradeItems(sfWebRequest $request)
	{
		$this->forward404Unless(
			$request->isXmlHttpRequest() AND
			$project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('pid')) AND
			$scheduleOfRate = Doctrine_Core::getTable('ScheduleOfRate')->find($request->getParameter('scheduleOfRateId'))
		);

		$pdo              = $project->getTable()->getConnection()->getDbh();
		$tradeItemIds     = json_decode($request->getParameter('trade_item_ids'), true);
		$tradeIds         = array();
		$itemIds          = array();
		$items            = array();
		$sumTotalQuantity = 0;
		$sumTotalCost     = 0;

		// get trade item first, then get trade level
		if ( !empty( $tradeItemIds ) )
		{
			$stmt = $pdo->prepare("SELECT DISTINCT i.id, i.trade_id FROM " . ScheduleOfRateItemTable::getInstance()->getTableName() . " AS i JOIN
			" . ScheduleOfRateItemFormulatedColumnTable::getInstance()->getTableName() . " AS ifc ON i.id = ifc.relation_id JOIN
			" . BillItemFormulatedColumnTable::getInstance()->getTableName() . " AS bifc ON ifc.id = bifc.schedule_of_rate_item_formulated_column_id JOIN
			" . BillItemTable::getInstance()->getTableName() . " AS bi ON bifc.relation_id = bi.id JOIN
			" . BillElementTable::getInstance()->getTableName() . " AS be ON bi.element_id = be.id JOIN
			" . ProjectStructureTable::getInstance()->getTableName() . " AS s ON be.project_structure_id = s.id
			WHERE s.root_id = " . $project->id . " AND i.id IN (" . implode(', ', $tradeItemIds) . ")
			AND bifc.column_name = '" . BillItem::FORMULATED_COLUMN_RATE . "'
			AND i.deleted_at IS NULL AND ifc.deleted_at IS NULL AND bifc.deleted_at IS NULL AND bi.project_revision_deleted_at IS NULL
			AND bi.deleted_at IS NULL AND be.deleted_at IS NULL AND s.deleted_at IS NULL");

			$stmt->execute(array());

			$scheduleOfRateItemIds = $stmt->fetchAll(PDO::FETCH_ASSOC);

			if ( !empty( $scheduleOfRateItemIds ) )
			{
				foreach ( $scheduleOfRateItemIds as $scheduleOfRateItemId )
				{
					$tradeIds[$scheduleOfRateItemId['trade_id']]  = $scheduleOfRateItemId['trade_id'];
					$itemIds[$scheduleOfRateItemId['trade_id']][] = $scheduleOfRateItemId['id'];
				}

				$stmt = $pdo->prepare("SELECT id, description FROM " . ScheduleOfRateTradeTable::getInstance()->getTableName() . " WHERE id IN
				(SELECT DISTINCT t.id FROM " . ScheduleOfRateTradeTable::getInstance()->getTableName() . " AS t JOIN
				" . ScheduleOfRateItemTable::getInstance()->getTableName() . " AS i ON t.id = i.trade_id JOIN
				" . ScheduleOfRateItemFormulatedColumnTable::getInstance()->getTableName() . " AS ifc ON i.id = ifc.relation_id JOIN
				" . BillItemFormulatedColumnTable::getInstance()->getTableName() . " AS bifc ON ifc.id = bifc.schedule_of_rate_item_formulated_column_id JOIN
				" . BillItemTable::getInstance()->getTableName() . " AS bi ON bifc.relation_id = bi.id JOIN
				" . BillElementTable::getInstance()->getTableName() . " AS be ON bi.element_id = be.id JOIN
				" . ProjectStructureTable::getInstance()->getTableName() . " AS s ON be.project_structure_id = s.id
				WHERE t.id IN (" . implode(', ', $tradeIds) . ") AND s.root_id = " . $project->id . " AND t.schedule_of_rate_id IN (" . $scheduleOfRate->id . ") AND bifc.column_name = '" . BillItem::FORMULATED_COLUMN_RATE . "'
				AND i.deleted_at IS NULL
				AND ifc.deleted_at IS NULL AND bifc.deleted_at IS NULL AND bi.deleted_at IS NULL AND bi.project_revision_deleted_at IS NULL
				AND be.deleted_at IS NULL AND s.deleted_at IS NULL) AND schedule_of_rate_id = " . $scheduleOfRate->id . " AND deleted_at IS NULL ORDER BY id");

				$stmt->execute();

				$trades = $stmt->fetchAll(PDO::FETCH_ASSOC);

				foreach ( $trades as $trade )
				{
					$tradeInfo = array(
						'id'                      => 'trade-' . $trade['id'],
						'description'             => $trade['description'],
						'type'                    => 0,
						'uom_id'                  => - 1,
						'uom_symbol'              => '',
						'multi-rate'              => false,
						'multi-item_markup'       => false,
						'total_qty'               => 0,
						'total_cost'              => 0,
						'view_bill_item_all'      => - 1,
						'view_bill_item_drill_in' => - 1
					);

					foreach ( array( 'rate', 'item_markup' ) as $formulatedColumnConstant )
					{
						$tradeInfo[$formulatedColumnConstant . '-value']       = '';
						$tradeInfo[$formulatedColumnConstant . '-final_value'] = 0;
						$tradeInfo[$formulatedColumnConstant . '-linked']      = false;
						$tradeInfo[$formulatedColumnConstant . '-has_formula'] = false;
					}

					$items[] = $tradeInfo;

					unset( $tradeInfo );

					$stmt = $pdo->prepare("SELECT DISTINCT p.id, p.root_id, p.description, p.type, p.uom_id,
					p.level, p.priority, p.lft, uom.symbol AS uom_symbol
					FROM " . ScheduleOfRateItemTable::getInstance()->getTableName() . " c
					JOIN " . ScheduleOfRateItemTable::getInstance()->getTableName() . " p ON c.lft BETWEEN p.lft AND p.rgt
					LEFT JOIN " . UnitOfMeasurementTable::getInstance()->getTableName() . " uom ON p.uom_id = uom.id AND uom.deleted_at IS NULL
					WHERE c.root_id = p.root_id AND c.type <> " . ScheduleOfRateItem::TYPE_HEADER . "
					AND c.id IN (" . implode(',', $itemIds[$trade['id']]) . ") AND p.trade_id = " . $trade['id'] . "
					AND c.deleted_at IS NULL AND p.deleted_at IS NULL
					ORDER BY p.priority, p.lft, p.level ASC");

					$stmt->execute(array());

					$records = $stmt->fetchAll(PDO::FETCH_ASSOC);

					foreach ( $records as $key => $record )
					{
						$multiItemMarkup = false;
						$multiRate       = false;
						$totalQty        = 0;
						$totalCost       = 0;

						foreach ( array( 'rate', 'item_markup' ) as $formulatedColumnConstant )
						{
							$records[$key][$formulatedColumnConstant . '-value']       = '';
							$records[$key][$formulatedColumnConstant . '-final_value'] = 0;
							$records[$key][$formulatedColumnConstant . '-linked']      = false;
							$records[$key][$formulatedColumnConstant . '-has_formula'] = false;
						}

						/*
						* getting bill item markup and sor rate
						*/
						if ( $record['type'] == ScheduleOfRateItem::TYPE_WORK_ITEM )
						{
							$stmt = $pdo->prepare("SELECT DISTINCT COALESCE(markup_column.final_value, 0) AS value
							FROM " . ProjectStructureTable::getInstance()->getTableName() . " AS s
							JOIN " . BillElementTable::getInstance()->getTableName() . " AS be ON be.project_structure_id = s.id
							JOIN " . BillItemTable::getInstance()->getTableName() . " AS bi ON bi.element_id = be.id
							JOIN " . BillItemFormulatedColumnTable::getInstance()->getTableName() . " markup_column ON markup_column.relation_id = bi.id
							JOIN " . BillItemFormulatedColumnTable::getInstance()->getTableName() . " ifc
							ON markup_column.relation_id = ifc.relation_id
							JOIN " . ScheduleOfRateItemFormulatedColumnTable::getInstance()->getTableName() . " AS sorifc
							ON ifc.schedule_of_rate_item_formulated_column_id = sorifc.id
							WHERE s.root_id = " . $project->id . " AND sorifc.relation_id = " . $record['id'] . " AND markup_column.column_name = '" . BillItem::FORMULATED_COLUMN_MARKUP_PERCENTAGE . "'
							AND s.deleted_at IS NULL AND be.deleted_at IS NULL AND bi.project_revision_deleted_at IS NULL AND bi.deleted_at IS NULL
							AND markup_column.deleted_at IS NULL AND ifc.deleted_at IS NULL AND sorifc.deleted_at IS NULL");

							$stmt->execute(array());

							if ( $stmt->rowCount() > 1 )
							{
								$multiItemMarkup = true;
							}
							else
							{
								$markup = $stmt->fetch(PDO::FETCH_ASSOC);

								$records[$key]['item_markup-value']       = $markup['value'];
								$records[$key]['item_markup-final_value'] = $markup['value'];
							}

							$stmt = $pdo->prepare("SELECT DISTINCT COALESCE(ifc.final_value, 0) AS value
							FROM " . ProjectStructureTable::getInstance()->getTableName() . " AS s
							JOIN " . BillElementTable::getInstance()->getTableName() . " AS be ON be.project_structure_id = s.id
							JOIN " . BillItemTable::getInstance()->getTableName() . " AS bi ON bi.element_id = be.id
							JOIN " . BillItemFormulatedColumnTable::getInstance()->getTableName() . " ifc ON ifc.relation_id = bi.id
							JOIN " . ScheduleOfRateItemFormulatedColumnTable::getInstance()->getTableName() . " AS sorifc
							ON ifc.schedule_of_rate_item_formulated_column_id = sorifc.id
							WHERE s.root_id = " . $project->id . " AND sorifc.relation_id = " . $record['id'] . " AND ifc.column_name = '" . BillItem::FORMULATED_COLUMN_RATE . "'
							AND s.deleted_at IS NULL AND be.deleted_at IS NULL AND bi.project_revision_deleted_at IS NULL AND bi.deleted_at IS NULL
							AND ifc.deleted_at IS NULL AND sorifc.deleted_at IS NULL");

							$stmt->execute(array());

							if ( $stmt->rowCount() > 1 )
							{
								$multiRate = true;
							}
							else
							{
								$rate = $stmt->fetch(PDO::FETCH_ASSOC);

								$records[$key]['rate-value']       = $rate['value'];
								$records[$key]['rate-final_value'] = $rate['value'];
							}

							list( $totalQty, $totalCost ) = ScheduleOfRateItemTable::calculateTotalCostForAnalysis($record['id'], $project->id);
						}

						$records[$key]['view_bill_item_all']      = $record['id'];
						$records[$key]['view_bill_item_drill_in'] = $record['id'];
						$records[$key]['multi-rate']              = $multiRate;
						$records[$key]['multi-item_markup']       = $multiItemMarkup;
						$records[$key]['total_qty']               = $totalQty;
						$records[$key]['total_cost']              = $totalCost;

						array_push($items, $records[$key]);
					}

					unset( $records, $itemIds[$trade['id']] );
				}
			}
		}

		$emptyRow = array(
			'id'                      => Constants::GRID_LAST_ROW,
			'description'             => '',
			'uom_id'                  => - 1,
			'uom_symbol'              => '',
			'multi-rate'              => false,
			'multi-item_markup'       => false,
			'total_qty'               => 0,
			'total_cost'              => 0,
			'view_bill_item_all'      => - 1,
			'view_bill_item_drill_in' => - 1
		);

		foreach ( array( 'rate', 'item_markup' ) as $formulatedColumnConstant )
		{
			$emptyRow[$formulatedColumnConstant . '-value']       = '';
			$emptyRow[$formulatedColumnConstant . '-final_value'] = 0;
			$emptyRow[$formulatedColumnConstant . '-linked']      = false;
			$emptyRow[$formulatedColumnConstant . '-has_formula'] = false;
		}

		array_push($items, $emptyRow);

		return $this->renderJson(array(
			'identifier'     => 'id',
			'items'          => $items,
			'sum_total_qty'  => $sumTotalQuantity,
			'sum_total_cost' => $sumTotalCost
		));
	}

	public function executeGetPrintingSelectedTradeItemsWithSelectedTenderer(sfWebRequest $request)
	{
		$this->forward404Unless(
			$request->isXmlHttpRequest() AND
			$project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('pid')) AND
			$scheduleOfRate = Doctrine_Core::getTable('ScheduleOfRate')->find($request->getParameter('scheduleOfRateId'))
		);

		$pdo              = $project->getTable()->getConnection()->getDbh();
		$tradeItemIds     = json_decode($request->getParameter('trade_item_ids'), true);
		$tradeIds         = array();
		$newTradeIds      = array();
		$itemIds          = array();
		$data             = array();
		$items            = array();
		$trades           = array();
		$awardedCompany   = $project->TenderSetting->AwardedCompany;
		$sumTotalQuantity = 0;
		$sumTotalCost     = 0;

		// get trade item first, then get trade level
		if ( !empty( $tradeItemIds ) )
		{
			$stmt = $pdo->prepare("SELECT DISTINCT i.id, i.trade_id FROM " . ScheduleOfRateItemTable::getInstance()->getTableName() . " AS i JOIN
			" . ScheduleOfRateItemFormulatedColumnTable::getInstance()->getTableName() . " AS ifc ON i.id = ifc.relation_id JOIN
			" . BillItemFormulatedColumnTable::getInstance()->getTableName() . " AS bifc ON ifc.id = bifc.schedule_of_rate_item_formulated_column_id JOIN
			" . BillItemTable::getInstance()->getTableName() . " AS bi ON bifc.relation_id = bi.id JOIN
			" . BillElementTable::getInstance()->getTableName() . " AS be ON bi.element_id = be.id JOIN
			" . ProjectStructureTable::getInstance()->getTableName() . " AS s ON be.project_structure_id = s.id
			WHERE s.root_id = " . $project->id . " AND i.id IN (" . implode(', ', $tradeItemIds) . ")
			AND bifc.column_name = '" . BillItem::FORMULATED_COLUMN_RATE . "'
			AND i.deleted_at IS NULL AND ifc.deleted_at IS NULL AND bifc.deleted_at IS NULL AND bi.project_revision_deleted_at IS NULL
			AND bi.deleted_at IS NULL AND be.deleted_at IS NULL AND s.deleted_at IS NULL");

			$stmt->execute(array());

			$scheduleOfRateItemIds = $stmt->fetchAll(PDO::FETCH_ASSOC);

			if ( !empty( $scheduleOfRateItemIds ) )
			{
				foreach ( $scheduleOfRateItemIds as $scheduleOfRateItemId )
				{
					$tradeIds[$scheduleOfRateItemId['trade_id']] = $scheduleOfRateItemId['trade_id'];
					$itemIds[]                                   = $scheduleOfRateItemId['id'];
				}

				$stmt = $pdo->prepare("SELECT id, description FROM " . ScheduleOfRateTradeTable::getInstance()->getTableName() . " WHERE id IN
				(SELECT DISTINCT t.id FROM " . ScheduleOfRateTradeTable::getInstance()->getTableName() . " AS t JOIN
				" . ScheduleOfRateItemTable::getInstance()->getTableName() . " AS i ON t.id = i.trade_id JOIN
				" . ScheduleOfRateItemFormulatedColumnTable::getInstance()->getTableName() . " AS ifc ON i.id = ifc.relation_id JOIN
				" . BillItemFormulatedColumnTable::getInstance()->getTableName() . " AS bifc ON ifc.id = bifc.schedule_of_rate_item_formulated_column_id JOIN
				" . BillItemTable::getInstance()->getTableName() . " AS bi ON bifc.relation_id = bi.id JOIN
				" . BillElementTable::getInstance()->getTableName() . " AS be ON bi.element_id = be.id JOIN
				" . ProjectStructureTable::getInstance()->getTableName() . " AS s ON be.project_structure_id = s.id
				WHERE t.id IN (" . implode(', ', $tradeIds) . ") AND s.root_id = " . $project->id . "
				AND t.schedule_of_rate_id IN (" . $scheduleOfRate->id . ") AND bifc.column_name = '" . BillItem::FORMULATED_COLUMN_RATE . "'
				AND i.deleted_at IS NULL
				AND ifc.deleted_at IS NULL AND bifc.deleted_at IS NULL AND bi.deleted_at IS NULL AND bi.project_revision_deleted_at IS NULL
				AND be.deleted_at IS NULL AND s.deleted_at IS NULL) AND schedule_of_rate_id = " . $scheduleOfRate->id . " AND deleted_at IS NULL ORDER BY id");

				$stmt->execute();

				$trades = $stmt->fetchAll(PDO::FETCH_ASSOC);

				foreach ( $trades as $trade )
				{
					$newTradeIds[$trade['id']] = $trade['id'];

					unset( $trade );
				}
			}
		}

		// will get item(s) list associated with the trade and item ids submitted
		if ( !empty( $trades ) AND !empty( $newTradeIds ) )
		{
			$stmt = $pdo->prepare("SELECT DISTINCT p.id, p.trade_id, p.root_id, p.description, p.type, p.uom_id,
			p.level, p.priority, p.lft, uom.symbol AS uom_symbol
			FROM " . ScheduleOfRateItemTable::getInstance()->getTableName() . " c
			JOIN " . ScheduleOfRateItemTable::getInstance()->getTableName() . " p ON c.lft BETWEEN p.lft AND p.rgt
			LEFT JOIN " . UnitOfMeasurementTable::getInstance()->getTableName() . " uom ON p.uom_id = uom.id AND uom.deleted_at IS NULL
			WHERE c.root_id = p.root_id AND c.type <> " . ScheduleOfRateItem::TYPE_HEADER . "
			AND p.trade_id IN (" . implode(',', $newTradeIds) . ") AND c.id IN (" . implode(',', $itemIds) . ")
			AND c.deleted_at IS NULL AND p.deleted_at IS NULL
			ORDER BY p.priority, p.lft, p.level ASC");

			$stmt->execute();

			$itemsData = $stmt->fetchAll(PDO::FETCH_ASSOC);

			foreach ( $itemsData as $item )
			{
				$items[$item['trade_id']][] = $item;

				unset( $item );
			}

			unset( $itemsData );
		}

		foreach ( $trades as $trade )
		{
			// if there is no item(s) associated with the current trade, then continue to other trade
			if ( !isset( $items[$trade['id']] ) )
			{
				continue;
			}

			$tradeInfo = array(
				'id'                      => 'trade-' . $trade['id'],
				'description'             => $trade['description'],
				'type'                    => 0,
				'uom_id'                  => - 1,
				'uom_symbol'              => '',
				'multi-rate'              => false,
				'multi-item_markup'       => false,
				'total_qty'               => 0,
				'total_cost'              => 0,
				'view_bill_item_all'      => - 1,
				'view_bill_item_drill_in' => - 1
			);

			foreach ( array( 'rate', 'item_markup' ) as $formulatedColumnConstant )
			{
				$tradeInfo[$formulatedColumnConstant . '-value']       = '';
				$tradeInfo[$formulatedColumnConstant . '-final_value'] = 0;
				$tradeInfo[$formulatedColumnConstant . '-linked']      = false;
				$tradeInfo[$formulatedColumnConstant . '-has_formula'] = false;
			}

			$data[] = $tradeInfo;

			unset( $tradeInfo );

			$records = $items[$trade['id']];

			foreach ( $records as $key => $record )
			{
				$multiItemMarkup = false;
				$multiRate       = false;
				$totalQty        = 0;
				$totalCost       = 0;

				foreach ( array( 'rate', 'item_markup' ) as $formulatedColumnConstant )
				{
					$record[$formulatedColumnConstant . '-value']       = '';
					$record[$formulatedColumnConstant . '-final_value'] = 0;
					$record[$formulatedColumnConstant . '-linked']      = false;
					$record[$formulatedColumnConstant . '-has_formula'] = false;
				}

				/*
				* getting bill item markup and sor rate
				*/
				if ( $record['type'] == ScheduleOfRateItem::TYPE_WORK_ITEM )
				{
					$stmt = $pdo->prepare("SELECT DISTINCT COALESCE(markup_column.final_value, 0) AS value
					FROM " . ProjectStructureTable::getInstance()->getTableName() . " AS s
					JOIN " . BillElementTable::getInstance()->getTableName() . " AS be ON be.project_structure_id = s.id
					JOIN " . BillItemTable::getInstance()->getTableName() . " AS bi ON bi.element_id = be.id
					JOIN " . BillItemFormulatedColumnTable::getInstance()->getTableName() . " markup_column ON markup_column.relation_id = bi.id
					JOIN " . BillItemFormulatedColumnTable::getInstance()->getTableName() . " ifc
					ON markup_column.relation_id = ifc.relation_id
					JOIN " . ScheduleOfRateItemFormulatedColumnTable::getInstance()->getTableName() . " AS sorifc
					ON ifc.schedule_of_rate_item_formulated_column_id = sorifc.id
					WHERE s.root_id = " . $project->id . " AND sorifc.relation_id = " . $record['id'] . "
					AND markup_column.column_name = '" . BillItem::FORMULATED_COLUMN_MARKUP_PERCENTAGE . "'
					AND s.deleted_at IS NULL AND be.deleted_at IS NULL AND bi.project_revision_deleted_at IS NULL AND bi.deleted_at IS NULL
					AND markup_column.deleted_at IS NULL AND ifc.deleted_at IS NULL AND sorifc.deleted_at IS NULL");

					$stmt->execute(array());

					if ( $stmt->rowCount() > 1 )
					{
						$multiItemMarkup = true;
					}
					else
					{
						$markup = $stmt->fetch(PDO::FETCH_ASSOC);

						$record['item_markup-value']       = $markup['value'];
						$record['item_markup-final_value'] = $markup['value'];
					}

					$stmt = $pdo->prepare("SELECT DISTINCT COALESCE(r.rate, 0) AS value
					FROM " . BillItemTable::getInstance()->getTableName() . " AS bi
					JOIN " . BillElementTable::getInstance()->getTableName() . " AS be ON bi.element_id = be.id
					JOIN " . BillItemFormulatedColumnTable::getInstance()->getTableName() . " ifc ON ifc.relation_id = bi.id
					JOIN " . TenderBillItemRateTable::getInstance()->getTableName() . " r ON r.bill_item_id = bi.id
					JOIN " . ProjectStructureTable::getInstance()->getTableName() . " AS s ON be.project_structure_id = s.id
					JOIN " . TenderCompanyTable::getInstance()->getTableName() . " tc ON
					(r.tender_company_id = tc.id AND tc.project_structure_id = s.root_id)
					JOIN " . ScheduleOfRateItemFormulatedColumnTable::getInstance()->getTableName() . " AS sorifc
					ON ifc.schedule_of_rate_item_formulated_column_id = sorifc.id
					WHERE r.rate <> 0 AND s.root_id = " . $project->id . "
					AND sorifc.relation_id = " . $record['id'] . " AND ifc.column_name = '" . BillItem::FORMULATED_COLUMN_RATE . "'
					AND tc.company_id = " . $awardedCompany->id . " AND tc.show IS TRUE
					AND s.deleted_at IS NULL AND be.deleted_at IS NULL AND bi.project_revision_deleted_at IS NULL AND bi.deleted_at IS NULL
					AND ifc.deleted_at IS NULL AND sorifc.deleted_at IS NULL");

					$stmt->execute(array());

					if ( $stmt->rowCount() > 1 )
					{
						$multiRate = true;
					}
					else
					{
						$rate = $stmt->fetch(PDO::FETCH_ASSOC);

						$record['rate-value']       = $rate['value'];
						$record['rate-final_value'] = $rate['value'];
					}

					list( $totalQty, $totalCost ) = ScheduleOfRateItemTable::calculateTotalCostForAnalysisWithSelectedTendererRates($record['id'], $project->id, $awardedCompany->id);
				}

				$record['view_bill_item_all']      = $record['id'];
				$record['view_bill_item_drill_in'] = $record['id'];
				$record['multi-rate']              = $multiRate;
				$record['multi-item_markup']       = $multiItemMarkup;
				$record['total_qty']               = $totalQty;
				$record['total_cost']              = $totalCost;

				array_push($data, $record);

				unset( $record );
			}

			unset( $items[$trade['id']], $trade, $records );
		}

		unset( $trades );

		$emptyRow = array(
			'id'                      => Constants::GRID_LAST_ROW,
			'description'             => '',
			'uom_id'                  => - 1,
			'uom_symbol'              => '',
			'multi-rate'              => false,
			'multi-item_markup'       => false,
			'total_qty'               => 0,
			'total_cost'              => 0,
			'view_bill_item_all'      => - 1,
			'view_bill_item_drill_in' => - 1
		);

		foreach ( array( 'rate', 'item_markup' ) as $formulatedColumnConstant )
		{
			$emptyRow[$formulatedColumnConstant . '-value']       = '';
			$emptyRow[$formulatedColumnConstant . '-final_value'] = 0;
			$emptyRow[$formulatedColumnConstant . '-linked']      = false;
			$emptyRow[$formulatedColumnConstant . '-has_formula'] = false;
		}

		array_push($data, $emptyRow);

		return $this->renderJson(array(
			'identifier'     => 'id',
			'items'          => $data,
			'sum_total_qty'  => $sumTotalQuantity,
			'sum_total_cost' => $sumTotalCost
		));
	}

	public function executeGetPrintingSelectedBillItems(sfWebRequest $request)
	{
		$this->forward404Unless(
			$request->isXmlHttpRequest() and
			$project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('pid')) and
			$scheduleOfRate = Doctrine_Core::getTable('ScheduleOfRate')->find($request->getParameter('scheduleOfRateId')) and
			$scheduleOfRateTrade = Doctrine_Core::getTable('ScheduleOfRateTrade')->find($request->getParameter('scheduleOfRateTradeId'))
		);

		$pdo               = $project->getTable()->getConnection()->getDbh();
		$tradeItemIds      = json_decode($request->getParameter('tradeItemIds'), true);
		$billItemIds       = json_decode($request->getParameter('bill_item_ids'), true);
		$printPreviewItems = array();
		$sorItemIds        = array();
		$contractorRates   = array();
		$companies         = array();
		$billCount         = 0;
		$sumTotalQuantity  = 0;
		$sumTotalCost      = 0;

		$formulatedColumnConstants = array(
			BillItem::FORMULATED_COLUMN_RATE,
			BillItem::FORMULATED_COLUMN_MARKUP_PERCENTAGE
		);

		if ( $project->MainInformation->status == ProjectMainInformation::STATUS_TENDERING or $project->MainInformation->status == ProjectMainInformation::STATUS_POSTCONTRACT )
		{
			$tenderSetting = $project->TenderSetting;

			switch ($tenderSetting->contractor_sort_by)
			{
				case TenderSetting::SORT_CONTRACTOR_NAME_ASC:
					$sqlOrder = "c.name ASC";
					break;
				case TenderSetting::SORT_CONTRACTOR_NAME_DESC:
					$sqlOrder = "c.name DESC";
					break;
				case TenderSetting::SORT_CONTRACTOR_HIGHEST_LOWEST:
					$sqlOrder = "total DESC";
					break;
				case TenderSetting::SORT_CONTRACTOR_LOWEST_HIGHEST:
					$sqlOrder = "total ASC";
					break;
				default:
					throw new Exception('invalid sort option');
			}

			$awardedCompanyId = $tenderSetting->awarded_company_id > 0 ? $tenderSetting->awarded_company_id : - 1;

			$stmt = $pdo->prepare("SELECT c.id, c.name, COALESCE(SUM(r.grand_total), 0) AS total
			FROM " . CompanyTable::getInstance()->getTableName() . " c
			JOIN " . TenderCompanyTable::getInstance()->getTableName() . " xref ON xref.company_id = c.id
			LEFT JOIN " . TenderBillElementGrandTotalTable::getInstance()->getTableName() . " r ON r.tender_company_id = xref.id
			WHERE xref.project_structure_id = " . $project->id . "
			AND c.id <> " . $awardedCompanyId . " AND xref.show IS TRUE
			AND c.deleted_at IS NULL GROUP BY c.id ORDER BY " . $sqlOrder);

			$stmt->execute();

			$companies = $stmt->fetchAll(PDO::FETCH_ASSOC);

			if ( $tenderSetting->awarded_company_id > 0 )
			{
				$awardedCompany = $tenderSetting->AwardedCompany;

				$company = array(
					'id'   => $awardedCompany->id,
					'name' => $awardedCompany->name
				);

				array_unshift($companies, $company);

				unset( $company, $awardedCompany );
			}
		}

		if ( !empty( $billItemIds ) )
		{
			$stmt = $pdo->prepare("SELECT DISTINCT p.id, p.element_id, p.root_id, p.description, p.type, p.uom_id, p.grand_total, p.grand_total_quantity, p.level, p.priority, e.priority AS element_priority, p.lft, uom.symbol AS uom_symbol, ifc.relation_id
			FROM " . BillItemTable::getInstance()->getTableName() . " c
			JOIN " . BillItemTable::getInstance()->getTableName() . " p ON c.lft BETWEEN p.lft AND p.rgt
			LEFT JOIN " . UnitOfMeasurementTable::getInstance()->getTableName() . " AS uom ON c.uom_id = uom.id AND uom.deleted_at IS NULL
			JOIN " . BillItemFormulatedColumnTable::getInstance()->getTableName() . " AS bifc ON c.id = bifc.relation_id
			JOIN " . ScheduleOfRateItemFormulatedColumnTable::getInstance()->getTableName() . " AS ifc ON bifc.schedule_of_rate_item_formulated_column_id = ifc.id
			JOIN " . BillElementTable::getInstance()->getTableName() . " AS e ON p.element_id = e.id
			JOIN " . ProjectStructureTable::getInstance()->getTableName() . " AS s ON e.project_structure_id = s.id
			WHERE c.id IN (" . implode(', ', $billItemIds) . ") AND ifc.relation_id IN (" . implode(',', $tradeItemIds) . ")
			AND s.root_id = " . $project->id . "
			AND c.root_id = p.root_id AND c.element_id = p.element_id
			AND bifc.column_name = '" . BillItem::FORMULATED_COLUMN_RATE . "'
			AND c.project_revision_deleted_at IS NULL AND c.deleted_at IS NULL
			AND p.project_revision_deleted_at IS NULL AND p.deleted_at IS NULL
			AND e.deleted_at IS NULL AND ifc.deleted_at IS NULL AND bifc.deleted_at IS NULL AND s.deleted_at IS NULL
			ORDER BY e.priority, p.priority, p.lft, p.level ASC");

			$stmt->execute();

			$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

			foreach ( $items as $item )
			{
				$sorItemIds[$item['relation_id']] = $item['relation_id'];
			}

			$stmt = $pdo->prepare("SELECT bifc.relation_id, bifc.column_name, bifc.final_value, bifc.linked, bifc.has_build_up
			FROM " . ProjectStructureTable::getInstance()->getTableName() . " AS s
			JOIN " . BillElementTable::getInstance()->getTableName() . " AS e ON s.id = e.project_structure_id
			JOIN " . BillItemTable::getInstance()->getTableName() . " i  ON i.element_id = e.id
			JOIN " . BillItemFormulatedColumnTable::getInstance()->getTableName() . " AS bifc ON i.id = bifc.relation_id
			JOIN " . BillItemFormulatedColumnTable::getInstance()->getTableName() . " AS bifc2 ON bifc.relation_id = bifc2.relation_id
			JOIN " . ScheduleOfRateItemFormulatedColumnTable::getInstance()->getTableName() . " AS ifc ON bifc2.schedule_of_rate_item_formulated_column_id = ifc.id
			WHERE s.root_id = " . $project->id . "
			AND ifc.relation_id IN (" . implode(', ', $sorItemIds) . ") AND bifc.column_name <> '" . BillItem::FORMULATED_COLUMN_MARKUP_AMOUNT . "'
			AND s.deleted_at IS NULL AND i.project_revision_deleted_at IS NULL AND i.deleted_at IS NULL
			AND e.deleted_at IS NULL AND ifc.deleted_at IS NULL AND bifc.deleted_at IS NULL AND bifc2.deleted_at IS NULL
			ORDER BY ifc.relation_id ASC");

			$stmt->execute();

			$formulatedColumns = $stmt->fetchAll(PDO::FETCH_ASSOC);

			/*
			* select bills
			*/
			$stmt = $pdo->prepare("SELECT DISTINCT s.id, s.title, s.lft FROM " . ProjectStructureTable::getInstance()->getTableName() . " AS s JOIN
			" . BillElementTable::getInstance()->getTableName() . " AS be ON s.id = be.project_structure_id JOIN
			" . BillItemTable::getInstance()->getTableName() . " AS bi ON be.id = bi.element_id JOIN
			" . BillItemFormulatedColumnTable::getInstance()->getTableName() . " AS bifc ON bi.id = bifc.relation_id JOIN
			" . ScheduleOfRateItemFormulatedColumnTable::getInstance()->getTableName() . " AS ifc ON bifc.schedule_of_rate_item_formulated_column_id = ifc.id
			WHERE s.root_id = " . $project->id . " AND ifc.relation_id IN (" . implode(', ', $sorItemIds) . ")
			AND bifc.column_name = '" . BillItem::FORMULATED_COLUMN_RATE . "'
			AND ifc.deleted_at IS NULL AND bifc.deleted_at IS NULL AND bi.project_revision_deleted_at IS NULL AND bi.deleted_at IS NULL AND be.deleted_at IS NULL
			AND s.deleted_at IS NULL ORDER BY s.lft ASC");

			$stmt->execute();

			$bills = $stmt->fetchAll(PDO::FETCH_ASSOC);

			if ( $project->MainInformation->status == ProjectMainInformation::STATUS_TENDERING or $project->MainInformation->status == ProjectMainInformation::STATUS_POSTCONTRACT )
			{
				$sql = "SELECT tc.company_id, r.bill_item_id, r.rate FROM " . TenderBillItemRateTable::getInstance()->getTableName() . " r
				JOIN " . TenderCompanyTable::getInstance()->getTableName() . " tc ON r.tender_company_id = tc.id
				WHERE tc.project_structure_id = " . $project->id . " AND tc.show IS TRUE";

				$stmt = $pdo->prepare($sql);

				$stmt->execute();

				$contractorRateRecords = $stmt->fetchAll(PDO::FETCH_ASSOC);

				foreach ( $contractorRateRecords as $record )
				{
					if ( !array_key_exists($record['company_id'], $contractorRates) )
					{
						$contractorRates[$record['company_id']] = array();
					}

					if ( !array_key_exists($record['bill_item_id'], $contractorRates[$record['company_id']]) )
					{
						$contractorRates[$record['company_id']][$record['bill_item_id']] = 0;
					}

					$contractorRates[$record['company_id']][$record['bill_item_id']] = $record['rate'];

					unset( $record );
				}

				unset( $contractorRateRecords );
			}

			// get schedule of rate trade item's information
			$stmt = $pdo->prepare("SELECT DISTINCT t.id, t.description, t.root_id, t.lft, t.priority, t.lft, t.level
			FROM " . ScheduleOfRateItemTable::getInstance()->getTableName() . " t
			WHERE t.id IN (" . implode(', ', $sorItemIds) . ") AND t.trade_id = " . $scheduleOfRateTrade->id . " AND t.deleted_at IS NULL
			ORDER BY t.priority, t.lft, t.level ASC");

			$stmt->execute();

			$tradeItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

			foreach ( $tradeItems as $tradeItem )
			{
				$tradeItemInfo = array(
					'id'                   => 'tradeItemId-' . $tradeItem['id'],
					'description'          => $tradeItem['description'],
					'type'                 => BillItem::PROJECT_ANALYZER_TRADE_ITEM,
					'level'                => 0,
					'uom_id'               => - 1,
					'uom_symbol'           => '',
					'grand_total_quantity' => 0,
					'grand_total'          => 0,
					'rate-value'           => 0,
					'rate-final_value'     => 0,
					'multi-rate'           => false,
				);

				if ( $project->MainInformation->status == ProjectMainInformation::STATUS_TENDERING or $project->MainInformation->status == ProjectMainInformation::STATUS_POSTCONTRACT )
				{
					foreach ( $companies as $company )
					{
						$tradeItemInfo['contractor_rate-' . $company['id'] . '-value']       = '';
						$tradeItemInfo['contractor_rate-' . $company['id'] . '-final_value'] = number_format(0, 2, '.', '');
						$tradeItemInfo['contractor_rate-' . $company['id'] . '-has_formula'] = false;
					}
				}

				foreach ( $formulatedColumnConstants as $formulatedColumnConstant )
				{
					$tradeItemInfo[$formulatedColumnConstant . '-value']        = '';
					$tradeItemInfo[$formulatedColumnConstant . '-final_value']  = 0;
					$tradeItemInfo[$formulatedColumnConstant . '-linked']       = false;
					$tradeItemInfo[$formulatedColumnConstant . '-has_formula']  = false;
					$tradeItemInfo[$formulatedColumnConstant . '-has_build_up'] = false;
				}

				$printPreviewItems[] = $tradeItemInfo;

				foreach ( $bills as $bill )
				{
					$stmt = $pdo->prepare("SELECT DISTINCT be.id, be.description, be.priority FROM
					" . BillElementTable::getInstance()->getTableName() . " AS be JOIN
					" . BillItemTable::getInstance()->getTableName() . " AS bi ON be.id = bi.element_id JOIN
					" . BillItemFormulatedColumnTable::getInstance()->getTableName() . " AS bifc ON bi.id = bifc.relation_id JOIN
					" . ScheduleOfRateItemFormulatedColumnTable::getInstance()->getTableName() . " AS ifc ON bifc.schedule_of_rate_item_formulated_column_id = ifc.id
					WHERE be.project_structure_id = " . $bill['id'] . " AND ifc.relation_id = " . $tradeItem['id'] . "
					AND bifc.column_name = '" . BillItem::FORMULATED_COLUMN_RATE . "'
					AND ifc.deleted_at IS NULL AND bifc.deleted_at IS NULL AND bi.project_revision_deleted_at IS NULL AND bi.deleted_at IS NULL AND be.deleted_at IS NULL
					ORDER BY be.priority ASC");

					$stmt->execute();

					$elements = $stmt->fetchAll(PDO::FETCH_ASSOC);

					foreach ( $elements as $element )
					{
						$generatedBillHeader = false;

						$result = array(
							'id'               => 'bill-' . $bill['id'] . '-elem' . $element['id'] . '-billCount-' . $billCount,
							'description'      => $bill['title'] . " > " . $element['description'],
							'type'             => - 1,
							'level'            => 0,
							'uom_id'           => - 1,
							'uom_symbol'       => '',
							'rate-value'       => 0,
							'rate-final_value' => 0,
						);

						foreach ( $formulatedColumnConstants as $formulatedColumnConstant )
						{
							$result[$formulatedColumnConstant . '-value']       = '';
							$result[$formulatedColumnConstant . '-final_value'] = 0;
							$result[$formulatedColumnConstant . '-linked']      = false;
							$result[$formulatedColumnConstant . '-has_formula'] = false;
						}

						if ( $project->MainInformation->status == ProjectMainInformation::STATUS_TENDERING or $project->MainInformation->status == ProjectMainInformation::STATUS_POSTCONTRACT )
						{
							foreach ( $companies as $company )
							{
								$result['contractor_rate-' . $company['id'] . '-value']       = '';
								$result['contractor_rate-' . $company['id'] . '-final_value'] = number_format(0, 2, '.', '');
								$result['contractor_rate-' . $company['id'] . '-has_formula'] = false;
							}
						}

						$billItem = array( 'id' => - 1 );

						foreach ( $items as $k => $item )
						{
							if ( $billItem['id'] != $item['id'] && $item['relation_id'] == $tradeItem['id'] && $item['element_id'] == $element['id'] )
							{
								if ( !$generatedBillHeader )
								{
									array_push($printPreviewItems, $result);

									$generatedBillHeader = true;
								}

								$billItem['id']                   = $item['id'] . '-billCount-' . $billCount;
								$billItem['description']          = $item['description'];
								$billItem['type']                 = $item['type'];
								$billItem['grand_total']          = $item['grand_total'];
								$billItem['grand_total_quantity'] = $item['grand_total_quantity'];
								$billItem['level']                = $item['level'];
								$billItem['uom_symbol']           = $item['uom_id'] > 0 ? $item['uom_symbol'] : '';

								foreach ( $formulatedColumnConstants as $formulatedColumnConstant )
								{
									$billItem[$formulatedColumnConstant . '-value']        = '';
									$billItem[$formulatedColumnConstant . '-final_value']  = 0;
									$billItem[$formulatedColumnConstant . '-linked']       = false;
									$billItem[$formulatedColumnConstant . '-has_formula']  = false;
									$billItem[$formulatedColumnConstant . '-has_build_up'] = false;
								}

								foreach ( $formulatedColumns as $key => $formulatedColumn )
								{
									if ( $formulatedColumn['relation_id'] == $item['id'] )
									{
										$columnName                              = $formulatedColumn['column_name'];
										$billItem[$columnName . '-value']        = $formulatedColumn['final_value'];
										$billItem[$columnName . '-final_value']  = $formulatedColumn['final_value'];
										$billItem[$columnName . '-linked']       = $formulatedColumn['linked'];
										$billItem[$columnName . '-has_formula']  = false;
										$billItem[$columnName . '-has_build_up'] = $formulatedColumn['has_build_up'];

										unset( $formulatedColumn, $formulatedColumns[$key] );
									}
								}

								if ( $project->MainInformation->status == ProjectMainInformation::STATUS_TENDERING or $project->MainInformation->status == ProjectMainInformation::STATUS_POSTCONTRACT )
								{
									foreach ( $companies as $company )
									{
										if ( array_key_exists($company['id'], $contractorRates) and array_key_exists($item['id'], $contractorRates[$company['id']]) )
										{
											$rate = $contractorRates[$company['id']][$item['id']];
										}
										else
										{
											$rate = number_format(0, 2, '.', '');
										}

										$billItem['contractor_rate-' . $company['id'] . '-value']       = $rate;
										$billItem['contractor_rate-' . $company['id'] . '-final_value'] = $rate;
										$billItem['contractor_rate-' . $company['id'] . '-has_formula'] = false;
									}
								}

								array_push($printPreviewItems, $billItem);

								unset( $items[$k], $item );
							}
						}
					}

					$billCount ++;
				}

				unset( $tradeItemInfo );
			}
		}

		$emptyRow = array(
			'id'                   => Constants::GRID_LAST_ROW,
			'description'          => '',
			'type'                 => BillItem::TYPE_WORK_ITEM,
			'level'                => 0,
			'uom_id'               => - 1,
			'uom_symbol'           => '',
			'grand_total_quantity' => 0,
			'grand_total'          => 0,
			'rate-value'           => 0,
			'rate-final_value'     => 0,
			'multi-rate'           => false,
		);

		if ( $project->MainInformation->status == ProjectMainInformation::STATUS_TENDERING or $project->MainInformation->status == ProjectMainInformation::STATUS_POSTCONTRACT )
		{
			foreach ( $companies as $company )
			{
				$emptyRow['contractor_rate-' . $company['id'] . '-value']       = '';
				$emptyRow['contractor_rate-' . $company['id'] . '-final_value'] = number_format(0, 2, '.', '');
				$emptyRow['contractor_rate-' . $company['id'] . '-has_formula'] = false;
			}
		}

		foreach ( $formulatedColumnConstants as $formulatedColumnConstant )
		{
			$emptyRow[$formulatedColumnConstant . '-value']        = '';
			$emptyRow[$formulatedColumnConstant . '-final_value']  = 0;
			$emptyRow[$formulatedColumnConstant . '-linked']       = false;
			$emptyRow[$formulatedColumnConstant . '-has_formula']  = false;
			$emptyRow[$formulatedColumnConstant . '-has_build_up'] = false;
		}

		unset( $companies, $contractorRates );

		array_push($printPreviewItems, $emptyRow);

		return $this->renderJson(array(
			'identifier'     => 'id',
			'items'          => $printPreviewItems,
			'sum_total_qty'  => $sumTotalQuantity,
			'sum_total_cost' => $sumTotalCost
		));
	}

	public function executeGetPrintingSelectedBillItemsWithSelectedTendererRates(sfWebRequest $request)
	{
		$this->forward404Unless(
			$request->isXmlHttpRequest() and
			$project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('pid')) and
			$scheduleOfRate = Doctrine_Core::getTable('ScheduleOfRate')->find($request->getParameter('scheduleOfRateId')) and
			$scheduleOfRateTrade = Doctrine_Core::getTable('ScheduleOfRateTrade')->find($request->getParameter('scheduleOfRateTradeId'))
		);

		$pdo                = $project->getTable()->getConnection()->getDbh();
		$tradeItemIds       = json_decode($request->getParameter('tradeItemIds'), true);
		$billItemIds        = json_decode($request->getParameter('bill_item_ids'), true);
		$awardedCompanyId   = $project->TenderSetting->awarded_company_id;
		$printPreviewItems  = array();
		$sorItemIds         = array();
		$tendererRates      = array();
		$tendererGrandTotal = array();
		$billCount          = 0;
		$sumTotalQuantity   = 0;
		$sumTotalCost       = 0;

		$formulatedColumnConstants = array(
			BillItem::FORMULATED_COLUMN_RATE,
			BillItem::FORMULATED_COLUMN_MARKUP_PERCENTAGE
		);

		if ( !empty( $billItemIds ) )
		{
			$stmt = $pdo->prepare("SELECT DISTINCT p.id, p.element_id, p.root_id, p.description, p.type, p.uom_id, p.grand_total, p.grand_total_quantity, p.level, p.priority, e.priority AS element_priority, p.lft, uom.symbol AS uom_symbol, ifc.relation_id
			FROM " . BillItemTable::getInstance()->getTableName() . " c
			JOIN " . BillItemTable::getInstance()->getTableName() . " p ON c.lft BETWEEN p.lft AND p.rgt
			LEFT JOIN " . UnitOfMeasurementTable::getInstance()->getTableName() . " AS uom ON c.uom_id = uom.id AND uom.deleted_at IS NULL
			JOIN " . BillItemFormulatedColumnTable::getInstance()->getTableName() . " AS bifc ON c.id = bifc.relation_id
			JOIN " . ScheduleOfRateItemFormulatedColumnTable::getInstance()->getTableName() . " AS ifc ON bifc.schedule_of_rate_item_formulated_column_id = ifc.id
			JOIN " . BillElementTable::getInstance()->getTableName() . " AS e ON p.element_id = e.id
			JOIN " . ProjectStructureTable::getInstance()->getTableName() . " AS s ON e.project_structure_id = s.id
			WHERE c.id IN (" . implode(', ', $billItemIds) . ") AND ifc.relation_id IN (" . implode(',', $tradeItemIds) . ")
			AND s.root_id = " . $project->id . "
			AND c.root_id = p.root_id AND c.element_id = p.element_id
			AND bifc.column_name = '" . BillItem::FORMULATED_COLUMN_RATE . "'
			AND c.project_revision_deleted_at IS NULL AND c.deleted_at IS NULL
			AND p.project_revision_deleted_at IS NULL AND p.deleted_at IS NULL
			AND e.deleted_at IS NULL AND ifc.deleted_at IS NULL AND bifc.deleted_at IS NULL AND s.deleted_at IS NULL
			ORDER BY e.priority, p.priority, p.lft, p.level ASC");

			$stmt->execute();

			$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

			foreach ( $items as $item )
			{
				$sorItemIds[$item['relation_id']] = $item['relation_id'];
			}

			$stmt = $pdo->prepare("SELECT bifc.relation_id, bifc.column_name, bifc.final_value, bifc.linked, bifc.has_build_up
			FROM " . ProjectStructureTable::getInstance()->getTableName() . " AS s
			JOIN " . BillElementTable::getInstance()->getTableName() . " AS e ON s.id = e.project_structure_id
			JOIN " . BillItemTable::getInstance()->getTableName() . " i  ON i.element_id = e.id
			JOIN " . BillItemFormulatedColumnTable::getInstance()->getTableName() . " AS bifc ON i.id = bifc.relation_id
			JOIN " . BillItemFormulatedColumnTable::getInstance()->getTableName() . " AS bifc2 ON bifc.relation_id = bifc2.relation_id
			JOIN " . ScheduleOfRateItemFormulatedColumnTable::getInstance()->getTableName() . " AS ifc ON bifc2.schedule_of_rate_item_formulated_column_id = ifc.id
			WHERE s.root_id = " . $project->id . "
			AND ifc.relation_id IN (" . implode(', ', $sorItemIds) . ") AND bifc.column_name <> '" . BillItem::FORMULATED_COLUMN_MARKUP_AMOUNT . "'
			AND s.deleted_at IS NULL AND i.project_revision_deleted_at IS NULL AND i.deleted_at IS NULL
			AND e.deleted_at IS NULL AND ifc.deleted_at IS NULL AND bifc.deleted_at IS NULL AND bifc2.deleted_at IS NULL
			ORDER BY ifc.relation_id ASC");

			$stmt->execute();

			$formulatedColumns = $stmt->fetchAll(PDO::FETCH_ASSOC);

			/*
			* select bills
			*/
			$stmt = $pdo->prepare("SELECT DISTINCT s.id, s.title, s.lft FROM " . ProjectStructureTable::getInstance()->getTableName() . " AS s JOIN
			" . BillElementTable::getInstance()->getTableName() . " AS be ON s.id = be.project_structure_id JOIN
			" . BillItemTable::getInstance()->getTableName() . " AS bi ON be.id = bi.element_id JOIN
			" . BillItemFormulatedColumnTable::getInstance()->getTableName() . " AS bifc ON bi.id = bifc.relation_id JOIN
			" . ScheduleOfRateItemFormulatedColumnTable::getInstance()->getTableName() . " AS ifc ON bifc.schedule_of_rate_item_formulated_column_id = ifc.id
			WHERE s.root_id = " . $project->id . " AND ifc.relation_id IN (" . implode(', ', $sorItemIds) . ")
			AND bifc.column_name = '" . BillItem::FORMULATED_COLUMN_RATE . "'
			AND ifc.deleted_at IS NULL AND bifc.deleted_at IS NULL AND bi.project_revision_deleted_at IS NULL
			AND bi.deleted_at IS NULL AND be.deleted_at IS NULL
			AND s.deleted_at IS NULL ORDER BY s.lft ASC");

			$stmt->execute();

			$bills = $stmt->fetchAll(PDO::FETCH_ASSOC);

			$stmt = $pdo->prepare("SELECT tc.company_id, r.bill_item_id, r.rate, r.grand_total
			FROM " . TenderBillItemRateTable::getInstance()->getTableName() . " r
			JOIN " . TenderCompanyTable::getInstance()->getTableName() . " tc ON r.tender_company_id = tc.id
			WHERE tc.project_structure_id = " . $project->id . " AND tc.company_id = " . $awardedCompanyId . " AND tc.show IS TRUE");

			$stmt->execute();

			$tendererRateRecords = $stmt->fetchAll(PDO::FETCH_ASSOC);

			foreach ( $tendererRateRecords as $record )
			{
				if ( !array_key_exists($record['company_id'], $tendererRates) )
				{
					$tendererRates[$record['company_id']]      = array();
					$tendererGrandTotal[$record['company_id']] = array();
				}

				if ( !array_key_exists($record['bill_item_id'], $tendererRates[$record['company_id']]) )
				{
					$tendererRates[$record['company_id']][$record['bill_item_id']]      = 0;
					$tendererGrandTotal[$record['company_id']][$record['bill_item_id']] = 0;
				}

				$tendererRates[$record['company_id']][$record['bill_item_id']]      = $record['rate'];
				$tendererGrandTotal[$record['company_id']][$record['bill_item_id']] = $record['grand_total'];

				unset( $record );
			}

			unset( $tendererRateRecords );

			// get schedule of rate trade item's information
			$stmt = $pdo->prepare("SELECT DISTINCT t.id, t.description, t.root_id, t.lft, t.priority, t.lft, t.level
			FROM " . ScheduleOfRateItemTable::getInstance()->getTableName() . " t
			WHERE t.id IN (" . implode(', ', $sorItemIds) . ") AND t.trade_id = " . $scheduleOfRateTrade->id . " AND t.deleted_at IS NULL
			ORDER BY t.priority, t.lft, t.level ASC");

			$stmt->execute();

			$tradeItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

			foreach ( $tradeItems as $tradeItem )
			{
				$tradeItemInfo = array(
					'id'                   => 'tradeItemId-' . $tradeItem['id'],
					'description'          => $tradeItem['description'],
					'type'                 => BillItem::PROJECT_ANALYZER_TRADE_ITEM,
					'level'                => 0,
					'uom_id'               => - 1,
					'uom_symbol'           => '',
					'grand_total_quantity' => 0,
					'grand_total'          => 0,
					'rate-value'           => 0,
					'rate-final_value'     => 0,
					'multi-rate'           => false,
				);

				foreach ( $formulatedColumnConstants as $formulatedColumnConstant )
				{
					$tradeItemInfo[$formulatedColumnConstant . '-value']        = '';
					$tradeItemInfo[$formulatedColumnConstant . '-final_value']  = 0;
					$tradeItemInfo[$formulatedColumnConstant . '-linked']       = false;
					$tradeItemInfo[$formulatedColumnConstant . '-has_formula']  = false;
					$tradeItemInfo[$formulatedColumnConstant . '-has_build_up'] = false;
				}

				$printPreviewItems[] = $tradeItemInfo;

				unset( $tradeItemInfo );

				foreach ( $bills as $bill )
				{
					$stmt = $pdo->prepare("SELECT DISTINCT be.id, be.description, be.priority FROM
					" . BillElementTable::getInstance()->getTableName() . " AS be JOIN
					" . BillItemTable::getInstance()->getTableName() . " AS bi ON be.id = bi.element_id JOIN
					" . BillItemFormulatedColumnTable::getInstance()->getTableName() . " AS bifc ON bi.id = bifc.relation_id JOIN
					" . ScheduleOfRateItemFormulatedColumnTable::getInstance()->getTableName() . " AS ifc ON bifc.schedule_of_rate_item_formulated_column_id = ifc.id
					WHERE be.project_structure_id = " . $bill['id'] . " AND ifc.relation_id = " . $tradeItem['id'] . "
					AND bifc.column_name = '" . BillItem::FORMULATED_COLUMN_RATE . "'
					AND ifc.deleted_at IS NULL AND bifc.deleted_at IS NULL AND bi.project_revision_deleted_at IS NULL
					AND bi.deleted_at IS NULL AND be.deleted_at IS NULL
					ORDER BY be.priority ASC");

					$stmt->execute();

					$elements = $stmt->fetchAll(PDO::FETCH_ASSOC);

					foreach ( $elements as $element )
					{
						$generatedBillHeader = false;

						$result = array(
							'id'               => 'bill-' . $bill['id'] . '-elem' . $element['id'] . '-billCount-' . $billCount,
							'description'      => $bill['title'] . " > " . $element['description'],
							'type'             => - 1,
							'level'            => 0,
							'uom_id'           => - 1,
							'uom_symbol'       => '',
							'rate-value'       => 0,
							'rate-final_value' => 0,
						);

						foreach ( $formulatedColumnConstants as $formulatedColumnConstant )
						{
							$result[$formulatedColumnConstant . '-value']       = '';
							$result[$formulatedColumnConstant . '-final_value'] = 0;
							$result[$formulatedColumnConstant . '-linked']      = false;
							$result[$formulatedColumnConstant . '-has_formula'] = false;
						}

						$billItem = array( 'id' => - 1 );

						foreach ( $items as $k => $item )
						{
							if ( $billItem['id'] != $item['id'] && $item['relation_id'] == $tradeItem['id'] && $item['element_id'] == $element['id'] )
							{
								if ( !$generatedBillHeader )
								{
									array_push($printPreviewItems, $result);

									$generatedBillHeader = true;
								}

								$grandTotal = number_format(0, 2, '.', '');

								if ( array_key_exists($awardedCompanyId, $tendererGrandTotal) and array_key_exists($item['id'], $tendererGrandTotal[$awardedCompanyId]) )
								{
									$grandTotal = $tendererGrandTotal[$awardedCompanyId][$item['id']];
								}

								$billItem['id']                   = $item['id'] . '-billCount-' . $billCount;
								$billItem['description']          = $item['description'];
								$billItem['type']                 = $item['type'];
								$billItem['grand_total']          = $grandTotal;
								$billItem['grand_total_quantity'] = $item['grand_total_quantity'];
								$billItem['level']                = $item['level'];
								$billItem['uom_symbol']           = $item['uom_id'] > 0 ? $item['uom_symbol'] : '';

								foreach ( $formulatedColumnConstants as $formulatedColumnConstant )
								{
									$billItem[$formulatedColumnConstant . '-value']        = '';
									$billItem[$formulatedColumnConstant . '-final_value']  = 0;
									$billItem[$formulatedColumnConstant . '-linked']       = false;
									$billItem[$formulatedColumnConstant . '-has_formula']  = false;
									$billItem[$formulatedColumnConstant . '-has_build_up'] = false;
								}

								foreach ( $formulatedColumns as $key => $formulatedColumn )
								{
									if ( $formulatedColumn['column_name'] == BillItem::FORMULATED_COLUMN_RATE )
									{
										$rate = number_format(0, 2, '.', '');

										if ( array_key_exists($awardedCompanyId, $tendererRates) and array_key_exists($item['id'], $tendererRates[$awardedCompanyId]) )
										{
											$rate = $tendererRates[$awardedCompanyId][$item['id']];
										}

										$columnName                              = $formulatedColumn['column_name'];
										$billItem[$columnName . '-value']        = $rate;
										$billItem[$columnName . '-final_value']  = $rate;
										$billItem[$columnName . '-linked']       = false;
										$billItem[$columnName . '-has_formula']  = false;
										$billItem[$columnName . '-has_build_up'] = false;
									}
									else if ( $formulatedColumn['relation_id'] == $item['id'] )
									{
										$columnName                              = $formulatedColumn['column_name'];
										$billItem[$columnName . '-value']        = $formulatedColumn['final_value'];
										$billItem[$columnName . '-final_value']  = $formulatedColumn['final_value'];
										$billItem[$columnName . '-linked']       = $formulatedColumn['linked'];
										$billItem[$columnName . '-has_formula']  = false;
										$billItem[$columnName . '-has_build_up'] = $formulatedColumn['has_build_up'];

										unset( $formulatedColumn, $formulatedColumns[$key] );
									}
								}

								array_push($printPreviewItems, $billItem);

								unset( $items[$k], $item );
							}
						}
					}

					$billCount ++;
				}
			}
		}

		$emptyRow = array(
			'id'                   => Constants::GRID_LAST_ROW,
			'description'          => '',
			'type'                 => BillItem::TYPE_WORK_ITEM,
			'level'                => 0,
			'uom_id'               => - 1,
			'uom_symbol'           => '',
			'grand_total_quantity' => 0,
			'grand_total'          => 0,
			'rate-value'           => 0,
			'rate-final_value'     => 0,
			'multi-rate'           => false,
		);

		foreach ( $formulatedColumnConstants as $formulatedColumnConstant )
		{
			$emptyRow[$formulatedColumnConstant . '-value']        = '';
			$emptyRow[$formulatedColumnConstant . '-final_value']  = 0;
			$emptyRow[$formulatedColumnConstant . '-linked']       = false;
			$emptyRow[$formulatedColumnConstant . '-has_formula']  = false;
			$emptyRow[$formulatedColumnConstant . '-has_build_up'] = false;
		}

		unset( $companies, $tendererRates );

		array_push($printPreviewItems, $emptyRow);

		return $this->renderJson(array(
			'identifier'     => 'id',
			'items'          => $printPreviewItems,
			'sum_total_qty'  => $sumTotalQuantity,
			'sum_total_cost' => $sumTotalCost
		));
	}

}