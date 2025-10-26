<?php

/**
 * supplyOfMaterialBill actions.
 *
 * @package    buildspace
 * @subpackage supplyOfMaterialBill
 * @author     1337 developers
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class supplyOfMaterialBillActions extends BaseActions
{

    public function executePrintBill(sfWebRequest $request)
    {
        sfConfig::set('sf_web_debug', false);

        $this->forward404Unless(
            $projectStructure = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('id'))
        );

        session_write_close();

        $elements = DoctrineQuery::create()
            ->select('e.id, e.description')
            ->from('SupplyOfMaterialElement e')
            ->where('e.project_structure_id = ?', $projectStructure->id)
            ->addOrderBy('e.priority ASC')
            ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
            ->execute();

        $bqPrintOutGenerator = new sfBuildSpaceSupplyOfMaterialBillPrintAll($request, $projectStructure, $elements);

        $pdfGen = new WkHtmlToPdf($this->getPrintBOQPageLayoutSettings($bqPrintOutGenerator));

        // set pdf generator
        $bqPrintOutGenerator->setPdfGenerator($pdfGen);

        try
        {
            $bqPrintOutGenerator->generateFullPrintoutPages();
        }
        catch(PageGeneratorException $e)
        {
            $data = $e->getData();
            $e = new PageGeneratorException($e->getMessage(), $data['data']);

            return $this->pageGeneratorExceptionView($e, $data['bqPageGenerator']);
        }

        return $bqPrintOutGenerator->pdfGenerator->send();
    }

    public function executePrintContractorsRate(sfWebRequest $request)
    {
        sfConfig::set('sf_web_debug', false);

//        $request->checkCSRFProtection();

        $this->forward404Unless(
            $project = ProjectStructureTable::getInstance()->find($request->getParameter('pid')) and
            $bill = ProjectStructureTable::getInstance()->find($request->getParameter('bid')) and
            $tenderCompany = TenderCompanyTable::getInstance()->find($request->getParameter('tcid'))
        );

        session_write_close();

        $elements = DoctrineQuery::create()
            ->select('e.id, e.description')
            ->from('SupplyOfMaterialElement e')
            ->where('e.project_structure_id = ?', $bill->id)
            ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
            ->addOrderBy('e.priority ASC')
            ->execute();

        $bqPrintOutGenerator = new sfBuildSpaceSupplyOfMaterialBillContractorPrintAll($request, $bill, $elements, $tenderCompany);

        $pdfGen = new WkHtmlToPdf($this->getPrintBOQPageLayoutSettings($bqPrintOutGenerator));

        // set pdf generator
        $bqPrintOutGenerator->setPdfGenerator($pdfGen);

        $bqPrintOutGenerator->generateFullBQPrintoutPages();

        return $bqPrintOutGenerator->pdfGenerator->send();
    }

    private function getPrintBOQPageLayoutSettings($bqPageGenerator)
    {
        $orientation = $bqPageGenerator->getOrientation();

        // for portrait printout
        $marginTop   = 8;
        $marginLeft  = 24;
        $marginRight = 4;

        // for landscape printout
        if ($orientation == sfBuildspaceBQMasterFunction::ORIENTATION_LANDSCAPE)
        {
            $marginLeft  = 12;
            $marginRight = 12;
        }

        return array(
            'disable-smart-shrinking',
            'disable-javascript',
            'no-outline',
            'no-background',
            'enableEscaping' => true,
            'binPath'        => sfConfig::get('app_wkhtmltopdf_bin_path'),
            'margin-top'     => $marginTop,
            'margin-right'   => $marginRight,
            'margin-bottom'  => 3,
            'margin-left'    => $marginLeft,
            'page-size'      => 'A4',
            'orientation'    => $orientation,
        );
    }

    protected function pageGeneratorExceptionView(PageGeneratorException $e, sfBuildspaceSupplyOfMaterialBillPageGenerator $bqPageGenerator)
    {
        $data = $e->getData();

        $this->errorMessage  = $e->getMessage();
        $this->stylesheet    = $this->getBQStyling();
        $this->layoutStyling = $bqPageGenerator->getLayoutStyling();
        $this->pageNumber    = $data['page_number'];
        $this->pageItems     = $data['page_items'];
        $this->billItem      = SupplyOfMaterialItemTable::getInstance()->find($data['id']);
        $this->occupiedRows  = $data['occupied_rows'];
        $this->maxRows       = $data['max_rows'];

        $pdo     = $this->billItem->getTable()->getConnection()->getDbh();
        $element = $this->billItem->Element;

        $stmt = $pdo->prepare("SELECT i.id, i.description, i.type, i.lft, i.level, i.supply_rate, i.contractor_supply_rate,
            i.estimated_qty, i.percentage_of_wastage, i.difference, i.amount, uom.id AS uom_id, uom.symbol AS uom_symbol, i.note
            FROM " . SupplyOfMaterialItemTable::getInstance()->getTableName() . " i
            LEFT JOIN " . UnitOfMeasurementTable::getInstance()->getTableName() . " uom ON i.uom_id = uom.id AND uom.deleted_at IS NULL
            WHERE i.element_id = " . $element->id . " AND i.deleted_at IS NULL
            ORDER BY i.priority, i.lft, i.level");


        $stmt->execute();

        $billItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $key = array_search($this->billItem->id, array_column($billItems, 'id'));

        $this->rowIdxInBillManager = $key+1;

        $this->setTemplate('pageGeneratorException');

        return sfView::SUCCESS;
    }
}