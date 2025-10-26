@extends('layout.main')

@section('css')
    <link href="{{ asset('js/summernote-0.9.0-dist/summernote.min.css')}}" rel="stylesheet">
@endsection

@section('breadcrumb')
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
                <a href="{{ route('projects.tender.index', array($project->id)) }}">{{{ trans('formOfTender.tenders') }}}</a>
            </li>
            <li>
                <a href="{{ route('projects.tender.show', array($project->id, $tender->id)) }}">{{{ str_limit($tender->current_tender_name, 50) }}}</a>
            </li>
            <li><a href="{{{ $backRoute }}}">{{ trans('formOfTender.formOfTender') }}</a></li>
        @endif
        <li>{{{ trans('formOfTender.tenderAlternatives') }}}</li>
    </ol>
@endsection

@section('content')
    <?php
    // Css Classes
    $disabled = ( isset( $editable ) && ( ! $editable ) );
    $selectedClass = "bg-color-greenLight txt-color-white";
    $unselectedClass = "bg-color-greenDark txt-color-white";
    ?>
    <article class="col-sm-12">

        <div class="row">
            <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                <h1 class="page-title">
                    <i class="fa fa-lg fa-fw fa-check-square"></i>
                    @if(isset($isTemplate) && $isTemplate)
                        {{{ trans('formOfTender.tenderAlternatives(Template)') }}}
                    @else
                        {{{ trans('formOfTender.tenderAlternatives') }}}
                    @endif
                </h1>
            </div>
        </div>

        <!-- Widget ID (each widget will need unique ID)-->
        <div class="jarviswidget well" id="wid-id-0">

            <!-- widget div-->
            <div id="tender-alternatives-form">
                <!-- widget content -->
                @if($isTemplate)
                    {{ Form::open(array('route'=> array('form_of_tender.tenderAlternatives.template.update', $templateId))) }}
                @else
                    {{ Form::open(array('route'=> array('form_of_tender.tenderAlternatives.update', $project->id, $tenderId))) }}
                @endif
                <input type="hidden" name="is_template" value="{{{ isset($isTemplate) ? $isTemplate : false }}}">
                <input type="hidden" name="ta_ids" id="ta_ids" value="">

                <div class="widget-body">
                    {{ $errors->first('tender_alternative', '<em class="invalid">:message</em>') }}

                    <div class="row bg-color-green rounded-less mb-8">
                        <div class="row" style="margin: 10px">
                            <div class="dd scrollable" id="tenderAlternativesList" style="min-width:100%;">
                                <ol class="dd-list root-list">
                                    <?php $count = 0; $is_editable = true; ?>
                                    @foreach($tenderAlternatives as $tenderAlternative)
                                        <li class="dd-item" data-id="{{{ $tenderAlternative->id }}}" {{{ $is_editable ? 'data-is_editable="true"' : '' }}}>
                                            <div class="dd3-content rounded-ne rounded-sw">

                                                <table>
                                                    <tr>
                                                        <td class="text-top">
                                                            <span class="label bg-color-greenDark" data-type="label" style="margin-right:5px;">{{{ ++$count }}}</span>
                                                        </td>
                                                        <td class="fill-horizontal">
                                                            <textarea class="summernote" name="tender_alternative_description_{{{ $tenderAlternative->id }}}" id="tender_alternative_description_{{{ $tenderAlternative->id }}}" style="display: none;">
                                                                {{ $tenderAlternative->description }}
                                                            </textarea>
                                                        </td>
                                                        <td style="width:5%; padding-left: 30px; vertical-align: middle">
                                                            <div class="checkbox" data-toggle="tooltip" title="{{ trans('forms.include?') }}" data-placement="left">
                                                                <label>
                                                                    <input type="checkbox" class="checkbox style-1" name="tender_alternative[{{{ $tenderAlternative->tender_alternative_class_name }}}]" data-type="tender-alternative-selection" id="tender_alternative_{{{ $tenderAlternative->id }}}" {{{ $tenderAlternative->show? 'checked' : null }}}>
                                                                    <span></span>
                                                                </label>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                </table>

                                            </div>
                                        </li>
                                    @endforeach
                                </ol>
                            </div>
                        </div>
                    </div>

                    <footer class="row text-right">
                        <a href="{{{ $backRoute }}}" class="btn btn-default">{{ trans('forms.back') }}</a>
                        <button type="button" class="btn btn-primary submit-form" {{{ $disabled ? 'disabled' : '' }}}><i class="fa fa-save"></i> {{ trans('forms.save') }}</button>
                    </footer>
                </div>
                {{ Form::close() }}
                <!-- end widget content -->

            </div>
            <!-- end widget div -->

        </div>
        <!-- end widget -->

    </article>
@endsection

@section('js')
    <script src="{{ asset('js/app/app.functions.js') }}"></script>
    <script src="{{ asset('js/plugin/nestable-master/jquery.nestable.js') }}"></script>
    <script src="{{ asset('js/summernote-0.9.0-dist/summernote.min.js')}}"></script>
    <script>
        $('#tenderAlternativesList .summernote').summernote({
            placeholder: 'Type here',
            toolbar: [
                ['style', ['bold', 'italic', 'underline', 'clear']],
                ['insert', ['picture', 'table', 'hr']],
                ['color', ['color']],
                ['para', ['style', 'ol', 'ul', 'paragraph', 'height']],
                ['font', ['strikethrough', 'superscript', 'subscript']],
                ['codeview', ['codeview']],
                ['help', ['help']],
                ['view', ['fullscreen']]
            ],
            hint: {
                mentions: [@foreach($tags as $tag)'{{{ $tag }}}',@endforeach
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

        $('.note-editor .note-toolbar').hide();
        $('.note-editor').addClass('mb-0');

        $(document).on('focus', '.note-editor .note-editable', function() {
            var toolbar = $(this).parents('.note-editor').children('.note-toolbar');

            if( ! (toolbar.hasClass('active'))) {
                $('.note-editor .note-toolbar').removeClass('active').slideUp();

                toolbar.addClass('active').slideDown();
            }
        });

        $('#tender-alternatives-form button.submit-form').on('click', function(){
            app_progressBar.toggle();
            app_progressBar.maxOut();

            var ta_ids = [];

            $('.summernote').each(function( index ) {
                var t = $(this);
                var ta_id = t.parents('.dd-item').data('id');
                //if ($('#tender_alternative_'+ta_id).is(':checked')) {
                    ta_ids.push(ta_id);
                //}
            });

            $('#ta_ids').val(ta_ids);
            $('#tender-alternatives-form form').submit();
        });
    </script>
@endsection