<?php

/**
 * postContractReport actions.
 *
 * @package    buildspace
 * @subpackage postContractReport
 * @author     1337 developers
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class postContractReportActions extends BaseActions {

    public function executeGetPrintingPreviewDataByTypes(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() AND
            $bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('bill_id')) AND
            $project = Doctrine_Core::getTable('ProjectStructure')->find($bill->root_id)
        );

        $elementGrandTotals = array();
        $postContract       = $project->PostContract;
        $revision           = PostContractClaimRevisionTable::getCurrentSelectedProjectRevision($project->PostContract);

        $elements = DoctrineQuery::create()->select('e.id, e.description, e.note')
            ->from('BillElement e')
            ->where('e.project_structure_id = ?', $bill->id)
            ->addOrderBy('e.priority ASC')
            ->fetchArray();

        // Get Type List
        $typeItems = DoctrineQuery::create()
            ->select('t.id, t.post_contract_id, t.bill_column_setting_id, t.counter, t.new_name')
            ->from('PostContractStandardClaimTypeReference t')
            ->leftJoin('t.BillColumnSetting cs')
            ->where('t.post_contract_id = ? AND cs.project_structure_id = ?', array( $project->PostContract->id, $bill->id ))
            ->orderBy('t.counter ASC')
            ->fetchArray();

        foreach ( $typeItems as $typeItem )
        {
            $typeItemObject                         = new stdClass();
            $typeItemObject->id                     = $typeItem['id'];
            $typeItemObject->bill_column_setting_id = $typeItem['bill_column_setting_id'];

            $elementGrandTotals[$typeItem['bill_column_setting_id']][] = PostContractTable::getTotalClaimRateGroupByElement($bill->id, $typeItemObject, $revision, $postContract->id);

            unset( $typeItemObject );
        }

        foreach ( $bill->BillColumnSettings as $billColumnSetting )
        {
            $defaultElementsTotal = PostContractTable::getTotalPerUnitGroupByElement($bill->id, $billColumnSetting['id'], $project->PostContract->id);
            $typeQuantityCounter  = 0;

            foreach ( $elements as $key => $element )
            {
                $elements[$key][$billColumnSetting['id'] . '-grand_total']                  = 0;
                $elements[$key][$billColumnSetting['id'] . '-type_total_percentage']        = 0;
                $elements[$key][$billColumnSetting['id'] . '-type_total_up_to_date_amount'] = 0;

                $elements[$key]['has_note'] = ( $element['note'] != null && $element['note'] != '' ) ? true : false;
            }

            // use PostContractStandardClaimTypeReference's if available
            if ( isset( $elementGrandTotals[$billColumnSetting['id']] ) )
            {
                foreach ( $elementGrandTotals[$billColumnSetting['id']] as $elementGrandTotal )
                {
                    foreach ( $elements as $key => $element )
                    {
                        $elementId = $element['id'];

                        if ( isset( $elementGrandTotal[$elementId] ) )
                        {
                            $elements[$key][$billColumnSetting['id'] . '-grand_total'] += $elementGrandTotal[$elementId][0]['total_per_unit'];
                            $elements[$key][$billColumnSetting['id'] . '-type_total_up_to_date_amount'] += $elementGrandTotal[$elementId][0]['up_to_date_amount'];
                        }
                    }

                    $typeQuantityCounter ++;
                }
            }

            // assign element total for unit that haven't been instantiate yet.
            while ($typeQuantityCounter < $billColumnSetting['quantity'])
            {
                foreach ( $elements as $key => $element )
                {
                    if ( isset( $defaultElementsTotal[$element['id']] ) )
                    {
                        $elements[$key][$billColumnSetting['id'] . '-grand_total'] += $defaultElementsTotal[$element['id']][0]['total_per_unit'];
                    }
                }

                $typeQuantityCounter ++;
            }

            foreach ( $elements as $key => $element )
            {
                if ( $elements[$key][$billColumnSetting['id'] . '-type_total_up_to_date_amount'] > 0 )
                {
                    $elements[$key][$billColumnSetting['id'] . '-type_total_percentage'] = Utilities::prelimRounding(Utilities::percent($elements[$key][$billColumnSetting['id'] . '-type_total_up_to_date_amount'], $elements[$key][$billColumnSetting['id'] . '-grand_total']));
                }
            }

            unset( $billColumnSetting );
        }

        $defaultLastRow = array(
            'id'                    => Constants::GRID_LAST_ROW,
            'description'           => '',
            'total_per_unit'        => 0,
            'prev_percentage'       => 0,
            'prev_amount'           => 0,
            'current_percentage'    => 0,
            'current_amount'        => 0,
            'up_to_date_percentage' => 0,
            'up_to_date_amount'     => 0,
            'up_to_date_qty'        => 0,
            'claim_type_ref_id'     => - 1,
            'relation_id'           => $bill->id,
        );

        array_push($elements, $defaultLastRow);

        $data = array(
            'identifier' => 'id',
            'items'      => $elements
        );

        return $this->renderJson($data);
    }

    public function executeGetPrintingPreviewDataByUnitsWithClaim(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() AND
            $bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('bill_id')) AND
            $project = Doctrine_Core::getTable('ProjectStructure')->find($bill->root_id)
        );

        $billColumns        = array();
        $gridStructure      = array();
        $elementGrandTotals = array();
        $postContract       = $project->PostContract;
        $revision           = PostContractClaimRevisionTable::getCurrentSelectedProjectRevision($project->PostContract);

        $elements = DoctrineQuery::create()->select('e.id, e.description, e.note')
            ->from('BillElement e')
            ->where('e.project_structure_id = ?', $bill->id)
            ->addOrderBy('e.priority ASC')
            ->fetchArray();

        $elementIds = array_map(function ($ar) {return $ar['id'];}, $elements);

        $contractAmountByElements = PostContractStandardClaimTable::getOverallTotalByBillElementId($elementIds, $revision);

        $unitNames = array();

        $totalClaimRates = PostContractTable::getTotalClaimRateGroupByElementAndTypeRef($bill->id, $revision, $postContract->id);

        foreach($totalClaimRates as $totalClaimRate)
        {
            $elementGrandTotals[$totalClaimRate['bill_column_setting_id']][$totalClaimRate['claim_type_ref_id']][$totalClaimRate['element_id']] = array(
                'prev_amount' => $totalClaimRate['prev_amount'],
                'up_to_date_amount' => $totalClaimRate['up_to_date_amount'],
                'up_to_date_qty' => $totalClaimRate['up_to_date_qty'],
                'current_amount' => $totalClaimRate['current_amount'],
                'total_per_unit' => $totalClaimRate['total_per_unit'],
                'prev_percentage' => $totalClaimRate['prev_percentage'],
                'up_to_date_percentage' => $totalClaimRate['up_to_date_percentage'],
                'current_percentage' => $totalClaimRate['current_percentage']
            );

            $unitNames[$totalClaimRate['bill_column_setting_id']][$totalClaimRate['claim_type_ref_id'].'-'.$totalClaimRate['counter']] = (strlen($totalClaimRate['new_name']) > 0) ? $totalClaimRate['new_name'] : "Unit ".$totalClaimRate['counter'];
        }

        foreach($unitNames as $billColumnSettingId => $unitName)
        {
            foreach($unitName as $key => $name)
            {
                $ids = explode('-', $key);
                // to be use from the front-end to generate dynamic unit table columns
                $gridStructure[$billColumnSettingId][] = array(
                    'id'       => $ids[0],//type ref id
                    'new_name' => $name
                );
            }
        }

        foreach ( $bill->BillColumnSettings as $billColumnSetting )
        {
            $typeQuantityCounter  = 0;

            // assign default variable
            foreach ( $elements as $key => $element )
            {
                $elements[$key][$billColumnSetting['id'].'-grand_total'] = (isset($contractAmountByElements[$element['id']]) && isset($contractAmountByElements[$element['id']][$billColumnSetting['id']])) ? $contractAmountByElements[$element['id']][$billColumnSetting['id']] : 0;
                $elements[$key][$billColumnSetting['id'] . '-type_total_percentage']        = 0;
                $elements[$key][$billColumnSetting['id'] . '-type_total_up_to_date_amount'] = 0;

                $elements[$key]['has_note'] = ( $element['note'] != null && $element['note'] != '' ) ? true : false;
            }


            // use PostContractStandardClaimTypeReference's if available
            if ( isset( $elementGrandTotals[$billColumnSetting['id']] ) )
            {
                if(!isset($billColumns[$billColumnSetting['id']]))
                {
                    $billColumns[$billColumnSetting['id']] = array(
                        'id'   => $billColumnSetting['id'],
                        'name' => $billColumnSetting['name'],
                    );
                }

                foreach ( $elementGrandTotals[$billColumnSetting['id']] as $typeId => $elementGrandTotal )
                {
                    foreach($elements as $key => $element)
                    {
                        foreach($elementGrandTotal as $elemId => $data)
                        {
                            if($element['id'] == $elemId)
                            {
                                $elements[$key][$typeId . '-unit_total_percentage'] = $elementGrandTotal[$elemId]['up_to_date_percentage'];
                                $elements[$key][$billColumnSetting['id'] . '-type_total_up_to_date_amount'] += $elementGrandTotal[$elemId]['up_to_date_amount'];

                                unset($elementGrandTotal[$elemId]);
                            }
                        }
                    }

                    $typeQuantityCounter ++;
                }
            }

            // calculate percentage
            foreach ( $elements as $elementKey => $element )
            {
                // by element's overall
                if (!empty($elements[$elementKey][$billColumnSetting['id'] . '-type_total_up_to_date_amount']) )
                {
                    $elements[$elementKey][$billColumnSetting['id'] . '-type_total_percentage'] = Utilities::prelimRounding(Utilities::percent($elements[$elementKey][$billColumnSetting['id'] . '-type_total_up_to_date_amount'], $elements[$elementKey][$billColumnSetting['id'] . '-grand_total']));
                }
            }

            unset( $billColumnSetting );
        }

        array_push($elements, array(
            'id'                    => Constants::GRID_LAST_ROW,
            'description'           => '',
            'total_per_unit'        => 0,
            'prev_percentage'       => 0,
            'prev_amount'           => 0,
            'current_percentage'    => 0,
            'current_amount'        => 0,
            'up_to_date_percentage' => 0,
            'up_to_date_amount'     => 0,
            'up_to_date_qty'        => 0,
            'claim_type_ref_id'     => - 1,
            'relation_id'           => $bill->id,
        ));

        $data['billColumns'] = $billColumns;

        $data['gridStructure'] = $gridStructure;

        $data['items'] = array(
            'identifier' => 'id',
            'items'      => $elements
        );

        return $this->renderJson($data);
    }

    public function executeGetPrintingPreviewDataBySelectedUnits(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() AND
            $bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('bill_id')) AND
            $project = Doctrine_Core::getTable('ProjectStructure')->find($bill->root_id)
        );

        $typeIds            = json_decode($request->getParameter('itemIds'), true);
        $billColumns        = array();
        $gridStructure      = array();
        $elementGrandTotals = array();
        $postContract       = $project->PostContract;
        $revision           = PostContractClaimRevisionTable::getCurrentSelectedProjectRevision($project->PostContract);

        $elements = DoctrineQuery::create()->select('e.id, e.description, e.note')
            ->from('BillElement e')
            ->where('e.project_structure_id = ?', $bill->id)
            ->addOrderBy('e.priority ASC')
            ->fetchArray();

        $elementIds = array_map(function ($ar) {return $ar['id'];}, $elements);

        $contractAmountByElements = PostContractStandardClaimTable::getOverallTotalByBillElementId($elementIds, $revision);

        $unitNames = array();

        $typeIdsFiltered = array();

        if(is_array($typeIds))
        {
            foreach( $typeIds as $typeId )
            {
                $explodedTypeId = explode('-', $typeId);

                if ( count($explodedTypeId) > 1 )
                {
                    $billColumnSettingId = $explodedTypeId[0];
                    $count               = $explodedTypeId[1];

                    if ( is_numeric($billColumnSettingId) AND is_numeric($count) )
                    {
                        $typeIdsFiltered[] = array($billColumnSettingId, $count);
                    }
                }

                unset($explodedTypeId);
            }
        }

        if(empty($typeIdsFiltered))
        {
            $typeIdsFiltered[] = array(-1, -1);
        }

        $totalClaimRates = PostContractTable::getTotalClaimRateGroupByElementAndTypeRef($bill->id, $revision, $postContract->id, $typeIdsFiltered);

        foreach($totalClaimRates as $totalClaimRate)
        {
            $elementGrandTotals[$totalClaimRate['bill_column_setting_id']][$totalClaimRate['claim_type_ref_id']][$totalClaimRate['element_id']] = array(
                'prev_amount' => $totalClaimRate['prev_amount'],
                'up_to_date_amount' => $totalClaimRate['up_to_date_amount'],
                'up_to_date_qty' => $totalClaimRate['up_to_date_qty'],
                'current_amount' => $totalClaimRate['current_amount'],
                'total_per_unit' => $totalClaimRate['total_per_unit'],
                'prev_percentage' => $totalClaimRate['prev_percentage'],
                'up_to_date_percentage' => $totalClaimRate['up_to_date_percentage'],
                'current_percentage' => $totalClaimRate['current_percentage']
            );

            $unitNames[$totalClaimRate['bill_column_setting_id']][$totalClaimRate['claim_type_ref_id'].'-'.$totalClaimRate['counter']] = (strlen($totalClaimRate['new_name']) > 0) ? $totalClaimRate['new_name'] : "Unit ".$totalClaimRate['counter'];
        }

        foreach($unitNames as $billColumnSettingId => $unitName)
        {
            foreach($unitName as $key => $name)
            {
                $ids = explode('-', $key);
                // to be use from the front-end to generate dynamic unit table columns
                $gridStructure[$billColumnSettingId][] = array(
                    'id'       => $ids[0],//type ref id
                    'new_name' => $name
                );
            }
        }

        foreach ( $bill->BillColumnSettings as $billColumnSetting )
        {
            $typeQuantityCounter  = 0;

            // assign default variable
            foreach ( $elements as $key => $element )
            {
                $elements[$key][$billColumnSetting['id'].'-grand_total'] = (isset($contractAmountByElements[$element['id']]) && isset($contractAmountByElements[$element['id']][$billColumnSetting['id']])) ? $contractAmountByElements[$element['id']][$billColumnSetting['id']] : 0;
                $elements[$key][$billColumnSetting['id'] . '-type_total_percentage']        = 0;
                $elements[$key][$billColumnSetting['id'] . '-type_total_up_to_date_amount'] = 0;

                $elements[$key]['has_note'] = ( $element['note'] != null && $element['note'] != '' ) ? true : false;
            }


            // use PostContractStandardClaimTypeReference's if available
            if ( isset( $elementGrandTotals[$billColumnSetting['id']] ) )
            {
                if(!isset($billColumns[$billColumnSetting['id']]))
                {
                    $billColumns[$billColumnSetting['id']] = array(
                        'id'   => $billColumnSetting['id'],
                        'name' => $billColumnSetting['name'],
                    );
                }

                foreach ( $elementGrandTotals[$billColumnSetting['id']] as $typeId => $elementGrandTotal )
                {
                    foreach($elements as $key => $element)
                    {
                        foreach($elementGrandTotal as $elemId => $data)
                        {
                            if($element['id'] == $elemId)
                            {
                                $elements[$key][$typeId . '-unit_total_percentage'] = $elementGrandTotal[$elemId]['up_to_date_percentage'];
                                $elements[$key][$billColumnSetting['id'] . '-type_total_up_to_date_amount'] += $elementGrandTotal[$elemId]['up_to_date_amount'];

                                unset($elementGrandTotal[$elemId]);
                            }
                        }
                    }

                    $typeQuantityCounter ++;
                }
            }

            // calculate percentage
            foreach ( $elements as $elementKey => $element )
            {
                // by element's overall
                if (!empty($elements[$elementKey][$billColumnSetting['id'] . '-type_total_up_to_date_amount']) )
                {
                    $elements[$elementKey][$billColumnSetting['id'] . '-type_total_percentage'] = Utilities::prelimRounding(Utilities::percent($elements[$elementKey][$billColumnSetting['id'] . '-type_total_up_to_date_amount'], $elements[$elementKey][$billColumnSetting['id'] . '-grand_total']));
                }
            }

            unset( $billColumnSetting );
        }

        array_push($elements, array(
            'id'                    => Constants::GRID_LAST_ROW,
            'description'           => '',
            'total_per_unit'        => 0,
            'prev_percentage'       => 0,
            'prev_amount'           => 0,
            'current_percentage'    => 0,
            'current_amount'        => 0,
            'up_to_date_percentage' => 0,
            'up_to_date_amount'     => 0,
            'up_to_date_qty'        => 0,
            'claim_type_ref_id'     => - 1,
            'relation_id'           => $bill->id,
        ));

        $data['billColumns'] = $billColumns;

        $data['gridStructure'] = $gridStructure;

        $data['items'] = array(
            'identifier' => 'id',
            'items'      => $elements
        );

        return $this->renderJson($data);
    }

    public function executePrintProjectClaimSummary(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('projectId'))
        );

        $count        = 0;
        $postContract = $project->PostContract;
        $revision     = PostContractClaimRevisionTable::getCurrentSelectedProjectRevision($postContract, false);
        $stylesheet   = file_get_contents(sfConfig::get('sf_web_dir') . '/css/projectSummary.css');

        $pageTitle    = $request->getPostParameter('printingPageTitle');
        $pageNoPrefix = $request->getPostParameter('pageNoPrefix');

        $records = DoctrineQuery::create()
            ->select('s.id, s.title, s.type, s.level, t.type, t.status, c.id, c.quantity, c.use_original_quantity, bls.id')
            ->from('ProjectStructure s')
            ->leftJoin('s.BillType t')
            ->leftJoin('s.BillColumnSettings c')
            ->leftJoin('s.BillLayoutSetting bls')
            ->where('s.lft >= ? AND s.rgt <= ? AND s.level != ?', array( $project->lft, $project->rgt, 0 ))
            ->andWhere('s.root_id = ?', $project->id)
            ->andWhere('s.type <= ?', array( ProjectStructure::TYPE_BILL ))
            ->addOrderBy('s.lft ASC')
            ->fetchArray();

        foreach ( $records as $key => $record )
        {
            $records[$key]['billLayoutSettingId'] = ( isset( $record['BillLayoutSetting']['id'] ) ) ? $record['BillLayoutSetting']['id'] : null;
            $count                                = $record['type'] == ProjectStructure::TYPE_BILL ? $count + 1 : $count;
            $billTotal                            = 0;
            $upToDateAmount                       = 0;
            $percentage                           = 0;

            if ( $record['type'] == ProjectStructure::TYPE_BILL and is_array($records[$key]['BillType']) )
            {
                $records[$key]['bill_type']   = $record['BillType']['type'];
                $records[$key]['bill_status'] = $record['BillType']['status'];

                if ( $records[$key]['BillType']['type'] == BillType::TYPE_PRELIMINARY )
                {
                    list( $billTotal, $upToDateAmount ) = PreliminariesClaimTable::getUpToDateAmountByBillId($postContract, $record['id'], $revision);
                }
                else
                {
                    $billTotal      = PostContractTable::getOverallTotalByBillId($record['id'], $revision->toArray());
                    $upToDateAmount = PostContractTable::getUpToDateAmountByBillId($record['id'], $revision->toArray());
                }

                $percentage = ( $billTotal > 0 ) ? Utilities::percent($upToDateAmount, $billTotal) : 0;
            }

            $records[$key]['count']                      = $record['type'] == ProjectStructure::TYPE_BILL ? $count : null;
            $records[$key]['overall_total_after_markup'] = ( $billTotal ) ? $billTotal : 0;
            $records[$key]['up_to_date_percentage']      = ( $percentage ) ? $percentage : 0;
            $records[$key]['up_to_date_amount']          = ( $upToDateAmount ) ? $upToDateAmount : 0;

            unset( $records[$key]['BillLayoutSetting'] );
            unset( $records[$key]['BillType'] );
            unset( $records[$key]['BillColumnSettings'] );
        }

        $additionalAutoBills = $this->generateDefaultPostContractBills($project);
        $reportGenerator     = new sfBuildSpacePostContractClaimReportGenerator($postContract, $records);

        $reportGenerator->setTitle($pageTitle);

        $page = $reportGenerator->generatePage();

        $pdfGen = new WkHtmlToPdf(array(
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
        ));

        if ( $page['summary_items'] instanceof SplFixedArray )
        {
            $pageNumberPrefix = $pageNoPrefix;

            foreach ( $page['summary_items'] as $pageCount => $summaryItems )
            {
                $layout = $this->getPartial('pageLayout', array(
                    'title'      => $project->ProjectSummaryGeneralSetting->summary_title,
                    'stylesheet' => $stylesheet
                ));

                $pageCount += 1;

                $isLastPage = $pageCount == count($page['summary_items']) ? true : false;
                $maxRow     = $reportGenerator->MAX_ROWS;

                if ( !$isLastPage )
                {
                    $maxRow = $reportGenerator->DEFAULT_MAX_ROWS;
                }

                $layout .= $this->getPartial('itemPageLayout', array(
                    'pageNumber'                => $pageNumberPrefix . "&nbsp;" . $pageCount,
                    'summaryTitleRows'          => $page['header'],
                    'itemPage'                  => $summaryItems,
                    'withPrice'                 => true,
                    'currency'                  => $project->MainInformation->Currency->currency_code,
                    'overallTotalProjectAmount' => $reportGenerator->getOverallContractAmount(),
                    'overallTotalClaimAmount'   => $reportGenerator->getOverallTotalClaimAmount(),
                    'projectSummaryFooter'      => null,
                    'isLastPage'                => $isLastPage,
                    'MAX_ROWS'                  => $maxRow,
                    'additionalDescriptions'    => array(),
                    'additionalAutoBills'       => $additionalAutoBills,
                    'revision'                  => $revision->toArray(),
                ));

                unset( $summaryItems );

                $pdfGen->addPage($layout);
            }
        }

        // ... send to client as file download
        return $pdfGen->send();
    }

    public function executePrintProjectClaimSummaryWithSubPackages(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('projectId'))
        );

        $postContract = $project->PostContract;
        $revision     = PostContractClaimRevisionTable::getCurrentSelectedProjectRevision($postContract, false);
        $stylesheet   = file_get_contents(sfConfig::get('sf_web_dir') . '/css/projectSummary.css');

        $normalBills = DoctrineQuery::create()
            ->select('s.id, s.title, s.type, s.level, t.type, t.status')
            ->from('ProjectStructure s')
            ->leftJoin('s.BillType t')
            ->where('s.lft >= ? AND s.rgt <= ? AND s.level != ?', array( $project->lft, $project->rgt, 0 ))
            ->andWhere('s.root_id = ?', $project->id)
            ->andWhere('s.type <= ?', array( ProjectStructure::TYPE_BILL ))
            ->addOrderBy('s.lft ASC')
            ->fetchArray();

        $billTotalContractAmount = 0;
        $billTotalClaimedAmount  = 0;

        foreach ( $normalBills as $normalBill )
        {
            $billTotal      = 0;
            $upToDateAmount = 0;

            if ( $normalBill['type'] == ProjectStructure::TYPE_BILL and is_array($normalBill['BillType']) )
            {
                if ( $normalBill['BillType']['type'] == BillType::TYPE_PRELIMINARY )
                {
                    list( $billTotal, $upToDateAmount ) = PreliminariesClaimTable::getUpToDateAmountByBillId($postContract, $normalBill['id'], $revision);
                }
                else
                {
                    $billTotal      = PostContractTable::getOverallTotalByBillId($normalBill['id'], $revision->toArray());
                    $upToDateAmount = PostContractTable::getUpToDateAmountByBillId($normalBill['id'], $revision->toArray());
                }
            }

            $billTotalContractAmount += $billTotal;
            $billTotalClaimedAmount += $upToDateAmount;
        }

        unset( $normalBills );

        // will pump main contract as information above subPackages array
        $mainContractInfo = $this->generateMainContractInfo($project, $billTotalContractAmount, $billTotalClaimedAmount);
        $data             = array_merge($mainContractInfo, SubPackageTable::getOverallTotalIncludingClaimed($project));
        $reportGenerator  = new sfBuildSpacePostContractClaimWithSubPackageReportGenerator($postContract, $data);

        $reportGenerator->setTitle($request->getParameter('printingPageTitle'));

        $page = $reportGenerator->generatePage();

        $pdfGen = new WkHtmlToPdf(array(
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
        ));

        if ( $page['summary_items'] instanceof SplFixedArray )
        {
            $pageNumberPrefix = $request->getParameter('pageNoPrefix');

            foreach ( $page['summary_items'] as $pageCount => $summaryItems )
            {
                $layout = $this->getPartial('pageLayout', array(
                    'title'      => $project->ProjectSummaryGeneralSetting->summary_title,
                    'stylesheet' => $stylesheet
                ));

                $pageCount += 1;

                $isLastPage = $pageCount == count($page['summary_items']) ? true : false;
                $maxRow     = $reportGenerator->MAX_ROWS;

                if ( !$isLastPage )
                {
                    $maxRow = $reportGenerator->DEFAULT_MAX_ROWS + 5;
                }

                $layout .= $this->getPartial('projectClaimSummarySubPackagePageLayout', array(
                    'pageNumber'                      => $pageNumberPrefix . "&nbsp;" . $pageCount,
                    'summaryTitleRows'                => $page['header'],
                    'itemPage'                        => $summaryItems,
                    'withPrice'                       => true,
                    'currency'                        => $project->MainInformation->Currency->currency_code,
                    'overallContractAmount'           => $reportGenerator->getTotalContractAmount(),
                    'overallContractClaimAmount'      => $reportGenerator->getContractTotalClaimAmount(),
                    'overallSubPackageContractAmount' => $reportGenerator->getTotalSubPackageContractAmount(),
                    'overallSubPackageClaimAmount'    => $reportGenerator->getSubPackageTotalClaimAmount(),
                    'projectSummaryFooter'            => null,
                    'isLastPage'                      => $isLastPage,
                    'MAX_ROWS'                        => $maxRow,
                    'revision'                        => $revision->toArray(),
                ));

                unset( $summaryItems );

                $pdfGen->addPage($layout);
            }
        }

        // ... send to client as file download
        return $pdfGen->send();
    }

    public function executeExportExcelProjectClaimReport(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('projectId'))
        );

        $count        = 0;
        $postContract = $project->PostContract;
        $revision     = PostContractClaimRevisionTable::getCurrentSelectedProjectRevision($postContract, false);

        $fileName     = $request->getPostParameter('exportFileName') ? : 'Project Claim Summary';
        $pageTitle    = $request->getPostParameter('printingPageTitle');
        $pageNoPrefix = $request->getPostParameter('pageNoPrefix');

        $records = DoctrineQuery::create()
            ->select('s.id, s.title, s.type, s.level, t.type, t.status, c.id, c.quantity, c.use_original_quantity, bls.id')
            ->from('ProjectStructure s')
            ->leftJoin('s.BillType t')
            ->leftJoin('s.BillColumnSettings c')
            ->leftJoin('s.BillLayoutSetting bls')
            ->where('s.lft >= ? AND s.rgt <= ? AND s.level != ?', array( $project->lft, $project->rgt, 0 ))
            ->andWhere('s.root_id = ?', $project->id)
            ->andWhere('s.type <= ?', array( ProjectStructure::TYPE_BILL ))
            ->addOrderBy('s.lft ASC')
            ->fetchArray();

        foreach ( $records as $key => $record )
        {
            $records[$key]['billLayoutSettingId'] = ( isset( $record['BillLayoutSetting']['id'] ) ) ? $record['BillLayoutSetting']['id'] : null;
            $count                                = $record['type'] == ProjectStructure::TYPE_BILL ? $count + 1 : $count;
            $billTotal                            = 0;
            $upToDateAmount                       = 0;
            $percentage                           = 0;

            if ( $record['type'] == ProjectStructure::TYPE_BILL and is_array($records[$key]['BillType']) )
            {
                $records[$key]['bill_type']   = $record['BillType']['type'];
                $records[$key]['bill_status'] = $record['BillType']['status'];

                if ( $records[$key]['BillType']['type'] == BillType::TYPE_PRELIMINARY )
                {
                    list( $billTotal, $upToDateAmount ) = PreliminariesClaimTable::getUpToDateAmountByBillId($postContract, $record['id'], $revision);
                }
                else
                {
                    $billTotal      = PostContractTable::getOverallTotalByBillId($record['id'], $revision->toArray());
                    $upToDateAmount = PostContractTable::getUpToDateAmountByBillId($record['id'], $revision->toArray());
                }

                $percentage = ( $billTotal > 0 ) ? Utilities::percent($upToDateAmount, $billTotal) : 0;
            }

            $records[$key]['count']                      = $record['type'] == ProjectStructure::TYPE_BILL ? $count : null;
            $records[$key]['overall_total_after_markup'] = ( $billTotal ) ? $billTotal : 0;
            $records[$key]['up_to_date_percentage']      = ( $percentage ) ? $percentage : 0;
            $records[$key]['up_to_date_amount']          = ( $upToDateAmount ) ? $upToDateAmount : 0;

            unset( $records[$key]['BillLayoutSetting'] );
            unset( $records[$key]['BillType'] );
            unset( $records[$key]['BillColumnSettings'] );
        }

        $additionalAutoBills = $this->generateDefaultPostContractBills($project);
        $reportGenerator     = new sfBuildSpacePostContractClaimReportGenerator($postContract, $records);

        $reportGenerator->setTitle($pageTitle);

        $page = $reportGenerator->generatePage();

        // will pump the generated page data into excel exporter
        $excelGenerator = new sfBuildSpacePostContractClaimExcelExportGenerator($postContract, $revision->toArray(), $page, $additionalAutoBills, $pageNoPrefix);
        $tmpFile        = $excelGenerator->write();

        return $this->sendExportExcelHeader($fileName, $tmpFile);
    }

    public function executeExportExcelProjectClaimSummaryWithSubPackages(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('projectId'))
        );

        $postContract = $project->PostContract;
        $revision     = PostContractClaimRevisionTable::getCurrentSelectedProjectRevision($postContract, false);
        $fileName     = $request->getPostParameter('exportFileName') ? : 'Project Claim Summary with Sub Packages';
        $pageNoPrefix = $request->getPostParameter('pageNoPrefix');

        $normalBills = DoctrineQuery::create()
            ->select('s.id, s.title, s.type, s.level, t.type, t.status')
            ->from('ProjectStructure s')
            ->leftJoin('s.BillType t')
            ->where('s.lft >= ? AND s.rgt <= ? AND s.level != ?', array( $project->lft, $project->rgt, 0 ))
            ->andWhere('s.root_id = ?', $project->id)
            ->andWhere('s.type <= ?', array( ProjectStructure::TYPE_BILL ))
            ->addOrderBy('s.lft ASC')
            ->fetchArray();

        $billTotalContractAmount = 0;
        $billTotalClaimedAmount  = 0;

        foreach ( $normalBills as $normalBill )
        {
            $billTotal      = 0;
            $upToDateAmount = 0;

            if ( $normalBill['type'] == ProjectStructure::TYPE_BILL and is_array($normalBill['BillType']) )
            {
                if ( $normalBill['BillType']['type'] == BillType::TYPE_PRELIMINARY )
                {
                    list( $billTotal, $upToDateAmount ) = PreliminariesClaimTable::getUpToDateAmountByBillId($postContract, $normalBill['id'], $revision);
                }
                else
                {
                    $billTotal      = PostContractTable::getOverallTotalByBillId($normalBill['id'], $revision->toArray());
                    $upToDateAmount = PostContractTable::getUpToDateAmountByBillId($normalBill['id'], $revision->toArray());
                }
            }

            $billTotalContractAmount += $billTotal;
            $billTotalClaimedAmount += $upToDateAmount;
        }

        unset( $normalBills );

        // will pump main contract as information above subPackages array
        $mainContractInfo = $this->generateMainContractInfo($project, $billTotalContractAmount, $billTotalClaimedAmount);
        $data             = array_merge($mainContractInfo, SubPackageTable::getOverallTotalIncludingClaimed($project));
        $reportGenerator  = new sfBuildSpacePostContractClaimWithSubPackageReportGenerator($postContract, $data);

        $reportGenerator->setTitle($request->getParameter('printingPageTitle'));

        $page = $reportGenerator->generatePage();

        // will pump the generated page data into excel exporter
        $excelGenerator = new sfBuildSpacePostContractClaimWithSubPackagesExcelExportGenerator($postContract, $revision->toArray(), $page, $pageNoPrefix);
        $excelGenerator->setOverallContractAmt($reportGenerator->getTotalContractAmount());
        $excelGenerator->setOverallContractClaimAmt($reportGenerator->getContractTotalClaimAmount());
        $excelGenerator->setSubPackageContractAmt($reportGenerator->getTotalSubPackageContractAmount());
        $excelGenerator->setSubPackageClaimAmt($reportGenerator->getSubPackageTotalClaimAmount());

        $tmpFile = $excelGenerator->write();

        return $this->sendExportExcelHeader($fileName, $tmpFile);
    }

    private function generateDefaultPostContractBills(ProjectStructure $project)
    {
        $data[PostContractClaim::TYPE_VARIATION_ORDER]['title']                      = PostContractClaim::TYPE_VARIATION_ORDER_TEXT;
        $data[PostContractClaim::TYPE_VARIATION_ORDER]['up_to_date_amount']          = $project->getVariationOrderUpToDateClaimAmount() ? : 0;
        $data[PostContractClaim::TYPE_VARIATION_ORDER]['up_to_date_percentage']      = $project->getVariationOrderUpToDateClaimAmountPercentage() ? : 0;
        $data[PostContractClaim::TYPE_VARIATION_ORDER]['overall_total_after_markup'] = $project->getVariationOrderOverallTotal();

        $data[PostContractClaim::TYPE_MATERIAL_ON_SITE]['title']                      = PostContractClaim::TYPE_MATERIAL_ON_SITE_TEXT;
        $data[PostContractClaim::TYPE_MATERIAL_ON_SITE]['up_to_date_amount']          = 0;
        $data[PostContractClaim::TYPE_MATERIAL_ON_SITE]['overall_total_after_markup'] = $project->getMaterialOnSiteUpToDateClaimAmount();

        return $data;
    }

    private function generateMainContractInfo(ProjectStructure $project, $billTotalContractAmount, $billTotalClaimedAmount)
    {
        $mainContractInfo[] = array(
            'title'                => sfBuildSpacePostContractClaimWithSubPackageReportGenerator::MAIN_CONTRACT_TEXT,
            'standard_bill_amount' => $billTotalContractAmount,
            'claimed_total'        => $billTotalClaimedAmount,
            'claimed_percentage'   => Utilities::percent($billTotalClaimedAmount, $billTotalContractAmount),
            'type'                 => sfBuildSpacePostContractClaimWithSubPackageReportGenerator::NORMAL_CONTRACT_TYPE,
        );

        $mainContractInfo[] = array(
            'title'                => sfBuildSpacePostContractClaimWithSubPackageReportGenerator::VO_TEXT,
            'standard_bill_amount' => $project->getVariationOrderOverallTotal(),
            'claimed_total'        => $project->getVariationOrderUpToDateClaimAmount(),
            'claimed_percentage'   => $project->getVariationOrderUpToDateClaimAmountPercentage(),
            'type'                 => sfBuildSpacePostContractClaimWithSubPackageReportGenerator::NORMAL_CONTRACT_TYPE,
        );

        $mainContractInfo[] = array(
            'title'                => sfBuildSpacePostContractClaimWithSubPackageReportGenerator::MOS_TEXT,
            'standard_bill_amount' => 0,
            'claimed_total'        => $project->getMaterialOnSiteUpToDateClaimAmount(),
            'claimed_percentage'   => 0,
            'type'                 => sfBuildSpacePostContractClaimWithSubPackageReportGenerator::NORMAL_CONTRACT_TYPE,
        );

        return $mainContractInfo;
    }

}