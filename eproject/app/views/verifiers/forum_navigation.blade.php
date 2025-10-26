<?php $threadFormId = isset($threadFormId) ? $threadFormId : 'approval_thread_form_id'; ?>
@if($object)
	@if (\PCK\Verifier\Verifier::canInitiateForumThread($currentUser, $object))
	    {{ Form::open(array('route' => array('approval.forum.threads.initialise', $project->id), 'id' => $threadFormId, 'hidden' => true)) }}
			<input type="hidden" name="object_id" value="{{ $object->id }}"/>
			<input type="hidden" name="object_type" value="{{ get_class($object) }}"/>
		{{ Form::close() }}
		<button type="button" class="btn btn-sm btn-warning" data-action="form-submit" data-target-id="{{{ $threadFormId }}}"><i class="fa fa-comment"></i> {{ trans('verifiers.comments') }}</button>
	@else
		@include('forum.partials.object_thread_link', array('cssClass' => "btn btn-sm btn-warning"))
	@endif
@endif