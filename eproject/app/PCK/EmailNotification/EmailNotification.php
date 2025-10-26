<?php namespace PCK\EmailNotification;

use Illuminate\Database\Eloquent\Model;
use PCK\Base\ModuleAttachmentTrait;

class EmailNotification extends Model
{
    use ModuleAttachmentTrait;

    protected $table = 'email_notifications';

    const DRAFT = '1';
    const SENT  = '2';

    public function project()
    {
        return $this->belongsTo('PCK\Projects\Project');
    }

    public function createdBy()
	{
		return $this->belongsTo('PCK\Users\User', 'created_by');
	}

    public function recipients()
    {
        return $this->hasMany('PCK\EmailNotification\EmailNotificationRecipient');
    }

    public function isDraft()
    {
        return ($this->status == self::DRAFT);
    }
}

