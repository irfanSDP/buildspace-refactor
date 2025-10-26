@extends('layout.main')

@section('breadcrumb')
	<ol class="breadcrumb">
		<li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), []) }}</li>
		@if ($isTemplate)
			<li>{{ trans('letterOfAward.letterOfAward') }}</li>
			<li>{{ link_to_route('letterOfAward.templates.selection', trans('letterOfAward.listOfTemplates'), []) }}</li>
			<li>{{{ $templateName }}} {{{ '(' . trans('letterOfAward.template') . ')' }}}</li>
		@else
			<li>{{ link_to_route('projects.show', str_limit($project->title, 50), [$project->id]) }}</li>
			<li>{{ trans('letterOfAward.letterOfAward') }}</li>
		@endif
	</ol>

	@if(!$isTemplate)
		@include('projects.partials.project_status')
	@endif
@endsection

@section('content')
	<article class="col-sm-12">
		<div class="row">
			<div class="col-xs-8">
				<h1 class="page-title">
					<i class="fa fa-file-alt"></i>
					@if ($isTemplate)
						{{{ $templateName }}} {{{ '(' . trans('letterOfAward.template') . ')' }}}
					@else
						{{ trans('letterOfAward.letterOfAward') }}
					@endif
					
				</h1>
			</div>
			@if (!$isTemplate)
				<div class="col-xs-12 col-sm-4 col-md-4 col-lg-4">
					<div class="btn-group pull-right header-btn">
						@include('letter_of_award.partials.index_actions_menu', array('classes' => 'pull-right'), [
							'showNotifyReviewerButton'			=> $canUserEditLetterOfAward,
							'showSendCommentNotificationButton'	=> $canUserCommentLetterOfAward
						])
					</div>
				</div>
			@endif
		</div>
		<div class="jarviswidget well" id="wid-id-0">
			<div class="row">
				<div>
					<div class="widget-body">
						<table class="table table-hover">
							<thead>
								<tr>
                                    <th class="text-middle text-center">{{trans('formOfTender.section')}}</th>
                                    <th class="text-middle text-center" style="width:120px;">&nbsp;</th>
                                </tr>
							</thead>
							<tbody>
								<tr>
									<td class="text-middle text-center">{{ trans('letterOfAward.contractDetails') }}</td>
									<td>
										<?php $contractDetailsRoute = $isTemplate ? route('letterOfAward.template.contractDetails.edit', [$letterOfAward->id]) : route('letterOfAward.contractDetails.edit', [$project->id]);  ?>
										<a href="{{{ $contractDetailsRoute }}}" class="btn btn-primary fill-horizontal"><i class="far fa-edit"></i> {{ trans('forms.edit')}}</a>
									</td>
								</tr>
								<tr>
									<td class="text-middle text-center">{{ trans('letterOfAward.clauses') }}</td>
									<td>
										<?php 
											$clausesRoute = $isTemplate ? route('letterOfAward.template.clause.edit', [$letterOfAward->id]) : route('letterOfAward.clause.edit', [$project->id]);
											$unreadCommentsText = !$isTemplate && ($unreadCommentsCount != 0) ? '&nbsp;&nbsp;<span class="badge bg-color-orange inbox-badge">' . $unreadCommentsCount . ' ' . trans('letterOfAward.newComments') . '</span>' : null;
										?>
										<a href="{{{ $clausesRoute }}}" class="btn btn-primary fill-horizontal"><i class="far fa-edit"></i> {{ trans('forms.edit')}} {{ $unreadCommentsText }}</a>
									</td>
								</tr>
								<tr>
									<td class="text-middle text-center">{{ trans('letterOfAward.signatory') }}</td>
									<td>
										<?php $signatoryRoute = $isTemplate ? route('letterOfAward.template.signatory.edit', [$letterOfAward->id]) : route('letterOfAward.signatory.edit', [$project->id]);  ?>
										<a href="{{{ $signatoryRoute }}}" class="btn btn-primary fill-horizontal"><i class="far fa-edit"></i> {{ trans('forms.edit')}}</a>
									</td>
								</tr>
							</tbody>
						</table>
						<footer class="pull-right" style="padding:6px;">
							<a href="{{{ $printRoute }}}" target="_blank" class="btn btn-success"><i class="fa fa-lg fa-fw fa-print"></i> {{ trans('letterOfAward.print') }}</a>
							<a href="{{{ $printSettingsEditRoute }}}" class="btn btn-success"><i class="fa fa-cog" aria-hidden="true"></i> {{ trans('letterOfAward.settings') }}</a>
							<button class="btn btn-default" id="btnViewLogs">{{ trans('letterOfAward.editLogs') }}</button>
							@if (!$isTemplate)
								<button type="button" id="btnViewVerifierLogs" class="btn btn-default">{{ trans('letterOfAward.verifierLogs') }}</button>
							@endif
						</footer>
					</div>
				</div>
			</div>
			@if (!$isTemplate)
				<div class="row">
						@if (!$isApproved && $canSubmitForApproval)
					<form id="letterOfAwardForm" action="{{ route('letterOfAward.approval.submit', [$project->id]) }}" method="POST" class="smart-form">
						<input type="hidden" name="_token" id = "_token" value="{{{ csrf_token() }}}">
							<div class="row">
								<section class="col col-xs-12 col-md-6 col-lg-6">
									@include('verifiers.select_verifiers', [
										'verifiers' => $reviewers,
									])
								</section>
							</div>
						<footer>
								<div class="pull-left">
									<button type="submit" id="btnSubmit" class="btn btn-primary">{{ trans('forms.submit') }}</button>
								</div>
						</footer>
					</form>
					@endif
					@if ($canApproveOrReject)
						<footer style="margin-bottom:15px;">
							@include('verifiers.approvalForm', [
								'object'	=> $letterOfAward,
							])
						</footer>
					@endif
				</div>
			@endif
		</div>
	</article>
	@if (!$isTemplate && $canApproveOrReject)
		@include('letter_of_award.partials.verifier_remarks_modal')
	@endif
	@if (!$isTemplate)
		@include('letter_of_award.partials.notification_sent_modal')
	@endif
	@include('letter_of_award.partials.log_modal')
	@if (!$isTemplate)
		@include('letter_of_award.partials.verifier_log_modal')
	@endif
	@include('templates.verifiers_required_modal')
@endsection
@section('js')
	<script>
		$(document).ready(function() {
			$('input[name=approve], input[name=reject]').on('click', function(e) {
				e.preventDefault();
				
				if(this.name == 'reject') {
					$('#letterOfAwardVerifierRejectRemarksModal').modal('show');
				}

				if(this.name == 'approve') {
					$('#letterOfAwardVerifierApproveRemarksModal').modal('show');
				}
			});

			$('button#verifier_approve_letter_of_award-submit_btn, button#verifier_reject_letter_of_award-submit_btn').on('click', function(e) {
				e.preventDefault();

				var remarksId;
				
				switch(this.id) {
					case 'verifier_approve_letter_of_award-submit_btn':
						var input = $("<input>").attr("type", "hidden").attr("name", "approve").val(1);
						$('#verifierForm').append(input);
						remarksId = 'approve_verifier_remarks';
						break;
					case 'verifier_reject_letter_of_award-submit_btn':
						remarksId = 'reject_verifier_remarks';
						break;
				}

				if($('#'+remarksId)){
					$('#verifierForm').append($("<input>")
					.attr("type", "hidden")
					.attr("name", "verifier_remarks").val($('#'+remarksId).val()));
				}

				$('#verifierForm').submit();
			});

			$('#remark').on('click', function(e) {
				e.preventDefault();
				$remarks = $('textarea[name=verifier_remark]').val();
				
				var verifier_remark_input = document.createElement('input');
				verifier_remark_input.setAttribute('type', 'hidden');
				verifier_remark_input.setAttribute('name', 'verifier_remarks');
				verifier_remark_input.setAttribute('value', $remarks);

				$('#verifierForm').append(verifier_remark_input);
				$('#verifierForm').submit();
			});
			
			@if (!$isTemplate)
				$('#btnNotifyReviewer').on('click', function(e) {
					e.preventDefault();
					$.ajax({
						url: "{{ route('letterOfAward.reviewer.notify', [$project->id]) }}",
						method: 'POST',
						data: {
							_token: '{{{ csrf_token() }}}'
						},
						success: function (data) {
							if(data.success) {
								$('#notification_sent_modal').modal('show');
							}
						}
					});
				});
			
				$('#btnSendCommentNotification').on('click', function(e) {
					e.preventDefault();
					$.ajax({
						url: "{{ route('letterOfAward.comment.notification.send', [$project->id]) }}",
						method: 'POST',
						data: {
							_token: '{{{ csrf_token() }}}'
						},
						success: function (data) {
							if(data.success) {
								$('#notification_sent_modal').modal('show');
							}
						}
					});
				});
			@endif

			$('#btnViewLogs').on('click', function(e) {
				e.preventDefault();
				$('#letterOfAwardLogModal').modal('show');
			});

			$(document).on('shown.bs.modal', '#letterOfAwardLogModal', function() {
				var modal = $(this);
				modal.find('#action_log-content ol').empty();
                modal.find('#action_log-content .message').empty();

				$.ajax({
					url: "{{{ $editLogRoute }}}",
					method: 'POST',
					data: {
						_token: '{{{ csrf_token() }}}',
						letter_of_award_id: {{{ $letterOfAward->id }}},
					},
					success: function (data) {
						var logEntry = "";
						var user = "";
						var type = "";
                        var date = "";

						if(data.logs.length < 1){
                            modal.find('#action_log-content .message').append('<div class="alert alert-warning fade in">{{ trans('letterOfAward.noChangesMade') }}</div>');
                        }

						for(var i = 0; i < data.logs.length; i++) {
							user = data.logs[i].user;
							type = data.logs[i].type;
							date = data.logs[i].date;
							logEntry = '<span style="color:blue">' + user + '</span> edited <span style="color:red">' + type + '</span> at <span style="color:blue"> ' + date + '</span>'; 
							modal.find('#action_log-content ol').append('<li style="margin: 0 0 0.5rem 0;">' + logEntry + '</li>');
						};	
					}
				});
			});

			$('#letterOfAwardForm').on('submit', function(e) {
                if(noVerifier(e)) {
                    $('#verifiersRequiredModal').modal('show');

                    return false;
                }
            });
		});

		@if(!$isTemplate)
			$('#btnViewVerifierLogs').on('click', function(e) {
				e.preventDefault();
				$('#letterOfAwardVerifierLogModal').modal('show');
			});
		@endif

		function noVerifier(e){
			var form = $(e.target).closest('form');
			var input = form.find(':input[name="verifiers[]"]').serializeArray();
			return !input.some(function(element){
				return (element.value > 0);
			});
		}
	</script>
@endsection