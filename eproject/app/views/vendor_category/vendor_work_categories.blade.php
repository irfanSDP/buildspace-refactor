@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{ link_to_route('vendorGroups.external.index', trans('contractGroupCategories.externalVendorGroups'), array()) }}</li>
        <li>{{ link_to_route('vendorCategories.index', $contractGroupCategory->name, array($contractGroupCategory->id)) }}</li>
        <li>{{{ $vendorCategory->name }}}</li>
    </ol>
@endsection

@section('content')
<div class="row">
    <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
        <h1 class="page-title txt-color-blueDark">
            <i class="fa fa-users"></i> {{{ trans('contractGroupCategories.vendorWorkCategories') }}}
        </h1>
    </div>
    <div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">
        <div class="btn-group pull-right header-btn">
            @include('vendor_category.vendor_work_categories_action_menu')
        </div>
    </div>
</div>

<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="jarviswidget ">
            <header>
                <h2> {{{ $vendorCategory->name }}} </h2>
            </header>
            <div>
                <div class="widget-body">
                    <div id="main-table"></div>
                    {{ Form::open(array('id' => 'assign-form', 'route' => array('vendorCategories.vendorWorkCategories.include', $contractGroupCategory->id, $vendorCategory->id))) }}
                    <footer class="pull-right">
                        <a href="{{ route('vendorWorkCategories.index') }}" class="btn btn-default">{{ trans('vendorManagement.viewVendorWorkCategoryList') }}</a>
                        <button class="btn btn-primary" data-action="submit"><i class="fa fa-save"></i> {{ trans('forms.save') }}</button>
                    </footer>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="add-vendor-work-category-modal">
    <div class="modal-dialog modal-dmf">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">{{ trans('vendorManagement.addVendorWorkCategory') }}
                </h4>
                <button type="button" class="close" data-dismiss="modal">
                    &times;
                </button>
            </div>
            {{ Form::open(array('id' => 'add-vendor-work-category-form')) }}
            <div class="modal-body smart-form">
                <div class="row">
                    <section class="col col-xs-12 col-md-3 col-lg-3">
                        <label class="label">{{{ trans('contractGroupCategories.code') }}} <span class="required">*</span>:</label>
                        <label class="input" data-input="code">
                            {{ Form::text('code', '', array('required' => 'required')) }}
                        </label>
                        <em class="invalid" data-error="code"></em>
                    </section>
                    <section class="col col-xs-12 col-md-9 col-lg-9">
                        <label class="label">{{{ trans('contractGroupCategories.name') }}} <span class="required">*</span>:</label>
                        <label class="input" data-input="name">
                            {{ Form::text('name', '', array('required' => 'required')) }}
                        </label>
                        <em class="invalid" data-error="name"></em>
                    </section>
                </div>
            </div>
            <div class="modal-footer">
                {{ Form::submit(trans('forms.save'), array('class' => 'btn btn-primary')) }}
                <button type="button" class="btn btn-default" data-dismiss="modal">{{ trans('forms.close') }}</button>
            </div>
            {{ Form::close() }}
        </div>
    </div>
</div>
@include('templates.generic_table_modal', array('modalId' => 'vendor-categories-summary-modal', 'title' => trans('vendorManagement.vendorCategories'), 'tableId' => 'vendor-categories-summary-table', 'tablePadding' => true))
@endsection

@section('js')
    <script>
        $(document).ready(function () {
            var selectedIds = {{ json_encode($includedIds) }};

            var mainTable = new Tabulator('#main-table', {
                height:450,
                placeholder: "{{ trans('general.noRecordsFound') }}",
                ajaxURL: "{{ route('vendorCategories.vendorWorkCategories.ajax.list', [$contractGroupCategory->id, $vendorCategory->id]) }}",
                ajaxConfig: "GET",
                paginationSize: 100,
                pagination: "remote",
                ajaxFiltering:true,
                layout:"fitColumns",
                rowSelected: function(row){
                    var id = row.getData()['id'];
                    if(selectedIds.indexOf(id) === -1) selectedIds.push(id);
                },
                rowDeselected: function(row){
                    var id = row.getData()['id'];
                    if((idx = selectedIds.indexOf(id)) > -1) selectedIds.splice(idx, 1);
                },
                dataLoaded: function(data){
                    mainTable.selectRow(selectedIds);

                    selectedIds.forEach(function(id){
                        $('#main-table [data-action=include][data-id='+id+']').prop('checked', true);
                    });
                },
                cellClick:function(e, cell){
                    var selectTriggerFields = ['counter', 'name', 'code', 'included'];
                    if(selectTriggerFields.includes(cell.getField())){
                        cell.getRow().toggleSelect();
                        $('#main-table [data-action=include][data-id='+cell.getData()['id']+']').prop('checked', cell.getRow().isSelected());
                    }
                },
                columns:[
                    {title:"{{ trans('general.no') }}", field:"counter", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
                    {title:"{{ trans('contractGroupCategories.code') }}", field:"code", width: 150, cssClass:"text-center text-middle", headerSort:true, headerFilter:"input", headerFilterPlaceholder: "{{ trans('general.filter') }}"},
                    {title:"{{ trans('contractGroupCategories.name') }}", field:"name", minWidth: 300, hozAlign:"left", headerSort:true, headerFilter:"input", headerFilterPlaceholder: "{{ trans('general.filter') }}"},
                    {title:"{{ trans('vendorManagement.vendorCategories') }}", field:"vendor_categories", minWidth: 300, headerSort:false, headerFilter:"input", headerFilterPlaceholder: "{{ trans('general.filter') }}", cssClass:"text-left text-middle", headerSort:false, formatter: function(cell){
                        var rowData = cell.getData();
                        var output = '<div class="well text-truncate" data-action="show-summary" data-id="'+rowData['id']+'">';
                        for(var i in rowData['vendor_categories']){
                            if(i < 3){
                                output += '<p>'+rowData['vendor_categories'][i]+'</p>';
                            }
                            else{
                                output += '<p>...</p>';
                                break;
                            }
                        }
                        output += '</div>';
                        return output;
                    }},
                    {title:"{{ trans('general.included') }}", field:"included", width: 100, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, editable: false, editor:"select", headerFilter:true, headerFilterParams:{values:{0:"{{ trans('general.all') }}", 'true':"{{ trans('general.yes') }}", 'false':"{{ trans('general.no') }}"}}, formatter:function(cell){
                        return '<input type="checkbox" data-action="include" data-id="'+cell.getData()['id']+'">';
                    }}
                ],
            });

            $('#assign-form').one('submit', function(e) {
                e.preventDefault();
                var idInput;
                selectedIds.forEach(function(id, index){
                    idInput = document.createElement('input');
                    idInput.setAttribute('type', 'hidden');
                    idInput.setAttribute('name', 'id[]');
                    idInput.setAttribute('value', id);
                    $('#assign-form').append(idInput);
                });
                $('#assign-form').submit();
            });

            $('[data-action=add-vendor-work-category]').on('click', function(){
                $('#add-vendor-work-category-modal').modal('show');
            });

            $("#add-vendor-work-category-form").on('submit', function(e){
                app_progressBar.toggle();
                var dataStr = $(this).serialize();
                $.ajax({
                    type: "POST",
                    url: "{{ route('vendorCategories.vendorWorkCategories.store', array($contractGroupCategory->id, $vendorCategory->id)) }}",
                    data: dataStr,
                    success: function (resp) {
                        app_progressBar.maxOut();
                        $("#add-vendor-work-category-form [data-input]").removeClass('state-error');
                        $("#add-vendor-work-category-form [data-error]").html("");

                        if(!resp.success){
                            $.each( resp.errors, function( key, data ) {
                                $("#add-vendor-work-category-form [data-input="+data.key+"]").addClass('state-error');
                                $("#add-vendor-work-category-form [data-error="+data.key+"]").html(data.msg);
                            });
                        }else{
                            $.smallBox({
                                title : "{{ trans('general.success') }}",
                                content : "<i class='fa fa-check'></i> <i>{{ trans('forms.saved') }}</i>",
                                color : "#179c8e",
                                sound: true,
                                iconSmall : "fa fa-save",
                                timeout : 1000
                            });
                            resetForm();
                            selectedIds.push(resp.id);
                            mainTable.setData();
                            $('#add-vendor-work-category-modal').modal('hide');
                        }
                        app_progressBar.toggle();
                    }
                });

                e.preventDefault();
            });
            function resetForm(){
                $("#form-header").html("{{{ trans('vendorManagement.addVendorWorkCategory') }}}");
                $("#add-vendor-work-category-form [data-input]").removeClass('state-error');
                $("#add-vendor-work-category-form [data-error]").html("");
                $("#add-vendor-work-category-form [name=name]").val("");
                $("#add-vendor-work-category-form [name=code]").val("");
                $("#add-vendor-work-category-form [name=code]").focus();
            }

            var vendorCategoriesSummaryTable = new Tabulator('#vendor-categories-summary-table', {
                height:450,
                placeholder: "{{ trans('general.noRecordsFound') }}",
                ajaxConfig: "GET",
                paginationSize: 100,
                pagination: "remote",
                ajaxFiltering:true,
                layout:"fitColumns",
                columns:[
                    {title:"{{ trans('general.no') }}", field:"counter", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
                    {title:"{{ trans('contractGroupCategories.code') }}", field:"code", width: 150, cssClass:"text-center text-middle", headerSort:true, headerFilter:"input", headerFilterPlaceholder: "{{ trans('general.filter') }}"},
                    {title:"{{ trans('contractGroupCategories.name') }}", field:"name", minWidth: 300, hozAlign:"left", headerSort:true, headerFilter:"input", headerFilterPlaceholder: "{{ trans('general.filter') }}"},
                ]
            });

            $('#main-table').on('click', '[data-action=show-summary]', function(){
                var row = mainTable.getRow($(this).data('id'));
                vendorCategoriesSummaryTable.setData(row.getData()['route:vendor_category_summary']);

                $('#vendor-categories-summary-modal').modal('show');
            });
        });
    </script>
@endsection