<?php namespace PCK\Notifications;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Mail\Mailer;
use PCK\Buildspace\CostData;
use PCK\Buildspace\PostContractClaimRevision;
use PCK\Companies\Company;
use PCK\Conversations\Conversation;
use PCK\ContractGroupProjectUsers\ContractGroupProjectUser;
use PCK\ContractGroups\ContractGroup;
use PCK\ContractGroups\Types\Role;
use PCK\ContractualClaim\ContractualClaimInterface;
use PCK\DigitalStar\Evaluation\DsEvaluationForm;
use PCK\DigitalStar\Evaluation\DsEvaluationFormUserRole;
use PCK\DigitalStar\Evaluation\DsRole;
use PCK\EmailNotificationSettings\EmailNotificationSetting;
use PCK\Filters\TenderFilters;
use PCK\Forum\Post;
use PCK\Helpers\Mailer as MailHelper;
use PCK\Inspections\Inspection;
use PCK\LetterOfAward\LetterOfAward;
use PCK\LetterOfAward\LetterOfAwardClauseCommentRepository;
use PCK\ProjectReport\ProjectReport;
use PCK\ProjectReport\ProjectReportNotification;
use PCK\ProjectReport\ProjectReportNotificationRepository;
use PCK\ProjectReport\ProjectReportUserPermission;
use PCK\Projects\Project;
use PCK\TenderCallingTenderInformation\TenderCallingTenderInformation;
use PCK\TenderDocumentFolders\TenderDocumentFolder;
use PCK\TenderInterviews\TenderInterview;
use PCK\TenderInterviews\TenderInterviewInformation;
use PCK\TenderListOfTendererInformation\TenderListOfTendererInformation;
use PCK\TenderRecommendationOfTendererInformation\ContractorCommitmentStatus;
use PCK\TenderRecommendationOfTendererInformation\TenderRecommendationOfTendererInformation;
use PCK\Tenders\Tender;
use PCK\Tenders\TenderStages;
use PCK\Users\User;
use PCK\Users\UserRepository;
use PCK\VendorManagement\VendorManagementUserPermission;
use PCK\VendorPerformanceEvaluation\RemovalRequest;
use PCK\VendorPerformanceEvaluation\VendorPerformanceEvaluation;
use PCK\VendorPerformanceEvaluation\VendorPerformanceEvaluationCompanyForm;
use PCK\VendorPerformanceEvaluation\VendorPerformanceEvaluationSetup;
use PCK\VendorRegistration\VendorRegistration;
use PCK\Verifier\Verifiable;

class EmailNotifier implements Notification {

    private $mailer;
    private $letterOfAwardClauseCommentRepository;
    private $projectReportNotificationRepository;
    private $userRepo;

    /**
     * @deprecated Make up your own names!
     */
    protected function getEmailSubjectMessage($model)
    {
        $subject = trans('email.eProjectNotification');

        if( $model instanceof ContractualClaimInterface ) $subject = trans('email.eClaimNotification');
        if( $model instanceof TenderDocumentFolder ) $subject = trans('email.eTenderNotification');
        if( $model instanceof Tender ) $subject = trans('email.eTenderNotification');
        if( $model instanceof Conversation ) $subject = trans('email.eProjectNotification');

        return $subject;
    }

    /**
     * @deprecated Make up your own names!
     */
    public static function generateEmailSubject(Project $project, $emailSubject)
    {
        return "[{$project->reference}] {$emailSubject}";
    }

    public function __construct(
        Mailer $mailer,
        LetterOfAwardClauseCommentRepository $letterOfAwardClauseCommentRepository,
        ProjectReportNotificationRepository $projectReportNotificationRepository,
        UserRepository $userRepo
    ) {
        $this->mailer                               = $mailer;
        $this->letterOfAwardClauseCommentRepository = $letterOfAwardClauseCommentRepository;
        $this->projectReportNotificationRepository  = $projectReportNotificationRepository;
        $this->userRepo                             = $userRepo;
    }

    public function sendCallingTenderSubmittedNotification(Tender $tender)
    {
        $sender = $this->getSenderInformation();
        $users  = $this->getGroupOwnerRecipientDetails($tender->project, array( Role::PROJECT_OWNER, TenderFilters::getListOfTendererFormRole($tender->project) ));

        $assignedCompany = is_null($sender->getAssignedCompany($tender->project)) ? $sender->company : $sender->getAssignedCompany($tender->project);

        $data['senderName']        = "{$sender->name} ({$assignedCompany->name})";
        $data['toRoute']           = route('projects.tender.show', array( $tender->project->id, $tender->id )) . '#s3';
        $data['workCategory']      = $tender->project->workCategory->name;
        $data['projectTitle']      = Project::find($tender->project->id)->title;
        $data['tenderStartDate']   = $tender->project->getProjectTimeZoneTime($tender->callingTenderInformation->date_of_calling_tender);
        $data['tenderClosingDate'] = $tender->project->getProjectTimeZoneTime($tender->callingTenderInformation->date_of_closing_tender);

        if( $tender->listOfTendererInformation->technical_evaluation_required )
        {
            $data['technicalTenderClosingDate'] = $tender->project->getProjectTimeZoneTime($tender->callingTenderInformation->technical_tender_closing_date ?? null);
        }

        foreach($users as $user)
        {
            $user = $this->userRepo->find($user['id']);

            if( ! $this->sendToUser($tender->project, $user) ) continue;

            $recipientLocale = $user->settings->language->code;
            $subject = trans('email.eTenderNotification', [], 'messages', $recipientLocale);

            $data['recipientLocale']   = $recipientLocale;
            $data['currentTenderName'] = $tender->getCurrentTenderNameByLocale($recipientLocale);

            $mailer = new MailHelper(self::generateEmailSubject($tender->project, $subject), "notifications.email.calling_tender_submitted", $data);
            $mailer->setRecipients(array($user));
            $mailer->send();
        }
    }

    public function sendNotification(Project $project, Model $model, array $roles, $viewName, $routeName, $tabId = null)
    {
        $sender = $this->getSenderInformation();
        $users  = $this->getGroupOwnerRecipientDetails($project, $roles);

        $data['senderName'] = "{$sender->name} ({$sender->getAssignedCompany($project)->name})";
        $data['toRoute']    = route($routeName, array( $project->id, $model->id ));

        // Re-query to prevent serialising errors
        // from unnecessary relations or trailing data.
        $data['project']      = Project::find($project->id);
        $data['workCategory'] = $project->workCategory->name;
        $model                = $model::find($model->id);

        $data['subject'] = self::generateEmailSubject($project, $model);

        $modelClassName = get_class($model);
        $data['model']  = $modelClassName::find($model->id);

        if( $tabId )
        {
            $data['toRoute'] .= $tabId;
        }

        foreach($users as $user)
        {
            $user = $this->userRepo->find($user['id']);

            if( ! $this->sendToUser($project, $user) ) continue;

            $recipientLocale = $user->settings->language->code;
            $subject = trans('email.eTenderNotification', [], 'messages', $recipientLocale);

            $data['recipientName']   = $user->name;
            $data['recipientLocale'] = $recipientLocale;

            MailHelper::queue(null, "notifications.email.{$viewName}", User::find($user['id']), self::generateEmailSubject($project, $subject), $data);
        }
    }

    public function sendNotificationByUsers(Project $project, Model $model, array $users, $viewName, $routeName, $tabId = null)
    {
        $sender = $this->getSenderInformation();

        // Re-query to prevent serialising errors
        // from unnecessary relations or trailing data.
        $project = Project::find($project->id);
        $model   = $model::find($model->id);

        $data['senderName']    = $sender->name;
        $data['toRoute']       = route($routeName, array( $project->id, $model->id ));
        $data['project_title'] = $project->title;
        $data['project']       = $project;
        $data['model']         = $model;

        if( $tabId )
        {
            $data['toRoute'] .= $tabId;
        }

        foreach($users as $user)
        {
            if( ! $this->sendToUser($project, $this->userRepo->find($user['id'])) ) continue;

            $data['recipientName'] = $user['name'];
            $data['company_name']  = isset( $user['company_name'] ) ? $user['company_name'] : null;

            MailHelper::queue(null, "notifications.email.{$viewName}", User::find($user['id']), self::generateEmailSubject($project, $this->getEmailSubjectMessage($model)), $data);
        }
    }

    public function sendTenderClosedNotification(Project $project, Tender $tender, array $users)
    {
        $data['projectName']       = $project->title;
        $data['tenderStartDate']   = $project->getProjectTimeZoneTime($tender['tender_starting_date']);
        $data['tenderClosingDate'] = $project->getProjectTimeZoneTime($tender['tender_closing_date']);
        $data['workCategory']      = $project->workCategory->name;

        if( $tender->listOfTendererInformation->technical_evaluation_required )
        {
            $data['technicalSubmissionClosingDate'] = $tender->project->getProjectTimeZoneTime($tender->callingTenderInformation->technical_tender_closing_date ?? null);
        }

        foreach($users as $user)
        {
            if( ! $this->sendToUser($project, $user) ) continue;

            $recipientLocale = $user->settings->language->code;
            $subject = trans('email.eTenderNotification', [], 'messages', $recipientLocale);

            $data['recipientName']   = $user->name;
            $data['tenderName'] = $tender->getCurrentTenderNameByLocale($recipientLocale);

            MailHelper::queue(null, "notifications.email.tender.tender_closed", $user, self::generateEmailSubject($project, $subject), $data);
        }
    }

    public function sendTenderVerifierNotification(Project $project, Tender $tender, array $users, $sendByUserId, $viewName, $routeName, $tabId = null)
    {
        $sender = $this->getSenderInformation($sendByUserId);

        $data['senderName']                 = $sender->name;
        $data['projectName']                = $project->title;
        $data['toRoute']                    = route($routeName, array( $project->id, $tender->id ));
        $data['tenderStartDate']            = $project->getProjectTimeZoneTime($tender['tender_starting_date']);
        $data['tenderClosingDate']          = $project->getProjectTimeZoneTime($tender['tender_closing_date']);
        $data['workCategory']               = $project->workCategory->name;

        if( $tender->listOfTendererInformation->technical_evaluation_required )
        {
            $data['technicalTenderClosingDate'] = $tender->project->getProjectTimeZoneTime($tender->callingTenderInformation->technical_tender_closing_date ?? null);
        }

        if( $tabId )
        {
            $data['toRoute'] .= $tabId;
        }

        foreach($users as $user)
        {
            $user = $this->userRepo->find($user['id']);

            if( ! $this->sendToUser($project, $user) ) continue;

            $recipientLocale = $user->settings->language->code;
            $subject = trans('email.eTenderNotification', [], 'messages', $recipientLocale);

            $data['recipientName']   = $user->name;
            $data['recipientLocale'] = $recipientLocale;
            $data['tenderName'] = $tender->getCurrentTenderNameByLocale($recipientLocale);

            MailHelper::queue(null, "notifications.email.tender.{$viewName}", User::find($user['id']), self::generateEmailSubject($project, $subject), $data);
        }
    }

    public function getGroupOwnerRecipientDetails(Project $project, array $roles)
    {
        return $this->userRepo->getProjectGroupOwnersByProjectAndRoles($project, $roles);
    }

    public function getSenderInformation($sendByUserId = null)
    {
        if( $sendByUserId )
        {
            return User::findOrFail($sendByUserId);
        }

        return \Confide::user();
    }

    public function sendNotificationForConfirmationStatus($projectId, $recipientIdAndKeys, $tenderId, $tenderStage, $emailDetails)
    {
        $project = Project::find($projectId);

        $data['projectTitle'] = $project->title;
        $data['emailMessage'] = $emailDetails['emailMessage'];
        $data['employerName'] = $emailDetails['employerName'] ? $emailDetails['employerName'] : \Confide::user()->company->name;
        $data['tenderStage']  = $tenderStage;

        switch($tenderStage)
        {
            case TenderStages::TENDER_STAGE_CALLING_TENDER:
                $emailSubject              = trans('tenders.tenderInvitation');
                $information               = TenderCallingTenderInformation::where('tender_id', '=', $tenderId)->first();
                $data['tenderCallingDate'] = $information->date_of_calling_tender;
                $data['tenderClosingDate'] = $information->date_of_closing_tender;
                break;
            default:
                $emailSubject              = trans('tenders.expressionOfInterest');
                $information               = TenderRecommendationOfTendererInformation::where('tender_id', '=', $tenderId)->first();
                $data['tenderCallingDate'] = $information->proposed_date_of_calling_tender;
                $data['tenderClosingDate'] = $information->proposed_date_of_closing_tender;
        }

        $data['tenderCallingDate'] = Carbon::parse($project->getProjectTimeZoneTime($data['tenderCallingDate']))->format(\Config::get('dates.full_format'));
        $data['tenderClosingDate'] = Carbon::parse($project->getProjectTimeZoneTime($data['tenderClosingDate']))->format(\Config::get('dates.full_format'));

        foreach($recipientIdAndKeys as $recipientId => $key)
        {
            $recipientUser = User::find($recipientId);

            if( ! $this->sendToUser($project, $recipientUser) ) continue;

            $data['link']          = route('contractors.confirmStatus', array( $key ));
            $data['recipientName'] = $recipientUser['name'];
            $data['companyName']   = $recipientUser->company->name;

            MailHelper::queue(null, 'notifications.email.tender_confirm_commitment_status', $recipientUser, self::generateEmailSubject($project, $emailSubject), $data);
        }

        // Send copy to self
        if( $emailDetails['sendCopyToSelf'] )
        {
            $recipientUser = \Confide::user();

            if( $this->sendToUser($project, $recipientUser) )
            {
                $data['link']          = trans('email.uniqueLinkPlaceholder');
                $data['recipientName'] = trans('email.recipientNamePlaceholder');
                $data['companyName']   = trans('email.companyNamePlaceholder');

                MailHelper::queue(null, 'notifications.email.tender_confirm_commitment_status', $recipientUser, self::generateEmailSubject($project, $emailSubject), $data);
            }

        }
    }

    public function sendNotificationForConfirmationStatusReply($projectId, $replyDetails)
    {
        $project = Project::find($projectId);

        $data['projectTitle']  = $project->title;
        $data['companyName']   = Company::find($replyDetails['companyId'])->name;
        $data['repliedAtTime'] = Carbon::parse($project->getProjectTimeZoneTime($replyDetails['replied_at_time']))->format(\Config::get('dates.full_format'));
        $data['loggedInUser']  = \Confide::user();
        
        // Send to all Business Unit, GCD and Group Access to Tender Document editors.
        $roles          = array_unique(Tender::rolesAllowedToUseModule($project));
        $contractGroups = ContractGroup::whereIn('group', $roles)->get();

        $contractGroupProjectUsers = ContractGroupProjectUser::where('project_id', '=', $projectId)
            ->whereIn('contract_group_id', $contractGroups->lists('id'))
            ->where('is_contract_group_project_owner', '=', true)
            ->get();

        foreach($contractGroupProjectUsers as $contractGroupProjectUser)
        {
            $recipient = User::find($contractGroupProjectUser->user_id);
            $recipientLocale = $recipient->settings->language->code;

            if( ! $this->sendToUser($project, $recipient) ) continue;

            $data['recipientName']   = $recipient->name;
            $data['recipientLocale'] = $recipientLocale;
            $data['status']          = ContractorCommitmentStatus::getText($replyDetails['status'], $recipientLocale);

            MailHelper::queue(null, 'notifications.email.tender_confirm_commitment_status_reply', $recipient, self::generateEmailSubject($project, trans('email.tenderInvitationReply')), $data);
        }
    }

    public function sendTenderInterviewRequest(Project $project, $selectedCompanies, $interviews)
    {
        $data['projectTitle'] = $project->title;

        foreach($selectedCompanies as $company)
        {
            $company = Company::find($company->id);

            foreach($company->companyAdmin()->get() as $recipient)
            {
                if( ! $this->sendToUser($project, $recipient) ) continue;

                $recipientLocale = $recipient->settings->language->code;
                $subject         = trans('email.tenderInterview', [], 'messages', $recipientLocale);

                $data['recipientName']   = $recipient->name;
                $data['recipientLocale'] = $recipientLocale;
                $data['companyName']     = $company->name;

                $data['date']  = Carbon::parse($project->getProjectTimeZoneTime($interviews[ $company->id ]->date_and_time))->format(\Config::get('dates.full_format'));
                $data['time']  = Carbon::parse($project->getProjectTimeZoneTime($interviews[ $company->id ]->date_and_time))->format(\Config::get('dates.time_only'));
                $data['venue'] = $interviews[ $company->id ]->venue;
                $data['link']  = route('tender_interview.request', array( $interviews[ $company->id ]->key ));

                MailHelper::queue(null, 'notifications.email.tender_interview_request', $recipient, self::generateEmailSubject($project, $subject), $data);
            }
        }
    }

    public function sendTenderMeetingRequest($project, $tenderId, $recipientIds)
    {
        $data['projectTitle'] = $project->title;

        $tender                     = Tender::find($tenderId);
        $tenderInterviewInformation = TenderInterviewInformation::where('tender_id', '=', $tenderId)->first();
        $data['discussionTime']     = Carbon::parse($project->getProjectTimeZoneTime($tenderInterviewInformation->date_and_time))->format(\Config::get('dates.time_only'));

        $data['tenderInterviews'] = $tender->tenderInterviewInfo->getCompanyInterviews();

        foreach($data['tenderInterviews'] as $key => $interview)
        {
            $data['tenderInterviews'][ $key ]->company         = $interview->company;
            $data['tenderInterviews'][ $key ]['date_and_time'] = $project->getProjectTimeZoneTime($interview['date_and_time'])->format(\Config::get('dates.time_only'));
        }

        $data['date'] = Carbon::parse($project->getProjectTimeZoneTime($tenderInterviewInformation->date_and_time))->format(\Config::get('dates.standard_spaced_date_and_day'));

        $firstInterview = $data['tenderInterviews']->first();
        $data['venue']  = ( $firstInterview ) ? $firstInterview->venue : null;

        foreach($recipientIds as $recipientId)
        {
            $recipient = User::find($recipientId);
            $recipientLocale = $recipient->settings->language->code;
            $subject   = trans('email.tenderClarificationMeeting', [], 'messages', $recipientLocale);

            if( ! $this->sendToUser($project, $recipient) ) continue;

            $data['recipientName']   = $recipient->name;
            $data['companyName']     = $recipient->company->name;
            $data['recipientLocale'] = $recipientLocale;

            MailHelper::queue(null, 'notifications.email.tender_interview_request_for_meeting', $recipient, self::generateEmailSubject($project, $subject), $data);
        }
    }

    public function notifyEditorsOnInterviewReply($project, $interview, $recipientIds)
    {
        $data['projectTitle']    = $project->title;
        $data['interview']       = $interview;
        $data['tendererCompany'] = $interview->company->name;
        $data['status']          = TenderInterview::getText($interview->status);

        //change to use logs when logs are implemented
        $data['repliedAt'] = Carbon::parse($project->getProjectTimeZoneTime($interview->updated_at))->format(\Config::get('dates.created_and_updated_at_formatting'));

        foreach($recipientIds as $recipientId)
        {
            $recipient = User::find($recipientId);
            if( ! $this->sendToUser($project, $recipient) ) continue;
            $data['recipientName'] = $recipient->name;
            $data['companyName']   = $recipient->company->name;

            MailHelper::queue(null, 'notifications.email.tender_interview_reply_notification', $recipient, self::generateEmailSubject($project, trans('email.tenderInterview')), $data);
        }
    }

    /**
     * General function to send notifications
     * with variable parameters.
     *
     * @param array        $recipients
     * @param              $subject
     * @param              $viewName
     * @param              $route
     * @param array        $viewData
     * @param Project|null $project
     * @param null         $sender
     */
    public function notify(array $recipients, $subject, $viewName, $route, $viewData = array(), Project $project = null, $sender = null)
    {
        if( ! $sender ) $sender = \Confide::user();

        $viewData['toRoute'] = $route;
        $data['senderName']  = "{$sender->name}";
        if( $project ) $data['senderName'] .= " ({$sender->getAssignedCompany($project)->name})";

        foreach($recipients as $user)
        {
            $viewData['recipientName'] = $user['name'];

            MailHelper::queue(null, "{$viewName}", User::find($user['id']), $subject, $viewData);
        }
    }

    /**
     * @deprecated Do not use a generic solution. View data should be specific to avoid tight coupling.
     */
    public function sendNotificationGeneric(
        Project $project,
        Model $model,
        array $users,
        $viewName,
        $toRoute,
        $additionalData = array()
    )
    {
        // Re-query to prevent serialising errors
        // from unnecessary relations or trailing data.
        $project = Project::find($project->id);
        $model   = $model::find($model->id);

        $data['toRoute']        = $toRoute;
        $data['project_title']  = $project->title;
        $data['project']        = $project;
        $data['model']          = $model;
        $data['additionalData'] = $additionalData;
        $data['workCategory']   = $project->workCategory->name;

        foreach($users as $user)
        {
            $user = $this->userRepo->find($user['id']);

            if( ! $this->sendToUser($project, $user) ) continue;

            $recipientLocale = $user->settings->language->code;
            $subject = trans('email.eTenderNotification', [], 'messages', $recipientLocale);

            $data['recipientName']   = $user->name;
            $data['recipientLocale'] = $recipientLocale;
            $data['modelName']       = $model->getCurrentTenderNameByLocale($recipientLocale);

            MailHelper::queue(null, "{$viewName}", $user, self::generateEmailSubject($project, $subject), $data);
        }
    }

    /**
     * Provides a check to see if mail should be sent to the user.
     *
     * @param Project $project
     * @param User    $user
     *
     * @return bool
     */
    private function sendToUser(Project $project, User $user)
    {
        if( ( ! $project->contractor_access_enabled ) && $user->hasCompanyProjectRole($project, Role::CONTRACTOR) ) return false;

        return true;
    }

    public function sendTenderProcessApproveOrRejectEmail(Tender $tender, $subject, $view, $recipient)
    {
        $viewData = array(
            'senderName'        => \Confide::user()->name,
            'recipientName'     => $recipient->name,
            'recipientLocale'   => $recipient->settings->language->code,
            'projectName'       => $tender->project->title,
            'tenderName'        => $tender->current_tender_name,
            'workCategory'      => $tender->project->workCategory->name,
        );

        if($tender->callingTenderInformation)
        {
            $viewData['tenderStartDate']   = $tender->project->getProjectTimeZoneTime($tender->callingTenderInformation->date_of_calling_tender);
            $viewData['tenderClosingDate'] = $tender->project->getProjectTimeZoneTime($tender->callingTenderInformation->date_of_closing_tender);
        }

        if( $tender->listOfTendererInformation && $tender->listOfTendererInformation->technical_evaluation_required )
        {
            $viewData['technicalTenderClosingDate'] = $tender->project->getProjectTimeZoneTime($tender->callingTenderInformation->technical_tender_closing_date ?? null);
        }

        $mailer = new MailHelper($subject, $view, $viewData);
        $mailer->setRecipients(array( $recipient ));
        $mailer->setPrivacy(true);
        $mailer->send();
    }

    // sends email notifications to verifier one by one for ROT
    public function sendROTTenderVerificationEmail(Tender $tender, TenderRecommendationOfTendererInformation $rotInfo)
    {
        if( ! $rotInfo->latestVerifier->first() ) return;

        $latestVerifier  = $rotInfo->latestVerifier->first();
        $recipientLocale = $latestVerifier->settings->language->code;
        $view            = "notifications.email.tender.recommendation_of_tenderer";
        $emailSubject    = trans('email/recommendationOfTenderer.subject', ['projectReference' => $tender->project->reference], 'messages', $recipientLocale);

        $route = (is_null($latestVerifier->getAssignedCompany($tender->project)) && $latestVerifier->isTopManagementVerifier()) ? 'topManagementVerifiers.projects.tender.show' : 'projects.tender.show';

        $viewData        = array(
            'senderName'        => $rotInfo->updatedBy->name,
            'recipientName'     => $latestVerifier->name,
            'recipientLocale'   => $recipientLocale,
            'projectName'       => $tender->project->title,
            'tenderName'        => $tender->getCurrentTenderNameByLocale($recipientLocale),
            'toRoute'           => route($route, array( $tender->project->id, $tender->id )) . '#s1',
            'tenderStartDate'   => $tender->project->getProjectTimeZoneTime($tender['tender_starting_date']),
            'tenderClosingDate' => $tender->project->getProjectTimeZoneTime($tender['tender_closing_date']),
            'workCategory'      => $tender->project->workCategory->name,
        );

        $mailer = new MailHelper($emailSubject, $view, $viewData);
        $mailer->setRecipients(array( $latestVerifier ));
        $mailer->send();
    }

    // sends email notifications to verifier one by one for LOT
    public function sendLOTTenderVerificationEmail(Tender $tender, TenderListOfTendererInformation $lotInfo)
    {
        if( ! $lotInfo->latestVerifier->first() ) return;

        $latestVerifier = $lotInfo->latestVerifier->first();
        $recipientLocale = $latestVerifier->settings->language->code;
        $view           = "notifications.email.tender.list_of_tenderer";
        $emailSubject    = trans('email/listOfTenderer.subject', ['projectReference' => $tender->project->reference], 'messages', $recipientLocale);

        $route = (is_null($latestVerifier->getAssignedCompany($tender->project)) && $latestVerifier->isTopManagementVerifier()) ? 'topManagementVerifiers.projects.tender.show' : 'projects.tender.show';

        $viewData = array(
            'senderName'        => $lotInfo->updatedBy->name,
            'recipientName'     => $latestVerifier->name,
            'recipientLocale'   => $recipientLocale,
            'projectName'       => $tender->project->title,
            'tenderName'        => $tender->getCurrentTenderNameByLocale($recipientLocale),
            'toRoute'           => route($route, array( $tender->project->id, $tender->id )) . '#s2',
            'tenderStartDate'   => $tender->project->getProjectTimeZoneTime($tender['tender_starting_date']),
            'tenderClosingDate' => $tender->project->getProjectTimeZoneTime($tender['tender_closing_date']),
            'workCategory'      => $tender->project->workCategory->name
        );

        $mailer = new MailHelper($emailSubject, $view, $viewData);
        $mailer->setRecipients(array( $latestVerifier ));
        $mailer->send();
    }

    // sends email notifications to verifier one by one for Calling Tender
    public function sendCTTenderVerificationEmail(Tender $tender, TenderCallingTenderInformation $ctInfo, $viewName)
    {
        if( ! $ctInfo->latestVerifier->first() ) return;

        $latestVerifier  = $ctInfo->latestVerifier->first();
        $recipientLocale = $latestVerifier->settings->language->code;
        $view            = "notifications.email.tender.$viewName";
        $emailSubject    = trans('email/callingTender.subject', ['projectReference' => $tender->project->reference], 'message', $recipientLocale);

        $route = (is_null($latestVerifier->getAssignedCompany($tender->project)) && $latestVerifier->isTopManagementVerifier()) ? 'topManagementVerifiers.projects.tender.show' : 'projects.tender.show';

        $viewData = array(
            'senderName'        => $ctInfo->updatedBy->name,
            'recipientName'     => $latestVerifier->name,
            'recipientLocale'   => $recipientLocale,
            'projectName'       => $tender->project->title,
            'tenderName'        => $tender->getCurrentTenderNameByLocale($recipientLocale),
            'toRoute'           => route($route, array( $tender->project->id, $tender->id )) . '#s3',
            'tenderStartDate'   => $tender->project->getProjectTimeZoneTime($tender->callingTenderInformation->date_of_calling_tender),
            'tenderClosingDate' => $tender->project->getProjectTimeZoneTime($tender->callingTenderInformation->date_of_closing_tender),
            'workCategory'      => $tender->project->workCategory->name,
        );

        if( $tender->listOfTendererInformation->technical_evaluation_required )
        {
            $viewData['technicalTenderClosingDate'] = $tender->project->getProjectTimeZoneTime($tender->callingTenderInformation->technical_tender_closing_date ?? null);
        }

        $mailer = new MailHelper($emailSubject, $view, $viewData);
        $mailer->setRecipients(array( $latestVerifier ));
        $mailer->send();
    }

    // sends notification to editor whenever a tender process advances to the next stage
    public function sendEmailNotificationToEditor(Tender $tender, $view, $projectEditor) {
        $recipientLocale = $projectEditor->settings->language->code;
        $subject = trans('email.eTenderNotification', [], 'messages', $recipientLocale);
        $viewData = array(
            'projectTitle'          => $tender->project->title,
            'current_tender_name'   => $tender->getCurrentTenderNameByLocale($recipientLocale),
            'toRoute'               => route('projects.tender.show', array( $tender->project->id, $tender->id )),
            'workCategory'          => $tender->project->workCategory->name,
            'recipientLocale'       => $recipientLocale,
        );

        $mailer = new MailHelper($subject, $view, $viewData);
        $mailer->setRecipients(array($projectEditor));
        $mailer->setPrivacy(true);
        $mailer->send();
    }

    public function sendEmailNotificationToPendingContractorsForPayment(Tender $tender, $view, $contractor)
    {
        $recipientLocale = $contractor->settings->language->code;
        $subject = trans('email.openTenderNotification', [], 'messages', $recipientLocale);
        $viewData = array(
            'projectTitle'          => $tender->project->title,
            'current_tender_name'   => $tender->getCurrentTenderNameByLocale($recipientLocale),
            'paymentLink'           => route('open_tenders.detail_project', array( $tender->project->id )),
            'recipientLocale'       => $recipientLocale,
        );

        $mailer = new MailHelper($subject, $view, $viewData);
        $mailer->setRecipients(array($contractor));
        $mailer->setPrivacy(true);
        $mailer->send();
    }

    public function sendStatusConfirmationEmailToSelectedContractor(Project $project, $tenderStage, $tenderPhaseObject, $admin, $emailDetails, $subject, $token = null)
    {
        $recipientLocale = $admin->settings->language->code;
        $company = Company::find($admin->company_id);
        $view = 'notifications.email.tender_confirm_commitment_status';
        $viewData = array(
            'recipientName'     => $admin->name,
            'recipientLocale'   => $recipientLocale,
            'projectTitle'      => $project->title,
            'tenderStage'       => $tenderStage,
            'companyName'       => $company->name,
            'employerName'      => $emailDetails['employerName'],
            'emailMessage'      => $emailDetails['emailMessage'],
        );

        switch($tenderStage)
        {
            case TenderStages::TENDER_STAGE_RECOMMENDATION_OF_TENDERER:
                $viewData['tenderCallingDate'] = Carbon::parse($project->getProjectTimeZoneTime($tenderPhaseObject->proposed_date_of_calling_tender))->format(\Config::get('dates.full_format'));
                $viewData['tenderClosingDate'] = Carbon::parse($project->getProjectTimeZoneTime($tenderPhaseObject->proposed_date_of_closing_tender))->format(\Config::get('dates.full_format'));
                $viewData['link']              = route('contractors.confirmStatus', array($token));
                break;
            case TenderStages::TENDER_STAGE_LIST_OF_TENDERER:
                $viewData['tenderCallingDate'] = Carbon::parse($project->getProjectTimeZoneTime($tenderPhaseObject->date_of_calling_tender))->format(\Config::get('dates.full_format'));
                $viewData['tenderClosingDate'] = Carbon::parse($project->getProjectTimeZoneTime($tenderPhaseObject->date_of_closing_tender))->format(\Config::get('dates.full_format'));
                $viewData['link']              = route('contractors.confirmStatus', array($token));
                break;
            case TenderStages::TENDER_STAGE_CALLING_TENDER:
                $viewData['tenderCallingDate'] = Carbon::parse($project->getProjectTimeZoneTime($tenderPhaseObject->date_of_calling_tender))->format(\Config::get('dates.full_format'));
                $viewData['tenderClosingDate'] = Carbon::parse($project->getProjectTimeZoneTime($tenderPhaseObject->date_of_closing_tender))->format(\Config::get('dates.full_format'));
                break;
            default:
                // will not happen
        }

        $mailer = new MailHelper($subject, $view, $viewData);
        $mailer->setRecipients(array( $admin ));
        $mailer->send();
    }

    public function sendVerificationResponseEmailTechnicalAssessment(Tender $tender, Verifiable $object, $responder)
    {
        $requestor       = User::find($object->submitted_by);
        $recipientLocale = $requestor->settings->language->code;
        $view            = 'notifications.email.technical_assessment.approved';
        $emailSubject    = 'Technical Assessment Form Notification';
        $viewData        = array(
            'senderName'          => $responder->name,
            'recipientName'       => $requestor->name,
            'project_title'       => $tender->project->title,
            'current_tender_name' => $tender->getCurrentTenderNameByLocale($recipientLocale),
            'workCategory'        => $tender->project->workCategory->name,
            'toRoute'             => route('technicalEvaluation.assessment.confirm', array( $tender->project->id, $tender->id )),
            'recipientLocale'     => $recipientLocale,
        );

        $mailer = new MailHelper($emailSubject, $view, $viewData);
        $mailer->setRecipients(array( $requestor ));
        $mailer->send();
    }

    public function sendRfvNotification(Project $project, $requestForVariation, $responder, $recipient, $view, $emailSubject)
    {
        $isVerifierWithoutProjectAccess = is_null($recipient->getAssignedCompany($project)) && $recipient->isTopManagementVerifier();

        $viewData = [
            'senderName'                        => $responder->name,
            'recipientName'                     => $recipient->name,
            'project_title'                     => $project->title,
            'request_for_variation_description' => $requestForVariation->description,
            'toRoute'                           => $isVerifierWithoutProjectAccess ? route('topManagementVerifiers.requestForVariation.form.show', [$project->id, $requestForVariation->id]) : route('requestForVariation.form.show', [$project->id, $requestForVariation->id]),
        ];

        $mailer = new MailHelper($emailSubject, $view, $viewData);
        $mailer->setRecipients([ $recipient ]);
        $mailer->send();
    }

    public function sendLetterOfAwardNotification(LetterOfAward $letterOfAward, User $sender, User $recipient, $view, $emailSubject)
    {
        $unreadCommentCount = array_sum($this->letterOfAwardClauseCommentRepository->getUnreadCommentsCountGroupedByClause($letterOfAward, $recipient));

        $viewData = [
            'senderName'         => $sender->name,
            'recipientName'      => $recipient->name,
            'project_title'      => $letterOfAward->project->title,
            'toRoute'            => $letterOfAward->getRoute(),
            'unreadCommentCount' => $unreadCommentCount,
            'recipientLocale'    => $recipient->settings->language->code,
        ];

        $mailer = new MailHelper($emailSubject, $view, $viewData);
        $mailer->setRecipients([ $recipient ]);
        $mailer->send();
    }

    public function sendRequestForInspectionEmail(Project $project, Inspection $inspection, User $sender = null, User $recipient, $view)
    {
        $viewData = [
            'recipientName'   => $recipient->name,
            'project_title'   => $project->title,
            'description'     => $inspection->getObjectDescription(),
            'toRoute'         => $inspection->getRoute(),
            'recipientLocale' => $recipient->settings->language->code,
        ];

        if($sender)
        {
            $viewData['senderName'] = $sender->name;
        }

        $mailer = new MailHelper(trans('inspection.requestForInspection'), 'notifications.email.' . $view, $viewData);
        $mailer->setRecipients([ $recipient ]);
        $mailer->send();
    }

    public function forumPostAlert($postId, array $userIds)
    {
        $post    = Post::find($postId);
        $project = $post->thread->project;

        $subject = "[{$post->thread->project->reference}] " . trans('forum.forumNotification');

        $viewData = array(
            'projectTitle' => "[{$post->thread->project->reference}] {$post->thread->project->title}",
            'threadTitle'  => $post->thread->title,
            'postContent'  => $post->getContent(),
            'posterName'   => $post->getPosterName(),
            'postedAt'     => Carbon::parse($project->getProjectTimeZoneTime($post->created_at))->format('d M Y g:i A'),
            'toRoute'      => route('forum.threads.show', array( $post->thread->project->id, $post->thread->id )) . "#post-{$post->id}",
        );

        $mailer = new MailHelper($subject, 'notifications.email.forum.post', $viewData);

        $recipients = array();

        foreach($userIds as $userId)
        {
            $user = User::find($userId);

            if( $post->thread->isViewable($user) ) $recipients[] = $user;
        }

        $mailer->setPrivacy(true);

        $nonContractorRecipients = array_filter($recipients, function($recipient) use ($project)
        {
            return ! $recipient->hasCompanyProjectRole($project, Role::CONTRACTOR);
        });

        $contractorRecipients = array_filter($recipients, function($recipient) use ($project)
        {
            return $recipient->hasCompanyProjectRole($project, Role::CONTRACTOR);
        });

        $mailer->setRecipients($nonContractorRecipients);
        $mailer->send();

        $mailer->setViewDataItem('posterName', trans('forum.anonymousClient'));

        $mailer->setRecipients($contractorRecipients);
        $mailer->send();
    }

    public function sendCallingTenderDateExtendedNotificationsToAssignedCompanies(Tender $tender)
    {
        $users = $this->getGroupOwnerRecipientDetails($tender->project, array( Role::PROJECT_OWNER, TenderFilters::getListOfTendererFormRole($tender->project) ));

        $viewData = array(
            'toRoute'            => route('projects.tender.show', array( $tender->project->id, $tender->id )) . '#s3',
            'projectTitle'       => $tender->project->title,
            'workCategory'       => $tender->project->workCategory->name,
            'tenderStartingDate' => $tender->project->getProjectTimeZoneTime($tender->tender_starting_date),
            'tenderClosingDate'  => $tender->project->getProjectTimeZoneTime($tender->tender_closing_date),
        );

        if( $tender->listOfTendererInformation->technical_evaluation_required )
        {
            $viewData['technicalTenderClosingDate'] = $tender->project->getProjectTimeZoneTime($tender->callingTenderInformation->technical_tender_closing_date ?? null);
        }

        foreach($users as $user)
        {
            $user = User::find($user['id']);

            if( ! $this->sendToUser($tender->project, $user) ) continue;

            $recipientLocale = $user->settings->language->code;
            $subject = "[{$tender->project->reference}] " . trans('email.eTenderNotification', [], 'messages', $recipientLocale);
            
            $viewData['recipientLocale'] = $recipientLocale;
            $viewData['tenderName']      = $tender->getCurrentTenderNameByLocale($recipientLocale);

            $mailer = new MailHelper($subject, "notifications.email.calling_tender_date_extended", $viewData);
            $mailer->setRecipients(array($user));
            $mailer->send();
        }
        
    }

    public function sendTechnicalOpeningSubmittedNotifications(Tender $tender)
    {
        $viewData = array(
            'toRoute'            => route('technicalEvaluation.results.show', array( $tender->project->id, $tender->id )),
            'projectTitle'       => $tender->project->title,
            'workCategory'       => $tender->project->workCategory->name,
            'tenderStartingDate' => $tender->project->getProjectTimeZoneTime($tender->tender_starting_date),
            'tenderClosingDate'  => $tender->project->getProjectTimeZoneTime($tender->tender_closing_date),
        );

        $users = $this->userRepo->getProjectSelectedUsersByProjectAndRoles($tender->project, Tender::rolesAllowedToUseModule($tender->project))->toArray();

        foreach($users as $user)
        {
            $user = User::find($user['id']);

            if( ! $this->sendToUser($tender->project, $user) ) continue;

            $recipientLocale = $user->settings->language->code;
            $subject = "[{$tender->project->reference}] " . trans('email.eTenderNotification', [], 'messages', $recipientLocale);
            
            $viewData['recipientLocale'] = $recipientLocale;
            $viewData['tenderName']      = $tender->getCurrentTenderNameByLocale($recipientLocale);

            $mailer = new MailHelper($subject, "notifications.email.technical_evaluation_open_tender_opened", $viewData);
            $mailer->setRecipients(array($user));
            $mailer->send();
        }
    }

    public function sendCommercialOpeningSubmittedNotifications(Tender $tender)
    {
        $viewData = array(
            'toRoute'            => route('projects.openTender.show', array( $tender->project->id, $tender->id )),
            'projectTitle'       => $tender->project->title,
            'workCategory'       => $tender->project->workCategory->name,
            'tenderStartingDate' => $tender->project->getProjectTimeZoneTime($tender->tender_starting_date),
            'tenderClosingDate'  => $tender->project->getProjectTimeZoneTime($tender->tender_closing_date),
        );

        $users = $this->getGroupOwnerRecipientDetails($tender->project, array( TenderFilters::getListOfTendererFormRole($tender->project) ));

        foreach($users as $user)
        {
            if( ! $this->sendToUser($tender->project, User::find($user['id'])) ) continue;

            $recipientLocale = $user->settings->language->code;
            $subject = "[{$tender->project->reference}] " . trans('email.eTenderNotification', [], 'messages', $recipientLocale);

            $viewData['recipientLocale'] = $recipientLocale;
            $viewData['tenderName']      = $tender->getCurrentTenderNameByLocale($recipientLocale);

            $mailer = new MailHelper($subject, "notifications.email.tender_open_tender_opened", $viewData);
            $mailer->setRecipients(array($user));
            $mailer->send();
        }
    }

    public function sendNewClaimRevisionInitiatedNotifications(PostContractClaimRevision $claimRevision)
    {
        $project = $claimRevision->postContract->projectStructure->mainInformation->getEProjectProject();

        $contractorContractGroupId = ContractGroup::getIdByGroup(Role::CONTRACTOR);

        $company = \PCK\CompanyProject\CompanyProject::where('project_id', '=', $project->id)
            ->where('contract_group_id', '=', $contractorContractGroupId)
            ->first()->company;

        $editorUserIds = ContractGroupProjectUser::where('project_id', '=', $project->id)
            ->where('contract_group_id', '=', $contractorContractGroupId)
            ->where('is_contract_group_project_owner', '=', true)
            ->lists('user_id');

        $users = $company->getActiveUsers()->filter(function($user) use ($editorUserIds) {
            return $user->isGroupAdmin() || in_array($user->id, $editorUserIds);
        });

        $viewData = array(
            'projectTitle' => $project->title,
            'toRoute'      => route('projects.contractorClaims', array($project->id)),
            'subsidiary'   => $project->subsidiary->name,
        );

        $mailer = new MailHelper("", "notifications.email.contractorClaim.new_claim_revision_initiated", $viewData);

        foreach($users as $user)
        {
            $recipientLocale = $user->settings->language->code;

            $mailer->setSubject("[{$project->reference}] " . trans('email.eClaimNotification', [], 'messages', $recipientLocale));
            $mailer->setRecipients([$user]);
            $mailer->send();
        }
    }

    public function sendContractorClaimSubmittedNotifications(PostContractClaimRevision $claimRevision)
    {
        $project = $claimRevision->postContract->projectStructure->mainInformation->getEProjectProject();

        $contractorContractGroupId = ContractGroup::getIdByGroup(Role::CONTRACTOR);

        $relevantContractGroupIds = array(
            ContractGroup::getIdByGroup(Role::PROJECT_OWNER),
            ContractGroup::getIdByGroup(Role::GROUP_CONTRACT),
            ContractGroup::getIdByGroup($project->getCallingTenderRole()),
        );

        $companyProjectRecords = \PCK\CompanyProject\CompanyProject::where('project_id', '=', $project->id)
            ->whereIn('contract_group_id', $relevantContractGroupIds)
            ->get();

        $editorUserIds = ContractGroupProjectUser::where('project_id', '=', $project->id)
            ->whereIn('contract_group_id', $relevantContractGroupIds)
            ->where('is_contract_group_project_owner', '=', true)
            ->lists('user_id');

        $users = new Collection;

        foreach($companyProjectRecords as $companyRecord)
        {
            $companyUsers = $companyRecord->company->getActiveUsers()->filter(function($user) use ($editorUserIds) {
                return $user->isGroupAdmin() || in_array($user->id, $editorUserIds);
            });

            $users = $users->merge($companyUsers);
        }

        $contractorCompany = \PCK\CompanyProject\CompanyProject::where('project_id', '=', $project->id)
            ->where('contract_group_id', '=', ContractGroup::getIdByGroup(Role::CONTRACTOR))
            ->first()->company;

        $viewData = array(
            'projectTitle' => $project->title,
            'toRoute'      => route('projects.contractorClaims', array($project->id)),
            'contractor'   => $contractorCompany->name,
        );

        $mailer = new MailHelper("", "notifications.email.contractorClaim.claim_submitted", $viewData);

        foreach($users as $user)
        {
            $recipientLocale = $user->settings->language->code;

            $mailer->setSubject("[{$project->reference}] " . trans('email.eClaimNotification', [], 'messages', $recipientLocale));
            $mailer->setRecipients([$user]);
            $mailer->send();
        }
    }

    public function sendClaimApprovedNotifications(PostContractClaimRevision $claimRevision)
    {
        $project = $claimRevision->postContract->projectStructure->mainInformation->getEProjectProject();

        $contractorContractGroupId = ContractGroup::getIdByGroup(Role::CONTRACTOR);

        $company = \PCK\CompanyProject\CompanyProject::where('project_id', '=', $project->id)
            ->where('contract_group_id', '=', $contractorContractGroupId)
            ->first()->company;

        $editorUserIds = ContractGroupProjectUser::where('project_id', '=', $project->id)
            ->where('contract_group_id', '=', $contractorContractGroupId)
            ->where('is_contract_group_project_owner', '=', true)
            ->lists('user_id');

        $users = $company->getActiveUsers()->filter(function($user) use ($editorUserIds) {
            return $user->isGroupAdmin() || in_array($user->id, $editorUserIds);
        });

        $viewData = array(
            'projectTitle' => $project->title,
            'toRoute'      => route('projects.contractorClaims', array($project->id)),
            'subsidiary'   => $project->subsidiary->name,
        );

        $mailer = new MailHelper("", "notifications.email.contractorClaim.claim_approved", $viewData);

        foreach($users as $user)
        {
            $recipientLocale = $user->settings->language->code;

            $mailer->setSubject("[{$project->reference}] " . trans('email.eClaimNotification', [], 'messages', $recipientLocale));
            $mailer->setRecipients([$user]);
            $mailer->send();
        }
    }

    public function sendClaimRejectedNotifications(PostContractClaimRevision $claimRevision)
    {
        $project = $claimRevision->postContract->projectStructure->mainInformation->getEProjectProject();

        $contractorContractGroupId = ContractGroup::getIdByGroup(Role::CONTRACTOR);

        $company = \PCK\CompanyProject\CompanyProject::where('project_id', '=', $project->id)
            ->where('contract_group_id', '=', $contractorContractGroupId)
            ->first()->company;

        $editorUserIds = ContractGroupProjectUser::where('project_id', '=', $project->id)
            ->where('contract_group_id', '=', $contractorContractGroupId)
            ->where('is_contract_group_project_owner', '=', true)
            ->lists('user_id');

        $users = $company->getActiveUsers()->filter(function($user) use ($editorUserIds) {
            return $user->isGroupAdmin() || in_array($user->id, $editorUserIds);
        });

        $viewData = array(
            'projectTitle' => $project->title,
            'toRoute'      => route('projects.contractorClaims', array($project->id)),
            'subsidiary'   => $project->subsidiary->name,
        );

        $mailer = new MailHelper("", "notifications.email.contractorClaim.claim_submission_unlocked", $viewData);

        foreach($users as $user)
        {
            $recipientLocale = $user->settings->language->code;

            $mailer->setSubject("[{$project->reference}] " . trans('email.eClaimNotification', [], 'messages', $recipientLocale));
            $mailer->setRecipients([$user]);
            $mailer->send();
        }
    }

    public function sendVendorRenewalReminders(array $companyIds)
    {
        $mailer = new MailHelper(trans('vendorManagement.renewalReminder'), "notifications.email.vendorRegistration.renewal_reminder");

        foreach($companyIds as $companyId)
        {
            $company = Company::find($companyId);

            if( is_null($company->expiry_date) ) continue;

            $mailer->setViewDataItem('companyName', $company->name);
            $mailer->setViewDataItem('expiryDate', Carbon::parse($company->expiry_date)->format(\Config::get('dates.submitted_at')));
            $mailer->setViewDataItem('expiryPassed', Carbon::parse($company->expiry_date)->isPast());
            $mailer->setViewDataItem('toRoute', route('vendors.vendorRegistration.index'));

            $mailer->setRecipients($company->companyAdmins);
            $mailer->send();
        }
    }

    public function sendVendorUpdateReminders(array $companyIds, $contents)
    {
        $mailer = new MailHelper(trans('vendorManagement.updateReminder'), "notifications.email.vendorRegistration.update_reminder");

        foreach($companyIds as $companyId)
        {
            $company = Company::find($companyId);

            $mailer->setViewDataItem('companyName', $company->name);
            $mailer->setViewDataItem('contents', nl2br($contents));

            $mailer->setRecipients($company->companyAdmins);
            $mailer->send();
        }
    }

    public function rejectVendorPerformanceEvaluationForm(VendorPerformanceEvaluationCompanyForm $companyForm, $remarks)
    {
        $subject = trans('email/vendorManagement.vendorPerformanceEvaluationNotification');
        $view    = 'notifications.email.vendorRegistration.vendor_performance_evaluation_rejected_form';
        $mailer  = new MailHelper($subject, $view);

        $recipients = VendorManagementUserPermission::getUsers(VendorManagementUserPermission::TYPE_PERFORMANCE_EVALUATION);

        $mailer->setViewDataItem('requestor', \Confide::user()->name);
        $mailer->setViewDataItem('companyName', $companyForm->company->name);
        $mailer->setViewDataItem('projectTitle', $companyForm->vendorPerformanceEvaluation->project->title);
        $mailer->setViewDataItem('vendorWorkCategory', $companyForm->vendorWorkCategory->name);
        $mailer->setViewDataItem('cycleStartDate', Carbon::parse($companyForm->vendorPerformanceEvaluation->cycle->start_date)->format(\Config::get('dates.full_format')));
        $mailer->setViewDataItem('cycleEndDate', Carbon::parse($companyForm->vendorPerformanceEvaluation->cycle->end_date)->format(\Config::get('dates.full_format')));
        $mailer->setViewDataItem('remarks', $remarks);
        $mailer->setViewDataItem('toRoute', route('vendorPerformanceEvaluation.setups.evaluations.vendors.index', array($companyForm->vendor_performance_evaluation_id)));

        $mailer->setRecipients($recipients);
        $mailer->send();
    }

    public function sendVendorSubmitRegistrationFormNotification(VendorRegistration $vendorRegistration)
    {
        $subject = null;

        if($vendorRegistration->isSubmissionTypeNew())
        {
            $subject = trans('email/vendorManagement.formSubmission');
            $view    = 'notifications.email.vendorRegistration.vendor_registration_form_submitted';
        }

        if($vendorRegistration->isSubmissionTypeUpdate())
        {
            $subject = trans('email/vendorManagement.vendorDetailsUpdate');
            $view    = 'notifications.email.vendorRegistration.vendor_registration_form_update_submitted';
        }

        if($vendorRegistration->isSubmissionTypeRenewal())
        {
            $subject = trans('email/vendorManagement.vendorRenewal');
            $view    = 'notifications.email.vendorRegistration.vendor_registration_form_renewal_submitted';  
        }

        $mailer = new MailHelper($subject, $view);

        // send to current processor if exists, else send to all processors
        $recipients = $vendorRegistration->getCurrentProcessor() ? [$vendorRegistration->getCurrentProcessor()] : VendorManagementUserPermission::getUsers(VendorManagementUserPermission::TYPE_APPROVAL_REGISTRATION);

        $route = $vendorRegistration->isProcessing() ? route('vendorManagement.approval.registrationAndPreQualification.show', [$vendorRegistration->id]) : route('vendorManagement.approval.registrationAndPreQualification.assignForm', [$vendorRegistration->id]);

        $mailer->setViewDataItem('company', $vendorRegistration->company->name);
        $mailer->setViewDataItem('toRoute', $route);
        $mailer->setRecipients($recipients);
        $mailer->send();
    }

    public function sendVendorSubmitRegistrationFormApprovalRequiredNotification(VendorRegistration $vendorRegistration)
    {
        $subject = null;
        $view    = null;

        if($vendorRegistration->isSubmissionTypeNew())
        {
            $subject = trans('email/vendorManagement.formSubmission');
            $view    = 'notifications.email.vendorRegistration.vendor_registration_form_submitted_approval_required';
        }

        if($vendorRegistration->isSubmissionTypeUpdate())
        {
            $subject = trans('email/vendorManagement.vendorDetailsUpdate');
            $view    = 'notifications.email.vendorRegistration.vendor_registration_form_update_submitted_approval_required';
        }

        if($vendorRegistration->isSubmissionTypeRenewal())
        {
            $subject = trans('email/vendorManagement.vendorRenewal');
            $view    = 'notifications.email.vendorRegistration.vendor_registration_form_renewal_submitted_approval_required';  
        }

        $mailer = new MailHelper($subject, $view);

        $recipients = $vendorRegistration->company->companyAdmins;

        $mailer->setRecipients($recipients);
        $mailer->send();
    }

    public function sendRejectVendorRegistrationFormNotification(VendorRegistration $vendorRegistration)
    {
        $accountConfirmationEmailSetting = EmailNotificationSetting::where('setting_identifier', '=', EmailNotificationSetting::NOTIFICATION_TO_VENDOR_RFI_DURING_REGISTRATION_AND_RENEWAL)->first();

        if( ! $accountConfirmationEmailSetting->activated ) return;

        $additionalContent = $accountConfirmationEmailSetting->modifiable_contents;
    
        $mailer = new MailHelper(trans('email/vendorManagement.requestForInformation'), "notifications.email.vendorRegistration.vendor_registration_form_rejected");

        $recipients = $vendorRegistration->company->companyAdmins;

        $mailer->setViewDataItem('company', $vendorRegistration->company->name);
        $mailer->setViewDataItem('contents', nl2br($additionalContent));
        $mailer->setRecipients($recipients);
        $mailer->send();
    }

    public function sendVerifierRejectedVendorRegistrationNotification(VendorRegistration $vendorRegistration)
    {
        $subject = null;

        if($vendorRegistration->isSubmissionTypeNew())
        {
            $subject = trans('email/vendorManagement.vendorRegistrationRejected');
        }

        if($vendorRegistration->isSubmissionTypeUpdate())
        {
            $subject = trans('email/vendorManagement.vendorDetailsUpdateRejected');
        }

        if($vendorRegistration->isSubmissionTypeRenewal())
        {
            $subject = trans('email/vendorManagement.vendorRenewalRejected');
        }

        $mailer = new MailHelper($subject, "notifications.email.vendorRegistration.vendor_registration_form_rejected_by_verifier");

        $mailer->setViewDataItem('company', $vendorRegistration->company->name);
        $mailer->setViewDataItem('toRoute', route('vendorManagement.approval.registrationAndPreQualification.show', [$vendorRegistration->id]));
        $mailer->setRecipients([$vendorRegistration->processor->user]);
        $mailer->send();
    }

    public function sendVendorRegistrationSubmittedForApprovalNotification(VendorRegistration $vendorRegistration, $recipientIds)
    {
        $subject = null;

        if($vendorRegistration->isSubmissionTypeNew())
        {
            $subject = trans('email/vendorManagement.approvalForVendorRegistration');
        }

        if($vendorRegistration->isSubmissionTypeUpdate())
        {
            $subject = trans('email/vendorManagement.approvalForVendorDetaisUpdate');
        }

        if($vendorRegistration->isSubmissionTypeRenewal())
        {
            $subject = trans('email/vendorManagement.approvalForVendorRenewal');
        }

        $mailer = new MailHelper($subject, "notifications.email.vendorRegistration.vendor_registration_form_submitted_for_approval");

        $user = \Confide::user();

        $recipients = [];

        foreach($recipientIds as $recipientId)
        {
            array_push($recipients, User::find($recipientId));
        }

        $mailer->setViewDataItem('userName', $user->name);
        $mailer->setViewDataItem('company', $vendorRegistration->company->name);
        $mailer->setViewDataItem('toRoute', route('vendorManagement.approval.registrationAndPreQualification.show', [$vendorRegistration->id]));
        $mailer->setRecipients($recipients);
        $mailer->send();
    }

    public function sendVendorRegistrationSuccessfulNotification(VendorRegistration $vendorRegistration)
    {
        $accountConfirmationEmailSetting = EmailNotificationSetting::where('setting_identifier', '=', EmailNotificationSetting::NOTIFICATION_TO_VENDOR_ON_SUCESSFUL_REGISTRATION_AND_RENEWAL)->first();

        if( ! $accountConfirmationEmailSetting->activated ) return;

        $additionalContent = $accountConfirmationEmailSetting->modifiable_contents;
    
        $mailer = new MailHelper(trans('email/vendorManagement.vendorRegistrationSuccessful'), "notifications.email.vendorRegistration.vendor_registration_successful");

        $recipients = $vendorRegistration->company->companyAdmins;

        $mailer->setViewDataItem('company', $vendorRegistration->company->name);
        $mailer->setViewDataItem('contents', nl2br($additionalContent));
        $mailer->setRecipients($recipients);
        $mailer->send();
    }

    public function sendVendorRegistrationUpdateOrRenewalApprovedNotification(VendorRegistration $vendorRegistration)
    {
        $subject = null;
        $view    = null;

        if($vendorRegistration->isSubmissionTypeUpdate())
        {
            $subject = trans('email/vendorManagement.vendorDetailsUpdateApproved');
            $view    = 'notifications.email.vendorRegistration.vendor_registration_details_update_successful';
        }

        if($vendorRegistration->isSubmissionTypeRenewal())
        {
            $subject = trans('email/vendorManagement.vendorRenewalApproved');
            $view    = 'notifications.email.vendorRegistration.vendor_registration_renewal_successful';
        }

        $mailer = new MailHelper($subject, $view);

        $recipients = $vendorRegistration->company->companyAdmins;

        $mailer->setRecipients($recipients);
        $mailer->send();
    }

    public function sendVendorRenewalReminderEmail($companyIds)
    {
        if(empty($companyIds)) return false;

        $companies = Company::whereIn('id', $companyIds)->orderBy('id', 'ASC')->get();

        foreach($companies as $company)
        {
            $subject = trans('email/vendorManagement.vendorRenewalReminder');
            $view    = 'notifications.email.vendorRegistration.vendor_renewal_reminder';
            $mailer  = new MailHelper($subject, $view);

            if($company->companyAdmins->count() < 1)
            {
                \Log::info("EmailNotifier@sendVendorRenewalReminderEmail : No company admins found for company ID [{$company->id}] {$company->name}.");

                continue;
            }

            $mailer->setViewDataItem('companyName', $company->name);
            $mailer->setViewDataItem('roc', $company->reference_no);
            $mailer->setViewDataItem('expiryDate', Carbon::parse($company->expiry_date)->format(\Config::get('dates.readable_timestamp_slash')));
            $mailer->setViewDataItem('toRoute', route('vendors.vendorRegistration.index'));

            $mailer->setRecipients($company->companyAdmins);
            $mailer->send();
        }
    }

    public function sendCompanyDeletionEmails(Company $company)
    {
        if($company->companyAdmins->count() < 1)
        {
            \Log::info("EmailNotifier@sendCompanyDeletionEmails : No company admins found for company ID [{$company->id}] {$company->name}.");

            return false;
        }

        $subject = trans('email/vendorManagement.duplicateCompany');
        $view    = 'notifications.email.vendorRegistration.duplicate_company';
        $mailer  = new MailHelper($subject, $view);

        $mailer->setRecipients($company->companyAdmins);
        $mailer->send();
    }

    public function sendVendorAssignedVpeFormNotifications(VendorPerformanceEvaluationSetup $setup)
    {
        $subject = trans('email/vendorManagement.vendorPerformanceEvaluationNotification');
        $view    = 'notifications.email.vendorPerformanceEvaluation.vendor_assigned_form';
        $mailer  = new MailHelper($subject, $view); 

        $buCompany = $setup->vendorPerformanceEvaluation->project->selectedCompanies()->where('contract_group_id', '=', ContractGroup::getIdByGroup(Role::PROJECT_OWNER))->first();

        $editorUserIds = ContractGroupProjectUser::where('project_id', '=', $setup->vendorPerformanceEvaluation->project->id)
            ->where('project_id', '=', $setup->vendorPerformanceEvaluation->project->id)
            ->where('is_contract_group_project_owner', '=', true)
            ->lists('user_id');

        $buEditorUsers = $buCompany->getActiveUsers()->filter(function($user) use ($editorUserIds) {
            return in_array($user->id, $editorUserIds);
        });

        $mailer->setViewDataItem('companyName', $setup->company->name);
        $mailer->setViewDataItem('projectTitle', $setup->vendorPerformanceEvaluation->project->title);
        $mailer->setViewDataItem('vendorWorkCategory', $setup->vendorWorkCategory->name);
        $mailer->setViewDataItem('cycleStartDate', Carbon::parse($setup->vendorPerformanceEvaluation->cycle->start_date)->format(\Config::get('dates.full_format')));
        $mailer->setViewDataItem('cycleEndDate', Carbon::parse($setup->vendorPerformanceEvaluation->cycle->end_date)->format(\Config::get('dates.full_format')));
        $mailer->setViewDataItem('toRoute', route('vendorPerformanceEvaluation.evaluations.evaluators.edit', [$setup->vendorPerformanceEvaluation->id]));

        $mailer->setRecipients($buEditorUsers);
        $mailer->send();
    }

    public function sendVendorAssignedVpeFormReminderNotifications(VendorPerformanceEvaluation $vendorPerformanceEvaluation)
    {
        $subject = trans('email/vendorManagement.vendorPerformanceEvaluationNotification');
        $view    = 'notifications.email.vendorPerformanceEvaluation.vendor_assigned_form_reminder';
        $mailer  = new MailHelper($subject, $view); 

        $buCompany = $vendorPerformanceEvaluation->project->selectedCompanies()->where('contract_group_id', '=', ContractGroup::getIdByGroup(Role::PROJECT_OWNER))->first();

        $editorUserIds = ContractGroupProjectUser::where('project_id', '=', $vendorPerformanceEvaluation->project->id)
            ->where('project_id', '=', $vendorPerformanceEvaluation->project->id)
            ->where('is_contract_group_project_owner', '=', true)
            ->lists('user_id');

        $buEditorUsers = $buCompany->getActiveUsers()->filter(function($user) use ($editorUserIds) {
            return in_array($user->id, $editorUserIds);
        });

        $mailer->setViewDataItem('projectTitle', $vendorPerformanceEvaluation->project->title);
        $mailer->setViewDataItem('cycleStartDate', Carbon::parse($vendorPerformanceEvaluation->cycle->start_date)->format(\Config::get('dates.full_format')));
        $mailer->setViewDataItem('cycleEndDate', Carbon::parse($vendorPerformanceEvaluation->cycle->end_date)->format(\Config::get('dates.full_format')));
        $mailer->setViewDataItem('toRoute', route('vendorPerformanceEvaluation.evaluations.evaluators.edit', [$vendorPerformanceEvaluation->id]));

        $mailer->setRecipients($buEditorUsers);
        $mailer->send();
    }

    public function sendVpeUsersAssignedAsEvaluators(VendorPerformanceEvaluation $vendorPerformanceEvaluation, $users)
    {
        if(count($users) < 1) return false;

        $subject = trans('email/vendorManagement.vendorPerformanceEvaluationNotification');
        $view    = 'notifications.email.vendorPerformanceEvaluation.evaluator_assigned';
        $mailer  = new MailHelper($subject, $view);

        $mailer->setViewDataItem('projectTitle', $vendorPerformanceEvaluation->project->title);
        $mailer->setViewDataItem('cycleStartDate', Carbon::parse($vendorPerformanceEvaluation->cycle->start_date)->format(\Config::get('dates.full_format')));
        $mailer->setViewDataItem('cycleEndDate', Carbon::parse($vendorPerformanceEvaluation->cycle->end_date)->format(\Config::get('dates.full_format')));
        $mailer->setViewDataItem('toRoute', route('vendorPerformanceEvaluation.evaluations.forms', [$vendorPerformanceEvaluation->id]));

        $mailer->setRecipients($users);
        $mailer->send();
    }

    public function sendEvaluatorSubmittedVpeFormNotification(VendorPerformanceEvaluationCompanyForm $companyForm)
    {
        $subject = trans('email/vendorManagement.vendorPerformanceEvaluationNotification');
        $view    = 'notifications.email.vendorPerformanceEvaluation.evaluator_submitted_form';
        $mailer  = new MailHelper($subject, $view);

        $buCompany = $companyForm->vendorPerformanceEvaluation->project->selectedCompanies()->where('contract_group_id', '=', ContractGroup::getIdByGroup(Role::PROJECT_OWNER))->first();

        $mailer->setViewDataItem('companyName', $companyForm->company->name);
        $mailer->setViewDataItem('projectTitle', $companyForm->vendorPerformanceEvaluation->project->title);
        $mailer->setViewDataItem('vendorWorkCategory', $companyForm->vendorWorkCategory->name);
        $mailer->setViewDataItem('cycleStartDate', Carbon::parse($companyForm->vendorPerformanceEvaluation->cycle->start_date)->format(\Config::get('dates.full_format')));
        $mailer->setViewDataItem('cycleEndDate', Carbon::parse($companyForm->vendorPerformanceEvaluation->cycle->end_date)->format(\Config::get('dates.full_format')));
        $mailer->setViewDataItem('submitter', \Confide::user()->name);
        $mailer->setViewDataItem('toRoute', route('vendorPerformanceEvaluation.companyForms.approval.edit', [$companyForm->id]));

        $mailer->setRecipients($buCompany->companyAdmins);
        $mailer->send();
    }

    public function sendSubmitterRejectedVpeFormNotification(VendorPerformanceEvaluationCompanyForm $companyForm)
    {
        $evaluatorUserIds = $companyForm->vendorPerformanceEvaluation->evaluators->lists('user_id');
        $evaluatorUsers   = User::whereIn('id', $evaluatorUserIds)->get();

        if($evaluatorUsers->count() < 1) return false;

        $subject = trans('email/vendorManagement.vendorPerformanceEvaluationNotification');
        $view    = 'notifications.email.vendorPerformanceEvaluation.submitter_rejected_form';
        $mailer  = new MailHelper($subject, $view);

        $mailer->setViewDataItem('companyName', $companyForm->company->name);
        $mailer->setViewDataItem('projectTitle', $companyForm->vendorPerformanceEvaluation->project->title);
        $mailer->setViewDataItem('vendorWorkCategory', $companyForm->vendorWorkCategory->name);
        $mailer->setViewDataItem('cycleStartDate', Carbon::parse($companyForm->vendorPerformanceEvaluation->cycle->start_date)->format(\Config::get('dates.full_format')));
        $mailer->setViewDataItem('cycleEndDate', Carbon::parse($companyForm->vendorPerformanceEvaluation->cycle->end_date)->format(\Config::get('dates.full_format')));
        $mailer->setViewDataItem('rejector', \Confide::user()->name);
        $mailer->setViewDataItem('toRoute', route('vendorPerformanceEvaluation.evaluations.forms.edit', [$companyForm->vendorPerformanceEvaluation->id, $companyForm->id]));

        $mailer->setRecipients($evaluatorUsers);
        $mailer->send();
    }

    public function sendVpeProjectRemovalFromEvaluationRequestNotification(VendorPerformanceEvaluation $vendorPerformanceEvaluation, $remarks)
    {
        $subject = trans('email/vendorManagement.vendorPerformanceEvaluationNotification');
        $view    = 'notifications.email.vendorPerformanceEvaluation.project_removal_request';
        $mailer  = new MailHelper($subject, $view);

        $recipients = VendorManagementUserPermission::getUsers(VendorManagementUserPermission::TYPE_PERFORMANCE_EVALUATION);

        $mailer->setViewDataItem('companyName', $vendorPerformanceEvaluation->project->businessUnit->name);
        $mailer->setViewDataItem('projectTitle', $vendorPerformanceEvaluation->project->title);
        $mailer->setViewDataItem('cycleStartDate', Carbon::parse($vendorPerformanceEvaluation->cycle->start_date)->format(\Config::get('dates.full_format')));
        $mailer->setViewDataItem('cycleEndDate', Carbon::parse($vendorPerformanceEvaluation->cycle->end_date)->format(\Config::get('dates.full_format')));
        $mailer->setViewDataItem('requestor', \Confide::user()->name);
        $mailer->setViewDataItem('remarks', $remarks);
        $mailer->setViewDataItem('toRoute', route('vendorPerformanceEvaluation.evaluations.removalRequest.index'));

        $mailer->setRecipients($recipients);
        $mailer->send();
    }

    public function sendVpeProjectRemovalRequestDismissedNotification(RemovalRequest $removalRequest, $remarks)
    {
        $subject = trans('email/vendorManagement.vendorPerformanceEvaluationNotification');
        $view    = 'notifications.email.vendorPerformanceEvaluation.project_removal_request_dismissed';
        $mailer  = new MailHelper($subject, $view);

        $buCompany = $removalRequest->vendorPerformanceEvaluation->project->selectedCompanies()->where('contract_group_id', '=', ContractGroup::getIdByGroup(Role::PROJECT_OWNER))->first();

        $editorUserIds = ContractGroupProjectUser::where('project_id', '=', $removalRequest->vendorPerformanceEvaluation->project->id)
            ->where('project_id', '=', $removalRequest->vendorPerformanceEvaluation->project->id)
            ->where('is_contract_group_project_owner', '=', true)
            ->lists('user_id');

        $buEditorUsers = $buCompany->getActiveUsers()->filter(function($user) use ($editorUserIds) {
            return in_array($user->id, $editorUserIds);
        });

        $mailer->setViewDataItem('companyName', $removalRequest->vendorPerformanceEvaluation->project->businessUnit->name);
        $mailer->setViewDataItem('projectTitle', $removalRequest->vendorPerformanceEvaluation->project->title);
        $mailer->setViewDataItem('cycleStartDate', Carbon::parse($removalRequest->vendorPerformanceEvaluation->cycle->start_date)->format(\Config::get('dates.full_format')));
        $mailer->setViewDataItem('cycleEndDate', Carbon::parse($removalRequest->vendorPerformanceEvaluation->cycle->end_date)->format(\Config::get('dates.full_format')));
        $mailer->setViewDataItem('user', \Confide::user()->name);
        $mailer->setViewDataItem('remarks', $remarks);

        $mailer->setRecipients($buEditorUsers);
        $mailer->send();
    }

    public function sendRemainderEmailsBeforeVpeCycleEndDate(VendorPerformanceEvaluation $vendorPerformanceEvaluation)
    {
        $subject = trans('email/vendorManagement.vendorPerformanceEvaluationNotification');
        $view    = 'notifications.email.vendorPerformanceEvaluation.cycle_ending_reminder';
        $mailer  = new MailHelper($subject, $view);

        $buCompany = $vendorPerformanceEvaluation->project->selectedCompanies()->where('contract_group_id', '=', ContractGroup::getIdByGroup(Role::PROJECT_OWNER))->first();

        $editorUserIds = ContractGroupProjectUser::where('project_id', '=', $vendorPerformanceEvaluation->project->id)
            ->where('project_id', '=', $vendorPerformanceEvaluation->project->id)
            ->where('is_contract_group_project_owner', '=', true)
            ->lists('user_id');

        $buEditorUsers = $buCompany->getActiveUsers()->filter(function($user) use ($editorUserIds) {
            return in_array($user->id, $editorUserIds);
        });

        $mailer->setViewDataItem('companyName', $vendorPerformanceEvaluation->project->businessUnit->name);
        $mailer->setViewDataItem('projectTitle', $vendorPerformanceEvaluation->project->title);
        $mailer->setViewDataItem('cycleStartDate', Carbon::parse($vendorPerformanceEvaluation->start_date)->format(\Config::get('dates.full_format')));
        $mailer->setViewDataItem('cycleEndDate', Carbon::parse($vendorPerformanceEvaluation->end_date)->format(\Config::get('dates.full_format')));

        $mailer->setRecipients($buEditorUsers);
        $mailer->send();
    }

    public function sendReminderEmailsBeforeCallingTenderClosingDate(Project $project)
    {
        $view = 'notifications.email.tender_closing_reminder';
        
        $companyIds = $project->latestTender->callingTenderInformation->selectedConfirmedContractors->lists('id');
        
        $companies = Company::whereIn('id', $companyIds)->get();

        foreach($companies as $company)
        {
            foreach($company->getActiveUsers() as $user)
            {
                $recipientLocale = $user->settings->language->code;
                $subject = trans('projects.tenderClosingReminder', [], 'messages', $recipientLocale);

                $mailer = new MailHelper($subject, $view);

                $mailer->setViewDataItem('companyName', $company->name);
                $mailer->setViewDataItem('projectTitle', $project->title);
                $mailer->setViewDataItem('tender', $project->latestTender->getCurrentTenderNameByLocale($recipientLocale));
                $mailer->setViewDataItem('dateOfClosingTender', $project->latestTender->callingTenderInformation->date_of_closing_tender);

                $mailer->setRecipients([$user]);
                $mailer->send();
            }
        }
    }

    /*
     * Array $content 
     * [
     *   'subject' => EMAIL_SUBJECT,
     *   'view' => BLADE_VIEW_FILENAME,
     *    'data' => [ '$variableName' => '$value' ...]
     * ]
     * 
     */
    public function sendGeneralEmail(Array $content, $recipients)
    {
        $mailer = new MailHelper($content['subject'], $content['view'], $content['data']);
        $mailer->setRecipients($recipients);
        $mailer->send();
    }

    public function sendAssignedToCostDataNotification($costDataId, array $userIds)
    {
        $costData = CostData::find($costDataId);
        $users    = User::whereIn('id', $userIds)->get();

        $subject = trans('email/costData.costDataManagementNotification');
        $view    = 'notifications.email.cost_data_management_inclusion';
        $mailer  = new MailHelper($subject, $view);

        $mailer->setViewDataItem('costDataName', $costData->name);
        $mailer->setViewDataItem('toRoute', $costData->getAppLink());
        $mailer->setRecipients($users);
        $mailer->send();
    }

    public function sendTechnicalEvaluationPeriodEndedNotifications($tender)
    {
        $contractGroups = ContractGroup::whereIn('group', [
            Role::PROJECT_OWNER,
            Role::GROUP_CONTRACT
        ])->get();

        $contractGroupProjectUsers = ContractGroupProjectUser::where('project_id', '=', $tender->project_id)
            ->whereIn('contract_group_id', $contractGroups->lists('id'))
            ->where('is_contract_group_project_owner', '=', true)
            ->get();

        $view    = 'notifications.email.tender.technical_evaluation_period_ended';
        $mailer  = new MailHelper("", $view);

        foreach($contractGroupProjectUsers as $contractGroupProjectUser)
        {
            $recipientLocale = $contractGroupProjectUser->user->settings->language->code;

            $subject = trans('email/tender.technicalEvaluationEndedSubject', [], 'messages', $recipientLocale);

            $mailer->setSubject($subject);

            $mailer->setViewDataItem('projectName', "[{$tender->project->reference}] {$tender->project->title}");
            $mailer->setViewDataItem('tenderName', $tender->current_tender_name);
            $mailer->setViewDataItem('closingDate', $tender->technical_tender_closing_date);
            $mailer->setViewDataItem('toRoute', route('technicalEvaluation.results.verifiers.form', [$tender->project_id, $tender->id]));

            $mailer->setRecipients([$contractGroupProjectUser->user]);
            $mailer->send();
        }
    }

    public function sendDocumentManagementFolderNotifications($folderId)
    {
        $folder = \PCK\DocumentManagementFolders\DocumentManagementFolder::find($folderId);

        $contractGroupProjectUsers = ContractGroupProjectUser::where('project_id', '=', $folder->project_id)
            ->whereIn('contract_group_id', $folder->contractGroups->lists('id'))
            ->where('is_contract_group_project_owner', '=', true)
            ->get();

        $view    = 'notifications.email.project_documents.shared';
        $mailer  = new MailHelper("", $view);

        foreach($contractGroupProjectUsers as $contractGroupProjectUser)
        {
            $recipientLocale = $contractGroupProjectUser->user->settings->language->code;

            $subject = trans('email/project_documents.projectDocumentsNotification', [], 'messages', $recipientLocale);

            $mailer->setSubject($subject);

            $mailer->setViewDataItem('projectName', "[{$folder->project->reference}] {$folder->project->title}");
            $mailer->setViewDataItem('toRoute', route('projectDocument.mySharedFolder', [$folder->project_id, $folder->id]));

            $mailer->setRecipients([$contractGroupProjectUser->user]);
            $mailer->send();
        }
    }

    public function sendProjectReportSavedNotifications(ProjectReport $projectReport)
    {
        $projectReportTypeId = $projectReport->projectReportTypeMapping->projectReportType->id;
        $submitters          = ProjectReportUserPermission::getLisOfUsersByIdentifier($projectReport->project, $projectReportTypeId, ProjectReportUserPermission::IDENTIFIER_SUBMIT_REPORT);

        $viewData = [
            'project'  => $projectReport->project->title,
            'title'    => $projectReport->title,
            'revision' => $projectReport->getRevisionText(),
            'toRoute'  => $projectReport->getShowRoute(),
        ];

        $mailer = new MailHelper(trans('projectReport.readyForApprovalSubject'), 'notifications.email.projectReport.ready_for_approval', $viewData);
        $mailer->setRecipients($submitters);
        $mailer->send();
    }

    public function sendProjectReportCreatedNotifications(ProjectReport  $projectReport)
    {
        $projectReportTypeId = $projectReport->projectReportTypeMapping->projectReportType->id;
        $submitters          = ProjectReportUserPermission::getLisOfUsersByIdentifier($projectReport->project, $projectReportTypeId, ProjectReportUserPermission::IDENTIFIER_SUBMIT_REPORT);

        $viewData = [
            'project'  => $projectReport->project->title,
            'title'    => $projectReport->title,
            'revision' => $projectReport->getRevisionText(),
            'toRoute'  => $projectReport->getShowRoute(),
        ];

        $mailer = new MailHelper(trans('projectReport.readyForEditingSubject'), 'notifications.email.projectReport.ready_for_editing', $viewData);
        $mailer->setRecipients($submitters);
        $mailer->send();
    }

    public function sendProjectReportReminder(ProjectReportNotification $notification)
    {
        $project = $notification->project;
        $mapping = $notification->projectReportTypeMapping;
        $projectReportType = $mapping->projectReportType;
        $recipients = ProjectReportUserPermission::getLisOfUsersByIdentifier($project, $projectReportType->id, ProjectReportUserPermission::IDENTIFIER_RECEIVE_REMINDER);
        if (empty($recipients)) {
            return false;
        }

        //$latestReport = ProjectReport::latestApprovedProjectReport($project, $mapping);

        $content = $notification->content;
        $subject = $content->subject;
        $body    = $content->body;

        $viewData = [
            //'projectTitle'  => $project->title,
            //'reportTitle'   => $latestReport->title,
            'body'          => $body,
        ];

        try {
            $mailer = new MailHelper($subject, 'notifications.email.projectReport.reminder', $viewData);
            $mailer->setRecipients($recipients);
            $mailer->send();

            foreach ($recipients as $recipient) {
                $this->projectReportNotificationRepository->notificationRecipientLog($notification->id, $recipient->id);
            }

            return true;
        } catch (\Exception $e) {
            // Log the error message
            \Log::error('Email sending failed: ' . $e->getMessage());
            return false;
        }
    }

    // Digital Star Notification - View data
    private function getDsViewData(DsEvaluationForm $evaluationForm) {
        $viewData = [];

        $evaluation = $evaluationForm->evaluation;
        if ($evaluation) {
            $company = $evaluation->company;
            if ($company) {
                $viewData['companyName'] = $company->name;

                $vendorGroup = $company->contractGroupCategory;
                if ($vendorGroup) {
                    $viewData['vendorGroup'] = $vendorGroup->name;
                }
            }

            $cycle = $evaluation->cycle;
            if ($cycle) {
                $viewData['cycleStartDate'] = Carbon::parse($cycle->start_date)->format(\Config::get('dates.full_format'));
                $viewData['cycleEndDate'] = Carbon::parse($cycle->end_date)->format(\Config::get('dates.full_format'));
            }
        }

        if (! is_null($evaluationForm->project_id)) {   // Project evaluation
            $project = $evaluationForm->project;
            if ($project) {
                $viewData['projectTitle'] = $project->title;
                $viewData['contractNo'] = $project->reference;
            }
        }

        return $viewData;
    }

    // Digital Star Notification - Recipients
    private function getDsNotificationRecipients(DsEvaluationForm $evaluationForm, $roleSlug)
    {
        $recipients = [];

        switch ($roleSlug) {
            case 'company-evaluator':   // Company admins
                $evaluation = $evaluationForm->evaluation;
                User::where('company_id', '=', $evaluation->company_id)
                    ->where('is_admin', '=', true)
                    ->get()
                    ->each(function($user) use (&$recipients) {
                        $recipients[] = $user;
                    });
                break;

            default:    // Others
                $role = DsRole::where('slug', '=', $roleSlug)->first();
                if ($role) {
                    $userRoles = DsEvaluationFormUserRole::where('ds_evaluation_form_id', $evaluationForm->id)
                        ->where('ds_role_id', $role->id)
                        ->get();

                    if (! $userRoles->isEmpty()) {
                        $userIds = $userRoles->lists('user_id');
                        $users = User::whereIn('id', $userIds)->get();

                        foreach ($users as $user) {
                            $recipients[] = $user;
                        }
                    }
                }
        }

        return $recipients;
    }

    // Digital Star - Cycle start/end reminder
    public function sendDsCycleReminderEmail(DsEvaluationForm $evaluationForm, $type = 'start')
    {
        $viewBaseDir = 'notifications.email.digitalStar';
        if ($type === 'start') {
            $subject = trans('digitalStar/email.cycleStartReminderTitle');
            $view    = $viewBaseDir.'.cycle_starting_reminder';
        } else {
            $subject = trans('digitalStar/email.cycleEndReminderTitle');
            $view    = $viewBaseDir.'.cycle_ending_reminder';
        }

        $evaluation = $evaluationForm->evaluation;

        $viewData = $this->getDsViewData($evaluationForm);

        if (! is_null($evaluationForm->project_id)) {   // Project evaluation
            $viewData['link'] = route('digital-star.evaluation.project.edit', [$evaluation->id, $evaluationForm->id]);
            $recipients = $this->getDsNotificationRecipients($evaluationForm, 'project-evaluator');
        } else {    // Company evaluation
            $viewData['link'] = route('digital-star.evaluation.company.edit', [$evaluation->id, $evaluationForm->id]);
            $recipients = $this->getDsNotificationRecipients($evaluationForm, 'company-evaluator');
        }

        $mailer = new MailHelper($subject, $view);
        $mailer->setViewData($viewData);
        $mailer->setRecipients($recipients);
        $mailer->send();
    }

    // Digital Star Notification - Evaluation form assigned to evaluator
    public function sendDsNotificationFormAssignedToEvaluator(DsEvaluationForm $evaluationForm, $assignee = null)
    {
        $user = \Confide::user();
        $viewData = $this->getDsViewData($evaluationForm);

        $evaluation = $evaluationForm->evaluation;

        if (is_null($evaluationForm->project_id)) { // Company
            $subject = trans('digitalStar/email.companyEvaluationAssignedTitle');
            $viewData['link'] = route('digital-star.evaluation.company.edit', [$evaluation->id, $evaluationForm->id]);

            if (empty($assignee)) {
                $recipients = $this->getDsNotificationRecipients($evaluationForm, 'company-evaluator');
            }
        } else {    // Project
            $subject = trans('digitalStar/email.projectEvaluationAssignedTitle');
            $viewData['link'] = route('digital-star.evaluation.project.edit', [$evaluation->id, $evaluationForm->id]);

            if (empty($assignee)) {
                $recipients = $this->getDsNotificationRecipients($evaluationForm, 'project-evaluator');
            }
        }

        if ($user) {
            $viewData['actionBy'] = $user->name;
        }

        $view = 'notifications.email.digitalStar.evaluator_assigned';

        // Recipients -> Assignee
        if (! empty($assignee)) {
            $recipients = [$assignee];
        }

        $mailer = new MailHelper($subject, $view);
        $mailer->setViewData($viewData);
        $mailer->setRecipients($recipients);
        $mailer->send();

        return [
            'actionBy' => $viewData['actionBy'] ?? null,
            'recipients' => $recipients ?? null,
            'link' => $viewData['link'] ?? null,
        ];
    }

    // Digital Star Notification - Evaluation form assigned to processor
    public function sendDsNotificationFormAssignedToProcessor(DsEvaluationForm $evaluationForm, $assignee = null)
    {
        $user = \Confide::user();
        $viewData = $this->getDsViewData($evaluationForm);

        if (is_null($evaluationForm->project_id)) { // Company
            $subject = trans('digitalStar/email.companyEvaluationAssignedTitle');
            $viewData['link'] = route('digital-star.approval.company.assign-verifiers.edit', [$evaluationForm->id]);

            if (empty($assignee)) {
                $recipients = $this->getDsNotificationRecipients($evaluationForm, 'company-processor');
            }
        } else {    // Project
            $subject = trans('digitalStar/email.projectEvaluationAssignedTitle');
            $viewData['link'] = route('digital-star.approval.project.assign-verifiers.edit', [$evaluationForm->id]);

            if (empty($assignee)) {
                $recipients = $this->getDsNotificationRecipients($evaluationForm, 'project-evaluator');
            }
        }

        if ($user) {
            $viewData['actionBy'] = $user->name;
        }

        $view = 'notifications.email.digitalStar.processor_assigned';

        // Recipients -> Assignee
        if (! empty($assignee)) {
            $recipients = [$assignee];
        }

        $mailer = new MailHelper($subject, $view);
        $mailer->setViewData($viewData);
        $mailer->setRecipients($recipients);
        $mailer->send();

        return [
            'actionBy' => $viewData['actionBy'] ?? null,
            'recipients' => $recipients ?? null,
            'link' => $viewData['link'] ?? null,
        ];
    }

    // Digital Star Notification - Evaluation form submitted by evaluator
    public function sendDsNotificationFormSubmittedByEvaluator(DsEvaluationForm $evaluationForm)
    {
        $user = \Confide::user();
        $viewData = $this->getDsViewData($evaluationForm);

        if (is_null($evaluationForm->project_id)) { // Company
            $subject = trans('digitalStar/email.companyEvaluationSubmittedTitle');
            $viewData['link'] = route('digital-star.approval.company.assign-verifiers.edit', [$evaluationForm->id]);

            // Recipients -> Processors
            $recipients = $this->getDsNotificationRecipients($evaluationForm, 'company-processor');
        } else {    // Project
            $subject = trans('digitalStar/email.projectEvaluationSubmittedTitle');
            $viewData['link'] = route('digital-star.approval.project.approve.edit', [$evaluationForm->id]);

            // Recipients -> Evaluators
            $recipients = $this->getDsNotificationRecipients($evaluationForm, 'project-evaluator');
        }

        if ($user) {
            $viewData['actionBy'] = $user->name;
        }

        $view = 'notifications.email.digitalStar.evaluator_submitted_form';

        $mailer = new MailHelper($subject, $view);
        $mailer->setViewData($viewData);
        $mailer->setRecipients($recipients);
        $mailer->send();

        return [
            'actionBy' => $viewData['actionBy'] ?? null,
            'recipients' => $recipients ?? null,
            'link' => $viewData['link'] ?? null,
        ];
    }

    // Digital Star Notification - Evaluation form rejected by processor
    public function sendDsNotificationFormRejectedByProcessor(DsEvaluationForm $evaluationForm)
    {
        $user = \Confide::user();
        $viewData = $this->getDsViewData($evaluationForm);

        if ($user) {
            $viewData['actionBy'] = $user->name;
        }

        $evaluation = $evaluationForm->evaluation;

        if (is_null($evaluationForm->project_id)) { // Company
            $viewData['link'] = route('digital-star.evaluation.company.edit', [$evaluation->id, $evaluationForm->id]);

            // Recipients -> Company's evaluators
            $recipients = $this->getDsNotificationRecipients($evaluationForm, 'company-evaluator');
        } else {    // Project
            $viewData['link'] = route('digital-star.evaluation.project.edit', [$evaluation->id, $evaluationForm->id]);

            // Recipients -> Project's evaluators
            $recipients = $this->getDsNotificationRecipients($evaluationForm, 'project-evaluator');
        }

        $subject = trans('digitalStar/email.companyEvaluationRejectedTitle');
        $view = 'notifications.email.digitalStar.processor_rejected_form';

        $mailer = new MailHelper($subject, $view);
        $mailer->setViewData($viewData);
        $mailer->setRecipients($recipients);
        $mailer->send();

        return [
            'actionBy' => $viewData['actionBy'] ?? null,
            'recipients' => $recipients ?? null,
            'link' => $viewData['link'] ?? null,
        ];
    }
}