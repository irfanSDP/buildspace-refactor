@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{{ trans('digitalStar/vendorManagement.vendorPerformanceEvaluation') }}}</li>
        <li>{{ trans('digitalStar/vendorManagement.setup') }}</li>
    </ol>
@endsection

@section('content')
<div class="row">
    <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
        <h1 class="page-title txt-color-blueDark">
            <i class="fa fa-users"></i> {{{ trans('digitalStar/vendorManagement.setup') }}}
        </h1>
    </div>
    <div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">
        <div class="btn-group pull-right header-btn">
            @include('digital_star.setups.evaluations.index_action_menu', array('classes' => 'pull-right'))
        </div>
    </div>
</div>

<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="jarviswidget ">
            <header>
                <h2>{{{ empty($cycle->remarks) ? trans('digitalStar/vendorManagement.vendorPerformanceEvaluationCycle') : $cycle->remarks }}}</h2>
            </header>
            <div>
                <div class="widget-body">
                    <div id="main-table"></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
    <script src="{{ asset('js/app/app.restfulDelete.js') }}"></script>
    <script>
        $(document).ready(function () {
            var mainTable = new Tabulator('#main-table', {
                height:450,
                ajaxURL: "{{ ! empty($cycle) ? route('digital-star.setups.list', array('cycle' => $cycle->id)) : route('digital-star.setups.list') }}",
                placeholder: "{{ trans('general.noRecordsFound') }}",
                ajaxConfig: "GET",
                paginationSize: 100,
                pagination: "remote",
                ajaxFiltering:true,
                layout:"fitColumns",
                columns:[
                    {title:"{{ trans('general.no') }}", formatter:"rownum", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false, frozen:true},
                    {title:"{{ trans('digitalStar/digitalStar.company') }}", field:"company_name", hozAlign:"left", headerSort:false, cssClass:"text-left text-middle", headerFilter:true},
                    {title:"{{ trans('digitalStar/digitalStar.vendorGroup') }}", field:"vendor_group", hozAlign:"left", headerSort:false, cssClass:"text-left text-middle", headerFilter:true},
                    {title:"{{ trans('digitalStar/vendorManagement.startDate') }}", field:"start_date", width:150, hozAlign:"center", headerSort:false, cssClass:"text-center text-middle", headerFilter:false},
                    {title:"{{ trans('digitalStar/vendorManagement.endDate') }}", field:"end_date", width:150, hozAlign:"center", headerSort:false, cssClass:"text-center text-middle", headerFilter:false},
                    {title:"{{ trans('general.status') }}", field:"status", width: 100, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, editable: false, editor:"select", headerFilter:true, headerFilterParams:{values:{0:"{{ trans('general.all') }}", {{ \PCK\DigitalStar\Evaluation\DsEvaluation::STATUS_DRAFT }}:"{{ trans('digitalStar/vendorManagement.draft') }}", {{ \PCK\DigitalStar\Evaluation\DsEvaluation::STATUS_IN_PROGRESS }}:"{{ trans('digitalStar/vendorManagement.inProgress')}}", {{ \PCK\DigitalStar\Evaluation\DsEvaluation::STATUS_COMPLETED }}:"{{ trans('digitalStar/vendorManagement.completed') }}"}}},
                    {title:"{{ trans('general.actions') }}", width: 200, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                        show: function(cell){
                            return cell.getData().hasOwnProperty('id');
                        },
                        innerHtml: [
                            {
                                tag: 'a',
                                attributes: {class:'btn btn-xs btn-primary', 'data-action':'send-form-assigned-email', 'title': '{{ trans("digitalStar/digitalStar.sendReminders") }}'},
                                rowAttributes: {'data-url': 'route:send_form_assigned_email'},
                                innerHtml: {
                                    tag: 'i',
                                    attributes: {class: 'fa fa-envelope'},
                                }
                            },{
                                innerHtml: function(){
                                    return '&nbsp;';
                                }
                            },{
                                opaque: function(cell){
                                    return cell.getData()['route:evaluators'];
                                },
                                tag: 'a',
                                attributes: {class:'btn btn-xs btn-primary', title: '{{ trans("digitalStar/digitalStar.evaluators") }}'},
                                rowAttributes: {'href': 'route:evaluators'},
                                innerHtml: {
                                    tag: 'i',
                                    attributes: {class: 'fa fa-users'}
                                }
                            },{
                                innerHtml: function(){
                                    return '&nbsp;';
                                }
                            },{
                                opaque: function(cell){
                                    return cell.getData()['route:processors'];
                                },
                                tag: 'a',
                                attributes: {
                                    class:'btn btn-xs',
                                    style: 'color: #fff; background-color: #886ab5; border-color: #886ab5;',
                                    title: '{{ trans("digitalStar/digitalStar.processors") }}'
                                },
                                rowAttributes: {'href': 'route:processors'},
                                innerHtml: {
                                    tag: 'i',
                                    attributes: {
                                        class: 'fa fa-users',
                                    }
                                }
                            }
                        ]
                    }}
                ],
            });

            $(document).on('click', '[data-action="send-form-assigned-email"]', function(e) {
                e.preventDefault();

                app_progressBar.toggle();
                app_progressBar.maxOut();

                var url = $(this).data('url');

                $.post(url, {
                    _token: '{{ csrf_token() }}'
                })
                .done(function(response){
                    if(response.success){
                        app_progressBar.toggle();

                        $.smallBox({
                            title : "{{ trans('general.success') }}",
                            content : "<i class='fa fa-check'></i> <i>{{ trans('digitalStar/digitalStar.remindersSent') }}</i>",
                            color : "#739E73",
                            sound: false,
                            iconSmall : "fa fa-paper-plane",
                            timeout : 5000
                        });
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