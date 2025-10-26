@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{{ trans('vendorManagement.vendorManagement') }}}</li>
        <li>{{ trans('vendorManagement.users') }}</li>
    </ol>
@endsection

@section('content')
<div class="row">
    <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
        <h1 class="page-title txt-color-blueDark">
            <i class="fa fa-users"></i> {{{ trans('vendorManagement.users') }}}
        </h1>
    </div>
</div>

<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="jarviswidget ">
            <header>
                <h2>{{ trans('vendorManagement.users') }}</h2>
            </header>
            <div>
                <div class="widget-body">
                    <div id="users-table"></div>
                </div>
                <div class="widget-footer">
                    <button type="button" class="btn btn-default" data-action="update-selected-users">{{ trans('general.update') }}</button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal" id="update-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
     aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h4 class="modal-title">
                    <i class="fa fa-user-check"></i>
                    {{ trans('general.update') }}
                </h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                    &times;
                </button>
            </div>

            <div class="modal-body" style="margin: 0 20px;">
                <div class="row">
                    <p class="well">
                        <span id="update-modal-message"></span>
                    </p>
                    <hr/>
                    <div class="row">
                        <div id="permissions-list-table"></div>
                    </div>
                    <div class="row">
                        <div class="col col-lg-12">
                            <label>{{{ trans('tags.tags') }}}:</label>
                            <label class="fill-horizontal">
                                @include('templates.tag_selector', ['id' => 'tags-input', 'styles' => 'width: 100%'])
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-action="batch-remove-permissions"><i class="fa fa-minus"></i> {{ trans('general.remove') }}</button>
                <button type="button" class="btn btn-default" data-action="batch-add-permissions"><i class="fa fa-plus"></i> {{ trans('general.add') }}</button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('js')
    <script>
        $(document).ready(function () {
            var usersTable = new Tabulator('#users-table', {
                height: 450,
                placeholder: "{{ trans('general.noRecordsFound') }}",
                ajaxURL: "{{ route('vendorManagement.users.list') }}",
                ajaxConfig: "GET",
                paginationSize: 100,
                pagination: "remote",
                ajaxFiltering: true,
                layout: "fitColumns",
                cellClick: function (e, cell) {
                    var selectTriggerFields = ['counter', 'name', 'username', 'company'];
                    if (selectTriggerFields.includes(cell.getField())) {
                        cell.getRow().toggleSelect();
                    }
                },
                columns: (function () {
                    let columns = [
                        {formatter: "rowSelection", titleFormatter: "rowSelection", cssClass: "text-center text-middle", align: "center", headerSort: false, frozen: true},
                        {title: "{{ trans('general.no') }}", field: "counter", width: 60, hozAlign: 'center', cssClass: "text-center text-middle", headerSort: false, frozen: true},
                        {title: "{{ trans('users.name') }}", field: "name", minWidth: 300, hozAlign: "left", headerSort: false, headerFilter: true, frozen: true},
                        {title: "{{ trans('users.email') }}", field: "username", width: 150, hozAlign: 'center', cssClass: "text-center text-middle", headerSort: false, headerFilter: true},
                        {title: "{{ trans('users.company') }}", field: "company", width: 150, hozAlign: 'center', headerSort: false, cssClass: "text-center text-middle", headerFilter: true}
                    ];

                    let addedColumns = new Set();  // Track all added fields

                    @foreach($permissionsList as $permissionType => $permissionName)
                    if (!addedColumns.has("permission_type_{{ $permissionType }}")) {
                            <?php
                            // Fetch sub-types for the permission group
                            $subTypes = \PCK\VendorManagement\VendorManagementUserPermission::getGroupSubTypes($permissionType);
                            ?>

                        @if(!empty($subTypes))
                        // Add sub-columns only if not already added
                        var subColumns = [];
                        @foreach($subTypes as $subType)
                        if (!addedColumns.has("permission_type_{{ $subType }}")) {
                            subColumns.push({
                                title: "{{ \PCK\VendorManagement\VendorManagementUserPermission::getSubHeaders($permissionType)[$subType] }}",
                                field: "permission_type_{{ $subType }}",
                                width: 200,
                                hozAlign: "center",
                                cssClass: "text-center text-middle",
                                headerSort: false,
                                editable: false,
                                editor: "select",
                                headerFilter: true,
                                headerFilterParams: {
                                    values: {
                                        0: "{{ trans('general.all') }}",
                                        'true': "{{ trans('general.yes') }}",
                                        'false': "{{ trans('general.no') }}"
                                    }
                                },
                                formatter: app_tabulator_utilities.variableHtmlFormatter,
                                formatterParams: {
                                    innerHtml: function (rowData) {
                                        if (rowData.hasOwnProperty('id')) {
                                            var checked = rowData['permission_type_{{ $subType }}'] ? 'checked' : '';
                                            return '<input type="checkbox" data-action="toggle-permission" data-type-id="{{ $subType }}" data-id="' + rowData['id'] + '" ' + checked + '>';
                                        }
                                    }
                                }
                            });

                            addedColumns.add("permission_type_{{ $subType }}");  // Mark sub-column as added
                        }
                        @endforeach

                        // Push the group column and track its permission type
                        columns.push({
                            title: "{{ $permissionName }}",
                            hozAlign: "center",
                            cssClass: "text-center text-middle",
                            headerSort: false,
                            editable: false,
                            columns: subColumns
                        });

                        addedColumns.add("permission_type_{{ $permissionType }}");  // Mark group parent as added
                        @else
                        // Add standalone column if not already added
                        columns.push({
                            title: "{{ $permissionName }}",
                            field: "permission_type_{{ $permissionType }}",
                            width: 200,
                            hozAlign: "center",
                            cssClass: "text-center text-middle",
                            headerSort: false,
                            editable: false,
                            editor: "select",
                            headerFilter: true,
                            headerFilterParams: {
                                values: {
                                    0: "{{ trans('general.all') }}",
                                    'true': "{{ trans('general.yes') }}",
                                    'false': "{{ trans('general.no') }}"
                                }
                            },
                            formatter: app_tabulator_utilities.variableHtmlFormatter,
                            formatterParams: {
                                innerHtml: function (rowData) {
                                    if (rowData.hasOwnProperty('id')) {
                                        var checked = rowData['permission_type_{{ $permissionType }}'] ? 'checked' : '';
                                        return '<input type="checkbox" data-action="toggle-permission" data-type-id="{{ $permissionType }}" data-id="' + rowData['id'] + '" ' + checked + '>';
                                    }
                                }
                            }
                        });

                        addedColumns.add("permission_type_{{ $permissionType }}");  // Mark single column as added
                        @endif
                    }
                    @endforeach

                    // Add the final tags column
                    columns.push({
                        title: "{{ trans('tags.tags') }}",
                        field: "tags",
                        width: 280,
                        hozAlign: "left",
                        headerSort: false,
                        editable: false,
                        editor: "select",
                        headerFilter: true,
                        headerFilterParams: { values: ['one', 'two', 'three'], multiselect: true },
                        formatter: function (cell) {
                            var tagsArray = cell.getData()['tagsArray'];
                            var output = [];
                            for (var i in tagsArray) {
                                output.push('<span class="label label-success">' + tagsArray[i] + '</span>');
                            }
                            return output.join('&nbsp;', output);
                        }
                    });

                    return columns;
                })()
            });

            function updateHeaderFilterTagOptions() {
                $.get("{{ route('vendorManagement.users.tags.list') }}")
                .done(function(data){
                    var options = {};
                    data.forEach(function(item){
                        options[item['id']] = item['text'];
                    });
                    usersTable.updateColumnDefinition("tags", {headerFilterParams:{values:options, multiselect:true}});
                })
                .fail(function(data){
                    console.error('failed');
                });
            }

            updateHeaderFilterTagOptions();

            var permissionsListTable = new Tabulator('#permissions-list-table', {
                height:380,
                placeholder: "{{ trans('general.noRecordsFound') }}",
                ajaxConfig: "GET",
                paginationSize: 20,
                pagination: "remote",
                ajaxFiltering:true,
                layout:"fitColumns",
                cellClick:function(e, cell){
                    var selectTriggerFields = ['counter', 'name'];
                    if(selectTriggerFields.includes(cell.getField())){
                        cell.getRow().toggleSelect();
                    }
                },
                columns:[
                    {formatter:"rowSelection", titleFormatter:"rowSelection", width:40, cssClass:"text-center text-top", headerSort:false},
                    {title:"{{ trans('general.no') }}", field:"counter", width:60, hozAlign:'center', cssClass:"text-center text-top", headerSort:false},
                    {title:"{{ trans('users.name') }}", field:"name", minWidth:300, hozAlign:"left", headerSort:false, headerFilter:true}
                ],
            });

            $('#users-table').on('change', 'input[type=checkbox][data-action=toggle-permission]', function(){
                usersTable.modules.ajax.showLoader();
                var row = usersTable.getRow($(this).data('id'));
                var params = {
                    _token: _csrf_token,
                    type: $(this).data('type-id'),
                    grant: $(this).prop('checked'),
                };
                $.post(row.getData()['route:update'], params)
                .done(function(data){
                    if(data.success){
                        // smallbox
                    }
                    usersTable.modules.ajax.hideLoader();
                })
                .fail(function(data){
                    console.error('failed');
                    usersTable.modules.ajax.hideLoader();
                });
            });

            $('[data-action=update-selected-users]').on('click', function(){
                var selectedRows = usersTable.getSelectedRows();
                permissionsListTable.setData("{{ route('vendorManagement.users.permissionsList') }}");
                $('#update-modal').modal('show');
                eproject.translate(function(translation){
                    $('#update-modal-message').html(translation['tables.nRowsSelected']);
                }, "tables.nRowsSelected", {n: selectedRows.length});
            });

            $('#update-modal').on('show.bs.modal', function(){
                $('select#tags-input').val(null).trigger('change');
                $.get("{{ route('vendorManagement.users.tags.list') }}")
                .done(function(data){
                    $("select#tags-input").select2({
                        tags: true,
                        data: data
                    });
                })
                .fail(function(data){
                    console.error('failed');
                });
            });

            $('[data-action=batch-add-permissions],[data-action=batch-remove-permissions]').on('click', function(){
                var usersData = usersTable.getSelectedRows();

                var userIds = [];

                usersData.forEach(function(record, index){
                    userIds.push(record.getData()['id']);
                });

                var permissionsData = permissionsListTable.getSelectedRows();

                var permissionTypes = [];

                permissionsData.forEach(function(record, index){
                    permissionTypes.push(record.getData()['id']);
                });

                var params = {
                    _token: _csrf_token,
                    user_ids: userIds,
                    types: permissionTypes,
                    tags: $("select#tags-input").select2('data').map(a => a.text),
                    grant: $(this).data('action') == 'batch-add-permissions',
                };

                $.post("{{ route('vendorManagement.users.batchUpdatePermissions') }}", params)
                .done(function(data){
                    if(data.success){
                        usersTable.replaceData();
                        updateHeaderFilterTagOptions();
                        SmallErrorBox.saved("{{ trans('general.success') }}", "{{ trans('forms.saved') }}");
                        $('#update-modal').modal('hide');
                    }
                })
                .fail(function(data){
                    console.error('failed');
                });
            });
        });
    </script>
@endsection