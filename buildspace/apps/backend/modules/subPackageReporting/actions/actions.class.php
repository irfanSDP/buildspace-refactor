<?php

/**
 * subPackageReporting actions.
 *
 * @package    buildspace
 * @subpackage subPackageReporting
 * @author     1337 developers
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class subPackageReportingActions extends BaseActions {

	public function executeGetSubContractors(sfWebRequest $request)
	{
		$this->forward404Unless(
			$request->isXmlHttpRequest() and
			$subPackage = Doctrine_Core::getTable('SubPackage')->find($request->getParameter('sid'))
		);

		$companies         = array();
		$sortBy            = $request->getParameter('type') ?? SubPackage::SORT_SUMMARY_SUB_CONTRACTOR_LOWEST_HIGHEST;
		$selectedCompanyId = $subPackage->selected_company_id > 0 ? $subPackage->selected_company_id : - 1;
		$records           = SubPackageCompanyTable::getSubConCompany($subPackage, $selectedCompanyId, $sortBy);

		if ( $subPackage->selected_company_id > 0 )
		{
			$selectedCompany = $subPackage->SelectedCompany;

			$companies[0] = array(
				'id'        => $selectedCompany->id,
				'name'      => $selectedCompany->name,
				'shortname' => $selectedCompany->shortname,
				'total'     => $selectedCompany->getSubPackageTotalBySubPackageId($subPackage->id),
				'selected'  => true
			);

			unset( $selectedCompany );
		}

		foreach ( $records as $key => $record )
		{
			$record['selected'] = false;

			$companies[$subPackage->selected_company_id > 0 ? $key + 1 : $key] = $record;

			unset( $records[$key], $record );
		}

		return $this->renderJson($companies);
	}

    public function executeGetPrintingSelectedBillsSummaryForSelectedTenderer(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $subPackage = Doctrine_Core::getTable('SubPackage')->find($request->getParameter('sid'))
        );

        $billIds = json_decode($request->getParameter('bill_ids'), true);
        $billArray = array();
        $billSplFixedArray = new SplFixedArray(1);

        if( ! empty( $billIds ) )
        {
            $billArray = SubPackageTable::getBillsBySubPackageAndBillIds($subPackage, $billIds);

            $contractorTotals = SubPackageBillItemRateTable::getBillContractorTotals($subPackage, $billIds);
            $estimateTotals = SubPackageBillItemRateTable::getBillEstimateTotals($subPackage, $billIds);

            $billSplFixedArray = new SplFixedArray(count($billArray) + 1);//plus 1 for last empty row in grid

            $count = 0;

            foreach($billArray as $billId => $bill)
            {
                $bill = array(
                    'id'         => $billId,
                    'title'      => $bill['title'],
                    'est_amount' => $estimateTotals[ $billId ]
                );

                foreach($contractorTotals as $contractorId => $contractorBillTotals)
                {
                    if( array_key_exists($billId, $contractorBillTotals) )
                    {
                        $amount = $contractorBillTotals[ $billId ];

                        $bill[ 'total_amount-' . $contractorId ] = $amount;
                        $bill[ 'difference_amount-' . $contractorId ] = $amount - $bill['est_amount'];
                        $bill[ 'difference_percentage-' . $contractorId ] = 0;

                        if( $bill['est_amount'] != 0 )
                        {
                            $bill[ 'difference_percentage-' . $contractorId ] = Utilities::prelimRounding(Utilities::percent($bill[ 'difference_amount-' . $contractorId ], $bill['est_amount']));
                        }
                    }
                }

                $billSplFixedArray[ $count ] = $bill;

                unset( $bill );

                $count++;
            }
        }

        $billSplFixedArray[ count($billArray) ] = array(
            'id'         => Constants::GRID_LAST_ROW,
            'title'      => "",
            'est_amount' => 0
        );

        unset( $billArray );

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $billSplFixedArray->toArray()
        ));
    }

    public function executeGetPrintingSelectedBillsSummaryForAllTenderers(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $subPackage = Doctrine_Core::getTable('SubPackage')->find($request->getParameter('sid'))
        );

        $billIds = json_decode($request->getParameter('bill_ids'), true);
        $billArray = array();
        $billSplFixedArray = new SplFixedArray(1);

        if( ! empty( $billIds ) )
        {
            $billArray = SubPackageTable::getBillsBySubPackageAndBillIds($subPackage, $billIds);

            $contractorTotals = SubPackageBillItemRateTable::getBillContractorTotals($subPackage, $billIds);
            $estimateTotals = SubPackageBillItemRateTable::getBillEstimateTotals($subPackage, $billIds);

            $billSplFixedArray = new SplFixedArray(count($billArray) + 1);//plus 1 for last empty row in grid

            $count = 0;

            foreach($billArray as $billId => $bill)
            {
                $tendererCostings = array();

                $bill = array(
                    'id'         => $billId,
                    'title'      => $bill['title'],
                    'est_amount' => $estimateTotals[ $billId ],
                    'type'       => $bill['type'],
                );

                foreach($contractorTotals as $contractorId => $contractorBillTotals)
                {
                    if( array_key_exists($billId, $contractorBillTotals) )
                    {
                        $amount = $contractorBillTotals[ $billId ];
                        $bill[ $contractorId . '-grand_total' ] = $amount;
                        $tendererCostings[ $contractorId ] = $amount;
                    }
                }

                // if more than 2 tenderers selected then only apply the assignment for the highest
                // and lowest costing from tenderers
                if( $bill['type'] == ProjectStructure::TYPE_BILL AND count($tendererCostings) > 1 )
                {
                    // determine which costing from tenderers is highest and lowest
                    $minTotalIndex = array_keys($tendererCostings, min($tendererCostings));
                    $maxTotalIndex = array_keys($tendererCostings, max($tendererCostings));

                    $bill[ $minTotalIndex[0] . '-lowest_cost' ] = true;
                    $bill[ $maxTotalIndex[0] . '-highest_cost' ] = true;
                }

                unset( $tendererCostings );

                $billSplFixedArray[ $count ] = $bill;

                unset( $bill );

                $count++;
            }
        }

        $billSplFixedArray[ count($billArray) ] = array(
            'id'         => Constants::GRID_LAST_ROW,
            'title'      => "",
            'est_amount' => 0
        );

        unset( $billArray );

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $billSplFixedArray->toArray()
        ));
    }

    public function executeGetPrintingSelectedItemsRateSummaryForSelectedTenderer(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $subPackage = Doctrine_Core::getTable('SubPackage')->find($request->getParameter('sid')) and
            $bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('bid')) and
            $project = Doctrine_Core::getTable('ProjectStructure')->find($bill->root_id)
        );

        $billItemIds = json_decode($request->getParameter('item_ids'), true);
        $pageNoPrefix = $bill->BillLayoutSetting->page_no_prefix;
        $projectMainInfo = $project->MainInformation;
        $isPostContract = ( $projectMainInfo->status == ProjectMainInformation::STATUS_POSTCONTRACT );
        $elementIds = array();
        $data = array();
        $temporarilyBillItemsContainer = array();
        $quantityPerTypes = array();
        $pdo = $subPackage->getTable()->getConnection()->getDbh();

        if( ! empty( $billItemIds ) )
        {
            $records = SubPackageTable::getRatesBySubPackageAndBillAndItemIds($subPackage, $bill, $billItemIds);

            foreach($records as $record)
            {
                $elementIds[ $record['element_id'] ] = $record['element_id'];
            }

            $subConRates = SubPackageBillItemRateTable::getSubConRatesBySubPackageAndItemIds($subPackage, $billItemIds);

            $billItemIds = Utilities::arrayValueRecursive('bill_item_id', $records);
            $billRefSelector = 'p.bill_ref_element_no, p.bill_ref_page_no, p.bill_ref_char';
            $postContractJoinTable = null;

            if( $isPostContract )
            {
                $billRefSelector = 'pc.bill_ref_element_no, pc.bill_ref_page_no, pc.bill_ref_char';
                $postContractJoinTable = 'JOIN ' . PostContractBillItemRateTable::getInstance()->getTableName() . " pc ON (pc.bill_item_id = p.id)";
            }

            $stmt = $pdo->prepare("SELECT DISTINCT p.id, p.element_id, p.root_id, p.description, p.type, p.uom_id, uom.symbol AS uom_symbol, p.grand_total, p.grand_total_quantity, p.level, p.priority, p.lft, {$billRefSelector}
			FROM " . BillItemTable::getInstance()->getTableName() . " c
			JOIN " . BillItemTable::getInstance()->getTableName() . " p ON c.lft BETWEEN p.lft AND p.rgt {$postContractJoinTable}
			LEFT JOIN " . UnitOfMeasurementTable::getInstance()->getTableName() . " uom ON p.uom_id = uom.id AND uom.deleted_at IS NULL
			WHERE c.root_id = p.root_id AND c.type != " . BillItem::TYPE_ITEM_NOT_LISTED . "
			AND c.id IN (" . implode(',', $billItemIds) . ")
			AND c.project_revision_deleted_at IS NULL
			AND c.deleted_at IS NULL AND p.project_revision_deleted_at IS NULL AND p.deleted_at IS NULL
			ORDER BY p.element_id, p.priority, p.lft, p.level ASC");

            $stmt->execute();
            $billItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $elements = BillElementTable::getElementsByElementIds($elementIds);

            if( ! empty( $records ) )
            {
                // will start to construct query to get quantity per type
                $getQuantityPerTypes = BillItemTypeReferenceFormulatedColumnTable::getQuantityPerType($records);

                foreach($getQuantityPerTypes as $quantityPerType)
                {
                    $quantityPerTypes[ $quantityPerType['bill_item_id'] ][ $quantityPerType['bill_column_setting_id'] ] = $quantityPerType['value'];

                    unset( $quantityPerType );
                }

                unset( $getQuantityPerTypes );

                // process all the bill item(s) before passing to be merge with element level
                foreach($records as $recordKey => &$record)
                {
                    foreach($billItems as $key => &$billItem)
                    {
                        $billItems[ $key ]['bill_ref'] = BillItemTable::generateBillRef($pageNoPrefix, $billItems[ $key ]['bill_ref_element_no'], $billItems[ $key ]['bill_ref_page_no'], $billItems[ $key ]['bill_ref_char']);

                        if( ! array_key_exists($record['bill_column_setting_id'] . '-' . BillItemTypeReference::FORMULATED_COLUMN_QTY_PER_UNIT . '-value', $billItem)
                            && ! array_key_exists($record['bill_column_setting_id'] . '-total_per_unit', $billItem)
                        )
                        {
                            $billItems[ $key ][ $record['bill_column_setting_id'] . '-' . BillItemTypeReference::FORMULATED_COLUMN_QTY_PER_UNIT . '-value' ] = 0;
                            $billItems[ $key ][ $record['bill_column_setting_id'] . '-total_per_unit' ] = 0;
                        }

                        if( ! array_key_exists('rate-value', $billItem) )
                        {
                            $billItems[ $key ]['rate-value'] = 0;
                            $billItems[ $key ]['type'] = (string)$billItems[ $key ]['type'];
                        }

                        if( $record['bill_item_id'] == $billItems[ $key ]['id'] )
                        {
                            $quantityPerType = isset( $quantityPerTypes[ $billItem['id'] ][ $record['bill_column_setting_id'] ] ) ? $quantityPerTypes[ $billItem['id'] ][ $record['bill_column_setting_id'] ] : 0;

                            $billItems[ $key ]['rate-value'] = $record['final_value'];

                            $billItems[ $key ][ $record['bill_column_setting_id'] . '-' . BillItemTypeReference::FORMULATED_COLUMN_QTY_PER_UNIT . '-value' ] = $quantityPerType;
                            $billItems[ $key ][ $record['bill_column_setting_id'] . '-total_per_unit' ] = $record['final_value'] * $quantityPerType;
                        }

                        foreach($subConRates as $subConRate)
                        {
                            if( ! array_key_exists($subConRate['company_id'] . '-rate-value', $billItems[ $key ]) and $billItems[ $key ]['type'] != BillItem::TYPE_HEADER and $billItems[ $key ]['type'] != BillItem::TYPE_HEADER_N and $billItems[ $key ]['type'] != BillItem::TYPE_NOID )
                            {
                                $billItems[ $key ][ $subConRate['company_id'] . '-rate-value' ] = 0;
                            }

                            if( $subConRate['bill_item_id'] == $billItems[ $key ]['id'] )
                            {
                                $billItems[ $key ][ $subConRate['company_id'] . '-rate-value' ] = $subConRate['rate'];
                            }

                            $rateValue = isset( $billItems[ $key ][ $subConRate['company_id'] . '-rate-value' ] ) ? $billItems[ $key ][ $subConRate['company_id'] . '-rate-value' ] : 0;

                            $billItems[ $key ][ $subConRate['company_id'] . '-difference_amount' ] = $rateValue - $billItems[ $key ]['rate-value'];
                            $billItems[ $key ][ $subConRate['company_id'] . '-difference_percentage' ] = 0;

                            if( $rateValue != 0 )
                            {
                                $billItems[ $key ][ $subConRate['company_id'] . '-difference_percentage' ] = Utilities::prelimRounding(Utilities::percent($billItems[ $key ][ $subConRate['company_id'] . '-difference_amount' ], $billItems[ $key ]['rate-value']));
                            }
                        }

                        $temporarilyBillItemsContainer[ $billItems[ $key ]['element_id'] ][ $billItems[ $key ]['id'] ] = $billItem;

                        unset( $billItem );
                    }

                    unset( $record, $records[ $recordKey ] );
                }

                // merge bill element(s) with bill item(s)
                foreach($elements as &$element)
                {
                    $data[] = array(
                        'id'          => 'element-' . $element['id'],
                        'bill_ref'    => null,
                        'description' => $element['description'],
                        'type'        => -1,
                        'uom_symbol'  => null,
                    );

                    foreach($temporarilyBillItemsContainer[ $element['id'] ] as &$billItem)
                    {
                        $data[] = $billItem;

                        unset( $billItem );
                    }

                    unset( $temporarilyBillItemsContainer[ $element['id'] ], $element );
                }

                unset( $records );
            }
        }

        array_push($data, array(
            'id'                   => Constants::GRID_LAST_ROW,
            'bill_ref'             => "",
            'priority'             => 0,
            'type'                 => (string)ProjectStructure::getDefaultItemType($bill->BillType->type),
            'grand_total'          => 0,
            'grand_total_quantity' => 0,
            'level'                => 0,
            'uom_id'               => null,
            'uom_symbol'           => null,
        ));

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $data
        ));
    }

    public function executeGetPrintingSelectedItemsRateSummaryForAllTenderer(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $subPackage = Doctrine_Core::getTable('SubPackage')->find($request->getParameter('sid')) and
            $bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('bid')) and
            $project = Doctrine_Core::getTable('ProjectStructure')->find($bill->root_id)
        );

        $billItemIds = json_decode($request->getParameter('item_ids'), true);

        $data = SubPackageTable::getRatesAndTotalBySubPackageAndBillAndItemIds($subPackage, $bill, $billItemIds);

        array_push($data, array(
            'id'                   => Constants::GRID_LAST_ROW,
            'bill_ref'             => "",
            'priority'             => 0,
            'type'                 => (string)ProjectStructure::getDefaultItemType($bill->BillType->type),
            'grand_total'          => 0,
            'grand_total_quantity' => 0,
            'level'                => 0,
            'uom_id'               => null,
            'uom_symbol'           => null,
        ));

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $data
        ));
    }

	// ====================================================================================================================================
	// Report Generator
	// ====================================================================================================================================
    public function executePrintBillSummarySelectedTenderer(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isMethod('POST') and
            $subPackage = Doctrine_Core::getTable('SubPackage')->find($request->getParameter('subPackageId'))
        );

        session_write_close();

        if( empty ( $subPackage->selected_company_id ) )
        {
            throw new InvalidArgumentException('Please select a company before re-printing again.');
        }

        $selectedSubCon = Doctrine_Core::getTable('Company')->find($subPackage->selected_company_id);
        $billIds = json_decode($request->getParameter('selectedRows'), true);
        $billSplFixedArray = new SplFixedArray(1);
        $totalEstimateAmount = 0;
        $totalSelectedContractorAmount = 0;

        $printingPageTitle = $request->getParameter('printingPageTitle');
        $descriptionFormat = $request->getParameter('descriptionFormat');
        $priceFormat = $request->getParameter('priceFormat');
        $printNoCents = ( $request->getParameter('printNoCents') == 't' ) ? true : false;

        if( ! empty( $billIds ) )
        {
            $billArray = SubPackageTable::getBillsBySubPackageAndBillIds($subPackage, $billIds);

            $contractorTotals = SubPackageBillItemRateTable::getBillContractorTotals($subPackage, $billIds);
            $estimateTotals = SubPackageBillItemRateTable::getBillEstimateTotals($subPackage, $billIds);

            foreach($estimateTotals as $billId => $billEstimateAmount)
            {
                $totalEstimateAmount += $billEstimateAmount;
            }

            $billSplFixedArray = new SplFixedArray(count($billArray));//plus 1 for last empty row in grid

            $count = 0;

            foreach($billArray as $billId => $bill)
            {
                $bill = array(
                    'id'         => $billId,
                    'title'      => $bill['title'],
                    'est_amount' => $estimateTotals[ $billId ],
                );

                foreach($contractorTotals as $contractorId => $contractorBillTotals)
                {
                    if( array_key_exists($billId, $contractorBillTotals) )
                    {
                        $amount = $contractorBillTotals[ $billId ];

                        $bill[ 'total_amount-' . $contractorId ] = $amount;
                        $bill[ 'difference_amount-' . $contractorId ] = $amount - $bill['est_amount'];
                        $bill[ 'difference_percentage-' . $contractorId ] = 0;

                        if( $bill['est_amount'] != 0 )
                        {
                            $bill[ 'difference_percentage-' . $contractorId ] = Utilities::prelimRounding(Utilities::percent($bill[ 'difference_amount-' . $contractorId ], $bill['est_amount']));
                        }

                        if( $contractorId == $selectedSubCon->id )
                        {
                            $totalSelectedContractorAmount += $amount;
                        }
                    }
                }

                $billSplFixedArray[ $count ] = $bill;

                unset( $bill );

                $count++;
            }
        }

        $reportGenerator = new sfSubPackageBillSummarySelectedTendererPageGenerator($subPackage, $billSplFixedArray, $descriptionFormat);
        $reportGenerator->setSelectedSubCon($selectedSubCon);

        $pages = $reportGenerator->generatePages();
        $maxRows = $reportGenerator->getMaxRows();
        $currency = $reportGenerator->getCurrency();
        $withoutPrice = false;

        $params = array(
            'disable-smart-shrinking',
            'disable-javascript',
            'no-outline',
            'no-background',
            'enableEscaping' => true,
            'binPath'        => sfConfig::get('app_wkhtmltopdf_bin_path'),
            'margin-top'     => $reportGenerator->getMarginTop(),
            'margin-right'   => $reportGenerator->getMarginRight(),
            'margin-bottom'  => $reportGenerator->getMarginBottom(),
            'margin-left'    => $reportGenerator->getMarginLeft(),
            'page-size'      => $reportGenerator->getPageSize(),
            'orientation'    => $reportGenerator->getOrientation()
        );

        $stylesheet = file_get_contents(sfConfig::get('sf_web_dir') . '/css/printBQ.css');
        $pdfGen = new WkHtmlToPdf($params);
        $totalPage = count($pages) - 1;
        $pageCount = 1;

        if( $pages instanceof SplFixedArray )
        {
            foreach($pages as $page)
            {
                if( empty( $page ) )
                {
                    continue;
                }

                $lastPage = ( $pageCount == $pages->count() - 1 ) ? true : false;

                $layout = $this->getPartial('printReport/pageLayout', array(
                    'stylesheet'    => $stylesheet,
                    'layoutStyling' => $reportGenerator->getLayoutStyling()
                ));

                $billItemsLayoutParams = array(
                    'itemPage'                   => $page,
                    'maxRows'                    => $maxRows + 2,
                    'currency'                   => $currency,
                    'totalEstimateAmt'           => $totalEstimateAmount,
                    'totalSubConAmt'             => $totalSelectedContractorAmount,
                    'lastPage'                   => $lastPage,
                    'selectedSubCon'             => $reportGenerator->subCon,
                    'pageCount'                  => $pageCount,
                    'totalPage'                  => $totalPage,
                    'reportTitle'                => $printingPageTitle,
                    'topLeftRow1'                => '',
                    'topLeftRow2'                => $subPackage->ProjectStructure->title,
                    'botLeftRow1'                => $reportGenerator->getBottomLeftFirstRowHeader(),
                    'botLeftRow2'                => $reportGenerator->getBottomLeftSecondRowHeader(),
                    'descHeader'                 => $reportGenerator->getTableHeaderDescriptionPrefix(),
                    'unitHeader'                 => $reportGenerator->getTableHeaderUnitPrefix(),
                    'rateHeader'                 => $reportGenerator->getTableHeaderRatePrefix(),
                    'qtyHeader'                  => $reportGenerator->getTableHeaderQtyPrefix(),
                    'amtHeader'                  => $reportGenerator->getTableHeaderAmtPrefix(),
                    'toCollection'               => $reportGenerator->getToCollectionPrefix(),
                    'priceFormatting'            => $reportGenerator->generatePriceFormat($priceFormat, $printNoCents),
                    'printNoPrice'               => $withoutPrice,
                    'toggleColumnArrangement'    => $reportGenerator->getToggleColumnArrangement(),
                    'printElementTitle'          => $reportGenerator->getPrintElementTitle(),
                    'printDollarAndCentColumn'   => $reportGenerator->getPrintDollarAndCentColumn(),
                    'currencyFormat'             => $reportGenerator->getCurrencyFormat(),
                    'rateCommaRemove'            => $reportGenerator->getRateCommaRemove(),
                    'qtyCommaRemove'             => $reportGenerator->getQtyCommaRemove(),
                    'amtCommaRemove'             => $reportGenerator->getAmtCommaRemove(),
                    'printAmountOnly'            => $reportGenerator->getPrintAmountOnly(),
                    'printElementInGridOnce'     => $reportGenerator->getPrintElementInGridOnce(),
                    'indentItem'                 => $reportGenerator->getIndentItem(),
                    'printElementInGrid'         => $reportGenerator->getPrintElementInGrid(),
                    'pageNoPrefix'               => $reportGenerator->getPageNoPrefix(),
                    'printDateOfPrinting'        => $reportGenerator->getPrintDateOfPrinting(),
                    'alignElementTitleToTheLeft' => $reportGenerator->getAlignElementToLeft(),
                );

                $layout .= $this->getPartial('bqReportComparisonBill', $billItemsLayoutParams);

                unset( $page );

                $pdfGen->addPage($layout);

                $pageCount++;
            }
        }

        return $pdfGen->send();
    }

    public function executePrintBillSummaryForAllTenderer(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isMethod('POST') and
            $subPackage = Doctrine_Core::getTable('SubPackage')->find($request->getParameter('subPackageId'))
        );

        session_write_close();

        $billIds = json_decode($request->getParameter('selectedRows'), true);
        $billArray = array();
        $newSubCons = array();
        $billSplFixedArray = new SplFixedArray(1);
        $contractorTotals = array();
        $totalEstimateAmount = 0;

        $sortBy = $request->getParameter('sortingType');
        $selectedCompanyId = $subPackage->selected_company_id > 0 ? $subPackage->selected_company_id : -1;
        $selectedSubCon = Doctrine_Core::getTable('Company')->find($selectedCompanyId);
        $subCons = SubPackageCompanyTable::getSubConCompany($subPackage, $selectedCompanyId, $sortBy);

        $printingPageTitle = $request->getParameter('printingPageTitle');
        $descriptionFormat = $request->getParameter('descriptionFormat');
        $priceFormat = $request->getParameter('priceFormat');
        $printNoCents = ( $request->getParameter('printNoCents') == 't' ) ? true : false;

        if( $selectedSubCon )
        {
            $arraySelectedSubCon = $selectedSubCon->toArray();
            $arraySelectedSubCon['selected'] = true;

            array_push($newSubCons, $arraySelectedSubCon);
        }

        array_walk($subCons, function ($subCon) use (&$newSubCons)
        {
            $newSubCons[] = $subCon;
        });

        unset( $selectedSubCon, $subCons );

        if( ! empty( $billIds ) )
        {
            $billArray = SubPackageTable::getBillsBySubPackageAndBillIds($subPackage, $billIds);

            $contractorTotals = SubPackageBillItemRateTable::getBillContractorTotals($subPackage, $billIds);
            $estimateTotals = SubPackageBillItemRateTable::getBillEstimateTotals($subPackage, $billIds);

            foreach($estimateTotals as $billId => $billEstimateAmount)
            {
                $totalEstimateAmount += $billEstimateAmount;
            }

            $billSplFixedArray = new SplFixedArray(count($billArray));//plus 1 for last empty row in grid

            $count = 0;

            foreach($billArray as $billId => $bill)
            {
                $bill = array(
                    'id'         => $billId,
                    'title'      => $bill['title'],
                    'est_amount' => $estimateTotals[ $billId ],
                    'type'       => $bill['type'],
                );

                foreach($contractorTotals as $contractorId => $contractorBillTotals)
                {
                    if( array_key_exists($billId, $contractorBillTotals) )
                    {
                        $amount = $contractorBillTotals[ $billId ];
                        $bill[ $contractorId . '-grand_total' ] = $amount;
                        $tendererCostings[ $contractorId ] = $amount;
                    }
                }

                $billSplFixedArray[ $count ] = $bill;

                unset( $bill );

                $count++;
            }
        }

        unset( $billArray );

        $reportGenerator = new sfSubPackageBillSummaryAllTendererPageGenerator($subPackage, $billSplFixedArray, $descriptionFormat);
        $pages = $reportGenerator->generatePages();
        $maxRows = $reportGenerator->getMaxRows();
        $currency = $reportGenerator->getCurrency();
        $withoutPrice = false;

        $params = array(
            'disable-smart-shrinking',
            'disable-javascript',
            'no-outline',
            'no-background',
            'enableEscaping' => true,
            'binPath'        => sfConfig::get('app_wkhtmltopdf_bin_path'),
            'margin-top'     => $reportGenerator->getMarginTop(),
            'margin-right'   => $reportGenerator->getMarginRight(),
            'margin-bottom'  => $reportGenerator->getMarginBottom(),
            'margin-left'    => $reportGenerator->getMarginLeft(),
            'page-size'      => $reportGenerator->getPageSize(),
            'orientation'    => $reportGenerator->getOrientation()
        );

        $stylesheet = file_get_contents(sfConfig::get('sf_web_dir') . '/css/printBQ.css');
        $pdfGen = new WkHtmlToPdf($params);
        $totalPage = count($pages) - 1;
        $pageCount = 1;

        if( $pages instanceof SplFixedArray )
        {
            foreach($pages as $page)
            {
                if( empty( $page ) )
                {
                    continue;
                }

                $lastPage = ( $pageCount == $pages->count() - 1 ) ? true : false;

                $layout = $this->getPartial('printReport/pageLayout', array(
                    'stylesheet'    => $stylesheet,
                    'layoutStyling' => $reportGenerator->getLayoutStyling()
                ));

                $billItemsLayoutParams = array(
                    'itemPage'                   => $page,
                    'maxRows'                    => $maxRows + 2,
                    'currency'                   => $currency,
                    'totalEstimateAmt'           => $totalEstimateAmount,
                    'lastPage'                   => $lastPage,
                    'subCons'                    => $newSubCons,
                    'subConsBillTotals'          => $contractorTotals,
                    'pageCount'                  => $pageCount,
                    'totalPage'                  => $totalPage,
                    'reportTitle'                => $printingPageTitle,
                    'topLeftRow1'                => '',
                    'topLeftRow2'                => $subPackage->ProjectStructure->title,
                    'botLeftRow1'                => $reportGenerator->getBottomLeftFirstRowHeader(),
                    'botLeftRow2'                => $reportGenerator->getBottomLeftSecondRowHeader(),
                    'descHeader'                 => $reportGenerator->getTableHeaderDescriptionPrefix(),
                    'unitHeader'                 => $reportGenerator->getTableHeaderUnitPrefix(),
                    'rateHeader'                 => $reportGenerator->getTableHeaderRatePrefix(),
                    'qtyHeader'                  => $reportGenerator->getTableHeaderQtyPrefix(),
                    'amtHeader'                  => $reportGenerator->getTableHeaderAmtPrefix(),
                    'toCollection'               => $reportGenerator->getToCollectionPrefix(),
                    'priceFormatting'            => $reportGenerator->generatePriceFormat($priceFormat, $printNoCents),
                    'printNoPrice'               => $withoutPrice,
                    'toggleColumnArrangement'    => $reportGenerator->getToggleColumnArrangement(),
                    'printElementTitle'          => $reportGenerator->getPrintElementTitle(),
                    'printDollarAndCentColumn'   => $reportGenerator->getPrintDollarAndCentColumn(),
                    'currencyFormat'             => $reportGenerator->getCurrencyFormat(),
                    'rateCommaRemove'            => $reportGenerator->getRateCommaRemove(),
                    'qtyCommaRemove'             => $reportGenerator->getQtyCommaRemove(),
                    'amtCommaRemove'             => $reportGenerator->getAmtCommaRemove(),
                    'printAmountOnly'            => $reportGenerator->getPrintAmountOnly(),
                    'printElementInGridOnce'     => $reportGenerator->getPrintElementInGridOnce(),
                    'indentItem'                 => $reportGenerator->getIndentItem(),
                    'printElementInGrid'         => $reportGenerator->getPrintElementInGrid(),
                    'pageNoPrefix'               => $reportGenerator->getPageNoPrefix(),
                    'printDateOfPrinting'        => $reportGenerator->getPrintDateOfPrinting(),
                    'alignElementTitleToTheLeft' => $reportGenerator->getAlignElementToLeft(),
                );

                $layout .= $this->getPartial('bqReportBill', $billItemsLayoutParams);

                unset( $page );

                $pdfGen->addPage($layout);

                $pageCount++;
            }
        }

        return $pdfGen->send();
    }

    public function executePrintItemRateForSelectedTenderer(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isMethod('POST') and
            $subPackage = Doctrine_Core::getTable('SubPackage')->find($request->getParameter('subPackageId')) and
            $bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('billId')) and
            $project = Doctrine_Core::getTable('ProjectStructure')->find($bill->root_id)
        );

        session_write_close();

        $selectedCompanyId = $subPackage->selected_company_id > 0 ? $subPackage->selected_company_id : -1;
        $selectedSubCon = Doctrine_Core::getTable('Company')->find($selectedCompanyId);

        $printingPageTitle = $request->getParameter('printingPageTitle');
        $descriptionFormat = $request->getParameter('descriptionFormat');
        $priceFormat = $request->getParameter('priceFormat');
        $printNoCents = ( $request->getParameter('printNoCents') == 't' ) ? true : false;

        $billItemIds = json_decode($request->getParameter('selectedRows'), true);
        $pageNoPrefix = $bill->BillLayoutSetting->page_no_prefix;
        $projectMainInfo = $project->MainInformation;
        $isPostContract = ( $projectMainInfo->status == ProjectMainInformation::STATUS_POSTCONTRACT );
        $elementIds = array();
        $temporarilyBillItemsContainer = array();
        $quantityPerTypes = array();
        $elements = array();
        $pdo = $subPackage->getTable()->getConnection()->getDbh();

        if( ! empty( $billItemIds ) )
        {
            $records = SubPackageTable::getRatesBySubPackageAndBillAndItemIds($subPackage, $bill, $billItemIds);

            foreach($records as $record)
            {
                $elementIds[ $record['element_id'] ] = $record['element_id'];
            }

            $subConRates = SubPackageBillItemRateTable::getSubConRatesBySubPackageAndItemIds($subPackage, $billItemIds);

            $billRefSelector = 'p.bill_ref_element_no, p.bill_ref_page_no, p.bill_ref_char';
            $postContractJoinTable = null;

            if( $isPostContract )
            {
                $billRefSelector = 'pc.bill_ref_element_no, pc.bill_ref_page_no, pc.bill_ref_char';
                $postContractJoinTable = 'JOIN ' . PostContractBillItemRateTable::getInstance()->getTableName() . " pc ON (pc.bill_item_id = p.id)";
            }

            $stmt = $pdo->prepare("SELECT DISTINCT p.id, p.element_id, p.root_id, p.description, p.type, p.uom_id, uom.symbol AS uom_symbol, p.grand_total, p.grand_total_quantity, p.level, p.priority, p.lft, {$billRefSelector}
			FROM " . BillItemTable::getInstance()->getTableName() . " c
			JOIN " . BillItemTable::getInstance()->getTableName() . " p ON c.lft BETWEEN p.lft AND p.rgt {$postContractJoinTable}
			LEFT JOIN " . UnitOfMeasurementTable::getInstance()->getTableName() . " uom ON p.uom_id = uom.id AND uom.deleted_at IS NULL
			WHERE c.root_id = p.root_id AND c.type != " . BillItem::TYPE_ITEM_NOT_LISTED . "
			AND c.id IN (" . implode(',', $billItemIds) . ")
			AND c.project_revision_deleted_at IS NULL
			AND c.deleted_at IS NULL AND p.project_revision_deleted_at IS NULL AND p.deleted_at IS NULL
			ORDER BY p.element_id, p.priority, p.lft, p.level ASC");

            $stmt->execute();
            $billItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $elements = BillElementTable::getElementsByElementIds($elementIds);

            if( ! empty( $records ) )
            {
                // will start to construct query to get quantity per type
                $getQuantityPerTypes = BillItemTypeReferenceFormulatedColumnTable::getQuantityPerType($records);

                foreach($getQuantityPerTypes as $quantityPerType)
                {
                    $quantityPerTypes[ $quantityPerType['bill_item_id'] ][ $quantityPerType['bill_column_setting_id'] ] = $quantityPerType['value'];

                    unset( $quantityPerType );
                }

                unset( $getQuantityPerTypes );

                // process all the bill item(s) before passing to be merge with element level
                foreach($records as $recordKey => &$record)
                {
                    foreach($billItems as $key => &$billItem)
                    {
                        $billItems[ $key ]['bill_ref'] = BillItemTable::generateBillRef($pageNoPrefix, $billItems[ $key ]['bill_ref_element_no'], $billItems[ $key ]['bill_ref_page_no'], $billItems[ $key ]['bill_ref_char']);

                        if( ! array_key_exists($record['bill_column_setting_id'] . '-' . BillItemTypeReference::FORMULATED_COLUMN_QTY_PER_UNIT . '-value', $billItem)
                            && ! array_key_exists($record['bill_column_setting_id'] . '-total_per_unit', $billItem)
                        )
                        {
                            $billItems[ $key ][ $record['bill_column_setting_id'] . '-' . BillItemTypeReference::FORMULATED_COLUMN_QTY_PER_UNIT . '-value' ] = 0;
                            $billItems[ $key ][ $record['bill_column_setting_id'] . '-total_per_unit' ] = 0;
                        }

                        if( ! array_key_exists('rate-value', $billItem) )
                        {
                            $billItems[ $key ]['rate-value'] = 0;
                            $billItems[ $key ]['type'] = (string)$billItems[ $key ]['type'];
                        }

                        if( $record['bill_item_id'] == $billItems[ $key ]['id'] )
                        {
                            $quantityPerType = isset( $quantityPerTypes[ $billItem['id'] ][ $record['bill_column_setting_id'] ] ) ? $quantityPerTypes[ $billItem['id'] ][ $record['bill_column_setting_id'] ] : 0;

                            $billItems[ $key ]['rate-value'] = $record['final_value'];

                            $billItems[ $key ][ $record['bill_column_setting_id'] . '-' . BillItemTypeReference::FORMULATED_COLUMN_QTY_PER_UNIT . '-value' ] = $quantityPerType;
                            $billItems[ $key ][ $record['bill_column_setting_id'] . '-total_per_unit' ] = $record['final_value'] * $quantityPerType;
                        }

                        foreach($subConRates as $subConRate)
                        {
                            if( ! array_key_exists($subConRate['company_id'] . '-rate-value', $billItems[ $key ]) and $billItems[ $key ]['type'] != BillItem::TYPE_HEADER and $billItems[ $key ]['type'] != BillItem::TYPE_HEADER_N and $billItems[ $key ]['type'] != BillItem::TYPE_NOID )
                            {
                                $billItems[ $key ][ $subConRate['company_id'] . '-rate-value' ] = 0;
                            }

                            if( $subConRate['bill_item_id'] == $billItems[ $key ]['id'] )
                            {
                                $billItems[ $key ][ $subConRate['company_id'] . '-rate-value' ] = $subConRate['rate'];
                            }

                            $rateValue = isset( $billItems[ $key ][ $subConRate['company_id'] . '-rate-value' ] ) ? $billItems[ $key ][ $subConRate['company_id'] . '-rate-value' ] : 0;

                            $billItems[ $key ][ $subConRate['company_id'] . '-difference_amount' ] = $rateValue - $billItems[ $key ]['rate-value'];
                            $billItems[ $key ][ $subConRate['company_id'] . '-difference_percentage' ] = 0;

                            if( $rateValue != 0 )
                            {
                                $billItems[ $key ][ $subConRate['company_id'] . '-difference_percentage' ] = Utilities::prelimRounding(Utilities::percent($billItems[ $key ][ $subConRate['company_id'] . '-difference_amount' ], $billItems[ $key ]['rate-value']));
                            }
                        }

                        $temporarilyBillItemsContainer[ $billItems[ $key ]['element_id'] ][ $billItems[ $key ]['id'] ] = $billItem;

                        unset( $billItem );
                    }

                    unset( $record, $records[ $recordKey ] );
                }

                unset( $records );
            }
        }

        $reportGenerator = new sfSubPackageItemRateSelectedTendererPageGenerator($subPackage, $bill, $elements, $temporarilyBillItemsContainer, $descriptionFormat);

        $reportGenerator->setSubCon($selectedSubCon);

        $pages = $reportGenerator->generatePages();
        $maxRows = $reportGenerator->getMaxRows();
        $currency = $reportGenerator->getCurrency();
        $withoutPrice = false;

        $params = array(
            'disable-smart-shrinking',
            'disable-javascript',
            'no-outline',
            'no-background',
            'enableEscaping' => true,
            'binPath'        => sfConfig::get('app_wkhtmltopdf_bin_path'),
            'margin-top'     => $reportGenerator->getMarginTop(),
            'margin-right'   => $reportGenerator->getMarginRight(),
            'margin-bottom'  => $reportGenerator->getMarginBottom(),
            'margin-left'    => $reportGenerator->getMarginLeft(),
            'page-size'      => $reportGenerator->getPageSize(),
            'orientation'    => $reportGenerator->getOrientation()
        );

        $stylesheet = file_get_contents(sfConfig::get('sf_web_dir') . '/css/printBQ.css');
        $pdfGen = new WkHtmlToPdf($params);
        $pageCount = 1;

        foreach($pages as $page)
        {
            for($i = 1; $i <= $page['item_pages']->count(); $i++)
            {
                if( $page['item_pages'] instanceof SplFixedArray and $page['item_pages']->offsetExists($i) )
                {
                    $printGrandTotal = ( ( $i + 1 ) == $page['item_pages']->count() ) ? true : false;

                    $layout = $this->getPartial('printReport/pageLayout', array(
                        'stylesheet'    => $stylesheet,
                        'layoutStyling' => $reportGenerator->getLayoutStyling()
                    ));

                    $billItemsLayoutParams = array(
                        'itemPage'                   => $page['item_pages']->offsetGet($i),
                        'maxRows'                    => $maxRows + 2,
                        'printGrandTotal'            => $printGrandTotal,
                        'currency'                   => $currency,
                        'elementId'                  => $page['id'],
                        'elementHeaderDescription'   => $page['description'],
                        'elementCount'               => $page['element_count'],
                        'estimateElementTotal'       => $reportGenerator->estimateElementTotal,
                        'estimateElementSubConTotal' => $reportGenerator->estimateElementSubConTotal,
                        'selectedSubCon'             => $reportGenerator->subCon,
                        'pageCount'                  => $pageCount,
                        'totalPage'                  => $reportGenerator->totalPage,
                        'reportTitle'                => $printingPageTitle,
                        'topLeftRow1'                => ( strlen($reportGenerator->printSettings['layoutSetting']['pageNoPrefix']) ) ? 'Prefix: ' . $reportGenerator->printSettings['layoutSetting']['pageNoPrefix'] : '',
                        'topLeftRow2'                => $bill->title,
                        'botLeftRow1'                => $reportGenerator->getBottomLeftFirstRowHeader(),
                        'botLeftRow2'                => $reportGenerator->getBottomLeftSecondRowHeader(),
                        'descHeader'                 => $reportGenerator->getTableHeaderDescriptionPrefix(),
                        'unitHeader'                 => $reportGenerator->getTableHeaderUnitPrefix(),
                        'rateHeader'                 => $reportGenerator->getTableHeaderRatePrefix(),
                        'qtyHeader'                  => $reportGenerator->getTableHeaderQtyPrefix(),
                        'amtHeader'                  => $reportGenerator->getTableHeaderAmtPrefix(),
                        'toCollection'               => $reportGenerator->getToCollectionPrefix(),
                        'priceFormatting'            => $reportGenerator->generatePriceFormat($priceFormat, $printNoCents),
                        'printNoPrice'               => $withoutPrice,
                        'toggleColumnArrangement'    => $reportGenerator->getToggleColumnArrangement(),
                        'printElementTitle'          => $reportGenerator->getPrintElementTitle(),
                        'printDollarAndCentColumn'   => $reportGenerator->getPrintDollarAndCentColumn(),
                        'currencyFormat'             => $reportGenerator->getCurrencyFormat(),
                        'rateCommaRemove'            => $reportGenerator->getRateCommaRemove(),
                        'qtyCommaRemove'             => $reportGenerator->getQtyCommaRemove(),
                        'amtCommaRemove'             => $reportGenerator->getAmtCommaRemove(),
                        'printAmountOnly'            => $reportGenerator->getPrintAmountOnly(),
                        'printElementInGridOnce'     => $reportGenerator->getPrintElementInGridOnce(),
                        'indentItem'                 => $reportGenerator->getIndentItem(),
                        'printElementInGrid'         => $reportGenerator->getPrintElementInGrid(),
                        'pageNoPrefix'               => $reportGenerator->getPageNoPrefix(),
                        'printDateOfPrinting'        => $reportGenerator->getPrintDateOfPrinting(),
                        'alignElementTitleToTheLeft' => $reportGenerator->getAlignElementToLeft(),
                    );

                    $layout .= $this->getPartial('bqReportItemRateSelectedTenderer', $billItemsLayoutParams);

                    $page['item_pages']->offsetUnset($i);

                    $pdfGen->addPage($layout);

                    $pageCount++;
                }
            }
        }

        return $pdfGen->send();
    }

    public function executePrintItemRateAndTotalForAllTenderer(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isMethod('POST') and
            $subPackage = Doctrine_Core::getTable('SubPackage')->find($request->getParameter('subPackageId')) and
            $bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('billId')) and
            $project = Doctrine_Core::getTable('ProjectStructure')->find($bill->root_id)
        );

        session_write_close();

        $billItemIds = json_decode($request->getParameter('selectedRows'), true);
        $projectMainInfo = $project->MainInformation;
        $isPostContract = ( $projectMainInfo->status == ProjectMainInformation::STATUS_POSTCONTRACT );
        $elements = array();
        $temporarilyBillItemsContainer = array();
        $newSubCons = array();

        $sortBy = $request->getParameter('sortingType');
        $selectedCompanyId = $subPackage->selected_company_id > 0 ? $subPackage->selected_company_id : -1;
        $selectedSubCon = Doctrine_Core::getTable('Company')->find($selectedCompanyId);
        $subCons = SubPackageCompanyTable::getSubConCompany($subPackage, $selectedCompanyId, $sortBy);

        $printingPageTitle = $request->getParameter('printingPageTitle');
        $descriptionFormat = $request->getParameter('descriptionFormat');
        $priceFormat = $request->getParameter('priceFormat');
        $printNoCents = ( $request->getParameter('printNoCents') == 't' ) ? true : false;

        if( $selectedSubCon )
        {
            $arraySelectedSubCon = $selectedSubCon->toArray();
            $arraySelectedSubCon['selected'] = true;

            array_push($newSubCons, $arraySelectedSubCon);
        }

        array_walk($subCons, function ($subCon) use (&$newSubCons)
        {
            $newSubCons[] = $subCon;
        });

        unset( $selectedSubCon, $subCons );

        $reportGenerator = new sfSubPackageItemRateAllTendererPageGenerator($subPackage, $bill, $elements, $temporarilyBillItemsContainer, $descriptionFormat, $billItemIds);
        $reportGenerator->setSubCons($newSubCons);

        $pages = $reportGenerator->generatePages();

        $maxRows = $reportGenerator->getMaxRows();
        $currency = $reportGenerator->getCurrency();
        $withoutPrice = false;

        $params = array(
            'disable-smart-shrinking',
            'disable-javascript',
            'no-outline',
            'no-background',
            'enableEscaping' => true,
            'binPath'        => sfConfig::get('app_wkhtmltopdf_bin_path'),
            'margin-top'     => $reportGenerator->getMarginTop(),
            'margin-right'   => $reportGenerator->getMarginRight(),
            'margin-bottom'  => $reportGenerator->getMarginBottom(),
            'margin-left'    => $reportGenerator->getMarginLeft(),
            'page-size'      => $reportGenerator->getPageSize(),
            'orientation'    => $reportGenerator->getOrientation()
        );

        $stylesheet = file_get_contents(sfConfig::get('sf_web_dir') . '/css/printBQ.css');
        $pdfGen = new WkHtmlToPdf($params);
        $pageCount = 1;

        foreach($pages as $elementId => $page)
        {
            for($i = 1; $i <= $page['item_pages']->count(); $i++)
            {
                if( $page['item_pages'] instanceof SplFixedArray and $page['item_pages']->offsetExists($i) )
                {
                    $printGrandTotal = ( ( $i + 1 ) == $page['item_pages']->count() ) ? true : false;

                    $layout = $this->getPartial('printReport/pageLayout', array(
                        'stylesheet'    => $stylesheet,
                        'layoutStyling' => $reportGenerator->getLayoutStyling()
                    ));

                    $billItemsLayoutParams = array(
                        'itemPage'                   => $page['item_pages']->offsetGet($i),
                        'maxRows'                    => $maxRows + 2,
                        'printGrandTotal'            => $printGrandTotal,
                        'currency'                   => $currency,
                        'elementId'                  => $page['id'],
                        'elementHeaderDescription'   => $page['description'],
                        'elementCount'               => $page['element_count'],
                        'estimateElementTotal'       => $reportGenerator->estimateElementTotal,
                        'contractorElementTotals'    => $reportGenerator->contractorElementTotals,
                        'subCons'                    => $reportGenerator->subCons,
                        'subConRates'                => $reportGenerator->getContractorRates(),
                        'printQty'                   => true,
                        'pageCount'                  => $pageCount,
                        'totalPage'                  => $reportGenerator->totalPage,
                        'reportTitle'                => $printingPageTitle,
                        'topLeftRow1'                => ( strlen($reportGenerator->printSettings['layoutSetting']['pageNoPrefix']) ) ? 'Prefix: ' . $reportGenerator->printSettings['layoutSetting']['pageNoPrefix'] : '',
                        'topLeftRow2'                => $bill->title,
                        'botLeftRow1'                => $reportGenerator->getBottomLeftFirstRowHeader(),
                        'botLeftRow2'                => $reportGenerator->getBottomLeftSecondRowHeader(),
                        'descHeader'                 => $reportGenerator->getTableHeaderDescriptionPrefix(),
                        'unitHeader'                 => $reportGenerator->getTableHeaderUnitPrefix(),
                        'rateHeader'                 => $reportGenerator->getTableHeaderRatePrefix(),
                        'qtyHeader'                  => $reportGenerator->getTableHeaderQtyPrefix(),
                        'amtHeader'                  => $reportGenerator->getTableHeaderAmtPrefix(),
                        'toCollection'               => $reportGenerator->getToCollectionPrefix(),
                        'priceFormatting'            => $reportGenerator->generatePriceFormat($priceFormat, $printNoCents),
                        'printNoPrice'               => $withoutPrice,
                        'toggleColumnArrangement'    => $reportGenerator->getToggleColumnArrangement(),
                        'printElementTitle'          => $reportGenerator->getPrintElementTitle(),
                        'printDollarAndCentColumn'   => $reportGenerator->getPrintDollarAndCentColumn(),
                        'currencyFormat'             => $reportGenerator->getCurrencyFormat(),
                        'rateCommaRemove'            => $reportGenerator->getRateCommaRemove(),
                        'qtyCommaRemove'             => $reportGenerator->getQtyCommaRemove(),
                        'amtCommaRemove'             => $reportGenerator->getAmtCommaRemove(),
                        'printAmountOnly'            => $reportGenerator->getPrintAmountOnly(),
                        'printElementInGridOnce'     => $reportGenerator->getPrintElementInGridOnce(),
                        'indentItem'                 => $reportGenerator->getIndentItem(),
                        'printElementInGrid'         => $reportGenerator->getPrintElementInGrid(),
                        'pageNoPrefix'               => $reportGenerator->getPageNoPrefix(),
                        'printDateOfPrinting'        => $reportGenerator->getPrintDateOfPrinting(),
                        'alignElementTitleToTheLeft' => $reportGenerator->getAlignElementToLeft(),
                    );

                    $layout .= $this->getPartial('bqReportItemRateAndTotalAllTenderers', $billItemsLayoutParams);

                    $page['item_pages']->offsetUnset($i);

                    $pdfGen->addPage($layout);

                    $pageCount++;
                }
            }
        }

        return $pdfGen->send();
    }
	// ====================================================================================================================================

}