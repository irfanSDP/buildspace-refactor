<?php $readOnly = $readOnly ?? false; ?>
<?php $singlePost = $singlePost ?? false; ?>
<table class="table" id="post-{{{ $post->id }}}" style="margin-bottom:0;">
    <tr>
        @if(!$singlePost)
            <td class="text-middle text-center" style="width:8px;cursor:pointer;" data-action="expandToggle" data-target="post-{{{ $post->id }}}">
                <i class="fa fa-plus-circle clickable"></i>
            </td>
        @endif
        <td class="text-middle text-left">
            {{{ trans('forum.postedByAt', array('name' => $post->getPosterName(), 'date' => \Carbon\Carbon::parse($project->getProjectTimeZoneTime($post->created_at))->format('d M Y g:i A'))) }}}
            @if($post->edited_at)
                <a href="{{ route('form.threads.posts.edit.history', array($project->id, $post->id)) }}" class="plain" data-toggle="tooltip" title="{{ trans('general.viewEditHistory') }}" data-placement="top">
                    {{{ trans('forum.editedAt', array('date' => \Carbon\Carbon::parse($project->getProjectTimeZoneTime($post->edited_at))->format('d M Y g:i A'))) }}}
                </a>
            @endif
        </td>
    </tr>
    <tr data-id="post-{{{ $post->id }}}" data-type="expandable">
        @if(!$singlePost)
            <td class="text-center clickable" data-action="expandToggle" style="background-color:#f8f8f8;border-bottom:none;" data-target="post-{{{ $post->id }}}"></td>
        @endif
        <td style="padding:0;border-left:1px solid #e9e9e9;border-bottom:none;">
            <table class="table" style="margin-bottom:0;">
                <tr data-category="content" data-id="post-{{{ $post->id }}}">
                    <td class=" {{{ isset($unreadPosts) ? ($unreadPosts->find($post->id) ? 'forum-unread' : '') : '' }}}" style="border:none;">
                        <div class="well">{{ nl2br($post->getContent()) }}</div>
                        @include('forum.posts.attachments')
                    </td>
                </tr>
                @if(!$readOnly)
                    <tr>
                        <td style="border:none;">
                            <a href="{{ route('form.threads.posts.create', array($project->id, $post->id)) }}" class="plain">
                                <strong>
                                    <i class="fa fa-comment"></i> {{{ trans('forum.comment') }}}
                                </strong>
                            </a>
                            @if($post->createdBy->id == $currentUser->id && $currentUser->stillInSameAssignedCompany($project, $post->created_at))
                                &nbsp;
                                <a href="{{ route('form.threads.posts.edit', array($project->id, $post->id)) }}" class="plain">
                                    <strong>
                                        <i class="fa fa-edit"></i> {{{ trans('forms.edit') }}}
                                    </strong>
                                </a>
                                &nbsp;
                                @if(!$currentUser->hasCompanyProjectRole($project, \PCK\ContractGroups\Types\Role::CONTRACTOR))
                                    <a class="plain clickable" data-toggle="modal" data-target="#assignUsersModal" data-url="{{{ route('form.threads.posts.alert', array($project->id, $post->id)) }}}">
                                        <strong>
                                            <i class="fa fa-share"></i> {{{ trans('general.notify') }}}
                                        </strong>
                                    </a>
                                @else
                                    <a class="plain clickable" data-action="automated-alert" data-url="{{ route('form.threads.posts.automatedAlert', array($project->id, $post->id)) }}">
                                        <strong>
                                            <i class="fa fa-share"></i> {{{ trans('general.notify') }}}
                                        </strong>
                                    </a>
                                @endif
                            @endif
                        </td>
                    </tr>
                @endif
                @if(!$singlePost)
                    @foreach($post->children as $childPost)
                        <tr>
                            <td style="padding:0;">
                                @include('forum.posts.post', array('post' => $childPost))
                            </td>
                        </tr>
                    @endforeach
                @endif
            </table>
        </td>
    </tr>

</table>

