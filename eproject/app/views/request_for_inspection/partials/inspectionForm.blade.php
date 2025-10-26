<?php $inspectionStatus = Input::old('status') ?? null; ?>
<?php $isUpdate = ($requestForInspection->inspections->last() ?? false) && (\PCK\Verifier\Verifier::isRejected($requestForInspection->inspections->last()) ); ?>
@if($isUpdate)
    <?php $inspectionStatus = $requestForInspection->inspections->last()->status; ?>
    {{ Form::model($requestForInspection->inspections->last(), array('route' => array('requestForInspection.inspection.update', $project->id, $requestForInspection->id, $requestForInspection->inspections->last()->id), 'method' => 'PUT', 'class' => 'smart-form' )) }}
@else
    {{ Form::open(array('route' => array('requestForInspection.inspection.store', $project->id, $requestForInspection->id), 'class' => 'smart-form' )) }}
@endif
    @if($isUpdate)
        <fieldset class="bg-grey-e">
            @include('request_for_inspection.partials.verifierInfo', array('object' => $requestForInspection->inspections->last()))
        </fieldset>
    @endif
    <fieldset class="bg-grey-e">
        <div class="row">
            <section class="col col-xs-12 col-lg-9 col-md-9">
                <label class="label">{{{ trans('requestForInspection.comments') }}} <span class="required">*</span>:</label>
                <label class="input {{{ $errors->has('comments') ? 'state-error' : null }}}">
                    {{ Form::text('comments', Input::old('comments'), array('required' => 'required', 'autofocus' => true)) }}
                </label>
                {{ $errors->first('comments', '<em class="invalid">:message</em>') }}
            </section>
            <section class="col col-xs-12 col-md-3 col-lg-3">
                <label class="label">{{ trans('requestForInspection.inspectedAt') }} <span class="required">*</span>:</label>
                <label class="input {{{ $errors->has('inspected_at') ? 'state-error' : null }}}">
                    {{ Form::text('inspected_at', Input::old('inspected_at') ?? ( isset($inspection) ? \Carbon\Carbon::parse($project->getProjectTimeZoneTime($inspection->inspected_at))->format('d-M-Y') : null ) ?? \Carbon\Carbon::now($project->timezone)->format('d-M-Y'), array('class' => 'datetimepicker')) }}
                </label>
                {{ $errors->first('inspected_at', '<em class="invalid">:message</em>') }}
            </section>
        </div>
        <div class="well padded rounded-less">
            <div class="row">
                <section class="col col-xs-12 col-lg-12 col-md-12">
                    <label class="radio">
                        <input type="radio" name="status" value="{{{ \PCK\RequestForInspection\RequestForInspectionInspection::STATUS_PASSED }}}"
                        {{{ $inspectionStatus == \PCK\RequestForInspection\RequestForInspectionInspection::STATUS_PASSED ? 'checked' : ''}}}>
                        <i></i>{{ trans('requestForInspection.inspectionPassed') }}</label>
                    <label class="radio">
                        <input type="radio" name="status" value="{{{ \PCK\RequestForInspection\RequestForInspectionInspection::STATUS_REMEDY_WITH_RE_INSPECTION }}}"
                        {{{ $inspectionStatus == \PCK\RequestForInspection\RequestForInspectionInspection::STATUS_REMEDY_WITH_RE_INSPECTION ? 'checked' : ''}}}>
                        <i></i>{{ trans('requestForInspection.remedyAndReInspectionRequired') }}</label>
                    <label class="radio">
                        <input type="radio" name="status" value="{{{ \PCK\RequestForInspection\RequestForInspectionInspection::STATUS_REMEDY_WITHOUT_RE_INSPECTION }}}"
                        {{{ $inspectionStatus == \PCK\RequestForInspection\RequestForInspectionInspection::STATUS_REMEDY_WITHOUT_RE_INSPECTION ? 'checked' : ''}}}>
                        <i></i>{{ trans('requestForInspection.remedyAndReInspectionNotRequired') }}</label>
                    {{ $errors->first('status', '<em class="invalid">:message</em>') }}
                </section>
            </div>
            <div class="row">
                <section class="col col-xs-12 col-lg-12 col-md-12">
                    <label class="label">{{{ trans('requestForInspection.remarks') }}} <span class="required">*</span>:</label>
                    <label class="textarea {{{ $errors->has('remarks') ? 'state-error' : null }}}">
                        {{ Form::textArea('remarks', Input::old('remarks'), array('required' => 'required', 'rows' => 3)) }}
                    </label>
                    {{ $errors->first('remarks', '<em class="invalid">:message</em>') }}
                </section>
            </div>
        </div>
        <br/>
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