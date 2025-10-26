<?php

/**
 * subProjectItemLink actions.
 *
 * @package    buildspace
 * @subpackage subProjectItemLink
 * @author     1337 developers
 * @version    SVN: $Id$
 */
class subProjectItemLinkActions extends sfActions
{
    public function executeGetSubProjectList(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $project = ProjectStructureTable::getInstance()->find($request->getParameter('pid'))
        );

        $pdo = $project->getTable()->getConnection()->getDbh();

        $subProjects = ProjectStructureTable::getSubProjects($project);

        $records = array();

        foreach($subProjects as $subProject)
        {
            $records[] = array(
                'id'    => $subProject->id,
                'title' => $subProject->title,
            );
        }

        array_push($records, array(
            'id'    => Constants::GRID_LAST_ROW,
            'title' => '',
        ));

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $records
        ));
    }

    public function executeGetSubProjectBillList(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $project = ProjectStructureTable::getInstance()->find($request->getParameter('pid'))
        );

        $pdo = $project->getTable()->getConnection()->getDbh();

        $stmt = $pdo->prepare("SELECT b.id, b.title as description, b.type, b.level
	        FROM " . ProjectStructureTable::getInstance()->getTableName() . " b
	        WHERE b.root_id = {$project->id}
	        AND b.id != b.root_id
	        AND b.deleted_at IS NULL
	        ORDER BY b.lft asc");

        $stmt->execute();

        $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmt = $pdo->prepare("SELECT b.id, COUNT(*)
            FROM " . BillItemTable::getInstance()->getTableName() . " i
            JOIN " . BillElementTable::getInstance()->getTableName() . " e on e.id = i.element_id
            JOIN " . ProjectStructureTable::getInstance()->getTableName() . " b on b.id = e.project_structure_id
            WHERE b.root_id = {$project->id}
            AND i.type <> " . BillItem::TYPE_HEADER . "
            AND i.type <> " . BillItem::TYPE_HEADER_N . "
            AND i.tender_origin_id IS NULL
            AND b.deleted_at IS NULL
            AND e.deleted_at IS NULL
            AND i.deleted_at IS NULL
            GROUP BY b.id");

        $stmt->execute();

        $untaggedBillItemCount = $stmt->fetch(PDO::FETCH_KEY_PAIR);

        foreach($records as $key => $record)
        {
            $records[ $key ]['untagged_item_count'] = $untaggedBillItemCount[ $record['id'] ] ?? 0;
        }

        array_push($records, array(
            'id'                  => Constants::GRID_LAST_ROW,
            'description'         => "",
            'type'                => - 1,
            'level'               => 0,
            'untagged_item_count' => 0,
        ));

        $stmt = $pdo->prepare("SELECT COUNT(*)
            FROM " . VariationOrderItemTable::getInstance()->getTableName() . " i
            JOIN " . VariationOrderTable::getInstance()->getTableName() . " vo on vo.id = i.variation_order_id
            WHERE vo.project_structure_id = {$project->id}
            AND i.type = " . VariationOrderItem::TYPE_WORK_ITEM . "
            AND i.tender_origin_id IS NULL
            AND vo.deleted_at IS NULL
            AND i.deleted_at IS NULL");

        $stmt->execute();

        $untaggedVariationOrderItemCount = $stmt->fetch(PDO::FETCH_COLUMN);

        array_push($records, array(
            'id'                  => PostContractClaim::TYPE_VARIATION_ORDER_TEXT,
            'description'         => PostContractClaim::TYPE_VARIATION_ORDER_TEXT,
            'untagged_item_count' => $untaggedVariationOrderItemCount,
            'type'                => PostContractClaim::TYPE_VARIATION_ORDER,
            'level'               => 0,
        ));

        array_push($records, array(
            'id'                  => -999,
            'description'         => '',
            'untagged_item_count' => 0,
            'type'                => 1,
            'level'               => 0,
        ));

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $records
        ));
    }

    public function executeGetSubProjectElementList(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $project = ProjectStructureTable::getInstance()->find($request->getParameter('pid')) and
            $bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('bill_id'))
        );

        $pdo = $project->getTable()->getConnection()->getDbh();

        $elements = DoctrineQuery::create()->select('e.id, e.description')
            ->from('BillElement e')
            ->where('e.project_structure_id = ?', $bill->id)
            ->addOrderBy('e.priority ASC')
            ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
            ->execute();

        $stmt = $pdo->prepare("SELECT e.id, COUNT(*)
            FROM " . BillItemTable::getInstance()->getTableName() . " i
            JOIN " . BillElementTable::getInstance()->getTableName() . " e on e.id = i.element_id
            WHERE e.project_structure_id = {$bill->id}
            AND i.type <> " . BillItem::TYPE_HEADER . "
            AND i.type <> " . BillItem::TYPE_HEADER_N . "
            AND i.tender_origin_id IS NULL
            AND e.deleted_at IS NULL
            AND i.deleted_at IS NULL
            GROUP BY e.id");

        $stmt->execute();

        $untaggedBillItemCount = $stmt->fetch(PDO::FETCH_KEY_PAIR);

        foreach($elements as $key => $element)
        {
            $elements[ $key ]['untagged_item_count'] = $untaggedBillItemCount[ $element['id'] ] ?? 0;
        }

        $defaultLastRow = array(
            'id'          => Constants::GRID_LAST_ROW,
            'description' => '',
        );

        array_push($elements, $defaultLastRow);

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $elements
        ));
    }

    public function executeGetSubProjectItemList(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $project = ProjectStructureTable::getInstance()->find($request->getParameter('pid')) and
            $bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('bill_id')) and
            $element = Doctrine_Core::getTable('BillElement')->find($request->getParameter('element_id'))
        );

        $pdo = $project->getTable()->getConnection()->getDbh();

        $billOriginInfo = ProjectStructureTable::extractOriginId($bill->tender_origin_id);

        $mainProject = ProjectStructureTable::getInstance()->find($billOriginInfo['project_id']);

        $stmt = $pdo->prepare("SELECT i.id, i.description, uom.id AS uom_id, uom.symbol AS uom_symbol, i.type, i.lft, i.level, i.tender_origin_id
            FROM " . BillItemTable::getInstance()->getTableName() . " i
            JOIN " . BillElementTable::getInstance()->getTableName() . " e on e.id = i.element_id
            LEFT JOIN " . UnitOfMeasurementTable::getInstance()->getTableName() . " uom on uom.id = i.uom_id
            WHERE e.id = {$element->id}
            AND e.deleted_at IS NULL
            AND i.deleted_at IS NULL
            ORDER BY i.priority, i.lft, i.level ASC;");

        $stmt->execute();

        $billItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $form = new BaseForm();

        foreach($billItems as $key => $billItem)
        {
            $billItems[ $key ]['tagged_to'] = null;

            if( ! empty( $billItem['tender_origin_id'] ) )
            {
                $originInfo = ProjectStructureTable::extractOriginId($billItem['tender_origin_id']);

                $billItems[ $key ]['tagged_to'] = $originInfo['origin_id'];
            }

            $billItems[ $key ]['_csrf_token'] = $form->getCSRFToken();
        }

        $billItems[] = [
            'id'          => Constants::GRID_LAST_ROW,
            'description' => null,
            'uom_id'      => '-1',
            'uom_symbol'  => '',
        ];

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $billItems
        ));
    }

    public function executeGetSubProjectVariationOrderList(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $project = ProjectStructureTable::getInstance()->find($request->getParameter('pid'))
        );

        $pdo = $project->getTable()->getConnection()->getDbh();

        $stmt = $pdo->prepare("SELECT vo.id, vo.description
            FROM " . VariationOrderTable::getInstance()->getTableName() . " vo
            WHERE vo.project_structure_id = {$project->id}
            AND vo.is_approved = TRUE
            AND vo.deleted_at IS NULL
            ORDER BY vo.priority ASC;");

        $stmt->execute();

        $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmt = $pdo->prepare("SELECT vo.id, COUNT(*)
            FROM " . VariationOrderItemTable::getInstance()->getTableName() . " i
            JOIN " . VariationOrderTable::getInstance()->getTableName() . " vo on vo.id = i.variation_order_id
            WHERE vo.project_structure_id = {$project->id}
            AND i.type = " . VariationOrderItem::TYPE_WORK_ITEM . "
            AND i.tender_origin_id IS NULL
            AND vo.deleted_at IS NULL
            AND i.deleted_at IS NULL
            GROUP BY vo.id");

        $stmt->execute();

        $untaggedItemCount = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

        foreach($records as $key => $record)
        {
            $records[ $key ]['untagged_item_count'] = $untaggedItemCount[ $record['id'] ] ?? 0;
        }

        $defaultLastRow = array(
            'id'          => Constants::GRID_LAST_ROW,
            'description' => '',
        );

        array_push($records, $defaultLastRow);

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $records
        ));
    }

    public function executeGetSubProjectVariationOrderItemList(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $project = ProjectStructureTable::getInstance()->find($request->getParameter('pid')) and
            $variationOrder = Doctrine_Core::getTable('VariationOrder')->find($request->getParameter('vo_id'))
        );

        $pdo = $project->getTable()->getConnection()->getDbh();

        $mainProject = ProjectStructureTable::getParentProject($project);

        $stmt = $pdo->prepare("SELECT i.id, i.description, i.type, i.lft, i.level, i.tender_origin_id
            FROM " . VariationOrderItemTable::getInstance()->getTableName() . " i
            JOIN " . VariationOrderTable::getInstance()->getTableName() . " vo on vo.id = i.variation_order_id
            WHERE vo.id = {$variationOrder->id}
            AND vo.deleted_at IS NULL
            AND i.deleted_at IS NULL
            ORDER BY i.priority, i.lft, i.level ASC;");

        $stmt->execute();

        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $form = new BaseForm();

        foreach($items as $key => $item)
        {
            $items[ $key ]['tagged_to'] = null;

            if( ! empty( $item['tender_origin_id'] ) )
            {
                $originInfo = ProjectStructureTable::extractOriginId($item['tender_origin_id']);

                $items[ $key ]['tagged_to'] = $originInfo['origin_id'];
            }

            $items[ $key ]['_csrf_token'] = $form->getCSRFToken();
        }

        $items[] = [
            'id'          => Constants::GRID_LAST_ROW,
            'description' => null,
        ];

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $items
        ));
    }

    public function executeGetMainProjectBillList(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $subProject = ProjectStructureTable::getInstance()->find($request->getParameter('sub_project')) and
            $project = ProjectStructureTable::getInstance()->find($request->getParameter('pid'))
        );

        $pdo = $project->getTable()->getConnection()->getDbh();

        $stmt = $pdo->prepare("SELECT b.id, b.title as description, b.type, b.level
	        FROM " . ProjectStructureTable::getInstance()->getTableName() . " b
	        WHERE b.root_id = {$project->id}
	        AND b.id != b.root_id
	        AND b.deleted_at IS NULL
	        ORDER BY b.lft asc");

        $stmt->execute();

        $records     = $stmt->fetchAll(PDO::FETCH_ASSOC);

        array_push($records, array(
            'id'                  => Constants::GRID_LAST_ROW,
            'description'         => "",
            'type'                => - 1,
            'level'               => 0,
            'untagged_item_count' => 0,
        ));

        array_push($records, array(
            'id'                  => PostContractClaim::TYPE_VARIATION_ORDER_TEXT,
            'description'         => PostContractClaim::TYPE_VARIATION_ORDER_TEXT,
            'untagged_item_count' => 0,
            'type'                => PostContractClaim::TYPE_VARIATION_ORDER,
            'level'               => 0,
        ));

        array_push($records, array(
            'id'                  => -999,
            'description'         => '',
            'untagged_item_count' => 0,
            'type'                => 1,
            'level'               => 0,
        ));

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $records,
        ));
    }

    public function executeGetMainProjectElementList(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $subProject = ProjectStructureTable::getInstance()->find($request->getParameter('sub_project')) and
            $project = ProjectStructureTable::getInstance()->find($request->getParameter('pid')) and
            $bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('bill_id'))
        );

        $pdo = ProjectStructureTable::getInstance()->getConnection()->getDbh();

        $elements = DoctrineQuery::create()->select('e.id, e.description')
            ->from('BillElement e')
            ->where('e.project_structure_id = ?', $bill->id)
            ->addOrderBy('e.priority ASC')
            ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
            ->execute();

        $defaultLastRow = array(
            'id'                  => Constants::GRID_LAST_ROW,
            'description'         => '',
            'untagged_item_count' => 0,
        );

        array_push($elements, $defaultLastRow);

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $elements
        ));
    }

    public function executeGetMainProjectItemList(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $subProject = ProjectStructureTable::getInstance()->find($request->getParameter('sub_project')) and
            $project = ProjectStructureTable::getInstance()->find($request->getParameter('pid')) and
            $bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('bill_id')) and
            $element = Doctrine_Core::getTable('BillElement')->find($request->getParameter('element_id'))
        );

        $pdo = $project->getTable()->getConnection()->getDbh();

        $stmt = $pdo->prepare("SELECT i.id, i.description, uom.id AS uom_id, uom.symbol AS uom_symbol, i.type, i.lft, i.level
            FROM " . BillItemTable::getInstance()->getTableName() . " i
            JOIN " . BillElementTable::getInstance()->getTableName() . " e on e.id = i.element_id
            LEFT JOIN " . UnitOfMeasurementTable::getInstance()->getTableName() . " uom on uom.id = i.uom_id
            WHERE e.id = {$element->id}
            AND e.deleted_at IS NULL
            AND i.deleted_at IS NULL
            ORDER BY i.priority, i.lft, i.level ASC;");

        $stmt->execute();

        $billItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $subProjectItemRates = BudgetReport::getSubProjectItemRates($project);

        $form = new BaseForm();

        foreach($billItems as $key => $billItem)
        {
            $billItems[ $key ]['tagged_to'] = null;

            if( isset( $subProjectItemRates[ $billItem['id'] ][ $subProject->id ] ) )
            {
                $subProjectBillItemId = $subProjectItemRates[ $billItem['id'] ][ $subProject->id ]['id'];

                $billItems[ $key ]['tagged_to'] = $subProjectBillItemId;
            }

            $billItems[ $key ]['_csrf_token'] = $form->getCSRFToken();
        }

        $billItems[] = [
            'id'          => Constants::GRID_LAST_ROW,
            'description' => null,
            'uom_id'      => '-1',
            'uom_symbol'  => '',
        ];

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $billItems
        ));
    }

    public function executeGetMainProjectVariationOrderList(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $subProject = ProjectStructureTable::getInstance()->find($request->getParameter('sub_project')) and
            $project = ProjectStructureTable::getInstance()->find($request->getParameter('pid'))
        );

        $pdo = ProjectStructureTable::getInstance()->getConnection()->getDbh();

        $stmt = $pdo->prepare("SELECT vo.id, vo.description
            FROM " . VariationOrderTable::getInstance()->getTableName() . " vo
            WHERE vo.project_structure_id = {$project->id}
            AND vo.is_approved = TRUE
            AND vo.deleted_at IS NULL
            ORDER BY vo.priority ASC;");

        $stmt->execute();

        $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $defaultLastRow = array(
            'id'          => Constants::GRID_LAST_ROW,
            'description' => '',
        );

        array_push($records, $defaultLastRow);

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $records
        ));
    }

    public function executeGetMainProjectVariationOrderItemList(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $subProject = ProjectStructureTable::getInstance()->find($request->getParameter('sub_project')) and
            $project = ProjectStructureTable::getInstance()->find($request->getParameter('pid')) and
            $variationOrder = Doctrine_Core::getTable('VariationOrder')->find($request->getParameter('vo_id'))
        );

        $pdo = $project->getTable()->getConnection()->getDbh();

        $stmt = $pdo->prepare("SELECT i.id, i.description, i.type, i.lft, i.level
            FROM " . VariationOrderItemTable::getInstance()->getTableName() . " i
            JOIN " . VariationOrderTable::getInstance()->getTableName() . " vo on vo.id = i.variation_order_id
            WHERE vo.id = {$variationOrder->id}
            AND vo.deleted_at IS NULL
            AND i.deleted_at IS NULL
            ORDER BY i.priority, i.lft, i.level ASC;");

        $stmt->execute();

        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $subProjectItemRates = BudgetReport::getSubProjectVariationOrderItemRates($project);

        $form = new BaseForm();

        foreach($items as $key => $item)
        {
            $items[ $key ]['tagged_to'] = null;

            if( isset( $subProjectItemRates[ $item['id'] ][ $subProject->id ] ) )
            {
                $subProjectItemId = $subProjectItemRates[ $item['id'] ][ $subProject->id ]['id'];

                $items[ $key ]['tagged_to'] = $subProjectItemId;
            }

            $items[ $key ]['_csrf_token'] = $form->getCSRFToken();
        }

        $items[] = [
            'id'          => Constants::GRID_LAST_ROW,
            'description' => null,
        ];

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $items
        ));
    }

    public function executeTagBillItem(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $mainProjectBillItem = Doctrine_Core::getTable('BillItem')->find($request->getParameter('main_project_item_id')) and
            $subProjectBillItem = Doctrine_Core::getTable('BillItem')->find($request->getParameter('sub_project_item_id')) and
            ProjectStructureTable::getParentProject($subProjectBillItem->Element->ProjectStructure->getRoot())->id == $mainProjectBillItem->Element->ProjectStructure->root_id
        );

        $pdo = Doctrine_Core::getTable('BillItem')->getConnection()->getDbh();

        try
        {
            $buildspaceId = sfConfig::get('app_register_buildspace_id');

            $tenderOriginId = ProjectStructureTable::generateTenderOriginId($buildspaceId, $mainProjectBillItem->id, $mainProjectBillItem->Element->project_structure_id);

            $stmt = $pdo->prepare("UPDATE " . BillItemTable::getInstance()->getTableName() . "
            	SET tender_origin_id = :tenderOriginId
            	WHERE id = :id");

            $stmt->execute(array( 'tenderOriginId' => $tenderOriginId, 'id' => $subProjectBillItem->id ));

            $success = true;
            $error   = null;
        }
        catch(Exception $e)
        {
            $items   = array();
            $success = false;
            $error   = $e->getMessage();
        }

        return $this->renderJson(array( 'success' => $success, 'error' => $error ));
    }

    public function executeTagVariationOrderItem(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $mainProjectItem = Doctrine_Core::getTable('VariationOrderItem')->find($request->getParameter('main_project_item_id')) and
            $subProjectItem = Doctrine_Core::getTable('VariationOrderItem')->find($request->getParameter('sub_project_item_id')) and
            ProjectStructureTable::getParentProject($subProjectItem->VariationOrder->ProjectStructure->getRoot())->id == $mainProjectItem->VariationOrder->ProjectStructure->root_id
        );

        $pdo = Doctrine_Core::getTable('VariationOrderItem')->getConnection()->getDbh();

        try
        {
            $buildspaceId = sfConfig::get('app_register_buildspace_id');

            $tenderOriginId = ProjectStructureTable::generateTenderOriginId($buildspaceId, $mainProjectItem->id, $mainProjectItem->VariationOrder->project_structure_id);

            $stmt = $pdo->prepare("UPDATE " . VariationOrderItemTable::getInstance()->getTableName() . "
                SET tender_origin_id = :tenderOriginId
                WHERE id = :id");

            $stmt->execute(array( 'tenderOriginId' => $tenderOriginId, 'id' => $subProjectItem->id ));

            $success = true;
            $error   = null;
        }
        catch(Exception $e)
        {
            $items   = array();
            $success = false;
            $error   = $e->getMessage();
        }

        return $this->renderJson(array( 'success' => $success, 'error' => $error ));
    }

    public function executeUntagBillItem(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $subProject = ProjectStructureTable::getInstance()->find($request->getParameter('sub_project')) and
            $mainProjectBillItem = Doctrine_Core::getTable('BillItem')->find($request->getParameter('main_project_item_id'))
        );

        $project = $mainProjectBillItem->Element->ProjectStructure->getRoot();

        $subProjectItemRates = BudgetReport::getSubProjectItemRates($project);

        try
        {
            $pdo = Doctrine_Core::getTable('BillItem')->getConnection()->getDbh();

            $subProjectBillItemId = $subProjectItemRates[ $mainProjectBillItem['id'] ][ $subProject['id'] ]['id'];

            $stmt = $pdo->prepare("UPDATE " . BillItemTable::getInstance()->getTableName() . "
            	SET tender_origin_id = NULL
            	WHERE id = :id");

            $stmt->execute(array( 'id' => $subProjectBillItemId ));

            $success = true;
            $error   = null;
        }
        catch(Exception $e)
        {
            $items   = array();
            $success = false;
            $error   = $e->getMessage();
        }

        return $this->renderJson(array( 'success' => $success, 'error' => $error ));
    }

    public function executeUntagVariationOrderItem(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $subProject = ProjectStructureTable::getInstance()->find($request->getParameter('sub_project')) and
            $mainProjectItem = Doctrine_Core::getTable('VariationOrderItem')->find($request->getParameter('main_project_item_id'))
        );

        $project = $mainProjectItem->VariationOrder->ProjectStructure->getRoot();

        $subProjectItemRates = BudgetReport::getSubProjectVariationOrderItemRates($project);

        try
        {
            $pdo = Doctrine_Core::getTable('VariationOrderItem')->getConnection()->getDbh();

            $subProjectVariationOrderItemId = $subProjectItemRates[ $mainProjectItem['id'] ][ $subProject['id'] ]['id'];

            $stmt = $pdo->prepare("UPDATE " . VariationOrderItemTable::getInstance()->getTableName() . "
                SET tender_origin_id = NULL
                WHERE id = :id");

            $stmt->execute(array( 'id' => $subProjectVariationOrderItemId ));

            $success = true;
            $error   = null;
        }
        catch(Exception $e)
        {
            $items   = array();
            $success = false;
            $error   = $e->getMessage();
        }

        return $this->renderJson(array( 'success' => $success, 'error' => $error ));
    }

    public function executeGetProjectsInformation(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $refProject = ProjectStructureTable::getInstance()->find($request->getParameter('pid'))
        );

        $mainProject = $refProject;

        $subProjectData = null;

        if( $refProject->MainInformation->getEProjectProject()->isSubProject() )
        {
            $subProject = $refProject;

            $subProjectData = array(
                'id'    => $subProject->id,
                'title' => $subProject->title,
            );

            $mainProject = ProjectStructureTable::getParentProject($subProject);
        }

        $mainProjectData = array(
            'id'    => $mainProject->id,
            'title' => $mainProject->title,
        );

        return $this->renderJson(array( 'mainProject' => $mainProjectData, 'subProject' => $subProjectData ));
    }

    public function executeGetTaggedSubProjectItemInfo(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() AND
            $taggedItem = BillItemTable::getInstance()->find($request->getParameter('id'))
        );

        $pdo = BillItemTable::getInstance()->getConnection()->getDbh();

        $stmt = $pdo->prepare("SELECT i.id, i.description, i.type, i.level
            FROM " . BillItemTable::getInstance()->getTableName() . " ref
            JOIN " . BillItemTable::getInstance()->getTableName() . " i ON i.lft BETWEEN ref.lft AND ref.rgt
            WHERE ref.id = {$taggedItem->id}
            AND i.root_id = ref.root_id
            AND i.deleted_at IS NULL
            ORDER BY i.priority, i.lft, i.level ASC;");

        $stmt->execute();

        $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $element = $taggedItem->Element;
        $bill    = $element->ProjectStructure;

        $billElementRecord = array(
            'id'          => 'bill-' . $bill['id'] . '-elem' . $element['id'],
            'description' => $bill['title'] . " > " . $element['description'],
            'type'        => -1,
            'level'       => 0,
        );

        array_unshift($records, $billElementRecord);

        $records[] = [
            'id'          => Constants::GRID_LAST_ROW,
            'description' => '',
            'type'        => BillItem::TYPE_WORK_ITEM,
            'level'       => 0,
        ];

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $records,
        ));
    }

    public function executeGetTaggedSubProjectVariationOrderItemInfo(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() AND
            $taggedItem = VariationOrderItemTable::getInstance()->find($request->getParameter('id'))
        );

        $pdo = BillItemTable::getInstance()->getConnection()->getDbh();

        $stmt = $pdo->prepare("SELECT i.id, i.description, i.type, i.level
            FROM " . VariationOrderItemTable::getInstance()->getTableName() . " ref
            JOIN " . VariationOrderItemTable::getInstance()->getTableName() . " i ON i.lft BETWEEN ref.lft AND ref.rgt
            WHERE ref.id = {$taggedItem->id}
            AND i.root_id = ref.root_id
            AND i.deleted_at IS NULL
            ORDER BY i.priority, i.lft, i.level ASC;");

        $stmt->execute();

        $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $variationOrder = $taggedItem->VariationOrder;

        $variationOrderRecord = array(
            'id'          => 'vo-' . $variationOrder['id'],
            'description' => $variationOrder['description'],
            'type'        => -1,
            'level'       => 0,
        );

        array_unshift($records, $variationOrderRecord);

        $records[] = [
            'id'          => Constants::GRID_LAST_ROW,
            'description' => '',
            'type'        => BillItem::TYPE_WORK_ITEM,
            'level'       => 0,
        ];

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $records,
        ));
    }
}
