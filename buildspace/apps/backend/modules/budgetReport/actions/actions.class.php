<?php

/**
 * budgetReport actions.
 *
 * @package    buildspace
 * @subpackage budgetReport
 * @author     1337 developers
 * @version    SVN: $Id$
 */
class budgetReportActions extends sfActions
{
    public function executeGetElementBudgetReport(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('pid')) and
            $bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('bill_id')) and
            $element = Doctrine_Core::getTable('BillElement')->find($request->getParameter('element_id'))
        );

        $pdo = ProjectStructureTable::getInstance()->getConnection()->getDbh();

        $billItemRates = BudgetReport::getProjectItemRates($project);

        $subConBillItemRates = BudgetReport::getSubProjectItemRates($project);

        $stmt = $pdo->prepare("SELECT i.id, i.description, uom.id AS uom_id, uom.symbol AS uom_symbol, i.type, i.lft, i.level
            FROM " . BillItemTable::getInstance()->getTableName() . " i
            JOIN " . BillElementTable::getInstance()->getTableName() . " e on e.id = i.element_id
            LEFT JOIN " . UnitOfMeasurementTable::getInstance()->getTableName() . " uom on uom.id = i.uom_id
            WHERE e.id = {$element->id}
            AND e.deleted_at IS NULL
            AND i.deleted_at IS NULL
            AND i.project_revision_deleted_at IS NULL
            ORDER BY i.priority, i.lft, i.level ASC;");

        $stmt->execute();

        $billItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $subProjects = ProjectStructureTable::getSubProjects($project);

        $data = array();

        foreach($billItems as $key => $billItem)
        {
            $billItem['total_quantity']   = $billItemRates[ $billItem['id'] ]['total_quantity'];
            $billItem['up_to_date_qty']   = $billItemRates[ $billItem['id'] ]['up_to_date_qty'];
            $billItem['rate']             = $billItemRates[ $billItem['id'] ]['rate'];
            $billItem['budget_rate']      = $billItemRates[ $billItem['id'] ]['budget_rate'];
            $billItem['revenue']          = $billItemRates[ $billItem['id'] ]['revenue'];
            $billItem['budget']           = $billItemRates[ $billItem['id'] ]['budget'];
            $billItem['progress_revenue'] = $billItemRates[ $billItem['id'] ]['progress_revenue'];
            $billItem['sub_con_quantity'] = $billItemRates[ $billItem['id'] ]['sub_con_quantity'];
            $billItem['sub_con_rate']     = $billItemRates[ $billItem['id'] ]['sub_con_rate'];
            $billItem['sub_con_budget']   = $billItemRates[ $billItem['id'] ]['sub_con_budget'];
            $billItem['sub_con_cost']     = $billItemRates[ $billItem['id'] ]['sub_con_cost'];
            $billItem['progress_cost']    = $billItemRates[ $billItem['id'] ]['progress_cost'];
            $billItem['is_tagged']        = ( $billItem['type'] == BillItem::TYPE_HEADER || $billItem['type'] == BillItem::TYPE_HEADER_N ) ? true : ( ! empty( $subConBillItemRates[ $billItem['id'] ] ) );

            if( $billItem['type'] == BillItem::TYPE_HEADER || $billItem['type'] == BillItem::TYPE_HEADER_N )
            {
                $data[] = $billItem;
                unset( $billItems[$key] );
                continue;
            }

            $subConDataArray = array();

            foreach($subProjects as $subProject)
            {
                if( ! array_key_exists($billItem['id'], $subConBillItemRates) || ! array_key_exists($subProject->id, $subConBillItemRates[ $billItem['id'] ]) ) continue;

                $subConData = array();

                $subConData['id']          = "{$billItem['id']}-{$subProject->id}";
                $subConData['description'] = $subProject->TenderSetting->AwardedCompany->name;
                $subConData['level']       = $billItem['level'];
                $subConData['uom_id']      = $billItem['uom_id'];
                $subConData['uom_symbol']  = $billItem['uom_symbol'];

                $subConData['total_quantity']   = $subConBillItemRates[ $billItem['id'] ][ $subProject->id ]['total_quantity'];
                $subConData['up_to_date_qty']   = $subConBillItemRates[ $billItem['id'] ][ $subProject->id ]['up_to_date_qty'];
                $subConData['rate']             = $subConBillItemRates[ $billItem['id'] ][ $subProject->id ]['rate'];
                $subConData['budget_rate']      = $subConBillItemRates[ $billItem['id'] ][ $subProject->id ]['budget_rate'];
                $subConData['revenue']          = $subConBillItemRates[ $billItem['id'] ][ $subProject->id ]['revenue'];
                $subConData['budget']           = $subConBillItemRates[ $billItem['id'] ][ $subProject->id ]['budget'];
                $subConData['progress_revenue'] = $subConBillItemRates[ $billItem['id'] ][ $subProject->id ]['progress_revenue'];
                $subConData['sub_con_quantity'] = $subConBillItemRates[ $billItem['id'] ][ $subProject->id ]['sub_con_quantity'];
                $subConData['sub_con_rate']     = $subConBillItemRates[ $billItem['id'] ][ $subProject->id ]['sub_con_rate'];
                $subConData['sub_con_budget']   = $subConBillItemRates[ $billItem['id'] ][ $subProject->id ]['sub_con_budget'];
                $subConData['sub_con_cost']     = $subConBillItemRates[ $billItem['id'] ][ $subProject->id ]['sub_con_cost'];
                $subConData['progress_cost']    = $subConBillItemRates[ $billItem['id'] ][ $subProject->id ]['progress_cost'];
                $subConData['is_sub_project']   = true;

                $billItem['sub_con_budget'] += $subConData['sub_con_budget'];
                $billItem['sub_con_cost'] += $subConData['sub_con_cost'];
                $billItem['progress_cost'] += $subConData['progress_cost'];

                $subConDataArray[] = $subConData;
            }

            $data[] = $billItem;

            foreach($subConDataArray as $subConData)
            {
                $data[] = $subConData;
            }

            unset( $billItems[$key], $subConDataArray );
        }

        $data[] = [
            'id'               => Constants::GRID_LAST_ROW,
            'description'      => null,
            'uom_id'           => '-1',
            'uom_symbol'       => '',
            'total_quantity'   => 0,
            'up_to_date_qty'   => 0,
            'rate'             => 0,
            'budget_rate'      => 0,
            'sub_con_rate'     => 0,
            'sub_con_quantity' => 0,
            'revenue'          => 0,
            'budget'           => 0,
            'sub_con_budget'   => 0,
            'sub_con_cost'     => 0,
            'progress_revenue' => 0,
            'progress_cost'    => 0,
        ];

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $data
        ));
    }

    public function executeGetBillBudgetReport(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('pid')) and
            $bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('bill_id'))
        );

        $pdo = ProjectStructureTable::getInstance()->getConnection()->getDbh();

        $subProjects         = ProjectStructureTable::getSubProjects($project);
        $billItemRates       = BudgetReport::getProjectItemRates($project);
        $subConBillItemRates = BudgetReport::getSubProjectItemRates($project);

        $stmt = $pdo->prepare("SELECT i.id as item_id, e.id as element_id, i.type
            FROM " . BillItemTable::getInstance()->getTableName() . " i
            JOIN " . BillElementTable::getInstance()->getTableName() . " e on e.id = i.element_id
            JOIN " . ProjectStructureTable::getInstance()->getTableName() . " b on b.id = e.project_structure_id
            JOIN " . ProjectStructureTable::getInstance()->getTableName() . " p on p.id = b.root_id
            WHERE p.id = {$project->id}
            AND p.deleted_at IS NULL
            AND b.deleted_at IS NULL
            AND e.deleted_at IS NULL
            AND i.deleted_at IS NULL
            AND i.project_revision_deleted_at IS NULL
            ORDER BY i.priority, i.lft, i.level ASC;");

        $stmt->execute();

        $billItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmt = $pdo->prepare("
            SELECT e.id, e.description, e.priority
            FROM " . BillElementTable::getInstance()->getTableName() . " e
            JOIN " . ProjectStructureTable::getInstance()->getTableName() . " b on b.id = e.project_structure_id
            WHERE b.id = {$bill->id}
            AND b.deleted_at IS NULL
            AND e.deleted_at IS NULL
            ORDER BY e.priority ASC;
        ");

        $stmt->execute();

        $elements = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $data = array();

        foreach($elements as $key => $element)
        {
            $element['revenue']             = 0;
            $element['budget']              = 0;
            $element['sub_con_budget']      = 0;
            $element['sub_con_cost']        = 0;
            $element['progress_revenue']    = 0;
            $element['progress_cost']       = 0;
            $element['untagged_item_count'] = 0;

            foreach($billItems as $key => $billItem)
            {
                $billItemId = $billItem['item_id'];
                $elementId  = $billItem['element_id'];

                if( $elementId != $element['id'] ) continue;

                $element['revenue'] += $billItemRates[ $billItemId ]['revenue'];
                $element['budget'] += $billItemRates[ $billItemId ]['budget'];
                $element['sub_con_budget'] += $billItemRates[ $billItemId ]['sub_con_budget'];
                $element['sub_con_cost'] += $billItemRates[ $billItemId ]['sub_con_cost'];
                $element['progress_revenue'] += $billItemRates[ $billItemId ]['progress_revenue'];
                $element['progress_cost'] += $billItemRates[ $billItemId ]['progress_cost'];

                $itemIsTagged = false;

                foreach($subProjects as $subProject)
                {
                    if( ! array_key_exists($billItemId, $subConBillItemRates) || ! array_key_exists($subProject->id, $subConBillItemRates[ $billItemId ]) ) continue;

                    $element['revenue'] += $subConBillItemRates[ $billItemId ][ $subProject->id ]['revenue'];
                    $element['budget'] += $subConBillItemRates[ $billItemId ][ $subProject->id ]['budget'];
                    $element['sub_con_budget'] += $subConBillItemRates[ $billItemId ][ $subProject->id ]['sub_con_budget'];
                    $element['sub_con_cost'] += $subConBillItemRates[ $billItemId ][ $subProject->id ]['sub_con_cost'];
                    $element['progress_revenue'] += $subConBillItemRates[ $billItemId ][ $subProject->id ]['progress_revenue'];
                    $element['progress_cost'] += $subConBillItemRates[ $billItemId ][ $subProject->id ]['progress_cost'];

                    $itemIsTagged = true;
                }

                if( ! $itemIsTagged && ! ( $billItem['type'] == BillItem::TYPE_HEADER || $billItem['type'] == BillItem::TYPE_HEADER_N ) ) $element['untagged_item_count']++;

                unset( $billItems[ $key ] );
            }

            $data[] = $element;

            unset( $element );
        }

        $data[] = [
            'id'                  => Constants::GRID_LAST_ROW,
            'description'         => null,
            'revenue'             => 0,
            'budget'              => 0,
            'sub_con_budget'      => 0,
            'sub_con_cost'        => 0,
            'progress_revenue'    => 0,
            'progress_cost'       => 0,
            'untagged_item_count' => 0,
        ];

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $data
        ));
    }

    public function executeGetProjectBudgetReport(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('pid')) and
            $project->node->isRoot()
        );

        $pdo = ProjectStructureTable::getInstance()->getConnection()->getDbh();

        $subProjects         = ProjectStructureTable::getSubProjects($project);
        $billItemRates       = BudgetReport::getProjectItemRates($project);
        $subConBillItemRates = BudgetReport::getSubProjectItemRates($project);

        $stmt = $pdo->prepare("SELECT i.id as item_id, b.id as bill_id, i.type
            FROM " . BillItemTable::getInstance()->getTableName() . " i
            JOIN " . BillElementTable::getInstance()->getTableName() . " e on e.id = i.element_id
            JOIN " . ProjectStructureTable::getInstance()->getTableName() . " b on b.id = e.project_structure_id
            JOIN " . ProjectStructureTable::getInstance()->getTableName() . " p on p.id = b.root_id
            WHERE p.id = {$project->id}
            AND p.deleted_at IS NULL
            AND b.deleted_at IS NULL
            AND e.deleted_at IS NULL
            AND i.deleted_at IS NULL
            AND i.project_revision_deleted_at IS NULL
            ORDER BY i.priority, i.lft, i.level ASC;");

        $stmt->execute();

        $billItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $tenderAlternative = $project->getAwardedTenderAlternative();

        $tenderAlternativeJoinSql = "";
        $tenderAlternativeWhereSql = "";

        if($tenderAlternative)
        {
            $tenderAlternativeJoinSql = " JOIN ".TenderAlternativeTable::getInstance()->getTableName()." ta ON ta.project_structure_id = p.id
                JOIN ".TenderAlternativeBillTable::getInstance()->getTableName()." tax ON tax.tender_alternative_id = ta.id AND tax.project_structure_id = b.id ";

            $tenderAlternativeWhereSql = " AND ta.id = ".$tenderAlternative->id." AND ta.deleted_at IS NULL AND ta.project_revision_deleted_at IS NULL ";
        }

        $stmt = $pdo->prepare("SELECT b.id, b.title as description, b.type, b.level
            FROM " . ProjectStructureTable::getInstance()->getTableName() . " b
            JOIN " . ProjectStructureTable::getInstance()->getTableName() . " p on p.id = b.root_id
            ".$tenderAlternativeJoinSql."
            WHERE p.id = {$project->id}
            AND b.id != p.id
            AND b.type = " . ProjectStructure::TYPE_BILL . "
            ".$tenderAlternativeWhereSql."
            AND p.deleted_at IS NULL
            AND b.deleted_at IS NULL
            ORDER BY b.priority, b.lft, b.level ASC;");

        $stmt->execute();

        $bills = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $data = array();

        foreach($bills as $key => $bill)
        {
            $bill['revenue']             = 0;
            $bill['budget']              = 0;
            $bill['sub_con_budget']      = 0;
            $bill['sub_con_cost']        = 0;
            $bill['progress_revenue']    = 0;
            $bill['progress_cost']       = 0;
            $bill['untagged_item_count'] = 0;

            foreach($billItems as $key => $billItem)
            {
                $billItemId = $billItem['item_id'];
                $billId     = $billItem['bill_id'];

                if( $billId != $bill['id'] ) continue;

                $bill['revenue'] += $billItemRates[ $billItemId ]['revenue'];
                $bill['budget'] += $billItemRates[ $billItemId ]['budget'];
                $bill['sub_con_budget'] += $billItemRates[ $billItemId ]['sub_con_budget'];
                $bill['sub_con_cost'] += $billItemRates[ $billItemId ]['sub_con_cost'];
                $bill['progress_revenue'] += $billItemRates[ $billItemId ]['progress_revenue'];
                $bill['progress_cost'] += $billItemRates[ $billItemId ]['progress_cost'];

                $itemIsTagged = false;

                foreach($subProjects as $subProject)
                {
                    if( ! array_key_exists($billItemId, $subConBillItemRates) || ! array_key_exists($subProject->id, $subConBillItemRates[ $billItemId ]) ) continue;

                    $bill['revenue'] += $subConBillItemRates[ $billItemId ][ $subProject->id ]['revenue'];
                    $bill['budget'] += $subConBillItemRates[ $billItemId ][ $subProject->id ]['budget'];
                    $bill['sub_con_budget'] += $subConBillItemRates[ $billItemId ][ $subProject->id ]['sub_con_budget'];
                    $bill['sub_con_cost'] += $subConBillItemRates[ $billItemId ][ $subProject->id ]['sub_con_cost'];
                    $bill['progress_revenue'] += $subConBillItemRates[ $billItemId ][ $subProject->id ]['progress_revenue'];
                    $bill['progress_cost'] += $subConBillItemRates[ $billItemId ][ $subProject->id ]['progress_cost'];

                    $itemIsTagged = true;
                }

                if( ! $itemIsTagged && ! ( $billItem['type'] == BillItem::TYPE_HEADER || $billItem['type'] == BillItem::TYPE_HEADER_N ) ) $bill['untagged_item_count']++;

                unset( $billItems[ $key ] );
            }

            $data[] = $bill;

            unset( $bill );
        }

        $variationOrderItemRates       = BudgetReport::getProjectVariationOrderItemRates($project);
        $subConVariationOrderItemRates = BudgetReport::getSubProjectVariationOrderItemRates($project);

        $stmt = $pdo->prepare("SELECT i.id, i.type
            FROM " . VariationOrderItemTable::getInstance()->getTableName() . " i
            JOIN " . VariationOrderTable::getInstance()->getTableName() . " vo on vo.id = i.variation_order_id
            JOIN " . ProjectStructureTable::getInstance()->getTableName() . " p on p.id = vo.project_structure_id
            WHERE p.id = {$project->id}
            AND vo.is_approved = true
            AND p.deleted_at IS NULL
            AND vo.deleted_at IS NULL
            AND i.deleted_at IS NULL
            ORDER BY i.priority, i.lft, i.level ASC;");

        $stmt->execute();

        $variationOrderItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $variationOrderData = array(
            'revenue'             => 0,
            'sub_con_cost'        => 0,
            'progress_revenue'    => 0,
            'progress_cost'       => 0,
            'untagged_item_count' => 0,
        );

        foreach($variationOrderItems as $key => $item)
        {
            $itemId = $item['id'];

            $variationOrderData['revenue'] += $variationOrderItemRates[ $itemId ]['revenue'] ?? 0;
            $variationOrderData['sub_con_cost'] += $variationOrderItemRates[ $itemId ]['sub_con_cost'] ?? 0;
            $variationOrderData['progress_revenue'] += $variationOrderItemRates[ $itemId ]['progress_revenue'] ?? 0;
            $variationOrderData['progress_cost'] += $variationOrderItemRates[ $itemId ]['progress_cost'] ?? 0;

            $itemIsTagged = false;

            foreach($subProjects as $subProject)
            {
                if( ! array_key_exists($itemId, $subConVariationOrderItemRates) || ! array_key_exists($subProject->id, $subConVariationOrderItemRates[ $itemId ]) ) continue;

                $variationOrderData['revenue'] += $subConVariationOrderItemRates[ $itemId ][ $subProject->id ]['revenue'];
                $variationOrderData['sub_con_cost'] += $subConVariationOrderItemRates[ $itemId ][ $subProject->id ]['sub_con_cost'];
                $variationOrderData['progress_revenue'] += $subConVariationOrderItemRates[ $itemId ][ $subProject->id ]['progress_revenue'];
                $variationOrderData['progress_cost'] += $subConVariationOrderItemRates[ $itemId ][ $subProject->id ]['progress_cost'];

                $itemIsTagged = true;
            }

            if( ! $itemIsTagged && ( $item['type'] == VariationOrderItem::TYPE_WORK_ITEM ) ) $variationOrderData['untagged_item_count']++;

            unset( $variationOrderItems[ $key ] );
        }

        $data[] = [
            'id'                  => Constants::GRID_LAST_ROW,
            'description'         => "",
            'type'                => -1,
            'level'               => 0,
            'revenue'             => 0,
            'budget'              => 0,
            'sub_con_budget'      => 0,
            'sub_con_cost'        => 0,
            'progress_revenue'    => 0,
            'progress_cost'       => 0,
            'untagged_item_count' => 0,
        ];

        $data[] = [
            'id'                  => PostContractClaim::TYPE_VARIATION_ORDER_TEXT,
            'description'         => PostContractClaim::TYPE_VARIATION_ORDER_TEXT,
            'type'                => PostContractClaim::TYPE_VARIATION_ORDER,
            'level'               => 0,
            'revenue'             => $variationOrderData['revenue'],
            'budget'              => 0,
            'sub_con_budget'      => 0,
            'sub_con_cost'        => $variationOrderData['sub_con_cost'],
            'progress_revenue'    => $variationOrderData['progress_revenue'],
            'progress_cost'       => $variationOrderData['progress_cost'],
            'untagged_item_count' => $variationOrderData['untagged_item_count'],
        ];

        $data[] = [
            'id'                  => -999,
            'description'         => '',
            'type'                => 1,
            'level'               => 0,
            'revenue'             => 0,
            'budget'              => 0,
            'sub_con_budget'      => 0,
            'sub_con_cost'        => 0,
            'progress_revenue'    => 0,
            'progress_cost'       => 0,
            'untagged_item_count' => 0,
        ];

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $data
        ));
    }

    public function executeGetVariationOrderItemList(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('pid')) and
            $variationOrder = Doctrine_Core::getTable('VariationOrder')->find($request->getParameter('vo_id'))
        );

        $pdo = ProjectStructureTable::getInstance()->getConnection()->getDbh();

        $itemRates = BudgetReport::getProjectVariationOrderItemRates($project);

        $subConItemRates = BudgetReport::getSubProjectVariationOrderItemRates($project);

        $stmt = $pdo->prepare("SELECT i.id, i.description, uom.id AS uom_id, uom.symbol AS uom_symbol, i.type, i.lft, i.level
            FROM " . VariationOrderItemTable::getInstance()->getTableName() . " i
            JOIN " . VariationOrderTable::getInstance()->getTableName() . " vo on vo.id = i.variation_order_id
            LEFT JOIN " . UnitOfMeasurementTable::getInstance()->getTableName() . " uom on uom.id = i.uom_id
            WHERE vo.id = {$variationOrder->id}
            AND vo.is_approved = true
            AND vo.deleted_at IS NULL
            AND i.deleted_at IS NULL
            ORDER BY i.priority, i.lft, i.level ASC;");

        $stmt->execute();

        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $subProjects = ProjectStructureTable::getSubProjects($project);

        $data = array();

        foreach($items as $key => $item)
        {
            $item['revenue']          = $itemRates[ $item['id'] ]['revenue'] ?? 0;
            $item['progress_revenue'] = $itemRates[ $item['id'] ]['progress_revenue'] ?? 0;
            $item['sub_con_cost']     = $itemRates[ $item['id'] ]['sub_con_cost'] ?? 0;
            $item['progress_cost']    = $itemRates[ $item['id'] ]['progress_cost'] ?? 0;
            $item['is_tagged']        = ( $item['type'] == VariationOrderItem::TYPE_HEADER ) ? true : ( ! empty( $subConItemRates[ $item['id'] ] ) );

            if( $item['type'] == VariationOrderItem::TYPE_HEADER )
            {
                $data[] = $item;
                unset( $items[$key] );
                continue;
            }

            $subConDataArray = array();

            foreach($subProjects as $subProject)
            {
                if( ! array_key_exists($item['id'], $subConItemRates) || ! array_key_exists($subProject->id, $subConItemRates[ $item['id'] ]) ) continue;

                $subConData = array();

                $subConData['id']          = "{$item['id']}-{$subProject->id}";
                $subConData['description'] = $subProject->TenderSetting->AwardedCompany->name;
                $subConData['level']       = $item['level'];
                $subConData['uom_id']      = $item['uom_id'];
                $subConData['uom_symbol']  = $item['uom_symbol'];

                $subConData['revenue']          = $subConItemRates[ $item['id'] ][ $subProject->id ]['revenue'];
                $subConData['progress_revenue'] = $subConItemRates[ $item['id'] ][ $subProject->id ]['progress_revenue'];
                $subConData['sub_con_cost']     = $subConItemRates[ $item['id'] ][ $subProject->id ]['sub_con_cost'];
                $subConData['progress_cost']    = $subConItemRates[ $item['id'] ][ $subProject->id ]['progress_cost'];
                $subConData['is_sub_project']   = true;

                $item['sub_con_cost'] += $subConData['sub_con_cost'];
                $item['progress_cost'] += $subConData['progress_cost'];

                $subConDataArray[] = $subConData;
            }

            $data[] = $item;

            foreach($subConDataArray as $subConData)
            {
                $data[] = $subConData;
            }

            unset( $items[$key] );
        }

        $data[] = [
            'id'               => Constants::GRID_LAST_ROW,
            'description'      => null,
            'uom_id'           => '-1',
            'uom_symbol'       => '',
            'revenue'          => 0,
            'sub_con_cost'     => 0,
            'progress_revenue' => 0,
            'progress_cost'    => 0,
        ];

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $data
        ));
    }

    public function executeGetVariationOrderList(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('pid'))
        );

        $pdo = ProjectStructureTable::getInstance()->getConnection()->getDbh();

        $subProjects     = ProjectStructureTable::getSubProjects($project);
        $itemRates       = BudgetReport::getProjectVariationOrderItemRates($project);
        $subConItemRates = BudgetReport::getSubProjectVariationOrderItemRates($project);

        $stmt = $pdo->prepare("SELECT i.id as item_id, vo.id as variation_order_id, i.type
            FROM " . VariationOrderItemTable::getInstance()->getTableName() . " i
            JOIN " . VariationOrderTable::getInstance()->getTableName() . " vo on vo.id = i.variation_order_id
            JOIN " . ProjectStructureTable::getInstance()->getTableName() . " p on p.id = vo.project_structure_id
            WHERE p.id = {$project->id}
            AND vo.is_approved = true
            AND p.deleted_at IS NULL
            AND vo.deleted_at IS NULL
            AND i.deleted_at IS NULL
            ORDER BY i.priority, i.lft, i.level ASC;");

        $stmt->execute();

        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmt = $pdo->prepare("
            SELECT vo.id, vo.description, vo.priority
            FROM " . VariationOrderTable::getInstance()->getTableName() . " vo
            JOIN " . ProjectStructureTable::getInstance()->getTableName() . " p on p.id = vo.project_structure_id
            WHERE p.id = {$project->id}
            AND vo.is_approved = true
            AND p.deleted_at IS NULL
            AND vo.deleted_at IS NULL
            ORDER BY vo.priority ASC;
        ");

        $stmt->execute();

        $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $data = array();

        foreach($records as $key => $record)
        {
            $record['revenue']             = 0;
            $record['sub_con_cost']        = 0;
            $record['progress_revenue']    = 0;
            $record['progress_cost']       = 0;
            $record['untagged_item_count'] = 0;

            foreach($items as $key => $item)
            {
                $itemId = $item['item_id'];
                $variationOrderId  = $item['variation_order_id'];

                if( $variationOrderId != $record['id'] ) continue;

                $record['revenue'] += $itemRates[ $itemId ]['revenue'] ?? 0;
                $record['sub_con_cost'] += $itemRates[ $itemId ]['sub_con_cost'] ?? 0;
                $record['progress_revenue'] += $itemRates[ $itemId ]['progress_revenue'] ?? 0;
                $record['progress_cost'] += $itemRates[ $itemId ]['progress_cost'] ?? 0;

                $itemIsTagged = false;

                foreach($subProjects as $subProject)
                {
                    if( ! array_key_exists($itemId, $subConItemRates) || ! array_key_exists($subProject->id, $subConItemRates[ $itemId ]) ) continue;

                    $record['revenue'] += $subConItemRates[ $itemId ][ $subProject->id ]['revenue'];
                    $record['sub_con_cost'] += $subConItemRates[ $itemId ][ $subProject->id ]['sub_con_cost'];
                    $record['progress_revenue'] += $subConItemRates[ $itemId ][ $subProject->id ]['progress_revenue'];
                    $record['progress_cost'] += $subConItemRates[ $itemId ][ $subProject->id ]['progress_cost'];

                    $itemIsTagged = true;
                }

                if( ! $itemIsTagged && ( $item['type'] == VariationOrderItem::TYPE_WORK_ITEM ) ) $record['untagged_item_count']++;

                unset( $items[ $key ] );
            }

            $data[] = $record;

            unset( $record );
        }

        $data[] = [
            'id'                  => Constants::GRID_LAST_ROW,
            'description'         => null,
            'revenue'             => 0,
            'sub_con_cost'        => 0,
            'progress_revenue'    => 0,
            'progress_cost'       => 0,
            'untagged_item_count' => 0,
        ];

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $data
        ));
    }
}
