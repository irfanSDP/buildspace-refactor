<?php namespace PCK\EmailAnnouncement;

use PCK\Users\User;
use PCK\Companies\Company;
use PCK\ContractGroupCategory\ContractGroupCategory;
use PCK\Base\BaseModuleRepository;
use PCK\EmailAnnouncement\EmailAnnouncement;
use PCK\EmailAnnouncement\EmailAnnouncementRecipient;

class EmailAnnouncementRepository extends BaseModuleRepository
{
    public function getEmailsByStatus($filters)
    {
        $query = EmailAnnouncement::query();

        if(isset($filters['status']) && !empty($filters['status']))
        {
            $query->where('status', $filters['status']);
        }
        
        if(isset($filters['subject']) && !empty($filters['subject']))
        {
            $query->where('subject', 'like', '%' . trim($filters['subject']) . '%');
        }

        if(isset($filters['message']) && !empty($filters['message']))
        {
            $query->where('message', 'like', '%' . trim($filters['message']) . '%');
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

    public function getContractGroupCategories()
    {
        $contractGroupCategories = ContractGroupCategory::all();

        $groups = [];
        foreach($contractGroupCategories as $contractGroupCategory){
            $companies = Company::where('contract_group_category_id', $contractGroupCategory->id)->get();

            foreach ($companies as $company) {
                $users = User::where('company_id', $company->id)->where('confirmed', true)->where('account_blocked_status', false)->get();

                if ($users->count() > 0) {
                    $groups[$contractGroupCategory->id]['name'] = $contractGroupCategory->name;
                    break;
                }

            }
        }
        return $groups;
    }

    public function createNewEmailAnnouncements($user, $inputs)
    {
        $emailAnnouncement = new EmailAnnouncement();
        $emailAnnouncement->subject = $inputs['subject'];
        $emailAnnouncement->message = $inputs['message'];
        $emailAnnouncement->status = EmailAnnouncement::DRAFT;
        $emailAnnouncement->created_by = $user->id;
        $emailAnnouncement->save();

        foreach($inputs['to_viewer'] as $contract_group_category_id)
        {
            $companies = Company::where('contract_group_category_id', $contract_group_category_id)->get();

            foreach ($companies as $company) {
                $activeUsers = $company->getActiveUsers();

                foreach ($activeUsers as $user) {
                    $emailAnnouncementRecipient = new EmailAnnouncementRecipient();
                    $emailAnnouncementRecipient->email_announcement_id = $emailAnnouncement->id;
                    $emailAnnouncementRecipient->contract_group_category_id = $contract_group_category_id;
                    $emailAnnouncementRecipient->user_id = $user->id;
                    $emailAnnouncementRecipient->save();
                }
            }
        }

        $this->saveAttachments($emailAnnouncement, $inputs);

        return $emailAnnouncement;
    }

    public function updateEmailAnnouncement($emailId, $inputs)
    {

        $emailAnnouncement = EmailAnnouncement::find($emailId);
        $emailAnnouncement->subject = $inputs['subject'];
        $emailAnnouncement->message = $inputs['message'];
        $emailAnnouncement->status = isset($inputs['draft']) ? EmailAnnouncement::DRAFT : EmailAnnouncement::SENT;
        $emailAnnouncement->save();
        
        $UsersID = [];
        foreach($inputs['to_viewer'] as $contract_group_category_id)
        {
            $companies = Company::where('contract_group_category_id', $contract_group_category_id)->get();

            foreach ($companies as $company) {
                $activeUsers = $company->getActiveUsers();

                foreach ($activeUsers as $user) {
                    $UsersID[] = $user->id;
                }
            }
        }

        $existingRecipientIds = array_column($emailAnnouncement->recipients->toArray(), 'user_id');
        $newlyAddedRecipientIds = array_diff($UsersID, $existingRecipientIds);
        $newlyRemovedRecipientIds = array_diff($existingRecipientIds, $UsersID);

        EmailAnnouncementRecipient::updateRecipients($emailAnnouncement, $contract_group_category_id, $newlyAddedRecipientIds, $newlyRemovedRecipientIds);

        $this->saveAttachments($emailAnnouncement, $inputs);
    }
}

