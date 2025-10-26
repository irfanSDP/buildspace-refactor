@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{{ trans('modulePermissions.permissions') }}}</li>
        <li>{{{ trans('modulePermissions.delegate') }}}</li>
    </ol>
@endsection

@section('content')
    <div class="row">
        <div class="col-xs-12 col-sm-10 col-md-10 col-lg-10">
            <h1 class="page-title txt-color-blueDark">
                <i class="fa fa-key green" data-type="tooltip" data-toggle="tooltip" data-placement="right" title="{{{ trans('modulePermissions.delegateHelp') }}}"></i> {{{ trans('modulePermissions.maintenanceModules') }}}
            </h1>
        </div>

        <div class="col-xs-12 col-sm-2 col-md-2 col-lg-2">
            <a class="btn btn-warning pull-right" data-toggle="modal" data-target="#assignUsersModal" data-action="assignUsers"">
                <i class="fa fa-check-square"></i>
                {{{ trans('modulePermissions.assignUsers') }}}
            </a>
        </div>
    </div>
    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <div class="jarviswidget ">
                <header>
                    <h2>{{ trans('modulePermissions.maintenanceModules') }}</h2>
                </header>
                <div>
                    <div class="widget-body no-padding">
                        <div class="form-inline padded-bottom padded-left padded-less-top">
                            <div style="padding-top:4px;padding-bottom:30px;">
                                <label class="control-label col-sm-1 text-right"><strong>{{ trans('modulePermissions.module') }}</strong></label>
                                <div class="col-sm-11">
                                    <select class="select2" style="width:100%" id="modulesFilter" data-action="filter">
                                        @foreach($modules as $moduleId => $moduleName)
                                            <option value="{{{ $moduleId }}}">{{{ $moduleName }}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div id="modulePermissionTable" data-url="{{ route('module.permissions.assigned') }}"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div id="workArea">
    @include('form_partials.assign_users_modal_2', array(
        'modalId' => 'assignUsersModal',
        'tableId' => 'assignUsersTable',
    ))
    @include('modulePermissions.subsidiaries.assignSubsidiariesModal')
@endsection

@section('js')
    <script src="{{ asset('js/app/app.functions.js') }}"></script>
    <script src="{{ asset('js/vue/dist/vue.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            var modulePermissionTableURL = $('#modulePermissionTable').data('url');
            var assignUsersTable = null;
            var assignSubsidiariesTable = null;

            var selectedUsers = [];

            var isEditorFormatter = function(cell, formatterParams, onRendered) {
                var chkIsEditor = document.createElement('input');
                chkIsEditor.type = 'checkbox';
                chkIsEditor.id = 'chkIsEditor_' + cell.getRow().getData().module_identifier + '_' + cell.getRow().getData().module_permission_id;
                chkIsEditor.name = 'chkIsEditor_' + cell.getRow().getData().module_identifier + '_' + cell.getRow().getData().module_permission_id;
                chkIsEditor.checked = cell.getRow().getData().isEditor;

                chkIsEditor.addEventListener('change', function(e) {
                    app_progressBar.toggle();
                    
                    $.ajax({
                        url: cell.getRow().getData().toggleEditorUrl,
                        method: 'POST',
                        data: {
                            _method: 'POST',
                            _token: '{{{ csrf_token() }}}'
                        },
                        success: function (data) {
                            if (data['success']) {
                                modulePermissionTable.setData();
                                app_progressBar.maxOut();
                                app_progressBar.toggle();
                            }
                        },
                        error: function (jqXHR, textStatus, errorThrown) {
                            // error
                        }
                    });
                });

                return chkIsEditor;
            };

            var actionsFormatter = function(cell, formatterParams, onRendered) {
                var hasSubsidiaryList = {{ json_encode(PCK\ModulePermission\ModulePermission::hasSubsidiaryList()) }};
                var moduleIdentifier = cell.getRow().getData().module_identifier;

                var container = document.createElement('div');
                container.className = 'text-middle text-center text-nowrap';

                var btnDeleteUser = document.createElement('button');
                btnDeleteUser.className = 'btn btn-xs btn-danger';
                btnDeleteUser.innerHTML = '<i class="fa fa-trash"></i>';
                btnDeleteUser.style.marginLeft = '5px';
                btnDeleteUser.setAttribute('title', "{{ trans('modulePermissions.unassignUser') }}");

                btnDeleteUser.addEventListener('click', function(e) {
                    app_progressBar.toggle();

                    $.ajax({
                        url: cell.getRow().getData().revokeUrl,
                        method: 'POST',
                        data: {
                            _method: 'DELETE',
                            _token: '{{{ csrf_token() }}}'
                        },
                        success: function (data) {
                            if (data['success']) {
                                modulePermissionTable.setData();
                            } else {
                                $.smallBox({
                                    title : "{{ trans('general.warning') }}",
                                    content : "<i class='fa fa-check'></i> <i>" + data['errors'] + "</i>",
                                    color : "#C46A69",
                                    sound: false,
                                    iconSmall : "fa fa-exclamation-triangle",
                                    timeout : 5000
                                });
                            }

                            app_progressBar.maxOut();
                            app_progressBar.toggle();
                        },
                        error: function (jqXHR, textStatus, errorThrown) {
                            // error
                        }
                    });
                });
                
                container.appendChild(btnDeleteUser);

                if (hasSubsidiaryList.includes(moduleIdentifier)) {
                    var btnAssignSubsidiary = document.createElement('button');
                    btnAssignSubsidiary.className = 'btn btn-xs btn-default';
                    btnAssignSubsidiary.innerHTML = '<i class="fa fa-cubes"></i>';
                    btnAssignSubsidiary.style.marginLeft = '5px'

                    btnAssignSubsidiary.dataset.id = cell.getRow().getData().id;
                    btnAssignSubsidiary.dataset.name = cell.getRow().getData().name
                    btnAssignSubsidiary.dataset.mid = moduleIdentifier;

                    btnAssignSubsidiary.dataset.assign = "{{ route('module.permissions.subsidiary.assignToUser') }}";
                    btnAssignSubsidiary.dataset.getList = "{{ route('module.permissions.subsidiary.getList') }}";
                    btnAssignSubsidiary.dataset.getAssigned = "{{ route('module.permissions.subsidiary.getAssigned') }}";

                    btnAssignSubsidiary.setAttribute('title', "{{ trans('modulePermissions.assignSubsidiaries') }}");

                    btnAssignSubsidiary.addEventListener('click', function(e) {
                        workAreaVue.userId = $(this).data('id');
                        workAreaVue.userName = $(this).data('name');
                        workAreaVue.mid = $(this).data('mid');
                        workAreaVue.assign = $(this).data('assign');
                        workAreaVue.getList = $(this).data('getList');
                        workAreaVue.getAssigned = $(this).data('getAssigned');
                        $('#assignSubsidiariesModal').modal('show');
                    });

                    container.appendChild(btnAssignSubsidiary);
                }

                return container;
            };

            var columns = [
                { title: "id", field: 'id', visible:false },
                { title: "{{ trans('general.no') }}", field: 'indexNo', width: 60, 'align': 'center', cssClass:"text-center", headerSort:false },
                { title: "{{ trans('users.name') }}", field: 'name', headerSort:false, headerFilter: 'input', headerFilterPlaceholder: 'filter name' },
                { title: "{{ trans('users.email') }}", field: 'email', width: 300, headerSort:false, headerFilter: 'input', headerFilterPlaceholder: 'filter email' },
                { title: "{{ trans('users.company') }}", field: 'company', width: 250, headerSort:false, headerFilter: 'input', headerFilterPlaceholder: 'filter company' },
                { title: "{{ trans('forms.editor') }}", field: 'isEditor', width: 60, 'align': 'center', cssClass:"text-center", headerSort:false, formatter: isEditorFormatter },
                { title: "{{ trans('forms.actions') }}", width: 75, 'align': 'center', cssClass:"text-center", headerSort:false, formatter: actionsFormatter },
            ];

            var modulePermissionTable = new Tabulator('#modulePermissionTable', {
                height:400,
                columns: columns,
                layout:"fitColumns",
                ajaxURL: modulePermissionTableURL,
                ajaxConfig: "GET",
                ajaxParams: { moduleId : getCurrentlySelectedModule() },
                movableColumns:true,
                placeholder:"No Data Available",
                columnHeaderSortMulti:false,
                ajaxProgressiveLoad: "scroll",
                ajaxProgressiveLoadScrollMargin: 50,
                ajaxFiltering: true,
            });

            $('#modulesFilter').on('change', function() {
                var selectedValue = this.options[this.selectedIndex].value;

                modulePermissionTable.setData(modulePermissionTableURL, { moduleId: selectedValue });
            });

            var selectUserColumnFormatter = function(cell, formatterParams, onRendered) {
                var rowId = cell.getRow().getData().id; // Get the row ID

                var chkSelectUser = document.createElement('input');
                chkSelectUser.type = 'checkbox';
                chkSelectUser.id = 'chkSelectUser_' + rowId;
                chkSelectUser.name = 'chkSelectUser_' + rowId;

                chkSelectUser.addEventListener('change', function(e) {
                    if(this.checked) {
                        selectedUsers.push(rowId);
                        checkedItems[rowId] = true; // Update the tracking object
                    } else {
                        selectedUsers = arrayRemove(selectedUsers, rowId);
                        checkedItems[rowId] = false; // Update the tracking object
                    }
                });

                return chkSelectUser;
            }

            var assignUserModalColumns = [
                { title: "id", field: 'id', visible:false },
                { title: "{{ trans('general.no') }}", field: 'indexNo', width: 60, 'align': 'center', headerSort:false },
                { title: "{{ trans('users.name') }}", field: 'name', headerSort:false, headerFilter: 'input', headerFilterPlaceholder: 'filter name' },
                { title: "{{ trans('users.email') }}", field: 'email', width: 300, headerSort:false, headerFilter: 'input', headerFilterPlaceholder: 'filter email' },
                { title: "{{ trans('users.company') }}", field: 'company', width: 250, headerSort:false, headerFilter: 'input', headerFilterPlaceholder: 'filter company' },
                { title: "{{ trans('forms.assign') }}", field: 'isEditor', width: 60, 'align': 'center', headerSort:false, formatter: selectUserColumnFormatter },
            ];

            var checkedItems = {};  // Object to keep track of checked items

            $('#assignUsersModal').on('shown.bs.modal', function (e) {
                selectedUsers = [];

                assignUsersTable = new Tabulator('#assignUsersTable', {
                    height:400,
                    columns: assignUserModalColumns,
                    layout:"fitColumns",
                    ajaxURL: "{{ route('module.permissions.assignable') }}",
                    ajaxConfig: "GET",
                    ajaxParams: { moduleId : getCurrentlySelectedModule() },
                    movableColumns:true,
                    placeholder:"No Data Available",
                    columnHeaderSortMulti:false,
                    ajaxProgressiveLoad: "scroll",
                    ajaxProgressiveLoadScrollMargin: 50,
                    ajaxFiltering: true,
                    rowFormatter: function(row) {
                        // Get the data for the current row
                        var data = row.getData();

                        // Check if this row's ID is in checkedItems
                        if(checkedItems[data.id]) {
                            // If so, find the checkbox and set it to checked
                            var checkbox = document.getElementById('chkSelectUser_' + data.id);
                            if(checkbox) {
                                checkbox.checked = true;
                            }
                        }
                    },
                });
            });

            $('#assignUsersModal').on('hidden.bs.modal', function(e) {
                assignUsersTable.destroy();
                assignUsersTable = null;
                checkedItems = {};
            });

            $('#assignUsersModal [data-action=submit]').on('click', function(){
                app_progressBar.toggle();

                $.ajax({
                    url: '{{{ route('module.permissions.assign') }}}',
                    method: 'POST',
                    data: {
                        _token: '{{{ csrf_token() }}}',
                        users: selectedUsers,
                        module_id: getCurrentlySelectedModule(),
                    },
                    success: function (data) {
                        if (data['success']) {
                            selectedUsers = [];
                            $('#assignUsersModal').modal('hide');
                            modulePermissionTable.setData();
                            app_progressBar.maxOut();
                            app_progressBar.toggle();
                        }
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        // error
                    }
                });
            });

            var workAreaVue = new Vue({
                el: '#workArea',
                data: {
                    userId: '',
                    userName: '',
                    selectedSubsidiaryIds: [],
                    mid: 0,
                    assign: '',
                    getList: '',
                    getAssigned: '',
                },

                methods: {
                    updateSelection: function(){
                        assignSubsidiariesTable.deselectRow();

                        for(var key in this.selectedSubsidiaryIds)
                        {
                            assignSubsidiariesTable.selectRow(this.selectedSubsidiaryIds[key]);
                        }
                    }
                }
            });

            $('#assignSubsidiariesModal').on('shown.bs.modal', function (e) {
                assignSubsidiariesTable = new Tabulator("#subsidiaries-table", {
                    height:"300px",
                    layout:"fitColumns",
                    selectable:true,
                    selectablePersistence:true,
                    ajaxURL: workAreaVue.getList,
                    columns: [
                        {title:"{{ trans('general.no') }}", field: 'no', align:"center", cssClass:"text-center", width: '5px', frozen: true, headerSort:false},
                        {title:"{{ trans('subsidiaries.subsidiary') }}", field: 'name', align:"center", cssClass:"text-left", headerFilter: "input", headerSort:false},
                    ],
                    dataLoaded:function(data){
                        this.redraw(true);
                        selectAssignedSubsidiaryRows();
                    },
                });
            });

            $('#assignSubsidiariesModal').on('hidden.bs.modal', function (e) {
                assignSubsidiariesTable.destroy();
                assignSubsidiariesTable = null;
            });

            $('[data-action=assignSubsidiaries]').on('click', function(){
                var selectedData = assignSubsidiariesTable.getSelectedData();

                workAreaVue.selectedSubsidiaryIds = [];

                for(var key in selectedData)
                {
                    workAreaVue.selectedSubsidiaryIds.push(selectedData[key]['id']);
                }

                assignSubsidiariesToUser();
            })

            function assignSubsidiariesToUser() {
                app_progressBar.show();
                $.ajax({
                    url: workAreaVue.assign,
                    method: 'POST',
                    data: {
                        _token: '{{{ csrf_token() }}}',
                        userId: workAreaVue.userId,
                        subsidiaryIds: workAreaVue.selectedSubsidiaryIds,
                        mid: workAreaVue.mid
                    },
                    success: function (data) {
                        workAreaVue.selectedSubsidiaryIds = data;
                        workAreaVue.updateSelection();
                        app_progressBar.maxOut(0, function(){
                            app_progressBar.hide();
                        });
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        // error
                    }
                });
            }

            function selectAssignedSubsidiaryRows() {
                $.ajax({
                    url: workAreaVue.getAssigned,
                    method: 'GET',
                    data: {
                        _token: '{{{ csrf_token() }}}',
                        userId: workAreaVue.userId,
                        mid: workAreaVue.mid
                    },
                    success: function (data) {
                        workAreaVue.selectedSubsidiaryIds = data;
                        workAreaVue.updateSelection();
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        // error
                    }
                });
            }

            function arrayRemove(array, value) {
                return array.filter(function(el) {
                    return el != value;
                });
            }

            function getCurrentlySelectedModule()
            {
                var modulesFilter = document.getElementById('modulesFilter');

                return modulesFilter.options[modulesFilter.selectedIndex].value;
            }
        });
    </script>
@endsection