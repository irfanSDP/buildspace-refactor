<?php

use PCK\Projects\Project;
use PCK\Users\User;
use PCK\EmailNotification\EmailNotification;
use PCK\EmailNotification\EmailNotificationRepository;
use PCK\Forms\EmailNotificationForm;
Use PCK\Helpers\Mailer;

class EmailNotificationsController extends \BaseController
{
    private $repository;
    private $validationForm;

    public function __construct(EmailNotificationRepository $repository, EmailNotificationForm $validationForm)
    {
        $this->repository = $repository;
        $this->validationForm = $validationForm;
    }

    public function index(Project $project)
    {
        $user   = Confide::user();
        $inputs = Input::all();
        
        $emails = $this->repository->getEmailsByStatus($project, $inputs);

        return View::make('email_notifications.partials.listing', compact('emails'))->render();
    }

    public function show(Project $project, $emailId)
    {
        $user = \Confide::user();
        $email = EmailNotification::find($emailId);
        $uploadedFiles = $this->getAttachmentDetails($email);

        return View::make('email_notifications.show', compact('project', 'user', 'email', 'uploadedFiles'))->render();
    }

    public function create(Project $project)
    {
        $user                  = \Confide::user();
        $users                 = User::all();
        $usersGroupedByCompany = $this->repository->getUserGroupedByCompany($project);

        return View::make('email_notifications.create', compact('user', 'users', 'project', 'usersGroupedByCompany'))->render();
    }

    public function store(Project $project)
    {
        $inputs = Input::all();
        $user = Confide::user();

        $this->validationForm->validate($inputs);

        $emailNotification = $this->repository->createNewEmailNotifications($project, $user, $inputs);

        if(!isset($inputs['draft']))
        {
            $this->sendEmail($emailNotification->id);
            $emailNotification->status = EmailNotification::SENT;
            $emailNotification->save();
        }

        $success = true;

        return Response::json(compact('success'));
    }

    public function edit(Project $project, $emailId)
    {
        $user = Confide::user();
        $emailNotification = EmailNotification::find($emailId);
        $selectedRecipientIds = array_column($emailNotification->recipients->toArray(), 'user_id');
        $uploadedFiles    = $this->getAttachmentDetails($emailNotification);
        $usersGroupedByCompany = $this->repository->getUserGroupedByCompany($project);

        return View::make('email_notifications.edit', compact('user', 'project', 'emailNotification', 'selectedRecipientIds', 'uploadedFiles', 'usersGroupedByCompany'))->render();
    }

    public function update(Project $project, $emailId)
    {
        $inputs = Input::all();

        $this->validationForm->validate($inputs);
        
        $this->repository->updateEmailNotification($emailId, $inputs);

        if(!isset($inputs['draft']))
        {
            $emailNotification = EmailNotification::find($emailId);

            $this->sendEmail($emailNotification->id);
            
            $emailNotification->status = EmailNotification::SENT;
            $emailNotification->save();
        }

        $success = true;

        return Response::json(compact('success'));
    }

    public function destroy(Project $project, $emailId)
    {
        EmailNotification::find($emailId)->delete();

        return Redirect::route('projects.show', $project->id);
    }

    private function sendEmail($emailId)
    {
        $emailNotification = EmailNotification::find($emailId);

        $mailer = new Mailer($emailNotification->subject, 'email_notifications.partials.email_notification_message', array(
            'messageContent' => $emailNotification->message,
        ));

        $listOfRecipients = [];
        
        foreach($emailNotification->recipients as $recipient)
        {
            array_push($listOfRecipients, $recipient->user);
        }

        $mailer->setRecipients($listOfRecipients);

        foreach($emailNotification->attachments as $attachment)
        {
            $fullpath = base_path() .$attachment->file->path . $attachment->file->filename;

            $mailer->addAttachment($fullpath, $attachment->file->filename);
        }

        $mailer->send();
    }
}
