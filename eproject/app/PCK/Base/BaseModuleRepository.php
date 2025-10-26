<?php namespace PCK\Base;

use PCK\Helpers\ModuleAttachment;
use PCK\Tenders\Tender;
use PCK\Projects\Project;
use Illuminate\Database\Eloquent\Model;
use PCK\Users\User;

abstract class BaseModuleRepository {

    protected function checkEventsProperty()
    {
        if( ! isset ( $this->events ) or ! is_object($this->events) )
        {
            throw new \InvalidArgumentException('Please set the $events property.');
        }
    }

    protected function sendEmailNotificationForStatusConfirmation($projectId, $keysByRecipientId, $tenderId, $tenderStage, $emailDetails)
    {
        $this->checkEventsProperty();

        return $this->events->fire('system.sendEmailNotificationForConfirmationStatus', compact('projectId', 'keysByRecipientId', 'tenderId', 'tenderStage', 'emailDetails'));
    }

    protected function sendEmailNotificationForStatusConfirmationReply($projectId, $replyDetails)
    {
        $this->checkEventsProperty();

        return $this->events->fire('system.sendEmailNotificationForConfirmationStatusReply', compact('projectId', 'replyDetails'));
    }

    public function sendEmailNotification(Project $project, Model $model, array $roles, $viewName, $routeName, $tabId = null)
    {
        $this->checkEventsProperty();

        return $this->events->fire('system.sendEmailNotification', compact(
            'project', 'model', 'roles', 'viewName', 'routeName', 'tabId'
        ));
    }

    public function sendEmailNotificationByUsers(Project $project, Model $model, array $users, $viewName, $routeName, $tabId = null)
    {
        $this->checkEventsProperty();

        return $this->events->fire('system.sendEmailNotificationByUsers', compact(
            'project', 'model', 'users', 'viewName', 'routeName', 'tabId'
        ));
    }

    protected function sendSystemNotification(Project $project, Model $model, array $roles, $viewName, $routeName, $tabId = null)
    {
        $this->checkEventsProperty();

        return $this->events->fire('system.sendSystemNotification', compact(
            'project', 'model', 'roles', 'viewName', 'routeName', 'tabId'
        ));
    }

    protected function sendSystemNotificationByUsers(Project $project, Model $model, array $users, $viewName, $routeName, $tabId = null)
    {
        $this->checkEventsProperty();

        return $this->events->fire('system.sendSystemNotificationByUsers', compact(
            'project', 'model', 'users', 'viewName', 'routeName', 'tabId'
        ));
    }

    public function sendTenderNotification(Tender $tender, array $users, $sendByUserId = null, $viewName, $routeName, $tabId = null)
    {
        $this->checkEventsProperty();

        $project = $tender->project;

        return $this->events->fire('tenderForms.sendNotification', compact(
            'project', 'tender', 'users', 'sendByUserId', 'viewName', 'routeName', 'tabId'
        ));
    }

    // a defer method to correctly name it's purpose
    public function sendContractorAdminUserEmailNotification(Tender $tender, array $users, $viewName, $routeName, $tabId = null)
    {
        $this->sendTenderNotification($tender, $users, null, $viewName, $routeName, $tabId);
    }

    public function sendVerifierSystemNotification(Tender $tender, array $users, $viewName, $routeName, $tabId = null)
    {
        $this->checkEventsProperty();

        $project = $tender->project;
        $sendByUserId = null;

        return $this->events->fire('tenderForms.sendSystemNotification', compact(
            'project', 'tender', 'users', 'sendByUserId', 'viewName', 'routeName', 'tabId'
        ));
    }

    protected function saveAttachments(Model $model, array $inputs)
    {
        return ModuleAttachment::saveAttachments($model, $inputs);
    }

}