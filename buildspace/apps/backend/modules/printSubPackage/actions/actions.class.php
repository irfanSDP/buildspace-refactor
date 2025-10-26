<?php

/**
 * printSubPackage actions.
 *
 * @package    buildspace
 * @subpackage printSubPackage
 * @author     1337 developers
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class printSubPackageActions extends BaseActions {

    public function executeIndex(sfWebRequest $request)
    {
        sfConfig::set('sf_web_debug', false);

        $request->checkCSRFProtection();

        $sendToBrowser = true;

        $this->forward404Unless(
            $subPackage = SubPackageTable::getInstance()->find($request->getParameter('sid')) and
            $project = ProjectStructureTable::getInstance()->find($subPackage->project_structure_id) and
            $bill = ProjectStructureTable::getInstance()->find($request->getParameter('bid'))
        );

        $sfBillRefGenerator = new sfSubPackageBillReferenceGenerator($bill);

        $sfBillRefGenerator->process();

        $newBillRef = $sfBillRefGenerator->getNewBillRef();

        unset( $sfBillRefGenerator );

        session_write_close();

        $elements = DoctrineQuery::create()
            ->select('e.id, e.description')
            ->from('BillElement e')
            ->where('e.project_structure_id = ?', $bill->id)
            ->addOrderBy('e.priority ASC')
            ->execute();

        $billColumnSettings = SubPackageTypeReferenceTable::getBySubPackageId($subPackage->id, $bill->id);

        $pdfGen = new WkHtmlToPdf(array(
            'disable-smart-shrinking',
            'disable-javascript',
            'no-outline',
            'no-background',
            'enableEscaping' => true,
            'binPath'        => sfConfig::get('app_wkhtmltopdf_bin_path'),
            'margin-top'     => 8,
            'margin-right'   => 4,
            'margin-bottom'  => 3,
            'margin-left'    => 24,
            'page-size'      => 'A4',
            'orientation'    => ( count($billColumnSettings) > 1 and !$subPackage->SubPackageBillLayoutSetting->print_grand_total_quantity ) ? sfBuildspaceBQEstimationPageGenerator::ORIENTATION_LANDSCAPE : sfBuildspaceBQEstimationPageGenerator::ORIENTATION_PORTRAIT
        ));

        $stylesheet = file_get_contents(sfConfig::get('sf_web_dir') . '/css/printBQ.css');

        $bqPageGenerator = new sfBuildspaceSubPackageSummaryPageGenerator($bill, null, $subPackage);
        $bqPageGenerator->setNewBillRef($newBillRef);
        $pages             = $bqPageGenerator->generatePages();
        $maxRows           = $bqPageGenerator->getSummaryMaxRows() - 16;
        $summaryPages      = $pages['summary_pages'];
        $summaryPageLayout = ( count($billColumnSettings) > 1 and !$bqPageGenerator->printGrandTotalQty ) ? 'multiTypeSummaryPage' : 'singleTypeSummaryPage';
        $currency          = $bqPageGenerator->getCurrency();
        $withoutPrice      = $bqPageGenerator->getPrintNoPrice();
        $printFullDecimal  = $bqPageGenerator->getPrintFullDecimal();

        if ( $sendToBrowser )
        {
            foreach ( $summaryPages as $pageNo => $summaryPage )
            {
                $isLastPage = $pageNo == count($summaryPages) ? true : false;

                $layout = $this->getPartial('printSubPackage/pageLayout', array(
                        'stylesheet'    => $stylesheet,
                        'layoutStyling' => $bqPageGenerator->getLayoutStyling()
                    )
                );

                $layout .= $this->getPartial('printSubPackage/' . $summaryPageLayout, array(
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

                $pdfGen->addPage($layout);

                unset( $layout );
            }
        }

        foreach ( $elements as $element )
        {
            $bqPageGenerator = new sfBuildspaceSubPackageBQPageGenerator($bill, $element, $subPackage);
            $bqPageGenerator->setNewBillRef($newBillRef);

            $pages                = $bqPageGenerator->generatePages();
            $billItemsLayout      = ( count($billColumnSettings) > 1 and !$bqPageGenerator->printGrandTotalQty ) ? 'multiTypeBillItemsLayout' : 'singleTypeBillItemsLayout';
            $collectionPageLayout = ( count($billColumnSettings) > 1 and !$bqPageGenerator->printGrandTotalQty ) ? 'multiTypeCollectionPage' : 'singleTypeCollectionPage';
            $maxRows              = $bqPageGenerator->getMaxRows();
            $currency             = $bqPageGenerator->getCurrency();
            $withoutPrice         = $bqPageGenerator->getPrintNoPrice();
            $printFullDecimal     = $bqPageGenerator->getPrintFullDecimal();

            if ( $sendToBrowser )
            {
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
                            $layout = $this->getPartial('printSubPackage/pageLayout', array(
                                    'stylesheet'    => $stylesheet,
                                    'layoutStyling' => $bqPageGenerator->getLayoutStyling()
                                )
                            );

                            $layout .= $this->getPartial('printSubPackage/' . $billItemsLayout, array(
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
                                'pageNoPrefix'               => $bill->BillLayoutSetting->page_no_prefix,
                                'printDateOfPrinting'        => $bqPageGenerator->getPrintDateOfPrinting(),
                                'printGrandTotalQty'         => $bqPageGenerator->printGrandTotalQty,
                                'alignElementTitleToTheLeft' => $bqPageGenerator->getAlignElementToLeft(),
                                'closeGrid'                  => $bqPageGenerator->getCloseGridConfiguration(),
                            ));

                            $page['item_pages']->offsetUnset($i);

                            $pdfGen->addPage($layout);

                            unset( $layout );
                        }
                    }

                    end($page['collection_pages']);
                    $lastCollectionPageNo = key($page['collection_pages']);

                    foreach ( $page['collection_pages'] as $pageNo => $collectionPage )
                    {
                        $isLastPage = ( $lastCollectionPageNo == $pageNo );

                        $layout = $this->getPartial('printSubPackage/pageLayout', array(
                                'stylesheet'    => $stylesheet,
                                'layoutStyling' => $bqPageGenerator->getLayoutStyling()
                            )
                        );

                        $layout .= $this->getPartial('printSubPackage/' . $collectionPageLayout, array(
                            'collectionPage'             => $collectionPage,
                            'billColumnSettings'         => $billColumnSettings,
                            'maxRows'                    => count($billColumnSettings) > 1 ? $maxRows - 4 : $maxRows,//less 4 rows for collection page
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
                            'pageNoPrefix'               => $bill->BillLayoutSetting->page_no_prefix,
                            'printDateOfPrinting'        => $bqPageGenerator->getPrintDateOfPrinting(),
                            'printGrandTotalQty'         => $bqPageGenerator->printGrandTotalQty,
                            'alignElementTitleToTheLeft' => $bqPageGenerator->getAlignElementToLeft(),
                            'closeGrid'                  => $bqPageGenerator->getCloseGridConfiguration(),
                        ));

                        // Add page from URL
                        $pdfGen->addPage($layout);

                        unset( $layout, $collectionPage, $page['collection_pages'][$pageNo] );
                    }

                    unset( $pages[$key] );
                }

                unset( $element, $pages, $bqPageGenerator );
            }
        }
        
        return $pdfGen->send();
    }


    public function executePrintContractorBQ(sfWebRequest $request)
    {
        sfConfig::set('sf_web_debug', false);

        $request->checkCSRFProtection();

        $sendToBrowser = true;

        $this->forward404Unless(
            $subPackage = SubPackageTable::getInstance()->find($request->getParameter('sid')) and
            $project = ProjectStructureTable::getInstance()->find($subPackage->project_structure_id) and
            $bill = ProjectStructureTable::getInstance()->find($request->getParameter('bid')) and
            $company = CompanyTable::getInstance()->find($request->getParameter('cid')) and
            $subPackageCompany = SubPackageCompanyTable::getBySubPackageIdAndCompanyId($subPackage->id, $company->id)
        );

        $sfBillRefGenerator = new sfSubPackageBillReferenceGenerator($bill);

        $sfBillRefGenerator->process();

        $newBillRef = $sfBillRefGenerator->getNewBillRef();

        unset( $sfBillRefGenerator );

        session_write_close();

        $elements = DoctrineQuery::create()
            ->select('e.id, e.description')
            ->from('BillElement e')
            ->where('e.project_structure_id = ?', $bill->id)
            ->addOrderBy('e.priority ASC')
            ->execute();

        $billColumnSettings = SubPackageTypeReferenceTable::getBySubPackageId($subPackage->id, $bill->id);

        $pdfGen = new WkHtmlToPdf(array(
            'disable-smart-shrinking',
            'disable-javascript',
            'no-outline',
            'no-background',
            'enableEscaping' => true,
            'binPath'        => sfConfig::get('app_wkhtmltopdf_bin_path'),
            'margin-top'     => 8,
            'margin-right'   => 4,
            'margin-bottom'  => 3,
            'margin-left'    => 24,
            'page-size'      => 'A4',
            'orientation'    => ( count($billColumnSettings) > 1 and !$subPackage->SubPackageBillLayoutSetting->print_grand_total_quantity ) ? sfBuildspaceBQEstimationPageGenerator::ORIENTATION_LANDSCAPE : sfBuildspaceBQEstimationPageGenerator::ORIENTATION_PORTRAIT
        ));

        $stylesheet = file_get_contents(sfConfig::get('sf_web_dir') . '/css/printBQ.css');

        $bqPageGenerator = new sfBuildspaceSubPackageCompanySummaryPageGenerator($bill, null, $subPackage, $subPackageCompany);
        $bqPageGenerator->setNewBillRef($newBillRef);
        $pages             = $bqPageGenerator->generatePages();
        $maxRows           = $bqPageGenerator->getSummaryMaxRows() - 16;
        $summaryPages      = $pages['summary_pages'];
        $summaryPageLayout = ( count($billColumnSettings) > 1 and !$bqPageGenerator->printGrandTotalQty ) ? 'multiTypeSummaryPage' : 'singleTypeSummaryPage';
        $currency          = $bqPageGenerator->getCurrency();
        $withoutPrice      = $bqPageGenerator->getPrintNoPrice();
        $printFullDecimal  = $bqPageGenerator->getPrintFullDecimal();

        if ( $sendToBrowser )
        {
            foreach ( $summaryPages as $pageNo => $summaryPage )
            {
                $isLastPage = $pageNo == count($summaryPages) ? true : false;

                $layout = $this->getPartial('printSubPackage/pageLayout', array(
                        'stylesheet'    => $stylesheet,
                        'layoutStyling' => $bqPageGenerator->getLayoutStyling()
                    )
                );

                $layout .= $this->getPartial('printSubPackage/' . $summaryPageLayout, array(
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

                $pdfGen->addPage($layout);

                unset( $layout );
            }
        }

        foreach ( $elements as $element )
        {
            $bqPageGenerator = new sfBuildspaceSubPackageCompanyBQPageGenerator($bill, $element, $subPackage, $subPackageCompany);
            $bqPageGenerator->setNewBillRef($newBillRef);
            $pages                = $bqPageGenerator->generatePages();
            $billItemsLayout      = ( count($billColumnSettings) > 1 and !$bqPageGenerator->printGrandTotalQty ) ? 'multiTypeBillItemsLayout' : 'singleTypeBillItemsLayout';
            $collectionPageLayout = ( count($billColumnSettings) > 1 and !$bqPageGenerator->printGrandTotalQty ) ? 'multiTypeCollectionPage' : 'singleTypeCollectionPage';
            $maxRows              = $bqPageGenerator->getMaxRows();
            $currency             = $bqPageGenerator->getCurrency();
            $withoutPrice         = $bqPageGenerator->getPrintNoPrice();
            $printFullDecimal     = $bqPageGenerator->getPrintFullDecimal();

            if ( $sendToBrowser )
            {
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
                            $layout = $this->getPartial('printSubPackage/pageLayout', array(
                                    'stylesheet'    => $stylesheet,
                                    'layoutStyling' => $bqPageGenerator->getLayoutStyling()
                                )
                            );

                            $layout .= $this->getPartial('printSubPackage/' . $billItemsLayout, array(
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
                                'pageNoPrefix'               => $bill->BillLayoutSetting->page_no_prefix,
                                'printDateOfPrinting'        => $bqPageGenerator->getPrintDateOfPrinting(),
                                'printGrandTotalQty'         => $bqPageGenerator->printGrandTotalQty,
                                'alignElementTitleToTheLeft' => $bqPageGenerator->getAlignElementToLeft(),
                                'closeGrid'                  => $bqPageGenerator->getCloseGridConfiguration(),
                            ));

                            $page['item_pages']->offsetUnset($i);

                            $pdfGen->addPage($layout);

                            unset( $layout );
                        }
                    }

                    end($page['collection_pages']);
                    $lastCollectionPageNo = key($page['collection_pages']);

                    foreach ( $page['collection_pages'] as $pageNo => $collectionPage )
                    {
                        $isLastPage = ( $lastCollectionPageNo == $pageNo );

                        $layout = $this->getPartial('printSubPackage/pageLayout', array(
                                'stylesheet'    => $stylesheet,
                                'layoutStyling' => $bqPageGenerator->getLayoutStyling()
                            )
                        );

                        $layout .= $this->getPartial('printSubPackage/' . $collectionPageLayout, array(
                            'collectionPage'             => $collectionPage,
                            'billColumnSettings'         => $billColumnSettings,
                            'maxRows'                    => count($billColumnSettings) > 1 ? $maxRows - 4 : $maxRows,//less 4 rows for collection page
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
                            'pageNoPrefix'               => $bill->BillLayoutSetting->page_no_prefix,
                            'printDateOfPrinting'        => $bqPageGenerator->getPrintDateOfPrinting(),
                            'printGrandTotalQty'         => $bqPageGenerator->printGrandTotalQty,
                            'alignElementTitleToTheLeft' => $bqPageGenerator->getAlignElementToLeft(),
                            'closeGrid'                  => $bqPageGenerator->getCloseGridConfiguration(),
                        ));

                        // Add page from URL
                        $pdfGen->addPage($layout);

                        unset( $layout, $collectionPage, $page['collection_pages'][$pageNo] );
                    }

                    unset( $pages[$key] );
                }

                unset( $element, $pages, $bqPageGenerator );
            }
        }

        return $pdfGen->send();
    }

    public function executeGetPrintList(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest() and $subPackage = Doctrine_Core::getTable('SubPackage')->find($request->getParameter('id')));

        $form   = new BaseForm();
        $result = array();
        $pdo    = $subPackage->getTable()->getConnection()->getDbh();


        $sqlFieldCond = '(
            CASE 
                WHEN spsori.sub_package_id is not null THEN spsori.sub_package_id
                WHEN spri.sub_package_id is not null THEN spri.sub_package_id
            ELSE
                spbi.sub_package_id
            END
            ) AS sub_package_id';

        $stmtItem = $pdo->prepare("SELECT DISTINCT c.id AS bill_column_setting_id, c.use_original_quantity, bill.title AS title, bill.id AS bill_id, i.id AS bill_item_id, rate.final_value AS final_value, $sqlFieldCond
        FROM " . SubPackageTable::getInstance()->getTableName() . " sp
        LEFT JOIN " . ProjectStructureTable::getInstance()->getTableName() . " bill ON bill.root_id = sp.project_structure_id
        LEFT JOIN " . SubPackageResourceItemTable::getInstance()->getTableName() . " AS spri ON spri.sub_package_id = sp.id
        LEFT JOIN " . SubPackageScheduleOfRateItemTable::getInstance()->getTableName() . " AS spsori ON spsori.sub_package_id = sp.id
        JOIN " . BillElementTable::getInstance()->getTableName() . " e ON e.project_structure_id = bill.id
        LEFT JOIN " . BillColumnSettingTable::getInstance()->getTableName() . " c ON c.project_structure_id = e.project_structure_id
        JOIN " . BillItemTable::getInstance()->getTableName() . " i ON i.element_id = e.id
        LEFT JOIN " . BillBuildUpRateItemTable::getInstance()->getTableName() . " bur ON bur.bill_item_id = i.id AND bur.resource_item_library_id = spri.resource_item_id AND bur.deleted_at IS NULL
        LEFT JOIN " . ScheduleOfRateItemFormulatedColumnTable::getInstance()->getTableName() . " AS sifc ON sifc.relation_id = spsori.schedule_of_rate_item_id AND sifc.deleted_at IS NULL
        LEFT JOIN " . BillItemFormulatedColumnTable::getInstance()->getTableName() . " AS rate ON rate.relation_id = i.id
        LEFT JOIN " . SubPackageBillItemTable::getInstance()->getTableName() . " spbi ON spbi.bill_item_id = i.id AND spbi.sub_package_id = sp.id
        WHERE sp.id =" . $subPackage->id . " AND sp.deleted_at IS NULL
        AND bill.deleted_at IS NULL
        AND NOT (spri.sub_package_id IS NULL AND spsori.sub_package_id IS NULL AND spbi.sub_package_id IS NULL)
        AND (rate.relation_id = bur.bill_item_id OR rate.schedule_of_rate_item_formulated_column_id = sifc.id OR rate.relation_id = spbi.bill_item_id)
        AND rate.column_name = '" . BillItem::FORMULATED_COLUMN_RATE . "' AND rate.final_value <> 0 AND rate.deleted_at IS NULL
        AND i.type <> " . BillItem::TYPE_ITEM_NOT_LISTED . " AND i.project_revision_deleted_at IS NULL AND i.deleted_at IS NULL
        AND e.deleted_at IS NULL AND c.deleted_at IS NULL ORDER BY bill.id");

        $stmtItem->execute();

        $records = $stmtItem->fetchAll(PDO::FETCH_ASSOC);

        $billArray = array();

        foreach ( $records as $record )
        {
            if ( !array_key_exists($record['bill_id'], $billArray) )
            {
                $billArray[$record['bill_id']] = array(
                    'description' => $record['title']
                );
            }

            unset( $record );
        }

        unset( $records );

        foreach ( $billArray as $key => $bill )
        {
            array_push($result, array(
                'id'             => $key,
                'description'    => $bill['description'],
                'print'          => true,
                'sub_package_id' => $subPackage->id,
                '_csrf_token'    => $form->getCSRFToken()
            ));

            unset( $bill );
        }

        unset( $billArray );

        array_push($result, array(
            'id'             => Constants::GRID_LAST_ROW,
            'description'    => null,
            'print'          => false,
            'sub_package_id' => null,
            '_csrf_token'    => null
        ));

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $result
        ));
    }

}