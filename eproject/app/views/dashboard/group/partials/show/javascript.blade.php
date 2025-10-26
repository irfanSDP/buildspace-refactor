<script type="text/javascript">
    $(document).ready(function() {
        var userTable = new Tabulator("#dashboard_group_user_list-table", {
            layout:"fitColumns",
            pagination:"remote",
            ajaxURL: "{{ route('dashboard.group.assigned.user', [$dashboardGroup->type]) }}",
            ajaxConfig: "GET",
            paginationSize: 50,
            placeholder: "{{ trans('general.noMatchingResults') }}",
            height: 360,
            tooltips:true,
            resizableColumns:false,
            ajaxFiltering: true,
            columns: [
                {title:"{{ trans('general.no') }}", cssClass:"text-center text-middle", width: 12, headerSort:false, formatter:"rownum"},
                {title:"{{ trans('users.name') }}", field: 'name', cssClass:"auto-width text-left", headerSort:false, headerFilter:"input", headerFilterPlaceholder: "{{ trans('general.filter') }}"},
                {title:"{{ trans('users.email') }}", field: 'email', cssClass:"text-left text-middle", width: 200, headerSort:false, headerFilter:"input", headerFilterPlaceholder: "{{ trans('general.filter') }}" },
                {title:"{{ trans('users.company') }}", field: 'company', cssClass:"text-left text-middle", width: 280, headerSort:false, headerFilter:"input", headerFilterPlaceholder: "{{ trans('general.filter') }}" },
                {title:"{{ trans('dashboard.actions') }}", cssClass:"text-center text-middle", width: 100, headerSort:false, formatter:openButton}
            ],
        });

        var assignUsersTable,
            assignUserModalColumns = [
                { formatter: "rowSelection", titleFormatter: "rowSelection", cssClass:"text-center text-middle", field: 'id', width: 12, 'align': 'center', headerSort:false},
                { title: "{{ trans('users.name') }}", cssClass:"auto-width text-left", field: 'name', headerSort:false, headerFilter: 'input', headerFilterPlaceholder: 'filter name' },
                { title: "{{ trans('users.email') }}", field: 'email', width: 200, headerSort:false, headerFilter: 'input', headerFilterPlaceholder: 'filter email' },
                { title: "{{ trans('users.company') }}", field: 'company', width: 280, headerSort:false, headerFilter: 'input', headerFilterPlaceholder: 'filter company' }
            ];

        $('#assignUsersModal').on('shown.bs.modal', function (e) {
            assignUsersTable = new Tabulator('#assignUsersTable', {
                height:320,
                columns: assignUserModalColumns,
                layout:"fitColumns",
                pagination:"remote",
                ajaxURL: "{{ route('dashboard.group.assignable') }}",
                ajaxConfig: "GET",
                paginationSize: 50,
                ajaxParams: { id : {{{$dashboardGroup->type}}} },
                movableColumns:true,
                placeholder:"No Data Available",
                columnHeaderSortMulti:false,
                ajaxFiltering: true,
                columnHeaderSortMulti:false,
            });
        });

        $('#assignUsersModal').on('hidden.bs.modal', function(e) {
            if(assignUsersTable){
                assignUsersTable.destroy();
                assignUsersTable = null;
            }
        });

        $('#assignUsersModal [data-action=submit]').on('click', function(){
            var selectedUsers = [];

            $.each(assignUsersTable.getSelectedData(), function(index, data){
                selectedUsers.push(data.id);
            });

            if(selectedUsers.length){
                app_progressBar.toggle();
                $.ajax({
                    url: '{{{ route("dashboard.group.assign.user") }}}',
                    method: 'POST',
                    data: {
                        _token: '{{{ csrf_token() }}}',
                        users: selectedUsers,
                        id: {{{$dashboardGroup->type}}},
                    },
                    success: function (data) {
                        if (data['success']) {
                            $('#assignUsersModal').modal('hide');

                            userTable.setData();
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
                $('#assignUsersModal').modal('hide');
            }
        });

        @if (in_array($dashboardGroup->type, [\PCK\Dashboard\DashboardGroup::TYPE_DEVELOPER, \PCK\Dashboard\DashboardGroup::TYPE_MAIN_CONTRACTOR]))
            var projectTable = new Tabulator("#dashboard_excluded_project_list-table", {
                layout:"fitColumns",
                pagination:"remote",
                ajaxURL: "{{ route('dashboard.group.excluded.project', [$dashboardGroup->type]) }}",
                ajaxConfig: "GET",
                paginationSize: 50,
                placeholder: "{{ trans('general.noMatchingResults') }}",
                height: 360,
                tooltips:true,
                resizableColumns:false,
                ajaxFiltering: true,
                columns: [
                    {title:"{{ trans('general.no') }}", cssClass:"text-center text-middle", width: 12, headerSort:false, formatter:"rownum"},
                    {title:"{{ trans('projects.title') }}", field: 'title', cssClass:"auto-width text-left", headerSort:false, headerFilter:"input", headerFilterPlaceholder: "{{ trans('general.filter') }}", formatter:"textarea"},
                    {title:"{{ trans('projects.reference') }}", field: 'reference', cssClass:"text-center text-middle", width: 210, headerSort:false, headerFilter:"input", headerFilterPlaceholder: "{{ trans('general.filter') }}" },
                    {title:"{{ trans('projects.country') }}", cssClass:"text-center text-middle", field: 'country', width: 160, headerSort:false, headerFilter: 'input', headerFilterPlaceholder: 'Filter' },
                    {title:"{{ trans('projects.status') }}", cssClass:"text-center text-middle", field: 'status', width: 120, headerSort:false, headerFilter:"select", headerFilterParams:{{ json_encode($projectStatuses) }}, headerFilterPlaceholder: 'Filter' },
                    {title:"{{ trans('dashboard.actions') }}", cssClass:"text-center text-middle", width: 100, headerSort:false, formatter:openButton}
                ],
            });

            var excludeProjectsTable,
                excludeProjectModalColumns = [
                    { formatter: "rowSelection", titleFormatter: "rowSelection", cssClass:"text-center text-middle", field: 'id', width: 12, 'align': 'center', headerSort:false},
                    { title: "{{ trans('projects.title') }}", cssClass:"auto-width text-left", field: 'title', headerSort:false, headerFilter: 'input', headerFilterPlaceholder: 'Filter', formatter:'textarea'},
                    { title: "{{ trans('projects.reference') }}", cssClass:"text-center text-middle", field: 'reference', width: 180, headerSort:false, headerFilter: 'input', headerFilterPlaceholder: 'Filter' },
                    { title: "{{ trans('projects.country') }}", cssClass:"text-center text-middle", field: 'country', width: 120, headerSort:false, headerFilter: 'input', headerFilterPlaceholder: 'Filter' },
                    { title: "{{ trans('projects.status') }}", cssClass:"text-center text-middle", field: 'status', width: 120, headerSort:false, headerFilter:"select", headerFilterParams:{{ json_encode($projectStatuses) }}, headerFilterPlaceholder: 'Filter' }
                ];

            $('#excludeProjectsModal').on('shown.bs.modal', function (e) {
                excludeProjectsTable = new Tabulator('#excludeProjectsTable', {
                    height:320,
                    columns: excludeProjectModalColumns,
                    layout:"fitColumns",
                    pagination:"remote",
                    ajaxFiltering: true,
                    ajaxURL: "{{ route('dashboard.group.excludable') }}",
                    ajaxConfig: "GET",
                    paginationSize: 50,
                    ajaxParams: { id : {{{$dashboardGroup->type}}} },
                    movableColumns:true,
                    placeholder:"No Data Available",
                    columnHeaderSortMulti:false,
                    ajaxFiltering: true,
                    columnHeaderSortMulti:false,
                });
            });

            $('#excludeProjectsModal').on('hidden.bs.modal', function(e) {
                if(excludeProjectsTable){
                    excludeProjectsTable.destroy();
                    excludeProjectsTable = null;
                }
            });

            $('#excludeProjectsModal [data-action=submit]').on('click', function(){
                var selectedProjects = [];

                $.each(excludeProjectsTable.getSelectedData(), function(index, data){
                    selectedProjects.push(data.id);
                });

                if(selectedProjects.length){
                    app_progressBar.toggle();
                    $.ajax({
                        url: '{{{ route("dashboard.group.exclude.project") }}}',
                        method: 'POST',
                        data: {
                            _token: '{{{ csrf_token() }}}',
                            projects: selectedProjects,
                            id: {{{$dashboardGroup->type}}},
                        },
                        success: function (data) {
                            if (data['success']) {
                                $('#excludeProjectsModal').modal('hide');

                                projectTable.setData();
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
                    $('#excludeProjectsModal').modal('hide');
                }
            });
        @endif
    });
    
    var openButton = function(cell, formatterParams, onRendered){
        var button = $('<button class="btn btn-xs btn-danger" title="{{ trans("forms.delete") }}"><i class="fa fa-trash"></i></button>');
        
        button.on("click", function(e){
            e.preventDefault();
            var r = confirm("'Are you sure you want to delete this record?'");
            if(!r){
                return false;
            }
            
            var data = cell.getRow().getData();
            var table = cell.getTable();
            
            app_progressBar.toggle();
            $.ajax({
                url: data.remove_url,
                method: 'DELETE',
                data: {
                    _token: '{{{ csrf_token() }}}'
                },
                success: function (data) {
                    if (data['success']) {
                        table.setData();
                    }

                    app_progressBar.maxOut();
                    app_progressBar.toggle();
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    app_progressBar.maxOut();
                    app_progressBar.toggle();
                }
            });
        });

        return button[ 0 ];
    };

    Tabulator.prototype.extendModule("format", "formatters", {
        textarea:function(cell, formatterParams){
            cell.getElement().style.whiteSpace = "pre-wrap";
            var obj = cell.getRow().getData();
            var str = '<a href="'+obj.show_url+'" class="plain">'
                + '<div class="well">'+this.sanitizeHTML(obj.title)+ '</div>'
                + '</a>';
            return this.emptyToSpace(str);
        }
    });
</script>