<?php namespace PCK\EmailReminder;

use PCK\Base\BaseModuleRepository;

class EmailReminderRepository extends BaseModuleRepository
{
    public function getRecord($eBiddingId)
    {
        return EmailReminder::where('ebidding_id', $eBiddingId)->first();
    }

    public function getEmailsByStatus($filters)
    {
        $query = EmailReminder::query();

        if(isset($filters['status']) && !empty($filters['status']))
        {
            $query->where('status', $filters['status']);
        }

        $searchableFields = ['subject', 'message', 'subject2', 'message2'];

        foreach ($searchableFields as $field) {
            if (! empty($filters[$field])) {
                $query->where($field, 'like', '%' . trim($filters[$field]) . '%');
            }
        }

        if(isset($filters['author']) && !empty($filters['author']))
        {
            $query->whereHas('createdBy', function ($q) use ($filters) {
                $q->where('name', 'like', '%' . trim($filters['author']) . '%');
            });
        }

        $emails = $query->get();
        return $emails->isEmpty() ? null : $emails;
    }

    public function create($data)
    {
        $emailReminder = new EmailReminder();
        $emailReminder->ebidding_id = $data['ebidding_id'];
        $emailReminder->subject = trans('eBiddingReminder.subjectPreview');
        $emailReminder->message = trans('eBiddingReminder.messagePreview');
        $emailReminder->subject2 = trans('eBiddingReminder.subjectBidding');
        $emailReminder->message2 = trans('eBiddingReminder.messageBidding');
        $emailReminder->status_preview_start_time = EmailReminder::DRAFT;
        $emailReminder->status_bidding_start_time = EmailReminder::DRAFT;
        $emailReminder->created_by = $data['created_by'];
        $emailReminder->save();

        return $emailReminder;
    }

    public function update($recordId, $updateData)
    {
        EmailReminder::where('id', $recordId)->update($updateData);
        return true;
    }

    public function deleteRecipients($emailReminderId)
    {
        if (EmailReminderRecipient::where('email_reminder_id', $emailReminderId)->count() > 0)
        {
            EmailReminderRecipient::where('email_reminder_id', $emailReminderId)->delete();
        }
        return true;
    }
}

