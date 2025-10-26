<?php $isEditor = $user->isEditor($project); ?>

@extends('layout.main')

@section('css')
    @if($isEditor && !$isSharedFolder)
        <!-- CSS to style the file input field as button and adjust the Bootstrap progress bars -->
        <link rel="stylesheet" href="{{ asset('css/jquery.fileupload.css') }}">
        <link rel="stylesheet" href="{{ asset('css/jquery.fileupload-ui.css') }}">
    @endif
@endsection

@section('breadcrumb')
    <?php $route = $isSharedFolder ? 'projectDocument.mySharedFolder' : 'projectDocument.myFolder'?>
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{ link_to_route('projects.show', str_limit($project->title, 50), array($project->id)) }}</li>
        <li>{{ trans('documentManagementFolders.projectDocuments') }}</li>
        <li>{{ link_to_route('projectDocument.index', str_limit($node->getRoot()->name, 30), array($project->id, $node->root_id)) }}</li>

        @foreach($node->getAncestorsWithoutRoot()->toArray() as $ancestor)
            @if(!$isSharedFolder || ($isSharedFolder && array_key_exists($ancestor['id'], $sharedFolderIds)))
                <li>{{ link_to_route($route, str_limit($ancestor['name'], 20), array($project->id, $ancestor['id'])) }}</li>
            @else
                <li>{{{ str_limit($ancestor['name'], 20) }}}</li>
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
                        @if($isEditor && !$isSharedFolder)
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
                        @if($isEditor && !$isSharedFolder)
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
                        {{ link_to_route($route, $child['name'], array($project->id, $child['id'])) }}
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

    @if($isEditor && !$isSharedFolder)
        @include('document_management_folders.partials.uploadModal')

        @include('document_management_folders.partials.editUploadedFileModal')

        @include('document_management_folders.partials.revisionsModal')
    @endif

    @if($isEditor && !$isSharedFolder)
        @include('layout.file_upload.template-upload')
    @endif

@endsection

@section('js')
    <script src="{{ asset('js/datatables_all_plugins.min.js') }}"></script>

    @if($isEditor && !$isSharedFolder)
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

            @if($isEditor && !$isSharedFolder)
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
                "ajax": "{{route("projectDocument.fileList", array($project->id, $node->id))}}",
                "columns": [
                    {
                        "data": "filename",
                        "mRender": function (data, type, row) {
                            if(row.id > 0){
                                var url = "{{route("projectDocument.fileDownload", array($project->id, 'fileID'))}}";
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
                    {
                        "data": "revision",
                        "class": "text-center",
                        "mRender": function (data, type, row) {
                            @if($isEditor && !$isSharedFolder)
                            if(data > 0){
                                var url = "{{route("projectDocument.fileRevisions", array($project->id, 'fileID'))}}";
                                url = url.replace('fileID', row.id);
                                return '<a title="{{ trans('documentManagementFolders.clickToViewListOfRevisions') }}" data-toggle="modal" href="'+url+'" data-target="#revisionsModal">'+data+'</a>';
                            }
                            @endif
                            return data;
                        }
                    },
                    { "data": "date_issued", "class": "text-center" },
                    { "data": "issued_by"}
                    @if($isEditor && !$isSharedFolder)
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
                "sDom": "<'dt-toolbar'<'col-xs-12 col-sm-6'f><'col-sm-6 col-xs-12 hidden-xs'<'toolbar'>l>r>"+
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

            @if($isEditor && !$isSharedFolder)
            $("div.toolbar").html('<div class="text-right" style="position: absolute; right: 65px;"><a class="btn btn-primary" href="#" onclick="uploadProjectDocument();return false;"><i class="fa fa-upload"></i> {{ trans('documentManagementFolders.upload') }}</a></div>');
            @endif

                // Apply the filter
            $("#uploadedFilesTable thead th input[type=text]").on( 'keyup change', function () {
                otable
                        .column( $(this).parent().index()+':visible' )
                        .search( this.value )
                        .draw();
            });

            @if($isEditor && !$isSharedFolder)
            $('#uploadedFilesTable tbody').on( 'click', 'button[data-action=edit]', function () {
                var data = otable.row( $(this).parents('tr') ).data();
                var url = "{{route("projectDocument.fileInfo", array($project->id, 'fileID'))}}";
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

                var url = "{{route("projectDocument.revisionList", array($project->id, 'fileID'))}}";
                url = url.replace('fileID', resp.id);

                $.get(url).done(function( response ) {
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
                url: '{{ route("projectDocument.upload", array($project->id, $node->id)) }}',
                formData: {_token :token},
                maxFileSize: '{{{ Config::get('uploader.max_file_size') }}}',
                maxChunkSize: null,
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
                var url = "{{ action('DocumentManagementsController@fileDelete', array($project->id, 'uploadedFileId')) }}";
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

        @if($isEditor && !$isSharedFolder)
        function uploadProjectDocument(){
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