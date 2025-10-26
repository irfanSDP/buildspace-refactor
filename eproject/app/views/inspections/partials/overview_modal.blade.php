<div class="modal" id="inspections-overview-modal" tabindex="-1" role="dialog" aria-labelledby="overviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">{{ trans('inspection.overview') }}</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                    &times;
                </button>
            </div>
            <div class="modal-body no-padding">
                <div id="inspections-overview-table"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success" data-action="toggle-view">@{{ toggleButtonLabel }}</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">{{{ trans('forms.close') }}}</button>
            </div>
        </div>
    </div>
</div>
<?php use PCK\Inspections\InspectionListItem; ?>
@include('templates.attachmentsListModal', array('modalId' => 'overview-attachments-modal', 'tableId' => 'overviewAttachmentsTable'))

<script>
    var inspectionOverviewDescriptionFormatter = function(cell, formatterParams, onRendered) {
        var rowData     = cell.getRow().getData();
        var paddingLeft = rowData.depth * 16;
        var style       = 'padding-left: ' + paddingLeft + 'px;';;

        if(rowData.type == "{{ InspectionListItem::TYPE_HEAD }}") {
            style += 'font-weight: bold;';
        }

        return `<span style="${ style }">${ rowData.description }</span>`;
    };

    var inspectionOverviewTableColumnsByInspection = [
        {title:"{{ trans('general.no') }}", formatter:"rownum", width:60, hozAlign:'center', cssClass:"text-center text-middle", frozen:true, headerSort:false},
        {title:"{{ trans('requestForInspection.description') }}", field:"description", minWidth: 380, hozAlign:"left", frozen:true, headerSort:false, formatter: inspectionOverviewTableColumnsByInspection },
        @foreach($requestForInspection->inspections()->where('status', '!=', \PCK\Inspections\Inspection::STATUS_DRAFT)->get()->reverse() as $inspectionObject)
            {
                title:'<a href="{{ route("inspection.submit.form", array($project->id, $requestForInspection->id, $inspectionObject->id)) }}" class="btn btn-xs btn-success">{{ trans("inspection.inspectionX", array("no" => $inspectionObject->revision+1)) }}</a>',
                columns: [
                    @foreach($project->inspectionRoles()->orderBy('created_at', 'asc')->get() as $role)
                    {
                        title:"{{{ $role->name }}}",
                        columns: [
                            {title:"{{ trans('inspection.progress') }} (%)", field:"progress_status-{{ $inspectionObject->id }}-{{ $role->id }}", minWidth: 30, width:110, hozAlign:"right", headerSort:false},
                            {title:"{{ trans('inspection.remarks') }}", field:"remarks-{{ $inspectionObject->id }}-{{ $role->id }}", width: 280, hozAlign:"left", headerSort:false},
                            {title:"{{ trans('forms.attachments') }}", minWidth: 30, width:110, hozAlign:"center", headerSort:false, formatter: function(cell, formatterParams, onRendered){
                                if(cell.getRow().getData().type == "{{ InspectionListItem::TYPE_HEAD }}") return null;

                                var rowData   = cell.getRow().getData();
                                var innerHtml = `<i class="fa fa-paperclip"></i>&nbsp;&nbsp;(${ rowData['attachmentCount-{{ $inspectionObject->id }}-{{ $role->id }}'] })`;

                                return `<button type="button" class="btn btn-xs btn-info" data-toggle="modal" data-target="#overview-attachments-modal" data-action="item-attachments-list" data-uploads-list="${ rowData['route:getUploads-{{ $inspectionObject->id }}-{{ $role->id }}'] }">${ innerHtml }</button>`;
                            }},
                        ]
                    },
                    @endforeach
                ],
            },
        @endforeach
    ];

    var inspectionOverviewTableColumnsByRole = [
        {title:"{{ trans('general.no') }}", formatter:"rownum", width:60, hozAlign:'center', cssClass:"text-center text-middle", frozen:true, headerSort:false},
        {title:"{{ trans('requestForInspection.description') }}", field:"description", minWidth: 380, hozAlign:"left", frozen:true, headerSort:false, formatter: inspectionOverviewTableColumnsByInspection },
        @foreach($project->inspectionRoles()->orderBy('created_at', 'asc')->get() as $role)
            {
                title:"{{{ $role->name }}}",
                columns: [
                    @foreach($requestForInspection->inspections()->where('status', '!=', \PCK\Inspections\Inspection::STATUS_DRAFT)->get()->reverse() as $inspectionObject)
                    {
                        title:'<a href="{{ route("inspection.submit.form", array($project->id, $requestForInspection->id, $inspectionObject->id)) }}" class="btn btn-xs btn-success">{{ trans("inspection.inspectionX", array("no" => $inspectionObject->revision+1)) }}</a>',
                        columns: [
                            {title:"{{ trans('inspection.progress') }} (%)", field:"progress_status-{{ $inspectionObject->id }}-{{ $role->id }}", minWidth: 30, width:110, hozAlign:"right", headerSort:false},
                            {title:"{{ trans('inspection.remarks') }}", field:"remarks-{{ $inspectionObject->id }}-{{ $role->id }}", width: 280, hozAlign:"left", headerSort:false},
                            {title:"{{ trans('forms.attachments') }}", minWidth: 30, width:110, hozAlign:"center", headerSort:false, formatter: function(cell, formatterParams, onRendered){
                                if(cell.getRow().getData().type == "{{ InspectionListItem::TYPE_HEAD }}") return null;

                                var rowData   = cell.getRow().getData();
                                var innerHtml = `<i class="fa fa-paperclip"></i>&nbsp;&nbsp;(${ rowData['attachmentCount-{{ $inspectionObject->id }}-{{ $role->id }}'] })`;

                                return `<button type="button" class="btn btn-xs btn-info" data-toggle="modal" data-target="#overview-attachments-modal" data-action="item-attachments-list" data-uploads-list="${ rowData['route:getUploads-{{ $inspectionObject->id }}-{{ $role->id }}'] }">${ innerHtml }</button>`;
                            }},
                        ]
                    },
                    @endforeach
                ],
            },
        @endforeach
    ];

    var inspectionsOverviewTable = new Tabulator('#inspections-overview-table', {
        minHeight:280,
        maxHeight:450,
        layout:"fitColumns",
        placeholder: "{{ trans('inspection.listEmpty') }}",
        columns:inspectionOverviewTableColumnsByInspection,
    });
    $('#inspections-overview-modal').on('show.bs.modal', function(){
        inspectionsOverviewTable.setData("{{ route('inspection.overview', array($project->id, $requestForInspection->id)) }}");
    });
    $('#inspections-overview-modal').on('shown.bs.modal', function(){
        inspectionsOverviewTable.redraw(true);
    });

    var inspectionOverViewModalVue = new Vue({
        el: '#inspections-overview-modal',
        data: {
            toggleButtonLabel: "{{ trans('inspection.groupByRole') }}",
            mode: 'byInspection',
        },
        methods: {
            toggleViewMode: function(){
                if(this.mode == 'byInspection'){
                    inspectionsOverviewTable.setColumns(inspectionOverviewTableColumnsByRole);
                    this.toggleButtonLabel = "{{ trans('inspection.groupByInspection') }}";
                    this.mode = 'byRole';
                }
                else{
                    inspectionsOverviewTable.setColumns(inspectionOverviewTableColumnsByInspection);
                    this.toggleButtonLabel = "{{ trans('inspection.groupByRole') }}";
                    this.mode = 'byInspection';
                }
                inspectionsOverviewTable.redraw();
            }
        }
    });
    $('#inspections-overview-modal').on('click', '[data-action=toggle-view]', function(){
        inspectionOverViewModalVue.toggleViewMode();
    });

    var overviewItemAttachmentsTable = new Tabulator("#overviewAttachmentsTable", {
        layout: "fitColumns",
        placeholder: "{{ trans('general.noAttachments') }}",
        columns: columns_overviewAttachmentsTable
    });

    $('#inspections-overview-table').on('click', '[data-action=item-attachments-list]', function(){
        overviewItemAttachmentsTable.setData($(this).data('uploads-list'));
    });
</script>