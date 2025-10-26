<div class="row" id="row_isAccumulated" style="{{ $display ? '':'display: none;' }}">
    <section class="col col-xs-12 col-md-6 col-lg-6">
        <label class="checkbox {{ $errors->has('isAccumulated') ? 'state-error' : null }}">
            {{ Form::checkbox('isAccumulated', 1, $record->is_accumulated, array('id' => 'isAccumulated')) }} <i></i>{{ trans('projectReportChart.accumulative') }}
        </label>
        {{ $errors->first('isAccumulated', '<em class="invalid">:message</em>') }}
    </section>
</div>