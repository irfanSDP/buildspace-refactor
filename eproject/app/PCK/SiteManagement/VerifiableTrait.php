<?php namespace PCK\SiteManagement;

use PCK\Helpers\Mailer;
use PCK\Notifications\SystemNotifier;

trait VerifiableTrait {

    public function getOnApprovedView()
    {
        return 'siteManagementBackcharge.approved';
    }

    public function getOnRejectedView()
    {
        return 'siteManagementBackcharge.rejected';
    }

    public function getOnPendingView()
    {
        return 'siteManagementBackcharge.pending';
    }

    public function getRoute()
    {
        return route('site-management-defect.showBackcharge', array( $this->siteManagementDefect->project_id, $this->id ));
    }

    public function getViewData($locale)
    {
        return array(
            'route' => $this->getRoute(),
        );
    }

    public function getOnApprovedNotifyList()
    {
        $project = $this->siteManagementDefect->project;

        $siteManagementDefect = SiteManagementDefect::find($this->siteManagementDefect->id);

        $selectedPIC = $siteManagementDefect->user;

        $recipients = SiteManagementUserPermission::getAssignedQs($project,SiteManagementUserPermission::MODULE_IDENTIFIER_DEFECT);

        if( $selectedPIC && $selectedPIC->stillInSameAssignedCompany($project, $this->created_at) )
        {
            $recipients = $recipients->add($selectedPIC);
        }

        return $recipients;
    }

    public function getOnRejectedNotifyList()
    {
        return $this->getOnApprovedNotifyList();
    }

    public function getOnApprovedFunction()
    {
        return function()
        {
            $project = $this->siteManagementDefect->project;

            $record = SiteManagementDefect::find($this->siteManagementDefect->id);
            $record->status_id = SiteManagementDefect::STATUS_BACKCHARGE_SUBMITTED;
            $record->save();

            $defectBackchargeDetail = SiteManagementDefectBackchargeDetail::find($this->id);
            $defectBackchargeDetail->status_id = SiteManagementDefectBackchargeDetail::STATUS_BACKCHARGE_SUBMITTED;
            $defectBackchargeDetail->save();

            $recipients = SiteManagementUserPermission::getAssignedQs($project,SiteManagementUserPermission::MODULE_IDENTIFIER_DEFECT);

            $siteManagementDefectRepository = \App::make("PCK\SiteManagement\SiteManagementDefectRepository") ;
            $siteManagementDefectRepository->sendBackchargeApprovedNotificationToContractor($project, $record);

            Mailer::queueMultiple($recipients, null, 'notifications.email.siteManagementBackcharge', trans('email.eProjectNotification'), array( 'project' => $project, 'route' => $this->getRoute(), ));

            SystemNotifier::send($recipients, $this->getRoute(), 'notifications.system.siteManagementBackcharge', $this->createdBy);
        };
    }

    public function getOnRejectedFunction()
    {
        return function()
        {
            $project = $this->siteManagementDefect->project;

            $record = SiteManagementDefect::find($this->siteManagementDefect->id);
            $record->status_id = SiteManagementDefect::STATUS_BACKCHARGE_REJECTED;
            $record->save();

            $defectBackchargeDetail = SiteManagementDefectBackchargeDetail::find($this->id);
            $defectBackchargeDetail->status_id = SiteManagementDefectBackchargeDetail::STATUS_BACKCHARGE_REJECTED;
            $defectBackchargeDetail->save();

            $siteManagementDefectRepository = \App::make("PCK\SiteManagement\SiteManagementDefectRepository") ;
            $siteManagementDefectRepository->sendBackchargeRejectedNotificationToContractor($project, $record);
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
        return trans('modules.siteManagement');
    }
}