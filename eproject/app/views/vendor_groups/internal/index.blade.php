@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{{ trans('contractGroupCategories.internalVendorGroups') }}}</li>
    </ol>
@endsection

@section('content')
<div class="row">
    <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
        <h1 class="page-title txt-color-blueDark">
            <i class="fa fa-users"></i> {{{ trans('contractGroupCategories.internalVendorGroups') }}}
        </h1>
    </div>
    <div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">
        <a href="{{ route('vendorGroups.internal.create') }}" class="btn btn-primary btn-md pull-right header-btn">
            <i class="fa fa-plus"></i> {{{ trans('forms.add') }}}
        </a>
    </div>
</div>

<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="jarviswidget ">
            <header>
                <h2>{{ trans('contractGroupCategories.internalVendorGroups') }}</h2>
            </header>
            <div>
                <div class="widget-body">
                    <div id="main-table"></div>
                    {{ Form::open(array('route' => array('vendorGroups.internal.updateSettings'))) }}
                    @foreach($recordIds as $id)
                    <input hidden type="checkbox" name="hide-id[{{ $id }}]" value="{{ $id }}"/>
                    <input hidden type="checkbox" name="buildspace-access-id[{{ $id }}]" value="{{ $id }}"/>
                    @endforeach
                    <footer class="pull-right">
                        <button class="btn btn-primary"><i class="fa fa-save"></i> {{ trans('forms.save') }}</button>
                    </footer>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
    <script>
        $(document).ready(function () {
            var mainTable = new Tabulator('#main-table', {
                height:450,
                placeholder: "{{ trans('general.noRecordsFound') }}",
                ajaxURL: "{{ route('vendorGroups.internal.ajax.list') }}",
                ajaxConfig: "GET",
                paginationSize: 100,
                pagination: "remote",
                ajaxFiltering:true,
                layout:"fitColumns",
                dataLoaded: function(data){
                    var selectedIds = {{ json_encode($hiddenIds) }};

                    for(var i in selectedIds){
                        $('input[type=checkbox][name="hide-id['+selectedIds[i]+']"]').prop('checked', true);
                    }

                    selectedIds = {{ json_encode($defaultBuildspaceAccessIds) }};

                    for(var i in selectedIds){
                        $('input[type=checkbox][name="buildspace-access-id['+selectedIds[i]+']"]').prop('checked', true);
                    }
                },
                columns:[
                    {title:"{{ trans('general.no') }}", field:"counter", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
                    {title:"{{ trans('contractGroupCategories.code') }}", field:"code", width: 150, cssClass:"text-center text-middle", headerSort:true, headerFilter:"input", headerFilterPlaceholder: "{{ trans('general.filter') }}"},
                    {title:"{{ trans('contractGroupCategories.name') }}", field:"name", minWidth: 300, hozAlign:"left", headerSort:true, headerFilter:"input", headerFilterPlaceholder: "{{ trans('general.filter') }}"},
                    {title:"{{ trans('contractGroupCategories.hidden') }}", field: 'hidden', width: 100, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                        innerHtml: function(rowData){
                            if(rowData.hasOwnProperty('id')){
                                var checked = rowData['hidden'] ? 'checked' : '';
                                return '<input type="checkbox" data-action="hide" data-id="'+rowData['id']+'" '+checked+'>';
                            }
                        }
                    }},
                    @if($currentUser->isSuperAdmin())
                    {title:"{{ trans('contractGroupCategories.defaultBuildSpaceAccess') }}", field: 'default_buildspace_access', width: 180, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                        innerHtml: function(rowData){
                            if(rowData.hasOwnProperty('id')){
                                var checked = rowData['default_buildspace_access'] ? 'checked' : '';
                                return '<input type="checkbox" data-action="toggle-default-buildspace-access" data-id="'+rowData['id']+'" '+checked+'>';
                            }
                        }
                    }},
                    @endif
                    {title:"{{ trans('general.actions') }}", width: 120, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                        innerHtml: [{
                            tag: 'a',
                            attributes: {class:'btn btn-xs btn-warning', title: '{{ trans("forms.edit") }}'},
                            rowAttributes: {'href': 'route:edit'},
                            innerHtml: {
                                tag: 'i',
                                attributes: {class: 'fa fa-edit'}
                            }
                        }]
                    }}
                ],
            });

            $('#main-table').on('click', 'input[data-action=hide]', function(){
                $('input[type=checkbox][name="hide-id['+$(this).data('id')+']"]').prop('checked', $(this).prop('checked'));
            });

            @if($currentUser->isSuperAdmin())
            $('#main-table').on('click', 'input[data-action=toggle-default-buildspace-access]', function(){
                $('input[type=checkbox][name="buildspace-access-id['+$(this).data('id')+']"]').prop('checked', $(this).prop('checked'));
            });
            @endif
        });
    </script>
@endsection