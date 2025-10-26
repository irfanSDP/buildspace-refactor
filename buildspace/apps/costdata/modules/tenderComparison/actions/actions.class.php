<?php

/**
 * tenderComparison actions.
 *
 * @package    buildspace
 * @subpackage tenderComparison
 * @author     1337 developers
 * @version    SVN: $Id$
 */
class tenderComparisonActions extends BaseActions
{
	public function executeGetProjectList(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $costData = Doctrine_Core::getTable('CostData')->find($request->getParameter('costData'))
        );

        $projects = $costData->getProjects(array(ProjectMainInformation::STATUS_TENDERING));

        array_push($projects, array(
            'id'          => Constants::GRID_LAST_ROW,
            'title' => null,
        ));

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $projects
        ));
    }

    public function executeGetTenderCompanies(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('pid'))
        );

        $pdo = CostDataTable::getInstance()->getConnection()->getDbh();

        $stmt = $pdo->prepare("SELECT c.id, c.name
            FROM " . TenderCompanyTable::getInstance()->getTableName() . " tc
            JOIN " . CompanyTable::getInstance()->getTableName() . " c on c.id = tc.company_id
            WHERE tc.project_structure_id = :project_structure_id");

        $stmt->execute(array(
            'project_structure_id' => $project->id
        ));

        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $this->renderJson(array(
            'data' => $data,
        ));
    }

    public function executeGetBreakdown(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $costData = Doctrine_Core::getTable('CostData')->find($request->getParameter('cost_data_id')) and
            $project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('pid'))
        );

        $pdo = CostDataTable::getInstance()->getConnection()->getDbh();

        $items = CostDataItemTable::getItemList($costData, null);

        $masterItemIds = array_column($items, 'id');

        $stmt = $pdo->prepare("SELECT tc.company_id FROM " . TenderCompanyTable::getInstance()->getTableName() . " tc
        WHERE tc.project_structure_id = :project_structure_id");

        $stmt->execute(array(
            'project_structure_id' => $project->id
        ));

        $tenderCompanyIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

        $itemRates = TenderComparison::getCostDataItemTendererRates($costData, $masterItemIds, $project);

        $records = array();

        foreach($items as $key => $item)
        {
            foreach($tenderCompanyIds as $tenderCompanyId)
            {
                $item["{$tenderCompanyId}_amount"] = $itemRates[$item['id']][$tenderCompanyId] ?? 0;
            }

            $records[] = $item;
        }

        array_push($records, array(
            'id'          => Constants::GRID_LAST_ROW,
            'description' => "",
            'type'        => MasterCostData::ITEM_TYPE_STANDARD,
        ));

        $totalRow = array(
            'id'          => 'total',
            'description' => 'Total',
            'type'        => 'summary',
        );

        foreach($tenderCompanyIds as $tenderCompanyId)
        {
            $totalRow["{$tenderCompanyId}_amount"] = array_sum(array_column($records, "{$tenderCompanyId}_amount"));
        }

        $records[] = $totalRow;

        $stmt = $pdo->prepare("
            SELECT p.id, p.description, p.summary_description, COALESCE(cdp.value, 0) AS value FROM " . MasterCostDataParticularTable::getInstance()->getTableName() . " p
            JOIN " . CostDataTable::getInstance()->getTableName() . " cd on cd.master_cost_data_id = p.master_cost_data_id
            LEFT JOIN " . CostDataParticularTable::getInstance()->getTableName() . " cdp on cdp.master_cost_data_particular_id = p.id AND cdp.cost_data_id = cd.id
            WHERE cd.id = {$costData->id}
            AND p.is_summary_displayed = TRUE
            AND p.deleted_at IS NULL
            ORDER BY p.priority ASC
            ");

        $stmt->execute();

        $summaryParticulars = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach($summaryParticulars as $particular)
        {
            $row = array(
                'id'          => 'particular-'.$particular['id'],
                'description' => empty($particular['summary_description']) ? "Total Cost/{$particular['description']}" : $particular['summary_description'],
                'type'        => 'summary',
            );

            foreach($tenderCompanyIds as $tenderCompanyId)
            {
                $row["{$tenderCompanyId}_amount"] = Utilities::divide($totalRow["{$tenderCompanyId}_amount"], $particular['value']);
            }

            $records[] = $row;
        }

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $records
        ));
    }

    public function executeGetWorkCategoryList(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $costData = Doctrine_Core::getTable('CostData')->find($request->getParameter('cost_data_id')) and
            $project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('pid')) and
            $masterProjectCostingItem = Doctrine_Core::getTable('MasterCostDataItem')->find($request->getParameter('parent_id'))
        );

        $pdo = CostDataTable::getInstance()->getConnection()->getDbh();

        $items = CostDataItemTable::getItemList($costData, $masterProjectCostingItem->id);

        $masterItemIds = array_column($items, 'id');

        $stmt = $pdo->prepare("SELECT tc.company_id FROM " . TenderCompanyTable::getInstance()->getTableName() . " tc
        WHERE tc.project_structure_id = :project_structure_id");

        $stmt->execute(array(
            'project_structure_id' => $project->id
        ));

        $tenderCompanyIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

        $itemRates = TenderComparison::getCostDataItemTendererRates($costData, $masterItemIds, $project);

        $records = array();

        foreach($items as $key => $item)
        {
            foreach($tenderCompanyIds as $tenderCompanyId)
            {
                $item["{$tenderCompanyId}_amount"] = $itemRates[$item['id']][$tenderCompanyId] ?? 0;
            }

            $records[] = $item;
        }

        array_push($records, array(
            'id'          => Constants::GRID_LAST_ROW,
            'description' => "",
            'type'        => MasterCostData::ITEM_TYPE_STANDARD,
        ));

        $totalRow = array(
            'id'          => 'total',
            'description' => 'Total',
            'type'        => 'summary',
        );

        foreach($tenderCompanyIds as $tenderCompanyId)
        {
            $totalRow["{$tenderCompanyId}_amount"] = array_sum(array_column($records, "{$tenderCompanyId}_amount"));
        }

        $records[] = $totalRow;

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $records
        ));
    }

    public function executeGetElementList(sfWebRequest $request)
    {
        return $this->executeGetWorkCategoryList($request);
    }

    public function executeExport(sfWebRequest $request)
    {
        $this->forward404Unless(
            $costData = Doctrine_Core::getTable('CostData')->find($request->getParameter('costData')) and
            $project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('pid'))
        );

        $reportGenerator = new sfCostDataTenderComparisonExcelReportGenerator($costData, $project);

        $reportGenerator->setParameters($request->getParameter('parent_id') ?? 0, $request->getParameter('level'));

        $reportGenerator->generateReport();

        return $this->sendExportExcelHeader($reportGenerator->fileInfo['filename'], $reportGenerator->savePath . DIRECTORY_SEPARATOR . $reportGenerator->fileInfo['filename'] . $reportGenerator->fileInfo['extension']);
    }
}
