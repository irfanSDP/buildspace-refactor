<?php $folderRoute = 'projects.tenderDocument.myFolder'?>

<?php $hasRole = $user->isSuperAdmin() || $user->hasCompanyProjectRole($project, $project->getCallingTenderRole()); ?>
<?php $isEditor = $user->isEditor($project); ?>

<?php $showUploadButton = $hasRole && $isEditor ? true : false; ?>

@extends('layout.main')

@section('css')
    @if($showUploadButton)
        <!-- CSS to style the file input field as button and adjust the Bootstrap progress bars -->
        <link rel="stylesheet" href="{{ asset('css/jquery.fileupload.css') }}">
        <link rel="stylesheet" href="{{ asset('css/jquery.fileupload-ui.css') }}">
    @endif
@endsection

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{ link_to_route('projects.show', str_limit($project->title, 50), array($project->id)) }}</li>
        <li>{{ link_to_route('projects.tenderDocument.index', 'Tender Documents', array($project->id)) }}</li>

        @foreach($node->getAncestorsAndSelf() as $ancestor)
            @if ( $ancestor->id !== $node->id )
                <li>{{ link_to_route($folderRoute, str_limit($ancestor->name, 20), array($project->id, $ancestor->id)) }}</li>
            @endif
        @endforeach

        <li>{{{ str_limit($node->name, 20) }}}</li>
    </ol>
@endsection

@section('content')

    <div class="row">
        <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
            <h1 class="page-title txt-color-blueDark">
                <i class="fa fa-folder"></i> {{{$node->name}}}
            </h1>
        </div>
        <div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">
            @include('tender_document_folders.partials.folderActionMenu')
        </div>
    </div>
    
    <div class="jarviswidget " id="wid-id-projectDocumentView"
         data-widget-colorbutton="false" data-widget-editbutton="false" data-widget-togglebutton="false"
         data-widget-deletebutton="false" data-widget-fullscreenbutton="false" data-widget-custombutton="false"
         data-widget-sortable="false">
        <header>
            <span class="widget-icon"> <i class="fa fa-file-alt"></i> </span>

            <h2>{{ trans('documentManagementFolders.documents') }}</h2>
        </header>

        <!-- widget div-->
        <div>

            <!-- widget edit box -->
            <div class="jarviswidget-editbox"></div>
            <!-- end widget edit box -->

            <!-- widget content -->
            <div class="widget-body no-padding">
                <table id="uploadedFilesTable" class="table  smallFont" cellspacing="0"
                       width="100%">
                    <thead>
                    <tr>
                        <th class="hasinput" style="width:35%;">
                            <input type="text" class="form-control" placeholder="{{ trans('documentManagementFolders.filename') }}"/>
                        </th>
                        <th class="hasinput" style="width:25%">
                            <input type="text" class="form-control" placeholder="{{ trans('documentManagementFolders.description') }}"/>
                        </th>
                        <th class="hasinput" style="width:40px;">
                            <input type="text" class="form-control" placeholder="{{ trans('documentManagementFolders.revision') }}"/>
                        </th>
                        <th class="hasinput icon-addon" style="">
                            <input id="dateselect_filter" type="text" placeholder="{{ trans('documentManagementFolders.date') }}"
                                   class="form-control datepicker" data-dateformat="dd/mm/yy">
                            <label for="dateselect_filter" class="glyphicon glyphicon-calendar no-margin padding-top-15"
                                   rel="tooltip" title="" data-original-title="{{ trans('documentManagementFolders.date') }}"></label>
                        </th>
                        <th class="hasinput" style="width:20%;">
                            <input type="text" class="form-control" placeholder="{{ trans('documentManagementFolders.issuedBy') }}"/>
                        </th>                       
                        @if($showUploadButton)
                            <th style="width:5%"></th>
                            <th style="width:5%;"></th>
                            <th style="width:5%;"></th>
                        @endif
                    </tr>
                    <tr>
                        <th data-class="expand">{{ trans('documentManagementFolders.filename') }}</th>
                        <th data-hide="phone">{{ trans('documentManagementFolders.description') }}</th>
                        <th>{{ trans('documentManagementFolders.revision') }}</th>
                        <th data-hide="phone">{{ trans('documentManagementFolders.date') }}</th>
                        <th data-hide="phone,tablet">{{ trans('documentManagementFolders.issuedBy') }}</th>
                        @if($showUploadButton)
                            <th>{{ trans('documentManagementFolders.log') }}</th>
                            <th>{{ trans('documentManagementFolders.edit') }}</th>
                            <th>{{ trans('files.delete') }}</th>
                        @endif
                    </tr>
                    </thead>
                </table>
            </div>
            <!-- end widget content -->

        </div>
        <!-- end widget div -->

    </div>

    <div class="jarviswidget " id="wid-id-projectFolderView" data-widget-colorbutton="false"
         data-widget-editbutton="false" data-widget-togglebutton="false" data-widget-deletebutton="false"
         data-widget-fullscreenbutton="false" data-widget-custombutton="false" data-widget-sortable="false">
        <header>
            <span class="widget-icon"> <i class="fa fa-folder"></i> </span>

            <h2>{{trans('documentManagementFolders.folders')}}</h2>
        </header>

        <!-- widget div-->
        <div>
            <!-- widget edit box -->
            <div class="jarviswidget-editbox"></div>
            <!-- end widget edit box -->

            <!-- widget content -->
            <div class="widget-body">
                <div class="row">
                    @foreach($children as $child)
                        <div class="col col-xs-12 col-sm-6 col-md-4 col-lg-4">
                            <h1 class="page-title txt-color-blueDark">
                                <i class="fa fa-folder"></i>
                                <span class="glyphicon-class" style="font-size:13px;">
                                    {{ link_to_route($folderRoute, $child['name'], array($project->id, $child['id'])) }}
                                </span>
                            </h1>
                        </div>
                    @endforeach
                </div>
            </div>
            <!-- end widget content -->

        </div>
        <!-- end widget div -->

    </div>

    @if($showUploadButton)
        <!-- Upload Modal -->
        @include('tender_document_folders.partials.uploadModal')

        <!-- edit uploaded file Modal -->
        @include('tender_document_folders.partials.editUploadedFileModal')

        <!-- Revisions Modal -->
        @include('tender_document_folders.partials.revisionsModal')

        <!-- download log modal -->
        @include('tender_document_folders.partials.documentDownloadLogModal')

    @endif

    @if($showUploadButton)

        @include('layout.file_upload.template-upload')

    @endif

@endsection

@section('js')
    <script src="{{ asset('js/datatables_all_plugins.min.js') }}"></script>

    @if($showUploadButton)
        <script src="{{ asset('js/jquery_file_upload/tmpl.min.js') }}"></script>
        <script src="{{ asset('js/jquery_file_upload/load-image.all.min.js') }}"></script>
        <script src="{{ asset('js/jquery_file_upload/canvas-to-blob.min.js') }}"></script>
        <script src="{{ asset('js/jquery_file_upload/jquery.iframe-transport.js') }}"></script>
        <script src="{{ asset('js/jquery_file_upload/jquery.fileupload.js') }}"></script>
        <script src="{{ asset('js/jquery_file_upload/jquery.fileupload-process.js') }}"></script>
        <script src="{{ asset('js/jquery_file_upload/jquery.fileupload-image.js') }}"></script>
        <script src="{{ asset('js/jquery_file_upload/jquery.fileupload-audio.js') }}"></script>
        <script src="{{ asset('js/jquery_file_upload/jquery.fileupload-video.js') }}"></script>
        <script src="{{ asset('js/jquery_file_upload/jquery.fileupload-validate.js') }}"></script>
        <script src="{{ asset('js/jquery_file_upload/jquery.fileupload-ui.js') }}"></script>
    @endif

    <script>
        $(document).ready(function() {
            'use strict';

            pageSetUp();

            var token = $('meta[name=_token]').attr("content");

            @if($showUploadButton)
                $('#uploadDocumentModal').modal({
                keyboard: false,
                show: false
            });

            $('#editUploadedFileModal').modal({
                keyboard: false,
                show: false
            });

            $('#editDocumentForm-revisionTo').select2({
                placeholder: "{{ trans('documentManagementFolders.selectFileToBeRevised') }}",
                theme: 'bootstrap',
                allowClear: true
            });
            @endif

            /* COLUMN FILTER  */
            var responsiveHelper_uploaded_files_table = undefined;
            var breakpointDefinition = {
                tablet : 1024,
                phone : 480
            };

            var otable = $('#uploadedFilesTable').DataTable({
                "ajax": "{{route("tenderDocument.fileList", array($project->id, $node->id))}}",
                "columns": [
                    {
                        "data": "filename",
                        "mRender": function (data, type, row) {
                            if(row.id > 0){
                                var url = "{{route("tenderDocument.fileDownload", array($project->id, 'fileID'))}}";
                                url = url.replace('fileID', row.id);
                                url += "?" + generateUUID();
                                if(row.physicalFileExists){
                                    @if($canDownload)
                                    return '<a title="{{ trans('documentManagementFolders.clickToDownload') }}" href="'+url+'" data-file-id="' + row.id + '">'+data+'</a>';
                                    @else
                                    return data;
                                    @endif
                                }
                                else{
                                    return '<a href="#" data-action="delete" data-id="'+row.id+'" data-type="deadLink"><span style="text-decoration: line-through">'+data+'</span></a>';
                                }
                            }
                            return "&nbsp;";
                        }
                    },
                    { "data": "description" },
                    {
                        "data": "revision",
                        "class": "text-center",
                        "mRender": function (data, type, row) {
                            @if($showUploadButton)
                            if(data > 0){
                                var url = "{{route("tenderDocument.fileRevisions", array($project->id, 'fileID'))}}";
                                url = url.replace('fileID', row.id);
                                return '<a title="{{ trans('documentManagementFolders.clickToViewListOfRevisions') }}" data-toggle="modal" href="'+url+'" data-target="#revisionsModal">'+data+'</a>';
                            }
                            @endif
                                    return data;
                        }
                    },
                    { "data": "date_issued", "class": "text-center" },
                    { "data": "issued_by"},
                    @if($showUploadButton)
                    {
                        "orderable":      false,
                        "data":           null,
                        "class": "text-center",
                        "mRender" : function (data, type, row) {
                            return '<button data-action="viewLog" data-id="' + row.id + '" data-downloadlogroute="' + row.fileDownloadLogRoute + '" class="btn btn-sm btn-success"><i class="fa fa-history" aria-hidden="true"></i></i></button>'
                        }
                    }
                    ,{
                        "orderable":      false,
                        "data":           null,
                        "class": "text-center",
                        "mRender": function (data, type, row) {
                            if(row.id > 0){
                                if(row.physicalFileExists){
                                    return '<button data-action="edit" class="btn btn-sm btn-primary"><i class="fa fa-edit"></i></button>';
                                }
                                else{
                                    return '<button data-type="deadLink" data-id="'+row.id+'" class="btn btn-sm btn-primary"><i class="fa fa-edit"></i></button>';
                                }
                            }
                            return "&nbsp;";
                        }
                    }
                    ,{
                        "orderable":      false,
                        "data":           null,
                        "class": "text-center",
                        "defaultContent": '<button data-action="delete" class="btn btn-sm btn-danger"><i class="fa fa-times"></i></button>'
                    }
                    @endif
                ],
                "ordering": false,
                "iDisplayLength": 10,
                "sDom": "<'dt-toolbar'<'col-xs-12 col-sm-6 hidden-xs'f><'col-sm-6 col-xs-12 hidden-xs'<'toolbar'>>r>"+
                "t"+
                "<'dt-toolbar-footer'<'col-sm-6 col-xs-12 hidden-xs'i><'col-xs-12 col-sm-6'p>>",
                "autoWidth" : true,
                "preDrawCallback" : function() {
                    // Initialize the responsive datatables helper once.
                    if (!responsiveHelper_uploaded_files_table) {
                        responsiveHelper_uploaded_files_table = new ResponsiveDatatablesHelper($('#uploadedFilesTable'), breakpointDefinition);
                    }
                },
                "rowCallback" : function(nRow) {
                    responsiveHelper_uploaded_files_table.createExpandIcon(nRow);
                },
                "drawCallback" : function(oSettings) {
                    responsiveHelper_uploaded_files_table.respond();
                }
            });

            // generate new UUID
            function generateUUID() {
                return Math.random().toString(36).substring(2, 15) + Math.random().toString(36).substring(2, 15);
            }

            // replaces UUID on the URL of downloaded documents
            $(document).on('click', 'a[data-file-id]', function(e) {
                var currentURL = $(this).prop('href');
                var startingIndex = currentURL.lastIndexOf('?') + 1;
                var currentUUID = currentURL.substring(startingIndex, currentURL.length);
                var newUUID = generateUUID();
                var newURL = currentURL.replace(currentUUID, newUUID);
                e.target.href = newURL;
            });

            @if($showUploadButton)
                $("div.toolbar").html('<div class="text-right"><a class="btn btn-primary" href="#" onclick="uploadTenderDocument();return false;"><i class="fa fa-upload"></i> {{ trans('documentManagementFolders.upload') }}</a></div>');
            @endif

            // Apply the filter
            $("#uploadedFilesTable thead th input[type=text]").on( 'keyup change', function () {
                otable
                        .column( $(this).parent().index()+':visible' )
                        .search( this.value )
                        .draw();
            });

            $('#uploadedFilesTable tbody').on('click', 'button[data-action=viewLog]', function () {
                var fileDownloadLogRoute = $(this).data('downloadlogroute');
                var downloadLogTable = $('#tenderDocumentDownloadLogTable').DataTable({
                    "ajax": fileDownloadLogRoute,
                    "columns": [
                        { "data": "company" },
                        { "data": "user" },
                        { "data": "dateAndTime" }
                    ],
                    "language": {
                        "emptyTable": "{{ trans('documentManagementFolders.noDownloadsSoFar') }}",
                    }
                });

                $('#tenderDocumentDownloadLogModal').modal('show');

                downloadLogTable.destroy();
            });

            @if($showUploadButton)
                $('#uploadedFilesTable tbody').on( 'click', 'button[data-action=edit]', function () {
                var data = otable.row( $(this).parents('tr') ).data();
                var url = "{{route("tenderDocument.fileInfo", array($project->id, 'fileID'))}}";
                url = url.replace('fileID', data.id);

                $('#editDocumentForm-filename').val("");
                $('#editDocumentForm-description').val("");
                $('#editDocumentForm-date_issued').text("");
                $('#editDocumentForm-issued_by').text("");
                $('#editDocumentForm').find( "input[name='id']" ).val("");

                $.get(url)
                 .done(function( resp ) {
                     if(resp.physicalFileExists)
                     {
                         showUploadEditModal(resp);
                     }
                 });
            });

            function showUploadEditModal(resp)
            {
                $('#editDocumentForm-filename').val(resp.filename);
                $('#editDocumentForm-description').val(resp.description);
                $('#editDocumentForm-date_issued').text(resp.date_issued);
                $('#editDocumentForm-issued_by').text('('+resp.issued_by+')');
                $('#editDocumentForm').find( "input[name='id']" ).val(resp.id);

                $('#editDocumentForm-thumbnail').attr("src", "/"+resp.thumbnail_src);

                var readOnlyExt = ['docx', 'doc'];

                if ($.inArray(resp.file_ext, readOnlyExt) !== -1){
                    $('#template_tender_document_file_roles_section').show();
                    $('#editDocumentForm-contract_group').val(resp.contract_group_id).trigger('change');
                }else{
                    $('#template_tender_document_file_roles_section').hide();
                }

                var url2 = "{{route("tenderDocument.revisionList", array($project->id, 'fileID'))}}";
                url2 = url2.replace('fileID', resp.id);

                $.get(url2).done(function( response ) {
                    $('#editDocumentForm-revisionTo').empty();
                    $('#editDocumentForm-revisionTo').append("<option></option>");

                    $.each(response.data, function(key, respData) {
                        $('#editDocumentForm-revisionTo').append($("<option></option>")
                                .attr("value", respData.id)
                                .text(respData.filename+'.'+respData.extension)
                        );
                    });

                    $('#editDocumentForm-revisionTo').select2('val', "");

                    $('#editUploadedFileModal').modal('show');

                    $('#editDocumentForm-revisionTo').select2('val', response.selected);
                });
            }

            // Initialize the jQuery File Upload widget:
            $('#fileupload').fileupload({
                                url: '{{ route("tenderDocument.upload", array($project->id, $node->id)) }}',
                                formData: {_token :token},
                                maxFileSize: '{{{ Config::get('uploader.max_file_size') }}}',
                                maxChunkSize: null, // 10 MB
                                // Enable image resizing, except for Android and Opera,
                                // which actually support image resizing, but fail to
                                // send Blob objects via XHR requests:
                                disableImageResize: /Android(?!.*Chrome)|Opera/
                                        .test(window.navigator.userAgent)
                            })
                            .bind('fileuploaddone', function (e, data){
                                otable.ajax.reload();
                            })
                            .bind('fileuploaddestroyed', function (e, data){
                                otable.ajax.reload();
                            });

            $('#uploadDocumentModal').on('hidden.bs.modal', function (e) {
                $('#uploadFileTable > tbody').html("");
            });

            $( "#editDocumentForm" ).submit(function( event ) {

                // Stop form from submitting normally
                event.preventDefault();

                // Get some values from elements on the page:
                var $form = $( this ),
                    url = $form.attr( "action" );

                var o = {};
                var a = $form.serializeArray();
                $.each(a, function() {
                    if (o[this.name] !== undefined) {
                        if (!o[this.name].push) {
                            o[this.name] = [o[this.name]];
                        }
                        o[this.name].push(this.value || '');
                    } else {
                        o[this.name] = this.value || '';
                    }
                });

                // Send the data using post
                var posting = $.post( url, o );

                // Put the results in a div
                posting.done(function( data ) {
                    if(data.success){
                        $('#editUploadedFileModal').modal('hide');
                        otable.ajax.reload();
                    }
                });
            });

            $('#revisionsModal').on('hidden.bs.modal', function() {
                $(this).removeData('bs.modal');
            });

            $('#uploadedFilesTable tbody').on( 'click', 'button[data-action=delete]', function () {
                var confirmDelete = confirm("Do you really want to delete this file?");
                if (confirmDelete == true) {
                    var data = otable.row( $(this).parents('tr') ).data();
                    deleteUploadedFile(data.id);
                }
            });

            $(document).on('click', '[data-type=deadLink]', function()
            {
                var fileId = $(this).attr("data-id");
                deleteUploadedFile(fileId);
                // alert saying it was a dead link
                alert("Oops! This file no longer exists. The link will be removed.");
            });

            function deleteUploadedFile(fileId)
            {
                var url = "{{ action('TenderDocumentFoldersController@fileDelete', array($project->id, 'uploadedFileId')) }}";
                url = url.replace('uploadedFileId', fileId);

                $.post(url, {
                     '_token': $('meta[name=_token]').attr("content")
                 })
                 .done(function(data) {
                     // code for result
                     if(!data.success)
                     {
                         alert(data.message);
                     }
                     otable.ajax.reload();
                 })
                 .fail(function(data) {
                     // handle failed request
                     alert('Something went wrong');
                     otable.ajax.reload();
                 });
            }
            @endif
        });

        @if($showUploadButton)
        function uploadTenderDocument(){
            $('#uploadDocumentModal').modal('show');
        }

        function deleteUpload(url){
            $.post(url, {
                 '_token': $('meta[name=_token]').attr("content")
             })
             .done(function(data) {
                 // code for result
             })
             .fail(function(data) {
                 // handle failed request
             });
        }
        @endif
    </script>

@endsection