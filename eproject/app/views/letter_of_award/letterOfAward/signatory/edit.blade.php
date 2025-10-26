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
		<li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), []) }}</li>
		@if ($isTemplate)
            <li>{{ trans('letterOfAward.letterOfAward') }}</li>
            <li>{{ link_to_route('letterOfAward.templates.selection', trans('letterOfAward.listOfTemplates'), []) }}</li>
            <li>{{ link_to_route('letterOfAward.template.index', $templateName . '(' . trans('letterOfAward.template') . ')', [$templateId]) }}</li>
        @else
            <li>{{ link_to_route('projects.show', str_limit($project->title, 50), [$project->id]) }}</li>
            <li>{{ link_to_route('letterOfAward.index', trans('letterOfAward.letterOfAward'), [$project->id]) }}</li>
        @endif
        <li>{{ trans('letterOfAward.signatory') }}</li>
	</ol>

	@if(!$isTemplate)
		@include('projects.partials.project_status')
	@endif
@endsection

@section('content')
    <section id="widget-grid" class="">
        <div class="row">
            <article class="col-sm-12 col-md-12 col-lg-12">
                <div class="jarviswidget" id="wid-id-0" 
                    data-widget-colorbutton="false" 
                    data-widget-editbutton="false" 
                    data-widget-deletebutton="false">
                    <header>
                        <span class="widget-icon"> <i class="fa fa-edit"></i> </span>
                        <h2>{{ trans('letterOfAward.edit') . ' ' . trans('letterOfAward.signatory') }}</h2>
                    </header>
                    <div>
                        <div class="jarviswidget-editbox"></div>
                        <div class="widget-body no-padding">
                            <form action="#" method="POST" class="smart-form">
                                <input type="hidden" name="_token" value="{{{  csrf_token() }}}">
                                <fieldset>
                                    <div id="summernoteTextInputSection">
                                        <div id="letterOfAwardSignatoryContents" class="summernote"></div>
                                    </div>
                                </fieldset>
                                <footer>
                                    @if ($canUserEditLetterOfAward)
                                        <button type="button" id="btnSave" class="btn btn-primary pull-left"><i class="fa fa-save"></i> {{ trans('forms.save') }}</button>
                                    @endif
                                    <a href="{{{ $indexRoute }}}" class="btn btn-default pull-left">{{ trans('forms.back') }}</a>
                                </footer>
                            </form>
                        </div>
                    </div>
                </div>
            </article>
        </div>
    </section>
@endsection

@section('js')
<script src="{{ asset('js/summernote-0.9.0-dist/summernote.min.js')}}"></script>
<script src="{{ asset('js/app/app.functions.js') }}"></script>
<script>
    $(document).ready(function() {
        $('#letterOfAwardSignatoryContents').summernote({
            placeholder: '{{ trans('letterOfAward.signatory') }}',
            height: 200,
            toolbar: [
                ['style', ['bold', 'italic', 'underline','clear']],
                ['insert', ['hr']],
                ['color', ['color']],
                ['para', ['style', 'ol', 'ul', 'paragraph']],
                ['table', ['table']],
                ['font', ['strikethrough', 'superscript', 'subscript']],
                ['view', ['fullscreen']],
                ['help', ['help']]
            ],
            disableDragAndDrop: true,
        });

        populateSignatory();

        function populateSignatory() {
            app_progressBar.toggle();
            $.ajax({
                url: "{{{ $populateContentsRoute }}}",
                method: 'GET',

                success: function(contents){
                    $('#letterOfAwardSignatoryContents').summernote('code', contents);
                    app_progressBar.maxOut();
                    app_progressBar.toggle();
                    app_progressBar.reset();
                }
            });
        }

        @if (!$canUserEditLetterOfAward)
            $('#letterOfAwardSignatoryContents').summernote('disable');
        @endif

        $('#btnSave').on('click', function(e) {
            e.preventDefault();
            app_progressBar.toggle();

            let url = "{{{ $saveContentsRoute }}}";
            let signatoryContents = $('#letterOfAwardSignatoryContents').summernote('code');

            $.ajax({
                url: url,
                method: 'POST',
                data: {
                    _token: '{{{ csrf_token() }}}',
                    contents: signatoryContents
                },
                success: function(data){
                    app_progressBar.maxOut();
                    app_progressBar.toggle();
                    app_progressBar.reset();
                    window.location.href = "{{{ $indexRoute }}}";
                }
            });
        });
    });
</script>
@endsection