@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{ link_to_route('projects.show', str_limit($project->title, 50), array($project->id)) }}</li>
        <li>{{ trans('contractManagement.contractManagement') }}</li>
        <li>{{{ trans('contractManagement.userManagement') }}}</li>
    </ol>
@endsection

@section('content')
    <div class="row">
        <div class="col-xs-12 col-sm-10 col-md-10 col-lg-10">
            <h1 class="page-title txt-color-blueDark">
                <i class="fa fa-users" data-type="tooltip" data-toggle="tooltip" data-placement="right" title="{{{ trans('modulePermissions.delegateHelp') }}}"></i> {{{ trans('contractManagement.userManagement') }}}
            </h1>
        </div>
        <div class="col-xs-12 col-sm-2 col-md-2 col-lg-2">
            <div class="btn-group pull-right header-btn">
                @include('contractManagement.userPermissions.partials.index_actions_menu', array('classes' => 'pull-right'))
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <div class="jarviswidget ">
                <header>
                    <h2>{{ trans('contractManagement.userManagement') }}</h2>
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
                        <div id="modulePermissionTable"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @include('form_partials.assign_users_modal_2', array(
        'modalId' => 'assignUsersModal',
        'tableId' => 'assignUsersTable',
    ))
@endsection
@section('js')
    <script src="{{ asset('js/app/app.functions.js') }}"></script>
    <script>
        $(document).ready(function() {
            var modulePermissionTableURL = "{{ route('contractManagement.permissions.assigned', [$project->id]) }}";
            var assignUsersTable = null;
            var selectedUsers = [];
            var assignVerifierRoutesByModule = {{ $assignVerifierRoutesByModule }};

            var isVerifierFormatter = function(cell, formatterParams, onRendered) {
                var chkIsVerifier = document.createElement('input');
                chkIsVerifier.type = 'checkbox';
                chkIsVerifier.id = 'chkIsVerifier_' + cell.getRow().getData().module_identifier + '_' + cell.getRow().getData().module_permission_id;
                chkIsVerifier.name = 'chkIsVerifier_' + cell.getRow().getData().module_identifier + '_' + cell.getRow().getData().module_permission_id;
                chkIsVerifier.checked = cell.getRow().getData().isVerifier;

                chkIsVerifier.addEventListener('change', function(e) {
                    app_progressBar.toggle();

                    $.ajax({
                        url: cell.getRow().getData().toggleVerifierUrl,
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

                return chkIsVerifier;
            };

            var actionsFormatter = function(cell, formatterParams, onRendered) {
                var btnDeleteUser = document.createElement('button');
                btnDeleteUser.className = 'btn btn-xs btn-danger';
                btnDeleteUser.innerHTML = '<i class="fa fa-trash"></i>';
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
                        success: function (response) {
                            if (response['success']) {
                                modulePermissionTable.setData();
                            } else {
                                $.smallBox({
                                    title : response.title,
                                    content : "<i class='fa fa-check'></i> <i>" + response.message + "</i>",
                                    color : "#C46A69",
                                    sound: true,
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

                return btnDeleteUser;
            };

            var columns = [
                { title: "id", field: 'id', visible:false },
                { title: "{{ trans('general.no') }}", field: 'indexNo', width: 60, 'align': 'center', cssClass:"text-center", headerSort:false },
                { title: "{{ trans('users.name') }}", field: 'name', headerSort:false, headerFilter: 'input', headerFilterPlaceholder: 'filter name' },
                { title: "{{ trans('users.email') }}", field: 'email', width: 300, headerSort:false, headerFilter: 'input', headerFilterPlaceholder: 'filter email' },
                { title: "{{ trans('users.company') }}", field: 'company', width: 250, headerSort:false, headerFilter: 'input', headerFilterPlaceholder: 'filter company' },
                { title: "{{ trans('contractManagement.isVerifier') }}", field: 'isVerifier', width: 80, 'align': 'center', cssClass:"text-center", headerSort:false, formatter: isVerifierFormatter },
                { title: "{{ trans('forms.actions') }}", width: 60, 'align': 'center', cssClass:"text-center", headerSort:false, formatter: actionsFormatter },
            ];

            var modulePermissionTable = new Tabulator('#modulePermissionTable', {
                fillHeight:true,
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
                columnHeaderSortMulti:false,
            });

            $('#modulesFilter').on('change', function() {
                var selectedValue = this.options[this.selectedIndex].value;

                modulePermissionTable.setData(modulePermissionTableURL, { moduleId: selectedValue });
            });

            var selectUserColumnFormatter = function(cell, formatterParams, onRendered) {
                var chkSelectUser = document.createElement('input');
                chkSelectUser.type = 'checkbox';
                chkSelectUser.id = 'chkSelectUser_' + cell.getRow().getData().id;
                chkSelectUser.name = 'chkSelectUser_' + cell.getRow().getData().id;

                chkSelectUser.addEventListener('change', function(e) {
                    if(this.checked) {
                        selectedUsers.push(cell.getRow().getData().id);
                    } else {
                        selectedUsers = arrayRemove(selectedUsers, cell.getRow().getData().id);
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

            $('#assignUsersModal').on('shown.bs.modal', function (e) {
                selectedUsers = [];

                assignUsersTable = new Tabulator('#assignUsersTable', {
                    height:400,
                    columns: assignUserModalColumns,
                    layout:"fitColumns",
                    ajaxURL: "{{ route('contractManagement.permissions.assignable', array($project->id)) }}",
                    ajaxConfig: "GET",
                    ajaxParams: { moduleId : getCurrentlySelectedModule() },
                    movableColumns:true,
                    placeholder:"No Data Available",
                    columnHeaderSortMulti:false,
                    ajaxProgressiveLoad: "scroll",
                    ajaxProgressiveLoadScrollMargin: 50,
                    ajaxFiltering: true,
                    columnHeaderSortMulti:false,
                });
            });

            $('#assignUsersModal').on('hidden.bs.modal', function(e) {
                assignUsersTable.destroy();
                assignUsersTable = null;
            });

            $('#assignUsersModal [data-action=submit]').on('click', function(){
                app_progressBar.toggle();

                $.ajax({
                    url: "{{ route('contractManagement.permissions.assign', [$project->id]) }}",
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

            $('#btnAssignVerifiers').on('click', function(e) {
                e.preventDefault();

                window.location.href = assignVerifierRoutesByModule[getCurrentlySelectedModule()];
            });

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