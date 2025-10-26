<?php

/**
 * billManagerImportRate actions.
 *
 * @package    buildspace
 * @subpackage billManagerImportRate
 * @author     1337 developers
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class billManagerImportRateActions extends BaseActions {

    public function executeGetScheduleOfRates(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest());

        $scheduleOfRates = DoctrineQuery::create()->select('s.id, s.name')
            ->from('ScheduleOfRate s')
            ->addOrderBy('s.id ASC')
            ->fetchArray();

        array_push($scheduleOfRates, array(
            'id'   => Constants::GRID_LAST_ROW,
            'name' => ''
        ));

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $scheduleOfRates
        ));
    }

    public function executeGetScheduleOfRateTrades(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest() and $scheduleOfRate = Doctrine_Core::getTable('ScheduleOfRate')->find($request->getParameter('id')));

        $trades = DoctrineQuery::create()->select('t.id, t.description')
            ->from('ScheduleOfRateTrade t')
            ->andWhere('t.schedule_of_rate_id = ?', $scheduleOfRate->id)
            ->addOrderBy('t.priority ASC')
            ->fetchArray();

        array_push($trades, array(
            'id'          => Constants::GRID_LAST_ROW,
            'description' => ''
        ));

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $trades
        ));
    }

    public function executeGetScheduleOfRateItems(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $trade = Doctrine_Core::getTable('ScheduleOfRateTrade')->find($request->getParameter('id'))
        );

        $pdo                       = $trade->getTable()->getConnection()->getDbh();
        $formulatedColumns         = array();
        $formulatedColumnConstants = Utilities::getAllFormulatedColumnConstants('ScheduleOfRateItem');

        $stmt = $pdo->prepare("SELECT DISTINCT c.id, c.description, c.type, uom.id as uom_id, c.priority, c.lft,
        c.level, c.trade_id, c.level, c.updated_at, c.recalculate_resources_library_status, uom.symbol AS uom_symbol
        FROM " . ScheduleOfRateItemTable::getInstance()->getTableName() . " c
        LEFT JOIN " . UnitOfMeasurementTable::getInstance()->getTableName() . " uom ON c.uom_id = uom.id AND uom.deleted_at IS NULL
        WHERE c.trade_id = " . $trade->id . " AND c.deleted_at IS NULL
        ORDER BY c.priority, c.lft, c.level ASC");

        $stmt->execute();

        $scheduleOfRateItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmt = $pdo->prepare("SELECT ifc.relation_id, ifc.column_name, ifc.final_value, ifc.value, ifc.has_build_up
        FROM " . ScheduleOfRateItemFormulatedColumnTable::getInstance()->getTableName() . " ifc JOIN
        " . ScheduleOfRateItemTable::getInstance()->getTableName() . " i ON i.id = ifc.relation_id
        WHERE i.trade_id = " . $trade->id . " AND ifc.deleted_at IS NULL
        AND i.deleted_at IS NULL AND ifc.final_value <> 0");

        $stmt->execute();
        $itemFormulatedColumns = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ( $itemFormulatedColumns as $itemFormulatedColumn )
        {
            if ( !array_key_exists($itemFormulatedColumn['relation_id'], $formulatedColumns) )
            {
                $formulatedColumns[$itemFormulatedColumn['relation_id']] = array();
            }

            array_push($formulatedColumns[$itemFormulatedColumn['relation_id']], $itemFormulatedColumn);

            unset( $itemFormulatedColumn );
        }

        unset( $itemFormulatedColumns );

        foreach ( $scheduleOfRateItems as $key => $sorItem )
        {
            $scheduleOfRateItems[$key]['type']       = (string) $sorItem['type'];
            $scheduleOfRateItems[$key]['uom_id']     = $sorItem['uom_id'] > 0 ? (string) $sorItem['uom_id'] : '-1';
            $scheduleOfRateItems[$key]['uom_symbol'] = $sorItem['uom_id'] > 0 ? $sorItem['uom_symbol'] : '';

            foreach ( $formulatedColumnConstants as $constant )
            {
                $scheduleOfRateItems[$key][$constant . '-final_value']        = 0;
                $scheduleOfRateItems[$key][$constant . '-value']              = '';
                $scheduleOfRateItems[$key][$constant . '-has_cell_reference'] = false;
                $scheduleOfRateItems[$key][$constant . '-has_formula']        = false;
                $scheduleOfRateItems[$key][$constant . '-has_build_up']       = false;
            }

            if ( array_key_exists($sorItem['id'], $formulatedColumns) )
            {
                foreach ( $formulatedColumns[$sorItem['id']] as $formulatedColumn )
                {
                    $columnName                                                     = $formulatedColumn['column_name'];
                    $scheduleOfRateItems[$key][$columnName . '-final_value']        = $formulatedColumn['final_value'];
                    $scheduleOfRateItems[$key][$columnName . '-value']              = $formulatedColumn['value'];
                    $scheduleOfRateItems[$key][$columnName . '-has_build_up']       = $formulatedColumn['has_build_up'];
                    $scheduleOfRateItems[$key][$columnName . '-has_cell_reference'] = false;
                    $scheduleOfRateItems[$key][$columnName . '-has_formula']        = $formulatedColumn['value'] != $formulatedColumn['final_value'] ? true : false;
                }
            }

            unset( $formulatedColumns[$sorItem['id']], $sorItem );
        }

        $defaultLastRow = array(
            'id'          => Constants::GRID_LAST_ROW,
            'description' => '',
            'type'        => (string) BillItem::TYPE_NOID,
            'uom_id'      => '-1',
            'uom_symbol'  => '',
            'level'       => 0
        );

        foreach ( $formulatedColumnConstants as $constant )
        {
            $defaultLastRow[$constant . '-final_value']        = "";
            $defaultLastRow[$constant . '-value']              = "";
            $defaultLastRow[$constant . '-has_build_up']       = false;
            $defaultLastRow[$constant . '-has_cell_reference'] = false;
            $defaultLastRow[$constant . '-has_formula']        = false;
        }

        array_push($scheduleOfRateItems, $defaultLastRow);

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $scheduleOfRateItems
        ));
    }

    public function executeGetScheduleOfRateBuildUpSummary(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest() and $item = Doctrine_Core::getTable('ScheduleOfRateItem')->find($request->getParameter('id')));

        return $this->renderJson(array(
            'apply_conversion_factor'     => $item->BuildUpRateSummary->apply_conversion_factor,
            'conversion_factor_amount'    => $item->BuildUpRateSummary->conversion_factor_amount,
            'conversion_factor_operator'  => $item->BuildUpRateSummary->conversion_factor_operator,
            'conversion_factor_uom'       => $item->BuildUpRateSummary->conversion_factor_uom_id > 0 ? $item->BuildUpRateSummary->UnitOfMeasurement->symbol : '---',
            'total_cost'                  => $item->BuildUpRateSummary->calculateTotalCost(),
            'total_cost_after_conversion' => $item->BuildUpRateSummary->getTotalCostAfterConversion(),
            'markup'                      => $item->BuildUpRateSummary->markup,
            'final_cost'                  => $item->BuildUpRateSummary->calculateFinalCost()
        ));
    }

    public function executeGetProjectBreakdown(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('id')) and
            $project->type == ProjectStructure::TYPE_ROOT
        );

        $tenderAlternativeProjectStructureIds = [];
        $tenderAlternative = $project->getAwardedTenderAlternative();

        if($tenderAlternative)
        {
            //we set default to -1 so it should return empty query if there is no assigned bill for this tender alternative;
            $tenderAlternativeProjectStructureIds = [-1];
            $tenderAlternativesBills = $tenderAlternative->getAssignedBills();

            if($tenderAlternativesBills)
            {
                $tenderAlternativeProjectStructureIds = array_column($tenderAlternativesBills, 'id');
            }
        }

        $query = DoctrineQuery::create()->select('s.id, s.title, s.type, s.level')
            ->from('ProjectStructure s')
            ->where('s.lft > ? AND s.rgt < ?', array( $project->lft, $project->rgt ))
            ->andWhere('s.root_id = ?', $project->id)
            ->andWhere('s.type <= ?', ProjectStructure::TYPE_BILL);

        if(!empty($tenderAlternativeProjectStructureIds))
        {
            $query->whereIn('s.id', $tenderAlternativeProjectStructureIds);
        }

        $records = $query->addOrderBy('s.lft ASC')
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
            'id'          => Constants::GRID_LAST_ROW,
            'count'       => null,
            'description' => '',
            'type'        => ProjectStructure::TYPE_LEVEL,
            'bill_type'   => - 1,
            'level'       => 0
        ));

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $records
        ));
    }

    public function executeGetElementList(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest() and $bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('id')));

        $elements = DoctrineQuery::create()->select('e.id, e.description')
            ->from('BillElement e')
            ->where('e.project_structure_id = ?', $bill->id)
            ->addOrderBy('e.priority ASC')
            ->fetchArray();

        $form = new BaseForm();

        foreach ( $elements as $key => $element )
        {
            $elements[$key]['_csrf_token'] = $form->getCSRFToken();
        }

        array_push($elements, array(
            'id'          => Constants::GRID_LAST_ROW,
            'description' => ''
        ));

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $elements
        ));
    }

    public function executeGetItemList(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $element = Doctrine_Core::getTable('BillElement')->find($request->getParameter('id'))
        );

        $pdo               = $element->getTable()->getConnection()->getDbh();
        $formulatedColumns = array();

        $stmt = $pdo->prepare("SELECT c.id, c.description, c.type, c.uom_id, c.lft, c.level,
            c.uom_id, uom.symbol AS uom_symbol
            FROM " . BillItemTable::getInstance()->getTableName() . " c
            LEFT JOIN " . UnitOfMeasurementTable::getInstance()->getTableName() . " uom ON c.uom_id = uom.id
            WHERE c.element_id = " . $element->id . "
            AND c.type <> " . BillItem::TYPE_ITEM_PC_RATE . " AND c.type <> " . BillItem::TYPE_ITEM_LUMP_SUM_PERCENT . "
            AND c.type <> " . BillItem::TYPE_ITEM_LUMP_SUM_EXCLUDE . " AND c.type <> " . BillItem::TYPE_NOID . "
            AND c.deleted_at IS NULL AND c.project_revision_deleted_at IS NULL
            AND uom.deleted_at IS NULL ORDER BY c.priority, c.lft, c.level");

        $stmt->execute();

        $billItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmt = $pdo->prepare("SELECT ifc.relation_id, ifc.column_name, ifc.final_value, ifc.value, ifc.linked, ifc.has_build_up
            FROM " . BillItemFormulatedColumnTable::getInstance()->getTableName() . " ifc JOIN
            " . BillItemTable::getInstance()->getTableName() . " i ON i.id = ifc.relation_id
            WHERE i.element_id = " . $element->id . "
            AND i.type <> " . BillItem::TYPE_ITEM_PC_RATE . " AND i.type <> " . BillItem::TYPE_ITEM_LUMP_SUM_PERCENT . "
            AND i.type <> " . BillItem::TYPE_ITEM_LUMP_SUM_EXCLUDE . " AND i.type <> " . BillItem::TYPE_NOID . "
            AND i.project_revision_deleted_at IS NULL AND i.deleted_at IS NULL
            AND ifc.deleted_at IS NULL AND ifc.column_name = '" . BillItem::FORMULATED_COLUMN_RATE . "'
            ORDER BY i.priority, i.lft, i.level");

        $stmt->execute();

        $itemFormulatedColumns = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ( $itemFormulatedColumns as $itemFormulatedColumn )
        {
            if ( !array_key_exists($itemFormulatedColumn['relation_id'], $formulatedColumns) )
            {
                $formulatedColumns[$itemFormulatedColumn['relation_id']] = array();
            }

            array_push($formulatedColumns[$itemFormulatedColumn['relation_id']], $itemFormulatedColumn);

            unset( $itemFormulatedColumn );
        }

        unset( $itemFormulatedColumns );

        foreach ( $billItems as $key => $billItem )
        {
            $billItems[$key]['type']       = (string) $billItem['type'];
            $billItems[$key]['uom_id']     = $billItem['uom_id'] > 0 ? (string) $billItem['uom_id'] : '-1';
            $billItems[$key]['uom_symbol'] = $billItem['uom_id'] > 0 ? $billItem['uom_symbol'] : '';

            $billItems[$key][BillItem::FORMULATED_COLUMN_RATE . '-final_value']        = 0;
            $billItems[$key][BillItem::FORMULATED_COLUMN_RATE . '-value']              = '';
            $billItems[$key][BillItem::FORMULATED_COLUMN_RATE . '-has_cell_reference'] = false;
            $billItems[$key][BillItem::FORMULATED_COLUMN_RATE . '-has_formula']        = false;
            $billItems[$key][BillItem::FORMULATED_COLUMN_RATE . '-linked']             = false;
            $billItems[$key][BillItem::FORMULATED_COLUMN_RATE . '-has_build_up']       = false;

            if ( array_key_exists($billItem['id'], $formulatedColumns) )
            {
                foreach ( $formulatedColumns[$billItem['id']] as $formulatedColumn )
                {
                    $columnName                                           = $formulatedColumn['column_name'];
                    $billItems[$key][$columnName . '-final_value']        = $formulatedColumn['final_value'];
                    $billItems[$key][$columnName . '-value']              = $formulatedColumn['value'];
                    $billItems[$key][$columnName . '-has_build_up']       = $formulatedColumn['has_build_up'];
                    $billItems[$key][$columnName . '-has_cell_reference'] = false;
                    $billItems[$key][$columnName . '-linked']             = $formulatedColumn['linked'];
                    $billItems[$key][$columnName . '-has_formula']        = $formulatedColumn['value'] != $formulatedColumn['final_value'] ? true : false;
                }
            }

            unset( $formulatedColumns[$billItem['id']], $billItem );
        }

        array_push($billItems, array(
            'id'                                                     => Constants::GRID_LAST_ROW,
            'description'                                            => '',
            'type'                                                   => (string) BillItem::TYPE_WORK_ITEM,
            'uom_id'                                                 => '-1',
            'uom_symbol'                                             => '',
            'level'                                                  => 0,
            BillItem::FORMULATED_COLUMN_RATE . '-final_value'        => 0,
            BillItem::FORMULATED_COLUMN_RATE . '-value'              => 0,
            BillItem::FORMULATED_COLUMN_RATE . '-linked'             => false,
            BillItem::FORMULATED_COLUMN_RATE . '-has_build_up'       => false,
            BillItem::FORMULATED_COLUMN_RATE . '-has_cell_reference' => false,
            BillItem::FORMULATED_COLUMN_RATE . '-has_formula'        => false
        ));

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
            $element = Doctrine_Core::getTable('BillElement')->find($request->getParameter('element_id')) and
            $scheduleOfRateItem = Doctrine_Core::getTable('ScheduleOfRateItem')->find($request->getParameter('schedule_of_rate_id'))
        );

        $billItemIds = explode(',', $request->getParameter('ids'));

        try
        {
            $items = array();

            $scheduleOfRateItem->importRateIntoBillItems($billItemIds, $element->id);

            $records = DoctrineQuery::create()->select('c.relation_id, c.value, c.linked, c.has_build_up, c.final_value')
                ->from('BillItemFormulatedColumn c')
                ->whereIn('c.relation_id', $billItemIds)
                ->andWhere('c.column_name = ?', BillItem::FORMULATED_COLUMN_RATE)
                ->fetchArray();

            foreach ( $records as $record )
            {
                $item['id']                                               = $record['relation_id'];
                $item[BillItem::FORMULATED_COLUMN_RATE . '-final_value']  = $record['final_value'];
                $item[BillItem::FORMULATED_COLUMN_RATE . '-value']        = $record['value'];
                $item[BillItem::FORMULATED_COLUMN_RATE . '-linked']       = $record['linked'];
                $item[BillItem::FORMULATED_COLUMN_RATE . '-has_build_up'] = $record['has_build_up'];

                array_push($items, $item);

                unset( $record );
            }

            unset( $records );

            $success = true;
            $error   = null;
        }
        catch (Exception $e)
        {
            $items   = array();
            $success = false;
            $error   = $e->getMessage();
        }

        return $this->renderJson(array( 'items' => $items, 'success' => $success, 'error' => $error ));
    }

}