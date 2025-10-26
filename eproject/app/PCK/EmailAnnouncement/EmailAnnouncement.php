<?php namespace PCK\EmailAnnouncement;

use Illuminate\Database\Eloquent\Model;
use PCK\Base\ModuleAttachmentTrait;

class EmailAnnouncement extends Model
{
    use ModuleAttachmentTrait;

    protected $table = 'email_announcements';

    const DRAFT = '1';
    const SENT  = '2';

    public function createdBy()
	{
		return $this->belongsTo('PCK\Users\User', 'created_by');
	}

    public function recipients()
    {
        return $this->hasMany('PCK\EmailAnnouncement\EmailAnnouncementRecipient');
    }

    public function getActiveRecipientsAttribute()
    {
        return $this->recipients->filter(function ($rec) {
            return $rec->user->isActive();
        });
    }

    public function isDraft()
    {
        return ($this->status == self::DRAFT);
    }
}

