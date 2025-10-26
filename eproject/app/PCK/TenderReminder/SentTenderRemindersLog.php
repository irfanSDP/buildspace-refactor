<?php namespace PCK\TenderReminder;

use Illuminate\Database\Eloquent\Model;
use Laracasts\Presenter\PresentableTrait;
use PCK\Base\TimestampFormatterTrait;
use PCK\Tenders\Tender;
use PCK\Users\User;

class SentTenderRemindersLog extends Model {

    use TimestampFormatterTrait, PresentableTrait;

    protected $table = 'sent_tender_reminders_log';

    public function sentBy()
    {
        return $this->belongsTo('PCK\Users\User', 'sent_by');
    }

    public function user()
    {
        return $this->belongsTo('PCK\Users\User', 'sent_by');
    }

    public static function log(User $user, Tender $tender)
    {
        $logEntry            = new self;
        $logEntry->sent_by   = $user->id;
        $logEntry->tender_id = $tender->id;

        return $logEntry->save();
    }

    public static function getLog(Tender $tender)
    {
        return self::where('tender_id', '=', $tender->id)
            ->orderBy('created_at', 'desc')
            ->get();
    }

}