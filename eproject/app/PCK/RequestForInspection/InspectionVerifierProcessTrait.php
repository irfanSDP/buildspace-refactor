<?php namespace PCK\RequestForInspection;

use PCK\Helpers\Mailer;
use PCK\Notifications\SystemNotifier;

trait InspectionVerifierProcessTrait {

    public function getOnApprovedView()
    {
        return 'requestForInspection.approved';
    }

    public function getOnRejectedView()
    {
        return 'requestForInspection.rejected';
    }

    public function getOnPendingView()
    {
        return 'requestForInspection.pending';
    }

    public function getRoute()
    {
        return route('requestForInspection.show', array( $this->request->project->id, $this->request->id ));
    }

    public function getViewData($locale)
    {
        return array(
            'route' => $this->getRoute(),
        );
    }

    public function getOnApprovedNotifyList()
    {
        $users = array();

        if( $this->createdBy->stillInSameAssignedCompany($this->request->project, $this->created_at) )
        {
            $users[] = $this->createdBy;
        }

        return $users;
    }

    public function getOnRejectedNotifyList()
    {
        return $this->getOnApprovedNotifyList();
    }

    public function getOnApprovedFunction()
    {
        return function()
        {
            $this->updateRequestStatus();

            $project = $this->request->project;

            $allRecipients = $this->request->createdBy->getAssignedCompany($this->request->project)->getActiveUsers();

            $systemRecipients = $allRecipients
                ->reject(function($user) use ($project)
                {
                    return ( ! $user->assignedToProject($project) );
                });

            $emailRecipients = $allRecipients
                ->reject(function($user) use ($project)
                {
                    return ( ! $user->isEditor($project) );
                });

            Mailer::queueMultiple($emailRecipients, null, 'notifications.email.requestForInspection.inspectionPosted', trans('email.eProjectNotification'), array( 'project' => $project, 'route' => $this->getRoute(), ));

            SystemNotifier::send($systemRecipients, $this->getRoute(), 'notifications.system.requestForInspection.inspectionPosted', $this->createdBy);
        };
    }

    public function getOnRejectedFunction()
    {
    }

    public function onReview()
    {
    }
    
    public function getEmailSubject($locale) 
    {
        return "";
    }

    public function getSubmitterId()
    {
        return null;
    }

    public function getModuleName()
    {
        return trans('modules.requestForInspection');
    }
}