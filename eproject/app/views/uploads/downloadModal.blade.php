<!--
Usage:
In your element, include the following attributes:
1. data-toggle="modal"
2. data-target="#downloadModal"
3. data-action="get-downloads"
4. data-get-downloads: route for obtaining the list of downloadable files.
    Returns an array where each element is in the format:
        [
            filename
            download_url
            uploaded_by
            uploaded_at
        ]
-->
<?php $modalId = isset($modalId) ? $modalId : 'downloadModal' ?>
<?php $tableId = isset($tableId) ? $tableId : 'downloadModalTable' ?>
<?php $title = isset($title) ? $title : '' ?>

<div class="modal scrollable-modal" id="{{{ $modalId }}}" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" data-type="formModal"
     aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="myModalLabel">
                    <i class="fa fa-paperclip"></i>
                    {{{ trans('general.attachments') }}}
                </h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                    &times;
                </button>
            </div>

            <div class="modal-body">
                <h6 data-type="title">
                    {{{ $title }}}
                </h6>
                <div id="{{ $tableId }}"></div>
            </div>
        </div>
    </div>
</div>

<script>
    var {{ $tableId }}_table = new Tabulator("#{{ $tableId }}", {
        layout: "fitColumns",
        placeholder: "{{ trans('general.noAttachments') }}",
        columns: [
            {title:"{{ trans('general.no') }}", cssClass:"text-center", width: 15, headerSort:false, formatter:"rownum"},
            {title:"{{ trans('general.name') }}", cssClass:"text-left", minWidth: 400, headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter,
                formatterParams: {
                    innerHtml: function(rowData){
                        return rowData.filename;
                    },
                    tag: 'a',
                    attributes: {'download': ''},
                    rowAttributes: {'href': 'download_url'}
                }
            },
            {title:"{{ trans('files.uploadedBy') }}", field:'uploaded_by', minWidth: 150, cssClass:"text-center", headerSort:false},
            {title:"{{ trans('files.uploadedAt') }}", field:'uploaded_at', minWidth: 150, cssClass:"text-center", headerSort:false},
        ]
    });

    $(document).on('click', '[data-action=get-downloads]', function(){
        {{ $tableId }}_table.setData($(this).data('get-downloads'));
    });
</script>