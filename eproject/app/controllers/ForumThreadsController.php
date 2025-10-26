<?php

use PCK\Forum\Thread;

class ForumThreadsController extends \BaseController {

    private $threadForm;
    private $postForm;

    const THREADS_PER_PAGE = 4;

    public function __construct(\PCK\Forms\ForumThreadForm $threadForm, \PCK\Forms\ForumPostForm $postForm)
    {
        $this->threadForm = $threadForm;
        $this->postForm   = $postForm;
    }

    public function index($project)
    {
        $user = Confide::user();

        $threads = Thread::where('project_id', '=', $project->id)->orderBy('created_at', 'desc')->where('type', '!=', Thread::TYPE_SECRET)->get();

        $threads = $threads->filter(function($thread) use ($user)
        {
            return $thread->isViewable($user);
        });

        $threads = $threads->sortByDesc(function($thread) use ($user)
        {
            return $thread->getUnreadPostCount($user, true) > 0;
        });

        $filteredThreads = new \Illuminate\Database\Eloquent\Collection();

        if( ! empty( trim($searchString = Input::get('search')) ) )
        {
            $filteredThreads = $threads->filter(function($thread) use ($searchString)
            {
                if( stristr($thread->title, $searchString) ) return true;

                if( ! $thread->getMatchingPosts($searchString)->isEmpty() ) return true;

                return false;
            });
        }

        $threads = \PCK\Helpers\Paginator::paginate($threads, self::THREADS_PER_PAGE, Input::get('page') ?? 1);

        return View::make('forum.threads.index', compact('project', 'threads', 'filteredThreads', 'searchString'));
    }

    public function create($project)
    {
        return View::make('forum.threads.create', compact('project'));
    }

    public function store($project)
    {
        \PCK\Helpers\FormValidatorHelper::validate(Input::all(), $this->threadForm, $this->postForm);

        $user = Confide::user();

        $thread = Thread::create(array(
            'project_id' => $project->id,
            'title'      => Input::get('title'),
            'created_by' => $user->id,
        ));

        $post = \PCK\Forum\Post::create(array(
            'thread_id'  => $thread->id,
            'content'    => Input::get('content'),
            'created_by' => $user->id,
        ));

        \PCK\Helpers\ModuleAttachment::saveAttachments($post, Input::all());

        $post->markAsRead($user);

        return Redirect::route('forum.threads.show', array( $project->id, $thread->id ));
    }

    public function show($project, $id)
    {
        $user = Confide::user();

        $thread = Thread::find($id);

        $unreadPosts = \PCK\Forum\ReadLog::getUnreadPostsByThread($user, $thread);

        \PCK\Forum\ReadLog::markThreadAsRead($user, $thread);

        $participants = $user->hasCompanyProjectRole($project, \PCK\ContractGroups\Types\Role::CONTRACTOR) ? new \Illuminate\Database\Eloquent\Collection() : $thread->getParticipants($user);

        return View::make('forum.threads.show', compact('project', 'thread', 'unreadPosts', 'participants'));
    }

    public function togglePrivacySetting($project, $id)
    {
        $thread = Thread::find($id);

        $thread->type = $thread->isTypePublic() ? Thread::TYPE_PRIVATE : Thread::TYPE_PUBLIC;
        $thread->save();

        \PCK\Forum\ThreadPrivacyLog::log($thread, Confide::user(), $thread->type);

        if( $thread->isTypePublic() )
        {
            Flash::success(trans('forum.threadSetToPublic'));
        }
        else
        {
            Flash::success(trans('forum.threadSetToPrivate'));
        }

        return Redirect::back();
    }

    public function getUserList($project, $threadId)
    {
        $thread = Thread::find($threadId);

        $userList = array();

        foreach(\PCK\Forum\ThreadUser::where('thread_id', '=', $thread->id)->get() as $pivotRecord)
        {
            $userList[] = array(
                'name' => $pivotRecord->user->name,
            );
        }

        return $userList;
    }
}
