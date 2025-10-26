<?php

/**
 * primeCostRate actions.
 *
 * @package    buildspace
 * @subpackage primeCostRate
 * @author     1337 developers
 * @version    SVN: $Id$
 */
class primeCostRateActions extends BaseActions
{
    public function executeGetBreakdown(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $costData = Doctrine_Core::getTable('CostData')->find($request->getParameter('costData'))
        );

        $pdo = $costData->getTable()->getConnection()->getDbh();

        $items = CostDataPrimeCostRateTable::getItemList($costData, $request->getParameter('parent_id'));

        $masterIds = array_column($items, 'id');

        $values            = CostDataPrimeCostRateTable::getRecordValues($costData, $masterIds);
        $itemDerivedStatus = CostDataPrimeCostRateTable::itemValuesAreDerived($costData, $masterIds);

        $form = new BaseForm();

        $records = [];

        foreach($items as $key => $item)
        {
            $item['units']                  = $values[ $item['id'] ]['units'];
            $item['approved_value']         = $values[ $item['id'] ]['approved_value'];
            $item['approved_value_derived'] = $itemDerivedStatus[ $item['id'] ]['approved_value'];
            $item['approved_total']         = $item['units'] * $item['approved_value'];
            $item['approved_brand']         = $values[ $item['id'] ]['approved_brand'];
            $item['awarded_value']          = $values[ $item['id'] ]['awarded_value'];
            $item['awarded_value_derived']  = $itemDerivedStatus[ $item['id'] ]['awarded_value'];
            $item['awarded_total']          = $item['units'] * $item['awarded_value'];
            $item['awarded_brand']          = $values[ $item['id'] ]['awarded_brand'];
            $item['_csrf_token']            = $form->getCSRFToken();

            $records[] = $item;
        }

        array_push($records, array(
            'id'             => Constants::GRID_LAST_ROW,
            'description'    => "",
            'uom_id'         => -1,
            'uom_symbol'     => '',
            'approved_brand' => null,
            'awarded_brand'  => null,
        ));

        $level = $request->getParameter('level');

        array_push($records, array(
            'id'             => 'total',
            'description'    => "Total",
            'uom_id'         => -1,
            'uom_symbol'     => '',
            'type'           => 'summary',
            'approved_brand' => null,
            'awarded_brand'  => null,
            'units'          => $units = ($level == 2 ? array_sum(array_column($records, 'units')) : 0),
            'approved_total' => $approvedTotal = array_sum(array_column($records, 'approved_total')),
            'awarded_total'  => $awardedTotal = array_sum(array_column($records, 'awarded_total')),
        ));

        if( $level == 2 )
        {
            array_push($records, array(
                'id'             => 'cost_per_type',
                'description'    => "Avg Cost/Type",
                'type'           => 'summary',
                'approved_brand' => null,
                'awarded_brand'  => null,
                'approved_total' => Utilities::divide($approvedTotal, $units),
                'awarded_total'  => Utilities::divide($awardedTotal, $units),
            ));

            $stmt = $pdo->prepare("
                SELECT p.id, p.description, p.summary_description, COALESCE(cdp.value, 0) AS value FROM " . MasterCostDataParticularTable::getInstance()->getTableName() . " p
                JOIN " . CostDataTable::getInstance()->getTableName() . " cd on cd.master_cost_data_id = p.master_cost_data_id
                LEFT JOIN " . CostDataParticularTable::getInstance()->getTableName() . " cdp on cdp.master_cost_data_particular_id = p.id AND cdp.cost_data_id = cd.id
                WHERE cd.id = {$costData->id}
                AND p.is_prime_cost_rate_summary_displayed = TRUE
                AND p.deleted_at IS NULL
                ORDER BY p.priority ASC
                ");

            $stmt->execute();

            $particulars = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach($particulars as $particular)
            {
                array_push($records, array(
                    'id'             => 'particular-'.$particular['id'],
                    'description'    => empty($particular['summary_description']) ? "Cost/{$particular['description']}" : $particular['summary_description'],
                    'type'           => 'summary',
                    'approved_brand' => null,
                    'awarded_brand'  => null,
                    'approved_total' => Utilities::divide($approvedTotal, $particular['value']),
                    'awarded_total'  => Utilities::divide($awardedTotal, $particular['value']),
                ));
            }
        }

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $records
        ));
    }

    public function executeUpdateItem(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $costData = Doctrine_Core::getTable('CostData')->find($request->getParameter('cost_data_id')) and
            $masterItem = Doctrine_Core::getTable('MasterCostDataPrimeCostRate')->find($request->getParameter('id'))
        );

        $attribute = $request->getParameter('attr_name');
        $value = trim($request->getParameter('val'));

        if( $attribute == 'approved_value' || $attribute == 'awarded_value' || $attribute == 'units' ) $value = (float)$value;

        $success  = false;
        $errorMsg = null;
        $data     = array();

        try
        {
            $item = CostDataPrimeCostRateTable::setValue($costData, $masterItem, $attribute, $value);
            $data = array(
                'units'          => $item->units,
                'approved_value' => $item->approved_value,
                'approved_total' => $item->units * $item->approved_value,
                'approved_brand' => $item->approved_brand,
                'awarded_value'  => $item->awarded_value,
                'awarded_total'  => $item->units * $item->awarded_value,
                'awarded_brand'  => $item->awarded_brand,
            );

            if( $attribute == 'approved_value' || $attribute == 'awarded_value' ) $data["{$attribute}_derived"] = false;

            $success = true;
        }
        catch(Exception $e)
        {
            $errorMsg = $e->getMessage();
        }

        return $this->renderJson(array(
            'success'  => $success,
            'errorMsg' => $errorMsg,
            'data'     => $data,
        ));
    }

    public function executeGetAllPrimeCostRateRecords(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $costData = Doctrine_Core::getTable('CostData')->find($request->getParameter('costData'))
        );

        $parentId = $request->getParameter('parent_id');

        $query = DoctrineQuery::create()->select('i.id, i.description')
            ->from('MasterCostDataPrimeCostRate i')
            ->where('i.master_cost_data_id = ?', $costData->master_cost_data_id);

        if( $parentId == 0 )
        {
            $query->andWhere('i.parent_id IS NULL');
        }
        else
        {
            $query->andWhere('i.parent_id = ?', $parentId);
        }

        $records = $query->addOrderBy('i.priority ASC')
            ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
            ->execute();

        array_push($records, array(
            'id'          => Constants::GRID_LAST_ROW,
            'description' => "",
        ));

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $records
        ));
    }

    public function executeUpdateItemVisibility(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $costData = Doctrine_Core::getTable('CostData')->find($request->getParameter('costData'))
        );

        $selectedBillItemIds   = $request->getParameter('selectedIds') ?? array();
        $deselectedBillItemIds = $request->getParameter('deselectedIds') ?? array();

        $success = false;
        $errorMsg = null;

        try
        {
            CostDataPrimeCostRateTable::setItemVisibility($costData, $selectedBillItemIds, true);
            CostDataPrimeCostRateTable::setItemVisibility($costData, $deselectedBillItemIds, false);

            $success = true;
        }
        catch(Exception $e)
        {
            $errorMsg = $e->getMessage();
        }

        return $this->renderJson(array(
            'success'  => $success,
            'errorMsg' => $errorMsg,
            'ids'      => $selectedBillItemIds,
        ));
    }

    public function executeExportComparisonReport(sfWebRequest $request)
    {
        $this->forward404Unless(
            $costData = Doctrine_Core::getTable('CostData')->find($request->getParameter('costData'))
        );

        $reportGenerator = new sfCostDataPrimeCostRateComparisonExcelReportGenerator($costData);

        $reportGenerator->setParameters($request->getParameter('selected_ids') ?? array(), $request->getParameter('parent_id') ?? 0);

        $reportGenerator->setLevel($request->getParameter('level'));

        $reportGenerator->generateReport();

        return $this->sendExportExcelHeader($reportGenerator->fileInfo['filename'], $reportGenerator->savePath . DIRECTORY_SEPARATOR . $reportGenerator->fileInfo['filename'] . $reportGenerator->fileInfo['extension']);
    }

    public function executeGetLinkedItems(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $costData = Doctrine_Core::getTable('CostData')->find($request->getParameter('cost_data_id')) and
            $masterItem = Doctrine_Core::getTable('MasterCostDataPrimeCostRate')->find($request->getParameter('id'))
        );

        $billItems = BillItemCostDataPrimeCostRateTable::getLinkedBillItems($costData, [$masterItem->id])[$masterItem->id];

        $ids = array_column($billItems, $request->getParameter('id_type'));

        return $this->renderJson(array(
            'success'           => true,
            'ids'               => $ids,
        ));
    }

    public function executeLinkBillItems(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $costData = Doctrine_Core::getTable('CostData')->find($request->getParameter('cost_data_id')) and
            $masterItem = Doctrine_Core::getTable('MasterCostDataPrimeCostRate')->find($request->getParameter('id'))
        );

        $selectedBillItemIds   = empty($request->getParameter('selectedIds')) ? array() : explode(',', $request->getParameter('selectedIds'));
        $deselectedBillItemIds = empty($request->getParameter('deselectedIds')) ? array() : explode(',', $request->getParameter('deselectedIds'));

        $success  = false;
        $errorMsg = null;

        try
        {
            BillItemCostDataPrimeCostRateTable::sync($costData, $masterItem, $selectedBillItemIds, $deselectedBillItemIds);

            $success = true;
        }
        catch(Exception $e)
        {
            $errorMsg = $e->getMessage();
        }

        $billItems = BillItemCostDataPrimeCostRateTable::getLinkedBillItems($costData, [$masterItem->id])[$masterItem->id];

        $billItemIds = array_column($billItems, 'bill_item_id');

        return $this->renderJson(array(
            'success'           => $success,
            'errorMsg'          => $errorMsg,
            'bill_item_ids'     => $billItemIds,
        ));
    }
}
