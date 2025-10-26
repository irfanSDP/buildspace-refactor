<?php

/**
 * variationOrderReporting actions.
 *
 * @package    buildspace
 * @subpackage variationOrderReporting
 * @author     1337 developers
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class variationOrderReportingActions extends BaseActions {

    public function executeGetAffectedItems(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() AND
            $request->hasParameter('vo_ids') AND
            $project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('pid'))
        );

        $data                = array();
        $variationOfOrderIds = json_decode($request->getParameter('vo_ids'), true);
        $pdo                 = $project->getTable()->getConnection()->getDbh();

        if ( count($variationOfOrderIds) > 0 )
        {
            $stmt = $pdo->prepare("SELECT i.id, i.variation_order_id FROM " . VariationOrderItemTable::getInstance()->getTableName() . " i
            WHERE i.variation_order_id IN (" . implode(',', $variationOfOrderIds) . ") AND i.deleted_at IS NULL
            ORDER BY i.priority, i.lft, i.level");
            $stmt->execute();

            $variationOrderItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if ( count($variationOrderItems) > 0 )
            {
                foreach ( $variationOrderItems as $variationOrderItem )
                {
                    $data[$variationOrderItem['variation_order_id']][] = $variationOrderItem['id'];
                }
            }
            else
            {
                // return empty array of variationOfOrderId's information so that the frontend can process it
                foreach ( $variationOfOrderIds as $variationOfOrderId )
                {
                    $data[$variationOfOrderId] = array();
                }
            }
        }

        return $this->renderJson($data);
    }

    public function executeGetAffectedVOS(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() AND
            $request->hasParameter('item_ids') AND
            $project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('pid'))
        );

        $data    = array();
        $itemIds = json_decode($request->getParameter('item_ids'), true);
        $pdo     = $project->getTable()->getConnection()->getDbh();

        if ( !empty( $itemIds ) )
        {
            $stmt = $pdo->prepare("SELECT i.id, i.variation_order_id FROM " . VariationOrderItemTable::getInstance()->getTableName() . " i
            WHERE i.id IN (" . implode(',', $itemIds) . ") AND i.deleted_at IS NULL
            ORDER BY i.priority, i.lft, i.level");
            $stmt->execute();

            $variationOrderItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ( $variationOrderItems as $variationOrderItem )
            {
                $data[$variationOrderItem['variation_order_id']][] = $variationOrderItem['id'];
            }
        }

        return $this->renderJson($data);
    }

    public function executeGetPrintingSelectedVO(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() AND
            $request->hasParameter('vo_ids') AND
            $project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('pid'))
        );

        $voIds   = json_decode($request->getParameter('vo_ids'), true);
        $records = array();

        if ( !empty( $voIds ) )
        {
            $pdo = $project->getTable()->getConnection()->getDbh();

            $records = Doctrine_Query::create()
                ->select('vo.id, vo.description, vo.is_approved, vo.updated_at')
                ->from('VariationOrder vo')
                ->where('vo.project_structure_id = ?', $project->id)
                ->andWhereIn('vo.id', $voIds)
                ->addOrderBy('vo.priority ASC')
                ->fetchArray();

            $stmt = $pdo->prepare("SELECT vo.id, COALESCE(COUNT(c.id), 0)
            FROM " . VariationOrderTable::getInstance()->getTableName() . " vo
            LEFT JOIN " . VariationOrderClaimTable::getInstance()->getTableName() . " c ON c.variation_order_id = vo.id AND c.deleted_at IS NULL
            WHERE vo.project_structure_id = " . $project->id . " AND vo.deleted_at IS NULL
            GROUP BY vo.id ORDER BY vo.priority");

            $stmt->execute();
            $claimCount = $stmt->fetchAll(PDO::FETCH_COLUMN | PDO::FETCH_GROUP);

            $stmt = $pdo->prepare("SELECT i.variation_order_id, ROUND(COALESCE(SUM(i.total_unit * i.omission_quantity * i.rate), 0), 2) AS omission,
            ROUND(COALESCE(SUM(i.total_unit * i.addition_quantity * i.rate), 0), 2) AS addition,
            ROUND(COALESCE(SUM((i.total_unit * i.addition_quantity * i.rate) - (i.total_unit * i.omission_quantity * i.rate))), 2) AS nett_omission_addition
            FROM " . VariationOrderItemTable::getInstance()->getTableName() . " i
            JOIN " . VariationOrderTable::getInstance()->getTableName() . " vo ON i.variation_order_id = vo.id
            WHERE vo.project_structure_id = " . $project->id . " AND i.type =" . VariationOrderItem::TYPE_WORK_ITEM . " AND i.rate <> 0
            AND vo.deleted_at IS NULL AND i.deleted_at IS NULL GROUP BY i.variation_order_id");

            $stmt->execute();
            $quantities = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $stmt = $pdo->prepare("SELECT vo.id AS variation_order_id,
            ROUND(COALESCE(SUM(
                CASE WHEN ((voi.rate * voi.addition_quantity) - (voi.rate * voi.omission_quantity) < 0)
                    THEN -1 * ABS(i.up_to_date_amount)
                    ELSE i.up_to_date_amount
                END
                ), 0), 2) AS amount
            FROM " . VariationOrderTable::getInstance()->getTableName() . " vo
            JOIN " . VariationOrderClaimTable::getInstance()->getTableName() . " c ON c.variation_order_id = vo.id
            JOIN " . VariationOrderClaimItemTable::getInstance()->getTableName() . " i ON i.variation_order_claim_id = c.id
            JOIN " . VariationOrderItemTable::getInstance()->getTableName() . " voi ON i.variation_order_item_id = voi.id
            WHERE vo.project_structure_id = " . $project->id . "
            AND vo.deleted_at IS NULL AND c.deleted_at IS NULL AND i.deleted_at IS NULL AND voi.deleted_at IS NULL
            GROUP BY vo.id");

            $stmt->execute();
            $upToDateClaims = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ( $records as $key => $record )
            {
                $records[$key]['omission'] = 0;
                $records[$key]['addition'] = 0;

                foreach ( $quantities as $quantity )
                {
                    if ( $quantity['variation_order_id'] == $record['id'] )
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
                array_unshift($records, array(
                    'id'          => 'row-header',
                    'description' => 'Variation Order Summary',
                    'type'        => 0,
                    'omission'    => 0,
                    'addition'    => 0,
                ));
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
            $project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('pid'))
        );

        $data = array();

        $records = Doctrine_Query::create()
            ->select('vo.id, vo.description, vo.is_approved, vo.updated_at')
            ->from('VariationOrder vo')
            ->andWhere('vo.project_structure_id = ?', $project->id)
            ->addOrderBy('vo.priority ASC')
            ->fetchArray();

        $pdo = $project->getTable()->getConnection()->getDbh();

        $stmt = $pdo->prepare("SELECT vo.id, COALESCE(COUNT(c.id), 0)
        FROM " . VariationOrderTable::getInstance()->getTableName() . " vo
        LEFT JOIN " . VariationOrderClaimTable::getInstance()->getTableName() . " c ON c.variation_order_id = vo.id AND c.deleted_at IS NULL
        WHERE vo.project_structure_id = " . $project->id . " AND vo.deleted_at IS NULL
        GROUP BY vo.id ORDER BY vo.priority");

        $stmt->execute();
        $claimCount = $stmt->fetchAll(PDO::FETCH_COLUMN | PDO::FETCH_GROUP);

        $stmt = $pdo->prepare("SELECT i.variation_order_id, ROUND(COALESCE(SUM(i.total_unit * i.omission_quantity * i.rate), 0), 2) AS omission,
        ROUND(COALESCE(SUM(i.total_unit * i.addition_quantity * i.rate), 0), 2) AS addition,
        ROUND(COALESCE(SUM((i.total_unit * i.addition_quantity * i.rate) - (i.total_unit * i.omission_quantity * i.rate))), 2) AS nett_omission_addition
        FROM " . VariationOrderItemTable::getInstance()->getTableName() . " i
        JOIN " . VariationOrderTable::getInstance()->getTableName() . " vo ON i.variation_order_id = vo.id
        WHERE vo.project_structure_id = " . $project->id . " AND i.type =" . VariationOrderItem::TYPE_WORK_ITEM . " AND i.rate <> 0
        AND vo.deleted_at IS NULL AND i.deleted_at IS NULL GROUP BY i.variation_order_id");

        $stmt->execute();
        $quantities = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmt = $pdo->prepare("SELECT vo.id AS variation_order_id, COALESCE(SUM(
            CASE WHEN ((voi.rate * voi.addition_quantity) - (voi.rate * voi.omission_quantity) < 0)
                THEN -1 * ABS(i.current_amount)
                ELSE i.current_amount
            END
        ), 0) AS amount,
        ROUND(COALESCE(SUM(i.up_to_date_percentage), 0), 2) AS up_to_date_percentage
        FROM " . VariationOrderTable::getInstance()->getTableName() . " vo
        JOIN (SELECT vo.id AS variation_order_id, MAX(c.revision) AS revision
            FROM " . VariationOrderTable::getInstance()->getTableName() . " vo
            JOIN " . VariationOrderClaimTable::getInstance()->getTableName() . " c ON c.variation_order_id = vo.id
            WHERE vo.project_structure_id = " . $project->id . "
            AND vo.deleted_at IS NULL AND c.deleted_at IS NULL
            GROUP BY vo.id
        ) lc ON lc.variation_order_id = vo.id
        JOIN " . VariationOrderClaimTable::getInstance()->getTableName() . " c ON c.variation_order_id = lc.variation_order_id
        JOIN " . VariationOrderClaimItemTable::getInstance()->getTableName() . " i ON i.variation_order_claim_id = c.id
        JOIN " . VariationOrderItemTable::getInstance()->getTableName() . " voi ON i.variation_order_item_id = voi.id
        WHERE vo.project_structure_id = " . $project->id . "
        AND c.revision <= lc.revision
        AND vo.deleted_at IS NULL AND c.deleted_at IS NULL AND i.deleted_at IS NULL AND voi.deleted_at IS NULL
        GROUP BY vo.id");

        $stmt->execute();
        $upToDateClaims = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ( $records as $key => $record )
        {
            $records[$key]['relation_id']            = $project->id;
            $records[$key]['omission']               = 0;
            $records[$key]['addition']               = 0;
            $records[$key]['total_claim']            = 0;
            $records[$key]['nett_omission_addition'] = 0;
            $records[$key]['updated_at']             = date('d/m/Y H:i', strtotime($record['updated_at']));

            foreach ( $quantities as $quantity )
            {
                if ( $quantity['variation_order_id'] == $record['id'] )
                {
                    $records[$key]['omission']               = $quantity['omission'];
                    $records[$key]['addition']               = $quantity['addition'];
                    $records[$key]['nett_omission_addition'] = $quantity['nett_omission_addition'];

                    unset( $quantity );
                }
            }

            foreach ( $upToDateClaims as $upToDateClaim )
            {
                if ( $upToDateClaim['variation_order_id'] == $record['id'] )
                {
                    $records[$key]['total_claim']           = round($upToDateClaim['amount'], 2, PHP_ROUND_HALF_UP);
                    $records[$key]['up_to_date_percentage'] = ( $records[$key]['total_claim'] != 0 ) ? Utilities::percent($records[$key]['total_claim'], $records[$key]['nett_omission_addition']) : 0;

                    unset( $upToDateClaim );
                }
            }

            if ( abs($records[$key]['total_claim']) > 0 )
            {
                $data[] = $records[$key];
            }

            unset( $record );
        }

        unset( $records );

        if ( !empty( $data ) )
        {
            array_unshift($data, array(
                'id'          => 'row-header',
                'description' => 'Variation Order Summary',
                'type'        => 0,
                'omission'    => 0,
                'addition'    => 0,
            ));
        }

        unset( $claimCount, $quantities, $upToDateClaims );

        //default last row
        array_push($data, array(
            'id'                     => Constants::GRID_LAST_ROW,
            'description'            => '',
            'can_be_edited'          => true,
            'relation_id'            => $project->id,
            'omission'               => 0,
            'addition'               => 0,
            'total_claim'            => 0,
            'nett_omission_addition' => 0,
        ));

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $data
        ));
    }

    public function executeGetPrintingSelectedVOItemsDialog(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() AND
            $request->hasParameter('item_ids') AND
            $project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('pid'))
        );

        $pdo     = $project->getTable()->getConnection()->getDbh();
        $itemIds = json_decode($request->getParameter('item_ids'), true);
        $data    = array();
        $voIds   = array();

        if ( !empty( $itemIds ) )
        {
            $stmt = $pdo->prepare("SELECT DISTINCT p.id, p.variation_order_id, p.description, p.type, p.lft, p.level, p.total_unit, p.rate,
            p.bill_ref, p.bill_item_id, p.omission_quantity, p.has_omission_build_up_quantity,
            p.addition_quantity, p.has_addition_build_up_quantity, uom.id AS uom_id, uom.symbol AS uom_symbol,
            p.priority, p.lft, p.level
            FROM " . VariationOrderItemTable::getInstance()->getTableName() . " i
            JOIN " . VariationOrderItemTable::getInstance()->getTableName() . " p ON (i.lft BETWEEN p.lft AND p.rgt AND p.deleted_at IS NULL)
            JOIN " . VariationOrderTable::getInstance()->getTableName() . " vo ON (p.variation_order_id = vo.id AND vo.deleted_at IS NULL)
            LEFT JOIN " . UnitOfMeasurementTable::getInstance()->getTableName() . " uom ON p.uom_id = uom.id AND uom.deleted_at IS NULL
            WHERE vo.project_structure_id = " . $project->id . " AND i.id IN (" . implode(',', $itemIds) . ")
            AND i.root_id = p.root_id AND i.type <> " . VariationOrderItem::TYPE_HEADER . "
            ORDER BY p.priority, p.lft, p.level");
            $stmt->execute();

            $variationOrderItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ( $variationOrderItems as $variationOrderItem )
            {
                $voIds[$variationOrderItem['variation_order_id']] = $variationOrderItem['variation_order_id'];
            }

            // get VO's information
            $stmt = $pdo->prepare("SELECT vo.id, vo.description FROM " . VariationOrderTable::getInstance()->getTableName() . " vo
            WHERE vo.id IN (" . implode(',', $voIds) . ") AND vo.project_structure_id = " . $project->id . " AND vo.deleted_at IS NULL
            ORDER BY vo.priority");

            $stmt->execute();

            $variationOrders = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ( $variationOrders as $variationOrder )
            {
                $generatedVOHeader = false;

                $stmt = $pdo->prepare("SELECT DISTINCT i.id AS variation_order_item_id,
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

                foreach ( $variationOrderItems as $key => $variationOrderItem )
                {
                    if ( !$generatedVOHeader )
                    {
                        array_push($data, array(
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
                        ));

                        $generatedVOHeader = true;
                    }

                    if ( $variationOrderItem['variation_order_id'] != $variationOrder['id'] )
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
                        if ( $claimItem['variation_order_item_id'] == $variationOrderItem['id'] )
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

        array_push($data, array(
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
        ));

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $data
        ));
    }

    public function executeGetPrintingVOItemsWithClaim(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() AND
            $project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('pid'))
        );

        $pdo     = $project->getTable()->getConnection()->getDbh();
        $data    = array();
        $voIds   = array();
        $itemIds = array();

        // get the claim item(s) that is currently got up to date claim
        $stmt = $pdo->prepare("SELECT DISTINCT i.id AS variation_order_item_id, vo.id as variation_order_id,
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
        CASE WHEN ((pcvoi.rate * pcvoi.addition_quantity) - (pcvoi.rate * pcvoi.omission_quantity) < 0)
            THEN -1 * ABS(pci.up_to_date_amount)
            ELSE pci.up_to_date_amount
        END AS previous_amount,
        CASE WHEN ((pcvoi.rate * pcvoi.addition_quantity) - (pcvoi.rate * pcvoi.omission_quantity) < 0)
            THEN -1 * ABS(pci.up_to_date_percentage)
            ELSE pci.up_to_date_percentage
        END AS previous_percentage
        FROM " . VariationOrderItemTable::getInstance()->getTableName() . " i
        JOIN " . VariationOrderClaimTable::getInstance()->getTableName() . " c ON i.variation_order_id = c.variation_order_id
        JOIN " . VariationOrderTable::getInstance()->getTableName() . " vo ON (vo.id = c.variation_order_id AND vo.deleted_at IS NULL)
        JOIN " . VariationOrderClaimItemTable::getInstance()->getTableName() . " ci ON ci.variation_order_claim_id = c.id AND ci.variation_order_item_id = i.id
        LEFT JOIN " . VariationOrderClaimTable::getInstance()->getTableName() . " pc ON pc.variation_order_id = c.variation_order_id AND pc.revision = c.revision - 1
        LEFT JOIN " . VariationOrderClaimItemTable::getInstance()->getTableName() . " pci ON pci.variation_order_claim_id = pc.id AND pci.variation_order_item_id = i.id
        LEFT JOIN " . VariationOrderItemTable::getInstance()->getTableName() . " pcvoi ON pci.variation_order_item_id = pcvoi.id
        WHERE vo.project_structure_id = " . $project->id . " AND ci.up_to_date_amount <> 0
        AND i.deleted_at IS NULL AND vo.deleted_at IS NULL AND c.deleted_at IS NULL AND ci.deleted_at IS NULL
        AND pc.deleted_at IS NULL AND pci.deleted_at IS NULL");

        $stmt->execute();
        $claimItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // assign item id(s) and claim id(s) into array in order to correctly query based on variation_order_id
        // for the query below
        foreach ( $claimItems as $claimItem )
        {
            $itemIds[$claimItem['variation_order_id']][] = $claimItem['variation_order_item_id'];
            $voIds[$claimItem['variation_order_id']]     = $claimItem['variation_order_id'];
        }

        if ( !empty( $voIds ) )
        {
            // get VO's information
            $stmt = $pdo->prepare("SELECT vo.id, vo.description FROM " . VariationOrderTable::getInstance()->getTableName() . " vo
            WHERE vo.id IN (" . implode(',', $voIds) . ") AND vo.project_structure_id = " . $project->id . " AND vo.deleted_at IS NULL
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

                $stmt = $pdo->prepare("SELECT DISTINCT p.id, p.variation_order_id, p.description, p.type, p.lft, p.level, p.total_unit, p.rate,
                p.bill_ref, p.bill_item_id, p.omission_quantity, p.has_omission_build_up_quantity,
                p.addition_quantity, p.has_addition_build_up_quantity, uom.id AS uom_id, uom.symbol AS uom_symbol,
                p.priority, p.lft, p.level
                FROM " . VariationOrderItemTable::getInstance()->getTableName() . " i
                JOIN " . VariationOrderItemTable::getInstance()->getTableName() . " p ON (i.lft BETWEEN p.lft AND p.rgt AND p.deleted_at IS NULL)
                JOIN " . VariationOrderTable::getInstance()->getTableName() . " vo ON (p.variation_order_id = vo.id AND vo.deleted_at IS NULL)
                LEFT JOIN " . UnitOfMeasurementTable::getInstance()->getTableName() . " uom ON p.uom_id = uom.id AND uom.deleted_at IS NULL
                WHERE vo.project_structure_id = " . $project->id . " AND i.id IN (" . implode(',', $itemIds[$variationOrder['id']]) . ")
                AND i.root_id = p.root_id AND i.type <> " . VariationOrderItem::TYPE_HEADER . "
                ORDER BY p.priority, p.lft, p.level");

                $stmt->execute();
                $variationOrderItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

                // append variation order's table row header if there is item(s) available
                if ( !empty( $variationOrderItems ) )
                {
                    array_push($data, array(
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
                    ));
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
                        if ( $claimItem['variation_order_item_id'] == $variationOrderItem['id'] )
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

        array_push($data, array(
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
        ));

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
            $project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('pid'))
        );

        $pdo     = $project->getTable()->getConnection()->getDbh();
        $itemIds = json_decode($request->getParameter('item_ids'), true);
        $data    = array();
        $voIds   = array();

        if ( !empty( $itemIds ) )
        {
            $stmt = $pdo->prepare("SELECT DISTINCT p.id, p.variation_order_id, p.description, p.type, p.lft, p.level, p.total_unit, p.rate,
            p.bill_ref, p.bill_item_id, p.omission_quantity, p.has_omission_build_up_quantity,
            p.addition_quantity, p.has_addition_build_up_quantity, uom.id AS uom_id, uom.symbol AS uom_symbol,
            p.priority, p.lft, p.level
            FROM " . VariationOrderItemTable::getInstance()->getTableName() . " i
            JOIN " . VariationOrderItemTable::getInstance()->getTableName() . " p ON (i.lft BETWEEN p.lft AND p.rgt AND p.deleted_at IS NULL)
            JOIN " . VariationOrderTable::getInstance()->getTableName() . " vo ON (p.variation_order_id = vo.id AND vo.deleted_at IS NULL)
            LEFT JOIN " . UnitOfMeasurementTable::getInstance()->getTableName() . " uom ON p.uom_id = uom.id AND uom.deleted_at IS NULL
            WHERE vo.project_structure_id = " . $project->id . " AND i.id IN (" . implode(',', $itemIds) . ")
            AND i.root_id = p.root_id AND i.type <> " . VariationOrderItem::TYPE_HEADER . "
            ORDER BY p.priority, p.lft, p.level");

            $stmt->execute();
            $variationOrderItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ( $variationOrderItems as $variationOrderItem )
            {
                $voIds[$variationOrderItem['variation_order_id']] = $variationOrderItem['variation_order_id'];
            }

            // get VO's information
            $stmt = $pdo->prepare("SELECT vo.id, vo.description FROM " . VariationOrderTable::getInstance()->getTableName() . " vo
            WHERE vo.id IN (" . implode(',', $voIds) . ") AND vo.project_structure_id = " . $project->id . " AND vo.deleted_at IS NULL
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
                        array_push($data, array(
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
                        ));

                        $generatedVOHeader = true;
                    }

                    if ( $variationOrderItem['variation_order_id'] != $variationOrder['id'] )
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

        array_push($data, array(
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
        ));

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $data
        ));
    }

}
