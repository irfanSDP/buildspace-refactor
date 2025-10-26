<?php

/**
 * budgetReportExport actions.
 *
 * @package    buildspace
 * @subpackage budgetReportExport
 * @author     1337 developers
 * @version    SVN: $Id$
 */
class budgetReportExportActions extends baseActions
{
	public function executeExportProjectReport(sfWebRequest $request)
    {
        $this->forward404Unless(
            $project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('pid'))
        );

        $reportGenerator = new sfBudgetReportProjectReportGenerator($project);

        $reportGenerator->generateReport();

        return $this->sendExportExcelHeader($reportGenerator->fileInfo['filename'], $reportGenerator->savePath . DIRECTORY_SEPARATOR . $reportGenerator->fileInfo['filename'] . $reportGenerator->fileInfo['extension']);
    }

    public function executeExportBillReport(sfWebRequest $request)
    {
        $this->forward404Unless(
            $project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('pid')) and
            $bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('bill_id'))
        );

        $reportGenerator = new sfBudgetReportBillReportGenerator($project);

        $reportGenerator->setParameter($bill);

        $reportGenerator->generateReport();

        return $this->sendExportExcelHeader($reportGenerator->fileInfo['filename'], $reportGenerator->savePath . DIRECTORY_SEPARATOR . $reportGenerator->fileInfo['filename'] . $reportGenerator->fileInfo['extension']);
    }

    public function executeExportElementReport(sfWebRequest $request)
    {
        $this->forward404Unless(
            $project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('pid')) and
            $bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('bill_id')) and
            $element = Doctrine_Core::getTable('BillElement')->find($request->getParameter('element_id'))
        );

        $reportGenerator = new sfBudgetReportElementReportGenerator($project);

        $reportGenerator->setParameter($element);

        $reportGenerator->generateReport();

        return $this->sendExportExcelHeader($reportGenerator->fileInfo['filename'], $reportGenerator->savePath . DIRECTORY_SEPARATOR . $reportGenerator->fileInfo['filename'] . $reportGenerator->fileInfo['extension']);
    }

    public function executeExportVariationOrderReport(sfWebRequest $request)
    {
        $this->forward404Unless(
            $project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('pid'))
        );

        $reportGenerator = new sfBudgetReportVariationOrderReportGenerator($project);

        $reportGenerator->generateReport();

        return $this->sendExportExcelHeader($reportGenerator->fileInfo['filename'], $reportGenerator->savePath . DIRECTORY_SEPARATOR . $reportGenerator->fileInfo['filename'] . $reportGenerator->fileInfo['extension']);
    }

    public function executeExportVariationOrderItemReport(sfWebRequest $request)
    {
        $this->forward404Unless(
            $project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('pid')) and
            $variationOrder = Doctrine_Core::getTable('VariationOrder')->find($request->getParameter('vo_id'))
        );

        $reportGenerator = new sfBudgetReportVariationOrderItemReportGenerator($project);

        $reportGenerator->setParameter($variationOrder);

        $reportGenerator->generateReport();

        return $this->sendExportExcelHeader($reportGenerator->fileInfo['filename'], $reportGenerator->savePath . DIRECTORY_SEPARATOR . $reportGenerator->fileInfo['filename'] . $reportGenerator->fileInfo['extension']);
    }
}
