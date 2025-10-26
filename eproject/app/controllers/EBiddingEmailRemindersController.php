<?php

use PCK\EmailReminder\EmailReminder;
use PCK\EmailReminder\EmailReminderRepository;
use PCK\Forms\EmailReminderForm;
Use PCK\Helpers\Mailer;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\MessageBag;
use PCK\EBiddings\EBidding;
use PCK\EBiddingCommittees\EBiddingCommittee; 
use PCK\EmailReminder\EmailReminderRecipient;

class EBiddingEmailRemindersController extends \BaseController
{
    private $repository;
    private $validationForm;

    public function __construct(EmailReminderRepository $repository, EmailReminderForm $validationForm)
    {
        $this->repository = $repository;
        $this->validationForm = $validationForm;
    }

    public function create($project)
    {
        return View::make('open_tenders.e_biddings.email_reminders.create', compact('project'))->render();
    }

    public function store($project)
    {
        $inputs = Input::all();
        $user   = Confide::user();
        $eBidding   = EBidding::where('project_id',$project->id)->first();
        $eBiddingId = $eBidding->id;

        $this->validationForm->validate($inputs);

        $record = $this->repository->create(['ebidding_id' => $eBiddingId, 'created_by' => $user->id]);
        if ($record) {
            $this->repository->update($record->id, [
                'subject' => $inputs['subject'],
                'message' => $inputs['message'],
                'subject2' => $inputs['subject2'],
                'message2' => $inputs['message2'],
            ]);
        }

        $success = true;

        return Response::json(compact('success'));
    }

    /*public function edit($project, $emailId)
    {
        $emailReminder = EmailReminder::find($emailId);

        return View::make('open_tenders.e_biddings.email_reminders.edit', compact('project', 'emailReminder'))->render();
    }*/

    public function update($project, $recordId)
    {
        $result = ['success' => false, 'message' => null];

        $inputs = Input::all();

        $this->validationForm->validate($inputs);

        $result['success'] = $this->repository->update($recordId, [
            'subject' => $inputs['subject'],
            'message' => $inputs['message'],
            'subject2' => $inputs['subject2'],
            'message2' => $inputs['message2'],
            'status_preview_start_time' => EmailReminder::DRAFT,
            'status_bidding_start_time' => EmailReminder::DRAFT,
        ]);
        if ($result['success']) {
            $result['message'] = trans('forms.updateSuccessful');
        } else {
            $result['message'] = trans('errors.unableToProceed');
        }

        return Response::json($result);
    }
}
