<?php

/**
 * billManagerImport actions.
 *
 * @package    buildspace
 * @subpackage billManagerImport
 * @author     1337 developers
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class billManagerImportActions extends BaseActions {

    public function executeGetProjectList(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest());

        $projects = DoctrineQuery::create()->select('p.id, p.title, i.id, i.created_at, s.id, s.name, c.country')
            ->from('ProjectStructure p')
            ->leftJoin('p.MainInformation i')
            ->leftJoin('i.Subregions s')
            ->leftJoin('s.Regions c')
            ->where('p.id = p.root_id')
            ->andWhere('p.type = ?', ProjectStructure::TYPE_ROOT)
            ->addOrderBy('p.priority ASC')
            ->fetchArray();

        $form = new BaseForm();

        foreach ( $projects as $key => $project )
        {
            $projects[$key]['_csrf_token'] = $form->getCSRFToken();

            if ( $project['MainInformation']['Subregions'] )
            {
                $projects[$key]['state'] = $project['MainInformation']['Subregions']['name'];

                $projects[$key]['country'] = $project['MainInformation']['Subregions']['Regions']['country'];

                $projects[$key]['created_at'] = date('d/m/Y H:i', strtotime($project['MainInformation']['created_at']));
            }

            unset( $projects[$key]['MainInformation'], $projects[$key]['Subregions'], $project );
        }

        array_push($projects, array(
            'id'         => Constants::GRID_LAST_ROW,
            'title'      => '',
            'state'      => '',
            'country'    => '',
            'created_at' => '-'
        ));

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $projects
        ));
    }

    public function executeGetBillList(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest() and $project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('id')));

        $bills = DoctrineQuery::create()->select('s.id, s.title, s.type, s.level')
            ->from('ProjectStructure s')
            ->where('s.lft >= ? AND s.rgt <= ?', array( $project->lft, $project->rgt ))
            ->andWhere('s.root_id = ?', $project->id)
            ->andWhere('s.type = ?', ProjectStructure::TYPE_BILL)
            ->addOrderBy('s.lft ASC')
            ->fetchArray();

        $form = new BaseForm();

        foreach ( $bills as $key => $bill )
        {
            $bills[$key]['_csrf_token'] = $form->getCSRFToken();

            unset( $bill );
        }

        array_push($bills, array(
            'id'    => Constants::GRID_LAST_ROW,
            'title' => ''
        ));

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $bills
        ));
    }

    public function executeGetLibraryList(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest());

        $libraries = DoctrineQuery::create()->select('l.id, l.name')
            ->from('BQLibrary l')
            ->addOrderBy('l.id ASC')
            ->fetchArray();

        $form = new BaseForm();

        foreach ( $libraries as $key => $library )
        {
            $libraries[$key]['_csrf_token'] = $form->getCSRFToken();
        }

        array_push($libraries, array(
            'id'   => Constants::GRID_LAST_ROW,
            'name' => ''
        ));

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $libraries
        ));
    }

    public function executeGetElementList(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest() and $library = Doctrine_Core::getTable('BQLibrary')->find($request->getParameter('id')));

        $elements = DoctrineQuery::create()->select('e.id, e.description')
            ->from('BQElement e')
            ->andWhere('e.library_id = ?', $library->id)
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

    public function executeGetBillElementList(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest() and $project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('id')));

        $elements = DoctrineQuery::create()->select('e.id, e.description')
            ->from('BillElement e')
            ->andWhere('e.project_structure_id = ?', $project->id)
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
            $element = Doctrine_Core::getTable('BQElement')->find($request->getParameter('id')
            ));

        $pdo               = $element->getTable()->getConnection()->getDbh();
        $formulatedColumns = array();

        $stmt = $pdo->prepare("SELECT DISTINCT c.id, c.description, c.type, uom.id as uom_id, c.priority, c.lft,
        c.level, c.element_id, c.updated_at, uom.symbol AS uom_symbol
        FROM " . BQItemTable::getInstance()->getTableName() . " c
        LEFT JOIN " . UnitOfMeasurementTable::getInstance()->getTableName() . " uom ON c.uom_id = uom.id AND uom.deleted_at IS NULL
        WHERE c.element_id = " . $element->id . " AND c.deleted_at IS NULL
        ORDER BY c.priority, c.lft, c.level");

        $stmt->execute();
        $bqItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmt = $pdo->prepare("SELECT ifc.relation_id, ifc.column_name, ifc.final_value, ifc.value, ifc.has_build_up
        FROM " . BQItemFormulatedColumnTable::getInstance()->getTableName() . " ifc JOIN
        " . BQItemTable::getInstance()->getTableName() . " i ON i.id = ifc.relation_id
        WHERE i.element_id = " . $element->id . " AND ifc.deleted_at IS NULL
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

        foreach ( $bqItems as $key => $bqItem )
        {
            $bqItems[$key]['type']       = (string) $bqItem['type'];
            $bqItems[$key]['uom_id']     = (string) $bqItem['uom_id'];
            $bqItems[$key]['uom_symbol'] = $bqItem['uom_id'] > 0 ? $bqItem['uom_symbol'] : '---';

            $bqItems[$key][BQItem::FORMULATED_COLUMN_RATE . '-final_value']        = 0;
            $bqItems[$key][BQItem::FORMULATED_COLUMN_RATE . '-value']              = '';
            $bqItems[$key][BQItem::FORMULATED_COLUMN_RATE . '-has_cell_reference'] = false;
            $bqItems[$key][BQItem::FORMULATED_COLUMN_RATE . '-has_formula']        = false;
            $bqItems[$key][BQItem::FORMULATED_COLUMN_RATE . '-linked']             = false;
            $bqItems[$key][BQItem::FORMULATED_COLUMN_RATE . '-has_build_up']       = false;

            if ( array_key_exists($bqItem['id'], $formulatedColumns) )
            {
                foreach ( $formulatedColumns[$bqItem['id']] as $formulatedColumn )
                {
                    $columnName                                         = $formulatedColumn['column_name'];
                    $bqItems[$key][$columnName . '-final_value']        = $formulatedColumn['final_value'];
                    $bqItems[$key][$columnName . '-value']              = $formulatedColumn['value'];
                    $bqItems[$key][$columnName . '-has_build_up']       = $formulatedColumn['has_build_up'];
                    $bqItems[$key][$columnName . '-has_cell_reference'] = false;
                    $bqItems[$key][$columnName . '-linked']             = false;
                    $bqItems[$key][$columnName . '-has_formula']        = $formulatedColumn['value'] != $formulatedColumn['final_value'] ? true : false;
                }
            }

            unset( $formulatedColumns[$bqItem['id']], $bqItem );
        }

        array_push($bqItems, array(
            'id'                                                   => Constants::GRID_LAST_ROW,
            'description'                                          => '',
            'uom_symbol'                                           => '',
            BQItem::FORMULATED_COLUMN_RATE . '-final_value'        => 0,
            BQItem::FORMULATED_COLUMN_RATE . '-value'              => 0,
            BQItem::FORMULATED_COLUMN_RATE . '-linked'             => false,
            BQItem::FORMULATED_COLUMN_RATE . '-has_build_up'       => false,
            BQItem::FORMULATED_COLUMN_RATE . '-has_cell_reference' => false,
            BQItem::FORMULATED_COLUMN_RATE . '-has_formula'        => false
        ));

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $bqItems
        ));
    }

    public function executeGetBillItemList(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest() and $element = Doctrine_Core::getTable('BillElement')->find($request->getParameter('id')));

        $pdo               = $element->getTable()->getConnection()->getDbh();
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

        $stmt = $pdo->prepare("SELECT ifc.relation_id, ifc.column_name, ifc.final_value, ifc.value, ifc.linked, ifc.has_build_up
            FROM " . BillItemFormulatedColumnTable::getInstance()->getTableName() . " ifc JOIN
            " . BillItemTable::getInstance()->getTableName() . " i ON i.id = ifc.relation_id
            WHERE i.element_id = " . $element->id . " AND i.deleted_at IS NULL
            AND ifc.deleted_at IS NULL AND ifc.final_value <> 0");

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

        foreach ( $billItems as $key => $billItem )
        {
            $billItems[$key]['type']       = (string) $billItem['type'];
            $billItems[$key]['uom_id']     = (string) $billItem['uom_id'];
            $billItems[$key]['uom_symbol'] = $billItem['uom_id'] > 0 ? $billItem['uom_symbol'] : '---';

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
                    $billItem[$columnName . '-linked']                    = $formulatedColumn['linked'];
                    $billItems[$key][$columnName . '-has_formula']        = $formulatedColumn['value'] != $formulatedColumn['final_value'] ? true : false;
                }
            }

            unset( $formulatedColumns[$billItem['id']], $billItem );
        }

        array_push($billItems, array(
            'id'                                                     => Constants::GRID_LAST_ROW,
            'description'                                            => '',
            'uom_symbol'                                             => '',
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

    public function executeImportBillItems(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $billElement = Doctrine_Core::getTable('BillElement')->find($request->getParameter('element_id'))
        );

        $errorMsg = null;

        try
        {
            $ids      = Utilities::array_filter_integer(explode(',', $request->getParameter('ids')));
            $withRate = $request->getParameter('with_rate') == 'true' ? true : false;

            BillItemTable::importBQItems($request->getParameter('id'), $billElement, $ids, $withRate, $request->getParameter('currentBQAddendumId'));

            $success = true;
        }
        catch (Exception $e)
        {
            $errorMsg = $e->getMessage();
            $success  = false;
        }

        return $this->renderJson(array( 'success' => $success, 'errorMsg' => $errorMsg ));
    }

    public function executeImportBillProjectItems(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $billElement = Doctrine_Core::getTable('BillElement')->find($request->getParameter('element_id'))
        );

        $errorMsg = null;

        try
        {
            $withRate = $request->getParameter('with_rate') == 'true' ? true : false;
            BillItemTable::importBillItems(
                $request->getParameter('id'),
                $billElement,
                Utilities::array_filter_integer(explode(',', $request->getParameter('ids'))),
                $withRate, $request->getParameter('currentBQAddendumId'));

            $success = true;
        }
        catch (Exception $e)
        {
            $errorMsg = $e->getMessage();
            $success  = false;
        }

        return $this->renderJson(array( 'success' => $success, 'errorMsg' => $errorMsg ));
    }

    public function executeImportBillElements(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless($request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('bill_id'))
        );

        try
        {
            $ids = Utilities::array_filter_integer(explode(',', $request->getParameter('ids')));

            $billElementIds = BillElementTable::importBQElements($request->getParameter('id'), $ids, $bill);

            $elements = DoctrineQuery::create()->select('e.id, e.description')
                ->from('BillElement e')
                ->where('e.project_structure_id = ?', $bill->id)
                ->andWhereIn('e.id', $billElementIds)
                ->addOrderBy('e.priority ASC')
                ->fetchArray();

            $form                      = new BaseForm();
            $formulatedColumnConstants = Utilities::getAllFormulatedColumnConstants('BillElement');

            $billMarkupSetting = $bill->BillMarkupSetting;

            foreach ( $elements as $key => $element )
            {
                $elements[$key]['markup_rounding_type'] = $billMarkupSetting->rounding_type;

                foreach ( $bill->BillColumnSettings as $column )
                {
                    $elements[$key][$column->id . '-total_per_unit'] = 0;
                    $elements[$key][$column->id . '-total']          = 0;
                    $elements[$key][$column->id . '-total_cost']     = 0;

                    $elements[$key][$column->id . '-estimated_cost']                  = '';
                    $elements[$key][$column->id . '-estimated_cost_per_metre_square'] = '';
                    $elements[$key][$column->id . '-element_sum_total']               = 0;
                }

                foreach ( $formulatedColumnConstants as $constant )
                {
                    $elements[$key][$constant . '-final_value']        = 0;
                    $elements[$key][$constant . '-value']              = '';
                    $elements[$key][$constant . '-has_cell_reference'] = false;
                    $elements[$key][$constant . '-has_formula']        = false;
                    unset( $formulatedColumn );
                }

                $elements[$key]['has_note']                   = false;
                $elements[$key]['note']                       = "";
                $elements[$key]['original_grand_total']       = 0;
                $elements[$key]['grand_total']                = 0;
                $elements[$key]['overall_total_after_markup'] = 0;
                $elements[$key]['element_sum_total']          = 0;
                $elements[$key]['relation_id']                = $bill->id;
                $elements[$key]['_csrf_token']                = $form->getCSRFToken();
            }

            $success  = true;
            $errorMsg = null;
        }
        catch (Exception $e)
        {
            $errorMsg = $e->getMessage();
            $success  = false;
            $elements = array();
        }

        return $this->renderJson(array( 'success' => $success, 'errorMsg' => $errorMsg, 'elements' => $elements ));
    }

}