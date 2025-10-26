
@extends('layout.main')

@section('breadcrumb')

    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{ link_to_route('projects.show', str_limit($project->title, 50), array($project->id)) }}</li>
        <li>{{{ trans('dailyreport.show-form') }}}</li>
    </ol>

@endsection

@section('content')

<div class="row">

    <article class="col-sm-12 col-md-12 col-lg-12" style="padding-top: 10px;">
    <div class="jarviswidget jarviswidget-sortable">
        <header role="heading">
            <h2>{{{ trans('dailyreport.daily-report') }}}</h2>
        </header>
                    
        <!-- widget div-->
        <div role="content">
            <!-- widget content -->
            <div class="widget-body no-padding">
                <div class="smart-form">
                    {{ Form::model($record, array('route' => array('daily-report.update', $project->id, $record->id), 'method' => 'PUT')) }}
                    <fieldset>
                        <div class="form-group">
                            {{ Form::label('instruction', trans('dailyreport.description')) }}
                            {{ Form::textarea('instruction', Input::old('instruction'), array('class' => 'form-control padded-less-left', 'readonly' => 'true', 'rows' => 5)) }}
                            {{ $errors->first('instruction', '<em class="invalid">:message</em>') }}
                        </div>
                    </fieldset>
                    {{ Form::close() }}
                    <footer>
                    {{ link_to_route('daily-report.index', trans('forms.back'), [$project->id], ['class' => 'btn btn-default']) }}
                    <button id="btnViewLogs" type="button" class="btn btn-sm btn-success pull-right" style="margin-right:4px;">View Logs</button>
                    </footer>
                </div>
                @if(!$isVerified)
                    @if($isCurrentVerifier)
                        <div class="pull-right" style="margin-right: 15px;">
                            @include('verifiers.approvalForm', [
                                'object'	=> $record,
                            ])
                        </div>
                    @endif
                @endif
                <div class="col col-lg-4">
                    <button type="button" class="btn btn-sm btn-info" data-toggle="modal" data-target="#downloadModal" data-action="get-downloads" data-get-downloads="{{ route('daily-report.attachements.get',[$project->id, $record->id]) }}">
                        <i class="fa fa-paperclip"></i> {{ trans('general.attachments') }} ({{$attachmentsCount}})
                    </button>
                </div>
            </div>
            <!-- end widget content -->
        </div>
        <!-- end widget div -->
    </div>
    </article>

</div>


@include('daily_report.partials.verifier_remarks_modal')
@include('daily_report.partials.verifier_log_modal')
@include('uploads.downloadModal')
@endsection 

@section('js')
    <script type="text/javascript" src="{{ asset('js/moment/min/moment.min.js') }}"></script>

    <script>
        $(document).ready(function() {
            $('#btnViewLogs').on('click', function(e) {
				e.preventDefault();
				$('#dailyReportVerifierLogModal').modal('show');
			});

            $('#verifierForm button[name=approve], #verifierForm button[name=reject]').on('click', function(e) {
				e.preventDefault();

				if(this.name == 'reject') {
					$('#dailyReportVerifierRejectRemarksModal').modal('show');
				}

				if(this.name == 'approve') {
					$('#dailyReportVerifierApproveRemarksModal').modal('show');           
                } 
			});

            $('button#verifier_approve_daily_report-submit_btn, button#verifier_reject_daily_report-submit_btn').on('click', function(e) {
				e.preventDefault();

				var remarksId;

                console.log("button clicked");
				            
				switch(this.id) {
					case 'verifier_approve_daily_report-submit_btn':
						var input = $("<input>").attr("type", "hidden").attr("name", "approve").val(1);
						$('#verifierForm').append(input);
						remarksId = 'approve_verifier_remarks';
						break;
					case 'verifier_reject_daily_report-submit_btn':
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

        });
    </script>
    
@endsection





