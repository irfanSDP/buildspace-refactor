<?php

/**
 * provisionalSum actions.
 *
 * @package    buildspace
 * @subpackage provisionalSum
 * @author     1337 developers
 * @version    SVN: $Id$
 */
class provisionalSumActions extends sfActions
{
    public function executeGetProvisionalSumBreakdown(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $costData = Doctrine_Core::getTable('CostData')->find($request->getParameter('costData'))
        );

        $items = DoctrineQuery::create()->select('i.id, i.description, i.approved_cost, i.awarded_cost, i.awarded_date, i.variation_order_cost, ROUND(i.awarded_cost + i.variation_order_cost) AS adjusted_sum')
            ->from('CostDataProvisionalSumItem i')
            ->where('i.cost_data_id = ?', $costData->id)
            ->addOrderBy('i.priority ASC')
            ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
            ->execute();

        $form = new BaseForm();

        foreach($items as $key => $item)
        {
            $items[$key]['_csrf_token'] = $form->getCSRFToken();
        }

        array_push($items, array(
            'id'            => Constants::GRID_LAST_ROW,
            'description'   => "",
            'approved_cost' => "0",
            'awarded_cost'  => "0",
            'awarded_date'  => null,
            '_csrf_token'   => $form->getCSRFToken()
        ));

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $items
        ));
    }

    public function executeAddNewItem(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $costData = Doctrine_Core::getTable('CostData')->find($request->getParameter('cost_data_id'))
        );

        $attribute = $request->getParameter('attr_name');

        $priority = 1;

        if( ( $previousItemId = ( intval($request->getParameter('prev_item_id')) ) ) > 0 )
        {
            $previousItem = Doctrine_Core::getTable('CostDataProvisionalSumItem')->find($previousItemId);
            $priority     = $previousItem->priority + 1;
        }

        $form = new BaseForm();

        $value = trim($request->getParameter('val'));

        $costColumns = array(
            'approved_cost',
            'awarded_cost',
            'variation_order_cost',
        );

        if( in_array($attribute, $costColumns) ) $value = (float)$value;

        $item               = new CostDataProvisionalSumItem();
        $item->cost_data_id = $costData->id;
        $item->{$attribute} = $value;
        $item->priority     = $priority;
        $item->save();

        $items = array();

        $items[] = array(
            'id'                   => $item->id,
            'description'          => $item->description,
            'approved_cost'        => $item->approved_cost,
            'awarded_cost'         => $item->awarded_cost,
            'awarded_date'         => $item->awarded_date,
            'variation_order_cost' => $item->variation_order_cost,
            'adjusted_sum'         => $item->awarded_cost + $item->variation_order_cost,
            '_csrf_token'          => $form->getCSRFToken(),
        );

        array_push($items, array(
            'id'                   => Constants::GRID_LAST_ROW,
            'description'          => "",
            'approved_cost'        => "0",
            'awarded_cost'         => "0",
            'awarded_date'         => null,
            '_csrf_token'          => $form->getCSRFToken(),
            'variation_order_cost' => "0",
        ));

        return $this->renderJson(array(
            'success' => true,
            'items'   => $items
        ));
    }

    public function executeUpdateItem(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $costData = Doctrine_Core::getTable('CostData')->find($request->getParameter('cost_data_id')) and
            $item = Doctrine_Core::getTable('CostDataProvisionalSumItem')->find($request->getParameter('id'))
        );

        $attribute = $request->getParameter('attr_name');

        $costColumns = array(
            'approved_cost',
            'awarded_cost',
            'variation_order_cost',
        );

        $success  = false;
        $errorMsg = null;

        try
        {
            $value = trim($request->getParameter('val'));

            if( in_array($attribute, $costColumns) ) $value = (float)$value;

            if( $attribute == 'awarded_date' ) $value = Utilities::convertJavascriptDateToPhp($value, 'Y-m-d');

            $item->{$attribute} = $value;
            $item->save();

            $success = true;
        }
        catch(Exception $e)
        {
            $errorMsg = $e->getMessage();
        }

        $data = array( $attribute => $item->{$attribute}, 'adjusted_sum' => $item->awarded_cost + $item->variation_order_cost );

        return $this->renderJson(array(
            'success'  => $success,
            'errorMsg' => $errorMsg,
            'data'     => $data
        ));
    }

    public function executeDeleteItem(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $item = Doctrine_Core::getTable('CostDataProvisionalSumItem')->find($request->getParameter('id'))
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
}
