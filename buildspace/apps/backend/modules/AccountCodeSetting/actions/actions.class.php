<?php

/**
 * AccountCodeSetting actions.
 *
 * @package    buildspace
 * @subpackage AccountCodeSetting
 * @author     1337 developers
 * @version    SVN: $Id$
 */
class AccountCodeSettingActions extends BaseActions
{
    public function executeGetItemCodeSettingBreakdowns(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $project = Doctrine_Core::getTable('ProjectStructure')->find(intval($request->getParameter('projectStructureId'))) and
            $claimCertificate = Doctrine_Core::getTable('ClaimCertificate')->find(intval($request->getParameter('claimCertificateId')))
        );

        $success = false;
        $errors = null;
        $data['itemCodeSettings'] = [];
        $data['itemCodeSettingObjects'] = [];
        $data['_csrf_token'] = null;
        $data['objectIds'] = [];
        $data['itemCodeSettingIds'] = [];

        $form = new ItemCodeSettingObjectForm();
        $data['_csrf_token'] = $form->getCSRFToken();

        $currentViewingClaimRevision = $claimCertificate->PostContractClaimRevision;
        $accountCodeSetting = $project->MainInformation->getEProjectProject()->AccountCodeSetting;
        $itemCodeSettings = ItemCodeSetting::getItemCodeSettings($project);

        // item code settings descriptions
        foreach($itemCodeSettings as $itemCodeSetting)
        {
            $accountCode = Doctrine_Core::getTable('AccountCode')->find($itemCodeSetting['account_code_id']);

            array_push($data['itemCodeSettingIds'], $itemCodeSetting['id']);
            array_push($data['itemCodeSettings'], [
                'id'          => $itemCodeSetting['id'],
                'description' => $accountCode->description,
            ]);
        }
        
        // current revision bill claim amount grouped by bills (normal bills)
        $thisRevisionBillWorkDoneClaims = [];

        if($currentViewingClaimRevision->getPreviousClaimRevision())
        {
            $thisRevisionBillUpToDateClaims = PostContractTable::getUpToDateAmountGroupByBills($project, $currentViewingClaimRevision->toArray());
            $previousRevisionBillUpToDateClaims = PostContractTable::getUpToDateAmountGroupByBills($project, $currentViewingClaimRevision->getPreviousClaimRevision()->toArray());

            foreach($thisRevisionBillUpToDateClaims as $billId => $amount)
            {
                if(array_key_exists($billId, $previousRevisionBillUpToDateClaims))
                {
                    $thisRevisionBillWorkDoneClaims[$billId] = ($thisRevisionBillUpToDateClaims[$billId] - $previousRevisionBillUpToDateClaims[$billId]);
                }
                else
                {
                    $thisRevisionBillWorkDoneClaims[$billId] = $thisRevisionBillUpToDateClaims[$billId];
                }
            }
        }
        else
        {
            $thisRevisionBillWorkDoneClaims = PostContractTable::getUpToDateAmountGroupByBills($project, $currentViewingClaimRevision->toArray());
        }

        // bill breakdowns
        $bills = DoctrineQuery::create()
            ->select('s.id, s.title, s.type, s.level, t.type AS bill_type, t.status')
            ->from('ProjectStructure s')
            ->leftJoin('s.BillType t')
            ->where('s.lft >= ? AND s.rgt <= ?', array( $project->lft, $project->rgt ))
            ->andWhere('s.root_id = ?', $project->id)
            ->andWhere('s.type = ?', ProjectStructure::TYPE_BILL)
            ->addOrderBy('s.lft ASC')
            ->fetchArray();
        
        $pdo  = PostContractTable::getInstance()->getConnection()->getDbh();

        $stmt = $pdo->prepare("SELECT b.id AS bill_id, e.id
        FROM ".BillElementTable::getInstance()->getTableName()." e
        JOIN ".BillTypeTable::getInstance()->getTableName()." t ON t.project_structure_id = e.project_structure_id
        JOIN ".ProjectStructureTable::getInstance()->getTableName()." b ON t.project_structure_id = b.id
        JOIN ".ProjectStructureTable::getInstance()->getTableName()." p ON b.root_id = p.id
        WHERE p.id = ".$project->id." AND b.type = ".ProjectStructure::TYPE_BILL."
        AND t.type = ".BillType::TYPE_PRELIMINARY."
        AND b.lft  >= ".$project->lft." AND b.rgt <= ".$project->rgt."
        AND e.deleted_at IS NULL AND t.deleted_at IS NULL AND b.deleted_at IS NULL
        ORDER BY b.lft ASC");
        $stmt->execute();
        $prelimBillsElements = $stmt->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_ASSOC);

        $postContract = PostContractTable::getInstance()->findOneBy('project_structure_id', $project->id);

        $prelimBillsAmount = [];

        foreach($prelimBillsElements as $billId => $prelimBillElements)
        {
            list(
                $elementBillItems, $initialCostings, $timeBasedCostings, $prevTimeBasedCostings, $workBasedCostings, $prevWorkBasedCostings, $finalCostings, $includeInitialCostings, $includeFinalCostings
            ) = PostContractBillItemRateTable::getPrelimElementClaimCosting($currentViewingClaimRevision, $prelimBillElements);
            
            $prelimBillsAmount[$billId] = 0;

            foreach ( $elementBillItems as $elementId => $billItems )
            {
                foreach($billItems as $billItem)
                {
                    PreliminariesClaimTable::calculateClaimRates($currentViewingClaimRevision->toArray(), $billItem, $currentViewingClaimRevision->toArray(), $initialCostings, $finalCostings, $timeBasedCostings, $workBasedCostings, $prevTimeBasedCostings, $prevWorkBasedCostings, $includeInitialCostings, $includeFinalCostings);
                    $prelimBillsAmount[$billId] += $billItem['currentClaim-amount'];

                    unset( $billItem );
                }
            }

            unset($elementBillItems, $initialCostings, $timeBasedCostings, $prevTimeBasedCostings, $workBasedCostings, $prevWorkBasedCostings, $finalCostings, $includeInitialCostings, $includeFinalCostings);
        }

        foreach($bills as &$bill)
        {
            if($bill['bill_type'] == BillType::TYPE_PRELIMINARY)
            {
                $thisRevisionBillAmount = array_key_exists($bill['id'], $prelimBillsAmount) ? $prelimBillsAmount[$bill['id']] : 0.0;
            }
            else
            {
                $thisRevisionBillAmount = array_key_exists($bill['id'], $thisRevisionBillWorkDoneClaims) ? $thisRevisionBillWorkDoneClaims[ $bill['id'] ] : 0.0;
            }
            
            $temp = [];
            $temp['object_id'] = $bill['id'];
            $temp['objectType'] = ItemCodeSettingObject::TYPE_BILL;
            $temp['description'] = $bill['title'];
            $temp['currentClaim'] = number_format($thisRevisionBillAmount, 2, '.', ',');
            $temp['breakdowns'] = [];

            foreach($itemCodeSettings as $itemCodeSetting)
            {
                $itemCodeSettingObject = ItemCodeSettingObject::find($project, $bill['id'], ItemCodeSettingObject::TYPE_BILL);
                $itemCodeSettingObjectBreakdown = null;

                if(!$itemCodeSettingObject)
                {
                    $itemCodeSettingObject = ItemCodeSettingObject::create($project, $bill['id'], ItemCodeSettingObject::TYPE_BILL);
                }

                $itemCodeSettingObjectBreakdown = ItemCodeSettingObjectBreakdown::find($itemCodeSettingObject->id, $claimCertificate->id, $itemCodeSetting['id']);

                $accountCode = Doctrine_Core::getTable('AccountCode')->find($itemCodeSetting['account_code_id']);

                array_push($temp['breakdowns'], [
                    'item_code_setting_id'  => $itemCodeSetting['id'],
                    'breakdown_description' => $accountCode->description,
                    'amount'                => $itemCodeSettingObjectBreakdown ? $itemCodeSettingObjectBreakdown->amount : 0.0,
                ]);
            }

            array_push($data['objectIds'], $bill['id']);
            array_push($data['itemCodeSettingObjects'], $temp);
        }

        $voTemp = [];
        $voTemp['object_id'] = PostContractClaim::TYPE_VARIATION_ORDER;
        $voTemp['objectType'] = ItemCodeSettingObject::TYPE_VARIATION_ORDER;
        $voTemp['description'] = PostContractClaim::TYPE_VARIATION_ORDER_TEXT;
        $voTemp['currentClaim'] = number_format($currentViewingClaimRevision->ClaimCertificate->getClaimCertInfo()['currentVoWorkDone'], 2, '.', ',');
        $voTemp['breakdowns'] = [];

        foreach($itemCodeSettings as $itemCodeSetting)
        {
            $itemCodeSettingObject = ItemCodeSettingObject::find($project, PostContractClaim::TYPE_VARIATION_ORDER, ItemCodeSettingObject::TYPE_VARIATION_ORDER);
            $voItemCodeSettingObjectBreakdown = null;

            if(!$itemCodeSettingObject)
            {
                $itemCodeSettingObject = ItemCodeSettingObject::create($project, PostContractClaim::TYPE_VARIATION_ORDER, ItemCodeSettingObject::TYPE_VARIATION_ORDER);
            }

            $voItemCodeSettingObjectBreakdown = ItemCodeSettingObjectBreakdown::find($itemCodeSettingObject->id, $claimCertificate->id, $itemCodeSetting['id']);

            array_push($voTemp['breakdowns'], [
                'item_code_setting_id'  => $itemCodeSetting['id'],
                'breakdown_description' => $accountCode->description,
                'amount'                => $voItemCodeSettingObjectBreakdown ? $voItemCodeSettingObjectBreakdown->amount : 0.0,
            ]);
        }

        array_push($data['objectIds'], PostContractClaim::TYPE_VARIATION_ORDER);
        array_push($data['itemCodeSettingObjects'], $voTemp);

        // RFV Claim
        $rfvTemp = [];
        $rfvTemp['object_id'] = PostContractClaim::TYPE_REQUEST_FOR_VARIATION_CLAIM;
        $rfvTemp['objectType'] = ItemCodeSettingObject::TYPE_REQUEST_FOR_VARIATION_CLAIM;
        $rfvTemp['description'] = PostContractClaim::TYPE_REQUEST_FOR_VARIATION_CLAIM_TEXT;
        $rfvTemp['currentClaim'] = number_format($currentViewingClaimRevision->ClaimCertificate->getClaimCertInfo()['currentRequestForVariationWorkDone'], 2, '.', ',');
        $rfvTemp['breakdowns'] = [];

        foreach($itemCodeSettings as $itemCodeSetting)
        {
            $itemCodeSettingObject = ItemCodeSettingObject::find($project, PostContractClaim::TYPE_REQUEST_FOR_VARIATION_CLAIM, ItemCodeSettingObject::TYPE_REQUEST_FOR_VARIATION_CLAIM);
            $rfvItemCodeSettingObjectBreakdown = null;

            if(!$itemCodeSettingObject)
            {
                $itemCodeSettingObject = ItemCodeSettingObject::create($project, PostContractClaim::TYPE_REQUEST_FOR_VARIATION_CLAIM, ItemCodeSettingObject::TYPE_REQUEST_FOR_VARIATION_CLAIM);
            }

            $rfvItemCodeSettingObjectBreakdown = ItemCodeSettingObjectBreakdown::find($itemCodeSettingObject->id, $claimCertificate->id, $itemCodeSetting['id']);

            array_push($rfvTemp['breakdowns'], [
                'item_code_setting_id'  => $itemCodeSetting['id'],
                'breakdown_description' => $accountCode->description,
                'amount'                => $rfvItemCodeSettingObjectBreakdown ? $rfvItemCodeSettingObjectBreakdown->amount : 0.0,
            ]);
        }

        array_push($data['objectIds'], PostContractClaim::TYPE_REQUEST_FOR_VARIATION_CLAIM);
        array_push($data['itemCodeSettingObjects'], $rfvTemp);

        // Material-On-Site (if included when submitting Letter of Award)
        $newPostContractFormInformation = $project->NewPostContractFormInformation;
        $includesMaterialOnSite = LetterOfAwardRetentionSumModulesTable::isIncluded($newPostContractFormInformation->id, PostContractClaim::TYPE_POST_CONTRACT_CLAIM_MATERIAL_ON_SITE);

        if($includesMaterialOnSite)
        {
            $totalAmount = ItemCodeSetting::calculateMaterialOnSiteAmount($currentViewingClaimRevision->ClaimCertificate, $project);

            if($currentViewingClaimRevision->getPreviousClaimRevision())
            {
                $previousTotalAmount = ItemCodeSetting::calculateMaterialOnSiteAmount($currentViewingClaimRevision->getPreviousClaimRevision()->ClaimCertificate, $project);
                $totalAmount -= $previousTotalAmount;
            }

            $mosTemp = [];
            $mosTemp['object_id'] = PostContractClaim::TYPE_MATERIAL_ON_SITE;
            $mosTemp['objectType'] = ItemCodeSettingObject::TYPE_MATERIAL_ON_SITE;
            $mosTemp['description'] = PostContractClaim::TYPE_MATERIAL_ON_SITE_TEXT;
            $mosTemp['currentClaim'] = number_format($totalAmount, 2, '.', ',');
            $mosTemp['breakdowns'] = [];

            foreach($itemCodeSettings as $itemCodeSetting)
            {
                $itemCodeSettingObject = ItemCodeSettingObject::find($project, PostContractClaim::TYPE_MATERIAL_ON_SITE, ItemCodeSettingObject::TYPE_MATERIAL_ON_SITE);
                $mosItemCodeSettingObjectBreakdown = null;

                if(!$itemCodeSettingObject)
                {
                    $itemCodeSettingObject = ItemCodeSettingObject::create($project, PostContractClaim::TYPE_MATERIAL_ON_SITE, ItemCodeSettingObject::TYPE_MATERIAL_ON_SITE);
                }

                $mosItemCodeSettingObjectBreakdown = ItemCodeSettingObjectBreakdown::find($itemCodeSettingObject->id, $claimCertificate->id, $itemCodeSetting['id']);

                array_push($mosTemp['breakdowns'], [
                    'item_code_setting_id'  => $itemCodeSetting['id'],
                    'breakdown_description' => $accountCode->description,
                    'amount'                => $mosItemCodeSettingObjectBreakdown ? $mosItemCodeSettingObjectBreakdown->amount : 0.0,
                ]);
            }

            array_push($data['objectIds'], PostContractClaim::TYPE_MATERIAL_ON_SITE);
            array_push($data['itemCodeSettingObjects'], $mosTemp);
        }

        $success = true;

        return $this->renderJson([
            'data'    => $data,
            'success' => $success,
            'errors'  => $errors,
        ]);
    }

    public function executeSaveItemCodeSettingsBreakdown(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('item_code_setting_object')['projectStructureId']) and
            $claimCertificate = Doctrine_Core::getTable('ClaimCertificate')->find(intval($request->getParameter('item_code_setting_object')['claimCertificateId']))
        );

        $success = null;
        $errors = null;
        $form = new ItemCodeSettingObjectForm($project->ItemCodeSettingObject);

        if($this->isFormValid($request, $form))
        {
            $form->save();
            $errors = null;
            $success = true;
        }
        else
        {
            $errors = $form->getErrors();
            $success = false;
        }

        return $this->renderJson(array(
            'success' => $success,
            'errors' => $errors,
        ));
    }
}