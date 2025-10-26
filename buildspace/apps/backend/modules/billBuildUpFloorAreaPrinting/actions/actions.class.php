<?php

/**
 * billBuildUpFloorAreaPrinting actions.
 *
 * @package    buildspace
 * @subpackage billBuildUpFloorAreaPrinting
 * @author     1337 developers
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class billBuildUpFloorAreaPrintingActions extends BaseActions
{

	public function executePrintFloorAreaReport(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless(
			$request->isMethod('POST') AND
			$billColumnSetting = Doctrine_Core::getTable('BillColumnSetting')->find($request->getParameter('billColumnSettingId')) AND
			$project = Doctrine_Core::getTable('ProjectStructure')->find($billColumnSetting->ProjectStructure->root_id)
		);

		$items                 = array();
		$formulatedColumnNames = Utilities::getAllFormulatedColumnConstants('BillBuildUpFloorAreaItem');
		$priceFormat           = $request->getParameter('priceFormat');
		$printNoCents          = ($request->getParameter('printNoCents') == 't') ? true : false;

		$buildUpFloorAreaItems = DoctrineQuery::create()
		->from('BillBuildUpFloorAreaItem i')
		->where('i.bill_column_setting_id = ?', $billColumnSetting->id)
		->addOrderBy('i.priority ASC')
		->execute();

		foreach($buildUpFloorAreaItems as $buildUpFloorAreaItem)
		{
			$item                = array();
			$item['id']          = $buildUpFloorAreaItem->id;
			$item['description'] = $buildUpFloorAreaItem->description;
			$item['sign']        = (string) $buildUpFloorAreaItem->sign;
			$item['sign_symbol'] = $buildUpFloorAreaItem->getSigntext();
			$item['total']       = $buildUpFloorAreaItem->calculateTotal();

			foreach($formulatedColumnNames as $constant)
			{
				$formulatedColumn                      = $buildUpFloorAreaItem->getFormulatedColumnByName($constant);
				$finalValue                            = $formulatedColumn ? $formulatedColumn->final_value : 0;
				$item[$constant.'-final_value']        = $finalValue;
				$item[$constant.'-value']              = $formulatedColumn ? $formulatedColumn->value : '';
				$item[$constant.'-has_cell_reference'] = $formulatedColumn ? $formulatedColumn->hasCellReference() : false;
				$item[$constant.'-has_formula']        = $formulatedColumn ? $formulatedColumn->hasFormula() : false;
			}

			$items[] = $item;

			unset($item, $buildUpFloorAreaItem);
		}

		unset($buildUpFloorAreaItems);

		// will use a generator to generate GFA's printout and then return
		// to be viewed as pdf format
		$reportPrintGenerator = new sfFloorAreaPrintOutGenerator($billColumnSetting, $items);
		$reportPrintGenerator->setPageFormat();
		$reportPrintGenerator->setDescriptionFormat($request->getParameter('descriptionFormat'));
		$pages                = $reportPrintGenerator->generatePages();
		$withoutPrice         = false;

		$stylesheet = $this->getBQStyling();

		$printingPageTitle = $request->getParameter('printingPageTitle');
		$maxRows           = $reportPrintGenerator->getMaxRows();
		$currency          = $reportPrintGenerator->getCurrency();
		$totalPage         = count($pages) - 1;
		$pageCount         = 1;
		$pdfGen            = $this->createNewPDFGenerator($reportPrintGenerator);

		if($pages instanceof SplFixedArray)
		{
			foreach($pages as $key => $page)
			{
				$lastPage = ($pageCount == $pages->count() - 1) ? true : false;

				if(count($page))
				{
					$layout = $this->getPartial('printReport/pageLayout', array(
						'stylesheet'    => $stylesheet,
						'layoutStyling' => $reportPrintGenerator->getLayoutStyling()
					));

					$billItemsLayoutParams = array(
						'summary'                    => $billColumnSetting->getBuildUpFloorAreaSummaries()->toArray(),
						'itemPage'                   => $page,
						'maxRows'                    => $maxRows + 2,
						'currency'                   => $currency,
						'lastPage'                   => $lastPage,
						'pageCount'                  => $pageCount,
						'totalPage'                  => $totalPage,
						'reportTitle'                => $printingPageTitle,
						'headerDescription'          => NULL,
						'topLeftRow1'                => $project->title,
						'topLeftRow2'                => "{$billColumnSetting->ProjectStructure->title} > {$billColumnSetting->name}",
						'botLeftRow1'                => $reportPrintGenerator->getBottomLeftFirstRowHeader(),
						'botLeftRow2'                => $reportPrintGenerator->getBottomLeftSecondRowHeader(),
						'descHeader'                 => $reportPrintGenerator->getTableHeaderDescriptionPrefix(),
						'unitHeader'                 => $reportPrintGenerator->getTableHeaderUnitPrefix(),
						'rateHeader'                 => $reportPrintGenerator->getTableHeaderRatePrefix(),
						'qtyHeader'                  => $reportPrintGenerator->getTableHeaderQtyPrefix(),
						'amtHeader'                  => $reportPrintGenerator->getTableHeaderAmtPrefix(),
						'toCollection'               => $reportPrintGenerator->getToCollectionPrefix(),
						'priceFormatting'            => $reportPrintGenerator->generatePriceFormat($priceFormat, $printNoCents),
						'printNoPrice'               => $withoutPrice,
						'toggleColumnArrangement'    => $reportPrintGenerator->getToggleColumnArrangement(),
						'printElementTitle'          => $reportPrintGenerator->getPrintElementTitle(),
						'printDollarAndCentColumn'   => $reportPrintGenerator->getPrintDollarAndCentColumn(),
						'currencyFormat'             => $reportPrintGenerator->getCurrencyFormat(),
						'rateCommaRemove'            => $reportPrintGenerator->getRateCommaRemove(),
						'qtyCommaRemove'             => $reportPrintGenerator->getQtyCommaRemove(),
						'amtCommaRemove'             => $reportPrintGenerator->getAmtCommaRemove(),
						'printAmountOnly'            => $reportPrintGenerator->getPrintAmountOnly(),
						'printElementInGridOnce'     => $reportPrintGenerator->getPrintElementInGridOnce(),
						'indentItem'                 => $reportPrintGenerator->getIndentItem(),
						'printElementInGrid'         => $reportPrintGenerator->getPrintElementInGrid(),
						'pageNoPrefix'               => $reportPrintGenerator->getPageNoPrefix(),
						'printDateOfPrinting'        => $reportPrintGenerator->getPrintDateOfPrinting(),
						'alignElementTitleToTheLeft' => $reportPrintGenerator->getAlignElementToLeft(),
					);

					$layout .= $this->getPartial('floorAreaReportsTemplate', $billItemsLayoutParams);

					unset($page);

					$pdfGen->addPage($layout);

					$pageCount++;
				}
			}
		}

		return $pdfGen->send();
	}

	public function executeExportFloorAreaReport(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless(
			$request->isMethod('POST') AND
			$billColumnSetting = Doctrine_Core::getTable('BillColumnSetting')->find($request->getParameter('billColumnSettingId')) AND
			$project = Doctrine_Core::getTable('ProjectStructure')->find($billColumnSetting->ProjectStructure->root_id)
		);

		$items                 = array();
		$formulatedColumnNames = Utilities::getAllFormulatedColumnConstants('BillBuildUpFloorAreaItem');
		$printNoCents          = ($request->getParameter('printNoCents') == 't') ? true : false;

		$buildUpFloorAreaItems = DoctrineQuery::create()
		->from('BillBuildUpFloorAreaItem i')
		->where('i.bill_column_setting_id = ?', $billColumnSetting->id)
		->addOrderBy('i.priority ASC')
		->execute();

		foreach($buildUpFloorAreaItems as $buildUpFloorAreaItem)
		{
			$item                = array();
			$item['id']          = $buildUpFloorAreaItem->id;
			$item['description'] = $buildUpFloorAreaItem->description;
			$item['sign']        = (string) $buildUpFloorAreaItem->sign;
			$item['sign_symbol'] = $buildUpFloorAreaItem->getSigntext();
			$item['total']       = $buildUpFloorAreaItem->calculateTotal();

			foreach($formulatedColumnNames as $constant)
			{
				$formulatedColumn                      = $buildUpFloorAreaItem->getFormulatedColumnByName($constant);
				$finalValue                            = $formulatedColumn ? $formulatedColumn->final_value : 0;
				$item[$constant.'-final_value']        = $finalValue;
				$item[$constant.'-value']              = $formulatedColumn ? $formulatedColumn->value : '';
				$item[$constant.'-has_cell_reference'] = $formulatedColumn ? $formulatedColumn->hasCellReference() : false;
				$item[$constant.'-has_formula']        = $formulatedColumn ? $formulatedColumn->hasFormula() : false;
			}

			$items[] = $item;

			unset($item, $buildUpFloorAreaItem);
		}

		unset($buildUpFloorAreaItems);

		$reportPrintGenerator = new sfFloorAreaPrintOutGenerator($billColumnSetting, $items);
		$reportPrintGenerator->setPageFormat();
		$reportPrintGenerator->setDescriptionFormat($request->getParameter('descriptionFormat'));

		$pages             = $reportPrintGenerator->generatePages();
		$printingPageTitle = $request->getParameter('printingPageTitle');
		$totalPage         = count($pages) - 1;
		$topLeftTitle      = $project->title;

		$sfItemExport = new sfFloorAreaExportExcelGenerator(
			$project,
			$billColumnSetting->BuildUpFloorAreaSummaries,
			$printingPageTitle,
			$reportPrintGenerator->getPrintSettings()
		);

		$sfItemExport->process($pages, false, $printingPageTitle, $topLeftTitle, $billColumnSetting->ProjectStructure->title.' > '.$billColumnSetting->name, $printNoCents, $totalPage);

		return $this->sendExportExcelHeader($sfItemExport->fileInfo['filename'], $sfItemExport->savePath.DIRECTORY_SEPARATOR.$sfItemExport->fileInfo['filename'].$sfItemExport->fileInfo['extension']);
	}

}
