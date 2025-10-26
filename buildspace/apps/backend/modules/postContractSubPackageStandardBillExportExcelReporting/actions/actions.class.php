<?php

/**
 * postContractSubPackageStandardBillExportExcelReporting actions.
 *
 * @package    buildspace
 * @subpackage postContractSubPackageStandardBillExportExcelReporting
 * @author     1337 developers
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class postContractSubPackageStandardBillExportExcelReportingActions extends BaseActions {

	public function executeExportExcelElementByTypes(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless(
			$bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('billId')) and
			$project = Doctrine_Core::getTable('ProjectStructure')->find($bill->root_id) and
			$subPackage = Doctrine_Core::getTable('SubPackage')->find($request->getParameter('subPackageId'))
		);

		session_write_close();

		$printingPageTitle = $request->getParameter('printingPageTitle');
		$descriptionFormat = $request->getParameter('descriptionFormat');
		$printNoCents      = ( $request->getParameter('printNoCents') == 't' ) ? true : false;

		$reportPrintGenerator = new sfBuildspacePostContractSubPackageReportPageElementByTypeGenerator($subPackage, $bill, $printingPageTitle, $descriptionFormat);
		$pages                = $reportPrintGenerator->generatePages();
		$totalPage            = count($pages) - 1;

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

	public function executeExportExcelElementWithClaimByTypes(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless(
			$bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('billId')) and
			$project = Doctrine_Core::getTable('ProjectStructure')->find($bill->root_id) and
			$subPackage = Doctrine_Core::getTable('SubPackage')->find($request->getParameter('subPackageId'))
		);

		session_write_close();

		$printingPageTitle = $request->getParameter('printingPageTitle');
		$descriptionFormat = $request->getParameter('descriptionFormat');
		$printNoCents      = ( $request->getParameter('printNoCents') == 't' ) ? true : false;

		$reportPrintGenerator = new sfBuildspacePostContractSubPackageReportPageElementByTypeWithClaimGenerator($subPackage, $bill, $printingPageTitle, $descriptionFormat);
		$pages                = $reportPrintGenerator->generatePages();
		$totalPage            = count($pages) - 1;

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

	public function executeExportExcelElementWithClaimByTypesBySelectedUnits(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless(
			$bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('billId')) and
			$project = Doctrine_Core::getTable('ProjectStructure')->find($bill->root_id) and
			$subPackage = Doctrine_Core::getTable('SubPackage')->find($request->getParameter('subPackageId'))
		);

		session_write_close();

		$printingPageTitle = $request->getParameter('printingPageTitle');
		$descriptionFormat = $request->getParameter('descriptionFormat');
		$typeIds           = json_decode($request->getParameter('selectedRows'), true);

		$printNoCents         = ( $request->getParameter('printNoCents') == 't' ) ? true : false;
		$reportPrintGenerator = new sfBuildspacePostContractSubPackageReportPageElementByTypeSelectedUnits($subPackage, $bill, $printingPageTitle, $descriptionFormat);
		$pages                = $reportPrintGenerator->generatePages($typeIds);
		$totalPage            = count($pages) - 1;

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

	public function executeExportExcelStandardBillSelectedElement(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless(
			$bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('billId')) and
			$project = Doctrine_Core::getTable('ProjectStructure')->find($bill->root_id) and
			$typeRef = Doctrine_Core::getTable('PostContractStandardClaimTypeReference')->find($request->getParameter('type_ref_id')) and
			$subPackage = Doctrine_Core::getTable('SubPackage')->find($request->getParameter('subPackageId'))
		);

		session_write_close();

		$printingPageTitle = $request->getParameter('printingPageTitle');
		$descriptionFormat = $request->getParameter('descriptionFormat');
		$elementIds        = json_decode($request->getParameter('selectedRows'), true);
		$printNoCents      = ( $request->getParameter('printNoCents') == 't' ) ? true : false;
		$printWithoutUnit  = false;

		$reportPrintGenerator = new sfBuildspacePostContractSubPackageStandardBillReportPageElementGenerator($subPackage, $bill, $elementIds, $printingPageTitle, $descriptionFormat);
		$pages                = $reportPrintGenerator->generatePages($typeRef);
		$totalPage            = count($pages) - 1;
		$typeRefTitle         = ( strlen($typeRef->new_name) ) ? $typeRef->new_name : 'Unit ' . $typeRef->counter;

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

		if ( !$printWithoutUnit )
		{
			$topLeftRow2 = $topLeftRow2 . ' > ' . $typeRef->BillColumnSetting->name . ' > ' . $typeRefTitle;
		}

		$sfElementExport->setTopRightTitle(' (' . sfBuildspacePostContractReportPageItemGenerator::CLAIM_PREFIX . '' . $reportPrintGenerator->revision['version'] . ')');

		$sfElementExport->process($pages, false, $printingPageTitle, $topLeftRow2, $project->title, $printNoCents, $totalPage);

		return $this->sendExportExcelHeader($sfElementExport->fileInfo['filename'], $sfElementExport->savePath . DIRECTORY_SEPARATOR . $sfElementExport->fileInfo['filename'] . $sfElementExport->fileInfo['extension']);
	}

	public function executeExportExcelStandardBillElementWorkDoneOnly(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless(
			$bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('billId')) and
			$project = Doctrine_Core::getTable('ProjectStructure')->find($bill->root_id) and
			$typeRef = Doctrine_Core::getTable('PostContractStandardClaimTypeReference')->find($request->getParameter('type_ref_id')) and
			$subPackage = Doctrine_Core::getTable('SubPackage')->find($request->getParameter('subPackageId'))
		);

		session_write_close();

		$pdo               = $bill->getTable()->getConnection()->getDbh();
		$printingPageTitle = $request->getParameter('printingPageTitle');
		$descriptionFormat = $request->getParameter('descriptionFormat');
		$printNoCents      = ( $request->getParameter('printNoCents') == 't' ) ? true : false;
		$printWithoutUnit  = false;
		$elementIds        = array();
		$elements          = $this->getAffectedElements($pdo, $subPackage, $bill);

		foreach ( $elements as $element )
		{
			$elementIds[] = $element['id'];
		}

		$reportPrintGenerator = new sfBuildspacePostContractSubPackageStandardBillReportPageElementGenerator($subPackage, $bill, $elementIds, $printingPageTitle, $descriptionFormat);
		$pages                = $reportPrintGenerator->generatePages($typeRef);
		$totalPage            = count($pages) - 1;
		$typeRefTitle         = ( strlen($typeRef->new_name) ) ? $typeRef->new_name : 'Unit ' . $typeRef->counter;

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

		if ( !$printWithoutUnit )
		{
			$topLeftRow2 = $topLeftRow2 . ' > ' . $typeRef->BillColumnSetting->name . ' > ' . $typeRefTitle;
		}

		$sfElementExport->setTopRightTitle(' (' . sfBuildspacePostContractReportPageItemGenerator::CLAIM_PREFIX . '' . $reportPrintGenerator->revision['version'] . ')');

		$sfElementExport->process($pages, false, $printingPageTitle, $topLeftRow2, $project->title, $printNoCents, $totalPage);

		return $this->sendExportExcelHeader($sfElementExport->fileInfo['filename'], $sfElementExport->savePath . DIRECTORY_SEPARATOR . $sfElementExport->fileInfo['filename'] . $sfElementExport->fileInfo['extension']);
	}

	public function executeExportExcelStandardBillSelectedItems(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless(
			$bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('billId')) and
			$project = Doctrine_Core::getTable('ProjectStructure')->find($bill->root_id) and
			$typeRef = Doctrine_Core::getTable('PostContractStandardClaimTypeReference')->find($request->getParameter('type_ref_id')) and
			$subPackage = Doctrine_Core::getTable('SubPackage')->find($request->getParameter('subPackageId'))
		);

		session_write_close();

		$printingPageTitle = $request->getParameter('printingPageTitle');
		$descriptionFormat = $request->getParameter('descriptionFormat');
		$itemIds           = json_decode($request->getParameter('selectedRows'), true);
		$printWithoutUnit  = false;
		$printNoCents      = ( $request->getParameter('printNoCents') == 't' ) ? true : false;
		$affectedElements  = SubPackageTable::getAffectedElementIdsByItemIds($request->getParameter('selectedRows'), $subPackage, $bill);

		$reportPrintGenerator = new sfBuildspacePostContractSubPackageReportPageItemGenerator($subPackage, $bill, $affectedElements, $itemIds, $printingPageTitle, $descriptionFormat);
		$elementPages         = $reportPrintGenerator->generatePages($typeRef);
		$typeRefTitle         = ( strlen($typeRef->new_name) ) ? $typeRef->new_name : 'Unit ' . $typeRef->counter;
		$topLeftTitle         = ( strlen($reportPrintGenerator->printSettings['layoutSetting']['pageNoPrefix']) ) ? 'Prefix: ' . $reportPrintGenerator->printSettings['layoutSetting']['pageNoPrefix'] : '';

		$sfItemExport = new sfPostContractItemReportGenerator(
			$project,
			null,
			$printingPageTitle,
			$reportPrintGenerator->printSettings
		);

		$topLeftRow2 = $bill->title;

		if ( !$printWithoutUnit )
		{
			$topLeftRow2 = $topLeftRow2 . ' > ' . $typeRef->BillColumnSetting->name . ' > ' . $typeRefTitle;
		}

		$sfItemExport->setParameter($reportPrintGenerator->elementTotals);

		$sfItemExport->setTopRightTitle(' (' . sfBuildspacePostContractReportPageItemGenerator::CLAIM_PREFIX . '' . $reportPrintGenerator->revision['version'] . ')');

		$sfItemExport->process($elementPages, false, $printingPageTitle, $topLeftTitle, $topLeftRow2, $printNoCents, $reportPrintGenerator->totalPage);

		return $this->sendExportExcelHeader($sfItemExport->fileInfo['filename'], $sfItemExport->savePath . DIRECTORY_SEPARATOR . $sfItemExport->fileInfo['filename'] . $sfItemExport->fileInfo['extension']);
	}

	public function executeExportExcelStandardBillItemWithCurrentClaim(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless(
			$bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('billId')) and
			$project = Doctrine_Core::getTable('ProjectStructure')->find($bill->root_id) and
			$typeRef = Doctrine_Core::getTable('PostContractStandardClaimTypeReference')->find($request->getParameter('type_ref_id')) and
			$subPackage = Doctrine_Core::getTable('SubPackage')->find($request->getParameter('subPackageId'))
		);

		session_write_close();

		$pdo               = $bill->getTable()->getConnection()->getDbh();
		$printingPageTitle = $request->getParameter('printingPageTitle');
		$descriptionFormat = $request->getParameter('descriptionFormat');
		$printWithoutUnit  = false;
		$printNoCents      = ( $request->getParameter('printNoCents') == 't' ) ? true : false;
		$affectedElements  = $this->getAffectedElements($pdo, $subPackage, $bill);

		$reportPrintGenerator = new sfBuildspacePostContractSubPackageReportPageItemWithCurrentClaimGenerator($subPackage, $bill, $affectedElements, null, $printingPageTitle, $descriptionFormat);
		$pages                = $reportPrintGenerator->generatePages($typeRef);
		$typeRefTitle         = ( strlen($typeRef->new_name) ) ? $typeRef->new_name : 'Unit ' . $typeRef->counter;
		$topLeftTitle         = ( strlen($reportPrintGenerator->printSettings['layoutSetting']['pageNoPrefix']) ) ? 'Prefix: ' . $reportPrintGenerator->printSettings['layoutSetting']['pageNoPrefix'] : '';

		$sfItemExport = new sfPostContractItemReportGenerator(
			$project,
			null,
			$printingPageTitle,
			$reportPrintGenerator->printSettings
		);

		$topLeftRow2 = $bill->title;

		if ( !$printWithoutUnit )
		{
			$topLeftRow2 = $topLeftRow2 . ' > ' . $typeRef->BillColumnSetting->name . ' > ' . $typeRefTitle;
		}

		$sfItemExport->setParameter($reportPrintGenerator->elementTotals);

		$sfItemExport->setTopRightTitle(' (' . sfBuildspacePostContractReportPageItemGenerator::CLAIM_PREFIX . '' . $reportPrintGenerator->revision['version'] . ')');

		$sfItemExport->process($pages, false, $printingPageTitle, $topLeftTitle, $topLeftRow2, $printNoCents, $reportPrintGenerator->totalPage);

		return $this->sendExportExcelHeader($sfItemExport->fileInfo['filename'], $sfItemExport->savePath . DIRECTORY_SEPARATOR . $sfItemExport->fileInfo['filename'] . $sfItemExport->fileInfo['extension']);
	}

	public function executeExportExcelStandardBillItemWithClaim(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless(
			$bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('billId')) and
			$project = Doctrine_Core::getTable('ProjectStructure')->find($bill->root_id) and
			$typeRef = Doctrine_Core::getTable('PostContractStandardClaimTypeReference')->find($request->getParameter('type_ref_id')) and
			$subPackage = Doctrine_Core::getTable('SubPackage')->find($request->getParameter('subPackageId'))
		);

		session_write_close();

		$pdo               = $bill->getTable()->getConnection()->getDbh();
		$printingPageTitle = $request->getParameter('printingPageTitle');
		$descriptionFormat = $request->getParameter('descriptionFormat');
		$printWithoutUnit  = false;
		$printNoCents      = ( $request->getParameter('printNoCents') == 't' ) ? true : false;
		$affectedElements  = $this->getAffectedElements($pdo, $subPackage, $bill);

		$reportPrintGenerator = new sfBuildspacePostContractSubPackageReportPageItemWithClaimGenerator($subPackage, $bill, $affectedElements, null, $printingPageTitle, $descriptionFormat);
		$pages                = $reportPrintGenerator->generatePages($typeRef);
		$typeRefTitle         = ( strlen($typeRef->new_name) ) ? $typeRef->new_name : 'Unit ' . $typeRef->counter;
		$topLeftTitle         = ( strlen($reportPrintGenerator->printSettings['layoutSetting']['pageNoPrefix']) ) ? 'Prefix: ' . $reportPrintGenerator->printSettings['layoutSetting']['pageNoPrefix'] : '';

		$sfItemExport = new sfPostContractItemReportGenerator(
			$project,
			null,
			$printingPageTitle,
			$reportPrintGenerator->printSettings
		);

		$topLeftRow2 = $bill->title;

		if ( !$printWithoutUnit )
		{
			$topLeftRow2 = $topLeftRow2 . ' > ' . $typeRef->BillColumnSetting->name . ' > ' . $typeRefTitle;
		}

		$sfItemExport->setParameter($reportPrintGenerator->elementTotals);

		$sfItemExport->setTopRightTitle(' (' . sfBuildspacePostContractReportPageItemGenerator::CLAIM_PREFIX . '' . $reportPrintGenerator->revision['version'] . ')');

		$sfItemExport->process($pages, false, $printingPageTitle, $topLeftTitle, $topLeftRow2, $printNoCents, $reportPrintGenerator->totalPage);

		return $this->sendExportExcelHeader($sfItemExport->fileInfo['filename'], $sfItemExport->savePath . DIRECTORY_SEPARATOR . $sfItemExport->fileInfo['filename'] . $sfItemExport->fileInfo['extension']);
	}

	public function executeExportExcelStandardBillItemWorkDoneOnlyWithQty(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless(
			$bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('billId')) and
			$project = Doctrine_Core::getTable('ProjectStructure')->find($bill->root_id) and
			$typeRef = Doctrine_Core::getTable('PostContractStandardClaimTypeReference')->find($request->getParameter('type_ref_id')) and
			$subPackage = Doctrine_Core::getTable('SubPackage')->find($request->getParameter('subPackageId'))
		);

		session_write_close();

		$pdo               = $bill->getTable()->getConnection()->getDbh();
		$printingPageTitle = $request->getParameter('printingPageTitle');
		$descriptionFormat = $request->getParameter('descriptionFormat');
		$printWithoutUnit  = false;
		$printNoCents      = ( $request->getParameter('printNoCents') == 't' ) ? true : false;
		$affectedElements  = $this->getAffectedElements($pdo, $subPackage, $bill);

		$reportPrintGenerator = new sfBuildspacePostContractSubPackageReportPageItemWithClaimGenerator($subPackage, $bill, $affectedElements, null, $printingPageTitle, $descriptionFormat);
		$pages                = $reportPrintGenerator->generatePages($typeRef);
		$topLeftTitle         = ( strlen($reportPrintGenerator->printSettings['layoutSetting']['pageNoPrefix']) ) ? 'Prefix: ' . $reportPrintGenerator->printSettings['layoutSetting']['pageNoPrefix'] : '';
		$typeRefTitle         = ( strlen($typeRef->new_name) ) ? $typeRef->new_name : 'Unit ' . $typeRef->counter;

		$sfItemExport = new sfPostContractItemReportGeneratorWorkdoneOnlyQty(
			$project,
			null,
			$printingPageTitle,
			$reportPrintGenerator->printSettings
		);

		$topLeftRow2 = $bill->title;

		if ( !$printWithoutUnit )
		{
			$topLeftRow2 = $topLeftRow2 . ' > ' . $typeRef->BillColumnSetting->name . ' > ' . $typeRefTitle;
		}

		$sfItemExport->setParameter($reportPrintGenerator->elementTotals);

		$sfItemExport->setTopRightTitle(' (' . sfBuildspacePostContractReportPageItemGenerator::CLAIM_PREFIX . '' . $reportPrintGenerator->revision['version'] . ')');

		$sfItemExport->process($pages, false, $printingPageTitle, $topLeftTitle, $topLeftRow2, $printNoCents, $reportPrintGenerator->totalPage);

		return $this->sendExportExcelHeader($sfItemExport->fileInfo['filename'], $sfItemExport->savePath . DIRECTORY_SEPARATOR . $sfItemExport->fileInfo['filename'] . $sfItemExport->fileInfo['extension']);
	}

	public function executeExportExcelStandardBillItemWorkDoneOnlyWithPercentage(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless(
			$bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('billId')) and
			$project = Doctrine_Core::getTable('ProjectStructure')->find($bill->root_id) and
			$typeRef = Doctrine_Core::getTable('PostContractStandardClaimTypeReference')->find($request->getParameter('type_ref_id')) and
			$subPackage = Doctrine_Core::getTable('SubPackage')->find($request->getParameter('subPackageId'))
		);

		session_write_close();

		$pdo               = $bill->getTable()->getConnection()->getDbh();
		$printingPageTitle = $request->getParameter('printingPageTitle');
		$descriptionFormat = $request->getParameter('descriptionFormat');
		$printWithoutUnit  = false;
		$printNoCents      = ( $request->getParameter('printNoCents') == 't' ) ? true : false;
		$affectedElements  = $this->getAffectedElements($pdo, $subPackage, $bill);

		$reportPrintGenerator = new sfBuildspacePostContractSubPackageReportPageItemWithClaimGenerator($subPackage, $bill, $affectedElements, null, $printingPageTitle, $descriptionFormat);
		$pages                = $reportPrintGenerator->generatePages($typeRef);
		$typeRefTitle         = ( strlen($typeRef->new_name) ) ? $typeRef->new_name : 'Unit ' . $typeRef->counter;
		$topLeftTitle         = ( strlen($reportPrintGenerator->printSettings['layoutSetting']['pageNoPrefix']) ) ? 'Prefix: ' . $reportPrintGenerator->printSettings['layoutSetting']['pageNoPrefix'] : '';

		$sfItemExport = new sfPostContractItemReportGeneratorWorkdoneOnlyPercentage(
			$project,
			null,
			$printingPageTitle,
			$reportPrintGenerator->printSettings
		);

		$topLeftRow2 = $bill->title;

		if ( !$printWithoutUnit )
		{
			$topLeftRow2 = $topLeftRow2 . ' > ' . $typeRef->BillColumnSetting->name . ' > ' . $typeRefTitle;
		}

		$sfItemExport->setParameter($reportPrintGenerator->elementTotals);

		$sfItemExport->setTopRightTitle(' (' . sfBuildspacePostContractReportPageItemGenerator::CLAIM_PREFIX . '' . $reportPrintGenerator->revision['version'] . ')');

		$sfItemExport->process($pages, false, $printingPageTitle, $topLeftTitle, $topLeftRow2, $printNoCents, $reportPrintGenerator->totalPage);

		return $this->sendExportExcelHeader($sfItemExport->fileInfo['filename'], $sfItemExport->savePath . DIRECTORY_SEPARATOR . $sfItemExport->fileInfo['filename'] . $sfItemExport->fileInfo['extension']);
	}

	private function getAffectedElements(PDO $pdo, SubPackage $subPackage, ProjectStructure $bill)
	{
		$stmt = $pdo->prepare("SELECT e.id, e.description, e.note
		FROM " . SubPackagePostContractBillItemRateTable::getInstance()->getTableName() . " rate
		JOIN " . BillItemTable::getInstance()->getTableName() . " i ON rate.bill_item_id  = i.id AND i.deleted_at IS NULL
		JOIN " . BillElementTable::getInstance()->getTableName() . " e ON i.element_id =  e.id AND e.deleted_at IS NULL
		JOIN " . ProjectStructureTable::getInstance()->getTableName() . " b ON b.id =  e.project_structure_id AND b.deleted_at IS NULL
		WHERE b.id = " . $bill->id . " AND rate.sub_package_id = " . $subPackage->id . " GROUP BY e.id ORDER BY e.priority ASC");

		$stmt->execute();

		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

}