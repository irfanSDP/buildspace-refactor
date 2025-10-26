<?php
/**
 * pageGenerator actions.
 *
 * @package    buildspace
 * @subpackage pageGenerator
 * @author     1337 developers
 * @version    SVN: $Id$
 */
class pageGeneratorActions extends BaseActions
{
    public function executeGetBills(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $project = ProjectStructureTable::getInstance()->find($request->getParameter('pid')) and
            $project->type == ProjectStructure::TYPE_ROOT
        );

        $form = new BaseForm();

        $query = DoctrineQuery::create()
        ->select('s.id, s.title, s.type, s.level, t.type, t.status, bls.id, somls.id, sorbls.id')
        ->from('ProjectStructure s')
        ->leftJoin('s.BillType t')
        ->leftJoin('s.BillLayoutSetting bls')
        ->leftJoin('s.SupplyOfMaterialLayoutSetting somls')
        ->leftJoin('s.ScheduleOfRateBillLayoutSetting sorbls')
        ->where('s.lft >= ? AND s.rgt <= ?', array( $project->lft, $project->rgt ))
        ->andWhere('s.root_id = ?', $project->root_id);

        if($request->hasParameter('bid') && $bill = ProjectStructureTable::getInstance()->find($request->getParameter('bid')))
        {
            $query->andWhere('s.id = ?', $bill->id);
        }

        $records = $query->andWhereIn('s.type', [ProjectStructure::TYPE_BILL, ProjectStructure::TYPE_SCHEDULE_OF_RATE_BILL, ProjectStructure::TYPE_SUPPLY_OF_MATERIAL_BILL])
        ->addOrderBy('s.lft ASC')
        ->fetchArray();

        foreach($records as $key => $record)
        {
            $records[ $key ]['billLayoutSettingId']    = ( isset( $record['BillLayoutSetting']['id'] ) ) ? $record['BillLayoutSetting']['id'] : null;
            $records[ $key ]['somBillLayoutSettingId'] = ( isset( $record['SupplyOfMaterialLayoutSetting']['id'] ) ) ? $record['SupplyOfMaterialLayoutSetting']['id'] : null;
            $records[ $key ]['sorBillLayoutSettingId'] = ( isset( $record['ScheduleOfRateBillLayoutSetting']['id'] ) ) ? $record['ScheduleOfRateBillLayoutSetting']['id'] : null;

            if( $record['type'] == ProjectStructure::TYPE_BILL and is_array($records[ $key ]['BillType']) )
            {
                $records[ $key ]['bill_type']   = $record['BillType']['type'];
                $records[ $key ]['bill_status'] = $record['BillType']['status'];
            }

            $records[ $key ]['_csrf_token'] = $form->getCSRFToken();

            unset( $records[ $key ]['BillLayoutSetting'], $records[ $key ]['SupplyOfMaterialLayoutSetting'], $records[ $key ]['ScheduleOfRateBillLayoutSetting'], $records[ $key ]['BillType'], $records[ $key ]['BillColumnSettings'] );
        }

        return $this->renderJson($records);
    }

    public function executeValidateBill(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('id'))
        );

        try
        {
            switch($bill->type)
            {
                case ProjectStructure::TYPE_BILL:
                    $bqPageGenerator = new sfBuildspaceBQPageGenerator($bill);
                    break;
                case ProjectStructure::TYPE_SUPPLY_OF_MATERIAL_BILL:
                    $bqPageGenerator = new sfBuildspaceSupplyOfMaterialBillPageGenerator($bill);
                    break;
                case ProjectStructure::TYPE_SCHEDULE_OF_RATE_BILL:
                    $bqPageGenerator = new sfBuildspaceScheduleOfRateBillPageGenerator($bill);
                    break;
                default:
                    throw new Exception('Invalid Bill Type');
            }

            $pages = $bqPageGenerator->generatePages();

            $success = true;
            $error   = null;
        }
        catch(PageGeneratorException $e)
        {
            $success = false;
            $error   = $e->getMessage();
        }

        return $this->renderJson([
            'success' => $success,
            'error'   => $error
        ]);
    }

    public function executeValidateAddendumBill(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('id'))
        );

        //only normal bills will have addendum 
        if($bill->type != ProjectStructure::TYPE_BILL)
        {
            return $this->renderJson([
                'success' => true,
                'error'   => null
            ]);
        }

        // get current root project structure's revision
        $selectedProjectRevision = ProjectRevisionTable::getCurrentSelectedProjectRevisionFromBillId($bill->root_id);

        // get affected element and page no
        $elements = DoctrineQuery::create()
            ->select('e.id, e.description, bp.id, bp.page_no, i.bill_item_id')
            ->from('BillElement e')
            ->leftJoin('e.BillPages bp')
            ->leftJoin('bp.Items i')
            ->where('e.project_structure_id = ?', $bill->id)
            ->andWhere('bp.new_revision_id = ?', $selectedProjectRevision->id)
            ->addOrderBy('e.priority, bp.page_no ASC')
            ->execute();
        
        if(empty($elements->toArray()))
        {
            return $this->renderJson([
                'success' => true,
                'error'   => null
            ]);
        }

        try
        {
            $bqPageGenerator = new sfBuildspaceBQAddendumGenerator($bill, $selectedProjectRevision, $elements);
            $pages           = $bqPageGenerator->generatePages();
            $success         = true;
            $error           = null;
        }
        catch(PageGeneratorException $e)
        {
            $success = false;
            $error   = $e->getMessage();
        }

        return $this->renderJson([
            'success' => $success,
            'error'   => $error
        ]);
    }
}
