@extends('layout.main')

@section('css')
    <link href="{{ asset('js/summernote-0.9.0-dist/summernote.min.css')}}" rel="stylesheet">
@endsection

@section('breadcrumb')
    <?php
    $disabled = ( isset($editable) && (!$editable) );
    switch($model)
    {
        case $model instanceof \PCK\FormOfTender\Header:
            $fontAwesomeIconClass = 'fa-header';
            $content = $model->header_text;
            $sectionText = trans('formOfTender.header');
            break;
        case $model instanceof \PCK\FormOfTender\Address:
            $fontAwesomeIconClass = 'fa-envelope';
            $content = $model->address;
            $sectionText = trans('formOfTender.address');
            break;
        default:
            $fontAwesomeIconClass = '';
            $content = '';
            $sectionText = '';
    }
    ?>
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        @if($isTemplate)
            <li>{{ trans('formOfTender.formOfTender') }}</li>
            <li>{{ link_to_route('form_of_tender.template.selection', trans('formOfTender.listOfTemplates'), array()) }}</li>
            <li>{{ link_to_route('form_of_tender.template.edit', $templateName . ' (' . trans('formOfTender.template') . ')', array($templateId)) }}</li>
        @else
            <li>
                <a href="{{ route('projects.show', array($project->id)) }}">{{{ str_limit($project->title, 50) }}}</a>
            </li>
            <li>
                <a href="{{ route('projects.tender.index', array($project->id)) }}">{{ trans('formOfTender.tenders') }}</a>
            </li>
            <li>
                <a href="{{ route('projects.tender.show', array($project->id, $tender->id)) }}">{{{ str_limit($tender->current_tender_name, 50) }}}</a>
            </li>
            <li><a href="{{{ $backRoute }}}">{{ trans('formOfTender.formOfTender') }}</a></li>
        @endif
        <li>{{{ $sectionText }}}</li>
    </ol>
@endsection

@section('content')
    <article class="col-sm-12">

        <div class="row">
            <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                <h1 class="page-title">
                    <i class="fa fa-lg fa-fw {{{ $fontAwesomeIconClass }}}"></i>
                    Edit Form Of Tender {{{ $sectionText }}} {{{ $isTemplate? '(Template)' : '' }}}
                </h1>
            </div>
        </div>

        <div class="jarviswidget well">

            <!-- widget div-->
            <div>
                <!-- widget content -->
                <div class="widget-body">
                    <div class="summernote" id="content-data">{{ $content }}</div>
                    <footer class="row" style="margin:20px 10px;text-align:right;">
                        <a href="{{{ $backRoute }}}" class="btn btn-default">{{ trans('forms.back') }}</a>
                        <button type="button" id="save_button" class="btn btn-primary" {{{ $disabled ? 'disabled' : '' }}}><i class="fa fa-save"></i> {{ trans('forms.save') }}</button>
                    </footer>
                </div>
                <!-- end widget content -->

            </div>
            <!-- end widget div -->

        </div>
        <!-- end widget -->

    </article>
@endsection

@section('js')
    <script src="{{ asset('js/summernote-0.9.0-dist/summernote.min.js')}}"></script>
    <script>
        $('.summernote').summernote({
            placeholder: 'Empty',
            toolbar: [
                ['style', ['bold', 'italic', 'underline', 'clear']],
                ['insert', ['picture', 'table', 'hr']],
                ['color', ['color']],
                ['para', ['style', 'ol', 'ul', 'paragraph', 'height']],
                ['font', ['strikethrough', 'superscript', 'subscript']],
                ['codeview', ['codeview']],
                ['help', ['help']],
                ['view', ['fullscreen']]
            ]
        });

        $('#save_button').on('click', function(){
            app_progressBar.toggle();
            $.ajax({
                url: '{{{ $updateRoute }}}',
                method: 'POST',
                data: {
                    _token: '{{{ csrf_token() }}}',
                    contentData: $('#content-data').summernote('code')
                },
                success: function(data){
                    app_progressBar.maxOut();
                    window.location.replace(data);
                }
            });
        });
    </script>
@endsection