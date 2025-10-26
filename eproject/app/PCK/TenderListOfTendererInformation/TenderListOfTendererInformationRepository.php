<?php namespace PCK\TenderListOfTendererInformation;

use Carbon\Carbon;
use PCK\Notifications\EmailNotifier;
use PCK\Notifications\SystemNotifier;
use PCK\Projects\Project;
use PCK\TenderInterviews\TenderInterviewRepository;
use PCK\Tenders\Tender;
use Illuminate\Events\Dispatcher;
use PCK\Base\BaseModuleRepository;
use PCK\TenderFormVerifierLogs\TenderFormVerifierLog;
use PCK\TenderRecommendationOfTendererInformation\ContractorCommitmentStatus;
use PCK\TenderRecommendationOfTendererInformation\TenderRecommendationOfTendererInformation;
use PCK\TenderFormVerifierLogs\FormLevelStatus;
use Illuminate\Support\Facades\DB;
use PCK\CompanyTenderListOfTendererInformation\CompanyTenderListOfTendererInformation;
use PCK\Users\User;
use PCK\Tenders\TenderStages;
use PCK\Helpers\Key;
use PCK\ExpressionOfInterest\ExpressionOfInterestTokens;
use PCK\Helpers\Mailer;
use PCK\Companies\Company;

class TenderListOfTendererInformationRepository extends BaseModuleRepository {

    protected $events;

    private $emailNotifier;
    private $systemNotifier;

    private $tenderInterviewRepository;

    public function __construct(Dispatcher $events, EmailNotifier $emailNotifier, SystemNotifier $systemNotifier, TenderInterviewRepository $tenderInterviewRepository)
    {
        $this->events                    = $events;
        $this->emailNotifier             = $emailNotifier;
        $this->systemNotifier            = $systemNotifier;
        $this->tenderInterviewRepository = $tenderInterviewRepository;
    }

    public function saveLOTInformation(Tender $tender, array $inputs)
    {
        $logStatus = false;
        $user      = \Confide::user();

        $sentToVerify        = isset( $inputs['send_to_verify'] );
        $verificationReject  = isset( $inputs['verification_reject'] );
        $verificationConfirm = isset( $inputs['verification_confirm'] );
        $verifierRemark      = $inputs['verifier_remark'] ?? null;

        $object = $tender->listOfTendererInformation;

        if( ( $tender->listOfTendererInformation && $object->stillInProgress() ) OR $sentToVerify )
        {
            $this->syncSelectedVerifiers($object, $inputs);
        }

        // sync contractors' commitment status and remarks when submitting
        if( $sentToVerify ) {
            $this->syncSelectedContractorCommitmentStatus($object, $inputs);
            $this->syncSelectedContractorRemark($object, $inputs);
        }

        $hasVerifiers = ! $object->verifiers->isEmpty();

        if( $object->stillInProgress() )
        {
            $object->date_of_calling_tender                         = $inputs['date_of_calling_tender'];
            $object->date_of_closing_tender                         = $inputs['date_of_closing_tender'];
            $object->technical_tender_closing_date                  = $inputs['technical_tender_closing_date'];
            $object->completion_period                              = $inputs['completion_period'];
            $object->project_incentive_percentage                   = empty( $inputs['project_incentive_percentage'] ) ? null : $inputs['project_incentive_percentage'];
            $object->allow_contractor_propose_own_completion_period = false;
            $object->disable_tender_rates_submission                = $inputs['disable_tender_rates_submission'] ?? false;
            $object->technical_evaluation_required                  = isset( $inputs['technical_evaluation_required'] ) ? $inputs['technical_evaluation_required'] : false;
            $object->contract_limit_id                              = $object->contractLimit ? $object->contractLimit->id : null;
            $object->procurement_method_id                          = empty( $inputs['procurement_method_id'] ) ? null : $inputs['procurement_method_id'];
            $object->remarks                                        = $inputs['lot_remarks'];

            if( $object->technical_evaluation_required )
            {
                $object->contract_limit_id = ( ! empty( $inputs['contract_limit_id'] ) ) ? $inputs['contract_limit_id'] : null;
            }

            $object->updated_by = $user->id;

            if( isset( $inputs['allow_contractor_propose_own_completion_period'] ) AND $inputs['allow_contractor_propose_own_completion_period'] )
            {
                $object->allow_contractor_propose_own_completion_period = $inputs['allow_contractor_propose_own_completion_period'];
            }
        }

        if( $sentToVerify )
        {
            $object->status     = TenderListOfTendererInformation::NEED_VALIDATION;
            $object->updated_by = $user->id;

            $logStatus = TenderListOfTendererInformation::NEED_VALIDATION;
        }

        if( $verificationReject )
        {
            $object->rejectVerification();
            $logStatus = TenderListOfTendererInformation::USER_VERIFICATION_REJECTED;
        }

        if( $verificationConfirm )
        {
            $this->setVerificationConfirmToCurrentVerifier($object);

            // if there is no in progress verifiers available then straight update the status to submission
            if( $object->verifiers->isEmpty() ) $object->status = TenderListOfTendererInformation::SUBMISSION;

            $logStatus = TenderListOfTendererInformation::USER_VERIFICATION_CONFIRMED;

            if( \PCK\Forum\ObjectThread::objectHasThread($object) )
            {
                $this->syncApprovalForumUsers($object);
            }
        }

        if( ( ! $hasVerifiers ) && $sentToVerify )
        {
            $object->status = TenderListOfTendererInformation::SUBMISSION;

            $logStatus = TenderListOfTendererInformation::USER_VERIFICATION_CONFIRMED;
        }

        $object = $tender->listOfTendererInformation()->save($object);

        $object->load('updatedBy');

        // Send requesting verification email.
        if( $sentToVerify ) {
            $this->emailNotifier->sendLOTTenderVerificationEmail($tender, $object);
            $this->systemNotifier->sendTenderVerificationNotification($tender, $tender->listOfTendererInformation, 'list_of_tenderer', '#s2');
        }

        // Send verifier's decision email.
        if( $verificationReject OR $verificationConfirm )
        {
            $viewName = 'list_of_tenderer_confirm';

            if( $verificationReject )
            {
                $viewName = 'list_of_tenderer_reject';
            }
            else
            {
                $this->emailNotifier->sendLOTTenderVerificationEmail($tender, $object);
                $this->systemNotifier->sendTenderVerificationNotification($tender, $tender->listOfTendererInformation, 'list_of_tenderer', '#s2');
            }

            $projectEditors = $this->getEditorsOfProject($tender->project);

            // sends email notifications whenever tender processes are approved or rejected
            $view = 'notifications.email.tender.' . $viewName;

            foreach($projectEditors as $projectEditor)
            {
                $subject = '[' . $tender->project->reference . '] ' . trans('email.eTenderNotification', [], 'messages', $projectEditor->settings->language->code);
                $this->emailNotifier->sendTenderProcessApproveOrRejectEmail($tender, $subject, $view, $projectEditor);
            }

            // sends system notifications whenever tender processes are approved or rejected
            $this->sendTenderProcessApproveOrRejectNotification($tender->project, $tender, $projectEditors, \Confide::user(), $viewName, 'projects.tender.show', '#s2');
        }

        if( $logStatus )
        {
            $log                  = new TenderFormVerifierLog();
            $log->user_id         = $user->id;
            $log->type            = $logStatus;
            $log->verifier_remark = $verifierRemark;

            $object->verifierLogs()->save($log);
        }

        // Reload object.
        return $object::find($object->id);
    }

    private function getEditorsOfProject(Project $project) {
        $editors = $project->contractGroupProjectUsers->reject(function($user) {
            return !$user->is_contract_group_project_owner;
        });

        $projectEditors = array();

        foreach($editors as $editor) {
            array_push($projectEditors, $editor->user);
        }

        return $projectEditors;
    }

    private function sendTenderProcessApproveOrRejectNotification(
        Project $project, Tender $tender, array $recipients, User $sender, $viewName, $routeName, $tabId) 
    {
        $url = route($routeName, array($project->id, $tender->id), false);
        $url .= $tabId;
        $notificationInfo = array();
        $currentTime = Carbon::now();
        $view = \View::make("notifications.system.{$viewName}");

        foreach($recipients as $recipient) {
            // don't send notification to self
            if($recipient->id == $sender->id) continue;

            $notificationInfo = array(
                'from_id'       => $sender->id,
                'from_type'     => 'PCK\Users\User',
                'to_id'         => $recipient->id,
                'to_type'       => 'PCK\Users\User',
                'category_id'   => 1,
                'url'           => $url,
                'extra'         => $view,
                'created_at'    => $currentTime,
                'updated_at'    => $currentTime
            );
        }

        if( ! empty( $notificationInfo ) ) \Notifynder::sendMultiple($notificationInfo);
    }

    public function sendEmailNotificationToEditors(Tender $tender, $view) {
        $projectEditors = $this->getEditorsOfProject($tender->project);

        foreach($projectEditors as $projectEditor)
        {
            $this->emailNotifier->sendEmailNotificationToEditor($tender, $view, $projectEditor);
        }
    }

    public function syncSelectedContractorCommitmentStatus(TenderListOfTendererInformation $lotInformation, array $inputs)
    {
        if( ! isset( $inputs['status'] ) ) return;

        foreach($inputs['status'] as $companyId => $status)
        {
            $data = array( 'status' => $status );

            $result = \DB::table('company_tender_lot_information')
                ->select('id', 'status')
                ->where('tender_lot_information_id', '=', $lotInformation->id)
                ->where('company_id', '=', $companyId)
                ->first();
            
            $lotInformation->selectedContractors()->updateExistingPivot($companyId, $data);

            if($data['status'] == $result->status) continue;

            $this->updateContractorsCommitmentStatusLog($result->id, $status);
        }
    }
    
    public function syncSelectedContractorRemark(TenderListOfTendererInformation $lotInformation, array $inputs)
    {
        if( ! isset( $inputs['remarks'] ) ) return;

        foreach($inputs['remarks'] as $companyId => $remark)
        {
            $remark = empty( $remark ) ? null : $remark;

            $data = array( 'remarks' => $remark );

            $lotInformation->selectedContractors()->updateExistingPivot($companyId, $data);
        }
    }

    public function syncSelectedCompanyForLOTInformation(TenderListOfTendererInformation $lotInformation, array $inputs)
    {
        $contractorIds = array();

        if( isset( $inputs['contractors'] ) )
        {
            foreach($inputs['contractors'] as $contractorId)
            {
                $contractorIds[] = $contractorId;
            }
        }

        $lotInformation->selectedContractors()->sync($contractorIds);
    }

    public function reEnableLOTContractor(Tender $tender, $contractorId)
    {
        $contractor = $tender->listOfTendererInformation
            ->selectedContractors()
            ->wherePivot('company_id', $contractorId)
            ->firstOrFail();

        $tender->listOfTendererInformation->selectedContractors()
            ->updateExistingPivot($contractor->id, array( 'deleted_at' => null ));
    }

    public function deleteLOTContractor(Tender $tender, $contractorId)
    {
        $contractor = $tender->listOfTendererInformation
            ->selectedContractors()
            ->wherePivot('company_id', $contractorId)
            ->firstOrFail();

        if( $contractor->pivot->added_by_gcd )
        {
            $tender->listOfTendererInformation->selectedContractors()
                ->detach($contractor->id);
        }
        else
        {
            $tender->listOfTendererInformation->selectedContractors()
                ->updateExistingPivot($contractor->id, array( 'deleted_at' => Carbon::now() ));
        }
    }

    public function cloneInformationToListOfTenderer(TenderRecommendationOfTendererInformation $rotInformation)
    {
        $contractors = array();
        $user        = \Confide::user();

        $tender = $rotInformation->tender;

        // create a record for TenderListOfTendererInformation
        $lotObject = $tender->listOfTendererInformation ?: new TenderListOfTendererInformation();

        $lotObject->date_of_calling_tender                         = $rotInformation->proposed_date_of_calling_tender;
        $lotObject->date_of_closing_tender                         = $rotInformation->proposed_date_of_closing_tender;
        $lotObject->technical_tender_closing_date                  = $rotInformation->technical_tender_closing_date;
        $lotObject->completion_period                              = $rotInformation->completion_period;
        $lotObject->project_incentive_percentage                   = $rotInformation->project_incentive_percentage;
        $lotObject->allow_contractor_propose_own_completion_period = $rotInformation->allow_contractor_propose_own_completion_period;
        $lotObject->disable_tender_rates_submission                = $rotInformation->disable_tender_rates_submission;
        $lotObject->technical_evaluation_required                  = $rotInformation->technical_evaluation_required;
        $lotObject->contract_limit_id                              = $rotInformation->contract_limit_id;
        $lotObject->procurement_method_id                          = $rotInformation->procurement_method_id;
        $lotObject->remarks                                        = $rotInformation->remarks;
        $lotObject->created_by                                     = $user->id;
        $lotObject->updated_by                                     = $user->id;

        $lotObject = $tender->listOfTendererInformation()->save($lotObject);

        $rotInformation->load('selectedContractors');

        // get selected contractor with status of OK
        foreach($rotInformation->selectedContractors as $contractor)
        {
            $contractorStatus = $contractor->pivot->status;

            if( $contractorStatus !== ContractorCommitmentStatus::OK ) continue;

            $contractors[ $contractor->id ] = array( 
                'added_by_gcd' => false,
                'status' => ContractorCommitmentStatus::OK
            );
        }

        $lotObject->selectedContractors()->sync($contractors);
    }

    public function syncSelectedVerifiers(TenderListOfTendererInformation $lotInformation, array $inputs)
    {
        $data = array();

        foreach($inputs['verifiers'] ?? array() as $verifier)
        {
            if( $verifier <= 0 ) continue;

            $data[] = $verifier;
        }

        $lotInformation->verifiers()->sync(array());
        $lotInformation->verifiers()->sync($data);

        // reload the relation, in order not keep cache copy
        $lotInformation->load('verifiers');

        if( \PCK\Forum\ObjectThread::objectHasThread($lotInformation) )
        {
            $this->syncApprovalForumUsers($lotInformation);
        }
    }

    private function setRejectedStatusToAllVerifiers(TenderListOfTendererInformation $lotInformation)
    {
        $statuses = array(
            TenderListOfTendererInformation::USER_VERIFICATION_IN_PROGRESS,
            TenderListOfTendererInformation::USER_VERIFICATION_CONFIRMED
        );

        \DB::table('tender_lot_information_user')
            ->where('tender_lot_information_id', '=', $lotInformation->id)
            ->whereIn('status', $statuses)
            ->update(array( 'status' => TenderListOfTendererInformation::USER_VERIFICATION_REJECTED ));

        $lotInformation->load('verifiers');
    }

    private function setVerificationConfirmToCurrentVerifier(TenderListOfTendererInformation $object)
    {
        $user = \Confide::user();

        $object->verifiers()->updateExistingPivot($user->id, array(
            'status' => TenderListOfTendererInformation::USER_VERIFICATION_CONFIRMED
        ));

        $object->load('verifiers');
    }

    public function sendEmailNotificationToSelectedContractors(array $allCompanyAdmins, $projectId, $tenderId, $emailDetails) {
        $tenderStage = TenderStages::TENDER_STAGE_LIST_OF_TENDERER;
        $tenderLOTInformation = TenderListOfTendererInformation::where('tender_id', '=', $tenderId)->first();
        $project = Project::find($projectId);

        foreach($allCompanyAdmins as $admin) {
            $token = Key::createUniqueKeyAcrossTables(array(
                'expression_of_interest_tokens' => 'token'
            ));

            $subject = '[' . $project->reference . '] ' . trans('tenders.expressionOfInterest', [], 'messages', $admin->settings->language->code);

            $this->saveEntryIntoExpressionOfInterestTable($tenderLOTInformation, $admin, $token);
            $this->emailNotifier->sendStatusConfirmationEmailToSelectedContractor($project, $tenderStage, $tenderLOTInformation, $admin, $emailDetails, $subject, $token);
        }
    }

    private function saveEntryIntoExpressionOfInterestTable(TenderListOfTendererInformation $tenderLOTInformation, User $admin, $token) {
        $expressionOfInterestTokens                         = new ExpressionOfInterestTokens();
        $expressionOfInterestTokens->tenderstageable_id     = $tenderLOTInformation->id;
        $expressionOfInterestTokens->tenderstageable_type   = TenderListOfTendererInformation::class;
        $expressionOfInterestTokens->user_id                = $admin->id;
        $expressionOfInterestTokens->company_id             = $admin->company_id;
        $expressionOfInterestTokens->token                  = $token;
        $expressionOfInterestTokens->save();
    }

    public function updateCompanyTenderLOTInfoConfirmationStatusUnauthenticated($expressionOfInterest, $input, $token = null)
    {
        $commitmentStatusId = $input['option'];

        $result = \DB::table('company_tender_lot_information')
            ->select('id')
            ->where('tender_lot_information_id', $expressionOfInterest->tenderstageable_id)
            ->where('company_id', $expressionOfInterest->company_id)
            ->first();
        
        $company_tender_lot_information_id = $result->id;

        $result = \DB::table('company_tender_lot_information')
                    ->where('tender_lot_information_id', $expressionOfInterest->tenderstageable_id)
                    ->where('company_id', $expressionOfInterest->company_id)
                    ->update(array(
                        'status' => $commitmentStatusId
                    ));

        if( $result )
        {
            $log = $this->updateContractorsCommitmentStatusLog($company_tender_lot_information_id, $commitmentStatusId, $input, $token);

            //send email to business unit
            $companyTenderListOfTendererInformation = CompanyTenderListOfTendererInformation::find($company_tender_lot_information_id);
            $projectId                              = $companyTenderListOfTendererInformation->tenderLOTInformation->tender->project->id;
            $replyDetails                           = array();
            $replyDetails['companyId']              = $companyTenderListOfTendererInformation->company_id;
            $replyDetails['status']                 = $log->status;
            $replyDetails['replied_at_time']        = $log->created_at;

            $this->sendEmailNotificationForStatusConfirmationReply($projectId, $replyDetails);
        }

        return $result;
    }

    public function updateContractorsCommitmentStatusLog($company_tender_lot_information_id, $commitmentStatusId, $input = array(), $token = null)
    {
        $company_tenderLOTInfo = CompanyTenderListOfTendererInformation::find($company_tender_lot_information_id);

        if( ! $user = \Confide::user() ) {
            $expressionOfInterestToken = ExpressionOfInterestTokens::where('token', $token)->first();
            $user = User::find($expressionOfInterestToken->user_id);
        }

        return $company_tenderLOTInfo->contractorsCommitmentStatusLog()
            ->create(array(
                "user_id" => $user->id,
                "status"  => $commitmentStatusId,
                "remarks" => $input['remarks'] ?? null,
            ));
    }

    public function getContractorsCommitmentStatusLog($tenderId, $companyId)
    {
        $record = \DB::table('company_tender_lot_information')
            ->join('tender_lot_information', 'company_tender_lot_information.tender_lot_information_id', '=', 'tender_lot_information.id')
            ->select('company_tender_lot_information.id')
            ->where('company_tender_lot_information.company_id', '=', $companyId)
            ->where('tender_lot_information.tender_id', '=', $tenderId)
            ->first();

        $company_tenderLOTInformation = CompanyTenderListOfTendererInformation::find($record->id);

        $log = array();

        foreach($company_tenderLOTInformation->contractorsCommitmentStatusLog as $logEntry)
        {
            $user   = User::find($logEntry->user_id);
            $status = ContractorCommitmentStatus::getText($logEntry->status);

            array_push($log, array( 'user' => $user->name, 'userEmail' => $user->email, 'status' => $status, 'date' => $logEntry->created_at, 'remarks' => $logEntry->remarks ));
        }

        return $log;
    }

    public function getPendingLotOfTenderersByUser(User $user, $includeFutureTasks, Project $project = null)
    {
        $listOfTendersLOTInfo = [];
        $proceed = false;

        if($project)
        {
            if($project->latestTender->listOfTendererInformation)
            {
                $listOfTenderer = $project->latestTender->listOfTendererInformation;

                if($listOfTenderer && $listOfTenderer->isBeingValidated())
                {
                    $proceed = ($includeFutureTasks) ? in_array($user->id, $listOfTenderer->verifiers->lists('id')) : ($listOfTenderer->latestVerifier->first() && ($user->id === $listOfTenderer->latestVerifier->first()->id));

                    if($proceed)
                    {
                        array_push($listOfTendersLOTInfo, [
                            'project_reference'        => $project->reference,
                            'parent_project_reference' => $project->isSubProject() ? $project->parentProject->reference : null,
                            'project_id'               => $project->id,
                            'parent_project_id'        => $project->isSubProject() ? $project->parentProject->id : null,
                            'company_id'               => $project->business_unit_id,
                            'project_title'            => $project->title,
                            'parent_project_title'     => $project->isSubProject() ? $project->parentProject->title : null,
                            'module'                   => TenderListOfTendererInformation::LIST_OF_TENDERER_MODULE_NAME,
                            'days_pending'             => TenderListOfTendererInformationUser::getDaysPending($listOfTenderer, $user),
                            'tender_id'                => $project->latestTender->id,
                            'is_future_task'           => !($listOfTenderer->latestVerifier->first() && ($user->id === $listOfTenderer->latestVerifier->first()->id)),
                            'route'                    => route('projects.tender.show', array('projectId' => $project->id, 'tenderId' => $project->latestTender->id)) . '#s2'
                        ]);
                    }
                }
            }
        }
        else
        {
            $listOfVerifiers = DB::table('tender_lot_information_user')
                                    ->join('tender_lot_information', 'tender_lot_information_user.tender_lot_information_id', '=', 'tender_lot_information.id')
                                    ->join('tenders', 'tender_lot_information.tender_id' , '=', 'tenders.id')
                                    ->join('projects', 'tenders.project_id', '=', 'projects.id')
                                    ->select('tender_lot_information_user.id', 'tender_lot_information_user.tender_lot_information_id', 'tender_lot_information_user.user_id')
                                    ->where('tender_lot_information_user.status', FormLevelStatus::USER_VERIFICATION_IN_PROGRESS)
                                    ->where('tender_lot_information_user.user_id', $user->id)
                                    ->whereNull('projects.deleted_at')
                                    ->get();

            foreach($listOfVerifiers as $verifier)
            {
                $listOfTenderer = TenderListOfTendererInformation::find($verifier->tender_lot_information_id);

                if($listOfTenderer && $listOfTenderer->isBeingValidated())
                {
                    $proceed = ($includeFutureTasks) ? in_array($user->id, $listOfTenderer->verifiers->lists('id')) : ($listOfTenderer->latestVerifier && ($user->id === $listOfTenderer->latestVerifier->first()->id));

                    if($proceed)
                    {
                        $project      = $listOfTenderer->tender->project;
                        $partialRoute = (is_null($user->getAssignedCompany($project)) && $user->isTopManagementVerifier()) ? 'topManagementVerifiers.projects.tender.show' : 'projects.tender.show';

                        array_push($listOfTendersLOTInfo, [
                            'project_reference'        => $project->reference,
                            'parent_project_reference' => $project->isSubProject() ? $project->parentProject->reference : null,
                            'project_id'               => $project->id,
                            'parent_project_id'        => $project->isSubProject() ? $project->parentProject->id : null,
                            'company_id'               => $project->business_unit_id,
                            'project_title'            => $project->title,
                            'parent_project_title'     => $project->isSubProject() ? $project->parentProject->title : null,
                            'module'                   => TenderListOfTendererInformation::LIST_OF_TENDERER_MODULE_NAME,
                            'days_pending'             => TenderListOfTendererInformationUser::getDaysPending($listOfTenderer, $user),
                            'tender_id'                => $listOfTenderer->tender->id,
                            'is_future_task'           => !($listOfTenderer->latestVerifier && ($user->id === $listOfTenderer->latestVerifier->first()->id)),
                            'route'                    => route($partialRoute, array('projectId' => $project->id, 'tenderId' => $listOfTenderer->tender->id)) . '#s2'
                        ]);                      
                    }
                }
            }
        }

        return $listOfTendersLOTInfo;
    }

    public function syncApprovalForumUsers(TenderListOfTendererInformation $object)
    {
        if( ! \PCK\Forum\ObjectThread::objectHasThread($object) ) return;

        $thread = \PCK\Forum\ObjectThread::getObjectThread($object);

        $userIds = $object->allVerifiers()->wherePivot('status', '=', TenderListOfTendererInformation::USER_VERIFICATION_CONFIRMED)->get()->lists('id');

        if( $latestVerifier = $object->latestVerifier->first() ) $userIds[] = $latestVerifier->id;

        $userIds[] = $object->updated_by;

        $thread->syncThreadUsers($userIds);
    }
}