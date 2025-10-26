<?php namespace PCK\RequestForInformation;

use Illuminate\Database\Eloquent\Collection;
use PCK\DirectedTo\DirectedTo;
use PCK\Helpers\Mailer;
use PCK\Notifications\SystemNotifier;

trait VerifierProcessTrait {

    public function getOnApprovedView()
    {
        return 'rfi.approved';
    }

    public function getOnRejectedView()
    {
        return 'rfi.rejected';
    }

    public function getOnPendingView()
    {
        return 'rfi.pending';
    }

    public function getRoute()
    {
        return route('requestForInformation.show', array( $this->requestForInformation->project->id, $this->requestForInformation->id ));
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

        if( $this->composer->stillInSameAssignedCompany($this->requestForInformation->project, $this->created_at) )
        {
            $users[] = $this->composer;
        }

        return $users;
    }

    public function getOnRejectedNotifyList()
    {
        return $this->getOnApprovedNotifyList();
    }

    public function getOnApprovedFunction()
    {
        $object = $this;

        return function() use ($object)
        {
            $allRecipients = new Collection();
            $project       = $object->requestForInformation->project;

            foreach(DirectedTo::getTargets($object) as $contractGroup)
            {
                $allRecipients = $allRecipients->merge($project->getCompanyByGroup($contractGroup->group)->getActiveUsers());
            }

            $systemRecipients = $allRecipients->reject(function($user) use ($project)
            {
                return ( ! $user->assignedToProject($project) );
            });

            $emailRecipients = $allRecipients->reject(function($user) use ($project)
            {
                return ( ! $user->isEditor($project) );
            });

            $emailView  = 'notifications.email.rfi.requestPosted';
            $systemView = 'notifications.system.rfi.requestPosted';

            if( $object->type == RequestForInformationMessage::TYPE_RESPONSE )
            {
                $systemRecipients = $object->requestForInformation->getLastRequest()->composer->getAssignedCompany($project, $object->created_at)->getActiveUsers()
                    ->reject(function($user) use ($project)
                    {
                        return ( ! $user->assignedToProject($project) );
                    });

                $emailRecipients = $systemRecipients->reject(function($user) use ($project)
                {
                    return ( ! $user->isEditor($project) );
                });

                $emailView  = 'notifications.email.rfi.responsePosted';
                $systemView = 'notifications.system.rfi.responsePosted';
            }

            Mailer::queueMultiple($emailRecipients, null, $emailView, trans('email.eProjectNotification'), array( 'project' => $project, 'route' => $this->getRoute() ));

            SystemNotifier::send($systemRecipients, $this->getRoute(), $systemView, $object->composer);
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
        return $this->issuer_id;
    }

    public function getModuleName()
    {
        return trans('modules.requestForInformation');
    }
}