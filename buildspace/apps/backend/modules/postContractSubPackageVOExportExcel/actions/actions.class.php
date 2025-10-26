<?php

/**
 * postContractSubPackageVOExportExcel actions.
 *
 * @package    buildspace
 * @subpackage postContractSubPackageVOExportExcel
 * @author     1337 developers
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class postContractSubPackageVOExportExcelActions extends BaseActions {

	public function executeExportExcelSelectedVO(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless(
			$subPackage = Doctrine_Core::getTable('SubPackage')->find($request->getParameter('subPackageId')) AND
			$project = Doctrine_Core::getTable('ProjectStructure')->find($subPackage->project_structure_id)
		);

		session_write_close();

		$voIds             = ( $request->hasParameter('selectedRows') ) ? json_decode($request->getParameter('selectedRows'), true) : array();
		$printingPageTitle = $request->getParameter('printingPageTitle');
		$descriptionFormat = $request->getParameter('descriptionFormat');
		$printNoCents      = ( $request->getParameter('printNoCents') == 't' ) ? true : false;

		$reportPrintGenerator = new sfBuildspacePostContractSubPackageVOSummaryReportGenerator($project, $subPackage, $voIds, $printingPageTitle, $descriptionFormat);
		$pages                = $reportPrintGenerator->generatePages();

		$totalPage = count($pages) - 1;

		$sfItemReportGenerator = new sfVOSummaryReportGenerator(
			$project,
			null,
			$printingPageTitle,
			$reportPrintGenerator->printSettings
		);

		$sfItemReportGenerator->setVoTotal($reportPrintGenerator->voTotals);

		$sfItemReportGenerator->process($pages, false, $printingPageTitle, '', $project->title, $printNoCents, $totalPage);

		return $this->sendExportExcelHeader($sfItemReportGenerator->fileInfo['filename'], $sfItemReportGenerator->savePath . DIRECTORY_SEPARATOR . $sfItemReportGenerator->fileInfo['filename'] . $sfItemReportGenerator->fileInfo['extension']);
	}

	public function executeExportExcelVOWithClaims(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless(
			$subPackage = Doctrine_Core::getTable('SubPackage')->find($request->getParameter('subPackageId')) AND
			$project = Doctrine_Core::getTable('ProjectStructure')->find($subPackage->project_structure_id)
		);

		session_write_close();

		$printingPageTitle = $request->getParameter('printingPageTitle');
		$descriptionFormat = $request->getParameter('descriptionFormat');
		$printNoCents      = ( $request->getParameter('printNoCents') == 't' ) ? true : false;

		$reportPrintGenerator = new sfBuildspaceSubPackageVOWithClaimsReportGenerator($project, $subPackage, $printingPageTitle, $descriptionFormat);
		$pages                = $reportPrintGenerator->generatePages();
		$totalPage            = count($pages) - 1;

		$sfItemReportGenerator = new sfVOSummaryReportWithClaimGenerator(
			$project,
			null,
			$printingPageTitle,
			$reportPrintGenerator->printSettings
		);

		$sfItemReportGenerator->setVoTotal($reportPrintGenerator->voTotals);

		$sfItemReportGenerator->process($pages, false, $printingPageTitle, '', $project->title, $printNoCents, $totalPage);

		return $this->sendExportExcelHeader($sfItemReportGenerator->fileInfo['filename'], $sfItemReportGenerator->savePath . DIRECTORY_SEPARATOR . $sfItemReportGenerator->fileInfo['filename'] . $sfItemReportGenerator->fileInfo['extension']);
	}

	public function executeExportExcelSelectedVOItemsDialog(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless(
			$subPackage = Doctrine_Core::getTable('SubPackage')->find($request->getParameter('subPackageId')) AND
			$project = Doctrine_Core::getTable('ProjectStructure')->find($subPackage->project_structure_id)
		);

		session_write_close();

		$printingPageTitle = $request->getParameter('printingPageTitle');
		$descriptionFormat = $request->getParameter('descriptionFormat');
		$itemIds           = json_decode($request->getParameter('selectedRows'), true);
		$printNoCents      = ( $request->getParameter('printNoCents') == 't' ) ? true : false;

		$reportPrintGenerator = new sfBuildspaceSubPackageVOItemsReportGenerator($project, $subPackage, $itemIds, $printingPageTitle, $descriptionFormat);
		$pages                = $reportPrintGenerator->generatePages();

		$sfItemExport = new sfVOItemReportGenerator(
			$project,
			null,
			$printingPageTitle,
			$reportPrintGenerator->printSettings
		);

		$sfItemExport->setParameter($reportPrintGenerator->variationTotals);

		$sfItemExport->process($pages, false, $printingPageTitle, $project->title, '', $printNoCents, $reportPrintGenerator->totalPage);

		return $this->sendExportExcelHeader($sfItemExport->fileInfo['filename'], $sfItemExport->savePath . DIRECTORY_SEPARATOR . $sfItemExport->fileInfo['filename'] . $sfItemExport->fileInfo['extension']);
	}

	public function executeExportExcelVOItemsWithClaims(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless(
			$subPackage = Doctrine_Core::getTable('SubPackage')->find($request->getParameter('subPackageId')) AND
			$project = Doctrine_Core::getTable('ProjectStructure')->find($subPackage->project_structure_id)
		);

		session_write_close();

		$printingPageTitle = $request->getParameter('printingPageTitle');
		$descriptionFormat = $request->getParameter('descriptionFormat');
		$printNoCents      = ( $request->getParameter('printNoCents') == 't' ) ? true : false;

		$reportPrintGenerator = new sfBuildspaceSubPackageVOItemsWithClaimReportGenerator($project, $subPackage, $printingPageTitle, $descriptionFormat);
		$pages                = $reportPrintGenerator->generatePages();

		$sfItemExport = new sfVOItemReportWithClaimGenerator(
			$project,
			null,
			$printingPageTitle,
			$reportPrintGenerator->printSettings
		);

		$sfItemExport->setParameter($reportPrintGenerator->variationTotals);

		$sfItemExport->process($pages, false, $printingPageTitle, $project->title, '', $printNoCents, $reportPrintGenerator->totalPage);

		return $this->sendExportExcelHeader($sfItemExport->fileInfo['filename'], $sfItemExport->savePath . DIRECTORY_SEPARATOR . $sfItemExport->fileInfo['filename'] . $sfItemExport->fileInfo['extension']);
	}

	public function executeExportExcelSelectedVOItemsWithBuildUpQty(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless(
			$subPackage = Doctrine_Core::getTable('SubPackage')->find($request->getParameter('subPackageId')) AND
			$project = Doctrine_Core::getTable('ProjectStructure')->find($subPackage->project_structure_id)
		);

		session_write_close();

		$itemIds    = json_decode($request->getParameter('selectedRows'), true);
		$stylesheet = file_get_contents(sfConfig::get('sf_web_dir') . '/css/printBQ.css');
		$data       = array();

		$printingPageTitle = $request->getParameter('printingPageTitle');
		$descriptionFormat = $request->getParameter('descriptionFormat');
		$priceFormat       = $request->getParameter('priceFormat');
		$printNoCents      = ( $request->getParameter('printNoCents') == 't' ) ? true : false;
		$withoutPrice      = false;
		$printedPages      = false;

		if ( !empty( $itemIds ) )
		{
			list(
				$data, $variationOrders,
				$buildUpItemsSummaries, $unitsDimensions, $buildUpItemsWithType
				) = SubPackageVariationOrderItemTable::getVOItemsStructure($subPackage, $itemIds);
		}

		$reportPrintGenerator = new sfBuildSpaceSubPackageVariationOrderBuildUpItemGenerator($project, $subPackage, $descriptionFormat);
		$reportPrintGenerator->setOrientationAndSize('portrait');

		$excelGenerator = new sfVariationOrderItemBuildUpQtyExcelGenerator($project, $printingPageTitle, $reportPrintGenerator->getPrintSettings());

		if ( empty( $data ) )
		{
			$excelGenerator->generateExcelFile();

			return $this->sendExportExcelHeader($excelGenerator->fileInfo['filename'], $excelGenerator->savePath . DIRECTORY_SEPARATOR . $excelGenerator->fileInfo['filename'] . $excelGenerator->fileInfo['extension']);
		}

		list(
			$soqItemsData, $soqFormulatedColumns, $manualBuildUpQuantityItems, $importedBuildUpQuantityItems
			) = ScheduleOfQuantitySubPackageVOItemXrefTable::getSelectedItemsBuildUpQuantity($project, $data);

		// first level will be looping variation order, then bill associated with it
		foreach ( $variationOrders as $variationOrder )
		{
			$voItems = ( isset( $data[$variationOrder['id']] ) ) ? $data[$variationOrder['id']] : array();

			foreach ( $voItems as $voItem )
			{
				$voItemId   = $voItem['id'];
				$dimensions = array();

				// for each VO's item, get build up qty's columns definitions
				foreach ( $unitsDimensions as $unitsDimension )
				{
					if ( $voItem['uom_id'] != $unitsDimension['unit_of_measurement_id'] )
					{
						continue;
					}

					$dimensions[] = $unitsDimension['Dimension'];
				}

				// set available dimension
				$reportPrintGenerator->setAvailableTableHeaderDimensions($dimensions);
				$excelGenerator->setDimensions($dimensions);

				// get voItem(s) build up if available
				foreach ( VariationOrderBuildUpQuantityItem::getBuildUpQtyTypes() as $variationOrderBuildUpQtyType )
				{
					// only allow existing type omission or addition to be available to be printed
					if ( $variationOrderBuildUpQtyType == SubPackageVariationOrderBuildUpQuantityItem::TYPE_OMISSION_QTY AND !$voItem['has_omission'] )
					{
						continue;
					}

					if ( $variationOrderBuildUpQtyType == SubPackageVariationOrderBuildUpQuantityItem::TYPE_ADDITIONAL_QTY AND !$voItem['has_addition'] )
					{
						continue;
					}

					$voQtyTypeText   = VariationOrderBuildUpQuantityItemTable::getTypeText($variationOrderBuildUpQtyType);
					$soqBuildUpItems = array();

					$voItemsBuildUpItems = isset( $buildUpItemsWithType[$variationOrderBuildUpQtyType][$voItem['id']] ) ? $buildUpItemsWithType[$variationOrderBuildUpQtyType][$voItem['id']] : array();

					$buildUpQuantitySummaryInfo = array();

					if ( isset( $soqItemsData[$variationOrderBuildUpQtyType][$voItemId] ) )
					{
						$soqBuildUpItems = $soqItemsData[$variationOrderBuildUpQtyType][$voItemId];

						unset( $soqItemsData[$variationOrderBuildUpQtyType][$voItemId] );
					}

					if ( isset( $buildUpItemsSummaries[$variationOrderBuildUpQtyType][$voItem['id']] ) )
					{
						$buildUpQuantitySummaryInfo = $buildUpItemsSummaries[$variationOrderBuildUpQtyType][$voItem['id']];

						unset( $buildUpItemsSummaries[$variationOrderBuildUpQtyType][$voItem['id']] );
					}

					// don't generate page that has no manual build up and soq build up item(s)
					if ( count($voItemsBuildUpItems) == 0 AND count($soqBuildUpItems) == 0 )
					{
						unset( $voItemsBuildUpItems, $soqBuildUpItems, $buildUpQuantitySummaryInfo );

						continue;
					}

					// will inject to the generator to correctly generate printout
					// need to pass build up qty item(s) into generator to correctly generate the printout page
					$reportPrintGenerator->setBuildUpQuantityItems($voItemsBuildUpItems);

					$reportPrintGenerator->setSOQBuildUpQuantityItems($soqBuildUpItems);

					$reportPrintGenerator->getSOQFormulatedColumn($soqFormulatedColumns);

					$reportPrintGenerator->setManualBuildUpQuantityMeasurements($manualBuildUpQuantityItems);
					$reportPrintGenerator->setImportedBuildUpQuantityMeasurements($importedBuildUpQuantityItems);

					$pages      = $reportPrintGenerator->generatePages();
					$voItemInfo = $reportPrintGenerator->setupBillItemHeader($voItem, $voItem['bill_ref']);

					$excelGenerator->setVariationOrderItemInfo($voItemInfo);
					$excelGenerator->setVariationOrderItemUOM($voItem['uom_symbol']);
					$excelGenerator->setBuildUpQuantitySummaryInfo($buildUpQuantitySummaryInfo);
					$excelGenerator->setQuantityPerUnit($voItem[strtolower($voQtyTypeText) . '_quantity']);

					if ( !( $pages instanceof SplFixedArray ) )
					{
						continue;
					}

					$excelGenerator->process($pages, false, $printingPageTitle, null, $variationOrder['description'] . ' > ' . $voQtyTypeText, $printNoCents, null);

					unset( $pages, $voItemInfo );
				}

				unset( $voItem );
			}

			unset( $variationOrder, $voItems );
		}

		unset( $variationOrders );

		$excelGenerator->generateExcelFile();

		return $this->sendExportExcelHeader($excelGenerator->fileInfo['filename'], $excelGenerator->savePath . DIRECTORY_SEPARATOR . $excelGenerator->fileInfo['filename'] . $excelGenerator->fileInfo['extension']);
	}

}