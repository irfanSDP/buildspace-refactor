@extends('layout.main')

@section('css')
    <!-- CSS to style the file input field as button and adjust the Bootstrap progress bars -->
    <link rel="stylesheet" href="{{ asset('css/jquery.fileupload.css') }}">
    <link rel="stylesheet" href="{{ asset('css/jquery.fileupload-ui.css') }}">
@endsection

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{ trans('tenderDocumentFolders.tenderDocumentFolders') }}</li>
        <li>{{ link_to_route('tender_documents.template.directory', trans('general.templates')) }}</li>
        <li>{{ link_to_route('tender_documents.template.index', trans('tenderDocumentFolders.tenderDocuments').' ('.$node->id.')', array($node->id)) }}</li>

        @foreach($node->getAncestorsAndSelf() as $ancestor)
            @if ( $ancestor->id !== $node->id && $ancestor->id != $node->root_id)
                <li>{{ link_to_route($folderRoute, str_limit($ancestor->name, 20), array($ancestor->id)) }}</li>
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
    </div>

    <div class="jarviswidget " id="wid-id-projectDocumentView"
         data-widget-colorbutton="false" data-widget-editbutton="false" data-widget-togglebutton="false"
         data-widget-deletebutton="false" data-widget-fullscreenbutton="false" data-widget-custombutton="false"
         data-widget-sortable="false">
        <header>
            <span class="widget-icon"> <i class="fa fa-file-alt"></i> </span>

            <h2>{{trans('documentManagementFolders.documents')}}</h2>
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
                        <th class="hasinput icon-addon" style="">
                            <input id="dateselect_filter" type="text" placeholder="{{ trans('documentManagementFolders.date') }}"
                                   class="form-control datepicker" data-dateformat="dd/mm/yy">
                            <label for="dateselect_filter" class="glyphicon glyphicon-calendar no-margin padding-top-15"
                                   rel="tooltip" title="" data-original-title="{{ trans('documentManagementFolders.date') }}"></label>
                        </th>
                        <th class="hasinput" style="width:20%;">
                            <input type="text" class="form-control" placeholder="{{ trans('documentManagementFolders.issuedBy') }}"/>
                        </th>
                        <th class="hasinput" style="width:15%;">
                            <input type="text" class="form-control" placeholder="{{ trans('documentManagementFolders.workCategory') }}"/>
                        </th>
                        <th style="width:5%;"></th>
                        <th style="width:5%;"></th>
                    </tr>
                    <tr>
                        <th data-class="expand">{{ trans('documentManagementFolders.filename') }}</th>
                        <th data-hide="phone">{{ trans('documentManagementFolders.description') }}</th>
                        <th data-hide="phone">{{ trans('documentManagementFolders.date') }}</th>
                        <th data-hide="phone,tablet">{{ trans('documentManagementFolders.issuedBy') }}</th>
                        <th data-hide="phone,tablet">{{ trans('documentManagementFolders.workCategory') }}</th>
                        <th>{{ trans('documentManagementFolders.edit') }}</th>
                        <th>{{ trans('files.delete') }}</th>
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
                                    {{ link_to_route($folderRoute, $child['name'], array($child['id'])) }}
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

        <!-- Upload Modal -->
        @include('tender_document_folders.partials.uploadModal')

        <!-- edit uploaded file Modal -->
        @include('tender_document_folders.template.partials.editUploadedFileModal')

        <!-- Revisions Modal -->
        @include('tender_document_folders.partials.revisionsModal')


    @include('layout.file_upload.template-upload')

@endsection

@section('js')
    <script src="{{ asset('js/datatables_all_plugins.min.js') }}"></script>

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
    <script>
        $(document).ready(function() {
            'use strict';

            pageSetUp();

            var token = $('meta[name=_token]').attr("content");

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

            /* COLUMN FILTER  */
            var responsiveHelper_uploaded_files_table = undefined;
            var breakpointDefinition = {
                tablet : 1024,
                phone : 480
            };

            var otable = $('#uploadedFilesTable').DataTable({
                "ajax": "{{route("tender_documents.template.fileList", array($node->id))}}",
                "columns": [
                    {
                        "data": "filename",
                        "mRender": function (data, type, row) {
                            if(row.id > 0){
                                var url = "{{route("tender_documents.template.fileDownload", 'fileID')}}";
                                url = url.replace('fileID', row.id);
                                if(row.physicalFileExists){
                                    return '<a title="{{ trans('documentManagementFolders.clickToDownload') }}" href="'+url+'">'+data+'</a>';
                                }
                                else{
                                    return '<a href="#" data-action="delete" data-id="'+row.id+'" data-type="deadLink"><span style="text-decoration: line-through">'+data+'</span></a>';
                                }
                            }
                            return "&nbsp;";
                        }
                    },
                    { "data": "description" },
                    { "data": "date_issued", "class": "text-center" },
                    { "data": "issued_by"},
                    { "data": "work_category_name"}
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

            $("div.toolbar").html('<div class="text-right"><a class="btn btn-primary" href="#" onclick="uploadTenderDocument();return false;"><i class="fa fa-upload"></i> {{ trans('documentManagementFolders.upload') }}</a></div>');

            // Apply the filter
            $("#uploadedFilesTable thead th input[type=text]").on( 'keyup change', function () {
                otable
                        .column( $(this).parent().index()+':visible' )
                        .search( this.value )
                        .draw();
            });

            $('#uploadedFilesTable tbody').on( 'click', 'button[data-action=edit]', function () {
                var data = otable.row( $(this).parents('tr') ).data();
                var url = "{{route("tender_documents.template.fileInfo", 'fileID')}}";
                url = url.replace('fileID', data.id);

                $('#editDocumentForm-filename').val("");
                $('#editDocumentForm-work_category').val("0");
                $('#editDocumentForm-description').val("");
                $('#editDocumentForm-date_issued').text("");
                $('#editDocumentForm-issued_by').text("");
                $('#editDocumentForm').find( "input[name='id']" ).val("");

                $.get(url).done(function( resp ) {
                    if(resp.physicalFileExists) {
                        showUploadEditModal(resp);
                    }
                });
            });

            function showUploadEditModal(resp){
                $('#editDocumentForm-filename').val(resp.filename);
                $('#editDocumentForm-work_category').val(resp.work_category_id).trigger('change');
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
                $('#editUploadedFileModal').modal('show');
            }

            // Initialize the jQuery File Upload widget:
            $('#fileupload').fileupload({
                url: '{{ route("tender_documents.template.upload", array($node->id)) }}',
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
                var url = "{{ action('TemplateTenderDocumentFoldersController@fileDelete', 'uploadedFileId') }}";
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
        });

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
    </script>
@endsection