@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{{ trans('digitalStar/vendorManagement.vendorPerformanceEvaluation') }}}</li>
        <li>{{ link_to_route('digital-star.templateForm', trans('forms.templateForms'), array()) }}</li>
        <li>{{{ $templateForm->weightedNode->name }}}</li>
    </ol>
@endsection

@section('content')
<div class="row">
    <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
        <h1 class="page-title txt-color-blueDark">
            <i class="fa fa-users"></i> {{{ trans('digitalStar/vendorManagement.forms') }}}
        </h1>
    </div>
</div>

<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="jarviswidget ">
            <header>
                <h2>{{{ $node->name }}}</h2>
            </header>
            <div>
                <div class="widget-body">
                    <ol class="breadcrumb bg-transparent border border-info">
                        @foreach($ancestors as $ancestor)
                            <li class="breadcrumb-item text-info"><a href="{{ $ancestor['route'] }}" class="text-info">{{{ $ancestor['name'] }}}</a></li>
                        @endforeach
                        <li class="breadcrumb-item">{{{ trans('digitalStar/vendorManagement.scores') }}}</li>
                    </ol>
                    @if($templateForm->isDraft())
                    <fieldset>
                        {{ Form::open(array('id' => 'score-form', 'class' => 'smart-form')) }}
                        <form class="smart-form">
                            <div class="row">
                                <section class="col col-xs-9 col-md-9 col-lg-9">
                                    <h5 id="form-header">{{{ trans('vendorPreQualification.addItem') }}}</h5>
                                </section>
                                <section class="col col-xs-3 col-md-3 col-lg-3">
                                    <div class="pull-right">
                                        <button type="submit" class="btn btn-primary btn-md header-btn">
                                            <i class="far fa-save"></i> {{{ trans('forms.save') }}}
                                        </button>
                                        <button type="button" class="btn btn-default btn-md header-btn" id="edit-cancel-btn" style="display:none;">{{{ trans('forms.cancel') }}}</button>
                                    </div>
                                </section>
                            </div>
                            <div id="remarks" class="well" style="display:none;">
                                <i class="fa fa-exclamation-triangle"></i> <span data-label="remarks"></span>
                            </div>
                            <br/>
                            <div class="row">
                                <section class="col col-xs-12 col-md-8 col-lg-8">
                                    <label class="label">{{{ trans('vendorPreQualification.name') }}} <span class="required">*</span>:</label>
                                    <label class="input" data-input="name">
                                        {{ Form::text('name', Input::old('name'), array('required' => 'required', 'autofocus' => 'autofocus')) }}
                                    </label>
                                    <em class="invalid" data-error="name"></em>
                                </section>
                                <section class="col col-xs-12 col-md-4 col-lg-4">
                                    <label class="label">{{{ trans('vendorPreQualification.score') }}} <span class="required">*</span>:</label>
                                    <label class="input" data-input="value">
                                        {{ Form::number('value', Input::old('value'), array('required' => 'required', 'min' => 0, 'max' => 100)) }}
                                    </label>
                                    <em class="invalid" data-error="value"></em>
                                </section>
                            </div>
                            {{ Form::hidden('score_id', Input::old('score_id') ?? -1, ['id'=>'score-id-hidden']) }}
                        {{ Form::close() }}
                    </fieldset>
                    <hr class="simple"/>
                    @endif
                    <div id="main-table"></div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('js')
    <script src="<?php echo asset('js/app/app.restfulDelete.js'); ?>"></script>
    <script>
        $(document).ready(function () {
            var mainTable = new Tabulator('#main-table', {
                height:450,
                placeholder: "{{ trans('general.noRecordsFound') }}",
                ajaxURL: "{{ route('digital-star.templateForm.nodes.scores.list', [$templateForm->id, $node->id]) }}",
                layout:"fitColumns",
                columns:[
                    {title:"{{ trans('general.no') }}", formatter:"rownum", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
                    {title:"{{ trans('digitalStar/vendorManagement.name') }}", field:"name", minWidth: 300, hozAlign:"left", headerSort:false},
                    {title:"{{ trans('digitalStar/vendorManagement.score') }}", field:"value", width: 100, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false},
                    {title:"{{ trans('general.actions') }}", field: "actions", width: 120, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                        show: function(cell){
                            return cell.getData().hasOwnProperty('id');
                        },
                        innerHtml: [
                            {
                                tag: 'button',
                                rowAttributes: {'data-id':'id'},
                                attributes: {"data-action":"edit", type:'button', class:'btn btn-xs btn-warning', title: '{{ trans("vendorPreQualification.updateItem") }}'},
                                innerHtml: {
                                    tag: 'i',
                                    attributes: {class: 'fa fa-edit'}
                                }
                            },{
                                innerHtml: function(){
                                    return '&nbsp;';
                                }
                            },{
                                innerHtml: function(rowData){
                                    return '<a href="'+rowData['route:delete']+'" class="btn btn-xs btn-danger" data-id="'+rowData['id']+'" data-method="delete" data-csrf_token="{{{ csrf_token() }}}"><i class="fa fa-trash"></i></a>';
                                }
                            },
                        ]
                    }}
                ],
            });

            @if(!$editable)
            mainTable.hideColumn('actions');
            @endif

            $("#score-form").on('submit', function(e){
                app_progressBar.toggle();
                var dataStr = $(this).serialize();
                $.ajax({
                    type: "POST",
                    url: "{{route('digital-star.templateForm.nodes.scores.storeOrUpdate', [$templateForm->id, $node->id])}}",
                    data: dataStr,
                    success: function (resp) {
                        app_progressBar.maxOut();
                        $("#score-form [data-input]").removeClass('state-error');
                        $("#score-form [data-error]").html("");

                        if(!resp.success){
                            $.each( resp.errors, function( key, data ) {
                                $("#score-form [data-input="+data.key+"]").addClass('state-error');
                                $("#score-form [data-error="+data.key+"]").html(data.msg);
                            });
                        }else{
                            $.smallBox({
                                title : "{{ trans('general.success') }}",
                                content : "<i class='fa fa-check'></i> <i>{{ trans('forms.saved') }}</i>",
                                color : "#179c8e",
                                sound: true,
                                iconSmall : "fa fa-save",
                                timeout : 1000
                            });
                            resetForm();
                            mainTable.setData();
                        }
                        app_progressBar.toggle();
                    }
                });

                e.preventDefault();
            });
            $("#edit-cancel-btn").on('click', function(){
                resetForm();
            });
            function resetForm(){
                $("#form-header").html("{{{ trans('vendorPreQualification.addItem') }}}");
                $("#edit-cancel-btn").hide();
                $("#remarks").hide();
                $("#score-form [data-input]").removeClass('state-error');
                $("#score-form [data-error]").html("");
                $("#score-form [name=name]").val("");
                $("#score-form [name=value]").val("");
                $('#score-id-hidden').val(-1);
                $("#score-form [name=name]").focus();
            }
            $('#main-table').on('click', '[data-action=edit]', function(){
                $("#form-header").html("{{{ trans('forms.edit') }}}");
                $("#edit-cancel-btn").show();
                $("#score-form [data-input]").removeClass('state-error');
                $("#score-form [data-error]").html("");
                var row = mainTable.getRow($(this).data('id'));
                $("#score-form [name=name]").val(row.getData()['name']);
                $("#score-form [name=value]").val(row.getData()['value']);
                $('#score-id-hidden').val($(this).data('id'));
                $("#remarks").hide();
                if(row.getData()['remarks'])
                {
                    $("#remarks").show();
                    if(row.getData()['amendments_required'])
                    {
                        $("#remarks").removeClass("border-success");
                        $("#remarks").removeClass("text-success");
                        $("#remarks").addClass("border-danger");
                        $("#remarks").addClass("text-danger");
                    }
                    else
                    {
                        $("#remarks").removeClass("border-danger");
                        $("#remarks").removeClass("text-danger");
                        $("#remarks").addClass("border-success");
                        $("#remarks").addClass("text-success");
                    }
                    $("[data-label=remarks]").html(row.getData()['remarks']);
                }
                $("#score-form [name=name]").focus();
            });
        });
    </script>
@endsection