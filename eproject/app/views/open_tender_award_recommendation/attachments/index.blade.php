@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        @if($user->getAssignedCompany($project))
            <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
            <li>{{ link_to_route('projects.show', str_limit($project->title, 50), array($project->id)) }}</li>
            <li>{{ link_to_route('projects.openTender.index', trans('navigation/projectnav.openTender'), array($project->id)) }}</li>
            <li>{{ link_to_route('projects.openTender.show', $tender->current_tender_name, [$project->id, $tender->id]) }}</li>
            <li>{{ link_to_route('open_tender.award_recommendation.report.show', trans('openTenderAwardRecommendation.awardRecommendation'), [$project->id, $tender->id]) }}</li>
            <li>{{ trans('openTenderAwardRecommendation.attachments') }}</li>
        @else
            <li>{{ link_to_route('home.index', trans('navigation/mainnav.home'), array()) }}</li>
            <li>{{ link_to_route('topManagementVerifiers.open_tender.award_recommendation.report.show', trans('openTenderAwardRecommendation.awardRecommendation'), [$project->id, $tender->id]) }}</li>
            <li>{{ trans('openTenderAwardRecommendation.attachments') }}</li>
        @endif
    </ol>
    @include('projects.partials.project_status')
@endsection

@section('css')
    <!-- CSS to style the file input field as button and adjust the Bootstrap progress bars -->
    <link rel="stylesheet" href="{{ asset('css/jquery.fileupload.css') }}">
    <link rel="stylesheet" href="{{ asset('css/jquery.fileupload-ui.css') }}">
@endsection

@section('content')
    <div class="row">
        <div class="col-xs-12 col-sm-10 col-md-10 col-lg-10">
            <h1 class="page-title txt-color-bluedark">
                <i class="fa fa-file-alt fa-fw"></i>
                {{ trans('openTenderAwardRecommendation.attachments') }}
            </h1>
        </div>
        @if ($canUploadDeleteFile)
            <div class="col-xs-12 col-sm-2 col-md-2 col-lg-2">
                <div class="btn-group pull-right header-btn">
                    <button id="btnUpload" class="btn btn-primary"><i class="fa fa-upload"></i> {{ trans('openTenderAwardRecommendation.upload') }}</button>
                </div>
            </div>
        @endif
   
    @if ($isEditor)
        <div class="col col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <div class="well">
                <p>{{ trans('openTenderAwardRecommendation.uploadReminderTitle') }}:</p>
                <ol>
                    <li>{{ trans('openTenderAwardRecommendation.layoutPlan') }}</li>
                    <li>{{ trans('openTenderAwardRecommendation.eAuctionResult') }}</li>
                    <li>{{ trans('openTenderAwardRecommendation.tenderOpeningForm') }} ({{ trans('openTenderAwardRecommendation.original') }} & {{ trans('openTenderAwardRecommendation.resubmission') }})</li>
                </ol>
            </div>
        </div>
    @endif
    </div>

    <div class="row">
        <div class="col col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <div class="jarviswidget" id="wid-id-projectDocumentView">
                <header>
                    <h2>{{trans('documentManagementFolders.documents')}}</h2>
                </header>
                <div>
                    <div class="widget-body no-padding">
                        <div class="table-responsive">
                            <table class="table " id="uploadedFilesTable">
                                <thead>
                                    <tr>
                                        <th>{{ trans('documentManagementFolders.filename') }}</th>
                                        <th style="width:280px;">{{ trans('openTenderAwardRecommendation.uploadedBy') }}</th>
                                        <th style="width:120px;">{{ trans('documentManagementFolders.date') }}</th>
                                        @if ($canUploadDeleteFile)<th class="text-center squeeze">{{ trans('files.delete') }}</th>@endif
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if ($canUploadDeleteFile)
        @include('templates.uploadModal')
        @include('layout.file_upload.template-upload')
    @endif

@endsection


@section('js')
    <script src="{{ asset('js/datatables_all_plugins.min.js') }}"></script>
    <script src="{{ asset('js/app/app.functions.js') }}"></script>

    @if ($canUploadDeleteFile)
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

            @if ($canUploadDeleteFile)
            $('#btnUpload').on('click', function(e) {
                e.preventDefault();
                $('#uploadDocumentModal').modal('show');
            });
            @endif

            $('#uploadedFilesTable').DataTable({
                "sDom": "tpi",
                "autoWidth" : false,
                scrollCollapse: true,
                "paging": true,
                "iDisplayLength":10,
                "bServerSide" : true,
                "language": {
                    "infoFiltered": "",
                    "zeroRecords": "No files uploaded"
                },
                "sAjaxSource": "{{ $getAttachmentsRoute }}",
                "fnServerParams": function ( aoData ) {
                    aoData.push( { name: 'tenderId', value: "{{{ $tender->id }}}" } );
                },
                "aoColumnDefs": [
                {
                    "aTargets": [ 0 ],
                    "orderable": false,
                    "mData": function ( source, type, val ) {
                        return '<a href="' + source['download_route'] + '">' + source['fileName'] + '</a>';
                    },
                    "sClass": "text-left text-center text-nowrap squeeze"
                },
                {
                    "aTargets": [ 1 ],
                    "orderable": false,
                    "mData": function ( source, type, val ) {
                        return source['uploaded_by'];
                    },
                    "sClass": "text-middle text-center text-nowrap squeeze"
                },
                {
                    "aTargets": [ 2 ],
                    "orderable": false,
                    "mData": function ( source, type, val ) {
                        return source['date'];
                    },
                    "sClass": "text-middle text-center text-nowrap squeeze"
                },
                @if ($canUploadDeleteFile)
                    {
                        "aTargets": [ 3 ],
                        "orderable": false,
                        "mData": function ( source, type, val ) {
                            return '<button type="button" data-action="deleteFile" data-url="' + source['delete_route'] + '" type="tooltip" class="btn btn-sm btn-danger"><i class="fa fa-times"></i></button>';
                        },
                        "sClass": "text-middle text-center text-nowrap btn-sm"
                    },
                @endif
                ],
                "sDom": "<'dt-toolbar'<'col-xs-12 col-sm-6 hidden-xs'f><'col-sm-6 col-xs-12 hidden-xs'<'toolbar'>>r>"+
                "t"+
                "<'dt-toolbar-footer'<'col-sm-6 col-xs-12 hidden-xs'i><'col-xs-12 col-sm-6'p>>",
            });

            @if ($canUploadDeleteFile)
            // Initialize the jQuery File Upload widget:
            $('#fileupload').fileupload({
                url: '{{ route("open_tender.award_recommendation.report.attachment.upload", [$project->id, $tender->id]) }}',
                formData: {
                    _token :token,
                    tenderId: "{{{ $tender->id }}}",
                },
                maxFileSize: '{{{ Config::get('uploader.max_file_size') }}}',
                maxChunkSize: null, // 10 MB
                // Enable image resizing, except for Android and Opera,
                // which actually support image resizing, but fail to
                // send Blob objects via XHR requests:
                disableImageResize: /Android(?!.*Chrome)|Opera/
                        .test(window.navigator.userAgent)
            })
            .bind('fileuploaddone', function (e, data){
                $('#uploadedFilesTable').DataTable().draw();
            })
            .bind('fileuploaddestroyed', function (e, data){
                $('#uploadedFilesTable').DataTable().draw();
            });

            $('#uploadDocumentModal').on('hidden.bs.modal', function (e) {
                $('#uploadFileTable > tbody').html("");
            });
            @endif
            
            $(document).on('click', '[data-action="deleteFile"]', function (e) {
                app_progressBar.toggle();
                $.ajax({
                    url: $(this).data('url'),
                    method: 'POST',
                    data: {
                        _token: '{{{ csrf_token() }}}'
                    },
                    success: function (data) {
                        $('#uploadedFilesTable').DataTable().draw();
                        app_progressBar.maxOut();
                        app_progressBar.toggle();
                        app_progressBar.reset();
                    }
                });
            });
        });

        function deleteUpload(url) {
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