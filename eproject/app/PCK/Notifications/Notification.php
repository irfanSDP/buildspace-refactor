<?php namespace PCK\Notifications;

use PCK\Tenders\Tender;
use PCK\Projects\Project;
use Illuminate\Database\Eloquent\Model;

interface Notification {

    public function sendNotification(Project $project, Model $model, array $roles, $viewName, $routeName, $tabId = null);

    public function sendNotificationByUsers(Project $project, Model $model, array $users, $viewName, $routeName, $tabId = null);

    public function sendTenderVerifierNotification(Project $project, Tender $tender, array $users, $sendByUserId, $viewName, $routeName, $tabId = null);

    public function getGroupOwnerRecipientDetails(Project $project, array $roles);

    public function getSenderInformation();

    public function notify(array $recipients, $subject, $viewName, $route, $viewData = array(), Project $project = null, $sender = null);

}