@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('home.index', trans('navigation/mainnav.home'), []) }}</li>
        <li>{{ link_to_route('contractor.questionnaires.index', trans('general.questionnaires'), []) }}</li>
        <li>{{{ str_limit($projectObj->title, 50) }}}</li>
    </ol>
@endsection

@section('content')

<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <h1 class="page-title txt-color-blueDark">
            <i class="fa fa-tasks"></i> {{{ trans('general.questionnaires') }}}
        </h1>
    </div>
</div>

<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="jarviswidget ">
            <header>
                <h2> {{{ trans('general.questionnaires') }}} </h2>
            </header>
            <div>
                <div class="widget-body">
                    <div class="row">
                        <div class="col col-lg-12">
                            <dl class="dl-horizontal no-margin">
                                <dt>{{ trans('projects.title') }}:</dt>
                                <dd><div class="well">{{ nl2br($projectObj->title) }}</div></dd>
                                <dt>&nbsp;</dt>
                                <dd>&nbsp;</dd>
                            </dl>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col col-lg-3">
                            <dl class="dl-horizontal no-margin">
                                <dt>{{ trans('companies.referenceNo') }}:</dt>
                                <dd>{{{ $projectObj->reference }}}</dd>
                                <dt>&nbsp;</dt>
                                <dd>&nbsp;</dd>
                            </dl>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col col-lg-12">
                            <dl class="dl-horizontal no-margin">
                                <dt>{{ trans('companies.address') }}:</dt>
                                <dd>{{ nl2br($projectObj->address) }}</dd>
                                <dt>&nbsp;</dt>
                                <dd>&nbsp;</dd>
                            </dl>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xs-3 col-sm-3 col-md-3 col-lg-3">
                            <dl class="dl-horizontal no-margin">
                                <dt>{{ trans('companies.state') }}:</dt>
                                <dd>{{{ $projectObj->state->name }}}</dd>
                                <dt>&nbsp;</dt>
                                <dd>&nbsp;</dd>
                            </dl>
                        </div>
                        <div class="col-xs-3 col-sm-3 col-md-3 col-lg-3">
                            <dl class="dl-horizontal no-margin">
                                <dt>{{ trans('companies.country') }}:</dt>
                                <dd>{{{ $projectObj->country->country }}}</dd>
                                <dt>&nbsp;</dt>
                                <dd>&nbsp;</dd>
                            </dl>
                        </div>
                        <div class="col-xs-3 col-sm-3 col-md-3 col-lg-3"></div>
                        <div class="col-xs-3 col-sm-3 col-md-3 col-lg-3"></div>
                    </div>
                    <div class="row">
                        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                            <dl class="dl-horizontal no-margin">
                                <dt>{{ trans('tenders.publishDateTime') }}:</dt>
                                <dd>{{{ date('d/m/Y H:i:s', strtotime($questionnaire->published_date)) }}}</dd>
                                <dt>&nbsp;</dt>
                                <dd>&nbsp;</dd>
                            </dl>
                        </div>
                    </div>
                    <hr class="simple">
                    <div class="row">
                        <section class="col col-xs-12 col-sm-12 col-md-12 col-lg-12">
                            {{ Form::open(['route' => ['contractor.questionnaires.notify'], 'id'=>$questionnaire->id.'-notify_form', 'class' => 'smart-form']) }}
                            <div class="alert alert-warning text-center">
                                <i class="fa-fw fa fa-info"></i>
                                Please click <strong>Notify</strong> once you have answered all the questions and ready to submit it
                                to our <strong>Person in Charge</strong> for review.
                                {{ Form::button('<i class="fa fa-envelope"></i> '.trans('general.notify'), ['type' => 'submit', 'class' => 'btn btn-sm btn-info'] )  }}
                            </div>
                            {{ Form::hidden('id', $questionnaire->id) }}
                            {{ Form::close() }}
                        </section>
                    </div>
                    
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row" id="questionnaire-widgets">
    <?php $count = 1;?>
    @foreach($questionnaire->questions as $question)
    <article class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="jarviswidget" data-widget-editbutton="false" data-widget-colorbutton="false" data-widget-deletebutton="false" data-widget-sortable="false">
            <header>
                <h2><i class="fa fa-tasks"></i> {{{trans('general.question')}}} {{ $count }}</h2>
                <p style="padding-left:4px;line-height:32px;font-size:12px;display:inline-block;">
                    @if($question->required)
                    <span class="label label-danger">Mandatory</span>
                    @endif
                    <?php
                    $replySubmittedDate = $question->getReplySubmittedDate();
                    ?>
                    <label class="label label-success">{{{ trans('forms.submittedAt') }}} : <span id="{{$question->id}}-question_form-submitted_at-lbl"> @if($replySubmittedDate) {{ date('d/m/Y H:i:s', strtotime($replySubmittedDate)) }} @else - @endif</span></label>
                </p>
            </header>
            <div>
                <div class="widget-body">
                    <div class="row">
                        <section class="col col-xs-12 col-sm-12 col-md-12 col-lg-12">
                            <div class="well">
                                <?php
                                $reply = $question->getReply();
                                ?>
                                {{ Form::open(['route' => ['contractor.questionnaires.reply'], 'id'=>$question->id.'-question_form', 'class' => 'smart-form']) }}
                                <div class="well">{{ $question->question }}</div>
                                <div style="padding-top:8px;">
                                    @if($question->type == PCK\ContractorQuestionnaire\Question::TYPE_TEXT)
                                        <label class="textarea">
                                        {{ Form::textarea('text', Input::old('text', ($reply) ? $reply->text : ''), ['required' => 'required', 'rows' => 3]) }}
                                        </label>
                                    @elseif($question->type == PCK\ContractorQuestionnaire\Question::TYPE_MULTI_SELECT)
                                        <?php
                                        $selectedOptions = [];
                                        if($reply)
                                        {
                                            foreach($reply as $optionReply)
                                            {
                                                $selectedOptions[] = $optionReply->contractor_questionnaire_option_id;
                                            }
                                        }
                                        ?>
                                        @foreach($question->options as $option)
                                        <label class="checkbox">
                                            <input type="checkbox" name="options[]" value="{{$option->id}}" @if(in_array($option->id, $selectedOptions)) checked @endif>
                                            <i></i>{{ $option->text }}
                                        </label>
                                        @endforeach
                                    @elseif($question->type == PCK\ContractorQuestionnaire\Question::TYPE_SINGLE_SELECT)
                                        @foreach($question->options as $option)
                                        <label class="radio">
                                            <input type="radio" name="options" value="{{$option->id}}" @if($reply && $reply->contractor_questionnaire_option_id == $option->id) checked @endif>
                                            <i></i>{{ $option->text }}
                                        </label>
                                        @endforeach
                                    @endif

                                    @if($question->with_attachment)
                                    <div id="reply_attachment-{{$question->id}}-container" style="padding-top:8px;display:none;" class="reply_attachment-container">
                                        <div style="padding-top:8px;padding-bottom:8px;">
                                            <button type="button" class="attachment_upload-btn btn btn-success" id="attachment_question_upload-{{$question->id}}-btn" data-id="{{$question->id}}">
                                                <i class="fas fa-upload fa-md"></i> {{ trans('forms.upload') }}
                                            </button>
                                        </div>
                                        <div id="reply_attachment-{{$question->id}}-table"></div>
                                    </div>
                                    @endif
                                    <footer style="padding:0;">
                                        {{ Form::hidden('id', $question->id) }}
                                        {{ Form::hidden('qid', $questionnaire->id) }}

                                        @if($question->type != PCK\ContractorQuestionnaire\Question::TYPE_ATTACHMENT_ONLY)
                                        {{ Form::button('<i class="fa fa-save"></i> '.trans('forms.save'), ['type' => 'submit', 'class' => 'question-submit btn btn-primary', 'style' => 'float:none;margin-left:0;'] )  }}
                                        @endif

                                        @if($question->with_attachment)
                                        {{ Form::button('<i class="fa fa-paperclip"></i> '.trans('general.attachments'), ['type' => 'button', 'class' => 'reply_attachment-btn btn btn-info', 'style' => 'float:none;margin-left:0;', 'data-id'=>$question->id] )  }}
                                        @endif
                                    </footer>
                                </div>
                                {{ Form::close() }}
                            </div>
                        </section>
                    </div>
                </div>
            </div>
        </div>
    </article>
    <?php $count++; ?>
    @endforeach
</div>

<div class="modal fade" id="uploadAttachmentModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">{{ trans('forms.attachments') }}</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                {{ Form::open(['id' => 'attachment-upload-form', 'class' => 'smart-form', 'method' => 'post', 'files' => true]) }}
                    <section>
                        <label class="label">{{{ trans('forms.upload') }}}:</label>
                        {{ $errors->first('uploaded_files', '<em class="invalid">:message</em>') }}

                        @include('file_uploads.partials.upload_file_modal', ['id' => 'contractor_attachment-upload'])
                    </section>
                {{ Form::close() }}
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary" data-action="submit-attachments"><i class="fa fa-upload"></i> {{ trans('forms.submit') }}</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script type="text/javascript">
$(document).ready(function () {

    $('#questionnaire-widgets').jarvisWidgets({
        grid : 'article',
        widgets : '.jarviswidget',
        buttonsHidden : false,
        toggleButton : true,
        toggleClass : 'fa fa-minus | fa fa-plus',
        toggleSpeed : 200,
        fullscreenButton : true,
        fullscreenClass : 'fa fa-expand | fa fa-compress',
        fullscreenDiff : 3,
        buttonOrder : '%refresh% %custom% %edit% %toggle% %fullscreen% %delete%'
    });

    $('.question-submit').on('click', function(e){
        e.preventDefault();
        app_progressBar.show();
        var btn = $(this);
        var form = btn.parents('form:first');
        btn.prop("disabled", true);
        $.ajax({
            type: form.attr('method'),
            url: form.attr('action'),
            data: form.serialize(),
            success: function (data) {
                if(data.success && data.submitted_date.length > 0){
                    $('#'+form.attr('id')+'-submitted_at-lbl').html(data.submitted_date);
                }
                app_progressBar.maxOut(null, function(){ app_progressBar.hide(); });
                btn.prop("disabled", false);
            },
            error: function (data) {
                app_progressBar.maxOut(null, function(){ app_progressBar.hide(); });
                btn.prop("disabled", false);
            },
        });
    });

    $(".reply_attachment-btn").on('click', function(e){
        e.preventDefault();
        var questionId = $(this).data('id');

        $("#reply_attachment-"+questionId+"-container").toggle("fast");
        $('.reply_attachment-container').not('#reply_attachment-'+questionId+'-container').hide();

        var attachmentTbl = Tabulator.prototype.findTable("#reply_attachment-"+questionId+"-table")[0];

        if(attachmentTbl){
            attachmentTbl.destroy();
        }

        if($("#reply_attachment-"+questionId+"-container").is(":visible")){
            var url = '{{ route("contractor.questionnaires.attachments.list", ":questionId") }}';
            url = url.replace(':questionId', parseInt(questionId));

            attachmentTbl = new Tabulator("#reply_attachment-"+questionId+"-table", {
                placeholder: "{{ trans('general.noRecordsFound') }}",
                height: 240,
                ajaxURL: url,
                ajaxConfig: "GET",
                ajaxParams:{qid: {{$questionnaire->id}}},
                layout:"fitColumns",
                selectable: 1,
                responsiveLayout:'collapse',
                columns:[
                    {title:"&nbsp;", field:"title", minWidth: 300, hozAlign:"left", headerSort:false, formatter:function(cell, formatterParams, onRendered){
                        return '<label class="text-success" style="font-size:14px;"><i class="fa-lg far fa-file"></i></label>&nbsp;&nbsp;' + cell.getValue();
                    }},
                    {title:"{{ trans('general.type') }}", field:"extension", width: 100, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false},
                    {title:"{{ trans('files.uploadedAt') }}", field:"uploaded_at", width: 160, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false},
                    {title:"{{ trans('general.actions') }}", field:"id", width: 100, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                        innerHtml: [{
                            innerHtml: function(rowData){
                                var content = '<a href="'+rowData['route:download']+'" class="btn btn-xs btn-primary" title="{{{ trans("general.download") }}}"><i class="fa fa-download"></i></a>';
                                
                                if(rowData.deletable){
                                    content += '&nbsp;<button type="button" class="btn btn-xs btn-danger" data-id="'+rowData.id+'" onclick="attachmentDelete(\''+rowData['route:delete']+'\', \''+questionId+'\')" title="{{{ trans("forms.delete") }}}"><i class="fa fa-trash"></i></button>';
                                }

                                return content;
                            }
                        }]
                    }}
                ]
            });
        }
    });

    $('.attachment_upload-btn').on('click', function(e){
        e.preventDefault();
        var questionId = $(this).data('id');

        $(".template-upload").remove();//remoev any previous upload
        $(".template-download").remove();//remoev any previous uploaded

        $("[data-action=submit-attachments]").off('click');//reset click from previous callback set

        $('#uploadAttachmentModal').modal('show');

        $("[data-action=submit-attachments]").on('click', function(evt){
            evt.preventDefault();

            var uploadedFilesInput = [];

            $('form#attachment-upload-form input[name="uploaded_files[]"]').each(function(index){
                uploadedFilesInput.push($(this).val());
            });

            app_progressBar.show();

            $.post("{{route('contractor.questionnaires.upload.attachments')}}",{
                _token: "{{{csrf_token()}}}",
                id: questionId,
                qid: {{$questionnaire->id}},
                uploaded_files: uploadedFilesInput
            })
            .done(function(data){
                if(data.success){
                    $(".template-download").remove();
                    $('#uploadAttachmentModal').modal('hide');

                    if(data.submitted_at.length > 0){
                        $('#'+questionId+'-question_form-submitted_at-lbl').html(data.submitted_at);
                    }

                    var attachmentTbl = Tabulator.prototype.findTable("#reply_attachment-"+questionId+"-table")[0];

                    if(attachmentTbl){
                        attachmentTbl.setData();
                    }
                }
                app_progressBar.maxOut(null, function(){ app_progressBar.hide(); });
            })
            .fail(function(data){
                console.error('failed');
            });
        });
    });
});

function attachmentDelete(route, questionId){
    var r = confirm('Are you sure you want to delete this record?');
    if (r == true) {
        $.ajax({
            url: route,
            type: 'DELETE',
            data: {
                qid: {{$questionnaire->id}},
                id: questionId,
                _token:'{{{csrf_token()}}}'
            },
            success: function(result) {
                var attachmentTbl = Tabulator.prototype.findTable("#reply_attachment-"+questionId+"-table")[0];
                if(result.success && attachmentTbl){
                    attachmentTbl.setData();//reload
                }
            }
        });
    }
}
</script>
@endsection
