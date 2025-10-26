@extends('layout.main')

@section('css')
    <link href="{{ asset('js/summernote-master/dist/summernote.css') }}" rel="stylesheet">
@endsection

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{ link_to_route('costData', trans('costData.costData')) }}</li>
        <li>{{{ trans('forms.edit') }}}</li>
    </ol>
@endsection

@section('content')

    <div class="row">
        <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
            <h1 class="page-title txt-color-blueDark">
                <i class="fas fa-map"></i> {{{ trans('costData.costData') }}}
            </h1>
        </div>

        <div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">
        </div>
    </div>

    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <div class="jarviswidget ">
                <header>
                    <h2> {{{ trans('costData.costData') }}} </h2>
                </header>
                <div>
                    <div class="widget-body no-padding">
                        {{ Form::model($costData, array('method' => 'put', 'class' => 'smart-form', 'id' => 'cost-data-form')) }}
                            @include('costData.formFields')
                            <footer>
                                <a href="{{ route('costData') }}" class="btn btn-default">{{ trans('forms.back') }}</a>
                                <button class="btn btn-primary"><i class="fa fa-save" aria-hidden="true"></i> {{ trans('general.save') }}</button>
                            </footer>
                        {{ Form::close() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
    @include('templates.generic_table_modal', ['modalId' => 'project-options-list-modal', 'title' => trans('projects.projects'), 'tableId' => 'project-option-list-table', 'modalDialogClass' => 'modal-xl fill-horizontal modal-dialog-centered'])
@endsection

@section('js')
    <script src="{{ asset('js/summernote-master/dist/summernote.min.js') }}"></script>
    <script src="{{ asset('js/app/app.dependentSelection.js') }}"></script>
    <script>
        var dependentSelection = $.extend({}, DependentSelection);
        dependentSelection.setUrls({first: webClaim.urlRegions, second: webClaim.urlSubregions});
        dependentSelection.setForms({first: $('form#cost-data-form [name=region_id]'), second: $('form#cost-data-form [name="subregion_id"]')});
        dependentSelection.setSelectedIds({first: webClaim.regionId, second: webClaim.subregionId});
        dependentSelection.setPreSelectOnLoad({first: true, second: false});
        dependentSelection.init();

        $('.summernote').summernote({
            placeholder: "{{ trans('costData.notes') }}",
            toolbar: [
                ['style', ['bold', 'italic', 'underline', 'clear']],
                ['insert', ['picture', 'table', 'hr']],
                ['color', ['color']],
                ['para', ['style', 'ol', 'ul', 'paragraph', 'height']],
                ['codeview', ['codeview']],
                ['help', ['help']],
                ['view', ['fullscreen']]
            ]
        });

        var selectedProjectIds    = webClaim.projectIds;
        var notSelectedProjectIds = [];

        $('form#cost-data-form').on('submit', function(e){
            var notes = $('form#cost-data-form [data-input=notes]').code();
            $('<input />').attr('type', 'hidden')
                  .attr('name', "notes")
                  .attr('value', notes)
                  .appendTo('form#cost-data-form');

            for(var i in selectedProjectIds){
                $('<input />').attr('type', 'hidden')
                  .attr('name', "project_id[]")
                  .attr('value', selectedProjectIds[i])
                  .appendTo('form#cost-data-form');
            }

            return true;
        });

        $('select[name=currency_id]').val(webClaim.currencyId).trigger("change");

        var projectListTable = new Tabulator('#project-list-table', {
            height:300,
            placeholder: "{{ trans('general.noRecordsFound') }}",
            ajaxConfig: "GET",
            paginationSize: 100,
            pagination: "remote",
            ajaxFiltering:true,
            layout:"fitColumns",
            columns:[
                {title:"{{ trans('general.no') }}", field:"counter", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
                {title:"{{ trans('projects.reference') }}", field:"reference", width:150, cssClass:"text-center text-middle", headerSort:false, headerFilter:true},
                {title:"{{ trans('projects.title') }}", field:"title", minWidth: 300, hozAlign:"left", headerSort:false, headerFilter: true},
                {title:"{{ trans('projects.country') }}", field:"country", width:180, hozAlign:"center", headerSort:false, headerFilter:true},
                {title:"{{ trans('projects.state') }}", field:"state", width:180, hozAlign:"center", headerSort:false, headerFilter:true},
                {title:"{{ trans('projects.status') }}", field: 'status', cssClass:"text-center text-middle", width: 140, headerSort:false, headerFilterPlaceholder: "{{ trans('general.filter') }}", editable: false, editor:"select", headerFilter:true, headerFilterParams:{{ json_encode($projectStatuses) }} },
                {title:"{{ trans('general.actions') }}", width: 120, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                    tag: 'button',
                    attributes: {type: 'button', 'data-action': 'delete', class:'btn btn-xs btn-danger', title: '{{ trans("general.remove") }}'},
                    rowAttributes: {'data-id': 'id'},
                    innerHtml: {
                        tag: 'i',
                        attributes: {class: 'fa fa-trash'}
                    }
                }}
            ]
        });

        projectListTable.setData('{{ route("costData.listProjects") }}', {subsidiaryId: $('form#cost-data-form [name=subsidiary_id]').val(), projectIds: selectedProjectIds});

        var projectOptionListTable = new Tabulator('#project-option-list-table', {
            height:450,
            placeholder: "{{ trans('general.noRecordsFound') }}",
            ajaxConfig: "GET",
            paginationSize: 100,
            pagination: "remote",
            ajaxFiltering:true,
            layout:"fitColumns",
            columns:[
                {title:"{{ trans('general.no') }}", field:"counter", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
                {title:"{{ trans('projects.reference') }}", field:"reference", width:150, cssClass:"text-center text-middle", headerSort:false, headerFilter:true},
                {title:"{{ trans('projects.title') }}", field:"title", minWidth: 300, hozAlign:"left", headerSort:false, headerFilter: true},
                {title:"{{ trans('projects.country') }}", field:"country", width:180, hozAlign:"center", headerSort:false, headerFilter:true},
                {title:"{{ trans('projects.state') }}", field:"state", width:180, hozAlign:"center", headerSort:false, headerFilter:true},
                {title:"{{ trans('projects.status') }}", field: 'status', cssClass:"text-center text-middle", width: 140, headerSort:false, headerFilterPlaceholder: "{{ trans('general.filter') }}", editable: false, editor:"select", headerFilter:true, headerFilterParams:{{ json_encode($projectStatuses) }} },
                {title:"{{ trans('general.actions') }}", width: 120, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                    tag: 'button',
                    attributes: {type: 'button', 'data-action': 'select', class:'btn btn-xs btn-default', title: '{{ trans("forms.select") }}'},
                    rowAttributes: {'data-id': 'id'},
                    innerHtml: function(rowData)
                    {
                        return "{{ trans('forms.select') }}";
                    }
                }}
            ]
        });

        $('[data-action=list-project-options]').on('click', function(){
            projectOptionListTable.setData('{{ route("costData.listProjectOptions") }}', {subsidiaryId: $('form#cost-data-form [name=subsidiary_id]').val(), projectIds: selectedProjectIds, notSelectedProjectIds: notSelectedProjectIds});
            $('#project-options-list-modal').modal('show');
        });

        $('#project-list-table').on('click', '[data-action=delete]', function(){
            var index = selectedProjectIds.indexOf($(this).data('id'));
            if (index > -1) {
                selectedProjectIds.splice(index, 1);

                if (! notSelectedProjectIds.includes($(this).data('id'))) notSelectedProjectIds.push($(this).data('id'));
            }

            projectListTable.deleteRow($(this).data('id'));
            //projectOptionListTable.setData('{{ route("costData.listProjectOptions") }}', {subsidiaryId: $('form#cost-data-form [name=subsidiary_id]').val(), projectIds: selectedProjectIds, costDataId: {{ $costData->id }}});
        });

        $('#project-option-list-table').on('click', '[data-action=select]', function(){
            if(!selectedProjectIds.includes($(this).data('id'))) selectedProjectIds.push($(this).data('id'));

            if (notSelectedProjectIds.includes($(this).data('id'))) {
                var index = notSelectedProjectIds.indexOf($(this).data('id'));
                if (index > -1) {
                    notSelectedProjectIds.splice(index, 1);
                }
            }

            projectOptionListTable.deleteRow($(this).data('id'));
            projectListTable.setData('{{ route("costData.listProjects") }}', {subsidiaryId: $('form#cost-data-form [name=subsidiary_id]').val(), projectIds: selectedProjectIds});
        });

        // $('form#cost-data-form [name=subsidiary_id]').on('change', function(){
        //     selectedProjectIds = [];
        //     projectListTable.setData('{{ route("costData.listProjects") }}', {subsidiaryId: $('form#cost-data-form [name=subsidiary_id]').val(), projectIds: selectedProjectIds});
        // });
    </script>
@endsection