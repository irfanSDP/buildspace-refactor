<?php namespace PCK\EmailNotification;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use PCK\Projects\Project;
use PCK\Users\User;

class EmailNotificationRecipient extends Model
{
    protected $table = 'email_notification_recipients';

    public function emailNotification()
    {
        return $this->belongsTo('PCK\EmailNotification\EmailNotification');
    }

    public function user()
    {
        return $this->belongsTo('PCK\Users\User');
    }

    public static function updateRecipients($emailNotification, $toAddIds, $toRemoveIds)
    {
        DB::table('email_notification_recipients')
            ->where('email_notification_id', $emailNotification->id)
            ->whereIn('user_id', $toRemoveIds)
            ->delete();

        foreach($toAddIds as $id)
        {
            $recipient = new self;
            $recipient->email_notification_id = $emailNotification->id;
            $recipient->user_id = $id;
            $recipient->save();
        }
    }
}

