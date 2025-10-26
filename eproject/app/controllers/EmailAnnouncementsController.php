<?php

use PCK\Users\User;
use PCK\EmailAnnouncement\EmailAnnouncement;
use PCK\EmailAnnouncement\EmailAnnouncementRepository;
use PCK\Forms\EmailAnnouncementForm;
use Illuminate\Support\Facades\Redirect;
use PCK\QueueJobs\EmailAnnouncementSendAsync;

class EmailAnnouncementsController extends \BaseController
{
    private $repository;
    private $validationForm;

    public function __construct(EmailAnnouncementRepository $repository, EmailAnnouncementForm $validationForm)
    {
        $this->repository = $repository;
        $this->validationForm = $validationForm;
    }

    public function index()
    {
        $user   = Confide::user();
        $inputs = Input::all();
        $emails = $this->repository->getEmailsByStatus($inputs);

        return View::make('email_announcements.partials.listing', compact('emails'))->render();
    }

    public function show($emailId)
    {
        $user =  Confide::user();
        $email = EmailAnnouncement::find($emailId);
        $uploadedFiles = $this->getAttachmentDetails($email);

        $emailRecipientNames = [];
        $emailRecipientNamesToDisplay = 5;
        $count = 0;

        foreach($email->activeRecipients as $recipient)
        {
            if ($count >= $emailRecipientNamesToDisplay) {
                break;
            }

            $emailRecipientNames[] = $recipient->user->name;
            ++$count;
        }

        $displayShowUsersButton = $email->activeRecipients->count() > $emailRecipientNamesToDisplay;
        $remainderUsersCount    = $email->activeRecipients->count() - $emailRecipientNamesToDisplay;

        return View::make('email_announcements.show', compact('user', 'email', 'uploadedFiles', 'emailRecipientNames', 'displayShowUsersButton', 'remainderUsersCount'))->render();
    }

    public function create()
    {
        $user     = Confide::user();
        $users    = User::all();
        $groups   = $this->repository->getContractGroupCategories();

        return View::make('email_announcements.create', compact('user', 'users', 'groups'))->render();
    }

    public function store()
    {
        $inputs = Input::all();
        $user   = Confide::user();

        $this->validationForm->validate($inputs);

        $emailAnnouncement = $this->repository->createNewEmailAnnouncements($user, $inputs);

        if(!isset($inputs['draft']))
        {
            $this->sendEmail($emailAnnouncement->id);
            $emailAnnouncement->status = EmailAnnouncement::SENT;
            $emailAnnouncement->save();
        }

        $success = true;

        return Response::json(compact('success'));
    }

    public function edit($emailId)
    {
        $user = Confide::user();
        $emailAnnouncement = EmailAnnouncement::find($emailId);
        $selectedGroupIds = array_column($emailAnnouncement->recipients->toArray(), 'contract_group_category_id');
        $uploadedFiles    = $this->getAttachmentDetails($emailAnnouncement);
        $groups = $this->repository->getContractGroupCategories();

        return View::make('email_announcements.edit', compact('user', 'emailAnnouncement', 'selectedGroupIds','uploadedFiles', 'groups'))->render();
    }

    public function update($emailId)
    {
        $inputs = Input::all();

        $this->validationForm->validate($inputs);
        
        $this->repository->updateEmailAnnouncement($emailId, $inputs);

        if(!isset($inputs['draft']))
        {
            $emailAnnouncement = EmailAnnouncement::find($emailId);

            $this->sendEmail($emailAnnouncement->id);
            
            $emailAnnouncement->status = EmailAnnouncement::SENT;
            $emailAnnouncement->save();
        }

        $success = true;

        return Response::json(compact('success'));
    }

    public function destroy($emailId)
    {
        EmailAnnouncement::find($emailId)->delete();

        return Redirect::route('email_announcements.main');
    }

    private function sendEmail($emailAnnouncementId)
    {
        \Queue::push(EmailAnnouncementSendAsync::class, ['email_announcement_id' => $emailAnnouncementId], 'default');
    }

    public function main()
    {
        $user = Confide::user();

        JavaScript::put(array(
            'getEmailAnnouncementsURL'   => route('email_announcements'),
            'createEmailAnnouncementURL' => route('email_announcements.create'),
        ));

        return View::make('email_announcements.main', compact('user'));
    }
}
