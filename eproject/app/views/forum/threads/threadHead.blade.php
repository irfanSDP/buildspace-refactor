<?php $readOnly = $readOnly ?? false; ?>
<div class="row">
    <div class="col-sm-12">

        <div class="well">
            <p class="color-grey-9">
                {{{ trans('forum.postedByAt', array('name' => $thread->getPosterName(), 'date' => \Carbon\Carbon::parse($project->getProjectTimeZoneTime($thread->created_at))->format('d M Y g:i A'))) }}}
                @if($thread->post->edited_at)
                    <a href="{{ route('form.threads.posts.edit.history', array($project->id, $thread->post->id)) }}" class="plain" data-toggle="tooltip" title="{{ trans('general.viewEditHistory') }}" data-placement="top">
                        {{{ trans('forum.editedAt', array('date' => \Carbon\Carbon::parse($project->getProjectTimeZoneTime($thread->post->edited_at))->format('d M Y g:i A'))) }}}
                    </a>
                @endif

                @if($thread->isTypeSecret())
                    <span class="pull-right clickable" data-toggle="modal" data-tooltip data-target="#threadUsersModal" data-ajax-url="{{{ route('forum.threads.users', array($project->id, $thread->id)) }}}">
                        <span class="badge bg-color-red" data-toggle="tooltip" data-placement="left" title="{{{ trans('forum.secretThreadTooltip') }}}"> <i class="fas fa-user-secret"></i> </span>
                        <span class="text-danger" data-toggle="tooltip" data-placement="left" title="{{{ trans('forum.secretThreadTooltip') }}}"> {{ trans('forum.secretThread') }} </span>
                    </span>
                @else
                    <span class="pull-right clickable" data-toggle="modal" data-tooltip data-target="#privacySettingsLog-{{{ $thread->id }}}">
                        @if($thread->isTypePublic())
                            <span class="badge bg-color-blue" data-toggle="tooltip" data-placement="left" title="{{{ trans('forum.publicThreadTooltip') }}}"> <i class="fas fa-user"></i> </span>
                            <span class="text-primary" data-toggle="tooltip" data-placement="left" title="{{{ trans('forum.publicThreadTooltip') }}}"> {{ trans('forum.publicThread') }} </span>
                        @elseif($thread->isTypePrivate())
                            <span class="badge bg-color-yellow" data-toggle="tooltip" data-placement="left" title="{{{ trans('forum.privateThreadTooltip') }}}"> <i class="fas fa-user-circle"></i> </span>
                            <span class="text-warning" data-toggle="tooltip" data-placement="left" title="{{{ trans('forum.privateThreadTooltip') }}}"> {{ trans('forum.privateThread') }} </span>
                        @endif
                    </span>
                @endif
            </p>

            <a href="{{ route('forum.threads.show', array($project->id, $thread->id)) }}" class="plain">
                <h1>
                    {{{ $thread->title }}}
                </h1>

                <p class="font-15">{{ nl2br($thread->post->getContent()) }}</p>
            </a>

            <br/>

            @if(!$readOnly)
                <a href="{{ route('forum.threads.show', array($project->id, $thread->id)) }}" class="plain color-grey-7">
                    <strong>
                        <i class="fa fa-comment"></i>
                        {{{ Lang::choice('forum.count:comments', $thread->getPostCount(), array('count' => $thread->getPostCount())) }}}
                        <?php $unreadPostCount = $thread->getUnreadPostCount($currentUser);?>
                        @if($unreadPostCount > 0)
                            <span class="text-warning">
                                ({{{ Lang::choice('forum.count:newComments', $unreadPostCount, array('count' => $unreadPostCount)) }}})
                            </span>
                        @elseif($thread->getUnreadPostCount($currentUser, true))
                            <span class="text-warning">
                                ({{{ trans('forum.newPost') }}})
                            </span>
                        @endif
                    </strong>
                </a>
            @endif

        </div>
    </div>
</div>

@include('templates.log_modal', array('modalId' => 'privacySettingsLog-'.$thread->id, 'title' => trans('forum.privacySettingsLog'), 'log' => $thread->log))