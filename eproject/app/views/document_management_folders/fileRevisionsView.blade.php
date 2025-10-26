<div class="modal-header">
    <h4 class="modal-title">{{ trans('documentManagementFolders.fileRevisions') }}</h4>
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
</div>
<div class="modal-body no-padding">
    <table id="fileRevisionsTable" class="table  smallFont" cellspacing="0" width="100%">
        <thead>
        <tr>
            <th class="hasinput" style="width:35%;">
                <input type="text" class="form-control" placeholder="Filter File Name" />
            </th>
            <th class="hasinput" style="width:25%;">
                <input type="text" class="form-control" placeholder="Filter Description" />
            </th>
            <th class="hasinput" style="width:6%">
                <input type="text" class="form-control" placeholder="Filter Revision" />
            </th>
            <th class="hasinput icon-addon">
                <input id="file_revisions_dateselect_filter" type="text" placeholder="Filter Date" class="form-control datepicker" data-dateformat="dd/mm/yy">
                <label for="file_revisions_dateselect_filter" class="glyphicon glyphicon-calendar no-margin padding-top-15" rel="tooltip" title="" data-original-title="Filter Date"></label>
            </th>
            <th class="hasinput" style="width:20%;">
                <input type="text" class="form-control" placeholder="Filter Issued By" />
            </th>
        </tr>
        <tr>
            <th data-class="expand">{{ trans('documentManagementFolders.filename') }}</th>
            <th data-hide="phone">{{ trans('documentManagementFolders.description') }}</th>
            <th>{{ trans('documentManagementFolders.revision') }}</th>
            <th data-hide="phone">{{ trans('documentManagementFolders.date') }}</th>
            <th data-hide="phone,tablet">{{ trans('documentManagementFolders.issuedBy') }}</th>
        </tr>
        </thead>
    </table>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-default" data-dismiss="modal">{{ trans('documentManagementFolders.close') }}</button>
</div>

<script type="application/javascript">
$(document).ready(function() {
    'use strict';

    pageSetUp();

    /* COLUMN FILTER  */
    var responsiveHelper_file_revisions_table = undefined;
    var breakpointDefinition = {
        tablet : 1024,
        phone : 480
    };

    var otable = $('#fileRevisionsTable').DataTable({
        "ajax": "{{route("projectDocument.fileRevisionList", array($project->id, $file->id))}}",
        "columns": [
            {
                "data": "filename",
                "mRender": function (data, type, row) {
                    if(row.id > 0){
                        var url = "{{route("projectDocument.fileDownload", array($project->id, 'fileID'))}}";
                        url = url.replace('fileID', row.id);
                        return '<a title="{{ trans('documentManagementFolders.clickToDownload') }}" href="'+url+'">'+data+'</a>';
                    }
                    return "&nbsp;";
                }
            },
            { "data": "description" },
            {
                "data": "revision",
                "class": "text-center"
            },
            { "data": "date_issued", "class": "text-center" },
            { "data": "issued_by"}
        ],
        "ordering": false,
        "iDisplayLength": 10,
        "sDom": "<'dt-toolbar'<'col-xs-12 col-sm-6 hidden-xs'><'col-sm-6 col-xs-12 hidden-xs'<'toolbar'>>>"+
        "t"+
        "<'dt-toolbar-footer'<'col-sm-6 col-xs-12 hidden-xs'i><'col-xs-12 col-sm-6'p>>",
        "autoWidth" : true,
        "preDrawCallback" : function() {
            // Initialize the responsive datatables helper once.
            if (!responsiveHelper_file_revisions_table) {
                responsiveHelper_file_revisions_table = new ResponsiveDatatablesHelper($('#fileRevisionsTable'), breakpointDefinition);
            }
        },
        "rowCallback" : function(nRow) {
            responsiveHelper_file_revisions_table.createExpandIcon(nRow);
        },
        "drawCallback" : function() {
            responsiveHelper_file_revisions_table.respond();
        }
    });

    // Apply the filter
    $("#fileRevisionsTable thead th input[type=text]").on( 'keyup change', function () {
        otable
        .column( $(this).parent().index()+':visible' )
        .search( this.value )
        .draw();
    });
});
</script>