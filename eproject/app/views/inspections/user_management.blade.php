@extends('layout.main')

@section('breadcrumb')
	<ol class="breadcrumb">
		<li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), []) }}</li>
		<li>{{ link_to_route('projects.show', str_limit($project->title, 50), [$project->id]) }}</li>
        <li>{{ trans('letterOfAward.userPermissions') }}</li>
	</ol>
	@include('projects.partials.project_status')
@endsection

@section('content')

    <div class="row">
        <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
            <h1 class="page-title txt-color-blueDark">
                <i class="fa fa-users"></i> {{{ trans('inspection.userManagement') }}}
            </h1>
        </div>
    </div>

    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <div class="jarviswidget">
                <header>
                    <h2> {{{ trans('inspection.inspection') }}} </h2>
                </header>
                <div class="widget-body">
                    <ol id="user-management-breadcrumbs" class="breadcrumb bg-transparent border border-info">
                    </ol>
                    <div id="group-table"></div>
                    <div class="row">
                        <div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
                            <div id="not-list-categories-table" hidden=""></div>
                        </div>
                        <div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
                            <div id="list-categories-table" hidden=""></div>
                        </div>
                    </div>
                    <div id="roles-table" hidden=""></div>
                    <div id="users-table" hidden=""></div>
                    <div id="submitters-table" hidden=""></div>
                    <div class="row">
                        <div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
                            <div id="not-verifiers-table" hidden=""></div>
                        </div>
                        <div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
                            <div id="verifiers-table" hidden=""></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('js')
    <!-- <script src="{{ asset('js/app/app.restfulDelete.js') }}"></script> -->
    <script type="text/javascript">
        var groupId;
        var roleId;
        var breadcrumbs = new DynamicBreadcrumbs('#user-management-breadcrumbs');
        breadcrumbs.addItem("{{ trans('inspection.groups') }}", 'groups');

        var groupTable = new Tabulator('#group-table', {
            fillHeight:true,
            placeholder: "{{ trans('general.noRecordsFound') }}",
            ajaxURL:"{{ route('inspection.groups', array($project->id)) }}",
            layout:"fitColumns",
            columns:[
                {title:"{{ trans('general.no') }}", formatter:"rownum", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
                {title:"{{ trans('inspection.group') }}", field:"name", minWidth: 300, hozAlign:"left", editor: "input", headerSort:false},
                {title:"{{ trans('inspection.roles') }}", width: 100, /*hozAlign:"center", */cssClass:"text-center text-middle", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                    innerHtml: function(rowData){
                        if(rowData.hasOwnProperty('id'))
                        {
                            return "<button class='btn btn-xs btn-warning' data-action='initRoleTable' data-id='"+rowData['id']+"' data-name='"+rowData['name']+"'>{{ trans('inspection.roles') }}</button>";
                        }
                    }
                }},
                {title:"{{ trans('inspection.inspectionLists') }}", width: 200, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                    innerHtml: function(rowData){
                        if(rowData.hasOwnProperty('id'))
                        {
                            return "<button class='btn btn-xs btn-warning' data-action='initInspectionListTable' data-id='"+rowData['id']+"' data-name='"+rowData['name']+"'>{{ trans('inspection.inspectionLists') }}</button>";
                        }
                    }
                }},
                {title:"{{ trans('general.actions') }}", width: 100, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                    innerHtml: function(rowData){
                        if(rowData.hasOwnProperty('id'))
                        {
                            return "<button type='button' class='btn btn-xs btn-danger' data-route='"+rowData['route:delete']+"' data-method='delete' data-id='"+rowData['id']+"'><i class='fa fa-trash'></i></button>";
                        }
                    }
                }},
            ],
            cellEdited:function(cell){
                var cellData = cell.getData();
                var table = cell.getTable();
                if(cellData.hasOwnProperty('id'))
                {
                    var input = {
                        _token: _csrf_token
                    };
                    input['field'] = cell.getField();
                    input['value'] = cell.getValue();
                    $.post(cellData['route:update'], input)
                    .done(function(data){
                        if(data.success){
                            cell.getRow().update(data.data);
                            // CustomTabulator.onEnter_focus(cell);
                        }
                        else{
                            SmallErrorBox.formValidationError("{{ trans('forms.invalidInput') }}", data.errors[Object.keys(data.errors)[0]]);
                            cell.restoreOldValue();
                            // cell.edit();
                        }
                    })
                    .fail(function(data){
                        console.error('failed');
                    });
                }
                else if(cell.getValue() !== '' && (cell.getValue() !== cell.getOldValue()) && cell.getValue())
                {
                    if(cell.getField() == 'name'){
                        var input = {
                            _token: _csrf_token
                        };
                        input[cell.getField()] = cell.getValue();
                        $.post("{{ route('inspection.groups.store', array($project->id)) }}", input)
                        .done(function(data){
                            if(data.success){
                                cell.getRow().update(data.data);
                                cell.getTable().addRow({});
                                // CustomTabulator.onEnter_focus(cell);
                                cell.getRow().reformat();
                            }
                            else{
                                SmallErrorBox.formValidationError("{{ trans('forms.invalidInput') }}", data.errors[Object.keys(data.errors)[0]]);
                                cell.restoreOldValue();
                                // cell.edit();
                            }
                        })
                        .fail(function(data){
                            console.error('failed');
                        });
                    }
                }
            }
        });

        var rolesTable = new Tabulator('#roles-table', {
            maxHeight:450,
            placeholder: "{{ trans('general.noRecordsFound') }}",
            ajaxURL:"{{ route('inspection.roles', array($project->id)) }}",
            layout:"fitColumns",
            columns:[
                {title:"{{ trans('general.no') }}", formatter:"rownum", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
                {title:"{{ trans('inspection.role') }}", field:"name", minWidth: 300, hozAlign:"left", editor: "input", headerSort:false,
                    editable:app_tabulator_utilities.integerIdEditable,
                    formatter:function(cell){
                        var cellData = cell.getData();
                        if(cellData.hasOwnProperty('id') && (!Number.isInteger(cellData['id'])))
                        {
                            cell.getRow().getElement().className += ' uneditable';
                        }
                        return cell.getValue();
                    }
                },
                {title:"{{ trans('inspection.canRequestInspection') }}", field:"can_request_inspection", minWidth: 100, width:200, hozAlign:"center", headerSort:false, editor:true, formatter:'tickCross', editable:app_tabulator_utilities.integerIdEditable},
                {title:"{{ trans('inspection.users') }}", width: 100, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                    innerHtml: function(rowData){
                        if(rowData.hasOwnProperty('id'))
                        {
                            switch(rowData['id']){
                                case 'submitters':
                                    return "<button class='btn btn-xs btn-warning' data-action='initSubmittersTable' data-id='"+rowData['id']+"'data-name='"+rowData['name']+"'>{{ trans('inspection.users') }}</button>";
                                case 'verifiers':
                                    return "<button class='btn btn-xs btn-warning' data-action='initVerifiersTable' data-id='"+rowData['id']+"'data-name='"+rowData['name']+"'>{{ trans('inspection.users') }}</button>";
                                default:
                                    return "<button class='btn btn-xs btn-warning' data-action='initUsersTable' data-id='"+rowData['id']+"'data-name='"+rowData['name']+"'>{{ trans('inspection.users') }}</button>";
                            }
                        }
                    }
                }},
                {title:"{{ trans('general.actions') }}", width: 100, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                    innerHtml: function(rowData){
                        if(rowData.hasOwnProperty('id') && Number.isInteger(rowData['id']))
                        {
                            return "<button type='button' class='btn btn-xs btn-danger' data-route='"+rowData['route:delete']+"' data-method='delete' data-id='"+rowData['id']+"'><i class='fa fa-trash'></i></button>";
                        }
                    }
                }}
            ],
            cellEdited:function(cell){
                var cellData = cell.getData();
                var table = cell.getTable();
                if(cellData.hasOwnProperty('id'))
                {
                    var input = {
                        _token: _csrf_token
                    };
                    input['field'] = cell.getField();
                    input['value'] = cell.getValue();
                    $.post(cellData['route:update'], input)
                    .done(function(data){
                        if(data.success){
                            cell.getRow().update(data.data);
                            // CustomTabulator.onEnter_focus(cell);
                        }
                        else{
                            SmallErrorBox.formValidationError("{{ trans('forms.invalidInput') }}", data.errors[Object.keys(data.errors)[0]]);
                            cell.restoreOldValue();
                            // cell.edit();
                        }
                    })
                    .fail(function(data){
                        console.error('failed');
                    });
                }
                else if(cell.getValue() !== '' && (cell.getValue() !== cell.getOldValue()) && cell.getValue())
                {
                    if(cell.getField() == 'name'){
                        var input = {
                            _token: _csrf_token
                        };
                        input[cell.getField()] = cell.getValue();
                        $.post("{{ route('inspection.roles.store', array($project->id)) }}", input)
                        .done(function(data){
                            if(data.success){
                                cell.getRow().update(data.data);
                                cell.getTable().addRow({});
                                // CustomTabulator.onEnter_focus(cell);
                                cell.getRow().reformat();
                            }
                            else{
                                SmallErrorBox.formValidationError("{{ trans('forms.invalidInput') }}", data.errors[Object.keys(data.errors)[0]]);
                                cell.restoreOldValue();
                                // cell.edit();
                            }
                        })
                        .fail(function(data){
                            console.error('failed');
                        });
                    }
                }
            }
        });

        var usersTable = new Tabulator('#users-table', {
            maxHeight:450,
            placeholder: "{{ trans('general.noRecordsFound') }}",
            layout:"fitColumns",
            columns:[
                {title:"{{ trans('general.no') }}", formatter:"rownum", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
                {title:"{{ trans('inspection.name') }}", field:"name", minWidth: 300, hozAlign:"left", headerSort:false},
                {title:"{{ trans('inspection.assigned') }}", field:"assigned", minWidth: 100, width:200, hozAlign:"center", headerSort:false, editor:true, formatter:'tickCross', cellEdited: function(cell){
                    var cellData = cell.getData();
                    if(cellData.hasOwnProperty('id'))
                    {
                        var input = {
                            _token: _csrf_token,
                            group_id: groupId,
                            role_id: roleId,
                            assigned: cell.getValue()
                        };
                        $.post(cellData['route:update'], input)
                        .done(function(data){
                            cell.getRow().update(data);
                            CustomTabulator.onEnter_focus(cell);
                        })
                        .fail(function(data){
                            console.error('failed');
                        });
                    }
                }},
            ],
        });

        var submittersTable = new Tabulator('#submitters-table', {
            maxHeight:450,
            placeholder: "{{ trans('general.noRecordsFound') }}",
            layout:"fitColumns",
            columns:[
                {title:"{{ trans('general.no') }}", formatter:"rownum", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
                {title:"{{ trans('inspection.name') }}", field:"name", minWidth: 300, hozAlign:"left", headerSort:false},
                {title:"{{ trans('inspection.assigned') }}", field:"assigned", minWidth: 100, width:200, hozAlign:"center", headerSort:false, editor:true, formatter:'tickCross', cellEdited: function(cell){
                    var cellData = cell.getData();
                    if(cellData.hasOwnProperty('id'))
                    {
                        var input = {
                            _token: _csrf_token,
                            group_id: groupId,
                            assigned: cell.getValue()
                        };
                        $.post(cellData['route:update'], input)
                        .done(function(data){
                            cell.getRow().update(data);
                            CustomTabulator.onEnter_focus(cell);
                        })
                        .fail(function(data){
                            console.error('failed');
                        });
                    }
                }},
            ],
        });

        var notVerifiersTable = new Tabulator('#not-verifiers-table', {
            maxHeight:450,
            placeholder: "{{ trans('general.noRecordsFound') }}",
            layout:"fitColumns",
            columns:[
                {title:"{{ trans('general.no') }}", formatter:"rownum", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
                {title:"{{ trans('inspection.name') }}", field:"name", minWidth: 300, hozAlign:"left", headerSort:false},
                {title:"{{ trans('general.action') }}", minWidth: 100, hozAlign:"center", headerSort:false, formatter:function(cell){
                    var id = cell.getRow().getIndex();
                    return '<button type="button" class="btn btn-xs btn-warning" data-action="add-as-verifier" data-id="'+id+'">{{ trans("general.add") }}</button';
                }},
            ],
        });

        var verifiersTable = new Tabulator('#verifiers-table', {
            maxHeight:450,
            placeholder: "{{ trans('general.noRecordsFound') }}",
            layout:"fitColumns",
            movableRows: true,
            columns:[
                {title:"{{ trans('general.no') }}", formatter:"rownum", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
                {title:"{{ trans('inspection.name') }}", field:"name", minWidth: 300, hozAlign:"left", headerSort:false},
                {title:"{{ trans('general.action') }}", minWidth: 100, hozAlign:"center", headerSort:false, formatter:function(cell){
                    var id = cell.getRow().getIndex();
                    return '<button type="button" class="btn btn-xs btn-danger" data-action="remove-verifier" data-id="'+id+'">{{ trans("general.remove") }}</button';
                }},
            ],
            rowMoved:function(row){
                var userIds;
                userIds = row.getTable().getData().map(function(value,index) {
                    return value['id'];
                });

                $.post("{{ route('inspection.verifierTemplate.users.update', array($project->id)) }}", {
                    _token: _csrf_token,
                    group_id: groupId,
                    user_ids: userIds
                })
                .done(function(data){
                    // cell.getRow().update(data);
                    // CustomTabulator.onEnter_focus(cell);
                })
                .fail(function(data){
                    console.error('failed');
                });
            },
            dataChanged:function(tableData){
                var userIds;
                userIds = tableData.map(function(value,index) {
                    return value['id'];
                });

                $.post("{{ route('inspection.verifierTemplate.users.update', array($project->id)) }}", {
                    _token: _csrf_token,
                    group_id: groupId,
                    user_ids: userIds
                })
                .done(function(data){
                    // cell.getRow().update(data);
                    // CustomTabulator.onEnter_focus(cell);
                })
                .fail(function(data){
                    console.error('failed');
                });
            },
        });

        var notListCategoriesTable = new Tabulator('#not-list-categories-table', {
            maxHeight:450,
            placeholder: "{{ trans('general.noRecordsFound') }}",
            layout:"fitColumns",
            columns:[
                {title:"{{ trans('general.no') }}", formatter:"rownum", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
                {title:"{{ trans('inspection.name') }}", field:"name", minWidth: 300, hozAlign:"left", headerSort:false},
                {title:"{{ trans('general.action') }}", minWidth: 100, hozAlign:"center", headerSort:false, formatter:function(cell){
                    var id = cell.getRow().getIndex();
                    return '<button type="button" class="btn btn-xs btn-warning" data-action="add-list-category" data-id="'+id+'">{{ trans("general.add") }}</button';
                }},
            ],
        });

        var listCategoriesTable = new Tabulator('#list-categories-table', {
            placeholder: "{{ trans('general.noRecordsFound') }}",
            maxHeight:450,
            layout:"fitColumns",
            columns:[
                {title:"{{ trans('general.no') }}", formatter:"rownum", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
                {title:"{{ trans('inspection.name') }}", field:"name", minWidth: 300, hozAlign:"left", headerSort:false},
                {title:"{{ trans('general.action') }}", minWidth: 100, hozAlign:"center", headerSort:false, formatter:function(cell){
                    var id = cell.getRow().getIndex();
                    return '<button type="button" class="btn btn-xs btn-danger" data-action="remove-list-category" data-id="'+id+'">{{ trans("general.remove") }}</button';
                }},
            ],
            dataChanged:function(tableData){
                var ids;
                ids = tableData.map(function(value,index) {
                    return value['id'];
                });

                $.post("{{ route('inspection.groups.listCategories.update', array($project->id)) }}", {
                    _token: _csrf_token,
                    group_id: groupId,
                    list_category_ids: ids
                })
                .done(function(data){
                })
                .fail(function(data){
                    console.error('failed');
                });
            },
        });

        $('#group-table').on('click', '[data-action=initInspectionListTable]', function(){
            $('#group-table').hide();
            groupId = $(this).data('id');
            listCategoriesTable.setData("{{ route('inspection.groups.listCategories', array($project->id)) }}", {group_id:groupId});
            notListCategoriesTable.setData("{{ route('inspection.groups.listCategories.not', array($project->id)) }}", {group_id:groupId});
            $('#list-categories-table').show();
            $('#not-list-categories-table').show();
            breadcrumbs.addItem($(this).data('name'), 'inspection-lists', function(){
                $('#list-categories-table').hide();
                $('#not-list-categories-table').hide();
                $('#group-table').show();
            });
        });

        $('#group-table').on('click', '[data-action=initRoleTable]', function(){
            $('#group-table').hide();
            groupId = $(this).data('id');
            $('#roles-table').show();
            breadcrumbs.addItem($(this).data('name'), 'roles', function(){
                $('#roles-table').hide();
                $('#group-table').show();
            });
        });

        $('#roles-table').on('click', '[data-action=initUsersTable]', function(){
            $('#roles-table').hide();
            roleId = $(this).data('id');
            usersTable.setData("{{ route('inspection.groups.users', array($project->id)) }}", {group_id:groupId, role_id:roleId});
            $('#users-table').show();
            breadcrumbs.addItem($(this).data('name'), 'users', function(){
                $('#users-table').hide();
                $('#roles-table').show();
            });
        });

        $('#roles-table').on('click', '[data-action=initSubmittersTable]', function(){
            $('#roles-table').hide();
            submittersTable.setData("{{ route('inspection.submitters', array($project->id)) }}", {group_id:groupId});
            $('#submitters-table').show();
            breadcrumbs.addItem($(this).data('name'), 'users', function(){
                $('#submitters-table').hide();
                $('#roles-table').show();
            });
        });

        $('#roles-table').on('click', '[data-action=initVerifiersTable]', function(){
            $('#roles-table').hide();
            verifiersTable.setData("{{ route('inspection.verifierTemplate.users', array($project->id)) }}", {group_id:groupId});
            notVerifiersTable.setData("{{ route('inspection.verifierTemplate.users.unassigned', array($project->id)) }}", {group_id:groupId});
            $('#verifiers-table').show();
            $('#not-verifiers-table').show();
            breadcrumbs.addItem($(this).data('name'), 'users', function(){
                $('#verifiers-table').hide();
                $('#not-verifiers-table').hide();
                $('#roles-table').show();
            });
        });

        $('#not-verifiers-table').on('click', '[data-action=add-as-verifier][data-id]', function(){
            var row = notVerifiersTable.getRow($(this).data('id'));
            verifiersTable.addData(row.getData());
            row.delete();
        });

        $('#verifiers-table').on('click', '[data-action=remove-verifier][data-id]', function(){
            var row = verifiersTable.getRow($(this).data('id'));
            notVerifiersTable.addData(row.getData());
            row.delete();
        });

        $('#not-list-categories-table').on('click', '[data-action=add-list-category][data-id]', function(){
            var row = notListCategoriesTable.getRow($(this).data('id'));
            listCategoriesTable.addData(row.getData());
            row.delete();
        });

        $('#list-categories-table').on('click', '[data-action=remove-list-category][data-id]', function(){
            var row = listCategoriesTable.getRow($(this).data('id'));
            notListCategoriesTable.addData(row.getData());
            row.delete();
        });

        $('.tabulator').on('click', '.tabulator-row [data-method=delete]', function(){
            var rowId = $(this).data('id');
            var self = this;
            $.post($(this).data('route'), {
                _token: _csrf_token,
                _method: $(this).data('method')
            })
            .done(function(data){
                if(data.success){
                    var table = Tabulator.prototype.findTable("#"+$(self).closest('.tabulator').attr('id'))[0];
                    table.getRow(rowId).delete();
                }
                else
                {
                    SmallErrorBox.formValidationError("{{ trans('forms.deleteFailed') }}", data.errorMsg);
                }
            })
            .fail(function(data){
                console.error('failed');
            });
        });
    </script>
@endsection