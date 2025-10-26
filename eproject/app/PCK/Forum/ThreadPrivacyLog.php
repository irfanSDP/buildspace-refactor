<?php namespace PCK\Forum;

use Illuminate\Database\Eloquent\Model;
use PCK\Base\LoggableInterface;
use PCK\Users\User;

class ThreadPrivacyLog extends Model implements LoggableInterface {

    protected $table = 'forum_thread_privacy_log';

    protected $fillable = [
        'thread_id',
        'type',
        'created_by',
    ];

    public function elaboration():string
    {
        return ( $this->type == Thread::TYPE_PUBLIC ) ? trans('forum.setToPublic') : trans('forum.setToPrivate');
    }

    public function user()
    {
        return $this->belongsTo('PCK\Users\User', 'created_by', 'id');
    }

    public function actionBy()
    {
        return $this->belongsTo('PCK\Users\User', 'created_by', 'id');
    }

    public static function log(Thread $thread, User $user, $newType)
    {
        self::create(array(
            'thread_id'  => $thread->id,
            'type'       => $newType,
            'created_by' => $user->id,
        ));
    }
}