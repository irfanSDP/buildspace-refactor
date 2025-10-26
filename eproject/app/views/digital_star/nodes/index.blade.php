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
                <h2>{{{ $parentNode->name }}}</h2>
            </header>
            <div>
                <div class="widget-body">
                    <ol class="breadcrumb bg-transparent border border-info">
                        @foreach($ancestors as $ancestor)
                            <li class="breadcrumb-item text-info"><a href="{{ $ancestor['route'] }}" class="text-info">{{{ $ancestor['name'] }}}</a></li>
                        @endforeach
                    </ol>
                    @if($templateForm->isDraft())
                    <fieldset>
                        {{ Form::open(array('id' => 'node-form', 'class' => 'smart-form')) }}
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
                                    <label class="label">{{{ trans('vendorPreQualification.weight') }}} <span class="required">*</span>:</label>
                                    <label class="input" data-input="weight">
                                        {{ Form::number('weight', Input::old('weight'), array('required' => 'required', 'min' => 0, 'max' => 100)) }}
                                    </label>
                                    <em class="invalid" data-error="weight"></em>
                                </section>
                            </div>
                            {{ Form::hidden('node_id', Input::old('node_id') ?? -1, ['id'=>'node-id-hidden']) }}
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
                ajaxURL: "{{ route('digital-star.templateForm.nodes.list', [$templateForm->id, $parentNode->id]) }}",
                layout:"fitColumns",
                columns:[
                    {title:"{{ trans('general.no') }}", formatter:"rownum", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
                    {title:"{{ trans('vendorPreQualification.name') }}", field:"name", minWidth: 300, hozAlign:"left", headerSort:false},
                    {title:"{{ trans('vendorPreQualification.weight') }}", field:"weight", width: 100, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false},
                    {title:"{{ trans('general.percentage') }}", field:"percentage", width: 100, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false},
                    {title:"{{ trans('general.actions') }}", width: 120, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                        show: function(cell){
                            return cell.getData().hasOwnProperty('id');
                        },
                        innerHtml: [
                            {
                                opaque: function(cell){
                                    return cell.getData()['can_go_next'];
                                },
                                tag: 'a',
                                attributes: {class:'btn btn-xs btn-default', title: '{{ trans("vendorPreQualification.items") }}'},
                                rowAttributes: {'href': 'route:next'},
                                innerHtml: {
                                    tag: 'i',
                                    attributes: {class: 'fa fa-arrow-right'}
                                }
                            },{
                                innerHtml: function(){
                                    return '&nbsp;';
                                }
                            },{
                                opaque: function(cell){
                                    return cell.getData()['can_add_score'];
                                },
                                tag: 'a',
                                attributes: {class:'btn btn-xs btn-default', title: '{{ trans("vendorPreQualification.scores") }}'},
                                rowAttributes: {'href': 'route:scores'},
                                innerHtml: {
                                    tag: 'i',
                                    attributes: {class: 'fa fa-list'}
                                }
                            },{
                                innerHtml: function(){
                                    return '&nbsp;';
                                }
                            },{
                                opaque:function(cell){
                                    return cell.getData()['can_edit'];
                                },
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
                                    if(rowData['deletable'])
                                    {
                                        return '<a href="'+rowData['route:delete']+'" class="btn btn-xs btn-danger" data-id="'+rowData['id']+'" data-method="delete" data-csrf_token="{{{ csrf_token() }}}"><i class="fa fa-trash"></i></a>';
                                    }

                                    return '<button type="button" class="btn btn-xs invisible"><i class="fa fa-trash"></i></button>';
                                }
                            },
                        ]
                    }}
                ],
            });

            $("#node-form").on('submit', function(e){
                app_progressBar.toggle();
                var dataStr = $(this).serialize();
                $.ajax({
                    type: "POST",
                    url: "{{route('digital-star.templateForm.nodes.storeOrUpdate', [$templateForm->id, $parentNode->id])}}",
                    data: dataStr,
                    success: function (resp) {
                        app_progressBar.maxOut();
                        $("#node-form [data-input]").removeClass('state-error');
                        $("#node-form [data-error]").html("");

                        if(!resp.success){
                            $.each( resp.errors, function( key, data ) {
                                $("#node-form [data-input="+data.key+"]").addClass('state-error');
                                $("#node-form [data-error="+data.key+"]").html(data.msg);
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
                $("#node-form [data-input]").removeClass('state-error');
                $("#node-form [data-error]").html("");
                $("#node-form [name=name]").val("");
                $("#node-form [name=weight]").val("");
                $('#node-id-hidden').val(-1);
                $("#node-form [name=name]").focus();
            }
            $('#main-table').on('click', '[data-action=edit]', function(){
                $("#form-header").html("{{{ trans('forms.edit') }}}");
                $("#edit-cancel-btn").show();
                $("#node-form [data-input]").removeClass('state-error');
                $("#node-form [data-error]").html("");
                var row = mainTable.getRow($(this).data('id'));
                $("#node-form [name=name]").val(row.getData()['name']);
                $("#node-form [name=weight]").val(row.getData()['weight']);
                $('#node-id-hidden').val($(this).data('id'));
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
                $("#node-form [name=name]").focus();
            });
        });
    </script>
@endsection