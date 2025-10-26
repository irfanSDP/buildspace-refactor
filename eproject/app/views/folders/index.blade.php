@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{{ trans('folders.eProjectDrive') }}}</li>
    </ol>
@endsection

@section('content')
<div class="row">
    <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
        <h1 class="page-title txt-color-blueDark">
            <i class="fa fa-hdd"></i> {{{ trans('folders.eProjectDrive') }}}
        </h1>
    </div>
    <div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">
        <div class="btn-group pull-right header-btn">
            @include('folders.action_menu')
        </div>
    </div>
</div>

<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="jarviswidget ">
            <header>
                <h2>{{{ trans('folders.eProjectDrive') }}}</h2>
            </header>
            <div>
                <div class="widget-body">
                    <ol class="breadcrumb bg-transparent border border-info">
                        @foreach($ancestors as $ancestor)
                            <li class="breadcrumb-item text-info"><a href="{{ $ancestor['route'] }}" class="text-info">{{{ $ancestor['name'] }}}</a></li>
                        @endforeach
                    </ol>
                    @if($canEdit)
                    <fieldset>
                        {{ Form::open(array('id' => 'folder-form', 'class' => 'smart-form')) }}
                        <form class="smart-form">
                            <div class="row">
                                <section class="col col-xs-9 col-md-9 col-lg-9">
                                    <h5 id="form-header">{{{ trans('folders.addFolder') }}}</h5>
                                </section>
                                <section class="col col-xs-3 col-md-3 col-lg-3">
                                    <div class="pull-right">
                                        <button type="submit" class="btn btn-primary btn-md header-btn">
                                            <i class="far fa-save"></i> {{{ trans('forms.save') }}}
                                        </button>
                                        <button type="button" class="btn btn-default btn-md header-btn" id="edit-cancel-btn" style="display:none;">{{{ trans('forms.cancel') }}}</button>
                                    </div>
                                </section>
                            </div>
                            <br/>
                            <div class="row">
                                <section class="col col-xs-12 col-md-6 col-lg-6">
                                    <label class="label">{{{ trans('folders.name') }}} <span class="required">*</span>:</label>
                                    <label class="input" data-input="name">
                                        {{ Form::text('name', Input::old('name'), array('required' => 'required', 'autofocus' => 'autofocus')) }}
                                    </label>
                                    <em class="invalid" data-error="form"></em> <em class="invalid" data-error="name"></em>
                                </section>
                                <section class="col col-xs-12 col-md-6 col-lg-6">
                                    <label class="label">{{{ trans('folders.description') }}}:</label>
                                    <label class="input" data-input="description">
                                        {{ Form::text('description', Input::old('description')) }}
                                    </label>
                                    <em class="invalid" data-error="description"></em>
                                </section>
                            </div>
                            {{ Form::hidden('file_node_id', Input::old('file_node_id') ?? -1, ['id'=>'folder-id-hidden']) }}
                        {{ Form::close() }}
                    </fieldset>
                    <hr class="simple"/>
                    <fieldset>
                        <form class="smart-form">
                            <div class="row">
                                <section class="col col-xs-9 col-md-9 col-lg-9">
                                    <h5 id="form-header">{{{ trans('folders.uploadFiles') }}}</h5>
                                </section>
                                <section class="col col-xs-3 col-md-3 col-lg-3">
                                    <div class="pull-right">
                                        <button type="button" class="btn btn-primary btn-md header-btn" data-action="upload-item-attachments" data-do-upload="{{ route('folders.upload', array($fileNode->id)) }}" data-get-uploads="{{ route('folders.getAttachments', array($fileNode->id)) }}">
                                            <i class="fa fa-upload"></i> {{{ trans('forms.upload') }}}
                                        </button>
                                    </div>
                                </section>
                            </div>
                        </form>
                    </fieldset>
                    @endif
                    <div id="t1"></div>
                    <div id="main-table"></div>
                </div>
            </div>
        </div>
    </div>
</div>
@include('uploads.uploadModal')
@include('templates.generic_table_modal', array('modalId' => 'move-modal', 'title' => trans('folders.moveTo'), 'tableId' => 'move-table', 'tablePadding' => true))
@include('templates.generic_table_modal', array('modalId' => 'permissions-modal', 'title' => trans('folders.permissions'), 'tableId' => 'permissions-table', 'modalDialogClass' => 'modal-xl', 'tablePadding' => true))
@include('templates.modals.confirmation', array('modalId' => 'apply-to-subfolders-confirmation-modal', 'message' => trans('folders.applyToSubfolders')))
@include('templates.generic_table_modal', array('modalId' => 'overview-modal', 'title' => trans('folders.overview'), 'tableId' => 'overview-table', 'tablePadding' => true))
@endsection

@section('js')
    <script src="<?php echo asset('js/app/app.restfulDelete.js'); ?>"></script>
    <script>
        $(document).ready(function () {
            var mainTable = new Tabulator('#main-table', {
                height:450,
                placeholder: "{{ trans('general.noRecordsFound') }}",
                ajaxConfig: "GET",
                paginationSize: 100,
                pagination: "remote",
                ajaxFiltering:true,
                layout:"fitColumns",
                ajaxURL: "{{ route('folders.list', array($fileNode->id)) }}",
                @if($fileNode->exists)
                movableRows: true,
                rowMoved:function(row){
                    this.modules.ajax.showLoader();

                    var position = this.getRowPosition(row);

                    var previousNodeId = null;

                    if(position > 0)
                    {
                        previousNodeId = this.getRowFromPosition(position-1).getData()['id'];
                    }

                    $.ajax({
                        type: "POST",
                        url: "{{ route('folders.reposition', array($fileNode->id)) }}",
                        data: {
                            node_id: row.getData()['id'],
                            new_previous_node_id: previousNodeId,
                            _token: _csrf_token,
                        },
                        success: function (resp) {
                            if(!resp.success){
                                if(resp.errors.length > 0){
                                    $.smallBox({
                                        title : "{{ trans('general.anErrorHasOccured') }}",
                                        content : "<i class='fa fa-times'></i> <i>" + resp.errors[0].msg + "</i>",
                                        color : "#C46A69",
                                        sound: false,
                                        iconSmall : "fa fa-exclamation-triangle shake animated"
                                    });
                                }
                                mainTable.setData();
                            }
                            mainTable.modules.ajax.hideLoader();
                        },
                        fail: function(resp){
                            mainTable.modules.ajax.hideLoader();
                        }
                    });
                },
                @endif
                columns:[
                    {title:"{{ trans('general.no') }}", formatter:"rownum", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
                    {title:"{{ trans('folders.type') }}", field:"type", width: 50, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, editable: false, editor:"select", headerFilter:true, headerFilterParams:{values:{0:"{{ trans('general.all') }}", 1:"{{ trans('folders.folder') }}", 2:"{{ trans('folders.file') }}"}}, formatter:function(cell){
                        if(cell.getData()['is_folder'])
                        {
                            return '<i class="fa fa-lg fa-folder text-success"></i>';
                        }
                        return '<i class="fa fa-lg fa-file text-primary"></i>';
                    }},
                    {title:"{{ trans('folders.name') }}", field:"name", minWidth: 300, hozAlign:"left", headerSort:false, headerFilter:true, formatter:function(cell){
                        var rowData = cell.getData();
                        if(rowData['is_folder'])
                        {
                            return '<a href="'+rowData['route:next']+'">'+rowData['name']+'</a>';
                        }
                        return '<a href="'+rowData['route:download']+'" download="'+rowData['name']+'">'+rowData['name']+'</a>';
                    }},
                    {title:"{{ trans('folders.description') }}", field:"description", width: 300, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter:true},
                    @if(!$currentUser->isCompanyTypeExternal())
                    {title:"{{ trans('general.actions') }}", width: 130, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                        innerHtml: [
                            {
                                opaque:function(cell){
                                    return cell.getData()['editable'];
                                },
                                tag: 'button',
                                rowAttributes: {'data-id':'id'},
                                attributes: {"data-action":"edit", type:'button', class:'btn btn-xs btn-warning', title: '{{ trans("general.edit") }}'},
                                innerHtml: {
                                    tag: 'i',
                                    attributes: {class: 'fa fa-edit'}
                                }
                            },{
                                innerHtml: function(){
                                    return '&nbsp;';
                                }
                            },{
                                opaque:function(cell){
                                    return cell.getData()['editable'];
                                },
                                tag: 'button',
                                rowAttributes: {'data-id':'id'},
                                attributes: {"data-action":"set-permissions", type:'button', class:'btn btn-xs btn-primary', title: '{{ trans("folders.permissions") }}'},
                                innerHtml: {
                                    tag: 'i',
                                    attributes: {class: 'fa fa-users'}
                                }
                            },{
                                innerHtml: function(){
                                    return '&nbsp;';
                                }
                            }
                            @if($canEdit)
                            ,{
                                opaque:function(cell){
                                    return cell.getData()['can_move'];
                                },
                                tag: 'button',
                                rowAttributes: {'data-id':'id'},
                                attributes: {"data-action":"move", type:'button', class:'btn btn-xs btn-default', title: '{{ trans("folders.move") }}'},
                                innerHtml: {
                                    tag: 'i',
                                    attributes: {class: 'fa fa-exchange-alt'}
                                }
                            },{
                                innerHtml: function(){
                                    return '&nbsp;';
                                }
                            },{
                                opaque:function(cell){
                                    return cell.getData()['editable'];
                                },
                                tag: 'span',
                                attributes: {},
                                innerHtml: function(rowData){
                                    return '<a href="'+rowData['route:delete']+'" class="btn btn-xs btn-danger" data-id="'+rowData['id']+'" data-method="delete" data-csrf_token="{{{ csrf_token() }}}"><i class="fa fa-trash"></i></a>';
                                }
                            }
                            @endif
                        ]
                    }}
                    @endif
                ],
            });

            $("#folder-form").on('submit', function(e){
                app_progressBar.toggle();
                var dataStr = $(this).serialize();
                $.ajax({
                    type: "POST",
                    url: "{{ route('folders.storeOrUpdate', array($fileNode->id)) }}",
                    data: dataStr,
                    success: function (resp) {
                        app_progressBar.maxOut();
                        $("#folder-form [data-input]").removeClass('state-error');
                        $("#folder-form [data-error]").html("");

                        if(!resp.success){
                            $.each( resp.errors, function( key, data ) {
                                $("#folder-form [data-input="+data.key+"]").addClass('state-error');
                                $("#folder-form [data-error="+data.key+"]").html(data.msg);
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
                            mainTable.setData();
                        }
                        app_progressBar.toggle();
                    }
                });

                e.preventDefault();
            });
            $("#edit-cancel-btn").on('click', function(){
                resetForm();
            });
            function resetForm(){
                $("#form-header").html("{{{ trans('vendorPreQualification.addItem') }}}");
                $("#edit-cancel-btn").hide();
                $("#remarks").hide();
                $("#folder-form [data-input]").removeClass('state-error');
                $("#folder-form [data-error]").html("");
                $("#folder-form [name=name]").val("");
                $("#folder-form [name=description]").val("");
                $('#folder-id-hidden').val(-1);
                $("#folder-form [name=name]").focus();
            }
            $('#main-table').on('click', '[data-action=edit]', function(){
                $("#form-header").html("{{{ trans('forms.edit') }}}");
                $("#edit-cancel-btn").show();
                $("#folder-form [data-input]").removeClass('state-error');
                $("#folder-form [data-error]").html("");
                var row = mainTable.getRow($(this).data('id'));
                $("#folder-form [name=name]").val(row.getData()['name']);
                $("#folder-form [name=description]").val(row.getData()['description']);
                $('#folder-id-hidden').val($(this).data('id'));
                $("#remarks").hide();
                if(row.getData()['remarks'])
                {
                    $("#remarks").show();
                    if(row.getData()['amendments_required'])
                    {
                        $("#remarks").removeClass("border-success");
                        $("#remarks").removeClass("text-success");
                        $("#remarks").addClass("border-danger");
                        $("#remarks").addClass("text-danger");
                    }
                    else
                    {
                        $("#remarks").removeClass("border-danger");
                        $("#remarks").removeClass("text-danger");
                        $("#remarks").addClass("border-success");
                        $("#remarks").addClass("text-success");
                    }
                    $("[data-label=remarks]").html(row.getData()['remarks']);
                }
                $("#folder-form [name=name]").focus();
            });

            $('#uploadAttachmentModal').on('uploadAttachmentModal.done', function(e, response){
                mainTable.setData();
            });

            var moveTable = new Tabulator('#move-table', {
                dataTree: true,
                dataTreeStartExpanded:true,
                height:450,
                placeholder: "{{ trans('general.noRecordsFound') }}",
                layout:"fitColumns",
                columns:[
                    {title:"{{ trans('folders.name') }}", field:"name", minWidth: 300, hozAlign:"left", headerSort:false},
                    {title:"{{ trans('folders.description') }}", field:"description", width: 300, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false},
                    {title:"{{ trans('general.actions') }}", width: 120, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                        innerHtml: [
                            {
                                tag: 'button',
                                rowAttributes: {'data-id':'id'},
                                attributes: {"data-action":"select-move-target", type:'button', class:'btn btn-xs btn-default', title: '{{ trans("forms.select") }}'},
                                innerHtml: function(rowData){
                                    return "{{ trans('forms.select') }}";
                                }
                            }
                        ]
                    }}
                ],
            });

            var moveRoute;

            $('#main-table').on('click', '[data-action=move]', function(){
                var row = mainTable.getRow($(this).data('id'));
                moveTable.setData(row.getData()['route:moveList']);
                moveRoute = row.getData()['route:move'];
                $('#move-modal').modal('show');
            });

            $('#move-table').on('click', '[data-action=select-move-target]', function(){
                mainTable.modules.ajax.showLoader();
                $.ajax({
                    type: "POST",
                    url: moveRoute,
                    data: {
                        target_node_id: $(this).data('id'),
                        _token: _csrf_token,
                    },
                    success: function (resp) {
                        if(!resp.success){
                            if(resp.errors.length > 0){
                                $.smallBox({
                                    title : "{{ trans('general.anErrorHasOccured') }}",
                                    content : "<i class='fa fa-times'></i> <i>" + resp.errors[0].msg + "</i>",
                                    color : "#C46A69",
                                    sound: false,
                                    iconSmall : "fa fa-exclamation-triangle shake animated"
                                });
                            }
                        }
                        mainTable.setData();
                        mainTable.modules.ajax.hideLoader();
                        $('#move-modal').modal('hide');
                    },
                    fail: function(resp){
                        mainTable.modules.ajax.hideLoader();
                    }
                });
            });

            var permissionsTable = new Tabulator('#permissions-table', {
                height:450,
                placeholder: "{{ trans('general.noRecordsFound') }}",
                ajaxConfig: "GET",
                paginationSize: 100,
                pagination: "remote",
                ajaxFiltering:true,
                layout:"fitColumns",
                columns:[
                    {title:"{{ trans('general.no') }}", field:"counter", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false, frozen: true},
                    {title:"{{ trans('users.name') }}", field:"name", minWidth:300, hozAlign:"left", headerSort:false, headerFilter:true, frozen: true},
                    {title:"{{ trans('users.email') }}", field:"username", width:200, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false, headerFilter:true},
                    {title:"{{ trans('users.company') }}", field:"company", width:200, hozAlign:"center", headerSort:false, cssClass:"text-center text-middle", headerFilter:true},
                    {title:"{{ trans('folders.viewer') }}", field:"viewer", width: 100, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, editable: false, editor:"select", headerFilter:true, headerFilterParams:{values:{0:"{{ trans('general.all') }}", 'true':"{{ trans('general.yes') }}", 'false':"{{ trans('general.no') }}"}}, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                        innerHtml: function(rowData){
                            var checked = rowData['is_viewer'] ? 'checked' : '';
                            return '<input type="checkbox" data-action="toggle-viewer" data-id="'+rowData['id']+'" '+checked+'>';
                        }
                    }},
                    {title:"{{ trans('folders.editor') }}", field:"editor", width: 100, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, editable: false, editor:"select", headerFilter:true, headerFilterParams:{values:{0:"{{ trans('general.all') }}", 'true':"{{ trans('general.yes') }}", 'false':"{{ trans('general.no') }}"}}, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                        innerHtml: function(rowData){
                            var checked = rowData['is_editor'] ? 'checked' : '';
                            var disabled = !rowData['can_be_editor'] ? 'disabled' : '';
                            return '<input type="checkbox" data-action="toggle-editor" data-id="'+rowData['id']+'" '+checked+' '+disabled+'>';
                        }
                    }},
                ],
            });

            $('#main-table').on('click', '[data-action=set-permissions]', function(){
                var row = mainTable.getRow($(this).data('id'));
                permissionsTable.setData(row.getData()['route:permissions']);
                $('#permissions-modal').modal('show');
            });

            function updatePermissions(url, applyToSubfolders, grant, setEditor){
                permissionsTable.modules.ajax.showLoader();
                var params = {
                    _token: _csrf_token,
                    apply_to_subfolders: applyToSubfolders,
                    set_editor: setEditor,
                    grant: grant,
                };
                $.post(url, params)
                .done(function(data){
                    if(!data.success){
                        if(data.errors.length > 0){
                            $.smallBox({
                                title : "{{ trans('general.anErrorHasOccured') }}",
                                content : "<i class='fa fa-times'></i> <i>" + data.errors[0].msg + "</i>",
                                color : "#C46A69",
                                sound: false,
                                iconSmall : "fa fa-exclamation-triangle shake animated"
                            });
                        }
                    }
                    permissionsTable.replaceData();
                    permissionsTable.modules.ajax.hideLoader();
                })
                .fail(function(data){
                   console.error('failed');
                   permissionsTable.modules.ajax.hideLoader();
                });
            }

            var permissionRoute;
            var grant;
            var setEditor;
            var eventTriggerElement;

            $('#permissions-table').on('click', '[data-action=toggle-viewer],[data-action=toggle-editor]', function(){
                eventTriggerElement = this;
                var row = permissionsTable.getRow($(this).data('id'));
                permissionRoute = row.getData()['route:update'];
                grant = $(this).prop('checked');
                setEditor = $(this).data('action') == 'toggle-editor';

                if(grant){
                    updatePermissions(permissionRoute, true, grant, setEditor);
                }
                else{
                    $('#apply-to-subfolders-confirmation-modal').modal('show');
                }
            });

            $('#apply-to-subfolders-confirmation-modal').on('click', '[data-action=proceed]', function(){
                updatePermissions(permissionRoute, true, grant, setEditor);
            });

            $('#apply-to-subfolders-confirmation-modal').on('click', '[data-action=abort]', function(){
                updatePermissions(permissionRoute, false, grant, setEditor);
            });

            $('#apply-to-subfolders-confirmation-modal').on('hide.bs.modal', function(){
                permissionsTable.replaceData();
            });

            var overviewTable = new Tabulator('#overview-table', {
                dataTree: true,
                dataTreeStartExpanded:true,
                height:450,
                placeholder: "{{ trans('general.noRecordsFound') }}",
                layout:"fitColumns",
                columns:[
                    {title:"{{ trans('folders.name') }}", field:"name", minWidth: 300, hozAlign:"left", headerSort:false, formatter:function(cell){
                        var rowData = cell.getData();

                        if(rowData['type'] == '{{ \PCK\Folder\FileNode::TYPE_FILE }}'){
                            if(rowData['route:download']){
                                return '<a href="'+rowData['route:download']+'" download="'+rowData['name']+'">'+rowData['name']+'</a>';
                            }
                        }

                        var icon = '<span class="label label-warning folder-state-label"><i class="fa fa-lg fa-folder-open folder-state"></i></span>';

                        var name = '<a href="'+rowData['route:next']+'" class="txt-color-white">'+rowData['name']+'</a>';

                        name = '<span class="label label-success">'+name+'</span>';

                        return icon + '&nbsp;' + name;
                    }},
                    {title:"{{ trans('folders.description') }}", field:"description", width: 300, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false},
                ],
            });

            $('[data-action=overview]').on('click', function(){
                overviewTable.setData("{{ route('folders.overviewList') }}");
                $('#overview-modal').modal('show');
            });
        });
    </script>
@endsection