<?php

use PCK\Notifications\EmailNotifier;

class ForumPostsController extends \BaseController {

    private $postForm;
    private $emailNotifier;

    public function __construct(\PCK\Forms\ForumPostForm $postForm, EmailNotifier $emailNotifier)
    {
        $this->postForm       = $postForm;
        $this->emailNotifier  = $emailNotifier;
    }

    public function create($project, $parentPostId)
    {
        $parentPost    = \PCK\Forum\Post::find($parentPostId);
        $thread        = $parentPost->thread;
        $uploadedFiles = $this->getAttachmentDetails();

        return View::make('forum.posts.create', compact('project', 'thread', 'parentPost', 'uploadedFiles'));
    }

    public function store($project, $parentPostId)
    {
        $this->postForm->validate(Input::all());

        $user = Confide::user();

        $parentPost = \PCK\Forum\Post::find($parentPostId);

        $post = new \PCK\Forum\Post(Input::all());

        $post->thread_id  = $parentPost->thread->id;
        $post->parent_id  = $parentPost->id;
        $post->created_by = $user->id;
        $post->save();

        \PCK\Helpers\ModuleAttachment::saveAttachments($post, Input::all());

        $post->markAsRead($user);

        if( $post->thread->isTypeSecret() )
        {
            $usersToBeNotified = \PCK\Forum\ThreadUser::where('thread_id', '=', $post->thread->id)
                ->where('user_id', '!=', $user->id)
                ->with(array('setting' => function($q){
                    $q->where('keep_me_posted', '=', true);
                }))
                ->lists('user_id');

            $this->emailNotifier->forumPostAlert($post->id, $usersToBeNotified);
        }

        $url = route('forum.threads.show', array( $project->id, $parentPost->thread->id )) . "#post-{$post->id}";

        return Redirect::to($url);
    }

    public function edit($project, $postId)
    {
        $post          = \PCK\Forum\Post::find($postId);
        $parentPost    = $post->parent;
        $thread        = $post->thread;
        $uploadedFiles = $this->getAttachmentDetails($post);

        return View::make('forum.posts.edit', compact('project', 'thread', 'parentPost', 'post', 'uploadedFiles'));
    }

    public function update($project, $postId)
    {
        $this->postForm->validate(Input::all());

        $user = Confide::user();

        $originalPost = \PCK\Forum\Post::find($postId);
        $parentPost   = $originalPost->parent;

        $post = new \PCK\Forum\Post(Input::all());

        $post->thread_id        = $originalPost->thread->id;
        $post->parent_id        = $parentPost->id ?? null;
        $post->original_post_id = $postId;
        $post->created_by       = $user->id;
        $post->save();

        \PCK\Helpers\ModuleAttachment::saveAttachments($originalPost, Input::all());

        $url = route('forum.threads.show', array( $project->id, $originalPost->thread->id )) . "#post-{$originalPost->id}";

        return Redirect::to($url);
    }

    public function editHistory($project, $postId)
    {
        $post = \PCK\Forum\Post::find($postId);

        $thread = $post->thread;

        $parentPost = $post->parent;

        return View::make('forum.posts.editHistory', compact('project', 'thread', 'parentPost', 'post'));
    }

    public function alert($project, $postId)
    {
        $this->emailNotifier->forumPostAlert($postId, Input::get('userIds'));

        return array(
            'success' => true,
        );
    }

    public function automatedAlert($project, $postId)
    {
        $userIds = \DB::table('contract_group_project_users')
            ->where('project_id', '=', $project->id)
            ->where('is_contract_group_project_owner', '=', true)
            ->lists('user_id');

        $this->emailNotifier->forumPostAlert($postId, $userIds);

        return array(
            'success' => true,
        );
    }
}
