<?php namespace PCK\Forum;

use Illuminate\Database\Eloquent\Model;

class ThreadUser extends Model {

    protected $table = 'forum_thread_user';

    protected $fillable = [
        'thread_id',
        'user_id',
    ];

    public function thread()
    {
        return $this->belongsTo('PCK\Forum\Thread', 'thread_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo('PCK\Users\User', 'user_id', 'id');
    }

    public function setting()
    {
        return $this->hasOne('PCK\Forum\ThreadUserSetting', 'forum_thread_user_id');
    }
}