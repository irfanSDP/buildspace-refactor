<?php namespace PCK\Forum;

use Illuminate\Database\Eloquent\Model;

class ThreadUserSetting extends Model {

    protected $table = 'forum_thread_user_settings';

    protected $fillable = [
        'forum_thread_user_id',
        'keep_me_posted',
    ];
}