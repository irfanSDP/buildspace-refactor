@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{ link_to_route('projects.show', str_limit($project->title, 50), array($project->id)) }}</li>
        <li>{{ link_to_route('technicalEvaluation.results.index', trans('technicalEvaluation.technicalEvaluationResults'), array($project->id)) }}</li>
        <li>{{ link_to_route('technicalEvaluation.results.show', $tender->current_tender_name, array($project->id, $tender->id)) }}</li>
        <li>{{ trans('technicalEvaluation.assessmentConfirmation') }}</li>
    </ol>

    @include('projects.partials.project_status')
@endsection
<?php use \PCK\TechnicalEvaluationTendererOption\TechnicalEvaluationTendererOption as Option; ?>
<?php
    $submittedForApproval = null;
    if(isset($submitter)) {
        $submittedForApproval = !is_null($submitter);
    }
?>
@section('content')
    <div class="row">
        <div class="col col-xs-12 col-sm-9 col-md-9 col-lg-9">
            <h1 class="page-title txt-color-blueDark">
                <i class="fa fa-users"></i> {{ trans('technicalEvaluation.assessmentConfirmationForm') }}
            </h1>
        </div>
        @if($tender->technicalEvaluation && $approvalProcessCompleted)
        <div class="col col-xs-12 col-sm-3 col-md-3 col-lg-3">
            <a href="{{ route('technical.assessment.export', [$project->id, $tender->id]) }}" target="_blank" class="btn btn-success pull-right"><i class="far fa-file-excel fa-lg"></i> {{ trans('general.export') }}</a>
        </div>
        @endif
    </div>
    @if ($tender->technicalEvaluation && $submittedForApproval && $isCurrentVerifier)
        {{ Form::open(array('route' => array('technicalEvaluation.approval.status.update', $project->id, $tender->id), 'method' => 'put', 'class' => 'smart-form')); }}
    @else
        {{ Form::open(array('route' => array('technicalEvaluation.approval.submit', $project->id, $tender->id), 'method' => 'put', 'class' => 'smart-form')) }}
    @endif
    <div class="smart-form">
        <section>
            <label class="label">{{ trans('projects.project') }} : </label>
            <label class="textarea">{{ nl2br($project->title) }}</label>
        </section>

        <section>
            <label class="label">{{ trans('technicalEvaluation.targetedDateOfAward') }} : </label>
            <label class="input">
            @if ($tender->technicalEvaluation)
                @if ($submittedForApproval)
                    {{ Form::label('targeted_date_of_award', $project->getProjectTimeZoneTime($targetedDateOfAward)) }}
                @else
                    <input type="date" name="targeted_date_of_award" value="{{ Input::old('targeted_date_of_award') ?? date('Y-m-d') }}" style="width: 250px;" required>
                @endif
            @else
                <input type="date" name="targeted_date_of_award" value="{{ Input::old('targeted_date_of_award') ?? date('Y-m-d') }}" style="width: 250px;" required>
            @endif
            </label>
        </section>
    </div>

    <div class="alert alert-warning fade in">
        <strong>{{ trans('technicalEvaluation.iHerebyConfirmThat') }}: -</strong>
    </div>

    <div class="well" style="margin-bottom:12px;">
        <strong>A) {{ trans('general.pass') }} - {{ trans('technicalEvaluation.qualifiedTenderers') }}.</strong>
        <table class="table table-bordered table-responsive" id="qualifiedContractors" >
            <thead>
                <tr>
                    <th class="text-center" style="width:20px;">{{ trans('general.no') }}</th>
                    <th>{{ trans('companies.companyName') }}</th>
                    <th style="width:35%">{{ trans('general.remarks') }}</th>
                    <th class="text-center" style="width:80px">{{ trans('general.score') }}</th>
                </tr>
            </thead>
            <tbody>
                <?php $count = 0; ?>
                @foreach ($selectedTenderers as $tenderer)
                    <tr>
                        <td class="text-center">{{{ ++$count }}}</td>
                        <td>{{{ $tenderer->name }}}</td>
                        <td>{{{ $tendererRemarks[$tenderer->id] }}}</td>
                        <td class="text-center">{{{ number_format(Option::getTendererScore($tenderer, $setReference->set), 2) }}}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="well" style="margin-bottom:12px;">
        <p><strong>B) {{ trans('general.fail') }} - {{ trans('technicalEvaluation.disqualifiedTenderers') }}.</strong></p>
        <p><strong>({{ trans('technicalEvaluation.furtherJustificationsRequired') }})</strong></p>
        <table class="table table-bordered table-responsive" id="disqualifiedContractors">
            <thead>
                <tr>
                    <th class="text-center" style="width:20px;">{{ trans('general.no') }}</th>
                    <th>{{ trans('companies.companyName') }}</th>
                    <th style="width:35%">{{ trans('general.remarks') }}</th>
                    <th class="text-center" style="width:80px;">{{ trans('general.score') }}</th>
                </tr>
            </thead>
            <tbody>
                @if (empty($notSelectedTenderers))
                    <tr>
                        <td colspan="4">
                            <div class="alert alert-success fade in">
                            {{ trans('technicalEvaluation.noDisqualifiedTenderers') }}.
                            </div>
                        </td>
                    </tr>
                @else
                    <?php $count = 0; ?>
                    @foreach ($notSelectedTenderers as $tenderer)
                        <tr>
                            <td class="text-center">{{{ ++$count }}}</td>
                            <td>{{{ $tenderer->name }}}</td>
                            <td>{{{ $tendererRemarks[$tenderer->id] }}}</td>
                            <td class="text-center">{{{ number_format(Option::getTendererScore($tenderer, $setReference->set), 2) }}}</td>
                        </tr>
                    @endforeach
                @endif
            </tbody>
        </table>
    </div>

    <div class="well" style="margin-bottom:12px;">
        <label class="label">{{{ trans('forms.attachments') }}}:</label>
        @if (!($submittedForApproval || $isCurrentVerifier))
            @include('file_uploads.partials.upload_file_modal')
        @elseif($tender->technicalEvaluation && !$tender->technicalEvaluation->getAttachmentDetails()->isEmpty())
            <table>
                @foreach($tender->technicalEvaluation->getAttachmentDetails() as $file)
                    <tr>
                        <td>
                            <a href="{{{ $file->download_url }}}" title="{{{ $file->filename }}}" download="{{{ $file->filename }}}">{{{ $file->filename }}}</a>
                        </td>
                    </tr>
                @endforeach
            </table>
        @endif
    </div>

    <section>
        <label class="label">{{{ trans('forms.remarks') }}}:</label>
        <label class="textarea">
            <?php $remarks = is_null(Input::old('remarks')) ? $remarks : Input::old('remarks'); ?>
            @if($submittedForApproval)
                @if(is_null($remarks) || ($remarks == ''))
                {{ '-' }}
                @else
                {{ nl2br($remarks) }}
                @endif
            @else
                {{ Form::textarea('remarks', $remarks, array('rows' => 10)) }}
            @endif
        </label>
    </section>

    @include('verifiers.verifier_requestor', array(
        'object' => $tender->technicalEvaluation ?? null
    ))

    @include('verifiers.verifier_status_overview', array(
        'verifierRecords' => $assignedVerifierRecords ?? null
    ))
    
    @if (!$submittedForApproval)
        <div class="row">
            <section class="col col-xs-12 col-md-5 col-lg-5">
                @include('verifiers.select_verifiers', array(
                    'verifiers' => $verifiers,
                    'selectedVerifiers' => $selectedVerifiers ?? null, 
                ))
            </section>
        </div>
    @else
        <?php $user = \Confide::user(); ?>
        @if (($user->id == $submitter->id) && !$approvalProcessCompleted)
            {{ Form::button('<i class="fa fa-envelope"></i> '.trans('verifiers.sendReminderEmail'), array('id' => 'sendReminderEmailTechAssessment', 'class' => 'btn btn-primary btn-sm')) }}
        @endif
    @endif
        <div id="divButtons" class="form-group pull-right">
            @if ($submittedForApproval && $isCurrentVerifier)
                <input type="hidden" name="tenderId" value="{{{ $tender->id }}}">
                <button id="btnApproveTechAssessment" value="approve" data-toggle="modal" data-target="#confirmation_remarks_modal" class="btn btn-success btn-sm"><i class="fa fa-check"></i> {{ trans('forms.approve') }}</button>
                <button id="btnRejectTechAssessment" value="reject" data-toggle="modal" data-target="#confirmation_remarks_modal" class="btn btn-danger btn-sm"><i class="fa fa-times"></i> {{ trans('forms.reject') }}</button>
            @else
                @if (!$submittedForApproval)
                    <button id="btnSubmitForApproval" type="submit" data-intercept="confirmation" data-intercept-condition="noVerifier" data-confirmation-message ="{{trans('general.submitWithoutVerifier')}}" class="btn btn-primary btn-sm"><i class="fa fa-save"></i> {{ trans('forms.submit') }}</button>
                @endif
            @endif
            <button id="btnCancelSubmitConfirmation" class="btn btn-default btn-sm">{{ trans('forms.back') }}</button>
        </div>
    @include('templates.confirmation_remarks_modal')
    {{ Form::close() }}
    @include('tenders.partials.tender_reminder_sent_modal')

</div>

@endsection

@section('js')
    <script>
        $(document).on('click', '#btnCancelSubmitConfirmation', function(e) {
            e.preventDefault();
            window.location.href = '{{ route('technicalEvaluation.results.show', array($project->id, $tender->id)) }}';
        });

        $(document).on('click', '#btnApproveTechAssessment, #btnRejectTechAssessment', function(e) {
            e.preventDefault();
            var value = $(this).prop('value');

            $('#confirmation_remarks_modal #remark').prop('name', 'verification_approve');
            $('#confirmation_remarks_modal #remark').prop('value', value);
        });

        $(document).on('click', '#sendReminderEmailTechAssessment', function(e) {
            e.preventDefault();
            var url = '{{ route('technicalEvaluation.approval.reminder.send', array($project->id, $tender->id)) }}';;
            $.ajax({
                url: url,
                method: 'GET',
                data: {
                    projectId: '{{{ $project->id }}}'
                },
                success: function (data) {
                    $('#tenderReminderSentModal').modal('show');
                }
            });
        });

        function noVerifier(e){
            var form = $(e.target).closest('form');
            var input = form.find(':input[name="verifiers[]"]').serializeArray();
            return !input.some(function(element)
            {
                return (element.value > 0);
            });
        }
    </script>
@endsection