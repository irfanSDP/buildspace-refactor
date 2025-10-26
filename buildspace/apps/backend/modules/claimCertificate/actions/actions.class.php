<?php

/**
 * ClaimCertificate actions.
 *
 * @package    buildspace
 * @subpackage ClaimCertificate
 * @author     1337 developers
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class ClaimCertificateActions extends BaseActions {

    public function executeGetVerifierList(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('pid')) and
            $project->node->isRoot() and
            $claimCertificate = Doctrine_Core::getTable('ClaimCertificate')->find(intval($request->getParameter('id')))
        );

        $success   = true;
        $errorMsg  = null;
        $verifiers = ContractManagementClaimVerifierTable::getVerifierList($project, PostContractClaim::TYPE_CLAIM_CERTIFICATE);

        $topManagementVerifiers = array_column(PostContractClaimTopManagementVerifier::getAllRecords($claimCertificate)->toArray(), 'user_id');

        if((count($topManagementVerifiers) > 0) && empty($verifiers))
        {
            $success = false;
            $errorMsg = 'Default verifiers in eProject are required.';
        }

        return $this->renderJson(array( 'success' => $success, 'errorMsg' => $errorMsg, 'verifiers' => $verifiers, 'topManagementVerifiers' => $topManagementVerifiers ));
    }

    public function executeSubmitForApproval(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('pid')) and
            $project->node->isRoot() and
            $claimCertificate = Doctrine_Core::getTable('ClaimCertificate')->find(intval($request->getParameter('id')))
        );

        $success  = true;
        $errorMsg = null;

        $hasNormalVerifiers        = !empty(EProjectProjectContractManagementModuleTable::getContractManagementVerifiers($project->MainInformation, PostContractClaim::TYPE_CLAIM_CERTIFICATE));
        $hasTopManagementVerifiers = !empty(array_column(PostContractClaimTopManagementVerifier::getAllRecords($claimCertificate)->toArray(), 'user_id'));
        $hasVerifiersAssigned      = ($hasNormalVerifiers || $hasTopManagementVerifiers);

        if(!is_null($project->MainInformation->eproject_origin_id) && $hasVerifiersAssigned)
        {
            $claimRevision = $claimCertificate->PostContractClaimRevision;
            $claimRevision->locked_status = true;
            $claimRevision->save();

            $claimCertificate->status = ClaimCertificate::STATUS_TYPE_PENDING_FOR_APPROVAL;
            $claimCertificate->save();

            ContractManagementClaimVerifierTable::initialiseVerifierList($project, PostContractClaim::TYPE_CLAIM_CERTIFICATE, $claimCertificate->id);

            SubProjectLatestApprovedClaimRevisionTable::insertRecords($claimRevision);

            $claimCertificate->onClaimReview();

            ContractManagementClaimVerifierTable::sendNotifications($project, PostContractClaim::TYPE_CLAIM_CERTIFICATE, $claimCertificate->id);
        }
        else
        {
            $claimCertificate->approve();
        }

        return $this->renderJson([
            'success'  => $success,
            'errorMsg' => $errorMsg
        ]);
    }

    public function executeGetNotes(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $claimCertificate = Doctrine_Core::getTable('ClaimCertificate')->find(intval($request->getParameter('id')))
        );

        $pdo = $claimCertificate->getTable()->getConnection()->getDbh();

        $stmt = $pdo->prepare("SELECT cert.id, cert.status, note.note, note.created_by, note.updated_at, note.created_at, u.name AS creator, rev.version, rev.locked_status, rev.id as claim_revision_id
        FROM ".ClaimCertificateTable::getInstance()->getTableName()." cert
        LEFT JOIN ".ClaimCertificateNoteTable::getInstance()->getTableName()." note ON cert.id = note.claim_certificate_id
        LEFT JOIN ".sfGuardUserProfileTable::getInstance()->getTableName()." u ON u.user_id = note.updated_by
        JOIN ".PostContractClaimRevisionTable::getInstance()->getTableName()." rev ON rev.id = cert.post_contract_claim_revision_id
        WHERE rev.post_contract_id = :postContractId AND rev.version <= :version
        AND rev.deleted_at IS NULL ORDER BY rev.version DESC");

        $stmt->execute([
            'postContractId' => $claimCertificate->PostContractClaimRevision->post_contract_id,
            'version'        => $claimCertificate->PostContractClaimRevision->version
        ]);

        $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach($records as $idx => $record)
        {
            $records[$idx]['status_txt'] =  ClaimCertificate::getStatusText($record['status']);
        }

        return $this->renderJson([
            'identifier' => 'id',
            'items'      => $records
        ]);
    }

    public function executeNoteUpdate(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $claimCertificate = Doctrine_Core::getTable('ClaimCertificate')->find(intval($request->getParameter('id')))
        );

        $errorMsg = null;
        $con      = $claimCertificate->getTable()->getConnection();

        try
        {
            $con->beginTransaction();

            $note = $claimCertificate->Note;

            if(!$note)
            {
                $note = new ClaimCertificateNote();
                $note->claim_certificate_id = $claimCertificate->id;
            }

            $note->note = trim($request->getParameter('note'));

            $note->save();

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
            'success'  => $success,
            'errorMsg' => $errorMsg,
            'note'     => ($note) ? $note->note : ""
        ]);
    }

    public function executeCreditDebitNoteClaimItemsDescriptionCheck(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $project = Doctrine_Core::getTable('ProjectStructure')->find(intval($request->getParameter('pid'))) and
            $claimCertificate = Doctrine_Core::getTable('ClaimCertificate')->find(intval($request->getParameter('id')))
        );

        $success = true;
        $details = [];

        $recordsWithoutDescription = DebitCreditNoteClaimItem::getRecordsWithoutDescription($project, $claimCertificate);

        if(count($recordsWithoutDescription) > 0)
        {
            $success = false;

            foreach($recordsWithoutDescription as $record)
            {
                array_push($details, [
                    'account_group'     => $record['account_group'],
                    'claim_description' => $record['claim_description'],
                    'count'             => $record['count'],
                ]);
            }
        }

        return $this->renderJson([
            'success' => $success,
            'details' => $details,
        ]);
    }

    public function executeGetTopManagementVerifiers(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $project = Doctrine_Core::getTable('ProjectStructure')->find(intval($request->getParameter('pid')))
        );

        $topManagementVerifiers = [];

        foreach(PostContractClaim::getPostContractClaimTopManagementVerifiers($project->MainInformation->getEProjectProject()->id, PostContractClaim::TYPE_CLAIM_CERTIFICATE) as $verifierId)
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
            $claimCertificate = Doctrine_Core::getTable('ClaimCertificate')->find(intval($request->getParameter('claimCertificateId')))
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
            $claimCertificate = Doctrine_Core::getTable('ClaimCertificate')->find(intval($request->getParameter('post_contract_claim_top_management_verifier')['objectId']))
        );

        $success = true;
        $errors  = null;

        $form = new PostContractClaimTopManagementVerifierForm(new PostContractClaimTopManagementVerifier());

        if($this->isFormValid($request, $form))
        {
            $bsUser = Doctrine_Core::getTable('sfGuardUser')->find($request->getParameter('post_contract_claim_top_management_verifier')['user_id']);
            $record = PostContractClaimTopManagementVerifier::findRecord($bsUser, $claimCertificate);

            if($record)
            {
                $errors['unique_error'] = "{$bsUser->name} is already selected. Please select another user.";
                $success = false;
            }
            else
            {
                $record              = new PostContractClaimTopManagementVerifier();
                $record->object_id   = $claimCertificate->id;
                $record->object_type = get_class($claimCertificate);
                $record->sequence    = PostContractClaimTopManagementVerifier::getNextFreeSequenceNumber($claimCertificate);
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
            $claimCertificate = Doctrine_Core::getTable('ClaimCertificate')->find(intval($request->getParameter('post_contract_claim_top_management_verifier')['claimCertificateId'])) and
            $record = Doctrine_Core::getTable('PostContractClaimTopManagementVerifier')->find(intval($request->getParameter('post_contract_claim_top_management_verifier')['id']))
        );

        $success = true;
        $errors  = null;

        $form = new PostContractClaimTopManagementVerifierForm($record);

        if($this->isFormValid($request, $form))
        {
            $bsUser                   = Doctrine_Core::getTable('sfGuardUser')->find($request->getParameter('post_contract_claim_top_management_verifier')['user_id']);
            $passUniquenessValidation = PostContractClaimTopManagementVerifier::uniquenessCheckForExistingRecord($bsUser, $claimCertificate, $record);

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
