<?php

/**
 * debitCreditNote actions.
 *
 * @package    buildspace
 * @subpackage debitCreditNote
 * @author     1337 developers
 * @version    SVN: $Id$
 */
class debitCreditNoteActions extends BaseActions
{
    public function executeGetAccountGroups(sfWebRequest $request) {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $projectStructure = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('projectStructureId'))
        );

        $postContractClaimRevisionId = $request->getParameter('postContractClaimRevisionId');

        $form = new BaseForm();

        $accountGroups = [];

        $accountGroups = DoctrineQuery::create()
                            ->select('ac.id, ac.name, ac.priority, ac.disable, ac.updated_at')
                            ->from('AccountGroup ac')
                            ->addOrderBy('ac.priority ASC')
                            ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
                            ->execute();

        foreach($accountGroups as $key => $accountGroup)
        {
            $totalAmount = 0.0;
            $debitCreditClaimRecords = null;
            $accountGroups[$key]['disable']  = $accountGroup['disable'] ? 'Yes' : 'No';
            $accountGroups[$key]['updated_at']  = date('d/m/Y H:i', strtotime($accountGroup['updated_at']));
            $accountGroups[$key]['_csrf_token'] = $form->getCSRFToken();

            $query = DoctrineQuery::create()
                                        ->select('c.id')
                                        ->from('DebitCreditNoteClaim c')
                                        ->where('c.project_structure_id = ?', $projectStructure->id)
                                        ->andWhere('c.account_group_id = ?', $accountGroup['id']);

            if($postContractClaimRevisionId)
            {
                $claimCertificates = PostContractClaimRevisionTable::getClaimCertificates($postContractClaimRevisionId, '<=');
    
                if( count($certIds = array_column($claimCertificates, 'id')) > 0 ){
                    $query = $query->andWhereIn("c.claim_certificate_id", $certIds);
                }
            }

            $query = $query->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY);

            $debitCreditClaimRecords = $query->execute();
            
            foreach($debitCreditClaimRecords as $record)
            {
                $totalAmount += DebitCreditNoteClaim::calculateDebitCreditClaimAmount($record['id']);
            }

            $accountGroups[$key]['amount'] = $totalAmount;

            unset($accountGroup);
        }                   

        array_push($accountGroups, array(
            'id'          => Constants::GRID_LAST_ROW,
            'name'        => '',
            'priority'    => '',
            'amount'      => '',
            'updated_at'  => '',
            '_csrf_token' => $form->getCSRFToken(),
        ));
        
        return $this->renderJson(array(
            'identifier'    => 'id',
            'label'         => 'name',
            'items'         => $accountGroups,
        ));
    }

    public function executeGetDebitCreditClaims(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $projectStructure = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('projectStructureId')) and
            $accountGroup = Doctrine_Core::getTable('AccountGroup')->find(intval($request->getParameter('accountGroupId')))
        );
        
        $form = new BaseForm();
        $debitCreditClaims = [];
        $className = get_class(new DebitCreditNoteClaim());
        $pdo  = DebitCreditNoteClaimTable::getInstance()->getConnection()->getDbh();
        $tableName = DebitCreditNoteClaimTable::getInstance()->getTableName();
        $clause = " AND obj.project_structure_id = {$projectStructure->id} AND obj.account_group_id = {$accountGroup->id} ";
        $attachments = $this->getAttachmentCount($pdo, $tableName, $className, $clause);
        $itemClaimCertificates = $this->getClaimCertificateRevision($projectStructure, $accountGroup);
        $postContractClaimRevisionId = $request->getParameter('postContractClaimRevisionId');
        $debitCreditClaims = [];

        $query = DoctrineQuery::create()
                                ->select('c.id, c.account_group_id, c.claim_certificate_id, c.description, c.priority, c.updated_at')
                                ->from('DebitCreditNoteClaim c')
                                ->where('c.project_structure_id = ?', $projectStructure->id)
                                ->andWhere('c.account_group_id = ?', $accountGroup->id);

        if($postContractClaimRevisionId)
        {
            $claimCertificates = PostContractClaimRevisionTable::getClaimCertificates($postContractClaimRevisionId, '<=');

            if( count($certIds = array_column($claimCertificates, 'id')) > 0 ){
                $query = $query->andWhereIn("c.claim_certificate_id", $certIds);
            }
        }

        $query = $query->addOrderBy('c.priority ASC')->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY);

        $debitCreditClaims = $query->execute();

        foreach($debitCreditClaims as $key => $claim)
        {
            $debitCreditClaims[$key]['attachment'] = 'Upload';
            $debitCreditClaims[$key]['claim_cert_number'] = null;
            $debitCreditClaims[$key]['amount'] = DebitCreditNoteClaim::calculateDebitCreditClaimAmount($claim['id']);
            $debitCreditClaims[$key]['updated_at'] = date('d/m/Y H:i', strtotime($claim['updated_at']));
            $debitCreditClaims[$key]['class'] = $className;
            $debitCreditClaims[$key]['_csrf_token'] = $form->getCSRFToken();

            foreach($attachments as $idx => $attachment)
            {
                if($attachment['object_id'] == $claim['id'])
                {
                    $debitCreditClaims[$key]['attachment'] = $attachment['number_of_attachments'];

                    unset($attachments[$idx]);
                    break;
                }
            }

            foreach($itemClaimCertificates as $claimCert)
            {
                if($claimCert['id'] == $claim['id'])
                {
                    $debitCreditClaims[$key]['claim_cert_number'] = $claimCert['version'];
                    break;
                }
            }

            $debitCreditClaims[$key]['locked'] = false; 
            if($debitCreditClaims[$key]['claim_certificate_id'] )
            {
                $debitCreditClaims[$key]['locked'] = $this->isEditingLocked($debitCreditClaims[$key]['claim_certificate_id'], $debitCreditClaims[$key]['claim_cert_number']);
            }

            unset($claim);
        }

        array_push($debitCreditClaims, array(
            'id'                    => Constants::GRID_LAST_ROW,
            'account_group_id'      => $accountGroup->id,
            'description'           => '',
            'attachment'            => '',
            'claim_cert_number'     => '',
            'amount'                => '',
            'priority'              => '',
            'updated_at'            => '',
            'class'                 => '',
            'locked'                => false,
            '_csrf_token'           => $form->getCSRFToken(),
        ));

        return $this->renderJson(array(
            'identifier'    => 'id',
            'label'         => 'name',
            'items'         => $debitCreditClaims,
        ));
    }

    public function executeDebitCreditClaimAdd(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $projectStructure = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('projectStructureId')) and
            $accountGroup = Doctrine_Core::getTable('AccountGroup')->find(intval($request->getParameter('accountGroupId')))
        );

        $success    = true;
        $errorMsg   = null;
        $form       = new BaseForm();
        $items    = array();
        $className = get_class(new DebitCreditNoteClaim());
        $con      = DebitCreditNoteClaimTable::getInstance()->getConnection();

        try
        {
            $con->beginTransaction();

            $debitCreditNoteClaim = new DebitCreditNoteClaim();
            $previousDebitCreditNoteClaim = $request->getParameter('prev_item_id') > 0 ? Doctrine_Core::getTable('DebitCreditNoteClaim')->find(intval($request->getParameter('prev_item_id'))) : null;
            $fieldName  = $request->hasParameter('attr_name') ? $request->getParameter('attr_name') : null;
            $fieldValue = $request->hasParameter('val') ? $request->getParameter('val') : null;
            $priority = 0;

            if ( $request->hasParameter('id') and $request->getParameter('id') == Constants::GRID_LAST_ROW )
            {
                if($previousDebitCreditNoteClaim)
                {
                    $priority = $previousDebitCreditNoteClaim->priority + 1;
                }

                if($fieldName)
                {
                    $debitCreditNoteClaim->{$fieldName} = $fieldValue;
                }

                $debitCreditNoteClaim->project_structure_id = $projectStructure->id;
                $debitCreditNoteClaim->account_group_id = $accountGroup->id;
                $debitCreditNoteClaim->priority = $priority;
                $debitCreditNoteClaim->save();
            }
            else
            {
                $this->forward404Unless($nextDebitCreditNoteClaim = Doctrine_Core::getTable('DebitCreditNoteClaim')->find(intval($request->getParameter('before_id'))));
                $priority = $nextDebitCreditNoteClaim->priority;

                $debitCreditNoteClaim->project_structure_id = $projectStructure->id;
                $debitCreditNoteClaim->account_group_id = $accountGroup->id;
                $debitCreditNoteClaim->priority = $priority;
                $debitCreditNoteClaim->save($con);

                $debitCreditNoteClaim->updatePriority(true);
            }

            $con->commit();
            $debitCreditNoteClaim->refresh();
        }
        catch(Exception $e) {
            $con->rollback();
            $errorMsg = $e->getMessage();
            $success  = false;
        }

        array_push($items, array(
            'id'                    => $debitCreditNoteClaim->id,
            'account_group_id'      => $accountGroup->id,
            'description'           => $debitCreditNoteClaim->description,
            'attachment'            => 'Upload',
            'claim_cert_number'     => '',
            'amount'                => '',
            'priority'              => $debitCreditNoteClaim->priority,
            'updated_at'            => date('d/m/Y H:i', strtotime($debitCreditNoteClaim->updated_at)),
            'class'                 => $className,
            'locked'                => false,
            '_csrf_token'           => $form->getCSRFToken(),
        ));

        if ( $request->hasParameter('id') and $request->getParameter('id') == Constants::GRID_LAST_ROW )
        {
            array_push($items, array(
                'id'                    => Constants::GRID_LAST_ROW,
                'account_group_id'      => '',
                'description'           => '',
                'attachment'            => '',
                'claim_cert_number'     => '',
                'amount'                => '',
                'priority'              => '',
                'updated_at'            => '',
                'class'                 => '',
                'locked'                => false,
                '_csrf_token'           => $form->getCSRFToken(),
            ));
        }

        return $this->renderJson(array(
            'success'   => $success,
            'items'     => $items,
            'errorMsg'  => $errorMsg,
        ));
    }

    public function executeDebitCreditClaimUpdate(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $projectStructure = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('projectStructureId')) and
            $accountGroup = Doctrine_Core::getTable('AccountGroup')->find(intval($request->getParameter('accountGroupId'))) and
            $debitCreditNoteClaim = Doctrine_Core::getTable('DebitCreditNoteClaim')->find(intval($request->getParameter('id')))
        );

        $success = true;
        $errorMsg = null;
        $form = new BaseForm();
        $className = get_class(new DebitCreditNoteClaim());
        $con = $debitCreditNoteClaim->getTable()->getConnection();
        $fieldName  = $request->hasParameter('attr_name') ? $request->getParameter('attr_name') : null;
        $fieldValue = $request->hasParameter('val') ? $request->getParameter('val') : null;

        try
        {
            $con->beginTransaction();

            if ( $fieldName )
            {
                $debitCreditNoteClaim->{$fieldName} = $fieldValue;
            }

            $debitCreditNoteClaim->save();
        }
        catch (Exception $e)
        {
            $con->rollback();
            $errorMsg = $e->getMessage();
            $success  = false;
        }

        $con->commit();
        $debitCreditNoteClaim->refresh();

        return $this->renderJson(array(
            'success'  => $success,
            'errorMsg' => $errorMsg,
            'data'     => array(
                'id'                    => $debitCreditNoteClaim->id,
                'account_group_id'      => $accountGroup->id,
                'description'           => $debitCreditNoteClaim->description,
                'attachment'            => 'Upload',
                'claim_cert_number'  => '',
                'amount'                => '',
                'priority'              => $debitCreditNoteClaim->priority,
                'updated_at'            => date('d/m/Y H:i', strtotime($debitCreditNoteClaim->updated_at)),
                'class'                 => $className,
                'locked'                => false,
                '_csrf_token'           => $form->getCSRFToken(),
            )
        ));
    }

    public function executeDebitCreditClaimDelete(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $projectStructure = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('projectStructureId')) and
            $accountGroup = Doctrine_Core::getTable('AccountGroup')->find(intval($request->getParameter('accountGroupId'))) and
            $debitCreditNoteClaim = Doctrine_Core::getTable('DebitCreditNoteClaim')->find(intval($request->getParameter('id')))
        );

        $items    = array();
        $errorMsg = null;
        $con = $debitCreditNoteClaim->getTable()->getConnection();

        try
        {
            $con->beginTransaction();

            $items = DoctrineQuery::create()->select('c.id')
                        ->from('DebitCreditNoteClaim c')
                        ->where('c.id = ?', $debitCreditNoteClaim->id)
                        ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
                        ->execute();

            $debitCreditNoteClaim->delete($con);

            $debitCreditNoteClaim->updatePriority(false);
            
            $con->commit();
            $success = true;
        }
        catch (Exception $e)
        {
            $con->rollback();
            $errorMsg = $e->getMessage();
            $success  = false;
        }

        return $this->renderJson(array(
            'success'   => $success,
            'errorMsg'  => $errorMsg,
            'items'     => $items,
        ));
    }

    public function executeGetDebitCreditClaimItems(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $debitCreditNoteClaim = Doctrine_Core::getTable('DebitCreditNoteClaim')->find(intval($request->getParameter('debitCreditNoteClaimId')))
        );

        $form = new BaseForm();
        $debitCreditClaimItems = [];
        $className = get_class(new DebitCreditNoteClaimItem());
        $pdo  = DebitCreditNoteClaimItemTable::getInstance()->getConnection()->getDbh();
        $tableName = DebitCreditNoteClaimItemTable::getInstance()->getTableName();
        $clause = " AND obj.debit_credit_note_claim_id = {$debitCreditNoteClaim->id} ";
        $attachments = $this->getAttachmentCount($pdo, $tableName, $className, $clause);

        $debitCreditClaimItems = DoctrineQuery::create()
                                    ->select('ci.id, ci.debit_credit_note_claim_id, ci.account_code_id, ci.invoice_number, ci.invoice_date, ci.due_date, ci.uom_id, ci.quantity, ci.rate, ci.remarks, ci.priority')
                                    ->from('DebitCreditNoteClaimItem ci')
                                    ->where('ci.debit_credit_note_claim_id = ?', $debitCreditNoteClaim->id)
                                    ->addOrderBy('ci.priority ASC')
                                    ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
                                    ->execute();

        foreach($debitCreditClaimItems as $key => $claimItem)
        {
            $debitCreditClaimItems[$key]['attachment'] = 'Upload';
            $debitCreditClaimItems[$key]['amount'] = $claimItem['rate'] * $claimItem['quantity'];
            $debitCreditClaimItems[$key]['class'] = $className;
            $debitCreditClaimItems[$key]['_csrf_token'] = $form->getCSRFToken();

            foreach($attachments as $idx => $attachment)
            {
                if($attachment['object_id'] == $claimItem['id'])
                {
                    $debitCreditClaimItems[$key]['attachment'] = $attachment['number_of_attachments'];

                    unset($attachments[$idx]);
                    break;
                }
            }

            unset($claimItem);
        }

        array_push($debitCreditClaimItems, array(
            'id'                          => Constants::GRID_LAST_ROW,
            'debit_credit_note_claim_id'  => '',
            'account_code_id'             => '',
            'invoice_number'              => '',
            'invoice_date'                => '',
            'due_date'                    => '',
            'attachment'                  => '',
            'uom_id'                      => '',
            'quantity'                    => '',
            'rate'                        => '',
            'amount'                      => '',
            'remarks'                     => '',
            'priority'                    => '',
            'class'                       => '',
            '_csrf_token'                 => $form->getCSRFToken(),
        ));

        return $this->renderJson(array(
            'identifier'    => 'id',
            'label'         => 'name',
            'items'         => $debitCreditClaimItems,
        ));
    }

    public function executeDebitCreditNoteClaimItemAdd(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $accountGroup = Doctrine_Core::getTable('AccountGroup')->find(intval($request->getParameter('accountGroupId'))) and
            $debitCreditNoteClaim = Doctrine_Core::getTable('DebitCreditNoteClaim')->find(intval($request->getParameter('debitCreditNoteClaimId')))
        );

        $success    = true;
        $errorMsg   = null;
        $form       = new BaseForm();
        $items    = array();
        $className = get_class(new DebitCreditNoteClaimItem());
        $con      = DebitCreditNoteClaimItemTable::getInstance()->getConnection();

        try
        {
            $con->beginTransaction();

            $debitCreditNoteClaimItem = new DebitCreditNoteClaimItem();
            $previousDebitCreditNoteClaimItem = $request->getParameter('prev_item_id') > 0 ? Doctrine_Core::getTable('DebitCreditNoteClaimItem')->find(intval($request->getParameter('prev_item_id'))) : null;
            $fieldName  = $request->hasParameter('attr_name') ? $request->getParameter('attr_name') : null;
            $fieldValue = $request->hasParameter('val') ? $request->getParameter('val') : null;
            $priority = 0;

            if ( $request->hasParameter('id') and $request->getParameter('id') == Constants::GRID_LAST_ROW )
            {
                if($previousDebitCreditNoteClaimItem)
                {
                    $priority = $previousDebitCreditNoteClaimItem->priority + 1;
                }
               
                if($fieldName)
                {
                    if(($fieldName === 'invoice_date') || ($fieldName === 'due_date'))
                    {
                        $fieldValue = Utilities::convertJavascriptDateToPhp($fieldValue, 'Y-m-d');
                    }

                    $debitCreditNoteClaimItem->{$fieldName} = $fieldValue;
                }

                $debitCreditNoteClaimItem->debit_credit_note_claim_id = $debitCreditNoteClaim->id;
                $debitCreditNoteClaimItem->priority = $priority;
                $debitCreditNoteClaimItem->save();
            }
            else
            {
                $this->forward404Unless($nextDebitCreditNoteClaimItem = Doctrine_Core::getTable('DebitCreditNoteClaimItem')->find(intval($request->getParameter('before_id'))));
                $priority = $nextDebitCreditNoteClaimItem->priority;

                $debitCreditNoteClaimItem->debit_credit_note_claim_id = $debitCreditNoteClaim->id;
                $debitCreditNoteClaimItem->priority = $priority;
                $debitCreditNoteClaimItem->save($con);

                $debitCreditNoteClaimItem->updatePriority(true);
            }
            
            $con->commit();
            $debitCreditNoteClaimItem->refresh();

            array_push($items, array(
                'id'                          => $debitCreditNoteClaimItem->id,
                'debit_credit_note_claim_id'  => $debitCreditNoteClaim->id,
                'account_code_id'             => $debitCreditNoteClaimItem->account_code_id,
                'invoice_number'              => $debitCreditNoteClaimItem->invoice_number,
                'invoice_date'                => $debitCreditNoteClaimItem->invoice_date,
                'due_date'                    => $debitCreditNoteClaimItem->due_date,
                'attachment'                  => 'Upload',
                'uom_id'                      => $debitCreditNoteClaimItem->uom_id,
                'quantity'                    => $debitCreditNoteClaimItem->quantity,
                'rate'                        => $debitCreditNoteClaimItem->rate,
                'amount'                      => $debitCreditNoteClaimItem->rate * $debitCreditNoteClaimItem->quantity,
                'remarks'                     => $debitCreditNoteClaimItem->remarks,
                'priority'                    => $debitCreditNoteClaimItem->priority,
                'class'                       => $className,
                '_csrf_token'                 => $form->getCSRFToken(),
            ));

            if ( $request->hasParameter('id') and $request->getParameter('id') == Constants::GRID_LAST_ROW )
            {
                array_push($items, array(
                    'id'                          => Constants::GRID_LAST_ROW,
                    'debit_credit_note_claim_id'  => '',
                    'account_code_id'             => '',
                    'invoice_number'              => '',
                    'invoice_date'                => '',
                    'due_date'                    => '',
                    'attachment'                  => '',
                    'uom_id'                      => '',
                    'quantity'                    => '',
                    'rate'                        => '',
                    'amount'                      => '',
                    'remarks'                     => '',
                    'priority'                    => '',
                    'class'                       => '',
                    '_csrf_token'                 => $form->getCSRFToken(),
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
            'success'   => $success,
            'items'     => $items,
            'errorMsg'  => $errorMsg,
        ));
    }

    public function executeDebitCreditNoteClaimItemUpdate(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $debitCreditNoteClaim = Doctrine_Core::getTable('DebitCreditNoteClaim')->find(intval($request->getParameter('debitCreditNoteClaimId'))) and
            $debitCreditNoteClaimItem = Doctrine_Core::getTable('DebitCreditNoteClaimItem')->find(intval($request->getParameter('id')))
        );

        $success = true;
        $errorMsg = null;
        $form = new BaseForm();
        $className = get_class(new DebitCreditNoteClaimItem());
        $con = $debitCreditNoteClaim->getTable()->getConnection();
        $fieldName  = $request->hasParameter('attr_name') ? $request->getParameter('attr_name') : null;
        $fieldValue = $request->hasParameter('val') ? $request->getParameter('val') : null;

        try
        {
            $con->beginTransaction();

            if ( $fieldName )
            {
                if(($fieldName === 'invoice_date') || ($fieldName === 'due_date'))
                {
                    $fieldValue = Utilities::convertJavascriptDateToPhp($fieldValue, 'Y-m-d');
                }

                $debitCreditNoteClaimItem->{$fieldName} = $fieldValue;
            }

            $debitCreditNoteClaimItem->save();
        }
        catch (Exception $e)
        {
            $con->rollback();
            $errorMsg = $e->getMessage();
            $success  = false;
        }

        $con->commit();
        $debitCreditNoteClaimItem->refresh();

        return $this->renderJson(array(
            'success'  => $success,
            'errorMsg' => $errorMsg,
            'data'     => array(
                'id'                          => $debitCreditNoteClaimItem->id,
                'debit_credit_note_claim_id'  => $debitCreditNoteClaim->id,
                'account_code_id'             => $debitCreditNoteClaimItem->account_code_id,
                'invoice_number'              => $debitCreditNoteClaimItem->invoice_number,
                'invoice_date'                => $debitCreditNoteClaimItem->invoice_date,
                'due_date'                    => $debitCreditNoteClaimItem->due_date,
                'attachment'                  => 'Upload',
                'uom_id'                      => $debitCreditNoteClaimItem->uom_id,
                'quantity'                    => $debitCreditNoteClaimItem->quantity,
                'rate'                        => $debitCreditNoteClaimItem->rate,
                'amount'                      => $debitCreditNoteClaimItem->rate * $debitCreditNoteClaimItem->quantity,
                'remarks'                     => $debitCreditNoteClaimItem->remarks,
                'priority'                    => $debitCreditNoteClaimItem->priority,
                'class'                       => $className,
                '_csrf_token'                 => $form->getCSRFToken(),
            )
        ));
    }

    public function executeDebitCreditClaimItemDelete(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $accountGroup = Doctrine_Core::getTable('AccountGroup')->find(intval($request->getParameter('accountGroupId'))) and
            $debitCreditNoteClaim = Doctrine_Core::getTable('DebitCreditNoteClaim')->find(intval($request->getParameter('debitCreditNoteClaimId'))) and
            $debitCreditNoteClaimItem = Doctrine_Core::getTable('DebitCreditNoteClaimItem')->find(intval($request->getParameter('id')))
        );

        $items    = array();
        $errorMsg = null;
        $con = $debitCreditNoteClaimItem->getTable()->getConnection();

        try
        {
            $con->beginTransaction();

            $items = DoctrineQuery::create()->select('ci.id')
                        ->from('DebitCreditNoteClaimItem ci')
                        ->where('ci.id = ?', $debitCreditNoteClaimItem->id)
                        ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
                        ->execute();

            $debitCreditNoteClaimItem->delete($con);

            $debitCreditNoteClaimItem->updatePriority(false);
            
            $con->commit();
            $success = true;
        }
        catch (Exception $e)
        {
            $con->rollback();
            $errorMsg = $e->getMessage();
            $success  = false;
        }

        return $this->renderJson(array(
            'success'   => $success,
            'errorMsg'  => $errorMsg,
            'items'     => $items,
        ));
    }

    public function executeGetAccountCodes(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $accountGroup = Doctrine_Core::getTable('AccountGroup')->find(intval($request->getParameter('accountGroupId')))
        );

        $options = array();
        $values  = array();

        array_push($values, '-1');
        array_push($options, '---');

        $accountCodes = DoctrineQuery::create()
            ->select('ac.id, ac.description')
            ->from('AccountCode ac')
            ->where('ac.account_group_id = ?', $accountGroup->id)
            ->addOrderBy('ac.priority ASC')
            ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
            ->execute();

        foreach($accountCodes as $accountCode)
        {
            array_push($values, $accountCode['id'] . '');
            array_push($options, $accountCode['description'] . '');
            unset($accountCode);
        }

        unset($accountCodes);

        return $this->renderJson(array(
            'values'  => $values,
            'options' => $options,
        ));
    }

    private function getAttachmentCount($pdo, $tableName, $className, $clause)
    {
        $stmt = $pdo->prepare("SELECT attachment.object_id AS object_id, COUNT(attachment.object_id) AS number_of_attachments
            FROM ".AttachmentsTable::getInstance()->getTableName()." attachment
            JOIN {$tableName} obj ON attachment.object_id = obj.id
            WHERE attachment.object_class= '{$className}'
            {$clause}
            AND obj.deleted_at IS NULL
            GROUP BY attachment.object_id");

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function executeGetOpenClaimCertificate(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $projectStructure = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('projectStructureId')) and
            $accountGroup = Doctrine_Core::getTable('AccountGroup')->find(intval($request->getParameter('accountGroupId')))
        );

        $claimRevision = $projectStructure->PostContract->getOpenClaimRevision();

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

    private function getClaimCertificateRevision($projectStructure, $accountGroup)
    {
        $pdo  = DebitCreditNoteClaimTable::getInstance()->getConnection()->getDbh();

        $stmt = $pdo->prepare("SELECT ci.id, pccr.version, cc.status 
                    FROM ".DebitCreditNoteClaimTable::getInstance()->getTableName()." ci
                    JOIN ".ClaimCertificateTable::getInstance()->getTableName()." cc ON ci.claim_certificate_id = cc.id
                    JOIN ".PostContractClaimRevisionTable::getInstance()->getTableName()." pccr ON cc.post_contract_claim_revision_id = pccr.id
                    JOIN ".PostContractTable::getInstance()->getTableName()." pc ON pccr.post_contract_id = pc.id AND pc.project_structure_id = ci.project_structure_id
                    WHERE ci.project_structure_id = {$projectStructure->id} AND 
                    ci.account_group_id = {$accountGroup->id} AND
                    ci.deleted_at IS NULL
                    ORDER BY ci.priority ASC"
        );
        
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function executeClaimCertificateAttach(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $claimCertificate = Doctrine_Core::getTable('ClaimCertificate')->find($request->getParameter('cid')) and
            $claimCertificate->status == ClaimCertificate::STATUS_TYPE_IN_PROGRESS and
            $debitCreditNoteClaim = Doctrine_Core::getTable('DebitCreditNoteClaim')->find(intval($request->getParameter('debitCreditNoteClaimId')))
        );

        $errorMsg = null;
        $con      = $claimCertificate->getTable()->getConnection();

        try
        {
            $con->beginTransaction();

            $debitCreditNoteClaim->claim_certificate_id = $claimCertificate->id;
            $debitCreditNoteClaim->save($con);

            $con->commit();

            $success = true;
            $claimCertificateNumber = $claimCertificate->PostContractClaimRevision->version;
            $locked = $this->isEditingLocked($debitCreditNoteClaim->claim_certificate_id, $claimCertificateNumber);
            
            $item = [
                'id'                => $debitCreditNoteClaim->id,
                'claim_cert_number' => $claimCertificateNumber,
                'locked'            => $locked,
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

    private function isEditingLocked($claimCertificateId, $claimCertificateNumber)
    {
        $isLocked = true;
        $pdo  = DebitCreditNoteClaimTable::getInstance()->getConnection()->getDbh();


        $stmt = $pdo->prepare("SELECT cc.id, pccr.version, pccr.current_selected_revision, pccr.locked_status
                    FROM ".ClaimCertificateTable::getInstance()->getTableName()." cc
                    JOIN ".PostContractClaimRevisionTable::getInstance()->getTableName()." pccr ON cc.post_contract_claim_revision_id = pccr.id
                    WHERE cc.id = {$claimCertificateId}");

        $stmt->execute();
        
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $isCurrentlySelectedRevision = $result[0]['current_selected_revision'];
        $claimCertificateNumberMatches = ($result[0]['version'] == $claimCertificateNumber);
        $isNotLocked = !$result[0]['locked_status'];

        if($isCurrentlySelectedRevision && $claimCertificateNumberMatches && $isNotLocked)
        {
            $isLocked = false;
        }

        return $isLocked;
    }
}
