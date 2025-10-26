<?php namespace PCK\Notifications;

use Carbon\Carbon;
use PCK\Users\User;
use PCK\Tenders\Tender;
use PCK\Projects\Project;
use PCK\Users\UserRepository;
use Illuminate\Database\Eloquent\Model;
use PCK\DocumentManagementFolders\DocumentManagementFolder;
use PCK\Verifier\Verifiable;

class SystemNotifier implements Notification {

    private $userRepo;

    public function __construct(UserRepository $userRepo)
    {
        $this->userRepo = $userRepo;
    }
    
    public function sendNotification(Project $project, Model $model, array $roles, $viewName, $routeName, $tabId = null)
    {
        $recipients = $this->getGroupOwnerRecipientDetails($project, $roles);

        $url = route($routeName, array( $project->id, $model->id ), false);

        if( isset( $tabId ) ) $url .= $tabId;

        $this->processNotifications($recipients, $url, $viewName);
    }

    public function sendNotificationByUsers(Project $project, Model $model, array $users, $viewName, $routeName, $tabId = null)
    {
        $url = route($routeName, array( $project->id, $model->id ), false);

        if( isset( $tabId ) ) $url .= $tabId;

        $this->processNotifications($users, $url, $viewName);
    }

    public function sendNotificationToCompanyAdmin(Project $project, Model $model, array $roles, $viewName, $routeName, $tabId = null)
    {
        $recipients = $this->getCompanyAdminRecipientDetails($project, $roles);

        $url = route($routeName, array( $project->id, $model->id ), false);

        if( isset( $tabId ) ) $url .= $tabId;

        $this->processNotifications($recipients, $url, $viewName);
    }

    public function sendProjectDocumentNotificationToSelectedGroupUsers(Project $project, DocumentManagementFolder $model, $viewName, $routeName, $returnToParentRoot)
    {
        $folderId   = $model->id;
        $recipients = $this->getProjectDocumentSelectedGroupUsers($project, $model);

        if( $returnToParentRoot ) $folderId = $model->parent_id;

        $url = route($routeName, array( $project->id, $folderId ), false);

        $this->processNotifications($recipients, $url, $viewName);
    }

    public function sendTenderVerifierNotification(Project $project, Tender $tender, array $users, $sendByUserId, $viewName, $routeName, $tabId = null)
    {
        $url = route($routeName, array( $project->id, $tender->id ), false);

        if( isset( $tabId ) ) $url .= $tabId;

        $this->processNotifications($users, $url, $viewName, $sendByUserId);
    }

    public function getGroupOwnerRecipientDetails(Project $project, array $roles)
    {
        return $this->userRepo->getProjectGroupOwnersByProjectAndRoles($project, $roles);
    }

    public function getCompanyAdminRecipientDetails(Project $project, array $roles)
    {
        return $this->userRepo->getCompanyAdminByProjectAndRoles($project, $roles);
    }

    private function getProjectDocumentSelectedGroupUsers(Project $project, DocumentManagementFolder $model)
    {
        return $this->userRepo->getProjectDocumentSelectedGroupUsers($project, $model);
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
        self::send($recipients, $route, $viewName, $sender, $viewData);
    }

    /**
     * Sends system notifications.
     *
     * @param       $recipients
     * @param       $url
     * @param       $viewName
     * @param User  $sender
     * @param array $viewData
     */
    public static function send($recipients, $url, $viewName, User $sender = null, $viewData = array())
    {
        if( ! $sender ) $sender = \Confide::user();

        $notificationInfo = array();
        $currentTime      = Carbon::now();

        $view = \View::make("{$viewName}", $viewData);

        foreach($recipients as $recipient)
        {
            // Don't send notification to self.
            if( $sender->id == $recipient->id ) continue;

            $notificationInfo[] = array(
                'from_id'     => $sender->id, // ID user that send the notification
                'from_type'   => 'PCK\Users\User', // Type of model used for polymorphic relation
                'to_id'       => $recipient->id, // ID user that receive the notification
                'to_type'     => 'PCK\Users\User',  // Type of model used for polymorphic relation
                'category_id' => 1, // category notification ID
                'url'         => $url, // Url of your notification
                'extra'       => $view,
                'created_at'  => $currentTime,
                'updated_at'  => $currentTime,
            );

            \Log::info("Notification ready: To [$recipient->id:$recipient->username]");
        }

        if( ! empty( $notificationInfo ) ) \Notifynder::sendMultiple($notificationInfo);
    }

    public function sendTenderVerificationNotification(Tender $tender, $tenderProcess, $viewName, $tabId) {
        if(!$tenderProcess->latestVerifier->first()) return;
        $latestVerifier = $tenderProcess->latestVerifier->first();

        $routeName = (is_null($latestVerifier->getAssignedCompany($tender->project)) && $latestVerifier->isTopManagementVerifier()) ? 'topManagementVerifiers.projects.tender.show' : 'projects.tender.show';

        $url = route($routeName, array($tender->project->id, $tender->id), false) . $tabId;
        $this->processNotifications(array($latestVerifier), $url, $viewName, $tenderProcess->updatedBy->id);
    }

    public function sendVerificationResponseNotificationTechnicalAssessment(Tender $tender, Verifiable $object) {
        $requestor = User::find($object->submitted_by);
        $url = route('technicalEvaluation.assessment.confirm', array($tender->project->id, $tender->id));
        $viewName = 'technical_assessment.approved';
        $this->processNotifications(array($requestor), $url, $viewName, \Confide::user()->id);
    }

    public function sendRfvNotification($recipient, $project, $requestForVariation, $viewName, $responder) {
        $url = route('requestForVariation.form.show', [$project->id, $requestForVariation->id]);
        $this->processNotifications([$recipient], $url, $viewName, $responder->id);
    }

    public function sendLetterOfAwardNotification($recipient, $route, $viewName, $sender) {
        $this->processNotifications([$recipient], $route, $viewName, $sender->id);
    }

    private function processNotifications($recipients, $url, $viewName, $sendByUserId = null)
    {
        $sender           = $this->getSenderInformation($sendByUserId);
        $notificationInfo = array();
        $currentTime      = Carbon::now();
        
        $view = \View::make("notifications.system.{$viewName}");

        foreach($recipients as $recipient)
        {
            // Don't send notification to self.
            if( $sender['id'] == $recipient['id'] ) continue;

            $notificationInfo[] = array(
                'from_id'     => $sender['id'], // ID user that send the notification
                'from_type'   => 'PCK\Users\User', // Type of model used for polymorphic relation
                'to_id'       => $recipient['id'], // ID user that receive the notification
                'to_type'     => 'PCK\Users\User',  // Type of model used for polymorphic relation
                'category_id' => 1, // category notification ID
                'url'         => $url, // Url of your notification
                'extra'       => $view,
                'created_at'  => $currentTime,
                'updated_at'  => $currentTime,
            );
        }
        
        if( ! empty( $notificationInfo ) ) \Notifynder::sendMultiple($notificationInfo);
    }

    public function getSenderInformation($sendByUserId = null)
    {
        if( $sendByUserId ) return User::findOrFail($sendByUserId);

        return \Confide::user();
    }
}