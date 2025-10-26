<div class="row">
    <div class="col-sm-12">

        <div class="well">
            <p class="color-grey-9">{{{ trans('forum.postedByAt', array('name' => $thread->getPosterName(), 'date' => \Carbon\Carbon::parse($project->getProjectTimeZoneTime($thread->created_at))->format('d M Y g:i A'))) }}}</p>

            <a href="{{ route('forum.threads.show', array($project->id, $thread->id)) }}" class="plain">
                <h1>
                    {{{ $thread->title }}}
                </h1>

                <?php $matchingPosts = $thread->getMatchingPosts($searchString); ?>
                @if(!$matchingPosts->isEmpty())
                    @foreach($matchingPosts as $post)
                        <hr/>
                        <p class="color-grey-9">
                            {{{ trans('forum.postedByAt', array('name' => $post->getPosterName(), 'date' => \Carbon\Carbon::parse($project->getProjectTimeZoneTime($post->created_at))->format('d M Y g:i A'))) }}}
                            @if($post->edited_at)
                                <a href="{{ route('form.threads.posts.edit.history', array($project->id, $post->original_post_id)) }}" class="plain" data-toggle="tooltip" title="{{ trans('general.viewEditHistory') }}" data-placement="top">
                                    {{{ trans('forum.editedAt', array('date' => \Carbon\Carbon::parse($project->getProjectTimeZoneTime($post->created_at))->format('d M Y g:i A'))) }}}
                                </a>
                            @elseif(!is_null($post->original_post_id))
                                <a href="{{ route('form.threads.posts.edit.history', array($project->id, $post->original_post_id)) }}" class="plain" data-toggle="tooltip" title="{{ trans('general.viewEditHistory') }}" data-placement="top">
                                    {{{ trans('forum.editedAt', array('date' => \Carbon\Carbon::parse($project->getProjectTimeZoneTime($post->created_at))->format('d M Y g:i A'))) }}}
                                </a>
                            @endif
                        </p>
                        <p class="font-15">{{{ $post->content }}}</p>
                    @endforeach
                @endif
            </a>

            <br/>
        </div>
    </div>
</div>
