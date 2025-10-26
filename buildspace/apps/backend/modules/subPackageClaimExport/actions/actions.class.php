<?php

/**
 * subPackageClaimExport actions.
 *
 * @package    buildspace
 * @subpackage subPackageClaimExport
 * @author     1337 developers
 * @version    SVN: $Id$
 */
class subPackageClaimExportActions extends baseActions
{
    public function executeExport(sfWebRequest $request)
    {
       $this->forward404Unless(
            $project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('pid'))
        );

        $reportGenerator = new sfSubPackageClaimReportGenerator($project);

        $reportGenerator->generateReport();

        return $this->sendExportExcelHeader($reportGenerator->fileInfo['filename'], $reportGenerator->savePath . DIRECTORY_SEPARATOR . $reportGenerator->fileInfo['filename'] . $reportGenerator->fileInfo['extension']);
    }
}
