<?php

class TenderComparison {

    public static function getCostDataItemTendererRates(CostData $costData, array $masterCostDataItemIds, ProjectStructure $project)
    {
        $pdo = CostDataTable::getInstance()->getConnection()->getDbh();

        $descendantIds = MasterCostDataItemTable::getDescendantIds($masterCostDataItemIds);

        $allItemIds = array_merge($masterCostDataItemIds, $descendantIds);

        if(empty($allItemIds)) return array();

        $implodedAllItemIds = implode(',', $allItemIds);

        $stmt = $pdo->prepare("SELECT ci.master_cost_data_item_id as id, tc.company_id, SUM(ROUND(COALESCE(r.grand_total, 0), 2)) as grand_total
            FROM " . TenderBillItemRateTable::getInstance()->getTableName() . " r
            JOIN " . TenderCompanyTable::getInstance()->getTableName() . " tc on tc.id = r.tender_company_id
            JOIN " . BillItemCostDataItemTable::getInstance()->getTableName() . " pivot on pivot.bill_item_id = r.bill_item_id
            JOIN " . CostDataItemTable::getInstance()->getTableName() . " ci on ci.id = pivot.cost_data_item_id
            WHERE ci.master_cost_data_item_id in ({$implodedAllItemIds})
            AND ci.cost_data_id = :costDataId
            AND tc.project_structure_id = :projectStructureId
            GROUP BY ci.master_cost_data_item_id, tc.company_id;");

        $stmt->execute(array('costDataId' => $costData['id'], 'projectStructureId' => $project->id));

        $tendererRates = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $data = array();

        foreach($masterCostDataItemIds as $masterCostDataItemId)
        {
            $data[$masterCostDataItemId] = array();

            $matchingIds = MasterCostDataItemTable::getDescendantIds([$masterCostDataItemId]);

            $matchingIds[] = $masterCostDataItemId;

            foreach($tendererRates as $key => $tendererRate)
            {
                if(in_array($tendererRate['id'], $matchingIds))
                {
                    if(!isset($data[$masterCostDataItemId][$tendererRate['company_id']])) $data[$masterCostDataItemId][$tendererRate['company_id']] = 0;

                    $data[$masterCostDataItemId][$tendererRate['company_id']] += $tendererRate['grand_total'];

                    unset($tendererRates[$key]);
                }
            }
        }

        return $data;
    }
}