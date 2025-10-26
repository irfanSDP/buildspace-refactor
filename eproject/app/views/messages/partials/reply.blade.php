<div class="jarviswidget post-{{{ $replyMessage->id }}}" data-widget-editbutton="false" data-widget-colorbutton="false" data-widget-deletebutton="false" data-widget-sortable="false">
	<header>
		<span class="widget-icon"> <i class="fa fa-paper-plane"></i> </span>
		<h2 class="hidden-mobile">{{{ trans('forms.reply') }}} {{ $replyMessage->conversation->project->getProjectTimeZoneTime($replyMessage->created_at) }}</h2>
	</header>
	<div>
		<div class="widget-body smart-form">
			<section>
				<strong>{{ trans('email.message') }}:</strong><br>
				{{ nl2br($replyMessage->message) }}
			</section>

			<section>
				<strong>{{ trans('email.dateReplied') }}:</strong><br>
				<span class="dateSubmitted">{{{ $replyMessage->conversation->project->getProjectTimeZoneTime($replyMessage->created_at) }}}</span>
			</section>

			<section>
				<strong>{{ trans('email.author') }}:</strong><br>
				<span class="color-blue">
					{{{ $replyMessage->createdBy->name }}}
					({{{ $replyMessage->createdBy->getProjectCompanyName($replyMessage->conversation->project, $replyMessage->created_at) }}})
				</span>
			</section>

			@if ( ! $replyMessage->attachments->isEmpty() )
				<p>
					<strong>{{ trans('general.attachments') }}:</strong><br>

					@include('file_uploads.partials.uploaded_file_show_only', ['files' => $replyMessage->attachments, 'projectId' => $replyMessage->conversation->project_id])
				</p>
			@endif
		</div>
	</div>
</div>

@if ( isset($isNewMessage) )
	<script type="text/javascript">
		$('.post-{{{ $replyMessage->id }}}').effect("highlight", {}, 2000);
	</script>
@endif