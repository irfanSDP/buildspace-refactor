<?php $isUpdate = $inspection->reply ?? false && (\PCK\Verifier\Verifier::isRejected($inspection->reply)); ?>
@if($isUpdate)
{{ Form::model($inspection->reply, array('route' => array('requestForInspection.inspection.reply.update', $project->id, $requestForInspection->id, $inspection->id, $inspection->reply->id), 'method' => 'PUT', 'class' => 'smart-form' )) }}
@else
{{ Form::open(array('route' => array('requestForInspection.inspection.reply.store', $project->id, $requestForInspection->id, $inspection->id), 'class' => 'smart-form' )) }}
@endif
    @if($isUpdate)
        <fieldset>
            @include('request_for_inspection.partials.verifierInfo', array('object' => $inspection->reply))
        </fieldset>
    @endif
    <fieldset>
        <div class="row">
            <section class="col col-xs-12 col-lg-12 col-md-12">
                <label class="label">{{{ trans('requestForInspection.comments') }}} <span class="required">*</span>:</label>
                <label class="input {{{ $errors->has('comments') ? 'state-error' : null }}}">
                    {{ Form::text('comments', Input::old('comments'), array('required' => 'required', 'autofocus' => true)) }}
                </label>
                {{ $errors->first('comments', '<em class="invalid">:message</em>') }}
            </section>
        </div>
        <div class="row">
            <section class="col col-xs-12 col-md-6 col-lg-6">
                @include('form_partials.select_contract_groups', array('defaultChecked' => false, 'selectedGroupIds' => \PCK\DirectedTo\DirectedTo::getTargetIds($requestForInspection)))
            </section>
            <section class="col col-xs-12 col-md-3 col-lg-3">
            </section>
            @if($inspection->status == \PCK\RequestForInspection\RequestForInspectionInspection::STATUS_REMEDY_WITH_RE_INSPECTION)
                <section class="col col-xs-12 col-md-3 col-lg-3">
                    <label class="label">{{ trans('requestForInspection.readyDate') }} <span class="required">*</span>:</label>
                    <label class="input {{{ $errors->has('ready_date') ? 'state-error' : null }}}">
                        {{ Form::text('ready_date', (Input::old('ready_date') ?? (isset($inspection->reply) ? \Carbon\Carbon::parse($project->getProjectTimeZoneTime($inspection->reply->ready_date))->format('d-M-Y') : null ?? \Carbon\Carbon::now($project->timezone)->format('d-M-Y') )), array('class' => 'datetimepicker')) }}
                    </label>
                    {{ $errors->first('ready_date', '<em class="invalid">:message</em>') }}
                </section>
            @endif
            @if($inspection->status == \PCK\RequestForInspection\RequestForInspectionInspection::STATUS_REMEDY_WITHOUT_RE_INSPECTION)
                <section class="col col-xs-12 col-md-3 col-lg-3">
                    <label class="label">{{ trans('requestForInspection.completedDate') }} <span class="required">*</span>:</label>
                    <label class="input {{{ $errors->has('completed_date') ? 'state-error' : null }}}">
                        {{ Form::text('completed_date', (Input::old('completed_date') ?? (isset($inspection->reply) ? \Carbon\Carbon::parse($project->getProjectTimeZoneTime($inspection->reply->completed_date))->format('d-M-Y') : null ?? \Carbon\Carbon::now($project->timezone)->format('d-M-Y') )), array('class' => 'datetimepicker')) }}
                    </label>
                    {{ $errors->first('completed_date', '<em class="invalid">:message</em>') }}
                </section>
            @endif
        </div>
        <div class="row">
            <section class="col col-xs-12 col-md-12 col-lg-12">
                <label class="label">{{{ trans('forms.attachments') }}}:</label>
                @include('file_uploads.partials.upload_file_modal')
            </section>
        </div>

        <div class="row">
            <section class="col col-xs-4 col-md-4 col-lg-4">
                @include('verifiers.select_verifiers')
            </section>
        </div>
    </fieldset>
    <footer>
        {{ Form::submit(trans('forms.save'), array('class' => 'btn btn-primary')) }}
    </footer>
{{ Form::close() }}