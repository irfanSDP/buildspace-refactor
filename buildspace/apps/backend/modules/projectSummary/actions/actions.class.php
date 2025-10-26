<?php

/**
 * projectSummary actions.
 *
 * @package    buildspace
 * @subpackage projectSummary
 * @author     1337 developers
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class projectSummaryActions extends BaseActions {

    public function executeGetBills(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $project = ProjectStructureTable::getInstance()->find($request->getParameter('pid'))
        );

        $records = DoctrineQuery::create()->select('s.id, s.title, s.type, s.level, style.reference_char, style.is_bold, style.is_italic, style.is_underline')
            ->from('ProjectStructure s')
            ->leftJoin('s.ProjectSummaryStyle style')
            ->where('s.lft >= ? AND s.rgt <= ?', array( $project->lft, $project->rgt ))
            ->andWhere('s.root_id = ?', $project->root_id)
            ->andWhere('(s.type = ? OR s.type = ?)', array( ProjectStructure::TYPE_BILL, ProjectStructure::TYPE_LEVEL ))
            ->addOrderBy('s.lft ASC')
            ->fetchArray();

        $form = new BaseForm();
        $overallTotalAfterMarkupRecords = ProjectStructureTable::getOverallTotalAfterMarkupByProject($project);

        foreach ( $records as $key => $record )
        {
            $records[$key]['page']        = null;
            $records[$key]['amount']      = ($record['type'] == ProjectStructure::TYPE_BILL && array_key_exists($record['id'], $overallTotalAfterMarkupRecords)) ? $overallTotalAfterMarkupRecords[$record['id']] : 0;
            $records[$key]['_csrf_token'] = $form->getCSRFToken();

            $records[$key]['reference_char'] = null;
            $records[$key]['is_bold']        = false;
            $records[$key]['is_italic']      = false;
            $records[$key]['is_underline']   = false;

            if ( !empty( $records[$key]['ProjectSummaryStyle'] ) )
            {
                $records[$key]['reference_char'] = $records[$key]['ProjectSummaryStyle']['reference_char'];
                $records[$key]['is_bold']        = $records[$key]['ProjectSummaryStyle']['is_bold'];
                $records[$key]['is_italic']      = $records[$key]['ProjectSummaryStyle']['is_italic'];
                $records[$key]['is_underline']   = $records[$key]['ProjectSummaryStyle']['is_underline'];
            }

            if ( $record['type'] == ProjectStructure::TYPE_BILL )
            {
                $bill = ProjectStructureTable::getInstance()->find($record['id']);

                $bqPageGenerator = new sfBuildspaceBQPageGenerator($bill);
                $pages           = $bqPageGenerator->generatePages();

                
                $pageNo = $bqPageGenerator->getSummaryPageNumberingPrefix(count($pages['summary_pages']));//get last page

                $records[$key]['page'] = $pageNo;

                unset( $bill, $bqPageGenerator );
            }

            unset( $records[$key]['ProjectSummaryStyle'] );
        }

        array_push($records, array(
            'id'             => Constants::GRID_LAST_ROW,
            'title'          => "",
            'type'           => - 1,
            'level'          => 0,
            'amount'         => 0,
            'reference_char' => null,
            'is_bold'        => false,
            'is_italic'      => false,
            'is_underline'   => false,
            'page'           => null,
            '_csrf_token'    => $form->getCSRFToken()
        ));

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $records
        ));
    }

    public function executeProjectSummaryData(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $project = ProjectStructureTable::getInstance()->find($request->getParameter('id')) and
            $project->type == ProjectStructure::TYPE_ROOT
        );

        $project->MainInformation->save();//we call save fo existing project that does not have project summary when it was created

        $bottomForm         = new ProjectSummaryBottomFooterForm($project->ProjectSummaryFooter);
        $tableForm          = new ProjectSummaryTableFooterForm($project->ProjectSummaryFooter);
        $generalSettingForm = new ProjectSummaryGeneralSettingForm($project->ProjectSummaryGeneralSetting);

        $projectSumTotal = ProjectStructureTable::getOverallTotalForProject($project->id);

        return $this->renderJson(array(
            'footer_form'          => array(
                'right_text'  => $bottomForm->getObject()->right_text,
                'left_text'   => $bottomForm->getObject()->left_text,
                '_csrf_token' => $bottomForm->getCSRFToken() ),
            'table_form'           => array(
                'project_summary_footer[first_row_text]'       => $tableForm->getObject()->first_row_text,
                'project_summary_footer[second_row_text]'      => $tableForm->getObject()->second_row_text,
                'project_summary_footer[project_structure_id]' => $project->id,
                'project_summary_footer[_csrf_token]'          => $tableForm->getCSRFToken() ),
            'general_setting_form' => array(
                'project_summary_general_setting[project_title]'                     => $generalSettingForm->getObject()->project_title,
                'project_summary_general_setting[additional_description]'            => $generalSettingForm->getObject()->additional_description,
                'project_summary_general_setting[summary_title]'                     => $generalSettingForm->getObject()->summary_title,
                'project_summary_general_setting[include_additional_description]'    => $generalSettingForm->getObject()->include_additional_description,
                'project_summary_general_setting[include_printing_date]'             => $generalSettingForm->getObject()->include_printing_date,
                'project_summary_general_setting[include_state_and_country]'         => $generalSettingForm->getObject()->include_state_and_country,
                'project_summary_general_setting[carried_to_next_page_text]'         => $generalSettingForm->getObject()->carried_to_next_page_text,
                'project_summary_general_setting[continued_from_previous_page_text]' => $generalSettingForm->getObject()->continued_from_previous_page_text,
                'project_summary_general_setting[page_number_prefix]'                => $generalSettingForm->getObject()->page_number_prefix,
                'project_summary_general_setting[include_tax]'                       => $generalSettingForm->getObject()->include_tax,
                'project_summary_general_setting[tax_name]'                          => $generalSettingForm->getObject()->tax_name,
                'project_summary_general_setting[tax_percentage]'                    => number_format($generalSettingForm->getObject()->tax_percentage, 2),
                'project_summary_general_setting[project_structure_id]'              => $project->id,
                'project_summary_general_setting[_csrf_token]'                       => $generalSettingForm->getCSRFToken()
            ),
            'currency_code'        => $project->MainInformation->Currency->currency_code,
            'total_cost'           => $projectSumTotal
        ));
    }

    public function executeFooterUpdate(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('id')) and
            $project->type == ProjectStructure::TYPE_ROOT
        );

        $form = new ProjectSummaryBottomFooterForm($project->ProjectSummaryFooter);

        if ( $this->isFormValid($request, $form) )
        {
            $form->save();

            $errors  = null;
            $success = true;
        }
        else
        {
            $errors  = $form->getErrors();
            $success = false;
        }

        return $this->renderJson(array( 'success' => $success, 'errors' => $errors ));
    }

    public function executeTableFooterUpdate(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('pid')) and
            $project->type == ProjectStructure::TYPE_ROOT
        );

        $form = new ProjectSummaryTableFooterForm($project->ProjectSummaryFooter);

        if ( $this->isFormValid($request, $form) )
        {
            $form->save();

            $errors  = null;
            $success = true;
        }
        else
        {
            $errors  = $form->getErrors();
            $success = false;
        }

        return $this->renderJson(array( 'success' => $success, 'errors' => $errors ));
    }

    public function executeBillStyleReferenceCharUpdate(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('id')) and
            $bill->type != ProjectStructure::TYPE_ROOT
        );

        $billStyle = $bill->ProjectSummaryStyle;

        if ( $billStyle->isNew() )
        {
            $billStyle->project_structure_id = $bill->id;
        }

        $con = $bill->getTable()->getConnection();

        try
        {
            $con->beginTransaction();

            $billStyle->reference_char = trim($request->getParameter('val'));
            $billStyle->save($con);

            $con->commit();

            $success  = true;
            $errorMsg = null;

            $referenceChar = $billStyle->reference_char;
        } catch (Exception $e)
        {
            $con->rollback();
            $errorMsg      = $e->getMessage();
            $success       = false;
            $referenceChar = null;
        }

        return $this->renderJson(array( 'success' => $success, 'errorMsg' => $errorMsg, 'data' => array( 'reference_char' => $referenceChar ) ));
    }

    public function executeBillFontStyleUpdate(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('pid')) and
            $project->type == ProjectStructure::TYPE_ROOT
        );

        $fontStyleProperty = null;

        switch ($request->getParameter('style'))
        {
            case "bold":
                $fontStyleProperty = "is_bold";
                break;
            case "italic":
                $fontStyleProperty = "is_italic";
                break;
            case "underline":
                $fontStyleProperty = "is_underline";
                break;
            default:
                throw new Exception("Invalid type");
        }

        $ids = Utilities::array_filter_integer(explode(",", $request->getParameter("ids")));

        $success  = false;
        $errorMsg = null;
        $data     = array();

        if ( count($ids) > 0 )
        {
            $con = $project->getTable()->getConnection();

            $bills = DoctrineQuery::create()->select('s.*')
                ->from('ProjectStructure s')
                ->whereIn('s.id', $ids)
                ->andWhere('s.root_id = ?', $project->root_id)
                ->execute();

            try
            {
                $con->beginTransaction();

                foreach ( $bills as $bill )
                {
                    $billStyle = $bill->ProjectSummaryStyle;

                    if ( $billStyle->isNew() )
                    {
                        $billStyle->project_structure_id = $bill->id;
                    }

                    $billStyle->{$fontStyleProperty} = $billStyle->{$fontStyleProperty} ? false : true;

                    $billStyle->save($con);

                    $data[] = array(
                        'id'  => $bill->id,
                        'val' => $billStyle->{$fontStyleProperty}
                    );

                    unset( $bill );
                }

                $con->commit();

                $success = true;
            } catch (Exception $e)
            {
                $con->rollback();
                $errorMsg      = $e->getMessage();
                $referenceChar = null;
            }
        }

        return $this->renderJson(array( 'success' => $success, 'errorMsg' => $errorMsg, 'data' => $data ));
    }

    public function executeGeneralSettingUpdate(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('pid')) and
            $project->type == ProjectStructure::TYPE_ROOT
        );

        $form = new ProjectSummaryGeneralSettingForm($project->ProjectSummaryGeneralSetting);

        if ( $this->isFormValid($request, $form) )
        {
            $form->save();

            $errors  = null;
            $success = true;
        }
        else
        {
            $errors  = $form->getErrors();
            $success = false;
        }

        return $this->renderJson(array( 'success' => $success, 'errors' => $errors ));
    }

    public function executePrintPdf(sfWebRequest $request)
    {
        sfConfig::set('sf_web_debug', false);
        
        $request->checkCSRFProtection();

        $this->forward404Unless($project = ProjectStructureTable::getInstance()->find($request->getParameter('pid')) and $project->type == ProjectStructure::TYPE_ROOT);

        $withPrice  = $request->getParameter('withPrice') == "withPrice" ? true : false;
        $stylesheet = file_get_contents(sfConfig::get('sf_web_dir') . '/css/projectSummary.css');

        $summaryPageGenerator = new sfBuildspaceProjectSummaryGenerator($project);

        $summaryPageGenerator->setParameters($withPrice);

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
                $layout = $this->getPartial('projectSummary/pageLayout', array(
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

                $layout .= $this->getPartial('projectSummary/itemPageLayout', array(
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

    public function executeTenderAlternativePrintPdf(sfWebRequest $request)
    {
        sfConfig::set('sf_web_debug', false);
        
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $tenderAlternative = TenderAlternativeTable::getInstance()->find($request->getParameter('id'))
        );

        $project    = $tenderAlternative->ProjectStructure;
        $withPrice  = $request->getParameter('withPrice') == "withPrice" ? true : false;
        $stylesheet = file_get_contents(sfConfig::get('sf_web_dir') . '/css/projectSummary.css');
        
        $summaryPageGenerator = new sfBuildspaceTenderAlternativeProjectSummaryGenerator($tenderAlternative);

        $summaryPageGenerator->setParameters($withPrice);

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
                $layout = $this->getPartial('projectSummary/pageLayout', array(
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

                $layout .= $this->getPartial('projectSummary/itemPageLayout', array(
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

    public function executeExportExcel(sfWebRequest $request)
    {
        sfConfig::set('sf_web_debug', false);

        $request->checkCSRFProtection();

        $this->forward404Unless(
            $project = ProjectStructureTable::getInstance()->find($request->getParameter('pid')) and
            $project->type == ProjectStructure::TYPE_ROOT and
            strlen($request->getParameter('filename')) > 0
        );

        $phpExcel = new sfProjectSummaryExcelGenerator($project);

        $tmpFile = $phpExcel->write();

        $fileSize     = filesize($tmpFile);
        $fileContents = file_get_contents($tmpFile);
        unlink($tmpFile);

        $this->getResponse()->clearHttpHeaders();
        $this->getResponse()->setStatusCode(200);
        $this->getResponse()->setContentType('application/vnd.ms-excel');
        $this->getResponse()->setHttpHeader(
            'Content-Disposition',
            'attachment; filename=' . $request->getParameter('filename') . '.xlsx'
        );
        $this->getResponse()->setHttpHeader('Content-Transfer-Encoding', 'binary');
        $this->getResponse()->setHttpHeader('Content-Length', $fileSize);

        return $this->renderText($fileContents);
    }

    public function executeTenderAlternativeExportExcel(sfWebRequest $request)
    {
        sfConfig::set('sf_web_debug', false);

        $request->checkCSRFProtection();

        $this->forward404Unless(
            $tenderAlternative = TenderAlternativeTable::getInstance()->find($request->getParameter('id')) and
            strlen($request->getParameter('filename')) > 0
        );

        $phpExcel = new sfTenderAlternativeProjectSummaryExcelGenerator($tenderAlternative);

        $tmpFile = $phpExcel->write();

        $fileSize     = filesize($tmpFile);
        $fileContents = file_get_contents($tmpFile);
        unlink($tmpFile);

        $this->getResponse()->clearHttpHeaders();
        $this->getResponse()->setStatusCode(200);
        $this->getResponse()->setContentType('application/vnd.ms-excel');
        $this->getResponse()->setHttpHeader(
            'Content-Disposition',
            'attachment; filename=' . $request->getParameter('filename') . '.xlsx'
        );
        $this->getResponse()->setHttpHeader('Content-Transfer-Encoding', 'binary');
        $this->getResponse()->setHttpHeader('Content-Length', $fileSize);

        return $this->renderText($fileContents);
    }

    public function executeContractorPrintPdf(sfWebRequest $request)
    {
        sfConfig::set('sf_web_debug', false);

        $request->checkCSRFProtection();

        $this->forward404Unless(
            $project = ProjectStructureTable::getInstance()->find($request->getParameter('pid')) and
            $project->type == ProjectStructure::TYPE_ROOT and
            $project = ProjectStructureTable::getInstance()->find($request->getParameter('pid')) and
            $tenderCompany = TenderCompanyTable::getInstance()->find($request->getParameter('cid'))
        );

        $withNotListedItem = $request->getParameter('with') == "with" ? true : false;

        $stylesheet = file_get_contents(sfConfig::get('sf_web_dir') . '/css/projectSummary.css');

        if($request->hasParameter('tid') && $tenderAlternative = TenderAlternativeTable::getInstance()->find($request->getParameter('tid')))
        {
            $project = $tenderAlternative->ProjectStructure;
            $summaryPageGenerator = new sfBuildspaceTenderAlternativeProjectSummaryGenerator($tenderAlternative, true, $tenderCompany, $withNotListedItem);
        }
        else
        {
            $summaryPageGenerator = new sfBuildspaceProjectSummaryGenerator($project, true, $tenderCompany, $withNotListedItem);
        }

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
            $carriedToNextPageText         = $project->ProjectSummaryGeneralSetting->carried_to_next_page_text;
            $continuedFromPreviousPageText = $project->ProjectSummaryGeneralSetting->continued_from_previous_page_text;
            $includeTax                    = $project->ProjectSummaryGeneralSetting->include_tax;
            $taxName                       = $project->ProjectSummaryGeneralSetting->tax_name ? $project->ProjectSummaryGeneralSetting->tax_name : '';
            $taxPercentage                 = $project->ProjectSummaryGeneralSetting->tax_percentage ? $project->ProjectSummaryGeneralSetting->tax_percentage : '';

            foreach ( $page['summary_items'] as $pageCount => $summaryItems )
            {
                $layout = $this->getPartial('projectSummary/pageLayout', array(
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

                $layout .= $this->getPartial('projectSummary/itemPageLayout', array(
                    'pageNumber'                    => $pageNumberPrefix . "&nbsp;" . $pageCount,
                    'includePrintingDate'           => $includePrintingDate,
                    'carriedToNextPageText'         => $carriedToNextPageText,
                    'continuedFromPreviousPageText' => $continuedFromPreviousPageText,
                    'summaryTitleRows'              => $page['header'],
                    'itemPage'                      => $summaryItems,
                    'withPrice'                     => true,
                    'currency'                      => $project->MainInformation->Currency->currency_code,
                    'totalProjectAmount'            => $totalProjectAmount,
                    'projectSummaryFooter'          => $projectSummaryFooter,
                    'isLastPage'                    => $isLastPage,
                    'MAX_ROWS'                      => $maxRow,
                    'additionalDescriptions'        => $page['additional_desc_price'],
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

    public function executeContractorExportExcel(sfWebRequest $request)
    {
        sfConfig::set('sf_web_debug', false);

        $request->checkCSRFProtection();

        $this->forward404Unless(
            $project = ProjectStructureTable::getInstance()->find($request->getParameter('pid')) and
            $project->type == ProjectStructure::TYPE_ROOT and
            $project = ProjectStructureTable::getInstance()->find($request->getParameter('pid')) and
            $tenderCompany = TenderCompanyTable::getInstance()->find($request->getParameter('cid')) and
            strlen($request->getParameter('filename')) > 0
        );

        $withNotListedItem = $request->getParameter('with') == "with" ? true : false;

        if($request->hasParameter('tid') && $tenderAlternative = TenderAlternativeTable::getInstance()->find($request->getParameter('tid')))
        {
            $project = $tenderAlternative->ProjectStructure;

            $phpExcel = new sfTenderAlternativeProjectSummaryExcelGenerator($tenderAlternative, $tenderCompany, $withNotListedItem);
        }
        else
        {
            $phpExcel = new sfProjectSummaryExcelGenerator($project, $tenderCompany, $withNotListedItem);
        }

        $tmpFile = $phpExcel->write();

        $fileSize     = filesize($tmpFile);
        $fileContents = file_get_contents($tmpFile);
        unlink($tmpFile);

        $this->getResponse()->clearHttpHeaders();
        $this->getResponse()->setStatusCode(200);
        $this->getResponse()->setContentType('application/vnd.ms-excel');
        $this->getResponse()->setHttpHeader(
            'Content-Disposition',
            'attachment; filename=' . $request->getParameter('filename') . '.xlsx'
        );
        $this->getResponse()->setHttpHeader('Content-Transfer-Encoding', 'binary');
        $this->getResponse()->setHttpHeader('Content-Length', $fileSize);

        return $this->renderText($fileContents);
    }

    public function executeTenderAlternativeProjectSummaryData(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $tenderAlternative = TenderAlternativeTable::getInstance()->find($request->getParameter('id'))
        );

        $project = $tenderAlternative->ProjectStructure;

        $project->MainInformation->save();//we call save fo existing project that does not have project summary when it was created

        $bottomForm         = new ProjectSummaryBottomFooterForm($project->ProjectSummaryFooter);
        $tableForm          = new ProjectSummaryTableFooterForm($project->ProjectSummaryFooter);
        $generalSettingForm = new ProjectSummaryGeneralSettingForm($project->ProjectSummaryGeneralSetting);

        $overallTotalAfterMarkupRecords = TenderAlternativeTable::getOverallTotalForTenderAlternatives($project);

        $projectSumTotal = 0;

        if(array_key_exists($tenderAlternative->id, $overallTotalAfterMarkupRecords))
        {
            $projectSumTotal = $overallTotalAfterMarkupRecords[$tenderAlternative->id];
        }
        
        return $this->renderJson(array(
            'footer_form'          => array(
                'right_text'  => $bottomForm->getObject()->right_text,
                'left_text'   => $bottomForm->getObject()->left_text,
                '_csrf_token' => $bottomForm->getCSRFToken() ),
            'table_form'           => array(
                'project_summary_footer[first_row_text]'       => $tableForm->getObject()->first_row_text,
                'project_summary_footer[second_row_text]'      => $tableForm->getObject()->second_row_text,
                'project_summary_footer[project_structure_id]' => $project->id,
                'project_summary_footer[_csrf_token]'          => $tableForm->getCSRFToken() ),
            'general_setting_form' => array(
                'project_summary_general_setting[project_title]'                     => $generalSettingForm->getObject()->project_title,
                'project_summary_general_setting[additional_description]'            => $generalSettingForm->getObject()->additional_description,
                'project_summary_general_setting[summary_title]'                     => $generalSettingForm->getObject()->summary_title,
                'project_summary_general_setting[include_additional_description]'    => $generalSettingForm->getObject()->include_additional_description,
                'project_summary_general_setting[include_printing_date]'             => $generalSettingForm->getObject()->include_printing_date,
                'project_summary_general_setting[include_state_and_country]'         => $generalSettingForm->getObject()->include_state_and_country,
                'project_summary_general_setting[carried_to_next_page_text]'         => $generalSettingForm->getObject()->carried_to_next_page_text,
                'project_summary_general_setting[continued_from_previous_page_text]' => $generalSettingForm->getObject()->continued_from_previous_page_text,
                'project_summary_general_setting[page_number_prefix]'                => $generalSettingForm->getObject()->page_number_prefix,
                'project_summary_general_setting[include_tax]'                       => $generalSettingForm->getObject()->include_tax,
                'project_summary_general_setting[tax_name]'                          => $generalSettingForm->getObject()->tax_name,
                'project_summary_general_setting[tax_percentage]'                    => number_format($generalSettingForm->getObject()->tax_percentage, 2),
                'project_summary_general_setting[project_structure_id]'              => $project->id,
                'project_summary_general_setting[_csrf_token]'                       => $generalSettingForm->getCSRFToken()
            ),
            'currency_code'        => $project->MainInformation->Currency->currency_code,
            'total_cost'           => $projectSumTotal
        ));
    }

    public function executeGetTenderAlternativeBills(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $tenderAlternative = TenderAlternativeTable::getInstance()->find($request->getParameter('id'))
        );

        $pdo = TenderAlternativeTable::getInstance()->getConnection()->getDbh();

        $project = $tenderAlternative->ProjectStructure;

        $linkedBillIds = [];
        foreach($tenderAlternative->Bills as $bill)
        {
            $linkedBillIds[] = $bill->project_structure_id;
        }

        $records = [];

        if(!empty($linkedBillIds))
        {
            $stmt = $pdo->prepare("SELECT DISTINCT p.id, p.title, p.type, p.priority, p.lft, p.level, style.reference_char, style.is_bold, style.is_italic, style.is_underline
            FROM " . ProjectStructureTable::getInstance()->getTableName() . " i
            JOIN " . ProjectStructureTable::getInstance()->getTableName() . " p
            ON (i.lft BETWEEN p.lft AND p.rgt AND p.deleted_at IS NULL)
            JOIN " . TenderAlternativeBillTable::getInstance()->getTableName() . " x ON i.id = x.project_structure_id
            JOIN " . TenderAlternativeTable::getInstance()->getTableName() . " ta ON ta.id = x.tender_alternative_id
            LEFT JOIN " . ProjectSummaryBillStyleTable::getInstance()->getTableName() . " style ON (p.id = style.project_structure_id)
            WHERE ta.id = " . $tenderAlternative->id . "  AND p.root_id = ".$tenderAlternative->project_structure_id." AND i.id IN (".implode(',', $linkedBillIds).")
            AND i.root_id = p.root_id AND i.type = ".ProjectStructure::TYPE_BILL."
            AND i.type <> " . ProjectStructure::TYPE_ROOT . " AND i.type <> " . ProjectStructure::TYPE_LEVEL . "
            AND p.deleted_at IS NULL AND i.deleted_at IS NULL AND ta.deleted_at IS NULL
            ORDER BY p.lft");
            
            $stmt->execute();

            $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        $form = new BaseForm();
        $overallTotalAfterMarkupRecords = ProjectStructureTable::getOverallTotalAfterMarkupByProject($project);

        $items = [];
        foreach ( $records as $key => $record )
        {
            if($record['type'] == ProjectStructure::TYPE_ROOT)
                continue;
            
            $record['page']        = null;
            $record['amount']      = ($record['type'] == ProjectStructure::TYPE_BILL && array_key_exists($record['id'], $overallTotalAfterMarkupRecords)) ? $overallTotalAfterMarkupRecords[$record['id']] : 0;
            $record['_csrf_token'] = $form->getCSRFToken();

            if ( $record['type'] == ProjectStructure::TYPE_BILL )
            {
                $bill = ProjectStructureTable::getInstance()->find($record['id']);

                $bqPageGenerator = new sfBuildspaceBQPageGenerator($bill);
                $pages           = $bqPageGenerator->generatePages();

                $pageNo = $bqPageGenerator->getSummaryPageNumberingPrefix(count($pages['summary_pages']));//get last page

                $record['page'] = $pageNo;

                unset( $bill, $bqPageGenerator );
            }

            $items[] = $record;
            
            unset( $records[$key]);
        }

        array_push($items, array(
            'id'             => Constants::GRID_LAST_ROW,
            'title'          => "",
            'type'           => - 1,
            'level'          => 0,
            'amount'         => 0,
            'reference_char' => null,
            'is_bold'        => false,
            'is_italic'      => false,
            'is_underline'   => false,
            'page'           => null,
            '_csrf_token'    => $form->getCSRFToken()
        ));

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $items
        ));
    }
}