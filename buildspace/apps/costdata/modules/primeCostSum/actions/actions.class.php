<?php

/**
 * primeCostSum actions.
 *
 * @package    buildspace
 * @subpackage primeCostSum
 * @author     1337 developers
 * @version    SVN: $Id$
 */
class primeCostSumActions extends sfActions
{
    public function executeGetPrimeCostSumBreakdown(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $costData = Doctrine_Core::getTable('CostData')->find($request->getParameter('costData'))
        );

        $items = CostDataPrimeCostSumItemTable::getItemList($costData);

        $itemIds           = array_column($items, 'id');
        $values            = CostDataPrimeCostSumItemTable::getValues($costData, $itemIds);
        $itemDerivedStatus = CostDataPrimeCostSumItemTable::itemValuesAreDerived($costData, $itemIds);

        $columns = CostDataPrimeCostSumColumnDefinitionTable::getPrimeCostSumColumns($costData);

        $columnValues        = CostDataPrimeCostSumColumnTable::getValues($costData, array_column($items, 'id'));
        $columnDerivedStatus = CostDataPrimeCostSumColumnTable::itemValuesAreDerived($costData, $itemIds);

        $form = new BaseForm();

        $records = array();

        foreach($items as $key => $item)
        {
            $item['approved_cost']                         = $values[ $item['id'] ]['approved_cost'];
            $item['awarded_cost']                          = $values[ $item['id'] ]['awarded_cost'];
            $item['awarded_cost_derived']                  = $itemDerivedStatus[ $item['id'] ]['awarded_cost'];
            $item['awarded_nominated_sub_contractor']      = $values[ $item['id'] ]['awarded_nominated_sub_contractor'];
            $item['awarded_date']                          = $values[ $item['id'] ]['awarded_date'];
            $item['nominated_sub_contractor_total_amount'] = 0;
            $item['_csrf_token']                           = $form->getCSRFToken();

            foreach($columns as $column)
            {
                $columnAmount = $columnValues[ $item['id'] ]["amount_{$column['id']}"];

                $item["additional_column-{$column['id']}"]         = $columnAmount;
                $item["additional_column-{$column['id']}-derived"] = $columnDerivedStatus[ $item['id'] ][ $column['id'] ]['awarded_cost'];

                $item['nominated_sub_contractor_total_amount'] += $columnAmount;
            }

            $records[] = $item;
        }

        array_push($records, array(
            'id'                               => Constants::GRID_LAST_ROW,
            'description'                      => "",
            'approved_cost'                    => null,
            'awarded_cost'                     => null,
            'awarded_nominated_sub_contractor' => null,
            'awarded_date'                     => null,
            '_csrf_token'                      => $form->getCSRFToken()
        ));

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $records
        ));
    }

    public function executeGetColumns(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $costData = Doctrine_Core::getTable('CostData')->find($request->getParameter('costData'))
        );

        $columns = CostDataPrimeCostSumColumnDefinitionTable::getPrimeCostSumColumns($costData);

        return $this->renderJson(array(
            'columns' => $columns
        ));
    }

    public function executeGetColumnsList(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $costData = Doctrine_Core::getTable('CostData')->find($request->getParameter('costData'))
        );

        $items = DoctrineQuery::create()->select('d.id, d.column_name')
            ->from('CostDataPrimeCostSumColumnDefinition d')
            ->where('d.cost_data_id = ?', $costData->id)
            ->addOrderBy('d.priority ASC')
            ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
            ->execute();

        $form = new BaseForm();

        foreach($items as &$item)
        {
            $item['_csrf_token'] = $form->getCSRFToken();
        }

        array_push($items, array(
            'id'          => Constants::GRID_LAST_ROW,
            'column_name' => "",
            '_csrf_token' => $form->getCSRFToken()
        ));

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $items
        ));
    }

    public function executeAddNewColumn(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $costData = Doctrine_Core::getTable('CostData')->find($request->getParameter('cost_data_id'))
        );

        $priority = 1;

        if( ( $previousItemId = ( intval($request->getParameter('prev_item_id')) ) ) > 0 )
        {
            $previousItem = Doctrine_Core::getTable('CostDataPrimeCostSumColumnDefinition')->find($previousItemId);
            $priority     = $previousItem->priority + 1;
        }

        $item               = new CostDataPrimeCostSumColumnDefinition();
        $item->cost_data_id = $costData->id;
        $item->column_name  = $request->getParameter('column_name');
        $item->priority     = $priority;
        $item->save();

        $items = array();

        $form = new BaseForm();

        $items[] = array(
            'id'          => $item->id,
            'column_name' => $item->column_name,
            '_csrf_token' => $form->getCSRFToken(),
        );

        array_push($items, array(
            'id'          => Constants::GRID_LAST_ROW,
            'column_name' => "",
            '_csrf_token' => $form->getCSRFToken(),
        ));

        return $this->renderJson(array(
            'success' => true,
            'items'   => $items
        ));
    }

    public function executeUpdateColumn(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $costData = Doctrine_Core::getTable('CostData')->find($request->getParameter('cost_data_id')) and
            $item = Doctrine_Core::getTable('CostDataPrimeCostSumColumnDefinition')->find($request->getParameter('id'))
        );

        $success  = false;
        $errorMsg = null;

        try
        {
            $item->column_name = $request->getParameter('column_name');
            $item->save();

            $success = true;
        }
        catch(Exception $e)
        {
            $errorMsg = $e->getMessage();
        }

        $data = array( 'column_name' => $item->column_name );

        return $this->renderJson(array(
            'success'  => $success,
            'errorMsg' => $errorMsg,
            'data'     => $data
        ));
    }

    public function executeDeleteColumn(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $item = Doctrine_Core::getTable('CostDataPrimeCostSumColumnDefinition')->find($request->getParameter('id'))
        );

        $success  = false;
        $errorMsg = null;

        try
        {
            $item->delete();
            $success = true;
        }
        catch(Exception $e)
        {
            $errorMsg = $e->getMessage();
        }

        return $this->renderJson(array(
            'success'  => $success,
            'errorMsg' => $errorMsg,
        ));
    }

    public function executeUpdatePrimeCostSumItem(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $costData = Doctrine_Core::getTable('CostData')->find($request->getParameter('cost_data_id')) and
            $masterItem = Doctrine_Core::getTable('MasterCostDataPrimeCostSumItem')->find($request->getParameter('id'))
        );

        $attribute = $request->getParameter('attr_name');

        $costColumns = array(
            'approved_cost',
            'awarded_cost',
        );

        $success  = false;
        $errorMsg = null;
        $data     = array();

        try
        {
            $value = trim($request->getParameter('val'));

            if( in_array($attribute, $costColumns) ) $value = (float)$value;

            if( $attribute == 'awarded_date' ) $value = Utilities::convertJavascriptDateToPhp($value, 'Y-m-d');

            $item = CostDataPrimeCostSumItemTable::setValue($costData, $masterItem, $attribute, $value);
            $data = array( $attribute => $item->{$attribute} );

            if( in_array($attribute, $costColumns) ) $data["{$attribute}_derived"] = false;

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

    public function executeUpdatePrimeCostSumColumn(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $costData = Doctrine_Core::getTable('CostData')->find($request->getParameter('cost_data_id')) and
            $masterItem = Doctrine_Core::getTable('MasterCostDataPrimeCostSumItem')->find($request->getParameter('id'))
        );

        $attribute = $request->getParameter('attr_name');
        $value = (float)trim($request->getParameter('val'));

        $columnId = intval(str_replace('additional_column-', '', $attribute));

        $success  = false;
        $errorMsg = null;
        $data     = array();

        try
        {
            $item = CostDataPrimeCostSumColumnTable::setValue($masterItem, $columnId, $value);

            $columns = CostDataPrimeCostSumColumnDefinitionTable::getPrimeCostSumColumns($costData);

            $columnValues = CostDataPrimeCostSumColumnTable::getValues($costData, array( $masterItem->id ));

            $columnsTotal = 0;

            foreach($columns as $column)
            {
                $columnAmount = $columnValues[ $masterItem->id ]["amount_{$column['id']}"];

                $columnsTotal += $columnAmount;
            }

            $data    = array( $attribute => $item->amount, 'nominated_sub_contractor_total_amount' => $columnsTotal );
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

    public function executeGetItemLinkedItems(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $costData = Doctrine_Core::getTable('CostData')->find($request->getParameter('cost_data_id')) and
            $masterItem = Doctrine_Core::getTable('MasterCostDataPrimeCostSumItem')->find($request->getParameter('id'))
        );

        $billItems = BillItemCostDataPrimeCostSumItemTable::getLinkedBillItems($costData, $masterItem);

        $ids = array_column($billItems, $request->getParameter('id_type'));

        $item = CostDataPrimeCostSumItemTable::getItem($costData, $masterItem);

        return $this->renderJson(array(
            'success'           => true,
            'ids'               => $ids,
            'conversion_factor' => $item->conversion_factor,
        ));
    }

    public function executeLinkItemToBillItems(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $costData = Doctrine_Core::getTable('CostData')->find($request->getParameter('cost_data_id')) and
            $masterItem = Doctrine_Core::getTable('MasterCostDataPrimeCostSumItem')->find($request->getParameter('id'))
        );

        $selectedBillItemIds   = empty($request->getParameter('selectedIds')) ? array() : explode(',', $request->getParameter('selectedIds'));
        $deselectedBillItemIds = empty($request->getParameter('deselectedIds')) ? array() : explode(',', $request->getParameter('deselectedIds'));
        $conversionFactor      = trim($request->getParameter('conversion_factor'));

        $item = CostDataPrimeCostSumItemTable::getItem($costData, $masterItem);

        $success  = false;
        $errorMsg = null;

        try
        {
            if( is_numeric($conversionFactor) )
            {
                $item->conversion_factor = $conversionFactor;
                $item->save();
            }

            BillItemCostDataPrimeCostSumItemTable::sync($costData, $masterItem, $selectedBillItemIds, $deselectedBillItemIds);

            $success = true;
        }
        catch(Exception $e)
        {
            $errorMsg = $e->getMessage();
        }

        $billItems = BillItemCostDataPrimeCostSumItemTable::getLinkedBillItems($costData, $masterItem);

        $billItemIds = array_column($billItems, 'bill_item_id');

        return $this->renderJson(array(
            'success'           => $success,
            'errorMsg'          => $errorMsg,
            'bill_item_ids'     => $billItemIds,
            'conversion_factor' => $item->conversion_factor,
        ));
    }

    public function executeGetColumnLinkedItems(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $masterItem = Doctrine_Core::getTable('MasterCostDataPrimeCostSumItem')->find($request->getParameter('id'))
        );

        $columnId = intval($request->getParameter('column_id'));

        $billItems = BillItemCostDataPrimeCostSumColumnTable::getLinkedBillItems($masterItem, $columnId);

        $ids = array_column($billItems, $request->getParameter('id_type'));

        $item = CostDataPrimeCostSumColumnTable::getItem($masterItem, $columnId);

        return $this->renderJson(array(
            'success'           => true,
            'ids'               => $ids,
            'conversion_factor' => $item->conversion_factor,
        ));
    }

    public function executeLinkColumnToBillItems(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $masterItem = Doctrine_Core::getTable('MasterCostDataPrimeCostSumItem')->find($request->getParameter('id'))
        );

        $columnId              = intval($request->getParameter('column_id'));
        $selectedBillItemIds   = empty($request->getParameter('selectedIds')) ? array() : explode(',', $request->getParameter('selectedIds'));
        $deselectedBillItemIds = empty($request->getParameter('deselectedIds')) ? array() : explode(',', $request->getParameter('deselectedIds'));
        $conversionFactor      = trim($request->getParameter('conversion_factor'));

        $item = CostDataPrimeCostSumColumnTable::getItem($masterItem, $columnId);

        $success  = false;
        $errorMsg = null;

        try
        {
            if( is_numeric($conversionFactor) )
            {
                $item->conversion_factor = $conversionFactor;
                $item->save();
            }

            BillItemCostDataPrimeCostSumColumnTable::sync($masterItem, $columnId, $selectedBillItemIds, $deselectedBillItemIds);

            $success = true;
        }
        catch(Exception $e)
        {
            $errorMsg = $e->getMessage();
        }

        $billItems = BillItemCostDataPrimeCostSumColumnTable::getLinkedBillItems($masterItem, $columnId);

        $billItemIds = array_column($billItems, 'bill_item_id');

        return $this->renderJson(array(
            'success'           => $success,
            'errorMsg'          => $errorMsg,
            'bill_item_ids'     => $billItemIds,
            'conversion_factor' => $item->conversion_factor,
        ));
    }

    public function executeGetAllPrimeCostSumItemRecords(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $costData = Doctrine_Core::getTable('CostData')->find($request->getParameter('costData'))
        );

        $records = DoctrineQuery::create()->select('i.id, i.description')
            ->from('MasterCostDataPrimeCostSumItem i')
            ->where('i.master_cost_data_id = ?', $costData->master_cost_data_id)
            ->addOrderBy('i.priority ASC')
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
            CostDataPrimeCostSumItemTable::setItemVisibility($costData, $selectedBillItemIds, true);
            CostDataPrimeCostSumItemTable::setItemVisibility($costData, $deselectedBillItemIds, false);

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
}
