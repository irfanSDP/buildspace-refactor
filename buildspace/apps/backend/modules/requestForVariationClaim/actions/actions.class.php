<?php

/**
 * requestForVariationClaim actions.
 *
 * @package    buildspace
 * @subpackage requestForVariationClaim
 * @author     1337 developers
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class requestForVariationClaimActions extends BaseActions
{
    public function executeGetRequestForVariationClaims(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('pid')) and
            $project->node->isRoot() and
            $project->PostContract->published_type == PostContract::PUBLISHED_TYPE_NEW and
            $project->MainInformation->eproject_origin_id
        );

        $pdo  = $project->getTable()->getConnection()->getDbh();

        $stmt = $pdo->prepare("SELECT vo.eproject_rfv_id, vo.id, vo.description, COALESCE(SUM(c.amount), 0) AS claim_amount
            FROM ".VariationOrderTable::getInstance()->getTableName()." vo
            LEFT JOIN ".VariationOrderItemTable::getInstance()->getTableName()." i ON vo.id = i.variation_order_id
            LEFT JOIN ".RequestForVariationItemClaimTable::getInstance()->getTableName()." c ON i.id = c.variation_order_item_id
            WHERE vo.project_structure_id = {$project->id}
            AND vo.eproject_rfv_id IS NOT NULL
            AND i.is_from_rfv IS TRUE
            AND vo.deleted_at IS NULL
            AND i.deleted_at IS NULL
            GROUP BY vo.id
            ORDER BY vo.priority ASC"
        );

        $stmt->execute();

        $variationOrders = $stmt->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_UNIQUE|PDO::FETCH_ASSOC);

        $stmt = $pdo->prepare("SELECT DISTINCT vo.id, r.version
        FROM ".VariationOrderTable::getInstance()->getTableName()." vo
        JOIN ".VariationOrderClaimCertificateTable::getInstance()->getTableName()." xref on xref.variation_order_id = vo.id
        JOIN ".ClaimCertificateTable::getInstance()->getTableName()." c on xref.claim_certificate_id = c.id
        JOIN ".PostContractClaimRevisionTable::getInstance()->getTableName()." r on c.post_contract_claim_revision_id = r.id
        WHERE vo.project_structure_id = {$project->id}
        AND vo.eproject_rfv_id IS NOT NULL
        AND vo.deleted_at IS NULL");

        $stmt->execute();

        $variationOrderClaimRevisions = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

        $eprojectRFVObj = new EProjectRequestForVariation();
        $eprojectPdo = $eprojectRFVObj->getTable()->getConnection()->getDbh();
        
        $stmt = $eprojectPdo->prepare("SELECT r.id, r.rfv_number, r.description
            FROM ".EProjectRequestForVariationTable::getInstance()->getTableName()." r
            WHERE r.project_id = {$project->MainInformation->eproject_origin_id}
            AND r.status = ".EProjectRequestForVariation::STATUS_APPROVED."
            ORDER BY r.rfv_number DESC"
        );

        $stmt->execute();

        $requestForVariations = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $form = new BaseForm();

        foreach($requestForVariations as $key => $requestForVariation)
        {
            if(array_key_exists($requestForVariation['id'], $variationOrders))
            {
                $requestForVariations[$key]['description']        = $variationOrders[$requestForVariation['id']]['description'];
                $requestForVariations[$key]['total_claim_amount'] = number_format($variationOrders[$requestForVariation['id']]['claim_amount'], 2, '.', '');
                $requestForVariations[$key]['claim_cert_number']  = array_key_exists($variationOrders[$requestForVariation['id']]['id'], $variationOrderClaimRevisions) ? $variationOrderClaimRevisions[$variationOrders[$requestForVariation['id']]['id']] : "";

                $variationOrders[$requestForVariation['id']] = null;
                unset($variationOrders[$requestForVariation['id']]);
            }
            else
            {
                $requestForVariations[$key]['description']        = $requestForVariation['description'];
                $requestForVariations[$key]['total_claim_amount'] = number_format(0, 2, '.', '');
                $requestForVariations[$key]['claim_cert_number']  = "";
            }


            $requestForVariations[$key]['_csrf_token'] = $form->getCSRFToken();
        }

        $variationOrders = null;
        unset($variationOrders);

        //default last row
        array_push($requestForVariations, [
            'id'                 => Constants::GRID_LAST_ROW,
            'rfv_number'         => '',
            'description'        => '',
            'claim_cert_number'  => '',
            'total_claim_amount' => 0,
            '_csrf_token'        => $form->getCSRFToken()
        ]);

        return $this->renderJson([
            'identifier' => 'id',
            'items'      => $requestForVariations
        ]);
    }

    public function executeGetRequestForVariationClaimItems(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $requestForVariation = Doctrine_Core::getTable('EProjectRequestForVariation')->find($request->getParameter('id')) and
            $requestForVariation->status == EProjectRequestForVariation::STATUS_APPROVED and
            $projectMainInformation = Doctrine_Core::getTable('ProjectMainInformation')->findOneByEProjectId($requestForVariation->project_id)
        );

        $project = $projectMainInformation->ProjectStructure;

        $pdo  = $project->getTable()->getConnection()->getDbh();
        $form = new BaseForm();

        $stmt = $pdo->prepare("SELECT x.variation_order_item_id, r.version, c.status
            FROM ".RequestForVariationItemClaimCertificateTable::getInstance()->getTableName()." x
            JOIN ".VariationOrderItemTable::getInstance()->getTableName()." i ON x.variation_order_item_id = i.id
            JOIN ".VariationOrderTable::getInstance()->getTableName()." vo ON i.variation_order_id = vo.id
            JOIN ".ClaimCertificateTable::getInstance()->getTableName()." c ON x.claim_certificate_id = c.id
            JOIN ".PostContractClaimRevisionTable::getInstance()->getTableName()." r ON c.post_contract_claim_revision_id = r.id
            JOIN ".PostContractTable::getInstance()->getTableName()." pc ON r.post_contract_id = pc.id AND pc.project_structure_id = vo.project_structure_id
            JOIN ".ProjectStructureTable::getInstance()->getTableName()." p ON pc.project_structure_id = p.id
            WHERE vo.eproject_rfv_id = ".$requestForVariation->id." AND p.id = ".$project->id."
            AND i.is_from_rfv IS TRUE AND i.deleted_at IS NULL
            ORDER BY x.variation_order_item_id"
        );

        $stmt->execute();
        
        $itemClaimCertificates = $stmt->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_UNIQUE|PDO::FETCH_ASSOC);

        $stmt = $pdo->prepare("SELECT c.variation_order_item_id, c.percentage, c.amount 
            FROM ".RequestForVariationItemClaimTable::getInstance()->getTableName()." c
            JOIN ".VariationOrderItemTable::getInstance()->getTableName()." i ON c.variation_order_item_id = i.id
            JOIN ".VariationOrderTable::getInstance()->getTableName()." vo ON i.variation_order_id = vo.id
            WHERE vo.eproject_rfv_id = ".$requestForVariation->id." AND vo.project_structure_id = ".$project->id."
            AND i.is_from_rfv IS TRUE AND i.deleted_at IS NULL
            ORDER BY i.priority, i.lft, i.level"
        );
        
        $stmt->execute();
        
        $claims = $stmt->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_UNIQUE|PDO::FETCH_ASSOC);
      
        $stmt = $pdo->prepare("SELECT i.id, i.bill_ref, i.description, i.type, i.reference_quantity AS quantity, i.reference_rate As rate, i.reference_amount AS total,
            uom.id AS uom_id, uom.symbol AS uom_symbol
            FROM ".VariationOrderItemTable::getInstance()->getTableName()." i
            LEFT JOIN ".UnitOfMeasurementTable::getInstance()->getTableName()." uom ON i.uom_id = uom.id AND uom.deleted_at IS NULL
            JOIN ".VariationOrderTable::getInstance()->getTableName()." vo ON i.variation_order_id = vo.id
            WHERE vo.eproject_rfv_id = ".$requestForVariation->id." AND vo.project_structure_id = ".$project->id."
            AND i.is_from_rfv IS TRUE AND i.deleted_at IS NULL
            ORDER BY i.priority, i.lft, i.level"
        );
        
        $stmt->execute();
        
        $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $canClaim = !($requestForVariation->hasClaimCertificate());

        foreach($records as $key => $record)
        {
            $percentage = 0;
            $amount     = 0;

            if(array_key_exists($record['id'], $claims))
            {
                $percentage = $claims[$record['id']]['percentage'];
                $amount     = $claims[$record['id']]['amount'];
            }

            if($canClaim && array_key_exists($record['id'], $itemClaimCertificates))
            {
                $records[$key]['can_claim'] = ($itemClaimCertificates[$record['id']]['status'] == ClaimCertificate::STATUS_TYPE_IN_PROGRESS);
            }
            else
            {
                $records[$key]['can_claim'] = $canClaim;
            }

            $records[$key]['type']                   = (string) $record['type'];
            $records[$key]['uom_id']                 = $record['uom_id'] > 0 ? $record['uom_id'] : -1;
            $records[$key]['uom_symbol']             = $record['uom_id'] > 0 ? $record['uom_symbol'] : '';
            $records[$key]['percentage-value']       = number_format($percentage, 2, '.', '');
            $records[$key]['amount-value']           = number_format($amount, 2, '.', '');
            $records[$key]['claim_cert_number']      = array_key_exists($record['id'], $itemClaimCertificates) ? $itemClaimCertificates[$record['id']]['version'] : null;
            $records[$key]['_csrf_token']            = $form->getCSRFToken();
        }

        array_push($records, [
            'id'                     => Constants::GRID_LAST_ROW,
            'description'            => '',
            'bill_ref'               => '',
            'type'                   => (string) VariationOrderItem::TYPE_WORK_ITEM,
            'uom_id'                 => '-1',
            'uom_symbol'             => '',
            'percentage-value'       => 0,
            'amount-value'           => 0,
            'rate'                   => 0,
            'quantity'               => 0,
            'total'                  => 0,
            'claim_cert_number'      => null,
            'can_claim'              => false,
            '_csrf_token'            => $form->getCSRFToken()
        ]);

        return $this->renderJson([
            'identifier' => 'id',
            'items'      => $records
        ]);
    }

    public function executeClaimItemUpdate(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $item = Doctrine_Core::getTable('VariationOrderItem')->find($request->getParameter('id'))
        );

        $rowData = array();

        $con = $item->getTable()->getConnection();

        try
        {
            $con->beginTransaction();

            $claim = $item->RequestForVariationItemClaim;

            if(!$claim)
            {
                $claim = new RequestForVariationItemClaim();
                $claim->variation_order_item_id = $item->id;
            }

            $fieldName  = $request->getParameter('attr_name');
            $fieldValue = $request->getParameter('val');

            $fieldValue = is_numeric($fieldValue) ? $fieldValue : 0;
            $fieldValue = number_format($fieldValue, 2, '.', '');

            $claim->{'set' . sfInflector::camelize($fieldName)}($fieldValue);

            $claim->save($con);

            $con->commit();

            $success = true;

            $errorMsg = null;

            $claim->refresh();

            $rowData['amount-value']     = number_format($claim->amount, 2, '.', '');
            $rowData['percentage-value'] = number_format($claim->percentage, 2, '.', '');
        }
        catch (Exception $e)
        {
            $con->rollback();
            $errorMsg = $e->getMessage();
            $success  = false;
        }

        return $this->renderJson(array( 'success' => $success, 'errorMsg' => $errorMsg, 'data' => $rowData ));
    }

    public function executeGetOpenClaimCertificate(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('pid')) and
            $project->node->isRoot() and
            $project->PostContract->published_type == PostContract::PUBLISHED_TYPE_NEW and
            $project->MainInformation->eproject_origin_id
        );

        $claimRevision = $project->PostContract->getOpenClaimRevision();

        $data = [
            'id' => -1,
            'version' => null
        ];

        if($claimRevision and $claimRevision->ClaimCertificate && $claimRevision->ClaimCertificate->status == ClaimCertificate::STATUS_TYPE_IN_PROGRESS)
        {
            $claimCertificate = $claimRevision->ClaimCertificate;
            $data = [
                'id'      => $claimCertificate->id,
                'version' => $claimRevision->version
            ];
        }

        return $this->renderJson($data);
    }

    public function executeClaimCertificateAttach(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $claimCertificate = Doctrine_Core::getTable('ClaimCertificate')->find($request->getParameter('cid')) and
            $claimCertificate->status == ClaimCertificate::STATUS_TYPE_IN_PROGRESS and
            $variationOrderItem = Doctrine_Core::getTable('VariationOrderItem')->find($request->getParameter('id'))
        );

        $errorMsg = null;
        $con      = $claimCertificate->getTable()->getConnection();

        try
        {
            $xref = DoctrineQuery::create()->select('x.id')
            ->from('RequestForVariationItemClaimCertificate x')
            ->where('x.variation_order_item_id = ?', $variationOrderItem->id)
            ->andWhere('x.claim_certificate_id = ?', $claimCertificate->id)
            ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
            ->fetchOne();

            if(!$xref)
            {
                $con->beginTransaction();

                $xref = new RequestForVariationItemClaimCertificate();
                $xref->variation_order_item_id = $variationOrderItem->id;
                $xref->claim_certificate_id    = $claimCertificate->id;
                
                $xref->save($con);

                $con->commit();
            }

            //call claimCert save() method so it will update claim cert certified amount. Certified amount must include RFV claim value
            $claimCertificate->save();

            $success = true;

            $item = [
                'id'                => $variationOrderItem->id,
                'claim_cert_number' => $claimCertificate->PostContractClaimRevision->version
            ];
        }
        catch (Exception $e)
        {
            $con->rollback();

            $item = [];
            $errorMsg = $e->getMessage();
            $success  = false;
        }

        return $this->renderJson([
            'item'     => $item,
            'success'  => $success,
            'errorMsg' => $errorMsg
        ]);
    }
}