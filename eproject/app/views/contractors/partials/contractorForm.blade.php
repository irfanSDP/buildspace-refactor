<fieldset>
    <section>
        <label class="label">Type of Work<span class="required">*</span>:</label>
        <label class="input {{{$errors->has('work_category') ? 'state-error' : null }}} ">
            {{ Form::select('work_category[]', $workCategories, isset($contractor) ? $contractor->getAllWorkCategoriesId() : null, ['id'=> 'work_category_select', 'multiple', 'style'=>'width:100%']) }}
        </label>
        {{ $errors->first('work_category', '<em class="invalid">:message</em>') }}
    </section>

    <section>
        <label class="label">Subcategory<span class="required">*</span>:</label>
        <label class="input {{{$errors->has('work_subcategory') ? 'state-error' : null }}} ">
            {{ Form::select('work_subcategory[]', $workSubcategories, isset($contractor) ? $contractor->getAllWorkSubcategoriesId() : null, ['id' => 'work_subcategory_select', 'multiple', 'style'=>'width:100%']) }}
        </label>
        {{ $errors->first('work_subcategory', '<em class="invalid">:message</em>') }}
    </section>

    <div class="row">
        <section class="col col-xs-8 col-md-4 col-lg-4">
            <label class="label">Previous CPE Grade<span class="required">*</span>:</label>
            <label class="select {{{$errors->has('previous_cpe_grade_id') ? 'state-error' : null }}}">
                {{ Form::select('previous_cpe_grade_id', $previous_cpe_grades, Input::old('previous_cpe_grade_id'), ['id' => 'previous_cpe_grade_id_select', 'class' => 'select2']) }}
            </label>
        </section>

        <section class="col col-xs-8 col-md-4 col-lg-4">
            <label class="label">Current CPE Grade<span class="required">*</span></label>
            <label class="select {{{$errors->has('current_cpe_grade_id') ? 'state-error' : null }}} ">
                {{ Form::select('current_cpe_grade_id', $current_cpe_grades, Input::old('current_cpe_grade_id'), ['id' => 'current_cpe_grade_id_select', 'class' => 'select2']) }}
            </label>
        </section>

        <section class="col col-xs-8 col-md-4 col-lg-4">
            <label class="label">Registration status<span class="required">*</span>:</label>
            <label class="select {{{ $errors->has('registration_status') ? 'state-error' : null }}}">
                {{ Form::select('registration_status_id', $registration_statuses, Input::old('registration_status_id'), ['id' => 'registration_status_id_select', 'class' => 'select2']) }}
            </label>
        </section>
    </div>

    <div class="row">
        <section class="col col-2">
            <label class="label">New Job Limit:</label>
            <label class="select {{{ $errors->has('job_limit_sign') ? 'state-error' : null }}}">
                {{ Form::select('job_limit_sign', $job_limit_symbol, Input::old('job_limit_sign'), ['id' => 'job_limit_sign_select', 'class' => 'select2'])}}
            </label>
        </section>

        <section class="col col-10">
            <label class="label">&nbsp</label>
            <label class="input {{{ $errors->has('job_limit_number') ? 'state-error' : null }}}">
                {{ Form::number('job_limit_number', isset($contractor) ? Input::old('job_limit_number') : 0, ['onClick'=>'this.select()']) }}
            </label>
            {{ $errors->first('job_limit_number', '<em class="invalid">:message</em>') }}
        </section>
    </div>

    <div class="row">
        <section class="col col-xs-12 col-md-6 col-lg-6">
            <label class="label">CIDB Category:</label>
            <label class="input {{{ $errors->has('cidb_category') ? 'state-error' : null }}}">
                {{ Form::text('cidb_category', Input::old('cidb_category')) }}
            </label>
        </section>

        <section class="col col-xs-12 col-md-6 col-lg-6">
            <label class="label">Registered Date:</label>
            <label class="input {{{ $errors->has('registered_date') ? 'state-error' : null }}}">
                <i class="icon-append fa fa-calendar"></i>
                {{ Form::text('registered_date', Input::old('registered_date'), array('class' =>'registered_date', 'placeholder' => 'yyyy-mm-dd')) }}
            </label>
            {{ $errors->first('registered_date', '<em class="invalid">:message</em>') }}
        </section>
    </div>

    <section>
        <label class="label">Remarks:</label>
        <label class="input {{{ $errors->has('remarks') ? 'state-error' : null }}}">
            {{ Form::textarea('remarks', Input::old('remarks'), array('rows'=>4, 'style'=>'width:100%')) }}
        </label>
    </section>

</fieldset>

@section('js')
    <script>
        $(document).ready(function() {
            $('#work_category_select').select2({
                placeholder: 'Choose at least one category'
            });

            $('#work_subcategory_select').select2({
                placeholder: 'Choose at least one category'
            });
        });

        $('.registered_date').datepicker({
            dateFormat : 'yy-mm-dd',
            prevText : '<i class="fa fa-chevron-left"></i>',
            nextText : '<i class="fa fa-chevron-right"></i>'
        });
    </script>

@endsection