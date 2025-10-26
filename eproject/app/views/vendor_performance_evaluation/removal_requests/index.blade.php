@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{{ trans('vendorManagement.vendorPerformanceEvaluation') }}}</li>
        <li>{{ trans('vendorManagement.evaluationRemovalRequests') }}</li>
    </ol>
@endsection

@section('content')
<div class="row">
    <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
        <h1 class="page-title txt-color-blueDark">
            <i class="fa fa-backspace"></i> {{{ trans('vendorManagement.evaluationRemovalRequests') }}}
        </h1>
    </div>
</div>

<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="jarviswidget ">
            <header>
                <h2>{{{ trans('vendorManagement.evaluationRemovalRequests') }}}</h2>
            </header>
            <div>
                <div class="widget-body">
                    <div id="main-table"></div>
                </div>
            </div>
        </div>
    </div>
</div>

@include('vendor_performance_evaluation.evaluations.forms.partials.message_modal', [
    'title'             => trans('vendorManagement.dismissRequest'),
    'modalId'           => 'additionalRemarksModal',
    'textAreaId'        => 'txtRemarks',
    'actionButtonText'  => trans('general.dismiss'),
    'actionButtonClass' => 'danger',
])
@endsection

@section('js')
    <script src="{{ asset('js/app/app.restfulDelete.js') }}"></script>
    <script>
        $(document).ready(function () {
            new Tabulator('#main-table', {
                placeholder: "{{ trans('general.noRecordsFound') }}",
                data: {{ json_encode($data) }},
                layout:"fitColumns",
                columns:[
                    {title:"{{ trans('general.no') }}", formatter:"rownum", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
                    {title:"{{ trans('projects.title') }}", field:"title", minWidth:300, hozAlign:"left", headerSort:false, headerFilter:true},
                    {title:"{{ trans('users.name') }}", field:"userName", width:150, hozAlign:"center", headerSort:false, cssClass:"text-center text-middle", headerFilter:true},
                    {title:"{{ trans('users.email') }}", field:"userEmail", width:150, hozAlign:"center", headerSort:false, cssClass:"text-center text-middle", headerFilter:true},
                    {title:"{{ trans('companies.company') }}", field:"company", width: 150, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false},
                    {title:"{{ trans('vendorManagement.reason') }}", field:"reason", width: 200, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false},
                    {title:"{{ trans('general.remarks') }}", field:"remarks", width: 300, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false},
                    {title:"{{ trans('general.actions') }}", width: 180, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                        innerHtml:[
                            {
                                tag: 'a',
                                rowAttributes: {href:'route:view'},
                                attributes: {class:'btn btn-xs btn-default', title: '{{ trans("general.view") }}'},
                                innerHtml: {
                                    tag: 'i',
                                    attributes: {class: 'fa fa-search'}
                                }
                            },{
                                innerHtml: function(){
                                    return '&nbsp;';
                                }
                            },{
                                tag: 'a',
                                rowAttributes: {href:'route:cycleEdit'},
                                attributes: {class:'btn btn-xs btn-warning', title: '{{ trans("vendorManagement.removeProjectEvaluation") }}'},
                                innerHtml: {
                                    tag: 'i',
                                    attributes: {class: 'fa fa-edit'}
                                }
                            },
                            {
                                innerHtml: function(){
                                    return '&nbsp;';
                                }
                            },{
                                tag: 'button',
                                rowAttributes: {'data-url':'route:destroy'},
                                attributes: {class:'btn btn-xs btn-danger', 'data-action':'dismiss_request', title: '{{ trans("vendorManagement.dismissRequest") }}'},
                                innerHtml: function(){
                                    return "{{ trans('vendorManagement.dismissRequest') }}";
                                }
                            }
                        ]
                    }}
                ],
            });

            $(document).on('click', '[data-action="dismiss_request"]', function(e) {
                e.preventDefault();

                var url = $(this).data('url');

                $('#additionalRemarksModal [data-action="sendEmailWithAdditionalRemarks"]').data('url', url);

                $('#additionalRemarksModal').modal('show');
            });

            $('#additionalRemarksModal [data-action="sendEmailWithAdditionalRemarks"]').on('click', function(e) {
                e.preventDefault();

                app_progressBar.toggle();
                app_progressBar.maxOut();

                var remarks = DOMPurify.sanitize($('#txtRemarks').val().trim());
                var url     = $(this).data('url');

                $.post(url, {
                    remarks: remarks,
                    _token: '{{ csrf_token() }}'
                })
                .done(function(response){
                    if(response.success){
                        $('#additionalRemarksModal').modal('hide');
                        app_progressBar.toggle();
                        window.location.reload();
                    }
                })
                .fail(function(){
                    SmallErrorBox.refreshAndRetry();
                    app_progressBar.toggle();
                });
            });
        });
    </script>
@endsection