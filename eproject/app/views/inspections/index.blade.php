@extends('layout.main')

@section('breadcrumb')
	<ol class="breadcrumb">
		<li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), []) }}</li>
        @if(isset($project))
            <li>{{ link_to_route('projects.show', str_limit($project->title, 50), [$project->id]) }}</li>
            <li>{{ trans('inspection.inspectionLists') }}</li>
        @else
            <li>{{ trans('inspection.masterInspectionLists') }}</li>
        @endif

	</ol>
@endsection
<?php use PCK\Inspections\InspectionListCategory; ?>
<?php use PCK\Inspections\InspectionListItem; ?>
@section('content')
    <div class="row">
		<div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
			<h1 class="page-title txt-color-blueDark">
				<i class="fa fa-list-alt"></i> @if(isset($project)) {{ trans('inspection.inspectionLists') }} @else {{ trans('inspection.masterInspectionLists') }} @endif
			</h1>
		</div>
        @if(isset($project))
            <div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">
                <a href="#" id="btnCopyFromMasterInspectionList" data-toggle="modal" data-target="#masterInspectionListSelectionModal" class="btn btn-primary pull-right">{{ trans('inspection.copyFromMasterList') }}</a>
            </div>
        @endif
	</div>

    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <div class="jarviswidget">
                <header>
                    <h2>@if(isset($project)) {{ trans('inspection.inspectionLists') }} @else {{ trans('inspection.masterInspectionLists') }} @endif</h2>
                </header>
                <div class="widget-body">
                    <ol id="inspectionListBreadCrumb" class="breadcrumb bg-transparent border border-info"></ol>
                    @if(!isset($project))
                        <div id="inspection-lists-table"></div>
                        <div id="inspection-categories-table" hidden=""></div>
                    @else
                        <div id="inspection-categories-table"></div>
                    @endif
                    <div id="inspection-list-items-table" hidden=""></div>
                </div>
            </div>
        </div>
    </div>

    @include('templates.yesNoModal', [
        'modalId'   => 'yesNoModal',
        'titleId'   => 'yesNoModalTitle',
        'title'     => trans('general.confirmation'),
        'messageId' => 'yesNoModalMessage',
    ])
    @include('templates.yesNoModal', [
        'modalId'   => 'switchTypeYesNoModal',
        'titleId'   => 'switchTypeYesNoModalTitle',
        'title'     => trans('general.confirmation'),
        'messageId' => 'switchTypeYesNoModalMessage',
        'isStatic'  => true,
    ])
    @include('templates.logs_table_modal', [
        'modalId'      => 'additionalFieldsModal',
        'modalTitleId' => 'additionalFieldsTableTitle',
        'tableId'      => 'additionalFieldsTable',
    ])
    @if(isset($project))
        @include('inspections.partials.master_inspection_list_selection_modal', [
            'modalId'                         => 'masterInspectionListSelectionModal',
            'breadcrumbId'                    => 'inspectionListSelectionBreadCrumb',
            'inspectionListsTableId'          => 'masterInspectionListSelectionTable',
            'inspectionListCategoriesTableId' => 'masterInspectionListCategoriesSelectionTable',
            'saveButtonId'                    => 'btnSaveSelectedMasterInspectionListCategories',
        ])
    @endif
    @include('templates.warning_modal', [
        'modalId'          => 'warningModal',
        'warningMessageId' => 'txtWarningMessage',
    ])
@endsection

@section('js')
    <script>
        $(document).ready(function(e) {
            var inspectionListsTable          = null;
            var inspectionListCategoriesTable = null;
            var inspectionListItemsTable      = null;
            var additionalFieldsTable         = null;

            @if(isset($project))
                var masterInspectionListSelectionTable = null;
                var masterInspectionListCategoriesSelectionTable = null;
            @endif

            var inspectionListId     = null;
            var inspectionCategoryId = null;

            $("#yesNoModal").on('show.bs.modal', function(e) {
                $(this).css("z-index", "2000");
            });

            $("#switchTypeYesNoModal").on('show.bs.modal', function(e) {
                $(this).css("z-index", "1500");
            });

            $("#switchTypeYesNoModal").on('hide.bs.modal', function(e) {
                $('#txtWarningMessage').html('');
            });

            var breadcrumbs = new DynamicBreadcrumbs('#inspectionListBreadCrumb');
            breadcrumbs.addItem('<i class="fa fa-home"></i>', 'inspection-list');

            @if(!isset($project))
                var inspectionListActionsFormatter = function(cell, formatterParams, onRendered) {
                    if(cell.getRow().getData().id < 0) return null;

                    var openButton = document.createElement('a');
                    openButton.id = 'btnOpenInspectionList_' + cell.getRow().getData().id;
                    openButton.dataset.id = cell.getRow().getData().id;
                    openButton.dataset.name = cell.getRow().getData().name;
                    openButton.dataset.url = cell.getRow().getData().route_show;
                    openButton.dataset.toggle = 'tooltip';
                    openButton.dataset.action = 'showInspectionListCategories';
                    openButton.title = "{{ trans('inspection.goToNextLevel') }}";
                    openButton.className = 'btn btn-xs btn-success';
                    openButton.innerHTML = '<i class="fa fa-arrow-right"></i>';
                    openButton.style['margin-right'] = '5px';

                    var deleteButton = document.createElement('a');
                    deleteButton.id = 'btnDeleteInspectionList_' + cell.getRow().getData().id;
                    deleteButton.dataset.route_delete = cell.getRow().getData().route_delete;
                    deleteButton.dataset.toggle = 'modal';
                    deleteButton.dataset.target = '#yesNoModal';
                    deleteButton.dataset.delete_type = 'list';
                    deleteButton.title = "{{ trans('inspection.deleteInspectionList') }}";
                    deleteButton.className = 'btn btn-xs btn-danger';
                    deleteButton.innerHTML = '<i class="fa fa-trash"></i>';
                    deleteButton.style['margin-right'] = '5px';

                    deleteButton.addEventListener('click', function(e) {
                        e.preventDefault();

                        $('#yesNoModalMessage').html("{{ trans('inspection.allContentsWillBeDeleted') . ' ' . trans('general.sureToProceed') }}");
                        $('[data-action=actionYes]').data('route_delete', $(this).data('route_delete'));
                        $('[data-action=actionYes]').data('delete_type', $(this).data('delete_type'));
                    });

                    var container = document.createElement('div');
                    container.appendChild(openButton);
                    container.appendChild(deleteButton);

                    return container;
                }

                inspectionListsTable = new Tabulator('#inspection-lists-table', {
                    height:500,
                    columns: [
                        { title: '<div class="text-center">{{ trans('general.no') }}</div>', field: 'indexNo', width: 60, 'align': 'center', headerSort:false },
                        { title: '<div class="text-left">{{ trans('general.name') }}</div>', field: 'name', headerSort:false, editor: 'input', headerFilter:"input" },
                        { title: '<div class="text-center">{{ trans('general.actions') }}</div>', width: 80, 'align': 'center', headerSort:false, formatter: inspectionListActionsFormatter },
                    ],
                    layout:"fitColumns",
                    ajaxURL: "{{ $getInspectionListRoute }}",
                    placeholder:"{{ trans('general.noDataAvailable') }}",
                    columnHeaderSortMulti:false,
                    cellEdited:function(cell) {
                        var row = cell.getRow();
                        var item = row.getData();
                        var field = cell.getField();
                        var value = cell.getValue();
                        var table = cell.getTable();

                        var itemID = parseInt(item.id);

                        var url = (itemID > 0) ? cell.getRow().getData().route_update : "{{ $inspectionListStoreRoute }}";

                        table.modules.ajax.showLoader();

                        var params = {
                            id: itemID,
                            @if(isset($project) && !is_null($project)) projectId: {{ $project->id }}, @endif
                            field: field,
                            val: value,
                            _token:'{{{csrf_token()}}}',
                        };

                        $.post(url, params)
                            .done(function(data){
                                cell.getRow().update(data.item);

                                if(itemID < 0) {
                                    cell.getTable().addRow(data.emptyRow);
                                }

                                cell.getRow().reformat();

                                table.modules.ajax.hideLoader();
                            })
                            .fail(function(data){
                                console.error('failed');
                                table.modules.ajax.hideLoader();
                            });
                    },
                });
            @endif

			var categoryTypeFormatter = function(cell, formatterParams, onRendered){
                var data = cell.getRow().getData();
                var cellValue = null;

                switch(data.type.toString()) {
                    case "{{ InspectionListCategory::TYPE_INSPECTION_CATEGORY }}": 
                        cellValue = "{{ trans('inspection.category') }}";
                        break;
                    default:
                        cellValue = "{{ trans('inspection.list') }}";
                }

                return cellValue;
            };

            var inspectionCategoryActionsFormatter = function(cell, formatterParams, onRendered) {
                if(cell.getRow().getData().id < 0) return null;

                var container = document.createElement('div');

				var openButton = document.createElement('a');
                openButton.id = 'btnOpenInspectionCategory_' + cell.getRow().getData().id;
				openButton.dataset.id = cell.getRow().getData().id;
                openButton.dataset.name = cell.getRow().getData().name;
                openButton.dataset.type = cell.getRow().getData().type;
                openButton.dataset.depth = cell.getRow().getData().depth;
                openButton.dataset.url = cell.getRow().getData().route_show;
                openButton.dataset.back_url = cell.getRow().getData().route_back;
				openButton.dataset.toggle = 'tooltip';
                openButton.dataset.action = 'showInspectionCategoryChildren';
				openButton.title = "{{ trans('inspection.goToNextLevel') }}";
                openButton.className = 'btn btn-xs btn-success';
                openButton.innerHTML = '<i class="fa fa-arrow-right"></i>';
                openButton.style['margin-right'] = '5px';

                container.appendChild(openButton);
                
                if(cell.getRow().getData().editable)
                {
                    var deleteButton = document.createElement('a');
                    deleteButton.id = 'btnDeleteInspectionList_' + cell.getRow().getData().id;
                    deleteButton.dataset.route_delete = cell.getRow().getData().route_delete;
                    deleteButton.dataset.toggle = 'modal';
                    deleteButton.dataset.target = '#yesNoModal';
                    deleteButton.dataset.delete_type = 'category';
                    deleteButton.title = "{{ trans('inspection.deleteInspectionList') }}";
                    deleteButton.className = 'btn btn-xs btn-danger';
                    deleteButton.innerHTML = '<i class="fa fa-trash"></i>';
                    deleteButton.style['margin-right'] = '5px';

                    deleteButton.addEventListener('click', function(e) {
                        e.preventDefault();

                        $('#yesNoModalMessage').html("{{ trans('inspection.allContentsWillBeDeleted') . ' ' . trans('general.sureToProceed') }}");
                        $('[data-action=actionYes]').data('id', cell.getRow().getData().id);
                        $('[data-action=actionYes]').data('route_delete', $(this).data('route_delete'));
                        $('[data-action=actionYes]').data('delete_type', $(this).data('delete_type'));
                    });

                    container.appendChild(deleteButton);
                }

                if(cell.getRow().getData().type == "{{ InspectionListCategory::TYPE_INSPECTION_LIST }}")
                {
                    var additionalFieldsButton = document.createElement('a');
                    additionalFieldsButton.id = 'btnAdditonalFields_' + cell.getRow().getData().id;
                    additionalFieldsButton.dataset.toggle = 'modal';
                    additionalFieldsButton.dataset.target = '#additionalFieldsModal';
                    additionalFieldsButton.className = 'btn btn-xs btn-primary';
                    additionalFieldsButton.innerHTML = '<i class="fa fa-list-ol"></i>';
                    additionalFieldsButton.style['margin-right'] = '5px';

                    additionalFieldsButton.addEventListener('click', function(e) {
                        e.preventDefault();

                        $('#additionalFieldsTableTitle').html("{{ trans('inspection.additionalFieldsFor') }}" + ' ' + cell.getRow().getData().name);
                        $('#additionalFieldsModal').data('route_additonal_fields', cell.getRow().getData().route_additonal_fields);
                    });

                    container.appendChild(additionalFieldsButton);
                }

				return container;
			}

            var editCheck = function(cell){
                return cell.getRow().getData().editable; 
            }

            inspectionListCategoriesTable = new Tabulator('#inspection-categories-table', {
                height:500,
                columns: [
                    { title: "id", field: 'id', visible:false },
                    { title: '<div class="text-center">{{ trans('general.no') }}</div>', field: 'indexNo', width: 60, 'align': 'center', headerSort:false },
                    { title: '<div class="text-left">{{ trans('general.name') }}</div>', field: 'name', editor: 'input', headerSort:false, editable:editCheck, headerFilter:"input" },
                    { title: '<div class="text-center">{{ trans('general.type') }}</div>', field: 'type', width: 120, 'align': 'center', editor:"select", formatter: categoryTypeFormatter, editorParams:{values: <?php echo json_encode($categoryTypes); ?>}, headerSort:false },
                    { title: '<div class="text-center">{{ trans('general.actions') }}</div>', width: 100, 'align': 'left', headerSort:false, formatter: inspectionCategoryActionsFormatter },
                ],
                layout:"fitColumns",
                movableColumns:true,
                @if(isset($project))
                ajaxURL: "{{ route('project.inspection.list.categories.get', [$project->id, $project->inspectionLists->first()->id]) }}",
                @endif
                placeholder:"{{ trans('general.noDataAvailable') }}",
                columnHeaderSortMulti:false,
				cellEdited:function(cell) {
					var row = cell.getRow();
                    var item = row.getData();
                    var field = cell.getField();
                    var value = cell.getValue();
                    var table = cell.getTable();

                    var itemID = parseInt(item.id);
                    var url = itemID > 0 ? "{{ $inspectionListCategoryUpdateRoute }}" : "{{ $inspectionListCategoryStoreRoute }}";

                    table.modules.ajax.showLoader();

                    var params = {
                		id: itemID,
                		field: field,
                		val: value,
                		inspectionListId: item.inspection_list_id, 
                        parentId: item.parent_id,
                		type: item.type,
                		_token:'{{{csrf_token()}}}',
                    };

                    if((field == 'type') && (itemID != -1)) {
                        $.ajax({
                            url: item.route_change_type_check,
                            method: 'GET',
                            data: params,
                            success: function (response) {
                                if(response.success) {
                                    if(response.warning) {
                                        $('#switchTypeYesNoModalMessage').html("{{ trans('inspection.changeCategoryTypeMessage') . ' ' . trans('general.sureToProceed') }}");
                                        $('#switchTypeYesNoModal [data-action=actionYes]').data('url', url);
                                        $('#switchTypeYesNoModal [data-action=actionYes]').data('params', params);
                                        $('#switchTypeYesNoModal [data-action=actionYes]').data('cell', cell);
                                        $('#switchTypeYesNoModal [data-dismiss=modal]').data('cell', cell);
                                        $('#switchTypeYesNoModal').modal('show');
                                    } else {
                                        executeRequest(url, params, cell);
                                    }
                                } else {
                                    displayWarning(response.errors);
                                    cell.restoreOldValue();
                                    cell.getTable().modules.ajax.hideLoader();
                                }
                            },
                            error: function (jqXHR, textStatus, errorThrown) {
                                // error
                            }
                        });
                    } else {
                        executeRequest(url, params, cell);
                    }
				},
            });

            function executeRequest(url, params, cell) {
                var itemID = cell.getRow().getData().id;

                $.post(url, params)
                    .done(function(data){
                        if(data.success) {
                            cell.getRow().update(data.item);

                            if(itemID < 0) {
                                cell.getTable().addRow(data.emptyRow);
                            }

                            cell.getRow().reformat();
                        } else {
                            displayWarning(data.errors);
                            cell.restoreOldValue();
                        }

                        cell.getTable().modules.ajax.hideLoader();
                    })
                    .fail(function(data){
                        console.error('failed');
                        cell.getTable().modules.ajax.hideLoader();
                    });
            }

            var inspectionListItemActionsFormatter = function(cell, formatterParams, onRendered) {
                if(cell.getRow().getData().id < 0) return null;

                var container = document.createElement('div');

                if(cell.getRow().getData().type == "{{ InspectionListItem::TYPE_HEAD }}")
                {
                    var openButton = document.createElement('a');
                    openButton.id = 'btnOpenInspectionListItem_' + cell.getRow().getData().id;
                    openButton.dataset.id = cell.getRow().getData().id;
                    openButton.dataset.description = cell.getRow().getData().description;
                    openButton.dataset.type = cell.getRow().getData().type;
                    openButton.dataset.depth = cell.getRow().getData().depth;
                    openButton.dataset.url = cell.getRow().getData().route_show;
                    openButton.dataset.back_url = cell.getRow().getData().route_back;
                    openButton.dataset.toggle = 'tooltip';
                    openButton.dataset.action = 'showInspectionListItemChildren';
                    openButton.title = "{{ trans('inspection.goToNextLevel') }}";
                    openButton.className = 'btn btn-xs btn-success';
                    openButton.innerHTML = '<i class="fa fa-arrow-right"></i>';
                    openButton.style['margin-right'] = '5px';

                    container.appendChild(openButton);
                }

                if(cell.getRow().getData().editable) {
                    var deleteButton = document.createElement('a');
                    deleteButton.id = 'btnDeleteInspectionListItem_' + cell.getRow().getData().id;
                    deleteButton.dataset.route_delete = cell.getRow().getData().route_delete;
                    deleteButton.dataset.toggle = 'modal';
                    deleteButton.dataset.target = '#yesNoModal';
                    deleteButton.dataset.delete_type = 'list_item';
                    deleteButton.title = "{{ trans('inspection.deleteInspectionList') }}";
                    deleteButton.className = 'btn btn-xs btn-danger';
                    deleteButton.innerHTML = '<i class="fa fa-trash"></i>';
                    deleteButton.style['margin-right'] = '5px';

                    deleteButton.addEventListener('click', function(e) {
                        e.preventDefault();

                        $('#yesNoModalMessage').html("{{ trans('inspection.allContentsWillBeDeleted') . ' ' . trans('general.sureToProceed') }}");
                        $('[data-action=actionYes]').data('route_delete', $(this).data('route_delete'));
                        $('[data-action=actionYes]').data('delete_type', $(this).data('delete_type'));
                    });

                    container.appendChild(deleteButton);
                }

				return container;
			}

            var listItemTypeFormatter = function(cell, formatterParams, onRendered){
                var data = cell.getRow().getData();
                var cellValue = null;

                switch(data.type.toString()) {
                    case "{{ InspectionListItem::TYPE_HEAD }}": 
                        cellValue = "{{ trans('inspection.head') }}";
                        break;
                    default:
                        cellValue = "{{ trans('inspection.item') }}";
                }

                return cellValue;
            };

            inspectionListItemsTable = new Tabulator('#inspection-list-items-table', {
                height:500,
                columns: [
                    { title: "id", field: 'id', visible:false },
                    { title: '<div class="text-center">{{ trans('general.no') }}</div>', field: 'indexNo', width: 60, 'align': 'center', headerSort:false },
                    { title: '<div class="text-left">{{ trans('general.description') }}</div>', field: 'description', editor: 'input', headerSort:false, editable:editCheck, headerFilter:"input" },
                    { title: '<div class="text-center">{{ trans('general.type') }}</div>', field: 'type', width: 120, 'align': 'center', editor:"select", formatter: listItemTypeFormatter, editorParams:{values: <?php echo json_encode($listItemTypes); ?>}, headerSort:false },
                    { title: '<div class="text-center">{{ trans('general.actions') }}</div>', width: 80, 'align': 'center', headerSort:false, formatter: inspectionListItemActionsFormatter },
                ],
                layout:"fitColumns",
                movableColumns:true,
                placeholder:"{{ trans('general.noDataAvailable') }}",
                columnHeaderSortMulti:false,
                cellEdited:function(cell) {
					var row = cell.getRow();
                    var item = row.getData();
                    var field = cell.getField();
                    var value = cell.getValue();
                    var table = cell.getTable();

                    var itemID = parseInt(item.id);
                    var url = (itemID > 0) ? "{{ $inspectionListItemUpdateRoute }}" : "{{ $inspectionListItemStoreRoute }}"

                    table.modules.ajax.showLoader();

                    var params = {
                		id: itemID,
                		field: field,
                		val: value,
                		inspectionListCategoryId: item.inspection_list_category_id, 
                        parentId: item.parent_id,
                		type: item.type,
                		_token:'{{{csrf_token()}}}',
                    };

                    if((field == 'type') && (itemID != -1)) {
                        $.ajax({
                            url: item.route_change_type_check,
                            method: 'GET',
                            data: params,
                            success: function (response) {
                                if(response.success) {
                                    if(response.warning) {
                                        $('#switchTypeYesNoModalMessage').html("{{ trans('inspection.changeCategoryTypeMessage') . ' ' . trans('general.sureToProceed') }}");
                                        $('#switchTypeYesNoModal [data-action=actionYes]').data('url', url);
                                        $('#switchTypeYesNoModal [data-action=actionYes]').data('params', params);
                                        $('#switchTypeYesNoModal [data-action=actionYes]').data('cell', cell);
                                        $('#switchTypeYesNoModal [data-dismiss=modal]').data('cell', cell);
                                        $('#switchTypeYesNoModal').modal('show');
                                    } else {
                                        executeRequest(url, params, cell);
                                    }
                                } else {
                                    displayWarning(response.errors);
                                    cell.restoreOldValue();
                                    cell.getTable().modules.ajax.hideLoader();
                                }
                            },
                            error: function (jqXHR, textStatus, errorThrown) {
                                // error
                            }
                        });
                    } else {
                        executeRequest(url, params, cell);
                    }
				},
            });

            $(document).on('click', '#switchTypeYesNoModal [data-action="actionYes"]', function(e) {
                executeRequest($(this).data('url'), $(this).data('params'), $(this).data('cell'));

                $('#switchTypeYesNoModal').modal('hide');
            });

            $(document).on('click', '#switchTypeYesNoModal [data-dismiss="modal"]', function(e) {
                var cell = $(this).data('cell');

                cell.restoreOldValue();
                cell.getTable().modules.ajax.hideLoader();;
            });

            

            $(document).on('click', '#yesNoModal [data-action="actionYes"]', function(e) {
				e.preventDefault();
				e.stopPropagation();

                var id         = $(this).data('id');
                var deleteType = $(this).data('delete_type');

				$.ajax({
                    url: $(this).data('route_delete'),
                    method: 'POST',
                    data: {
                        id: id,
                        _token: '{{ csrf_token() }}',
                    },
                    success: function (data) {
                        if (data.success) {
                            $('#yesNoModal').modal('hide');

                            if(deleteType == 'list') {
                                inspectionListsTable.setData();
                            }

                            if(deleteType == 'category') {
                                inspectionListCategoriesTable.setData();
                            }
                            
                            if(deleteType == 'list_item') {
                                inspectionListItemsTable.setData();
                            }

                            if(deleteType == 'additional_field') {
                                additionalFieldsTable.setData();
                            }
                        } else {
                            displayWarning(data.errors);
                            $('#yesNoModal').modal('hide');
                        }
                    },
                    error: function (request, status, error) {
                        // error
                    }
                });
            });

            var additionalFieldsActionsFormatter = function(cell, formatterParams, onRendered){
                var data = cell.getRow().getData();

                if(data.id < 0) return null;
                if(!data.editable) return null;

                var deleteButton = document.createElement('a');
                deleteButton.id = 'btnDeleteInspectionListItem_' + cell.getRow().getData().id;
                deleteButton.dataset.route_delete = cell.getRow().getData().route_delete;
				deleteButton.dataset.toggle = 'modal';
                deleteButton.dataset.target = '#yesNoModal';
                deleteButton.dataset.delete_type = 'additional_field';
				deleteButton.title = "{{ trans('inspection.deleteInspectionList') }}";
                deleteButton.className = 'btn btn-xs btn-danger';
                deleteButton.innerHTML = '<i class="fa fa-trash"></i>';
                deleteButton.style['margin-right'] = '5px';

                deleteButton.addEventListener('click', function(e) {
                    e.preventDefault();

                    $('#yesNoModalMessage').html("{{ trans('general.sureToProceed') }}");
                    $('[data-action=actionYes]').data('route_delete', $(this).data('route_delete'));
                    $('[data-action=actionYes]').data('delete_type', $(this).data('delete_type'));
                });

                return deleteButton;
            };

            $('#additionalFieldsModal').on('show.bs.modal', function(e) {
                var url = $(this).data('route_additonal_fields');

                additionalFieldsTable = new Tabulator('#additionalFieldsTable', {
                    height:500,
                    columns: [
                        { title: "id", field: 'id', visible:false },
                        { title: '<div class="text-center">{{ trans('general.no') }}</div>', field: 'indexNo', width: 60, 'align': 'center', headerSort:false },
                        { title: '<div class="text-left">{{ trans('inspection.fieldName') }}</div>', field: 'name', editor: 'input', headerSort:false, headerFilter:"input", editable:editCheck },
                        { title: '<div class="text-center">{{ trans('general.actions') }}</div>', width: 80, 'align': 'center', headerSort:false, formatter: additionalFieldsActionsFormatter },
                    ],
                    layout:"fitColumns",
                    movableColumns:true,
                    ajaxURL: url,
                    placeholder:"{{ trans('general.noDataAvailable') }}",
                    columnHeaderSortMulti:false,
                    cellEdited:function(cell) {
                        var row = cell.getRow();
                        var item = row.getData();
                        var field = cell.getField();
                        var value = cell.getValue();
                        var table = cell.getTable();

                        var itemID = parseInt(item.id);
                        var url = itemID > 0 ? "{{ $inspectionListCategoryAdditonalFieldUpdateRoute }}" : "{{ $inspectionListCategoryAdditonalFieldStoreRoute }}";

                        table.modules.ajax.showLoader();

                        var params = {
                            id: itemID,
                            field: field,
                            val: value,
                            inspectionListCategoryId: item.inspection_list_category_id, 
                            _token:'{{{csrf_token()}}}',
                        };

                        $.post(url, params)
                            .done(function(data){
                                if(data.success) {
                                    cell.getRow().update(data.item);

                                    if(itemID < 0) {
                                        cell.getTable().addRow(data.emptyRow);
                                    }

                                    cell.getRow().reformat();
                                } else {
                                    cell.restoreOldValue();
                                    displayWarning(data.errors);
                                }

                                table.modules.ajax.hideLoader();
                            })
                            .fail(function(data){
                                console.error('failed');
                                table.modules.ajax.hideLoader();
                            });
                    },
                });
            });

            @if(!isset($project))
                $(document).on('click', '[data-action=showInspectionListCategories]', function() {
                    $('#inspection-lists-table').hide();
                    inspectionListId = $(this).data('id');
                    $('#inspection-categories-table').show();
                    inspectionListCategoriesTable.setData($(this).data('url'));
                    breadcrumbs.addItem($(this).data('name'), ('inspection-category_' + inspectionListId), function() {
                        $('#inspection-lists-table').show();
                        $('#inspection-categories-table').hide();
                    });
                });
            @endif

            $(document).on('click', '[data-action=showInspectionCategoryChildren]', function() {
                inspectionCategoryId = $(this).data('id');

                var type  = $(this).data('type');
                var depth = $(this).data('depth');

                if(type == "{{ InspectionListCategory::TYPE_INSPECTION_CATEGORY }}") {
                    inspectionListCategoriesTable.setData($(this).data('url'));
                } else {
                    $('#inspection-categories-table').hide();
                    $('#inspection-list-items-table').show();
                    inspectionListItemsTable.setData($(this).data('url'));
                }

                $('#btnCopyFromMasterInspectionList').hide();

                var routeBack = (typeof $(this).data('back_url') !== 'undefined') ? $(this).data('back_url') : null;
 
                breadcrumbs.addItem($(this).data('name'), ('inspection-category_children' + inspectionCategoryId), function() {
                    if(routeBack) {
                        inspectionListCategoriesTable.setData(routeBack);
                    }

                    if(type == "{{ InspectionListCategory::TYPE_INSPECTION_LIST }}") {
                        $('#inspection-categories-table').show();
                        $('#inspection-list-items-table').hide();
                    }

                    if(depth == 0) {
                        $('#btnCopyFromMasterInspectionList').show();
                    }
                });
            });

            $(document).on('click', '[data-action="showInspectionListItemChildren"]', function() {
                var inspectionListItemId = $(this).data('id');
                var url                  = $(this).data('url');

                inspectionListItemsTable.setData(url);

                var routeBack = (typeof $(this).data('back_url') !== 'undefined') ? $(this).data('back_url') : null;

                breadcrumbs.addItem($(this).data('description'), ('inspection-list-item_children' + inspectionListItemId), function() {
                    if(routeBack) {
                        inspectionListItemsTable.setData(routeBack);
                    }
                });
            });

            @if(isset($project))
                var inspectionListSelectionBreadcrumb = new DynamicBreadcrumbs('#inspectionListSelectionBreadCrumb');
                inspectionListSelectionBreadcrumb.addItem('<i class="fa fa-home"></i>', 'inspection-list-selection');

                var inspectionListSelectionActionsFormatter = function(cell, formatterParams, onRendered) {
                    var openButton = document.createElement('a');
                    openButton.id = 'btnOpenInspectionListSelection_' + cell.getRow().getData().id;
                    openButton.dataset.id = cell.getRow().getData().id;
                    openButton.dataset.name = cell.getRow().getData().name;
                    openButton.dataset.url = cell.getRow().getData().route_show;
                    openButton.dataset.toggle = 'tooltip';
                    openButton.dataset.action = 'showInspectionListCategoriesSelection';
                    openButton.title = "{{ trans('inspection.goToNextLevel') }}";
                    openButton.className = 'btn btn-xs btn-success';
                    openButton.innerHTML = '<i class="fa fa-arrow-right"></i>';

                    return openButton;
                }

                $("#masterInspectionListSelectionModal").on('shown.bs.modal', function(e) {
                    masterInspectionListSelectionTable = new Tabulator('#masterInspectionListSelectionTable', {
                        height:450,
                        columns: [
                            { title:"{{ trans('general.no') }}", formatter:"rownum", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false },
                            { title: '<div class="text-left">{{ trans('general.name') }}</div>', field: 'name', headerSort:false, headerFilter:"input" },
                            { title: '<div class="text-center">{{ trans('general.actions') }}</div>', width: 80, 'align': 'center', headerSort:false, formatter: inspectionListSelectionActionsFormatter },
                        ],
                        layout:"fitColumns",
                        movableColumns:true,
                        ajaxURL : "{{ route('master.inspection.lists.selection.get') }}",
                        placeholder:"{{ trans('general.noDataAvailable') }}",
                        columnHeaderSortMulti:false,
                    });
                });

                var inspectionListCategoriesSelectionActionsFormatter = function(cell, formatterParams, onRendered) {
                    if(cell.getRow().getData().type == "{{ InspectionListCategory::TYPE_INSPECTION_LIST }}") return null;

                    var openButton = document.createElement('a');
                    openButton.id = 'btnOpenInspectionListCategorySelection_' + cell.getRow().getData().id;
                    openButton.dataset.id = cell.getRow().getData().id;
                    openButton.dataset.name = cell.getRow().getData().name;
                    openButton.dataset.url = cell.getRow().getData().route_show;
                    openButton.dataset.back_url = cell.getRow().getData().route_back;
                    openButton.dataset.toggle = 'tooltip';
                    openButton.dataset.action = 'showInspectionListCategoryChildrenSelection';
                    openButton.title = "{{ trans('inspection.goToNextLevel') }}";
                    openButton.className = 'btn btn-xs btn-success';
                    openButton.innerHTML = '<i class="fa fa-arrow-right"></i>';

                    return openButton;
                }

                masterInspectionListCategoriesSelectionTable = new Tabulator('#masterInspectionListCategoriesSelectionTable', {
                    height:450,
                    columns: [
                        { formatter:"rowSelection", titleFormatter:"rowSelection", width:30, align:"center", cssClass:"text-center text-middle", headerSort:false },
                        { title:"{{ trans('general.no') }}", formatter:"rownum", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false },
                        { title: '<div class="text-left">{{ trans('general.name') }}</div>', field: 'name', headerSort:false, headerFilter:"input" },
                        { title: '<div class="text-center">{{ trans('general.actions') }}</div>', width: 80, 'align': 'center', headerSort:false, formatter: inspectionListCategoriesSelectionActionsFormatter },
                    ],
                    layout:"fitColumns",
                    movableColumns:true,
                    placeholder:"{{ trans('general.noDataAvailable') }}",
                    columnHeaderSortMulti:false,
                    rowSelectionChanged:function(data, rows){
                        $('#btnSaveSelectedMasterInspectionListCategories').prop('disabled', (data.length == 0));
                    },
                });

                $(document).on('click', '[data-action=showInspectionListCategoriesSelection]', function() {
                    var id = $(this).data('id');

                    $('#masterInspectionListSelectionTable').hide();
                    $('#masterInspectionListCategoriesSelectionTable').show();
                    $('#btnSaveSelectedMasterInspectionListCategories').show();

                    masterInspectionListCategoriesSelectionTable.setData($(this).data('url'));

                    inspectionListSelectionBreadcrumb.addItem($(this).data('name'), ('inspection-list_' + id + '_selection'), function() {
                        $('#masterInspectionListSelectionTable').show();
                        $('#masterInspectionListCategoriesSelectionTable').hide();
                        $('#btnSaveSelectedMasterInspectionListCategories').hide();
                    });
                });

                $(document).on('click', '[data-action=showInspectionListCategoryChildrenSelection]', function() {
                    var id = $(this).data('id');
                    var url = $(this).data('url');
                    var routeBack = $(this).data('back_url');

                    masterInspectionListCategoriesSelectionTable.setData($(this).data('url'));

                    inspectionListSelectionBreadcrumb.addItem($(this).data('name'), ('inspection-category_' + id + '_selection'), function() {
                        if(routeBack) {
                            masterInspectionListCategoriesSelectionTable.setData(routeBack);
                        }
                    });
                });

                $('#btnSaveSelectedMasterInspectionListCategories').on('click', function(e) {
                    e.preventDefault();

                    app_progressBar.toggle();

                    var selectedInspectionListCategories   = masterInspectionListCategoriesSelectionTable.getSelectedRows();
                    var selectedInspectionListCategoryIds  = [];
                    
                    selectedInspectionListCategories.forEach(function(el) {
                        selectedInspectionListCategoryIds.push(el.getData().id);
                    });

                    var url    = "{{ route('clone.master.inspection.list.categories', [$project->id]) }}"; 
                    var params = {
                        projectId : "{{ $project->id }}",
                        selectedInspectionListCategoryIds: selectedInspectionListCategoryIds,
                        _token: '{{{csrf_token()}}}',
                    };

                    $.post(url, params)
                        .done(function(data) {
                            if(data.success) {
                                app_progressBar.maxOut();
                                app_progressBar.hide();

                                $("#masterInspectionListSelectionModal").modal('hide');
                                inspectionListCategoriesTable.setData();
                            }
                        })
                        .fail(function(data){
                            console.error('failed');
                        });
                });
            @endif

            function displayWarning(message) {
                $('#txtWarningMessage').html(message);
                $('#warningModal').modal('show');
            }
        });
    </script>
@endsection