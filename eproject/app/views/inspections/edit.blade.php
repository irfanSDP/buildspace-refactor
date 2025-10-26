@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{ link_to_route('projects.show', \PCK\Helpers\StringOperations::shorten($project->title, 50), array($project->id)) }}</li>
        <li>{{ link_to_route('inspection.request', trans('inspection.requestForInspection'), array($project->id)) }}</li>
        <li>{{ trans('inspection.inspectionX', array('no' => $inspection->revision+1)) }}</li>
    </ol>
@endsection

@section('content')
<?php use PCK\Inspections\InspectionListItem; ?>
    <div class="row">
        <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
            <h1 class="page-title txt-color-blueDark">
                <i class="fa fa-search"></i> {{{ trans('inspection.inspection') }}}
            </h1>
        </div>
    </div>
    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <div class="jarviswidget ">
                <header>
                    <h2> {{{ trans('inspection.inspectionX', array('no' => $inspection->revision+1)) }}} </h2>
                </header>
                <div>
                    <div class="widget-body no-padding">
                        {{ Form::model($inspection, array('route' => array('inspection.update', $project->id, $requestForInspection->id, $inspection->id), 'method' => 'POST', 'class' => 'smart-form', 'id' => 'add-form')) }}
                            <fieldset>
                                <div class="row">
                                    <section class="col col-xs-12 col-md-6 col-lg-6">
                                        <label class="label">{{{ trans('requestForInspection.location') }}}:</label>
                                        <label class="input">
                                            @foreach($locationsDescription as $description)
                                                <input type="text" value="{{ $description }}" disabled/>
                                            @endforeach
                                        </label>
                                    </section>
                                    <section class="col col-xs-12 col-md-6 col-lg-6">
                                        <label class="label">{{{ trans('requestForInspection.inspectionList') }}}:</label>
                                        <label class="input">
                                            @foreach($inspectionLists as $name)
                                                <input type="text" value="{{ $name }}" disabled/>
                                            @endforeach
                                        </label>
                                    </section>
                                </div>
                                <div class="well" data-id="list-category-info">
                                    <div class="row" data-id="dynamic-form">
                                        @foreach($additionalFields as $fieldInfo)
                                            <section class="col col-xs-12 col-md-6 col-lg-6">
                                                <label class="label">{{ $fieldInfo['name'] }}</label>
                                                <label class="input">
                                                    <input type="text" value="{{ $fieldInfo['value'] }}" disabled>
                                                </label>
                                            </section>
                                        @endForeach
                                    </div>
                                    <div id="inspection-list-items-table"></div>
                                    <br/>
                                    <div class="row">
                                        <section class="col col-xs-12 col-md-6 col-lg-6">
                                            <label class="label">{{{ trans('requestForInspection.inspectionReadyDate') }}}:</label>
                                            <label class="input {{{ $errors->has('ready_for_inspection_date') ? 'state-error' : null }}}">
                                                {{ Form::text('ready_for_inspection_date', Input::old('ready_for_inspection_date') ?? ($inspection->ready_for_inspection_date ? \Carbon\Carbon::parse($inspection->ready_for_inspection_date)->format(\Config::get('dates.readable_timestamp')) : null), array('class' => 'datetimepicker')) }}
                                            </label>
                                            {{ $errors->first('ready_for_inspection_date', '<em class="invalid">:message</em>') }}
                                        </section>
                                    </div>
                                </div>
                            </fieldset>
                            <footer>
                                {{ link_to_route('inspection.request', trans('forms.back'), array($project->id), array('class' => 'btn btn-default')) }}
                                <button type="button" class="btn btn-warning" data-toggle="modal" data-target="#inspections-overview-modal">{{ trans('inspection.overview') }}</button>
                                {{ Form::button('<i class="fa fa-save"></i> '.trans('forms.submit'), ['type' => 'submit', 'class' => 'btn btn-primary', 'name' => 'submit'] )  }}
                                {{ Form::button('<i class="fa fa-save"></i> '.trans('forms.saveAsDraft'), ['type' => 'submit', 'class' => 'btn btn-warning'] )  }}
                            </footer>
                        {{ Form::close() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@include('inspections.partials.overview_modal')
@endsection

@section('js')
    <link rel="stylesheet" href="{{ asset('js/eonasdan-bootstrap-datetimepicker/build/css/bootstrap-datetimepicker.min.css') }}" />
    <script type="text/javascript" src="{{ asset('js/moment/min/moment.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/eonasdan-bootstrap-datetimepicker/build/js/bootstrap-datetimepicker.min.js') }}"></script>
    <script>
        $('.datetimepicker').datetimepicker({
            format: 'DD-MMM-YYYY hh:mm A',
            stepping: '{{{ \Config::get('tender.MINUTES_INTERVAL') }}}',
            showTodayButton: true,
            allowInputToggle: true
        });
    </script>
    <script>
        var inspectionListItemTable = new Tabulator('#inspection-list-items-table', {
            maxheight:450,
            placeholder: "{{ trans('general.noRecordsFound') }}",
            layout:"fitColumns",
            columns:[
                {title:"{{ trans('general.no') }}", formatter:"rownum", width:60, hozAlign:'center', cssClass:"text-center text-middle", frozen:true, headerSort:false},
                {title:"{{ trans('requestForInspection.description') }}", field:"description", minWidth: 300, hozAlign:"left", frozen:true, headerSort:false, formatter: function(cell, formatterParams, onRendered) {
                    var rowData     = cell.getRow().getData();
                    var paddingLeft = rowData.depth * 16;
                    var style       = 'padding-left: ' + paddingLeft + 'px;';;

                    if(rowData.type == "{{ InspectionListItem::TYPE_HEAD }}") {
                        style += 'font-weight: bold;';
                    }

                    return `<span style="${ style }">${ rowData.description }</span>`;
                }},
                {
                    title:"{{ trans('inspection.inspectionX', array('no' => $inspection->revision+1)) }}",
                    columns: [
                        {title:"{{ trans('inspection.progress') }} (%)", field:"progress_status", minWidth: 30, width:110, hozAlign:"right", headerSort:false},
                        {title:"{{ trans('inspection.remarks') }}", field:"remarks", minWidth: 120, hozAlign:"center", headerSort:false},
                    ]
                },
                @if(isset($previousInspection))
                    {
                        title:"{{ trans('inspection.inspectionX', array('no' => $previousInspection->revision+1)) }}",
                        columns: [
                            {title:"{{ trans('inspection.progress') }} (%)", field:"progress_status-{{ $previousInspection->revision }}", minWidth: 30, width:110, hozAlign:"right", headerSort:false},
                            {title:"{{ trans('inspection.remarks') }}", field:"remarks-{{ $previousInspection->revision }}", minWidth: 120, hozAlign:"center", headerSort:false},
                        ]
                    },
                @endif
            ],
        });
        inspectionListItemTable.setData(webClaim.listItemData);
        inspectionListItemTable.redraw(true);
    </script>
@endsection