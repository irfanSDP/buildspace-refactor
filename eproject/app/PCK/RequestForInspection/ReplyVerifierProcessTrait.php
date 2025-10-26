<?php namespace PCK\RequestForInspection;

use Illuminate\Database\Eloquent\Collection;
use PCK\DirectedTo\DirectedTo;
use PCK\Helpers\Mailer;
use PCK\Notifications\SystemNotifier;

trait ReplyVerifierProcessTrait {

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
        return route('requestForInspection.show', array( $this->inspection->request->project->id, $this->inspection->request->id ));
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

        if( $this->createdBy->stillInSameAssignedCompany($this->inspection->request->project, $this->created_at) )
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

            $allRecipients = new Collection();

            $project = $this->inspection->request->project;

            foreach(DirectedTo::getTargets($this) as $contractGroup)
            {
                $allRecipients = $allRecipients->merge($project->getCompanyByGroup($contractGroup->group)->getActiveUsers());
            }

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

            Mailer::queueMultiple($emailRecipients, null, 'notifications.email.requestForInspection.replyPosted', trans('email.eProjectNotification'), array( 'project' => $project, 'route' => $this->getRoute(), ));

            SystemNotifier::send($systemRecipients, $this->getRoute(), 'notifications.system.requestForInspection.replyPosted', $this->createdBy);
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