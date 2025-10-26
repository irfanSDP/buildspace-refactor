<?php namespace PCK\RiskRegister;

use Illuminate\Database\Eloquent\Collection;
use PCK\DirectedTo\DirectedTo;
use PCK\Helpers\Mailer;
use PCK\Notifications\SystemNotifier;

trait VerifierProcessTrait {

    public function getOnApprovedView()
    {
        return 'risk_register.approved';
    }

    public function getOnRejectedView()
    {
        return 'risk_register.rejected';
    }

    public function getOnPendingView()
    {
        return 'risk_register.pending';
    }

    public function getRoute()
    {
        return route('riskRegister.show', array( $this->riskRegister->project->id, $this->riskRegister->id ));
    }

    public function getViewData($locale)
    {
        return array( 'route' => $this->getRoute() );
    }

    public function getOnApprovedNotifyList()
    {
        $users = array();

        if( $this->composer->stillInSameAssignedCompany($this->riskRegister->project, $this->created_at) )
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
            $object->moveToThreadEnd();

            $allRecipients = new Collection();
            $project       = $object->riskRegister->project;

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

            $emailView  = 'notifications.email.risk_register.riskPosted';
            $systemView = 'notifications.system.risk_register.riskPosted';

            if( $object->type == RiskRegisterMessage::TYPE_COMMENT )
            {
                $systemRecipients = $object->riskRegister->getLatestRisk()->composer->getAssignedCompany($project, $object->created_at)->getActiveUsers()
                    ->reject(function($user) use ($project)
                    {
                        return ( ! $user->assignedToProject($project) );
                    });

                $emailRecipients = $systemRecipients->reject(function($user) use ($project)
                {
                    return ( ! $user->isEditor($project) );
                });

                $emailView  = 'notifications.email.risk_register.commentPosted';
                $systemView = 'notifications.system.risk_register.commentPosted';
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
        return trans('modules.riskRegister');
    }
}