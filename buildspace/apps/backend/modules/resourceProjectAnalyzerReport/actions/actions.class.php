<?php

/**
 * resourceProjectAnalyzerReport actions.
 *
 * @package    buildspace
 * @subpackage resourceProjectAnalyzerReport
 * @author     1337 developers
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class resourceProjectAnalyzerReportActions extends BaseActions {

	public function executeGetAffectedSelectionTradeItemsAndBillItems(sfWebRequest $request)
	{
		$this->forward404Unless(
			$request->isXmlHttpRequest() AND
			$request->hasParameter('trade_ids') AND
			$project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('pid'))
		);

		$tradeIds               = array_filter(json_decode($request->getParameter('trade_ids'), true), "is_numeric");
		$pdo                    = $project->getTable()->getConnection()->getDbh();
		$data                   = array();
		$resourceItemLibraryIds = array();

		if ( !empty( $tradeIds ) )
		{
			// get trade item that is currently associated with the project
			$stmt = $pdo->prepare("SELECT DISTINCT bur.id, bur.resource_item_library_id FROM
			" . BillBuildUpRateResourceTable::getInstance()->getTableName() . " AS r JOIN
			" . BillBuildUpRateResourceTradeTable::getInstance()->getTableName() . " AS t ON t.build_up_rate_resource_id = r.id JOIN
			" . BillBuildUpRateItemTable::getInstance()->getTableName() . " AS bur ON bur.build_up_rate_resource_trade_id = t.id JOIN
			" . BillBuildUpRateFormulatedColumnTable::getInstance()->getTableName() . " AS ifc ON bur.id = ifc.relation_id JOIN
			" . BillItemTable::getInstance()->getTableName() . " AS i ON bur.bill_item_id = i.id JOIN
			" . BillElementTable::getInstance()->getTableName() . " AS e ON i.element_id = e.id JOIN
			" . ProjectStructureTable::getInstance()->getTableName() . " AS s ON e.project_structure_id = s.id
			WHERE s.root_id = " . $project->id . " AND t.resource_trade_library_id IN (" . implode(', ', $tradeIds) . ")
			AND bur.resource_item_library_id IS NOT NULL
			AND ifc.column_name = '" . BillBuildUpRateItem::FORMULATED_COLUMN_QUANTITY . "' AND ifc.final_value IS NOT NULL AND ifc.final_value <> 0
			AND r.deleted_at IS NULL AND t.deleted_at IS NULL AND bur.deleted_at IS NULL AND ifc.deleted_at IS NULL AND i.project_revision_deleted_at IS NULL
			AND i.deleted_at IS NULL AND e.deleted_at IS NULL AND s.deleted_at IS NULL");

			$stmt->execute(array());

			$buildUpRateItemWithResourceItemIds = $stmt->fetchAll(PDO::FETCH_ASSOC);

			if ( !empty( $buildUpRateItemWithResourceItemIds ) )
			{
				foreach ( $buildUpRateItemWithResourceItemIds as $record )
				{
					$resourceItemLibraryIds[$record['resource_item_library_id']] = $record['resource_item_library_id'];
				}

				$stmt = $pdo->prepare("SELECT DISTINCT p.id, p.resource_trade_id, p.root_id, p.level, p.priority, p.lft
				FROM " . ResourceItemTable::getInstance()->getTableName() . " c
				JOIN " . ResourceItemTable::getInstance()->getTableName() . " p
				ON c.lft BETWEEN p.lft AND p.rgt
				WHERE c.root_id = p.root_id AND c.type <> " . ResourceItem::TYPE_HEADER . "
				AND c.id IN (" . implode(',', $resourceItemLibraryIds) . ")
				AND c.resource_trade_id IN (" . implode(', ', $tradeIds) . ") AND p.resource_trade_id IN (" . implode(', ', $tradeIds) . ")
				AND c.deleted_at IS NULL AND p.deleted_at IS NULL
				ORDER BY p.root_id, p.priority, p.lft, p.level ASC");

				$stmt->execute(array());

				$resourceItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

				// get affected bill item(s)
				foreach ( $resourceItems as $resourceItem )
				{
					$data[$resourceItem['resource_trade_id']][$resourceItem['id']] = array();

					$stmt = $pdo->prepare("SELECT DISTINCT p.id, p.element_id, p.level, p.priority, p.lft
					FROM " . BillBuildUpRateResourceTable::getInstance()->getTableName() . " AS r
					JOIN " . BillBuildUpRateItemTable::getInstance()->getTableName() . " AS bur ON bur.build_up_rate_resource_id = r.id AND r.deleted_at IS NULL
					JOIN " . BillItemTable::getInstance()->getTableName() . " c  ON bur.bill_item_id = c.id AND bur.deleted_at IS NULL
					JOIN " . BillItemTable::getInstance()->getTableName() . " p ON c.lft BETWEEN p.lft AND p.rgt
					JOIN " . BillElementTable::getInstance()->getTableName() . " e ON p.element_id = e.id
					JOIN " . ProjectStructureTable::getInstance()->getTableName() . " s ON e.project_structure_id = s.id
					WHERE s.root_id = " . $project->id . " AND c.root_id = p.root_id AND c.element_id = p.element_id
					AND bur.resource_item_library_id = " . $resourceItem['id'] . "
					AND c.project_revision_deleted_at IS NULL AND c.deleted_at IS NULL
					AND p.project_revision_deleted_at IS NULL AND p.deleted_at IS NULL
					AND e.deleted_at IS NULL AND s.deleted_at IS NULL
					ORDER BY p.element_id, p.priority, p.lft, p.level ASC");

					$stmt->execute(array());

					$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

					foreach ( $items as $item )
					{
						$data[$resourceItem['resource_trade_id']][$resourceItem['id']][] = $item['id'];
					}
				}

				unset( $resourceItems );
			}

			unset( $buildUpRateItemWithResourceItemIds );
		}

		return $this->renderJson($data);
	}

	public function executeGetAffectedSelectionTradeAndBillItems(sfWebRequest $request)
	{
		$this->forward404Unless(
			$request->isXmlHttpRequest() AND
			$request->hasParameter('trade_item_ids') AND
			$project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('pid'))
		);

		$tradeItemIds = array_filter(json_decode($request->getParameter('trade_item_ids'), true), "is_numeric");
		$pdo          = $project->getTable()->getConnection()->getDbh();
		$data         = array();

		if ( !empty( $tradeItemIds ) )
		{
			$stmt = $pdo->prepare("SELECT DISTINCT p.id, p.resource_trade_id, p.root_id, p.level, p.priority, p.lft
			FROM " . ResourceItemTable::getInstance()->getTableName() . " c
			JOIN " . ResourceItemTable::getInstance()->getTableName() . " p
			ON c.lft BETWEEN p.lft AND p.rgt
			WHERE c.root_id = p.root_id AND c.type <> " . ResourceItem::TYPE_HEADER . "
			AND c.id IN (" . implode(',', $tradeItemIds) . ")
			AND c.deleted_at IS NULL AND p.deleted_at IS NULL
			ORDER BY p.root_id, p.priority, p.lft, p.level ASC");

			$stmt->execute(array());

			$resourceItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

			// get affected bill item(s)
			foreach ( $resourceItems as $resourceItem )
			{
				$data[$resourceItem['resource_trade_id']][$resourceItem['id']] = array();

				$stmt = $pdo->prepare("SELECT DISTINCT p.id, p.element_id, p.level, p.priority, p.lft
				FROM " . BillBuildUpRateResourceTable::getInstance()->getTableName() . " AS r
				JOIN " . BillBuildUpRateItemTable::getInstance()->getTableName() . " AS bur ON bur.build_up_rate_resource_id = r.id AND r.deleted_at IS NULL
				JOIN " . BillItemTable::getInstance()->getTableName() . " c  ON bur.bill_item_id = c.id AND bur.deleted_at IS NULL
				JOIN " . BillItemTable::getInstance()->getTableName() . " p ON c.lft BETWEEN p.lft AND p.rgt
				JOIN " . BillElementTable::getInstance()->getTableName() . " e ON p.element_id = e.id
				JOIN " . ProjectStructureTable::getInstance()->getTableName() . " s ON e.project_structure_id = s.id
				WHERE s.root_id = " . $project->id . " AND c.root_id = p.root_id AND c.element_id = p.element_id
				AND bur.resource_item_library_id = " . $resourceItem['id'] . "
				AND c.project_revision_deleted_at IS NULL AND c.deleted_at IS NULL
				AND p.project_revision_deleted_at IS NULL AND p.deleted_at IS NULL
				AND e.deleted_at IS NULL AND s.deleted_at IS NULL
				ORDER BY p.element_id, p.priority, p.lft, p.level ASC");

				$stmt->execute(array());

				$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

				foreach ( $items as $item )
				{
					$data[$resourceItem['resource_trade_id']][$resourceItem['id']][] = $item['id'];
				}
			}

			unset( $resourceItems );
		}

		return $this->renderJson($data);
	}

	public function executeGetAffectedSelectionTradeAndTradeItems(sfWebRequest $request)
	{
		$this->forward404Unless(
			$request->isXmlHttpRequest() AND
			$request->hasParameter('bill_item_ids') AND
			$project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('pid')) AND
			$trade = Doctrine_Core::getTable('ResourceTrade')->find($request->getParameter('trade_id')) AND
			$tradeItem = Doctrine_Core::getTable('ResourceItem')->find($request->getParameter('trade_item_id'))
		);

		$billItemIds = array_filter(json_decode($request->getParameter('bill_item_ids'), true), "is_numeric");
		$pdo         = $project->getTable()->getConnection()->getDbh();
		$data        = array();

		if ( !empty( $billItemIds ) )
		{
			$stmt = $pdo->prepare("SELECT DISTINCT c.id, c.element_id, c.level, c.priority, c.lft
			FROM " . BillItemTable::getInstance()->getTableName() . " c
			JOIN " . BillElementTable::getInstance()->getTableName() . " e ON c.element_id = e.id
			JOIN " . ProjectStructureTable::getInstance()->getTableName() . " s ON e.project_structure_id = s.id
			WHERE c.id IN (" . implode(',', $billItemIds) . ") AND s.root_id = " . $project->id . "
			AND c.project_revision_deleted_at IS NULL AND c.deleted_at IS NULL
			AND e.deleted_at IS NULL AND s.deleted_at IS NULL
			ORDER BY c.element_id, c.priority, c.lft, c.level ASC");

			$stmt->execute(array());

			$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

			foreach ( $items as $item )
			{
				$data[$trade->id][$tradeItem->id][] = $item['id'];
			}

			unset( $items );
		}

		return $this->renderJson($data);
	}

	public function executeGetPrintingSelectedTradeItems(sfWebRequest $request)
	{
		$this->forward404Unless(
			$request->isXmlHttpRequest() AND
			$request->hasParameter('trade_item_ids') AND
			$request->hasParameter('resourceId') AND
			$project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('pid'))
		);

		$tradeItemIds              = json_decode($request->getParameter('trade_item_ids'), true);
		$pdo                       = $project->getTable()->getConnection()->getDbh();
		$sumTotalQuantity          = 0;
		$sumTotalCost              = 0;
		$data                      = array();
		$formulatedColumnConstants = array( BillBuildUpRateItem::FORMULATED_COLUMN_RATE, BillBuildUpRateItem::FORMULATED_COLUMN_WASTAGE );

		/*
		* Query resource obj using PDO instead of Doctrine because we want to get the record even when the resource has been flagged as deleted(soft delete)
		*/
		$sth = $pdo->prepare("SELECT id FROM " . ResourceTable::getInstance()->getTableName() . " WHERE id = " . $request->getParameter('resourceId'));
		$sth->execute();

		$this->forward404Unless($resource = $sth->fetch(PDO::FETCH_ASSOC));

		if ( ! empty($tradeItemIds) )
		{
            $claimQuantities = ($project->PostContract->exists() && ($request->getParameter('type') == ProjectMainInformation::STATUS_POSTCONTRACT)) ? PostContractStandardClaimTable::getClaimQuantities($project->PostContract) : array();
            $totalResourceClaimQuantities = array();

			$stmt = $pdo->prepare("SELECT DISTINCT bur.id, bur.resource_item_library_id, t.resource_trade_library_id, bur.bill_item_id FROM
			" . BillBuildUpRateResourceTable::getInstance()->getTableName() . " AS r JOIN
			" . BillBuildUpRateResourceTradeTable::getInstance()->getTableName() . " AS t ON t.build_up_rate_resource_id = r.id JOIN
			" . BillBuildUpRateItemTable::getInstance()->getTableName() . " AS bur ON bur.build_up_rate_resource_trade_id = t.id JOIN
			" . BillBuildUpRateFormulatedColumnTable::getInstance()->getTableName() . " AS ifc ON bur.id = ifc.relation_id JOIN
			" . BillItemTable::getInstance()->getTableName() . " AS i ON bur.bill_item_id = i.id JOIN
			" . BillElementTable::getInstance()->getTableName() . " AS e ON i.element_id = e.id JOIN
			" . ProjectStructureTable::getInstance()->getTableName() . " AS s ON e.project_structure_id = s.id
			WHERE bur.resource_item_library_id IN (" . implode(', ', $tradeItemIds) . ") AND s.root_id = " . $project->id . " AND r.resource_library_id = " . $resource['id'] . "
			AND bur.resource_item_library_id IS NOT NULL
			AND ifc.column_name = '" . BillBuildUpRateItem::FORMULATED_COLUMN_QUANTITY . "' AND ifc.final_value IS NOT NULL AND ifc.final_value <> 0
			AND r.deleted_at IS NULL AND t.deleted_at IS NULL AND bur.deleted_at IS NULL AND ifc.deleted_at IS NULL AND i.project_revision_deleted_at IS NULL
			AND i.deleted_at IS NULL AND e.deleted_at IS NULL AND s.deleted_at IS NULL");

			$stmt->execute();

			$buildUpRateItemWithResourceItemIds = $stmt->fetchAll(PDO::FETCH_ASSOC);

			if ( ! empty($buildUpRateItemWithResourceItemIds) )
			{
				$tradeIds               = array();
				$buildUpRateItemIds     = array();
				$resourceItemLibraryIds = array();

				foreach ( $buildUpRateItemWithResourceItemIds as $record )
				{
					$tradeIds[]                                                     = $record['resource_trade_library_id'];
					$buildUpRateItemIds[$record['resource_trade_library_id']][]     = $record['id'];
					$resourceItemLibraryIds[$record['resource_trade_library_id']][] = $record['resource_item_library_id'];

                    $totalResourceClaimQuantities[$record['resource_item_library_id']] = ($totalResourceClaimQuantities[$record['resource_item_library_id']] ?? 0) + ($claimQuantities[$record['resource_item_library_id']][$record['bill_item_id']] ?? 0);
				}

				// query resource trade in order to get it's ordering and description
				$stmt = $pdo->prepare("SELECT t.id, t.description, t.priority FROM " . ResourceTradeTable::getInstance()->getTableName() . " t
				WHERE id IN (" . implode(',', array_unique($tradeIds)) . ") AND t.deleted_at IS NULL ORDER BY t.priority");

				$stmt->execute();

				$trades = $stmt->fetchAll(PDO::FETCH_ASSOC);

				foreach ( $trades as $trade )
				{
					$totalCostAndQuantityByResourceItems = ResourceItemTable::calculateTotalForResourceAnalysis(array_unique($resourceItemLibraryIds[$trade['id']]), $resource['id'], $project->id);

					$tradeInformation = array(
						'id'            => 'trade-' . $trade['id'],
						'description'   => $trade['description'],
						'uom_symbol'    => '',
						'type'          => 0,
						'total_qty'     => 0,
						'total_cost'    => 0,
						'multi-rate'    => false,
						'multi-wastage' => false,
					);

					foreach ( $formulatedColumnConstants as $formulatedColumnConstant )
					{
						$tradeInformation[$formulatedColumnConstant . '-value']       = '';
						$tradeInformation[$formulatedColumnConstant . '-final_value'] = 0;
						$tradeInformation[$formulatedColumnConstant . '-linked']      = false;
						$tradeInformation[$formulatedColumnConstant . '-has_formula'] = false;
					}

					$data[] = $tradeInformation;

					unset( $tradeInformation );

					$stmt = $pdo->prepare("SELECT DISTINCT p.id, p.root_id, p.description, p.type, p.uom_id, p.level, p.priority, p.lft, uom.symbol AS uom_symbol
					FROM " . ResourceItemTable::getInstance()->getTableName() . " c
					JOIN " . ResourceItemTable::getInstance()->getTableName() . " p
					ON c.lft BETWEEN p.lft AND p.rgt
					LEFT JOIN " . UnitOfMeasurementTable::getInstance()->getTableName() . " uom ON p.uom_id = uom.id AND uom.deleted_at IS NULL
					WHERE c.root_id = p.root_id AND c.type <> " . ResourceItem::TYPE_HEADER . "
					AND c.id IN (" . implode(',', array_unique($resourceItemLibraryIds[$trade['id']])) . ")
					AND c.deleted_at IS NULL AND p.deleted_at IS NULL
					ORDER BY p.root_id, p.priority, p.lft, p.level ASC");

					$stmt->execute();

					$resourceItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

					$stmt = $pdo->prepare("SELECT bur.resource_item_library_id, ifc.column_name, ifc.final_value FROM
					" . BillBuildUpRateFormulatedColumnTable::getInstance()->getTableName() . " AS ifc JOIN
					" . BillBuildUpRateItemTable::getInstance()->getTableName() . " AS bur ON ifc.relation_id = bur.id JOIN
					" . BillItemTable::getInstance()->getTableName() . " AS i ON bur.bill_item_id = i.id JOIN
					" . BillElementTable::getInstance()->getTableName() . " AS e ON i.element_id = e.id JOIN
					" . ProjectStructureTable::getInstance()->getTableName() . " AS s ON e.project_structure_id = s.id
					WHERE s.root_id = " . $project->id . " AND bur.id IN (" . implode(',', array_unique($buildUpRateItemIds[$trade['id']])) . ")
					AND (ifc.column_name = '" . BillBuildUpRateItem::FORMULATED_COLUMN_RATE . "' OR ifc.column_name = '" . BillBuildUpRateItem::FORMULATED_COLUMN_WASTAGE . "')
					AND ifc.deleted_at IS NULL AND bur.deleted_at IS NULL AND i.project_revision_deleted_at IS NULL
					AND i.deleted_at IS NULL AND e.deleted_at IS NULL
					GROUP BY bur.resource_item_library_id, ifc.column_name, ifc.final_value
					ORDER BY bur.resource_item_library_id");

					$stmt->execute();

					$formulatedColumnNames = $stmt->fetchAll(PDO::FETCH_COLUMN | PDO::FETCH_GROUP);

					$stmt->execute();
					$formulatedColumns = $stmt->fetchAll(PDO::FETCH_ASSOC);

					foreach ( $resourceItems as $key => $item )
					{
						$multiRate     = false;
						$multiWastage  = false;
						$totalQuantity = 0;
						$totalCost     = 0;

						foreach ( $formulatedColumnConstants as $formulatedColumnConstant )
						{
							$item[$formulatedColumnConstant . '-value']       = '';
							$item[$formulatedColumnConstant . '-final_value'] = number_format(0, 2, '.', '');
							$item[$formulatedColumnConstant . '-linked']      = false;
							$item[$formulatedColumnConstant . '-has_formula'] = false;
						}

						if ( array_key_exists($item['id'], $formulatedColumnNames) )
						{
							$columnNames = array_count_values($formulatedColumnNames[$item['id']]);

							if ( array_key_exists(ResourceItem::FORMULATED_COLUMN_RATE, $columnNames) && $columnNames[ResourceItem::FORMULATED_COLUMN_RATE] > 1 )
							{
								$item[ResourceItem::FORMULATED_COLUMN_RATE . '-value']       = '';
								$item[ResourceItem::FORMULATED_COLUMN_RATE . '-final_value'] = 0;
								$multiRate                                                   = true;
							}
							else
							{
								foreach ( $formulatedColumns as $formulatedColumn )
								{
									$columnName = $formulatedColumn['column_name'];
									if ( $formulatedColumn['resource_item_library_id'] == $item['id'] and $columnName == ResourceItem::FORMULATED_COLUMN_RATE )
									{
										$finalValue                         = $formulatedColumn['final_value'] ? $formulatedColumn['final_value'] : number_format(0, 2, '.', '');
										$item[$columnName . '-value']       = $finalValue;
										$item[$columnName . '-final_value'] = $finalValue;

										break 1;
									}
								}
							}

							if ( array_key_exists(ResourceItem::FORMULATED_COLUMN_WASTAGE, $columnNames) && $columnNames[ResourceItem::FORMULATED_COLUMN_WASTAGE] > 1 )
							{
								$item[ResourceItem::FORMULATED_COLUMN_WASTAGE . '-value']       = '';
								$item[ResourceItem::FORMULATED_COLUMN_WASTAGE . '-final_value'] = 0;
								$multiWastage                                                   = true;
							}
							else
							{
								foreach ( $formulatedColumns as $formulatedColumn )
								{
									$columnName = $formulatedColumn['column_name'];
									if ( $formulatedColumn['resource_item_library_id'] == $item['id'] and $columnName == ResourceItem::FORMULATED_COLUMN_WASTAGE )
									{
										$finalValue                         = $formulatedColumn['final_value'] ? $formulatedColumn['final_value'] : number_format(0, 2, '.', '');
										$item[$columnName . '-value']       = $finalValue;
										$item[$columnName . '-final_value'] = $finalValue;

										break 1;
									}
								}
							}
						}

						if ( $item['type'] == ResourceItem::TYPE_WORK_ITEM && array_key_exists($item['id'], $totalCostAndQuantityByResourceItems) )
						{
							$totalCost     = $totalCostAndQuantityByResourceItems[$item['id']]['total_cost'];
							$totalQuantity = $totalCostAndQuantityByResourceItems[$item['id']]['total_quantity'];
						}

						$item['total_qty']     = $totalQuantity;
						$item['total_cost']    = $totalCost;
						$item['multi-rate']    = $multiRate;
						$item['multi-wastage'] = $multiWastage;

                        $item['claim_quantity'] = ($totalResourceClaimQuantities[$item['id']] ?? 0);
                        $item['claim_amount'] = $item['claim_quantity'] * $item[BillBuildUpRateItem::FORMULATED_COLUMN_RATE.'-final_value'];

						$sumTotalQuantity += $totalQuantity;
						$sumTotalCost += $totalCost;

						array_push($data, $item);
						unset( $resourceItems[$key] );
					}

					unset( $resourceItems );
				}
			}
		}

		$emptyRow = array(
			'id'            => Constants::GRID_LAST_ROW,
			'description'   => '',
			'uom_symbol'    => '',
			'total_qty'     => 0,
			'total_cost'    => 0,
			'multi-rate'    => false,
			'multi-wastage' => false
		);

		foreach ( $formulatedColumnConstants as $formulatedColumnConstant )
		{
			$emptyRow[$formulatedColumnConstant . '-value']       = '';
			$emptyRow[$formulatedColumnConstant . '-final_value'] = 0;
			$emptyRow[$formulatedColumnConstant . '-linked']      = false;
			$emptyRow[$formulatedColumnConstant . '-has_formula'] = false;
		}

		array_push($data, $emptyRow);

		return $this->renderJson(array(
			'identifier' => 'id',
			'items'      => $data,
		));
	}

	public function executeGetPrintingSelectedBillItems(sfWebRequest $request)
	{
		$this->forward404Unless(
			$request->isXmlHttpRequest() AND
			$request->hasParameter('bill_item_ids') AND
			$project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('pid')) AND
			$trade = Doctrine_Core::getTable('ResourceTrade')->find($request->getParameter('trade_id'))
		);

		$pdo          = $project->getTable()->getConnection()->getDbh();
		$tradeItemIds = json_decode($request->getParameter('tradeItemIds'), true);
		$billItemIds  = json_decode($request->getParameter('bill_item_ids'), true);
		$data         = array();

		// used to differentiate bill if there is similar bill occur.
		$billCount = 1;

		$formulatedColumnConstants = array(
			BillBuildUpRateItem::FORMULATED_COLUMN_RATE,
			BillBuildUpRateItem::FORMULATED_COLUMN_QUANTITY,
			BillBuildUpRateItem::FORMULATED_COLUMN_WASTAGE,
		);

		/*
		* Query resource obj using PDO instead of Doctrine because we want to get the record even when the resource has been flagged as deleted(soft delete)
		*/
		$sth = $pdo->prepare("SELECT id FROM " . ResourceTable::getInstance()->getTableName() . " WHERE id = " . $request->getParameter('resource_id'));
		$sth->execute();

		$this->forward404Unless($resource = $sth->fetch(PDO::FETCH_ASSOC));

		if ( ! empty($billItemIds) )
		{
			$stmt = $pdo->prepare("SELECT DISTINCT p.id, p.element_id, p.root_id, p.description, p.type, p.uom_id, p.grand_total, p.grand_total_quantity, bur.resource_item_library_id, p.level, p.priority, p.lft
			FROM " . BillBuildUpRateResourceTable::getInstance()->getTableName() . " AS r
			JOIN " . BillBuildUpRateItemTable::getInstance()->getTableName() . " AS bur ON bur.build_up_rate_resource_id = r.id AND r.deleted_at IS NULL
			JOIN " . BillItemTable::getInstance()->getTableName() . " c ON bur.bill_item_id = c.id AND bur.deleted_at IS NULL
			JOIN " . BillItemTable::getInstance()->getTableName() . " p ON c.lft BETWEEN p.lft AND p.rgt
			JOIN " . BillElementTable::getInstance()->getTableName() . " e ON p.element_id = e.id
			JOIN " . ProjectStructureTable::getInstance()->getTableName() . " s ON e.project_structure_id = s.id
			WHERE c.id IN (" . implode(', ', $billItemIds) . ") AND s.root_id = " . $project->id . "
			AND c.root_id = p.root_id AND c.element_id = p.element_id
			AND r.resource_library_id = " . $resource['id'] . " AND bur.resource_item_library_id IS NOT NULL
			AND c.project_revision_deleted_at IS NULL AND c.deleted_at IS NULL
			AND p.project_revision_deleted_at IS NULL AND p.deleted_at IS NULL
			AND e.deleted_at IS NULL AND s.deleted_at IS NULL
			ORDER BY p.element_id, p.priority, p.lft, p.level ASC");

			$stmt->execute();

			$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

			/*
			 * select elements
			 */
			$elementQuery = $pdo->prepare("SELECT DISTINCT e.id, e.description, e.priority FROM
			" . BillElementTable::getInstance()->getTableName() . " AS e JOIN
			" . BillItemTable::getInstance()->getTableName() . " AS i ON i.element_id = e.id JOIN
			" . BillBuildUpRateResourceTable::getInstance()->getTableName() . " AS r ON r.bill_item_id = i.id JOIN
			" . BillBuildUpRateItemTable::getInstance()->getTableName() . " AS bur ON bur.build_up_rate_resource_id = r.id
			WHERE i.id IN (" . implode(',', $billItemIds) . ") AND e.project_structure_id = :bill_id
			AND r.resource_library_id = " . $resource['id'] . " AND bur.resource_item_library_id = :resourceItemId
			AND e.deleted_at IS NULL AND i.project_revision_deleted_at IS NULL AND i.deleted_at IS NULL AND bur.deleted_at IS NULL ORDER BY e.priority ASC");

			$formulatedColumnQuery = $pdo->prepare("SELECT bur.bill_item_id, bur.uom_id, uom.symbol AS uom_symbol, ifc.column_name, ifc.value, ifc.final_value, ifc.linked
			FROM " . BillBuildUpRateFormulatedColumnTable::getInstance()->getTableName() . " AS ifc
			JOIN " . BillBuildUpRateItemTable::getInstance()->getTableName() . " AS bur ON ifc.relation_id = bur.id
			LEFT JOIN " . UnitOfMeasurementTable::getInstance()->getTableName() . " AS uom ON bur.uom_id = uom.id AND uom.deleted_at IS NULL
			JOIN " . BillBuildUpRateResourceTable::getInstance()->getTableName() . " AS r ON bur.build_up_rate_resource_id = r.id
			JOIN " . BillItemTable::getInstance()->getTableName() . " AS i ON r.bill_item_id = i.id
			JOIN " . BillElementTable::getInstance()->getTableName() . " AS e ON i.element_id = e.id
			JOIN " . ProjectStructureTable::getInstance()->getTableName() . " AS s ON e.project_structure_id = s.id
			WHERE i.id IN (" . implode(',', $billItemIds) . ") AND s.root_id = " . $project->id . "
			AND r.resource_library_id = " . $resource['id'] . " AND bur.resource_item_library_id = :resourceItemId
			AND ifc.column_name NOT IN ('" . BillBuildUpRateItem::FORMULATED_COLUMN_NUMBER . "', '" . BillBuildUpRateItem::FORMULATED_COLUMN_CONSTANT . "')
			AND s.deleted_at IS NULL AND e.deleted_at IS NULL AND i.project_revision_deleted_at IS NULL AND i.deleted_at IS NULL
			AND ifc.deleted_at IS NULL AND bur.deleted_at IS NULL ORDER BY bur.bill_item_id");

			$billQuery = $pdo->prepare("SELECT DISTINCT s.id, s.title, s.lft FROM " . ProjectStructureTable::getInstance()->getTableName() . " AS s JOIN
			" . BillElementTable::getInstance()->getTableName() . " AS e ON e.project_structure_id = s.id JOIN
			" . BillItemTable::getInstance()->getTableName() . " AS i ON i.element_id = e.id JOIN
			" . BillBuildUpRateResourceTable::getInstance()->getTableName() . " AS r ON r.bill_item_id = i.id JOIN
			" . BillBuildUpRateItemTable::getInstance()->getTableName() . " AS bur ON bur.build_up_rate_resource_id = r.id
			WHERE i.id IN (" . implode(',', $billItemIds) . ") AND s.root_id = " . $project->id . "
			AND r.resource_library_id = " . $resource['id'] . " AND bur.resource_item_library_id = :resourceItemId
			AND s.deleted_at IS NULL AND e.deleted_at IS NULL AND i.project_revision_deleted_at IS NULL AND i.deleted_at IS NULL
			AND bur.deleted_at IS NULL ORDER BY s.lft ASC");

			// get resource item's information and ordering
			$resourceItemsQuery = $pdo->prepare("SELECT DISTINCT ri.id, ri.description, ri.priority FROM " . ResourceItemTable::getInstance()->getTableName() . " ri
			WHERE ri.id IN (" . implode(', ', $tradeItemIds) . ") AND ri.deleted_at IS NULL ORDER BY ri.priority");

			$resourceItemsQuery->execute();

			$resourceItems = $resourceItemsQuery->fetchAll(PDO::FETCH_ASSOC);

			foreach ( $resourceItems as $resourceItem )
			{
				$totalCostAndQuantity = BillItemTable::calculateTotalForResourceAnalysis($resource['id'], $resourceItem['id'], null, false);

				$resourceItemInfo = array(
					'id'          => 'tradeItem-' . $resourceItem['id'],
					'description' => $resourceItem['description'],
					'type'        => 'tradeItem',
					'level'       => 0,
					'uom_id'      => - 1,
					'uom_symbol'  => ''
				);

				foreach ( $formulatedColumnConstants as $formulatedColumnConstant )
				{
					$resourceItemInfo[$formulatedColumnConstant . '-value']        = '';
					$resourceItemInfo[$formulatedColumnConstant . '-final_value']  = 0;
					$resourceItemInfo[$formulatedColumnConstant . '-linked']       = false;
					$resourceItemInfo[$formulatedColumnConstant . '-has_formula']  = false;
					$resourceItemInfo[$formulatedColumnConstant . '-has_build_up'] = false;
				}

				/*
				* get rate and wastage from build up rate item
				*/

				$formulatedColumnQuery->execute(array( 'resourceItemId' => $resourceItem['id'] ));

				$formulatedColumnRecords = $formulatedColumnQuery->fetchAll(PDO::FETCH_ASSOC);

				/*
				 * select bills
				 */
				$billQuery->execute(array( 'resourceItemId' => $resourceItem['id'] ));

				$bills = $billQuery->fetchAll(PDO::FETCH_ASSOC);

				if ( count($bills) > 0 )
				{
					$data[] = $resourceItemInfo;

					unset( $resourceItemInfo );
				}

				$buildUpRateItemId = null;
				$sumTotalQuantity  = 0;
				$sumTotalCost      = 0;

				$formulatedColumns = array();

				foreach ( $formulatedColumnRecords as $k => $formulatedColumn )
				{
					if ( !array_key_exists($formulatedColumn['bill_item_id'], $formulatedColumns) )
					{
						$formulatedColumns[$formulatedColumn['bill_item_id']] = array();
					}

					$columnName = $formulatedColumn['column_name'];

					$formulatedColumns[$formulatedColumn['bill_item_id']][] = array(
						'column_name'                => $columnName,
						'uom_symbol'                 => $formulatedColumn['uom_symbol'],
						$columnName . '-value'       => $formulatedColumn['final_value'],
						$columnName . '-final_value' => $formulatedColumn['final_value'],
						$columnName . '-linked'      => $formulatedColumn['linked']
					);

					unset( $formulatedColumn, $formulatedColumnRecords[$k] );
				}

				foreach ( $bills as $bill )
				{
					$elementQuery->execute(array(
						'bill_id'        => $bill['id'],
						'resourceItemId' => $resourceItem['id'],
					));

					$elements = $elementQuery->fetchAll(PDO::FETCH_ASSOC);

					foreach ( $elements as $element )
					{
						$result = array(
							'id'          => 'bill-' . $bill['id'] . '-elem' . $element['id'] . '-billcount' . $billCount,
							'description' => $bill['title'] . " > " . $element['description'],
							'type'        => - 1,
							'level'       => 0,
							'uom_id'      => - 1,
							'uom_symbol'  => ''
						);

						foreach ( $formulatedColumnConstants as $formulatedColumnConstant )
						{
							$result[$formulatedColumnConstant . '-value']        = '';
							$result[$formulatedColumnConstant . '-final_value']  = 0;
							$result[$formulatedColumnConstant . '-linked']       = false;
							$result[$formulatedColumnConstant . '-has_formula']  = false;
							$result[$formulatedColumnConstant . '-has_build_up'] = false;
						}

						array_push($data, $result);

						$billItem['id'] = - 1;

						foreach ( $items as $key => $item )
						{
							if ( $billItem['id'] != $item['id'] && $item['element_id'] == $element['id'] && $item['resource_item_library_id'] == $resourceItem['id'] )
							{
								$billItem['id']                   = $item['id'] . '-billcount' . $billCount;
								$billItem['description']          = $item['description'];
								$billItem['type']                 = $item['type'];
								$billItem['grand_total']          = $item['grand_total'];
								$billItem['grand_total_quantity'] = $item['grand_total_quantity'];
								$billItem['level']                = $item['level'];
								$billItem['uom_symbol']           = '';

								foreach ( $formulatedColumnConstants as $formulatedColumnConstant )
								{
									$billItem[$formulatedColumnConstant . '-value']        = '';
									$billItem[$formulatedColumnConstant . '-final_value']  = 0;
									$billItem[$formulatedColumnConstant . '-linked']       = false;
									$billItem[$formulatedColumnConstant . '-has_formula']  = false;
									$billItem[$formulatedColumnConstant . '-has_build_up'] = false;
								}

								if ( array_key_exists($item['id'], $formulatedColumns) )
								{
									foreach ( $formulatedColumns[$item['id']] as $formulatedColumn )
									{
										$columnName             = $formulatedColumn['column_name'];
										$billItem['uom_symbol'] = $formulatedColumn['uom_symbol'];

										$billItem[$columnName . '-value']       = $formulatedColumn[$columnName . '-value'];
										$billItem[$columnName . '-final_value'] = $formulatedColumn[$columnName . '-final_value'];
										$billItem[$columnName . '-linked']      = $formulatedColumn[$columnName . '-linked'];
									}

									unset( $formulatedColumn, $formulatedColumns[$item['id']] );
								}

								$totalQuantity = 0;
								$totalCost     = 0;

								if ( array_key_exists($item['id'], $totalCostAndQuantity) and $item['grand_total_quantity'] != '' and $item['grand_total_quantity'] != 0 and $item['type'] != BillItem::TYPE_HEADER and $item['type'] != BillItem::TYPE_HEADER_N and $item['type'] != BillItem::TYPE_NOID )
								{
									$totalCost     = $totalCostAndQuantity[$item['id']]['total_cost'];
									$totalQuantity = $totalCostAndQuantity[$item['id']]['total_quantity'];

									unset( $totalCostAndQuantity[$item['id']] );
								}

								$billItem['total_qty']  = $totalQuantity;
								$billItem['total_cost'] = $totalCost;

								$sumTotalQuantity += $totalQuantity;
								$sumTotalCost += $totalCost;

								array_push($data, $billItem);

								unset( $item, $items[$key] );
							}
						}

						unset( $result );
					}

					unset( $elements );

					$billCount ++;
				}

				unset( $bills );
			}

			unset( $resourceItems );
		}

		unset( $billItemIds );

		$emptyRow = array(
			'id'          => Constants::GRID_LAST_ROW,
			'description' => '',
			'type'        => BillItem::TYPE_WORK_ITEM,
			'level'       => 0,
			'uom_id'      => - 1,
			'uom_symbol'  => '',
			'total_qty'   => 0,
			'total_cost'  => 0
		);

		foreach ( $formulatedColumnConstants as $formulatedColumnConstant )
		{
			$emptyRow[$formulatedColumnConstant . '-value']        = '';
			$emptyRow[$formulatedColumnConstant . '-final_value']  = 0;
			$emptyRow[$formulatedColumnConstant . '-linked']       = false;
			$emptyRow[$formulatedColumnConstant . '-has_formula']  = false;
			$emptyRow[$formulatedColumnConstant . '-has_build_up'] = false;
		}

		$data[] = $emptyRow;

		return $this->renderJson(array(
			'identifier' => 'id',
			'items'      => $data
		));
	}

}