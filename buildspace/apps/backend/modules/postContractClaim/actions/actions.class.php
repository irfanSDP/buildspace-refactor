<?php

/**
 * postContractClaim actions.
 *
 * @package    buildspace
 * @subpackage postContractClaim
 * @author     1337 developers
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class postContractClaimActions extends BaseActions
{
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

    public function executeGetClaimStatus(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $postContractClaim = Doctrine_Core::getTable('PostContractClaim')->find($request->getParameter('id'))
        );

        if( ! $request->hasParameter('claimRevision') )
        {
            $canAddNewClaim     = $postContractClaim->canAddNewClaim();
            $count              = $postContractClaim->Claims->count();
            $canEditClaimAmount = $postContractClaim->canEditClaimAmount();
        }
        else
        {
            $pdo  = $postContractClaim->getTable()->getConnection()->getDbh();

            $claimCertificates = PostContractClaimRevisionTable::getClaimCertificates($request->getParameter('claimRevision'), '<=');

            $stmt = $pdo->prepare($sql = "SELECT count(claim.id)
                FROM ".PostContractClaimClaimTable::getInstance()->getTableName()." claim
                JOIN ".ClaimCertificateTable::getInstance()->getTableName()." cert ON cert.id = claim.claim_certificate_id
                JOIN ".PostContractClaimRevisionTable::getInstance()->getTableName()." rev ON rev.id = cert.post_contract_claim_revision_id
                WHERE rev.post_contract_id = :postContractId 
                AND post_contract_claim_id = :postContractClaimId
                AND cert.id in (".implode(',', array_column($claimCertificates, 'id')).")
                AND rev.deleted_at IS NULL");

            $stmt->execute(array( 'postContractId' => $postContractClaim->ProjectStructure->PostContract->id, 'postContractClaimId' => $postContractClaim->id));

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

    public function executeGetClaimList(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $postContractClaim = Doctrine_Core::getTable('PostContractClaim')->find($request->getParameter('id'))
        );

        $pdo  = $postContractClaim->getTable()->getConnection()->getDbh();

        $query = DoctrineQuery::create()->select('c.*, c.id, c.revision, c.post_contract_claim_id, c.status, c.is_viewing, c.updated_at')
            ->from('PostContractClaimClaim c')
            ->where('c.post_contract_claim_id = ?', $postContractClaim->id);

        if($request->hasParameter('claimRevision'))
        {
            $claimCertificates = PostContractClaimRevisionTable::getClaimCertificates($request->getParameter('claimRevision'), '<=');

            $query->andWhereIn('claim_certificate_id', array_column($claimCertificates, 'id'));
        }

        $claims = $query->addOrderBy('c.revision ASC')
            ->fetchArray();

        $stmt = $pdo->prepare("SELECT DISTINCT x.id, rev.version
        FROM ".PostContractClaimClaimTable::getInstance()->getTableName()." x
        JOIN ".ClaimCertificateTable::getInstance()->getTableName()." c ON c.id = x.claim_certificate_id
        JOIN ".PostContractClaimRevisionTable::getInstance()->getTableName()." rev ON rev.id = c.post_contract_claim_revision_id
        WHERE rev.post_contract_id = :postContractId AND rev.deleted_at IS NULL ORDER BY rev.version ASC");

        $stmt->execute(array( 'postContractId' => $postContractClaim->ProjectStructure->PostContract->id ));

        $claimCertificateVersions = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

        $form  = new BaseForm();

        foreach ( $claims as $key => $claim )
        {
            $claims[$key]['claim_cert_number'] = array_key_exists($claim['id'], $claimCertificateVersions) ? $claimCertificateVersions[$claim['id']] : "";
            $claims[$key]['can_be_edited']     = $claim['status'] == PostContractClaimClaim::STATUS_CLOSED ? false : true;
            $claims[$key]['can_be_deleted']    = $claim['status'] == PostContractClaimClaim::STATUS_CLOSED ? false : true;
            $claims[$key]['updated_at']        = date('d/m/Y H:i', strtotime($claim['updated_at']));
            $claims[$key]['_csrf_token']       = $form->getCSRFToken();

            unset( $claim );
        }

        array_push($claims, array(
            'id'                 => Constants::GRID_LAST_ROW,
            'revision'           => -1,
            'post_contract_claim_id' => $postContractClaim->id,
            'claim_cert_number'  => "",
            'status'             => -1,
            'is_viewing'         => false,
            'updated_at'         => '-',
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
            $postContractClaim = Doctrine_Core::getTable('PostContractClaim')->find($request->getParameter('id'))
        );

        $con = $postContractClaim->getTable()->getConnection();

        try
        {
            $con->beginTransaction();
            $form = new BaseForm();

            if ( $postContractClaim->canAddNewClaim() )
            {
                $claim                         = new PostContractClaimClaim();
                $claim->post_contract_claim_id = $postContractClaim->id;

                $claim->save();
            }
            else
            {
                throw new Exception('Cannot add new claim because there is still an in progress claim for post contract claim with id:' . $postContractClaim->id);
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
            $claim = Doctrine_Core::getTable('PostContractClaimClaim')->find($request->getParameter('id'))
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
            $rowData['can_be_edited']  = $claim->status == PostContractClaimClaim::STATUS_CLOSED ? false : true;
            $rowData['can_be_deleted'] = $claim->status == PostContractClaimClaim::STATUS_CLOSED ? false : true;

            if($claim->status == PostContractClaimClaim::STATUS_CLOSED)
            {
               $pdo  = $claim->getTable()->getConnection()->getDbh();

               $stmt = $pdo->prepare("SELECT DISTINCT x.id, rev.version
                FROM ".PostContractClaimClaimTable::getInstance()->getTableName()." x
                JOIN ".ClaimCertificateTable::getInstance()->getTableName()." c ON c.id = x.claim_certificate_id
                JOIN ".PostContractClaimRevisionTable::getInstance()->getTableName()." rev ON rev.id = c.post_contract_claim_revision_id
                WHERE rev.post_contract_id = :postContractId AND rev.deleted_at IS NULL ORDER BY rev.version ASC");

                $stmt->execute(array( 'postContractId' => $claim->PostContractClaim->ProjectStructure->PostContract->id ));

                $claimCertificateVersions = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

                $rowData['claim_cert_number'] = isset($claimCertificateVersions[$claim->id]) ? $claimCertificateVersions[$claim->id] : '';
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
            $claim = Doctrine_Core::getTable('PostContractClaimClaim')->find($request->getParameter('id'))
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
            $claim = Doctrine_Core::getTable('PostContractClaimClaim')->find($request->getParameter('id'))
        );

        $con = $claim->getTable()->getConnection();

        try
        {
            $con->beginTransaction();

            $viewingClaim = $claim->PostContractClaim->getViewingClaim();

            $claim->setAsViewingClaim();

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

    public function executeClaimItemUpdate(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $postContractClaimItem = Doctrine_Core::getTable('PostContractClaimItem')->find($request->getParameter('id'))
        );

        $rowData = array();

        $con = $postContractClaimItem->getTable()->getConnection();

        try
        {
            $con->beginTransaction();

            $fieldName  = $request->getParameter('attr_name');
            $fieldValue = $request->getParameter('val');

            $claimItem = $postContractClaimItem->updateClaimItem($fieldName, $fieldValue);

            $con->commit();

            $success = true;

            $errorMsg = null;

            $rowData['current_quantity-value']      = $claimItem->current_quantity;
            $rowData['current_percentage-value']    = $claimItem->current_percentage;
            $rowData['current_amount-value']        = $claimItem->current_amount;
            $rowData['up_to_date_quantity-value']   = $claimItem->up_to_date_quantity;
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
}
