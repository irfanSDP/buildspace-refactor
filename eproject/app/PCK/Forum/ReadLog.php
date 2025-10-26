<?php namespace PCK\Forum;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use PCK\Users\User;

class ReadLog extends Model {

    protected $table = 'forum_posts_read_log';

    protected $fillable = [ 'user_id', 'post_id' ];

    public static function markThreadAsRead(User $user, Thread $thread)
    {
        $postIds   = self::getUnreadPostsByThread($user, $thread)->lists('id');
        $timestamp = Carbon::now();

        $data = array();
        foreach($postIds as $postId)
        {
            $data[] = array(
                'post_id'    => $postId,
                'user_id'    => $user->id,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            );
        }

        if( ! empty( $data ) ) self::insert($data);
    }

    public static function getUnreadPostsByThread(User $user, Thread $thread)
    {
        return Post::where('thread_id', '=', $thread->id)
            ->whereNull('original_post_id')
            ->whereDoesntHave('readLog', function($query) use ($user)
            {
                $query->where('user_id', '=', $user->id);
            })
            ->get();
    }

}