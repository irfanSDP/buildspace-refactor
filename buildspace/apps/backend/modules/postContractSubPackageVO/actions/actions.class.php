<?php

/**
 * postContractSubPackageVO actions.
 *
 * @package    buildspace
 * @subpackage postContractSubPackageVO
 * @author     1337 developers
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class postContractSubPackageVOActions extends BaseActions {

	public function executeGetPrintingSelectedVO(sfWebRequest $request)
	{
		$this->forward404Unless(
			$request->isXmlHttpRequest() AND
			$request->hasParameter('vo_ids') AND
			$subPackage = Doctrine_Core::getTable('SubPackage')->find($request->getParameter('subPackageId'))
		);

		$voIds   = json_decode($request->getParameter('vo_ids'), true);
		$records = array();

		if ( !empty( $voIds ) )
		{
			$pdo = $subPackage->getTable()->getConnection()->getDbh();

			$records = Doctrine_Query::create()
				->select('vo.id, vo.description, vo.is_approved, vo.updated_at')
				->from('SubPackageVariationOrder vo')
				->andWhere('vo.sub_package_id = ?', $subPackage->id)
				->andWhereIn('vo.id', $voIds)
				->addOrderBy('vo.priority ASC')
				->fetchArray();

			$stmt = $pdo->prepare("SELECT vo.id, COALESCE(COUNT(c.id), 0)
			FROM " . SubPackageVariationOrderTable::getInstance()->getTableName() . " vo
			LEFT JOIN " . SubPackageVariationOrderClaimTable::getInstance()->getTableName() . " c ON c.sub_package_variation_order_id = vo.id AND c.deleted_at IS NULL
			WHERE vo.sub_package_id = " . $subPackage->id . " AND vo.deleted_at IS NULL
			GROUP BY vo.id ORDER BY vo.priority");

			$stmt->execute();
			$claimCount = $stmt->fetchAll(PDO::FETCH_COLUMN | PDO::FETCH_GROUP);

			$stmt = $pdo->prepare("SELECT i.sub_package_variation_order_id, ROUND(COALESCE(SUM(i.total_unit * i.omission_quantity * i.rate), 0), 2) AS omission,
			ROUND(COALESCE(SUM(i.total_unit * i.addition_quantity * i.rate), 0), 2) AS addition,
			ROUND(COALESCE(SUM((i.total_unit * i.addition_quantity * i.rate) - (i.total_unit * i.omission_quantity * i.rate))), 2) AS nett_omission_addition
			FROM " . SubPackageVariationOrderItemTable::getInstance()->getTableName() . " i
			JOIN " . SubPackageVariationOrderTable::getInstance()->getTableName() . " vo ON i.sub_package_variation_order_id = vo.id
			WHERE vo.sub_package_id = " . $subPackage->id . " AND i.type <> " . VariationOrderItem::TYPE_HEADER . " AND i.rate <> 0
			AND vo.deleted_at IS NULL AND i.deleted_at IS NULL GROUP BY i.sub_package_variation_order_id");

			$stmt->execute();
			$quantities = $stmt->fetchAll(PDO::FETCH_ASSOC);

			$stmt = $pdo->prepare("SELECT vo.id AS sub_package_variation_order_id, ROUND(COALESCE(SUM(i.up_to_date_amount), 0), 2) AS amount
			FROM " . SubPackageVariationOrderTable::getInstance()->getTableName() . " vo
			JOIN " . SubPackageVariationOrderClaimTable::getInstance()->getTableName() . " c ON c.sub_package_variation_order_id = vo.id
			JOIN " . SubPackageVariationOrderClaimItemTable::getInstance()->getTableName() . " i ON i.sub_package_variation_order_claim_id = c.id
			WHERE vo.sub_package_id = " . $subPackage->id . " AND c.is_viewing IS TRUE
			AND vo.deleted_at IS NULL AND c.deleted_at IS NULL AND i.deleted_at IS NULL GROUP BY vo.id");

			$stmt->execute();
			$upToDateClaims = $stmt->fetchAll(PDO::FETCH_ASSOC);

			foreach ( $records as $key => $record )
			{
				$records[$key]['omission'] = 0;
				$records[$key]['addition'] = 0;

				foreach ( $quantities as $quantity )
				{
					if ( $quantity['sub_package_variation_order_id'] == $record['id'] )
					{
						$records[$key]['omission'] = $quantity['omission'];
						$records[$key]['addition'] = $quantity['addition'];

						unset( $quantity );
					}
				}

				unset( $record );
			}

			if ( !empty( $records ) )
			{
				$headerRow = array(
					'id'          => 'row-header',
					'description' => 'Variation Order Summary',
					'type'        => 0,
					'omission'    => 0,
					'addition'    => 0,
				);

				array_unshift($records, $headerRow);

				unset( $headerRow );
			}

			unset( $claimCount, $quantities, $upToDateClaims );
		}

		//default last row
		array_push($records, array(
			'id'          => Constants::GRID_LAST_ROW,
			'description' => '',
			'omission'    => 0,
			'addition'    => 0,
		));

		return $this->renderJson(array(
			'identifier' => 'id',
			'items'      => $records
		));
	}

	public function executeGetPrintingVOWithClaims(sfWebRequest $request)
	{
		$this->forward404Unless(
			$request->isXmlHttpRequest() AND
			$subPackage = Doctrine_Core::getTable('SubPackage')->find($request->getParameter('subPackageId'))
		);

		$data = array();
		$pdo  = $subPackage->getTable()->getConnection()->getDbh();

		$records = Doctrine_Query::create()
			->select('vo.id, vo.description, vo.is_approved, vo.updated_at')
			->from('SubPackageVariationOrder vo')
			->andWhere('vo.sub_package_id = ?', $subPackage->id)
			->addOrderBy('vo.priority ASC')
			->fetchArray();

		$stmt = $pdo->prepare("SELECT vo.id, COALESCE(COUNT(c.id), 0)
		FROM " . SubPackageVariationOrderTable::getInstance()->getTableName() . " vo
		LEFT JOIN " . SubPackageVariationOrderClaimTable::getInstance()->getTableName() . " c ON c.sub_package_variation_order_id = vo.id AND c.deleted_at IS NULL
		WHERE vo.sub_package_id = " . $subPackage->id . " AND vo.deleted_at IS NULL
		GROUP BY vo.id ORDER BY vo.priority");

		$stmt->execute();
		$claimCount = $stmt->fetchAll(PDO::FETCH_COLUMN | PDO::FETCH_GROUP);

		$stmt = $pdo->prepare("SELECT i.sub_package_variation_order_id, ROUND(COALESCE(SUM(i.total_unit * i.omission_quantity * i.rate), 0), 2) AS omission,
		ROUND(COALESCE(SUM(i.total_unit * i.addition_quantity * i.rate), 0), 2) AS addition,
		ROUND(COALESCE(SUM((i.total_unit * i.addition_quantity * i.rate) - (i.total_unit * i.omission_quantity * i.rate))), 2) AS nett_omission_addition
		FROM " . SubPackageVariationOrderItemTable::getInstance()->getTableName() . " i
		JOIN " . SubPackageVariationOrderTable::getInstance()->getTableName() . " vo ON i.sub_package_variation_order_id = vo.id
		WHERE vo.sub_package_id = " . $subPackage->id . " AND i.type <> " . VariationOrderItem::TYPE_HEADER . " AND i.rate <> 0
		AND vo.deleted_at IS NULL AND i.deleted_at IS NULL GROUP BY i.sub_package_variation_order_id");

		$stmt->execute();
		$quantities = $stmt->fetchAll(PDO::FETCH_ASSOC);

		$stmt = $pdo->prepare("SELECT vo.id AS sub_package_variation_order_id, ROUND(COALESCE(SUM(i.up_to_date_amount), 0), 2) AS amount, ROUND(COALESCE(SUM(i.up_to_date_percentage), 0), 2) AS up_to_date_percentage
		FROM " . SubPackageVariationOrderTable::getInstance()->getTableName() . " vo
		JOIN " . SubPackageVariationOrderClaimTable::getInstance()->getTableName() . " c ON c.sub_package_variation_order_id = vo.id
		JOIN " . SubPackageVariationOrderClaimItemTable::getInstance()->getTableName() . " i ON i.sub_package_variation_order_claim_id = c.id
		WHERE vo.sub_package_id = " . $subPackage->id . " AND c.is_viewing IS TRUE
		AND vo.deleted_at IS NULL AND c.deleted_at IS NULL AND i.deleted_at IS NULL GROUP BY vo.id");

		$stmt->execute();
		$upToDateClaims = $stmt->fetchAll(PDO::FETCH_ASSOC);

		foreach ( $records as $key => $record )
		{
			$records[$key]['omission']               = 0;
			$records[$key]['addition']               = 0;
			$records[$key]['nett_omission_addition'] = 0;

			foreach ( $quantities as $quantity )
			{
				if ( $quantity['sub_package_variation_order_id'] == $record['id'] )
				{
					$records[$key]['omission']               = $quantity['omission'];
					$records[$key]['addition']               = $quantity['addition'];
					$records[$key]['nett_omission_addition'] = $quantity['nett_omission_addition'];

					unset( $quantity );
				}
			}

			foreach ( $upToDateClaims as $upToDateClaim )
			{
				if ( $upToDateClaim['sub_package_variation_order_id'] == $record['id'] )
				{
					$records[$key]['total_claim']           = $upToDateClaim['amount'];
					$records[$key]['up_to_date_percentage'] = ( $records[$key]['total_claim'] != 0 ) ? Utilities::percent($records[$key]['total_claim'], $records[$key]['nett_omission_addition']) : 0;

					unset( $upToDateClaim );
				}
			}

			if ( isset( $records[$key]['total_claim'] ) AND $records[$key]['total_claim'] > 0 )
			{
				$data[] = $records[$key];
			}

			unset( $record );
		}

		if ( !empty( $data ) )
		{
			$headerRow = array(
				'id'          => 'row-header',
				'description' => 'Variation Order Summary',
				'type'        => 0,
				'omission'    => 0,
				'addition'    => 0,
			);

			array_unshift($data, $headerRow);

			unset( $headerRow );
		}

		unset( $records, $claimCount, $quantities, $upToDateClaims );

		//default last row
		array_push($data, array(
			'id'                     => Constants::GRID_LAST_ROW,
			'description'            => '',
			'omission'               => 0,
			'addition'               => 0,
			'nett_omission_addition' => 0,
		));

		return $this->renderJson(array(
			'identifier' => 'id',
			'items'      => $data,
		));
	}

	public function executeGetPrintingSelectedVOItemsDialog(sfWebRequest $request)
	{
		$this->forward404Unless(
			$request->isXmlHttpRequest() AND
			$request->hasParameter('item_ids') AND
			$subPackage = Doctrine_Core::getTable('SubPackage')->find($request->getParameter('subPackageId'))
		);

		$pdo     = $subPackage->getTable()->getConnection()->getDbh();
		$itemIds = json_decode($request->getParameter('item_ids'), true);
		$data    = array();
		$voIds   = array();

		if ( !empty( $itemIds ) )
		{
			$stmt = $pdo->prepare("SELECT DISTINCT p.id, p.sub_package_variation_order_id, p.description, p.type, p.priority, p.lft, p.level, p.total_unit, p.rate,
			p.bill_ref, p.bill_item_id, p.omission_quantity, p.has_omission_build_up_quantity,
			p.addition_quantity, p.has_addition_build_up_quantity, uom.id AS uom_id, uom.symbol AS uom_symbol
			FROM " . SubPackageVariationOrderItemTable::getInstance()->getTableName() . " i
			JOIN " . SubPackageVariationOrderItemTable::getInstance()->getTableName() . " p ON (i.lft BETWEEN p.lft AND p.rgt AND p.deleted_at IS NULL)
			LEFT JOIN " . UnitOfMeasurementTable::getInstance()->getTableName() . " uom ON p.uom_id = uom.id AND uom.deleted_at IS NULL
			WHERE i.id IN (" . implode(',', $itemIds) . ") AND i.deleted_at IS NULL
			AND i.root_id = p.root_id AND i.type <> " . SubPackageVariationOrderItem::TYPE_HEADER . "
			ORDER BY p.priority, p.lft, p.level");

			$stmt->execute();
			$variationOrderItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

			foreach ( $variationOrderItems as $variationOrderItem )
			{
				$voIds[$variationOrderItem['sub_package_variation_order_id']] = $variationOrderItem['sub_package_variation_order_id'];
			}

			// get VO's information
			$stmt = $pdo->prepare("SELECT vo.id, vo.description FROM " . SubPackageVariationOrderTable::getInstance()->getTableName() . " vo
			WHERE vo.id IN (" . implode(',', $voIds) . ") AND vo.sub_package_id = " . $subPackage->id . " AND vo.deleted_at IS NULL
			ORDER BY vo.priority");

			$stmt->execute();
			$variationOrders = $stmt->fetchAll(PDO::FETCH_ASSOC);

			foreach ( $variationOrders as $variationOrder )
			{
				$generatedVOHeader = false;

				$stmt = $pdo->prepare("SELECT DISTINCT i.id AS sub_package_variation_order_item_id, ci.current_amount, ci.current_percentage, ci.up_to_date_amount, ci.up_to_date_percentage,
				COALESCE(pci.up_to_date_amount, 0) AS previous_amount, COALESCE(pci.up_to_date_percentage, 0) AS previous_percentage
				FROM " . SubPackageVariationOrderItemTable::getInstance()->getTableName() . " i
				JOIN " . SubPackageVariationOrderClaimTable::getInstance()->getTableName() . " c ON i.sub_package_variation_order_id = c.sub_package_variation_order_id
				LEFT JOIN " . SubPackageVariationOrderClaimItemTable::getInstance()->getTableName() . " ci ON ci.sub_package_variation_order_claim_id = c.id AND ci.sub_package_variation_order_item_id = i.id
				LEFT JOIN " . SubPackageVariationOrderClaimTable::getInstance()->getTableName() . " pc ON pc.sub_package_variation_order_id = c.sub_package_variation_order_id AND pc.revision = c.revision - 1
				LEFT JOIN " . SubPackageVariationOrderClaimItemTable::getInstance()->getTableName() . " pci ON pci.sub_package_variation_order_claim_id = pc.id AND pci.sub_package_variation_order_item_id = i.id
				WHERE i.sub_package_variation_order_id = " . $variationOrder['id'] . " AND c.is_viewing IS TRUE
				AND i.deleted_at IS NULL AND c.deleted_at IS NULL AND ci.deleted_at IS NULL AND pc.deleted_at IS NULL AND pci.deleted_at IS NULL");

				$stmt->execute();
				$claimItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

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

						array_push($data, $voInformation);

						unset( $voInformation );

						$generatedVOHeader = true;
					}

					if ( $variationOrderItem['sub_package_variation_order_id'] != $variationOrder['id'] )
					{
						continue;
					}

					$variationOrderItem['omission_quantity-value'] = $variationOrderItem['omission_quantity'];
					$variationOrderItem['addition_quantity-value'] = $variationOrderItem['addition_quantity'];
					$variationOrderItem['rate-value']              = $variationOrderItem['rate'];
					$variationOrderItem['type']                    = (string) $variationOrderItem['type'];
					$variationOrderItem['uom_id']                  = $variationOrderItem['uom_id'] > 0 ? (string) $variationOrderItem['uom_id'] : '-1';
					$variationOrderItem['uom_symbol']              = $variationOrderItem['uom_id'] > 0 ? $variationOrderItem['uom_symbol'] : '';

					$variationOrderItem['previous_percentage-value']   = number_format(0, 2, '.', '');
					$variationOrderItem['previous_amount-value']       = number_format(0, 2, '.', '');
					$variationOrderItem['current_percentage-value']    = number_format(0, 2, '.', '');
					$variationOrderItem['current_amount-value']        = number_format(0, 2, '.', '');
					$variationOrderItem['up_to_date_percentage-value'] = number_format(0, 2, '.', '');
					$variationOrderItem['up_to_date_amount-value']     = number_format(0, 2, '.', '');

					foreach ( $claimItems as $claimItem )
					{
						if ( $claimItem['sub_package_variation_order_item_id'] == $variationOrderItem['id'] )
						{
							$variationOrderItem['previous_percentage-value']   = $claimItem['previous_percentage'];
							$variationOrderItem['previous_amount-value']       = $claimItem['previous_amount'];
							$variationOrderItem['current_percentage-value']    = $claimItem['current_percentage'];
							$variationOrderItem['current_amount-value']        = $claimItem['current_amount'];
							$variationOrderItem['up_to_date_percentage-value'] = $claimItem['up_to_date_percentage'];
							$variationOrderItem['up_to_date_amount-value']     = $claimItem['up_to_date_amount'];

							unset( $claimItem );
						}
					}

					$variationOrderItem['id'] = "{$variationOrder['id']}-{$variationOrderItem['id']}";

					$data[] = $variationOrderItem;

					unset( $variationOrderItem, $variationOrderItems[$key] );
				}

				unset( $claimItems );
			}

			unset( $variationOrders );
		}

		$defaultLastRow = array(
			'id'                             => Constants::GRID_LAST_ROW,
			'description'                    => '',
			'bill_ref'                       => '',
			'total_unit'                     => '',
			'bill_item_id'                   => - 1,
			'type'                           => (string) VariationOrderItem::TYPE_WORK_ITEM,
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

		array_push($data, $defaultLastRow);

		return $this->renderJson(array(
			'identifier' => 'id',
			'items'      => $data
		));
	}

	public function executeGetPrintingVOItemsWithClaim(sfWebRequest $request)
	{
		$this->forward404Unless(
			$request->isXmlHttpRequest() AND
			$subPackage = Doctrine_Core::getTable('SubPackage')->find($request->getParameter('subPackageId'))
		);

		$pdo     = $subPackage->getTable()->getConnection()->getDbh();
		$data    = array();
		$voIds   = array();
		$itemIds = array();

		// get the claim item(s) that is currently got up to date claim
		$stmt = $pdo->prepare("SELECT DISTINCT vo.id AS sub_package_variation_order_id, i.id AS sub_package_variation_order_item_id, ci.current_amount, ci.current_percentage, ci.up_to_date_amount, ci.up_to_date_percentage,
		COALESCE(pci.up_to_date_amount, 0) AS previous_amount, COALESCE(pci.up_to_date_percentage, 0) AS previous_percentage
		FROM " . SubPackageVariationOrderItemTable::getInstance()->getTableName() . " i
		JOIN " . SubPackageVariationOrderClaimTable::getInstance()->getTableName() . " c ON i.sub_package_variation_order_id = c.sub_package_variation_order_id
		LEFT JOIN " . SubPackageVariationOrderClaimItemTable::getInstance()->getTableName() . " ci ON ci.sub_package_variation_order_claim_id = c.id AND ci.sub_package_variation_order_item_id = i.id
		LEFT JOIN " . SubPackageVariationOrderClaimTable::getInstance()->getTableName() . " pc ON pc.sub_package_variation_order_id = c.sub_package_variation_order_id AND pc.revision = c.revision - 1
		LEFT JOIN " . SubPackageVariationOrderClaimItemTable::getInstance()->getTableName() . " pci ON pci.sub_package_variation_order_claim_id = pc.id AND pci.sub_package_variation_order_item_id = i.id
		JOIN " . SubPackageVariationOrderTable::getInstance()->getTableName() . " vo ON (vo.id = c.sub_package_variation_order_id AND vo.deleted_at IS NULL)
		WHERE vo.sub_package_id = " . $subPackage->id . " AND ci.up_to_date_amount > 0
		AND i.deleted_at IS NULL AND c.deleted_at IS NULL AND ci.deleted_at IS NULL AND pc.deleted_at IS NULL AND pci.deleted_at IS NULL");

		$stmt->execute();
		$claimItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

		// assign item id(s) and claim id(s) into array in order to correctly query based on sub_package_variation_order_id
		// for the query below
		foreach ( $claimItems as $claimItem )
		{
			$voIds[$claimItem['sub_package_variation_order_id']]     = $claimItem['sub_package_variation_order_id'];
			$itemIds[$claimItem['sub_package_variation_order_id']][] = $claimItem['sub_package_variation_order_item_id'];
		}

		if ( !empty( $voIds ) )
		{
			// get VO's information
			$stmt = $pdo->prepare("SELECT vo.id, vo.description FROM " . SubPackageVariationOrderTable::getInstance()->getTableName() . " vo
			WHERE vo.id IN (" . implode(',', $voIds) . ") AND vo.sub_package_id = " . $subPackage->id . " AND vo.deleted_at IS NULL
			ORDER BY vo.priority");

			$stmt->execute();
			$variationOrders = $stmt->fetchAll(PDO::FETCH_ASSOC);

			// we will start first with variation order so that we can correctly separate item(s) by vos
			foreach ( $variationOrders as $variationOrder )
			{
				if ( !isset( $itemIds[$variationOrder['id']] ) AND count($itemIds[$variationOrder['id']]) == 0 )
				{
					continue;
				}

				$stmt = $pdo->prepare("SELECT DISTINCT p.id, p.sub_package_variation_order_id, p.description, p.type, p.priority, p.lft, p.level, p.total_unit, p.rate,
				p.bill_ref, p.bill_item_id, p.omission_quantity, p.has_omission_build_up_quantity,
				p.addition_quantity, p.has_addition_build_up_quantity, uom.id AS uom_id, uom.symbol AS uom_symbol
				FROM " . SubPackageVariationOrderItemTable::getInstance()->getTableName() . " i
				JOIN " . SubPackageVariationOrderItemTable::getInstance()->getTableName() . " p ON (i.lft BETWEEN p.lft AND p.rgt AND p.deleted_at IS NULL)
				LEFT JOIN " . UnitOfMeasurementTable::getInstance()->getTableName() . " uom ON p.uom_id = uom.id AND uom.deleted_at IS NULL
				WHERE i.id IN (" . implode(',', $itemIds[$variationOrder['id']]) . ") AND i.deleted_at IS NULL
				AND i.root_id = p.root_id AND i.type <> " . SubPackageVariationOrderItem::TYPE_HEADER . "
				ORDER BY p.priority, p.lft, p.level");

				$stmt->execute();
				$variationOrderItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

				// append variation order's table row header if there is item(s) available
				if ( count($variationOrderItems) > 0 )
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

					array_push($data, $voInformation);

					unset( $voInformation );
				}

				foreach ( $variationOrderItems as $key => $variationOrderItem )
				{
					$variationOrderItem['omission_quantity-value'] = $variationOrderItem['omission_quantity'];
					$variationOrderItem['addition_quantity-value'] = $variationOrderItem['addition_quantity'];
					$variationOrderItem['rate-value']              = $variationOrderItem['rate'];
					$variationOrderItem['type']                    = (string) $variationOrderItem['type'];
					$variationOrderItem['uom_id']                  = $variationOrderItem['uom_id'] > 0 ? (string) $variationOrderItem['uom_id'] : '-1';
					$variationOrderItem['uom_symbol']              = $variationOrderItem['uom_id'] > 0 ? $variationOrderItem['uom_symbol'] : '';

					$variationOrderItem['previous_percentage-value']   = number_format(0, 2, '.', '');
					$variationOrderItem['previous_amount-value']       = number_format(0, 2, '.', '');
					$variationOrderItem['current_percentage-value']    = number_format(0, 2, '.', '');
					$variationOrderItem['current_amount-value']        = number_format(0, 2, '.', '');
					$variationOrderItem['up_to_date_percentage-value'] = number_format(0, 2, '.', '');
					$variationOrderItem['up_to_date_amount-value']     = number_format(0, 2, '.', '');

					foreach ( $claimItems as $claimItem )
					{
						if ( $claimItem['sub_package_variation_order_item_id'] == $variationOrderItem['id'] )
						{
							$variationOrderItem['previous_percentage-value']   = $claimItem['previous_percentage'];
							$variationOrderItem['previous_amount-value']       = $claimItem['previous_amount'];
							$variationOrderItem['current_percentage-value']    = $claimItem['current_percentage'];
							$variationOrderItem['current_amount-value']        = $claimItem['current_amount'];
							$variationOrderItem['up_to_date_percentage-value'] = $claimItem['up_to_date_percentage'];
							$variationOrderItem['up_to_date_amount-value']     = $claimItem['up_to_date_amount'];

							unset( $claimItem );
						}
					}

					$variationOrderItem['id'] = "{$variationOrder['id']}-{$variationOrderItem['id']}";

					$data[] = $variationOrderItem;

					unset( $variationOrderItem, $variationOrderItems[$key] );
				}

				unset( $variationOrderItems );
			}

			unset( $variationOrders, $itemIds, $voIds );
		}

		$defaultLastRow = array(
			'id'                             => Constants::GRID_LAST_ROW,
			'description'                    => '',
			'bill_ref'                       => '',
			'total_unit'                     => '',
			'bill_item_id'                   => - 1,
			'type'                           => (string) VariationOrderItem::TYPE_WORK_ITEM,
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

		array_push($data, $defaultLastRow);

		return $this->renderJson(array(
			'identifier' => 'id',
			'items'      => $data,
		));
	}

	public function executeGetPrintingSelectedVOItemsWithBuildUpQty(sfWebRequest $request)
	{
		$this->forward404Unless(
			$request->isXmlHttpRequest() AND
			$request->hasParameter('item_ids') AND
			$subPackage = Doctrine_Core::getTable('SubPackage')->find($request->getParameter('subPackageId'))
		);

		$pdo     = $subPackage->getTable()->getConnection()->getDbh();
		$itemIds = json_decode($request->getParameter('item_ids'), true);
		$data    = array();
		$voIds   = array();

		if ( !empty( $itemIds ) )
		{
			$stmt = $pdo->prepare("SELECT DISTINCT p.id, p.sub_package_variation_order_id, p.description, p.type, p.priority, p.lft, p.level, p.total_unit, p.rate,
			p.bill_ref, p.bill_item_id, p.omission_quantity, p.has_omission_build_up_quantity,
			p.addition_quantity, p.has_addition_build_up_quantity, uom.id AS uom_id, uom.symbol AS uom_symbol
			FROM " . SubPackageVariationOrderItemTable::getInstance()->getTableName() . " i
			JOIN " . SubPackageVariationOrderItemTable::getInstance()->getTableName() . " p ON (i.lft BETWEEN p.lft AND p.rgt AND p.deleted_at IS NULL)
			LEFT JOIN " . UnitOfMeasurementTable::getInstance()->getTableName() . " uom ON p.uom_id = uom.id AND uom.deleted_at IS NULL
			WHERE i.id IN (" . implode(',', $itemIds) . ") AND i.deleted_at IS NULL
			AND i.root_id = p.root_id AND i.type <> " . SubPackageVariationOrderItem::TYPE_HEADER . "
			ORDER BY p.priority, p.lft, p.level");

			$stmt->execute();
			$variationOrderItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

			foreach ( $variationOrderItems as $variationOrderItem )
			{
				$voIds[$variationOrderItem['sub_package_variation_order_id']] = $variationOrderItem['sub_package_variation_order_id'];
			}

			// get VO's information
			$stmt = $pdo->prepare("SELECT vo.id, vo.description FROM " . SubPackageVariationOrderTable::getInstance()->getTableName() . " vo
			WHERE vo.id IN (" . implode(',', $voIds) . ") AND vo.sub_package_id = " . $subPackage->id . " AND vo.deleted_at IS NULL
			ORDER BY vo.priority");

			$stmt->execute(array());
			$variationOrders = $stmt->fetchAll(PDO::FETCH_ASSOC);

			foreach ( $variationOrders as $variationOrder )
			{
				$generatedVOHeader = false;

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
						);

						array_push($data, $voInformation);

						unset( $voInformation );

						$generatedVOHeader = true;
					}

					if ( $variationOrderItem['sub_package_variation_order_id'] != $variationOrder['id'] )
					{
						continue;
					}

					$variationOrderItem['omission_quantity-value'] = $variationOrderItem['omission_quantity'];
					$variationOrderItem['addition_quantity-value'] = $variationOrderItem['addition_quantity'];
					$variationOrderItem['rate-value']              = $variationOrderItem['rate'];
					$variationOrderItem['type']                    = (string) $variationOrderItem['type'];
					$variationOrderItem['uom_id']                  = $variationOrderItem['uom_id'] > 0 ? (string) $variationOrderItem['uom_id'] : '-1';
					$variationOrderItem['uom_symbol']              = $variationOrderItem['uom_id'] > 0 ? $variationOrderItem['uom_symbol'] : '';
					$variationOrderItem['id']                      = "{$variationOrder['id']}-{$variationOrderItem['id']}";

					$data[] = $variationOrderItem;

					unset( $variationOrderItem, $variationOrderItems[$key] );
				}
			}

			unset( $variationOrders );
		}

		$defaultLastRow = array(
			'id'                             => Constants::GRID_LAST_ROW,
			'description'                    => '',
			'bill_ref'                       => '',
			'total_unit'                     => '',
			'bill_item_id'                   => - 1,
			'type'                           => (string) VariationOrderItem::TYPE_WORK_ITEM,
			'uom_id'                         => '-1',
			'uom_symbol'                     => '',
			'updated_at'                     => '-',
			'level'                          => 0,
			'rate-value'                     => 0,
			'omission_quantity-value'        => 0,
			'has_omission_build_up_quantity' => false,
			'addition_quantity-value'        => 0,
			'has_addition_build_up_quantity' => false,
		);

		array_push($data, $defaultLastRow);

		return $this->renderJson(array(
			'identifier' => 'id',
			'items'      => $data
		));
	}

	// ===============================================================================================================
	// Print Report
	// ===============================================================================================================
	public function executePrintSelectedVO(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless(
			$subPackage = Doctrine_Core::getTable('SubPackage')->find($request->getParameter('subPackageId')) AND
			$project = Doctrine_Core::getTable('ProjectStructure')->find($subPackage->project_structure_id)
		);

		session_write_close();

		$voIds             = ( $request->hasParameter('selectedRows') ) ? json_decode($request->getParameter('selectedRows'), true) : array();
		$printingPageTitle = $request->getParameter('printingPageTitle');
		$descriptionFormat = $request->getParameter('descriptionFormat');
		$priceFormat       = $request->getParameter('priceFormat');
		$printNoCents      = ( $request->getParameter('printNoCents') == 't' ) ? true : false;

		$reportPrintGenerator = new sfBuildspacePostContractSubPackageVOSummaryReportGenerator($project, $subPackage, $voIds, $printingPageTitle, $descriptionFormat);
		$pages                = $reportPrintGenerator->generatePages();
		$maxRows              = $reportPrintGenerator->getMaxRows();
		$currency             = $reportPrintGenerator->getCurrency();
		$withoutPrice         = false;

		$params = array(
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
			'orientation'    => $reportPrintGenerator->getOrientation()
		);

		$stylesheet = $this->getBQStyling();

		$pdfGen = new WkHtmlToPdf($params);

		$totalPage = count($pages) - 1;

		$pageCount = 1;

		$generateSignaturePage = false;

		$voFooterSettings = Doctrine_Core::getTable('VoFooterDefaultSetting')->find(1);

		if ( $pages instanceof SplFixedArray )
		{
			foreach ( $pages as $page )
			{
				if ( empty( $page ) )
				{
					continue;
				}

				$printGrandTotal = ( $pageCount == $pages->count() - 1 ) ? true : false;
				$isLastPage      = ( $pageCount == $pages->count() - 1 ) ? true : false;

				if ( $isLastPage )
				{
					if ( ( $maxRows + 2 ) - count($page) > 15 )
					{
						$isLastPage = true;
						$maxRows    = ( $maxRows + 2 ) - 18;
					}
					else
					{
						$isLastPage            = false;
						$generateSignaturePage = true;
					}
				}

				$layout = $this->getPartial('printReport/pageLayout', array(
					'stylesheet'    => $stylesheet,
					'layoutStyling' => $reportPrintGenerator->getLayoutStyling()
				));

				$billItemsLayoutParams = array(
					'itemPage'                   => $page,
					'maxRows'                    => $maxRows + 2,
					'currency'                   => $currency,
					'headerDescription'          => '',
					'voTotals'                   => $reportPrintGenerator->voTotals,
					'pageCount'                  => $pageCount,
					'totalPage'                  => $totalPage,
					'printGrandTotal'            => $printGrandTotal,
					'workdoneOnly'               => false,
					'reportTitle'                => $printingPageTitle,
					'topLeftRow1'                => $project->title,
					'topLeftRow2'                => '',
					'isLastPage'                 => $isLastPage,
					'left_text'                  => $voFooterSettings->left_text,
					'right_text'                 => $voFooterSettings->right_text,
					'botLeftRow1'                => $reportPrintGenerator->getBottomLeftFirstRowHeader(),
					'botLeftRow2'                => $reportPrintGenerator->getBottomLeftSecondRowHeader(),
					'descHeader'                 => $reportPrintGenerator->getTableHeaderDescriptionPrefix(),
					'unitHeader'                 => $reportPrintGenerator->getTableHeaderUnitPrefix(),
					'rateHeader'                 => $reportPrintGenerator->getTableHeaderRatePrefix(),
					'qtyHeader'                  => $reportPrintGenerator->getTableHeaderQtyPrefix(),
					'amtHeader'                  => $reportPrintGenerator->getTableHeaderAmtPrefix(),
					'toCollection'               => $reportPrintGenerator->getToCollectionPrefix(),
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

				$layout .= $this->getPartial('printReport/voSummaryReport', $billItemsLayoutParams);

				unset( $page );

				$pdfGen->addPage($layout);

				$pageCount ++;
			}

			if ( $generateSignaturePage )
			{
				$layout = $this->getPartial('printReport/pageLayout', array(
					'stylesheet'    => $stylesheet,
					'layoutStyling' => $reportPrintGenerator->getLayoutStyling()
				));

				$billItemsLayoutParams = array(
					'itemPage'          => array(),
					'maxRows'           => ( $maxRows + 2 ) - 15,
					'headerDescription' => '',
					'pageCount'         => $pageCount,
					'totalPage'         => $totalPage,
					'printGrandTotal'   => false,
					'workdoneOnly'      => false,
					'reportTitle'       => $printingPageTitle,
					'topLeftRow1'       => $project->title,
					'topLeftRow2'       => '',
					'isLastPage'        => true,
					'left_text'         => $voFooterSettings->left_text,
					'right_text'        => $voFooterSettings->right_text,
					'descHeader'        => $reportPrintGenerator->getTableHeaderDescriptionPrefix(),
					'unitHeader'        => $reportPrintGenerator->getTableHeaderUnitPrefix(),
					'rateHeader'        => $reportPrintGenerator->getTableHeaderRatePrefix(),
					'qtyHeader'         => $reportPrintGenerator->getTableHeaderQtyPrefix(),
					'amtHeader'         => $reportPrintGenerator->getTableHeaderAmtPrefix(),
					'indentItem'        => $reportPrintGenerator->getIndentItem()
				);

				$layout .= $this->getPartial('printReport/voSummaryBlankPage', $billItemsLayoutParams);

				$pdfGen->addPage($layout);
			}
		}

		return $pdfGen->send();
	}

	public function executePrintVOWithClaims(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless(
			$subPackage = Doctrine_Core::getTable('SubPackage')->find($request->getParameter('subPackageId')) AND
			$project = Doctrine_Core::getTable('ProjectStructure')->find($subPackage->project_structure_id)
		);

		session_write_close();

		$printingPageTitle = $request->getParameter('printingPageTitle');
		$descriptionFormat = $request->getParameter('descriptionFormat');
		$priceFormat       = $request->getParameter('priceFormat');
		$printNoCents      = ( $request->getParameter('printNoCents') == 't' ) ? true : false;

		$reportPrintGenerator = new sfBuildspaceSubPackageVOWithClaimsReportGenerator($project, $subPackage, $printingPageTitle, $descriptionFormat);
		$pages                = $reportPrintGenerator->generatePages();
		$maxRows              = $reportPrintGenerator->getMaxRows();
		$currency             = $reportPrintGenerator->getCurrency();
		$withoutPrice         = false;

		$params = array(
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
			'orientation'    => $reportPrintGenerator->getOrientation()
		);

		$stylesheet            = $this->getBQStyling();
		$pdfGen                = new WkHtmlToPdf($params);
		$totalPage             = count($pages) - 1;
		$pageCount             = 1;
		$generateSignaturePage = false;
		$voFooterSettings      = Doctrine_Core::getTable('VoFooterDefaultSetting')->find(1);

		if ( $pages instanceof SplFixedArray )
		{
			foreach ( $pages as $page )
			{
				if ( empty( $page ) )
				{
					continue;
				}

				$printGrandTotal = ( $pageCount == $pages->count() - 1 ) ? true : false;
				$isLastPage      = ( $pageCount == $pages->count() - 1 ) ? true : false;

				if ( $isLastPage )
				{
					if ( ( $maxRows + 2 ) - count($page) > 15 )
					{
						$isLastPage = true;
						$maxRows    = ( $maxRows + 2 ) - 18;
					}
					else
					{
						$isLastPage            = false;
						$generateSignaturePage = true;
					}
				}

				$layout = $this->getPartial('printReport/pageLayout', array(
					'stylesheet'    => $stylesheet,
					'layoutStyling' => $reportPrintGenerator->getLayoutStyling()
				));

				$billItemsLayoutParams = array(
					'itemPage'                   => $page,
					'maxRows'                    => $maxRows + 2,
					'currency'                   => $currency,
					'headerDescription'          => '',
					'pageCount'                  => $pageCount,
					'totalPage'                  => $totalPage,
					'printGrandTotal'            => $printGrandTotal,
					'workdoneOnly'               => false,
					'reportTitle'                => $printingPageTitle,
					'topLeftRow1'                => $project->title,
					'topLeftRow2'                => '',
					'voTotals'                   => $reportPrintGenerator->voTotals,
					'isLastPage'                 => $isLastPage,
					'left_text'                  => $voFooterSettings->left_text,
					'right_text'                 => $voFooterSettings->right_text,
					'botLeftRow1'                => $reportPrintGenerator->getBottomLeftFirstRowHeader(),
					'botLeftRow2'                => $reportPrintGenerator->getBottomLeftSecondRowHeader(),
					'descHeader'                 => $reportPrintGenerator->getTableHeaderDescriptionPrefix(),
					'unitHeader'                 => $reportPrintGenerator->getTableHeaderUnitPrefix(),
					'rateHeader'                 => $reportPrintGenerator->getTableHeaderRatePrefix(),
					'qtyHeader'                  => $reportPrintGenerator->getTableHeaderQtyPrefix(),
					'amtHeader'                  => $reportPrintGenerator->getTableHeaderAmtPrefix(),
					'toCollection'               => $reportPrintGenerator->getToCollectionPrefix(),
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

				$layout .= $this->getPartial('printReport/voSummaryReportWithClaims', $billItemsLayoutParams);

				unset( $page );

				$pdfGen->addPage($layout);

				$pageCount ++;
			}

			if ( $generateSignaturePage )
			{
				$layout = $this->getPartial('printReport/pageLayout', array(
					'stylesheet'    => $stylesheet,
					'layoutStyling' => $reportPrintGenerator->getLayoutStyling()
				));

				$billItemsLayoutParams = array(
					'itemPage'          => array(),
					'maxRows'           => ( $maxRows + 2 ) - 15,
					'headerDescription' => '',
					'pageCount'         => $pageCount,
					'totalPage'         => $totalPage,
					'printGrandTotal'   => false,
					'workdoneOnly'      => false,
					'reportTitle'       => $printingPageTitle,
					'topLeftRow1'       => $project->title,
					'topLeftRow2'       => '',
					'isLastPage'        => true,
					'left_text'         => $voFooterSettings->left_text,
					'right_text'        => $voFooterSettings->right_text,
					'descHeader'        => $reportPrintGenerator->getTableHeaderDescriptionPrefix(),
					'unitHeader'        => $reportPrintGenerator->getTableHeaderUnitPrefix(),
					'rateHeader'        => $reportPrintGenerator->getTableHeaderRatePrefix(),
					'qtyHeader'         => $reportPrintGenerator->getTableHeaderQtyPrefix(),
					'amtHeader'         => $reportPrintGenerator->getTableHeaderAmtPrefix(),
					'indentItem'        => $reportPrintGenerator->getIndentItem()
				);

				$layout .= $this->getPartial('printReport/voSummaryReportWithClaimsBlankPage', $billItemsLayoutParams);

				$pdfGen->addPage($layout);
			}
		}

		return $pdfGen->send();
	}

	public function executePrintSelectedVOItemsDialog(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless(
			$subPackage = Doctrine_Core::getTable('SubPackage')->find($request->getParameter('subPackageId')) AND
			$project = Doctrine_Core::getTable('ProjectStructure')->find($subPackage->project_structure_id)
		);

		session_write_close();

		$printingPageTitle = $request->getParameter('printingPageTitle');
		$descriptionFormat = $request->getParameter('descriptionFormat');
		$itemIds           = json_decode($request->getParameter('selectedRows'), true);
		$priceFormat       = $request->getParameter('priceFormat');
		$printNoCents      = ( $request->getParameter('printNoCents') == 't' ) ? true : false;

		$reportPrintGenerator = new sfBuildspaceSubPackageVOItemsReportGenerator($project, $subPackage, $itemIds, $printingPageTitle, $descriptionFormat);
		$pages                = $reportPrintGenerator->generatePages();
		$maxRows              = $reportPrintGenerator->getMaxRows();
		$currency             = $reportPrintGenerator->getCurrency();
		$withoutPrice         = false;

		$params = array(
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
			'orientation'    => $reportPrintGenerator->getOrientation()
		);

		$stylesheet            = $this->getBQStyling();
		$pdfGen                = new WkHtmlToPdf($params);
		$pageCount             = 1;
		$groupCount            = 1;
		$generateSignaturePage = false;

		$voFooterSettings = Doctrine_Core::getTable('VoFooterDefaultSetting')->find(1);
		$variationTotals  = $reportPrintGenerator->variationTotals;
		$totalPage        = count($pages) - 1;

		foreach ( $pages as $key => $page )
		{
			$lastGroup = ( $groupCount == count($pages) ) ? true : false;

			for ( $i = 1; $i <= $page['item_pages']->count(); $i ++ )
			{
				if ( $page['item_pages'] instanceof SplFixedArray and $page['item_pages']->offsetExists($i) )
				{
					$printGrandTotal = ( ( $i + 1 ) == $page['item_pages']->count() ) ? true : false;
					$isLastPage      = ( ( $i + 1 ) == $page['item_pages']->count() && $lastGroup ) ? true : false;

					if ( $isLastPage )
					{
						if ( ( $maxRows + 2 ) - count($page) > 15 )
						{
							$isLastPage = true;
							$maxRows    = ( $maxRows + 2 ) - 18;
						}
						else
						{
							$isLastPage            = false;
							$generateSignaturePage = true;
						}
					}

					$layout = $this->getPartial('printReport/pageLayout', array(
						'stylesheet'    => $stylesheet,
						'layoutStyling' => $reportPrintGenerator->getLayoutStyling()
					));

					$billItemsLayoutParams = array(
						'itemPage'                   => $page['item_pages']->offsetGet($i),
						'maxRows'                    => $maxRows + 2,
						'currency'                   => $currency,
						'headerDescription'          => '',
						'pageCount'                  => $pageCount,
						'totalPage'                  => $reportPrintGenerator->totalPage,
						'printGrandTotal'            => $printGrandTotal,
						'reportTitle'                => $printingPageTitle,
						'topLeftRow1'                => $project->title,
						'topLeftRow2'                => '',
						'variationTotal'             => ( $printGrandTotal && array_key_exists($key, $variationTotals) ) ? $variationTotals[$key] : 0,
						'isLastPage'                 => $isLastPage,
						'left_text'                  => $voFooterSettings->left_text,
						'right_text'                 => $voFooterSettings->right_text,
						'botLeftRow1'                => $reportPrintGenerator->getBottomLeftFirstRowHeader(),
						'botLeftRow2'                => $reportPrintGenerator->getBottomLeftSecondRowHeader(),
						'descHeader'                 => $reportPrintGenerator->getTableHeaderDescriptionPrefix(),
						'unitHeader'                 => $reportPrintGenerator->getTableHeaderUnitPrefix(),
						'rateHeader'                 => $reportPrintGenerator->getTableHeaderRatePrefix(),
						'qtyHeader'                  => $reportPrintGenerator->getTableHeaderQtyPrefix(),
						'amtHeader'                  => $reportPrintGenerator->getTableHeaderAmtPrefix(),
						'toCollection'               => $reportPrintGenerator->getToCollectionPrefix(),
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

					$layout .= $this->getPartial('printReport/voItemReport', $billItemsLayoutParams);

					$page['item_pages']->offsetUnset($i);

					$pdfGen->addPage($layout);

					$pageCount ++;
				}
			}

			$groupCount ++;
		}

		if ( $generateSignaturePage )
		{
			$layout = $this->getPartial('printReport/pageLayout', array(
				'stylesheet'    => $stylesheet,
				'layoutStyling' => $reportPrintGenerator->getLayoutStyling()
			));

			$billItemsLayoutParams = array(
				'itemPage'          => array(),
				'maxRows'           => ( $maxRows + 2 ) - 15,
				'headerDescription' => '',
				'pageCount'         => $pageCount,
				'totalPage'         => $totalPage,
				'printGrandTotal'   => false,
				'workdoneOnly'      => false,
				'reportTitle'       => $printingPageTitle,
				'topLeftRow1'       => $project->title,
				'topLeftRow2'       => '',
				'isLastPage'        => true,
				'left_text'         => $voFooterSettings->left_text,
				'right_text'        => $voFooterSettings->right_text,
				'descHeader'        => $reportPrintGenerator->getTableHeaderDescriptionPrefix(),
				'unitHeader'        => $reportPrintGenerator->getTableHeaderUnitPrefix(),
				'rateHeader'        => $reportPrintGenerator->getTableHeaderRatePrefix(),
				'qtyHeader'         => $reportPrintGenerator->getTableHeaderQtyPrefix(),
				'amtHeader'         => $reportPrintGenerator->getTableHeaderAmtPrefix(),
				'indentItem'        => $reportPrintGenerator->getIndentItem()
			);

			$layout .= $this->getPartial('printReport/voItemReportBlankPage', $billItemsLayoutParams);

			$pdfGen->addPage($layout);
		}

		return $pdfGen->send();
	}

	public function executePrintVOItemsWithClaims(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless(
			$subPackage = Doctrine_Core::getTable('SubPackage')->find($request->getParameter('subPackageId')) AND
			$project = Doctrine_Core::getTable('ProjectStructure')->find($subPackage->project_structure_id)
		);

		session_write_close();

		$printingPageTitle = $request->getParameter('printingPageTitle');
		$descriptionFormat = $request->getParameter('descriptionFormat');
		$priceFormat       = $request->getParameter('priceFormat');
		$printNoCents      = ( $request->getParameter('printNoCents') == 't' ) ? true : false;

		$reportPrintGenerator = new sfBuildspaceSubPackageVOItemsWithClaimReportGenerator($project, $subPackage, $printingPageTitle, $descriptionFormat);
		$pages                = $reportPrintGenerator->generatePages();
		$maxRows              = $reportPrintGenerator->getMaxRows();
		$currency             = $reportPrintGenerator->getCurrency();
		$withoutPrice         = false;

		$params = array(
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
			'orientation'    => $reportPrintGenerator->getOrientation()
		);

		$stylesheet            = $this->getBQStyling();
		$pdfGen                = new WkHtmlToPdf($params);
		$pageCount             = 1;
		$groupCount            = 1;
		$generateSignaturePage = false;

		$voFooterSettings = Doctrine_Core::getTable('VoFooterDefaultSetting')->find(1);
		$variationTotals  = $reportPrintGenerator->variationTotals;
		$totalPage        = count($pages) - 1;

		foreach ( $pages as $key => $page )
		{
			$lastGroup = ( $groupCount == count($pages) ) ? true : false;

			for ( $i = 1; $i <= $page['item_pages']->count(); $i ++ )
			{
				if ( $page['item_pages'] instanceof SplFixedArray and $page['item_pages']->offsetExists($i) )
				{
					$printGrandTotal = ( ( $i + 1 ) == $page['item_pages']->count() ) ? true : false;
					$isLastPage      = ( ( $i + 1 ) == $page['item_pages']->count() && $lastGroup ) ? true : false;

					if ( $isLastPage )
					{
						if ( ( $maxRows + 2 ) - count($page) > 15 )
						{
							$isLastPage = true;
							$maxRows    = ( $maxRows + 2 ) - 18;
						}
						else
						{
							$isLastPage            = false;
							$generateSignaturePage = true;
						}
					}

					$layout = $this->getPartial('printReport/pageLayout', array(
							'stylesheet'    => $stylesheet,
							'layoutStyling' => $reportPrintGenerator->getLayoutStyling()
						)
					);

					$billItemsLayoutParams = array(
						'itemPage'                   => $page['item_pages']->offsetGet($i),
						'maxRows'                    => $maxRows + 2,
						'currency'                   => $currency,
						'headerDescription'          => '',
						'pageCount'                  => $pageCount,
						'totalPage'                  => $reportPrintGenerator->totalPage,
						'printGrandTotal'            => $printGrandTotal,
						'reportTitle'                => $printingPageTitle,
						'topLeftRow1'                => $project->title,
						'topLeftRow2'                => '',
						'variationTotal'             => ( $printGrandTotal && array_key_exists($key, $variationTotals) ) ? $variationTotals[$key] : 0,
						'isLastPage'                 => $isLastPage,
						'left_text'                  => $voFooterSettings->left_text,
						'right_text'                 => $voFooterSettings->right_text,
						'botLeftRow1'                => $reportPrintGenerator->getBottomLeftFirstRowHeader(),
						'botLeftRow2'                => $reportPrintGenerator->getBottomLeftSecondRowHeader(),
						'descHeader'                 => $reportPrintGenerator->getTableHeaderDescriptionPrefix(),
						'unitHeader'                 => $reportPrintGenerator->getTableHeaderUnitPrefix(),
						'rateHeader'                 => $reportPrintGenerator->getTableHeaderRatePrefix(),
						'qtyHeader'                  => $reportPrintGenerator->getTableHeaderQtyPrefix(),
						'amtHeader'                  => $reportPrintGenerator->getTableHeaderAmtPrefix(),
						'toCollection'               => $reportPrintGenerator->getToCollectionPrefix(),
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

					$layout .= $this->getPartial('printReport/voItemReportWithClaim', $billItemsLayoutParams);

					$page['item_pages']->offsetUnset($i);

					$pdfGen->addPage($layout);

					$pageCount ++;
				}
			}

			$groupCount ++;
		}

		if ( $generateSignaturePage )
		{
			$layout = $this->getPartial('printReport/pageLayout', array(
				'stylesheet'    => $stylesheet,
				'layoutStyling' => $reportPrintGenerator->getLayoutStyling()
			));

			$billItemsLayoutParams = array(
				'itemPage'          => array(),
				'maxRows'           => ( $maxRows + 2 ) - 15,
				'headerDescription' => '',
				'pageCount'         => $pageCount,
				'totalPage'         => $totalPage,
				'printGrandTotal'   => false,
				'workdoneOnly'      => false,
				'reportTitle'       => $printingPageTitle,
				'topLeftRow1'       => $project->title,
				'topLeftRow2'       => '',
				'isLastPage'        => true,
				'left_text'         => $voFooterSettings->left_text,
				'right_text'        => $voFooterSettings->right_text,
				'descHeader'        => $reportPrintGenerator->getTableHeaderDescriptionPrefix(),
				'unitHeader'        => $reportPrintGenerator->getTableHeaderUnitPrefix(),
				'rateHeader'        => $reportPrintGenerator->getTableHeaderRatePrefix(),
				'qtyHeader'         => $reportPrintGenerator->getTableHeaderQtyPrefix(),
				'amtHeader'         => $reportPrintGenerator->getTableHeaderAmtPrefix(),
				'indentItem'        => $reportPrintGenerator->getIndentItem()
			);

			$layout .= $this->getPartial('printReport/voItemReportBlankPage', $billItemsLayoutParams);

			$pdfGen->addPage($layout);
		}

		return $pdfGen->send();
	}

	public function executePrintSelectedVOItemsWithBuildUpQty(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless(
			$subPackage = Doctrine_Core::getTable('SubPackage')->find($request->getParameter('subPackageId')) AND
			$project = Doctrine_Core::getTable('ProjectStructure')->find($subPackage->project_structure_id)
		);

		session_write_close();

		$itemIds    = json_decode($request->getParameter('selectedRows'), true);
		$stylesheet = $this->getBQStyling();
		$data       = array();

		$printingPageTitle = $request->getParameter('printingPageTitle');
		$descriptionFormat = $request->getParameter('descriptionFormat');
		$priceFormat       = $request->getParameter('priceFormat');
		$printNoCents      = ( $request->getParameter('printNoCents') == 't' ) ? true : false;
		$withoutPrice      = false;
		$printedPages      = false;

		if ( !empty( $itemIds ) )
		{
			list(
				$data, $variationOrders,
				$buildUpItemsSummaries, $unitsDimensions, $buildUpItemsWithType
				) = SubPackageVariationOrderItemTable::getVOItemsStructure($subPackage, $itemIds);
		}

		if ( empty( $data ) )
		{
			$this->message     = 'ERROR';
			$this->explanation = 'Nothing can be printed because there are no item(s) detected.';

			$this->setTemplate('nothingToPrint');

			return sfView::ERROR;
		}

		list(
			$soqItemsData, $soqFormulatedColumns, $manualBuildUpQuantityItems, $importedBuildUpQuantityItems
			) = ScheduleOfQuantitySubPackageVOItemXrefTable::getSelectedItemsBuildUpQuantity($project, $data);

		$reportPrintGenerator = new sfBuildSpaceSubPackageVariationOrderBuildUpItemGenerator($project, $subPackage, $descriptionFormat);
		$reportPrintGenerator->setOrientationAndSize('portrait');

		$currency  = $reportPrintGenerator->getCurrency();
		$pageCount = 1;

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

		$variationOrderBuildUpQtyTypes = array(
			SubPackageVariationOrderBuildUpQuantityItem::TYPE_OMISSION_QTY,
			SubPackageVariationOrderBuildUpQuantityItem::TYPE_ADDITIONAL_QTY
		);

		// first level will be looping variation order, then bill associated with it
		foreach ( $variationOrders as $variationOrder )
		{
			$voItems = ( isset( $data[$variationOrder['id']] ) ) ? $data[$variationOrder['id']] : array();

			foreach ( $voItems as $voItem )
			{
				$dimensions = array();

				// for each VO's item, get build up qty's columns definitions
				foreach ( $unitsDimensions as $unitsDimension )
				{
					if ( $voItem['uom_id'] != $unitsDimension['unit_of_measurement_id'] )
					{
						continue;
					}

					$dimensions[] = $unitsDimension['Dimension'];
				}

				// set available dimension
				$reportPrintGenerator->setAvailableTableHeaderDimensions($dimensions);

				$maxRows = $reportPrintGenerator->getMaxRows();

				// get voItem(s) build up if available
				foreach ( $variationOrderBuildUpQtyTypes as $variationOrderBuildUpQtyType )
				{
					// only allow existing type omission or addition to be available to be printed
					if ( $variationOrderBuildUpQtyType == SubPackageVariationOrderBuildUpQuantityItem::TYPE_OMISSION_QTY AND !$voItem['has_omission'] )
					{
						continue;
					}

					if ( $variationOrderBuildUpQtyType == SubPackageVariationOrderBuildUpQuantityItem::TYPE_ADDITIONAL_QTY AND !$voItem['has_addition'] )
					{
						continue;
					}

					$voItemId        = $voItem['id'];
					$columnPageCount = 1;
					$voQtyTypeText   = VariationOrderBuildUpQuantityItemTable::getTypeText($variationOrderBuildUpQtyType);
					$soqBuildUpItems = array();

					$voItemsBuildUpItems = isset( $buildUpItemsWithType[$variationOrderBuildUpQtyType][$voItem['id']] ) ? $buildUpItemsWithType[$variationOrderBuildUpQtyType][$voItem['id']] : array();

					$quantityPerUnit            = $voItem[strtolower($voQtyTypeText) . '_quantity'];
					$buildUpQuantitySummaryInfo = array();

					if ( isset( $soqItemsData[$variationOrderBuildUpQtyType][$voItemId] ) )
					{
						$soqBuildUpItems = $soqItemsData[$variationOrderBuildUpQtyType][$voItemId];

						unset( $soqItemsData[$variationOrderBuildUpQtyType][$voItemId] );
					}

					if ( isset( $buildUpItemsSummaries[$variationOrderBuildUpQtyType][$voItem['id']] ) )
					{
						$buildUpQuantitySummaryInfo = $buildUpItemsSummaries[$variationOrderBuildUpQtyType][$voItem['id']];

						unset( $buildUpItemsSummaries[$variationOrderBuildUpQtyType][$voItem['id']] );
					}

					// don't generate page that has no manual build up and soq build up item(s)
					if ( count($voItemsBuildUpItems) == 0 AND count($soqBuildUpItems) == 0 )
					{
						unset( $voItemsBuildUpItems, $soqBuildUpItems, $buildUpQuantitySummaryInfo );

						continue;
					}

					// will inject to the generator to correctly generate printout
					// need to pass build up qty item(s) into generator to correctly generate the printout page
					$reportPrintGenerator->setBuildUpQuantityItems($voItemsBuildUpItems);

					$reportPrintGenerator->setSOQBuildUpQuantityItems($soqBuildUpItems);

					$reportPrintGenerator->getSOQFormulatedColumn($soqFormulatedColumns);

					$reportPrintGenerator->setManualBuildUpQuantityMeasurements($manualBuildUpQuantityItems);
					$reportPrintGenerator->setImportedBuildUpQuantityMeasurements($importedBuildUpQuantityItems);

					$pages        = $reportPrintGenerator->generatePages();
					$billItemInfo = $reportPrintGenerator->setupBillItemHeader($voItem, $voItem['bill_ref']);

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

						$lastPage     = ( $columnPageCount == $pages->count() - 1 ) ? true : false;
						$printedPages = true;

						$layout = $this->getPartial('printReport/pageLayout', array(
							'stylesheet'    => $stylesheet,
							'layoutStyling' => $reportPrintGenerator->getLayoutStyling()
						));

						$billItemsLayoutParams = array(
							'buildUpQuantitySummary'     => $buildUpQuantitySummaryInfo,
							'lastPage'                   => $lastPage,
							'totalQtyPerColumnSetting'   => $quantityPerUnit,
							'billItemInfos'              => $billItemInfo,
							'billItemUOM'                => $voItem['uom_symbol'],
							'itemPage'                   => $page,
							'maxRows'                    => $maxRows + 2,
							'currency'                   => $currency,
							'pageCount'                  => $pageCount,
							'elementTitle'               => $project->title,
							'printingPageTitle'          => $printingPageTitle,
							'billDescription'            => "{$variationOrder['description']} > {$voQtyTypeText}",
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

						$layout .= $this->getPartial('printReport/buildUpQtyReport', $billItemsLayoutParams);

						$pdfGen->addPage($layout);

						unset( $layout, $page );

						$pageCount ++;
						$columnPageCount ++;
					}

					unset( $pages, $billItemInfo );
				}

				unset( $voItem );
			}

			unset( $variationOrder, $voItems );
		}

		unset( $variationOrders );

		if ( !$printedPages )
		{
			$this->message     = 'ERROR';
			$this->explanation = 'Sorry, there are no page(s) of omission or addition to be printed.';

			$this->setTemplate('nothingToPrint');

			return sfView::ERROR;
		}

		return $pdfGen->send();
	}
	// ===============================================================================================================

}