<?php namespace PCK\EmailReminder;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use PCK\Users\User;

class EmailReminderRecipient extends Model
{
    protected $table = 'e_bidding_email_reminder_recipients';

    protected $fillable = ['email_reminder_id', 'user_id', 'role'];

    const ROLE_BIDDER = 'bidder';
    const ROLE_COMMITTEE = 'committee';

    public function emailReminder()
    {
        return $this->belongsTo('PCK\EmailReminder\EmailReminder');
    }

    public function user()
    {
        return $this->belongsTo('PCK\Users\User','user_id');
    }
}

