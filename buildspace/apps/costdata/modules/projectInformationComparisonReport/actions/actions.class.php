<?php

/**
 * projectInformationComparisonReport actions.
 *
 * @package    buildspace
 * @subpackage projectInformationComparisonReport
 * @author     1337 developers
 * @version    SVN: $Id$
 */
class projectInformationComparisonReportActions extends BaseActions
{
    public function executeExport(sfWebRequest $request)
    {
        $this->forward404Unless(
            $costData = Doctrine_Core::getTable('CostData')->find($request->getParameter('costData'))
        );

        if($request->hasParameter('parent_id'))
        {
            $reportGenerator = new sfCostDataProjectInformationItemListComparisonExcelReportGenerator($costData);
            $reportGenerator->setParameters($request->getParameter('selected_ids') ?? array(), $request->getParameter('parent_id'));
        }
        else
        {
            $reportGenerator = new sfCostDataProjectInformationBreakdownComparisonExcelReportGenerator($costData);
            $reportGenerator->setParameters($request->getParameter('selected_ids') ?? array());
        }

        $reportGenerator->generateReport();

        return $this->sendExportExcelHeader($reportGenerator->fileInfo['filename'], $reportGenerator->savePath . DIRECTORY_SEPARATOR . $reportGenerator->fileInfo['filename'] . $reportGenerator->fileInfo['extension']);
    }

    public function executeGetBreakdown(sfWebRequest $request)
    {
        $this->forward404Unless(
            $costData = Doctrine_Core::getTable('CostData')->find($request->getParameter('cost_data_id'))
        );

        $masterCostData = $costData->MasterCostData;

        $selectedIds = $request->hasParameter('selected_ids') ? explode(',', $request->getParameter('selected_ids')) : array();

        array_unshift($selectedIds, $costData->id);

        $pdo = CostDataTable::getInstance()->getConnection()->getDbh();

        $stmt = $pdo->prepare("
            SELECT mi.id, mi.description
            FROM " . MasterCostDataProjectInformationTable::getInstance()->getTableName() . " mi
            WHERE mi.master_cost_data_id = {$masterCostData->id}
            AND mi.deleted_at IS NULL
            AND mi.level = 1
            ORDER BY priority;
            ");

        $stmt->execute();

        $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmt = $pdo->prepare("
            SELECT mi.parent_id, mi.id, mi.description
            FROM " . MasterCostDataProjectInformationTable::getInstance()->getTableName() . " mi
            WHERE mi.master_cost_data_id = {$masterCostData->id}
            AND mi.deleted_at IS NULL
            AND mi.level = 2
            ORDER BY priority;
            ");

        $stmt->execute();

        $secondLevelRecords = $stmt->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_ASSOC);

        $questionMarks = '(' . implode(',', array_fill(0, count($selectedIds), '?')) . ')';

        $stmt = $pdo->prepare("
            SELECT mi.id, i.cost_data_id, i.description
            FROM " . CostDataProjectInformationTable::getInstance()->getTableName() . " i
            JOIN " . MasterCostDataProjectInformationTable::getInstance()->getTableName() . " mi on mi.id = i.master_cost_data_project_information_id
            WHERE mi.master_cost_data_id = {$masterCostData->id}
            AND i.cost_data_id IN {$questionMarks}
            AND mi.deleted_at IS NULL
            ");

        $stmt->execute($selectedIds);

        $costDataProjectInformation = $stmt->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_ASSOC);

        foreach($costDataProjectInformation as $masterInfoId => $costDataMasterInfo)
        {
            $costDataProjectInformation[$masterInfoId] = Utilities::getKeyPairFromAttributes($costDataMasterInfo, 'cost_data_id', 'description');
        }

        $data = [];

        foreach($records as $record)
        {
            $data[] = [
                'id'    => $record['id'],
                'item'  => $record['description'],
                'level' => 1,
            ];

            foreach($secondLevelRecords[$record['id']] as $secondLevelRecord)
            {
                $secondLevelRow = [
                    'id'    => $secondLevelRecord['id'],
                    'item'  => $secondLevelRecord['description'],
                    'level' => 2,
                ];

                foreach($selectedIds as $selectedId)
                {
                    $secondLevelRow['description-'.$selectedId] = $costDataProjectInformation[$secondLevelRecord['id']][$selectedId] ?? "";
                }

                $data[] = $secondLevelRow;
            }
        }

        array_push($data, array(
            'id'          => Constants::GRID_LAST_ROW,
            'description' => "",
            'type'        => MasterCostData::ITEM_TYPE_STANDARD,
        ));

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $data
        ));
    }

    public function executeGetItemList(sfWebRequest $request)
    {
        $this->forward404Unless(
            $costData = Doctrine_Core::getTable('CostData')->find($request->getParameter('cost_data_id')) and
            $parentProjectInformationItem = Doctrine_Core::getTable('MasterCostDataProjectInformation')->find($request->getParameter('parent_id'))
        );

        $masterCostData = $costData->MasterCostData;

        $selectedIds = $request->hasParameter('selected_ids') ? explode(',', $request->getParameter('selected_ids')) : array();

        array_unshift($selectedIds, $costData->id);

        $pdo = CostDataTable::getInstance()->getConnection()->getDbh();

        $stmt = $pdo->prepare("
            SELECT mi.id, mi.description
            FROM " . MasterCostDataProjectInformationTable::getInstance()->getTableName() . " mi
            WHERE mi.master_cost_data_id = {$masterCostData->id}
            AND mi.deleted_at IS NULL
            AND mi.level = 2
            AND mi.parent_id = {$parentProjectInformationItem->id}
            ORDER BY priority;
            ");

        $stmt->execute();

        $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $questionMarks = '(' . implode(',', array_fill(0, count($selectedIds), '?')) . ')';

        $stmt = $pdo->prepare("
            SELECT mi.id, i.cost_data_id, i.description
            FROM " . CostDataProjectInformationTable::getInstance()->getTableName() . " i
            JOIN " . MasterCostDataProjectInformationTable::getInstance()->getTableName() . " mi on mi.id = i.master_cost_data_project_information_id
            WHERE mi.master_cost_data_id = {$masterCostData->id}
            AND i.cost_data_id IN {$questionMarks}
            AND mi.parent_id = {$parentProjectInformationItem->id}
            AND mi.deleted_at IS NULL
            ");

        $stmt->execute($selectedIds);

        $costDataProjectInformation = $stmt->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_ASSOC);

        foreach($costDataProjectInformation as $masterInfoId => $costDataMasterInfo)
        {
            $costDataProjectInformation[$masterInfoId] = Utilities::getKeyPairFromAttributes($costDataMasterInfo, 'cost_data_id', 'description');
        }

        $data = [];

        foreach($records as $record)
        {
            $row = [
                'id'    => $record['id'],
                'item'  => $record['description'],
            ];

            foreach($selectedIds as $selectedId)
            {
                $row['description-'.$selectedId] = $costDataProjectInformation[$record['id']][$selectedId] ?? "";
            }

            $data[] = $row;
        }

        array_push($data, array(
            'id'          => Constants::GRID_LAST_ROW,
            'description' => "",
        ));

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $data
        ));
    }
}
