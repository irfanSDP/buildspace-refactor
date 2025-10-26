<?php

/**
 * variationOrder actions.
 *
 * @package    buildspace
 * @subpackage variationOrder
 * @author     1337 developers
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class variationOrderActions extends BaseActions {

    /**** vo actions ****/
    public function executeGetVariationOrderList(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('pid')) and
            $project->node->isRoot()
        );

        $requestForVariations = [];

        $variationOrderRFVClause = " AND vo.eproject_rfv_id IS NULL ";

        if($project->MainInformation->eproject_origin_id)
        {
            $eprojectRFVObj = new EProjectRequestForVariation();
            $eprojectPdo = $eprojectRFVObj->getTable()->getConnection()->getDbh();

            $stmt = $eprojectPdo->prepare("SELECT r.id, r.rfv_number
                FROM ".EProjectRequestForVariationTable::getInstance()->getTableName()." r
                WHERE r.project_id = {$project->MainInformation->eproject_origin_id}
                AND r.status = ".EProjectRequestForVariation::STATUS_APPROVED."
                ORDER BY r.created_at DESC");

            $stmt->execute();

            $requestForVariations = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

            if(!empty($requestForVariations))
            {
                $variationOrderRFVClause = " AND (vo.eproject_rfv_id IS NULL OR vo.eproject_rfv_id IN (".implode(',', array_keys($requestForVariations))."))";
            }
        }

        $pdo  = VariationOrderTable::getInstance()->getConnection()->getDbh();

        $itemClause = $request->hasParameter('oid') ? "AND vo.id = {$request->getParameter('oid')}" : "";

        $claimCertificateClause = "";
        $selectedRevisionClause = "";
        if($request->hasParameter('claimRevision'))
        {
            $claimCertificates = PostContractClaimRevisionTable::getClaimCertificates($request->getParameter('claimRevision'), '<=');

            if( count($claimCertIds = array_column($claimCertificates, 'id')) > 0 )
            {
                $claimCertificateClause = "AND x.claim_certificate_id in (".implode(',', $claimCertIds).")";
                $selectedRevisionClause = "AND cert.id in (" . implode(',', $claimCertIds) . ")";
            }
        }

        $stmt = $pdo->prepare("SELECT vo.id, vo.description, vo.type, vo.is_approved, vo.status, vo.updated_at, vo.eproject_rfv_id, x.claim_certificate_id, SUM(i.reference_amount) as reference_amount
        FROM ".VariationOrderTable::getInstance()->getTableName()." vo
        LEFT JOIN ".VariationOrderClaimCertificateTable::getInstance()->getTableName()." x ON vo.id = x.variation_order_id
        LEFT JOIN ".VariationOrderItemTable::getInstance()->getTableName()." i ON vo.id = i.variation_order_id
        WHERE vo.project_structure_id = {$project->id}
        {$itemClause}
        {$claimCertificateClause}
        {$variationOrderRFVClause}
        AND vo.deleted_at IS NULL AND i.deleted_at IS NULL
        GROUP BY vo.id, x.claim_certificate_id ORDER BY vo.priority ASC");

        $stmt->execute();

        $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $form = new BaseForm();

        $stmt = $pdo->prepare("SELECT i.variation_order_id,
        ROUND(SUM(ROUND(i.total_unit * i.omission_quantity * i.rate, 2)), 2) AS omission,
        ROUND(SUM(ROUND(i.total_unit * i.addition_quantity * i.rate, 2)), 2) AS addition,
        ROUND(SUM(ROUND(i.total_unit * i.addition_quantity * i.rate, 2) - ROUND(i.total_unit * i.omission_quantity * i.rate, 2)), 2) AS nett_omission_addition
        FROM " . VariationOrderItemTable::getInstance()->getTableName() . " i
        JOIN " . VariationOrderTable::getInstance()->getTableName() . " vo ON i.variation_order_id = vo.id
        WHERE vo.project_structure_id = " . $project->id . "
        {$variationOrderRFVClause}
        AND i.type <> " . VariationOrderItem::TYPE_HEADER . " AND i.rate <> 0
        AND vo.deleted_at IS NULL AND i.deleted_at IS NULL GROUP BY i.variation_order_id");

        $stmt->execute();

        $quantities = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmt = $pdo->prepare("SELECT vo.id AS variation_order_id, ROUND(SUM(
        CASE WHEN ((voi.rate * voi.addition_quantity) - (voi.rate * voi.omission_quantity) < 0)
            THEN -1 * ABS(i.up_to_date_amount)
            ELSE i.up_to_date_amount
        END
        ), 2) AS amount
        FROM " . VariationOrderTable::getInstance()->getTableName() . " vo
        JOIN " . VariationOrderClaimTable::getInstance()->getTableName() . " c ON c.variation_order_id = vo.id
        JOIN " . VariationOrderClaimItemTable::getInstance()->getTableName() . " i ON i.variation_order_claim_id = c.id
        JOIN " . VariationOrderItemTable::getInstance()->getTableName() . " voi ON i.variation_order_item_id = voi.id
        LEFT JOIN ".VariationOrderClaimClaimCertificateTable::getInstance()->getTableName()." xref ON xref.variation_order_claim_id = c.id
        LEFT JOIN ".ClaimCertificateTable::getInstance()->getTableName()." cert ON cert.id = xref.claim_certificate_id
        LEFT JOIN ".PostContractClaimRevisionTable::getInstance()->getTableName()." rev ON rev.id = cert.post_contract_claim_revision_id
        WHERE vo.project_structure_id = " . $project->id . "
        AND c.is_viewing IS TRUE
        AND i.up_to_date_amount <> 0
        {$selectedRevisionClause}
        {$variationOrderRFVClause}
        AND vo.deleted_at IS NULL AND c.deleted_at IS NULL AND i.deleted_at IS NULL AND voi.deleted_at IS NULL
        GROUP BY vo.id");
        
        $stmt->execute();

        $upToDateClaims = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmt = $pdo->prepare("SELECT attachment.object_id AS object_id, COUNT(attachment.object_id) AS number_of_attachments
            FROM ".AttachmentsTable::getInstance()->getTableName()." attachment
            JOIN " . VariationOrderTable::getInstance()->getTableName() . " vo ON attachment.object_id = vo.id
            WHERE attachment.object_class= '".get_class(new VariationOrder())."'
            AND vo.project_structure_id = " . $project->id . "
            {$variationOrderRFVClause}
            AND vo.deleted_at IS NULL
            GROUP BY attachment.object_id");

        $stmt->execute();

        $attachments = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $claimCertificateVersions = array();
        if($project->PostContract->published_type == PostContract::PUBLISHED_TYPE_NEW)
        {
            $stmt = $pdo->prepare("SELECT DISTINCT x.variation_order_id, rev.version
                FROM ".VariationOrderClaimCertificateTable::getInstance()->getTableName()." x
                JOIN ".ClaimCertificateTable::getInstance()->getTableName()." c ON c.id = x.claim_certificate_id
                JOIN ".PostContractClaimRevisionTable::getInstance()->getTableName()." rev ON rev.id = c.post_contract_claim_revision_id
                WHERE rev.post_contract_id = :postContractId AND rev.deleted_at IS NULL ORDER BY rev.version ASC");

            $stmt->execute(array( 'postContractId' => $project->PostContract->id ));

            $claimCertificateVersions = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        }

        foreach ( $records as $key => $record )
        {
            $fromRFV = (!empty($record['eproject_rfv_id']) && array_key_exists($record['eproject_rfv_id'], $requestForVariations));

            $records[$key]['claim_cert_number'] = array_key_exists($record['id'], $claimCertificateVersions) ? $claimCertificateVersions[$record['id']] : "";
            $records[$key]['can_be_edited']     = ($record['status'] == PostContractClaim::STATUS_PREPARING);
            $records[$key]['is_approved']       = $record['is_approved'] ? "true" : "false";
            $records[$key]['status']            = $record['status'];
            $records[$key]['relation_id']       = $project->id;
            $records[$key]['class']             = get_class(new VariationOrder());
            $records[$key]['attachment']        = "Upload";
            $records[$key]['is_from_rfv']       = $fromRFV;
            $records[$key]['rfv_number']        = ($fromRFV) ? $requestForVariations[$record['eproject_rfv_id']] : "";

            foreach ($attachments as $attachmentIdx => $attachment)
            {
                if( $attachment['object_id'] == $record['id'])
                {
                    $records[$key]['attachment'] = $attachment['number_of_attachments'];

                    unset($attachments[$attachmentIdx]);
                    break;
                }
            }
            $records[$key]['omission']               = 0;
            $records[$key]['addition']               = 0;
            $records[$key]['total_claim']            = 0;
            $records[$key]['nett_omission_addition'] = 0;
            $records[$key]['updated_at']             = date('d/m/Y H:i', strtotime($record['updated_at']));
            $records[$key]['_csrf_token']            = $form->getCSRFToken();

            foreach ( $quantities as $quantity )
            {
                if ( $quantity['variation_order_id'] == $record['id'] )
                {
                    $records[$key]['omission']               = $quantity['omission'];
                    $records[$key]['addition']               = $quantity['addition'];
                    $records[$key]['nett_omission_addition'] = $quantity['nett_omission_addition'];

                    unset( $quantity );
                }
            }

            foreach ( $upToDateClaims as $upToDateClaim )
            {
                if ( $upToDateClaim['variation_order_id'] == $record['id'] )
                {
                    $records[$key]['total_claim'] = $upToDateClaim['amount'];
                    unset( $upToDateClaim );
                }
            }
            unset( $record );
        }

        unset( $quantities, $upToDateClaims );

        //default last row
        array_push($records, array(
            'id'                     => Constants::GRID_LAST_ROW,
            'claim_cert_number'      => '',
            'description'            => '',
            'attachment'             => '',
            'can_be_edited'          => true,
            'relation_id'            => $project->id,
            'omission'               => 0,
            'addition'               => 0,
            'total_claim'            => 0,
            'nett_omission_addition' => 0,
            'type'                   => VariationOrder::TYPE_BUDGETARY,
            'is_approved'            => false,
            'status'                 => null,
            'updated_at'             => '-',
            'is_from_rfv'            => false,
            'rfv_number'             => '',
            '_csrf_token'            => $form->getCSRFToken()
        ));

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $records
        ));
    }

    public function executeVariationOrderAdd(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless($request->isXmlHttpRequest() and $request->isMethod('post'));

        $variationOrder = new VariationOrder();
        $con            = $variationOrder->getTable()->getConnection();

        if ( $request->hasParameter('id') and $request->getParameter('id') == Constants::GRID_LAST_ROW )
        {
            $prevVariationOrder = $request->getParameter('prev_item_id') > 0 ? Doctrine_Core::getTable('VariationOrder')->find($request->getParameter('prev_item_id')) : null;

            $priority           = $prevVariationOrder ? $prevVariationOrder->priority + 1 : 0;
            $projectStructureId = $request->getParameter('relation_id');

            if ( $request->hasParameter('attr_name') )
            {
                $fieldName  = $request->getParameter('attr_name');
                $fieldValue = $request->getParameter('val');

                $variationOrder->{'set' . sfInflector::camelize($fieldName)}($fieldValue);
            }

        }
        else
        {
            $this->forward404Unless($nextVariationOrder = Doctrine_Core::getTable('VariationOrder')->find($request->getParameter('before_id')));

            $priority           = $nextVariationOrder->priority;
            $projectStructureId = $nextVariationOrder->project_structure_id;
        }

        $items = array();
        try
        {
            $con->beginTransaction();

            DoctrineQuery::create()
                ->update('VariationOrder')
                ->set('priority', 'priority + 1')
                ->where('priority >= ?', $priority)
                ->andWhere('project_structure_id = ?', $projectStructureId)
                ->execute();

            $variationOrder->project_structure_id = $projectStructureId;
            $variationOrder->priority             = $priority;

            $variationOrder->save();

            $con->commit();

            $success = true;

            $errorMsg = null;

            $item = array();

            $form = new BaseForm();

            $pdo  = $variationOrder->getTable()->getConnection()->getDbh();

            $stmt = $pdo->prepare("SELECT attachment.object_id AS object_id, COUNT(attachment.object_id) AS number_of_attachments
                FROM ".AttachmentsTable::getInstance()->getTableName()." attachment
                JOIN " . VariationOrderTable::getInstance()->getTableName() . " vo ON attachment.object_id = vo.id
                WHERE attachment.object_class= '".get_class(new VariationOrder())."'
                AND vo.project_structure_id = " . $projectStructureId . "
                AND vo.deleted_at IS NULL
                GROUP BY attachment.object_id");

            $stmt->execute();

            $attachments = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $item['id']                     = $variationOrder->id;
            $item['claim_cert_number']      = '';
            $item['description']            = $variationOrder->description;
            $item['class'] = get_class(new VariationOrder());
            $item['attachment'] = "Upload";
            foreach ($attachments as $attachmentIdx => $attachment)
            {
                if( $attachment['object_id'] == $item['id'])
                {
                    $item['attachment'] = $attachment['number_of_attachments'];

                    unset($attachments[$attachmentIdx]);
                    break;
                }
            }
            $item['is_approved']            = $variationOrder->is_approved ? "true" : "false";
            $item['status']                 = $variationOrder->status;
            $item['relation_id']            = $projectStructureId;
            $item['omission']               = 0;
            $item['addition']               = 0;
            $item['total_claim']            = 0;
            $item['nett_omission_addition'] = 0;
            $item['can_be_edited']          = true;
            $item['type']                   = $variationOrder->type;
            $item['updated_at']             = date('d/m/Y H:i', strtotime($variationOrder->updated_at));
            $item['rfv_number']             = '';
            $item['is_from_rfv']            = false;
            $item['_csrf_token']            = $form->getCSRFToken();

            array_push($items, $item);

            if ( $request->hasParameter('id') and $request->getParameter('id') == Constants::GRID_LAST_ROW )
            {
                array_push($items, array(//default last row
                    'id'                     => Constants::GRID_LAST_ROW,
                    'claim_cert_number'      => '',
                    'description'            => '',
                    'attachment'             => '',
                    'can_be_edited'          => true,
                    'relation_id'            => $projectStructureId,
                    'omission'               => 0,
                    'addition'               => 0,
                    'total_claim'            => 0,
                    'nett_omission_addition' => 0,
                    'type'                   => VariationOrder::TYPE_BUDGETARY,
                    'is_approved'            => false,
                    'status'                 => null,
                    'updated_at'             => '-',
                    'rfv_number'             => '',
                    'is_from_rfv'            => false,
                    '_csrf_token'            => $form->getCSRFToken()
                ));
            }
        }
        catch (Exception $e)
        {
            $con->rollback();
            $errorMsg = $e->getMessage();
            $success  = false;
        }

        return $this->renderJson(array(
            'success'  => $success,
            'items'    => $items,
            'errorMsg' => $errorMsg
        ));
    }

    public function executeVariationOrderUpdate(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $variationOrder = Doctrine_Core::getTable('VariationOrder')->find($request->getParameter('id'))
        );

        $rowData = array();
        $con     = $variationOrder->getTable()->getConnection();

        $form = new BaseForm();

        try
        {
            $con->beginTransaction();

            $fieldName  = $request->getParameter('attr_name');
            $fieldValue = $request->getParameter('val');

            $variationOrder->{'set' . sfInflector::camelize($fieldName)}($fieldValue);

            $variationOrder->save($con);

            $con->commit();

            if( $variationOrder->status == PostContractClaim::STATUS_PENDING ) ContractManagementClaimVerifierTable::sendNotifications($variationOrder->ProjectStructure, PostContractClaim::TYPE_VARIATION_ORDER, $variationOrder->id);

            $success = true;

            $errorMsg = null;

            $variationOrder->refresh();

            $value = $variationOrder->$fieldName;

            $xref = $variationOrder->getClaimCertificateXref()->toArray();
            $rowData = array(
                $fieldName          => $value,
                'can_be_edited'     => $variationOrder->canBeEdited(),
                'claim_cert_number' => !empty($xref['claim_certificate_id']) ? $variationOrder->getClaimCertificateXref()->ClaimCertificate->PostContractClaimRevision->version : '',
                'updated_at'        => date('d/m/Y H:i', strtotime($variationOrder->updated_at)),
                '_csrf_token'       => $form->getCSRFToken(),
            );
        }
        catch (Exception $e)
        {
            $con->rollback();
            $errorMsg = $e->getMessage();
            $success  = false;
        }

        return $this->renderJson(array( 'success' => $success, 'errorMsg' => $errorMsg, 'data' => $rowData ));
    }

    public function executeVariationOrderDelete(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $variationOrder = Doctrine_Core::getTable('VariationOrder')->find($request->getParameter('id')) and
            empty($variationOrder->eproject_rfv_id)
        );

        $errorMsg = null;
        try
        {
            $item['id'] = $variationOrder->id;

            $variationOrder->delete();

            $success = true;
        }
        catch (Exception $e)
        {
            $errorMsg = $e->getMessage();
            $item     = array();
            $success  = false;
        }

        // return items need to be in array since grid js expect an array of items for delete operation (delete from store)
        return $this->renderJson(array( 'success' => $success, 'errorMsg' => $errorMsg, 'items' => array( $item ) ));
    }

    /**** item actions ****/
    public function executeGetVariationOrderItemList(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $variationOrder = Doctrine_Core::getTable('VariationOrder')->find($request->getParameter('id'))
        );

        $pdo  = $variationOrder->getTable()->getConnection()->getDbh();
        $form = new BaseForm();

        $stmt = $pdo->prepare("SELECT i.id, i.description, i.type, i.lft, i.level, i.total_unit, i.rate, i.reference_quantity, i.reference_rate, i.reference_amount,
            i.bill_ref, i.bill_item_id, i.omission_quantity, i.has_omission_build_up_quantity, i.is_from_rfv,
            i.addition_quantity, i.has_addition_build_up_quantity, uom.id AS uom_id, uom.symbol AS uom_symbol
            FROM " . VariationOrderItemTable::getInstance()->getTableName() . " i
            LEFT JOIN " . UnitOfMeasurementTable::getInstance()->getTableName() . " uom ON i.uom_id = uom.id AND uom.deleted_at IS NULL
            WHERE i.variation_order_id = " . $variationOrder->id . " AND i.deleted_at IS NULL
            ORDER BY i.priority, i.lft, i.level");

        $stmt->execute();

        $variationOrderItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $canBeEdited = $variationOrder->canBeEdited();
        $canClaim    = $variationOrder->canClaim();

        $selectedRevisionClause = "AND c.is_viewing IS TRUE";

        if( $request->hasParameter('claimRevision') )
        {
            $claimCertificates = PostContractClaimRevisionTable::getClaimCertificates($request->getParameter('claimRevision'), '<=');

            if(count($certIds = array_column($claimCertificates, 'id')) > 0)
            {
                $selectedRevisionClause = "AND cert.id in (" . implode(',', $certIds) . ")
                    and c.revision = (
                        select coalesce(max(c.revision), 0)
                        FROM " . VariationOrderItemTable::getInstance()->getTableName() . " i
                        JOIN " . VariationOrderClaimTable::getInstance()->getTableName() . " c ON i.variation_order_id = c.variation_order_id
                        JOIN " . VariationOrderClaimItemTable::getInstance()->getTableName() . " ci ON ci.variation_order_claim_id = c.id AND ci.variation_order_item_id = i.id
                        JOIN " . VariationOrderClaimClaimCertificateTable::getInstance()->getTableName() . " xref ON xref.variation_order_claim_id = ci.variation_order_claim_id
                        JOIN " . ClaimCertificateTable::getInstance()->getTableName() . " cert ON cert.id = xref.claim_certificate_id
                        WHERE i.variation_order_id = " . $variationOrder->id . "
                        AND cert.id in (" . implode(',', $certIds) . ")
                        AND i.deleted_at IS NULL AND c.deleted_at IS NULL AND ci.deleted_at IS NULL
                    )";
            }
        }

        $stmt = $pdo->prepare("SELECT DISTINCT i.id AS variation_order_item_id,
            CASE WHEN ((i.rate * i.addition_quantity) - (i.rate * i.omission_quantity) < 0)
                THEN -1 * ABS(ROUND(ci.current_amount, 2))
                ELSE ROUND(ci.current_amount, 2)
            END AS current_amount,
            CASE WHEN ((i.rate * i.addition_quantity) - (i.rate * i.omission_quantity) < 0)
                THEN -1 * ABS(ROUND(ci.current_percentage, 2))
                ELSE ROUND(ci.current_percentage, 2)
            END AS current_percentage,
            CASE WHEN ((i.rate * i.addition_quantity) - (i.rate * i.omission_quantity) < 0)
                THEN -1 * ABS(ROUND(ci.current_quantity, 2))
                ELSE ROUND(ci.current_quantity, 2)
            END AS current_quantity,
            CASE WHEN ((i.rate * i.addition_quantity) - (i.rate * i.omission_quantity) < 0)
                THEN -1 * ABS(ROUND(ci.up_to_date_quantity, 2))
                ELSE ROUND(ci.up_to_date_quantity, 2)
            END AS up_to_date_quantity,
            CASE WHEN ((i.rate * i.addition_quantity) - (i.rate * i.omission_quantity) < 0)
                THEN -1 * ABS(ROUND(ci.up_to_date_amount, 2))
                ELSE ROUND(ci.up_to_date_amount, 2)
            END AS up_to_date_amount,
            CASE WHEN ((i.rate * i.addition_quantity) - (i.rate * i.omission_quantity) < 0)
                THEN -1 * ABS(ROUND(ci.up_to_date_percentage, 2))
                ELSE ROUND(ci.up_to_date_percentage, 2)
            END AS up_to_date_percentage,
            CASE WHEN ((pvoi.rate * pvoi.addition_quantity) - (pvoi.rate * pvoi.omission_quantity) < 0)
                THEN -1 * ABS(ROUND(pci.up_to_date_quantity, 2))
                ELSE ROUND(pci.up_to_date_quantity, 2)
            END AS previous_quantity,
            CASE WHEN ((pvoi.rate * pvoi.addition_quantity) - (pvoi.rate * pvoi.omission_quantity) < 0)
                THEN -1 * ABS(ROUND(pci.up_to_date_amount, 2))
                ELSE ROUND(pci.up_to_date_amount, 2)
            END AS previous_amount,
            CASE WHEN ((pvoi.rate * pvoi.addition_quantity) - (pvoi.rate * pvoi.omission_quantity) < 0)
                THEN -1 * ABS(ROUND(pci.up_to_date_percentage, 2))
                ELSE ROUND(pci.up_to_date_percentage, 2)
            END AS previous_percentage, ci.remarks
            FROM " . VariationOrderItemTable::getInstance()->getTableName() . " i
            JOIN " . VariationOrderClaimTable::getInstance()->getTableName() . " c ON i.variation_order_id = c.variation_order_id
            JOIN " . VariationOrderClaimItemTable::getInstance()->getTableName() . " ci ON ci.variation_order_claim_id = c.id AND ci.variation_order_item_id = i.id
            LEFT JOIN " . VariationOrderClaimTable::getInstance()->getTableName() . " pc ON pc.variation_order_id = c.variation_order_id AND pc.revision = c.revision - 1
            LEFT JOIN " . VariationOrderClaimItemTable::getInstance()->getTableName() . " pci ON pci.variation_order_claim_id = pc.id AND pci.variation_order_item_id = i.id
            LEFT JOIN " . VariationOrderItemTable::getInstance()->getTableName() . " pvoi ON pci.variation_order_item_id = pvoi.id
            LEFT JOIN ".VariationOrderClaimClaimCertificateTable::getInstance()->getTableName()." xref ON xref.variation_order_claim_id = c.id
            LEFT JOIN ".ClaimCertificateTable::getInstance()->getTableName()." cert ON cert.id = xref.claim_certificate_id
            LEFT JOIN ".PostContractClaimRevisionTable::getInstance()->getTableName()." rev ON rev.id = cert.post_contract_claim_revision_id
            WHERE i.variation_order_id = " . $variationOrder->id . "
            {$selectedRevisionClause}
            AND i.deleted_at IS NULL AND c.deleted_at IS NULL AND ci.deleted_at IS NULL AND pc.deleted_at IS NULL AND pci.deleted_at IS NULL");

        $stmt->execute();

        $claimItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmt = $pdo->prepare("SELECT attachment.object_id AS object_id, COUNT(attachment.object_id) AS number_of_attachments
            FROM ".AttachmentsTable::getInstance()->getTableName()." attachment
            JOIN " . VariationOrderItemTable::getInstance()->getTableName() . " i ON attachment.object_id = i.id
            WHERE attachment.object_class= '".get_class(new VariationOrderItem())."'
            AND i.variation_order_id = ".$variationOrder->id."
            AND i.deleted_at IS NULL
            GROUP BY attachment.object_id");

        $stmt->execute();

        $attachments = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ( $variationOrderItems as $key => $variationOrderItem )
        {
            $variationOrderItems[$key]['class']                       = get_class(new VariationOrderItem());
            $variationOrderItems[$key]['attachment']                  = "Upload";

            foreach ($attachments as $attachmentIdx => $attachment)
            {
                if( $attachment['object_id'] == $variationOrderItem['id'])
                {
                    $variationOrderItems[$key]['attachment'] = $attachment['number_of_attachments'];

                    unset($attachments[$attachmentIdx]);
                    break;
                }
            }
            $variationOrderItems[ $key ]['reference_quantity-value'] = round($variationOrderItem['reference_quantity'], 2);
            $variationOrderItems[ $key ]['reference_rate-value']     = round($variationOrderItem['reference_rate'], 2);
            $variationOrderItems[ $key ]['reference_amount-value']   = round($variationOrderItem['reference_amount'], 2);

            $variationOrderItems[$key]['omission_quantity-value'] = round($variationOrderItem['omission_quantity'], 2);
            $variationOrderItems[$key]['omission_total'] = round($variationOrderItems[$key]['omission_quantity-value'] * $variationOrderItem['rate'] * $variationOrderItem['total_unit'], 2);

            $variationOrderItems[$key]['addition_quantity-value'] = round($variationOrderItem['addition_quantity'], 2);
            $variationOrderItems[$key]['addition_total'] = round($variationOrderItems[$key]['addition_quantity-value'] * $variationOrderItem['rate'] * $variationOrderItem['total_unit'], 2);

            $variationOrderItems[$key]['rate-value']              = round($variationOrderItem['rate'], 2);
            $variationOrderItems[$key]['nett_omission_addition']  = $variationOrderItems[$key]['addition_total'] - $variationOrderItems[$key]['omission_total'];

            $variationOrderItems[$key]['type']                    = (string) $variationOrderItem['type'];
            $variationOrderItems[$key]['uom_id']                  = $variationOrderItem['uom_id'] > 0 ? (string) $variationOrderItem['uom_id'] : '-1';
            $variationOrderItems[$key]['uom_symbol']              = $variationOrderItem['uom_id'] > 0 ? $variationOrderItem['uom_symbol'] : '';
            $variationOrderItems[$key]['relation_id']             = $variationOrder->id;
            $variationOrderItems[$key]['can_be_edited']           = $canBeEdited;
            $variationOrderItems[$key]['can_claim']               = $canClaim;
            $variationOrderItems[$key]['remarks']                 = "";
            $variationOrderItems[$key]['_csrf_token']             = $form->getCSRFToken();

            $variationOrderItems[$key]['previous_quantity-value']     = number_format(0, 2, '.', '');
            $variationOrderItems[$key]['previous_percentage-value']   = number_format(0, 2, '.', '');
            $variationOrderItems[$key]['previous_amount-value']       = number_format(0, 2, '.', '');

            $variationOrderItems[$key]['current_quantity-value']      = number_format(0, 2, '.', '');
            $variationOrderItems[$key]['current_percentage-value']    = number_format(0, 2, '.', '');
            $variationOrderItems[$key]['current_amount-value']        = number_format(0, 2, '.', '');

            $variationOrderItems[$key]['up_to_date_quantity-value']   = number_format(0, 2, '.', '');
            $variationOrderItems[$key]['up_to_date_percentage-value'] = number_format(0, 2, '.', '');
            $variationOrderItems[$key]['up_to_date_amount-value']     = number_format(0, 2, '.', '');

            foreach ( $claimItems as $claimItem )
            {
                if ( $claimItem['variation_order_item_id'] == $variationOrderItem['id'] )
                {
                    $variationOrderItems[$key]['previous_quantity-value']     = $claimItem['previous_quantity'];
                    $variationOrderItems[$key]['previous_percentage-value']   = $claimItem['previous_percentage'];
                    $variationOrderItems[$key]['previous_amount-value']       = $claimItem['previous_amount'];

                    $variationOrderItems[$key]['current_quantity-value']      = $claimItem['current_quantity'];
                    $variationOrderItems[$key]['current_percentage-value']    = $claimItem['current_percentage'];
                    $variationOrderItems[$key]['current_amount-value']        = $claimItem['current_amount'];

                    $variationOrderItems[$key]['up_to_date_quantity-value']   = $claimItem['up_to_date_quantity'];
                    $variationOrderItems[$key]['up_to_date_percentage-value'] = $claimItem['up_to_date_percentage'];
                    $variationOrderItems[$key]['up_to_date_amount-value']     = $claimItem['up_to_date_amount'];

                    $variationOrderItems[$key]['remarks']                     = $claimItem['remarks'];

                    unset( $claimItem );
                }
            }
        }

        unset( $claimItems );

        array_push($variationOrderItems, array(
            'id'                             => Constants::GRID_LAST_ROW,
            'description'                    => '',
            'attachment'                     => '',
            'bill_ref'                       => '',
            'total_unit'                     => '',
            'bill_item_id'                   => - 1,
            'type'                           => (string) VariationOrderItem::TYPE_WORK_ITEM,
            'uom_id'                         => '-1',
            'uom_symbol'                     => '',
            'relation_id'                    => $variationOrder->id,
            'updated_at'                     => '-',
            'level'                          => 0,
            'rate-value'                     => 0,
            'omission_quantity-value'        => 0,
            'has_omission_build_up_quantity' => false,
            'addition_quantity-value'        => 0,
            'has_addition_build_up_quantity' => false,
            'addition_total'                 => 0,
            'omission_total'                 => 0,
            'nett_omission_addition'         => 0,
            'can_be_edited'                  => $canBeEdited,
            'can_claim'                      => $canClaim,
            'is_from_rfv'                    => false,
            'previous_quantity-value'        => 0,
            'previous_percentage-value'      => 0,
            'previous_amount-value'          => 0,
            'current_quantity-value'         => 0,
            'current_percentage-value'       => 0,
            'current_amount-value'           => 0,
            'up_to_date_quantity-value'      => 0,
            'up_to_date_percentage-value'    => 0,
            'up_to_date_amount-value'        => 0,
            'reference_rate-value'           => 0,
            'reference_quantity-value'       => 0,
            'remarks'                        => "",
            '_csrf_token'                    => $form->getCSRFToken()
        ));

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $variationOrderItems
        ));
    }

    public function executeVariationOrderItemAdd(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless($request->isXmlHttpRequest() and $request->isMethod('post'));

        $items = array();

        $con = Doctrine_Core::getTable('VariationOrderItem')->getConnection();

        try
        {
            $con->beginTransaction();

            if ( $request->hasParameter('id') and $request->getParameter('id') == Constants::GRID_LAST_ROW )
            {
                $previousItem     = $request->getParameter('prev_item_id') > 0 ? Doctrine_Core::getTable('VariationOrderItem')->find($request->getParameter('prev_item_id')) : null;
                $variationOrderId = $request->getParameter('relation_id');

                $fieldName  = $request->hasParameter('attr_name') ? $request->getParameter('attr_name') : null;
                $fieldValue = $request->hasParameter('val') ? $request->getParameter('val') : null;


                $item = VariationOrderItemTable::createItemFromLastRow($previousItem, $variationOrderId, $fieldName, $fieldValue);
            }
            else
            {
                $this->forward404Unless($nextItem = Doctrine_Core::getTable('VariationOrderItem')->find($request->getParameter('before_id')));

                $variationOrderId = $nextItem->variation_order_id;

                $item = VariationOrderItemTable::createItem($nextItem, $variationOrderId);
            }

            $con->commit();

            $success = true;

            $errorMsg = null;

            $data = array();

            $form = new BaseForm();

            $item->refresh();

            $pdo  = $item->getTable()->getConnection()->getDbh();

            $stmt = $pdo->prepare("SELECT attachment.object_id AS object_id, COUNT(attachment.object_id) AS number_of_attachments
                FROM ".AttachmentsTable::getInstance()->getTableName()." attachment
                JOIN " . VariationOrderItemTable::getInstance()->getTableName() . " i ON attachment.object_id = i.id
                WHERE attachment.object_class= '".get_class(new VariationOrderItem())."'
                AND i.variation_order_id = ".$item->variation_order_id."
                AND i.deleted_at IS NULL
                GROUP BY attachment.object_id");

            $stmt->execute();

            $attachments = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $data['id']                             = $item->id;
            $data['class']                          = get_class(new VariationOrderItem());
            $data['attachment']                     = "Upload";
            foreach ($attachments as $attachmentIdx => $attachment)
            {
                if( $attachment['object_id'] == $data['id'])
                {
                    $data['attachment'] = $attachment['number_of_attachments'];

                    unset($attachments[$attachmentIdx]);
                    break;
                }
            }
            $data['bill_ref']                       = '';
            $data['bill_item_id']                   = $item->bill_item_id;
            $data['total_unit']                     = $item->total_unit;
            $data['description']                    = $item->description;
            $data['type']                           = (string) $item->type;
            $data['uom_id']                         = $item->uom_id > 0 ? (string) $item->uom_id : '-1';
            $data['uom_symbol']                     = $item->uom_id > 0 ? Doctrine_Core::getTable('UnitOfMeasurement')->find($item->uom_id)->symbol : '';
            $data['relation_id']                    = $variationOrderId;
            $data['rate-value']                     = $item->rate;
            $data['omission_quantity-value']        = $item->omission_quantity;
            $data['has_omission_build_up_quantity'] = $item->has_omission_build_up_quantity;
            $data['addition_quantity-value']        = $item->addition_quantity;
            $data['has_addition_build_up_quantity'] = $item->has_addition_build_up_quantity;
            $data['omission_total']                 = round($item->omission_quantity * $item->rate * $item->total_unit, 2);
            $data['addition_total']                 = round($item->addition_quantity * $item->rate * $item->total_unit, 2);
            $data['nett_omission_addition']         = $data['addition_total'] - $data['omission_total'];
            $data['level']                          = $item->level;
            $data['can_be_edited']                  = true;
            $data['is_from_rfv']                    = false;
            $data['previous_quantity-value']        = number_format(0, 2, '.', '');
            $data['previous_percentage-value']      = number_format(0, 2, '.', '');
            $data['previous_amount-value']          = number_format(0, 2, '.', '');
            $data['current_quantity-value']         = number_format(0, 2, '.', '');
            $data['current_percentage-value']       = number_format(0, 2, '.', '');
            $data['current_amount-value']           = number_format(0, 2, '.', '');
            $data['up_to_date_quantity-value']      = number_format(0, 2, '.', '');
            $data['up_to_date_percentage-value']    = number_format(0, 2, '.', '');
            $data['up_to_date_amount-value']        = number_format(0, 2, '.', '');
            $data['reference_rate-value']           = number_format(0, 2, '.', '');
            $data['reference_quantity-value']       = number_format(0, 2, '.', '');
            $data['reference_amount-value']         = number_format(0, 2, '.', '');
            $data['remarks']                        = "";
            $data['_csrf_token']                    = $form->getCSRFToken();

            array_push($items, $data);

            if ( $request->hasParameter('id') and $request->getParameter('id') == Constants::GRID_LAST_ROW )
            {
                array_push($items, array(
                    'id'                             => Constants::GRID_LAST_ROW,
                    'bill_ref'                       => '',
                    'bill_item_id'                   => - 1,
                    'total_unit'                     => '',
                    'description'                    => '',
                    'attachment'                     => '',
                    'type'                           => (string) VariationOrderItem::TYPE_WORK_ITEM,
                    'uom_id'                         => '-1',
                    'uom_symbol'                     => '',
                    'rate-value'                     => 0,
                    'omission_quantity-value'        => 0,
                    'has_omission_build_up_quantity' => false,
                    'omission_total'                 => 0,
                    'addition_total'                 => 0,
                    'nett_omission_addition'         => 0,
                    'addition_quantity-value'        => 0,
                    'has_addition_build_up_quantity' => false,
                    'relation_id'                    => $variationOrderId,
                    'updated_at'                     => '-',
                    'can_be_edited'                  => true,
                    'is_from_rfv'                    => false,
                    'previous_quantity-value'        => 0,
                    'previous_percentage-value'      => 0,
                    'previous_amount-value'          => 0,
                    'current_quantity-value'         => 0,
                    'current_percentage-value'       => 0,
                    'current_amount-value'           => 0,
                    'up_to_date_quantity-value'      => 0,
                    'up_to_date_percentage-value'    => 0,
                    'up_to_date_amount-value'        => 0,
                    'reference_rate-value'           => 0,
                    'reference_quantity-value'       => 0,
                    'level'                          => 0,
                    'remarks'                        => "",
                    '_csrf_token'                    => $form->getCSRFToken()
                ));
            }
        }
        catch (Exception $e)
        {
            $con->rollback();
            $errorMsg = $e->getMessage();
            $success  = false;
        }

        return $this->renderJson(array(
            'success'  => $success,
            'items'    => $items,
            'errorMsg' => $errorMsg
        ));
    }

    public function executeVariationOrderItemUpdate(sfWebRequest $request)
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

            $fieldName  = $request->getParameter('attr_name');
            $fieldValue = $request->getParameter('val');

            $fieldValue = ( $fieldName == 'uom_id' and $fieldValue == - 1 ) ? null : $fieldValue;

            if ( $fieldName == "type" or $fieldName == "uom_id" )
            {
                $item->updateColumnByColumnName($fieldName, $fieldValue);
            }
            elseif ( $fieldName == 'omission_quantity' or $fieldName == 'addition_quantity' )
            {
                $fieldValue = is_numeric($fieldValue) ? $fieldValue : 0;
                $item->updateColumnByColumnName($fieldName, $fieldValue);

                $fieldName = $fieldName . '-value';
            }
            elseif ( $fieldName == 'rate' )
            {
                $fieldValue = is_numeric($fieldValue) ? $fieldValue : 0;
                $item->rate = $fieldValue;

                $fieldName = 'rate-value';
            }
            elseif ( $fieldName == 'reference_rate' || $fieldName == 'reference_quantity' )
            {
                $fieldValue = is_numeric($fieldValue) ? $fieldValue : 0;
                $item->updateColumnByColumnName($fieldName, $fieldValue);

                $fieldName = $fieldName . '-value';
            }
            else
            {
                $item->{'set' . sfInflector::camelize($fieldName)}($fieldValue);
            }

            $item->save($con);

            $con->commit();

            $success = true;

            $errorMsg = null;

            $item->refresh();

            $rowData[$fieldName] = $item->{$request->getParameter('attr_name')};

            $rowData['type']       = (string) $item->type;
            $rowData['uom_id']     = $item->uom_id > 0 ? (string) $item->uom_id : '-1';
            $rowData['uom_symbol'] = $item->uom_id > 0 ? $item->UnitOfMeasurement->symbol : '';

            if ( $fieldName == "type" or $fieldName == "uom_id" )
            {
                $rowData['addition_quantity-value'] = $item->addition_quantity;
                $rowData['omission_quantity-value'] = $item->omission_quantity;
            }

            if ( $fieldName == "reference_rate-value" || $fieldName == "reference_quantity-value" )
            {
                $rowData['reference_amount-value'] = $item->reference_amount;
            }

            $rowData['has_omission_build_up_quantity'] = $item->has_omission_build_up_quantity;
            $rowData['has_addition_build_up_quantity'] = $item->has_addition_build_up_quantity;
            $rowData['omission_total']                 = round($item->omission_quantity * $item->rate * $item->total_unit, 2);
            $rowData['addition_total']                 = round($item->addition_quantity * $item->rate * $item->total_unit, 2);
            $rowData['nett_omission_addition']         = $rowData['addition_total'] - $rowData['omission_total'];
        }
        catch (Exception $e)
        {
            $con->rollback();
            $errorMsg = $e->getMessage();
            $success  = false;
        }

        return $this->renderJson(array( 'success' => $success, 'errorMsg' => $errorMsg, 'data' => $rowData ));
    }

    public function executeVariationOrderItemDelete(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless($request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $item = Doctrine_Core::getTable('VariationOrderItem')->find($request->getParameter('id')) and
            !$item->is_from_rfv
        );

        $errorMsg = null;
        $con      = $item->getTable()->getConnection();

        try
        {
            $con->beginTransaction();

            $items = Doctrine_Query::create()->select('i.id')
                ->from('VariationOrderItem i')
                ->andWhere('i.root_id = ?', $item->root_id)
                ->andWhere('i.variation_order_id = ?', $item->variation_order_id)
                ->andWhere('i.lft >= ? AND i.rgt <= ?', array( $item->lft, $item->rgt ))
                ->addOrderBy('i.lft')
                ->fetchArray();

            $item->delete($con);

            $con->commit();

            $success = true;
        }
        catch (Exception $e)
        {
            $con->rollback();

            $items    = array();
            $errorMsg = $e->getMessage();
            $success  = false;
        }

        return $this->renderJson(array( 'success' => $success, 'errorMsg' => $errorMsg, 'items' => $items, 'affected_nodes' => array() ));
    }

    public function executeVariationOrderItemIndent(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless($request->isXmlHttpRequest() and $item = Doctrine_Core::getTable('VariationOrderItem')->find($request->getParameter('id')));

        $success  = false;
        $children = array();
        $errorMsg = null;
        $data     = array();
        try
        {
            if ( $item->indent() )
            {
                $data['id']    = $item->id;
                $data['level'] = $item->level;

                $children = Doctrine_Query::create()->select('i.id, i.level')
                    ->from('VariationOrderItem i')
                    ->where('i.root_id = ?', $item->root_id)
                    ->andWhere('i.lft > ? AND i.rgt < ?', array( $item->lft, $item->rgt ))
                    ->addOrderBy('i.lft')
                    ->fetchArray();

                $success = true;
            }
        }
        catch (Exception $e)
        {
            $errorMsg = $e->getMessage();
        }

        return $this->renderJson(array( 'success' => $success, 'errorMsg' => $errorMsg, 'item' => $data, 'c' => $children ));
    }

    public function executeVariationOrderItemOutdent(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless($request->isXmlHttpRequest() and $item = Doctrine_Core::getTable('VariationOrderItem')->find($request->getParameter('id')));

        $success  = false;
        $children = array();
        $errorMsg = null;
        $data     = array();
        try
        {
            if ( $item->outdent() )
            {
                $data['id']    = $item->id;
                $data['level'] = $item->level;

                $children = Doctrine_Query::create()->select('i.id, i.level')
                    ->from('VariationOrderItem i')
                    ->where('i.root_id = ?', $item->root_id)
                    ->andWhere('i.lft > ? AND i.rgt < ?', array( $item->lft, $item->rgt ))
                    ->addOrderBy('i.lft')
                    ->fetchArray();

                $success = true;
            }
        }
        catch (Exception $e)
        {
            $errorMsg = $e->getMessage();
        }

        return $this->renderJson(array( 'success' => $success, 'errorMsg' => $errorMsg, 'item' => $data, 'c' => $children ));
    }

    public function executeVariationOrderItemPaste(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless($request->isXmlHttpRequest() and $item = Doctrine_Core::getTable('VariationOrderItem')->find($request->getParameter('id')));

        $data         = array();
        $children     = array();
        $lastPosition = false;
        $success      = false;
        $errorMsg     = null;

        $targetItem = Doctrine_Core::getTable('VariationOrderItem')->find(intval($request->getParameter('target_id')));

        if ( !$targetItem )
        {
            $this->forward404Unless($targetItem = Doctrine_Core::getTable('VariationOrderItem')->find($request->getParameter('prev_item_id')));
            $lastPosition = true;
        }

        if ( $targetItem->root_id == $item->root_id and $targetItem->lft >= $item->lft and $targetItem->rgt <= $item->rgt )
        {
            $errorMsg = "cannot move item into itself";
            $results  = array( 'success' => false, 'errorMsg' => $errorMsg, 'data' => $data, 'c' => array() );

            return $this->renderJson($results);
        }

        try
        {
            $item->moveTo($targetItem, $lastPosition);

            $children = DoctrineQuery::create()->select('i.id, i.level')
                ->from('VariationOrderItem i')
                ->where('i.root_id = ?', $item->root_id)
                ->andWhere('i.lft > ? AND i.rgt < ?', array( $item->lft, $item->rgt ))
                ->addOrderBy('i.lft')
                ->fetchArray();

            $data['id']    = $item->id;
            $data['level'] = $item->level;

            $success  = true;
            $errorMsg = null;
        }
        catch (Exception $e)
        {
            $errorMsg = $e->getMessage();
        }

        return $this->renderJson(array( 'success' => $success, 'errorMsg' => $errorMsg, 'data' => $data, 'c' => $children ));
    }

    public function executeGetBuildUpQuantityItemList(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $variationOrderItem = Doctrine_Core::getTable('VariationOrderItem')->find($request->getParameter('id')) and
            $request->hasParameter('type')
        );

        switch ($request->getParameter('type'))
        {
            case "addition":
                $type = VariationOrderBuildUpQuantityItem::TYPE_ADDITIONAL_QTY;
                break;
            case "omission":
                $type = VariationOrderBuildUpQuantityItem::TYPE_OMISSION_QTY;
                break;
            default:
                throw new Exception('invalid type');
        }

        $buildUpQuantityItems = DoctrineQuery::create()->select('i.id, i.description, i.sign, i.total, ifc.column_name, ifc.value, ifc.final_value')
            ->from('VariationOrderBuildUpQuantityItem i')
            ->leftJoin('i.FormulatedColumns ifc')
            ->where('i.variation_order_item_id = ?', $variationOrderItem->id)
            ->andWhere('i.type = ?', $type)
            ->addOrderBy('i.priority ASC')
            ->fetchArray();

        $form = new BaseForm();

        $formulatedColumnNames = BillBuildUpQuantityItemTable::getFormulatedColumnNames($variationOrderItem->UnitOfMeasurement);

        foreach ( $buildUpQuantityItems as $key => $buildUpQuantityItem )
        {
            $buildUpQuantityItems[$key]['sign']        = (string) $buildUpQuantityItem['sign'];
            $buildUpQuantityItems[$key]['sign_symbol'] = BillBuildUpQuantityItemTable::getSignTextBySign($buildUpQuantityItem['sign']);
            $buildUpQuantityItems[$key]['relation_id'] = $variationOrderItem->id;
            $buildUpQuantityItems[$key]['_csrf_token'] = $form->getCSRFToken();

            foreach ( $formulatedColumnNames as $constant )
            {
                $buildUpQuantityItems[$key][$constant . '-final_value']        = 0;
                $buildUpQuantityItems[$key][$constant . '-value']              = '';
                $buildUpQuantityItems[$key][$constant . '-has_cell_reference'] = false;
                $buildUpQuantityItems[$key][$constant . '-has_formula']        = false;
            }

            foreach ( $buildUpQuantityItem['FormulatedColumns'] as $formulatedColumn )
            {
                $buildUpQuantityItems[$key][$formulatedColumn['column_name'] . '-final_value']        = $formulatedColumn['final_value'];
                $buildUpQuantityItems[$key][$formulatedColumn['column_name'] . '-value']              = $formulatedColumn['value'];
                $buildUpQuantityItems[$key][$formulatedColumn['column_name'] . '-has_cell_reference'] = false;
                $buildUpQuantityItems[$key][$formulatedColumn['column_name'] . '-has_formula']        = $formulatedColumn['value'] != $formulatedColumn['final_value'] ? true : false;
            }

            unset( $buildUpQuantityItem, $buildUpQuantityItems[$key]['FormulatedColumns'] );
        }

        $defaultLastRow = array(
            'id'          => Constants::GRID_LAST_ROW,
            'description' => '',
            'sign'        => (string) BillBuildUpQuantityItem::SIGN_POSITIVE,
            'sign_symbol' => BillBuildUpQuantityItem::SIGN_POSITIVE_TEXT,
            'relation_id' => $variationOrderItem->id,
            'total'       => '',
            '_csrf_token' => $form->getCSRFToken()
        );

        foreach ( $formulatedColumnNames as $columnName )
        {
            $defaultLastRow[$columnName . '-final_value']        = 0;
            $defaultLastRow[$columnName . '-value']              = "";
            $defaultLastRow[$columnName . '-has_cell_reference'] = false;
            $defaultLastRow[$columnName . '-has_formula']        = false;
        }

        array_push($buildUpQuantityItems, $defaultLastRow);

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $buildUpQuantityItems
        ));
    }

    public function executeGetBuildUpSummary(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $variationOrderItem = Doctrine_Core::getTable('VariationOrderItem')->find($request->getParameter('id')) and
            $request->hasParameter('type')
        );

        switch ($request->getParameter('type'))
        {
            case "addition":
                $type = VariationOrderBuildUpQuantityItem::TYPE_ADDITIONAL_QTY;
                break;
            case "omission":
                $type = VariationOrderBuildUpQuantityItem::TYPE_OMISSION_QTY;
                break;
            default:
                throw new Exception('invalid type');
        }

        $buildUpQuantitySummary = VariationOrderBuildUpQuantitySummaryTable::createByVariationOrderItemIdAndType($variationOrderItem->id, $type);

        return $this->renderJson(array(
            'apply_conversion_factor'    => $buildUpQuantitySummary->apply_conversion_factor,
            'conversion_factor_amount'   => number_format($buildUpQuantitySummary->conversion_factor_amount, 2, '.', ''),
            'conversion_factor_operator' => $buildUpQuantitySummary->conversion_factor_operator,
            'linked_total_quantity'      => number_format($buildUpQuantitySummary->linked_total_quantity, 2, '.', ''),
            'total_quantity'             => number_format($buildUpQuantitySummary->calculateTotalQuantity(), 2, '.', ''),
            'final_quantity'             => number_format($buildUpQuantitySummary->getTotalQuantityAfterConversion(), 2, '.', ''),
            'rounding_type'              => $buildUpQuantitySummary->rounding_type
        ));
    }

    public function executeBuildUpQuantityItemAdd(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless($request->isXmlHttpRequest() and $request->isMethod('post') and $request->hasParameter('type'));

        $items = array();

        $item = new VariationOrderBuildUpQuantityItem();

        $con = $item->getTable()->getConnection();

        $isFormulatedColumn = false;

        switch ($request->getParameter('type'))
        {
            case "addition":
                $type = VariationOrderBuildUpQuantityItem::TYPE_ADDITIONAL_QTY;
                break;
            case "omission":
                $type = VariationOrderBuildUpQuantityItem::TYPE_OMISSION_QTY;
                break;
            default:
                throw new Exception('invalid type');
        }

        if ( $request->hasParameter('id') and $request->getParameter('id') == Constants::GRID_LAST_ROW )
        {
            $this->forward404Unless($variationOrderItem = Doctrine_Core::getTable('VariationOrderItem')->find($request->getParameter('relation_id')));

            $formulatedColumnNames = VariationOrderBuildUpQuantityItemTable::getFormulatedColumnNames($variationOrderItem->UnitOfMeasurement);

            $previousItem = $request->getParameter('prev_item_id') > 0 ? Doctrine_Core::getTable('VariationOrderBuildUpQuantityItem')->find($request->getParameter('prev_item_id')) : null;

            $priority = $previousItem ? $previousItem->priority + 1 : 0;

            if ( $request->hasParameter('attr_name') )
            {
                $fieldName  = $request->getParameter('attr_name');
                $fieldValue = $request->getParameter('val');

                if ( in_array($fieldName, $formulatedColumnNames) )
                {
                    $isFormulatedColumn = true;
                }
                else
                {
                    $item->{'set' . sfInflector::camelize($fieldName)}($fieldValue);
                }
            }
        }
        else
        {
            $this->forward404Unless($nextItem = Doctrine_Core::getTable('VariationOrderBuildUpQuantityItem')->find($request->getParameter('before_id')));

            $variationOrderItem    = $nextItem->VariationOrderItem;
            $formulatedColumnNames = VariationOrderBuildUpQuantityItemTable::getFormulatedColumnNames($variationOrderItem->UnitOfMeasurement);

            $priority = $nextItem->priority;
        }

        try
        {
            $con->beginTransaction();

            DoctrineQuery::create()
                ->update('VariationOrderBuildUpQuantityItem')
                ->set('priority', 'priority + 1')
                ->where('priority >= ?', $priority)
                ->andWhere('variation_order_item_id = ?', $variationOrderItem->id)
                ->andWhere('type = ?', $type)
                ->execute();

            $item->variation_order_item_id = $variationOrderItem->id;
            $item->priority                = $priority;
            $item->type                    = $type;

            $item->save($con);

            if ( $isFormulatedColumn )
            {
                $formulatedColumn              = new VariationOrderBuildUpQuantityFormulatedColumn();
                $formulatedColumn->relation_id = $item->id;
                $formulatedColumn->column_name = $fieldName;
                $formulatedColumn->setFormula($fieldValue);

                $formulatedColumn->save();
            }

            $con->commit();

            $success = true;

            $errorMsg = null;

            $data = array();

            $form = new BaseForm();

            $data['id']          = $item->id;
            $data['description'] = $item->description;
            $data['sign']        = (string) $item->sign;
            $data['sign_symbol'] = $item->getSignText();
            $data['total']       = $item->calculateTotal();
            $data['relation_id'] = $variationOrderItem->id;
            $data['_csrf_token'] = $form->getCSRFToken();

            foreach ( $formulatedColumnNames as $columnName )
            {
                $formulatedColumn                          = $item->getFormulatedColumnByName($columnName, Doctrine_Core::HYDRATE_ARRAY);
                $finalValue                                = $formulatedColumn ? $formulatedColumn['final_value'] : 0;
                $data[$columnName . '-final_value']        = $finalValue;
                $data[$columnName . '-value']              = $formulatedColumn ? $formulatedColumn['value'] : '';
                $data[$columnName . '-has_cell_reference'] = false;
                $data[$columnName . '-has_formula']        = $formulatedColumn && $formulatedColumn['value'] != $formulatedColumn['final_value'] ? true : false;
            }

            array_push($items, $data);

            if ( $request->hasParameter('id') and $request->getParameter('id') == Constants::GRID_LAST_ROW )
            {
                $defaultLastRow = array(
                    'id'          => Constants::GRID_LAST_ROW,
                    'description' => '',
                    'sign'        => (string) VariationOrderBuildUpQuantityItem::SIGN_POSITIVE,
                    'sign_symbol' => VariationOrderBuildUpQuantityItem::SIGN_POSITIVE_TEXT,
                    'relation_id' => $variationOrderItem->id,
                    'total'       => '',
                    '_csrf_token' => $form->getCSRFToken()
                );

                foreach ( $formulatedColumnNames as $columnName )
                {
                    $defaultLastRow[$columnName . '-final_value']        = "";
                    $defaultLastRow[$columnName . '-value']              = "";
                    $defaultLastRow[$columnName . '-has_cell_reference'] = false;
                    $defaultLastRow[$columnName . '-has_formula']        = false;
                }

                array_push($items, $defaultLastRow);
            }

        }
        catch (Exception $e)
        {
            $con->rollback();
            $errorMsg = $e->getMessage();
            $success  = false;
        }

        return $this->renderJson(array(
            'success'  => $success,
            'items'    => $items,
            'errorMsg' => $errorMsg
        ));
    }

    public function executeBuildUpQuantityItemUpdate(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $item = Doctrine_Core::getTable('VariationOrderBuildUpQuantityItem')->find($request->getParameter('id'))
        );

        $rowData = array();
        $con     = $item->getTable()->getConnection();

        $variationOrderItem = $item->VariationOrderItem;

        $formulatedColumnNames = VariationOrderBuildUpQuantityItemTable::getFormulatedColumnNames($variationOrderItem->UnitOfMeasurement);

        try
        {
            $con->beginTransaction();

            $fieldName  = $request->getParameter('attr_name');
            $fieldValue = $request->getParameter('val');

            $affectedNodes      = array();
            $isFormulatedColumn = false;

            if ( in_array($fieldName, $formulatedColumnNames) )
            {
                $formulatedColumnTable = Doctrine_Core::getTable('VariationOrderBuildUpQuantityFormulatedColumn');

                $formulatedColumn = $formulatedColumnTable->getByRelationIdAndColumnName($item->id, $fieldName);

                $formulatedColumn->setFormula($fieldValue);

                $formulatedColumn->save($con);

                $formulatedColumn->refresh();

                $isFormulatedColumn = true;
            }
            else
            {
                $item->{'set' . sfInflector::camelize($fieldName)}($fieldValue);
                $item->save($con);
            }

            $con->commit();

            $success = true;

            $errorMsg = null;

            $item->refresh();

            if ( $isFormulatedColumn )
            {
                $referencedNodes = $formulatedColumn->getNodesRelatedByColumnName($fieldName);

                foreach ( $referencedNodes as $referencedNode )
                {
                    $node = $formulatedColumnTable->find($referencedNode['node_from']);

                    if ( $node )
                    {
                        $total = $node->BuildUpQuantityItem->calculateTotal();

                        $affectedNode = array(
                            'id'                        => $node->relation_id,
                            $fieldName . '-final_value' => $node->final_value,
                            'total'                     => $total
                        );

                        array_push($affectedNodes, $affectedNode);
                    }
                }
            }
            else
            {
                $rowData[$fieldName] = $item->$fieldName;
            }

            foreach ( $formulatedColumnNames as $columnName )
            {
                $formulatedColumn                             = $item->getFormulatedColumnByName($columnName, Doctrine_Core::HYDRATE_ARRAY);
                $finalValue                                   = $formulatedColumn ? $formulatedColumn['final_value'] : 0;
                $rowData[$columnName . '-final_value']        = $finalValue;
                $rowData[$columnName . '-value']              = $formulatedColumn ? $formulatedColumn['value'] : '';
                $rowData[$columnName . '-has_cell_reference'] = false;
                $rowData[$columnName . '-has_formula']        = $formulatedColumn && $formulatedColumn['value'] != $formulatedColumn['final_value'] ? true : false;
            }

            $rowData['sign']           = (string) $item->sign;
            $rowData['sign_symbol']    = $item->getSignText();
            $rowData['total']          = $item->calculateTotal();
            $rowData['affected_nodes'] = $affectedNodes;
        }
        catch (Exception $e)
        {
            $con->rollback();
            $errorMsg = $e->getMessage();
            $success  = false;
        }

        return $this->renderJson(array(
            'success'  => $success,
            'errorMsg' => $errorMsg,
            'data'     => $rowData
        ));
    }

    public function executeBuildUpQuantityItemDelete(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless($request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $buildUpItem = Doctrine_Core::getTable('VariationOrderBuildUpQuantityItem')->find($request->getParameter('id'))
        );

        $errorMsg = null;

        try
        {
            $item['id']    = $buildUpItem->id;
            $affectedNodes = $buildUpItem->delete();

            $success = true;
        }
        catch (Exception $e)
        {
            $errorMsg      = $e->getMessage();
            $item          = array();
            $affectedNodes = array();
            $success       = false;
        }

        return $this->renderJson(array( 'success' => $success, 'errorMsg' => $errorMsg, 'item' => $item, 'affected_nodes' => $affectedNodes ));
    }

    public function executeBuildUpQuantityItemPaste(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $buildUpQuantityItem = Doctrine_Core::getTable('VariationOrderBuildUpQuantityItem')->find($request->getParameter('id'))
        );

        $data         = array();
        $lastPosition = false;
        $success      = false;
        $errorMsg     = null;

        $targetBuildUpQuantityItem = Doctrine_Core::getTable('VariationOrderBuildUpQuantityItem')->find(intval($request->getParameter('target_id')));
        if ( !$targetBuildUpQuantityItem )
        {
            $this->forward404Unless($targetBuildUpQuantityItem = Doctrine_Core::getTable('VariationOrderBuildUpQuantityItem')->find($request->getParameter('prev_item_id')));
            $lastPosition = true;
        }

        if ( $request->getParameter('type') == 'cut' and $targetBuildUpQuantityItem->id == $buildUpQuantityItem->id )
        {
            $errorMsg = "cannot move item into itself";
            $results  = array( 'success' => false, 'errorMsg' => $errorMsg, 'data' => $data, 'c' => array() );

            return $this->renderJson($results);
        }

        switch ($request->getParameter('type'))
        {
            case 'cut':
                try
                {
                    $buildUpQuantityItem->moveTo($targetBuildUpQuantityItem->priority, $lastPosition);

                    $data['id'] = $buildUpQuantityItem->id;

                    $success  = true;
                    $errorMsg = null;
                }
                catch (Exception $e)
                {
                    $errorMsg = $e->getMessage();
                }
                break;
            case 'copy':
                try
                {
                    $formulatedColumnNames  = VariationOrderBuildUpQuantityItemTable::getFormulatedColumnNames($buildUpQuantityItem->VariationOrderItem->UnitOfMeasurement);
                    $newBuildUpQuantityItem = $buildUpQuantityItem->copyTo($targetBuildUpQuantityItem, $lastPosition);

                    $form = new BaseForm();

                    $data['id']          = $newBuildUpQuantityItem->id;
                    $data['description'] = $newBuildUpQuantityItem->description;
                    $data['sign']        = (string) $newBuildUpQuantityItem->sign;
                    $data['sign_symbol'] = $newBuildUpQuantityItem->getSigntext();
                    $data['relation_id'] = $newBuildUpQuantityItem->variation_order_item_id;
                    $data['total']       = $newBuildUpQuantityItem->calculateTotal();
                    $data['_csrf_token'] = $form->getCSRFToken();

                    foreach ( $formulatedColumnNames as $constant )
                    {
                        $formulatedColumn                        = $newBuildUpQuantityItem->getFormulatedColumnByName($constant, Doctrine_Core::HYDRATE_ARRAY);
                        $finalValue                              = $formulatedColumn ? $formulatedColumn['final_value'] : 0;
                        $data[$constant . '-final_value']        = $finalValue;
                        $data[$constant . '-value']              = $formulatedColumn ? $formulatedColumn['value'] : '';
                        $data[$constant . '-has_cell_reference'] = false;
                        $data[$constant . '-has_formula']        = $formulatedColumn && $formulatedColumn['value'] != $formulatedColumn['final_value'] ? true : false;
                    }

                    $success  = true;
                    $errorMsg = null;
                }
                catch (Exception $e)
                {
                    $errorMsg = $e->getMessage();
                }
                break;
            default:
                throw new Exception('invalid paste operation');
        }

        return $this->renderJson(array( 'success' => $success, 'errorMsg' => $errorMsg, 'data' => $data ));
    }

    public function executeBuildUpSummaryRoundingUpdate(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $variationOrderItem = Doctrine_Core::getTable('VariationOrderItem')->find($request->getParameter('id')) and
            $request->hasParameter('type')
        );

        switch ($request->getParameter('type'))
        {
            case "addition":
                $type = VariationOrderBuildUpQuantityItem::TYPE_ADDITIONAL_QTY;
                break;
            case "omission":
                $type = VariationOrderBuildUpQuantityItem::TYPE_OMISSION_QTY;
                break;
            default:
                throw new Exception('invalid type');
        }

        $buildUpQuantitySummary = VariationOrderBuildUpQuantitySummaryTable::getByVariationOrderItemIdAndType($variationOrderItem->id, $type);

        $buildUpQuantitySummary->rounding_type = $request->getParameter('rounding_type');

        $buildUpQuantitySummary->save();
        $buildUpQuantitySummary->refresh();

        return $this->renderJson(array(
            'total_quantity' => number_format($buildUpQuantitySummary->calculateTotalQuantity(), 2, '.', ''),
            'final_quantity' => number_format($buildUpQuantitySummary->getTotalQuantityAfterConversion(), 2, '.', ''),
            'rounding_type'  => $buildUpQuantitySummary->rounding_type
        ));
    }

    public function executeBuildUpSummaryApplyConversionFactorUpdate(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $variationOrderItem = Doctrine_Core::getTable('VariationOrderItem')->find($request->getParameter('id')) and
            $request->hasParameter('type')
        );

        switch ($request->getParameter('type'))
        {
            case "addition":
                $type = VariationOrderBuildUpQuantityItem::TYPE_ADDITIONAL_QTY;
                break;
            case "omission":
                $type = VariationOrderBuildUpQuantityItem::TYPE_OMISSION_QTY;
                break;
            default:
                throw new Exception('invalid type');
        }

        $value = $request->getParameter('value');

        $buildUpQuantitySummary = VariationOrderBuildUpQuantitySummaryTable::getByVariationOrderItemIdAndType($variationOrderItem->id, $type);

        $buildUpQuantitySummary->apply_conversion_factor = $value;
        $buildUpQuantitySummary->save();

        $buildUpQuantitySummary->refresh();

        return $this->renderJson(array(
            'conversion_factor_amount'   => number_format($buildUpQuantitySummary->conversion_factor_amount, 2, '.', ''),
            'conversion_factor_operator' => $buildUpQuantitySummary->conversion_factor_operator,
            'final_quantity'             => number_format($buildUpQuantitySummary->getTotalQuantityAfterConversion(), 2, '.', '')
        ));
    }

    public function executeBuildUpSummaryConversionFactorUpdate(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $variationOrderItem = Doctrine_Core::getTable('VariationOrderItem')->find($request->getParameter('id')) and
            $request->hasParameter('type')
        );

        switch ($request->getParameter('type'))
        {
            case "addition":
                $type = VariationOrderBuildUpQuantityItem::TYPE_ADDITIONAL_QTY;
                break;
            case "omission":
                $type = VariationOrderBuildUpQuantityItem::TYPE_OMISSION_QTY;
                break;
            default:
                throw new Exception('invalid type');
        }

        $buildUpQuantitySummary = VariationOrderBuildUpQuantitySummaryTable::getByVariationOrderItemIdAndType($variationOrderItem->id, $type);

        $val = $request->getParameter('val');

        switch ($request->getParameter('token'))
        {
            case 'amount':
                $conversionFactorAmount                           = strlen($val) > 0 ? floatval($val) : 0;
                $buildUpQuantitySummary->conversion_factor_amount = $conversionFactorAmount;
                break;
            case 'operator':
                $buildUpQuantitySummary->conversion_factor_operator = $val;
                break;
            default:
                break;
        }

        $buildUpQuantitySummary->save();
        $buildUpQuantitySummary->refresh();

        return $this->renderJson(array(
            'conversion_factor_amount'   => number_format($buildUpQuantitySummary->conversion_factor_amount, 2, '.', ''),
            'final_quantity'             => number_format($buildUpQuantitySummary->getTotalQuantityAfterConversion(), 2, '.', ''),
            'conversion_factor_operator' => $buildUpQuantitySummary->conversion_factor_operator
        ));
    }

    public function executeGetClaimList(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $variationOrder = Doctrine_Core::getTable('VariationOrder')->find($request->getParameter('id'))
        );

        $pdo  = $variationOrder->getTable()->getConnection()->getDbh();

        $claimCertClause = "";
        if( $request->hasParameter('claimRevision') )
        {
            $claimCertIds = array_column(PostContractClaimRevisionTable::getClaimCertificates($request->getParameter('claimRevision'), '<='), 'id');

            if( count($claimCertIds) > 0 ) $claimCertClause = "AND claim_certificate_id IN (" . implode(',', $claimCertIds) . ")";
        }

        $stmt = $pdo->prepare("SELECT c.id, c.revision, c.variation_order_id, c.status, c.is_viewing
            FROM " . VariationOrderClaimTable::getInstance()->getTableName() . " c
            LEFT JOIN " . VariationOrderClaimClaimCertificateTable::getInstance()->getTableName() . " xref on xref.variation_order_claim_id = c.id
            WHERE c.variation_order_id  = {$variationOrder->id}
            {$claimCertClause}
            AND c.deleted_at IS NULL
            ORDER BY c.revision ASC
        ");

        $stmt->execute();

        $claims = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $claimCertificateVersions = array();
        if($variationOrder->ProjectStructure->PostContract->published_type == PostContract::PUBLISHED_TYPE_NEW)
        {
            $stmt = $pdo->prepare("SELECT DISTINCT x.variation_order_claim_id, rev.version
                FROM ".VariationOrderClaimClaimCertificateTable::getInstance()->getTableName()." x
                JOIN ".ClaimCertificateTable::getInstance()->getTableName()." c ON c.id = x.claim_certificate_id
                JOIN ".PostContractClaimRevisionTable::getInstance()->getTableName()." rev ON rev.id = c.post_contract_claim_revision_id
                WHERE rev.post_contract_id = :postContractId AND rev.deleted_at IS NULL ORDER BY rev.version ASC");

            $stmt->execute(array( 'postContractId' => $variationOrder->ProjectStructure->PostContract->id ));

            $claimCertificateVersions = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        }

        $count = count($claims);
        $form  = new BaseForm();

        foreach ( $claims as $key => $claim )
        {
            $claims[$key]['claim_cert_number'] = array_key_exists($claim['id'], $claimCertificateVersions) ? $claimCertificateVersions[$claim['id']] : "";
            $claims[$key]['can_be_edited']     = $claim['status'] == VariationOrderClaim::STATUS_CLOSED ? false : true;
            $claims[$key]['can_be_deleted']    = $claim['status'] == VariationOrderClaim::STATUS_CLOSED ? false : true;
            $claims[$key]['_csrf_token']       = $form->getCSRFToken();

            unset( $claim );
        }

        array_push($claims, array(
            'id'                 => Constants::GRID_LAST_ROW,
            'revision'           => -1,
            'variation_order_id' => $variationOrder->id,
            'claim_cert_number'  => "",
            'status'             => -1,
            'is_viewing'         => false,
            'can_be_edited'      => false,
            'can_be_deleted'     => false,
            '_csrf_token'        => $form->getCSRFToken()
        ));

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $claims
        ));
    }

    public function executeClaimAdd(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $variationOrder = Doctrine_Core::getTable('VariationOrder')->find($request->getParameter('id'))
        );

        $con = $variationOrder->getTable()->getConnection();

        try
        {
            $con->beginTransaction();
            $form = new BaseForm();

            if ( $variationOrder->canAddNewClaim() )
            {
                $claim                     = new VariationOrderClaim();
                $claim->variation_order_id = $variationOrder->id;

                $claim->save();
            }
            else
            {
                throw new Exception('Cannot add new claim because there is still an in progress claim for variation order with id:' . $variationOrder->id);
            }

            $con->commit();

            $success = true;
            $errorMsg = null;
        }
        catch (Exception $e)
        {
            $con->rollback();
            $errorMsg = $e->getMessage();
            $success  = false;
        }

        return $this->renderJson(array( 'success' => $success, 'errorMsg' => $errorMsg ));
    }

    public function executeClaimUpdate(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $claim = Doctrine_Core::getTable('VariationOrderClaim')->find($request->getParameter('id'))
        );

        $rowData = array();

        $con = $claim->getTable()->getConnection();

        try
        {
            $con->beginTransaction();

            $fieldName  = $request->getParameter('attr_name');
            $fieldValue = $request->getParameter('val');

            $claim->{'set' . sfInflector::camelize($fieldName)}($fieldValue);

            $claim->save($con);

            $con->commit();

            $success = true;

            $errorMsg = null;

            $rowData[$fieldName]       = $claim->{$request->getParameter('attr_name')};
            $rowData['can_be_edited']  = $claim->status == VariationOrderClaim::STATUS_CLOSED ? false : true;
            $rowData['can_be_deleted'] = $claim->status == VariationOrderClaim::STATUS_CLOSED ? false : true;

            if($claim->status == VariationOrderClaim::STATUS_CLOSED && $claim->VariationOrder->ProjectStructure->PostContract->published_type == PostContract::PUBLISHED_TYPE_NEW)
            {
                $rowData['claim_cert_number'] = $claim->ClaimCertificateXref->ClaimCertificate->PostContractClaimRevision->version;
                $rowData['claim_cert_number'] = empty($rowData['claim_cert_number']) ? "" : $rowData['claim_cert_number'];
            }
        }
        catch (Exception $e)
        {
            $con->rollback();
            $errorMsg = $e->getMessage();
            $success  = false;
        }

        return $this->renderJson(array( 'success' => $success, 'errorMsg' => $errorMsg, 'data' => $rowData ));
    }

    public function executeClaimDelete(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $claim = Doctrine_Core::getTable('VariationOrderClaim')->find($request->getParameter('id'))
        );

        try
        {
            $claim->delete();

            $success  = true;
            $errorMsg = null;
        }
        catch (Exception $e)
        {
            $errorMsg = $e->getMessage();
            $success  = false;
        }

        return $this->renderJson(array( 'success' => $success, 'errorMsg' => $errorMsg ));
    }

    public function executeViewClaimRevision(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $claim = Doctrine_Core::getTable('VariationOrderClaim')->find($request->getParameter('id'))
        );

        $con = $claim->getTable()->getConnection();

        try
        {
            $con->beginTransaction();

            $viewingClaim = $claim->VariationOrder->getViewingClaim();

            $claim->setAsViewingClaim();

            $con->commit();

            $success  = true;
            $errorMsg = null;

            $items = array(
                array( 'id' => $claim->id, 'is_viewing' => $claim->is_viewing ),
                array( 'id' => ($viewingClaim) ? $viewingClaim->id : -1, 'is_viewing' => ($viewingClaim) ? $viewingClaim->is_viewing : false )
            );
        }
        catch (Exception $e)
        {
            $con->rollback();
            $errorMsg = $e->getMessage();
            $items    = array();
            $success  = false;
        }

        return $this->renderJson(array( 'success' => $success, 'errorMsg' => $errorMsg, 'items' => $items ));
    }

    public function executeClaimItemUpdate(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $variationOrderItem = Doctrine_Core::getTable('VariationOrderItem')->find($request->getParameter('id'))
        );

        $rowData = array();

        $con = $variationOrderItem->getTable()->getConnection();

        try
        {
            $con->beginTransaction();

            $fieldName  = $request->getParameter('attr_name');
            $fieldValue = $request->getParameter('val');

            $claimItem = $variationOrderItem->updateClaimItem($fieldName, $fieldValue);

            $con->commit();

            $success = true;

            $errorMsg = null;

            $currentAmount = $claimItem->current_amount;
            $currentPercentage = $claimItem->current_percentage;
            $currentQty = $claimItem->current_quantity;
            $upToDateAmount = $claimItem->up_to_date_amount;
            $upToDatePercentage = $claimItem->up_to_date_percentage;
            $upToDateQty = $claimItem->up_to_date_quantity;

            if(($variationOrderItem->rate * $variationOrderItem->addition_quantity) - ($variationOrderItem->rate * $variationOrderItem->omission_quantity) < 0)
            {
                $currentAmount = abs($currentAmount) * -1;
                $currentPercentage = abs($currentPercentage) * -1;
                $currentQty = abs($currentQty) * -1;
                $upToDateAmount = abs($upToDateAmount) * -1;
                $upToDatePercentage = abs($upToDatePercentage) * -1;
                $upToDateQty = abs($upToDateQty) * -1;
            }

            $rowData['current_quantity-value']      = $currentQty;
            $rowData['current_percentage-value']    = $currentPercentage;
            $rowData['current_amount-value']        = $currentAmount;
            $rowData['up_to_date_quantity-value']   = $upToDateQty;
            $rowData['up_to_date_percentage-value'] = $upToDatePercentage;
            $rowData['up_to_date_amount-value']     = $upToDateAmount;
            $rowData['remarks']                     = $claimItem->remarks;
        }
        catch (Exception $e)
        {
            $con->rollback();
            $errorMsg = $e->getMessage();
            $success  = false;
        }

        return $this->renderJson(array( 'success' => $success, 'errorMsg' => $errorMsg, 'data' => $rowData ));
    }

    public function executeGetBillList(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $variationOrder = Doctrine_Core::getTable('VariationOrder')->find($request->getParameter('void'))
        );

        $project = $variationOrder->ProjectStructure;

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

        $queryBills = DoctrineQuery::create()->select('p.id, p.title')
            ->from('ProjectStructure p')
            ->leftJoin('p.BillType t')
            ->where('p.root_id = ?', $project->id)
            ->andWhere('p.type = ?', ProjectStructure::TYPE_BILL);
        
        if(!empty($tenderAlternativeProjectStructureIds))
        {
            $queryBills->whereIn('p.id', $tenderAlternativeProjectStructureIds);
        }

        $bills = $queryBills->addOrderBy('p.lft, p.level ASC')
            ->fetchArray();

        array_push($bills, array(
            'id'    => Constants::GRID_LAST_ROW,
            'title' => ''
        ));

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $bills
        ));
    }

    public function executeGetTypeReferenceList(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('bid'))
        );

        $variationOrderItem = Doctrine_Core::getTable('VariationOrderItem')->find(intval($request->getParameter('vid')));

        $billColumnSettingItems = array();
        $records                = array();
        $pdo                    = $bill->getTable()->getConnection()->getDbh();

        $typeItems = DoctrineQuery::create()->select('t.id, t.post_contract_id, t.bill_column_setting_id, t.counter, t.new_name')
            ->from('PostContractStandardClaimTypeReference t')
            ->leftJoin('t.BillColumnSetting cs')
            ->where('t.post_contract_id = ? AND cs.project_structure_id = ?', array( $bill->getRoot()->PostContract->id, $bill->id ))
            ->fetchArray();

        foreach ( $typeItems as $typeItem )
        {
            $billColumnSettingItems[$typeItem['bill_column_setting_id']][$typeItem['counter']] = array(
                'id'       => $typeItem['id'],
                'new_name' => $typeItem['new_name']
            );
        }

        $billColumnSettings = DoctrineQuery::create()->select('cs.*')
            ->from('BillColumnSetting cs')
            ->where('cs.project_structure_id = ? ', $bill->id)
            ->fetchArray();

        $form                     = new BaseForm();
        $assignedTypeReferenceIds = array();

        if ( $variationOrderItem )
        {
            $stmt = $pdo->prepare("SELECT type_reference_id FROM " . VariationOrderItemUnitTable::getInstance()->getTableName() . "
            WHERE variation_order_item_id = " . $variationOrderItem->id);
            $stmt->execute();

            $assignedTypeReferenceIds = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
        }

        foreach ( $billColumnSettings as $column )
        {
            $count = $column['quantity'];

            array_push($records, array(
                'id'          => 'type' . '-' . $column['id'],
                'description' => $column['name'],
                'new_name'    => '',
                'level'       => 0
            ));

            for ( $i = 1; $i <= $count; $i ++ )
            {
                $record['id']            = $column['id'] . '-' . $i;
                $record['description']   = 'Unit ' . $i;
                $record['new_name']      = "";
                $record['relation_id']   = $column['id'];
                $record['relation_name'] = $column['name'];
                $record['level']         = 1;
                $record['selected']      = false;
                $record['_csrf_token']   = $form->getCSRFToken();

                if ( array_key_exists($column['id'], $billColumnSettingItems) and array_key_exists($i, $billColumnSettingItems[$column['id']]) )
                {
                    if ( $billColumnSettingItems[$column['id']][$i]['new_name'] != null and strlen($billColumnSettingItems[$column['id']][$i]['new_name']) > 0 )
                    {
                        $record['description'] = $billColumnSettingItems[$column['id']][$i]['new_name'];
                    }

                    $record['selected'] = in_array($billColumnSettingItems[$column['id']][$i]['id'], $assignedTypeReferenceIds) ? true : false;
                }

                array_push($records, $record);

                unset( $record );
            }
        }

        unset( $billColumnSettings, $assignedTypeReferenceIds );

        array_push($records, array(
            'id'          => Constants::GRID_LAST_ROW,
            'description' => "",
            'new_name'    => '',
            'level'       => 0
        ));

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $records
        ));
    }

    public function executeGetVariationOrderItemUnitList(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $variationOrderItem = Doctrine_Core::getTable('VariationOrderItem')->find($request->getParameter('vid')) and
            $variationOrderItem->bill_item_id > 0
        );

        $billColumnSettingItems = array();
        $records                = array();
        $pdo                    = $variationOrderItem->getTable()->getConnection()->getDbh();

        $stmt = $pdo->prepare("SELECT cs.*
        FROM " . BillColumnSettingTable::getInstance()->getTableName() . " cs
        JOIN " . PostContractStandardClaimTypeReferenceTable::getInstance()->getTablename() . " r ON r.bill_column_setting_id = cs.id
        JOIN " . VariationOrderItemUnitTable::getInstance()->getTableName() . " u ON u.type_reference_id = r.id
        WHERE u.variation_order_item_id = " . $variationOrderItem->id . "
        AND cs.project_structure_id = " . $variationOrderItem->BillItem->Element->project_structure_id . " AND cs.deleted_at IS NULL");

        $stmt->execute();

        $billColumnSetting = $stmt->fetch(PDO::FETCH_ASSOC);// always have bill column setting since this is an omitted bill items

        $form = new BaseForm();

        $stmt = $pdo->prepare("SELECT type_reference_id FROM " . VariationOrderItemUnitTable::getInstance()->getTableName() . " WHERE variation_order_item_id = " . $variationOrderItem->id);

        $stmt->execute();

        $assignedTypeReferenceIds = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);

        $count = $billColumnSetting['quantity'];

        //get new name from PostContractStandardClaimTypeReferenceTable for case where there is no assignment in VariationOrderItemUnitTable
        $stmt = $pdo->prepare("SELECT DISTINCT r.id, r.bill_column_setting_id, r.post_contract_id, r.counter, r.new_name
        FROM " . PostContractStandardClaimTypeReferenceTable::getInstance()->getTablename() . " r
        JOIN " . PostContractTable::getInstance()->getTableName() . " pc ON r.post_contract_id = pc.id
        WHERE r.bill_column_setting_id = " . $billColumnSetting['id'] . "
        AND pc.project_structure_id = " . $variationOrderItem->VariationOrder->project_structure_id);

        $stmt->execute();

        $postContractTypeReferences = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ( $postContractTypeReferences as $postContractTypeReference )
        {
            $billColumnSettingItems[$postContractTypeReference['counter']] = array(
                'id'       => $postContractTypeReference['id'],
                'new_name' => $postContractTypeReference['new_name']
            );
        }

        $unselectableUnits = array();

        foreach(VariationOrderItemTable::getBillItemsTaggedToOtherVariationOrders($variationOrderItem->VariationOrder) as $item)
        {
            if( $item['bill_item_id'] == $variationOrderItem->bill_item_id ) $unselectableUnits[] = $item['counter'];
        }

        array_push($records, array(
            'id'          => 'type' . '-' . $billColumnSetting['id'],
            'description' => $billColumnSetting['name'],
            'new_name'    => '',
            'level'       => 0
        ));

        for ( $i = 1; $i <= $count; $i ++ )
        {
            if(in_array($i, $unselectableUnits)) continue;

            $record['id']            = $billColumnSetting['id'] . '-' . $i;
            $record['description']   = 'Unit ' . $i;
            $record['new_name']      = "";
            $record['relation_id']   = $billColumnSetting['id'];
            $record['relation_name'] = $billColumnSetting['name'];
            $record['level']         = 1;
            $record['selected']      = false;
            $record['_csrf_token']   = $form->getCSRFToken();

            if ( array_key_exists($i, $billColumnSettingItems) )
            {
                if ( $billColumnSettingItems[$i]['new_name'] != null and strlen($billColumnSettingItems[$i]['new_name']) > 0 )
                {
                    $record['description'] = $billColumnSettingItems[$i]['new_name'];
                }

                $record['selected'] = in_array($billColumnSettingItems[$i]['id'], $assignedTypeReferenceIds) ? true : false;
            }

            array_push($records, $record);

            unset( $record );
        }

        unset( $billColumnSettings, $assignedTypeReferenceIds );

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $records
        ));
    }

    public function executeTotalUnitUpdate(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $variationOrderItem = Doctrine_Core::getTable('VariationOrderItem')->find($request->getParameter('vid')) and
            $request->hasParameter("ids")
        );

        if ( $variationOrderItem->bill_item_id > 0 )
        {
            $bill = $variationOrderItem->BillItem->Element->ProjectStructure;
        }
        else
        {
            $this->forward404Unless($bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('bid')));
        }

        try
        {
            $variationOrderItem->updateTotalUnit($request->getParameter("ids"), $bill);

            $errorMsg = null;
            $success  = true;
        }
        catch (Exception $e)
        {
            $errorMsg = $e->getMessage();
            $success  = false;
        }

        return $this->renderJson(array( 'success' => $success, 'errorMsg' => $errorMsg, 'id' => $variationOrderItem->id ));
    }

    public function executeRenameUnit(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $request->hasParameter('id')
        );

        $exploded = explode("-", $request->getParameter("id"));

        $this->forward404Unless($billColumnSetting = Doctrine_Core::getTable('BillColumnSetting')->find($exploded[0]));

        $con = $billColumnSetting->getTable()->getConnection();

        try
        {
            $con->beginTransaction();

            $typeReference = DoctrineQuery::create()->select('r.*')
                ->from('PostContractStandardClaimTypeReference r')
                ->where('r.post_contract_id = ? ', $billColumnSetting->ProjectStructure->getRoot()->PostContract->id)
                ->andWhere('r.bill_column_setting_id = ?', $billColumnSetting->id)
                ->andWhere('r.counter = ?', $exploded[1])
                ->fetchOne();

            if ( !$typeReference )
            {
                $typeReference                         = new PostContractStandardClaimTypeReference();
                $typeReference->post_contract_id       = $billColumnSetting->ProjectStructure->getRoot()->PostContract->id;
                $typeReference->bill_column_setting_id = $billColumnSetting->id;
                $typeReference->counter                = $exploded[1];
            }

            $typeReference->new_name = $request->getParameter("val");

            $typeReference->save();

            $con->commit();

            $errorMsg = null;
            $success  = true;

            $data["description"] = $typeReference->new_name;
            $data["new_name"]    = "";
        }
        catch (Exception $e)
        {
            $con->rollback();
            $errorMsg = $e->getMessage();
            $success  = false;
            $data     = array();
        }

        return $this->renderJson(array( 'success' => $success, 'errorMsg' => $errorMsg, "data" => $data ));
    }

    public function executeGetBillElementList(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('bid')) and
            $request->hasParameter("tid") and strlen($request->getParameter("tid")) > 0
        );

        $typeUnitData = explode("-", $request->getParameter("tid"));

        $this->forward404Unless($billColumnSetting = Doctrine_Core::getTable('BillColumnSetting')->find($typeUnitData[0]));

        $typeReference = DoctrineQuery::create()->select('t.id')
            ->from('PostContractStandardClaimTypeReference t')
            ->where('t.bill_column_setting_id = ?', array( $billColumnSetting->id ))
            ->andWhere('t.post_contract_id = ?', array( $bill->getRoot()->PostContract->id ))
            ->andWhere('t.counter = ? ', array( $typeUnitData[1] ))
            ->limit(1)
            ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
            ->fetchOne();

        if ( !$typeReference )
        {
            $typeReference                         = new PostContractStandardClaimTypeReference();
            $typeReference->bill_column_setting_id = $billColumnSetting->id;
            $typeReference->post_contract_id       = $bill->getRoot()->PostContract->id;
            $typeReference->counter                = $typeUnitData[1];

            $typeReference->save();
        }

        $elements = DoctrineQuery::create()->select('e.id, e.description')
            ->from('BillElement e')
            ->where('e.project_structure_id = ?', $bill->id)
            ->addOrderBy('e.priority ASC')
            ->fetchArray();

        array_push($elements, array(
            'id'          => Constants::GRID_LAST_ROW,
            'description' => ""
        ));

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $elements
        ));
    }

    public function executeGetBillItemList(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $element = Doctrine_Core::getTable('BillElement')->find($request->getParameter('eid')) and
            $variationOrder = Doctrine_Core::getTable('VariationOrder')->find($request->getParameter('void')) and
            $request->hasParameter("tid") and strlen($request->getParameter("tid")) > 0
        );

        $typeUnitData = explode("-", $request->getParameter("tid"));

        $this->forward404Unless($billColumnSetting = Doctrine_Core::getTable('BillColumnSetting')->find($typeUnitData[0]));

        $pdo = $element->getTable()->getConnection()->getDbh();

        $unselectableBillItemIds = array();

        foreach(VariationOrderItemTable::getBillItemsTaggedToOtherVariationOrders($variationOrder) as $item)
        {
            if( $item['counter'] == $typeUnitData[1] ) $unselectableBillItemIds[] = $item['bill_item_id'];
        }

        $unselectableBillItemIdsClause = ( count($unselectableBillItemIds) > 0 ) ? "AND c.id not in (" . implode(',', $unselectableBillItemIds) . ")" : "";

        $stmt = $pdo->prepare("SELECT DISTINCT p.id, p.element_id, p.root_id, p.description, p.type, p.uom_id, p.level, p.priority, p.lft,
            uom.symbol AS uom_symbol, r.bill_ref_element_no, r.bill_ref_page_no, r.bill_ref_char, r.rate,
            pc.supply_rate AS pc_supply_rate, pc.wastage_percentage AS pc_wastage_percentage,
            pc.wastage_amount AS pc_wastage_amount, pc.labour_for_installation AS pc_labour_for_installation,
            pc.other_cost AS pc_other_cost, pc.profit_percentage AS pc_profit_percentage,
            pc.profit_amount AS pc_profit_amount, pc.total AS pc_total
            FROM " . BillItemTable::getInstance()->getTableName() . " c
            JOIN " . BillItemTable::getInstance()->getTableName() . " p ON c.lft BETWEEN p.lft AND p.rgt
            LEFT JOIN " . UnitOfMeasurementTable::getInstance()->getTableName() . " uom ON p.uom_id = uom.id AND uom.deleted_at IS NULL
            LEFT JOIN " . PostContractBillItemRateTable::getInstance()->getTableName() . " r ON p.id = r.bill_item_id AND r.post_contract_id = " . $element->ProjectStructure->getRoot()->PostContract->id . "
            LEFT JOIN " . BillItemPrimeCostRateTable::getInstance()->getTableName() . " pc ON p.id = pc.bill_item_id AND pc.deleted_at IS NULL
            JOIN " . BillElementTable::getInstance()->getTableName() . " e ON p.element_id = e.id
            WHERE e.id = " . $element->id . " AND c.root_id = p.root_id
            AND c.id NOT IN (SELECT DISTINCT i.id FROM " . BillItemTable::getInstance()->getTableName() . " i
            JOIN " . BillItemTypeReferenceTable::getInstance()->getTableName() . " r ON r.bill_item_id = i.id
            WHERE r.bill_column_setting_id =" . $billColumnSetting->id . " AND i.element_id = " . $element->id . " AND include IS FALSE
            AND r.deleted_at IS NULL AND i.deleted_at IS NULL and i.project_revision_deleted_at IS NULL)
            {$unselectableBillItemIdsClause}
            AND c.project_revision_deleted_at IS NULL AND c.deleted_at IS NULL
            AND p.project_revision_deleted_at IS NULL AND p.deleted_at IS NULL
            AND e.deleted_at IS NULL ORDER BY p.priority, p.lft, p.level ASC");

        $stmt->execute();

        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $billItemTypeFormulatedColumnName = $billColumnSetting->use_original_quantity ? BillItemTypeReference::FORMULATED_COLUMN_QTY_PER_UNIT : BillItemTypeReference::FORMULATED_COLUMN_QTY_PER_UNIT_REMEASUREMENT;

        $stmt = $pdo->prepare("SELECT DISTINCT r.bill_item_id, ifc.final_value, ifc.has_build_up FROM
            " . BillItemTypeReferenceTable::getInstance()->getTableName() . " r
            JOIN " . BillItemTypeReferenceFormulatedColumnTable::getInstance()->getTableName() . " ifc ON ifc.relation_id = r.id
            WHERE r.bill_column_setting_id = " . $billColumnSetting->id . " AND r.include IS TRUE
            AND ifc.column_name = '" . $billItemTypeFormulatedColumnName . "' AND ifc.final_value <> 0
            AND r.deleted_at IS NULL AND ifc.deleted_at IS NULL");

        $stmt->execute();

        $quantities = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $pageNoPrefix = $element->ProjectStructure->BillLayoutSetting->page_no_prefix;

        $stmt = $pdo->prepare("SELECT DISTINCT i.bill_item_id FROM " . VariationOrderItemTable::getInstance()->getTableName() . " i
            LEFT JOIN " . VariationOrderItemUnitTable::getInstance()->getTableName() . " u ON u.variation_order_item_id = i.id
            JOIN " . PostContractStandardClaimTypeReferenceTable::getInstance()->getTableName() . " r ON u.type_reference_id = r.id
            WHERE r.bill_column_setting_id = " . $billColumnSetting->id . " AND counter = " . $typeUnitData[1] . "
            AND post_contract_id = " . $element->ProjectStructure->getRoot()->PostContract->id . "
            AND i.bill_item_id > 0 AND i.variation_order_id = " . $variationOrder->id . " AND i.deleted_at IS NULL");

        $stmt->execute();

        $variationOrderItemUnitReferences = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);

        $notSelectableItemType = array(
            BillItem::TYPE_HEADER,
            BillItem::TYPE_HEADER_N
        );

        foreach ( $items as $key => $item )
        {
            $items[$key]['bill_ref']                  = BillItemTable::generateBillRef($pageNoPrefix, $item['bill_ref_element_no'], $item['bill_ref_page_no'], $item['bill_ref_char']);
            $items[$key]['type']                      = (string) $item['type'];
            $items[$key]['uom_id']                    = $item['uom_id'] > 0 ? (string) $item['uom_id'] : '-1';
            $items[$key]['qty_per_unit-value']        = 0;
            $items[$key]['qty_per_unit-final_value']  = 0;
            $items[$key]['qty_per_unit-has_build_up'] = false;

            if ( ! in_array($item['type'], $notSelectableItemType) )
            {
                $items[$key]['selected'] = in_array($item['id'], $variationOrderItemUnitReferences) ? true : false;
            }

            foreach ( $quantities as $quantity )
            {
                if ( $quantity['bill_item_id'] == $item['id'] )
                {
                    $items[$key]['qty_per_unit-value']        = $quantity['final_value'];
                    $items[$key]['qty_per_unit-final_value']  = $quantity['final_value'];
                    $items[$key]['qty_per_unit-has_build_up'] = $quantity['has_build_up'];

                    unset( $quantity );
                }
            }

            unset( $item );
        }

        unset( $billItemIds, $quantities, $variationOrderItemUnitReferences );

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $items
        ));
    }

    public function executeOmitBillItems(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $variationOrder = Doctrine_Core::getTable('VariationOrder')->find($request->getParameter('void')) and
            $request->hasParameter("vid") and
            $request->hasParameter("sid") and
            $request->hasParameter("usid") and
            $request->hasParameter("uid") and strlen($request->getParameter("uid")) > 0
        );

        try
        {
            $typeUnitData = explode("-", $request->getParameter("uid"));

            $this->forward404Unless($billColumnSetting = Doctrine_Core::getTable('BillColumnSetting')->find($typeUnitData[0]));

            VariationOrderItemTable::omitBillItems(
                $request->getParameter('vid'),
                Utilities::array_filter_integer(explode(",", $request->getParameter("sid"))),
                Utilities::array_filter_integer(explode(",", $request->getParameter("usid"))),
                $variationOrder, $billColumnSetting, $typeUnitData[1]);

            $errorMsg = null;
            $success  = true;
        }
        catch (Exception $e)
        {
            $errorMsg = $e->getMessage();
            $success  = false;
        }

        return $this->renderJson(array( 'success' => $success, 'errorMsg' => $errorMsg ));
    }

    public function executeGetClaimStatus(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $variationOrder = Doctrine_Core::getTable('VariationOrder')->find($request->getParameter('id'))
        );

        if( ! $request->hasParameter('claimRevision') )
        {
            $canAddNewClaim     = $variationOrder->canAddNewClaim();
            $count              = $variationOrder->Claims->count();
            $canEditClaimAmount = $variationOrder->canEditClaimAmount();
        }
        else
        {
            $pdo  = $variationOrder->getTable()->getConnection()->getDbh();

            $claimCertIds = array_column(PostContractClaimRevisionTable::getClaimCertificates($request->getParameter('claimRevision'), '<='), 'id');

            $claimCertClause = ( count($claimCertIds) > 0 ) ? " AND cert.id IN (" . implode(',', $claimCertIds) . ") " : "";

            $stmt = $pdo->prepare("SELECT count(vo.id)
                FROM " . VariationOrderTable::getInstance()->getTableName() . " vo
                JOIN " . VariationOrderClaimTable::getInstance()->getTableName() . " c ON c.variation_order_id = vo.id
                JOIN " . VariationOrderClaimItemTable::getInstance()->getTableName() . " i ON i.variation_order_claim_id = c.id
                LEFT JOIN ".VariationOrderClaimClaimCertificateTable::getInstance()->getTableName()." xref ON xref.variation_order_claim_id = c.id
                LEFT JOIN ".ClaimCertificateTable::getInstance()->getTableName()." cert ON cert.id = xref.claim_certificate_id
                LEFT JOIN ".PostContractClaimRevisionTable::getInstance()->getTableName()." rev ON rev.id = cert.post_contract_claim_revision_id
                WHERE rev.post_contract_id = :postContractId
                AND vo.id = :variationOrderId
                ".$claimCertClause."
                AND vo.deleted_at IS NULL AND c.deleted_at IS NULL AND i.deleted_at IS NULL GROUP BY vo.id");

            $stmt->execute([
                'postContractId' => $variationOrder->ProjectStructure->PostContract->id,
                'variationOrderId' => $variationOrder->id
            ]);

            $count = $stmt->fetch(PDO::FETCH_COLUMN);

            $canAddNewClaim     = false;
            $canEditClaimAmount = false;
        }

        $form = new BaseForm();

        return $this->renderJson(array(
            'can_add_new_claim'     => $canAddNewClaim,
            'count'                 => $count,
            'can_edit_claim_amount' => $canEditClaimAmount,
            '_csrf_token'           => $form->getCSRFToken()
        ));
    }

    public function executeGetUnits(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest());

        $options = array();
        $values  = array();

        array_push($values, '-1');
        array_push($options, '---');

        $records = Doctrine_Query::create()->select('u.id, u.symbol')
            ->from('UnitOfMeasurement u')
            ->where('u.display IS TRUE')
            ->addOrderBy('u.symbol ASC')
            ->fetchArray();

        foreach ( $records as $record )
        {
            array_push($values, (string) $record['id']); //damn, dojo store handles ids in string format
            array_push($options, $record['symbol']);
        }

        unset( $records );

        return $this->renderJson(array(
            'values'  => $values,
            'options' => $options
        ));
    }

    public function executeGetLinkInfo(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest() and
            $item = Doctrine_Core::getTable('VariationOrderItem')->find($request->getParameter('id')) and
            $request->hasParameter('t')

        );

        switch ($request->getParameter('t'))
        {
            case "addition":
                $type = VariationOrderBuildUpQuantityItem::TYPE_ADDITIONAL_QTY;
                break;
            case "omission":
                $type = VariationOrderBuildUpQuantityItem::TYPE_OMISSION_QTY;
                break;
            default:
                throw new Exception('invalid type');
        }

        $scheduleOfQuantityLinks = ScheduleOfQuantityVariationOrderItemXrefTable::getInstance()
            ->createQuery('x')->select('x.schedule_of_quantity_item_id')
            ->where('x.variation_order_item_id = ?', $item->id)
            ->andWhere('x.type = ?', $type)
            ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
            ->count();

        $hasLinkedQty = $scheduleOfQuantityLinks > 0 ? true : false;

        return $this->renderJson(array( 'has_linked_qty' => $hasLinkedQty ));
    }

    public function executeGetScheduleOfQuantities(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $item = Doctrine_Core::getTable('VariationOrderItem')->find($request->getParameter('id')) and
            $request->hasParameter('t')
        );

        $projectId = $item->BillItem->Element->ProjectStructure->root_id;

        switch ($request->getParameter('t'))
        {
            case "addition":
                $type = VariationOrderBuildUpQuantityItem::TYPE_ADDITIONAL_QTY;
                break;
            case "omission":
                $type = VariationOrderBuildUpQuantityItem::TYPE_OMISSION_QTY;
                break;
            default:
                throw new Exception("invalid type");
        }

        $pdo = $item->getTable()->getConnection()->getDbh();

        $stmt = $pdo->prepare("SELECT DISTINCT p.id, p.description, p.type, p.lft, p.level, p.priority, p.third_party_identifier, p.schedule_of_quantity_trade_id,
            p.identifier_type, p.uom_id AS uom_id, uom.symbol AS uom_symbol
            FROM " . ScheduleOfQuantityVariationOrderItemXrefTable::getInstance()->getTableName() . " xref
            JOIN " . ScheduleOfQuantityItemTable::getInstance()->getTableName() . " c ON c.id = xref.schedule_of_quantity_item_id
            JOIN " . ScheduleOfQuantityItemTable::getInstance()->getTableName() . " p ON c.lft BETWEEN p.lft AND p.rgt
            LEFT JOIN " . UnitOfMeasurementTable::getInstance()->getTableName() . " uom ON p.uom_id = uom.id AND uom.deleted_at IS NULL
            JOIN " . ScheduleOfQuantityTradeTable::getInstance()->getTableName() . " trade ON p.schedule_of_quantity_trade_id = trade.id
            JOIN " . ScheduleOfQuantityTable::getInstance()->getTableName() . " soq ON trade.schedule_of_quantity_id = soq.id
            WHERE xref.variation_order_item_id = " . $item->id . " AND xref.type = " . $type . "
            AND c.root_id = p.root_id AND c.schedule_of_quantity_trade_id = p.schedule_of_quantity_trade_id AND c.deleted_at IS NULL AND p.deleted_at IS NULL
            AND soq.project_structure_id = " . $projectId . " AND trade.deleted_at IS NULL AND soq.deleted_at IS NULL
            ORDER BY p.schedule_of_quantity_trade_id, p.priority, p.lft, p.level ASC");

        $stmt->execute();

        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmt = $pdo->prepare("SELECT ifc.relation_id, ifc.column_name, ifc.final_value, ifc.value, ifc.linked, ifc.has_build_up
            FROM " . ScheduleOfQuantityItemFormulatedColumnTable::getInstance()->getTableName() . " ifc
            JOIN " . ScheduleOfQuantityItemTable::getInstance()->getTableName() . " i ON i.id = ifc.relation_id
            JOIN " . ScheduleOfQuantityVariationOrderItemXrefTable::getInstance()->getTableName() . " xref ON i.id = xref.schedule_of_quantity_item_id
            WHERE xref.variation_order_item_id = " . $item->id . " AND xref.type = " . $type . " AND i.deleted_at IS NULL
            AND ifc.deleted_at IS NULL AND ifc.final_value <> 0");

        $stmt->execute();

        $itemFormulatedColumns = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $formulatedColumns = array();

        foreach ( $itemFormulatedColumns as $itemFormulatedColumn )
        {
            if ( !array_key_exists($itemFormulatedColumn['relation_id'], $formulatedColumns) )
            {
                $formulatedColumns[$itemFormulatedColumn['relation_id']] = array();
            }

            array_push($formulatedColumns[$itemFormulatedColumn['relation_id']], $itemFormulatedColumn);

            unset( $itemFormulatedColumn );
        }

        unset( $itemFormulatedColumns );

        foreach ( $items as $key => $item )
        {
            $items[$key]['type']               = (string) $item['type'];
            $items[$key]['uom_id']             = $item['uom_id'] > 0 ? (string) $item['uom_id'] : '-1';
            $items[$key]['editable_total']     = ( $item['type'] != ScheduleOfQuantityItem::TYPE_HEADER ) ? ScheduleOfQuantityItemTable::getBuildUpTotalByIdAndCanEditStatus($item['id'], true) : 0;
            $items[$key]['non_editable_total'] = ( $item['type'] != ScheduleOfQuantityItem::TYPE_HEADER ) ? ScheduleOfQuantityItemTable::getBuildUpTotalByIdAndCanEditStatus($item['id'], false) : 0;

            $items[$key][ScheduleOfQuantityItem::FORMULATED_COLUMN_QUANTITY . '-final_value']        = 0;
            $items[$key][ScheduleOfQuantityItem::FORMULATED_COLUMN_QUANTITY . '-value']              = '';
            $items[$key][ScheduleOfQuantityItem::FORMULATED_COLUMN_QUANTITY . '-has_cell_reference'] = false;
            $items[$key][ScheduleOfQuantityItem::FORMULATED_COLUMN_QUANTITY . '-has_formula']        = false;
            $items[$key][ScheduleOfQuantityItem::FORMULATED_COLUMN_QUANTITY . '-linked']             = false;
            $items[$key][ScheduleOfQuantityItem::FORMULATED_COLUMN_QUANTITY . '-has_build_up']       = false;

            if ( array_key_exists($item['id'], $formulatedColumns) )
            {
                $itemFormulatedColumns = $formulatedColumns[$item['id']];

                foreach ( $itemFormulatedColumns as $formulatedColumn )
                {
                    $items[$key][$formulatedColumn['column_name'] . '-final_value']  = $formulatedColumn['final_value'];
                    $items[$key][$formulatedColumn['column_name'] . '-value']        = $formulatedColumn['value'];
                    $items[$key][$formulatedColumn['column_name'] . '-linked']       = $formulatedColumn['linked'];
                    $items[$key][$formulatedColumn['column_name'] . '-has_build_up'] = $formulatedColumn['has_build_up'];
                    $items[$key][$formulatedColumn['column_name'] . '-has_formula']  = $formulatedColumn && $formulatedColumn['value'] != $formulatedColumn['final_value'] ? true : false;
                }

                unset( $formulatedColumns[$item['id']], $itemFormulatedColumns );
            }
        }

        array_push($items, array(
            'id'                                                                       => Constants::GRID_LAST_ROW,
            'description'                                                              => '',
            'type'                                                                     => (string) ScheduleOfQuantityItem::TYPE_WORK_ITEM,
            'uom_id'                                                                   => '-1',
            'uom_symbol'                                                               => '',
            'updated_at'                                                               => '-',
            'editable_total'                                                           => 0,
            'non_editable_total'                                                       => 0,
            ScheduleOfQuantityItem::FORMULATED_COLUMN_QUANTITY . '-final_value'        => 0,
            ScheduleOfQuantityItem::FORMULATED_COLUMN_QUANTITY . '-value'              => '',
            ScheduleOfQuantityItem::FORMULATED_COLUMN_QUANTITY . '-has_cell_reference' => false,
            ScheduleOfQuantityItem::FORMULATED_COLUMN_QUANTITY . '-has_formula'        => false,
            ScheduleOfQuantityItem::FORMULATED_COLUMN_QUANTITY . '-linked'             => false,
            ScheduleOfQuantityItem::FORMULATED_COLUMN_QUANTITY . '-has_build_up'       => false
        ));

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $items
        ));
    }

    /**** sub package vo actions ****/
    public function executeGetSpVariationOrderList(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $subPackage = Doctrine_Core::getTable('SubPackage')->find($request->getParameter('spid'))
        );

        $records = Doctrine_Query::create()->select('vo.id, vo.description, vo.is_approved, vo.updated_at')
            ->from('SubPackageVariationOrder vo')
            ->andWhere('vo.sub_package_id = ?', $subPackage->id)
            ->addOrderBy('vo.priority ASC')
            ->fetchArray();

        $pdo  = $subPackage->getTable()->getConnection()->getDbh();
        $form = new BaseForm();

        $stmt = $pdo->prepare("SELECT vo.id, COALESCE(COUNT(c.id), 0)
            FROM " . SubPackageVariationOrderTable::getInstance()->getTableName() . " vo
            LEFT JOIN " . SubPackageVariationOrderClaimTable::getInstance()->getTableName() . " c ON c.sub_package_variation_order_id = vo.id AND c.deleted_at IS NULL
            WHERE vo.sub_package_id = " . $subPackage->id . " AND vo.deleted_at IS NULL
            GROUP BY vo.id ORDER BY vo.priority");

        $stmt->execute();

        $claimCount = $stmt->fetchAll(PDO::FETCH_COLUMN | PDO::FETCH_GROUP);

        $stmt = $pdo->prepare("SELECT i.sub_package_variation_order_id, ROUND(COALESCE(SUM(i.total_unit * i.omission_quantity * i.rate), 0), 2) AS omission,
        ROUND(COALESCE(SUM(i.total_unit * i.addition_quantity * i.rate), 0), 2) AS addition,
        ROUND(COALESCE(SUM((i.total_unit * i.addition_quantity * i.rate) - (i.total_unit * i.omission_quantity * i.rate))), 2) AS nett_omission_addition
        FROM " . SubPackageVariationOrderItemTable::getInstance()->getTableName() . " i
        JOIN " . SubPackageVariationOrderTable::getInstance()->getTableName() . " vo ON i.sub_package_variation_order_id = vo.id
        WHERE vo.sub_package_id = " . $subPackage->id . " AND i.type <> " . VariationOrderItem::TYPE_HEADER . " AND i.rate <> 0
        AND vo.deleted_at IS NULL AND i.deleted_at IS NULL GROUP BY i.sub_package_variation_order_id");

        $stmt->execute();

        $quantities = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmt = $pdo->prepare("SELECT vo.id AS sub_package_variation_order_id, ROUND(COALESCE(SUM(i.up_to_date_amount), 0), 2) AS amount
        FROM " . SubPackageVariationOrderTable::getInstance()->getTableName() . " vo
        JOIN " . SubPackageVariationOrderClaimTable::getInstance()->getTableName() . " c ON c.sub_package_variation_order_id = vo.id
        JOIN " . SubPackageVariationOrderClaimItemTable::getInstance()->getTableName() . " i ON i.sub_package_variation_order_claim_id = c.id
        WHERE vo.sub_package_id = " . $subPackage->id . " AND c.is_viewing IS TRUE
        AND vo.deleted_at IS NULL AND c.deleted_at IS NULL AND i.deleted_at IS NULL GROUP BY vo.id");

        $stmt->execute();

        $upToDateClaims = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ( $records as $key => $record )
        {
            if ( $claimCount[$record['id']][0] > 0 )
            {
                $canBeEdited = false;
            }
            else
            {
                $canBeEdited = true;
            }

            $records[$key]['can_be_edited']          = $canBeEdited;
            $records[$key]['is_approved']            = $record['is_approved'] ? "true" : "false";
            $records[$key]['relation_id']            = $subPackage->id;
            $records[$key]['omission']               = 0;
            $records[$key]['addition']               = 0;
            $records[$key]['total_claim']            = 0;
            $records[$key]['nett_omission_addition'] = 0;
            $records[$key]['updated_at']             = date('d/m/Y H:i', strtotime($record['updated_at']));
            $records[$key]['_csrf_token']            = $form->getCSRFToken();

            foreach ( $quantities as $quantity )
            {
                if ( $quantity['sub_package_variation_order_id'] == $record['id'] )
                {
                    $records[$key]['omission']               = $quantity['omission'];
                    $records[$key]['addition']               = $quantity['addition'];
                    $records[$key]['nett_omission_addition'] = $quantity['nett_omission_addition'];

                    unset( $quantity );
                }
            }

            foreach ( $upToDateClaims as $upToDateClaim )
            {
                if ( $upToDateClaim['sub_package_variation_order_id'] == $record['id'] )
                {
                    $records[$key]['total_claim'] = $upToDateClaim['amount'];

                    unset( $upToDateClaim );
                }
            }

            unset( $record );
        }

        unset( $claimCount, $quantities, $upToDateClaims );

        //default last row
        array_push($records, array(
            'id'                     => Constants::GRID_LAST_ROW,
            'description'            => '',
            'can_be_edited'          => true,
            'relation_id'            => $subPackage->id,
            'omission'               => 0,
            'addition'               => 0,
            'total_claim'            => 0,
            'nett_omission_addition' => 0,
            'is_approved'            => false,
            'updated_at'             => '-',
            '_csrf_token'            => $form->getCSRFToken()
        ));

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $records
        ));
    }

    public function executeSpVariationOrderAdd(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless($request->isXmlHttpRequest() and $request->isMethod('post'));

        $variationOrder = new SubPackageVariationOrder();
        $con            = $variationOrder->getTable()->getConnection();

        if ( $request->hasParameter('id') and $request->getParameter('id') == Constants::GRID_LAST_ROW )
        {
            $prevVariationOrder = $request->getParameter('prev_item_id') > 0 ? Doctrine_Core::getTable('SubPackageVariationOrder')->find($request->getParameter('prev_item_id')) : null;

            $priority     = $prevVariationOrder ? $prevVariationOrder->priority + 1 : 0;
            $subPackageId = $request->getParameter('relation_id');

            if ( $request->hasParameter('attr_name') )
            {
                $fieldName  = $request->getParameter('attr_name');
                $fieldValue = $request->getParameter('val');

                $variationOrder->{'set' . sfInflector::camelize($fieldName)}($fieldValue);
            }

        }
        else
        {
            $this->forward404Unless($nextVariationOrder = Doctrine_Core::getTable('SubPackageVariationOrder')->find($request->getParameter('before_id')));

            $priority     = $nextVariationOrder->priority;
            $subPackageId = $nextVariationOrder->sub_package_id;
        }

        $items = array();

        try
        {
            $con->beginTransaction();

            DoctrineQuery::create()
                ->update('SubPackageVariationOrder')
                ->set('priority', 'priority + 1')
                ->where('priority >= ?', $priority)
                ->andWhere('sub_package_id = ?', $subPackageId)
                ->execute();

            $variationOrder->sub_package_id = $subPackageId;
            $variationOrder->priority       = $priority;

            $variationOrder->save();

            $con->commit();

            $success = true;

            $errorMsg = null;

            $item = array();

            $form = new BaseForm();

            $item['id']                     = $variationOrder->id;
            $item['description']            = $variationOrder->description;
            $item['is_approved']            = $variationOrder->is_approved ? "true" : "false";
            $item['relation_id']            = $subPackageId;
            $item['omission']               = 0;
            $item['addition']               = 0;
            $item['total_claim']            = 0;
            $item['nett_omission_addition'] = 0;
            $item['can_be_edited']          = true;
            $item['updated_at']             = date('d/m/Y H:i', strtotime($variationOrder->updated_at));
            $item['_csrf_token']            = $form->getCSRFToken();

            array_push($items, $item);

            if ( $request->hasParameter('id') and $request->getParameter('id') == Constants::GRID_LAST_ROW )
            {
                array_push($items, array(//default last row
                    'id'                     => Constants::GRID_LAST_ROW,
                    'description'            => '',
                    'can_be_edited'          => true,
                    'relation_id'            => $subPackageId,
                    'omission'               => 0,
                    'addition'               => 0,
                    'total_claim'            => 0,
                    'nett_omission_addition' => 0,
                    'is_approved'            => false,
                    'updated_at'             => '-',
                    '_csrf_token'            => $form->getCSRFToken()
                ));
            }
        }
        catch (Exception $e)
        {
            $con->rollback();
            $errorMsg = $e->getMessage();
            $success  = false;
        }

        return $this->renderJson(array(
            'success'  => $success,
            'items'    => $items,
            'errorMsg' => $errorMsg
        ));
    }

    public function executeSpVariationOrderUpdate(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $variationOrder = Doctrine_Core::getTable('SubPackageVariationOrder')->find($request->getParameter('id'))
        );

        $rowData = array();
        $con     = $variationOrder->getTable()->getConnection();

        try
        {
            $con->beginTransaction();

            $fieldName  = $request->getParameter('attr_name');
            $fieldValue = $request->getParameter('val');

            $variationOrder->{'set' . sfInflector::camelize($fieldName)}($fieldValue);

            $variationOrder->save($con);

            $con->commit();

            $success = true;

            $errorMsg = null;

            $variationOrder->refresh();

            if ( $fieldName == "is_approved" )
            {
                $value = $variationOrder->is_approved ? "true" : "false";
            }
            else
            {
                $value = $variationOrder->$fieldName;
            }

            $rowData = array(
                $fieldName   => $value,
                'updated_at' => date('d/m/Y H:i', strtotime($variationOrder->updated_at))
            );
        }
        catch (Exception $e)
        {
            $con->rollback();
            $errorMsg = $e->getMessage();
            $success  = false;
        }

        return $this->renderJson(array( 'success' => $success, 'errorMsg' => $errorMsg, 'data' => $rowData ));
    }

    public function executeSpVariationOrderDelete(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $variationOrder = Doctrine_Core::getTable('SubPackageVariationOrder')->find($request->getParameter('id'))
        );

        $errorMsg = null;
        try
        {
            $item['id'] = $variationOrder->id;

            $variationOrder->delete();

            $success = true;
        }
        catch (Exception $e)
        {
            $errorMsg = $e->getMessage();
            $item     = array();
            $success  = false;
        }

        // return items need to be in array since grid js expect an array of items for delete operation (delete from store)
        return $this->renderJson(array( 'success' => $success, 'errorMsg' => $errorMsg, 'items' => array( $item ) ));
    }

    /**** sub package vo item actions ****/
    public function executeGetSpVariationOrderItemList(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $variationOrder = Doctrine_Core::getTable('SubPackageVariationOrder')->find($request->getParameter('id'))
        );

        $pdo  = $variationOrder->getTable()->getConnection()->getDbh();
        $form = new BaseForm();

        $stmt = $pdo->prepare("SELECT i.id, i.description, i.type, i.lft, i.level, i.total_unit, i.rate,
            i.bill_ref, i.bill_item_id, i.omission_quantity, i.has_omission_build_up_quantity,
            i.addition_quantity, i.has_addition_build_up_quantity, uom.id AS uom_id, uom.symbol AS uom_symbol
            FROM " . SubPackageVariationOrderItemTable::getInstance()->getTableName() . " i
            LEFT JOIN " . UnitOfMeasurementTable::getInstance()->getTableName() . " uom ON i.uom_id = uom.id AND uom.deleted_at IS NULL
            WHERE i.sub_package_variation_order_id = " . $variationOrder->id . " AND i.deleted_at IS NULL
            ORDER BY i.priority, i.lft, i.level");

        $stmt->execute();

        $variationOrderItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $canBeEdited = ($variationOrder->status == PostContractClaim::STATUS_PREPARING);

        $stmt = $pdo->prepare("SELECT DISTINCT i.id AS sub_package_variation_order_item_id, ci.current_amount, ci.current_percentage, ci.up_to_date_amount, ci.up_to_date_percentage,
        COALESCE(pci.up_to_date_amount, 0) AS previous_amount, COALESCE(pci.up_to_date_percentage, 0) AS previous_percentage
        FROM " . SubPackageVariationOrderItemTable::getInstance()->getTableName() . " i
        JOIN " . SubPackageVariationOrderClaimTable::getInstance()->getTableName() . " c ON i.sub_package_variation_order_id = c.sub_package_variation_order_id
        LEFT JOIN " . SubPackageVariationOrderClaimItemTable::getInstance()->getTableName() . " ci ON ci.sub_package_variation_order_claim_id = c.id AND ci.sub_package_variation_order_item_id = i.id
        LEFT JOIN " . SubPackageVariationOrderClaimTable::getInstance()->getTableName() . " pc ON pc.sub_package_variation_order_id = c.sub_package_variation_order_id AND pc.revision = c.revision - 1
        LEFT JOIN " . SubPackageVariationOrderClaimItemTable::getInstance()->getTableName() . " pci ON pci.sub_package_variation_order_claim_id = pc.id AND pci.sub_package_variation_order_item_id = i.id
        WHERE i.sub_package_variation_order_id = " . $variationOrder->id . " AND c.is_viewing IS TRUE
        AND i.deleted_at IS NULL AND c.deleted_at IS NULL AND ci.deleted_at IS NULL AND pc.deleted_at IS NULL AND pci.deleted_at IS NULL");

        $stmt->execute();

        $claimItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ( $variationOrderItems as $key => $variationOrderItem )
        {
            $variationOrderItems[$key]['omission_quantity-value'] = $variationOrderItem['omission_quantity'];
            $variationOrderItems[$key]['addition_quantity-value'] = $variationOrderItem['addition_quantity'];
            $variationOrderItems[$key]['rate-value']              = $variationOrderItem['rate'];
            $variationOrderItems[$key]['type']                    = (string) $variationOrderItem['type'];
            $variationOrderItems[$key]['uom_id']                  = $variationOrderItem['uom_id'] > 0 ? (string) $variationOrderItem['uom_id'] : '-1';
            $variationOrderItems[$key]['uom_symbol']              = $variationOrderItem['uom_id'] > 0 ? $variationOrderItem['uom_symbol'] : '';
            $variationOrderItems[$key]['relation_id']             = $variationOrder->id;
            $variationOrderItems[$key]['can_be_edited']           = $canBeEdited;
            $variationOrderItems[$key]['_csrf_token']             = $form->getCSRFToken();

            $variationOrderItems[$key]['previous_percentage-value']   = number_format(0, 2, '.', '');
            $variationOrderItems[$key]['previous_amount-value']       = number_format(0, 2, '.', '');
            $variationOrderItems[$key]['current_percentage-value']    = number_format(0, 2, '.', '');
            $variationOrderItems[$key]['current_amount-value']        = number_format(0, 2, '.', '');
            $variationOrderItems[$key]['up_to_date_percentage-value'] = number_format(0, 2, '.', '');
            $variationOrderItems[$key]['up_to_date_amount-value']     = number_format(0, 2, '.', '');

            foreach ( $claimItems as $claimItem )
            {
                if ( $claimItem['sub_package_variation_order_item_id'] == $variationOrderItem['id'] )
                {
                    $variationOrderItems[$key]['previous_percentage-value']   = $claimItem['previous_percentage'];
                    $variationOrderItems[$key]['previous_amount-value']       = $claimItem['previous_amount'];
                    $variationOrderItems[$key]['current_percentage-value']    = $claimItem['current_percentage'];
                    $variationOrderItems[$key]['current_amount-value']        = $claimItem['current_amount'];
                    $variationOrderItems[$key]['up_to_date_percentage-value'] = $claimItem['up_to_date_percentage'];
                    $variationOrderItems[$key]['up_to_date_amount-value']     = $claimItem['up_to_date_amount'];

                    unset( $claimItem );
                }
            }
        }

        unset( $claimItems );

        array_push($variationOrderItems, array(
            'id'                             => Constants::GRID_LAST_ROW,
            'description'                    => '',
            'bill_ref'                       => '',
            'total_unit'                     => '',
            'bill_item_id'                   => - 1,
            'type'                           => (string) SubPackageVariationOrderItem::TYPE_WORK_ITEM,
            'uom_id'                         => '-1',
            'uom_symbol'                     => '',
            'relation_id'                    => $variationOrder->id,
            'updated_at'                     => '-',
            'level'                          => 0,
            'rate-value'                     => 0,
            'omission_quantity-value'        => 0,
            'has_omission_build_up_quantity' => false,
            'addition_quantity-value'        => 0,
            'has_addition_build_up_quantity' => false,
            'can_be_edited'                  => $canBeEdited,
            'previous_percentage-value'      => 0,
            'previous_amount-value'          => 0,
            'current_percentage-value'       => 0,
            'current_amount-value'           => 0,
            'up_to_date_percentage-value'    => 0,
            'up_to_date_amount-value'        => 0,
            '_csrf_token'                    => $form->getCSRFToken()
        ));

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $variationOrderItems
        ));
    }

    public function executeSpVariationOrderItemAdd(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless($request->isXmlHttpRequest() and $request->isMethod('post'));

        $items = array();

        $con = Doctrine_Core::getTable('SubPackageVariationOrderItem')->getConnection();

        try
        {
            $con->beginTransaction();

            if ( $request->hasParameter('id') and $request->getParameter('id') == Constants::GRID_LAST_ROW )
            {
                $previousItem     = $request->getParameter('prev_item_id') > 0 ? Doctrine_Core::getTable('SubPackageVariationOrderItem')->find($request->getParameter('prev_item_id')) : null;
                $variationOrderId = $request->getParameter('relation_id');

                $fieldName  = $request->hasParameter('attr_name') ? $request->getParameter('attr_name') : null;
                $fieldValue = $request->hasParameter('val') ? $request->getParameter('val') : null;


                $item = SubPackageVariationOrderItemTable::createItemFromLastRow($previousItem, $variationOrderId, $fieldName, $fieldValue);
            }
            else
            {
                $this->forward404Unless($nextItem = Doctrine_Core::getTable('SubPackageVariationOrderItem')->find($request->getParameter('before_id')));

                $variationOrderId = $nextItem->sub_package_variation_order_id;

                $item = SubPackageVariationOrderItemTable::createItem($nextItem, $variationOrderId);
            }

            $con->commit();

            $success = true;

            $errorMsg = null;

            $data = array();

            $form = new BaseForm();

            $item->refresh();

            $data['id']                             = $item->id;
            $data['bill_ref']                       = '';
            $data['bill_item_id']                   = $item->bill_item_id;
            $data['total_unit']                     = $item->total_unit;
            $data['description']                    = $item->description;
            $data['type']                           = (string) $item->type;
            $data['uom_id']                         = $item->uom_id > 0 ? (string) $item->uom_id : '-1';
            $data['uom_symbol']                     = $item->uom_id > 0 ? Doctrine_Core::getTable('UnitOfMeasurement')->find($item->uom_id)->symbol : '';
            $data['relation_id']                    = $variationOrderId;
            $data['rate-value']                     = $item->rate;
            $data['omission_quantity-value']        = $item->omission_quantity;
            $data['has_omission_build_up_quantity'] = $item->has_omission_build_up_quantity;
            $data['addition_quantity-value']        = $item->addition_quantity;
            $data['has_addition_build_up_quantity'] = $item->has_addition_build_up_quantity;
            $data['level']                          = $item->level;
            $data['can_be_edited']                  = true;
            $data['previous_percentage-value']      = number_format(0, 2, '.', '');
            $data['previous_amount-value']          = number_format(0, 2, '.', '');
            $data['current_percentage-value']       = number_format(0, 2, '.', '');
            $data['current_amount-value']           = number_format(0, 2, '.', '');
            $data['up_to_date_percentage-value']    = number_format(0, 2, '.', '');
            $data['up_to_date_amount-value']        = number_format(0, 2, '.', '');
            $data['_csrf_token']                    = $form->getCSRFToken();

            array_push($items, $data);

            if ( $request->hasParameter('id') and $request->getParameter('id') == Constants::GRID_LAST_ROW )
            {
                array_push($items, array(
                    'id'                             => Constants::GRID_LAST_ROW,
                    'bill_ref'                       => '',
                    'bill_item_id'                   => - 1,
                    'total_unit'                     => '',
                    'description'                    => '',
                    'type'                           => (string) SubPackageVariationOrderItem::TYPE_WORK_ITEM,
                    'uom_id'                         => '-1',
                    'uom_symbol'                     => '',
                    'rate-value'                     => 0,
                    'omission_quantity-value'        => 0,
                    'has_omission_build_up_quantity' => false,
                    'addition_quantity-value'        => 0,
                    'has_addition_build_up_quantity' => false,
                    'relation_id'                    => $variationOrderId,
                    'updated_at'                     => '-',
                    'can_be_edited'                  => true,
                    'previous_percentage-value'      => 0,
                    'previous_amount-value'          => 0,
                    'current_percentage-value'       => 0,
                    'current_amount-value'           => 0,
                    'up_to_date_percentage-value'    => 0,
                    'up_to_date_amount-value'        => 0,
                    'level'                          => 0,
                    '_csrf_token'                    => $form->getCSRFToken()
                ));
            }
        }
        catch (Exception $e)
        {
            $con->rollback();
            $errorMsg = $e->getMessage();
            $success  = false;
        }

        return $this->renderJson(array(
            'success'  => $success,
            'items'    => $items,
            'errorMsg' => $errorMsg
        ));
    }

    public function executeSpVariationOrderItemUpdate(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $item = Doctrine_Core::getTable('SubPackageVariationOrderItem')->find($request->getParameter('id'))
        );

        $rowData = array();

        $con = $item->getTable()->getConnection();

        try
        {
            $con->beginTransaction();

            $fieldName  = $request->getParameter('attr_name');
            $fieldValue = $request->getParameter('val');

            $fieldValue = ( $fieldName == 'uom_id' and $fieldValue == - 1 ) ? null : $fieldValue;

            if ( $fieldName == "type" or $fieldName == "uom_id" )
            {
                $item->updateColumnByColumnName($fieldName, $fieldValue);
            }
            elseif ( $fieldName == 'omission_quantity' or $fieldName == 'addition_quantity' )
            {
                $fieldValue = is_numeric($fieldValue) ? $fieldValue : 0;
                $item->updateColumnByColumnName($fieldName, $fieldValue);

                $fieldName = $fieldName . '-value';
            }
            elseif ( $fieldName == 'rate' )
            {
                $fieldValue = is_numeric($fieldValue) ? $fieldValue : 0;
                $item->rate = number_format($fieldValue, 2, '.', '');

                $fieldName = 'rate-value';
            }
            else
            {
                $item->{'set' . sfInflector::camelize($fieldName)}($fieldValue);
            }

            $item->save($con);

            $con->commit();

            $success = true;

            $errorMsg = null;

            $item->refresh();

            $rowData[$fieldName] = $item->{$request->getParameter('attr_name')};

            $rowData['type']       = (string) $item->type;
            $rowData['uom_id']     = $item->uom_id > 0 ? (string) $item->uom_id : '-1';
            $rowData['uom_symbol'] = $item->uom_id > 0 ? $item->UnitOfMeasurement->symbol : '';

            if ( $fieldName == "type" or $fieldName == "uom_id" )
            {
                $rowData['addition_quantity-value'] = $item->addition_quantity;
                $rowData['omission_quantity-value'] = $item->omission_quantity;
            }

            $rowData['has_omission_build_up_quantity'] = $item->has_omission_build_up_quantity;
            $rowData['has_addition_build_up_quantity'] = $item->has_addition_build_up_quantity;
        }
        catch (Exception $e)
        {
            $con->rollback();
            $errorMsg = $e->getMessage();
            $success  = false;
        }

        return $this->renderJson(array( 'success' => $success, 'errorMsg' => $errorMsg, 'data' => $rowData ));
    }

    public function executeSpVariationOrderItemDelete(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless($request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $item = Doctrine_Core::getTable('SubPackageVariationOrderItem')->find($request->getParameter('id'))
        );

        $errorMsg = null;

        try
        {
            $items = Doctrine_Query::create()->select('i.id')
                ->from('SubPackageVariationOrderItem i')
                ->andWhere('i.root_id = ?', $item->root_id)
                ->andWhere('i.sub_package_variation_order_id = ?', $item->sub_package_variation_order_id)
                ->andWhere('i.lft >= ? AND i.rgt <= ?', array( $item->lft, $item->rgt ))
                ->addOrderBy('i.lft')
                ->fetchArray();

            $affectedNodes = $item->delete();

            $success = true;
        }
        catch (Exception $e)
        {
            $items         = array();
            $affectedNodes = array();
            $errorMsg      = $e->getMessage();
            $success       = false;
        }

        return $this->renderJson(array( 'success' => $success, 'errorMsg' => $errorMsg, 'items' => $items, 'affected_nodes' => $affectedNodes ));
    }

    public function executeSpVariationOrderItemIndent(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless($request->isXmlHttpRequest() and $item = Doctrine_Core::getTable('SubPackageVariationOrderItem')->find($request->getParameter('id')));

        $success  = false;
        $children = array();
        $errorMsg = null;
        $data     = array();
        try
        {
            if ( $item->indent() )
            {
                $data['id']    = $item->id;
                $data['level'] = $item->level;

                $children = Doctrine_Query::create()->select('i.id, i.level')
                    ->from('SubPackageVariationOrderItem i')
                    ->where('i.root_id = ?', $item->root_id)
                    ->andWhere('i.lft > ? AND i.rgt < ?', array( $item->lft, $item->rgt ))
                    ->addOrderBy('i.lft')
                    ->fetchArray();

                $success = true;
            }
        }
        catch (Exception $e)
        {
            $errorMsg = $e->getMessage();
        }

        return $this->renderJson(array( 'success' => $success, 'errorMsg' => $errorMsg, 'item' => $data, 'c' => $children ));
    }

    public function executeSpVariationOrderItemOutdent(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless($request->isXmlHttpRequest() and $item = Doctrine_Core::getTable('SubPackageVariationOrderItem')->find($request->getParameter('id')));

        $success  = false;
        $children = array();
        $errorMsg = null;
        $data     = array();
        try
        {
            if ( $item->outdent() )
            {
                $data['id']    = $item->id;
                $data['level'] = $item->level;

                $children = Doctrine_Query::create()->select('i.id, i.level')
                    ->from('SubPackageVariationOrderItem i')
                    ->where('i.root_id = ?', $item->root_id)
                    ->andWhere('i.lft > ? AND i.rgt < ?', array( $item->lft, $item->rgt ))
                    ->addOrderBy('i.lft')
                    ->fetchArray();

                $success = true;
            }
        }
        catch (Exception $e)
        {
            $errorMsg = $e->getMessage();
        }

        return $this->renderJson(array( 'success' => $success, 'errorMsg' => $errorMsg, 'item' => $data, 'c' => $children ));
    }

    public function executeSpVariationOrderItemPaste(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless($request->isXmlHttpRequest() and $item = Doctrine_Core::getTable('SubPackageVariationOrderItem')->find($request->getParameter('id')));

        $data         = array();
        $children     = array();
        $lastPosition = false;
        $success      = false;
        $errorMsg     = null;

        $targetItem = Doctrine_Core::getTable('SubPackageVariationOrderItem')->find(intval($request->getParameter('target_id')));

        if ( !$targetItem )
        {
            $this->forward404Unless($targetItem = Doctrine_Core::getTable('SubPackageVariationOrderItem')->find($request->getParameter('prev_item_id')));
            $lastPosition = true;
        }

        if ( $targetItem->root_id == $item->root_id and $targetItem->lft >= $item->lft and $targetItem->rgt <= $item->rgt )
        {
            $errorMsg = "cannot move item into itself";
            $results  = array( 'success' => false, 'errorMsg' => $errorMsg, 'data' => $data, 'c' => array() );

            return $this->renderJson($results);
        }

        try
        {
            $item->moveTo($targetItem, $lastPosition);

            $children = DoctrineQuery::create()->select('i.id, i.level')
                ->from('SubPackageVariationOrderItem i')
                ->where('i.root_id = ?', $item->root_id)
                ->andWhere('i.lft > ? AND i.rgt < ?', array( $item->lft, $item->rgt ))
                ->addOrderBy('i.lft')
                ->fetchArray();

            $data['id']    = $item->id;
            $data['level'] = $item->level;

            $success  = true;
            $errorMsg = null;
        }
        catch (Exception $e)
        {
            $errorMsg = $e->getMessage();
        }

        return $this->renderJson(array( 'success' => $success, 'errorMsg' => $errorMsg, 'data' => $data, 'c' => $children ));
    }

    public function executeGetSpBuildUpQuantityItemList(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $variationOrderItem = Doctrine_Core::getTable('SubPackageVariationOrderItem')->find($request->getParameter('id')) and
            $request->hasParameter('type')
        );

        switch ($request->getParameter('type'))
        {
            case "addition":
                $type = SubPackageVariationOrderBuildUpQuantityItem::TYPE_ADDITIONAL_QTY;
                break;
            case "omission":
                $type = SubPackageVariationOrderBuildUpQuantityItem::TYPE_OMISSION_QTY;
                break;
            default:
                throw new Exception('invalid type');
        }

        $buildUpQuantityItems = DoctrineQuery::create()->select('i.id, i.description, i.sign, i.total, ifc.column_name, ifc.value, ifc.final_value')
            ->from('SubPackageVariationOrderBuildUpQuantityItem i')
            ->leftJoin('i.FormulatedColumns ifc')
            ->where('i.sub_package_variation_order_item_id = ?', $variationOrderItem->id)
            ->andWhere('i.type = ?', $type)
            ->addOrderBy('i.priority ASC')
            ->fetchArray();

        $form = new BaseForm();

        $formulatedColumnNames = BillBuildUpQuantityItemTable::getFormulatedColumnNames($variationOrderItem->UnitOfMeasurement);

        foreach ( $buildUpQuantityItems as $key => $buildUpQuantityItem )
        {
            $buildUpQuantityItems[$key]['sign']        = (string) $buildUpQuantityItem['sign'];
            $buildUpQuantityItems[$key]['sign_symbol'] = BillBuildUpQuantityItemTable::getSignTextBySign($buildUpQuantityItem['sign']);
            $buildUpQuantityItems[$key]['relation_id'] = $variationOrderItem->id;
            $buildUpQuantityItems[$key]['_csrf_token'] = $form->getCSRFToken();

            foreach ( $formulatedColumnNames as $constant )
            {
                $buildUpQuantityItems[$key][$constant . '-final_value']        = 0;
                $buildUpQuantityItems[$key][$constant . '-value']              = '';
                $buildUpQuantityItems[$key][$constant . '-has_cell_reference'] = false;
                $buildUpQuantityItems[$key][$constant . '-has_formula']        = false;
            }

            foreach ( $buildUpQuantityItem['FormulatedColumns'] as $formulatedColumn )
            {
                $buildUpQuantityItems[$key][$formulatedColumn['column_name'] . '-final_value']        = $formulatedColumn['final_value'];
                $buildUpQuantityItems[$key][$formulatedColumn['column_name'] . '-value']              = $formulatedColumn['value'];
                $buildUpQuantityItems[$key][$formulatedColumn['column_name'] . '-has_cell_reference'] = false;
                $buildUpQuantityItems[$key][$formulatedColumn['column_name'] . '-has_formula']        = $formulatedColumn['value'] != $formulatedColumn['final_value'] ? true : false;
            }

            unset( $buildUpQuantityItem, $buildUpQuantityItems[$key]['FormulatedColumns'] );
        }

        $defaultLastRow = array(
            'id'          => Constants::GRID_LAST_ROW,
            'description' => '',
            'sign'        => (string) BillBuildUpQuantityItem::SIGN_POSITIVE,
            'sign_symbol' => BillBuildUpQuantityItem::SIGN_POSITIVE_TEXT,
            'relation_id' => $variationOrderItem->id,
            'total'       => '',
            '_csrf_token' => $form->getCSRFToken()
        );

        foreach ( $formulatedColumnNames as $columnName )
        {
            $defaultLastRow[$columnName . '-final_value']        = 0;
            $defaultLastRow[$columnName . '-value']              = "";
            $defaultLastRow[$columnName . '-has_cell_reference'] = false;
            $defaultLastRow[$columnName . '-has_formula']        = false;
        }

        array_push($buildUpQuantityItems, $defaultLastRow);

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $buildUpQuantityItems
        ));
    }

    public function executeGetSpBuildUpSummary(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $variationOrderItem = Doctrine_Core::getTable('SubPackageVariationOrderItem')->find($request->getParameter('id')) and
            $request->hasParameter('type')
        );

        switch ($request->getParameter('type'))
        {
            case "addition":
                $type = SubPackageVariationOrderBuildUpQuantityItem::TYPE_ADDITIONAL_QTY;
                break;
            case "omission":
                $type = SubPackageVariationOrderBuildUpQuantityItem::TYPE_OMISSION_QTY;
                break;
            default:
                throw new Exception('invalid type');
        }

        $buildUpQuantitySummary = SubPackageVariationOrderBuildUpQuantitySummaryTable::createByVariationOrderItemIdAndType($variationOrderItem->id, $type);

        return $this->renderJson(array(
            'apply_conversion_factor'    => $buildUpQuantitySummary->apply_conversion_factor,
            'conversion_factor_amount'   => number_format($buildUpQuantitySummary->conversion_factor_amount, 2, '.', ''),
            'conversion_factor_operator' => $buildUpQuantitySummary->conversion_factor_operator,
            'linked_total_quantity'      => number_format($buildUpQuantitySummary->linked_total_quantity, 2, '.', ''),
            'total_quantity'             => number_format($buildUpQuantitySummary->calculateTotalQuantity(), 2, '.', ''),
            'final_quantity'             => number_format($buildUpQuantitySummary->getTotalQuantityAfterConversion(), 2, '.', ''),
            'rounding_type'              => $buildUpQuantitySummary->rounding_type
        ));
    }

    public function executeSpBuildUpQuantityItemAdd(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless($request->isXmlHttpRequest() and $request->isMethod('post') and $request->hasParameter('type'));

        $items = array();

        $item = new SubPackageVariationOrderBuildUpQuantityItem();

        $con = $item->getTable()->getConnection();

        $isFormulatedColumn = false;

        switch ($request->getParameter('type'))
        {
            case "addition":
                $type = SubPackageVariationOrderBuildUpQuantityItem::TYPE_ADDITIONAL_QTY;
                break;
            case "omission":
                $type = SubPackageVariationOrderBuildUpQuantityItem::TYPE_OMISSION_QTY;
                break;
            default:
                throw new Exception('invalid type');
        }

        if ( $request->hasParameter('id') and $request->getParameter('id') == Constants::GRID_LAST_ROW )
        {
            $this->forward404Unless($variationOrderItem = Doctrine_Core::getTable('SubPackageVariationOrderItem')->find($request->getParameter('relation_id')));

            $formulatedColumnNames = SubPackageVariationOrderBuildUpQuantityItemTable::getFormulatedColumnNames($variationOrderItem->UnitOfMeasurement);

            $previousItem = $request->getParameter('prev_item_id') > 0 ? Doctrine_Core::getTable('SubPackageVariationOrderBuildUpQuantityItem')->find($request->getParameter('prev_item_id')) : null;

            $priority = $previousItem ? $previousItem->priority + 1 : 0;

            if ( $request->hasParameter('attr_name') )
            {
                $fieldName  = $request->getParameter('attr_name');
                $fieldValue = $request->getParameter('val');

                if ( in_array($fieldName, $formulatedColumnNames) )
                {
                    $isFormulatedColumn = true;
                }
                else
                {
                    $item->{'set' . sfInflector::camelize($fieldName)}($fieldValue);
                }
            }
        }
        else
        {
            $this->forward404Unless($nextItem = Doctrine_Core::getTable('SubPackageVariationOrderBuildUpQuantityItem')->find($request->getParameter('before_id')));

            $variationOrderItem    = $nextItem->SubPackageVariationOrderItem;
            $formulatedColumnNames = SubPackageVariationOrderBuildUpQuantityItemTable::getFormulatedColumnNames($variationOrderItem->UnitOfMeasurement);

            $priority = $nextItem->priority;
        }

        try
        {
            $con->beginTransaction();

            DoctrineQuery::create()
                ->update('SubPackageVariationOrderBuildUpQuantityItem')
                ->set('priority', 'priority + 1')
                ->where('priority >= ?', $priority)
                ->andWhere('sub_package_variation_order_item_id = ?', $variationOrderItem->id)
                ->andWhere('type = ?', $type)
                ->execute();

            $item->sub_package_variation_order_item_id = $variationOrderItem->id;
            $item->priority                            = $priority;
            $item->type                                = $type;

            $item->save($con);

            if ( $isFormulatedColumn )
            {
                $formulatedColumn              = new SubPackageVariationOrderBuildUpQuantityFormulatedColumn();
                $formulatedColumn->relation_id = $item->id;
                $formulatedColumn->column_name = $fieldName;
                $formulatedColumn->setFormula($fieldValue);

                $formulatedColumn->save();
            }

            $con->commit();

            $success = true;

            $errorMsg = null;

            $data = array();

            $form = new BaseForm();

            $data['id']          = $item->id;
            $data['description'] = $item->description;
            $data['sign']        = (string) $item->sign;
            $data['sign_symbol'] = $item->getSignText();
            $data['total']       = $item->calculateTotal();
            $data['relation_id'] = $variationOrderItem->id;
            $data['_csrf_token'] = $form->getCSRFToken();

            foreach ( $formulatedColumnNames as $columnName )
            {
                $formulatedColumn                          = $item->getFormulatedColumnByName($columnName, Doctrine_Core::HYDRATE_ARRAY);
                $finalValue                                = $formulatedColumn ? $formulatedColumn['final_value'] : 0;
                $data[$columnName . '-final_value']        = $finalValue;
                $data[$columnName . '-value']              = $formulatedColumn ? $formulatedColumn['value'] : '';
                $data[$columnName . '-has_cell_reference'] = false;
                $data[$columnName . '-has_formula']        = $formulatedColumn && $formulatedColumn['value'] != $formulatedColumn['final_value'] ? true : false;
            }

            array_push($items, $data);

            if ( $request->hasParameter('id') and $request->getParameter('id') == Constants::GRID_LAST_ROW )
            {
                $defaultLastRow = array(
                    'id'          => Constants::GRID_LAST_ROW,
                    'description' => '',
                    'sign'        => (string) SubPackageVariationOrderBuildUpQuantityItem::SIGN_POSITIVE,
                    'sign_symbol' => SubPackageVariationOrderBuildUpQuantityItem::SIGN_POSITIVE_TEXT,
                    'relation_id' => $variationOrderItem->id,
                    'total'       => '',
                    '_csrf_token' => $form->getCSRFToken()
                );

                foreach ( $formulatedColumnNames as $columnName )
                {
                    $defaultLastRow[$columnName . '-final_value']        = "";
                    $defaultLastRow[$columnName . '-value']              = "";
                    $defaultLastRow[$columnName . '-has_cell_reference'] = false;
                    $defaultLastRow[$columnName . '-has_formula']        = false;
                }

                array_push($items, $defaultLastRow);
            }

        }
        catch (Exception $e)
        {
            $con->rollback();
            $errorMsg = $e->getMessage();
            $success  = false;
        }

        return $this->renderJson(array(
            'success'  => $success,
            'items'    => $items,
            'errorMsg' => $errorMsg
        ));
    }

    public function executeSpBuildUpQuantityItemUpdate(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $item = Doctrine_Core::getTable('SubPackageVariationOrderBuildUpQuantityItem')->find($request->getParameter('id'))
        );

        $rowData = array();
        $con     = $item->getTable()->getConnection();

        $variationOrderItem = $item->SubPackageVariationOrderItem;

        $formulatedColumnNames = SubPackageVariationOrderBuildUpQuantityItemTable::getFormulatedColumnNames($variationOrderItem->UnitOfMeasurement);

        try
        {
            $con->beginTransaction();

            $fieldName  = $request->getParameter('attr_name');
            $fieldValue = $request->getParameter('val');

            $affectedNodes      = array();
            $isFormulatedColumn = false;

            if ( in_array($fieldName, $formulatedColumnNames) )
            {
                $formulatedColumn = SubPackageVariationOrderBuildUpQuantityFormulatedColumnTable::getInstance()->getByRelationIdAndColumnName($item->id, $fieldName);

                $formulatedColumn->setFormula($fieldValue);

                $formulatedColumn->save($con);

                $formulatedColumn->refresh();

                $isFormulatedColumn = true;
            }
            else
            {
                $item->{'set' . sfInflector::camelize($fieldName)}($fieldValue);
                $item->save($con);
            }

            $con->commit();

            $success = true;

            $errorMsg = null;

            $item->refresh();

            if ( $isFormulatedColumn )
            {
                $referencedNodes = $formulatedColumn->getNodesRelatedByColumnName($fieldName);

                foreach ( $referencedNodes as $referencedNode )
                {
                    $node = SubPackageVariationOrderBuildUpQuantityFormulatedColumnTable::getInstance()->find($referencedNode['node_from']);

                    if ( $node )
                    {
                        $total = $node->BuildUpQuantityItem->calculateTotal();

                        array_push($affectedNodes, array(
                            'id'                        => $node->relation_id,
                            $fieldName . '-final_value' => $node->final_value,
                            'total'                     => $total
                        ));
                    }
                }
            }
            else
            {
                $rowData[$fieldName] = $item->$fieldName;
            }

            foreach ( $formulatedColumnNames as $columnName )
            {
                $formulatedColumn                             = $item->getFormulatedColumnByName($columnName, Doctrine_Core::HYDRATE_ARRAY);
                $finalValue                                   = $formulatedColumn ? $formulatedColumn['final_value'] : 0;
                $rowData[$columnName . '-final_value']        = $finalValue;
                $rowData[$columnName . '-value']              = $formulatedColumn ? $formulatedColumn['value'] : '';
                $rowData[$columnName . '-has_cell_reference'] = false;
                $rowData[$columnName . '-has_formula']        = $formulatedColumn && $formulatedColumn['value'] != $formulatedColumn['final_value'] ? true : false;
            }

            $rowData['sign']           = (string) $item->sign;
            $rowData['sign_symbol']    = $item->getSignText();
            $rowData['total']          = $item->calculateTotal();
            $rowData['affected_nodes'] = $affectedNodes;
        }
        catch (Exception $e)
        {
            $con->rollback();
            $errorMsg = $e->getMessage();
            $success  = false;
        }

        return $this->renderJson(array(
            'success'  => $success,
            'errorMsg' => $errorMsg,
            'data'     => $rowData
        ));
    }

    public function executeSpBuildUpQuantityItemDelete(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless($request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $buildUpItem = Doctrine_Core::getTable('SubPackageVariationOrderBuildUpQuantityItem')->find($request->getParameter('id'))
        );

        $errorMsg = null;

        try
        {
            $item['id']    = $buildUpItem->id;
            $affectedNodes = $buildUpItem->delete();

            $success = true;
        }
        catch (Exception $e)
        {
            $errorMsg      = $e->getMessage();
            $item          = array();
            $affectedNodes = array();
            $success       = false;
        }

        return $this->renderJson(array( 'success' => $success, 'errorMsg' => $errorMsg, 'item' => $item, 'affected_nodes' => $affectedNodes ));
    }

    public function executeSpBuildUpQuantityItemPaste(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $buildUpQuantityItem = Doctrine_Core::getTable('SubPackageVariationOrderBuildUpQuantityItem')->find($request->getParameter('id'))
        );

        $data         = array();
        $lastPosition = false;
        $success      = false;
        $errorMsg     = null;

        $targetBuildUpQuantityItem = Doctrine_Core::getTable('SubPackageVariationOrderBuildUpQuantityItem')->find(intval($request->getParameter('target_id')));
        if ( !$targetBuildUpQuantityItem )
        {
            $this->forward404Unless($targetBuildUpQuantityItem = Doctrine_Core::getTable('SubPackageVariationOrderBuildUpQuantityItem')->find($request->getParameter('prev_item_id')));
            $lastPosition = true;
        }

        if ( $request->getParameter('type') == 'cut' and $targetBuildUpQuantityItem->id == $buildUpQuantityItem->id )
        {
            $errorMsg = "cannot move item into itself";
            $results  = array( 'success' => false, 'errorMsg' => $errorMsg, 'data' => $data, 'c' => array() );

            return $this->renderJson($results);
        }

        switch ($request->getParameter('type'))
        {
            case 'cut':
                try
                {
                    $buildUpQuantityItem->moveTo($targetBuildUpQuantityItem->priority, $lastPosition);

                    $data['id'] = $buildUpQuantityItem->id;

                    $success  = true;
                    $errorMsg = null;
                }
                catch (Exception $e)
                {
                    $errorMsg = $e->getMessage();
                }
                break;
            case 'copy':
                try
                {
                    $formulatedColumnNames  = SubPackageVariationOrderBuildUpQuantityItemTable::getFormulatedColumnNames($buildUpQuantityItem->SubPackageVariationOrderItem->UnitOfMeasurement);
                    $newBuildUpQuantityItem = $buildUpQuantityItem->copyTo($targetBuildUpQuantityItem, $lastPosition);

                    $form = new BaseForm();

                    $data['id']          = $newBuildUpQuantityItem->id;
                    $data['description'] = $newBuildUpQuantityItem->description;
                    $data['sign']        = (string) $newBuildUpQuantityItem->sign;
                    $data['sign_symbol'] = $newBuildUpQuantityItem->getSigntext();
                    $data['relation_id'] = $newBuildUpQuantityItem->sub_package_variation_order_item_id;
                    $data['total']       = $newBuildUpQuantityItem->calculateTotal();
                    $data['_csrf_token'] = $form->getCSRFToken();

                    foreach ( $formulatedColumnNames as $constant )
                    {
                        $formulatedColumn                        = $newBuildUpQuantityItem->getFormulatedColumnByName($constant, Doctrine_Core::HYDRATE_ARRAY);
                        $finalValue                              = $formulatedColumn ? $formulatedColumn['final_value'] : 0;
                        $data[$constant . '-final_value']        = $finalValue;
                        $data[$constant . '-value']              = $formulatedColumn ? $formulatedColumn['value'] : '';
                        $data[$constant . '-has_cell_reference'] = false;
                        $data[$constant . '-has_formula']        = $formulatedColumn && $formulatedColumn['value'] != $formulatedColumn['final_value'] ? true : false;
                    }

                    $success  = true;
                    $errorMsg = null;
                }
                catch (Exception $e)
                {
                    $errorMsg = $e->getMessage();
                }
                break;
            default:
                throw new Exception('invalid paste operation');
        }

        return $this->renderJson(array( 'success' => $success, 'errorMsg' => $errorMsg, 'data' => $data ));
    }

    public function executeSpBuildUpSummaryRoundingUpdate(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $variationOrderItem = Doctrine_Core::getTable('SubPackageVariationOrderItem')->find($request->getParameter('id')) and
            $request->hasParameter('type')
        );

        switch ($request->getParameter('type'))
        {
            case "addition":
                $type = SubPackageVariationOrderBuildUpQuantityItem::TYPE_ADDITIONAL_QTY;
                break;
            case "omission":
                $type = SubPackageVariationOrderBuildUpQuantityItem::TYPE_OMISSION_QTY;
                break;
            default:
                throw new Exception('invalid type');
        }

        $buildUpQuantitySummary = SubPackageVariationOrderBuildUpQuantitySummaryTable::getByVariationOrderItemIdAndType($variationOrderItem->id, $type);

        $buildUpQuantitySummary->rounding_type = $request->getParameter('rounding_type');

        $buildUpQuantitySummary->save();
        $buildUpQuantitySummary->refresh();

        return $this->renderJson(array(
            'total_quantity' => number_format($buildUpQuantitySummary->calculateTotalQuantity(), 2, '.', ''),
            'final_quantity' => number_format($buildUpQuantitySummary->getTotalQuantityAfterConversion(), 2, '.', ''),
            'rounding_type'  => $buildUpQuantitySummary->rounding_type
        ));
    }

    public function executeSpBuildUpSummaryApplyConversionFactorUpdate(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $variationOrderItem = Doctrine_Core::getTable('SubPackageVariationOrderItem')->find($request->getParameter('id')) and
            $request->hasParameter('type')
        );

        switch ($request->getParameter('type'))
        {
            case "addition":
                $type = SubPackageVariationOrderBuildUpQuantityItem::TYPE_ADDITIONAL_QTY;
                break;
            case "omission":
                $type = SubPackageVariationOrderBuildUpQuantityItem::TYPE_OMISSION_QTY;
                break;
            default:
                throw new Exception('invalid type');
        }

        $value = $request->getParameter('value');

        $buildUpQuantitySummary = SubPackageVariationOrderBuildUpQuantitySummaryTable::getByVariationOrderItemIdAndType($variationOrderItem->id, $type);

        $buildUpQuantitySummary->apply_conversion_factor = $value;
        $buildUpQuantitySummary->save();

        $buildUpQuantitySummary->refresh();

        return $this->renderJson(array(
            'conversion_factor_amount'   => number_format($buildUpQuantitySummary->conversion_factor_amount, 2, '.', ''),
            'conversion_factor_operator' => $buildUpQuantitySummary->conversion_factor_operator,
            'final_quantity'             => number_format($buildUpQuantitySummary->getTotalQuantityAfterConversion(), 2, '.', '')
        ));
    }

    public function executeSpBuildUpSummaryConversionFactorUpdate(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $variationOrderItem = Doctrine_Core::getTable('SubPackageVariationOrderItem')->find($request->getParameter('id')) and
            $request->hasParameter('type')
        );

        switch ($request->getParameter('type'))
        {
            case "addition":
                $type = SubPackageVariationOrderBuildUpQuantityItem::TYPE_ADDITIONAL_QTY;
                break;
            case "omission":
                $type = SubPackageVariationOrderBuildUpQuantityItem::TYPE_OMISSION_QTY;
                break;
            default:
                throw new Exception('invalid type');
        }

        $buildUpQuantitySummary = SubPackageVariationOrderBuildUpQuantitySummaryTable::getByVariationOrderItemIdAndType($variationOrderItem->id, $type);

        $val = $request->getParameter('val');

        switch ($request->getParameter('token'))
        {
            case 'amount':
                $conversionFactorAmount                           = strlen($val) > 0 ? floatval($val) : 0;
                $buildUpQuantitySummary->conversion_factor_amount = $conversionFactorAmount;
                break;
            case 'operator':
                $buildUpQuantitySummary->conversion_factor_operator = $val;
                break;
            default:
                break;
        }

        $buildUpQuantitySummary->save();
        $buildUpQuantitySummary->refresh();

        return $this->renderJson(array(
            'conversion_factor_amount'   => number_format($buildUpQuantitySummary->conversion_factor_amount, 2, '.', ''),
            'final_quantity'             => number_format($buildUpQuantitySummary->getTotalQuantityAfterConversion(), 2, '.', ''),
            'conversion_factor_operator' => $buildUpQuantitySummary->conversion_factor_operator
        ));
    }

    public function executeGetSpBillList(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $variationOrder = Doctrine_Core::getTable('SubPackageVariationOrder')->find($request->getParameter('void'))
        );

        $pdo = $variationOrder->getTable()->getConnection()->getDbh();

        $stmt = $pdo->prepare("SELECT bill.id, bill.title
            FROM " . ProjectStructureTable::getInstance()->getTableName() . " bill
            JOIN " . BillTypeTable::getInstance()->getTableName() . " t ON t.project_structure_id = bill.id
            JOIN " . BillElementTable::getInstance()->getTableName() . " e ON e.project_structure_id = t.project_structure_id
            JOIN " . BillItemTable::getInstance()->getTableName() . " i ON i.element_id = e.id
            JOIN " . SubPackagePostContractBillItemRateTable::getInstance()->getTableName() . " sp ON sp.bill_item_id = i.id
            WHERE sp.sub_package_id = " . $variationOrder->sub_package_id . " AND bill.type = " . ProjectStructure::TYPE_BILL . "
            AND t.type <> " . BillType::TYPE_PRELIMINARY . " AND t.type <> " . BillType::TYPE_PRIMECOST . "
            AND e.deleted_at IS NULL AND i.deleted_at IS NULL AND i.project_revision_deleted_at IS NULL
            GROUP BY bill.id ORDER BY bill.lft, bill.level ASC");

        $stmt->execute();

        $bills = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ( count($bills) == 0 )
        {
            array_push($bills, array(
                'id'    => Constants::GRID_LAST_ROW,
                'title' => ''
            ));
        }

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $bills
        ));
    }

    public function executeGetSpClaimStatus(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $variationOrder = Doctrine_Core::getTable('SubPackageVariationOrder')->find($request->getParameter('id'))
        );

        $form = new BaseForm();

        return $this->renderJson(array(
            'can_add_new_claim'     => $variationOrder->canAddNewClaim(),
            'count'                 => $variationOrder->Claims->count(),
            'can_edit_claim_amount' => $variationOrder->canEditClaimAmount(),
            '_csrf_token'           => $form->getCSRFToken()
        ));
    }

    public function executeGetSpTypeReferenceList(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('bid')) and
            $variationOrder = Doctrine_Core::getTable('SubPackageVariationOrder')->find($request->getParameter('void'))
        );

        $variationOrderItem = Doctrine_Core::getTable('SubPackageVariationOrderItem')->find(intval($request->getParameter('vid')));

        $pdo = $bill->getTable()->getConnection()->getDbh();

        $stmt = $pdo->prepare("SELECT cs.id AS bill_column_setting_id, cs.name AS description, pcr.new_name AS new_name, r.counter AS counter, pcr.id AS type_reference_id FROM " . BillColumnSettingTable::getInstance()->getTableName() . " cs
        JOIN " . SubPackageTypeReferenceTable::getInstance()->getTableName() . " r ON r.bill_column_setting_id = cs.id
        LEFT JOIN " . PostContractStandardClaimTypeReferenceTable::getInstance()->getTableName() . " pcr
        ON pcr.bill_column_setting_id = r.bill_column_setting_id AND pcr.counter = r.counter
        WHERE r.sub_package_id = " . $variationOrder->sub_package_id . "
        AND cs.project_structure_id = " . $bill->id . " AND cs.deleted_at IS NULL ORDER BY cs.id, r.counter");

        $stmt->execute();

        $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $billColumnSettingsArray = array();
        $form                    = new BaseForm();

        $assignedTypeReferenceIds = array();

        if ( $variationOrderItem )
        {
            $stmt = $pdo->prepare("SELECT type_reference_id FROM " . SubPackageVariationOrderItemUnitTable::getInstance()->getTableName() . "
            WHERE sub_package_variation_order_item_id = " . $variationOrderItem->id);

            $stmt->execute();

            $assignedTypeReferenceIds = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
        }

        $items = array();

        foreach ( $records as $record )
        {
            if ( !array_key_exists($record['bill_column_setting_id'], $billColumnSettingsArray) )
            {
                array_push($items, array(
                    'id'          => 'type' . '-' . $record['bill_column_setting_id'],
                    'description' => $record['description'],
                    'new_name'    => '',
                    'level'       => 0
                ));

                $billColumnSettingsArray[$record['bill_column_setting_id']] = $record['bill_column_setting_id'];
            }

            $record['id']            = $record['bill_column_setting_id'] . '-' . $record['counter'];
            $record['description']   = ( !is_null($record['new_name']) and strlen($record['new_name']) > 0 ) ? $record['new_name'] : 'Unit ' . $record['counter'];
            $record['new_name']      = "";
            $record['relation_id']   = $record['bill_column_setting_id'];
            $record['relation_name'] = $record['description'];
            $record['level']         = 1;
            $record['selected']      = false;
            $record['_csrf_token']   = $form->getCSRFToken();

            $record['selected'] = in_array($record['type_reference_id'], $assignedTypeReferenceIds) ? true : false;

            array_push($items, $record);
        }

        unset( $billColumnSettingsArray, $records, $assignedTypeReferenceIds );

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $items
        ));
    }

    public function executeGetSpBillElementList(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('bid')) and
            $variationOrder = Doctrine_Core::getTable('SubPackageVariationOrder')->find($request->getParameter('void')) and
            $request->hasParameter("tid") and strlen($request->getParameter("tid")) > 0
        );

        $typeUnitData = explode("-", $request->getParameter("tid"));

        $this->forward404Unless($billColumnSetting = Doctrine_Core::getTable('BillColumnSetting')->find($typeUnitData[0]));

        $typeReference = DoctrineQuery::create()->select('t.id')
            ->from('PostContractStandardClaimTypeReference t')
            ->where('t.bill_column_setting_id = ?', array( $billColumnSetting->id ))
            ->andWhere('t.post_contract_id = ?', array( $bill->getRoot()->PostContract->id ))
            ->andWhere('t.counter = ? ', array( $typeUnitData[1] ))
            ->limit(1)
            ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
            ->fetchOne();

        if ( !$typeReference )
        {
            $typeReference                         = new PostContractStandardClaimTypeReference();
            $typeReference->bill_column_setting_id = $billColumnSetting->id;
            $typeReference->post_contract_id       = $bill->getRoot()->PostContract->id;
            $typeReference->counter                = $typeUnitData[1];

            $typeReference->save();
        }

        $pdo = $bill->getTable()->getConnection()->getDbh();

        $stmt = $pdo->prepare("SELECT DISTINCT e.id, e.description, e.priority FROM " . BillElementTable::getInstance()->getTableName() . " e
        JOIN " . BillItemTable::getInstance()->getTableName() . " i ON i.element_id = e.id
        JOIN " . SubPackagePostContractBillItemRateTable::getInstance()->getTableName() . " r ON r.bill_item_id = i.id
        WHERE r.sub_package_id = " . $variationOrder->sub_package_id . "
        AND e.project_structure_id = " . $bill->id . "
        AND e.deleted_at IS NULL AND i.deleted_at IS NULL AND i.project_revision_deleted_at IS NULL
        ORDER BY e.priority ASC");

        $stmt->execute();

        $elements = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $elements
        ));
    }

    public function executeGetSpBillItemList(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $element = Doctrine_Core::getTable('BillElement')->find($request->getParameter('eid')) and
            $variationOrder = Doctrine_Core::getTable('SubPackageVariationOrder')->find($request->getParameter('void')) and
            $request->hasParameter("tid") and strlen($request->getParameter("tid")) > 0
        );

        $typeUnitData = explode("-", $request->getParameter("tid"));

        $this->forward404Unless($billColumnSetting = Doctrine_Core::getTable('BillColumnSetting')->find($typeUnitData[0]));

        $pdo = $element->getTable()->getConnection()->getDbh();

        $stmt = $pdo->prepare("SELECT DISTINCT p.id, p.element_id, p.root_id, p.description, p.type, p.uom_id, p.level, p.priority, p.lft,
        uom.symbol AS uom_symbol, r.bill_ref_element_no, r.bill_ref_page_no, r.bill_ref_char, spi.rate
        FROM " . BillItemTable::getInstance()->getTableName() . " c
        JOIN " . BillItemTable::getInstance()->getTableName() . " p ON c.lft BETWEEN p.lft AND p.rgt
        LEFT JOIN " . UnitOfMeasurementTable::getInstance()->getTableName() . " uom ON p.uom_id = uom.id AND uom.deleted_at IS NULL
        LEFT JOIN " . PostContractBillItemRateTable::getInstance()->getTableName() . " r ON p.id = r.bill_item_id AND r.post_contract_id = " . $element->ProjectStructure->getRoot()->PostContract->id . "
        JOIN " . SubPackagePostContractBillItemRateTable::getInstance()->getTableName() . " spi ON spi.bill_item_id = p.id
        JOIN " . BillElementTable::getInstance()->getTableName() . " e ON p.element_id = e.id
        WHERE spi.sub_package_id = " . $variationOrder->sub_package_id . "
        AND e.id = " . $element->id . " AND c.root_id = p.root_id
        AND c.project_revision_deleted_at IS NULL AND c.deleted_at IS NULL
        AND p.project_revision_deleted_at IS NULL AND p.deleted_at IS NULL
        AND e.deleted_at IS NULL ORDER BY p.priority, p.lft, p.level ASC");

        $stmt->execute();

        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $billItemTypeFormulatedColumnName = $billColumnSetting->use_original_quantity ? BillItemTypeReference::FORMULATED_COLUMN_QTY_PER_UNIT : BillItemTypeReference::FORMULATED_COLUMN_QTY_PER_UNIT_REMEASUREMENT;

        $stmt = $pdo->prepare("SELECT DISTINCT r.bill_item_id, ifc.final_value, ifc.has_build_up
        FROM " . SubPackagePostContractBillItemRateTable::getInstance()->getTableName() . " spi
        JOIN " . BillItemTypeReferenceTable::getInstance()->getTableName() . " r ON r.bill_item_id = spi.bill_item_id
        JOIN " . BillItemTypeReferenceFormulatedColumnTable::getInstance()->getTableName() . " ifc ON ifc.relation_id = r.id
        WHERE spi.sub_package_id = " . $variationOrder->sub_package_id . "
        AND r.bill_column_setting_id = " . $billColumnSetting->id . " AND r.include IS TRUE
        AND ifc.column_name = '" . $billItemTypeFormulatedColumnName . "' AND ifc.final_value <> 0
        AND r.deleted_at IS NULL AND ifc.deleted_at IS NULL");

        $stmt->execute();

        $quantities = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $pageNoPrefix = $element->ProjectStructure->BillLayoutSetting->page_no_prefix;

        $stmt = $pdo->prepare("SELECT DISTINCT i.bill_item_id FROM " . SubPackageVariationOrderItemTable::getInstance()->getTableName() . " i
        LEFT JOIN " . SubPackageVariationOrderItemUnitTable::getInstance()->getTableName() . " u ON u.sub_package_variation_order_item_id = i.id
        JOIN " . PostContractStandardClaimTypeReferenceTable::getInstance()->getTableName() . " r ON u.type_reference_id = r.id
        WHERE r.bill_column_setting_id = " . $billColumnSetting->id . " AND counter = " . $typeUnitData[1] . "
        AND post_contract_id = " . $element->ProjectStructure->getRoot()->PostContract->id . "
        AND i.bill_item_id > 0 AND i.sub_package_variation_order_id = " . $variationOrder->id . " AND i.deleted_at IS NULL");

        $stmt->execute();

        $variationOrderItemUnitReferences = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);

        $notSelectableItemType = array(
            BillItem::TYPE_HEADER,
            BillItem::TYPE_HEADER_N
        );

        foreach ( $items as $key => $item )
        {
            $items[$key]['bill_ref']                  = BillItemTable::generateBillRef($pageNoPrefix, $item['bill_ref_element_no'], $item['bill_ref_page_no'], $item['bill_ref_char']);
            $items[$key]['type']                      = (string) $item['type'];
            $items[$key]['uom_id']                    = $item['uom_id'] > 0 ? (string) $item['uom_id'] : '-1';
            $items[$key]['qty_per_unit-value']        = 0;
            $items[$key]['qty_per_unit-final_value']  = 0;
            $items[$key]['qty_per_unit-has_build_up'] = false;

            if ( ! in_array($item['type'], $notSelectableItemType) )
            {
                $items[$key]['selected'] = in_array($item['id'], $variationOrderItemUnitReferences) ? true : false;
            }

            foreach ( $quantities as $quantity )
            {
                if ( $quantity['bill_item_id'] == $item['id'] )
                {
                    $items[$key]['qty_per_unit-value']        = $quantity['final_value'];
                    $items[$key]['qty_per_unit-final_value']  = $quantity['final_value'];
                    $items[$key]['qty_per_unit-has_build_up'] = $quantity['has_build_up'];

                    unset( $quantity );
                }
            }

            unset( $item );
        }

        unset( $billItemIds, $quantities, $variationOrderItemUnitReferences );

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $items
        ));
    }

    public function executeSpOmitBillItems(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $variationOrder = Doctrine_Core::getTable('SubPackageVariationOrder')->find($request->getParameter('void')) and
            $request->hasParameter("vid") and
            $request->hasParameter("sid") and
            $request->hasParameter("usid") and
            $request->hasParameter("uid") and strlen($request->getParameter("uid")) > 0
        );

        try
        {
            $typeUnitData = Utilities::array_filter_integer(explode("-", $request->getParameter("uid")));

            $this->forward404Unless($billColumnSetting = Doctrine_Core::getTable('BillColumnSetting')->find($typeUnitData[0]));

            SubPackageVariationOrderItemTable::omitBillItems(
                $request->getParameter('vid'),
                Utilities::array_filter_integer(explode(",", $request->getParameter("sid"))),
                Utilities::array_filter_integer(explode(",", $request->getParameter("usid"))),
                $variationOrder, $billColumnSetting, $typeUnitData[1]);

            $errorMsg = null;
            $success  = true;
        }
        catch (Exception $e)
        {
            $errorMsg = $e->getMessage();
            $success  = false;
        }

        return $this->renderJson(array( 'success' => $success, 'errorMsg' => $errorMsg ));
    }

    public function executeGetSpVariationOrderItemUnitList(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $variationOrderItem = Doctrine_Core::getTable('SubPackageVariationOrderItem')->find($request->getParameter('vid')) and
            $variationOrderItem->bill_item_id > 0
        );

        $billColumnSettingItems = array();
        $records                = array();
        $pdo                    = $variationOrderItem->getTable()->getConnection()->getDbh();

        $stmt = $pdo->prepare("SELECT cs.*
        FROM " . BillColumnSettingTable::getInstance()->getTableName() . " cs
        JOIN " . PostContractStandardClaimTypeReferenceTable::getInstance()->getTablename() . " r ON r.bill_column_setting_id = cs.id
        JOIN " . SubPackageVariationOrderItemUnitTable::getInstance()->getTableName() . " u ON u.type_reference_id = r.id
        WHERE u.sub_package_variation_order_item_id = " . $variationOrderItem->id . "
        AND cs.project_structure_id = " . $variationOrderItem->BillItem->Element->project_structure_id . " AND cs.deleted_at IS NULL");

        $stmt->execute();

        $billColumnSetting = $stmt->fetch(PDO::FETCH_ASSOC);// always have bill column setting since this is an omitted bill items

        //get new name from PostContractStandardClaimTypeReferenceTable for case where there is no assignment in VariationOrderItemUnitTable
        $stmt = $pdo->prepare("SELECT DISTINCT r.id, r.bill_column_setting_id, r.post_contract_id, r.counter, r.new_name
        FROM " . PostContractStandardClaimTypeReferenceTable::getInstance()->getTablename() . " r
        JOIN " . SubPackageTypeReferenceTable::getInstance()->getTableName() . " spr ON spr.bill_column_setting_id = r.bill_column_setting_id AND spr.counter = r.counter
        JOIN " . PostContractTable::getInstance()->getTableName() . " pc ON r.post_contract_id = pc.id
        WHERE spr.sub_package_id = " . $variationOrderItem->SubPackageVariationOrder->sub_package_id . "
        AND r.bill_column_setting_id = " . $billColumnSetting['id'] . "
        AND pc.project_structure_id = " . $variationOrderItem->SubPackageVariationOrder->SubPackage->project_structure_id);

        $stmt->execute();

        $postContractTypeReferences = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ( $postContractTypeReferences as $postContractTypeReference )
        {
            $billColumnSettingItems[$postContractTypeReference['counter']] = array(
                'id'       => $postContractTypeReference['id'],
                'new_name' => $postContractTypeReference['new_name']
            );
        }

        $form = new BaseForm();

        $stmt = $pdo->prepare("SELECT type_reference_id FROM " . SubPackageVariationOrderItemUnitTable::getInstance()->getTableName() . " WHERE sub_package_variation_order_item_id = " . $variationOrderItem->id);

        $stmt->execute();

        $assignedTypeReferenceIds = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);

        $stmt = $pdo->prepare("SELECT bill_column_setting_id, counter FROM " . SubPackageTypeReferenceTable::getInstance()->getTableName() . "
        WHERE bill_column_setting_id = " . $billColumnSetting['id'] . "
        AND sub_package_id = " . $variationOrderItem->SubPackageVariationOrder->sub_package_id . "
        ORDER BY counter");

        $stmt->execute();

        $typeReferences = $stmt->fetchAll(PDO::FETCH_ASSOC);

        array_push($records, array(
            'id'          => 'type' . '-' . $billColumnSetting['id'],
            'description' => $billColumnSetting['name'],
            'new_name'    => '',
            'level'       => 0
        ));

        foreach ( $typeReferences as $typeReference )
        {
            $record['id']            = $billColumnSetting['id'] . '-' . $typeReference['counter'];
            $record['description']   = 'Unit ' . $typeReference['counter'];
            $record['new_name']      = "";
            $record['relation_id']   = $billColumnSetting['id'];
            $record['relation_name'] = $billColumnSetting['name'];
            $record['level']         = 1;
            $record['selected']      = false;
            $record['_csrf_token']   = $form->getCSRFToken();

            if ( array_key_exists($typeReference['counter'], $billColumnSettingItems) )
            {
                if ( $billColumnSettingItems[$typeReference['counter']]['new_name'] != null and strlen($billColumnSettingItems[$typeReference['counter']]['new_name']) > 0 )
                {
                    $record['description'] = $billColumnSettingItems[$typeReference['counter']]['new_name'];
                }

                $record['selected'] = in_array($billColumnSettingItems[$typeReference['counter']]['id'], $assignedTypeReferenceIds) ? true : false;
            }

            array_push($records, $record);

            unset( $record );
        }

        unset( $billColumnSettings, $assignedTypeReferenceIds );

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $records
        ));
    }

    public function executeSpTotalUnitUpdate(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $variationOrderItem = Doctrine_Core::getTable('SubPackageVariationOrderItem')->find($request->getParameter('vid')) and
            $request->hasParameter("ids")
        );

        if ( $variationOrderItem->bill_item_id > 0 )
        {
            $bill = $variationOrderItem->BillItem->Element->ProjectStructure;
        }
        else
        {
            $this->forward404Unless($bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('bid')));
        }

        try
        {
            $variationOrderItem->updateTotalUnit(Utilities::array_filter_integer($request->getParameter("ids")), $bill);

            $errorMsg = null;
            $success  = true;
        }
        catch (Exception $e)
        {
            $errorMsg = $e->getMessage();
            $success  = false;
        }

        return $this->renderJson(array( 'success' => $success, 'errorMsg' => $errorMsg, 'id' => $variationOrderItem->id ));
    }

    public function executeGetSpClaimList(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $variationOrder = Doctrine_Core::getTable('SubPackageVariationOrder')->find($request->getParameter('id'))
        );

        $claims = DoctrineQuery::create()->select('c.id, c.revision, c.sub_package_variation_order_id, c.status, c.is_viewing, c.updated_at')
            ->from('SubPackageVariationOrderClaim c')
            ->where('c.sub_package_variation_order_id = ?', $variationOrder->id)
            ->addOrderBy('c.revision ASC')
            ->fetchArray();

        $count = count($claims);
        $form  = new BaseForm();

        foreach ( $claims as $key => $claim )
        {
            if ( $count == $key + 1 )
            {
                $canBeEdited  = true;
                $canBeDeleted = true;
            }
            else
            {
                $canBeEdited  = false;
                $canBeDeleted = false;
            }

            $claims[$key]['can_be_edited']  = $canBeEdited;
            $claims[$key]['can_be_deleted'] = $canBeDeleted;
            $claims[$key]['updated_at']     = date('d/m/Y H:i', strtotime($claim['updated_at']));
            $claims[$key]['_csrf_token']    = $form->getCSRFToken();

            unset( $claim );
        }

        if ( count($claims) == 0 )
        {
            array_push($claims, array(
                'id'                             => Constants::GRID_LAST_ROW,
                'revision'                       => - 1,
                'sub_package_variation_order_id' => $variationOrder->id,
                'status'                         => - 1,
                'is_viewing'                     => false,
                'updated_at'                     => '-',
                'can_be_edited'                  => false,
                'can_be_deleted'                 => false,
                '_csrf_token'                    => $form->getCSRFToken()
            ));
        }

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $claims
        ));
    }

    public function executeSpClaimAdd(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $variationOrder = Doctrine_Core::getTable('SubPackageVariationOrder')->find($request->getParameter('id'))
        );

        $con = $variationOrder->getTable()->getConnection();

        try
        {
            $con->beginTransaction();
            $form = new BaseForm();

            if ( $variationOrder->canAddNewClaim() )
            {
                $claim                                 = new SubPackageVariationOrderClaim();
                $claim->sub_package_variation_order_id = $variationOrder->id;

                $claim->save();

                $previousClaims = DoctrineQuery::create()->select('c.id, c.is_viewing')
                    ->from('SubPackageVariationOrderClaim c')
                    ->where('c.sub_package_variation_order_id = ?', $claim->sub_package_variation_order_id)
                    ->andWhere('c.id <> ?', $claim->id)
                    ->andWhere('c.revision < ? ', $claim->revision)
                    ->fetchArray();

                foreach ( $previousClaims as $key => $previousClaim )
                {
                    $previousClaims[$key]['can_be_edited']  = false;
                    $previousClaims[$key]['can_be_deleted'] = false;
                }

                $item = array(
                    'id'                 => $claim->id,
                    'revision'           => $claim->revision,
                    'variation_order_id' => $claim->sub_package_variation_order_id,
                    'status'             => $claim->status,
                    'is_viewing'         => $claim->is_viewing,
                    'can_be_edited'      => true,
                    'can_be_deleted'     => true,
                    'updated_at'         => date('d/m/Y H:i', strtotime($claim->updated_at)),
                    '_csrf_token'        => $form->getCSRFToken()
                );
            }
            else
            {
                throw new Exception('Cannot add new claim because there is still an in progress claim for variation order with id:' . $variationOrder->id);
            }

            $con->commit();

            $success = true;

            $errorMsg = null;
        }
        catch (Exception $e)
        {
            $con->rollback();
            $errorMsg       = $e->getMessage();
            $success        = false;
            $item           = array();
            $previousClaims = array();
        }

        return $this->renderJson(array( 'success' => $success, 'errorMsg' => $errorMsg, 'item' => $item, 'prev_claims' => $previousClaims ));
    }

    public function executeSpClaimUpdate(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $claim = Doctrine_Core::getTable('SubPackageVariationOrderClaim')->find($request->getParameter('id'))
        );

        $rowData = array();

        $con = $claim->getTable()->getConnection();

        try
        {
            $con->beginTransaction();

            $fieldName  = $request->getParameter('attr_name');
            $fieldValue = $request->getParameter('val');

            $claim->{'set' . sfInflector::camelize($fieldName)}($fieldValue);

            $claim->save($con);

            $con->commit();

            $success = true;

            $errorMsg = null;

            $rowData[$fieldName] = $claim->{$request->getParameter('attr_name')};
        }
        catch (Exception $e)
        {
            $con->rollback();
            $errorMsg = $e->getMessage();
            $success  = false;
        }

        return $this->renderJson(array( 'success' => $success, 'errorMsg' => $errorMsg, 'data' => $rowData ));
    }

    public function executeSpClaimDelete(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $claim = Doctrine_Core::getTable('SubPackageVariationOrderClaim')->find($request->getParameter('id'))
        );

        $defaultLastRow = false;

        try
        {
            $previousClaim    = $claim->getPreviousClaim();
            $variationOrderId = $claim->sub_package_variation_order_id;

            $data['id']        = $claim->id;
            $data['prev_item'] = $previousClaim ? $previousClaim->id : - 1;

            $claim->delete();

            $variationOrder = Doctrine_Core::getTable('SubPackageVariationOrder')->find($variationOrderId);

            if ( $variationOrder->Claims->count() == 0 )
            {
                $form = new BaseForm();

                $data['default_last_row'] = array(
                    'id'                             => Constants::GRID_LAST_ROW,
                    'revision'                       => - 1,
                    'sub_package_variation_order_id' => $variationOrder->id,
                    'status'                         => - 1,
                    'is_viewing'                     => false,
                    'updated_at'                     => '-',
                    'can_be_edited'                  => false,
                    'can_be_deleted'                 => false,
                    '_csrf_token'                    => $form->getCSRFToken()
                );
            }
            else
            {
                $data['default_last_row'] = $defaultLastRow;
            }

            $success  = true;
            $errorMsg = null;
        }
        catch (Exception $e)
        {
            $errorMsg = $e->getMessage();
            $data     = array();
            $success  = false;
        }

        return $this->renderJson(array( 'success' => $success, 'errorMsg' => $errorMsg, 'data' => $data ));
    }

    public function executeViewSpClaimRevision(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $claim = Doctrine_Core::getTable('SubPackageVariationOrderClaim')->find($request->getParameter('id'))
        );

        $con = $claim->getTable()->getConnection();

        try
        {
            $con->beginTransaction();

            $variationOrder = $claim->SubPackageVariationOrder;

            $viewingClaim = $variationOrder->getViewingClaim();

            $claim->is_viewing = true;
            $claim->save();

            if ( $viewingClaim->id != $claim->id )
            {
                $viewingClaim->is_viewing = false;
                $viewingClaim->save();
            }

            $con->commit();

            $success  = true;
            $errorMsg = null;

            $items = array(
                array( 'id' => $claim->id, 'is_viewing' => $claim->is_viewing ),
                array( 'id' => $viewingClaim->id, 'is_viewing' => $viewingClaim->is_viewing )
            );
        }
        catch (Exception $e)
        {
            $con->rollback();
            $errorMsg = $e->getMessage();
            $items    = array();
            $success  = false;
        }

        return $this->renderJson(array( 'success' => $success, 'errorMsg' => $errorMsg, 'items' => $items ));
    }

    public function executeSpClaimItemUpdate(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $variationOrderItem = Doctrine_Core::getTable('SubPackageVariationOrderItem')->find($request->getParameter('id'))
        );

        $rowData = array();

        $con = $variationOrderItem->getTable()->getConnection();

        try
        {
            $con->beginTransaction();

            $fieldName  = $request->getParameter('attr_name');
            $fieldValue = $request->getParameter('val');

            $claimItem = $variationOrderItem->updateClaimItem($fieldName, $fieldValue);

            $con->commit();

            $success = true;

            $errorMsg = null;

            $rowData['current_percentage-value']    = $claimItem->current_percentage;
            $rowData['current_amount-value']        = $claimItem->current_amount;
            $rowData['up_to_date_percentage-value'] = $claimItem->up_to_date_percentage;
            $rowData['up_to_date_amount-value']     = $claimItem->up_to_date_amount;

        }
        catch (Exception $e)
        {
            $con->rollback();
            $errorMsg = $e->getMessage();
            $success  = false;
        }

        return $this->renderJson(array( 'success' => $success, 'errorMsg' => $errorMsg, 'data' => $rowData ));
    }

    public function executeGetSpLinkInfo(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest() and
            $item = Doctrine_Core::getTable('SubPackageVariationOrderItem')->find($request->getParameter('id')) and
            $request->hasParameter('t')

        );

        switch ($request->getParameter('t'))
        {
            case "addition":
                $type = SubPackageVariationOrderBuildUpQuantityItem::TYPE_ADDITIONAL_QTY;
                break;
            case "omission":
                $type = SubPackageVariationOrderBuildUpQuantityItem::TYPE_OMISSION_QTY;
                break;
            default:
                throw new Exception('invalid type');
        }

        $scheduleOfQuantityLinks = ScheduleOfQuantitySubPackageVOItemXrefTable::getInstance()
            ->createQuery('x')->select('x.schedule_of_quantity_item_id')
            ->where('x.sub_package_variation_order_item_id = ?', $item->id)
            ->andWhere('x.type = ?', $type)
            ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
            ->count();

        $hasLinkedQty = $scheduleOfQuantityLinks > 0 ? true : false;

        return $this->renderJson(array( 'has_linked_qty' => $hasLinkedQty ));
    }

    public function executeGetSpScheduleOfQuantities(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $item = Doctrine_Core::getTable('SubPackageVariationOrderItem')->find($request->getParameter('id')) and
            $request->hasParameter('t')
        );

        $projectId = $item->BillItem->Element->ProjectStructure->root_id;

        switch ($request->getParameter('t'))
        {
            case "addition":
                $type = SubPackageVariationOrderBuildUpQuantityItem::TYPE_ADDITIONAL_QTY;
                break;
            case "omission":
                $type = SubPackageVariationOrderBuildUpQuantityItem::TYPE_OMISSION_QTY;
                break;
            default:
                throw new Exception("invalid type");
        }

        $pdo = $item->getTable()->getConnection()->getDbh();

        $stmt = $pdo->prepare("SELECT DISTINCT p.id, p.description, p.type, p.lft, p.level, p.priority, p.third_party_identifier, p.schedule_of_quantity_trade_id,
            p.identifier_type, p.uom_id AS uom_id, uom.symbol AS uom_symbol
            FROM " . ScheduleOfQuantitySubPackageVOItemXrefTable::getInstance()->getTableName() . " xref
            JOIN " . ScheduleOfQuantityItemTable::getInstance()->getTableName() . " c ON c.id = xref.schedule_of_quantity_item_id
            JOIN " . ScheduleOfQuantityItemTable::getInstance()->getTableName() . " p ON c.lft BETWEEN p.lft AND p.rgt
            LEFT JOIN " . UnitOfMeasurementTable::getInstance()->getTableName() . " uom ON p.uom_id = uom.id AND uom.deleted_at IS NULL
            JOIN " . ScheduleOfQuantityTradeTable::getInstance()->getTableName() . " trade ON p.schedule_of_quantity_trade_id = trade.id
            JOIN " . ScheduleOfQuantityTable::getInstance()->getTableName() . " soq ON trade.schedule_of_quantity_id = soq.id
            WHERE xref.sub_package_variation_order_item_id = " . $item->id . " AND xref.type = " . $type . "
            AND c.root_id = p.root_id AND c.schedule_of_quantity_trade_id = p.schedule_of_quantity_trade_id AND c.deleted_at IS NULL AND p.deleted_at IS NULL
            AND soq.project_structure_id = " . $projectId . " AND trade.deleted_at IS NULL AND soq.deleted_at IS NULL
            ORDER BY p.schedule_of_quantity_trade_id, p.priority, p.lft, p.level ASC");

        $stmt->execute();

        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmt = $pdo->prepare("SELECT ifc.relation_id, ifc.column_name, ifc.final_value, ifc.value, ifc.linked, ifc.has_build_up
            FROM " . ScheduleOfQuantityItemFormulatedColumnTable::getInstance()->getTableName() . " ifc
            JOIN " . ScheduleOfQuantityItemTable::getInstance()->getTableName() . " i ON i.id = ifc.relation_id
            JOIN " . ScheduleOfQuantitySubPackageVOItemXrefTable::getInstance()->getTableName() . " xref ON i.id = xref.schedule_of_quantity_item_id
            WHERE xref.sub_package_variation_order_item_id = " . $item->id . " AND xref.type = " . $type . " AND i.deleted_at IS NULL
            AND ifc.deleted_at IS NULL AND ifc.final_value <> 0");

        $stmt->execute();

        $itemFormulatedColumns = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $formulatedColumns = array();

        foreach ( $itemFormulatedColumns as $itemFormulatedColumn )
        {
            if ( !array_key_exists($itemFormulatedColumn['relation_id'], $formulatedColumns) )
            {
                $formulatedColumns[$itemFormulatedColumn['relation_id']] = array();
            }

            array_push($formulatedColumns[$itemFormulatedColumn['relation_id']], $itemFormulatedColumn);

            unset( $itemFormulatedColumn );
        }

        unset( $itemFormulatedColumns );

        foreach ( $items as $key => $item )
        {
            $items[$key]['type']               = (string) $item['type'];
            $items[$key]['uom_id']             = $item['uom_id'] > 0 ? (string) $item['uom_id'] : '-1';
            $items[$key]['editable_total']     = ( $item['type'] != ScheduleOfQuantityItem::TYPE_HEADER ) ? ScheduleOfQuantityItemTable::getBuildUpTotalByIdAndCanEditStatus($item['id'], true) : 0;
            $items[$key]['non_editable_total'] = ( $item['type'] != ScheduleOfQuantityItem::TYPE_HEADER ) ? ScheduleOfQuantityItemTable::getBuildUpTotalByIdAndCanEditStatus($item['id'], false) : 0;

            $items[$key][ScheduleOfQuantityItem::FORMULATED_COLUMN_QUANTITY . '-final_value']        = 0;
            $items[$key][ScheduleOfQuantityItem::FORMULATED_COLUMN_QUANTITY . '-value']              = '';
            $items[$key][ScheduleOfQuantityItem::FORMULATED_COLUMN_QUANTITY . '-has_cell_reference'] = false;
            $items[$key][ScheduleOfQuantityItem::FORMULATED_COLUMN_QUANTITY . '-has_formula']        = false;
            $items[$key][ScheduleOfQuantityItem::FORMULATED_COLUMN_QUANTITY . '-linked']             = false;
            $items[$key][ScheduleOfQuantityItem::FORMULATED_COLUMN_QUANTITY . '-has_build_up']       = false;

            if ( array_key_exists($item['id'], $formulatedColumns) )
            {
                $itemFormulatedColumns = $formulatedColumns[$item['id']];

                foreach ( $itemFormulatedColumns as $formulatedColumn )
                {
                    $items[$key][$formulatedColumn['column_name'] . '-final_value']  = $formulatedColumn['final_value'];
                    $items[$key][$formulatedColumn['column_name'] . '-value']        = $formulatedColumn['value'];
                    $items[$key][$formulatedColumn['column_name'] . '-linked']       = $formulatedColumn['linked'];
                    $items[$key][$formulatedColumn['column_name'] . '-has_build_up'] = $formulatedColumn['has_build_up'];
                    $items[$key][$formulatedColumn['column_name'] . '-has_formula']  = $formulatedColumn && $formulatedColumn['value'] != $formulatedColumn['final_value'] ? true : false;
                }

                unset( $formulatedColumns[$item['id']], $itemFormulatedColumns );
            }
        }

        array_push($items, array(
            'id'                                                                       => Constants::GRID_LAST_ROW,
            'description'                                                              => '',
            'type'                                                                     => (string) ScheduleOfQuantityItem::TYPE_WORK_ITEM,
            'uom_id'                                                                   => '-1',
            'uom_symbol'                                                               => '',
            'updated_at'                                                               => '-',
            'editable_total'                                                           => 0,
            'non_editable_total'                                                       => 0,
            ScheduleOfQuantityItem::FORMULATED_COLUMN_QUANTITY . '-final_value'        => 0,
            ScheduleOfQuantityItem::FORMULATED_COLUMN_QUANTITY . '-value'              => '',
            ScheduleOfQuantityItem::FORMULATED_COLUMN_QUANTITY . '-has_cell_reference' => false,
            ScheduleOfQuantityItem::FORMULATED_COLUMN_QUANTITY . '-has_formula'        => false,
            ScheduleOfQuantityItem::FORMULATED_COLUMN_QUANTITY . '-linked'             => false,
            ScheduleOfQuantityItem::FORMULATED_COLUMN_QUANTITY . '-has_build_up'       => false
        ));

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $items
        ));
    }

    public function executeGetVerifierList(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('pid')) and
            $project->node->isRoot() and
            $variationOrder = Doctrine_Core::getTable('VariationOrder')->find(intval($request->getParameter('variationOrderId')))
        );

        $success   = true;
        $errorMsg  = null;
        $verifiers = ContractManagementClaimVerifierTable::getVerifierList($project, PostContractClaim::TYPE_VARIATION_ORDER);

        $topManagementVerifiers = array_column(PostContractClaimTopManagementVerifier::getAllRecords($variationOrder)->toArray(), 'user_id');

        if((count($topManagementVerifiers) > 0) && empty($verifiers))
        {
            $success = false;
            $errorMsg = 'Default verifiers in eProject are required.';
        }

        return $this->renderJson(array( 'success' => $success, 'errorMsg' => $errorMsg, 'verifiers' => $verifiers, 'topManagementVerifiers' => $topManagementVerifiers ));
    }

    public function executeGetImportedVariationOrders(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('pid'))
        );

        $pdo = $project->getTable()->getConnection()->getDbh();

        if( $request->hasParameter('claimRevisionId') )
        {
            $claimRevision  = Doctrine_Core::getTable('PostContractClaimRevision')->find($request->getParameter('claimRevisionId'));
        }
        else
        {
            $claimRevision = PostContractClaimRevisionTable::getCurrentSelectedProjectRevision($project->PostContract);
        }

        $stmt = $pdo->prepare("SELECT vo.id, vo.description, SUM(COALESCE(i.total_amount, 0)) as total_amount, SUM(COALESCE(c.up_to_date_amount, 0)) as up_to_date_amount
            FROM " . ImportedVariationOrderTable::getInstance()->getTableName() . " vo
            JOIN " . PostContractClaimRevisionTable::getInstance()->getTableName() . " rev ON rev.id = vo.revision_id
            LEFT JOIN " . ImportedVariationOrderItemTable::getInstance()->getTableName() . " i ON vo.id = i.imported_variation_order_id
            LEFT JOIN (
                SELECT i.id, c.up_to_date_amount
                FROM " . ImportedVariationOrderClaimItemTable::getInstance()->getTableName() . " c
                JOIN " . PostContractClaimRevisionTable::getInstance()->getTableName() . " rev ON rev.id = c.revision_id
                JOIN " . ImportedVariationOrderItemTable::getInstance()->getTableName() . " i ON i.id = c.imported_variation_order_item_id
                JOIN " . ImportedVariationOrderTable::getInstance()->getTableName() . " vo ON vo.id = i.imported_variation_order_id
                WHERE vo.project_structure_id = {$project->id}
                AND rev.version = {$claimRevision['version']}
            ) c on c.id = i.id
            WHERE vo.project_structure_id = {$project->id}
            AND rev.version <= {$claimRevision['version']}
            GROUP BY vo.id ORDER BY vo.priority ASC");

        $stmt->execute();

        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach($items as $key => $item)
        {
            $items[ $key ]['attachments'] = count(AttachmentsTable::getAttachments($item['id'], ImportedVariationOrderTable::getInstance()->getClassnameToReturn()));
            $items[ $key ]['class']       = ImportedVariationOrderTable::getInstance()->getClassnameToReturn();
        }

        array_push($items, array(
            'id'           => Constants::GRID_LAST_ROW,
            'description'  => '',
            'total_amount' => 0,
        ));

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $items
        ));
    }

    public function executeGetImportedVariationOrderItems(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('pid')) and
            $variationOrder = Doctrine_Core::getTable('ImportedVariationOrder')->find($request->getParameter('void'))
        );

        $pdo = $project->getTable()->getConnection()->getDbh();

        if( $request->hasParameter('claimRevisionId') )
        {
            $claimRevision  = Doctrine_Core::getTable('PostContractClaimRevision')->find($request->getParameter('claimRevisionId'));
        }
        else
        {
            $claimRevision = PostContractClaimRevisionTable::getCurrentSelectedProjectRevision($project->PostContract);
        }

        $stmt = $pdo->prepare("SELECT i.id, i.description, i.type, i.lft, i.level, i.total_amount, COALESCE(c.up_to_date_amount, 0) as up_to_date_amount, i.total_unit, i.rate, i.uom_symbol as unit, i.quantity
            FROM " . ImportedVariationOrderItemTable::getInstance()->getTableName() . " i
            JOIN " . ImportedVariationOrderTable::getInstance()->getTableName() . " vo ON vo.id = i.imported_variation_order_id
            JOIN " . PostContractClaimRevisionTable::getInstance()->getTableName() . " rev ON rev.id = vo.revision_id
            LEFT JOIN " . ImportedVariationOrderClaimItemTable::getInstance()->getTableName() . " c ON c.imported_variation_order_item_id = i.id AND c.revision_id = {$claimRevision['id']}
            WHERE vo.project_structure_id = {$project->id}
            AND vo.id = {$variationOrder->id}
            AND rev.version <= {$claimRevision['version']}
            ORDER BY i.priority, i.lft, i.level");

        $stmt->execute();

        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach($items as $key => $item)
        {
            $items[ $key ]['type']        = (string)$item['type'];
            $items[ $key ]['attachments'] = count(AttachmentsTable::getAttachments($item['id'], ImportedVariationOrderItemTable::getInstance()->getClassnameToReturn()));
            $items[ $key ]['class']       = ImportedVariationOrderItemTable::getInstance()->getClassnameToReturn();
        }

        array_push($items, array(
            'id'          => Constants::GRID_LAST_ROW,
            'description' => '',
            'type'        => (string)VariationOrderItem::TYPE_WORK_ITEM,
        ));

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $items
        ));
    }

    public function executeGetTopManagementVerifiers(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $project = Doctrine_Core::getTable('ProjectStructure')->find(intval($request->getParameter('pid')))
        );

        $topManagementVerifiers = [];

        foreach(PostContractClaim::getPostContractClaimTopManagementVerifiers($project->MainInformation->getEProjectProject()->id, PostContractClaim::TYPE_VARIATION_ORDER) as $verifierId)
        {
            $eprojectUser = Doctrine_Core::getTable('EProjectUser')->find($verifierId);

            array_push($topManagementVerifiers, [
                'id'   => $eprojectUser->getBuildSpaceUser()->user_id,
                'name' => $eprojectUser->getBuildSpaceUser()->name,
            ]);
        }

        return $this->renderJson([
            'identifier' => 'id',
            'label'      => 'name',
            'items'      => $topManagementVerifiers,
        ]);
    }

    public function executeGetSavedTopManagementVerifiers(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $claimCertificate = Doctrine_Core::getTable('VariationOrder')->find(intval($request->getParameter('variationOrderId')))
        );

        $records = [];

        foreach(PostContractClaimTopManagementVerifier::getAllRecords($claimCertificate) as $record)
        {
            $bsUser = $record->sfGuardUser;

            array_push($records, [
                'id'      => $record->id,
                'user_id' => $bsUser->id,
                'name'    => $bsUser->name,
                'email'   => $bsUser->email_address,
            ]);
        }

        return $this->renderJson([
            'records' => $records,
        ]);
    }

    public function executeSaveTopManagementVerifier(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $variationOrder = Doctrine_Core::getTable('VariationOrder')->find(intval($request->getParameter('post_contract_claim_top_management_verifier')['objectId']))
        );

        $success = true;
        $errors  = null;

        $form = new PostContractClaimTopManagementVerifierForm(new PostContractClaimTopManagementVerifier());

        if($this->isFormValid($request, $form))
        {
            $bsUser = Doctrine_Core::getTable('sfGuardUser')->find($request->getParameter('post_contract_claim_top_management_verifier')['user_id']);
            $record = PostContractClaimTopManagementVerifier::findRecord($bsUser, $variationOrder);

            if($record)
            {
                $errors['unique_error'] = "{$bsUser->name} is already selected. Please select another user.";
                $success = false;
            }
            else
            {
                $record              = new PostContractClaimTopManagementVerifier();
                $record->object_id   = $variationOrder->id;
                $record->object_type = get_class($variationOrder);
                $record->sequence    = PostContractClaimTopManagementVerifier::getNextFreeSequenceNumber($variationOrder);
                $record->user_id     = $request->getParameter('post_contract_claim_top_management_verifier')['user_id'];
                $record->save();
            }
        }
        else
        {
            $success = false;
            $errors  = $form->getErrors();
        }

        return $this->renderJson([
            'success' => $success,
            'errors'  => $errors,
        ]);
    }

    public function executeUpdateTopManagementVerifier(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $variationOrder = Doctrine_Core::getTable('VariationOrder')->find(intval($request->getParameter('post_contract_claim_top_management_verifier')['objectId'])) and
            $record = Doctrine_Core::getTable('PostContractClaimTopManagementVerifier')->find(intval($request->getParameter('post_contract_claim_top_management_verifier')['id']))
        );

        $success = true;
        $errors  = null;

        $form = new PostContractClaimTopManagementVerifierForm($record);

        if($this->isFormValid($request, $form))
        {
            $bsUser                   = Doctrine_Core::getTable('sfGuardUser')->find($request->getParameter('post_contract_claim_top_management_verifier')['user_id']);
            $passUniquenessValidation = PostContractClaimTopManagementVerifier::uniquenessCheckForExistingRecord($bsUser, $variationOrder, $record);

            if($passUniquenessValidation)
            {
                $record->user_id = $request->getParameter('post_contract_claim_top_management_verifier')['user_id'];
                $record->save();
            }
            else
            {
                $errors['unique_error'] = "{$bsUser->name} is already selected. Please select another user.";
                $success = false;
            }
        }
        else
        {
            $success = false;
            $errors  = $form->getErrors();
        }

        return $this->renderJson([
            'success' => $success,
            'errors'  => $errors,
        ]);
    }

    public function executeDeleteTopManagementVerifier(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $record = Doctrine_Core::getTable('PostContractClaimTopManagementVerifier')->find(intval($request->getParameter('id')))
        );

        $success  = false;
        $errorMsg = null;
        $con      = PostContractClaimTopManagementVerifierTable::getInstance()->getConnection();

        try
        {
            $con->beginTransaction();

            $pdo = PostContractClaimTopManagementVerifierTable::getInstance()->getConnection()->getDbh();

            $query = "UPDATE bs_post_contract_claim_top_management_verifiers SET sequence = (sequence - 1) WHERE id IN (
                          SELECT id
                          FROM bs_post_contract_claim_top_management_verifiers
                          WHERE object_id = {$record->object_id}
                          AND object_type = '{$record->object_type}'
                          AND sequence > {$record->sequence}
                          ORDER BY sequence ASC
                      );";

            $stmt = $pdo->prepare($query);

            $stmt->execute();

            $record->delete();

            $con->commit();
            
            $success = true;
        }
        catch (Exception $e)
        {
            $con->rollback();

            $errorMsg = $e->getMessage();
            $success  = false;
        }

        return $this->renderJson([
            'success' => $success,
            'errors'  => $errorMsg,
            'query' => $query,
        ]);
    }
}
