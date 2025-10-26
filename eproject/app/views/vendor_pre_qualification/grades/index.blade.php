@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('home.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{{ trans('vendorManagement.vendorManagement') }}}</li>
        <li>{{{ trans('vendorManagement.vendorPreQualificationGrades') }}}</li>
    </ol>
@endsection

@section('content')

<div id="content">
    <div class="row">
        <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
            <h1 class="page-title txt-color-blueDark">
                <i class="fa fa-users"></i> {{{ trans('vendorManagement.vendorPreQualificationGrades') }}}
            </h1>
        </div>
    </div>

    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <div class="jarviswidget ">
                <header>
                    <h2>{{ trans('vendorManagement.vendorPreQualificationGrades') }}</h2>
                </header>
                <div class="widget-body">
                    <div id="main-table"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal" id="grades-list-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">
                    {{ trans('vendorManagement.grades') }}
                </h4>
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
            </div>

            <div class="modal-body">
                <div id="grades-list-table"></div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">{{ trans('general.close') }}</button>
            </div>
        </div>
    </div>
</div>

<div class="modal" id="grades-preview-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="grades-preview-modal-title">
                </h4>
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
            </div>

            <div class="modal-body">
                <div id="grades-preview-table"></div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-dismiss="modal" data-action="select">{{ trans('forms.select') }}</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">{{ trans('general.close') }}</button>
            </div>
        </div>
    </div>
</div>
@include('templates.modals.confirmation', ['modalId' => 'delete-modal'])
@endsection

@section('js')
    <script>
        $(document).ready(function () {

            var mainTable = new Tabulator('#main-table', {
                height:450,
                placeholder: "{{ trans('general.noRecordsFound') }}",
                ajaxURL: "{{ route('vendorPreQualification.grades.list') }}",
                ajaxConfig: "GET",
                paginationSize: 100,
                pagination: "remote",
                ajaxFiltering:true,
                layout:"fitColumns",
                columns:[
                    {title:"{{ trans('general.no') }}", field:"counter", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
                    {title:"{{ trans('vendorManagement.vendorGroup') }}", field:"vendor_group", minWidth: 300, hozAlign:"left", headerSort:false, headerFilter: true},
                    {title:"{{ trans('vendorManagement.grade') }}", field:"grade", width: 200, hozAlign:"center", headerSort:false, headerFilter: true, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                        tag: 'a',
                        attributes: {href: 'javascript:void(0)', 'data-action': 'view-grade', title: '{{ trans("general.view") }}'},
                        rowAttributes: {'data-id': 'id'},
                        innerHtml: function(rowData){
                            return rowData['grade'] ?? "";
                        }
                    }},
                    {title:"{{ trans('general.actions') }}", width: 120, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                        innerHtml: [
                            {
                                tag: 'button',
                                attributes: {type: 'button', 'data-action': 'update', class:'btn btn-xs btn-primary', title: '{{ trans("forms.edit") }}'},
                                rowAttributes: {'data-id': 'id'},
                                innerHtml: {
                                    tag: 'i',
                                    attributes: {class: 'fa fa-edit'}
                                }
                            },{
                                innerHtml:function(){
                                    return "&nbsp;";
                                }
                            },{
                                opaque:function(cell)
                                {
                                    return cell.getData()['can_delete'];
                                },
                                tag: 'button',
                                attributes: {type: 'button', 'data-action': 'delete', class:'btn btn-xs btn-danger', title: '{{ trans("general.unlink") }}'},
                                rowAttributes: {'data-id': 'id'},
                                innerHtml: {
                                    tag: 'i',
                                    attributes: {class: 'fa fa-trash'}
                                }
                            }
                        ]
                    }}
                ],
            });
            var gradesTable = new Tabulator('#grades-list-table', {
                height:450,
                placeholder: "{{ trans('general.noRecordsFound') }}",
                ajaxURL: "{{ route('vendorPreQualification.grades.gradesList') }}",
                ajaxConfig: "GET",
                paginationSize: 100,
                pagination: "remote",
                ajaxFiltering:true,
                layout:"fitColumns",
                columns:[
                    {title:"{{ trans('general.no') }}", field:"counter", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
                    {title:"{{ trans('general.name') }}", field:"name", minWidth: 300, hozAlign:"left", headerSort:false, headerFilter: true, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                        tag: 'a',
                        attributes: {href: 'javascript:void(0)', 'data-action': 'view-grade'},
                        rowAttributes: {'data-id': 'id'},
                        innerHtml: function(rowData)
                        {
                            return rowData['name'] ?? "";
                        }
                    }},
                    {title:"{{ trans('general.actions') }}", width: 120, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                        tag: 'button',
                        attributes: {type: 'button', 'data-action': 'select', class:'btn btn-xs btn-default', title: '{{ trans("forms.select") }}'},
                        rowAttributes: {'data-id': 'id'},
                        innerHtml: function(rowData)
                        {
                            return "{{ trans('forms.select') }}";
                        }
                    }}
                ],
            });
            var gradesPreviewTable = new Tabulator('#grades-preview-table', {
                height:450,
                placeholder: "{{ trans('general.noRecordsFound') }}",
                ajaxConfig: "GET",
                paginationSize: 100,
                pagination: "remote",
                ajaxFiltering:true,
                layout:"fitColumns",
                columns:[
                    { title:"{{ trans('vendorManagement.level') }}", formatter:"rownum", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false },
                    { title:"{{ trans('vendorManagement.rating') }}", field:"description", hozAlign:'left', cssClass:"text-middle text-left", headerFilter:"input", headerFilterPlaceholder: "{{ trans('general.filter') }}", headerSort:false, editable:true },
                    { title:"{{ trans('vendorManagement.definition') }}", field:"definition", hozAlign:'center', cssClass:"text-middle text-center", headerFilter:"input", headerFilterPlaceholder: "{{ trans('general.filter') }}", headerSort:false, editable:true },
                    { title:"{{ trans('vendorManagement.upperLimit') }}", field:"score_upper_limit", width:170, hozAlign:'center', headerFilter:"input", headerFilterPlaceholder: "{{ trans('general.filter') }}", cssClass:"text-center text-middle" },
                ],
            });

            var updateRoute;
            var deleteRoute;
            var selectedGradeId;

            $('#main-table').on('click', '[data-action=update]', function(){
                var data = mainTable.getRow($(this).data('id')).getData();
                gradesTable.setData();
                updateRoute = data['route:update'];
                $('#grades-list-modal').modal('show');
            });
            function updateSelection(){
                app_progressBar.show();
                app_progressBar.maxOut();
                $.post(updateRoute, {
                    id: selectedGradeId,
                    _token: "{{ csrf_token() }}"
                }, function(data){
                    if(data['success'])
                    {
                        mainTable.setData();
                        $('#grades-list-modal').modal('hide');
                        SmallErrorBox.saved("{{ trans('general.success') }}", "{{ trans('forms.saved') }}");
                    }
                    else
                    {
                        SmallErrorBox.formValidationError("{{ trans('general.somethingWentWrong') }}", data['errorMessage']);
                    }

                    app_progressBar.hide();
                });
            }
            $('#grades-list-table').on('click', '[data-action=select]', function(){
                var data = gradesTable.getRow($(this).data('id')).getData();
                selectedGradeId = data['id'];
                updateSelection();
            });
            $('#grades-preview-modal').on('click', '[data-action=select]', function(){
                updateSelection();
            });
            $('#main-table').on('click', '[data-action=delete]', function(){
                var data = mainTable.getRow($(this).data('id')).getData();
                deleteRoute = data['route:delete'];
                $('#delete-modal').modal('show');
            });
            $('#delete-modal').on('click', '[data-action=proceed]', function(){
                app_progressBar.show();
                app_progressBar.maxOut();
                $.post(deleteRoute, {
                    _method: 'delete',
                    _token: "{{ csrf_token() }}"
                }, function(data){
                    if(data['success'])
                    {
                        mainTable.setData();
                        $('#grades-list-modal').modal('hide');
                        SmallErrorBox.saved("{{ trans('general.success') }}", "{{ trans('forms.saved') }}");
                    }
                    else
                    {
                        SmallErrorBox.formValidationError("{{ trans('general.somethingWentWrong') }}", data['errorMessage']);
                    }

                    app_progressBar.hide();
                });
            });
            $('#main-table').on('click', '[data-action=view-grade]', function(){
                var data = mainTable.getRow($(this).data('id')).getData();
                gradesPreviewTable.setData(data['route:preview']);
                $('#grades-preview-modal-title').html(data['grade']);
                $('#grades-preview-modal').modal('show');
            });
            $('#grades-list-table').on('click', '[data-action=view-grade]', function(){
                var data = gradesTable.getRow($(this).data('id')).getData();
                gradesPreviewTable.setData(data['route:preview']);
                selectedGradeId = $(this).data('id');
                $('#grades-preview-modal [data-action=select]').show();
                $('#grades-preview-modal-title').html(data['name']);
                $('#grades-preview-modal').modal('show');
            });
            $('#grades-preview-modal').on('hide.bs.modal', function(){
                $('#grades-preview-modal [data-action=select]').hide();
            });
            $('#grades-preview-modal [data-action=select]').hide();
            $('#grades-preview-modal').on('show.bs.modal', function(){
                $('#grades-list-modal').modal('hide');
            });
            $('#grades-preview-modal').on('hide.bs.modal', function(){
                if(selectedGradeId) $('#grades-list-modal').modal('show');
                selectedGradeId = null;
            });
        });
    </script>
@endsection