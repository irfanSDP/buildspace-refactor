<?php namespace PCK\Forum;

use Illuminate\Database\Eloquent\Model;
use PCK\Base\ModuleAttachmentTrait;
use PCK\ContractGroups\Types\Role;
use PCK\Projects\Project;
use PCK\Users\User;

class Post extends Model {

    use ModuleAttachmentTrait;

    protected $table = 'forum_posts';

    protected $fillable = [
        'thread_id',
        'parent_id',
        'original_post_id',
        'content',
        'created_by',
    ];

    public function thread()
    {
        return $this->belongsTo('PCK\Forum\Thread', 'thread_id', 'id');
    }

    public function children()
    {
        return $this->hasMany('PCK\Forum\Post', 'parent_id', 'id')->whereNull('original_post_id')->orderBy('created_at', 'desc');
    }

    public function parent()
    {
        return $this->belongsTo('PCK\Forum\Post', 'parent_id', 'id');
    }

    public function readLog()
    {
        return $this->hasMany('PCK\Forum\ReadLog', 'post_id', 'id');
    }

    public function createdBy()
    {
        return $this->belongsTo('PCK\Users\User', 'created_by', 'id');
    }

    public function revisions()
    {
        return $this->hasMany('PCK\Forum\Post', 'original_post_id', 'id')->whereNotNull('original_post_id')->orderBy('created_at', 'desc');
    }

    public function markAsRead(User $user)
    {
        return ReadLog::create(array(
            'user_id' => $user->id,
            'post_id' => $this->id,
        ));
    }

    public function getEditedAtAttribute()
    {
        if( $this->revisions->isEmpty() ) return null;

        return $this->revisions->first()->created_at;
    }

    public function getContent()
    {
        if( $this->revisions->isEmpty() ) return $this->content;

        $latestRevision = self::where('original_post_id', '=', $this->id)->orderBy('created_at', 'desc')->first();

        return $latestRevision->content;
    }

    public static function shouldObfuscateName($object, Project $project)
    {
        $user = \Confide::user();

        $thread = ($object instanceof self) ? $object->thread : $object;

        if( $thread->isTypeSecret() ) return false;

        if( $user->hasCompanyProjectRole($project, Role::getRolesExcept(Role::CONTRACTOR)) ) return false;

        if( ( $object->createdBy->getAssignedCompany($project, $object->created_at)->id ?? null ) == $user->getAssignedCompany($project)->id ) return false;

        return true;
    }

    public function getPosterName()
    {
        if( ! self::shouldObfuscateName($this, $this->thread->project) ) return $this->createdBy->name;

        $creatorCompany = $this->createdBy->getAssignedCompany($this->thread->project, $this->created_at);

        if( ( ! $creatorCompany) || $creatorCompany->hasProjectRole($this->thread->project, Role::CONTRACTOR) )
        {
            return trans('forum.anonymousTenderer');
        }

        return trans('forum.anonymousClient');
    }
}