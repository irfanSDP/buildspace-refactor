<?php

class sfBuildSpaceBQContractorPrintAll {

    public $pdfGenerator = null;
    public $tenderCompany;
    public $withNotListedItem;

    public function __construct($request, ProjectStructure $projectStructure, $elements = null, TenderCompany $tenderCompany, $withNotListedItem = false)
    {
        sfProjectConfiguration::getActive()->loadHelpers('Partial');

        $this->stylesheet          = file_get_contents(sfConfig::get('sf_web_dir').'/css/printBQ.css');
        $this->projectStructure    = $projectStructure;
        $this->tenderCompany       = $tenderCompany;
        $this->elements            = $elements;
        $this->billColumnSettings  = $this->projectStructure->getBillColumnSettings()->toArray();
        $this->numberOfBillColumns = $projectStructure->getBillColumnSettings()->count();
        $this->orientation         = ($this->numberOfBillColumns > 1 and !$projectStructure->BillLayoutSetting->print_grand_total_quantity)? sfBuildspaceBQContractorPageGenerator::ORIENTATION_LANDSCAPE : sfBuildspaceBQContractorPageGenerator::ORIENTATION_PORTRAIT;
        $this->withNotListedItem   = $withNotListedItem;

        $this->request             = $request;

        $this->pdo                 = ProjectStructureTable::getInstance()->getConnection()->getDbh();

        $this->selectedRevision    = ProjectRevisionTable::getCurrentSelectedProjectRevisionFromBillId($projectStructure->root_id);
        $this->currentRevision     = ProjectRevisionTable::getLatestProjectRevisionFromBillId($projectStructure->root_id);
    }

    public function getOrientation()
    {
        return $this->orientation;
    }

    public function setPdfGenerator($pdfGenerator)
    {
        $this->pdfGenerator = $pdfGenerator;
    }

    public function generateFullBQPrintoutPages($sendToBrowser = true)
    {
        self::generateSummaryPages($sendToBrowser);
        self::generateBillItemAndCollectionPages($sendToBrowser);
    }

    public function generateProjectSummaryPages($project, $withNotListedItem = false, $sendToBrowser = true) 
    {
        if($withNotListedItem)
        {
            $whereClause = '';
        }
        else
        {
            $whereClause = 'AND i.type <> '.BillItem::TYPE_ITEM_NOT_LISTED;
        }
        
        $sql = "SELECT p.id, p.title AS description, ROUND(COALESCE(SUM(rate.grand_total) ,0),2) AS total FROM ".BillItemTable::getInstance()->getTableName()." i
                LEFT JOIN ".TenderBillItemRateTable::getInstance()->getTableName()." rate ON rate.bill_item_id = i.id AND rate.tender_company_id = ".$this->tenderCompany->id."
                LEFT JOIN ".BillElementTable::getInstance()->getTableName()." e ON e.id = i.element_id AND e.deleted_at IS NULL
                LEFT JOIN ".ProjectStructureTable::getInstance()->getTableName()." p ON p.id = e.project_structure_id
                WHERE i.deleted_at IS NULL AND i.project_revision_deleted_at IS NULL AND p.root_id = ".$project['id']." $whereClause GROUP BY p.id, p.title";

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute();

        $billTotals = array_map('reset', $stmt->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_ASSOC ));

        $summaryPages = array();

        $this->createProjectSummaryPage($billTotals, 1, $summaryPages);

        if ( $sendToBrowser )
        {
            $summaryPageLayout  = 'singleTypeProjectSummaryPage';

            foreach($summaryPages as $pageNo => $summaryPage)
            {
                $isLastPage = $pageNo == count($summaryPages) ? true : false;

                $layout = get_partial('printBQ/pageLayout', array(
                        'stylesheet' => $this->stylesheet,
                        'layoutStyling' => sfBuildspaceBQMasterFunction::getProjectSummaryLayoutStyling()
                    )
                );

                $layout .= get_partial('printBQ/'.$summaryPageLayout, array(
                    'summaryPage'              => $summaryPage,
                    'currency'                 => $project->MainInformation->Currency,
                    'topLeftRow1'              => '',
                    'topLeftRow2'              => '',
                    'topRightRow1'             => '',
                    'botLeftRow1'              => '',
                    'botLeftRow2'              => '',
                    'summaryHeaderDescription' => 'Project Summary',
                    'descHeader'               => 'Description',
                    'summaryPageNoPrefix'      => 'Page No.',
                    'amtHeader'                => 'Amount',
                    'maxRows'                  => 50,
                    'pageNo'                   => 'Page No ' . $pageNo,
                    'priceFormatting'          => array('.', ',', 2),
                    'printNoPrice'             => FALSE,
                    'printDollarAndCentColumn' => FALSE,
                    'currencyFormat'           => array('RM', 'sen'),
                    'amtCommaRemove'           => FALSE,
                    'isLastPage'               => $isLastPage,
                    'closeGrid'                => true,
                ));

                $this->pdfGenerator->addPage($layout);

                unset($layout);
            }
        }

    }

    public function createProjectSummaryPage($rows, $pageCount, &$summaryPages, $continuePage = false)
    {   
        $summaryPages[$pageCount] = array();
        $maxRows = 72;

        $blankRow    = new SplFixedArray(3);
        $blankRow[0] = null;
        $blankRow[1] = sfBuildspaceBQMasterFunction::ROW_TYPE_BLANK;
        $blankRow[2] = null;

        //blank row
        array_push($summaryPages[$pageCount], $blankRow);//starts with a blank row

        $contdPrefix       = ! $continuePage ? null : " Cont'd "; // Manual since Project has no layout setting
        $summaryPageHeader = new SplFixedArray(3);

        $summaryPageHeader[0] = $contdPrefix;

        $summaryPageHeader[1] = sfBuildspaceBQMasterFunction::ROW_TYPE_SUMMARY_PAGE_TITLE;
        $summaryPageHeader[2] = null;

        //blank row
        array_push($summaryPages[$pageCount], $summaryPageHeader);//starts with a blank row

        if ( $continuePage )
        {
            //blank row
            array_push($summaryPages[$pageCount], $blankRow);//starts with a blank row

            $bringForwardHeader    = new SplFixedArray(3);
            $bringForwardHeader[0] = '';
            $bringForwardHeader[1] = sfBuildspaceBQMasterFunction::ROW_TYPE_SUMMARY_PAGE_TITLE;
            $bringForwardHeader[2] = '0';

            //blank row
            array_push($summaryPages[$pageCount], $bringForwardHeader);//starts with a blank row
        }

        //blank row
        array_push($summaryPages[$pageCount], $blankRow);//starts with a blank row

        $rowCount = ( $continuePage ) ? 5 : 3;

        foreach($rows as $idx => $row)
        {
            $occupiedRows = Utilities::justify($row['description'], 76);
            $rowCount += $occupiedRows->count();

            if($rowCount <= $maxRows)
            {
                foreach($occupiedRows as $key => $occupiedRow)
                {
                    $elementRow    = new SplFixedArray(3);
                    $elementRow[0] = $occupiedRow;
                    $elementRow[1] = sfBuildspaceBQMasterFunction::ROW_TYPE_ELEMENT;
                    $elementRow[2] = null;

                    if ($key == count($occupiedRows)-1)
                    {
                        $elementRow[2] = $row['total'];
                    }

                    array_push($summaryPages[$pageCount], $elementRow);
                }

                unset($occupiedRows, $rows[$idx], $row);

                //blank row
                array_push($summaryPages[$pageCount], $blankRow);

                $rowCount++;//plus one blank row;
            }
            else
            {
                $pageCount++;
                $this->generateSummaryPage($rows, $pageCount, $summaryPages, true);
                break;
            }
        }
    }

    public function generateSummaryPages($sendToBrowser)
    {
        $bqPageGenerator    = new sfBuildspaceContractorSummaryPageGenerator($this->projectStructure, null, $this->tenderCompany, $this->withNotListedItem);
        $pages              = $bqPageGenerator->generatePages();
        $maxRows            = $bqPageGenerator->getSummaryMaxRows() - 16;
        $summaryPages       = $pages['summary_pages'];
        $summaryPageLayout  = (count($this->billColumnSettings) > 1 and !$bqPageGenerator->printGrandTotalQty) ? 'multiTypeSummaryPage' : 'singleTypeSummaryPage';
        $currency           = $bqPageGenerator->getCurrency();
        $withoutPrice       = false;
        $printFullDecimal   = $bqPageGenerator->getPrintFullDecimal();

        if ( $sendToBrowser )
        {
            foreach($summaryPages as $pageNo => $summaryPage)
            {
                $isLastPage = $pageNo == count($summaryPages) ? true : false;

                $layout = get_partial('printBQ/pageLayout', array(
                        'stylesheet' => $this->stylesheet,
                        'layoutStyling' => $bqPageGenerator->getLayoutStyling()
                    )
                );

                $layout .= get_partial('printBQ/'.$summaryPageLayout, array(
                    'summaryPage'                => $summaryPage,
                    'billColumnSettings'         => $this->billColumnSettings,
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
                    'printFullDecimal'           => $printFullDecimal,
                    'pageNo'                     => $bqPageGenerator->getSummaryPageNumberingPrefix($pageNo),
                    'priceFormatting'            => $bqPageGenerator->getPriceFormatting(),
                    'printNoPrice'               => $withoutPrice,
                    'printElementTitle'          => $bqPageGenerator->getPrintElementTitle(),
                    'printDollarAndCentColumn'   => $bqPageGenerator->getPrintDollarAndCentColumn(),
                    'currencyFormat'             => $bqPageGenerator->getCurrencyFormat(),
                    'amtCommaRemove'             => $bqPageGenerator->getAmtCommaRemove(),
                    'isLastPage'                 => $isLastPage,
                    'summaryInGridPrefix'        => $bqPageGenerator->getSummaryInGridPrefix(),
                    'printDateOfPrinting'        => $bqPageGenerator->getPrintDateOfPrinting(),
                    'printGrandTotalQty'         => $bqPageGenerator->printGrandTotalQty,
                    'alignElementTitleToTheLeft' => $bqPageGenerator->getAlignElementToLeft(),
                    'closeGrid'                  => true,
                ));

                $this->pdfGenerator->addPage($layout);

                unset($layout);
            }
        }
    }

    public function generateBillItemAndCollectionPages($sendToBrowser)
    {
        foreach ( $this->elements as $element )
        {
            $bqPageGenerator      = new sfBuildspaceBQContractorPageGenerator($this->projectStructure, $element, $this->tenderCompany, $this->withNotListedItem);
            $pages                = $bqPageGenerator->generatePages();
            $billItemsLayout      = (count($this->billColumnSettings) > 1 and !$bqPageGenerator->printGrandTotalQty) ? 'multiTypeBillItemsLayout' : 'singleTypeBillItemsLayout';
            $collectionPageLayout = (count($this->billColumnSettings) > 1 and !$bqPageGenerator->printGrandTotalQty) ? 'multiTypeCollectionPage' : 'singleTypeCollectionPage';
            $maxRows              = $bqPageGenerator->getMaxRows();
            $currency             = $bqPageGenerator->getCurrency();
            $withoutPrice         = false;
            $printFullDecimal     = $bqPageGenerator->getPrintFullDecimal();

            if ( $sendToBrowser )
            {
                foreach($pages as $key => $page)
                {
                    if($key == 'summary_pages')
                        continue;

                    for($i=1;$i<=$page['item_pages']->count(); $i++)
                    {
                        if($page['item_pages'] instanceof SplFixedArray and $page['item_pages']->offsetExists($i))
                        {
                            $layout = get_partial('printBQ/pageLayout', array(
                                'stylesheet' => $this->stylesheet,
                                'layoutStyling' => $bqPageGenerator->getLayoutStyling()
                                )
                            );

                            $layout .= get_partial('printBQ/'.$billItemsLayout, array(
                                'itemPage'                   => $page['item_pages']->offsetGet($i),
                                'billColumnSettings'         => $this->billColumnSettings,
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
                                'closeGrid'                  => true,
                            ));

                            $page['item_pages']->offsetUnset($i);

                            // Add page from URL
                            $this->pdfGenerator->addPage($layout);

                            unset($layout);
                        }
                    }

                    // get last collection's page page no.
                    end($page['collection_pages']);
                    $lastCollectionPageNo = key($page['collection_pages']);

                    foreach($page['collection_pages'] as $pageNo => $collectionPage)
                    {
                        $isLastPage = ($lastCollectionPageNo == $pageNo);

                        $layout = get_partial('printBQ/pageLayout', array(
                                'stylesheet' => $this->stylesheet,
                                'layoutStyling' => $bqPageGenerator->getLayoutStyling()
                            )
                        );

                        $layout .= get_partial('printBQ/'.$collectionPageLayout, array(
                            'collectionPage'             => $collectionPage,
                            'billColumnSettings'         => $this->billColumnSettings,
                            'maxRows'                    => count($this->billColumnSettings) > 1 ? $maxRows-4 : $maxRows,//less 4 rows for collection page
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
                            'printFullDecimal'           => $printFullDecimal,
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
                            'closeGrid'                  => true,
                        ));

                        // Add page from URL
                        $this->pdfGenerator->addPage($layout);

                        unset($layout, $collectionPage, $page['collection_pages'][$pageNo]);
                    }

                    unset($pages[$key]);
                }

                unset($element, $pages, $bqPageGenerator);
            }
        }
    }

}