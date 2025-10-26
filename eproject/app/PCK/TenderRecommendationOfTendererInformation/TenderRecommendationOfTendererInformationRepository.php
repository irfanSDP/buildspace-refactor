<?php namespace PCK\TenderRecommendationOfTendererInformation;

use PCK\Tenders\TenderStages;
use PCK\Users\User;
use PCK\Helpers\Key;
use PCK\Tenders\Tender;
use Illuminate\Events\Dispatcher;
use PCK\Base\BaseModuleRepository;
use PCK\TenderFormVerifierLogs\TenderFormVerifierLog;
use PCK\CompanyTenderRecommendationOfTendererInformation\CompanyTenderRecommendationOfTendererInformation;
use PCK\TenderRecommendationOfTendererInformation\TenderRecommendationOfTendererInformationUser;
use Illuminate\Support\Facades\DB;
use PCK\TenderFormVerifierLogs\FormLevelStatus;
use PCK\TenderRecommendationOfTendererInformation\TenderRecommendationOfTendererInformation;
use PCK\ExpressionOfInterest\ExpressionOfInterestTokens;
use PCK\ContractGroupProjectUsers\ContractGroupProjectUser;
use PCK\Helpers\Mailer;
use PCK\Projects\Project;
use PCK\Companies\Company;
use Carbon\Carbon;
use PCK\Notifications\EmailNotifier;
use PCK\Notifications\SystemNotifier;
use PCK\FormOfTender\FormOfTenderRepository;
use PCK\TenderAlternatives\TenderAlternativeFive;
use PCK\FormOfTender\TenderAlternative;

class TenderRecommendationOfTendererInformationRepository extends BaseModuleRepository implements FormLevelStatus {

    protected $events;
    protected $emailNotifier;
    protected $systemNotifier;
    protected $formOfTenderRepository;

    public function __construct(Dispatcher $events, EmailNotifier $emailNotifier, SystemNotifier $systemNotifier, FormOfTenderRepository $formOfTenderRepository)
    {
        $this->events = $events;
        $this->emailNotifier = $emailNotifier;
        $this->systemNotifier = $systemNotifier;
        $this->formOfTenderRepository = $formOfTenderRepository;
    }

    public function saveROTInformation(Tender $tender, array $inputs)
    {
        $logStatus = false;
        $user      = \Confide::user();

        $sentToVerify        = isset( $inputs['send_to_verify'] );
        $verificationReject  = isset( $inputs['verification_reject'] );
        $verificationConfirm = isset( $inputs['verification_confirm'] );
        $verifierRemark      = $inputs['verifier_remark'] ?? null;

        $object = $tender->recommendationOfTendererInformation ?: new TenderRecommendationOfTendererInformation();

        if( ( $tender->recommendationOfTendererInformation && $object->stillInProgress() ) OR $sentToVerify )
        {
            $this->syncSelectedVerifiers($object, $inputs);
        }

        // sync status when submitting
        if( $sentToVerify ) {
            $this->syncSelectedContractorCommitmentStatus($object, $inputs);
        }

        $hasVerifiers = ! $object->verifiers->isEmpty();

        if( ! $tender->recommendationOfTendererInformation OR $object->stillInProgress() )
        {
            $object->proposed_date_of_calling_tender                = $inputs['proposed_date_of_calling_tender'];
            $object->proposed_date_of_closing_tender                = $inputs['proposed_date_of_closing_tender'];
            $object->technical_tender_closing_date                  = $inputs['technical_tender_closing_date'] ?? $inputs['proposed_date_of_closing_tender'];
            $object->target_date_of_site_possession                 = $inputs['target_date_of_site_possession'];
            $object->budget                                         = $inputs['budget'];
            $object->consultant_estimates                           = empty( $inputs['consultant_estimates'] ) ? null : $inputs['consultant_estimates'];
            $object->completion_period                              = $inputs['completion_period'];
            $object->completion_period_metric                       = $inputs['completion_period_metric'];
            $object->project_incentive_percentage                   = empty( $inputs['project_incentive_percentage'] ) ? null : $inputs['project_incentive_percentage'];
            $object->allow_contractor_propose_own_completion_period = false;
            $object->disable_tender_rates_submission                = $inputs['disable_tender_rates_submission'] ?? false;
            $object->technical_evaluation_required                  = isset( $inputs['technical_evaluation_required'] ) ? $inputs['technical_evaluation_required'] : false;
            $object->contract_limit_id                              = null;
            $object->procurement_method_id                          = empty( $inputs['procurement_method_id'] ) ? null : $inputs['procurement_method_id'];
            $object->remarks                                        = $inputs['remarks'];

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
        
        $tenderAlternativeFive = TenderAlternative::where('form_of_tender_id', $tender->formOfTender->id)->where('tender_alternative_class_name', TenderAlternativeFive::class)->first();
        $tenderAlternativeFive->show = $object->allow_contractor_propose_own_completion_period;
        $tenderAlternativeFive->save();

        if( ! $tender->recommendationOfTendererInformation )
        {
            $object->status     = TenderRecommendationOfTendererInformation::IN_PROGRESS;
            $object->created_by = $user->id;
        }

        if( $sentToVerify )
        {
            $object->status     = TenderRecommendationOfTendererInformation::NEED_VALIDATION;
            $object->updated_by = $user->id;

            $logStatus = TenderRecommendationOfTendererInformation::NEED_VALIDATION;
        }

        if( $verificationReject )
        {
            $object->rejectVerification();
            $logStatus = TenderRecommendationOfTendererInformation::USER_VERIFICATION_REJECTED;
        }

        if( $verificationConfirm )
        {
            $this->setVerificationConfirmToCurrentVerifier($object);

            // if there is no in progress verifiers available then straight update the status to submission
            if( $object->verifiers->isEmpty() ) $object->status = TenderRecommendationOfTendererInformation::SUBMISSION;

            $logStatus = TenderRecommendationOfTendererInformation::USER_VERIFICATION_CONFIRMED;

            if( \PCK\Forum\ObjectThread::objectHasThread($object) )
            {
                $object->syncApprovalForumUsers();
            }
        }

        if( ( ! $hasVerifiers ) && $sentToVerify )
        {
            $object->status = TenderRecommendationOfTendererInformation::SUBMISSION;

            $logStatus = TenderRecommendationOfTendererInformation::USER_VERIFICATION_CONFIRMED;
        }

        // Syncs selected verifiers for the newly created record.
        $objectExists = $object->exists;

        $object = $tender->recommendationOfTendererInformation()->save($object);

        if( ! $objectExists ) $this->syncSelectedVerifiers($object, $inputs);

        $object->load('updatedBy');

        // Send requesting verification email.
        if( $sentToVerify ) {
            $this->emailNotifier->sendROTTenderVerificationEmail($tender, $object);
            $this->systemNotifier->sendTenderVerificationNotification($tender, $tender->recommendationOfTendererInformation, 'recommendation_of_tenderer', '#s1');
        }

        // Send verifier's decision email.
        if( $verificationReject OR $verificationConfirm )
        {
            $viewName = 'recommendation_of_tenderer_confirm';

            if( $verificationReject )
            {
                $viewName = 'recommendation_of_tenderer_reject';
            }
            else
            {
                $this->emailNotifier->sendROTTenderVerificationEmail($tender, $object);
                $this->systemNotifier->sendTenderVerificationNotification($tender, $tender->recommendationOfTendererInformation, 'recommendation_of_tenderer', '#s1');
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
            $this->sendTenderProcessApproveOrRejectNotification($tender->project, $tender, $projectEditors, \Confide::user(), $viewName, 'projects.tender.show', '#s1');
        }

        if( $logStatus )
        {
            $log                  = new TenderFormVerifierLog();
            $log->user_id         = $user->id;
            $log->type            = $logStatus;
            $log->verifier_remark = $verifierRemark;

            $object->verifierLogs()->save($log);
        }

        return $object;
    }

    private function getEditorsOfProject(Project $project) {
        $projectEditors = array();

        foreach($project->getProjectEditors() as $editor) {
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

    // sends notification to editors whenever a tender process advances to the next stage
    public function sendEmailNotificationToEditors(Tender $tender, $view) {
        $projectEditors = $this->getEditorsOfProject($tender->project);

        foreach($projectEditors as $projectEditor)
        {
            $this->emailNotifier->sendEmailNotificationToEditor($tender, $view, $projectEditor);
        }
    }

    public function syncSelectedContractorCommitmentStatus(TenderRecommendationOfTendererInformation $rotInformation, array $inputs)
    {
        if( ! isset( $inputs['status'] ) ) return;

        foreach($inputs['status'] as $companyId => $status)
        {
            $data = array( 'status' => $status );

            $result = \DB::table('company_tender_rot_information')
                ->select('id', 'status')
                ->where('tender_rot_information_id', '=', $rotInformation->id)
                ->where('company_id', '=', $companyId)
                ->first();

            $rotInformation->selectedContractors()->updateExistingPivot($companyId, $data);

            if($data['status'] == $result->status) continue;
            
            $this->updateContractorsCommitmentStatusLog($result->id, $status);
        }
    }

    public function syncSelectedCompanyForROTInformation(TenderRecommendationOfTendererInformation $rotInformation, array $inputs)
    {
        $contractorIds = array();

        if( isset( $inputs['contractors'] ) )
        {
            foreach($inputs['contractors'] as $contractorId)
            {
                $contractorIds[] = $contractorId;
            }
        }

        $rotInformation->selectedContractors()->sync($contractorIds);
    }

    public function syncSelectedVerifiers(TenderRecommendationOfTendererInformation $rotInformation, array $inputs)
    {
        $data = array();

        foreach($inputs['verifiers'] ?? array() as $verifier)
        {
            if( $verifier <= 0 ) continue;

            $data[] = $verifier;
        }

        $rotInformation->verifiers()->sync(array());
        $rotInformation->verifiers()->sync($data);

        // reload the relation, in order not keep cache copy
        $rotInformation->load('verifiers');

        if( \PCK\Forum\ObjectThread::objectHasThread($rotInformation) )
        {
            $rotInformation->syncApprovalForumUsers();
        }
    }

    public function deleteROTContractor(Tender $tender, $contractorId)
    {
        $tender->recommendationOfTendererInformation->selectedContractors()->detach($contractorId);
    }

    private function setRejectedStatusToAllVerifiers(TenderRecommendationOfTendererInformation $rotInformation)
    {
        $statuses = array(
            TenderRecommendationOfTendererInformation::USER_VERIFICATION_IN_PROGRESS,
            TenderRecommendationOfTendererInformation::USER_VERIFICATION_CONFIRMED
        );

        \DB::table('tender_rot_information_user')
            ->where('tender_rot_information_id', '=', $rotInformation->id)
            ->whereIn('status', $statuses)
            ->update(array( 'status' => TenderRecommendationOfTendererInformation::USER_VERIFICATION_REJECTED ));

        $rotInformation->load('verifiers');
    }

    private function setVerificationConfirmToCurrentVerifier(TenderRecommendationOfTendererInformation $object)
    {
        $user = \Confide::user();

        $object->verifiers()->updateExistingPivot($user->id, array(
            'status' => TenderRecommendationOfTendererInformation::USER_VERIFICATION_CONFIRMED
        ));

        $object->load('verifiers');
    }

    public function sendEmailNotificationToSelectedContractors(array $allCompanyAdmins, $projectId, $tenderId, $emailDetails) {
        $tenderStage = TenderStages::TENDER_STAGE_RECOMMENDATION_OF_TENDERER;
        $tenderROTInformation = TenderRecommendationOfTendererInformation::where('tender_id', '=', $tenderId)->first();
        $project = Project::find($projectId);

        foreach($allCompanyAdmins as $admin) {
            $token = Key::createUniqueKeyAcrossTables(array(
                'expression_of_interest_tokens' => 'token'
            ));

            $subject = '[' . $project->reference . '] ' . trans('tenders.expressionOfInterest', [], 'messages', $admin->settings->language->code);
            
            $this->saveEntryIntoExpressionOfInterestTable($tenderROTInformation, $admin, $token);
            $this->emailNotifier->sendStatusConfirmationEmailToSelectedContractor($project, $tenderStage, $tenderROTInformation, $admin, $emailDetails, $subject, $token);
        }
    }

    private function saveEntryIntoExpressionOfInterestTable(TenderRecommendationOfTendererInformation $tenderROTInformation, User $admin, $token) {
        $expressionOfInterestTokens                         = new ExpressionOfInterestTokens();
        $expressionOfInterestTokens->tenderstageable_id     = $tenderROTInformation->id;
        $expressionOfInterestTokens->tenderstageable_type   = TenderRecommendationOfTendererInformation::class;
        $expressionOfInterestTokens->user_id                = $admin->id;
        $expressionOfInterestTokens->company_id             = $admin->company_id;
        $expressionOfInterestTokens->token                  = $token;
        $expressionOfInterestTokens->save();
    }

    public function updateCompanyTenderROTInfoConfirmationStatusUnauthenticated($expressionOfInterest, $input, $token = null)
    {
        $commitmentStatusId = $input['option'];

        $result = \DB::table('company_tender_rot_information')
            ->select('id')
            ->where('tender_rot_information_id', $expressionOfInterest->tenderstageable_id)
            ->where('company_id', $expressionOfInterest->company_id)
            ->first();
        
        $company_tender_rot_information_id = $result->id;

        $result = \DB::table('company_tender_rot_information')
                    ->where('tender_rot_information_id', $expressionOfInterest->tenderstageable_id)
                    ->where('company_id', $expressionOfInterest->company_id)
                    ->update(array(
                        'status' => $commitmentStatusId
                    ));

        if( $result )
        {
            $log = $this->updateContractorsCommitmentStatusLog($company_tender_rot_information_id, $commitmentStatusId, $input, $token);

            //send email to business unit
            $companyTenderRecommendationOfTendererInformation = CompanyTenderRecommendationOfTendererInformation::find($company_tender_rot_information_id);
            $projectId                                        = $companyTenderRecommendationOfTendererInformation->tenderROTInformation->tender->project->id;
            $replyDetails                                     = array();
            $replyDetails['companyId']                        = $companyTenderRecommendationOfTendererInformation->company_id;
            $replyDetails['status']                           = $log->status;
            $replyDetails['replied_at_time']                  = $log->created_at;

            $this->sendEmailNotificationForStatusConfirmationReply($projectId, $replyDetails);
        }

        return $result;
    }

    public function updateContractorsCommitmentStatusLog($company_tender_rot_information_id, $commitmentStatusId, $input = array(), $token = null)
    {
        $company_tenderROTInfo = CompanyTenderRecommendationOfTendererInformation::find($company_tender_rot_information_id);
        
        if( ! $user = \Confide::user() ) {
            $expressionOfInterestToken = ExpressionOfInterestTokens::where('token', $token)->first();
            $user = User::find($expressionOfInterestToken->user_id);
        }
        
        return $company_tenderROTInfo->contractorsCommitmentStatusLog()
            ->create(array(
                "user_id" => $user->id,
                "status"  => $commitmentStatusId,
                "remarks" => $input['remarks'] ?? null,
            ));
    }

    public function getContractorsCommitmentStatusLog($tenderId, $companyId)
    {
        $record = \DB::table('company_tender_rot_information')
            ->join('tender_rot_information', 'company_tender_rot_information.tender_rot_information_id', '=', 'tender_rot_information.id')
            ->select('company_tender_rot_information.id')
            ->where('company_tender_rot_information.company_id', '=', $companyId)
            ->where('tender_rot_information.tender_id', '=', $tenderId)
            ->first();

        $company_tenderROTInformation = CompanyTenderRecommendationOfTendererInformation::find($record->id);

        $log = array();

        foreach($company_tenderROTInformation->contractorsCommitmentStatusLog as $logEntry)
        {
            $user   = User::find($logEntry->user_id);
            $status = ContractorCommitmentStatus::getText($logEntry->status);

            array_push($log, array( 'user' => $user->name, 'userEmail' => $user->email, 'status' => $status, 'date' => $logEntry->created_at, 'remarks' => $logEntry->remarks ));
        }

        return $log;
    }

    public function getPendingRecOfTenderersByUser(User $user, $includeFutureTasks, Project $project = null)
    {       
        $listOfTendersROTInfo = [];
        $proceed = false;

        if($project)
        {
            if($project->latestTender->recommendationOfTendererInformation)
            {
                $recOfTenderer = $project->latestTender->recommendationOfTendererInformation;

                if($recOfTenderer && $recOfTenderer->isBeingValidated())
                {
                    $proceed = ($includeFutureTasks) ? in_array($user->id, $recOfTenderer->verifiers->lists('id')) : ($recOfTenderer->latestVerifier->first() && ($user->id === $recOfTenderer->latestVerifier->first()->id));

                    if($proceed)
                    {
                        array_push($listOfTendersROTInfo, [
                            'project_reference'        => $project->reference,
                            'parent_project_reference' => $project->isSubProject() ? $project->parentProject->reference : null,
                            'project_id'               => $project->id,
                            'parent_project_id'        => $project->isSubProject() ? $project->parentProject->id : null,
                            'company_id'               => $project->business_unit_id,
                            'project_title'            => $project->title,
                            'parent_project_title'     => $project->isSubProject() ? $project->parentProject->title : null,
                            'module'                   => TenderRecommendationOfTendererInformation::RECOMMENDATION_OF_TENDERER_MODULE_NAME,
                            'days_pending'             => TenderRecommendationOfTendererInformationUser::getDaysPending($recOfTenderer, $user),
                            'tender_id'                => $project->latestTender->id,
                            'is_future_task'           => !($recOfTenderer->latestVerifier->first() && ($user->id === $recOfTenderer->latestVerifier->first()->id)),
                            'route'                    => route('projects.tender.show', array('projectId' => $project->id, 'tenderId' => $project->latestTender->id)) . '#s1'
                        ]);
                    }
                }
            }
        }
        else
        {
            $listOfVerifiers = DB::table('tender_rot_information_user')
            ->join('tender_rot_information', 'tender_rot_information_user.tender_rot_information_id', '=', 'tender_rot_information.id')
            ->join('tenders', 'tender_rot_information.tender_id' , '=', 'tenders.id')
            ->join('projects', 'tenders.project_id', '=', 'projects.id')
            ->select('tender_rot_information_user.id', 'tender_rot_information_user.tender_rot_information_id', 'tender_rot_information_user.user_id')
            ->where('tender_rot_information_user.status', FormLevelStatus::USER_VERIFICATION_IN_PROGRESS)
            ->where('tender_rot_information_user.user_id', $user->id)
            ->whereNull('projects.deleted_at')
            ->get();

            foreach($listOfVerifiers as $verifier)
            {
                $recOfTenderer = TenderRecommendationOfTendererInformation::find($verifier->tender_rot_information_id);

                if($recOfTenderer && $recOfTenderer->isBeingValidated())
                {
                    $proceed = ($includeFutureTasks) ? in_array($user->id, $recOfTenderer->verifiers->lists('id')) : ($recOfTenderer->latestVerifier && ($user->id === $recOfTenderer->latestVerifier->first()->id));

                    if($proceed)
                    {
                        $project      = $recOfTenderer->tender->project;
                        $partialRoute = (is_null($user->getAssignedCompany($project)) && $user->isTopManagementVerifier()) ? 'topManagementVerifiers.projects.tender.show' : 'projects.tender.show';

                        array_push($listOfTendersROTInfo, [
                            'project_reference'        => $project->reference,
                            'parent_project_reference' => $project->isSubProject() ? $project->parentProject->reference : null,
                            'project_id'               => $project->id,
                            'parent_project_id'        => $project->isSubProject() ? $project->parentProject->id : null,
                            'company_id'               => $project->business_unit_id,
                            'project_title'            => $project->title,
                            'parent_project_title'     => $project->isSubProject() ? $project->parentProject->title : null,
                            'module'                   => TenderRecommendationOfTendererInformation::RECOMMENDATION_OF_TENDERER_MODULE_NAME,
                            'days_pending'             => TenderRecommendationOfTendererInformationUser::getDaysPending($recOfTenderer, $user),
                            'tender_id'                => $recOfTenderer->tender->id,
                            'is_future_task'           => !(($recOfTenderer->latestVerifier && ($user->id === $recOfTenderer->latestVerifier->first()->id))),
                            'route'                    => route($partialRoute, array('projectId' => $project->id, 'tenderId' => $recOfTenderer->tender->id)) . '#s1'
                        ]);
                    }
                }
            }
        }

        return $listOfTendersROTInfo;
    }
}