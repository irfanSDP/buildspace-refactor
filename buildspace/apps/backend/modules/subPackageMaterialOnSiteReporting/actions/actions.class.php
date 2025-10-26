<?php

/**
 * subPackageMaterialOnSiteReporting actions.
 *
 * @package    buildspace
 * @subpackage subPackageMaterialOnSiteReporting
 * @author     1337 developers
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class subPackageMaterialOnSiteReportingActions extends BaseActions {

	public function executeGetAffectedItems(sfWebRequest $request)
	{
		$this->forward404Unless(
			$request->isXmlHttpRequest() AND
			$request->hasParameter('mos_ids') AND
			$subPackage = Doctrine_Core::getTable('SubPackage')->find($request->getParameter('subPackageId'))
		);

		$data   = array();
		$mosIds = json_decode($request->getParameter('mos_ids'), true);
		$pdo    = $subPackage->getTable()->getConnection()->getDbh();

		// return empty array of variationOfOrderId's information so that the frontend can process it
		foreach ( $mosIds as $mosId )
		{
			$data[$mosId] = array();
		}

		if ( !empty( $mosIds ) )
		{
			$stmt = $pdo->prepare("SELECT i.id, i.sp_material_on_site_id FROM " . SubPackageMaterialOnSiteItemTable::getInstance()->getTableName() . " i
			WHERE i.sp_material_on_site_id IN (" . implode(',', $mosIds) . ") AND i.deleted_at IS NULL
			ORDER BY i.priority, i.lft, i.level");

			$stmt->execute();

			foreach ( $stmt->fetchAll(PDO::FETCH_ASSOC) as $mosItem )
			{
				$data[$mosItem['sp_material_on_site_id']][] = $mosItem['id'];
			}
		}

		return $this->renderJson($data);
	}

	public function executeGetAffectedMOS(sfWebRequest $request)
	{
		$this->forward404Unless(
			$request->isXmlHttpRequest() AND
			$request->hasParameter('item_ids') AND
			$project = Doctrine_Core::getTable('SubPackage')->find($request->getParameter('subPackageId'))
		);

		$data    = array();
		$itemIds = json_decode($request->getParameter('item_ids'), true);
		$pdo     = $project->getTable()->getConnection()->getDbh();

		if ( !empty( $itemIds ) )
		{
			$stmt = $pdo->prepare("SELECT i.id, i.sp_material_on_site_id FROM " . SubPackageMaterialOnSiteItemTable::getInstance()->getTableName() . " i
			WHERE i.id IN (" . implode(',', $itemIds) . ") AND i.deleted_at IS NULL
			ORDER BY i.priority, i.lft, i.level");

			$stmt->execute();

			foreach ( $stmt->fetchAll(PDO::FETCH_ASSOC) as $mosItem )
			{
				$data[$mosItem['sp_material_on_site_id']][] = $mosItem['id'];
			}
		}

		return $this->renderJson($data);
	}

	public function executeGetPrintPreviewSelectedItems(sfWebRequest $request)
	{
		$this->forward404Unless(
			$request->isXmlHttpRequest() and
			$project = Doctrine_Core::getTable('SubPackage')->find($request->getParameter('subPackageId'))
		);

		$data = array();

		$selectedItemIds = $request->getParameter('item_ids', json_encode(array()));

		$items = SubPackageMaterialOnSiteItemTable::getItemsByIds($project, $selectedItemIds);

		if ( !empty( $items ) )
		{
			$materialOnSites = array();
			$newItems        = array();

			foreach ( $items as $item )
			{
				$mosId                   = $item['sp_material_on_site_id'];
				$materialOnSites[$mosId] = $item['material_on_site_name'];

				$newItems[$mosId][] = $item;
			}

			foreach ( $materialOnSites as $mosId => $mosDescription )
			{
				$data[] = array(
					'id'            => 'sp_material_on_site-' . $mosId,
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
			$subPackage = Doctrine_Core::getTable('SubPackage')->find($request->getParameter('subPackageId'))
		);

		session_write_close();

		$selectedItemIds   = $request->getPostParameter('selectedRows', json_encode(array()));
		$descriptionFormat = $request->getParameter('descriptionFormat');
		$priceFormat       = $request->getParameter('priceFormat');
		$printNoCents      = ( $request->getParameter('printNoCents') == 't' ) ? true : false;
		$withoutPrice      = false;
		$stylesheet        = $this->getBQStyling();
		$withSignature     = ( $request->getParameter('withSignature') == 't' ) ? true : false;

		$items = SubPackageMaterialOnSiteItemTable::getItemsByIds($subPackage, $selectedItemIds);

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
			$mosId                   = $item['sp_material_on_site_id'];
			$materialOnSites[$mosId] = $item['material_on_site_name'];

			$newItems[$mosId][] = $item;
		}

		$printSettings = $subPackage->SubPackageMaterialOnSitePrintSetting;

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

			$mos = Doctrine_Core::getTable('SubPackageMaterialOnSite')->find($mosId);

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
					'lastPage'                   => false,
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
					'currency'                   => $subPackage->ProjectStructure->MainInformation->Currency,
					'reduction_percentage'       => $mos->reduction_percentage,
					'withSignature'              => $withSignature,
					'mosTotal'                   => $reportPrintGenerator->mosTotal,
				);

				$layout .= $this->getPartial('materialOnSiteReporting/mosNormalItemReport', $billItemsLayoutParams);

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
			$subPackage = Doctrine_Core::getTable('SubPackage')->find($request->getParameter('subPackageId'))
		);

		session_write_close();

		$selectedItemIds   = $request->getPostParameter('selectedRows', json_encode(array()));
		$descriptionFormat = $request->getParameter('descriptionFormat');
		$priceFormat       = $request->getParameter('priceFormat');
		$printNoCents      = ( $request->getParameter('printNoCents') == 't' ) ? true : false;
		$stylesheet        = $this->getBQStyling();
		$withSignature     = ( $request->getParameter('withSignature') == 't' ) ? true : false;

		$items = SubPackageMaterialOnSiteItemTable::getItemsByIds($subPackage, $selectedItemIds);

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
			$mosId                   = $item['sp_material_on_site_id'];
			$materialOnSites[$mosId] = $item['material_on_site_name'];

			$newItems[$mosId][] = $item;
		}

		$printSettings = $subPackage->SubPackageMaterialOnSitePrintSetting;

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

			$mos = Doctrine_Core::getTable('SubPackageMaterialOnSite')->find($mosId);

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
					'lastPage'                   => false,
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
					'projectTitle'               => $subPackage->ProjectStructure->title,
					'left_text'                  => $voFooterSettings->left_text,
					'right_text'                 => $voFooterSettings->right_text,
					'currency'                   => $subPackage->ProjectStructure->MainInformation->Currency,
					'reduction_percentage'       => $mos->reduction_percentage,
					'withSignature'              => $withSignature,
					'mosDescription'             => $mosDescription,
					'mosTotal'                   => $reportPrintGenerator->mosTotal,
				);

				$layout .= $this->getPartial('materialOnSiteReporting/mosItemByAmountReport', $billItemsLayoutParams);

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
			$subPackage = Doctrine_Core::getTable('SubPackage')->find($request->getParameter('subPackageId'))
		);

		session_write_close();

		$selectedItemIds   = $request->getPostParameter('selectedRows', json_encode(array()));
		$descriptionFormat = $request->getParameter('descriptionFormat');
		$printNoCents      = ( $request->getParameter('printNoCents') == 't' ) ? true : false;
		$withSignature     = ( $request->getParameter('withSignature') == 't' ) ? true : false;

		$items = SubPackageMaterialOnSiteItemTable::getItemsByIds($subPackage, $selectedItemIds);

		$printSettings = $subPackage->SubPackageMaterialOnSitePrintSetting;

		// will take the data variable and then pump it into report generator
		$reportPrintGenerator = new sfMaterialOnSiteNormalItemReportGenerator($descriptionFormat);

		$reportPrintGenerator->setOrientationAndSize('portrait');
		$reportPrintGenerator->setWithSignature($withSignature);
		$reportPrintGenerator->setMOSPrintSettings($printSettings);

		$excelGenerator = new sfMaterialOnSiteItemExcelGenerator($subPackage->ProjectStructure, $printSettings->project_name, $reportPrintGenerator->getPrintSettings());
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
			$mosId                   = $item['sp_material_on_site_id'];
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

			$excelGenerator->setMOS(Doctrine_Core::getTable('SubPackageMaterialOnSite')->find($mosId));
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

	public function executeExportExcelSelectedItemsByAmount(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless(
			$subPackage = Doctrine_Core::getTable('SubPackage')->find($request->getParameter('subPackageId'))
		);

		session_write_close();

		$selectedItemIds   = $request->getPostParameter('selectedRows', json_encode(array()));
		$descriptionFormat = $request->getParameter('descriptionFormat');
		$printNoCents      = ( $request->getParameter('printNoCents') == 't' ) ? true : false;
		$withSignature     = ( $request->getParameter('withSignature') == 't' ) ? true : false;

		$items = SubPackageMaterialOnSiteItemTable::getItemsByIds($subPackage, $selectedItemIds);

		$printSettings = $subPackage->SubPackageMaterialOnSitePrintSetting;

		// will take the data variable and then pump it into report generator
		$reportPrintGenerator = new sfMaterialOnSiteItemByAmountReportGenerator($descriptionFormat);

		$reportPrintGenerator->setOrientationAndSize('portrait');
		$reportPrintGenerator->setWithSignature($withSignature);
		$reportPrintGenerator->setMOSPrintSettings($printSettings);

		$excelGenerator = new sfMaterialOnSiteItemByAmountExcelGenerator($subPackage->ProjectStructure, $printSettings->project_name, $reportPrintGenerator->getPrintSettings());
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
			$mosId                   = $item['sp_material_on_site_id'];
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

			$excelGenerator->setMOS(Doctrine_Core::getTable('SubPackageMaterialOnSite')->find($mosId));
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