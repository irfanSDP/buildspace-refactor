<?php $cssClass = $cssClass ?? "btn btn-warning"; ?>
@if ($object && \PCK\Forum\Thread::hasForumThreadAccess($currentUser, $object))
	<?php $thread = \PCK\Forum\ObjectThread::getObjectThread($object); $postCount = $thread->getPostCount()?>
	<a href="{{ route('forum.threads.show', array($project->id, $thread->id)) }}" class="{{{ $cssClass }}}">
		<i class="fa fa-comment"></i> {{ trans('verifiers.comments') }}
		@if($postCount > 0)
			({{{ $postCount }}})
		@endif
	</a>
@endif