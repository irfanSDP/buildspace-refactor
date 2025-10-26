@extends('layout.main')

@section('breadcrumb')
<ol class="breadcrumb">
    <li>{{ link_to_route('home.index', trans('navigation/mainnav.home'), []) }}</li>
    <li>{{{ trans('general.consultantManagement') }}} {{{ trans('contractManagement.userManagement') }}}</li>
</ol>
@endsection

@section('content')
<div class="row">
    <div class="col-xs-9 col-sm-9 col-md-9 col-lg-9">
        <h1 class="page-title txt-color-blueDark">
            <i class="fa fa-users"></i> {{{ trans('contractManagement.userManagement') }}}
        </h1>
    </div>
    <div class="col-xs-3 col-sm-3 col-md-3 col-lg-3 mb-4">
        {{ Form::button('<i class="fa fa-plus"></i> '.trans('letterOfAward.addUsers'), ['class' => 'btn btn-primary btn-md pull-right header-btn', 'data-toggle' => 'modal', 'data-target' => '#user_management-modal']) }}
    </div>
</div>

<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="jarviswidget ">
            <header>
                <h2> {{{ trans('users.users') }}} </h2>
            </header>
            <div>
                <div class="widget-body no-padding">
                    <div id="assigned_users-table"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="user_management-modal" tabindex="-1" role="dialog" aria-labelledby="userManagementModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title"><i class="fa fa-users"></i> {{ trans('users.users') }}</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                    &times;
                </button>
            </div>

            <div class="modal-body no-padding">
                <div id="users_modal-table"></div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-dismiss="modal" id="user_management_select-btn"><i class="fa fa-check-square"></i> {{ trans('forms.select') }}</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">{{ trans('forms.close') }}</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script src="<?php echo asset('js/app/app.restfulDelete.js'); ?>"></script>
<script type="text/javascript">
$(document).ready(function () {
    var assignedUserTable = new Tabulator('#assigned_users-table', {
        height:520,
        placeholder: "{{ trans('general.noRecordsFound') }}",
        ajaxURL: "{{ route('consultant.management.consultant.user.management.assigned.list') }}",
        ajaxConfig: "GET",
        paginationSize: 100,
        pagination: "remote",
        ajaxFiltering:true,
        layout:"fitColumns",
        columns:[
            {title:"{{ trans('general.no') }}", field:"counter", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
            {title:"{{ trans('users.name') }}", field:"name",minWidth: 300, hozAlign:"left", headerSort:false, headerFilter:"input", headerFilterPlaceholder: "{{ trans('general.filter') }}"},
            {title:"{{ trans('users.email') }}", field:"email", width: 220, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false, headerFilter:"input", headerFilterPlaceholder: "{{ trans('general.filter') }}"},
            {title:"Company Admin", field:"is_admin", width: 140, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false, formatter:function(cell, formatterParams, onRendered){
                return (cell.getValue()) ? 'YES' : 'NO';
            }},
            {title:"{{ trans('general.actions') }}", width: 100, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                innerHtml: [{
                    innerHtml: function(rowData){
                        return '<a href="'+rowData['route:delete']+'" class="btn btn-xs btn-danger" data-id="'+rowData.id+'" data-method="delete" data-csrf_token="{{{ csrf_token() }}}"><i class="fa fa-trash"></i></a>';
                    }
                }]
            }}
        ]
    });

    var selectUserTable = new Tabulator('#users_modal-table', {
        height:420,
        placeholder: "{{ trans('general.noRecordsFound') }}",
        ajaxURL: "{{ route('consultant.management.consultant.user.management.unassigned.list') }}",
        ajaxConfig: "GET",
        paginationSize: 100,
        pagination: "remote",
        ajaxFiltering:true,
        layout:"fitColumns",
        columns:[
            {formatter:"rowSelection", titleFormatter:"rowSelection", width:42, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false, cellClick:function(e, cell){
                cell.getRow().toggleSelect();
            }},
            {title:"{{ trans('users.name') }}", field:"name",minWidth: 300, hozAlign:"left", headerSort:false, headerFilter:"input", headerFilterPlaceholder: "{{ trans('general.filter') }}"},
            {title:"{{ trans('users.email') }}", field:"email", width: 220, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false, headerFilter:"input", headerFilterPlaceholder: "{{ trans('general.filter') }}"},
            {title:"Company Admin", field:"is_admin", width: 140, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false, formatter:function(cell, formatterParams, onRendered){
                return (cell.getValue()) ? 'YES' : 'NO';
            }}
        ]
    });

    $('#user_management-modal').on('hidden.bs.modal', function (e) {
        selectUserTable.clearFilter(true);
    });

    $('#user_management_select-btn').on('click', function(e){
        var selectedUsers = [];
        $.each(selectUserTable.getSelectedData(), function(index, data){
            selectedUsers.push(data.id);
        });

        if(selectedUsers.length){
            app_progressBar.toggle();
            $.ajax({
                url: '{{{ route("consultant.management.consultant.user.management.assign") }}}',
                method: 'POST',
                data: {
                    _token: '{{{ csrf_token() }}}',
                    users: selectedUsers
                },
                success: function (data) {
                    if (data['success']) {
                        $('#user_management-modal').modal('hide');
                        selectUserTable.setData();
                        assignedUserTable.setData();
                    }

                    app_progressBar.maxOut();
                    app_progressBar.toggle();
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    app_progressBar.maxOut();
                    app_progressBar.toggle();
                }
            });
        }else{
            $('#user_management-modal').modal('hide');
        }
    });
});
</script>
@endsection

