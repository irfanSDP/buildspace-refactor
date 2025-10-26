<?php namespace PCK\IndonesiaCivilContract\ArchitectInstruction;

use PCK\Helpers\Mailer;
use PCK\Notifications\SystemNotifier;

trait VerifiableTrait {

    public function getOnApprovedView()
    {
        return 'architectInstruction.approved';
    }

    public function getOnRejectedView()
    {
        return 'architectInstruction.rejected';
    }

    public function getOnPendingView()
    {
        return 'architectInstruction.pending';
    }

    public function getRoute()
    {
        return route('indonesiaCivilContract.architectInstructions.show', array( $this->project->id, $this->id ));
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

        if( $this->createdBy->stillInSameAssignedCompany($this->project, $this->created_at) )
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
        return function()
        {
            $project = $this->project;

            $this->status = self::STATUS_SUBMITTED;
            $this->save();

            $recipients = $project->getSelectedContractor()->getAllUsers();

            Mailer::queueMultiple($recipients, null, 'notifications.email.architect_instruction', trans('email.eProjectNotification'), array( 'project' => $project, 'toRoute' => $this->getRoute(), 'senderName' => $this->createdBy->name ));

            SystemNotifier::send($recipients, $this->getRoute(), 'notifications.system.architect_instruction', $this->createdBy);
        };
    }

    public function getOnRejectedFunction()
    {
        return function()
        {
            $this->status = self::STATUS_DRAFT;
            $this->save();
        };
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
        return trans('modules.architectInstruction');
    }
}