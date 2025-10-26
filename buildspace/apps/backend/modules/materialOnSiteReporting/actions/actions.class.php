<?php

/**
 * materialOnSiteReporting actions.
 *
 * @package    buildspace
 * @subpackage materialOnSiteReporting
 * @author     1337 developers
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class materialOnSiteReportingActions extends BaseActions {

	public function executeGetAffectedItems(sfWebRequest $request)
	{
		$this->forward404Unless(
			$request->isXmlHttpRequest() AND
			$request->hasParameter('mos_ids') AND
			$project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('pid'))
		);

		$data   = array();
		$mosIds = json_decode($request->getParameter('mos_ids'), true);
		$pdo    = $project->getTable()->getConnection()->getDbh();

		// return empty array of variationOfOrderId's information so that the frontend can process it
		foreach ( $mosIds as $mosId )
		{
			$data[$mosId] = array();
		}

		if ( !empty( $mosIds ) )
		{
			$stmt = $pdo->prepare("SELECT i.id, i.material_on_site_id FROM " . MaterialOnSiteItemTable::getInstance()->getTableName() . " i
			WHERE i.material_on_site_id IN (" . implode(',', $mosIds) . ") AND i.deleted_at IS NULL
			ORDER BY i.priority, i.lft, i.level");

			$stmt->execute();

			foreach ( $stmt->fetchAll(PDO::FETCH_ASSOC) as $mosItem )
			{
				$data[$mosItem['material_on_site_id']][] = $mosItem['id'];
			}
		}

		return $this->renderJson($data);
	}

	public function executeGetAffectedMOS(sfWebRequest $request)
	{
		$this->forward404Unless(
			$request->isXmlHttpRequest() AND
			$request->hasParameter('item_ids') AND
			$project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('pid'))
		);

		$data    = array();
		$itemIds = json_decode($request->getParameter('item_ids'), true);
		$pdo     = $project->getTable()->getConnection()->getDbh();

		if ( !empty( $itemIds ) )
		{
			$stmt = $pdo->prepare("SELECT i.id, i.material_on_site_id FROM " . MaterialOnSiteItemTable::getInstance()->getTableName() . " i
			WHERE i.id IN (" . implode(',', $itemIds) . ") AND i.deleted_at IS NULL
			ORDER BY i.priority, i.lft, i.level");

			$stmt->execute();

			foreach ( $stmt->fetchAll(PDO::FETCH_ASSOC) as $mosItem )
			{
				$data[$mosItem['material_on_site_id']][] = $mosItem['id'];
			}
		}

		return $this->renderJson($data);
	}

	public function executeGetPrintPreviewSelectedItems(sfWebRequest $request)
	{
		$this->forward404Unless(
			$request->isXmlHttpRequest() and
			$project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('pid'))
		);

		$data = array();

		$selectedItemIds = $request->getParameter('item_ids', json_encode(array()));

		$items = MaterialOnSiteItemTable::getItemsByIds($project, $selectedItemIds);

		if ( !empty( $items ) )
		{
			$materialOnSites = array();
			$newItems        = array();

			foreach ( $items as $item )
			{
				$mosId                   = $item['material_on_site_id'];
				$materialOnSites[$mosId] = $item['material_on_site_name'];

				$newItems[$mosId][] = $item;
			}

			foreach ( $materialOnSites as $mosId => $mosDescription )
			{
				$data[] = array(
					'id'            => 'material_on_site-' . $mosId,
					'description'   => $mosDescription,
					'type'          => 0,
					'uom_id'        => '-1',
					'uom_symbol'    => '',
					'delivered_qty' => 0,
					'used_qty'      => 0,
					'balance_qty'   => 0,
					'amount'        => 0,
					'level'         => 0,
					'rate-value'    => 0,
				);

				foreach ( $newItems[$mosId] as $currentItem )
				{
					$data[] = $currentItem;
				}
			}
		}

		array_push($data, array(
			'id'            => Constants::GRID_LAST_ROW,
			'description'   => '',
			'type'          => (string) ResourceItem::TYPE_WORK_ITEM,
			'uom_id'        => '-1',
			'uom_symbol'    => '',
			'delivered_qty' => 0,
			'used_qty'      => 0,
			'balance_qty'   => 0,
			'amount'        => 0,
			'level'         => 0,
			'rate-value'    => 0,
		));

		return $this->renderJson(array(
			'identifier' => 'id',
			'items'      => $data,
		));
	}

	public function executePrintSelectedItems(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless(
			$project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('projectId'))
		);

		session_write_close();

		$selectedItemIds   = $request->getPostParameter('selectedRows', json_encode(array()));
		$descriptionFormat = $request->getParameter('descriptionFormat');
		$priceFormat       = $request->getParameter('priceFormat');
		$printNoCents      = ( $request->getParameter('printNoCents') == 't' ) ? true : false;
		$withoutPrice      = false;
		$stylesheet        = $this->getBQStyling();
		$withSignature     = ( $request->getParameter('withSignature') == 't' ) ? true : false;

		$items = MaterialOnSiteItemTable::getItemsByIds($project, $selectedItemIds);

		if ( empty( $items ) )
		{
			$this->message     = 'Error';
			$this->explanation = 'Nothing can be printed because there were no selected item(s) detected.';

			$this->setTemplate('nothingToPrint');

			return sfView::ERROR;
		}

		$voFooterSettings = Doctrine_Core::getTable('VoFooterDefaultSetting')->find(1);
		$materialOnSites  = array();
		$newItems         = array();

		foreach ( $items as $item )
		{
			$mosId                   = $item['material_on_site_id'];
			$materialOnSites[$mosId] = $item['material_on_site_name'];

			$newItems[$mosId][] = $item;
		}

		$printSettings = $project->MaterialOnSitePrintSetting;

		// will take the data variable and then pump it into report generator
		$reportPrintGenerator = new sfMaterialOnSiteNormalItemReportGenerator($descriptionFormat);
		$pageCount            = 1;

		$reportPrintGenerator->setOrientationAndSize('portrait');
		$reportPrintGenerator->setWithSignature($withSignature);
		$reportPrintGenerator->setMOSPrintSettings($printSettings);

		$pdfGen = $this->createNewPDFGenerator($reportPrintGenerator);

		foreach ( $materialOnSites as $mosId => $mosDescription )
		{
			$mosPageCount = 1;

			$mos = Doctrine_Core::getTable('MaterialOnSite')->find($mosId);

			$reportPrintGenerator->setItems($newItems[$mosId]);

			$pages   = $reportPrintGenerator->generatePages();
			$maxRows = $reportPrintGenerator->getMaxRows();

			if ( !( $pages instanceof SplFixedArray ) )
			{
				continue;
			}

			foreach ( $pages as $page )
			{
				if ( empty( $page ) )
				{
					continue;
				}

				$layout = $this->getPartial('printReport/pageLayout', array(
					'stylesheet'    => $stylesheet,
					'layoutStyling' => $reportPrintGenerator->getLayoutStyling()
				));

				$billItemsLayoutParams = array(
					'materialOnSitePrintSetting' => $printSettings,
					'itemPage'                   => $page,
					'totalRate'                  => 0,
					'maxRows'                    => $maxRows + 2,
					'pageCount'                  => $pageCount,
					'billDescription'            => null,
					'columnDescription'          => null,
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
					'reportPrintGenerator'       => $reportPrintGenerator,
					'isLastPage'                 => ( $mosPageCount == $pages->count() ),
					'left_text'                  => $voFooterSettings->left_text,
					'right_text'                 => $voFooterSettings->right_text,
					'currency'                   => $project->MainInformation->Currency,
					'reduction_percentage'       => $mos->reduction_percentage,
					'withSignature'              => $withSignature,
					'mosTotal'                   => $reportPrintGenerator->mosTotal,
				);

				$layout .= $this->getPartial('mosNormalItemReport', $billItemsLayoutParams);

				$pdfGen->addPage($layout);

				unset( $layout, $page );

				$pageCount ++;
				$mosPageCount ++;
			}
		}

		return $pdfGen->send();
	}

	public function executePrintSelectedItemsByAmount(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless(
			$project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('projectId'))
		);

		session_write_close();

		$selectedItemIds   = $request->getPostParameter('selectedRows', json_encode(array()));
		$descriptionFormat = $request->getParameter('descriptionFormat');
		$priceFormat       = $request->getParameter('priceFormat');
		$printNoCents      = ( $request->getParameter('printNoCents') == 't' ) ? true : false;
		$stylesheet        = $this->getBQStyling();
		$withSignature     = ( $request->getParameter('withSignature') == 't' ) ? true : false;

		$items = MaterialOnSiteItemTable::getItemsByIds($project, $selectedItemIds);

		if ( empty( $items ) )
		{
			$this->message     = 'Error';
			$this->explanation = 'Nothing can be printed because there were no selected item(s) detected.';

			$this->setTemplate('nothingToPrint');

			return sfView::ERROR;
		}

		$voFooterSettings = Doctrine_Core::getTable('VoFooterDefaultSetting')->find(1);
		$materialOnSites  = array();
		$newItems         = array();

		foreach ( $items as $item )
		{
			$mosId                   = $item['material_on_site_id'];
			$materialOnSites[$mosId] = $item['material_on_site_name'];

			$newItems[$mosId][] = $item;
		}

		$printSettings = $project->MaterialOnSitePrintSetting;

		// will take the data variable and then pump it into report generator
		$reportPrintGenerator = new sfMaterialOnSiteItemByAmountReportGenerator($descriptionFormat);
		$pageCount            = 1;

		$reportPrintGenerator->setOrientationAndSize('portrait');
		$reportPrintGenerator->setWithSignature($withSignature);
		$reportPrintGenerator->setMOSPrintSettings($printSettings);

		$pdfGen = $this->createNewPDFGenerator($reportPrintGenerator);

		foreach ( $materialOnSites as $mosId => $mosDescription )
		{
			$mosPageCount = 1;

			$mos = Doctrine_Core::getTable('MaterialOnSite')->find($mosId);

			$reportPrintGenerator->setItems($newItems[$mosId]);

			$pages   = $reportPrintGenerator->generatePages();
			$maxRows = $reportPrintGenerator->getMaxRows();

			if ( !( $pages instanceof SplFixedArray ) )
			{
				continue;
			}

			foreach ( $pages as $page )
			{
				if ( empty( $page ) )
				{
					continue;
				}

				$layout = $this->getPartial('printReport/pageLayout', array(
					'stylesheet'    => $stylesheet,
					'layoutStyling' => $reportPrintGenerator->getLayoutStyling()
				));

				$billItemsLayoutParams = array(
					'materialOnSitePrintSetting' => $printSettings,
					'itemPage'                   => $page,
					'maxRows'                    => $maxRows + 2,
					'pageCount'                  => $pageCount,
					'priceFormatting'            => $reportPrintGenerator->generatePriceFormat($priceFormat, $printNoCents),
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
					'reportPrintGenerator'       => $reportPrintGenerator,
					'isLastPage'                 => ( $mosPageCount == $pages->count() ),
					'projectTitle'               => $project->title,
					'left_text'                  => $voFooterSettings->left_text,
					'right_text'                 => $voFooterSettings->right_text,
					'currency'                   => $project->MainInformation->Currency,
					'reduction_percentage'       => $mos->reduction_percentage,
					'withSignature'              => $withSignature,
					'mosDescription'             => $mosDescription,
					'mosTotal'                   => $reportPrintGenerator->mosTotal,
				);

				$layout .= $this->getPartial('mosItemByAmountReport', $billItemsLayoutParams);

				$pdfGen->addPage($layout);

				unset( $layout, $page );

				$pageCount ++;
				$mosPageCount ++;
			}
		}

		return $pdfGen->send();
	}

	public function executeExportExcelSelectedItems(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless(
			$project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('projectId'))
		);

		session_write_close();

		$selectedItemIds   = $request->getPostParameter('selectedRows', json_encode(array()));
		$descriptionFormat = $request->getParameter('descriptionFormat');
		$printNoCents      = ( $request->getParameter('printNoCents') == 't' ) ? true : false;
		$withSignature     = ( $request->getParameter('withSignature') == 't' ) ? true : false;

		$items = MaterialOnSiteItemTable::getItemsByIds($project, $selectedItemIds);

		$printSettings = $project->MaterialOnSitePrintSetting;

		// will take the data variable and then pump it into report generator
		$reportPrintGenerator = new sfMaterialOnSiteNormalItemReportGenerator($descriptionFormat);

		$reportPrintGenerator->setOrientationAndSize('portrait');
		$reportPrintGenerator->setWithSignature($withSignature);
		$reportPrintGenerator->setMOSPrintSettings($printSettings);

		$excelGenerator = new sfMaterialOnSiteItemExcelGenerator($project, $printSettings->project_name, $reportPrintGenerator->getPrintSettings());
		$excelGenerator->setMOSPrintSettings($printSettings);

		if ( empty( $items ) )
		{
			$excelGenerator->generateExcelFile();

			return $this->sendExportExcelHeader($excelGenerator->fileInfo['filename'], $excelGenerator->savePath . DIRECTORY_SEPARATOR . $excelGenerator->fileInfo['filename'] . $excelGenerator->fileInfo['extension']);
		}

		$materialOnSites = array();
		$newItems        = array();

		foreach ( $items as $item )
		{
			$mosId                   = $item['material_on_site_id'];
			$materialOnSites[$mosId] = $item['material_on_site_name'];

			$newItems[$mosId][] = $item;
		}

		foreach ( $materialOnSites as $mosId => $mosDescription )
		{
			$mosPageCount = 1;

			$reportPrintGenerator->setItems($newItems[$mosId]);

			$pages = $reportPrintGenerator->generatePages();

			if ( !( $pages instanceof SplFixedArray ) )
			{
				continue;
			}

			$excelGenerator->setMOS(Doctrine_Core::getTable('MaterialOnSite')->find($mosId));
			$excelGenerator->setMOSTotal($reportPrintGenerator->mosTotal);

			foreach ( $pages as $page )
			{
				if ( empty( $page ) )
				{
					continue;
				}

				$excelGenerator->isLastPage($mosPageCount == $pages->count());

				$excelGenerator->process($pages, false, null, null, null, $printNoCents, null);

				$mosPageCount ++;
			}
		}

		$excelGenerator->generateExcelFile();

		return $this->sendExportExcelHeader($excelGenerator->fileInfo['filename'], $excelGenerator->savePath . DIRECTORY_SEPARATOR . $excelGenerator->fileInfo['filename'] . $excelGenerator->fileInfo['extension']);
	}

	public function executeExportSelectedItemsByAmount(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless(
			$project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('projectId'))
		);

		session_write_close();

		$selectedItemIds   = $request->getPostParameter('selectedRows', json_encode(array()));
		$descriptionFormat = $request->getParameter('descriptionFormat');
		$printNoCents      = ( $request->getParameter('printNoCents') == 't' ) ? true : false;
		$withSignature     = ( $request->getParameter('withSignature') == 't' ) ? true : false;

		$items = MaterialOnSiteItemTable::getItemsByIds($project, $selectedItemIds);

		$printSettings = $project->MaterialOnSitePrintSetting;

		// will take the data variable and then pump it into report generator
		$reportPrintGenerator = new sfMaterialOnSiteItemByAmountReportGenerator($descriptionFormat);

		$reportPrintGenerator->setOrientationAndSize('portrait');
		$reportPrintGenerator->setWithSignature($withSignature);
		$reportPrintGenerator->setMOSPrintSettings($printSettings);

		$excelGenerator = new sfMaterialOnSiteItemByAmountExcelGenerator($project, $printSettings->project_name, $reportPrintGenerator->getPrintSettings());
		$excelGenerator->setMOSPrintSettings($printSettings);

		if ( empty( $items ) )
		{
			$excelGenerator->generateExcelFile();

			return $this->sendExportExcelHeader($excelGenerator->fileInfo['filename'], $excelGenerator->savePath . DIRECTORY_SEPARATOR . $excelGenerator->fileInfo['filename'] . $excelGenerator->fileInfo['extension']);
		}

		$materialOnSites = array();
		$newItems        = array();

		foreach ( $items as $item )
		{
			$mosId                   = $item['material_on_site_id'];
			$materialOnSites[$mosId] = $item['material_on_site_name'];

			$newItems[$mosId][] = $item;
		}

		foreach ( $materialOnSites as $mosId => $mosDescription )
		{
			$mosPageCount = 1;

			$reportPrintGenerator->setItems($newItems[$mosId]);

			$pages = $reportPrintGenerator->generatePages();

			if ( !( $pages instanceof SplFixedArray ) )
			{
				continue;
			}

			$excelGenerator->setMOS(Doctrine_Core::getTable('MaterialOnSite')->find($mosId));
			$excelGenerator->setMOSTotal($reportPrintGenerator->mosTotal);

			foreach ( $pages as $page )
			{
				if ( empty( $page ) )
				{
					continue;
				}

				$excelGenerator->isLastPage($mosPageCount == $pages->count());

				$excelGenerator->process($pages, false, null, null, null, $printNoCents, null);

				$mosPageCount ++;
			}
		}

		$excelGenerator->generateExcelFile();

		return $this->sendExportExcelHeader($excelGenerator->fileInfo['filename'], $excelGenerator->savePath . DIRECTORY_SEPARATOR . $excelGenerator->fileInfo['filename'] . $excelGenerator->fileInfo['extension']);
	}

}