<?php namespace PCK\EmailReminder;

use Illuminate\Database\Eloquent\Model;
use PCK\Base\ModuleAttachmentTrait;

class EmailReminder extends Model
{
    use ModuleAttachmentTrait;

    protected $table = 'e_bidding_email_reminders';

    protected $fillable = [
        'ebidding_id',
        'subject',
        'message',
        'subject2',
        'message2',
        'status_preview_start_time',
        'status_bidding_start_time',
        'created_by',
    ];

    const NOT_SENT  = 0;
    const SENT      = 1;
    const DRAFT     = 2;

    public function createdBy()
	{
		return $this->belongsTo('PCK\Users\User', 'created_by');
	}

    public function recipients()
    {
        return $this->hasMany('PCK\EmailReminder\EmailReminderRecipient');
    }

    public function isNotSent()
    {
        return ($this->status == self::NOT_SENT);
    }

    public function isSent()
    {
        return ($this->status == self::SENT);
    }

    public function isDraft()
    {
        return ($this->status == self::DRAFT);
    }

    public function canEdit()
    {
        return ($this->status == self::DRAFT || $this->status == self::NOT_SENT);
    }
}

