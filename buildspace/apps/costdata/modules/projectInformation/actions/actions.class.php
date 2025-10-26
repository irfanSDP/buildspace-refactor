<?php

class projectInformationActions extends BaseActions
{
    public function executeGetBreakdown(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $costData = Doctrine_Core::getTable('CostData')->find($request->getParameter('costData'))
        );

        $parentId = $request->hasParameter('parent_id') ? $request->getParameter('parent_id') : null;

        $items = CostDataProjectInformationTable::getItemList($costData, $parentId);

        $masterIds = array_column($items, 'id');

        $values = CostDataProjectInformationTable::getRecordValues($costData, $masterIds);

        $form = new BaseForm();

        $records = [];

        foreach($items as $key => $item)
        {
            $item['value']       = $values[ $item['id'] ]['description'];
            $item['_csrf_token'] = $form->getCSRFToken();

            $records[] = $item;
        }

        array_push($records, array(
            'id'          => Constants::GRID_LAST_ROW,
            'description' => "",
            'value'       => "",
        ));

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
            $masterItem = Doctrine_Core::getTable('MasterCostDataProjectInformation')->find($request->getParameter('id'))
        );

        $value = trim($request->getParameter('val'));

        $success  = false;
        $errorMsg = null;
        $data     = array();

        try
        {
            $item = CostDataProjectInformationTable::setValue($costData, $masterItem, 'description', $value);

            $data = array( 'value' => $item->description );

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
}
