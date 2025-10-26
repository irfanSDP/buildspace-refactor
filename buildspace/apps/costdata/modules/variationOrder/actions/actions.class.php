<?php

/**
 * variationOrder actions.
 *
 * @package    buildspace
 * @subpackage variationOrder
 * @author     1337 developers
 * @version    SVN: $Id$
 */
class variationOrderActions extends sfActions
{
    public function executeGetLinkedItems(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $costData = Doctrine_Core::getTable('CostData')->find($request->getParameter('cost_data_id')) and
            $masterItem = Doctrine_Core::getTable('MasterCostDataItem')->find($request->getParameter('id'))
        );

        $items = VariationOrderItemCostDataItemTable::getLinkedItems($costData, $masterItem);

        $ids = array_column($items, $request->getParameter('id_type'));

        return $this->renderJson(array(
            'success' => true,
            'ids'     => $ids,
        ));
    }

    public function executeGetProjectList(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $costData = Doctrine_Core::getTable('CostData')->find($request->getParameter('costData')) and
            $masterItem = Doctrine_Core::getTable('MasterCostDataItem')->find($request->getParameter('masterItem'))
        );

        $projects = $costData->getProjects(array(ProjectMainInformation::STATUS_POSTCONTRACT, ProjectMainInformation::STATUS_TENDERING));

        $linkedItems = VariationOrderItemCostDataItemTable::getLinkedItems($costData, $masterItem);

        $linkedIds = array_column($linkedItems, 'project_id');

        foreach($projects as $key => $project)
        {
            $projects[$key]['description'] = $project['title'];
            $projects[$key]['is_linked']   = in_array($project['id'], $linkedIds);
        }

        array_push($projects, array(
            'id'          => Constants::GRID_LAST_ROW,
            'description' => null,
            'is_linked'   => null,
        ));

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $projects
        ));
    }

    public function executeGetVariationOrderList(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $costData = Doctrine_Core::getTable('CostData')->find($request->getParameter('costData')) and
            $masterItem = Doctrine_Core::getTable('MasterCostDataItem')->find($request->getParameter('masterItem')) and
            $project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('pid'))
        );

        $pdo = ProjectStructureTable::getInstance()->getConnection()->getDbh();

        $stmt = $pdo->prepare("SELECT vo.id, vo.description
            FROM " . VariationOrderTable::getInstance()->getTableName() . " vo
            JOIN " . ProjectStructureTable::getInstance()->getTableName() . " p on p.id = vo.project_structure_id
            WHERE p.id = {$project->id}
            AND vo.is_approved = TRUE
            AND p.deleted_at IS NULL
            AND vo.deleted_at IS NULL
            ORDER BY vo.priority;");

        $stmt->execute();

        $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $linkedItems = VariationOrderItemCostDataItemTable::getLinkedItems($costData, $masterItem);

        $linkedIds = array_column($linkedItems, 'variation_order_id');

        $form = new BaseForm();

        foreach($records as $key => $record)
        {
            $records[$key]['is_linked']   = in_array($record['id'], $linkedIds);
            $records[$key]['_csrf_token'] = $form->getCSRFToken();
        }

        array_push($records, array(
            'id'          => Constants::GRID_LAST_ROW,
            'description' => null,
            'is_linked'   => null,
        ));

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $records
        ));
    }

    public function executeGetVariationOrderItemList(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $costData = Doctrine_Core::getTable('CostData')->find($request->getParameter('costData')) and
            $variationOrder = Doctrine_Core::getTable('VariationOrder')->find($request->getParameter('void'))
        );

        $pdo = ProjectStructureTable::getInstance()->getConnection()->getDbh();

        $stmt = $pdo->prepare("SELECT i.id, i.description, uom.id AS uom_id, uom.symbol AS uom_symbol,
            ROUND(COALESCE((i.total_unit * i.addition_quantity * i.rate) - (i.total_unit * i.omission_quantity * i.rate)), 2) AS nett_omission_addition
            FROM " . VariationOrderItemTable::getInstance()->getTableName() . " i
            LEFT JOIN " . UnitOfMeasurementTable::getInstance()->getTableName() . " uom ON uom.id = i.uom_id
            JOIN " . VariationOrderTable::getInstance()->getTableName() . " vo ON vo.id = i.variation_order_id
            JOIN " . ProjectStructureTable::getInstance()->getTableName() . " p ON p.id = vo.project_structure_id
            WHERE vo.id = {$variationOrder->id}
            AND p.deleted_at IS NULL
            AND vo.deleted_at IS NULL
            AND i.deleted_at IS NULL
            ORDER BY i.priority, i.lft, i.level;");

        $stmt->execute();

        $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

        array_push($records, array(
            'id'          => Constants::GRID_LAST_ROW,
            'description' => null,
            'uom_id'      => -1,
            'uom_symbol'  => null,
        ));

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $records
        ));
    }

    public function executeLinkItems(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $costData = Doctrine_Core::getTable('CostData')->find($request->getParameter('cost_data_id')) and
            $masterItem = Doctrine_Core::getTable('MasterCostDataItem')->find($request->getParameter('id'))
        );

        $selectedBillItemIds   = empty($request->getParameter('selectedIds')) ? array() : explode(',', $request->getParameter('selectedIds'));
        $deselectedBillItemIds = empty($request->getParameter('deselectedIds')) ? array() : explode(',', $request->getParameter('deselectedIds'));

        $success  = false;
        $errorMsg = null;

        try
        {
            VariationOrderItemCostDataItemTable::sync($costData, $masterItem, $selectedBillItemIds, $deselectedBillItemIds);

            $success = true;
        }
        catch(Exception $e)
        {
            $errorMsg = $e->getMessage();
        }

        $items = VariationOrderItemCostDataItemTable::getLinkedItems($costData, $masterItem);

        $itemIds = array_column($items, 'variation_order_item_id');

        return $this->renderJson(array(
            'success'  => $success,
            'errorMsg' => $errorMsg,
            'item_ids' => $itemIds,
        ));
    }
}
