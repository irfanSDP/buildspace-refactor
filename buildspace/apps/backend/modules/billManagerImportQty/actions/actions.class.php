<?php

/**
 * billManagerImportQty actions.
 *
 * @package    buildspace
 * @subpackage billManagerImportQty
 * @author     1337 developers
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class billManagerImportQtyActions extends BaseActions {

    public function executeGetProjects(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest());

        $projects = DoctrineQuery::create()->select('p.id, p.title, i.id, s.id, s.name, c.country')
            ->from('ProjectStructure p')
            ->leftJoin('p.MainInformation i')
            ->leftJoin('i.Subregions s')
            ->leftJoin('s.Regions c')
            ->where('p.id = p.root_id')
            ->andWhere('p.type = ?', ProjectStructure::TYPE_ROOT)
            ->addOrderBy('p.priority ASC')
            ->fetchArray();

        foreach ( $projects as $key => $project )
        {
            if ( $project['MainInformation']['Subregions'] )
            {
                $projects[$key]['state'] = $project['MainInformation']['Subregions']['name'];

                $projects[$key]['country'] = $project['MainInformation']['Subregions']['Regions']['country'];
            }

            unset( $projects[$key]['MainInformation'], $projects[$key]['Subregions'], $project );
        }

        $defaultLastRow = array(
            'id'      => Constants::GRID_LAST_ROW,
            'title'   => '',
            'state'   => '',
            'country' => ''
        );

        array_push($projects, $defaultLastRow);

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $projects
        ));
    }

    public function executeGetBillList(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest() and $project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('pid')));

        $records = DoctrineQuery::create()->select('s.id, s.title, s.type, s.level')
            ->from('ProjectStructure s')
            ->where('s.lft > ? AND s.rgt < ?', array( $project->lft, $project->rgt ))
            ->andWhere('s.root_id = ?', $project->id)
            ->andWhere('s.type <= ?', ProjectStructure::TYPE_BILL)
            ->addOrderBy('s.lft ASC')
            ->fetchArray();

        $count = 0;

        foreach ( $records as $key => $record )
        {
            $records[$key]['level'] = $record['level'] - 1;

            if ( $record['type'] == ProjectStructure::TYPE_BILL )
            {
                $count = $record['type'] == ProjectStructure::TYPE_BILL ? $count + 1 : $count;

                $billType = DoctrineQuery::create()->select('t.id, t.type, t.status')
                    ->from('BillType t')
                    ->where('t.project_structure_id = ?', $record['id'])
                    ->limit(1)
                    ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
                    ->fetchOne();

                $records[$key]['bill_type'] = $billType['type'];
            }
            else
            {
                $records[$key]['bill_type'] = - 1;
            }

            $records[$key]['count'] = $record['type'] == ProjectStructure::TYPE_BILL ? $count : null;
        }

        array_push($records, array(
            'id'        => Constants::GRID_LAST_ROW,
            'count'     => null,
            'title'     => '',
            'type'      => ProjectStructure::TYPE_LEVEL,
            'bill_type' => - 1,
            'level'     => 0
        ));

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $records
        ));
    }

    public function executeGetBillColumnSettingList(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest() and $bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('bid')));

        $records = DoctrineQuery::create()->select('c.id, c.name, c.remeasurement_quantity_enabled')
            ->from('BillColumnSetting c')
            ->where('c.project_structure_id = ?', $bill->id)
            ->addOrderBy('c.id ASC')
            ->fetchArray();

        array_push($records, array(
            'id'                             => Constants::GRID_LAST_ROW,
            'name'                           => '',
            'remeasurement_quantity_enabled' => false
        ));

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $records
        ));
    }

    public function executeGetElementList(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest() and $billColumnSetting = Doctrine_Core::getTable('BillColumnSetting')->find($request->getParameter('cid')));

        $elements = DoctrineQuery::create()->select('e.id, e.description')
            ->from('BillElement e')
            ->andWhere('e.project_structure_id = ?', $billColumnSetting->project_structure_id)
            ->addOrderBy('e.priority ASC')
            ->fetchArray();

        $defaultLastRow = array(
            'id'          => Constants::GRID_LAST_ROW,
            'description' => ''
        );

        array_push($elements, $defaultLastRow);

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $elements
        ));
    }

    public function executeGetItemList(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $billColumnSetting = Doctrine_Core::getTable('BillColumnSetting')->find($request->getParameter('cid')) and
            $element = Doctrine_Core::getTable('BillElement')->find($request->getParameter('eid'))
        );

        $pdo = $element->getTable()->getConnection()->getDbh();

        $formulatedColumns = array();

        $stmt = $pdo->prepare("SELECT i.id, i.description, i.type, i.lft, i.level,
            uom.id AS uom_id, uom.symbol AS uom_symbol,
            pc.supply_rate AS pc_supply_rate, pc.wastage_percentage AS pc_wastage_percentage,
            pc.wastage_amount AS pc_wastage_amount, pc.labour_for_installation AS pc_labour_for_installation,
            pc.other_cost AS pc_other_cost, pc.profit_percentage AS pc_profit_percentage,
            pc.profit_amount AS pc_profit_amount, pc.total AS pc_total
            FROM " . BillItemTable::getInstance()->getTableName() . " i
            LEFT JOIN " . UnitOfMeasurementTable::getInstance()->getTableName() . " uom ON i.uom_id = uom.id AND uom.deleted_at IS NULL
            LEFT JOIN " . BillItemPrimeCostRateTable::getInstance()->getTableName() . " pc ON i.id = pc.bill_item_id
            LEFT JOIN " . ProjectRevisionTable::getInstance()->getTableName() . " r ON i.project_revision_id = r.id
            WHERE i.element_id = " . $element->id . "
            AND i.deleted_at IS NULL AND i.project_revision_deleted_at IS NULL
            AND pc.deleted_at IS NULL AND r.deleted_at IS NULL ORDER BY i.priority, i.lft, i.level");

        $stmt->execute();

        $billItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmt = $pdo->prepare("SELECT DISTINCT r.bill_item_id, fc.id, fc.column_name, fc.value, fc.final_value, fc.linked, fc.has_build_up
            FROM " . BillItemTypeReferenceFormulatedColumnTable::getInstance()->getTableName() . " fc
            JOIN " . BillItemTypeReferenceTable::getInstance()->getTableName() . " r ON fc.relation_id = r.id
            JOIN " . BillItemTable::getInstance()->getTableName() . " i ON r.bill_item_id = i.id
            WHERE i.element_id = " . $element->id . " AND r.bill_column_setting_id = " . $billColumnSetting->id . "
            AND r.include IS TRUE AND fc.final_value <> 0
            AND r.deleted_at IS NULL AND fc.deleted_at IS NULL
            AND i.deleted_at IS NULL AND i.project_revision_deleted_at IS NULL");

        $stmt->execute();

        $quantities = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ( $quantities as $quantity )
        {
            if ( !array_key_exists($quantity['bill_item_id'], $formulatedColumns) )
            {
                $formulatedColumns[$quantity['bill_item_id']] = array();
            }

            array_push($formulatedColumns[$quantity['bill_item_id']], $quantity);

            unset( $itemFormulatedColumn );
        }

        $form = new BaseForm();

        foreach ( $billItems as $key => $billItem )
        {
            $billItems[$key]['type']            = (string) $billItem['type'];
            $billItems[$key]['uom_id']          = (string) $billItem['uom_id'];
            $billItems[$key]['uom_symbol']      = $billItem['uom_id'] > 0 ? $billItem['uom_symbol'] : '---';
            $billItems[$key]['_csrf_token']     = $form->getCSRFToken();
            $billItems[$key]['quantity_import'] = - 1;

            $billItems[$key][BillItemTypeReference::FORMULATED_COLUMN_QTY_PER_UNIT . '-final_value']        = 0;
            $billItems[$key][BillItemTypeReference::FORMULATED_COLUMN_QTY_PER_UNIT . '-value']              = '';
            $billItems[$key][BillItemTypeReference::FORMULATED_COLUMN_QTY_PER_UNIT . '-has_cell_reference'] = false;
            $billItems[$key][BillItemTypeReference::FORMULATED_COLUMN_QTY_PER_UNIT . '-has_formula']        = false;
            $billItems[$key][BillItemTypeReference::FORMULATED_COLUMN_QTY_PER_UNIT . '-linked']             = false;
            $billItems[$key][BillItemTypeReference::FORMULATED_COLUMN_QTY_PER_UNIT . '-has_build_up']       = false;

            if ( $billColumnSetting->remeasurement_quantity_enabled )
            {
                $billItems[$key][BillItemTypeReference::FORMULATED_COLUMN_QTY_PER_UNIT_REMEASUREMENT . '-final_value']        = 0;
                $billItems[$key][BillItemTypeReference::FORMULATED_COLUMN_QTY_PER_UNIT_REMEASUREMENT . '-value']              = '';
                $billItems[$key][BillItemTypeReference::FORMULATED_COLUMN_QTY_PER_UNIT_REMEASUREMENT . '-has_cell_reference'] = false;
                $billItems[$key][BillItemTypeReference::FORMULATED_COLUMN_QTY_PER_UNIT_REMEASUREMENT . '-has_formula']        = false;
                $billItems[$key][BillItemTypeReference::FORMULATED_COLUMN_QTY_PER_UNIT_REMEASUREMENT . '-linked']             = false;
                $billItems[$key][BillItemTypeReference::FORMULATED_COLUMN_QTY_PER_UNIT_REMEASUREMENT . '-has_build_up']       = false;

                $billItems[$key]['quantity_remeasurement_import'] = - 1;

            }

            if ( array_key_exists($billItem['id'], $formulatedColumns) )
            {
                foreach ( $formulatedColumns[$billItem['id']] as $formulatedColumn )
                {
                    $columnName                                           = $formulatedColumn['column_name'];
                    $billItems[$key][$columnName . '-final_value']        = $formulatedColumn['final_value'];
                    $billItems[$key][$columnName . '-value']              = $formulatedColumn['value'];
                    $billItems[$key][$columnName . '-has_build_up']       = $formulatedColumn['has_build_up'];
                    $billItems[$key][$columnName . '-has_cell_reference'] = false;
                    $billItem[$columnName . '-linked']                    = $formulatedColumn['linked'];
                    $billItems[$key][$columnName . '-has_formula']        = $formulatedColumn['value'] != $formulatedColumn['final_value'] ? true : false;

                    if ( $columnName == BillItemTypeReference::FORMULATED_COLUMN_QTY_PER_UNIT )
                    {
                        $billItems[$key]['quantity_import'] = $formulatedColumn['id'];
                    }
                    elseif ( $columnName == BillItemTypeReference::FORMULATED_COLUMN_QTY_PER_UNIT_REMEASUREMENT )
                    {
                        $billItems[$key]['quantity_remeasurement_import'] = $formulatedColumn['id'];
                    }
                }
            }

            unset( $formulatedColumns[$billItem['id']], $billItem );
        }

        $defaultLastRow = array(
            'id'              => Constants::GRID_LAST_ROW,
            'description'     => '',
            'uom_symbol'      => '',
            '_csrf_token'     => $form->getCSRFToken(),
            'quantity_import' => - 1
        );

        $defaultLastRow[BillItemTypeReference::FORMULATED_COLUMN_QTY_PER_UNIT . '-final_value']        = 0;
        $defaultLastRow[BillItemTypeReference::FORMULATED_COLUMN_QTY_PER_UNIT . '-value']              = 0;
        $defaultLastRow[BillItemTypeReference::FORMULATED_COLUMN_QTY_PER_UNIT . '-linked']             = false;
        $defaultLastRow[BillItemTypeReference::FORMULATED_COLUMN_QTY_PER_UNIT . '-has_build_up']       = false;
        $defaultLastRow[BillItemTypeReference::FORMULATED_COLUMN_QTY_PER_UNIT . '-has_cell_reference'] = false;
        $defaultLastRow[BillItemTypeReference::FORMULATED_COLUMN_QTY_PER_UNIT . '-has_formula']        = false;

        if ( $billColumnSetting->remeasurement_quantity_enabled )
        {
            $defaultLastRow[BillItemTypeReference::FORMULATED_COLUMN_QTY_PER_UNIT_REMEASUREMENT . '-final_value']        = 0;
            $defaultLastRow[BillItemTypeReference::FORMULATED_COLUMN_QTY_PER_UNIT_REMEASUREMENT . '-value']              = 0;
            $defaultLastRow[BillItemTypeReference::FORMULATED_COLUMN_QTY_PER_UNIT_REMEASUREMENT . '-linked']             = false;
            $defaultLastRow[BillItemTypeReference::FORMULATED_COLUMN_QTY_PER_UNIT_REMEASUREMENT . '-has_build_up']       = false;
            $defaultLastRow[BillItemTypeReference::FORMULATED_COLUMN_QTY_PER_UNIT_REMEASUREMENT . '-has_cell_reference'] = false;
            $defaultLastRow[BillItemTypeReference::FORMULATED_COLUMN_QTY_PER_UNIT_REMEASUREMENT . '-has_formula']        = false;

            $defaultLastRow['quantity_remeasurement_import'] = - 1;
        }

        array_push($billItems, $defaultLastRow);

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $billItems
        ));
    }

    public function executeImport(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $targetBillItem = Doctrine_Core::getTable('BillItem')->find($request->getParameter('tid')) and
            $targetBillColumnSetting = Doctrine_Core::getTable('BillColumnSetting')->find($request->getParameter('tcid')) and
            $sourceBillItem = Doctrine_Core::getTable('BillItem')->find($request->getParameter('sid')) and
            $sourceBillItemTypeReferenceFormulatedColumn = Doctrine_Core::getTable('BillItemTypeReferenceFormulatedColumn')->find($request->getParameter('rid')) and
            $request->hasParameter('type')
        );

        $errorMsg = null;

        try
        {
            $targetBillItem->importQuantityFromItemByBillItemTypeReference($targetBillColumnSetting, $sourceBillItem, $sourceBillItemTypeReferenceFormulatedColumn, $request->getParameter('type'));

            $targetBillItem->refresh(true);

            $targetBillItem->updateBillItemTotalColumns();

            $success = true;
        }
        catch (Exception $e)
        {
            $errorMsg = $e->getMessage();
            $success  = false;
        }

        return $this->renderJson(array( 'success' => $success, 'errorMsg' => $errorMsg ));
    }

}