<?php

/**
 * postContractMaterialOnSite actions.
 *
 * @package    buildspace
 * @subpackage postContractMaterialOnSite
 * @author     1337 developers
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class postContractClaimMaterialOnSiteActions extends BaseActions
{
    protected $postContractClaimType = PostContractClaim::TYPE_POST_CONTRACT_CLAIM_MATERIAL_ON_SITE;

	public function executeGetMaterialOnSiteList(sfWebRequest $request)
	{
		$this->forward404Unless(
			$request->isXmlHttpRequest() and
			$project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('pid')) and
			$project->node->isRoot()
		);

        $pdo  = $project->getTable()->getConnection()->getDbh();

        $form = new BaseForm();

        $records = Doctrine_Query::create()->select('id, description, claim_certificate_id, status, updated_at')
            ->from('PostContractClaim')
            ->where('project_structure_id = ?', $project->id)
            ->andWhere('type = ?', PostContractClaim::TYPE_POST_CONTRACT_CLAIM_MATERIAL_ON_SITE);

        if($request->hasParameter('oid')) $records->andWhere('id = ?', $request->getParameter('oid'));

        if($request->hasParameter('claimRevision'))
        {
            $claimCertificates = PostContractClaimRevisionTable::getClaimCertificates($request->getParameter('claimRevision'), '<=');

            $records->andWhereIn('claim_certificate_id', array_column($claimCertificates, 'id'));
        }

        $records = $records->addOrderBy('sequence ASC')
            ->fetchArray();

        if( $request->hasParameter('claimRevision') )
        {
            $claimCertificates = PostContractClaimRevisionTable::getClaimCertificates($request->getParameter('claimRevision'), PostContractClaimTable::hasProgressClaim($this->postContractClaimType) ? '<=' : '=');

            $selectedRevisionClause = ( count($certIds = array_column($claimCertificates, 'id')) > 0 ) ? "AND cert.id in (" . implode(',', $certIds) . ")" : "";

            $stmt = $pdo->prepare("SELECT DISTINCT pc.id
            FROM " . PostContractClaimTable::getInstance()->getTableName() . " pc
            JOIN " . PostContractClaimClaimTable::getInstance()->getTableName() . " c ON c.post_contract_claim_id = pc.id
            JOIN " . PostContractClaimClaimItemTable::getInstance()->getTableName() . " i ON i.post_contract_claim_claim_id = c.id 
            LEFT JOIN " . ClaimCertificateTable::getInstance()->getTableName() . " cert ON cert.id = c.claim_certificate_id
            LEFT JOIN " . PostContractClaimRevisionTable::getInstance()->getTableName() . " rev ON rev.id = cert.post_contract_claim_revision_id
            WHERE pc.project_structure_id = " . $project->id . "
            AND pc.type = " . $this->postContractClaimType . "
            {$selectedRevisionClause}
            AND pc.deleted_at IS NULL AND c.deleted_at IS NULL AND i.deleted_at IS NULL");

            $stmt->execute();

            $currentAmounts = array();

            if(count($claimIds = $stmt->fetchAll(PDO::FETCH_COLUMN)) > 0)
            {
                $stmt = $pdo->prepare("SELECT pc.id AS post_contract_claim_id, ROUND(COALESCE(SUM(i.current_amount), 0), 2) AS current_amount
                FROM " . PostContractClaimTable::getInstance()->getTableName() . " pc
                JOIN " . PostContractClaimClaimTable::getInstance()->getTableName() . " c ON c.post_contract_claim_id = pc.id
                JOIN " . PostContractClaimClaimItemTable::getInstance()->getTableName() . " i ON i.post_contract_claim_claim_id = c.id 
                LEFT JOIN " . ClaimCertificateTable::getInstance()->getTableName() . " cert ON cert.id = c.claim_certificate_id
                LEFT JOIN " . PostContractClaimRevisionTable::getInstance()->getTableName() . " rev ON rev.id = cert.post_contract_claim_revision_id
                WHERE pc.id in (" . implode(',', $claimIds) . ")
                AND rev.id = " . $request->getParameter('claimRevision') . "
                AND pc.deleted_at IS NULL AND c.deleted_at IS NULL AND i.deleted_at IS NULL GROUP BY pc.id");

                $stmt->execute();

                $currentAmounts = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
            }

            $upToDateClaims = array();
            foreach($claimIds as $claimId)
            {
                $stmt = $pdo->prepare("SELECT pc.id AS post_contract_claim_id, ROUND(COALESCE(SUM(i.current_amount), 0), 2) AS up_to_date_amount
                    FROM " . PostContractClaimTable::getInstance()->getTableName() . " pc
                    JOIN " . PostContractClaimClaimTable::getInstance()->getTableName() . " c ON c.post_contract_claim_id = pc.id
                    JOIN " . PostContractClaimClaimItemTable::getInstance()->getTableName() . " i ON i.post_contract_claim_claim_id = c.id 
                    LEFT JOIN " . ClaimCertificateTable::getInstance()->getTableName() . " cert ON cert.id = c.claim_certificate_id
                    LEFT JOIN " . PostContractClaimRevisionTable::getInstance()->getTableName() . " rev ON rev.id = cert.post_contract_claim_revision_id
                    WHERE pc.id = {$claimId}
                    {$selectedRevisionClause} 
                    AND pc.deleted_at IS NULL AND c.deleted_at IS NULL AND i.deleted_at IS NULL group by pc.id ");

                $stmt->execute();

                $claim = $stmt->fetch(PDO::FETCH_ASSOC);

                $claim['current_amount'] = $currentAmounts[ $claimId ] ?? 0;
                $upToDateClaims[]        = $claim;
            }
        }
        else
        {
            $stmt = $pdo->prepare("SELECT pc.id AS post_contract_claim_id, ROUND(COALESCE(SUM(i.current_amount), 0), 2) AS current_amount, ROUND(COALESCE(SUM(i.up_to_date_amount), 0), 2) AS up_to_date_amount
            FROM " . PostContractClaimTable::getInstance()->getTableName() . " pc
            JOIN " . PostContractClaimClaimTable::getInstance()->getTableName() . " c ON c.post_contract_claim_id = pc.id
            JOIN " . PostContractClaimClaimItemTable::getInstance()->getTableName() . " i ON i.post_contract_claim_claim_id = c.id 
            LEFT JOIN " . ClaimCertificateTable::getInstance()->getTableName() . " cert ON cert.id = c.claim_certificate_id
            LEFT JOIN " . PostContractClaimRevisionTable::getInstance()->getTableName() . " rev ON rev.id = cert.post_contract_claim_revision_id
            WHERE pc.project_structure_id = " . $project->id . " 
            AND pc.type = " . $this->postContractClaimType . "
            AND c.is_viewing IS TRUE
            AND pc.deleted_at IS NULL AND c.deleted_at IS NULL AND i.deleted_at IS NULL GROUP BY pc.id");

            $stmt->execute();

            $upToDateClaims = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        $stmt = $pdo->prepare("SELECT attachment.object_id AS object_id, COUNT(attachment.object_id) AS number_of_attachments
            FROM ".AttachmentsTable::getInstance()->getTableName()." attachment
            WHERE attachment.object_class= '".get_class(new PostContractClaim())."'
            GROUP BY attachment.object_id");

        $stmt->execute();

        $attachments = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmt = $pdo->prepare("SELECT DISTINCT x.id, rev.version
            FROM ".PostContractClaimTable::getInstance()->getTableName()." x
            JOIN ".ClaimCertificateTable::getInstance()->getTableName()." c ON c.id = x.claim_certificate_id
            JOIN ".PostContractClaimRevisionTable::getInstance()->getTableName()." rev ON rev.id = c.post_contract_claim_revision_id
            WHERE rev.post_contract_id = :postContractId AND rev.deleted_at IS NULL ORDER BY rev.version ASC");

        $stmt->execute(array( 'postContractId' => $project->PostContract->id ));

        $claimCertificateVersions = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

        foreach ( $records as $key => $record )
        {
            $canBeEdited = $record['status'] == PostContractClaim::STATUS_PREPARING;

            $records[$key]['claim_cert_number'] = array_key_exists($record['id'], $claimCertificateVersions) ? $claimCertificateVersions[$record['id']] : "";
            $records[$key]['can_be_edited'] = $canBeEdited;
            $records[$key]['relation_id'] = $project->id;
            $records[$key]['class'] = get_class(new PostContractClaim());
            $records[$key]['attachment'] = "Upload";

            foreach ($attachments as $attachment) 
            {
                if( $attachment['object_id'] == $record['id'])
                {
                    $records[$key]['attachment'] = $attachment['number_of_attachments'];
                }
            }

            $records[$key]['_csrf_token'] = $form->getCSRFToken();

            foreach ( $upToDateClaims as $upToDateClaim )
            {
                if ( $upToDateClaim['post_contract_claim_id'] == $record['id'] )
                {
                    $records[$key]['current_payback']    = $upToDateClaim['current_amount'];
                    $records[$key]['up_to_date_payback'] = $upToDateClaim['up_to_date_amount'];

                    unset( $upToDateClaim );
                }
            }
            
            $postContractClaim = Doctrine_Core::getTable('PostContractClaim')->find($record['id']);

            $finalAmounts = array(); 

            if($postContractClaimItems = $postContractClaim->PostContractClaimItems)
            {
                foreach($postContractClaim->PostContractClaimItems as $postContractClaimItem )
                {
                    if($postContractClaimItem->PostContractClaimMaterialOnSite)
                    {
                        $finalAmounts[$postContractClaimItem->id] = $postContractClaimItem->PostContractClaimMaterialOnSite->final_amount;
                    }
                }

                $sumFinalAmount = 0;

                foreach($finalAmounts as $postContractClaimItemId => $finalAmount)
                {
                    $sumFinalAmount += $finalAmount;
                }

                $records[$key]['amount'] = $sumFinalAmount;
            }
        }

        array_push($records, array(
            'id'                     => Constants::GRID_LAST_ROW,
            'description'            => '',
            'attachment'             => '',
            'can_be_edited'          => true,
            'claim_cert_number'      => '',
            'relation_id'            => $project->id,
            'amount'                 => 0,
            'current_payback'        => 0,
            'up_to_date_payback'     => 0,
            'status'                 => '',
            'updated_at'             => '',
            '_csrf_token'            => $form->getCSRFToken()
        ));

		return $this->renderJson(array(
		'identifier' => 'id',
		'items'      => $records
		));
	}

	public function executeMaterialOnSiteAdd(sfWebRequest $request)
  	{
	  	$request->checkCSRFProtection();

	    $this->forward404Unless($request->isXmlHttpRequest() and $request->isMethod('post'));

	    $postContractClaim = new PostContractClaim();

	    $con = $postContractClaim->getTable()->getConnection();

	    if ( $request->hasParameter('id') and $request->getParameter('id') == Constants::GRID_LAST_ROW )
	    {
	    	$prevPostContractClaim = $request->getParameter('prev_item_id') > 0 ? Doctrine_Core::getTable('PostContractClaim')->find($request->getParameter('prev_item_id')) : null;

	    	$sequence = $prevPostContractClaim ? $prevPostContractClaim->sequence + 1 : 0;

	        $projectStructureId = $request->getParameter('relation_id');

	        if ($request->hasParameter('attr_name') )
	        {
	        	$fieldName  = $request->getParameter('attr_name');
	            $fieldValue = $request->getParameter('val');

	        	$postContractClaim->{'set' . sfInflector::camelize($fieldName)}($fieldValue);
	        }
	    }
	    else
	    {
	        $this->forward404Unless($currentPostContractClaim = Doctrine_Core::getTable('PostContractClaim')->find($request->getParameter('current_selected_item_id')));

	        $sequence           = $currentPostContractClaim->sequence;
	        $projectStructureId = $currentPostContractClaim->project_structure_id;
	    }

	    $items = array();

	    $type = $request->getParameter('type');

	   	try
	    {
	        $con->beginTransaction();

			DoctrineQuery::create()
			->update('PostContractClaim')
			->set('sequence', 'sequence + 1')
			->where('sequence >= ?', $sequence)
	        ->andWhere('type = ?', $type)
			->andWhere('project_structure_id = ?', $projectStructureId)
			->execute();

	        $postContractClaim->project_structure_id = $projectStructureId;
	        $postContractClaim->sequence = $sequence;
	        $postContractClaim->type = $type;
	        $postContractClaim->save();

	        $con->commit();

	        $success = true;

	        $errorMsg = null;

	        $item = array();

	        $form = new BaseForm();

            $pdo  = $postContractClaim->getTable()->getConnection()->getDbh();

            $stmt = $pdo->prepare("SELECT attachment.object_id AS object_id, COUNT(attachment.object_id) AS number_of_attachments
                FROM ".AttachmentsTable::getInstance()->getTableName()." attachment
                WHERE attachment.object_class= '".get_class(new PostContractClaim())."'
                GROUP BY attachment.object_id");

            $stmt->execute();

            $attachments = $stmt->fetchAll(PDO::FETCH_ASSOC);

	        $item['id'] = $postContractClaim->id;
	        $item['description'] = $postContractClaim->description;
            $item['class'] = get_class(new PostContractClaim());
            $item['attachment'] = "Upload";
            foreach ($attachments as $attachment) 
            {
                if( $attachment['object_id'] == $item['id'])
                {
                    $item['attachment'] = $attachment['number_of_attachments'];
                }
            }
	        $item['claim_cert_number'] = '';
	        $item['amount'] = 0 ;
	        $item['relation_id'] = $projectStructureId;
	        $item['can_be_edited'] = true;
	        $item['current_payback'] = 0;
	        $item['up_to_date_payback'] = 0;
	        $item['status'] = $postContractClaim->status;
	        $item['updated_at'] = date('d/m/Y H:i', strtotime($postContractClaim->updated_at));
	        $item['_csrf_token'] = $form->getCSRFToken();

	        array_push($items, $item);

	        if ( $request->hasParameter('id') and $request->getParameter('id') == Constants::GRID_LAST_ROW )
	        {
	            array_push($items, array(
			        'id'                     => Constants::GRID_LAST_ROW,
			        'description'            => '',
                    'attachment'             => '',
			        'claim_cert_number'  	 => '',
			        'amount'				 => 0,
			        'relation_id'            => $projectStructureId,
                	'can_be_edited'          => true,
			        'current_payback'        => 0,
			        'up_to_date_payback'	 => 0,
			        'status'				 => '',
			        'updated_at'             => '',
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

  public function executeMaterialOnSiteUpdate(sfWebRequest $request)
  {
  	$request->checkCSRFProtection();

    $this->forward404Unless($request->isXmlHttpRequest() and
    						$request->isMethod('post'));

	$postContractClaim = Doctrine_Core::getTable('PostContractClaim')->find($request->getParameter('id'));

    $rowData = array();
    $con     = $postContractClaim->getTable()->getConnection();

    try
    {
        $con->beginTransaction();

    	$fieldName  = $request->getParameter('attr_name');
        $fieldValue = $request->getParameter('val');

    	$postContractClaim->{'set' . sfInflector::camelize($fieldName)}($fieldValue);

        $postContractClaim->save($con);

        $con->commit();

        $success = true;

        $errorMsg = null;

        $postContractClaim->refresh();

    	$value = $postContractClaim->$fieldName;

        $form = new BaseForm();

        $project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('relation_id'));

        if( $postContractClaim->status == PostContractClaim::STATUS_PENDING ) ContractManagementClaimVerifierTable::sendNotifications($project, $this->postContractClaimType, $postContractClaim->id);

        $pdo  = $project->getTable()->getConnection()->getDbh();

        $claimCertificateVersions = array();

       	if($postContractClaim->status == PostContractClaim::STATUS_APPROVED)
        {
            $stmt = $pdo->prepare("SELECT DISTINCT x.id, rev.version
            FROM ".PostContractClaimTable::getInstance()->getTableName()." x
            JOIN ".ClaimCertificateTable::getInstance()->getTableName()." c ON c.id = x.claim_certificate_id
            JOIN ".PostContractClaimRevisionTable::getInstance()->getTableName()." rev ON rev.id = c.post_contract_claim_revision_id
            WHERE rev.post_contract_id = :postContractId AND rev.deleted_at IS NULL ORDER BY rev.version ASC");

            $stmt->execute(array( 'postContractId' => $project->PostContract->id ));

            $claimCertificateVersions = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        }
   
        $rowData = array(
            $fieldName          => $value,
            'can_be_edited'     => $postContractClaim->canBeEdited(),
            'claim_cert_number' => isset($claimCertificateVersions[$postContractClaim->id]) ? $claimCertificateVersions[$postContractClaim->id] : '',
            'updated_at'        => date('d/m/Y H:i', strtotime($postContractClaim->updated_at)),
            '_csrf_token'       => $form->getCSRFToken()
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

  public function executeMaterialOnSiteDelete(sfWebRequest $request)
  {
  	$request->checkCSRFProtection();

    $this->forward404Unless(
        $request->isXmlHttpRequest() and
        $request->isMethod('post')
    );

	$postContractClaim = Doctrine_Core::getTable('PostContractClaim')->find($request->getParameter('id'));

    $errorMsg = null;
    try
    {
        $item['id'] = $postContractClaim->id;

        $postContractClaim->delete();

        $success = true;
    }
    catch (Exception $e)
    {
        $errorMsg = $e->getMessage();
        $item     = array();
        $success  = false;
    }

    return $this->renderJson(array( 'success' => $success, 'errorMsg' => $errorMsg, 'items' => array( $item ) ));
  }

  public function executeGetMaterialOnSiteItemList(sfWebRequest $request)
  {
    $this->forward404Unless(
        $request->isXmlHttpRequest() and
        $postContractClaim = Doctrine_Core::getTable('PostContractClaim')->find($request->getParameter('id'))
    );

    $pdo  = $postContractClaim->getTable()->getConnection()->getDbh();
    $form = new BaseForm();

    $stmt = $pdo->prepare("SELECT i.id, i.description, i.type, i.lft, i.level, i.quantity, i.rate, uom.id AS uom_id, uom.symbol AS uom_symbol
     		FROM " . PostContractClaimItemTable::getInstance()->getTableName() . " i
            LEFT JOIN " . UnitOfMeasurementTable::getInstance()->getTableName() . " uom ON i.uom_id = uom.id AND uom.deleted_at IS NULL
            WHERE i.post_contract_claim_id = " . $postContractClaim->id . " AND i.deleted_at IS NULL
            ORDER BY i.sequence, i.lft, i.level");

    $stmt->execute();

    $postContractClaimItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $canBeEdited = $postContractClaim->canBeEdited();
    $canClaim    = $postContractClaim->canClaim();

    $claimItems = $postContractClaim->getClaimItems($request->hasParameter('claimRevision') ? $request->getParameter('claimRevision') : null);

    $stmt = $pdo->prepare("SELECT attachment.object_id AS object_id, COUNT(attachment.object_id) AS number_of_attachments
            FROM ".AttachmentsTable::getInstance()->getTableName()." attachment
            WHERE attachment.object_class= '".get_class(new PostContractClaimItem())."'
            GROUP BY attachment.object_id");

    $stmt->execute();

    $attachments = $stmt->fetchAll(PDO::FETCH_ASSOC);


    $stmt = $pdo->prepare("SELECT reduction.post_contract_claim_item_id AS post_contract_claim_item_id, reduction.reduction_percentage, reduction.reduction_amount, reduction.final_amount
            FROM ".PostContractClaimMaterialOnSiteTable::getInstance()->getTableName()." reduction");

    $stmt->execute();

    $reductionAmounts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($postContractClaimItems as $key => $postContractClaimItem)
    {
        $postContractClaimItems[$key]['relation_id']                 = $postContractClaim->id;
        $postContractClaimItems[$key]['class']                       = get_class(new PostContractClaimItem());
        $postContractClaimItems[$key]['attachment']                  = 'Upload';

        foreach ($attachments as $attachment) 
        {
            if( $attachment['object_id'] == $postContractClaimItem['id'])
            {
                $postContractClaimItems[$key]['attachment'] = $attachment['number_of_attachments'];
            }
        }

        $postContractClaimItems[$key]['can_be_edited']               = $canBeEdited;
        $postContractClaimItems[$key]['can_claim']                   = $canClaim;
        $postContractClaimItems[$key]['_csrf_token']                 = $form->getCSRFToken();

        $postContractClaimItems[$key]['reduction_percentage-value']  = number_format(0, 2, '.', '');
        $postContractClaimItems[$key]['reduction_amount-value']      = number_format(0, 2, '.', '');

        $postContractClaimItems[$key]['previous_quantity-value']     = number_format(0, 2, '.', '');
        $postContractClaimItems[$key]['previous_percentage-value']   = number_format(0, 2, '.', '');
        $postContractClaimItems[$key]['previous_amount-value']       = number_format(0, 2, '.', '');

        $postContractClaimItems[$key]['current_quantity-value']      = number_format(0, 2, '.', '');
        $postContractClaimItems[$key]['current_percentage-value']    = number_format(0, 2, '.', '');
        $postContractClaimItems[$key]['current_amount-value']        = number_format(0, 2, '.', '');

        $postContractClaimItems[$key]['up_to_date_quantity-value']   = number_format(0, 2, '.', '');
        $postContractClaimItems[$key]['up_to_date_percentage-value'] = number_format(0, 2, '.', '');
        $postContractClaimItems[$key]['up_to_date_amount-value']     = number_format(0, 2, '.', '');

        foreach ( $claimItems as $claimItem )
        {
            if ( $claimItem['post_contract_claim_item_id'] == $postContractClaimItem['id'] )
            {
                $postContractClaimItems[$key]['previous_quantity-value']     = $claimItem['previous_quantity'];
                $postContractClaimItems[$key]['previous_percentage-value']   = $claimItem['previous_percentage'];
                $postContractClaimItems[$key]['previous_amount-value']       = $claimItem['previous_amount'];

                $postContractClaimItems[$key]['current_quantity-value']      = $claimItem['current_quantity'];
                $postContractClaimItems[$key]['current_percentage-value']    = $claimItem['current_percentage'];
                $postContractClaimItems[$key]['current_amount-value']        = $claimItem['current_amount'];

                $postContractClaimItems[$key]['up_to_date_quantity-value']   = $claimItem['up_to_date_quantity'];
                $postContractClaimItems[$key]['up_to_date_percentage-value'] = $claimItem['up_to_date_percentage'];
                $postContractClaimItems[$key]['up_to_date_amount-value']     = $claimItem['up_to_date_amount'];

                unset( $claimItem );
            }
        }

        foreach($reductionAmounts as $reductionAmount)
        {
            if($reductionAmount['post_contract_claim_item_id'] == $postContractClaimItem['id'])
            {
                $postContractClaimItems[$key]['amount']                      = $reductionAmount['final_amount'];
                $postContractClaimItems[$key]['reduction_percentage-value']  = $reductionAmount['reduction_percentage'];
                $postContractClaimItems[$key]['reduction_amount-value']      = $reductionAmount['reduction_amount'];
            }
        }
    }

    unset( $claimItems );
    unset( $reductionAmounts );

    array_push($postContractClaimItems, array(
        'id'                             => Constants::GRID_LAST_ROW,
        'description'                    => '',
        'attachment'                     => '',
        'type'                           => (string) PostContractClaimItem::TYPE_WORK_ITEM,
        'relation_id'                    => $postContractClaim->id,
        'uom_id'                         => '-1',
        'uom_symbol'                     => '',
        'quantity'                       => '',
        'rate'                           => '',
        'amount'                         => '',
        'updated_at'                     => '-',
        'level'                          => 0,
        'can_be_edited'                  => $canBeEdited,
        'can_claim'                      => $canClaim,
        'reduction_percentage-value'     => 0,
        'reduction_amount-value'         => 0,
        'previous_quantity-value'        => 0,
        'previous_percentage-value'      => 0,
        'previous_amount-value'          => 0,
        'current_quantity-value'         => 0,
        'current_percentage-value'       => 0,
        'current_amount-value'           => 0,
        'up_to_date_quantity-value'      => 0,
        'up_to_date_percentage-value'    => 0,
        'up_to_date_amount-value'        => 0,
        '_csrf_token'                    => $form->getCSRFToken()
    ));

    return $this->renderJson(array(
        'identifier' => 'id',
        'items'      => $postContractClaimItems
    ));
  }

  public function executeMaterialOnSiteItemAdd(sfWebRequest $request)
  {
    $request->checkCSRFProtection();

    $this->forward404Unless($request->isXmlHttpRequest() and $request->isMethod('post'));

    $items = array();

    $con = Doctrine_Core::getTable('PostContractClaimItem')->getConnection();

    try
    {
        $con->beginTransaction();

        $type = $request->getParameter('type');

        if ( $request->hasParameter('id') and $request->getParameter('id') == Constants::GRID_LAST_ROW )
        {
            $previousItem = $request->getParameter('prev_item_id') > 0 ? Doctrine_Core::getTable('PostContractClaimItem')->find($request->getParameter('prev_item_id')) : null;
            $postContractClaimId = $request->getParameter('relation_id') ;

            $fieldName  = $request->hasParameter('attr_name') ? $request->getParameter('attr_name') : null;
            $fieldValue = $request->hasParameter('val') ? $request->getParameter('val') : null;

            $item = PostContractClaimItemTable::createItemFromLastRow($type, $previousItem, $postContractClaimId, $fieldName, $fieldValue);
        }
        else
        {
            $this->forward404Unless($currentSelectedItem = Doctrine_Core::getTable('PostContractClaimItem')->find($request->getParameter('current_selected_item_id')));

            $postContractClaimId = $currentSelectedItem->post_contract_claim_id;

            $item = PostContractClaimItemTable::createItem($type, $currentSelectedItem, $postContractClaimId);
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
            WHERE attachment.object_class= '".get_class(new PostContractClaimItem())."'
            GROUP BY attachment.object_id");

        $stmt->execute();

        $attachments = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $data['id']                             = $item->id;
        $data['description']                    = $item->description;
        $data['class']                          = get_class(new PostContractClaimItem());
        $data['attachment']                     = "Upload";
        foreach ($attachments as $attachment) 
        {
            if( $attachment['object_id'] == $data['id'])
            {
                $data['attachment'] = $attachment['number_of_attachments'];
            }
        }
        $data['type']                           = (string) $item->type;
        $data['uom_id']                         = $item->uom_id > 0 ? (string) $item->uom_id : '-1';
        $data['uom_symbol']                     = $item->uom_id > 0 ? Doctrine_Core::getTable('UnitOfMeasurement')->find($item->uom_id)->symbol : '';
        $data['quantity']                       = $item->quantity;
        $data['relation_id']                    = $postContractClaimId;
        $data['rate']                           = $item->rate;
        $data['amount']                         = $item->PostContractClaimMaterialOnSite->final_amount;
        $data['level']                          = $item->level;
        $data['can_be_edited']                  = true;
        $data['reduction_percentage-value']     = number_format(0, 2, '.', '');
        $data['reduction_amount-value']         = number_format(0, 2, '.', '');
        $data['previous_quantity-value']        = number_format(0, 2, '.', '');
        $data['previous_percentage-value']      = number_format(0, 2, '.', '');
        $data['previous_amount-value']          = number_format(0, 2, '.', '');
        $data['current_quantity-value']         = number_format(0, 2, '.', '');
        $data['current_percentage-value']       = number_format(0, 2, '.', '');
        $data['current_amount-value']           = number_format(0, 2, '.', '');
        $data['up_to_date_quantity-value']      = number_format(0, 2, '.', '');
        $data['up_to_date_percentage-value']    = number_format(0, 2, '.', '');
        $data['up_to_date_amount-value']        = number_format(0, 2, '.', '');
        $data['_csrf_token']                    = $form->getCSRFToken();

        array_push($items, $data);

        if ( $request->hasParameter('id') and $request->getParameter('id') == Constants::GRID_LAST_ROW )
        {
            array_push($items, array(
                'id'                             => Constants::GRID_LAST_ROW,
                'description'                    => '',
                'attachment'                     => '',
                'type'                           => (string) PostContractClaimItem::TYPE_WORK_ITEM,
                'uom_id'                         => '-1',
                'uom_symbol'                     => '',
                'relation_id'                    => $postContractClaimId,
                'can_be_edited'                  => true,
                'quantity'                       => '',
                'rate'                           => '',
                'amount'                         => '',
                'updated_at'                     => '-',
                'level'                          => 0,
                'reduction_percentage-value'     => 0,
                'reduction_amount-value'         => 0,
                'previous_quantity-value'        => 0,
                'previous_percentage-value'      => 0,
                'previous_amount-value'          => 0,
                'current_quantity-value'         => 0,
                'current_percentage-value'       => 0,
                'current_amount-value'           => 0,
                'up_to_date_quantity-value'      => 0,
                'up_to_date_percentage-value'    => 0,
                'up_to_date_amount-value'        => 0,
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

    public function executeMaterialOnSiteItemUpdate(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $item = Doctrine_Core::getTable('PostContractClaimItem')->find($request->getParameter('id'))
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
            elseif ( $fieldName == 'rate' )
            {
                $fieldValue = is_numeric($fieldValue) ? $fieldValue : 0;
                $item->rate = number_format($fieldValue, 2, '.', '');
            }
            elseif($fieldName == 'reduction_percentage' || $fieldName == 'reduction_amount')
            { 
                $reduction = $item->updateReduction($fieldName, $fieldValue); 
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

            $rowData['type']       = (string) $item->type;
            $rowData['uom_id']     = $item->uom_id > 0 ? (string) $item->uom_id : '-1';
            $rowData['uom_symbol'] = $item->uom_id > 0 ? $item->UnitOfMeasurement->symbol : '';
            $rowData['reduction_percentage-value'] = $item->PostContractClaimMaterialOnSite->reduction_percentage; 
            $rowData['reduction_amount-value'] = $item->PostContractClaimMaterialOnSite->reduction_amount; 
    
            if($fieldName == 'reduction_percentage' || $fieldName == 'reduction_amount')
            { 
                $rowData['reduction_percentage-value'] = $reduction->reduction_percentage; 
                $rowData['reduction_amount-value'] = $reduction->reduction_amount; 
            }
            else{

                $rowData[$fieldName] = $item->{$request->getParameter('attr_name')};
            }

            $rowData['amount'] = $item->PostContractClaimMaterialOnSite->final_amount; 
        }
        catch (Exception $e)
        {
            $con->rollback();
            $errorMsg = $e->getMessage();
            $success  = false;
        }

        return $this->renderJson(array( 'success' => $success, 'errorMsg' => $errorMsg, 'data' => $rowData ));
    }

    public function executeMaterialOnSiteItemDelete(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless($request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $item = Doctrine_Core::getTable('PostContractClaimItem')->find($request->getParameter('id'))
        );

        $errorMsg = null;
        $con      = $item->getTable()->getConnection();

        try
        {
            $con->beginTransaction();

            $items = Doctrine_Query::create()->select('i.id')
                ->from('PostContractClaimItem i')
                ->andWhere('i.root_id = ?', $item->root_id)
                ->andWhere('i.post_contract_claim_id = ?', $item->post_contract_claim_id)
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

    public function executeMaterialOnSiteItemPaste(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless($request->isXmlHttpRequest() and $item = Doctrine_Core::getTable('PostContractClaimItem')->find($request->getParameter('id')));

        $data         = array();
        $children     = array();
        $lastPosition = false;
        $success      = false;
        $errorMsg     = null;

        $targetItem = Doctrine_Core::getTable('PostContractClaimItem')->find(intval($request->getParameter('target_id')));

        if ( !$targetItem )
        {
            $this->forward404Unless($targetItem = Doctrine_Core::getTable('PostContractClaimItem')->find($request->getParameter('prev_item_id')));
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
                ->from('PostContractClaimItem i')
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

    public function executeMaterialOnSiteItemIndent(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless($request->isXmlHttpRequest() and $item = Doctrine_Core::getTable('PostContractClaimItem')->find($request->getParameter('id')));

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
                    ->from('PostContractClaimItem i')
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

    public function executeMaterialOnSiteItemOutdent(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless($request->isXmlHttpRequest() and $item = Doctrine_Core::getTable('PostContractClaimItem')->find($request->getParameter('id')));

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
                    ->from('PostContractClaimItem i')
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

    public function executeGetVerifierList(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('pid')) and
            $project->node->isRoot()
        );

        $success = true;
        $errorMsg = null;
        $items = ContractManagementClaimVerifierTable::getVerifierList($project, PostContractClaim::TYPE_POST_CONTRACT_CLAIM_MATERIAL_ON_SITE);

        return $this->renderJson(array( 'success' => $success, 'errorMsg' => $errorMsg, 'items' => $items ));
    }

    public function executeGetImportedMaterialOnSiteList(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('pid'))
        );

        $pdo = $project->getTable()->getConnection()->getDbh();

        if( $request->hasParameter('claimRevisionId') )
        {
            $claimRevision = Doctrine_Core::getTable('PostContractClaimRevision')->find($request->getParameter('claimRevisionId'));
        }
        else
        {
            $claimRevision = PostContractClaimRevisionTable::getCurrentSelectedProjectRevision($project->PostContract);
        }

        $stmt = $pdo->prepare("SELECT mos.id, mos.description, SUM(COALESCE(mosi.final_amount, 0)) as final_amount
            FROM " . ImportedMaterialOnSiteTable::getInstance()->getTableName() . " mos
            JOIN " . PostContractClaimRevisionTable::getInstance()->getTableName() . " rev ON rev.id = mos.revision_id
            JOIN " . ImportedMaterialOnSiteItemTable::getInstance()->getTableName() . " mosi on mosi.imported_material_on_site_id = mos.id
            WHERE mos.project_structure_id = {$project->id}
            AND rev.version = {$claimRevision['version']}
            GROUP BY mos.id ORDER BY mos.sequence ASC");

        $stmt->execute();

        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach($items as $key => $item)
        {
            $items[ $key ]['attachments'] = count(AttachmentsTable::getAttachments($item['id'], ImportedMaterialOnSiteTable::getInstance()->getClassnameToReturn()));
            $items[ $key ]['class']       = ImportedMaterialOnSiteTable::getInstance()->getClassnameToReturn();
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

    public function executeGetImportedMaterialOnSiteItemList(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('pid')) and
            $materialOnSite = Doctrine_Core::getTable('ImportedMaterialOnSite')->find($request->getParameter('id'))
        );

        $pdo = $project->getTable()->getConnection()->getDbh();

        if( $request->hasParameter('claimRevisionId') )
        {
            $claimRevision = Doctrine_Core::getTable('PostContractClaimRevision')->find($request->getParameter('claimRevisionId'));
        }
        else
        {
            $claimRevision = PostContractClaimRevisionTable::getCurrentSelectedProjectRevision($project->PostContract);
        }

        $stmt = $pdo->prepare("SELECT mosi.id, mosi.description, mosi.type, mosi.uom_symbol, mosi.lft, mosi.level, mosi.quantity, mosi.rate, mosi.final_amount, mosi.reduction_percentage, mosi.reduction_amount
            FROM " . ImportedMaterialOnSiteItemTable::getInstance()->getTableName() . " mosi
            JOIN " . ImportedMaterialOnSiteTable::getInstance()->getTableName() . " mos on mos.id = mosi.imported_material_on_site_id
            JOIN " . PostContractClaimRevisionTable::getInstance()->getTableName() . " rev ON rev.id = mos.revision_id
            WHERE mos.project_structure_id = {$project->id}
            AND mos.id = {$materialOnSite->id}
            AND rev.version = {$claimRevision['version']}
            ORDER BY mosi.sequence, mosi.lft, mosi.level");

        $stmt->execute();

        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach($items as $key => $item)
        {
            $items[ $key ]['type']        = (string)$item['type'];
            $items[ $key ]['attachments'] = count(AttachmentsTable::getAttachments($item['id'], ImportedMaterialOnSiteItemTable::getInstance()->getClassnameToReturn()));
            $items[ $key ]['class']       = ImportedMaterialOnSiteItemTable::getInstance()->getClassnameToReturn();
        }

        array_push($items, array(
            'id'          => Constants::GRID_LAST_ROW,
            'description' => '',
            'type'        => (string)PostContractClaimItem::TYPE_WORK_ITEM,
        ));

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $items
        ));
    }

    public function executeCheckAmountAgainstRetentionSum(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $project           = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('pid')) and
            $postContractClaim = Doctrine_Core::getTable('PostContractClaim')->find($request->getParameter('id'))
        );

        $exceeded     = false;
        $totalAmount  = 0.0;
        $retentionSum = 0.0;

        $currentSelectedClaimRevision = $project->PostContract->getLatestClaimRevision();

        if($currentSelectedClaimRevision)
        {
            $claimCertificate     = $project->PostContract->getLatestClaimRevision()->ClaimCertificate;
            $claimCertificateInfo = ClaimCertificateTable::getClaimCertInfo([$claimCertificate]);
            $retentionSum         = $claimCertificateInfo[$claimCertificate->id]['cumulativeRetentionSum'];
    
            $records = Doctrine_Query::create()->select('id, description, claim_certificate_id, status, updated_at')
                ->from('PostContractClaim')
                ->where('project_structure_id = ?', $project->id)
                ->andWhere('type = ?', PostContractClaim::TYPE_POST_CONTRACT_CLAIM_MATERIAL_ON_SITE)
                ->andWhere('claim_certificate_id = ?', $project->PostContract->getLatestClaimRevision()->ClaimCertificate->id)
                ->addOrderBy('sequence ASC')
                ->fetchArray();
    
            foreach($records as &$record)
            {
                $postContractClaim = Doctrine_Core::getTable('PostContractClaim')->find($record['id']);
        
                $finalAmounts = array(); 
        
                if($postContractClaim->PostContractClaimItems)
                {
                    foreach($postContractClaim->PostContractClaimItems as $postContractClaimItem )
                    {
                        if($postContractClaimItem->PostContractClaimMaterialOnSite)
                        {
                            $finalAmounts[$postContractClaimItem->id] = $postContractClaimItem->PostContractClaimMaterialOnSite->final_amount;
                        }
                    }
        
                    $sumFinalAmount = 0;
        
                    foreach($finalAmounts as $finalAmount)
                    {
                        $sumFinalAmount += $finalAmount;
                    }
        
                    $record['amount'] = $sumFinalAmount;
                    $totalAmount += $sumFinalAmount;
                }
            }

            if($totalAmount > $retentionSum)
            {
                $exceeded = true;
            }
        }

        return $this->renderJson(array(
            'success'      => true,
            'exceeded'     => $exceeded,
            'totalAmount'  => $totalAmount,
            'retentionSum' => $retentionSum,
        ));
    }

    public function executeDuplicateRow(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $project           = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('pid')) and
            $postContractClaim = Doctrine_Core::getTable('PostContractClaim')->find($request->getParameter('id'))
        );

        $success = false;

        $latestFreeSequence = PostContractClaim::getLatestFreeSequence($project->id, $postContractClaim->type);
        
        $newPostContractClaim                       = new PostContractClaim();
        $newPostContractClaim->description          = $postContractClaim->description . ' (Duplicate)';
	    $newPostContractClaim->sequence             = $latestFreeSequence;
        $newPostContractClaim->project_structure_id = $postContractClaim->project_structure_id;
        $newPostContractClaim->type                 = $postContractClaim->type;
        $newPostContractClaim->save();

        foreach($postContractClaim->PostContractClaimItems as $claimItem)
        {
            $newClaimItem           = PostContractClaimItemTable::createItem($newPostContractClaim->type, $claimItem, $newPostContractClaim->id, $claimItem->description);
            $newClaimItem->type     = $claimItem->type;
            $newClaimItem->quantity = $claimItem->quantity;
            $newClaimItem->uom_id   = $claimItem->uom_id;
            $newClaimItem->save();
        }
        
        $success = true;

        return $this->renderJson([
            'success' => $success,
        ]);
    }
}
