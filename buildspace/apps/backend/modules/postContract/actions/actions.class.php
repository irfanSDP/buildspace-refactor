<?php

/**
 * postContract actions.
 *
 * @package    buildspace
 * @subpackage postContract
 * @author     1337 developers
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class postContractActions extends BaseActions {

    public function executeGetProjects(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest());

        $user = $this->getUser()->getGuardUser();

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => ProjectStructureTable::getProjectsByUser($user, ProjectUserPermission::STATUS_POST_CONTRACT)
        ));
    }

    protected function isModuleEnabled($postContractClaimType)
    {
        switch($postContractClaimType)
        {
            case PostContractClaim::TYPE_WATER_DEPOSIT:
                return !(sfConfig::get('app_post_contract_disabled_modules_water_deposit') ?? false);
            case PostContractClaim::TYPE_DEPOSIT:
                return !(sfConfig::get('app_post_contract_disabled_modules_deposit') ?? false);
            case PostContractClaim::TYPE_OUT_OF_CONTRACT_ITEM:
                return !(sfConfig::get('app_post_contract_disabled_modules_out_of_contract_item') ?? false);
            case PostContractClaim::TYPE_PURCHASE_ON_BEHALF:
                return !(sfConfig::get('app_post_contract_disabled_modules_purchase_on_behalf') ?? false);
            case PostContractClaim::TYPE_ADVANCED_PAYMENT:
                return !(sfConfig::get('app_post_contract_disabled_modules_advanced_payment') ?? false);
            case PostContractClaim::TYPE_WORK_ON_BEHALF:
                return !(sfConfig::get('app_post_contract_disabled_modules_work_on_behalf') ?? false);
            case PostContractClaim::TYPE_WORK_ON_BEHALF_BACK_CHARGE:
                return !(sfConfig::get('app_post_contract_disabled_modules_work_on_behalf_back_charge') ?? false);
            case PostContractClaim::TYPE_PENALTY:
                return !(sfConfig::get('app_post_contract_disabled_modules_penalty') ?? false);
            case PostContractClaim::TYPE_PERMIT:
                return !(sfConfig::get('app_post_contract_disabled_modules_permit') ?? false);
            case PostContractClaim::TYPE_POST_CONTRACT_CLAIM_MATERIAL_ON_SITE:
                return !(sfConfig::get('app_post_contract_disabled_modules_post_contract_claim_material_on_site') ?? false);
            case PostContractClaim::TYPE_REQUEST_FOR_VARIATION_CLAIM:
                return !(sfConfig::get('app_post_contract_disabled_modules_request_for_variation_claim') ?? false);
            case PostContractClaim::TYPE_DEBIT_CREDIT_NOTE:
                return !(sfConfig::get('app_post_contract_disabled_modules_debit_credit_note') ?? false);
            default:
                throw new Exception('Invalid type');
        }
    }

    public function executeGetProjectBreakdown(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('id')) and
            $project->type == ProjectStructure::TYPE_ROOT
        );

        $postContract = $project->PostContract;

        $revision         = $request->hasParameter('claimRevision') ? Doctrine_Core::getTable('PostContractClaimRevision')->find($request->getParameter('claimRevision')) : PostContractClaimRevisionTable::getCurrentSelectedProjectRevision($postContract, false);
        $filterByRevision = $request->hasParameter('claimRevision');
        $revisionArray    = $revision->toArray();

        $tenderAlternativeProjectStructureIds = [];
        $tenderAlternative = $project->getAwardedTenderAlternative();

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
        
        $query = DoctrineQuery::create()
            ->select('s.id, s.title, s.type, s.level, t.type, t.status, c.id, c.quantity, c.use_original_quantity, bls.id')
            ->from('ProjectStructure s')
            ->leftJoin('s.BillType t')
            ->leftJoin('s.BillColumnSettings c')
            ->leftJoin('s.BillLayoutSetting bls')
            ->where('s.lft >= ? AND s.rgt <= ?', array( $project->lft, $project->rgt ))
            ->andWhere('s.root_id = ?', $project->id)
            ->andWhere('s.type <= ?', ProjectStructure::TYPE_BILL);

        if(!empty($tenderAlternativeProjectStructureIds))
        {
            $query->whereIn('s.id', $tenderAlternativeProjectStructureIds);
        }

        $records = $query->addOrderBy('s.lft ASC')->fetchArray();

        $count = 0;
        $form  = new BaseForm();

        $billOverallTotalRecords         = PostContractTable::getOverallTotalGroupByBills($project);
        $billUpToDateAmountRecords       = PostContractTable::getUpToDateAmountGroupByBills($project, $revisionArray);
        $importedStandardClaimAmounts    = PostContractTable::getImportedUpToDateAmountGroupByBills($project, $revisionArray);
        $importedPreliminaryClaimAmounts = PostContractImportedPreliminaryClaimTable::getImportedBillClaimAmounts($revision->id);
        $variationOrderOmittedBillItems  = VariationOrderItemTable::getNumberOfOmittedBillItems($project);

        foreach ( $records as $key => $record )
        {
            $records[$key]['billLayoutSettingId'] = ( isset( $record['BillLayoutSetting']['id'] ) ) ? $record['BillLayoutSetting']['id'] : null;
            $count                                = $record['type'] == ProjectStructure::TYPE_BILL ? $count + 1 : $count;

            $billTotal              = 0;
            $upToDateAmount         = 0;
            $importedUpToDateAmount = 0;
            $percentage             = 0;
            $importedPercentage     = 0;

            if ( $record['type'] == ProjectStructure::TYPE_BILL and is_array($records[$key]['BillType']) )
            {
                $records[ $key ]['bill_type']        = $record['BillType']['type'];
                $records[ $key ]['bill_status']      = $record['BillType']['status'];
                $records[ $key ]['vo_omitted_items'] = $variationOrderOmittedBillItems[ $record['id'] ] ?? "";

                if ( $records[$key]['BillType']['type'] == BillType::TYPE_PRELIMINARY )
                {
                    list( $billTotal, $upToDateAmount ) = PreliminariesClaimTable::getUpToDateAmountByBillId($postContract, $record['id'], $revision);

                    $importedUpToDateAmount = $importedPreliminaryClaimAmounts[$record['id']] ?? 0;
                }
                else
                {
                    $billTotal              = array_key_exists($record['id'], $billOverallTotalRecords) ? $billOverallTotalRecords[ $record['id'] ] : 0;
                    $upToDateAmount         = array_key_exists($record['id'], $billUpToDateAmountRecords) ? $billUpToDateAmountRecords[ $record['id'] ] : 0;
                    $importedUpToDateAmount = array_key_exists($record['id'], $importedStandardClaimAmounts) ? $importedStandardClaimAmounts[ $record['id'] ] : 0;
                }

                $percentage = ( $billTotal > 0 ) ? number_format(( $upToDateAmount / $billTotal ) * 100, 2, '.', '') : 0;

                $importedPercentage = ( $billTotal > 0 ) ? number_format(( $importedUpToDateAmount / $billTotal ) * 100, 2, '.', '') : 0;
            }

            $records[$key]['count']                          = $record['type'] == ProjectStructure::TYPE_BILL ? $count : null;
            $records[$key]['overall_total_after_markup']     = ( $billTotal ) ? $billTotal : 0;
            $records[$key]['up_to_date_percentage']          = ( $percentage ) ? $percentage : 0;
            $records[$key]['up_to_date_amount']              = ( $upToDateAmount ) ? $upToDateAmount : 0;
            $records[$key]['imported_up_to_date_amount']     = ( $importedUpToDateAmount ) ? $importedUpToDateAmount : 0;
            $records[$key]['imported_up_to_date_percentage'] = ( $importedPercentage ) ? $importedPercentage : 0;
            $records[$key]['_csrf_token']                    = $form->getCSRFToken();

            unset( $records[$key]['BillLayoutSetting'] );
            unset( $records[$key]['BillType'] );
            unset( $records[$key]['BillColumnSettings'] );
        }
        
        $postContractClaims = array(
            array(//empty row
                'id'                         => Constants::GRID_LAST_ROW,
                'title'                      => "",
                'type'                       => - 1,
                'level'                      => 0,
                'count'                      => 0,
                'up_to_date_percentage'      => 0,
                'up_to_date_amount'          => 0,
                'overall_total_after_markup' => 0,
                '_csrf_token'                => 0
            ),
            array(
                'id'                         => PostContractClaim::TYPE_VARIATION_ORDER_TEXT . '-' . PostContractClaim::TYPE_VARIATION_ORDER,
                'title'                      => PostContractClaim::TYPE_VARIATION_ORDER_TEXT,
                'type'                       => PostContractClaim::TYPE_VARIATION_ORDER,
                'level'                      => 0,
                'count'                      => 0,
                'up_to_date_percentage'      => $project->getVariationOrderUpToDateClaimAmountPercentage(($filterByRevision ? $revision : null)),
                'up_to_date_amount'          => $project->getVariationOrderUpToDateClaimAmount($revision),
                'overall_total_after_markup' => $project->getVariationOrderOverallTotal(($filterByRevision ? $revision : null)),
                'imported_up_to_date_amount' => $project->getVariationOrderImportedClaimAmount(($filterByRevision ? $revision : null)),
                '_csrf_token'                => $form->getCSRFToken()
            )
        );

        if($postContract->published_type == PostContract::PUBLISHED_TYPE_NEW)
        {
            if($this->isModuleEnabled(PostContractClaim::TYPE_REQUEST_FOR_VARIATION_CLAIM))
            {
                $postContractClaims[] = [
                    'id'                         => PostContractClaim::TYPE_REQUEST_FOR_VARIATION_CLAIM_TEXT . '-' . PostContractClaim::TYPE_REQUEST_FOR_VARIATION_CLAIM,
                    'title'                      => PostContractClaim::TYPE_REQUEST_FOR_VARIATION_CLAIM_TEXT,
                    'type'                       => PostContractClaim::TYPE_REQUEST_FOR_VARIATION_CLAIM,
                    'level'                      => 0,
                    'count'                      => 0,
                    'up_to_date_percentage'      => 0,
                    'up_to_date_amount'          => 0,
                    'overall_total_after_markup' => $project->getRequestForVariationClaimOverallTotal(($filterByRevision ? $revisionArray : null)),
                    '_csrf_token'                => $form->getCSRFToken()
                ];
            }
        }
        elseif($postContract->published_type == PostContract::PUBLISHED_TYPE_NORMAL)
        {
            $postContractClaims[] = [
                'id'                         => PostContractClaim::TYPE_MATERIAL_ON_SITE_TEXT . '-' . PostContractClaim::TYPE_MATERIAL_ON_SITE,
                'title'                      => PostContractClaim::TYPE_MATERIAL_ON_SITE_TEXT,
                'type'                       => PostContractClaim::TYPE_MATERIAL_ON_SITE,
                'level'                      => 0,
                'count'                      => 0,
                'up_to_date_percentage'      => 0,
                'up_to_date_amount'          => $project->getMaterialOnSiteUpToDateClaimAmount(),
                'overall_total_after_markup' => 0,
                '_csrf_token'                => $form->getCSRFToken()
            ];
        }

        $miscellaneousRows = [];

        if($this->isModuleEnabled(PostContractClaim::TYPE_ADVANCED_PAYMENT))
        {
            $miscellaneousRows[] = array(
                'id'                         => PostContractClaim::TYPE_ADVANCE_PAYMENT_TEXT . '-' . PostContractClaim::TYPE_ADVANCED_PAYMENT,
                'title'                      => PostContractClaim::TYPE_ADVANCE_PAYMENT_TEXT,
                'type'                       => PostContractClaim::TYPE_ADVANCED_PAYMENT,
                'level'                      => 0,
                'count'                      => 0,
                'up_to_date_percentage'      => $project->getPostContractClaimUpToDatePercentage(PostContractClaim::TYPE_ADVANCED_PAYMENT, ($filterByRevision ? $revisionArray : null)),
                'up_to_date_amount'          => $project->getPostContractClaimUpToDateAmount(PostContractClaim::TYPE_ADVANCED_PAYMENT, ($filterByRevision ? $revisionArray : null)),
                'overall_total_after_markup' => $project->getPostContractClaimOverallTotal(PostContractClaim::TYPE_ADVANCED_PAYMENT, ($filterByRevision ? $revisionArray : null)),
                '_csrf_token'                => $form->getCSRFToken()
            );
        }

        if($this->isModuleEnabled(PostContractClaim::TYPE_POST_CONTRACT_CLAIM_MATERIAL_ON_SITE))
        {
            $miscellaneousRows[] = array(
                'id'                         => PostContractClaim::TYPE_MATERIAL_ON_SITE_TEXT . '-' . PostContractClaim::TYPE_POST_CONTRACT_CLAIM_MATERIAL_ON_SITE,
                'title'                      => PostContractClaim::TYPE_MATERIAL_ON_SITE_TEXT,
                'type'                       => PostContractClaim::TYPE_POST_CONTRACT_CLAIM_MATERIAL_ON_SITE,
                'level'                      => 0,
                'count'                      => 0,
                'up_to_date_percentage'      => $project->getPostContractClaimUpToDatePercentage(PostContractClaim::TYPE_POST_CONTRACT_CLAIM_MATERIAL_ON_SITE, ($filterByRevision ? $revisionArray : null)),
                'up_to_date_amount'          => $project->getPostContractClaimUpToDateAmount(PostContractClaim::TYPE_POST_CONTRACT_CLAIM_MATERIAL_ON_SITE, ($filterByRevision ? $revisionArray : null)),
                'overall_total_after_markup' => $project->getPostContractClaimOverallTotal(PostContractClaim::TYPE_POST_CONTRACT_CLAIM_MATERIAL_ON_SITE, ($filterByRevision ? $revisionArray : null)),
                'imported_up_to_date_amount' => $project->getMaterialOnSiteImportedClaimAmount(($filterByRevision ? $revisionArray : null)),
                '_csrf_token'                => $form->getCSRFToken()
            );
        }

        if($this->isModuleEnabled(PostContractClaim::TYPE_DEPOSIT))
        {
            $miscellaneousRows[] = array(
                'id'                         => PostContractClaim::TYPE_DEPOSIT_TEXT . '-' . PostContractClaim::TYPE_DEPOSIT,
                'title'                      => PostContractClaim::TYPE_DEPOSIT_TEXT,
                'type'                       => PostContractClaim::TYPE_DEPOSIT,
                'level'                      => 0,
                'count'                      => 0,
                'up_to_date_percentage'      => $project->getPostContractClaimUpToDatePercentage(PostContractClaim::TYPE_DEPOSIT, ($filterByRevision ? $revisionArray : null)),
                'up_to_date_amount'          => $project->getPostContractClaimUpToDateAmount(PostContractClaim::TYPE_DEPOSIT, ($filterByRevision ? $revisionArray : null)),
                'overall_total_after_markup' => $project->getPostContractClaimOverallTotal(PostContractClaim::TYPE_DEPOSIT, ($filterByRevision ? $revisionArray : null)),
                '_csrf_token'                => $form->getCSRFToken()
            );
        }

        if($this->isModuleEnabled(PostContractClaim::TYPE_OUT_OF_CONTRACT_ITEM))
        {
            $miscellaneousRows[] = array(
                'id'                         => PostContractClaim::TYPE_OUT_OF_CONTRACT_ITEM_TEXT . '-' . PostContractClaim::TYPE_OUT_OF_CONTRACT_ITEM,
                'title'                      => PostContractClaim::TYPE_OUT_OF_CONTRACT_ITEM_TEXT,
                'type'                       => PostContractClaim::TYPE_OUT_OF_CONTRACT_ITEM,
                'level'                      => 0,
                'count'                      => 0,
                'up_to_date_percentage'      => $project->getPostContractClaimUpToDatePercentage(PostContractClaim::TYPE_OUT_OF_CONTRACT_ITEM, ($filterByRevision ? $revisionArray : null)),
                'up_to_date_amount'          => $project->getPostContractClaimUpToDateAmount(PostContractClaim::TYPE_OUT_OF_CONTRACT_ITEM, ($filterByRevision ? $revisionArray : null)),
                'overall_total_after_markup' => $project->getPostContractClaimOverallTotal(PostContractClaim::TYPE_OUT_OF_CONTRACT_ITEM, ($filterByRevision ? $revisionArray : null), ($filterByRevision ? $revisionArray : null)),
                '_csrf_token'                => $form->getCSRFToken()
            );
        }

        if($this->isModuleEnabled(PostContractClaim::TYPE_WORK_ON_BEHALF))
        {
            $miscellaneousRows[] = array(
                'id'                         => PostContractClaim::TYPE_WORK_ON_BEHALF_TEXT . '-' . PostContractClaim::TYPE_WORK_ON_BEHALF,
                'title'                      => PostContractClaim::TYPE_WORK_ON_BEHALF_TEXT,
                'type'                       => PostContractClaim::TYPE_WORK_ON_BEHALF,
                'level'                      => 0,
                'count'                      => 0,
                'up_to_date_percentage'      => $project->getPostContractClaimUpToDatePercentage(PostContractClaim::TYPE_WORK_ON_BEHALF, ($filterByRevision ? $revisionArray : null)),
                'up_to_date_amount'          => $project->getPostContractClaimUpToDateAmount(PostContractClaim::TYPE_WORK_ON_BEHALF, ($filterByRevision ? $revisionArray : null)),
                'overall_total_after_markup' => $project->getPostContractClaimOverallTotal(PostContractClaim::TYPE_WORK_ON_BEHALF, ($filterByRevision ? $revisionArray : null)),
                '_csrf_token'                => $form->getCSRFToken()
            );
        }

        if(!empty($miscellaneousRows))
        {
            array_unshift($miscellaneousRows, array(
                'id'                         => 'MISCELLANEOUS',
                'title'                      => "MISCELLANEOUS",
                'type'                       => - 1,
                'level'                      => 0,
                'count'                      => 0,
                'up_to_date_percentage'      => 0,
                'up_to_date_amount'          => 0,
                'overall_total_after_markup' => 0,
                '_csrf_token'                => $form->getCSRFToken()
            ));
        }

        $backchargeRows = [];

        if($this->isModuleEnabled(PostContractClaim::TYPE_DEBIT_CREDIT_NOTE))
        {
            $backchargeRows[] = array(
                'id'                         => PostContractClaim::TYPE_DEBIT_CREDIT_NOTE_TEXT . '-' . PostContractClaim::TYPE_DEBIT_CREDIT_NOTE,
                'title'                      => PostContractClaim::TYPE_DEBIT_CREDIT_NOTE_TEXT,
                'type'                       => PostContractClaim::TYPE_DEBIT_CREDIT_NOTE,
                'level'                      => 0,
                'count'                      => 0,
                'up_to_date_percentage'      => 0,
                'up_to_date_amount'          => 0,
                'overall_total_after_markup' => $project->getDebitCreditNoteClaimOverallTotal(($filterByRevision ? $revisionArray : null)),
                '_csrf_token'                => $form->getCSRFToken()
            );
        }

        if($this->isModuleEnabled(PostContractClaim::TYPE_PURCHASE_ON_BEHALF))
        {
            $backchargeRows[] = array(
                'id'                         => PostContractClaim::TYPE_PURCHASE_ON_BEHALF_TEXT . '-' . PostContractClaim::TYPE_PURCHASE_ON_BEHALF,
                'title'                      => PostContractClaim::TYPE_PURCHASE_ON_BEHALF_TEXT,
                'type'                       => PostContractClaim::TYPE_PURCHASE_ON_BEHALF,
                'level'                      => 0,
                'count'                      => 0,
                'up_to_date_percentage'      => $project->getPostContractClaimUpToDatePercentage(PostContractClaim::TYPE_PURCHASE_ON_BEHALF, ($filterByRevision ? $revisionArray : null)),
                'up_to_date_amount'          => $project->getPostContractClaimUpToDateAmount(PostContractClaim::TYPE_PURCHASE_ON_BEHALF, ($filterByRevision ? $revisionArray : null)),
                'overall_total_after_markup' => $project->getPostContractClaimOverallTotal(PostContractClaim::TYPE_PURCHASE_ON_BEHALF, ($filterByRevision ? $revisionArray : null)),
                '_csrf_token'                => $form->getCSRFToken()
            );
        }

        if($this->isModuleEnabled(PostContractClaim::TYPE_WORK_ON_BEHALF_BACK_CHARGE))
        {
            $backchargeRows[] = array(
                'id'                         => PostContractClaim::TYPE_WORK_ON_BEHALF_BACK_CHARGE_TEXT . '-' . PostContractClaim::TYPE_WORK_ON_BEHALF_BACK_CHARGE,
                'title'                      => PostContractClaim::TYPE_WORK_ON_BEHALF_BACK_CHARGE_TEXT,
                'type'                       => PostContractClaim::TYPE_WORK_ON_BEHALF_BACK_CHARGE,
                'level'                      => 0,
                'count'                      => 0,
                'up_to_date_percentage'      => $project->getPostContractClaimUpToDatePercentage(PostContractClaim::TYPE_WORK_ON_BEHALF_BACK_CHARGE, ($filterByRevision ? $revisionArray : null)),
                'up_to_date_amount'          => $project->getPostContractClaimUpToDateAmount(PostContractClaim::TYPE_WORK_ON_BEHALF_BACK_CHARGE, ($filterByRevision ? $revisionArray : null)),
                'overall_total_after_markup' => $project->getPostContractClaimOverallTotal(PostContractClaim::TYPE_WORK_ON_BEHALF_BACK_CHARGE, ($filterByRevision ? $revisionArray : null)),
                '_csrf_token'                => $form->getCSRFToken()
            );
        }

        if($this->isModuleEnabled(PostContractClaim::TYPE_PENALTY))
        {
            $backchargeRows[] = array(
                'id'                         => PostContractClaim::TYPE_PENALTY_TEXT . '-' . PostContractClaim::TYPE_PENALTY,
                'title'                      => PostContractClaim::TYPE_PENALTY_TEXT,
                'type'                       => PostContractClaim::TYPE_PENALTY,
                'level'                      => 0,
                'count'                      => 0,
                'up_to_date_percentage'      => $project->getPostContractClaimUpToDatePercentage(PostContractClaim::TYPE_PENALTY, ($filterByRevision ? $revisionArray : null)),
                'up_to_date_amount'          => $project->getPostContractClaimUpToDateAmount(PostContractClaim::TYPE_PENALTY, ($filterByRevision ? $revisionArray : null)),
                'overall_total_after_markup' => $project->getPostContractClaimOverallTotal(PostContractClaim::TYPE_PENALTY, ($filterByRevision ? $revisionArray : null)),
                '_csrf_token'                => $form->getCSRFToken()
            );
        }

        if(!empty($backchargeRows))
        {
            array_unshift($backchargeRows, array(
                'id'                         => 'BACKCHARGE',
                'title'                      => "BACKCHARGE",
                'type'                       => - 1,
                'level'                      => 0,
                'count'                      => 0,
                'up_to_date_percentage'      => 0,
                'up_to_date_amount'          => 0,
                'overall_total_after_markup' => 0,
                '_csrf_token'                => $form->getCSRFToken()
            ));
        }

        $paymentOnBehalfRows = [];

        if($this->isModuleEnabled(PostContractClaim::TYPE_WATER_DEPOSIT))
        {
            $paymentOnBehalfRows[] = array(
                'id'                         => PostContractClaim::TYPE_WATER_DEPOSIT_TEXT . '-' . PostContractClaim::TYPE_WATER_DEPOSIT,
                'title'                      => PostContractClaim::TYPE_WATER_DEPOSIT_TEXT,
                'type'                       => PostContractClaim::TYPE_WATER_DEPOSIT,
                'level'                      => 0,
                'count'                      => 0,
                'up_to_date_percentage'      => $project->getPostContractClaimUpToDatePercentage(PostContractClaim::TYPE_WATER_DEPOSIT, ($filterByRevision ? $revisionArray : null)),
                'up_to_date_amount'          => $project->getPostContractClaimUpToDateAmount(PostContractClaim::TYPE_WATER_DEPOSIT, ($filterByRevision ? $revisionArray : null)),
                'overall_total_after_markup' => $project->getPostContractClaimOverallTotal(PostContractClaim::TYPE_WATER_DEPOSIT, ($filterByRevision ? $revisionArray : null)),
                '_csrf_token'                => $form->getCSRFToken()
            );
        }

        if($this->isModuleEnabled(PostContractClaim::TYPE_PERMIT))
        {
            $paymentOnBehalfRows[] = array(
                'id'                         => PostContractClaim::TYPE_PERMIT_TEXT . '-' . PostContractClaim::TYPE_PERMIT,
                'title'                      => PostContractClaim::TYPE_PERMIT_TEXT,
                'type'                       => PostContractClaim::TYPE_PERMIT,
                'level'                      => 0,
                'count'                      => 0,
                'up_to_date_percentage'      => $project->getPostContractClaimUpToDatePercentage(PostContractClaim::TYPE_PERMIT, ($filterByRevision ? $revisionArray : null)),
                'up_to_date_amount'          => $project->getPostContractClaimUpToDateAmount(PostContractClaim::TYPE_PERMIT, ($filterByRevision ? $revisionArray : null)),
                'overall_total_after_markup' => $project->getPostContractClaimOverallTotal(PostContractClaim::TYPE_PERMIT, ($filterByRevision ? $revisionArray : null)),
                '_csrf_token'                => $form->getCSRFToken()
            );
        }

        if(!empty($paymentOnBehalfRows))
        {
            array_unshift($paymentOnBehalfRows, array(
                'id'                         => 'PAYMENT-ON-BEHALF',
                'title'                      => "PAYMENT ON BEHALF",
                'type'                       => - 1,
                'level'                      => 0,
                'count'                      => 0,
                'up_to_date_percentage'      => 0,
                'up_to_date_amount'          => 0,
                'overall_total_after_markup' => 0,
                '_csrf_token'                => $form->getCSRFToken()
            ));
        }

        foreach ( $postContractClaims as $postContractClaim )
        {
            array_push($records, $postContractClaim);
        }

        if($postContract->published_type == PostContract::PUBLISHED_TYPE_NEW)
        { 
            $newPostContractClaims = array_merge($miscellaneousRows, $backchargeRows, $paymentOnBehalfRows);

            foreach( $newPostContractClaims as $newPostContractClaim)
            {
                array_push($records, $newPostContractClaim);
            }
         }

        array_push($records, array(
            'id'                         => - 999, //last empty row
            'title'                      => "",
            'type'                       => 1,
            'level'                      => 0,
            'count'                      => 0,
            'up_to_date_percentage'      => 0,
            'up_to_date_amount'          => 0,
            'overall_total_after_markup' => 0,
            '_csrf_token'                => 0
        ));

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $records
        ));
    }

    public function executeGetClaimRevisionLists(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest() and $project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('id')));

        // get bill revisions
        $claimRevision = $project->getPostContract()->getClaimRevisions();

        if ( count($claimRevision->toArray()) )
        {
            foreach ( $claimRevision as $revision )
            {
                $data['claimRevisions'][] = array(
                    'id'               => $revision['id'],
                    'post_contract_id' => $revision['post_contract_id'],
                    'version'          => $revision['version'],
                    'selected'         => $revision['current_selected_revision'],
                    'locked_status'    => ( $revision['locked_status'] ) ? 1 : 0,
                    'updated_at'       => date('d M Y', strtotime($revision['updated_at']))
                );
            }
        }
        else
        {
            $claimRevision                            = new PostContractClaimRevision();
            $claimRevision->post_contract_id          = $project->PostContract->id;
            $claimRevision->current_selected_revision = true;
            $claimRevision->version                   = 1;
            $claimRevision->save();

            $claimRevision->refresh();

            $data['claimRevisions'][] = array(
                'id'               => $claimRevision->id,
                'post_contract_id' => $claimRevision->post_contract_id,
                'version'          => $claimRevision->version,
                'selected'         => $claimRevision->current_selected_revision,
                'locked_status'    => ( $claimRevision->locked_status ) ? 1 : 0,
                'updated_at'       => date('d M Y', strtotime($claimRevision->updated_at))
            );
        }

        $claimRevisionForm      = new PostContractClaimRevisionForm();
        $data['form']           = array( 'csrf_token' => $claimRevisionForm->getCSRFToken() );
        $data['postContractId'] = $project->PostContract->id;

        return $this->renderJson($data);
    }

    public function executeSaveClaimRevision(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest() and $postContract = Doctrine_Core::getTable('PostContract')->find($request->getParameter('post_contract_id')));

        $isNew          = false;
        $claimRevisions = array();

        $claimRevisionRecord = Doctrine_Core::getTable('PostContractClaimRevision')->find($request->getParameter('revisionId'));
        $claimRevisionRecord = $claimRevisionRecord ? : new PostContractClaimRevision();
        $form                = new PostContractClaimRevisionForm($claimRevisionRecord);

        if ( $this->isFormValid($request, $form) )
        {
            if ( $claimRevisionRecord->isNew() )
            {
                $isNew = true;
            }

            $claim   = $form->save();
            $errors  = null;
            $success = true;
            $form    = new BaseForm();

            $item = array(
                'id'            => $claim->id,
                'locked_status' => ( $claim->locked_status ) ? 1 : 0,
                'updated_at'    => date('d M Y', strtotime($claim->updated_at)),
                '_csrf_token'   => $form->getCSRFToken()
            );

            if ( $isNew )
            {
                $postContract->refresh(true);

                $claims = $postContract->getClaimRevisions()->toArray();

                foreach ( $claims as $claim )
                {
                    $claimRevisions[] = array(
                        'id'               => $claim['id'],
                        'version'          => $claim['version'],
                        'post_contract_id' => $claim['post_contract_id'],
                        'selected'         => $claim['current_selected_revision'],
                        'locked_status'    => ( $claim['locked_status'] ) ? 1 : 0,
                        'updated_at'       => date('d M Y', strtotime($claim['updated_at']))
                    );
                }
            }
        }
        else
        {
            $errors  = $form->getErrors();
            $item    = array();
            $success = false;
        }

        $claimRevisionForm = new PostContractClaimRevisionForm();

        $data         = array( 'success' => $success, 'errors' => $errors, 'item' => $item, 'claimRevisions' => $claimRevisions );
        $data['form'] = array( 'csrf_token' => $claimRevisionForm->getCSRFToken() );

        return $this->renderJson($data);
    }

    public function executeAssignNewSelectedRevision(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest() and $postContract = Doctrine_Core::getTable('PostContract')->find($request->getParameter('post_contract_id')) and $claimRevision = Doctrine_Core::getTable('PostContractClaimRevision')->find($request->getParameter('revisionId')));

        $claimRevisions = array();
        $form           = new PostContractClaimRevisionForm($claimRevision, array( 'type' => 'assignSelectedRevision' ));

        if ( $this->isFormValid($request, $form) )
        {
            $pdo = $claimRevision->getTable()->getConnection()->getDbh();

            $sql  = "UPDATE " . PostContractClaimRevisionTable::getInstance()->getTableName() . " SET current_selected_revision = false WHERE (post_contract_id = :postContractId)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(array( 'postContractId' => $postContract->id ));

            $sql  = "UPDATE " . PostContractClaimRevisionTable::getInstance()->getTableName() . " SET current_selected_revision = true WHERE (id = :revisionId)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(array( 'revisionId' => $claimRevision->id ));

            $errors  = null;
            $success = true;

            $postContract->refresh();

            $claims = $postContract->getClaimRevisions()->toArray();

            foreach ( $claims as $claim )
            {
                $claimRevisions[] = array(
                    'id'               => $claim['id'],
                    'version'          => $claim['version'],
                    'post_contract_id' => $claim['post_contract_id'],
                    'selected'         => $claim['current_selected_revision'],
                    'locked_status'    => ( $claim['locked_status'] ) ? 1 : 0,
                    'updated_at'       => date('d M Y', strtotime($claim['updated_at']))
                );
            }
        }
        else
        {
            $errors  = $form->getErrors();
            $success = false;
        }

        $claimRevisionForm = new PostContractClaimRevisionForm();

        return $this->renderJson(array(
            'success' => $success,
            'errors' => $errors,
            'claimRevisions' => $claimRevisions,
            'form' => array( 'csrf_token' => $claimRevisionForm->getCSRFToken() )
        ));
    }

    public function executeGetBillInfo(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $structure = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('id')) and
            $postContract = Doctrine_Core::getTable('PostContract')->findOneBy('project_structure_id', $structure->root_id)
        );

        $data['markup_settings'] = array(
            'bill_markup_enabled'    => $structure->BillMarkupSetting->bill_markup_enabled,
            'bill_markup_percentage' => number_format($structure->BillMarkupSetting->bill_markup_percentage, 2, '.', ''),
            'bill_markup_amount'     => number_format($structure->BillMarkupSetting->bill_markup_amount, 2, '.', ''),
            'element_markup_enabled' => $structure->BillMarkupSetting->element_markup_enabled,
            'item_markup_enabled'    => $structure->BillMarkupSetting->item_markup_enabled,
            'rounding_type'          => $structure->BillMarkupSetting->rounding_type
        );

        $data['bill_type'] = array(
            'id'   => $structure->BillType->id,
            'type' => $structure->BillType->type
        );

        $data['column_settings'] = DoctrineQuery::create()->select('c.id, c.name, c.quantity, c.is_hidden, c.total_floor_area_m2, c.total_floor_area_ft2, c.floor_area_has_build_up, c.floor_area_use_metric, c.floor_area_display_metric, c.show_estimated_total_cost, c.remeasurement_quantity_enabled, c.use_original_quantity')
            ->from('BillColumnSetting c')
            ->where('c.project_structure_id = ?', $structure->id)
            ->addOrderBy('c.id ASC')
            ->fetchArray();

        $data['claim_project_revision_status'] = PostContractClaimRevisionTable::getCurrentProjectRevision($postContract);

        // get selected printable BQ's Claim Version
        $data['current_selected_claim_project_revision_status'] = PostContractClaimRevisionTable::getCurrentSelectedProjectRevision($postContract);

        $editable = true;

        if(($data['claim_project_revision_status']['locked_status'] || $data['claim_project_revision_status']['id'] != $data['current_selected_claim_project_revision_status']['id']))
        {
            $editable = false;
        }

        if ($postContract->published_type == PostContract::PUBLISHED_TYPE_NEW && $editable &&
            (!array_key_exists('ClaimCertificate', $data['current_selected_claim_project_revision_status']) || empty($data['current_selected_claim_project_revision_status']['ClaimCertificate'])))
        {
            $editable = false;
        }

        $data['editable'] = $editable;

        // addendum printing csrf protection
        $addendumCSRF = new BaseForm();

        $data['bqCSRFToken'] = $addendumCSRF->getCSRFToken();

        return $this->renderJson($data);
    }

    public function executeGetClaimGridEditableStatus(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $structure = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('id')) and
            $postContract = Doctrine_Core::getTable('PostContract')->findOneBy('project_structure_id', $structure->root_id)
        );

        $currentProjectRevision = PostContractClaimRevisionTable::getCurrentProjectRevision($postContract);

        $selectedProjectRevision = PostContractClaimRevisionTable::getCurrentSelectedProjectRevision($postContract);

        $editable = true;

        if($currentProjectRevision['id'] != $selectedProjectRevision['id'])
        {
            $editable = false;
        }

        return $this->renderJson(array('editable' => $editable));
    }

    public function executeGetPostContractProjectGroupLists(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() AND
            $projectStructure = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('id'))
        );

        $form = new PostContractProjectGroupsAssignmentForm($projectStructure);

        $groupWithProjects = $projectStructure->PostContractGroups;

        $projectGroups = array();

        if ( count($groupWithProjects) > 0 )
        {
            foreach ( $groupWithProjects as $groupWithProject )
            {
                $projectGroups[$groupWithProject->id] = $groupWithProject->id;
            }
        }

        $groups = Doctrine_Query::create()
            ->from('sfGuardGroup u')
            ->orderBy('u.id')
            ->execute();

        // get available user list
        $data = array();

        foreach ( $groups as $group )
        {
            $data[] = array(
                'id'          => $group->id,
                'name'        => $group->name,
                'updated_at'  => date('d/m/Y H:i', strtotime($group->updated_at)),
                '_csrf_token' => $form->getCSRFToken()
            );
        }

        return $this->renderJson(array( 'groups' => array( $projectGroups ), 'data' => array(
            'identifier' => 'id',
            'items'      => $data
        ) ));
    }

    public function executeUpdatePostContractProjectGroupInfo(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() AND
            $request->isMethod('post') AND
            $projectStructure = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('id'))
        );

        $form = new PostContractProjectGroupsAssignmentForm($projectStructure);

        if ( $this->isFormValid($request, $form) )
        {
            $group   = $form->save();
            $id      = $group->getId();
            $success = true;
            $errors  = array();
        }
        else
        {
            $id      = $request->getPostParameter('id');
            $errors  = $form->getErrors();
            $success = false;
        }

        return $this->renderJson(array( 'id' => $id, 'success' => $success, 'errorMsgs' => $errors ));
    }

    public function executeGetTaxPercentage(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest() and
            $project = Doctrine_Core::getTable('ProjectStructure')->find(intval($request->getParameter('pid'))) and
            $project->node->isRoot()
        );

        $currentSelectedClaimRevision = $project->PostContract->getLatestClaimRevision();

        $items   = [];
        $records = $project->NewPostContractFormInformation->getTaxPercentageByProject($currentSelectedClaimRevision);

        foreach ($records as $key => $record) 
        {
            $item['id'] = $record['tax_percentage'];
            $item['name'] = (string)($record['tax_percentage']). " %";

            $items[] = $item; 
        }

        return $this->renderJson(array(
            'identifier' => 'id',
            'label'      => 'name',
            'items'      => $items
        ));
    }

    public function executeGetClaimCertificateTaxPercentage(sfWebRequest $request)
    {
        $claimCertTaxes = DoctrineQuery::create()
                            ->select('cct.id, cct.tax, cct.description, cct.priority, cct.updated_at')
                            ->from("ClaimCertificateTax cct")
                            ->addOrderBy('cct.priority ASC')
                            ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
                            ->execute();

        $items = [];

        foreach ($claimCertTaxes as $key => $record) 
        {
            $item['id'] = $record['tax'];
            $item['name'] = (string)($record['tax']). " %";

            $items[] = $item; 
        }

        return $this->renderJson(array(
            'identifier' => 'id',
            'label'      => 'name',
            'items'      => $items
        ));
    }

    public function executeGetRetentionSum(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest() and
            $project = Doctrine_Core::getTable('ProjectStructure')->find(intval($request->getParameter('pid'))) and
            $project->node->isRoot());

        $latestClaimRevision = $project->PostContract->getLatestClaimRevision();

        $retentionSumByGST = $project->NewPostContractFormInformation->getRetentionSum($latestClaimRevision);

        return $this->renderJson(array(
            'retentionSumByGST' => $retentionSumByGST
        ));
    }

    public function executeCheckForPendingClaimCertificates(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest() and
            $project = Doctrine_Core::getTable('ProjectStructure')->find(intval($request->getParameter('pid'))) and
            $project->node->isRoot()
        );

        $hasPendingClaimRevision    = ! is_null($project->PostContract->getPendingClaimRevision());
        $hasInProgressClaimRevision = ! is_null($project->PostContract->getInProgressClaimRevision());

        return $this->renderJson(array(
            'hasPendingClaimCertificates'    => $hasPendingClaimRevision,
            'hasInProgressClaimCertificates' => $hasInProgressClaimRevision,
        ));
    }

    public function executeClaimCertificateForm(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest() and
            $project = Doctrine_Core::getTable('ProjectStructure')->find(intval($request->getParameter('pid'))) and
            $project->node->isRoot()
        );

        $latestClaimRevision = $project->PostContract->getLatestClaimRevision();

        if(!$claimRevision = $project->PostContract->getOpenClaimRevision())
        {   
            // To create the current version of claim revision for database query purpose.
            $claimRevision                            = new PostContractClaimRevision();
            $claimRevision->post_contract_id          = $project->PostContract->id;
            $claimRevision->version                   = $latestClaimRevision ? ($latestClaimRevision->version + 1) : PostContractClaimRevision::ORIGINAL_BILL_VERSION;

            $claimCertificate = $claimRevision->ClaimCertificate;
            $claimCertificate->PostContractClaimRevision = $claimRevision;
        }
        else
        {
            $claimCertificate = $claimRevision->ClaimCertificate;
        }

        $previousClaimRevision = $project->PostContract->getPreviousClaimRevision($claimRevision);

        $form = new ClaimCertificateForm($claimCertificate);

        $newPostContractFormInformation = $project->NewPostContractFormInformation;
        $subPackageWork1 = $newPostContractFormInformation->getSubPackageWorkByType(SubPackageWorks::TYPE_1, Doctrine_Core::HYDRATE_ARRAY);
        $subPackageWork2 = $newPostContractFormInformation->getSubPackageWorkByType(SubPackageWorks::TYPE_2, Doctrine_Core::HYDRATE_ARRAY);

        $currencyCode = $project->MainInformation->Currency->currency_code;

        $claimCertificateAmountData = $newPostContractFormInformation->getClaimCertificateAmountInfoByClaimRevision($claimRevision);

        $claimCertificatePrintSettings = $project->PostContract->ClaimCertificatePrintSetting;

        return $this->renderJson(array(
            'currencyCode'                 => $currencyCode,
            'taxLabel'                     => ($claimCertificatePrintSettings->tax_label) ? $claimCertificatePrintSettings->tax_label : 'GST',
            'contractorSubmittedDateLabel' => (!is_null($claimCertificatePrintSettings->contractor_submitted_date_label) && strlen($claimCertificatePrintSettings->contractor_submitted_date_label)) ? $claimCertificatePrintSettings->contractor_submitted_date_label : "Contractor Submitted Date",
            'siteVerifiedDateLabel'        => (!is_null($claimCertificatePrintSettings->site_verified_date_label) && strlen($claimCertificatePrintSettings->site_verified_date_label)) ? $claimCertificatePrintSettings->site_verified_date_label : "Site Verified Date",
            'certificateReceivedDateLabel' => (!is_null($claimCertificatePrintSettings->certificate_received_date_label) && strlen($claimCertificatePrintSettings->certificate_received_date_label)) ? $claimCertificatePrintSettings->certificate_received_date_label : "Certificate Received Date",
            'post_contract_info'  => array(
                'companyName'     => $project->MainInformation->getEProjectProject()->Subsidiary->name ?? Doctrine_Core::getTable('myCompanyProfile')->find(1)->name,
                'contractorName'  => $project->TenderSetting->AwardedCompany->name ?? '-',
                'formNumber'      => (string)$newPostContractFormInformation->form_number,
                'claimNumber'     => !$claimRevision ? "-" : (string)$claimRevision->version,
                'subPackageWork1' => ($subPackageWork1) ? $subPackageWork1['name'] : "",
                'subPackageWork2' => ($subPackageWork2) ? $subPackageWork2['name'] : "",
                'contractSum'     => $currencyCode." ".number_format($claimCertificateAmountData['contractSum'], 2, '.', ','),
                'workDoneAmount'  => $currencyCode." ".number_format($claimCertificateAmountData['workDoneAmount'], 2, '.', ','),
                'completionPercentage' => (string) $claimCertificateAmountData['percentageCompletion']. "%"
            ),
            'claim_certificate'=> array(
                'claim_certificate[is_first_claim_certificate]'   => !$claimCertificate->exists() && !$previousClaimRevision,
                'claim_certificate[contractor_submitted_date]'    => $claimCertificate->exists() ? date('Y-m-d', strtotime($claimCertificate->contractor_submitted_date)) : date('Y-m-d'),
                'claim_certificate[site_verified_date]'           => $claimCertificate->exists() ? date('Y-m-d', strtotime($claimCertificate->site_verified_date)) : date('Y-m-d'),
                'claim_certificate[qs_received_date]'             => $claimCertificate->exists() ? date('Y-m-d', strtotime($claimCertificate->qs_received_date)) : date('Y-m-d'),
                'claim_certificate[release_retention_percentage]' => $claimCertificate->exists() ? round($claimCertificate->release_retention_percentage, 2) : 0,
                'claim_certificate[release_retention_amount]'     => $claimCertificate->exists() ? round($claimCertificate->release_retention_amount, 2) : 0,
                'claim_certificate[retention_tax_percentage]'     => $claimCertificate->exists() ? (string)number_format($claimCertificate->retention_tax_percentage, 2, '.', ',') : "0.00",
                'claim_certificate[person_in_charge]'             => $claimCertificate->exists() ? $claimCertificate->person_in_charge : ($previousClaimRevision ? $previousClaimRevision->ClaimCertificate->person_in_charge : ""),
                'claim_certificate[valuation_date]'               => $claimCertificate->exists() ? date('Y-m-d', strtotime($claimCertificate->valuation_date)) : date('Y-m-d'),
                'claim_certificate[due_date]'                     => $claimCertificate->exists() ? date('Y-m-d', strtotime($claimCertificate->due_date)) : date('Y-m-d'),
                'claim_certificate[budget_amount]'                => $claimCertificate->exists() ? round($claimCertificate->budget_amount, 2) : 0,
                'claim_certificate[budget_due_date]'              => $claimCertificate->exists() ? date('Y-m-d', strtotime($claimCertificate->budget_due_date)) : date('Y-m-d'),
                'claim_certificate[tax_percentage]'               => $claimCertificate->exists() ? $claimCertificate->tax_percentage: ($previousClaimRevision ? round($previousClaimRevision->ClaimCertificate->tax_percentage, 2) : 0),
                'claim_certificate[acc_remarks]'                  => $claimCertificate->exists() ? $claimCertificate->acc_remarks : "",
                'claim_certificate[qs_remarks]'                   => $claimCertificate->exists() ? $claimCertificate->qs_remarks : "",
                'claim_certificate[retention_sum]'                => $claimCertificateAmountData['retentionSum'],
                'claim_certificate[retention_limit]'              => 0,
                'claim_certificate[_csrf_token]'                  => $form->getCSRFToken()
            )
        ));
    }

    public function executeClaimCertificateUpdate(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $project = Doctrine_Core::getTable('ProjectStructure')->find(intval($request->getParameter('pid'))) and
            $project->node->isRoot()
        );

        $errorMsg = null;
        $con      = $project->getTable()->getConnection();
        $data     = array();

        try
        {
            $con->beginTransaction();

            if(!$claimRevision = $project->PostContract->getOpenClaimRevision())
            {
                $latestClaimRevision = $project->PostContract->getLatestClaimRevision();

                if($currentSelectedClaimRevision = $project->PostContract->getCurrentSelectedClaimRevision())
                {
                    $currentSelectedClaimRevision->current_selected_revision = false;
                    $currentSelectedClaimRevision->save();
                }

                $claimRevision                            = new PostContractClaimRevision();
                $claimRevision->post_contract_id          = $project->PostContract->id;
                $claimRevision->current_selected_revision = true;
                $claimRevision->version                   = $latestClaimRevision ? ($latestClaimRevision->version + 1) : PostContractClaimRevision::ORIGINAL_BILL_VERSION;

                $claimRevision->save();
            }

            $claimCertificate = $claimRevision ? $claimRevision->ClaimCertificate : new ClaimCertificate();

            $claimCertificate->post_contract_claim_revision_id = $claimRevision->id;

            $form = new ClaimCertificateForm($claimCertificate);

            $claimCertRequest = $request->getParameter('claim_certificate');

            foreach($claimCertRequest as $key => $value)
            {
                if(!$form->offsetExists($key))
                {
                    unset($claimCertRequest[$key]);
                }
            }

            $request->offsetUnset('claim_certificate');

            $request->setParameter('claim_certificate', $claimCertRequest);

            if ($this->isFormValid($request, $form))
            {
                $claimCertificate = $form->save($con);

                $con->commit();

                $data = $claimCertificate->toArray();

                $data['contractor_submitted_date']    = date("d/m/Y", strtotime($data['contractor_submitted_date']));
                $data['site_verified_date']           = date("d/m/Y", strtotime($data['site_verified_date']));
                $data['qs_received_date']             = date("d/m/Y", strtotime($data['qs_received_date']));
                $data['release_retention_percentage'] = round($data['release_retention_percentage'], 2);
                $data['release_retention_amount']     = round($data['release_retention_amount'], 2);
                $data['budget_amount']                = round($data['budget_amount'], 2);
                $data['tax_percentage']               = round($data['tax_percentage'], 2);
                $data['due_date']                     = date("d/m/Y", strtotime($data['due_date']));
                $data['budget_due_date']              = date("d/m/Y", strtotime($data['budget_due_date']));
                $data['version']                      = $claimCertificate->PostContractClaimRevision->version;
                $data['claim_revision_id']            = $claimCertificate->PostContractClaimRevision->id;
                $data['status_txt']                   = ClaimCertificate::getStatusText($data['status']);
                $data['_csrf_token']                  = $form->getCSRFToken();

                $success = true;

                Notifications::sendNewClaimRevisionInitiatedNotifications($claimRevision->id);
            }
            else
            {
                $con->rollback();

                $errorMsg = $form->getErrors();
                $success  = false;
            }
        }
        catch (Exception $e)
        {
            $con->rollback();

            $errorMsg = $e->getMessage();
            $success  = false;
        }

        return $this->renderJson(array(
            'success'          => $success,
            'errorMsg'         => $errorMsg,
            'claimCertificate' => $data
        ));
    }

    public function executeGetClaimCertificates(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $project = Doctrine_Core::getTable('ProjectStructure')->find(intval($request->getParameter('pid'))) and
            $project->node->isRoot()
        );

        $postContract = $project->PostContract;

        $pdo = $project->getTable()->getConnection()->getDbh();

        $additionalWhereClause = '';

        if($request->hasParameter('oid')) $additionalWhereClause = "AND cert.id = {$request->getParameter('oid')}";

        $stmt = $pdo->prepare("SELECT cert.id, cert.status, cert.created_at, rev.version, rev.locked_status, rev.current_selected_revision, rev.id as claim_revision_id, note.note
        FROM ".ClaimCertificateTable::getInstance()->getTableName()." cert
        LEFT JOIN ".ClaimCertificateNoteTable::getInstance()->getTableName()." note ON cert.id = note.claim_certificate_id
        JOIN ".PostContractClaimRevisionTable::getInstance()->getTableName()." rev ON rev.id = cert.post_contract_claim_revision_id
        WHERE rev.post_contract_id = :postContractId {$additionalWhereClause} AND rev.deleted_at IS NULL ORDER BY rev.version ASC");

        $stmt->execute(['postContractId' => $postContract->id]);

        $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmt = $pdo->prepare("SELECT l.claim_certificate_id, l.created_at
        FROM ".ClaimCertificateApprovalLogTable::getInstance()->getTableName()." l
        JOIN ".ClaimCertificateTable::getInstance()->getTableName()." cert ON l.claim_certificate_id = cert.id
        JOIN ".PostContractClaimRevisionTable::getInstance()->getTableName()." rev ON rev.id = cert.post_contract_claim_revision_id
        WHERE rev.post_contract_id = :postContractId {$additionalWhereClause} AND cert.status = ".ClaimCertificate::STATUS_TYPE_APPROVED."
        AND rev.deleted_at IS NULL ORDER BY rev.version ASC");

        $stmt->execute(['postContractId' => $postContract->id]);

        $approvedRecords = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

        $certAmountCertified = ClaimCertificateTable::getAmountCertifiedByClaimCertificates($postContract);

        $claimCertificatePayments = ClaimCertificateTable::getPaidAmountByClaimCertificates($postContract);

        $form = new BaseForm();

        $claimCertificates =[];

        foreach(array_column($records, 'id') as $claimCertificateId)
        {
            $claimCertificates[] = ClaimCertificateTable::getInstance()->find($claimCertificateId);
        }

        $claimCertificateInfo = ClaimCertificateTable::getClaimCertInfo($claimCertificates);

        foreach ( $records as $key => $record )
        {
            $records[$key]['amount_certified']    = array_key_exists($record['id'], $claimCertificateInfo) ? $claimCertificateInfo[$record['id']]['amountCertified'] : 0;
            $records[$key]['paid_amount']         = array_key_exists($record['id'], $claimCertificatePayments) ? $claimCertificatePayments[$record['id']] : 0;
            $records[$key]['status_txt']          = ClaimCertificate::getStatusText($record['status']);
            $records[$key]['approval_date']       = array_key_exists($record['id'], $approvedRecords) ? date("d/m/Y", strtotime($approvedRecords[$record['id']])) : "-";
            $records[$key]['created_at']          = !empty($record['created_at']) ? date("d/m/Y", strtotime($record['created_at'])) : "";
            $records[$key]['_csrf_token']         = $form->getCSRFToken();
        }

        array_push($records, [
            'id'                        => Constants::GRID_LAST_ROW,
            'amount_certified'          => 0,
            'version'                   => "",
            'status'                    => "",
            'status_txt'                => "",
            'paid_amount'               => "",
            'locked_status'             => true,
            'current_selected_revision' => false,
            'note'                      => "",
            'created_at'                => "",
            'approval_date'             => ""
        ]);

        return $this->renderJson([
            'identifier' => 'id',
            'items'      => $records
        ]);
    }

    public function getPreviousClaimCertificate($project, $claimRevision)
    {
        $postContract = $project->PostContract;
        $currentSelectedClaimRevision = $project->PostContract->getCurrentSelectedClaimRevision();

        return DoctrineQuery::create()
            ->select('c.*')
            ->from('ClaimCertificate c')
            ->innerJoin('c.PostContractClaimRevision cr')
            ->where('cr.post_contract_id = ?', $postContract->id)
            ->andWhere('c.status = ?', ClaimCertificate::STATUS_TYPE_APPROVED)
            ->andWhere('cr.version = ?', $currentSelectedClaimRevision->version - 1)
            ->orderBy('cr.version DESC')
            ->fetchOne();
    }

    public function getTotalAmountCertifiedWithTax($project)
    {
        $pdo  = $project->getTable()->getConnection()->getDbh();

        $currentSelectedClaimRevision = $project->PostContract->getCurrentSelectedClaimRevision();

        $stmt = $pdo->prepare("SELECT SUM((claimCertificate.amount_certified + (claimCertificate.amount_certified * claimCertificate.tax_percentage / 100))) AS amount
                FROM ".PostContractClaimRevisionTable::getInstance()->getTableName()." rev
                JOIN " . ClaimCertificateTable::getInstance()->getTableName(). " claimCertificate ON claimCertificate.post_contract_claim_revision_id = rev.id
                WHERE rev.post_contract_id = " . $project->PostContract->id . "
                AND rev.version <= " . $currentSelectedClaimRevision->version . "
                AND rev.deleted_at IS NULL");

        $stmt->execute();

        $totalAmount = $stmt->fetch(PDO::FETCH_COLUMN, 0);

        return $totalAmount;
    }

    public function getAdvancePaymentAmountByClaimRevision($project)
    {
        $pdo  = $project->getTable()->getConnection()->getDbh();

        $currentSelectedClaimRevision = $project->PostContract->getCurrentSelectedClaimRevision();

        $stmt = $pdo->prepare("SELECT ROUND(COALESCE(SUM(i.quantity * i.rate), 0), 2) AS amount
                FROM " . PostContractClaimTable::getInstance()->getTableName() . " pc
                JOIN " . PostContractClaimItemTable::getInstance()->getTableName() . " i ON i.post_contract_claim_id = pc.id
                JOIN " . ClaimCertificateTable::getInstance()->getTableName(). " claimCertificate ON claimCertificate.id = pc.claim_certificate_id
                JOIN ".PostContractClaimRevisionTable::getInstance()->getTableName()." rev ON rev.id = claimCertificate.post_contract_claim_revision_id
                WHERE pc.project_structure_id = " . $project->id . "
                AND pc.type = ".PostContractClaim::TYPE_ADVANCED_PAYMENT."
                AND pc.status = ".PostContractClaim::STATUS_APPROVED."
                AND rev.id = " . $currentSelectedClaimRevision->id . "
                AND pc.deleted_at IS NULL AND i.deleted_at IS NULL");

        $stmt->execute();

        $totalAmount = $stmt->fetch(PDO::FETCH_COLUMN, 0);

        return $totalAmount;
    }

    public function getCumulativeAdvancePaymentAmountByClaimRevision($project)
    {
        $pdo  = $project->getTable()->getConnection()->getDbh();

        $currentSelectedClaimRevision = $project->PostContract->getCurrentSelectedClaimRevision();

        $stmt = $pdo->prepare("SELECT ROUND(COALESCE(SUM(i.quantity * i.rate), 0), 2) AS amount
                FROM " . PostContractClaimTable::getInstance()->getTableName() . " pc
                JOIN " . PostContractClaimItemTable::getInstance()->getTableName() . " i ON i.post_contract_claim_id = pc.id
                JOIN " . ClaimCertificateTable::getInstance()->getTableName(). " claimCertificate ON claimCertificate.id = pc.claim_certificate_id
                JOIN ".PostContractClaimRevisionTable::getInstance()->getTableName()." rev ON rev.id = claimCertificate.post_contract_claim_revision_id
                WHERE pc.project_structure_id = " . $project->id . "
                AND pc.type = ".PostContractClaim::TYPE_ADVANCED_PAYMENT."
                AND pc.status = ".PostContractClaim::STATUS_APPROVED."
                AND rev.id <= " . $currentSelectedClaimRevision->id . "
                AND pc.deleted_at IS NULL AND i.deleted_at IS NULL");

        $stmt->execute();

        $totalAmount = $stmt->fetch(PDO::FETCH_COLUMN, 0);

        return $totalAmount;
    }


    public function getAdvancePaymentCurrentPayback($project)
    {
        $pdo  = $project->getTable()->getConnection()->getDbh();

        $currentSelectedClaimRevision = $project->PostContract->getCurrentSelectedClaimRevision();

        $stmt = $pdo->prepare("SELECT i.id AS post_contract_claim_item_id, ROUND(COALESCE(SUM(ci.current_quantity), 0), 2) AS current_quantity, ROUND(COALESCE(SUM(ci.current_amount), 0), 2) AS current_amount, ROUND(COALESCE(SUM(ci.current_percentage), 0), 2) AS current_percentage
            FROM " . PostContractClaimTable::getInstance()->getTableName() . " pc
            JOIN " . PostContractClaimItemTable::getInstance()->getTableName() . " i ON i.post_contract_claim_id = pc.id
            JOIN " . PostContractClaimClaimTable::getInstance()->getTableName() . " c ON i.post_contract_claim_id = c.post_contract_claim_id
            LEFT JOIN " . PostContractClaimClaimItemTable::getInstance()->getTableName() . " ci ON ci.post_contract_claim_claim_id = c.id AND ci.post_contract_claim_item_id = i.id
            LEFT JOIN " . ClaimCertificateTable::getInstance()->getTableName() . " cert ON cert.id = c.claim_certificate_id
            LEFT JOIN " . PostContractClaimRevisionTable::getInstance()->getTableName() . " rev ON rev.id = cert.post_contract_claim_revision_id
            WHERE pc.project_structure_id = " . $project->id . "
            AND rev.id = " . $currentSelectedClaimRevision->id . "
            AND pc.type = ".PostContractClaim::TYPE_ADVANCED_PAYMENT."
            AND pc.status = ".PostContractClaim::STATUS_APPROVED."
            AND i.deleted_at IS NULL AND c.deleted_at IS NULL AND ci.deleted_at IS NULL GROUP BY i.id");

        $stmt->execute();

        $currentValues = $stmt->fetchAll(PDO::FETCH_UNIQUE | PDO::FETCH_ASSOC);

        $currentValueOverallTotal = 0;

        foreach ($currentValues as $currentValue) 
        {
            $currentValueOverallTotal += $currentValue['current_amount'];
        }

        return $currentValueOverallTotal;
    }

    public function getAdvancePaymentPreviousPayback($project)
    {
        $pdo  = $project->getTable()->getConnection()->getDbh();

        $latestRevision = $project->PostContract->getLatestApprovedClaimRevision();

        $previousClaimCertificates = PostContractClaimRevisionTable::getClaimCertificates($latestRevision->id, '<');

        $selectedRevisionClause = ( count($certIds = array_column($previousClaimCertificates, 'id')) > 0 ) ? "AND c.claim_certificate_id in (" . implode(',', $certIds) . ")" : "";

        $stmt = $pdo->prepare("SELECT DISTINCT i.id AS post_contract_claim_item_id, SUM(COALESCE(ci.current_quantity, 0)) as previous_quantity, SUM(COALESCE(ci.current_amount, 0)) as previous_amount, SUM(COALESCE(ci.current_percentage, 0)) as previous_percentage
        FROM " . PostContractClaimTable::getInstance()->getTableName() . " pc
        JOIN " . PostContractClaimItemTable::getInstance()->getTableName() . " i ON i.post_contract_claim_id = pc.id
        JOIN " . PostContractClaimClaimTable::getInstance()->getTableName() . " c ON i.post_contract_claim_id = c.post_contract_claim_id
        LEFT JOIN " . PostContractClaimClaimItemTable::getInstance()->getTableName() . " ci ON ci.post_contract_claim_claim_id = c.id AND ci.post_contract_claim_item_id = i.id
        LEFT JOIN " . ClaimCertificateTable::getInstance()->getTableName() . " cert ON cert.id = c.claim_certificate_id
        LEFT JOIN " . PostContractClaimRevisionTable::getInstance()->getTableName() . " rev ON rev.id = cert.post_contract_claim_revision_id
        WHERE pc.project_structure_id = " . $project->id . "
        {$selectedRevisionClause}
        AND pc.type = ".PostContractClaim::TYPE_ADVANCED_PAYMENT."
        AND pc.status = ".PostContractClaim::STATUS_APPROVED."
        AND i.deleted_at IS NULL AND c.deleted_at IS NULL AND ci.deleted_at IS NULL group by i.id");

        $stmt->execute();

        $previousValues = $stmt->fetchAll(PDO::FETCH_UNIQUE | PDO::FETCH_ASSOC);

        $previousValueOverallTotal = 0;

        foreach ($previousValues as $previousValue) 
        {
            $previousValueOverallTotal += $previousValue['previous_amount'];
        }

        return $previousValueOverallTotal;
    }

    public function executeClaimCertificateInfo(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $claimCertificate = Doctrine_Core::getTable('ClaimCertificate')->find(intval($request->getParameter('id')))
        );

        $project = $claimCertificate->PostContractClaimRevision->PostContract->ProjectStructure;
        $newPostContractFormInformation = $project->NewPostContractFormInformation;
        $subPackageWork1 = $newPostContractFormInformation->getSubPackageWorkByType(SubPackageWorks::TYPE_1, Doctrine_Core::HYDRATE_ARRAY);
        $subPackageWork2 = $newPostContractFormInformation->getSubPackageWorkByType(SubPackageWorks::TYPE_2, Doctrine_Core::HYDRATE_ARRAY);
        $showAccountCodeSettings = false;
        $isTopManagementVerifierEditable = false;

        $claimCertificatePrintSettings = $project->PostContract->ClaimCertificatePrintSetting;

        if($claimCertificatePrintSettings->isNew())
        {
            $claimCertificatePrintSettings = new ClaimCertificatePrintSetting();

            $claimCertificatePrintSettings->post_contract_id                           = $project->PostContract->id;
            $claimCertificatePrintSettings->certificate_title                          = "Certificate Of Payment";
            $claimCertificatePrintSettings->certificate_print_format                   = ClaimCertificatePrintSetting::CERTIFICATE_INFO_FORMAT_STANDARD;
            $claimCertificatePrintSettings->section_a_label                            = "A";
            $claimCertificatePrintSettings->section_b_label                            = "B";
            $claimCertificatePrintSettings->section_c_label                            = "C";
            $claimCertificatePrintSettings->section_d_label                            = "D";
            $claimCertificatePrintSettings->section_misc_label                         = "MISC";
            $claimCertificatePrintSettings->section_others_label                       = "Others";
            $claimCertificatePrintSettings->section_payment_on_behalf_label            = "Payment On Behalf";
            $claimCertificatePrintSettings->tax_label                                  = "GST";
            $claimCertificatePrintSettings->tax_invoice_by_sub_contractor_label        = "Tax Invoice By Sub Contractor";
            $claimCertificatePrintSettings->tax_invoice_by_subsidiary_label            = "Tax Invoice By";
            $claimCertificatePrintSettings->footer_bank_label                          = "Bank";
            $claimCertificatePrintSettings->footer_bank_signature_label                = "Prepared By";
            $claimCertificatePrintSettings->footer_cheque_number_label                 = "Cheque No.";
            $claimCertificatePrintSettings->footer_cheque_number_signature_label       = "Checked By";
            $claimCertificatePrintSettings->footer_cheque_date_label                   = "Cheque Date";
            $claimCertificatePrintSettings->footer_cheque_date_signature_label         = "Approved By";
            $claimCertificatePrintSettings->footer_cheque_amount_label                 = "Cheque Amount";
            $claimCertificatePrintSettings->footer_cheque_amount_signature_label       = "Received By";
            $claimCertificatePrintSettings->debit_credit_note_with_breakdown           = false;
            $claimCertificatePrintSettings->display_tax_column                         = true;
            $claimCertificatePrintSettings->display_tax_amount                         = false;
            $claimCertificatePrintSettings->contractor_submitted_date_label            = "Contractor Submitted Date";
            $claimCertificatePrintSettings->site_verified_date_label                   = "Site Verified Date";
            $claimCertificatePrintSettings->certificate_received_date_label            = "Certificate Received Date";
            $claimCertificatePrintSettings->request_for_variation_category_id_to_print = null;

            $claimCertificatePrintSettings->save();
        }

        $claimCertInfo = $claimCertificate->getClaimCertInfo($claimCertificatePrintSettings->certificate_print_format == ClaimCertificatePrintSetting::CERTIFICATE_INFO_FORMAT_NSC);
        $claimCertInfo['invoiceDate']     = ($claimCertificate->Invoice && $claimCertificate->Invoice->invoice_date) ? date("d/m/Y", strtotime($claimCertificate->Invoice->invoice_date)) : "";
        $claimCertInfo['invoiceNo']       = ($claimCertificate->Invoice && $claimCertificate->Invoice->invoice_number) ? $claimCertificate->Invoice->invoice_number : "";
        $claimCertInfo['budgetDueDate']   = date('d/m/Y', strtotime($claimCertificate->budget_due_date));
        $claimCertInfo['certificateDate'] = date('d/m/Y', strtotime($claimCertificate->qs_received_date));
        $claimCertInfo['tax_percentage']  = $claimCertificate->tax_percentage;

        $latestClaimRevision                             = $project->PostContract->getLatestApprovedClaimRevision();
        $latestApprovedClaimCertificate                  = $this->getPreviousClaimCertificate($project, $latestClaimRevision->version);
        $claimCertInfo['previous_tax_percentage']        = $latestApprovedClaimCertificate ? $latestApprovedClaimCertificate->tax_percentage: 0;
        $claimCertInfo['cumulativeAmountCertifiedPlusTax'] = $this->getTotalAmountCertifiedWithTax($project);

        $claimCertInfo['advancePaymentOverallTotalFormatB']  = $this->getCumulativeAdvancePaymentAmountByClaimRevision($project);
        $claimCertInfo['advancePaymentThisClaimFormatB']     = $this->getAdvancePaymentAmountByClaimRevision($project);
        $claimCertInfo['advancePaymentPreviousClaimFormatB'] = $this->getCumulativeAdvancePaymentAmountByClaimRevision($project) - $this->getAdvancePaymentAmountByClaimRevision($project);

        $claimCertInfo['advancePaymentRecoupmentThisClaim']      = $this->getAdvancePaymentCurrentPayback($project);

        $currentSelectedRevision = $project->PostContract->getCurrentSelectedClaimRevision();
        $claimCertInfo["advancePaymentRecoupmentOverallTotal"]   = $project->getPostContractClaimUpToDateAmount(PostContractClaim::TYPE_ADVANCED_PAYMENT, $currentSelectedRevision);
        $claimCertInfo['advancePaymentRecoupmentPreviousClaim']  = $claimCertInfo['advancePaymentRecoupmentOverallTotal'] - $claimCertInfo['advancePaymentRecoupmentThisClaim'];

        $claimCertInfo['advancePaymentThisClaim']      = $claimCertificate->getPostContractClaimThisClaim($project, PostContractClaim::TYPE_ADVANCED_PAYMENT);
        $claimCertInfo['advancePaymentPreviousClaim']  = $claimCertificate->getPostContractPreviousClaimTotalSecondLevel($project, PostContractClaim::TYPE_ADVANCED_PAYMENT);

        $requestForVariation = $project->MainInformation->getEProjectProject()->RequestForVariation;

        $addOmitTotal = $requestForVariation->accumulative_approved_rfv_amount + $requestForVariation->proposed_rfv_amount;

        $requestForVariationContractAndContingencySum = $project->MainInformation->getEProjectProject()->RequestForVariationContractAndContingencySum;
        $claimCertInfo['balanceOfContingency']  = number_format(($requestForVariationContractAndContingencySum->contingency_sum - $addOmitTotal), 2, '.', ',');

        $data = $claimCertificate->toArray();

        $data['currencyCode']                 = $claimCertInfo['currencyCode'];
        $data['company_name']                 = $claimCertInfo['companyName'];
        $data['contractor_name']              = $claimCertInfo['contractorName'] ?? '-';
        $data['amount_certified']             = $claimCertInfo['currencyCode']." ".number_format($claimCertInfo['amountCertified'], 2, '.', ',');
        $data['form_number']                  = (string)$newPostContractFormInformation->form_number;
        $data['claim_number']                 = (string)$claimCertInfo['claimNo'];
        $data['sub_package_work_1']           = ($subPackageWork1) ? $subPackageWork1['name'] : "";
        $data['sub_package_work_2']           = ($subPackageWork2) ? $subPackageWork2['name'] : "";
        $data['contractSum']                  = $claimCertInfo['currencyCode']." ".number_format($claimCertInfo['contractSum'], 2, '.', ',');
        $data['workDoneAmount']               = $claimCertInfo['currencyCode']." ".number_format($claimCertInfo['totalWorkDone'], 2, '.', ',');
        $data['completionPercentage']         = (string) $claimCertInfo['completionPercentage']. "%";
        $data['retention_sum']                = $claimCertInfo['retentionSumByTaxes'];
        $data['contractor_submitted_date']    = date("d/m/Y", strtotime($data['contractor_submitted_date']));
        $data['site_verified_date']           = date("d/m/Y", strtotime($data['site_verified_date']));
        $data['qs_received_date']             = date("d/m/Y", strtotime($data['qs_received_date']));
        $data['release_retention_percentage'] = number_format($data['release_retention_percentage'], 2, '.', '');
        $data['release_retention_amount']     = $claimCertInfo['currencyCode']." ".number_format($data['release_retention_amount'], 2, '.', ',');
        $data['due_date']                     = date("d/m/Y", strtotime($data['due_date']));
        $data['budget_due_date']              = date("d/m/Y", strtotime($data['budget_due_date']));
        $data['budget_amount']                = number_format($data['budget_amount'], 2, '.', '');
        $data['tax_percentage']               = number_format($data['tax_percentage'], 2, '.', '');
        $data['can_submit']                   = $claimCertificate->canSubmit();
        $data['taxLabel']                     = $claimCertificatePrintSettings->tax_label;
        $data['contractorSubmittedDateLabel'] = (!is_null($claimCertificatePrintSettings->contractor_submitted_date_label) && strlen($claimCertificatePrintSettings->contractor_submitted_date_label)) ? $claimCertificatePrintSettings->contractor_submitted_date_label : "Contractor Submitted Date";
        $data['siteVerifiedDateLabel']        = (!is_null($claimCertificatePrintSettings->site_verified_date_label) && strlen($claimCertificatePrintSettings->site_verified_date_label)) ? $claimCertificatePrintSettings->site_verified_date_label : "Site Verified Date";
        $data['certificateReceivedDateLabel'] = (!is_null($claimCertificatePrintSettings->certificate_received_date_label) && strlen($claimCertificatePrintSettings->certificate_received_date_label)) ? $claimCertificatePrintSettings->certificate_received_date_label : "Certificate Received Date";

        $importedClaimInfo['can_submit'] = ! $claimCertificate->PostContractClaimRevision->claim_submission_locked;
        $accountCodeSetting = $project->MainInformation->getEProjectProject()->AccountCodeSetting;

        if($accountCodeSetting->exists() && ($accountCodeSetting->status == EProjectAccountCodeSetting::STATUS_APPROVED) )
        {
            $showAccountCodeSettings = true;
        }

        if($data['status'] == ClaimCertificate::STATUS_TYPE_IN_PROGRESS)
        {
            $isTopManagementVerifierEditable = true;
        }

        return $this->renderJson([
            'certInfo'                        => $data,
            'printInfo'                       => $this->getClaimCertificatePrintInfo($claimCertInfo, $claimCertificatePrintSettings),
            'importedClaim'                   => $importedClaimInfo,
            'showAccountCodeSettings'         => $showAccountCodeSettings,
            'isTopManagementVerifierEditable' => $isTopManagementVerifierEditable,
        ]);
    }

    public function executeGetRequestForVariationCategories(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest()
        );

        $rfvCategories = EProjectRequestForVariationCategory::getRequestForVariationCategories();

        array_unshift($rfvCategories, [
            'id' => null,
            'name' => 'None',
        ]);

        return $this->renderJson([
            'identifier' => 'id',
            'label'      => 'name',
            'items'      => $rfvCategories
        ]);
    }

    public function executeClaimCertificatePrintSettingsForm(sfWebRequest $request)
    {
        $this->forward404Unless(
            // $request->isXmlHttpRequest() and
            $project = Doctrine_Core::getTable('ProjectStructure')->find(intval($request->getParameter('pid'))) and
            $project->node->isRoot()
        );

        $claimCertificatePrintSettings = $project->PostContract->ClaimCertificatePrintSetting;

        if($claimCertificatePrintSettings->isNew())
        {
            $claimCertificatePrintSettings = new ClaimCertificatePrintSetting();

            $claimCertificatePrintSettings->post_contract_id                           = $project->PostContract->id;
            $claimCertificatePrintSettings->certificate_title                          = "Certificate Of Payment";
            $claimCertificatePrintSettings->certificate_print_format                   = ClaimCertificatePrintSetting::CERTIFICATE_INFO_FORMAT_STANDARD;
            $claimCertificatePrintSettings->section_a_label                            = "A";
            $claimCertificatePrintSettings->section_b_label                            = "B";
            $claimCertificatePrintSettings->section_c_label                            = "C";
            $claimCertificatePrintSettings->section_d_label                            = "D";
            $claimCertificatePrintSettings->section_misc_label                         = "MISC";
            $claimCertificatePrintSettings->section_others_label                       = "Others";
            $claimCertificatePrintSettings->section_payment_on_behalf_label            = "Payment On Behalf";
            $claimCertificatePrintSettings->tax_label                                  = "GST";
            $claimCertificatePrintSettings->tax_invoice_by_sub_contractor_label        = "Tax Invoice By Sub Contractor";
            $claimCertificatePrintSettings->tax_invoice_by_subsidiary_label            = "Tax Invoice By";
            $claimCertificatePrintSettings->footer_bank_label                          = "Bank";
            $claimCertificatePrintSettings->footer_bank_signature_label                = "Prepared By";
            $claimCertificatePrintSettings->footer_cheque_number_label                 = "Cheque No.";
            $claimCertificatePrintSettings->footer_cheque_number_signature_label       = "Checked By";
            $claimCertificatePrintSettings->footer_cheque_date_label                   = "Cheque Date";
            $claimCertificatePrintSettings->footer_cheque_date_signature_label         = "Approved By";
            $claimCertificatePrintSettings->footer_cheque_amount_label                 = "Cheque Amount";
            $claimCertificatePrintSettings->footer_cheque_amount_signature_label       = "Received By";
            $claimCertificatePrintSettings->debit_credit_note_with_breakdown           = false;
            $claimCertificatePrintSettings->display_tax_column                         = true;
            $claimCertificatePrintSettings->contractor_submitted_date_label            = "Contractor Submitted Date";
            $claimCertificatePrintSettings->site_verified_date_label                   = "Site Verified Date";
            $claimCertificatePrintSettings->certificate_received_date_label            = "Certificate Received Date";
            $claimCertificatePrintSettings->request_for_variation_category_id_to_print = null;

            $claimCertificatePrintSettings->save();
        }

        $form = new ClaimCertificatePrintSettingForm($claimCertificatePrintSettings);

        return $this->renderJson([
            'claim_certificate_print_setting[certificate_title]'                          => $claimCertificatePrintSettings->certificate_title,
            'claim_certificate_print_setting[certificate_print_format]'                   => (string)$claimCertificatePrintSettings->certificate_print_format,
            'claim_certificate_print_setting[section_a_label]'                            => $claimCertificatePrintSettings->section_a_label,
            'claim_certificate_print_setting[section_b_label]'                            => $claimCertificatePrintSettings->section_b_label,
            'claim_certificate_print_setting[section_c_label]'                            => $claimCertificatePrintSettings->section_c_label,
            'claim_certificate_print_setting[section_d_label]'                            => $claimCertificatePrintSettings->section_d_label,
            'claim_certificate_print_setting[section_misc_label]'                         => $claimCertificatePrintSettings->section_misc_label,
            'claim_certificate_print_setting[section_others_label]'                       => $claimCertificatePrintSettings->section_others_label,
            'claim_certificate_print_setting[section_payment_on_behalf_label]'            => $claimCertificatePrintSettings->section_payment_on_behalf_label,
            'claim_certificate_print_setting[tax_label]'                                  => $claimCertificatePrintSettings->tax_label,
            'claim_certificate_print_setting[tax_invoice_by_sub_contractor_label]'        => $claimCertificatePrintSettings->tax_invoice_by_sub_contractor_label,
            'claim_certificate_print_setting[tax_invoice_by_subsidiary_label]'            => $claimCertificatePrintSettings->tax_invoice_by_subsidiary_label,
            'claim_certificate_print_setting[include_debit_credit_note]'                  => $claimCertificatePrintSettings->include_debit_credit_note,
            'claim_certificate_print_setting[debit_credit_note_with_breakdown]'           => $claimCertificatePrintSettings->debit_credit_note_with_breakdown,
            'claim_certificate_print_setting[include_advance_payment]'                    => $claimCertificatePrintSettings->include_advance_payment,
            'claim_certificate_print_setting[include_deposit]'                            => $claimCertificatePrintSettings->include_deposit,
            'claim_certificate_print_setting[include_material_on_site]'                   => $claimCertificatePrintSettings->include_material_on_site,
            'claim_certificate_print_setting[include_ksk]'                                => $claimCertificatePrintSettings->include_ksk,
            'claim_certificate_print_setting[include_work_on_behalf_mc]'                  => $claimCertificatePrintSettings->include_work_on_behalf_mc,
            'claim_certificate_print_setting[include_work_on_behalf]'                     => $claimCertificatePrintSettings->include_work_on_behalf,
            'claim_certificate_print_setting[include_purchase_on_behalf]'                 => $claimCertificatePrintSettings->include_purchase_on_behalf,
            'claim_certificate_print_setting[include_penalty]'                            => $claimCertificatePrintSettings->include_penalty,
            'claim_certificate_print_setting[include_utility]'                            => $claimCertificatePrintSettings->include_utility,
            'claim_certificate_print_setting[include_permit]'                             => $claimCertificatePrintSettings->include_permit,
            'claim_certificate_print_setting[footer_format]'                              => (string)$claimCertificatePrintSettings->footer_format,
            'claim_certificate_print_setting[footer_bank_label]'                          => $claimCertificatePrintSettings->footer_bank_label,
            'claim_certificate_print_setting[footer_bank_signature_label]'                => $claimCertificatePrintSettings->footer_bank_signature_label,
            'claim_certificate_print_setting[footer_cheque_number_label]'                 => $claimCertificatePrintSettings->footer_cheque_number_label,
            'claim_certificate_print_setting[footer_cheque_number_signature_label]'       => $claimCertificatePrintSettings->footer_cheque_number_signature_label,
            'claim_certificate_print_setting[footer_cheque_date_label]'                   => $claimCertificatePrintSettings->footer_cheque_date_label,
            'claim_certificate_print_setting[footer_cheque_date_signature_label]'         => $claimCertificatePrintSettings->footer_cheque_date_signature_label,
            'claim_certificate_print_setting[footer_cheque_amount_label]'                 => $claimCertificatePrintSettings->footer_cheque_amount_label,
            'claim_certificate_print_setting[footer_cheque_amount_signature_label]'       => $claimCertificatePrintSettings->footer_cheque_amount_signature_label,
            'claim_certificate_print_setting[display_tax_column]'                         => $claimCertificatePrintSettings->display_tax_column,
            'claim_certificate_print_setting[display_tax_amount]'                         => $claimCertificatePrintSettings->display_tax_amount,
            'claim_certificate_print_setting[contractor_submitted_date_label]'            => (!is_null($claimCertificatePrintSettings->contractor_submitted_date_label) && strlen($claimCertificatePrintSettings->contractor_submitted_date_label)) ? $claimCertificatePrintSettings->contractor_submitted_date_label : "Contractor Submitted Date",
            'claim_certificate_print_setting[site_verified_date_label]'                   => (!is_null($claimCertificatePrintSettings->site_verified_date_label) && strlen($claimCertificatePrintSettings->site_verified_date_label)) ? $claimCertificatePrintSettings->site_verified_date_label : "Site Verified Date",
            'claim_certificate_print_setting[certificate_received_date_label]'            => (!is_null($claimCertificatePrintSettings->certificate_received_date_label) && strlen($claimCertificatePrintSettings->certificate_received_date_label)) ? $claimCertificatePrintSettings->certificate_received_date_label : "Certificate Received Date",
            'claim_certificate_print_setting[request_for_variation_category_id_to_print]' => is_null($claimCertificatePrintSettings->request_for_variation_category_id_to_print) ? 0 : $claimCertificatePrintSettings->request_for_variation_category_id_to_print,

            'claim_certificate_print_setting[_csrf_token]'                          => $form->getCSRFToken()
        ]);
    }

    public function executeClaimCertificatePrintSettingsUpdate(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $project = Doctrine_Core::getTable('ProjectStructure')->find(intval($request->getParameter('pid'))) and
            $project->node->isRoot()
        );

        $errorMsg = null;
        $con      = $project->getTable()->getConnection();
        $data     = array();

        try
        {
            $con->beginTransaction();

            $claimCertificatePrintSettings = $project->PostContract->ClaimCertificatePrintSetting;

            $form = new ClaimCertificatePrintSettingForm($claimCertificatePrintSettings);

            if ($this->isFormValid($request, $form))
            {
                $claimCertificatePrintSettings = $form->save($con);

                $con->commit();

                $data = $claimCertificatePrintSettings->toArray();

                $data['certificate_print_format'] = (string)$data['certificate_print_format'];

                $success = true;
            }
            else
            {
                $con->rollback();

                $errorMsg = $form->getErrors();
                $success  = false;
            }
        }
        catch (Exception $e)
        {
            $con->rollback();

            $errorMsg = $e->getMessage();
            $success  = false;
        }

        return $this->renderJson([
            'success'                   => $success,
            'errorMsg'                  => $errorMsg,
            'claimCertificatePrintInfo' => $data
        ]);
    }

    protected function getClaimCertificatePrintInfo(Array $claimCertInfo, ClaimCertificatePrintSetting $claimCertificatePrintSettings)
    {
        $claimCertificateInfo                                       = [];
        $claimCertificateInfo['companyName']                        = $claimCertInfo['companyName'];
        $claimCertificateInfo['contractorName']                     = $claimCertInfo['contractorName'];
        $claimCertificateInfo['contractorAddr']                     = $claimCertInfo['contractorAddr'];
        $claimCertificateInfo['contractorTel']                      = $claimCertInfo['contractorTel'];
        $claimCertificateInfo['fax']                                = $claimCertInfo['fax'];
        $claimCertificateInfo['contractorPIC']                      = $claimCertInfo['contractorPIC'];
        $claimCertificateInfo['claimNo']                            = $claimCertInfo['claimNo'];
        $claimCertificateInfo['personInCharge']                     = $claimCertInfo['personInCharge'];
        $claimCertificateInfo['remark']                             = $claimCertInfo['remark'];
        $claimCertificateInfo['subPackageTitle']                    = $claimCertInfo['subPackageTitle'];
        $claimCertificateInfo['projectTitle']                       = $claimCertInfo['projectTitle'];
        $claimCertificateInfo['projectCode']                        = $claimCertInfo['projectCode'];
        $claimCertificateInfo['letterOfAwardNo']                    = $claimCertInfo['letterOfAwardNo'];
        $claimCertificateInfo['reference']                          = $claimCertInfo['reference'];
        $claimCertificateInfo['worksfromLA']                        = $claimCertInfo['worksfromLA'];
        $claimCertificateInfo['date']                               = $claimCertInfo['date'];
        $claimCertificateInfo['dueDate']                            = $claimCertInfo['dueDate'];
        $claimCertificateInfo['completionPercentage2']              = round($claimCertInfo['completionPercentage'], 2) . "%";
        $claimCertificateInfo['cumulativePreviousAmountCertified2'] = number_format($claimCertInfo['cumulativePreviousAmountCertified'], 2, '.', ',');

        $claimCertificateInfo['invoiceDate']     = $claimCertInfo['invoiceDate'];
        $claimCertificateInfo['invoiceNo']       = $claimCertInfo['invoiceNo'];
        $claimCertificateInfo['periodEnding']    = $claimCertInfo['budgetDueDate'];
        $claimCertificateInfo['certificateDate'] = $claimCertInfo['certificateDate'];

        $claimCertificateInfo['previousBillClaimWorkDone']                          = number_format($claimCertInfo['previousBillClaimWorkDone'], 2);
        $claimCertificateInfo['currentBillClaimWorkDone']                           = number_format($claimCertInfo['currentBillClaimWorkDone'], 2);
        $claimCertificateInfo['previousCumulativeVoWorkDone']                       = number_format($claimCertInfo['previousCumulativeVoWorkDone'], 2);
        $claimCertificateInfo['currentVoWorkDone']                                  = number_format($claimCertInfo['currentVoWorkDone'], 2);
        $claimCertificateInfo['previousCumulativeRequestForVariationWorkDone']      = number_format($claimCertInfo['previousCumulativeRequestForVariationWorkDone'], 2);
        $claimCertificateInfo['currentRequestForVariationWorkDone']                 = number_format($claimCertInfo['currentRequestForVariationWorkDone'], 2);
        $claimCertificateInfo['previousTotalWorkDone']                              = number_format($claimCertInfo['previousTotalWorkDone'], 2);
        $claimCertificateInfo['currentTotalWorkDone']                               = number_format($claimCertInfo['currentTotalWorkDone'], 2);
        $claimCertificateInfo['previousCumulativeRetentionSum']                     = "(" . number_format($claimCertInfo['previousCumulativeRetentionSum'], 2) . ")";
        $claimCertificateInfo['currentRetentionSum']                                = "(" . number_format($claimCertInfo['currentRetentionSum'], 2) . ")";
        $claimCertificateInfo['previousCumulativeReleasedRetentionAmount']          = number_format($claimCertInfo['previousCumulativeReleasedRetentionAmount'], 2);
        $claimCertificateInfo['cumulativeReleasedRetentionAmount']                  = number_format($claimCertInfo['cumulativeReleasedRetentionAmount'], 2);
        $claimCertificateInfo['cumulativeTotalRetention']                           = number_format($claimCertInfo['cumulativeTotalRetention'], 2);
        $claimCertificateInfo['previousCumulativeTotalRetention']                   = number_format($claimCertInfo['previousCumulativeTotalRetention'], 2);
        $claimCertificateInfo['currentTotalRetention']                              = number_format($claimCertInfo['currentTotalRetention'], 2);
        $claimCertificateInfo['cumulativeAmountCertified']                          = number_format($claimCertInfo['cumulativeAmountCertified'], 2);
        $claimCertificateInfo['cumulativePreviousAmountCertified']                  = number_format($claimCertInfo['cumulativePreviousAmountCertified'], 2);
        $claimCertificateInfo['cumulativeAmountGSTAmount']                          = number_format($claimCertInfo['cumulativeAmountGSTAmount'], 2);
        $claimCertificateInfo['subPackages']                                        = [];

        $claimCertificateInfo['projectAndSubPackagesCurrentAmountCertified']             = number_format($claimCertInfo['projectAndSubPackagesCurrentAmountCertified'], 2);
        $claimCertificateInfo['projectAndSubPackagesCurrentAmountCertifiedIncludingTax'] = number_format($claimCertInfo['projectAndSubPackagesCurrentAmountCertifiedIncludingTax'], 2);

        foreach($claimCertInfo['subPackages'] as $subPackage)
        {
            $claimCertificateInfo['subPackages'][] = [
                'title'                             => $subPackage['title'],
                'cumulativeAmountCertified'         => number_format($subPackage['cumulativeAmountCertified'], 2),
                'cumulativePreviousAmountCertified' => number_format($subPackage['cumulativePreviousAmountCertified'], 2),
                'amountCertified'                   => number_format($subPackage['amountCertified'], 2),
                'amountCertifiedTaxAmount'          => number_format($subPackage['amountCertifiedTaxAmount'], 2),
                'amountCertifiedIncludingTax'       => number_format($subPackage['amountCertifiedIncludingTax'], 2),
            ];
        }

        $rfvCategory = Doctrine_Core::getTable('EProjectRequestForVariationCategory')->find($claimCertificatePrintSettings->request_for_variation_category_id_to_print);

        $progressClaimInfo['rfvCategoryName'] = $rfvCategory ? $rfvCategory->name : null;

        $progressClaimInfo['voWorkDoneForSelectedRfvCategory']                   = number_format($claimCertInfo['voWorkDoneForSelectedRfvCategory'], 2);
        $progressClaimInfo['previousCumulativeVoWorkDoneForSelectedRfvCategory'] = number_format($claimCertInfo['previousVoWorkDoneForSelectedRfvCategory'], 2);
        $progressClaimInfo['currentVoWorkDoneForSelectedRfvCategory']            = number_format($claimCertInfo['currentVoWorkDoneForSelectedRfvCategory'], 2);

        $progressClaimInfo['currencyCode']                                  = $claimCertInfo['currencyCode'];
        $progressClaimInfo['companyName2']                                  = $claimCertInfo['companyName'];
        $progressClaimInfo['taxPercentage']                                 = $claimCertInfo['tax_percentage'];
        $progressClaimInfo['billTotal']                                     = number_format($claimCertInfo['billTotal'], 2, '.', ',');
        $progressClaimInfo['voOverallTotal']                                = number_format($claimCertInfo['voTotal'], 2, '.', ',');
        $progressClaimInfo['contractSum']                                   = number_format($claimCertInfo['contractSum'], 2, '.', ',');
        $progressClaimInfo['billTotalWithTax']                              = number_format(($claimCertInfo['billTotal'] + ($claimCertInfo['billTotal'] * $claimCertInfo["tax_percentage"] / 100)), 2, '.', ',');
        $progressClaimInfo['voOverallTotalWithTax']                         = number_format(($claimCertInfo['voTotal'] + ($claimCertInfo['voTotal'] * $claimCertInfo["tax_percentage"] / 100)), 2, '.', ',');
        $progressClaimInfo['urn']                                           = $claimCertInfo['projectCode'] . "/" . $claimCertInfo['claimNo'];
        $progressClaimInfo['contractSumWithTax']                            = number_format(($claimCertInfo['contractSum'] + ($claimCertInfo['contractSum'] * $claimCertInfo["tax_percentage"] / 100)), 2, '.', ',');
        $progressClaimInfo['balanceOfContingency']                          = $claimCertInfo['balanceOfContingency'];
        $progressClaimInfo['billWorkDone']                                  = number_format($claimCertInfo['billWorkDone'], 2);
        $progressClaimInfo['voWorkDone']                                    = number_format($claimCertInfo['voWorkDone'], 2);
        $progressClaimInfo['requestForVariationWorkDone']                   = number_format($claimCertInfo['requestForVariationWorkDone'], 2);
        $progressClaimInfo['showRequestForVariationWorkDone']               = $claimCertInfo['showRequestForVariationWorkDone'];
        $progressClaimInfo['materialOnSiteWorkDone']                        = number_format($claimCertInfo['materialOnSiteWorkDone'], 2, '.', ',');
        $progressClaimInfo['cumulativeMaterialOnSiteWorkDone']              = number_format($claimCertInfo['cumulativeMaterialOnSiteWorkDone'], 2, '.', ',');
        $progressClaimInfo['previousCumulativeMaterialOnSiteWorkDone']      = number_format($claimCertInfo['previousCumulativeMaterialOnSiteWorkDone'], 2, '.', ',');
        $progressClaimInfo['currentMaterialOnSiteWorkDone']                 = number_format($claimCertInfo['currentMaterialOnSiteWorkDone'], 2, '.', ',');
        $progressClaimInfo['totalWorkDone']                                 = number_format($claimCertInfo['totalWorkDone'], 2, '.', ',');
        $progressClaimInfo['completionPercentage']                          = round($claimCertInfo['completionPercentage'], 2) . "%";

        $progressClaimInfo['cumulativeRetentionSum']            = "( " . number_format($claimCertInfo['cumulativeRetentionSum'], 2, '.', ',') . " )";
        $progressClaimInfo['cumulativePreviousAmountCertified'] = number_format($claimCertInfo['cumulativePreviousAmountCertified'], 2, '.', ',');

        $progressClaimInfo['cumulativeTotalRetentionWithoutCurrentClaimRelease'] = "( " . number_format($claimCertInfo['cumulativeTotalRetentionWithoutCurrentClaimRelease'], 2, '.', ',') . " )";

        $progressClaimInfo['totalAmount']                    = number_format(round($claimCertInfo['totalAmount'], 2), 2, '.', ',');
        $progressClaimInfo['totalAmountAfterGST']            = number_format(round($claimCertInfo['totalAmountAfterGST'], 2), 2, '.', ',');
        $progressClaimInfo['currentReleaseRetentionAmount']  = '(' . number_format(round($claimCertInfo['retention_tax_percentage'], 2), 2) . '%) ' . number_format(round($claimCertInfo['currentReleaseRetentionAmount'], 2), 2, '.', ',');
        $progressClaimInfo['releaseRetentionAmountAfterGST'] = number_format(round($claimCertInfo['releaseRetentionAmountAfterGST'], 2), 2, '.', ',');
        $progressClaimInfo['amountCertified']                = number_format(round($claimCertInfo['amountCertified'], 2), 2, '.', ',');

        $amountCertifiedTax                         = $claimCertInfo['amountCertified'] * $claimCertInfo["tax_percentage"] / 100;
        $previousAmountCertifiedTax                 = $claimCertInfo['cumulativePreviousAmountCertified'] * $claimCertInfo["previous_tax_percentage"] / 100;
        $amountCertifiedPlusTax                     = $claimCertInfo['amountCertified'] + $amountCertifiedTax;
        $cumulativeAmountCertifiedPlusTax           = $claimCertInfo['cumulativeAmountCertifiedPlusTax'];
        $cumulativePreviousAmountCertifiedPlusTax   = $claimCertInfo['cumulativeAmountCertifiedPlusTax'] - $amountCertifiedPlusTax;

        $progressClaimInfo['amountCertifiedTax']                       = number_format($amountCertifiedTax,2 , '.', ',');
        $progressClaimInfo['amountCertifiedTaxPercentageLabel']        = "Tax " . $claimCertInfo['tax_percentage'];
        $progressClaimInfo['amountCertifiedPlusTax']                   = number_format(round($amountCertifiedPlusTax, 2), 2, '.', ',');
        $progressClaimInfo['cumulativePreviousAmountCertifiedPlusTax'] = number_format(round($cumulativePreviousAmountCertifiedPlusTax, 2), 2, '.', ',');
        $progressClaimInfo['cumulativeAmountCertifiedPlusTax']         = number_format(round($cumulativeAmountCertifiedPlusTax, 2), 2, '.', ',');
        $progressClaimInfo['amountCertifiedTaxAmount']                 = number_format(round($claimCertInfo['amountCertifiedTaxAmount'], 2), 2, '.', ',');
        $progressClaimInfo['amountCertifiedIncludingTax']              = number_format(round($claimCertInfo['amountCertifiedIncludingTax'], 2), 2, '.', ',');

        $progressClaimInfo['advancePaymentOverallTotal']  = number_format($claimCertInfo['advancePaymentOverallTotal'], 2, '.', ',');
        $progressClaimInfo['depositOverallTotal']         = number_format($claimCertInfo['depositOverallTotal'], 2, '.', ',');
        $progressClaimInfo['materialOnSiteOverallTotal']  = number_format($claimCertInfo['materialOnSiteOverallTotal'], 2, '.', ',');
        $progressClaimInfo['kskOverallTotal']             = number_format($claimCertInfo['kskOverallTotal'], 2, '.', ',');
        $progressClaimInfo['wobMCOverallTotal']           = number_format($claimCertInfo['wobMCOverallTotal'], 2, '.', ',');
        $progressClaimInfo['debitCreditNoteOverallTotal'] = number_format($claimCertInfo['debitCreditNoteOverallTotal'], 2, '.', ',');

        $progressClaimInfo['debitCreditNoteBreakdownOverallTotal'] = $claimCertInfo['debitCreditNoteBreakdownOverallTotal'];

        $progressClaimInfo['pobOverallTotal']          = number_format($claimCertInfo['pobOverallTotal'], 2, '.', ',');
        $progressClaimInfo['wobOverallTotal']          = number_format($claimCertInfo['wobOverallTotal'], 2, '.', ',');
        $progressClaimInfo['penaltyOverallTotal']      = number_format($claimCertInfo['penaltyOverallTotal'], 2, '.', ',');
        $progressClaimInfo['waterDepositOverallTotal'] = number_format($claimCertInfo['waterDepositOverallTotal'], 2, '.', ',');
        $progressClaimInfo['permitOverallTotal']       = number_format($claimCertInfo['permitOverallTotal'], 2, '.', ',');

        $progressClaimInfo['advancePaymentPreviousClaim']  = "( " . number_format($claimCertInfo['advancePaymentPreviousClaim'], 2, '.', ',') . " )";
        $progressClaimInfo['depositPreviousClaim']         = "( " . number_format($claimCertInfo['depositPreviousClaim'], 2, '.', ',') . " )";
        $progressClaimInfo['materialOnSitePreviousClaim']  = "( " . number_format($claimCertInfo['materialOnSitePreviousClaim'], 2, '.', ',') . " )";
        $progressClaimInfo['kskPreviousClaim']             = number_format($claimCertInfo['kskPreviousClaim'], 2, '.', ',');
        $progressClaimInfo['wobMCPreviousClaim']           = number_format($claimCertInfo['wobMCPreviousClaim'], 2, '.', ',');
        $progressClaimInfo['debitCreditNotePreviousClaim'] = number_format($claimCertInfo['debitCreditNotePreviousClaim'], 2, '.', ',');

        $progressClaimInfo['debitCreditNoteBreakdownPreviousClaim'] = $claimCertInfo['debitCreditNoteBreakdownPreviousClaim'];

        $progressClaimInfo['pobPreviousClaim']          = "( " . number_format($claimCertInfo['pobPreviousClaim'], 2, '.', ',') . " )";
        $progressClaimInfo['wobPreviousClaim']          = number_format($claimCertInfo['wobPreviousClaim'], 2, '.', ',');
        $progressClaimInfo['penaltyPreviousClaim']      = number_format($claimCertInfo['penaltyPreviousClaim'], 2, '.', ',');
        $progressClaimInfo['waterDepositPreviousClaim'] = number_format($claimCertInfo['waterDepositPreviousClaim'], 2, '.', ',');
        $progressClaimInfo['permitPreviousClaim']       = "( " . number_format($claimCertInfo['permitPreviousClaim'], 2, '.', ',') . " )";

        $progressClaimInfo['advancePaymentThisClaim']  = number_format($claimCertInfo['advancePaymentThisClaim'], 2, '.', ',');
        $progressClaimInfo['depositThisClaim']         = number_format($claimCertInfo['depositThisClaim'], 2, '.', ',');
        $progressClaimInfo['materialOnSiteThisClaim']  = number_format($claimCertInfo['materialOnSiteThisClaim'], 2, '.', ',');
        $progressClaimInfo['kskThisClaim']             = number_format($claimCertInfo['kskThisClaim'], 2, '.', ',');
        $progressClaimInfo['wobMCThisClaim']           = number_format($claimCertInfo['wobMCThisClaim'], 2, '.', ',');
        $progressClaimInfo['debitCreditNoteThisClaim'] = number_format($claimCertInfo['debitCreditNoteThisClaim'], 2, '.', ',');

        $progressClaimInfo['advancePaymentOverallTotalFormatB']  = number_format($claimCertInfo['advancePaymentOverallTotalFormatB'], 2, '.', ',');
        $progressClaimInfo['advancePaymentThisClaimFormatB']  = number_format($claimCertInfo['advancePaymentThisClaimFormatB'], 2, '.', ',');
        $progressClaimInfo['advancePaymentPreviousClaimFormatB']  = number_format($claimCertInfo['advancePaymentPreviousClaimFormatB'], 2, '.', ',');

        $progressClaimInfo['advancePaymentRecoupmentOverallTotal']   = "( " . number_format($claimCertInfo['advancePaymentRecoupmentOverallTotal'], 2, '.', ',') . " )";
        $progressClaimInfo['advancePaymentRecoupmentPreviousClaim']  = "( " . number_format($claimCertInfo['advancePaymentRecoupmentPreviousClaim'], 2, '.', ',') . " )";
        $progressClaimInfo['advancePaymentRecoupmentThisClaim']      = "( " . number_format($claimCertInfo['advancePaymentRecoupmentThisClaim'], 2, '.', ',') . " )";

        $progressClaimInfo['debitCreditNoteBreakdownThisClaim'] = $claimCertInfo['debitCreditNoteBreakdownThisClaim'];

        $progressClaimInfo['pobThisClaim']          = number_format($claimCertInfo['pobThisClaim'], 2, '.', ',');
        $progressClaimInfo['wobThisClaim']          = number_format($claimCertInfo['wobThisClaim'], 2, '.', ',');
        $progressClaimInfo['penaltyThisClaim']      = number_format($claimCertInfo['penaltyThisClaim'], 2, '.', ',');
        $progressClaimInfo['waterDepositThisClaim'] = number_format($claimCertInfo['waterDepositThisClaim'], 2, '.', ',');
        $progressClaimInfo['permitThisClaim']       = number_format($claimCertInfo['permitThisClaim'], 2, '.', ',');

        $progressClaimInfo['advancePaymentThisClaimAfterGST']  = number_format($claimCertInfo['advancePaymentThisClaimAfterGST'], 2, '.', ',');
        $progressClaimInfo['materialOnSiteThisClaimAfterGST']  = number_format($claimCertInfo['materialOnSiteThisClaimAfterGST'], 2, '.', ',');
        $progressClaimInfo['kskThisClaimAfterGST']             = number_format($claimCertInfo['kskThisClaimAfterGST'], 2, '.', ',');
        $progressClaimInfo['wobMCThisClaimAfterGST']           = number_format($claimCertInfo['wobMCThisClaimAfterGST'], 2, '.', ',');
        $progressClaimInfo['debitCreditNoteThisClaimAfterGST'] = number_format($claimCertInfo['debitCreditNoteThisClaimAfterGST'], 2, '.', ',');

        $progressClaimInfo['debitCreditNoteBreakdownThisClaimAfterGST'] = $claimCertInfo['debitCreditNoteBreakdownThisClaimAfterGST'];

        $progressClaimInfo['pobThisClaimAfterGST'] = number_format($claimCertInfo['pobThisClaimAfterGST'], 2, '.', ',');
        $progressClaimInfo['wobThisClaimAfterGST'] = number_format($claimCertInfo['wobThisClaimAfterGST'], 2, '.', ',');

        $progressClaimInfo['miscThisClaimSubTotal']              = number_format($claimCertInfo['miscThisClaimSubTotal'], 2, '.', ',');
        $progressClaimInfo['miscThisClaimAfterGSTSubTotal']      = number_format($claimCertInfo['miscThisClaimAfterGSTSubTotal'], 2, '.', ',');
        $progressClaimInfo['miscThisClaimOverallTotal']          = number_format($claimCertInfo['miscThisClaimOverallTotal'], 2, '.', ',');
        $progressClaimInfo['taxInvoiceBySubConSubTotal']         = number_format($claimCertInfo['taxInvoiceBySubConSubTotal'], 2, '.', ',');
        $progressClaimInfo['taxInvoiceBySubConAfterGSTSubTotal'] = number_format($claimCertInfo['taxInvoiceBySubConAfterGSTSubTotal'], 2, '.', ',');
        $progressClaimInfo['taxInvoiceBySubConOverallTotal']     = number_format($claimCertInfo['taxInvoiceBySubConOverallTotal'], 2, '.', ',');

        $progressClaimInfo['otherThisClaimSubTotal']         = number_format($claimCertInfo['otherThisClaimSubTotal'], 2, '.', ',');
        $progressClaimInfo['otherThisClaimAfterGSTSubTotal'] = number_format($claimCertInfo['otherThisClaimAfterGSTSubTotal'], 2, '.', ',');
        $progressClaimInfo['otherThisClaimOverallTotal']     = number_format($claimCertInfo['otherThisClaimOverallTotal'], 2, '.', ',');

        $progressClaimInfo['paymentOnBehalfThisClaimSubTotal']         = number_format($claimCertInfo['paymentOnBehalfThisClaimSubTotal'], 2, '.', ',');
        $progressClaimInfo['paymentOnBehalfThisClaimAfterGSTSubTotal'] = number_format($claimCertInfo['paymentOnBehalfThisClaimAfterGSTSubTotal'], 2, '.', ',');
        $progressClaimInfo['paymentOnBehalfThisClaimOverallTotal']     = number_format($claimCertInfo['paymentOnBehalfThisClaimOverallTotal'], 2, '.', ',');

        $progressClaimInfo['netPayableAmount']             = number_format($claimCertInfo['netPayableAmount'], 2, '.', ',');
        $progressClaimInfo['netPayableAmountGST']          = number_format($claimCertInfo['netPayableAmountGST'], 2, '.', ',');
        $progressClaimInfo['netPayableAmountOverallTotal'] = number_format($claimCertInfo['netPayableAmountOverallTotal'], 2, '.', ',');

        $letterOfAwardOptions                                      = [];
        $letterOfAwardOptions['retentionSumIncludeVO']             = $claimCertInfo['retentionSumIncludeVO'];
        $letterOfAwardOptions['retentionSumIncludeMaterialOnSite'] = $claimCertInfo['retentionSumIncludeMaterialOnSite'];


        $progressClaimInfo['miscThisClaimSubTotalRecoupment']         = number_format(($claimCertInfo['miscThisClaimSubTotalRecoupment'] - $claimCertInfo['advancePaymentRecoupmentThisClaim']), 2, '.', ',');
        $progressClaimInfo['taxInvoiceBySubConSubTotalRecoupment']    = number_format(($claimCertInfo['taxInvoiceBySubConSubTotalRecoupment'] - $claimCertInfo['advancePaymentRecoupmentThisClaim'] + $amountCertifiedTax), 2, '.', ',');
        $progressClaimInfo['netPayableAmountRecoupmentWithTax']       = number_format(($claimCertInfo['netPayableAmountRecoupmentWithTax'] - $claimCertInfo['advancePaymentRecoupmentThisClaim'] + $amountCertifiedTax), 2, '.', ',');

        $claimCertificatePrintSettings = $claimCertificatePrintSettings->toArray();

        if(!$this->isModuleEnabled(PostContractClaim::TYPE_ADVANCED_PAYMENT)) $claimCertificatePrintSettings['include_advance_payment'] = false;
        if(!$this->isModuleEnabled(PostContractClaim::TYPE_DEPOSIT)) $claimCertificatePrintSettings['include_deposit'] = false;
        if(!$this->isModuleEnabled(PostContractClaim::TYPE_POST_CONTRACT_CLAIM_MATERIAL_ON_SITE)) $claimCertificatePrintSettings['include_material_on_site'] = false;
        if(!$this->isModuleEnabled(PostContractClaim::TYPE_OUT_OF_CONTRACT_ITEM)) $claimCertificatePrintSettings['include_ksk'] = false;
        if(!$this->isModuleEnabled(PostContractClaim::TYPE_WORK_ON_BEHALF_BACK_CHARGE)) $claimCertificatePrintSettings['include_work_on_behalf_mc'] = false;
        if(!$this->isModuleEnabled(PostContractClaim::TYPE_DEBIT_CREDIT_NOTE)) $claimCertificatePrintSettings['include_debit_credit_note'] = false;
        if(!$this->isModuleEnabled(PostContractClaim::TYPE_WORK_ON_BEHALF)) $claimCertificatePrintSettings['include_work_on_behalf'] = false;
        if(!$this->isModuleEnabled(PostContractClaim::TYPE_PURCHASE_ON_BEHALF)) $claimCertificatePrintSettings['include_purchase_on_behalf'] = false;
        if(!$this->isModuleEnabled(PostContractClaim::TYPE_PENALTY)) $claimCertificatePrintSettings['include_penalty'] = false;
        if(!$this->isModuleEnabled(PostContractClaim::TYPE_WATER_DEPOSIT)) $claimCertificatePrintSettings['include_utility'] = false;
        if(!$this->isModuleEnabled(PostContractClaim::TYPE_PERMIT)) $claimCertificatePrintSettings['include_permit'] = false;

        $displaySectionB = false;
        $displaySectionC = false;
        $displaySectionD = false;

        $printSettingsSectionBColumns = [
            'include_advance_payment',
            'include_deposit',
            'include_material_on_site',
            'include_ksk',
            'include_work_on_behalf_mc'
        ];

        $printSettingsSectionCColumns = [
            'include_debit_credit_note',
            'include_work_on_behalf',
            'include_purchase_on_behalf',
            'include_penalty'
        ];

        $printSettingsSectionDColumns = [
            'include_utility',
            'include_permit'
        ];

        foreach($claimCertificatePrintSettings as $key => $value)
        {
            if( in_array($key, $printSettingsSectionBColumns) && $value )
            {
                $displaySectionB = true;
                continue;
            }

            if( in_array($key, $printSettingsSectionCColumns) && $value )
            {
                $displaySectionC = true;
                continue;
            }

            if( in_array($key, $printSettingsSectionDColumns) && $value )
            {
                $displaySectionD = true;
            }
        }

        $claimCertificatePrintSettings['display_section_b'] = $displaySectionB;
        $claimCertificatePrintSettings['display_section_c'] = $displaySectionC;
        $claimCertificatePrintSettings['display_section_d'] = $displaySectionD;

        unset(
            $claimCertificatePrintSettings['id'],
            $claimCertificatePrintSettings['post_contract_id'],
            $claimCertificatePrintSettings['created_at'],
            $claimCertificatePrintSettings['updated_at'],
            $claimCertificatePrintSettings['created_by'],
            $claimCertificatePrintSettings['updated_by']
        );

        $claimCertificatePrintSettings['request_for_variation_category_to_print'] = $rfvCategory ? $rfvCategory->name : null;

        return [
            'claimCertificateInfo'          => $claimCertificateInfo,
            'progressClaimInfo'             => $progressClaimInfo,
            'letterOfAwardOptions'          => $letterOfAwardOptions,
            'claimCertificatePrintSettings' => $claimCertificatePrintSettings
        ];
    }

    public function executeGetInvoiceInformation(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $claimCertificate = Doctrine_Core::getTable('ClaimCertificate')->find(intval($request->getParameter('claimCertificateId')))
        );

        $invoiceInformation = null;

        $form = new ClaimCertificateInvoiceForm();

        if($claimCertificate->Invoice->exists())
        {
            $invoiceInformation['invoice_date']   = $claimCertificate->Invoice->invoice_date;
            $invoiceInformation['invoice_number'] = $claimCertificate->Invoice->invoice_number;
            $invoiceInformation['post_month']     = $claimCertificate->Invoice->post_month;
        }

        return $this->renderJson([
            'invoiceInformation' => $invoiceInformation,
            '_csrf_token'        => $form->getCSRFToken(),
        ]);
    }

    public function executeUpdateInvoiceInformation(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $claimCertificate = Doctrine_Core::getTable('ClaimCertificate')->find(intval($request->getParameter('claim_certificate_invoice')['claimCertificateId']))
        );

        $success = false;
        $errors  = null;

        $form = new ClaimCertificateInvoiceForm(null, ['userId' => $this->getUser()->getGuardUser()->id]);

        if ($this->isFormValid($request, $form))
        {
            $form->save();

            $success = true;
        }
        else
        {
            $errors = $form->getErrors();
        }

        return $this->renderJson(array(
            'success' => $success,
            'errors'  => $errors,
        ));
    }

    public function executeGetEnabledPostContractClaimModules(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest());

        $data = [
            PostContractClaim::TYPE_ADVANCED_PAYMENT                     => $this->isModuleEnabled(PostContractClaim::TYPE_ADVANCED_PAYMENT),
            PostContractClaim::TYPE_DEPOSIT                              => $this->isModuleEnabled(PostContractClaim::TYPE_DEPOSIT),
            PostContractClaim::TYPE_POST_CONTRACT_CLAIM_MATERIAL_ON_SITE => $this->isModuleEnabled(PostContractClaim::TYPE_POST_CONTRACT_CLAIM_MATERIAL_ON_SITE),
            PostContractClaim::TYPE_OUT_OF_CONTRACT_ITEM                 => $this->isModuleEnabled(PostContractClaim::TYPE_OUT_OF_CONTRACT_ITEM),
            PostContractClaim::TYPE_WORK_ON_BEHALF_BACK_CHARGE           => $this->isModuleEnabled(PostContractClaim::TYPE_WORK_ON_BEHALF_BACK_CHARGE),
            PostContractClaim::TYPE_DEBIT_CREDIT_NOTE                    => $this->isModuleEnabled(PostContractClaim::TYPE_DEBIT_CREDIT_NOTE),
            PostContractClaim::TYPE_WORK_ON_BEHALF                       => $this->isModuleEnabled(PostContractClaim::TYPE_WORK_ON_BEHALF),
            PostContractClaim::TYPE_PURCHASE_ON_BEHALF                   => $this->isModuleEnabled(PostContractClaim::TYPE_PURCHASE_ON_BEHALF),
            PostContractClaim::TYPE_PENALTY                              => $this->isModuleEnabled(PostContractClaim::TYPE_PENALTY),
            PostContractClaim::TYPE_WATER_DEPOSIT                        => $this->isModuleEnabled(PostContractClaim::TYPE_WATER_DEPOSIT),
            PostContractClaim::TYPE_PERMIT                               => $this->isModuleEnabled(PostContractClaim::TYPE_PERMIT),
        ];

        return $this->renderJson([
            'modules' => $data
        ]);
    }

    protected function setUpClaimCertificatePDFGenerator(ClaimCertificate $claimCertificate)
    {
        $claimCertificatePrintSettings = $claimCertificate->PostContractClaimRevision->PostContract->ClaimCertificatePrintSetting;

        $claimCertificatePrintSettings = $claimCertificatePrintSettings->toArray();

        if(!$this->isModuleEnabled(PostContractClaim::TYPE_ADVANCED_PAYMENT)) $claimCertificatePrintSettings['include_advance_payment'] = false;
        if(!$this->isModuleEnabled(PostContractClaim::TYPE_DEPOSIT)) $claimCertificatePrintSettings['include_deposit'] = false;
        if(!$this->isModuleEnabled(PostContractClaim::TYPE_POST_CONTRACT_CLAIM_MATERIAL_ON_SITE)) $claimCertificatePrintSettings['include_material_on_site'] = false;
        if(!$this->isModuleEnabled(PostContractClaim::TYPE_OUT_OF_CONTRACT_ITEM)) $claimCertificatePrintSettings['include_ksk'] = false;
        if(!$this->isModuleEnabled(PostContractClaim::TYPE_WORK_ON_BEHALF_BACK_CHARGE)) $claimCertificatePrintSettings['include_work_on_behalf_mc'] = false;
        if(!$this->isModuleEnabled(PostContractClaim::TYPE_DEBIT_CREDIT_NOTE)) $claimCertificatePrintSettings['include_debit_credit_note'] = false;
        if(!$this->isModuleEnabled(PostContractClaim::TYPE_WORK_ON_BEHALF)) $claimCertificatePrintSettings['include_work_on_behalf'] = false;
        if(!$this->isModuleEnabled(PostContractClaim::TYPE_PURCHASE_ON_BEHALF)) $claimCertificatePrintSettings['include_purchase_on_behalf'] = false;
        if(!$this->isModuleEnabled(PostContractClaim::TYPE_PENALTY)) $claimCertificatePrintSettings['include_penalty'] = false;
        if(!$this->isModuleEnabled(PostContractClaim::TYPE_WATER_DEPOSIT)) $claimCertificatePrintSettings['include_utility'] = false;
        if(!$this->isModuleEnabled(PostContractClaim::TYPE_PERMIT)) $claimCertificatePrintSettings['include_permit'] = false;

        $claimCertInfo = $claimCertificate->getClaimCertInfo($claimCertificatePrintSettings['certificate_print_format'] == ClaimCertificatePrintSetting::CERTIFICATE_INFO_FORMAT_NSC);

        $claimCertInfo['tax_percentage']  = $claimCertificate->tax_percentage;

        $project = $claimCertificate->PostContractClaimRevision->PostContract->ProjectStructure;

        $latestClaimRevision                             = $project->PostContract->getLatestApprovedClaimRevision();
        $latestApprovedClaimCertificate                  = $this->getPreviousClaimCertificate($project, $latestClaimRevision->version);
        $claimCertInfo['previous_tax_percentage']        = $latestApprovedClaimCertificate ? $latestApprovedClaimCertificate->tax_percentage: 0;

        $requestForVariation = $project->MainInformation->getEProjectProject()->RequestForVariation;
        $addOmitTotal = $requestForVariation->accumulative_approved_rfv_amount + $requestForVariation->proposed_rfv_amount;
        $requestForVariationContractAndContingencySum = $project->MainInformation->getEProjectProject()->RequestForVariationContractAndContingencySum;

        $claimCertInfo['balanceOfContingency']  = number_format(($requestForVariationContractAndContingencySum->contingency_sum - $addOmitTotal), 2, '.', ',');

        $selectedRfvCategory     = Doctrine_Core::getTable('EProjectRequestForVariationCategory')->find($claimCertificatePrintSettings['request_for_variation_category_id_to_print']);
        $selectedRfvCategoryName = $selectedRfvCategory ? $selectedRfvCategory->name : null;

        $stylesheet = file_get_contents(sfConfig::get('sf_web_dir') . '/css/projectSummary.css');

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
            'margin-left'    => 20,
            'page-size'      => 'A4',
            'orientation'    => "Portrait"
        );

        $pdfGen = new WkHtmlToPdf($params);

        $layout = $this->getPartial('postContract/pageLayout', array(
            'title'      => 'Claim Certificate Print Info',
            'stylesheet' => $stylesheet
        ));

        $displaySectionB = false;
        $displaySectionC = false;
        $displaySectionD = false;

        $printSettingsSectionBColumns = [
            'include_advance_payment',
            'include_deposit',
            'include_material_on_site',
            'include_ksk',
            'include_work_on_behalf_mc'
        ];

        $printSettingsSectionCColumns = [
            'include_debit_credit_note',
            'include_work_on_behalf',
            'include_purchase_on_behalf',
            'include_penalty'
        ];

        $printSettingsSectionDColumns = [
            'include_utility',
            'include_permit'
        ];

        foreach($claimCertificatePrintSettings as $key => $value)
        {
            if(in_array($key, $printSettingsSectionBColumns) && $value)
            {
                $displaySectionB = true;
                continue;
            }

            if(in_array($key, $printSettingsSectionCColumns) && $value)
            {
                $displaySectionC = true;
                continue;
            }

            if(in_array($key, $printSettingsSectionDColumns) && $value)
            {
                $displaySectionD = true;
            }
        }

        $claimCertificatePrintSettings['display_section_b'] = $displaySectionB;
        $claimCertificatePrintSettings['display_section_c'] = $displaySectionC;
        $claimCertificatePrintSettings['display_section_d'] = $displaySectionD;

        unset(
            $claimCertificatePrintSettings['id'],
            $claimCertificatePrintSettings['post_contract_id'],
            $claimCertificatePrintSettings['created_at'],
            $claimCertificatePrintSettings['updated_at'],
            $claimCertificatePrintSettings['created_by'],
            $claimCertificatePrintSettings['updated_by']
        );

        switch($claimCertificatePrintSettings['certificate_print_format'])
        {
            case ClaimCertificatePrintSetting::CERTIFICATE_INFO_FORMAT_A:
                $pageLayout = 'postContract/claimCertificatePrintInfoFormatAPageLayout';
                break;
            case ClaimCertificatePrintSetting::CERTIFICATE_INFO_FORMAT_B:
                $pageLayout = 'postContract/claimCertificatePrintInfoFormatBPageLayout';
                break;
            case ClaimCertificatePrintSetting::CERTIFICATE_INFO_FORMAT_NSC:
                $pageLayout = 'postContract/claimCertificatePrintInfoFormatNscPageLayout';
                break;
            default:
                $pageLayout = 'postContract/claimCertificatePrintInfoPageLayout';
        }

        $subPackages = [];

        foreach($claimCertInfo['subPackages'] as $subPackage)
        {
            $subPackages[] = [
                'title'                             => $subPackage['title'],
                'cumulativeAmountCertified'         => number_format($subPackage['cumulativeAmountCertified'], 2),
                'cumulativePreviousAmountCertified' => number_format($subPackage['cumulativePreviousAmountCertified'], 2),
                'amountCertified'                   => number_format($subPackage['amountCertified'], 2),
                'amountCertifiedTaxAmount'          => number_format($subPackage['amountCertifiedTaxAmount'], 2),
                'amountCertifiedIncludingTax'       => number_format($subPackage['amountCertifiedIncludingTax'], 2),
            ];
        }

        $amountCertifiedTax                         = $claimCertInfo['amountCertified'] * $claimCertInfo["tax_percentage"] / 100;
        $previousAmountCertifiedTax                 = $claimCertInfo['cumulativePreviousAmountCertified'] * $claimCertInfo["previous_tax_percentage"] / 100;
        $amountCertifiedTaxPercentageLabel          = "Tax " . $claimCertInfo['tax_percentage'];
        $amountCertifiedPlusTax                     = $claimCertInfo['amountCertified'] + $amountCertifiedTax;
        $cumulativeAmountCertifiedPlusTax           = $this->getTotalAmountCertifiedWithTax($project);
        $cumulativePreviousAmountCertifiedPlusTax   = $cumulativeAmountCertifiedPlusTax - $amountCertifiedPlusTax;

        $advancePaymentOverallTotalFormatB  = $this->getCumulativeAdvancePaymentAmountByClaimRevision($project);
        $advancePaymentThisClaimFormatB     = $this->getAdvancePaymentAmountByClaimRevision($project);
        $advancePaymentPreviousClaimFormatB = $advancePaymentOverallTotalFormatB - $advancePaymentThisClaimFormatB;

        $currentSelectedRevision = $project->PostContract->getCurrentSelectedClaimRevision();
        $advancePaymentRecoupmentOverallTotal   = $project->getPostContractClaimUpToDateAmount(PostContractClaim::TYPE_ADVANCED_PAYMENT, $currentSelectedRevision);
        $advancePaymentRecoupmentThisClaim      = $this->getAdvancePaymentCurrentPayback($project);
        $advancePaymentRecoupmentPreviousClaim  = $advancePaymentRecoupmentOverallTotal - $advancePaymentRecoupmentThisClaim;

        $layout .= $this->getPartial($pageLayout, array(
            'claimCertificate'                                        => $claimCertificate,
            'claimCertificatePrintSettings'                           => $claimCertificatePrintSettings,
            'currencyCode'                                            => $claimCertInfo['currencyCode'],
            'companyName'                                             => $claimCertInfo['companyName'],
            'contractorName'                                          => $claimCertInfo['contractorName'],
            'contractorAddr'                                          => $claimCertInfo['contractorAddr'],
            'contractorTel'                                           => $claimCertInfo['contractorTel'],
            'fax'                                                     => $claimCertInfo['fax'],
            'contractorPIC'                                           => $claimCertInfo['contractorPIC'],
            'claimNo'                                                 => $claimCertInfo['claimNo'],
            'personInCharge'                                          => $claimCertInfo['personInCharge'],
            'remark'                                                  => $claimCertInfo['remark'],
            'subPackageTitle'                                         => $claimCertInfo['subPackageTitle'],
            'projectTitle'                                            => $claimCertInfo['projectTitle'],
            'projectCode'                                             => $claimCertInfo['projectCode'],
            'letterOfAwardNo'                                         => $claimCertInfo['letterOfAwardNo'],
            'reference'                                               => $claimCertInfo['reference'],
            'worksfromLA'                                             => $claimCertInfo['worksfromLA'],
            'completionPercentage'                                    => $claimCertInfo['completionPercentage'] . "%",
            'date'                                                    => $claimCertInfo['date'],
            'dueDate'                                                 => $claimCertInfo['dueDate'],
            'taxPercentage'                                           => $claimCertInfo['taxPercentage'],
            'billTotal'                                               => number_format($claimCertInfo['billTotal'], 2),
            'voTotal'                                                 => number_format($claimCertInfo['voTotal'], 2),
            'contractSum'                                             => number_format($claimCertInfo['contractSum'], 2),
            'billWorkDone'                                            => number_format($claimCertInfo['billWorkDone'], 2),
            'voWorkDone'                                              => number_format($claimCertInfo['voWorkDone'], 2),
            'billTotalWithTax'                                        => number_format(($claimCertInfo['billTotal'] + ($claimCertInfo['billTotal'] * $claimCertInfo["tax_percentage"] / 100)), 2, '.', ','),
            'voOverallTotalWithTax'                                   => number_format(($claimCertInfo['voTotal'] + ($claimCertInfo['voTotal'] * $claimCertInfo["tax_percentage"] / 100)), 2, '.', ','),
            'urn'                                                     => $claimCertInfo['projectCode'] . "/" . $claimCertInfo['claimNo'],
            'contractSumWithTax'                                      => number_format(($claimCertInfo['contractSum'] + ($claimCertInfo['contractSum'] * $claimCertInfo["tax_percentage"] / 100)), 2, '.', ','),
            'balanceOfContingency'                                    => $claimCertInfo['balanceOfContingency'],
            'requestForVariationWorkDone'                             => number_format($claimCertInfo['requestForVariationWorkDone'], 2),
            'materialOnSiteWorkDone'                                  => number_format($claimCertInfo['materialOnSiteWorkDone'], 2),
            'totalWorkDone'                                           => number_format($claimCertInfo['totalWorkDone'], 2),
            'cumulativeRetentionSum'                                  => number_format($claimCertInfo['cumulativeRetentionSum'], 2),
            'totalAmount'                                             => number_format($claimCertInfo['totalAmount'], 2),
            'totalAmountAfterGST'                                     => number_format($claimCertInfo['totalAmountAfterGST'], 2),
            'retentionTaxPercentage'                                  => number_format(round($claimCertInfo['retention_tax_percentage'], 2), 2) . '%',
            'currentReleaseRetentionAmount'                           => number_format($claimCertInfo['currentReleaseRetentionAmount'], 2, '.', ','),
            'releaseRetentionAmountAfterGST'                          => number_format($claimCertInfo['releaseRetentionAmountAfterGST'], 2),
            'amountCertified'                                         => number_format($claimCertInfo['amountCertified'], 2),
            'amountCertifiedTaxAmount'                                => number_format($claimCertInfo['amountCertifiedTaxAmount'], 2),
            'amountCertifiedIncludingTax'                             => number_format($claimCertInfo['amountCertifiedIncludingTax'], 2),
            'amountCertifiedTax'                                      => number_format($amountCertifiedTax, 2, '.', ','),
            'amountCertifiedTaxPercentageLabel'                       => $amountCertifiedTaxPercentageLabel,
            'amountCertifiedPlusTax'                                  => number_format(round($amountCertifiedPlusTax, 2), 2, '.', ','),
            'cumulativeAmountCertifiedPlusTax'                        => number_format(round($cumulativeAmountCertifiedPlusTax, 2), 2, '.', ','),
            'cumulativePreviousAmountCertifiedPlusTax'                => number_format(round($cumulativePreviousAmountCertifiedPlusTax, 2), 2, '.', ','),
            'advancePaymentOverallTotal'                              => number_format($claimCertInfo['advancePaymentOverallTotal'], 2),
            'advancePaymentPreviousClaim'                             => number_format($claimCertInfo['advancePaymentPreviousClaim'], 2),
            'advancePaymentThisClaim'                                 => number_format($claimCertInfo['advancePaymentThisClaim'], 2),
            'advancePaymentOverallTotalFormatB'                       => number_format($advancePaymentOverallTotalFormatB, 2),
            'advancePaymentPreviousClaimFormatB'                      => number_format($advancePaymentPreviousClaimFormatB, 2),
            'advancePaymentThisClaimFormatB'                          => number_format($advancePaymentThisClaimFormatB, 2), 
            'advancePaymentRecoupmentOverallTotal'                    => number_format($advancePaymentRecoupmentOverallTotal, 2),
            'advancePaymentRecoupmentPreviousClaim'                   => number_format($advancePaymentRecoupmentPreviousClaim, 2),
            'advancePaymentRecoupmentThisClaim'                       => number_format($advancePaymentRecoupmentThisClaim, 2),
            'advancePaymentThisClaimAfterGST'                         => number_format($claimCertInfo['advancePaymentThisClaimAfterGST'], 2),
            'depositOverallTotal'                                     => number_format($claimCertInfo['depositOverallTotal'], 2),
            'depositPreviousClaim'                                    => number_format($claimCertInfo['depositPreviousClaim'], 2),
            'depositThisClaim'                                        => number_format($claimCertInfo['depositThisClaim'], 2),
            'materialOnSiteOverallTotal'                              => number_format($claimCertInfo['materialOnSiteOverallTotal'], 2),
            'materialOnSitePreviousClaim'                             => number_format($claimCertInfo['materialOnSitePreviousClaim'], 2),
            'materialOnSiteThisClaim'                                 => number_format($claimCertInfo['materialOnSiteThisClaim'], 2),
            'materialOnSiteThisClaimAfterGST'                         => number_format($claimCertInfo['materialOnSiteThisClaimAfterGST'], 2),
            'kskOverallTotal'                                         => number_format($claimCertInfo['kskOverallTotal'], 2),
            'kskPreviousClaim'                                        => number_format($claimCertInfo['kskPreviousClaim'], 2),
            'kskThisClaim'                                            => number_format($claimCertInfo['kskThisClaim'], 2),
            'kskThisClaimAfterGST'                                    => number_format($claimCertInfo['kskThisClaimAfterGST'], 2),
            'wobMCOverallTotal'                                       => number_format($claimCertInfo['wobMCOverallTotal'], 2),
            'wobMCPreviousClaim'                                      => number_format($claimCertInfo['wobMCPreviousClaim'], 2),
            'wobMCThisClaim'                                          => number_format($claimCertInfo['wobMCThisClaim'], 2),
            'wobMCThisClaimAfterGST'                                  => number_format($claimCertInfo['wobMCThisClaimAfterGST'], 2),
            'miscThisClaimSubTotal'                                   => number_format($claimCertInfo['miscThisClaimSubTotal'], 2),
            'miscThisClaimSubTotalRecoupment'                         => number_format(($claimCertInfo['miscThisClaimSubTotalRecoupment'] - $advancePaymentRecoupmentThisClaim), 2),
            'miscThisClaimAfterGSTSubTotal'                           => number_format($claimCertInfo['miscThisClaimAfterGSTSubTotal'], 2),
            'miscThisClaimOverallTotal'                               => number_format($claimCertInfo['miscThisClaimOverallTotal'], 2),
            'taxInvoiceBySubConSubTotal'                              => number_format($claimCertInfo['taxInvoiceBySubConSubTotal'], 2),
            'taxInvoiceBySubConSubTotalRecoupment'                    => number_format(($claimCertInfo['taxInvoiceBySubConSubTotalRecoupment'] - $advancePaymentRecoupmentThisClaim + $amountCertifiedTax), 2, '.', ','),
            'taxInvoiceBySubConAfterGSTSubTotal'                      => number_format($claimCertInfo['taxInvoiceBySubConAfterGSTSubTotal'], 2),
            'taxInvoiceBySubConOverallTotal'                          => number_format($claimCertInfo['taxInvoiceBySubConOverallTotal'], 2),
            'debitCreditNoteOverallTotal'                             => number_format($claimCertInfo['debitCreditNoteOverallTotal'], 2),
            'debitCreditNotePreviousClaim'                            => number_format($claimCertInfo['debitCreditNotePreviousClaim'], 2),
            'debitCreditNoteThisClaim'                                => number_format($claimCertInfo['debitCreditNoteThisClaim'], 2),
            'debitCreditNoteThisClaimAfterGST'                        => number_format($claimCertInfo['debitCreditNoteThisClaimAfterGST'], 2),
            'debitCreditNoteBreakdownOverallTotal'                    => $claimCertInfo['debitCreditNoteBreakdownOverallTotal'],
            'debitCreditNoteBreakdownPreviousClaim'                   => $claimCertInfo['debitCreditNoteBreakdownPreviousClaim'],
            'debitCreditNoteBreakdownThisClaim'                       => $claimCertInfo['debitCreditNoteBreakdownThisClaim'],
            'debitCreditNoteBreakdownThisClaimAfterGST'               => $claimCertInfo['debitCreditNoteBreakdownThisClaimAfterGST'],
            'pobOverallTotal'                                         => number_format($claimCertInfo['pobOverallTotal'], 2),
            'pobPreviousClaim'                                        => number_format($claimCertInfo['pobPreviousClaim'], 2),
            'pobThisClaim'                                            => number_format($claimCertInfo['pobThisClaim'], 2),
            'pobThisClaimAfterGST'                                    => number_format($claimCertInfo['pobThisClaimAfterGST'], 2),
            'wobOverallTotal'                                         => number_format($claimCertInfo['wobOverallTotal'], 2),
            'wobPreviousClaim'                                        => number_format($claimCertInfo['wobPreviousClaim'], 2),
            'wobThisClaim'                                            => number_format($claimCertInfo['wobThisClaim'], 2),
            'wobThisClaimAfterGST'                                    => number_format($claimCertInfo['wobThisClaimAfterGST'], 2),
            'penaltyOverallTotal'                                     => number_format($claimCertInfo['penaltyOverallTotal'], 2),
            'penaltyPreviousClaim'                                    => number_format($claimCertInfo['penaltyPreviousClaim'], 2),
            'penaltyThisClaim'                                        => number_format($claimCertInfo['penaltyThisClaim'], 2),
            'otherThisClaimSubTotal'                                  => number_format($claimCertInfo['otherThisClaimSubTotal'], 2),
            'otherThisClaimAfterGSTSubTotal'                          => number_format($claimCertInfo['otherThisClaimAfterGSTSubTotal'], 2),
            'otherThisClaimOverallTotal'                              => number_format($claimCertInfo['otherThisClaimOverallTotal'], 2),
            'waterDepositOverallTotal'                                => number_format($claimCertInfo['waterDepositOverallTotal'], 2),
            'waterDepositPreviousClaim'                               => number_format($claimCertInfo['waterDepositPreviousClaim'], 2),
            'waterDepositThisClaim'                                   => number_format($claimCertInfo['waterDepositThisClaim'], 2),
            'permitOverallTotal'                                      => number_format($claimCertInfo['permitOverallTotal'], 2),
            'permitPreviousClaim'                                     => number_format($claimCertInfo['permitPreviousClaim'], 2),
            'permitThisClaim'                                         => number_format($claimCertInfo['permitThisClaim'], 2),
            'paymentOnBehalfThisClaimSubTotal'                        => number_format($claimCertInfo['paymentOnBehalfThisClaimSubTotal'], 2),
            'paymentOnBehalfThisClaimAfterGSTSubTotal'                => number_format($claimCertInfo['paymentOnBehalfThisClaimAfterGSTSubTotal'], 2),
            'paymentOnBehalfThisClaimOverallTotal'                    => number_format($claimCertInfo['paymentOnBehalfThisClaimOverallTotal'], 2),
            'netPayableAmount'                                        => number_format($claimCertInfo['netPayableAmount'], 2),
            'netPayableAmountRecoupmentWithTax'                       => number_format(($claimCertInfo['netPayableAmountRecoupmentWithTax'] - $advancePaymentRecoupmentThisClaim + $amountCertifiedTax), 2, '.', ','),
            'netPayableAmountGST'                                     => number_format($claimCertInfo['netPayableAmountGST'], 2),
            'netPayableAmountOverallTotal'                            => number_format($claimCertInfo['netPayableAmountOverallTotal'], 2),
            'retentionSumIncludeVO'                                   => $claimCertInfo['retentionSumIncludeVO'],
            'retentionSumIncludeMaterialOnSite'                       => $claimCertInfo['retentionSumIncludeMaterialOnSite'],
            'previousBillClaimWorkDone'                               => number_format($claimCertInfo['previousBillClaimWorkDone'], 2),
            'currentBillClaimWorkDone'                                => number_format($claimCertInfo['currentBillClaimWorkDone'], 2),
            'previousCumulativeVoWorkDone'                            => number_format($claimCertInfo['previousCumulativeVoWorkDone'], 2),
            'currentVoWorkDone'                                       => number_format($claimCertInfo['currentVoWorkDone'], 2),
            'previousCumulativeRequestForVariationWorkDone'           => number_format($claimCertInfo['previousCumulativeRequestForVariationWorkDone'], 2),
            'currentRequestForVariationWorkDone'                      => number_format($claimCertInfo['currentRequestForVariationWorkDone'], 2),
            'showRequestForVariationWorkDone'                         => $claimCertInfo['showRequestForVariationWorkDone'],
            'previousTotalWorkDone'                                   => number_format($claimCertInfo['previousTotalWorkDone'], 2),
            'currentTotalWorkDone'                                    => number_format($claimCertInfo['currentTotalWorkDone'], 2),
            'previousCumulativeRetentionSum'                          => number_format($claimCertInfo['previousCumulativeRetentionSum'], 2),
            'currentRetentionSum'                                     => number_format($claimCertInfo['currentRetentionSum'], 2),
            'previousCumulativeReleasedRetentionAmount'               => number_format($claimCertInfo['previousCumulativeReleasedRetentionAmount'], 2),
            'cumulativeReleasedRetentionAmount'                       => number_format($claimCertInfo['cumulativeReleasedRetentionAmount'], 2),
            'cumulativeTotalRetention'                                => number_format($claimCertInfo['cumulativeTotalRetention'], 2),
            'previousCumulativeTotalRetention'                        => number_format($claimCertInfo['previousCumulativeTotalRetention'], 2),
            'currentTotalRetention'                                   => number_format($claimCertInfo['currentTotalRetention'], 2),
            'cumulativeAmountCertified'                               => number_format($claimCertInfo['cumulativeAmountCertified'], 2),
            'cumulativePreviousAmountCertified'                       => number_format($claimCertInfo['cumulativePreviousAmountCertified'], 2),
            'cumulativeAmountGSTAmount'                               => number_format($claimCertInfo['cumulativeAmountGSTAmount'], 2),
            'cumulativeTotalRetentionWithoutCurrentClaimRelease'      => number_format($claimCertInfo['cumulativeTotalRetentionWithoutCurrentClaimRelease'], 2),
            'cumulativeMaterialOnSiteWorkDone'                        => number_format($claimCertInfo['cumulativeMaterialOnSiteWorkDone'], 2),
            'previousCumulativeMaterialOnSiteWorkDone'                => number_format($claimCertInfo['previousCumulativeMaterialOnSiteWorkDone'], 2),
            'currentMaterialOnSiteWorkDone'                           => number_format($claimCertInfo['currentMaterialOnSiteWorkDone'], 2),
            'subPackages'                                             => $subPackages,
            'projectAndSubPackagesCurrentAmountCertified'             => number_format($claimCertInfo['projectAndSubPackagesCurrentAmountCertified'], 2),
            'projectAndSubPackagesCurrentAmountCertifiedIncludingTax' => number_format($claimCertInfo['projectAndSubPackagesCurrentAmountCertifiedIncludingTax'], 2),
            'selectedRfvCategoryName'                                 => $selectedRfvCategoryName,
            'voWorkDoneForSelectedRfvCategory'                        => number_format($claimCertInfo['voWorkDoneForSelectedRfvCategory'], 2),
            'previousVoWorkDoneForSelectedRfvCategory'                => number_format($claimCertInfo['previousVoWorkDoneForSelectedRfvCategory'], 2),
            'currentVoWorkDoneForSelectedRfvCategory'                 => number_format($claimCertInfo['currentVoWorkDoneForSelectedRfvCategory'], 2),
        ));

        $pdfGen->addPage($layout);

        return $pdfGen;
    }

    public function executePrintClaimCertificate(sfWebRequest $request)
    {
        sfConfig::set('sf_web_debug', false);

        $this->forward404Unless(
            $claimCertificate = Doctrine_Core::getTable('ClaimCertificate')->find(intval($request->getParameter('claimCertificateId'))) and $project = ProjectStructureTable::getInstance()->find($request->getParameter('pid')) and $project->type == ProjectStructure::TYPE_ROOT
        );

        $pdfGen = $this->setUpClaimCertificatePDFGenerator($claimCertificate);
       
        // ... send to client as file download
        return $pdfGen->send();
    }

    public function executeSavePrintedClaimCertificate(sfWebRequest $request)
    {
        sfConfig::set('sf_web_debug', false);

        $this->forward404Unless(
            $claimCertificate = Doctrine_Core::getTable('ClaimCertificate')->find(intval($request->getParameter('claimCertificateId'))) and $project = ProjectStructureTable::getInstance()->find($request->getParameter('pid')) and $project->type == ProjectStructure::TYPE_ROOT
        );

        $success    = false;
        $pathToFile = null;
        $errorMsg   = null;

        try
        {
            $directory = sfConfig::get('app_eproject_shared_folder') . "/" . ClaimCertificate::SHARED_FOLDER_DIRECTORY;

            if( ! file_exists($directory) ) mkdir($directory, 0755, true);

            $pathToFile = "{$directory}/{$project->id}-{$claimCertificate->id}";

            $pdfGen = $this->setUpClaimCertificatePDFGenerator($claimCertificate);

            $success = $pdfGen->saveAs($pathToFile);
        }
        catch(Exception $exception)
        {
            $errorMsg = $exception->getMessage();
        }

        return $this->renderJson(array(
            'success'    => $success,
            'errorMsg'   => $errorMsg,
            'pathToFile' => $pathToFile,
        ));
    }

    public function executeExportExcel(sfWebRequest $request)
    {
        sfConfig::set('sf_web_debug', false);

        $request->checkCSRFProtection();

        $this->forward404Unless(
            $claimCertificate = Doctrine_Core::getTable('ClaimCertificate')->find(intval($request->getParameter('claimCertificateId'))) and
            strlen($request->getParameter('filename')) > 0
        );

        $phpExcel = new sfPostContractClaimCertificateReportGenerator($claimCertificate);

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

    public function executeExportAccounting(sfWebRequest $request)
    {
        sfConfig::set('sf_web_debug', false);

        $request->checkCSRFProtection();

        $this->forward404Unless(
            $claimCertificate = Doctrine_Core::getTable('ClaimCertificate')->find(intval($request->getParameter('claimCertificateId'))) and
            $projectStructure = Doctrine_Core::getTable('ProjectStructure')->find(intval($request->getParameter('pid'))) and
            strlen($request->getParameter('filename')) > 0
        );

        $phpExcel = new sfPostContractAccountingReportGenerator($projectStructure, $claimCertificate);

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

    public function executeGetClaimCertificatePayments(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $claimCertificate = Doctrine_Core::getTable('ClaimCertificate')->find(intval($request->getParameter('cid')))
        );

        $pdo = $claimCertificate->getTable()->getConnection()->getDbh();

        $stmt = $pdo->prepare("SELECT p.id, p.amount, p.remarks, p.created_at
        FROM ".ClaimCertificatePaymentLogTable::getInstance()->getTableName()." p
        WHERE p.claim_certificate_id = :claimCertificateId ORDER BY p.id ASC");

        $stmt->execute(array( 'claimCertificateId' => $claimCertificate->id ));

        $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $form = new BaseForm();

        foreach ( $records as $key => $record )
        {
            $records[$key]['created_at']  = !empty($record['created_at']) ? date("d/m/Y", strtotime($record['created_at'])) : "";
            $records[$key]['_csrf_token'] = $form->getCSRFToken();
        }

        array_push($records, array(
            'id'          => Constants::GRID_LAST_ROW,
            'amount'      => 0,
            'remarks'     => "",
            'created_at'  => "",
            '_csrf_token' => $form->getCSRFToken()
        ));

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $records
        ));
    }

    public function executeClaimCertificatePaymentUpdate(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $claimCertificate = Doctrine_Core::getTable('ClaimCertificate')->find(intval($request->getParameter('cid')))
        );

        $errorMsg = null;
        $con      = $claimCertificate->getTable()->getConnection();

        try
        {
            $con->beginTransaction();

            $form = new BaseForm();

            if($request->getParameter("id") == Constants::GRID_LAST_ROW){
                $payment = new ClaimCertificatePaymentLog();
                $payment->claim_certificate_id = $claimCertificate->id;

                $defaultLastRow = array(
                    'id'          => Constants::GRID_LAST_ROW,
                    'amount'      => 0,
                    'remarks'     => "",
                    'created_at'  => "",
                    '_csrf_token' => $form->getCSRFToken()
                );

            }else{
                $this->forward404Unless(
                    $payment = Doctrine_Core::getTable('ClaimCertificatePaymentLog')->find(intval($request->getParameter('id')))
                );

                $defaultLastRow = null;
            }

            $fieldName = $request->getParameter('attr_name');

            if(strtolower($fieldName) == "amount")
            {
                $fieldValue = ( is_numeric($request->getParameter('val')) ) ? $request->getParameter('val') : 0;
            }
            else
            {
                $fieldValue = $request->getParameter('val');
            }

            $payment->{'set' . sfInflector::camelize($fieldName)}($fieldValue);

            $payment->save($con);

            $con->commit();

            $success = true;

            $items[] = array(
                'id'          => $payment->id,
                'amount'      => number_format($payment->amount, 2, '.', ''),
                'remarks'     => $payment->remarks,
                'created_at'  => date("d/m/Y", strtotime($payment->created_at)),
                '_csrf_token' => $form->getCSRFToken()
            );

            if($defaultLastRow)
            {
                array_push($items, $defaultLastRow);
            }
        }
        catch (Exception $e)
        {
            $con->rollback();

            $items = array();
            $errorMsg = $e->getMessage();
            $success  = false;
        }

        return $this->renderJson(array(
            'items'    => $items,
            'success'  => $success,
            'errorMsg' => $errorMsg
        ));
    }

    public function executeClaimCertificateSetSelectedRevision(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $claimCertificate = Doctrine_Core::getTable('ClaimCertificate')->find(intval($request->getParameter('id')))
        );

        $pdo = $claimCertificate->getTable()->getConnection()->getDbh();

        try
        {
            $selectedRevision = Doctrine_Core::getTable('PostContractClaimRevision')->find($claimCertificate->post_contract_claim_revision_id);

            $selectedRevision->setSelectedRevision();

            $stmt = $pdo->prepare("SELECT cert.id, rev.current_selected_revision, rev.id as revision_id
                FROM ".ClaimCertificateTable::getInstance()->getTableName()." cert
                JOIN ".PostContractClaimRevisionTable::getInstance()->getTableName()." rev ON rev.id = cert.post_contract_claim_revision_id
                WHERE rev.post_contract_id = :postContractId AND rev.deleted_at IS NULL ORDER BY rev.version ASC");

            $stmt->execute(array( 'postContractId' => $claimCertificate->PostContractClaimRevision->PostContract->id ));

            $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $success = true;
            $errorMsg = null;
        }
        catch (Exception $e)
        {
            $errorMsg = $e->getMessage();
            $success  = false;

            $records = array();
        }

        return $this->renderJson(array(
            'success' => $success,
            'errors' => $errorMsg,
            'items' => $records
        ));
    }

    public function executeGetPublishedType(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $project = Doctrine_Core::getTable('ProjectStructure')->find(intval($request->getParameter('pid'))) and
            $project->node->isRoot()
        );

        $success = false;

        $postContractInformation = $project->NewPostContractFormInformation;

        if($postContractInformation->id)
        {
            $success = true;
        }

        return $this->renderJson(array(
            'items' => $postContractInformation,
            'success' => $success
        )); 
    }

    public function executeGetCurrentViewingClaimRevision(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('id'))
        );

        $success = false;
        $revision = null;

        if($postContract = $project->PostContract)
        {
            $selectedRevision = PostContractClaimRevisionTable::getCurrentSelectedProjectRevision($postContract,true);
            $currentRevision = PostContractClaimRevisionTable::getCurrentProjectRevision($postContract,true);
            if($selectedRevision['id'] != $currentRevision['id']) $revision = $selectedRevision;
        }

        return $this->renderJson(array(
            'revision' => $revision,
            'success' => $success
        ));
    }
}