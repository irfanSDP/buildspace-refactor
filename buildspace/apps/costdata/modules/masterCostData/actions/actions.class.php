<?php

class masterCostDataActions extends BaseActions
{
    public function executeGetBreakdown(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $masterCostData = Doctrine_Core::getTable('MasterCostData')->find($request->getParameter('id'))
        );

        $items = DoctrineQuery::create()->select('i.id, i.description')
            ->from('MasterCostDataItem i')
            ->where('i.master_cost_data_id = ?', $masterCostData->id)
            ->andWhere('i.parent_id IS NULL')
            ->addOrderBy('i.priority ASC')
            ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
            ->execute();

        $records = array(
            array(
                'id'          => 'provisional_sum',
                'description' => 'Provisional Sum',
                'type'        => MasterCostData::ITEM_TYPE_PROVISIONAL_SUM,
            ),
            array(
                'id'          => 'prime_cost_sum',
                'description' => 'Prime Cost Sum',
                'type'        => MasterCostData::ITEM_TYPE_PRIME_COST_SUM,
            ),
            array(
                'id'          => 'prime_cost_rate',
                'description' => 'Project Rates Analysis',
                'type'        => MasterCostData::ITEM_TYPE_PRIME_COST_RATE,
            ),
            array(
                'id'          => 'row_separator',
                'description' => 'Standard Items',
                'type'        => null,
            ),
        );

        $form = new BaseForm();

        foreach($items as $item)
        {
            $item['type']        = MasterCostData::ITEM_TYPE_STANDARD;
            $item['_csrf_token'] = $form->getCSRFToken();

            $records[] = $item;
        }

        array_push($records, array(
            'id'          => Constants::GRID_LAST_ROW,
            'description' => "",
            'type'        => MasterCostData::ITEM_TYPE_STANDARD,
            '_csrf_token' => $form->getCSRFToken()
        ));

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $records
        ));
    }

    public function executeAddNewProjectOverallCostingItem(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $masterCostData = Doctrine_Core::getTable('MasterCostData')->find($request->getParameter('master_cost_data_id'))
        );

        $items    = array();
        $success  = false;
        $errorMsg = null;

        $con = Doctrine_Core::getTable('MasterCostData')->getConnection();

        try
        {
            $con->beginTransaction();

            $attribute = $request->getParameter('attr_name');

            $form = new BaseForm();

            $priority = 1;

            if( ( $previousItemId = ( intval($request->getParameter('prev_item_id')) ) ) > 0 )
            {
                $previousItem = Doctrine_Core::getTable('MasterCostDataItem')->find($previousItemId);
                $priority     = $previousItem->priority + 1;
            }

            $item                      = new MasterCostDataItem();
            $item->master_cost_data_id = $masterCostData->id;
            $item->level               = MasterCostDataItem::ITEM_LEVEL_PROJECT_OVERALL_COSTING;
            $item->priority            = $priority;

            if($attribute) $item->{$attribute} = $request->getParameter('val');

            $item->save();

            $item->refresh();

            $items = array();

            $items[] = array(
                'id'          => $item->id,
                'description' => $item->description,
                'type'        => MasterCostData::ITEM_TYPE_STANDARD,
                '_csrf_token' => $form->getCSRFToken(),
            );

            DoctrineQuery::create()
            ->update('MasterCostDataItem')
            ->set('priority', 'priority + 1')
            ->where('priority >= ?', $priority)
            ->andWhere('master_cost_data_id >= ?', $masterCostData->id)
            ->andWhere('level = ?', MasterCostDataItem::ITEM_LEVEL_PROJECT_OVERALL_COSTING)
            ->andWhere('parent_id IS NULL')
            ->andWhere('id != ?', $item->id)
            ->execute();

            if($request->getParameter('current_id') === Constants::GRID_LAST_ROW)
            {
                array_push($items, array(
                    'id'          => Constants::GRID_LAST_ROW,
                    'description' => "",
                    'type'        => null,
                    '_csrf_token' => $form->getCSRFToken(),
                ));
            }

            $con->commit();

            $success = true;
        }
        catch(Exception $e)
        {
            $con->rollback();
            $errorMsg = $e->getMessage();
        }

        return $this->renderJson(array(
            'success'  => $success,
            'items'    => $items,
            'errorMsg' => $errorMsg
        ));
    }

    public function executeUpdateItem(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $masterCostData = Doctrine_Core::getTable('MasterCostData')->find($request->getParameter('master_cost_data_id')) and
            $item = Doctrine_Core::getTable('MasterCostDataItem')->find($request->getParameter('id'))
        );

        $attribute = $request->getParameter('attr_name') == 'description' ? 'description' : '';

        $item->{$attribute} = $request->getParameter('val');
        $item->save();

        $data = array( $attribute => $item->{$attribute} );

        return $this->renderJson(array(
            'success' => true,
            'data'    => $data
        ));
    }

    public function executeGetWorkCategoryList(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $masterCostData = Doctrine_Core::getTable('MasterCostData')->find($request->getParameter('masterCostData')) and
            $projectCostingItem = Doctrine_Core::getTable('MasterCostDataItem')->find($request->getParameter('id'))
        );

        $items = DoctrineQuery::create()->select('i.id, i.description')
            ->from('MasterCostDataItem i')
            ->where('i.master_cost_data_id = ?', $masterCostData->id)
            ->andWhere('i.parent_id = ?', $projectCostingItem->id)
            ->addOrderBy('i.priority ASC')
            ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
            ->execute();

        $form = new BaseForm();

        $records = array();

        foreach($items as $item)
        {
            $item['_csrf_token']      = $form->getCSRFToken();
            $item['unit']             = null;
            $item['quantity']         = null;
            $item['total_acres']      = null;
            $item['gross_floor_area'] = null;
            $item['nett_floor_area']  = null;

            $records[] = $item;
        }

        array_push($records, array(
            'id'               => Constants::GRID_LAST_ROW,
            'description'      => "",
            'unit'             => null,
            'quantity'         => null,
            'total_acres'      => null,
            'gross_floor_area' => null,
            'nett_floor_area'  => null,
            '_csrf_token'      => $form->getCSRFToken()
        ));

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $records
        ));
    }

    public function executeAddNewWorkCategory(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $masterCostData = Doctrine_Core::getTable('MasterCostData')->find($request->getParameter('master_cost_data_id')) and
            $parentItem = Doctrine_Core::getTable('MasterCostDataItem')->find($request->getParameter('parent_id'))
        );

        $items    = array();
        $success  = false;
        $errorMsg = null;

        $con = Doctrine_Core::getTable('MasterCostData')->getConnection();

        try
        {
            $con->beginTransaction();

            $attribute = $request->getParameter('attr_name');

            $form = new BaseForm();

            $priority = 1;

            if( ( $previousItemId = ( intval($request->getParameter('prev_item_id')) ) ) > 0 )
            {
                $previousItem = Doctrine_Core::getTable('MasterCostDataItem')->find($previousItemId);
                $priority     = $previousItem->priority + 1;
            }

            $item                      = new MasterCostDataItem();
            $item->master_cost_data_id = $masterCostData->id;
            $item->parent_id           = $parentItem->id;
            $item->level               = MasterCostDataItem::ITEM_LEVEL_WORK_CATEGORY;
            $item->priority            = $priority;
            
            if($attribute) $item->{$attribute} = $request->getParameter('val');

            $item->save();

            $item->refresh();

            $items = array();

            $items[] = array(
                'id'               => $item->id,
                'description'      => $item->description,
                'unit'             => null,
                'quantity'         => null,
                'total_acres'      => null,
                'gross_floor_area' => null,
                'nett_floor_area'  => null,
                'pc_supply_rate'   => null,
                'brand'            => null,
                '_csrf_token'      => $form->getCSRFToken(),
            );

            DoctrineQuery::create()
            ->update('MasterCostDataItem')
            ->set('priority', 'priority + 1')
            ->where('priority >= ?', $priority)
            ->andWhere('master_cost_data_id >= ?', $masterCostData->id)
            ->andWhere('parent_id = ?', $parentItem->id)
            ->andWhere('id != ?', $item->id)
            ->execute();

            if($request->getParameter('current_id') === Constants::GRID_LAST_ROW)
            {
                array_push($items, array(
                    'id'               => Constants::GRID_LAST_ROW,
                    'description'      => "",
                    'unit'             => null,
                    'quantity'         => null,
                    'total_acres'      => null,
                    'gross_floor_area' => null,
                    'nett_floor_area'  => null,
                    'pc_supply_rate'   => null,
                    'brand'            => null,
                    '_csrf_token'      => $form->getCSRFToken(),
                ));
            }

            $con->commit();

            $success = true;
        }
        catch(Exception $e)
        {
            $con->rollback();
            $errorMsg = $e->getMessage();
        }

        return $this->renderJson(array(
            'success'  => $success,
            'items'    => $items,
            'errorMsg' => $errorMsg
        ));
    }

    public function executeGetElementList(sfWebRequest $request)
    {
        $form = new BaseForm();

        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $masterCostData = Doctrine_Core::getTable('MasterCostData')->find($request->getParameter('masterCostData')) and
            $workCategoryItem = Doctrine_Core::getTable('MasterCostDataItem')->find($request->getParameter('id'))
        );

        $records = DoctrineQuery::create()->select('i.id, i.description')
            ->from('MasterCostDataItem i')
            ->where('i.master_cost_data_id = ?', $masterCostData->id)
            ->andWhere('i.parent_id = ?', $workCategoryItem->id)
            ->addOrderBy('i.priority ASC')
            ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
            ->execute();

        foreach($records as $key => $record)
        {
            $records[ $key ]['_csrf_token'] = $form->getCSRFToken();
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

    public function executeAddNewElement(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $masterCostData = Doctrine_Core::getTable('MasterCostData')->find($request->getParameter('master_cost_data_id')) and
            $parentItem = Doctrine_Core::getTable('MasterCostDataItem')->find($request->getParameter('parent_id'))
        );

        $items    = array();
        $success  = false;
        $errorMsg = null;

        $con = Doctrine_Core::getTable('MasterCostData')->getConnection();

        try
        {
            $con->beginTransaction();

            $attribute = $request->getParameter('attr_name');

            $form = new BaseForm();

            $priority = 1;

            if( ( $previousItemId = ( intval($request->getParameter('prev_item_id')) ) ) > 0 )
            {
                $previousItem = Doctrine_Core::getTable('MasterCostDataItem')->find($previousItemId);
                $priority     = $previousItem->priority + 1;
            }

            $item                      = new MasterCostDataItem();
            $item->master_cost_data_id = $masterCostData->id;
            $item->parent_id           = $parentItem->id;
            $item->level               = MasterCostDataItem::ITEM_LEVEL_ELEMENT;
            $item->priority            = $priority;

            if($attribute) $item->{$attribute} = $request->getParameter('val');

            $item->save();

            $item->refresh();

            $items = array();

            $items[] = array(
                'id'          => $item->id,
                'description' => $item->description,
                '_csrf_token' => $form->getCSRFToken(),
            );

            DoctrineQuery::create()
            ->update('MasterCostDataItem')
            ->set('priority', 'priority + 1')
            ->where('priority >= ?', $priority)
            ->andWhere('master_cost_data_id >= ?', $masterCostData->id)
            ->andWhere('parent_id = ?', $parentItem->id)
            ->andWhere('id != ?', $item->id)
            ->execute();

            if($request->getParameter('current_id') === Constants::GRID_LAST_ROW)
            {
                array_push($items, array(
                    'id'          => Constants::GRID_LAST_ROW,
                    'description' => "",
                    '_csrf_token' => $form->getCSRFToken(),
                ));
            }

            $con->commit();

            $success = true;
        }
        catch(Exception $e)
        {
            $con->rollback();
            $errorMsg = $e->getMessage();
        }

        return $this->renderJson(array(
            'success'  => $success,
            'items'    => $items,
            'errorMsg' => $errorMsg
        ));
    }

    public function executeGetProjectParticularList(sfWebRequest $request)
    {
        $form = new BaseForm();

        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $masterCostData = Doctrine_Core::getTable('MasterCostData')->find($request->getParameter('master_cost_data_id'))
        );

        $records = DoctrineQuery::create()->select('p.id, p.description, p.uom_id, uom.symbol as uom_symbol, is_summary_displayed, summary_description, is_used_for_cost_comparison, is_prime_cost_rate_summary_displayed')
            ->from('MasterCostDataParticular p')
            ->leftJoin('p.UnitOfMeasurement uom')
            ->where('p.master_cost_data_id = ?', $masterCostData->id)
            ->addOrderBy('p.priority ASC')
            ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
            ->execute();

        $items = [];

        foreach($records as $key => $record)
        {
            $items[] = [
                'id'                                   => $record['id'],
                'description'                          => $record['description'],
                'uom_id'                               => $record['uom_id'],
                'uom_symbol'                           => $record['uom_symbol'],
                'is_summary_displayed'                 => $record['is_summary_displayed'],
                'is_prime_cost_rate_summary_displayed' => $record['is_prime_cost_rate_summary_displayed'],
                'summary_description'                  => $record['summary_description'],
                'is_used_for_cost_comparison'          => $record['is_used_for_cost_comparison'],
                '_csrf_token'                          => $form->getCSRFToken(),
            ];
        }

        array_push($items, array(
            'id'                                   => Constants::GRID_LAST_ROW,
            'description'                          => "",
            'uom_id'                               => -1,
            'uom_symbol'                           => '',
            'is_summary_displayed'                 => null,
            'is_prime_cost_rate_summary_displayed' => null,
            'summary_description'                  => '',
            'is_used_for_cost_comparison'          => null,
            '_csrf_token'                          => $form->getCSRFToken()
        ));

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $items
        ));
    }

    public function executeAddNewProjectParticular(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $masterCostData = Doctrine_Core::getTable('MasterCostData')->find($request->getParameter('master_cost_data_id'))
        );

        $attribute = $request->getParameter('attr_name');

        $priority = 1;

        if( ( $previousItemId = ( intval($request->getParameter('prev_item_id')) ) ) > 0 )
        {
            $previousItem = Doctrine_Core::getTable('MasterCostDataParticular')->find($previousItemId);
            $priority     = $previousItem->priority + 1;
        }

        $item                      = new MasterCostDataParticular();
        $item->master_cost_data_id = $masterCostData->id;
        $item->{$attribute}        = $request->getParameter('val');
        $item->priority            = $priority;
        $item->save();

        $items = array();

        $form = new BaseForm();

        $items[] = array(
            'id'                      => $item->id,
            'description'             => $item->description,
            'uom_id'                  => $item->uom_id > 0 ? (string)$item->uom_id : -1,
            'uom_symbol'              => $item->uom_id > 0 ? $item->UnitOfMeasurement->symbol : '',
            'is_summary_displayed'    => $item->is_summary_displayed,
            'summary_description'     => $item->summary_description,
            'is_used_for_cost_comparison' => $item->is_used_for_cost_comparison,
            '_csrf_token'             => $form->getCSRFToken(),
        );

        array_push($items, array(
            'id'                      => Constants::GRID_LAST_ROW,
            'description'             => "",
            'uom_id'                  => -1,
            'uom_symbol'              => '',
            'is_summary_displayed'    => null,
            'summary_description'     => '',
            'is_used_for_cost_comparison' => null,
            '_csrf_token'             => $form->getCSRFToken(),
        ));

        return $this->renderJson(array(
            'success' => true,
            'items'   => $items
        ));
    }

    public function executeUpdateProjectParticular(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $masterCostData = Doctrine_Core::getTable('MasterCostData')->find($request->getParameter('master_cost_data_id')) and
            $item = Doctrine_Core::getTable('MasterCostDataParticular')->find($request->getParameter('id'))
        );

        $attribute = $request->getParameter('attr_name');

        $success  = false;
        $errorMsg = null;

        if( ! $item->inUse() )
        {
            try
            {
                $editableFields = ['description', 'uom_id', 'summary_description'];

                if(in_array($attribute, $editableFields)) $item->itemUpdate($attribute, $request->getParameter('val'));

                $success = true;
            }
            catch(Exception $e)
            {
                $errorMsg = $e->getMessage();
            }
        }
        else
        {
            $errorMsg = 'Item in use.';
        }

        $data = array(
            $attribute   => $item->{$attribute},
            'uom_symbol' => $item->uom_id > 0 ? $item->UnitOfMeasurement->symbol : ''
        );

        return $this->renderJson(array(
            'success'  => $success,
            'errorMsg' => $errorMsg,
            'data'     => $data
        ));
    }

    public function executeGetLinkedProjectParticulars(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $masterCostData = Doctrine_Core::getTable('MasterCostData')->find($request->getParameter('master_cost_data_id')) and
            $item = Doctrine_Core::getTable('MasterCostDataItem')->find($request->getParameter('id'))
        );

        $field = $request->getParameter('field');

        if( $field == 'unit' || $field == 'quantity' ) $field = MasterCostDataItemColumn::COLUMN_UNIT_AND_QUANTITY;

        $linkedProjectParticularIds = MasterCostDataItemColumnParticularTable::getLinkedItemParticulars($item, $field);

        return $this->renderJson(array(
            'success'                       => true,
            'linked_project_particular_ids' => $linkedProjectParticularIds,
        ));
    }

    public function executeLinkProjectParticulars(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $item = Doctrine_Core::getTable('MasterCostDataItem')->find($request->getParameter('id'))
        );

        $field = $request->getParameter('field');

        if( $field == 'unit' || $field == 'quantity' ) $field = MasterCostDataItemColumn::COLUMN_UNIT_AND_QUANTITY;

        $selectedParticularIds = $request->getParameter('selectedIds') ?? array();

        $success  = false;
        $errorMsg = null;

        if( MasterCostDataParticularTable::unitsMatch($selectedParticularIds) )
        {
            MasterCostDataItemColumnParticularTable::sync($item, $field, $selectedParticularIds);
            $success = true;
        }
        else
        {
            $errorMsg = 'Linked particulars must have the same unit of measurement';
        }

        $selectedParticularIds = MasterCostDataItemColumnParticularTable::getLinkedItemParticulars($item, $field);

        return $this->renderJson(array(
            'success'                       => $success,
            'errorMsg'                      => $errorMsg,
            'linked_project_particular_ids' => $selectedParticularIds,
        ));
    }

    public function executeDeleteItem(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $item = Doctrine_Core::getTable('MasterCostDataItem')->find($request->getParameter('id'))
        );

        $success  = false;
        $errorMsg = null;

        if( ! $item->inUse() )
        {
            try
            {
                $item->delete();
                $success = true;
            }
            catch(Exception $e)
            {
                $errorMsg = $e->getMessage();
            }
        }
        else
        {
            $errorMsg = 'This item is still in use.';
        }

        return $this->renderJson(array(
            'success'  => $success,
            'errorMsg' => $errorMsg,
        ));
    }

    public function executeDeleteProjectParticular(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $item = Doctrine_Core::getTable('MasterCostDataParticular')->find($request->getParameter('id'))
        );

        $success  = false;
        $errorMsg = null;

        if( ! $item->inUse() )
        {
            try
            {
                $item->delete();
                $success = true;
            }
            catch(Exception $e)
            {
                $errorMsg = $e->getMessage();
            }
        }
        else
        {
            $errorMsg = 'This item is linked to other records.';
        }

        return $this->renderJson(array(
            'success'  => $success,
            'errorMsg' => $errorMsg,
        ));
    }

    public function executeGetPrimeCostSumBreakdown(sfWebRequest $request)
    {
        $form = new BaseForm();

        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $masterCostData = Doctrine_Core::getTable('MasterCostData')->find($request->getParameter('masterCostData'))
        );

        $records = DoctrineQuery::create()->select('i.id, i.description')
            ->from('MasterCostDataPrimeCostSumItem i')
            ->where('i.master_cost_data_id = ?', $masterCostData->id)
            ->addOrderBy('i.priority ASC')
            ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
            ->execute();

        foreach($records as $key => $record)
        {
            $records[ $key ]['_csrf_token'] = $form->getCSRFToken();
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

    public function executeAddNewPrimeCostSumItem(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $masterCostData = Doctrine_Core::getTable('MasterCostData')->find($request->getParameter('master_cost_data_id'))
        );

        $attribute = $request->getParameter('attr_name');

        $priority = 1;

        if( ( $previousItemId = ( intval($request->getParameter('prev_item_id')) ) ) > 0 )
        {
            $previousItem = Doctrine_Core::getTable('MasterCostDataPrimeCostSumItem')->find($previousItemId);
            $priority     = $previousItem->priority + 1;
        }

        $item                      = new MasterCostDataPrimeCostSumItem();
        $item->master_cost_data_id = $masterCostData->id;
        $item->{$attribute}        = $request->getParameter('val');
        $item->priority            = $priority;
        $item->save();

        $items = array();

        $form = new BaseForm();

        $items[] = array(
            'id'          => $item->id,
            'description' => $item->description,
            '_csrf_token' => $form->getCSRFToken(),
        );

        array_push($items, array(
            'id'          => Constants::GRID_LAST_ROW,
            'description' => "",
            '_csrf_token' => $form->getCSRFToken(),
        ));

        return $this->renderJson(array(
            'success' => true,
            'items'   => $items
        ));
    }

    public function executeUpdatePrimeCostSumItem(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $masterCostData = Doctrine_Core::getTable('MasterCostData')->find($request->getParameter('master_cost_data_id')) and
            $item = Doctrine_Core::getTable('MasterCostDataPrimeCostSumItem')->find($request->getParameter('id'))
        );

        $attribute = $request->getParameter('attr_name');

        $item->{$attribute} = $request->getParameter('val');
        $item->save();

        $data = array( $attribute => $item->{$attribute} );

        return $this->renderJson(array(
            'success' => true,
            'data'    => $data
        ));
    }

    public function executeDeletePrimeCostSumItem(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $item = Doctrine_Core::getTable('MasterCostDataPrimeCostSumItem')->find($request->getParameter('id'))
        );

        $success  = false;
        $errorMsg = null;

        if(!$item->inUse())
        {
            try
            {
                $item->delete();
                $success = true;
            }
            catch(Exception $e)
            {
                $errorMsg = $e->getMessage();
            }
        }
        else
        {
            $errorMsg = 'This item is still in use.';
        }


        return $this->renderJson(array(
            'success'  => $success,
            'errorMsg' => $errorMsg,
        ));
    }

    public function executeGetPrimeCostRateBreakdown(sfWebRequest $request)
    {
        $form = new BaseForm();

        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $masterCostData = Doctrine_Core::getTable('MasterCostData')->find($request->getParameter('masterCostData'))
        );

        $parentId = $request->getParameter('parent_id');

        $query = DoctrineQuery::create()->select('i.id, i.description, i.uom_id, uom.symbol as uom_symbol')
            ->from('MasterCostDataPrimeCostRate i')
            ->leftJoin('i.UnitOfMeasurement uom')
            ->where('i.master_cost_data_id = ?', $masterCostData->id);

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

        $items = [];

        foreach($records as $key => $record)
        {
            $items[] = [
                'id'          => $record['id'],
                'description' => $record['description'],
                'uom_id'      => $record['uom_id'],
                'uom_symbol'  => $record['uom_symbol'],
                '_csrf_token' => $form->getCSRFToken()
            ];
        }

        array_push($items, array(
            'id'          => Constants::GRID_LAST_ROW,
            'description' => "",
            'uom_id'      => -1,
            'uom_symbol'  => '',
            '_csrf_token' => $form->getCSRFToken()
        ));

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $items
        ));
    }

    public function executeAddNewPrimeCostRate(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $masterCostData = Doctrine_Core::getTable('MasterCostData')->find($request->getParameter('master_cost_data_id'))
        );

        $attribute = $request->getParameter('attr_name');
        $parentId  = $request->getParameter('parent_id');

        $priority = 1;
        $level    = 1;
        $parent   = null;

        if( ( $previousItemId = ( intval($request->getParameter('prev_item_id')) ) ) > 0 )
        {
            $previousItem = Doctrine_Core::getTable('MasterCostDataPrimeCostRate')->find($previousItemId);
            $priority     = $previousItem->priority + 1;
        }

        if( intval($parentId) > 0 )
        {
            $parent = Doctrine_Core::getTable('MasterCostDataPrimeCostRate')->find($parentId);
            $level  = $parent->level + 1;
        }

        $item                      = new MasterCostDataPrimeCostRate();
        $item->master_cost_data_id = $masterCostData->id;
        $item->{$attribute}        = $request->getParameter('val');
        $item->level               = $level;
        $item->parent_id           = $parent ? $parent->id : null;
        $item->priority            = $priority;
        $item->save();

        $items = array();

        $form = new BaseForm();

        $items[] = array(
            'id'          => $item->id,
            'description' => $item->description,
            'uom_id'      => $item->uom_id > 0 ? (string)$item->uom_id : -1,
            'uom_symbol'  => $item->uom_id > 0 ? $item->UnitOfMeasurement->symbol : '',
            '_csrf_token' => $form->getCSRFToken(),
        );

        array_push($items, array(
            'id'          => Constants::GRID_LAST_ROW,
            'description' => "",
            'uom_id'      => -1,
            'uom_symbol'  => '',
            '_csrf_token' => $form->getCSRFToken(),
        ));

        return $this->renderJson(array(
            'success' => true,
            'items'   => $items
        ));
    }

    public function executeUpdatePrimeCostRate(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $masterCostData = Doctrine_Core::getTable('MasterCostData')->find($request->getParameter('master_cost_data_id')) and
            $item = Doctrine_Core::getTable('MasterCostDataPrimeCostRate')->find($request->getParameter('id'))
        );

        $attribute = $request->getParameter('attr_name');

        $editableFields = ['description', 'uom_id'];

        if(in_array($attribute, $editableFields)) $item->itemUpdate($attribute, $request->getParameter('val'));

        $data = array(
            $attribute   => $item->{$attribute},
            'uom_symbol' => $item->uom_id > 0 ? $item->UnitOfMeasurement->symbol : ''
        );

        return $this->renderJson(array(
            'success' => true,
            'data'    => $data
        ));
    }

    public function executeDeletePrimeCostRate(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $item = Doctrine_Core::getTable('MasterCostDataPrimeCostRate')->find($request->getParameter('id'))
        );

        $success  = false;
        $errorMsg = null;

        if( ! $item->inUse() )
        {
            try
            {
                $item->delete();
                $success = true;
            }
            catch(Exception $e)
            {
                $errorMsg = $e->getMessage();
            }
        }
        else
        {
            $errorMsg = 'This item is still in use.';
        }

        return $this->renderJson(array(
            'success'  => $success,
            'errorMsg' => $errorMsg,
        ));
    }

    public function executeToggleDisplayInSummary(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $masterCostData = Doctrine_Core::getTable('MasterCostData')->find($request->getParameter('master_cost_data_id')) and
            $item = Doctrine_Core::getTable('MasterCostDataParticular')->find($request->getParameter('id'))
        );

        $success  = false;
        $errorMsg = null;
        $data     = [];

        try
        {
            $item->is_summary_displayed = ! $item->is_summary_displayed;
            $item->save();

            $data = [
                'is_summary_displayed' => $item->is_summary_displayed,
            ];

            $success = true;
        }
        catch(Exception $e)
        {
            $errorMsg = $e->getMessage();
        }

        return $this->renderJson(array(
            'success'  => $success,
            'errorMsg' => $errorMsg,
            'data'     => $data
        ));
    }

    public function executeTogglePrimeCostRateDisplayInSummary(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $masterCostData = Doctrine_Core::getTable('MasterCostData')->find($request->getParameter('master_cost_data_id')) and
            $item = Doctrine_Core::getTable('MasterCostDataParticular')->find($request->getParameter('id'))
        );

        $success  = false;
        $errorMsg = null;
        $data     = [];

        try
        {
            $item->is_prime_cost_rate_summary_displayed = ! $item->is_prime_cost_rate_summary_displayed;
            $item->save();

            $data = [
                'is_prime_cost_rate_summary_displayed' => $item->is_prime_cost_rate_summary_displayed,
            ];

            $success = true;
        }
        catch(Exception $e)
        {
            $errorMsg = $e->getMessage();
        }

        return $this->renderJson(array(
            'success'  => $success,
            'errorMsg' => $errorMsg,
            'data'     => $data
        ));
    }

    public function executeUseForCostComparison(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $masterCostData = Doctrine_Core::getTable('MasterCostData')->find($request->getParameter('master_cost_data_id')) and
            $item = Doctrine_Core::getTable('MasterCostDataParticular')->find($request->getParameter('id'))
        );

        $success  = false;
        $errorMsg = null;
        $data     = [];

        try
        {
            $item->is_used_for_cost_comparison = ! $item->is_used_for_cost_comparison;
            $item->save();

            $data = [
                'is_used_for_cost_comparison' => $item->is_used_for_cost_comparison,
            ];

            $success = true;
        }
        catch(Exception $e)
        {
            $errorMsg = $e->getMessage();
        }

        return $this->renderJson(array(
            'success'  => $success,
            'errorMsg' => $errorMsg,
            'data'     => $data
        ));
    }

    public function executeGetProjectInformationBreakdown(sfWebRequest $request)
    {
        $form = new BaseForm();

        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $masterCostData = Doctrine_Core::getTable('MasterCostData')->find($request->getParameter('masterCostData'))
        );

        $parentId = $request->getParameter('parent_id');

        $query = DoctrineQuery::create()->select('i.id, i.description')
            ->from('MasterCostDataProjectInformation i')
            ->where('i.master_cost_data_id = ?', $masterCostData->id);

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

        foreach($records as $key => $record)
        {
            $records[ $key ]['_csrf_token'] = $form->getCSRFToken();
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

    public function executeAddNewProjectInformation(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $masterCostData = Doctrine_Core::getTable('MasterCostData')->find($request->getParameter('master_cost_data_id'))
        );

        $parentId  = $request->getParameter('parent_id');

        $priority = 1;
        $level    = 1;
        $parent   = null;

        if( ( $previousItemId = ( intval($request->getParameter('prev_item_id')) ) ) > 0 )
        {
            $previousItem = Doctrine_Core::getTable('MasterCostDataProjectInformation')->find($previousItemId);
            $priority     = $previousItem->priority + 1;
        }

        if( intval($parentId) > 0 )
        {
            $parent = Doctrine_Core::getTable('MasterCostDataProjectInformation')->find($parentId);
            $level  = $parent->level + 1;
        }

        $item                      = new MasterCostDataProjectInformation();
        $item->master_cost_data_id = $masterCostData->id;
        $item->description         = $request->getParameter('val');
        $item->level               = $level;
        $item->parent_id           = $parent ? $parent->id : null;
        $item->priority            = $priority;
        $item->save();

        $items = array();

        $form = new BaseForm();

        $items[] = array(
            'id'          => $item->id,
            'description' => $item->description,
            '_csrf_token' => $form->getCSRFToken(),
        );

        array_push($items, array(
            'id'          => Constants::GRID_LAST_ROW,
            'description' => "",
            '_csrf_token' => $form->getCSRFToken(),
        ));

        return $this->renderJson(array(
            'success' => true,
            'items'   => $items
        ));
    }

    public function executeUpdateProjectInformation(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $masterCostData = Doctrine_Core::getTable('MasterCostData')->find($request->getParameter('master_cost_data_id')) and
            $item = Doctrine_Core::getTable('MasterCostDataProjectInformation')->find($request->getParameter('id'))
        );

        $item->description = $request->getParameter('val');
        $item->save();

        $data = array( 'description' => $item->description );

        return $this->renderJson(array(
            'success' => true,
            'data'    => $data
        ));
    }

    public function executeDeleteProjectInformation(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $item = Doctrine_Core::getTable('MasterCostDataProjectInformation')->find($request->getParameter('id'))
        );

        $success  = false;
        $errorMsg = null;

        if( ! $item->inUse() )
        {
            try
            {
                $item->delete();
                $success = true;
            }
            catch(Exception $e)
            {
                $errorMsg = $e->getMessage();
            }
        }
        else
        {
            $errorMsg = 'This item is still in use.';
        }

        return $this->renderJson(array(
            'success'  => $success,
            'errorMsg' => $errorMsg,
        ));
    }

    public function executeGetProjectParticularComponentList(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $masterCostData = Doctrine_Core::getTable('MasterCostData')->find($request->getParameter('master_cost_data_id')) and
            $item = Doctrine_Core::getTable('MasterCostDataParticular')->find($request->getParameter('id'))
        );

        $items = DoctrineQuery::create()->select('i.id, i.description')
            ->from('MasterCostDataItem i')
            ->where('i.master_cost_data_id = ?', $masterCostData->id)
            ->andWhere('i.parent_id IS NULL')
            ->addOrderBy('i.priority ASC')
            ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
            ->execute();

        array_push($items, array(
            'id'          => Constants::GRID_LAST_ROW,
            'description' => "",
        ));

        array_unshift($items, array(
            'id'          => 'provisional_sum',
            'description' => 'Provisional Sum',
        ));

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $items
        ));
    }

    public function executeGetProjectParticularGetSelectedComponents(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $item = Doctrine_Core::getTable('MasterCostDataParticular')->find($request->getParameter('id'))
        );

        $pdo = Doctrine_Core::getTable('MasterCostDataParticular')::getInstance()->getConnection()->getDbh();

        $stmt = $pdo->prepare("
            SELECT master_cost_data_item_id FROM " . MasterCostDataParticularMasterCostDataItemTable::getInstance()->getTableName() . "
            WHERE master_cost_data_particular_id = {$item->id};
            ");

        $stmt->execute();

        $data = $stmt->fetchAll(PDO::FETCH_COLUMN);

        if($item->include_provisional_sum) $data[] = 'provisional_sum';

        return $this->renderJson(array(
            'success'      => true,
            'selected_ids' => $data
        ));
    }

    public function executeUpdateProjectParticularSelectedComponents(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $particular = Doctrine_Core::getTable('MasterCostDataParticular')->find($request->getParameter('id'))
        );

        $selectedIds = $request->getParameter('selected_ids') ?? [];
        $deselectedIds = $request->getParameter('deselected_ids') ?? [];

        $includeProvisionalSum = false;

        if(in_array('provisional_sum', $selectedIds)) $includeProvisionalSum = true;
        if(in_array('provisional_sum', $deselectedIds)) $includeProvisionalSum = false;

        // Remove provisional_sum from the list.
        $selectedIds = array_diff($selectedIds, ['provisional_sum']);
        $deselectedIds = array_diff($deselectedIds, ['provisional_sum']);

        $pdo = Doctrine_Core::getTable('MasterCostDataParticularMasterCostDataItem')::getInstance()->getConnection()->getDbh();

        $success = false;
        $errorMsg = null;

        try
        {
            if(!empty($deselectedIds))
            {
                $questionMarks = '(' . implode(',', array_fill(0, count($deselectedIds), '?')) . ')';

                $statement = "DELETE FROM " . MasterCostDataParticularMasterCostDataItemTable::getInstance()->getTableName() . " pivot
                    WHERE master_cost_data_particular_id = {$particular->id}
                    AND master_cost_data_item_id in {$questionMarks}";

                $stmt = $pdo->prepare($statement);

                $stmt->execute($deselectedIds);
            }
            if(!empty($selectedIds))
            {
                $stmt = $pdo->prepare("
                    SELECT master_cost_data_item_id FROM " . MasterCostDataParticularMasterCostDataItemTable::getInstance()->getTableName() . "
                    WHERE master_cost_data_particular_id = {$particular->id};
                    ");

                $stmt->execute();

                $linkedItemIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

                $newlySelectedIds = array_diff($selectedIds, $linkedItemIds);

                foreach($newlySelectedIds as $itemId)
                {
                    $pivotRecord = new MasterCostDataParticularMasterCostDataItem();
                    $pivotRecord->master_cost_data_particular_id = $particular->id;
                    $pivotRecord->master_cost_data_item_id = $itemId;
                    $pivotRecord->save();
                }
            }

            if($particular->include_provisional_sum !== $includeProvisionalSum)
            {
                $particular->include_provisional_sum = $includeProvisionalSum;
                $particular->save();
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
        ));
    }

    public function executeGetWorkCategoryParticulars(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $item = Doctrine_Core::getTable('MasterCostDataItem')->find($request->getParameter('id'))
        );

        $pdo = Doctrine_Core::getTable('MasterCostDataItem')::getInstance()->getConnection()->getDbh();

        $stmt = $pdo->prepare("
            SELECT master_cost_data_particular_id FROM " . MasterCostDataItemParticularTable::getInstance()->getTableName() . "
            WHERE master_cost_data_item_id = {$item->id};
            ");

        $stmt->execute();

        $data = $stmt->fetchAll(PDO::FETCH_COLUMN);

        return $this->renderJson(array(
            'success'      => true,
            'selected_ids' => $data
        ));
    }

    public function executeUpdateWorkCategorySelectedParticulars(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $item = Doctrine_Core::getTable('MasterCostDataItem')->find($request->getParameter('id'))
        );

        $selectedIds = $request->getParameter('selected_ids') ?? [];
        $deselectedIds = $request->getParameter('deselected_ids') ?? [];

        $pdo = Doctrine_Core::getTable('MasterCostDataItemParticular')::getInstance()->getConnection()->getDbh();

        $success = false;
        $errorMsg = null;

        try
        {
            if(!empty($deselectedIds))
            {
                $questionMarks = '(' . implode(',', array_fill(0, count($deselectedIds), '?')) . ')';

                $statement = "DELETE FROM " . MasterCostDataItemParticularTable::getInstance()->getTableName() . " pivot
                    WHERE master_cost_data_item_id = {$item->id}
                    AND master_cost_data_particular_id in {$questionMarks}";

                $stmt = $pdo->prepare($statement);

                $stmt->execute($deselectedIds);
            }
            if(!empty($selectedIds))
            {
                $stmt = $pdo->prepare("
                    SELECT master_cost_data_particular_id FROM " . MasterCostDataItemParticularTable::getInstance()->getTableName() . "
                    WHERE master_cost_data_item_id = {$item->id};
                    ");

                $stmt->execute();

                $linkedItemIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

                $newlySelectedIds = array_diff($selectedIds, $linkedItemIds);

                foreach($newlySelectedIds as $particularId)
                {
                    $pivotRecord = new MasterCostDataItemParticular();
                    $pivotRecord->master_cost_data_item_id = $item->id;
                    $pivotRecord->master_cost_data_particular_id = $particularId;
                    $pivotRecord->save();
                }
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
        ));
    }
}
