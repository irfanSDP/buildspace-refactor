@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{{ trans('vendorManagement.vendorManagement') }}}</li>
        <li>{{ link_to_route('vendorManagement.approval.registrationAndPreQualification', trans('vendorManagement.registrationAndPreQualification'), array()) }}</li>
        <li>{{ link_to_route('vendorManagement.approval.registrationAndPreQualification.show', $vendorRegistration->company->name, array($vendorRegistration->id)) }}</li>
        <li>{{ link_to_route('vendorManagement.approval.projectTrackRecord', trans('vendorManagement.projectTrackRecord'), array($vendorRegistration->id)) }}</li>
        <li>{{{ trans('vendorPreQualification.updateItem') }}}</li>
    </ol>
@endsection

@section('content')
<div class="row">
    <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
        <h1 class="page-title txt-color-blueDark">
            <i class="fa fa-users"></i> {{{ trans('vendorPreQualification.updateItem') }}}
        </h1>
    </div>
</div>

<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="jarviswidget ">
            <header>
                <h2>{{{ trans('vendorPreQualification.updateItem') }}}</h2>
            </header>
            <div>
                <div class="widget-body">
                    {{ Form::model($trackRecordProject, array('route' => array('vendorManagement.approval.projectTrackRecord.update', $trackRecordProject->id), 'class' => 'smart-form')) }}
                        <div class="row">
                            <section class="col col-xs-12 col-md-12 col-lg-12">
                                <label class="label">{{{ trans('projects.title') }}} <span class="required">*</span>:</label>
                                <label class="input {{{ $errors->has('title') ? 'state-error' : null }}}">
                                    {{ Form::text('title', Input::old('title'), array('required' => 'required', 'autofocus' => 'autofocus')) }}
                                </label>
                                {{ $errors->first('title', '<em class="invalid">:message</em>') }}
                            </section>
                        </div>
                        <div class="row">
                            <section class="col col-xs-12 col-md-4 col-lg-4">
                                <label class="label">{{{ trans('vendorManagement.vendorCategory') }}} <span class="required">*</span>:</label>
                                <label class="fill-horizontal {{{ $errors->has('vendor_category_id') ? 'state-error' : null }}}">
                                    <select class="fill-horizontal select2" name="vendor_category_id" style="width:100%;">
                                    @foreach($vendorCategorySelections as $selection)
                                        <option value="{{ $selection->id }}">{{ $selection->name }}</option>
                                    @endforeach
                                    </select>
                                </label>
                                {{ $errors->first('vendor_category_id', '<em class="invalid">:message</em>') }}
                            </section>
                            <section class="col col-xs-12 col-md-4 col-lg-4">
                                <label class="label">{{{ trans('vendorManagement.vendorWorkCategory') }}} <span class="required">*</span>:</label>
                                <label class="fill-horizontal {{{ $errors->has('vendor_work_category_id') ? 'state-error' : null }}}">
                                    <select class="fill-horizontal select2" name="vendor_work_category_id" style="width:100%;"></select>
                                </label>
                                {{ $errors->first('vendor_work_category_id', '<em class="invalid">:message</em>') }}
                            </section>
                            <section class="col col-xs-12 col-md-4 col-lg-4">
                                <label class="label">{{{ trans('vendorManagement.vendorSubWorkCategory') }}} :</label>
                                <label class="fill-horizontal {{{ $errors->has('vendor_work_subcategory_id') ? 'state-error' : null }}}">
                                    <select class="fill-horizontal select2" name="vendor_work_subcategory_id[]" style="width:100%;" multiple></select>
                                </label>
                                {{ $errors->first('vendor_work_subcategory_id', '<em class="invalid">:message</em>') }}
                            </section>
                        </div>
                        <div class="row">
                            <section class="col col-xs-12 col-md-6 col-lg-6">
                                <label class="label">{{{ trans('propertyDevelopers.propertyDeveloper') }}} <span class="required">*</span>:</label>
                                <label class="fill-horizontal {{{ $errors->has('property_developer_id') ? 'state-error' : null }}}">
                                    {{ Form::select('property_developer_id', $propertyDeveloperIds, Input::old('property_developer_id'), ['class' => 'select2 fill-horizontal'])}}
                                </label>
                                {{ $errors->first('property_developer_id', '<em class="invalid">:message</em>') }}
                            </section>
                            <section class="col col-xs-12 col-md-6 col-lg-6" id="property_developer_text" hidden>
                                <label class="label">&nbsp;</label>
                                <label class="input {{{ $errors->has('property_developer_text') ? 'state-error' : null }}}">
                                    {{ Form::text('property_developer_text', Input::old('property_developer_text')) }}
                                </label>
                                {{ $errors->first('property_developer_text', '<em class="invalid">:message</em>') }}
                            </section>
                        </div>
                        <div class="row">
                            <section class="col col-xs-12 col-md-4 col-lg-4">
                                <label class="label">{{{ trans('vendorManagement.projectAmount') }}}<span class="required">*</span>:</label>
                                <label class="input {{{ $errors->has('project_amount') ? 'state-error' : null }}}">
                                    {{ Form::number('project_amount', Input::old('project_amount'), array('required' => 'required', 'step' => 0.01)) }}
                                </label>
                                {{ $errors->first('project_amount', '<em class="invalid">:message</em>') }}
                            </section>
                            <section class="col col-xs-12 col-md-4 col-lg-4">
                                <label class="label">{{{ trans('currencies.currency') }}}<span class="required">*</span>:</label>
                                <label class="input {{{ $errors->has('country_id') ? 'state-error' : null }}}">
                                    {{ Form::select('country_id', $countryCurrencies, null, ['class' => 'select2'])}}
                                </label>
                                {{ $errors->first('country_id', '<em class="invalid">:message</em>') }}
                            </section>
                            <section class="col col-xs-12 col-md-4 col-lg-4">
                                <label class="label">{{{ trans('vendorManagement.remarks') }}}:</label>
                                <label class="input {{{ $errors->has('project_amount_remarks') ? 'state-error' : null }}}">
                                    {{ Form::text('project_amount_remarks', Input::old('project_amount_remarks')) }}
                                </label>
                                {{ $errors->first('project_amount_remarks', '<em class="invalid">:message</em>') }}
                            </section>
                        </div>
                        <div class="row">
                            <section class="col col-xs-12 col-md-4 col-lg-4">
                                <label class="label">{{{ trans('vendorManagement.yearOfSitePosession') }}}<span class="required">*</span>:</label>
                                <label class="input {{{ $errors->has('year_of_site_possession') ? 'state-error' : null }}}">
                                    {{ Form::text('year_of_site_possession', Input::old('year_of_site_possession'), array('class' => 'datetimepicker')) }}
                                </label>
                                {{ $errors->first('year_of_site_possession', '<em class="invalid">:message</em>') }}
                            </section>
                            <section class="col col-xs-12 col-md-4 col-lg-4">
                                <label class="label">{{{ trans('vendorManagement.yearOfCompletion') }}}<span class="required">*</span>:</label>
                                <label class="input {{{ $errors->has('year_of_completion') ? 'state-error' : null }}}">
                                    {{ Form::text('year_of_completion', Input::old('year_of_completion'), array('class' => 'datetimepicker')) }}
                                </label>
                                {{ $errors->first('year_of_completion', '<em class="invalid">:message</em>') }}
                            </section>
                        </div>
                        <div class="row">
                            <section class="col col-xs-12 col-md-4 col-lg-4">
                                <label class="label">{{{ trans('vendorManagement.type') }}} <span class="required">*</span>:</label>
                                <label class="input {{{ $errors->has('type') ? 'state-error' : null }}}">
                                    {{ Form::select('type', $typeOptions, Input::old('type'), array('class' => 'form-control')) }}
                                </label>
                                {{ $errors->first('type', '<em class="invalid">:message</em>') }}
                            </section>
                        </div>
                        <div class="row" data-category="completed-projects-section" hidden>
                            <section class="col col-xs-12 col-md-12 col-lg-12">
                                <label class="checkbox">
                                    {{ Form::checkbox('has_qlassic_or_conquas_score', 1, Input::old('has_qlassic_or_conquas_score')) }}

                                    <i></i>{{ trans('vendorManagement.qlassicOrConquasScore') }}
                                </label>
                            </section>
                        </div>
                        <div id="qlassic_conquas_section">
                            <div class="row" data-category="completed-projects-section" hidden>
                                <section class="col col-xs-12 col-md-4 col-lg-4">
                                    <label class="label">{{{ trans('vendorManagement.qlassicScore') }}}:</label>
                                    <label class="input {{{ $errors->has('qlassic_score') ? 'state-error' : null }}}">
                                        {{ Form::text('qlassic_score', Input::old('qlassic_score'), array()) }}
                                    </label>
                                    {{ $errors->first('qlassic_score', '<em class="invalid">:message</em>') }}
                                </section>
                                <section class="col col-xs-12 col-md-4 col-lg-4">
                                    <label class="label">{{{ trans('vendorManagement.qlassicYearOfAchievement') }}}:</label>
                                    <label class="input {{{ $errors->has('qlassic_year_of_achievement') ? 'state-error' : null }}}">
                                        {{ Form::text('qlassic_year_of_achievement', Input::old('qlassic_year_of_achievement'), array('class' => 'datetimepicker')) }}
                                    </label>
                                    {{ $errors->first('qlassic_year_of_achievement', '<em class="invalid">:message</em>') }}
                                </section>
                            </div>
                            <div class="row" data-category="completed-projects-section" hidden>
                                <section class="col col-xs-12 col-md-4 col-lg-4">
                                    <label class="label">{{{ trans('vendorManagement.conquasScore') }}}:</label>
                                    <label class="input {{{ $errors->has('conquas_score') ? 'state-error' : null }}}">
                                        {{ Form::text('conquas_score', Input::old('conquas_score'), array()) }}
                                    </label>
                                    {{ $errors->first('conquas_score', '<em class="invalid">:message</em>') }}
                                </section>
                                <section class="col col-xs-12 col-md-4 col-lg-4">
                                    <label class="label">{{{ trans('vendorManagement.conquasYearOfAchievement') }}}:</label>
                                    <label class="input {{{ $errors->has('conquas_year_of_achievement') ? 'state-error' : null }}}">
                                        {{ Form::text('conquas_year_of_achievement', Input::old('conquas_year_of_achievement'), array('class' => 'datetimepicker')) }}
                                    </label>
                                    {{ $errors->first('conquas_year_of_achievement', '<em class="invalid">:message</em>') }}
                                </section>
                            </div>
                            <div class="row" data-category="completed-projects-section" hidden>
                                <section class="col col-xs-12 col-md-4 col-lg-4">
                                    <label class="label">{{{ trans('vendorManagement.awardsReceived') }}}:</label>
                                    <label class="input {{{ $errors->has('awards_received') ? 'state-error' : null }}}">
                                        {{ Form::text('awards_received', Input::old('awards_received'), array()) }}
                                    </label>
                                    {{ $errors->first('awards_received', '<em class="invalid">:message</em>') }}
                                </section>
                                <section class="col col-xs-12 col-md-4 col-lg-4">
                                    <label class="label">{{{ trans('vendorManagement.yearOfAwardsReceived') }}}:</label>
                                    <label class="input {{{ $errors->has('year_of_recognition_awards') ? 'state-error' : null }}}">
                                        {{ Form::text('year_of_recognition_awards', Input::old('year_of_recognition_awards'), array('class' => 'datetimepicker')) }}
                                    </label>
                                    {{ $errors->first('year_of_recognition_awards', '<em class="invalid">:message</em>') }}
                                </section>
                            </div>
                            <div class="row" data-category="completed-projects-section" hidden>
                                <section class="col col-xs-12 col-md-4 col-lg-4">
                                    <label class="label">{{{ trans('vendorManagement.shassicScore') }}}:</label>
                                    <label class="input {{{ $errors->has('shassic_score') ? 'state-error' : null }}}">
                                        {{ Form::number('shassic_score', Input::old('shassic_score'), array('min' => 1, 'max' => 100, 'step' => 0.01)) }}
                                    </label>
                                    {{ $errors->first('shassic_score', '<em class="invalid">:message</em>') }}
                                </section>
                            </div>
                        </div>
                        <div class="row" data-category="completed-projects-section" hidden>
                            <section class="col col-xs-12 col-md-12 col-lg-12">
                                <label class="label">{{{ trans('vendorManagement.remarks') }}}:</label>
                                <label class="textarea {{{ $errors->has('recognition_awards') ? 'state-error' : null }}}">
                                    {{ Form::textarea('remarks', Input::old('remarks'), array('rows' => 3)) }}
                                </label>
                                {{ $errors->first('remarks', '<em class="invalid">:message</em>') }}
                            </section>
                        </div>
                        @if($setting->project_detail_attachments)
                        <section>
                            <label class="label">{{{ trans('forms.attachments') }}}:</label>

                            @include('file_uploads.partials.upload_file_modal')
                        </section>
                        @endif
                        <footer>
                            {{ link_to_route('vendorManagement.approval.projectTrackRecord', trans('forms.back'), array($vendorRegistration->id), array('class' => 'btn btn-default')) }}
                            {{ Form::button('<i class="fa fa-save"></i> '.trans('forms.save'), ['type' => 'submit', 'class' => 'btn btn-primary'] )  }}
                        </footer>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
    <link rel="stylesheet" href="{{ asset('js/eonasdan-bootstrap-datetimepicker/build/css/bootstrap-datetimepicker.min.css') }}" />
    <script type="text/javascript" src="{{ asset('js/moment/min/moment.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/eonasdan-bootstrap-datetimepicker/build/js/bootstrap-datetimepicker.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            $('.datetimepicker').datetimepicker({
                format: 'YYYY',
                showTodayButton: true,
                allowInputToggle: true
            });

            $('[name="has_qlassic_or_conquas_score"]').change(function() {
                var isChecked = $(this).is(':checked');

                if(isChecked) {
                    $('#qlassic_conquas_section').show();
                } else {
                    $('#qlassic_conquas_section').hide();
                    resetQlassicConquasSection();
                }
            });

            @if($trackRecordProject->has_qlassic_or_conquas_score)
            $('#qlassic_conquas_section').show();
            @else
            $('#qlassic_conquas_section').hide();
            @endif
    
            if($('select[name=property_developer_id]').prop('value')=='others'){
                $('#property_developer_text').show();
            }
            else{
                $('#property_developer_text').hide();
            }
    
            $('select[name=property_developer_id]').on('change', function(){
                if($('select[name=property_developer_id]').prop('value')=='others'){
                    $('#property_developer_text').show();
                }
                else{
                    $('#property_developer_text').hide();
                }
            });

            function resetQlassicConquasSection() {
                $('[name="qlassic_score"]').val('');
                $('[name="qlassic_year_of_achievement"]').val('');
                $('[name="conquas_score"]').val('');
                $('[name="conquas_year_of_achievement"]').val('');
                $('[name="awards_received"]').val('');
                $('[name="year_of_recognition_awards"]').val('');
                $('[name="shassic_score"]').val('');
            }
    
            $('select[name=type]').on('change', function(){
                if($(this).val() == {{ \PCK\TrackRecordProject\TrackRecordProject::TYPE_COMPLETED }}){
                    $('[data-category=completed-projects-section]').show();
                }
                else{
                    $('[data-category=completed-projects-section]').hide();
                    $('[name="has_qlassic_or_conquas_score"]').prop('checked', false);
                    $('[name="has_qlassic_or_conquas_score"]').trigger('change');
                }
            });
    
            if($('select[name=type]').val() == {{ \PCK\TrackRecordProject\TrackRecordProject::TYPE_COMPLETED }}){
                $('[data-category=completed-projects-section]').show();
            }

            $('[name="vendor_category_id"]').on('change', function() {
                var vendorCategoryId = $(this).val();

                if(vendorCategoryId == '') return;

                $.ajax({
                    url: "{{ route('vendor.work.categories.get') }}",
                    method: 'GET',
                    data: {
                        vendorCategoryId: vendorCategoryId,
                    },
                    success: function (response) {
                        $('[name="vendor_work_category_id"]').empty().append('<option value="">{{ trans("general.selectAnOption") }}</option>');

                        response.data.forEach(function(item, index) {
                            $('[name="vendor_work_category_id"]').append(`<option value="${item.id}">${item.description}</option>`);
                        });

                        $('[name="vendor_work_category_id"]').val(webClaim.vendorWorkCategoryId).trigger('change');
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        // error
                    }
                });
            });

            $('[name="vendor_work_category_id"]').on('change', function() {
                $('[name="vendor_work_subcategory_id[]"]').empty();

                var vendorWorkCategoryId = $(this).val();

                if(vendorWorkCategoryId == '') return;

                $.ajax({
                    url: "{{ route('vendor.work.sub.categories.get') }}",
                    method: 'GET',
                    data: {
                        vendorWorkCategoryId: vendorWorkCategoryId,
                    },
                    success: function (response) {
                        response.data.forEach(function(item, index) {
                            $('[name="vendor_work_subcategory_id[]"]').append(`<option value="${item.id}">${item.description}</option>`);
                        });

                        $('[name="vendor_work_subcategory_id[]"]').val(webClaim.vendorWorkSubCategoryIds);
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        // error
                    }
                });
            });

            function initVendorCategorySelect() {
                $('[name="vendor_category_id"]').val(webClaim.vendorCategoryId).trigger('change');
            }

            initVendorCategorySelect();

            $('select[name=country_id]').val("{{ Input::old('country_id') ?? $trackRecordProject->country_id }}").trigger("change");
        });
    </script>
@endsection('js')