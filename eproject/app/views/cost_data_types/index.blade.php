@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{{ trans('costData.costDataTypes') }}}</li>
    </ol>
@endsection

@section('content')

<div id="content">
    <div class="row">
        <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
            <h1 class="page-title txt-color-blueDark">
                <i class="fa fa-list"></i> {{{ trans('costData.costDataTypes') }}}
            </h1>
        </div>
    </div>

    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <div class="jarviswidget ">
                <header>
                    <h2>{{{ trans('costData.costDataTypes') }}}</h2>
                </header>
                <div>
                    <div class="widget-body">
                        <fieldset>
                            {{ Form::open(array('id' => 'main-form', 'class' => 'smart-form')) }}
                            <form class="smart-form">
                                <div class="row">
                                    <section class="col col-xs-9 col-md-9 col-lg-9">
                                        <h5 id="form-header">{{{ trans('forms.addItem') }}}</h5>
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
                                <br/>
                                <div class="row">
                                    <section class="col col-xs-12 col-md-8 col-lg-8">
                                        <label class="label">{{{ trans('costData.name') }}} <span class="required">*</span>:</label>
                                        <label class="input" data-input="name">
                                            {{ Form::text('name', Input::old('name'), array('required' => 'required', 'autofocus' => 'autofocus')) }}
                                        </label>
                                        <em class="invalid" data-error="name"></em>
                                    </section>
                                </div>
                                {{ Form::hidden('id', Input::old('id') ?? -1, ['id'=>'id-hidden']) }}
                            {{ Form::close() }}
                        </fieldset>
                        <hr class="simple"/>
                        <div id="main-table"></div>
                    </div>
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
                ajaxURL: "{{ route('costDataTypes.list') }}",
                ajaxConfig: "GET",
                paginationSize: 100,
                pagination: "remote",
                ajaxFiltering:true,
                layout:"fitColumns",
                columns:[
                    {title:"{{ trans('general.no') }}", formatter:"rownum", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
                    {title:"{{ trans('costData.name') }}", field:"name", minWidth: 300, hozAlign:"left", headerSort:false},
                    {title:"{{ trans('general.actions') }}", width: 120, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                        innerHtml: [
                            {
                                tag: 'button',
                                rowAttributes: {'data-id':'id'},
                                attributes: {"data-action":"edit", type:'button', class:'btn btn-xs btn-warning', title: '{{ trans("forms.updateItem") }}'},
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

            $("#main-form").on('submit', function(e){
                app_progressBar.toggle();
                var dataStr = $(this).serialize();
                $.ajax({
                    type: "POST",
                    url: "{{route('costDataTypes.update')}}",
                    data: dataStr,
                    success: function (resp) {
                        app_progressBar.maxOut();
                        $("#main-form [data-input]").removeClass('state-error');
                        $("#main-form [data-error]").html("");

                        if(!resp.success){
                            $.each( resp.errors, function( key, data ) {
                                $("#main-form [data-input="+data.key+"]").addClass('state-error');
                                $("#main-form [data-error="+data.key+"]").html(data.msg);
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
                $("#form-header").html("{{{ trans('forms.addItem') }}}");
                $("#edit-cancel-btn").hide();
                $("#remarks").hide();
                $("#main-form [data-input]").removeClass('state-error');
                $("#main-form [data-error]").html("");
                $("#main-form [name=name]").val("");
                $('#id-hidden').val(-1);
                $("#main-form [name=name]").focus();
            }
            $('#main-table').on('click', '[data-action=edit]', function(){
                $("#form-header").html("{{{ trans('forms.edit') }}}");
                $("#edit-cancel-btn").show();
                $("#main-form [data-input]").removeClass('state-error');
                $("#main-form [data-error]").html("");
                var row = mainTable.getRow($(this).data('id'));
                $("#main-form [name=name]").val(row.getData()['name']);
                $('#id-hidden').val($(this).data('id'));
                $("#remarks").hide();

                $("#main-form [name=name]").focus();
            });
        });
    </script>
@endsection