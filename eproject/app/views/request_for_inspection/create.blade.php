@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{ link_to_route('projects.show', \PCK\Helpers\StringOperations::shorten($project->title, 50), array($project->id)) }}</li>
        <li>{{ link_to_route('inspection.request', trans('requestForInspection.requestForInspection'), array($project->id)) }}</li>
        <li>{{ trans('requestForInspection.issueNew') }}</li>
    </ol>
@endsection

@section('content')
<?php use PCK\Inspections\InspectionListItem; ?>
    <div class="row">
        <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
            <h1 class="page-title txt-color-blueDark">
                <i class="fa fa-search"></i> {{{ trans('requestForInspection.issueNew') }}}
            </h1>
        </div>
    </div>
    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <div class="jarviswidget ">
                <header>
                    <h2> {{{ trans('requestForInspection.issueNew') }}} </h2>
                </header>
                <div>
                    <div class="widget-body no-padding">
                        @if(isset($requestForInspection))
                            {{ Form::model($requestForInspection, array('route' => array('inspection.request.update', $project->id, $requestForInspection->id), 'method' => 'PUT', 'class' => 'smart-form', 'id' => 'add-form')) }}
                        @else
                            {{ Form::open(array('route' => array('inspection.request.store', $project->id), 'class' => 'smart-form', 'id' => 'add-form')) }}
                        @endif
                            <fieldset>
                                <div class="row">
                                    <section class="col col-xs-12 col-md-6 col-lg-6">
                                        <label class="label">{{{ trans('requestForInspection.location') }}}<span class="required">*</span>:</label>
                                        <label class="input {{{ $errors->has('location_id') ? 'state-error' : null }}}">
                                            <input type="text" name="location_description[]" data-type="location-display" data-toggle="modal" data-target="#select-location-modal"/>
                                            {{ Form::hidden('location_id', Input::old('location_id')) }}
                                        </label>
                                        {{ $errors->first('location_id', '<em class="invalid">:message</em>') }}
                                    </section>
                                    <section class="col col-xs-12 col-md-6 col-lg-6">
                                        <label class="label">{{{ trans('requestForInspection.inspectionList') }}}<span class="required">*</span>:</label>
                                        <label class="input {{{ $errors->has('inspection_list_category_id') ? 'state-error' : null }}}">
                                            <input type="text" name="inspection_list_name[]" data-type="inspection-list-display" data-toggle="modal" data-target="#select-inspection-list-modal"/>
                                            {{ Form::hidden('inspection_list_category_id', Input::old('inspection_list_category_id')) }}
                                        </label>
                                        {{ $errors->first('inspection_list_category_id', '<em class="invalid">:message</em>') }}
                                    </section>
                                </div>
                                <div class="well" data-id="list-category-info" hidden>
                                    <div class="row" data-id="dynamic-form">
                                    </div>
                                    <div id="inspection-list-items-table"></div>
                                    <br/>
                                    <div class="row">
                                        <section class="col col-xs-12 col-md-6 col-lg-6">
                                            <label class="label">{{{ trans('requestForInspection.inspectionReadyDate') }}}:</label>
                                            <label class="input {{{ $errors->has('ready_for_inspection_date') ? 'state-error' : null }}}">
                                                <?php
                                                    $date = Input::old('ready_for_inspection_date');

                                                    if (!$date && isset($requestForInspection) && $requestForInspection->latestInspection)
                                                    {
                                                        $date = $requestForInspection->latestInspection->ready_for_inspection_date;
                                                    }

                                                    if (!$date)
                                                    {
                                                        $date = date('Y-m-d H:i');
                                                    }

                                                    $requestForInspectionDate = date('Y-m-d\TH:i', strtotime($date));
                                                ?>
                                                <input id="ready_for_inspection_date" name="ready_for_inspection_date" type="datetime-local" value="{{ $requestForInspectionDate }}" required>
                                            </label>
                                            {{ $errors->first('ready_for_inspection_date', '<em class="invalid">:message</em>') }}
                                        </section>
                                    </div>
                                </div>
                            </fieldset>
                            <footer>
                                {{ link_to_route('inspection.request', trans('forms.back'), array($project->id), array('class' => 'btn btn-default')) }}
                                {{ Form::button('<i class="fa fa-save"></i> '.trans('forms.submit'), ['type' => 'submit', 'class' => 'btn btn-primary', 'name' => 'submit'] )  }}
                                {{ Form::button('<i class="fa fa-save"></i> '.trans('forms.saveAsDraft'), ['type' => 'submit', 'class' => 'btn btn-warning'] )  }}
                            </footer>
                        {{ Form::close() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

<div data-category="template" hidden>
    <div data-type="dynamic-form-field">
        <section class="col col-xs-12 col-md-6 col-lg-6">
            <label class="label"></label>
            <label class="input">
                <input type="text" name="">
            </label>
        </section>
    </div>
</div>

<div class="modal fade" id="select-location-modal" tabindex="-1" role="dialog" aria-labelledby="editorLabel" aria-hidden="true" xmlns="http://www.w3.org/1999/html">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <div class="modal-header bg-grey-e">
                <h6 class="modal-title" data-id="editorLabel">
                    {{ trans('requestForInspection.location') }}
                </h6>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                    &times;
                </button>
            </div>

            <div class="modal-body">
                <ol id="locations-table-breadcrumbs" class="breadcrumb bg-transparent border border-info">
                </ol>
                <div id="locations-table"></div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="select-inspection-list-modal" tabindex="-1" role="dialog" aria-labelledby="editorLabel" aria-hidden="true" xmlns="http://www.w3.org/1999/html">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <div class="modal-header bg-grey-e">
                <h6 class="modal-title" data-id="editorLabel">
                    {{ trans('requestForInspection.inspectionList') }}
                </h6>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                    &times;
                </button>
            </div>

            <div class="modal-body">
                <ol id="inspection-list-table-breadcrumbs" class="breadcrumb bg-transparent border border-info">
                </ol>
                <div id="inspection-list-table"></div>
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
        $('.datetimepicker').datetimepicker({
            format: 'DD-MMM-YYYY hh:mm A',
            stepping: '{{{ \Config::get('tender.MINUTES_INTERVAL') }}}',
            showTodayButton: true,
            allowInputToggle: true
        });
    </script>
    <script>
        var locationsTableBreadcrumbs = new DynamicBreadcrumbs('#locations-table-breadcrumbs');
        var currentLevelLocationId = 0;
        var locationDescriptions = [];
        locationsTableBreadcrumbs.addItem("{{ trans('requestForInspection.locations') }}", 'project-locations');
        var locationsTable = new Tabulator('#locations-table', {
            minHeight:200,
            maxHeight:350,
            layout:"fitColumns",
            columns:[
                {title:"{{ trans('general.no') }}", formatter:"rownum", width:60, hozAlign:'center', cssClass:"text-center text-middle"},
                {title:"{{ trans('requestForInspection.location') }}", field:"description", minWidth: 300, hozAlign:"left",
                    cellClick:function(e, cell){
                        locationsTableBreadcrumbs.addItem(cell.getData().description, 'id-'+cell.getData().id, function(params){
                            locationsTable.setData("{{ route('inspection.getLocationByLevel', array($project->id)) }}", {id: params['parentItemId']});
                            currentLevelLocationId = params['parentItemId'];
                            locationDescriptions.pop();
                        }, {parentItemId: currentLevelLocationId});
                        locationsTable.setData("{{ route('inspection.getLocationByLevel', array($project->id)) }}", {id: cell.getData().id});
                        currentLevelLocationId = cell.getData().id;

                        locationDescriptions.push(cell.getData().description);
                    }
                },
                {title:"{{ trans('general.action') }}", minWidth: 100, width:100, hozAlign:"center", formatter:function(cell){
                    var id = cell.getRow().getIndex();
                    return '<button type="button" class="btn btn-xs btn-warning" data-action="select-location" data-id="'+id+'">{{ trans("forms.select") }}</button';
                }},
            ],
        });
        locationsTable.setData("{{ route('inspection.getLocationByLevel', array($project->id)) }}");
        function updateLocationDisplay(selectedLocationDescription){
            var clone;
            var firstLocationInput = $('#add-form input[data-type=location-display]').first();
            firstLocationInput.siblings().remove('input[data-type=location-display]');
            firstLocationInput.val(selectedLocationDescription);
            locationDescriptions.forEach(function(description){
                clone = firstLocationInput.clone();
                clone.val(description);
                firstLocationInput.before(clone);
            });
        }
        $('#locations-table').on('click', '[data-action=select-location]', function(){
            $('#add-form input[name=location_id]').val($(this).data('id'));
            var rowData = locationsTable.getRow($(this).data('id')).getData();
            updateLocationDisplay(rowData['description']);
            $('#select-location-modal').modal('hide');
        });

        if(webClaim.formData.locations !== null && typeof webClaim.formData.locations === 'object'){
            webClaim.formData.locations.forEach(function(description){
                locationDescriptions.push(description);
            });
            var selectedLocationDescription = locationDescriptions.pop();
            updateLocationDisplay(selectedLocationDescription);
            locationDescriptions = [];
        }

        var inspectionListTableBreadcrumbs = new DynamicBreadcrumbs('#inspection-list-table-breadcrumbs');
        var currentLevelInspectionListId = 0;
        var inspectionListNames = [];
        inspectionListTableBreadcrumbs.addItem("{{ trans('requestForInspection.inspectionLists') }}", 'inspection-lists');
        var inspectionListsTable = new Tabulator('#inspection-list-table', {
            minHeight:200,
            maxHeight:350,
            layout:"fitColumns",
            columns:[
                {title:"{{ trans('general.no') }}", formatter:"rownum", width:60, hozAlign:'center', cssClass:"text-center text-middle"},
                {title:"{{ trans('requestForInspection.name') }}", field:"name", minWidth: 300, hozAlign:"left",
                    cellClick:function(e, cell){
                        if(!cell.getData().selectable)
                        {
                            inspectionListTableBreadcrumbs.addItem(cell.getData().name, 'id-'+cell.getData().id, function(params){
                                inspectionListsTable.setData("{{ route('inspection.getInspectionListByLevel', array($project->id)) }}", {id: params['parentItemId']});
                                currentLevelInspectionListId = params['parentItemId'];
                                inspectionListNames.pop();
                            }, {parentItemId: currentLevelInspectionListId});
                            inspectionListsTable.setData("{{ route('inspection.getInspectionListByLevel', array($project->id)) }}", {id: cell.getData().id});
                            currentLevelInspectionListId = cell.getData().id;

                            inspectionListNames.push(cell.getData().name);
                        }
                    }
                },
                {title:"{{ trans('general.action') }}", minWidth: 100, width:100, hozAlign:"center", formatter:function(cell){
                    var id = cell.getRow().getIndex();
                    if(cell.getRow().getData().selectable){
                        return '<button type="button" class="btn btn-xs btn-warning" data-action="select-inspection-list" data-id="'+id+'">{{ trans("forms.select") }}</button';
                    }
                }},
            ],
        });
        inspectionListsTable.setData("{{ route('inspection.getInspectionListByLevel', array($project->id)) }}");
        function updateInspectionListDisplay(selectedListName){
            var clone;
            var firstInput = $('#add-form input[data-type=inspection-list-display]').first();
            firstInput.siblings().remove('input[data-type=inspection-list-display]');
            firstInput.val(selectedListName);
            inspectionListNames.forEach(function(name){
                clone = firstInput.clone();
                clone.val(name);
                firstInput.before(clone);
            });
        }
        var inspectionListItemTable = new Tabulator('#inspection-list-items-table', {
            minHeight:200,
            maxHeight:450,
            layout:"fitColumns",
            placeholder: "{{ trans('inspection.listEmpty') }}",
            columns:[
                {title:"{{ trans('general.no') }}", formatter:"rownum", width:60, hozAlign:'center', cssClass:"text-center text-middle"},
                {title:"{{ trans('requestForInspection.description') }}", field:"description", minWidth: 300, hozAlign:"left", formatter: function(cell, formatterParams, onRendered) {
                    var rowData     = cell.getRow().getData();
                    var paddingLeft = rowData.depth * 16;
                    var style       = 'padding-left: ' + paddingLeft + 'px;';;

                    if(rowData.type == "{{ InspectionListItem::TYPE_HEAD }}") {
                        style += 'font-weight: bold;';
                    }

                    return `<span style="${ style }">${ rowData.description }</span>`;
                }}
            ],
        });
        function addFieldToForm(label, name, value){
            var element = $('[data-category=template] [data-type=dynamic-form-field] section').clone();
            element.children('label.label').html(label + ':');
            element.find('label.input input').prop('name', name);
            element.find('label.input input').val(value);
            $('[data-id=dynamic-form]').append(element);
        }
        function loadRequestForInspectionDetails(listCategoryId){
            $.get("{{ route('inspection.request.listCategoryFormDetails', array($project->id)) }}", {list_category_id: listCategoryId})
            .done(function(data){
                $('[data-id=dynamic-form]').empty();
                $('[data-id=list-category-info]').show();
                for(var key in data.additionalFields){
                    addFieldToForm(data.additionalFields[key]['name'], 'additional_fields['+data.additionalFields[key]['id']+']', data.additionalFields[key]['value']);
                }
                if(webClaim.formData.additionalFields !== null && typeof webClaim.formData.additionalFields === 'object'){
                    for(var i in webClaim.formData.additionalFields){
                        $('[data-id=dynamic-form] [name="additional_fields['+i+']"]').val(webClaim.formData.additionalFields[i]);
                    }
                }
                inspectionListItemTable.setData(data.listItems);
            })
            .fail(function(data){
                console.error('failed');
            });
        }
        $('#inspection-list-table').on('click', '[data-action=select-inspection-list]', function(){
            $('#add-form input[name=inspection_list_category_id]').val($(this).data('id'));
            var rowData = inspectionListsTable.getRow($(this).data('id')).getData();
            updateInspectionListDisplay(rowData['name']);
            $('#select-inspection-list-modal').modal('hide');
            loadRequestForInspectionDetails($(this).data('id'));
        });

        // Pre-populate values
        if(webClaim.formData.inspectionLists !== null && typeof webClaim.formData.inspectionLists === 'object'){
            webClaim.formData.inspectionLists.forEach(function(name){
                inspectionListNames.push(name);
            });
            var selectedInspectionListName = inspectionListNames.pop();
            updateInspectionListDisplay(selectedInspectionListName);
            inspectionListNames = [];
        }
        if($('#add-form input[name=inspection_list_category_id]').val() !== ''){
            loadRequestForInspectionDetails($('#add-form input[name=inspection_list_category_id]').val());
        }
    </script>
@endsection