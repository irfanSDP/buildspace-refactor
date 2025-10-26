<?php

/**
 * printBQ actions.
 *
 * @package    buildspace
 * @subpackage printBQ
 * @author     1337 developers
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class printBQActions extends BaseActions {

    public function executeIndex(sfWebRequest $request)
    {
        sfConfig::set('sf_web_debug', false);

        $request->checkCSRFProtection();

        $this->forward404Unless($projectStructure = ProjectStructureTable::getInstance()->find($request->getParameter('bid')));

        $element = BillElementTable::getInstance()->find($request->getParameter('eid'));

        try
        {
            $element         = (!$element) ? null : $element;//doctrine returns boolean if no record found. We need to convert it to null to pass to pageGeneratpr params
            $bqPageGenerator = new sfBuildspaceBQByRevisionPageGenerator($projectStructure, $element);
            $stylesheet      = $this->getBQStyling();
            $pages           = $bqPageGenerator->generatePages();
        }
        catch(PageGeneratorException $e)
        {
            return $this->pageGeneratorExceptionView($e, $bqPageGenerator);
        }

        $billColumnSettings   = $projectStructure->getBillColumnSettings()->toArray();
        $billItemsLayout      = ( count($billColumnSettings) > 1 and !$bqPageGenerator->printGrandTotalQty ) ? 'multiTypeBillItemsLayout' : 'singleTypeBillItemsLayout';
        $collectionPageLayout = ( count($billColumnSettings) > 1 and !$bqPageGenerator->printGrandTotalQty ) ? 'multiTypeCollectionPage' : 'singleTypeCollectionPage';
        $maxRows              = $bqPageGenerator->getMaxRows();
        $currency             = $bqPageGenerator->getCurrency();
        $withoutPrice         = $bqPageGenerator->getPrintNoPrice();
        $printFullDecimal     = $bqPageGenerator->getPrintFullDecimal();

        if ( $request->getParameter('currentModule') AND $request->getParameter('currentModule') == 'tendering' )
        {
            $withoutPrice = ( $request->getParameter('withPrice') ) ? false : true;
        }

        $pdfGen = new WkHtmlToPdf($this->getPrintBOQPageLayoutSettings($bqPageGenerator));

        foreach ( $pages as $key => $page )
        {
            if ( $key == 'summary_pages' )
            {
                continue;
            }

            for ( $i = 1; $i <= $page['item_pages']->count(); $i ++ )
            {
                if ( $page['item_pages'] instanceof SplFixedArray and $page['item_pages']->offsetExists($i) )
                {
                    $layout = $this->getPartial('printBQ/pageLayout', array(
                        'stylesheet'    => $stylesheet,
                        'layoutStyling' => $bqPageGenerator->getLayoutStyling()
                    ));

                    $layout .= $this->getPartial('printBQ/' . $billItemsLayout, array(
                        'itemPage'                   => $page['item_pages']->offsetGet($i),
                        'billColumnSettings'         => $billColumnSettings,
                        'maxRows'                    => $maxRows,
                        'currency'                   => $currency,
                        'elementHeaderDescription'   => $page['description'],
                        'elementCount'               => $page['element_count'],
                        'pageCount'                  => $i,
                        'topLeftRow1'                => $bqPageGenerator->getTopLeftFirstRowHeader(),
                        'topLeftRow2'                => $bqPageGenerator->getTopLeftSecondRowHeader(),
                        'topRightRow1'               => $bqPageGenerator->getTopRightFirstRowHeader(),
                        'botLeftRow1'                => $bqPageGenerator->getBottomLeftFirstRowHeader(),
                        'botLeftRow2'                => $bqPageGenerator->getBottomLeftSecondRowHeader(),
                        'descHeader'                 => $bqPageGenerator->getTableHeaderDescriptionPrefix(),
                        'unitHeader'                 => $bqPageGenerator->getTableHeaderUnitPrefix(),
                        'rateHeader'                 => $bqPageGenerator->getTableHeaderRatePrefix(),
                        'qtyHeader'                  => $bqPageGenerator->getTableHeaderQtyPrefix(),
                        'amtHeader'                  => $bqPageGenerator->getTableHeaderAmtPrefix(),
                        'toCollection'               => $bqPageGenerator->getToCollectionPrefix(),
                        'priceFormatting'            => $bqPageGenerator->getPriceFormatting(),
                        'printNoPrice'               => $withoutPrice,
                        'printFullDecimal'           => $printFullDecimal,
                        'toggleColumnArrangement'    => $bqPageGenerator->getToggleColumnArrangement(),
                        'printElementTitle'          => $bqPageGenerator->getPrintElementTitle(),
                        'printDollarAndCentColumn'   => $bqPageGenerator->getPrintDollarAndCentColumn(),
                        'currencyFormat'             => $bqPageGenerator->getCurrencyFormat(),
                        'rateCommaRemove'            => $bqPageGenerator->getRateCommaRemove(),
                        'qtyCommaRemove'             => $bqPageGenerator->getQtyCommaRemove(),
                        'amtCommaRemove'             => $bqPageGenerator->getAmtCommaRemove(),
                        'printAmountOnly'            => $bqPageGenerator->getPrintAmountOnly(),
                        'printElementInGridOnce'     => $bqPageGenerator->getPrintElementInGridOnce(),
                        'indentItem'                 => $bqPageGenerator->getIndentItem(),
                        'printElementInGrid'         => $bqPageGenerator->getPrintElementInGrid(),
                        'pageNoPrefix'               => $bqPageGenerator->getPageNoPrefix(),
                        'printDateOfPrinting'        => $bqPageGenerator->getPrintDateOfPrinting(),
                        'printGrandTotalQty'         => $bqPageGenerator->printGrandTotalQty,
                        'alignElementTitleToTheLeft' => $bqPageGenerator->getAlignElementToLeft(),
                        'closeGrid'                  => $bqPageGenerator->getCloseGridConfiguration(),
                    ));

                    $page['item_pages']->offsetUnset($i);

                    // Add page from URL
                    $pdfGen->addPage($layout);
                }
            }

            // get last collection's page page no.
            end($page['collection_pages']);
            $lastCollectionPageNo = key($page['collection_pages']);

            foreach ( $page['collection_pages'] as $pageNo => $collectionPage )
            {
                $isLastPage = ( $lastCollectionPageNo == $pageNo );

                $layout = $this->getPartial('printBQ/pageLayout', array(
                    'stylesheet'    => $stylesheet,
                    'layoutStyling' => $bqPageGenerator->getLayoutStyling()
                ));

                $layout .= $this->getPartial('printBQ/' . $collectionPageLayout, array(
                    'collectionPage'             => $collectionPage,
                    'billColumnSettings'         => $billColumnSettings,
                    'maxRows'                    => count($billColumnSettings) > 1 ? $maxRows - 2 : $maxRows,//less 4 rows for collection page
                    'currency'                   => $currency,
                    'elementHeaderDescription'   => $page['description'],
                    'elementCount'               => $page['element_count'],
                    'pageCount'                  => $pageNo,
                    'topLeftRow1'                => $bqPageGenerator->getTopLeftFirstRowHeader(),
                    'topLeftRow2'                => $bqPageGenerator->getTopLeftSecondRowHeader(),
                    'topRightRow1'               => $bqPageGenerator->getTopRightFirstRowHeader(),
                    'botLeftRow1'                => $bqPageGenerator->getBottomLeftFirstRowHeader(),
                    'botLeftRow2'                => $bqPageGenerator->getBottomLeftSecondRowHeader(),
                    'descHeader'                 => $bqPageGenerator->getTableHeaderDescriptionPrefix(),
                    'amtHeader'                  => $bqPageGenerator->getTableHeaderAmtPrefix(),
                    'toCollection'               => $bqPageGenerator->getToCollectionPrefix(),
                    'priceFormatting'            => $bqPageGenerator->getPriceFormatting(),
                    'printNoPrice'               => $withoutPrice,
                    'printElementTitle'          => $bqPageGenerator->getPrintElementTitle(),
                    'printDollarAndCentColumn'   => $bqPageGenerator->getPrintDollarAndCentColumn(),
                    'currencyFormat'             => $bqPageGenerator->getCurrencyFormat(),
                    'amtCommaRemove'             => $bqPageGenerator->getAmtCommaRemove(),
                    'printElementInGrid'         => $bqPageGenerator->getPrintElementInGrid(),
                    'isLastPage'                 => $isLastPage,
                    'pageNoPrefix'               => $bqPageGenerator->getPageNoPrefix(),
                    'printDateOfPrinting'        => $bqPageGenerator->getPrintDateOfPrinting(),
                    'printGrandTotalQty'         => $bqPageGenerator->printGrandTotalQty,
                    'alignElementTitleToTheLeft' => $bqPageGenerator->getAlignElementToLeft(),
                    'closeGrid'                  => $bqPageGenerator->getCloseGridConfiguration(),
                ));

                unset( $collectionPage, $page['collection_pages'][$pageNo] );

                // Add page from URL
                $pdfGen->addPage($layout);
            }
        }

        // ... send to client as file download
        return $pdfGen->send();
    }

    public function executeSummaryPage(sfWebRequest $request)
    {
        sfConfig::set('sf_web_debug', false);

        $request->checkCSRFProtection();

        $this->forward404Unless($projectStructure = ProjectStructureTable::getInstance()->find($request->getParameter('bid')));

        try
        {
            $bqPageGenerator = new sfBuildspaceBQByRevisionPageGenerator($projectStructure);
            $stylesheet      = $this->getBQStyling();
            $pages           = $bqPageGenerator->generatePages();
        }
        catch(PageGeneratorException $e)
        {
            return $this->pageGeneratorExceptionView($e, $bqPageGenerator);
        }

        $billColumnSettings = $projectStructure->getBillColumnSettings()->toArray();
        $maxRows            = $bqPageGenerator->getSummaryMaxRows() - 16;
        $summaryPages       = $pages['summary_pages'];
        $summaryPageLayout  = ( count($billColumnSettings) > 1 and !$bqPageGenerator->printGrandTotalQty ) ? 'multiTypeSummaryPage' : 'singleTypeSummaryPage';
        $currency           = $bqPageGenerator->getCurrency();
        $withoutPrice       = $bqPageGenerator->getPrintNoPrice();
        $printFullDecimal   = $bqPageGenerator->getPrintFullDecimal();

        if ( $request->getParameter('currentModule') AND $request->getParameter('currentModule') == 'tendering' )
        {
            $withoutPrice = ( $request->getParameter('withPrice') ) ? false : true;
        }

        $pdfGen = new WkHtmlToPdf($this->getPrintBOQPageLayoutSettings($bqPageGenerator));

        foreach ( $summaryPages as $pageNo => $summaryPage )
        {
            $isLastPage = $pageNo == count($summaryPages) ? true : false;

            $layout = $this->getPartial('printBQ/pageLayout', array(
                'stylesheet'    => $stylesheet,
                'layoutStyling' => $bqPageGenerator->getLayoutStyling()
            ));

            $layout .= $this->getPartial('printBQ/' . $summaryPageLayout, array(
                'summaryPage'                => $summaryPage,
                'billColumnSettings'         => $billColumnSettings,
                'currency'                   => $currency,
                'topLeftRow1'                => $bqPageGenerator->getTopLeftFirstRowHeader(),
                'topLeftRow2'                => $bqPageGenerator->getTopLeftSecondRowHeader(),
                'topRightRow1'               => $bqPageGenerator->getTopRightFirstRowHeader(),
                'botLeftRow1'                => $bqPageGenerator->getBottomLeftFirstRowHeader(),
                'botLeftRow2'                => $bqPageGenerator->getBottomLeftSecondRowHeader(),
                'summaryHeaderDescription'   => $bqPageGenerator->getSummaryHeaderDescription(),
                'totalPerUnitPrefix'         => $bqPageGenerator->getTotalPerUnitPrefix(),
                'totalPerTypePrefix'         => $bqPageGenerator->getTotalPerTypePrefix(),
                'totalUnitPrefix'            => $bqPageGenerator->getTotalUnitPrefix(),
                'tenderPrefix'               => $bqPageGenerator->getTenderPrefix(),
                'descHeader'                 => $bqPageGenerator->getTableHeaderDescriptionPrefix(),
                'summaryPageNoPrefix'        => $bqPageGenerator->getTableHeaderSummaryPageNoPrefix(),
                'amtHeader'                  => $bqPageGenerator->getTableHeaderAmtPrefix(),
                'maxRows'                    => $maxRows,
                'pageNo'                     => $bqPageGenerator->getSummaryPageNumberingPrefix($pageNo),
                'priceFormatting'            => $bqPageGenerator->getPriceFormatting(),
                'printNoPrice'               => $withoutPrice,
                'printFullDecimal'           => $printFullDecimal,
                'printElementTitle'          => $bqPageGenerator->getPrintElementTitle(),
                'printDollarAndCentColumn'   => $bqPageGenerator->getPrintDollarAndCentColumn(),
                'currencyFormat'             => $bqPageGenerator->getCurrencyFormat(),
                'amtCommaRemove'             => $bqPageGenerator->getAmtCommaRemove(),
                'isLastPage'                 => $isLastPage,
                'summaryInGridPrefix'        => $bqPageGenerator->getSummaryInGridPrefix(),
                'printDateOfPrinting'        => $bqPageGenerator->getPrintDateOfPrinting(),
                'printGrandTotalQty'         => $bqPageGenerator->printGrandTotalQty,
                'alignElementTitleToTheLeft' => $bqPageGenerator->getAlignElementToLeft(),
                'closeGrid'                  => $bqPageGenerator->getCloseGridConfiguration(),
            ));

            $pdfGen->addPage($layout);
        }

        // ... send to client as file download
        return $pdfGen->send();
    }

    public function executeGetPrintList(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest() and $bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('id')));

        $form   = new BaseForm();
        $result = array();
        $pdo    = $bill->getTable()->getConnection()->getDbh();

        $summaryPageRow = array(
            'id'                   => 0,
            'item_count'           => '-',
            'description'          => 'Summary Page',
            'project_structure_id' => $bill->id,
            'print'                => true,
            '_csrf_token'          => $form->getCSRFToken()
        );

        $result[0] = $summaryPageRow;

        // get current original project revision's version
        $originalProjectRevision = DoctrineQuery::create()
            ->select('pr.id')
            ->from('ProjectRevision pr')
            ->where('pr.project_structure_id = ?', $bill->root_id)
            ->andWhere('pr.version = ?', ProjectRevision::ORIGINAL_BILL_VERSION)
            ->limit(1)
            ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
            ->fetchOne();

        $elements = DoctrineQuery::create()
            ->select('e.id, e.description, e.project_structure_id')
            ->from('BillElement e')
            ->where('e.project_structure_id = ?', $bill->id)
            ->addOrderBy('e.priority ASC')
            ->fetchArray();

        $stmt = $pdo->prepare("SELECT i.element_id, COUNT(i.id) AS count FROM " . BillItemTable::getInstance()->getTableName() . " i
        JOIN " . BillElementTable::getInstance()->getTableName() . " e ON i.element_id = e.id WHERE
        e.project_structure_id = " . $bill->id . " AND e.deleted_at IS NULL AND i.project_revision_id = " . $originalProjectRevision['id'] . " AND i.project_revision_deleted_at IS NULL
        AND i.deleted_at IS NULL GROUP BY i.element_id");

        $stmt->execute();

        $itemCount = $stmt->fetchAll(PDO::FETCH_COLUMN | PDO::FETCH_GROUP);

        foreach ( $elements as $key => $element )
        {
            $element['item_count']  = array_key_exists($element['id'], $itemCount) ? $itemCount[$element['id']] : 0;
            $element['print']       = true;
            $element['_csrf_token'] = $form->getCSRFToken();
            $result[$key + 1]       = $element;

            unset( $element );
        }

        array_push($result, array(
            'id'                   => Constants::GRID_LAST_ROW,
            'item_count'           => null,
            'description'          => null,
            'print'                => false,
            'project_structure_id' => null,
            '_csrf_token'          => null
        ));

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $result
        ));
    }

    public function executeGetProjectPrintList(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('id')) and
            $project->type == ProjectStructure::TYPE_ROOT
        );

        $form      = new BaseForm();
        $result    = array();
        $noSummary = true;
        $pdo       = $project->getTable()->getConnection()->getDbh();

        if ( !$request->hasParameter('no_summary') )
        {
            $summaryPageRow = array(
                'id'                   => 0,
                'item_count'           => '-',
                'title'                => 'Project Summary Page',
                'project_structure_id' => $project->id,
                'print'                => true,
                '_csrf_token'          => $form->getCSRFToken()
            );

            $result[0] = $summaryPageRow;

            $noSummary = false;
        }

        $tenderAlternativeProjectStructureIds = [];
        if($request->hasParameter('tid'))
        {
            $tenderAlternative = Doctrine_Core::getTable('TenderAlternative')->find($request->getParameter('tid'));

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
        }

        $queryBills = DoctrineQuery::create()
            ->select('p.id, p.title, p.type')
            ->from('ProjectStructure p')
            ->where('p.root_id = ?', $project->id)
            ->whereIn('p.type', [ ProjectStructure::TYPE_BILL, ProjectStructure::TYPE_SCHEDULE_OF_RATE_BILL, ProjectStructure::TYPE_SUPPLY_OF_MATERIAL_BILL ]);

        if(!empty($tenderAlternativeProjectStructureIds))
        {
            $queryBills->whereIn('p.id', $tenderAlternativeProjectStructureIds);
        }

        $bills = $queryBills->addOrderBy('p.lft ASC')->fetchArray();

        $queryForBill = "SELECT p.id, COUNT(i.id) AS count
            FROM " . BillItemTable::getInstance()->getTableName() . " i
            JOIN " . BillElementTable::getInstance()->getTableName() . " e ON i.element_id = e.id
            JOIN " . ProjectStructureTable::getInstance()->getTableName() . " p ON e.project_structure_id = p.id
            WHERE p.root_id = " . $project->id . " AND e.deleted_at IS NULL AND i.project_revision_deleted_at IS NULL
            AND i.deleted_at IS NULL GROUP BY p.id";

        $queryForSupplyOfMaterial = "SELECT p.id, COUNT(i.id) AS count
            FROM " . SupplyOfMaterialItemTable::getInstance()->getTableName() . " i
            JOIN " . SupplyOfMaterialElementTable::getInstance()->getTableName() . " e ON i.element_id = e.id
            JOIN " . ProjectStructureTable::getInstance()->getTableName() . " p ON e.project_structure_id = p.id
            WHERE p.root_id = " . $project->id . " AND e.deleted_at IS NULL
            AND i.deleted_at IS NULL GROUP BY p.id";

        $queryForScheduleOfRateBill = "SELECT p.id, COUNT(i.id) AS count
            FROM " . ScheduleOfRateBillItemTable::getInstance()->getTableName() . " i
            JOIN " . ScheduleOfRateBillElementTable::getInstance()->getTableName() . " e ON i.element_id = e.id
            JOIN " . ProjectStructureTable::getInstance()->getTableName() . " p ON e.project_structure_id = p.id
            WHERE p.root_id = " . $project->id . " AND e.deleted_at IS NULL
            AND i.deleted_at IS NULL GROUP BY p.id";

        $stmt = $pdo->prepare($queryForBill.' UNION '.$queryForSupplyOfMaterial.' UNION '.$queryForScheduleOfRateBill);

        $stmt->execute();

        $itemCount = $stmt->fetchAll(PDO::FETCH_COLUMN | PDO::FETCH_GROUP);

        foreach ( $bills as $key => $bill )
        {
            $bill['item_count']           = array_key_exists($bill['id'], $itemCount) ? $itemCount[$bill['id']] : 0;
            $bill['print']                = true;
            $bill['project_structure_id'] = $project->id;

            $counter = $noSummary ? $key : $key + 1;

            $result[$counter] = $bill;

            unset( $bill );
        }

        array_push($result, array(
            'id'                   => Constants::GRID_LAST_ROW,
            'item_count'           => null,
            'title'                => null,
            'print'                => false,
            'project_structure_id' => null,
            'type'                 => null,
        ));

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $result
        ));
    }

    public function executePrintAllBOQ(sfWebRequest $request)
    {
        sfConfig::set('sf_web_debug', false);

        $request->checkCSRFProtection();

        $this->forward404Unless($projectStructure = ProjectStructureTable::getInstance()->find($request->getParameter('bid')));

        session_write_close();

        $elements = DoctrineQuery::create()
            ->select('e.id, e.description')
            ->from('BillElement e')
            ->where('e.project_structure_id = ?', $projectStructure->id)
            ->addOrderBy('e.priority ASC')
            ->execute();

        $bqPrintOutGenerator = new sfBuildSpaceBQPrintAll($request, $projectStructure, $elements);

        $pdfGen = new WkHtmlToPdf($this->getPrintBOQPageLayoutSettings($bqPrintOutGenerator));

        // set pdf generator
        $bqPrintOutGenerator->setPdfGenerator($pdfGen);

        try
        {
            $bqPrintOutGenerator->generateFullBQPrintoutPages();
        }
        catch(PageGeneratorException $e)
        {
            $data = $e->getData();
            $e = new PageGeneratorException($e->getMessage(), $data['data']);

            return $this->pageGeneratorExceptionView($e, $data['bqPageGenerator']);
        }

        return $bqPrintOutGenerator->pdfGenerator->send();
    }

    public function executePrintBOQAddendum(sfWebRequest $request)
    {
        sfConfig::set('sf_web_debug', false);

        $request->checkCSRFProtection();

        $this->forward404Unless($projectStructure = ProjectStructureTable::getInstance()->find($request->getParameter('bid')));

        $withPrice = ( $request->getParameter('withPrice') ) ? false : true;

        session_write_close();

        // get current root project structure's revision
        $selectedProjectRevision = ProjectRevisionTable::getCurrentSelectedProjectRevisionFromBillId($projectStructure->root_id);

        // get affected element and page no
        $elements = DoctrineQuery::create()
            ->select('e.id, e.description, bp.id, bp.page_no, i.bill_item_id')
            ->from('BillElement e')
            ->leftJoin('e.BillPages bp')
            ->leftJoin('bp.Items i')
            ->where('e.project_structure_id = ?', $projectStructure->id)
            ->andWhere('bp.new_revision_id = ?', $selectedProjectRevision->id)
            ->addOrderBy('e.priority, bp.page_no ASC')
            ->execute();

        $bqAddendumPrintOutGenerator = new sfBuildSpaceBQAddendumPrintAll($projectStructure, $selectedProjectRevision, $elements);

        $pdfGen = new WkHtmlToPdf($this->getPrintBOQPageLayoutSettings($bqAddendumPrintOutGenerator));

        // set pdf generator
        $bqAddendumPrintOutGenerator->setPdfGenerator($pdfGen);

        try
        {
            $bqAddendumPrintOutGenerator->generateFullBQPrintoutPages($withPrice);

            return $bqAddendumPrintOutGenerator->pdfGenerator->send();
        }
        catch(PageGeneratorException $e)
        {
            $data = $e->getData();
            $e    = new PageGeneratorException($e->getMessage(), $data['data']);

            return $this->pageGeneratorExceptionView($e, $data['bqPageGenerator']);
        }
        catch (Exception $e)
        {
            $this->message     = $e->getMessage();
            $this->explanation = "<em>Are you trying to print addendum?</em> Nothing can be printed because there were no changes made in this bill. You can either select previous revision <i>(if you want to print the previous addendum revision or original bill)</i> or make an addendum on the current bill.";

            $this->setTemplate('nothingToPrint');

            return sfView::SUCCESS;
        }
    }


    public function executePrintContractorBQ(sfWebRequest $request)
    {
        sfConfig::set('sf_web_debug', false);

        $request->checkCSRFProtection();

        $this->forward404Unless(
            $project = ProjectStructureTable::getInstance()->find($request->getParameter('pid')) and
            $bill = ProjectStructureTable::getInstance()->find($request->getParameter('bid')) and
            $tenderCompany = TenderCompanyTable::getInstance()->find($request->getParameter('tcid'))
        );

        $withNotListedItem = $request->getParameter('withNotListedItem');

        session_write_close();

        $elements = DoctrineQuery::create()
            ->select('e.id, e.description')
            ->from('BillElement e')
            ->where('e.project_structure_id = ?', $bill->id)
            ->addOrderBy('e.priority ASC')
            ->execute();

        $bqPrintOutGenerator = new sfBuildSpaceBQContractorPrintAll($request, $bill, $elements, $tenderCompany, $withNotListedItem);

        $pdfGen = new WkHtmlToPdf($this->getPrintBOQPageLayoutSettings($bqPrintOutGenerator));

        // set pdf generator
        $bqPrintOutGenerator->setPdfGenerator($pdfGen);

        $bqPrintOutGenerator->generateFullBQPrintoutPages();

        return $bqPrintOutGenerator->pdfGenerator->send();
    }


    public function executePrintContractorProjectSummaryPages(sfWebRequest $request)
    {
        sfConfig::set('sf_web_debug', false);

        $request->checkCSRFProtection();

        $this->forward404Unless(
            $project = ProjectStructureTable::getInstance()->find($request->getParameter('pid')) and
            $tenderCompany = TenderCompanyTable::getInstance()->find($request->getParameter('tcid'))
        );

        $withNotListedItem = $request->getParameter('withNotListedItem');

        session_write_close();

        $bqPrintOutGenerator = new sfBuildSpaceBQContractorPrintAll($request, $project, null, $tenderCompany, $withNotListedItem);

        $pdfGen = new WkHtmlToPdf($this->getPrintBOQPageLayoutSettings($bqPrintOutGenerator));

        // set pdf generator
        $bqPrintOutGenerator->setPdfGenerator($pdfGen);

        $bqPrintOutGenerator->generateProjectSummaryPages($project, $withNotListedItem, true);

        return $bqPrintOutGenerator->pdfGenerator->send();
    }

    public function executePrintEstimationProjectSummaryPages(sfWebRequest $request)
    {
        sfConfig::set('sf_web_debug', false);

        $request->checkCSRFProtection();

        $this->forward404Unless(
            $project = ProjectStructureTable::getInstance()->find($request->getParameter('pid'))
        );

        $withNotListedItem = $request->getParameter('withNotListedItem');

        session_write_close();

        $bqPrintOutGenerator = new sfBuildSpaceBQEstimationPrintAll($request, $project, null, $withNotListedItem);

        $pdfGen = new WkHtmlToPdf($this->getPrintBOQPageLayoutSettings($bqPrintOutGenerator));

        $bqPrintOutGenerator->setPdfGenerator($pdfGen);

        $bqPrintOutGenerator->generateProjectSummaryPages($project, $withNotListedItem, true);

        return $bqPrintOutGenerator->pdfGenerator->send();
    }

    public function executePrintSubPackageEstimationProjectSummaryPages(sfWebRequest $request)
    {
        sfConfig::set('sf_web_debug', false);

        $request->checkCSRFProtection();

        $this->forward404Unless(
            $project = ProjectStructureTable::getInstance()->find($request->getParameter('pid'))
        );

        $withNotListedItem = $request->getParameter('withNotListedItem');

        session_write_close();

        $bqPrintOutGenerator = new sfBuildSpaceBQSubPackageEstimationPrintAll($request, $project, null, $withNotListedItem);

        $pdfGen = new WkHtmlToPdf($this->getPrintBOQPageLayoutSettings($bqPrintOutGenerator));

        $bqPrintOutGenerator->setPdfGenerator($pdfGen);

        $bqPrintOutGenerator->generateProjectSummaryPages($project, $withNotListedItem, true);

        return $bqPrintOutGenerator->pdfGenerator->send();
    }

    public function executePrintSubPackageRationalizedProjectSummaryPages(sfWebRequest $request)
    {
        sfConfig::set('sf_web_debug', false);

        $request->checkCSRFProtection();

        $this->forward404Unless(
            $project = ProjectStructureTable::getInstance()->find($request->getParameter('pid'))
        );

        $withNotListedItem = $request->getParameter('withNotListedItem');

        session_write_close();

        $bqPrintOutGenerator = new sfBuildSpaceBQRationalizedPrintAll($request, $project, null, $withNotListedItem);

        $pdfGen = new WkHtmlToPdf($this->getPrintBOQPageLayoutSettings($bqPrintOutGenerator));

        // set pdf generator
        $bqPrintOutGenerator->setPdfGenerator($pdfGen);

        $bqPrintOutGenerator->generateProjectSummaryPages($project, $withNotListedItem, true);

        return $bqPrintOutGenerator->pdfGenerator->send();
    }

    public function executePrintRationalizedProjectSummaryPages(sfWebRequest $request)
    {
        sfConfig::set('sf_web_debug', false);

        $request->checkCSRFProtection();

        $this->forward404Unless(
            $project = ProjectStructureTable::getInstance()->find($request->getParameter('pid'))
        );

        $withNotListedItem = $request->getParameter('withNotListedItem');

        session_write_close();

        $bqPrintOutGenerator = new sfBuildSpaceBQRationalizedPrintAll($request, $project, null, $withNotListedItem);

        $pdfGen = new WkHtmlToPdf($this->getPrintBOQPageLayoutSettings($bqPrintOutGenerator));

        // set pdf generator
        $bqPrintOutGenerator->setPdfGenerator($pdfGen);

        $bqPrintOutGenerator->generateProjectSummaryPages($project, $withNotListedItem, true);

        return $bqPrintOutGenerator->pdfGenerator->send();
    }


    public function executePrintEstimationBQ(sfWebRequest $request)
    {
        sfConfig::set('sf_web_debug', false);

        $request->checkCSRFProtection();

        $this->forward404Unless(
            $project = ProjectStructureTable::getInstance()->find($request->getParameter('pid')) and
            $bill = ProjectStructureTable::getInstance()->find($request->getParameter('bid'))
        );

        $withNotListedItem = $request->getParameter('withNotListedItem');

        session_write_close();

        $elements = DoctrineQuery::create()
            ->select('e.id, e.description')
            ->from('BillElement e')
            ->where('e.project_structure_id = ?', $bill->id)
            ->addOrderBy('e.priority ASC')
            ->execute();

        $bqPrintOutGenerator = new sfBuildSpaceBQEstimationPrintAll($request, $bill, $elements, $withNotListedItem);

        $pdfGen = new WkHtmlToPdf($this->getPrintBOQPageLayoutSettings($bqPrintOutGenerator));

        // set pdf generator
        $bqPrintOutGenerator->setPdfGenerator($pdfGen);

        $bqPrintOutGenerator->generateFullBQPrintoutPages();

        return $bqPrintOutGenerator->pdfGenerator->send();
    }

    public function executePrintSubPackageEstimationBQ(sfWebRequest $request)
    {
        sfConfig::set('sf_web_debug', false);

        $request->checkCSRFProtection();

        $this->forward404Unless(
            $project = ProjectStructureTable::getInstance()->find($request->getParameter('pid')) and
            $bill = ProjectStructureTable::getInstance()->find($request->getParameter('bid'))
        );

        $withNotListedItem = $request->getParameter('withNotListedItem');

        session_write_close();

        $elements = DoctrineQuery::create()
            ->select('e.id, e.description')
            ->from('BillElement e')
            ->where('e.project_structure_id = ?', $bill->id)
            ->addOrderBy('e.priority ASC')
            ->execute();

        $bqPrintOutGenerator = new sfBuildSpaceBQSubPackageEstimationPrintAll($request, $bill, $elements, $withNotListedItem);

        $pdfGen = new WkHtmlToPdf($this->getPrintBOQPageLayoutSettings($bqPrintOutGenerator));

        // set pdf generator
        $bqPrintOutGenerator->setPdfGenerator($pdfGen);

        $bqPrintOutGenerator->generateFullBQPrintoutPages();

        return $bqPrintOutGenerator->pdfGenerator->send();
    }

    public function executePrintSubPackageRationalizedBQ(sfWebRequest $request)
    {
        sfConfig::set('sf_web_debug', false);

        $request->checkCSRFProtection();

        $this->forward404Unless(
            $project = ProjectStructureTable::getInstance()->find($request->getParameter('pid')) and
            $bill = ProjectStructureTable::getInstance()->find($request->getParameter('bid'))
        );

        $withNotListedItem = $request->getParameter('withNotListedItem');

        session_write_close();

        $elements = DoctrineQuery::create()
            ->select('e.id, e.description')
            ->from('BillElement e')
            ->where('e.project_structure_id = ?', $bill->id)
            ->addOrderBy('e.priority ASC')
            ->execute();

        $bqPrintOutGenerator = new sfBuildSpaceBQSubPackageRationalizedPrintAll($request, $bill, $elements, $withNotListedItem);

        $pdfGen = new WkHtmlToPdf($this->getPrintBOQPageLayoutSettings($bqPrintOutGenerator));

        // set pdf generator
        $bqPrintOutGenerator->setPdfGenerator($pdfGen);

        $bqPrintOutGenerator->generateFullBQPrintoutPages();

        return $bqPrintOutGenerator->pdfGenerator->send();
    }

    public function executePrintRationalizedBQ(sfWebRequest $request)
    {
        sfConfig::set('sf_web_debug', false);

        $request->checkCSRFProtection();

        $this->forward404Unless(
            $project = ProjectStructureTable::getInstance()->find($request->getParameter('pid')) and
            $bill = ProjectStructureTable::getInstance()->find($request->getParameter('bid'))
        );

        $withNotListedItem = $request->getParameter('withNotListedItem');

        session_write_close();

        $elements = DoctrineQuery::create()
            ->select('e.id, e.description')
            ->from('BillElement e')
            ->where('e.project_structure_id = ?', $bill->id)
            ->addOrderBy('e.priority ASC')
            ->execute();

        $bqPrintOutGenerator = new sfBuildSpaceBQRationalizedPrintAll($request, $bill, $elements, $withNotListedItem);

        $pdfGen = new WkHtmlToPdf($this->getPrintBOQPageLayoutSettings($bqPrintOutGenerator));

        // set pdf generator
        $bqPrintOutGenerator->setPdfGenerator($pdfGen);

        $bqPrintOutGenerator->generateFullBQPrintoutPages();

        return $bqPrintOutGenerator->pdfGenerator->send();
    }

    private function getPrintBOQPageLayoutSettings($bqPageGenerator)
    {
        $orientation = $bqPageGenerator->getOrientation();

        // for portrait printout
        $marginTop   = 8;
        $marginLeft  = 24;
        $marginRight = 4;

        // for landscape printout
        if ( $orientation == sfBuildspaceBQMasterFunction::ORIENTATION_LANDSCAPE )
        {
            $marginLeft  = 12;
            $marginRight = 12;
        }

        return array(
            'disable-smart-shrinking',
            'disable-javascript',
            'no-outline',
            'no-background',
            'enableEscaping' => true,
            'binPath'        => sfConfig::get('app_wkhtmltopdf_bin_path'),
            'margin-top'     => $marginTop,
            'margin-right'   => $marginRight,
            'margin-bottom'  => 3,
            'margin-left'    => $marginLeft,
            'page-size'      => 'A4',
            'orientation'    => $orientation,
        );
    }

    public function executePrintTenderSubPackagePdf(sfWebRequest $request)
    {
        sfConfig::set('sf_web_debug', false);

        $request->checkCSRFProtection();

        $this->forward404Unless($projectStructure = ProjectStructureTable::getInstance()->find($request->getParameter('bid')));

        $element = BillElementTable::getInstance()->find($request->getParameter('eid'));

        $bqPageGenerator = new sfBuildspaceTenderSubPackageBQPageGenerator($projectStructure, $element);

        $pages = $bqPageGenerator->generatePages();

        $billColumnSettings   = $projectStructure->getBillColumnSettings()->toArray();
        $billItemsLayout      = ( count($billColumnSettings) > 1 and !$bqPageGenerator->printGrandTotalQty ) ? 'multiTypeBillItemsLayout' : 'singleTypeBillItemsLayout';
        $collectionPageLayout = ( count($billColumnSettings) > 1 and !$bqPageGenerator->printGrandTotalQty ) ? 'multiTypeCollectionPage' : 'singleTypeCollectionPage';
        $maxRows              = $bqPageGenerator->getMaxRows();
        $currency             = $bqPageGenerator->getCurrency();
        $withoutPrice         = $bqPageGenerator->getPrintNoPrice();
        $printFullDecimal     = $bqPageGenerator->getPrintFullDecimal();

        if ( $request->getParameter('currentModule') AND $request->getParameter('currentModule') == 'tendering' )
        {
            $withoutPrice = ( $request->getParameter('withPrice') ) ? false : true;
        }

        $stylesheet = $this->getBQStyling();

        $pdfGen = new WkHtmlToPdf($this->getPrintBOQPageLayoutSettings($bqPageGenerator));

        foreach ( $pages as $key => $page )
        {
            if ( $key == 'summary_pages' )
            {
                continue;
            }

            for ( $i = 1; $i <= $page['item_pages']->count(); $i ++ )
            {
                if ( $page['item_pages'] instanceof SplFixedArray and $page['item_pages']->offsetExists($i) )
                {
                    $layout = $this->getPartial('printBQ/pageLayout', array(
                        'stylesheet'    => $stylesheet,
                        'layoutStyling' => $bqPageGenerator->getLayoutStyling()
                    ));

                    $layout .= $this->getPartial('printBQ/' . $billItemsLayout, array(
                        'itemPage'                   => $page['item_pages']->offsetGet($i),
                        'billColumnSettings'         => $billColumnSettings,
                        'maxRows'                    => $maxRows,
                        'currency'                   => $currency,
                        'elementHeaderDescription'   => $page['description'],
                        'elementCount'               => $page['element_count'],
                        'pageCount'                  => $i,
                        'topLeftRow1'                => $bqPageGenerator->getTopLeftFirstRowHeader(),
                        'topLeftRow2'                => $bqPageGenerator->getTopLeftSecondRowHeader(),
                        'topRightRow1'               => $bqPageGenerator->getTopRightFirstRowHeader(),
                        'botLeftRow1'                => $bqPageGenerator->getBottomLeftFirstRowHeader(),
                        'botLeftRow2'                => $bqPageGenerator->getBottomLeftSecondRowHeader(),
                        'descHeader'                 => $bqPageGenerator->getTableHeaderDescriptionPrefix(),
                        'unitHeader'                 => $bqPageGenerator->getTableHeaderUnitPrefix(),
                        'rateHeader'                 => $bqPageGenerator->getTableHeaderRatePrefix(),
                        'qtyHeader'                  => $bqPageGenerator->getTableHeaderQtyPrefix(),
                        'amtHeader'                  => $bqPageGenerator->getTableHeaderAmtPrefix(),
                        'toCollection'               => $bqPageGenerator->getToCollectionPrefix(),
                        'priceFormatting'            => $bqPageGenerator->getPriceFormatting(),
                        'printNoPrice'               => $withoutPrice,
                        'printFullDecimal'           => $printFullDecimal,
                        'toggleColumnArrangement'    => $bqPageGenerator->getToggleColumnArrangement(),
                        'printElementTitle'          => $bqPageGenerator->getPrintElementTitle(),
                        'printDollarAndCentColumn'   => $bqPageGenerator->getPrintDollarAndCentColumn(),
                        'currencyFormat'             => $bqPageGenerator->getCurrencyFormat(),
                        'rateCommaRemove'            => $bqPageGenerator->getRateCommaRemove(),
                        'qtyCommaRemove'             => $bqPageGenerator->getQtyCommaRemove(),
                        'amtCommaRemove'             => $bqPageGenerator->getAmtCommaRemove(),
                        'printAmountOnly'            => $bqPageGenerator->getPrintAmountOnly(),
                        'printElementInGridOnce'     => $bqPageGenerator->getPrintElementInGridOnce(),
                        'indentItem'                 => $bqPageGenerator->getIndentItem(),
                        'printElementInGrid'         => $bqPageGenerator->getPrintElementInGrid(),
                        'pageNoPrefix'               => $bqPageGenerator->getPageNoPrefix(),
                        'printDateOfPrinting'        => $bqPageGenerator->getPrintDateOfPrinting(),
                        'printGrandTotalQty'         => $bqPageGenerator->printGrandTotalQty,
                        'alignElementTitleToTheLeft' => $bqPageGenerator->getAlignElementToLeft(),
                        'closeGrid'                  => $bqPageGenerator->getCloseGridConfiguration(),
                    ));

                    $page['item_pages']->offsetUnset($i);

                    // Add page from URL
                    $pdfGen->addPage($layout);
                }
            }

            // get last collection's page page no.
            end($page['collection_pages']);
            $lastCollectionPageNo = key($page['collection_pages']);

            foreach ( $page['collection_pages'] as $pageNo => $collectionPage )
            {
                $isLastPage = ( $lastCollectionPageNo == $pageNo );

                $layout = $this->getPartial('printBQ/pageLayout', array(
                    'stylesheet'    => $stylesheet,
                    'layoutStyling' => $bqPageGenerator->getLayoutStyling()
                ));

                $layout .= $this->getPartial('printBQ/' . $collectionPageLayout, array(
                    'collectionPage'             => $collectionPage,
                    'billColumnSettings'         => $billColumnSettings,
                    'maxRows'                    => count($billColumnSettings) > 1 ? $maxRows - 2 : $maxRows,//less 4 rows for collection page
                    'currency'                   => $currency,
                    'elementHeaderDescription'   => $page['description'],
                    'elementCount'               => $page['element_count'],
                    'pageCount'                  => $pageNo,
                    'topLeftRow1'                => $bqPageGenerator->getTopLeftFirstRowHeader(),
                    'topLeftRow2'                => $bqPageGenerator->getTopLeftSecondRowHeader(),
                    'topRightRow1'               => $bqPageGenerator->getTopRightFirstRowHeader(),
                    'botLeftRow1'                => $bqPageGenerator->getBottomLeftFirstRowHeader(),
                    'botLeftRow2'                => $bqPageGenerator->getBottomLeftSecondRowHeader(),
                    'descHeader'                 => $bqPageGenerator->getTableHeaderDescriptionPrefix(),
                    'amtHeader'                  => $bqPageGenerator->getTableHeaderAmtPrefix(),
                    'toCollection'               => $bqPageGenerator->getToCollectionPrefix(),
                    'priceFormatting'            => $bqPageGenerator->getPriceFormatting(),
                    'printNoPrice'               => $withoutPrice,
                    'printElementTitle'          => $bqPageGenerator->getPrintElementTitle(),
                    'printDollarAndCentColumn'   => $bqPageGenerator->getPrintDollarAndCentColumn(),
                    'currencyFormat'             => $bqPageGenerator->getCurrencyFormat(),
                    'amtCommaRemove'             => $bqPageGenerator->getAmtCommaRemove(),
                    'printElementInGrid'         => $bqPageGenerator->getPrintElementInGrid(),
                    'isLastPage'                 => $isLastPage,
                    'pageNoPrefix'               => $bqPageGenerator->getPageNoPrefix(),
                    'printDateOfPrinting'        => $bqPageGenerator->getPrintDateOfPrinting(),
                    'printGrandTotalQty'         => $bqPageGenerator->printGrandTotalQty,
                    'alignElementTitleToTheLeft' => $bqPageGenerator->getAlignElementToLeft(),
                    'closeGrid'                  => $bqPageGenerator->getCloseGridConfiguration(),
                ));

                unset( $collectionPage, $page['collection_pages'][$pageNo] );

                // Add page from URL
                $pdfGen->addPage($layout);
            }
        }

        // ... send to client as file download
        return $pdfGen->send();
    }

    public function executePrintTenderSubPackageSummaryPage(sfWebRequest $request)
    {
        sfConfig::set('sf_web_debug', false);

        $request->checkCSRFProtection();

        $this->forward404Unless($projectStructure = ProjectStructureTable::getInstance()->find($request->getParameter('bid')));

        $bqPageGenerator    = new sfBuildspaceTenderSubPackageBQPageGenerator($projectStructure, null);
        $pages              = $bqPageGenerator->generatePages();
        $stylesheet         = $this->getBQStyling();
        $billColumnSettings = $projectStructure->getBillColumnSettings()->toArray();
        $maxRows            = $bqPageGenerator->getSummaryMaxRows() - 16;
        $summaryPages       = $pages['summary_pages'];
        $summaryPageLayout  = ( count($billColumnSettings) > 1 and !$bqPageGenerator->printGrandTotalQty ) ? 'multiTypeSummaryPage' : 'singleTypeSummaryPage';
        $currency           = $bqPageGenerator->getCurrency();
        $withoutPrice       = $bqPageGenerator->getPrintNoPrice();
        $printFullDecimal   = $bqPageGenerator->getPrintFullDecimal();

        if ( $request->getParameter('currentModule') AND $request->getParameter('currentModule') == 'tendering' )
        {
            $withoutPrice = ( $request->getParameter('withPrice') ) ? false : true;
        }

        $pdfGen = new WkHtmlToPdf($this->getPrintBOQPageLayoutSettings($bqPageGenerator));

        foreach ( $summaryPages as $pageNo => $summaryPage )
        {
            $isLastPage = $pageNo == count($summaryPages) ? true : false;

            $layout = $this->getPartial('printBQ/pageLayout', array(
                'stylesheet'    => $stylesheet,
                'layoutStyling' => $bqPageGenerator->getLayoutStyling()
            ));

            $layout .= $this->getPartial('printBQ/' . $summaryPageLayout, array(
                'summaryPage'                => $summaryPage,
                'billColumnSettings'         => $billColumnSettings,
                'currency'                   => $currency,
                'topLeftRow1'                => $bqPageGenerator->getTopLeftFirstRowHeader(),
                'topLeftRow2'                => $bqPageGenerator->getTopLeftSecondRowHeader(),
                'topRightRow1'               => $bqPageGenerator->getTopRightFirstRowHeader(),
                'botLeftRow1'                => $bqPageGenerator->getBottomLeftFirstRowHeader(),
                'botLeftRow2'                => $bqPageGenerator->getBottomLeftSecondRowHeader(),
                'summaryHeaderDescription'   => $bqPageGenerator->getSummaryHeaderDescription(),
                'totalPerUnitPrefix'         => $bqPageGenerator->getTotalPerUnitPrefix(),
                'totalPerTypePrefix'         => $bqPageGenerator->getTotalPerTypePrefix(),
                'totalUnitPrefix'            => $bqPageGenerator->getTotalUnitPrefix(),
                'tenderPrefix'               => $bqPageGenerator->getTenderPrefix(),
                'descHeader'                 => $bqPageGenerator->getTableHeaderDescriptionPrefix(),
                'summaryPageNoPrefix'        => $bqPageGenerator->getTableHeaderSummaryPageNoPrefix(),
                'amtHeader'                  => $bqPageGenerator->getTableHeaderAmtPrefix(),
                'maxRows'                    => $maxRows,
                'pageNo'                     => $bqPageGenerator->getSummaryPageNumberingPrefix($pageNo),
                'priceFormatting'            => $bqPageGenerator->getPriceFormatting(),
                'printNoPrice'               => $withoutPrice,
                'printFullDecimal'           => $printFullDecimal,
                'printElementTitle'          => $bqPageGenerator->getPrintElementTitle(),
                'printDollarAndCentColumn'   => $bqPageGenerator->getPrintDollarAndCentColumn(),
                'currencyFormat'             => $bqPageGenerator->getCurrencyFormat(),
                'amtCommaRemove'             => $bqPageGenerator->getAmtCommaRemove(),
                'isLastPage'                 => $isLastPage,
                'summaryInGridPrefix'        => $bqPageGenerator->getSummaryInGridPrefix(),
                'printDateOfPrinting'        => $bqPageGenerator->getPrintDateOfPrinting(),
                'printGrandTotalQty'         => $bqPageGenerator->printGrandTotalQty,
                'alignElementTitleToTheLeft' => $bqPageGenerator->getAlignElementToLeft(),
                'closeGrid'                  => $bqPageGenerator->getCloseGridConfiguration(),
            ));

            $pdfGen->addPage($layout);
        }

        // ... send to client as file download
        return $pdfGen->send();
    }

    public function executePrintTenderSubPackageAll(sfWebRequest $request)
    {
        sfConfig::set('sf_web_debug', false);

        $request->checkCSRFProtection();

        $this->forward404Unless($projectStructure = ProjectStructureTable::getInstance()->find($request->getParameter('bid')));

        session_write_close();

        $elements = DoctrineQuery::create()
            ->select('e.id, e.description')
            ->from('BillElement e')
            ->where('e.project_structure_id = ?', $projectStructure->id)
            ->addOrderBy('e.priority ASC')
            ->execute();

        $bqPrintOutGenerator = new sfBuildSpaceTenderSubPackagePrintAll($request, $projectStructure, $elements);

        $pdfGen = new WkHtmlToPdf($this->getPrintBOQPageLayoutSettings($bqPrintOutGenerator));

        // set pdf generator
        $bqPrintOutGenerator->setPdfGenerator($pdfGen);

        $bqPrintOutGenerator->generateFullBQPrintoutPages();

        return $bqPrintOutGenerator->pdfGenerator->send();
    }

    public function executeGetFinalBQPrintList(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('id')) and
            $project->type == ProjectStructure::TYPE_ROOT
        );

        $tenderAlternativeProjectStructureIds = [];
        if($request->hasParameter('tid'))
        {
            $tenderAlternative = Doctrine_Core::getTable('TenderAlternative')->find($request->getParameter('tid'));

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
        }

        $form   = new BaseForm();
        $result = [];
        $pdo    = $project->getTable()->getConnection()->getDbh();

        $queryBills = DoctrineQuery::create()
            ->select('p.id, p.title, p.type')
            ->from('ProjectStructure p')
            ->where('p.root_id = ?', $project->id)
            ->andWhere('p.type = ?',ProjectStructure::TYPE_BILL);
        
        $itemCountSql = "";
        if(!empty($tenderAlternativeProjectStructureIds))
        {
            $queryBills->whereIn('p.id', $tenderAlternativeProjectStructureIds);

            $itemCountSql = " AND b.id IN (".implode(',', $tenderAlternativeProjectStructureIds).") ";
        }

        $bills = $queryBills->addOrderBy('p.lft ASC')->fetchArray();

        $stmt = $pdo->prepare("SELECT b.id, COUNT(i.id) AS count
            FROM " . BillItemTable::getInstance()->getTableName() . " i
            JOIN " . BillElementTable::getInstance()->getTableName() . " e ON i.element_id = e.id
            JOIN " . ProjectStructureTable::getInstance()->getTableName() . " b ON e.project_structure_id = b.id
            WHERE b.root_id = " . $project->id . " ".$itemCountSql." AND e.deleted_at IS NULL
            AND i.project_revision_deleted_at IS NULL AND i.deleted_at IS NULL
            GROUP BY b.id");

        $stmt->execute();

        $itemCount = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

        foreach ( $bills as $key => $bill )
        {
            $bill['item_count']           = array_key_exists($bill['id'], $itemCount) ? $itemCount[$bill['id']] : 0;
            $bill['print']                = true;
            $bill['project_structure_id'] = $project->id;

            $result[$key] = $bill;

            unset( $bill );
        }

        array_push($result, [
            'id'                   => Constants::GRID_LAST_ROW,
            'item_count'           => null,
            'title'                => null,
            'print'                => false,
            'project_structure_id' => null,
            'type'                 => null,
        ]);

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $result
        ));
    }

    public function executePrintFinalBQ(sfWebRequest $request)
    {
        sfConfig::set('sf_web_debug', false);

        $request->checkCSRFProtection();

        $this->forward404Unless(
            $bill = ProjectStructureTable::getInstance()->find($request->getParameter('id'))
        );

        $project   = $bill->getRoot();
        $withPrice = (int)$request->getParameter('withPrice');

        try
        {
            $bqPageGenerator = new sfBuildspaceBQPageGenerator($bill);
            $stylesheet      = $this->getBQStyling();
            $pages           = $bqPageGenerator->generatePages();
        }
        catch(PageGeneratorException $e)
        {
            return $this->pageGeneratorExceptionView($e, $bqPageGenerator);
        }

        $billColumnSettings   = $bill->getBillColumnSettings()->toArray();
        $billItemsLayout      = ( count($billColumnSettings) > 1 and !$bqPageGenerator->printGrandTotalQty ) ? 'multiTypeBillItemsLayout' : 'singleTypeBillItemsLayout';
        $collectionPageLayout = ( count($billColumnSettings) > 1 and !$bqPageGenerator->printGrandTotalQty ) ? 'multiTypeCollectionPage' : 'singleTypeCollectionPage';
        $maxRows              = $bqPageGenerator->getMaxRows();
        $currency             = $bqPageGenerator->getCurrency();
        $printFullDecimal     = $bqPageGenerator->getPrintFullDecimal();
        $withoutPrice         = !$withPrice;

        $pdfGen = new WkHtmlToPdf($this->getPrintBOQPageLayoutSettings($bqPageGenerator));

        $isEmptyBQ = true;

        foreach ( $pages as $key => $page )
        {
            if ( $key == 'summary_pages' )
            {
                continue;
            }

            $isEmptyBQ = false;

            for ( $i = 1; $i <= $page['item_pages']->count(); $i ++ )
            {
                if ( $page['item_pages'] instanceof SplFixedArray and $page['item_pages']->offsetExists($i) )
                {
                    $layout = $this->getPartial('printBQ/pageLayout', array(
                        'stylesheet'    => $stylesheet,
                        'layoutStyling' => $bqPageGenerator->getLayoutStyling()
                    ));

                    $layout .= $this->getPartial('printBQ/' . $billItemsLayout, array(
                        'itemPage'                   => $page['item_pages']->offsetGet($i),
                        'billColumnSettings'         => $billColumnSettings,
                        'maxRows'                    => $maxRows,
                        'currency'                   => $currency,
                        'elementHeaderDescription'   => $page['description'],
                        'elementCount'               => $page['element_count'],
                        'pageCount'                  => $i,
                        'topLeftRow1'                => $bqPageGenerator->getTopLeftFirstRowHeader(),
                        'topLeftRow2'                => $bqPageGenerator->getTopLeftSecondRowHeader(),
                        'topRightRow1'               => $bqPageGenerator->getTopRightFirstRowHeader(),
                        'botLeftRow1'                => $bqPageGenerator->getBottomLeftFirstRowHeader(),
                        'botLeftRow2'                => $bqPageGenerator->getBottomLeftSecondRowHeader(),
                        'descHeader'                 => $bqPageGenerator->getTableHeaderDescriptionPrefix(),
                        'unitHeader'                 => $bqPageGenerator->getTableHeaderUnitPrefix(),
                        'rateHeader'                 => $bqPageGenerator->getTableHeaderRatePrefix(),
                        'qtyHeader'                  => $bqPageGenerator->getTableHeaderQtyPrefix(),
                        'amtHeader'                  => $bqPageGenerator->getTableHeaderAmtPrefix(),
                        'toCollection'               => $bqPageGenerator->getToCollectionPrefix(),
                        'priceFormatting'            => $bqPageGenerator->getPriceFormatting(),
                        'printNoPrice'               => $withoutPrice,
                        'printFullDecimal'           => $printFullDecimal,
                        'toggleColumnArrangement'    => $bqPageGenerator->getToggleColumnArrangement(),
                        'printElementTitle'          => $bqPageGenerator->getPrintElementTitle(),
                        'printDollarAndCentColumn'   => $bqPageGenerator->getPrintDollarAndCentColumn(),
                        'currencyFormat'             => $bqPageGenerator->getCurrencyFormat(),
                        'rateCommaRemove'            => $bqPageGenerator->getRateCommaRemove(),
                        'qtyCommaRemove'             => $bqPageGenerator->getQtyCommaRemove(),
                        'amtCommaRemove'             => $bqPageGenerator->getAmtCommaRemove(),
                        'printAmountOnly'            => $bqPageGenerator->getPrintAmountOnly(),
                        'printElementInGridOnce'     => $bqPageGenerator->getPrintElementInGridOnce(),
                        'indentItem'                 => $bqPageGenerator->getIndentItem(),
                        'printElementInGrid'         => $bqPageGenerator->getPrintElementInGrid(),
                        'pageNoPrefix'               => $bqPageGenerator->getPageNoPrefix(),
                        'printDateOfPrinting'        => $bqPageGenerator->getPrintDateOfPrinting(),
                        'printGrandTotalQty'         => $bqPageGenerator->printGrandTotalQty,
                        'alignElementTitleToTheLeft' => $bqPageGenerator->getAlignElementToLeft(),
                        'closeGrid'                  => $bqPageGenerator->getCloseGridConfiguration(),
                    ));

                    $page['item_pages']->offsetUnset($i);

                    // Add page from URL
                    $pdfGen->addPage($layout);
                }
            }

            // get last collection's page page no.
            end($page['collection_pages']);
            $lastCollectionPageNo = key($page['collection_pages']);

            foreach ( $page['collection_pages'] as $pageNo => $collectionPage )
            {
                $isLastPage = ( $lastCollectionPageNo == $pageNo );

                $layout = $this->getPartial('printBQ/pageLayout', array(
                    'stylesheet'    => $stylesheet,
                    'layoutStyling' => $bqPageGenerator->getLayoutStyling()
                ));

                $layout .= $this->getPartial('printBQ/' . $collectionPageLayout, array(
                    'collectionPage'             => $collectionPage,
                    'billColumnSettings'         => $billColumnSettings,
                    'maxRows'                    => count($billColumnSettings) > 1 ? $maxRows - 2 : $maxRows,//less 4 rows for collection page
                    'currency'                   => $currency,
                    'elementHeaderDescription'   => $page['description'],
                    'elementCount'               => $page['element_count'],
                    'pageCount'                  => $pageNo,
                    'topLeftRow1'                => $bqPageGenerator->getTopLeftFirstRowHeader(),
                    'topLeftRow2'                => $bqPageGenerator->getTopLeftSecondRowHeader(),
                    'topRightRow1'               => $bqPageGenerator->getTopRightFirstRowHeader(),
                    'botLeftRow1'                => $bqPageGenerator->getBottomLeftFirstRowHeader(),
                    'botLeftRow2'                => $bqPageGenerator->getBottomLeftSecondRowHeader(),
                    'descHeader'                 => $bqPageGenerator->getTableHeaderDescriptionPrefix(),
                    'amtHeader'                  => $bqPageGenerator->getTableHeaderAmtPrefix(),
                    'toCollection'               => $bqPageGenerator->getToCollectionPrefix(),
                    'priceFormatting'            => $bqPageGenerator->getPriceFormatting(),
                    'printNoPrice'               => $withoutPrice,
                    'printElementTitle'          => $bqPageGenerator->getPrintElementTitle(),
                    'printDollarAndCentColumn'   => $bqPageGenerator->getPrintDollarAndCentColumn(),
                    'currencyFormat'             => $bqPageGenerator->getCurrencyFormat(),
                    'amtCommaRemove'             => $bqPageGenerator->getAmtCommaRemove(),
                    'printElementInGrid'         => $bqPageGenerator->getPrintElementInGrid(),
                    'isLastPage'                 => $isLastPage,
                    'pageNoPrefix'               => $bqPageGenerator->getPageNoPrefix(),
                    'printDateOfPrinting'        => $bqPageGenerator->getPrintDateOfPrinting(),
                    'printGrandTotalQty'         => $bqPageGenerator->printGrandTotalQty,
                    'alignElementTitleToTheLeft' => $bqPageGenerator->getAlignElementToLeft(),
                    'closeGrid'                  => $bqPageGenerator->getCloseGridConfiguration(),
                ));

                unset( $collectionPage, $page['collection_pages'][$pageNo] );

                // Add page from URL
                $pdfGen->addPage($layout);
            }
        }

        if($isEmptyBQ)
        {
            $this->message = "Empty BQ";
            $this->explanation = "Nothing to be printed since this BQ is empty.";

            $this->setTemplate('nothingToPrint');
            return sfView::SUCCESS;
        }

        // ... send to client as file download
        return $pdfGen->send();
    }

    protected function pageGeneratorExceptionView(PageGeneratorException $e, sfBuildspaceBQPageGenerator $bqPageGenerator)
    {
        $data = $e->getData();

        $this->errorMessage  = $e->getMessage();
        $this->stylesheet    = $this->getBQStyling();
        $this->layoutStyling = $bqPageGenerator->getLayoutStyling();
        $this->pageNumber    = $data['page_number'];
        $this->pageItems     = $data['page_items'];
        $this->billItem      = BillItemTable::getInstance()->find($data['id']);
        $this->occupiedRows  = $data['occupied_rows'];
        $this->maxRows       = $data['max_rows'];
        $this->currency      = $bqPageGenerator->getCurrency();
        $this->descHeader    = $bqPageGenerator->getTableHeaderDescriptionPrefix();
        $this->unitHeader    = $bqPageGenerator->getTableHeaderUnitPrefix();
        $this->pcRateRows    = [];

        switch($this->billItem->type)
        {
            case BillItem::TYPE_ITEM_PC_RATE:
                $this->pcRateRows = $bqPageGenerator->generatePrimeCostRateRows($this->billItem->id);
                $this->maxRows    = $data['max_rows'] - sfBuildspaceBQPageGenerator::PC_RATE_TABLE_SIZE;
                break;
        }

        list($billItems, $formulatedColumns, $quantityPerUnitByColumns, $billItemTypeReferences, $billItemTypeRefFormulatedColumns) = BillItemTable::getDataStructureForBillItemList($this->billItem->Element, $this->billItem->Element->ProjectStructure);

        $key = array_search($this->billItem->id, array_column($billItems, 'id'));

        $this->rowIdxInBillManager = $key+1;

        $this->setTemplate('pageGeneratorException');

        return sfView::SUCCESS;
    }
}
