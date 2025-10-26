@extends('layout.main')

@section('css')
    <link href="{{ asset('js/summernote/summernote.min.css') }}" rel="stylesheet">
@endsection

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('consultant.management.contracts.index', trans('navigation/mainnav.home')) }}</li>
        <li>{{ link_to_route('consultant.management.contracts.contract.show', $consultantManagementContract->short_title, [$consultantManagementContract->id]) }}</li>
        @if(!isset($questionnaire))
        <li>{{ link_to_route('consultant.management.questionnaire.settings.index', trans('general.questionnaireSettings'), [$consultantManagementContract->id]) }}</li>
        <li>{{{ trans('general.newQuestionnaire') }}}</li>
        @else
        <li>{{ link_to_route('consultant.management.questionnaire.settings.show', trans('general.questionnaireSettings'), [$consultantManagementContract->id, $questionnaire->id]) }}</li>
        <li>{{{ trans('forms.edit') }}} {{{ trans('general.questionnaire') }}}</li>
        @endif
    </ol>
@endsection

@section('content')
<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <h1 class="page-title txt-color-blueDark">
            <i class="fa fa-tasks"></i> {{{ trans('general.generalQuestionnaires') }}}
        </h1>
    </div>
</div>

<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="jarviswidget ">
            <header>
                <h2>@if(isset($questionnaire)) {{{ trans('forms.edit') }}} {{{ $questionnaire->title }}} @else {{{ trans('general.newQuestionnaire') }}} @endif</h2>
            </header>
            <div>
                <div class="widget-body">
                    {{ Form::open(['route' => ['consultant.management.questionnaire.settings.store', $consultantManagementContract->id], 'class' => 'smart-form']) }}
                    <div class="row">
                        <section class="col col-xs-12 col-md-12 col-lg-12">
                            <label class="label">{{{ trans('general.question') }}} <span class="required">*</span>:</label>
                            <label class="input {{{ $errors->has('question') ? 'state-error' : null }}}">
                                {{ Form::textarea('question', Input::old('question', isset($questionnaire) ? $questionnaire->question : null), ['id'=>'questionnaire_question-txt', 'autofocus' => 'autofocus', 'class'=>'summernote']) }}
                            </label>
                            {{ $errors->first('question', '<em class="invalid">:message</em>') }}
                        </section>
                    </div>
                    <div class="row">
                        <section class="col col-xs-12 col-md-4 col-lg-4">
                            <label class="label">{{{ trans('general.type') }}} <span class="required">*</span>:</label>
                            <label class="input {{{ $errors->has('type') ? 'state-error' : null }}}">
                                <select class="select2 fill-horizontal" name="type" id="type-select">
                                    @foreach($typeList as $value => $text)
                                    <option value="{{$value}}" @if($value == Input::old('type', ($questionnaire) ? $questionnaire->type : null)) selected @endif>{{{ $text }}}</option>
                                    @endforeach
                                </select>
                            </label>
                            {{ $errors->first('type', '<em class="invalid">:message</em>') }}
                        </section>
                        <section class="col col-xs-12 col-md-4 col-lg-4">
                            <?php $defaultMandatory = (isset($questionnaire) && !$questionnaire->required) ? 0 : 1; ?>
                            <label class="label">Mandatory <span class="required">*</span>:</label>
                            <label class="input {{{ $errors->has('required') ? 'state-error' : null }}}">
                                <select class="select2 fill-horizontal" name="required" id="required-select">
                                    <option value="1" @if(1 == Input::old('required', $defaultMandatory)) selected @endif>{{{ trans('general.yes') }}}</option>
                                    <option value="0" @if(0 == Input::old('required', $defaultMandatory)) selected @endif>{{{ trans('general.no') }}}</option>
                                </select>
                            </label>
                            {{ $errors->first('required', '<em class="invalid">:message</em>') }}
                        </section>
                    </div>
                    <div class="row">
                        <section class="col col-xs-12 col-sm-12 col-md-12 col-lg-12">
                            <div class="custom-control custom-checkbox">
                                {{ Form::checkbox('with_attachment', 1, Input::old('with_attachment', isset($questionnaire) ? $questionnaire->with_attachment : false), ['id'=>'with_attachment-chkbox', 'class'=>'custom-control-input']) }}
                                <label class="custom-control-label" for="with_attachment-chkbox"><strong>With Attachment</strong></label>
                            </div>
                            {{ $errors->first('with_attachment', '<em class="invalid">:message</em>') }}
                        </section>
                    </div>

                    <?php
                    $selectedType = Input::old('type', ($questionnaire) ? $questionnaire->type : null);
                    ?>
                    <div id="questionnaire-options" @if( $selectedType == PCK\ConsultantManagement\ConsultantManagementQuestionnaire::TYPE_MULTI_SELECT or $selectedType == PCK\ConsultantManagement\ConsultantManagementQuestionnaire::TYPE_SINGLE_SELECT)  @else style="display:none;" @endif>
                        <hr class="simple">
                        <div class="row">
                            <section class="col col-xs-6 col-sm-6 col-md-8 col-lg-8">
                                <h5>{{{trans('documentManagementFolders.options')}}}</h5>
                            </section>
                            <section class="col col-xs-6 col-sm-6 col-md-4 col-lg-4">
                                <div class="pull-right">
                                {{ Form::button('<i class="fa fa-plus"></i> '.trans('forms.add')." ".trans('documentManagementFolders.options'), ['id'=>'addOptionBtn', 'class' => 'btn btn-info'] )  }}
                                </div>
                            </section>
                        </div>
                        <div class="row">
                            <section class="col col-xs-12 col-md-12 col-lg-12">
                                <table class="table table-bordered table-condensed table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th style="width:auto;">{{{trans('documentManagementFolders.options')}}}</th>
                                            <th style="width:82px;text-align:center;">{{{trans('forms.delete')}}}</th>
                                        </tr>
                                    </thead>
                                    <tbody id="optionRow" class="optionRow">
                                    <?php
                                    $questionnaireOptions = Input::old('options', (!isset($questionnaire) or empty($questionnaire->options->toArray())) ? [['option_id'=>-1, 'text'=>'', 'value'=>'']] : $questionnaire->options->toArray());
                                    ?>
                                    @foreach($questionnaireOptions as $optionIdx => $questionnaireOption)
                                        <tr class="optionRecordRow">
                                            <td>
                                                <div class="row">
                                                    <section class="col col-xs-12 col-sm-12 col-md-12 col-lg-12">
                                                        <label class="label">Text <span class="required">*</span>:</label>
                                                        <label class="input {{{ $errors->has('options.'.$optionIdx.'.text') ? 'state-error' : null }}}">
                                                            {{ Form::text('options['.$optionIdx.'][text]', Input::old('options.'.$optionIdx.'.text', $questionnaireOption['text']), ['autofocus' => 'autofocus']) }}
                                                        </label>
                                                        {{ $errors->first('options.'.$optionIdx.'.text', '<em class="invalid">:message</em>') }}
                                                    </section>
                                                </div>
                                            </td>
                                            <td class="text-middle text-center squeeze">
                                                @if($optionIdx > 0)
                                                {{ Form::button('<i class="fa fa-trash"></i>', ['class' => 'btn btn-md btn-danger deleteOptionBtn', 'title'=>trans('forms.delete')] ) }}
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </section>
                        </div>
                    </div>

                    <footer>
                        {{ Form::hidden('id', (isset($questionnaire)) ? $questionnaire->id : -1) }}
                        {{ Form::hidden('cid', $consultantManagementContract->id) }}
                        @if(!isset($questionnaire))
                        {{ link_to_route('consultant.management.questionnaire.settings.index', trans('forms.back'), [$consultantManagementContract->id], ['class' => 'btn btn-default']) }}
                        @else
                        {{ link_to_route('consultant.management.questionnaire.settings.show', trans('forms.back'), [$consultantManagementContract->id, $questionnaire->id], ['class' => 'btn btn-default']) }}
                        @endif
                        {{ Form::button('<i class="fa fa-save"></i> '.trans('forms.save'), ['type' => 'submit', 'class' => 'btn btn-primary'] )  }}
                    </footer>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/handlebars.js/4.7.6/handlebars.min.js"></script>
<script id="document-template" type="text/x-handlebars-template">
    <tr class="optionRecordRow">
        <td>
            <div class="row">
                <section class="col col-xs-12 col-sm-12 col-md-12 col-lg-12">
                    <label class="label">Text <span class="required">*</span>:</label>
                    <label class="input">
                        <input autofocus="autofocus" name="options[@{{inputIdx}}][text]" type="text" value="">
                    </label>
                </section>
            </div>
        </td>
        <td class="text-middle text-center squeeze">
            {{ Form::button('<i class="fa fa-trash"></i>', ['class' => 'btn btn-md btn-danger deleteOptionBtn', 'title'=>trans('forms.delete')] ) }}
        </td>
    </tr>
</script>
<script src="{{ asset('js/summernote/summernote.min.js') }}"></script>
<script type="text/javascript">
$(document).ready(function () {
    $('#questionnaire_question-txt').summernote({
        focus: true,
        disableResizeEditor: true,
        placeholder: "{{ trans('general.question') }}",
        toolbar: [
            ['style', ['bold', 'italic', 'underline', 'clear']],
            ['insert', ['link', 'picture', 'table', 'hr']],
            ['color', ['color']],
            ['para', ['style', 'ol', 'ul', 'paragraph', 'height']],
            ['codeview', ['codeview']],
            ['help', ['help']],
            ['view', ['fullscreen']]
        ]
    });
    $('.note-statusbar').hide();//remove resize bar

    $('#type-select').on('change',function(e){
        e.preventDefault();
        if(parseInt($(this).val()) == {{PCK\ConsultantManagement\ConsultantManagementQuestionnaire::TYPE_MULTI_SELECT}} || parseInt($(this).val()) == {{PCK\ConsultantManagement\ConsultantManagementQuestionnaire::TYPE_SINGLE_SELECT}}){
            $('#questionnaire-options').show();
        }else{
            $('#questionnaire-options').hide();
        }
    });

    $('#addOptionBtn').on('click',function(e){
        e.preventDefault();
        var source = $("#document-template").html();
        var template = Handlebars.compile(source);

        var inputIdx;

        $('.optionRecordRow').each(function(i, fields){
            $('select,input', fields).each(function(){
                // Rename first array value from name to group index
                $(this).attr('name', $(this).attr('name').replace(/e\[[^\]]*\]/, 'e['+i+']')); 
            });
            i++;
            inputIdx = i;
        });

        var html = template({
            inputIdx:inputIdx
        });

        $("#optionRow").append(html);
    });

    $(document).on('click','.deleteOptionBtn',function(event){
        $(this).closest('.optionRecordRow').remove();
        $('.optionRecordRow').each(function(i, fields){
            $('select,input', fields).each(function(){
                $(this).attr('name', $(this).attr('name').replace(/e\[[^\]]*\]/, 'e['+i+']')); 
            });
            i++;
        });
    });
});
</script>
@endsection