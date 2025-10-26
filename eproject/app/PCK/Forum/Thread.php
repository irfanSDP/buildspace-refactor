<?php namespace PCK\Forum;

use Illuminate\Database\Eloquent\Model;
use PCK\ContractGroups\Types\Role;
use PCK\Users\User;
use PCK\Projects\Project;

class Thread extends Model {

    protected $table = 'forum_threads';

    protected $fillable = [
        'project_id',
        'title',
        'created_by',
        'type',
    ];

    const TYPE_PRIVATE = 1;
    const TYPE_PUBLIC  = 2;
    const TYPE_SECRET  = 3;

    public function isTypePublic()
    {
        return $this->type == self::TYPE_PUBLIC;
    }

    public function isTypePrivate()
    {
        return $this->type == self::TYPE_PRIVATE;
    }

    public function isTypeSecret()
    {
        return $this->type == self::TYPE_SECRET;
    }

    public function project()
    {
        return $this->belongsTo('PCK\Projects\Project');
    }

    public function post()
    {
        return $this->hasOne('PCK\Forum\Post', 'thread_id', 'id')->whereNull('parent_id')->whereNull('original_post_id');
    }

    public function users()
    {
        return $this->belongsToMany('PCK\Users\User', 'forum_thread_user', 'thread_id', 'user_id')->withTimestamps();
    }

    public function getPosterName()
    {
        if( ! Post::shouldObfuscateName($this, $this->project) ) return $this->createdBy->name;

        $creatorCompany = $this->createdBy->getAssignedCompany($this->project, $this->created_at);

        if( ( ! $creatorCompany ) || $creatorCompany->hasProjectRole($this->project, Role::CONTRACTOR) )
        {
            return trans('forum.anonymousTenderer');
        }

        return trans('forum.anonymousClient');
    }

    public function createdBy()
    {
        return $this->belongsTo('PCK\Users\User', 'created_by', 'id');
    }

    public function log()
    {
        return $this->hasMany('PCK\Forum\ThreadPrivacyLog', 'thread_id', 'id')->orderBy('created_at', 'desc');
    }

    public function getPostCount()
    {
        return Post::where('thread_id', '=', $this->id)->whereNull('original_post_id')->whereNotNull('parent_id')->count();
    }

    public function getUnreadPostCount(User $user, $includeMainPost = false)
    {
        $unreadPosts = ReadLog::getUnreadPostsByThread($user, $this);

        if( ! $includeMainPost )
        {
            $unreadPosts = $unreadPosts->reject(function($post)
            {
                return $post->id == $post->thread->post->id;
            });
        }

        return $unreadPosts->count();
    }

    public function isViewable(User $user)
    {
        if( $this->isTypeSecret() )
        {
            $record = ThreadUser::where('user_id', '=', $user->id)
                ->where('thread_id', '=', $this->id)
                ->first();

            return !is_null($record);
        }

        if( $user->isSuperAdmin() ) return false;

        if( $user->hasCompanyProjectRole($this->project, Role::getRolesExcept(Role::CONTRACTOR)) ) return true;

        if( ! ( $userCompany = $user->getAssignedCompany($this->project, $this->created_at) ) ) return false;

        if( ( $creatorCompany = $this->createdBy->getAssignedCompany($this->project, $this->created_at) ) && ( $userCompany->id == $creatorCompany->id ) ) return true;

        if( $this->isTypePublic() ) return true;

        return false;
    }

    public function getMatchingPosts($searchString, $includeEdited = true)
    {
        $query = Post::where('thread_id', '=', $this->id)
            ->where('content', 'ilike', "%{$searchString}%");

        if( ! $includeEdited ) $query->whereNotNull('original_post_id');

        return $query->get();
    }

    public function getParticipants(User $user)
    {
        $thread       = $this;
        $participants = [];

        $projectUsers = $this->project->getProjectUsers(true)->filter(function($user) use ($thread)
        {
            return $user->isActive() && $thread->isViewable($user);
        });

        foreach($projectUsers as $projectUser)
        {
            array_push($participants, $projectUser);
        }

        return $participants;
    }

    public static function hasForumThreadAccess(User $user, $object)
    {
        $thread = ObjectThread::getObjectThread($object);

        if( is_null($thread) ) return false;

        return $thread->isViewable($user);
    }

    public static function init(Project $project, User $user, $object, $moduleName)
    {
        $thread = Thread::create(array(
            'project_id' => $project->id,
            'title'      => trans('verifiers.approval'). " ({$moduleName})",
            'created_by' => $user->id,
            'type'       => self::TYPE_SECRET,
        ));

        $post = Post::create(array(
            'thread_id'  => $thread->id,
            'content'    => trans('verifiers.comments'),
            'created_by' => $user->id,
        ));

        ObjectThread::create(array(
            'thread_id'   => $thread->id,
            'object_id'   => $object->id,
            'object_type' => get_class($object),
        ));

        return $thread;
    }

    public function syncThreadUsers(array $userIds)
    {
        $this->users()->sync($userIds);

        foreach(ThreadUser::whereIn('user_id', $userIds)->get() as $pivotRecord)
        {
            \PCK\Forum\ThreadUserSetting::firstOrCreate(array(
                'forum_thread_user_id' => $pivotRecord->id
            ));
        }
    }

}
