<?php

/**
 * exportExcelReport actions.
 *
 * @package    buildspace
 * @subpackage exportExcelReport
 * @author     1337 developers
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class exportExcelReportActions extends BaseActions {

	public function executeExportBill(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless(
			$project = ProjectStructureTable::getInstance()->find($request->getParameter('projectId'))
		);

		session_write_close();

		//Setup Necessary parameter
		$printingPageTitle = $request->getParameter('printingPageTitle');
		$descriptionFormat = $request->getParameter('descriptionFormat');
		$tendererIds       = json_decode($request->getParameter('selectedTenderers'), true);
		$billIds           = json_decode($request->getParameter('selectedRows'), true);
		$sortingType       = $request->getParameter('sortingType');
		$printNoCents      = ( $request->getParameter('printNoCents') == 't' ) ? true : false;

		$reportPrintGenerator = new sfBuildspaceReportBillPageGenerator($project, $tendererIds, $billIds, $sortingType, $printingPageTitle, $descriptionFormat);

		//Generate Pages
		$pages       = $reportPrintGenerator->generatePages();
		$participate = ( $project->MainInformation->tender_type_id == ProjectMainInformation::TENDER_TYPE_PARTICIPATED ) ? true : false;
		$totalPage   = count($pages) - 1;

		if ( $sortingType )
		{
			$sfBillExport = new sfBillReportGenerator(
				$project,
				$reportPrintGenerator->getEstimateProjectGrandTotal(),
				null,
				$printingPageTitle,
				$reportPrintGenerator->printSettings
			);

			$sfBillExport->setMultipleContractorParameter(
				$reportPrintGenerator->tenderers,
				$reportPrintGenerator->getContractorProjectGrandTotals(),
				$reportPrintGenerator->contractorBillGrandTotals
			);
		}
		else
		{
			if ( $participate )
			{
				$sfBillExport = new sfBillReportGenerator(
					$project,
					$reportPrintGenerator->getEstimateProjectGrandTotal(),
					null,
					$printingPageTitle,
					$reportPrintGenerator->printSettings
				);

				$sfBillExport->setRationalizedComparison(
					$reportPrintGenerator->getRationalizedProjectGrandTotal(),
					$reportPrintGenerator->getRationalizedBillGrandTotals()
				);
			}
			else
			{
				$sfBillExport = new sfBillReportGenerator(
					$project,
					$reportPrintGenerator->getEstimateProjectGrandTotal(),
					null,
					$printingPageTitle,
					$reportPrintGenerator->printSettings
				);

				$sfBillExport->setSelectedComparison(
					$reportPrintGenerator->selectedTenderer,
					$reportPrintGenerator->getSelectedProjectGrandTotal(),
					$reportPrintGenerator->getSelectedBillGrandTotals()
				);
			}
		}

		$sfBillExport->process($pages, false, $printingPageTitle, null, $project->title, $printNoCents, $totalPage);

		return $this->sendExportExcelHeader($sfBillExport->fileInfo['filename'], $sfBillExport->savePath . DIRECTORY_SEPARATOR . $sfBillExport->fileInfo['filename'] . $sfBillExport->fileInfo['extension']);
	}

    public function executeExportBillRevisions(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $project = ProjectStructureTable::getInstance()->find($request->getParameter('projectId'))
        );

        session_write_close();

        //Setup Necessary parameter
        $printingPageTitle = $request->getParameter('printingPageTitle');
        $descriptionFormat = $request->getParameter('descriptionFormat');
        $tendererIds       = json_decode($request->getParameter('selectedTenderers'), true);
        $tendererIds       = empty($tendererIds) ? array() : $tendererIds;
        $billIds           = json_decode($request->getParameter('selectedRows'), true);
        $sortingType       = $request->getParameter('sortingType');
        $printNoCents      = ( $request->getParameter('printNoCents') == 't' ) ? true : false;

        $reportPrintGenerator = new sfBuildspaceReportBillPageGenerator($project, $tendererIds, $billIds, $sortingType, $printingPageTitle, $descriptionFormat);

        //Generate Pages
        $pages       = $reportPrintGenerator->generatePages();
        $totalPage   = count($pages) - 1;

        $sfBillExport = new sfBillRevisionsReportGenerator(
            $project,
            $reportPrintGenerator->getEstimateProjectGrandTotal(),
            null,
            $printingPageTitle,
            $reportPrintGenerator->printSettings
        );

        $sfBillExport->setParameters(
            $reportPrintGenerator->tenderers,
            $reportPrintGenerator->getContractorProjectGrandTotals(),
            $reportPrintGenerator->contractorBillGrandTotals,
            ProjectRevisionTable::getEstimateBillGrandTotalRevisions($project),
            ProjectRevisionTable::getTendererBillGrandTotalRevisions($project, $tendererIds)
        );

        $sfBillExport->process($pages, false, $printingPageTitle, null, $project->title, $printNoCents, $totalPage);

        return $this->sendExportExcelHeader($sfBillExport->fileInfo['filename'], $sfBillExport->savePath . DIRECTORY_SEPARATOR . $sfBillExport->fileInfo['filename'] . $sfBillExport->fileInfo['extension']);
    }

	public function executeExportSelectedElement(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless(
			$bill = ProjectStructureTable::getInstance()->find($request->getParameter('billId'))
		);

		session_write_close();

		$project = ProjectStructureTable::getInstance()->find($bill->root_id);

		//Setup Necessary parameter
		$printingPageTitle = $request->getParameter('printingPageTitle');
		$descriptionFormat = $request->getParameter('descriptionFormat');
		$tendererIds       = json_decode($request->getParameter('selectedTenderers'), true);
		$elementIds        = json_decode($request->getParameter('selectedRows'), true);
		$sortingType       = $request->getParameter('sortingType');
		$printNoCents      = ( $request->getParameter('printNoCents') == 't' ) ? true : false;

		$reportPrintGenerator = new sfBuildspaceReportElementPageGenerator($bill, $tendererIds, $elementIds, $sortingType, $printingPageTitle, $descriptionFormat);

		//Generate Pages
		$pages       = $reportPrintGenerator->generatePages();
		$participate = ( $project->MainInformation->tender_type_id == ProjectMainInformation::TENDER_TYPE_PARTICIPATED ) ? true : false;
		$totalPage   = count($pages) - 1;

		if ( $sortingType )
		{
			$sfElementExport = new sfElementReportGenerator(
				$project,
				$reportPrintGenerator->getEstimateBillGrandTotal(),
				null,
				$printingPageTitle,
				$reportPrintGenerator->printSettings
			);

			$sfElementExport->setMultipleContractorParameter(
				$reportPrintGenerator->tenderers,
				$reportPrintGenerator->getContractorBillGrandTotals(),
				$reportPrintGenerator->contractorElementGrandTotals
			);
		}
		else
		{
			if ( $participate )
			{
				$sfElementExport = new sfElementReportGenerator(
					$project,
					$reportPrintGenerator->getEstimateBillGrandTotal(),
					null,
					$printingPageTitle,
					$reportPrintGenerator->printSettings
				);

				$sfElementExport->setRationalizedComparison(
					$reportPrintGenerator->getRationalizedBillGrandTotal(),
					$reportPrintGenerator->rationalizedElementTotals
				);
			}
			else
			{
				$sfElementExport = new sfElementReportGenerator(
					$project,
					$reportPrintGenerator->getEstimateBillGrandTotal(),
					null,
					$printingPageTitle,
					$reportPrintGenerator->printSettings
				);

				$sfElementExport->setSelectedComparison(
					$reportPrintGenerator->selectedTenderer,
					$reportPrintGenerator->getSelectedBillGrandTotal(),
					$reportPrintGenerator->selectedElementTotals
				);
			}
		}

		$sfElementExport->process($pages, false, $printingPageTitle, $bill->title, $project->title, $printNoCents, $totalPage);

		return $this->sendExportExcelHeader($sfElementExport->fileInfo['filename'], $sfElementExport->savePath . DIRECTORY_SEPARATOR . $sfElementExport->fileInfo['filename'] . $sfElementExport->fileInfo['extension']);
	}

    public function executeExportSelectedElementRevisions(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $bill = ProjectStructureTable::getInstance()->find($request->getParameter('billId'))
        );

        session_write_close();

        $project = ProjectStructureTable::getInstance()->find($bill->root_id);

        //Setup Necessary parameter
        $printingPageTitle = $request->getParameter('printingPageTitle');
        $descriptionFormat = $request->getParameter('descriptionFormat');
        $tendererIds       = json_decode($request->getParameter('selectedTenderers'), true);
        $tendererIds       = empty($tendererIds) ? array() : $tendererIds;
        $elementIds        = json_decode($request->getParameter('selectedRows'), true);
        $sortingType       = $request->getParameter('sortingType');
        $printNoCents      = ( $request->getParameter('printNoCents') == 't' ) ? true : false;

        $reportPrintGenerator = new sfBuildspaceReportElementPageGenerator($bill, $tendererIds, $elementIds, $sortingType, $printingPageTitle, $descriptionFormat);

        //Generate Pages
        $pages       = $reportPrintGenerator->generatePages();
        $totalPage   = count($pages) - 1;

        $sfElementExport = new sfElementRevisionsReportGenerator(
            $project,
            $reportPrintGenerator->getEstimateBillGrandTotal(),
            null,
            $printingPageTitle,
            $reportPrintGenerator->printSettings
        );

        $sfElementExport->setParameters(
            $reportPrintGenerator->tenderers,
            $reportPrintGenerator->getContractorBillGrandTotals(),
            $reportPrintGenerator->contractorElementGrandTotals,
            ProjectRevisionTable::getEstimateElementGrandTotalRevisions($bill),
            ProjectRevisionTable::getTendererElementGrandTotalRevisions($bill, $tendererIds)
        );

        $sfElementExport->process($pages, false, $printingPageTitle, $bill->title, $project->title, $printNoCents, $totalPage);

        return $this->sendExportExcelHeader($sfElementExport->fileInfo['filename'], $sfElementExport->savePath . DIRECTORY_SEPARATOR . $sfElementExport->fileInfo['filename'] . $sfElementExport->fileInfo['extension']);
    }

	public function executeExportItem(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless(
			$bill = ProjectStructureTable::getInstance()->find($request->getParameter('billId'))
		);

		session_write_close();

		$element            = BillElementTable::getInstance()->find($request->getParameter('elementId'));
		$billColumnSettings = $bill->getBillColumnSettings()->toArray();
		$project            = ProjectStructureTable::getInstance()->find($bill->root_id);

		//Setup Necessary parameter
		$printingPageTitle = $request->getParameter('printingPageTitle');
		$descriptionFormat = $request->getParameter('descriptionFormat');
		$tendererIds       = json_decode($request->getParameter('selectedTenderers'), true);
		$itemIds           = json_decode($request->getParameter('selectedRows'), true);
		$sortingType       = $request->getParameter('sortingType');
		$printNoCents      = ( $request->getParameter('printNoCents') == 't' ) ? true : false;

		//Generate Item Pages
		$reportPrintGenerator = new sfBuildspaceReportPageGenerator($bill, $element, $tendererIds, $itemIds, $sortingType, $printingPageTitle, $descriptionFormat);

		$topLeftTitle = ( strlen($reportPrintGenerator->printSettings['layoutSetting']['pageNoPrefix']) ) ? 'Prefix: ' . $reportPrintGenerator->printSettings['layoutSetting']['pageNoPrefix'] : '';
		$pages        = $reportPrintGenerator->generatePages();

		//Pass Parameter & Generate Excel
		$sfItemExport = new sfItemReportGenerator(
			$project,
			$reportPrintGenerator->getEstimateElementGrandTotals(),
			null,
			$printingPageTitle,
			true,
			$reportPrintGenerator->printSettings
		);

		$sfItemExport->setParameter(
			$billColumnSettings,
			$reportPrintGenerator->tenderers,
			$reportPrintGenerator->contractorRates,
			$reportPrintGenerator->getContractorElementGrandTotals()
		);

		$sfItemExport->process($pages, false, $printingPageTitle, $topLeftTitle, $bill->title, $printNoCents, $reportPrintGenerator->totalPage);

		return $this->sendExportExcelHeader($sfItemExport->fileInfo['filename'], $sfItemExport->savePath . DIRECTORY_SEPARATOR . $sfItemExport->fileInfo['filename'] . $sfItemExport->fileInfo['extension']);
	}

	public function executeExportItemTotal(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless(
			$bill = ProjectStructureTable::getInstance()->find($request->getParameter('billId'))
		);

		session_write_close();

		$element            = BillElementTable::getInstance()->find($request->getParameter('elementId'));
		$billColumnSettings = $bill->getBillColumnSettings()->toArray();
		$project            = ProjectStructureTable::getInstance()->find($bill->root_id);

		//Setup Necessary parameter
		$printingPageTitle = $request->getParameter('printingPageTitle');
		$descriptionFormat = $request->getParameter('descriptionFormat');
		$tendererIds       = json_decode($request->getParameter('selectedTenderers'), true);
		$itemIds           = json_decode($request->getParameter('selectedRows'), true);
		$sortingType       = $request->getParameter('sortingType');
		$printNoCents      = ( $request->getParameter('printNoCents') == 't' ) ? true : false;

		//Generate Item Pages
		$reportPrintGenerator = new sfBuildspaceReportPageItemTotalGenerator($bill, $element, $tendererIds, $itemIds, $sortingType, $printingPageTitle, $descriptionFormat);

		$topLeftTitle = ( strlen($reportPrintGenerator->printSettings['layoutSetting']['pageNoPrefix']) ) ? 'Prefix: ' . $reportPrintGenerator->printSettings['layoutSetting']['pageNoPrefix'] : '';
		$pages        = $reportPrintGenerator->generatePages();

		//Pass Parameter & Generate Excel
		$sfItemExport = new sfItemReportGenerator(
			$project,
			$reportPrintGenerator->getEstimateElementGrandTotals(),
			null,
			$printingPageTitle,
			false,
			$reportPrintGenerator->printSettings
		);

		$sfItemExport->setParameter(
			$billColumnSettings,
			$reportPrintGenerator->tenderers,
			$reportPrintGenerator->contractorRates,
			$reportPrintGenerator->getContractorElementGrandTotals()
		);

		$sfItemExport->process($pages, false, $printingPageTitle, $topLeftTitle, $bill->title, $printNoCents, $reportPrintGenerator->totalPage);

		return $this->sendExportExcelHeader($sfItemExport->fileInfo['filename'], $sfItemExport->savePath . DIRECTORY_SEPARATOR . $sfItemExport->fileInfo['filename'] . $sfItemExport->fileInfo['extension']);
	}

    public function executeExportItemRateAndTotalPerUnit(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $bill = ProjectStructureTable::getInstance()->find($request->getParameter('billId'))
        );

        session_write_close();

        $billColumnSettings = $bill->getBillColumnSettings()->toArray();
        $project            = ProjectStructureTable::getInstance()->find($bill->root_id);

        //Setup Necessary parameter
        $printingPageTitle = $request->getParameter('printingPageTitle');
        $descriptionFormat = $request->getParameter('descriptionFormat');
        $tendererIds       = json_decode($request->getParameter('selectedTenderers'), true);
        $itemIds           = json_decode($request->getParameter('selectedRows'), true);
        $sortingType       = $request->getParameter('sortingType');
        $printNoCents      = ( $request->getParameter('printNoCents') == 't' ) ? true : false;

        $elementIds = array();
        foreach(BillElementTable::getAffectedElementIdsByItemIds($request->getParameter('selectedRows')) as $elementId => $element)
        {
            $elementIds[] = $elementId;
        }

        $reportPrintGenerator = new sfBuildspaceReportItemRateAndTotalPerUnitPageGenerator($bill, $tendererIds, $elementIds, $itemIds, $sortingType, $printingPageTitle, $descriptionFormat);

        list(
            $elements, $elementsWithBillItems, $formulatedColumns, $quantityPerUnitByColumns,
            $billItemTypeReferences, $billItemTypeRefFormulatedColumns
            ) = BillItemTable::getPrintingPreviewDataStructureForBillItemList($request->getParameter('selectedRows'), $bill);

        $reportPrintGenerator->setParameters($elements, $elementsWithBillItems, $formulatedColumns, $quantityPerUnitByColumns, $billItemTypeReferences, $billItemTypeRefFormulatedColumns);
        $pages = $reportPrintGenerator->generatePages();

        $topLeftTitle = ( strlen($reportPrintGenerator->printSettings['layoutSetting']['pageNoPrefix']) ) ? 'Prefix: ' . $reportPrintGenerator->printSettings['layoutSetting']['pageNoPrefix'] : '';

        //Pass Parameter & Generate Excel
        $sfItemExport = new sfItemRateAndTotalPerUnitReportGenerator(
            $project,
            null,
            $printingPageTitle,
            true,
            $reportPrintGenerator->printSettings
        );

        $sfItemExport->setParameter(
            $billColumnSettings,
            $reportPrintGenerator->tenderers,
            $reportPrintGenerator->contractorRates,
            $reportPrintGenerator->estimateElementTotals,
            $reportPrintGenerator->contractorElementTotals
        );

        $sfItemExport->process($pages, false, $printingPageTitle, $bill->title, $topLeftTitle, $printNoCents, $reportPrintGenerator->totalPage);

        return $this->sendExportExcelHeader($sfItemExport->fileInfo['filename'], $sfItemExport->savePath . DIRECTORY_SEPARATOR . $sfItemExport->fileInfo['filename'] . $sfItemExport->fileInfo['extension']);
    }

    /**
     * Generates an excel file for exporting Item rate and total.
     *
     * @param sfWebRequest $request
     *
     * @return string
     * @throws sfError404Exception
     */
    public function executeExportItemRateAndTotal(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $bill = ProjectStructureTable::getInstance()->find($request->getParameter('billId'))
        );

        session_write_close();

        $element            = BillElementTable::getInstance()->find($request->getParameter('elementId'));
        $billColumnSettings = $bill->getBillColumnSettings()->toArray();
        $project            = ProjectStructureTable::getInstance()->find($bill->root_id);

        //Setup Necessary parameter
        $printingPageTitle = $request->getParameter('printingPageTitle');
        $descriptionFormat = $request->getParameter('descriptionFormat');
        $tendererIds       = json_decode($request->getParameter('selectedTenderers'), true);
        $itemIds           = json_decode($request->getParameter('selectedRows'), true);
        $sortingType       = $request->getParameter('sortingType');
        $printNoCents      = ( $request->getParameter('printNoCents') == 't' ) ? true : false;

        //Generate Item Pages
        $reportPrintGenerator = new sfBuildspaceReportItemRateAndTotalPageGenerator($bill, $element, $tendererIds, $itemIds, $sortingType, $printingPageTitle, $descriptionFormat);

        $topLeftTitle = ( strlen($reportPrintGenerator->printSettings['layoutSetting']['pageNoPrefix']) ) ? 'Prefix: ' . $reportPrintGenerator->printSettings['layoutSetting']['pageNoPrefix'] : '';
        $pages        = $reportPrintGenerator->generatePages();

        //Pass Parameter & Generate Excel
        $sfItemExport = new sfItemRateAndTotalReportGenerator(
            $project,
            $reportPrintGenerator->getEstimateElementGrandTotals(),
            null,
            $printingPageTitle,
            true,
            $reportPrintGenerator->printSettings
        );

        $sfItemExport->setParameter(
            $billColumnSettings,
            $reportPrintGenerator->tenderers,
            $reportPrintGenerator->contractorRates,
            $reportPrintGenerator->contractorTotals,
            $reportPrintGenerator->getContractorElementGrandTotals()
        );

        $sfItemExport->process($pages, false, $printingPageTitle, $topLeftTitle, $bill->title, $printNoCents, $reportPrintGenerator->totalPage);

        return $this->sendExportExcelHeader($sfItemExport->fileInfo['filename'], $sfItemExport->savePath . DIRECTORY_SEPARATOR . $sfItemExport->fileInfo['filename'] . $sfItemExport->fileInfo['extension']);
    }

    /**
     * Generates an excel file for exporting Item rate and total with all revisions.
     *
     * @param sfWebRequest $request
     *
     * @return string
     * @throws sfError404Exception
     */
    public function executeExportItemRateAndTotalRevisions(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $bill = ProjectStructureTable::getInstance()->find($request->getParameter('billId'))
        );

        session_write_close();

        $element            = BillElementTable::getInstance()->find($request->getParameter('elementId'));
        $billColumnSettings = $bill->getBillColumnSettings()->toArray();
        $project            = ProjectStructureTable::getInstance()->find($bill->root_id);

        //Setup Necessary parameter
        $printingPageTitle = $request->getParameter('printingPageTitle');
        $descriptionFormat = $request->getParameter('descriptionFormat');
        $tendererIds       = json_decode($request->getParameter('selectedTenderers'), true);
        $itemIds           = json_decode($request->getParameter('selectedRows'), true);
        $sortingType       = $request->getParameter('sortingType');
        $printNoCents      = ( $request->getParameter('printNoCents') == 't' ) ? true : false;

        //Generate Item Pages
        $reportPrintGenerator = new sfBuildspaceReportItemRateAndTotalRevisionsPageGenerator($bill, $element, $tendererIds, $itemIds, $sortingType, $printingPageTitle, $descriptionFormat);

        $topLeftTitle = ( strlen($reportPrintGenerator->printSettings['layoutSetting']['pageNoPrefix']) ) ? 'Prefix: ' . $reportPrintGenerator->printSettings['layoutSetting']['pageNoPrefix'] : '';
        $pages        = $reportPrintGenerator->generatePages();

        //Pass Parameter & Generate Excel
        $sfItemExport = new sfItemRateAndTotalRevisionsReportGenerator(
            $project,
            $reportPrintGenerator->getEstimateElementGrandTotals(),
            null,
            $printingPageTitle,
            true,
            $reportPrintGenerator->printSettings
        );

        list(
            $estimateRateRevisions,
            $estimateTotalRevisions,
            $tendererRateRevisions,
            $tendererTotalRevisions
            ) = ProjectRevisionTable::getRatesAndTotalsRevisions($bill, $tendererIds);

        $sfItemExport->setParameter(
            $billColumnSettings,
            $reportPrintGenerator->tenderers,
            $reportPrintGenerator->contractorRates,
            $reportPrintGenerator->contractorTotals,
            $reportPrintGenerator->getContractorElementGrandTotals(),
            $estimateRateRevisions,
            $estimateTotalRevisions,
            $tendererRateRevisions,
            $tendererTotalRevisions
        );

        $sfItemExport->process($pages, false, $printingPageTitle, $topLeftTitle, $bill->title, $printNoCents, $reportPrintGenerator->totalPage);

        return $this->sendExportExcelHeader($sfItemExport->fileInfo['filename'], $sfItemExport->savePath . DIRECTORY_SEPARATOR . $sfItemExport->fileInfo['filename'] . $sfItemExport->fileInfo['extension']);
    }

	public function executeExportSelectedItemRate(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless(
			$bill = ProjectStructureTable::getInstance()->find($request->getParameter('billId'))
		);

		session_write_close();

		$element            = BillElementTable::getInstance()->find($request->getParameter('elementId'));
		$billColumnSettings = $bill->getBillColumnSettings()->toArray();
		$project            = ProjectStructureTable::getInstance()->find($bill->root_id);

		//Setup Necessary parameter
		$printingPageTitle = $request->getParameter('printingPageTitle');
		$descriptionFormat = $request->getParameter('descriptionFormat');
		$tendererIds       = json_decode($request->getParameter('selectedTenderers'), true);
		$itemIds           = json_decode($request->getParameter('selectedRows'), true);
		$sortingType       = $request->getParameter('sortingType');
		$printNoCents      = ( $request->getParameter('printNoCents') == 't' ) ? true : false;

		//Generate Item Pages
		$reportPrintGenerator = new sfBuildspaceReportPageItemComparisonGenerator($bill, $element, $tendererIds, $itemIds, $sortingType, $printingPageTitle, $descriptionFormat);

		$topLeftTitle = ( strlen($reportPrintGenerator->printSettings['layoutSetting']['pageNoPrefix']) ) ? 'Prefix: ' . $reportPrintGenerator->printSettings['layoutSetting']['pageNoPrefix'] : '';
		$pages        = $reportPrintGenerator->generatePages();
		$participate  = ( $project->MainInformation->tender_type_id == ProjectMainInformation::TENDER_TYPE_PARTICIPATED ) ? true : false;

		//Pass Parameter & Generate Excel
		$sfItemExport = new sfItemComparisonReportGenerator(
			$project,
			$reportPrintGenerator->getEstimateElementGrandTotals(),
			null,
			$printingPageTitle,
			true,
			$reportPrintGenerator->printSettings
		);

		if ( $participate )
		{
			$sfItemExport->setRationalizedParameter(
				$billColumnSettings,
				$reportPrintGenerator->rationalizedRates,
				$reportPrintGenerator->rationalizedElementGrandTotal
			);
		}
		else
		{
			$sfItemExport->setSelectedParameter(
				$billColumnSettings,
				$reportPrintGenerator->selectedTenderer,
				$reportPrintGenerator->selectedRates,
				$reportPrintGenerator->selectedElementGrandTotal
			);
		}

		$sfItemExport->process($pages, false, $printingPageTitle, $topLeftTitle, $bill->title, $printNoCents, $reportPrintGenerator->totalPage);

		return $this->sendExportExcelHeader($sfItemExport->fileInfo['filename'], $sfItemExport->savePath . DIRECTORY_SEPARATOR . $sfItemExport->fileInfo['filename'] . $sfItemExport->fileInfo['extension']);
	}

	public function executeExportSelectedItemTotal(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless(
			$bill = ProjectStructureTable::getInstance()->find($request->getParameter('billId'))
		);

		session_write_close();

		$element            = BillElementTable::getInstance()->find($request->getParameter('elementId'));
		$billColumnSettings = $bill->getBillColumnSettings()->toArray();
		$project            = ProjectStructureTable::getInstance()->find($bill->root_id);

		//Setup Necessary parameter
		$printingPageTitle = $request->getParameter('printingPageTitle');
		$descriptionFormat = $request->getParameter('descriptionFormat');
		$tendererIds       = json_decode($request->getParameter('selectedTenderers'), true);
		$itemIds           = json_decode($request->getParameter('selectedRows'), true);
		$sortingType       = $request->getParameter('sortingType');
		$printNoCents      = ( $request->getParameter('printNoCents') == 't' ) ? true : false;

		//Generate Item Pages
		$reportPrintGenerator = new sfBuildspaceReportPageItemTotalComparisonGenerator($bill, $element, $tendererIds, $itemIds, $sortingType, $printingPageTitle, $descriptionFormat);

		$topLeftTitle = ( strlen($reportPrintGenerator->printSettings['layoutSetting']['pageNoPrefix']) ) ? 'Prefix: ' . $reportPrintGenerator->printSettings['layoutSetting']['pageNoPrefix'] : '';
		$pages        = $reportPrintGenerator->generatePages();
		$participate  = ( $project->MainInformation->tender_type_id == ProjectMainInformation::TENDER_TYPE_PARTICIPATED ) ? true : false;

		//Pass Parameter & Generate Excel
		$sfItemExport = new sfItemComparisonReportGenerator(
			$project,
			$reportPrintGenerator->getEstimateElementGrandTotals(),
			null,
			$printingPageTitle,
			false,
			$reportPrintGenerator->printSettings
		);

		if ( $participate )
		{
			$sfItemExport->setRationalizedParameter(
				$billColumnSettings,
				$reportPrintGenerator->rationalizedRates,
				$reportPrintGenerator->rationalizedElementGrandTotal
			);
		}
		else
		{
			$sfItemExport->setSelectedParameter(
				$billColumnSettings,
				$reportPrintGenerator->selectedTenderer,
				$reportPrintGenerator->selectedRates,
				$reportPrintGenerator->selectedElementGrandTotal
			);
		}

		$sfItemExport->process($pages, false, $printingPageTitle, $topLeftTitle, $bill->title, $printNoCents, $reportPrintGenerator->totalPage);

		return $this->sendExportExcelHeader($sfItemExport->fileInfo['filename'], $sfItemExport->savePath . DIRECTORY_SEPARATOR . $sfItemExport->fileInfo['filename'] . $sfItemExport->fileInfo['extension']);
	}

	public function executeExportPostContractElementByTypes(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless(
			$bill = ProjectStructureTable::getInstance()->find($request->getParameter('billId')) AND
			$project = Doctrine_Core::getTable('ProjectStructure')->find($bill->root_id)
		);

		session_write_close();

		$printingPageTitle = $request->getParameter('printingPageTitle');
		$descriptionFormat = $request->getParameter('descriptionFormat');
		$printNoCents      = ( $request->getParameter('printNoCents') == 't' ) ? true : false;

		$reportPrintGenerator = new sfBuildspacePostContractReportPageElementByTypeGenerator($project, $bill, $printingPageTitle, $descriptionFormat);
		$pages                = $reportPrintGenerator->generatePages();

		$totalPage = count($pages) - 1;

		$sfElementExport = new sfPostContractElementReportGenerator(
			$project,
			null,
			$printingPageTitle,
			$reportPrintGenerator->printSettings
		);

		$sfElementExport->setParameter(
			$bill->BillColumnSettings->toArray(),
			$reportPrintGenerator->typeTotals,
			$reportPrintGenerator->elementTotals
		);

		$sfElementExport->process($pages, false, $printingPageTitle, $bill->title . ' (' . sfBuildspacePostContractReportPageItemGenerator::CLAIM_PREFIX . '' . $reportPrintGenerator->revision['version'] . ')', $project->title, $printNoCents, $totalPage);

		return $this->sendExportExcelHeader($sfElementExport->fileInfo['filename'], $sfElementExport->savePath . DIRECTORY_SEPARATOR . $sfElementExport->fileInfo['filename'] . $sfElementExport->fileInfo['extension']);
	}

	public function executeExportPostContractElementWithClaimByTypes(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless(
			$bill = ProjectStructureTable::getInstance()->find($request->getParameter('billId')) AND
			$project = Doctrine_Core::getTable('ProjectStructure')->find($bill->root_id)
		);

		session_write_close();

		$printingPageTitle = $request->getParameter('printingPageTitle');
		$descriptionFormat = $request->getParameter('descriptionFormat');
		$printNoCents      = ( $request->getParameter('printNoCents') == 't' ) ? true : false;

		$reportPrintGenerator = new sfBuildspacePostContractReportPageElementByTypeWithClaimGenerator($project, $bill, $printingPageTitle, $descriptionFormat);
		$pages                = $reportPrintGenerator->generatePages();

		$totalPage = count($pages) - 1;

		$sfElementExport = new sfPostContractElementReportWithClaimGenerator(
			$project,
			null,
			$printingPageTitle,
			$reportPrintGenerator->printSettings
		);

		$sfElementExport->setParameter(
			$bill->BillColumnSettings->toArray(),
			$reportPrintGenerator->typeTotals,
			$reportPrintGenerator->elementTotals
		);

		$sfElementExport->setUnitNames($reportPrintGenerator->unitNames);

		$sfElementExport->process($pages, false, $printingPageTitle, $bill->title . ' (' . sfBuildspacePostContractReportPageItemGenerator::CLAIM_PREFIX . '' . $reportPrintGenerator->revision['version'] . ')', $project->title, $printNoCents, $totalPage);

		return $this->sendExportExcelHeader($sfElementExport->fileInfo['filename'], $sfElementExport->savePath . DIRECTORY_SEPARATOR . $sfElementExport->fileInfo['filename'] . $sfElementExport->fileInfo['extension']);
	}

	public function executeExportPostContractElementWithClaimByTypesBySelectedUnits(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless(
			$bill = ProjectStructureTable::getInstance()->find($request->getParameter('billId')) AND
			$project = Doctrine_Core::getTable('ProjectStructure')->find($bill->root_id)
		);

		session_write_close();

		$printingPageTitle = $request->getParameter('printingPageTitle');
		$descriptionFormat = $request->getParameter('descriptionFormat');
		$typeIds           = json_decode($request->getParameter('selectedRows'), true);
		$printNoCents      = ( $request->getParameter('printNoCents') == 't' ) ? true : false;

		$reportPrintGenerator = new sfBuildspacePostContractReportPageElementByTypeSelectedUnits($project, $bill, $printingPageTitle, $descriptionFormat);
		$pages                = $reportPrintGenerator->generatePages($typeIds);

		$totalPage = count($pages) - 1;

		$sfElementExport = new sfPostContractElementReportWithClaimGenerator(
			$project,
			null,
			$printingPageTitle,
			$reportPrintGenerator->printSettings
		);

		$sfElementExport->setParameter(
			$bill->BillColumnSettings->toArray(),
			$reportPrintGenerator->typeTotals,
			$reportPrintGenerator->elementTotals
		);

		$sfElementExport->setUnitNames($reportPrintGenerator->unitNames);

		$sfElementExport->process($pages, false, $printingPageTitle, $bill->title . ' (' . sfBuildspacePostContractReportPageItemGenerator::CLAIM_PREFIX . '' . $reportPrintGenerator->revision['version'] . ')', $project->title, $printNoCents, $totalPage);

		return $this->sendExportExcelHeader($sfElementExport->fileInfo['filename'], $sfElementExport->savePath . DIRECTORY_SEPARATOR . $sfElementExport->fileInfo['filename'] . $sfElementExport->fileInfo['extension']);
	}

	public function executeExportPostContractSelectedElement(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless(
			$bill = ProjectStructureTable::getInstance()->find($request->getParameter('billId')) AND
			$project = Doctrine_Core::getTable('ProjectStructure')->find($bill->root_id) AND
			$typeRef = Doctrine_Core::getTable('PostContractStandardClaimTypeReference')->find($request->getParameter('type_ref_id'))
		);

		session_write_close();

		$printingPageTitle = $request->getParameter('printingPageTitle');
		$descriptionFormat = $request->getParameter('descriptionFormat');
		$elementIds        = json_decode($request->getParameter('selectedRows'), true);
		$typeRefTitle      = ( strlen($typeRef->new_name) ) ? $typeRef->new_name : 'Unit ' . $typeRef->counter;
		$printNoCents      = ( $request->getParameter('printNoCents') == 't' ) ? true : false;
		$printWithoutUnit  = ( $request->getParameter('printNoUnit') == 't' ) ? false : true;


		$reportPrintGenerator = new sfBuildspacePostContractReportPageElementGenerator($project, $bill, $elementIds, $printingPageTitle, $descriptionFormat);
		$pages                = $reportPrintGenerator->generatePages($typeRef);
		$totalPage            = count($pages) - 1;

		$sfElementExport = new sfPostContractStandardElementReport(
			$project,
			null,
			$printingPageTitle,
			$reportPrintGenerator->printSettings
		);

		$sfElementExport->setParameter(
			null,
			$reportPrintGenerator->typeTotals,
			null
		);

		$topLeftRow2 = $bill->title;

		if ( $printWithoutUnit )
		{
			$topLeftRow2 = $topLeftRow2 . ' > ' . $typeRef->BillColumnSetting->name . ' > ' . $typeRefTitle;
		}

		$sfElementExport->setTopRightTitle(' (' . sfBuildspacePostContractReportPageItemGenerator::CLAIM_PREFIX . '' . $reportPrintGenerator->revision['version'] . ')');

		$sfElementExport->process($pages, false, $printingPageTitle, $topLeftRow2, $project->title, $printNoCents, $totalPage);

		return $this->sendExportExcelHeader($sfElementExport->fileInfo['filename'], $sfElementExport->savePath . DIRECTORY_SEPARATOR . $sfElementExport->fileInfo['filename'] . $sfElementExport->fileInfo['extension']);
	}

	public function executeExportPostContractElementWorkdoneOnly(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless(
			$bill = ProjectStructureTable::getInstance()->find($request->getParameter('billId')) AND
			$project = Doctrine_Core::getTable('ProjectStructure')->find($bill->root_id) AND
			$typeRef = Doctrine_Core::getTable('PostContractStandardClaimTypeReference')->find($request->getParameter('type_ref_id'))
		);

		session_write_close();

		$pdo = $bill->getTable()->getConnection()->getDbh();

		$printingPageTitle = $request->getParameter('printingPageTitle');
		$descriptionFormat = $request->getParameter('descriptionFormat');
		$typeRefTitle      = ( strlen($typeRef->new_name) ) ? $typeRef->new_name : 'Unit ' . $typeRef->counter;
		$printNoCents      = ( $request->getParameter('printNoCents') == 't' ) ? true : false;
		$printWithoutUnit  = ( $request->getParameter('printNoUnit') == 't' ) ? false : true;

		$stmt = $pdo->prepare("SELECT e.id FROM " . BillElementTable::getInstance()->getTableName() . " e
            WHERE e.project_structure_id = " . $bill->id . " AND e.deleted_at IS NULL ORDER BY e.priority ASC");
		$stmt->execute();

		$elementIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

		$reportPrintGenerator = new sfBuildspacePostContractReportPageElementGenerator($project, $bill, $elementIds, $printingPageTitle, $descriptionFormat);
		$pages                = $reportPrintGenerator->generatePages($typeRef);

		$totalPage = count($pages) - 1;

		$sfElementExport = new sfPostContractStandardElementReportWorkdoneOnly(
			$project,
			null,
			$printingPageTitle,
			$reportPrintGenerator->printSettings
		);

		$sfElementExport->setParameter(
			null,
			$reportPrintGenerator->typeTotals,
			null
		);

		$topLeftRow2 = $bill->title;

		if ( $printWithoutUnit )
		{
			$topLeftRow2 = $topLeftRow2 . ' > ' . $typeRef->BillColumnSetting->name . ' > ' . $typeRefTitle;
		}

		$sfElementExport->setTopRightTitle(' (' . sfBuildspacePostContractReportPageItemGenerator::CLAIM_PREFIX . '' . $reportPrintGenerator->revision['version'] . ')');

		$sfElementExport->process($pages, false, $printingPageTitle, $topLeftRow2, $project->title, $printNoCents, $totalPage);

		return $this->sendExportExcelHeader($sfElementExport->fileInfo['filename'], $sfElementExport->savePath . DIRECTORY_SEPARATOR . $sfElementExport->fileInfo['filename'] . $sfElementExport->fileInfo['extension']);
	}

	public function executeExportPostContractItem(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless(
			$bill = ProjectStructureTable::getInstance()->find($request->getParameter('billId')) AND
			$project = Doctrine_Core::getTable('ProjectStructure')->find($bill->root_id) AND
			$typeRef = Doctrine_Core::getTable('PostContractStandardClaimTypeReference')->find($request->getParameter('type_ref_id'))
		);

		session_write_close();

		$printingPageTitle = $request->getParameter('printingPageTitle');
		$descriptionFormat = $request->getParameter('descriptionFormat');
		$itemIds           = json_decode($request->getParameter('selectedRows'), true);
		$printNoCents      = ( $request->getParameter('printNoCents') == 't' ) ? true : false;
		$printWithoutUnit  = ( $request->getParameter('printNoUnit') == 't' ) ? false : true;

		$affectedElements = BillElementTable::getAffectedElementIdsByItemIds($request->getParameter('selectedRows'));

		$reportPrintGenerator = new sfBuildspacePostContractReportPageItemGenerator($project, $bill, $affectedElements, $itemIds, $printingPageTitle, $descriptionFormat);

		$typeRefTitle = ( strlen($typeRef->new_name) ) ? $typeRef->new_name : 'Unit ' . $typeRef->counter;
		$topLeftTitle = ( strlen($reportPrintGenerator->printSettings['layoutSetting']['pageNoPrefix']) ) ? 'Prefix: ' . $reportPrintGenerator->printSettings['layoutSetting']['pageNoPrefix'] : '';

		$elementPages = $reportPrintGenerator->generatePages($typeRef);

		$sfItemExport = new sfPostContractItemReportGenerator(
			$project,
			null,
			$printingPageTitle,
			$reportPrintGenerator->printSettings
		);

		$topLeftRow2 = $bill->title;

		if ( $printWithoutUnit )
		{
			$topLeftRow2 = $topLeftRow2 . ' > ' . $typeRef->BillColumnSetting->name . ' > ' . $typeRefTitle;
		}

		$sfItemExport->setParameter($reportPrintGenerator->elementTotals);

		$sfItemExport->setTopRightTitle(' (' . sfBuildspacePostContractReportPageItemGenerator::CLAIM_PREFIX . '' . $reportPrintGenerator->revision['version'] . ')');

		$sfItemExport->process($elementPages, false, $printingPageTitle, $topLeftTitle, $topLeftRow2, $printNoCents, $reportPrintGenerator->totalPage);

		return $this->sendExportExcelHeader($sfItemExport->fileInfo['filename'], $sfItemExport->savePath . DIRECTORY_SEPARATOR . $sfItemExport->fileInfo['filename'] . $sfItemExport->fileInfo['extension']);
	}

	public function executeExportPostContractItemWithCurrentClaim(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless(
			$bill = ProjectStructureTable::getInstance()->find($request->getParameter('billId')) AND
			$project = Doctrine_Core::getTable('ProjectStructure')->find($bill->root_id) AND
			$typeRef = Doctrine_Core::getTable('PostContractStandardClaimTypeReference')->find($request->getParameter('type_ref_id'))
		);

		session_write_close();

		$printingPageTitle = $request->getParameter('printingPageTitle');
		$descriptionFormat = $request->getParameter('descriptionFormat');
		$printNoCents      = ( $request->getParameter('printNoCents') == 't' ) ? true : false;
		$printWithoutUnit  = ( $request->getParameter('printNoUnit') == 't' ) ? false : true;

		$affectedElements = DoctrineQuery::create()
			->select('e.id, e.description, e.note')
			->from('BillElement e')
			->where('e.project_structure_id = ?', $bill->id)
			->addOrderBy('e.priority ASC')
			->fetchArray();

		$reportPrintGenerator = new sfBuildspacePostContractReportPageItemWithCurrentClaimGenerator($project, $bill, $affectedElements, null, $printingPageTitle, $descriptionFormat);

		$typeRefTitle = ( strlen($typeRef->new_name) ) ? $typeRef->new_name : 'Unit ' . $typeRef->counter;
		$topLeftTitle = ( strlen($reportPrintGenerator->printSettings['layoutSetting']['pageNoPrefix']) ) ? 'Prefix: ' . $reportPrintGenerator->printSettings['layoutSetting']['pageNoPrefix'] : '';
		$pages        = $reportPrintGenerator->generatePages($typeRef);

		$sfItemExport = new sfPostContractItemReportGenerator(
			$project,
			null,
			$printingPageTitle,
			$reportPrintGenerator->printSettings
		);

		$topLeftRow2 = $bill->title;

		if ( $printWithoutUnit )
		{
			$topLeftRow2 = $topLeftRow2 . ' > ' . $typeRef->BillColumnSetting->name . ' > ' . $typeRefTitle;
		}

		$sfItemExport->setParameter($reportPrintGenerator->elementTotals);

		$sfItemExport->setTopRightTitle(' (' . sfBuildspacePostContractReportPageItemGenerator::CLAIM_PREFIX . '' . $reportPrintGenerator->revision['version'] . ')');

		$sfItemExport->process($pages, false, $printingPageTitle, $topLeftTitle, $topLeftRow2, $printNoCents, $reportPrintGenerator->totalPage);

		return $this->sendExportExcelHeader($sfItemExport->fileInfo['filename'], $sfItemExport->savePath . DIRECTORY_SEPARATOR . $sfItemExport->fileInfo['filename'] . $sfItemExport->fileInfo['extension']);
	}

	public function executeExportPostContractItemWithClaim(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless(
			$bill = ProjectStructureTable::getInstance()->find($request->getParameter('billId')) AND
			$project = Doctrine_Core::getTable('ProjectStructure')->find($bill->root_id) AND
			$typeRef = Doctrine_Core::getTable('PostContractStandardClaimTypeReference')->find($request->getParameter('type_ref_id'))
		);

		session_write_close();

		$printingPageTitle = $request->getParameter('printingPageTitle');
		$descriptionFormat = $request->getParameter('descriptionFormat');
		$printNoCents      = ( $request->getParameter('printNoCents') == 't' ) ? true : false;
		$printWithoutUnit  = ( $request->getParameter('printNoUnit') == 't' ) ? false : true;

		$affectedElements = DoctrineQuery::create()
			->select('e.id, e.description, e.note')
			->from('BillElement e')
			->where('e.project_structure_id = ?', $bill->id)
			->addOrderBy('e.priority ASC')
			->fetchArray();

		$reportPrintGenerator = new sfBuildspacePostContractReportPageItemWithClaimGenerator($project, $bill, $affectedElements, null, $printingPageTitle, $descriptionFormat);

		$typeRefTitle = ( strlen($typeRef->new_name) ) ? $typeRef->new_name : 'Unit ' . $typeRef->counter;
		$topLeftTitle = ( strlen($reportPrintGenerator->printSettings['layoutSetting']['pageNoPrefix']) ) ? 'Prefix: ' . $reportPrintGenerator->printSettings['layoutSetting']['pageNoPrefix'] : '';
		$pages        = $reportPrintGenerator->generatePages($typeRef);

		$sfItemExport = new sfPostContractItemReportGenerator(
			$project,
			null,
			$printingPageTitle,
			$reportPrintGenerator->printSettings
		);

		$topLeftRow2 = $bill->title;

		if ( $printWithoutUnit )
		{
			$topLeftRow2 = $topLeftRow2 . ' > ' . $typeRef->BillColumnSetting->name . ' > ' . $typeRefTitle;
		}

		$sfItemExport->setParameter($reportPrintGenerator->elementTotals);

		$sfItemExport->setTopRightTitle(' (' . sfBuildspacePostContractReportPageItemGenerator::CLAIM_PREFIX . '' . $reportPrintGenerator->revision['version'] . ')');

		$sfItemExport->process($pages, false, $printingPageTitle, $topLeftTitle, $topLeftRow2, $printNoCents, $reportPrintGenerator->totalPage);

		return $this->sendExportExcelHeader($sfItemExport->fileInfo['filename'], $sfItemExport->savePath . DIRECTORY_SEPARATOR . $sfItemExport->fileInfo['filename'] . $sfItemExport->fileInfo['extension']);
	}

	public function executeExportPostContractItemWorkdoneOnlyWithQty(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless(
			$bill = ProjectStructureTable::getInstance()->find($request->getParameter('billId')) AND
			$project = Doctrine_Core::getTable('ProjectStructure')->find($bill->root_id) AND
			$typeRef = Doctrine_Core::getTable('PostContractStandardClaimTypeReference')->find($request->getParameter('type_ref_id'))
		);

		session_write_close();

		$printingPageTitle = $request->getParameter('printingPageTitle');
		$descriptionFormat = $request->getParameter('descriptionFormat');
		$printNoCents      = ( $request->getParameter('printNoCents') == 't' ) ? true : false;
		$printWithoutUnit  = ( $request->getParameter('printNoUnit') == 't' ) ? false : true;

		$affectedElements = DoctrineQuery::create()
			->select('e.id, e.description, e.note')
			->from('BillElement e')
			->where('e.project_structure_id = ?', $bill->id)
			->addOrderBy('e.priority ASC')
			->fetchArray();

		$reportPrintGenerator = new sfBuildspacePostContractReportPageItemWithClaimGenerator($project, $bill, $affectedElements, null, $printingPageTitle, $descriptionFormat);

		$typeRefTitle = ( strlen($typeRef->new_name) ) ? $typeRef->new_name : 'Unit ' . $typeRef->counter;
		$topLeftTitle = ( strlen($reportPrintGenerator->printSettings['layoutSetting']['pageNoPrefix']) ) ? 'Prefix: ' . $reportPrintGenerator->printSettings['layoutSetting']['pageNoPrefix'] : '';
		$pages        = $reportPrintGenerator->generatePages($typeRef);

		$sfItemExport = new sfPostContractItemReportGeneratorWorkdoneOnlyQty(
			$project,
			null,
			$printingPageTitle,
			$reportPrintGenerator->printSettings
		);

		$topLeftRow2 = $bill->title;

		if ( $printWithoutUnit )
		{
			$topLeftRow2 = $topLeftRow2 . ' > ' . $typeRef->BillColumnSetting->name . ' > ' . $typeRefTitle;
		}

		$sfItemExport->setParameter($reportPrintGenerator->elementTotals);

		$sfItemExport->setTopRightTitle(' (' . sfBuildspacePostContractReportPageItemGenerator::CLAIM_PREFIX . '' . $reportPrintGenerator->revision['version'] . ')');

		$sfItemExport->process($pages, false, $printingPageTitle, $topLeftTitle, $topLeftRow2, $printNoCents, $reportPrintGenerator->totalPage);

		return $this->sendExportExcelHeader($sfItemExport->fileInfo['filename'], $sfItemExport->savePath . DIRECTORY_SEPARATOR . $sfItemExport->fileInfo['filename'] . $sfItemExport->fileInfo['extension']);
	}

	public function executeExportPostContractItemWorkdoneOnlyWithPercentage(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless(
			$bill = ProjectStructureTable::getInstance()->find($request->getParameter('billId')) AND
			$project = Doctrine_Core::getTable('ProjectStructure')->find($bill->root_id) AND
			$typeRef = Doctrine_Core::getTable('PostContractStandardClaimTypeReference')->find($request->getParameter('type_ref_id'))
		);

		session_write_close();

		$printingPageTitle = $request->getParameter('printingPageTitle');
		$descriptionFormat = $request->getParameter('descriptionFormat');
		$printNoCents      = ( $request->getParameter('printNoCents') == 't' ) ? true : false;
		$printWithoutUnit  = ( $request->getParameter('printNoUnit') == 't' ) ? false : true;

		$affectedElements = DoctrineQuery::create()
			->select('e.id, e.description, e.note')
			->from('BillElement e')
			->where('e.project_structure_id = ?', $bill->id)
			->addOrderBy('e.priority ASC')
			->fetchArray();

		$reportPrintGenerator = new sfBuildspacePostContractReportPageItemWithClaimGenerator($project, $bill, $affectedElements, null, $printingPageTitle, $descriptionFormat);

		$typeRefTitle = ( strlen($typeRef->new_name) ) ? $typeRef->new_name : 'Unit ' . $typeRef->counter;
		$topLeftTitle = ( strlen($reportPrintGenerator->printSettings['layoutSetting']['pageNoPrefix']) ) ? 'Prefix: ' . $reportPrintGenerator->printSettings['layoutSetting']['pageNoPrefix'] : '';
		$pages        = $reportPrintGenerator->generatePages($typeRef);

		$sfItemExport = new sfPostContractItemReportGeneratorWorkdoneOnlyPercentage(
			$project,
			null,
			$printingPageTitle,
			$reportPrintGenerator->printSettings
		);

		$topLeftRow2 = $bill->title;

		if ( $printWithoutUnit )
		{
			$topLeftRow2 = $topLeftRow2 . ' > ' . $typeRef->BillColumnSetting->name . ' > ' . $typeRefTitle;
		}

		$sfItemExport->setParameter($reportPrintGenerator->elementTotals);

		$sfItemExport->setTopRightTitle(' (' . sfBuildspacePostContractReportPageItemGenerator::CLAIM_PREFIX . '' . $reportPrintGenerator->revision['version'] . ')');

		$sfItemExport->process($pages, false, $printingPageTitle, $topLeftTitle, $topLeftRow2, $printNoCents, $reportPrintGenerator->totalPage);

		return $this->sendExportExcelHeader($sfItemExport->fileInfo['filename'], $sfItemExport->savePath . DIRECTORY_SEPARATOR . $sfItemExport->fileInfo['filename'] . $sfItemExport->fileInfo['extension']);
	}

	public function executeExportPostContractPrelimSelectedItem(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless(
			$bill = ProjectStructureTable::getInstance()->find($request->getParameter('billId')) AND
			$project = Doctrine_Core::getTable('ProjectStructure')->find($bill->root_id)
		);

		session_write_close();

		$printingPageTitle = $request->getParameter('printingPageTitle');
		$descriptionFormat = $request->getParameter('descriptionFormat');
		$itemIds           = json_decode($request->getParameter('selectedRows'), true);
		$printNoCents      = ( $request->getParameter('printNoCents') == 't' ) ? true : false;

		$affectedElements = BillElementTable::getAffectedElementIdsByItemIds($request->getParameter('selectedRows'));

		$type = null;

		$reportPrintGenerator = new sfBuildspacePostContractPrelimReportPageItemGenerator($project, $bill, $affectedElements, $itemIds, $printingPageTitle, $descriptionFormat, $type);

		$topLeftTitle = ( strlen($reportPrintGenerator->printSettings['layoutSetting']['pageNoPrefix']) ) ? 'Prefix: ' . $reportPrintGenerator->printSettings['layoutSetting']['pageNoPrefix'] : '';
		$pages        = $reportPrintGenerator->generatePages();

		$sfItemExport = new sfPostContractPrelimItemReportGenerator(
			$project,
			null,
			$printingPageTitle,
			$reportPrintGenerator->printSettings
		);

		$sfItemExport->setParameter($reportPrintGenerator->elementTotals);

		$sfItemExport->setTopRightTitle(' (' . sfBuildspacePostContractReportPageItemGenerator::CLAIM_PREFIX . '' . $reportPrintGenerator->revision['version'] . ')');

		$sfItemExport->process($pages, false, $printingPageTitle, $topLeftTitle, $bill->title, $printNoCents, $reportPrintGenerator->totalPage);

		return $this->sendExportExcelHeader($sfItemExport->fileInfo['filename'], $sfItemExport->savePath . DIRECTORY_SEPARATOR . $sfItemExport->fileInfo['filename'] . $sfItemExport->fileInfo['extension']);
	}

	public function executeExportPostContractPrelimSelectedItemWithClaim(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless(
			$bill = ProjectStructureTable::getInstance()->find($request->getParameter('billId')) AND
			$project = Doctrine_Core::getTable('ProjectStructure')->find($bill->root_id)
		);

		session_write_close();

		$printingPageTitle = $request->getParameter('printingPageTitle');
		$descriptionFormat = $request->getParameter('descriptionFormat');
		$itemIds           = json_decode($request->getParameter('selectedRows'), true);
		$printNoCents      = ( $request->getParameter('printNoCents') == 't' ) ? true : false;

		$affectedElements = BillElementTable::getAffectedElementIdsByItemIds($request->getParameter('selectedRows'));

		$type = null;

		$reportPrintGenerator = new sfBuildspacePostContractPrelimReportPageItemGenerator($project, $bill, $affectedElements, $itemIds, $printingPageTitle, $descriptionFormat, $type);

		$topLeftTitle = ( strlen($reportPrintGenerator->printSettings['layoutSetting']['pageNoPrefix']) ) ? 'Prefix: ' . $reportPrintGenerator->printSettings['layoutSetting']['pageNoPrefix'] : '';
		$pages        = $reportPrintGenerator->generatePages();

		$sfItemExport = new sfPostContractPrelimItemReportGeneratorWithClaim(
			$project,
			null,
			$printingPageTitle,
			$reportPrintGenerator->printSettings
		);

		$sfItemExport->setParameter($reportPrintGenerator->elementTotals);

		$sfItemExport->setTopRightTitle(' (' . sfBuildspacePostContractReportPageItemGenerator::CLAIM_PREFIX . '' . $reportPrintGenerator->revision['version'] . ')');

		$sfItemExport->process($pages, false, $printingPageTitle, $topLeftTitle, $bill->title, $printNoCents, $reportPrintGenerator->totalPage);

		return $this->sendExportExcelHeader($sfItemExport->fileInfo['filename'], $sfItemExport->savePath . DIRECTORY_SEPARATOR . $sfItemExport->fileInfo['filename'] . $sfItemExport->fileInfo['extension']);
	}

	public function executeExportPostContractPrelimAllItemWithClaimMoreThanZero(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless(
			$bill = ProjectStructureTable::getInstance()->find($request->getParameter('billId')) AND
			$project = Doctrine_Core::getTable('ProjectStructure')->find($bill->root_id)
		);

		session_write_close();

		$printingPageTitle = $request->getParameter('printingPageTitle');
		$descriptionFormat = $request->getParameter('descriptionFormat');
		$itemIds           = json_decode($request->getParameter('selectedRows'), true);
		$printNoCents      = ( $request->getParameter('printNoCents') == 't' ) ? true : false;

		$type = 'currentClaim-amount';

		// get available bill element(s)
		$affectedElements = Doctrine_Query::create()
			->select('e.id, e.description')
			->from('BillElement e')
			->where('e.project_structure_id = ?', $bill->id)
			->orderBy('e.priority ASC')
			->fetchArray();

		$reportPrintGenerator = new sfBuildspacePostContractPrelimReportPageAllItemGeneratorMoreThanZero($project, $bill, $affectedElements, $itemIds, $printingPageTitle, $descriptionFormat, $type);

		$topLeftTitle = ( strlen($reportPrintGenerator->printSettings['layoutSetting']['pageNoPrefix']) ) ? 'Prefix: ' . $reportPrintGenerator->printSettings['layoutSetting']['pageNoPrefix'] : '';
		$pages        = $reportPrintGenerator->generatePages();

		$sfItemExport = new sfPostContractPrelimItemReportGeneratorWithClaim(
			$project,
			null,
			$printingPageTitle,
			$reportPrintGenerator->printSettings
		);

		$sfItemExport->setParameter($reportPrintGenerator->elementTotals);

		$sfItemExport->setTopRightTitle(' (' . sfBuildspacePostContractReportPageItemGenerator::CLAIM_PREFIX . '' . $reportPrintGenerator->revision['version'] . ')');

		$sfItemExport->process($pages, false, $printingPageTitle, $topLeftTitle, $bill->title, $printNoCents, $reportPrintGenerator->totalPage);

		return $this->sendExportExcelHeader($sfItemExport->fileInfo['filename'], $sfItemExport->savePath . DIRECTORY_SEPARATOR . $sfItemExport->fileInfo['filename'] . $sfItemExport->fileInfo['extension']);
	}

	public function executeExportPostContractPrelimAllItemWithClaim(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless(
			$bill = ProjectStructureTable::getInstance()->find($request->getParameter('billId')) AND
			$project = Doctrine_Core::getTable('ProjectStructure')->find($bill->root_id)
		);

		session_write_close();

		$printingPageTitle = $request->getParameter('printingPageTitle');
		$descriptionFormat = $request->getParameter('descriptionFormat');
		$itemIds           = json_decode($request->getParameter('selectedRows'), true);
		$printNoCents      = ( $request->getParameter('printNoCents') == 't' ) ? true : false;

		$type = 'upToDateClaim-amount';

		// get available bill element(s)
		$affectedElements = Doctrine_Query::create()
			->select('e.id, e.description')
			->from('BillElement e')
			->where('e.project_structure_id = ?', $bill->id)
			->orderBy('e.priority ASC')
			->fetchArray();

		$reportPrintGenerator = new sfBuildspacePostContractPrelimReportPageAllItemGenerator($project, $bill, $affectedElements, $itemIds, $printingPageTitle, $descriptionFormat, $type);

		$topLeftTitle = ( strlen($reportPrintGenerator->printSettings['layoutSetting']['pageNoPrefix']) ) ? 'Prefix: ' . $reportPrintGenerator->printSettings['layoutSetting']['pageNoPrefix'] : '';
		$pages        = $reportPrintGenerator->generatePages();

		$sfItemExport = new sfPostContractPrelimItemReportGeneratorWithClaim(
			$project,
			null,
			$printingPageTitle,
			$reportPrintGenerator->printSettings
		);

		$sfItemExport->setParameter($reportPrintGenerator->elementTotals);

		$sfItemExport->setTopRightTitle(' (' . sfBuildspacePostContractReportPageItemGenerator::CLAIM_PREFIX . '' . $reportPrintGenerator->revision['version'] . ')');

		$sfItemExport->process($pages, false, $printingPageTitle, $topLeftTitle, $bill->title, $printNoCents, $reportPrintGenerator->totalPage);

		return $this->sendExportExcelHeader($sfItemExport->fileInfo['filename'], $sfItemExport->savePath . DIRECTORY_SEPARATOR . $sfItemExport->fileInfo['filename'] . $sfItemExport->fileInfo['extension']);
	}

	public function executeExportSelectedResourceItem(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless(
			$request->hasParameter('resourceId') AND
			$project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('projectId'))
		);

		session_write_close();

		$printingPageTitle = $request->getParameter('printingPageTitle');
		$descriptionFormat = $request->getParameter('descriptionFormat');
		$tradeIds          = json_decode($request->getParameter('selectedRows'), true);
		$printNoCents      = ( $request->getParameter('printNoCents') == 't' ) ? true : false;

		$reportPrintGenerator = new sfBuildspaceResourceItemGenerator($project, $request->getParameter('resourceId'), $tradeIds, $printingPageTitle, $descriptionFormat);
		$tradePages           = $reportPrintGenerator->generatePages();
        $includeClaimInformation = ($project->PostContract->exists() && ($request->getParameter('type') == ProjectMainInformation::STATUS_POSTCONTRACT));

		$sfItemExport = new sfResourceItemReportGenerator(
			$project,
			array(),
			null,
			$printingPageTitle,
			$reportPrintGenerator->printSettings,
            $includeClaimInformation
		);

		$sfItemExport->process($tradePages, false, $printingPageTitle, $reportPrintGenerator->resource['name'], $project->title, $printNoCents, $reportPrintGenerator->totalPage);

		return $this->sendExportExcelHeader($sfItemExport->fileInfo['filename'], $sfItemExport->savePath . DIRECTORY_SEPARATOR . $sfItemExport->fileInfo['filename'] . $sfItemExport->fileInfo['extension']);
	}

	public function executeExportSelectedTradeBillItem(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless(
			$project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('projectId')) AND
			$trade = Doctrine_Core::getTable('ResourceTrade')->find($request->getParameter('tradeId'))
		);

		session_write_close();

		$printingPageTitle = $request->getParameter('printingPageTitle');
		$descriptionFormat = $request->getParameter('descriptionFormat');
		$tradeItemIds      = json_decode($request->getParameter('selectedTradeItemIds'), true);
		$billItemIds       = json_decode($request->getParameter('selectedRows'), true);
		$printNoCents      = ( $request->getParameter('printNoCents') == 't' ) ? true : false;

		$reportPrintGenerator = new sfBuildspaceResourceTradeItemGenerator($project, $request->getParameter('resourceId'), $tradeItemIds, $billItemIds, $printingPageTitle, $descriptionFormat);
		$resourcePages        = $reportPrintGenerator->generatePages();

		$sfItemExport = new sfResourceTradeItemReportGenerator(
			$project,
			array(),
			null,
			$printingPageTitle,
			$reportPrintGenerator->printSettings
		);

		$sfItemExport->setHeaderParameter($reportPrintGenerator->billElementIdToDescription, $reportPrintGenerator->resourceIdToDescription);

		$sfItemExport->process($resourcePages, false, $printingPageTitle, '', $reportPrintGenerator->resource['name'], $printNoCents, $reportPrintGenerator->totalPage);

		return $this->sendExportExcelHeader($sfItemExport->fileInfo['filename'], $sfItemExport->savePath . DIRECTORY_SEPARATOR . $sfItemExport->fileInfo['filename'] . $sfItemExport->fileInfo['extension']);
	}

	public function executeExportSelectedScheduleOfRateTradeItems(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless(
			$project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('projectId')) AND
			$scheduleOfRate = Doctrine_Core::getTable('ScheduleOfRate')->find($request->getParameter('scheduleOfRateId'))
		);

		session_write_close();

		$printingPageTitle = $request->getParameter('printingPageTitle');
		$descriptionFormat = $request->getParameter('descriptionFormat');
		$tradeIds          = json_decode($request->getParameter('selectedRows'), true);
		$printNoCents      = ( $request->getParameter('printNoCents') == 't' ) ? true : false;

		$reportPrintGenerator = new sfBuildspaceScheduleOfRateItemGenerator($project, $scheduleOfRate, $tradeIds, $printingPageTitle, $descriptionFormat);
		$tradePages           = $reportPrintGenerator->generatePages();

		$sfItemExport = new sfSorItemReportGenerator(
			$project,
			array(),
			null,
			$printingPageTitle,
			$reportPrintGenerator->printSettings
		);

		$sfItemExport->process($tradePages, false, $printingPageTitle, $reportPrintGenerator->scheduleOfRate['name'], $project->title, $printNoCents, $reportPrintGenerator->totalPage);

		return $this->sendExportExcelHeader($sfItemExport->fileInfo['filename'], $sfItemExport->savePath . DIRECTORY_SEPARATOR . $sfItemExport->fileInfo['filename'] . $sfItemExport->fileInfo['extension']);
	}

	public function executeExportSelectedScheduleOfRateTradeItemsWithSelectedTendererRates(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless(
			$project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('projectId')) AND
			$scheduleOfRate = Doctrine_Core::getTable('ScheduleOfRate')->find($request->getParameter('scheduleOfRateId'))
		);

		session_write_close();

		$printingPageTitle = $request->getParameter('printingPageTitle');
		$descriptionFormat = $request->getParameter('descriptionFormat');
		$tradeIds          = json_decode($request->getParameter('selectedRows'), true);
		$printNoCents      = ( $request->getParameter('printNoCents') == 't' ) ? true : false;

		$reportPrintGenerator = new sfBuildspaceScheduleOfRateWithSelectedTendererRatesItemGenerator($project, $scheduleOfRate, $tradeIds, $printingPageTitle, $descriptionFormat);
		$tradePages           = $reportPrintGenerator->generatePages();

		$sfItemExport = new sfSorItemReportGenerator(
			$project,
			array(),
			null,
			$printingPageTitle,
			$reportPrintGenerator->printSettings
		);

		$sfItemExport->process($tradePages, false, $printingPageTitle, $reportPrintGenerator->scheduleOfRate['name'], $project->title, $printNoCents, $reportPrintGenerator->totalPage);

		return $this->sendExportExcelHeader($sfItemExport->fileInfo['filename'], $sfItemExport->savePath . DIRECTORY_SEPARATOR . $sfItemExport->fileInfo['filename'] . $sfItemExport->fileInfo['extension']);
	}

    public function executeExportSelectedScheduleOfRateTradeItemsCostAnalysis(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('projectId')) AND
            $scheduleOfRate = Doctrine_Core::getTable('ScheduleOfRate')->find($request->getParameter('scheduleOfRateId'))
        );

        session_write_close();

        $printingPageTitle = $request->getParameter('printingPageTitle');
        $descriptionFormat = $request->getParameter('descriptionFormat');
        $tradeIds          = json_decode($request->getParameter('selectedRows'), true);
        $printNoCents      = ( $request->getParameter('printNoCents') == 't' ) ? true : false;

        $reportPrintGenerator = new sfBuildspaceScheduleOfRateItemCostAnalysisGenerator($project, $scheduleOfRate, $tradeIds, $printingPageTitle, $descriptionFormat);
        $tradePages           = $reportPrintGenerator->generatePages();

        $sfItemExport = new sfSorItemCostAnalysisReportGenerator(
            $project,
            null,
            $printingPageTitle,
            $reportPrintGenerator->printSettings
        );

        $sfItemExport->setParameters($reportPrintGenerator->scheduleOfRateItemCosts, $reportPrintGenerator->scheduleOfRateTradeResourceTotals);

        $sfItemExport->process($tradePages, false, $printingPageTitle, $reportPrintGenerator->scheduleOfRate['name'], $project->title, $printNoCents, $reportPrintGenerator->totalPage);

        return $this->sendExportExcelHeader($sfItemExport->fileInfo['filename'], $sfItemExport->savePath . DIRECTORY_SEPARATOR . $sfItemExport->fileInfo['filename'] . $sfItemExport->fileInfo['extension']);
    }

    public function executeExportSelectedScheduleOfRateTradeBillItemCostAnalysis(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('projectId')) and
            $scheduleOfRate = Doctrine_Core::getTable('ScheduleOfRate')->find($request->getParameter('scheduleOfRateId')) and
            $scheduleOfRateTrade = Doctrine_Core::getTable('ScheduleOfRateTrade')->find($request->getParameter('tradeId'))
        );

        session_write_close();

        $printingPageTitle = $request->getParameter('printingPageTitle');
        $descriptionFormat = $request->getParameter('descriptionFormat');
        $tradeItemIds      = json_decode($request->getParameter('tradeItemIds'), true);
        $billItemIds       = json_decode($request->getParameter('selectedRows'), true);
        $printNoCents      = ( $request->getParameter('printNoCents') == 't' ) ? true : false;

        $reportPrintGenerator = new sfBuildspaceScheduleOfRateTradeItemCostAnalysisGenerator($project, $scheduleOfRate, $scheduleOfRateTrade, $tradeItemIds, $billItemIds, $printingPageTitle, $descriptionFormat);
        $scheduleOfRatePages  = $reportPrintGenerator->generatePages();

        $sfItemExport = new sfSorTradeItemCostAnalysisReportGenerator(
            $project,
            $scheduleOfRateTrade,
            $reportPrintGenerator->billItemResourceRates,
            $reportPrintGenerator->profitFromBillMarkup,
            $reportPrintGenerator->scheduleOfRatesNoBuildUp,
            null,
            $printingPageTitle,
            $reportPrintGenerator->printSettings
        );

        $sfItemExport->setHeaderParameter($reportPrintGenerator->billElementIdToDescription, $reportPrintGenerator->tradeIdToDescription);

        $sfItemExport->process($scheduleOfRatePages, false, $printingPageTitle, $project->title, $reportPrintGenerator->scheduleOfRate['name'], $printNoCents, $reportPrintGenerator->totalPage);

        return $this->sendExportExcelHeader($sfItemExport->fileInfo['filename'], $sfItemExport->savePath . DIRECTORY_SEPARATOR . $sfItemExport->fileInfo['filename'] . $sfItemExport->fileInfo['extension']);
    }

	public function executeExportSelectedScheduleOfRateTradeBillItem(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless(
			$project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('projectId')) and
			$scheduleOfRate = Doctrine_Core::getTable('ScheduleOfRate')->find($request->getParameter('scheduleOfRateId')) and
			$scheduleOfRateTrade = Doctrine_Core::getTable('ScheduleOfRateTrade')->find($request->getParameter('tradeId'))
		);

		session_write_close();

		$printingPageTitle = $request->getParameter('printingPageTitle');
		$descriptionFormat = $request->getParameter('descriptionFormat');
		$tradeItemIds      = json_decode($request->getParameter('tradeItemIds'), true);
		$billItemIds       = json_decode($request->getParameter('selectedRows'), true);
		$printNoCents      = ( $request->getParameter('printNoCents') == 't' ) ? true : false;

		$reportPrintGenerator = new sfBuildspaceScheduleOfRateTradeItemGenerator($project, $scheduleOfRate, $scheduleOfRateTrade, $tradeItemIds, $billItemIds, $printingPageTitle, $descriptionFormat);
		$scheduleOfRatePages  = $reportPrintGenerator->generatePages();

		$sfItemExport = new sfSorTradeItemReportGenerator(
			$project,
			array(),
			null,
			$printingPageTitle,
			$reportPrintGenerator->printSettings
		);

		$sfItemExport->setHeaderParameter($reportPrintGenerator->billElementIdToDescription, $reportPrintGenerator->tradeIdToDescription);

		$sfItemExport->process($scheduleOfRatePages, false, $printingPageTitle, '', $reportPrintGenerator->scheduleOfRate['name'], $printNoCents, $reportPrintGenerator->totalPage);

		return $this->sendExportExcelHeader($sfItemExport->fileInfo['filename'], $sfItemExport->savePath . DIRECTORY_SEPARATOR . $sfItemExport->fileInfo['filename'] . $sfItemExport->fileInfo['extension']);
	}

	public function executeExportSelectedScheduleOfRateTradeBillItemWithSelectedTendererRates(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless(
			$project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('projectId')) and
			$scheduleOfRate = Doctrine_Core::getTable('ScheduleOfRate')->find($request->getParameter('scheduleOfRateId')) and
			$scheduleOfRateTrade = Doctrine_Core::getTable('ScheduleOfRateTrade')->find($request->getParameter('tradeId'))
		);

		session_write_close();

		$printingPageTitle = $request->getParameter('printingPageTitle');
		$descriptionFormat = $request->getParameter('descriptionFormat');
		$tradeItemIds      = json_decode($request->getParameter('tradeItemIds'), true);
		$billItemIds       = json_decode($request->getParameter('selectedRows'), true);
		$printNoCents      = ( $request->getParameter('printNoCents') == 't' ) ? true : false;

		$reportPrintGenerator = new sfBuildspaceScheduleOfRateTradeItemWithSelectedTendererRatesGenerator($project, $scheduleOfRate, $scheduleOfRateTrade, $tradeItemIds, $billItemIds, $printingPageTitle, $descriptionFormat);
		$scheduleOfRatePages  = $reportPrintGenerator->generatePages();

		$sfItemExport = new sfSorTradeItemReportGenerator(
			$project,
			array(),
			null,
			$printingPageTitle,
			$reportPrintGenerator->printSettings
		);

		$sfItemExport->setHeaderParameter($reportPrintGenerator->billElementIdToDescription, $reportPrintGenerator->tradeIdToDescription);

		$sfItemExport->process($scheduleOfRatePages, false, $printingPageTitle, '', $reportPrintGenerator->scheduleOfRate['name'], $printNoCents, $reportPrintGenerator->totalPage);

		return $this->sendExportExcelHeader($sfItemExport->fileInfo['filename'], $sfItemExport->savePath . DIRECTORY_SEPARATOR . $sfItemExport->fileInfo['filename'] . $sfItemExport->fileInfo['extension']);
	}

	public function executeExportSelectedVO(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless(
			$project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('projectId'))
		);

		session_write_close();

		$printingPageTitle = $request->getParameter('printingPageTitle');
		$descriptionFormat = $request->getParameter('descriptionFormat');
		$voIds             = json_decode($request->getParameter('selectedRows'), true);
		$printNoCents      = ( $request->getParameter('printNoCents') == 't' ) ? true : false;

		$reportPrintGenerator = new sfBuildspaceVOSummaryReportGenerator($project, $voIds, $printingPageTitle, $descriptionFormat);
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

	public function executeExportVOWithClaims(sfWebRequest $request)
	{
		$this->forward404Unless(
			$project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('projectId'))
		);

		session_write_close();

		$printingPageTitle = $request->getParameter('printingPageTitle');
		$descriptionFormat = $request->getParameter('descriptionFormat');
		$printNoCents      = ( $request->getParameter('printNoCents') == 't' ) ? true : false;

		$reportPrintGenerator = new sfBuildspaceVOWithClaimsReportGenerator($project, $printingPageTitle, $descriptionFormat);
		$pages                = $reportPrintGenerator->generatePages();

		$totalPage = count($pages) - 1;

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

	public function executeExportSelectedVOItemsDialog(sfWebRequest $request)
	{
		$this->forward404Unless(
			$project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('projectId'))
		);

		session_write_close();

		$printingPageTitle = $request->getParameter('printingPageTitle');
		$descriptionFormat = $request->getParameter('descriptionFormat');
		$itemIds           = json_decode($request->getParameter('selectedRows'), true);
		$printNoCents      = ( $request->getParameter('printNoCents') == 't' ) ? true : false;

		$reportPrintGenerator = new sfBuildspaceVOItemsReportGenerator($project, $itemIds, $printingPageTitle, $descriptionFormat);
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

	public function executeExportVOItemsWithClaims(sfWebRequest $request)
	{
		$this->forward404Unless(
			$project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('projectId'))
		);

		session_write_close();

		$printingPageTitle = $request->getParameter('printingPageTitle');
		$descriptionFormat = $request->getParameter('descriptionFormat');
		$printNoCents      = ( $request->getParameter('printNoCents') == 't' ) ? true : false;

		$reportPrintGenerator = new sfBuildspaceVOItemsWithClaimReportGenerator($project, $printingPageTitle, $descriptionFormat);
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

	public function executeExportSelectedVOItemsWithBuildUpQty(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless(
			$request->isMethod('POST') AND
			$project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('projectId'))
		);

		session_write_close();

		$itemIds           = json_decode($request->getParameter('selectedRows'), true);
		$data              = array();
		$printingPageTitle = $request->getParameter('printingPageTitle');
		$descriptionFormat = $request->getParameter('descriptionFormat');
		$printNoCents      = ( $request->getParameter('printNoCents') == 't' ) ? true : false;

		if ( !empty( $itemIds ) )
		{
			list(
				$data, $variationOrders,
				$buildUpItemsSummaries, $unitsDimensions, $buildUpItemsWithType
				) = VariationOrderItemTable::getVOItemsStructure($project, $itemIds);
		}

		$reportPrintGenerator = new sfBuildSpaceVariationOrderBuildUpItemGenerator($project, $descriptionFormat);
		$reportPrintGenerator->setOrientationAndSize('portrait');

		$excelGenerator = new sfVariationOrderItemBuildUpQtyExcelGenerator($project, $printingPageTitle, $reportPrintGenerator->getPrintSettings());

		if ( empty( $data ) )
		{
			$excelGenerator->generateExcelFile();

			return $this->sendExportExcelHeader($excelGenerator->fileInfo['filename'], $excelGenerator->savePath . DIRECTORY_SEPARATOR . $excelGenerator->fileInfo['filename'] . $excelGenerator->fileInfo['extension']);
		}

		list(
			$soqItemsData, $soqFormulatedColumns, $manualBuildUpQuantityItems, $importedBuildUpQuantityItems
			) = ScheduleOfQuantityVariationOrderItemXrefTable::getSelectedItemsBuildUpQuantity($project, $data);

		// first level will be looping variation order, then bill associated with it
		foreach ( $variationOrders as $variationOrder )
		{
			$voItems = ( isset( $data[$variationOrder['id']] ) ) ? $data[$variationOrder['id']] : array();

			foreach ( $voItems as $voItem )
			{
				$dimensions = array();
				$voItemId   = $voItem['id'];

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
					if ( $variationOrderBuildUpQtyType == VariationOrderBuildUpQuantityItem::TYPE_OMISSION_QTY AND !$voItem['has_omission'] )
					{
						continue;
					}

					if ( $variationOrderBuildUpQtyType == VariationOrderBuildUpQuantityItem::TYPE_ADDITIONAL_QTY AND !$voItem['has_addition'] )
					{
						continue;
					}

					$voQtyTypeText   = VariationOrderBuildUpQuantityItemTable::getTypeText($variationOrderBuildUpQtyType);
					$soqBuildUpItems = array();

					$voItemsBuildUpItems = isset( $buildUpItemsWithType[$variationOrderBuildUpQtyType][$voItemId] ) ? $buildUpItemsWithType[$variationOrderBuildUpQtyType][$voItemId] : array();

					$buildUpQuantitySummaryInfo = array();

					if ( isset( $soqItemsData[$variationOrderBuildUpQtyType][$voItemId] ) )
					{
						$soqBuildUpItems = $soqItemsData[$variationOrderBuildUpQtyType][$voItemId];

						unset( $soqItemsData[$variationOrderBuildUpQtyType][$voItemId] );
					}

					if ( isset( $buildUpItemsSummaries[$variationOrderBuildUpQtyType][$voItemId] ) )
					{
						$buildUpQuantitySummaryInfo = $buildUpItemsSummaries[$variationOrderBuildUpQtyType][$voItemId];

						unset( $buildUpItemsSummaries[$variationOrderBuildUpQtyType][$voItemId] );
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

		unset( $variationOrders, $soqItemsData, $soqFormulatedColumns, $manualBuildUpQuantityItems, $importedBuildUpQuantityItems );

		$excelGenerator->generateExcelFile();

		return $this->sendExportExcelHeader($excelGenerator->fileInfo['filename'], $excelGenerator->savePath . DIRECTORY_SEPARATOR . $excelGenerator->fileInfo['filename'] . $excelGenerator->fileInfo['extension']);
	}

	public function executeExportSelectedElementSummaryPerUnitTypeByTenderer(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless(
			$bill = ProjectStructureTable::getInstance()->find($request->getParameter('billId'))
		);

		session_write_close();

		$project = ProjectStructureTable::getInstance()->find($bill->root_id);

		//Setup Necessary parameter
		$printingPageTitle = $request->getParameter('printingPageTitle');
		$descriptionFormat = $request->getParameter('descriptionFormat');
		$tendererIds       = json_decode($request->getParameter('selectedTenderers'), true);
		$elementIds        = json_decode($request->getParameter('selectedRows'), true);
		$printNoCents      = ( $request->getParameter('printNoCents') == 't' ) ? true : false;

		$reportPrintGenerator = new sfBuildSpaceReportElementByTendererAndType($bill, $tendererIds, $elementIds, $printingPageTitle, $descriptionFormat);

		//Generate Pages
		$pages     = $reportPrintGenerator->generatePages();
		$totalPage = count($pages) - 1;

		$sfElementExport = new sfElementReportByTypesGenerator(
			$project,
			null,
			$printingPageTitle,
			$reportPrintGenerator->printSettings
		);

		$sfElementExport->setTenderer($reportPrintGenerator->tenderers);
		$sfElementExport->setEstimateOverAllTotal($reportPrintGenerator->estimateOverAllTotal);
		$sfElementExport->setContractorOverAllTotal($reportPrintGenerator->contractorOverAllTotal);

		$sfElementExport->setupExportExcelPage(false, $printingPageTitle, $project->title, $printNoCents, $totalPage);

		foreach ( $reportPrintGenerator->billColumnSettings as $billColumnSetting )
		{
			$sfElementExport->setCurrentBillColumnSetting($billColumnSetting);

			$sfElementExport->process($pages, false, $printingPageTitle, $bill->title, $project->title, $printNoCents, $totalPage);
		}

		$sfElementExport->endWritingExcelProcess();

		return $this->sendExportExcelHeader($sfElementExport->fileInfo['filename'], $sfElementExport->savePath . DIRECTORY_SEPARATOR . $sfElementExport->fileInfo['filename'] . $sfElementExport->fileInfo['extension']);
	}

	public function executeExportFinalAccountStatement(sfWebRequest $request)
    {
        $this->forward404Unless(
            $project = ProjectStructureTable::getInstance()->find($request->getParameter('pid')) and
            $project->type == ProjectStructure::TYPE_ROOT
        );

        $reportGenerator = new sfFinalAccountStatementExcelReportGenerator($project);

        $reportGenerator->generateReport();

        return $this->sendExportExcelHeader($reportGenerator->fileInfo['filename'], $reportGenerator->savePath . DIRECTORY_SEPARATOR . $reportGenerator->fileInfo['filename'] . $reportGenerator->fileInfo['extension']);
    }

    public function executeExportVariationOrderReport(sfWebRequest $request)
    {
        $this->forward404Unless(
            $project = ProjectStructureTable::getInstance()->find($request->getParameter('pid')) and
            $project->type == ProjectStructure::TYPE_ROOT
        );

		$rfvIds = $request->getParameter('rfvIds');
        $reportGenerator = new sfVariationOrderExcelReportGenerator($project, $rfvIds);

        $reportGenerator->generateReport();

        return $this->sendExportExcelHeader($reportGenerator->fileInfo['filename'], $reportGenerator->savePath . DIRECTORY_SEPARATOR . $reportGenerator->fileInfo['filename'] . $reportGenerator->fileInfo['extension']);
	}
	
	public function executeExportPostContractAccountingReport(sfWebRequest $request)
	{
		$this->forward404Unless(
            $project = ProjectStructureTable::getInstance()->find($request->getParameter('pid')) and
            $project->type == ProjectStructure::TYPE_ROOT and
			$claimCertificate = ClaimCertificateTable::getInstance()->find($request->getParameter('cid'))
		);

		$projectCodeSettingIds = explode(',', $request->getParameter('projectCodeSettingIds'));

		$reportGenerator = new sfPostContractAccountingReportGenerator($project, $claimCertificate, $projectCodeSettingIds);
		$reportGenerator->generateReport();
		
		return $this->sendExportExcelHeader($reportGenerator->fileInfo['filename'], $reportGenerator->savePath . DIRECTORY_SEPARATOR . $reportGenerator->fileInfo['filename'] . $reportGenerator->fileInfo['extension']);
	}

	public function executeExportSupplyOfMaterialItem(sfWebRequest $request)
	{
		$this->forward404Unless(
			$bill = ProjectStructureTable::getInstance()->find($request->getParameter('billId'))
		);

		session_write_close();

		$project     = ProjectStructureTable::getInstance()->find($bill->root_id);
		$tendererIds = json_decode($request->getParameter('selectedTenderers')) ?? [];
		$itemIds     = json_decode($request->getParameter('selectedRows')) ?? [];

		//Setup Necessary parameter
		$printingPageTitle = $request->getParameter('printingPageTitle');
		$descriptionFormat = $request->getParameter('descriptionFormat');
		$printNoCents      = ( $request->getParameter('printNoCents') == 't' ) ? true : false;

		$reportGenerator = new sfSupplyOfMaterialReportGenerator($project, $bill, $itemIds, $tendererIds);
		$reportGenerator->generateReport($printingPageTitle, null, $project->title, $printNoCents);

		return $this->sendExportExcelHeader($reportGenerator->fileInfo['filename'], $reportGenerator->savePath . DIRECTORY_SEPARATOR . $reportGenerator->fileInfo['filename'] . $reportGenerator->fileInfo['extension']);
	}
}