<?php namespace PCK\EmailNotification;

use PCK\Projects\Project;
use PCK\Base\BaseModuleRepository;
use PCK\EmailNotification\EmailNotification;
use PCK\EmailNotification\EmailNotificationRecipient;

class EmailNotificationRepository extends BaseModuleRepository
{
    public function getEmailsByStatus(Project $project, $filters)
    {
        $emails = $project->emailNotifications;

        if(isset($filters['status']) && !empty($filters['status']))
        {
            $status = $filters['status'];

            $emails = $emails->reject(function($email) use ($status) {
                return $email->status != $status;
            });
        }
        
        if(isset($filters['subject']) && !empty($filters['subject']))
        {
            $subject = trim($filters['subject']);

            $emails = $emails->reject(function($email) use ($subject) {
                return (stripos($email->subject, $subject) === false);
            });
        }

        if(isset($filters['message']) && !empty($filters['message']))
        {
            $message = trim($filters['message']);

            $emails = $emails->reject(function($email) use ($message) {
                return (stripos($email->message, $message) === false);
            });
        }

        if(isset($filters['author']) && !empty($filters['author']))
        {
            $author = trim($filters['author']);

            $emails = $emails->reject(function($email) use ($author) {
                return (stripos($email->createdBy->name, $author) === false);
            });
        }

        return $emails->isEmpty() ? null : $emails;
    }

    public function getUserGroupedByCompany(Project $project)
    {
        $tenderStage = $project->latestTender->getTenderStageInformation();

        if(is_null($tenderStage)) return [];

        $activeUsersByCompany = [];

        foreach($tenderStage->selectedContractors as $company)
        {
            $activeUsers = $company->getActiveUsers();
            $temp = [];

            foreach($activeUsers as $user)
            {
                array_push($temp, $user);
            }

            $activeUsersByCompany[$company->id]['name'] = $company->name;
            $activeUsersByCompany[$company->id]['users'] = $temp;
        }

        return $activeUsersByCompany;
    }

    public function createNewEmailNotifications(Project $project, $user, $inputs)
    {
        $emailNotification = new EmailNotification();
        $emailNotification->project_id = $project->id;
        $emailNotification->subject = $inputs['subject'];
        $emailNotification->message = $inputs['message'];
        $emailNotification->status = EmailNotification::DRAFT;
        $emailNotification->created_by = $user->id;
        $emailNotification->save();

        foreach($inputs['to_viewer'] as $userId)
        {
            $emailNotificationRecipient = new EmailNotificationRecipient();
            $emailNotificationRecipient->email_notification_id = $emailNotification->id;
            $emailNotificationRecipient->user_id = $userId;
            $emailNotificationRecipient->save();
        }

        $this->saveAttachments($emailNotification, $inputs);

        return $emailNotification;
    }

    public function updateEmailNotification($emailId, $inputs)
    {

        $emailNotification = EmailNotification::find($emailId);
        $emailNotification->subject = $inputs['subject'];
        $emailNotification->message = $inputs['message'];
        $emailNotification->status = isset($inputs['draft']) ? EmailNotification::DRAFT : EmailNotification::SENT;
        $emailNotification->save();

        $existingRecipientIds = array_column($emailNotification->recipients->toArray(), 'user_id');
        $newlyAddedRecipientIds = array_diff($inputs['to_viewer'], $existingRecipientIds);
        $newlyRemovedRecipientIds = array_diff($existingRecipientIds, $inputs['to_viewer']);

        EmailNotificationRecipient::updateRecipients($emailNotification, $newlyAddedRecipientIds, $newlyRemovedRecipientIds);

        $this->saveAttachments($emailNotification, $inputs);
    }
}

