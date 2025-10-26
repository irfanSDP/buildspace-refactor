<?php

class printBQActions extends BaseActions
{
    public function executeIndex(sfWebRequest $request)
    {
        sfConfig::set('sf_web_debug', false);

        $request->checkCSRFProtection();

        $this->forward404Unless(
            $element = BillElementTable::getInstance()->find($request->getParameter('eid'))
        );

        $user    = $this->getUser()->getGuardUser();
        $company = $user->Profile->getEProjectUser()->Company->getBsCompany();
        $bill    = $element->ProjectStructure;
        $project = ProjectStructureTable::getInstance()->find($bill->root_id);

        $editorProjectInfo = $company->getEditorProjectInformationByProject($project);

        session_write_close();

        $bqPrintOutGenerator = new sfBuildSpaceBQEditorPrint($editorProjectInfo, $bill, [$element], empty($request->getParameter('withPrice')));

        $pdfGen = new WkHtmlToPdf($this->getPrintBQPageLayoutSettings($bqPrintOutGenerator));

        // set pdf generator
        $bqPrintOutGenerator->setPdfGenerator($pdfGen);

        $bqPrintOutGenerator->generateFullBQPrintoutPages(sfBuildSpaceBQEditorPrint::PRINT_TYPE_BQ_ITEMS_ONLY);

        return $bqPrintOutGenerator->pdfGenerator->send();
    }

    public function executeSummaryPage(sfWebRequest $request)
    {
        sfConfig::set('sf_web_debug', false);

        $request->checkCSRFProtection();

        $this->forward404Unless(
            $bill = ProjectStructureTable::getInstance()->find($request->getParameter('bid'))
        );

        $user    = $this->getUser()->getGuardUser();
        $company = $user->Profile->getEProjectUser()->Company->getBsCompany();

        $project = ProjectStructureTable::getInstance()->find($bill->root_id);

        $editorProjectInfo = $company->getEditorProjectInformationByProject($project);

        session_write_close();

        $bqPrintOutGenerator = new sfBuildSpaceBQEditorPrint($editorProjectInfo, $bill, [], empty($request->getParameter('withPrice')));

        $pdfGen = new WkHtmlToPdf($this->getPrintBQPageLayoutSettings($bqPrintOutGenerator));

        // set pdf generator
        $bqPrintOutGenerator->setPdfGenerator($pdfGen);

        $bqPrintOutGenerator->generateFullBQPrintoutPages(sfBuildSpaceBQEditorPrint::PRINT_TYPE_SUMMARY_PAGE_ONLY);

        return $bqPrintOutGenerator->pdfGenerator->send();
    }

    public function executePrintAll(sfWebRequest $request)
    {
        sfConfig::set('sf_web_debug', false);

        $request->checkCSRFProtection();

        $this->forward404Unless(
            $bill = ProjectStructureTable::getInstance()->find($request->getParameter('bid'))
        );

        $user    = $this->getUser()->getGuardUser();
        $company = $user->Profile->getEProjectUser()->Company->getBsCompany();

        $project = ProjectStructureTable::getInstance()->find($bill->root_id);

        $editorProjectInfo = $company->getEditorProjectInformationByProject($project);

        $elements = DoctrineQuery::create()
            ->select('e.*')
            ->from('BillElement e')
            ->where('e.project_structure_id = ?', $bill->id)
            ->addOrderBy('e.priority ASC')
            ->execute();

        session_write_close();

        $bqPrintOutGenerator = new sfBuildSpaceBQEditorPrint($editorProjectInfo, $bill, $elements, empty($request->getParameter('withPrice')));

        $pdfGen = new WkHtmlToPdf($this->getPrintBQPageLayoutSettings($bqPrintOutGenerator));

        // set pdf generator
        $bqPrintOutGenerator->setPdfGenerator($pdfGen);

        $bqPrintOutGenerator->generateFullBQPrintoutPages(sfBuildSpaceBQEditorPrint::PRINT_TYPE_BOTH);

        return $bqPrintOutGenerator->pdfGenerator->send();
    }

    public function executePrintBQAddendum(sfWebRequest $request)
    {
        sfConfig::set('sf_web_debug', false);

        $request->checkCSRFProtection();

        $this->forward404Unless(
            $bill = ProjectStructureTable::getInstance()->find($request->getParameter('bid'))
        );

        $user    = $this->getUser()->getGuardUser();
        $company = $user->Profile->getEProjectUser()->Company->getBsCompany();

        $project = ProjectStructureTable::getInstance()->find($bill->root_id);

        $editorProjectInfo = $company->getEditorProjectInformationByProject($project);

        $withPrice = ( $request->getParameter('withPrice') ) ? false : true;

        session_write_close();

        // get affected element and page no
        $elements = DoctrineQuery::create()
            ->select('e.id, e.description, bp.id, bp.page_no, i.bill_item_id')
            ->from('BillElement e')
            ->leftJoin('e.BillPages bp')
            ->leftJoin('bp.Items i')
            ->where('e.project_structure_id = ?', $bill->id)
            ->andWhere('bp.new_revision_id = ?', $editorProjectInfo->printing_revision_id)
            ->addOrderBy('e.priority, bp.page_no ASC')
            ->execute();

        $bqAddendumPrintOutGenerator = new sfBuildSpaceBQEditorAddendumPrintAll($bill, $editorProjectInfo, $elements);

        $pdfGen = new WkHtmlToPdf($this->getPrintBQPageLayoutSettings($bqAddendumPrintOutGenerator));

        // set pdf generator
        $bqAddendumPrintOutGenerator->setPdfGenerator($pdfGen);

        try
        {
            $bqAddendumPrintOutGenerator->generateFullBQPrintoutPages($withPrice);

            return $bqAddendumPrintOutGenerator->pdfGenerator->send();
        }
        catch (Exception $e)
        {
            $this->message     = $e->getMessage();
            $this->explanation = "<em>Are you trying to print addendum?</em> Nothing can be printed because there were no changes made in this bill. You can either select previous revision <i>(if you want to print the previous addendum revision or original bill)</i> or make an addendum on the current bill.";

            $this->setTemplate('nothingToPrint');

            return sfView::SUCCESS;
        }
    }

    public function executeGetPrintList(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('id'))
        );

        $form   = new BaseForm();
        $result = array();
        $pdo    = $bill->getTable()->getConnection()->getDbh();

        $summaryPageRow = array(
            'id'                   => 0,
            'item_count'           => '-',
            'description'          => 'Summary Page',
            'project_structure_id' => $bill->id,
            'print'                => true,
            '_csrf_token'          => $form->getCSRFToken()
        );

        $result[0] = $summaryPageRow;

        // get current original project revision's version
        $originalProjectRevision = DoctrineQuery::create()
            ->select('pr.id')
            ->from('ProjectRevision pr')
            ->where('pr.project_structure_id = ?', $bill->root_id)
            ->andWhere('pr.version = ?', ProjectRevision::ORIGINAL_BILL_VERSION)
            ->limit(1)
            ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
            ->fetchOne();

        $elements = DoctrineQuery::create()
            ->select('e.id, e.description, e.project_structure_id')
            ->from('BillElement e')
            ->where('e.project_structure_id = ?', $bill->id)
            ->addOrderBy('e.priority ASC')
            ->fetchArray();

        $stmt = $pdo->prepare("SELECT i.element_id, COUNT(i.id) AS count
        FROM " . BillItemTable::getInstance()->getTableName() . " i
        JOIN " . BillElementTable::getInstance()->getTableName() . " e ON i.element_id = e.id WHERE
        e.project_structure_id = " . $bill->id . " AND e.deleted_at IS NULL
        AND i.project_revision_id = " . $originalProjectRevision['id'] . " AND i.project_revision_deleted_at IS NULL
        AND i.deleted_at IS NULL GROUP BY i.element_id");

        $stmt->execute();

        $itemCount = $stmt->fetchAll(PDO::FETCH_COLUMN | PDO::FETCH_GROUP);

        foreach ( $elements as $key => $element )
        {
            $element['item_count']  = array_key_exists($element['id'], $itemCount) ? $itemCount[$element['id']] : 0;
            $element['print']       = true;
            $element['_csrf_token'] = $form->getCSRFToken();
            $result[$key + 1]       = $element;

            unset( $element );
        }

        array_push($result, array(
            'id'                   => Constants::GRID_LAST_ROW,
            'item_count'           => null,
            'description'          => null,
            'print'                => false,
            'project_structure_id' => null,
            '_csrf_token'          => null
        ));

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $result
        ));
    }

    public function executeProjectSummary(sfWebRequest $request)
    {
        sfConfig::set('sf_web_debug', false);

        $request->checkCSRFProtection();

        $this->forward404Unless(
            $project = ProjectStructureTable::getInstance()->find($request->getParameter('pid')) and
            $project->type == ProjectStructure::TYPE_ROOT
        );

        $user    = $this->getUser()->getGuardUser();
        $company = $user->Profile->getEProjectUser()->Company->getBsCompany();

        $editorProjectInfo = $company->getEditorProjectInformationByProject($project);

        $withPrice  = !empty($request->getParameter('withPrice'));

        $stylesheet = file_get_contents(sfConfig::get('sf_web_dir') . '/css/projectSummary.css');

        $summaryPageGenerator = new sfBuildspaceEditorProjectSummaryGenerator($editorProjectInfo, null, $withPrice);

        $page = $summaryPageGenerator->generatePage();

        $totalProjectAmount = 0;

        $params = array(
            'disable-smart-shrinking',
            'disable-javascript',
            'no-outline',
            'no-background',
            'enableEscaping' => true,
            'binPath'        => sfConfig::get('app_wkhtmltopdf_bin_path'),
            'margin-top'     => 8,
            'margin-right'   => 7,
            'margin-bottom'  => 3,
            'margin-left'    => 24,
            'page-size'      => 'A4',
            'orientation'    => "Portrait"
        );

        $projectSummaryFooter = $project->ProjectSummaryFooter;

        $pdfGen = new WkHtmlToPdf($params);

        if ( $page['summary_items'] instanceof SplFixedArray )
        {
            $pageNumberPrefix              = $project->ProjectSummaryGeneralSetting->page_number_prefix;
            $includePrintingDate           = $project->ProjectSummaryGeneralSetting->include_printing_date;
            $includeStateCountry           = $project->ProjectSummaryGeneralSetting->include_state_and_country;
            $carriedToNextPageText         = $project->ProjectSummaryGeneralSetting->carried_to_next_page_text;
            $continuedFromPreviousPageText = $project->ProjectSummaryGeneralSetting->continued_from_previous_page_text;
            $includeTax                    = $project->ProjectSummaryGeneralSetting->include_tax;
            $taxName                       = $project->ProjectSummaryGeneralSetting->tax_name ? $project->ProjectSummaryGeneralSetting->tax_name : '';
            $taxPercentage                 = $project->ProjectSummaryGeneralSetting->tax_percentage ? $project->ProjectSummaryGeneralSetting->tax_percentage : '';

            foreach ( $page['summary_items'] as $pageCount => $summaryItems )
            {
                $layout = $this->getPartial('printBQ/projectSummary/pageLayout', array(
                    'title'      => $project->ProjectSummaryGeneralSetting->summary_title,
                    'stylesheet' => $stylesheet
                ));

                $pageCount += 1;

                $isLastPage = $pageCount == count($page['summary_items']) ? true : false;
                $maxRow     = $summaryPageGenerator->MAX_ROWS;

                if ( !$isLastPage )
                {
                    $maxRow = $summaryPageGenerator->DEFAULT_MAX_ROWS;
                }

                $layout .= $this->getPartial('printBQ/projectSummary/itemPageLayout', array(
                    'pageNumber'                    => $pageNumberPrefix . "&nbsp;" . $pageCount,
                    'includePrintingDate'           => $includePrintingDate,
                    'includeStateCountry'           => $includeStateCountry,
                    'carriedToNextPageText'         => $carriedToNextPageText,
                    'continuedFromPreviousPageText' => $continuedFromPreviousPageText,
                    'summaryTitleRows'              => $page['header'],
                    'itemPage'                      => $summaryItems,
                    'withPrice'                     => $withPrice,
                    'currency'                      => $project->MainInformation->Currency->currency_code,
                    'totalProjectAmount'            => $totalProjectAmount,
                    'projectSummaryFooter'          => $projectSummaryFooter,
                    'isLastPage'                    => $isLastPage,
                    'MAX_ROWS'                      => $maxRow,
                    'additionalDescriptions'        => $withPrice ? $page['additional_desc_price'] : $page['additional_desc'],
                    'includeTax'                    => $includeTax,
                    'taxName'                       => $taxName,
                    'taxPercentage'                 => $taxPercentage,
                    'eProjectOriginId'              => $project->MainInformation->eproject_origin_id,
                ));

                unset( $summaryItems );

                $pdfGen->addPage($layout);
            }
        }
        
        // ... send to client as file download
        return $pdfGen->send();
    }

    public function executeTenderAlternativeProjectSummary(sfWebRequest $request)
    {
        sfConfig::set('sf_web_debug', false);

        $request->checkCSRFProtection();

        $this->forward404Unless(
            $tenderAlternative = TenderAlternativeTable::getInstance()->find($request->getParameter('id'))
        );

        $project = $tenderAlternative->ProjectStructure;
        $user    = $this->getUser()->getGuardUser();
        $company = $user->Profile->getEProjectUser()->Company->getBsCompany();

        $editorProjectInfo = $company->getEditorProjectInformationByProject($project);

        $withPrice  = !empty($request->getParameter('withPrice'));

        $stylesheet = file_get_contents(sfConfig::get('sf_web_dir') . '/css/projectSummary.css');

        $summaryPageGenerator = new sfBuildspaceEditorProjectSummaryGenerator($editorProjectInfo, $tenderAlternative, $withPrice);

        $page = $summaryPageGenerator->generatePage();

        $totalProjectAmount = 0;

        $params = array(
            'disable-smart-shrinking',
            'disable-javascript',
            'no-outline',
            'no-background',
            'enableEscaping' => true,
            'binPath'        => sfConfig::get('app_wkhtmltopdf_bin_path'),
            'margin-top'     => 8,
            'margin-right'   => 7,
            'margin-bottom'  => 3,
            'margin-left'    => 24,
            'page-size'      => 'A4',
            'orientation'    => "Portrait"
        );

        $projectSummaryFooter = $project->ProjectSummaryFooter;

        $pdfGen = new WkHtmlToPdf($params);

        if ( $page['summary_items'] instanceof SplFixedArray )
        {
            $pageNumberPrefix              = $project->ProjectSummaryGeneralSetting->page_number_prefix;
            $includePrintingDate           = $project->ProjectSummaryGeneralSetting->include_printing_date;
            $includeStateCountry           = $project->ProjectSummaryGeneralSetting->include_state_and_country;
            $carriedToNextPageText         = $project->ProjectSummaryGeneralSetting->carried_to_next_page_text;
            $continuedFromPreviousPageText = $project->ProjectSummaryGeneralSetting->continued_from_previous_page_text;
            $includeTax                    = $project->ProjectSummaryGeneralSetting->include_tax;
            $taxName                       = $project->ProjectSummaryGeneralSetting->tax_name ? $project->ProjectSummaryGeneralSetting->tax_name : '';
            $taxPercentage                 = $project->ProjectSummaryGeneralSetting->tax_percentage ? $project->ProjectSummaryGeneralSetting->tax_percentage : '';

            foreach ( $page['summary_items'] as $pageCount => $summaryItems )
            {
                $layout = $this->getPartial('printBQ/projectSummary/pageLayout', array(
                    'title'      => $project->ProjectSummaryGeneralSetting->summary_title,
                    'stylesheet' => $stylesheet
                ));

                $pageCount += 1;

                $isLastPage = $pageCount == count($page['summary_items']) ? true : false;
                $maxRow     = $summaryPageGenerator->MAX_ROWS;

                if ( !$isLastPage )
                {
                    $maxRow = $summaryPageGenerator->DEFAULT_MAX_ROWS;
                }

                $layout .= $this->getPartial('printBQ/projectSummary/itemPageLayout', array(
                    'pageNumber'                    => $pageNumberPrefix . "&nbsp;" . $pageCount,
                    'includePrintingDate'           => $includePrintingDate,
                    'includeStateCountry'           => $includeStateCountry,
                    'carriedToNextPageText'         => $carriedToNextPageText,
                    'continuedFromPreviousPageText' => $continuedFromPreviousPageText,
                    'summaryTitleRows'              => $page['header'],
                    'itemPage'                      => $summaryItems,
                    'withPrice'                     => $withPrice,
                    'currency'                      => $project->MainInformation->Currency->currency_code,
                    'totalProjectAmount'            => $totalProjectAmount,
                    'projectSummaryFooter'          => $projectSummaryFooter,
                    'isLastPage'                    => $isLastPage,
                    'MAX_ROWS'                      => $maxRow,
                    'additionalDescriptions'        => $withPrice ? $page['additional_desc_price'] : $page['additional_desc'],
                    'includeTax'                    => $includeTax,
                    'taxName'                       => $taxName,
                    'taxPercentage'                 => $taxPercentage,
                    'eProjectOriginId'              => $project->MainInformation->eproject_origin_id,
                ));

                unset( $summaryItems );

                $pdfGen->addPage($layout);
            }
        }

        // ... send to client as file download
        return $pdfGen->send();
    }

    public function executeGetFinalBQPrintList(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('id')) and
            $project->type == ProjectStructure::TYPE_ROOT
        );

        $tenderAlternativeProjectStructureIds = [];
        if($request->hasParameter('tid'))
        {
            $tenderAlternative = Doctrine_Core::getTable('TenderAlternative')->find($request->getParameter('tid'));

            if($tenderAlternative)
            {
                //we set default to -1 so it should return empty query if there is no assigned bill for this tender alternative;
                $tenderAlternativeProjectStructureIds = [-1];
                $tenderAlternativesBills = $tenderAlternative->getAssignedBills();

                if($tenderAlternativesBills)
                {
                    $tenderAlternativeProjectStructureIds = array_column($tenderAlternativesBills, 'id');
                }
            }
        }

        $form         = new BaseForm();
        $result       = [];
        $itemCountSql = "";
        $pdo          = $project->getTable()->getConnection()->getDbh();

        $queryBills = DoctrineQuery::create()
            ->select('p.id, p.title, p.type')
            ->from('ProjectStructure p')
            ->where('p.root_id = ?', $project->id)
            ->andWhere('p.type = ?', ProjectStructure::TYPE_BILL);
        
        if(!empty($tenderAlternativeProjectStructureIds))
        {
            $queryBills->andWhereIn('p.id', $tenderAlternativeProjectStructureIds);

            $itemCountSql = " AND b.id IN (".implode(',', $tenderAlternativeProjectStructureIds).") ";
        }

        $bills = $queryBills->addOrderBy('p.lft ASC')->fetchArray();

        $stmt = $pdo->prepare("SELECT b.id, COUNT(i.id) AS count
            FROM " . BillItemTable::getInstance()->getTableName() . " i
            JOIN " . BillElementTable::getInstance()->getTableName() . " e ON i.element_id = e.id
            JOIN " . ProjectStructureTable::getInstance()->getTableName() . " b ON e.project_structure_id = b.id
            JOIN " . ProjectStructureTable::getInstance()->getTableName() . " p ON b.root_id = p.id
            JOIN " . ProjectRevisionTable::getInstance()->getTableName() . " r ON i.project_revision_id = r.id AND r.project_structure_id = p.id
            WHERE p.id = " . $project->id . " ".$itemCountSql." AND r.locked_status IS TRUE
            AND r.deleted_at IS NULL AND e.deleted_at IS NULL AND i.project_revision_deleted_at IS NULL AND i.deleted_at IS NULL
            GROUP BY b.id");

        $stmt->execute();

        $itemCount = $stmt->fetchAll(PDO::FETCH_COLUMN | PDO::FETCH_GROUP);

        foreach ( $bills as $key => $bill )
        {
            $bill['item_count']           = array_key_exists($bill['id'], $itemCount) ? $itemCount[$bill['id']] : 0;
            $bill['print']                = true;
            $bill['project_structure_id'] = $project->id;

            $result[$key] = $bill;

            unset( $bill );
        }

        array_push($result, [
            'id'                   => Constants::GRID_LAST_ROW,
            'item_count'           => null,
            'title'                => null,
            'print'                => false,
            'project_structure_id' => null,
            'type'                 => null,
        ]);

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $result
        ));
    }

    public function executePrintFinalBQ(sfWebRequest $request)
    {
        sfConfig::set('sf_web_debug', false);

        $request->checkCSRFProtection();

        $this->forward404Unless(
            $bill = ProjectStructureTable::getInstance()->find($request->getParameter('id'))
        );

        $user      = $this->getUser()->getGuardUser();
        $company   = $user->Profile->getEProjectUser()->Company->getBsCompany();
        $project   = $bill->getRoot();
        $withPrice = (int)$request->getParameter('withPrice');

        $editorProjectInfo = $company->getEditorProjectInformationByProject($project);

        session_write_close();

        $bqPrintOutGenerator = new sfBuildSpaceBQEditorPrintFinalBQ($editorProjectInfo, $bill, $withPrice);

        $pdfGen = new WkHtmlToPdf($this->getPrintBQPageLayoutSettings($bqPrintOutGenerator));

        // set pdf generator
        $bqPrintOutGenerator->setPdfGenerator($pdfGen);

        $bqPrintOutGenerator->generateFullBQPrintoutPages();
        
        return $bqPrintOutGenerator->pdfGenerator->send();
    }

    private function getPrintBQPageLayoutSettings($bqPageGenerator)
    {
        $orientation = $bqPageGenerator->getOrientation();

        // for portrait printout
        $marginTop   = 8;
        $marginLeft  = 24;
        $marginRight = 4;

        // for landscape printout
        if ( $orientation == sfBuildspaceBQMasterFunction::ORIENTATION_LANDSCAPE )
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
}
