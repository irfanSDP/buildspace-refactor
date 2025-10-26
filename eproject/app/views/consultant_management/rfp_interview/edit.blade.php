@extends('layout.main')

@section('css')
    <link href="{{ asset('js/summernote/summernote.min.css') }}" rel="stylesheet">
@endsection

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('consultant.management.contracts.index', trans('navigation/mainnav.home')) }}</li>
        <li>{{ link_to_route('consultant.management.calling.rfp.show', $vendorCategoryRfp->vendorCategory->name, [$vendorCategoryRfp->id, $callingRfp->id]) }}</li>
        <li>{{ link_to_route('consultant.management.consultant.rfp.interview.index', trans('general.consultantInterview'), [$vendorCategoryRfp->id, $callingRfp->id]) }}</li>

        @if(isset($rfpInterview))
        <li>{{ link_to_route('consultant.management.consultant.rfp.interview.show', trans('general.view'), [$vendorCategoryRfp->id, $callingRfp->id, $rfpInterview->id]) }}</li>
        <li>{{{ trans('forms.edit') }}} {{{ trans('general.consultantInterview') }}}</li>
        @else
        <li>{{{ trans('general.new') }}} {{{ trans('general.consultantInterview') }}}</li>
        @endif
    </ol>
@endsection

@section('content')
<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <h1 class="page-title txt-color-blueDark">
            <i class="fa fa-comments"></i> @if(isset($rfpInterview)) {{{ trans('forms.edit') }}} @else {{{ trans('general.new') }}} @endif {{{ trans('general.consultantInterview') }}}
        </h1>
    </div>
</div>

<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="jarviswidget ">
            <header>
                <h2>@if(isset($rfpInterview)) {{{ trans('forms.edit') }}} @else {{{ trans('general.new') }}} @endif {{{ trans('general.consultantInterview') }}}</h2>
            </header>
            <div>
                <div class="widget-body">
                    {{ Form::open(['route' => ['consultant.management.consultant.rfp.interview.store', $vendorCategoryRfp->id], 'class' => 'smart-form']) }}
                    <div class="row">
                        <section class="col col-xs-12 col-md-12 col-lg-12">
                            <label class="label">{{{ trans('general.title') }}} <span class="required">*</span>:</label>
                            <label class="input {{{ $errors->has('title') ? 'state-error' : null }}}">
                                {{ Form::text('title', Input::old('title', isset($rfpInterview) ? $rfpInterview->title : null), ['required' => 'required', 'autofocus' => 'autofocus']) }}
                            </label>
                            {{ $errors->first('title', '<em class="invalid">:message</em>') }}
                        </section>
                    </div>
                    <div class="row">
                        <section class="col col-xs-12 col-md-12 col-lg-12">
                            <label class="label">{{{ trans('companies.details') }}} :</label>
                            <label class="input {{{ $errors->has('details') ? 'state-error' : null }}}">
                                {{ Form::textarea('details', Input::old('details', isset($rfpInterview) ? $rfpInterview->details : null), ['id'=>'interview_details-txt', 'autofocus' => 'autofocus', 'class'=>'summernote']) }}
                            </label>
                            {{ $errors->first('details', '<em class="invalid">:message</em>') }}
                        </section>
                    </div>
                    <div class="row">
                        <section class="col col-xs-12 col-md-4 col-lg-4">
                            <label class="label">{{{ trans('general.date') }}} <span class="required">*</span>:</label>
                            <label class="input {{{ $errors->has('interview_date') ? 'state-error' : null }}}">
                                <?php
                                    $date      = Input::old('interview_date', isset($rfpInterview) ? $consultantManagementContract->getContractTimeZoneTime($rfpInterview->interview_date) : null);
                                    $timestamp = strtotime($date);

                                    if ($timestamp === false) {
                                        $timestamp = time();
                                    }

                                    $interviewDate = date('Y-m-d', $timestamp);
                                ?>
                                <input id="interview_date" name="interview_date" type="date" value="{{ $interviewDate }}" required>
                            </label>
                            {{ $errors->first('interview_date', '<em class="invalid">:message</em>') }}
                        </section>
                    </div>

                    <div id="rfp_interview-consultants">
                        <hr class="simple">
                        <div class="row">
                            <section class="col col-xs-6 col-sm-6 col-md-8 col-lg-8">
                                <h5><i class="fa fa-users"></i> Consultant(s)</h5>
                            </section>
                            <section class="col col-xs-6 col-sm-6 col-md-4 col-lg-4">
                                <div class="pull-right">
                                {{ Form::button('<i class="fa fa-plus"></i> '.trans('forms.add')." ".trans('vendorManagement.consultant'), ['id'=>'addConsultantBtn', 'type'=>'button', 'class' => 'btn btn-info'] )  }}
                                </div>
                            </section>
                        </div>
                        <div class="row">
                            <section class="col col-xs-12 col-md-12 col-lg-12">
                                <label class="input {{{ $errors->has('consultants') ? 'state-error' : null }}}"></label>
                                {{ $errors->first('consultants', '<em class="invalid">:message</em>') }}
                                <table class="table table-bordered table-condensed table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th style="width:auto;">Consultant(s)</th>
                                            <th style="width:200px;text-align:center;">{{{ trans('general.time') }}}</th>
                                            <th style="width:82px;text-align:center;">{{{trans('forms.delete')}}}</th>
                                        </tr>
                                    </thead>
                                    <tbody id="consultantRow" class="consultantRow">
                                    <?php
                                    $consultants = Input::old('consultants', $consultants);
                                    ?>
                                    @foreach($consultants as $consultantIdx => $consultant)
                                        <tr class="consultantRecordRow">
                                            <td>
                                                <div class="row">
                                                    <section class="col col-xs-12 col-sm-12 col-md-12 col-lg-12">
                                                        <label class="label">{{ Input::old('consultants.'.$consultantIdx.'.name', $consultant['name']) }}</label>
                                                        {{ Form::hidden('consultants['.$consultantIdx.'][name]', Input::old('consultants.'.$consultantIdx.'.name', $consultant['name'])) }}
                                                        {{ Form::hidden('consultants['.$consultantIdx.'][id]', Input::old('consultants.'.$consultantIdx.'.id', $consultant['id']), ['class'=>'consultant_id-hidden']) }}
                                                        @if($errors->has('consultants.'.$consultantIdx.'.admin_user'))
                                                        <label class="input state-error"></label>
                                                        {{ $errors->first('consultants.'.$consultantIdx.'.admin_user', '<em class="invalid">:message</em>') }}
                                                        @endif
                                                    </section>
                                                </div>
                                                <div class="row">
                                                    <section class="col col-xs-12 col-sm-12 col-md-12 col-lg-12">
                                                        <label class="label">{{{ trans('general.remarks') }}} :</label>
                                                        <label class="textarea {{{ $errors->has('consultants.'.$consultantIdx.'.remarks') ? 'state-error' : null }}}">
                                                            {{ Form::textarea('consultants['.$consultantIdx.'][remarks]', Input::old('consultants.'.$consultantIdx.'.remarks', $consultant['remarks']), ['autofocus' => 'autofocus', 'rows' => 3]) }}
                                                        </label>
                                                        {{ $errors->first('consultants.'.$consultantIdx.'.remarks', '<em class="invalid">:message</em>') }}
                                                    </section>
                                                </div>
                                            </td>
                                            <td style="vertical-align:top;text-align:center;">
                                                <div class="row">
                                                    <section class="col col-xs-12 col-md-12 col-lg-12">
                                                        <label class="input {{{ $errors->has('consultants.'.$consultantIdx.'.interview_timestamp') ? 'state-error' : null }}}">
                                                            <?php
                                                                $date      = Input::old('consultants.'.$consultantIdx.'.interview_timestamp', $consultantManagementContract->getContractTimeZoneTime($consultant['interview_timestamp']));
                                                                $timestamp = strtotime($date);

                                                                if ($timestamp === false) {
                                                                    $timestamp = time();
                                                                }

                                                                $consultantInterviewDate = date('Y-m-d\TH:i:s', $timestamp);
                                                            ?>
                                                            <input id="{{ 'consultants['.$consultantIdx.'][interview_timestamp]' }}" name="{{ 'consultants['.$consultantIdx.'][interview_timestamp]' }}" type="datetime-local" value="{{ $consultantInterviewDate }}" required>
                                                        </label>
                                                        {{ $errors->first('consultants.'.$consultantIdx.'.interview_timestamp', '<em class="invalid">:message</em>') }}
                                                    </section>
                                                </div>
                                            </td>
                                            <td style="vertical-align:top;text-align:center;">
                                                {{ Form::button('<i class="fa fa-trash"></i>', ['type'=>'button', 'class' => 'btn btn-md btn-danger deleteConsultantBtn', 'title'=>trans('forms.delete')] ) }}
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </section>
                        </div>
                    </div>

                    <footer>
                        {{ Form::hidden('id', (isset($rfpInterview)) ? $rfpInterview->id : -1) }}
                        {{ Form::hidden('vendor_category_rfp_id', $vendorCategoryRfp->id) }}
                        {{ Form::hidden('calling_rfp_id', $callingRfp->id) }}
                        @if(!isset($rfpInterview))
                        {{ link_to_route('consultant.management.consultant.rfp.interview.index', trans('forms.back'), [$vendorCategoryRfp->id, $callingRfp->id], ['class' => 'btn btn-default']) }}
                        @else
                        {{ link_to_route('consultant.management.consultant.rfp.interview.show', trans('forms.back'), [$vendorCategoryRfp->id, $callingRfp->id, $rfpInterview->id], ['class' => 'btn btn-default']) }}
                        @endif
                        {{ Form::button('<i class="fa fa-save"></i> '.trans('forms.save'), ['type' => 'submit', 'class' => 'btn btn-primary'] )  }}
                    </footer>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="consultantListModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title"><i class="fa fa-users"></i> Consultant(s)</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body no-padding">
                <div id="consultant_list-table"></div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary" id="add_consultant-btn"><i class="fa fa-plus"></i> {{ trans('forms.add') }}</button>
                <button class="btn btn-default btn-md" data-dismiss="modal">{{{ trans('forms.close') }}}</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/handlebars.js/4.7.6/handlebars.min.js"></script>
<script id="document-template" type="text/x-handlebars-template">
    <tr class="consultantRecordRow">
        <td>
            <div class="row">
                <section class="col col-xs-12 col-sm-12 col-md-12 col-lg-12">
                    <label class="label">@{{consultantName}}</label>
                    <input type="hidden" name="consultants[@{{inputIdx}}][name]" value="@{{consultantName}}">
                    <input type="hidden" name="consultants[@{{inputIdx}}][id]" value="@{{consultantId}}" class="consultant_id-hidden">
                </section>
            </div>
            <div class="row">
                <section class="col col-xs-12 col-sm-12 col-md-12 col-lg-12">
                    <label class="label">{{{ trans('general.remarks') }}} :</label>
                    <label class="textarea">
                        <textarea autofocus="autofocus" rows="3" name="consultants[@{{inputIdx}}][remarks]"></textarea>
                    </label>
                </section>
            </div>
        </td>
        <td style="vertical-align:top;text-align:center;">
            <div class="row">
                <section class="col col-xs-12 col-md-12 col-lg-12">
                    <label class="input">
                        <input type="datetime-local" name="consultants[@{{inputIdx}}][interview_timestamp]" required="required">
                    </label>
                </section>
            </div>
        </td>
        <td style="vertical-align:top;text-align:center;">
            {{ Form::button('<i class="fa fa-trash"></i>', ['type'=>'button', 'class' => 'btn btn-md btn-danger deleteConsultantBtn', 'title'=>trans('forms.delete')] ) }}
        </td>
    </tr>
</script>
<script src="{{ asset('js/summernote/summernote.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/moment/min/moment.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/eonasdan-bootstrap-datetimepicker/build/js/bootstrap-datetimepicker.min.js') }}"></script>
<script type="text/javascript">
$(document).ready(function () {
    $('#interview_details-txt').summernote({
        focus: true,
        disableResizeEditor: true,
        placeholder: "{{ trans('companies.details') }}",
        toolbar: [
            ['style', ['bold', 'italic', 'underline', 'clear']],
            ['insert', ['link', 'picture', 'table', 'hr']],
            ['color', ['color']],
            ['para', ['style', 'ol', 'ul', 'paragraph', 'height']],
            ['codeview', ['codeview']],
            ['help', ['help']],
            ['view', ['fullscreen']]
        ]
    });
    $('.note-statusbar').hide();//remove resize bar

    $('#addConsultantBtn').on('click', function(e){
        e.preventDefault();
        var table = Tabulator.prototype.findTable("#consultant_list-table")[0];
        var excludeIds;
        excludeIds = $('tbody#consultantRow tr .consultant_id-hidden').map(function(i, v) {
            return this.value;
        }).get();

        var ajaxParams = (excludeIds && excludeIds.length) ? {ids: excludeIds} : {ids:[-1]};
        if(!table){
            table = new Tabulator('#consultant_list-table', {
                selectable:true,
                height:320,
                columns: [
                    {formatter: "rowSelection", titleFormatter: "rowSelection", cssClass:"text-center text-middle", field: 'id', width: 12, hozAlign: 'center', headerSort:false},
                    {title:"{{ trans('vendorManagement.consultant') }}", field:"name", minWidth: 300, hozAlign:"left", headerSort:false},
                    {title:"{{ trans('companies.referenceNumber') }}", field:"reference_no", width:180, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false}
                ],
                layout:"fitColumns",
                ajaxURL: '{{ route('consultant.management.consultant.rfp.interview.consultant.list', [$vendorCategoryRfp->id, $callingRfp->id]) }}',
                ajaxConfig: "GET",
                ajaxParams: ajaxParams,
                placeholder:"{{ trans('general.noRecordsFound') }}"
            });
        }else{
            table.setData('{{route('consultant.management.consultant.rfp.interview.consultant.list', [$vendorCategoryRfp->id, $callingRfp->id])}}', ajaxParams);
        }

        $('#consultantListModal').modal('show');
    });

    $('#add_consultant-btn').on('click', function(e){
        e.preventDefault();
        var table = Tabulator.prototype.findTable("#consultant_list-table")[0];
        if(table){
            var selectedData = table.getSelectedData();
            $.each(selectedData, function(idx, data){
                var source = $("#document-template").html();
                var template = Handlebars.compile(source);
                var inputIdx = 1;

                $('.consultantRecordRow').each(function(i, fields){
                    $('select,input', fields).each(function(){
                        // Rename first array value from name to group index
                        $(this).attr('name', $(this).attr('name').replace(/e\[[^\]]*\]/, 'e['+i+']')); 
                    });
                    i++;
                    inputIdx++;
                });
                
                var html = template({
                    inputIdx:inputIdx,
                    consultantName: data.name,
                    consultantId: data.id
                });
                html = $(html);
                html.find('.datetimepicker').datetimepicker({
                    format: 'DD-MMM-YYYY hh:mm A',
                    stepping: '{{{ \Config::get('tender.MINUTES_INTERVAL') }}}',
                    showTodayButton: true,
                    allowInputToggle: true
                });
                $("#consultantRow").append(html);
            });
            $('#consultantListModal').modal('hide');
        }
    });

    $(document).on('click','.deleteConsultantBtn',function(event){
        event.preventDefault();
        $(this).closest('tr.consultantRecordRow').remove();
        $('.consultantRecordRow').each(function(i, fields){
            $('select,input', fields).each(function(){
                $(this).attr('name', $(this).attr('name').replace(/e\[[^\]]*\]/, 'e['+i+']')); 
            });
            i++;
        });
    });
});
</script>
@endsection