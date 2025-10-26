<?php

/**
 * costData actions.
 *
 * @package    buildspace
 * @subpackage costData
 * @author     1337 developers
 * @version    SVN: $Id$
 */
class costDataActions extends BaseActions
{
    public function executeGetBreakdown(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $costData = Doctrine_Core::getTable('CostData')->find($request->getParameter('id'))
        );

        $items = CostDataItemTable::getItemList($costData, null);

        $masterItemIds = array_column($items, 'id');

        $values            = CostDataItemTable::getOverallCostingItemValues($costData, $masterItemIds);
        $itemDerivedStatus = CostDataItemTable::itemValuesAreDerived($costData, $masterItemIds);

        $itemSums            = CostDataItemTable::getItemSum($costData, $masterItemIds);
        $provisionalSumTotal = CostDataProvisionalSumItemTable::getTotalSum($costData);

        $totalApprovedSum = $itemSums['approved_sum'] + $provisionalSumTotal['approved_sum'];
        $totalAwardedSum  = $itemSums['awarded_sum'] + $provisionalSumTotal['awarded_sum'];
        $totalAdjustedSum = $itemSums['adjusted_sum'] + $provisionalSumTotal['adjusted_sum'];

        $nodelessItemRemarks = CostDataNodelessItemRemarkTable::getRemarks($costData->id);

        $form = new BaseForm();

        $provisionalSumLastUpdaterInfo = CostDataProvisionalSumItemTable::getLatestUpdateDetails($costData);
        $primeCostSumLastUpdaterInfo   = CostDataPrimeCostSumItemTable::getLatestUpdateDetails($costData);
        $primeCostRateLastUpdaterInfo  = CostDataPrimeCostRateTable::getLatestUpdateDetails($costData);

        $pdo = $costData->getTable()->getConnection()->getDbh();

        $stmt = $pdo->prepare("
            SELECT p.id FROM " . MasterCostDataParticularTable::getInstance()->getTableName() . " p
            JOIN " . CostDataTable::getInstance()->getTableName() . " cd on cd.master_cost_data_id = p.master_cost_data_id
            LEFT JOIN " . CostDataParticularTable::getInstance()->getTableName() . " cdp on cdp.master_cost_data_particular_id = p.id AND cdp.cost_data_id = cd.id
            WHERE cd.id = {$costData->id}
            AND p.is_used_for_cost_comparison = TRUE
            AND p.deleted_at IS NULL
            ORDER BY p.priority ASC
            ");

        $stmt->execute();

        $costComparisonParticulars = $stmt->fetchAll(PDO::FETCH_COLUMN);

        $records = array(
            array(
                'id'                           => 'provisional_sum',
                'description'                  => 'Provisional Sum',
                'type'                         => MasterCostData::ITEM_TYPE_PROVISIONAL_SUM,
                'approved_cost'                => $provisionalSumTotal['approved_sum'],
                'approved_cost_derived'        => true,
                'percentage_of_approved_sum'   => Utilities::percent($provisionalSumTotal['approved_sum'], $totalApprovedSum),
                'awarded_cost'                 => $provisionalSumTotal['awarded_sum'],
                'awarded_cost_derived'         => true,
                'percentage_of_awarded_sum'    => Utilities::percent($provisionalSumTotal['awarded_sum'], $totalAwardedSum),
                'variation_order_cost'         => $provisionalSumTotal['variation_order_sum'],
                'variation_order_cost_derived' => true,
                'adjusted_cost'                => $adjustedSum = ( $provisionalSumTotal['awarded_sum'] + $provisionalSumTotal['variation_order_sum'] ) ,
                'percentage_of_adjusted_sum'   => Utilities::percent($adjustedSum, $totalAdjustedSum),
                'remarks'                      => $nodelessItemRemarks[MasterCostData::ITEM_TYPE_PROVISIONAL_SUM],
                'updated_by'                   => $provisionalSumLastUpdaterInfo['updater_name'] ?? '-',
                'updated_at'                   => $provisionalSumLastUpdaterInfo['updated_at'] ? date('d/m/Y H:i', strtotime($provisionalSumLastUpdaterInfo['updated_at'])) : '-',
                '_csrf_token'                  => $form->getCSRFToken(),
            ),
            array(
                'id'          => 'prime_cost_sum',
                'description' => 'Prime Cost Sum',
                'type'        => MasterCostData::ITEM_TYPE_PRIME_COST_SUM,
                'remarks'     => $nodelessItemRemarks[MasterCostData::ITEM_TYPE_PRIME_COST_SUM],
                'updated_by'  => $primeCostSumLastUpdaterInfo['updater_name'] ?? '-',
                'updated_at'  => $primeCostSumLastUpdaterInfo['updated_at'] ? date('d/m/Y H:i', strtotime($primeCostSumLastUpdaterInfo['updated_at'])) : '-',
                '_csrf_token' => $form->getCSRFToken(),
            ),
            array(
                'id'          => 'prime_cost_rate',
                'description' => 'Project Rates Analysis',
                'type'        => MasterCostData::ITEM_TYPE_PRIME_COST_RATE,
                'remarks'     => $nodelessItemRemarks[MasterCostData::ITEM_TYPE_PRIME_COST_RATE],
                'updated_by'  => $primeCostRateLastUpdaterInfo['updater_name'] ?? '-',
                'updated_at'  => $primeCostRateLastUpdaterInfo['updated_at'] ? date('d/m/Y H:i', strtotime($primeCostRateLastUpdaterInfo['updated_at'])) : '-',
                '_csrf_token' => $form->getCSRFToken(),
            ),
            array(
                'id'          => 'row_separator',
                'description' => 'Standard Items',
                'type'        => null,
            ),
        );

        foreach($items as $key => $item)
        {
            $item['approved_cost']                = $values[ $item['id'] ]['approved_cost'];
            $item['approved_cost_derived']        = $itemDerivedStatus[ $item['id'] ]['approved_cost'];
            $item['percentage_of_approved_sum']   = Utilities::percent($item['approved_cost'], $totalApprovedSum);
            $item['awarded_cost']                 = $values[ $item['id'] ]['awarded_cost'];
            $item['awarded_cost_derived']         = $itemDerivedStatus[ $item['id'] ]['awarded_cost'];
            $item['percentage_of_awarded_sum']    = Utilities::percent($item['awarded_cost'], $totalAwardedSum);
            $item['variation_order_cost']         = $values[ $item['id'] ]['variation_order_cost'];
            $item['variation_order_cost_derived'] = $itemDerivedStatus[ $item['id'] ]['variation_order_cost'];
            $item['adjusted_cost']                = $values[ $item['id'] ]['adjusted_cost'];
            $item['percentage_of_adjusted_sum']   = Utilities::percent($item['adjusted_cost'], $totalAdjustedSum);
            $item['type']                         = MasterCostData::ITEM_TYPE_STANDARD;
            $item['remarks']                      = $values[ $item['id'] ]['remarks'];
            $item['updated_by']                   = $item['updater_name'] ?? '-';
            $item['updated_at']                   = $item['updated_at'] ? date('d/m/Y H:i', strtotime($item['updated_at'])) : '-';
            $item['_csrf_token']                  = $form->getCSRFToken();

            foreach($costComparisonParticulars as $particularId)
            {
                $item['approved-'.$particularId] = $values[$item['id']]['approved-'.$particularId];
                $item['awarded-'.$particularId]  = $values[$item['id']]['awarded-'.$particularId];
                $item['adjusted-'.$particularId] = $values[$item['id']]['adjusted-'.$particularId];
            }

            $records[] = $item;
        }

        array_push($records, array(
            'id'          => Constants::GRID_LAST_ROW,
            'description' => "",
            'type'        => MasterCostData::ITEM_TYPE_STANDARD,
            '_csrf_token' => $form->getCSRFToken()
        ));

        $totalRow = array(
            'id'                           => 'total',
            'description'                  => 'Total',
            'approved_cost'                => $totalApprovedSum,
            'approved_cost_derived'        => false,
            'percentage_of_approved_sum'   => $totalApprovedSum != 0 ? 100 : null,
            'awarded_cost'                 => $totalAwardedSum,
            'awarded_cost_derived'         => false,
            'percentage_of_awarded_sum'    => $totalAwardedSum != 0 ? 100 : null,
            'type'                         => 'summary',
            'variation_order_cost'         => array_sum(array_column($records, 'variation_order_cost')),
            'variation_order_cost_derived' => false,
            'adjusted_cost'                => $totalAdjustedSum,
            'percentage_of_adjusted_sum'   => $totalAdjustedSum != 0 ? 100 : null,
        );

        foreach($costComparisonParticulars as $particularId)
        {
            $totalRow['approved-'.$particularId] = array_sum(array_column($records, "approved-{$particularId}"));
            $totalRow['awarded-'.$particularId]  = array_sum(array_column($records, "awarded-{$particularId}"));
            $totalRow['adjusted-'.$particularId] = array_sum(array_column($records, "adjusted-{$particularId}"));
        }

        array_push($records, $totalRow);

        $overallProjectCostingParticularSummary = CostDataParticularTable::getOverallProjectCostingSummary($costData);

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
            array_push($records, array(
                'id'                           => 'particular-'.$particular['id'],
                'description'                  => empty($particular['summary_description']) ? "Total Cost/{$particular['description']}" : $particular['summary_description'],
                'approved_cost'                => Utilities::divide($overallProjectCostingParticularSummary[$particular['id']]['approved_cost'], $particular['value']),
                'approved_cost_derived'        => false,
                'percentage_of_approved_sum'   => null,
                'awarded_cost'                 => Utilities::divide($overallProjectCostingParticularSummary[$particular['id']]['awarded_cost'], $particular['value']),
                'awarded_cost_derived'         => false,
                'percentage_of_awarded_sum'    => null,
                'type'                         => 'summary',
                'variation_order_cost'         => Utilities::divide($overallProjectCostingParticularSummary[$particular['id']]['variation_order_cost'], $particular['value']),
                'variation_order_cost_derived' => false,
                'adjusted_cost'                => Utilities::divide($overallProjectCostingParticularSummary[$particular['id']]['adjusted_cost'], $particular['value']),
                'percentage_of_adjusted_sum'   => null,
            ));
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
            $costData = Doctrine_Core::getTable('CostData')->find($request->getParameter('costData')) and
            $masterProjectCostingItem = Doctrine_Core::getTable('MasterCostDataItem')->find($request->getParameter('id'))
        );

        $pdo = CostDataTable::getInstance()->getConnection()->getDbh();

        $items = CostDataItemTable::getItemList($costData, $masterProjectCostingItem->id);

        $itemIds           = array_column($items, 'id');
        $values            = CostDataItemTable::getWorkCategoryValues($costData, $itemIds);
        $itemDerivedStatus = CostDataItemTable::itemValuesAreDerived($costData, $itemIds);
        $itemSums          = CostDataItemTable::getItemSum($costData, $itemIds);

        $form = new BaseForm();

        $records = array();

        foreach($items as $item)
        {
            $item = array_merge($item, $values[$item['id']]);

            $item['_csrf_token']                  = $form->getCSRFToken();
            $item['approved_cost_derived']        = $itemDerivedStatus[ $item['id'] ]['approved_cost'];
            $item['awarded_cost_derived']         = $itemDerivedStatus[ $item['id'] ]['awarded_cost'];
            $item['variation_order_cost_derived'] = $itemDerivedStatus[ $item['id'] ]['variation_order_cost'];

            $item['approved_percentage'] = Utilities::percent($values[$item['id']]['approved_cost'], $itemSums['approved_sum']);
            $item['awarded_percentage']  = Utilities::percent($values[$item['id']]['awarded_cost'], $itemSums['awarded_sum']);
            $item['adjusted_percentage'] = Utilities::percent($values[$item['id']]['adjusted_cost'], $itemSums['adjusted_sum']);

            $records[] = $item;
        }

        array_push($records, array(
            'id'                        => Constants::GRID_LAST_ROW,
            'description'               => "",
            'uom_symbol'                => null,
            'remarks'                   => "",
            '_csrf_token'               => $form->getCSRFToken()
        ));

        $stmt = $pdo->prepare("
            SELECT p.id, COALESCE(cdp.value, 0) AS value FROM " . MasterCostDataParticularTable::getInstance()->getTableName() . " p
            JOIN " . CostDataTable::getInstance()->getTableName() . " cd on cd.master_cost_data_id = p.master_cost_data_id
            LEFT JOIN " . CostDataParticularTable::getInstance()->getTableName() . " cdp on cdp.master_cost_data_particular_id = p.id AND cdp.cost_data_id = cd.id
            JOIN " . MasterCostDataItemParticularTable::getInstance()->getTableName() . " ip on ip.master_cost_data_particular_id = p.id
            WHERE cd.id = {$costData->id}
            AND ip.master_cost_data_item_id = {$masterProjectCostingItem->id}
            AND p.deleted_at IS NULL
            ORDER BY p.priority ASC
            ");

        $stmt->execute();

        $particulars = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

        $row = array(
            'id'                                 => 'total',
            'description'                        => "Total",
            'uom_symbol'                         => null,
            'approved_cost'                      => $approvedCost = array_sum(array_column($records, 'approved_cost')),
            'approved_cost_derived'              => false,
            'approved_percentage'                => $approvedCost != 0 ? 100 : null,
            'awarded_cost'                       => $awardedCost = array_sum(array_column($records, 'awarded_cost')),
            'awarded_cost_derived'               => false,
            'awarded_percentage'                 => $awardedCost != 0 ? 100 : null,
            'adjusted_cost'                      => $adjustedCost = array_sum(array_column($records, 'adjusted_cost')),
            'adjusted_cost_derived'              => false,
            'adjusted_percentage'                => $adjustedCost != 0 ? 100 : null,
            'variation_order_cost'               => array_sum(array_column($records, 'variation_order_cost')),
            'variation_order_cost_derived'       => false,
            'type'                               => 'summary',
            'remarks'                            => '',
        );

        foreach($particulars as $particularId => $particularValue)
        {
            $row['approved_column-'.$particularId] = array_sum(array_column($records, 'approved_column-'.$particularId));
            $row['awarded_column-'.$particularId]  = array_sum(array_column($records, 'awarded_column-'.$particularId));
            $row['adjusted_column-'.$particularId] = array_sum(array_column($records, 'adjusted_column-'.$particularId));
        }

        array_push($records, $row);

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $records
        ));
    }

    public function executeGetElementList(sfWebRequest $request)
    {
        $form = new BaseForm();

        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $costData = Doctrine_Core::getTable('CostData')->find($request->getParameter('costData')) and
            $workCategoryItem = Doctrine_Core::getTable('MasterCostDataItem')->find($request->getParameter('id'))
        );

        $pdo = CostDataTable::getInstance()->getConnection()->getDbh();

        $records = CostDataItemTable::getItemList($costData, $workCategoryItem->id);

        $masterItemIds     = array_column($records, 'id');
        $values            = CostDataItemTable::getElementValues($costData, $workCategoryItem, $masterItemIds);
        $itemDerivedStatus = CostDataItemTable::itemValuesAreDerived($costData, $masterItemIds);

        foreach($records as $key => $record)
        {
            $records[ $key ] = array_merge($record, $values[$record['id']]);

            $records[ $key ]['approved_cost_derived']        = $itemDerivedStatus[ $record['id'] ]['approved_cost'];
            $records[ $key ]['awarded_cost_derived']         = $itemDerivedStatus[ $record['id'] ]['awarded_cost'];
            $records[ $key ]['adjusted_cost_derived']        = false;
            $records[ $key ]['variation_order_cost_derived'] = $itemDerivedStatus[ $record['id'] ]['variation_order_cost'];
            $records[ $key ]['_csrf_token']                  = $form->getCSRFToken();
        }

        array_push($records, array(
            'id'          => Constants::GRID_LAST_ROW,
            'description' => "",
            'remarks'     => "",
            '_csrf_token' => $form->getCSRFToken()
        ));

        $stmt = $pdo->prepare("
            SELECT p.id, COALESCE(cdp.value, 0) AS value FROM " . MasterCostDataParticularTable::getInstance()->getTableName() . " p
            JOIN " . CostDataTable::getInstance()->getTableName() . " cd on cd.master_cost_data_id = p.master_cost_data_id
            LEFT JOIN " . CostDataParticularTable::getInstance()->getTableName() . " cdp on cdp.master_cost_data_particular_id = p.id AND cdp.cost_data_id = cd.id
            WHERE cd.id = {$costData->id}
            AND p.is_used_for_cost_comparison = TRUE
            AND p.deleted_at IS NULL
            ORDER BY p.priority ASC
            ");

        $stmt->execute();

        $particulars = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

        $row = array(
            'id'                           => 'total',
            'description'                  => "Total",
            'uom_symbol'                   => null,
            'approved_cost'                => $approvedCost = array_sum(array_column($records, 'approved_cost')),
            'approved_cost_derived'        => false,
            'approved_percentage'          => $approvedCost != 0 ? 100 : null,
            'awarded_cost'                 => $awardedCost = array_sum(array_column($records, 'awarded_cost')),
            'awarded_cost_derived'         => false,
            'awarded_percentage'           => $awardedCost != 0 ? 100 : null,
            'adjusted_cost'                => $adjustedCost = array_sum(array_column($records, 'adjusted_cost')),
            'adjusted_cost_derived'        => false,
            'adjusted_percentage'          => $adjustedCost != 0 ? 100 : null,
            'variation_order_cost'         => array_sum(array_column($records, 'variation_order_cost')),
            'variation_order_cost_derived' => false,
            'type'                         => 'summary',
            'remarks'                      => '',
        );

        foreach($particulars as $particularId => $particularValue)
        {
            $row['approved-'.$particularId] = array_sum(array_column($records, 'approved-'.$particularId));
            $row['awarded-'.$particularId] = array_sum(array_column($records, 'awarded-'.$particularId));
            $row['adjusted-'.$particularId] = array_sum(array_column($records, 'adjusted-'.$particularId));
        }

        array_push($records, $row);

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
            $masterItem = Doctrine_Core::getTable('MasterCostDataItem')->find($request->getParameter('id'))
        );

        $attribute = $request->getParameter('attr_name');

        $value = $request->getParameter('val');

        if(CostDataItemTable::isCascadingAttribute($attribute))
        {
            $value = (float)trim($value);
        }

        $success  = false;
        $errorMsg = null;
        $data     = array();

        try
        {
            $item    = CostDataItemTable::setValue($costData, $masterItem, $attribute, $value);
            $data    = array( $attribute => $item->{$attribute} );

            if(CostDataItemTable::isCascadingAttribute($attribute))
            {
                $data["{$attribute}_derived"] = false;
                $data["adjusted_cost"]        = $item->awarded_cost + $item->variation_order_cost;
            }

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

    public function executeUpdateNodelessItemRemarks(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $costData = Doctrine_Core::getTable('CostData')->find($request->getParameter('cost_data_id'))
        );

        $type = $request->getParameter('type');

        $value = $request->getParameter('val');

        $success  = false;
        $errorMsg = null;
        $data     = array();

        try
        {
            $userId = sfContext::getInstance()->getUser()->getAttribute('user_id', null, 'sfGuardSecurityUser');

            $item = CostDataNodelessItemRemarkTable::updateRemarks($costData->id, $type, $value, $userId);
            $data = array( 'remarks' => CostDataNodelessItemRemarkTable::getRemarks($costData->id)[$type] );

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

    public function executeGetLinkedItems(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $costData = Doctrine_Core::getTable('CostData')->find($request->getParameter('cost_data_id')) and
            $masterItem = Doctrine_Core::getTable('MasterCostDataItem')->find($request->getParameter('id'))
        );

        $billItems = BillItemCostDataItemTable::getLinkedBillItems($costData, $masterItem);

        $ids = array_column($billItems, $request->getParameter('id_type'));

        $item = CostDataItemTable::getItem($costData, $masterItem);

        return $this->renderJson(array(
            'success'           => true,
            'ids'               => $ids,
            'conversion_factor' => $item->conversion_factor,
        ));
    }

    public function executeGetLinkBillItemProjectsList(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $costData = Doctrine_Core::getTable('CostData')->find($request->getParameter('costData'))
        );

        $projects = $costData->getProjects(array(ProjectMainInformation::STATUS_POSTCONTRACT, ProjectMainInformation::STATUS_TENDERING));

        foreach($projects as $key => $project)
        {
            $projects[$key]['description'] = $project['title'];
            $projects[$key]['status']      = ProjectMainInformation::getProjectStatusById($project['status']);
        }

        array_push($projects, array(
            'id'          => Constants::GRID_LAST_ROW,
            'description' => null,
            'status'      => null,
        ));

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $projects
        ));
    }

    public function executeGetLinkBillItemBillList(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $costData = Doctrine_Core::getTable('CostData')->find($request->getParameter('costData')) and
            $project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('id'))
        );

        $pdo = $costData->getTable()->getConnection()->getDbh();

        $stmt = $pdo->prepare("SELECT p.id, p.title as description
        FROM " . ProjectStructureTable::getInstance()->getTableName() . " p
        WHERE p.root_id = {$project->id}
        AND p.root_id != p.id
        AND p.deleted_at IS NULL
        ORDER BY p.lft ASC");

        $stmt->execute();

        $bills = $stmt->fetchAll(PDO::FETCH_ASSOC);

        array_push($bills, array(
            'id'          => Constants::GRID_LAST_ROW,
            'description' => null,
        ));

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $bills
        ));
    }

    public function executeGetLinkBillItemElementList(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $costData = Doctrine_Core::getTable('CostData')->find($request->getParameter('costData')) and
            $bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('id'))
        );

        $pdo = $costData->getTable()->getConnection()->getDbh();

        $stmt = $pdo->prepare("SELECT e.id, e.description
        FROM " . BillElementTable::getInstance()->getTableName() . " e
        WHERE e.project_structure_id = {$bill->id}
        AND e.deleted_at IS NULL
        ORDER BY e.priority ASC");

        $stmt->execute();

        $elements = $stmt->fetchAll(PDO::FETCH_ASSOC);

        array_push($elements, array(
            'id' => Constants::GRID_LAST_ROW,
            'linked'      => false,
            'description' => null,
        ));

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $elements
        ));
    }

    public function executeGetBillItemList(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $costData = Doctrine_Core::getTable('CostData')->find($request->getParameter('costData')) and
            $element = Doctrine_Core::getTable('BillElement')->find($request->getParameter('id'))
        );

        $pdo = $costData->getTable()->getConnection()->getDbh();

        if($element->ProjectStructure->getRoot()->MainInformation->status == ProjectMainInformation::STATUS_POSTCONTRACT)
        {
            $stmt = $pdo->prepare("SELECT p.id, p.project_structure_id, p.selected_type_rate FROM " . PostContractTable::getInstance()->getTableName() . " p
                WHERE p.id = " . $element->ProjectStructure->getRoot()->PostContract->id);

            $stmt->execute();

            $postContract = $stmt->fetch(PDO::FETCH_ASSOC);

            if ( $postContract['selected_type_rate'] == PostContract::RATE_TYPE_CONTRACTOR )
            {
                $stmt = $pdo->prepare("SELECT tc.id, tc.company_id FROM " . TenderSettingTable::getInstance()->getTableName() . " ts
                    JOIN " . TenderCompanyTable::getInstance()->getTableName() . " tc ON tc.company_id = ts.awarded_company_id AND tc.project_structure_id = ts.project_structure_id
                    WHERE ts.project_structure_id = " . $postContract['project_structure_id'] . " AND ts.deleted_at IS NULL");

                $stmt->execute();

                $tenderCompany = $stmt->fetch(PDO::FETCH_ASSOC);

                $sqlNOtListedField = ', (CASE i.type WHEN ' . BillItem::TYPE_ITEM_NOT_LISTED . '
                        THEN tnl.description
                        ELSE i.description
                        END
                    ) AS description,
                    (CASE i.type WHEN ' . BillItem::TYPE_ITEM_NOT_LISTED . '
                        THEN uom_not_listed.id
                        ELSE uom.id
                        END
                    ) AS uom_id,
                    (CASE i.type WHEN ' . BillItem::TYPE_ITEM_NOT_LISTED . '
                        THEN uom_not_listed.symbol
                        ELSE uom.symbol
                        END
                    ) AS uom_symbol';

                $sqlNOtListed = "LEFT JOIN " . TenderBillItemNotListedTable::getInstance()->getTableName() . " tnl ON tnl.bill_item_id = i.id AND tnl.tender_company_id = " . $tenderCompany['id'] . "
                                LEFT JOIN " . UnitOfMeasurementTable::getInstance()->getTableName() . " uom_not_listed ON tnl.uom_id = uom_not_listed.id AND uom_not_listed.deleted_at IS NULL";
            }
            else if ( $postContract['selected_type_rate'] == PostContract::RATE_TYPE_RATIONALIZED )
            {
                $sqlNOtListedField = ', (CASE i.type WHEN ' . BillItem::TYPE_ITEM_NOT_LISTED . '
                        THEN tnl.description
                        ELSE i.description
                        END
                    ) AS description,
                    (CASE i.type WHEN ' . BillItem::TYPE_ITEM_NOT_LISTED . '
                        THEN uom_not_listed.id
                        ELSE uom.id
                        END
                    ) AS uom_id,
                    (CASE i.type WHEN ' . BillItem::TYPE_ITEM_NOT_LISTED . '
                        THEN uom_not_listed.symbol
                        ELSE uom.symbol
                        END
                    ) AS uom_symbol';

                $sqlNOtListed = "LEFT JOIN " . TenderBillItemNotListedRationalizedTable::getInstance()->getTableName() . " tnl ON tnl.bill_item_id = i.id
                                LEFT JOIN " . UnitOfMeasurementTable::getInstance()->getTableName() . " uom_not_listed ON uom_not_listed.id = tnl.uom_id";
            }
            else
            {
                $sqlNOtListedField = ', i.description, uom.id AS uom_id, uom.symbol AS uom_symbol';
                $sqlNOtListed      = '';
            }

            $stmt = $pdo->prepare("SELECT i.id, i.level, ROUND(COALESCE(qty.total_quantity,0), 2) as grand_total_quantity, ROUND(COALESCE(r.rate,0), 2) AS rate, ROUND(COALESCE(r.grand_total, 0),2) as amount
                {$sqlNOtListedField}
                FROM " . BillItemTable::getInstance()->getTableName() . " i
                LEFT JOIN " . PostContractBillItemRateTable::getInstance()->getTableName() . " r ON i.id = r.bill_item_id
                LEFT JOIN " . UnitOfMeasurementTable::getInstance()->getTableName() . " uom ON uom.id = i.uom_id
                JOIN " . BillElementTable::getInstance()->getTableName() . " e ON e.id = i.element_id
                JOIN " . ProjectStructureTable::getInstance()->getTableName() . " b ON b.id = e.project_structure_id
                JOIN " . ProjectStructureTable::getInstance()->getTableName() . " p ON p.id = b.root_id
                LEFT JOIN (
                    SELECT i.id, ROUND(SUM(bit.total_quantity), 2) AS total_quantity
                    FROM bs_bill_items i
                    JOIN " . PostContractBillItemTypeTable::getInstance()->getTableName() . " bit ON bit.bill_item_id = i.id
                    WHERE i.element_id = {$element->id}
                    GROUP BY i.id
                    ) qty ON qty.id = i.id
                {$sqlNOtListed}
                WHERE e.id = {$element->id}
                AND i.deleted_at IS NULL
                AND i.project_revision_deleted_at IS NULL
                ORDER BY i.priority, i.lft, i.level");

            $stmt->execute();

            $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        else
        {
            $stmt = $pdo->prepare("SELECT i.id, i.level, ROUND(COALESCE(qty.total_quantity,0), 2) as grand_total_quantity, ROUND(COALESCE(r.rate,0), 2) AS rate, ROUND(COALESCE(r.grand_total, 0),2) as amount, i.description, uom.id AS uom_id, uom.symbol AS uom_symbol
                FROM " . BillItemTable::getInstance()->getTableName() . " i
                LEFT JOIN " . PostContractBillItemRateTable::getInstance()->getTableName() . " r ON i.id = r.bill_item_id
                LEFT JOIN " . UnitOfMeasurementTable::getInstance()->getTableName() . " uom ON uom.id = i.uom_id
                JOIN " . BillElementTable::getInstance()->getTableName() . " e ON e.id = i.element_id
                JOIN " . ProjectStructureTable::getInstance()->getTableName() . " b ON b.id = e.project_structure_id
                JOIN " . ProjectStructureTable::getInstance()->getTableName() . " p ON p.id = b.root_id
                JOIN " . ProjectRevisionTable::getInstance()->getTableName() . " rev ON rev.id = i.project_revision_id
                LEFT JOIN (
                    SELECT i.id, ROUND(SUM(bit.total_quantity), 2) AS total_quantity
                    FROM bs_bill_items i
                    JOIN " . PostContractBillItemTypeTable::getInstance()->getTableName() . " bit ON bit.bill_item_id = i.id
                    WHERE i.element_id = {$element->id}
                    GROUP BY i.id
                    ) qty ON qty.id = i.id
                WHERE e.id = {$element->id}
                AND rev.locked_status = TRUE
                AND i.deleted_at IS NULL
                AND i.project_revision_deleted_at IS NULL
                ORDER BY i.priority, i.lft, i.level");

            $stmt->execute();

            $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        array_push($items, array(
            'id'                   => Constants::GRID_LAST_ROW,
            'description'          => null,
            'uom_id'               => -1,
            'uom_symbol'           => null,
            'amount'               => 0,
            'rate'                 => 0,
            'grand_total_quantity' => 0,
        ));

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $items
        ));
    }

    public function executeLinkBillItems(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $costData = Doctrine_Core::getTable('CostData')->find($request->getParameter('cost_data_id')) and
            $masterCostDataItem = Doctrine_Core::getTable('MasterCostDataItem')->find($request->getParameter('id'))
        );

        $selectedBillItemIds   = empty($request->getParameter('selectedIds')) ? array() : explode(',', $request->getParameter('selectedIds'));
        $deselectedBillItemIds = empty($request->getParameter('deselectedIds')) ? array() : explode(',', $request->getParameter('deselectedIds'));

        $conversionFactor      = trim($request->getParameter('conversion_factor'));

        $item = CostDataItemTable::getItem($costData, $masterCostDataItem);

        $success  = false;
        $errorMsg = null;

        try
        {
            if( is_numeric($conversionFactor) )
            {
                $item->conversion_factor = $conversionFactor;
                $item->save();
            }

            BillItemCostDataItemTable::sync($costData, $masterCostDataItem, $selectedBillItemIds, $deselectedBillItemIds);

            $success = true;
        }
        catch(Exception $e)
        {
            $errorMsg = $e->getMessage();
        }

        $billItems = BillItemCostDataItemTable::getLinkedBillItems($costData, $masterCostDataItem);

        $billItemIds = array_column($billItems, 'bill_item_id');

        return $this->renderJson(array(
            'success'           => $success,
            'errorMsg'          => $errorMsg,
            'bill_item_ids'     => $billItemIds,
            'conversion_factor' => $item->conversion_factor,
        ));
    }

    public function executeGetProjectParticularList(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $costData = Doctrine_Core::getTable('CostData')->find($request->getParameter('costData'))
        );

        $form = new BaseForm();

        $pdo = $costData->getTable()->getConnection()->getDbh();

        $stmt = $pdo->prepare("SELECT mp.id, mp.description, mp.uom_id, mp.summary_description, uom.symbol as uom_symbol, COALESCE(p.value, 0) as value
        FROM " . MasterCostDataParticularTable::getInstance()->getTableName() . " mp
        JOIN " . MasterCostDataTable::getInstance()->getTableName() . " mcd on mcd.id = mp.master_cost_data_id
        JOIN " . CostDataTable::getInstance()->getTableName() . " cd on mcd.id = cd.master_cost_data_id
        LEFT JOIN " . UnitOfMeasurementTable::getInstance()->getTableName() . " uom ON uom.id = mp.uom_id
        LEFT JOIN " . CostDataParticularTable::getInstance()->getTableName() . " p ON p.master_cost_data_particular_id = mp.id AND p.cost_data_id = :costDataId
        WHERE cd.id = :costDataId
        AND mp.deleted_at IS NULL ORDER BY mp.priority ASC");

        $stmt->execute(array( 'costDataId' => $costData->id ));

        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach($items as $key => $item)
        {
            $items[ $key ]['_csrf_token'] = $form->getCSRFToken();
        }

        array_push($items, array(
            'id'                  => Constants::GRID_LAST_ROW,
            'description'         => "",
            'summary_description' => "",
            'uom_id'              => -1,
            'uom_symbol'          => '',
            '_csrf_token'         => $form->getCSRFToken()
        ));

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $items
        ));
    }

    public function executeUpdateProjectParticularValue(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $costData = Doctrine_Core::getTable('CostData')->find($request->getParameter('cost_data_id')) and
            $masterCostDataParticular = Doctrine_Core::getTable('MasterCostDataParticular')->find($request->getParameter('id'))
        );

        $value = (float)trim($request->getParameter('val'));

        $success  = false;
        $errorMsg = null;

        try
        {
            $costDataParticular = CostDataParticularTable::setValue($costData, $masterCostDataParticular, $value);
            $success            = true;
        }
        catch(Exception $exception)
        {
            $errorMsg = $exception->getMessage();
        }

        $data = array(
            'value' => $costDataParticular->value ?? 0,
        );

        return $this->renderJson(array(
            'success'  => $success,
            'errorMsg' => $errorMsg,
            'data'     => $data
        ));
    }

    public function executeGetAllCostDataItemRecords(sfWebRequest $request)
    {
        $form = new BaseForm();

        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $costData = Doctrine_Core::getTable('CostData')->find($request->getParameter('costData'))
        );

        if( $parentItem = Doctrine_Core::getTable('MasterCostDataItem')->find($request->getParameter('parent_id')) )
        {
            $records = DoctrineQuery::create()->select('i.id, i.description')
                ->from('MasterCostDataItem i')
                ->where('i.master_cost_data_id = ?', $costData->master_cost_data_id)
                ->andWhere('i.parent_id = ?', $parentItem->id)
                ->addOrderBy('i.priority ASC')
                ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
                ->execute();
        }
        else
        {
            $records = DoctrineQuery::create()->select('i.id, i.description')
                ->from('MasterCostDataItem i')
                ->where('i.master_cost_data_id = ?', $costData->master_cost_data_id)
                ->andWhere('i.parent_id IS NULL')
                ->addOrderBy('i.priority ASC')
                ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
                ->execute();
        }

        array_push($records, array(
            'id'          => Constants::GRID_LAST_ROW,
            'description' => "",
            '_csrf_token' => $form->getCSRFToken()
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
            CostDataItemTable::setItemVisibility($costData, $selectedBillItemIds, true);
            CostDataItemTable::setItemVisibility($costData, $deselectedBillItemIds, false);

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

    public function executeGetCostDataList(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $costData = Doctrine_Core::getTable('CostData')->find($request->getParameter('costData'))
        );

        $pdo = CostDataTable::getInstance()->getConnection()->getDbh();

        $stmt = $pdo->prepare("SELECT cd.id, cd.name, cd.awarded_date, cd.approved_date, cd.adjusted_date, cdt.name as type, EXTRACT(YEAR FROM cd.tender_date) as tender_year, EXTRACT(YEAR FROM cd.award_date) as award_year, r.country, sr.name as state
        FROM ".CostDataTable::getInstance()->getTableName()." cd
        JOIN ".CostDataTypeTable::getInstance()->getTableName()." cdt on cdt.id = cd.cost_data_type_id
        JOIN ".RegionsTable::getInstance()->getTableName()." r on r.id = cd.region_id
        JOIN ".SubregionsTable::getInstance()->getTableName()." sr on sr.id = cd.subregion_id
        WHERE cd.master_cost_data_id = {$costData->master_cost_data_id}
        AND cd.id != {$costData->id}
        AND cd.deleted_at IS NULL
        ORDER BY cd.created_at ASC");

        $stmt->execute();

        $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach($records as $key => $record)
        {
            $records[$key]['approved_date'] = $record['approved_date'] ? date('d-m-Y', strtotime($record['approved_date'])) : null;
            $records[$key]['awarded_date']  = $record['awarded_date'] ? date('d-m-Y', strtotime($record['awarded_date'])) : null;
            $records[$key]['adjusted_date'] = $record['adjusted_date'] ? date('d-m-Y', strtotime($record['adjusted_date'])) : null;
        }

        array_push($records, array(
            'id'          => Constants::GRID_LAST_ROW,
            'name'        => "",
            'type'        => "",
            'tender_year' => "",
            'award_year'  => "",
            'country'     => "",
            'state'       => "",
        ));

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $records
        ));
    }

    public function executeGetCostDataInformationForm(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $costData = Doctrine_Core::getTable('CostData')->find($request->getParameter('cost_data_id'))
        );

        $form = new CostDataForm();

        return $this->renderJson(array(
            'formValues' => array(
                'cost_data[awarded_date]' => $costData->awarded_date ? date('Y-m-d', strtotime($costData->awarded_date)) : null,
                'cost_data[approved_date]' => $costData->approved_date ? date('Y-m-d', strtotime($costData->approved_date)) : null,
                'cost_data[adjusted_date]' => $costData->adjusted_date ? date('Y-m-d', strtotime($costData->adjusted_date)) : null,
                'cost_data[_csrf_token]' => $form->getCSRFToken(),
            )
        ));
    }

    public function executeUpdateCostDataInformationForm(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $costData = Doctrine_Core::getTable('CostData')->find($request->getParameter('cost_data_id'))
        );

        $form = new CostDataForm($costData);

        if ( $this->isFormValid($request, $form) )
        {
            $costData = $form->save();

            $errors  = null;
            $success = true;

            $values  = array(
                'cost_data[awarded_date]' => $costData->awarded_date ? date('d-m-Y', strtotime($costData->awarded_date)) : null,
                'cost_data[approved_date]' => $costData->approved_date ? date('d-m-Y', strtotime($costData->approved_date)) : null,
                'cost_data[adjusted_date]' => $costData->adjusted_date ? date('d-m-Y', strtotime($costData->adjusted_date)) : null,
            );

            $formValues  = array(
                'cost_data[awarded_date]' => $costData->awarded_date ? date('Y-m-d', strtotime($costData->awarded_date)) : null,
                'cost_data[approved_date]' => $costData->approved_date ? date('Y-m-d', strtotime($costData->approved_date)) : null,
                'cost_data[adjusted_date]' => $costData->adjusted_date ? date('Y-m-d', strtotime($costData->adjusted_date)) : null,
                'cost_data[_csrf_token]' => $form->getCSRFToken(),
            );
        }
        else
        {
            $errors  = $form->getErrors();
            $success = false;
            $values  = array();
            $formValues  = array();
        }

        return $this->renderJson(array( 'success' => $success, 'errors' => $errors, 'values' => $values, 'formValues' => $formValues ));
    }

    public function executeGetWorkCategoryParticulars(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $costData = Doctrine_Core::getTable('CostData')->find($request->getParameter('cost_data_id')) and
            $item = Doctrine_Core::getTable('MasterCostDataItem')->find($request->getParameter('id'))
        );

        $success  = false;
        $errorMsg = null;
        $data     = array();

        try
        {
            $pdo = CostDataTable::getInstance()->getConnection()->getDbh();

            $stmt = $pdo->prepare("
                SELECT p.id, p.description
                FROM " . MasterCostDataParticularTable::getInstance()->getTableName() . " p
                JOIN " . MasterCostDataItemParticularTable::getInstance()->getTableName() . " ip on ip.master_cost_data_particular_id = p.id
                WHERE ip.master_cost_data_item_id = {$item->id}
                AND p.deleted_at IS NULL
                ORDER BY p.priority ASC
                ");

            $stmt->execute();

            $records = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

            foreach($records as $id => $description)
            {
                $data[] = [
                    'id'          => $id,
                    'description' => $description ?? "",
                ];
            }

            $success = true;
        }
        catch(Exception $exception)
        {
            $errorMsg = $exception->getMessage();
        }

        return $this->renderJson(array(
            'success'  => $success,
            'data'     => $data,
        ));
    }

    public function executeGetCostComparisonProjectParticulars(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $costData = Doctrine_Core::getTable('CostData')->find($request->getParameter('cost_data_id'))
        );

        $success  = false;
        $errorMsg = null;
        $data     = [];

        try
        {
            $pdo = CostDataTable::getInstance()->getConnection()->getDbh();

            $stmt = $pdo->prepare("
                SELECT p.id, p.description, COALESCE(uom.symbol, '') as uom_symbol
                FROM " . MasterCostDataParticularTable::getInstance()->getTableName() . " p
                JOIN " . CostDataTable::getInstance()->getTableName() . " cd on cd.master_cost_data_id = p.master_cost_data_id
                LEFT JOIN " . CostDataParticularTable::getInstance()->getTableName() . " cdp on cdp.master_cost_data_particular_id = p.id AND cdp.cost_data_id = cd.id
                LEFT JOIN " . UnitOfMeasurementTable::getInstance()->getTableName() . " uom ON uom.id = p.uom_id
                WHERE cd.id = {$costData->id}
                AND p.is_used_for_cost_comparison = TRUE
                AND p.deleted_at IS NULL
                ORDER BY p.priority ASC
                ");

            $stmt->execute();

            $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach($records as $record)
            {
                $data[] = ['id' => $record['id'], 'description' => $record['description'], 'uom_symbol' => $record['uom_symbol']];
            }

            $success = true;
        }
        catch(Exception $exception)
        {
            $errorMsg = $exception->getMessage();
        }

        return $this->renderJson(array(
            'success'  => $success,
            'errorMsg' => $errorMsg,
            'data'     => $data,
        ));
    }
}