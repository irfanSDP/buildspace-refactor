<?php namespace PCK\EmailAnnouncement;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use PCK\Users\User;

class EmailAnnouncementRecipient extends Model
{
    protected $table = 'email_announcement_recipients';

    public function emailAnnouncement()
    {
        return $this->belongsTo('PCK\EmailAnnouncement\EmailAnnouncement');
    }

    public function user()
    {
        return $this->belongsTo('PCK\Users\User');
    }

    public static function updateRecipients($emailAnnouncement, $contract_group_category_id, $toAddIds, $toRemoveIds)
    {
        DB::table('email_announcement_recipients')
            ->where('email_announcement_id', $emailAnnouncement->id)
            ->whereIn('user_id', $toRemoveIds)
            ->delete();

        foreach($toAddIds as $id)
        {
            $recipient = new self;
            $recipient->email_announcement_id = $emailAnnouncement->id;
            $recipient->contract_group_category_id = $contract_group_category_id;
            $recipient->user_id = $id;
            $recipient->save();
        }
    }
}

