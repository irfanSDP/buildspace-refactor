<?php namespace PCK\RequestForVariation;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use PCK\Projects\Project;
use PCK\Users\User;
use PCK\Notifications\EmailNotifier;
use PCK\Notifications\SystemNotifier;
use PCK\Verifier\Verifier;
use PCK\Buildspace\VariationOrder;
use PCK\Buildspace\VariationOrderItem;
use PCK\RequestForVariation\RequestForVariationUserPermissionGroup;

class RequestForVariationRepository {

    protected $emailNotifier;
    protected $systemNotifier;

    public function __construct(EmailNotifier $emailNotifier, SystemNotifier $systemNotifier)
    {
        $this->emailNotifier  = $emailNotifier;
        $this->systemNotifier = $systemNotifier;
    }

    public function listRequestForVariationByGroup(Project $project, User $user)
    {
        $userPermissionGroupIds = array_column($user->getRequestForVariationUserPermissionGroups($project), 'id');

        $query = \DB::table('request_for_variation_user_permission_groups AS pg')
            ->join('request_for_variation_user_permissions AS p', 'p.request_for_variation_user_permission_group_id', '=', 'pg.id')
            ->join('request_for_variations AS r', 'r.request_for_variation_user_permission_group_id', '=', 'pg.id')
            ->join('request_for_variation_categories AS c', 'r.request_for_variation_category_id', '=', 'c.id')
            ->join('users AS u', 'r.initiated_by', '=', 'u.id')
            ->select('r.id', 'r.rfv_number', 'r.ai_number', 'r.description', 'r.nett_omission_addition', 'r.status', 'c.name AS rfv_category', 'u.name', 'pg.id AS permission_group_id', 'pg.name AS permission_group_name')
            ->where('pg.project_id', $project->id)
            ->whereIn('r.request_for_variation_user_permission_group_id', $userPermissionGroupIds)
            ->distinct('r.id')
            ->orderBy('r.rfv_number', 'DESC');

        $queryResults = $query->get();

        $count = 0;
        $data = [];

        foreach($queryResults as $result)
        {
            $nettOmissionAddition = $result->nett_omission_addition ? $result->nett_omission_addition : 0.0;

            array_push($data, [
                'indexNo'               => ++$count,
                'id'                    => $result->id,
                'permission_group_id'   => $result->permission_group_id,
                'permission_group_name' => $result->permission_group_name,
                'rfvNumber'             => $result->rfv_number,
                'aiNumber'              => $result->ai_number,
                'description'           => $result->description,
                'rfvCategory'           => $result->rfv_category,
                'nettOmissionAddition'  => $project->getModifiedCurrencyCodeAttribute($project->modified_currency_code) . ' ' . number_format($nettOmissionAddition, 2, '.', ','),
                'createdBy'             => $result->name,
                'status'                => $result->status,
                'statusText'            => ($requestForVariation = RequestForVariation::find($result->id)) ? $requestForVariation->getStatusText() : trans('requestForVariation.deleted'),
                'route_show'            => route('requestForVariation.form.show', [$project->id, $result->id]),
                'is_deleted'            => $requestForVariation ? false : true,
                'csrf_token'            => csrf_token(),
            ]);
        }

        return $data;
    }

    public function contractAndContingencySumSave(Project $project, $inputs)
    {
        $rfvCnCSum                                        = new RequestForVariationContractAndContingencySum();
        $rfvCnCSum->project_id                            = $project->id;
        $rfvCnCSum->original_contract_sum                 = $inputs['original_contract_sum'];
        $rfvCnCSum->contingency_sum                       = $inputs['contingency_sum'];
        $rfvCnCSum->user_id                               = \Confide::user()->id;
        $rfvCnCSum->contract_sum_includes_contingency_sum = isset($inputs['contract_sum_includes_contingency']);
        $rfvCnCSum->save();
    }

    public function executeAction(RequestForVariation $requestForVariation, $inputs)
    {
        switch($requestForVariation->status)
        {
            case RequestForVariation::STATUS_PENDING_COST_ESTIMATE:
                $requestForVariation = RequestForVariation::findOrFail($inputs['requestForVariationId']);
                $this->updateRfvOmissionAdditionAmount($requestForVariation, $inputs);
                break;
            case RequestForVariation::STATUS_PENDING_VERIFICATION:
                $requestForVariation = RequestForVariation::findOrFail($inputs['requestForVariationId']);
                $this->approveRejectOmissionAdditionAmount($requestForVariation, $inputs);
                break;
            case RequestForVariation::STATUS_VERIFIED:
                $requestForVariation = RequestForVariation::findOrFail($inputs['requestForVariationId']);
                $this->submitForVerification($requestForVariation, $inputs);
                break;
            case RequestForVariation::STATUS_PENDING_APPROVAL:
                $requestForVariation = RequestForVariation::findOrFail($inputs['requestForVariationId']);
                $this->approveRejectVerification($requestForVariation, $inputs);
                break;
            default:
                // nothing here
        }
    }

    public function createNewRfv(Project $project, $inputs)
    {
        $userPermissionGroup = RequestForVariationUserPermissionGroup::findOrFail($inputs['request_for_variation_user_permission_group_id']);

        $requestForVariation                                                 = new RequestForVariation();
        $requestForVariation->project_id                                     = $project->id;
        $requestForVariation->request_for_variation_user_permission_group_id = $userPermissionGroup->id;
        $requestForVariation->description                                    = trim($inputs['decription_of_proposed_variation']);
        $requestForVariation->reasons_for_variation                          = trim($inputs['reasons_for_variation']);
        $requestForVariation->request_for_variation_category_id              = $inputs['rfv_category'];
        $requestForVariation->time_implication                               = trim($inputs['time_implication']);
        $requestForVariation->approved_category_amount                       = 0.0;

        $requestForVariation->save();

        $this->logAction(
            $requestForVariation,
            \Confide::user(),
            RequestForVariationUserPermission::ROLE_SUBMIT_RFV,
            RequestForVariationActionLog::ACTION_TYPE_SUBMITTED_NEW_RFV,
            null,
            null,
            null
        );

        $this->sendRfvNotifications($requestForVariation, \Confide::user(), RequestForVariationUserPermission::ROLE_FILL_UP_OMISSION_ADDITION, 'rfv.submitted_new_rfv');

        return $requestForVariation;
    }

    private function updateRfvOmissionAdditionAmount(RequestForVariation $requestForVariation, $inputs)
    {
        $requestForVariation->status = RequestForVariation::STATUS_PENDING_VERIFICATION;

        $requestForVariation->save();

        $this->logAction(
            $requestForVariation,
            \Confide::user(),
            RequestForVariationUserPermission::ROLE_FILL_UP_OMISSION_ADDITION,
            RequestForVariationActionLog::ACTION_TYPE_FILLED_OMISSION_ADDITION,
            null,
            null,
            null
        );

        $this->sendRfvNotifications($requestForVariation, \Confide::user(), RequestForVariationUserPermission::ROLE_SUBMIT_RFV, 'rfv.filled_nett_amount');
    }

    private function approveRejectOmissionAdditionAmount(RequestForVariation $requestForVariation, $inputs)
    {
        $approved = isset( $inputs['approve'] );
        $status   = $approved ? RequestForVariation::STATUS_VERIFIED : RequestForVariation::STATUS_PENDING_COST_ESTIMATE;
        $remarks  = $inputs['remarks'];

        $requestForVariation->description                       = $inputs['decription_of_proposed_variation'];
        $requestForVariation->reasons_for_variation             = $inputs['reasons_for_variation'];
        $requestForVariation->request_for_variation_category_id = $inputs['rfv_category'];
        $requestForVariation->time_implication                  = $inputs['time_implication'];
        $requestForVariation->status                            = $status;

        $requestForVariation->save();

        $actionType = ( $approved ) ? RequestForVariationActionLog::ACTION_TYPE_APPROVED_OMISSION_ADDITION : RequestForVariationActionLog::ACTION_TYPE_REJECTED_OMISSION_ADDITION;

        $this->logAction(
            $requestForVariation,
            \Confide::user(),
            RequestForVariationUserPermission::ROLE_SUBMIT_RFV,
            $actionType,
            null,
            $approved,
            $remarks
        );

        if( $approved )
        {
            $this->sendRfvNotifications($requestForVariation, \Confide::user(), RequestForVariationUserPermission::ROLE_SUBMIT_FOR_APPROVAL, 'rfv.nett_amount_approved', true);
        }
        else
        {
            $this->sendRfvNotifications($requestForVariation, \Confide::user(), RequestForVariationUserPermission::ROLE_FILL_UP_OMISSION_ADDITION, 'rfv.nett_amount_rejected');
        }
    }

    private function submitForVerification(RequestForVariation $requestForVariation, $inputs)
    {
        $verifiers = array_key_exists('verifiers', $inputs) ? array_filter($inputs['verifiers'], function($value)
        {
            return $value != "";
        }) : [];

        $requestForVariation->accumulative_approved_rfv_amount = number_format((float)$requestForVariation->project->getAccumulativeApprovedRfvAmount(), 2, '.', '');
        $requestForVariation->proposed_rfv_amount              = number_format((float)$requestForVariation->project->getProposedRfvAmount(), 2, '.', '');
        $requestForVariation->submitted_by                     = \Confide::user()->id;

        if( empty( $verifiers ) )
        {
            $approvedCategoryAmount = $requestForVariation->getCumulativeRfvAmountByStatusAndCategory([RequestForVariation::STATUS_APPROVED], $requestForVariation->request_for_variation_category_id) + $requestForVariation->nett_omission_addition;

            $requestForVariation->approved_category_amount = $approvedCategoryAmount;
            $requestForVariation->status = RequestForVariation::STATUS_APPROVED;
            $requestForVariation->save();

            $this->logAction(
                $requestForVariation,
                \Confide::user(),
                RequestForVariationUserPermission::ROLE_SUBMIT_FOR_APPROVAL,
                RequestForVariationActionLog::ACTION_TYPE_RFV_APPROVED,
                null,
                true
            );

            $this->sendRfvNotificationsToAllUserWithRfvPermission($requestForVariation);
        }
        else
        {
            Verifier::setVerifiers($verifiers, $requestForVariation);

            if( \PCK\Forum\ObjectThread::objectHasThread($requestForVariation) )
            {
                Verifier::syncForumUsers($requestForVariation);
            }

            $requestForVariation->status = RequestForVariation::STATUS_PENDING_APPROVAL;
            $requestForVariation->save();

            foreach($verifiers as $veriferId)
            {
                $this->logAction(
                    $requestForVariation,
                    \Confide::user(),
                    RequestForVariationUserPermission::ROLE_SUBMIT_FOR_APPROVAL,
                    RequestForVariationActionLog::ACTION_TYPE_SUBMITTED_FOR_APPROVAL,
                    (int)$veriferId,
                    null,
                    null
                );
            }

            $nextVerifier = Verifier::getCurrentVerifier($requestForVariation);

            $this->sendRfvNotificationsToVerifier($requestForVariation, \Confide::user(), $nextVerifier, 'rfv.submitted_for_approval');
        }
    }

    public function approveRejectVerification(RequestForVariation $requestForVariation, $inputs)
    {
        $user = \Confide::user();

        if( ! Verifier::isCurrentVerifier($user, $requestForVariation) )
        {
            return false;
        }

        $approved   = isset( $inputs['approve'] );

        $status     = $approved ? RequestForVariation::STATUS_APPROVED : RequestForVariation::STATUS_PENDING_VERIFICATION;
        $actionType = ( $approved ) ? RequestForVariationActionLog::ACTION_TYPE_RFV_APPROVED : RequestForVariationActionLog::ACTION_TYPE_RFV_REJECTED;
        $remarks    = $inputs['remarks'];

        Verifier::approve($requestForVariation, $approved);

        $this->logAction(
            $requestForVariation,
            $user,
            RequestForVariationUserPermission::ROLE_SUBMIT_FOR_APPROVAL,
            $actionType,
            $user->id,
            $approved,
            $remarks
        );

        $submitter    = $requestForVariation->submittedBy;
        $nextVerifier = Verifier::getCurrentVerifier($requestForVariation);

        if( $approved && $nextVerifier )
        {
            if( \PCK\Forum\ObjectThread::objectHasThread($requestForVariation) )
            {
                Verifier::syncForumUsers($requestForVariation);
            }

            // notify submitter of approval
            $this->sendRfvNotificationsToSender($requestForVariation, $user, $submitter, 'rfv.approved');
            // notify next verifier
            $this->sendRfvNotificationsToVerifier($requestForVariation, $user, $nextVerifier, 'rfv.submitted_for_approval');

            return true;
        }

        if($approved)
        {
            $approvedCategoryAmount = $requestForVariation->getCumulativeRfvAmountByStatusAndCategory([RequestForVariation::STATUS_APPROVED], $requestForVariation->request_for_variation_category_id) + $requestForVariation->nett_omission_addition;
            $requestForVariation->approved_category_amount = $approvedCategoryAmount;
        }

        $requestForVariation->status = $status;
        $requestForVariation->save();

        if( $approved )
        {
            $this->sendRfvNotificationsToAllUserWithRfvPermission($requestForVariation);
        }
        else
        {
            if( \PCK\Forum\ObjectThread::objectHasThread($requestForVariation) )
            {
                $thread = \PCK\Forum\ObjectThread::getObjectThread($requestForVariation);
                $thread->users()->sync(array());
            }

            //rejected, goes back to pending verification
            Verifier::deleteLog($requestForVariation);

            $this->sendRfvNotificationsToSender($requestForVariation, $user, $submitter, 'rfv.rejected');
            $this->sendRfvNotifications($requestForVariation, $user, RequestForVariationUserPermission::ROLE_SUBMIT_RFV, 'rfv.rejected');
        }
    }

    public function logAction(RequestForVariation $requestForVariation, User $user, $permissionModuleId, $actionType, $verifier = null, $approved = null, $remarks = null)
    {
        $requestForVariationActionLog                           = new RequestForVariationActionLog();
        $requestForVariationActionLog->request_for_variation_id = $requestForVariation->id;
        $requestForVariationActionLog->user_id                  = $user->id;
        $requestForVariationActionLog->permission_module_id     = $permissionModuleId;
        $requestForVariationActionLog->action_type              = $actionType;

        if( $verifier )
        {
            $requestForVariationActionLog->verifier = $verifier;
        }

        if( ! is_null($approved) )
        {
            $requestForVariationActionLog->approved = $approved;
        }

        if( $remarks )
        {
            $requestForVariationActionLog->remarks = $remarks;
        }

        $requestForVariationActionLog->save();
    }

    /**
     * gets the mapping of permission module to the current stage in a given rfv
     */
    public function getRfvStatusModuleMapping($rfvStatus)
    {
        return RequestForVariation::getRfvStatusModuleMapping($rfvStatus);
    }

    public function getUploadedFiles(Project $project, $requestForVariationId)
    {
        $requestForVariation = RequestForVariation::find($requestForVariationId);
        $records             = RequestForVariationFile::where('request_for_variation_id', $requestForVariation->id)
                                ->orderBy('id', 'ASC')
                                ->get();

        $fileInfo = [];

        foreach($records as $record)
        {
            $userIsUploader                = ($record->fileProperties->user_id == \Confide::user()->id);
            $uploader                      = User::find($record->fileProperties->user_id);
            $statusesEligibleForFileDelete = [ RequestForVariation::STATUS_PENDING_COST_ESTIMATE ];
            $canUserDeleteFile             = $userIsUploader && !($requestForVariation->isApproved() || $requestForVariation->isPendingForApproval());

            array_push($fileInfo, [
                'cabinet_id'        => $record->fileProperties->id,
                'fileName'          => $record->fileProperties->filename,
                'canUserDeleteFile' => $canUserDeleteFile,
                'uploader'          => ($uploader) ? $uploader->name : '-',
                'upload_date'       => Carbon::parse($record->created_at)->format(\Config::get('dates.created_and_updated_at_formatting')),
                'delete_route'      => route('requestForVariation.document.uploadDelete', [ $project->id, $record->fileProperties->id ]),
                'download_route'    => route('requestForVariation.document.download', [ $project->id, $record->fileProperties->id ]),
            ]);
        }

        return $fileInfo;
    }

    public function uploadDelete(Project $project, $id)
    {
        $rfvFile = RequestForVariationFile::where('cabinet_file_id', $id)->first();
        $rfvFile->fileProperties->delete();
        $rfvFile->delete();

        return $rfvFile->fileProperties->filename;
    }

    // gets pending RFVs in to-do list
    public function getPendingRequestForVariation(User $user, $includeFutureTasks, Project $project = null)
    {
        $query = \DB::table('request_for_variation_user_permission_groups as rfvGroup')
            ->join('request_for_variation_user_permissions as rfvPerm', 'rfvGroup.id', '=', 'rfvPerm.request_for_variation_user_permission_group_id')
            ->join('request_for_variations as rfv', 'rfvGroup.id', '=', 'rfv.request_for_variation_user_permission_group_id')
            ->join('projects as p', 'p.id', '=', 'rfv.project_id')
            ->where('rfvPerm.module_id', '=', \DB::raw('rfv.permission_module_in_charge'))
            ->whereIn('rfv.status', [ RequestForVariation::STATUS_PENDING_COST_ESTIMATE, RequestForVariation::STATUS_PENDING_VERIFICATION ])
            ->select('rfv.id', 'rfv.project_id', 'rfvPerm.user_id', 'rfv.description', 'rfvPerm.module_id', 'rfv.permission_module_in_charge', 'rfv.status', 'rfv.updated_at')
            ->where('rfvPerm.user_id', '=', $user->id)
            ->whereNull('p.deleted_at')
            ->whereNull('rfv.deleted_at');

        if( $project )
        {
            $query = $query->where('rfvGroup.project_id', $project->id);
        }

        $records = $query
            ->distinct('rfv.id')
            ->get();

        $pendingRfvRecords = new Collection();
        $pendingRFVs       = [];
        $count             = 0;

        foreach($records as $record)
        {
            $requestForVariation = RequestForVariation::find($record->id);

            if( $record->status == RequestForVariation::STATUS_VERIFIED )
            {
                if( ! $requestForVariation->canUserAssignVerifiers($user) )
                {
                    continue;
                }
            }

            $pendingRFVs[ $count++ ][ $requestForVariation->id ] = $requestForVariation;
        }

        // approval process codes
        $pendingApprovalRfvRecords = [];

        $pendingApprovalRfvQuery = \DB::table('request_for_variations as rfv')
                ->join('projects as p', 'p.id', '=', 'rfv.project_id')
                ->select('rfv.id', 'rfv.description', 'rfv.project_id', 'rfv.status', 'rfv.updated_at')
                ->where('rfv.status', RequestForVariation::STATUS_PENDING_APPROVAL)
                ->whereNull('p.deleted_at');

        if($project)
        {
            $pendingApprovalRfvQuery->where('rfv.project_id', $project->id);
        }

        $pendingApprovalRfvRecords = $pendingApprovalRfvQuery->distinct('rfv.id')->get();

        foreach($pendingApprovalRfvRecords as $record)
        {
            $requestForVariation = RequestForVariation::find($record->id);
            $proceed             = $includeFutureTasks ? Verifier::isAVerifierInline($user, $requestForVariation) : Verifier::isCurrentVerifier($user, $requestForVariation);

            if( ! $proceed )
            {
                continue;
            }

            $pendingRFVs[ $count++ ][ $requestForVariation->id ] = $requestForVariation;
        }
        // approval process codes end

        foreach($pendingRFVs as $key => $pendingRFV)
        {
            $pendingRfvRecords = $pendingRfvRecords->merge($pendingRFV);
        }

        return $pendingRfvRecords;
    }

    private function sendRfvNotifications(RequestForVariation $requestForVariation, $responder, $recipientModule, $view, $editorsOnly = false)
    {
        $subject                = 'RFV Notification';
        $emailNotificationView  = 'notifications.email.' . $view;
        $systemNotificationView = $view;
        $project                = $requestForVariation->project;

        $recipientIds = $requestForVariation->getAllUserIdsInModule($recipientModule, $editorsOnly);

        foreach($recipientIds as $id)
        {
            $recipient = User::find($id);
            $this->emailNotifier->sendRfvNotification($project, $requestForVariation, $responder, $recipient, $emailNotificationView, $subject);
            $this->systemNotifier->sendRfvNotification($recipient, $project, $requestForVariation, $systemNotificationView, $responder);
        }
    }

    private function sendRfvNotificationsToVerifier(RequestForVariation $requestForVariation, $responder, $nextVerifier, $view)
    {
        $subject                = 'RFV Notification';
        $emailNotificationView  = 'notifications.email.' . $view;
        $systemNotificationView = $view;
        $project                = $requestForVariation->project;

        $this->emailNotifier->sendRfvNotification($project, $requestForVariation, $responder, $nextVerifier, $emailNotificationView, $subject);
        $this->systemNotifier->sendRfvNotification($nextVerifier, $project, $requestForVariation, $systemNotificationView, $responder);
    }

    private function sendRfvNotificationsToSender(RequestForVariation $requestForVariation, $responder, $submitter, $view)
    {
        $subject                = 'RFV Notification';
        $emailNotificationView  = 'notifications.email.' . $view;
        $systemNotificationView = $view;
        $project                = $requestForVariation->project;

        $this->emailNotifier->sendRfvNotification($project, $requestForVariation, $responder, $submitter, $emailNotificationView, $subject);
        $this->systemNotifier->sendRfvNotification($submitter, $project, $requestForVariation, $systemNotificationView, $responder);
    }

    private function sendRfvNotificationsToAllUserWithRfvPermission(RequestForVariation $requestForVariation)
    {
        $userPermissions = $requestForVariation->userPermissionGroup->userPermissions;

        foreach($userPermissions as $userPermission)
        {
            $this->sendRfvNotificationsToSender($requestForVariation, \Confide::user(), $userPermission->user, 'rfv.rfv_approved');
        }
    }

    /**
     * generates financial standing data
     *
     * @param RequestForVariation $requestForVariation
     * @param bool                $selectFromDB - select from DB instead of calculating
     *
     * @return array
     */
    public function getFinancialStandingData(RequestForVariation $requestForVariation, $selectFromDB = true)
    {
        $project                                   = $requestForVariation->project;
        $cncSum                                    = $project->requestForVariationContractAndContingencySum;
        $originalContractSum                       = $cncSum->contract_sum_includes_contingency_sum ? $cncSum->original_contract_sum : ($cncSum->original_contract_sum + $cncSum->contingency_sum); 
        $cncTotal                                  = $originalContractSum - $cncSum->contingency_sum;
        $accumulativeApprovedRfvAmount             = $selectFromDB ? $requestForVariation->accumulative_approved_rfv_amount : $project->getAccumulativeApprovedRfvAmount();
        $proposedRfvAmount                         = $selectFromDB ? $requestForVariation->proposed_rfv_amount : $project->getProposedRfvAmount();
        $addOmitTotal                              = $accumulativeApprovedRfvAmount + $proposedRfvAmount;
        $anticipatedContractSum                    = $cncTotal + $addOmitTotal;
        $accumulativeApprovePlusCurrentProposedRfv = $accumulativeApprovedRfvAmount + $requestForVariation->nett_omission_addition;

        return [
            'originalContractSum'                                 => $originalContractSum,
            'contingencySum'                                      => $cncSum->contingency_sum,
            'cncTotal'                                            => number_format((float)$cncTotal, 2, '.', ''),
            'accumulativeApprovedRfvAmount'                       => number_format((float)$accumulativeApprovedRfvAmount, 2, '.', ''),
            'proposedRfvAmount'                                   => number_format((float)$proposedRfvAmount, 2, '.', ''),
            'addOmitTotal'                                        => number_format((float)$addOmitTotal, 2, '.', ''),
            'addOmitTotalPercentage'                              => ( $cncTotal ) ? round(( ( $addOmitTotal / $cncTotal ) * 100 ), 2) . ' %' : 0 . ' %',
            'anticipatedContractSum'                              => number_format((float)( $anticipatedContractSum ), 2, '.', ''),
            'balanceOfContingency'                                => number_format((float)( $cncSum->contingency_sum - $addOmitTotal ), 2, '.', ''),
            'accumulativeApprovePlusCurrentProposedRfv'           => number_format((float) $accumulativeApprovePlusCurrentProposedRfv, 2, '.', ','),
            'accumulativeApprovePlusCurrentProposedRfvPercentage' => ( $cncTotal ) ? round(( ( $accumulativeApprovePlusCurrentProposedRfv / $cncTotal ) * 100 ), 2) . ' %' : 0 . ' %',
        ];
    }


    public function getIsContractAndContingencySumFilled(Project $project)
    {
        $filled = false;
        $record = $project->requestForVariationContractAndContingencySum;

        if( $record ) $filled = true;

        return $filled;
    }

    public static function getApprovedRfvCount($project)
    {
        if( ! $project ) return;

        return RequestForVariation::where('project_id', $project->id)
            ->where('status', RequestForVariation::STATUS_APPROVED)
            ->count();
    }

    public function saveRfvAiNumber(RequestForVariation $requestForVariation, $aiNumber)
    {
        $requestForVariation->ai_number = $aiNumber;
        $requestForVariation->save();

        return $requestForVariation;
    }

    public function getVariationOrderItems(RequestForVariation $requestForVariation)
    {
        $variationOrderItems = $requestForVariation->getCostEstimateItems();

        foreach($variationOrderItems as $key => $variationOrderItem)
        {
            $variationOrderItems[ $key ]['uom_id']     = $variationOrderItem['uom_id'] > 0 ? $variationOrderItem['uom_id'] : -1;
            $variationOrderItems[ $key ]['uom_symbol'] = $variationOrderItem['uom_id'] > 0 ? $variationOrderItem['uom_symbol'] : '';
        }

        return $variationOrderItems;
    }

    public function flushData(RequestForVariation $requestForVariation)
    {
        $buildspaceProjectId = $requestForVariation->project->getBsProjectMainInformation()->project_structure_id;

        $variationOrder = VariationOrder::where('eproject_rfv_id', '=', $requestForVariation->id)->where('project_structure_id', '=', $buildspaceProjectId)->first();

        if( ! $variationOrder ) return true;

        VariationOrderItem::where('variation_order_id', '=', $variationOrder->id)->delete();

        $requestForVariation->nett_omission_addition = 0;

        return $requestForVariation->save();
    }

    public function addData(RequestForVariation $requestForVariation, array $requestForVariationItemData)
    {
        $buildspaceProjectId = $requestForVariation->project->getBsProjectMainInformation()->project_structure_id;

        $variationOrder = VariationOrder::where('eproject_rfv_id', '=', $requestForVariation->id)->where('project_structure_id', '=', $buildspaceProjectId)->first();

        if( ! $variationOrder )
        {
            $variationOrder = $requestForVariation->createBuildspaceVariationOrder();
        }

        $nextPriority = VariationOrderItem::where('variation_order_id', '=', $variationOrder->id)->max('priority') + 1;

        $columnIndexes = array(
            'bill_ref'           => 0,
            'description'        => 1,
            'unit'               => 2,
            'reference_rate'     => 3,
            'reference_quantity' => 4,
        );

        $item = null;

        foreach($requestForVariationItemData as $key => $itemData)
        {
            if( \PCK\Helpers\Arrays::arrayValuesEmpty($itemData) ) continue;

            if( ! $item )
            {
                $item = new VariationOrderItem(array(
                    'bill_ref'    => trim($itemData[ $columnIndexes['bill_ref'] ]) ?? "",
                    'description' => trim($itemData[ $columnIndexes['description'] ]) ?? "",
                ));

                $type = VariationOrderItem::TYPE_WORK_ITEM;

                if( empty($itemData[$columnIndexes['reference_rate']]) && empty($itemData[$columnIndexes['reference_quantity']]) && empty($itemData[$columnIndexes['unit']]) ) $type = VariationOrderItem::TYPE_HEADER;

                $item->type = $type;

                $item->variation_order_id = $variationOrder->id;

                if( ! empty( $itemData[ $columnIndexes['unit'] ] ) )
                {
                    $unit = \PCK\Buildspace\UnitOfMeasurement::getOrCreate($itemData[ $columnIndexes['unit'] ]);

                    $item->uom_id = $unit->id;
                }

                $item->reference_rate     = is_numeric($itemData[ $columnIndexes['reference_rate'] ]) ? $itemData[ $columnIndexes['reference_rate'] ] : 0;
                $item->reference_quantity = is_numeric($itemData[ $columnIndexes['reference_quantity'] ]) ? $itemData[ $columnIndexes['reference_quantity'] ] : 0;

                $item->priority = $nextPriority++;
            }
            else
            {
                $item->description .= ' ' . trim($itemData[ $columnIndexes['description'] ]);
            }

            // Save if next line is empty.
            if( \PCK\Helpers\Arrays::arrayValuesEmpty($requestForVariationItemData[ $key + 1 ] ?? array()) )
            {
                $item->save();

                $item = null;
            }
        }
    }

}
