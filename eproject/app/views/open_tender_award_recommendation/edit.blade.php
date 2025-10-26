@extends('layout.main')

@section('css')
    <link href="{{ asset('js/summernote-0.9.0-dist/summernote.min.css')}}" rel="stylesheet">
    <style>
        .note-editor.note-frame .note-editing-area .note-editable[contenteditable="false"] {
            background-color: #fff;
        }
    </style>
@endsection

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{ link_to_route('projects.show', str_limit($project->title, 50), array($project->id)) }}</li>
        <li>{{ link_to_route('projects.openTender.index', trans('navigation/projectnav.openTender'), array($project->id)) }}</li>
        <li>{{ link_to_route('projects.openTender.show', $tender->current_tender_name, [$project->id, $tender->id]) }}</li>
        <li>{{ link_to_route('open_tender.award_recommendation.report.show', trans('openTenderAwardRecommendation.awardRecommendation'), [$project->id, $tender->id]) }}</li>
        <li>{{ trans('openTenderAwardRecommendation.editReport') }}</li>
    </ol>
    @include('projects.partials.project_status')
@endsection

@section('content')
    <section id="widget-grid" class="">
        <div class="row">
            <article class="col-sm-12 col-md-12 col-lg-12">
                <div class="jarviswidget" id="open_tender_award_recommendation_edit-content" 
                    data-widget-colorbutton="false" 
                    data-widget-editbutton="false" 
                    data-widget-deletebutton="false">
                    <header>
                        <span class="widget-icon"> <i class="fa fa-edit"></i> </span>
                        <h2>{{ trans('openTenderAwardRecommendation.editReport') }}</h2>
                    </header>
                    <div>
                        <div class="jarviswidget-editbox"></div>
                        <div class="widget-body no-padding">
                            <form action="#" method="POST" class="smart-form">
                                <input type="hidden" name="_token" value="{{{  csrf_token() }}}">
                                <fieldset>
                                    <div id="summernoteTextInputSection">
                                        <div id="award_recommendation_report" class="summernote" id="award_recommendation_report"></div>
                                    </div>
                                </fieldset>
                                <footer class="pull-right" style="padding:6px;">
                                    <a href="{{ route('open_tender.award_recommendation.report.show', [$project->id, $tender->id]) }}" type="button" class="btn btn-default">{{ trans('forms.back') }}</a>
                                    <button type="button" class="btn btn-success" id="btnViewReportEditLogs" data-toggle="modal" data-target="#openTenderAwardRecommendationReportEditLogModal"><i class="fa fa-search"></i> {{ trans('openTenderAwardRecommendation.viewReportEditLogs') }}</button>
                                    <button type="button" id="btnSaveReport" class="btn btn-primary"><i class="fa fa-save"></i> {{ trans('forms.save') }}</button>
                                </footer>
                            </form>
                        </div>
                    </div>
                </div>
            </article>
        </div>
    </section>

    @include('open_tender_award_recommendation.partials.award_recommendation_report_edit_log_modal')
@endsection

@section('js')
<script src="{{ asset('js/summernote-0.9.0-dist/summernote.min.js')}}"></script>
<script src="{{ asset('js/app/app.functions.js') }}"></script>
<script>
$(document).ready(function() {
    $('#award_recommendation_report').summernote({
        placeholder: 'Type your report here',
        height: 400,
        toolbar: [
            ['style', ['bold', 'italic', 'underline', 'clear']],
            ['insert', ['hr']],
            ['color', ['color']],
            ['para', ['style', 'ol', 'ul', 'paragraph']],
            ['table', ['table']],
            ['font', ['strikethrough', 'superscript', 'subscript']],
            ['view', ['fullscreen']],
            ['help', ['help']]
        ],
        hint: {
            mentions: [@foreach($tableTags as $tag)'{{{ $tag }}}',@endforeach
            ],
            match: /\B@(\w*)$/,
            search: function(keyword, callback) {
                callback($.grep(this.mentions, function (item) {
                    return item.indexOf(keyword) == 0;
                }));
            },
            content: function(item) {
                return '@' + item;
            }
        }
    });

    populateReportContents();

    $('#btnSaveReport').on('click', function(e) {
        e.preventDefault();
        app_progressBar.toggle();
        let url = '{{ route('open_tender.award_recommendation.report.save', [$project->id, $tender->id]) }}';
        var report_contents = $('#award_recommendation_report').summernote('code');

        $.ajax({
            url: url,
            method: 'POST',
            data: {
                _token: '{{{ csrf_token() }}}',
                projectId: '{{{ $project->id }}}',
                tenderId: '{{{ $tender->id }}}',
                report_contents: report_contents
            },
            success: function(data){
                app_progressBar.maxOut();
                app_progressBar.toggle();
                app_progressBar.reset();
                window.location.href = "{{ route('open_tender.award_recommendation.report.show', [$project->id, $tender->id]) }}";
            }
        });
    });

    function populateReportContents() {
        app_progressBar.toggle();
        $.ajax({
            url: "{{ route('open_tender.award_recommendation.report.get', [$project->id, $tender->id]) }}",
            method: 'POST',
            data: {
                _token: '{{{ csrf_token() }}}',
                projectId: '{{{ $project->id }}}',
                tenderId: '{{{ $tender->id }}}',
            },
            success: function(reportContents){
                $('#award_recommendation_report').summernote('code', reportContents);
                app_progressBar.maxOut();
                app_progressBar.toggle();
                app_progressBar.reset();
            }
        });
    }

    $('#openTenderAwardRecommendationReportEditLogModal').on('show.bs.modal', function (event) {
        var modal = $(this);
        modal.find('.modal-body ol').empty();
        modal.find('.message').empty();

        $.ajax({
            url: "{{ route('open_tender.award_recommendation.report.edit.logs.get', [$project->id, $tender->id]) }}",
            data: {
                projectId: {{{ $project->id }}},
                awardRecommendationId: {{{ $awardRecommendation->id }}},
            },
            success: function(data){
                var logEntry = "";
                var user = "";
                var logText = "";
                var updatedAt = "";

                if(data.length < 1)
                {
                    modal.find('.message').append('No changes have been made');
                }
                for(dataIndex in data)
                {
                    logText = '<span style="color:black">' + data[dataIndex].actionString + '</span> ';
                    user = '<span style="color:blue">' + data[dataIndex].user + '</span> ';
                    updatedAt = '<span style="color:red">' + data[dataIndex].formattedDateTime + '</span>';
                    logEntry = logText + user + updatedAt;
                    modal.find('.modal-body ol').append('<li>' + logEntry + '</li>');
                }
            }
        });
    });

});
</script>
@endsection
